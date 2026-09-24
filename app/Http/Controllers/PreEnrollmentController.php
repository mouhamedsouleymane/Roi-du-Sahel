<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class PreEnrollmentController extends Controller
{
    /**
     * Traite la soumission d'une demande de pré-inscription en ligne depuis le site web public.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_last_name' => 'required|string|max:255',
            'student_first_name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'gender' => 'required|in:M,F',
            'requested_cycle' => 'required|string',
            'guardian_name' => 'required|string|max:255',
            'guardian_phone' => 'required|string|max:50',
            'guardian_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $ref = 'PRE-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 5));

        return back()->with('preinscription_success', [
            'ref' => $ref,
            'name' => $validated['student_first_name'] . ' ' . $validated['student_last_name'],
            'cycle' => $validated['requested_cycle'],
        ]);
    }
}
