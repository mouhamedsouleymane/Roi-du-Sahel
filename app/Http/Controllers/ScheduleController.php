<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Display weekly timetable for a class.
     */
    public function index(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $classes = SchoolClass::with('level.cycle')->where('academic_year_id', $activeYear?->id ?? 0)->get();

        $selectedClassId = $request->query('class_id', $classes->first()?->id);

        $schedules = Schedule::with(['subject', 'teacher.user', 'schoolClass'])
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->when($selectedClassId, function ($q) use ($selectedClassId) {
                $q->where('class_id', $selectedClassId);
            })
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        $teachers = Teacher::with('user')->where('status', 'ACTIF')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        return view('schedules.index', compact('schedules', 'classes', 'selectedClassId', 'teachers', 'subjects', 'activeYear'));
    }

    /**
     * Store a new timetable slot.
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

        return redirect()->route('schedules.index', ['class_id' => $validated['class_id']])
            ->with('status', 'Le créneau horaire a été ajouté avec succès.');
    }

    /**
     * Display the specified schedule slot.
     */
    public function show(Schedule $schedule): View
    {
        $schedule->load(['subject', 'teacher.user', 'schoolClass.level.cycle', 'academicYear']);

        return view('schedules.show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified schedule slot.
     */
    public function edit(Schedule $schedule): View
    {
        $activeYear = AcademicYear::getActive();
        $classes = SchoolClass::with('level.cycle')->where('academic_year_id', $activeYear?->id ?? 0)->get();
        $teachers = Teacher::with('user')->where('status', 'ACTIF')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        return view('schedules.edit', compact('schedule', 'classes', 'teachers', 'subjects'));
    }

    /**
     * Update the specified schedule slot in storage.
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

        $schedule->update($validated);

        return redirect()->route('schedules.index', ['class_id' => $validated['class_id']])
            ->with('status', 'Le créneau a été mis à jour avec succès.');
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
}
