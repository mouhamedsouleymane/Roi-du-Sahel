<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicYearTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_years_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/academic-years');

        $response->assertStatus(200);
        $response->assertSee('2025-2026');
    }

    public function test_academic_year_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/academic-years', [
            'name' => '2026-2027',
            'start_date' => '2026-09-15',
            'end_date' => '2027-06-30',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/academic-years');
        $this->assertDatabaseHas('academic_years', [
            'name' => '2026-2027',
            'is_active' => true,
        ]);
    }

    public function test_academic_year_can_be_activated(): void
    {
        $user = User::factory()->create();

        $year1 = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-15',
            'end_date' => '2025-06-30',
            'is_active' => true,
        ]);

        $year2 = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->patch("/academic-years/{$year2->id}/activate");

        $response->assertRedirect('/academic-years');
        $this->assertFalse($year1->fresh()->is_active);
        $this->assertTrue($year2->fresh()->is_active);
    }
}
