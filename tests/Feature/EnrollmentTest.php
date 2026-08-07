<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollment_form_can_be_rendered(): void
    {
        $user = User::factory()->create();

        AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/enrollments/create');

        $response->assertStatus(200);
        $response->assertSee('Formulaire d\'Inscription');
    }

    public function test_new_student_can_be_enrolled(): void
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
        $class = SchoolClass::create([
            'academic_year_id' => $year->id,
            'level_id' => $level->id,
            'name' => '6ème A',
            'capacity' => 40,
        ]);

        $response = $this->actingAs($user)->post('/enrollments', [
            'first_name' => 'Souleymane',
            'last_name' => 'Moussa',
            'gender' => 'M',
            'birth_date' => '2012-05-14',
            'birth_place' => 'Niamey',
            'guardian_first_name' => 'Moussa',
            'guardian_last_name' => 'Souley',
            'relationship' => 'PERE',
            'phone_primary' => '+227 90 11 22 33',
            'class_id' => $class->id,
            'type' => 'NOUVEAU',
        ]);

        $this->assertDatabaseHas('students', [
            'first_name' => 'Souleymane',
            'last_name' => 'Moussa',
        ]);

        $this->assertDatabaseHas('guardians', [
            'first_name' => 'Moussa',
            'phone_primary' => '+227 90 11 22 33',
        ]);

        $this->assertDatabaseHas('enrollments', [
            'class_id' => $class->id,
            'status' => 'VALIDE',
        ]);
    }
}
