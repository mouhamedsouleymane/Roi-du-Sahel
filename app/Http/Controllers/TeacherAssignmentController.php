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
            $configuredSubjectIds = DB::table('class_subject_coefficients')
                ->where('level_id', $cls->level_id)
                ->pluck('subject_id')
                ->all();
            $configuredCount = count($configuredSubjectIds);

            $assignedSubjectIds = $assignments->where('class_id', $cls->id)
                ->pluck('subject_id')
                ->unique()
                ->all();

            $assignedCount = $configuredCount > 0
                ? count(array_intersect($assignedSubjectIds, $configuredSubjectIds))
                : count($assignedSubjectIds);

            return [$cls->id => [
                'class' => $cls,
                'configured' => $configuredCount,
                'assigned' => $assignedCount,
                'missing' => max(0, $configuredCount - $assignedCount),
                'percent' => $configuredCount > 0 ? min(100, (int) round(($assignedCount / $configuredCount) * 100)) : 0,
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
    /**
     * Show the form for creating a new teacher assignment.
     * Assignments MUST be initiated from the '⚡ Gérer' button of a specific class.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return redirect()->route('assignments.index')
                ->withErrors(['error' => 'Aucune année scolaire active configurée.']);
        }

        // L'ajout doit obligatoirement se faire à partir du bouton "Gérer" d'une classe
        $classId = $request->query('class_id');
        if (! $classId) {
            return redirect()->route('assignments.index')
                ->with('warning', "L'affectation des cours doit obligatoirement se faire à partir du bouton '⚡ Gérer' de la classe concernée.");
        }

        $selectedClass = SchoolClass::with(['level.cycle'])->findOrFail($classId);

        // Seules les matières du niveau auquel la classe est rattachée doivent apparaître
        $levelCoefficients = DB::table('class_subject_coefficients')
            ->join('subjects', 'class_subject_coefficients.subject_id', '=', 'subjects.id')
            ->where('class_subject_coefficients.level_id', $selectedClass->level_id)
            ->where('subjects.is_active', true)
            ->select(
                'subjects.id',
                'subjects.code',
                'subjects.name',
                'subjects.category',
                'class_subject_coefficients.coefficient',
                'class_subject_coefficients.weekly_hours',
                'class_subject_coefficients.is_optional'
            )
            ->orderBy('subjects.name')
            ->get();

        $subjects = $levelCoefficients->map(function ($item) {
            return [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'category' => $item->category,
                'coefficient' => $item->coefficient,
                'weekly_hours' => $item->weekly_hours,
                'is_optional' => (bool) $item->is_optional,
            ];
        });

        // Affectations existantes pour cette classe et cette année scolaire
        $existingAssignments = TeacherAssignment::with(['teacher.user'])
            ->where('academic_year_id', $activeYear->id)
            ->where('class_id', $selectedClass->id)
            ->get()
            ->keyBy('subject_id');

        $existingAssignmentsMap = [];
        $existingTeacherIds = [];
        foreach ($subjects as $sbj) {
            $asn = $existingAssignments->get($sbj['id']);
            $existingAssignmentsMap[$sbj['id']] = $asn ? [
                'teacher_id' => $asn->teacher_id,
                'teacher_name' => $asn->teacher?->user?->name ?? 'Non assigné',
            ] : null;

            if ($asn?->teacher_id) {
                $existingTeacherIds[] = $asn->teacher_id;
            }
        }

        // Cycle de la classe
        $cycleName = strtolower(trim($selectedClass->level?->cycle?->name ?? ''));
        $isPolyvalentCycle = in_array($cycleName, Teacher::polyvalentCycleNames(), true);

        // Enseignants éligibles pour cette classe
        $teachersQuery = Teacher::with(['user', 'cycle', 'cycles', 'levels', 'assignments.schoolClass.level'])
            ->where('status', 'ACTIF');

        if ($isPolyvalentCycle) {
            // Pour Maternelle/Primaire : enseignants habilités sur ce cycle
            $teachersQuery->where(function ($q) use ($selectedClass) {
                $q->where('cycle_id', $selectedClass->level->cycle_id)
                    ->orWhereHas('cycles', function ($cq) use ($selectedClass) {
                        $cq->where('cycles.id', $selectedClass->level->cycle_id);
                    });
            });
        }

        $allTeachers = $teachersQuery->get();

        $eligibleTeachers = $allTeachers->filter(function (Teacher $t) use ($selectedClass, $activeYear, $isPolyvalentCycle) {
            if ($isPolyvalentCycle) {
                // Règle stricte Maternelle/Primaire : un instituteur ne peut avoir qu'une et une seule classe
                return $t->canBeAssignedToClass($selectedClass->id, (int) $activeYear->id);
            }

            return true;
        })->values()->map(function (Teacher $t) use ($selectedClass, $activeYear) {
            $yearAssignments = $t->assignments->where('academic_year_id', $activeYear->id);
            $assignedClassIds = $yearAssignments->pluck('class_id')->unique()->all();
            $isAssignedToThisClass = in_array($selectedClass->id, $assignedClassIds, true);

            return [
                'id' => $t->id,
                'name' => $t->user->name,
                'matricule' => $t->matricule,
                'speciality' => $t->speciality,
                'is_polyvalent' => (bool) $t->is_polyvalent,
                'cycle_name' => $t->cycle?->name ?? '—',
                'is_assigned_to_this_class' => $isAssignedToThisClass,
                'assigned_count' => $yearAssignments->count(),
            ];
        });

        // Détecter le titulaire pré-sélectionné
        $uniqueExistingTeacherIds = array_unique(array_filter($existingTeacherIds));
        $defaultTitulaireId = '';
        if ($request->query('teacher_id')) {
            $defaultTitulaireId = (int) $request->query('teacher_id');
        } elseif (count($uniqueExistingTeacherIds) === 1) {
            $defaultTitulaireId = reset($uniqueExistingTeacherIds);
        }

        return view('assignments.create', compact(
            'selectedClass',
            'subjects',
            'eligibleTeachers',
            'existingAssignmentsMap',
            'activeYear',
            'isPolyvalentCycle',
            'defaultTitulaireId'
        ));
    }

    /**
     * Store one or many teacher assignments at once (bulk).
     * Supports both By-Class (Gérer flow) and By-Teacher payloads.
     */
    public function store(Request $request): RedirectResponse
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return redirect()->back()->withErrors(['error' => 'Aucune année scolaire active.']);
        }

        // ══════════════════════════════════════════════════════════
        // CASE A : Saisie par Classe (Bouton "⚡ Gérer" de la classe)
        // ══════════════════════════════════════════════════════════
        if ($request->has('class_assignments') && $request->filled('class_id')) {
            $request->validate([
                'class_id' => 'required|exists:classes,id',
                'class_assignments' => 'required|array|min:1',
                'class_assignments.*.subject_id' => 'required|exists:subjects,id',
                'class_assignments.*.teacher_id' => 'nullable|exists:teachers,id',
            ]);

            $classId = (int) $request->class_id;
            $schoolClass = SchoolClass::with('level.cycle')->findOrFail($classId);

            // Vérifier que chaque matière appartient strictement au niveau de la classe
            $allowedSubjectIds = DB::table('class_subject_coefficients')
                ->where('level_id', $schoolClass->level_id)
                ->pluck('subject_id')
                ->all();

            $classAssignments = $request->input('class_assignments');
            $savedCount = 0;
            $rowErrors = [];

            foreach ($classAssignments as $idx => $item) {
                $subjectId = (int) $item['subject_id'];
                $teacherId = ! empty($item['teacher_id']) ? (int) $item['teacher_id'] : null;

                // Si des coefficients existent pour ce niveau, vérifier la conformité de la matière
                if (! empty($allowedSubjectIds) && ! in_array($subjectId, $allowedSubjectIds, true)) {
                    $subject = Subject::find($subjectId);
                    $subjectName = $subject ? $subject->name : "#{$subjectId}";
                    $rowErrors[] = "La matière {$subjectName} n'appartient pas au programme du niveau {$schoolClass->level->name}.";

                    continue;
                }

                if ($teacherId) {
                    $teacher = Teacher::with(['cycle', 'cycles', 'user'])->find($teacherId);
                    if ($teacher && ! $teacher->canBeAssignedToClass($classId, (int) $activeYear->id)) {
                        $existingClassId = $teacher->assignments()
                            ->where('academic_year_id', $activeYear->id)
                            ->where('class_id', '!=', $classId)
                            ->value('class_id');
                        $existingClass = SchoolClass::find($existingClassId);
                        $existingClassName = $existingClass ? $existingClass->name : 'une autre classe';
                        $rowErrors[] = "{$teacher->full_name} est rattaché au cycle Maternelle/Primaire et ne peut avoir qu'une seule classe (déjà titulaire de {$existingClassName}).";

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
                } else {
                    // Si l'enseignant est retiré pour cette matière
                    TeacherAssignment::where('academic_year_id', $activeYear->id)
                        ->where('class_id', $classId)
                        ->where('subject_id', $subjectId)
                        ->delete();
                }
            }

            if (! empty($rowErrors)) {
                return redirect()->back()->withInput()->withErrors(['rows' => implode(' | ', $rowErrors)]);
            }

            $msg = "Les affectations pour la classe {$schoolClass->name} ont été enregistrées avec succès ({$savedCount} matière(s) assignée(s)).";

            return redirect()->route('assignments.index')->with('status', $msg);
        }

        // ══════════════════════════════════════════════════════════
        // CASE B : Saisie par Enseignant (Legacy / Direct)
        // ══════════════════════════════════════════════════════════
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'rows' => 'required|array|min:1',
            'rows.*.class_id' => 'required|exists:classes,id',
            'rows.*.subject_id' => 'required|exists:subjects,id',
        ]);

        $teacher = Teacher::with(['cycle', 'cycles', 'user'])->findOrFail($request->teacher_id);
        $rows = $request->input('rows');
        $savedCount = 0;
        $rowErrors = [];

        // Si l'enseignant est polyvalent ou Maternelle/Primaire, vérifier qu'il n'y a pas plusieurs classes dans la soumission
        if ($teacher->isRestrictedToOneClass()) {
            $submittedClasses = collect($rows)->pluck('class_id')->unique();
            if ($submittedClasses->count() > 1) {
                return redirect()->back()->withInput()->withErrors([
                    'rows' => "L'enseignant {$teacher->full_name} est du cycle Maternelle/Primaire et ne peut pas être affecté à plusieurs classes différentes.",
                ]);
            }
        }

        foreach ($rows as $idx => $row) {
            $classId = (int) $row['class_id'];
            $subjectId = (int) $row['subject_id'];
            $schoolClass = SchoolClass::with('level')->find($classId);

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

            // Vérifier que la matière appartient au niveau de la classe
            if ($schoolClass) {
                $hasAnyCoeffs = DB::table('class_subject_coefficients')
                    ->where('level_id', $schoolClass->level_id)
                    ->exists();

                if ($hasAnyCoeffs) {
                    $isAllowed = DB::table('class_subject_coefficients')
                        ->where('level_id', $schoolClass->level_id)
                        ->where('subject_id', $subjectId)
                        ->exists();

                    if (! $isAllowed) {
                        $rowErrors[] = 'Ligne '.($idx + 1).": La matière sélectionnée n'appartient pas au niveau {$schoolClass->level->name}.";

                        continue;
                    }
                }
            }

            // Vérifier que la matière n'est pas déjà affectée à un autre enseignant pour cette classe
            $alreadyAssignedOther = TeacherAssignment::where('academic_year_id', $activeYear->id)
                ->where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->where('teacher_id', '!=', $teacher->id)
                ->with(['teacher.user', 'subject', 'schoolClass'])
                ->first();

            if ($alreadyAssignedOther) {
                $otherName = $alreadyAssignedOther->teacher?->user?->name ?? 'un autre enseignant';
                $subjName = $alreadyAssignedOther->subject?->name ?? 'cette matière';
                $clsName = $alreadyAssignedOther->schoolClass?->name ?? 'cette classe';
                $rowErrors[] = 'Ligne '.($idx + 1).": La matière {$subjName} est déjà enseignée par {$otherName} dans la classe {$clsName}. Deux enseignants ne peuvent pas enseigner la même matière pour la même classe.";

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

        $teacher = Teacher::with(['cycle', 'cycles', 'user'])->findOrFail($validated['teacher_id']);
        $academicYearId = (int) $assignment->academic_year_id;
        $newClassId = (int) $validated['class_id'];
        $subjectId = (int) $validated['subject_id'];

        if (! $teacher->canBeAssignedToClass($newClassId, $academicYearId, $assignment->id)) {
            $existingClass = SchoolClass::find($assignment->class_id);
            $existingClassName = $existingClass ? $existingClass->name : 'une autre classe';
            $cycleName = $teacher->cycle?->name ?? 'Maternelle / Primaire';

            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'teacher_id' => "L'enseignant {$teacher->full_name} est rattaché au cycle {$cycleName} et ne peut pas avoir plus d'une seule classe par année scolaire (déjà affecté à {$existingClassName}).",
                ]);
        }

        // Vérifier que la matière appartient au niveau de la classe
        $schoolClass = SchoolClass::with('level')->find($newClassId);
        if ($schoolClass) {
            $hasAnyCoeffs = DB::table('class_subject_coefficients')
                ->where('level_id', $schoolClass->level_id)
                ->exists();

            if ($hasAnyCoeffs) {
                $isAllowed = DB::table('class_subject_coefficients')
                    ->where('level_id', $schoolClass->level_id)
                    ->where('subject_id', $subjectId)
                    ->exists();

                if (! $isAllowed) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors([
                            'subject_id' => "Cette matière n'appartient pas au programme du niveau {$schoolClass->level->name}.",
                        ]);
                }
            }
        }

        // Règle d'or : deux enseignants ne peuvent pas enseigner la même matière pour la même classe
        $duplicate = TeacherAssignment::where('academic_year_id', $academicYearId)
            ->where('class_id', $newClassId)
            ->where('subject_id', $subjectId)
            ->where('id', '!=', $assignment->id)
            ->with(['teacher.user', 'subject', 'schoolClass'])
            ->first();

        if ($duplicate) {
            $otherName = $duplicate->teacher?->user?->name ?? 'un autre enseignant';
            $subjName = $duplicate->subject?->name ?? 'cette matière';
            $clsName = $duplicate->schoolClass?->name ?? 'cette classe';

            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'subject_id' => "Conflit d'attribution : La matière {$subjName} est déjà enseignée par {$otherName} dans la classe {$clsName}. Deux enseignants ne peuvent pas enseigner la même matière pour la même classe.",
                ]);
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
