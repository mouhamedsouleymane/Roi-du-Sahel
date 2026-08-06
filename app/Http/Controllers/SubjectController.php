<?php

namespace App\Http\Controllers;

use App\Models\ClassSubjectCoefficient;
use App\Models\Level;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    /**
     * Display listing of subjects and coefficients.
     */
    public function index(): View
    {
        $subjects = Subject::orderBy('category')->orderBy('name')->get();
        $levels = Level::with(['cycle', 'coefficients.subject'])->orderBy('order_index')->get();

        return view('subjects.index', compact('subjects', 'levels'));
    }

    /**
     * Store a new subject.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:subjects,code|max:50',
            'name' => 'required|string|max:125',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        Subject::create($validated);

        return redirect()->route('subjects.index')->with('status', 'La matière a été ajoutée avec succès.');
    }

    /**
     * Store or update subject coefficient for a level.
     */
    public function storeCoefficient(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'level_id' => 'required|exists:levels,id',
            'subject_id' => 'required|exists:subjects,id',
            'coefficient' => 'required|integer|min:1|max:20',
            'weekly_hours' => 'required|integer|min:1|max:20',
        ]);

        ClassSubjectCoefficient::updateOrCreate(
            [
                'level_id' => $validated['level_id'],
                'subject_id' => $validated['subject_id'],
            ],
            [
                'coefficient' => $validated['coefficient'],
                'weekly_hours' => $validated['weekly_hours'],
            ]
        );

        return redirect()->route('subjects.index')->with('status', 'Le coefficient de la matière a été enregistré.');
    }
}
