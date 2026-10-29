<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                🏫 Fiche Classe — {{ $schoolClass->name }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('schedules.index', ['class_id' => $schoolClass->id]) }}"
                    class="px-4 py-2 text-xs font-black bg-indigo-900 hover:bg-indigo-800 text-white rounded-xl transition shadow-sm flex items-center gap-1.5">
                    📅 Emploi du Temps
                </a>
                <a href="{{ route('classes.edit', $schoolClass) }}"
                    class="px-4 py-2 text-xs font-black bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition shadow-sm flex items-center gap-1">
                    ✏️ Modifier
                </a>
                <a href="{{ route('classes.index', ['academic_year_id' => $schoolClass->academic_year_id]) }}"
                    class="text-xs font-black text-purple-900 hover:underline px-2 py-1">
                    ← Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            @php
                $activeEnrollments = $schoolClass->enrollments->whereIn('status', ['EN_ATTENTE', 'VALIDE']);
                $totalStudents = $activeEnrollments->count();
                $boysCount = $activeEnrollments->filter(fn($e) => $e->student?->gender === 'M')->count();
                $girlsCount = $activeEnrollments->filter(fn($e) => $e->student?->gender === 'F')->count();
                $capacity = $schoolClass->capacity ?? 40;
                $boysPct = $totalStudents > 0 ? round(($boysCount / $totalStudents) * 100) : 0;
                $girlsPct = $totalStudents > 0 ? round(($girlsCount / $totalStudents) * 100) : 0;
            @endphp

            <!-- En-tête Classe -->
            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                <div class="p-8 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <div class="text-xs font-black text-amber-400 uppercase tracking-widest">
                                {{ $schoolClass->level->cycle->name }} › {{ $schoolClass->level->name }}
                            </div>
                            <h1 class="text-3xl font-black text-white mt-1">{{ $schoolClass->name }}</h1>
                            <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-purple-200">
                                @if ($schoolClass->room_number)
                                    <span>📍 Salle {{ $schoolClass->room_number }}</span>
                                @endif
                                <span>📅 Année : {{ $schoolClass->academicYear->name }}</span>
                                @if ($schoolClass->mainTeacher)
                                    <span>👤 Prof Principal : {{ $schoolClass->mainTeacher->name }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-center">
                            {{-- Effectif Total --}}
                            <div
                                class="bg-white/10 backdrop-blur-xs rounded-2xl px-5 py-3.5 border border-white/10 flex flex-col justify-between">
                                <div class="text-[11px] text-purple-300 font-bold uppercase tracking-wider">Effectif
                                </div>
                                <div class="text-2xl font-black text-white mt-0.5">
                                    {{ $totalStudents }}
                                    <span class="text-xs font-semibold text-purple-300">/ {{ $capacity }}</span>
                                </div>
                                <div class="w-full bg-white/20 rounded-full h-1.5 mt-2">
                                    <div class="bg-amber-400 h-1.5 rounded-full transition-all duration-500"
                                        style="width: {{ min(100, $capacity > 0 ? round(($totalStudents / $capacity) * 100) : 0) }}%">
                                    </div>
                                </div>
                            </div>

                            {{-- Effectif par Genre --}}
                            <div
                                class="bg-white/10 backdrop-blur-xs rounded-2xl px-5 py-3.5 border border-white/10 flex flex-col justify-between">
                                <div class="text-[11px] text-purple-300 font-bold uppercase tracking-wider">Par Genre
                                </div>
                                <div class="flex items-center justify-center gap-3 mt-0.5">
                                    <div class="text-center"
                                        title="Garçons : {{ $boysCount }} ({{ $boysPct }}%)">
                                        <div
                                            class="text-lg font-black text-sky-300 flex items-center justify-center gap-1">
                                            <span>👦</span> {{ $boysCount }}
                                        </div>
                                        <div class="text-[10px] font-bold text-sky-200">Garçons</div>
                                    </div>
                                    <div class="h-6 w-px bg-white/20"></div>
                                    <div class="text-center"
                                        title="Filles : {{ $girlsCount }} ({{ $girlsPct }}%)">
                                        <div
                                            class="text-lg font-black text-pink-300 flex items-center justify-center gap-1">
                                            <span>👧</span> {{ $girlsCount }}
                                        </div>
                                        <div class="text-[10px] font-bold text-pink-200">Filles</div>
                                    </div>
                                </div>
                                <div class="w-full bg-white/20 rounded-full h-1.5 mt-2 flex overflow-hidden">
                                    <div class="bg-sky-400 h-1.5" style="width: {{ $boysPct }}%"
                                        title="Garçons: {{ $boysPct }}%"></div>
                                    <div class="bg-pink-400 h-1.5" style="width: {{ $girlsPct }}%"
                                        title="Filles: {{ $girlsPct }}%"></div>
                                </div>
                            </div>

                            {{-- Matières --}}
                            <div
                                class="bg-white/10 backdrop-blur-xs rounded-2xl px-5 py-3.5 border border-white/10 flex flex-col justify-between col-span-2 sm:col-span-1">
                                <div class="text-[11px] text-purple-300 font-bold uppercase tracking-wider">Matières
                                </div>
                                <div class="text-2xl font-black text-white mt-0.5">
                                    {{ $schoolClass->assignments->unique('subject_id')->count() }}
                                </div>
                                <div class="text-[10px] text-purple-300 font-bold mt-2">
                                    {{ $schoolClass->assignments->pluck('teacher_id')->unique()->count() }}
                                    enseignant(s)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Liste des Élèves -->
                <div class="bg-white shadow-xl rounded-3xl p-6 border border-slate-200/80">
                    <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">🎓 Élèves Inscrits
                            </h3>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-purple-100 text-purple-900">
                                {{ $totalStudents }} élève(s)
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 text-xs font-black">
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-sky-50 text-sky-800 border border-sky-200 shadow-2xs">
                                <span>👦</span> {{ $boysCount }} Garçons
                            </span>
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-pink-50 text-pink-800 border border-pink-200 shadow-2xs">
                                <span>👧</span> {{ $girlsCount }} Filles
                            </span>
                        </div>
                    </div>
                    <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                        @forelse ($activeEnrollments->sortBy(fn($e) => $e->student->last_name) as $enrollment)
                            @php
                                $isGirl = $enrollment->student?->gender === 'F';
                            @endphp
                            <a href="{{ route('students.show', $enrollment->student) }}"
                                class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:bg-purple-50/50 hover:border-purple-200 transition">
                                <div
                                    class="w-8 h-8 rounded-full {{ $isGirl ? 'bg-gradient-to-tr from-pink-500 to-rose-700' : 'bg-gradient-to-tr from-sky-600 to-indigo-900' }} flex items-center justify-center text-white font-black text-xs shrink-0 shadow-2xs">
                                    {{ mb_substr($enrollment->student->first_name, 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="font-black text-slate-900 text-xs truncate">
                                            {{ $enrollment->student->last_name }}
                                            {{ $enrollment->student->first_name }}
                                        </div>
                                        <span
                                            class="text-[10px] font-black px-1.5 py-0.5 rounded-md {{ $isGirl ? 'bg-pink-50 text-pink-700 border border-pink-200' : 'bg-sky-50 text-sky-700 border border-sky-200' }} shrink-0">
                                            {{ $isGirl ? '👧 Fille' : '👦 Garçon' }}
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 font-semibold">
                                        {{ $enrollment->student->matricule }}
                                    </div>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-slate-400 italic text-center py-4">Aucun élève inscrit dans cette
                                classe.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Matières & Enseignants + Raccourci Emploi du temps -->
                <div
                    class="bg-white shadow-xl rounded-3xl p-6 border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">📚 Matières &
                                Enseignants</h3>
                            <a href="{{ route('schedules.index', ['class_id' => $schoolClass->id]) }}"
                                class="text-xs font-black text-indigo-700 hover:underline">
                                📅 Voir le planning →
                            </a>
                        </div>
                        <div class="space-y-2 max-h-80 overflow-y-auto">
                            @forelse ($schoolClass->assignments->unique('subject_id') as $assignment)
                                <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 bg-slate-50">
                                    <span
                                        class="px-2 py-1 text-[10px] font-black bg-purple-100 text-purple-900 rounded-lg">{{ $assignment->subject->code }}</span>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-black text-slate-900 text-xs truncate">
                                            {{ $assignment->subject->name }}</div>
                                        <div class="text-[10px] text-slate-500 font-semibold">
                                            {{ $assignment->teacher->user->name }}</div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-400 italic text-center py-4">Aucune matière affectée.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="pt-4 mt-4 border-t border-slate-100 flex flex-col sm:flex-row gap-2">
                        <a href="{{ route('assignments.create', ['class_id' => $schoolClass->id]) }}"
                            class="flex-1 py-2.5 px-4 bg-emerald-100 hover:bg-emerald-200 text-emerald-950 border border-emerald-300 text-xs font-black rounded-xl transition flex items-center justify-center gap-2 shadow-2xs">
                            <span>⚡</span> Gérer les affectations
                        </a>
                        <a href="{{ route('schedules.index', ['class_id' => $schoolClass->id]) }}"
                            class="flex-1 py-2.5 px-4 bg-purple-50 hover:bg-purple-100 text-purple-950 text-xs font-black rounded-xl transition flex items-center justify-center gap-2">
                            <span>📅</span> Gérer l'emploi du temps
                        </a>
                    </div>
                </div>

            </div>

            @php
                $daysList = [
                    'LUNDI' => 'Lundi',
                    'MARDI' => 'Mardi',
                    'MERCREDI' => 'Mercredi',
                    'JEUDI' => 'Jeudi',
                    'VENDREDI' => 'Vendredi',
                    'SAMEDI' => 'Samedi',
                ];
                $schedulesCount = $schoolClass->schedules->count();
            @endphp

            <!-- Section Emploi du Temps Hebdomadaire -->
            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                <div
                    class="p-6 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center gap-3">
                        <span
                            class="w-10 h-10 rounded-2xl bg-amber-400 text-purple-950 font-black text-lg flex items-center justify-center shadow-xs shrink-0">
                            📅
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-black text-white">Emploi du Temps Hebdomadaire</h3>
                                <span
                                    class="text-xs font-black px-2.5 py-0.5 rounded-full bg-white/10 text-amber-300 border border-white/10">
                                    {{ $schedulesCount }} créneau{{ $schedulesCount > 1 ? 'x' : '' }}
                                </span>
                            </div>
                            <p class="text-xs text-purple-200 font-medium mt-0.5">
                                Planning des cours pour la classe <strong
                                    class="text-white">{{ $schoolClass->name }}</strong>
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('schedules.create', ['class_id' => $schoolClass->id]) }}"
                            class="px-3 py-1.5 text-xs font-black bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl transition shadow-xs flex items-center gap-1.5">
                            <span>➕</span> Ajouter un créneau
                        </a>
                        <a href="{{ route('schedules.export-pdf', $schoolClass) }}"
                            class="px-3 py-1.5 text-xs font-black bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-xl transition flex items-center gap-1.5">
                            <span>🖨️</span> PDF
                        </a>
                        <a href="{{ route('schedules.index', ['class_id' => $schoolClass->id]) }}"
                            class="px-3 py-1.5 text-xs font-black bg-purple-800 hover:bg-purple-700 text-amber-300 border border-purple-700 rounded-xl transition flex items-center gap-1.5">
                            <span>⚙️</span> Vue Grille
                        </a>
                    </div>
                </div>

                <div class="p-6">
                    @if ($schedulesCount === 0)
                        <div class="py-12 text-center space-y-3">
                            <div
                                class="w-16 h-16 bg-purple-50 text-purple-900 rounded-full flex items-center justify-center text-3xl mx-auto shadow-inner">
                                📅
                            </div>
                            <h4 class="text-base font-black text-slate-800">Aucun créneau programmé</h4>
                            <p class="text-xs text-slate-500 max-w-md mx-auto font-medium">
                                L'emploi du temps de la classe {{ $schoolClass->name }} n'a pas encore été configuré.
                            </p>
                            <div class="pt-2">
                                <a href="{{ route('schedules.create', ['class_id' => $schoolClass->id]) }}"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-purple-900 hover:bg-purple-800 text-amber-300 rounded-xl text-xs font-black transition shadow-sm">
                                    <span>➕</span> Programmer le premier cours
                                </a>
                            </div>
                        </div>
                    @else
                        {{-- Grille des 6 Jours (Lundi à Samedi) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3.5">
                            @foreach ($daysList as $dayKey => $dayLabel)
                                @php
                                    $daySlots = $schoolClass->schedules
                                        ->where('day_of_week', $dayKey)
                                        ->sortBy('start_time');
                                @endphp
                                <div
                                    class="bg-slate-50/80 rounded-2xl border border-slate-200/70 p-3.5 flex flex-col justify-between">
                                    {{-- En-tête du jour --}}
                                    <div>
                                        <div
                                            class="flex items-center justify-between pb-2 mb-2.5 border-b border-slate-200">
                                            <span class="text-xs font-black uppercase tracking-wider text-slate-800">
                                                {{ $dayLabel }}
                                            </span>
                                            <span
                                                class="text-[10px] font-extrabold px-1.5 py-0.5 rounded-full {{ $daySlots->isNotEmpty() ? 'bg-purple-100 text-purple-900' : 'bg-slate-200 text-slate-500' }}">
                                                {{ $daySlots->count() }}
                                            </span>
                                        </div>

                                        {{-- Créneaux du jour --}}
                                        @if ($daySlots->isNotEmpty())
                                            <div class="space-y-2">
                                                @foreach ($daySlots as $slot)
                                                    <div
                                                        class="p-2.5 rounded-xl bg-white border border-purple-100 shadow-2xs hover:border-purple-300 hover:bg-purple-50/30 transition group">
                                                        <div class="flex items-center justify-between gap-1 mb-1">
                                                            <span
                                                                class="text-[10px] font-black px-1.5 py-0.5 rounded-md bg-purple-100 text-purple-900 shrink-0">
                                                                {{ substr($slot->start_time, 0, 5) }} -
                                                                {{ substr($slot->end_time, 0, 5) }}
                                                            </span>
                                                            @if ($slot->room_number)
                                                                <span
                                                                    class="text-[9px] font-bold text-slate-400 truncate">
                                                                    📍 {{ $slot->room_number }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="text-xs font-black text-slate-900 leading-tight">
                                                            {{ $slot->subject?->name }}
                                                        </div>
                                                        <div
                                                            class="text-[10px] font-semibold text-slate-500 truncate mt-0.5">
                                                            👤 {{ $slot->teacher?->user?->name ?? 'Non assigné' }}
                                                        </div>
                                                        <div
                                                            class="flex items-center justify-end gap-1.5 mt-2 pt-1.5 border-t border-slate-100 opacity-60 group-hover:opacity-100 transition">
                                                            <a href="{{ route('schedules.show', $slot) }}"
                                                                class="p-1 text-slate-500 hover:text-purple-900 rounded hover:bg-purple-50 text-[10px] font-bold"
                                                                title="Détails">
                                                                👁️
                                                            </a>
                                                            <a href="{{ route('schedules.edit', $slot) }}"
                                                                class="p-1 text-amber-600 hover:text-amber-800 rounded hover:bg-amber-50 text-[10px] font-bold"
                                                                title="Modifier">
                                                                ✏️
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="py-6 text-center text-slate-400 text-xs italic">
                                                Pas de cours
                                            </div>
                                        @endif
                                    </div>

                                    <div class="pt-2 mt-2 border-t border-slate-200/50">
                                        <a href="{{ route('schedules.create', ['class_id' => $schoolClass->id, 'day_of_week' => $dayKey]) }}"
                                            class="w-full py-1 text-[10px] font-black text-purple-800 hover:text-purple-950 hover:bg-purple-100/60 rounded-lg transition text-center block">
                                            + Ajouter
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
