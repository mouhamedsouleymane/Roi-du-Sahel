<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Display weekly timetable for a class or teacher with intelligent conflict management.
     */
    public function index(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $activeYearId = $activeYear?->id ?? 0;

        $viewType = $request->query('view_type', 'class'); // 'class' or 'teacher'

        $classes = SchoolClass::with(['level.cycle', 'schedules' => function ($q) use ($activeYearId) {
            $q->where('academic_year_id', $activeYearId)
                ->with(['subject', 'teacher.user'])
                ->orderBy('start_time');
        }])
            ->where('academic_year_id', $activeYearId)
            ->get();

        $teachers = Teacher::with(['user', 'cycle', 'schedules' => function ($q) use ($activeYearId) {
            $q->where('academic_year_id', $activeYearId)
                ->with(['subject', 'schoolClass.level.cycle'])
                ->orderBy('start_time');
        }])
            ->where('status', 'ACTIF')
            ->get();

        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        // Si aucun identifiant n'est spécifié, on reste à null pour afficher tous les emplois du temps par défaut
        $selectedClassId = $request->filled('class_id') ? (int) $request->query('class_id') : null;
        $selectedTeacherId = $request->filled('teacher_id') ? (int) $request->query('teacher_id') : null;

        $selectedClass = $selectedClassId ? $classes->firstWhere('id', $selectedClassId) : null;
        $selectedTeacher = $selectedTeacherId ? $teachers->firstWhere('id', $selectedTeacherId) : null;

        // Affectations officielles pour la classe sélectionnée (liaison intelligente)
        $classAssignments = $selectedClassId
            ? TeacherAssignment::with(['subject', 'teacher.user'])
                ->where('academic_year_id', $activeYearId)
                ->where('class_id', $selectedClassId)
                ->get()
            : collect();

        // Récupération des créneaux selon le mode de vue
        if ($viewType === 'teacher') {
            if ($selectedTeacherId) {
                $schedules = Schedule::with(['subject', 'schoolClass.level.cycle', 'teacher.user'])
                    ->where('academic_year_id', $activeYearId)
                    ->where('teacher_id', $selectedTeacherId)
                    ->orderBy('start_time')
                    ->get()
                    ->groupBy('day_of_week');
            } else {
                $schedules = Schedule::with(['subject', 'schoolClass.level.cycle', 'teacher.user'])
                    ->where('academic_year_id', $activeYearId)
                    ->orderBy('start_time')
                    ->get()
                    ->groupBy('teacher_id');
            }
        } else {
            if ($selectedClassId) {
                $schedules = Schedule::with(['subject', 'teacher.user', 'schoolClass.level.cycle'])
                    ->where('academic_year_id', $activeYearId)
                    ->where('class_id', $selectedClassId)
                    ->orderBy('start_time')
                    ->get()
                    ->groupBy('day_of_week');
            } else {
                $schedules = Schedule::with(['subject', 'teacher.user', 'schoolClass.level.cycle'])
                    ->where('academic_year_id', $activeYearId)
                    ->orderBy('start_time')
                    ->get()
                    ->groupBy('class_id');
            }
        }

        // Statistiques globales de l'emploi du temps
        $totalSlotsCount = Schedule::where('academic_year_id', $activeYearId)->count();
        $classSlotsCount = $selectedClass ? $selectedClass->schedules->count() : $totalSlotsCount;

        return view('schedules.index', compact(
            'schedules',
            'classes',
            'teachers',
            'subjects',
            'classAssignments',
            'selectedClassId',
            'selectedTeacherId',
            'selectedClass',
            'selectedTeacher',
            'viewType',
            'activeYear',
            'totalSlotsCount',
            'classSlotsCount'
        ));
    }

    /**
     * Show the form for creating a new timetable slot.
     */
    public function create(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $activeYearId = $activeYear?->id ?? 0;

        $cycles = Cycle::with('levels')->where('is_active', true)->get();

        $classes = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYearId)
            ->get();

        $selectedClassId = $request->query('class_id', $classes->first()?->id);
        $selectedClass = $classes->firstWhere('id', $selectedClassId);

        $teachers = Teacher::with(['user', 'cycle'])->where('status', 'ACTIF')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        $classAssignments = TeacherAssignment::with(['subject', 'teacher.user'])
            ->where('academic_year_id', $activeYearId)
            ->where('class_id', $selectedClassId)
            ->get();

        return view('schedules.create', compact(
            'classes',
            'cycles',
            'teachers',
            'subjects',
            'classAssignments',
            'selectedClassId',
            'selectedClass',
            'activeYear'
        ));
    }

    /**
     * Store a new timetable slot with collision check.
     */
    public function store(Request $request): RedirectResponse
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return redirect()->back()->withErrors(['error' => 'Aucune année scolaire active.']);
        }

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day_of_week' => 'required|in:LUNDI,MARDI,MERCREDI,JEUDI,VENDREDI,SAMEDI',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room_number' => 'nullable|string|max:50',
        ]);

        // Vérification intelligente des conflits
        $conflict = $this->checkScheduleConflict(
            $activeYear->id,
            (int) $validated['class_id'],
            (int) $validated['teacher_id'],
            $validated['day_of_week'],
            $validated['start_time'],
            $validated['end_time'],
            $validated['room_number'] ?? null
        );

        if ($conflict) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['conflict' => $conflict]);
        }

        Schedule::create([
            'academic_year_id' => $activeYear->id,
            'class_id' => $validated['class_id'],
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $validated['teacher_id'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'room_number' => $validated['room_number'] ?? null,
        ]);

        return redirect()->route('schedules.index', [
            'class_id' => $validated['class_id'],
            'view_type' => 'class',
        ])->with('status', 'Le créneau de cours a été programmé avec succès.');
    }

    /**
     * Display the specified schedule slot.
     */
    public function show(Schedule $schedule): View
    {
        $schedule->load(['subject', 'teacher.user', 'schoolClass.level.cycle', 'academicYear']);

        // Calcul du volume horaire hebdomadaire de cette matière pour cette classe
        $subjectWeeklySlots = Schedule::where('academic_year_id', $schedule->academic_year_id)
            ->where('class_id', $schedule->class_id)
            ->where('subject_id', $schedule->subject_id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Créneaux de cet enseignant dans la semaine
        $teacherWeeklySlots = Schedule::with(['schoolClass'])
            ->where('academic_year_id', $schedule->academic_year_id)
            ->where('teacher_id', $schedule->teacher_id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return view('schedules.show', compact('schedule', 'subjectWeeklySlots', 'teacherWeeklySlots'));
    }

    /**
     * Show the form for editing the specified schedule slot.
     */
    public function edit(Schedule $schedule): View
    {
        $activeYear = AcademicYear::getActive();
        $activeYearId = $activeYear?->id ?? 0;

        $classes = SchoolClass::with('level.cycle')->where('academic_year_id', $activeYearId)->get();
        $teachers = Teacher::with('user')->where('status', 'ACTIF')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        $classAssignments = TeacherAssignment::with(['subject', 'teacher.user'])
            ->where('academic_year_id', $activeYearId)
            ->where('class_id', $schedule->class_id)
            ->get();

        return view('schedules.edit', compact('schedule', 'classes', 'teachers', 'subjects', 'classAssignments'));
    }

    /**
     * Update the specified schedule slot in storage with collision check.
     */
    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day_of_week' => 'required|in:LUNDI,MARDI,MERCREDI,JEUDI,VENDREDI,SAMEDI',
            'start_time' => 'required',
            'end_time' => 'required',
            'room_number' => 'nullable|string|max:50',
        ]);

        // Vérification des conflits en excluant le créneau actuel
        $conflict = $this->checkScheduleConflict(
            $schedule->academic_year_id,
            (int) $validated['class_id'],
            (int) $validated['teacher_id'],
            $validated['day_of_week'],
            $validated['start_time'],
            $validated['end_time'],
            $validated['room_number'] ?? null,
            $schedule->id
        );

        if ($conflict) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['conflict' => $conflict]);
        }

        $schedule->update($validated);

        return redirect()->route('schedules.index', [
            'class_id' => $validated['class_id'],
            'view_type' => 'class',
        ])->with('status', 'Le créneau horaire a été mis à jour avec succès.');
    }

    /**
     * Duplicate a schedule slot to another day or slot dynamically.
     */
    public function duplicate(Request $request, Schedule $schedule): RedirectResponse
    {
        $targetDay = $request->input('target_day', $schedule->day_of_week);
        $startTime = $request->input('start_time', substr($schedule->start_time, 0, 5));
        $endTime = $request->input('end_time', substr($schedule->end_time, 0, 5));

        $conflict = $this->checkScheduleConflict(
            $schedule->academic_year_id,
            $schedule->class_id,
            $schedule->teacher_id,
            $targetDay,
            $startTime,
            $endTime,
            $schedule->room_number
        );

        if ($conflict) {
            return redirect()->back()->withErrors(['conflict' => "Impossible de dupliquer : {$conflict}"]);
        }

        Schedule::create([
            'academic_year_id' => $schedule->academic_year_id,
            'class_id' => $schedule->class_id,
            'subject_id' => $schedule->subject_id,
            'teacher_id' => $schedule->teacher_id,
            'day_of_week' => $targetDay,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'room_number' => $schedule->room_number,
        ]);

        return redirect()->route('schedules.index', [
            'class_id' => $schedule->class_id,
            'view_type' => 'class',
        ])->with('status', "Le créneau de {$schedule->subject?->name} a été dupliqué avec succès pour le {$targetDay}.");
    }

    /**
     * Clear all schedules for a selected class.
     */
    public function clearClass(Request $request): RedirectResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $activeYear = AcademicYear::getActive();
        $classId = (int) $request->input('class_id');

        $deletedCount = Schedule::where('academic_year_id', $activeYear?->id ?? 0)
            ->where('class_id', $classId)
            ->delete();

        return redirect()->route('schedules.index', [
            'class_id' => $classId,
            'view_type' => 'class',
        ])->with('status', "L'emploi du temps a été réinitialisé ({$deletedCount} créneau(x) supprimé(s)).");
    }

    /**
     * Export the weekly timetable of a class as a PDF file.
     */
    public function exportPdf(SchoolClass $schoolClass): Response
    {
        $activeYear = AcademicYear::getActive();
        $activeYearId = $activeYear?->id ?? 0;

        $schoolName = SchoolSetting::get('school_name', 'Établissement Scolaire');
        $schoolAddress = SchoolSetting::get('school_address', '');

        $schoolClass->load(['level.cycle']);

        $schedules = Schedule::with(['subject', 'teacher.user'])
            ->where('academic_year_id', $activeYearId)
            ->where('class_id', $schoolClass->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        $pdf = Pdf::loadView('schedules.pdf', compact(
            'schoolClass',
            'schedules',
            'activeYear',
            'schoolName',
            'schoolAddress'
        ))->setPaper('a4', 'landscape');

        $filename = 'emploi-du-temps-'.str($schoolClass->name)->slug().'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Remove the specified schedule slot from storage.
     */
    public function destroy(Schedule $schedule): RedirectResponse
    {
        $classId = $schedule->class_id;
        $schedule->delete();

        return redirect()->route('schedules.index', ['class_id' => $classId])
            ->with('status', 'Le créneau horaire a été supprimé.');
    }

    /**
     * Intelligent collision detector across Class, Teacher and Room.
     */
    private function checkScheduleConflict(
        int $academicYearId,
        int $classId,
        int $teacherId,
        string $dayOfWeek,
        string $startTime,
        string $endTime,
        ?string $roomNumber = null,
        ?int $excludeId = null
    ): ?string {
        // 1. Conflit Classe (la classe a-t-elle déjà cours sur ce créneau ?)
        $classConflict = Schedule::with(['subject', 'teacher.user'])
            ->where('academic_year_id', $academicYearId)
            ->where('class_id', $classId)
            ->where('day_of_week', $dayOfWeek)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->first();

        if ($classConflict) {
            $start = substr($classConflict->start_time, 0, 5);
            $end = substr($classConflict->end_time, 0, 5);

            return "Collision de classe : cette classe a déjà un cours de {$classConflict->subject?->name} avec {$classConflict->teacher?->user?->name} programmé de {$start} à {$end} le {$dayOfWeek}.";
        }

        // 2. Conflit Enseignant (l'enseignant est-il déjà pris dans une autre classe ?)
        $teacherConflict = Schedule::with(['schoolClass', 'subject', 'teacher.user'])
            ->where('academic_year_id', $academicYearId)
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->first();

        if ($teacherConflict) {
            $start = substr($teacherConflict->start_time, 0, 5);
            $end = substr($teacherConflict->end_time, 0, 5);
            $teacherName = $teacherConflict->teacher?->user?->name ?? 'L\'enseignant';

            return "Collision enseignant : {$teacherName} donne déjà un cours de {$teacherConflict->subject?->name} à la classe {$teacherConflict->schoolClass?->name} de {$start} à {$end} le {$dayOfWeek}.";
        }

        // 3. Conflit de Salle physique (si renseignée)
        if (! empty(trim((string) $roomNumber))) {
            $roomConflict = Schedule::with(['schoolClass', 'subject', 'teacher.user'])
                ->where('academic_year_id', $academicYearId)
                ->where('room_number', trim((string) $roomNumber))
                ->where('day_of_week', $dayOfWeek)
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                })
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->first();

            if ($roomConflict) {
                $start = substr($roomConflict->start_time, 0, 5);
                $end = substr($roomConflict->end_time, 0, 5);

                return "Collision de salle : La salle '{$roomNumber}' est déjà occupée par la classe {$roomConflict->schoolClass?->name} ({$roomConflict->subject?->name}) de {$start} à {$end} le {$dayOfWeek}.";
            }
        }

        return null;
    }
}
