<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

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
            $guardianData = [
                'first_name' => $guardianData['guardian_first_name'] ?? $guardianData['first_name'] ?? null,
                'last_name' => $guardianData['guardian_last_name'] ?? $guardianData['last_name'] ?? null,
                'relationship' => $guardianData['relationship'] ?? 'PERE',
                'profession' => $guardianData['profession'] ?? null,
                'phone_primary' => $guardianData['phone_primary'],
                'phone_secondary' => $guardianData['phone_secondary'] ?? null,
                'email' => $guardianData['email'] ?? null,
                'address' => $guardianData['address'] ?? null,
                'city' => $guardianData['city'] ?? 'Niamey',
            ];

            $guardian = Guardian::firstOrCreate(
                ['phone_primary' => $guardianData['phone_primary']],
                $guardianData
            );

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
            $inscriptFeeType = \App\Models\FeeType::firstOrCreate(
                ['code' => 'INSCRIPT'],
                ['name' => "Frais d'inscription", 'is_recurring' => false, 'is_active' => true]
            );

            $inscriptAmount = $paymentService->resolveFeeAmount($inscriptFeeType, $enrollment) ?? 30000;

            $inscriptInvoice = \App\Models\Invoice::create([
                'invoice_number'   => $paymentService->generateInvoiceNumber(),
                'student_id'       => $student->id,
                'enrollment_id'    => $enrollment->id,
                'academic_year_id' => $activeYear->id,
                'fee_type_id'      => $inscriptFeeType->id,
                'amount_due'       => $inscriptAmount,
                'amount_paid'      => 0,
                'status'           => 'IMPAYEE',
                'due_date'         => now()->toDateString(),
                'notes'            => "Frais d'inscription réglés lors de l'inscription.",
            ]);

            $paidNow = $customAmountPaid !== null ? $customAmountPaid : $inscriptAmount;
            if ($paidNow > 0) {
                $paymentService->recordPayment($inscriptInvoice, [
                    'amount'         => min($paidNow, $inscriptAmount),
                    'payment_method' => $paymentMethod,
                    'payment_date'   => now()->toDateString(),
                    'reference'      => $paymentReference ?? 'RECU-INCS-' . strtoupper(substr(md5(uniqid()), 0, 6)),
                    'recorded_by'    => auth()->user()?->name ?? 'Système',
                    'notes'          => "Règlement des frais d'inscription du matricule {$student->matricule}",
                ]);
            }

            // 6. Create Tuition (Scolarité) Invoice for tracking in Factures & Paiements
            $scolariteFeeType = \App\Models\FeeType::firstOrCreate(
                ['code' => 'SCOLARITE'],
                ['name' => 'Frais de scolarité', 'is_recurring' => true, 'is_active' => true]
            );

            $scolariteAmount = $paymentService->resolveFeeAmount($scolariteFeeType, $enrollment) ?? 25000;
            if ($scolariteAmount > 0) {
                \App\Models\Invoice::firstOrCreate(
                    [
                        'enrollment_id' => $enrollment->id,
                        'fee_type_id'   => $scolariteFeeType->id,
                    ],
                    [
                        'invoice_number'   => $paymentService->generateInvoiceNumber(),
                        'student_id'       => $student->id,
                        'academic_year_id' => $activeYear->id,
                        'amount_due'       => $scolariteAmount,
                        'amount_paid'      => 0,
                        'status'           => 'IMPAYEE',
                        'due_date'         => now()->addMonth()->toDateString(),
                        'notes'            => "Facture annuelle de scolarité.",
                    ]
                );
            }

            return $enrollment;
        });
    }

    /**
     * Re-enroll an existing student into a new class for the active academic year.
     */
    public function reEnrollStudent(Student $student, int $classId, bool $isRepeater = false, ?string $notes = null, string $paymentMethod = 'ESPECES', ?float $customAmountPaid = null): Enrollment
    {
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

        return DB::transaction(function () use ($student, $classId, $activeYear, $isRepeater, $notes, $paymentMethod, $customAmountPaid) {
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

            // Inscription fee & Scolarité
            $paymentService = app(PaymentService::class);
            $inscriptFeeType = \App\Models\FeeType::firstOrCreate(
                ['code' => 'INSCRIPT'],
                ['name' => "Frais d'inscription", 'is_recurring' => false, 'is_active' => true]
            );

            $inscriptAmount = $paymentService->resolveFeeAmount($inscriptFeeType, $enrollment) ?? 30000;

            $inscriptInvoice = \App\Models\Invoice::create([
                'invoice_number'   => $paymentService->generateInvoiceNumber(),
                'student_id'       => $student->id,
                'enrollment_id'    => $enrollment->id,
                'academic_year_id' => $activeYear->id,
                'fee_type_id'      => $inscriptFeeType->id,
                'amount_due'       => $inscriptAmount,
                'amount_paid'      => 0,
                'status'           => 'IMPAYEE',
                'due_date'         => now()->toDateString(),
            ]);

            $paidNow = $customAmountPaid !== null ? $customAmountPaid : $inscriptAmount;
            if ($paidNow > 0) {
                $paymentService->recordPayment($inscriptInvoice, [
                    'amount'         => min($paidNow, $inscriptAmount),
                    'payment_method' => $paymentMethod,
                    'payment_date'   => now()->toDateString(),
                    'reference'      => 'RECU-REINS-' . strtoupper(substr(md5(uniqid()), 0, 6)),
                    'recorded_by'    => auth()->user()?->name ?? 'Système',
                ]);
            }

            return $enrollment;
        });
    }
}
