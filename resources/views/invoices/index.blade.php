<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-3">
                    <span class="p-2.5 bg-purple-900 text-amber-300 rounded-2xl shadow-md text-lg">💳</span>
                    <span>Factures & Gestion Comptable</span>
                </h2>
                <p class="text-xs font-bold text-slate-500 mt-1">
                    Année scolaire active : <span class="text-purple-950 font-extrabold">{{ $activeYear?->name ?? 'Non définie' }}</span> • Regroupement par type de frais et classe
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('invoices.create') }}" class="px-4 py-2.5 text-xs font-black bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 text-purple-950 rounded-2xl shadow-md transition transform hover:scale-105 flex items-center gap-1.5">
                    <span>➕</span>
                    <span>Émettre une Facture</span>
                </a>
                <a href="{{ route('invoices.settings') }}" class="px-4 py-2.5 text-xs font-black bg-purple-900 text-amber-300 rounded-2xl hover:bg-purple-800 transition shadow-md flex items-center gap-1.5">
                    <span>⚙️</span>
                    <span>Paramètres des Frais</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            @if (session('status'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-bold flex items-center gap-3 shadow-xs">
                    <span class="text-lg">✅</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- 4 Cartes KPIs Financiers --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Total Frais Dûs -->
                <div class="bg-white rounded-3xl shadow-xl border border-slate-200/80 p-6 flex items-center gap-4 hover:-translate-y-1 transition duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-900 flex items-center justify-center text-2xl flex-shrink-0 shadow-xs">
                        🧾
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Total Facturé</div>
                        <div class="text-xl font-black text-purple-950">{{ number_format($totalDue, 0, ',', ' ') }} <span class="text-xs">F</span></div>
                        <div class="text-[11px] font-bold text-slate-500">Montant total émis</div>
                    </div>
                </div>

                <!-- Total Recouvré -->
                <div class="bg-white rounded-3xl shadow-xl border border-emerald-200/80 p-6 flex items-center gap-4 hover:-translate-y-1 transition duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-900 flex items-center justify-center text-2xl flex-shrink-0 shadow-xs">
                        ✅
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Total Encaissé</div>
                        <div class="text-xl font-black text-emerald-900">{{ number_format($totalPaid, 0, ',', ' ') }} <span class="text-xs">F</span></div>
                        <div class="text-[11px] font-bold text-emerald-600">Recouvrement perçu</div>
                    </div>
                </div>

                <!-- Reste à Recouvrer -->
                <div class="bg-white rounded-3xl shadow-xl border border-amber-200/80 p-6 flex items-center gap-4 hover:-translate-y-1 transition duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-900 flex items-center justify-center text-2xl flex-shrink-0 shadow-xs">
                        ⏳
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Reste à Recouvrer</div>
                        <div class="text-xl font-black text-amber-900">{{ number_format($totalRemaining, 0, ',', ' ') }} <span class="text-xs">F</span></div>
                        <div class="text-[11px] font-bold text-amber-600">Créances restantes</div>
                    </div>
                </div>

                <!-- Taux de Recouvrement -->
                <div class="bg-white rounded-3xl shadow-xl border border-blue-200/80 p-6 flex flex-col justify-between hover:-translate-y-1 transition duration-300">
                    <div class="flex items-center justify-between">
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Taux Recouvrement</div>
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-900 rounded-lg text-xs font-black">{{ $recoveryRate }}%</span>
                    </div>
                    <div class="my-2">
                        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden shadow-inner">
                            <div class="bg-gradient-to-r from-blue-600 to-emerald-500 h-3 rounded-full transition-all duration-500" style="width: {{ min(100, $recoveryRate) }}%"></div>
                        </div>
                    </div>
                    <div class="text-[11px] font-bold text-slate-500 text-right">Sur l'année {{ $activeYear?->name }}</div>
                </div>
            </div>

            {{-- Formulaire de Filtres --}}
            <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80">
                <form method="GET" action="{{ route('invoices.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
                    <!-- Recherche -->
                    <div class="lg:col-span-2">
                        <label for="search" class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Recherche</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" placeholder="N° Facture, matricule, nom, prénom…"
                               class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500 placeholder-slate-400" />
                    </div>

                    <!-- Type de Frais -->
                    <div>
                        <label for="fee_type_id" class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Type de Frais</label>
                        <select id="fee_type_id" name="fee_type_id" class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Tous les types --</option>
                            @foreach ($feeTypes as $ft)
                                <option value="{{ $ft->id }}" {{ $feeTypeId == $ft->id ? 'selected' : '' }}>
                                    {{ $ft->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Cycle -->
                    <div>
                        <label for="cycle_id" class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Cycle</label>
                        <select id="cycle_id" name="cycle_id" class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Tous les cycles --</option>
                            @foreach ($cycles as $cyc)
                                <option value="{{ $cyc->id }}" {{ $cycleId == $cyc->id ? 'selected' : '' }}>
                                    {{ $cyc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Classe -->
                    <div>
                        <label for="class_id" class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Classe</label>
                        <select id="class_id" name="class_id" class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Toutes les classes --</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->level?->cycle?->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Statut -->
                    <div>
                        <label for="status" class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Statut</label>
                        <select id="status" name="status" class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Tous les statuts --</option>
                            <option value="IMPAYEE"  {{ $status === 'IMPAYEE'  ? 'selected' : '' }}>❌ Impayée</option>
                            <option value="PARTIELLE"{{ $status === 'PARTIELLE'? 'selected' : '' }}>⚡ Partielle</option>
                            <option value="PAYEE"    {{ $status === 'PAYEE'    ? 'selected' : '' }}>✅ Payée</option>
                            <option value="ANNULEE"  {{ $status === 'ANNULEE'  ? 'selected' : '' }}>🚫 Annulée</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="sm:col-span-2 lg:col-span-6 flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="submit" class="px-6 py-2.5 bg-purple-900 hover:bg-purple-800 text-amber-300 text-xs font-black uppercase tracking-wider rounded-2xl shadow-md transition">
                            🔍 Filtrer les Factures
                        </button>
                        @if ($search || $status || $feeTypeId || $cycleId || $classId)
                            <a href="{{ route('invoices.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-2xl transition" title="Réinitialiser">
                                Réinitialiser ✕
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Affichage Regroupé par Type de Frais et par Classe --}}
            <div class="space-y-8">
                @forelse ($invoicesByFeeTypeAndClass as $feeTypeName => $classesGroup)
                    @php
                        $feeTotalDue = $classesGroup->flatten()->sum('amount_due');
                        $feeTotalPaid = $classesGroup->flatten()->sum('amount_paid');
                        $feeTotalRemaining = max(0, $feeTotalDue - $feeTotalPaid);
                        $feeCount = $classesGroup->flatten()->count();
                    @endphp

                    <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80 space-y-6 p-6">
                        <!-- En-tête Principal : Type de Frais -->
                        <div class="p-5 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 rounded-2xl text-white flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-md">
                            <div class="flex items-center gap-3.5">
                                <span class="w-12 h-12 rounded-2xl bg-amber-400 text-purple-950 font-black text-xl flex items-center justify-center shadow-xs flex-shrink-0">
                                    💳
                                </span>
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-amber-300 block">Type de Frais</span>
                                    <h3 class="text-lg font-black text-white leading-tight">
                                        {{ $feeTypeName }}
                                    </h3>
                                    <p class="text-xs text-purple-200 font-bold mt-0.5">
                                        {{ $feeCount }} facture(s) émise(s)
                                    </p>
                                </div>
                            </div>

                            <!-- Mini-KPIs du Type de Frais -->
                            <div class="flex flex-wrap items-center gap-3 text-xs font-bold">
                                <div class="px-3.5 py-2 bg-purple-900/80 rounded-xl border border-purple-700/60">
                                    <span class="text-[10px] text-purple-300 uppercase block">Total Dû</span>
                                    <span class="text-sm font-black text-white">{{ number_format($feeTotalDue, 0, ',', ' ') }} F</span>
                                </div>
                                <div class="px-3.5 py-2 bg-emerald-950/60 rounded-xl border border-emerald-600/40">
                                    <span class="text-[10px] text-emerald-300 uppercase block">Encaissé</span>
                                    <span class="text-sm font-black text-emerald-300">{{ number_format($feeTotalPaid, 0, ',', ' ') }} F</span>
                                </div>
                                <div class="px-3.5 py-2 bg-amber-950/60 rounded-xl border border-amber-600/40">
                                    <span class="text-[10px] text-amber-300 uppercase block">Reste</span>
                                    <span class="text-sm font-black text-amber-300">{{ number_format($feeTotalRemaining, 0, ',', ' ') }} F</span>
                                </div>
                            </div>
                        </div>

                        <!-- Sous-groupement : Par Classe -->
                        <div class="space-y-6">
                            @foreach ($classesGroup as $className => $classInvoices)
                                @php
                                    $classDue = $classInvoices->sum('amount_due');
                                    $classPaid = $classInvoices->sum('amount_paid');
                                    $classRemaining = max(0, $classDue - $classPaid);
                                @endphp

                                <div class="bg-slate-50/60 rounded-2xl border border-slate-200/80 overflow-hidden">
                                    <!-- En-tête Classe -->
                                    <div class="px-5 py-3.5 bg-slate-100/90 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-sm">🏫</span>
                                            <h4 class="text-xs font-black text-purple-950 uppercase tracking-wider">
                                                Classe : {{ $className }}
                                            </h4>
                                            <span class="px-2.5 py-0.5 bg-purple-100 text-purple-900 font-extrabold text-[10px] rounded-full">
                                                {{ $classInvoices->count() }} élève(s)
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-4 text-[11px] font-bold text-slate-600">
                                            <span>Dû : <strong class="text-purple-950">{{ number_format($classDue, 0, ',', ' ') }} F</strong></span>
                                            <span>Payé : <strong class="text-emerald-700">{{ number_format($classPaid, 0, ',', ' ') }} F</strong></span>
                                            <span>Reste : <strong class="{{ $classRemaining > 0 ? 'text-red-700' : 'text-slate-400' }}">{{ number_format($classRemaining, 0, ',', ' ') }} F</strong></span>
                                        </div>
                                    </div>

                                    <!-- Table des Factures pour cette Classe -->
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-slate-100">
                                            <thead class="bg-white text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                                <tr>
                                                    <th class="px-5 py-2.5 text-left">N° Facture</th>
                                                    <th class="px-5 py-2.5 text-left">Élève</th>
                                                    <th class="px-5 py-2.5 text-right">Montant Dû</th>
                                                    <th class="px-5 py-2.5 text-right">Payé</th>
                                                    <th class="px-5 py-2.5 text-right">Reste</th>
                                                    <th class="px-5 py-2.5 text-center">Statut</th>
                                                    <th class="px-5 py-2.5 text-right">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 bg-white">
                                                @foreach ($classInvoices as $inv)
                                                    @php
                                                        $statusConfig = [
                                                            'PAYEE'     => ['bg-emerald-100 text-emerald-800 border-emerald-200', '✅ Payée'],
                                                            'PARTIELLE' => ['bg-amber-100 text-amber-800 border-amber-200',     '⚡ Partielle'],
                                                            'IMPAYEE'   => ['bg-red-100 text-red-800 border-red-200',         '❌ Impayée'],
                                                            'ANNULEE'   => ['bg-slate-100 text-slate-600 border-slate-200',        '🚫 Annulée'],
                                                        ];
                                                        [$badgeClass, $label] = $statusConfig[$inv->status] ?? ['bg-slate-100 text-slate-600 border-slate-200', $inv->status];
                                                        $remaining = (float)$inv->amount_due - (float)$inv->amount_paid;
                                                    @endphp
                                                    <tr class="hover:bg-purple-50/20 transition">
                                                        <td class="px-5 py-3 whitespace-nowrap">
                                                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-mono font-bold bg-purple-50 text-purple-900 border border-purple-100">
                                                                {{ $inv->invoice_number }}
                                                            </span>
                                                        </td>
                                                        <td class="px-5 py-3 whitespace-nowrap">
                                                            <div class="text-xs font-black text-slate-900">{{ $inv->student->last_name }} {{ $inv->student->first_name }}</div>
                                                            <div class="text-[10px] text-slate-400 font-mono font-bold">{{ $inv->student->matricule }}</div>
                                                        </td>
                                                        <td class="px-5 py-3 whitespace-nowrap text-right text-xs font-black text-slate-900">
                                                            {{ number_format($inv->amount_due, 0, ',', ' ') }} F
                                                        </td>
                                                        <td class="px-5 py-3 whitespace-nowrap text-right text-xs font-black text-emerald-700">
                                                            {{ number_format($inv->amount_paid, 0, ',', ' ') }} F
                                                        </td>
                                                        <td class="px-5 py-3 whitespace-nowrap text-right text-xs font-black {{ $remaining > 0 ? 'text-red-600' : 'text-slate-400' }}">
                                                            {{ number_format($remaining, 0, ',', ' ') }} F
                                                        </td>
                                                        <td class="px-5 py-3 whitespace-nowrap text-center">
                                                            <span class="px-2.5 py-0.5 text-[11px] font-black rounded-full border {{ $badgeClass }}">
                                                                {{ $label }}
                                                            </span>
                                                        </td>
                                                        <td class="px-5 py-3 whitespace-nowrap text-right">
                                                            <a href="{{ route('invoices.show', $inv) }}"
                                                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-100 hover:bg-purple-900 hover:text-amber-300 text-purple-950 text-xs font-black rounded-xl transition">
                                                                <span>Détail</span>
                                                                <span>→</span>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-3xl p-12 text-center shadow-xl border border-slate-200/80 space-y-3">
                        <div class="text-4xl">🧾</div>
                        <h4 class="text-base font-black text-purple-950">Aucune facture trouvée</h4>
                        <p class="text-xs text-slate-500 max-w-md mx-auto font-medium">
                            Aucune facture ne correspond à vos critères de recherche. Vous pouvez émettre une facture manuelle ou lancer une génération automatique en masse.
                        </p>
                        <div class="flex items-center justify-center gap-3 pt-2">
                            <a href="{{ route('invoices.create') }}" class="px-4 py-2 bg-amber-400 hover:bg-amber-300 text-purple-950 font-black text-xs rounded-xl shadow-xs transition">
                                ➕ Émettre une Facture
                            </a>
                            <a href="{{ route('invoices.settings') }}" class="px-4 py-2 bg-purple-900 hover:bg-purple-800 text-white font-bold text-xs rounded-xl transition">
                                ⚙️ Génération en Masse
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($invoices->hasPages())
                <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200/80">
                    {{ $invoices->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
