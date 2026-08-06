<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of academic years.
     */
    public function index(): View
    {
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();

        return view('academic_years.index', compact('academicYears'));
    }

    /**
     * Store a newly created academic year in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:academic_years,name|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $year = AcademicYear::create([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'is_closed' => false,
        ]);

        if ($year->is_active) {
            $year->activate();
        }

        return redirect()->route('academic-years.index')->with('status', 'Année scolaire créée avec succès.');
    }

    /**
     * Activate the specified academic year.
     */
    public function activate(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->activate();

        return redirect()->route('academic-years.index')->with('status', "L'année scolaire {$academicYear->name} est désormais l'année active.");
    }

    /**
     * Toggle close status for academic year.
     */
    public function toggleClose(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->update(['is_closed' => ! $academicYear->is_closed]);
        $statusMessage = $academicYear->is_closed ? 'clôturée' : 'réouverte';

        return redirect()->route('academic-years.index')->with('status', "L'année scolaire {$academicYear->name} a été {$statusMessage}.");
    }
}
