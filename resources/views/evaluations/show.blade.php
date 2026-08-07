<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                📝 Évaluation — {{ $evaluation->title }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('evaluations.grades', $evaluation) }}" class="px-4 py-2 text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition shadow-sm">
                    ✍️ Saisir les Notes
                </a>
                <a href="{{ route('evaluations.edit', $evaluation) }}" class="px-4 py-2 text-xs font-black bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition shadow-sm">
                    ✏️ Modifier
                </a>
                <a href="{{ route('evaluations.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                    ← Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            <!-- En-tête Évaluation -->
            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                <div class="p-8 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <div class="text-xs font-black text-amber-400 uppercase tracking-widest">{{ $evaluation->type }}</div>
                            <h1 class="text-2xl font-black text-white mt-1">{{ $evaluation->title }}</h1>
                            <p class="text-purple-200 text-sm mt-0.5">
                                {{ $evaluation->schoolClass->name }} — {{ $evaluation->subject->name }}
                            </p>
                        </div>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div class="bg-white/10 rounded-2xl p-4">
                                <div class="text-xs text-purple-300 font-bold">Date</div>
                                <div class="text-base font-black text-white mt-1">{{ $evaluation->evaluation_date->format('d/m/Y') }}</div>
                            </div>
                            <div class="bg-white/10 rounded-2xl p-4">
                                <div class="text-xs text-purple-300 font-bold">Sur</div>
                                <div class="text-base font-black text-white mt-1">{{ $evaluation->max_score }}</div>
                            </div>
                            <div class="bg-white/10 rounded-2xl p-4">
                                <div class="text-xs text-purple-300 font-bold">Coeff.</div>
                                <div class="text-base font-black text-white mt-1">{{ $evaluation->coefficient }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes saisies -->
                <div class="p-8">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-4">
                        Notes des Élèves ({{ $evaluation->grades->count() }} / {{ $evaluation->schoolClass->enrollments->whereIn('status', ['VALIDE', 'INSCRIT'])->count() }})
                    </h3>

                    @if ($evaluation->grades->isEmpty())
                        <div class="text-center py-12">
                            <p class="text-slate-400 text-sm italic mb-4">Aucune note saisie pour cette évaluation.</p>
                            <a href="{{ route('evaluations.grades', $evaluation) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-purple-900 text-white text-xs font-black rounded-2xl hover:bg-purple-800 transition shadow-lg">
                                ✍️ Commencer la saisie des notes
                            </a>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-2xl border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-black text-slate-600 uppercase tracking-widest">Élève</th>
                                        <th class="px-6 py-3 text-center text-xs font-black text-slate-600 uppercase tracking-widest">Note / {{ $evaluation->max_score }}</th>
                                        <th class="px-6 py-3 text-center text-xs font-black text-slate-600 uppercase tracking-widest">Appréciation</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    @foreach ($evaluation->grades->sortBy(fn($g) => $g->enrollment->student->last_name) as $grade)
                                        <tr class="hover:bg-purple-50/30 transition">
                                            <td class="px-6 py-3 text-sm font-bold text-slate-900">
                                                {{ $grade->enrollment->student->last_name }} {{ $grade->enrollment->student->first_name }}
                                            </td>
                                            <td class="px-6 py-3 text-center">
                                                <span class="text-base font-black {{ $grade->score >= ($evaluation->max_score * 0.5) ? 'text-emerald-700' : 'text-red-600' }}">
                                                    {{ $grade->score }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-3 text-center text-xs text-slate-500 font-semibold">
                                                {{ $grade->comment ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
