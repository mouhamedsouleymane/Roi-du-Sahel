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
}
