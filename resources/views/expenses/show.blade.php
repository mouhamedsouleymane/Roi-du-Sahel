<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4 justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-purple-700 font-bold uppercase tracking-wider mb-1">
                    <a href="{{ route('expenses.index') }}" class="hover:underline">💸 Dépenses</a>
                    <span>/</span><span class="text-slate-500">{{ $expense->reference }}</span>
                </div>
                <h2 class="font-black text-2xl text-purple-950">{{ $expense->label }}</h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('expenses.edit', $expense) }}"
                   class="px-3.5 py-2 bg-amber-100 hover:bg-amber-200 text-amber-800 font-bold text-xs rounded-xl transition flex items-center gap-1.5">✏️ Modifier</a>
                <a href="{{ route('expenses.index') }}"
                   class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5">← Retour</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 min-h-screen bg-slate-50">
        <div class="w-full max-w-2xl mx-auto px-4 sm:px-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-5">

                {{-- En-tête coloré --}}
                <div class="bg-gradient-to-r from-red-600 to-rose-700 rounded-xl p-5 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-red-200 uppercase tracking-wider">Référence</p>
                            <p class="font-mono font-black text-lg">{{ $expense->reference }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-red-200 uppercase tracking-wider">Montant</p>
                            <p class="font-black text-2xl">{{ number_format($expense->amount, 0, ',', ' ') }} <span class="text-sm font-bold text-red-200">FCFA</span></p>
                        </div>
                    </div>
                </div>

                {{-- Détails --}}
                <dl class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <dt class="font-bold text-slate-500 uppercase tracking-wider">Date</dt>
                        <dd class="font-black text-slate-900 mt-0.5">{{ $expense->expense_date->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-slate-500 uppercase tracking-wider">Catégorie</dt>
                        <dd class="mt-0.5">
                            @if ($expense->category)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black text-white"
                                      style="background-color: {{ $expense->category->color }};">
                                    {{ $expense->category->name }}
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="font-bold text-slate-500 uppercase tracking-wider">Mode de Paiement</dt>
                        <dd class="font-bold text-slate-900 mt-0.5">{{ \App\Models\Expense::paymentMethods()[$expense->payment_method] ?? $expense->payment_method }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-slate-500 uppercase tracking-wider">Année Scolaire</dt>
                        <dd class="font-bold text-slate-900 mt-0.5">{{ $expense->academicYear->name }}</dd>
                    </div>
                    @if ($expense->supplier)
                        <div class="col-span-2">
                            <dt class="font-bold text-slate-500 uppercase tracking-wider">Fournisseur / Bénéficiaire</dt>
                            <dd class="font-bold text-slate-900 mt-0.5">{{ $expense->supplier }}</dd>
                        </div>
                    @endif
                    <div class="col-span-2">
                        <dt class="font-bold text-slate-500 uppercase tracking-wider">Saisi par</dt>
                        <dd class="font-bold text-slate-900 mt-0.5">{{ $expense->user->name }}</dd>
                    </div>
                    @if ($expense->notes)
                        <div class="col-span-2">
                            <dt class="font-bold text-slate-500 uppercase tracking-wider">Notes</dt>
                            <dd class="text-slate-700 mt-0.5 bg-slate-50 rounded-xl p-3">{{ $expense->notes }}</dd>
                        </div>
                    @endif
                </dl>

                {{-- Actions --}}
                <div class="pt-2 flex gap-3 border-t border-slate-100">
                    <a href="{{ route('expenses.edit', $expense) }}"
                       class="px-5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs rounded-xl transition">✏️ Modifier</a>
                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                          onsubmit="return confirm('Supprimer définitivement cette dépense ?');" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-5 py-2 bg-red-50 hover:bg-red-100 text-red-700 font-bold text-xs rounded-xl transition">🗑️ Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
