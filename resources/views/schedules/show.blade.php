<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-1 bg-purple-100 text-purple-900 rounded-lg text-xs font-black uppercase">
                        {{ $schedule->day_of_week }}
                    </span>
                    <span class="px-2.5 py-1 bg-amber-100 text-amber-900 rounded-lg text-xs font-black">
                        {{ substr($schedule->start_time, 0, 5) }} – {{ substr($schedule->end_time, 0, 5) }}
                    </span>
                    @php
                        $start = \Carbon\Carbon::parse($schedule->start_time);
                        $end = \Carbon\Carbon::parse($schedule->end_time);
                        $durationMinutes = $end->diffInMinutes($start);
                        $hours = intdiv($durationMinutes, 60);
                        $mins = $durationMinutes % 60;
                        $durationText = ($hours > 0 ? $hours . 'h' : '') . ($mins > 0 ? sprintf('%02d', $mins) : ($hours > 0 ? '00' : ''));
                    @endphp
                    <span class="px-2.5 py-1 bg-indigo-100 text-indigo-900 rounded-lg text-xs font-black">
                        ⏱️ Durée : {{ $durationText }}
                    </span>
                    @if ($schedule->room_number)
                        <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-black">
                            📍 {{ $schedule->room_number }}
                        </span>
                    @endif
                </div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight mt-2">
                    {{ $schedule->subject?->name }} — {{ $schedule->schoolClass?->name }}
                </h2>
            </div>
            <div class="flex items-center gap-3 no-print">
                <a href="{{ route('schedules.edit', $schedule) }}" 
                   class="px-4 py-2 text-xs font-black bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition shadow-sm flex items-center gap-1.5">
                    ✏️ Modifier
                </a>
                <form method="POST" action="{{ route('schedules.destroy', $schedule) }}" 
                      data-confirm="Êtes-vous certain de vouloir supprimer définitivement ce créneau ?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 text-xs font-black bg-rose-600 hover:bg-rose-700 text-white rounded-xl transition shadow-sm flex items-center gap-1.5">
                        🗑️ Supprimer
                    </button>
                </form>
                <a href="{{ route('schedules.index', ['class_id' => $schedule->class_id]) }}" 
                   class="px-4 py-2 text-xs font-black bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">
                    ← Emploi du Temps ({{ $schedule->schoolClass?->name }})
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            {{-- Alertes --}}
            @if (session('status'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-xl text-xs font-black text-emerald-900 shadow-xs">
                    ✅ {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-xl shadow-xs">
                    <ul class="text-xs font-bold text-rose-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Colonne 1 & 2 : Informations principales du créneau --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Fiche Principale --}}
                    <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                        <div class="p-8 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-amber-400 uppercase tracking-widest">
                                    {{ $schedule->schoolClass?->level?->cycle?->name ?? 'Cycle' }} • {{ $schedule->schoolClass?->level?->name ?? 'Niveau' }}
                                </span>
                                <span class="text-xs font-bold text-purple-200">
                                    Année : {{ $schedule->academicYear?->name ?? 'Active' }}
                                </span>
                            </div>
                            <h1 class="text-3xl font-black text-white mt-2">
                                {{ $schedule->subject?->name }}
                            </h1>
                            <p class="text-purple-200 text-sm mt-1 font-medium">
                                Classe : <strong class="text-white">{{ $schedule->schoolClass?->name }}</strong>
                            </p>
                        </div>

                        <div class="p-8 grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50/50">
                            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-wider block">👨‍🏫 Enseignant Responsable</span>
                                <div class="text-base font-black text-purple-950 mt-1">
                                    {{ $schedule->teacher?->user?->name ?? 'Non assigné' }}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Matricule : <strong class="text-slate-700">{{ $schedule->teacher?->matricule ?? 'N/A' }}</strong>
                                </div>
                                @if ($schedule->teacher?->user?->email)
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        Email : {{ $schedule->teacher->user->email }}
                                    </div>
                                @endif
                                @if ($schedule->teacher?->speciality)
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        Spécialité : {{ $schedule->teacher->speciality }}
                                    </div>
                                @endif
                            </div>

                            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-wider block">📚 Informations Pédagogiques</span>
                                <div class="text-base font-black text-purple-950 mt-1">
                                    Code : {{ $schedule->subject?->code ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Jour : <strong class="text-slate-800">{{ $schedule->day_of_week }}</strong>
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Horaires : <strong class="text-slate-800">{{ substr($schedule->start_time, 0, 5) }} à {{ substr($schedule->end_time, 0, 5) }}</strong> ({{ $durationText }})
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Salle de cours : <strong class="text-purple-900">{{ $schedule->room_number ?? 'Non définie' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Autres créneaux de cette matière dans la semaine pour cette classe --}}
                    <div class="bg-white shadow-xl rounded-3xl p-6 border border-slate-200/80">
                        <h3 class="text-sm font-black text-purple-950 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span>📅</span> Répartition hebdomadaire de {{ $schedule->subject?->name }} pour {{ $schedule->schoolClass?->name }}
                        </h3>

                        @if ($subjectWeeklySlots->count() > 1)
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach ($subjectWeeklySlots as $slot)
                                    <div class="p-3.5 rounded-2xl border {{ $slot->id === $schedule->id ? 'bg-purple-900 text-white border-purple-900 shadow-md' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-purple-50' }} transition">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-black {{ $slot->id === $schedule->id ? 'text-amber-400' : 'text-purple-900' }}">
                                                {{ $slot->day_of_week }}
                                            </span>
                                            @if ($slot->id === $schedule->id)
                                                <span class="text-[10px] bg-amber-400 text-purple-950 font-black px-1.5 py-0.5 rounded">Actuel</span>
                                            @endif
                                        </div>
                                        <div class="text-xs font-extrabold mt-1">
                                            {{ substr($slot->start_time, 0, 5) }} – {{ substr($slot->end_time, 0, 5) }}
                                        </div>
                                        @if ($slot->room_number)
                                            <div class="text-[11px] opacity-80 mt-0.5">
                                                Salle : {{ $slot->room_number }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-500 font-bold">
                                Il s'agit du seul créneau de {{ $schedule->subject?->name }} planifié cette semaine pour cette classe.
                            </p>
                        @endif
                    </div>

                </div>

                {{-- Colonne 3 : Actions Rapides & Duplication --}}
                <div class="space-y-6">

                    {{-- Formulaire de Duplication Rapide --}}
                    <div class="bg-white shadow-xl rounded-3xl p-6 border border-slate-200/80">
                        <h3 class="text-sm font-black text-purple-950 uppercase tracking-wider mb-2 flex items-center gap-2">
                            <span>📋</span> Dupliquer ce créneau
                        </h3>
                        <p class="text-xs text-slate-500 font-medium mb-4">
                            Ajoutez rapidement ce cours à un autre jour de la semaine sans ressaisir les informations.
                        </p>

                        <form method="POST" action="{{ route('schedules.duplicate', $schedule) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Jour Cible</label>
                                <select name="target_day" required class="w-full text-xs font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                                    @foreach (['LUNDI' => 'Lundi', 'MARDI' => 'Mardi', 'MERCREDI' => 'Mercredi', 'JEUDI' => 'Jeudi', 'VENDREDI' => 'Vendredi', 'SAMEDI' => 'Samedi'] as $val => $label)
                                        <option value="{{ $val }}" {{ $schedule->day_of_week === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Début</label>
                                    <input type="time" name="start_time" required value="{{ substr($schedule->start_time, 0, 5) }}"
                                           class="w-full text-xs font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Fin</label>
                                    <input type="time" name="end_time" required value="{{ substr($schedule->end_time, 0, 5) }}"
                                           class="w-full text-xs font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500" />
                                </div>
                            </div>

                            <button type="submit" class="w-full py-2.5 px-4 bg-purple-900 hover:bg-purple-950 text-white font-black text-xs rounded-xl transition shadow-md flex items-center justify-center gap-2">
                                <span>➕</span> Dupliquer le Créneau
                            </button>
                        </form>
                    </div>

                    {{-- Autres cours de l'enseignant --}}
                    @if ($schedule->teacher)
                        <div class="bg-white shadow-xl rounded-3xl p-6 border border-slate-200/80">
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-3">
                                👨‍🏫 Charge de {{ $schedule->teacher->user?->name }}
                            </h3>
                            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                @forelse ($teacherWeeklySlots as $tSlot)
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                                        <div>
                                            <span class="font-black text-purple-950">{{ $tSlot->day_of_week }}</span>
                                            <span class="text-slate-500">({{ substr($tSlot->start_time, 0, 5) }}-{{ substr($tSlot->end_time, 0, 5) }})</span>
                                            <div class="text-[11px] font-bold text-slate-700">{{ $tSlot->schoolClass?->name }}</div>
                                        </div>
                                        <a href="{{ route('schedules.show', $tSlot) }}" class="text-[11px] font-black text-purple-900 hover:underline">
                                            Voir
                                        </a>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-500">Aucun autre créneau planifié.</p>
                                @endforelse
                            </div>
                        </div>
                    @endif

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
