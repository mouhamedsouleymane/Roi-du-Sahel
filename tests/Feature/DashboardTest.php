<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_dashboard_is_accessible_to_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Tableau de Bord');
    }

    public function test_dashboard_shows_active_year_info(): void
    {
        $user = User::factory()->create();

        AcademicYear::create([
            'name'       => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date'   => '2026-06-30',
            'is_active'  => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('2025-2026');
    }

    public function test_dashboard_shows_kpi_cards(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Élèves inscrits');
        $response->assertSee('Enseignants actifs');
        $response->assertSee('Classes ouvertes');
    }

    public function test_stats_page_is_accessible(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/stats');

        $response->assertStatus(200);
        $response->assertSee('Statistiques');
    }
}
