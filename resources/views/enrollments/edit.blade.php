<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('enrollments.show', $enrollment) }}"
                   class="p-2.5 bg-white hover:bg-slate-100 text-purple-950 font-bold text-xs rounded-2xl shadow-sm border border-slate-200/80 transition">
                    ←
                </a>
                <div>
                    <h2 class="font-black text-2xl text-purple-950 leading-tight">
                        ✏️ Modifier l'Inscription
                    </h2>
                    <p class="text-xs font-bold text-slate-500 mt-1">
                        Élève : <span class="text-purple-950 font-extrabold">{{ $enrollment->student->last_name }} {{ $enrollment->student->first_name }}</span> ({{ $enrollment->enrollment_number }})
                    </p>
                </div>
            </div>
            <a href="{{ route('enrollments.show', $enrollment) }}" class="text-xs font-black text-purple-900 hover:underline">
                Annuler
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            @if (session('status'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-bold flex items-center gap-3 shadow-xs">
                    <span class="text-lg">✅</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <div class="bg-white shadow-xl rounded-3xl p-6 sm:p-8 border border-slate-200/80"
                 x-data="{
                     cycles: {{ Js::from($cycles) }},
                     levels: {{ Js::from($levels) }},
                     classes: {{ Js::from($classes) }},

                     selectedCycleId: '{{ old('cycle_id', $enrollment->schoolClass?->level?->cycle_id ?? '') }}',
                     selectedLevelId: '{{ old('level_id', $enrollment->schoolClass?->level_id ?? '') }}',
                     selectedClassId: '{{ old('class_id', $enrollment->class_id) }}',

                     get availableLevels() {
                         if (!this.selectedCycleId) return [];
                         return this.levels.filter(l => String(l.cycle_id) === String(this.selectedCycleId));
                     },

                     get availableClasses() {
                         if (!this.selectedLevelId) return [];
                         return this.classes.filter(c => String(c.level_id) === String(this.selectedLevelId));
                     },

                     onCycleChange() {
                         this.selectedLevelId = '';
                         this.selectedClassId = '';
                     },

                     onLevelChange() {
                         this.selectedClassId = '';
                     }
                 }">

                <!-- En-tête Élève & Inscription (readonly) -->
                <div class="p-5 bg-purple-50 rounded-2xl border border-purple-100 flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-purple-900 text-amber-300 font-black text-base flex items-center justify-center shadow-xs flex-shrink-0">
                        {{ mb_substr($enrollment->student->first_name, 0, 1) }}{{ mb_substr($enrollment->student->last_name, 0, 1) }}
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-purple-700 uppercase tracking-widest">N° Inscription : {{ $enrollment->enrollment_number }}</div>
                        <div class="text-lg font-black text-purple-950">{{ $enrollment->student->last_name }} {{ $enrollment->student->first_name }}</div>
                        <div class="text-xs text-slate-500 font-mono">Matricule : {{ $enrollment->student->matricule }} • Année : {{ $enrollment->academicYear->name }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('enrollments.update', $enrollment) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Affectation en cascade Cycle -> Niveau -> Classe -->
                    <div class="p-5 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-4">
                        <h3 class="text-xs font-black text-purple-950 uppercase tracking-widest flex items-center gap-2">
                            <span>🏫</span>
                            <span>Affectation Scolaire (Cycle &rarr; Niveau &rarr; Classe)</span>
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Cycle -->
                            <div>
                                <label for="cycle_id" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">1. Cycle *</label>
                                <select id="cycle_id" name="cycle_id" x-model="selectedCycleId" @change="onCycleChange()" required
                                        class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">-- Choisir le cycle --</option>
                                    <template x-for="c in cycles" :key="c.id">
                                        <option :value="String(c.id)" x-text="c.name" :selected="String(c.id) === selectedCycleId"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Niveau -->
                            <div>
                                <label for="level_id" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">2. Niveau *</label>
                                <select id="level_id" name="level_id" x-model="selectedLevelId" @change="onLevelChange()" required
                                        :disabled="!selectedCycleId"
                                        class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500 disabled:bg-slate-100 disabled:text-slate-400">
                                    <option value="">-- Choisir le niveau --</option>
                                    <template x-for="lvl in availableLevels" :key="lvl.id">
                                        <option :value="String(lvl.id)" x-text="lvl.name" :selected="String(lvl.id) === selectedLevelId"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Classe -->
                            <div>
                                <label for="class_id" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">3. Classe d'Affectation *</label>
                                <select id="class_id" name="class_id" x-model="selectedClassId" required
                                        :disabled="!selectedLevelId"
                                        class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500 disabled:bg-slate-100 disabled:text-slate-400">
                                    <option value="">-- Choisir la classe --</option>
                                    <template x-for="cls in availableClasses" :key="cls.id">
                                        <option :value="String(cls.id)" x-text="cls.name" :selected="String(cls.id) === selectedClassId"></option>
                                    </template>
                                </select>
                                @error('class_id') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label for="status" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Statut de l'Inscription *</label>
                            <select id="status" name="status" required class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                <option value="VALIDE" {{ old('status', $enrollment->status) === 'VALIDE' ? 'selected' : '' }}>Validé</option>
                                <option value="EN_ATTENTE" {{ old('status', $enrollment->status) === 'EN_ATTENTE' ? 'selected' : '' }}>En Attente</option>
                                <option value="ANNULE" {{ old('status', $enrollment->status) === 'ANNULE' ? 'selected' : '' }}>Annulé</option>
                            </select>
                            @error('status') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="type" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Type d'Admission *</label>
                            <select id="type" name="type" required class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                <option value="NOUVEAU" {{ old('type', $enrollment->type) === 'NOUVEAU' ? 'selected' : '' }}>Nouvelle Inscription</option>
                                <option value="REINSCRIPTION" {{ old('type', $enrollment->type) === 'REINSCRIPTION' ? 'selected' : '' }}>Réinscription</option>
                                <option value="TRANSFERT" {{ old('type', $enrollment->type) === 'TRANSFERT' ? 'selected' : '' }}>Transfert Entrant</option>
                            </select>
                            @error('type') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="enrollment_date" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Date d'Inscription *</label>
                            <input type="date" id="enrollment_date" name="enrollment_date" required
                                   value="{{ old('enrollment_date', $enrollment->enrollment_date ? $enrollment->enrollment_date->format('Y-m-d') : '') }}"
                                   class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            @error('enrollment_date') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-3">
                            <label class="relative flex items-center gap-3 cursor-pointer py-1">
                                <input type="checkbox" id="is_repeater" name="is_repeater" value="1" {{ old('is_repeater', $enrollment->is_repeater) ? 'checked' : '' }}
                                       class="w-5 h-5 rounded-lg border-slate-300 text-purple-900 focus:ring-purple-500" />
                                <span class="text-xs font-extrabold text-slate-700 uppercase">Élève Redoublant(e)</span>
                            </label>
                        </div>

                        <div class="md:col-span-3">
                            <label for="notes" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Observations / Remarques (Optionnel)</label>
                            <textarea id="notes" name="notes" rows="3" placeholder="Informations complémentaires sur cette inscription..."
                                      class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">{{ old('notes', $enrollment->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end items-center gap-3 pt-6 border-t border-slate-100">
                        <a href="{{ route('enrollments.show', $enrollment) }}"
                           class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-black uppercase tracking-wider rounded-2xl transition">
                            Annuler
                        </a>
                        <button type="submit"
                                class="px-6 py-3 bg-purple-900 hover:bg-purple-800 text-amber-300 text-xs font-black uppercase tracking-wider rounded-2xl shadow-lg hover:shadow-xl transition">
                            💾 Enregistrer les Modifications
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
