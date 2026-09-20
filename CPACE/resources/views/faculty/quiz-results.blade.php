<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results · {{ $quiz->title }} - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.1/chart.umd.min.js"></script>
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; --orange:#f59e0b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:16px; position:relative; z-index:100; }
        .breadcrumb { display:flex; align-items:center; gap:6px; font-size:12px; color:#aaa; margin-bottom:4px; }
        .breadcrumb a { color:var(--accent); text-decoration:none; }
        .page-title { font-size:24px; font-weight:700; color:#14283E; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-ghost { background:#fff; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }
        .btn-outline { background:#fff; color:var(--primary); border:1.5px solid var(--primary); }
        .btn-outline:hover { background:var(--primary-light); }

        /* KPI cards — same card shape used on the faculty dashboard and Test
           Bank: label + big number + icon badge up top, a context line
           pinned to the bottom that adds the "so what" for this number. */
        .stats-row { display:grid; grid-template-columns:repeat(auto-fit, minmax(190px, 1fr)); gap:16px; margin-bottom:22px; }
        .stat {
            background:#fff; border-radius:14px; padding:18px 20px;
            display:flex; flex-direction:column; height:100%;
            box-shadow:0 2px 6px rgba(15,10,10,.06), 0 8px 18px -10px rgba(15,10,10,.15);
            transition:transform .18s ease, box-shadow .18s ease;
        }
        .stat:hover { transform:translateY(-2px); box-shadow:0 4px 10px rgba(15,10,10,.08), 0 14px 26px -10px rgba(15,10,10,.22); }
        .stat-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; }
        .stat-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; }
        .si-blue   { background:#dbeafe; color:var(--blue); }
        .si-green  { background:#d1fae5; color:var(--green); }
        .si-orange { background:#fef3c7; color:var(--orange); }
        .si-red    { background:#fde8e8; color:var(--accent); }
        .si-gray   { background:#f3f4f6; color:#9ca3af; }
        .stat-lbl { font-size:11.5px; font-weight:600; color:#999; text-transform:uppercase; letter-spacing:.3px; }
        .stat b { display:block; font-size:26px; font-weight:700; color:#1a1a1a; line-height:1; margin-top:8px; }
        .stat-context { font-size:11px; color:#aaa; margin-top:auto; padding-top:12px; border-top:1px dashed #eee; line-height:1.4; }
        .stat-context strong { color:#1a1a1a; font-weight:700; }
        .stat-context.warn strong { color:var(--accent); }
        .stat-context.good strong { color:var(--green); }

        /* Insight cards — same shape as the faculty dashboard's insight panel */
        .insights-head { font-size:13px; font-weight:700; color:#333; margin:4px 0 12px; display:flex; align-items:center; gap:8px; }
        .insights-head i { color:var(--primary); }
        .insights-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(270px, 1fr)); gap:14px; margin-bottom:20px; align-items:stretch; }
        .insight-card { background:#fff; border-radius:12px; padding:16px 18px; display:flex; gap:13px; align-items:flex-start; border-left:4px solid #ccc; }
        .insight-card.tone-good { border-left-color:var(--green); }
        .insight-card.tone-warn { border-left-color:var(--orange); }
        .insight-card.tone-crit { border-left-color:var(--accent); }
        .insight-card.tone-info { border-left-color:var(--blue); }
        .insight-icon { width:34px; height:34px; border-radius:9px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:14px; }
        .insight-card.tone-good .insight-icon { background:#d1fae5; color:var(--green); }
        .insight-card.tone-warn .insight-icon { background:#fef3c7; color:var(--orange); }
        .insight-card.tone-crit .insight-icon { background:#fde8e8; color:var(--accent); }
        .insight-card.tone-info .insight-icon { background:#dbeafe; color:var(--blue); }
        .insight-title { font-size:12.5px; font-weight:700; color:#1a1a1a; margin-bottom:3px; }
        .insight-text { font-size:11.5px; color:#777; line-height:1.5; }

        .layout { display:grid; grid-template-columns:1fr 360px; gap:20px; align-items:start; }
        .card { background:#fff; border-radius:14px; overflow:hidden; }
        .card-head { padding:16px 20px; border-bottom:1px solid #f2f2f2; font-size:13.5px; font-weight:700; color:#222; display:flex; justify-content:space-between; align-items:center; }
        .card-head small { font-weight:500; color:#aaa; font-size:11px; }
        table { width:100%; border-collapse:collapse; }
        thead th { text-align:left; font-size:11px; color:#aaa; font-weight:600; padding:11px 16px; text-transform:uppercase; letter-spacing:.4px; background:#fafafa; border-bottom:1px solid #f5f5f5; }
        tbody td { padding:13px 16px; font-size:13px; border-bottom:1px solid #f8f8f8; vertical-align:middle; }
        tbody tr:last-child td { border-bottom:none; }
        .s-name { font-weight:600; color:#1a1a1a; }
        .s-email { font-size:11px; color:#aaa; }
        .pct { font-weight:700; }
        .pct.good { color:#059669; } .pct.mid { color:#d97706; } .pct.bad { color:var(--accent); }
        .pill { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; }
        .p-done { background:#d1fae5; color:#059669; }
        .p-prog { background:#fef3c7; color:#d97706; }
        .empty { padding:44px 20px; text-align:center; color:#bbb; font-size:13px; }

        .item-row { padding:13px 18px; border-bottom:1px solid #f6f6f6; }
        .item-row:last-child { border-bottom:none; }
        .item-q { font-size:12.5px; color:#333; line-height:1.5; margin-bottom:7px; }
        .item-q b { color:var(--primary); margin-right:5px; }
        .bar { height:6px; background:#f0f0f0; border-radius:4px; overflow:hidden; }
        .bar span { display:block; height:100%; border-radius:4px; }
        .item-meta { display:flex; justify-content:space-between; font-size:11px; color:#999; margin-top:5px; }

        .charts-row { display:grid; grid-template-columns:1fr 1fr 320px; gap:20px; margin-bottom:20px; align-items:stretch; }
        .chart-card { background:#fff; border-radius:14px; padding:18px 20px; }
        .chart-card h4 { font-size:13px; font-weight:700; color:#222; margin-bottom:14px; }
        .chart-card .chart-wrap { position:relative; height:220px; }
        .chart-empty { height:220px; display:flex; align-items:center; justify-content:center; color:#bbb; font-size:12.5px; text-align:center; }
        .donut-legend { display:flex; justify-content:center; gap:18px; margin-top:12px; font-size:11.5px; color:#666; }
        .donut-legend span { display:inline-flex; align-items:center; gap:6px; }
        .donut-legend i { width:9px; height:9px; border-radius:3px; display:inline-block; }

        @media (max-width:1100px) { .stats-row { grid-template-columns:repeat(3,1fr); } .layout { grid-template-columns:1fr; } .charts-row { grid-template-columns:1fr; } }
        @media (max-width:900px) { .main { margin-left:68px; } }
        @media (max-width:768px) { .main { margin-left:0; padding:16px; } .stats-row { grid-template-columns:repeat(2,1fr); } .card { overflow-x:auto; } .topbar { flex-direction:column; align-items:flex-start; } }
    </style>
</head>
<body>

@include('partials.faculty-sidebar', ['active' => 'quizzes'])

@php
    // Land back on the subject's own quiz list this quiz belongs to, not
    // all the way back at the top-level subject grid.
    $backRoute = $quiz->subject_id
        ? route('faculty.quizzes.subject', $quiz->subject_id)
        : route('faculty.quizzes');
@endphp

<main class="main">
    <div class="topbar">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('faculty.quizzes') }}">Class Quizzes</a>
                @if($quiz->subject_id)
                    <i class="fas fa-chevron-right" style="font-size:9px;"></i>
                    <a href="{{ $backRoute }}">{{ $quiz->subject?->code }}</a>
                @endif
                <i class="fas fa-chevron-right" style="font-size:9px;"></i> Results
            </div>
            <div class="page-title">{{ $quiz->title }}</div>
            <div class="page-sub">
                {{ $quiz->subject?->code ?? 'No subject' }} · {{ $quiz->items->count() }} question{{ $quiz->items->count() === 1 ? '' : 's' }} · {{ $quiz->totalPoints() }} points
                @if($quiz->due_at) · due {{ $quiz->due_at->format('M j, Y g:i A') }} @endif
            </div>
        </div>
        <div class="topbar-right">
            @include('partials.topbar-actions')
            <a href="{{ route('faculty.quizzes.edit', $quiz->id) }}" class="btn btn-outline"><i class="fas fa-pen"></i> Edit quiz</a>
            <a href="{{ $backRoute }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    @php
        $inProgress = $stats['started'] - $stats['submitted'];
        $completionPct = $stats['started'] > 0 ? round($stats['submitted'] / $stats['started'] * 100) : null;
        $benchmarkGap = $stats['average'] !== null ? round($stats['average'] - 75, 1) : null;
    @endphp
    <div class="stats-row">
        <div class="stat">
            <div class="stat-top">
                <div><span class="stat-lbl">Students started</span><b>{{ $stats['started'] }}</b></div>
                <span class="stat-icon si-blue"><i class="fas fa-users"></i></span>
            </div>
            <div class="stat-context {{ $inProgress > 0 ? 'warn' : '' }}">
                @if($stats['started'] === 0)
                    Share the quiz link to get started.
                @elseif($inProgress > 0)
                    <strong>{{ $inProgress }}</strong> haven't submitted yet.
                @else
                    All students who started have submitted.
                @endif
            </div>
        </div>
        <div class="stat">
            <div class="stat-top">
                <div><span class="stat-lbl">Submitted</span><b>{{ $stats['submitted'] }}</b></div>
                <span class="stat-icon si-green"><i class="fas fa-inbox"></i></span>
            </div>
            <div class="stat-context">
                @if($completionPct !== null)
                    <strong>{{ $completionPct }}%</strong> completion rate.
                @else
                    No attempts yet.
                @endif
            </div>
        </div>
        <div class="stat">
            <div class="stat-top">
                <div><span class="stat-lbl">Average score</span><b>{{ $stats['average'] !== null ? $stats['average'] . '%' : '—' }}</b></div>
                <span class="stat-icon si-orange"><i class="fas fa-chart-simple"></i></span>
            </div>
            <div class="stat-context {{ $benchmarkGap !== null && $benchmarkGap < 0 ? 'warn' : ($benchmarkGap !== null ? 'good' : '') }}">
                @if($benchmarkGap === null)
                    No submissions yet.
                @elseif($benchmarkGap >= 0)
                    <strong>+{{ $benchmarkGap }} pts</strong> above the 75% benchmark.
                @else
                    <strong>{{ $benchmarkGap }} pts</strong> below the 75% benchmark.
                @endif
            </div>
        </div>
        <div class="stat">
            <div class="stat-top">
                <div><span class="stat-lbl">Highest</span><b>{{ $stats['highest'] !== null ? $stats['highest'] . '%' : '—' }}</b></div>
                <span class="stat-icon si-green"><i class="fas fa-trophy"></i></span>
            </div>
            <div class="stat-context">
                {{ $stats['highest'] !== null ? 'Top submitted score.' : 'No submissions yet.' }}
            </div>
        </div>
        <div class="stat">
            <div class="stat-top">
                <div><span class="stat-lbl">Lowest</span><b>{{ $stats['lowest'] !== null ? $stats['lowest'] . '%' : '—' }}</b></div>
                <span class="stat-icon si-red"><i class="fas fa-arrow-down"></i></span>
            </div>
            <div class="stat-context {{ $stats['lowest'] !== null && $stats['lowest'] < 50 ? 'warn' : '' }}">
                @if($stats['lowest'] === null)
                    No submissions yet.
                @elseif($stats['lowest'] < 50)
                    <strong>May need extra support.</strong>
                @else
                    Lowest submitted score.
                @endif
            </div>
        </div>
    </div>

    <div class="charts-row">
        <div class="chart-card">
            <h4>Score distribution</h4>
            @if($stats['submitted'] > 0)
                <div class="chart-wrap"><canvas id="distChart"></canvas></div>
            @else
                <div class="chart-empty">No submissions yet</div>
            @endif
        </div>
        <div class="chart-card">
            <h4>Per-question accuracy</h4>
            @if($stats['submitted'] > 0 && $quiz->items->count() > 0)
                <div class="chart-wrap"><canvas id="qChart"></canvas></div>
            @else
                <div class="chart-empty">No data yet</div>
            @endif
        </div>
        <div class="chart-card">
            <h4>Pass rate <span style="font-weight:500;color:#aaa;font-size:11px;">(≥75%)</span></h4>
            @if($stats['submitted'] > 0)
                <div class="chart-wrap" style="height:180px;"><canvas id="passChart"></canvas></div>
                <div class="donut-legend">
                    <span><i style="background:#10b981;"></i> Passed ({{ $passRate }})</span>
                    <span><i style="background:#e5484d;"></i> Failed ({{ $stats['submitted'] - $passRate }})</span>
                </div>
            @else
                <div class="chart-empty">No submissions yet</div>
            @endif
        </div>
    </div>

    @if(!empty($insights))
        <div class="insights-head"><i class="fas fa-lightbulb"></i> What the charts above mean</div>
        <div class="insights-grid">
            @foreach($insights as $insight)
                <div class="insight-card tone-{{ $insight['tone'] }}">
                    <div class="insight-icon"><i class="fas {{ $insight['icon'] }}"></i></div>
                    <div>
                        <div class="insight-title">{{ $insight['title'] }}</div>
                        <div class="insight-text">{{ $insight['text'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="layout">
        <div class="card">
            <div class="card-head">Student submissions <small>{{ $attempts->count() }}</small></div>
            @if($attempts->isEmpty())
                <div class="empty">No student has opened this quiz yet. Share the quiz link to get started.</div>
            @else
                <table>
                    <thead><tr><th>Student</th><th>Status</th><th>Score</th><th>%</th><th>Submitted</th></tr></thead>
                    <tbody>
                    @foreach($attempts as $a)
                        <tr>
                            <td>
                                <div class="s-name">{{ $a->student?->name ?? 'Unknown' }}</div>
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
                            <td style="font-size:12px;color:#777;">{{ $a->submitted_at?->format('M j, g:i A') ?? 'started ' . $a->started_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card">
            <div class="card-head">Per-question accuracy <small>of {{ $stats['submitted'] }} submitted</small></div>
            @forelse($quiz->items as $i => $item)
                @php $st = $itemStats[$item->id]; $rate = $st['rate']; @endphp
                <div class="item-row">
                    <div class="item-q"><b>Q{{ $i + 1 }}</b>{{ \Illuminate\Support\Str::limit($item->question_text, 110) }}</div>
                    <div class="bar"><span style="width:{{ $rate ?? 0 }}%;background:{{ $rate === null ? '#ddd' : ($rate >= 75 ? '#10b981' : ($rate >= 50 ? '#f59e0b' : '#c0392b')) }};"></span></div>
                    <div class="item-meta">
                        <span>Correct: <b>{{ $item->correctLabel() }}</b> · {{ $item->points }} pt{{ $item->points === 1 ? '' : 's' }}</span>
                        <span>{{ $rate === null ? 'no data' : $st['right'] . ' correct (' . $rate . '%)' }}</span>
                    </div>
                </div>
            @empty
                <div class="empty">This quiz has no questions.</div>
            @endforelse
        </div>
    </div>
</main>

@include('partials.alerts')

@if($stats['submitted'] > 0)
<script>
Chart.defaults.font.family = "'Poppins', sans-serif";
Chart.defaults.color = '#999';

@if($stats['submitted'] > 0)
new Chart(document.getElementById('distChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_keys($buckets)) !!},
        datasets: [{
            data: {!! json_encode(array_values($buckets)) !!},
            backgroundColor: '#7B1D1D',
            borderRadius: 5,
            maxBarThickness: 34,
        }],
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
        datasets: [{
            data: [{{ $passRate }}, {{ $stats['submitted'] - $passRate }}],
            backgroundColor: ['#10b981', '#e5484d'],
            borderWidth: 0,
        }],
    },
    options: {
        responsive: true, maintainAspectRatio: false, cutout: '68%',
        plugins: { legend: { display: false } },
    },
});
@endif

@if($quiz->items->count() > 0)
new Chart(document.getElementById('qChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($quiz->items->values()->map(fn ($item, $idx) => 'Q' . ($idx + 1))) !!},
        datasets: [{
            data: {!! json_encode($quiz->items->map(fn ($i) => $itemStats[$i->id]['rate'] ?? 0)->values()) !!},
            backgroundColor: {!! json_encode($quiz->items->map(function ($i) use ($itemStats) {
                $rate = $itemStats[$i->id]['rate'] ?? null;
                if ($rate === null) return '#ddd';
                return $rate >= 75 ? '#10b981' : ($rate >= 50 ? '#f59e0b' : '#c0392b');
            })->values()) !!},
            borderRadius: 5,
            maxBarThickness: 34,
        }],
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => c.parsed.y + '% correct' } } },
        scales: { y: { beginAtZero: true, max: 100, grid: { color: '#f2f2f2' } }, x: { grid: { display: false } } },
    },
});
@endif
</script>
@endif
</body>
</html>
