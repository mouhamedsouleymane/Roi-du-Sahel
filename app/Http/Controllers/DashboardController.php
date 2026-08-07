<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\Invoice;
use App\Models\Period;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main application dashboard with aggregated statistics.
     */
    public function index(): View
    {
        $activeYear = AcademicYear::getActive();
        $activeYearId = $activeYear?->id ?? 0;

        // ── KPIs principaux ──────────────────────────────────────────────
        $totalStudents = Student::count();
        $totalTeachers = Teacher::where('status', 'ACTIF')->count();
        $totalClasses = SchoolClass::where('academic_year_id', $activeYearId)->count();
        $totalEnrolled = Enrollment::where('academic_year_id', $activeYearId)
            ->where('status', 'VALIDE')
            ->count();

        // ── Inscriptions par Cycle ────────────────────────────────────────
        $enrollmentsByCycle = Enrollment::selectRaw('cycles.id as cycle_id, cycles.name as cycle_name, COUNT(*) as total')
            ->join('classes', 'enrollments.class_id', '=', 'classes.id')
            ->join('levels', 'classes.level_id', '=', 'levels.id')
            ->join('cycles', 'levels.cycle_id', '=', 'cycles.id')
            ->where('enrollments.academic_year_id', $activeYearId)
            ->where('enrollments.status', 'VALIDE')
            ->groupBy('cycles.id', 'cycles.name')
            ->orderBy('cycles.id')
            ->get();

        // ── Répartition Filles/Garçons ────────────────────────────────────
        $genderStats = Student::selectRaw('gender, COUNT(*) as total')
            ->groupBy('gender')
            ->pluck('total', 'gender');

        $totalBoys = $genderStats['M'] ?? 0;
        $totalGirls = $genderStats['F'] ?? 0;

        // ── Évaluations du Trimestre en cours ────────────────────────────
        $activePeriod = $activeYear ? Period::getActive($activeYearId) : null;
        $periodEvaluations = Evaluation::where('period_id', $activePeriod?->id ?? 0)->count();
        $publishedBulletins = ReportCard::where('period_id', $activePeriod?->id ?? 0)
            ->where('is_published', true)
            ->count();

        // ── Classes avec taux de remplissage ──────────────────────────────
        $classesOverview = SchoolClass::with(['level.cycle', 'enrollments' => function ($q) {
            $q->where('status', 'VALIDE');
        }])
            ->where('academic_year_id', $activeYearId)
            ->get()
            ->map(function ($class) {
                return [
                    'name' => $class->name,
                    'cycle' => $class->level->cycle->name,
                    'capacity' => $class->capacity ?? 40,
                    'enrolled' => $class->enrollments->count(),
                ];
            });

        // ── 5 dernières inscriptions ──────────────────────────────────────
        $recentEnrollments = Enrollment::with(['student', 'schoolClass.level.cycle'])
            ->where('academic_year_id', $activeYearId)
            ->where('status', 'VALIDE')
            ->latest()
            ->limit(6)
            ->get();

        // ── Résumé Financier ─────────────────────────────────────────────
        $totalRevenueDue = Invoice::where('academic_year_id', $activeYearId)->sum('amount_due');
        $totalRevenuePaid = Invoice::where('academic_year_id', $activeYearId)->sum('amount_paid');
        $totalUnpaid = max(0, $totalRevenueDue - $totalRevenuePaid);

        return view('dashboard', compact(
            'activeYear',
            'activePeriod',
            'totalStudents',
            'totalTeachers',
            'totalClasses',
            'totalEnrolled',
            'totalBoys',
            'totalGirls',
            'enrollmentsByCycle',
            'periodEvaluations',
            'publishedBulletins',
            'classesOverview',
            'recentEnrollments',
            'totalRevenueDue',
            'totalRevenuePaid',
            'totalUnpaid'
        ));
    }
}
