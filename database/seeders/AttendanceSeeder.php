<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\TeacherAssignment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
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

        $classes = SchoolClass::where('academic_year_id', $activeYear->id)->get();

        // Générer les présences sur 10 jours ouvrés
        $dates = [];
        $date = Carbon::create(2025, 10, 1);
        while (count($dates) < 10) {
            if (! $date->isWeekend()) {
                $dates[] = $date->copy();
            }
            $date->addDay();
        }

        foreach ($classes as $class) {
            $enrollments = Enrollment::where('class_id', $class->id)
                ->where('academic_year_id', $activeYear->id)
                ->where('status', 'VALIDE')
                ->get();

            $assignments = TeacherAssignment::where('class_id', $class->id)
                ->where('academic_year_id', $activeYear->id)
                ->get();

            foreach ($dates as $day) {
                foreach ($assignments as $asn) {
                    foreach ($enrollments as $enrollment) {
                        // 85% présents, 8% absents, 5% retards, 2% excusés
                        $roll = rand(1, 100);
                        $status = match (true) {
                            $roll <= 85 => 'PRESENT',
                            $roll <= 93 => 'ABSENT',
                            $roll <= 98 => 'RETARD',
                            default => 'EXCUSE',
                        };

                        Attendance::firstOrCreate(
                            [
                                'student_id' => $enrollment->student_id,
                                'class_id' => $class->id,
                                'subject_id' => $asn->subject_id,
                                'attendance_date' => $day->toDateString(),
                                'session' => 'JOURNEE',
                            ],
                            [
                                'teacher_id' => $asn->teacher_id,
                                'academic_year_id' => $activeYear->id,
                                'status' => $status,
                                'reason' => $status === 'ABSENT' ? 'Absence non justifiée' : ($status === 'RETARD' ? 'Arrivée tardive' : null),
                                'recorded_by' => 'Surveillant Général',
                            ]
                        );
                    }
                }
            }
        }
    }
}
