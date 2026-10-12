<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4 justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-purple-700 font-bold uppercase tracking-wider mb-1">
                    <a href="{{ route('expenses.index') }}" class="hover:underline">💸 Dépenses</a>
                    <span>/</span><span class="text-slate-500">Modifier</span>
                </div>
                <h2 class="font-black text-2xl text-purple-950">✏️ Modifier la Dépense</h2>
                <p class="text-xs text-slate-500 mt-0.5 font-mono">{{ $expense->reference }}</p>
            </div>
            <a href="{{ route('expenses.index') }}"
               class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                ← Retour
            </a>
        </div>
    </x-slot>

    <div class="py-6 min-h-screen bg-slate-50">
        <div class="w-full max-w-2xl mx-auto px-4 sm:px-6">

            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-900 p-4 rounded-xl mb-4">
                    <ul class="list-disc list-inside text-xs font-semibold space-y-0.5">
                        @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <form method="POST" action="{{ route('expenses.update', $expense) }}" class="space-y-4">
                    @csrf @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Intitulé <span class="text-red-500">*</span></label>
                            <input type="text" name="label" value="{{ old('label', $expense->label) }}" required
                                   class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catégorie <span class="text-red-500">*</span></label>
                            <select name="expense_category_id" required
                                    class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs">
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('expense_category_id', $expense->expense_category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required
                                   class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Montant (FCFA) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="number" name="amount" value="{{ old('amount', (int) $expense->amount) }}" required min="1"
                                       class="w-full text-xs font-black rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs pr-14" />
                                <span class="absolute right-3 top-2.5 text-[10px] font-black text-slate-400 pointer-events-none">FCFA</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Mode de Paiement <span class="text-red-500">*</span></label>
                            <select name="payment_method" required
                                    class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs">
                                @foreach (\App\Models\Expense::paymentMethods() as $key => $lbl)
                                    <option value="{{ $key }}" {{ old('payment_method', $expense->payment_method) === $key ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Fournisseur / Bénéficiaire</label>
                            <input type="text" name="supplier" value="{{ old('supplier', $expense->supplier) }}"
                                   class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs" />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Notes</label>
                            <textarea name="notes" rows="3"
                                      class="w-full text-xs rounded-xl border-slate-300 focus:border-purple-900 focus:ring-purple-900 shadow-xs">{{ old('notes', $expense->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="pt-2 flex gap-3">
                        <button type="submit"
                                class="flex-1 py-2.5 bg-purple-950 hover:bg-purple-900 text-white font-black text-xs rounded-xl shadow-md transition flex items-center justify-center gap-1.5">
                            💾 Mettre à Jour
                        </button>
                        <a href="{{ route('expenses.index') }}"
                           class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center justify-center">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
