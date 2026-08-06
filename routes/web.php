<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\CycleController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SchoolSettingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
});

require __DIR__.'/auth.php';
