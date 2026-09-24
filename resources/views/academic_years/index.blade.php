<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                📅 Années Scolaires
            </h2>
            <a href="{{ route('academic-years.create') }}" class="px-4 py-2 text-xs font-black bg-purple-900 hover:bg-purple-800 text-white rounded-xl transition shadow-sm">
                ➕ Nouvelle Année
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">Année Scolaire</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">Période</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">Clôture</th>
                                <th class="px-6 py-4 text-right text-xs font-black text-slate-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            @forelse ($academicYears as $year)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-black text-slate-900 text-sm">{{ $year->name }}</div>
                                        @if($year->description)
                                            <div class="text-xs text-slate-400 mt-0.5">{{ $year->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                        {{ $year->start_date->format('d/m/Y') }} → {{ $year->end_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($year->is_active)
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">
                                                ✅ Active
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-500">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($year->is_closed)
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-red-100 text-red-700">
                                                🔒 Clôturée
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                                Ouverte
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('academic-years.show', $year) }}"
                                               class="text-xs font-bold px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                                👁️ Voir
                                            </a>
                                            <a href="{{ route('academic-years.edit', $year) }}"
                                               class="text-xs font-bold px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
                                                ✏️ Modifier
                                            </a>
                                            @if(! $year->is_active)
                                                <form method="POST" action="{{ route('academic-years.activate', $year) }}" class="inline">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">
                                                        ▶️ Activer
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('academic-years.toggle-close', $year) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg {{ $year->is_closed ? 'bg-blue-50 text-blue-700 hover:bg-blue-100' : 'bg-gray-50 text-gray-700 hover:bg-gray-100' }} transition">
                                                    {{ $year->is_closed ? '🔓 Réouvrir' : '🔒 Clôturer' }}
                                                </button>
                                            </form>
                                            @if(! $year->is_active)
                                                <form method="POST" action="{{ route('academic-years.destroy', $year) }}" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition">
                                                        🗑️
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-400 italic">
                                        Aucune année scolaire enregistrée.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
