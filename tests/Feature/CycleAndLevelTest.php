<?php

namespace Tests\Feature;

use App\Models\Cycle;
use App\Models\Level;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CycleAndLevelTest extends TestCase
{
    use RefreshDatabase;

    public function test_cycles_page_can_be_rendered(): void
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
            'name' => 'Sixième (6ème)',
            'order_index' => 1,
        ]);

        $response = $this->actingAs($user)->get('/cycles');

        $response->assertStatus(200);
        $response->assertSee('Collège');
        $response->assertSee('Sixième (6ème)');
    }
}
