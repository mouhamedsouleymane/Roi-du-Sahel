<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeacherAssignmentController extends Controller
{
    /**
     * Display a listing of teacher assignments grouped by cycle.
     */
    public function index(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $cycles = Cycle::where('is_active', true)->orderBy('id')->get();
        $teachers = Teacher::with(['user', 'cycle'])->where('status', 'ACTIF')->get();
        $classes = SchoolClass::with('level.cycle')->where('academic_year_id', $activeYear?->id ?? 0)->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        $query = TeacherAssignment::with(['teacher.user', 'teacher.cycle', 'schoolClass.level.cycle', 'subject'])
            ->when($activeYear, function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id);
            });

        if ($request->filled('cycle_id')) {
            $query->whereHas('schoolClass.level', function ($q) use ($request) {
                $q->where('cycle_id', $request->cycle_id);
            });
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('teacher.user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%");
                })
                    ->orWhereHas('teacher', function ($tq) use ($search) {
                        $tq->where('matricule', 'like', "%{$search}%");
                    })
                    ->orWhereHas('schoolClass', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('subject', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $assignments = $query->get();

        // Group assignments by Cycle
        $assignmentsByCycle = $assignments->groupBy(function ($asn) {
            return $asn->schoolClass?->level?->cycle?->name ?? 'Non défini';
        });

        // Compute KPIs
        $totalAssignments = $assignments->count();
        $coveredClassesCount = $assignments->pluck('class_id')->unique()->count();
        $assignedTeachersCount = $assignments->pluck('teacher_id')->unique()->count();
        $coveredCyclesCount = $assignmentsByCycle->count();
        $totalClassesCount = $classes->count();

        // Class coverage: assigned subjects count vs configured subjects count per class
        $classCoverage = $classes->mapWithKeys(function (SchoolClass $cls) use ($assignments) {
            $configuredCount = DB::table('class_subject_coefficients')
                ->where('level_id', $cls->level_id)
                ->count();
            $assignedCount = $assignments->where('class_id', $cls->id)->count();

            return [$cls->id => [
                'class' => $cls,
                'configured' => $configuredCount,
                'assigned' => $assignedCount,
                'missing' => max(0, $configuredCount - $assignedCount),
                'percent' => $configuredCount > 0 ? round(($assignedCount / $configuredCount) * 100) : 0,
            ]];
        });

        return view('assignments.index', compact(
            'assignments',
            'assignmentsByCycle',
            'cycles',
            'teachers',
            'classes',
            'subjects',
            'activeYear',
            'totalAssignments',
            'coveredClassesCount',
            'assignedTeachersCount',
            'coveredCyclesCount',
            'classCoverage',
            'totalClassesCount'
        ));
    }

    /**
     * Show the form for creating a new teacher assignment.
     */
    public function create(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $preselectedClassId = $request->query('class_id', '');
        $preselectedTeacherId = $request->query('teacher_id', '');

        // Level-Subject coefficients and weekly hours map
        $coefficients = DB::table('class_subject_coefficients')
            ->select('level_id', 'subject_id', 'coefficient', 'weekly_hours')
            ->get()
            ->keyBy(fn ($item) => "{$item->level_id}_{$item->subject_id}")
            ->toArray();

        // Existing assignments for active year
        $existingAssignments = TeacherAssignment::with(['teacher.user', 'schoolClass.level'])
            ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
            ->get();

        $existingAssignmentsMap = [];
        foreach ($existingAssignments as $asn) {
            $key = "{$asn->class_id}_{$asn->subject_id}";
            $existingAssignmentsMap[$key] = [
                'teacher_id' => $asn->teacher_id,
                'teacher_name' => $asn->teacher?->user?->name ?? 'Autre enseignant',
            ];
        }

        $teachers = Teacher::with(['user', 'cycle', 'cycles', 'assignments.schoolClass.level'])
            ->where('status', 'ACTIF')
            ->get()
            ->map(function (Teacher $t) use ($activeYear, $coefficients) {
                $cycleIds = $t->cycles->pluck('id')->toArray();
                if ($t->cycle_id && ! in_array($t->cycle_id, $cycleIds)) {
                    $cycleIds[] = $t->cycle_id;
                }

                $yearAssignments = $t->assignments->where('academic_year_id', $activeYear?->id ?? 0);
                $assignedClassNames = $yearAssignments->pluck('schoolClass.name')->filter()->unique()->values()->toArray();
                $assignedClassIds = $yearAssignments->pluck('class_id')->filter()->unique()->values()->toArray();

                // Compute weekly hours already assigned
                $assignedHours = 0;
                foreach ($yearAssignments as $asn) {
                    $levelId = $asn->schoolClass?->level_id;
                    $subjectId = $asn->subject_id;
                    if ($levelId && $subjectId && isset($coefficients["{$levelId}_{$subjectId}"])) {
                        $assignedHours += (int) $coefficients["{$levelId}_{$subjectId}"]->weekly_hours;
                    }
                }

                return [
                    'id' => $t->id,
                    'name' => $t->user->name,
                    'matricule' => $t->matricule,
                    'speciality' => $t->speciality,
                    'is_polyvalent' => (bool) $t->is_polyvalent,
                    'cycle_id' => $t->cycle_id,
                    'cycle_name' => $t->cycle?->name ?? '—',
                    'cycle_ids' => $cycleIds,
                    'assigned_classes' => $assignedClassNames,
                    'assigned_class_ids' => $assignedClassIds,
                    'assigned_hours' => $assignedHours,
                    'assigned_count' => $yearAssignments->count(),
                ];
            });

        $classes = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->get()
            ->map(fn (SchoolClass $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'level_id' => $c->level_id,
                'level_name' => $c->level->name,
                'cycle_id' => $c->level->cycle_id,
                'cycle_name' => $c->level->cycle->name,
            ]);

        $subjects = Subject::with('levels')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Subject $s) => [
                'id' => $s->id,
                'code' => $s->code,
                'name' => $s->name,
                'level_ids' => $s->levels->pluck('id')->toArray(),
            ]);

        return view('assignments.create', compact(
            'teachers',
            'classes',
            'subjects',
            'activeYear',
            'coefficients',
            'existingAssignmentsMap',
            'preselectedClassId',
            'preselectedTeacherId'
        ));
    }

    /**
     * Store one or many teacher assignments at once (bulk).
     * Supports both By-Teacher and By-Class payloads.
     */
    public function store(Request $request): RedirectResponse
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return redirect()->back()->withErrors(['error' => 'Aucune année scolaire active.']);
        }

        // ══════════════════════════════════════════════════════════
        // CASE A : Saisie par Classe (Matrice de la classe)
        // ══════════════════════════════════════════════════════════
        if ($request->has('class_assignments') && $request->filled('class_id')) {
            $request->validate([
                'class_id' => 'required|exists:classes,id',
                'class_assignments' => 'required|array|min:1',
                'class_assignments.*.subject_id' => 'required|exists:subjects,id',
                'class_assignments.*.teacher_id' => 'nullable|exists:teachers,id',
            ]);

            $classId = (int) $request->class_id;
            $classAssignments = $request->input('class_assignments');
            $savedCount = 0;
            $rowErrors = [];

            foreach ($classAssignments as $idx => $item) {
                $subjectId = (int) $item['subject_id'];
                $teacherId = ! empty($item['teacher_id']) ? (int) $item['teacher_id'] : null;

                if ($teacherId) {
                    $teacher = Teacher::with(['cycle', 'user'])->find($teacherId);
                    if ($teacher && ! $teacher->canBeAssignedToClass($classId, (int) $activeYear->id)) {
                        $existingClassId = $teacher->assignments()
                            ->where('academic_year_id', $activeYear->id)
                            ->where('class_id', '!=', $classId)
                            ->value('class_id');
                        $existingClass = SchoolClass::find($existingClassId);
                        $existingClassName = $existingClass ? $existingClass->name : 'une autre classe';
                        $rowErrors[] = "{$teacher->full_name} est polyvalent et ne peut avoir qu'une seule classe (déjà titulaire de {$existingClassName}).";

                        continue;
                    }

                    TeacherAssignment::updateOrCreate(
                        [
                            'academic_year_id' => $activeYear->id,
                            'class_id' => $classId,
                            'subject_id' => $subjectId,
                        ],
                        ['teacher_id' => $teacherId]
                    );
                    $savedCount++;
                }
            }

            if (! empty($rowErrors)) {
                return redirect()->back()->withInput()->withErrors(['rows' => implode(' | ', $rowErrors)]);
            }

            $msg = $savedCount === 1
                ? "L'affectation pour cette classe a été enregistrée avec succès."
                : "{$savedCount} matières ont été affectées pour cette classe avec succès.";

            return redirect()->route('assignments.index')->with('status', $msg);
        }

        // ══════════════════════════════════════════════════════════
        // CASE B : Saisie par Enseignant (Défaut)
        // ══════════════════════════════════════════════════════════
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'rows' => 'required|array|min:1',
            'rows.*.class_id' => 'required|exists:classes,id',
            'rows.*.subject_id' => 'required|exists:subjects,id',
        ]);

        $teacher = Teacher::with(['cycle', 'user'])->findOrFail($request->teacher_id);
        $rows = $request->input('rows');
        $savedCount = 0;
        $rowErrors = [];

        foreach ($rows as $idx => $row) {
            $classId = (int) $row['class_id'];
            $subjectId = (int) $row['subject_id'];

            if (! $teacher->canBeAssignedToClass($classId, (int) $activeYear->id)) {
                $existingClassId = $teacher->assignments()
                    ->where('academic_year_id', $activeYear->id)
                    ->where('class_id', '!=', $classId)
                    ->value('class_id');
                $existingClass = SchoolClass::find($existingClassId);
                $existingClassName = $existingClass ? $existingClass->name : 'une autre classe';
                $rowErrors[] = 'Ligne '.($idx + 1).": {$teacher->full_name} ne peut avoir qu'une seule classe (déjà affecté à {$existingClassName}).";

                continue;
            }

            TeacherAssignment::updateOrCreate(
                [
                    'academic_year_id' => $activeYear->id,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                ],
                ['teacher_id' => $teacher->id]
            );
            $savedCount++;
        }

        if (! empty($rowErrors)) {
            return redirect()->back()->withInput()->withErrors(['rows' => implode(' | ', $rowErrors)]);
        }

        $msg = $savedCount === 1
            ? "L'affectation a été enregistrée avec succès."
            : "{$savedCount} affectations ont été enregistrées avec succès.";

        return redirect()->route('assignments.index')->with('status', $msg);
    }

    /**
     * Display the specified assignment.
     */
    public function show(TeacherAssignment $assignment): View
    {
        $assignment->load(['teacher.user', 'schoolClass.level.cycle', 'subject', 'academicYear']);

        return view('assignments.show', compact('assignment'));
    }

    /**
     * Show the form for editing the specified assignment.
     */
    public function edit(TeacherAssignment $assignment): View
    {
        $activeYear = AcademicYear::getActive();
        $teachers = Teacher::with('user')->where('status', 'ACTIF')->get();
        $classes = SchoolClass::with('level.cycle')->where('academic_year_id', $activeYear?->id ?? 0)->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        return view('assignments.edit', compact('assignment', 'teachers', 'classes', 'subjects'));
    }

    /**
     * Update the specified assignment in storage.
     */
    public function update(Request $request, TeacherAssignment $assignment): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $teacher = Teacher::with(['cycle', 'user'])->findOrFail($validated['teacher_id']);
        $academicYearId = (int) $assignment->academic_year_id;
        $newClassId = (int) $validated['class_id'];

        if ($teacher->isRestrictedToOneClass() && $newClassId !== (int) $assignment->class_id) {
            $remainingInOldClass = $teacher->assignments()
                ->where('academic_year_id', $academicYearId)
                ->where('class_id', $assignment->class_id)
                ->where('id', '!=', $assignment->id)
                ->exists();

            if ($remainingInOldClass) {
                $existingClass = SchoolClass::find($assignment->class_id);
                $existingClassName = $existingClass ? $existingClass->name : 'une autre classe';
                $cycleName = $teacher->cycle?->name ?? 'Maternelle / Primaire';

                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'teacher_id' => "L'enseignant {$teacher->full_name} est rattaché au cycle {$cycleName} et ne peut pas avoir plus d'une seule classe par année scolaire (déjà affecté à {$existingClassName}).",
                    ]);
            }
        }

        $assignment->update($validated);

        return redirect()->route('assignments.index')->with('status', "L'affectation a été mise à jour avec succès.");
    }

    /**
     * Remove the specified assignment from storage.
     */
    public function destroy(TeacherAssignment $assignment): RedirectResponse
    {
        $assignment->delete();

        return redirect()->route('assignments.index')->with('status', "L'affectation a été supprimée.");
    }
}
