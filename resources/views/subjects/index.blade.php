<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-purple-950 leading-tight flex items-center gap-2">
                    <span>📚</span> Matières & Coefficients
                </h2>
                <p class="text-xs text-slate-500 font-semibold mt-0.5">
                    Catalogue pédagogique et matrice des pondérations par niveau scolaire
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('subjects.create') }}"
                   class="px-4 py-2 text-xs font-black bg-purple-900 hover:bg-purple-800 text-white rounded-xl transition shadow-md flex items-center gap-1.5">
                    <span>➕</span> Nouvelle Matière
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
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-2xl text-sm font-bold flex items-center gap-2 shadow-xs">
                    <span>⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Stat Cards --}}
            @php
                $totalSubjects = $subjects->count();
                $activeSubjects = $subjects->where('is_active', true)->count();
                $totalCoeffs = $subjects->sum('coefficients_count');
                $totalLevels = $allLevels->count();
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-md">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-1">Total Matières</div>
                    <div class="text-3xl font-black text-slate-900">{{ $totalSubjects }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">Catalogue officiel</div>
                </div>
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-md">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-emerald-600 mb-1">Matières Actives</div>
                    <div class="text-3xl font-black text-emerald-700">{{ $activeSubjects }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">Enseignées</div>
                </div>
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-md">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-purple-600 mb-1">Coefficients Définis</div>
                    <div class="text-3xl font-black text-purple-900">{{ $totalCoeffs }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">Pondérations actives</div>
                </div>
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-md">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-amber-600 mb-1">Niveaux d'Enseignement</div>
                    <div class="text-3xl font-black text-amber-600">{{ $totalLevels }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">Sur 4 cycles</div>
                </div>
            </div>

            {{-- SECTION 1 : CATALOGUE DES MATIÈRES --}}
            <div class="bg-white shadow-xl rounded-3xl border border-slate-200/80">
                <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/60">
                    <div>
                        <h3 class="text-base font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <span>📖</span> Catalogue des Matières
                        </h3>
                        <p class="text-xs text-slate-500 font-semibold mt-0.5">
                            Gestion des disciplines, statuts et affectations
                        </p>
                    </div>

                    {{-- Filtre par catégorie --}}
                    <form method="GET" action="{{ route('subjects.index') }}" data-no-confirm="true" class="flex items-center gap-2">
                        @if($selectedLevelId)
                            <input type="hidden" name="level_id" value="{{ $selectedLevelId }}">
                        @endif
                        <label class="text-xs font-extrabold text-slate-600 uppercase">Filtrer :</label>
                        <select name="category" onchange="this.form.submit()"
                                class="text-xs font-bold border-purple-300 rounded-xl shadow-xs focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Toutes les catégories</option>
                            @foreach ($categories as $catKey => $catLabel)
                                <option value="{{ $catKey }}" {{ $selectedCategory === $catKey ? 'selected' : '' }}>
                                    {{ $catLabel }}
                                </option>
                            @endforeach
                        </select>
                        @if($selectedCategory)
                            <a href="{{ route('subjects.index', array_filter(['level_id' => $selectedLevelId])) }}"
                               class="text-xs font-bold text-slate-400 hover:text-slate-700 px-2 py-1">
                                ✕ Effacer
                            </a>
                        @endif
                    </form>
                </div>

                <div class="p-6">
                    @if($subjects->isEmpty())
                        <div class="text-center py-12">
                            <div class="text-4xl mb-3">📚</div>
                            <div class="text-sm font-bold text-slate-700">Aucune matière trouvée</div>
                            <p class="text-xs text-slate-400 mt-1">Commencez par ajouter votre première matière au catalogue.</p>
                            <a href="{{ route('subjects.create') }}" class="inline-block mt-4 px-4 py-2 text-xs font-black bg-purple-900 text-white rounded-xl shadow-md">
                                ➕ Ajouter une matière
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($subjects as $sbj)
                                @include('subjects.partials.subject-card', ['sbj' => $sbj])
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- SECTION 2 : COEFFICIENTS PAR NIVEAU (Navigation vers levels/show) --}}
            <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-slate-200/80">
                <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/60">
                    <div>
                        <h3 class="text-base font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <span>📊</span> Coefficients par Niveau
                        </h3>
                        <p class="text-xs text-slate-500 font-semibold mt-0.5">
                            Cliquez sur un niveau pour gérer ses matières et pondérations
                        </p>
                    </div>
                    <span class="text-xs font-bold px-3 py-1.5 rounded-xl bg-purple-100 text-purple-900">
                        {{ $allLevels->count() }} niveau(x) configuré(s)
                    </span>
                </div>

                <div class="p-6 space-y-6">
                    @php
                        $groupedLevels = $allLevels->groupBy(fn ($lvl) => $lvl->cycle->name ?? 'Sans cycle');
                    @endphp

                    @forelse ($groupedLevels as $cycleName => $cycleLevels)
                        <div class="space-y-3">
                            <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
                                <span class="text-base">🏫</span>
                                <h4 class="text-sm font-black text-purple-950 uppercase tracking-wider">{{ $cycleName }}</h4>
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-purple-100 text-purple-800">
                                    {{ $cycleLevels->count() }} niveaux
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($cycleLevels as $lvl)
                                    @php
                                        $lvlCoeffCount = $lvl->coefficients->count();
                                        $lvlCoeffSum   = $lvl->coefficients->sum('coefficient');
                                        $lvlHours      = $lvl->coefficients->sum('weekly_hours');
                                    @endphp
                                    <a href="{{ route('cycles.levels.show', [$lvl->cycle, $lvl]) }}"
                                       class="group flex flex-col justify-between bg-slate-50/70 rounded-2xl border border-slate-200 p-4 hover:border-purple-400 hover:shadow-md transition-all">
                                        <div>
                                            <div class="flex items-start justify-between gap-2 mb-3">
                                                <div>
                                                    <span class="text-sm font-black text-slate-900 group-hover:text-purple-900 transition">
                                                        {{ $lvl->name }}
                                                    </span>
                                                    <span class="text-2xs font-extrabold text-slate-400 uppercase tracking-wider block">
                                                        Code : {{ $lvl->code }}
                                                    </span>
                                                </div>
                                                <span class="text-xs font-black text-purple-900 shrink-0">
                                                    Σ {{ $lvlCoeffSum }}
                                                </span>
                                            </div>

                                            <div class="flex items-center gap-3 text-2xs font-bold text-slate-500">
                                                <span class="flex items-center gap-1">
                                                    📚 {{ $lvlCoeffCount }} matière(s)
                                                </span>
                                                <span class="flex items-center gap-1">
                                                    ⏱ {{ $lvlHours }}h/sem
                                                </span>
                                            </div>

                                            @if($lvlCoeffCount === 0)
                                                <p class="mt-2 text-2xs font-bold text-amber-600 italic">⚠ Aucun coefficient défini</p>
                                            @endif
                                        </div>

                                        <div class="mt-3 pt-3 border-t border-slate-200 flex justify-end">
                                            <span class="text-2xs font-extrabold text-purple-900 group-hover:underline flex items-center gap-1">
                                                Gérer les coefficients <span class="group-hover:translate-x-0.5 transition-transform">→</span>
                                            </span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-sm text-slate-400 font-bold">
                            Aucun niveau disponible.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
