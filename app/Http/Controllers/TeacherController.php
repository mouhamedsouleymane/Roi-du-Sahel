<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TeacherController extends Controller
{
    /**
     * Display a listing of teachers grouped by cycle.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $cycleId = $request->query('cycle_id');
        $status = $request->query('status');
        $employmentType = $request->query('employment_type');

        $cycles = Cycle::where('is_active', true)->orderBy('id')->get();

        $teachers = Teacher::with(['user', 'cycle', 'cycles', 'levels.cycle', 'assignments.schoolClass', 'assignments.subject'])
            ->when($search, function ($q) use ($search) {
                $q->where('matricule', 'LIKE', "%{$search}%")
                    ->orWhere('speciality', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                    });
            })
            ->when($cycleId, function ($q) use ($cycleId) {
                $q->where('cycle_id', $cycleId)
                    ->orWhereHas('cycles', fn ($cq) => $cq->where('cycles.id', $cycleId));
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($employmentType, fn ($q) => $q->where('employment_type', $employmentType))
            ->orderBy('id', 'desc')
            ->get();

        // Group teachers by cycle
        $teachersByCycle = $teachers->groupBy(function ($teacher) {
            return $teacher->cycle?->name ?? 'Sans Cycle Assigné';
        });

        // Global statistics
        $totalTeachers = $teachers->count();
        $activeTeachersCount = $teachers->where('status', 'ACTIF')->count();
        $permanentTeachersCount = $teachers->where('employment_type', 'PERMANENT')->count();
        $totalAssignmentsCount = $teachers->sum(fn ($t) => $t->assignments->count());

        return view('teachers.index', compact(
            'teachers',
            'teachersByCycle',
            'cycles',
            'search',
            'cycleId',
            'status',
            'employmentType',
            'totalTeachers',
            'activeTeachersCount',
            'permanentTeachersCount',
            'totalAssignmentsCount'
        ));
    }

    /**
     * Show the form for creating a new teacher.
     */
    public function create(): View
    {
        $cycles = Cycle::with(['levels' => fn ($q) => $q->orderBy('order_index')])
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        return view('teachers.create', compact('cycles'));
    }

    /**
     * Store a newly registered teacher.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:125',
            'email' => 'required|email|unique:users,email|max:125',
            'phone' => 'nullable|string|max:50',
            'cycle_id' => 'nullable|exists:cycles,id',
            'cycle_ids' => 'nullable|array',
            'cycle_ids.*' => 'exists:cycles,id',
            'level_ids' => 'nullable|array',
            'level_ids.*' => 'exists:levels,id',
            'speciality' => 'required|string|max:125',
            'qualification' => 'nullable|string|max:125',
            'employment_type' => 'required|in:PERMANENT,VACATAIRE,CONTRACTUEL',
            'hire_date' => 'nullable|date',
        ]);

        $cycleIds = $request->input('cycle_ids', []);
        if (empty($cycleIds) && ! empty($validated['cycle_id'])) {
            $cycleIds = [(int) $validated['cycle_id']];
        }
        $cycleIds = array_values(array_unique(array_filter(array_map('intval', (array) $cycleIds))));

        if (empty($cycleIds)) {
            return redirect()->back()->withInput()->withErrors(['cycle_ids' => 'Veuillez sélectionner au moins un cycle d\'enseignement.']);
        }

        $selectedCycles = Cycle::whereIn('id', $cycleIds)->get();
        $polyvalentNames = Teacher::polyvalentCycleNames();
        $polyvalentSelected = $selectedCycles->filter(fn ($c) => in_array(strtolower(trim($c->name)), $polyvalentNames, true));

        if ($polyvalentSelected->isNotEmpty() && count($cycleIds) > 1) {
            return redirect()->back()->withInput()->withErrors([
                'cycle_ids' => 'Les cycles Maternelle et Primaire sont exclusifs et ne peuvent pas être combinés avec d\'autres cycles.',
            ]);
        }

        $primaryCycleId = $cycleIds[0];

        $normalizedPhone = null;
        if (! empty($validated['phone'])) {
            $digits = preg_replace('/\D/', '', $validated['phone']);
            $normalizedPhone = strlen($digits) > 8 ? substr($digits, -8) : $digits;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $normalizedPhone,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('Enseignant');

        $nextNum = Teacher::count() + 1;
        $matricule = sprintf('ENS-%s-%04d', now()->year, $nextNum);

        // Detect polyvalence from the selected cycle name
        $primaryCycle = $selectedCycles->firstWhere('id', $primaryCycleId);
        $isPolyvalent = $primaryCycle && in_array(strtolower(trim($primaryCycle->name)), Teacher::polyvalentCycleNames(), strict: true);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'cycle_id' => $primaryCycleId,
            'matricule' => $matricule,
            'speciality' => $validated['speciality'],
            'qualification' => $validated['qualification'] ?? null,
            'employment_type' => $validated['employment_type'],
            'hire_date' => $validated['hire_date'] ?? now()->toDateString(),
            'status' => 'ACTIF',
            'is_polyvalent' => $isPolyvalent,
        ]);

        // Sync multiple cycles and levels
        $teacher->cycles()->sync($cycleIds);
        if ($request->has('level_ids')) {
            $teacher->levels()->sync($request->input('level_ids', []));
        }

        return redirect()->route('teachers.index')
            ->with('status', "L'enseignant a été enregistré avec succès.");
    }

    /**
     * Display teacher profile and assignment details.
     */
    public function show(Teacher $teacher): View
    {
        $teacher->load([
            'user',
            'cycle',
            'cycles',
            'levels.cycle',
            'assignments.schoolClass.level.cycle',
            'assignments.subject',
            'schedules.schoolClass',
            'schedules.subject',
        ]);

        return view('teachers.show', compact('teacher'));
    }

    /**
     * Show the form for editing the specified teacher.
     */
    public function edit(Teacher $teacher): View
    {
        $teacher->load(['user', 'cycle', 'cycles', 'levels']);
        $cycles = Cycle::with(['levels' => fn ($q) => $q->orderBy('order_index')])
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $selectedCycleIds = $teacher->cycles->isNotEmpty()
            ? $teacher->cycles->pluck('id')->toArray()
            : ($teacher->cycle_id ? [$teacher->cycle_id] : []);

        $selectedLevelIds = $teacher->levels->pluck('id')->toArray();

        return view('teachers.edit', compact('teacher', 'cycles', 'selectedCycleIds', 'selectedLevelIds'));
    }

    /**
     * Update the specified teacher in storage.
     */
    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:125',
            'email' => 'required|email|max:125|unique:users,email,'.$teacher->user_id,
            'phone' => 'nullable|string|max:50',
            'cycle_id' => 'nullable|exists:cycles,id',
            'cycle_ids' => 'nullable|array',
            'cycle_ids.*' => 'exists:cycles,id',
            'level_ids' => 'nullable|array',
            'level_ids.*' => 'exists:levels,id',
            'speciality' => 'required|string|max:125',
            'qualification' => 'nullable|string|max:125',
            'employment_type' => 'required|in:PERMANENT,VACATAIRE,CONTRACTUEL',
            'status' => 'required|in:ACTIF,INACTIF,CONGE',
        ]);

        $cycleIds = $request->input('cycle_ids', []);
        if (empty($cycleIds) && ! empty($validated['cycle_id'])) {
            $cycleIds = [(int) $validated['cycle_id']];
        }
        $cycleIds = array_values(array_unique(array_filter(array_map('intval', (array) $cycleIds))));

        if (empty($cycleIds)) {
            return redirect()->back()->withInput()->withErrors(['cycle_ids' => 'Veuillez sélectionner au moins un cycle d\'enseignement.']);
        }

        $selectedCycles = Cycle::whereIn('id', $cycleIds)->get();
        $polyvalentNames = Teacher::polyvalentCycleNames();
        $polyvalentSelected = $selectedCycles->filter(fn ($c) => in_array(strtolower(trim($c->name)), $polyvalentNames, true));

        if ($polyvalentSelected->isNotEmpty() && count($cycleIds) > 1) {
            return redirect()->back()->withInput()->withErrors([
                'cycle_ids' => 'Les cycles Maternelle et Primaire sont exclusifs et ne peuvent pas être combinés avec d\'autres cycles.',
            ]);
        }

        $primaryCycleId = $cycleIds[0];

        $normalizedPhone = null;
        if (! empty($validated['phone'])) {
            $digits = preg_replace('/\D/', '', $validated['phone']);
            $normalizedPhone = strlen($digits) > 8 ? substr($digits, -8) : $digits;
        }

        $teacher->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $normalizedPhone,
        ]);

        $primaryCycle = $selectedCycles->firstWhere('id', $primaryCycleId);
        $isPolyvalent = $primaryCycle && in_array(strtolower(trim($primaryCycle->name)), Teacher::polyvalentCycleNames(), strict: true);

        $teacher->update([
            'cycle_id' => $primaryCycleId,
            'speciality' => $validated['speciality'],
            'qualification' => $validated['qualification'] ?? null,
            'employment_type' => $validated['employment_type'],
            'status' => $validated['status'],
            'is_polyvalent' => $isPolyvalent,
        ]);

        // Sync multiple cycles and levels
        $teacher->cycles()->sync($cycleIds);
        if ($request->has('level_ids')) {
            $teacher->levels()->sync($request->input('level_ids', []));
        }

        return redirect()->route('teachers.show', $teacher)
            ->with('status', "Le profil de l'enseignant a été mis à jour avec succès.");
    }
}
