<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class EnrollmentService
{
    /**
     * Complete enrollment process for a new student with guardian.
     */
    public function enrollNewStudent(array $studentData, array $guardianData, int $classId, string $type = 'NOUVEAU', bool $isRepeater = false, ?string $notes = null): Enrollment
    {
        return DB::transaction(function () use ($studentData, $guardianData, $classId, $type, $isRepeater, $notes) {
            $activeYear = AcademicYear::getActive();
            if (! $activeYear) {
                throw new \Exception("Aucune année scolaire active n'a été définie.");
            }

            // 1. Generate Matricule & Create Student
            $studentData['matricule'] = StudentMatriculeGenerator::generate($activeYear->name);
            $student = Student::create($studentData);

            // 2. Find or Create Guardian
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

            return Enrollment::create([
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
        });
    }

    /**
     * Re-enroll an existing student into a new class for the active academic year.
     */
    public function reEnrollStudent(Student $student, int $classId, bool $isRepeater = false, ?string $notes = null): Enrollment
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

        $enrollmentNumber = StudentMatriculeGenerator::generateEnrollmentNumber($activeYear->name);

        return Enrollment::create([
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
    }
}
