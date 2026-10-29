<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl">⏱️</span>
                    <h2 class="font-black text-2xl text-purple-950 leading-tight">
                        Plages Horaires
                    </h2>
                </div>
                <p class="text-xs text-slate-500 font-semibold mt-1">
                    Gérez les lignes d'heures de la grille des emplois du temps (ajout, ordre, pauses et suppression).
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('schedules.index') }}"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                    <span>📅</span>
                    <span>Emplois du Temps</span>
                </a>
                <a href="{{ route('time-slots.create') }}"
                    class="px-4 py-2 bg-purple-950 hover:bg-purple-900 text-white font-black text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                    <span>➕</span>
                    <span>Nouvelle Plage</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div
                    class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-950 flex items-center gap-3 shadow-xs">
                    <span class="text-2xl">✅</span>
                    <div class="text-xs font-bold">{{ session('status') }}</div>
                </div>
            @endif

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">
                        Liste des Plages Horaires Actives ({{ $timeSlots->count() }})
                    </h3>
                    <span class="text-xs text-slate-400">
                        L'ordre détermine la disposition verticale dans la grille
                    </span>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-100">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-purple-950 text-white font-black uppercase text-[10px] tracking-wider">
                                <th class="p-3 text-center w-16">Ordre</th>
                                <th class="p-3">Horaires & Libellé</th>
                                <th class="p-3">Intitulé</th>
                                <th class="p-3">Cycle</th>
                                <th class="p-3 text-center">Type</th>
                                <th class="p-3 text-center">Statut</th>
                                <th class="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($timeSlots as $slot)
                                <tr
                                    class="hover:bg-purple-50/30 transition {{ $slot->is_pause ? 'bg-amber-50/40' : '' }}">
                                    <td class="p-3 text-center font-black text-slate-500">
                                        #{{ $slot->order_index }}
                                    </td>
                                    <td class="p-3 font-extrabold text-purple-950">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="px-2 py-0.5 rounded-md font-mono text-[11px] font-bold {{ $slot->is_pause ? 'bg-amber-100 text-amber-900' : 'bg-purple-100 text-purple-900' }}">
                                                {{ substr($slot->start_time, 0, 5) }} -
                                                {{ substr($slot->end_time, 0, 5) }}
                                            </span>
                                            @if ($slot->label && $slot->label !== substr($slot->start_time, 0, 5) . ' - ' . substr($slot->end_time, 0, 5))
                                                <span
                                                    class="text-[11px] text-slate-500 font-normal">({{ $slot->label }})</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-3 font-bold text-slate-700">
                                        {{ $slot->name ?: '—' }}
                                    </td>
                                    <td class="p-3 text-slate-600 font-semibold">
                                        {{ $slot->cycle?->name ?? '🌐 Tous les cycles' }}
                                    </td>
                                    <td class="p-3 text-center">
                                        @if ($slot->is_pause)
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300">
                                                🥪 Pause Déjeuner
                                            </span>
                                        @else
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                                📚 Cours
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center">
                                        <form method="POST" action="{{ route('time-slots.toggle-status', $slot) }}"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="px-2 py-0.5 rounded-full text-[10px] font-black transition border {{ $slot->is_active ? 'bg-emerald-100 text-emerald-900 border-emerald-300 hover:bg-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-300 hover:bg-slate-200' }}"
                                                title="Cliquer pour activer/désactiver">
                                                {{ $slot->is_active ? '✅ Actif' : '⏸️ Inactif' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="p-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('time-slots.edit', $slot) }}"
                                                class="p-1.5 text-amber-700 hover:bg-amber-100 rounded-lg transition"
                                                title="Modifier cette plage">
                                                ✏️
                                            </a>
                                            <form method="POST" action="{{ route('time-slots.destroy', $slot) }}"
                                                class="inline"
                                                data-confirm="Êtes-vous sûr de vouloir supprimer définitivement la plage {{ $slot->label }} ?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-1.5 text-rose-700 hover:bg-rose-100 rounded-lg transition"
                                                    title="Supprimer cette plage">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-400 italic">
                                        Aucune plage horaire configurée. Cliquez sur « Nouvelle Plage » pour commencer.
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
