<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight tracking-tight flex items-center gap-2">
                    <span>📅</span>
                    <span>Emplois du Temps Hebdomadaires</span>
                </h2>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-2">
                    @if ($activeYear)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-purple-100 text-purple-900 border border-purple-200">
                            ✨ {{ $activeYear->name }}
                        </span>
                    @endif
                    <span class="text-slate-400 font-semibold">• {{ $totalSlotsCount }} créneau(x) programmé(s) au total</span>
                </p>
            </div>
            <div class="flex items-center gap-2 no-print">
                <a href="{{ route('schedules.create', ['class_id' => $selectedClassId]) }}"
                   class="px-4 py-2 bg-gradient-to-r from-purple-950 to-indigo-950 hover:from-purple-900 hover:to-indigo-900 text-white text-xs font-black rounded-xl shadow-sm transition flex items-center gap-1.5">
                    <span>➕</span>
                    <span>Nouveau Créneau</span>
                </a>
                <button onclick="window.print()" class="px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl shadow-2xs transition flex items-center gap-1.5">
                    🖨️ Imprimer
                </button>
            </div>
        </div>
    </x-slot>

    @php
        $daysList = [
            'LUNDI'    => 'Lundi',
            'MARDI'    => 'Mardi',
            'MERCREDI' => 'Mercredi',
            'JEUDI'    => 'Jeudi',
            'VENDREDI' => 'Vendredi',
            'SAMEDI'   => 'Samedi',
        ];

        $timeSlots = [
            ['start' => '07:00', 'end' => '08:00', 'label' => '07:00 - 08:00'],
            ['start' => '08:00', 'end' => '09:00', 'label' => '08:00 - 09:00'],
            ['start' => '09:00', 'end' => '10:00', 'label' => '09:00 - 10:00'],
            ['start' => '10:00', 'end' => '11:00', 'label' => '10:00 - 11:00'],
            ['start' => '11:00', 'end' => '12:00', 'label' => '11:00 - 12:00'],
            ['start' => '12:00', 'end' => '13:00', 'label' => '12:00 - 13:00', 'is_pause' => true],
            ['start' => '13:00', 'end' => '14:00', 'label' => '13:00 - 14:00'],
            ['start' => '14:00', 'end' => '15:00', 'label' => '14:00 - 15:00'],
            ['start' => '15:00', 'end' => '16:00', 'label' => '15:00 - 16:00'],
            ['start' => '16:00', 'end' => '17:00', 'label' => '16:00 - 17:00'],
            ['start' => '17:00', 'end' => '18:00', 'label' => '17:00 - 18:00'],
        ];
    @endphp

    <div class="py-6 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen"
         x-data="{
             duplicateModal: false,
             duplicateScheduleId: null,
             duplicateSubjectName: '',
             duplicateTargetDay: 'MARDI',
             duplicateStartTime: '08:00',
             duplicateEndTime: '10:00',
             activeCycleFilter: 'all',
             openDuplicateModal(id, name, day, start, end) {
                 this.duplicateScheduleId = id;
                 this.duplicateSubjectName = name;
                 this.duplicateTargetDay = day;
                 this.duplicateStartTime = start;
                 this.duplicateEndTime = end;
                 this.duplicateModal = true;
             }
         }">
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

            @if ($errors->has('conflict'))
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-950 flex items-center gap-3 shadow-md">
                    <span class="text-3xl">🚫</span>
                    <div>
                        <div class="font-black text-sm text-rose-900">Collision d'Emploi du Temps Détectée</div>
                        <div class="text-xs text-rose-800 mt-0.5 font-bold leading-relaxed">{{ $errors->first('conflict') }}</div>
                    </div>
                </div>
            @endif

            {{-- ── BARRE DE NAVIGATION & SÉLECTION ──────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm no-print p-4 space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    {{-- Onglets Vue Classe / Vue Enseignant --}}
                    <div class="flex items-center gap-2 bg-slate-100 p-1.5 rounded-2xl">
                        <a href="{{ route('schedules.index', ['view_type' => 'class', 'class_id' => $selectedClassId]) }}"
                           class="px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-1.5 {{ $viewType === 'class' ? 'bg-purple-950 text-white shadow-xs' : 'text-slate-600 hover:text-purple-950' }}">
                            <span>🏫</span>
                            <span>Vue par Classe</span>
                        </a>
                        <a href="{{ route('schedules.index', ['view_type' => 'teacher', 'teacher_id' => $selectedTeacherId]) }}"
                           class="px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-1.5 {{ $viewType === 'teacher' ? 'bg-purple-950 text-white shadow-xs' : 'text-slate-600 hover:text-purple-950' }}">
                            <span>👨‍🏫</span>
                            <span>Vue par Enseignant</span>
                        </a>
                    </div>

                    {{-- Formulaire de Sélection Rapide --}}
                    <form method="GET" action="{{ route('schedules.index') }}" class="flex items-center gap-3 flex-1 md:justify-end">
                        <input type="hidden" name="view_type" value="{{ $viewType }}">

                        @if ($viewType === 'teacher')
                            <label for="teacher_id" class="text-xs font-extrabold text-slate-700 shrink-0">👨‍🏫 Enseignant :</label>
                            <select id="teacher_id" name="teacher_id" onchange="this.form.submit()"
                                    class="text-xs font-bold rounded-xl border-slate-300 focus:border-purple-600 focus:ring-purple-600 min-w-[260px]">
                                <option value="">🌐 Tous les enseignants (Grilles globales)</option>
                                @foreach ($teachers as $t)
                                    <option value="{{ $t->id }}" {{ $selectedTeacherId == $t->id ? 'selected' : '' }}>
                                        👨‍🏫 {{ $t->user->name }}{{ $t->speciality ? ' — ' . $t->speciality : '' }} ({{ $t->schedules->count() }} cours)
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <label for="class_id" class="text-xs font-extrabold text-slate-700 shrink-0">🏫 Classe :</label>
                            <select id="class_id" name="class_id" onchange="this.form.submit()"
                                    class="text-xs font-bold rounded-xl border-slate-300 focus:border-purple-600 focus:ring-purple-600 min-w-[260px]">
                                <option value="">🌐 Toutes les classes (Grilles globales)</option>
                                @php
                                    $classesByCycle = $classes->groupBy(fn($c) => $c->level->cycle->name ?? 'Autre');
                                @endphp
                                @foreach ($classesByCycle as $cycleName => $cycleClasses)
                                    <optgroup label="── {{ strtoupper($cycleName) }} ──">
                                        @foreach ($cycleClasses->groupBy(fn($c) => $c->level->name ?? 'Autre') as $levelName => $levelClasses)
                                            @foreach ($levelClasses as $cls)
                                                <option value="{{ $cls->id }}" {{ $selectedClassId == $cls->id ? 'selected' : '' }}>
                                                    {{ $levelName }} • {{ $cls->name }} ({{ $cls->schedules->count() }} créneaux)
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        @endif

                        @if ($selectedClassId || $selectedTeacherId)
                            <a href="{{ route('schedules.index', ['view_type' => $viewType]) }}"
                               class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition"
                               title="Afficher toutes les grilles">
                                ✕ Tous
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- CASE 1 : VUE D'ENSEMBLE DE TOUTES LES CLASSES (GRILLES MATRICIELLES)     --}}
            {{-- ========================================================================= --}}
            @if ($viewType === 'class' && ! $selectedClassId)
                @php
                    $classesByCycle = $classes->groupBy(fn($c) => $c->level->cycle->name ?? 'Non défini');
                @endphp

                {{-- Filtres par cycle --}}
                <div class="flex items-center gap-2 overflow-x-auto pb-2 no-print">
                    <button type="button" @click="activeCycleFilter = 'all'"
                            :class="activeCycleFilter === 'all' ? 'bg-purple-950 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                            class="px-4 py-2 rounded-xl text-xs font-black transition whitespace-nowrap">
                        🌐 Tous les cycles ({{ $classes->count() }} classes)
                    </button>
                    @foreach ($classesByCycle as $cycleName => $cycleClassesList)
                        <button type="button" @click="activeCycleFilter = '{{ Str::slug($cycleName) }}'"
                                :class="activeCycleFilter === '{{ Str::slug($cycleName) }}' ? 'bg-purple-950 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                class="px-4 py-2 rounded-xl text-xs font-black transition whitespace-nowrap">
                            🏫 {{ $cycleName }} ({{ $cycleClassesList->count() }})
                        </button>
                    @endforeach
                </div>

                {{-- Liste des classes et de leurs grilles matricielles --}}
                <div class="space-y-10">
                    @foreach ($classesByCycle as $cycleName => $cycleClassesList)
                        <div x-show="activeCycleFilter === 'all' || activeCycleFilter === '{{ Str::slug($cycleName) }}'" class="space-y-6">
                            
                            {{-- En-tête de Cycle --}}
                            <div class="flex items-center justify-between border-b-2 border-purple-200 pb-2">
                                <h3 class="text-xl font-black text-purple-950 flex items-center gap-2">
                                    <span class="text-2xl">🏫</span>
                                    <span>Cycle {{ $cycleName }}</span>
                                </h3>
                                <span class="text-xs font-bold text-purple-800 bg-purple-100 px-3 py-1 rounded-full">
                                    {{ $cycleClassesList->sum(fn($c) => $c->schedules->count()) }} cours programmés
                                </span>
                            </div>

                            @foreach ($cycleClassesList as $cls)
                                @php
                                    $classSchedules = $cls->schedules;
                                    $hasSchedules = $classSchedules->isNotEmpty();
                                @endphp

                                <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden space-y-4 p-5">
                                    
                                    {{-- Titre de la classe & Actions rapides --}}
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs font-black text-purple-900 uppercase tracking-wider bg-purple-100 px-2.5 py-1 rounded-lg">
                                                {{ $cls->level->name ?? 'Niveau' }}
                                            </span>
                                            <h4 class="text-lg font-black text-purple-950">
                                                Classe : {{ $cls->name }}
                                            </h4>
                                            <span class="text-xs font-extrabold text-slate-500">
                                                • {{ $classSchedules->count() }} créneau(x)
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 no-print">
                                            <a href="{{ route('schedules.index', ['view_type' => 'class', 'class_id' => $cls->id]) }}"
                                               class="px-3.5 py-1.5 bg-purple-950 hover:bg-purple-900 text-white font-black text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                                                <span>⚙️</span>
                                                <span>Gérer & Programmer</span>
                                            </a>
                                            <a href="{{ route('schedules.export-pdf', $cls) }}"
                                               target="_blank"
                                               class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-black text-xs rounded-xl transition flex items-center gap-1.5"
                                               title="Exporter l'emploi du temps en PDF">
                                                <span>📄</span>
                                                <span>PDF</span>
                                            </a>
                                            <a href="{{ route('schedules.create', ['class_id' => $cls->id]) }}"
                                               class="px-3 py-1.5 bg-white hover:bg-purple-50 text-purple-950 border border-slate-200 font-black text-xs rounded-xl transition"
                                               title="Ajouter un créneau">
                                                ➕
                                            </a>
                                        </div>

                                    </div>

                                    @if ($hasSchedules)
                                        {{-- ── TABLEAU GRILLE : LIGNES = HEURES, COLONNES = JOURS ── --}}
                                        <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-2xs">
                                            <table class="w-full text-left border-collapse min-w-[700px]">
                                                <thead>
                                                    <tr class="bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white text-xs font-black uppercase tracking-wider">
                                                        <th class="p-3 text-center w-28 bg-purple-950/90 border-r border-purple-800">
                                                            🕒 Horaires
                                                        </th>
                                                        @foreach ($daysList as $dayKey => $dayLabel)
                                                            <th class="p-3 text-center border-r border-purple-800/60 last:border-r-0">
                                                                {{ $dayLabel }}
                                                            </th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 text-xs">
                                                    @foreach ($timeSlots as $slotRange)
                                                        @php
                                                            $isPause = $slotRange['is_pause'] ?? false;
                                                            // Filtrer si au moins un jour a un cours dans cette tranche ou si c'est une tranche active
                                                            $matchingSlotsInRow = $classSchedules->filter(function ($s) use ($slotRange) {
                                                                return substr($s->start_time, 0, 5) < $slotRange['end'] && substr($s->end_time, 0, 5) > $slotRange['start'];
                                                            });
                                                        @endphp
                                                        @if ($matchingSlotsInRow->isNotEmpty() || in_array($slotRange['start'], ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00']))
                                                            <tr class="hover:bg-purple-50/20 transition {{ $isPause ? 'bg-slate-50/60' : 'bg-white' }}">
                                                                {{-- 1ère Colonne : Heures --}}
                                                                <td class="p-2.5 font-black text-slate-700 bg-slate-50 text-center border-r border-slate-200 shrink-0">
                                                                    <span class="inline-block px-2 py-0.5 rounded-md bg-slate-200/80 text-slate-800 text-[10px] font-black">
                                                                        {{ $slotRange['label'] }}
                                                                    </span>
                                                                    @if ($isPause)
                                                                        <span class="block text-[9px] font-bold text-amber-600 mt-0.5">Pause</span>
                                                                    @endif
                                                                </td>

                                                                {{-- Colonnes Jours (Lundi à Samedi) --}}
                                                                @foreach ($daysList as $dayKey => $dayLabel)
                                                                    @php
                                                                        $cellSlots = $classSchedules->filter(function ($s) use ($dayKey, $slotRange) {
                                                                            return $s->day_of_week === $dayKey
                                                                                && substr($s->start_time, 0, 5) < $slotRange['end']
                                                                                && substr($s->end_time, 0, 5) > $slotRange['start'];
                                                                        });
                                                                    @endphp
                                                                    <td class="p-2 border-r border-slate-100 last:border-r-0 align-top min-w-[110px]">
                                                                        @if ($cellSlots->isNotEmpty())
                                                                            <div class="space-y-1.5">
                                                                                @foreach ($cellSlots as $slot)
                                                                                    <div class="p-2 rounded-xl bg-purple-50 border border-purple-200/80 shadow-2xs hover:bg-purple-100/70 transition space-y-1 group relative">
                                                                                        <a href="{{ route('schedules.show', $slot) }}"
                                                                                           class="block font-black text-[11px] text-purple-950 hover:text-purple-700 leading-tight">
                                                                                            {{ $slot->subject?->name }}
                                                                                        </a>
                                                                                        <div class="text-[10px] font-semibold text-slate-600 leading-tight">
                                                                                            👤 {{ $slot->teacher?->user?->name ? Str::limit($slot->teacher->user->name, 14) : '—' }}
                                                                                        </div>
                                                                                        <div class="flex items-center justify-between text-[9px] font-bold text-slate-400">
                                                                                            <span>⏱️ {{ substr($slot->start_time, 0, 5) }}-{{ substr($slot->end_time, 0, 5) }}</span>
                                                                                            @if ($slot->room_number)
                                                                                                <span>📍 {{ $slot->room_number }}</span>
                                                                                            @endif
                                                                                        </div>
                                                                                        {{-- Actions no-print au survol --}}
                                                                                        <div class="flex items-center justify-between pt-1 border-t border-purple-100/60 no-print opacity-0 group-hover:opacity-100 transition">
                                                                                            <a href="{{ route('schedules.show', $slot) }}"
                                                                                               class="text-slate-400 hover:text-purple-900 text-[10px] font-bold" title="Détails">
                                                                                                👁️
                                                                                            </a>
                                                                                            <div class="flex items-center gap-1">
                                                                                                <button type="button"
                                                                                                        @click="openDuplicateModal({{ $slot->id }}, '{{ addslashes($slot->subject->name) }}', '{{ $slot->day_of_week }}', '{{ substr($slot->start_time, 0, 5) }}', '{{ substr($slot->end_time, 0, 5) }}')"
                                                                                                        class="text-[10px] p-0.5 text-purple-600 hover:bg-purple-100 rounded" title="Dupliquer">
                                                                                                    📋
                                                                                                </button>
                                                                                                <a href="{{ route('schedules.edit', $slot) }}"
                                                                                                   class="text-[10px] p-0.5 text-amber-600 hover:bg-amber-100 rounded" title="Modifier">
                                                                                                    ✏️
                                                                                                </a>
                                                                                                <form method="POST" action="{{ route('schedules.destroy', $slot) }}" class="inline"
                                                                                                      data-confirm="Supprimer ce créneau ?">
                                                                                                    @csrf @method('DELETE')
                                                                                                    <button type="submit" class="text-[10px] p-0.5 text-rose-600 hover:bg-rose-100 rounded" title="Supprimer">
                                                                                                        🗑️
                                                                                                    </button>
                                                                                                </form>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @else
                                                                            <div class="h-full flex items-center justify-center py-2 text-slate-200 text-xs">
                                                                                —
                                                                            </div>
                                                                        @endif
                                                                    </td>
                                                                @endforeach
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="py-6 text-center text-slate-400 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                                            <p class="text-xs font-semibold">Aucun créneau programmé pour cette classe.</p>
                                            <a href="{{ route('schedules.create', ['class_id' => $cls->id]) }}"
                                               class="inline-flex items-center gap-1 mt-2 text-xs font-bold text-purple-900 hover:underline">
                                                ➕ Programmer le 1er cours
                                            </a>
                                        </div>
                                    @endif

                                </div>
                            @endforeach

                        </div>
                    @endforeach
                </div>

            {{-- ========================================================================= --}}
            {{-- CASE 2 : VUE DÉTAILLÉE D'UNE CLASSE SPÉCIFIQUE (GRILLE MATRICIELLE)      --}}
            {{-- ========================================================================= --}}
            @elseif ($viewType === 'class' && $selectedClass)

                {{-- Formulaire Intelligent d'Ajout Rapide --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-5 no-print"
                     x-data="{
                         subjectId: '{{ old('subject_id', $classAssignments->first()?->subject_id ?? $subjects->first()?->id ?? '') }}',
                         teacherId: '{{ old('teacher_id', $classAssignments->first()?->teacher_id ?? $teachers->first()?->id ?? '') }}',
                         assignedTeacherName: '',
                         isOfficial: false,
                         isManualOverride: false,
                         assignments: {{ Js::from($classAssignments->map(fn($a) => [
                             'id' => $a->id,
                             'subject_id' => (string) $a->subject_id,
                             'subject_name' => $a->subject?->name ?? '',
                             'teacher_id' => (string) $a->teacher_id,
                             'teacher_name' => $a->teacher?->user?->name ?? '',
                         ])) }},
                         init() {
                             this.syncTeacherFromSubject(false);
                         },
                         onSubjectChange() {
                             this.isManualOverride = false;
                             this.syncTeacherFromSubject(true);
                         },
                         syncTeacherFromSubject(forceSelect = true) {
                             const found = this.assignments.find(a => String(a.subject_id) === String(this.subjectId));
                             if (found) {
                                 this.isOfficial = true;
                                 this.assignedTeacherName = found.teacher_name;
                                 if (forceSelect || !this.isManualOverride) {
                                     this.teacherId = String(found.teacher_id);
                                 }
                             } else {
                                 this.isOfficial = false;
                                 this.assignedTeacherName = '';
                             }
                         },
                         onTeacherChange() {
                             const found = this.assignments.find(a => String(a.subject_id) === String(this.subjectId));
                             if (found && String(this.teacherId) !== String(found.teacher_id)) {
                                 this.isManualOverride = true;
                             } else {
                                 this.isManualOverride = false;
                             }
                         }
                     }">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">➕</span>
                            <h3 class="text-sm font-black text-purple-950">
                                Programmer un cours pour <span class="text-purple-700">{{ $selectedClass->level->cycle->name }} • {{ $selectedClass->level->name }} • {{ $selectedClass->name }}</span>
                            </h3>
                        </div>
                        <div class="flex items-center gap-3">
                            @if ($classAssignments->isNotEmpty())
                                <span class="px-2.5 py-1 rounded-lg text-[11px] font-black bg-purple-100 text-purple-900 border border-purple-200">
                                    🔗 {{ $classAssignments->count() }} matière(s) affectée(s) à cette classe
                                </span>
                            @endif

                            @if ($schedules->flatten()->isNotEmpty())
                                <form method="POST" action="{{ route('schedules.clear-class') }}"
                                      data-confirm="Supprimer TOUS les créneaux de {{ $selectedClass->name }} ?">
                                    @csrf
                                    <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                                    <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800 hover:underline">
                                        🗑️ Vider
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('schedules.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $selectedClassId }}" />

                        {{-- Ligne 1 : Horaires --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="day_of_week" class="block font-bold text-xs text-slate-700 mb-1">Jour *</label>
                                <select id="day_of_week" name="day_of_week" required
                                        class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-600 focus:ring-purple-600">
                                    @foreach ($daysList as $val => $label)
                                        <option value="{{ $val }}" {{ old('day_of_week') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="start_time" class="block font-bold text-xs text-slate-700 mb-1">Heure Début *</label>
                                <input type="time" id="start_time" name="start_time" required value="{{ old('start_time', '08:00') }}"
                                       class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-600 focus:ring-purple-600" />
                            </div>
                            <div>
                                <label for="end_time" class="block font-bold text-xs text-slate-700 mb-1">Heure Fin *</label>
                                <input type="time" id="end_time" name="end_time" required value="{{ old('end_time', '10:00') }}"
                                       class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-600 focus:ring-purple-600" />
                            </div>
                        </div>

                        {{-- Ligne 2 : Matière, Enseignant & Salle --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            {{-- Matière --}}
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="subject_id" class="block font-bold text-xs text-slate-700">Matière *</label>
                                    <span class="text-[10px] font-bold text-purple-700" x-show="isOfficial">🔗 Auto-liée</span>
                                </div>
                                <select id="subject_id" name="subject_id" x-model="subjectId" @change="onSubjectChange()" required
                                        class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-600 focus:ring-purple-600">
                                    <option value="">-- Sélectionner une matière --</option>
                                    @php
                                        $assignedSubjectIds = $classAssignments->pluck('subject_id')->toArray();
                                    @endphp
                                    @if ($classAssignments->isNotEmpty())
                                        <optgroup label="── Matières affectées à cette classe ──">
                                            @foreach ($classAssignments as $assign)
                                                <option value="{{ $assign->subject_id }}">
                                                    ⭐ {{ $assign->subject?->name }} (Prof. {{ $assign->teacher?->user?->name }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    <optgroup label="── {{ $classAssignments->isNotEmpty() ? 'Autres matières' : 'Toutes les matières' }} ──">
                                        @foreach ($subjects as $sbj)
                                            @if (!in_array($sbj->id, $assignedSubjectIds))
                                                <option value="{{ $sbj->id }}">
                                                    {{ $sbj->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>

                            {{-- Enseignant --}}
                            <div>
                                <label for="teacher_id" class="block font-bold text-xs text-slate-700 mb-1">Enseignant *</label>
                                <select id="teacher_id" name="teacher_id" x-model="teacherId" @change="onTeacherChange()" required
                                        class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-600 focus:ring-purple-600">
                                    <option value="">-- Sélectionner un enseignant --</option>
                                    @foreach ($teachers as $t)
                                        <option value="{{ $t->id }}">
                                            👨‍🏫 {{ $t->user->name }}{{ $t->speciality ? ' — ' . $t->speciality : '' }}
                                        </option>
                                    @endforeach
                                </select>

                                {{-- Indicateur dynamique de liaison intelligente --}}
                                <div class="mt-1.5 text-[11px] leading-tight" x-cloak>
                                    <template x-if="isOfficial && !isManualOverride">
                                        <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                            <span>✅</span>
                                            <span>Attitré : <strong x-text="assignedTeacherName"></strong> (auto-sélectionné)</span>
                                        </span>
                                    </template>
                                    <template x-if="isOfficial && isManualOverride">
                                        <span class="inline-flex items-center gap-1 font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                                            <span>⚠️</span>
                                            <span>Remplacement (Attitré : <strong x-text="assignedTeacherName"></strong>)</span>
                                        </span>
                                    </template>
                                    <template x-if="!isOfficial && subjectId">
                                        <span class="inline-flex items-center gap-1 text-slate-500 font-medium">
                                            <span>ℹ️</span>
                                            <span>Sélection manuelle de l'enseignant</span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            {{-- Salle --}}
                            <div>
                                <label for="room_number" class="block font-bold text-xs text-slate-700 mb-1">Salle (Optionnelle)</label>
                                <input type="text" id="room_number" name="room_number" placeholder="Ex: Salle 1, Bât A..."
                                       class="w-full text-xs font-bold rounded-xl border-slate-300 focus:border-purple-600 focus:ring-purple-600" />
                            </div>
                        </div>

                        <div class="flex justify-end pt-1">
                            <button type="submit"
                                    class="px-5 py-2.5 bg-gradient-to-r from-purple-950 to-indigo-950 hover:from-purple-900 hover:to-indigo-900 text-white rounded-xl text-xs font-black shadow-sm transition flex items-center gap-1.5">
                                <span>➕</span>
                                <span>Programmer le Cours</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- En-tête Grille Classe --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
                    <div>
                        <div class="text-xs font-black text-amber-600 uppercase tracking-widest">
                            Emploi du Temps Hebdomadaire
                        </div>
                        <h3 class="text-xl font-black text-purple-950 mt-0.5">
                            🏫 {{ $selectedClass->level->cycle->name }} • {{ $selectedClass->level->name }} • {{ $selectedClass->name }}
                        </h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-extrabold text-slate-600 bg-purple-50 px-3 py-1.5 rounded-xl border border-purple-100">
                            {{ $selectedClass->schedules->count() }} créneau(x)
                        </span>
                        <a href="{{ route('schedules.export-pdf', $selectedClass) }}"
                           target="_blank"
                           class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black rounded-xl shadow-xs transition flex items-center gap-1.5 no-print"
                           title="Exporter en PDF">
                            <span>📄</span>
                            <span>Exporter PDF</span>
                        </a>
                        <a href="{{ route('schedules.index', ['view_type' => 'class']) }}"
                           class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition no-print">
                            ← Toutes les classes
                        </a>
                    </div>
                </div>

                {{-- ── GRILLE MATRICIELLE CLASSE : LIGNES = HEURES, COLONNES = JOURS ── --}}
                @php
                    $classSlots = $selectedClass->schedules;
                @endphp

                <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden p-5 space-y-4">
                    <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-2xs">
                        <table class="w-full text-left border-collapse min-w-[750px]">
                            <thead>
                                <tr class="bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white text-xs font-black uppercase tracking-wider">
                                    <th class="p-3.5 text-center w-32 bg-purple-950/90 border-r border-purple-800">
                                        🕒 Horaires
                                    </th>
                                    @foreach ($daysList as $dayKey => $dayLabel)
                                        <th class="p-3.5 text-center border-r border-purple-800/60 last:border-r-0">
                                            {{ $dayLabel }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @foreach ($timeSlots as $slotRange)
                                    @php
                                        $isPause = $slotRange['is_pause'] ?? false;
                                    @endphp
                                    <tr class="hover:bg-purple-50/20 transition {{ $isPause ? 'bg-slate-50/60' : 'bg-white' }}">
                                        {{-- 1ère Colonne : Heures --}}
                                        <td class="p-3 font-black text-slate-700 bg-slate-50 text-center border-r border-slate-200 shrink-0">
                                            <span class="inline-block px-2 py-1 rounded-md bg-slate-200/80 text-slate-800 text-[11px] font-black">
                                                {{ $slotRange['label'] }}
                                            </span>
                                            @if ($isPause)
                                                <span class="block text-[9px] font-bold text-amber-600 mt-0.5">Pause Déjeuner</span>
                                            @endif
                                        </td>

                                        {{-- Colonnes Jours (Lundi à Samedi) --}}
                                        @foreach ($daysList as $dayKey => $dayLabel)
                                            @php
                                                $cellSlots = $classSlots->filter(function ($s) use ($dayKey, $slotRange) {
                                                    return $s->day_of_week === $dayKey
                                                        && substr($s->start_time, 0, 5) < $slotRange['end']
                                                        && substr($s->end_time, 0, 5) > $slotRange['start'];
                                                });
                                            @endphp
                                            <td class="p-2 border-r border-slate-100 last:border-r-0 align-top min-w-[120px]">
                                                @if ($cellSlots->isNotEmpty())
                                                    <div class="space-y-2">
                                                        @foreach ($cellSlots as $slot)
                                                            <div class="p-2.5 rounded-xl bg-purple-50 border border-purple-200/80 shadow-2xs hover:bg-white hover:border-purple-300 hover:shadow-md transition space-y-1.5 group relative">
                                                                <div class="flex items-center justify-between">
                                                                    <span class="px-1.5 py-0.5 rounded bg-purple-200 text-purple-950 font-black text-[9px]">
                                                                        {{ substr($slot->start_time, 0, 5) }}-{{ substr($slot->end_time, 0, 5) }}
                                                                    </span>
                                                                    @if ($slot->room_number)
                                                                        <span class="text-[9px] font-bold text-slate-400">📍 {{ $slot->room_number }}</span>
                                                                    @endif
                                                                </div>

                                                                <a href="{{ route('schedules.show', $slot) }}"
                                                                   class="block font-black text-xs text-purple-950 hover:text-purple-700 leading-tight">
                                                                    {{ $slot->subject?->name }}
                                                                </a>

                                                                <div class="text-[10px] font-semibold text-slate-600 leading-tight">
                                                                    👤 {{ $slot->teacher?->user?->name ?? '—' }}
                                                                </div>

                                                                {{-- Actions no-print au hover --}}
                                                                <div class="flex items-center justify-between pt-1 border-t border-purple-100/60 no-print opacity-0 group-hover:opacity-100 transition">
                                                                    <a href="{{ route('schedules.show', $slot) }}"
                                                                       class="text-slate-400 hover:text-purple-900 text-[10px] font-bold" title="Détails">
                                                                        👁️
                                                                    </a>
                                                                    <div class="flex items-center gap-1">
                                                                        <button type="button"
                                                                                @click="openDuplicateModal({{ $slot->id }}, '{{ addslashes($slot->subject->name) }}', '{{ $slot->day_of_week }}', '{{ substr($slot->start_time, 0, 5) }}', '{{ substr($slot->end_time, 0, 5) }}')"
                                                                                class="text-[10px] p-0.5 text-purple-600 hover:bg-purple-100 rounded" title="Dupliquer">
                                                                            📋
                                                                        </button>
                                                                        <a href="{{ route('schedules.edit', $slot) }}"
                                                                           class="text-[10px] p-0.5 text-amber-600 hover:bg-amber-100 rounded" title="Modifier">
                                                                            ✏️
                                                                        </a>
                                                                        <form method="POST" action="{{ route('schedules.destroy', $slot) }}" class="inline"
                                                                              data-confirm="Supprimer ce créneau ?">
                                                                            @csrf @method('DELETE')
                                                                            <button type="submit" class="text-[10px] p-0.5 text-rose-600 hover:bg-rose-100 rounded" title="Supprimer">
                                                                                🗑️
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="h-full flex items-center justify-center py-4 text-slate-200 text-xs">
                                                        —
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            {{-- ========================================================================= --}}
            {{-- CASE 3 : VUE D'ENSEMBLE DE TOUS LES ENSEIGNANTS                          --}}
            {{-- ========================================================================= --}}
            @elseif ($viewType === 'teacher' && ! $selectedTeacherId)
                <div class="space-y-8">
                    <div class="flex items-center justify-between border-b-2 border-purple-200 pb-2">
                        <h3 class="text-xl font-black text-purple-950 flex items-center gap-2">
                            <span>👨‍🏫</span>
                            <span>Planning de tous les Enseignants</span>
                        </h3>
                        <span class="text-xs font-bold text-indigo-700 bg-indigo-100 px-3 py-1 rounded-full">
                            {{ $teachers->count() }} enseignant(s) actif(s)
                        </span>
                    </div>

                    <div class="space-y-6">
                        @foreach ($teachers as $teacher)
                            @php
                                $teacherSlots = $teacher->schedules;
                                $hasSlots = $teacherSlots->isNotEmpty();
                            @endphp

                            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden space-y-4 p-5">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-black text-indigo-900 uppercase tracking-wider bg-indigo-100 px-2.5 py-1 rounded-lg">
                                            {{ $teacher->cycle->name ?? 'Général' }}
                                        </span>
                                        <h4 class="text-lg font-black text-purple-950">
                                            👨‍🏫 {{ $teacher->user->name }}
                                        </h4>
                                        @if ($teacher->speciality)
                                            <span class="text-xs text-slate-500 font-semibold">— {{ $teacher->speciality }}</span>
                                        @endif
                                        <span class="text-xs font-extrabold text-slate-500">
                                            • {{ $teacherSlots->count() }} cours
                                        </span>
                                    </div>
                                    <a href="{{ route('schedules.index', ['view_type' => 'teacher', 'teacher_id' => $teacher->id]) }}"
                                       class="px-3.5 py-1.5 bg-purple-950 hover:bg-purple-900 text-white font-black text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 no-print">
                                        <span>📅</span>
                                        <span>Vue individuelle complète</span>
                                    </a>
                                </div>

                                @if ($hasSlots)
                                    <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-2xs">
                                        <table class="w-full text-left border-collapse min-w-[700px]">
                                            <thead>
                                                <tr class="bg-gradient-to-r from-indigo-950 via-purple-950 to-indigo-900 text-white text-xs font-black uppercase tracking-wider">
                                                    <th class="p-3 text-center w-28 bg-indigo-950/90 border-r border-indigo-800">🕒 Horaires</th>
                                                    @foreach ($daysList as $dayKey => $dayLabel)
                                                        <th class="p-3 text-center border-r border-indigo-800/60 last:border-r-0">{{ $dayLabel }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 text-xs">
                                                @foreach ($timeSlots as $slotRange)
                                                    @php
                                                        $isPause = $slotRange['is_pause'] ?? false;
                                                        $matchingSlotsInRow = $teacherSlots->filter(function ($s) use ($slotRange) {
                                                            return substr($s->start_time, 0, 5) < $slotRange['end'] && substr($s->end_time, 0, 5) > $slotRange['start'];
                                                        });
                                                    @endphp
                                                    @if ($matchingSlotsInRow->isNotEmpty() || in_array($slotRange['start'], ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00']))
                                                        <tr class="hover:bg-indigo-50/20 transition {{ $isPause ? 'bg-slate-50/60' : 'bg-white' }}">
                                                            <td class="p-2.5 font-black text-slate-700 bg-slate-50 text-center border-r border-slate-200 shrink-0">
                                                                <span class="inline-block px-2 py-0.5 rounded-md bg-slate-200/80 text-slate-800 text-[10px] font-black">
                                                                    {{ $slotRange['label'] }}
                                                                </span>
                                                            </td>
                                                            @foreach ($daysList as $dayKey => $dayLabel)
                                                                @php
                                                                    $cellSlots = $teacherSlots->filter(function ($s) use ($dayKey, $slotRange) {
                                                                        return $s->day_of_week === $dayKey
                                                                            && substr($s->start_time, 0, 5) < $slotRange['end']
                                                                            && substr($s->end_time, 0, 5) > $slotRange['start'];
                                                                    });
                                                                @endphp
                                                                <td class="p-2 border-r border-slate-100 last:border-r-0 align-top min-w-[110px]">
                                                                    @if ($cellSlots->isNotEmpty())
                                                                        <div class="space-y-1.5">
                                                                            @foreach ($cellSlots as $slot)
                                                                                <div class="p-2 rounded-xl bg-indigo-50 border border-indigo-200/80 shadow-2xs space-y-1">
                                                                                    <div class="font-black text-[11px] text-purple-950 leading-tight">
                                                                                        {{ $slot->subject?->name }}
                                                                                    </div>
                                                                                    <div class="text-[10px] font-black text-indigo-900 leading-tight">
                                                                                        🏫 {{ $slot->schoolClass?->name }}
                                                                                    </div>
                                                                                    <div class="text-[9px] font-bold text-slate-400">
                                                                                        ⏱️ {{ substr($slot->start_time, 0, 5) }}-{{ substr($slot->end_time, 0, 5) }}
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @else
                                                                        <div class="h-full flex items-center justify-center py-2 text-slate-200 text-xs">—</div>
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="py-4 text-center text-slate-400 text-xs italic">
                                        Aucun cours programmé
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

            {{-- ========================================================================= --}}
            {{-- CASE 4 : VUE DÉTAILLÉE D'UN ENSEIGNANT SPÉCIFIQUE                        --}}
            {{-- ========================================================================= --}}
            @elseif ($viewType === 'teacher' && $selectedTeacher)
                @php
                    $teacherSlots = $selectedTeacher->schedules;
                @endphp

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
                    <div>
                        <div class="text-xs font-black text-indigo-600 uppercase tracking-widest">
                            Planning Hebdomadaire Enseignant
                        </div>
                        <h3 class="text-xl font-black text-purple-950 mt-0.5">
                            👨‍🏫 {{ $selectedTeacher->user->name }}
                            @if ($selectedTeacher->speciality)
                                <span class="text-xs font-semibold text-slate-400">— {{ $selectedTeacher->speciality }}</span>
                            @endif
                        </h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-extrabold text-slate-600 bg-indigo-50 px-3 py-1.5 rounded-xl border border-indigo-100">
                            {{ $teacherSlots->count() }} créneau(x)
                        </span>
                        <a href="{{ route('schedules.index', ['view_type' => 'teacher']) }}"
                           class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition no-print">
                            ← Tous les enseignants
                        </a>
                    </div>
                </div>

                {{-- ── GRILLE MATRICIELLE ENSEIGNANT : LIGNES = HEURES, COLONNES = JOURS ── --}}
                <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden p-5 space-y-4">
                    <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-2xs">
                        <table class="w-full text-left border-collapse min-w-[750px]">
                            <thead>
                                <tr class="bg-gradient-to-r from-indigo-950 via-purple-950 to-indigo-900 text-white text-xs font-black uppercase tracking-wider">
                                    <th class="p-3.5 text-center w-32 bg-indigo-950/90 border-r border-indigo-800">
                                        🕒 Horaires
                                    </th>
                                    @foreach ($daysList as $dayKey => $dayLabel)
                                        <th class="p-3.5 text-center border-r border-indigo-800/60 last:border-r-0">
                                            {{ $dayLabel }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @foreach ($timeSlots as $slotRange)
                                    @php
                                        $isPause = $slotRange['is_pause'] ?? false;
                                    @endphp
                                    <tr class="hover:bg-indigo-50/20 transition {{ $isPause ? 'bg-slate-50/60' : 'bg-white' }}">
                                        {{-- 1ère Colonne : Heures --}}
                                        <td class="p-3 font-black text-slate-700 bg-slate-50 text-center border-r border-slate-200 shrink-0">
                                            <span class="inline-block px-2 py-1 rounded-md bg-slate-200/80 text-slate-800 text-[11px] font-black">
                                                {{ $slotRange['label'] }}
                                            </span>
                                            @if ($isPause)
                                                <span class="block text-[9px] font-bold text-amber-600 mt-0.5">Pause</span>
                                            @endif
                                        </td>

                                        {{-- Colonnes Jours (Lundi à Samedi) --}}
                                        @foreach ($daysList as $dayKey => $dayLabel)
                                            @php
                                                $cellSlots = $teacherSlots->filter(function ($s) use ($dayKey, $slotRange) {
                                                    return $s->day_of_week === $dayKey
                                                        && substr($s->start_time, 0, 5) < $slotRange['end']
                                                        && substr($s->end_time, 0, 5) > $slotRange['start'];
                                                });
                                            @endphp
                                            <td class="p-2 border-r border-slate-100 last:border-r-0 align-top min-w-[120px]">
                                                @if ($cellSlots->isNotEmpty())
                                                    <div class="space-y-2">
                                                        @foreach ($cellSlots as $slot)
                                                            <div class="p-2.5 rounded-xl bg-indigo-50 border border-indigo-200/80 shadow-2xs hover:bg-white hover:border-indigo-300 hover:shadow-md transition space-y-1.5 group relative">
                                                                <div class="flex items-center justify-between">
                                                                    <span class="px-1.5 py-0.5 rounded bg-indigo-200 text-indigo-950 font-black text-[9px]">
                                                                        {{ substr($slot->start_time, 0, 5) }}-{{ substr($slot->end_time, 0, 5) }}
                                                                    </span>
                                                                    @if ($slot->room_number)
                                                                        <span class="text-[9px] font-bold text-slate-400">📍 {{ $slot->room_number }}</span>
                                                                    @endif
                                                                </div>

                                                                <a href="{{ route('schedules.show', $slot) }}"
                                                                   class="block font-black text-xs text-purple-950 hover:text-purple-700 leading-tight">
                                                                    {{ $slot->subject?->name }}
                                                                </a>

                                                                <div class="text-[10px] font-black text-indigo-900 leading-tight">
                                                                    🏫 {{ $slot->schoolClass?->name }}
                                                                </div>

                                                                <div class="flex items-center justify-between pt-1 border-t border-indigo-100/60 no-print opacity-0 group-hover:opacity-100 transition">
                                                                    <a href="{{ route('schedules.show', $slot) }}"
                                                                       class="text-slate-400 hover:text-purple-900 text-[10px] font-bold" title="Détails">
                                                                        👁️
                                                                    </a>
                                                                    <div class="flex items-center gap-1">
                                                                        <button type="button"
                                                                                @click="openDuplicateModal({{ $slot->id }}, '{{ addslashes($slot->subject->name) }}', '{{ $slot->day_of_week }}', '{{ substr($slot->start_time, 0, 5) }}', '{{ substr($slot->end_time, 0, 5) }}')"
                                                                                class="text-[10px] p-0.5 text-purple-600 hover:bg-purple-100 rounded" title="Dupliquer">
                                                                            📋
                                                                        </button>
                                                                        <a href="{{ route('schedules.edit', $slot) }}"
                                                                           class="text-[10px] p-0.5 text-amber-600 hover:bg-amber-100 rounded" title="Modifier">
                                                                            ✏️
                                                                        </a>
                                                                        <form method="POST" action="{{ route('schedules.destroy', $slot) }}" class="inline"
                                                                              data-confirm="Supprimer ce créneau ?">
                                                                            @csrf @method('DELETE')
                                                                            <button type="submit" class="text-[10px] p-0.5 text-rose-600 hover:bg-rose-100 rounded" title="Supprimer">
                                                                                🗑️
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="h-full flex items-center justify-center py-4 text-slate-200 text-xs">—</div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            @endif

        </div>

        {{-- ── MODAL DUPLICATION ─────────────────────────────────────────── --}}
        <div x-show="duplicateModal" x-cloak style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div @click.away="duplicateModal = false"
                 class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-100 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-black text-purple-950 flex items-center gap-2">
                        📋 Dupliquer le Créneau
                    </h3>
                    <button type="button" @click="duplicateModal = false"
                            class="text-slate-400 hover:text-slate-600 font-black text-lg">✕</button>
                </div>

                <p class="text-xs text-slate-600">
                    Dupliquer <strong class="text-purple-950" x-text="duplicateSubjectName"></strong> vers un autre créneau.
                </p>

                <form :action="'/schedules/' + duplicateScheduleId + '/duplicate'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Jour Cible *</label>
                        <select name="target_day" x-model="duplicateTargetDay" required
                                class="w-full text-xs font-bold border-slate-300 rounded-xl">
                            @foreach ($daysList as $v => $l)
                                <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Début *</label>
                            <input type="time" name="start_time" x-model="duplicateStartTime" required
                                   class="w-full text-xs font-bold border-slate-300 rounded-xl" />
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Fin *</label>
                            <input type="time" name="end_time" x-model="duplicateEndTime" required
                                   class="w-full text-xs font-bold border-slate-300 rounded-xl" />
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="duplicateModal = false"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">
                            Annuler
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-purple-900 hover:bg-purple-950 text-white font-black text-xs rounded-xl shadow-md">
                            Dupliquer
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <style>
        @media print {
            .no-print, header, nav, aside { display: none !important; }
            body { background: white !important; }
            .py-6 { padding: 0 !important; }
            .shadow-sm, .shadow-2xs, .shadow-xs { box-shadow: none !important; }
            table { width: 100% !important; }
        }
    </style>
</x-app-layout>
