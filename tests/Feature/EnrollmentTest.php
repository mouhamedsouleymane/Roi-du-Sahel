<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollments_index_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $year = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $class = SchoolClass::create([
            'academic_year_id' => $year->id,
            'level_id' => $level->id,
            'name' => '6ème A',
            'capacity' => 40,
        ]);

        $student = Student::create([
            'matricule' => 'RS-2025-0001',
            'first_name' => 'Souleymane',
            'last_name' => 'Moussa',
            'gender' => 'M',
            'birth_date' => '2012-05-14',
            'birth_place' => 'Niamey',
        ]);

        Enrollment::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'academic_year_id' => $year->id,
            'enrollment_number' => 'INS-2025-0001',
            'type' => 'NOUVEAU',
            'status' => 'VALIDE',
            'enrollment_date' => '2025-09-15',
        ]);

        $response = $this->actingAs($user)->get('/enrollments');

        $response->assertStatus(200);
        $response->assertSee('Registre des Inscriptions');
        $response->assertSee('INS-2025-0001');
        $response->assertSee('Souleymane');
    }

    public function test_enrollment_form_can_be_rendered(): void
    {
        $user = User::factory()->create();

        AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/enrollments/create');

        $response->assertStatus(200);
        $response->assertSee('Inscription Scolaire');
    }

    public function test_new_student_can_be_enrolled(): void
    {
        $user = User::factory()->create();

        $year = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $inscriptFee = FeeType::create(['code' => 'INSCRIPT', 'name' => "Frais d'inscription", 'is_recurring' => false, 'is_active' => true]);
        FeeType::create(['code' => 'SCOLARITE', 'name' => 'Frais de scolarité', 'is_recurring' => true, 'is_active' => true]);

        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $class = SchoolClass::create([
            'academic_year_id' => $year->id,
            'level_id' => $level->id,
            'name' => '6ème A',
            'capacity' => 40,
        ]);

        FeeStructure::create([
            'fee_type_id' => $inscriptFee->id,
            'academic_year_id' => $year->id,
            'cycle_id' => $cycle->id,
            'amount' => 30000,
        ]);

        $response = $this->actingAs($user)->post('/enrollments', [
            'first_name' => 'Souleymane',
            'last_name' => 'Moussa',
            'gender' => 'M',
            'birth_date' => '2012-05-14',
            'birth_place' => 'Niamey',
            'guardian_first_name' => 'Moussa',
            'guardian_last_name' => 'Souley',
            'relationship' => 'PERE',
            'guardian_profession' => 'Commerçant',
            'guardian_city' => 'Niamey',
            'phone_primary' => '+227 90 11 22 33',
            'class_id' => $class->id,
            'type' => 'NOUVEAU',
            'payment_method' => 'ESPECES',
            'amount_paid' => 30000,
        ]);

        $this->assertDatabaseHas('students', [
            'first_name' => 'Souleymane',
            'last_name' => 'Moussa',
            'birth_place' => 'Niamey',
        ]);

        $this->assertDatabaseHas('guardians', [
            'first_name' => 'Moussa',
            'profession' => 'Commerçant',
            'city' => 'Niamey',
            'phone_primary' => '+227 90 11 22 33',
        ]);

        $this->assertDatabaseHas('enrollments', [
            'class_id' => $class->id,
            'status' => 'VALIDE',
        ]);

        $this->assertDatabaseHas('invoices', [
            'fee_type_id' => FeeType::where('code', 'INSCRIPT')->first()->id,
            'amount_paid' => 30000,
            'status' => 'PAYEE',
        ]);

        // Tranches are generated
        $this->assertDatabaseHas('invoices', [
            'fee_type_id' => FeeType::where('code', 'SCOLARITE_T1')->first()->id,
            'status' => 'IMPAYEE',
        ]);
        $this->assertDatabaseHas('invoices', [
            'fee_type_id' => FeeType::where('code', 'SCOLARITE_T2')->first()->id,
            'status' => 'IMPAYEE',
        ]);
        $this->assertDatabaseHas('invoices', [
            'fee_type_id' => FeeType::where('code', 'SCOLARITE_T3')->first()->id,
            'status' => 'IMPAYEE',
        ]);
    }

    public function test_enrollment_creates_tuition_tranches_with_correct_splits_and_surplus_payment(): void
    {
        $user = User::factory()->create();

        $year = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $class = SchoolClass::create([
            'academic_year_id' => $year->id,
            'level_id' => $level->id,
            'name' => '6ème A',
            'capacity' => 40,
        ]);

        $inscriptFee = FeeType::create(['code' => 'INSCRIPT', 'name' => "Frais d'inscription", 'is_recurring' => false]);
        $scolariteFee = FeeType::create(['code' => 'SCOLARITE', 'name' => 'Frais de scolarité', 'is_recurring' => true]);

        FeeStructure::create([
            'fee_type_id' => $inscriptFee->id,
            'academic_year_id' => $year->id,
            'cycle_id' => $cycle->id,
            'amount' => 30000,
        ]);

        FeeStructure::create([
            'fee_type_id' => $scolariteFee->id,
            'academic_year_id' => $year->id,
            'cycle_id' => $cycle->id,
            'amount' => 100000,
        ]);

        // Pay 30 000 (inscription) + 25 000 (towards Tranche 1) = 55 000 FCFA
        $response = $this->actingAs($user)->post('/enrollments', [
            'first_name' => 'Ibrahim',
            'last_name' => 'Amadou',
            'gender' => 'M',
            'birth_date' => '2012-08-20',
            'birth_place' => 'Maradi',
            'guardian_first_name' => 'Amadou',
            'guardian_last_name' => 'Ibrahim',
            'relationship' => 'PERE',
            'guardian_profession' => 'Fonctionnaire',
            'guardian_city' => 'Niamey',
            'phone_primary' => '+227 90 99 88 77',
            'class_id' => $class->id,
            'type' => 'NOUVEAU',
            'payment_method' => 'ESPECES',
            'amount_paid' => 55000,
        ]);

        $response->assertRedirect();

        // 1. Inscription Invoice (30 000 FCFA) -> Fully Paid
        $this->assertDatabaseHas('invoices', [
            'fee_type_id' => $inscriptFee->id,
            'amount_due' => 30000,
            'amount_paid' => 30000,
            'status' => 'PAYEE',
        ]);

        // 2. Tranche 1 (50% of 100 000 = 50 000 FCFA) -> 25 000 paid -> PARTIELLE
        $t1Fee = FeeType::where('code', 'SCOLARITE_T1')->first();
        $this->assertNotNull($t1Fee);
        $this->assertDatabaseHas('invoices', [
            'fee_type_id' => $t1Fee->id,
            'amount_due' => 50000,
            'amount_paid' => 25000,
            'status' => 'PARTIELLE',
        ]);

        // 3. Tranche 2 (35% of 100 000 = 35 000 FCFA) -> IMPAYEE, due 2026-01-01
        $t2Fee = FeeType::where('code', 'SCOLARITE_T2')->first();
        $this->assertNotNull($t2Fee);
        $t2Invoice = Invoice::where('fee_type_id', $t2Fee->id)->first();
        $this->assertNotNull($t2Invoice);
        $this->assertEquals(35000, (float) $t2Invoice->amount_due);
        $this->assertEquals(0, (float) $t2Invoice->amount_paid);
        $this->assertEquals('IMPAYEE', $t2Invoice->status);
        $this->assertEquals('2026-01-01', $t2Invoice->due_date->format('Y-m-d'));

        // 4. Tranche 3 (15% of 100 000 = 15 000 FCFA) -> IMPAYEE, due 2026-03-01
        $t3Fee = FeeType::where('code', 'SCOLARITE_T3')->first();
        $this->assertNotNull($t3Fee);
        $t3Invoice = Invoice::where('fee_type_id', $t3Fee->id)->first();
        $this->assertNotNull($t3Invoice);
        $this->assertEquals(15000, (float) $t3Invoice->amount_due);
        $this->assertEquals(0, (float) $t3Invoice->amount_paid);
        $this->assertEquals('IMPAYEE', $t3Invoice->status);
        $this->assertEquals('2026-03-01', $t3Invoice->due_date->format('Y-m-d'));
    }

    public function test_enrollment_receipt_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $year = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $cycle = Cycle::create(['code' => 'LYCEE', 'name' => 'Lycée']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => 'TLE', 'name' => 'Terminale']);
        $class = SchoolClass::create([
            'academic_year_id' => $year->id,
            'level_id' => $level->id,
            'name' => 'Terminale D1',
            'capacity' => 35,
        ]);

        $student = Student::create([
            'matricule' => 'RS-2025-0005',
            'first_name' => 'Halima',
            'last_name' => 'Idrissa',
            'gender' => 'F',
            'birth_date' => '2007-03-22',
            'birth_place' => 'Niamey',
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'academic_year_id' => $year->id,
            'enrollment_number' => 'INS-2025-0005',
            'type' => 'NOUVEAU',
            'status' => 'VALIDE',
            'enrollment_date' => '2025-09-15',
        ]);

        $response = $this->actingAs($user)->get("/enrollments/{$enrollment->id}/receipt");

        $response->assertStatus(200);
        $response->assertSee('INS-2025-0005');
        $response->assertSee('Halima');
    }

    public function test_enrollment_can_be_updated(): void
    {
        $user = User::factory()->create();

        $year = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $class1 = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A', 'capacity' => 40]);
        $class2 = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème B', 'capacity' => 40]);

        $student = Student::create([
            'matricule' => 'RS-2025-0006',
            'first_name' => 'Ali',
            'last_name' => 'Maman',
            'gender' => 'M',
            'birth_date' => '2012-01-10',
            'birth_place' => 'Dosso',
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'class_id' => $class1->id,
            'academic_year_id' => $year->id,
            'enrollment_number' => 'INS-2025-0006',
            'type' => 'NOUVEAU',
            'status' => 'VALIDE',
            'enrollment_date' => '2025-09-15',
        ]);

        $response = $this->actingAs($user)->put("/enrollments/{$enrollment->id}", [
            'class_id' => $class2->id,
            'type' => 'REINSCRIPTION',
            'status' => 'VALIDE',
            'enrollment_date' => '2025-09-20',
            'notes' => 'Transféré en 6ème B',
        ]);

        $response->assertRedirect(route('enrollments.show', $enrollment));
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'class_id' => $class2->id,
            'type' => 'REINSCRIPTION',
            'notes' => 'Transféré en 6ème B',
        ]);
    }

    public function test_enrollment_show_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $year = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A', 'capacity' => 40]);

        $student = Student::create([
            'matricule' => 'RS-2025-0007',
            'first_name' => 'Fatima',
            'last_name' => 'Souley',
            'gender' => 'F',
            'birth_date' => '2013-03-15',
            'birth_place' => 'Niamey',
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'academic_year_id' => $year->id,
            'enrollment_number' => 'INS-2025-0007',
            'type' => 'NOUVEAU',
            'status' => 'VALIDE',
            'enrollment_date' => '2025-09-15',
        ]);

        $response = $this->actingAs($user)->get(route('enrollments.show', $enrollment));

        $response->assertStatus(200);
        $response->assertSee('INS-2025-0007');
        $response->assertSee('Fatima');
    }
}
