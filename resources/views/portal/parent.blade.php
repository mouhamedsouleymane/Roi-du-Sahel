<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight">
                    👨‍👩‍👧‍👦 Espace Parents — Suivi Scolaire
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Consultation officielle des résultats, présences et factures de vos enfants
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs">
                👑 Année active : {{ $activeYear?->name ?? '–' }}
            </span>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            {{-- Banner Devise Officielle --}}
            <div class="bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white rounded-3xl p-6 shadow-xl border border-purple-800/40 flex flex-wrap justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-400 to-purple-600 flex items-center justify-center text-2xl shadow-lg ring-2 ring-amber-400/40">
                        👑
                    </div>
                    <div>
                        <div class="text-xs font-black text-amber-400 uppercase tracking-widest">Complexe Scolaire Privé</div>
                        <div class="text-xl font-black text-white">LES ROIS DU SAHEL</div>
                    </div>
                </div>
                <div class="px-4 py-1.5 bg-amber-400/20 border border-amber-400/40 rounded-full text-xs font-black text-amber-300 tracking-widest uppercase">
                    DISCIPLINE • TRAVAIL • SUCCÈS
                </div>
            </div>

            {{-- Sélection de l'enfant --}}
            @if ($children->count() > 1)
                <div class="flex items-center gap-3 bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80">
                    <span class="text-xs font-black text-slate-700">Sélectionner un enfant :</span>
                    <div class="flex gap-2">
                        @foreach ($children as $child)
                            <a href="{{ route('portal.parent', ['student_id' => $child->id]) }}"
                               class="px-4 py-2 text-xs font-black rounded-xl transition-all duration-300 {{ $selectedStudent?->id == $child->id ? 'bg-gradient-to-r from-amber-500 to-purple-800 text-white shadow-md ring-2 ring-amber-400/50' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                🎓 {{ $child->first_name }} {{ $child->last_name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($selectedStudent)
                {{-- Fiche de l'enfant sélectionné --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 flex flex-wrap justify-between items-center gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-purple-800 to-indigo-900 text-amber-400 font-black text-2xl flex items-center justify-center flex-shrink-0 shadow-md ring-2 ring-amber-400/30">
                            {{ mb_substr($selectedStudent->first_name, 0, 1) }}{{ mb_substr($selectedStudent->last_name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-900">
                                {{ $selectedStudent->last_name }} {{ $selectedStudent->first_name }}
                            </h3>
                            <div class="flex items-center gap-3 mt-1 text-xs text-slate-500 font-semibold">
                                <span class="font-mono font-black text-purple-900 bg-purple-50 px-2 py-0.5 rounded-md">Matricule : {{ $selectedStudent->matricule }}</span>
                                <span>•</span>
                                <span>Classe : <strong class="text-slate-800">{{ $selectedStudent->enrollments->first()?->schoolClass->name ?? 'Non inscrit' }}</strong></span>
                                <span>•</span>
                                <span>Cycle : <strong class="text-slate-800">{{ $selectedStudent->enrollments->first()?->schoolClass->level->cycle->name ?? '–' }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Grille de suivi (Bulletins, Absences, Factures) --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Bulletins Publiés --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 space-y-4">
                        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                            <h4 class="text-base font-black text-slate-900 flex items-center gap-2">
                                📄 Bulletins Trimestriels
                            </h4>
                            <span class="text-xs text-amber-600 font-extrabold bg-amber-50 px-2 py-0.5 rounded-full">Officiels</span>
                        </div>

                        @forelse ($reportCards as $rc)
                            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/80 hover:border-purple-200 transition-all flex justify-between items-center">
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
                            <p class="text-xs text-slate-400 italic text-center py-6">Aucun bulletin officiel disponible.</p>
                        @endforelse
                    </div>

                    {{-- Présence & Absences --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 space-y-4">
                        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                            <h4 class="text-base font-black text-slate-900 flex items-center gap-2">
                                📋 Suivi des Présences
                            </h4>
                            <span class="text-xs text-slate-400 font-semibold">Derniers événements</span>
                        </div>

                        @php
                            $absCount    = $attendances->where('status', 'ABSENT')->count();
                            $retardCount = $attendances->where('status', 'RETARD')->count();
                        @endphp

                        <div class="grid grid-cols-2 gap-3 mb-2">
                            <div class="p-3 bg-red-50 rounded-2xl text-center border border-red-100">
                                <div class="text-2xl font-black text-red-700">{{ $absCount }}</div>
                                <div class="text-[11px] text-red-600 font-black">Absence(s)</div>
                            </div>
                            <div class="p-3 bg-amber-50 rounded-2xl text-center border border-amber-100">
                                <div class="text-2xl font-black text-amber-700">{{ $retardCount }}</div>
                                <div class="text-[11px] text-amber-600 font-black">Retard(s)</div>
                            </div>
                        </div>

                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            @forelse ($attendances as $att)
                                <div class="flex justify-between items-center p-3 rounded-xl bg-slate-50 text-xs border border-slate-100">
                                    <div>
                                        <div class="font-extrabold text-slate-800">{{ $att->attendance_date->format('d/m/Y') }} — {{ $att->session }}</div>
                                        <div class="text-slate-400 text-[11px] font-semibold">{{ $att->subject?->name ?? 'Appel général' }}</div>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full font-black
                                        {{ $att->status === 'PRESENT' ? 'bg-emerald-100 text-emerald-800' : ($att->status === 'ABSENT' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $att->status }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 italic text-center py-4">Aucune absence signalée.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Situation Financière --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 space-y-4">
                        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                            <h4 class="text-base font-black text-slate-900 flex items-center gap-2">
                                💳 Situation Financière
                            </h4>
                            <span class="text-xs text-slate-400 font-semibold">Factures</span>
                        </div>

                        @forelse ($invoices as $inv)
                            @php $remaining = (float)$inv->amount_due - (float)$inv->amount_paid; @endphp
                            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/80 space-y-2">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="text-sm font-extrabold text-slate-900">{{ $inv->feeType->name }}</div>
                                        <div class="text-xs text-purple-900 font-mono font-bold">{{ $inv->invoice_number }}</div>
                                    </div>
                                    <span class="px-2.5 py-0.5 text-xs font-black rounded-full
                                        {{ $inv->status === 'PAYEE' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $inv->status }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-xs pt-2 border-t border-slate-200/70 font-semibold">
                                    <span class="text-slate-600">Montant dû : <strong>{{ number_format($inv->amount_due, 0, ',', ' ') }} FCFA</strong></span>
                                    <span class="{{ $remaining > 0 ? 'text-red-600 font-black' : 'text-emerald-600 font-black' }}">
                                        Reste : {{ number_format($remaining, 0, ',', ' ') }} FCFA
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic text-center py-6">Aucune facture enregistrée.</p>
                        @endforelse
                    </div>

                </div>
            @else
                <div class="bg-white rounded-3xl p-8 text-center text-slate-500 border border-slate-200">
                    <p class="text-lg font-black text-slate-800">Aucun enfant n'est actuellement lié à votre compte parent.</p>
                    <p class="text-sm text-slate-400 mt-1">Veuillez contacter l'administration du Complexe Scolaire Les Rois du Sahel.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
