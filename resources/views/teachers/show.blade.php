<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('teachers.index') }}" class="text-xs font-black text-purple-900 hover:underline">
                        ← Répertoire
                    </a>
                    @php
                        $statusColors = [
                            'ACTIF'   => 'bg-emerald-100 text-emerald-800',
                            'INACTIF' => 'bg-red-100 text-red-800',
                            'CONGE'   => 'bg-orange-100 text-orange-800',
                        ];
                        $statusColor = $statusColors[$teacher->status] ?? 'bg-slate-100 text-slate-700';
                    @endphp
                    <span class="text-xs font-black px-2.5 py-1 rounded-lg {{ $statusColor }}">
                        {{ $teacher->status }}
                    </span>
                </div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                    <span>👨‍🏫</span> {{ $teacher->user->name }}
                </h2>
                <p class="text-xs text-slate-500 font-semibold mt-0.5">
                    Matricule : <span class="font-mono font-black text-indigo-700">{{ $teacher->matricule }}</span>
                    &bull; {{ $teacher->speciality }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('teachers.edit', $teacher) }}"
                   class="px-4 py-2 text-xs font-black bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition shadow-md flex items-center gap-1.5">
                    ✏️ Modifier le Profil
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-8 animate-fade-in-up">

            {{-- Notifications --}}
            @if(session('status'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm font-bold flex items-center gap-2 shadow-xs">
                    <span>✅</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Stat Cards --}}
            @php
                $assignCount   = $teacher->assignments->count();
                $scheduleCount = $teacher->schedules->count();
                $classesSet    = $teacher->assignments->pluck('class_id')->unique()->count();
                $subjectsSet   = $teacher->assignments->pluck('subject_id')->unique()->count();
                $contractColors = [
                    'PERMANENT'   => 'bg-indigo-100 text-indigo-800',
                    'VACATAIRE'   => 'bg-amber-100 text-amber-800',
                    'CONTRACTUEL' => 'bg-sky-100 text-sky-800',
                ];
                $contractColor = $contractColors[$teacher->employment_type] ?? 'bg-slate-100 text-slate-700';
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-md">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-purple-600 mb-1">Classe(s)</div>
                    <div class="text-3xl font-black text-purple-900">{{ $classesSet }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">Classe(s) titulaire(s)</div>
                </div>
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-md">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-indigo-600 mb-1">Matières / Cours</div>
                    <div class="text-3xl font-black text-indigo-700">{{ $assignCount }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">Total cours affectés ({{ $subjectsSet }} disc.)</div>
                </div>
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-md">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-amber-600 mb-1">Créneaux</div>
                    <div class="text-3xl font-black text-amber-600">{{ $scheduleCount }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">Heures programmées</div>
                </div>
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-md">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-1">Contrat</div>
                    <div class="text-lg font-black mt-1">
                        <span class="px-2.5 py-1 rounded-xl text-xs font-black {{ $contractColor }}">
                            {{ $teacher->employment_type }}
                        </span>
                    </div>
                    @if($teacher->hire_date)
                        <div class="text-xs text-slate-500 font-bold mt-2">
                            Depuis {{ \Carbon\Carbon::parse($teacher->hire_date)->format('d/m/Y') }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Fiche d'identité --}}
            <div class="bg-white shadow-xl rounded-3xl border border-slate-200/80 overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-purple-900 text-white">
                    <h3 class="text-base font-black uppercase tracking-wider flex items-center gap-2">
                        <span>🪪</span> Fiche Professionnelle
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Nom Complet</div>
                            <div class="text-sm font-black text-slate-900">{{ $teacher->user->name }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Email</div>
                            <div class="text-sm font-bold text-slate-700">{{ $teacher->user->email }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Téléphone (Login)</div>
                            <div class="text-sm font-bold text-slate-700">{{ $teacher->user->phone ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Matricule</div>
                            <div class="font-mono text-sm font-black text-indigo-700">{{ $teacher->matricule }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Spécialité</div>
                            <div class="text-sm font-bold text-slate-900">{{ $teacher->speciality }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Qualification</div>
                            <div class="text-sm font-bold text-slate-700">{{ $teacher->qualification ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Date de Recrutement</div>
                            <div class="text-sm font-bold text-slate-700">
                                {{ $teacher->hire_date ? \Carbon\Carbon::parse($teacher->hire_date)->format('d/m/Y') : '—' }}
                            </div>
                        </div>
                        <div class="md:col-span-2 lg:col-span-3 pt-3 border-t border-slate-100">
                            <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">Cycle(s) & Niveaux d'Enseignement Autorisés</div>
                            <div class="flex flex-wrap items-center gap-2">
                                @if($teacher->cycles->isNotEmpty())
                                    @foreach($teacher->cycles as $cyc)
                                        <span class="px-2.5 py-1 rounded-xl bg-purple-100 text-purple-950 font-black text-xs border border-purple-200">
                                            🏛️ {{ $cyc->name }}
                                        </span>
                                    @endforeach
                                @elseif($teacher->cycle)
                                    <span class="px-2.5 py-1 rounded-xl bg-purple-100 text-purple-950 font-black text-xs border border-purple-200">
                                        🏛️ {{ $teacher->cycle->name }}
                                    </span>
                                @endif

                                @if($teacher->levels->isNotEmpty())
                                    @foreach($teacher->levels as $lvl)
                                        <span class="px-2.5 py-1 rounded-xl bg-amber-50 text-amber-900 font-bold text-xs border border-amber-200">
                                            📖 {{ $lvl->name }}
                                        </span>
                                    @endforeach
                                @elseif($teacher->is_polyvalent)
                                    <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-800 font-bold text-xs border border-emerald-200">
                                        🌟 Enseignant Polyvalent (Toutes les matières du niveau assigné)
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 font-semibold italic">Tous les niveaux du cycle</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grille 2 colonnes : Affectations + Emploi du temps --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Classes & Matières Affectées --}}
                <div class="bg-white shadow-xl rounded-3xl border border-slate-200/80 overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-indigo-50/60">
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <span>🏫</span> Classes & Matières Affectées
                            </h3>
                            <p class="text-xs text-slate-500 font-semibold mt-0.5">{{ $assignCount }} affectation(s) cette année</p>
                        </div>
                        <a href="{{ route('assignments.index') }}"
                           class="text-[11px] font-black text-indigo-700 hover:underline px-2 py-1">
                            Gérer →
                        </a>
                    </div>
                    <div class="p-5 space-y-2">
                        @forelse ($teacher->assignments as $asn)
                            <div class="flex items-center justify-between px-3 py-2.5 bg-slate-50 rounded-xl border border-slate-200 hover:border-indigo-200 transition">
                                <div>
                                    <div class="font-black text-slate-900 text-xs">{{ $asn->schoolClass->name }}</div>
                                    <div class="text-[10px] text-slate-500 font-semibold">
                                        {{ $asn->schoolClass->level->cycle->name ?? '' }} &bull; {{ $asn->schoolClass->level->name ?? '' }}
                                    </div>
                                </div>
                                <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-purple-100 text-purple-800">
                                    {{ $asn->subject->name }}
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-8 text-xs text-slate-400 font-bold italic">
                                Aucune affectation enregistrée pour le moment.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Emploi du Temps --}}
                <div class="bg-white shadow-xl rounded-3xl border border-slate-200/80 overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-amber-50/60">
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <span>📅</span> Créneaux Hebdomadaires
                            </h3>
                            <p class="text-xs text-slate-500 font-semibold mt-0.5">{{ $scheduleCount }} créneau(x) programmé(s)</p>
                        </div>
                        <a href="{{ route('schedules.index') }}"
                           class="text-[11px] font-black text-amber-700 hover:underline px-2 py-1">
                            Emploi du temps →
                        </a>
                    </div>
                    <div class="p-5 space-y-2">
                        @php
                            $dayOrder = ['Lundi' => 1, 'Mardi' => 2, 'Mercredi' => 3, 'Jeudi' => 4, 'Vendredi' => 5, 'Samedi' => 6];
                            $sortedSchedules = $teacher->schedules->sortBy([
                                fn($s) => $dayOrder[$s->day_of_week] ?? 7,
                                fn($s) => $s->start_time,
                            ]);
                        @endphp
                        @forelse ($sortedSchedules as $sch)
                            <div class="flex items-center justify-between px-3 py-2.5 bg-slate-50 rounded-xl border border-slate-200 hover:border-amber-200 transition">
                                <div>
                                    <div class="font-black text-slate-900 text-xs flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[10px] font-black">
                                            {{ $sch->day_of_week }}
                                        </span>
                                        {{ substr($sch->start_time, 0, 5) }} – {{ substr($sch->end_time, 0, 5) }}
                                    </div>
                                    <div class="text-[10px] text-slate-500 font-semibold mt-0.5">
                                        {{ $sch->schoolClass->name ?? '—' }} &bull; {{ $sch->subject->name ?? '—' }}
                                    </div>
                                </div>
                                @if($sch->room_number)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-slate-200 text-slate-700">
                                        Salle {{ $sch->room_number }}
                                    </span>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-8 text-xs text-slate-400 font-bold italic">
                                Aucun créneau d'emploi du temps défini.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
