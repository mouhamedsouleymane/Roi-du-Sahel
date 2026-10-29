<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black uppercase tracking-wider px-2.5 py-1 rounded-lg bg-purple-100 text-purple-900">
                        {{ $cycle->name }}
                    </span>
                    <span class="text-xs text-slate-400 font-bold">Ordre : {{ $level->order_index }}</span>
                </div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight mt-1">
                    📖 Niveau : {{ $level->name }} ({{ $level->code }})
                </h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('cycles.show', $cycle) }}" class="text-xs font-black text-purple-900 hover:underline px-3 py-1.5">
                    ← Retour au cycle
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-8 animate-fade-in-up">

            {{-- Notifications --}}
            @if(session('status'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm font-bold flex items-center gap-2 shadow-xs">
                    <span>✅</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-2xl text-sm font-bold flex items-center gap-2 shadow-xs">
                    <span>⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Stat Cards du Niveau --}}
            @php
                $totalCoeffSum = $level->coefficients->sum('coefficient');
                $totalWeeklyHours = $level->coefficients->sum('weekly_hours');
                $classesCount = $level->classes->count();
                $totalStudents = $level->classes->sum('enrollments_count');
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-md">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-purple-600 mb-1">Matières Affectées</div>
                    <div class="text-3xl font-black text-purple-950">{{ $level->coefficients->count() }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">Au programme</div>
                </div>
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-md">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-indigo-600 mb-1">Total Coefficients</div>
                    <div class="text-3xl font-black text-indigo-700">{{ $totalCoeffSum }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">Poids total du barème</div>
                </div>
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-md">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-amber-600 mb-1">Volume Horaire</div>
                    <div class="text-3xl font-black text-amber-600">{{ $totalWeeklyHours }}h</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">Par semaine</div>
                </div>
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-md">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-emerald-600 mb-1">Classes & Élèves</div>
                    <div class="text-3xl font-black text-emerald-700">{{ $classesCount }} / {{ $totalStudents }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">{{ $classesCount }} classe(s) &bull; {{ $totalStudents }} élève(s)</div>
                </div>
            </div>

            {{-- SECTION : GESTION DES MATIÈRES & COEFFICIENTS DU NIVEAU --}}
            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80"
                 x-data="{
                     openAssign: {{ $errors->any() ? 'true' : 'false' }},
                     editModalOpen: false,
                     editCoeffId: null,
                     editSubjectId: null,
                     editSubjectName: '',
                     editSubjectCode: '',
                     editCoefficient: 2,
                     editWeeklyHours: 3,
                     openEditModal(id, subjectId, name, code, coeff, hours) {
                         this.editCoeffId = id;
                         this.editSubjectId = subjectId;
                         this.editSubjectName = name;
                         this.editSubjectCode = code;
                         this.editCoefficient = coeff;
                         this.editWeeklyHours = hours;
                         this.editModalOpen = true;
                     }
                 }">
                
                {{-- En-tête de section avec bouton toggle --}}
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-purple-900 text-white">
                    <div>
                        <h3 class="text-base font-black uppercase tracking-wider flex items-center gap-2">
                            <span>⚖️</span> Matières & Coefficients du Niveau ({{ $level->name }})
                        </h3>
                        <p class="text-xs text-purple-200 font-semibold mt-0.5">
                            Pondérations et volumes horaires des cours pour ce palier d'apprentissage
                        </p>
                    </div>
                    <button type="button" @click="openAssign = !openAssign"
                            class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-black transition shadow-xs flex items-center gap-1.5 self-start sm:self-auto">
                        <span x-text="openAssign ? 'Masquer le formulaire ✕' : '➕ Ajouter une matière'"></span>
                    </button>
                </div>

                {{-- Formulaire d'attribution de coefficient pour ce niveau --}}
                <div x-show="openAssign" style="display:none" class="p-6 bg-purple-50/40 border-b border-purple-100 animate-fade-in-up">
                    <form method="POST" action="{{ route('subjects.coefficients.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        @csrf
                        <input type="hidden" name="level_id" value="{{ $level->id }}">

                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Choisir la Matière
                            </label>
                            <select name="subject_id" required
                                    class="w-full text-xs font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                <option value="">-- Sélectionner une discipline --</option>
                                @foreach ($subjects as $sbj)
                                    <option value="{{ $sbj->id }}" {{ old('subject_id') == $sbj->id ? 'selected' : '' }}>
                                        {{ $sbj->name }} ({{ $sbj->code }}) — {{ ucfirst($sbj->category) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id') <span class="text-[11px] text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Coefficient
                            </label>
                            <input type="number" name="coefficient" value="{{ old('coefficient', 2) }}" min="1" max="20" required
                                   class="w-full text-xs font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            @error('coefficient') <span class="text-[11px] text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                Volume Horaire (Heures/Semaine)
                            </label>
                            <input type="number" name="weekly_hours" value="{{ old('weekly_hours', 3) }}" min="1" max="20" required
                                   class="w-full text-xs font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                            @error('weekly_hours') <span class="text-[11px] text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <x-primary-button class="w-full justify-center bg-purple-900 hover:bg-purple-800 text-xs font-black shadow-md py-2.5">
                                💾 Enregistrer le Coefficient
                            </x-primary-button>
                        </div>
                    </form>
                </div>

                {{-- Liste des matières actuellement configurées pour ce niveau --}}
                <div class="p-6">
                    @if($level->coefficients->isEmpty())
                        <div class="text-center py-12">
                            <div class="text-4xl mb-3">📚</div>
                            <div class="text-sm font-bold text-slate-700">Aucune matière affectée à ce niveau</div>
                            <p class="text-xs text-slate-400 mt-1">Utilisez le bouton ci-dessus pour attribuer des matières et coefficients au niveau {{ $level->name }}.</p>
                            <button type="button" @click="openAssign = true" class="inline-block mt-4 px-4 py-2 text-xs font-black bg-purple-900 text-white rounded-xl shadow-md">
                                ➕ Définir la première matière
                            </button>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-700">
                                <thead class="bg-slate-50 text-[11px] uppercase font-black tracking-wider text-slate-500 border-b border-slate-200">
                                    <tr>
                                        <th class="py-3 px-4">Code</th>
                                        <th class="py-3 px-4">Matière</th>
                                        <th class="py-3 px-4">Catégorie</th>
                                        <th class="py-3 px-4 text-center">Coefficient</th>
                                        <th class="py-3 px-4 text-center">Volume Horaire</th>
                                        <th class="py-3 px-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-semibold">
                                    @foreach ($level->coefficients as $coeff)
                                        <tr class="hover:bg-purple-50/30 transition">
                                            <td class="py-3.5 px-4 font-black text-purple-950">
                                                <span class="px-2 py-0.5 rounded-lg bg-purple-100 text-purple-900 text-[11px]">
                                                    {{ $coeff->subject->code }}
                                                </span>
                                            </td>
                                            <td class="py-3.5 px-4 font-bold text-slate-900 text-sm">
                                                {{ $coeff->subject->name }}
                                            </td>
                                            <td class="py-3.5 px-4">
                                                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md bg-slate-100 text-slate-700">
                                                    {{ $coeff->subject->category }}
                                                </span>
                                            </td>
                                            <td class="py-3.5 px-4 text-center">
                                                <span class="px-2.5 py-1 rounded-xl bg-purple-100 text-purple-950 font-black text-xs">
                                                    Coeff {{ $coeff->coefficient }}
                                                </span>
                                            </td>
                                            <td class="py-3.5 px-4 text-center font-bold text-slate-600">
                                                {{ $coeff->weekly_hours }}h / semaine
                                            </td>
                                            <td class="py-3.5 px-4 text-right">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    <button type="button"
                                                            @click="openEditModal('{{ $coeff->id }}', '{{ $coeff->subject_id }}', '{{ addslashes($coeff->subject->name) }}', '{{ $coeff->subject->code }}', {{ $coeff->coefficient }}, {{ $coeff->weekly_hours }})"
                                                            class="px-2.5 py-1.5 rounded-xl bg-purple-50 text-purple-950 hover:bg-purple-100 border border-purple-200 font-bold transition text-xs flex items-center gap-1 shadow-2xs"
                                                            title="Modifier le coefficient et les heures">
                                                        <span>✏️</span>
                                                        <span>Modifier</span>
                                                    </button>

                                                    <form method="POST" action="{{ route('subjects.coefficients.destroy', $coeff) }}" class="inline"
                                                          onsubmit="return confirm('Retirer la matière {{ $coeff->subject->name }} de ce niveau ?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 font-bold transition text-xs flex items-center gap-1 shadow-2xs" title="Retirer">
                                                            <span>🗑️</span>
                                                            <span>Retirer</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- MODAL DE MODIFICATION DU COEFFICIENT & VOLUME HORAIRE --}}
                <template x-teleport="body">
                    <div x-show="editModalOpen" style="display: none;"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 z-[100] overflow-y-auto flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs">
                        
                        <div @click.away="editModalOpen = false"
                             x-show="editModalOpen"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden text-left my-auto">
                            
                            <div class="p-5 bg-gradient-to-r from-purple-950 to-indigo-950 text-white flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-xl">✏️</span>
                                    <div>
                                        <h3 class="text-sm font-black uppercase tracking-wider">Modifier la Pondération</h3>
                                        <p class="text-xs text-purple-200 font-semibold">Niveau : {{ $level->name }}</p>
                                    </div>
                                </div>
                                <button type="button" @click="editModalOpen = false" class="text-white/80 hover:text-white text-lg font-black p-1 transition">
                                    ✕
                                </button>
                            </div>

                            <form method="POST" action="{{ route('subjects.coefficients.store') }}" class="p-6 space-y-4">
                                @csrf
                                <input type="hidden" name="level_id" value="{{ $level->id }}">
                                <input type="hidden" name="subject_id" :value="editSubjectId">

                                <div class="p-3.5 bg-purple-50/60 rounded-2xl border border-purple-100 flex items-center justify-between">
                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-wider text-purple-900">Matière</div>
                                        <div class="text-sm font-black text-purple-950 mt-0.5" x-text="editSubjectName"></div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-xl bg-purple-200 text-purple-950 font-black text-xs" x-text="editSubjectCode"></span>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                            Coefficient <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" name="coefficient" x-model="editCoefficient" min="1" max="20" required
                                               class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                    </div>

                                    <div>
                                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                            Heures / sem. <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" name="weekly_hours" x-model="editWeeklyHours" min="1" max="20" required
                                               class="w-full text-sm font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                                    <button type="button" @click="editModalOpen = false"
                                            class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                                        Annuler
                                    </button>
                                    <x-primary-button class="bg-gradient-to-r from-purple-950 to-indigo-950 hover:from-purple-900 hover:to-indigo-900 text-white text-xs font-black px-5 py-2.5 rounded-xl shadow-md transition">
                                        💾 Mettre à jour
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </template>
            </div>

            {{-- SECTION : CLASSES DU NIVEAU --}}
            <div class="bg-white shadow-xl rounded-3xl p-6 sm:p-8 border border-slate-200/80">
                <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <span>🏫</span> Classes rattachées au niveau ({{ $level->classes->count() }})
                        </h3>
                        <p class="text-xs text-slate-500 font-semibold mt-0.5">
                            Groupes scolaires actifs appliquant ce barème
                        </p>
                    </div>
                    <a href="{{ route('classes.create') }}" class="px-3 py-1.5 text-xs font-black bg-purple-900 text-white rounded-xl shadow-xs hover:bg-purple-800 transition">
                        ➕ Créer une classe
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @forelse ($level->classes as $cls)
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col justify-between hover:border-purple-200 transition">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-black text-slate-900 text-sm">{{ $cls->name }}</span>
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">
                                    {{ $cls->enrollments_count ?? 0 }}/{{ $cls->capacity }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 font-semibold">
                                <div>📍 {{ $cls->room_number ?? 'Salle non définie' }}</div>
                            </div>
                            <div class="mt-3 pt-2 border-t border-slate-200 flex justify-end gap-2">
                                <a href="{{ route('classes.show', $cls) }}" class="text-xs font-bold text-indigo-700 hover:underline">
                                    Voir la classe →
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-6 text-xs text-slate-400 font-bold italic">
                            Aucune classe n'a encore été créée pour ce niveau.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
