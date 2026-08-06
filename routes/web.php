<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolSettingController;
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
});

require __DIR__.'/auth.php';
