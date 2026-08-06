<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $teachers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Enseignant', 'Super Admin', 'Directeur', 'Censeur']);
        })->get();

        $classes = SchoolClass::with(['level.cycle', 'academicYear', 'mainTeacher'])
            ->when($selectedYearId, function ($query) use ($selectedYearId) {
                $query->where('academic_year_id', $selectedYearId);
            })
            ->get()
            ->groupBy('level.cycle.name');

        return view('classes.index', compact('classes', 'academicYears', 'levels', 'teachers', 'selectedYearId'));
    }

    /**
     * Store a newly created class in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'level_id' => 'required|exists:levels,id',
            'name' => 'required|string|max:125',
            'capacity' => 'required|integer|min:1|max:100',
            'room_number' => 'nullable|string|max:50',
            'main_teacher_id' => 'nullable|exists:users,id',
        ]);

        SchoolClass::create($validated);

        return redirect()->route('classes.index', ['academic_year_id' => $validated['academic_year_id']])
            ->with('status', 'La classe a été créée avec succès.');
    }
}
