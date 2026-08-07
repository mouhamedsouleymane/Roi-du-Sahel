<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Period;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Services\ReportCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportCardController extends Controller
{
    public function __construct(private readonly ReportCardService $reportCardService) {}

    /**
     * Display report cards for a class/period selection.
     */
    public function index(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $classes    = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->get();

        $selectedClassId  = (int) $request->query('class_id', $classes->first()?->id ?? 0);
        $selectedClass    = $classes->find($selectedClassId);

        $periods          = Period::where('academic_year_id', $activeYear?->id ?? 0)
            ->orderBy('order')
            ->get();

        $selectedPeriodId = (int) $request->query('period_id', $periods->first()?->id ?? 0);
        $selectedPeriod   = $periods->find($selectedPeriodId);

        $reportCards = ReportCard::with('student')
            ->where('class_id', $selectedClassId)
            ->where('period_id', $selectedPeriodId)
            ->orderBy('rank')
            ->get();

        return view('report_cards.index', compact(
            'reportCards',
            'classes',
            'periods',
            'selectedClassId',
            'selectedPeriodId',
            'selectedClass',
            'selectedPeriod',
            'activeYear'
        ));
    }

    /**
     * Trigger generation of report cards for a class + period.
     */
    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id'  => 'required|exists:classes,id',
            'period_id' => 'required|exists:periods,id',
        ]);

        $period = Period::findOrFail($validated['period_id']);
        $count  = $this->reportCardService->generateForClass($validated['class_id'], $period);

        return redirect()->route('report-cards.index', [
            'class_id'  => $validated['class_id'],
            'period_id' => $validated['period_id'],
        ])->with('status', "Bulletins générés pour {$count} élève(s) avec succès.");
    }

    /**
     * Publish all report cards for a given class and period.
     */
    public function publish(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id'  => 'required|exists:classes,id',
            'period_id' => 'required|exists:periods,id',
        ]);

        $period = Period::findOrFail($validated['period_id']);
        $count  = $this->reportCardService->publishForClass($validated['class_id'], $period);

        return redirect()->route('report-cards.index', [
            'class_id'  => $validated['class_id'],
            'period_id' => $validated['period_id'],
        ])->with('status', "{$count} bulletin(s) publiés avec succès.");
    }

    /**
     * Show one student's full bulletin for a period.
     */
    public function show(ReportCard $reportCard): View
    {
        $reportCard->load(['student.enrollments.schoolClass', 'period.academicYear', 'schoolClass.level.cycle']);

        $period   = $reportCard->period;
        $student  = $reportCard->student;
        $classId  = $reportCard->class_id;

        // Build subject-level details for this report card
        $subjectDetails = $this->reportCardService->calculateGeneralAverage($student, $period, $classId)['details'];

        return view('report_cards.show', compact('reportCard', 'subjectDetails'));
    }
}
