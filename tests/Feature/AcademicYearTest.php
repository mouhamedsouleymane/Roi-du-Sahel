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
            'name'       => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date'   => '2026-06-30',
            'is_active'  => true,
        ]);

        $response = $this->actingAs($user)->get('/academic-years');

        $response->assertStatus(200);
        $response->assertSee('2025-2026');
    }

    public function test_academic_year_create_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/academic-years/create')->assertStatus(200);
    }

    public function test_academic_year_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/academic-years', [
            'name'       => '2026-2027',
            'start_date' => '2026-09-15',
            'end_date'   => '2027-06-30',
            'is_active'  => '1',
        ]);

        $response->assertRedirect('/academic-years');
        $this->assertDatabaseHas('academic_years', [
            'name'      => '2026-2027',
            'is_active' => true,
        ]);
    }

    public function test_academic_year_name_must_be_unique(): void
    {
        $user = User::factory()->create();
        AcademicYear::create([
            'name'       => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date'   => '2026-06-30',
        ]);

        $response = $this->actingAs($user)->post('/academic-years', [
            'name'       => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date'   => '2026-06-30',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_academic_year_end_date_must_be_after_start_date(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/academic-years', [
            'name'       => '2026-2027',
            'start_date' => '2026-09-15',
            'end_date'   => '2026-01-01',
        ]);

        $response->assertSessionHasErrors('end_date');
    }

    public function test_academic_year_show_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $year = AcademicYear::create([
            'name'       => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date'   => '2026-06-30',
        ]);

        $this->actingAs($user)->get("/academic-years/{$year->id}")->assertStatus(200)->assertSee('2025-2026');
    }

    public function test_academic_year_can_be_updated(): void
    {
        $user = User::factory()->create();
        $year = AcademicYear::create([
            'name'       => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date'   => '2026-06-30',
        ]);

        $response = $this->actingAs($user)->put("/academic-years/{$year->id}", [
            'name'        => '2025-2026',
            'start_date'  => '2025-09-01',
            'end_date'    => '2026-07-01',
            'description' => 'Mise à jour',
        ]);

        $response->assertRedirect('/academic-years');
        $this->assertDatabaseHas('academic_years', [
            'id'          => $year->id,
            'description' => 'Mise à jour',
        ]);
    }

    public function test_academic_year_can_be_activated(): void
    {
        $user = User::factory()->create();
        $year1 = AcademicYear::create([
            'name'       => '2024-2025',
            'start_date' => '2024-09-15',
            'end_date'   => '2025-06-30',
            'is_active'  => true,
        ]);
        $year2 = AcademicYear::create([
            'name'       => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date'   => '2026-06-30',
            'is_active'  => false,
        ]);

        $response = $this->actingAs($user)->patch("/academic-years/{$year2->id}/activate");

        $response->assertRedirect('/academic-years');
        $this->assertFalse($year1->fresh()->is_active);
        $this->assertTrue($year2->fresh()->is_active);
    }

    public function test_academic_year_can_be_closed(): void
    {
        $user = User::factory()->create();
        $year1 = AcademicYear::create([
            'name'       => '2024-2025',
            'start_date' => '2024-09-15',
            'end_date'   => '2025-06-30',
            'is_active'  => true,
        ]);
        $year2 = AcademicYear::create([
            'name'       => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date'   => '2026-06-30',
            'is_active'  => false,
        ]);

        $response = $this->actingAs($user)->patch("/academic-years/{$year2->id}/toggle-close");

        $response->assertRedirect('/academic-years');
        $this->assertTrue($year2->fresh()->is_closed);
    }

    public function test_active_academic_year_cannot_be_closed(): void
    {
        $user = User::factory()->create();
        $year = AcademicYear::create([
            'name'       => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date'   => '2026-06-30',
            'is_active'  => true,
        ]);

        $response = $this->actingAs($user)->patch("/academic-years/{$year->id}/toggle-close");

        $response->assertRedirect('/academic-years');
        $response->assertSessionHas('error');
        $this->assertFalse($year->fresh()->is_closed);
    }

    public function test_active_academic_year_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $year = AcademicYear::create([
            'name'       => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date'   => '2026-06-30',
            'is_active'  => true,
        ]);

        $response = $this->actingAs($user)->delete("/academic-years/{$year->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('academic_years', ['id' => $year->id]);
    }

    public function test_inactive_academic_year_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $year = AcademicYear::create([
            'name'       => '2023-2024',
            'start_date' => '2023-09-15',
            'end_date'   => '2024-06-30',
            'is_active'  => false,
        ]);

        $response = $this->actingAs($user)->delete("/academic-years/{$year->id}");

        $response->assertRedirect('/academic-years');
        $this->assertDatabaseMissing('academic_years', ['id' => $year->id]);
    }

    public function test_guest_cannot_access_academic_years(): void
    {
        $this->get('/academic-years')->assertRedirect('/login');
    }
}
