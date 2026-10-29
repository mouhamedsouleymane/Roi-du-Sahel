<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bulletin — {{ $reportCard->student?->matricule }} — {{ $reportCard->student?->last_name }} {{ $reportCard->student?->first_name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 11mm 8mm 11mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7.8pt;
            color: #000000;
            background: #ffffff;
            line-height: 9.5pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        td, th {
            vertical-align: middle;
        }

        .b { font-weight: bold; }
        .c { text-align: center; }
        .r { text-align: right; }
        .l { text-align: left; }

        /* ---------- En-tête ---------- */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3pt;
        }

        .header-table td {
            vertical-align: top;
            border: none;
        }

        .hd-line-bold {
            font-size: 7.2pt;
            font-weight: bold;
            color: #000000;
            line-height: 8.8pt;
        }

        .hd-line {
            font-size: 7.2pt;
            color: #000000;
            line-height: 8.8pt;
        }

        .hd-year {
            font-size: 8pt;
            font-weight: bold;
            color: #000000;
            text-align: right;
            line-height: 9.5pt;
        }

        .hd-logo {
            text-align: center;
            vertical-align: middle;
        }

        .hd-logo img {
            width: 46pt;
            height: 46pt;
        }

        /* ---------- Titre ---------- */
        .bulletin-title {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            line-height: 12pt;
            color: #000000;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            margin-top: 2pt;
            margin-bottom: 6pt;
        }

        /* ---------- Identité de l'élève ---------- */
        .id-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5pt;
        }

        .id-table td {
            border: 1px solid #000000;
            padding: 2.2pt 4pt;
            font-size: 7.8pt;
            line-height: 9.5pt;
            background-color: #ffffff;
            color: #000000;
        }

        .id-table td.lbl {
            font-weight: bold;
        }

        .matricule-row {
            font-size: 8pt;
            margin-bottom: 5pt;
            padding-left: 2pt;
            line-height: 11pt;
        }

        .matricule-row span.lbl {
            font-weight: bold;
            margin-right: 25pt;
        }

        /* ---------- Tableau des notes ---------- */
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6pt;
        }

        .grades-table th {
            border: 1px solid #000000;
            background-color: #e5e7eb;
            font-size: 7.5pt;
            font-weight: bold;
            text-align: center;
            line-height: 9pt;
            padding: 2.5pt 2pt;
            color: #000000;
        }

        .grades-table td {
            border: 1px solid #000000;
            padding: 1.8pt 2pt;
            font-size: 7.8pt;
            line-height: 9.2pt;
            text-align: center;
            background-color: #ffffff;
            color: #000000;
        }

        .grades-table td.l {
            text-align: left;
            padding-left: 4pt;
        }

        .grades-table tr.total-row td {
            background-color: #ffffff;
            padding: 1.8pt 2pt;
        }

        /* ---------- Moyenne générale & Statistiques ---------- */
        .stats-section {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6pt;
        }

        .stats-section td {
            vertical-align: top;
            border: none;
            padding: 0;
        }

        .avg-box-title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            font-size: 7.8pt;
            margin-bottom: 2pt;
            line-height: 9.5pt;
        }

        .avg-table {
            width: 100%;
            border-collapse: collapse;
        }

        .avg-table th {
            background-color: #e5e7eb;
            border: 1px solid #000000;
            font-size: 7.5pt;
            font-weight: bold;
            text-align: center;
            line-height: 8.5pt;
            padding: 2.2pt 2pt;
            color: #000000;
        }

        .avg-table td {
            border: 1px solid #000000;
            font-size: 8pt;
            line-height: 9.5pt;
            padding: 2.5pt 2pt;
            text-align: center;
            color: #000000;
            background-color: #ffffff;
        }

        .stat-table {
            width: 100%;
            border-collapse: collapse;
        }

        .stat-table td {
            border: 1px solid #000000;
            font-size: 7.8pt;
            line-height: 9.2pt;
            padding: 2pt 4pt;
            background-color: #e5e7eb;
            color: #000000;
        }

        .stat-table td.stat-lbl {
            font-weight: bold;
            text-align: left;
        }

        .stat-table td.stat-val {
            font-weight: bold;
            text-align: right;
        }

        /* ---------- 4 Panneaux discipline ---------- */
        .panels-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .panels-table th {
            background-color: #e5e7eb;
            border: 1px solid #000000;
            font-size: 7.5pt;
            font-weight: bold;
            text-align: center;
            line-height: 8.8pt;
            padding: 2.2pt 2pt;
            color: #000000;
        }

        .panels-table td.panel-cell {
            border: 1px solid #000000;
            background-color: #ffffff;
            vertical-align: top;
            padding: 2.5pt 3pt;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
        }

        .item-table td {
            border: none;
            font-size: 7.4pt;
            line-height: 8.8pt;
            padding: 0.6pt 0;
            color: #000000;
        }

        .item-table td.cb {
            text-align: right;
            width: 16pt;
        }

        .cb-on {
            font-family: monospace;
            font-weight: bold;
            font-size: 7.5pt;
            color: #000000;
        }

        .cb-off {
            font-family: monospace;
            font-size: 7.5pt;
            color: #94a3b8;
        }

        /* ---------- Signatures ---------- */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        .sign-table td {
            border: 1px solid #000000;
            border-top: none; /* Collé directement sous les panneaux */
            background-color: #ffffff;
            vertical-align: top;
            padding: 3pt 4pt;
            height: 58pt;
            position: relative;
        }

        .sign-title {
            font-size: 7.5pt;
            font-weight: bold;
            text-decoration: underline;
            color: #000000;
            line-height: 8.8pt;
        }

        .sign-appr {
            text-align: center;
            margin-top: 14pt;
            font-size: 9.5pt;
            font-weight: bold;
            font-style: italic;
            color: #1e3a8a;
            line-height: 11.5pt;
        }

        .sign-stamp {
            position: absolute;
            left: 12pt;
            top: 6pt;
            width: 48pt;
            height: 48pt;
            opacity: 0.90;
        }
    </style>
