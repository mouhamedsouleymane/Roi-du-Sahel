<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassSubjectCoefficient;
use App\Models\Evaluation;
use App\Models\Period;
use App\Models\TeacherAssignment;
use Illuminate\Database\Seeder;

class EvaluationSeeder extends Seeder
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

        $assignments = TeacherAssignment::with(['teacher', 'schoolClass', 'subject'])
            ->where('academic_year_id', $activeYear->id)
            ->get();

        $types = ['DEVOIR', 'COMPOSITION', 'CONTROLE'];

        $index = 0;
        foreach ($assignments as $asn) {
            $coeff = ClassSubjectCoefficient::where('level_id', $asn->schoolClass->level_id)
                ->where('subject_id', $asn->subject_id)
                ->first();

            $type = $types[$index % count($types)];

            Evaluation::firstOrCreate(
                [
                    'period_id' => $period->id,
                    'class_id' => $asn->class_id,
                    'subject_id' => $asn->subject_id,
                    'title' => "{$type} - {$asn->subject->name}",
                ],
                [
                    'teacher_id' => $asn->teacher_id,
                    'type' => $type,
                    'max_score' => 20.00,
                    'coefficient' => $coeff?->coefficient ?? 1.00,
                    'evaluation_date' => '2025-10-15',
                ]
            );

            $index++;
        }
    }
}
