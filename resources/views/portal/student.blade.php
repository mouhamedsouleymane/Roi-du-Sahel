<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight">
                    🎓 Mon Espace Élève
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Consultez vos évaluations, vos notes et vos bulletins officiels
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs">
                👑 Année : {{ $activeYear?->name ?? '–' }}
            </span>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            @if ($student)
                {{-- En-tête profil élève aux couleurs officielles --}}
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 p-8 text-white shadow-2xl border border-purple-800/40">
                    <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10 flex flex-wrap justify-between items-center gap-6">
                        <div class="flex items-center gap-5">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-400 to-purple-600 font-black text-3xl flex items-center justify-center text-white shadow-lg ring-2 ring-amber-400/40 animate-float">
                                🎓
                            </div>
                            <div>
                                <div class="text-xs font-black text-amber-400 uppercase tracking-widest">LES ROIS DU SAHEL</div>
                                <h3 class="text-2xl sm:text-3xl font-black text-white">
                                    {{ $student->last_name }} {{ $student->first_name }}
                                </h3>
                                <div class="flex items-center gap-3 mt-1.5 text-xs text-purple-200/90 font-semibold">
                                    <span>Matricule : <strong class="font-mono text-amber-300">{{ $student->matricule }}</strong></span>
                                    <span>•</span>
                                    <span>Classe : <strong>{{ $enrollment?->schoolClass->name ?? 'Non affecté' }}</strong></span>
                                    <span>•</span>
                                    <span>Cycle : <strong>{{ $enrollment?->schoolClass->level->cycle->name ?? '–' }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Devise Banner -->
                        <div class="bg-white/10 backdrop-blur-md border border-white/20 px-4 py-2 rounded-2xl text-center">
                            <span class="text-[10px] font-black tracking-widest text-amber-300 uppercase">
                                DISCIPLINE • TRAVAIL • SUCCÈS
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Contenu : Notes & Bulletins --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Dernières Notes Obtenues --}}
                    <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-200/80 p-7 space-y-4">
                        <h4 class="text-lg font-black text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-4">
                            📝 Dernières Notes Obtenues
                        </h4>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100 text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-extrabold text-slate-500 uppercase">Matière</th>
                                        <th class="px-4 py-3 text-left text-xs font-extrabold text-slate-500 uppercase">Évaluation</th>
                                        <th class="px-4 py-3 text-center text-xs font-extrabold text-slate-500 uppercase">Note / Max</th>
                                        <th class="px-4 py-3 text-center text-xs font-extrabold text-slate-500 uppercase">Période</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($grades as $grade)
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="px-4 py-3.5 font-bold text-slate-900">
                                                {{ $grade->evaluation->subject->name }}
                                            </td>
                                            <td class="px-4 py-3.5 text-xs text-slate-600 font-semibold">
                                                {{ $grade->evaluation->title }}
                                            </td>
                                            <td class="px-4 py-3.5 text-center">
                                                @if ($grade->is_absent)
                                                    <span class="px-2.5 py-0.5 text-xs font-black rounded-full bg-red-100 text-red-700">Absent</span>
                                                @else
                                                    @php $score = (float)$grade->score; @endphp
                                                    <span class="text-base font-black {{ $score >= 10 ? 'text-emerald-700' : 'text-red-700' }}">
                                                        {{ number_format($score, 2) }}
                                                    </span>
                                                    <span class="text-xs text-slate-400">/ {{ number_format($grade->evaluation->max_score, 0) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3.5 text-center text-xs font-bold text-slate-500">
                                                {{ $grade->evaluation->period->name }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-400 italic">
                                                Aucune note enregistrée.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Bulletins & Résumé --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-7 space-y-4">
                        <h4 class="text-lg font-black text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-4">
                            📄 Bulletins Publiés
                        </h4>

                        <div class="space-y-3">
                            @forelse ($reportCards as $rc)
                                <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/80 hover:border-purple-200 transition flex justify-between items-center">
                                    <div>
                                        <div class="font-extrabold text-slate-900 text-sm">{{ $rc->period->name }}</div>
                                        <div class="text-xs text-slate-500 font-semibold">Rang : {{ $rc->rank }}/{{ $rc->total_students }}</div>
                                    </div>
                                    <div class="text-right">
                                        @php $avg = (float)$rc->general_average; @endphp
                                        <div class="text-xl font-black {{ $avg >= 10 ? 'text-emerald-700' : 'text-red-700' }}">
                                            {{ number_format($avg, 2) }}/20
                                        </div>
                                        <a href="{{ route('report-cards.show', $rc) }}" class="text-xs font-black text-purple-900 hover:underline">
                                            Voir bulletin →
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 italic text-center py-6">Aucun bulletin disponible.</p>
                            @endforelse
                        </div>
                    </div>

                </div>
            @else
                <div class="bg-white rounded-3xl p-8 text-center text-slate-500 border border-slate-200">
                    <p class="text-lg font-black text-slate-800">Aucun profil élève n'est lié à ce compte utilisateur.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
