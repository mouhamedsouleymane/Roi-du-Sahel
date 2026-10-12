<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                    <span>🔗</span> Détail de l'Affectation
                </h2>
                <p class="text-xs font-bold text-slate-500 mt-1">
                    Année scolaire :
                    <span class="text-purple-950 font-extrabold">{{ $assignment->academicYear->name }}</span>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('assignments.edit', $assignment) }}"
                    class="px-4 py-2 text-xs font-black bg-amber-400 hover:bg-amber-500 text-amber-950 rounded-xl transition shadow-sm flex items-center gap-1.5">
                    <span>✏️</span> Modifier
                </a>
                <form method="POST" action="{{ route('assignments.destroy', $assignment) }}" class="inline"
                    data-confirm="Voulez-vous vraiment supprimer cette affectation ?">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2 text-xs font-black bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-xl transition shadow-sm flex items-center gap-1.5">
                        <span>🗑️</span> Supprimer
                    </button>
                </form>
                <a href="{{ route('assignments.index') }}"
                    class="text-xs font-black text-purple-900 hover:underline flex items-center gap-1">
                    ← Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto space-y-6 animate-fade-in-up">

            {{-- Hero Banner --}}
            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                <div class="p-8 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white relative overflow-hidden">
                    {{-- Decorative circles --}}
                    <div class="absolute -top-8 -right-8 w-40 h-40 bg-white/5 rounded-full"></div>
                    <div class="absolute -bottom-10 right-20 w-24 h-24 bg-amber-400/10 rounded-full"></div>

                    <div class="relative z-10">
                        <div class="text-xs font-black text-amber-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                            <span class="w-8 h-0.5 bg-amber-400 inline-block"></span>
                            Affectation de Cours
                        </div>
                        <h1 class="text-3xl font-black text-white leading-tight">
                            {{ $assignment->subject->name }}
                        </h1>
                        <p class="text-purple-300 text-base mt-1 font-bold">
                            {{ $assignment->schoolClass->name }}
                            <span class="text-purple-400 font-normal mx-2">—</span>
                            {{ $assignment->schoolClass->level->name }}
                        </p>
                        <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 bg-white/10 backdrop-blur-sm rounded-full text-xs font-bold text-purple-200 border border-white/20">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                            {{ $assignment->schoolClass->level->cycle->name }}
                        </div>
                    </div>
                </div>

                {{-- Info Grid --}}
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">

                    {{-- Enseignant --}}
                    <div class="space-y-5">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                            <span class="text-lg">👨‍🏫</span>
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Enseignant</span>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center text-2xl shadow-inner">
                                👤
                            </div>
                            <div>
                                <div class="text-lg font-black text-slate-900">
                                    {{ $assignment->teacher->user->name }}
                                </div>
                                <div class="text-xs font-bold text-slate-500 mt-0.5">
                                    {{ $assignment->teacher->speciality }}
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-slate-50 rounded-2xl p-3.5 border border-slate-100">
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Matricule</div>
                                <div class="text-sm font-black font-mono text-indigo-700">{{ $assignment->teacher->matricule }}</div>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-3.5 border border-slate-100">
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Cycle</div>
                                <div class="text-sm font-black text-purple-900">{{ $assignment->teacher->cycle?->name ?? '—' }}</div>
                            </div>
                        </div>

                        @if($assignment->teacher->isRestrictedToOneClass())
                            <div class="flex items-center gap-2 text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
                                <span>⚠️</span>
                                Enseignant polyvalent — limité à <strong>1 classe</strong> par année
                            </div>
                        @else
                            <div class="flex items-center gap-2 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl px-3 py-2">
                                <span>✅</span>
                                Peut enseigner dans <strong>plusieurs classes</strong>
                            </div>
                        @endif
                    </div>

                    {{-- Classe & Matière --}}
                    <div class="space-y-5">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                            <span class="text-lg">🏫</span>
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Classe & Matière</span>
                        </div>

                        <div class="bg-purple-50 border border-purple-100 rounded-2xl p-4 space-y-3">
                            <div>
                                <div class="text-[10px] font-black text-purple-400 uppercase tracking-widest mb-1">Classe</div>
                                <div class="text-base font-black text-purple-950">{{ $assignment->schoolClass->name }}</div>
                                <div class="text-xs font-bold text-purple-700 mt-0.5">
                                    {{ $assignment->schoolClass->level->name }} · {{ $assignment->schoolClass->level->cycle->name }}
                                </div>
                            </div>
                        </div>

                        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 space-y-3">
                            <div>
                                <div class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Matière</div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="px-2.5 py-1 text-xs font-black bg-indigo-100 text-indigo-800 rounded-lg">
                                        {{ $assignment->subject->code }}
                                    </span>
                                    <span class="text-base font-black text-indigo-950">{{ $assignment->subject->name }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-2xl p-3.5 border border-slate-100">
                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Créé le</div>
                            <div class="text-sm font-bold text-slate-700">
                                {{ $assignment->created_at->format('d/m/Y à H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
