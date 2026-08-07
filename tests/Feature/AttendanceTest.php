<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private function createBaseData(): array
    {
        $year = AcademicYear::create([
            'name'       => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date'   => '2026-06-30',
            'is_active'  => true,
        ]);
        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A']);

        $student = Student::create([
            'matricule'  => 'RS-2025-0001',
            'first_name' => 'Ibrahim',
            'last_name'  => 'Sani',
            'birth_date' => '2010-01-01',
            'gender'     => 'M',
        ]);

        Enrollment::create([
            'student_id'       => $student->id,
            'academic_year_id' => $year->id,
            'class_id'         => $class->id,
            'status'           => 'VALIDE',
        ]);

        return compact('year', 'class', 'student');
    }

    public function test_attendances_page_requires_authentication(): void
    {
        $response = $this->get('/attendances');
        $response->assertRedirect('/login');
    }

    public function test_attendances_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendances');

        $response->assertStatus(200);
        $response->assertSee('Feuille de Présence');
    }

    public function test_attendance_sheet_can_be_saved(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        $response = $this->actingAs($user)->post('/attendances', [
            'class_id'        => $data['class']->id,
            'attendance_date' => '2025-10-20',
            'session'         => 'MATIN',
            'attendances'     => [
                [
                    'student_id' => $data['student']->id,
                    'status'     => 'ABSENT',
                    'reason'     => 'Maladie',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'student_id'      => $data['student']->id,
            'class_id'        => $data['class']->id,
            'attendance_date' => '2025-10-20',
            'session'         => 'MATIN',
            'status'          => 'ABSENT',
            'reason'          => 'Maladie',
        ]);
    }

    public function test_attendance_report_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendances/report');

        $response->assertStatus(200);
        $response->assertSee('Bilan');
    }
}
