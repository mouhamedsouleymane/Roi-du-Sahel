<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSubjectCoefficient;
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

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    private function createActiveYear(): AcademicYear
    {
        return AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);
    }

    private function createCycleWithClass(AcademicYear $year, string $cycleCode, string $cycleName): array
    {
        $cycle = Cycle::create(['code' => $cycleCode, 'name' => $cycleName, 'is_active' => true]);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => 'L1', 'name' => 'Niveau 1']);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => 'Classe A']);

        return [$cycle, $level, $class];
    }

    private function createTeacher(int $cycleId, bool $isPolyvalent = false, string $suffix = '001'): Teacher
    {
        $user = User::factory()->create();

        return Teacher::create([
            'user_id' => $user->id,
            'cycle_id' => $cycleId,
            'matricule' => "ENS-{$suffix}",
            'speciality' => 'Générale',
            'is_polyvalent' => $isPolyvalent,
        ]);
    }

    private function createSubject(string $code = 'MAT'): Subject
    {
        return Subject::create(['code' => $code, 'name' => "Matière {$code}"]);
    }

    // ---------------------------------------------------------------------------
    // Index
    // ---------------------------------------------------------------------------

    public function test_assignments_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/assignments');

        $response->assertStatus(200);
        $response->assertSee('Affectation des Enseignants');
    }

    public function test_assignments_create_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $year = $this->createActiveYear();
        [$cycle, $level, $class] = $this->createCycleWithClass($year, 'PRI', 'Primaire');

        $response = $this->actingAs($user)->get("/assignments/create?class_id={$class->id}");

        $response->assertStatus(200);
        $response->assertSee('Nouvelle Affectation');
        $response->assertSee($class->name);
    }

    public function test_assignments_create_page_requires_class_id_from_gerer_flow(): void
    {
        $user = User::factory()->create();
        $this->createActiveYear();

        $response = $this->actingAs($user)->get('/assignments/create');

        $response->assertRedirect('/assignments');
        $response->assertSessionHas('warning');
    }

    public function test_create_view_only_shows_subjects_attached_to_class_level(): void
    {
        $user = User::factory()->create();
        $year = $this->createActiveYear();
        [$cycle, $level, $class] = $this->createCycleWithClass($year, 'PRI', 'Primaire');

        $level2 = Level::create(['cycle_id' => $cycle->id, 'code' => 'L2', 'name' => 'Niveau 2']);

        $subjectInLevel = $this->createSubject('LECTURE_CP');
        $subjectOtherLevel = $this->createSubject('HISTOIRE_CM2');

        ClassSubjectCoefficient::create([
            'level_id' => $level->id,
            'subject_id' => $subjectInLevel->id,
            'coefficient' => 3,
            'weekly_hours' => 5,
        ]);

        ClassSubjectCoefficient::create([
            'level_id' => $level2->id,
            'subject_id' => $subjectOtherLevel->id,
            'coefficient' => 2,
            'weekly_hours' => 3,
        ]);

        $response = $this->actingAs($user)->get("/assignments/create?class_id={$class->id}");

        $response->assertStatus(200);
        $response->assertSee($subjectInLevel->name);
        $response->assertDontSee($subjectOtherLevel->name);
    }

    // ---------------------------------------------------------------------------
    // Store
    // ---------------------------------------------------------------------------

    public function test_teacher_can_be_assigned_to_class_and_subject(): void
    {
        $user = User::factory()->create();
        $year = $this->createActiveYear();
        [$cycle, , $class] = $this->createCycleWithClass($year, 'COLLEGE', 'Collège');
        $subject = $this->createSubject('MATH');
        $teacher = $this->createTeacher($cycle->id, false);

        $response = $this->actingAs($user)->post('/assignments', [
            'teacher_id' => $teacher->id,
            'rows' => [['class_id' => $class->id, 'subject_id' => $subject->id]],
        ]);

        $response->assertRedirect('/assignments');
        $this->assertDatabaseHas('teacher_assignments', [
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);
    }

    public function test_assignment_requires_all_fields(): void
    {
        $user = User::factory()->create();
        $this->createActiveYear();

        $response = $this->actingAs($user)->post('/assignments', []);

        $response->assertSessionHasErrors(['teacher_id', 'rows']);
    }

    public function test_maternelle_teacher_cannot_be_assigned_to_more_than_one_class(): void
    {
        $user = User::factory()->create();
        $year = $this->createActiveYear();
        [$cycle, $level, $class1] = $this->createCycleWithClass($year, 'MAT', 'Maternelle');
        $class2 = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => 'Classe B']);
        $subject1 = $this->createSubject('EVEIL');
        $subject2 = $this->createSubject('GRAPH');
        $teacher = $this->createTeacher($cycle->id, true, 'MAT-001');

        // First class assignment should succeed
        $response1 = $this->actingAs($user)->post('/assignments', [
            'teacher_id' => $teacher->id,
            'rows' => [['class_id' => $class1->id, 'subject_id' => $subject1->id]],
        ]);
        $response1->assertRedirect('/assignments');

        // Second subject in the SAME class should succeed
        $responseSameClass = $this->actingAs($user)->post('/assignments', [
            'teacher_id' => $teacher->id,
            'rows' => [['class_id' => $class1->id, 'subject_id' => $subject2->id]],
        ]);
        $responseSameClass->assertRedirect('/assignments');

        // Assignment to a DIFFERENT class should fail with error
        $response2 = $this->actingAs($user)->post('/assignments', [
            'teacher_id' => $teacher->id,
            'rows' => [['class_id' => $class2->id, 'subject_id' => $subject1->id]],
        ]);
        $response2->assertSessionHasErrors('rows');
        $this->assertDatabaseMissing('teacher_assignments', [
            'teacher_id' => $teacher->id,
            'class_id' => $class2->id,
        ]);
    }

    public function test_primaire_teacher_cannot_be_assigned_to_more_than_one_class(): void
    {
        $user = User::factory()->create();
        $year = $this->createActiveYear();
        [$cycle, $level, $class1] = $this->createCycleWithClass($year, 'PRI', 'Primaire');
        $class2 = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => 'Classe B']);
        $subject = $this->createSubject('FRANCAIS');
        $teacher = $this->createTeacher($cycle->id, true, 'PRI-001');

        $this->actingAs($user)->post('/assignments', [
            'teacher_id' => $teacher->id,
            'rows' => [['class_id' => $class1->id, 'subject_id' => $subject->id]],
        ]);

        $response = $this->actingAs($user)->post('/assignments', [
            'teacher_id' => $teacher->id,
            'rows' => [['class_id' => $class2->id, 'subject_id' => $subject->id]],
        ]);

        $response->assertSessionHasErrors('rows');
        $this->assertDatabaseMissing('teacher_assignments', [
            'teacher_id' => $teacher->id,
            'class_id' => $class2->id,
        ]);
    }

    public function test_college_teacher_can_be_assigned_to_multiple_classes(): void
    {
        $user = User::factory()->create();
        $year = $this->createActiveYear();
        [$cycle, $level, $class1] = $this->createCycleWithClass($year, 'COL', 'Collège');
        $class2 = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème B']);
        $subject = $this->createSubject('MATHS');
        $teacher = $this->createTeacher($cycle->id, false, 'COL-001');

        $this->actingAs($user)->post('/assignments', [
            'teacher_id' => $teacher->id,
            'rows' => [['class_id' => $class1->id, 'subject_id' => $subject->id]],
        ])->assertRedirect('/assignments');

        $this->actingAs($user)->post('/assignments', [
            'teacher_id' => $teacher->id,
            'rows' => [['class_id' => $class2->id, 'subject_id' => $subject->id]],
        ])->assertRedirect('/assignments');

        $this->assertDatabaseHas('teacher_assignments', ['teacher_id' => $teacher->id, 'class_id' => $class1->id]);
        $this->assertDatabaseHas('teacher_assignments', ['teacher_id' => $teacher->id, 'class_id' => $class2->id]);
    }

    // ---------------------------------------------------------------------------
    // Show
    // ---------------------------------------------------------------------------

    public function test_assignment_show_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $year = $this->createActiveYear();
        [$cycle, , $class] = $this->createCycleWithClass($year, 'COLLEGE', 'Collège');
        $subject = $this->createSubject('HIST');
        $teacher = $this->createTeacher($cycle->id, false, 'SHOW-001');

        $assignment = TeacherAssignment::create([
            'academic_year_id' => $year->id,
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);

        $response = $this->actingAs($user)->get("/assignments/{$assignment->id}");

        $response->assertStatus(200);
        $response->assertSee($subject->name);
        $response->assertSee($class->name);
        $response->assertSee($teacher->user->name);
    }

    // ---------------------------------------------------------------------------
    // Edit / Update
    // ---------------------------------------------------------------------------

    public function test_assignment_edit_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $year = $this->createActiveYear();
        [$cycle, , $class] = $this->createCycleWithClass($year, 'COLLEGE', 'Collège');
        $subject = $this->createSubject('GEO');
        $teacher = $this->createTeacher($cycle->id, false, 'EDIT-001');

        $assignment = TeacherAssignment::create([
            'academic_year_id' => $year->id,
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);

        $response = $this->actingAs($user)->get("/assignments/{$assignment->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Modifier');
        $response->assertSee('Affectation');
    }

    public function test_assignment_can_be_updated(): void
    {
        $user = User::factory()->create();
        $year = $this->createActiveYear();
        [$cycle, , $class] = $this->createCycleWithClass($year, 'COLLEGE', 'Collège');
        $subject1 = $this->createSubject('PHYS');
        $subject2 = $this->createSubject('CHIM');
        $teacher = $this->createTeacher($cycle->id, false, 'UPD-001');

        $assignment = TeacherAssignment::create([
            'academic_year_id' => $year->id,
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject1->id,
        ]);

        $response = $this->actingAs($user)->put("/assignments/{$assignment->id}", [
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject2->id,
        ]);

        $response->assertRedirect('/assignments');
        $this->assertDatabaseHas('teacher_assignments', [
            'id' => $assignment->id,
            'subject_id' => $subject2->id,
        ]);
    }

    public function test_update_blocks_restricted_teacher_from_switching_to_new_class(): void
    {
        $user = User::factory()->create();
        $year = $this->createActiveYear();
        [$cycle, $level, $class1] = $this->createCycleWithClass($year, 'MAT', 'Maternelle');
        $class2 = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => 'PS B']);
        $subject1 = $this->createSubject('ACT');
        $subject2 = $this->createSubject('EVEIL2');
        $teacher = $this->createTeacher($cycle->id, true, 'RES-001');

        // Two assignments in class1 (pinning the teacher to class1)
        $assignment1 = TeacherAssignment::create([
            'academic_year_id' => $year->id,
            'teacher_id' => $teacher->id,
            'class_id' => $class1->id,
            'subject_id' => $subject1->id,
        ]);
        TeacherAssignment::create([
            'academic_year_id' => $year->id,
            'teacher_id' => $teacher->id,
            'class_id' => $class1->id,
            'subject_id' => $subject2->id,
        ]);

        // Attempt to update assignment1 to class2: must fail because teacher still has class1 via assignment2
        $response = $this->actingAs($user)->put("/assignments/{$assignment1->id}", [
            'teacher_id' => $teacher->id,
            'class_id' => $class2->id,
            'subject_id' => $subject1->id,
        ]);

        $response->assertSessionHasErrors('teacher_id');
        $this->assertDatabaseHas('teacher_assignments', [
            'id' => $assignment1->id,
            'class_id' => $class1->id,
        ]);
    }

    // ---------------------------------------------------------------------------
    // Destroy
    // ---------------------------------------------------------------------------

    public function test_assignment_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $year = $this->createActiveYear();
        [$cycle, , $class] = $this->createCycleWithClass($year, 'COLLEGE', 'Collège');
        $subject = $this->createSubject('SVT');
        $teacher = $this->createTeacher($cycle->id, false, 'DEL-001');

        $assignment = TeacherAssignment::create([
            'academic_year_id' => $year->id,
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);

        $response = $this->actingAs($user)->delete("/assignments/{$assignment->id}");

        $response->assertRedirect('/assignments');
        $this->assertDatabaseMissing('teacher_assignments', ['id' => $assignment->id]);
    }

    public function test_deleting_assignment_allows_restricted_teacher_to_be_reassigned(): void
    {
        $user = User::factory()->create();
        $year = $this->createActiveYear();
        [$cycle, $level, $class1] = $this->createCycleWithClass($year, 'PRI', 'Primaire');
        $class2 = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => 'CP B']);
        $subject = $this->createSubject('LECT');
        $teacher = $this->createTeacher($cycle->id, true, 'FREE-001');

        $assignment = TeacherAssignment::create([
            'academic_year_id' => $year->id,
            'teacher_id' => $teacher->id,
            'class_id' => $class1->id,
            'subject_id' => $subject->id,
        ]);

        // Delete the original assignment
        $this->actingAs($user)->delete("/assignments/{$assignment->id}");

        // Now assigning to class2 should succeed
        $response = $this->actingAs($user)->post('/assignments', [
            'teacher_id' => $teacher->id,
            'rows' => [['class_id' => $class2->id, 'subject_id' => $subject->id]],
        ]);

        $response->assertRedirect('/assignments');
        $this->assertDatabaseHas('teacher_assignments', [
            'teacher_id' => $teacher->id,
            'class_id' => $class2->id,
        ]);
    }

    public function test_assignments_can_be_stored_by_class_matrix(): void
    {
        $user = User::factory()->create();
        $year = $this->createActiveYear();
        [$cycle, , $class] = $this->createCycleWithClass($year, 'COLLEGE', 'Collège');
        $subject1 = $this->createSubject('MATH');
        $subject2 = $this->createSubject('FRAN');
        $teacher1 = $this->createTeacher($cycle->id, false, 'TCH-1');
        $teacher2 = $this->createTeacher($cycle->id, false, 'TCH-2');

        $response = $this->actingAs($user)->post('/assignments', [
            'class_id' => $class->id,
            'class_assignments' => [
                ['subject_id' => $subject1->id, 'teacher_id' => $teacher1->id],
                ['subject_id' => $subject2->id, 'teacher_id' => $teacher2->id],
            ],
        ]);

        $response->assertRedirect('/assignments');
        $this->assertDatabaseHas('teacher_assignments', [
            'teacher_id' => $teacher1->id,
            'class_id' => $class->id,
            'subject_id' => $subject1->id,
        ]);
        $this->assertDatabaseHas('teacher_assignments', [
            'teacher_id' => $teacher2->id,
            'class_id' => $class->id,
            'subject_id' => $subject2->id,
        ]);
    }

    public function test_store_by_class_matrix_rejects_subject_not_attached_to_class_level(): void
    {
        $user = User::factory()->create();
        $year = $this->createActiveYear();
        [$cycle, $level, $class] = $this->createCycleWithClass($year, 'PRI', 'Primaire');
        $level2 = Level::create(['cycle_id' => $cycle->id, 'code' => 'L2', 'name' => 'Niveau 2']);

        $validSubject = $this->createSubject('MATH_PRI');
        $invalidSubject = $this->createSubject('PHILO_TERM');
        $teacher = $this->createTeacher($cycle->id, true, 'PRI-SUBJ');

        ClassSubjectCoefficient::create([
            'level_id' => $level->id,
            'subject_id' => $validSubject->id,
            'coefficient' => 2,
            'weekly_hours' => 3,
        ]);

        ClassSubjectCoefficient::create([
            'level_id' => $level2->id,
            'subject_id' => $invalidSubject->id,
            'coefficient' => 4,
            'weekly_hours' => 4,
        ]);

        $response = $this->actingAs($user)->post('/assignments', [
            'class_id' => $class->id,
            'class_assignments' => [
                ['subject_id' => $invalidSubject->id, 'teacher_id' => $teacher->id],
            ],
        ]);

        $response->assertSessionHasErrors('rows');
        $this->assertDatabaseMissing('teacher_assignments', [
            'class_id' => $class->id,
            'subject_id' => $invalidSubject->id,
        ]);
    }

    public function test_store_by_class_matrix_enforces_single_class_rule_for_maternelle_primaire_teacher(): void
    {
        $user = User::factory()->create();
        $year = $this->createActiveYear();
        [$cycle, $level, $class1] = $this->createCycleWithClass($year, 'PRI', 'Primaire');
        $class2 = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => 'Classe 2']);

        $subject1 = $this->createSubject('FRAN_1');
        $subject2 = $this->createSubject('MATH_1');
        $teacher = $this->createTeacher($cycle->id, true, 'PRI-SINGLE');

        ClassSubjectCoefficient::create([
            'level_id' => $level->id,
            'subject_id' => $subject1->id,
            'coefficient' => 2,
            'weekly_hours' => 3,
        ]);
        ClassSubjectCoefficient::create([
            'level_id' => $level->id,
            'subject_id' => $subject2->id,
            'coefficient' => 2,
            'weekly_hours' => 3,
        ]);

        // Assign teacher to class1
        $this->actingAs($user)->post('/assignments', [
            'class_id' => $class1->id,
            'class_assignments' => [
                ['subject_id' => $subject1->id, 'teacher_id' => $teacher->id],
            ],
        ])->assertRedirect('/assignments');

        // Attempt to assign the SAME teacher to class2
        $response = $this->actingAs($user)->post('/assignments', [
            'class_id' => $class2->id,
            'class_assignments' => [
                ['subject_id' => $subject2->id, 'teacher_id' => $teacher->id],
            ],
        ]);

        $response->assertSessionHasErrors('rows');
        $this->assertDatabaseMissing('teacher_assignments', [
            'class_id' => $class2->id,
            'teacher_id' => $teacher->id,
        ]);
    }

    public function test_two_teachers_cannot_teach_the_same_subject_for_the_same_class(): void
    {
        $user = User::factory()->create();
        $year = $this->createActiveYear();
        [$cycle, $level, $class] = $this->createCycleWithClass($year, 'COL', 'Collège');

        $subject = $this->createSubject('MATH_UNIQUE');
        $teacher1 = $this->createTeacher($cycle->id, false, 'COL-001');
        $teacher2 = $this->createTeacher($cycle->id, false, 'COL-002');

        ClassSubjectCoefficient::create([
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'coefficient' => 4,
            'weekly_hours' => 4,
        ]);

        // Teacher 1 is already assigned to this class and subject
        TeacherAssignment::create([
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher1->id,
        ]);

        // Teacher 2 attempts to be assigned to the same class and subject via By-Teacher store
        $response = $this->actingAs($user)->post('/assignments', [
            'teacher_id' => $teacher2->id,
            'rows' => [
                ['class_id' => $class->id, 'subject_id' => $subject->id],
            ],
        ]);

        $response->assertSessionHasErrors('rows');

        // Verify the database still has teacher1 and did NOT duplicate or assign teacher2
        $this->assertEquals(1, TeacherAssignment::where('academic_year_id', $year->id)->where('class_id', $class->id)->where('subject_id', $subject->id)->count());
        $this->assertDatabaseHas('teacher_assignments', [
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher1->id,
        ]);
    }
}
