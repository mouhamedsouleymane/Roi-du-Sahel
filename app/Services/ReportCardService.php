<?php

namespace App\Services;

use App\Models\ClassSubjectCoefficient;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\Grade;
use App\Models\Period;
use App\Models\ReportCard;
use App\Models\Student;
use Illuminate\Support\Collection;

class ReportCardService
{
    /**
     * Calculate average for a student in a given subject for a period,
     * taking into account each evaluation's individual coefficient.
     *
     * @return float|null  Null if no grades exist
     */
    public function calculateSubjectAverage(Student $student, int $subjectId, Period $period): ?float
    {
        $evaluations = Evaluation::where('period_id', $period->id)
            ->where('subject_id', $subjectId)
            ->with(['grades' => function ($q) use ($student) {
                $q->where('student_id', $student->id);
            }])
            ->get();

        $weightedSum = 0.0;
        $totalWeight = 0.0;

        foreach ($evaluations as $eval) {
            /** @var Grade|null $grade */
            $grade = $eval->grades->first();

            if (! $grade || $grade->is_absent || $grade->score === null) {
                continue;
            }

            $normalized  = ($grade->score / $eval->max_score) * 20;
            $coefficient = (float) $eval->coefficient;

            $weightedSum += $normalized * $coefficient;
            $totalWeight += $coefficient;
        }

        if ($totalWeight === 0.0) {
            return null;
        }

        return round($weightedSum / $totalWeight, 2);
    }

    /**
     * Calculate general average for a student in a period,
     * weighted by subject coefficients from the class configuration.
     *
     * @return array{average: float, details: array}
     */
    public function calculateGeneralAverage(Student $student, Period $period, int $classId): array
    {
        $subjectCoefficients = ClassSubjectCoefficient::where('class_id', $classId)
            ->with('subject')
            ->get()
            ->keyBy('subject_id');

        $weightedSum   = 0.0;
        $totalWeight   = 0.0;
        $subjectDetail = [];

        foreach ($subjectCoefficients as $subjectId => $coeff) {
            $avg = $this->calculateSubjectAverage($student, $subjectId, $period);

            if ($avg === null) {
                continue;
            }

            $subjectWeight = (float) $coeff->coefficient;

            $weightedSum += $avg * $subjectWeight;
            $totalWeight += $subjectWeight;

            $subjectDetail[] = [
                'subject'     => $coeff->subject->name,
                'average'     => $avg,
                'coefficient' => $subjectWeight,
            ];
        }

        $generalAvg = $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : 0.0;

        return [
            'average' => $generalAvg,
            'details' => $subjectDetail,
        ];
    }

    /**
     * Generate and persist a report card for every enrolled student in a class for a given period.
     */
    public function generateForClass(int $classId, Period $period): int
    {
        $enrollments = Enrollment::where('class_id', $classId)
            ->where('academic_year_id', $period->academic_year_id)
            ->where('status', 'VALIDE')
            ->with('student')
            ->get();

        $totalStudents = $enrollments->count();
        $averages      = [];

        foreach ($enrollments as $enrollment) {
            $result = $this->calculateGeneralAverage($enrollment->student, $period, $classId);

            $averages[$enrollment->student_id] = $result['average'];
        }

        // Sort descending to assign ranks
        arsort($averages);
        $rank = 1;

        foreach ($averages as $studentId => $avg) {
            ReportCard::updateOrCreate(
                ['student_id' => $studentId, 'period_id' => $period->id],
                [
                    'class_id'        => $classId,
                    'general_average' => $avg,
                    'rank'            => $rank,
                    'total_students'  => $totalStudents,
                    'is_published'    => false,
                ]
            );
            $rank++;
        }

        return $totalStudents;
    }

    /**
     * Publish all report cards for a given period and class.
     */
    public function publishForClass(int $classId, Period $period): int
    {
        return ReportCard::where('class_id', $classId)
            ->where('period_id', $period->id)
            ->update(['is_published' => true]);
    }
}
