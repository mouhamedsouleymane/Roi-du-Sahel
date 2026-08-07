<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-purple-950 leading-tight">
                    Tableau de Bord
                </h2>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-2">
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
            <div class="hidden sm:flex items-center gap-2 text-xs font-semibold text-slate-500">
                <span class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs">
                    📅 {{ now()->translatedFormat('l d F Y') }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8 animate-fade-in-up">

            {{-- ── HERO BRAND BANNER ────────────────────────────────────── --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 p-8 text-white shadow-2xl shadow-purple-950/30 border border-purple-800/40">
                <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-10 -top-10 w-72 h-72 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-wrap justify-between items-center gap-6">
                    <div class="space-y-2 max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/20 border border-amber-400/40 text-amber-300 text-xs font-extrabold tracking-widest uppercase animate-pulse-glow">
                            👑 COMPLEXE SCOLAIRE PRIVÉ
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-black text-white tracking-wide">
                            Bienvenue sur <span class="text-gold-gradient">ROIS DU SAHEL</span>
                        </h1>
                        <p class="text-sm text-purple-200/90 leading-relaxed">
                            Plateforme de gestion globale de la vie scolaire, des évaluations, de la présence et de la comptabilité.
                        </p>
                    </div>

                    {{-- Devise officielle --}}
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl text-center space-y-1 shadow-lg">
                        <div class="text-[10px] uppercase font-extrabold text-amber-300 tracking-widest">Devise de l'Établissement</div>
                        <div class="text-base font-extrabold tracking-wider text-white">
                            DISCIPLINE • TRAVAIL • SUCCÈS
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── KPI CARDS ────────────────────────────────────────────── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">

                {{-- Élèves Inscrits --}}
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-5 flex items-center gap-4 hover:-translate-y-1 hover:shadow-xl hover:border-purple-300 transition-all duration-300 group">
                    <div class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-800 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-110 transition-transform">🎓</div>
                    <div>
                        <div class="text-3xl font-black text-purple-950">{{ number_format($totalEnrolled) }}</div>
                        <div class="text-xs text-slate-600 font-bold">Élèves inscrits</div>
                        <div class="text-[11px] text-slate-400 font-medium">sur {{ number_format($totalStudents) }} au total</div>
                    </div>
                </div>

                {{-- Enseignants --}}
                <div class="bg-white rounded-2xl shadow-sm border border-amber-100 p-5 flex items-center gap-4 hover:-translate-y-1 hover:shadow-xl hover:border-amber-300 transition-all duration-300 group">
                    <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-110 transition-transform">👨‍🏫</div>
                    <div>
                        <div class="text-3xl font-black text-amber-950">{{ number_format($totalTeachers) }}</div>
                        <div class="text-xs text-slate-600 font-bold">Enseignants actifs</div>
                        <div class="text-[11px] text-slate-400 font-medium">corps professoral</div>
                    </div>
                </div>

                {{-- Classes --}}
                <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 p-5 flex items-center gap-4 hover:-translate-y-1 hover:shadow-xl hover:border-emerald-300 transition-all duration-300 group">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-110 transition-transform">🏫</div>
                    <div>
                        <div class="text-3xl font-black text-emerald-950">{{ number_format($totalClasses) }}</div>
                        <div class="text-xs text-slate-600 font-bold">Classes ouvertes</div>
                        <div class="text-[11px] text-slate-400 font-medium">année en cours</div>
                    </div>
                </div>

                {{-- Bulletins Publiés --}}
                <div class="bg-white rounded-2xl shadow-sm border border-indigo-100 p-5 flex items-center gap-4 hover:-translate-y-1 hover:shadow-xl hover:border-indigo-300 transition-all duration-300 group">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-100 text-indigo-800 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-110 transition-transform">📄</div>
                    <div>
                        <div class="text-3xl font-black text-indigo-950">{{ number_format($publishedBulletins) }}</div>
                        <div class="text-xs text-slate-600 font-bold">Bulletins publiés</div>
                        <div class="text-[11px] text-slate-400 font-medium">{{ $periodEvaluations }} évaluations</div>
                    </div>
                </div>

            </div>

            {{-- ── RÉSUMÉ FINANCIER ────────────────────────────────────── --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 flex items-center gap-4 hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center text-xl flex-shrink-0">🧾</div>
                    <div>
                        <div class="text-xl font-extrabold text-blue-900">{{ number_format($totalRevenueDue, 0, ',', ' ') }} FCFA</div>
                        <div class="text-xs text-slate-500 font-bold">Frais totaux dus</div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-emerald-200/80 p-5 flex items-center gap-4 hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-xl flex-shrink-0">✅</div>
                    <div>
                        <div class="text-xl font-extrabold text-emerald-900">{{ number_format($totalRevenuePaid, 0, ',', ' ') }} FCFA</div>
                        <div class="text-xs text-slate-500 font-bold">Montants recouvrés</div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-red-200/80 p-5 flex items-center gap-4 hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-xl bg-red-100 text-red-800 flex items-center justify-center text-xl flex-shrink-0">⚠️</div>
                    <div>
                        <div class="text-xl font-extrabold text-red-800">{{ number_format($totalUnpaid, 0, ',', ' ') }} FCFA</div>
                        <div class="text-xs text-slate-500 font-bold">Reste impayé</div>
                    </div>
                </div>
            </div>

            {{-- ── SECTION CENTRALE (Graphiques + Accès Rapides) ────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Inscriptions par Cycle (Barres de progression élégantes) --}}
                <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-200/80 p-7">
                    <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-lg font-black text-slate-900">📊 Répartition des Inscrits par Cycle</h3>
                            <p class="text-xs text-slate-400">Effectifs de l'année {{ $activeYear?->name ?? '–' }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-purple-50 text-purple-800 text-xs font-extrabold">
                            {{ $totalEnrolled }} inscrits
                        </span>
                    </div>

                    @php $maxCycle = $enrollmentsByCycle->max('total') ?: 1; @endphp

                    <div class="space-y-5">
                        @forelse ($enrollmentsByCycle as $row)
                            @php $pct = round(($row->total / $maxCycle) * 100); @endphp
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <span class="text-sm font-extrabold text-slate-800">{{ $row->cycle_name }}</span>
                                    <span class="text-sm font-extrabold text-purple-900 bg-purple-50 px-2.5 py-0.5 rounded-md">{{ $row->total }} élève(s)</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-3.5 p-0.5 overflow-hidden border border-slate-200/50">
                                    <div class="h-full rounded-full bg-gradient-to-r from-purple-800 via-purple-600 to-amber-500 transition-all duration-700 shadow-sm"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400 italic text-center py-6">Aucune inscription enregistrée.</p>
                        @endforelse
                    </div>

                    {{-- Répartition Genre --}}
                    <div class="mt-8 pt-5 border-t border-slate-100 grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-blue-50/80 rounded-2xl border border-blue-100">
                            <div class="text-3xl font-black text-blue-900">{{ number_format($totalBoys) }}</div>
                            <div class="text-xs text-blue-700 font-extrabold mt-1">👦 Garçons</div>
                        </div>
                        <div class="text-center p-4 bg-pink-50/80 rounded-2xl border border-pink-100">
                            <div class="text-3xl font-black text-pink-900">{{ number_format($totalGirls) }}</div>
                            <div class="text-xs text-pink-700 font-extrabold mt-1">👧 Filles</div>
                        </div>
                    </div>
                </div>

                {{-- Accès Rapides Stylisés --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-7 flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 mb-1">⚡ Raccourcis Rapides</h3>
                        <p class="text-xs text-slate-400 mb-5">Accès direct aux modules clé</p>

                        @php
                            $quickLinks = [
                                ['route' => 'enrollments.create', 'icon' => '➕', 'label' => 'Nouvelle inscription',  'bg' => 'hover:bg-purple-900 hover:text-white bg-purple-50 text-purple-900'],
                                ['route' => 'students.index',     'icon' => '🎓', 'label' => 'Répertoire élèves',    'bg' => 'hover:bg-purple-900 hover:text-white bg-indigo-50 text-indigo-900'],
                                ['route' => 'teachers.index',     'icon' => '👨‍🏫', 'label' => 'Corps professoral',    'bg' => 'hover:bg-purple-900 hover:text-white bg-amber-50 text-amber-900'],
                                ['route' => 'evaluations.index',  'icon' => '📝', 'label' => 'Saisie des notes',    'bg' => 'hover:bg-purple-900 hover:text-white bg-emerald-50 text-emerald-900'],
                                ['route' => 'report-cards.index', 'icon' => '📄', 'label' => 'Bulletins de notes',   'bg' => 'hover:bg-purple-900 hover:text-white bg-rose-50 text-rose-900'],
                                ['route' => 'invoices.index',     'icon' => '💳', 'label' => 'Factures & Versements', 'bg' => 'hover:bg-purple-900 hover:text-white bg-blue-50 text-blue-900'],
                                ['route' => 'attendances.index',  'icon' => '✔️', 'label' => 'Appel de présence',    'bg' => 'hover:bg-purple-900 hover:text-white bg-teal-50 text-teal-900'],
                            ];
                        @endphp

                        <div class="space-y-2">
                            @foreach ($quickLinks as $link)
                                <a href="{{ route($link['route']) }}"
                                   class="flex items-center gap-3 px-4 py-3 rounded-2xl font-bold text-xs transition-all duration-300 transform hover:scale-[1.02] shadow-2xs {{ $link['bg'] }}">
                                    <span class="text-lg">{{ $link['icon'] }}</span>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── TAUX DE REMPLISSAGE DES CLASSES ─────────────────────── --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-7">
                <h3 class="text-lg font-black text-slate-900 mb-1">🏫 Taux de Remplissage des Classes</h3>
                <p class="text-xs text-slate-400 mb-6">Indicateur visuel d'occupation par classe</p>

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

        </div>
    </div>
</x-app-layout>
