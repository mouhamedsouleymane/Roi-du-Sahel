<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Checks if the connected user is a teacher.
     */
    private function isTeacher(): bool
    {
        return auth()->user()?->teacher !== null;
    }

    /**
     * Retrieves the classes and subjects assigned to the connected teacher for the active year.
     * Returns null if the user is not a teacher.
     *
     * @return array{classes: Collection, subjects: Collection}|null
     */
    private function teacherAssignments(): ?array
    {
        $user = auth()->user();
        $activeYear = AcademicYear::getActive();

        if (! $activeYear) {
            return null;
        }

        $teacher = $user?->teacher;
        if (! $teacher) {
            return null;
        }

        $classes = $teacher->assignedClasses($activeYear->id);
        $subjects = $teacher->assignedSubjects($activeYear->id);

        return compact('classes', 'subjects');
    }

    /**
     * Grille des classes de l'année active.
     */
    public function index(): View
    {
        $activeYear = AcademicYear::getActive();

        $classes = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->get()
            ->groupBy(fn ($c) => $c->level->cycle->name);

        return view('attendances.index', compact('classes', 'activeYear'));
    }

    /**
     * Formulaire de feuille d'appel (vierge ou pré-remplie si existante).
     */
    public function create(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $classes = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->get();

        $selectedClassId = $request->query('class_id');
        $date = $request->query('date', date('Y-m-d'));
        $session = $request->query('session', 'MATIN');
        $subjectId = $request->query('subject_id');

        $subjects = Subject::orderBy('name')->get();

        // Si un enseignant, restreindre aux classes/matières assignées
        if ($this->isTeacher()) {
            $teacherAssignments = $this->teacherAssignments();
            if ($teacherAssignments) {
                $classes = $classes->whereIn('id', $teacherAssignments['classes']->pluck('id'));
                $subjects = $subjects->whereIn('id', $teacherAssignments['subjects']->pluck('id'));

                // Par défaut on prend la première classe et la première matière de l'enseignant si rien n'est spécifié
                if (! $selectedClassId && $teacherAssignments['classes']->isNotEmpty()) {
                    $selectedClassId = $teacherAssignments['classes']->first();
                }
                if (! $subjectId && $teacherAssignments['subjects']->isNotEmpty()) {
                    $subjectId = $teacherAssignments['subjects']->first();
                }
            }
        }

        $selectedClass = $classes->firstWhere('id', $selectedClassId);

        // Fetch students enrolled in this class
        $students = collect();
        if ($selectedClassId) {
            $students = Student::whereHas('enrollments', function ($q) use ($selectedClassId, $activeYear) {
                $q->where('class_id', $selectedClassId)
                    ->where('academic_year_id', $activeYear?->id ?? 0)
                    ->where('status', 'VALIDE');
            })
                ->with(['attendances' => function ($q) use ($selectedClassId, $date, $session, $subjectId) {
                    $q->where('class_id', $selectedClassId)
                        ->whereDate('attendance_date', $date)
                        ->where('session', $session)
                        ->when($subjectId, fn ($sq) => $sq->where('subject_id', $subjectId));
                }])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }

        return view('attendances.create', compact(
            'classes',
            'selectedClass',
            'selectedClassId',
            'date',
            'session',
            'subjectId',
            'subjects',
            'students',
            'activeYear'
        ));
    }

    /**
     * Recherche la période clôturée couvrant une date (année active).
     */
    private function closedPeriodCovering(AcademicYear $activeYear, string $date): ?Period
    {
        return Period::where('academic_year_id', $activeYear->id)
            ->where('is_closed', true)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
    }

    /**
     * Règles de validation communes pour désigner une feuille
     * (classe / date / séance / matière).
     *
     * @return array<string, mixed>
     */
    private function sheetSelectionRules(?AcademicYear $activeYear): array
    {
        return [
            'class_id' => [
                'required',
                Rule::exists('classes', 'id')->where('academic_year_id', $activeYear?->id ?? 0),
            ],
            'date' => 'required|date',
            'subject_id' => 'nullable|exists:subjects,id',
        ];
    }

    /**
     * Charge les données d'une feuille : classe, matière, enregistrements
     * triés, statistiques par statut et période clôturée éventuelle.
     *
     * @param  array{class_id: int, date: string, session: string, subject_id?: int|null}  $validated
     * @return array{schoolClass: SchoolClass, subject: ?Subject, date: string, session: string, records: Collection, stats: array<string, int>, closedPeriod: ?Period}
     */
    private function getSheetData(array $validated, ?AcademicYear $activeYear): array
    {
        $schoolClass = SchoolClass::with('level.cycle')->findOrFail($validated['class_id']);
        $subject = isset($validated['subject_id']) && $validated['subject_id']
            ? Subject::find($validated['subject_id'])
            : null;

        $records = Attendance::with(['student', 'teacher'])
            ->where('class_id', $validated['class_id'])
            ->whereDate('attendance_date', $validated['date'])
            ->when(
                isset($validated['subject_id']) && $validated['subject_id'],
                fn ($q) => $q->where('subject_id', $validated['subject_id']),
                fn ($q) => $q->whereNull('subject_id')
            )
            ->get()
            ->sortBy(fn ($a) => $a->student->last_name.' '.$a->student->first_name)
            ->values();

        $closedPeriod = null;
        if ($activeYear) {
            $closedPeriod = $this->closedPeriodCovering(
                $activeYear,
                Carbon::parse($validated['date'])->toDateString()
            );
        }

        return [
            'schoolClass' => $schoolClass,
            'subject' => $subject,
            'date' => $validated['date'],
            'records' => $records,
            'stats' => [
                'PRESENT' => $records->where('status', 'PRESENT')->count(),
                'ABSENT' => $records->where('status', 'ABSENT')->count(),
                'RETARD' => $records->where('status', 'RETARD')->count(),
                'EXCUSE' => $records->where('status', 'EXCUSE')->count(),
            ],
            'closedPeriod' => $closedPeriod,
        ];
    }

    /**
     * Persiste une feuille d'appel (création ou mise à jour).
     *
     * @return array{count: int}
     */
    private function persistSheet(array $validated, AcademicYear $activeYear): array
    {
        $count = 0;

        DB::transaction(function () use ($validated, $activeYear, &$count) {
            foreach ($validated['attendances'] as $item) {
                Attendance::updateOrCreate(
                    [
                        'student_id' => $item['student_id'],
                        'class_id' => $validated['class_id'],
                        'subject_id' => $validated['subject_id'] ?? null,
                        'attendance_date' => $validated['attendance_date'],
                    ],
                    [
                        'teacher_id' => $validated['teacher_id'] ?? null,
                        'academic_year_id' => $activeYear->id,
                        'status' => $item['status'],
                        'reason' => $item['reason'] ?? null,
                        'recorded_by' => auth()->user()?->name,
                    ]
                );

                $count++;
            }
        });

        return ['count' => $count];
    }

    /**
     * Store bulk attendance for a class session.
     *
     * Gardes métier (alignées sur les autres modules) :
     * - la classe doit appartenir à l'année scolaire active ;
     * - chaque élève doit y être inscrit avec le statut VALIDE ;
     * - la date doit appartenir à l'année scolaire active et ne pas être future ;
     * - aucune écriture dans une période clôturée couvrant la date.
     */
    public function store(Request $request): RedirectResponse
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return redirect()->back()->withErrors(['error' => 'Aucune année scolaire active.']);
        }

        $maxDate = min(today()->toDateString(), $activeYear->end_date->toDateString());
        $isFuture = $request->attendance_date > today()->toDateString();

        $validated = $request->validate([
            'class_id' => [
                'required',
                Rule::exists('classes', 'id')->where('academic_year_id', $activeYear->id),
            ],
            'attendance_date' => [
                'required',
                'date',
                'after_or_equal:'.$activeYear->start_date->toDateString(),
                'before_or_equal:'.$maxDate,
            ],
            'subject_id' => 'nullable|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'attendances' => 'required|array',
            'attendances.*.student_id' => [
                'required',
                Rule::exists('enrollments', 'student_id')
                    ->where('class_id', $request->input('class_id'))
                    ->where('academic_year_id', $activeYear->id)
                    ->where('status', 'VALIDE'),
            ],
            'attendances.*.status' => 'required|in:PRESENT,ABSENT,RETARD,EXCUSE',
            'attendances.*.reason' => 'nullable|string|max:250',
        ], [
            'attendances.*.student_id.exists' => "Un élève n'est pas inscrit (statut VALIDE) dans cette classe.",
            'attendance_date.before_or_equal' => $isFuture
                ? 'La date de présence ne peut pas être dans le futur.'
                : "La date de présence ne peut pas dépasser le {$activeYear->end_date->format('d/m/Y')} (fin de l'année scolaire).",
            'attendance_date.after_or_equal' => "La date doit être après le {$activeYear->start_date->format('d/m/Y')} (début de l'année scolaire).",
        ]);

        $date = Carbon::parse($validated['attendance_date'])->toDateString();
        $closedPeriod = $this->closedPeriodCovering($activeYear, $date);

        if ($closedPeriod) {
            return redirect()->route('attendances.create', [
                'class_id' => $validated['class_id'],
                'date' => $validated['attendance_date'],
                'subject_id' => $validated['subject_id'] ?? null,
            ])->withErrors(['error' => "Impossible d'enregistrer la présence : la période « {$closedPeriod->name} » est clôturée."]);
        }

        $result = $this->persistSheet($validated, $activeYear);

        return redirect()->route('attendances.show', [
            'class_id' => $validated['class_id'],
        ])->with('status', "Feuille d'appel enregistrée avec succès ({$result['count']} élève(s)).");
    }

    /**
     * Formulaire de correction d'une feuille existante.
     */
    public function edit(Request $request): View
    {
        $activeYear = AcademicYear::getActive();

        $validated = $request->validate($this->sheetSelectionRules($activeYear));

        if ($activeYear) {
            $closedPeriod = $this->closedPeriodCovering(
                $activeYear,
                Carbon::parse($validated['date'])->toDateString()
            );

            if ($closedPeriod) {
                abort(403, 'Periode cloturee : modification interdite.');
            }
        }

        $request->merge([
            'class_id' => $validated['class_id'],
            'date' => $validated['date'],
            'subject_id' => $validated['subject_id'] ?? null,
        ]);

        return $this->create($request);
    }

    /**
     * Historique des feuilles d'une classe, groupé par semaine.
     */
    public function show(Request $request): View
    {
        $activeYear = AcademicYear::getActive();

        $request->validate([
            'class_id' => ['required', Rule::exists('classes', 'id')->where('academic_year_id', $activeYear?->id ?? 0)],
        ]);

        $schoolClass = SchoolClass::with('level.cycle')->findOrFail($request->class_id);

        // Dates distinctes de la classe, groupées par semaine
        $sheets = Attendance::where('class_id', $schoolClass->id)
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->orderByDesc('attendance_date')
            ->pluck('attendance_date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->values()
            ->groupBy(fn ($d) => Carbon::parse($d)->startOfWeek()->toDateString());

        return view('attendances.show', compact('schoolClass', 'sheets', 'activeYear'));
    }

    /**
     * Tableau croisé d'une semaine : noms en lignes, jours en colonnes.
     */
    public function sheet(Request $request): View
    {
        $activeYear = AcademicYear::getActive();

        $request->validate([
            'class_id' => ['required', Rule::exists('classes', 'id')->where('academic_year_id', $activeYear?->id ?? 0)],
            'week_start' => 'required|date',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $schoolClass = SchoolClass::with('level.cycle')->findOrFail($request->class_id);
        $subject = $request->subject_id ? Subject::find($request->subject_id) : null;

        $weekStart = Carbon::parse($request->week_start)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        // Jours de la semaine qui ont au moins une feuille
        $days = Attendance::where('class_id', $schoolClass->id)
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->whereDate('attendance_date', '>=', $weekStart->toDateString())
            ->whereDate('attendance_date', '<=', $weekEnd->toDateString())
            ->when($subject, fn ($q) => $q->where('subject_id', $subject->id), fn ($q) => $q->whereNull('subject_id'))
            ->pluck('attendance_date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->unique()->sort()->values();

        // Élèves inscrits VALIDE
        $students = Student::whereHas('enrollments', fn ($q) => $q
            ->where('class_id', $schoolClass->id)
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->where('status', 'VALIDE')
        )->orderBy('last_name')->orderBy('first_name')->get();

        // Tous les enregistrements de la semaine, indexés [student_id][date]
        $records = Attendance::where('class_id', $schoolClass->id)
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->whereDate('attendance_date', '>=', $weekStart->toDateString())
            ->whereDate('attendance_date', '<=', $weekEnd->toDateString())
            ->when($subject, fn ($q) => $q->where('subject_id', $subject->id), fn ($q) => $q->whereNull('subject_id'))
            ->get()
            ->groupBy('student_id')
            ->map(fn ($rows) => $rows->keyBy(fn ($r) => Carbon::parse($r->attendance_date)->toDateString()));

        return view('attendances.sheet', compact(
            'schoolClass', 'subject', 'students',
            'records', 'days', 'weekStart', 'weekEnd', 'activeYear'
        ));
    }

    /**
     * Met a jour une feuille d'appel existante.
     */
    public function update(Request $request): RedirectResponse
    {
        $response = $this->store($request);

        if ($response->getSession()->has('status')) {
            $status = (string) $response->getSession()->get('status');
            $response->getSession()->flash('status', str_replace('enregistree', 'mise a jour', $status));
        }

        return $response;
    }

    /**
     * Supprime une feuille d'appel complète (classe / date / séance / matière).
     *
     * Refusée si la date tombe dans une période clôturée.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $activeYear = AcademicYear::getActive();

        $validated = $request->validate([
            'class_id' => [
                'required',
                Rule::exists('classes', 'id')->where('academic_year_id', $activeYear?->id ?? 0),
            ],
            'attendance_date' => 'required|date',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $query = Attendance::where('class_id', $validated['class_id'])
            ->whereDate('attendance_date', $validated['attendance_date'])
            ->when(
                array_key_exists('subject_id', $validated) && $validated['subject_id'],
                fn ($q) => $q->where('subject_id', $validated['subject_id']),
                fn ($q) => $q->whereNull('subject_id')
            );

        $count = (clone $query)->count();
        if ($count === 0) {
            return redirect()->route('attendances.show', ['class_id' => $validated['class_id']])
                ->with('error', "Aucune feuille d'appel à supprimer pour cette sélection.");
        }

        if ($activeYear) {
            $date = Carbon::parse($validated['attendance_date'])->toDateString();
            $closedPeriod = Period::where('academic_year_id', $activeYear->id)
                ->where('is_closed', true)
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->first();

            if ($closedPeriod) {
                return redirect()->route('attendances.show', ['class_id' => $validated['class_id']])
                    ->with('error', "Impossible de supprimer cette feuille d'appel : la période « {$closedPeriod->name} » est clôturée.");
            }
        }

        $query->delete();

        return redirect()->route('attendances.show', ['class_id' => $validated['class_id']])
            ->with('status', "Feuille d'appel supprimée ({$count} enregistrement(s)).");
    }

    /**
     * Display attendance report & stats per class.
     */
    public function report(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $classes = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->get();

        $request->mergeIfMissing([
            'class_id' => $classes->first()?->id,
            'start_date' => date('Y-m-01'),
            'end_date' => date('Y-m-d'),
        ]);

        $request->validate([
            'class_id' => 'nullable|exists:classes,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ], [
            'end_date.after_or_equal' => 'La date de fin du bilan doit être postérieure ou égale à la date de début.',
        ]);

        $selectedClassId = $request->input('class_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $selectedClass = $classes->firstWhere('id', $selectedClassId);

        $reportData = collect();
        if ($selectedClassId) {
            $reportData = Student::whereHas('enrollments', function ($q) use ($selectedClassId, $activeYear) {
                $q->where('class_id', $selectedClassId)
                    ->where('academic_year_id', $activeYear?->id ?? 0)
                    ->where('status', 'VALIDE');
            })
                ->withCount([
                    'attendances as total_presents' => function ($q) use ($selectedClassId, $startDate, $endDate) {
                        $q->where('class_id', $selectedClassId)
                            ->whereBetween('attendance_date', [$startDate, $endDate])
                            ->where('status', 'PRESENT');
                    },
                    'attendances as total_absents' => function ($q) use ($selectedClassId, $startDate, $endDate) {
                        $q->where('class_id', $selectedClassId)
                            ->whereBetween('attendance_date', [$startDate, $endDate])
                            ->where('status', 'ABSENT');
                    },
                    'attendances as total_retards' => function ($q) use ($selectedClassId, $startDate, $endDate) {
                        $q->where('class_id', $selectedClassId)
                            ->whereBetween('attendance_date', [$startDate, $endDate])
                            ->where('status', 'RETARD');
                    },
                    'attendances as total_excuses' => function ($q) use ($selectedClassId, $startDate, $endDate) {
                        $q->where('class_id', $selectedClassId)
                            ->whereBetween('attendance_date', [$startDate, $endDate])
                            ->where('status', 'EXCUSE');
                    },
                ])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }

        return view('attendances.report', compact(
            'classes',
            'selectedClass',
            'selectedClassId',
            'startDate',
            'endDate',
            'reportData',
            'activeYear'
        ));
    }
}
