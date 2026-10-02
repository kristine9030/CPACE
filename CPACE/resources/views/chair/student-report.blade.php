<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Performance Report - CPACE</title>
    {{-- Rendered by dompdf (StudentManagementController::exportPdf): table-based
         layout, no flex/grid, no remote assets — the logo arrives as a data URI. --}}
    <style>
        @page { margin: 30mm 12mm 16mm 12mm; }
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 8.5px; color: #1f2937; margin: 0; }

        /* Repeated on every page */
        .page-head { position: fixed; top: -24mm; left: 0; right: 0; height: 20mm; }
        .page-head table { width: 100%; border-collapse: collapse; }
        .page-head td { vertical-align: middle; padding: 0; }
        .logo { width: 44px; height: 44px; }
        .brand-name { font-size: 15px; font-weight: bold; color: #7B1D1D; letter-spacing: .5px; }
        .brand-sub { font-size: 8px; color: #6b7280; margin-top: 2px; }
        .head-meta { text-align: right; font-size: 7.5px; color: #6b7280; line-height: 1.5; }
        .head-meta strong { color: #1f2937; }
        .head-rule { height: 3px; background: #7B1D1D; margin-top: 6px; }
        .head-rule-thin { height: 1px; background: #e8d4d4; margin-top: 1px; }

        .page-foot { position: fixed; bottom: -11mm; left: 0; right: 0; height: 8mm; border-top: 1px solid #e5e7eb; padding-top: 5px; font-size: 7px; color: #9ca3af; }
        .page-foot table { width: 100%; border-collapse: collapse; }

        /* Title + scope */
        .title { font-size: 17px; font-weight: bold; color: #111827; margin: 0 0 3px; }
        .scope { font-size: 8.5px; color: #6b7280; margin-bottom: 12px; }
        .scope span { display: inline-block; background: #f3f4f6; color: #374151; border-radius: 9px; padding: 2px 8px; margin-right: 3px; }

        /* Summary tiles */
        .summary { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px 14px; }
        .summary td { width: 25%; border: 1px solid #ececef; border-radius: 6px; padding: 9px 11px; background: #fafafa; }
        .summary .num { font-size: 17px; font-weight: bold; color: #111827; }
        .summary .lbl { font-size: 7px; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; margin-top: 2px; }
        .summary td.accent { border-left: 3px solid #7B1D1D; }
        .summary td.risk .num { color: #b91c1c; }

        /* Roster */
        .roster { width: 100%; border-collapse: collapse; }
        .roster thead th {
            background: #f3f4f6; color: #4b5563; text-align: left; font-size: 7px; font-weight: bold;
            text-transform: uppercase; letter-spacing: .4px; padding: 7px 6px; border-bottom: 1px solid #d1d5db;
        }
        .roster td { padding: 6px; border-bottom: 1px solid #eef0f2; vertical-align: middle; }
        .roster tbody tr:nth-child(even) td { background: #fafafa; }
        .roster tr { page-break-inside: avoid; }
        .num-col { text-align: right; }
        .idx { color: #9ca3af; text-align: center; }
        .name { font-weight: bold; color: #111827; }
        .email { color: #9ca3af; font-size: 7.5px; }
        .muted { color: #9ca3af; }
        .bar { width: 52px; height: 4px; background: #e5e7eb; border-radius: 2px; margin-top: 3px; }
        .bar div { height: 4px; border-radius: 2px; }
        .pill { display: inline-block; font-size: 7px; font-weight: bold; padding: 2px 7px; border-radius: 8px; }
        .pill.at-risk { background: #fde8e8; color: #b91c1c; }
        .pill.on-track { background: #d1fae5; color: #047857; }
        .pill.setup-pending { background: #fef3c7; color: #b45309; }
        .pill.disabled { background: #eceef1; color: #6b7280; }
        .pill.alumni { background: #e0e7ff; color: #4338ca; }
        .empty { text-align: center; padding: 24px; color: #9ca3af; }
    </style>
</head>
<body>

<div class="page-head">
    <table>
        <tr>
            @if ($logo)
                <td style="width:52px;"><img class="logo" src="{{ $logo }}" alt="CPACE"></td>
            @endif
            <td>
                <div class="brand-name">CPACE</div>
                <div class="brand-sub">College of Accountancy &bull; Program Chair Office</div>
            </td>
            <td class="head-meta">
                <strong>Student Performance Report</strong><br>
                Generated {{ now()->format('F j, Y \a\t g:i A') }}<br>
                Prepared by {{ $preparedBy }}
            </td>
        </tr>
    </table>
    <div class="head-rule"></div>
    <div class="head-rule-thin"></div>
</div>

<div class="page-foot">
    <table>
        <tr>
            <td>CPACE &bull; Confidential departmental document &mdash; for internal use only</td>
            <td style="text-align:right;">{{-- page numbers are drawn here by exportPdf() --}}</td>
        </tr>
    </table>
</div>

<div class="title">Enrollment and Readiness Summary</div>
<div class="scope">
    @foreach (explode(' · ', $scope) as $part)<span>{{ $part }}</span>@endforeach
</div>

<table class="summary">
    <tr>
        <td class="accent"><div class="num">{{ $stats['total'] }}</div><div class="lbl">Students in report</div></td>
        <td><div class="num">{{ $stats['active'] }}</div><div class="lbl">Active accounts</div></td>
        <td><div class="num">{{ $stats['average'] }}%</div><div class="lbl">Average readiness (75% target)</div></td>
        <td class="risk"><div class="num">{{ $stats['at_risk'] }}</div><div class="lbl">Need intervention</div></td>
    </tr>
</table>

<table class="roster">
    <thead>
        <tr>
            <th style="width:22px;" class="idx">#</th>
            <th>Student</th>
            <th style="width:72px;">Student No.</th>
            <th style="width:70px;">Section</th>
            <th style="width:72px;">Readiness</th>
            <th style="width:56px;" class="num-col">Answered</th>
            <th style="width:46px;" class="num-col">Quizzes</th>
            <th style="width:44px;" class="num-col">Streak</th>
            <th style="width:78px;">Last Active</th>
            <th style="width:72px;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $i => $row)
            @php
                $status = $statusOf($row);
                $barColor = match (true) {
                    $row['score'] === null => '#d1d5db',
                    $row['score'] >= 75 => '#059669',
                    $row['score'] >= 60 => '#d97706',
                    default => '#c0392b',
                };
            @endphp
            <tr>
                <td class="idx">{{ $i + 1 }}</td>
                <td>
                    <div class="name">{{ $row['name'] }}</div>
                    <div class="email">{{ $row['email'] }}</div>
                </td>
                <td>{!! $row['student_number'] ? e($row['student_number']) : '<span class="muted">—</span>' !!}</td>
                <td>{!! $row['section'] ? e($row['section']) : '<span class="muted">No section</span>' !!}</td>
                <td>
                    @if ($row['score'] === null)
                        <span class="muted">Not rated</span>
                    @else
                        <strong style="color:{{ $barColor }};">{{ $row['score'] }}%</strong>
                        <div class="bar"><div style="width:{{ $row['score'] }}%; background:{{ $barColor }};"></div></div>
                    @endif
                </td>
                <td class="num-col">{{ $row['attempted'] }}</td>
                <td class="num-col">{{ $row['quizzes'] }}</td>
                <td class="num-col">{{ $row['streak'] }}d</td>
                <td>{!! $row['last_active'] ? e($row['last_active']->format('M j, Y')) : '<span class="muted">Never</span>' !!}</td>
                <td><span class="pill {{ \Illuminate\Support\Str::slug($status) }}">{{ $status }}</span></td>
            </tr>
        @empty
            <tr><td colspan="10" class="empty">No students matched this scope.</td></tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
