<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                🏫 Fiche Classe — {{ $schoolClass->name }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('classes.edit', $schoolClass) }}" class="px-4 py-2 text-xs font-black bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition shadow-sm">
                    ✏️ Modifier
                </a>
                <a href="{{ route('classes.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                    ← Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <!-- En-tête Classe -->
            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                <div class="p-8 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <div class="text-xs font-black text-amber-400 uppercase tracking-widest">
                                {{ $schoolClass->level->cycle->name }} › {{ $schoolClass->level->name }}
                            </div>
                            <h1 class="text-3xl font-black text-white mt-1">{{ $schoolClass->name }}</h1>
                            @if ($schoolClass->room_number)
                                <p class="text-purple-300 text-sm mt-0.5">📍 Salle {{ $schoolClass->room_number }}</p>
                            @endif
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div class="bg-white/10 rounded-2xl px-6 py-4">
                                <div class="text-xs text-purple-300 font-bold">Effectif</div>
                                <div class="text-2xl font-black text-white mt-1">
                                    {{ $schoolClass->enrollments->whereIn('status', ['VALIDE', 'INSCRIT'])->count() }}
                                </div>
                                <div class="text-xs text-purple-400">/ {{ $schoolClass->capacity ?? 40 }}</div>
                            </div>
                            <div class="bg-white/10 rounded-2xl px-6 py-4">
                                <div class="text-xs text-purple-300 font-bold">Matières</div>
                                <div class="text-2xl font-black text-white mt-1">
                                    {{ $schoolClass->assignments->unique('subject_id')->count() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Liste des Élèves -->
                <div class="bg-white shadow-xl rounded-3xl p-6 border border-slate-200/80">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-4">🎓 Élèves Inscrits</h3>
                    <div class="space-y-2 max-h-80 overflow-y-auto">
                        @forelse ($schoolClass->enrollments->whereIn('status', ['VALIDE', 'INSCRIT'])->sortBy(fn($e) => $e->student->last_name) as $enrollment)
                            <a href="{{ route('students.show', $enrollment->student) }}"
                               class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:bg-purple-50/50 hover:border-purple-200 transition">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-purple-600 to-indigo-900 flex items-center justify-center text-white font-black text-xs flex-shrink-0">
                                    {{ mb_substr($enrollment->student->first_name, 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-black text-slate-900 text-xs truncate">{{ $enrollment->student->last_name }} {{ $enrollment->student->first_name }}</div>
                                    <div class="text-[10px] text-slate-500 font-semibold">{{ $enrollment->student->matricule }}</div>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-slate-400 italic text-center py-4">Aucun élève inscrit dans cette classe.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Matières & Enseignants -->
                <div class="bg-white shadow-xl rounded-3xl p-6 border border-slate-200/80">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-4">📚 Matières & Enseignants</h3>
                    <div class="space-y-2">
                        @forelse ($schoolClass->assignments->unique('subject_id') as $assignment)
                            <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 bg-slate-50">
                                <span class="px-2 py-1 text-[10px] font-black bg-purple-100 text-purple-900 rounded-lg">{{ $assignment->subject->code }}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="font-black text-slate-900 text-xs truncate">{{ $assignment->subject->name }}</div>
                                    <div class="text-[10px] text-slate-500 font-semibold">{{ $assignment->teacher->user->name }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400 italic text-center py-4">Aucune matière affectée.</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
