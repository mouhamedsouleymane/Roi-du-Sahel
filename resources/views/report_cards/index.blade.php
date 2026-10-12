<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <div>
                <h2 class="font-black text-xl text-purple-950 leading-tight tracking-tight">
                    📄 Bulletins de Notes
                </h2>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">
                    {{ $activeYear?->name ?? '—' }}
                    &nbsp;·&nbsp; Regroupés par Niveau → Classe
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            {{-- Notification flash --}}
            @if (session('status'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3.5 rounded-2xl flex items-center gap-3 shadow-sm" role="alert">
                    <span class="text-xl">✅</span>
                    <div>
                        <span class="font-black">Succès !</span>
                        <span class="font-medium"> {{ session('status') }}</span>
                    </div>
                </div>
            @endif

            {{-- ── BARRE FILTRES & STATS GLOBALES ─────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5">
                <form method="GET" action="{{ route('report-cards.index') }}"
                      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">

                    {{-- Période --}}
                    <div>
                        <label class="block text-[11px] font-black text-slate-600 uppercase tracking-wider mb-1.5">
                            📅 Période
                        </label>
                        <select name="period_id" onchange="this.form.submit()"
                                class="w-full text-sm font-bold rounded-xl border-slate-300 focus:border-purple-600 focus:ring-purple-600">
                            @php
                                $trimestres = $periods->where('type', 'TRIMESTRE')->sortBy('order');
                                $semestres = $periods->where('type', 'SEMESTRE')->sortBy('order');
                            @endphp
                            @if ($trimestres->isNotEmpty())
                                <optgroup label="── TRIMESTRES (Maternelle & Primaire) ──">
                                    @foreach ($trimestres as $p)
                                        <option value="{{ $p->id }}" {{ $selectedPeriodId == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @if ($semestres->isNotEmpty())
                                <optgroup label="── SEMESTRES (Collège & Lycée) ──">
                                    @foreach ($semestres as $p)
                                        <option value="{{ $p->id }}" {{ $selectedPeriodId == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>

                    {{-- Classe (optionnel) --}}
                    <div>
                        <label class="block text-[11px] font-black text-slate-600 uppercase tracking-wider mb-1.5">
                            🏫 Filtrer par Classe
                        </label>
                        <select name="class_id" onchange="this.form.submit()"
                                class="w-full text-sm font-bold rounded-xl border-slate-300 focus:border-purple-600 focus:ring-purple-600">
                            <option value="">— Toutes les classes —</option>
                            @php $classesByCycle = $classes->groupBy(fn($c) => $c->level->cycle->name ?? 'Autre'); @endphp
                            @foreach ($classesByCycle as $cycleName => $cycleClasses)
                                <optgroup label="── {{ strtoupper($cycleName) }} ──">
                                    @foreach ($cycleClasses as $cls)
                                        <option value="{{ $cls->id }}" {{ $selectedClassId == $cls->id ? 'selected' : '' }}>
                                            {{ $cls->level->name }} • {{ $cls->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    {{-- Recherche élève --}}
                    <div>
                        <label class="block text-[11px] font-black text-slate-600 uppercase tracking-wider mb-1.5">
                            🔍 Rechercher un Élève
                        </label>
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Nom, prénom ou matricule..."
                               class="w-full text-sm font-bold rounded-xl border-slate-300 focus:border-purple-600 focus:ring-purple-600" />
                    </div>

                    {{-- Boutons --}}
                    <div class="flex items-center gap-2">
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 bg-purple-900 hover:bg-purple-800 text-white text-sm font-black rounded-xl shadow-sm transition">
                            Filtrer
                        </button>
                        @if ($selectedClassId || $search)
                            <a href="{{ route('report-cards.index', ['period_id' => $selectedPeriodId]) }}"
                               class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-bold rounded-xl transition"
                               title="Réinitialiser">✕</a>
                        @endif
                    </div>
                </form>

                {{-- Stats globales --}}
                @if ($totalBulletins > 0)
                    <div class="grid grid-cols-3 gap-4 mt-5 pt-5 border-t border-slate-100">
                        <div class="text-center">
                            <div class="text-[10px] font-black tracking-widest uppercase text-slate-400">Total Bulletins</div>
                            <div class="text-2xl font-black text-purple-950 mt-1">{{ $totalBulletins }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-[10px] font-black tracking-widest uppercase text-slate-400">Taux de Réussite</div>
                            <div class="text-2xl font-black mt-1 {{ ($totalSuccess / $totalBulletins * 100) >= 50 ? 'text-emerald-700' : 'text-orange-600' }}">
                                {{ round($totalSuccess / $totalBulletins * 100) }}<span class="text-base text-slate-400">%</span>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-[10px] font-black tracking-widest uppercase text-slate-400">Moyenne Générale</div>
                            <div class="text-2xl font-black mt-1 {{ $overallAvg >= 10 ? 'text-emerald-700' : 'text-red-700' }}">
                                {{ number_format($overallAvg, 2) }}<span class="text-base text-slate-400">/20</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ── LISTE GROUPÉE : NIVEAU → CLASSE ────────────────────────── --}}
            <div class="space-y-8">
                @forelse ($grouped as $levelName => $classGroup)
                    @php
                        $firstRcOfLevel  = $classGroup->first()?->first();
                        $cycleName       = $firstRcOfLevel?->schoolClass?->level?->cycle?->name ?? '';
                        $levelTotal      = $classGroup->sum(fn($rcs) => $rcs->count());
                    @endphp

                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">

                        {{-- En-tête Niveau --}}
                        <div class="px-6 py-4 bg-gradient-to-r from-purple-950 to-indigo-950 text-white flex items-center justify-between flex-wrap gap-3">
                            <div class="flex items-center gap-3">
                                <span class="text-xl">🎓</span>
                                <div>
                                    @if ($cycleName)
                                        <div class="text-[10px] font-black tracking-widest uppercase text-amber-400 mb-0.5">
                                            {{ $cycleName }}
                                        </div>
                                    @endif
                                    <h3 class="text-sm font-black uppercase tracking-widest text-white">{{ $levelName }}</h3>
                                    <p class="text-[11px] text-purple-200 font-semibold">
                                        {{ $classGroup->count() }} classe(s) · {{ $levelTotal }} bulletin(s)
                                    </p>
                                </div>
                            </div>
                            <span class="text-xs font-black bg-white/10 text-amber-300 px-3.5 py-1 rounded-full border border-white/10">
                                {{ $levelTotal }} élève(s)
                            </span>
                        </div>

                        {{-- Classes du niveau --}}
                        <div class="divide-y divide-slate-100">
                            @foreach ($classGroup as $className => $reportCards)
                                @php
                                    $firstRc      = $reportCards->first();
                                    $classModelId = $firstRc?->class_id;
                                    $classAvg     = round($reportCards->avg(fn($rc) => (float)$rc->general_average), 2);
                                    $classSuccess = $reportCards->filter(fn($rc) => (float)$rc->general_average >= 10)->count();
                                @endphp

                                <div class="p-6">
                                    {{-- Titre Classe + actions --}}
                                    <div class="flex items-center justify-between flex-wrap gap-3 pb-4 mb-4 border-b border-slate-100">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-base font-black text-purple-950 flex items-center gap-1.5">
                                                🎒 Classe : {{ $className }}
                                            </span>
                                            <span class="text-[11px] font-extrabold text-slate-500 bg-slate-50 px-2.5 py-0.5 rounded-full border border-slate-200">
                                                {{ $reportCards->count() }} élève(s)
                                            </span>
                                            {{-- Moy. classe mini-badge --}}
                                            <span class="text-[11px] font-extrabold px-2.5 py-0.5 rounded-full
                                                {{ $classAvg >= 10 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                                Moy. {{ number_format($classAvg, 2) }}/20
                                            </span>
                                            <span class="text-[11px] font-extrabold text-slate-500 bg-slate-50 px-2.5 py-0.5 rounded-full border border-slate-200">
                                                Réussite : {{ $classSuccess }}/{{ $reportCards->count() }}
                                            </span>
                                        </div>

                                        @if ($classModelId)
                                            <div class="flex items-center gap-2">
                                                {{-- Générer --}}
                                                <form method="POST" action="{{ route('report-cards.generate') }}">
                                                    @csrf
                                                    <input type="hidden" name="class_id" value="{{ $classModelId }}" />
                                                    <input type="hidden" name="period_id" value="{{ $selectedPeriodId }}" />
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1.5 text-xs font-black px-3.5 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-purple-950 shadow-sm active:scale-95 transition-all">
                                                        ⚙️ Générer
                                                    </button>
                                                </form>
                                                {{-- Publier --}}
                                                <form method="POST" action="{{ route('report-cards.publish') }}">
                                                    @csrf
                                                    <input type="hidden" name="class_id" value="{{ $classModelId }}" />
                                                    <input type="hidden" name="period_id" value="{{ $selectedPeriodId }}" />
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1.5 text-xs font-black px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm active:scale-95 transition-all">
                                                        ✅ Publier
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Tableau classement --}}
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-slate-100">
                                            <thead class="bg-slate-50">
                                                <tr>
                                                    <th class="px-4 py-2.5 text-left text-[10px] font-black text-slate-400 uppercase tracking-wider">Rang</th>
                                                    <th class="px-4 py-2.5 text-left text-[10px] font-black text-slate-400 uppercase tracking-wider">Matricule</th>
                                                    <th class="px-4 py-2.5 text-left text-[10px] font-black text-slate-400 uppercase tracking-wider">Élève</th>
                                                    <th class="px-4 py-2.5 text-center text-[10px] font-black text-slate-400 uppercase tracking-wider">Moy. Générale</th>
                                                    <th class="px-4 py-2.5 text-center text-[10px] font-black text-slate-400 uppercase tracking-wider">Mention</th>
                                                    <th class="px-4 py-2.5 text-center text-[10px] font-black text-slate-400 uppercase tracking-wider">Statut</th>
                                                    <th class="px-4 py-2.5 text-right text-[10px] font-black text-slate-400 uppercase tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-slate-100">
                                                @foreach ($reportCards as $rc)
                                                    @php $avg = (float)$rc->general_average; @endphp
                                                    <tr class="hover:bg-purple-50/20 transition-colors">
                                                        <td class="px-4 py-3">
                                                            @if($rc->rank === 1)
                                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-amber-100 text-base">🥇</span>
                                                            @elseif($rc->rank === 2)
                                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-100 text-base">🥈</span>
                                                            @elseif($rc->rank === 3)
                                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-orange-100 text-base">🥉</span>
                                                            @else
                                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-50 text-slate-500 font-extrabold text-sm border border-slate-200">
                                                                    {{ $rc->rank }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <span class="text-xs font-mono font-black text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md">
                                                                {{ $rc->student->matricule }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3 text-sm font-black text-slate-900">
                                                            {{ $rc->student->last_name }} {{ $rc->student->first_name }}
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            <span class="text-lg font-black {{ $avg >= 10 ? 'text-emerald-700' : 'text-red-700' }}">
                                                                {{ number_format($avg, 2) }}
                                                            </span>
                                                            <span class="text-xs font-semibold text-slate-400">/20</span>
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            <span class="px-2.5 py-0.5 text-xs font-black rounded-full
                                                                {{ $avg >= 16 ? 'bg-emerald-100 text-emerald-800' :
                                                                   ($avg >= 12 ? 'bg-blue-100 text-blue-800' :
                                                                   ($avg >= 10 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800')) }}">
                                                                {{ $rc->appreciationLabel }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            @if ($rc->is_published)
                                                                <span class="inline-flex items-center gap-1 text-xs font-black text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span> Publié
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center gap-1 text-xs font-black text-orange-700 bg-orange-50 px-2.5 py-0.5 rounded-full border border-orange-200">
                                                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 inline-block"></span> Brouillon
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 text-right">
                                                            <div class="inline-flex items-center justify-end gap-1.5">
                                                                <a href="{{ route('report-cards.show', $rc) }}"
                                                                   class="inline-flex items-center gap-1 text-xs font-black px-2.5 py-1.5 rounded-xl bg-purple-950 text-white hover:bg-purple-800 active:scale-95 transition-all shadow-sm"
                                                                   title="Voir le bulletin">
                                                                    👁️ Voir
                                                                </a>
                                                                <a href="{{ route('report-cards.pdf', $rc) }}"
                                                                   class="inline-flex items-center gap-1 text-xs font-black px-2.5 py-1.5 rounded-xl bg-red-600 text-white hover:bg-red-700 active:scale-95 transition-all shadow-sm"
                                                                   title="Télécharger le bulletin en PDF">
                                                                    📥 PDF
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-16 text-center">
                        <div class="text-5xl mb-4">📋</div>
                        <h3 class="text-base font-black text-slate-800">Aucun bulletin trouvé</h3>
                        <p class="text-sm text-slate-400 font-medium mt-1 max-w-sm mx-auto">
                            @if ($search || $selectedClassId)
                                Aucun résultat pour ces filtres. <a href="{{ route('report-cards.index', ['period_id' => $selectedPeriodId]) }}" class="text-purple-700 font-black underline">Réinitialiser</a>
                            @else
                                Aucun bulletin généré pour la période <strong>{{ $selectedPeriod?->name }}</strong>. Sélectionnez une classe ci-dessus et cliquez sur ⚙️ Générer.
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>

