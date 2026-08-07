<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignments_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/assignments');

        $response->assertStatus(200);
        $response->assertSee('Affectation des Enseignants');
    }

    public function test_teacher_can_be_assigned_to_class_and_subject(): void
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

        $response = $this->actingAs($user)->post('/assignments', [
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);

        $response->assertRedirect('/assignments');
        $this->assertDatabaseHas('teacher_assignments', [
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);
    }
}
