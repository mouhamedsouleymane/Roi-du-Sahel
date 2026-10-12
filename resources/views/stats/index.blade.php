<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight tracking-tight">
                    📊 Statistiques & Analyses Stratégiques
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Indicateurs clés de performance académique, financière, démographique et des ressources humaines.
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if ($activeYear)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black bg-purple-100 text-purple-900 border border-purple-200">
                        ✨ Année : {{ $activeYear->name }}
                    </span>
                @endif
                <a href="{{ route('dashboard') }}" class="px-3.5 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition shadow-2xs">
                    ← Tableau de bord
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen"
         x-data="{ tab: 'pedagogy' }">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            {{-- ── BARRE DE NAVIGATION PAR ONGLETS THÉMATIQUES ──────────── --}}
            <div class="bg-white rounded-2xl p-1.5 border border-slate-200/80 shadow-xs flex flex-wrap gap-1">
                <button @click="tab = 'pedagogy'"
                        :class="tab === 'pedagogy' ? 'bg-purple-950 text-white shadow-sm' : 'text-slate-600 hover:text-purple-950 hover:bg-purple-50/60'"
                        class="flex-1 min-w-[140px] py-2.5 px-4 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2">
                    <span>🎓</span>
                    <span>Pédagogie & Résultats</span>
                </button>
                <button @click="tab = 'finance'"
                        :class="tab === 'finance' ? 'bg-purple-950 text-white shadow-sm' : 'text-slate-600 hover:text-purple-950 hover:bg-purple-50/60'"
                        class="flex-1 min-w-[140px] py-2.5 px-4 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2">
                    <span>💰</span>
                    <span>Finances & Trésorerie</span>
                </button>
                <button @click="tab = 'demography'"
                        :class="tab === 'demography' ? 'bg-purple-950 text-white shadow-sm' : 'text-slate-600 hover:text-purple-950 hover:bg-purple-50/60'"
                        class="flex-1 min-w-[140px] py-2.5 px-4 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2">
                    <span>👥</span>
                    <span>Effectifs & Inscriptions</span>
                </button>
                <button @click="tab = 'hr'"
                        :class="tab === 'hr' ? 'bg-purple-950 text-white shadow-sm' : 'text-slate-600 hover:text-purple-950 hover:bg-purple-50/60'"
                        class="flex-1 min-w-[140px] py-2.5 px-4 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2">
                    <span>👨‍🏫</span>
                    <span>Vie Scolaire & RH</span>
                </button>
            </div>

            {{-- ════════════════════════════════════════════════════════════ --}}
            {{-- ── ONGLET 1 : PÉDAGOGIE & ÉVALUATIONS ───────────────────── --}}
            {{-- ════════════════════════════════════════════════════════════ --}}
            <div x-show="tab === 'pedagogy'" class="space-y-6">

                {{-- Moyennes par classe --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-lg font-black text-slate-900">📈 Palmarès des Moyennes Générales par Classe</h3>
                            <p class="text-xs text-slate-400">
                                @if ($latestPeriod)
                                    Période d'évaluation : <span class="font-extrabold text-purple-900">{{ $latestPeriod->name }}</span>
                                @else
                                    Dernière période d'évaluation
                                @endif
                            </p>
                        </div>
                        @if ($latestPeriod)
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-black">
                                📌 {{ count($averagesByClass) }} classe(s) évaluée(s)
                            </span>
                        @endif
                    </div>

                    @if ($latestPeriod && count($averagesByClass) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-xs">
                                <thead class="bg-slate-50/80 text-slate-500 font-extrabold uppercase border-b border-slate-100">
                                    <tr>
                                        <th class="px-4 py-3">Rang</th>
                                        <th class="px-4 py-3">Classe</th>
                                        <th class="px-4 py-3 text-center">Élèves Notés</th>
                                        <th class="px-4 py-3 text-center">Moyenne / 20</th>
                                        <th class="px-4 py-3 text-center">Taux de Réussite (≥ 10)</th>
                                        <th class="px-4 py-3 text-left">Niveau Global</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-semibold">
                                    @foreach ($averagesByClass as $i => $row)
                                        @php $avg = (float)$row->avg_class; @endphp
                                        <tr class="hover:bg-purple-50/30 transition">
                                            <td class="px-4 py-3.5 font-black {{ $i === 0 ? 'text-amber-500 text-sm' : ($i < 3 ? 'text-purple-900 font-bold' : 'text-slate-400') }}">
                                                @if ($i === 0) 🥇 #1 @elseif ($i === 1) 🥈 #2 @elseif ($i === 2) 🥉 #3 @else #{{ $i + 1 }} @endif
                                            </td>
                                            <td class="px-4 py-3.5 font-extrabold text-slate-900 text-sm">{{ $row->class_name }}</td>
                                            <td class="px-4 py-3.5 text-center text-slate-700 font-bold">{{ $row->nb }}</td>
                                            <td class="px-4 py-3.5 text-center">
                                                <span class="text-base font-black px-2.5 py-1 rounded-lg {{ $avg >= 12 ? 'bg-emerald-100 text-emerald-900' : ($avg >= 10 ? 'bg-amber-100 text-amber-900' : 'bg-rose-100 text-rose-900') }}">
                                                    {{ number_format($avg, 2) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3.5 text-center font-black {{ $row->pass_rate >= 70 ? 'text-emerald-700' : ($row->pass_rate >= 50 ? 'text-amber-700' : 'text-rose-700') }}">
                                                {{ $row->pass_rate }}%
                                            </td>
                                            <td class="px-4 py-3.5">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-28 bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                                        <div class="h-2.5 rounded-full {{ $avg >= 12 ? 'bg-emerald-500' : ($avg >= 10 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                                             style="width: {{ min(100, round($avg / 20 * 100)) }}%"></div>
                                                    </div>
                                                    <span class="text-[11px] font-bold text-slate-400">{{ round($avg / 20 * 100) }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-10 text-slate-400 text-xs italic">
                            Aucune moyenne calculée pour la période sélectionnée.
                        </div>
                    @endif
                </div>

                {{-- Taux d'absence par matière --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6">
                    <h3 class="text-lg font-black text-slate-900 mb-1">📉 Taux d'Absence par Matière lors des Évaluations</h3>
                    <p class="text-xs text-slate-400 mb-5">Matières enregistrant le plus d'absences injustifiées ou signalées</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @forelse ($absenceBySubject as $subj)
                            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/70 space-y-2">
                                <div class="flex justify-between items-start gap-2">
                                    <span class="font-extrabold text-xs text-slate-800 truncate">{{ $subj->subject_name }}</span>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black {{ $subj->absence_rate > 10 ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $subj->absence_rate }}% absents
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 font-semibold">
                                    {{ $subj->total_absent }} absence(s) sur {{ $subj->total_grades }} notes
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-rose-500 h-1.5 rounded-full" style="width: {{ min(100, $subj->absence_rate) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-6 text-slate-400 text-xs italic">
                                Aucune absence enregistrée lors des évaluations.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- ════════════════════════════════════════════════════════════ --}}
            {{-- ── ONGLET 2 : FINANCES & TRÉSORERIE ─────────────────────── --}}
            {{-- ════════════════════════════════════════════════════════════ --}}
            <div x-show="tab === 'finance'" class="space-y-6" style="display: none;">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- Recettes par type de frais --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6">
                        <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-3">
                            <div>
                                <h3 class="text-base font-black text-slate-900">📥 Recettes par Type de Frais</h3>
                                <p class="text-xs text-slate-400">Ventilation des encaissements sur l'année</p>
                            </div>
                            <span class="text-xs font-extrabold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-lg">
                                Encaissé vs Total
                            </span>
                        </div>

                        <div class="space-y-4">
                            @forelse ($receiptsByFeeType as $fee)
                                @php
                                    $rate = $fee->total_due > 0 ? round(($fee->total_paid / $fee->total_due) * 100, 1) : 0;
                                @endphp
                                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 space-y-1.5">
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="font-black text-slate-800">{{ $fee->fee_name }}</span>
                                        <span class="font-extrabold text-emerald-800">{{ number_format($fee->total_paid, 0, ',', ' ') }} FCFA</span>
                                    </div>
                                    <div class="flex justify-between text-[11px] text-slate-400 font-semibold">
                                        <span>Facturé : {{ number_format($fee->total_due, 0, ',', ' ') }} FCFA</span>
                                        <span class="font-bold text-slate-600">{{ $rate }}% perçu</span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                        <div class="bg-emerald-600 h-2 rounded-full transition-all duration-500" style="width: {{ min(100, $rate) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-6 text-slate-400 text-xs italic">
                                    Aucun type de frais facturé pour le moment.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Dépenses par Catégorie --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6">
                        <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-3">
                            <div>
                                <h3 class="text-base font-black text-slate-900">💸 Répartition des Dépenses par Poste</h3>
                                <p class="text-xs text-slate-400">Total des charges et sorties de caisse</p>
                            </div>
                            <span class="text-xs font-extrabold text-rose-800 bg-rose-50 px-2.5 py-1 rounded-lg">
                                Par catégorie
                            </span>
                        </div>

                        @php $maxExp = $expensesByCategory->max('total') ?: 1; @endphp

                        <div class="space-y-4">
                            @forelse ($expensesByCategory as $expCat)
                                @php $expPct = round(($expCat->total / $maxExp) * 100); @endphp
                                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 space-y-1.5">
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="font-black text-slate-800 flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $expCat->color ?? '#ef4444' }}"></span>
                                            {{ $expCat->name }}
                                        </span>
                                        <span class="font-black text-rose-800">{{ number_format($expCat->total, 0, ',', ' ') }} FCFA</span>
                                    </div>
                                    <div class="flex justify-between text-[11px] text-slate-400 font-semibold">
                                        <span>{{ $expCat->count }} opération(s) enregistrée(s)</span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                        <div class="h-2 rounded-full transition-all duration-500" style="background-color: {{ $expCat->color ?? '#ef4444' }}; width: {{ $expPct }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-6 text-slate-400 text-xs italic">
                                    Aucune dépense enregistrée sur cette année.
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

                {{-- Taux de recouvrement par cycle --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6">
                    <h3 class="text-base font-black text-slate-900 mb-1">🎯 Taux de Recouvrement par Cycle d'Enseignement</h3>
                    <p class="text-xs text-slate-400 mb-5">Performance financière et efficacité de collecte par niveau d'études</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @forelse ($recoveryByCycle as $cRec)
                            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/80 space-y-2.5">
                                <div class="flex justify-between items-center">
                                    <span class="font-black text-xs text-purple-950">{{ $cRec->cycle_name }}</span>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black {{ $cRec->recovery_rate >= 80 ? 'bg-emerald-100 text-emerald-800' : ($cRec->recovery_rate >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $cRec->recovery_rate }}%
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-emerald-800">{{ number_format($cRec->total_paid, 0, ',', ' ') }} FCFA</div>
                                    <div class="text-[10px] text-slate-400 font-semibold">sur {{ number_format($cRec->total_due, 0, ',', ' ') }} FCFA dus</div>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full {{ $cRec->recovery_rate >= 80 ? 'bg-emerald-600' : ($cRec->recovery_rate >= 50 ? 'bg-amber-500' : 'bg-rose-600') }}" style="width: {{ min(100, $cRec->recovery_rate) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-6 text-slate-400 text-xs italic">
                                Aucune donnée de recouvrement disponible.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- ════════════════════════════════════════════════════════════ --}}
            {{-- ── ONGLET 3 : EFFECTIFS & DÉMOGRAPHIE ────────────────────── --}}
            {{-- ════════════════════════════════════════════════════════════ --}}
            <div x-show="tab === 'demography'" class="space-y-6" style="display: none;">

                {{-- Effectifs par cycle et genre --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-7">
                    <h3 class="text-lg font-black text-slate-900 mb-1">👥 Répartition Paritaire Garçons / Filles par Cycle</h3>
                    <p class="text-xs text-slate-400 mb-6">Équilibre des genres au sein des différents cycles</p>

                    <div class="space-y-6">
                        @forelse ($enrollmentsByGenderCycle as $cycleName => $rows)
                            @php
                                $boys  = $rows->where('gender', 'M')->first()?->total ?? 0;
                                $girls = $rows->where('gender', 'F')->first()?->total ?? 0;
                                $total = $boys + $girls;
                                $boyPct   = $total > 0 ? round(($boys  / $total) * 100) : 0;
                                $girlPct  = $total > 0 ? round(($girls / $total) * 100) : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-extrabold text-slate-900">{{ $cycleName }}</span>
                                    <span class="text-xs text-purple-900 bg-purple-50 font-bold px-2.5 py-0.5 rounded-md">{{ $total }} élève(s)</span>
                                </div>
                                <div class="flex h-7 rounded-full overflow-hidden p-0.5 bg-slate-100 border border-slate-200/60 shadow-xs">
                                    @if ($boys > 0)
                                        <div class="bg-gradient-to-r from-blue-600 to-indigo-500 flex items-center justify-center text-[10px] font-black text-white transition-all duration-500 shadow-sm"
                                             style="width: {{ $boyPct }}%">
                                            {{ $boys }} Garçons ({{ $boyPct }}%)
                                        </div>
                                    @endif
                                    @if ($girls > 0)
                                        <div class="bg-gradient-to-r from-pink-500 to-purple-500 flex items-center justify-center text-[10px] font-black text-white transition-all duration-500 shadow-sm"
                                             style="width: {{ $girlPct }}%">
                                            {{ $girls }} Filles ({{ $girlPct }}%)
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400 italic text-center py-6">Aucune donnée d'inscription disponible.</p>
                        @endforelse
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Évolution mensuelle des inscriptions --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6">
                        <h3 class="text-base font-black text-slate-900 mb-1">📅 Évolution Temporelle des Inscriptions</h3>
                        <p class="text-xs text-slate-400 mb-5">Nombre d'élèves inscrits par mois calendaire</p>

                        @php $maxMonth = $enrollmentsByMonth->max('total') ?: 1; @endphp

                        <div class="space-y-3.5">
                            @forelse ($enrollmentsByMonth as $mRow)
                                @php $mPct = round(($mRow->total / $maxMonth) * 100); @endphp
                                <div>
                                    <div class="flex justify-between items-center text-xs mb-1">
                                        <span class="font-black text-purple-950">{{ \Carbon\Carbon::parse($mRow->month.'-01')->translatedFormat('F Y') }}</span>
                                        <span class="font-extrabold text-slate-700">{{ $mRow->total }} élève(s)</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                                        <div class="bg-gradient-to-r from-purple-800 to-indigo-600 h-3 rounded-full transition-all duration-500" style="width: {{ $mPct }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-6 text-slate-400 text-xs italic">
                                    Aucune inscription mensuelle enregistrée.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Suivi des pré-inscriptions web --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 flex flex-col justify-between">
                        <div>
                            <h3 class="text-base font-black text-slate-900 mb-1">🌐 Entonnoir des Pré-inscriptions Web</h3>
                            <p class="text-xs text-slate-400 mb-5">Statut de conversion des demandes soumises en ligne</p>

                            @php
                                $totalPre = $preEnrollmentStats->sum();
                                $pendingPre = $preEnrollmentStats['EN_ATTENTE'] ?? 0;
                                $acceptedPre = $preEnrollmentStats['VALIDE'] ?? ($preEnrollmentStats['ACCEPTE'] ?? 0);
                                $rejectedPre = $preEnrollmentStats['REJETE'] ?? 0;
                            @endphp

                            <div class="grid grid-cols-3 gap-3 text-center mb-6">
                                <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200">
                                    <div class="text-2xl font-black text-amber-900">{{ $pendingPre }}</div>
                                    <div class="text-[10px] font-bold text-amber-700 uppercase mt-0.5">En Attente</div>
                                </div>
                                <div class="p-3.5 bg-emerald-50 rounded-2xl border border-emerald-200">
                                    <div class="text-2xl font-black text-emerald-900">{{ $acceptedPre }}</div>
                                    <div class="text-[10px] font-bold text-emerald-700 uppercase mt-0.5">Validées</div>
                                </div>
                                <div class="p-3.5 bg-rose-50 rounded-2xl border border-rose-200">
                                    <div class="text-2xl font-black text-rose-900">{{ $rejectedPre }}</div>
                                    <div class="text-[10px] font-bold text-rose-700 uppercase mt-0.5">Refusées</div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-purple-50/80 rounded-2xl border border-purple-100 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-purple-900">Total dossiers reçus en ligne :</span>
                                <span class="text-lg font-black text-purple-950 ml-1">{{ $totalPre }}</span>
                            </div>
                            <a href="{{ route('pre-enrollments.index') }}" class="px-3 py-1.5 bg-purple-900 hover:bg-purple-950 text-white rounded-xl text-xs font-extrabold transition">
                                Gérer →
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ════════════════════════════════════════════════════════════ --}}
            {{-- ── ONGLET 4 : VIE SCOLAIRE & RH ─────────────────────────── --}}
            {{-- ════════════════════════════════════════════════════════════ --}}
            <div x-show="tab === 'hr'" class="space-y-6" style="display: none;">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Enseignants par type de contrat --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-7">
                        <h3 class="text-lg font-black text-slate-900 mb-1">👨‍🏫 Corps Professoral par Type de Contrat</h3>
                        <p class="text-xs text-slate-400 mb-6">Répartition des enseignants actifs</p>

                        @php
                            $contractColors = [
                                'PERMANENT'   => ['bg' => 'bg-purple-700', 'badge' => 'bg-purple-100 text-purple-900'],
                                'VACATAIRE'   => ['bg' => 'bg-amber-500',  'badge' => 'bg-amber-100 text-amber-900'],
                                'CONTRACTUEL' => ['bg' => 'bg-emerald-600', 'badge' => 'bg-emerald-100 text-emerald-900'],
                            ];
                            $totalTeachers = $teachersByContract->sum();
                        @endphp

                        <div class="space-y-4">
                            @forelse ($teachersByContract as $type => $count)
                                @php
                                    $pct    = $totalTeachers > 0 ? round(($count / $totalTeachers) * 100) : 0;
                                    $colors = $contractColors[$type] ?? ['bg' => 'bg-slate-400', 'badge' => 'bg-slate-100 text-slate-800'];
                                @endphp
                                <div>
                                    <div class="flex justify-between items-center mb-1.5">
                                        <span class="px-2.5 py-0.5 text-xs font-black rounded-full {{ $colors['badge'] }}">{{ $type }}</span>
                                        <span class="text-sm font-black text-slate-900">{{ $count }} <span class="text-xs font-normal text-slate-400">({{ $pct }}%)</span></span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                                        <div class="h-3 rounded-full {{ $colors['bg'] }} transition-all duration-500" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-400 italic text-center py-4">Aucun enseignant enregistré.</p>
                            @endforelse
                        </div>

                        @if ($totalTeachers > 0)
                            <div class="mt-6 p-4 bg-purple-50 rounded-2xl text-center border border-purple-100">
                                <span class="text-3xl font-black text-purple-950">{{ $totalTeachers }}</span>
                                <span class="text-xs text-purple-700 font-extrabold block mt-0.5">enseignants actifs au total</span>
                            </div>
                        @endif
                    </div>

                    {{-- Classement des classes par effectif --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-7">
                        <h3 class="text-lg font-black text-slate-900 mb-1">🏆 Classement des Classes par Effectif</h3>
                        <p class="text-xs text-slate-400 mb-5">Classes ordonnées par nombre d'élèves inscrits</p>

                        <div class="space-y-3.5 max-h-96 overflow-y-auto pr-2">
                            @php $maxEff = $classesByEnrollment->max('total') ?: 1; @endphp
                            @forelse ($classesByEnrollment as $i => $cls)
                                @php $pct = round(($cls->total / $maxEff) * 100); @endphp
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-black w-6 text-purple-900 bg-purple-50 p-1 text-center rounded-lg">{{ $i + 1 }}</span>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-xs font-extrabold text-slate-800">{{ $cls->class_name }} <span class="text-[10px] text-slate-400 font-normal">({{ $cls->cycle_name }})</span></span>
                                            <span class="text-xs font-black text-purple-900">{{ $cls->total }} élève(s)</span>
                                        </div>
                                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                            <div class="h-2.5 rounded-full bg-gradient-to-r from-amber-500 via-purple-700 to-indigo-600 transition-all duration-500"
                                                 style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-400 italic text-center py-4">Aucune inscription.</p>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>

