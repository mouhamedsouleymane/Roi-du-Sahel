<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-extrabold text-xl text-purple-950 leading-tight">
                📄 Bulletin de Notes — {{ $reportCard->period->name }}
            </h2>
            <a href="{{ route('report-cards.index', ['class_id' => $reportCard->class_id, 'period_id' => $reportCard->period_id]) }}" class="text-sm font-bold text-purple-900 hover:text-purple-700">
                ← Retour aux bulletins
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <!-- En-tête du Bulletin -->
            <div class="bg-white shadow-2xl rounded-3xl overflow-hidden border border-purple-100">
                <!-- Entête officiel établissement -->
                <div class="bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white p-8 relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10 flex flex-wrap justify-between items-center gap-6">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-400 to-purple-600 flex items-center justify-center text-3xl shadow-lg ring-2 ring-amber-400/40">
                                👑
                            </div>
                            <div>
                                <div class="text-xs font-black tracking-widest uppercase text-amber-400">Complexe Scolaire Privé</div>
                                <div class="text-2xl font-black tracking-wide text-white">LES ROIS DU SAHEL</div>
                                <div class="text-xs opacity-80 mt-0.5">Koubia Plateau • Niamey, Niger</div>
                            </div>
                        </div>

                        <div class="text-right bg-white/10 backdrop-blur-md px-5 py-3 rounded-2xl border border-white/20">
                            <div class="text-[10px] uppercase font-extrabold tracking-widest text-amber-300">Bulletin Officiel</div>
                            <div class="text-xl font-black text-white">{{ $reportCard->period->name }}</div>
                            <div class="text-xs opacity-80">{{ $reportCard->period->academicYear->name }}</div>
                        </div>
                    </div>

                    <!-- Devise Officielle Banner -->
                    <div class="mt-6 pt-4 border-t border-purple-800/60 text-center">
                        <span class="text-xs font-black tracking-widest text-amber-300 uppercase">
                            DISCIPLINE • TRAVAIL • SUCCÈS
                        </span>
                    </div>
                </div>

                <!-- Info élève -->
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wide">Élève</div>
                        <div class="text-lg font-black text-slate-900 mt-1">
                            {{ $reportCard->student->last_name }} {{ $reportCard->student->first_name }}
                        </div>
                        <div class="text-xs text-purple-700 font-mono font-bold">{{ $reportCard->student->matricule }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wide">Classe & Cycle</div>
                        <div class="text-base font-black text-slate-900 mt-1">{{ $reportCard->schoolClass->name }}</div>
                        <div class="text-xs text-slate-500 font-semibold">{{ $reportCard->schoolClass->level->cycle->name }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wide">Rang du Trimestre</div>
                        <div class="text-3xl font-black text-purple-950 mt-1">
                            {{ $reportCard->rank }}<span class="text-sm text-slate-400 font-normal">/{{ $reportCard->total_students }}</span>
                        </div>
                    </div>
                </div>

                <!-- Tableau des moyennes par matière -->
                <div class="p-6">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead>
                            <tr class="bg-purple-950 text-white">
                                <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider">Matière</th>
                                <th class="px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wider">Coeff.</th>
                                <th class="px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wider">Moy. / 20</th>
                                <th class="px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wider">Appréciation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($subjectDetails as $detail)
                                @php $avg = (float)$detail['average']; @endphp
                                <tr class="{{ $avg < 10 ? 'bg-red-50/50' : '' }}">
                                    <td class="px-4 py-3.5 text-sm font-bold text-slate-900">{{ $detail['subject'] }}</td>
                                    <td class="px-4 py-3.5 text-center text-sm font-bold text-slate-600">{{ $detail['coefficient'] }}</td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="text-base font-extrabold {{ $avg >= 10 ? 'text-slate-900' : 'text-red-700' }}">
                                            {{ number_format($avg, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center text-xs font-extrabold text-slate-700">
                                        {{ match(true) {
                                            $avg >= 18 => 'Excellent',
                                            $avg >= 16 => 'Très Bien',
                                            $avg >= 14 => 'Bien',
                                            $avg >= 12 => 'Assez Bien',
                                            $avg >= 10 => 'Passable',
                                            default    => 'Insuffisant',
                                        } }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Moyenne Générale -->
                <div class="p-6 bg-slate-50 border-t border-slate-200 flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <div class="text-xs font-bold text-slate-500 uppercase tracking-wide">Appréciation du Conseil</div>
                        <div class="text-lg font-black text-purple-950 mt-1">
                            {{ $reportCard->appreciationLabel }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-bold text-slate-500 uppercase tracking-wide">Moyenne Générale</div>
                        @php $genAvg = (float)$reportCard->general_average; @endphp
                        <div class="text-4xl font-black mt-1 {{ $genAvg >= 10 ? 'text-emerald-700' : 'text-red-700' }}">
                            {{ number_format($genAvg, 2) }}<span class="text-base font-normal text-slate-500">/20</span>
                        </div>
                    </div>
                </div>

                @if (! $reportCard->is_published)
                    <div class="px-6 py-3 bg-amber-50 border-t border-amber-200 text-xs text-amber-800 font-bold">
                        ⚠️ Ce bulletin est en mode brouillon et n'a pas encore été publié.
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
