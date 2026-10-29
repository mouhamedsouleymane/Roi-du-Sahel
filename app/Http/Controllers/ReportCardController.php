<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Period;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Services\ReportCardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportCardController extends Controller
{
    public function __construct(private readonly ReportCardService $reportCardService) {}

    /**
     * Display all report cards grouped by level → class, with optional filters.
     */
    public function index(Request $request): View
    {
        $activeYear = AcademicYear::getActive();

        $cycles = Cycle::query()->where('is_active', true)->orderBy('id')->get();

        $classes = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->orderBy('name')
            ->get();

        $periods = Period::query()->where('academic_year_id', $activeYear?->id ?? 0)
            ->orderBy('order')
            ->get();

        $selectedClassId = $request->query('class_id');   // null = all classes
        $selectedClass = $selectedClassId ? $classes->find($selectedClassId) : null;
        $selectedPeriodParam = $request->query('period_id');
        $selectedCycleCode = $request->query('cycle');

        $order1PeriodIds = $periods->where('order', 1)->pluck('id')->all();

        // Filters — auto-select appropriate period based on class's cycle
        if ($request->filled('period_id') && is_numeric($selectedPeriodParam)) {
            $selectedPeriodId = (int) $selectedPeriodParam;
            $selectedPeriod = $periods->find($selectedPeriodId);
            $isMultiPeriod = false;
        } elseif ($selectedClass) {
            $selectedPeriod = $periods->where('type', $selectedClass->expectedPeriodType())->where('order', 1)->first()
                ?? ($periods->where('type', $selectedClass->expectedPeriodType())->first() ?? $periods->first());
            $selectedPeriodId = $selectedPeriod?->id ?? 0;
            $isMultiPeriod = false;
        } else {
            // Default: show current primary periods (Order 1: Trimestre 1 for primary & Semestre 1 for secondary)
            $selectedPeriodId = null;
            $selectedPeriod = null;
            $isMultiPeriod = true;
        }

        $search = trim((string) $request->query('search', ''));

        $query = ReportCard::query()->with(['student', 'schoolClass.level.cycle'])
            ->whereHas('schoolClass', fn ($q) => $q->where('academic_year_id', $activeYear?->id ?? 0));

        if (! $isMultiPeriod && $selectedPeriodId) {
            $query->where('period_id', $selectedPeriodId);
        } elseif ($isMultiPeriod && ! empty($order1PeriodIds)) {
            $query->whereIn('period_id', $order1PeriodIds);
        }

        if ($selectedClassId) {
            $query->where('class_id', $selectedClassId);
        }

        if ($selectedCycleCode && in_array(strtoupper($selectedCycleCode), ['MATERNELLE', 'PRIMAIRE', 'COLLEGE', 'LYCEE'], true)) {
            $query->whereHas('schoolClass.level.cycle', fn ($q) => $q->where('code', strtoupper($selectedCycleCode)));
        }

        if ($search !== '') {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('matricule', 'like', "%{$search}%");
            });
        }

        $allReportCards = $query->orderBy('rank')->get();

        // Group by cycle key → level name → class name (sorted)
        $groupedRaw = $allReportCards->groupBy([
            fn ($rc) => ($rc->schoolClass?->level?->cycle?->code ?? 'AUTRE').'|||'.($rc->schoolClass?->level?->cycle?->name ?? 'Autre').'|||'.($rc->schoolClass?->level?->name ?? 'Niveau non défini'),
            fn ($rc) => $rc->schoolClass?->name ?? 'Classe non définie',
        ]);

        $levelCycleMap = [];
        $grouped = $groupedRaw->sortKeys()->mapWithKeys(function ($classGroup, $key) use (&$levelCycleMap) {
            [$cycleCode, $cycleName, $levelName] = explode('|||', $key, 3);
            $levelCycleMap[$levelName] = [
                'code' => $cycleCode,
                'name' => $cycleName,
            ];

            return [$levelName => $classGroup->sortKeys()];
        });

        // Global stats for the header
        $totalBulletins = $allReportCards->count();
        $totalSuccess = $allReportCards->filter(fn ($rc) => (float) $rc->general_average >= 10)->count();
        $overallAvg = $totalBulletins > 0
            ? round($allReportCards->avg(fn ($rc) => (float) $rc->general_average), 2)
            : null;

        // Stats by cycle for quick tabs
        $cycleStats = [];
        foreach ($cycles as $c) {
            $cycleCards = $allReportCards->filter(fn ($rc) => $rc->schoolClass?->level?->cycle_id === $c->id);
            $cycleCount = $cycleCards->count();
            $cycleStats[$c->code] = [
                'count' => $cycleCount,
                'avg' => $cycleCount > 0 ? round($cycleCards->avg(fn ($rc) => (float) $rc->general_average), 2) : 0,
                'success' => $cycleCards->filter(fn ($rc) => (float) $rc->general_average >= 10)->count(),
            ];
        }

        return view('report_cards.index', compact(
            'grouped',
            'levelCycleMap',
            'allReportCards',
            'classes',
            'periods',
            'cycles',
            'cycleStats',
            'selectedPeriodId',
            'selectedPeriod',
            'isMultiPeriod',
            'selectedClassId',
            'selectedCycleCode',
            'search',
            'activeYear',
            'totalBulletins',
            'totalSuccess',
            'overallAvg',
        ));
    }

    /**
     * Trigger generation of report cards for a class + period.
     */
    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'period_id' => 'required|exists:periods,id',
        ]);

        $schoolClass = SchoolClass::with('level.cycle')->findOrFail($validated['class_id']);
        $period = Period::findOrFail($validated['period_id']);

        if (! $period->isApplicableToClass($schoolClass)) {
            $expected = $schoolClass->expectedPeriodType() === 'SEMESTRE' ? 'un Semestre (Collège / Lycée)' : 'un Trimestre (Maternelle / Primaire)';

            return redirect()->route('report-cards.index', [
                'class_id' => $validated['class_id'],
                'period_id' => $validated['period_id'],
            ])->with('error', "La classe {$schoolClass->name} fonctionne par {$expected}.");
        }

        $count = $this->reportCardService->generateForClass($validated['class_id'], $period);

        return redirect()->route('report-cards.index', [
            'class_id' => $validated['class_id'],
            'period_id' => $validated['period_id'],
        ])->with('status', "Bulletins générés pour {$count} élève(s) avec succès.");
    }

    /**
     * Publish all report cards for a given class and period.
     */
    public function publish(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'period_id' => 'required|exists:periods,id',
        ]);

        $schoolClass = SchoolClass::with('level.cycle')->findOrFail($validated['class_id']);
        $period = Period::findOrFail($validated['period_id']);

        if (! $period->isApplicableToClass($schoolClass)) {
            $expected = $schoolClass->expectedPeriodType() === 'SEMESTRE' ? 'un Semestre (Collège / Lycée)' : 'un Trimestre (Maternelle / Primaire)';

            return redirect()->route('report-cards.index', [
                'class_id' => $validated['class_id'],
                'period_id' => $validated['period_id'],
            ])->with('error', "La classe {$schoolClass->name} fonctionne par {$expected}.");
        }

        $count = $this->reportCardService->publishForClass($validated['class_id'], $period);

        return redirect()->route('report-cards.index', [
            'class_id' => $validated['class_id'],
            'period_id' => $validated['period_id'],
        ])->with('status', "{$count} bulletin(s) publiés avec succès.");
    }

    /**
     * Show one student's full bulletin for a period.
     */
    public function show(ReportCard $reportCard): View
    {
        $reportCard->load(['student.enrollments.schoolClass', 'period.academicYear', 'schoolClass.level.cycle']);

        $period = $reportCard->period;
        $student = $reportCard->student;
        $classId = $reportCard->class_id;

        $payload = $reportCard->details;
        if (! $payload || empty($payload['details'])) {
            $payload = $this->reportCardService->calculateGeneralAverage($student, $period, $classId);
        }

        $subjectDetails = $payload['details'] ?? [];
        $totals = $payload['totals'] ?? [];
        $classStats = $payload['class_stats'] ?? [
            'class_average' => $reportCard->class_average,
            'max_average' => $reportCard->max_average,
            'min_average' => $reportCard->min_average,
            'total_students' => $reportCard->total_students,
        ];
        $annualProgression = $payload['annual_progression'] ?? [];
        $attendance = $payload['attendance'] ?? [];
        $discipline = $payload['discipline'] ?? [];

        return view('report_cards.show', compact(
            'reportCard',
            'subjectDetails',
            'totals',
            'classStats',
            'annualProgression',
            'attendance',
            'discipline'
        ));
    }

    /**
     * Download or stream the report card as a PDF.
     */
    public function pdf(Request $request, ReportCard $reportCard): Response
    {
        $reportCard->load([
            'student.enrollments.schoolClass',
            'period.academicYear',
            'schoolClass.level.cycle',
        ]);

        $period = $reportCard->period;
        $student = $reportCard->student;
        $classId = $reportCard->class_id;

        $payload = $reportCard->details;
        if (! $payload || empty($payload['details'])) {
            $payload = $this->reportCardService->calculateGeneralAverage($student, $period, $classId);
        }

        $subjectDetails = $payload['details'] ?? [];
        $totals = $payload['totals'] ?? [];
        $classStats = $payload['class_stats'] ?? [
            'class_average' => $reportCard->class_average,
            'max_average' => $reportCard->max_average,
            'min_average' => $reportCard->min_average,
            'total_students' => $reportCard->total_students,
        ];
        $annualProgression = $payload['annual_progression'] ?? [];
        $attendance = $payload['attendance'] ?? [];
        $discipline = $payload['discipline'] ?? [];

        $pdf = Pdf::loadView('report_cards.pdf', compact(
            'reportCard',
            'subjectDetails',
            'totals',
            'classStats',
            'annualProgression',
            'attendance',
            'discipline'
        ))->setPaper('a4', 'portrait');

        $filename = 'bulletin-'.str($student->matricule.'-'.$student->last_name.'-'.$period->name)->slug().'.pdf';

        if ($request->boolean('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
