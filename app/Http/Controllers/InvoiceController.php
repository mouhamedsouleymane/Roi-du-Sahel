<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Level;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    /**
     * Manage fee types and structures.
     */
    public function settings(): View
    {
        $activeYear   = AcademicYear::getActive();
        $feeTypes     = FeeType::orderBy('code')->get();
        $cycles       = Cycle::orderBy('id')->get();
        $levels       = Level::with('cycle')->orderBy('id')->get();

        $structures = FeeStructure::with(['feeType', 'cycle', 'level'])
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->orderBy('fee_type_id')
            ->get();

        return view('invoices.settings', compact('feeTypes', 'structures', 'cycles', 'levels', 'activeYear'));
    }

    /**
     * Store a new fee type.
     */
    public function storeFeeType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code'         => 'required|string|max:50|unique:fee_types,code',
            'name'         => 'required|string|max:125',
            'description'  => 'nullable|string|max:250',
            'is_recurring' => 'boolean',
        ]);

        FeeType::create([
            'code'         => strtoupper($validated['code']),
            'name'         => $validated['name'],
            'description'  => $validated['description'] ?? null,
            'is_recurring' => $request->boolean('is_recurring'),
        ]);

        return redirect()->route('invoices.settings')->with('status', 'Type de frais créé avec succès.');
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
            'cycle_id'    => 'nullable|exists:cycles,id',
            'level_id'    => 'nullable|exists:levels,id',
            'amount'      => 'required|numeric|min:1',
        ]);

        FeeStructure::updateOrCreate(
            [
                'fee_type_id'      => $validated['fee_type_id'],
                'academic_year_id' => $activeYear->id,
                'cycle_id'         => $validated['cycle_id'] ?? null,
                'level_id'         => $validated['level_id'] ?? null,
            ],
            ['amount' => $validated['amount']]
        );

        return redirect()->route('invoices.settings')->with('status', 'Tarif enregistré avec succès.');
    }

    /**
     * List all invoices with filters.
     */
    public function index(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $search     = $request->query('search');
        $status     = $request->query('status');

        $invoices = Invoice::with(['student', 'feeType', 'enrollment.schoolClass.level.cycle'])
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->when($search, function ($q) use ($search) {
                $q->where('invoice_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('last_name', 'LIKE', "%{$search}%")
                         ->orWhere('matricule', 'LIKE', "%{$search}%");
                  });
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20);

        $feeTypes = FeeType::where('is_active', true)->get();

        // Summary totals
        $totalDue  = Invoice::where('academic_year_id', $activeYear?->id ?? 0)->sum('amount_due');
        $totalPaid = Invoice::where('academic_year_id', $activeYear?->id ?? 0)->sum('amount_paid');

        return view('invoices.index', compact(
            'invoices', 'feeTypes', 'search', 'status', 'totalDue', 'totalPaid', 'activeYear'
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
        $count   = $this->paymentService->generateInvoicesForYear($feeType, $activeYear);

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
