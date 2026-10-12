<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CycleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\PreEnrollmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SchoolSettingController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherAssignmentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsAppBroadcastController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Models\Cycle;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes & Espace Public
|--------------------------------------------------------------------------
*/

// PWA — page de secours hors ligne (pré-cachée par le service worker).
Route::get('/offline', fn () => response(
    File::get(public_path('offline.html')), 200, ['Content-Type' => 'text/html; charset=utf-8']
))->name('pwa.offline');

// PWA — manifest + service worker servis par Laravel (bon type MIME ; en
// production le serveur web peut aussi les servir en statique directement).
Route::get('/manifest.webmanifest', function () {
    $manifest = json_decode(File::get(public_path('manifest.webmanifest')), true);

    return response()->json($manifest, 200, ['Content-Type' => 'application/manifest+json'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
})->name('pwa.manifest');
Route::get('/sw.js', fn () => response(
    File::get(public_path('sw.js')),
    200,
    ['Content-Type' => 'application/javascript; charset=utf-8', 'Service-Worker-Allowed' => '/', 'Cache-Control' => 'no-cache']
))->name('pwa.service-worker');

Route::get('/', function () {
    $cycles = Cycle::with('levels')->where('is_active', true)->get();

    return view('welcome', compact('cycles'));
})->name('home');

// Soumission de demande de pré-inscription en ligne depuis le site web public
Route::post('/pre-inscription', [PreEnrollmentController::class, 'store'])->name('pre-enrollment.store');

// ─────────────────────────────────────────────
// WhatsApp Webhook & Chatbot Meta (Public)
// ─────────────────────────────────────────────
Route::get('/whatsapp/webhook', [WhatsAppWebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'handle'])->name('whatsapp.webhook.handle');

/*
|--------------------------------------------------------------------------
| Espace Authentifié (Administration, Enseignants, Portails)
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Profil utilisateur
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Paramètres Établissement
    Route::get('/settings', [SchoolSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SchoolSettingController::class, 'update'])->name('settings.update');

    // Utilisateurs
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::match(['PUT', 'PATCH'], '/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Rôles & Habilitations
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::match(['PUT', 'PATCH'], '/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // Années Scolaires
    Route::get('/academic-years', [AcademicYearController::class, 'index'])->name('academic-years.index');
    Route::get('/academic-years/create', [AcademicYearController::class, 'create'])->name('academic-years.create');
    Route::post('/academic-years', [AcademicYearController::class, 'store'])->name('academic-years.store');
    Route::get('/academic-years/{academicYear}', [AcademicYearController::class, 'show'])->name('academic-years.show');
    Route::get('/academic-years/{academicYear}/edit', [AcademicYearController::class, 'edit'])->name('academic-years.edit');
    Route::match(['PUT', 'PATCH'], '/academic-years/{academicYear}', [AcademicYearController::class, 'update'])->name('academic-years.update');
    Route::delete('/academic-years/{academicYear}', [AcademicYearController::class, 'destroy'])->name('academic-years.destroy');
    Route::patch('/academic-years/{academicYear}/activate', [AcademicYearController::class, 'activate'])->name('academic-years.activate');
    Route::patch('/academic-years/{academicYear}/toggle-close', [AcademicYearController::class, 'toggleClose'])->name('academic-years.toggle-close');
    // Périodes (Trimestres & Semestres)
    Route::post('/academic-years/{academicYear}/periods', [PeriodController::class, 'store'])->name('academic-years.periods.store');
    Route::match(['PUT', 'PATCH'], '/academic-years/{academicYear}/periods/{period}', [PeriodController::class, 'update'])->name('academic-years.periods.update');
    Route::delete('/academic-years/{academicYear}/periods/{period}', [PeriodController::class, 'destroy'])->name('academic-years.periods.destroy');
    Route::patch('/academic-years/{academicYear}/periods/{period}/toggle-close', [PeriodController::class, 'toggleClose'])->name('academic-years.periods.toggle-close');

    // Cycles & Niveaux
    Route::get('/cycles', [CycleController::class, 'index'])->name('cycles.index');
    Route::get('/cycles/create', [CycleController::class, 'create'])->name('cycles.create');
    Route::post('/cycles', [CycleController::class, 'store'])->name('cycles.store');
    Route::get('/cycles/{cycle}', [CycleController::class, 'show'])->name('cycles.show');
    Route::get('/cycles/{cycle}/edit', [CycleController::class, 'edit'])->name('cycles.edit');
    Route::match(['PUT', 'PATCH'], '/cycles/{cycle}', [CycleController::class, 'update'])->name('cycles.update');
    Route::patch('/cycles/{cycle}/toggle-active', [CycleController::class, 'toggleActive'])->name('cycles.toggle-active');
    Route::delete('/cycles/{cycle}', [CycleController::class, 'destroy'])->name('cycles.destroy');
    Route::post('/cycles/{cycle}/levels', [CycleController::class, 'storeLevel'])->name('cycles.levels.store');
    Route::get('/cycles/{cycle}/levels/{level}', [LevelController::class, 'show'])->name('cycles.levels.show');
    Route::match(['PUT', 'PATCH'], '/cycles/{cycle}/levels/{level}', [CycleController::class, 'updateLevel'])->name('cycles.levels.update');
    Route::delete('/cycles/{cycle}/levels/{level}', [CycleController::class, 'destroyLevel'])->name('cycles.levels.destroy');

    // Classes
    Route::get('/classes', [SchoolClassController::class, 'index'])->name('classes.index');
    Route::get('/classes/create', [SchoolClassController::class, 'create'])->name('classes.create');
    Route::post('/classes', [SchoolClassController::class, 'store'])->name('classes.store');
    Route::get('/classes/{schoolClass}', [SchoolClassController::class, 'show'])->name('classes.show');
    Route::get('/classes/{schoolClass}/edit', [SchoolClassController::class, 'edit'])->name('classes.edit');
    Route::match(['PUT', 'PATCH'], '/classes/{schoolClass}', [SchoolClassController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{schoolClass}', [SchoolClassController::class, 'destroy'])->name('classes.destroy');

    // Matières & Coefficients
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::get('/subjects/create', [SubjectController::class, 'create'])->name('subjects.create');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::get('/subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
    Route::match(['PUT', 'PATCH'], '/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::patch('/subjects/{subject}/toggle-active', [SubjectController::class, 'toggleActive'])->name('subjects.toggle-active');
    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
    Route::post('/subjects/coefficients', [SubjectController::class, 'storeCoefficient'])->name('subjects.coefficients.store');
    Route::delete('/subjects/coefficients/{coefficient}', [SubjectController::class, 'destroyCoefficient'])->name('subjects.coefficients.destroy');

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
    Route::get('/enrollments/{enrollment}/receipt', [EnrollmentController::class, 'receipt'])->name('enrollments.receipt');
    Route::get('/enrollments/{enrollment}/edit', [EnrollmentController::class, 'edit'])->name('enrollments.edit');
    Route::match(['PUT', 'PATCH'], '/enrollments/{enrollment}', [EnrollmentController::class, 'update'])->name('enrollments.update');

    // Pré-inscriptions en Ligne (Demandes publiques)
    Route::get('/pre-enrollments', [PreEnrollmentController::class, 'index'])->name('pre-enrollments.index');
    Route::get('/pre-enrollments/{preEnrollment}', [PreEnrollmentController::class, 'show'])->name('pre-enrollments.show');
    Route::post('/pre-enrollments/{preEnrollment}/convert', [PreEnrollmentController::class, 'convert'])->name('pre-enrollments.convert');
    Route::post('/pre-enrollments/{preEnrollment}/reject', [PreEnrollmentController::class, 'reject'])->name('pre-enrollments.reject');
    Route::delete('/pre-enrollments/{preEnrollment}', [PreEnrollmentController::class, 'destroy'])->name('pre-enrollments.destroy');

    // Enseignants
    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::get('/teachers/create', [TeacherController::class, 'create'])->name('teachers.create');
    Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
    Route::get('/teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
    Route::match(['PUT', 'PATCH'], '/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');

    // Affectations Cours
    Route::get('/assignments', [TeacherAssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/create', [TeacherAssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/{assignment}', [TeacherAssignmentController::class, 'show'])->name('assignments.show');
    Route::get('/assignments/{assignment}/edit', [TeacherAssignmentController::class, 'edit'])->name('assignments.edit');
    Route::match(['PUT', 'PATCH'], '/assignments/{assignment}', [TeacherAssignmentController::class, 'update'])->name('assignments.update');
    Route::delete('/assignments/{assignment}', [TeacherAssignmentController::class, 'destroy'])->name('assignments.destroy');

    // Emplois du temps (CRUD Complet & Dynamique)
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/create', [ScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::post('/schedules/clear-class', [ScheduleController::class, 'clearClass'])->name('schedules.clear-class');
    Route::get('/schedules/classes/{schoolClass}/pdf', [ScheduleController::class, 'exportPdf'])->name('schedules.export-pdf');
    Route::get('/schedules/{schedule}', [ScheduleController::class, 'show'])->name('schedules.show');
    Route::get('/schedules/{schedule}/edit', [ScheduleController::class, 'edit'])->name('schedules.edit');
    Route::match(['PUT', 'PATCH'], '/schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
    Route::post('/schedules/{schedule}/duplicate', [ScheduleController::class, 'duplicate'])->name('schedules.duplicate');
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
    Route::get('/report-cards/{reportCard}/pdf', [ReportCardController::class, 'pdf'])->name('report-cards.pdf');

    // Statistiques & Analyses
    Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');

    // Frais Scolaires & Facturation (Paramètres & CRUD)
    Route::get('/invoices/settings', [InvoiceController::class, 'settings'])->name('invoices.settings');
    Route::post('/invoices/fee-types', [InvoiceController::class, 'storeFeeType'])->name('invoices.fee-types.store');
    Route::match(['PUT', 'PATCH'], '/invoices/fee-types/{feeType}', [InvoiceController::class, 'updateFeeType'])->name('invoices.fee-types.update');
    Route::patch('/invoices/fee-types/{feeType}/toggle-active', [InvoiceController::class, 'toggleActiveFeeType'])->name('invoices.fee-types.toggle-active');
    Route::delete('/invoices/fee-types/{feeType}', [InvoiceController::class, 'destroyFeeType'])->name('invoices.fee-types.destroy');

    Route::post('/invoices/fee-structures', [InvoiceController::class, 'storeFeeStructure'])->name('invoices.fee-structures.store');
    Route::match(['PUT', 'PATCH'], '/invoices/fee-structures/{feeStructure}', [InvoiceController::class, 'updateFeeStructure'])->name('invoices.fee-structures.update');
    Route::delete('/invoices/fee-structures/{feeStructure}', [InvoiceController::class, 'destroyFeeStructure'])->name('invoices.fee-structures.destroy');

    Route::post('/invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');

    // Paiements
    Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');

    // Dépenses & Sorties de Caisse
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/settings', [ExpenseController::class, 'settings'])->name('expenses.settings');
    Route::get('/expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::match(['PUT', 'PATCH'], '/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Catégories de Dépenses
    Route::post('/expense-categories', [ExpenseController::class, 'storeCategory'])->name('expense-categories.store');
    Route::match(['PUT', 'PATCH'], '/expense-categories/{expenseCategory}', [ExpenseController::class, 'updateCategory'])->name('expense-categories.update');
    Route::patch('/expense-categories/{expenseCategory}/toggle', [ExpenseController::class, 'toggleCategory'])->name('expense-categories.toggle');
    Route::delete('/expense-categories/{expenseCategory}', [ExpenseController::class, 'destroyCategory'])->name('expense-categories.destroy');

    // Gestion des Absences & Présences
    Route::get('/attendances/report', [AttendanceController::class, 'report'])->name('attendances.report');
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store');

    // Communication Institutionnelle WhatsApp
    Route::post('/whatsapp/announcements/send', [WhatsAppBroadcastController::class, 'sendAnnouncement'])
        ->name('whatsapp.announcements.send');

    // Portails Parents & Élèves
    Route::get('/parent-portal', [ParentPortalController::class, 'index'])->name('portal.parent');
    Route::get('/student-portal', [StudentPortalController::class, 'index'])->name('portal.student');
});

require __DIR__.'/auth.php';
