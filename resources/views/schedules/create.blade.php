<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                    <span>➕</span>
                    <span>Nouveau Créneau de Cours</span>
                </h2>
                <p class="text-xs text-slate-500 font-semibold mt-1">
                    Programmation intelligente avec filtres rapides et détection de collisions
                </p>
            </div>
            <a href="{{ route('schedules.index', ['class_id' => $selectedClassId]) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-purple-950 font-black text-xs rounded-xl transition shadow-xs">
                ← Retour à l'emploi du temps
            </a>
        </div>
    </x-slot>

    @php
        $classList = $classes
            ->map(
                fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'level_id' => $c->level_id,
                    'level_name' => $c->level?->name ?? '',
                    'cycle_id' => $c->level?->cycle_id,
                    'cycle_name' => $c->level?->cycle?->name ?? '',
                    'display' => ($c->level?->cycle?->name ?? '') . ' • ' . ($c->level?->name ?? '') . ' • ' . $c->name,
                ],
            )
            ->values();

        $cycleList = $cycles
            ->map(
                fn($cy) => [
                    'id' => $cy->id,
                    'name' => $cy->name,
                    'levels' => $cy->levels->map(fn($l) => ['id' => $l->id, 'name' => $l->name]),
                ],
            )
            ->values();

        $teacherList = $teachers
            ->map(
                fn($t) => [
                    'id' => $t->id,
                    'name' => $t->user?->name ?? '',
                    'matricule' => $t->matricule ?? '',
                    'speciality' => $t->speciality ?? '',
                    'cycle_id' => $t->cycle_id,
                    'cycle_name' => $t->cycle?->name ?? '',
                ],
            )
            ->values();

        $subjectList = $subjects
            ->map(
                fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'code' => $s->code ?? '',
                ],
            )
            ->values();

        $assignmentList = $classAssignments
            ->map(
                fn($a) => [
                    'id' => $a->id,
                    'subject_id' => $a->subject_id,
                    'subject_name' => $a->subject?->name ?? '',
                    'subject_code' => $a->subject?->code ?? '',
                    'teacher_id' => $a->teacher_id,
                    'teacher_name' => $a->teacher?->user?->name ?? '',
                    'label' => ($a->subject?->name ?? '') . ' — ' . ($a->teacher?->user?->name ?? ''),
                ],
            )
            ->values();
    @endphp

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen" x-data="{
        mode: '{{ $classAssignments->isNotEmpty() ? 'assignment' : 'manual' }}',
    
        // Données sources
        allClasses: {{ Js::from($classList) }},
        allCycles: {{ Js::from($cycleList) }},
        allTeachers: {{ Js::from($teacherList) }},
        allSubjects: {{ Js::from($subjectList) }},
        allAssignments: {{ Js::from($assignmentList) }},
    
        // Filtres de sélection de classe
        selectedCycleFilter: 'all',
        selectedLevelFilter: 'all',
        searchClassText: '',
        selectedClassId: '{{ old('class_id', $selectedClassId) }}',
    
        // Filtres de cours / prof / matière
        searchAssignmentText: '',
        searchSubjectText: '',
        searchTeacherText: '',
    
        // Valeurs du formulaire
        subjectId: '{{ old('subject_id', '') }}',
        teacherId: '{{ old('teacher_id', '') }}',
        startTime: '{{ old('start_time', '08:00') }}',
        endTime: '{{ old('end_time', '10:00') }}',
    
        // Classes filtrées
        get filteredClasses() {
            return this.allClasses.filter(c => {
                const matchCycle = this.selectedCycleFilter === 'all' || c.cycle_id == this.selectedCycleFilter;
                const matchLevel = this.selectedLevelFilter === 'all' || c.level_id == this.selectedLevelFilter;
                const matchSearch = !this.searchClassText || c.display.toLowerCase().includes(this.searchClassText.toLowerCase());
                return matchCycle && matchLevel && matchSearch;
            });
        },
    
        // Niveaux disponibles selon le cycle choisi
        get availableLevels() {
            if (this.selectedCycleFilter === 'all') {
                let levels = [];
                this.allCycles.forEach(cy => {
                    levels = levels.concat(cy.levels);
                });
                return levels;
            }
            const cy = this.allCycles.find(c => c.id == this.selectedCycleFilter);
            return cy ? cy.levels : [];
        },
    
        // Cours officiels filtrés
        get filteredAssignments() {
            if (!this.searchAssignmentText) return this.allAssignments;
            const q = this.searchAssignmentText.toLowerCase();
            return this.allAssignments.filter(a => a.label.toLowerCase().includes(q));
        },
    
        // Matières filtrées
        get filteredSubjects() {
            if (!this.searchSubjectText) return this.allSubjects;
            const q = this.searchSubjectText.toLowerCase();
            return this.allSubjects.filter(s => s.name.toLowerCase().includes(q) || (s.code && s.code.toLowerCase().includes(q)));
        },
    
        // Enseignants filtrés
        get filteredTeachers() {
            if (!this.searchTeacherText) return this.allTeachers;
            const q = this.searchTeacherText.toLowerCase();
            return this.allTeachers.filter(t => t.name.toLowerCase().includes(q) || (t.speciality && t.speciality.toLowerCase().includes(q)));
        },
    
        onClassChange(newClassId) {
            if (newClassId) {
                window.location.href = '{{ route('schedules.create') }}?class_id=' + newClassId;
            }
        },

        onSubjectSelect(newSubjectId) {
            this.subjectId = newSubjectId;
            const found = this.allAssignments.find(a => String(a.subject_id) === String(newSubjectId));
            if (found) {
                this.teacherId = String(found.teacher_id);
            }
        },

        onAssignmentSelect(e) {
            const val = e.target.value;
            if (!val) {
                this.subjectId = '';
                this.teacherId = '';
                return;
            }
            const parts = val.split('_');
            this.subjectId = parts[0];
            this.teacherId = parts[1];
        }
    }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            {{-- Alertes Erreurs / Collisions --}}
            @if ($errors->any())
                <div class="bg-rose-50 border-l-4 border-rose-500 p-5 rounded-2xl shadow-xs animate-shake">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl">⚠️</span>
                        <div>
                            <h3 class="text-sm font-black text-rose-900 uppercase tracking-wide">
                                Conflit de programmation détecté
                            </h3>
                            <ul class="text-xs font-bold text-rose-700 mt-1 space-y-1 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white shadow-xl rounded-3xl p-8 border border-slate-200/80 space-y-6">

                {{-- En-tête avec bascule de mode --}}
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-black text-slate-900">Paramètres du créneau</h3>
                        <p class="text-xs text-slate-500">Sélectionnez la classe, puis configurez le jour, les horaires
                            et la discipline</p>
                    </div>
                    <div class="flex items-center bg-slate-100 p-1 rounded-xl shrink-0">
                        <button type="button" @click="mode = 'assignment'"
                            :class="mode === 'assignment' ? 'bg-white shadow-xs text-purple-950 font-black' :
                                'text-slate-600 font-bold hover:text-slate-900'"
                            class="px-3 py-1.5 text-xs rounded-lg transition">
                            🔗 Cours Officiel
                        </button>
                        <button type="button" @click="mode = 'manual'"
                            :class="mode === 'manual' ? 'bg-white shadow-xs text-purple-950 font-black' :
                                'text-slate-600 font-bold hover:text-slate-900'"
                            class="px-3 py-1.5 text-xs rounded-lg transition">
                            ✍️ Sélection Libre
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ route('schedules.store') }}" class="space-y-6">
                    @csrf

                    {{-- ── 1. SÉLECTION ET FILTRES DE LA CLASSE ── --}}
                    <div
                        class="p-5 rounded-2xl bg-gradient-to-r from-purple-50/60 via-slate-50 to-purple-50/30 border border-purple-100 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-black text-purple-950 uppercase tracking-wider">
                                🏫 1. Sélectionner la Classe <span class="text-rose-500">*</span>
                            </label>
                            @if ($selectedClass)
                                <span
                                    class="text-[11px] font-black text-purple-800 bg-purple-100 px-2.5 py-0.5 rounded-md">
                                    Active : {{ $selectedClass->level?->cycle?->name }} • {{ $selectedClass->name }}
                                </span>
                            @endif
                        </div>

                        {{-- Barre de filtres Cycle + Niveau + Recherche --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1">
                            {{-- Filtre Cycle --}}
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Filtrer par Cycle
                                    :</label>
                                <select x-model="selectedCycleFilter" @change="selectedLevelFilter = 'all'"
                                    class="w-full text-xs font-bold rounded-xl border-slate-200 focus:border-purple-600 focus:ring-purple-600 bg-white">
                                    <option value="all">🌐 Tous les cycles</option>
                                    <template x-for="cy in allCycles" :key="cy.id">
                                        <option :value="cy.id" x-text="cy.name"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Filtre Niveau --}}
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Filtrer par Niveau
                                    :</label>
                                <select x-model="selectedLevelFilter"
                                    class="w-full text-xs font-bold rounded-xl border-slate-200 focus:border-purple-600 focus:ring-purple-600 bg-white">
                                    <option value="all">📚 Tous les niveaux</option>
                                    <template x-for="lvl in availableLevels" :key="lvl.id">
                                        <option :value="lvl.id" x-text="lvl.name"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Recherche rapide --}}
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Recherche de classe
                                    :</label>
                                <input type="text" x-model="searchClassText"
                                    placeholder="Ex: 6ème, CE2, Terminale..."
                                    class="w-full text-xs font-bold rounded-xl border-slate-200 focus:border-purple-600 focus:ring-purple-600 bg-white" />
                            </div>
                        </div>

                        {{-- Liste déroulante principale des Classes --}}
                        <div class="pt-2">
                            <label class="block text-xs font-extrabold text-slate-700 mb-1">
                                Choisir dans les classes filtrées (<span x-text="filteredClasses.length"></span>
                                disponibles) :
                            </label>
                            <select name="class_id" x-model="selectedClassId"
                                @change="onClassChange($event.target.value)" required
                                class="w-full text-sm font-bold border-purple-200 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 bg-white">
                                <option value="">-- Sélectionnez une classe --</option>
                                <template x-for="cls in filteredClasses" :key="cls.id">
                                    <option :value="cls.id" x-text="cls.display"
                                        :selected="cls.id == selectedClassId"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- ── 2. CHOIX MATIÈRE & ENSEIGNANT ── --}}

                    {{-- Mode 1 : Sélection combinée via Affectation Officielle --}}
                    <div x-show="mode === 'assignment'"
                        class="bg-purple-50/50 p-5 rounded-2xl border border-purple-100 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-black text-purple-950 uppercase tracking-wider">
                                📚 2. Cours & Enseignant Attitré <span class="text-rose-500">*</span>
                            </label>
                            @if ($classAssignments->isNotEmpty())
                                <span class="text-[11px] font-bold text-purple-700">
                                    {{ $classAssignments->count() }} cours officiel(s)
                                </span>
                            @endif
                        </div>

                        @if ($classAssignments->isNotEmpty())
                            {{-- Filtre de recherche dans les affectations --}}
                            <div>
                                <input type="text" x-model="searchAssignmentText"
                                    placeholder="🔍 Filtrer par matière ou nom de professeur..."
                                    class="w-full text-xs font-bold rounded-xl border-purple-200 focus:border-purple-600 focus:ring-purple-600 bg-white mb-2" />
                            </div>

                            <select @change="onAssignmentSelect($event)"
                                class="w-full text-sm font-bold border-purple-200 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 bg-white">
                                <option value="">-- Choisir un cours officiel de la classe --</option>
                                <template x-for="assign in filteredAssignments" :key="assign.id">
                                    <option :value="assign.subject_id + '_' + assign.teacher_id"
                                        :selected="subjectId == assign.subject_id && teacherId == assign.teacher_id"
                                        x-text="'📚 ' + assign.subject_name + ' — 👨‍🏫 ' + assign.teacher_name">
                                    </option>
                                </template>
                            </select>
                            <p class="text-[11px] font-bold text-purple-700">
                                💡 Ce mode pré-sélectionne le professeur officiel de la classe pour éviter toute
                                incohérence.
                            </p>
                        @else
                            <div
                                class="text-xs font-bold text-amber-700 bg-amber-50 p-3 rounded-xl border border-amber-200">
                                ⚠️ Aucune affectation d'enseignant n'a encore été configurée pour cette classe. Utilisez
                                le mode "Sélection Libre" ci-dessus ou configurez d'abord les affectations.
                            </div>
                        @endif
                    </div>

                    {{-- Mode 2 : Sélection Libre avec Filtres --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-show="mode === 'manual'">
                        {{-- Matière avec filtre --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">
                                Matière <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" x-model="searchSubjectText" placeholder="🔍 Filtrer matière..."
                                class="w-full text-xs font-bold rounded-xl border-slate-200 focus:border-purple-600 focus:ring-purple-600 bg-white" />
                            <select name="subject_id" x-model="subjectId" @change="onSubjectSelect($event.target.value)" :required="mode === 'manual'"
                                class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                <option value="">-- Choisir une matière (<span
                                        x-text="filteredSubjects.length"></span>) --</option>
                                <template x-for="sbj in filteredSubjects" :key="sbj.id">
                                    <option :value="sbj.id"
                                        x-text="sbj.name + (sbj.code ? ' (' + sbj.code + ')' : '')"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Enseignant avec filtre --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">
                                Enseignant <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" x-model="searchTeacherText"
                                placeholder="🔍 Filtrer enseignant / spécialité..."
                                class="w-full text-xs font-bold rounded-xl border-slate-200 focus:border-purple-600 focus:ring-purple-600 bg-white" />
                            <select name="teacher_id" x-model="teacherId" :required="mode === 'manual'"
                                class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                <option value="">-- Choisir un enseignant (<span
                                        x-text="filteredTeachers.length"></span>) --</option>
                                <template x-for="t in filteredTeachers" :key="t.id">
                                    <option :value="t.id"
                                        x-text="t.name + (t.speciality ? ' (' + t.speciality + ')' : '')"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Inputs cachés si mode assignment --}}
                    <template x-if="mode === 'assignment'">
                        <div>
                            <input type="hidden" name="subject_id" :value="subjectId" required />
                            <input type="hidden" name="teacher_id" :value="teacherId" required />
                        </div>
                    </template>

                    {{-- ── 3. PARAMÈTRES HORAIRES ET SALLE ── --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t border-slate-100">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">
                                Jour <span class="text-rose-500">*</span>
                            </label>
                            <select name="day_of_week" required
                                class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                @foreach (['LUNDI' => 'Lundi', 'MARDI' => 'Mardi', 'MERCREDI' => 'Mercredi', 'JEUDI' => 'Jeudi', 'VENDREDI' => 'Vendredi', 'SAMEDI' => 'Samedi'] as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('day_of_week') == $val ? 'selected' : '' }}>{{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">
                                Heure Début <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" name="start_time" x-model="startTime" required
                                class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">
                                Heure Fin <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" name="end_time" x-model="endTime" required
                                class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">
                                Salle / Lieu
                            </label>
                            <input type="text" name="room_number" value="{{ old('room_number') }}"
                                placeholder="Ex: Salle 104, Labo..."
                                class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-6 border-t border-slate-100">
                        <a href="{{ route('schedules.index', ['class_id' => $selectedClassId]) }}"
                            class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Annuler
                        </a>
                        <x-primary-button class="bg-purple-900 hover:bg-purple-950 shadow-md">
                            💾 Enregistrer et Planifier le Créneau
                        </x-primary-button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
