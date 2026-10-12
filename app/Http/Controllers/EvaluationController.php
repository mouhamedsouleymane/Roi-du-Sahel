<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassSubjectCoefficient;
use App\Models\Evaluation;
use App\Models\Grade;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
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
        $periods = Period::where('academic_year_id', $activeYear?->id ?? 0)
            ->orderBy('type')
            ->orderBy('order')
            ->get();

        $classes = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->orderBy('name')
            ->get();

        $selectedClassId = $request->query('class_id');
        $selectedClass = $selectedClassId ? $classes->find($selectedClassId) : null;

        // Auto-select appropriate period: if class is selected and no period explicitly given, pick the class's period type
        if ($request->has('period_id')) {
            $selectedPeriodId = (int) $request->query('period_id');
        } elseif ($selectedClass) {
            $selectedPeriodId = $periods->where('type', $selectedClass->expectedPeriodType())->first()?->id ?? ($periods->first()?->id ?? 0);
        } else {
            $selectedPeriodId = $periods->first()?->id ?? 0;
        }

        $selectedCycleId = $request->query('cycle_id');
        $selectedLevelId = $request->query('level_id');
        $selectedSubjectId = $request->query('subject_id');
        $search = $request->query('search');

        $evaluationsQuery = Evaluation::with([
            'subject',
            'teacher.user',
            'schoolClass.level.cycle',
            'period',
            'grades',
        ])
            ->whereHas('schoolClass', function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear?->id ?? 0);
            });

        if ($selectedPeriodId) {
            $evaluationsQuery->where('period_id', $selectedPeriodId);
        }

        if ($selectedCycleId) {
            $evaluationsQuery->whereHas('schoolClass.level', function ($q) use ($selectedCycleId) {
                $q->where('cycle_id', $selectedCycleId);
            });
        }

        if ($selectedLevelId) {
            $evaluationsQuery->whereHas('schoolClass', function ($q) use ($selectedLevelId) {
                $q->where('level_id', $selectedLevelId);
            });
        }

        if ($selectedClassId) {
            $evaluationsQuery->where('class_id', $selectedClassId);
        }

        if ($selectedSubjectId) {
            $evaluationsQuery->where('subject_id', $selectedSubjectId);
        }

        if ($search) {
            $evaluationsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('subject', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('teacher.user', function ($tq) use ($search) {
                        $tq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('schoolClass', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $evaluations = $evaluationsQuery
            ->orderBy('evaluation_date', 'desc')
            ->get();

        // Groupement hiérarchique : Cycle → Niveau → Classe → Matière → Évaluations
        // Key format: "cycleName|||levelName" so we can sort by cycle order
        $groupedRaw = $evaluations->groupBy([
            fn ($e) => ($e->schoolClass?->level?->cycle?->name ?? 'Autre').'|||'.($e->schoolClass?->level?->name ?? 'Niveau non défini'),
            fn ($e) => $e->schoolClass?->name ?? 'Classe non définie',
            fn ($e) => $e->subject?->name ?? 'Matière non définie',
        ]);

        // Sort by the combined key (cycle + level) then rebuild with clean level name as key
        $groupedEvaluations = $groupedRaw->sortKeys()->mapWithKeys(function ($classesGroup, $key) {
            [, $levelName] = explode('|||', $key, 2);

            return [$levelName => $classesGroup->sortKeys()];
        });

        $teachers = Teacher::with('user')->where('status', 'ACTIF')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        // Affectations officielles complètes pour l'année
        $allAssignments = TeacherAssignment::with(['subject', 'teacher.user'])
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->get();

        $levelCoefficients = ClassSubjectCoefficient::with('subject')
            ->get()
            ->groupBy('level_id');

        // Mappage exhaustif par classe : Matières de la classe + Enseignant affecté + Coefficient
        $assignmentsByClass = $classes->mapWithKeys(function ($class) use ($allAssignments, $levelCoefficients, $subjects) {
            $classAssignments = $allAssignments->where('class_id', $class->id);
            $items = collect();

            // 1. Matières affectées à un enseignant dans cette classe
            foreach ($classAssignments as $a) {
                if ($a->subject) {
                    $levelCoeff = $levelCoefficients->get($class->level_id)?->firstWhere('subject_id', $a->subject_id);
                    $items->push([
                        'subject_id' => $a->subject_id,
                        'subject_name' => $a->subject->name,
                        'teacher_id' => $a->teacher_id,
                        'teacher_name' => $a->teacher?->user?->name ?? 'Enseignant non assigné',
                        'coefficient' => $levelCoeff?->coefficient ?? 1,
                    ]);
                }
            }

            // 2. Matières du niveau non encore affectées à un enseignant
            if ($class->level_id && $levelCoefficients->has($class->level_id)) {
                foreach ($levelCoefficients->get($class->level_id) as $lc) {
                    if ($lc->subject && ! $items->contains('subject_id', $lc->subject_id)) {
                        $items->push([
                            'subject_id' => $lc->subject_id,
                            'subject_name' => $lc->subject->name,
                            'teacher_id' => null,
                            'teacher_name' => null,
                            'coefficient' => $lc->coefficient ?? 1,
                        ]);
                    }
                }
            }

            // 3. Fallback si aucune matière n'est configurée pour ce niveau / classe
            if ($items->isEmpty()) {
                foreach ($subjects as $sbj) {
                    $items->push([
                        'subject_id' => $sbj->id,
                        'subject_name' => $sbj->name,
                        'teacher_id' => null,
                        'teacher_name' => null,
                        'coefficient' => 1,
                    ]);
                }
            }

            return [$class->id => $items->values()];
        });

        // Affectations pour la classe sélectionnée (ou la 1ère classe pour initialisation)
        $defaultClassId = $selectedClassId ?: ($classes->first()?->id ?? 0);
        $classAssignments = $defaultClassId
            ? $allAssignments->where('class_id', $defaultClassId)->values()
            : collect();

        $assignedSubjects = $classAssignments->pluck('subject')->filter()->unique('id');
        $classSubjects = $assignedSubjects->isNotEmpty()
            ? $assignedSubjects->sortBy('name')->values()
            : $subjects;

        return view('evaluations.index', compact(
            'evaluations',
            'groupedEvaluations',
            'classes',
            'periods',
            'teachers',
            'subjects',
            'classSubjects',
            'selectedClassId',
            'selectedPeriodId',
            'selectedCycleId',
            'selectedLevelId',
            'selectedSubjectId',
            'search',
            'selectedClass',
            'activeYear',
            'classAssignments',
            'assignmentsByClass'
        ));
    }

    /**
     * Store a new evaluation.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_id' => 'required|exists:periods,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'title' => 'required|string|max:125',
            'type' => 'required|in:DEVOIR,COMPOSITION,CONTROLE,EXAMEN',
            'max_score' => 'required|numeric|min:1|max:100',
            'evaluation_date' => 'required|date',
        ]);

        $schoolClass = SchoolClass::with('level.cycle')->findOrFail($validated['class_id']);
        $period = Period::findOrFail($validated['period_id']);

        if (! $period->isApplicableToClass($schoolClass)) {
            $expected = $schoolClass->expectedPeriodType() === 'SEMESTRE' ? 'un Semestre (Collège / Lycée)' : 'un Trimestre (Maternelle / Primaire)';

            return back()->withInput()->withErrors([
                'period_id' => "La classe {$schoolClass->name} est organisée par {$expected}.",
            ]);
        }

        $validated['coefficient'] = 1;

        Evaluation::create($validated);

        return redirect()->route('evaluations.index', [
            'class_id' => $validated['class_id'],
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
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:students,id',
            'grades.*.score' => 'nullable|numeric|min:0|max:'.$evaluation->max_score,
            'grades.*.is_absent' => 'nullable|boolean',
        ]);

        foreach ($request->input('grades') as $gradeData) {
            $isAbsent = isset($gradeData['is_absent']) && $gradeData['is_absent'];

            Grade::updateOrCreate(
                [
                    'evaluation_id' => $evaluation->id,
                    'student_id' => $gradeData['student_id'],
                ],
                [
                    'score' => $isAbsent ? null : ($gradeData['score'] ?? null),
                    'is_absent' => $isAbsent,
                    'comment' => $gradeData['comment'] ?? null,
                ]
            );
        }

        return redirect()->route('evaluations.grades', $evaluation)
            ->with('status', 'Les notes ont été enregistrées avec succès.');
    }

    /**
     * Display the specified evaluation.
     */
    public function show(Evaluation $evaluation): View
    {
        $evaluation->load(['subject', 'teacher.user', 'schoolClass.level.cycle', 'schoolClass.enrollments', 'period', 'grades.student']);

        return view('evaluations.show', compact('evaluation'));
    }

    /**
     * Show the form for editing the specified evaluation.
     */
    public function edit(Evaluation $evaluation): View
    {
        $evaluation->load(['schoolClass.level.cycle', 'subject', 'teacher']);
        $activeYear = AcademicYear::getActive();
        $classes = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->get();
        $teachers = Teacher::with('user')->where('status', 'ACTIF')->get();

        $classAssignments = TeacherAssignment::with(['subject', 'teacher.user'])
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->where('class_id', $evaluation->class_id)
            ->get();

        $assignedSubjects = $classAssignments->pluck('subject')->filter()->unique('id');
        $subjects = $assignedSubjects->isNotEmpty()
            ? $assignedSubjects->sortBy('name')->values()
            : Subject::where('is_active', true)->orderBy('name')->get();

        $periods = Period::where('academic_year_id', $activeYear?->id ?? 0)->orderBy('order')->get();

        return view('evaluations.edit', compact('evaluation', 'classes', 'teachers', 'subjects', 'periods'));
    }

    /**
     * Update the specified evaluation in storage.
     */
    public function update(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:125',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'type' => 'required|in:DEVOIR,COMPOSITION,CONTROLE,EXAMEN',
            'max_score' => 'required|numeric|min:1|max:100',
            'evaluation_date' => 'required|date',
        ]);

        $evaluation->update($validated);

        return redirect()->route('evaluations.show', $evaluation)
            ->with('status', 'L\'évaluation a été mise à jour avec succès.');
    }

    /**
     * Remove the specified evaluation from storage.
     */
    public function destroy(Evaluation $evaluation): RedirectResponse
    {
        $classId = $evaluation->class_id;
        $periodId = $evaluation->period_id;

        $evaluation->delete();

        return redirect()->route('evaluations.index', ['class_id' => $classId, 'period_id' => $periodId])
            ->with('status', 'L\'évaluation a été supprimée.');
    }
}
