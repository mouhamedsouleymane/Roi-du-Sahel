<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                    <span>✏️</span>
                    <span>Modifier le Créneau de Cours</span>
                </h2>
                <p class="text-xs text-slate-500 font-semibold mt-1">
                    {{ $schedule->schoolClass?->level?->cycle?->name }} • {{ $schedule->schoolClass?->level?->name }} • {{ $schedule->schoolClass?->name }} — {{ $schedule->subject?->name }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('schedules.show', $schedule) }}" 
                   class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-purple-950 font-black text-xs rounded-xl transition shadow-xs">
                    👁️ Voir Détail
                </a>
                <a href="{{ route('schedules.index', ['class_id' => $schedule->class_id]) }}" 
                   class="text-xs font-black text-purple-900 hover:underline">
                    ← Retour à l'emploi du temps
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            {{-- Alertes de Collisions / Erreurs --}}
            @if ($errors->any())
                <div class="bg-rose-50 border-l-4 border-rose-500 p-5 rounded-2xl shadow-xs">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl">⚠️</span>
                        <div>
                            <h3 class="text-xs font-black text-rose-900 uppercase tracking-wide">Erreur lors de la modification</h3>
                            <ul class="text-xs font-bold text-rose-700 mt-1 list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white shadow-xl rounded-3xl p-8 border border-slate-200/80"
                 x-data="{
                     mode: '{{ $classAssignments->contains(fn($a) => $a->subject_id == $schedule->subject_id && $a->teacher_id == $schedule->teacher_id) ? 'assignment' : 'manual' }}',
                     classId: '{{ old('class_id', $schedule->class_id) }}',
                     subjectId: '{{ old('subject_id', $schedule->subject_id) }}',
                     teacherId: '{{ old('teacher_id', $schedule->teacher_id) }}',
                     startTime: '{{ old('start_time', substr($schedule->start_time, 0, 5)) }}',
                     endTime: '{{ old('end_time', substr($schedule->end_time, 0, 5)) }}',
                     onAssignmentChange(e) {
                         const val = e.target.value;
                         if (!val) return;
                         const parts = val.split('_');
                         this.subjectId = parts[0];
                         this.teacherId = parts[1];
                     }
                 }">

                <div class="flex items-center justify-between pb-6 border-b border-slate-100 mb-6">
                    <div>
                        <h3 class="text-base font-black text-slate-900">Ajuster les paramètres du créneau</h3>
                        <p class="text-xs text-slate-500">Modifiez les horaires, le jour ou l'attribution enseignant/matière</p>
                    </div>
                    <div class="flex items-center bg-slate-100 p-1 rounded-xl">
                        <button type="button" @click="mode = 'assignment'"
                                :class="mode === 'assignment' ? 'bg-white shadow-xs text-purple-950 font-black' : 'text-slate-600 font-bold hover:text-slate-900'"
                                class="px-3 py-1.5 text-xs rounded-lg transition">
                            🔗 Cours Officiel
                        </button>
                        <button type="button" @click="mode = 'manual'"
                                :class="mode === 'manual' ? 'bg-white shadow-xs text-purple-950 font-black' : 'text-slate-600 font-bold hover:text-slate-900'"
                                class="px-3 py-1.5 text-xs rounded-lg transition">
                            ✍️ Sélection Libre
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ route('schedules.update', $schedule) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Classe avec Optgroup --}}
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Classe Concernée *</label>
                        <select name="class_id" x-model="classId" required class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            @php
                                $classesByCycle = $classes->groupBy(fn($c) => $c->level->cycle->name ?? 'Autre');
                            @endphp
                            @foreach ($classesByCycle as $cycleName => $cycleClasses)
                                <optgroup label="── {{ strtoupper($cycleName) }} ──">
                                    @foreach ($cycleClasses->groupBy(fn($c) => $c->level->name ?? 'Autre') as $levelName => $levelClasses)
                                        @foreach ($levelClasses as $cls)
                                            <option value="{{ $cls->id }}" {{ old('class_id', $schedule->class_id) == $cls->id ? 'selected' : '' }}>
                                                {{ $levelName }} • {{ $cls->name }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    {{-- Mode Affectation Officielle --}}
                    <div x-show="mode === 'assignment'" class="bg-purple-50/50 p-5 rounded-2xl border border-purple-100 space-y-3">
                        <label class="block text-xs font-black text-purple-950 uppercase tracking-wider">
                            Matière & Enseignant Attitré *
                        </label>
                        @if ($classAssignments->isNotEmpty())
                            <select @change="onAssignmentChange($event)" class="w-full text-sm font-bold border-purple-200 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 bg-white">
                                @foreach ($classAssignments as $assignment)
                                    <option value="{{ $assignment->subject_id }}_{{ $assignment->teacher_id }}"
                                            {{ ($schedule->subject_id == $assignment->subject_id && $schedule->teacher_id == $assignment->teacher_id) ? 'selected' : '' }}>
                                        📚 {{ $assignment->subject?->name }} ({{ $assignment->subject?->code }}) — 👨‍🏫 {{ $assignment->teacher?->user?->name }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div class="text-xs font-bold text-amber-700 bg-amber-50 p-3 rounded-xl border border-amber-200">
                                ⚠️ Aucune affectation officielle pour cette classe. Utilisez le mode "Sélection Libre".
                            </div>
                        @endif
                    </div>

                    {{-- Mode Sélection Libre --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-show="mode === 'manual'">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Matière *</label>
                            <select name="subject_id" x-model="subjectId" :required="mode === 'manual'" class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id', $schedule->subject_id) == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }} ({{ $subject->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Enseignant *</label>
                            <select name="teacher_id" x-model="teacherId" :required="mode === 'manual'" class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('teacher_id', $schedule->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->user?->name }}{{ $teacher->speciality ? ' (' . $teacher->speciality . ')' : '' }}
                                    </option>
                                @endforeach
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

                    {{-- Paramètres Horaires et Salle --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 pt-4 border-t border-slate-100">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Jour *</label>
                            <select name="day_of_week" required class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                @foreach (['LUNDI' => 'Lundi', 'MARDI' => 'Mardi', 'MERCREDI' => 'Mercredi', 'JEUDI' => 'Jeudi', 'VENDREDI' => 'Vendredi', 'SAMEDI' => 'Samedi'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('day_of_week', $schedule->day_of_week) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Heure de Début *</label>
                            <input type="time" name="start_time" required x-model="startTime"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Heure de Fin *</label>
                            <input type="time" name="end_time" required x-model="endTime"
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Salle</label>
                            <input type="text" name="room_number" value="{{ old('room_number', $schedule->room_number) }}" placeholder="Ex: Salle 104, Labo..."
                                   class="w-full text-sm font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-6 border-t border-slate-100">
                        <a href="{{ route('schedules.index', ['class_id' => $schedule->class_id]) }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Annuler
                        </a>
                        <x-primary-button class="bg-purple-900 hover:bg-purple-950 shadow-md">
                            💾 Mettre à Jour le Créneau
                        </x-primary-button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
