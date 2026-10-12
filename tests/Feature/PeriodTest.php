<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_period_cannot_be_closed_before_its_end_date(): void
    {
        $user = User::factory()->create();
        $period = $this->makePeriod(today()->addMonths(2)->toDateString());

        $response = $this->actingAs($user)->patch($this->toggleClosePath($period));

        $response->assertSessionHas('error');
        $this->assertFalse($period->fresh()->is_closed);
    }

    public function test_period_cannot_be_closed_on_its_end_date(): void
    {
        $user = User::factory()->create();
        $period = $this->makePeriod(today()->toDateString());

        $response = $this->actingAs($user)->patch($this->toggleClosePath($period));

        $response->assertSessionHas('error');
        $this->assertFalse($period->fresh()->is_closed);
    }

    public function test_period_can_be_closed_after_its_end_date(): void
    {
        $user = User::factory()->create();
        $period = $this->makePeriod(today()->subDay()->toDateString());

        $response = $this->actingAs($user)->patch($this->toggleClosePath($period));

        $response->assertRedirect("/academic-years/{$period->academic_year_id}");
        $this->assertTrue($period->fresh()->is_closed);
    }

    public function test_closed_period_can_be_reopened_before_its_end_date(): void
    {
        $user = User::factory()->create();
        $period = $this->makePeriod(today()->addMonths(2)->toDateString(), isClosed: true);

        $response = $this->actingAs($user)->patch($this->toggleClosePath($period));

        $response->assertRedirect("/academic-years/{$period->academic_year_id}");
        $response->assertSessionMissing('error');
        $this->assertFalse($period->fresh()->is_closed);
    }

    public function test_close_button_is_disabled_while_the_period_is_still_in_progress(): void
    {
        $user = User::factory()->create();
        $period = $this->makePeriod(today()->addMonths(2)->toDateString());

        $response = $this->actingAs($user)->get("/academic-years/{$period->academic_year_id}");

        $response->assertOk();
        $response->assertSee('Impossible de clôturer', false);
        $response->assertDontSee('toggle-close', false);
    }

    public function test_close_button_is_available_once_the_period_has_ended(): void
    {
        $user = User::factory()->create();
        $period = $this->makePeriod(today()->subDay()->toDateString());

        $response = $this->actingAs($user)->get("/academic-years/{$period->academic_year_id}");

        $response->assertOk();
        $response->assertSee('toggle-close', false);
        $response->assertDontSee('Impossible de clôturer', false);
    }

    private function makePeriod(string $endDate, bool $isClosed = false): Period
    {
        $year = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => today()->subMonths(9)->toDateString(),
            'end_date' => today()->addMonths(6)->toDateString(),
        ]);

        return Period::create([
            'academic_year_id' => $year->id,
            'name' => 'Trimestre 1',
            'type' => 'TRIMESTRE',
            'order' => 1,
            'start_date' => today()->subMonths(3)->toDateString(),
            'end_date' => $endDate,
            'is_closed' => $isClosed,
        ]);
    }

    private function toggleClosePath(Period $period): string
    {
        return "/academic-years/{$period->academic_year_id}/periods/{$period->id}/toggle-close";
    }
}
