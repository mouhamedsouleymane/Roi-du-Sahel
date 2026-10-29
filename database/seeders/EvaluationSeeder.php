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

        $trimesterPeriod = Period::where('academic_year_id', $activeYear->id)
            ->where('order', 1)
            ->where('type', 'TRIMESTRE')
            ->first();

        $semesterPeriod = Period::where('academic_year_id', $activeYear->id)
            ->where('order', 1)
            ->where('type', 'SEMESTRE')
            ->first();

        $assignments = TeacherAssignment::with(['teacher', 'schoolClass.level.cycle', 'subject'])
            ->where('academic_year_id', $activeYear->id)
            ->get();

        foreach ($assignments as $asn) {
            if (! $asn->schoolClass || ! $asn->subject || ! $asn->teacher) {
                continue;
            }

            $isSemester = $asn->schoolClass->isSemesterBased();
            $period = $isSemester ? $semesterPeriod : $trimesterPeriod;

            if (! $period) {
                continue;
            }

            $coeff = ClassSubjectCoefficient::where('level_id', $asn->schoolClass->level_id)
                ->where('subject_id', $asn->subject_id)
                ->first();

            $coefficientValue = $coeff?->coefficient ?? 1.00;

            // 1. Devoir N°1
            Evaluation::firstOrCreate(
                [
                    'period_id' => $period->id,
                    'class_id' => $asn->class_id,
                    'subject_id' => $asn->subject_id,
                    'type' => 'DEVOIR',
                ],
                [
                    'teacher_id' => $asn->teacher_id,
                    'title' => "Devoir 1 - {$asn->subject->name}",
                    'max_score' => 20.00,
                    'coefficient' => $coefficientValue,
                    'evaluation_date' => '2025-10-15',
                ]
            );

            // 2. Composition N°1
            Evaluation::firstOrCreate(
                [
                    'period_id' => $period->id,
                    'class_id' => $asn->class_id,
                    'subject_id' => $asn->subject_id,
                    'type' => 'COMPOSITION',
                ],
                [
                    'teacher_id' => $asn->teacher_id,
                    'title' => "Composition 1 - {$asn->subject->name}",
                    'max_score' => 20.00,
                    'coefficient' => $coefficientValue,
                    'evaluation_date' => '2025-11-20',
                ]
            );
        }
    }
}
