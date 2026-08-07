<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-extrabold text-xl text-purple-950 leading-tight">
                📊 Statistiques & Analyses Établissement
            </h2>
            <span class="text-xs font-bold text-purple-900 bg-purple-100 px-3 py-1 rounded-full border border-purple-200">
                Année : {{ $activeYear?->name ?? '–' }}
            </span>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8 animate-fade-in-up">

            {{-- ── EFFECTIFS PAR CYCLE ET GENRE ──────────────────────────── --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-7">
                <h3 class="text-lg font-black text-slate-900 mb-1">👥 Effectifs par Cycle & Genre</h3>
                <p class="text-xs text-slate-400 mb-6">Répartition Garçons / Filles par cycle d'enseignement</p>

                <div class="space-y-6">
                    @forelse ($enrollmentsByGenderCycle as $cycleName => $rows)
                        @php
                            $boys  = $rows->where('gender', 'M')->first()?->total ?? 0;
                            $girls = $rows->where('gender', 'F')->first()?->total ?? 0;
                            $total = $boys + $girls;
                            $boyPct   = $total > 0 ? round(($boys  / $total) * 100) : 0;
                            $girlPct  = $total > 0 ? round(($girls / $total) * 100) : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-extrabold text-slate-900">{{ $cycleName }}</span>
                                <span class="text-xs text-purple-900 bg-purple-50 font-bold px-2.5 py-0.5 rounded-md">{{ $total }} élève(s)</span>
                            </div>
                            <div class="flex h-7 rounded-full overflow-hidden p-0.5 bg-slate-100 border border-slate-200/60 shadow-xs">
                                @if ($boys > 0)
                                    <div class="bg-gradient-to-r from-blue-600 to-indigo-500 flex items-center justify-center text-[10px] font-black text-white transition-all duration-500 shadow-sm"
                                         style="width: {{ $boyPct }}%">
                                        {{ $boys }} Garçons ({{ $boyPct }}%)
                                    </div>
                                @endif
                                @if ($girls > 0)
                                    <div class="bg-gradient-to-r from-pink-500 to-purple-500 flex items-center justify-center text-[10px] font-black text-white transition-all duration-500 shadow-sm"
                                         style="width: {{ $girlPct }}%">
                                        {{ $girls }} Filles ({{ $girlPct }}%)
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 italic text-center py-6">Aucune donnée d'inscription disponible.</p>
                    @endforelse
                </div>
            </div>

            {{-- ── CLASSEMENT DES CLASSES PAR EFFECTIF ─────────────────── --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-7">
                    <h3 class="text-lg font-black text-slate-900 mb-1">🏆 Classement des Classes par Effectif</h3>
                    <p class="text-xs text-slate-400 mb-5">Classes ordonnées par nombre d'inscrits</p>

                    <div class="space-y-3.5">
                        @php $maxEff = $classesByEnrollment->max('total') ?: 1; @endphp
                        @forelse ($classesByEnrollment as $i => $cls)
                            @php $pct = round(($cls->total / $maxEff) * 100); @endphp
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-black w-6 text-purple-900 bg-purple-50 p-1 text-center rounded-lg">{{ $i + 1 }}</span>
                                <div class="flex-1">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-xs font-extrabold text-slate-800">{{ $cls->class_name }}</span>
                                        <span class="text-xs font-black text-purple-900">{{ $cls->total }}</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                        <div class="h-2.5 rounded-full bg-gradient-to-r from-amber-500 via-purple-700 to-indigo-600 transition-all duration-500"
                                             style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400 italic text-center py-4">Aucune inscription.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Enseignants par type de contrat --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-7">
                    <h3 class="text-lg font-black text-slate-900 mb-1">👨‍🏫 Enseignants par Contrat</h3>
                    <p class="text-xs text-slate-400 mb-6">Répartition des enseignants actifs</p>

                    @php
                        $contractColors = [
                            'PERMANENT'   => ['bg' => 'bg-purple-700', 'badge' => 'bg-purple-100 text-purple-900'],
                            'VACATAIRE'   => ['bg' => 'bg-amber-500',  'badge' => 'bg-amber-100 text-amber-900'],
                            'CONTRACTUEL' => ['bg' => 'bg-emerald-600', 'badge' => 'bg-emerald-100 text-emerald-900'],
                        ];
                        $totalTeachers = $teachersByContract->sum();
                    @endphp

                    <div class="space-y-4">
                        @forelse ($teachersByContract as $type => $count)
                            @php
                                $pct    = $totalTeachers > 0 ? round(($count / $totalTeachers) * 100) : 0;
                                $colors = $contractColors[$type] ?? ['bg' => 'bg-slate-400', 'badge' => 'bg-slate-100 text-slate-800'];
                            @endphp
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <span class="px-2.5 py-0.5 text-xs font-black rounded-full {{ $colors['badge'] }}">{{ $type }}</span>
                                    <span class="text-sm font-black text-slate-900">{{ $count }} <span class="text-xs font-normal text-slate-400">({{ $pct }}%)</span></span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                                    <div class="h-3 rounded-full {{ $colors['bg'] }} transition-all duration-500" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400 italic text-center py-4">Aucun enseignant enregistré.</p>
                        @endforelse
                    </div>

                    @if ($totalTeachers > 0)
                        <div class="mt-6 p-4 bg-purple-50 rounded-2xl text-center border border-purple-100">
                            <span class="text-3xl font-black text-purple-950">{{ $totalTeachers }}</span>
                            <span class="text-xs text-purple-700 font-extrabold block mt-0.5">enseignants actifs au total</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ── MOYENNES PAR CLASSE (DERNIER TRIMESTRE) ─────────────── --}}
            @if ($latestPeriod && count($averagesByClass) > 0)
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-7">
                <h3 class="text-lg font-black text-slate-900 mb-1">📈 Moyennes Générales par Classe</h3>
                <p class="text-xs text-slate-400 mb-6">Période : <span class="font-extrabold text-purple-900">{{ $latestPeriod->name }}</span></p>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-extrabold text-slate-500 uppercase">Rang</th>
                                <th class="px-4 py-3 text-left text-xs font-extrabold text-slate-500 uppercase">Classe</th>
                                <th class="px-4 py-3 text-center text-xs font-extrabold text-slate-500 uppercase">Élèves notés</th>
                                <th class="px-4 py-3 text-center text-xs font-extrabold text-slate-500 uppercase">Moyenne / 20</th>
                                <th class="px-4 py-3 text-left text-xs font-extrabold text-slate-500 uppercase">Progression</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($averagesByClass as $i => $row)
                                @php $avg = (float)$row->avg_class; @endphp
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3.5 text-sm font-black {{ $i === 0 ? 'text-amber-500' : 'text-slate-400' }}">
                                        #{{ $i + 1 }}
                                    </td>
                                    <td class="px-4 py-3.5 text-sm font-bold text-slate-900">{{ $row->class_name }}</td>
                                    <td class="px-4 py-3.5 text-center text-sm text-slate-700 font-semibold">{{ $row->nb }}</td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="text-lg font-black {{ $avg >= 12 ? 'text-emerald-700' : ($avg >= 10 ? 'text-amber-600' : 'text-red-600') }}">
                                            {{ number_format($avg, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                                <div class="h-2.5 rounded-full {{ $avg >= 12 ? 'bg-emerald-500' : ($avg >= 10 ? 'bg-amber-500' : 'bg-red-500') }}"
                                                     style="width: {{ min(100, round($avg / 20 * 100)) }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold text-slate-400 w-8">{{ round($avg / 20 * 100) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
