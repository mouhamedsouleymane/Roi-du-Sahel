<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                👨‍👩‍👧 Fiche Tuteur — {{ $guardian->first_name }} {{ $guardian->last_name }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('guardians.edit', $guardian) }}" class="px-4 py-2 text-xs font-black bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition shadow-sm">
                    ✏️ Modifier
                </a>
                <a href="{{ route('guardians.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                    ← Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <!-- En-tête Identité -->
            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                <div class="p-8 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-400 to-purple-700 flex items-center justify-center text-3xl shadow-lg">
                            👨‍👩‍👧
                        </div>
                        <div>
                            <div class="text-xs font-black text-amber-400 uppercase tracking-widest">{{ $guardian->relationship }}</div>
                            <h1 class="text-2xl font-black text-white mt-1">{{ $guardian->first_name }} {{ $guardian->last_name }}</h1>
                            <p class="text-sm text-purple-200 mt-0.5">{{ $guardian->profession ?? 'Profession non renseignée' }}</p>
                        </div>
                    </div>
                    <div class="space-y-1 text-right">
                        <div class="text-xs text-purple-300">📱 {{ $guardian->phone_primary }}</div>
                        @if ($guardian->phone_secondary)
                            <div class="text-xs text-purple-400">📞 {{ $guardian->phone_secondary }}</div>
                        @endif
                        @if ($guardian->email)
                            <div class="text-xs text-purple-400">✉️ {{ $guardian->email }}</div>
                        @endif
                    </div>
                </div>

                <!-- Enfants rattachés -->
                <div class="p-8">
                    <h3 class="text-base font-black text-slate-900 mb-4 flex items-center gap-2">
                        🎓 Élèves Rattachés ({{ $guardian->students->count() }})
                    </h3>
                    @forelse ($guardian->students as $student)
                        <a href="{{ route('students.show', $student) }}"
                           class="flex items-center gap-4 p-4 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-purple-50 hover:border-purple-200 transition mb-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-purple-600 to-indigo-900 flex items-center justify-center text-white font-black text-sm flex-shrink-0">
                                {{ mb_substr($student->first_name, 0, 1) }}{{ mb_substr($student->last_name, 0, 1) }}
                            </div>
                            <div class="flex-1">
                                <div class="font-black text-slate-900 text-sm">{{ $student->last_name }} {{ $student->first_name }}</div>
                                <div class="text-xs text-slate-500 font-semibold">
                                    Matricule : {{ $student->matricule }} &bull;
                                    Classe : {{ $student->currentEnrollment?->schoolClass?->name ?? 'Non inscrit' }}
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @empty
                        <p class="text-sm text-slate-400 italic text-center py-6">Aucun élève rattaché à ce tuteur.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
