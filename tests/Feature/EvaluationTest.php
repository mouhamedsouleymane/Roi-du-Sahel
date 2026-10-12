<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
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
        $cycle = Cycle::create(['code' => 'PRIMAIRE', 'name' => 'Primaire']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => 'CM2', 'name' => 'CM2']);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => 'CM2 A']);
        $subject = Subject::create(['code' => 'MATH', 'name' => 'Mathématiques']);

        $tUser = User::factory()->create();
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
            'period_id' => $data['period']->id,
            'class_id' => $data['class']->id,
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'title' => 'DS1 Maths',
            'type' => 'DEVOIR',
            'max_score' => 20,
            'evaluation_date' => '2025-10-15',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('evaluations', ['title' => 'DS1 Maths']);
    }

    public function test_evaluation_creation_fails_when_period_type_mismatches_cycle(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        $semesterPeriod = Period::create([
            'academic_year_id' => $data['year']->id,
            'name' => 'Semestre 1', 'type' => 'SEMESTRE', 'order' => 1,
            'start_date' => '2025-09-15', 'end_date' => '2026-01-31',
        ]);

        // Trying to assign a Semestre period to a Primaire class
        $response = $this->actingAs($user)->post('/evaluations', [
            'period_id' => $semesterPeriod->id,
            'class_id' => $data['class']->id,
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'title' => 'DS1 Invalid Period',
            'type' => 'DEVOIR',
            'max_score' => 20,
            'evaluation_date' => '2025-10-15',
        ]);

        $response->assertSessionHasErrors(['period_id']);
        $this->assertDatabaseMissing('evaluations', ['title' => 'DS1 Invalid Period']);
    }

    public function test_evaluation_creation_succeeds_for_semester_in_college(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        $collegeCycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $collegeLevel = Level::create(['cycle_id' => $collegeCycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $collegeClass = SchoolClass::create(['academic_year_id' => $data['year']->id, 'level_id' => $collegeLevel->id, 'name' => '6ème B']);

        $semesterPeriod = Period::create([
            'academic_year_id' => $data['year']->id,
            'name' => 'Semestre 1', 'type' => 'SEMESTRE', 'order' => 1,
            'start_date' => '2025-09-15', 'end_date' => '2026-01-31',
        ]);

        $response = $this->actingAs($user)->post('/evaluations', [
            'period_id' => $semesterPeriod->id,
            'class_id' => $collegeClass->id,
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'title' => 'DS1 Collège Semestre',
            'type' => 'DEVOIR',
            'max_score' => 20,
            'evaluation_date' => '2025-10-15',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('evaluations', ['title' => 'DS1 Collège Semestre']);
    }

    public function test_grades_can_be_saved_for_evaluation(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        $evaluation = Evaluation::create([
            'period_id' => $data['period']->id,
            'class_id' => $data['class']->id,
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'title' => 'DS1 Maths',
            'type' => 'DEVOIR',
            'max_score' => 20,
            'coefficient' => 1,
            'evaluation_date' => '2025-10-15',
        ]);

        $student = Student::create([
            'matricule' => 'RS-2025-0001', 'first_name' => 'Ali', 'last_name' => 'Oumarou',
            'birth_date' => '2010-01-01', 'gender' => 'M', 'birth_place' => 'Niamey',
        ]);

        $response = $this->actingAs($user)->post("/evaluations/{$evaluation->id}/grades", [
            'grades' => [
                ['student_id' => $student->id, 'score' => 14.50, 'is_absent' => false],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('grades', [
            'evaluation_id' => $evaluation->id,
            'student_id' => $student->id,
            'score' => 14.50,
        ]);
    }

    public function test_evaluations_page_displays_only_class_subjects(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        $otherSubject = Subject::create(['code' => 'HIST', 'name' => 'Histoire-Géographie']);

        TeacherAssignment::create([
            'academic_year_id' => $data['year']->id,
            'class_id' => $data['class']->id,
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
        ]);

        $response = $this->actingAs($user)->get('/evaluations?class_id='.$data['class']->id);

        $response->assertStatus(200);
        $response->assertSee($data['subject']->name);
        $response->assertDontSee($otherSubject->name);
    }

    public function test_evaluation_show_page_renders_with_grades(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        $evaluation = Evaluation::create([
            'period_id' => $data['period']->id,
            'class_id' => $data['class']->id,
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'title' => 'DEVOIR - Mathématiques',
            'type' => 'DEVOIR',
            'max_score' => 20,
            'coefficient' => 1,
            'evaluation_date' => '2025-10-15',
        ]);

        $student = Student::create([
            'matricule' => 'RS-2025-0002', 'first_name' => 'Fatima', 'last_name' => 'Souley',
            'birth_date' => '2010-02-02', 'gender' => 'F', 'birth_place' => 'Niamey',
        ]);

        Grade::create([
            'evaluation_id' => $evaluation->id,
            'student_id' => $student->id,
            'score' => 17.5,
            'is_absent' => false,
        ]);

        $response = $this->actingAs($user)->get("/evaluations/{$evaluation->id}");

        $response->assertStatus(200);
        $response->assertSee('Fatima');
        $response->assertSee('Souley');
        $response->assertSee('17.50');
    }

    public function test_evaluation_grades_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        $evaluation = Evaluation::create([
            'period_id' => $data['period']->id,
            'class_id' => $data['class']->id,
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'title' => 'DEVOIR - Mathématiques',
            'type' => 'DEVOIR',
            'max_score' => 20,
            'coefficient' => 1,
            'evaluation_date' => '2025-10-15',
        ]);

        $student = Student::create([
            'matricule' => 'RS-2025-0003', 'first_name' => 'Moussa', 'last_name' => 'Garba',
            'birth_date' => '2010-03-03', 'gender' => 'M', 'birth_place' => 'Niamey',
        ]);

        Enrollment::create([
            'student_id' => $student->id,
            'class_id' => $data['class']->id,
            'academic_year_id' => $data['year']->id,
            'enrollment_number' => 'INS-2025-0001',
            'enrollment_date' => '2025-09-15',
            'status' => 'VALIDE',
        ]);

        $response = $this->actingAs($user)->get("/evaluations/{$evaluation->id}/grades");

        $response->assertStatus(200);
        $response->assertSee('Saisie de Notes');
        $response->assertSee('Moussa');
        $response->assertSee('Garba');
    }

    public function test_evaluations_index_displays_hierarchical_grouping(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        Evaluation::create([
            'period_id' => $data['period']->id,
            'class_id' => $data['class']->id,
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'title' => 'DS1 Algèbre',
            'type' => 'DEVOIR',
            'max_score' => 20,
            'coefficient' => 1,
            'evaluation_date' => '2025-10-15',
        ]);

        Evaluation::create([
            'period_id' => $data['period']->id,
            'class_id' => $data['class']->id,
            'subject_id' => $data['subject']->id,
            'teacher_id' => $data['teacher']->id,
            'title' => 'DS2 Géométrie',
            'type' => 'DEVOIR',
            'max_score' => 20,
            'coefficient' => 1.5,
            'evaluation_date' => '2025-11-20',
        ]);

        $response = $this->actingAs($user)->get('/evaluations?period_id='.$data['period']->id);

        $response->assertStatus(200);
        $response->assertSee($data['class']->level->name);
        $response->assertSee('Classe : '.$data['class']->name);
        $response->assertSee($data['subject']->name);
        $response->assertSee('DS1 Algèbre');
        $response->assertSee('DS2 Géométrie');
    }
}
