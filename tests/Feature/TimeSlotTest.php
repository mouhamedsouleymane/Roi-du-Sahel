<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeSlotTest extends TestCase
{
    use RefreshDatabase;

    public function test_time_slots_index_can_be_rendered(): void
    {
        $user = User::factory()->create();
        TimeSlot::factory()->create([
            'label' => '08:00 - 09:00',
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        $response = $this->actingAs($user)->get(route('time-slots.index'));

        $response->assertStatus(200);
        $response->assertSee('Plages Horaires');
        $response->assertSee('08:00 - 09:00');
    }

    public function test_time_slot_create_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('time-slots.create'));

        $response->assertStatus(200);
        $response->assertSee('Nouvelle Plage Horaire');
    }

    public function test_time_slot_can_be_created_with_valid_data(): void
    {
        $user = User::factory()->create();
        $cycle = Cycle::create(['name' => 'Collège', 'code' => 'COL', 'is_active' => true]);

        $response = $this->actingAs($user)->post(route('time-slots.store'), [
            'name' => 'Cours du matin 1',
            'label' => '07:30 - 08:30',
            'start_time' => '07:30',
            'end_time' => '08:30',
            'cycle_id' => $cycle->id,
            'order_index' => 1,
            'is_pause' => 0,
            'is_active' => 1,
            'color' => '#8b5cf6',
        ]);

        $response->assertRedirect(route('time-slots.index'));
        $this->assertDatabaseHas('time_slots', [
            'label' => '07:30 - 08:30',
            'start_time' => '07:30:00',
            'end_time' => '08:30:00',
            'cycle_id' => $cycle->id,
        ]);
    }

    public function test_time_slot_creation_validates_end_time_after_start_time(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('time-slots.store'), [
            'label' => 'Invalide',
            'start_time' => '10:00',
            'end_time' => '09:00',
        ]);

        $response->assertSessionHasErrors('end_time');
    }

    public function test_time_slot_edit_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $slot = TimeSlot::factory()->create();

        $response = $this->actingAs($user)->get(route('time-slots.edit', $slot));

        $response->assertStatus(200);
        $response->assertSee('Modifier la Plage Horaire');
    }

    public function test_time_slot_can_be_updated(): void
    {
        $user = User::factory()->create();
        $slot = TimeSlot::factory()->create([
            'label' => 'Ancien Libellé',
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        $response = $this->actingAs($user)->put(route('time-slots.update', $slot), [
            'name' => 'Mis à jour',
            'label' => '08:15 - 09:15',
            'start_time' => '08:15',
            'end_time' => '09:15',
            'order_index' => 2,
            'is_pause' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('time-slots.index'));
        $slot->refresh();
        $this->assertEquals('08:15 - 09:15', $slot->label);
        $this->assertTrue((bool) $slot->is_pause);
    }

    public function test_time_slot_status_can_be_toggled(): void
    {
        $user = User::factory()->create();
        $slot = TimeSlot::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->post(route('time-slots.toggle-status', $slot));

        $response->assertRedirect(route('time-slots.index'));
        $this->assertFalse((bool) $slot->fresh()->is_active);
    }

    public function test_time_slot_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $slot = TimeSlot::factory()->create();

        $response = $this->actingAs($user)->delete(route('time-slots.destroy', $slot));

        $response->assertRedirect(route('time-slots.index'));
        $this->assertDatabaseMissing('time_slots', ['id' => $slot->id]);
    }

    public function test_time_slot_is_rendered_in_schedules_index(): void
    {
        $user = User::factory()->create();
        $year = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $cycle = Cycle::create(['code' => 'COL', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A']);

        TimeSlot::create([
            'label' => '07:15 - 08:15',
            'start_time' => '07:15',
            'end_time' => '08:15',
            'order_index' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('schedules.index', ['class_id' => $class->id]));

        $response->assertStatus(200);
        $response->assertSee('07:15 - 08:15');
    }
}

