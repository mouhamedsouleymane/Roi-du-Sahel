<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">➕ Nouvelle Classe</h2>
            <a href="{{ route('classes.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                ← Retour aux classes
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 max-w-4xl mx-auto animate-fade-in-up" x-data="{
            levels: @js(
    $levels->map(
        fn($l) => [
            'id' => $l->id,
            'name' => $l->name,
            'code' => $l->code,
            'cycle_id' => $l->cycle_id,
            'cycle_name' => $l->cycle?->name ?? 'Général',
        ],
    ),
),
            teachers: @js($teachers),
            selectedLevelId: '{{ old('level_id', $selectedLevelId ?? '') }}',
            section: 'A',
            name: '{{ old('name', '') }}',
            selectedTeacherId: '{{ old('main_teacher_id', '') }}',
        
            init() {
                if (this.selectedLevelId) {
                    this.onLevelChange();
                }
            },
        
            get currentLevel() {
                return this.levels.find(l => l.id == this.selectedLevelId) || null;
            },
        
            getLevelPrefix() {
                if (!this.currentLevel) return '';
                const code = (this.currentLevel.code || '').toUpperCase().trim();
                const map = {
                    'PS': 'Petite Section',
                    'MS': 'Moyenne Section',
                    'GS': 'Grande Section',
                    'CI': 'CI',
                    'CP': 'CP',
                    'CE1': 'CE1',
                    'CE2': 'CE2',
                    'CM1': 'CM1',
                    'CM2': 'CM2',
                    '6EME': '6ème',
                    '5EME': '5ème',
                    '4EME': '4ème',
                    '3EME': '3ème',
                    '2NDE_A': '2nde A',
                    '2NDE_C': '2nde C',
                    '1ERE_A': '1ère A',
                    '1ERE_C': '1ère C',
                    '1ERE_D': '1ère D',
                    'TLE_A': 'Terminale A',
                    'TLE_C': 'Terminale C',
                    'TLE_D': 'Terminale D'
                };
                return map[code] || this.currentLevel.name;
            },
        
            get isLycee() {
                return this.currentLevel && this.currentLevel.cycle_id === 4;
            },
        
            onLevelChange() {
                if (this.currentLevel) {
                    if (this.isLycee && !['1', '2', '3'].includes(this.section)) {
                        this.section = '1';
                    } else if (!this.isLycee && ['1', '2', '3'].includes(this.section)) {
                        this.section = 'A';
                    }
                    this.updateFullName();
                }
            },
        
            updateFullName() {
                const prefix = this.getLevelPrefix();
                if (!prefix) return;
                const separator = this.isLycee && /^\d+$/.test(this.section) ? '' : ' ';
                this.name = prefix + separator + this.section;
            },
        
            setSection(sec) {
                this.section = sec;
                this.updateFullName();
            },
        
            get filteredTeachers() {
                if (!this.currentLevel) {
                    return this.teachers;
                }
                const cycleId = this.currentLevel.cycle_id;
                return this.teachers.filter(t => {
                    if (!t.cycle_id) return true;
                    // Maternelle (1)
                    if (cycleId === 1) return t.cycle_id === 1;
                    // Primaire (2)
                    if (cycleId === 2) return t.cycle_id === 2;
                    // Collège (3) ou Lycée (4)
                    if (cycleId === 3 || cycleId === 4) return t.cycle_id === 3 || t.cycle_id === 4;
                    return true;
                });
            }
        }">
            <div class="bg-white shadow-xl rounded-3xl p-8 border border-slate-200/80">
                <form method="POST" action="{{ route('classes.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                            Année Scolaire
                        </label>
                        <select name="academic_year_id" required
                            class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}"
                                    {{ old('academic_year_id', $selectedYearId ?? $activeYear?->id) == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}{{ $year->is_active ? ' ✅ (Active)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_year_id')
                            <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                            Niveau Pédagogique <span class="text-rose-500">*</span>
                        </label>
                        <select name="level_id" required x-model="selectedLevelId" @change="onLevelChange()"
                            class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Choisir un niveau --</option>
                            @foreach ($levels as $level)
                                <option value="{{ $level->id }}">
                                    {{ $level->cycle->name }} — {{ $level->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('level_id')
                            <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider">
                                Nom de la Classe <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex items-center gap-1.5" x-show="selectedLevelId">
                                <span class="text-[11px] font-bold text-slate-400">Sections rapides :</span>
                                <template x-for="s in (isLycee ? ['1', '2', '3', 'A', 'B'] : ['A', 'B', 'C', 'D'])"
                                    :key="s">
                                    <button type="button" @click="setSection(s)"
                                        :class="section === s ? 'bg-purple-900 text-amber-300 ring-2 ring-purple-600' :
                                            'bg-purple-100 hover:bg-purple-200 text-purple-900'"
                                        class="px-2.5 py-0.5 text-[11px] font-black rounded-lg transition">
                                        <span x-text="s"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Champ avec Préfixe Dynamique Intégré --}}
                        <div class="flex items-center">
                            <span
                                class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-l-xl border border-r-0 border-purple-300 bg-purple-50 text-xs font-black text-purple-950 select-none shrink-0"
                                x-show="selectedLevelId">
                                <span>🏷️</span>
                                <span x-text="getLevelPrefix()"></span>
                            </span>
                            <input type="text" name="name" required placeholder="ex: 6ème A" x-model="name"
                                :class="selectedLevelId ? 'rounded-r-xl rounded-l-none' : 'rounded-xl'"
                                class="w-full text-sm font-bold border-purple-300 shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>

                        <div class="flex items-center justify-between mt-1.5" x-show="selectedLevelId && name">
                            <p class="text-[11px] text-slate-400 font-semibold">
                                Préfixe dynamique calculé en direct selon le niveau sélectionné.
                            </p>
                            <div
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900 text-[11px] font-bold">
                                <span>Aperçu :</span>
                                <strong class="font-black text-purple-950" x-text="name"></strong>
                            </div>
                        </div>
                        @error('name')
                            <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Capacité d'élèves (max)
                            </label>
                            <input type="number" name="capacity" min="1" max="100"
                                value="{{ old('capacity', 40) }}"
                                class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            @error('capacity')
                                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Numéro / Nom de la Salle (Optionnel)
                            </label>
                            <input type="text" name="room_number" placeholder="Salle 102"
                                value="{{ old('room_number') }}"
                                class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider">
                                Enseignant Principal / Titulaire (Optionnel)
                            </label>
                            <span class="text-[11px] font-bold text-slate-400" x-show="currentLevel">
                                Filtré pour : <strong class="text-purple-900"
                                    x-text="currentLevel ? currentLevel.cycle_name : ''"></strong>
                            </span>
                        </div>
                        <select name="main_teacher_id" x-model="selectedTeacherId"
                            class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Aucun --</option>
                            <template x-for="teacher in filteredTeachers" :key="teacher.id">
                                <option :value="teacher.id" :selected="selectedTeacherId == teacher.id">
                                    <span
                                        x-text="teacher.name + ' (' + teacher.speciality + ' - ' + teacher.cycle_name + ')'"></span>
                                </option>
                            </template>
                        </select>
                        <p class="text-[11px] text-slate-400 font-semibold mt-1">La liste s'ajuste dynamiquement selon
                            le cycle du niveau sélectionné.</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('classes.index') }}"
                            class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Annuler
                        </a>
                        <x-primary-button class="bg-purple-900 hover:bg-purple-800 shadow-md">
                            💾 Enregistrer la Classe
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
