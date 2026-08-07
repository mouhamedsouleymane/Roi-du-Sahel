<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\Grade;
use App\Models\Period;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatsController extends Controller
{
    /**
     * Display detailed statistics and analytics.
     */
    public function index(Request $request): View
    {
        $activeYear   = AcademicYear::getActive();
        $activeYearId = $activeYear?->id ?? 0;

        // ── Évolution des inscriptions par mois ──────────────────────────
        $enrollmentsByMonth = Enrollment::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->where('academic_year_id', $activeYearId)
            ->where('status', 'VALIDE')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // ── Répartition par genre et par cycle ───────────────────────────
        $enrollmentsByGenderCycle = Enrollment::selectRaw(
            "cycles.id as cycle_id, cycles.name as cycle_name, students.gender, COUNT(*) as total"
        )
        ->join('students', 'enrollments.student_id', '=', 'students.id')
        ->join('classes', 'enrollments.class_id', '=', 'classes.id')
        ->join('levels', 'classes.level_id', '=', 'levels.id')
        ->join('cycles', 'levels.cycle_id', '=', 'cycles.id')
        ->where('enrollments.academic_year_id', $activeYearId)
        ->where('enrollments.status', 'VALIDE')
        ->groupBy('cycles.id', 'cycles.name', 'students.gender')
        ->orderBy('cycles.id')
        ->get()
        ->groupBy('cycle_name');

        // ── Classement des classes par effectif ──────────────────────────
        $classesByEnrollment = Enrollment::selectRaw('classes.id as class_id, classes.name as class_name, levels.name as level_name, cycles.name as cycle_name, COUNT(*) as total')
            ->join('classes', 'enrollments.class_id', '=', 'classes.id')
            ->join('levels', 'classes.level_id', '=', 'levels.id')
            ->join('cycles', 'levels.cycle_id', '=', 'cycles.id')
            ->where('enrollments.academic_year_id', $activeYearId)
            ->where('enrollments.status', 'VALIDE')
            ->groupBy('classes.id', 'classes.name', 'levels.name', 'cycles.name')
            ->orderByDesc('total')
            ->get();

        // ── Moyennes générales par classe (dernier période) ───────────────
        $periods       = Period::where('academic_year_id', $activeYearId)->orderBy('order')->get();
        $latestPeriod  = $periods->last();

        $averagesByClass = [];
        if ($latestPeriod) {
            $averagesByClass = ReportCard::selectRaw(
                "classes.name as class_name, AVG(report_cards.general_average) as avg_class, COUNT(*) as nb"
            )
            ->join('classes', 'report_cards.class_id', '=', 'classes.id')
            ->where('report_cards.period_id', $latestPeriod->id)
            ->groupBy('classes.name')
            ->orderByDesc('avg_class')
            ->get();
        }

        // ── Statistiques enseignants par type de contrat ─────────────────
        $teachersByContract = Teacher::selectRaw('employment_type, COUNT(*) as total')
            ->where('status', 'ACTIF')
            ->groupBy('employment_type')
            ->pluck('total', 'employment_type');

        // ── Taux d'absence moyen par matière ─────────────────────────────
        $absenceBySubject = Grade::selectRaw(
            'subjects.name as subject_name, COUNT(*) as total_grades, SUM(grades.is_absent) as total_absent'
        )
        ->join('evaluations', 'grades.evaluation_id', '=', 'evaluations.id')
        ->join('subjects', 'evaluations.subject_id', '=', 'subjects.id')
        ->groupBy('subjects.name')
        ->having('total_grades', '>', 0)
        ->orderByDesc('total_absent')
        ->limit(10)
        ->get()
        ->map(function ($row) {
            $row->absence_rate = round(($row->total_absent / $row->total_grades) * 100, 1);
            return $row;
        });

        return view('stats.index', compact(
            'activeYear',
            'enrollmentsByMonth',
            'enrollmentsByGenderCycle',
            'classesByEnrollment',
            'averagesByClass',
            'latestPeriod',
            'teachersByContract',
            'absenceBySubject'
        ));
    }
}
