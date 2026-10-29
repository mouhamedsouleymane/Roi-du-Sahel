<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                    ➕ {{ __('Émettre une Nouvelle Facture') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Année scolaire active : <span class="font-extrabold text-purple-900">{{ $activeYear->name }}</span>
                </p>
            </div>
            <a href="{{ route('invoices.index') }}" class="text-xs font-black text-purple-900 hover:underline flex items-center gap-1">
                ← Retour aux factures
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-2xl font-bold text-sm shadow-xs">
                    ⚠️ {{ $errors->first() }}
                </div>
            @endif

            <div class="bg-white shadow-xl rounded-3xl p-8 border border-slate-200/80">
                <form method="POST" action="{{ route('invoices.store') }}" class="space-y-6">
                    @csrf

                    {{-- Sélection de l'élève inscrit --}}
                    <div>
                        <label for="enrollment_id" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                            Élève & Classe *
                        </label>
                        <select id="enrollment_id" name="enrollment_id" required
                                class="w-full text-xs font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 py-3 px-4">
                            <option value="">-- Sélectionner l'élève concerné --</option>
                            @foreach ($enrollments as $e)
                                <option value="{{ $e->id }}" {{ old('enrollment_id') == $e->id ? 'selected' : '' }}>
                                    🎓 {{ $e->student->last_name }} {{ $e->student->first_name }} (Matricule : {{ $e->student->matricule }}) — Classe : {{ $e->schoolClass->name }} ({{ $e->schoolClass->level->cycle->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('enrollment_id') <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Type de Frais --}}
                        <div>
                            <label for="fee_type_id" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Type de Frais *
                            </label>
                            <select id="fee_type_id" name="fee_type_id" required
                                    class="w-full text-xs font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 py-3 px-4">
                                <option value="">-- Choisir le type de frais --</option>
                                @foreach ($feeTypes as $ft)
                                    <option value="{{ $ft->id }}" {{ old('fee_type_id') == $ft->id ? 'selected' : '' }}>
                                        💳 {{ $ft->name }} ({{ $ft->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('fee_type_id') <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Montant Dû --}}
                        <div>
                            <label for="amount_due" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Montant Dû (FCFA) *
                            </label>
                            <input type="number" id="amount_due" name="amount_due" min="1" step="100" required
                                   value="{{ old('amount_due') }}" placeholder="ex: 25000"
                                   class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 py-3 px-4" />
                            @error('amount_due') <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Date d'échéance --}}
                        <div>
                            <label for="due_date" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Date Limite de Paiement (Échéance)
                            </label>
                            <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}"
                                   class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 py-3 px-4" />
                            @error('due_date') <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Notes & Observations --}}
                        <div>
                            <label for="notes" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Notes & Références (optionnel)
                            </label>
                            <input type="text" id="notes" name="notes" value="{{ old('notes') }}" placeholder="ex: Frais de tenue scolaire ou sortie"
                                   class="w-full text-sm font-medium border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 py-3 px-4" />
                            @error('notes') <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                        <a href="{{ route('invoices.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Annuler
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-purple-950 hover:bg-purple-900 text-white text-xs font-black rounded-xl shadow-md transition flex items-center gap-2">
                            💾 Émettre la Facture
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
