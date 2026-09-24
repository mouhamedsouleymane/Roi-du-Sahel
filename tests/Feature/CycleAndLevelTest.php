<?php

namespace Tests\Feature;

use App\Models\Cycle;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CycleAndLevelTest extends TestCase
{
    use RefreshDatabase;

    // ==================== TESTS CYCLES ====================

    public function test_cycles_index_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $cycle = Cycle::create([
            'code' => 'COLLEGE',
            'name' => 'Collège',
            'uniform_tshirt_color' => 'Jaune',
        ]);
        Level::create([
            'cycle_id' => $cycle->id,
            'code' => '6EME',
            'name' => 'Sixième',
            'order_index' => 1,
        ]);

        $response = $this->actingAs($user)->get('/cycles');

        $response->assertStatus(200);
        $response->assertSee('Collège');
        $response->assertSee('Sixième');
    }

    public function test_cycle_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/cycles', [
            'code' => 'LYCEE',
            'name' => 'Lycée',
            'uniform_tshirt_color' => 'Jaune',
            'start_time' => '08:00',
            'end_time' => '13:30',
        ]);

        $response->assertRedirect(route('cycles.index'));
        $this->assertDatabaseHas('cycles', [
            'code' => 'LYCEE',
            'name' => 'Lycée',
        ]);
    }

    public function test_cycle_show_page_displays_levels_and_classes(): void
    {
        $user = User::factory()->create();
        $cycle = Cycle::create([
            'code' => 'PRIMAIRE',
            'name' => 'Primaire',
            'uniform_tshirt_color' => 'Violet',
        ]);
        $level = Level::create([
            'cycle_id' => $cycle->id,
            'code' => 'CM2',
            'name' => 'Cours Moyen 2',
            'order_index' => 1,
        ]);

        $response = $this->actingAs($user)->get("/cycles/{$cycle->id}");

        $response->assertStatus(200);
        $response->assertSee('Primaire');
        $response->assertSee('Cours Moyen 2');
    }

    public function test_cycle_can_be_updated(): void
    {
        $user = User::factory()->create();
        $cycle = Cycle::create([
            'code' => 'MATERNELLE',
            'name' => 'Maternelle',
            'uniform_tshirt_color' => 'Violet',
        ]);

        $response = $this->actingAs($user)->put("/cycles/{$cycle->id}", [
            'code' => 'MATERNELLE',
            'name' => 'Maternelle Update',
            'uniform_tshirt_color' => 'Vert',
            'start_time' => '08:30',
            'end_time' => '12:00',
        ]);

        $response->assertRedirect(route('cycles.index'));
        $this->assertDatabaseHas('cycles', [
            'id' => $cycle->id,
            'name' => 'Maternelle Update',
            'uniform_tshirt_color' => 'Vert',
        ]);
    }

    public function test_cycle_can_be_toggled_active(): void
    {
        $user = User::factory()->create();
        $cycle = Cycle::create([
            'code' => 'TEST',
            'name' => 'Test Cycle',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->patch("/cycles/{$cycle->id}/toggle-active");

        $response->assertRedirect(route('cycles.index'));
        $this->assertFalse($cycle->refresh()->is_active);

        $this->actingAs($user)->patch("/cycles/{$cycle->id}/toggle-active");
        $this->assertTrue($cycle->refresh()->is_active);
    }

    public function test_cycle_cannot_be_deleted_if_it_has_levels(): void
    {
        $user = User::factory()->create();
        $cycle = Cycle::create(['code' => 'TEST', 'name' => 'Test']);
        Level::create([
            'cycle_id' => $cycle->id,
            'code' => 'TEST_LEVEL',
            'name' => 'Test Level',
            'order_index' => 1,
        ]);

        $response = $this->actingAs($user)->delete("/cycles/{$cycle->id}");

        $response->assertRedirect(route('cycles.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('cycles', ['id' => $cycle->id]);
    }

    public function test_cycle_can_be_deleted_if_empty(): void
    {
        $user = User::factory()->create();
        $cycle = Cycle::create(['code' => 'TEST', 'name' => 'Test']);

        $response = $this->actingAs($user)->delete("/cycles/{$cycle->id}");

        $response->assertRedirect(route('cycles.index'));
        $this->assertDatabaseMissing('cycles', ['id' => $cycle->id]);
    }

    public function test_cycle_code_must_be_unique(): void
    {
        $user = User::factory()->create();
        Cycle::create(['code' => 'UNIQUE', 'name' => 'First']);

        $response = $this->actingAs($user)->post('/cycles', [
            'code' => 'UNIQUE',
            'name' => 'Second',
        ]);

        $response->assertSessionHasErrors('code');
    }

    // ==================== TESTS NIVEAUX ====================

    public function test_level_can_be_added_to_cycle(): void
    {
        $user = User::factory()->create();
        $cycle = Cycle::create(['code' => 'TEST', 'name' => 'Test']);

        $response = $this->actingAs($user)->post("/cycles/{$cycle->id}/levels", [
            'code' => 'L1',
            'name' => 'Level 1',
            'order_index' => 1,
        ]);

        $response->assertRedirect(route('cycles.show', $cycle));
        $this->assertDatabaseHas('levels', [
            'cycle_id' => $cycle->id,
            'code' => 'L1',
            'name' => 'Level 1',
        ]);
    }

    public function test_level_code_must_be_unique_within_cycle(): void
    {
        $user = User::factory()->create();
        $cycle = Cycle::create(['code' => 'TEST', 'name' => 'Test']);
        Level::create([
            'cycle_id' => $cycle->id,
            'code' => 'L1',
            'name' => 'Level 1',
            'order_index' => 1,
        ]);

        $response = $this->actingAs($user)->post("/cycles/{$cycle->id}/levels", [
            'code' => 'L1',
            'name' => 'Another Level 1',
            'order_index' => 2,
        ]);

        $response->assertSessionHas('error');
    }

    public function test_level_can_be_deleted_if_no_classes(): void
    {
        $user = User::factory()->create();
        $cycle = Cycle::create(['code' => 'TEST', 'name' => 'Test']);
        $level = Level::create([
            'cycle_id' => $cycle->id,
            'code' => 'L1',
            'name' => 'Level 1',
            'order_index' => 1,
        ]);

        $response = $this->actingAs($user)->delete("/cycles/{$cycle->id}/levels/{$level->id}");

        $response->assertRedirect(route('cycles.show', $cycle));
        $this->assertDatabaseMissing('levels', ['id' => $level->id]);
    }

    public function test_level_cannot_be_deleted_if_has_classes(): void
    {
        $user = User::factory()->create();
        $cycle = Cycle::create(['code' => 'TEST', 'name' => 'Test']);
        $level = Level::create([
            'cycle_id' => $cycle->id,
            'code' => 'L1',
            'name' => 'Level 1',
            'order_index' => 1,
        ]);

        // Create an academic year first (required for classes)
        \DB::table('academic_years')->insert([
            'id' => 1,
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Use raw insert to bypass FK constraints in SQLite test
        \DB::table('classes')->insert([
            'name' => 'Classe Test',
            'level_id' => $level->id,
            'academic_year_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->delete("/cycles/{$cycle->id}/levels/{$level->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('levels', ['id' => $level->id]);
    }

    public function test_guest_cannot_access_cycles(): void
    {
        $response = $this->get('/cycles');
        $response->assertRedirect('/login');
    }
}
