<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight tracking-tight flex items-center gap-2">
                    <span>✍️</span>
                    <span>Saisie de Notes</span>
                </h2>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 font-extrabold text-[11px]">
                        🏫 {{ $evaluation->schoolClass->name }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 font-extrabold text-[11px]">
                        📚 {{ $evaluation->subject->name }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-extrabold text-[11px]">
                        📅 {{ $evaluation->period->name }}
                    </span>
                    <span class="text-slate-400 font-semibold">• Sur {{ number_format($evaluation->max_score, 0) }} pts ({{ $evaluation->type }})</span>
                </p>
            </div>
            <a href="{{ route('evaluations.index', ['class_id' => $evaluation->class_id, 'period_id' => $evaluation->period_id]) }}"
               class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-black rounded-xl transition">
                ← Retour
            </a>
        </div>
    </x-slot>

    <div class="py-6 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            @if (session('status'))
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-950 flex items-center gap-3 shadow-xs">
                    <span class="text-2xl">✅</span>
                    <div>
                        <div class="font-black text-sm text-emerald-900">Succès !</div>
                        <div class="text-xs text-emerald-800">{{ session('status') }}</div>
                    </div>
                </div>
            @endif

            {{-- INFO BANNER --}}
            <div class="bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 rounded-3xl p-6 text-white shadow-xl">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div>
                        <div class="text-xs font-black text-amber-400 uppercase tracking-widest">{{ $evaluation->type }}</div>
                        <h1 class="text-xl font-black text-white mt-1">{{ $evaluation->title }}</h1>
                        <p class="text-purple-200 text-xs mt-0.5">
                            Enseignant : {{ $evaluation->teacher->user->name }}
                            · Date : {{ $evaluation->evaluation_date->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="bg-white/10 rounded-2xl p-4 text-center min-w-[80px]">
                            <div class="text-xs text-purple-300 font-bold">Élèves</div>
                            <div class="text-2xl font-black text-white">{{ $students->count() }}</div>
                        </div>
                        <div class="bg-white/10 rounded-2xl p-4 text-center min-w-[80px]">
                            <div class="text-xs text-purple-300 font-bold">Notés</div>
                            <div class="text-2xl font-black text-amber-400">
                                {{ $students->filter(fn($s) => $s->grades->isNotEmpty())->count() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABLE SAISIE --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                        <span>📋</span> Liste des Élèves
                    </h3>
                    <span class="text-[11px] font-black text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                        {{ $students->count() }} élève(s)
                    </span>
                </div>

                <form method="POST" action="{{ route('evaluations.grades.save', $evaluation) }}">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-gradient-to-r from-purple-950 to-indigo-950 text-white">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-widest w-10">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-widest">Matricule</th>
                                    <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-widest">Élève</th>
                                    <th class="px-6 py-3 text-center text-xs font-black uppercase tracking-widest">Absent</th>
                                    <th class="px-6 py-3 text-center text-xs font-black uppercase tracking-widest">
                                        Note (/ {{ number_format($evaluation->max_score, 0) }})
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-widest">Observation</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @foreach ($students as $i => $student)
                                    @php $grade = $student->grades->first(); @endphp
                                    <tr class="hover:bg-purple-50/30 transition">
                                        <td class="px-6 py-3 text-sm text-slate-400 font-bold tabular-nums">{{ $i + 1 }}</td>
                                        <td class="px-6 py-3 text-xs font-black font-mono text-indigo-600">{{ $student->matricule }}</td>
                                        <td class="px-6 py-3 text-sm font-black text-slate-900">
                                            {{ $student->last_name }} {{ $student->first_name }}
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <input type="hidden" name="grades[{{ $i }}][student_id]" value="{{ $student->id }}" />
                                            <input type="checkbox"
                                                   id="absent_{{ $student->id }}"
                                                   name="grades[{{ $i }}][is_absent]"
                                                   value="1"
                                                   {{ $grade?->is_absent ? 'checked' : '' }}
                                                   onchange="toggleScore(this, 'score_{{ $student->id }}')"
                                                   class="w-4 h-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500 cursor-pointer" />
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <input type="number"
                                                   id="score_{{ $student->id }}"
                                                   name="grades[{{ $i }}][score]"
                                                   min="0"
                                                   max="{{ $evaluation->max_score }}"
                                                   step="0.25"
                                                   value="{{ $grade && !$grade->is_absent ? $grade->score : '' }}"
                                                   placeholder="—"
                                                   {{ $grade?->is_absent ? 'disabled' : '' }}
                                                   class="w-24 text-center text-sm font-black border-purple-300 rounded-xl shadow-xs focus:border-purple-500 focus:ring-purple-500 disabled:bg-slate-100 disabled:text-slate-400 transition" />
                                        </td>
                                        <td class="px-6 py-3">
                                            <input type="text"
                                                   name="grades[{{ $i }}][comment]"
                                                   value="{{ $grade?->comment ?? '' }}"
                                                   placeholder="Facultatif"
                                                   class="w-full text-xs font-semibold border-purple-300 rounded-xl shadow-xs focus:border-purple-500 focus:ring-purple-500" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <p class="text-xs text-slate-500 font-semibold">
                            💡 Cochez "Absent" pour désactiver la saisie de note.
                        </p>
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-purple-950 to-indigo-950 hover:from-purple-900 hover:to-indigo-900 text-white text-xs font-black rounded-xl shadow-sm transition">
                            💾 Enregistrer toutes les Notes
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        function toggleScore(checkbox, scoreFieldId) {
            const field = document.getElementById(scoreFieldId);
            if (checkbox.checked) {
                field.disabled = true;
                field.value = '';
            } else {
                field.disabled = false;
                field.focus();
            }
        }
    </script>
</x-app-layout>

