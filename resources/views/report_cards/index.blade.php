<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <div>
                <h2 class="font-black text-xl text-purple-950 leading-tight tracking-tight flex items-center gap-2">
                    <span>📄</span>
                    <span>Bulletins de Notes Officiels</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">
                    {{ $activeYear?->name ?? '—' }}
                    &nbsp;·&nbsp; Architecture bivalente : Semestres (Collège/Lycée) &amp; Trimestres (Primaire/Maternelle)
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-purple-50 text-purple-900 border border-purple-200 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Année Active : {{ $activeYear?->name ?? '2025-2026' }}</span>
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50/60 min-h-screen" x-data="{
        activeCycle: '{{ $selectedCycleCode ? strtoupper($selectedCycleCode) : 'all' }}',
        setCycle(code) {
            this.activeCycle = code;
        }
    }">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            {{-- Notification flash --}}
            @if (session('status'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3.5 rounded-2xl flex items-center gap-3 shadow-sm" role="alert">
                    <span class="text-xl">✅</span>
                    <div>
                        <span class="font-black">Succès !</span>
                        <span class="font-medium"> {{ session('status') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-3.5 rounded-2xl flex items-center gap-3 shadow-sm" role="alert">
                    <span class="text-xl">⚠️</span>
                    <div>
                        <span class="font-black">Attention :</span>
                        <span class="font-medium"> {{ session('error') }}</span>
                    </div>
                </div>
            @endif

            {{-- ── SÉLECTEUR D'ONGLETS PAR CYCLE (INTERACTIF & IMMÉDIAT) ────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-2">
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                    {{-- 1. Tous les cycles --}}
                    <button type="button"
                            @click="setCycle('all')"
                            :class="activeCycle === 'all'
                                ? 'bg-gradient-to-r from-purple-950 to-indigo-950 text-white shadow-md ring-2 ring-purple-900/30'
                                : 'bg-slate-50/80 hover:bg-slate-100 text-slate-700 border border-transparent'"
                            class="px-4 py-3 rounded-2xl transition-all duration-200 text-left flex flex-col justify-between group">
                        <div class="flex items-center justify-between">
                            <span class="text-lg">🌟</span>
                            <span :class="activeCycle === 'all' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-900'"
                                  class="text-[10px] font-black px-2 py-0.5 rounded-full font-mono">
                                {{ $totalBulletins }}
                            </span>
                        </div>
                        <div class="mt-2">
                            <div class="text-xs font-black tracking-tight">Tous les Cycles</div>
                            <div :class="activeCycle === 'all' ? 'text-purple-200' : 'text-slate-400'" class="text-[10px] font-semibold">
                                Vue d'ensemble
                            </div>
                        </div>
                    </button>

                    {{-- 2. Maternelle --}}
                    @php $matStats = $cycleStats['MATERNELLE'] ?? ['count' => 0, 'avg' => 0]; @endphp
                    <button type="button"
                            @click="setCycle('MATERNELLE')"
                            :class="activeCycle === 'MATERNELLE'
                                ? 'bg-gradient-to-r from-rose-950 to-pink-900 text-white shadow-md ring-2 ring-rose-900/30'
                                : 'bg-slate-50/80 hover:bg-rose-50/50 text-slate-700 border border-transparent hover:border-rose-200'"
                            class="px-4 py-3 rounded-2xl transition-all duration-200 text-left flex flex-col justify-between group">
                        <div class="flex items-center justify-between">
                            <span class="text-lg">🧸</span>
                            <span :class="activeCycle === 'MATERNELLE' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-900'"
                                  class="text-[10px] font-black px-2 py-0.5 rounded-full font-mono">
                                {{ $matStats['count'] }}
                            </span>
                        </div>
                        <div class="mt-2">
                            <div class="text-xs font-black tracking-tight">Maternelle</div>
                            <div :class="activeCycle === 'MATERNELLE' ? 'text-rose-200' : 'text-slate-400'" class="text-[10px] font-semibold">
                                Trimestres · Éveil
                            </div>
                        </div>
                    </button>

                    {{-- 3. Primaire --}}
                    @php $primStats = $cycleStats['PRIMAIRE'] ?? ['count' => 0, 'avg' => 0]; @endphp
                    <button type="button"
                            @click="setCycle('PRIMAIRE')"
                            :class="activeCycle === 'PRIMAIRE'
                                ? 'bg-gradient-to-r from-emerald-950 to-teal-900 text-white shadow-md ring-2 ring-emerald-900/30'
                                : 'bg-slate-50/80 hover:bg-emerald-50/50 text-slate-700 border border-transparent hover:border-emerald-200'"
                            class="px-4 py-3 rounded-2xl transition-all duration-200 text-left flex flex-col justify-between group">
                        <div class="flex items-center justify-between">
                            <span class="text-lg">🎒</span>
                            <span :class="activeCycle === 'PRIMAIRE' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-900'"
                                  class="text-[10px] font-black px-2 py-0.5 rounded-full font-mono">
                                {{ $primStats['count'] }}
                            </span>
                        </div>
                        <div class="mt-2">
                            <div class="text-xs font-black tracking-tight">Primaire</div>
                            <div :class="activeCycle === 'PRIMAIRE' ? 'text-emerald-200' : 'text-slate-400'" class="text-[10px] font-semibold">
                                Trimestres · Fondam. 1
                            </div>
                        </div>
                    </button>

                    {{-- 4. Collège --}}
                    @php $colStats = $cycleStats['COLLEGE'] ?? ['count' => 0, 'avg' => 0]; @endphp
                    <button type="button"
                            @click="setCycle('COLLEGE')"
                            :class="activeCycle === 'COLLEGE'
                                ? 'bg-gradient-to-r from-purple-950 to-indigo-900 text-white shadow-md ring-2 ring-purple-900/30'
                                : 'bg-slate-50/80 hover:bg-purple-50/50 text-slate-700 border border-transparent hover:border-purple-200'"
                            class="px-4 py-3 rounded-2xl transition-all duration-200 text-left flex flex-col justify-between group">
                        <div class="flex items-center justify-between">
                            <span class="text-lg">🏛️</span>
                            <span :class="activeCycle === 'COLLEGE' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-900'"
                                  class="text-[10px] font-black px-2 py-0.5 rounded-full font-mono">
                                {{ $colStats['count'] }}
                            </span>
                        </div>
                        <div class="mt-2">
                            <div class="text-xs font-black tracking-tight">Collège</div>
                            <div :class="activeCycle === 'COLLEGE' ? 'text-purple-200' : 'text-slate-400'" class="text-[10px] font-semibold">
                                Semestres · 6e à 3e
                            </div>
                        </div>
                    </button>

                    {{-- 5. Lycée --}}
                    @php $lycStats = $cycleStats['LYCEE'] ?? ['count' => 0, 'avg' => 0]; @endphp
                    <button type="button"
                            @click="setCycle('LYCEE')"
                            :class="activeCycle === 'LYCEE'
                                ? 'bg-gradient-to-r from-indigo-950 via-purple-950 to-slate-950 text-white shadow-md ring-2 ring-amber-400/40'
                                : 'bg-slate-50/80 hover:bg-indigo-50/50 text-slate-700 border border-transparent hover:border-indigo-200'"
                            class="px-4 py-3 rounded-2xl transition-all duration-200 text-left flex flex-col justify-between group">
                        <div class="flex items-center justify-between">
                            <span class="text-lg">🎓</span>
                            <span :class="activeCycle === 'LYCEE' ? 'bg-amber-400 text-purple-950' : 'bg-amber-100 text-amber-900'"
                                  class="text-[10px] font-black px-2 py-0.5 rounded-full font-mono">
                                {{ $lycStats['count'] }}
                            </span>
                        </div>
                        <div class="mt-2">
                            <div class="text-xs font-black tracking-tight">Lycée</div>
                            <div :class="activeCycle === 'LYCEE' ? 'text-amber-300' : 'text-slate-400'" class="text-[10px] font-semibold">
                                Semestres · 2nde à Tle
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            {{-- ── BARRE FILTRES & STATS GLOBALES ─────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6">
                <form method="GET" action="{{ route('report-cards.index') }}"
                      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">

                    {{-- Période --}}
                    <div>
                        <label for="filter-period-id" class="block text-[11px] font-black text-slate-600 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>📅 Période Pédagogique</span>
                            @if ($isMultiPeriod)
                                <span class="text-[10px] text-purple-700 font-bold lowercase">par défaut : Période 1</span>
                            @endif
                        </label>
                        <select id="filter-period-id"
                                name="period_id"
                                onchange="this.form.submit()"
                                class="w-full text-sm font-bold rounded-xl border-purple-300 focus:border-purple-600 focus:ring-purple-600 shadow-xs">
                            <option value="" {{ $isMultiPeriod ? 'selected' : '' }}>
                                🌟 Périodes Actuelles (Trimestre 1 &amp; Semestre 1)
                            </option>
                            @php
                                $trimestres = $periods->where('type', 'TRIMESTRE')->sortBy('order');
                                $semestres = $periods->where('type', 'SEMESTRE')->sortBy('order');
                            @endphp
                            @if ($trimestres->isNotEmpty())
                                <optgroup label="── TRIMESTRES (Maternelle &amp; Primaire) ──">
                                    @foreach ($trimestres as $p)
                                        <option value="{{ $p->id }}" {{ (! $isMultiPeriod && $selectedPeriodId == $p->id) ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @if ($semestres->isNotEmpty())
                                <optgroup label="── SEMESTRES (Collège &amp; Lycée) ──">
                                    @foreach ($semestres as $p)
                                        <option value="{{ $p->id }}" {{ (! $isMultiPeriod && $selectedPeriodId == $p->id) ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>

                    {{-- Classe (optionnel) --}}
                    <div>
                        <label for="filter-class-id" class="block text-[11px] font-black text-slate-600 uppercase tracking-wider mb-1.5">
                            🏫 Filtrer par Classe
                        </label>
                        <select id="filter-class-id"
                                name="class_id"
                                onchange="this.form.submit()"
                                class="w-full text-sm font-bold rounded-xl border-purple-300 focus:border-purple-600 focus:ring-purple-600 shadow-xs">
                            <option value="">— Toutes les classes —</option>
                            @php $classesByCycle = $classes->groupBy(fn($c) => $c->level->cycle->name ?? 'Autre'); @endphp
                            @foreach ($classesByCycle as $cycleName => $cycleClasses)
                                <optgroup label="── {{ strtoupper($cycleName) }} ──">
                                    @foreach ($cycleClasses as $cls)
                                        <option value="{{ $cls->id }}" {{ $selectedClassId == $cls->id ? 'selected' : '' }}>
                                            {{ $cls->level->name }} • {{ $cls->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    {{-- Recherche élève --}}
                    <div>
                        <label for="filter-search" class="block text-[11px] font-black text-slate-600 uppercase tracking-wider mb-1.5">
                            🔍 Recherche Rapide
                        </label>
                        <input id="filter-search"
                               type="text"
                               name="search"
                               value="{{ $search }}"
                               placeholder="Nom, prénom ou matricule..."
                               class="w-full text-sm font-bold rounded-xl border-purple-300 focus:border-purple-600 focus:ring-purple-600 shadow-xs" />
                    </div>

                    {{-- Boutons --}}
                    <div class="flex items-center gap-2">
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 bg-purple-900 hover:bg-purple-800 text-white text-sm font-black rounded-xl shadow-xs transition active:scale-95">
                            Filtrer
                        </button>
                        @if ($selectedClassId || $search || ! $isMultiPeriod)
                            <a href="{{ route('report-cards.index') }}"
                               class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-bold rounded-xl transition"
                               title="Réinitialiser les filtres">✕</a>
                        @endif
                    </div>
                </form>

                {{-- Stats globales --}}
                @if ($totalBulletins > 0)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100">
                        <div class="p-3.5 rounded-2xl bg-purple-50/60 border border-purple-100 text-center">
                            <div class="text-[10px] font-black tracking-widest uppercase text-slate-400">Total Bulletins</div>
                            <div class="text-2xl font-black text-purple-950 mt-1">{{ $totalBulletins }}</div>
                            <div class="text-[10px] font-semibold text-purple-700 mt-0.5">Toutes classes confondues</div>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-emerald-50/60 border border-emerald-100 text-center">
                            <div class="text-[10px] font-black tracking-widest uppercase text-slate-400">Taux de Réussite</div>
                            <div class="text-2xl font-black mt-1 {{ ($totalSuccess / $totalBulletins * 100) >= 50 ? 'text-emerald-700' : 'text-orange-600' }}">
                                {{ round($totalSuccess / $totalBulletins * 100) }}<span class="text-base text-slate-400">%</span>
                            </div>
                            <div class="text-[10px] font-semibold text-emerald-800 mt-0.5">{{ $totalSuccess }} élèves admis(es)</div>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-blue-50/60 border border-blue-100 text-center">
                            <div class="text-[10px] font-black tracking-widest uppercase text-slate-400">Moyenne Globale</div>
                            <div class="text-2xl font-black mt-1 {{ $overallAvg >= 10 ? 'text-blue-900' : 'text-red-700' }}">
                                {{ number_format($overallAvg, 2) }}<span class="text-base text-slate-400">/20</span>
                            </div>
                            <div class="text-[10px] font-semibold text-blue-800 mt-0.5">Échelle nationale</div>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-amber-50/60 border border-amber-100 text-center">
                            <div class="text-[10px] font-black tracking-widest uppercase text-slate-400">Période Affichée</div>
                            <div class="text-base font-black text-amber-950 mt-1 truncate">
                                {{ $selectedPeriod?->name ?? 'Période 1 (T1 & S1)' }}
                            </div>
                            <div class="text-[10px] font-semibold text-amber-800 mt-0.5">
                                {{ $isMultiPeriod ? 'Adaptée au cycle' : ($selectedPeriod?->type ?? '—') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ── LISTE GROUPÉE : NIVEAU → CLASSE (CYCLE-AWARE) ────────────────────────── --}}
            <div class="space-y-8">
                @php $visibleSectionsCount = 0; @endphp

                @forelse ($grouped as $levelName => $classGroup)
                    @php
                        $firstRcOfLevel = $classGroup->first()?->first();
                        $cycleCode = $levelCycleMap[$levelName]['code'] ?? ($firstRcOfLevel?->schoolClass?->level?->cycle?->code ?? 'COLLEGE');
                        $cycleName = $levelCycleMap[$levelName]['name'] ?? ($firstRcOfLevel?->schoolClass?->level?->cycle?->name ?? 'Secondaire');
                        $levelTotal = $classGroup->sum(fn($rcs) => $rcs->count());

                        // Configuration visuelle du niveau selon son cycle
                        if ($cycleCode === 'MATERNELLE') {
                            $levelHeaderClass = 'bg-gradient-to-r from-rose-950 via-purple-900 to-amber-950 text-white';
                            $levelBadge = '🧸 Préscolaire';
                            $levelBorder = 'border-rose-200';
                            $levelBadgeClass = 'bg-rose-500/20 text-rose-200 border-rose-400/30';
                        } elseif ($cycleCode === 'PRIMAIRE') {
                            $levelHeaderClass = 'bg-gradient-to-r from-emerald-950 via-teal-900 to-slate-950 text-white';
                            $levelBadge = '🎒 Primaire Élémentaire';
                            $levelBorder = 'border-emerald-200';
                            $levelBadgeClass = 'bg-emerald-500/20 text-emerald-200 border-emerald-400/30';
                        } elseif ($cycleCode === 'COLLEGE') {
                            $levelHeaderClass = 'bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white';
                            $levelBadge = '🏛️ Collège (1er Cycle)';
                            $levelBorder = 'border-purple-200';
                            $levelBadgeClass = 'bg-purple-500/20 text-purple-200 border-purple-400/30';
                        } else {
                            $levelHeaderClass = 'bg-gradient-to-r from-indigo-950 via-purple-950 to-slate-950 text-white';
                            $levelBadge = '🎓 Lycée (2nd Cycle)';
                            $levelBorder = 'border-indigo-200';
                            $levelBadgeClass = 'bg-amber-500/20 text-amber-200 border-amber-400/30';
                        }
                    @endphp

                    <div x-show="activeCycle === 'all' || activeCycle === '{{ $cycleCode }}'"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-98"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         class="bg-white rounded-3xl border {{ $levelBorder }} shadow-xs overflow-hidden">

                        {{-- En-tête Niveau aux couleurs du cycle --}}
                        <div class="px-6 py-4.5 {{ $levelHeaderClass }} flex items-center justify-between flex-wrap gap-3">
                            <div class="flex items-center gap-3.5">
                                <span class="text-2xl">{{ match($cycleCode) { 'MATERNELLE' => '🧸', 'PRIMAIRE' => '🎒', 'COLLEGE' => '🏛️', 'LYCEE' => '🎓', default => '🏫' } }}</span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-black tracking-widest uppercase px-2 py-0.5 rounded-full border {{ $levelBadgeClass }}">
                                            {{ $levelBadge }}
                                        </span>
                                    </div>
                                    <h3 class="text-base font-black uppercase tracking-wider text-white mt-1">{{ $levelName }}</h3>
                                    <p class="text-[11px] text-white/70 font-semibold">
                                        {{ $classGroup->count() }} classe(s) · {{ $levelTotal }} bulletin(s) généré(s)
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-black bg-white/10 text-white px-3.5 py-1.5 rounded-full border border-white/20 shadow-inner">
                                    {{ $levelTotal }} élève(s) au total
                                </span>
                            </div>
                        </div>

                        {{-- Classes du niveau --}}
                        <div class="divide-y divide-slate-100">
                            @foreach ($classGroup as $className => $reportCards)
                                @php
                                    $firstRc = $reportCards->first();
                                    $classModelId = $firstRc?->class_id;
                                    $classAvg = round($reportCards->avg(fn($rc) => (float)$rc->general_average), 2);
                                    $classSuccess = $reportCards->filter(fn($rc) => (float)$rc->general_average >= 10)->count();
                                    $classSchool = $firstRc?->schoolClass;
                                    $classPeriodName = $firstRc?->period?->name ?? '—';
                                @endphp

                                <div class="p-6">
                                    {{-- Titre Classe + actions --}}
                                    <div class="flex items-center justify-between flex-wrap gap-3 pb-4 mb-4 border-b border-slate-100">
                                        <div class="flex items-center gap-2.5 flex-wrap">
                                            <span class="text-base font-black text-slate-900 flex items-center gap-1.5">
                                                <span>{{ match($cycleCode) { 'MATERNELLE' => '🧸', 'PRIMAIRE' => '🎒', 'COLLEGE' => '🏛️', default => '🎓' } }}</span>
                                                <span>Classe : {{ $className }}</span>
                                            </span>
                                            <span class="text-[11px] font-black text-purple-900 bg-purple-50 px-2.5 py-0.5 rounded-full border border-purple-200">
                                                {{ $classPeriodName }}
                                            </span>
                                            <span class="text-[11px] font-extrabold text-slate-500 bg-slate-50 px-2.5 py-0.5 rounded-full border border-slate-200">
                                                {{ $reportCards->count() }} élève(s)
                                            </span>
                                            {{-- Moy. classe badge --}}
                                            <span class="text-[11px] font-black px-2.5 py-0.5 rounded-full
                                                {{ $classAvg >= 10 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                                                Moy. {{ number_format($classAvg, 2) }}/20
                                            </span>
                                            <span class="text-[11px] font-extrabold text-slate-600 bg-slate-50 px-2.5 py-0.5 rounded-full border border-slate-200">
                                                Réussite : {{ $classSuccess }}/{{ $reportCards->count() }}
                                            </span>
                                        </div>

                                        @if ($classModelId)
                                            @php
                                                $targetPeriodId = ($classSchool && $selectedPeriod && $classSchool->expectedPeriodType() !== $selectedPeriod->type)
                                                    ? ($periods->where('type', $classSchool->expectedPeriodType())->where('order', 1)->first()?->id ?? $selectedPeriodId)
                                                    : ($selectedPeriodId ?? $firstRc?->period_id);
                                            @endphp
                                            <div class="flex items-center gap-2">
                                                {{-- Générer --}}
                                                <form method="POST" action="{{ route('report-cards.generate') }}">
                                                    @csrf
                                                    <input type="hidden" name="class_id" value="{{ $classModelId }}" />
                                                    <input type="hidden" name="period_id" value="{{ $targetPeriodId }}" />
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1.5 text-xs font-black px-3.5 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-purple-950 shadow-xs active:scale-95 transition-all">
                                                        <span>⚙️</span>
                                                        <span>Générer</span>
                                                    </button>
                                                </form>
                                                {{-- Publier --}}
                                                <form method="POST" action="{{ route('report-cards.publish') }}">
                                                    @csrf
                                                    <input type="hidden" name="class_id" value="{{ $classModelId }}" />
                                                    <input type="hidden" name="period_id" value="{{ $targetPeriodId }}" />
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1.5 text-xs font-black px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs active:scale-95 transition-all">
                                                        <span>✅</span>
                                                        <span>Publier</span>
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Tableau classement des élèves --}}
                                    <div class="overflow-x-auto rounded-2xl border border-slate-200/80">
                                        <table class="min-w-full divide-y divide-slate-100">
                                            <thead class="bg-slate-50/80">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-wider">Rang</th>
                                                    <th class="px-4 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-wider">Matricule</th>
                                                    <th class="px-4 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-wider">Élève</th>
                                                    <th class="px-4 py-3 text-center text-[10px] font-black text-slate-400 uppercase tracking-wider">Moy. Générale</th>
                                                    <th class="px-4 py-3 text-center text-[10px] font-black text-slate-400 uppercase tracking-wider">Mention / Appréciation</th>
                                                    <th class="px-4 py-3 text-center text-[10px] font-black text-slate-400 uppercase tracking-wider">Statut</th>
                                                    <th class="px-4 py-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-slate-100">
                                                @foreach ($reportCards as $rc)
                                                    @php $avg = (float)$rc->general_average; @endphp
                                                    <tr class="hover:bg-purple-50/20 transition-colors">
                                                        <td class="px-4 py-3">
                                                            @if($rc->rank === 1)
                                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-amber-100 text-base shadow-xs" title="1er de la classe">🥇</span>
                                                            @elseif($rc->rank === 2)
                                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-100 text-base shadow-xs" title="2e de la classe">🥈</span>
                                                            @elseif($rc->rank === 3)
                                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-orange-100 text-base shadow-xs" title="3e de la classe">🥉</span>
                                                            @else
                                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-50 text-slate-600 font-black text-xs border border-slate-200">
                                                                    {{ $rc->rank }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <span class="text-xs font-mono font-black text-purple-900 bg-purple-50 px-2.5 py-1 rounded-lg border border-purple-200">
                                                                {{ $rc->student->matricule }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <div class="text-sm font-black text-slate-900">
                                                                {{ strtoupper($rc->student->last_name) }} {{ $rc->student->first_name }}
                                                            </div>
                                                            <div class="text-[11px] text-slate-400 font-semibold">
                                                                {{ $rc->student->gender ?? '—' }} · Né(e) le {{ $rc->student->birth_date ? $rc->student->birth_date->format('d/m/Y') : '—' }}
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            <span class="text-lg font-black {{ $avg >= 10 ? 'text-emerald-700' : 'text-red-700' }}">
                                                                {{ number_format($avg, 2) }}
                                                            </span>
                                                            <span class="text-xs font-semibold text-slate-400">/20</span>
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            <span class="px-3 py-1 text-xs font-black rounded-full border
                                                                {{ $avg >= 16 ? 'bg-emerald-50 text-emerald-800 border-emerald-200' :
                                                                   ($avg >= 12 ? 'bg-blue-50 text-blue-800 border-blue-200' :
                                                                   ($avg >= 10 ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-red-50 text-red-800 border-red-200')) }}">
                                                                {{ $rc->appreciationLabel }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            @if ($rc->is_published)
                                                                <span class="inline-flex items-center gap-1.5 text-xs font-black text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                                                                    <span>Publié</span>
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center gap-1.5 text-xs font-black text-amber-700 bg-amber-50 px-3 py-1 rounded-full border border-amber-200">
                                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>
                                                                    <span>Brouillon</span>
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 text-right">
                                                            <div class="inline-flex items-center justify-end gap-1.5">
                                                                <a href="{{ route('report-cards.show', $rc) }}"
                                                                   class="inline-flex items-center gap-1 text-xs font-black px-3 py-1.5 rounded-xl bg-purple-950 text-white hover:bg-purple-800 active:scale-95 transition-all shadow-xs"
                                                                   title="Consulter le bulletin en ligne">
                                                                    <span>👁️</span>
                                                                    <span>Voir</span>
                                                                </a>
                                                                <a href="{{ route('report-cards.pdf', $rc) }}"
                                                                   target="_blank"
                                                                   class="inline-flex items-center gap-1 text-xs font-black px-3 py-1.5 rounded-xl bg-red-600 text-white hover:bg-red-700 active:scale-95 transition-all shadow-xs"
                                                                   title="Télécharger l'imprimé officiel en PDF">
                                                                    <span>📥</span>
                                                                    <span>PDF</span>
                                                                </a>
                                                            </div>
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
                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-16 text-center">
                        <div class="text-5xl mb-4">📋</div>
                        <h3 class="text-base font-black text-slate-800">Aucun bulletin trouvé</h3>
                        <p class="text-sm text-slate-400 font-medium mt-1 max-w-sm mx-auto">
                            @if ($search || $selectedClassId)
                                Aucun résultat pour ces filtres. <a href="{{ route('report-cards.index') }}" class="text-purple-700 font-black underline">Réinitialiser</a>
                            @else
                                Aucun bulletin généré pour les critères actuels. Sélectionnez une classe ci-dessus et cliquez sur ⚙️ Générer.
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
