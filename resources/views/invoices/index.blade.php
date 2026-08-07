<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                💳 Factures & Gestion Comptable
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('invoices.settings') }}" class="px-4 py-2.5 text-xs font-black bg-purple-900 text-white rounded-xl hover:bg-purple-800 transition-all shadow-md">
                    ⚙️ Paramètres des frais & Tarifs
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            @if (session('status'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-2xl shadow-xs">
                    <strong>Succès ! </strong>{{ session('status') }}
                </div>
            @endif

            {{-- KPI Financier --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 flex items-center gap-4 hover:-translate-y-1 transition duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-900 flex items-center justify-center text-2xl flex-shrink-0">🧾</div>
                    <div>
                        <div class="text-2xl font-black text-blue-900">{{ number_format($totalDue, 0, ',', ' ') }} FCFA</div>
                        <div class="text-xs text-slate-500 font-extrabold">Total frais dus</div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-emerald-200/80 p-5 flex items-center gap-4 hover:-translate-y-1 transition duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-900 flex items-center justify-center text-2xl flex-shrink-0">✅</div>
                    <div>
                        <div class="text-2xl font-black text-emerald-900">{{ number_format($totalPaid, 0, ',', ' ') }} FCFA</div>
                        <div class="text-xs text-slate-500 font-extrabold">Total recouvrés</div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-red-200/80 p-5 flex items-center gap-4 hover:-translate-y-1 transition duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-red-100 text-red-900 flex items-center justify-center text-2xl flex-shrink-0">⚠️</div>
                    <div>
                        <div class="text-2xl font-black text-red-900">{{ number_format($totalDue - $totalPaid, 0, ',', ' ') }} FCFA</div>
                        <div class="text-xs text-slate-500 font-extrabold">Reste à recouvrer</div>
                    </div>
                </div>
            </div>

            {{-- Filtres --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5">
                <form method="GET" action="{{ route('invoices.index') }}" class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-bold text-slate-600 mb-1">Recherche</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Matricule, nom, N° facture…"
                               class="block w-full text-xs border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Statut</label>
                        <select name="status" class="block text-xs border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 font-bold">
                            <option value="">Tous les statuts</option>
                            <option value="IMPAYEE"  {{ $status === 'IMPAYEE'  ? 'selected' : '' }}>Impayée</option>
                            <option value="PARTIELLE"{{ $status === 'PARTIELLE'? 'selected' : '' }}>Partielle</option>
                            <option value="PAYEE"    {{ $status === 'PAYEE'    ? 'selected' : '' }}>Payée</option>
                            <option value="ANNULEE"  {{ $status === 'ANNULEE'  ? 'selected' : '' }}>Annulée</option>
                        </select>
                    </div>
                    <button type="submit" class="px-5 py-2.5 bg-purple-950 text-white text-xs font-black rounded-xl hover:bg-purple-900 transition">
                        Filtrer
                    </button>
                    @if ($search || $status)
                        <a href="{{ route('invoices.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200 transition">
                            Réinitialiser
                        </a>
                    @endif
                </form>
            </div>

            {{-- Table des factures --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3.5 text-left text-xs font-black text-slate-500 uppercase">N° Facture</th>
                                <th class="px-5 py-3.5 text-left text-xs font-black text-slate-500 uppercase">Élève</th>
                                <th class="px-5 py-3.5 text-left text-xs font-black text-slate-500 uppercase">Classe</th>
                                <th class="px-5 py-3.5 text-left text-xs font-black text-slate-500 uppercase">Type de Frais</th>
                                <th class="px-5 py-3.5 text-right text-xs font-black text-slate-500 uppercase">Dû</th>
                                <th class="px-5 py-3.5 text-right text-xs font-black text-slate-500 uppercase">Payé</th>
                                <th class="px-5 py-3.5 text-right text-xs font-black text-slate-500 uppercase">Reste</th>
                                <th class="px-5 py-3.5 text-center text-xs font-black text-slate-500 uppercase">Statut</th>
                                <th class="px-5 py-3.5 text-right text-xs font-black text-slate-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($invoices as $inv)
                                @php
                                    $statusConfig = [
                                        'PAYEE'     => ['bg-emerald-100 text-emerald-800', '✅ Payée'],
                                        'PARTIELLE' => ['bg-amber-100 text-amber-800',     '⚡ Partielle'],
                                        'IMPAYEE'   => ['bg-red-100 text-red-800',         '❌ Impayée'],
                                        'ANNULEE'   => ['bg-slate-100 text-slate-600',        '🚫 Annulée'],
                                    ];
                                    [$badgeClass, $label] = $statusConfig[$inv->status] ?? ['bg-slate-100 text-slate-600', $inv->status];
                                    $remaining = (float)$inv->amount_due - (float)$inv->amount_paid;
                                @endphp
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-5 py-3.5 text-xs font-mono font-black text-purple-900">
                                        {{ $inv->invoice_number }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="text-sm font-black text-slate-900">{{ $inv->student->last_name }} {{ $inv->student->first_name }}</div>
                                        <div class="text-xs text-slate-400 font-mono font-bold">{{ $inv->student->matricule }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs font-semibold text-slate-700">
                                        {{ $inv->enrollment->schoolClass->name }}
                                        <div class="text-[11px] text-slate-400 font-normal">{{ $inv->enrollment->schoolClass->level->cycle->name }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs font-bold text-slate-800">
                                        {{ $inv->feeType->name }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-xs font-black text-slate-900">
                                        {{ number_format($inv->amount_due, 0, ',', ' ') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-xs font-black text-emerald-700">
                                        {{ number_format($inv->amount_paid, 0, ',', ' ') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-xs font-black {{ $remaining > 0 ? 'text-red-600' : 'text-slate-400' }}">
                                        {{ number_format($remaining, 0, ',', ' ') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="px-2.5 py-1 text-xs font-black rounded-full {{ $badgeClass }}">
                                            {{ $label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <a href="{{ route('invoices.show', $inv) }}" class="text-purple-900 hover:text-purple-700 text-xs font-black">
                                            Détail →
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-5 py-8 text-center text-sm text-slate-400 italic">
                                        Aucune facture trouvée. Générez-en depuis
                                        <a href="{{ route('invoices.settings') }}" class="text-purple-900 font-bold hover:underline">Paramètres des frais</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $invoices->appends(request()->query())->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
