<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-black text-2xl text-purple-950 leading-tight">
                📋 Feuille de Présence & Appel
            </h2>
            <a href="{{ route('attendances.report') }}" class="px-4 py-2.5 text-xs font-black bg-purple-900 text-white rounded-xl hover:bg-purple-800 transition-all shadow-md">
                📊 Bilan & Rapport des Absences
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            @if (session('status'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-2xl shadow-xs">
                    <strong>Succès ! </strong>{{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-2xl shadow-xs">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Filtres de prise de présence --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6">
                <form method="GET" action="{{ route('attendances.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Classe</label>
                        <select name="class_id" onchange="this.form.submit()" class="w-full text-xs border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 font-bold">
                            @foreach ($classes as $cls)
                                <option value="{{ $cls->id }}" {{ $selectedClassId == $cls->id ? 'selected' : '' }}>
                                    {{ $cls->level->cycle->name }} — {{ $cls->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Date</label>
                        <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                               class="w-full text-xs border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 font-bold" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Séance</label>
                        <select name="session" onchange="this.form.submit()" class="w-full text-xs border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 font-bold">
                            <option value="MATIN" {{ $session === 'MATIN' ? 'selected' : '' }}>Matin (7h30 - 12h)</option>
                            <option value="APRES_MIDI" {{ $session === 'APRES_MIDI' ? 'selected' : '' }}>Après-midi (15h - 18h)</option>
                            <option value="JOURNEE" {{ $session === 'JOURNEE' ? 'selected' : '' }}>Journée entière</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Matière (Optionnel)</label>
                        <select name="subject_id" onchange="this.form.submit()" class="w-full text-xs border-slate-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 font-bold">
                            <option value="">— Appel Général —</option>
                            @foreach ($subjects as $sbj)
                                <option value="{{ $sbj->id }}" {{ $subjectId == $sbj->id ? 'selected' : '' }}>
                                    {{ $sbj->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="w-full px-4 py-2.5 bg-purple-950 text-white text-xs font-black rounded-xl hover:bg-purple-900 transition">
                            🔄 Charger la liste
                        </button>
                    </div>
                </form>
            </div>

            {{-- Formulaire d'appel --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 text-white flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <h3 class="text-xl font-black">
                            Classe : {{ $selectedClass?->name ?? '–' }}
                        </h3>
                        <p class="text-xs text-amber-300 font-semibold mt-0.5">
                            Fiche du {{ \Carbon\Carbon::parse($date)->translatedFormat('l d F Y') }} • Session : {{ $session }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="setAllStatus('PRESENT')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl transition shadow-xs">
                            Tous Présents
                        </button>
                        <button type="button" onclick="setAllStatus('ABSENT')" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-black rounded-xl transition shadow-xs">
                            Tous Absents
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ route('attendances.store') }}">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $selectedClassId }}" />
                    <input type="hidden" name="attendance_date" value="{{ $date }}" />
                    <input type="hidden" name="session" value="{{ $session }}" />
                    <input type="hidden" name="subject_id" value="{{ $subjectId }}" />

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-black text-slate-500 uppercase w-10">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-black text-slate-500 uppercase">Matricule</th>
                                    <th class="px-4 py-3 text-left text-xs font-black text-slate-500 uppercase">Élève</th>
                                    <th class="px-4 py-3 text-center text-xs font-black text-slate-500 uppercase">Statut de Présence</th>
                                    <th class="px-4 py-3 text-left text-xs font-black text-slate-500 uppercase">Motif / Observation</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($students as $i => $student)
                                    @php
                                        $record = $student->attendances->first();
                                        $currentStatus = $record?->status ?? 'PRESENT';
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-4 py-3.5 text-sm text-slate-400 font-bold">{{ $i + 1 }}</td>
                                        <td class="px-4 py-3.5 text-xs font-mono font-black text-purple-900">
                                            {{ $student->matricule }}
                                            <input type="hidden" name="attendances[{{ $i }}][student_id]" value="{{ $student->id }}" />
                                        </td>
                                        <td class="px-4 py-3.5 text-sm font-extrabold text-slate-900">
                                            {{ $student->last_name }} {{ $student->first_name }}
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <div class="inline-flex rounded-xl p-1 bg-slate-100 gap-1 border border-slate-200/60">
                                                <label class="px-3 py-1 rounded-lg text-xs font-extrabold cursor-pointer transition-all has-[:checked]:bg-emerald-600 has-[:checked]:text-white text-slate-600 hover:text-slate-900">
                                                    <input type="radio" name="attendances[{{ $i }}][status]" value="PRESENT"
                                                           class="sr-only status-radio" {{ $currentStatus === 'PRESENT' ? 'checked' : '' }} />
                                                    Présent
                                                </label>
                                                <label class="px-3 py-1 rounded-lg text-xs font-extrabold cursor-pointer transition-all has-[:checked]:bg-red-600 has-[:checked]:text-white text-slate-600 hover:text-slate-900">
                                                    <input type="radio" name="attendances[{{ $i }}][status]" value="ABSENT"
                                                           class="sr-only status-radio" {{ $currentStatus === 'ABSENT' ? 'checked' : '' }} />
                                                    Absent
                                                </label>
                                                <label class="px-3 py-1 rounded-lg text-xs font-extrabold cursor-pointer transition-all has-[:checked]:bg-amber-500 has-[:checked]:text-white text-slate-600 hover:text-slate-900">
                                                    <input type="radio" name="attendances[{{ $i }}][status]" value="RETARD"
                                                           class="sr-only status-radio" {{ $currentStatus === 'RETARD' ? 'checked' : '' }} />
                                                    Retard
                                                </label>
                                                <label class="px-3 py-1 rounded-lg text-xs font-extrabold cursor-pointer transition-all has-[:checked]:bg-blue-600 has-[:checked]:text-white text-slate-600 hover:text-slate-900">
                                                    <input type="radio" name="attendances[{{ $i }}][status]" value="EXCUSE"
                                                           class="sr-only status-radio" {{ $currentStatus === 'EXCUSE' ? 'checked' : '' }} />
                                                    Excusé
                                                </label>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <input type="text" name="attendances[{{ $i }}][reason]" value="{{ $record?->reason ?? '' }}"
                                                   placeholder="Raison médicale, voyage…"
                                                   class="w-full text-xs border-slate-300 rounded-xl focus:ring-purple-500 focus:border-purple-500" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400 italic">
                                            Aucun élève inscrit dans cette classe.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($students->isNotEmpty())
                        <div class="p-5 bg-slate-50 border-t border-slate-100 flex justify-end">
                            <x-primary-button class="bg-purple-900 hover:bg-purple-800 shadow-md">
                                💾 Enregistrer la Feuille d'Appel
                            </x-primary-button>
                        </div>
                    @endif
                </form>
            </div>

        </div>
    </div>

    <script>
        function setAllStatus(status) {
            document.querySelectorAll(`.status-radio[value="${status}"]`).forEach(radio => {
                radio.checked = true;
                radio.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }
    </script>
</x-app-layout>
