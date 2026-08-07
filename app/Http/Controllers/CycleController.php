<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CycleController extends Controller
{
    /**
     * Display listing of cycles and levels.
     */
    public function index(): View
    {
        $cycles = Cycle::with(['levels.classes'])->get();

        return view('cycles.index', compact('cycles'));
    }

    /**
     * Display the specified cycle.
     */
    public function show(Cycle $cycle): View
    {
        $cycle->load(['levels.classes']);

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
    public function update(Request $request, Cycle $cycle)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:125',
            'code' => 'required|string|max:50|unique:cycles,code,' . $cycle->id,
            'description' => 'nullable|string',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'uniform_tshirt_color' => 'nullable|string|max:50',
        ]);

        $cycle->update($validated);

        return redirect()->route('cycles.index')
            ->with('status', 'Le cycle a été mis à jour avec succès.');
    }
}
