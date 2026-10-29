<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\Period;
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
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);
        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A']);

        $student = Student::create([
            'matricule' => 'RS-2025-0001',
            'first_name' => 'Ibrahim',
            'last_name' => 'Sani',
            'birth_date' => '2010-01-01',
            'birth_place' => 'Niamey',
            'gender' => 'M',
        ]);

        Enrollment::create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'class_id' => $class->id,
            'enrollment_number' => 'INS-2025-0001',
            'enrollment_date' => '2025-09-15',
            'status' => 'VALIDE',
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
    }

    public function test_attendance_create_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        $response = $this->actingAs($user)->get('/attendances/create?'.http_build_query([
            'class_id' => $data['class']->id,
            'date' => '2025-10-20',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Nouvelle Feuille');
        $response->assertSee('Sani');
    }

    public function test_attendance_sheet_can_be_saved(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        $response = $this->actingAs($user)->post('/attendances', [
            'class_id' => $data['class']->id,
            'attendance_date' => '2025-10-20',
            'attendances' => [
                [
                    'student_id' => $data['student']->id,
                    'status' => 'ABSENT',
                    'reason' => 'Maladie',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'student_id' => $data['student']->id,
            'class_id' => $data['class']->id,
            'status' => 'ABSENT',
            'reason' => 'Maladie',
        ]);
    }

    public function test_attendance_show_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        Attendance::create([
            'student_id' => $data['student']->id,
            'class_id' => $data['class']->id,
            'academic_year_id' => $data['year']->id,
            'attendance_date' => '2025-10-20',
            'status' => 'PRESENT',
        ]);

        $response = $this->actingAs($user)->get('/attendances/class?'.http_build_query([
            'class_id' => $data['class']->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('6ème A');

        $sheetResponse = $this->actingAs($user)->get('/attendances/sheet?'.http_build_query([
            'class_id' => $data['class']->id,
            'week_start' => '2025-10-20',
        ]));

        $sheetResponse->assertStatus(200);
        $sheetResponse->assertSee('Sani');
    }

    public function test_attendance_edit_page_is_prefilled_and_update_works(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        Attendance::create([
            'student_id' => $data['student']->id,
            'class_id' => $data['class']->id,
            'academic_year_id' => $data['year']->id,
            'attendance_date' => '2025-10-20',
            'status' => 'PRESENT',
        ]);

        $response = $this->actingAs($user)->get('/attendances/edit?'.http_build_query([
            'class_id' => $data['class']->id,
            'date' => '2025-10-20',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Corriger la Feuille');

        $response = $this->actingAs($user)->put('/attendances', [
            'class_id' => $data['class']->id,
            'attendance_date' => '2025-10-20',
            'attendances' => [
                [
                    'student_id' => $data['student']->id,
                    'status' => 'RETARD',
                    'reason' => 'Embouteillage',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'student_id' => $data['student']->id,
            'class_id' => $data['class']->id,
            'status' => 'RETARD',
            'reason' => 'Embouteillage',
        ]);
    }

    public function test_attendance_edit_is_forbidden_in_closed_period(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        Period::create([
            'academic_year_id' => $data['year']->id,
            'name' => 'Trimestre 1',
            'type' => 'TRIMESTRE',
            'order' => 1,
            'start_date' => '2025-09-15',
            'end_date' => '2025-12-15',
            'is_closed' => true,
        ]);

        $response = $this->actingAs($user)->get('/attendances/edit?'.http_build_query([
            'class_id' => $data['class']->id,
            'date' => '2025-10-20',
        ]));

        $response->assertForbidden();
    }

    public function test_attendance_report_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendances/report');

        $response->assertStatus(200);
        $response->assertSee('Bilan');
    }

    public function test_attendance_report_rejects_inverted_dates(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        $response = $this->actingAs($user)->get('/attendances/report?'.http_build_query([
            'class_id' => $data['class']->id,
            'start_date' => '2025-11-10',
            'end_date' => '2025-10-01',
        ]));

        $response->assertSessionHasErrors('end_date');
    }

    public function test_attendance_cannot_be_saved_for_student_outside_class(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        $outsider = Student::create([
            'matricule' => 'RS-2025-9999',
            'first_name' => 'Hors',
            'last_name' => 'Classe',
            'birth_date' => '2010-01-01',
            'birth_place' => 'Niamey',
            'gender' => 'F',
        ]);

        $response = $this->actingAs($user)->post('/attendances', [
            'class_id' => $data['class']->id,
            'attendance_date' => '2025-10-20',
            'attendances' => [
                [
                    'student_id' => $outsider->id,
                    'status' => 'PRESENT',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('attendances.0.student_id');
        $this->assertDatabaseMissing('attendances', [
            'student_id' => $outsider->id,
            'class_id' => $data['class']->id,
        ]);
    }

    public function test_attendance_cannot_be_saved_with_future_date(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        $response = $this->actingAs($user)->post('/attendances', [
            'class_id' => $data['class']->id,
            'attendance_date' => today()->addDay()->toDateString(),
            'attendances' => [
                [
                    'student_id' => $data['student']->id,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('attendance_date');
    }

    public function test_attendance_cannot_be_saved_in_closed_period(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        Period::create([
            'academic_year_id' => $data['year']->id,
            'name' => 'Trimestre 1',
            'type' => 'TRIMESTRE',
            'order' => 1,
            'start_date' => '2025-09-15',
            'end_date' => '2025-12-15',
            'is_closed' => true,
        ]);

        $response = $this->actingAs($user)->post('/attendances', [
            'class_id' => $data['class']->id,
            'attendance_date' => '2025-10-20',
            'attendances' => [
                ['student_id' => $data['student']->id, 'status' => 'PRESENT'],
            ],
        ]);

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseMissing('attendances', [
            'student_id' => $data['student']->id,
            'class_id' => $data['class']->id,
            'attendance_date' => '2025-10-20',
        ]);
    }

    public function test_attendance_sheet_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        $this->actingAs($user)->post('/attendances', [
            'class_id' => $data['class']->id,
            'attendance_date' => '2025-10-20',
            'attendances' => [
                ['student_id' => $data['student']->id, 'status' => 'ABSENT'],
            ],
        ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $data['student']->id,
            'class_id' => $data['class']->id,
        ]);

        $response = $this->actingAs($user)->delete('/attendances', [
            'class_id' => $data['class']->id,
            'attendance_date' => '2025-10-20',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('attendances', [
            'student_id' => $data['student']->id,
            'class_id' => $data['class']->id,
            'attendance_date' => '2025-10-20',
        ]);
    }

    public function test_attendance_sheet_cannot_be_deleted_in_closed_period(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        Attendance::create([
            'student_id' => $data['student']->id,
            'class_id' => $data['class']->id,
            'academic_year_id' => $data['year']->id,
            'attendance_date' => '2025-10-20',
            'status' => 'PRESENT',
        ]);

        Period::create([
            'academic_year_id' => $data['year']->id,
            'name' => 'Trimestre 1',
            'type' => 'TRIMESTRE',
            'order' => 1,
            'start_date' => '2025-09-15',
            'end_date' => '2025-12-15',
            'is_closed' => true,
        ]);

        $response = $this->actingAs($user)->delete('/attendances', [
            'class_id' => $data['class']->id,
            'attendance_date' => '2025-10-20',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('attendances', [
            'student_id' => $data['student']->id,
            'class_id' => $data['class']->id,
            'status' => 'PRESENT',
        ]);
    }
}
