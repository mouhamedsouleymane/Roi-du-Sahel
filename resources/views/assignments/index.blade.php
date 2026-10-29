<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                    <span>🔗</span> Affectation des Enseignants
                </h2>
                <p class="text-xs font-bold text-slate-500 mt-1">
                    Répartition et attribution des matières par <span class="text-purple-950 font-extrabold">Cycle
                        d'Enseignement</span> — Année : <span
                        class="text-purple-950 font-extrabold">{{ $activeYear?->name ?? 'Non définie' }}</span>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('teachers.index') }}"
                    class="px-4 py-2 text-xs font-black bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl transition shadow-sm flex items-center gap-1.5">
                    <span>👨‍🏫</span> Corps Professoral
                </a>
                <a href="#classes-coverage"
                    class="px-4 py-2 text-xs font-black bg-purple-900 hover:bg-purple-800 text-amber-300 rounded-xl transition shadow-sm flex items-center gap-1.5">
                    <span>⚡</span> Gérer par Classe
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8 animate-fade-in-up">

            {{-- Notifications --}}
            @if (session('status'))
                <div
                    class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm font-bold flex items-center gap-2 shadow-xs">
                    <span>✅</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div
                    class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-2xl text-sm font-bold space-y-1 shadow-xs">
                    <div class="flex items-center gap-2 text-red-900 font-extrabold text-base">
                        <span>⚠️</span>
                        <span>Erreur lors de l'affectation</span>
                    </div>
                    <ul class="list-disc list-inside text-xs text-red-700 font-semibold space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 5 Cartes KPIs Globales --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div
                    class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-md hover:-translate-y-1 transition duration-300">
                    <div class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1">Total Affectations
                    </div>
                    <div class="text-2xl sm:text-3xl font-black text-purple-950">{{ $totalAssignments }}</div>
                    <div class="text-[11px] text-slate-500 font-bold mt-1">Attributions actives</div>
                </div>
                <div
                    class="bg-white p-5 rounded-3xl border border-emerald-200/80 shadow-md hover:-translate-y-1 transition duration-300">
                    <div class="text-[11px] font-black uppercase tracking-wider text-emerald-600 mb-1">Enseignants
                        Assignés</div>
                    <div class="text-2xl sm:text-3xl font-black text-emerald-700">{{ $assignedTeachersCount }}</div>
                    <div class="text-[11px] text-slate-500 font-bold mt-1">En poste cette année</div>
                </div>
                <div
                    class="bg-white p-5 rounded-3xl border border-indigo-200/80 shadow-md hover:-translate-y-1 transition duration-300">
                    <div class="text-[11px] font-black uppercase tracking-wider text-indigo-600 mb-1">Classes Couvertes
                    </div>
                    <div class="text-2xl sm:text-3xl font-black text-indigo-700">{{ $coveredClassesCount }}
                        <span class="text-base text-slate-400 font-bold">/ {{ $totalClassesCount }}</span>
                    </div>
                    <div class="text-[11px] text-slate-500 font-bold mt-1">Classes avec cours</div>
                </div>
                <div
                    class="bg-white p-5 rounded-3xl border border-amber-200/80 shadow-md hover:-translate-y-1 transition duration-300">
                    <div class="text-[11px] font-black uppercase tracking-wider text-amber-600 mb-1">Cycles Actifs</div>
                    <div class="text-2xl sm:text-3xl font-black text-amber-900">{{ $coveredCyclesCount }}</div>
                    <div class="text-[11px] text-slate-500 font-bold mt-1">Niveaux représentés</div>
                </div>
                @php
                    $totalMissing = collect($classCoverage)->sum('missing');
                @endphp
                <div
                    class="bg-white p-5 rounded-3xl border {{ $totalMissing > 0 ? 'border-rose-200/80' : 'border-emerald-200/80' }} shadow-md hover:-translate-y-1 transition duration-300">
                    <div
                        class="text-[11px] font-black uppercase tracking-wider {{ $totalMissing > 0 ? 'text-rose-600' : 'text-emerald-600' }} mb-1">
                        Matières Manquantes</div>
                    <div
                        class="text-2xl sm:text-3xl font-black {{ $totalMissing > 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                        {{ $totalMissing }}</div>
                    <div class="text-[11px] text-slate-500 font-bold mt-1">
                        {{ $totalMissing === 0 ? '✅ Couverture complète' : 'À affecter' }}
                    </div>
                </div>
            </div>

            {{-- Bandeau règle pédagogique --}}
            <div
                class="p-4 bg-purple-50 border border-purple-200/80 rounded-2xl flex items-start gap-3 text-xs text-purple-950 shadow-xs">
                <span class="text-lg">💡</span>
                <div>
                    <strong class="font-extrabold block text-sm text-purple-950">Règle pédagogique par cycle :</strong>
                    <p class="font-medium text-purple-900 mt-0.5">
                        Tout enseignant rattaché aux cycles <strong>Maternelle</strong> ou <strong>Primaire</strong>
                        (enseignement polyvalent) ne peut être affecté qu'à <strong>une seule classe</strong> par année
                        scolaire. Les enseignants des cycles <strong>Collège</strong> et <strong>Lycée</strong> peuvent
                        être affectés à plusieurs classes par discipline.
                    </p>
                </div>
            </div>

            {{-- Barre de Filtres & Recherche --}}
            <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80">
                <form method="GET" action="{{ route('assignments.index') }}"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
                    <!-- Recherche textuelle -->
                    <div class="lg:col-span-2">
                        <label for="search"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Recherche</label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                            placeholder="Nom enseignant, matricule, classe, matière…"
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500 placeholder-slate-400" />
                    </div>

                    <!-- Filtre par Cycle -->
                    <div>
                        <label for="cycle_id"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Cycle</label>
                        <select id="cycle_id" name="cycle_id"
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Tous les cycles --</option>
                            @foreach ($cycles as $cyc)
                                <option value="{{ $cyc->id }}"
                                    {{ request('cycle_id') == $cyc->id ? 'selected' : '' }}>
                                    {{ $cyc->name }} ({{ $cyc->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtre par Classe -->
                    <div>
                        <label for="class_id"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Classe</label>
                        <select id="class_id" name="class_id"
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Toutes les classes --</option>
                            @foreach ($classes as $cls)
                                <option value="{{ $cls->id }}"
                                    {{ request('class_id') == $cls->id ? 'selected' : '' }}>
                                    {{ $cls->name }} ({{ $cls->level->cycle->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtre par Enseignant -->
                    <div>
                        <label for="teacher_id"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Enseignant</label>
                        <select id="teacher_id" name="teacher_id"
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Tous les enseignants --</option>
                            @foreach ($teachers as $tch)
                                <option value="{{ $tch->id }}"
                                    {{ request('teacher_id') == $tch->id ? 'selected' : '' }}>
                                    {{ $tch->user->name }} ({{ $tch->matricule }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Boutons -->
                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="w-full py-3 px-4 bg-purple-900 hover:bg-purple-800 text-white font-black text-xs uppercase tracking-wider rounded-2xl shadow-md transition">
                            Filtrer
                        </button>
                        @if (request()->hasAny(['search', 'cycle_id', 'class_id', 'teacher_id', 'subject_id']))
                            <a href="{{ route('assignments.index') }}"
                                class="py-3 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-2xl transition font-bold text-xs flex items-center justify-center"
                                title="Réinitialiser">
                                ✕
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- Couverture des Classes par Cycle           --}}
            {{-- ══════════════════════════════════════════ --}}
            @if ($classes->count() > 0 && !request()->hasAny(['search', 'cycle_id', 'class_id', 'teacher_id', 'subject_id']))
                <div id="classes-coverage"
                    class="bg-white shadow-xl rounded-3xl border border-slate-200/80 overflow-hidden">
                    <div
                        class="px-8 py-5 bg-gradient-to-r from-slate-800 to-slate-900 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span
                                class="w-10 h-10 bg-amber-400 text-purple-950 rounded-2xl flex items-center justify-center font-black text-lg">📊</span>
                            <div>
                                <div class="text-sm font-black text-white">État de Couverture des Classes & Affectations
                                    Rapides</div>
                                <div class="text-xs font-bold text-slate-400 mt-0.5">Cliquez sur <span
                                        class="text-emerald-400 font-extrabold">⚡ Gérer</span> sur une classe pour lui
                                    affecter ses cours et ses enseignants</div>
                            </div>
                        </div>
                        <div class="text-xs font-black text-slate-400">
                            {{ $coveredClassesCount }} / {{ $totalClassesCount }} classes couvertes
                        </div>
                    </div>
                    <div class="p-6">
                        @php
                            $coverageByGroupCycle = collect($classCoverage)->groupBy(
                                fn($item) => $item['class']->level->cycle->name ?? 'Autre',
                            );
                        @endphp
                        <div class="space-y-6">
                            @foreach ($coverageByGroupCycle as $cycleName => $items)
                                <div>
                                    <div
                                        class="text-[11px] font-black uppercase tracking-widest text-slate-500 mb-3 flex items-center gap-2">
                                        <span class="w-1 h-4 bg-purple-600 rounded-full inline-block"></span>
                                        {{ $cycleName }}
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                                        @foreach ($items as $coverage)
                                            @php
                                                $pct = $coverage['percent'];
                                                $barColor =
                                                    $pct >= 100
                                                        ? 'bg-emerald-500'
                                                        : ($pct >= 50
                                                            ? 'bg-amber-500'
                                                            : 'bg-rose-500');
                                                $textColor =
                                                    $pct >= 100
                                                        ? 'text-emerald-700'
                                                        : ($pct >= 50
                                                            ? 'text-amber-700'
                                                            : 'text-rose-700');
                                                $bgColor =
                                                    $pct >= 100
                                                        ? 'bg-emerald-50 border-emerald-200'
                                                        : ($pct >= 50
                                                            ? 'bg-amber-50 border-amber-200'
                                                            : 'bg-rose-50 border-rose-200');
                                            @endphp
                                            <div class="p-4 rounded-2xl border {{ $bgColor }} space-y-2.5">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <div class="text-sm font-black text-slate-900">
                                                            {{ $coverage['class']->name }}</div>
                                                        <div class="text-[10px] text-slate-500 font-bold">
                                                            {{ $coverage['class']->level->name }}</div>
                                                    </div>
                                                    <span
                                                        class="text-sm font-black {{ $textColor }}">{{ $pct }}%</span>
                                                </div>
                                                {{-- Progress bar --}}
                                                <div class="w-full bg-slate-200 rounded-full h-1.5">
                                                    <div class="{{ $barColor }} h-1.5 rounded-full transition-all duration-500"
                                                        style="width: {{ min(100, $pct) }}%"></div>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between text-[10px] font-bold text-slate-600 pt-1 border-t border-slate-200/60">
                                                    <span>{{ $coverage['assigned'] }} / {{ $coverage['configured'] }}
                                                        matières</span>
                                                    <a href="{{ route('assignments.create', ['class_id' => $coverage['class']->id]) }}"
                                                        class="px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-950 hover:bg-emerald-200 border border-emerald-300 transition flex items-center gap-1 font-black text-[11px] shadow-2xs">
                                                        <span>⚡</span>
                                                        <span>Gérer</span>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- ══════════════════════════════════════════ --}}
            {{-- Affichage Regroupé par Cycle               --}}
            {{-- ══════════════════════════════════════════ --}}
            @php
                $colorClasses = [
                    'Violet' => [
                        'badge' => 'bg-purple-600 text-white',
                        'border' => 'border-purple-200',
                        'pill' => 'bg-purple-100 text-purple-900',
                    ],
                    'Jaune' => [
                        'badge' => 'bg-amber-500 text-purple-950 font-black',
                        'border' => 'border-amber-200',
                        'pill' => 'bg-amber-100 text-amber-900',
                    ],
                    'Vert' => [
                        'badge' => 'bg-emerald-600 text-white',
                        'border' => 'border-emerald-200',
                        'pill' => 'bg-emerald-100 text-emerald-900',
                    ],
                    'Bleu' => [
                        'badge' => 'bg-blue-600 text-white',
                        'border' => 'border-blue-200',
                        'pill' => 'bg-blue-100 text-blue-900',
                    ],
                    'Rouge' => [
                        'badge' => 'bg-rose-600 text-white',
                        'border' => 'border-rose-200',
                        'pill' => 'bg-rose-100 text-rose-900',
                    ],
                ];
            @endphp

            <div class="space-y-8">
                @forelse ($assignmentsByCycle as $cycleName => $cycleAssignments)
                    @php
                        $cycleModel =
                            $cycleAssignments->first()?->schoolClass?->level?->cycle ??
                            $cycles->firstWhere('name', $cycleName);
                        $tshirtColor = $cycleModel?->uniform_tshirt_color ?? 'Violet';
                        $colorConfig = $colorClasses[$tshirtColor] ?? [
                            'badge' => 'bg-purple-900 text-white',
                            'border' => 'border-purple-200',
                            'pill' => 'bg-purple-100 text-purple-900',
                        ];
                        $isPolyvalent = $cycleModel
                            ? in_array(
                                strtolower(trim($cycleModel->name)),
                                \App\Models\Teacher::polyvalentCycleNames(),
                                strict: true,
                            )
                            : false;
                        $distinctClassesCount = $cycleAssignments->pluck('class_id')->unique()->count();
                        $distinctTeachersCount = $cycleAssignments->pluck('teacher_id')->unique()->count();

                        // Coverage for this cycle's classes
$cycleClassCoverage = collect($classCoverage)->filter(
    fn($c) => $c['class']->level->cycle->name === $cycleName,
);
$cycleMissing = $cycleClassCoverage->sum('missing');
                    @endphp

                    <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                        {{-- En-tête de Section Cycle --}}
                        <div
                            class="p-6 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-md">
                            <div class="flex items-center gap-3.5">
                                <span
                                    class="w-12 h-12 rounded-2xl bg-amber-400 text-purple-950 font-black text-xl flex items-center justify-center shadow-xs shrink-0">
                                    🏫
                                </span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-amber-300">
                                            Cycle d'Enseignement
                                        </span>
                                        @if ($cycleModel?->code)
                                            <span
                                                class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase {{ $colorConfig['badge'] }}">
                                                {{ $cycleModel->code }}
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-xl font-black text-white leading-tight mt-0.5">
                                        {{ $cycleName }}
                                    </h3>
                                    <div
                                        class="flex flex-wrap items-center gap-3 text-xs text-purple-200 font-bold mt-1">
                                        <span>
                                            📚 Mode :
                                            @if ($isPolyvalent)
                                                <strong class="text-emerald-300">Polyvalent (1 seule classe
                                                    max)</strong>
                                            @else
                                                <strong class="text-amber-300">Spécialisé (Par matière)</strong>
                                            @endif
                                        </span>
                                        @if ($cycleMissing > 0)
                                            <span class="text-rose-300">
                                                ⚠️ {{ $cycleMissing }} matière{{ $cycleMissing > 1 ? 's' : '' }} non
                                                affectée{{ $cycleMissing > 1 ? 's' : '' }}
                                            </span>
                                        @else
                                            <span class="text-emerald-300">✅ Cycle complet</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Badges statistiques du cycle --}}
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <div
                                    class="px-3.5 py-2 bg-purple-900/80 rounded-2xl border border-purple-700/60 flex items-center gap-2">
                                    <span class="text-base">📋</span>
                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-wider text-purple-300">
                                            Affectations</div>
                                        <div class="text-sm font-black text-white leading-tight">
                                            {{ $cycleAssignments->count() }}</div>
                                    </div>
                                </div>
                                <div
                                    class="px-3.5 py-2 bg-purple-900/80 rounded-2xl border border-purple-700/60 flex items-center gap-2">
                                    <span class="text-base">🏫</span>
                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-wider text-purple-300">
                                            Classes</div>
                                        <div class="text-sm font-black text-white leading-tight">
                                            {{ $distinctClassesCount }}</div>
                                    </div>
                                </div>
                                <div
                                    class="px-3.5 py-2 bg-purple-900/80 rounded-2xl border border-purple-700/60 flex items-center gap-2">
                                    <span class="text-base">👨‍🏫</span>
                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-wider text-purple-300">
                                            Enseignants</div>
                                        <div class="text-sm font-black text-white leading-tight">
                                            {{ $distinctTeachersCount }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Table des affectations du cycle --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-700">
                                <thead
                                    class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-500 border-b border-slate-200">
                                    <tr>
                                        <th class="py-3.5 px-6">Classe & Niveau</th>
                                        @if (!$isPolyvalent)
                                            <th class="py-3.5 px-6">Matière</th>
                                        @endif
                                        <th class="py-3.5 px-6">Enseignant Attribué</th>
                                        <th class="py-3.5 px-6">Couverture classe</th>
                                        <th class="py-3.5 px-6 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white font-semibold">
                                    @php
                                        $cycleRows = $isPolyvalent
                                            ? $cycleAssignments
                                                ->unique(fn($a) => $a->class_id . '-' . $a->teacher_id)
                                                ->sortBy(fn($a) => $a->schoolClass?->name)
                                            : $cycleAssignments->sortBy(fn($a) => $a->schoolClass?->name);
                                    @endphp
                                    @foreach ($cycleRows as $asn)
                                        @php
                                            $cov = $classCoverage[$asn->class_id] ?? null;
                                            $covPct = $cov['percent'] ?? 0;
                                            $covColor =
                                                $covPct >= 100
                                                    ? 'bg-emerald-500'
                                                    : ($covPct >= 50
                                                        ? 'bg-amber-500'
                                                        : 'bg-rose-500');
                                            $covText =
                                                $covPct >= 100
                                                    ? 'text-emerald-700'
                                                    : ($covPct >= 50
                                                        ? 'text-amber-700'
                                                        : 'text-rose-700');
                                        @endphp
                                        <tr class="hover:bg-purple-50/30 transition">
                                            {{-- Classe & Niveau --}}
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-2.5">
                                                    <div
                                                        class="w-8 h-8 rounded-xl bg-purple-100 text-purple-900 flex items-center justify-center font-black text-xs shrink-0">
                                                        🏫
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-black text-slate-900">
                                                            {{ $asn->schoolClass->name }}</div>
                                                        <div class="text-[10px] text-slate-400 font-bold">
                                                            {{ $asn->schoolClass->level->name }}</div>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Matière (Maternelle et Primaire n'ont pas cette colonne) --}}
                                            @if (!$isPolyvalent)
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        class="text-xs font-black text-purple-900 px-3 py-1 bg-purple-50 border border-purple-100 rounded-xl">
                                                        {{ $asn->subject->name }}
                                                    </span>
                                                </td>
                                            @endif

                                            {{-- Enseignant --}}
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-2.5">
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-gradient-to-tr from-purple-900 to-indigo-900 text-amber-300 font-black text-xs flex items-center justify-center shrink-0 shadow-xs">
                                                        {{ mb_substr($asn->teacher->user->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-bold text-slate-900">
                                                            {{ $asn->teacher->user->name }}</div>
                                                        <div class="text-[10px] text-slate-500 font-medium">
                                                            {{ $asn->teacher->speciality ?? ($isPolyvalent ? 'Titulaire de classe' : 'Générale') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Couverture classe --}}
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($cov)
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-20 bg-slate-200 rounded-full h-1.5">
                                                            <div class="{{ $covColor }} h-1.5 rounded-full"
                                                                style="width: {{ min(100, $covPct) }}%"></div>
                                                        </div>
                                                        <span class="text-[10px] font-black {{ $covText }}">
                                                            {{ $cov['assigned'] }}/{{ $cov['configured'] }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <span class="text-[10px] text-slate-400">—</span>
                                                @endif
                                            </td>

                                            {{-- Actions --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium">
                                                <div class="flex items-center justify-end gap-2">
                                                    <a href="{{ route('assignments.create', ['class_id' => $asn->class_id]) }}"
                                                        class="px-2.5 py-1.5 text-[11px] font-black bg-emerald-50 text-emerald-950 hover:bg-emerald-100 border border-emerald-200 rounded-xl transition flex items-center gap-1 shadow-2xs"
                                                        title="Gérer toutes les matières de {{ $asn->schoolClass->name }}">
                                                        <span>⚡</span> Gérer
                                                    </a>
                                                    <a href="{{ route('assignments.show', $asn) }}"
                                                        class="px-2.5 py-1.5 text-[11px] font-black bg-purple-50 text-purple-900 hover:bg-purple-100 rounded-xl transition shadow-2xs">
                                                        👁️ Voir
                                                    </a>
                                                    <a href="{{ route('assignments.edit', $asn) }}"
                                                        class="px-2.5 py-1.5 text-[11px] font-black bg-amber-50 text-amber-800 hover:bg-amber-100 rounded-xl transition shadow-2xs">
                                                        ✏️ Modifier
                                                    </a>
                                                    <form method="POST"
                                                        action="{{ route('assignments.destroy', $asn) }}"
                                                        class="inline"
                                                        data-confirm="Voulez-vous supprimer cette affectation d'enseignant ?">
                                                        @csrf @method('DELETE')
                                                        <button type="submit"
                                                            class="px-2.5 py-1.5 text-[11px] font-black bg-red-50 text-red-700 hover:bg-red-100 rounded-xl transition shadow-2xs">
                                                            🗑️ Supprimer
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-xl space-y-4">
                        <div
                            class="w-16 h-16 bg-purple-50 text-purple-900 rounded-full flex items-center justify-center text-3xl mx-auto">
                            🔗
                        </div>
                        <h3 class="text-lg font-black text-purple-950">Aucune affectation trouvée</h3>
                        <p class="text-xs text-slate-500 max-w-md mx-auto">
                            Aucune affectation d'enseignant ne correspond aux critères sélectionnés pour l'année
                            scolaire active.
                        </p>
                        <div class="pt-2">
                            <a href="{{ route('assignments.create') }}"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-purple-900 hover:bg-purple-800 text-amber-300 text-xs font-black uppercase tracking-wider rounded-2xl shadow-md transition">
                                <span>➕</span>
                                <span>Créer une affectation</span>
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
