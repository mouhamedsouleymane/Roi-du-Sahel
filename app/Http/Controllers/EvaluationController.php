<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Evaluation;
use App\Models\Grade;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvaluationController extends Controller
{
    /**
     * List evaluations for a class/period, with grade entry form.
     */
    public function index(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $classes    = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->get();

        $selectedClassId = (int) $request->query('class_id', $classes->first()?->id ?? 0);
        $selectedClass   = $classes->find($selectedClassId);

        $periods = Period::where('academic_year_id', $activeYear?->id ?? 0)
            ->orderBy('order')
            ->get();

        $selectedPeriodId = (int) $request->query('period_id', $periods->first()?->id ?? 0);

        $evaluations = Evaluation::with(['subject', 'teacher.user', 'grades.student'])
            ->where('class_id', $selectedClassId)
            ->where('period_id', $selectedPeriodId)
            ->orderBy('evaluation_date')
            ->get();

        $teachers = Teacher::with('user')->where('status', 'ACTIF')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        return view('evaluations.index', compact(
            'evaluations',
            'classes',
            'periods',
            'teachers',
            'subjects',
            'selectedClassId',
            'selectedPeriodId',
            'selectedClass',
            'activeYear'
        ));
    }

    /**
     * Store a new evaluation.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_id'       => 'required|exists:periods,id',
            'class_id'        => 'required|exists:classes,id',
            'subject_id'      => 'required|exists:subjects,id',
            'teacher_id'      => 'required|exists:teachers,id',
            'title'           => 'required|string|max:125',
            'type'            => 'required|in:DEVOIR,COMPOSITION,CONTROLE,EXAMEN',
            'max_score'       => 'required|numeric|min:1|max:100',
            'coefficient'     => 'required|numeric|min:0.5|max:5',
            'evaluation_date' => 'required|date',
        ]);

        Evaluation::create($validated);

        return redirect()->route('evaluations.index', [
            'class_id'  => $validated['class_id'],
            'period_id' => $validated['period_id'],
        ])->with('status', 'L\'évaluation a été créée avec succès.');
    }

    /**
     * Show grade entry form for a specific evaluation.
     */
    public function grades(Evaluation $evaluation): View
    {
        $evaluation->load(['subject', 'teacher.user', 'schoolClass.level.cycle', 'period']);

        // Get all students enrolled in this class for the active year
        $students = Student::whereHas('enrollments', function ($q) use ($evaluation) {
            $q->where('class_id', $evaluation->class_id)
              ->where('academic_year_id', $evaluation->period->academic_year_id)
              ->where('status', 'VALIDE');
        })
        ->with(['grades' => function ($q) use ($evaluation) {
            $q->where('evaluation_id', $evaluation->id);
        }])
        ->orderBy('last_name')
        ->get();

        return view('evaluations.grades', compact('evaluation', 'students'));
    }

    /**
     * Save grades for a bulk submission (all students at once).
     */
    public function saveGrades(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $request->validate([
            'grades'               => 'required|array',
            'grades.*.student_id'  => 'required|exists:students,id',
            'grades.*.score'       => 'nullable|numeric|min:0|max:' . $evaluation->max_score,
            'grades.*.is_absent'   => 'nullable|boolean',
        ]);

        foreach ($request->input('grades') as $gradeData) {
            $isAbsent = isset($gradeData['is_absent']) && $gradeData['is_absent'];

            Grade::updateOrCreate(
                [
                    'evaluation_id' => $evaluation->id,
                    'student_id'    => $gradeData['student_id'],
                ],
                [
                    'score'     => $isAbsent ? null : ($gradeData['score'] ?? null),
                    'is_absent' => $isAbsent,
                    'comment'   => $gradeData['comment'] ?? null,
                ]
            );
        }

        return redirect()->route('evaluations.grades', $evaluation)
            ->with('status', 'Les notes ont été enregistrées avec succès.');
    }
}
