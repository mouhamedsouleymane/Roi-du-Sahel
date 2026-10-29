<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Period;
use App\Services\ReportCardService;
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

        $trimesterPeriod = Period::where('academic_year_id', $activeYear->id)
            ->where('order', 1)
            ->where('type', 'TRIMESTRE')
            ->first();

        $semesterPeriod = Period::where('academic_year_id', $activeYear->id)
            ->where('order', 1)
            ->where('type', 'SEMESTRE')
            ->first();

        /** @var ReportCardService $reportCardService */
        $reportCardService = app(ReportCardService::class);

        // Grouper les inscriptions par classe
        $enrollments = Enrollment::where('academic_year_id', $activeYear->id)
            ->where('status', 'VALIDE')
            ->with(['student', 'schoolClass.level.cycle'])
            ->get()
            ->groupBy('class_id');

        foreach ($enrollments as $classId => $classEnrollments) {
            $schoolClass = $classEnrollments->first()?->schoolClass;
            $isSemester = $schoolClass?->isSemesterBased() ?? false;
            $period = $isSemester ? $semesterPeriod : $trimesterPeriod;

            if (! $period) {
                continue;
            }

            $reportCardService->generateForClass($classId, $period);
            $reportCardService->publishForClass($classId, $period);
        }
    }
}
