<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Generate an invoice number in the format FACT-YYYY-NNNNN.
     */
    public function generateInvoiceNumber(): string
    {
        $year  = date('Y');
        $count = Invoice::whereYear('created_at', $year)->count() + 1;

        return sprintf('FACT-%s-%05d', $year, $count);
    }

    /**
     * Generate a receipt number in the format RECU-YYYY-NNNNN.
     */
    public function generateReceiptNumber(): string
    {
        $year  = date('Y');
        $count = Payment::whereYear('created_at', $year)->count() + 1;

        return sprintf('RECU-%s-%05d', $year, $count);
    }

    /**
     * Resolve the applicable fee amount for an enrollment from the fee_structures table.
     * Falls back: level → cycle → global (null cycle & null level).
     */
    public function resolveFeeAmount(FeeType $feeType, Enrollment $enrollment): ?float
    {
        $class = $enrollment->schoolClass;
        $level = $class->level;

        // 1. Level-specific price
        $structure = FeeStructure::where('fee_type_id', $feeType->id)
            ->where('academic_year_id', $enrollment->academic_year_id)
            ->where('level_id', $level->id)
            ->first();

        // 2. Cycle-specific price
        if (! $structure) {
            $structure = FeeStructure::where('fee_type_id', $feeType->id)
                ->where('academic_year_id', $enrollment->academic_year_id)
                ->where('cycle_id', $level->cycle_id)
                ->whereNull('level_id')
                ->first();
        }

        // 3. Global price (no cycle, no level)
        if (! $structure) {
            $structure = FeeStructure::where('fee_type_id', $feeType->id)
                ->where('academic_year_id', $enrollment->academic_year_id)
                ->whereNull('cycle_id')
                ->whereNull('level_id')
                ->first();
        }

        return $structure ? (float) $structure->amount : null;
    }

    /**
     * Generate invoices for a specific fee type for all active enrollments.
     *
     * @return int  Number of invoices created (skips already existing ones)
     */
    public function generateInvoicesForYear(FeeType $feeType, AcademicYear $year): int
    {
        $enrollments = Enrollment::with(['student', 'schoolClass.level.cycle'])
            ->where('academic_year_id', $year->id)
            ->where('status', 'VALIDE')
            ->get();

        $created = 0;

        DB::transaction(function () use ($enrollments, $feeType, $year, &$created) {
            foreach ($enrollments as $enrollment) {
                // Skip if invoice already exists for this enrollment + fee type
                $exists = Invoice::where('enrollment_id', $enrollment->id)
                    ->where('fee_type_id', $feeType->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $amount = $this->resolveFeeAmount($feeType, $enrollment);
                if ($amount === null || $amount <= 0) {
                    continue;
                }

                Invoice::create([
                    'invoice_number'   => $this->generateInvoiceNumber(),
                    'student_id'       => $enrollment->student_id,
                    'enrollment_id'    => $enrollment->id,
                    'academic_year_id' => $year->id,
                    'fee_type_id'      => $feeType->id,
                    'amount_due'       => $amount,
                    'amount_paid'      => 0,
                    'status'           => 'IMPAYEE',
                ]);

                $created++;
            }
        });

        return $created;
    }

    /**
     * Record a payment against an invoice, updating amount_paid and status.
     *
     * @throws \RuntimeException if payment exceeds remaining balance
     */
    public function recordPayment(Invoice $invoice, array $data): Payment
    {
        $amount = (float) $data['amount'];

        if ($amount > $invoice->amount_remaining) {
            throw new \RuntimeException(
                "Le montant payé ({$amount}) excède le solde restant ({$invoice->amount_remaining})."
            );
        }

        return DB::transaction(function () use ($invoice, $data, $amount) {
            $payment = Payment::create([
                'receipt_number' => $this->generateReceiptNumber(),
                'invoice_id'     => $invoice->id,
                'student_id'     => $invoice->student_id,
                'amount'         => $amount,
                'payment_method' => $data['payment_method'],
                'payment_date'   => $data['payment_date'],
                'reference'      => $data['reference'] ?? null,
                'recorded_by'    => $data['recorded_by'] ?? null,
                'notes'          => $data['notes'] ?? null,
            ]);

            // Update invoice totals
            $invoice->amount_paid = (float) $invoice->amount_paid + $amount;
            $invoice->save();
            $invoice->recalculateStatus();

            return $payment;
        });
    }
}
