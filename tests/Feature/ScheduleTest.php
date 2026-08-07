<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Level;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/schedules');

        $response->assertStatus(200);
        $response->assertSee('Emploi du Temps');
    }

    public function test_schedule_slot_can_be_created(): void
    {
        $user = User::factory()->create();

        $year = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A']);
        $subject = Subject::create(['code' => 'MATH', 'name' => 'Mathématiques']);

        $teacherUser = User::factory()->create();
        $teacher = Teacher::create(['user_id' => $teacherUser->id, 'matricule' => 'ENS-001', 'speciality' => 'Maths']);

        $response = $this->actingAs($user)->post('/schedules', [
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 'LUNDI',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'room_number' => 'Salle 12',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('schedules', [
            'class_id' => $class->id,
            'day_of_week' => 'LUNDI',
            'start_time' => '08:00:00',
        ]);
    }
}
