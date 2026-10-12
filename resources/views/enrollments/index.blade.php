<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-3">
                    <span class="p-2.5 bg-purple-900 text-amber-300 rounded-2xl shadow-md text-lg">📋</span>
                    <span>Registre des Inscriptions par Niveau</span>
                </h2>
                <p class="text-xs font-bold text-slate-500 mt-1">
                    Année scolaire active : <span
                        class="text-purple-950 font-extrabold">{{ $activeYear?->name ?? 'Non définie' }}</span> •
                    Répartition par cycle et niveau d'enseignement
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pre-enrollments.index') }}"
                    class="px-4 py-3 bg-white hover:bg-slate-100 text-purple-950 font-bold text-xs rounded-2xl shadow-sm border border-slate-200/80 transition flex items-center gap-1.5">
                    <span>🌐</span>
                    <span>Demandes Web</span>
                </a>
                <a href="{{ route('enrollments.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-purple-900 hover:bg-purple-800 text-amber-300 font-black text-xs uppercase tracking-wider rounded-2xl shadow-lg hover:shadow-xl transition-all duration-200">
                    <span class="text-base">➕</span>
                    <span>Nouvelle Inscription</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Status Message -->
            @if (session('status'))
                <div
                    class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-bold flex items-center gap-3 shadow-xs">
                    <span class="text-lg">✅</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- 4 Cartes Statistiques Globales -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Total Inscriptions -->
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-900 flex items-center justify-center text-2xl shadow-xs">
                        📋
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Inscriptions
                            Totales</div>
                        <div class="text-2xl font-black text-purple-950">{{ $totalEnrollments }}</div>
                        <div class="text-[11px] font-bold text-slate-500">Année {{ $activeYear?->name }}</div>
                    </div>
                </div>

                <!-- Nouvelles Inscriptions -->
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-900 flex items-center justify-center text-2xl shadow-xs">
                        🌟
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Nouveaux Élèves
                        </div>
                        <div class="text-2xl font-black text-blue-950">{{ $newEnrollmentsCount }}</div>
                        <div class="text-[11px] font-bold text-blue-600">Première admission</div>
                    </div>
                </div>

                <!-- Réinscriptions -->
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-900 flex items-center justify-center text-2xl shadow-xs">
                        🔄
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Réinscriptions /
                            Transf.</div>
                        <div class="text-2xl font-black text-amber-950">{{ $reEnrollmentsCount }}</div>
                        <div class="text-[11px] font-bold text-amber-600">Anciens & transferts</div>
                    </div>
                </div>

                <!-- Validées -->
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-2xl shadow-xs">
                        ✅
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Inscriptions
                            Validées</div>
                        <div class="text-2xl font-black text-emerald-900">{{ $validEnrollmentsCount }}</div>
                        <div class="text-[11px] font-bold text-emerald-600">Dossiers confirmés</div>
                    </div>
                </div>
            </div>

            <!-- Filtres de recherche -->
            <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80">
                <form method="GET" action="{{ route('enrollments.index') }}"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
                    <div>
                        <label for="search"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Recherche</label>
                        <input type="text" id="search" name="search" value="{{ $search }}"
                            placeholder="N° Inscription, matricule, nom..."
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500 placeholder-slate-400" />
                    </div>

                    <div>
                        <label for="cycle_id"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Cycle</label>
                        <select id="cycle_id" name="cycle_id"
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Tous les cycles --</option>
                            @foreach ($cycles as $cyc)
                                <option value="{{ $cyc->id }}" {{ $cycleId == $cyc->id ? 'selected' : '' }}>
                                    {{ $cyc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="level_id"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Niveau</label>
                        <select id="level_id" name="level_id"
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Tous les niveaux --</option>
                            @foreach ($levels as $lvl)
                                <option value="{{ $lvl->id }}" {{ $levelId == $lvl->id ? 'selected' : '' }}>
                                    {{ $lvl->cycle->name }} — {{ $lvl->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="class_id"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Classe</label>
                        <select id="class_id" name="class_id"
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Toutes les classes --</option>
                            @foreach ($classes as $cls)
                                <option value="{{ $cls->id }}" {{ $classId == $cls->id ? 'selected' : '' }}>
                                    {{ $cls->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="type"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Type
                            d'Admission</label>
                        <select id="type" name="type"
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Tous les types --</option>
                            <option value="NOUVEAU" {{ $type === 'NOUVEAU' ? 'selected' : '' }}>Nouvelle Inscription
                            </option>
                            <option value="REINSCRIPTION" {{ $type === 'REINSCRIPTION' ? 'selected' : '' }}>
                                Réinscription</option>
                            <option value="TRANSFERT" {{ $type === 'TRANSFERT' ? 'selected' : '' }}>Transfert</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="flex-1 py-2.5 px-4 bg-purple-900 hover:bg-purple-800 text-amber-300 font-extrabold text-xs uppercase tracking-wider rounded-2xl shadow-md transition">
                            🔍 Filtrer
                        </button>
                        @if ($search || $levelId || $classId || $type || $status || $cycleId)
                            <a href="{{ route('enrollments.index') }}"
                                class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-2xl transition"
                                title="Effacer les filtres">
                                ✕
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Registre des Inscriptions Regroupé par Niveau -->
            <div class="space-y-6">
                @forelse ($enrollmentsByLevel as $levelTitle => $levelEnrollments)
                    <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                        <!-- En-tête du Niveau -->
                        <div
                            class="px-6 py-4 bg-gradient-to-r from-purple-950 to-purple-900 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <span class="p-1.5 bg-purple-800 text-amber-300 rounded-xl text-sm font-black">📁</span>
                                <h3 class="text-sm font-black text-amber-300 uppercase tracking-widest">
                                    Niveau : {{ $levelTitle }}
                                </h3>
                            </div>
                            <span
                                class="px-3 py-1 bg-purple-800/80 text-purple-100 text-xs font-extrabold rounded-full border border-purple-700/50">
                                {{ $levelEnrollments->count() }} inscription(s)
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead>
                                    <tr
                                        class="bg-slate-50/80 text-[11px] font-black text-slate-500 uppercase tracking-wider">

                                        <th class="px-6 py-3 text-left">Élève</th>
                                        <th class="px-6 py-3 text-left">Classe</th>
                                        <th class="px-6 py-3 text-left">Type & Date</th>
                                        <th class="px-6 py-3 text-center">Statut</th>
                                        <th class="px-6 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach ($levelEnrollments as $enrollment)
                                        <tr class="hover:bg-purple-50/30 transition-colors">
                                            <!-- Élève -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="w-10 h-10 rounded-2xl font-black text-xs flex items-center justify-center shadow-xs {{ $enrollment->student->gender === 'F' ? 'bg-pink-100 text-pink-700 border-2 border-pink-200' : 'bg-blue-100 text-blue-700 border-2 border-blue-200' }}">
                                                        {{ mb_substr($enrollment->student->first_name, 0, 1) }}{{ mb_substr($enrollment->student->last_name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <a href="{{ route('students.show', $enrollment->student) }}"
                                                            class="text-sm font-black text-slate-900 hover:text-purple-900 transition">
                                                            {{ $enrollment->student->last_name }}
                                                            {{ $enrollment->student->first_name }}
                                                        </a>
                                                        <div class="text-xs text-slate-400 font-bold font-mono">
                                                            {{ $enrollment->enrollment_number }} • {{ $enrollment->student->matricule }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Classe -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-3 py-1 rounded-xl text-xs font-extrabold bg-purple-100 text-purple-900 border border-purple-200">
                                                    {{ $enrollment->schoolClass->name }}
                                                </span>
                                            </td>

                                            <!-- Type & Date -->
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600">
                                                <div
                                                    class="font-extrabold {{ $enrollment->type === 'NOUVEAU' ? 'text-blue-700' : 'text-amber-700' }}">
                                                    {{ $enrollment->type }}
                                                    @if ($enrollment->is_repeater)
                                                        <span
                                                            class="text-[10px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-bold ml-1">Redoublant</span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-400 mt-0.5">
                                                    Le
                                                    {{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('d/m/Y') : 'N/A' }}
                                                </div>
                                            </td>

                                            <!-- Statut -->
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if ($enrollment->status === 'VALIDE')
                                                    <span
                                                        class="px-2.5 py-1 text-[11px] font-black rounded-full bg-emerald-100 text-emerald-800">
                                                        Validé
                                                    </span>
                                                @elseif ($enrollment->status === 'EN_ATTENTE')
                                                    <span
                                                        class="px-2.5 py-1 text-[11px] font-black rounded-full bg-amber-100 text-amber-800">
                                                        En attente
                                                    </span>
                                                @else
                                                    <span
                                                        class="px-2.5 py-1 text-[11px] font-black rounded-full bg-red-100 text-red-800">
                                                        {{ $enrollment->status }}
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Actions -->
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <a href="{{ route('enrollments.receipt', $enrollment) }}"
                                                        class="px-3 py-1.5 bg-amber-400 hover:bg-amber-300 text-purple-950 font-black text-xs rounded-xl transition shadow-xs flex items-center gap-1"
                                                        title="Imprimer le reçu officiel">
                                                        <span>📄</span>
                                                        <span>Reçu</span>
                                                    </a>
                                                    <a href="{{ route('enrollments.show', $enrollment) }}"
                                                        class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-950 font-black text-xs rounded-xl transition flex items-center gap-1">
                                                        <span>👁️</span>
                                                        <span>Détails</span>
                                                    </a>
                                                    <a href="{{ route('enrollments.edit', $enrollment) }}"
                                                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-xs rounded-xl transition">
                                                        ✏️
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
                    <div class="bg-white shadow-xl rounded-3xl p-12 text-center border border-slate-200/80">
                        <div class="max-w-sm mx-auto space-y-3">
                            <div class="text-4xl">📋</div>
                            <div class="text-base font-black text-slate-800">Aucune inscription trouvée</div>
                            <p class="text-xs text-slate-400">
                                Aucun dossier d'inscription ne correspond aux critères sélectionnés pour cette année
                                scolaire.
                            </p>
                            <div class="pt-3">
                                <a href="{{ route('enrollments.create') }}"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-900 hover:bg-purple-800 text-amber-300 font-black text-xs uppercase tracking-wider rounded-2xl shadow-md transition">
                                    <span>➕</span>
                                    <span>Inscrire un premier élève</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforelse

                @if ($enrollments->hasPages())
                    <div class="p-6 bg-white rounded-3xl shadow-xl border border-slate-200/80">
                        {{ $enrollments->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
