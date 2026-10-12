<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight tracking-tight flex items-center gap-2">
                    <span>📝</span>
                    <span>Évaluations & Saisie de Notes</span>
                </h2>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-2 flex-wrap">
                    @if ($activeYear)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-purple-100 text-purple-900 border border-purple-200">
                            ✨ {{ $activeYear->name }}
                        </span>
                    @endif
                    <span class="text-slate-400 font-semibold">• {{ $evaluations->count() }} évaluation(s) affichée(s)</span>
                    <span class="text-slate-400 font-semibold">• Regroupées par Niveau → Classe → Matière</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="openCreateModal()"
                        class="px-4 py-2 bg-gradient-to-r from-purple-950 to-indigo-950 hover:from-purple-900 hover:to-indigo-900 text-white text-xs font-black rounded-xl shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                    <span>➕</span>
                    <span>Nouvelle Évaluation</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen"
         x-data="evaluationsManager(
             {{ Js::from($assignmentsByClass) }},
             {{ Js::from($classes->map(fn($c) => [
                 'id' => $c->id,
                 'name' => $c->name,
                 'level_name' => $c->level->name ?? '',
                 'cycle_name' => $c->level->cycle->name ?? '',
                 'expected_period_type' => $c->expectedPeriodType(),
             ])) }},
             {{ Js::from($teachers->map(fn($t) => ['id' => $t->id, 'name' => $t->user->name ?? ''])) }},
             {{ Js::from($periods->map(fn($p) => [
                 'id' => $p->id,
                 'name' => $p->name,
                 'type' => $p->type,
                 'order' => $p->order,
             ])) }},
             {
                 class_id: '{{ old('class_id', $selectedClassId) }}',
                 period_id: '{{ old('period_id', $selectedPeriodId) }}',
                 subject_id: '{{ old('subject_id', $selectedSubjectId) }}',
                 teacher_id: '{{ old('teacher_id') }}',
                 title: '{{ old('title') }}',
                 type: '{{ old('type', 'DEVOIR') }}'
             }
         )">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            {{-- ── ALERTES ─────────────────────────────────────────────── --}}
            @if (session('status'))
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-950 flex items-center gap-3 shadow-xs">
                    <span class="text-2xl">✅</span>
                    <div>
                        <div class="font-black text-sm text-emerald-900">Succès !</div>
                        <div class="text-xs text-emerald-800">{{ session('status') }}</div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-300 flex items-center gap-3 shadow-xs">
                    <span class="text-2xl">⚠️</span>
                    <ul class="text-xs font-bold list-disc list-inside text-rose-900">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ── BARRE DE FILTRES & RECHERCHE ────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5">
                <form method="GET" action="{{ route('evaluations.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                    <div>
                        <label class="block text-[11px] font-black text-slate-600 uppercase tracking-wider mb-1.5">📅 Période</label>
                        <select name="period_id" onchange="this.form.submit()"
                                class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-600 focus:ring-purple-600">
                            @php
                                $trimestres = $periods->where('type', 'TRIMESTRE')->sortBy('order');
                                $semestres = $periods->where('type', 'SEMESTRE')->sortBy('order');
                            @endphp
                            @if ($trimestres->isNotEmpty())
                                <optgroup label="── TRIMESTRES (Maternelle & Primaire) ──">
                                    @foreach ($trimestres as $p)
                                        <option value="{{ $p->id }}" {{ $selectedPeriodId == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @if ($semestres->isNotEmpty())
                                <optgroup label="── SEMESTRES (Collège & Lycée) ──">
                                    @foreach ($semestres as $p)
                                        <option value="{{ $p->id }}" {{ $selectedPeriodId == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black text-slate-600 uppercase tracking-wider mb-1.5">🏫 Filtrer par Classe</label>
                        <select name="class_id" onchange="this.form.submit()"
                                class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-600 focus:ring-purple-600">
                            <option value="">-- Toutes les classes --</option>
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

                    <div>
                        <label class="block text-[11px] font-black text-slate-600 uppercase tracking-wider mb-1.5">📚 Matière</label>
                        <select name="subject_id" onchange="this.form.submit()"
                                class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-600 focus:ring-purple-600">
                            <option value="">-- Toutes les matières --</option>
                            @php $filterSubjects = $selectedClassId ? $classSubjects : $subjects; @endphp
                            @foreach ($filterSubjects as $sbj)
                                <option value="{{ $sbj->id }}" {{ $selectedSubjectId == $sbj->id ? 'selected' : '' }}>
                                    {{ $sbj->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black text-slate-600 uppercase tracking-wider mb-1.5">🔍 Recherche</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Titre, matière, prof..."
                                class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-600 focus:ring-purple-600" />
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 bg-purple-900 hover:bg-purple-800 text-white text-xs font-black rounded-xl shadow-xs transition">
                            Filtrer
                        </button>
                        @if ($selectedClassId || $selectedSubjectId || $selectedCycleId || $selectedLevelId || $search)
                            <a href="{{ route('evaluations.index', ['period_id' => $selectedPeriodId]) }}"
                               class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition"
                               title="Réinitialiser les filtres">
                                ✕
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- ── LISTE HIÉRARCHIQUE : CYCLE → NIVEAU → CLASSE → MATIÈRE ───── --}}
            <div class="space-y-8">
                @forelse ($groupedEvaluations as $levelName => $classesGroup)
                    @php
                        $levelTotalEvaluations = $classesGroup->sum(fn($subjects) => $subjects->sum(fn($evals) => $evals->count()));
                        // Extract cycle name from first evaluation in this level group
                        $firstEvalOfLevel = $classesGroup->first()?->first()?->first();
                        $cycleName = $firstEvalOfLevel?->schoolClass?->level?->cycle?->name ?? '';
                    @endphp

                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                        {{-- 🎓 EN-TÊTE NIVEAU --}}
                        <div class="px-6 py-4 bg-gradient-to-r from-purple-950 to-indigo-950 text-white flex items-center justify-between flex-wrap gap-3">
                            <div class="flex items-center gap-3">
                                <span class="text-xl">🎓</span>
                                <div>
                                    @if ($cycleName)
                                        <div class="text-[10px] font-black tracking-widest uppercase text-amber-400 mb-0.5">
                                            {{ $cycleName }}
                                        </div>
                                    @endif
                                    <h3 class="text-sm font-black uppercase tracking-widest text-white">
                                        {{ $levelName }}
                                    </h3>
                                    <p class="text-[11px] text-purple-200 font-semibold">
                                        {{ $classesGroup->count() }} classe(s) · {{ $levelTotalEvaluations }} évaluation(s)
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-black bg-white/10 text-amber-300 px-3.5 py-1 rounded-full border border-white/10">
                                    {{ $levelTotalEvaluations }} évaluation(s) au total
                                </span>
                            </div>
                        </div>

                        {{-- 🏫 CLASSES DU NIVEAU --}}
                        <div class="divide-y divide-slate-100">
                            @foreach ($classesGroup as $className => $subjectsGroup)
                                @php
                                    $sampleEval = $subjectsGroup->first()?->first();
                                    $classModelId = $sampleEval?->class_id;
                                    $classTotalEvaluations = $subjectsGroup->sum(fn($evals) => $evals->count());
                                @endphp

                                <div class="p-6 bg-slate-50/50">
                                    {{-- Titre de la Classe + Bouton rapide d'ajout --}}
                                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-200/60 flex-wrap gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-base font-black text-purple-950 flex items-center gap-1.5">
                                                <span>🎒</span>
                                                <span>Classe : {{ $className }}</span>
                                            </span>
                                            <span class="text-[11px] font-extrabold text-slate-500 bg-white px-2.5 py-0.5 rounded-full border border-slate-200">
                                                {{ $subjectsGroup->count() }} matière(s) · {{ $classTotalEvaluations }} évaluation(s)
                                            </span>
                                        </div>
                                        @if ($classModelId)
                                            <button type="button"
                                                    @click="openCreateModal('{{ $classModelId }}', null, true)"
                                                    class="inline-flex items-center gap-1.5 text-xs font-black px-4 py-2 rounded-xl bg-purple-950 hover:bg-purple-900 text-white shadow-xs hover:shadow-md transition cursor-pointer">
                                                <span>➕</span>
                                                <span>Ajouter dans cette classe</span>
                                            </button>
                                        @endif
                                    </div>

                                    {{-- 📚 MATIÈRES DE LA CLASSE --}}
                                    <div class="space-y-4">
                                        @foreach ($subjectsGroup as $subjectName => $subjectEvaluations)
                                            @php
                                                $firstEval = $subjectEvaluations->first();
                                                $teacherName = $firstEval?->teacher?->user?->name ?? 'Enseignant non assigné';
                                                $subjectModelId = $firstEval?->subject_id;
                                            @endphp

                                            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                                                {{-- En-tête Matière --}}
                                                <div class="px-5 py-3 bg-purple-50/40 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-black text-sm text-slate-900 flex items-center gap-1.5">
                                                            <span>📚</span>
                                                            <span>{{ $subjectName }}</span>
                                                        </span>
                                                        <span class="text-[11px] font-bold text-slate-600 bg-white px-2.5 py-0.5 rounded-md border border-slate-200">
                                                            👨‍🏫 {{ $teacherName }}
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-[11px] font-black text-purple-800 bg-purple-100/70 px-2.5 py-0.5 rounded-full">
                                                            {{ $subjectEvaluations->count() }} évaluation(s)
                                                        </span>
                                                        @if ($classModelId && $subjectModelId)
                                                            <button type="button"
                                                                    @click="openCreateModal('{{ $classModelId }}', '{{ $subjectModelId }}', true, true)"
                                                                    class="text-xs font-black text-purple-900 hover:text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 px-2.5 py-1 rounded-lg transition cursor-pointer flex items-center gap-1">
                                                                <span>➕</span> Éval
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- Tableau des évaluations pour cette matière --}}
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-slate-100">
                                                        <thead class="bg-slate-50/70">
                                                            <tr>
                                                                <th class="px-5 py-2.5 text-left text-[11px] font-black text-slate-600 uppercase tracking-wider">Intitulé</th>
                                                                <th class="px-5 py-2.5 text-left text-[11px] font-black text-slate-600 uppercase tracking-wider">Type</th>
                                                                <th class="px-5 py-2.5 text-left text-[11px] font-black text-slate-600 uppercase tracking-wider">Date</th>
                                                                <th class="px-5 py-2.5 text-center text-[11px] font-black text-slate-600 uppercase tracking-wider">Note Max</th>
                                                                <th class="px-5 py-2.5 text-center text-[11px] font-black text-slate-600 uppercase tracking-wider">Notes Saisies</th>
                                                                <th class="px-5 py-2.5 text-right text-[11px] font-black text-slate-600 uppercase tracking-wider">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-slate-100 bg-white">
                                                            @foreach ($subjectEvaluations as $eval)
                                                                @php
                                                                    $typeMeta = [
                                                                        'DEVOIR'      => ['label' => 'DS',          'class' => 'bg-blue-100 text-blue-800'],
                                                                        'COMPOSITION' => ['label' => 'Composition', 'class' => 'bg-purple-100 text-purple-800'],
                                                                        'CONTROLE'    => ['label' => 'Contrôle',    'class' => 'bg-amber-100 text-amber-800'],
                                                                        'EXAMEN'      => ['label' => 'Examen',      'class' => 'bg-rose-100 text-rose-800'],
                                                                    ];
                                                                    $meta = $typeMeta[$eval->type] ?? ['label' => $eval->type, 'class' => 'bg-slate-100 text-slate-700'];
                                                                    $noteCount = $eval->grades->count();
                                                                @endphp
                                                                <tr class="hover:bg-purple-50/20 transition">
                                                                    <td class="px-5 py-3 text-sm font-black text-slate-900">
                                                                        {{ $eval->title }}
                                                                    </td>
                                                                    <td class="px-5 py-3">
                                                                        <span class="px-2.5 py-0.5 text-[11px] font-black rounded-full {{ $meta['class'] }}">
                                                                            {{ $meta['label'] }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="px-5 py-3 text-xs font-semibold text-slate-600 tabular-nums">
                                                                        {{ $eval->evaluation_date->format('d/m/Y') }}
                                                                    </td>
                                                                    <td class="px-5 py-3 text-center text-xs font-black text-slate-800 tabular-nums">
                                                                        / {{ number_format($eval->max_score, 0) }}
                                                                    </td>
                                                                    <td class="px-5 py-3 text-center">
                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-black
                                                                            {{ $noteCount > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                                                            {{ $noteCount > 0 ? "✓ $noteCount note(s)" : '— Aucune note' }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="px-5 py-3 text-right">
                                                                        <div class="flex items-center justify-end gap-1.5">
                                                                            <a href="{{ route('evaluations.grades', $eval) }}"
                                                                               class="inline-flex items-center gap-1 text-[11px] font-black px-3 py-1.5 rounded-xl bg-purple-900 text-white hover:bg-purple-800 transition shadow-xs">
                                                                                ✍️ Notes
                                                                            </a>
                                                                            <a href="{{ route('evaluations.show', $eval) }}"
                                                                               class="inline-flex items-center gap-1 text-[11px] font-black px-2.5 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition"
                                                                               title="Voir l'évaluation">
                                                                                👁️
                                                                            </a>
                                                                            <a href="{{ route('evaluations.edit', $eval) }}"
                                                                               class="inline-flex items-center gap-1 text-[11px] font-black px-2.5 py-1.5 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 transition"
                                                                               title="Modifier">
                                                                                ✏️
                                                                            </a>
                                                                            <form method="POST" action="{{ route('evaluations.destroy', $eval) }}" class="inline"
                                                                                  data-confirm="Supprimer « {{ $eval->title }} » ? Toutes les notes seront perdues.">
                                                                                @csrf @method('DELETE')
                                                                                <button type="submit"
                                                                                        class="inline-flex items-center gap-1 text-[11px] font-black px-2.5 py-1.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition cursor-pointer"
                                                                                        title="Supprimer">
                                                                                    🗑️
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
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-16 text-center">
                        <div class="text-5xl mb-4">📭</div>
                        <h3 class="text-base font-black text-slate-800">Aucune évaluation trouvée</h3>
                        <p class="text-xs text-slate-500 font-semibold mt-1">
                            Aucune évaluation ne correspond aux critères de filtre sélectionnés pour cette période.
                        </p>
                        <div class="mt-6 flex items-center justify-center gap-3">
                            <a href="{{ route('evaluations.index', ['period_id' => $selectedPeriodId]) }}"
                               class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                                Réinitialiser les filtres
                            </a>
                            <button type="button"
                                    @click="openCreateModal()"
                                    class="px-4 py-2 bg-purple-900 hover:bg-purple-800 text-white text-xs font-black rounded-xl transition cursor-pointer">
                                ➕ Créer une première évaluation
                            </button>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- ── MODAL POPUP DE CRÉATION D'ÉVALUATION (Téléporté au body pour un centrage parfait à l'écran) ── --}}
            <template x-teleport="body">
                <div x-show="createModalOpen"
                     x-cloak
                     @keydown.escape.window="closeCreateModal()"
                     class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6"
                     style="display: none;">

                    <!-- Overlay Backdrop -->
                    <div x-show="createModalOpen"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         @click="closeCreateModal()"
                         class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-40"></div>

                    <!-- Modal Dialog Box -->
                    <div x-show="createModalOpen"
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200 transform"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                         @click.stop
                         class="relative z-50 w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden my-auto">

                        <!-- Header -->
                        <div class="px-6 py-4 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-black uppercase tracking-wider flex items-center gap-2 text-amber-400">
                                    <span>➕</span> Créer une Évaluation
                                </h3>
                                <p class="text-xs text-purple-200 font-semibold mt-0.5" x-text="modalSubtitle"></p>
                            </div>
                            <button type="button" @click="closeCreateModal()"
                                    class="text-purple-200 hover:text-white p-1 rounded-xl hover:bg-white/10 transition cursor-pointer">
                                ✕
                            </button>
                        </div>

                        <!-- Form -->
                        <form method="POST" action="{{ route('evaluations.store') }}"
                              class="p-6 space-y-5"
                              data-no-confirm="true">
                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- CLASSE --}}
                                <div>
                                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Classe *
                                    </label>

                                    {{-- En mode classe fixée / verrouillée --}}
                                    <div x-show="isClassLocked" class="space-y-1">
                                        <div class="flex items-center justify-between px-3.5 py-2.5 bg-purple-50 border border-purple-200 rounded-xl text-purple-950 font-black text-xs">
                                            <div class="flex items-center gap-2">
                                                <span>🎒</span>
                                                <span x-text="selectedClassName"></span>
                                            </div>
                                            <span class="text-[10px] font-extrabold text-purple-700 bg-purple-100/80 px-2 py-0.5 rounded-md border border-purple-200">
                                                🔒 Fixée
                                            </span>
                                        </div>
                                    </div>

                                    {{-- En mode classe sélectionnable --}}
                                    <div x-show="!isClassLocked">
                                        <select x-model="formClassId" @change="onClassChange()"
                                                class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                            <option value="">-- Choisir la classe --</option>
                                            @foreach ($classesByCycle as $cycleName => $cycleClasses)
                                                <optgroup label="── {{ strtoupper($cycleName) }} ──">
                                                    @foreach ($cycleClasses as $cls)
                                                        <option value="{{ $cls->id }}">
                                                            {{ $cls->level->name }} • {{ $cls->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input type="hidden" name="class_id" :value="formClassId" required />
                                </div>

                                {{-- PÉRIODE (TRIMESTRE / SEMESTRE ADAPTÉ) --}}
                                <div>
                                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                        <span>Période *</span>
                                        <span x-show="selectedClassPeriodBadge" x-text="selectedClassPeriodBadge"
                                              class="text-[10px] font-black px-2 py-0.5 rounded-full bg-purple-100 text-purple-900 border border-purple-200"></span>
                                    </label>
                                    <select x-model="formPeriodId" name="period_id" required
                                            class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                        <template x-for="p in currentPeriods" :key="p.id">
                                            <option :value="p.id" x-text="p.name + (p.type === 'TRIMESTRE' ? ' (Trimestre)' : ' (Semestre)')"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            {{-- ROW 2: MATIÈRE & ENSEIGNANT --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- MATIÈRE --}}
                                <div>
                                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                        <span>Matière de cette classe *</span>
                                        <span x-show="!isSubjectLocked && formClassId && currentSubjects.length > 0" class="text-[10px] font-black text-purple-700" x-text="currentSubjects.length + ' matière(s)'"></span>
                                    </label>

                                    {{-- En mode matière fixée / verrouillée --}}
                                    <div x-show="isSubjectLocked" class="space-y-1">
                                        <div class="flex items-center justify-between px-3.5 py-2.5 bg-purple-50 border border-purple-200 rounded-xl text-purple-950 font-black text-xs">
                                            <div class="flex items-center gap-2">
                                                <span>📚</span>
                                                <span x-text="selectedSubjectName"></span>
                                            </div>
                                            <span class="text-[10px] font-extrabold text-purple-700 bg-purple-100/80 px-2 py-0.5 rounded-md border border-purple-200">
                                                🔒 Fixée
                                            </span>
                                        </div>
                                    </div>

                                    {{-- En mode matière sélectionnable --}}
                                    <div x-show="!isSubjectLocked">
                                        <select x-model="formSubjectId" @change="onSubjectChange()"
                                                class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 disabled:bg-slate-100 disabled:text-slate-400">
                                            <option value="">-- Choisir la matière --</option>
                                            <template x-for="subj in currentSubjects" :key="subj.id">
                                                <option :value="subj.id" x-text="subj.name"></option>
                                            </template>
                                        </select>
                                        <p x-show="formClassId && currentSubjects.length === 0" class="text-[11px] text-amber-600 font-bold mt-1">
                                            ⚠️ Aucune matière affectée à cette classe pour cette année.
                                        </p>
                                    </div>
                                    <input type="hidden" name="subject_id" :value="formSubjectId" required />
                                </div>

                                {{-- ENSEIGNANT --}}
                                <div>
                                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                        <span>Enseignant *</span>
                                        <span x-show="!isTeacherLocked && autoAssignedTeacher && formTeacherId" x-cloak
                                              class="text-[10px] font-black text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full px-2 py-0.5">
                                            ✓ Auto (Assigné)
                                        </span>
                                    </label>

                                    {{-- En mode enseignant fixé / verrouillé --}}
                                    <div x-show="isTeacherLocked && formTeacherId" class="space-y-1">
                                        <div class="flex items-center justify-between px-3.5 py-2.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-950 font-black text-xs">
                                            <div class="flex items-center gap-2">
                                                <span>👨‍🏫</span>
                                                <span x-text="selectedTeacherName"></span>
                                            </div>
                                            <span class="text-[10px] font-extrabold text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded-md border border-purple-200">
                                                🔒 Fixé
                                            </span>
                                        </div>
                                    </div>

                                    {{-- En mode enseignant sélectionnable --}}
                                    <div x-show="!isTeacherLocked || !formTeacherId">
                                        <select x-model="formTeacherId"
                                                :class="autoAssignedTeacher ? 'border-emerald-400 ring-1 ring-emerald-300' : 'border-slate-300'"
                                                class="w-full text-sm font-bold rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 transition disabled:bg-slate-100 disabled:text-slate-400">
                                            <option value="">-- Choisir l'enseignant --</option>
                                            <template x-for="t in currentTeachers" :key="t.id">
                                                <option :value="t.id" x-text="t.name"></option>
                                            </template>
                                        </select>
                                        <p x-show="formSubjectId && currentTeachers.length === 0" class="text-[11px] text-amber-600 font-bold mt-1">
                                            ⚠️ Aucun enseignant disponible pour cette matière.
                                        </p>
                                    </div>
                                    <input type="hidden" name="teacher_id" :value="formTeacherId" required />
                                </div>
                            </div>

                            {{-- ROW 3: TYPE & INTITULÉ --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- TYPE --}}
                                <div>
                                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Type *</label>
                                    <select name="type" required
                                            x-model="formType" @change="onTypeChange()"
                                            class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                        <option value="DEVOIR">Devoir Surveillé (DS)</option>
                                        <option value="COMPOSITION">Composition</option>
                                        <option value="CONTROLE">Contrôle</option>
                                        <option value="EXAMEN">Examen</option>
                                    </select>
                                </div>

                                {{-- INTITULÉ --}}
                                <div>
                                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                        <span>Intitulé de l'évaluation *</span>
                                        <span x-show="titleAutoGenerated && formTitle" x-cloak
                                              class="text-[10px] font-black text-purple-700 bg-purple-50 border border-purple-200 rounded-full px-2 py-0.5">
                                            ✓ Auto
                                        </span>
                                    </label>
                                    <input type="text" name="title" required placeholder="Ex: DEVOIR - Mathématiques"
                                           x-model="formTitle" @input="titleAutoGenerated = false"
                                           class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- DATE --}}
                                <div>
                                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Date *</label>
                                    <input type="date" name="evaluation_date" required
                                           value="{{ old('evaluation_date', now()->format('Y-m-d')) }}"
                                           class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                </div>

                                {{-- NOTE MAX --}}
                                <div>
                                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Note Max</label>
                                    <input type="number" name="max_score" min="1" max="100" step="0.5" value="20" required
                                           class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 text-center" />
                                </div>
                            </div>

                            <!-- Footer actions -->
                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                                <button type="button" @click="closeCreateModal()"
                                        class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                                    Annuler
                                </button>
                                <button type="submit"
                                        class="px-6 py-2.5 bg-gradient-to-r from-purple-950 to-indigo-950 hover:from-purple-900 hover:to-indigo-900 text-white text-xs font-black rounded-xl shadow-md transition cursor-pointer">
                                    ➕ Enregistrer l'Évaluation
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>

        </div>
    </div>

    @push('scripts')
    <script>
        function evaluationsManager(assignmentsByClass, classes, allTeachers, allPeriods, oldValues = {}) {
            return {
                createModalOpen: false,
                isClassLocked: false,
                isSubjectLocked: false,
                isTeacherLocked: false,
                formClassId: oldValues.class_id || '',
                formPeriodId: oldValues.period_id || '',
                formSubjectId: oldValues.subject_id || '',
                formTeacherId: oldValues.teacher_id || '',
                formTitle: oldValues.title || '',
                formType: oldValues.type || 'DEVOIR',
                titleAutoGenerated: !oldValues.title,
                autoAssignedTeacher: false,
                assignmentsByClass: assignmentsByClass || {},
                classes: classes || [],
                allTeachers: allTeachers || [],
                allPeriods: allPeriods || [],

                get selectedClass() {
                    return this.classes.find(c => String(c.id) === String(this.formClassId)) || null;
                },

                get selectedClassPeriodBadge() {
                    if (!this.selectedClass) return '';
                    return this.selectedClass.expected_period_type === 'SEMESTRE'
                        ? 'Semestres (Collège / Lycée)'
                        : 'Trimestres (Maternelle / Primaire)';
                },

                get currentPeriods() {
                    if (!this.selectedClass) {
                        return this.allPeriods;
                    }
                    const expectedType = this.selectedClass.expected_period_type;
                    const filtered = this.allPeriods.filter(p => p.type === expectedType);
                    return filtered.length > 0 ? filtered : this.allPeriods;
                },

                syncPeriod() {
                    const periods = this.currentPeriods;
                    const isValid = periods.some(p => String(p.id) === String(this.formPeriodId));
                    if (!isValid && periods.length > 0) {
                        this.formPeriodId = String(periods[0].id);
                    }
                },

                get selectedClassName() {
                    const found = this.selectedClass;
                    return found ? `${found.level_name} • ${found.name}` : '';
                },

                get selectedSubjectName() {
                    const list = this.currentSubjects;
                    const found = list.find(s => String(s.id) === String(this.formSubjectId));
                    if (found) return found.name;
                    if (this.assignmentsByClass[this.formClassId]) {
                        const match = this.assignmentsByClass[this.formClassId].find(a => String(a.subject_id) === String(this.formSubjectId));
                        if (match) return match.subject_name;
                    }
                    return '';
                },

                get selectedTeacherName() {
                    const found = this.allTeachers.find(t => String(t.id) === String(this.formTeacherId));
                    if (found) return found.name;
                    const current = this.currentTeachers.find(t => String(t.id) === String(this.formTeacherId));
                    return current ? current.name : '';
                },

                get modalSubtitle() {
                    if (this.selectedClassName && this.selectedSubjectName) {
                        return `${this.selectedClassName} • ${this.selectedSubjectName}`;
                    }
                    if (this.selectedClassName) {
                        return `Classe : ${this.selectedClassName}`;
                    }
                    return 'Sélectionnez la classe et la matière';
                },

                // 1. Matières filtrées strictement pour la classe choisie / fixée
                get currentSubjects() {
                    if (!this.formClassId || !this.assignmentsByClass[this.formClassId]) {
                        return [];
                    }
                    const list = this.assignmentsByClass[this.formClassId];
                    const unique = [];
                    const seen = new Set();
                    for (const item of list) {
                        if (!seen.has(item.subject_id)) {
                            seen.add(item.subject_id);
                            unique.push({
                                id: item.subject_id,
                                name: item.subject_name
                            });
                        }
                    }
                    return unique.sort((a, b) => a.name.localeCompare(b.name));
                },

                // 2. Enseignants filtrés pour la matière sélectionnée dans cette classe
                get currentTeachers() {
                    if (!this.formClassId || !this.formSubjectId) {
                        return this.allTeachers;
                    }
                    const classAssignments = this.assignmentsByClass[this.formClassId] || [];
                    const matching = classAssignments.filter(
                        a => String(a.subject_id) === String(this.formSubjectId) && a.teacher_id
                    );

                    if (matching.length > 0) {
                        const uniqueTeachers = [];
                        const seen = new Set();
                        for (const m of matching) {
                            if (!seen.has(m.teacher_id)) {
                                seen.add(m.teacher_id);
                                uniqueTeachers.push({ id: m.teacher_id, name: m.teacher_name });
                            }
                        }
                        return uniqueTeachers;
                    }

                    // Fallback sur tous les enseignants si aucun n'est spécifiquement lié
                    return this.allTeachers;
                },

                onClassChange() {
                    this.syncPeriod();
                    this.formSubjectId = '';
                    this.formTeacherId = '';
                    this.autoAssignedTeacher = false;
                    if (this.titleAutoGenerated) {
                        this.formTitle = '';
                    }
                },

                onSubjectChange() {
                    this.syncTeacher();
                    this.generateTitle();
                },

                syncTeacher() {
                    if (!this.formClassId || !this.formSubjectId) {
                        this.formTeacherId = '';
                        this.autoAssignedTeacher = false;
                        return;
                    }
                    const teachers = this.currentTeachers;
                    if (teachers.length === 1) {
                        this.formTeacherId = String(teachers[0].id);
                        this.autoAssignedTeacher = true;
                    } else if (teachers.length > 1) {
                        const exists = teachers.some(t => String(t.id) === String(this.formTeacherId));
                        if (!exists) {
                            this.formTeacherId = String(teachers[0].id);
                        }
                        this.autoAssignedTeacher = true;
                    } else {
                        this.formTeacherId = '';
                        this.autoAssignedTeacher = false;
                    }
                },

                onTypeChange() {
                    if (this.titleAutoGenerated) {
                        this.generateTitle();
                    }
                },

                generateTitle() {
                    const subj = this.currentSubjects.find(s => String(s.id) === String(this.formSubjectId));
                    if (subj) {
                        this.formTitle = `${this.formType} - ${subj.name}`;
                        this.titleAutoGenerated = true;
                    }
                },

                openCreateModal(classId = null, subjectId = null, lockClass = false, lockSubject = false) {
                    this.isClassLocked = Boolean(lockClass && classId);
                    this.isSubjectLocked = Boolean(lockSubject && subjectId);
                    this.isTeacherLocked = Boolean(lockSubject && subjectId);

                    if (classId) {
                        this.formClassId = String(classId);
                        this.syncPeriod();
                        this.onClassChange();
                    } else {
                        this.isClassLocked = false;
                        this.isSubjectLocked = false;
                        this.isTeacherLocked = false;
                        this.syncPeriod();
                    }

                    if (subjectId) {
                        this.$nextTick(() => {
                            this.formSubjectId = String(subjectId);
                            this.syncTeacher();
                            this.generateTitle();
                            if (lockSubject && this.formTeacherId) {
                                this.isTeacherLocked = true;
                            }
                        });
                    }

                    this.createModalOpen = true;
                },

                closeCreateModal() {
                    this.createModalOpen = false;
                }
            };
        }
        window.evaluationsManager = evaluationsManager;
    </script>
    @endpush
</x-app-layout>
