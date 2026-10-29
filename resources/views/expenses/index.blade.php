<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-purple-700 font-bold uppercase tracking-wider mb-1">
                    <span>💸 Comptabilité</span>
                    <span>/</span>
                    <span class="text-slate-500">Dépenses & Sorties</span>
                </div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                    💸 Suivi des Dépenses & Sorties de Caisse
                </h2>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    Année scolaire : <span
                        class="font-bold text-purple-900">{{ $activeYear?->name ?? 'Aucune année active' }}</span>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('expenses.settings') }}"
                    class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-xs">
                    ⚙️ Catégories
                </a>
                <a href="{{ route('expenses.create') }}"
                    class="px-3.5 py-2 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white font-black rounded-xl text-xs shadow-md transition flex items-center gap-1.5">
                    ➕ Nouvelle Dépense
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 min-h-screen bg-slate-50">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash --}}
            @if (session('status'))
                <div
                    class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-900 p-4 rounded-xl shadow-xs flex items-center gap-2 font-bold text-sm">
                    <span>✅</span> <span>{{ session('status') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-900 p-4 rounded-xl">
                    <ul class="list-disc list-inside text-xs font-semibold space-y-0.5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- KPI Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total dépenses --}}
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex items-center gap-3">
                    <div
                        class="w-11 h-11 rounded-xl bg-red-100 text-red-700 flex items-center justify-center font-black text-lg shrink-0">
                        💸</div>
                    <div>
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Dépenses</p>
                        <p class="text-lg font-black text-red-700">{{ number_format($totalExpenses, 0, ',', ' ') }}
                            <span class="text-[10px] font-bold text-slate-400">FCFA</span>
                        </p>
                    </div>
                </div>

                {{-- Total recettes --}}
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex items-center gap-3">
                    <div
                        class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-black text-lg shrink-0">
                        💰</div>
                    <div>
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Recettes</p>
                        <p class="text-lg font-black text-emerald-700">{{ number_format($totalReceipts, 0, ',', ' ') }}
                            <span class="text-[10px] font-bold text-slate-400">FCFA</span>
                        </p>
                    </div>
                </div>

                {{-- Solde --}}
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex items-center gap-3">
                    <div
                        class="w-11 h-11 rounded-xl {{ $balance >= 0 ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700' }} flex items-center justify-center font-black text-lg shrink-0">
                        {{ $balance >= 0 ? '📈' : '📉' }}
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Solde Net</p>
                        <p class="text-lg font-black {{ $balance >= 0 ? 'text-blue-700' : 'text-red-700' }}">
                            {{ $balance >= 0 ? '+' : '' }}{{ number_format($balance, 0, ',', ' ') }} <span
                                class="text-[10px] font-bold text-slate-400">FCFA</span>
                        </p>
                    </div>
                </div>

                {{-- Ce mois --}}
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex items-center gap-3">
                    <div
                        class="w-11 h-11 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-black text-lg shrink-0">
                        📅</div>
                    <div>
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Ce Mois-ci</p>
                        <p class="text-lg font-black text-amber-700">
                            {{ number_format($expensesThisMonth, 0, ',', ' ') }} <span
                                class="text-[10px] font-bold text-slate-400">FCFA</span></p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                {{-- Répartition par catégorie --}}
                @if ($expensesByCategory->isNotEmpty())
                    <div class="lg:col-span-1 bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
                        <h3
                            class="text-sm font-black text-purple-950 uppercase tracking-wide mb-4 flex items-center gap-2">
                            🗂️ Par Catégorie
                        </h3>
                        <div class="space-y-3">
                            @foreach ($expensesByCategory as $cat)
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-xs font-bold text-slate-700">{{ $cat['name'] }}</span>
                                        <span
                                            class="text-xs font-black text-slate-900">{{ number_format($cat['total'], 0, ',', ' ') }}</span>
                                    </div>
                                    @php $pct = $totalExpenses > 0 ? round(($cat['total'] / $totalExpenses) * 100) : 0; @endphp
                                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-2 rounded-full"
                                            style="width: {{ $pct }}%; background-color: {{ $cat['color'] }};">
                                        </div>
                                    </div>
                                    <span class="text-[10px] text-slate-400">{{ $pct }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Table des dépenses --}}
                <div
                    class="{{ $expensesByCategory->isNotEmpty() ? 'lg:col-span-3' : 'lg:col-span-4' }} bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

                    {{-- Filters --}}
                    <div class="px-5 py-4 border-b border-slate-100">
                        <form method="GET" action="{{ route('expenses.index') }}"
                            class="flex flex-wrap gap-2 items-center">
                            <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher…"
                                class="text-xs font-bold rounded-xl border-purple-300 focus:border-purple-900 focus:ring-purple-900 py-1.5 shadow-2xs w-40">
                            <select name="category_id" onchange="this.form.submit()"
                                class="text-xs font-bold rounded-xl border-purple-300 focus:border-purple-900 focus:ring-purple-900 py-1.5 shadow-2xs">
                                <option value="">Toutes catégories</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <select name="payment_method" onchange="this.form.submit()"
                                class="text-xs font-bold rounded-xl border-purple-300 focus:border-purple-900 focus:ring-purple-900 py-1.5 shadow-2xs">
                                <option value="">Tous modes</option>
                                @foreach (\App\Models\Expense::paymentMethods() as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ $paymentMethod === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <select name="month" onchange="this.form.submit()"
                                class="text-xs font-bold rounded-xl border-purple-300 focus:border-purple-900 focus:ring-purple-900 py-1.5 shadow-2xs">
                                <option value="">Tous les mois</option>
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->locale('fr')->isoFormat('MMMM') }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($search || $categoryId || $month || $paymentMethod)
                                <button type="submit"
                                    class="text-xs font-bold px-3 py-1.5 bg-purple-950 text-white rounded-xl hover:bg-purple-900 transition">🔍</button>
                                <a href="{{ route('expenses.index') }}"
                                    class="text-xs font-black text-purple-900 hover:text-red-600 underline">Réinitialiser</a>
                            @else
                                <button type="submit"
                                    class="text-xs font-bold px-3 py-1.5 bg-purple-950 text-white rounded-xl hover:bg-purple-900 transition">🔍</button>
                            @endif
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr
                                    class="bg-gradient-to-r from-red-700 to-rose-800 text-white font-black uppercase text-[10px] tracking-wider">

                                    <th class="py-3 px-4">Date</th>
                                    <th class="py-3 px-4">Intitulé / Fournisseur</th>
                                    <th class="py-3 px-4">Catégorie</th>
                                    <th class="py-3 px-4">Mode</th>
                                    <th class="py-3 px-4 text-right">Montant</th>
                                    <th class="py-3 px-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-800 bg-white">
                                @forelse ($expenses as $expense)
                                    <tr class="hover:bg-red-50/30 transition">

                                        <td class="py-3 px-4 whitespace-nowrap font-bold text-slate-600">
                                            {{ $expense->expense_date->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-900">{{ $expense->label }}</div>
                                            @if ($expense->supplier)
                                                <div class="text-[10px] text-slate-500">{{ $expense->supplier }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            @if ($expense->category)
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black text-white"
                                                    style="background-color: {{ $expense->category->color }};">
                                                    {{ $expense->category->name }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-full text-[10px] font-bold">
                                                {{ \App\Models\Expense::paymentMethods()[$expense->payment_method] ?? $expense->payment_method }}
                                            </span>
                                        </td>
                                        <td
                                            class="py-3 px-4 text-right whitespace-nowrap font-black text-red-700 text-sm">
                                            {{ number_format($expense->amount, 0, ',', ' ') }}
                                            <span class="text-[10px] text-slate-400 font-bold">FCFA</span>
                                        </td>
                                        <td class="py-3 px-4 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <a href="{{ route('expenses.show', $expense) }}"
                                                    class="p-1.5 bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-lg transition text-xs shadow-2xs"
                                                    title="Voir">👁️</a>
                                                <a href="{{ route('expenses.edit', $expense) }}"
                                                    class="p-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-lg transition text-xs shadow-2xs"
                                                    title="Modifier">✏️</a>
                                                <form method="POST"
                                                    action="{{ route('expenses.destroy', $expense) }}"
                                                    onsubmit="return confirm('Supprimer cette dépense ?');"
                                                    class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="p-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg transition text-xs shadow-2xs"
                                                        title="Supprimer">🗑️</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-12 text-center text-slate-400">
                                            <div class="text-3xl mb-2">💸</div>
                                            <p class="font-bold text-sm">Aucune dépense enregistrée</p>
                                            <p class="text-xs mt-1">Utilisez le bouton « Nouvelle Dépense » pour
                                                commencer.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($expenses->hasPages())
                        <div class="px-5 py-4 border-t border-slate-100">
                            {{ $expenses->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
