@php
$colorClasses = [
    'Violet' => 'bg-purple-600',
    'Jaune' => 'bg-amber-500',
    'Vert' => 'bg-green-600',
    'Bleu' => 'bg-blue-600',
    'Rouge' => 'bg-red-600',
];
$colorClass = $colorClasses[$cycle->uniform_tshirt_color] ?? 'bg-gray-500';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                🏛️ Fiche Cycle — {{ $cycle->name }}
                @if(! $cycle->is_active)
                    <span class="text-xs font-bold uppercase tracking-wider px-2 py-1 rounded-full bg-gray-200 text-gray-600 ml-2">
                        Inactif
                    </span>
                @endif
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

            <!-- En-tête Cycle -->
            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                <div class="p-8 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white flex items-center gap-6">
                    <div class="w-16 h-16 rounded-2xl bg-amber-400/20 border-2 border-amber-400/40 flex items-center justify-center text-3xl">
                        🏛️
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black uppercase tracking-widest px-2 py-0.5 rounded {{ $colorClass }} text-white">
                                {{ $cycle->code }}
                            </span>
                            <span class="text-xs text-purple-300">{{ $cycle->start_time }} - {{ $cycle->end_time }}</span>
                        </div>
                        <h1 class="text-3xl font-black text-white mt-1">{{ $cycle->name }}</h1>
                        @if ($cycle->description)
                            <p class="text-purple-300 text-sm mt-1">{{ $cycle->description }}</p>
                        @endif
                        <p class="text-purple-400 text-xs mt-1">👕 T-shirt : {{ $cycle->uniform_tshirt_color }}</p>
                    </div>
                </div>

                <!-- Niveaux rattachés -->
                <div class="p-8" x-data="{ showForm: false }">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">
                            📚 Niveaux du Cycle ({{ $cycle->levels->count() }})
                        </h3>
                        <div class="flex gap-2">
                            <button type="button" x-show="!showForm" @click="showForm = true"
                                    class="px-3 py-1.5 text-xs font-bold bg-purple-100 text-purple-900 rounded-lg hover:bg-purple-200 transition">
                                ➕ Ajouter un niveau
                            </button>
                            <button type="button" x-show="showForm" @click="showForm = false"
                                    class="px-3 py-1.5 text-xs font-bold bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                                ✕ Annuler
                            </button>
                        </div>
                    </div>

                    <!-- Formulaire ajout niveau -->
                    <div x-show="showForm" class="mb-6 p-4 bg-purple-50 rounded-2xl border border-purple-200" style="display:none">
                        <form method="POST" action="{{ route('cycles.levels.store', $cycle) }}" class="grid grid-cols-4 gap-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Code</label>
                                <input type="text" name="code" required placeholder="6EME"
                                       class="w-full text-xs font-bold border-slate-300 rounded-lg px-2 py-1.5 uppercase" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Nom</label>
                                <input type="text" name="name" required placeholder="Sixième"
                                       class="w-full text-xs font-bold border-slate-300 rounded-lg px-2 py-1.5" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Ordre</label>
                                <input type="number" name="order_index" required min="1" value="{{ $cycle->levels->max('order_index') + 1 }}"
                                       class="w-full text-xs font-bold border-slate-300 rounded-lg px-2 py-1.5" />
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="px-4 py-1.5 text-xs font-bold bg-purple-900 text-white rounded-lg hover:bg-purple-800">
                                    💾 Ajouter
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @forelse ($cycle->levels as $level)
                            <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 hover:bg-purple-50/50 hover:border-purple-200 transition relative group">
                                <div class="font-black text-slate-900 text-sm">{{ $level->name }}</div>
                                <div class="text-xs text-slate-500 mt-1 font-semibold">
                                    {{ $level->classes->count() }} classe(s) &bull;
                                    {{ $level->classes->sum('enrollments_count') }} élèves
                                </div>
                                <div class="mt-3 flex flex-wrap gap-1">
                                    @foreach ($level->classes as $class)
                                        <span class="px-2 py-0.5 text-[10px] font-bold bg-purple-100 text-purple-900 rounded-full">{{ $class->name }}</span>
                                    @endforeach
                                </div>
                                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition flex gap-1">
                                    <form method="POST" action="{{ route('cycles.levels.destroy', [$cycle, $level]) }}" onsubmit="return confirm('Supprimer ce niveau ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-bold">🗑️</button>
                                    </form>
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
