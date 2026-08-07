<?php

namespace Tests\Feature;

use App\Models\Guardian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuardianTest extends TestCase
{
    use RefreshDatabase;

    public function test_guardians_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        Guardian::create([
            'first_name' => 'Moussa',
            'last_name' => 'Souley',
            'relationship' => 'PERE',
            'phone_primary' => '+227 90 11 22 33',
        ]);

        $response = $this->actingAs($user)->get('/guardians');

        $response->assertStatus(200);
        $response->assertSee('Moussa');
        $response->assertSee('+227 90 11 22 33');
    }
}
