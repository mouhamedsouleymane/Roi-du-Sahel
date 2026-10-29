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
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_index_renders_class_cards(): void
    {
        $user = User::factory()->create();
        $academicYear = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $cycle = Cycle::create([
            'name' => 'Collège',
            'code' => 'COL',
            'is_active' => true,
        ]);

        $level = Level::create([
            'cycle_id' => $cycle->id,
            'name' => 'Sixième',
            'code' => '6EME',
            'order_index' => 1,
        ]);

        $class = SchoolClass::create([
            'level_id' => $level->id,
            'academic_year_id' => $academicYear->id,
            'name' => '6ème A',
            'capacity' => 40,
        ]);

        $response = $this->actingAs($user)->get('/students');

        $response->assertStatus(200);
        $response->assertSee('Cycle Collège');
        $response->assertSee('6ème A');
        $response->assertSee('Effectif Global');
    }

    public function test_students_can_be_filtered_by_class(): void
    {
        $user = User::factory()->create();
        $academicYear = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $cycle = Cycle::create(['name' => 'Lycée', 'code' => 'LYC', 'is_active' => true]);
        $level = Level::create(['cycle_id' => $cycle->id, 'name' => 'Terminale', 'code' => 'TLE', 'order_index' => 1]);
        $class = SchoolClass::create([
            'level_id' => $level->id,
            'academic_year_id' => $academicYear->id,
            'name' => 'Terminale D1',
            'capacity' => 35,
        ]);

        $student = Student::create([
            'matricule' => 'RS-2025-0001',
            'first_name' => 'Souleymane',
            'last_name' => 'Moussa',
            'gender' => 'M',
            'birth_date' => '2007-05-14',
            'birth_place' => 'Niamey',
        ]);

        Enrollment::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'academic_year_id' => $academicYear->id,
            'enrollment_number' => 'INS-2025-0001',
            'type' => 'NOUVEAU',
            'status' => 'VALIDE',
            'enrollment_date' => '2025-09-02',
        ]);

        $response = $this->actingAs($user)->get("/students?class_id={$class->id}");

        $response->assertStatus(200);
        $response->assertSee('RS-2025-0001');
        $response->assertSee('Souleymane');
        $response->assertSee('Terminale D1');
    }

    public function test_students_can_be_searched(): void
    {
        $user = User::factory()->create();

        Student::create([
            'matricule' => 'RS-2025-0099',
            'first_name' => 'Mahamadou',
            'last_name' => 'Issoufou',
            'gender' => 'M',
            'birth_date' => '2008-01-01',
            'birth_place' => 'Tahoua',
        ]);

        $response = $this->actingAs($user)->get('/students?search=Mahamadou');

        $response->assertStatus(200);
        $response->assertSee('RS-2025-0099');
        $response->assertSee('Issoufou');
    }

    public function test_student_profile_can_be_viewed(): void
    {
        $user = User::factory()->create();

        $student = Student::create([
            'matricule' => 'RS-2025-0002',
            'first_name' => 'Fati',
            'last_name' => 'Amadou',
            'gender' => 'F',
            'birth_date' => '2008-11-20',
            'birth_place' => 'Maradi',
        ]);

        $response = $this->actingAs($user)->get("/students/{$student->id}");

        $response->assertStatus(200);
        $response->assertSee('Fati');
        $response->assertSee('RS-2025-0002');
    }

    public function test_student_profile_displays_evaluations_and_grades(): void
    {
        $user = User::factory()->create();

        $year = AcademicYear::create([
            'name' => '2026-2027',
            'start_date' => '2026-09-15',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième', 'order_index' => 1]);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A', 'capacity' => 40]);

        $period = Period::create([
            'academic_year_id' => $year->id,
            'name' => 'Trimestre 1',
            'type' => 'TRIMESTRE',
            'order' => 1,
            'start_date' => '2026-09-15',
            'end_date' => '2026-12-15',
        ]);

        $subject = Subject::create([
            'name' => 'Histoire-Géographie',
            'code' => 'HIST-GEO',
        ]);

        $student = Student::create([
            'matricule' => 'RS-2026-0005',
            'first_name' => 'Moustapha',
            'last_name' => 'Garba',
            'gender' => 'M',
            'birth_date' => '2014-04-12',
            'birth_place' => 'Niamey',
            'status' => 'ACTIF',
        ]);

        Enrollment::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'academic_year_id' => $year->id,
            'enrollment_number' => 'INS-2026-0005',
            'type' => 'NOUVEAU',
            'status' => 'VALIDE',
            'enrollment_date' => '2026-09-15',
        ]);

        $evaluation = Evaluation::create([
            'period_id' => $period->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'title' => 'Devoir d\'Histoire N°1',
            'type' => 'DEVOIR',
            'max_score' => 20,
            'coefficient' => 2,
            'evaluation_date' => '2026-10-15',
        ]);

        Grade::create([
            'evaluation_id' => $evaluation->id,
            'student_id' => $student->id,
            'score' => 16.50,
            'is_absent' => false,
            'comment' => 'Très bon travail d\'analyse',
        ]);

        $response = $this->actingAs($user)->get("/students/{$student->id}");

        $response->assertStatus(200);
        $response->assertSee('Évaluations &amp; Notes de l&#039;Élève', false);
        $response->assertSee('Histoire-Géographie');
        $response->assertSee('Devoir d&#039;Histoire N°1', false);
        $response->assertSee('16,50');
        $response->assertSee('Très bon travail d&#039;analyse', false);
        $response->assertSee('Très Bien');
    }

    public function test_student_edit_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $student = Student::create([
            'matricule' => 'RS-2025-0003',
            'first_name' => 'Ibrahim',
            'last_name' => 'Oumarou',
            'gender' => 'M',
            'birth_date' => '2010-01-15',
            'birth_place' => 'Zinder',
        ]);

        $response = $this->actingAs($user)->get("/students/{$student->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Ibrahim');
        $response->assertSee('RS-2025-0003');
    }

    public function test_student_can_be_updated(): void
    {
        $user = User::factory()->create();

        $student = Student::create([
            'matricule' => 'RS-2025-0004',
            'first_name' => 'Amina',
            'last_name' => 'Salifou',
            'gender' => 'F',
            'birth_date' => '2011-04-10',
            'birth_place' => 'Tahoua',
        ]);

        $response = $this->actingAs($user)->put("/students/{$student->id}", [
            'first_name' => 'Amina',
            'last_name' => 'Salifou Modifié',
            'gender' => 'F',
            'birth_date' => '2011-04-10',
            'birth_place' => 'Tahoua Ville',
            'blood_group' => 'O+',
            'previous_school' => 'École Annexe',
            'medical_notes' => 'Légère myopie',
            'status' => 'ACTIF',
        ]);

        $response->assertRedirect(route('students.show', $student));
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'last_name' => 'Salifou Modifié',
            'birth_place' => 'Tahoua Ville',
            'blood_group' => 'O+',
            'medical_notes' => 'Légère myopie',
        ]);
    }

    public function test_students_create_redirects_to_enrollments_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/students/create');

        $response->assertRedirect(route('enrollments.create'));
    }
}
