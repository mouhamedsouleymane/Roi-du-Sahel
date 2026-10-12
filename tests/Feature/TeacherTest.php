<?php

namespace Tests\Feature;

use App\Models\Cycle;
use App\Models\Level;
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
        $cycle = Cycle::create([
            'name' => 'Collège',
            'code' => 'COL',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/teachers', [
            'name' => 'Prof Oumar',
            'email' => 'oumar@roisdusahel.ne',
            'phone' => '90123456',
            'cycle_id' => $cycle->id,
            'speciality' => 'Physique',
            'employment_type' => 'PERMANENT',
        ]);

        $response->assertRedirect('/teachers');
        $this->assertDatabaseHas('users', ['email' => 'oumar@roisdusahel.ne', 'phone' => '90123456']);
        $this->assertDatabaseHas('teachers', ['speciality' => 'Physique']);
    }

    public function test_teachers_are_displayed_grouped_by_cycle(): void
    {
        $user = User::factory()->create();

        $cycleMaternelle = Cycle::create([
            'name' => 'Maternelle',
            'code' => 'MAT',
            'uniform_tshirt_color' => 'Violet',
            'is_active' => true,
        ]);

        $cycleCollege = Cycle::create([
            'name' => 'Collège',
            'code' => 'COL',
            'uniform_tshirt_color' => 'Jaune',
            'is_active' => true,
        ]);

        $teacherMaternelle = User::factory()->create(['name' => 'Mme Aïcha']);
        Teacher::create([
            'user_id' => $teacherMaternelle->id,
            'cycle_id' => $cycleMaternelle->id,
            'matricule' => 'ENS-2025-0010',
            'speciality' => 'Polyvalente',
            'is_polyvalent' => true,
        ]);

        $teacherCollege = User::factory()->create(['name' => 'M. Moussa']);
        Teacher::create([
            'user_id' => $teacherCollege->id,
            'cycle_id' => $cycleCollege->id,
            'matricule' => 'ENS-2025-0011',
            'speciality' => 'SVT',
            'is_polyvalent' => false,
        ]);

        $response = $this->actingAs($user)->get('/teachers');

        $response->assertStatus(200);
        $response->assertSee('Maternelle');
        $response->assertSee('Collège');
        $response->assertSee('Mme Aïcha');
        $response->assertSee('M. Moussa');
    }

    public function test_teachers_can_be_filtered_by_cycle(): void
    {
        $user = User::factory()->create();

        $cyclePrimaire = Cycle::create(['name' => 'Primaire', 'code' => 'PRI', 'is_active' => true]);
        $cycleLycee = Cycle::create(['name' => 'Lycée', 'code' => 'LYC', 'is_active' => true]);

        $teacherPrimaire = User::factory()->create(['name' => 'Prof Primaire']);
        Teacher::create([
            'user_id' => $teacherPrimaire->id,
            'cycle_id' => $cyclePrimaire->id,
            'matricule' => 'ENS-2025-0020',
            'speciality' => 'Général',
        ]);

        $teacherLycee = User::factory()->create(['name' => 'Prof Lycee']);
        Teacher::create([
            'user_id' => $teacherLycee->id,
            'cycle_id' => $cycleLycee->id,
            'matricule' => 'ENS-2025-0021',
            'speciality' => 'Philosophie',
        ]);

        $response = $this->actingAs($user)->get('/teachers?cycle_id='.$cyclePrimaire->id);

        $response->assertStatus(200);
        $response->assertSee('Prof Primaire');
        $response->assertDontSee('Prof Lycee');
    }

    public function test_teacher_can_be_created_with_multiple_cycles_and_levels(): void
    {
        $user = User::factory()->create();

        $cycleCollege = Cycle::create(['name' => 'Collège', 'code' => 'COL', 'is_active' => true]);
        $cycleLycee = Cycle::create(['name' => 'Lycée', 'code' => 'LYC', 'is_active' => true]);

        $level1 = Level::create(['cycle_id' => $cycleCollege->id, 'code' => '3EME', 'name' => 'Troisième', 'order_index' => 4]);
        $level2 = Level::create(['cycle_id' => $cycleLycee->id, 'code' => '2NDE', 'name' => 'Seconde', 'order_index' => 1]);

        $response = $this->actingAs($user)->post('/teachers', [
            'name' => 'Prof Multi-Cycles',
            'email' => 'multi@roisdusahel.ne',
            'phone' => '99887766',
            'cycle_ids' => [$cycleCollege->id, $cycleLycee->id],
            'level_ids' => [$level1->id, $level2->id],
            'speciality' => 'Mathématiques',
            'employment_type' => 'PERMANENT',
        ]);

        $response->assertRedirect('/teachers');

        $teacher = Teacher::where('speciality', 'Mathématiques')->first();
        $this->assertNotNull($teacher);

        $this->assertDatabaseHas('teacher_cycles', [
            'teacher_id' => $teacher->id,
            'cycle_id' => $cycleCollege->id,
        ]);
        $this->assertDatabaseHas('teacher_cycles', [
            'teacher_id' => $teacher->id,
            'cycle_id' => $cycleLycee->id,
        ]);

        $this->assertDatabaseHas('teacher_levels', [
            'teacher_id' => $teacher->id,
            'level_id' => $level1->id,
        ]);
        $this->assertDatabaseHas('teacher_levels', [
            'teacher_id' => $teacher->id,
            'level_id' => $level2->id,
        ]);
    }

    public function test_polyvalent_cycles_cannot_be_combined_with_other_cycles(): void
    {
        $user = User::factory()->create();

        $cycleMaternelle = Cycle::create(['name' => 'Maternelle', 'code' => 'MAT', 'is_active' => true]);
        $cycleCollege = Cycle::create(['name' => 'Collège', 'code' => 'COL', 'is_active' => true]);
        $cyclePrimaire = Cycle::create(['name' => 'Primaire', 'code' => 'PRI', 'is_active' => true]);

        // Attempt Maternelle + Collège
        $response = $this->actingAs($user)->post('/teachers', [
            'name' => 'Prof Invalide',
            'email' => 'invalide@roisdusahel.ne',
            'cycle_ids' => [$cycleMaternelle->id, $cycleCollege->id],
            'speciality' => 'Général',
            'employment_type' => 'PERMANENT',
        ]);

        $response->assertSessionHasErrors(['cycle_ids']);

        // Attempt Maternelle + Primaire
        $response2 = $this->actingAs($user)->post('/teachers', [
            'name' => 'Prof Invalide 2',
            'email' => 'invalide2@roisdusahel.ne',
            'cycle_ids' => [$cycleMaternelle->id, $cyclePrimaire->id],
            'speciality' => 'Général',
            'employment_type' => 'PERMANENT',
        ]);

        $response2->assertSessionHasErrors(['cycle_ids']);
    }
}
