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
     * Show the form for creating a new academic year.
     */
    public function create(): View
    {
        return view('academic_years.create');
    }

    /**
     * Store a newly created academic year in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|unique:academic_years,name|max:125',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after:start_date',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $year = AcademicYear::create([
            'name'        => $validated['name'],
            'start_date'  => $validated['start_date'],
            'end_date'    => $validated['end_date'],
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->boolean('is_active'),
            'is_closed'   => false,
        ]);

        if ($year->is_active) {
            $year->activate();
        }

        return redirect()->route('academic-years.index')
            ->with('status', 'Année scolaire créée avec succès.');
    }

    /**
     * Display the specified academic year.
     */
    public function show(AcademicYear $academicYear): View
    {
        $academicYear->load('periods');

        return view('academic_years.show', compact('academicYear'));
    }

    /**
     * Show the form for editing the specified academic year.
     */
    public function edit(AcademicYear $academicYear): View
    {
        return view('academic_years.edit', compact('academicYear'));
    }

    /**
     * Update the specified academic year in storage.
     */
    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:125|unique:academic_years,name,'.$academicYear->id,
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after:start_date',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $academicYear->update([
            'name'        => $validated['name'],
            'start_date'  => $validated['start_date'],
            'end_date'    => $validated['end_date'],
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->boolean('is_active')) {
            $academicYear->activate();
        }

        return redirect()->route('academic-years.index')
            ->with('status', "L'année scolaire {$academicYear->name} a été mise à jour.");
    }

    /**
     * Remove the specified academic year from storage.
     */
    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        if ($academicYear->is_active) {
            return redirect()->route('academic-years.index')
                ->with('error', 'Impossible de supprimer l\'année scolaire active.');
        }

        if ($academicYear->enrollments()->exists()) {
            return redirect()->route('academic-years.index')
                ->with('error', 'Impossible de supprimer une année scolaire contenant des inscriptions.');
        }

        $academicYear->delete();

        return redirect()->route('academic-years.index')
            ->with('status', "L'année scolaire {$academicYear->name} a été supprimée.");
    }

    /**
     * Activate the specified academic year.
     */
    public function activate(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->activate();

        return redirect()->route('academic-years.index')
            ->with('status', "L'année scolaire {$academicYear->name} est désormais l'année active.");
    }

    /**
     * Toggle close status for academic year.
     */
    public function toggleClose(AcademicYear $academicYear): RedirectResponse
    {
        if (! $academicYear->is_closed && $academicYear->is_active) {
            return redirect()->route('academic-years.index')
                ->with('error', 'Impossible de clôturer l\'année scolaire active. Activez d\'abord une autre année.');
        }

        $academicYear->update(['is_closed' => ! $academicYear->is_closed]);
        $statusMessage = $academicYear->is_closed ? 'clôturée' : 'réouverte';

        return redirect()->route('academic-years.index')
            ->with('status', "L'année scolaire {$academicYear->name} a été {$statusMessage}.");
    }
}
