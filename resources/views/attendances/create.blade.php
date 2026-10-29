<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <div>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-0.5">
                    {{ $selectedClass?->level->cycle->name ?? '–' }} · {{ $activeYear?->name ?? '–' }}
                </p>
                <h2 class="font-black text-2xl text-purple-950 leading-tight">
                    {{ request()->routeIs('attendances.edit') ? 'Corriger la Feuille d\'Appel' : 'Nouvelle Feuille d\'Appel' }}
                </h2>
            </div>
            <a href="{{ route('attendances.show', ['class_id' => $selectedClassId]) }}"
               class="px-4 py-2.5 text-xs font-black bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition shadow-md">
                ← Retour
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-6 animate-fade-in-up">

            @if (session('status'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-2xl">
                    <strong>Succès !</strong> {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-2xl">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Filtres --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6">
                <h3 class="text-sm font-black text-purple-950 mb-4">Paramètres de la feuille</h3>
                <form method="GET"
                      action="{{ request()->routeIs('attendances.edit') ? route('attendances.edit') : route('attendances.create') }}"
                      data-no-confirm="true"
                      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 items-end">

                    {{-- Classe fixée, non modifiable --}}
                    <input type="hidden" name="class_id" value="{{ $selectedClassId }}" />
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Classe</label>
                        <div class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2.5 bg-slate-50 font-black text-slate-700">
                            {{ $selectedClass?->level->cycle->name }} — {{ $selectedClass?->name }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Date</label>
                        <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                               class="w-full text-xs border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 font-bold" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Matière <span class="font-semibold text-slate-400">(optionnel)</span></label>
                        <select name="subject_id" onchange="this.form.submit()"
                                class="w-full text-xs border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500 font-bold">
                            <option value="">— Appel général —</option>
                            @foreach ($subjects as $sbj)
                                <option value="{{ $sbj->id }}" {{ $subjectId == $sbj->id ? 'selected' : '' }}>
                                    {{ $sbj->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            {{-- Tableau d'appel --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">

                {{-- En-tête du tableau --}}
                <div class="px-6 py-5 bg-purple-950 flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <h3 class="text-lg font-black text-white">{{ $selectedClass?->name ?? '–' }}</h3>
                        <p class="text-xs text-purple-300 font-semibold mt-0.5">
                            {{ \Carbon\Carbon::parse($date)->translatedFormat('l d F Y') }}
                            @if ($subjectId)
                                · {{ $subjects->firstWhere('id', $subjectId)?->name }}
                            @else
                                · Appel général
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="setAllStatus('PRESENT')"
                                class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-400 text-white text-xs font-black rounded-xl transition">
                            Tous présents
                        </button>
                        <button type="button" onclick="setAllStatus('ABSENT')"
                                class="px-3 py-1.5 bg-red-500 hover:bg-red-400 text-white text-xs font-black rounded-xl transition">
                            Tous absents
                        </button>
                    </div>
                </div>

                <form method="POST"
                      action="{{ request()->routeIs('attendances.edit') ? route('attendances.update') : route('attendances.store') }}"
                      data-confirm="{{ request()->routeIs('attendances.edit') ? 'Écraser la feuille existante avec ces nouvelles valeurs ?' : 'Enregistrer cette feuille d\'appel ?' }}">
                    @csrf
                    @if (request()->routeIs('attendances.edit')) @method('PUT') @endif
                    <input type="hidden" name="class_id"        value="{{ $selectedClassId }}" />
                    <input type="hidden" name="attendance_date" value="{{ $date }}" />
                    <input type="hidden" name="subject_id"      value="{{ $subjectId }}" />

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-black text-slate-500 uppercase w-10">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-black text-slate-500 uppercase">Matricule</th>
                                    <th class="px-4 py-3 text-left text-xs font-black text-slate-500 uppercase">Élève</th>
                                    <th class="px-4 py-3 text-center text-xs font-black text-slate-500 uppercase">Statut</th>
                                    <th class="px-4 py-3 text-left text-xs font-black text-slate-500 uppercase">Motif / Observation</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($students as $i => $student)
                                    @php $record = $student->attendances->first(); $currentStatus = $record?->status ?? 'PRESENT'; @endphp
                                    <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50/40' }} hover:bg-purple-50/30 transition">
                                        <td class="px-4 py-3.5 text-sm text-slate-400 font-bold">{{ $i + 1 }}</td>
                                        <td class="px-4 py-3.5 text-xs font-mono font-black text-purple-900">
                                            {{ $student->matricule }}
                                            <input type="hidden" name="attendances[{{ $i }}][student_id]" value="{{ $student->id }}" />
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
                                                    <span class="text-[10px] font-black text-purple-800">{{ mb_strtoupper(mb_substr($student->last_name, 0, 1)) }}</span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-black text-slate-900">{{ $student->last_name }}</div>
                                                    <div class="text-[11px] text-slate-400 font-semibold">{{ $student->first_name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <div class="inline-flex rounded-xl p-1 bg-slate-100 gap-1">
                                                <label class="px-3 py-1 rounded-lg text-xs font-black cursor-pointer transition-all has-[:checked]:bg-emerald-500 has-[:checked]:text-white text-slate-500 hover:text-slate-800">
                                                    <input type="radio" name="attendances[{{ $i }}][status]" value="PRESENT" class="sr-only status-radio" {{ $currentStatus === 'PRESENT' ? 'checked' : '' }} />
                                                    Présent
                                                </label>
                                                <label class="px-3 py-1 rounded-lg text-xs font-black cursor-pointer transition-all has-[:checked]:bg-red-500 has-[:checked]:text-white text-slate-500 hover:text-slate-800">
                                                    <input type="radio" name="attendances[{{ $i }}][status]" value="ABSENT" class="sr-only status-radio" {{ $currentStatus === 'ABSENT' ? 'checked' : '' }} />
                                                    Absent
                                                </label>
                                                <label class="px-3 py-1 rounded-lg text-xs font-black cursor-pointer transition-all has-[:checked]:bg-amber-500 has-[:checked]:text-white text-slate-500 hover:text-slate-800">
                                                    <input type="radio" name="attendances[{{ $i }}][status]" value="RETARD" class="sr-only status-radio" {{ $currentStatus === 'RETARD' ? 'checked' : '' }} />
                                                    Retard
                                                </label>
                                                <label class="px-3 py-1 rounded-lg text-xs font-black cursor-pointer transition-all has-[:checked]:bg-blue-500 has-[:checked]:text-white text-slate-500 hover:text-slate-800">
                                                    <input type="radio" name="attendances[{{ $i }}][status]" value="EXCUSE" class="sr-only status-radio" {{ $currentStatus === 'EXCUSE' ? 'checked' : '' }} />
                                                    Excusé
                                                </label>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <input type="text" name="attendances[{{ $i }}][reason]" value="{{ $record?->reason ?? '' }}"
                                                   placeholder="Raison médicale, voyage…"
                                                   class="w-full text-xs border-purple-300 rounded-xl focus:ring-purple-500 focus:border-purple-500" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-12 text-center text-sm text-slate-400 italic">
                                            Aucun élève inscrit (statut VALIDE) dans cette classe.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($students->isNotEmpty())
                        <div class="p-5 bg-slate-50 border-t border-slate-100 flex flex-wrap justify-between items-center gap-3">
                            <span class="text-xs text-slate-400 font-semibold">{{ $students->count() }} élève(s)</span>
                            <button type="submit"
                                    class="px-6 py-2.5 bg-purple-950 hover:bg-purple-900 text-white text-xs font-black rounded-xl transition shadow-md">
                                {{ request()->routeIs('attendances.edit') ? 'Mettre à jour la Feuille' : 'Enregistrer la Feuille d\'Appel' }}
                            </button>
                        </div>
                    @endif
                </form>
            </div>

        </div>
    </div>

    <script>
        function setAllStatus(status) {
            document.querySelectorAll(`.status-radio[value="${status}"]`).forEach(r => r.checked = true);
        }
    </script>
</x-app-layout>
