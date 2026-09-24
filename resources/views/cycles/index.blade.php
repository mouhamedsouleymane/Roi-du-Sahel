@php
$colorClasses = [
    'Violet' => 'bg-purple-600',
    'Jaune' => 'bg-amber-500',
    'Vert' => 'bg-green-600',
    'Bleu' => 'bg-blue-600',
    'Rouge' => 'bg-red-600',
];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Cycles d\'Enseignement & Niveaux Scolaires') }}
            </h2>
            <a href="{{ route('cycles.create') }}" class="px-4 py-2 text-xs font-black bg-purple-900 hover:bg-purple-800 text-white rounded-xl transition shadow-sm">
                ➕ Nouveau Cycle
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($cycles as $cycle)
                    @php $colorClass = $colorClasses[$cycle->uniform_tshirt_color] ?? 'bg-gray-500'; @endphp
                    <div class="bg-white shadow sm:rounded-lg overflow-hidden border border-gray-100 {{ ! $cycle->is_active ? 'opacity-60' : '' }}">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-full text-white {{ $colorClass }}">
                                        {{ $cycle->code }}
                                    </span>
                                    @if(! $cycle->is_active)
                                        <span class="text-xs font-bold uppercase tracking-wider px-2 py-1 rounded-full bg-gray-200 text-gray-600">
                                            Inactif
                                        </span>
                                    @endif
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mt-2">{{ $cycle->name }}</h3>
                            </div>
                            <div class="text-right text-xs text-gray-500">
                                <div><strong>Horaires :</strong> {{ $cycle->start_time }} - {{ $cycle->end_time }}</div>
                                <div><strong>Tenue T-shirt :</strong> {{ $cycle->uniform_tshirt_color }}</div>
                            </div>
                        </div>

                        <div class="p-6">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Niveaux Scolaires Rattachés</h4>
                            <div class="space-y-2">
                                @forelse ($cycle->levels as $level)
                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border border-gray-100">
                                        <div class="flex items-center gap-3">
                                            <span class="w-7 h-7 bg-white text-gray-700 border font-bold text-xs rounded-full flex items-center justify-center">
                                                {{ $level->order_index }}
                                            </span>
                                            <div>
                                                <div class="font-semibold text-gray-800 text-sm">{{ $level->name }}</div>
                                                <div class="text-xs text-gray-400">Code: {{ $level->code }}</div>
                                            </div>
                                        </div>
                                        <span class="text-xs font-semibold px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-md">
                                            {{ $level->classes->count() }} classe(s)
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-sm text-gray-500 italic">Aucun niveau défini pour ce cycle.</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="p-4 border-t border-gray-100 flex justify-end gap-2 bg-white">
                            <a href="{{ route('cycles.show', $cycle) }}"
                               class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                👁️ Voir
                            </a>
                            <a href="{{ route('cycles.edit', $cycle) }}"
                               class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
                                ✏️ Modifier
                            </a>
                            <form method="POST" action="{{ route('cycles.toggle-active', $cycle) }}" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg {{ $cycle->is_active ? 'bg-gray-50 text-gray-700 hover:bg-gray-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }} transition">
                                    {{ $cycle->is_active ? '⏸️ Désactiver' : '▶️ Activer' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('cycles.destroy', $cycle) }}" class="inline" onsubmit="return confirm('Supprimer ce cycle ?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition">
                                    🗑️ Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
