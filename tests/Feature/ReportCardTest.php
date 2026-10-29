<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSubjectCoefficient;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Period;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\ReportCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_cards_page_can_be_rendered(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/report-cards');

        $response->assertStatus(200);
        $response->assertSee('Bulletins de Notes');
    }

    public function test_report_card_show_displays_student_bulletin(): void
    {
        /** @var User $user */
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
        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => '6ème A']);

        $student = Student::create([
            'matricule' => 'RS-2025-0001', 'first_name' => 'Mariam', 'last_name' => 'Diallo',
            'birth_date' => '2010-01-01', 'birth_place' => 'Niamey', 'gender' => 'F',
        ]);

        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'period_id' => $period->id,
            'class_id' => $class->id,
            'general_average' => 15.50,
            'rank' => 1,
            'total_students' => 30,
            'is_published' => true,
        ]);

        $response = $this->actingAs($user)->get("/report-cards/{$reportCard->id}");

        $response->assertStatus(200);
        $response->assertSee('Mariam');
        $response->assertSee('15.50');
    }

    public function test_report_card_can_be_downloaded_as_pdf(): void
    {
        /** @var User $user */
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
        $cycle = Cycle::create(['code' => 'PRIMAIRE', 'name' => 'Primaire']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => 'CM2', 'name' => 'CM2']);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $level->id, 'name' => 'CM2 A']);

        $student = Student::create([
            'matricule' => 'RS-2025-0001', 'first_name' => 'Mariam', 'last_name' => 'Diallo',
            'birth_date' => '2010-01-01', 'birth_place' => 'Niamey', 'gender' => 'F',
        ]);

        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'period_id' => $period->id,
            'class_id' => $class->id,
            'general_average' => 15.50,
            'rank' => 1,
            'total_students' => 30,
            'is_published' => true,
        ]);

        $response = $this->actingAs($user)->get("/report-cards/{$reportCard->id}/pdf");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_report_card_generation_fails_when_period_mismatches_class_cycle(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $year = AcademicYear::create([
            'name' => '2025-2026', 'start_date' => '2025-09-15',
            'end_date' => '2026-06-30', 'is_active' => true,
        ]);
        $semesterPeriod = Period::create([
            'academic_year_id' => $year->id,
            'name' => 'Semestre 1', 'type' => 'SEMESTRE', 'order' => 1,
            'start_date' => '2025-09-15', 'end_date' => '2026-01-31',
        ]);
        $primaireCycle = Cycle::create(['code' => 'PRIMAIRE', 'name' => 'Primaire']);
        $primaireLevel = Level::create(['cycle_id' => $primaireCycle->id, 'code' => 'CM1', 'name' => 'CM1']);
        $primaireClass = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $primaireLevel->id, 'name' => 'CM1 A']);

        // Attempting to generate report cards for a Primaire class with a Semestre period
        $response = $this->actingAs($user)->post('/report-cards/generate', [
            'class_id' => $primaireClass->id,
            'period_id' => $semesterPeriod->id,
        ]);

        $response->assertSessionHas('error');
    }

    public function test_college_and_lycee_official_mc_nc_ms_calculation_formula(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $year = AcademicYear::create([
            'name' => '2025-2026', 'start_date' => '2025-09-15',
            'end_date' => '2026-06-30', 'is_active' => true,
        ]);
        $semesterPeriod = Period::create([
            'academic_year_id' => $year->id,
            'name' => 'Semestre 1', 'type' => 'SEMESTRE', 'order' => 1,
            'start_date' => '2025-09-15', 'end_date' => '2026-01-31',
        ]);
        $collegeCycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $sixiemeLevel = Level::create(['cycle_id' => $collegeCycle->id, 'code' => '6EME', 'name' => 'Sixième']);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $sixiemeLevel->id, 'name' => '6ème A']);

        $student = Student::create([
            'matricule' => '1199', 'first_name' => 'Sani Abakar', 'last_name' => 'Ousmane',
            'birth_date' => '2012-05-10', 'birth_place' => 'Niamey', 'gender' => 'M',
        ]);
        Enrollment::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'academic_year_id' => $year->id,
            'enrollment_number' => 'INS-2025-0001',
            'enrollment_date' => '2025-09-15',
            'status' => 'VALIDE',
        ]);

        $francais = Subject::create(['code' => 'FRAN', 'name' => 'Français', 'category' => 'littéraire']);
        ClassSubjectCoefficient::create([
            'level_id' => $sixiemeLevel->id, 'subject_id' => $francais->id, 'coefficient' => 4,
        ]);

        $teacherUser = User::factory()->create();
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'matricule' => 'ENS01',
            'speciality' => 'Français',
        ]);

        // Evaluation 1: DEVOIR (16/20)
        $evalDevoir = Evaluation::create([
            'period_id' => $semesterPeriod->id, 'class_id' => $class->id, 'subject_id' => $francais->id,
            'teacher_id' => $teacher->id, 'title' => 'Devoir 1', 'type' => 'DEVOIR',
            'max_score' => 20, 'coefficient' => 1, 'evaluation_date' => '2025-10-15',
        ]);
        Grade::create(['evaluation_id' => $evalDevoir->id, 'student_id' => $student->id, 'score' => 16.00]);

        // Evaluation 2: CONTROLE (17/20)
        $evalControle = Evaluation::create([
            'period_id' => $semesterPeriod->id, 'class_id' => $class->id, 'subject_id' => $francais->id,
            'teacher_id' => $teacher->id, 'title' => 'Interro 1', 'type' => 'CONTROLE',
            'max_score' => 20, 'coefficient' => 1, 'evaluation_date' => '2025-11-10',
        ]);
        Grade::create(['evaluation_id' => $evalControle->id, 'student_id' => $student->id, 'score' => 17.00]);

        // Evaluation 3: COMPOSITION (14/20)
        $evalCompo = Evaluation::create([
            'period_id' => $semesterPeriod->id, 'class_id' => $class->id, 'subject_id' => $francais->id,
            'teacher_id' => $teacher->id, 'title' => 'Composition S1', 'type' => 'COMPOSITION',
            'max_score' => 20, 'coefficient' => 1, 'evaluation_date' => '2026-01-20',
        ]);
        Grade::create(['evaluation_id' => $evalCompo->id, 'student_id' => $student->id, 'score' => 14.00]);

        $service = app(ReportCardService::class);
        $scores = $service->calculateSubjectScores($student, $francais->id, $semesterPeriod, true);

        // MC = (16 + 17) / 2 = 16.50
        $this->assertEquals(16.50, $scores['mc']);
        // NC = 14.00
        $this->assertEquals(14.00, $scores['nc']);
        // MS = (16.50 + 14.00) / 2 = 15.25
        $this->assertEquals(15.25, $scores['ms']);

        // Generate report cards for class
        $service->generateForClass($class->id, $semesterPeriod);

        $reportCard = ReportCard::query()->where('student_id', $student->id)
            ->where('period_id', $semesterPeriod->id)
            ->first();

        $this->assertNotNull($reportCard);
        $this->assertEquals(15.25, (float) $reportCard->general_average);
        $this->assertEquals(1, $reportCard->rank);
        $this->assertEquals(15.25, (float) $reportCard->class_average);
        $this->assertEquals(15.25, (float) $reportCard->literary_average);

        // Subject details check
        $this->assertNotNull($reportCard->details);
        $subjectDetail = collect($reportCard->details['details'])->firstWhere('subject_id', $francais->id);
        $this->assertNotNull($subjectDetail);
        $this->assertEquals(16.50, $subjectDetail['mc']);
        $this->assertEquals(14.00, $subjectDetail['nc']);
        $this->assertEquals(15.25, $subjectDetail['ms']);
        $this->assertEquals(4.0, $subjectDetail['coefficient']);
        $this->assertEquals(61.00, $subjectDetail['weighted']); // 15.25 * 4
        $this->assertEquals(1, $subjectDetail['rank']);

        // Web view and PDF view rendering
        $showResponse = $this->actingAs($user)->get("/report-cards/{$reportCard->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee('16.50');
        $showResponse->assertSee('14.00');
        $showResponse->assertSee('15.25');

        $pdfResponse = $this->actingAs($user)->get("/report-cards/{$reportCard->id}/pdf");
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_report_card_show_displays_cycle_specific_designs_for_all_cycles(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $year = AcademicYear::create([
            'name' => '2025-2026', 'start_date' => '2025-09-15',
            'end_date' => '2026-06-30', 'is_active' => true,
        ]);
        $trimester = Period::create([
            'academic_year_id' => $year->id,
            'name' => 'Trimestre 1', 'type' => 'TRIMESTRE', 'order' => 1,
            'start_date' => '2025-09-15', 'end_date' => '2025-12-14',
        ]);
        $semester = Period::create([
            'academic_year_id' => $year->id,
            'name' => 'Semestre 1', 'type' => 'SEMESTRE', 'order' => 1,
            'start_date' => '2025-09-15', 'end_date' => '2026-01-31',
        ]);

        // 1. Maternelle
        $matCycle = Cycle::create(['code' => 'MATERNELLE', 'name' => 'Maternelle']);
        $matLevel = Level::create(['cycle_id' => $matCycle->id, 'code' => 'PS', 'name' => 'Petite Section']);
        $matClass = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $matLevel->id, 'name' => 'PS A']);
        $matStudent = Student::create([
            'matricule' => 'MAT-01', 'first_name' => 'Aïcha', 'last_name' => 'Mounkaila',
            'birth_date' => '2022-03-01', 'birth_place' => 'Niamey', 'gender' => 'F',
        ]);
        $matRc = ReportCard::create([
            'student_id' => $matStudent->id, 'period_id' => $trimester->id, 'class_id' => $matClass->id,
            'general_average' => 17.00, 'rank' => 1, 'total_students' => 15, 'is_published' => true,
            'details' => ['details' => [
                ['subject' => 'Langage et Communication', 'ms' => 18.00, 'appreciation' => 'Très expressive'],
            ]],
        ]);

        $matResp = $this->actingAs($user)->get("/report-cards/{$matRc->id}");
        $matResp->assertStatus(200);
        $matResp->assertSeeText('La Directrice du Préscolaire');
        $matResp->assertSeeText("Domaines d'Activités & Éveil");

        // 2. Primaire
        $primCycle = Cycle::create(['code' => 'PRIMAIRE', 'name' => 'Primaire']);
        $primLevel = Level::create(['cycle_id' => $primCycle->id, 'code' => 'CE1', 'name' => 'CE1']);
        $primClass = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $primLevel->id, 'name' => 'CE1 B']);
        $primStudent = Student::create([
            'matricule' => 'PRIM-01', 'first_name' => 'Moussa', 'last_name' => 'Ibrahim',
            'birth_date' => '2017-06-15', 'birth_place' => 'Niamey', 'gender' => 'M',
        ]);
        $primRc = ReportCard::create([
            'student_id' => $primStudent->id, 'period_id' => $trimester->id, 'class_id' => $primClass->id,
            'general_average' => 14.50, 'rank' => 2, 'total_students' => 25, 'is_published' => true,
            'details' => ['details' => [
                ['subject' => 'Calcul & Problèmes', 'mc' => 14.00, 'nc' => 15.00, 'ms' => 14.50, 'coefficient' => 3, 'weighted' => 43.50, 'rank' => 2, 'appreciation' => 'Bon travail'],
            ]],
        ]);

        $primResp = $this->actingAs($user)->get("/report-cards/{$primRc->id}");
        $primResp->assertStatus(200);
        $primResp->assertSeeText("Le Directeur de l'École Primaire");
        $primResp->assertSee('Composition /10');

        // 3. Lycée
        $lycCycle = Cycle::create(['code' => 'LYCEE', 'name' => 'Lycée']);
        $lycLevel = Level::create(['cycle_id' => $lycCycle->id, 'code' => 'TLE_D', 'name' => 'Terminale D']);
        $lycClass = SchoolClass::create(['academic_year_id' => $year->id, 'level_id' => $lycLevel->id, 'name' => 'Tle D1']);
        $lycStudent = Student::create([
            'matricule' => 'LYC-01', 'first_name' => 'Fatima', 'last_name' => 'Oumarou',
            'birth_date' => '2008-09-20', 'birth_place' => 'Niamey', 'gender' => 'F',
        ]);
        $lycRc = ReportCard::create([
            'student_id' => $lycStudent->id, 'period_id' => $semester->id, 'class_id' => $lycClass->id,
            'general_average' => 16.25, 'rank' => 1, 'total_students' => 35, 'is_published' => true,
            'details' => ['details' => [
                ['subject' => 'Mathématiques', 'mc' => 16.00, 'nc' => 17.00, 'ms' => 16.50, 'coefficient' => 5, 'weighted' => 82.50, 'rank' => 1, 'appreciation' => 'Excellente élève', 'teacher' => 'M. Salifou'],
            ]],
        ]);

        $lycResp = $this->actingAs($user)->get("/report-cards/{$lycRc->id}");
        $lycResp->assertStatus(200);
        $lycResp->assertSee('Le Proviseur du Lycée');
        $lycResp->assertSee('MS /20');
        $lycResp->assertSee('COEF');
    }

    public function test_report_cards_index_displays_cycle_tabs(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/report-cards');
        $response->assertStatus(200);
        $response->assertSee('Tous les Cycles');
        $response->assertSee('Maternelle');
        $response->assertSee('Primaire');
        $response->assertSee('Collège');
        $response->assertSee('Lycée');
    }
}
