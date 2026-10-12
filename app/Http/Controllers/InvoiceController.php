<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    /**
     * Show form for creating a new single invoice manually.
     */
    public function create(): View|RedirectResponse
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return redirect()->route('invoices.index')->withErrors(['error' => 'Aucune année scolaire active.']);
        }

        $enrollments = Enrollment::with(['student', 'schoolClass.level.cycle'])
            ->where('academic_year_id', $activeYear->id)
            ->where('status', 'VALIDE')
            ->get();

        $feeTypes = FeeType::where('is_active', true)->orderBy('name')->get();

        return view('invoices.create', compact('enrollments', 'feeTypes', 'activeYear'));
    }

    /**
     * Store a newly created single invoice.
     */
    public function store(Request $request): RedirectResponse
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return redirect()->back()->withErrors(['error' => 'Aucune année scolaire active.']);
        }

        $validated = $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id',
            'fee_type_id' => 'required|exists:fee_types,id',
            'amount_due' => 'required|numeric|min:1',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $enrollment = Enrollment::with('student')->findOrFail($validated['enrollment_id']);

        $invoice = Invoice::create([
            'invoice_number' => $this->paymentService->generateInvoiceNumber(),
            'student_id' => $enrollment->student_id,
            'enrollment_id' => $enrollment->id,
            'academic_year_id' => $activeYear->id,
            'fee_type_id' => $validated['fee_type_id'],
            'amount_due' => $validated['amount_due'],
            'amount_paid' => 0,
            'status' => 'IMPAYEE',
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('status', "La facture {$invoice->invoice_number} a été émise avec succès.");
    }

    /**
     * Manage fee types and structures.
     */
    public function settings(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $feeTypeFilter = $request->query('fee_type_id');
        $cycleFilter = $request->query('cycle_id');

        $feeTypes = FeeType::withCount(['structures', 'invoices'])
            ->orderBy('code')
            ->get();

        $cycles = Cycle::with(['levels' => fn ($q) => $q->orderBy('order_index')])
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $levels = Level::with('cycle')
            ->orderBy('order_index')
            ->get();

        $structures = FeeStructure::with(['feeType', 'cycle', 'level'])
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->when($feeTypeFilter, fn ($q) => $q->where('fee_type_id', $feeTypeFilter))
            ->when($cycleFilter, fn ($q) => $q->where('cycle_id', $cycleFilter))
            ->orderBy('fee_type_id')
            ->orderBy('cycle_id')
            ->orderByRaw('level_id IS NULL ASC')
            ->orderBy('level_id')
            ->get();

        $totalFeeTypesCount = $feeTypes->count();
        $activeFeeTypesCount = $feeTypes->where('is_active', true)->count();
        $totalStructuresCount = $structures->count();
        $activeStudentsCount = Enrollment::when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
            ->where('status', 'VALIDE')
            ->count();

        // Nested grouping: FeeType -> Cycle -> Level
        $structuresByFeeType = $structures->groupBy('fee_type_id')->map(function ($typeStructures) {
            return $typeStructures->groupBy('cycle_id');
        });

        return view('invoices.settings', compact(
            'feeTypes',
            'structures',
            'structuresByFeeType',
            'cycles',
            'levels',
            'activeYear',
            'feeTypeFilter',
            'cycleFilter',
            'totalFeeTypesCount',
            'activeFeeTypesCount',
            'totalStructuresCount',
            'activeStudentsCount'
        ));
    }

    /**
     * Store a new fee type.
     */
    public function storeFeeType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:fee_types,code',
            'name' => 'required|string|max:125',
            'description' => 'nullable|string|max:250',
            'is_recurring' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        FeeType::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_recurring' => $request->boolean('is_recurring'),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return redirect()->route('invoices.settings')->with('status', 'Type de frais créé avec succès.');
    }

    /**
     * Update an existing fee type.
     */
    public function updateFeeType(Request $request, FeeType $feeType): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:fee_types,code,'.$feeType->id,
            'name' => 'required|string|max:125',
            'description' => 'nullable|string|max:250',
            'is_recurring' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $feeType->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_recurring' => $request->boolean('is_recurring'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('invoices.settings')->with('status', "Le type de frais « {$feeType->name} » a été mis à jour.");
    }

    /**
     * Toggle active state for a fee type.
     */
    public function toggleActiveFeeType(FeeType $feeType): RedirectResponse
    {
        $feeType->update(['is_active' => ! $feeType->is_active]);

        $statusText = $feeType->is_active ? 'activé' : 'désactivé';

        return redirect()->route('invoices.settings')
            ->with('status', "Le type de frais « {$feeType->name} » est maintenant {$statusText}.");
    }

    /**
     * Delete a fee type.
     */
    public function destroyFeeType(FeeType $feeType): RedirectResponse
    {
        if ($feeType->invoices()->exists()) {
            return redirect()->route('invoices.settings')
                ->withErrors(['error' => "Impossible de supprimer « {$feeType->name} » car des factures existantes y sont rattachées. Vous pouvez le désactiver à la place."]);
        }

        $feeTypeName = $feeType->name;
        $feeType->delete();

        return redirect()->route('invoices.settings')
            ->with('status', "Le type de frais « {$feeTypeName} » a été supprimé.");
    }

    /**
     * Store a fee structure (tariff for a fee type/cycle/level).
     */
    public function storeFeeStructure(Request $request): RedirectResponse
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return redirect()->back()->withErrors(['error' => 'Aucune année scolaire active.']);
        }

        $validated = $request->validate([
            'fee_type_id' => 'required|exists:fee_types,id',
            'cycle_id' => 'nullable|exists:cycles,id',
            'level_id' => 'nullable|exists:levels,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $cycleId = $validated['cycle_id'] ?? null;
        if (! empty($validated['level_id']) && ! $cycleId) {
            $level = Level::find($validated['level_id']);
            $cycleId = $level?->cycle_id;
        }

        FeeStructure::updateOrCreate(
            [
                'fee_type_id' => $validated['fee_type_id'],
                'academic_year_id' => $activeYear->id,
                'cycle_id' => $cycleId,
                'level_id' => $validated['level_id'] ?? null,
            ],
            ['amount' => $validated['amount']]
        );

        return redirect()->route('invoices.settings')->with('status', 'Tarif enregistré avec succès.');
    }

    /**
     * Update an existing fee structure.
     */
    public function updateFeeStructure(Request $request, FeeStructure $feeStructure): RedirectResponse
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return redirect()->back()->withErrors(['error' => 'Aucune année scolaire active.']);
        }

        $validated = $request->validate([
            'fee_type_id' => 'required|exists:fee_types,id',
            'cycle_id' => 'nullable|exists:cycles,id',
            'level_id' => 'nullable|exists:levels,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $cycleId = $validated['cycle_id'] ?? null;
        if (! empty($validated['level_id']) && ! $cycleId) {
            $level = Level::find($validated['level_id']);
            $cycleId = $level?->cycle_id;
        }

        // Check unique duplicate
        $duplicate = FeeStructure::where('fee_type_id', $validated['fee_type_id'])
            ->where('academic_year_id', $feeStructure->academic_year_id)
            ->where('cycle_id', $cycleId)
            ->where('level_id', $validated['level_id'] ?? null)
            ->where('id', '!=', $feeStructure->id)
            ->first();

        if ($duplicate) {
            $duplicate->update(['amount' => $validated['amount']]);
            $feeStructure->delete();

            return redirect()->route('invoices.settings')->with('status', 'Tarif mis à jour et fusionné.');
        }

        $feeStructure->update([
            'fee_type_id' => $validated['fee_type_id'],
            'cycle_id' => $cycleId,
            'level_id' => $validated['level_id'] ?? null,
            'amount' => $validated['amount'],
        ]);

        return redirect()->route('invoices.settings')->with('status', 'Tarif mis à jour avec succès.');
    }

    /**
     * Delete a fee structure.
     */
    public function destroyFeeStructure(FeeStructure $feeStructure): RedirectResponse
    {
        $feeStructure->delete();

        return redirect()->route('invoices.settings')->with('status', 'Tarif supprimé de la grille avec succès.');
    }

    /**
     * List all invoices grouped by fee type and class with filters.
     */
    public function index(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $search = $request->query('search');
        $status = $request->query('status');
        $feeTypeId = $request->query('fee_type_id');
        $cycleId = $request->query('cycle_id');
        $classId = $request->query('class_id');

        $invoicesQuery = Invoice::with(['student', 'feeType', 'enrollment.schoolClass.level.cycle'])
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->when($search, function ($q) use ($search) {
                $q->where('invoice_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('last_name', 'LIKE', "%{$search}%")
                            ->orWhere('first_name', 'LIKE', "%{$search}%")
                            ->orWhere('matricule', 'LIKE', "%{$search}%");
                    });
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($feeTypeId, fn ($q) => $q->where('fee_type_id', $feeTypeId))
            ->when($cycleId, function ($q) use ($cycleId) {
                $q->whereHas('enrollment.schoolClass.level', fn ($lq) => $lq->where('cycle_id', $cycleId));
            })
            ->when($classId, function ($q) use ($classId) {
                $q->whereHas('enrollment', fn ($eq) => $eq->where('class_id', $classId));
            })
            ->orderByDesc('created_at');

        $invoices = $invoicesQuery->paginate(50)->withQueryString();

        // Group invoices in the current collection by FeeType and then by SchoolClass
        $invoicesByFeeTypeAndClass = $invoices->getCollection()
            ->groupBy(fn ($inv) => $inv->feeType?->name ?? 'Frais Divers')
            ->map(function ($feeInvoices) {
                return $feeInvoices->groupBy(function ($inv) {
                    $class = $inv->enrollment?->schoolClass;
                    $className = $class?->name ?? 'Sans classe';
                    $cycle = $class?->level?->cycle?->name ?? '';

                    return $className.($cycle ? " — {$cycle}" : '');
                });
            });

        $feeTypes = FeeType::where('is_active', true)->orderBy('name')->get();
        $cycles = Cycle::where('is_active', true)->orderBy('id')->get();
        $classes = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->when($cycleId, fn ($q) => $q->whereHas('level', fn ($lq) => $lq->where('cycle_id', $cycleId)))
            ->orderBy('name')
            ->get();

        // Summary totals based on active year
        $totalDue = (float) Invoice::where('academic_year_id', $activeYear?->id ?? 0)->sum('amount_due');
        $totalPaid = (float) Invoice::where('academic_year_id', $activeYear?->id ?? 0)->sum('amount_paid');
        $totalRemaining = max(0.0, $totalDue - $totalPaid);
        $recoveryRate = $totalDue > 0 ? round(($totalPaid / $totalDue) * 100, 1) : 0;

        return view('invoices.index', compact(
            'invoices',
            'invoicesByFeeTypeAndClass',
            'feeTypes',
            'cycles',
            'classes',
            'search',
            'status',
            'feeTypeId',
            'cycleId',
            'classId',
            'totalDue',
            'totalPaid',
            'totalRemaining',
            'recoveryRate',
            'activeYear'
        ));
    }

    /**
     * Trigger bulk invoice generation for a fee type.
     */
    public function generate(Request $request): RedirectResponse
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return redirect()->back()->withErrors(['error' => 'Aucune année scolaire active.']);
        }

        $validated = $request->validate([
            'fee_type_id' => 'required|exists:fee_types,id',
        ]);

        $feeType = FeeType::findOrFail($validated['fee_type_id']);
        $count = $this->paymentService->generateInvoicesForYear($feeType, $activeYear);

        return redirect()->route('invoices.index')
            ->with('status', "{$count} facture(s) générée(s) pour « {$feeType->name} ».");
    }

    /**
     * Show a single invoice with its payments.
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load(['student.guardians', 'feeType', 'enrollment.schoolClass.level.cycle', 'payments']);

        return view('invoices.show', compact('invoice'));
    }
}
