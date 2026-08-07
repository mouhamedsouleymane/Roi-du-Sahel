<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Evaluation;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluationTest extends TestCase
{
    use RefreshDatabase;

    private function createBaseData(): array
    {
        $year = AcademicYear::create([
            'name' => '2025-2026', 'start_date' => '2025-09-15',
            'end_date' => '2026-06-30', 'is_active' => true,
        ]);
        $period = Period::create([
            'academic_year_id' => $year->id,
            'name' => 'Trimestre 1', 'type' => 'TRIMESTRE', 'order' => 1,
            'start_date' => '2025-09-15', 'end_date' => '2025-12-14',
        ]);
        $cycle   = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level   = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $class   = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A']);
        $subject = Subject::create(['code' => 'MATH', 'name' => 'Mathématiques']);

        $tUser   = User::factory()->create();
        $teacher = Teacher::create(['user_id' => $tUser->id, 'matricule' => 'ENS-001', 'speciality' => 'Maths']);

        return compact('year', 'period', 'class', 'subject', 'teacher');
    }

    public function test_evaluations_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/evaluations');

        $response->assertStatus(200);
        $response->assertSee('Évaluations');
    }

    public function test_evaluation_can_be_created(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        $response = $this->actingAs($user)->post('/evaluations', [
            'period_id'       => $data['period']->id,
            'class_id'        => $data['class']->id,
            'subject_id'      => $data['subject']->id,
            'teacher_id'      => $data['teacher']->id,
            'title'           => 'DS1 Maths',
            'type'            => 'DEVOIR',
            'max_score'       => 20,
            'coefficient'     => 1,
            'evaluation_date' => '2025-10-15',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('evaluations', ['title' => 'DS1 Maths']);
    }

    public function test_grades_can_be_saved_for_evaluation(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        $evaluation = Evaluation::create([
            'period_id'       => $data['period']->id,
            'class_id'        => $data['class']->id,
            'subject_id'      => $data['subject']->id,
            'teacher_id'      => $data['teacher']->id,
            'title'           => 'DS1 Maths',
            'type'            => 'DEVOIR',
            'max_score'       => 20,
            'coefficient'     => 1,
            'evaluation_date' => '2025-10-15',
        ]);

        $student = Student::create([
            'matricule' => 'RS-2025-0001', 'first_name' => 'Ali', 'last_name' => 'Oumarou',
            'birth_date' => '2010-01-01', 'gender' => 'M',
        ]);

        $response = $this->actingAs($user)->post("/evaluations/{$evaluation->id}/grades", [
            'grades' => [
                ['student_id' => $student->id, 'score' => 14.50, 'is_absent' => false],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('grades', [
            'evaluation_id' => $evaluation->id,
            'student_id'    => $student->id,
            'score'         => 14.50,
        ]);
    }
}
