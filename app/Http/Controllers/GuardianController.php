<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuardianController extends Controller
{
    /**
     * Display a listing of guardians.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $guardians = Guardian::with('students')
            ->when($search, function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('phone_primary', 'LIKE', "%{$search}%");
            })
            ->orderBy('last_name')
            ->paginate(15);

        return view('guardians.index', compact('guardians', 'search'));
    }

    /**
     * Display the specified guardian.
     */
    public function show(Guardian $guardian): View
    {
        $guardian->load('students.enrollments.schoolClass');

        return view('guardians.show', compact('guardian'));
    }

    /**
     * Show the form for editing the specified guardian.
     */
    public function edit(Guardian $guardian): View
    {
        return view('guardians.edit', compact('guardian'));
    }

    /**
     * Update the specified guardian in storage.
     */
    public function update(Request $request, Guardian $guardian)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:125',
            'last_name' => 'required|string|max:125',
            'relationship' => 'required|string|max:50',
            'phone_primary' => 'required|string|max:50',
            'phone_secondary' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'profession' => 'nullable|string|max:125',
            'address' => 'nullable|string|max:255',
        ]);

        $guardian->update($validated);

        return redirect()->route('guardians.show', $guardian)
            ->with('status', 'Le tuteur a été mis à jour avec succès.');
    }
}
