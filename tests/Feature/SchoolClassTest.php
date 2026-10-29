<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SchoolClassTest extends TestCase
{
    use RefreshDatabase;

    private function makeYear(): AcademicYear
    {
        return AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);
    }

    private function makeLevel(): Level
    {
        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);

        return Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième', 'order_index' => 1]);
    }

    public function test_classes_index_can_be_rendered(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $year = $this->makeYear();
        $level = $this->makeLevel();
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A', 'capacity' => 40]);

        DB::table('students')->insert([
            'id' => 1, 'matricule' => 'MAT001', 'first_name' => 'Jean', 'last_name' => 'Dupont',
            'birth_date' => '2010-01-01', 'birth_place' => 'Niamey', 'gender' => 'M',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('enrollments')->insert([
            'student_id' => 1, 'class_id' => $class->id, 'academic_year_id' => $year->id,
            'enrollment_number' => 'INS-001', 'type' => 'NOUVEAU', 'status' => 'VALIDE',
            'enrollment_date' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($user)->get('/classes')
            ->assertStatus(200)
            ->assertSee('6ème A')
            ->assertSee('1/40');
    }

    public function test_classes_create_page_can_be_rendered(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->makeYear();

        $this->actingAs($user)->get('/classes/create')->assertStatus(200);
    }

    public function test_class_can_be_created(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $year = $this->makeYear();
        $level = $this->makeLevel();

        $response = $this->actingAs($user)->post('/classes', [
            'academic_year_id' => $year->id,
            'level_id' => $level->id,
            'name' => 'Terminale D1',
            'capacity' => 45,
            'room_number' => 'Salle 15',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('classes', ['name' => 'Terminale D1', 'capacity' => 45]);
    }

    public function test_class_name_must_be_unique_per_year_and_level(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $year = $this->makeYear();
        $level = $this->makeLevel();
        SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A', 'capacity' => 40]);

        $response = $this->actingAs($user)->post('/classes', [
            'academic_year_id' => $year->id,
            'level_id' => $level->id,
            'name' => '6ème A',
            'capacity' => 40,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_class_show_page_can_be_rendered(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $year = $this->makeYear();
        $level = $this->makeLevel();
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A', 'capacity' => 40]);

        $this->actingAs($user)->get("/classes/{$class->id}")->assertStatus(200)->assertSee('6ème A');
    }

    public function test_class_show_page_displays_gender_breakdown(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $year = $this->makeYear();
        $level = $this->makeLevel();
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A', 'capacity' => 40]);

        DB::table('students')->insert([
            ['id' => 1, 'matricule' => 'MAT001', 'first_name' => 'Ibrahim', 'last_name' => 'Ali', 'birth_date' => '2010-01-01', 'birth_place' => 'Niamey', 'gender' => 'M', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'matricule' => 'MAT002', 'first_name' => 'Fatima', 'last_name' => 'Sani', 'birth_date' => '2010-02-02', 'birth_place' => 'Niamey', 'gender' => 'F', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('enrollments')->insert([
            ['student_id' => 1, 'class_id' => $class->id, 'academic_year_id' => $year->id, 'enrollment_number' => 'INS-001', 'type' => 'NOUVEAU', 'status' => 'VALIDE', 'enrollment_date' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['student_id' => 2, 'class_id' => $class->id, 'academic_year_id' => $year->id, 'enrollment_number' => 'INS-002', 'type' => 'NOUVEAU', 'status' => 'VALIDE', 'enrollment_date' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->actingAs($user)->get("/classes/{$class->id}")
            ->assertStatus(200)
            ->assertSee('Par Genre')
            ->assertSee('Garçons')
            ->assertSee('Filles');
    }

    public function test_class_can_be_updated(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $year = $this->makeYear();
        $level = $this->makeLevel();
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A', 'capacity' => 40]);

        $response = $this->actingAs($user)->put("/classes/{$class->id}", [
            'level_id' => $level->id,
            'name' => '6ème A',
            'capacity' => 35,
            'room_number' => 'Salle 5',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('classes', ['id' => $class->id, 'capacity' => 35, 'room_number' => 'Salle 5']);
    }

    public function test_class_can_be_deleted_if_no_enrollments(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $year = $this->makeYear();
        $level = $this->makeLevel();
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A', 'capacity' => 40]);

        $response = $this->actingAs($user)->delete("/classes/{$class->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('classes', ['id' => $class->id]);
    }

    public function test_class_cannot_be_deleted_if_has_enrollments(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $year = $this->makeYear();
        $level = $this->makeLevel();
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A', 'capacity' => 40]);

        DB::table('students')->insert([
            'id' => 1, 'matricule' => 'MAT001', 'first_name' => 'Jean', 'last_name' => 'Dupont',
            'birth_date' => '2010-01-01', 'birth_place' => 'Niamey', 'gender' => 'M',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('enrollments')->insert([
            'student_id' => 1, 'class_id' => $class->id, 'academic_year_id' => $year->id,
            'enrollment_number' => 'INS-001', 'type' => 'NOUVEAU', 'status' => 'VALIDE',
            'enrollment_date' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->delete("/classes/{$class->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('classes', ['id' => $class->id]);
    }

    public function test_classes_index_displays_a_label_for_each_action_button(): void
    {
        $user = User::factory()->create();
        $year = $this->makeYear();
        $level = $this->makeLevel();
        SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A', 'capacity' => 40]);

        $response = $this->actingAs($user)->get('/classes');

        $response->assertOk();
        $response->assertSee('👁️ Voir', false);
        $response->assertSee('📅 Emploi du temps', false);
        $response->assertSee('✏️ Modifier', false);
        $response->assertSee('🗑️ Supprimer', false);
    }

    public function test_class_delete_button_is_disabled_when_the_class_has_enrollments(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $year = $this->makeYear();
        $level = $this->makeLevel();
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A', 'capacity' => 40]);

        DB::table('students')->insert([
            'id' => 1, 'matricule' => 'MAT001', 'first_name' => 'Jean', 'last_name' => 'Dupont',
            'birth_date' => '2010-01-01', 'birth_place' => 'Niamey', 'gender' => 'M',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('enrollments')->insert([
            'student_id' => 1, 'class_id' => $class->id, 'academic_year_id' => $year->id,
            'enrollment_number' => 'INS-001', 'type' => 'NOUVEAU', 'status' => 'VALIDE',
            'enrollment_date' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/classes');

        $response->assertOk();
        $response->assertSee('Impossible de supprimer : 1 inscription(s) rattachée(s)', false);
        $response->assertDontSee('value="DELETE"', false);
    }

    public function test_class_delete_button_is_available_when_the_class_has_no_enrollments(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $year = $this->makeYear();
        $level = $this->makeLevel();
        SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A', 'capacity' => 40]);

        $response = $this->actingAs($user)->get('/classes');

        $response->assertOk();
        $response->assertSee('value="DELETE"', false);
        $response->assertDontSee('Impossible de supprimer', false);
    }

    public function test_guest_cannot_access_classes(): void
    {
        $this->get('/classes')->assertRedirect('/login');
    }
}
