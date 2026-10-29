<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <div>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-0.5">
                    {{ $schoolClass->level->cycle->name }} · {{ $activeYear?->name ?? '–' }}
                </p>
                <h2 class="font-black text-2xl text-purple-950 leading-tight">
                    {{ $schoolClass->name }} — Semaine du {{ $weekStart->translatedFormat('d M') }} au {{ $weekEnd->translatedFormat('d M Y') }}
                </h2>
                <p class="text-xs text-slate-400 font-semibold mt-0.5">
                    @if ($subject) {{ $subject->name }} @else Appel général @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('attendances.show', ['class_id' => $schoolClass->id]) }}"
                   class="px-4 py-2.5 text-xs font-black bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition shadow-md">
                    ← Retour
                </a>
                <a href="{{ route('attendances.create', ['class_id' => $schoolClass->id]) }}"
                   class="px-4 py-2.5 text-xs font-black bg-purple-900 text-white rounded-xl hover:bg-purple-800 transition shadow-md">
                    + Nouvelle Feuille
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            @if (session('status'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-2xl">
                    <strong>Succès !</strong> {{ session('status') }}
                </div>
            @endif

            @if ($days->isEmpty())
                <div class="bg-white rounded-3xl border border-slate-200/80 p-16 text-center">
                    <p class="text-sm text-slate-400 italic">Aucune feuille enregistrée pour cette semaine.</p>
                    <a href="{{ route('attendances.create', ['class_id' => $schoolClass->id]) }}"
                       class="inline-block mt-4 px-5 py-2.5 text-xs font-black bg-purple-900 text-white rounded-xl hover:bg-purple-800 transition">
                        + Créer une feuille
                    </a>
                </div>
            @else

                {{-- Légende --}}
                <div class="flex flex-wrap items-center gap-4">
                    @foreach ([['bg-emerald-100 text-emerald-800', 'P', 'Présent'], ['bg-red-100 text-red-800', 'A', 'Absent'], ['bg-amber-100 text-amber-800', 'R', 'Retard'], ['bg-blue-100 text-blue-800', 'E', 'Excusé'], ['bg-slate-100 text-slate-400', '–', 'Non saisi']] as [$cls, $lbl, $name])
                        <span class="flex items-center gap-1.5 text-xs font-semibold text-slate-600">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full font-black text-[11px] {{ $cls }}">{{ $lbl }}</span>
                            {{ $name }}
                        </span>
                    @endforeach
                </div>

                {{-- Tableau croisé --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs border-collapse">

                            {{-- En-tête : jours en colonnes --}}
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    {{-- Colonne noms --}}
                                    <th class="sticky left-0 z-20 bg-slate-50 px-5 py-4 text-left font-black text-slate-500 uppercase tracking-wide whitespace-nowrap border-r border-slate-200 min-w-[180px]">
                                        Élève
                                    </th>
                                    {{-- Une colonne par jour --}}
                                    @foreach ($days as $day)
                                        <th class="px-4 py-4 text-center font-black text-slate-700 whitespace-nowrap min-w-[80px] border-r border-slate-100 last:border-r-0">
                                            <div class="text-base text-purple-950">{{ \Carbon\Carbon::parse($day)->format('d') }}</div>
                                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">
                                                {{ \Carbon\Carbon::parse($day)->translatedFormat('D') }}
                                            </div>
                                            <a href="{{ route('attendances.edit', ['class_id' => $schoolClass->id, 'date' => $day, 'subject_id' => $subject?->id]) }}"
                                               class="inline-flex items-center gap-1 mt-1.5 px-2 py-0.5 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition text-[10px] font-black">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                                                Modifier
                                            </a>
                                        </th>
                                    @endforeach
                                    {{-- Colonne totaux --}}
                                    <th class="px-4 py-4 text-center font-black text-slate-500 uppercase tracking-wide whitespace-nowrap border-l border-slate-200 min-w-[60px]">
                                        Abs.
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                @forelse ($students as $i => $student)
                                    @php
                                        $studentRecords = $records->get($student->id, collect());
                                        $absCount = $studentRecords->filter(fn ($r) => in_array($r->status, ['ABSENT', 'RETARD']))->count();
                                    @endphp
                                    <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50/50' }} hover:bg-purple-50/30 transition">

                                        {{-- Nom élève (sticky) --}}
                                        <td class="sticky left-0 z-10 {{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50/50' }} hover:bg-purple-50/30 px-5 py-3.5 border-r border-slate-200 whitespace-nowrap">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
                                                    <span class="text-[10px] font-black text-purple-800">{{ mb_strtoupper(mb_substr($student->last_name, 0, 1)) }}</span>
                                                </div>
                                                <div>
                                                    <div class="font-black text-slate-900">{{ $student->last_name }}</div>
                                                    <div class="text-[11px] text-slate-400 font-semibold">{{ $student->first_name }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Statut par jour --}}
                                        @foreach ($days as $day)
                                            @php
                                                $record = $studentRecords->get($day);
                                                [$bg, $label] = match($record?->status) {
                                                    'PRESENT' => ['bg-emerald-100 text-emerald-800', 'P'],
                                                    'ABSENT'  => ['bg-red-100 text-red-800', 'A'],
                                                    'RETARD'  => ['bg-amber-100 text-amber-800', 'R'],
                                                    'EXCUSE'  => ['bg-blue-100 text-blue-800', 'E'],
                                                    default   => ['bg-slate-100 text-slate-400', '–'],
                                                };
                                            @endphp
                                            <td class="px-4 py-3.5 text-center border-r border-slate-100 last:border-r-0">
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full font-black text-[11px] {{ $bg }}"
                                                      title="{{ $record?->reason ?? '' }}">
                                                    {{ $label }}
                                                </span>
                                            </td>
                                        @endforeach

                                        {{-- Total absences --}}
                                        <td class="px-4 py-3.5 text-center border-l border-slate-200">
                                            @if ($absCount > 0)
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-red-100 text-red-700 font-black text-[11px]">
                                                    {{ $absCount }}
                                                </span>
                                            @else
                                                <span class="text-slate-300 font-bold">0</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $days->count() + 2 }}" class="px-5 py-10 text-center text-sm text-slate-400 italic">
                                            Aucun élève inscrit dans cette classe.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            {{-- Pied de tableau : totaux par jour --}}
                            <tfoot>
                                <tr class="bg-slate-50 border-t-2 border-slate-200">
                                    <td class="sticky left-0 z-10 bg-slate-50 px-5 py-3 font-black text-slate-500 text-[11px] uppercase tracking-wide border-r border-slate-200">
                                        Absents / Retards
                                    </td>
                                    @foreach ($days as $day)
                                        @php
                                            $dayAbs = 0;
                                            foreach ($students as $s) {
                                                $r = $records->get($s->id, collect())->get($day);
                                                if ($r && in_array($r->status, ['ABSENT', 'RETARD'])) $dayAbs++;
                                            }
                                        @endphp
                                        <td class="px-4 py-3 text-center border-r border-slate-100 last:border-r-0">
                                            @if ($dayAbs > 0)
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-red-100 text-red-700 font-black text-[11px]">{{ $dayAbs }}</span>
                                            @else
                                                <span class="text-slate-300 font-bold text-xs">0</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="border-l border-slate-200"></td>
                                </tr>
                            </tfoot>

                        </table>
                    </div>
                </div>

            @endif

        </div>
    </div>
</x-app-layout>
