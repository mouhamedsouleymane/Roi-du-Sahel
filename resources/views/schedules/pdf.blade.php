<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Emploi du Temps — {{ $schoolClass->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1e1b4b;
            background: #ffffff;
            padding: 12px;
        }
        .header {
            background-color: #3b0764;
            color: #ffffff;
            padding: 14px 18px 12px;
            margin-bottom: 14px;
            border-radius: 4px;
        }
        .school-name { font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .school-subtitle { font-size: 8px; opacity: 0.75; margin-top: 2px; }
        .header-meta { font-size: 8px; opacity: 0.7; margin-top: 2px; }
        .class-title { font-size: 16px; font-weight: bold; margin-top: 8px; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 8px; }
        .class-meta { font-size: 8px; opacity: 0.75; margin-bottom: 4px; }
        .stat-pill {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            padding: 2px 8px; border-radius: 20px;
            font-size: 8px; font-weight: bold; margin-top: 4px;
        }
        /* Grille */
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead tr { background-color: #3b0764; color: #ffffff; }
        thead th {
            padding: 6px 3px; text-align: center;
            font-size: 8px; font-weight: bold; text-transform: uppercase;
            border-right: 1px solid rgba(255,255,255,0.15);
        }
        thead th:last-child { border-right: none; }
        thead th.time-col { width: 72px; background-color: #2e1065; }
        tbody tr { border-bottom: 1px solid #e2e8f0; }
        tbody tr:nth-child(even) { background-color: #f8f7ff; }
        tbody tr.pause-row { background-color: #fefce8; }
        tbody td { padding: 4px 3px; vertical-align: top; border-right: 1px solid #e2e8f0; }
        tbody td:last-child { border-right: none; }
        .time-cell { text-align: center; background-color: #f1f0ff; font-size: 7.5px; font-weight: bold; color: #3b0764; }
        .time-label { display: block; background: #ddd6fe; color: #3b0764; padding: 2px 3px; border-radius: 2px; font-size: 7px; }
        .pause-label { display: block; color: #b45309; font-size: 7px; margin-top: 2px; }
        .slot-card {
            background: #ede9fe; border: 1px solid #c4b5fd;
            border-left: 3px solid #7c3aed; border-radius: 2px;
            padding: 3px 4px; margin-bottom: 2px;
        }
        .slot-subject { font-size: 8.5px; font-weight: bold; color: #3b0764; line-height: 1.3; }
        .slot-teacher { font-size: 7.5px; color: #5b21b6; margin-top: 1px; }
        .slot-time { font-size: 7px; color: #7c3aed; font-weight: bold; margin-top: 1px; }
        .slot-room { font-size: 6.5px; color: #6b7280; margin-top: 1px; }
        .empty-cell { color: #d1d5db; text-align: center; font-size: 8px; padding-top: 4px; }
        /* Légende */
        .legend {
            margin-top: 10px; padding: 7px 10px;
            background: #f8f7ff; border: 1px solid #e0d9ff; border-radius: 3px;
        }
        .legend-title { font-size: 8px; font-weight: bold; color: #3b0764; margin-bottom: 5px; text-transform: uppercase; }
        .legend-item { display: inline-block; font-size: 7.5px; color: #5b21b6; margin-right: 14px; margin-bottom: 3px; }
        /* Pied de page */
        .footer {
            margin-top: 14px; border-top: 1px solid #e2e8f0; padding-top: 8px;
            font-size: 7.5px; color: #9ca3af;
        }
        .footer-inner { display: flex; justify-content: space-between; align-items: flex-end; }
        .footer-left { line-height: 1.6; }
        .signature-box {
            border: 1px solid #d1d5db; padding: 4px 14px; border-radius: 2px;
            font-size: 7.5px; color: #6b7280; text-align: center; width: 120px; margin-top: 3px;
        }
    </style>
</head>
<body>

    {{-- EN-TÊTE --}}
    <div class="header">
        <div class="school-name">{{ $schoolName }}</div>
        @if ($schoolAddress)
            <div class="school-subtitle">{{ $schoolAddress }}</div>
        @endif
        @if ($activeYear)
            <div class="header-meta">Année Scolaire : {{ $activeYear->name }}</div>
        @endif
        <div class="class-meta">{{ $schoolClass->level->cycle->name ?? '' }} &bull; {{ $schoolClass->level->name ?? '' }}</div>
        <div class="class-title">Emploi du Temps — {{ $schoolClass->name }}</div>
        <div class="stat-pill">{{ $schedules->flatten()->count() }} créneau(x) programmé(s)</div>
    </div>

    {{-- GRILLE --}}
    @php
        $daysList = [
            'LUNDI'    => 'Lundi',
            'MARDI'    => 'Mardi',
            'MERCREDI' => 'Mercredi',
            'JEUDI'    => 'Jeudi',
            'VENDREDI' => 'Vendredi',
            'SAMEDI'   => 'Samedi',
        ];

        $timeSlots = [
            ['start' => '07:00', 'end' => '08:00', 'label' => '07h-08h'],
            ['start' => '08:00', 'end' => '09:00', 'label' => '08h-09h'],
            ['start' => '09:00', 'end' => '10:00', 'label' => '09h-10h'],
            ['start' => '10:00', 'end' => '11:00', 'label' => '10h-11h'],
            ['start' => '11:00', 'end' => '12:00', 'label' => '11h-12h'],
            ['start' => '12:00', 'end' => '13:00', 'label' => '12h-13h', 'is_pause' => true],
            ['start' => '13:00', 'end' => '14:00', 'label' => '13h-14h'],
            ['start' => '14:00', 'end' => '15:00', 'label' => '14h-15h'],
            ['start' => '15:00', 'end' => '16:00', 'label' => '15h-16h'],
            ['start' => '16:00', 'end' => '17:00', 'label' => '16h-17h'],
            ['start' => '17:00', 'end' => '18:00', 'label' => '17h-18h'],
        ];

        $allSlots    = $schedules->flatten();
        $essentials  = ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00'];
    @endphp

    <table>
        <thead>
            <tr>
                <th class="time-col">Horaires</th>
                @foreach ($daysList as $dayKey => $dayLabel)
                    <th>{{ $dayLabel }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($timeSlots as $slot)
                @php
                    $isPause = $slot['is_pause'] ?? false;
                    $rowSlots = $allSlots->filter(fn ($s) =>
                        substr($s->start_time, 0, 5) < $slot['end']
                        && substr($s->end_time, 0, 5) > $slot['start']
                    );
                    $show = $rowSlots->isNotEmpty()
                        || in_array($slot['start'], $essentials)
                        || $isPause;
                @endphp
                @if ($show)
                    <tr class="{{ $isPause ? 'pause-row' : '' }}">
                        <td class="time-cell">
                            <span class="time-label">{{ $slot['label'] }}</span>
                            @if ($isPause)
                                <span class="pause-label">Dejeuner</span>
                            @endif
                        </td>
                        @foreach ($daysList as $dayKey => $dayLabel)
                            @php
                                $cellSlots = $allSlots->filter(fn ($s) =>
                                    $s->day_of_week === $dayKey
                                    && substr($s->start_time, 0, 5) < $slot['end']
                                    && substr($s->end_time, 0, 5) > $slot['start']
                                );
                            @endphp
                            <td>
                                @forelse ($cellSlots as $s)
                                    <div class="slot-card">
                                        <div class="slot-subject">{{ $s->subject?->name }}</div>
                                        <div class="slot-teacher">{{ Str::limit($s->teacher?->user?->name ?? '—', 18) }}</div>
                                        <div class="slot-time">{{ substr($s->start_time, 0, 5) }}-{{ substr($s->end_time, 0, 5) }}</div>
                                        @if ($s->room_number)
                                            <div class="slot-room">{{ $s->room_number }}</div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="empty-cell">—</div>
                                @endforelse
                            </td>
                        @endforeach
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    {{-- LÉGENDE ENSEIGNANTS --}}
    @php
        $teachersInSchedule = $allSlots->map(fn ($s) => $s->teacher)->filter()->unique('id')->values();
    @endphp
    @if ($teachersInSchedule->isNotEmpty())
        <div class="legend">
            <div class="legend-title">Intervenants</div>
            @foreach ($teachersInSchedule as $t)
                <span class="legend-item">
                    <strong>{{ $t->user?->name ?? '—' }}</strong>
                    @if ($t->speciality) — {{ $t->speciality }} @endif
                </span>
            @endforeach
        </div>
    @endif

    {{-- PIED DE PAGE --}}
    <div class="footer">
        <div class="footer-inner">
            <div class="footer-left">
                <div>Document généré le {{ now()->locale('fr')->isoFormat('D MMMM YYYY') }} à {{ now()->format('H\hi') }}</div>
                <div>{{ $activeYear?->name ?? '' }} — {{ $schoolName }}</div>
            </div>
            <div style="text-align:right;">
                <div>Le Directeur</div>
                <div class="signature-box">
                    <div style="height:28px;"></div>
                    <div>Signature &amp; Cachet</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
