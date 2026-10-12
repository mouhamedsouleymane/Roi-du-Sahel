<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Guardian;
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

    private function createUser(): User
    {
        /** @var User $user */
        $user = User::factory()->create();

        return $user;
    }

    private function createBaseData(): array
    {
        $year = AcademicYear::create([
            'name' => '2025-2026', 'start_date' => '2025-09-15',
            'end_date' => '2026-06-30', 'is_active' => true,
        ]);
        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A']);
        $student = Student::create([
            'matricule' => 'RS-2025-0001', 'first_name' => 'Ali',
            'last_name' => 'Oumarou', 'birth_date' => '2010-01-01', 'birth_place' => 'Niamey', 'gender' => 'M',
        ]);
        $enrollment = Enrollment::create([
            'enrollment_number' => 'INS-2025-0001',
            'enrollment_date' => '2025-09-15',
            'student_id' => $student->id, 'academic_year_id' => $year->id,
            'class_id' => $class->id, 'status' => 'VALIDE',
        ]);
        $feeType = FeeType::create([
            'code' => 'INSCRIPT', 'name' => "Frais d'inscription",
        ]);

        return compact('year', 'student', 'enrollment', 'feeType', 'cycle', 'level', 'class');
    }

    public function test_invoices_page_requires_authentication(): void
    {
        $this->get('/invoices')->assertRedirect('/login');
    }

    public function test_invoices_page_is_accessible(): void
    {
        $user = $this->createUser();
        $this->actingAs($user)->get('/invoices')->assertStatus(200);
    }

    public function test_invoices_settings_page_is_accessible(): void
    {
        $user = $this->createUser();
        $this->createBaseData();

        $response = $this->actingAs($user)->get('/invoices/settings');
        $response->assertStatus(200);
        $response->assertSee('Paramètres des Frais');
    }

    public function test_fee_type_can_be_created(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post('/invoices/fee-types', [
            'code' => 'CANTINE',
            'name' => 'Frais de cantine',
            'description' => 'Restauration scolaire',
            'is_recurring' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('invoices.settings'));
        $this->assertDatabaseHas('fee_types', [
            'code' => 'CANTINE',
            'name' => 'Frais de cantine',
            'is_recurring' => 1,
            'is_active' => 1,
        ]);
    }

    public function test_fee_type_can_be_updated(): void
    {
        $user = $this->createUser();
        $feeType = FeeType::create([
            'code' => 'TRANSPORT',
            'name' => 'Frais de transport',
            'is_recurring' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->put("/invoices/fee-types/{$feeType->id}", [
            'code' => 'TRANSPORT',
            'name' => 'Transport Scolaire Mensuel',
            'description' => 'Navette quotidienne',
            'is_recurring' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('invoices.settings'));
        $this->assertDatabaseHas('fee_types', [
            'id' => $feeType->id,
            'name' => 'Transport Scolaire Mensuel',
            'description' => 'Navette quotidienne',
        ]);
    }

    public function test_fee_type_can_be_toggled(): void
    {
        $user = $this->createUser();
        $feeType = FeeType::create([
            'code' => 'TENUE',
            'name' => 'Tenue scolaire',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->patch("/invoices/fee-types/{$feeType->id}/toggle-active");
        $response->assertRedirect(route('invoices.settings'));
        $feeType->refresh();
        $this->assertFalse($feeType->is_active);

        $this->actingAs($user)->patch("/invoices/fee-types/{$feeType->id}/toggle-active");
        $feeType->refresh();
        $this->assertTrue($feeType->is_active);
    }

    public function test_fee_type_can_be_deleted(): void
    {
        $user = $this->createUser();
        $feeType = FeeType::create([
            'code' => 'TEMPORAIRE',
            'name' => 'Frais Temporaires',
        ]);

        $response = $this->actingAs($user)->delete("/invoices/fee-types/{$feeType->id}");
        $response->assertRedirect(route('invoices.settings'));
        $this->assertDatabaseMissing('fee_types', ['id' => $feeType->id]);
    }

    public function test_fee_type_cannot_be_deleted_if_it_has_invoices(): void
    {
        $user = $this->createUser();
        $data = $this->createBaseData();

        Invoice::create([
            'invoice_number' => 'FACT-2025-00999',
            'student_id' => $data['student']->id,
            'enrollment_id' => $data['enrollment']->id,
            'academic_year_id' => $data['year']->id,
            'fee_type_id' => $data['feeType']->id,
            'amount_due' => 30000,
            'amount_paid' => 0,
            'status' => 'IMPAYEE',
        ]);

        $response = $this->actingAs($user)->delete("/invoices/fee-types/{$data['feeType']->id}");
        $response->assertRedirect(route('invoices.settings'));
        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('fee_types', ['id' => $data['feeType']->id]);
    }

    public function test_fee_structure_can_be_created(): void
    {
        $user = $this->createUser();
        $data = $this->createBaseData();

        $response = $this->actingAs($user)->post('/invoices/fee-structures', [
            'fee_type_id' => $data['feeType']->id,
            'cycle_id' => $data['cycle']->id,
            'level_id' => $data['level']->id,
            'amount' => 45000,
        ]);

        $response->assertRedirect(route('invoices.settings'));
        $this->assertDatabaseHas('fee_structures', [
            'fee_type_id' => $data['feeType']->id,
            'academic_year_id' => $data['year']->id,
            'level_id' => $data['level']->id,
            'amount' => 45000,
        ]);
    }

    public function test_fee_structure_can_be_updated(): void
    {
        $user = $this->createUser();
        $data = $this->createBaseData();

        $structure = FeeStructure::create([
            'fee_type_id' => $data['feeType']->id,
            'academic_year_id' => $data['year']->id,
            'cycle_id' => $data['cycle']->id,
            'amount' => 30000,
        ]);

        $response = $this->actingAs($user)->put("/invoices/fee-structures/{$structure->id}", [
            'fee_type_id' => $data['feeType']->id,
            'cycle_id' => $data['cycle']->id,
            'level_id' => $data['level']->id,
            'amount' => 35000,
        ]);

        $response->assertRedirect(route('invoices.settings'));
        $this->assertDatabaseHas('fee_structures', [
            'id' => $structure->id,
            'amount' => 35000,
            'level_id' => $data['level']->id,
        ]);
    }

    public function test_fee_structure_can_be_deleted(): void
    {
        $user = $this->createUser();
        $data = $this->createBaseData();

        $structure = FeeStructure::create([
            'fee_type_id' => $data['feeType']->id,
            'academic_year_id' => $data['year']->id,
            'amount' => 20000,
        ]);

        $response = $this->actingAs($user)->delete("/invoices/fee-structures/{$structure->id}");
        $response->assertRedirect(route('invoices.settings'));
        $this->assertDatabaseMissing('fee_structures', ['id' => $structure->id]);
    }

    public function test_payment_can_be_recorded_against_invoice(): void
    {
        $user = $this->createUser();
        $data = $this->createBaseData();

        $invoice = Invoice::create([
            'invoice_number' => 'FACT-2025-00001',
            'student_id' => $data['student']->id,
            'enrollment_id' => $data['enrollment']->id,
            'academic_year_id' => $data['year']->id,
            'fee_type_id' => $data['feeType']->id,
            'amount_due' => 25000,
            'amount_paid' => 0,
            'status' => 'IMPAYEE',
        ]);

        $response = $this->actingAs($user)->post("/invoices/{$invoice->id}/payments", [
            'amount' => 10000,
            'payment_method' => 'ESPECES',
            'payment_date' => date('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 10000,
        ]);

        $invoice->refresh();
        $this->assertEquals(10000, $invoice->amount_paid);
        $this->assertEquals('PARTIELLE', $invoice->status);
    }

    public function test_invoice_status_becomes_payee_when_fully_paid(): void
    {
        $user = $this->createUser();
        $data = $this->createBaseData();

        $invoice = Invoice::create([
            'invoice_number' => 'FACT-2025-00002',
            'student_id' => $data['student']->id,
            'enrollment_id' => $data['enrollment']->id,
            'academic_year_id' => $data['year']->id,
            'fee_type_id' => $data['feeType']->id,
            'amount_due' => 25000,
            'amount_paid' => 0,
            'status' => 'IMPAYEE',
        ]);

        $this->actingAs($user)->post("/invoices/{$invoice->id}/payments", [
            'amount' => 25000,
            'payment_method' => 'MOBILE_MONEY',
            'payment_date' => date('Y-m-d'),
        ]);

        $invoice->refresh();
        $this->assertEquals('PAYEE', $invoice->status);
    }

    public function test_manual_invoice_can_be_created(): void
    {
        $user = $this->createUser();
        $data = $this->createBaseData();

        $response = $this->actingAs($user)->post(route('invoices.store'), [
            'enrollment_id' => $data['enrollment']->id,
            'fee_type_id' => $data['feeType']->id,
            'amount_due' => 15000,
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'notes' => 'Frais manuels additionnels',
        ]);

        $this->assertDatabaseHas('invoices', [
            'enrollment_id' => $data['enrollment']->id,
            'amount_due' => 15000,
            'status' => 'IMPAYEE',
        ]);

        $invoice = Invoice::where([
            'enrollment_id' => $data['enrollment']->id,
            'amount_due' => 15000,
        ])->first();
        $this->assertNotNull($invoice);
        $response->assertRedirect(route('invoices.show', $invoice));
    }

    public function test_invoices_can_be_filtered(): void
    {
        $user = $this->createUser();
        $data = $this->createBaseData();

        $feeType2 = FeeType::create(['code' => 'TENUE', 'name' => 'Tenue scolaire']);

        $inv1 = Invoice::create([
            'invoice_number' => 'FACT-2025-00101',
            'student_id' => $data['student']->id,
            'enrollment_id' => $data['enrollment']->id,
            'academic_year_id' => $data['year']->id,
            'fee_type_id' => $data['feeType']->id,
            'amount_due' => 25000,
            'amount_paid' => 0,
            'status' => 'IMPAYEE',
        ]);

        $inv2 = Invoice::create([
            'invoice_number' => 'FACT-2025-00102',
            'student_id' => $data['student']->id,
            'enrollment_id' => $data['enrollment']->id,
            'academic_year_id' => $data['year']->id,
            'fee_type_id' => $feeType2->id,
            'amount_due' => 10000,
            'amount_paid' => 10000,
            'status' => 'PAYEE',
        ]);

        $response = $this->actingAs($user)->get(route('invoices.index', ['fee_type_id' => $feeType2->id]));
        $response->assertStatus(200);
        $response->assertSee('FACT-2025-00102');
        $response->assertDontSee('FACT-2025-00101');
    }

    public function test_whatsapp_payment_reminders_command_runs(): void
    {
        $data = $this->createBaseData();

        $guardian = Guardian::create([
            'first_name' => 'Maman',
            'last_name' => 'Oumarou',
            'phone_primary' => '90123456',
        ]);

        $data['student']->guardians()->attach($guardian->id, ['is_primary_contact' => true]);

        Invoice::create([
            'invoice_number' => 'FACT-2025-00201',
            'student_id' => $data['student']->id,
            'enrollment_id' => $data['enrollment']->id,
            'academic_year_id' => $data['year']->id,
            'fee_type_id' => $data['feeType']->id,
            'amount_due' => 50000,
            'amount_paid' => 0,
            'status' => 'IMPAYEE',
        ]);

        $this->artisan('whatsapp:send-payment-reminders')
            ->assertSuccessful();
    }
}
