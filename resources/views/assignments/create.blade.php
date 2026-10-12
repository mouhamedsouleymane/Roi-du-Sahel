<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                    <span>🔗</span>
                    <span>Nouvelle Affectation des Cours</span>
                </h2>
                <p class="text-xs font-bold text-slate-500 mt-1 flex items-center gap-2">
                    <span>Année scolaire active :</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-950 font-black">
                        📅 {{ $activeYear?->name ?? 'Non définie' }}
                    </span>
                </p>
            </div>
            <a href="{{ route('assignments.index') }}"
                class="px-4 py-2 text-xs font-black bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition shadow-2xs flex items-center gap-1.5">
                ← Retour au tableau de bord
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto space-y-6 animate-fade-in-up"
             x-data="{
                activeTab: '{{ $preselectedClassId ? 'by_class' : 'by_class' }}',
                teachers: {{ $teachers->values()->toJson() }},
                allClasses: {{ $classes->values()->toJson() }},
                allSubjects: {{ $subjects->values()->toJson() }},
                coefficients: {{ json_encode($coefficients) }},
                existingAssignments: {{ json_encode($existingAssignmentsMap) }},

                // ── FILTRES CYCLES STRICTS ──
                isPolyvalentCycleName(name) {
                    const n = (name || '').toLowerCase();
                    return n.includes('maternelle') || n.includes('primaire');
                },

                // Mode 1 : Uniquement classes et enseignants de Maternelle & Primaire
                get polyvalentClasses() {
                    return this.allClasses.filter(c => this.isPolyvalentCycleName(c.cycle_name));
                },
                get polyvalentTeachers() {
                    return this.teachers.filter(t => t.is_polyvalent || this.isPolyvalentCycleName(t.cycle_name));
                },

                // Mode 2 : Uniquement enseignants et classes de Collège & Lycée (Spécialistes)
                get specialistTeachers() {
                    return this.teachers.filter(t => !t.is_polyvalent && !this.isPolyvalentCycleName(t.cycle_name));
                },

                // ── Mode 1 : Par Classe (Maternelle & Primaire) ──
                selectedClassId: '{{ old('class_id', $preselectedClassId) }}',
                bulkPolyvalentTeacherId: '',
                classAssignments: {},

                get selectedClass() {
                    return this.allClasses.find(c => c.id == this.selectedClassId) ?? null;
                },
                get subjectsForSelectedClass() {
                    if (!this.selectedClass) return [];
                    return this.allSubjects.filter(s => s.level_ids.includes(this.selectedClass.level_id));
                },
                get eligibleTeachersForClass() {
                    if (!this.selectedClass) return [];
                    return this.polyvalentTeachers.filter(t => {
                        // 1. Doit appartenir au cycle de la classe
                        const matchCycle = t.cycle_ids && t.cycle_ids.includes(this.selectedClass.cycle_id);
                        if (!matchCycle) return false;

                        // 2. Doit être SANS classe assignée cette année, OU déjà titulaire de CETTE classe spécifique
                        const assignedIds = t.assigned_class_ids || [];
                        const isFreeOrAssignedToThisClass = assignedIds.length === 0 || assignedIds.includes(this.selectedClass.id);
                        return isFreeOrAssignedToThisClass;
                    });
                },
                onClassSelected() {
                    this.bulkPolyvalentTeacherId = '';
                    this.classAssignments = {};
                    if (!this.selectedClass) return;

                    // Pré-remplir les affectations existantes
                    let detectedSingleTeacher = null;
                    let allSame = true;

                    this.subjectsForSelectedClass.forEach((sbj, idx) => {
                        const key = this.selectedClass.id + '_' + sbj.id;
                        const existing = this.existingAssignments[key];
                        const tId = existing ? String(existing.teacher_id) : '';
                        this.classAssignments[sbj.id] = tId;

                        if (idx === 0) {
                            detectedSingleTeacher = tId;
                        } else if (tId !== detectedSingleTeacher) {
                            allSame = false;
                        }
                    });

                    if (allSame && detectedSingleTeacher) {
                        this.bulkPolyvalentTeacherId = detectedSingleTeacher;
                    }
                },
                applyBulkPolyvalentTeacher() {
                    if (!this.bulkPolyvalentTeacherId) return;
                    this.subjectsForSelectedClass.forEach(sbj => {
                        this.classAssignments[sbj.id] = String(this.bulkPolyvalentTeacherId);
                    });
                },
                get validClassAssignmentsCount() {
                    return Object.values(this.classAssignments).filter(Boolean).length;
                },

                // ── Mode 2 : Par Enseignant (Collège & Lycée) ──
                selectedTeacherId: '{{ old('teacher_id', $preselectedTeacherId) }}',
                rows: [{ class_id: '', subject_id: '' }],

                get teacher() {
                    return this.specialistTeachers.find(t => t.id == this.selectedTeacherId) ?? null;
                },
                get filteredClassesForTeacher() {
                    if (!this.teacher) return [];
                    return this.allClasses.filter(c => this.teacher.cycle_ids.includes(c.cycle_id) && !this.isPolyvalentCycleName(c.cycle_name));
                },
                filteredSubjectsForTeacherClass(classId) {
                    const cls = this.allClasses.find(c => c.id == classId);
                    if (!cls) return [];
                    return this.allSubjects.filter(s => s.level_ids.includes(cls.level_id));
                },
                getSubjectConfig(classId, subjectId) {
                    if (!classId || !subjectId) return null;
                    const cls = this.allClasses.find(c => c.id == classId);
                    if (!cls) return null;
                    const key = cls.level_id + '_' + subjectId;
                    return this.coefficients[key] ?? null;
                },
                findMatchingSubjectForTeacher(classId) {
                    if (!this.teacher || !this.teacher.speciality) return '';
                    const available = this.filteredSubjectsForTeacherClass(classId);
                    const spec = this.teacher.speciality.toLowerCase().trim();
                    const exact = available.find(s => s.name.toLowerCase() === spec || s.code.toLowerCase() === spec);
                    if (exact) return exact.id;
                    const partial = available.find(s => s.name.toLowerCase().includes(spec) || spec.includes(s.name.toLowerCase()));
                    if (partial) return partial.id;
                    return '';
                },
                onTeacherChange() {
                    this.rows = [{ class_id: '', subject_id: '' }];
                },
                onTeacherClassChange(row) {
                    const matchedSubjectId = this.findMatchingSubjectForTeacher(row.class_id);
                    row.subject_id = matchedSubjectId ? String(matchedSubjectId) : '';
                },
                addRow() {
                    this.rows.push({
                        class_id: '',
                        subject_id: ''
                    });
                },
                removeRow(idx) {
                    if (this.rows.length > 1) {
                        this.rows.splice(idx, 1);
                    }
                },
                get totalTeacherNewWeeklyHours() {
                    let total = 0;
                    const seen = new Set();
                    this.rows.forEach(r => {
                        if (r.class_id && r.subject_id) {
                            const pairKey = r.class_id + '_' + r.subject_id;
                            if (!seen.has(pairKey)) {
                                seen.add(pairKey);
                                const cfg = this.getSubjectConfig(r.class_id, r.subject_id);
                                if (cfg && cfg.weekly_hours) {
                                    total += parseInt(cfg.weekly_hours);
                                }
                            }
                        }
                    });
                    return total;
                },
                get validTeacherRowsCount() {
                    return this.rows.filter(r => r.class_id && r.subject_id).length;
                },
                init() {
                    if (this.selectedClassId) {
                        // Check if preselected class is polyvalent or specialist to set active tab
                        const cls = this.allClasses.find(c => c.id == this.selectedClassId);
                        if (cls && !this.isPolyvalentCycleName(cls.cycle_name)) {
                            this.activeTab = 'by_teacher';
                        } else {
                            this.activeTab = 'by_class';
                            this.onClassSelected();
                        }
                    }
                }
             }">

            @if (! $activeYear)
                <div class="bg-amber-50 border border-amber-200 text-amber-900 px-5 py-4 rounded-3xl text-sm font-bold flex items-center gap-3 shadow-xs">
                    <span class="text-2xl">⚠️</span>
                    <div>
                        <strong class="block text-amber-950">Aucune année scolaire active configurée</strong>
                        <span class="text-xs font-semibold text-amber-800">Veuillez activer une année scolaire avant de procéder aux affectations de cours.</span>
                    </div>
                </div>
            @endif

            {{-- Erreurs de validation globales --}}
            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 p-5 rounded-3xl text-rose-900 shadow-xs">
                    <div class="flex items-center gap-2 font-black text-sm mb-1.5">
                        <span>⚠️</span> Erreurs lors de l'enregistrement :
                    </div>
                    <ul class="list-disc list-inside text-xs font-semibold space-y-1 text-rose-800">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- SÉLECTEUR DE MODE D'AFFECTATION (ONGLETS ERGONOMIQUES) --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-2 bg-slate-200/80 rounded-3xl border border-slate-300/80 shadow-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 w-full sm:w-auto">
                    {{-- Onglet 1 : Maternelle & Primaire --}}
                    <button type="button"
                            @click="activeTab = 'by_class'"
                            :class="activeTab === 'by_class'
                                ? 'bg-gradient-to-r from-purple-950 to-indigo-950 text-white shadow-md ring-2 ring-purple-500/40'
                                : 'bg-white/80 text-slate-700 hover:bg-white'"
                            class="px-5 py-3 rounded-2xl text-xs font-black transition flex items-center justify-center gap-2">
                        <span>🏫</span>
                        <span>Mode 1 : Maternelle & Primaire</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-400 text-purple-950 font-black">Par Classe</span>
                    </button>

                    {{-- Onglet 2 : Collège & Lycée --}}
                    <button type="button"
                            @click="activeTab = 'by_teacher'"
                            :class="activeTab === 'by_teacher'
                                ? 'bg-gradient-to-r from-purple-950 to-indigo-950 text-white shadow-md ring-2 ring-purple-500/40'
                                : 'bg-white/80 text-slate-700 hover:bg-white'"
                            class="px-5 py-3 rounded-2xl text-xs font-black transition flex items-center justify-center gap-2">
                        <span>👨‍🏫</span>
                        <span>Mode 2 : Collège & Lycée</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-200 text-indigo-950 font-black">Par Enseignant</span>
                    </button>
                </div>

                <div class="text-[11px] font-bold text-slate-500 px-3 hidden md:block">
                    <span x-show="activeTab === 'by_class'">Attribution de la classe aux instituteurs (Maternelle / Primaire)</span>
                    <span x-show="activeTab === 'by_teacher'">Attribution par discipline aux professeurs (Collège / Lycée)</span>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- ONGLET 1 : MATERNELLE & PRIMAIRE (PAR CLASSE)                     --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div x-show="activeTab === 'by_class'" x-cloak class="space-y-6">

                {{-- Étape 1 : Choisir la classe du Primaire / Maternelle --}}
                <div class="bg-white shadow-xl rounded-3xl border border-slate-200/80 overflow-hidden">
                    <div class="px-6 sm:px-8 py-5 bg-gradient-to-r from-purple-950 to-indigo-950 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 bg-amber-400 text-purple-950 rounded-2xl flex items-center justify-center font-black text-sm shrink-0 shadow-2xs">
                                1
                            </span>
                            <div>
                                <div class="text-sm font-black text-white">Sélectionner la Classe (Maternelle ou Primaire)</div>
                                <div class="text-xs font-semibold text-purple-200 mt-0.5">
                                    Seules les classes des cycles Maternelle et Primaire sont affichées
                                </div>
                            </div>
                        </div>

                        <template x-if="selectedClass">
                            <span class="px-3 py-1 rounded-xl text-xs font-black bg-amber-400 text-purple-950">
                                🌟 <span x-text="selectedClass.cycle_name"></span> &bull; <span x-text="selectedClass.level_name"></span>
                            </span>
                        </template>
                    </div>

                    <div class="p-6 sm:p-8 space-y-4">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                                Classe Maternelle / Primaire <span class="text-rose-500">*</span>
                            </label>
                            <select
                                x-model="selectedClassId"
                                @change="onClassSelected()"
                                class="w-full text-sm font-bold border-slate-300 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500 py-3">
                                <option value="">-- Choisir une classe Maternelle / Primaire --</option>
                                <template x-for="cls in polyvalentClasses" :key="cls.id">
                                    <option :value="cls.id" :selected="cls.id == selectedClassId">
                                        <span x-text="cls.name"></span>
                                        — <span x-text="cls.level_name"></span> (<span x-text="cls.cycle_name"></span>)
                                    </option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Étape 2 : Attribution à l'instituteur --}}
                <template x-if="selectedClass">
                    <form method="POST" action="{{ route('assignments.store') }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="class_id" :value="selectedClass.id">

                        <div class="bg-white shadow-xl rounded-3xl border border-slate-200/80 overflow-hidden">
                            <div class="px-6 sm:px-8 py-5 bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-purple-950">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 bg-purple-950 text-amber-300 rounded-2xl flex items-center justify-center font-black text-sm shrink-0 shadow-2xs">
                                        2
                                    </span>
                                    <div>
                                        <div class="text-sm font-black">
                                            Maître Titulaire & Matières de la Classe : <span class="underline" x-text="selectedClass.name"></span>
                                        </div>
                                        <div class="text-xs font-bold text-purple-900 mt-0.5">
                                            <span x-text="subjectsForSelectedClass.length"></span> matière(s) au programme pour ce niveau
                                        </div>
                                    </div>
                                </div>

                                <div class="text-xs font-black bg-purple-950 text-amber-300 px-3 py-1 rounded-xl">
                                    <span x-text="validClassAssignmentsCount"></span> / <span x-text="subjectsForSelectedClass.length"></span> matière(s) assignée(s)
                                </div>
                            </div>

                            <div class="p-6 sm:p-8 space-y-6">

                                {{-- ASSISTANT TITULAIRE UNIQUE --}}
                                <div class="p-5 bg-amber-50/90 border border-amber-300 rounded-3xl space-y-3">
                                    <div class="flex items-center gap-2 text-amber-950">
                                        <span class="text-xl">🌟</span>
                                        <h4 class="text-xs font-black uppercase tracking-wider">
                                            Attribuer Toute la Classe à un Enseignant (Recommandé)
                                        </h4>
                                    </div>
                                    <p class="text-[11px] font-semibold text-amber-900">
                                        Sélectionnez un instituteur habilité en Maternelle / Primaire pour lui assigner automatiquement l'ensemble des matières de la classe en 1 clic :
                                    </p>
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                                        <div class="sm:col-span-8">
                                            <select
                                                x-model="bulkPolyvalentTeacherId"
                                                class="w-full text-xs font-bold border-slate-300 rounded-xl shadow-xs focus:ring-amber-500 focus:border-amber-500">
                                                <option value="">-- Choisir l'Instituteur Titulaire (Disponibles) --</option>
                                                <template x-for="t in eligibleTeachersForClass" :key="t.id">
                                                    <option :value="t.id" :selected="bulkPolyvalentTeacherId == t.id">
                                                        <span x-text="t.name"></span>
                                                        (<span x-text="t.matricule"></span> — <span x-text="t.cycle_name"></span>)
                                                        <span x-text="t.assigned_classes && t.assigned_classes.length > 0 ? ' · [Déjà titulaire de cette classe]' : ' · [Disponible]'"></span>
                                                    </option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="sm:col-span-4">
                                            <button
                                                type="button"
                                                @click="applyBulkPolyvalentTeacher()"
                                                :disabled="!bulkPolyvalentTeacherId"
                                                class="w-full py-2.5 px-3 bg-gradient-to-r from-purple-950 to-indigo-950 hover:from-purple-900 hover:to-indigo-900 text-amber-300 font-black text-xs rounded-xl shadow-md transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-1.5">
                                                <span>✨</span>
                                                <span>Appliquer à toutes les matières</span>
                                            </button>
                                        </div>
                                    </div>
                                    <p x-show="selectedClass && eligibleTeachersForClass.length === 0" class="text-xs text-rose-700 font-bold bg-rose-50 p-3 rounded-xl border border-rose-200 mt-2">
                                        ⚠️ Aucun instituteur disponible pour ce cycle (tous les enseignants de ce cycle ont déjà une classe assignée cette année).
                                    </p>
                                </div>

                                {{-- TABLEAU DÉTAILLÉ DES MATIÈRES DE LA CLASSE --}}
                                <div class="space-y-3">
                                    <div class="grid grid-cols-12 gap-3 text-[10px] font-black uppercase tracking-wider text-slate-400 px-3 hidden sm:grid">
                                        <span class="col-span-4">Matière au programme</span>
                                        <span class="col-span-2 text-center">Coeff & Heures</span>
                                        <span class="col-span-6">Enseignant Titulaire</span>
                                    </div>

                                    <template x-for="(sbj, idx) in subjectsForSelectedClass" :key="sbj.id">
                                        <div class="p-4 rounded-2xl border transition-all duration-150 space-y-3 sm:space-y-0 sm:grid sm:grid-cols-12 sm:gap-3 sm:items-center"
                                             :class="classAssignments[sbj.id] ? 'bg-amber-50/50 border-amber-200' : 'bg-slate-50 border-slate-200'">

                                            <input type="hidden" :name="'class_assignments[' + idx + '][subject_id]'" :value="sbj.id">

                                            {{-- Colonne 1 : Matière --}}
                                            <div class="sm:col-span-4 flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-xl bg-amber-200 text-amber-950 flex items-center justify-center font-black text-xs shrink-0">
                                                    📖
                                                </div>
                                                <div>
                                                    <div class="text-xs font-black text-slate-900" x-text="sbj.name"></div>
                                                    <div class="text-[10px] font-bold text-slate-400" x-text="sbj.code"></div>
                                                </div>
                                            </div>

                                            {{-- Colonne 2 : Coeff & Horaires --}}
                                            <div class="sm:col-span-2 flex items-center justify-start sm:justify-center gap-1">
                                                <template x-if="getSubjectConfig(selectedClass.id, sbj.id)">
                                                    <div class="flex items-center gap-1">
                                                        <span class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-950 font-black text-[10px]"
                                                              x-text="'Coeff ' + getSubjectConfig(selectedClass.id, sbj.id).coefficient"></span>
                                                        <span class="px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-950 font-black text-[10px]"
                                                              x-text="getSubjectConfig(selectedClass.id, sbj.id).weekly_hours + 'h'"></span>
                                                    </div>
                                                </template>
                                                <template x-if="!getSubjectConfig(selectedClass.id, sbj.id)">
                                                    <span class="text-[10px] text-slate-300 font-bold">—</span>
                                                </template>
                                            </div>

                                            {{-- Colonne 3 : Choix de l'Instituteur --}}
                                            <div class="sm:col-span-6">
                                                <select
                                                    :name="'class_assignments[' + idx + '][teacher_id]'"
                                                    x-model="classAssignments[sbj.id]"
                                                    class="w-full text-xs font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                                    <option value="">-- Sélectionner l'instituteur (Non assigné) --</option>
                                                    <template x-for="t in eligibleTeachersForClass" :key="t.id">
                                                        <option :value="t.id" :selected="classAssignments[sbj.id] == t.id">
                                                            <span x-text="t.name"></span>
                                                            (<span x-text="t.matricule"></span> — <span x-text="t.cycle_name"></span>)
                                                            <span x-text="t.assigned_classes && t.assigned_classes.length > 0 ? ' · [Déjà titulaire]' : ' · [Disponible]'"></span>
                                                        </option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Validation --}}
                            <div class="p-6 bg-slate-50 border-t border-slate-200/80 flex flex-col sm:flex-row justify-between items-center gap-4">
                                <a href="{{ route('assignments.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                                    Annuler
                                </a>

                                <x-primary-button
                                    ::disabled="validClassAssignmentsCount === 0"
                                    class="bg-gradient-to-r from-purple-950 to-indigo-950 hover:from-purple-900 hover:to-indigo-900 text-amber-300 font-black px-7 py-3 rounded-2xl shadow-lg transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2">
                                    <span>💾</span>
                                    <span>Enregistrer les cours de la classe (<span x-text="validClassAssignmentsCount"></span>)</span>
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </template>
            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- ONGLET 2 : COLLÈGE & LYCÉE (PAR ENSEIGNANT SPÉCIALISTE)         --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div x-show="activeTab === 'by_teacher'" x-cloak class="space-y-6">

                <form method="POST" action="{{ route('assignments.store') }}" class="space-y-6">
                    @csrf

                    {{-- Étape 1 : Choisir le professeur de Collège / Lycée --}}
                    <div class="bg-white shadow-xl rounded-3xl border border-slate-200/80 overflow-hidden">
                        <div class="px-6 sm:px-8 py-5 bg-gradient-to-r from-purple-950 to-indigo-950 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 bg-amber-400 text-purple-950 rounded-2xl flex items-center justify-center font-black text-sm shrink-0 shadow-2xs">
                                    1
                                </span>
                                <div>
                                    <div class="text-sm font-black text-white">Sélectionner le Professeur (Collège ou Lycée)</div>
                                    <div class="text-xs font-semibold text-purple-200 mt-0.5">
                                        Seuls les enseignants spécialistes du Collège et Lycée sont listés
                                    </div>
                                </div>
                            </div>

                            <template x-if="teacher">
                                <span class="px-3 py-1 rounded-xl text-xs font-black bg-indigo-700 text-white">
                                    🎓 Professeur Spécialiste
                                </span>
                            </template>
                        </div>

                        <div class="p-6 sm:p-8 space-y-6">
                            <div>
                                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                                    Professeur Référent (Collège / Lycée) <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    name="teacher_id"
                                    x-model="selectedTeacherId"
                                    @change="onTeacherChange()"
                                    required
                                    class="w-full text-sm font-bold border-slate-300 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500 py-3">
                                    <option value="">-- Choisir un professeur de Collège / Lycée --</option>
                                    <template x-for="t in specialistTeachers" :key="t.id">
                                        <option :value="t.id" :selected="t.id == selectedTeacherId">
                                            <span x-text="t.name"></span>
                                            — Spécialité : <span x-text="t.speciality || 'Non définie'"></span>
                                            (<span x-text="t.cycle_name"></span>)
                                        </option>
                                    </template>
                                </select>
                            </div>

                            {{-- Fiche Récapitulative du Professeur --}}
                            <template x-if="teacher">
                                <div class="p-5 rounded-2xl border border-purple-200 bg-purple-50/50 space-y-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-purple-200/60">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-2xl bg-purple-200 text-purple-950 flex items-center justify-center font-black text-base shadow-2xs"
                                                 x-text="teacher.name.split(' ').map(w => w[0]).slice(0,2).join('').toUpperCase()">
                                            </div>
                                            <div>
                                                <div class="font-black text-sm text-slate-900 flex items-center gap-2">
                                                    <span x-text="teacher.name"></span>
                                                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full bg-white border border-slate-200 text-slate-600"
                                                          x-text="teacher.matricule"></span>
                                                </div>
                                                <div class="text-xs font-semibold text-slate-500 mt-0.5">
                                                    Discipline : <strong class="text-purple-950" x-text="teacher.speciality"></strong>
                                                    · Cycle : <span class="font-bold text-slate-700" x-text="teacher.cycle_name"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <div class="text-[10px] font-extrabold uppercase text-slate-400">Charge Actuelle</div>
                                            <div class="text-xs font-black text-purple-950">
                                                <span x-text="teacher.assigned_hours"></span> h/sem
                                                <span class="text-slate-400 font-medium">(<span x-text="teacher.assigned_count"></span> cours)</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2 text-xs">
                                        <span class="font-bold text-slate-600">Classes déjà assignées :</span>
                                        <template x-if="teacher.assigned_classes && teacher.assigned_classes.length > 0">
                                            <div class="flex flex-wrap gap-1.5">
                                                <template x-for="clsName in teacher.assigned_classes" :key="clsName">
                                                    <span class="px-2.5 py-0.5 rounded-lg bg-white font-extrabold text-purple-950 border border-purple-200 shadow-2xs"
                                                          x-text="clsName"></span>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="!teacher.assigned_classes || teacher.assigned_classes.length === 0">
                                            <span class="text-slate-400 font-semibold italic">Aucune classe pour l'instant</span>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Étape 2 : Lignes de cours pour ce professeur --}}
                    <template x-if="teacher">
                        <div class="bg-white shadow-xl rounded-3xl border border-slate-200/80 overflow-hidden">
                            <div class="px-6 sm:px-8 py-5 bg-gradient-to-r from-indigo-950 to-purple-950 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 bg-amber-400 text-purple-950 rounded-2xl flex items-center justify-center font-black text-sm shrink-0 shadow-2xs">
                                        2
                                    </span>
                                    <div>
                                        <div class="text-sm font-black text-white">Classes & Matières Assignées au Professeur</div>
                                        <div class="text-xs font-semibold text-purple-200 mt-0.5">
                                            Sélectionnez les classes de Collège / Lycée où intervient <strong class="text-white" x-text="teacher.name"></strong>
                                        </div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="addRow()"
                                    class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl transition flex items-center gap-1.5 shadow-md">
                                    <span>➕</span>
                                    <span>Ajouter une classe / matière</span>
                                </button>
                            </div>

                            <div class="p-6 sm:p-8 space-y-6">
                                <div class="space-y-3">
                                    <div class="grid grid-cols-12 gap-3 text-[10px] font-black uppercase tracking-wider text-slate-400 px-3 hidden sm:grid">
                                        <span class="col-span-5">1. Classe (Collège / Lycée)</span>
                                        <span class="col-span-4">2. Matière enseignée</span>
                                        <span class="col-span-2 text-center">Paramètres</span>
                                        <span class="col-span-1 text-center">Action</span>
                                    </div>

                                    <template x-for="(row, idx) in rows" :key="idx">
                                        <div class="p-4 rounded-2xl border transition-all duration-150 space-y-3 sm:space-y-0 sm:grid sm:grid-cols-12 sm:gap-3 sm:items-center"
                                             :class="row.class_id && row.subject_id ? 'bg-slate-50/80 border-slate-200' : 'bg-white border-purple-200 shadow-xs'">

                                            {{-- Classe --}}
                                            <div class="sm:col-span-5">
                                                <select
                                                    :name="'rows[' + idx + '][class_id]'"
                                                    x-model="row.class_id"
                                                    @change="onTeacherClassChange(row)"
                                                    required
                                                    class="w-full text-xs font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                                    <option value="">-- Choisir la Classe (Collège / Lycée) --</option>
                                                    <template x-for="cls in filteredClassesForTeacher" :key="cls.id">
                                                        <option :value="cls.id" :selected="cls.id == row.class_id">
                                                            <span x-text="cls.name"></span>
                                                            (<span x-text="cls.level_name"></span>)
                                                        </option>
                                                    </template>
                                                </select>
                                            </div>

                                            {{-- Matière --}}
                                            <div class="sm:col-span-4">
                                                <select
                                                    :name="'rows[' + idx + '][subject_id]'"
                                                    x-model="row.subject_id"
                                                    required
                                                    :disabled="!row.class_id"
                                                    class="w-full text-xs font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 disabled:opacity-40 disabled:cursor-not-allowed">
                                                    <option value="">-- Choisir la Matière --</option>
                                                    <template x-for="sbj in filteredSubjectsForTeacherClass(row.class_id)" :key="sbj.id">
                                                        <option :value="sbj.id" :selected="sbj.id == row.subject_id">
                                                            <span x-text="sbj.name"></span>
                                                            (<span x-text="sbj.code"></span>)
                                                        </option>
                                                    </template>
                                                </select>
                                            </div>

                                            {{-- Coeff & Heures --}}
                                            <div class="sm:col-span-2 flex flex-col items-center justify-center text-center">
                                                <template x-if="getSubjectConfig(row.class_id, row.subject_id)">
                                                    <div class="inline-flex flex-wrap items-center justify-center gap-1">
                                                        <span class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-950 font-black text-[10px]"
                                                              x-text="'Coeff ' + getSubjectConfig(row.class_id, row.subject_id).coefficient"></span>
                                                        <span class="px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-950 font-black text-[10px]"
                                                              x-text="getSubjectConfig(row.class_id, row.subject_id).weekly_hours + ' h/sem'"></span>
                                                    </div>
                                                </template>
                                                <template x-if="!getSubjectConfig(row.class_id, row.subject_id)">
                                                    <span class="text-[10px] font-bold text-slate-300">—</span>
                                                </template>
                                            </div>

                                            {{-- Action Supprimer --}}
                                            <div class="sm:col-span-1 flex items-center justify-end sm:justify-center">
                                                <button
                                                    type="button"
                                                    @click="removeRow(idx)"
                                                    :disabled="rows.length === 1"
                                                    class="w-8 h-8 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 transition flex items-center justify-center disabled:opacity-20 disabled:cursor-not-allowed">
                                                    🗑️
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Bilan horaire & validation --}}
                            <div class="p-6 bg-slate-50 border-t border-slate-200/80 flex flex-col sm:flex-row justify-between items-center gap-4">
                                <div class="text-xs font-bold text-slate-600">
                                    Volume additionnel : <strong class="text-purple-950">+<span x-text="totalTeacherNewWeeklyHours"></span> h/sem</strong>
                                    (Total estimé : <span x-text="teacher.assigned_hours + totalTeacherNewWeeklyHours"></span> h/sem)
                                </div>

                                <div class="flex items-center gap-3">
                                    <a href="{{ route('assignments.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                                        Annuler
                                    </a>

                                    <x-primary-button
                                        ::disabled="validTeacherRowsCount === 0"
                                        class="bg-gradient-to-r from-purple-950 to-indigo-950 hover:from-purple-900 hover:to-indigo-900 text-amber-300 font-black px-7 py-3 rounded-2xl shadow-lg transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2">
                                        <span>💾</span>
                                        <span>Enregistrer les cours du professeur (<span x-text="validTeacherRowsCount"></span>)</span>
                                    </x-primary-button>
                                </div>
                            </div>
                        </div>
                    </template>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>
