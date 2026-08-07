<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Display attendance dashboard / sheet selector.
     */
    public function index(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $classes    = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->get();

        $selectedClassId = $request->query('class_id', $classes->first()?->id);
        $date            = $request->query('date', date('Y-m-d'));
        $session         = $request->query('session', 'MATIN');
        $subjectId       = $request->query('subject_id');

        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::where('status', 'ACTIF')->get();

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
                  ->where('attendance_date', $date)
                  ->where('session', $session)
                  ->when($subjectId, fn ($sq) => $sq->where('subject_id', $subjectId));
            }])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        }

        return view('attendances.index', compact(
            'classes',
            'selectedClass',
            'selectedClassId',
            'date',
            'session',
            'subjectId',
            'subjects',
            'teachers',
            'students',
            'activeYear'
        ));
    }

    /**
     * Store bulk attendance for a class session.
     */
    public function store(Request $request): RedirectResponse
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return redirect()->back()->withErrors(['error' => 'Aucune année scolaire active.']);
        }

        $validated = $request->validate([
            'class_id'        => 'required|exists:classes,id',
            'attendance_date' => 'required|date',
            'session'         => 'required|in:MATIN,APRES_MIDI,JOURNEE',
            'subject_id'      => 'nullable|exists:subjects,id',
            'teacher_id'      => 'nullable|exists:teachers,id',
            'attendances'     => 'required|array',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status'     => 'required|in:PRESENT,ABSENT,RETARD,EXCUSE',
            'attendances.*.reason'     => 'nullable|string|max:250',
        ]);

        DB::transaction(function () use ($validated, $activeYear) {
            foreach ($validated['attendances'] as $item) {
                Attendance::updateOrCreate(
                    [
                        'student_id'      => $item['student_id'],
                        'class_id'        => $validated['class_id'],
                        'subject_id'      => $validated['subject_id'] ?? null,
                        'attendance_date' => $validated['attendance_date'],
                        'session'         => $validated['session'],
                    ],
                    [
                        'teacher_id'       => $validated['teacher_id'] ?? null,
                        'academic_year_id' => $activeYear->id,
                        'status'           => $item['status'],
                        'reason'           => $item['reason'] ?? null,
                        'recorded_by'      => auth()->user()?->name ?? 'Système',
                    ]
                );
            }
        });

        return redirect()->route('attendances.index', [
            'class_id'   => $validated['class_id'],
            'date'       => $validated['attendance_date'],
            'session'    => $validated['session'],
            'subject_id' => $validated['subject_id'] ?? null,
        ])->with('status', 'Feuille de présence enregistrée avec succès.');
    }

    /**
     * Display attendance report & stats per class.
     */
    public function report(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $classes    = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->get();

        $selectedClassId = $request->query('class_id', $classes->first()?->id);
        $startDate       = $request->query('start_date', date('Y-m-01'));
        $endDate         = $request->query('end_date', date('Y-m-d'));

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
