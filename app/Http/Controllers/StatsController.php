<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Expense;
use App\Models\Grade;
use App\Models\Invoice;
use App\Models\Period;
use App\Models\PreEnrollment;
use App\Models\ReportCard;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatsController extends Controller
{
    /**
     * Display detailed statistics and analytics.
     */
    public function index(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $activeYearId = $activeYear?->id ?? 0;

        $driver = DB::connection()->getDriverName();
        $monthExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', created_at) as month"
            : "DATE_FORMAT(created_at, '%Y-%m') as month";

        // ── 1. DÉMOGRAPHIE & EFFECTIFS ────────────────────────────────────
        $enrollmentsByMonth = Enrollment::selectRaw("{$monthExpr}, COUNT(*) as total")
            ->where('academic_year_id', $activeYearId)
            ->where('status', 'VALIDE')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $enrollmentsByGenderCycle = Enrollment::selectRaw(
            'cycles.id as cycle_id, cycles.name as cycle_name, students.gender, COUNT(*) as total'
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

        $classesByEnrollment = Enrollment::selectRaw('classes.id as class_id, classes.name as class_name, levels.name as level_name, cycles.name as cycle_name, COUNT(*) as total')
            ->join('classes', 'enrollments.class_id', '=', 'classes.id')
            ->join('levels', 'classes.level_id', '=', 'levels.id')
            ->join('cycles', 'levels.cycle_id', '=', 'cycles.id')
            ->where('enrollments.academic_year_id', $activeYearId)
            ->where('enrollments.status', 'VALIDE')
            ->groupBy('classes.id', 'classes.name', 'levels.name', 'cycles.name')
            ->orderByDesc('total')
            ->get();

        // ── 2. PÉDAGOGIE & RÉSULTATS ──────────────────────────────────────
        $periods = Period::where('academic_year_id', $activeYearId)->orderBy('order')->get();
        $latestPeriod = $periods->last();

        $averagesByClass = [];
        if ($latestPeriod) {
            $averagesByClass = ReportCard::selectRaw(
                'classes.name as class_name, AVG(report_cards.general_average) as avg_class, COUNT(*) as nb,
                 SUM(CASE WHEN report_cards.general_average >= 10 THEN 1 ELSE 0 END) as passing_count'
            )
                ->join('classes', 'report_cards.class_id', '=', 'classes.id')
                ->where('report_cards.period_id', $latestPeriod->id)
                ->groupBy('classes.name')
                ->orderByDesc('avg_class')
                ->get()
                ->map(function ($row) {
                    $row->pass_rate = $row->nb > 0 ? round(($row->passing_count / $row->nb) * 100, 1) : 0;

                    return $row;
                });
        }

        $absenceBySubject = Grade::selectRaw(
            'subjects.name as subject_name, COUNT(*) as total_grades, SUM(grades.is_absent) as total_absent'
        )
            ->join('evaluations', 'grades.evaluation_id', '=', 'evaluations.id')
            ->join('subjects', 'evaluations.subject_id', '=', 'subjects.id')
            ->groupBy('subjects.name')
            ->having('total_grades', '>', 0)
            ->orderByDesc('total_absent')
            ->limit(8)
            ->get()
            ->map(function ($row) {
                $row->absence_rate = round(($row->total_absent / $row->total_grades) * 100, 1);

                return $row;
            });

        // ── 3. ANALYSE FINANCIÈRE & TRÉSORERIE ────────────────────────────
        $receiptsByFeeType = Invoice::selectRaw('fee_types.name as fee_name, SUM(invoices.amount_paid) as total_paid, SUM(invoices.amount_due) as total_due')
            ->join('fee_types', 'invoices.fee_type_id', '=', 'fee_types.id')
            ->where('invoices.academic_year_id', $activeYearId)
            ->groupBy('fee_types.name')
            ->orderByDesc('total_paid')
            ->get();

        $expensesByCategory = Expense::selectRaw('expense_categories.name, expense_categories.color, SUM(expenses.amount) as total, COUNT(expenses.id) as count')
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->where('expenses.academic_year_id', $activeYearId)
            ->groupBy('expense_categories.name', 'expense_categories.color')
            ->orderByDesc('total')
            ->get();

        $recoveryByCycle = Invoice::selectRaw('cycles.name as cycle_name, SUM(invoices.amount_due) as total_due, SUM(invoices.amount_paid) as total_paid')
            ->join('enrollments', 'invoices.enrollment_id', '=', 'enrollments.id')
            ->join('classes', 'enrollments.class_id', '=', 'classes.id')
            ->join('levels', 'classes.level_id', '=', 'levels.id')
            ->join('cycles', 'levels.cycle_id', '=', 'cycles.id')
            ->where('invoices.academic_year_id', $activeYearId)
            ->groupBy('cycles.id', 'cycles.name')
            ->orderBy('cycles.id')
            ->get()
            ->map(function ($row) {
                $row->recovery_rate = $row->total_due > 0 ? round(($row->total_paid / $row->total_due) * 100, 1) : 0;

                return $row;
            });

        // ── 4. RESSOURCES HUMAINES & VIE SCOLAIRE ─────────────────────────
        $teachersByContract = Teacher::selectRaw('employment_type, COUNT(*) as total')
            ->where('status', 'ACTIF')
            ->groupBy('employment_type')
            ->pluck('total', 'employment_type');

        $preEnrollmentStats = PreEnrollment::selectRaw('status, COUNT(*) as total')
            ->where('academic_year_id', $activeYearId)
            ->groupBy('status')
            ->pluck('total', 'status');

        $attendanceStats = Attendance::selectRaw('status, COUNT(*) as total')
            ->where('academic_year_id', $activeYearId)
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalAttendances = $attendanceStats->sum();
        $presentCount = $attendanceStats['PRESENT'] ?? 0;
        $overallAttendanceRate = $totalAttendances > 0 ? round(($presentCount / $totalAttendances) * 100, 1) : 100;

        return view('stats.index', compact(
            'activeYear',
            'enrollmentsByMonth',
            'enrollmentsByGenderCycle',
            'classesByEnrollment',
            'averagesByClass',
            'latestPeriod',
            'teachersByContract',
            'absenceBySubject',
            'receiptsByFeeType',
            'expensesByCategory',
            'recoveryByCycle',
            'preEnrollmentStats',
            'attendanceStats',
            'overallAttendanceRate'
        ));
    }
}
