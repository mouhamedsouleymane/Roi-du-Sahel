<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private function createBaseData(): array
    {
        $year = AcademicYear::create([
            'name' => '2025-2026', 'start_date' => '2025-09-15',
            'end_date' => '2026-06-30', 'is_active' => true,
        ]);
        $cycle   = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level   = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $class   = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A']);
        $student = Student::create([
            'matricule' => 'RS-2025-0001', 'first_name' => 'Ali',
            'last_name' => 'Oumarou', 'birth_date' => '2010-01-01', 'gender' => 'M',
        ]);
        $enrollment = Enrollment::create([
            'student_id' => $student->id, 'academic_year_id' => $year->id,
            'class_id' => $class->id, 'status' => 'VALIDE',
        ]);
        $feeType = FeeType::create([
            'code' => 'INSCRIPT', 'name' => "Frais d'inscription",
        ]);

        return compact('year', 'student', 'enrollment', 'feeType');
    }

    public function test_invoices_page_requires_authentication(): void
    {
        $this->get('/invoices')->assertRedirect('/login');
    }

    public function test_invoices_page_is_accessible(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/invoices')->assertStatus(200);
    }

    public function test_fee_type_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/invoices/fee-types', [
            'code'         => 'CANTINE',
            'name'         => 'Frais de cantine',
            'is_recurring' => true,
        ]);

        $response->assertRedirect(route('invoices.settings'));
        $this->assertDatabaseHas('fee_types', ['code' => 'CANTINE']);
    }

    public function test_payment_can_be_recorded_against_invoice(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        $invoice = Invoice::create([
            'invoice_number'   => 'FACT-2025-00001',
            'student_id'       => $data['student']->id,
            'enrollment_id'    => $data['enrollment']->id,
            'academic_year_id' => $data['year']->id,
            'fee_type_id'      => $data['feeType']->id,
            'amount_due'       => 25000,
            'amount_paid'      => 0,
            'status'           => 'IMPAYEE',
        ]);

        $response = $this->actingAs($user)->post("/invoices/{$invoice->id}/payments", [
            'amount'         => 10000,
            'payment_method' => 'ESPECES',
            'payment_date'   => date('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount'     => 10000,
        ]);

        $invoice->refresh();
        $this->assertEquals(10000, $invoice->amount_paid);
        $this->assertEquals('PARTIELLE', $invoice->status);
    }

    public function test_invoice_status_becomes_payee_when_fully_paid(): void
    {
        $user = User::factory()->create();
        $data = $this->createBaseData();

        $invoice = Invoice::create([
            'invoice_number'   => 'FACT-2025-00002',
            'student_id'       => $data['student']->id,
            'enrollment_id'    => $data['enrollment']->id,
            'academic_year_id' => $data['year']->id,
            'fee_type_id'      => $data['feeType']->id,
            'amount_due'       => 25000,
            'amount_paid'      => 0,
            'status'           => 'IMPAYEE',
        ]);

        $this->actingAs($user)->post("/invoices/{$invoice->id}/payments", [
            'amount'         => 25000,
            'payment_method' => 'MOBILE_MONEY',
            'payment_date'   => date('Y-m-d'),
        ]);

        $invoice->refresh();
        $this->assertEquals('PAYEE', $invoice->status);
    }
}
