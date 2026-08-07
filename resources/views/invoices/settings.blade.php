<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ⚙️ Paramètres des Frais Scolaires
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-lg">
                    <strong>Succès ! </strong>{{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Nouveau Type de Frais --}}
                <div class="bg-white shadow sm:rounded-xl p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4">➕ Nouveau Type de Frais</h3>
                    <form method="POST" action="{{ route('invoices.fee-types.store') }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Code</label>
                                <input type="text" name="code" required placeholder="INSCRIPT" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm uppercase" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Intitulé</label>
                                <input type="text" name="name" required placeholder="Frais d'inscription" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description (optionnel)</label>
                            <input type="text" name="description" placeholder="Payable une fois à l'inscription" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_recurring" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm" />
                            <span class="text-sm text-gray-700">Frais récurrent (trimestriel)</span>
                        </label>
                        <x-primary-button>Créer le Type de Frais</x-primary-button>
                    </form>

                    {{-- Liste des types existants --}}
                    <div class="mt-5 space-y-2">
                        @foreach ($feeTypes as $ft)
                            <div class="flex justify-between items-center px-3 py-2 bg-gray-50 rounded-lg border border-gray-100">
                                <div>
                                    <span class="font-mono text-xs font-bold text-indigo-600">{{ $ft->code }}</span>
                                    <span class="text-sm font-semibold text-gray-800 ml-2">{{ $ft->name }}</span>
                                </div>
                                <span class="text-[11px] px-2 py-0.5 rounded-full font-semibold {{ $ft->is_recurring ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $ft->is_recurring ? 'Récurrent' : 'Ponctuel' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Grille Tarifaire --}}
                <div class="bg-white shadow sm:rounded-xl p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-1">💰 Grille Tarifaire</h3>
                    <p class="text-xs text-gray-400 mb-4">Année active : {{ $activeYear?->name ?? '–' }}</p>

                    <form method="POST" action="{{ route('invoices.fee-structures.store') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type de Frais</label>
                            <select name="fee_type_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="">-- Choisir --</option>
                                @foreach ($feeTypes as $ft)
                                    <option value="{{ $ft->id }}">{{ $ft->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cycle (optionnel)</label>
                                <select name="cycle_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    <option value="">— Tous les cycles —</option>
                                    @foreach ($cycles as $cycle)
                                        <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Niveau (optionnel)</label>
                                <select name="level_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    <option value="">— Tous les niveaux —</option>
                                    @foreach ($levels as $lvl)
                                        <option value="{{ $lvl->id }}">{{ $lvl->cycle->name }} – {{ $lvl->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Montant (FCFA)</label>
                            <input type="number" name="amount" min="1" required placeholder="25000" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                        </div>
                        <x-primary-button>Enregistrer le Tarif</x-primary-button>
                    </form>

                    {{-- Tarifs existants --}}
                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">Cycle</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">Niveau</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">Montant</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($structures as $s)
                                    <tr>
                                        <td class="px-3 py-2 font-semibold text-gray-800">{{ $s->feeType->name }}</td>
                                        <td class="px-3 py-2 text-gray-600">{{ $s->cycle?->name ?? '—' }}</td>
                                        <td class="px-3 py-2 text-gray-600">{{ $s->level?->name ?? '—' }}</td>
                                        <td class="px-3 py-2 text-right font-bold text-emerald-700">
                                            {{ number_format($s->amount, 0, ',', ' ') }} FCFA
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-3 py-4 text-center text-gray-400 italic">Aucun tarif défini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- Génération de Factures en Masse --}}
            <div class="bg-white shadow sm:rounded-xl p-6">
                <h3 class="text-base font-bold text-gray-900 mb-1">🧾 Génération de Factures en Masse</h3>
                <p class="text-xs text-gray-400 mb-4">
                    Génère automatiquement une facture pour chaque élève inscrit cette année — sans dupliquer.
                </p>
                <form method="POST" action="{{ route('invoices.generate') }}" class="flex items-end gap-4">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700">Type de Frais à Facturer</label>
                        <select name="fee_type_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Sélectionner un type de frais --</option>
                            @foreach ($feeTypes as $ft)
                                <option value="{{ $ft->id }}">{{ $ft->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-5 py-2.5 bg-purple-900 hover:bg-purple-800 text-white font-bold text-sm rounded-lg transition">
                        ⚙️ Générer les Factures
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
