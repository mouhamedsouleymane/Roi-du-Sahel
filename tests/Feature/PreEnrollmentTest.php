<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\FeeType;
use App\Models\Level;
use App\Models\PreEnrollment;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pre_enrollment_can_be_submitted(): void
    {
        $year = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $response = $this->post(route('pre-enrollment.store'), [
            'student_last_name' => 'Souley',
            'student_first_name' => 'Amina',
            'birth_date' => '2014-06-12',
            'birth_place' => 'Niamey',
            'gender' => 'F',
            'blood_group' => 'O+',
            'previous_school' => 'CSP Les Étoiles',
            'medical_notes' => 'Légère allergie aux arachides',
            'requested_cycle' => 'Collège - Sixième',
            'type' => 'NOUVEAU',
            'guardian_last_name' => 'Souley',
            'guardian_first_name' => 'Moussa',
            'relationship' => 'PERE',
            'guardian_profession' => 'Ingénieur',
            'guardian_phone' => '+227 90 11 22 33',
            'guardian_phone_secondary' => '+227 96 11 22 33',
            'guardian_email' => 'moussa.souley@example.com',
            'guardian_city' => 'Niamey',
            'guardian_address' => 'Plateau 2, Rue des Rois',
            'notes' => 'Candidature rentrée 2025',
        ]);

        $response->assertRedirect(url('/#preinscription'));
        $response->assertSessionHas('preinscription_success');

        $this->assertDatabaseHas('pre_enrollments', [
            'student_last_name' => 'Souley',
            'student_first_name' => 'Amina',
            'birth_place' => 'Niamey',
            'blood_group' => 'O+',
            'previous_school' => 'CSP Les Étoiles',
            'medical_notes' => 'Légère allergie aux arachides',
            'guardian_phone' => '+227 90 11 22 33',
            'guardian_profession' => 'Ingénieur',
            'guardian_address' => 'Plateau 2, Rue des Rois',
            'status' => 'EN_ATTENTE',
        ]);
    }

    public function test_admin_can_view_pre_enrollments_index(): void
    {
        $user = User::factory()->create();

        PreEnrollment::create([
            'reference' => 'PRE-2025-0001',
            'student_first_name' => 'Fatima',
            'student_last_name' => 'Idrissa',
            'birth_date' => '2013-02-10',
            'gender' => 'F',
            'requested_cycle' => 'Primaire',
            'guardian_first_name' => 'Idrissa',
            'guardian_last_name' => 'Ousmane',
            'relationship' => 'PERE',
            'guardian_phone' => '+227 91 22 33 44',
            'status' => 'EN_ATTENTE',
        ]);

        $response = $this->actingAs($user)->get(route('pre-enrollments.index'));

        $response->assertStatus(200);
        $response->assertSee('PRE-2025-0001');
        $response->assertSee('Fatima');
        $response->assertSee('Idrissa');
    }

    public function test_admin_can_view_pre_enrollment_show_page(): void
    {
        $user = User::factory()->create();

        $item = PreEnrollment::create([
            'reference' => 'PRE-2025-0002',
            'student_first_name' => 'Oumarou',
            'student_last_name' => 'Amadou',
            'birth_date' => '2012-08-20',
            'gender' => 'M',
            'requested_cycle' => 'Collège',
            'guardian_first_name' => 'Amadou',
            'guardian_last_name' => 'Sani',
            'relationship' => 'PERE',
            'guardian_phone' => '+227 92 33 44 55',
            'status' => 'EN_ATTENTE',
        ]);

        $response = $this->actingAs($user)->get(route('pre-enrollments.show', $item));

        $response->assertStatus(200);
        $response->assertSee('PRE-2025-0002');
        $response->assertSee('Oumarou');
    }

    public function test_admin_can_convert_pre_enrollment_to_official_enrollment(): void
    {
        $user = User::factory()->create();

        $year = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        FeeType::create(['code' => 'INSCRIPT', 'name' => "Frais d'inscription", 'is_recurring' => false, 'is_active' => true]);
        FeeType::create(['code' => 'SCOLARITE', 'name' => 'Frais de scolarité', 'is_recurring' => true, 'is_active' => true]);

        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $class = SchoolClass::create([
            'academic_year_id' => $year->id,
            'level_id' => $level->id,
            'name' => '6ème A',
            'capacity' => 40,
        ]);

        $preEnrollment = PreEnrollment::create([
            'reference' => 'PRE-2025-0003',
            'student_first_name' => 'Hassane',
            'student_last_name' => 'Diallo',
            'birth_date' => '2012-04-18',
            'birth_place' => 'Niamey',
            'gender' => 'M',
            'blood_group' => 'B+',
            'previous_school' => 'École Al-Housseini',
            'medical_notes' => 'Aucune',
            'requested_cycle' => 'Collège - Sixième',
            'type' => 'NOUVEAU',
            'guardian_first_name' => 'Diallo',
            'guardian_last_name' => 'Boubacar',
            'relationship' => 'PERE',
            'guardian_profession' => 'Commerçant',
            'guardian_phone' => '+227 93 44 55 66',
            'guardian_phone_secondary' => '+227 88 44 55 66',
            'guardian_email' => 'diallo@example.com',
            'guardian_city' => 'Niamey',
            'guardian_address' => 'Quartier Yantala',
            'status' => 'EN_ATTENTE',
        ]);

        $response = $this->actingAs($user)->post(route('pre-enrollments.convert', $preEnrollment), [
            'class_id' => $class->id,
            'payment_method' => 'ESPECES',
            'amount_paid' => 30000,
        ]);

        $preEnrollment->refresh();
        $this->assertSame('VALIDEE', $preEnrollment->status);
        $this->assertNotNull($preEnrollment->converted_enrollment_id);

        $response->assertRedirect(route('enrollments.show', $preEnrollment->converted_enrollment_id));

        $this->assertDatabaseHas('students', [
            'first_name' => 'Hassane',
            'last_name' => 'Diallo',
            'birth_place' => 'Niamey',
            'blood_group' => 'B+',
            'previous_school' => 'École Al-Housseini',
        ]);

        $this->assertDatabaseHas('guardians', [
            'first_name' => 'Diallo',
            'profession' => 'Commerçant',
            'phone_primary' => '+227 93 44 55 66',
            'phone_secondary' => '+227 88 44 55 66',
            'address' => 'Quartier Yantala',
        ]);

        $this->assertDatabaseHas('enrollments', [
            'id' => $preEnrollment->converted_enrollment_id,
            'class_id' => $class->id,
            'status' => 'VALIDE',
        ]);
    }

    public function test_admin_can_reject_pre_enrollment(): void
    {
        $user = User::factory()->create();

        $preEnrollment = PreEnrollment::create([
            'reference' => 'PRE-2025-0004',
            'student_first_name' => 'Ali',
            'student_last_name' => 'Mounkaila',
            'birth_date' => '2011-09-01',
            'gender' => 'M',
            'requested_cycle' => 'Collège',
            'guardian_first_name' => 'Mounkaila',
            'guardian_last_name' => 'Adamou',
            'relationship' => 'PERE',
            'guardian_phone' => '+227 94 55 66 77',
            'status' => 'EN_ATTENTE',
        ]);

        $response = $this->actingAs($user)->post(route('pre-enrollments.reject', $preEnrollment));

        $response->assertRedirect();
        $preEnrollment->refresh();
        $this->assertSame('REJETEE', $preEnrollment->status);
    }
}
