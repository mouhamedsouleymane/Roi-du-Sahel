<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bulletin — {{ $reportCard->student->matricule }} — {{ $reportCard->student->last_name }} {{ $reportCard->student->first_name }}</title>
    <style>
        @page {
            margin: 10mm 10mm 10mm 10mm;
            size: A4 portrait;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #0f172a;
            background: #ffffff;
            line-height: 1.3;
        }

        /* En-tête officiel */
        .official-header {
            width: 100%;
            border-bottom: 2px solid #3b0764;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-left {
            width: 38%;
            text-align: center;
            vertical-align: top;
            font-size: 8px;
            text-transform: uppercase;
        }
        .header-left .rep {
            font-weight: bold;
            font-size: 8.5px;
            color: #1e1b4b;
        }
        .header-left .devise {
            font-size: 7px;
            font-style: italic;
            margin-top: 2px;
            color: #64748b;
        }
        .header-left .min {
            font-size: 7.5px;
            font-weight: bold;
            margin-top: 3px;
            color: #334155;
        }
        .header-center {
            width: 24%;
            text-align: center;
            vertical-align: middle;
        }
        .emblem-box {
            display: inline-block;
            background: #f3e8ff;
            border: 2px solid #7e22ce;
            border-radius: 8px;
            padding: 4px 8px;
        }
        .emblem-title {
            font-size: 16px;
            line-height: 1;
        }
        .header-right {
            width: 38%;
            text-align: center;
            vertical-align: top;
        }
        .school-name {
            font-size: 11px;
            font-weight: bold;
            color: #3b0764;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .school-sub {
            font-size: 7.5px;
            color: #6b21a8;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 1px;
        }
        .school-address {
            font-size: 7px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Titre du Bulletin */
        .bulletin-title-box {
            background-color: #3b0764;
            color: #ffffff;
            text-align: center;
            padding: 6px 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .bulletin-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .bulletin-period {
            font-size: 8.5px;
            color: #fde047;
            font-weight: bold;
            margin-top: 2px;
        }

        /* Cadre infos élève */
        .student-card {
            width: 100%;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .student-table {
            width: 100%;
            border-collapse: collapse;
        }
        .student-table td {
            padding: 4px 8px;
            font-size: 8.5px;
            vertical-align: top;
        }
        .info-label {
            color: #64748b;
            font-size: 7.5px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .info-value {
            font-weight: bold;
            color: #0f172a;
            font-size: 9.5px;
        }
        .info-matricule {
            color: #6b21a8;
            font-family: monospace;
            font-weight: bold;
        }

        /* Tableau des notes */
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .grades-table thead tr {
            background-color: #3b0764;
            color: #ffffff;
        }
        .grades-table th {
            padding: 5px 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #1e1b4b;
            text-align: center;
        }
        .grades-table th.th-subject {
            text-align: left;
            padding-left: 8px;
            width: 32%;
        }
        .grades-table tbody td {
            padding: 4.5px 4px;
            font-size: 8.5px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }
        .grades-table tbody td.td-subject {
            text-align: left;
            font-weight: bold;
            color: #1e293b;
            padding-left: 8px;
        }
        .grades-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .note-danger {
            color: #b91c1c;
            font-weight: bold;
        }
        .note-success {
            color: #0f172a;
            font-weight: bold;
        }
        .appreciation-tag {
            font-size: 7.5px;
            font-weight: bold;
            color: #475569;
        }

        /* Récapitulatif et totaux */
        .summary-box {
            width: 100%;
            margin-bottom: 12px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            vertical-align: top;
        }
        .stat-card {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            border-radius: 4px;
            padding: 6px 8px;
            text-align: center;
        }
        .stat-label {
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
        }
        .stat-value-lg {
            font-size: 16px;
            font-weight: bold;
            margin-top: 2px;
            color: #3b0764;
        }
        .stat-value-rank {
            font-size: 14px;
            font-weight: bold;
            margin-top: 2px;
            color: #0f172a;
        }
        .stat-appreciation {
            font-size: 10px;
            font-weight: bold;
            color: #6b21a8;
            margin-top: 2px;
        }

        /* Bas de page & Signatures */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            page-break-inside: avoid;
        }
        .signatures-table td {
            width: 33.33%;
            border: 1px solid #cbd5e1;
            background: #fafafa;
            padding: 6px 8px;
            vertical-align: top;
            height: 65px;
        }
        .sig-title {
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #475569;
            text-align: center;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 3px;
            margin-bottom: 30px;
        }
        .sig-footnote {
            font-size: 6.5px;
            color: #94a3b8;
            text-align: center;
            font-style: italic;
        }

        .footer-note {
            margin-top: 10px;
            text-align: center;
            font-size: 7px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    {{-- En-tête Officiel --}}
    <div class="official-header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <div class="rep">RÉPUBLIQUE DU NIGER</div>
                    <div class="devise">Fraternité - Travail - Progrès</div>
                    <div class="min">MINISTÈRE DE L'ÉDUCATION NATIONALE</div>
                    <div style="font-size: 7px; color: #64748b; margin-top: 1px;">DREPS NIAMEY</div>
                </td>
                <td class="header-center">
                    <div class="emblem-box">
                        <div class="emblem-title">👑</div>
                    </div>
                </td>
                <td class="header-right">
                    <div class="school-name">COMPLEXE SCOLAIRE PRIVÉ</div>
                    <div class="school-sub">« LES ROIS DU SAHEL »</div>
                    <div class="school-address">Koubia Plateau • Niamey, Niger</div>
                    <div class="school-address">Devise : <em>Discipline • Travail • Succès</em></div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Titre --}}
    <div class="bulletin-title-box">
        <div class="bulletin-title">BULLETIN DE NOTES SCOLAIRES</div>
        <div class="bulletin-period">{{ strtoupper($reportCard->period->name) }} — ANNÉE SCOLAIRE {{ $reportCard->period->academicYear->name }}</div>
    </div>

    {{-- Informations Élève --}}
    <div class="student-card">
        <table class="student-table">
            <tr>
                <td style="width: 50%;">
                    <div class="info-label">Nom et Prénom de l'Élève :</div>
                    <div class="info-value">{{ strtoupper($reportCard->student->last_name) }} {{ $reportCard->student->first_name }}</div>
                </td>
                <td style="width: 25%;">
                    <div class="info-label">Matricule :</div>
                    <div class="info-value info-matricule">{{ $reportCard->student->matricule }}</div>
                </td>
                <td style="width: 25%;">
                    <div class="info-label">Sexe :</div>
                    <div class="info-value">{{ $reportCard->student->gender === 'M' ? 'Masculin (M)' : ($reportCard->student->gender === 'F' ? 'Féminin (F)' : '—') }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="info-label">Classe :</div>
                    <div class="info-value">{{ $reportCard->schoolClass->name }} ({{ $reportCard->schoolClass->level->name }} — {{ $reportCard->schoolClass->level->cycle->name }})</div>
                </td>
                <td>
                    <div class="info-label">Effectif Classe :</div>
                    <div class="info-value">{{ $reportCard->total_students }} élève(s)</div>
                </td>
                <td>
                    <div class="info-label">Date de Naissance :</div>
                    <div class="info-value">{{ $reportCard->student->birth_date ? \Carbon\Carbon::parse($reportCard->student->birth_date)->format('d/m/Y') : '—' }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Tableau des Notes et Matières --}}
    @php
        $totalCoeff = 0;
        $totalPoints = 0;
    @endphp

    <table class="grades-table">
        <thead>
            <tr>
                <th class="th-subject">Matières</th>
                <th style="width: 10%;">Coeff.</th>
                <th style="width: 14%;">Moy. / 20</th>
                <th style="width: 14%;">Total (Moy × C)</th>
                <th style="width: 30%;">Appréciation / Observation</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($subjectDetails as $item)
                @php
                    $coeff = (float) $item['coefficient'];
                    $avg = (float) $item['average'];
                    $weighted = $avg * $coeff;
                    $totalCoeff += $coeff;
                    $totalPoints += $weighted;

                    $appr = match(true) {
                        $avg >= 18 => 'Excellent travail',
                        $avg >= 16 => 'Très Bon travail',
                        $avg >= 14 => 'Bon travail',
                        $avg >= 12 => 'Assez bien',
                        $avg >= 10 => 'Passable',
                        $avg >= 8  => 'Insuffisant, des efforts requis',
                        default    => 'Faible, doit redoubler d\'effort',
                    };
                @endphp
                <tr>
                    <td class="td-subject">{{ $item['subject'] }}</td>
                    <td>{{ $coeff }}</td>
                    <td class="{{ $avg >= 10 ? 'note-success' : 'note-danger' }}">
                        {{ number_format($avg, 2) }}
                    </td>
                    <td>
                        {{ number_format($weighted, 2) }}
                    </td>
                    <td class="appreciation-tag">
                        {{ $appr }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #94a3b8; padding: 12px;">
                        Aucune note enregistrée pour cette période.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($totalCoeff > 0)
            <tfoot>
                <tr style="background: #f1f5f9; font-weight: bold;">
                    <td class="td-subject" style="font-weight: bold; text-transform: uppercase;">TOTAUX</td>
                    <td style="text-align: center;">{{ $totalCoeff }}</td>
                    <td style="text-align: center;">—</td>
                    <td style="text-align: center;">{{ number_format($totalPoints, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- Bilan Général & Statistiques --}}
    @php
        $genAvg = (float) $reportCard->general_average;
    @endphp
    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td style="width: 33%; padding-right: 4px;">
                    <div class="stat-card">
                        <div class="stat-label">Moyenne Générale</div>
                        <div class="stat-value-lg" style="color: {{ $genAvg >= 10 ? '#047857' : '#b91c1c' }};">
                            {{ number_format($genAvg, 2) }} <span style="font-size: 9px; color: #64748b;">/ 20</span>
                        </div>
                    </div>
                </td>
                <td style="width: 33%; padding: 0 4px;">
                    <div class="stat-card">
                        <div class="stat-label">Rang Trimestriel</div>
                        <div class="stat-value-rank">
                            {{ $reportCard->rank }}<sup>{{ $reportCard->rank == 1 ? 'er' : 'ème' }}</sup>
                            <span style="font-size: 9px; color: #64748b;">/ {{ $reportCard->total_students }}</span>
                        </div>
                    </div>
                </td>
                <td style="width: 34%; padding-left: 4px;">
                    <div class="stat-card">
                        <div class="stat-label">Mention du Conseil</div>
                        <div class="stat-appreciation">
                            {{ $reportCard->appreciationLabel }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Signatures --}}
    <table class="signatures-table">
        <tr>
            <td>
                <div class="sig-title">Le Titulaire de Classe</div>
                <div class="sig-footnote">Signature & Date</div>
            </td>
            <td>
                <div class="sig-title">Le Parent / Tuteur</div>
                <div class="sig-footnote">Vu et pris connaissance</div>
            </td>
            <td>
                <div class="sig-title">Le Chef d'Établissement</div>
                <div class="sig-footnote">Cachet et Signature</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Complexe Scolaire Privé Les Rois du Sahel — Niamey, Niger • Document officiel généré le {{ now()->format('d/m/Y à H:i') }}
    </div>

</body>
</html>
