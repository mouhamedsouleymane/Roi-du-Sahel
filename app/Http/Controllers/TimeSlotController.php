<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\TimeSlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimeSlotController extends Controller
{
    /**
     * Display a listing of the time slots.
     */
    public function index(Request $request): View
    {
        $cycleId = $request->query('cycle_id');
        $search = $request->query('search');
        $status = $request->query('status');

        $cycles = Cycle::where('is_active', true)->orderBy('id')->get();

        $query = TimeSlot::with('cycle')
            ->when($cycleId, fn ($q) => $q->where('cycle_id', $cycleId))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('label', 'like', "%{$search}%")
                        ->orWhere('start_time', 'like', "%{$search}%")
                        ->orWhere('end_time', 'like', "%{$search}%");
                });
            })
            ->when($status !== null && $status !== '', function ($q) use ($status) {
                $q->where('is_active', (bool) $status);
            })
            ->ordered();

        $timeSlots = $query->paginate(25)->withQueryString();

        $totalCount = TimeSlot::count();
        $activeCount = TimeSlot::where('is_active', true)->count();
        $pauseCount = TimeSlot::where('is_pause', true)->count();

        return view('time_slots.index', compact(
            'timeSlots',
            'cycles',
            'cycleId',
            'search',
            'status',
            'totalCount',
            'activeCount',
            'pauseCount'
        ));
    }

    /**
     * Show the form for creating a new time slot.
     */
    public function create(): View
    {
        $cycles = Cycle::where('is_active', true)->orderBy('id')->get();
        $nextOrder = (TimeSlot::max('order_index') ?? 0) + 1;

        return view('time_slots.create', compact('cycles', 'nextOrder'));
    }

    /**
     * Store a newly created time slot in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
            'label' => 'nullable|string|max:100',
            'start_time' => 'required',
            'end_time' => 'required',
            'cycle_id' => 'nullable|exists:cycles,id',
            'order_index' => 'nullable|integer|min:0',
            'is_pause' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $start = substr($validated['start_time'], 0, 5);
        $end = substr($validated['end_time'], 0, 5);

        if ($start >= $end) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['end_time' => "L'heure de fin ({$end}) doit être strictement postérieure à l'heure de début ({$start})."]);
        }

        $label = ! empty(trim($validated['label'] ?? ''))
            ? trim($validated['label'])
            : "{$start} - {$end}";

        $activeYear = AcademicYear::getActive();

        TimeSlot::create([
            'name' => ($validated['name'] ?? null) ?: null,
            'label' => $label,
            'start_time' => strlen($validated['start_time']) === 5 ? "{$validated['start_time']}:00" : $validated['start_time'],
            'end_time' => strlen($validated['end_time']) === 5 ? "{$validated['end_time']}:00" : $validated['end_time'],
            'cycle_id' => ($validated['cycle_id'] ?? null) ?: null,
            'academic_year_id' => $activeYear?->id,
            'order_index' => (int) ($validated['order_index'] ?? 0),
            'is_pause' => (bool) ($validated['is_pause'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()->route('time-slots.index')
            ->with('status', "La plage horaire « {$label} » a été créée avec succès.");
    }

    /**
     * Show the form for editing the specified time slot.
     */
    public function edit(TimeSlot $timeSlot): View
    {
        $cycles = Cycle::where('is_active', true)->orderBy('id')->get();

        return view('time_slots.edit', compact('timeSlot', 'cycles'));
    }

    /**
     * Update the specified time slot in storage.
     */
    public function update(Request $request, TimeSlot $timeSlot): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
            'label' => 'nullable|string|max:100',
            'start_time' => 'required',
            'end_time' => 'required',
            'cycle_id' => 'nullable|exists:cycles,id',
            'order_index' => 'nullable|integer|min:0',
            'is_pause' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $start = substr($validated['start_time'], 0, 5);
        $end = substr($validated['end_time'], 0, 5);

        if ($start >= $end) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['end_time' => "L'heure de fin ({$end}) doit être strictement postérieure à l'heure de début ({$start})."]);
        }

        $label = ! empty(trim($validated['label'] ?? ''))
            ? trim($validated['label'])
            : "{$start} - {$end}";

        $timeSlot->update([
            'name' => ($validated['name'] ?? null) ?: null,
            'label' => $label,
            'start_time' => strlen($validated['start_time']) === 5 ? "{$validated['start_time']}:00" : $validated['start_time'],
            'end_time' => strlen($validated['end_time']) === 5 ? "{$validated['end_time']}:00" : $validated['end_time'],
            'cycle_id' => ($validated['cycle_id'] ?? null) ?: null,
            'order_index' => (int) ($validated['order_index'] ?? 0),
            'is_pause' => (bool) ($validated['is_pause'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()->route('time-slots.index')
            ->with('status', "La plage horaire « {$label} » a été modifiée avec succès.");
    }

    /**
     * Toggle the active status of the specified time slot.
     */
    public function toggleStatus(TimeSlot $timeSlot): RedirectResponse
    {
        $timeSlot->update(['is_active' => ! $timeSlot->is_active]);

        $state = $timeSlot->is_active ? 'activée' : 'désactivée';

        return redirect()->route('time-slots.index')
            ->with('status', "La plage horaire « {$timeSlot->label} » a été {$state}.");
    }

    /**
     * Remove the specified time slot from storage.
     */
    public function destroy(TimeSlot $timeSlot): RedirectResponse
    {
        $label = $timeSlot->label;
        $linkedCount = $timeSlot->schedules()->count();

        TimeSlot::destroy($timeSlot->id);

        $msg = "La plage horaire « {$label} » a été supprimée.";
        if ($linkedCount > 0) {
            $msg .= " ({$linkedCount} créneau(x) d'emploi du temps y étaient rattaché(s)).";
        }

        return redirect()->route('time-slots.index')
            ->with('status', $msg);
    }
}
