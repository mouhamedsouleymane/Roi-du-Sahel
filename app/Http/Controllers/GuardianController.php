<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class GuardianController extends Controller
{
    /**
     * Display a listing of guardians with stats and filters.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $relationship = $request->query('relationship');

        // Statistiques globales
        $totalGuardians = Guardian::count();
        $fathersCount = Guardian::where('relationship', 'PERE')->count();
        $mothersCount = Guardian::where('relationship', 'MERE')->count();
        $otherGuardiansCount = Guardian::whereNotIn('relationship', ['PERE', 'MERE'])->count();

        $guardians = Guardian::with('students.currentEnrollment.schoolClass.level.cycle')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('phone_primary', 'LIKE', "%{$search}%")
                        ->orWhere('phone_secondary', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                });
            })
            ->when($relationship, function ($q) use ($relationship) {
                $q->where('relationship', $relationship);
            })
            ->orderBy('last_name')
            ->paginate(15)
            ->withQueryString();

        return view('guardians.index', compact(
            'guardians',
            'search',
            'relationship',
            'totalGuardians',
            'fathersCount',
            'mothersCount',
            'otherGuardiansCount'
        ));
    }

    /**
     * Show the form for creating a new guardian.
     */
    public function create(): View
    {
        return view('guardians.create');
    }

    /**
     * Store a newly created guardian in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:125',
            'last_name' => 'required|string|max:125',
            'relationship' => 'required|string|max:50',
            'phone_primary' => 'required|string|max:50',
            'phone_secondary' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:125',
            'profession' => 'nullable|string|max:125',
            'city' => 'nullable|string|max:125',
            'address' => 'nullable|string|max:191',
        ]);

        // Clean & Normalize Phone Number (extract 8 digits)
        $digits = preg_replace('/\D/', '', $validated['phone_primary']);
        $cleanPhone = strlen($digits) > 8 ? substr($digits, -8) : $digits;

        // Find or create User for Parent
        $user = null;
        if (! empty($validated['email'])) {
            $user = User::where('email', $validated['email'])->first();
        }
        if (! $user && ! empty($cleanPhone)) {
            $user = User::where('phone', $cleanPhone)->first();
        }

        if (! $user) {
            $userEmail = ! empty($validated['email']) ? $validated['email'] : "parent.{$cleanPhone}@roisdusahel.ne";
            if (User::where('email', $userEmail)->exists()) {
                $userEmail = "parent.{$cleanPhone}.".uniqid().'@roisdusahel.ne';
            }

            $user = User::create([
                'name' => trim($validated['first_name'].' '.$validated['last_name']),
                'email' => $userEmail,
                'phone' => ! empty($cleanPhone) ? $cleanPhone : null,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            $user->assignRole('Parent');
        }

        $validated['user_id'] = $user->id;

        $guardian = Guardian::create($validated);

        return redirect()->route('guardians.show', $guardian)
            ->with('status', 'Le dossier du tuteur a été créé et son compte d\'accès Espace Parent a été activé.');
    }

    /**
     * Display the specified guardian.
     */
    public function show(Guardian $guardian): View
    {
        $guardian->load(['user', 'students.currentEnrollment.schoolClass.level.cycle']);

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
    public function update(Request $request, Guardian $guardian): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:125',
            'last_name' => 'required|string|max:125',
            'relationship' => 'required|string|max:50',
            'phone_primary' => 'required|string|max:50',
            'phone_secondary' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:125',
            'profession' => 'nullable|string|max:125',
            'city' => 'nullable|string|max:125',
            'address' => 'nullable|string|max:191',
        ]);

        $digits = preg_replace('/\D/', '', $validated['phone_primary']);
        $cleanPhone = strlen($digits) > 8 ? substr($digits, -8) : $digits;

        $guardian->update($validated);

        if ($guardian->user) {
            // Synchronise le compte existant
            $guardian->user->update([
                'name' => trim($validated['first_name'].' '.$validated['last_name']),
                'email' => ! empty($validated['email']) ? $validated['email'] : $guardian->user->email,
                'phone' => ! empty($cleanPhone) ? $cleanPhone : $guardian->user->phone,
            ]);
        } else {
            // Aucun compte lié — création automatique (parents importés anciennement)
            $userEmail = ! empty($validated['email']) ? $validated['email'] : "parent.{$cleanPhone}@roisdusahel.ne";
            if (User::where('email', $userEmail)->exists()) {
                $userEmail = "parent.{$cleanPhone}.".uniqid().'@roisdusahel.ne';
            }

            $user = User::create([
                'name' => trim($validated['first_name'].' '.$validated['last_name']),
                'email' => $userEmail,
                'phone' => ! empty($cleanPhone) ? $cleanPhone : null,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            $user->assignRole('Parent');
            $guardian->update(['user_id' => $user->id]);
        }

        return redirect()->route('guardians.show', $guardian)
            ->with('status', 'Le dossier du tuteur a été mis à jour avec succès.');
    }
}
