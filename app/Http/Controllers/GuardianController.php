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
}
