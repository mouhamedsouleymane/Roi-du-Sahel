<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\TeacherAssignment;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
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

        $assignments = TeacherAssignment::with(['teacher', 'schoolClass', 'subject'])
            ->where('academic_year_id', $activeYear->id)
            ->get();

        $days = ['LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI'];
        $index = 0;

        foreach ($assignments as $asn) {
            $day = $days[$index % count($days)];

            Schedule::firstOrCreate([
                'academic_year_id' => $activeYear->id,
                'class_id' => $asn->class_id,
                'day_of_week' => $day,
                'start_time' => '08:00:00',
            ], [
                'subject_id' => $asn->subject_id,
                'teacher_id' => $asn->teacher_id,
                'end_time' => '10:00:00',
                'room_number' => $asn->schoolClass->room_number ?? 'Salle 1',
            ]);

            $index++;
        }
    }
}
