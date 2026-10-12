<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
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

        $classes = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->orderBy('name')
            ->get();

        $periods = Period::where('academic_year_id', $activeYear?->id ?? 0)
            ->orderBy('order')
            ->get();

        // Filters — period is always set (defaults to first), class is optional
        $selectedPeriodId = (int) $request->query('period_id', $periods->first()?->id ?? 0);
        $selectedPeriod = $periods->find($selectedPeriodId);
        $selectedClassId = $request->query('class_id');   // null = all classes
        $search = trim((string) $request->query('search', ''));

        $query = ReportCard::with(['student', 'schoolClass.level.cycle'])
            ->where('period_id', $selectedPeriodId)
            ->whereHas('schoolClass', fn ($q) => $q->where('academic_year_id', $activeYear?->id ?? 0));

        if ($selectedClassId) {
            $query->where('class_id', $selectedClassId);
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
            fn ($rc) => ($rc->schoolClass?->level?->cycle?->name ?? 'Autre').'|||'.($rc->schoolClass?->level?->name ?? 'Niveau non défini'),
            fn ($rc) => $rc->schoolClass?->name ?? 'Classe non définie',
        ]);

        $grouped = $groupedRaw->sortKeys()->mapWithKeys(function ($classGroup, $key) {
            [, $levelName] = explode('|||', $key, 2);

            return [$levelName => $classGroup->sortKeys()];
        });

        // Global stats for the header
        $totalBulletins = $allReportCards->count();
        $totalSuccess = $allReportCards->filter(fn ($rc) => (float) $rc->general_average >= 10)->count();
        $overallAvg = $totalBulletins > 0
            ? round($allReportCards->avg(fn ($rc) => (float) $rc->general_average), 2)
            : null;

        return view('report_cards.index', compact(
            'grouped',
            'allReportCards',
            'classes',
            'periods',
            'selectedPeriodId',
            'selectedPeriod',
            'selectedClassId',
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

        // Build subject-level details for this report card
        $subjectDetails = $this->reportCardService->calculateGeneralAverage($student, $period, $classId)['details'];

        return view('report_cards.show', compact('reportCard', 'subjectDetails'));
    }

    /**
     * Download the report card as a PDF.
     */
    public function pdf(ReportCard $reportCard): Response
    {
        $reportCard->load([
            'student.enrollments.schoolClass',
            'period.academicYear',
            'schoolClass.level.cycle',
        ]);

        $period = $reportCard->period;
        $student = $reportCard->student;
        $classId = $reportCard->class_id;

        $subjectDetails = $this->reportCardService->calculateGeneralAverage($student, $period, $classId)['details'];

        $pdf = Pdf::loadView('report_cards.pdf', compact('reportCard', 'subjectDetails'))
            ->setPaper('a4', 'portrait');

        $filename = 'bulletin-'.str($student->matricule.'-'.$student->last_name.'-'.$period->name)->slug().'.pdf';

        return $pdf->download($filename);
    }
}
