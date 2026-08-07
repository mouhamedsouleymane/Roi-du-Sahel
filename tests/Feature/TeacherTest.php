<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    public function test_teachers_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $teacherUser = User::factory()->create(['name' => 'Prof Ali']);
        Teacher::create([
            'user_id' => $teacherUser->id,
            'matricule' => 'ENS-2025-0001',
            'speciality' => 'Mathématiques',
        ]);

        $response = $this->actingAs($user)->get('/teachers');

        $response->assertStatus(200);
        $response->assertSee('Prof Ali');
        $response->assertSee('ENS-2025-0001');
    }

    public function test_teacher_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/teachers', [
            'name' => 'Prof Oumar',
            'email' => 'oumar@roisdusahel.ne',
            'speciality' => 'Physique',
            'employment_type' => 'PERMANENT',
        ]);

        $response->assertRedirect('/teachers');
        $this->assertDatabaseHas('users', ['email' => 'oumar@roisdusahel.ne']);
        $this->assertDatabaseHas('teachers', ['speciality' => 'Physique']);
    }
}