</head>
<body>

    @php
        $schoolClass = $reportCard->schoolClass;
        $isMaternelle = $schoolClass?->isMaternelle() ?? false;
        $isPrimaire = $schoolClass?->isPrimaire() ?? false;
        $isCollege = $schoolClass?->isCollege() ?? false;
        $isLycee = $schoolClass?->isLycee() ?? false;
        $isSemester = $schoolClass?->isSemesterBased() ?? false;

        $academicYearName = $reportCard->period?->academicYear?->name ?? '2025 - 2026';

        // Libellé de période : « Semestre 1 » devient « PREMIER SEMESTRE »
        $periodRaw = trim($reportCard->period?->name ?? 'Premier semestre');
        $periodLabel = mb_strtoupper($periodRaw);
        if (preg_match('/^(semestre|trimestre)\s*(\d)$/i', $periodRaw, $periodMatch)) {
            $ordinal = [1 => 'PREMIER', 2 => 'DEUXIÈME', 3 => 'TROISIÈME'][(int) $periodMatch[2]] ?? null;
            if ($ordinal) {
                $periodLabel = $ordinal . ' ' . mb_strtoupper($periodMatch[1]);
            }
        }

        if ($isMaternelle) {
            $bulletinTitle = "BILAN TRIMESTRIEL DES ACQUIS DU {$periodLabel}";
        } elseif ($isPrimaire) {
            $bulletinTitle = "BULLETIN DE NOTES DU {$periodLabel}";
        } else {
            $bulletinTitle = "BULLETIN DE NOTES DE FIN DU {$periodLabel}";
        }

        $matricule = $reportCard->student?->matricule ?? '—';
        $nom = $reportCard->student?->last_name ?? '';
        $prenom = $reportCard->student?->first_name ?? '';
        $sexe = $reportCard->student?->gender ?? '—';
        $className = $schoolClass?->name ?? '—';
        $effectif = $reportCard->total_students ?? ($classStats['total_students'] ?? '—');

        $fmt = fn ($value, string $fallback = '') => ($value !== null && $value !== '')
            ? number_format((float) $value, 2, ',', ' ')
            : $fallback;

        $moyObtenue = $fmt($reportCard->general_average);
        $moyClasse = $fmt($classStats['class_average'] ?? null, '—');
        $forteMoy = $fmt($classStats['max_average'] ?? null, '—');
        $faibleMoy = $fmt($classStats['min_average'] ?? null, '—');
        $rankFormatted = $reportCard->rank ? $reportCard->rank . ($reportCard->rank == 1 ? 'er' : 'e') : '—';

        $moyLitteraire = $fmt($totals['literary_average'] ?? null, '-');
        $moyScientifique = $fmt($totals['scientific_average'] ?? null, '-');
        $totalCoeff = $totals['total_coefficients'] ?? '';
        $totalPoints = $fmt($totals['total_points'] ?? null);

        $sem1 = $fmt($annualProgression['semester_1'] ?? null);
        $sem2 = $fmt($annualProgression['semester_2'] ?? null);
        $t1 = $fmt($annualProgression['trimester_1'] ?? null);
        $t2 = $fmt($annualProgression['trimester_2'] ?? null);
        $t3 = $fmt($annualProgression['trimester_3'] ?? null);
        $annuelle = $fmt($annualProgression['annual_average'] ?? null);

        if ($isSemester && $sem1 === '' && (float) $reportCard->general_average > 0) {
            $sem1 = $moyObtenue;
        }

        $dash = fn ($value) => $value === '' ? '-' : $value;

        $conduiteChoice = $discipline['conduite'] ?? 'Bien';
        $travailChoice = $discipline['travail'] ?? '';
        $honneurChoice = $discipline['tableau_honneur'] ?? 'Inscrit';

        $absJust = $attendance['justified_absences'] ?? 0;
        $absNonJust = $attendance['unjustified_absences'] ?? 0;
        $retard = $attendance['late_arrivals'] ?? 0;
        $expulsions = $attendance['expulsions'] ?? 0;

        $generalAverage = (float) $reportCard->general_average;
        $isInscrit = in_array(strtolower(trim($honneurChoice)), ['inscrit', 'félicitations', 'felicitations', 'encouragements']) || $generalAverage >= 12;
        $isFelicitation = str_contains(strtolower($honneurChoice), 'fél') || str_contains(strtolower($honneurChoice), 'fel') || $generalAverage >= 14;
        $isEncouragement = str_contains(strtolower($honneurChoice), 'enco') || ($generalAverage >= 12 && $generalAverage < 14);
        $isNonInscrit = ! $isInscrit;

        $logoBase64 = null;
        $cleanLogo = public_path('images/logo_roi_clean.png');
        if (file_exists($cleanLogo)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($cleanLogo));
        } elseif (file_exists(public_path('images/logo_from_bulletin.png'))) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/logo_from_bulletin.png')));
        }

        $stampBase64 = null;
        if ($isLycee && file_exists(public_path('images/stamp_lycee.png'))) {
            $stampBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/stamp_lycee.png')));
        } elseif ($isCollege && file_exists(public_path('images/stamp_college.png'))) {
            $stampBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/stamp_college.png')));
        } elseif ($isPrimaire && file_exists(public_path('images/stamp_primaire.png'))) {
            $stampBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/stamp_primaire.png')));
        } elseif ($isMaternelle && file_exists(public_path('images/stamp_maternelle.png'))) {
            $stampBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/stamp_maternelle.png')));
        } elseif (file_exists(public_path('images/stamp_from_bulletin.png'))) {
            $stampBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/stamp_from_bulletin.png')));
        }

        $checkbox = fn (bool $on) => $on
            ? '<span class="cb-on">[X]</span>'
            : '<span class="cb-off">[&nbsp;]</span>';
    @endphp

    {{-- 1. En-tête officiel identique à bulletin.jpeg --}}
    <table class="header-table">
        <tr>
            <td style="width: 58%;">
                <div class="hd-line-bold">MINISTERE DE L'EDUCATION NATIONALE, DE L'ALPHABETISATION,</div>
                <div class="hd-line-bold">ET DE LA PROMOTION DES LANGUES</div>
                @if ($isMaternelle)
                    <div class="hd-line">DREN</div>
                    <div class="hd-line">Inspection du Préscolaire</div>
                @elseif ($isPrimaire)
                    <div class="hd-line">DREN</div>
                    <div class="hd-line">IEP</div>
                @else
                    <div class="hd-line">DREN</div>
                    <div class="hd-line">IRESG</div>
                    <div class="hd-line">IESG Niamey 1</div>
                @endif
                <div class="hd-line-bold">CSP LES ROIS DU SAHEL</div>
                <div class="hd-line">Contact: 90420680/90419300</div>
            </td>
            <td class="hd-logo" style="width: 16%;">
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="CSP Les Rois du Sahel">
                @endif
            </td>
            <td style="width: 26%;">
                <div class="hd-year">Année scolaire: {{ $academicYearName }}</div>
            </td>
        </tr>
    </table>

    {{-- 2. Titre centré sans boîte --}}
    <div class="bulletin-title">{{ $bulletinTitle }}</div>

    {{-- 3. Identification de l'élève (Tableau à 3 lignes + Matricule dessous comme sur bulletin.jpeg) --}}
    <table class="id-table">
        <tr>
            <td class="lbl" style="width: 14%;">Nom:</td>
            <td style="width: 26%;">{{ $nom }}</td>
            <td class="lbl" style="width: 19%;">Moy. obtenue:</td>
            <td class="c b" style="width: 11%;">{{ $moyObtenue }}</td>
            <td class="lbl" style="width: 14%;">Rang:</td>
            <td class="c b" style="width: 16%;">{{ $rankFormatted }}</td>
        </tr>
        <tr>
            <td class="lbl">Prénom:</td>
            <td>{{ $prenom }}</td>
            <td class="lbl">Moy. de la classe:</td>
            <td class="c b">{{ $moyClasse }}</td>
            <td class="lbl">Classe:</td>
            <td class="c b">{{ $className }}</td>
        </tr>
        <tr>
            <td class="lbl">Sexe:</td>
            <td class="c">{{ $sexe }}</td>
            <td colspan="2"></td>
            <td class="lbl">Effectif:</td>
            <td class="c">{{ $effectif }}</td>
        </tr>
    </table>
    <div class="matricule-row">
        <span class="lbl">Matricule:</span>
        <span>{{ $matricule }}</span>
    </div>

    {{-- 4. Tableau des notes --}}
    @if ($isMaternelle)
        <table class="grades-table">
            <thead>
                <tr>
                    <th style="width: 42%;">Domaines d'Activités &amp; Éveil Préscolaire</th>
                    <th style="width: 14%;">Note /10</th>
                    <th style="width: 22%;">Niveau d'Acquisition</th>
                    <th style="width: 22%;">Observations</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subjectDetails as $item)
                    @php
                        $score = (float) ($item['ms'] ?? 0);
                        $acq = $score >= 8 ? 'Très Bien Acquis' : ($score >= 6 ? 'Bien Acquis' : ($score >= 5 ? "En voie d'acquisition" : 'En éveil / À encourager'));
                    @endphp
                    <tr>
                        <td class="l">{{ $item['subject'] }}</td>
                        <td>{{ $fmt($item['ms'] ?? null, '—') }}</td>
                        <td class="l">{{ $acq }}</td>
                        <td class="l">{{ $item['appreciation'] ?? 'Participe activement' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="l">Aucune note enregistrée pour cette période.</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td class="l b">MOYENNE GLOBALE D'ÉVEIL</td>
                    <td class="b">{{ $moyObtenue }}</td>
                    <td class="b">Rang : {{ $rankFormatted }}</td>
                    <td class="l b">{{ $reportCard->appreciationLabel }}</td>
                </tr>
            </tbody>
        </table>

    @elseif ($isPrimaire)
        {{-- Cycle Primaire : une seule note (Note de composition uniquement, sur 10 points) --}}
        <table class="grades-table">
            <thead>
                <tr>
                    <th style="width: 36%;">Matières</th>
                    <th style="width: 14%;">Note Compo /10</th>
                    <th style="width: 10%;">COEF</th>
                    <th style="width: 12%;">Total Points</th>
                    <th style="width: 10%;">RANG</th>
                    <th style="width: 18%;">Appréciation</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subjectDetails as $item)
                    @php
                        $sRank = ! empty($item['rank']) ? $item['rank'] . ($item['rank'] == 1 ? 'er' : 'e') : '';
                        $compoNote = $item['nc'] ?? $item['ms'] ?? null;
                    @endphp
                    <tr>
                        <td class="l">{{ $item['subject'] }}</td>
                        <td class="b">{{ $fmt($compoNote) }}</td>
                        <td>{{ $item['coefficient'] ?? 1 }}</td>
                        <td>{{ $fmt($item['weighted'] ?? null) }}</td>
                        <td>{{ $sRank }}</td>
                        <td class="c">{{ $item['appreciation'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="l">Aucune note enregistrée pour cette période.</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td class="r b" style="padding-right: 6pt;">Total / Moyenne :</td>
                    <td class="b c">{{ $moyObtenue }}</td>
                    <td class="b c">{{ $totalCoeff }}</td>
                    <td class="b c">{{ $totalPoints }}</td>
                    <td class="b c">{{ $rankFormatted }}</td>
                    <td class="c b">{{ $reportCard->appreciationLabel }}</td>
                </tr>
            </tbody>
        </table>

    @else
        {{-- Collège & Lycée : 9 colonnes exactement comme bulletin.jpeg --}}
        <table class="grades-table">
            <thead>
                <tr>
                    <th style="width: 24%;">Matières</th>
                    <th style="width: 7.5%;">MC/20</th>
                    <th style="width: 7.5%;">NC/20</th>
                    <th style="width: 7.5%;">MS/20</th>
                    <th style="width: 6.5%;">COEF</th>
                    <th style="width: 9.5%;">MS. COEF</th>
                    <th style="width: 8.5%;">RANG</th>
                    <th style="width: 16%;">Appréciation</th>
                    <th style="width: 13%;">Visa des profs</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subjectDetails as $item)
                    @php
                        $sRank = ! empty($item['rank']) ? $item['rank'] . ($item['rank'] == 1 ? 'er' : 'e') : '';
                    @endphp
                    <tr>
                        <td class="l">{{ $item['subject'] }}</td>
                        <td>{{ $fmt($item['mc'] ?? null) }}</td>
                        <td>{{ $fmt($item['nc'] ?? null) }}</td>
                        <td class="b">{{ $fmt($item['ms'] ?? null) }}</td>
                        <td>{{ $item['coefficient'] ?? 1 }}</td>
                        <td>{{ $fmt($item['weighted'] ?? null) }}</td>
                        <td>{{ $sRank }}</td>
                        <td class="c">{{ $item['appreciation'] ?? '' }}</td>
                        <td></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="l">Aucune note enregistrée pour cette période.</td>
                    </tr>
                @endforelse
                {{-- Lignes Total : alignement strict identique à bulletin_crop_grades.jpg --}}
                <tr class="total-row">
                    <td rowspan="2" class="c b" style="font-size: 8.5pt;">Total</td>
                    <td colspan="2" class="l b" style="font-size: 7.2pt; padding-left: 3pt;">Moy littér:</td>
                    <td class="c b" style="font-size: 7.8pt;">{{ $moyLitteraire }}</td>
                    <td rowspan="2" class="c b" style="font-size: 8.5pt;">{{ $totalCoeff }}</td>
                    <td rowspan="2" class="c b" style="font-size: 8.5pt;">{{ $totalPoints }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="total-row">
                    <td colspan="2" class="l b" style="font-size: 7.2pt; padding-left: 3pt;">Moy scient:</td>
                    <td class="c b" style="font-size: 7.8pt;">{{ $moyScientifique }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- 5. Moyenne générale & Statistiques (Exactement comme bulletin_crop_stats.jpg) --}}
    <table class="stats-section">
        <tr>
            <td style="width: 44%;">
                <div class="avg-box-title">Moyenne générale</div>
                <table class="avg-table">
                    <tr>
                        @if ($isSemester)
                            <th style="width: 36%;">1er semestre</th>
                            <th style="width: 36%;">2e semestre</th>
                            <th style="width: 28%;">Annuelle</th>
                        @else
                            <th style="width: 25%;">1er trimestre</th>
                            <th style="width: 25%;">2e trimestre</th>
                            <th style="width: 25%;">3e trimestre</th>
                            <th style="width: 25%;">Annuelle</th>
                        @endif
                    </tr>
                    <tr>
                        @if ($isSemester)
                            <td class="b">{{ $dash($sem1) }}</td>
                            <td>{{ $dash($sem2) }}</td>
                            <td>{{ $dash($annuelle) }}</td>
                        @else
                            <td>{{ $dash($t1) }}</td>
                            <td>{{ $dash($t2) }}</td>
                            <td>{{ $dash($t3) }}</td>
                            <td class="b">{{ $dash($annuelle) }}</td>
                        @endif
                    </tr>
                </table>
            </td>
            <td style="width: 10%;"></td>
            <td style="width: 46%;">
                <table class="stat-table">
                    <tr>
                        <td class="stat-lbl" style="width: 65%;">Forte moyenne:</td>
                        <td class="stat-val" style="width: 35%;">{{ $forteMoy }}</td>
                    </tr>
                    <tr>
                        <td class="stat-lbl">Faible moyenne:</td>
                        <td class="stat-val">{{ $faibleMoy }}</td>
                    </tr>
                    <tr>
                        <td class="stat-lbl">Moyenne de la classe:</td>
                        <td class="stat-val">{{ $moyClasse }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- 6. Panneaux : Conduite, Travail, Tableau d'honneur, Assiduité (Table unique 4 colonnes contiguës) --}}
    <table class="panels-table">
        <thead>
            <tr>
                <th style="width: 17%;">Conduite</th>
                <th style="width: 22%;">Travail</th>
                <th style="width: 26%;">Tableau d'honneur</th>
                <th style="width: 35%;">Assiduité - Retard</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                {{-- Conduite (17%) --}}
                <td class="panel-cell">
                    <table class="item-table">
                        @foreach (['Très bien', 'Bien', 'Passable', 'Mauvaise', 'Avertissement', 'Blâme'] as $option)
                            <tr>
                                <td>{{ $option }}</td>
                                <td class="cb">{!! $checkbox(strtolower(trim($conduiteChoice)) === strtolower($option)) !!}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>

                {{-- Travail (22%) --}}
                <td class="panel-cell">
                    <table class="item-table">
                        @foreach (['Avertissement', 'Blâme', 'Remarques'] as $option)
                            <tr>
                                <td>{{ $option }}</td>
                                <td class="cb">{!! $checkbox($travailChoice !== '' && str_contains(strtolower($travailChoice), strtolower(mb_substr($option, 0, 5)))) !!}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>

                {{-- Tableau d'honneur (26%) --}}
                <td class="panel-cell">
                    <table class="item-table">
                        <tr>
                            <td>Inscrit:</td>
                            <td class="cb">{!! $checkbox($isInscrit) !!}</td>
                        </tr>
                        <tr>
                            <td>Félicitations</td>
                            <td class="cb">{!! $checkbox($isFelicitation) !!}</td>
                        </tr>
                        <tr>
                            <td>Encouragements</td>
                            <td class="cb">{!! $checkbox($isEncouragement) !!}</td>
                        </tr>
                        <tr>
                            <td>Non inscrit</td>
                            <td class="cb">{!! $checkbox($isNonInscrit) !!}</td>
                        </tr>
                    </table>
                </td>

                {{-- Assiduité - Retard (35%) --}}
                <td class="panel-cell">
                    <table class="item-table">
                        <tr>
                            <td>Absences justifiées</td>
                            <td class="cb b">{{ $absJust }}</td>
                        </tr>
                        <tr>
                            <td>Absences non justifiées</td>
                            <td class="cb b">{{ $absNonJust }}</td>
                        </tr>
                        <tr>
                            <td>Retard</td>
                            <td class="cb b">{{ $retard }}</td>
                        </tr>
                        <tr>
                            <td>Expulsions</td>
                            <td class="cb b">{{ $expulsions }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- 7. Appréciation du chef d'établissement & Visa des parents (Ligne verticale alignée avec Travail/Tableau d'honneur) --}}
    <table class="sign-table">
        <tr>
            {{-- Conduite (17%) + Travail (22%) = 39% pour aligner parfaitement la séparation verticale --}}
            <td style="width: 39%;">
                <div class="sign-title">Appréciation du chef de l'établissement</div>
                <div class="sign-appr">{{ $reportCard->appreciation ?? $reportCard->appreciationLabel }}</div>
                @if ($stampBase64)
                    <img src="{{ $stampBase64 }}" class="sign-stamp" alt="Cachet officiel">
                @endif
            </td>
            {{-- Tableau d'honneur (26%) + Assiduité (35%) = 61% --}}
            <td style="width: 61%; text-align: center;">
                <div class="sign-title">Visa des parents</div>
            </td>
        </tr>
    </table>

</body>
</html>
