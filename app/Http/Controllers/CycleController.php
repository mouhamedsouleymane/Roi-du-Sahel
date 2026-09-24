<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use App\Models\Level;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CycleController extends Controller
{
    protected array $colorClasses = [
        'Violet' => 'bg-purple-600',
        'Jaune' => 'bg-amber-500',
        'Vert' => 'bg-green-600',
        'Bleu' => 'bg-blue-600',
        'Rouge' => 'bg-red-600',
    ];

    /**
     * Display listing of cycles and levels.
     */
    public function index(): View
    {
        $cycles = Cycle::with(['levels.classes'])->orderBy('code')->get();

        return view('cycles.index', compact('cycles'));
    }

    /**
     * Show the form for creating a new cycle.
     */
    public function create(): View
    {
        return view('cycles.create');
    }

    /**
     * Store a newly created cycle in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:125',
            'code' => 'required|string|max:50|unique:cycles,code',
            'description' => 'nullable|string',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'uniform_tshirt_color' => 'nullable|string|max:50',
        ]);

        $validated['is_active'] = true;

        Cycle::create($validated);

        return redirect()->route('cycles.index')
            ->with('status', 'Le cycle a été créé avec succès.');
    }

    /**
     * Display the specified cycle.
     */
    public function show(Cycle $cycle): View
    {
        $cycle->load(['levels.classes' => fn ($q) => $q->withCount('enrollments')]);

        return view('cycles.show', compact('cycle'));
    }

    /**
     * Show the form for editing the specified cycle.
     */
    public function edit(Cycle $cycle): View
    {
        return view('cycles.edit', compact('cycle'));
    }

    /**
     * Update the specified cycle in storage.
     */
    public function update(Request $request, Cycle $cycle): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:125',
            'code' => 'required|string|max:50|unique:cycles,code,'.$cycle->id,
            'description' => 'nullable|string',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'uniform_tshirt_color' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $cycle->update($validated);

        return redirect()->route('cycles.index')
            ->with('status', 'Le cycle a été mis à jour avec succès.');
    }

    /**
     * Activate or deactivate the specified cycle.
     */
    public function toggleActive(Cycle $cycle): RedirectResponse
    {
        $cycle->update(['is_active' => ! $cycle->is_active]);

        $status = $cycle->is_active ? 'activé' : 'désactivé';

        return redirect()->route('cycles.index')
            ->with('status', "Le cycle a été {$status} avec succès.");
    }

    /**
     * Remove the specified cycle from storage.
     */
    public function destroy(Cycle $cycle): RedirectResponse
    {
        if ($cycle->levels()->exists()) {
            return redirect()->route('cycles.index')
                ->with('error', 'Impossible de supprimer un cycle contenant des niveaux.');
        }

        $cycle->delete();

        return redirect()->route('cycles.index')
            ->with('status', 'Le cycle a été supprimé avec succès.');
    }

    // ==================== NIVEAUX ====================

    /**
     * Store a new level for the specified cycle.
     */
    public function storeLevel(Request $request, Cycle $cycle): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:125',
            'order_index' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        // Validation manuelle pour l'unicité composite
        if ($cycle->levels()->where('code', $validated['code'])->exists()) {
            return redirect()->route('cycles.show', $cycle)
                ->with('error', "Le code '{$validated['code']}' existe déjà pour ce cycle.")
                ->withInput();
        }

        $cycle->levels()->create($validated);

        return redirect()->route('cycles.show', $cycle)
            ->with('status', 'Le niveau a été ajouté avec succès.');
    }

    /**
     * Update the specified level.
     */
    public function updateLevel(Request $request, Cycle $cycle, Level $level): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:125',
            'order_index' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        // Validation manuelle pour l'unicité composite
        if ($cycle->levels()->where('code', $validated['code'])->where('id', '!=', $level->id)->exists()) {
            return redirect()->route('cycles.show', $cycle)
                ->with('error', "Le code '{$validated['code']}' existe déjà pour ce cycle.")
                ->withInput();
        }

        $level->update($validated);

        return redirect()->route('cycles.show', $cycle)
            ->with('status', 'Le niveau a été mis à jour avec succès.');
    }

    /**
     * Remove the specified level.
     */
    public function destroyLevel(Cycle $cycle, Level $level): RedirectResponse
    {
        if ($level->classes()->exists()) {
            return redirect()->route('cycles.show', $cycle)
                ->with('error', 'Impossible de supprimer un niveau contenant des classes.');
        }

        $level->delete();

        return redirect()->route('cycles.show', $cycle)
            ->with('status', 'Le niveau a été supprimé avec succès.');
    }
}
