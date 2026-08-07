<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherAssignmentController extends Controller
{
    /**
     * Display a listing of teacher assignments.
     */
    public function index(): View
    {
        $activeYear = AcademicYear::getActive();

        $assignments = TeacherAssignment::with(['teacher.user', 'schoolClass.level.cycle', 'subject'])
            ->when($activeYear, function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id);
            })
            ->get();

        $teachers = Teacher::with('user')->where('status', 'ACTIF')->get();
        $classes = SchoolClass::with('level.cycle')->where('academic_year_id', $activeYear?->id ?? 0)->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        return view('assignments.index', compact('assignments', 'teachers', 'classes', 'subjects', 'activeYear'));
    }

    /**
     * Store a new teacher assignment.
     */
    public function store(Request $request): RedirectResponse
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return redirect()->back()->withErrors(['error' => 'Aucune année scolaire active.']);
        }

        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        TeacherAssignment::updateOrCreate(
            [
                'academic_year_id' => $activeYear->id,
                'class_id' => $validated['class_id'],
                'subject_id' => $validated['subject_id'],
            ],
            [
                'teacher_id' => $validated['teacher_id'],
            ]
        );

        return redirect()->route('assignments.index')->with('status', 'L\'affectation de l\'enseignant a été enregistrée.');
    }
}
