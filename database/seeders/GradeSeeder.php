<?php

namespace Database\Seeders;

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
        $evaluations = Evaluation::all();

        foreach ($evaluations as $evaluation) {
            // Récupérer les élèves inscrits dans la classe de cette évaluation
            $enrollments = Enrollment::where('class_id', $evaluation->class_id)
                ->where('status', 'VALIDE')
                ->get();

            foreach ($enrollments as $enrollment) {
                // 10% d'absences, sinon note aléatoire entre 6 et 20
                $isAbsent = rand(1, 10) === 1;

                Grade::firstOrCreate(
                    [
                        'evaluation_id' => $evaluation->id,
                        'student_id' => $enrollment->student_id,
                    ],
                    [
                        'score' => $isAbsent ? null : rand(60, 200) / 10,
                        'is_absent' => $isAbsent,
                        'comment' => $isAbsent ? 'Absent(e) à l\'évaluation' : null,
                    ]
                );
            }
        }
    }
}
