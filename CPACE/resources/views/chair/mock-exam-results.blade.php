<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results {{ $exam->title }} - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.1/chart.umd.min.js"></script>
    @include('partials.mock-exam-styles')
    <style>
        .kpis { display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:16px; margin-bottom:18px; }
        .stat-card { background:#fff; border-radius:14px; padding:18px 20px;
                     box-shadow:0 2px 6px rgba(15,10,10,.08), 0 10px 22px -10px rgba(15,10,10,.22); }
        .stat-top { display:flex; justify-content:space-between; align-items:flex-start; }
        .stat-icon { width:40px; height:40px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:17px; flex-shrink:0; }
        .si-blue   { background:#dbeafe; color:var(--blue); }
        .si-orange { background:#fef3c7; color:var(--amber); }
        .si-green  { background:#d1fae5; color:var(--green); }
        .si-red    { background:#fde8e8; color:var(--red); }
        .stat-lbl { font-size:11.5px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.4px; }
        .stat-num { font-size:28px; font-weight:700; color:var(--ink); font-family:'Montserrat',sans-serif; line-height:1; margin-top:6px; }

        .charts-row { display:grid; grid-template-columns:1fr 320px; gap:18px; margin-bottom:18px; }
        .charts-row .card + .card { margin-top:0; }
        .chart-wrap { position:relative; height:220px; margin-top:14px; }
        .chart-empty { height:220px; display:flex; align-items:center; justify-content:center; color:#bbb; font-size:12.5px; }
        .donut-legend { display:flex; justify-content:center; gap:18px; margin-top:12px; font-size:11.5px; color:#666; }
        .donut-legend i { width:9px; height:9px; border-radius:3px; display:inline-block; margin-right:5px; }

        .layout { display:grid; grid-template-columns:1fr 380px; gap:18px; align-items:start; margin-bottom:18px; }
        .layout .card + .card { margin-top:0; }
        /* Long lists scroll inside their card so the page itself stays short. */
        .scroll-card { display:flex; flex-direction:column; max-height:520px; padding:0; overflow:hidden; }
        .scroll-head { padding:16px 20px; border-bottom:1px solid #f2f2f2; flex-shrink:0; display:flex; justify-content:space-between; align-items:center; }
        .scroll-head small { color:#aaa; font-size:11px; font-weight:500; }
        .scroll-body { overflow-y:auto; flex:1; min-height:0; }
        table { width:100%; border-collapse:collapse; }
        thead th { position:sticky; top:0; z-index:1; text-align:left; font-size:11px; color:#aaa; font-weight:600; padding:11px 16px; text-transform:uppercase; letter-spacing:.4px; background:#fafafa; border-bottom:1px solid #f5f5f5; }
        tbody td { padding:12px 16px; font-size:13px; border-bottom:1px solid #f8f8f8; vertical-align:middle; }
        tbody tr:last-child td { border-bottom:none; }
        tbody tr:hover { background:#fafbfc; }
        .s-name { font-weight:600; color:var(--ink); text-decoration:none; }
        .s-name:hover { color:var(--primary); }
        .s-email { font-size:11px; color:#aaa; }
        .pct { font-weight:700; }
        .pct.good { color:#059669; } .pct.mid { color:#d97706; } .pct.bad { color:var(--red); }
        .pill { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; }
        .p-done { background:#d1fae5; color:#059669; }
        .p-prog { background:#fef3c7; color:#d97706; }
        .risk { display:inline-block; font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; white-space:nowrap; }
        .risk-low    { background:#e8f1fb; color:#2a5da8; }
        .risk-medium { background:#fdf0d8; color:#9a6200; }
        .risk-high   { background:#fdeceb; color:#a32318; }
        .item-row { padding:13px 18px; border-bottom:1px solid #f6f6f6; }
        .item-row:last-child { border-bottom:none; }
        .item-q { font-size:12.5px; color:#333; line-height:1.5; margin-bottom:7px; }
        .item-q b { color:var(--primary); margin-right:5px; }
        .bar { height:6px; background:#f0f0f0; border-radius:4px; overflow:hidden; }
        .bar span { display:block; height:100%; border-radius:4px; }
        .item-meta { display:flex; justify-content:space-between; font-size:11px; color:#999; margin-top:5px; }
        .empty-note { padding:44px 20px; text-align:center; color:#bbb; font-size:13px; }
        @media (max-width: 1100px) { .charts-row, .layout { grid-template-columns:1fr; } }
    </style>
</head>
<body>
@if($isChair)
    @include('partials.chair-sidebar', ['active' => 'mock-exams'])
@else
    @include('partials.faculty-sidebar', ['active' => 'mock-exams'])
@endif

@php
    $prefix = $isChair ? 'chair' : 'faculty';
    $barColor = fn ($rate) => $rate === null ? '#ddd' : ($rate >= 75 ? '#10b981' : ($rate >= 50 ? '#f59e0b' : '#c0392b'));
@endphp

<main class="main">
    <div class="topbar">
        <div>
            <a href="{{ route($prefix . '.mock-exams.subject', $exam->subject_id) }}"
               style="font-size:12px;color:var(--muted);text-decoration:none;">
                <i class="fas fa-arrow-left"></i> {{ $subject->code }}
            </a>
            <div class="page-title" style="margin-top:4px;">{{ $exam->title }}</div>
            <div class="page-sub">
                Results · {{ $exam->scheduled_at?->format('M j, Y · g:i A') }} ·
                {{ $exam->items->count() }} questions · {{ $exam->duration_minutes }} min
            </div>
        </div>
        <div class="topbar-right">
            @include('partials.topbar-actions')
            <a href="{{ route($prefix . '.mock-exams.monitor', $exam) }}" class="btn btn-ghost"><i class="fas fa-desktop"></i> Live monitor</a>
        </div>
    </div>

    <div class="kpis">
        <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">Redeemed code</div><div class="stat-num">{{ $stats['registered'] }}</div></div><div class="stat-icon si-blue"><i class="fas fa-ticket"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">Started</div><div class="stat-num">{{ $stats['started'] }}</div></div><div class="stat-icon si-orange"><i class="fas fa-person-running"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">Submitted</div><div class="stat-num">{{ $stats['submitted'] }}</div></div><div class="stat-icon si-green"><i class="fas fa-circle-check"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">Average</div><div class="stat-num">{{ $stats['average'] !== null ? $stats['average'] . '%' : '—' }}</div></div><div class="stat-icon si-orange"><i class="fas fa-chart-simple"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">Highest</div><div class="stat-num">{{ $stats['highest'] !== null ? $stats['highest'] . '%' : '—' }}</div></div><div class="stat-icon si-green"><i class="fas fa-trophy"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">Lowest</div><div class="stat-num">{{ $stats['lowest'] !== null ? $stats['lowest'] . '%' : '—' }}</div></div><div class="stat-icon si-red"><i class="fas fa-arrow-down"></i></div></div></div>
    </div>

    <div class="charts-row">
        <div class="card">
            <div class="card-title">Score distribution</div>
            @if($stats['submitted'] > 0)
                <div class="chart-wrap"><canvas id="distChart"></canvas></div>
            @else
                <div class="chart-empty">No submissions yet</div>
            @endif
        </div>
        <div class="card">
            <div class="card-title">Pass rate <span style="font-weight:500;color:#aaa;font-size:11px;">(≥75%)</span></div>
            @if($stats['submitted'] > 0)
                <div class="chart-wrap" style="height:180px;"><canvas id="passChart"></canvas></div>
                <div class="donut-legend">
                    <span><i style="background:#10b981;"></i>Passed ({{ $passCount }})</span>
                    <span><i style="background:#e5484d;"></i>Failed ({{ $stats['submitted'] - $passCount }})</span>
                </div>
            @else
                <div class="chart-empty">No submissions yet</div>
            @endif
        </div>
    </div>

    <div class="layout">
        <div class="card scroll-card">
            <div class="scroll-head"><div class="card-title">Student submissions</div><small>{{ $attempts->count() }}</small></div>
            @if($attempts->isEmpty())
                <div class="empty-note">Nobody has started this exam yet.</div>
            @else
                <div class="scroll-body">
                    <table>
                        <thead><tr><th>Student</th><th>Status</th><th>Score</th><th>%</th><th>Integrity</th><th>Submitted</th><th></th></tr></thead>
                        <tbody>
                        @foreach($attempts as $a)
                            @php $r = $risk[$a->id]; @endphp
                            <tr>
                                <td>
                                    <a class="s-name" href="{{ route($prefix . '.mock-exams.attempt', $a) }}">{{ trim($a->student?->first_name . ' ' . $a->student?->last_name) ?: 'Unknown' }}</a>
                                    <div class="s-email">{{ $a->student?->email }}</div>
                                </td>
                                <td>
                                    @if($a->isSubmitted())
                                        <span class="pill p-done"><i class="fas fa-check"></i> Submitted</span>
                                    @else
                                        <span class="pill p-prog"><i class="fas fa-hourglass-half"></i> In progress</span>
                                    @endif
                                </td>
                                <td>{{ $a->isSubmitted() ? $a->score . ' / ' . $a->total_points : '—' }}</td>
                                <td>
                                    @if($a->isSubmitted())
                                        @php $p = (float) $a->percent; @endphp
                                        <span class="pct {{ $p >= 75 ? 'good' : ($p >= 50 ? 'mid' : 'bad') }}">{{ round($p, 1) }}%</span>
                                    @else — @endif
                                </td>
                                <td>
                                    @if($r['level'] !== 'none')
                                        <span class="risk risk-{{ $r['level'] }}" title="{{ $a->flag_count }} flag(s)">{{ $r['label'] }}</span>
                                    @else
                                        <span style="color:#bbb;font-size:12px;">Clean</span>
                                    @endif
                                </td>
                                <td style="font-size:12px;color:#777;">{{ $a->submitted_at?->format('M j, g:i A') ?? 'started ' . $a->started_at->diffForHumans() }}</td>
                                <td style="text-align:right;"><a class="btn btn-ghost btn-sm" href="{{ route($prefix . '.mock-exams.attempt', $a) }}#answers"><i class="fas fa-file-lines"></i> Result</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="card scroll-card">
            <div class="scroll-head"><div class="card-title">Per-question accuracy</div><small>of {{ $stats['submitted'] }} submitted</small></div>
            <div class="scroll-body">
                @forelse($exam->items as $i => $item)
                    @php $st = $itemStats[$item->id]; $rate = $st['rate']; @endphp
                    <div class="item-row">
                        <div class="item-q"><b>Q{{ $i + 1 }}</b>{{ \Illuminate\Support\Str::limit($item->question_text, 110) }}</div>
                        <div class="bar"><span style="width:{{ $rate ?? 0 }}%;background:{{ $barColor($rate) }};"></span></div>
                        <div class="item-meta">
                            <span>Correct: <b>{{ $item->correctLabel() }}</b>{{ $item->topic ? ' · ' . $item->topic->name : '' }}</span>
                            <span>{{ $rate === null ? 'no data' : $st['right'] . ' correct (' . $rate . '%)' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="empty-note">This exam has no questions.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card scroll-card" style="max-height:420px;">
        <div class="scroll-head"><div class="card-title">Accuracy by topic</div><small>weakest first</small></div>
        <div class="scroll-body">
            @forelse($topicStats as $name => $t)
                <div class="item-row">
                    <div class="item-q"><b>{{ $name }}</b></div>
                    <div class="bar"><span style="width:{{ $t['rate'] ?? 0 }}%;background:{{ $barColor($t['rate']) }};"></span></div>
                    <div class="item-meta"><span>{{ $t['right'] }} of {{ $t['total'] }} answers correct</span><span>{{ $t['rate'] === null ? 'no data' : $t['rate'] . '%' }}</span></div>
                </div>
            @empty
                <div class="empty-note">No topic data yet.</div>
            @endforelse
        </div>
    </div>
</main>

@include('partials.alerts')

@if($stats['submitted'] > 0)
<script>
Chart.defaults.font.family = "'Poppins', sans-serif";
Chart.defaults.color = '#999';

new Chart(document.getElementById('distChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_keys($buckets)) !!},
        datasets: [{ data: {!! json_encode(array_values($buckets)) !!}, backgroundColor: '#7B1D1D', borderRadius: 5, maxBarThickness: 34 }],
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => c.parsed.y + ' student' + (c.parsed.y === 1 ? '' : 's') } } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f2f2f2' } }, x: { grid: { display: false } } },
    },
});

new Chart(document.getElementById('passChart'), {
    type: 'doughnut',
    data: {
        labels: ['Passed', 'Failed'],
        datasets: [{ data: [{{ $passCount }}, {{ $stats['submitted'] - $passCount }}], backgroundColor: ['#10b981', '#e5484d'], borderWidth: 0 }],
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { display: false } } },
});
</script>
@endif
</body>
</html>
