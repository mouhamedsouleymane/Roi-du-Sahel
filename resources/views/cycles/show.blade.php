<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                🏛️ Fiche Cycle — {{ $cycle->name }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('cycles.edit', $cycle) }}" class="px-4 py-2 text-xs font-black bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition shadow-sm">
                    ✏️ Modifier
                </a>
                <a href="{{ route('cycles.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                    ← Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <!-- En-tête Cycle -->
            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                <div class="p-8 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white flex items-center gap-6">
                    <div class="w-16 h-16 rounded-2xl bg-amber-400/20 border-2 border-amber-400/40 flex items-center justify-center text-3xl">
                        🏛️
                    </div>
                    <div>
                        <div class="text-xs font-black text-amber-400 uppercase tracking-widest">Code : {{ $cycle->code }}</div>
                        <h1 class="text-3xl font-black text-white mt-1">{{ $cycle->name }}</h1>
                        @if ($cycle->description)
                            <p class="text-purple-300 text-sm mt-1">{{ $cycle->description }}</p>
                        @endif
                    </div>
                </div>

                <!-- Niveaux rattachés -->
                <div class="p-8">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-4">
                        📚 Niveaux du Cycle ({{ $cycle->levels->count() }})
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @forelse ($cycle->levels as $level)
                            <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-purple-50/50 hover:border-purple-200 transition">
                                <div class="font-black text-slate-900 text-sm">{{ $level->name }}</div>
                                <div class="text-xs text-slate-500 mt-1 font-semibold">
                                    {{ $level->classes->count() }} classe(s) &bull;
                                    {{ $level->classes->sum(fn($c) => $c->enrollments_count ?? 0) }} élèves
                                </div>
                                <div class="mt-3 flex flex-wrap gap-1">
                                    @foreach ($level->classes as $class)
                                        <span class="px-2 py-0.5 text-[10px] font-bold bg-purple-100 text-purple-900 rounded-full">{{ $class->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-8 text-sm text-slate-400 italic">
                                Aucun niveau défini pour ce cycle.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
