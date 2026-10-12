<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use App\Models\Level;
use App\Models\Subject;
use Illuminate\View\View;

class LevelController extends Controller
{
    /**
     * Display the specified level and its subjects/coefficients & classes.
     */
    public function show(Cycle $cycle, Level $level): View
    {
        $level->load([
            'cycle',
            'classes' => fn ($q) => $q->withCount('enrollments'),
            'coefficients.subject',
        ]);

        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        return view('levels.show', compact('cycle', 'level', 'subjects'));
    }
}
