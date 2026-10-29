<x-app-layout>
    @php
        $schoolClass = $reportCard->schoolClass;
        $cycle = $schoolClass?->level?->cycle;
        $isMaternelle = $schoolClass?->isMaternelle() ?? false;
        $isPrimaire = $schoolClass?->isPrimaire() ?? false;
        $isCollege = $schoolClass?->isCollege() ?? false;
        $isLycee = $schoolClass?->isLycee() ?? false;
        $isSemester = $schoolClass?->isSemesterBased() ?? false;

        // Configuration visuelle et thématique par cycle
        if ($isMaternelle) {
            $cycleTheme = [
                'name' => 'Enseignement Préscolaire (Maternelle)',
                'subtitle' => 'Bilan Périodique des Acquis & Éveil',
                'badge' => '🧸 Préscolaire',
                'gradient' => 'from-rose-900 via-purple-900 to-amber-950',
                'badge_bg' => 'bg-rose-500/20 text-rose-200 border-rose-400/30',
                'card_border' => 'border-rose-300',
                'accent_text' => 'text-rose-700',
                'accent_bg' => 'bg-rose-50 text-rose-950',
                'table_header' => 'bg-rose-900 text-white',
                'table_highlight' => 'bg-rose-50/70 text-rose-950 font-black',
                'signatory' => 'La Directrice du Préscolaire',
                'teacher_label' => 'Éducatrice titulaire',
                'stamp_border' => 'border-rose-500',
                'stamp_ring' => 'border-rose-600',
                'stamp_text' => 'text-rose-900',
                'stamp_bg' => 'bg-rose-50/50',
            ];
        } elseif ($isPrimaire) {
            $cycleTheme = [
                'name' => 'Enseignement Primaire Élémentaire',
                'subtitle' => 'Bulletin d\'Évaluation Trimestriel',
                'badge' => '🎒 Fondamental 1',
                'gradient' => 'from-emerald-900 via-teal-900 to-slate-950',
                'badge_bg' => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/30',
                'card_border' => 'border-emerald-300',
                'accent_text' => 'text-emerald-700',
                'accent_bg' => 'bg-emerald-50 text-emerald-950',
                'table_header' => 'bg-emerald-900 text-white',
                'table_highlight' => 'bg-emerald-50/70 text-emerald-950 font-black',
                'signatory' => 'Le Directeur de l\'École Primaire',
                'teacher_label' => 'Maître / Maîtresse titulaire',
                'stamp_border' => 'border-emerald-600',
                'stamp_ring' => 'border-emerald-700',
                'stamp_text' => 'text-emerald-950',
                'stamp_bg' => 'bg-emerald-50/50',
            ];
        } elseif ($isCollege) {
            $cycleTheme = [
                'name' => 'Enseignement Secondaire — Premier Cycle',
                'subtitle' => 'Bulletin Officiel Semestriel',
                'badge' => '🏛️ Collège',
                'gradient' => 'from-purple-900 via-indigo-900 to-slate-950',
                'badge_bg' => 'bg-purple-500/20 text-purple-200 border-purple-400/30',
                'card_border' => 'border-purple-300',
                'accent_text' => 'text-purple-700',
                'accent_bg' => 'bg-purple-50 text-purple-950',
                'table_header' => 'bg-purple-900 text-white',
                'table_highlight' => 'bg-purple-50/70 text-purple-950 font-black',
                'signatory' => 'Le Principal du Collège',
                'teacher_label' => 'Professeur principal',
                'stamp_border' => 'border-purple-500',
                'stamp_ring' => 'border-purple-600',
                'stamp_text' => 'text-purple-950',
                'stamp_bg' => 'bg-purple-50/50',
            ];
        } else {
            $cycleTheme = [
                'name' => 'Enseignement Secondaire — Second Cycle',
                'subtitle' => 'Bulletin Officiel Semestriel d\'Excellence',
                'badge' => '🎓 Lycée',
                'gradient' => 'from-indigo-950 via-purple-950 to-slate-950',
                'badge_bg' => 'bg-amber-500/20 text-amber-200 border-amber-400/30',
                'card_border' => 'border-indigo-300',
                'accent_text' => 'text-indigo-700',
                'accent_bg' => 'bg-indigo-50 text-indigo-950',
                'table_header' => 'bg-indigo-950 text-white',
                'table_highlight' => 'bg-indigo-50/70 text-indigo-950 font-black',
                'signatory' => 'Le Proviseur du Lycée',
                'teacher_label' => 'Professeur principal',
                'stamp_border' => 'border-indigo-500',
                'stamp_ring' => 'border-indigo-600',
                'stamp_text' => 'text-indigo-950',
                'stamp_bg' => 'bg-indigo-50/50',
            ];
        }

        $genAvg = (float) $reportCard->general_average;
        $rankFormatted = $reportCard->rank ? $reportCard->rank . ($reportCard->rank == 1 ? 'er' : 'e') : '—';

        $totalCoeff = $totals['total_coefficients'] ?? 0;
        $totalPoints = isset($totals['total_points']) ? number_format((float) $totals['total_points'], 2) : '0,00';
        $moyLit = isset($totals['literary_average']) && $totals['literary_average'] !== null ? number_format((float) $totals['literary_average'], 2) : '—';
        $moySci = isset($totals['scientific_average']) && $totals['scientific_average'] !== null ? number_format((float) $totals['scientific_average'], 2) : '—';

        $sem1 = isset($annualProgression['semester_1']) && $annualProgression['semester_1'] !== null ? number_format((float) $annualProgression['semester_1'], 2) : '—';
        $sem2 = isset($annualProgression['semester_2']) && $annualProgression['semester_2'] !== null ? number_format((float) $annualProgression['semester_2'], 2) : '—';
        $t1 = isset($annualProgression['trimester_1']) && $annualProgression['trimester_1'] !== null ? number_format((float) $annualProgression['trimester_1'], 2) : '—';
        $t2 = isset($annualProgression['trimester_2']) && $annualProgression['trimester_2'] !== null ? number_format((float) $annualProgression['trimester_2'], 2) : '—';
        $t3 = isset($annualProgression['trimester_3']) && $annualProgression['trimester_3'] !== null ? number_format((float) $annualProgression['trimester_3'], 2) : '—';
        $annuelle = isset($annualProgression['annual_average']) && $annualProgression['annual_average'] !== null ? number_format((float) $annualProgression['annual_average'], 2) : '—';

        $forteMoy = isset($classStats['max_average']) && $classStats['max_average'] !== null ? number_format((float) $classStats['max_average'], 2) : '—';
        $faibleMoy = isset($classStats['min_average']) && $classStats['min_average'] !== null ? number_format((float) $classStats['min_average'], 2) : '—';
        $moyClasse = isset($classStats['class_average']) && $classStats['class_average'] !== null ? number_format((float) $classStats['class_average'], 2) : '—';

        $conduiteChoice = $discipline['conduite'] ?? 'Bien';
        $honneurChoice = $discipline['tableau_honneur'] ?? 'Inscrit';
        $travailChoice = $discipline['travail'] ?? 'Remarques';

        $absJust = $attendance['justified_absences'] ?? 0;
        $absNonJust = $attendance['unjustified_absences'] ?? 0;
        $retards = $attendance['late_arrivals'] ?? 0;

        $isOutOfTen = $isMaternelle || $isPrimaire;
        $scaleMax = $isOutOfTen ? 10 : 20;
        $passingMark = $isOutOfTen ? 5.0 : 10.0;
    @endphp

    <x-slot name="header">
        <style>
            @media print {
                header, nav, .print\:hidden, aside { display: none !important; }
                body { background: #ffffff !important; padding: 0 !important; color: #000000 !important; }
                .min-h-screen { min-height: auto !important; }
                .shadow-2xl, .shadow-xl, .shadow-md, .shadow-sm, .shadow-xs { box-shadow: none !important; }
                .rounded-2xl, .rounded-xl { border-radius: 4px !important; }
                .bg-slate-50\/70, .bg-slate-50\/80, .bg-slate-50, .bg-slate-100 { background-color: #ffffff !important; }
                @page { margin: 10mm; size: A4 portrait; }
            }
        </style>
        <div class="flex flex-wrap justify-between items-center gap-3">
            <div class="flex items-center gap-2.5">
                <span class="text-xl">{{ $cycleTheme['badge'] }}</span>
                <div>
                    <h2 class="font-black text-lg text-purple-950 leading-tight">
                        {{ $cycleTheme['subtitle'] }} — {{ $reportCard->period->name }}
                    </h2>
                    <p class="text-xs text-slate-500 font-semibold">
                        {{ $cycleTheme['name'] }} · {{ $reportCard->period->academicYear->name }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 print:hidden">
                <a href="{{ route('report-cards.pdf', $reportCard) }}"
                   target="_blank"
                   id="btn-download-pdf"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white text-xs font-black rounded-xl shadow-sm transition active:scale-95">
                    <span>📄</span>
                    <span>Télécharger PDF Officiel</span>
                </a>
                <button type="button"
                        id="btn-print-bulletin"
                        onclick="window.print()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-purple-900 hover:bg-purple-800 text-white text-xs font-black rounded-xl shadow-sm transition active:scale-95">
                    <span>🖨️</span>
                    <span>Imprimer</span>
                </button>
                <a href="{{ route('report-cards.index', ['class_id' => $reportCard->class_id, 'period_id' => $reportCard->period_id]) }}"
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 hover:text-purple-900 bg-white border border-slate-200 px-3 py-2 rounded-xl shadow-xs transition">
                    <span>←</span>
                    <span>Retour</span>
                </a>
            </div>
        </div>
    </x-slot>

    {{-- CONTENEUR PRINCIPAL AVEC LARGEUR ET LONGUEUR MAÎTRISÉES (max-w-4xl = 896px) --}}
    <div class="py-6 bg-slate-100/80 min-h-screen">
        <div class="max-w-4xl mx-auto px-3 sm:px-6 space-y-4">

            {{-- CARTE DU BULLETIN COMPACTE ET ÉLÉGANTE --}}
            <div class="bg-white shadow-lg rounded-2xl overflow-hidden border {{ $cycleTheme['card_border'] }}">

                {{-- 1. EN-TÊTE COMPACT OFFICIEL --}}
                <div class="bg-gradient-to-r {{ $cycleTheme['gradient'] }} text-white p-4 sm:p-5 relative overflow-hidden">
                    <div class="relative z-10 flex flex-wrap justify-between items-center gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-md p-1 border border-white/20 flex items-center justify-center shadow-md flex-shrink-0">
                                @php
                                    $logoPath = public_path('images/logo_roi.jpeg');
                                @endphp
                                @if (file_exists($logoPath))
                                    <img src="{{ asset('images/logo_roi.jpeg') }}" alt="Logo CSP Les Rois du Sahel" class="w-full h-full object-contain rounded-lg">
                                @else
                                    <span class="text-2xl">👑</span>
                                @endif
                            </div>
                            <div>
                                <div class="text-[10px] font-black tracking-widest uppercase text-amber-300">
                                    République du Niger • Complexe Scolaire Privé
                                </div>
                                <h1 class="text-lg sm:text-xl font-black tracking-tight text-white leading-tight">
                                    LES ROIS DU SAHEL
                                </h1>
                                <div class="text-[11px] text-slate-200 font-medium flex items-center gap-2 mt-0.5">
                                    <span>📍 Koubia Plateau · Niamey</span>
                                    <span>•</span>
                                    <span class="text-amber-300 font-bold">{{ $cycleTheme['name'] }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Période & Année --}}
                        <div class="text-right bg-white/10 backdrop-blur-md px-3.5 py-2 rounded-xl border border-white/20 shadow-inner">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider {{ $cycleTheme['badge_bg'] }} border mb-0.5">
                                {{ $cycleTheme['badge'] }}
                            </span>
                            <div class="text-sm font-black text-white">{{ $reportCard->period->name }}</div>
                            <div class="text-[11px] text-amber-300 font-bold">{{ $reportCard->period->academicYear->name }}</div>
                        </div>
                    </div>

                    {{-- Devise Officielle --}}
                    <div class="mt-3 pt-2.5 border-t border-white/15 flex flex-wrap justify-between items-center text-[10px] font-bold gap-2">
                        <div class="text-amber-300 tracking-wider uppercase">
                            ★ DISCIPLINE • TRAVAIL • SUCCÈS ★
                        </div>
                        <div class="text-purple-200">
                            Statut : <span class="font-black {{ $reportCard->is_published ? 'text-emerald-300' : 'text-amber-300' }}">{{ $reportCard->is_published ? 'Document Officiel Publié' : 'Brouillon provisoire' }}</span>
                        </div>
                    </div>
                </div>

                {{-- 2. INFORMATIONS ÉLÈVE ET CLASSE (GRILLE COMPACTE ET STRUCTURÉE) --}}
                <div class="p-3.5 bg-slate-50 border-b border-slate-200 text-xs">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <div class="bg-white p-2.5 rounded-xl border border-slate-200 shadow-2xs">
                            <span class="text-[10px] font-black uppercase text-slate-400 block tracking-wider">Élève</span>
                            <strong class="text-xs text-slate-900 block truncate mt-0.5">
                                {{ strtoupper($reportCard->student->last_name) }} {{ $reportCard->student->first_name }}
                            </strong>
                            <span class="text-[10px] text-purple-700 font-mono font-bold block mt-0.5">
                                N° {{ $reportCard->student->matricule }} ({{ $reportCard->student->gender ?? '—' }})
                            </span>
                        </div>

                        <div class="bg-white p-2.5 rounded-xl border border-slate-200 shadow-2xs">
                            <span class="text-[10px] font-black uppercase text-slate-400 block tracking-wider">Classe & Effectif</span>
                            <strong class="text-xs text-slate-900 block truncate mt-0.5">
                                {{ $reportCard->schoolClass->name }}
                            </strong>
                            <span class="text-[10px] text-slate-500 font-semibold block mt-0.5">
                                {{ $reportCard->total_students }} élèves · {{ $reportCard->schoolClass->level->name }}
                            </span>
                        </div>

                        <div class="bg-white p-2.5 rounded-xl border border-slate-200 shadow-2xs">
                            <span class="text-[10px] font-black uppercase text-slate-400 block tracking-wider">{{ $cycleTheme['teacher_label'] }}</span>
                            <strong class="text-xs text-slate-900 block truncate mt-0.5">
                                {{ $reportCard->schoolClass->mainTeacher?->name ?? 'Direction pédagogique' }}
                            </strong>
                            <span class="text-[10px] text-slate-500 font-semibold block mt-0.5">
                                Responsable de classe
                            </span>
                        </div>

                        <div class="bg-white p-2.5 rounded-xl border border-slate-200 shadow-2xs">
                            <span class="text-[10px] font-black uppercase text-slate-400 block tracking-wider">État Civil</span>
                            <strong class="text-xs text-slate-800 block truncate mt-0.5">
                                Né(e) le : {{ $reportCard->student->birth_date ? $reportCard->student->birth_date->format('d/m/Y') : '—' }}
                            </strong>
                            <span class="text-[10px] text-slate-500 font-semibold block mt-0.5 truncate">
                                À : {{ $reportCard->student->birth_place ?? 'Niamey' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 3. 4 CARTES KPI COMPACTES (MOYENNE, RANG, CLASSE, DISCIPLINE) --}}
                <div class="px-3.5 py-3 grid grid-cols-2 sm:grid-cols-4 gap-2.5 bg-white border-b border-slate-200">
                    {{-- Moyenne Générale --}}
                    <div class="p-2.5 rounded-xl border {{ $genAvg >= $passingMark ? 'bg-emerald-50/70 border-emerald-200' : 'bg-red-50/70 border-red-200' }}">
                        <span class="text-[9px] font-black uppercase tracking-wider text-slate-500 block">Moyenne Générale</span>
                        <div class="text-xl font-black mt-0.5 {{ $genAvg >= $passingMark ? 'text-emerald-700' : 'text-red-700' }}">
                            {{ number_format($genAvg, 2) }}
                            <span class="text-[10px] font-bold text-slate-400">/{{ $scaleMax }}</span>
                        </div>
                        <span class="inline-block text-[9px] font-extrabold px-1.5 py-0.2 rounded mt-0.5 {{ $genAvg >= $passingMark ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                            {{ $genAvg >= $passingMark ? 'Admis(e)' : 'Insuffisant' }}
                        </span>
                    </div>

                    {{-- Rang --}}
                    <div class="p-2.5 rounded-xl border bg-amber-50/70 border-amber-200">
                        <span class="text-[9px] font-black uppercase tracking-wider text-slate-500 block">Rang dans la classe</span>
                        <div class="text-xl font-black mt-0.5 text-amber-700 flex items-center gap-1">
                            <span>{{ $rankFormatted }}</span>
                            @if ($reportCard->rank === 1)
                                <span class="text-base">🥇</span>
                            @elseif ($reportCard->rank === 2)
                                <span class="text-base">🥈</span>
                            @elseif ($reportCard->rank === 3)
                                <span class="text-base">🥉</span>
                            @endif
                        </div>
                        <span class="text-[9px] font-bold text-amber-800 block mt-0.5">
                            Sur {{ $reportCard->total_students }} élèves
                        </span>
                    </div>

                    {{-- Statistiques Classe --}}
                    <div class="p-2.5 rounded-xl border bg-slate-50 border-slate-200">
                        <span class="text-[9px] font-black uppercase tracking-wider text-slate-500 block">Moyenne de classe</span>
                        <div class="text-lg font-black mt-0.5 text-slate-900">
                            {{ $moyClasse }} <span class="text-[9px] font-bold text-slate-400">/{{ $scaleMax }}</span>
                        </div>
                        <span class="text-[9px] text-slate-600 font-semibold block mt-0.5">
                            Min: <strong class="text-red-700">{{ $faibleMoy }}</strong> • Max: <strong class="text-emerald-700">{{ $forteMoy }}</strong>
                        </span>
                    </div>

                    {{-- Assiduité & Conduite --}}
                    <div class="p-2.5 rounded-xl border bg-purple-50/70 border-purple-200">
                        <span class="text-[9px] font-black uppercase tracking-wider text-slate-500 block">Assiduité & Conduite</span>
                        <div class="text-sm font-black mt-0.5 text-purple-950 truncate">
                            {{ $absNonJust }} abs. non just.
                        </div>
                        <span class="text-[9px] font-bold text-purple-800 block mt-0.5">
                            {{ $retards }} retard(s) · {{ $conduiteChoice }}
                        </span>
                    </div>
                </div>

                {{-- ========================================================================= --}}
                {{-- 4. TABLEAU DES MATIÈRES ET NOTES (TABLE RIGIDE, BORDURES TRACÉES, SANS DÉBORDEMENT) --}}
                {{-- ========================================================================= --}}
                <div class="p-3.5">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                            <span>📊</span>
                            <span>Détail des Performances par Matière</span>
                        </h3>
                        <span class="text-[11px] font-bold text-slate-500">
                            Barème officiel sur {{ $scaleMax }} points
                        </span>
                    </div>

                    @if ($isMaternelle)
                        {{-- ── 1. MODÈLE MATERNELLE / PRÉSCOLAIRE (Table tracée, sans débordement) ── --}}
                        <div class="rounded-xl border border-rose-300 overflow-hidden">
                            <table class="w-full table-fixed border-collapse text-left text-xs">
                                <thead>
                                    <tr class="{{ $cycleTheme['table_header'] }} text-[10px] uppercase tracking-wider">
                                        <th style="width: 42%;" class="px-3 py-2 font-black border-r border-rose-800">Domaines d'Activités &amp; Éveil</th>
                                        <th style="width: 14%;" class="px-2 py-2 text-center font-black border-r border-rose-800">Note /10</th>
                                        <th style="width: 22%;" class="px-2 py-2 text-center font-black border-r border-rose-800">Niveau d'Acquisition</th>
                                        <th style="width: 22%;" class="px-3 py-2 text-left font-black">Observations de l'Éducatrice</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-rose-200 text-xs text-slate-800 bg-white">
                                    @forelse ($subjectDetails as $detail)
                                        @php
                                            $ms = $detail['ms'] !== null ? (float) $detail['ms'] : null;
                                        @endphp
                                        <tr class="hover:bg-rose-50/40 transition">
                                            <td class="px-3 py-1.5 font-bold text-slate-900 border-r border-slate-200 truncate">
                                                {{ $detail['subject'] }}
                                            </td>
                                            <td class="px-2 py-1.5 text-center font-black text-rose-950 border-r border-slate-200">
                                                {{ $ms !== null ? number_format($ms, 2) : '—' }}
                                            </td>
                                            <td class="px-2 py-1.5 text-center border-r border-slate-200 text-[11px]">
                                                @if($ms !== null)
                                                    {{ $ms >= 8 ? 'Très Bien Acquis' : ($ms >= 6 ? 'Bien Acquis' : ($ms >= 5 ? 'En voie' : 'À encourager')) }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="px-3 py-1.5 text-slate-700 text-[11px] truncate">
                                                {{ $detail['appreciation'] ?? 'Participe activement' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-4 text-center text-slate-500 font-medium">
                                                Aucune note enregistrée pour cette période.
                                            </td>
                                        </tr>
                                    @endforelse

                                    <tr class="bg-rose-100/80 font-black text-rose-950 border-t-2 border-rose-300">
                                        <td class="px-3 py-2 uppercase text-[11px] border-r border-rose-300">MOYENNE GLOBALE D'ÉVEIL</td>
                                        <td class="px-2 py-2 text-center text-sm font-black text-rose-950 border-r border-rose-300">
                                            {{ number_format($genAvg, 2) }}
                                        </td>
                                        <td class="px-2 py-2 text-center text-[11px] border-r border-rose-300">Rang : {{ $rankFormatted }}</td>
                                        <td class="px-3 py-2 text-xs font-bold text-rose-900">{{ $reportCard->appreciationLabel }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    @elseif ($isPrimaire)
                        {{-- ── 2. MODÈLE PRIMAIRE (Note de composition unique) ── --}}
                        <div class="rounded-xl border border-emerald-300 overflow-hidden">
                            <table class="w-full table-fixed border-collapse text-left text-xs">
                                <thead>
                                    <tr class="{{ $cycleTheme['table_header'] }} text-[10px] uppercase tracking-wider">
                                        <th style="width: 36%;" class="px-3 py-2 font-black border-r border-emerald-800">Matières Enseignées</th>
                                        <th style="width: 14%;" class="px-2 py-2 text-center font-black bg-teal-950 border-r border-emerald-800">Composition /10</th>
                                        <th style="width: 10%;" class="px-1 py-2 text-center font-black border-r border-emerald-800">COEF</th>
                                        <th style="width: 12%;" class="px-1.5 py-2 text-center font-black border-r border-emerald-800">Total Points</th>
                                        <th style="width: 10%;" class="px-1 py-2 text-center font-black border-r border-emerald-800">Rang</th>
                                        <th style="width: 18%;" class="px-2 py-2 text-center font-black">Appréciation</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 text-xs text-slate-800 bg-white">
                                    @forelse ($subjectDetails as $detail)
                                        @php
                                            $compo = $detail['nc'] !== null ? (float)$detail['nc'] : ($detail['ms'] !== null ? (float)$detail['ms'] : null);
                                        @endphp
                                        <tr class="{{ $compo !== null && $compo < 5 ? 'bg-red-50/40' : 'hover:bg-slate-50' }} transition">
                                            <td class="px-3 py-1.5 font-bold text-slate-900 border-r border-slate-200 truncate">
                                                {{ $detail['subject'] }}
                                            </td>
                                            <td class="px-2 py-1.5 text-center font-black border-r border-slate-200 {{ $compo !== null && $compo >= 5 ? 'text-emerald-950 bg-emerald-50/60' : 'text-red-700 bg-red-50/60' }}">
                                                {{ $compo !== null ? number_format($compo, 2) : '—' }}
                                            </td>
                                            <td class="px-1 py-1.5 text-center text-slate-700 font-bold border-r border-slate-200">
                                                {{ $detail['coefficient'] }}
                                            </td>
                                            <td class="px-1.5 py-1.5 text-center font-bold text-slate-900 border-r border-slate-200">
                                                {{ $detail['weighted'] !== null ? number_format((float)$detail['weighted'], 2) : '—' }}
                                            </td>
                                            <td class="px-1 py-1.5 text-center font-black text-amber-700 border-r border-slate-200">
                                                {{ $detail['rank'] ? $detail['rank'] . ($detail['rank'] == 1 ? 'er' : 'e') : '—' }}
                                            </td>
                                            <td class="px-2 py-1.5 text-center text-slate-700 text-[11px] truncate">
                                                {{ $detail['appreciation'] }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-4 text-center text-slate-500 font-medium">
                                                Aucune note enregistrée pour cette période.
                                            </td>
                                        </tr>
                                    @endforelse

                                    <!-- Total Général Primaire -->
                                    <tr class="bg-emerald-100/80 font-black text-emerald-950 border-t-2 border-emerald-300">
                                        <td class="px-3 py-2 uppercase text-[11px] text-right font-black border-r border-emerald-300">TOTAL / MOYENNE :</td>
                                        <td class="px-2 py-2 text-center text-sm font-black text-emerald-950 border-r border-emerald-300">
                                            {{ number_format($genAvg, 2) }}
                                        </td>
                                        <td class="px-1 py-2 text-center border-r border-emerald-300">{{ $totalCoeff }}</td>
                                        <td class="px-1.5 py-2 text-center border-r border-emerald-300">{{ $totalPoints }}</td>
                                        <td class="px-1 py-2 text-center text-amber-800 font-black border-r border-emerald-300">{{ $rankFormatted }}</td>
                                        <td class="px-2 py-2 text-center text-[11px] font-bold text-emerald-900 truncate">
                                            {{ $reportCard->appreciationLabel }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    @else
                        {{-- ── 3. MODÈLE COLLÈGE & LYCÉE (Table rigide 9 colonnes, sous-totaux alignés, bordures tracées) ── --}}
                        <div class="rounded-xl border border-purple-300 overflow-hidden">
                            <table class="w-full table-fixed border-collapse text-left text-xs">
                                <thead>
                                    <tr class="{{ $cycleTheme['table_header'] }} text-[10px] uppercase tracking-wider">
                                        <th style="width: 26%;" class="px-3 py-2 font-black border-r border-purple-800">Matières Enseignées</th>
                                        <th style="width: 8%;" class="px-1 py-2 text-center font-black border-r border-purple-800" title="Moyenne de Classe">MC /20</th>
                                        <th style="width: 8%;" class="px-1 py-2 text-center font-black border-r border-purple-800" title="Note de Composition">NC /20</th>
                                        <th style="width: 8%;" class="px-1 py-2 text-center font-black bg-purple-950 border-r border-purple-800" title="Moyenne Semestrielle">MS /20</th>
                                        <th style="width: 6%;" class="px-1 py-2 text-center font-black border-r border-purple-800" title="Coefficient">COEF</th>
                                        <th style="width: 10%;" class="px-1 py-2 text-center font-black border-r border-purple-800" title="Total Points">MS · COEF</th>
                                        <th style="width: 7%;" class="px-1 py-2 text-center font-black border-r border-purple-800">RANG</th>
                                        <th style="width: 14%;" class="px-2 py-2 text-center font-black border-r border-purple-800">Appréciation</th>
                                        <th style="width: 13%;" class="px-2 py-2 text-center font-black">Professeur</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 text-xs text-slate-800 bg-white">
                                    @forelse ($subjectDetails as $detail)
                                        @php
                                            $ms = $detail['ms'] !== null ? (float) $detail['ms'] : null;
                                        @endphp
                                        <tr class="{{ $ms !== null && $ms < 10 ? 'bg-red-50/40' : 'hover:bg-purple-50/30' }} transition">
                                            <td class="px-3 py-1.5 font-bold text-slate-900 border-r border-slate-200 truncate">
                                                {{ $detail['subject'] }}
                                            </td>
                                            <td class="px-1 py-1.5 text-center text-slate-600 border-r border-slate-200">
                                                {{ $detail['mc'] !== null ? number_format((float)$detail['mc'], 2) : '—' }}
                                            </td>
                                            <td class="px-1 py-1.5 text-center text-slate-600 border-r border-slate-200">
                                                {{ $detail['nc'] !== null ? number_format((float)$detail['nc'], 2) : '—' }}
                                            </td>
                                            <td class="px-1 py-1.5 text-center font-black border-r border-slate-200 {{ $ms !== null && $ms >= 10 ? 'text-purple-950 bg-purple-50/60' : 'text-red-700 bg-red-50/60' }}">
                                                {{ $ms !== null ? number_format($ms, 2) : '—' }}
                                            </td>
                                            <td class="px-1 py-1.5 text-center text-slate-700 font-bold border-r border-slate-200">
                                                {{ $detail['coefficient'] }}
                                            </td>
                                            <td class="px-1 py-1.5 text-center font-bold text-slate-900 border-r border-slate-200">
                                                {{ $detail['weighted'] !== null ? number_format((float)$detail['weighted'], 2) : '—' }}
                                            </td>
                                            <td class="px-1 py-1.5 text-center font-black text-amber-600 border-r border-slate-200">
                                                {{ $detail['rank'] ? $detail['rank'] . ($detail['rank'] == 1 ? 'er' : 'e') : '—' }}
                                            </td>
                                            <td class="px-2 py-1.5 text-center font-bold text-slate-700 border-r border-slate-200 text-[11px] truncate">
                                                {{ $detail['appreciation'] }}
                                            </td>
                                            <td class="px-2 py-1.5 text-center text-slate-500 text-[10px] truncate">
                                                {{ $detail['teacher'] ?? '—' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="px-4 py-4 text-center text-slate-500 font-medium">
                                                Aucune note enregistrée pour cette période.
                                            </td>
                                        </tr>
                                    @endforelse

                                    {{-- Sous-totaux officiels nigériens --}}
                                    <tr class="bg-purple-50/70 font-bold text-purple-950 border-t border-purple-200">
                                        <td colspan="2" rowspan="2" class="px-3 py-2 text-center align-middle font-black uppercase text-xs border-r border-purple-300">
                                            Total
                                        </td>
                                        <td class="px-2 py-1.5 text-left font-black text-[11px] border-r border-purple-300 bg-purple-100/60">
                                            Moy littér:
                                        </td>
                                        <td class="px-1 py-1.5 text-center font-black text-xs text-purple-950 border-r border-purple-300">
                                            {{ $moyLit }}
                                        </td>
                                        <td rowspan="2" class="px-1 py-2 text-center align-middle font-black text-xs border-r border-purple-300 bg-purple-100/50">
                                            {{ $totalCoeff }}
                                        </td>
                                        <td rowspan="2" class="px-1 py-2 text-center align-middle font-black text-xs border-r border-purple-300 bg-purple-100/50">
                                            {{ $totalPoints }}
                                        </td>
                                        <td rowspan="2" class="border-r border-purple-300"></td>
                                        <td rowspan="2" class="border-r border-purple-300"></td>
                                        <td rowspan="2"></td>
                                    </tr>
                                    <tr class="bg-purple-50/70 font-bold text-purple-950 border-t border-purple-200">
                                        <td class="px-2 py-1.5 text-left font-black text-[11px] border-r border-purple-300 bg-purple-100/60">
                                            Moy scient:
                                        </td>
                                        <td class="px-1 py-1.5 text-center font-black text-xs text-purple-950 border-r border-purple-300">
                                            {{ $moySci }}
                                        </td>
                                    </tr>

                                    <!-- Ligne Total Général Récapitulatif -->
                                    <tr class="bg-purple-950 text-white font-black text-xs border-t-2 border-purple-900">
                                        <td colspan="3" class="px-3 py-2 uppercase tracking-wider text-center border-r border-purple-800">
                                            Bilan Général
                                        </td>
                                        <td class="px-1 py-2 text-center text-amber-300 font-black text-xs border-r border-purple-800">
                                            {{ number_format($genAvg, 2) }}
                                        </td>
                                        <td class="px-1 py-2 text-center text-amber-300 border-r border-purple-800">{{ $totalCoeff }}</td>
                                        <td class="px-1 py-2 text-center text-amber-300 border-r border-purple-800">{{ $totalPoints }}</td>
                                        <td class="px-1 py-2 text-center text-amber-300 font-black border-r border-purple-800">{{ $rankFormatted }}</td>
                                        <td colspan="2" class="px-3 py-2 text-center text-[11px] font-bold text-purple-200 truncate">
                                            {{ $reportCard->appreciationLabel }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- 5. PANNEAUX DU BAS : PROGRESSION, DISCIPLINE & SIGNATURE (GRILLE COMPACTE) --}}
                <div class="p-3.5 bg-slate-50 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-3">

                    {{-- 1. Bilan & Progression Annuelle --}}
                    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-2xs flex flex-col justify-between">
                        <div>
                            <div class="text-[10px] font-black uppercase text-purple-950 tracking-wider flex items-center gap-1.5 mb-2">
                                <span>📅</span>
                                <span>Progression Annuelle</span>
                            </div>

                            @if ($isSemester)
                                <div class="grid grid-cols-3 gap-1.5 text-center">
                                    <div class="p-1.5 rounded-lg {{ $reportCard->period->order == 1 ? 'bg-purple-100 border border-purple-300' : 'bg-slate-50' }}">
                                        <div class="text-[9px] font-bold uppercase text-slate-500">1er Sem.</div>
                                        <div class="text-xs font-black text-slate-900 mt-0.5">{{ $sem1 }}</div>
                                    </div>
                                    <div class="p-1.5 rounded-lg {{ $reportCard->period->order == 2 ? 'bg-purple-100 border border-purple-300' : 'bg-slate-50' }}">
                                        <div class="text-[9px] font-bold uppercase text-slate-500">2e Sem.</div>
                                        <div class="text-xs font-black text-slate-900 mt-0.5">{{ $sem2 }}</div>
                                    </div>
                                    <div class="p-1.5 rounded-lg bg-amber-50 border border-amber-200">
                                        <div class="text-[9px] font-bold uppercase text-amber-800">Annuelle</div>
                                        <div class="text-xs font-black text-amber-900 mt-0.5">{{ $annuelle }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-4 gap-1 text-center">
                                    <div class="p-1.5 rounded-lg {{ $reportCard->period->order == 1 ? 'bg-emerald-100 border border-emerald-300' : 'bg-slate-50' }}">
                                        <div class="text-[8px] font-bold uppercase text-slate-500">T1</div>
                                        <div class="text-xs font-black text-slate-900">{{ $t1 }}</div>
                                    </div>
                                    <div class="p-1.5 rounded-lg {{ $reportCard->period->order == 2 ? 'bg-emerald-100 border border-emerald-300' : 'bg-slate-50' }}">
                                        <div class="text-[8px] font-bold uppercase text-slate-500">T2</div>
                                        <div class="text-xs font-black text-slate-900">{{ $t2 }}</div>
                                    </div>
                                    <div class="p-1.5 rounded-lg {{ $reportCard->period->order == 3 ? 'bg-emerald-100 border border-emerald-300' : 'bg-slate-50' }}">
                                        <div class="text-[8px] font-bold uppercase text-slate-500">T3</div>
                                        <div class="text-xs font-black text-slate-900">{{ $t3 }}</div>
                                    </div>
                                    <div class="p-1.5 rounded-lg bg-amber-50 border border-amber-200">
                                        <div class="text-[8px] font-bold uppercase text-amber-800">Bilan</div>
                                        <div class="text-xs font-black text-amber-900">{{ $annuelle }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mt-2.5 pt-2 border-t border-slate-100 text-[10px] text-slate-500 font-semibold flex justify-between">
                            <span>Décision Conseil :</span>
                            <span class="font-black text-purple-950">{{ $reportCard->isPassing ? 'Admis(e)' : 'Doit progresser' }}</span>
                        </div>
                    </div>

                    {{-- 2. Assiduité & Discipline --}}
                    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-2xs flex flex-col justify-between">
                        <div>
                            <div class="text-[10px] font-black uppercase text-purple-950 tracking-wider flex items-center gap-1.5 mb-2">
                                <span>📋</span>
                                <span>Discipline & Assiduité</span>
                            </div>

                            <div class="space-y-1 text-xs">
                                <div class="flex justify-between items-center py-0.5 border-b border-slate-100">
                                    <span class="text-slate-500 text-[11px]">Absences justifiées :</span>
                                    <strong class="text-slate-900 px-1.5 py-0.2 rounded bg-slate-100 text-xs">{{ $absJust }}</strong>
                                </div>
                                <div class="flex justify-between items-center py-0.5 border-b border-slate-100">
                                    <span class="text-slate-500 text-[11px]">Absences non justifiées :</span>
                                    <strong class="{{ $absNonJust > 0 ? 'text-red-700 bg-red-100' : 'text-slate-900 bg-slate-100' }} px-1.5 py-0.2 rounded text-xs">
                                        {{ $absNonJust }}
                                    </strong>
                                </div>
                                <div class="flex justify-between items-center py-0.5 border-b border-slate-100">
                                    <span class="text-slate-500 text-[11px]">Retards :</span>
                                    <strong class="text-amber-700 px-1.5 py-0.2 rounded bg-amber-50 text-xs">{{ $retards }}</strong>
                                </div>
                                <div class="flex justify-between items-center py-0.5">
                                    <span class="text-slate-500 text-[11px]">Tableau d'Honneur :</span>
                                    <strong class="text-purple-900 text-xs">{{ $honneurChoice }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2.5 pt-2 border-t border-slate-100 text-[10px] text-slate-500 font-semibold flex justify-between">
                            <span>Conduite :</span>
                            <span class="font-black text-emerald-700">{{ $conduiteChoice }}</span>
                        </div>
                    </div>

                    {{-- 3. Visa & Cachet Officiel du Chef d'Établissement --}}
                    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-2xs flex flex-col justify-between text-center">
                        <div>
                            <div class="text-[10px] font-black uppercase text-purple-950 tracking-wider mb-1">
                                Visa &amp; Cachet Officiel
                            </div>
                            <div class="text-[11px] font-bold text-slate-600">
                                {{ $cycleTheme['signatory'] }}
                            </div>

                            {{-- Cachet Numérique Thématique Compact --}}
                            <div class="my-2 mx-auto w-18 h-18 rounded-full border-2 border-dashed {{ $cycleTheme['stamp_border'] }} p-1 flex items-center justify-center rotate-3 shadow-2xs {{ $cycleTheme['stamp_bg'] }}">
                                <div class="w-full h-full rounded-full border {{ $cycleTheme['stamp_ring'] }} flex flex-col items-center justify-center p-1 text-[7px] font-black {{ $cycleTheme['stamp_text'] }} leading-tight">
                                    <span class="tracking-wider">ROIS DU SAHEL</span>
                                    <span class="text-[9px] text-amber-500">★ ★ ★</span>
                                    <span>VU &amp; APPROUVÉ</span>
                                    <span class="text-[6px] font-bold opacity-80">{{ date('Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-1.5 border-t border-slate-100 text-[10px] text-slate-500 font-medium">
                            Niamey, le {{ today()->format('d/m/Y') }}
                        </div>
                    </div>
                </div>

                {{-- 6. FOOTER DU BULLETIN : APPRÉCIATION GLOBALE COMPACTE --}}
                <div class="px-4 py-3 bg-gradient-to-r {{ $cycleTheme['gradient'] }} text-white flex flex-wrap justify-between items-center gap-2">
                    <div>
                        <div class="text-[9px] font-black uppercase tracking-wider text-amber-300">
                            Appréciation Globale du Chef d'Établissement
                        </div>
                        <div class="text-sm font-black text-white mt-0.5">
                            « {{ $reportCard->appreciation ?? $reportCard->appreciationLabel }} »
                        </div>
                    </div>

                    <div class="text-right">
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-extrabold {{ $reportCard->is_published ? 'bg-emerald-400 text-purple-950' : 'bg-amber-400 text-purple-950' }}">
                            {{ $reportCard->is_published ? '✅ Document Officiel Validé' : '📝 Brouillon' }}
                        </span>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
