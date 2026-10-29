<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                    <span>✏️</span>
                    <span>Modifier la Matière — {{ $subject->name }}</span>
                </h2>
                <p class="text-xs text-slate-500 font-semibold mt-0.5">
                    Mise à jour des informations et rattachement aux cycles et niveaux scolaires
                </p>
            </div>
            <a href="{{ route('subjects.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-purple-950 text-xs font-black rounded-xl transition shadow-2xs">
                ← Retour au catalogue
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            {{-- Alertes erreurs --}}
            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 p-4 rounded-2xl text-rose-900 shadow-xs">
                    <div class="flex items-center gap-2 font-black text-sm mb-1">
                        <span>⚠️</span> Erreurs de validation :
                    </div>
                    <ul class="list-disc list-inside text-xs font-semibold space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('subjects.update', $subject) }}"
                  x-data="{
                      cycles: @js($cycles),
                      selectedCycleIds: @js($cycles->pluck('id')),
                      selectedLevels: @js(old('level_ids', $existingLevelIds)),
                      coefficientsByLevel: @js($coefficientsByLevel),
                      
                      toggleCycle(cycleId) {
                          const idx = this.selectedCycleIds.indexOf(cycleId);
                          if (idx > -1) {
                              this.selectedCycleIds.splice(idx, 1);
                          } else {
                              this.selectedCycleIds.push(cycleId);
                          }
                      },
                      isCycleSelected(cycleId) {
                          return this.selectedCycleIds.includes(cycleId);
                      },
                      selectAllCycles() {
                          this.selectedCycleIds = this.cycles.map(c => c.id);
                      },
                      deselectAllCycles() {
                          this.selectedCycleIds = [];
                      },
                      toggleLevel(levelId) {
                          const idx = this.selectedLevels.indexOf(levelId);
                          if (idx > -1) {
                              this.selectedLevels.splice(idx, 1);
                          } else {
                              this.selectedLevels.push(levelId);
                          }
                      },
                      isLevelSelected(levelId) {
                          return this.selectedLevels.includes(levelId);
                      },
                      countSelectedInCycle(cycle) {
                          if (!cycle || !cycle.levels) return 0;
                          return cycle.levels.filter(l => this.selectedLevels.includes(l.id)).length;
                      },
                      selectAllInCycle(cycle) {
                          if (!cycle || !cycle.levels) return;
                          cycle.levels.forEach(lvl => {
                              if (!this.selectedLevels.includes(lvl.id)) {
                                  this.selectedLevels.push(lvl.id);
                              }
                          });
                      },
                      deselectAllInCycle(cycle) {
                          if (!cycle || !cycle.levels) return;
                          const ids = cycle.levels.map(l => l.id);
                          this.selectedLevels = this.selectedLevels.filter(id => !ids.includes(id));
                      },
                      selectAllLevelsOverall() {
                          const allLevelIds = [];
                          this.cycles.forEach(c => {
                              if (c.levels) {
                                  c.levels.forEach(l => allLevelIds.push(l.id));
                              }
                          });
                          this.selectedLevels = allLevelIds;
                      },
                      deselectAllLevelsOverall() {
                          this.selectedLevels = [];
                      },
                      getLevelCoeff(levelId) {
                          return this.coefficientsByLevel[levelId]?.coefficient || 1;
                      },
                      getLevelHours(levelId) {
                          return this.coefficientsByLevel[levelId]?.weekly_hours || 2;
                      }
                  }"
                  class="space-y-6">
                @csrf
                @method('PUT')

                {{-- BLOC 1 : INFORMATIONS FONDAMENTALES DE LA MATIÈRE --}}
                <div class="bg-white shadow-xl rounded-3xl p-6 sm:p-8 border border-slate-200/80 space-y-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-2xl bg-purple-100 text-purple-900 flex items-center justify-center font-black text-lg">
                            📖
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900">1. Informations Générales</h3>
                            <p class="text-xs text-slate-500 font-semibold">Identité pédagogique et classification</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                                Code de la Matière <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="code" required
                                   value="{{ old('code', $subject->code) }}"
                                   class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 uppercase" />
                            @error('code') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                                Catégorie Pédagogique <span class="text-rose-500">*</span>
                            </label>
                            <select name="category" required
                                    class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                @foreach ($categories as $catKey => $catLabel)
                                    <option value="{{ $catKey }}" {{ old('category', $subject->category) === $catKey ? 'selected' : '' }}>
                                        {{ $catLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                            Nom Complet de la Matière <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" required
                               value="{{ old('name', $subject->name) }}"
                               class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        @error('name') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                            Description ou Objectifs du Programme (optionnel)
                        </label>
                        <textarea name="description" rows="2"
                                  class="w-full text-xs font-semibold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">{{ old('description', $subject->description) }}</textarea>
                        @error('description') <span class="text-xs text-red-600 mt-1 block font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $subject->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                               class="rounded border-slate-300 text-purple-900 shadow-xs focus:ring-purple-500 w-4 h-4">
                        <label for="is_active" class="text-xs font-black text-slate-700 select-none cursor-pointer">
                            Matière active (disponible immédiatement pour les affectations, emplois du temps et évaluations)
                        </label>
                    </div>
                </div>

                {{-- BLOC 2 : SÉLECTION MULTIPLE DE CYCLES & NIVEAUX CONCERNÉS --}}
                <div class="bg-white shadow-xl rounded-3xl p-6 sm:p-8 border border-slate-200/80 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-900 flex items-center justify-center font-black text-lg">
                                🎓
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900">2. Cycles & Niveaux Concernés</h3>
                                <p class="text-xs text-slate-500 font-semibold">Ajustez les cycles et les niveaux scolaires où cette matière est enseignée</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black px-3 py-1.5 rounded-xl bg-purple-100 text-purple-950 border border-purple-200">
                                <span x-text="selectedLevels.length"></span> niveau(x) sélectionné(s)
                            </span>
                        </div>
                    </div>

                    {{-- ÉTAPE 2.1 : SÉLECTION MULTIPLE DES CYCLES --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">
                                Étape 1 : Sélectionner le(s) Cycle(s) Concerné(s)
                            </label>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="selectAllCycles()"
                                        class="text-[11px] font-bold text-purple-950 hover:underline px-2 py-0.5">
                                    ✓ Tous les cycles
                                </button>
                                <span class="text-slate-300">•</span>
                                <button type="button" @click="deselectAllCycles()"
                                        class="text-[11px] font-bold text-slate-500 hover:underline px-2 py-0.5">
                                    ✕ Aucun cycle
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <template x-for="cycle in cycles" :key="cycle.id">
                                <div @click="toggleCycle(cycle.id)"
                                     :class="isCycleSelected(cycle.id) 
                                         ? 'bg-gradient-to-r from-purple-950 to-indigo-950 text-white shadow-md ring-2 ring-purple-500/50' 
                                         : 'bg-slate-50 hover:bg-purple-50/40 text-slate-700 border border-slate-200'"
                                     class="p-4 rounded-2xl cursor-pointer transition-all duration-150 flex flex-col justify-between select-none relative overflow-hidden group">
                                    
                                    <div class="flex items-center justify-between w-full mb-2">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox"
                                                   :checked="isCycleSelected(cycle.id)"
                                                   @click.stop="toggleCycle(cycle.id)"
                                                   class="w-4 h-4 rounded text-amber-400 focus:ring-amber-400 cursor-pointer" />
                                            <span class="text-xs font-black uppercase tracking-wider" x-text="cycle.name"></span>
                                        </div>
                                        <span x-show="countSelectedInCycle(cycle) > 0"
                                              class="text-[10px] font-black px-2 py-0.5 rounded-full bg-amber-400 text-purple-950 shadow-2xs"
                                              x-text="countSelectedInCycle(cycle) + ' sel.'">
                                        </span>
                                    </div>
                                    
                                    <div class="text-[11px] opacity-75 font-semibold pl-6"
                                         x-text="(cycle.levels ? cycle.levels.length : 0) + ' niveaux'">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ÉTAPE 2.2 : NIVEAUX DÉPLIÉS POUR CHAQUE CYCLE SÉLECTIONNÉ --}}
                    <div class="pt-4 border-t border-slate-100 space-y-6">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-black text-slate-700 uppercase tracking-wider">
                                Étape 2 : Niveaux d'enseignement par cycle sélectionné
                            </label>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="selectAllLevelsOverall()"
                                        class="text-[11px] font-black px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                    ✓ Tout sélectionner partout
                                </button>
                                <button type="button" @click="deselectAllLevelsOverall()"
                                        class="text-[11px] font-black px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                    ✕ Tout désélectionner
                                </button>
                            </div>
                        </div>

                        <div x-show="selectedCycleIds.length === 0"
                             class="p-8 text-center bg-slate-50 border border-dashed border-slate-300 rounded-3xl text-slate-500 space-y-2">
                            <span class="text-3xl">👈</span>
                            <div class="text-xs font-black text-slate-700">Aucun cycle sélectionné</div>
                            <div class="text-[11px] text-slate-500 font-semibold">Veuillez cocher au moins un cycle ci-dessus pour afficher ses niveaux.</div>
                        </div>

                        {{-- Pour chaque cycle coché --}}
                        <template x-for="cycle in cycles" :key="'cycle-section-' + cycle.id">
                            <div x-show="isCycleSelected(cycle.id)"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="p-5 rounded-3xl bg-slate-50/70 border border-slate-200/80 space-y-4">
                                
                                {{-- En-tête du Cycle dans la liste --}}
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-200/80">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm">🏛️</span>
                                        <h4 class="text-sm font-black text-purple-950 uppercase tracking-wide" x-text="cycle.name"></h4>
                                        <span class="text-[11px] font-bold text-slate-500" x-text="'(' + countSelectedInCycle(cycle) + '/' + (cycle.levels ? cycle.levels.length : 0) + ' cochés)'"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="selectAllInCycle(cycle)"
                                                class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-white hover:bg-purple-50 text-purple-950 border border-slate-200 transition">
                                            ✓ Cocher tout <span x-text="cycle.name"></span>
                                        </button>
                                        <button type="button" @click="deselectAllInCycle(cycle)"
                                                class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 transition">
                                            ✕ Décocher
                                        </button>
                                    </div>
                                </div>

                                {{-- Si aucun niveau dans ce cycle --}}
                                <div x-show="!cycle.levels || cycle.levels.length === 0"
                                     class="p-4 text-center text-xs font-bold text-slate-400 bg-white rounded-2xl border border-slate-200">
                                    Aucun niveau configuré dans ce cycle.
                                </div>

                                {{-- Grille des Niveaux de ce Cycle --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <template x-for="level in (cycle.levels || [])" :key="level.id">
                                        <div :class="isLevelSelected(level.id) ? 'border-purple-500 bg-purple-50/50 shadow-sm ring-1 ring-purple-400/50' : 'border-slate-200 bg-white hover:border-slate-300'"
                                             class="p-3.5 rounded-2xl border transition-all duration-150 flex flex-col justify-between gap-2.5">
                                            
                                            {{-- En-tête du niveau --}}
                                            <div class="flex items-center justify-between cursor-pointer" @click="toggleLevel(level.id)">
                                                <div class="flex items-center gap-2.5">
                                                    <input type="checkbox"
                                                           name="level_ids[]"
                                                           :value="level.id"
                                                           :checked="isLevelSelected(level.id)"
                                                           @click.stop="toggleLevel(level.id)"
                                                           class="w-4 h-4 rounded text-purple-900 focus:ring-purple-500 cursor-pointer" />
                                                    <div>
                                                        <div class="text-xs font-black text-slate-900" x-text="level.name"></div>
                                                        <div class="text-[10px] font-bold text-slate-400" x-text="level.code"></div>
                                                    </div>
                                                </div>
                                                <span x-show="isLevelSelected(level.id)" class="text-[10px] font-black text-purple-900 bg-purple-100 px-2 py-0.5 rounded-lg">
                                                    Actif
                                                </span>
                                            </div>

                                            {{-- Réglages Coeff & Heures (si coché) --}}
                                            <div x-show="isLevelSelected(level.id)"
                                                 x-transition:enter="transition ease-out duration-150"
                                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 class="pt-2 border-t border-purple-100 grid grid-cols-2 gap-2 bg-white/90 p-2 rounded-xl">
                                                <div>
                                                    <label class="block text-[9px] font-black uppercase text-slate-600 mb-0.5">
                                                        Coeff.
                                                    </label>
                                                    <input type="number"
                                                           :name="'levels_data[' + level.id + '][coefficient]'"
                                                           min="1" max="20"
                                                           :value="getLevelCoeff(level.id)"
                                                           class="w-full text-xs font-bold border-purple-300 rounded-lg p-1 focus:ring-purple-500 focus:border-purple-500" />
                                                </div>
                                                <div>
                                                    <label class="block text-[9px] font-black uppercase text-slate-600 mb-0.5">
                                                        H / sem.
                                                    </label>
                                                    <input type="number"
                                                           :name="'levels_data[' + level.id + '][weekly_hours]'"
                                                           min="1" max="20"
                                                           :value="getLevelHours(level.id)"
                                                           class="w-full text-xs font-bold border-purple-300 rounded-lg p-1 focus:ring-purple-500 focus:border-purple-500" />
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- BOUTONS D'ACTION --}}
                <div class="flex items-center justify-between pt-4">
                    <a href="{{ route('subjects.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                        Annuler
                    </a>
                    <x-primary-button class="bg-gradient-to-r from-purple-950 to-indigo-950 hover:from-purple-900 hover:to-indigo-900 text-white font-black px-6 py-3 rounded-xl shadow-lg transition">
                        💾 Mettre à Jour la Matière & Ses Niveaux
                    </x-primary-button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
