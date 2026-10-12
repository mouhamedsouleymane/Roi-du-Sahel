<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSubjectCoefficient;
use App\Models\Cycle;
use App\Models\Evaluation;
use App\Models\Level;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectAndCoefficientTest extends TestCase
{
    use RefreshDatabase;

    private function makeCycleAndLevel(): array
    {
        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '3EME', 'name' => 'Troisième', 'order_index' => 4]);

        return [$cycle, $level];
    }

    public function test_guest_cannot_access_subjects(): void
    {
        $this->get('/subjects')->assertRedirect('/login');
        $this->get('/subjects/create')->assertRedirect('/login');
    }

    public function test_subjects_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        Subject::create([
            'code' => 'MATH',
            'name' => 'Mathématiques',
            'category' => 'scientifique',
        ]);

        $response = $this->actingAs($user)->get('/subjects');

        $response->assertStatus(200);
        $response->assertSee('Mathématiques');
    }

    public function test_subjects_index_can_be_filtered_by_category(): void
    {
        $user = User::factory()->create();

        Subject::create(['code' => 'MATH', 'name' => 'Mathématiques', 'category' => 'scientifique']);
        Subject::create(['code' => 'FRAN', 'name' => 'Français', 'category' => 'littéraire']);

        $response = $this->actingAs($user)->get('/subjects?category=scientifique');
        $response->assertStatus(200);
        $response->assertSee('Mathématiques');
        $response->assertDontSee('Français');
    }

    public function test_subjects_create_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/subjects/create');
        $response->assertStatus(200);
        $response->assertSee('Nouvelle Matière Enseignée');
    }

    public function test_subject_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/subjects', [
            'code' => 'pc',
            'name' => 'Physique-Chimie',
            'category' => 'scientifique',
            'description' => 'Cours théoriques et TP de physique',
            'is_active' => 1,
        ]);

        $response->assertRedirect('/subjects');
        $this->assertDatabaseHas('subjects', [
            'code' => 'PC',
            'name' => 'Physique-Chimie',
            'category' => 'scientifique',
            'description' => 'Cours théoriques et TP de physique',
            'is_active' => 1,
        ]);
    }

    public function test_subject_code_must_be_unique(): void
    {
        $user = User::factory()->create();

        Subject::create(['code' => 'MATH', 'name' => 'Mathématiques', 'category' => 'scientifique']);

        $response = $this->actingAs($user)->post('/subjects', [
            'code' => 'MATH',
            'name' => 'Autre Math',
            'category' => 'scientifique',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_subject_category_must_be_valid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/subjects', [
            'code' => 'TEST',
            'name' => 'Test Subject',
            'category' => 'invalid_category',
        ]);

        $response->assertSessionHasErrors('category');
    }

    public function test_subject_edit_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $subject = Subject::create(['code' => 'ANG', 'name' => 'Anglais', 'category' => 'langues']);

        $response = $this->actingAs($user)->get("/subjects/{$subject->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Anglais');
        $response->assertSee('ANG');
    }

    public function test_subject_can_be_updated(): void
    {
        $user = User::factory()->create();

        $subject = Subject::create(['code' => 'EPS', 'name' => 'Sport', 'category' => 'sport']);

        $response = $this->actingAs($user)->put("/subjects/{$subject->id}", [
            'code' => 'EPS',
            'name' => 'Éducation Physique et Sportive',
            'category' => 'sport',
            'description' => 'Activités sportives',
            'is_active' => 1,
        ]);

        $response->assertRedirect('/subjects');
        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'name' => 'Éducation Physique et Sportive',
        ]);
    }

    public function test_subject_can_toggle_active(): void
    {
        $user = User::factory()->create();

        $subject = Subject::create(['code' => 'SVT', 'name' => 'Sciences', 'category' => 'scientifique', 'is_active' => true]);

        $response = $this->actingAs($user)->patch("/subjects/{$subject->id}/toggle-active");
        $response->assertRedirect('/subjects');
        $this->assertDatabaseHas('subjects', ['id' => $subject->id, 'is_active' => 0]);

        $response = $this->actingAs($user)->patch("/subjects/{$subject->id}/toggle-active");
        $response->assertRedirect('/subjects');
        $this->assertDatabaseHas('subjects', ['id' => $subject->id, 'is_active' => 1]);
    }

    public function test_subject_can_be_deleted_if_orphan(): void
    {
        $user = User::factory()->create();

        $subject = Subject::create(['code' => 'CHIN', 'name' => 'Chinois', 'category' => 'langues']);

        $response = $this->actingAs($user)->delete("/subjects/{$subject->id}");
        $response->assertRedirect('/subjects');
        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
    }

    public function test_subject_cannot_be_deleted_if_has_coefficients(): void
    {
        $user = User::factory()->create();
        [$cycle, $level] = $this->makeCycleAndLevel();

        $subject = Subject::create(['code' => 'MATH', 'name' => 'Maths', 'category' => 'scientifique']);
        ClassSubjectCoefficient::create([
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'coefficient' => 4,
            'weekly_hours' => 5,
        ]);

        $response = $this->actingAs($user)->delete("/subjects/{$subject->id}");
        $response->assertRedirect('/subjects');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('subjects', ['id' => $subject->id]);
    }

    public function test_subject_cannot_be_deleted_if_has_teacher_assignments(): void
    {
        $user = User::factory()->create();
        [$cycle, $level] = $this->makeCycleAndLevel();

        $year = AcademicYear::create(['name' => '2025-2026', 'start_date' => '2025-09-15', 'end_date' => '2026-06-30', 'is_active' => true]);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '3ème A', 'capacity' => 40]);
        $teacherUser = User::factory()->create();
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'matricule' => 'ENS-2025-0001',
            'speciality' => 'Maths',
        ]);
        $subject = Subject::create(['code' => 'MATH', 'name' => 'Maths', 'category' => 'scientifique']);

        TeacherAssignment::create([
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
        ]);

        $response = $this->actingAs($user)->delete("/subjects/{$subject->id}");
        $response->assertRedirect('/subjects');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('subjects', ['id' => $subject->id]);
    }

    public function test_subject_cannot_be_deleted_if_has_evaluations(): void
    {
        $user = User::factory()->create();
        [$cycle, $level] = $this->makeCycleAndLevel();

        $year = AcademicYear::create(['name' => '2025-2026', 'start_date' => '2025-09-15', 'end_date' => '2026-06-30', 'is_active' => true]);
        $period = Period::create(['academic_year_id' => $year->id, 'name' => 'Trimestre 1', 'type' => 'TRIMESTRE', 'order' => 1, 'start_date' => '2025-09-15', 'end_date' => '2025-12-15']);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '3ème A', 'capacity' => 40]);
        $teacherUser = User::factory()->create();
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'matricule' => 'ENS-2025-0002',
            'speciality' => 'Maths',
        ]);
        $subject = Subject::create(['code' => 'MATH', 'name' => 'Maths', 'category' => 'scientifique']);

        Evaluation::create([
            'period_id' => $period->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'title' => 'Devoir 1',
            'type' => 'DEVOIR',
            'max_score' => 20,
            'coefficient' => 1,
            'evaluation_date' => '2025-10-15',
        ]);

        $response = $this->actingAs($user)->delete("/subjects/{$subject->id}");
        $response->assertRedirect('/subjects');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('subjects', ['id' => $subject->id]);
    }

    public function test_coefficient_can_be_deleted(): void
    {
        $user = User::factory()->create();
        [$cycle, $level] = $this->makeCycleAndLevel();
        $subject = Subject::create(['code' => 'PC', 'name' => 'Physique-Chimie', 'category' => 'scientifique']);

        $coeff = ClassSubjectCoefficient::create([
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'coefficient' => 3,
            'weekly_hours' => 4,
        ]);

        $response = $this->actingAs($user)->delete("/subjects/coefficients/{$coeff->id}");
        $response->assertRedirect(route('cycles.levels.show', [$cycle, $level]));
        $this->assertDatabaseMissing('class_subject_coefficients', ['id' => $coeff->id]);
    }

    public function test_subject_and_coefficient_can_be_created(): void
    {
        $user = User::factory()->create();

        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '3EME', 'name' => 'Troisième']);

        $subject = Subject::create([
            'code' => 'PC',
            'name' => 'Physique-Chimie',
            'category' => 'scientifique',
        ]);

        $response = $this->actingAs($user)->post('/subjects/coefficients', [
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'coefficient' => 3,
            'weekly_hours' => 4,
        ]);

        $response->assertRedirect(route('cycles.levels.show', [$cycle, $level]));
        $this->assertDatabaseHas('class_subject_coefficients', [
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'coefficient' => 3,
        ]);
    }

    public function test_subject_can_be_created_with_selected_levels(): void
    {
        $user = User::factory()->create();

        $cycle = Cycle::create(['code' => 'LYCEE', 'name' => 'Lycée']);
        $level1 = Level::create(['cycle_id' => $cycle->id, 'code' => '2NDE', 'name' => 'Seconde', 'order_index' => 1]);
        $level2 = Level::create(['cycle_id' => $cycle->id, 'code' => '1ERE', 'name' => 'Première', 'order_index' => 2]);

        $response = $this->actingAs($user)->post('/subjects', [
            'code' => 'INFO',
            'name' => 'Informatique & Numérique',
            'category' => 'scientifique',
            'level_ids' => [$level1->id, $level2->id],
            'levels_data' => [
                $level1->id => ['coefficient' => 2, 'weekly_hours' => 3],
                $level2->id => ['coefficient' => 3, 'weekly_hours' => 4],
            ],
        ]);

        $response->assertRedirect('/subjects');

        $subject = Subject::where('code', 'INFO')->first();
        $this->assertNotNull($subject);

        $this->assertDatabaseHas('class_subject_coefficients', [
            'level_id' => $level1->id,
            'subject_id' => $subject->id,
            'coefficient' => 2,
            'weekly_hours' => 3,
        ]);

        $this->assertDatabaseHas('class_subject_coefficients', [
            'level_id' => $level2->id,
            'subject_id' => $subject->id,
            'coefficient' => 3,
            'weekly_hours' => 4,
        ]);
    }
}
