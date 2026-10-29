<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\Grade;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return;
        }

        $evaluations = Evaluation::whereHas('schoolClass', function ($q) use ($activeYear) {
            $q->where('academic_year_id', $activeYear->id);
        })->get();

        // Pré-charger toutes les inscriptions validées groupées par classe
        $enrollmentsByClass = Enrollment::where('academic_year_id', $activeYear->id)
            ->where('status', 'VALIDE')
            ->with('student')
            ->get()
            ->groupBy('class_id');

        $gradesBatch = [];
        $now = now();

        foreach ($evaluations as $evaluation) {
            $enrollments = $enrollmentsByClass->get($evaluation->class_id, collect());

            foreach ($enrollments as $enrollment) {
                $student = $enrollment->student;
                if (! $student) {
                    continue;
                }

                // Profil académique cohérent par élève (basé sur son matricule ou ID)
                $studentSeed = abs(crc32($student->matricule ?: (string) $student->id)) % 100;

                // 2.5% de chances d'absence justifiée
                $isAbsent = (abs(crc32($student->id.'-'.$evaluation->id)) % 40) === 0;

                if ($isAbsent) {
                    $score = null;
                    $comment = 'Absent(e) excusé(e)';
                } else {
                    // Profil académique :
                    // 20% d'élèves brillants (14.5 - 19.5)
                    // 65% d'élèves moyens / réguliers (10.0 - 14.0)
                    // 15% d'élèves en difficulté (6.5 - 9.75)
                    if ($studentSeed >= 80) {
                        $baseScore = 14.5 + (($studentSeed - 80) / 20) * 4.5;
                    } elseif ($studentSeed >= 15) {
                        $baseScore = 10.0 + (($studentSeed - 15) / 65) * 4.0;
                    } else {
                        $baseScore = 6.5 + ($studentSeed / 15) * 3.25;
                    }

                    // Petite variation par matière et évaluation (+/- 1.5 pt)
                    $variationSeed = (abs(crc32($student->id.'sub'.$evaluation->subject_id.'eval'.$evaluation->id)) % 31) - 15;
                    $variation = $variationSeed / 10.0;

                    $finalScore = $baseScore + $variation;
                    // Borner entre 5.00 et 20.00
                    $finalScore = max(5.0, min(20.0, $finalScore));
                    // Arrondir au quart de point (0.25)
                    $score = round($finalScore * 4) / 4;

                    $comment = match (true) {
                        $score >= 17 => 'Excellent travail',
                        $score >= 14 => 'Très bon travail',
                        $score >= 12 => 'Bon travail',
                        $score >= 10 => 'Travail satisfaisant',
                        default => 'Doit persévérer et fournir plus d\'efforts',
                    };
                }

                $gradesBatch[] = [
                    'evaluation_id' => $evaluation->id,
                    'student_id' => $student->id,
                    'score' => $score,
                    'is_absent' => $isAbsent ? 1 : 0,
                    'comment' => $comment,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // Upsert par paquet de 500 pour une vitesse maximale
                if (count($gradesBatch) >= 500) {
                    Grade::upsert(
                        $gradesBatch,
                        ['evaluation_id', 'student_id'],
                        ['score', 'is_absent', 'comment', 'updated_at']
                    );
                    $gradesBatch = [];
                }
            }
        }

        if (! empty($gradesBatch)) {
            Grade::upsert(
                $gradesBatch,
                ['evaluation_id', 'student_id'],
                ['score', 'is_absent', 'comment', 'updated_at']
            );
        }
    }
}
