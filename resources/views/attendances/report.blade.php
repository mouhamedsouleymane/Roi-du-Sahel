<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                📊 Bilan & Rapport des Absences
            </h2>
            <a href="{{ route('attendances.index') }}" class="px-4 py-2.5 text-xs font-black bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-all shadow-md">
                ← Retour à la feuille d'appel
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-2xl shadow-xs">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Filtres de période et classe --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6">
                <form method="GET" action="{{ route('attendances.report') }}" data-no-confirm="true" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Classe</label>
                        <select name="class_id" onchange="this.form.submit()" class="w-full text-xs border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 font-bold">
                            @foreach ($classes as $cls)
                                <option value="{{ $cls->id }}" {{ $selectedClassId == $cls->id ? 'selected' : '' }}>
                                    {{ $cls->level->cycle->name }} — {{ $cls->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Date Début</label>
                        <input type="date" name="start_date" value="{{ $startDate }}"
                               class="w-full text-xs border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 font-bold" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Date Fin</label>
                        <input type="date" name="end_date" value="{{ $endDate }}"
                               class="w-full text-xs border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 font-bold" />
                    </div>

                    <div>
                        <button type="submit" class="w-full px-4 py-2.5 bg-purple-900 text-white text-xs font-black rounded-xl hover:bg-purple-800 transition-all shadow-md">
                            📊 Générer le Bilan
                        </button>
                    </div>
                </form>
            </div>

            {{-- Résumé par classe --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-base font-black text-purple-950">
                            Classe : {{ $selectedClass?->name ?? '–' }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5 font-semibold">
                            Période du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Matricule</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Élève</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-emerald-700 uppercase">Présences</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-red-700 uppercase">Absences</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-amber-700 uppercase">Retards</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-blue-700 uppercase">Excusés</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Taux Assiduité</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($reportData as $st)
                                @php
                                    $totalRecorded = $st->total_presents + $st->total_absents + $st->total_retards + $st->total_excuses;
                                    $assiduousCount = $st->total_presents + $st->total_retards + $st->total_excuses;
                                    $rate = $totalRecorded > 0 ? round(($assiduousCount / $totalRecorded) * 100) : null;
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-xs font-mono font-bold text-indigo-600">
                                        {{ $st->matricule }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-bold text-gray-900">
                                        {{ $st->last_name }} {{ $st->first_name }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm font-bold text-emerald-600 bg-emerald-50/50">
                                        {{ $st->total_presents }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm font-bold text-red-600 bg-red-50/50">
                                        {{ $st->total_absents }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm font-bold text-amber-600 bg-amber-50/50">
                                        {{ $st->total_retards }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm font-bold text-blue-600 bg-blue-50/50">
                                        {{ $st->total_excuses }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($rate === null)
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-slate-100 text-slate-400">
                                                –
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $rate >= 90 ? 'bg-emerald-100 text-emerald-800' : ($rate >= 75 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                                {{ $rate }}%
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400 italic">
                                        Aucune donnée de présence disponible pour cette sélection.
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
