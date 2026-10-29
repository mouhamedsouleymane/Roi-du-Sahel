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
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-950 font-black">
                        📅 {{ $activeYear?->name ?? 'Non définie' }}
                    </span>
                    <span class="text-slate-300">•</span>
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-950 font-extrabold text-[11px]">
                        ⚡ Saisie initiée depuis le bouton Gérer
                    </span>
                </p>
            </div>
            <a href="{{ route('assignments.index') }}"
                class="px-4 py-2 text-xs font-black bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition shadow-2xs flex items-center gap-1.5">
                ← Retour aux affectations
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto space-y-6 animate-fade-in-up" x-data="{
            classId: '{{ $selectedClass->id }}',
            isPolyvalent: {{ $isPolyvalentCycle ? 'true' : 'false' }},
            titulaireTeacherId: '{{ old('titulaire_teacher_id', $defaultTitulaireId) }}',
            subjects: {{ json_encode($subjects) }},
            eligibleTeachers: {{ json_encode($eligibleTeachers) }},
            assignments: {
                @foreach ($subjects as $sbj)
                        '{{ $sbj['id'] }}': '{{ old("class_assignments.{$loop->index}.teacher_id", $existingAssignmentsMap[$sbj['id']]['teacher_id'] ?? '') }}', @endforeach
            },
        
            applyTitulaireToAll() {
                if (!this.titulaireTeacherId) return;
                this.subjects.forEach(sbj => {
                    this.assignments[sbj.id] = String(this.titulaireTeacherId);
                });
            },
        
            isSpecialistFor(teacher, subject) {
                if (!teacher || !teacher.speciality) return false;
                const spec = teacher.speciality.toLowerCase().trim();
                const name = (subject.name || '').toLowerCase().trim();
                const code = (subject.code || '').toUpperCase().trim();
                if (spec.includes(name) || name.includes(spec)) return true;
                if (code === 'MATH' && spec.includes('math')) return true;
                if (code === 'FRAN' && spec.includes('fran')) return true;
                if (code === 'PC' && (spec.includes('physique') || spec.includes('chimie'))) return true;
                if (code === 'SVT' && (spec.includes('svt') || spec.includes('biologie'))) return true;
                if ((code === 'HIST_GEO' || code === 'ECM') && (spec.includes('histoire') || spec.includes('géo'))) return true;
                if (code === 'ANG' && spec.includes('anglais')) return true;
                if (code === 'EPS' && spec.includes('eps')) return true;
                if (code === 'PHIL' && spec.includes('philo')) return true;
                if (code === 'INFO' && spec.includes('info')) return true;
                if (code === 'ARABE' && spec.includes('arabe')) return true;
                return false;
            },
        
            getTeachersForSubject(subject) {
                return [...this.eligibleTeachers].sort((a, b) => {
                    const aSpec = this.isSpecialistFor(a, subject);
                    const bSpec = this.isSpecialistFor(b, subject);
                    if (aSpec && !bSpec) return -1;
                    if (!aSpec && bSpec) return 1;
                    return a.name.localeCompare(b.name);
                });
            },
        
            get assignedCount() {
                return Object.values(this.assignments).filter(Boolean).length;
            },
        
            get totalHours() {
                let total = 0;
                this.subjects.forEach(sbj => {
                    if (this.assignments[sbj.id] && sbj.weekly_hours) {
                        total += parseInt(sbj.weekly_hours);
                    }
                });
                return total;
            }
        }">

            {{-- Erreurs de validation globales --}}
            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 p-5 rounded-3xl text-rose-900 shadow-xs">
                    <div class="flex items-center gap-2 font-black text-sm mb-1.5">
                        <span>⚠️</span> Erreur lors de l'enregistrement :
                    </div>
                    <ul class="list-disc list-inside text-xs font-semibold space-y-1 text-rose-800">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- CARTE DES CHAMPS FIXÉS ET VERROUILLÉS DEPUIS LE BOUTON GÉRER     --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div class="bg-white shadow-xl rounded-3xl border border-slate-200/80 overflow-hidden">
                <div
                    class="px-6 sm:px-8 py-5 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-white">
                    <div class="flex items-center gap-3">
                        <span
                            class="w-10 h-10 bg-amber-400 text-purple-950 rounded-2xl flex items-center justify-center font-black text-lg shrink-0 shadow-xs">
                            🏫
                        </span>
                        <div>
                            <div
                                class="text-xs font-black uppercase tracking-wider text-amber-300 flex items-center gap-2">
                                <span>Classe Sélectionnée & Contexte Fixé</span>
                                <span
                                    class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-[10px] font-black">
                                    🔒 Fixé via Gérer
                                </span>
                            </div>
                            <div class="text-xl font-black text-white mt-0.5">
                                {{ $selectedClass->name }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <span
                            class="px-3 py-1 rounded-xl text-xs font-black bg-white/10 text-purple-100 border border-white/20">
                            Cycle : <strong>{{ $selectedClass->level->cycle->name }}</strong>
                        </span>
                        <span
                            class="px-3 py-1 rounded-xl text-xs font-black bg-white/10 text-purple-100 border border-white/20">
                            Niveau : <strong>{{ $selectedClass->level->name }}</strong>
                        </span>
                        <span class="px-3 py-1 rounded-xl text-xs font-black bg-amber-400 text-purple-950">
                            📚 {{ $subjects->count() }} matière(s) au programme
                        </span>
                    </div>
                </div>

                {{-- 3 Cartes de Récapitulation Fixes --}}
                <div class="p-6 sm:p-8 bg-slate-50/70 border-b border-slate-200/80">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        {{-- Classe --}}
                        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-2xs space-y-1">
                            <div
                                class="text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
                                <span>Classe</span>
                                <span
                                    class="text-emerald-700 font-extrabold text-[10px] bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">🔒
                                    Fixée</span>
                            </div>
                            <div class="text-base font-black text-slate-900">{{ $selectedClass->name }}</div>
                            <div class="text-[11px] font-bold text-slate-500">Affectation directe des cours de cette
                                classe</div>
                        </div>

                        {{-- Cycle --}}
                        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-2xs space-y-1">
                            <div
                                class="text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
                                <span>Cycle d'Enseignement</span>
                                <span
                                    class="text-emerald-700 font-extrabold text-[10px] bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">🔒
                                    Fixé</span>
                            </div>
                            <div class="text-base font-black text-purple-950">{{ $selectedClass->level->cycle->name }}
                            </div>
                            <div class="text-[11px] font-bold text-slate-500">
                                @if ($isPolyvalentCycle)
                                    <span class="text-emerald-700 font-black">Régime Polyvalent (1 seule classe
                                        max)</span>
                                @else
                                    <span class="text-indigo-700 font-black">Régime Spécialisé (Par discipline)</span>
                                @endif
                            </div>
                        </div>

                        {{-- Niveau --}}
                        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-2xs space-y-1">
                            <div
                                class="text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
                                <span>Niveau Scolaire</span>
                                <span
                                    class="text-emerald-700 font-extrabold text-[10px] bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">🔒
                                    Fixé</span>
                            </div>
                            <div class="text-base font-black text-slate-900">{{ $selectedClass->level->name }}</div>
                            <div class="text-[11px] font-bold text-slate-500">
                                Seules les matières de ce niveau sont autorisées
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Message d'Information Pédagogique --}}
                <div
                    class="p-4 sm:px-8 bg-amber-50/80 border-b border-amber-200/80 flex items-start gap-3 text-xs text-amber-950">
                    <span class="text-lg">💡</span>
                    <div>
                        @if ($isPolyvalentCycle)
                            <strong class="font-extrabold block text-amber-950">Règle d'Affectation Maternelle &
                                Primaire :</strong>
                            <p class="font-medium text-amber-900 mt-0.5">
                                Un enseignant du cycle <strong>{{ $selectedClass->level->cycle->name }}</strong> ne
                                peut être affecté qu'à <strong>une et une seule classe</strong> d'un et un seul niveau.
                                Seules les matières rattachées au niveau
                                <strong>{{ $selectedClass->level->name }}</strong> sont configurables ci-dessous.
                            </p>
                        @else
                            <strong class="font-extrabold block text-amber-950">Règle d'Affectation Collège & Lycée
                                :</strong>
                            <p class="font-medium text-amber-900 mt-0.5">
                                Les enseignants du secondaire interviennent par discipline. Seules les matières
                                rattachées au niveau <strong>{{ $selectedClass->level->name }}</strong> apparaissent
                                ci-dessous.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- FORMULAIRE D'AFFECTATION DES MATIÈRES DE LA CLASSE               --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            @if ($subjects->isEmpty())
                <div class="bg-white shadow-xl rounded-3xl border border-slate-200/80 p-8 text-center space-y-4">
                    <div
                        class="w-16 h-16 rounded-3xl bg-amber-100 text-amber-700 flex items-center justify-center text-3xl mx-auto">
                        ⚠️
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-black text-slate-900">Aucune matière configurée pour ce niveau</h3>
                        <p class="text-xs text-slate-500 max-w-md mx-auto">
                            Le niveau <strong>{{ $selectedClass->level->name }}</strong> ne dispose pas encore de
                            matières et coefficients enregistrés.
                            Veuillez configurer les matières du niveau avant d'affecter les enseignants.
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('subjects.index') }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-900 hover:bg-purple-800 text-amber-300 font-black text-xs rounded-xl shadow-md transition">
                            <span>⚙️</span>
                            <span>Configurer les matières du niveau {{ $selectedClass->level->name }}</span>
                        </a>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('assignments.store') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">

                    <div class="bg-white shadow-xl rounded-3xl border border-slate-200/80 overflow-hidden">

                        {{-- Section Titulaire Unique (Maternelle & Primaire) --}}
                        @if ($isPolyvalentCycle)
                            <div
                                class="p-6 sm:p-8 bg-gradient-to-br from-amber-50/90 to-amber-100/40 border-b border-amber-200 space-y-4">
                                <div class="flex items-center gap-2 text-amber-950">
                                    <span class="text-xl">🌟</span>
                                    <h4 class="text-xs font-black uppercase tracking-wider">
                                        Instituteur Titulaire de la Classe (Polyvalent)
                                    </h4>
                                </div>
                                <p class="text-[11px] font-semibold text-amber-900">
                                    Sélectionnez l'instituteur titulaire habilité pour lui assigner automatiquement
                                    l'ensemble des matières du niveau
                                    <strong>{{ $selectedClass->level->name }}</strong> en 1 clic :
                                </p>

                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                                    <div class="sm:col-span-8">
                                        <label
                                            class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1.5">
                                            Instituteur Titulaire Disponible
                                        </label>
                                        <select x-model="titulaireTeacherId"
                                            class="w-full text-xs font-bold border-purple-300 rounded-xl shadow-xs focus:ring-amber-500 focus:border-amber-500 py-2.5">
                                            <option value="">-- Choisir l'instituteur titulaire --</option>
                                            <template x-for="t in eligibleTeachers" :key="t.id">
                                                <option :value="t.id" :selected="titulaireTeacherId == t.id">
                                                    <span x-text="t.name"></span>
                                                    (<span x-text="t.matricule"></span>)
                                                    <span
                                                        x-text="t.is_assigned_to_this_class ? ' · [🌟 Titulaire actuel de cette classe]' : ' · [✅ Disponible]'"></span>
                                                </option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-4">
                                        <button type="button" @click="applyTitulaireToAll()"
                                            :disabled="!titulaireTeacherId"
                                            class="w-full py-2.5 px-3 bg-gradient-to-r from-purple-950 to-indigo-950 hover:from-purple-900 hover:to-indigo-900 text-amber-300 font-black text-xs rounded-xl shadow-md transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-1.5">
                                            <span>✨</span>
                                            <span>Appliquer à toutes les matières</span>
                                        </button>
                                    </div>
                                </div>

                                @if ($eligibleTeachers->isEmpty())
                                    <div
                                        class="text-xs text-rose-800 font-bold bg-rose-50 p-3 rounded-xl border border-rose-200 mt-2 flex items-center gap-2">
                                        <span>⚠️</span>
                                        <span>Aucun instituteur disponible pour ce cycle : tous les instituteurs de
                                            {{ $selectedClass->level->cycle->name }} ont déjà une classe assignée pour
                                            cette année scolaire.</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- En-tête du Tableau des Matières --}}
                        <div
                            class="px-6 sm:px-8 py-5 bg-gradient-to-r from-slate-900 to-slate-800 flex items-center justify-between gap-4 text-white">
                            <div>
                                <div class="text-sm font-black text-white flex items-center gap-2">
                                    <span>📖</span>
                                    <span>Matières du Niveau {{ $selectedClass->level->name }} & Enseignants
                                        Assignés</span>
                                </div>
                                <div class="text-xs text-slate-400 font-semibold mt-0.5">
                                    Seules les {{ $subjects->count() }} matières de ce niveau sont modifiables pour
                                    cette classe
                                </div>
                            </div>
                            <div
                                class="text-xs font-black bg-purple-900 text-amber-300 px-3.5 py-1.5 rounded-xl border border-purple-700/60 shadow-2xs">
                                <span x-text="assignedCount"></span> / {{ $subjects->count() }} matière(s) assignée(s)
                            </div>
                        </div>

                        {{-- Liste des Matières du Niveau --}}
                        <div class="p-6 sm:p-8 space-y-3">
                            <div
                                class="grid grid-cols-12 gap-3 text-[10px] font-black uppercase tracking-wider text-slate-400 px-3 hidden sm:grid">
                                <span class="col-span-5">Matière du Niveau {{ $selectedClass->level->name }}</span>
                                <span class="col-span-2 text-center">Coefficient & Heures</span>
                                <span class="col-span-5">Enseignant Attribué</span>
                            </div>

                            @foreach ($subjects as $idx => $sbj)
                                <div class="p-4 rounded-2xl border transition-all duration-150 space-y-3 sm:space-y-0 sm:grid sm:grid-cols-12 sm:gap-3 sm:items-center"
                                    :class="assignments['{{ $sbj['id'] }}'] ? 'bg-amber-50/40 border-amber-200' :
                                        'bg-slate-50 border-slate-200'">

                                    <input type="hidden" name="class_assignments[{{ $idx }}][subject_id]"
                                        value="{{ $sbj['id'] }}">

                                    {{-- Colonne 1 : Matière --}}
                                    <div class="sm:col-span-5 flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-xl bg-purple-100 text-purple-950 flex items-center justify-center font-black text-sm shrink-0">
                                            📖
                                        </div>
                                        <div>
                                            <div class="text-xs font-black text-slate-900">{{ $sbj['name'] }}</div>
                                            <div
                                                class="text-[10px] font-bold text-slate-400 flex items-center gap-1.5">
                                                <span>{{ $sbj['code'] }}</span>
                                                @if (!empty($sbj['category']))
                                                    <span class="text-slate-300">•</span>
                                                    <span
                                                        class="uppercase text-[9px] font-extrabold text-purple-700">{{ $sbj['category'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Colonne 2 : Coeff & Horaires --}}
                                    <div
                                        class="sm:col-span-2 flex items-center justify-start sm:justify-center gap-1.5">
                                        <span
                                            class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-950 font-black text-[10px]">
                                            Coeff {{ $sbj['coefficient'] }}
                                        </span>
                                        <span
                                            class="px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-950 font-black text-[10px]">
                                            {{ $sbj['weekly_hours'] }} h/sem
                                        </span>
                                    </div>

                                    {{-- Colonne 3 : Choix de l'Enseignant --}}
                                    <div class="sm:col-span-5">
                                        <select name="class_assignments[{{ $idx }}][teacher_id]"
                                            x-model="assignments['{{ $sbj['id'] }}']"
                                            class="w-full text-xs font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 py-2">
                                            <option value="">-- Non assigné (Vacant) --</option>
                                            <template x-for="t in getTeachersForSubject(sbj)" :key="t.id">
                                                <option :value="t.id"
                                                    :selected="assignments['{{ $sbj['id'] }}'] == t.id">
                                                    <span
                                                        x-text="(isSpecialistFor(t, sbj) ? '⭐ ' : '') + t.name"></span>
                                                    (<span x-text="t.matricule"></span><span
                                                        x-text="t.speciality ? ' — ' + t.speciality : ''"></span>)
                                                    <span
                                                        x-text="t.is_assigned_to_this_class ? ' · [🌟 Titulaire]' : ''"></span>
                                                </option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Bilan & Boutons de Validation --}}
                        <div
                            class="p-6 bg-slate-50 border-t border-slate-200/80 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div class="text-xs font-bold text-slate-600">
                                Volume assigné : <strong class="text-purple-950"><span x-text="totalHours"></span>
                                    h/semaine</strong>
                                <span class="text-slate-400 font-medium">(<span x-text="assignedCount"></span> sur
                                    {{ $subjects->count() }} matière(s))</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <a href="{{ route('assignments.index') }}"
                                    class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                                    Annuler
                                </a>

                                <x-primary-button
                                    class="bg-gradient-to-r from-purple-950 to-indigo-950 hover:from-purple-900 hover:to-indigo-900 text-amber-300 font-black px-7 py-3 rounded-2xl shadow-lg transition flex items-center gap-2">
                                    <span>💾</span>
                                    <span>Enregistrer les cours de la classe (<span
                                            x-text="assignedCount"></span>)</span>
                                </x-primary-button>
                            </div>
                        </div>

                    </div>
                </form>
            @endif

        </div>
    </div>
</x-app-layout>
