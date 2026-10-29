<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Display listing of students or class directory cards.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $classId = $request->query('class_id');
        $gender = $request->query('gender');
        $cycleId = $request->query('cycle_id');
        $activeYear = AcademicYear::getActive();

        // Cycles avec Niveaux et Classes (avec décompte d'élèves inscrits)
        $cycles = Cycle::with(['levels.classes' => function ($q) use ($activeYear) {
            $q->where('academic_year_id', $activeYear?->id ?? 0)
                ->withCount([
                    'enrollments as active_enrollments_count' => function ($eq) {
                        $eq->where('status', 'VALIDE');
                    },
                    'enrollments as boys_count' => function ($eq) {
                        $eq->where('status', 'VALIDE')
                            ->whereHas('student', fn ($sq) => $sq->where('gender', 'M'));
                    },
                    'enrollments as girls_count' => function ($eq) {
                        $eq->where('status', 'VALIDE')
                            ->whereHas('student', fn ($sq) => $sq->where('gender', 'F'));
                    },
                ])
                ->with('mainTeacher');
        }])
            ->where('is_active', true)
            ->when($cycleId, function ($q) use ($cycleId) {
                $q->where('id', $cycleId);
            })
            ->get();

        $classes = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->get();

        $selectedClass = $classId ? SchoolClass::with(['level.cycle', 'mainTeacher'])->find($classId) : null;

        // Statistiques globales
        $totalStudents = Student::count();
        $maleCount = Student::where('gender', 'M')->count();
        $femaleCount = Student::where('gender', 'F')->count();
        $enrolledThisYear = Student::whereHas('enrollments', function ($q) use ($activeYear) {
            $q->where('academic_year_id', $activeYear?->id ?? 0)
                ->where('status', 'VALIDE');
        })->count();

        // Récupération des élèves (si classe sélectionnée ou recherche active)
        $students = null;
        if ($classId || $search) {
            $students = Student::with(['guardians', 'enrollments.schoolClass.level.cycle', 'currentEnrollment.schoolClass.level.cycle'])
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sq) use ($search) {
                        $sq->where('matricule', 'LIKE', "%{$search}%")
                            ->orWhere('first_name', 'LIKE', "%{$search}%")
                            ->orWhere('last_name', 'LIKE', "%{$search}%");
                    });
                })
                ->when($classId, function ($q) use ($classId) {
                    $q->whereHas('enrollments', function ($eq) use ($classId) {
                        $eq->where('class_id', $classId)->where('status', 'VALIDE');
                    });
                })
                ->when($gender, function ($q) use ($gender) {
                    $q->where('gender', $gender);
                })
                ->orderBy('last_name')
                ->paginate(20)
                ->withQueryString();
        }

        return view('students.index', compact(
            'cycles',
            'classes',
            'selectedClass',
            'students',
            'search',
            'classId',
            'gender',
            'cycleId',
            'totalStudents',
            'maleCount',
            'femaleCount',
            'enrolledThisYear',
            'activeYear'
        ));
    }

    /**
     * Redirect to enrollment creation form.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('enrollments.create');
    }

    /**
     * Display student record / profile.
     */
    public function show(Student $student): View
    {
        $student->load([
            'guardians',
            'currentEnrollment.schoolClass.level.cycle',
            'enrollments.schoolClass.level.cycle',
            'enrollments.academicYear',
            'attendances' => function ($q) {
                $q->latest('attendance_date')->limit(10);
            },
            'grades' => function ($q) {
                $q->with([
                    'evaluation.subject',
                    'evaluation.period',
                    'evaluation.teacher',
                    'evaluation.schoolClass',
                ]);
            },
            'reportCards' => function ($q) {
                $q->with(['period', 'schoolClass'])->latest();
            },
        ]);

        $grades = $student->grades
            ->sortByDesc(fn ($g) => $g->evaluation?->evaluation_date?->timestamp ?? $g->created_at?->timestamp ?? 0)
            ->values();

        $reportCards = $student->reportCards;

        $isOutOfTen = (bool) ($student->currentEnrollment?->schoolClass?->isPrimaire() || $student->currentEnrollment?->schoolClass?->isMaternelle());
        $scale = $isOutOfTen ? 10 : 20;

        $presentGrades = $grades->where('is_absent', false)->whereNotNull('score');
        $totalEvaluations = $grades->count();
        $absencesCount = $grades->where('is_absent', true)->count();

        $highestGrade = $presentGrades->max('score');
        $lowestGrade = $presentGrades->min('score');

        $totalWeightedPoints = $presentGrades->sum(function ($g) {
            $max = (float) ($g->evaluation?->max_score ?? 20);
            $coef = (float) ($g->evaluation?->coefficient ?? 1);
            $score = (float) $g->score;

            return ($max > 0 ? ($score / $max) * 20 : 0) * $coef;
        });

        $totalCoef = $presentGrades->sum(fn ($g) => (float) ($g->evaluation?->coefficient ?? 1));
        $averageOnTwenty = $totalCoef > 0 ? round($totalWeightedPoints / $totalCoef, 2) : null;
        $generalAverage = $averageOnTwenty !== null
            ? ($isOutOfTen ? round($averageOnTwenty / 2, 2) : $averageOnTwenty)
            : null;

        $periods = $grades->map(fn ($g) => $g->evaluation?->period)->filter()->unique('id')->values();
        $subjects = $grades->map(fn ($g) => $g->evaluation?->subject)->filter()->unique('id')->values();

        return view('students.show', compact(
            'student',
            'grades',
            'reportCards',
            'isOutOfTen',
            'scale',
            'totalEvaluations',
            'absencesCount',
            'highestGrade',
            'lowestGrade',
            'generalAverage',
            'periods',
            'subjects'
        ));
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student): View
    {
        return view('students.edit', compact('student'));
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:125',
            'last_name' => 'required|string|max:125',
            'gender' => 'required|in:M,F',
            'birth_date' => 'required|date',
            'birth_place' => 'required|string|max:125',
            'blood_group' => 'nullable|string|max:10',
            'previous_school' => 'nullable|string|max:191',
            'medical_notes' => 'nullable|string',
            'status' => 'required|in:ACTIF,INACTIF,TRANSFERE,RADIE',
        ]);

        $student->update($validated);

        return redirect()->route('students.show', $student)
            ->with('status', 'Le dossier de l\'élève a été mis à jour avec succès.');
    }
}
