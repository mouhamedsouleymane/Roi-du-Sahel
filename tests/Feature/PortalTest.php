<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    private function createBaseData(): array
    {
        $year = AcademicYear::create([
            'name'       => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date'   => '2026-06-30',
            'is_active'  => true,
        ]);

        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A']);

        $student = Student::create([
            'matricule'  => 'RS-2025-0001',
            'first_name' => 'Amina',
            'last_name'  => 'Moussa',
            'birth_date' => '2010-01-01',
            'gender'     => 'F',
        ]);

        Enrollment::create([
            'student_id'       => $student->id,
            'academic_year_id' => $year->id,
            'class_id'         => $class->id,
            'status'           => 'VALIDE',
        ]);

        $parentUser = User::factory()->create(['email' => 'parent@example.com']);
        $guardian   = Guardian::create([
            'user_id'        => $parentUser->id,
            'first_name'     => 'Moussa',
            'last_name'      => 'Abdou',
            'phone_primary'  => '90000000',
            'email'          => 'parent@example.com',
            'relationship'   => 'PERE',
        ]);

        $guardian->students()->attach($student->id, ['is_primary_contact' => true]);

        return compact('year', 'class', 'student', 'guardian', 'parentUser');
    }

    public function test_parent_portal_requires_authentication(): void
    {
        $response = $this->get('/parent-portal');
        $response->assertRedirect('/login');
    }

    public function test_parent_portal_can_be_rendered_by_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/parent-portal');

        $response->assertStatus(200);
        $response->assertSee('Espace Parents');
    }

    public function test_parent_portal_displays_linked_child(): void
    {
        $data = $this->createBaseData();

        $response = $this->actingAs($data['parentUser'])->get('/parent-portal');

        $response->assertStatus(200);
        $response->assertSee('Amina');
    }

    public function test_student_portal_requires_authentication(): void
    {
        $response = $this->get('/student-portal');
        $response->assertRedirect('/login');
    }

    public function test_student_portal_can_be_rendered_by_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/student-portal');

        $response->assertStatus(200);
        $response->assertSee('Mon Espace Élève');
    }
}
