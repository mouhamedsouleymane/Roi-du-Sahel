<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolClassTest extends TestCase
{
    use RefreshDatabase;

    public function test_classes_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $year = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième (6ème)']);

        SchoolClass::create([
            'academic_year_id' => $year->id,
            'level_id' => $level->id,
            'name' => '6ème A',
            'capacity' => 40,
        ]);

        $response = $this->actingAs($user)->get('/classes');

        $response->assertStatus(200);
        $response->assertSee('6ème A');
    }

    public function test_class_can_be_created(): void
    {
        $user = User::factory()->create();

        $year = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $cycle = Cycle::create(['code' => 'LYCEE', 'name' => 'Lycée']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => 'TLE_D', 'name' => 'Terminale D']);

        $response = $this->actingAs($user)->post('/classes', [
            'academic_year_id' => $year->id,
            'level_id' => $level->id,
            'name' => 'Terminale D1',
            'capacity' => 45,
            'room_number' => 'Salle 15',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('classes', [
            'name' => 'Terminale D1',
            'capacity' => 45,
        ]);
    }
}
