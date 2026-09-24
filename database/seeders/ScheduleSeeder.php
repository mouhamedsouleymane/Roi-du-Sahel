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
        $timeSlots = [
            ['start' => '08:00:00', 'end' => '09:00:00'],
            ['start' => '09:00:00', 'end' => '10:00:00'],
            ['start' => '10:00:00', 'end' => '11:00:00'],
            ['start' => '11:00:00', 'end' => '12:00:00'],
            ['start' => '14:00:00', 'end' => '15:00:00'],
            ['start' => '15:00:00', 'end' => '16:00:00'],
        ];

        // Grouper les affectations par classe
        $byClass = $assignments->groupBy('class_id');

        foreach ($byClass as $classId => $classAssignments) {
            $dayIndex = 0;
            $slotIndex = 0;

            foreach ($classAssignments as $asn) {
                $day = $days[$dayIndex % count($days)];
                $slot = $timeSlots[$slotIndex % count($timeSlots)];

                Schedule::firstOrCreate(
                    [
                        'academic_year_id' => $activeYear->id,
                        'class_id' => $classId,
                        'day_of_week' => $day,
                        'start_time' => $slot['start'],
                    ],
                    [
                        'subject_id' => $asn->subject_id,
                        'teacher_id' => $asn->teacher_id,
                        'end_time' => $slot['end'],
                        'room_number' => $asn->schoolClass->room_number ?? 'Salle '.rand(1, 20),
                    ]
                );

                $dayIndex++;
                $slotIndex++;
            }
        }
    }
}
