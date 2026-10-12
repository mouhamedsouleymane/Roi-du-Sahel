<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Period;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    public function store(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'type' => 'required|in:TRIMESTRE,SEMESTRE',
            'order' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $exists = $academicYear->periods()
            ->where('type', $validated['type'])
            ->where('order', $validated['order'])
            ->exists();

        if ($exists) {
            return redirect()->route('academic-years.show', $academicYear)
                ->with('error', "Un {$validated['type']} n°{$validated['order']} existe déjà pour cette année.");
        }

        $academicYear->periods()->create($validated);

        return redirect()->route('academic-years.show', $academicYear)
            ->with('status', "{$validated['name']} ajouté avec succès.");
    }

    public function update(Request $request, AcademicYear $academicYear, Period $period): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $period->update($validated);

        return redirect()->route('academic-years.show', $academicYear)
            ->with('status', "{$period->name} mis à jour avec succès.");
    }

    public function destroy(AcademicYear $academicYear, Period $period): RedirectResponse
    {
        if ($period->is_closed) {
            return redirect()->route('academic-years.show', $academicYear)
                ->with('error', 'Impossible de supprimer une période clôturée.');
        }

        $period->delete();

        return redirect()->route('academic-years.show', $academicYear)
            ->with('status', 'Période supprimée avec succès.');
    }

    /**
     * Toggle close status for a period.
     *
     * Une période ne peut être clôturée qu'après sa date de fin prévue.
     * Sa réouverture reste toujours possible.
     */
    public function toggleClose(AcademicYear $academicYear, Period $period): RedirectResponse
    {
        if (! $period->is_closed && ! $period->hasEnded()) {
            return redirect()->route('academic-years.show', $academicYear)
                ->with('error', "Impossible de clôturer {$period->name} : la date de fin prévue ({$period->end_date->format('d/m/Y')}) n'est pas encore dépassée. Clôture possible à partir du {$period->end_date->copy()->addDay()->format('d/m/Y')}.");
        }

        $period->update(['is_closed' => ! $period->is_closed]);
        $msg = $period->is_closed ? 'clôturée' : 'réouverte';

        return redirect()->route('academic-years.show', $academicYear)
            ->with('status', "{$period->name} {$msg} avec succès.");
    }
}
