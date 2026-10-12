<?php

namespace App\Http\Controllers;

use App\Models\ClassSubjectCoefficient;
use App\Models\Cycle;
use App\Models\Level;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    /**
     * Categories allowed for subjects.
     */
    public const CATEGORIES = [
        'scientifique' => 'Scientifique',
        'littéraire' => 'Littéraire',
        'langues' => 'Langues',
        'sport' => 'Sport / EPS',
        'général' => 'Général',
        'autre' => 'Autre',
    ];

    /**
     * Display listing of subjects and coefficients.
     */
    public function index(Request $request): View
    {
        $selectedCategory = $request->query('category');
        $selectedLevelId = $request->query('level_id');

        $subjects = Subject::withCount(['coefficients', 'teacherAssignments', 'evaluations'])
            ->when($selectedCategory, fn ($q) => $q->where('category', $selectedCategory))
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $levels = Level::with(['cycle', 'coefficients.subject'])
            ->when($selectedLevelId, fn ($q) => $q->where('id', $selectedLevelId))
            ->orderBy('order_index')
            ->get();

        $allLevels = Level::with('cycle')->orderBy('order_index')->get();

        $categories = self::CATEGORIES;

        return view('subjects.index', compact(
            'subjects',
            'levels',
            'allLevels',
            'categories',
            'selectedCategory',
            'selectedLevelId'
        ));
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create(): View
    {
        $categories = self::CATEGORIES;
        $cycles = Cycle::with(['levels' => fn ($q) => $q->orderBy('order_index')])->get();

        return view('subjects.create', compact('categories', 'cycles'));
    }

    /**
     * Store a new subject.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:subjects,code',
            'name' => 'required|string|max:125',
            'category' => ['required', 'string', 'max:50', Rule::in(array_keys(self::CATEGORIES))],
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'level_ids' => 'nullable|array',
            'level_ids.*' => 'exists:levels,id',
            'levels_data' => 'nullable|array',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);

        $subject = Subject::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'],
        ]);

        if (! empty($validated['level_ids'])) {
            $levelsData = $request->input('levels_data', []);
            foreach ($validated['level_ids'] as $levelId) {
                $coeff = isset($levelsData[$levelId]['coefficient']) ? (int) $levelsData[$levelId]['coefficient'] : 1;
                $hours = isset($levelsData[$levelId]['weekly_hours']) ? (int) $levelsData[$levelId]['weekly_hours'] : 2;

                ClassSubjectCoefficient::updateOrCreate(
                    [
                        'level_id' => $levelId,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'coefficient' => max(1, $coeff),
                        'weekly_hours' => max(1, $hours),
                        'is_optional' => false,
                    ]
                );
            }
        }

        return redirect()->route('subjects.index')
            ->with('status', 'La matière a été ajoutée avec succès.');
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject): View
    {
        $categories = self::CATEGORIES;
        $cycles = Cycle::with(['levels' => fn ($q) => $q->orderBy('order_index')])->get();
        $subject->load('coefficients');
        $existingLevelIds = $subject->coefficients->pluck('level_id')->toArray();
        $coefficientsByLevel = $subject->coefficients->keyBy('level_id');

        return view('subjects.edit', compact(
            'subject',
            'categories',
            'cycles',
            'existingLevelIds',
            'coefficientsByLevel'
        ));
    }

    /**
     * Update the specified subject in storage.
     */
    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('subjects', 'code')->ignore($subject->id)],
            'name' => 'required|string|max:125',
            'category' => ['required', 'string', 'max:50', Rule::in(array_keys(self::CATEGORIES))],
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'level_ids' => 'nullable|array',
            'level_ids.*' => 'exists:levels,id',
            'levels_data' => 'nullable|array',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active');

        $subject->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'],
        ]);

        if ($request->has('level_ids')) {
            $selectedLevelIds = $validated['level_ids'] ?? [];
            $levelsData = $request->input('levels_data', []);

            // Ajouter ou mettre à jour les coefficients pour les niveaux sélectionnés
            foreach ($selectedLevelIds as $levelId) {
                $coeff = isset($levelsData[$levelId]['coefficient']) ? (int) $levelsData[$levelId]['coefficient'] : 1;
                $hours = isset($levelsData[$levelId]['weekly_hours']) ? (int) $levelsData[$levelId]['weekly_hours'] : 2;

                ClassSubjectCoefficient::updateOrCreate(
                    [
                        'level_id' => $levelId,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'coefficient' => max(1, $coeff),
                        'weekly_hours' => max(1, $hours),
                        'is_optional' => false,
                    ]
                );
            }
        }

        return redirect()->route('subjects.index')
            ->with('status', 'La matière a été mise à jour avec succès.');
    }

    /**
     * Toggle the active status of a subject.
     */
    public function toggleActive(Subject $subject): RedirectResponse
    {
        $subject->update(['is_active' => ! $subject->is_active]);

        $status = $subject->is_active ? 'activée' : 'désactivée';

        return redirect()->route('subjects.index')
            ->with('status', "La matière {$subject->name} a été {$status} avec succès.");
    }

    /**
     * Remove the specified subject from storage.
     */
    public function destroy(Subject $subject): RedirectResponse
    {
        if ($subject->coefficients()->exists()) {
            return redirect()->route('subjects.index')
                ->with('error', 'Impossible de supprimer cette matière car des coefficients lui sont associés par niveau.');
        }

        if ($subject->teacherAssignments()->exists()) {
            return redirect()->route('subjects.index')
                ->with('error', 'Impossible de supprimer cette matière car elle est affectée à un enseignant.');
        }

        if ($subject->evaluations()->exists()) {
            return redirect()->route('subjects.index')
                ->with('error', 'Impossible de supprimer cette matière car des évaluations/notes y sont rattachées.');
        }

        if ($subject->schedules()->exists()) {
            return redirect()->route('subjects.index')
                ->with('error', 'Impossible de supprimer cette matière car des cours y sont planifiés dans l\'emploi du temps.');
        }

        $subject->delete();

        return redirect()->route('subjects.index')
            ->with('status', 'La matière a été supprimée avec succès.');
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

        $level = Level::with('cycle')->findOrFail($validated['level_id']);

        return redirect()->route('cycles.levels.show', [$level->cycle, $level])
            ->with('status', 'Le coefficient de la matière a été enregistré.');
    }

    /**
     * Delete a subject coefficient.
     */
    public function destroyCoefficient(ClassSubjectCoefficient $coefficient): RedirectResponse
    {
        $level = Level::with('cycle')->findOrFail($coefficient->level_id);
        $coefficient->delete();

        return redirect()->route('cycles.levels.show', [$level->cycle, $level])
            ->with('status', 'Le coefficient a été supprimé.');
    }
}
