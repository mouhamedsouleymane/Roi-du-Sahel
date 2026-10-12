<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight tracking-tight">
                    Tableau de Bord
                </h2>
                <p class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-2">
                    @if ($activeYear)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-purple-100 text-purple-900 border border-purple-200">
                            ✨ Année active : {{ $activeYear->name }}
                        </span>
                        @if ($activePeriod)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-amber-100 text-amber-900 border border-amber-200">
                                📌 {{ $activePeriod->name }}
                            </span>
                        @endif
                    @else
                        <span class="text-red-600 font-bold">⚠ Aucune année scolaire active</span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                <span class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 shadow-2xs">
                    📅 {{ now()->translatedFormat('l d F Y') }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-7 animate-fade-in-up">

            {{-- ── HERO BRAND BANNER ────────────────────────────────────── --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 p-7 text-white shadow-2xl shadow-purple-950/25 border border-purple-800/40">
                <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-10 -top-10 w-72 h-72 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-wrap justify-between items-center gap-6">
                    <div class="space-y-2 max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/20 border border-amber-400/40 text-amber-300 text-xs font-extrabold tracking-widest uppercase">
                            👑 COMPLEXE SCOLAIRE PRIVÉ
                        </div>
                        <h1 class="text-2xl sm:text-4xl font-black text-white tracking-wide">
                            Bienvenue sur <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-yellow-200 to-amber-400">ROI DU SAHEL</span>
                        </h1>
                        <p class="text-sm text-purple-200/90 leading-relaxed">
                            Supervision globale de la scolarité, de la gestion pédagogique, des flux financiers et des dépenses.
                        </p>
                    </div>

                    {{-- Devise officielle & Action rapide --}}
                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <div class="bg-white/10 backdrop-blur-md border border-white/20 px-5 py-3.5 rounded-2xl text-center space-y-0.5 shadow-lg">
                            <div class="text-[10px] uppercase font-extrabold text-amber-300 tracking-widest">Devise de l'Établissement</div>
                            <div class="text-sm font-black tracking-wider text-white">
                                DISCIPLINE • TRAVAIL • SUCCÈS
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── BANDEAU D'ALERTES / ACTIONS REQUISES ─────────────────── --}}
            @if ($pendingPreEnrollments > 0 || $unpaidInvoicesCount > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if ($pendingPreEnrollments > 0)
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-950 shadow-sm">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🌐</span>
                                <div>
                                    <div class="font-extrabold text-sm text-amber-900">{{ $pendingPreEnrollments }} pré-inscription(s) web en attente</div>
                                    <div class="text-xs text-amber-700">Dossiers soumis par les parents à valider</div>
                                </div>
                            </div>
                            <a href="{{ route('pre-enrollments.index') }}" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-black shadow-sm transition">
                                Traiter →
                            </a>
                        </div>
                    @endif

                    @if ($unpaidInvoicesCount > 0)
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-950 shadow-sm">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">⚠️</span>
                                <div>
                                    <div class="font-extrabold text-sm text-rose-900">{{ $unpaidInvoicesCount }} facture(s) avec solde impayé</div>
                                    <div class="text-xs text-rose-700">Reste total à recouvrer : {{ number_format($totalUnpaid, 0, ',', ' ') }} FCFA</div>
                                </div>
                            </div>
                            <a href="{{ route('invoices.index') }}" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black shadow-sm transition">
                                Voir factures →
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ── SECTION 1 : KPIS SCOLARITÉ ───────────────────────────── --}}
            <div>
                <div class="flex items-center justify-between mb-3 px-1">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">📚 Vie Scolaire & Pédagogie</h3>
                    <a href="{{ route('stats.index') }}" class="text-xs font-bold text-purple-900 hover:underline">Statistiques détaillées →</a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {{-- Élèves Inscrits --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-5 flex items-center gap-4 hover:-translate-y-0.5 hover:shadow-md hover:border-purple-300 transition-all duration-300 group">
                        <div class="w-13 h-13 rounded-2xl bg-purple-100 text-purple-800 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-105 transition-transform">🎓</div>
                        <div>
                            <div class="text-2xl sm:text-3xl font-black text-purple-950">{{ number_format($totalEnrolled) }}</div>
                            <div class="text-xs text-slate-600 font-bold">Élèves inscrits</div>
                            <div class="text-[11px] text-slate-400 font-medium">sur {{ number_format($totalStudents) }} au total</div>
                        </div>
                    </div>

                    {{-- Enseignants --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-amber-100 p-5 flex items-center gap-4 hover:-translate-y-0.5 hover:shadow-md hover:border-amber-300 transition-all duration-300 group">
                        <div class="w-13 h-13 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-105 transition-transform">👨‍🏫</div>
                        <div>
                            <div class="text-2xl sm:text-3xl font-black text-amber-950">{{ number_format($totalTeachers) }}</div>
                            <div class="text-xs text-slate-600 font-bold">Enseignants actifs</div>
                            <div class="text-[11px] text-slate-400 font-medium">corps professoral</div>
                        </div>
                    </div>

                    {{-- Classes --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 p-5 flex items-center gap-4 hover:-translate-y-0.5 hover:shadow-md hover:border-emerald-300 transition-all duration-300 group">
                        <div class="w-13 h-13 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-105 transition-transform">🏫</div>
                        <div>
                            <div class="text-2xl sm:text-3xl font-black text-emerald-950">{{ number_format($totalClasses) }}</div>
                            <div class="text-xs text-slate-600 font-bold">Classes ouvertes</div>
                            <div class="text-[11px] text-slate-400 font-medium">année en cours</div>
                        </div>
                    </div>

                    {{-- Bulletins Publiés --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-indigo-100 p-5 flex items-center gap-4 hover:-translate-y-0.5 hover:shadow-md hover:border-indigo-300 transition-all duration-300 group">
                        <div class="w-13 h-13 rounded-2xl bg-indigo-100 text-indigo-800 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-105 transition-transform">📄</div>
                        <div>
                            <div class="text-2xl sm:text-3xl font-black text-indigo-950">{{ number_format($publishedBulletins) }}</div>
                            <div class="text-xs text-slate-600 font-bold">Bulletins publiés</div>
                            <div class="text-[11px] text-slate-400 font-medium">{{ $periodEvaluations }} évaluations</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECTION 2 : COMPTABILITÉ & TRÉSORERIE CONSOLIDÉE ─────── --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xl">💰</span>
                            <h3 class="text-lg font-black text-purple-950">Comptabilité & Trésorerie Globale</h3>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">Bilan consolidé des encaissements, des dépenses et de la trésorerie nette</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('invoices.index') }}" class="px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-900 text-xs font-extrabold transition">
                            💳 Facturation & Recettes
                        </a>
                        <a href="{{ route('expenses.index') }}" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-900 text-xs font-extrabold transition">
                            💸 Dépenses & Sorties
                        </a>
                    </div>
                </div>

                {{-- 4 KPIS FINANCIERS CONSOLIDÉS --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- 1. Frais dus & Recouvrement --}}
                    <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-4 space-y-2">
                        <div class="flex justify-between items-center text-xs font-bold text-slate-500">
                            <span>🧾 Facturation Totale</span>
                            <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 text-[10px] font-black">{{ $recoveryRate }}% recouvré</span>
                        </div>
                        <div class="text-xl sm:text-2xl font-black text-slate-900">
                            {{ number_format($totalRevenueDue, 0, ',', ' ') }} <span class="text-xs font-bold text-slate-400">FCFA</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" style="width: {{ min(100, $recoveryRate) }}%"></div>
                        </div>
                    </div>

                    {{-- 2. Recettes encaissées --}}
                    <div class="bg-emerald-50/60 rounded-2xl border border-emerald-200/80 p-4 space-y-2">
                        <div class="flex justify-between items-center text-xs font-bold text-emerald-800">
                            <span>📥 Total Recettes Encaissées</span>
                            <span class="text-base">✅</span>
                        </div>
                        <div class="text-xl sm:text-2xl font-black text-emerald-950">
                            {{ number_format($totalRevenuePaid, 0, ',', ' ') }} <span class="text-xs font-bold text-emerald-600">FCFA</span>
                        </div>
                        <div class="text-[11px] text-emerald-700 font-medium">
                            Encaissés sur l'année active
                        </div>
                    </div>

                    {{-- 3. Dépenses engagées --}}
                    <div class="bg-rose-50/60 rounded-2xl border border-rose-200/80 p-4 space-y-2">
                        <div class="flex justify-between items-center text-xs font-bold text-rose-800">
                            <span>💸 Total Dépenses & Sorties</span>
                            <span class="text-base">📤</span>
                        </div>
                        <div class="text-xl sm:text-2xl font-black text-rose-950">
                            {{ number_format($totalExpenses, 0, ',', ' ') }} <span class="text-xs font-bold text-rose-600">FCFA</span>
                        </div>
                        <div class="text-[11px] text-rose-700 font-medium">
                            Charges & décaissements enregistrés
                        </div>
                    </div>

                    {{-- 4. Solde net de trésorerie --}}
                    <div class="rounded-2xl border p-4 space-y-2 {{ $netCashBalance >= 0 ? 'bg-gradient-to-br from-purple-950 to-indigo-950 text-white border-purple-800' : 'bg-red-950 text-white border-red-800' }}">
                        <div class="flex justify-between items-center text-xs font-extrabold {{ $netCashBalance >= 0 ? 'text-amber-300' : 'text-red-300' }}">
                            <span>💎 Solde Net Trésorerie</span>
                            <span>{{ $netCashBalance >= 0 ? '🟢 Positif' : '🔴 Déficit' }}</span>
                        </div>
                        <div class="text-xl sm:text-2xl font-black {{ $netCashBalance >= 0 ? 'text-white' : 'text-red-200' }}">
                            {{ number_format($netCashBalance, 0, ',', ' ') }} <span class="text-xs font-bold opacity-80">FCFA</span>
                        </div>
                        <div class="text-[11px] {{ $netCashBalance >= 0 ? 'text-purple-200' : 'text-red-300' }} font-medium">
                            Recettes réelles - Dépenses
                        </div>
                    </div>
                </div>

                {{-- DERNIERS FLUX FINANCIERS (PAIEMENTS VS DÉPENSES) --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-2">
                    {{-- Derniers Encaissements --}}
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <h4 class="text-xs font-black uppercase tracking-wider text-emerald-900 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Derniers Paiements Encaissés
                            </h4>
                            <a href="{{ route('invoices.index') }}" class="text-[11px] font-bold text-purple-900 hover:underline">Tout voir →</a>
                        </div>
                        <div class="space-y-2">
                            @forelse ($recentPayments as $payment)
                                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 hover:bg-purple-50/40 transition text-xs">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-black text-xs">
                                            📥
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-slate-800">{{ $payment->student?->full_name ?? 'Élève' }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $payment->invoice?->feeType?->name ?? 'Frais scolaires' }} • {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') : '–' }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-black text-emerald-800">+{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</div>
                                        <div class="text-[10px] text-slate-400 uppercase font-semibold">{{ $payment->payment_method }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-6 bg-slate-50 rounded-xl text-slate-400 text-xs italic">
                                    Aucun paiement enregistré pour le moment.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Dernières Dépenses --}}
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <h4 class="text-xs font-black uppercase tracking-wider text-rose-900 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                Dernières Dépenses Réalisées
                            </h4>
                            <a href="{{ route('expenses.index') }}" class="text-[11px] font-bold text-purple-900 hover:underline">Tout voir →</a>
                        </div>
                        <div class="space-y-2">
                            @forelse ($recentExpenses as $exp)
                                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 hover:bg-rose-50/40 transition text-xs">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center font-black text-xs" style="background-color: {{ $exp->category?->color ?? '#cbd5e1' }}20; color: {{ $exp->category?->color ?? '#475569' }}">
                                            💸
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-slate-800">{{ $exp->label }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $exp->category?->name ?? 'Général' }} • {{ $exp->expense_date ? \Carbon\Carbon::parse($exp->expense_date)->format('d/m/Y') : '–' }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-black text-rose-800">-{{ number_format($exp->amount, 0, ',', ' ') }} FCFA</div>
                                        <div class="text-[10px] text-slate-400 uppercase font-semibold">{{ $exp->payment_method }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-6 bg-slate-50 rounded-xl text-slate-400 text-xs italic">
                                    Aucune dépense enregistrée pour le moment.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Principales catégories de dépenses --}}
                @if ($topExpenseCategories->isNotEmpty())
                    <div class="pt-4 border-t border-slate-100">
                        <div class="text-xs font-bold text-slate-500 mb-3">🏷️ Répartition des principaux postes de dépenses :</div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach ($topExpenseCategories as $cat)
                                <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/70 flex items-center gap-2.5">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $cat->color ?? '#6366f1' }}"></span>
                                    <div class="min-w-0">
                                        <div class="text-xs font-extrabold text-slate-800 truncate">{{ $cat->name }}</div>
                                        <div class="text-xs font-black text-slate-900">{{ number_format($cat->total, 0, ',', ' ') }} FCFA</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- ── SECTION 3 : RÉPARTITION DES EFFECTIFS (CYCLES & GENRE) ── --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-7">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6 border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-900">📊 Répartition des Inscrits par Cycle & Genre</h3>
                        <p class="text-xs text-slate-400">Effectifs détaillés pour l'année scolaire {{ $activeYear?->name ?? '–' }}</p>
                    </div>
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full bg-purple-50 border border-purple-100 text-purple-900 text-xs font-black self-start sm:self-auto">
                        🎓 {{ $totalEnrolled }} élèves inscrits au total
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-center">
                    {{-- Barres par Cycle (2 colonnes sur grand écran) --}}
                    <div class="lg:col-span-2 space-y-4">
                        @php $maxCycle = $enrollmentsByCycle->max('total') ?: 1; @endphp

                        @forelse ($enrollmentsByCycle as $row)
                            @php $pct = round(($row->total / $maxCycle) * 100); @endphp
                            <div class="p-3 rounded-2xl bg-slate-50/70 border border-slate-100 hover:border-purple-200 transition">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-extrabold text-slate-800">{{ $row->cycle_name }}</span>
                                    <span class="text-xs font-black text-purple-950 bg-purple-100/80 px-2.5 py-0.5 rounded-md">{{ $row->total }} élève(s)</span>
                                </div>
                                <div class="w-full bg-slate-200/70 rounded-full h-3 p-0.5 overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-purple-800 via-purple-600 to-amber-500 transition-all duration-700 shadow-xs"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400 italic text-center py-6">Aucune inscription enregistrée.</p>
                        @endforelse
                    </div>

                    {{-- Répartition Genre (1 colonne sur grand écran) --}}
                    <div class="grid grid-cols-2 lg:grid-cols-1 gap-4">
                        <div class="flex items-center gap-4 p-4 bg-blue-50/80 rounded-2xl border border-blue-100">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center text-2xl flex-shrink-0">👦</div>
                            <div>
                                <div class="text-2xl font-black text-blue-900">{{ number_format($totalBoys) }}</div>
                                <div class="text-xs text-blue-700 font-extrabold">Garçons inscrits</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 p-4 bg-pink-50/80 rounded-2xl border border-pink-100">
                            <div class="w-12 h-12 rounded-xl bg-pink-100 text-pink-800 flex items-center justify-center text-2xl flex-shrink-0">👧</div>
                            <div>
                                <div class="text-2xl font-black text-pink-900">{{ number_format($totalGirls) }}</div>
                                <div class="text-xs text-pink-700 font-extrabold">Filles inscrites</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECTION 4 : TAUX DE REMPLISSAGE DES CLASSES ─────────── --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-7">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-black text-slate-900">🏫 Taux de Remplissage des Classes</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Indicateur visuel d'occupation par classe pour l'année active</p>
                    </div>
                    <div class="flex items-center gap-3 text-[11px] font-bold text-slate-500">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Normal (< 70%)</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Dense (70-89%)</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span> Saturée (≥ 90%)</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-4">
                    @forelse ($classesOverview as $cls)
                        @php
                            $capacity = $cls['capacity'] ?: 40;
                            $enrolled = $cls['enrolled'];
                            $pct      = min(100, round(($enrolled / $capacity) * 100));
                        @endphp
                        <div class="flex flex-col items-center p-4 rounded-2xl border border-slate-100 bg-slate-50/70 hover:shadow-lg hover:border-purple-200 transition-all duration-300">
                            <div class="relative w-16 h-16 mb-3">
                                <svg viewBox="0 0 36 36" class="w-16 h-16 -rotate-90">
                                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e2e8f0" stroke-width="3.5"/>
                                    <circle cx="18" cy="18" r="15.9" fill="none"
                                            stroke="{{ $pct >= 90 ? '#ef4444' : ($pct >= 70 ? '#f59e0b' : '#059669') }}"
                                            stroke-width="3.5"
                                            stroke-dasharray="{{ $pct }}, 100"
                                            stroke-linecap="round"/>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-xs font-black {{ $pct >= 90 ? 'text-red-600' : ($pct >= 70 ? 'text-amber-600' : 'text-emerald-600') }}">
                                        {{ $pct }}%
                                    </span>
                                </div>
                            </div>
                            <div class="font-extrabold text-slate-900 text-sm text-center">{{ $cls['name'] }}</div>
                            <div class="text-[10px] text-slate-400 text-center font-semibold">{{ $cls['cycle'] }}</div>
                            <div class="text-xs font-extrabold text-slate-700 mt-1">{{ $enrolled }}/{{ $capacity }}</div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-6 text-sm text-slate-400 italic">
                            Aucune classe enregistrée pour l'année active.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ── SECTION 5 : RACCOURCIS RAPIDES & ACTIONS DIRECTES (HORIZONTAL) ─ --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">⚡</span>
                        <h3 class="text-base font-black text-purple-950">Raccourcis Rapides & Actions Fréquentes</h3>
                    </div>
                    <span class="text-xs text-slate-400 font-semibold hidden sm:inline">Accès direct aux modules clés</span>
                </div>

                @php
                    $quickLinks = [
                        ['route' => 'enrollments.create',   'icon' => '➕', 'label' => 'Nouvelle Inscription',    'bg' => 'hover:bg-purple-900 hover:text-white bg-purple-50 text-purple-950 border-purple-100'],
                        ['route' => 'pre-enrollments.index', 'icon' => '🌐', 'label' => 'Pré-inscriptions Web',   'bg' => 'hover:bg-amber-600 hover:text-white bg-amber-50 text-amber-950 border-amber-200'],
                        ['route' => 'invoices.index',       'icon' => '💳', 'label' => 'Factures & Versements',   'bg' => 'hover:bg-blue-600 hover:text-white bg-blue-50 text-blue-950 border-blue-200'],
                        ['route' => 'expenses.create',      'icon' => '💸', 'label' => 'Enregistrer Dépense',    'bg' => 'hover:bg-rose-600 hover:text-white bg-rose-50 text-rose-950 border-rose-200'],
                        ['route' => 'students.index',       'icon' => '🎓', 'label' => 'Répertoire Élèves',       'bg' => 'hover:bg-indigo-600 hover:text-white bg-indigo-50 text-indigo-950 border-indigo-200'],
                        ['route' => 'evaluations.index',    'icon' => '📝', 'label' => 'Saisie des Notes',        'bg' => 'hover:bg-emerald-600 hover:text-white bg-emerald-50 text-emerald-950 border-emerald-200'],
                        ['route' => 'attendances.index',    'icon' => '✔️', 'label' => 'Appel de Présence',      'bg' => 'hover:bg-teal-600 hover:text-white bg-teal-50 text-teal-950 border-teal-200'],
                    ];
                @endphp

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-3">
                    @foreach ($quickLinks as $link)
                        <a href="{{ route($link['route']) }}"
                           class="flex flex-col sm:flex-row items-center justify-center sm:justify-start gap-2.5 p-3.5 rounded-2xl font-bold text-xs transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-md border shadow-2xs {{ $link['bg'] }} group">
                            <span class="text-xl flex-shrink-0 group-hover:scale-110 transition-transform">{{ $link['icon'] }}</span>
                            <span class="text-center sm:text-left leading-tight font-extrabold">{{ $link['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
