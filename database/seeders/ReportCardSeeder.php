<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Period;
use App\Models\ReportCard;
use Illuminate\Database\Seeder;

class ReportCardSeeder extends Seeder
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

        $period = Period::where('academic_year_id', $activeYear->id)
            ->where('order', 1)
            ->where('type', 'TRIMESTRE')
            ->first();

        if (! $period) {
            return;
        }

        // Grouper les inscriptions par classe
        $enrollments = Enrollment::where('academic_year_id', $activeYear->id)
            ->where('status', 'VALIDE')
            ->with('student')
            ->with('schoolClass')
            ->get()
            ->groupBy('class_id');

        foreach ($enrollments as $classId => $classEnrollments) {
            $studentsAverages = [];

            foreach ($classEnrollments as $enrollment) {
                // Calculer la moyenne générale à partir des notes
                $grades = Grade::where('student_id', $enrollment->student_id)
                    ->whereHas('evaluation', function ($q) use ($period, $classId) {
                        $q->where('period_id', $period->id)->where('class_id', $classId);
                    })
                    ->with('evaluation')
                    ->get();

                $totalPoints = 0;
                $totalCoefficients = 0;

                foreach ($grades as $grade) {
                    if ($grade->is_absent || $grade->score === null) {
                        continue;
                    }

                    $normScore = $grade->normalized_score ?? 0;
                    $coeff = (float) $grade->evaluation->coefficient;

                    $totalPoints += $normScore * $coeff;
                    $totalCoefficients += $coeff;
                }

                $average = $totalCoefficients > 0 ? round($totalPoints / $totalCoefficients, 2) : 0;

                $studentsAverages[$enrollment->student_id] = $average;
            }

            // Trier par moyenne décroissante pour le classement
            arsort($studentsAverages);
            $totalStudents = count($studentsAverages);
            $rank = 1;

            foreach ($studentsAverages as $studentId => $average) {
                ReportCard::firstOrCreate(
                    [
                        'student_id' => $studentId,
                        'period_id' => $period->id,
                    ],
                    [
                        'class_id' => $classId,
                        'general_average' => $average,
                        'rank' => $rank,
                        'total_students' => $totalStudents,
                        'appreciation' => $this->getAppreciation($average),
                        'is_published' => true,
                    ]
                );

                $rank++;
            }
        }
    }

    /**
     * Rétourne l'appréciation selon la moyenne.
     */
    private function getAppreciation(float $average): string
    {
        return match (true) {
            $average >= 18 => 'Excellent travail',
            $average >= 16 => 'Très bien, continuez ainsi',
            $average >= 14 => 'Bien, des progrès notables',
            $average >= 12 => 'Assez bien, poursuivez vos efforts',
            $average >= 10 => 'Passable, peut mieux faire',
            default => 'Insuffisant, un travail sérieux est nécessaire',
        };
    }
}
