<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CycleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SchoolSettingController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherAssignmentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Soumission de demande de pré-inscription en ligne depuis le site web public
Route::post('/pre-inscription', function (Request $request) {
    $validated = $request->validate([
        'student_last_name' => 'required|string|max:255',
        'student_first_name' => 'required|string|max:255',
        'birth_date' => 'required|date',
        'gender' => 'required|in:M,F',
        'requested_cycle' => 'required|string',
        'guardian_name' => 'required|string|max:255',
        'guardian_phone' => 'required|string|max:50',
        'guardian_email' => 'nullable|email|max:255',
        'notes' => 'nullable|string|max:1000',
    ]);

    $ref = 'PRE-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 5));

    return back()->with('preinscription_success', [
        'ref' => $ref,
        'name' => $validated['student_first_name'] . ' ' . $validated['student_last_name'],
        'cycle' => $validated['requested_cycle'],
    ]);
})->name('pre-enrollment.store');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Paramètres Établissement
    Route::get('/settings', [SchoolSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SchoolSettingController::class, 'update'])->name('settings.update');

    // Années Scolaires
    Route::get('/academic-years', [AcademicYearController::class, 'index'])->name('academic-years.index');
    Route::post('/academic-years', [AcademicYearController::class, 'store'])->name('academic-years.store');
    Route::patch('/academic-years/{academicYear}/activate', [AcademicYearController::class, 'activate'])->name('academic-years.activate');
    Route::patch('/academic-years/{academicYear}/toggle-close', [AcademicYearController::class, 'toggleClose'])->name('academic-years.toggle-close');

    // Cycles & Niveaux
    Route::get('/cycles', [CycleController::class, 'index'])->name('cycles.index');

    // Classes
    Route::get('/classes', [SchoolClassController::class, 'index'])->name('classes.index');
    Route::post('/classes', [SchoolClassController::class, 'store'])->name('classes.store');

    // Matières & Coefficients
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::post('/subjects/coefficients', [SubjectController::class, 'storeCoefficient'])->name('subjects.coefficients.store');

    // Élèves
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');

    // Parents / Tuteurs
    Route::get('/guardians', [GuardianController::class, 'index'])->name('guardians.index');

    // Inscriptions & Réinscriptions
    Route::get('/enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::get('/enrollments/create', [EnrollmentController::class, 'create'])->name('enrollments.create');
    Route::post('/enrollments', [EnrollmentController::class, 'store'])->name('enrollments.store');

    // Enseignants
    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');

    // Affectations Cours
    Route::get('/assignments', [TeacherAssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');

    // Emplois du temps
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');

    // Évaluations & Saisie de Notes
    Route::get('/evaluations', [EvaluationController::class, 'index'])->name('evaluations.index');
    Route::post('/evaluations', [EvaluationController::class, 'store'])->name('evaluations.store');
    Route::get('/evaluations/{evaluation}/grades', [EvaluationController::class, 'grades'])->name('evaluations.grades');
    Route::post('/evaluations/{evaluation}/grades', [EvaluationController::class, 'saveGrades'])->name('evaluations.grades.save');

    // Bulletins de Notes
    Route::get('/report-cards', [ReportCardController::class, 'index'])->name('report-cards.index');
    Route::post('/report-cards/generate', [ReportCardController::class, 'generate'])->name('report-cards.generate');
    Route::post('/report-cards/publish', [ReportCardController::class, 'publish'])->name('report-cards.publish');
    Route::get('/report-cards/{reportCard}', [ReportCardController::class, 'show'])->name('report-cards.show');

    // Statistiques & Analyses
    Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');

    // Frais Scolaires & Facturation
    Route::get('/invoices/settings', [InvoiceController::class, 'settings'])->name('invoices.settings');
    Route::post('/invoices/fee-types', [InvoiceController::class, 'storeFeeType'])->name('invoices.fee-types.store');
    Route::post('/invoices/fee-structures', [InvoiceController::class, 'storeFeeStructure'])->name('invoices.fee-structures.store');
    Route::post('/invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');

    // Paiements
    Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');

    // Gestion des Absences & Présences
    Route::get('/attendances/report', [AttendanceController::class, 'report'])->name('attendances.report');
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store');

    // Portails Parents & Élèves
    Route::get('/parent-portal', [ParentPortalController::class, 'index'])->name('portal.parent');
    Route::get('/student-portal', [StudentPortalController::class, 'index'])->name('portal.student');
});

require __DIR__.'/auth.php';
