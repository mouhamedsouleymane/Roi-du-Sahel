<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TeacherController extends Controller
{
    /**
     * Display a listing of teachers.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $teachers = Teacher::with(['user', 'assignments.schoolClass', 'assignments.subject'])
            ->when($search, function ($q) use ($search) {
                $q->where('matricule', 'LIKE', "%{$search}%")
                  ->orWhere('speciality', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            })
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('teachers.index', compact('teachers', 'search'));
    }

    /**
     * Store a newly registered teacher.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:125',
            'email' => 'required|email|unique:users,email|max:125',
            'speciality' => 'required|string|max:125',
            'qualification' => 'nullable|string|max:125',
            'employment_type' => 'required|in:PERMANENT,VACATAIRE,CONTRACTUEL',
            'hire_date' => 'nullable|date',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('Enseignant');

        $nextNum = Teacher::count() + 1;
        $matricule = sprintf('ENS-2025-%04d', $nextNum);

        Teacher::create([
            'user_id' => $user->id,
            'matricule' => $matricule,
            'speciality' => $validated['speciality'],
            'qualification' => $validated['qualification'] ?? null,
            'employment_type' => $validated['employment_type'],
            'hire_date' => $validated['hire_date'] ?? now()->toDateString(),
            'status' => 'ACTIF',
        ]);

        return redirect()->route('teachers.index')->with('status', 'L\'enseignant a été enregistré avec succès.');
    }

    /**
     * Display teacher profile and assignment details.
     */
    public function show(Teacher $teacher): View
    {
        $teacher->load(['user', 'assignments.schoolClass.level.cycle', 'assignments.subject', 'schedules.schoolClass', 'schedules.subject']);

        return view('teachers.show', compact('teacher'));
    }

    /**
     * Show the form for editing the specified teacher.
     */
    public function edit(Teacher $teacher): View
    {
        $teacher->load('user');

        return view('teachers.edit', compact('teacher'));
    }

    /**
     * Update the specified teacher in storage.
     */
    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:125',
            'email' => 'required|email|max:125|unique:users,email,' . $teacher->user_id,
            'speciality' => 'required|string|max:125',
            'qualification' => 'nullable|string|max:125',
            'employment_type' => 'required|in:PERMANENT,VACATAIRE,CONTRACTUEL',
            'status' => 'required|in:ACTIF,INACTIF,CONGE',
        ]);

        $teacher->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $teacher->update([
            'speciality' => $validated['speciality'],
            'qualification' => $validated['qualification'] ?? null,
            'employment_type' => $validated['employment_type'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('teachers.show', $teacher)
            ->with('status', 'Le profil de l\'enseignant a été mis à jour avec succès.');
    }
}
