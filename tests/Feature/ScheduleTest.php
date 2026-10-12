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
        $response->assertSee('Emplois du Temps');
    }

    public function test_schedule_page_displays_selected_class_grid(): void
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

        $teacherUser = User::factory()->create(['name' => 'Professeur Alpha']);
        $teacher = Teacher::create(['user_id' => $teacherUser->id, 'matricule' => 'ENS-001', 'speciality' => 'Maths']);

        Schedule::create([
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 'LUNDI',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'room_number' => 'Salle 12',
        ]);

        $response = $this->actingAs($user)->get("/schedules?class_id={$class->id}");

        $response->assertStatus(200);
        $response->assertSee('Mathématiques');
        $response->assertSee('Salle 12');
        $response->assertSee('6ème A');
    }

    public function test_schedule_create_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/schedules/create');

        $response->assertStatus(200);
        $response->assertSee('Nouveau Créneau');
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
            'room_number' => 'Salle 12',
        ]);
    }

    public function test_schedule_slot_can_be_updated(): void
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

        $schedule = Schedule::create([
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 'LUNDI',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'room_number' => 'Salle 1',
        ]);

        $response = $this->actingAs($user)->put("/schedules/{$schedule->id}", [
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 'MARDI',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'room_number' => 'Salle 5',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'day_of_week' => 'MARDI',
            'room_number' => 'Salle 5',
        ]);
    }

    public function test_schedule_slot_can_be_duplicated(): void
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

        $schedule = Schedule::create([
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 'LUNDI',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'room_number' => 'Salle 1',
        ]);

        $response = $this->actingAs($user)->post("/schedules/{$schedule->id}/duplicate", [
            'target_day' => 'JEUDI',
            'start_time' => '14:00',
            'end_time' => '16:00',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('schedules', [
            'class_id' => $class->id,
            'day_of_week' => 'JEUDI',
        ]);
    }

    public function test_schedule_class_can_be_cleared(): void
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

        Schedule::create([
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 'LUNDI',
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);

        $response = $this->actingAs($user)->post('/schedules/clear-class', [
            'class_id' => $class->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('schedules', [
            'class_id' => $class->id,
        ]);
    }

    public function test_schedule_slot_can_be_deleted(): void
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

        $schedule = Schedule::create([
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 'LUNDI',
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);

        $response = $this->actingAs($user)->delete("/schedules/{$schedule->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    public function test_schedule_collision_is_prevented(): void
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
        $classA = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A']);
        $classB = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème B']);
        $subject = Subject::create(['code' => 'MATH', 'name' => 'Mathématiques']);

        $teacherUser = User::factory()->create();
        $teacher = Teacher::create(['user_id' => $teacherUser->id, 'matricule' => 'ENS-001', 'speciality' => 'Maths']);

        // First slot
        $this->actingAs($user)->post('/schedules', [
            'class_id' => $classA->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 'LUNDI',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'room_number' => 'Salle 1',
        ]);

        // Attempt second overlapping slot with same teacher in different class
        $response = $this->actingAs($user)->post('/schedules', [
            'class_id' => $classB->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 'LUNDI',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'room_number' => 'Salle 2',
        ]);

        $response->assertSessionHasErrors('conflict');
        $this->assertDatabaseMissing('schedules', [
            'class_id' => $classB->id,
            'room_number' => 'Salle 2',
        ]);
    }
}
