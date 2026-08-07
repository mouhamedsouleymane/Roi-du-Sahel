<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                📋 Détail Inscription
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('enrollments.edit', $enrollment) }}" class="px-4 py-2 text-xs font-black bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition shadow-sm">
                    ✏️ Modifier
                </a>
                <a href="{{ route('enrollments.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                    ← Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                <div class="p-8 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white">
                    <div class="text-xs font-black text-amber-400 uppercase tracking-widest">Inscription N° {{ str_pad($enrollment->id, 5, '0', STR_PAD_LEFT) }}</div>
                    <h1 class="text-2xl font-black text-white mt-1">
                        {{ $enrollment->student->last_name }} {{ $enrollment->student->first_name }}
                    </h1>
                    <p class="text-purple-200 text-sm mt-0.5">Matricule : {{ $enrollment->student->matricule }}</p>
                </div>

                <div class="p-8 grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div>
                        <div class="text-xs font-black text-slate-500 uppercase tracking-widest">Classe</div>
                        <div class="text-base font-black text-slate-900 mt-1">{{ $enrollment->schoolClass->name }}</div>
                        <div class="text-xs text-slate-500">{{ $enrollment->schoolClass->level->cycle->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-black text-slate-500 uppercase tracking-widest">Année</div>
                        <div class="text-base font-black text-slate-900 mt-1">{{ $enrollment->academicYear->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-black text-slate-500 uppercase tracking-widest">Type</div>
                        <div class="mt-1">
                            <span class="px-2.5 py-1 text-xs font-black bg-blue-100 text-blue-800 rounded-full">{{ $enrollment->type }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-black text-slate-500 uppercase tracking-widest">Statut</div>
                        <div class="mt-1">
                            <span class="px-2.5 py-1 text-xs font-black rounded-full
                                {{ $enrollment->status === 'VALIDE' ? 'bg-emerald-100 text-emerald-800' : ($enrollment->status === 'EN_ATTENTE' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                {{ $enrollment->status }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-black text-slate-500 uppercase tracking-widest">Date d'Inscription</div>
                        <div class="text-sm font-bold text-slate-900 mt-1">{{ $enrollment->enrollment_date->format('d/m/Y') }}</div>
                    </div>
                    @if ($enrollment->notes)
                        <div class="col-span-2 md:col-span-3">
                            <div class="text-xs font-black text-slate-500 uppercase tracking-widest">Remarques</div>
                            <div class="text-sm text-slate-700 mt-1">{{ $enrollment->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
