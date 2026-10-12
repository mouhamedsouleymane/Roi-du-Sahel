<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <h2 class="font-black text-2xl text-purple-950 leading-tight">🏫 Classes</h2>
                <form method="GET" action="{{ route('classes.index') }}" data-no-confirm="true">
                    <select name="academic_year_id" onchange="this.form.submit()"
                            class="text-xs font-bold border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}{{ $year->is_active ? ' ✅' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <a href="{{ route('classes.create') }}" class="px-4 py-2 text-xs font-black bg-purple-900 hover:bg-purple-800 text-white rounded-xl transition shadow-sm">
                ➕ Nouvelle Classe
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            @if(session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm font-semibold">
                    {{ session('status') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            @forelse ($classes as $cycleName => $classList)
                <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">🏫 {{ $cycleName }}</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach ($classList as $cls)
                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl hover:border-purple-200 hover:bg-purple-50/40 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-base font-black text-slate-900">{{ $cls->name }}</span>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">
                                        {{ $cls->enrollments_count ?? 0 }}/{{ $cls->capacity }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 space-y-0.5 font-semibold">
                                    <div>📚 {{ $cls->level->name }}</div>
                                    <div>📍 {{ $cls->room_number ?? 'Salle non attribuée' }}</div>
                                    <div>👤 {{ $cls->mainTeacher?->name ?? 'Sans prof principal' }}</div>
                                </div>
                                <div class="mt-3 grid grid-cols-2 gap-1.5">
                                    <a href="{{ route('classes.show', $cls) }}"
                                       title="Fiche détaillée"
                                       class="flex items-center justify-center gap-1 text-[11px] font-bold px-2 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                        👁️ Voir
                                    </a>
                                    <a href="{{ route('schedules.index', ['class_id' => $cls->id]) }}"
                                       title="Emploi du temps"
                                       class="flex items-center justify-center gap-1 text-[11px] font-bold px-2 py-1.5 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 transition">
                                        📅 Emploi du temps
                                    </a>
                                    <a href="{{ route('classes.edit', $cls) }}"
                                       title="Modifier"
                                       class="flex items-center justify-center gap-1 text-[11px] font-bold px-2 py-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
                                        ✏️ Modifier
                                    </a>
                                    @if(($cls->all_enrollments_count ?? 0) > 0)
                                        <button type="button" disabled
                                                title="Impossible de supprimer : {{ $cls->all_enrollments_count }} inscription(s) rattachée(s)"
                                                class="w-full flex items-center justify-center gap-1 text-[11px] font-bold px-2 py-1.5 rounded-lg bg-slate-100 text-slate-400 cursor-not-allowed">
                                            🗑️ Supprimer
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('classes.destroy', $cls) }}" class="inline" onsubmit="return confirm('Supprimer la classe {{ $cls->name }} ?');">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    title="Supprimer"
                                                    class="w-full flex items-center justify-center gap-1 text-[11px] font-bold px-2 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition">
                                                🗑️ Supprimer
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-xl rounded-3xl p-12 text-center border border-slate-200/80">
                    <p class="text-slate-400 italic text-sm">Aucune classe définie pour cette année scolaire.</p>
                    <a href="{{ route('classes.create') }}" class="mt-4 inline-block px-5 py-2.5 text-xs font-black bg-purple-900 text-white rounded-xl hover:bg-purple-800 transition">
                        ➕ Créer la première classe
                    </a>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>
