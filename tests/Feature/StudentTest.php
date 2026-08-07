<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        Student::create([
            'matricule' => 'RS-2025-0001',
            'first_name' => 'Souleymane',
            'last_name' => 'Moussa',
            'gender' => 'M',
            'birth_date' => '2012-05-14',
            'birth_place' => 'Niamey',
        ]);

        $response = $this->actingAs($user)->get('/students');

        $response->assertStatus(200);
        $response->assertSee('RS-2025-0001');
        $response->assertSee('Souleymane');
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
}
