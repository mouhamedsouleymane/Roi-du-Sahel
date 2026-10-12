<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                ➕ Ajouter un Enseignant
            </h2>
            <a href="{{ route('teachers.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                ← Retour au répertoire
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            {{-- Erreurs de validation --}}
            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 p-4 rounded-2xl text-rose-900 shadow-xs">
                    <div class="flex items-center gap-2 font-black text-sm mb-1">
                        <span>⚠️</span> Erreurs lors de l'enregistrement :
                    </div>
                    <ul class="list-disc list-inside text-xs font-semibold space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-xl rounded-3xl p-8 border border-slate-200/80"
                 x-data="{
                     cycles: @js($cycles),
                     polyvalentNames: @js(\App\Models\Teacher::polyvalentCycleNames()),
                     selectedCycleIds: @js(old('cycle_ids', [])),
                     selectedLevelIds: @js(old('level_ids', [])),

                     isCycleSelected(cycleId) {
                         return this.selectedCycleIds.includes(cycleId);
                     },
                     isPolyvalentCycle(cycle) {
                         if (!cycle || !cycle.name) return false;
                         return this.polyvalentNames.includes(cycle.name.toLowerCase().trim());
                     },
                     hasSpecialistCycleSelected() {
                         return this.selectedCycleIds.some(id => {
                             const c = this.cycles.find(cyc => cyc.id == id);
                             return c && !this.isPolyvalentCycle(c);
                         });
                     },
                     hasPolyvalentCycleSelected() {
                         return this.selectedCycleIds.some(id => {
                             const c = this.cycles.find(cyc => cyc.id == id);
                             return c && this.isPolyvalentCycle(c);
                         });
                     },
                     isCycleDisabled(cycle) {
                         if (!cycle) return false;
                         if (this.isCycleSelected(cycle.id)) return false;
                         if (this.selectedCycleIds.length === 0) return false;

                         // Dès qu'on coche Maternelle ou Primaire, on ne peut plus cocher aucun autre cycle
                         if (this.hasPolyvalentCycleSelected()) {
                             return true;
                         }

                         // Si on coche Collège ou Lycée, on ne peut cocher que Collège / Lycée (pas de Maternelle/Primaire)
                         if (this.hasSpecialistCycleSelected() && this.isPolyvalentCycle(cycle)) {
                             return true;
                         }

                         return false;
                     },
                     toggleCycle(cycleId) {
                         const cycle = this.cycles.find(c => c.id == cycleId);
                         if (!cycle) return;

                         if (this.isCycleDisabled(cycle)) return;

                         const idx = this.selectedCycleIds.indexOf(cycleId);
                         if (idx > -1) {
                             this.selectedCycleIds.splice(idx, 1);
                             // Deselect levels belonging to this removed cycle
                             if (cycle.levels) {
                                 const lvlIds = cycle.levels.map(l => l.id);
                                 this.selectedLevelIds = this.selectedLevelIds.filter(id => !lvlIds.includes(id));
                             }
                         } else {
                             // Si on sélectionne un cycle polyvalent (Maternelle ou Primaire), il est exclusif
                             if (this.isPolyvalentCycle(cycle)) {
                                 this.selectedCycleIds = [cycleId];
                                 this.selectedLevelIds = [];
                             } else {
                                 // Pour Collège / Lycée : retirer tout cycle polyvalent résiduel
                                 this.selectedCycleIds = this.selectedCycleIds.filter(id => {
                                     const c = this.cycles.find(cyc => cyc.id == id);
                                     return c && !this.isPolyvalentCycle(c);
                                 });
                                 this.selectedCycleIds.push(cycleId);
                                 // Ne pas cocher les niveaux automatiquement par défaut
                             }
                         }
                     },
                     toggleLevel(levelId) {
                         const idx = this.selectedLevelIds.indexOf(levelId);
                         if (idx > -1) {
                             this.selectedLevelIds.splice(idx, 1);
                         } else {
                             this.selectedLevelIds.push(levelId);
                         }
                     },
                     isLevelSelected(levelId) {
                         return this.selectedLevelIds.includes(levelId);
                     },
                     selectAllLevelsInCycle(cycle) {
                         if (!cycle || !cycle.levels) return;
                         cycle.levels.forEach(l => {
                             if (!this.selectedLevelIds.includes(l.id)) {
                                 this.selectedLevelIds.push(l.id);
                             }
                         });
                     },
                     deselectAllLevelsInCycle(cycle) {
                         if (!cycle || !cycle.levels) return;
                         const ids = cycle.levels.map(l => l.id);
                         this.selectedLevelIds = this.selectedLevelIds.filter(id => !ids.includes(id));
                     }
                 }">
                <form method="POST" action="{{ route('teachers.store') }}" class="space-y-8">
                    @csrf

                    {{-- ÉTAPE 1 : Compte Utilisateur --}}
                    <div>
                        <h3 class="text-sm font-black text-purple-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center text-xs font-black">1</span>
                            Compte d'Accès & Identité
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Nom Complet *</label>
                                <input type="text" name="name" required value="{{ old('name') }}" placeholder="ex: Amadou Abdou"
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Numéro Téléphone</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="ex: 90123456"
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('phone') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                <p class="text-[10px] text-slate-400 font-semibold mt-1">Utilisé pour la connexion</p>
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Adresse Email *</label>
                                <input type="email" name="email" required value="{{ old('email') }}" placeholder="enseignant@example.com"
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('email') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                <p class="text-[10px] text-slate-400 font-semibold mt-1">Mot de passe par défaut : <code class="bg-slate-100 px-1 rounded">password</code></p>
                            </div>
                        </div>
                    </div>

                    {{-- ÉTAPE 2 : Cycle(s) & Niveaux d'Enseignement --}}
                    <div class="border-t border-slate-100 pt-6 space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-black text-purple-900 uppercase tracking-widest flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-amber-100 flex items-center justify-center text-xs font-black">2</span>
                                    Cycle(s) & Niveaux d'Enseignement *
                                </h3>
                                <p class="text-[11px] font-semibold text-slate-500 mt-0.5">
                                    💡 <em>Maternelle et Primaire sont exclusifs. Collège et Lycée peuvent être combinés entre eux.</em>
                                </p>
                            </div>
                            <span class="text-xs font-bold text-slate-500">
                                <span x-text="selectedCycleIds.length"></span> cycle(s) sélectionné(s)
                            </span>
                        </div>

                        {{-- Grille des Cycles à cocher --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <template x-for="cycle in cycles" :key="cycle.id">
                                <div @click="toggleCycle(cycle.id)"
                                     :class="{
                                         'border-purple-600 bg-purple-50 shadow-md ring-2 ring-purple-400/50 cursor-pointer': isCycleSelected(cycle.id),
                                         'border-slate-200 bg-slate-50/60 hover:border-purple-300 cursor-pointer': !isCycleSelected(cycle.id) && !isCycleDisabled(cycle),
                                         'opacity-40 bg-slate-100/80 border-slate-200 cursor-not-allowed': isCycleDisabled(cycle)
                                     }"
                                     class="p-4 rounded-2xl border-2 transition-all flex flex-col justify-between select-none">
                                    
                                    <div class="flex items-start gap-3">
                                        <input type="checkbox"
                                               name="cycle_ids[]"
                                               :value="cycle.id"
                                               :checked="isCycleSelected(cycle.id)"
                                               :disabled="isCycleDisabled(cycle)"
                                               @click.stop="toggleCycle(cycle.id)"
                                               class="mt-1 rounded text-purple-700 focus:ring-purple-500 cursor-pointer disabled:cursor-not-allowed" />
                                        <div>
                                            <div class="font-black text-slate-900 text-sm" x-text="cycle.name"></div>
                                            <div class="text-[11px] font-bold text-slate-400" x-text="cycle.code"></div>
                                        </div>
                                    </div>

                                    <div class="mt-3 pt-2 border-t border-slate-200/60 flex items-center justify-between">
                                        <span x-show="isPolyvalentCycle(cycle)"
                                              class="inline-block text-[10px] font-black px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">
                                            🌟 Polyvalent
                                        </span>
                                        <span x-show="!isPolyvalentCycle(cycle)"
                                              class="inline-block text-[10px] font-black px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800">
                                            🎓 Spécialiste
                                        </span>
                                        <span x-show="isCycleDisabled(cycle)" class="text-[10px] font-bold text-slate-400">
                                            Non cumulable
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                        @error('cycle_ids') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror

                        {{-- Information Polyvalent si sélectionné --}}
                        <div x-show="hasPolyvalentCycleSelected()" x-transition class="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3 text-xs text-amber-900">
                            <span class="text-xl shrink-0">🌟</span>
                            <div>
                                <strong class="font-black block uppercase text-[11px] text-amber-950">Régime Polyvalent (Maternelle / Primaire)</strong>
                                <span class="font-semibold text-amber-800">L'enseignant prend en charge l'ensemble des matières de la classe unique qui lui sera attribuée.</span>
                            </div>
                        </div>

                        {{-- SECTIONS NIVEAUX CONCERNÉS (POUR COLLÈGE, LYCÉE ET CYCLES SPÉCIALISÉS) --}}
                        <div x-show="hasSpecialistCycleSelected()" x-transition class="space-y-4 pt-2">
                            <div class="flex items-center gap-2">
                                <span class="text-base">📚</span>
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">
                                    Sélectionnez les Niveaux à Enseigner (Collège / Lycée) :
                                </h4>
                            </div>

                            <template x-for="cycle in cycles" :key="'level-group-' + cycle.id">
                                <div x-show="isCycleSelected(cycle.id) && !isPolyvalentCycle(cycle)"
                                     class="p-5 bg-purple-50/40 rounded-2xl border border-purple-200/80 space-y-3">
                                    
                                    <div class="flex items-center justify-between pb-2 border-b border-purple-200/60">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-black uppercase text-purple-950" x-text="'Niveaux du cycle ' + cycle.name"></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="selectAllLevelsInCycle(cycle)"
                                                    class="text-[10px] font-bold px-2 py-1 rounded-lg bg-white text-purple-900 border border-purple-200 hover:bg-purple-100 transition">
                                                ✓ Tout cocher
                                            </button>
                                            <button type="button" @click="deselectAllLevelsInCycle(cycle)"
                                                    class="text-[10px] font-bold px-2 py-1 rounded-lg bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 transition">
                                                ✕ Tout décocher
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                        <template x-for="level in (cycle.levels || [])" :key="level.id">
                                            <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition select-none"
                                                   :class="isLevelSelected(level.id)
                                                       ? 'bg-purple-950 text-white border-purple-950 shadow-xs'
                                                       : 'bg-white text-slate-700 border-slate-200 hover:border-purple-300'">
                                                <input type="checkbox"
                                                       name="level_ids[]"
                                                       :value="level.id"
                                                       :checked="isLevelSelected(level.id)"
                                                       @click="toggleLevel(level.id)"
                                                       class="w-4 h-4 rounded text-amber-400 focus:ring-amber-400 cursor-pointer" />
                                                <span class="text-xs font-black" x-text="level.name"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ÉTAPE 3 : Profil Professionnel --}}
                    <div class="border-t border-slate-100 pt-6">
                        <h3 class="text-sm font-black text-purple-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center text-xs font-black">3</span>
                            Profil Professionnel & Spécialité
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Spécialité / Discipline *</label>
                                <input type="text" name="speciality" required value="{{ old('speciality') }}"
                                       placeholder="ex: Mathématiques, SVT, Français..."
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                @error('speciality') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Qualification / Diplôme</label>
                                <input type="text" name="qualification" value="{{ old('qualification') }}"
                                       placeholder="ex: Master 2, CAPES, Licence, BEPC..."
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Type de Contrat *</label>
                                <select name="employment_type" required
                                        class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">-- Choisir --</option>
                                    <option value="PERMANENT"   {{ old('employment_type', 'PERMANENT') == 'PERMANENT'   ? 'selected' : '' }}>Permanent / Titulaire</option>
                                    <option value="VACATAIRE"   {{ old('employment_type') == 'VACATAIRE'   ? 'selected' : '' }}>Vacataire</option>
                                    <option value="CONTRACTUEL" {{ old('employment_type') == 'CONTRACTUEL' ? 'selected' : '' }}>Contractuel</option>
                                </select>
                                @error('employment_type') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">Date de Recrutement</label>
                                <input type="date" name="hire_date" value="{{ old('hire_date', now()->toDateString()) }}"
                                       class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('teachers.index') }}"
                           class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Annuler
                        </a>
                        <x-primary-button class="bg-gradient-to-r from-purple-950 to-indigo-950 hover:from-purple-900 hover:to-indigo-900 text-white font-black px-6 py-3 rounded-xl shadow-md transition">
                            💾 Enregistrer l'Enseignant
                        </x-primary-button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
