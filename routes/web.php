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
    Route::get('/cycles/{cycle}', [CycleController::class, 'show'])->name('cycles.show');
    Route::get('/cycles/{cycle}/edit', [CycleController::class, 'edit'])->name('cycles.edit');
    Route::match(['PUT', 'PATCH'], '/cycles/{cycle}', [CycleController::class, 'update'])->name('cycles.update');

    // Classes
    Route::get('/classes', [SchoolClassController::class, 'index'])->name('classes.index');
    Route::post('/classes', [SchoolClassController::class, 'store'])->name('classes.store');
    Route::get('/classes/{schoolClass}', [SchoolClassController::class, 'show'])->name('classes.show');
    Route::get('/classes/{schoolClass}/edit', [SchoolClassController::class, 'edit'])->name('classes.edit');
    Route::match(['PUT', 'PATCH'], '/classes/{schoolClass}', [SchoolClassController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{schoolClass}', [SchoolClassController::class, 'destroy'])->name('classes.destroy');

    // Matières & Coefficients
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::post('/subjects/coefficients', [SubjectController::class, 'storeCoefficient'])->name('subjects.coefficients.store');

    // Élèves
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
    Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::match(['PUT', 'PATCH'], '/students/{student}', [StudentController::class, 'update'])->name('students.update');

    // Parents / Tuteurs
    Route::get('/guardians', [GuardianController::class, 'index'])->name('guardians.index');
    Route::get('/guardians/create', [GuardianController::class, 'create'])->name('guardians.create');
    Route::post('/guardians', [GuardianController::class, 'store'])->name('guardians.store');
    Route::get('/guardians/{guardian}', [GuardianController::class, 'show'])->name('guardians.show');
    Route::get('/guardians/{guardian}/edit', [GuardianController::class, 'edit'])->name('guardians.edit');
    Route::match(['PUT', 'PATCH'], '/guardians/{guardian}', [GuardianController::class, 'update'])->name('guardians.update');

    // Inscriptions & Réinscriptions
    Route::get('/enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::get('/enrollments/create', [EnrollmentController::class, 'create'])->name('enrollments.create');
    Route::post('/enrollments', [EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::get('/enrollments/{enrollment}', [EnrollmentController::class, 'show'])->name('enrollments.show');
    Route::get('/enrollments/{enrollment}/edit', [EnrollmentController::class, 'edit'])->name('enrollments.edit');
    Route::match(['PUT', 'PATCH'], '/enrollments/{enrollment}', [EnrollmentController::class, 'update'])->name('enrollments.update');

    // Enseignants
    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
    Route::get('/teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
    Route::match(['PUT', 'PATCH'], '/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');

    // Affectations Cours
    Route::get('/assignments', [TeacherAssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/{assignment}', [TeacherAssignmentController::class, 'show'])->name('assignments.show');
    Route::get('/assignments/{assignment}/edit', [TeacherAssignmentController::class, 'edit'])->name('assignments.edit');
    Route::match(['PUT', 'PATCH'], '/assignments/{assignment}', [TeacherAssignmentController::class, 'update'])->name('assignments.update');
    Route::delete('/assignments/{assignment}', [TeacherAssignmentController::class, 'destroy'])->name('assignments.destroy');

    // Emplois du temps
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::get('/schedules/{schedule}', [ScheduleController::class, 'show'])->name('schedules.show');
    Route::get('/schedules/{schedule}/edit', [ScheduleController::class, 'edit'])->name('schedules.edit');
    Route::match(['PUT', 'PATCH'], '/schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

    // Évaluations & Saisie de Notes
    Route::get('/evaluations', [EvaluationController::class, 'index'])->name('evaluations.index');
    Route::post('/evaluations', [EvaluationController::class, 'store'])->name('evaluations.store');
    Route::get('/evaluations/{evaluation}', [EvaluationController::class, 'show'])->name('evaluations.show');
    Route::get('/evaluations/{evaluation}/edit', [EvaluationController::class, 'edit'])->name('evaluations.edit');
    Route::match(['PUT', 'PATCH'], '/evaluations/{evaluation}', [EvaluationController::class, 'update'])->name('evaluations.update');
    Route::delete('/evaluations/{evaluation}', [EvaluationController::class, 'destroy'])->name('evaluations.destroy');
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
