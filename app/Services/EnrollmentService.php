<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\FeeType;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EnrollmentService
{
    /**
     * Complete enrollment process for a new student with guardian and inscription payment.
     */
    public function enrollNewStudent(
        array $studentData,
        array $guardianData,
        int $classId,
        string $type = 'NOUVEAU',
        bool $isRepeater = false,
        ?string $notes = null,
        string $paymentMethod = 'ESPECES',
        ?float $customAmountPaid = null,
        ?string $paymentReference = null
    ): Enrollment {
        return DB::transaction(function () use ($studentData, $guardianData, $classId, $type, $isRepeater, $notes, $paymentMethod, $customAmountPaid, $paymentReference) {
            $activeYear = AcademicYear::getActive();
            if (! $activeYear) {
                throw new \Exception("Aucune année scolaire active n'a été définie.");
            }

            // 1. Generate Matricule & Create Student
            $studentData['matricule'] = StudentMatriculeGenerator::generate($activeYear->name);
            $student = Student::create($studentData);

            // 2. Find or Create Guardian
            // Note: the enrollment form uses prefixed keys (guardian_profession, guardian_email, guardian_city)
            // while the convert flow passes normalized keys. Both are handled via fallback chains.
            $guardianData = [
                'first_name' => $guardianData['guardian_first_name'] ?? $guardianData['first_name'] ?? null,
                'last_name' => $guardianData['guardian_last_name'] ?? $guardianData['last_name'] ?? null,
                'relationship' => $guardianData['relationship'] ?? 'PERE',
                'profession' => $guardianData['guardian_profession'] ?? $guardianData['profession'] ?? null,
                'phone_primary' => $guardianData['phone_primary'],
                'phone_secondary' => $guardianData['phone_secondary'] ?? null,
                'email' => $guardianData['guardian_email'] ?? $guardianData['email'] ?? null,
                'address' => $guardianData['address'] ?? null,
                'city' => $guardianData['guardian_city'] ?? $guardianData['city'] ?? 'Niamey',
            ];

            $guardian = Guardian::firstOrCreate(
                ['phone_primary' => $guardianData['phone_primary']],
                $guardianData
            );

            // Ensure Guardian has an active User account for Parent portal access
            if (! $guardian->user_id) {
                $digits = preg_replace('/\D/', '', $guardianData['phone_primary']);
                $cleanPhone = strlen($digits) > 8 ? substr($digits, -8) : $digits;

                $user = null;
                if (! empty($guardianData['email'])) {
                    $user = User::where('email', $guardianData['email'])->first();
                }
                if (! $user && ! empty($cleanPhone)) {
                    $user = User::where('phone', $cleanPhone)->first();
                }

                if (! $user) {
                    $userEmail = ! empty($guardianData['email']) ? $guardianData['email'] : "parent.{$cleanPhone}@roisdusahel.ne";
                    if (User::where('email', $userEmail)->exists()) {
                        $userEmail = "parent.{$cleanPhone}.".uniqid().'@roisdusahel.ne';
                    }

                    $user = User::create([
                        'name' => trim(($guardianData['first_name'] ?? '').' '.($guardianData['last_name'] ?? '')),
                        'email' => $userEmail,
                        'phone' => ! empty($cleanPhone) ? $cleanPhone : null,
                        'password' => Hash::make('password'),
                        'email_verified_at' => now(),
                    ]);
                    $user->assignRole('Parent');
                }

                $guardian->update(['user_id' => $user->id]);
            }

            // 3. Link Student & Guardian
            $student->guardians()->syncWithoutDetaching([
                $guardian->id => [
                    'is_primary_contact' => true,
                    'can_pick_up' => true,
                ],
            ]);

            // 4. Create Enrollment Record
            $enrollmentNumber = StudentMatriculeGenerator::generateEnrollmentNumber($activeYear->name);

            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'class_id' => $classId,
                'academic_year_id' => $activeYear->id,
                'enrollment_number' => $enrollmentNumber,
                'type' => $type,
                'status' => 'VALIDE',
                'is_repeater' => $isRepeater,
                'enrollment_date' => now()->toDateString(),
                'notes' => $notes,
            ]);

            // 5. Create & Pay Inscription Fee Invoice
            $paymentService = app(PaymentService::class);
            $inscriptFeeType = FeeType::firstOrCreate(
                ['code' => 'INSCRIPT'],
                ['name' => "Frais d'inscription", 'is_recurring' => false, 'is_active' => true]
            );

            $inscriptAmount = $paymentService->resolveFeeAmount($inscriptFeeType, $enrollment) ?? 0;

            $inscriptInvoice = Invoice::create([
                'invoice_number' => $paymentService->generateInvoiceNumber(),
                'student_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'academic_year_id' => $activeYear->id,
                'fee_type_id' => $inscriptFeeType->id,
                'amount_due' => $inscriptAmount,
                'amount_paid' => 0,
                'status' => 'IMPAYEE',
                'due_date' => now()->toDateString(),
                'notes' => "Frais d'inscription réglés lors de l'inscription.",
            ]);

            $paidNow = $customAmountPaid !== null ? $customAmountPaid : $inscriptAmount;
            $surplusPayment = 0.0;

            if ($paidNow > 0) {
                $amountToInscript = min($paidNow, $inscriptAmount);
                if ($amountToInscript > 0) {
                    $paymentService->recordPayment($inscriptInvoice, [
                        'amount' => $amountToInscript,
                        'payment_method' => $paymentMethod,
                        'payment_date' => now()->toDateString(),
                        'reference' => $paymentReference ?? 'RECU-INCS-'.strtoupper(substr(md5(uniqid()), 0, 6)),
                        'recorded_by' => auth()->user()?->name ?? 'Système',
                        'notes' => "Règlement des frais d'inscription du matricule {$student->matricule}",
                    ]);
                }
                $surplusPayment = max(0.0, (float) ($paidNow - $inscriptAmount));
            }

            // 6. Generate Tuition in 3 mandatory tranches (50%, 35%, 15%)
            $this->generateTuitionTranches(
                paymentService: $paymentService,
                enrollment: $enrollment,
                student: $student,
                activeYear: $activeYear,
                surplusPayment: $surplusPayment > 0 ? $surplusPayment : null,
                paymentMethod: $paymentMethod,
                paymentReference: $paymentReference
            );

            return $enrollment;
        });
    }

    /**
     * Re-enroll an existing student into a new class for the active academic year.
     */
    public function reEnrollStudent(
        Student $student,
        int $classId,
        bool $isRepeater = false,
        ?string $notes = null,
        string $paymentMethod = 'ESPECES',
        ?float $customAmountPaid = null,
        ?string $paymentReference = null
    ): Enrollment {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            throw new \Exception("Aucune année scolaire active n'a été définie.");
        }

        $existing = Enrollment::where('student_id', $student->id)
            ->where('academic_year_id', $activeYear->id)
            ->first();

        if ($existing) {
            throw new \Exception("Cet élève est déjà inscrit pour l'année scolaire {$activeYear->name}.");
        }

        return DB::transaction(function () use ($student, $classId, $activeYear, $isRepeater, $notes, $paymentMethod, $customAmountPaid, $paymentReference) {
            $enrollmentNumber = StudentMatriculeGenerator::generateEnrollmentNumber($activeYear->name);

            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'class_id' => $classId,
                'academic_year_id' => $activeYear->id,
                'enrollment_number' => $enrollmentNumber,
                'type' => 'REINSCRIPTION',
                'status' => 'VALIDE',
                'is_repeater' => $isRepeater,
                'enrollment_date' => now()->toDateString(),
                'notes' => $notes,
            ]);

            // Inscription fee & Scolarité tranches
            $paymentService = app(PaymentService::class);
            $inscriptFeeType = FeeType::firstOrCreate(
                ['code' => 'INSCRIPT'],
                ['name' => "Frais d'inscription", 'is_recurring' => false, 'is_active' => true]
            );

            $inscriptAmount = $paymentService->resolveFeeAmount($inscriptFeeType, $enrollment) ?? 0;

            $inscriptInvoice = Invoice::create([
                'invoice_number' => $paymentService->generateInvoiceNumber(),
                'student_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'academic_year_id' => $activeYear->id,
                'fee_type_id' => $inscriptFeeType->id,
                'amount_due' => $inscriptAmount,
                'amount_paid' => 0,
                'status' => 'IMPAYEE',
                'due_date' => now()->toDateString(),
                'notes' => "Frais de réinscription réglés lors de l'inscription.",
            ]);

            $paidNow = $customAmountPaid !== null ? $customAmountPaid : $inscriptAmount;
            $surplusPayment = 0.0;

            if ($paidNow > 0) {
                $amountToInscript = min($paidNow, $inscriptAmount);
                if ($amountToInscript > 0) {
                    $paymentService->recordPayment($inscriptInvoice, [
                        'amount' => $amountToInscript,
                        'payment_method' => $paymentMethod,
                        'payment_date' => now()->toDateString(),
                        'reference' => $paymentReference ?? 'RECU-REINS-'.strtoupper(substr(md5(uniqid()), 0, 6)),
                        'recorded_by' => auth()->user()?->name ?? 'Système',
                    ]);
                }
                $surplusPayment = max(0.0, (float) ($paidNow - $inscriptAmount));
            }

            // Generate Tuition in 3 mandatory tranches (50%, 35%, 15%)
            $this->generateTuitionTranches(
                paymentService: $paymentService,
                enrollment: $enrollment,
                student: $student,
                activeYear: $activeYear,
                surplusPayment: $surplusPayment > 0 ? $surplusPayment : null,
                paymentMethod: $paymentMethod,
                paymentReference: $paymentReference
            );

            return $enrollment;
        });
    }

    /**
     * Generate tuition fee invoices broken into 3 mandatory tranches:
     * - Tranche 1 (50%): Due immediately upon enrollment
     * - Tranche 2 (35%): Due before January 1st
     * - Tranche 3 (15%): Due before March 1st
     *
     * Also automatically applies any surplus payment from enrollment to Tranche 1 (and cascading to T2, T3).
     */
    private function generateTuitionTranches(
        PaymentService $paymentService,
        Enrollment $enrollment,
        Student $student,
        AcademicYear $activeYear,
        ?float $surplusPayment = null,
        string $paymentMethod = 'ESPECES',
        ?string $paymentReference = null
    ): void {
        $scolariteFeeType = FeeType::firstOrCreate(
            ['code' => 'SCOLARITE'],
            ['name' => 'Frais de scolarité', 'is_recurring' => true, 'is_active' => true]
        );

        $totalTuition = $paymentService->resolveFeeAmount($scolariteFeeType, $enrollment) ?? 25000;

        if ($totalTuition <= 0) {
            return;
        }

        // Calculate 3 Tranches: 50%, 35%, 15% (with exact balance matching total)
        $t1Amount = round($totalTuition * 0.50);
        $t2Amount = round($totalTuition * 0.35);
        $t3Amount = $totalTuition - $t1Amount - $t2Amount;

        // Calculate Target Due Dates
        $targetYear = $activeYear->end_date
            ? Carbon::parse($activeYear->end_date)->year
            : ((now()->month >= 7) ? now()->year + 1 : now()->year);

        $tranches = [
            [
                'code' => 'SCOLARITE_T1',
                'name' => 'Scolarité — 1ère Tranche (50%)',
                'amount' => $t1Amount,
                'due_date' => $enrollment->enrollment_date ? Carbon::parse($enrollment->enrollment_date)->toDateString() : now()->toDateString(),
                'notes' => '1ère Tranche de scolarité (50%) exigible à l\'inscription.',
            ],
            [
                'code' => 'SCOLARITE_T2',
                'name' => 'Scolarité — 2ème Tranche (35%)',
                'amount' => $t2Amount,
                'due_date' => "{$targetYear}-01-01",
                'notes' => '2ème Tranche de scolarité (35%) exigible avant le 1er janvier.',
            ],
            [
                'code' => 'SCOLARITE_T3',
                'name' => 'Scolarité — 3ème Tranche (15%)',
                'amount' => $t3Amount,
                'due_date' => "{$targetYear}-03-01",
                'notes' => '3ème Tranche de scolarité (15%) exigible avant le 1er mars.',
            ],
        ];

        $remainingSurplus = $surplusPayment ?? 0.0;

        foreach ($tranches as $tranche) {
            $feeType = FeeType::firstOrCreate(
                ['code' => $tranche['code']],
                ['name' => $tranche['name'], 'is_recurring' => true, 'is_active' => true]
            );

            // Check if level/cycle specific structure exists for this tranche or use calculated ratio
            $trancheAmount = $paymentService->resolveFeeAmount($feeType, $enrollment) ?? $tranche['amount'];

            $invoice = Invoice::firstOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'fee_type_id' => $feeType->id,
                ],
                [
                    'invoice_number' => $paymentService->generateInvoiceNumber(),
                    'student_id' => $student->id,
                    'academic_year_id' => $activeYear->id,
                    'amount_due' => $trancheAmount,
                    'amount_paid' => 0,
                    'status' => 'IMPAYEE',
                    'due_date' => $tranche['due_date'],
                    'notes' => $tranche['notes'],
                ]
            );

            // Apply any surplus payment to this tranche
            if ($remainingSurplus > 0 && $invoice->amount_remaining > 0) {
                $payAmount = min($remainingSurplus, (float) $invoice->amount_remaining);
                $paymentService->recordPayment($invoice, [
                    'amount' => $payAmount,
                    'payment_method' => $paymentMethod,
                    'payment_date' => now()->toDateString(),
                    'reference' => $paymentReference ?? 'RECU-SCOL-'.strtoupper(substr(md5(uniqid()), 0, 6)),
                    'recorded_by' => auth()->user()?->name ?? 'Système',
                    'notes' => "Règlement partiel/total de la {$tranche['name']} lors de l'inscription.",
                ]);
                $remainingSurplus -= $payAmount;
            }
        }
    }
}
