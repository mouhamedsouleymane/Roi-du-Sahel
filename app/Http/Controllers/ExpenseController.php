<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    /**
     * List expenses with filters and financial summary.
     */
    public function index(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $categoryId = $request->query('category_id');
        $month = $request->query('month');
        $paymentMethod = $request->query('payment_method');
        $search = $request->query('search');

        $expenses = Expense::with(['category', 'user'])
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->when($categoryId, fn ($q) => $q->where('expense_category_id', $categoryId))
            ->when($month, fn ($q) => $q->whereMonth('expense_date', $month))
            ->when($paymentMethod, fn ($q) => $q->where('payment_method', $paymentMethod))
            ->when($search, fn ($q) => $q->where('label', 'like', "%{$search}%")
                ->orWhere('reference', 'like', "%{$search}%")
                ->orWhere('supplier', 'like', "%{$search}%"))
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $categories = ExpenseCategory::orderBy('name')->get();

        // KPI financiers
        $totalExpenses = (float) Expense::where('academic_year_id', $activeYear?->id ?? 0)->sum('amount');
        $totalReceipts = (float) Invoice::where('academic_year_id', $activeYear?->id ?? 0)->sum('amount_paid');
        $balance = $totalReceipts - $totalExpenses;

        // Dépenses par catégorie (pour le résumé)
        $expensesByCategory = Expense::with('category')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->selectRaw('expense_category_id, SUM(amount) as total')
            ->groupBy('expense_category_id')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->category?->name ?? 'Inconnu',
                'color' => $row->category?->color ?? '#6366f1',
                'total' => (float) $row->total,
            ])
            ->sortByDesc('total')
            ->values();

        $expensesThisMonth = (float) Expense::where('academic_year_id', $activeYear?->id ?? 0)
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        return view('expenses.index', compact(
            'expenses',
            'categories',
            'activeYear',
            'categoryId',
            'month',
            'paymentMethod',
            'search',
            'totalExpenses',
            'totalReceipts',
            'balance',
            'expensesByCategory',
            'expensesThisMonth'
        ));
    }

    /**
     * Show the form to create a new expense.
     */
    public function create(): View|RedirectResponse
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return redirect()->route('expenses.index')->withErrors(['error' => 'Aucune année scolaire active.']);
        }

        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();

        return view('expenses.create', compact('categories', 'activeYear'));
    }

    /**
     * Store a new expense.
     */
    public function store(Request $request): RedirectResponse
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return redirect()->back()->withErrors(['error' => 'Aucune année scolaire active.']);
        }

        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'label' => 'required|string|max:200',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:'.implode(',', array_keys(Expense::paymentMethods())),
            'supplier' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:1000',
        ]);

        Expense::create([
            'reference' => Expense::generateReference(),
            'expense_category_id' => $validated['expense_category_id'],
            'academic_year_id' => $activeYear->id,
            'user_id' => Auth::id(),
            'label' => $validated['label'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'payment_method' => $validated['payment_method'],
            'supplier' => $validated['supplier'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('expenses.index')
            ->with('status', "Dépense « {$validated['label']} » enregistrée avec succès.");
    }

    /**
     * Show a single expense.
     */
    public function show(Expense $expense): View
    {
        $expense->load(['category', 'academicYear', 'user']);

        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the edit form.
     */
    public function edit(Expense $expense): View
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();

        return view('expenses.edit', compact('expense', 'categories'));
    }

    /**
     * Update an expense.
     */
    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'label' => 'required|string|max:200',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:'.implode(',', array_keys(Expense::paymentMethods())),
            'supplier' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:1000',
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')
            ->with('status', "Dépense « {$expense->label} » mise à jour.");
    }

    /**
     * Delete an expense.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        $label = $expense->label;
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('status', "Dépense « {$label} » supprimée.");
    }

    // ──────────────────────────────────────────────────
    // Catégories de Dépenses (CRUD inline)
    // ──────────────────────────────────────────────────

    /**
     * Store a new expense category.
     */
    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:30|unique:expense_categories,code',
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        ExpenseCategory::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#6366f1',
            'is_active' => true,
        ]);

        return redirect()->route('expenses.settings')
            ->with('status', "Catégorie « {$validated['name']} » créée.");
    }

    /**
     * Update an expense category.
     */
    public function updateCategory(Request $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:30|unique:expense_categories,code,'.$expenseCategory->id,
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        $expenseCategory->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? $expenseCategory->color,
        ]);

        return redirect()->route('expenses.settings')
            ->with('status', "Catégorie « {$expenseCategory->name} » mise à jour.");
    }

    /**
     * Toggle active state for a category.
     */
    public function toggleCategory(ExpenseCategory $expenseCategory): RedirectResponse
    {
        $expenseCategory->update(['is_active' => ! $expenseCategory->is_active]);
        $status = $expenseCategory->is_active ? 'activée' : 'désactivée';

        return redirect()->route('expenses.settings')
            ->with('status', "Catégorie « {$expenseCategory->name} » {$status}.");
    }

    /**
     * Delete a category.
     */
    public function destroyCategory(ExpenseCategory $expenseCategory): RedirectResponse
    {
        if ($expenseCategory->expenses()->exists()) {
            return redirect()->route('expenses.settings')
                ->withErrors(['error' => "Impossible de supprimer « {$expenseCategory->name} » car des dépenses y sont rattachées."]);
        }

        $name = $expenseCategory->name;
        $expenseCategory->delete();

        return redirect()->route('expenses.settings')
            ->with('status', "Catégorie « {$name} » supprimée.");
    }

    /**
     * Settings page for expense categories.
     */
    public function settings(): View
    {
        $categories = ExpenseCategory::withCount('expenses')->orderBy('name')->get();

        return view('expenses.settings', compact('categories'));
    }
}
