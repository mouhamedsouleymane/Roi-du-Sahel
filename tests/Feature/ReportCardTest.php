<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Level;
use App\Models\Period;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_cards_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/report-cards');

        $response->assertStatus(200);
        $response->assertSee('Bulletins de Notes');
    }

    public function test_report_card_show_displays_student_bulletin(): void
    {
        $user = User::factory()->create();

        $year = AcademicYear::create([
            'name' => '2025-2026', 'start_date' => '2025-09-15',
            'end_date' => '2026-06-30', 'is_active' => true,
        ]);
        $period = Period::create([
            'academic_year_id' => $year->id,
            'name' => 'Trimestre 1', 'type' => 'TRIMESTRE', 'order' => 1,
            'start_date' => '2025-09-15', 'end_date' => '2025-12-14',
        ]);
        $cycle   = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level   = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $class   = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A']);

        $student = Student::create([
            'matricule' => 'RS-2025-0001', 'first_name' => 'Mariam', 'last_name' => 'Diallo',
            'birth_date' => '2010-01-01', 'gender' => 'F',
        ]);

        $reportCard = ReportCard::create([
            'student_id'      => $student->id,
            'period_id'       => $period->id,
            'class_id'        => $class->id,
            'general_average' => 15.50,
            'rank'            => 1,
            'total_students'  => 30,
            'is_published'    => true,
        ]);

        $response = $this->actingAs($user)->get("/report-cards/{$reportCard->id}");

        $response->assertStatus(200);
        $response->assertSee('Mariam');
        $response->assertSee('15.50');
    }
}
