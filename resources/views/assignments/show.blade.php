<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                📋 Détail Affectation
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('assignments.edit', $assignment) }}" class="px-4 py-2 text-xs font-black bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition shadow-sm">
                    ✏️ Modifier
                </a>
                <a href="{{ route('assignments.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                    ← Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                <div class="p-8 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white">
                    <div class="text-xs font-black text-amber-400 uppercase tracking-widest mb-2">Affectation de Cours</div>
                    <h1 class="text-2xl font-black text-white">
                        {{ $assignment->subject->name }}
                        <span class="text-purple-300 text-lg">— {{ $assignment->schoolClass->name }}</span>
                    </h1>
                    <p class="text-purple-300 text-sm mt-1">
                        {{ $assignment->schoolClass->level->cycle->name }}
                    </p>
                </div>

                <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="col-span-1 space-y-4">
                        <div>
                            <div class="text-xs font-black text-slate-500 uppercase tracking-widest">Enseignant</div>
                            <div class="text-base font-black text-slate-900 mt-1">{{ $assignment->teacher->user->name }}</div>
                            <div class="text-xs text-slate-500">{{ $assignment->teacher->matricule }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-black text-slate-500 uppercase tracking-widest">Spécialité</div>
                            <div class="text-sm font-bold text-slate-800 mt-1">{{ $assignment->teacher->speciality }}</div>
                        </div>
                    </div>
                    <div class="col-span-2 space-y-4">
                        <div>
                            <div class="text-xs font-black text-slate-500 uppercase tracking-widest">Classe</div>
                            <div class="text-base font-black text-slate-900 mt-1">{{ $assignment->schoolClass->name }}</div>
                            <div class="text-xs text-slate-500">{{ $assignment->schoolClass->level->name }} — {{ $assignment->schoolClass->level->cycle->name }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-black text-slate-500 uppercase tracking-widest">Matière</div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="px-3 py-1 text-xs font-black bg-purple-100 text-purple-900 rounded-full">{{ $assignment->subject->code }}</span>
                                <span class="text-base font-black text-slate-900">{{ $assignment->subject->name }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="text-xs font-black text-slate-500 uppercase tracking-widest">Créé le</div>
                            <div class="text-sm font-bold text-slate-700 mt-1">{{ $assignment->created_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
