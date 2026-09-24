<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.chart-kit')
    <style>
        :root {
            --primary: #7B1D1D;
            --primary-hover: #6a1818;
            --primary-light: #f5e8e8;
            --accent: #c0392b;
            --green: #10b981;
            --blue: #3b82f6;
            --orange: #f59e0b;
            --purple: #8b5cf6;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

        /* MAIN */
        .main { margin-left:230px; padding:26px 30px; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }

        /* TOP BAR */
        .topbar {
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:24px; gap:16px;
            position:relative; z-index:50;
        }
        .topbar-left { display:flex; align-items:center; gap:12px; }
        .page-title { font-size:26px; font-weight:700; color:#14283E; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .topbar-right { display:flex; align-items:center; gap:12px; }
        .btn {
            display:inline-flex; align-items:center; gap:7px;
            padding:9px 18px; border-radius:8px;
            font-size:13px; font-weight:600; font-family:'Poppins',sans-serif;
            cursor:pointer; border:none; text-decoration:none; transition:all .2s;
        }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-outline { background:white; color:var(--primary); border:1.5px solid var(--primary); }
        .btn-outline:hover { background:var(--primary-light); }

        /* STATS ROW */
        .stats-row {
            display:grid; grid-template-columns:repeat(4,1fr);
            gap:16px; margin-bottom:22px;
        }
        .stat-card {
            background:white; border-radius:14px; padding:20px 22px;
            display:flex; flex-direction:column; height:100%;
            text-decoration:none; color:inherit;
            box-shadow:0 2px 6px rgba(15,10,10,.08), 0 10px 22px -10px rgba(15,10,10,.22);
            transition:transform .18s ease, box-shadow .18s ease;
        }
        a.stat-card:hover {
            transform:translateY(-3px);
            box-shadow:0 4px 10px rgba(15,10,10,.1), 0 16px 30px -10px rgba(15,10,10,.3);
        }

        /* ANALYTICS SECTION */
        .section-label { font-size:12.5px; font-weight:700; color:#999; text-transform:uppercase; letter-spacing:.5px; margin:0 0 12px; }
        .doughnut-legend { display:flex; flex-direction:column; gap:10px; }
        .dl-row { display:flex; align-items:center; gap:9px; font-size:12.5px; color:#555; }
        .dl-row .dl-swatch { width:10px; height:10px; border-radius:3px; flex-shrink:0; }
        .dl-row .dl-val { margin-left:auto; font-weight:700; color:#1a1a1a; }

        /* Donut card: chart pinned to a sane size on the left, legend fills
           the rest of the (now much wider, 2-column) card instead of leaving
           empty space beside a chart that used to stretch full width. */
        .donut-row { display:flex; align-items:center; gap:28px; }
        .donut-row .chart-canvas-wrap { flex:0 0 190px; width:190px; height:190px!important; }
        .donut-row .doughnut-legend { flex:1; min-width:0; }
        .donut-insight {
            display:flex; gap:10px; align-items:flex-start;
            margin-top:18px; padding-top:16px; border-top:1px solid #f2f2f2;
            font-size:12px; color:#666; line-height:1.55;
        }
        .donut-insight i { color:var(--primary); font-size:13px; margin-top:2px; flex-shrink:0; }

        @media (max-width:560px) {
            .donut-row { flex-direction:column; align-items:stretch; }
            .donut-row .chart-canvas-wrap { width:100%; margin:0 auto; }
        }

        /* Comparison / benchmark line under each KPI number — pinned to the
           card's bottom edge so the dashed rule lines up across all 4 cards
           no matter how long each card's own comparison text runs. */
        .stat-top { flex:1; }
        .stat-context {
            font-size:10.5px; color:#aaa; margin-top:0;
            padding-top:6px; border-top:1px dashed #eee;
            line-height:1.4;
        }
        .stat-context strong { color:#1a1a1a; font-weight:700; }

        /* INSIGHTS */
        .insights-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
        .insights-refresh-btn {
            display:inline-flex; align-items:center; gap:7px;
            padding:7px 14px; border-radius:8px; border:1.5px solid var(--primary);
            background:white; color:var(--primary);
            font-size:11.5px; font-weight:600; font-family:'Poppins',sans-serif;
            cursor:pointer; transition:background .15s;
        }
        .insights-refresh-btn:hover { background:var(--primary-light); }
        .insights-refresh-btn:disabled { opacity:.6; cursor:default; }
        .insights-refresh-btn i.fa-spin { animation:spin .7s linear infinite; }
        @keyframes spin { to { transform:rotate(360deg); } }
        .insights-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(270px, 1fr)); gap:14px; margin-bottom:18px; align-items:stretch; }
        .insight-card {
            background:white; border-radius:12px; padding:16px 18px;
            display:flex; gap:13px; align-items:flex-start;
            border-left:4px solid #ccc;
        }
        .insight-card.tone-good { border-left-color:#059669; }
        .insight-card.tone-warn { border-left-color:#d97706; }
        .insight-card.tone-crit { border-left-color:var(--accent); }
        .insight-card.tone-info { border-left-color:#2563eb; }
        .insight-icon {
            width:34px; height:34px; border-radius:9px; flex-shrink:0;
            display:flex; align-items:center; justify-content:center; font-size:14px;
        }
        .insight-card.tone-good .insight-icon { background:#d1fae5; color:#059669; }
        .insight-card.tone-warn .insight-icon { background:#fef3c7; color:#d97706; }
        .insight-card.tone-crit .insight-icon { background:#fde8e8; color:var(--accent); }
        .insight-card.tone-info .insight-icon { background:#dbeafe; color:#2563eb; }
        .insight-title { font-size:12.5px; font-weight:700; color:#1a1a1a; margin-bottom:3px; }
        .insight-text { font-size:11.5px; color:#777; line-height:1.5; }
        .insights-empty { background:white; border-radius:12px; padding:20px; text-align:center; color:#aaa; font-size:13px; margin-bottom:18px; }

        .viz-grid-3 { grid-template-columns:repeat(3, 1fr) !important; }
        @media (max-width:1200px) { .viz-grid-3 { grid-template-columns:1fr 1fr !important; } }
        @media (max-width:768px)  { .viz-grid-3 { grid-template-columns:1fr !important; } }
        .stat-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; }
        .stat-icon {
            width:40px; height:40px; border-radius:10px;
            display:flex; align-items:center; justify-content:center; font-size:18px;
        }
        .si-red    { background:#fde8e8; color:var(--accent); }
        .si-green  { background:#d1fae5; color:var(--green); }
        .si-blue   { background:#dbeafe; color:var(--blue); }
        .si-orange { background:#fef3c7; color:var(--orange); }
        .si-purple { background:#ede9fe; color:var(--purple); }
        .stat-lbl  { font-family:'Montserrat',sans-serif; font-size:16px; font-weight:700; color:#1a1a1a; margin-bottom:12px; display:block; }
        .stat-num  { font-size:28px; font-weight:700; color:#1a1a1a; line-height:1; margin-bottom:0; }
        .stat-chg  { font-size:11px; color:var(--green); margin-top:2px; }
        .stat-chg.neutral { color:#999; }

        /* MAIN GRID */
        .main-grid {
            display:grid; grid-template-columns:1fr 340px;
            gap:18px; margin-bottom:18px;
        }

        /* CARDS — a real shadow (not just a hairline border) so every card
           reads as a raised surface no matter what colour sits behind it. */
        .card {
            background:white; border-radius:14px; padding:22px;
            box-shadow:0 2px 6px rgba(15,10,10,.08), 0 10px 22px -10px rgba(15,10,10,.22);
        }
        .card + .card { margin-top:18px; }

        .viz-card, .insights-empty {
            box-shadow:0 2px 6px rgba(15,10,10,.08), 0 10px 22px -10px rgba(15,10,10,.22);
            border-color:transparent;
        }
        .insight-card {
            box-shadow:0 2px 6px rgba(15,10,10,.07), 0 8px 18px -10px rgba(15,10,10,.18);
        }
        .card-head {
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:18px;
        }
        .card-title { font-size:14px; font-weight:600; color:#1a1a1a; }
        .card-link { font-size:12px; color:var(--accent); text-decoration:none; font-weight:500; }
        .card-link:hover { text-decoration:underline; }

        /* TABLE */
        table { width:100%; border-collapse:collapse; }
        thead th {
            text-align:left; font-size:11px; color:#aaa;
            font-weight:600; padding:0 10px 12px;
            text-transform:uppercase; letter-spacing:.4px;
        }
        tbody tr { border-top:1px solid #f5f5f5; }
        tbody td { padding:12px 10px; font-size:13px; vertical-align:middle; }
        tbody tr:hover { background:#fafafa; }

        .subj-badge {
            display:inline-block; padding:3px 9px; border-radius:5px;
            font-size:10px; font-weight:700;
        }
        .b-far  { background:#dbeafe; color:#2563eb; }
        .b-aud  { background:#fce7f3; color:#db2777; }
        .b-tax  { background:#d1fae5; color:#059669; }
        .b-ms   { background:#ede9fe; color:#7c3aed; }
        .b-rfbt { background:#fef3c7; color:#d97706; }
        .b-afar { background:#cffafe; color:#0891b2; }

        .diff-badge {
            display:inline-block; padding:3px 9px; border-radius:5px;
            font-size:10px; font-weight:600;
        }
        .d-easy   { background:#d1fae5; color:#059669; }
        .d-medium { background:#fef3c7; color:#d97706; }
        .d-hard   { background:#fde8e8; color:var(--accent); }

        .status-dot {
            width:7px; height:7px; border-radius:50%;
            display:inline-block; margin-right:5px;
        }
        .dot-active { background:var(--green); }
        .dot-draft  { background:#d1d5db; }

        .action-btn {
            width:28px; height:28px; border:none; border-radius:6px;
            cursor:pointer; font-size:12px;
            display:inline-flex; align-items:center; justify-content:center;
            transition:all .2s;
        }
        .ab-edit { background:#dbeafe; color:var(--blue); }
        .ab-del  { background:#fde8e8; color:var(--accent); }
        .ab-edit:hover { background:#bfdbfe; }
        .ab-del:hover  { background:#fecaca; }

        /* RIGHT COLUMN */
        .right-col { display:flex; flex-direction:column; gap:18px; }

        /* QUICK ACTIONS */
        .quick-actions { display:flex; flex-direction:column; gap:10px; }
        .qa-btn {
            display:flex; align-items:center; gap:12px;
            padding:13px 16px; border-radius:10px;
            text-decoration:none; transition:all .2s; cursor:pointer;
            border:none; width:100%; font-family:'Poppins',sans-serif;
        }
        .qa-btn .qa-icon {
            width:36px; height:36px; border-radius:8px;
            display:flex; align-items:center; justify-content:center;
            font-size:16px; flex-shrink:0;
        }
        .qa-title  { font-size:13px; font-weight:600; color:#1a1a1a; display:block; }
        .qa-sub    { font-size:11px; color:#999; display:block; }
        .qa-btn.primary-qa { background:var(--primary); }
        .qa-btn.primary-qa .qa-title,
        .qa-btn.primary-qa .qa-sub { color:rgba(255,255,255,.9); }
        .qa-btn.primary-qa .qa-icon { background:rgba(255,255,255,.2); color:white; }
        .qa-btn.secondary-qa { background:#f9f9f9; }
        .qa-btn.secondary-qa .qa-icon { background:white; }
        .qa-btn:hover { opacity:.9; transform:translateY(-1px); }

        /* SUBJECT DISTRIBUTION */
        .subj-dist-item { margin-bottom:14px; }
        .subj-dist-item:last-child { margin-bottom:0; }
        .subj-dist-top {
            display:flex; justify-content:space-between;
            font-size:12px; color:#555; margin-bottom:5px;
        }
        .subj-dist-top .val { font-weight:700; color:#1a1a1a; }
        .bar-bg { height:7px; background:#f0f0f0; border-radius:5px; overflow:hidden; }
        .bar-fill { height:100%; border-radius:5px; }

        /* ACTIVITY FEED */
        .activity-item {
            display:flex; align-items:center; gap:12px;
            padding:11px 0; border-bottom:1px solid #f5f5f5;
        }
        .activity-item:last-child { border-bottom:none; }
        .act-icon {
            width:34px; height:34px; border-radius:9px;
            display:flex; align-items:center; justify-content:center;
            font-size:14px; flex-shrink:0;
        }
        .act-name { font-size:13px; font-weight:600; color:#1a1a1a; margin-bottom:2px; }
        .act-sub  { font-size:11px; color:#999; }
        .act-time { font-size:11px; color:#bbb; white-space:nowrap; }

        /* BOTTOM ROW */
        .bottom-row {
            display:grid; grid-template-columns:repeat(2,1fr);
            gap:18px; align-items:stretch;
        }
        /* Both cards stretch to the same row height; pin the insight strip to
           the bottom of each so their divider lines stay level with each
           other no matter how long either card's caption text runs. */
        .bottom-row .card { display:flex; flex-direction:column; }
        .bottom-row .donut-insight { margin-top:auto; }

        .mini-stat {
            display:flex; align-items:center; gap:12px;
            padding:13px 0; border-bottom:1px solid #f5f5f5;
        }
        .mini-stat:last-child { border-bottom:none; }
        .mini-icon {
            width:32px; height:32px; border-radius:8px;
            display:flex; align-items:center; justify-content:center;
            font-size:13px; flex-shrink:0;
        }
        .mini-label { font-size:12px; color:#888; margin-bottom:1px; }
        .mini-val   { font-size:14px; font-weight:700; color:#1a1a1a; }

        @keyframes fadeUp {
            from { opacity:0; transform:translateY(14px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .a0 { animation:fadeUp .4s ease both; }
        .a1 { animation:fadeUp .4s .07s ease both; }
        .a2 { animation:fadeUp .4s .14s ease both; }
        .a3 { animation:fadeUp .4s .21s ease both; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            /* table overflow */
            .card { overflow-x: auto; }
            table { min-width: 520px; }
            /* bottom 3-col → 1-col */
            .bottom-row { grid-template-columns: 1fr !important; }
            /* right column stacks under left */
            .right-col { flex-direction: column; }
            /* stat numbers */
            .kpi-num { font-size: 22px; }
        }

        @media (max-width: 480px) {
            .kpi-num { font-size: 20px; }
            .card-title { font-size: 13px; }
            .qa-btn { padding: 10px 12px; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
@include('partials.faculty-sidebar', ['active' => 'dashboard'])

<!-- MAIN -->
<main class="main">

    <!-- TOPBAR -->
    <div class="topbar a0">
        <div class="topbar-left">
            <div>
                <div class="page-title">Faculty Dashboard</div>
                <div class="page-sub">Welcome back, {{ Auth::user()->name }}. Here's your overview.</div>
            </div>
        </div>
        <div class="topbar-right">
            @include('partials.topbar-actions')
        </div>
    </div>

    <!-- STATS -->
    <div class="stats-row a1">
        <a href="{{ route('faculty.test-bank') }}" class="stat-card" title="View test bank">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Total Questions</div>
                    <div class="stat-num">{{ number_format($stats['total_questions']) }}</div>
                    @if($stats['added_this_week'] > 0)
                        <div class="stat-chg"><i class="fas fa-arrow-up"></i> {{ $stats['added_this_week'] }} this week</div>
                    @else
                        <div class="stat-chg neutral">No new this week</div>
                    @endif
                </div>
                <div class="stat-icon si-red"><i class="fas fa-database"></i></div>
            </div>
            <div class="stat-context">Averaging <strong>{{ $stats['questions_per_subject'] }}</strong> questions per assigned subject.</div>
        </a>
        <a href="{{ route('faculty.performance') }}" class="stat-card" title="View student performance">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Active Students</div>
                    <div class="stat-num">{{ number_format($stats['active_students']) }}</div>
                    @if($stats['new_this_month'] > 0)
                        <div class="stat-chg"><i class="fas fa-arrow-up"></i> {{ $stats['new_this_month'] }} new this month</div>
                    @else
                        <div class="stat-chg neutral">With graded activity</div>
                    @endif
                </div>
                <div class="stat-icon si-green"><i class="fas fa-users"></i></div>
            </div>
            <div class="stat-context">
                @if($stats['engagement_delta'] === null)
                    Not enough history yet to compare to last week.
                @elseif($stats['engagement_delta'] > 0)
                    <strong style="color:var(--green);">&uarr; {{ $stats['engagement_delta'] }}</strong> more than last week{{ $stats['engagement_delta_pct'] !== null ? ' ('.$stats['engagement_delta_pct'].'%)' : '' }}.
                @elseif($stats['engagement_delta'] < 0)
                    <strong style="color:var(--accent);">&darr; {{ abs($stats['engagement_delta']) }}</strong> fewer than last week{{ $stats['engagement_delta_pct'] !== null ? ' ('.$stats['engagement_delta_pct'].'%)' : '' }}.
                @else
                    Unchanged from last week.
                @endif
            </div>
        </a>
        <a href="{{ route('faculty.performance') }}" class="stat-card" title="View student performance">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Overall Accuracy</div>
                    <div class="stat-num">{{ $stats['avg_score'] }}%</div>
                    @if($stats['avg_delta'] === null)
                        <div class="stat-chg neutral">Pooled across all attempts, all-time</div>
                    @elseif($stats['avg_delta'] >= 0)
                        <div class="stat-chg"><i class="fas fa-arrow-up"></i> {{ $stats['avg_delta'] }}% from last month</div>
                    @else
                        <div class="stat-chg" style="color:var(--accent);"><i class="fas fa-arrow-down"></i> {{ abs($stats['avg_delta']) }}% from last month</div>
                    @endif
                </div>
                <div class="stat-icon si-blue"><i class="fas fa-chart-bar"></i></div>
            </div>
            <div class="stat-context">
                @if($stats['benchmark_gap'] >= 0)
                    <strong style="color:var(--green);">{{ $stats['benchmark_gap'] }} pts above</strong> the {{ $benchmark }}% readiness benchmark.
                @else
                    <strong style="color:var(--accent);">{{ abs($stats['benchmark_gap']) }} pts below</strong> the {{ $benchmark }}% readiness benchmark.
                @endif
            </div>
        </a>
        <a href="{{ route('faculty.test-bank') }}" class="stat-card" title="View test bank">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Questions Added</div>
                    <div class="stat-num">{{ number_format($stats['added_this_week']) }}</div>
                    <div class="stat-chg neutral">This week</div>
                </div>
                <div class="stat-icon si-orange"><i class="fas fa-pen"></i></div>
            </div>
            <div class="stat-context">
                @if($stats['weekly_pace_avg'] == 0)
                    No recent baseline to compare against yet.
                @elseif($stats['added_this_week'] >= $stats['weekly_pace_avg'])
                    On pace — your 8-week average is <strong>{{ $stats['weekly_pace_avg'] }}</strong>/week.
                @else
                    Below your usual pace of <strong>~{{ $stats['weekly_pace_avg'] }}</strong>/week.
                @endif
            </div>
        </a>
    </div>

    <!-- INSIGHTS -->
    <div class="insights-head a1">
        <div class="section-label" style="margin-bottom:0;">Insights</div>
        <button type="button" class="insights-refresh-btn" id="insightsRefreshBtn"><i class="fas fa-rotate"></i> Regenerate</button>
    </div>
    <div id="insightsContainer" class="a1">
        @if(empty($insights))
            <div class="insights-empty"><i class="fas fa-circle-check" style="font-size:20px;color:#ccc;display:block;margin-bottom:8px;"></i>Nothing stands out right now — figures are within normal range.</div>
        @else
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
    </div>

    <!-- ANALYTICS -->
    <div class="section-label a1">Analytics</div>
    <section class="viz-grid-layout viz-grid-3 a1" aria-labelledby="analytics-title" style="margin-bottom:18px;">
        <div class="viz-card">
            <h4 id="analytics-title"><i class="fas fa-users"></i> Student Engagement</h4>
            <div class="viz-sub">Distinct students completing a graded quiz each week, last 8 weeks.</div>
            <div class="chart-canvas-wrap h-sm"><canvas id="engagementChart"></canvas></div>
        </div>
        <div class="viz-card">
            <h4><i class="fas fa-chart-line"></i> Accuracy Trend</h4>
            <div class="viz-sub">Average correct-answer rate vs. the {{ $benchmark }}% readiness benchmark (dashed line), last 8 weeks.</div>
            <div class="chart-canvas-wrap h-sm"><canvas id="accuracyChart"></canvas></div>
        </div>
        <div class="viz-card">
            <h4><i class="fas fa-user-graduate"></i> Student Readiness</h4>
            <div class="viz-sub">{{ $studentBand['measured'] }} measured student{{ $studentBand['measured'] === 1 ? '' : 's' }} · {{ $studentBand['total_active'] - $studentBand['measured'] > 0 ? ($studentBand['total_active'] - $studentBand['measured']) . ' not yet measurable' : 'all active students measured' }}.</div>
            <div class="chart-canvas-wrap h-sm"><canvas id="readinessChart"></canvas></div>
        </div>
    </section>

    <!-- MAIN GRID -->
    <div class="main-grid a2">
        <!-- LEFT -->
        <div>
            <!-- RECENT QUESTIONS -->
            <div class="card">
                <div class="card-head">
                    <span class="card-title">Recently Added Questions</span>
                    <a href="{{ route('faculty.test-bank') }}" class="card-link">View All</a>
                </div>
                @php
                    $badgeClass = [
                        'FAR' => 'b-far', 'AUD' => 'b-aud', 'TAX' => 'b-tax',
                        'MS' => 'b-ms', 'RFBT' => 'b-rfbt', 'AFAR' => 'b-afar',
                    ];
                    $diffClass = ['Easy' => 'd-easy', 'Medium' => 'd-medium', 'Hard' => 'd-hard'];
                @endphp
                <table>
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Subject</th>
                            <th>Difficulty</th>
                            <th>Status</th>
                            <th>Added</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentQuestions as $q)
                        <tr>
                            <td style="max-width:260px;">
                                <div style="font-weight:600;color:#1a1a1a;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:250px;">{{ $q['text'] }}</div>
                                <div style="font-size:11px;color:#aaa;">{{ $q['type_label'] }}</div>
                            </td>
                            <td><span class="subj-badge {{ $badgeClass[$q['subject']] ?? 'b-far' }}">{{ $q['subject'] }}</span></td>
                            <td><span class="diff-badge {{ $diffClass[$q['difficulty']] ?? 'd-medium' }}">{{ $q['difficulty'] }}</span></td>
                            <td>
                                @if($q['active'])
                                    <span class="status-dot dot-active"></span><span style="font-size:12px;">Active</span>
                                @else
                                    <span class="status-dot dot-draft"></span><span style="font-size:12px;color:#aaa;">Draft</span>
                                @endif
                            </td>
                            <td style="font-size:11px;color:#aaa;">{{ $q['ago'] }}</td>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('faculty.question.edit', $q['id']) }}" class="action-btn ab-edit"><i class="fas fa-pen"></i></a>
                                <form method="POST" action="{{ route('faculty.question.destroy', $q['id']) }}" style="display:inline;"
                                      data-confirm="This question and all of its variants will be permanently removed from the test bank."
                                      data-confirm-title="Delete this question?"
                                      data-confirm-ok="Yes, delete it"
                                      data-confirm-danger>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn ab-del" style="margin-left:4px;"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center;color:#aaa;padding:26px;">No questions in your subjects yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- STUDENT ACTIVITY -->
            <div class="card">
                <div class="card-head">
                    <span class="card-title">Recent Student Activity</span>
                    <a href="{{ route('faculty.performance') }}" class="card-link">View All</a>
                </div>
                @forelse($recentActivity as $act)
                <div class="activity-item">
                    <div class="act-icon" style="background:{{ $act['tone']['bg'] }};color:{{ $act['tone']['fg'] }};"><i class="fas {{ $act['tone']['icon'] }}"></i></div>
                    <div style="flex:1">
                        <div class="act-name">{{ $act['name'] }}</div>
                        <div class="act-sub">{!! $act['detail'] !!}</div>
                    </div>
                    <div class="act-time">{{ $act['ago'] }}</div>
                </div>
                @empty
                <div style="text-align:center;color:#aaa;padding:24px;font-size:13px;">No recent student activity in your subjects.</div>
                @endforelse
            </div>
        </div>

        <!-- RIGHT -->
        <div class="right-col">
            <!-- QUICK ACTIONS -->
            <div class="card">
                <div class="card-head"><span class="card-title">Quick Actions</span></div>
                <div class="quick-actions">
                    <a href="{{ route('faculty.question.create') }}" class="qa-btn primary-qa">
                        <div class="qa-icon"><i class="fas fa-plus"></i></div>
                        <div>
                            <span class="qa-title">Add New Question</span>
                            <span class="qa-sub">Add to test bank</span>
                        </div>
                    </a>
                    <a href="{{ route('faculty.performance') }}" class="qa-btn secondary-qa">
                        <div class="qa-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-users"></i></div>
                        <div>
                            <span class="qa-title">View Student Scores</span>
                            <span class="qa-sub">Monitor performance</span>
                        </div>
                    </a>
                    <a href="{{ route('faculty.reports') }}" class="qa-btn secondary-qa">
                        <div class="qa-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-file-export"></i></div>
                        <div>
                            <span class="qa-title">Export Report</span>
                            <span class="qa-sub">Download CSV / PDF</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- QUESTION DISTRIBUTION -->
            <div class="card">
                <div class="card-head"><span class="card-title">Questions by Subject</span></div>
                @if($bySubject->isEmpty())
                    <div style="text-align:center;color:#aaa;padding:16px;font-size:13px;">No subjects assigned.</div>
                @else
                    <div class="chart-canvas-wrap" style="height:{{ max(140, count($bySubject) * 42) }}px;"><canvas id="subjectChart"></canvas></div>
                @endif
            </div>

            <!-- TOP PERFORMING STUDENTS -->
            <div class="card">
                <div class="card-head"><span class="card-title">Top Performing Students</span></div>
                @php $rankStyles = [['#fde8e8','var(--accent)'],['#dbeafe','#2563eb'],['#d1fae5','#059669']]; @endphp
                @forelse($topStudents as $i => $st)
                <div class="mini-stat">
                    <div class="mini-icon" style="background:{{ $rankStyles[$i][0] ?? '#f1f5f9' }};color:{{ $rankStyles[$i][1] ?? '#64748b' }};font-weight:700;font-size:14px;">{{ $i + 1 }}</div>
                    <div style="flex:1">
                        <div class="mini-label">{{ $st['name'] }}</div>
                        <div class="mini-val" style="font-size:13px;">{{ $st['score'] }}% avg</div>
                    </div>
                </div>
                @empty
                <div style="text-align:center;color:#aaa;padding:24px;font-size:13px;">Not enough graded activity yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- BOTTOM ROW -->
    <div class="bottom-row a3">
        <div class="card">
            <div class="card-head"><span class="card-title">Question Type Breakdown</span></div>
            @if($byType['total'] === 0)
                <div style="text-align:center;color:#aaa;padding:16px;font-size:13px;">No questions yet.</div>
            @else
                <div class="donut-row">
                    <div class="chart-canvas-wrap"><canvas id="typeChart"></canvas></div>
                    <div class="doughnut-legend">
                        <div class="dl-row"><span class="dl-swatch" style="background:#2563eb;"></span> Multiple Choice <span class="dl-val">{{ number_format($byType['mcq']['count']) }} ({{ $byType['mcq']['pct'] }}%)</span></div>
                        <div class="dl-row"><span class="dl-swatch" style="background:#059669;"></span> True / False <span class="dl-val">{{ number_format($byType['tf']['count']) }} ({{ $byType['tf']['pct'] }}%)</span></div>
                    </div>
                </div>
                <div class="donut-insight"><i class="fas fa-lightbulb"></i> {{ $typeInsight }}</div>
            @endif
        </div>

        <div class="card">
            <div class="card-head"><span class="card-title">Difficulty Distribution</span></div>
            @if(($byDifficulty['easy']['count'] + $byDifficulty['medium']['count'] + $byDifficulty['hard']['count']) === 0)
                <div style="text-align:center;color:#aaa;padding:16px;font-size:13px;">No questions yet.</div>
            @else
                <div class="donut-row">
                    <div class="chart-canvas-wrap"><canvas id="difficultyChart"></canvas></div>
                    <div class="doughnut-legend">
                        <div class="dl-row"><span class="dl-swatch" style="background:#059669;"></span> Easy <span class="dl-val">{{ number_format($byDifficulty['easy']['count']) }} ({{ $byDifficulty['easy']['pct'] }}%)</span></div>
                        <div class="dl-row"><span class="dl-swatch" style="background:#d97706;"></span> Medium <span class="dl-val">{{ number_format($byDifficulty['medium']['count']) }} ({{ $byDifficulty['medium']['pct'] }}%)</span></div>
                        <div class="dl-row"><span class="dl-swatch" style="background:var(--accent);"></span> Hard <span class="dl-val">{{ number_format($byDifficulty['hard']['count']) }} ({{ $byDifficulty['hard']['pct'] }}%)</span></div>
                    </div>
                </div>
                <div class="donut-insight"><i class="fas fa-lightbulb"></i> {{ $difficultyInsight }}</div>
            @endif
        </div>
    </div>

</main>

<script>
(function () {
    const btn = document.getElementById('insightsRefreshBtn');
    const container = document.getElementById('insightsContainer');
    if (!btn || !container) return;

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function render(insights) {
        if (!insights.length) {
            container.innerHTML = '<div class="insights-empty"><i class="fas fa-circle-check" style="font-size:20px;color:#ccc;display:block;margin-bottom:8px;"></i>Nothing stands out right now — figures are within normal range.</div>';
            return;
        }
        container.innerHTML = '<div class="insights-grid">' + insights.map((i) => (
            '<div class="insight-card tone-' + escapeHtml(i.tone) + '">' +
                '<div class="insight-icon"><i class="fas ' + escapeHtml(i.icon) + '"></i></div>' +
                '<div>' +
                    '<div class="insight-title">' + escapeHtml(i.title) + '</div>' +
                    '<div class="insight-text">' + escapeHtml(i.text) + '</div>' +
                '</div>' +
            '</div>'
        )).join('') + '</div>';
    }

    btn.addEventListener('click', function () {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-rotate fa-spin"></i> Regenerating…';

        fetch(@json(route('faculty.dashboard.insights')), { headers: { 'Accept': 'application/json' } })
            .then((r) => r.json())
            .then((data) => { render(data.insights || []); })
            .catch(() => {})
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-rotate"></i> Regenerate';
            });
    });
})();

(function () {
    const P = Viz.palette;
    const trend = @json($weeklyTrend);
    const bySubject = @json($bySubject);
    const byType = @json($byType);
    const byDifficulty = @json($byDifficulty);
    const studentBand = @json($studentBand);
    const benchmark = @json($benchmark);
    const atRiskThreshold = @json($atRiskThreshold);
    const pluck = (rows, key) => rows.map((row) => row[key]);

    // Dashed horizontal rule at a fixed y-value — used to mark the board-readiness
    // benchmark on the accuracy trend so "on target" is a visual read, not a lookup.
    const benchmarkLine = (value, label) => ({
        id: 'benchmarkLine',
        afterDatasetsDraw(chart) {
            const { ctx, chartArea, scales } = chart;
            if (!chartArea || !scales.y) return;
            const y = scales.y.getPixelForValue(value);
            ctx.save();
            ctx.strokeStyle = P.crit;
            ctx.setLineDash([5, 4]);
            ctx.lineWidth = 1.5;
            ctx.beginPath();
            ctx.moveTo(chartArea.left, y);
            ctx.lineTo(chartArea.right, y);
            ctx.stroke();
            ctx.setLineDash([]);
            ctx.font = "600 9.5px 'Poppins', sans-serif";
            ctx.fillStyle = P.crit;
            ctx.textBaseline = 'bottom';
            ctx.fillText(label, chartArea.right - ctx.measureText(label).width - 4, y - 3);
            ctx.restore();
        },
    });

    Viz.chart('engagementChart', {
        type: 'bar',
        data: {
            labels: pluck(trend, 'label'),
            datasets: [Viz.bar({ label: 'Active students', data: pluck(trend, 'active_students'), backgroundColor: P.s1 })],
        },
        options: {
            scales: { y: Viz.countAxis(), x: Viz.catAxis() },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => {
                    const week = trend[c.dataIndex];
                    return week.active_students + ' student' + (week.active_students === 1 ? '' : 's') + ' · ' + week.quizzes + ' quiz' + (week.quizzes === 1 ? '' : 'zes');
                } } },
            },
        },
    });

    Viz.chart('accuracyChart', {
        type: 'line',
        data: {
            labels: pluck(trend, 'label'),
            datasets: [Viz.line({ label: 'Accuracy', data: pluck(trend, 'accuracy'), borderColor: P.s2, backgroundColor: 'rgba(42,120,214,.08)', pointBackgroundColor: P.s2, fill: true })],
        },
        options: {
            spanGaps: true,
            scales: { y: Viz.percentAxis(), x: Viz.catAxis() },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => c.raw === null ? 'No quizzes that week' : c.raw + '% accuracy (benchmark ' + benchmark + '%)' } },
            },
        },
        plugins: [benchmarkLine(benchmark, benchmark + '% benchmark')],
    });

    if (bySubject.length) {
        const subjectTotals = pluck(bySubject, 'total');
        const subjectAvg = subjectTotals.reduce((a, b) => a + b, 0) / subjectTotals.length;
        Viz.chart('subjectChart', {
            type: 'bar',
            data: {
                labels: pluck(bySubject, 'code'),
                datasets: [Viz.bar({ data: subjectTotals, backgroundColor: pluck(bySubject, 'color') })],
            },
            options: {
                indexAxis: 'y',
                scales: { x: Viz.countAxis(), y: Viz.catAxis() },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (c) => c.raw + ' question' + (c.raw === 1 ? '' : 's') + ' (avg ' + Math.round(subjectAvg) + ')' } },
                },
            },
            plugins: [Viz.referenceMarks(subjectTotals.map(() => subjectAvg), '#1a1a1a')],
        });
    }

    if (byType.total > 0) {
        Viz.chart('typeChart', {
            type: 'doughnut',
            data: {
                labels: ['Multiple Choice', 'True / False'],
                datasets: [{ data: [byType.mcq.count, byType.tf.count], backgroundColor: ['#2563eb', '#059669'], borderWidth: 2, borderColor: P.surface }],
            },
            options: {
                cutout: '62%',
                plugins: { legend: { display: false } },
            },
        });
    }

    const diffTotal = byDifficulty.easy.count + byDifficulty.medium.count + byDifficulty.hard.count;
    if (diffTotal > 0) {
        Viz.chart('difficultyChart', {
            type: 'doughnut',
            data: {
                labels: ['Easy', 'Medium', 'Hard'],
                datasets: [{ data: [byDifficulty.easy.count, byDifficulty.medium.count, byDifficulty.hard.count], backgroundColor: ['#059669', '#d97706', P.crit], borderWidth: 2, borderColor: P.surface }],
            },
            options: {
                cutout: '62%',
                plugins: { legend: { display: false } },
            },
        });
    }

    if (studentBand.measured > 0) {
        Viz.chart('readinessChart', {
            type: 'doughnut',
            data: {
                labels: ['Ready (≥' + benchmark + '%)', 'Developing', 'At risk (<' + atRiskThreshold + '%)'],
                datasets: [{
                    data: [studentBand.ready, studentBand.developing, studentBand.at_risk],
                    backgroundColor: [P.good, P.warn, P.crit],
                    borderWidth: 2, borderColor: P.surface,
                }],
            },
            options: {
                cutout: '58%',
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: (c) => c.label + ': ' + c.raw + ' (' + Math.round(c.raw / studentBand.measured * 100) + '%)' } },
                },
            },
        });
    } else {
        const el = document.getElementById('readinessChart');
        if (el && el.parentElement) el.parentElement.innerHTML = '<div class="viz-empty">Not enough graded attempts yet to measure readiness.</div>';
    }
})();
</script>

    @include('partials.alerts')
</body>
</html>
