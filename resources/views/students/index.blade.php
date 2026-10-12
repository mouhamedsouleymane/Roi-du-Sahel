<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-3">
                    <span class="p-2.5 bg-purple-900 text-amber-300 rounded-2xl shadow-md text-lg">🎓</span>
                    <span>Registre & Dossiers des Élèves</span>
                </h2>
                <p class="text-xs font-bold text-slate-500 mt-1">
                    @if ($selectedClass)
                        Dossiers des élèves de la classe <span
                            class="text-purple-950 font-extrabold">{{ $selectedClass->name }}</span>
                        ({{ $selectedClass->level->cycle->name }})
                    @elseif ($search)
                        Résultats de recherche globale pour <span
                            class="text-purple-950 font-extrabold">"{{ $search }}"</span>
                    @else
                        Sélectionnez une classe pour consulter la liste de ses élèves
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                @if ($selectedClass || $search)
                    <a href="{{ route('students.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-100 text-purple-950 font-black text-xs uppercase tracking-wider rounded-2xl shadow-sm border border-slate-200 transition">
                        <span>←</span>
                        <span>Toutes les Classes</span>
                    </a>
                @endif
                <a href="{{ route('enrollments.create', $selectedClass ? ['class_id' => $selectedClass->id] : []) }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-purple-900 hover:bg-purple-800 text-amber-300 font-black text-xs uppercase tracking-wider rounded-2xl shadow-lg hover:shadow-xl transition-all duration-200">
                    <span class="text-base">➕</span>
                    <span>Inscrire un Élève</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-slate-50 via-purple-50/20 to-slate-100 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">

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
                <!-- Total Élèves -->
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-900 flex items-center justify-center text-2xl shadow-xs">
                        🎓
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Effectif Global
                        </div>
                        <div class="text-2xl font-black text-purple-950">{{ $totalStudents }}</div>
                        <div class="text-[11px] font-bold text-slate-500">Dossiers enregistrés</div>
                    </div>
                </div>

                <!-- Inscrits cette année -->
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-2xl shadow-xs">
                        📋
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Inscrits
                            {{ $activeYear?->name ?? 'En cours' }}</div>
                        <div class="text-2xl font-black text-emerald-900">{{ $enrolledThisYear }}</div>
                        <div class="text-[11px] font-bold text-emerald-600">Inscriptions actives</div>
                    </div>
                </div>

                <!-- Filles -->
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-pink-100 text-pink-700 flex items-center justify-center text-2xl shadow-xs">
                        👧
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Filles</div>
                        <div class="text-2xl font-black text-pink-900">{{ $femaleCount }}</div>
                        <div class="text-[11px] font-bold text-pink-600">
                            {{ $totalStudents > 0 ? round(($femaleCount / $totalStudents) * 100) : 0 }}% de l'effectif
                        </div>
                    </div>
                </div>

                <!-- Garçons -->
                <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80 flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-800 flex items-center justify-center text-2xl shadow-xs">
                        👦
                    </div>
                    <div>
                        <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Garçons</div>
                        <div class="text-2xl font-black text-blue-950">{{ $maleCount }}</div>
                        <div class="text-[11px] font-bold text-blue-600">
                            {{ $totalStudents > 0 ? round(($maleCount / $totalStudents) * 100) : 0 }}% de l'effectif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barre de Recherche Globale et Filtres -->
            <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80">
                <form method="GET" action="{{ route('students.index') }}"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    <div>
                        <label for="search"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Recherche d'un
                            élève</label>
                        <input type="text" id="search" name="search" value="{{ $search }}"
                            placeholder="Matricule, nom ou prénom..."
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500 placeholder-slate-400" />
                    </div>

                    <div>
                        <label for="class_id"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Accéder
                            directement à une classe</label>
                        <select id="class_id" name="class_id"
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Choisir une classe --</option>
                            @foreach ($classes as $cls)
                                <option value="{{ $cls->id }}" {{ $classId == $cls->id ? 'selected' : '' }}>
                                    {{ $cls->level->cycle->name }} — {{ $cls->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="gender"
                            class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Sexe</label>
                        <select id="gender" name="gender"
                            class="w-full text-sm font-bold border-slate-200 rounded-2xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Tous les genres --</option>
                            <option value="F" {{ $gender === 'F' ? 'selected' : '' }}>Fille (F)</option>
                            <option value="M" {{ $gender === 'M' ? 'selected' : '' }}>Garçon (M)</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="flex-1 py-2.5 px-4 bg-purple-900 hover:bg-purple-800 text-amber-300 font-extrabold text-xs uppercase tracking-wider rounded-2xl shadow-md transition">
                            🔍 Afficher
                        </button>
                        @if ($search || $classId || $gender || $cycleId)
                            <a href="{{ route('students.index') }}"
                                class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-2xl transition">
                                ✕ Réinitialiser
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- ═════════════════════════════════════════════════════════════════ --}}
            {{-- CAS 1 : VUE EN CARTES PAR CLASSE (Mode par défaut)               --}}
            {{-- ═════════════════════════════════════════════════════════════════ --}}
            @if (!$selectedClass && !$search)
                <div class="space-y-10">

                    <!-- Filtres par Cycle (Onglets) -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-2">
                        <a href="{{ route('students.index') }}"
                            class="px-4 py-2 rounded-2xl text-xs font-black transition {{ !$cycleId ? 'bg-purple-900 text-amber-300 shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                            🏫 Tous les Cycles
                        </a>
                        @foreach ($cycles as $cycle)
                            <a href="{{ route('students.index', ['cycle_id' => $cycle->id]) }}"
                                class="px-4 py-2 rounded-2xl text-xs font-black transition {{ $cycleId == $cycle->id ? 'bg-purple-900 text-amber-300 shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                                {{ $cycle->name }}
                            </a>
                        @endforeach
                    </div>

                    @foreach ($cycles as $cycle)
                        @php
                            $cycleClasses = $cycle->levels->flatMap->classes;
                            $cycleStudentsCount = $cycleClasses->sum('active_enrollments_count');
                        @endphp

                        <div class="space-y-4">
                            <!-- En-tête du Cycle -->
                            <div class="flex items-center justify-between px-2">
                                <div class="flex items-center gap-3">
                                    <span class="w-3 h-8 bg-purple-900 rounded-full"></span>
                                    <div>
                                        <h3 class="text-lg font-black text-purple-950 uppercase tracking-wide">
                                            Cycle {{ $cycle->name }}
                                        </h3>
                                        <p class="text-xs font-bold text-slate-400">
                                            {{ $cycleClasses->count() }} classe(s) • {{ $cycleStudentsCount }}
                                            élève(s) inscrit(s)
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Groupes par Niveau dans le Cycle (Disposés horizontalement) -->
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 items-stretch">
                                @forelse ($cycle->levels as $level)
                                    @php
                                        $levelClasses = $level->classes;
                                    @endphp
                                    @if ($levelClasses->isNotEmpty())
                                        <div
                                            class="bg-slate-50/70 p-5 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col justify-between space-y-4">
                                            <!-- En-tête Niveau -->
                                            <div
                                                class="flex items-center justify-between pb-3 border-b border-slate-200/70">
                                                <div class="flex items-center gap-2.5">
                                                    <span
                                                        class="w-3 h-3 rounded-full bg-purple-900 ring-4 ring-purple-100"></span>
                                                    <h4
                                                        class="text-sm font-black text-purple-950 uppercase tracking-wide">
                                                        {{ $level->name }}
                                                    </h4>
                                                </div>
                                                <span
                                                    class="text-[11px] font-bold px-2.5 py-1 rounded-xl bg-white border border-slate-200 text-slate-600 shadow-xs">
                                                    {{ $levelClasses->count() }} classe(s) •
                                                    {{ $levelClasses->sum('active_enrollments_count') }} élève(s)
                                                </span>
                                            </div>

                                            <!-- Grille des Cartes de Classes de ce Niveau -->
                                            <div
                                                class="grid grid-cols-1 {{ $levelClasses->count() > 1 ? 'sm:grid-cols-2' : '' }} gap-3.5 flex-1">
                                                @foreach ($levelClasses as $class)
                                                    @php
                                                        $enrolled = $class->active_enrollments_count ?? 0;
                                                        $capacity = $class->capacity ?? 35;
                                                        $fillPercentage = min(
                                                            100,
                                                            round(($enrolled / max(1, $capacity)) * 100),
                                                        );
                                                    @endphp
                                                    <div
                                                        class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 hover:border-purple-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between group">
                                                        <div class="space-y-3">
                                                            <!-- Haut de Carte : Nom & Salle -->
                                                            <div class="flex items-start justify-between">
                                                                <h4
                                                                    class="text-base font-black text-slate-900 group-hover:text-purple-900 transition">
                                                                    {{ $class->name }}
                                                                </h4>
                                                                @if ($class->room_number)
                                                                    <span
                                                                        class="text-[10px] font-bold text-slate-400 bg-slate-50 px-2 py-0.5 rounded-lg border border-slate-100">
                                                                        🚪 {{ $class->room_number }}
                                                                    </span>
                                                                @endif
                                                            </div>

                                                            <!-- Stat Effectif de la classe -->
                                                            <div
                                                                class="p-2.5 bg-slate-50/80 rounded-xl border border-slate-100 space-y-1.5">
                                                                <div class="flex items-center justify-between">
                                                                    <span
                                                                        class="text-[10px] font-extrabold text-slate-500 uppercase">Effectif</span>
                                                                    <span class="text-xs font-black text-purple-950">
                                                                        {{ $enrolled }} <span
                                                                            class="text-[10px] font-normal text-slate-400">/
                                                                            {{ $capacity }}</span>
                                                                    </span>
                                                                </div>

                                                                <!-- Jauge de Remplissage -->
                                                                <div
                                                                    class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                                                    <div class="bg-purple-900 h-1.5 rounded-full transition-all duration-500"
                                                                        style="width: {{ $fillPercentage }}%"></div>
                                                                </div>

                                                                <!-- Répartition Filles / Garçons -->
                                                                <div
                                                                    class="flex items-center justify-between text-[10px] font-bold text-slate-500 pt-0.5">
                                                                    <span
                                                                        class="flex items-center gap-1 text-pink-700">
                                                                        👧 {{ $class->girls_count ?? 0 }} F
                                                                    </span>
                                                                    <span
                                                                        class="flex items-center gap-1 text-blue-700">
                                                                        👦 {{ $class->boys_count ?? 0 }} G
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <!-- Titulaire -->
                                                            @if ($class->mainTeacher)
                                                                <div
                                                                    class="text-[10px] text-slate-500 flex items-center gap-1.5 truncate">
                                                                    <span class="text-purple-700">👨‍🏫</span>
                                                                    <span
                                                                        class="font-bold truncate">{{ $class->mainTeacher->name }}</span>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <!-- Bouton Accéder à la liste des élèves -->
                                                        <div class="pt-3 mt-3 border-t border-slate-100">
                                                            <a href="{{ route('students.index', ['class_id' => $class->id]) }}"
                                                                class="w-full py-1.5 px-3 bg-purple-50 group-hover:bg-purple-900 text-purple-950 group-hover:text-amber-300 font-black text-xs rounded-xl transition-all duration-200 flex items-center justify-center gap-1.5 shadow-xs">
                                                                <span>📋</span>
                                                                <span>Registre ({{ $enrolled }})</span>
                                                                <span
                                                                    class="group-hover:translate-x-1 transition-transform">→</span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @empty
                                    <div
                                        class="col-span-full p-8 text-center bg-white rounded-3xl border border-slate-200/80 text-xs text-slate-400 italic">
                                        Aucun niveau configuré dans ce cycle pour l'année scolaire active.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach

                </div>
            @endif

            {{-- ═════════════════════════════════════════════════════════════════ --}}
            {{-- CAS 2 : LISTE DES ÉLÈVES D'UNE CLASSE OU RÉSULTATS DE RECHERCHE   --}}
            {{-- ═════════════════════════════════════════════════════════════════ --}}
            @if ($selectedClass || $search)
                <div class="space-y-6">

                    <!-- Bannière Classe Sélectionnée -->
                    @if ($selectedClass)
                        <div
                            class="bg-purple-950 text-white p-6 sm:p-8 rounded-3xl shadow-xl border border-purple-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-[11px] font-black uppercase tracking-widest px-3 py-1 rounded-full bg-purple-800 text-purple-200">
                                        {{ $selectedClass->level->cycle->name }} • {{ $selectedClass->level->name }}
                                    </span>
                                    @if ($selectedClass->room_number)
                                        <span class="text-[11px] font-bold text-amber-300">
                                            🚪 Salle {{ $selectedClass->room_number }}
                                        </span>
                                    @endif
                                </div>
                                <h3 class="text-3xl font-black text-amber-300">
                                    Classe : {{ $selectedClass->name }}
                                </h3>
                                <p class="text-xs font-bold text-purple-200">
                                    {{ $students ? $students->total() : 0 }} élève(s) inscrit(s) dans cette classe
                                    @if ($selectedClass->mainTeacher)
                                        • Titulaire : <span
                                            class="text-white">{{ $selectedClass->mainTeacher->name }}</span>
                                    @endif
                                </p>
                            </div>

                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <a href="{{ route('students.index') }}"
                                    class="flex-1 sm:flex-none text-center px-4 py-3 bg-purple-900 hover:bg-purple-800 text-purple-100 font-black text-xs uppercase tracking-wider rounded-2xl transition border border-purple-800">
                                    ← Changer de classe
                                </a>
                                <a href="{{ route('enrollments.create', ['class_id' => $selectedClass->id]) }}"
                                    class="flex-1 sm:flex-none text-center px-5 py-3 bg-amber-400 hover:bg-amber-300 text-purple-950 font-black text-xs uppercase tracking-wider rounded-2xl shadow-md transition">
                                    ➕ Inscrire un élève
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Tableau des Élèves -->
                    <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                        <div
                            class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <h3 class="text-xs font-black text-purple-950 uppercase tracking-widest">
                                @if ($selectedClass)
                                    Liste nominative des élèves ({{ $students->total() }})
                                @else
                                    Résultats de recherche globale ({{ $students->total() }})
                                @endif
                            </h3>
                            <span class="text-xs font-bold text-slate-400">Page {{ $students->currentPage() }} sur
                                {{ $students->lastPage() }}</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead>
                                    <tr
                                        class="bg-slate-50/80 text-[11px] font-black text-slate-500 uppercase tracking-wider">
                                        <th class="px-6 py-4 text-left">Élève</th>
                                        <th class="px-6 py-4 text-left">Matricule</th>
                                        <th class="px-6 py-4 text-left">Naissance & Âge</th>
                                        <th class="px-6 py-4 text-left">Classe Active</th>
                                        <th class="px-6 py-4 text-left">Tuteur Principal</th>
                                        <th class="px-6 py-4 text-center">Statut</th>
                                        <th class="px-6 py-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse ($students as $student)
                                        @php
                                            $currentClass = $student->currentEnrollment?->schoolClass;
                                            $primaryGuardian = $student->guardians->first();
                                            $age = $student->birth_date ? $student->birth_date->age : null;
                                        @endphp
                                        <tr class="hover:bg-purple-50/30 transition-colors">
                                            <!-- Élève (Avatar + Nom) -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="w-10 h-10 rounded-2xl font-black text-xs flex items-center justify-center shadow-xs {{ $student->gender === 'F' ? 'bg-pink-100 text-pink-700 border-2 border-pink-200' : 'bg-blue-100 text-blue-700 border-2 border-blue-200' }}">
                                                        {{ mb_substr($student->first_name, 0, 1) }}{{ mb_substr($student->last_name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <a href="{{ route('students.show', $student) }}"
                                                            class="text-sm font-black text-slate-900 hover:text-purple-900 transition">
                                                            {{ $student->last_name }} {{ $student->first_name }}
                                                        </a>
                                                        <div class="text-xs text-slate-400 font-bold">
                                                            {{ $student->gender === 'F' ? 'Fille' : 'Garçon' }}
                                                            @if ($student->blood_group)
                                                                • <span
                                                                    class="text-purple-700 font-mono">{{ $student->blood_group }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Matricule -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-mono font-bold bg-purple-50 text-purple-900 border border-purple-100">
                                                    {{ $student->matricule }}
                                                </span>
                                            </td>

                                            <!-- Naissance & Âge -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-xs font-bold text-slate-800">
                                                    {{ $student->birth_date ? $student->birth_date->format('d/m/Y') : 'N/A' }}
                                                </div>
                                                @if ($age !== null)
                                                    <div class="text-[11px] font-bold text-slate-400">
                                                        {{ $age }} ans</div>
                                                @endif
                                            </td>

                                            <!-- Classe Active -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($currentClass)
                                                    <div class="inline-flex flex-col">
                                                        <span
                                                            class="px-3 py-1 rounded-xl text-xs font-extrabold bg-purple-100 text-purple-900 border border-purple-200">
                                                            {{ $currentClass->name }}
                                                        </span>
                                                        <span
                                                            class="text-[10px] font-black text-slate-400 mt-0.5 ml-1">
                                                            {{ $currentClass->level->cycle->name ?? '' }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <span
                                                        class="px-2.5 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-500">
                                                        Non inscrit
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Tuteur Principal -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($primaryGuardian)
                                                    <div class="text-xs font-bold text-slate-900">
                                                        {{ $primaryGuardian->first_name }}
                                                        {{ $primaryGuardian->last_name }}
                                                        <span
                                                            class="text-[10px] font-normal text-slate-400">({{ $primaryGuardian->relationship }})</span>
                                                    </div>
                                                    <div
                                                        class="text-[11px] font-mono text-slate-500 flex items-center gap-1.5 mt-0.5">
                                                        <span>📞 {{ $primaryGuardian->phone_primary }}</span>
                                                        @if ($primaryGuardian->phone_primary)
                                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $primaryGuardian->phone_primary) }}"
                                                                target="_blank" title="WhatsApp"
                                                                class="text-emerald-600 hover:text-emerald-700 text-xs">
                                                                💬
                                                            </a>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-xs text-slate-400 italic">Non renseigné</span>
                                                @endif
                                            </td>

                                            <!-- Statut -->
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if ($student->status === 'ACTIF')
                                                    <span
                                                        class="px-2.5 py-1 text-[11px] font-black rounded-full bg-emerald-100 text-emerald-800">
                                                        Actif
                                                    </span>
                                                @elseif ($student->status === 'TRANSFERE')
                                                    <span
                                                        class="px-2.5 py-1 text-[11px] font-black rounded-full bg-amber-100 text-amber-800">
                                                        Transféré
                                                    </span>
                                                @elseif ($student->status === 'RADIE')
                                                    <span
                                                        class="px-2.5 py-1 text-[11px] font-black rounded-full bg-red-100 text-red-800">
                                                        Radié
                                                    </span>
                                                @else
                                                    <span
                                                        class="px-2.5 py-1 text-[11px] font-black rounded-full bg-slate-100 text-slate-700">
                                                        {{ $student->status }}
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Actions -->
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <a href="{{ route('students.show', $student) }}"
                                                        class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-950 font-black text-xs rounded-xl transition flex items-center gap-1">
                                                        <span>👁️</span>
                                                        <span>Fiche</span>
                                                    </a>
                                                    <a href="{{ route('students.edit', $student) }}"
                                                        class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-900 font-black text-xs rounded-xl transition flex items-center gap-1">
                                                        <span>✏️</span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-12 text-center">
                                                <div class="max-w-sm mx-auto space-y-3">
                                                    <div class="text-4xl">🎓</div>
                                                    <div class="text-sm font-black text-slate-800">Aucun élève trouvé
                                                    </div>
                                                    <p class="text-xs text-slate-400">
                                                        Aucun dossier d'élève dans cette classe pour le moment.
                                                    </p>
                                                    @if ($selectedClass)
                                                        <div class="pt-2">
                                                            <a href="{{ route('enrollments.create', ['class_id' => $selectedClass->id]) }}"
                                                                class="inline-flex items-center gap-2 px-4 py-2 bg-purple-900 hover:bg-purple-800 text-amber-300 font-black text-xs rounded-xl transition shadow-sm">
                                                                ➕ Inscrire le premier élève
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($students->hasPages())
                            <div class="p-6 border-t border-slate-100 bg-slate-50/50">
                                {{ $students->links() }}
                            </div>
                        @endif
                    </div>

                </div>
            @endif

        </div>
    </div>
</x-app-layout>
