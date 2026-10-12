<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    /**
     * Display a listing of classes.
     */
    public function index(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $selectedYearId = $request->query('academic_year_id', $activeYear?->id);

        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $levels = Level::with('cycle')->orderBy('order_index')->get();
        $teachers = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['Enseignant', 'Super Admin', 'Directeur', 'Censeur']))->get();

        $classes = SchoolClass::with(['level.cycle', 'academicYear', 'mainTeacher'])
            ->withCount([
                'enrollments' => fn ($q) => $q->whereIn('status', ['EN_ATTENTE', 'VALIDE']),
                'enrollments as all_enrollments_count',
            ])
            ->when($selectedYearId, fn ($q) => $q->where('academic_year_id', $selectedYearId))
            ->get()
            ->groupBy('level.cycle.name');

        return view('classes.index', compact('classes', 'academicYears', 'levels', 'teachers', 'selectedYearId'));
    }

    /**
     * Show the form for creating a new class.
     */
    public function create(): View
    {
        $activeYear = AcademicYear::getActive();
        $levels = Level::with('cycle')->orderBy('order_index')->get();
        $teachers = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['Enseignant', 'Super Admin', 'Directeur', 'Censeur']))->get();
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();

        return view('classes.create', compact('levels', 'teachers', 'academicYears', 'activeYear'));
    }

    /**
     * Store a newly created class in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'level_id' => 'required|exists:levels,id',
            'name' => [
                'required', 'string', 'max:125',
                Rule::unique('classes')->where(fn ($q) => $q
                    ->where('academic_year_id', $request->academic_year_id)
                    ->where('level_id', $request->level_id)
                ),
            ],
            'capacity' => 'required|integer|min:1|max:100',
            'room_number' => 'nullable|string|max:50',
            'main_teacher_id' => 'nullable|exists:users,id',
        ]);

        if (! empty($validated['main_teacher_id'])) {
            $mainTeacher = Teacher::with('cycle')->where('user_id', $validated['main_teacher_id'])->first();
            if ($mainTeacher && $mainTeacher->isRestrictedToOneClass()) {
                $otherClass = SchoolClass::where('academic_year_id', $validated['academic_year_id'])
                    ->where('main_teacher_id', $validated['main_teacher_id'])
                    ->first();

                if ($otherClass) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors([
                            'main_teacher_id' => "Cet enseignant de cycle {$mainTeacher->cycle?->name} est déjà titulaire de la classe {$otherClass->name} pour cette année scolaire.",
                        ]);
                }
            }
        }

        SchoolClass::create($validated);

        return redirect()->route('classes.index', ['academic_year_id' => $validated['academic_year_id']])
            ->with('status', 'La classe a été créée avec succès.');
    }

    /**
     * Display the specified class.
     */
    public function show(SchoolClass $schoolClass): View
    {
        $schoolClass->load([
            'level.cycle',
            'academicYear',
            'mainTeacher',
            'enrollments.student',
            'assignments.teacher.user',
            'assignments.subject',
        ]);

        return view('classes.show', compact('schoolClass'));
    }

    /**
     * Show the form for editing the specified class.
     */
    public function edit(SchoolClass $schoolClass): View
    {
        $levels = Level::with('cycle')->orderBy('order_index')->get();
        $teachers = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['Enseignant', 'Super Admin', 'Directeur', 'Censeur']))->get();

        return view('classes.edit', compact('schoolClass', 'levels', 'teachers'));
    }

    /**
     * Update the specified class in storage.
     */
    public function update(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $validated = $request->validate([
            'level_id' => 'required|exists:levels,id',
            'name' => [
                'required', 'string', 'max:125',
                Rule::unique('classes')->where(fn ($q) => $q
                    ->where('academic_year_id', $schoolClass->academic_year_id)
                    ->where('level_id', $request->level_id)
                )->ignore($schoolClass->id),
            ],
            'capacity' => 'required|integer|min:1|max:100',
            'room_number' => 'nullable|string|max:50',
            'main_teacher_id' => 'nullable|exists:users,id',
        ]);

        if (! empty($validated['main_teacher_id'])) {
            $mainTeacher = Teacher::with('cycle')->where('user_id', $validated['main_teacher_id'])->first();
            if ($mainTeacher && $mainTeacher->isRestrictedToOneClass()) {
                $otherClass = SchoolClass::where('academic_year_id', $schoolClass->academic_year_id)
                    ->where('main_teacher_id', $validated['main_teacher_id'])
                    ->where('id', '!=', $schoolClass->id)
                    ->first();

                if ($otherClass) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors([
                            'main_teacher_id' => "Cet enseignant de cycle {$mainTeacher->cycle?->name} est déjà titulaire de la classe {$otherClass->name} pour cette année scolaire.",
                        ]);
                }
            }
        }

        $schoolClass->update($validated);

        return redirect()->route('classes.index', ['academic_year_id' => $schoolClass->academic_year_id])
            ->with('status', 'La classe a été mise à jour avec succès.');
    }

    /**
     * Remove the specified class from storage.
     */
    public function destroy(SchoolClass $schoolClass): RedirectResponse
    {
        $yearId = $schoolClass->academic_year_id;

        if ($schoolClass->enrollments()->exists()) {
            return redirect()->route('classes.index', ['academic_year_id' => $yearId])
                ->with('error', "Impossible de supprimer la classe {$schoolClass->name} : {$schoolClass->enrollments()->count()} inscription(s) y sont rattachées.");
        }

        $schoolClass->delete();

        return redirect()->route('classes.index', ['academic_year_id' => $yearId])
            ->with('status', 'La classe a été supprimée.');
    }
}
