<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                    <span>👨‍🏫</span> Corps Professoral
                </h2>
                <p class="text-xs text-slate-500 font-bold mt-1">
                    Répertoire et affectations des enseignants ventilés par <span
                        class="text-purple-950 font-extrabold">Cycle d'Enseignement</span>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('teachers.create') }}"
                    class="px-4 py-2.5 text-xs font-black bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 text-purple-950 rounded-2xl transition transform hover:scale-105 shadow-md flex items-center gap-1.5">
                    <span>➕</span>
                    <span>Ajouter un Enseignant</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8 animate-fade-in-up">

            {{-- Notifications --}}
            @if (session('status'))
                <div
                    class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm font-bold flex items-center gap-2 shadow-xs">
                    <span>✅</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div
                    class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-2xl text-sm font-bold flex items-center gap-2 shadow-xs">
                    <span>⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- 4 Cartes KPIs Globales --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div
                    class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-md hover:-translate-y-1 transition duration-300">
                    <div class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1">Total Enseignants
                    </div>
                    <div class="text-2xl sm:text-3xl font-black text-purple-950">{{ $totalTeachers }}</div>
                    <div class="text-[11px] text-slate-500 font-bold mt-1">Corps professoral global</div>
                </div>
                <div
                    class="bg-white p-5 rounded-3xl border border-emerald-200/80 shadow-md hover:-translate-y-1 transition duration-300">
                    <div class="text-[11px] font-black uppercase tracking-wider text-emerald-600 mb-1">En Activité</div>
                    <div class="text-2xl sm:text-3xl font-black text-emerald-700">{{ $activeTeachersCount }}</div>
                    <div class="text-[11px] text-slate-500 font-bold mt-1">Statut ACTIF</div>
                </div>
                <div
                    class="bg-white p-5 rounded-3xl border border-indigo-200/80 shadow-md hover:-translate-y-1 transition duration-300">
                    <div class="text-[11px] font-black uppercase tracking-wider text-indigo-600 mb-1">Titulaires /
                        Permanents</div>
                    <div class="text-2xl sm:text-3xl font-black text-indigo-700">{{ $permanentTeachersCount }}</div>
                    <div class="text-[11px] text-slate-500 font-bold mt-1">Contrats permanents</div>
                </div>
                <div
                    class="bg-white p-5 rounded-3xl border border-amber-200/80 shadow-md hover:-translate-y-1 transition duration-300">
                    <div class="text-[11px] font-black uppercase tracking-wider text-amber-600 mb-1">Affectations</div>
                    <div class="text-2xl sm:text-3xl font-black text-amber-900">{{ $totalAssignmentsCount }}</div>
                    <div class="text-[11px] text-slate-500 font-bold mt-1">Classes assignées</div>
                </div>
            </div>

            {{-- Barre de Filtres & Recherche --}}
            <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80">
                <form method="GET" action="{{ route('teachers.index') }}"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    <!-- Recherche textuelle -->
                    <div class="lg:col-span-2">
                        <label for="search"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Recherche</label>
                        <input type="text" id="search" name="search" value="{{ $search }}"
                            placeholder="Nom, matricule, spécialité, email, tél…"
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500 placeholder-slate-400" />
                    </div>

                    <!-- Filtre par Cycle -->
                    <div>
                        <label for="cycle_id"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Cycle</label>
                        <select id="cycle_id" name="cycle_id"
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Tous les cycles --</option>
                            @foreach ($cycles as $cyc)
                                <option value="{{ $cyc->id }}" {{ $cycleId == $cyc->id ? 'selected' : '' }}>
                                    {{ $cyc->name }} ({{ $cyc->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtre Type de Contrat -->
                    <div>
                        <label for="employment_type"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Contrat</label>
                        <select id="employment_type" name="employment_type"
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Tous les contrats --</option>
                            <option value="PERMANENT" {{ $employmentType === 'PERMANENT' ? 'selected' : '' }}>
                                Permanent</option>
                            <option value="VACATAIRE" {{ $employmentType === 'VACATAIRE' ? 'selected' : '' }}>
                                Vacataire</option>
                            <option value="CONTRACTUEL" {{ $employmentType === 'CONTRACTUEL' ? 'selected' : '' }}>
                                Contractuel</option>
                        </select>
                    </div>

                    <!-- Filtre Statut -->
                    <div>
                        <label for="status"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Statut</label>
                        <select id="status" name="status"
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Tous les statuts --</option>
                            <option value="ACTIF" {{ $status === 'ACTIF' ? 'selected' : '' }}>✅ Actif</option>
                            <option value="INACTIF" {{ $status === 'INACTIF' ? 'selected' : '' }}>❌ Inactif</option>
                            <option value="CONGE" {{ $status === 'CONGE' ? 'selected' : '' }}>⏳ En congé</option>
                        </select>
                    </div>

                    <!-- Boutons Actions -->
                    <div
                        class="sm:col-span-2 lg:col-span-5 flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="submit"
                            class="px-6 py-2.5 bg-purple-900 hover:bg-purple-800 text-amber-300 text-xs font-black uppercase tracking-wider rounded-2xl shadow-md transition flex items-center gap-1.5">
                            <span>🔍</span>
                            <span>Filtrer les Enseignants</span>
                        </button>
                        @if ($search || $cycleId || $employmentType || $status)
                            <a href="{{ route('teachers.index') }}"
                                class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-2xl transition">
                                Réinitialiser ✕
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Affichage Regroupé par Cycle --}}
            <div class="space-y-8">
                @php
                    $colorClasses = [
                        'Violet' => [
                            'badge' => 'bg-purple-600 text-white',
                            'border' => 'border-purple-200',
                            'pill' => 'bg-purple-100 text-purple-900',
                        ],
                        'Jaune' => [
                            'badge' => 'bg-amber-500 text-purple-950 font-black',
                            'border' => 'border-amber-200',
                            'pill' => 'bg-amber-100 text-amber-900',
                        ],
                        'Vert' => [
                            'badge' => 'bg-emerald-600 text-white',
                            'border' => 'border-emerald-200',
                            'pill' => 'bg-emerald-100 text-emerald-900',
                        ],
                        'Bleu' => [
                            'badge' => 'bg-blue-600 text-white',
                            'border' => 'border-blue-200',
                            'pill' => 'bg-blue-100 text-blue-900',
                        ],
                        'Rouge' => [
                            'badge' => 'bg-rose-600 text-white',
                            'border' => 'border-rose-200',
                            'pill' => 'bg-rose-100 text-rose-900',
                        ],
                    ];
                @endphp

                @forelse ($teachersByCycle as $cycleName => $cycleTeachers)
                    @php
                        $cycleModel = $cycleTeachers->first()?->cycle;
                        $tshirtColor = $cycleModel?->uniform_tshirt_color ?? 'Violet';
                        $colorConfig = $colorClasses[$tshirtColor] ?? [
                            'badge' => 'bg-purple-900 text-white',
                            'border' => 'border-purple-200',
                            'pill' => 'bg-purple-100 text-purple-900',
                        ];
                        $isPolyvalent = $cycleModel
                            ? in_array(
                                strtolower(trim($cycleModel->name)),
                                \App\Models\Teacher::polyvalentCycleNames(),
                                strict: true,
                            )
                            : false;
                    @endphp

                    <div
                        class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80 space-y-6 p-6">
                        <!-- En-tête de Section Cycle -->
                        <div
                            class="p-5 bg-gradient-to-r from-purple-950 via-purple-900 to-indigo-950 rounded-2xl text-white flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-md">
                            <div class="flex items-center gap-3.5">
                                <span
                                    class="w-12 h-12 rounded-2xl bg-amber-400 text-purple-950 font-black text-xl flex items-center justify-center shadow-xs flex-shrink-0">
                                    🏫
                                </span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-amber-300">
                                            Cycle d'Enseignement
                                        </span>
                                        @if ($cycleModel?->code)
                                            <span
                                                class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase {{ $colorConfig['badge'] }}">
                                                {{ $cycleModel->code }}
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-xl font-black text-white leading-tight mt-0.5">
                                        {{ $cycleName }}
                                    </h3>
                                    <div
                                        class="flex flex-wrap items-center gap-3 text-xs text-purple-200 font-bold mt-1">
                                        @if ($cycleModel?->uniform_tshirt_color)
                                            <span>👕 T-shirt : <strong
                                                    class="text-amber-300">{{ $cycleModel->uniform_tshirt_color }}</strong></span>
                                        @endif
                                        @if ($cycleModel?->start_time && $cycleModel?->end_time)
                                            <span>⏰ Horaires : <strong
                                                    class="text-purple-100">{{ $cycleModel->start_time }} -
                                                    {{ $cycleModel->end_time }}</strong></span>
                                        @endif
                                        <span>
                                            📚 Mode :
                                            @if ($isPolyvalent)
                                                <strong class="text-emerald-300">Polyvalent (Toutes matières)</strong>
                                            @else
                                                <strong class="text-amber-300">Spécialisé (Par discipline)</strong>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Compteur d'enseignants dans ce cycle -->
                            <div class="flex items-center gap-3">
                                <div
                                    class="px-4 py-2.5 bg-purple-900/80 rounded-2xl border border-purple-700/60 flex items-center gap-2">
                                    <span class="text-lg">👨‍🏫</span>
                                    <div>
                                        <span class="text-[10px] uppercase text-purple-300 font-black block">Effectif
                                            Cycle</span>
                                        <span class="text-sm font-black text-white">{{ $cycleTeachers->count() }}
                                            enseignant(s)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tableau des Enseignants du Cycle -->
                        <div class="overflow-x-auto rounded-2xl border border-slate-100 shadow-xs">
                            <table class="w-full text-left text-xs text-slate-700">
                                <thead
                                    class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-500 border-b border-slate-200">
                                    <tr>

                                        <th class="py-3 px-4">Enseignant</th>
                                        <th class="py-3 px-4">Spécialité / Diplôme</th>
                                        <th class="py-3 px-4 text-center">Contrat</th>
                                        <th class="py-3 px-4 text-center">Statut</th>
                                        <th class="py-3 px-4 text-center">Affectations</th>
                                        <th class="py-3 px-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white font-semibold">
                                    @foreach ($cycleTeachers as $teacher)
                                        @php
                                            $contractColors = [
                                                'PERMANENT' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                                'VACATAIRE' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                'CONTRACTUEL' => 'bg-sky-100 text-sky-800 border-sky-200',
                                            ];
                                            $contractColor =
                                                $contractColors[$teacher->employment_type] ??
                                                'bg-slate-100 text-slate-700 border-slate-200';

                                            $statusColors = [
                                                'ACTIF' => [
                                                    'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                    '✅ Actif',
                                                ],
                                                'INACTIF' => ['bg-red-100 text-red-800 border-red-200', '❌ Inactif'],
                                                'CONGE' => [
                                                    'bg-orange-100 text-orange-800 border-orange-200',
                                                    '⏳ En congé',
                                                ],
                                            ];
                                            [$statusClass, $statusLabel] = $statusColors[$teacher->status] ?? [
                                                'bg-slate-100 text-slate-700 border-slate-200',
                                                $teacher->status,
                                            ];
                                        @endphp
                                        <tr class="hover:bg-purple-50/30 transition">


                                            <!-- Enseignant (Nom, Email, Téléphone) -->
                                            <td class="py-3.5 px-4">
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="w-9 h-9 rounded-2xl bg-purple-100 text-purple-900 flex items-center justify-center font-black text-sm shrink-0 shadow-xs">
                                                        {{ strtoupper(substr($teacher->user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="font-black text-slate-900 text-sm flex items-center gap-2">
                                                            <span>{{ $teacher->user->name }}</span>
                                                            <span class="text-[10px] font-mono font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200">{{ $teacher->matricule }}</span>
                                                        </div>
                                                        <div
                                                            class="text-[10px] text-slate-400 font-semibold flex items-center gap-2 mt-0.5">
                                                            <span>✉️ {{ $teacher->user->email }}</span>
                                                            @if ($teacher->user->phone)
                                                                <span class="text-slate-500 font-mono font-bold">📞
                                                                    {{ $teacher->user->phone }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Spécialité / Diplôme / Niveaux -->
                                            <td class="py-3.5 px-4">
                                                <div class="font-black text-slate-800 text-xs flex items-center gap-1">
                                                    <span>🎓</span>
                                                    <span>{{ $teacher->speciality }}</span>
                                                </div>
                                                @if ($teacher->qualification)
                                                    <div class="text-[10px] text-slate-400 font-semibold mt-0.5">
                                                        {{ $teacher->qualification }}
                                                    </div>
                                                @endif
                                                @if($teacher->levels->isNotEmpty())
                                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                                        @foreach($teacher->levels as $lvl)
                                                            <span class="px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-900 border border-amber-200 text-[9px] font-bold">
                                                                {{ $lvl->name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>

                                            <!-- Contrat -->
                                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                                <span
                                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black border {{ $contractColor }}">
                                                    {{ $teacher->employment_type }}
                                                </span>
                                            </td>

                                            <!-- Statut -->
                                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                                <span
                                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black border {{ $statusClass }}">
                                                    {{ $statusLabel }}
                                                </span>
                                            </td>

                                            <!-- Affectations -->
                                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                                @php
                                                    $distinctClassesCount = $teacher->assignments->pluck('class_id')->unique()->count();
                                                    $totalAssignCount = $teacher->assignments->count();
                                                @endphp
                                                @if ($totalAssignCount > 0)
                                                    <span
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-purple-50 text-purple-950 border border-purple-200/80 rounded-full font-black text-[11px]"
                                                        title="{{ $teacher->assignments->map(fn($a) => ($a->schoolClass?->name ?? 'Classe') . ' (' . ($a->subject?->name ?? 'Matière') . ')')->join(', ') }}">
                                                        <span>📚</span>
                                                        <span>{{ $distinctClassesCount }} {{ Str::plural('classe', $distinctClassesCount) }}</span>
                                                        <span class="text-slate-400 font-semibold text-[10px]">({{ $totalAssignCount }} {{ Str::plural('matière', $totalAssignCount) }})</span>
                                                    </span>
                                                @else
                                                    <span class="text-slate-300 text-[10px] italic">Non assigné</span>
                                                @endif
                                            </td>

                                            <!-- Actions -->
                                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                                <div class="flex items-center justify-end gap-2">
                                                    <a href="{{ route('teachers.show', $teacher) }}"
                                                        class="px-2.5 py-1.5 text-[11px] font-black bg-purple-100 hover:bg-purple-900 hover:text-amber-300 text-purple-950 rounded-xl transition flex items-center gap-1">
                                                        👁️ Profil
                                                    </a>
                                                    <a href="{{ route('teachers.edit', $teacher) }}"
                                                        class="px-2.5 py-1.5 text-[11px] font-black bg-amber-100 hover:bg-amber-200 text-amber-900 rounded-xl transition flex items-center gap-1">
                                                        ✏️ Modifier
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-3xl p-12 text-center shadow-xl border border-slate-200/80 space-y-3">
                        <div class="text-4xl">👨‍🏫</div>
                        <h4 class="text-base font-black text-purple-950">Aucun enseignant trouvé</h4>
                        <p class="text-xs text-slate-500 max-w-md mx-auto font-medium">
                            @if ($search || $cycleId || $employmentType || $status)
                                Aucun enseignant ne correspond à vos critères de recherche.
                            @else
                                Aucun enseignant n'est encore enregistré dans l'établissement.
                            @endif
                        </p>
                        <div class="flex items-center justify-center gap-3 pt-2">
                            @if ($search || $cycleId || $employmentType || $status)
                                <a href="{{ route('teachers.index') }}"
                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                                    Réinitialiser les filtres
                                </a>
                            @endif
                            <a href="{{ route('teachers.create') }}"
                                class="px-4 py-2 bg-purple-900 hover:bg-purple-800 text-amber-300 font-black text-xs rounded-xl transition shadow-sm">
                                ➕ Ajouter un Enseignant
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
