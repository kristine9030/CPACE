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
    @include('partials.chart-filter-styles')
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

            /* Monochrome scale: every colour on this page is a step of the
               brand maroon. Order carries meaning (lighter = less / lower),
               and every coloured mark also has a text label. */
            --m-900: #4a1010;
            --m-800: #5f1515;
            --m-700: #7B1D1D;
            --m-600: #9a2b2b;
            --m-500: #b54848;
            --m-400: #cc7272;
            --m-300: #dfa2a2;
            --m-200: #eec9c9;
            --m-100: #f7e6e6;
            --m-50:  #fcf4f4;
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
            gap:18px; margin-bottom:26px;
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
        .donut-row { display:flex; align-items:center; gap:22px; }
        .donut-row .chart-canvas-wrap { flex:0 0 150px; width:150px; height:150px!important; }
        .donut-row { min-height:200px; }
        .donut-row .doughnut-legend { flex:1; min-width:0; }
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

        .stat-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; }
        .stat-icon {
            width:40px; height:40px; border-radius:10px;
            display:flex; align-items:center; justify-content:center; font-size:18px;
        }
        .si-red, .si-green, .si-blue, .si-orange, .si-purple { background:var(--m-100); color:var(--m-700); }
        .stat-lbl  { font-family:'Montserrat',sans-serif; font-size:16px; font-weight:700; color:#1a1a1a; margin-bottom:12px; display:block; }
        .stat-num  { font-size:28px; font-weight:700; color:#1a1a1a; line-height:1; margin-bottom:0; }
        .stat-chg  { font-size:11px; color:var(--m-700); font-weight:600; margin-top:2px; }
        .stat-chg.neutral { color:#999; }

        /* MAIN GRID */
        .main-grid {
            display:grid; grid-template-columns:minmax(0, 1fr) 340px;
            gap:18px; align-items:start;
        }
        @media (max-width:1100px) { .main-grid { grid-template-columns:1fr; } }

        /* CARDS — a real shadow (not just a hairline border) so every card
           reads as a raised surface no matter what colour sits behind it. */
        .card {
            background:white; border-radius:14px; padding:22px;
            box-shadow:0 2px 6px rgba(15,10,10,.08), 0 10px 22px -10px rgba(15,10,10,.22);
        }
        .card + .card { margin-top:18px; }

        .viz-card {
            box-shadow:0 2px 6px rgba(15,10,10,.08), 0 10px 22px -10px rgba(15,10,10,.22);
            border-color:transparent;
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
        /* Subjects are told apart by their code, not a colour. */
        .b-far, .b-aud, .b-tax, .b-ms, .b-rfbt, .b-afar { background:var(--m-100); color:var(--m-700); }

        .diff-badge {
            display:inline-block; padding:3px 9px; border-radius:5px;
            font-size:10px; font-weight:600;
        }
        .d-easy   { background:var(--m-50);  color:var(--m-500); }
        .d-medium { background:var(--m-100); color:var(--m-700); }
        .d-hard   { background:var(--m-200); color:var(--m-900); }

        .status-dot {
            width:7px; height:7px; border-radius:50%;
            display:inline-block; margin-right:5px;
        }
        .dot-active { background:var(--m-700); }
        .dot-draft  { background:#d1d5db; }

        .action-btn {
            width:28px; height:28px; border:none; border-radius:6px;
            cursor:pointer; font-size:12px;
            display:inline-flex; align-items:center; justify-content:center;
            transition:all .2s;
        }
        .ab-edit { background:var(--m-50); color:var(--m-600); }
        .ab-del  { background:var(--m-100); color:var(--m-800); }
        .ab-edit:hover { background:var(--m-100); }
        .ab-del:hover  { background:var(--m-200); }

        /* ── Sections: one rhythm for the whole page ──
           Every group is a label + an 18px-gap grid, and groups sit 26px
           apart, so the page reads as three blocks instead of loose cards. */
        .dash-section { margin-bottom:26px; }
        .dash-section:last-child { margin-bottom:0; }
        .dash-grid-3 { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:18px; align-items:stretch; }
        .dash-grid-3 .viz-card { display:flex; flex-direction:column; }
        /* Plots sit on a common baseline across a row; donuts centre in
           whatever height the row leaves them. */
        .dash-grid-3 .viz-card > .chart-canvas-wrap { margin-top:auto; }
        .dash-grid-3 .viz-card > .donut-row { margin:auto 0; }
        .stack { display:flex; flex-direction:column; gap:18px; min-width:0; }
        .stack > .card + .card { margin-top:0; }
        @media (max-width:1200px) { .dash-grid-3 { grid-template-columns:repeat(2, minmax(0, 1fr)); } }
        @media (max-width:768px)  { .dash-grid-3 { grid-template-columns:1fr; } }

        /* Insight tip: a lightbulb in the tone of the chart's most urgent
           insight, pinned to the right of the chart title. */
        .viz-card h4 .insight-tip { margin-left:auto; }
        .viz-card h4 .insight-tip i { color:inherit; font-size:12px; } /* chart-kit colours every h4 icon maroon */
        .cx-tip-btn.insight-tip { width:26px; height:26px; font-size:12px; cursor:pointer; }
        /* Urgency is intensity: the more urgent, the darker the bulb. */
        .cx-tip-btn.insight-tip.tone-crit { background:var(--m-700); color:#fff; }
        .cx-tip-btn.insight-tip.tone-warn { background:var(--m-200); color:var(--m-800); }
        .cx-tip-btn.insight-tip.tone-info { background:var(--m-100); color:var(--m-700); }
        .cx-tip-btn.insight-tip.tone-good { background:var(--m-50); color:var(--m-500); }
        .cx-tip-btn.insight-tip:hover, .cx-tip-btn.insight-tip[aria-expanded="true"] { filter:brightness(.95); box-shadow:0 0 0 3px rgba(0,0,0,.05); }

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
        .no-anim .a0, .no-anim .a1, .no-anim .a2, .no-anim .a3 { animation:none; }

        /* Filter bar extras (the bar itself comes from partials.chart-filter-styles). */
        .btn-sm { padding:6px 12px; font-size:12px; }
        .range-cancel { background:#f1f1f3; color:#555; }
        .range-cancel:hover { background:#e6e6e9; }
        #dashBody { transition:opacity .2s; }
        #dashBody.is-loading { opacity:.55; pointer-events:none; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            /* table overflow */
            .card { overflow-x: auto; }
            table { min-width: 520px; }
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

    {{-- Filters re-fetch this page and swap everything below in place (no
         full reload), and are mirrored into the URL so a filtered view can be
         bookmarked. Only this faculty member's own subjects and sections are
         offered. --}}
    <section class="dash-filters a0" aria-label="Filters">
        <div class="filter-label"><i class="fas fa-filter"></i> Filters <x-tip label="How the filters apply">Student figures follow the date range, subject and section. Test-bank figures (questions, type and difficulty mix) follow the subject only; "Questions Added" also follows the date range.</x-tip></div>

        <div class="filter-field">
            <button type="button" class="filter-trigger" id="rangeTrigger" aria-haspopup="dialog" aria-expanded="false" aria-controls="rangePop">
                <span class="filter-ico"><i class="far fa-calendar"></i></span>
                <span class="filter-text"><span class="filter-cap">Date Range</span><span class="filter-val" id="rangeLabel">&nbsp;</span></span>
                <i class="fas fa-chevron-down filter-chev"></i>
            </button>
            <div class="range-pop" id="rangePop" role="dialog" aria-label="Choose a date range" hidden>
                <div class="range-presets">
                    <button type="button" data-preset="7">Last 7 days</button>
                    <button type="button" data-preset="30">Last 30 days</button>
                    <button type="button" data-preset="90">Last 90 days</button>
                    <button type="button" data-preset="365">Last 12 months</button>
                    <button type="button" data-preset="this-month">This month</button>
                    <button type="button" data-preset="last-month">Last month</button>
                </div>
                <div class="range-custom">
                    <label>From <input type="date" id="rangeFrom"></label>
                    <label>To <input type="date" id="rangeTo"></label>
                </div>
                <div class="range-error" id="rangeError" role="alert"></div>
                <div class="range-actions">
                    <button type="button" class="btn btn-sm range-cancel" id="rangeCancel">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" id="rangeApply">Apply</button>
                </div>
            </div>
        </div>

        <label class="filter-field">
            <span class="filter-ico"><i class="fas fa-book-open"></i></span>
            <span class="filter-text">
                <span class="filter-cap">Subject</span>
                <select id="filterSubject" aria-label="Subject">
                    <option value="">All my subjects</option>
                    @foreach($assigned as $s)
                        <option value="{{ $s->id }}" @selected($filters['subject'] === $s->id)>{{ $s->code }} — {{ $s->name }}</option>
                    @endforeach
                </select>
            </span>
            <i class="fas fa-chevron-down filter-chev"></i>
        </label>

        @if($sectionOptions->isNotEmpty())
        <label class="filter-field">
            <span class="filter-ico"><i class="fas fa-users"></i></span>
            <span class="filter-text">
                <span class="filter-cap">Section</span>
                <select id="filterSection" aria-label="Section">
                    <option value="">All my sections</option>
                    @foreach($sectionOptions as $name)
                        <option value="{{ $name }}" @selected($filters['section'] === $name)>{{ $name }}</option>
                    @endforeach
                </select>
            </span>
            <i class="fas fa-chevron-down filter-chev"></i>
        </label>
        @endif

        <span class="filter-status" id="filterStatus" aria-live="polite"></span>
        <button type="button" class="filter-reset" id="filterReset"><i class="fas fa-rotate"></i> Reset Filters</button>
    </section>

    <div id="dashBody">

    <!-- STATS -->
    <div class="stats-row a1">
        <a href="{{ route('faculty.test-bank') }}" class="stat-card" title="View test bank">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Total Questions</div>
                    <div class="stat-num">{{ number_format($stats['total_questions']) }}</div>
                    @if($stats['added_in_range'] > 0)
                        <div class="stat-chg"><i class="fas fa-arrow-up"></i> {{ $stats['added_in_range'] }} added in this period</div>
                    @else
                        <div class="stat-chg neutral">None added in this period</div>
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
                    @if($stats['new_in_range'] > 0)
                        <div class="stat-chg"><i class="fas fa-arrow-up"></i> {{ $stats['new_in_range'] }} new in this period</div>
                    @else
                        <div class="stat-chg neutral">With graded activity in this period</div>
                    @endif
                </div>
                <div class="stat-icon si-green"><i class="fas fa-users"></i></div>
            </div>
            <div class="stat-context">
                @if($stats['engagement_delta'] > 0)
                    <strong style="color:var(--m-700);">&uarr; {{ $stats['engagement_delta'] }}</strong> more than the period before{{ $stats['engagement_delta_pct'] !== null ? ' ('.$stats['engagement_delta_pct'].'%)' : '' }}.
                @elseif($stats['engagement_delta'] < 0)
                    <strong style="color:var(--accent);">&darr; {{ abs($stats['engagement_delta']) }}</strong> fewer than the period before{{ $stats['engagement_delta_pct'] !== null ? ' ('.$stats['engagement_delta_pct'].'%)' : '' }}.
                @else
                    Unchanged from the period before.
                @endif
            </div>
        </a>
        <a href="{{ route('faculty.performance') }}" class="stat-card" title="View student performance">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Overall Accuracy</div>
                    <div class="stat-num">{{ $stats['avg_score'] === null ? '—' : $stats['avg_score'].'%' }}</div>
                    @if($stats['avg_delta'] === null)
                        <div class="stat-chg neutral">Pooled across attempts in this period</div>
                    @elseif($stats['avg_delta'] >= 0)
                        <div class="stat-chg"><i class="fas fa-arrow-up"></i> {{ $stats['avg_delta'] }} pts vs the period before</div>
                    @else
                        <div class="stat-chg" style="color:var(--accent);"><i class="fas fa-arrow-down"></i> {{ abs($stats['avg_delta']) }} pts vs the period before</div>
                    @endif
                </div>
                <div class="stat-icon si-blue"><i class="fas fa-chart-bar"></i></div>
            </div>
            <div class="stat-context">
                @if($stats['benchmark_gap'] === null)
                    No graded quizzes in this period.
                @elseif($stats['benchmark_gap'] >= 0)
                    <strong style="color:var(--m-700);">{{ $stats['benchmark_gap'] }} pts above</strong> the {{ $benchmark }}% readiness benchmark.
                @else
                    <strong style="color:var(--accent);">{{ abs($stats['benchmark_gap']) }} pts below</strong> the {{ $benchmark }}% readiness benchmark.
                @endif
            </div>
        </a>
        <a href="{{ route('faculty.test-bank') }}" class="stat-card" title="View test bank">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Questions Added</div>
                    <div class="stat-num">{{ number_format($stats['added_in_range']) }}</div>
                    <div class="stat-chg neutral">In this period</div>
                </div>
                <div class="stat-icon si-orange"><i class="fas fa-pen"></i></div>
            </div>
            <div class="stat-context">
                About <strong>{{ $stats['weekly_pace'] }}</strong>/week · <strong>{{ $stats['added_before'] }}</strong> in the period before.
            </div>
        </a>
    </div>

    {{-- Three groups, each on the same grid and spacing: how students are
         doing, what the test bank looks like, and what happened recently.
         A chart's insight (if any) sits behind the lightbulb in its header. --}}

    <!-- STUDENT PERFORMANCE -->
    <section class="dash-section a1" aria-labelledby="sec-students">
        <div class="section-label" id="sec-students">Student Performance</div>
        <div class="dash-grid-3">
            <div class="viz-card">
                <h4><i class="fas fa-users"></i> Student Engagement @include('faculty.partials.chart-insight', ['tip' => $chartInsights['engagement'] ?? null])</h4>
                <div class="viz-sub">Distinct students completing a graded quiz per {{ $range['bucket'] }}.</div>
                <div class="chart-canvas-wrap h-sm"><canvas id="engagementChart"></canvas></div>
            </div>
            <div class="viz-card">
                <h4><i class="fas fa-chart-line"></i> Accuracy Trend @include('faculty.partials.chart-insight', ['tip' => $chartInsights['accuracy'] ?? null])</h4>
                <div class="viz-sub">Correct-answer rate per {{ $range['bucket'] }} vs. the {{ $benchmark }}% readiness benchmark (dashed line).</div>
                <div class="chart-canvas-wrap h-sm"><canvas id="accuracyChart"></canvas></div>
            </div>
            <div class="viz-card">
                <h4><i class="fas fa-user-graduate"></i> Student Readiness @include('faculty.partials.chart-insight', ['tip' => $chartInsights['readiness'] ?? null])</h4>
                <div class="viz-sub">{{ $studentBand['measured'] }} measured student{{ $studentBand['measured'] === 1 ? '' : 's' }} · {{ $studentBand['total_active'] - $studentBand['measured'] > 0 ? ($studentBand['total_active'] - $studentBand['measured']) . ' not yet measurable' : 'all active students measured' }} · as of the range end.</div>
                <div class="chart-canvas-wrap h-sm"><canvas id="readinessChart"></canvas></div>
            </div>
        </div>
    </section>

    <!-- TEST BANK -->
    <section class="dash-section a2" aria-labelledby="sec-bank">
        <div class="section-label" id="sec-bank">Test Bank</div>
        <div class="dash-grid-3">
            <div class="viz-card">
                <h4><i class="fas fa-layer-group"></i> Questions by Subject @include('faculty.partials.chart-insight', ['tip' => $chartInsights['subjects'] ?? null])</h4>
                <div class="viz-sub">Questions per subject; the dark rule marks your per-subject average.</div>
                @if($bySubject->isEmpty())
                    <div class="chart-canvas-wrap h-sm"><div class="viz-empty">No subjects assigned.</div></div>
                @else
                    <div class="chart-canvas-wrap h-sm"><canvas id="subjectChart"></canvas></div>
                @endif
            </div>
            <div class="viz-card">
                <h4><i class="fas fa-list-check"></i> Question Type @include('faculty.partials.chart-insight', ['tip' => $chartInsights['type'] ?? null])</h4>
                <div class="viz-sub">Multiple choice vs. true / false in the bank.</div>
                @if($byType['total'] === 0)
                    <div class="chart-canvas-wrap h-sm"><div class="viz-empty">No questions yet.</div></div>
                @else
                    <div class="donut-row">
                        <div class="chart-canvas-wrap"><canvas id="typeChart"></canvas></div>
                        <div class="doughnut-legend">
                            <div class="dl-row"><span class="dl-swatch" style="background:var(--m-700);"></span> Multiple Choice <span class="dl-val">{{ number_format($byType['mcq']['count']) }} ({{ $byType['mcq']['pct'] }}%)</span></div>
                            <div class="dl-row"><span class="dl-swatch" style="background:var(--m-300);"></span> True / False <span class="dl-val">{{ number_format($byType['tf']['count']) }} ({{ $byType['tf']['pct'] }}%)</span></div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="viz-card">
                <h4><i class="fas fa-gauge-high"></i> Difficulty Mix @include('faculty.partials.chart-insight', ['tip' => $chartInsights['difficulty'] ?? null])</h4>
                <div class="viz-sub">Easy / medium / hard split of the bank.</div>
                @if(($byDifficulty['easy']['count'] + $byDifficulty['medium']['count'] + $byDifficulty['hard']['count']) === 0)
                    <div class="chart-canvas-wrap h-sm"><div class="viz-empty">No questions yet.</div></div>
                @else
                    <div class="donut-row">
                        <div class="chart-canvas-wrap"><canvas id="difficultyChart"></canvas></div>
                        <div class="doughnut-legend">
                            <div class="dl-row"><span class="dl-swatch" style="background:var(--m-300);"></span> Easy <span class="dl-val">{{ number_format($byDifficulty['easy']['count']) }} ({{ $byDifficulty['easy']['pct'] }}%)</span></div>
                            <div class="dl-row"><span class="dl-swatch" style="background:var(--m-500);"></span> Medium <span class="dl-val">{{ number_format($byDifficulty['medium']['count']) }} ({{ $byDifficulty['medium']['pct'] }}%)</span></div>
                            <div class="dl-row"><span class="dl-swatch" style="background:var(--m-700);"></span> Hard <span class="dl-val">{{ number_format($byDifficulty['hard']['count']) }} ({{ $byDifficulty['hard']['pct'] }}%)</span></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- ACTIVITY -->
    <section class="dash-section a3" aria-labelledby="sec-activity">
        <div class="section-label" id="sec-activity">Activity</div>
        <div class="main-grid">
            <div class="stack">
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
                    <div style="text-align:center;color:#aaa;padding:24px;font-size:13px;">No student activity in your subjects in this period.</div>
                    @endforelse
                </div>
            </div>

            <div class="stack">
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
                            <div class="qa-icon" style="background:var(--m-100);color:var(--m-700);"><i class="fas fa-users"></i></div>
                            <div>
                                <span class="qa-title">View Student Scores</span>
                                <span class="qa-sub">Monitor performance</span>
                            </div>
                        </a>
                        <a href="{{ route('faculty.reports') }}" class="qa-btn secondary-qa">
                            <div class="qa-icon" style="background:var(--m-100);color:var(--m-700);"><i class="fas fa-file-export"></i></div>
                            <div>
                                <span class="qa-title">Export Report</span>
                                <span class="qa-sub">Download CSV / PDF</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- TOP PERFORMING STUDENTS -->
                <div class="card">
                    <div class="card-head"><span class="card-title">Top Performing Students</span><span style="font-size:10.5px;color:#aaa;">In this period</span></div>
                    @php $rankStyles = [['var(--m-700)','#fff'],['var(--m-200)','var(--m-800)'],['var(--m-100)','var(--m-700)']]; @endphp
                    @forelse($topStudents as $i => $st)
                    <div class="mini-stat">
                        <div class="mini-icon" style="background:{{ $rankStyles[$i][0] ?? 'var(--m-50)' }};color:{{ $rankStyles[$i][1] ?? 'var(--m-500)' }};font-weight:700;font-size:14px;">{{ $i + 1 }}</div>
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
    </section>

    <script type="application/json" id="dashData">{!! json_encode([
        'weeklyTrend' => $weeklyTrend,
        'bySubject' => $bySubject,
        'byType' => $byType,
        'byDifficulty' => $byDifficulty,
        'studentBand' => $studentBand,
        'benchmark' => $benchmark,
        'atRiskThreshold' => $atRiskThreshold,
        'filters' => $filters,
    ], JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    </div>{{-- /#dashBody --}}

</main>

<script>
/* ── Charts ─────────────────────────────────────────────────────────────
   Built from the JSON block inside #dashBody, so they can be rebuilt after a
   filter change swaps that block for a freshly rendered one. */
function initFacultyCharts() {
    const P = Viz.palette;
    const css = getComputedStyle(document.documentElement);
    const M = (step) => css.getPropertyValue('--m-' + step).trim();
    const data = JSON.parse(document.getElementById('dashData').textContent);
    const { weeklyTrend: trend, bySubject, byType, byDifficulty, studentBand, benchmark, atRiskThreshold } = data;
    const pluck = (rows, key) => rows.map((row) => row[key]);
    const xAxis = Viz.catAxis({ ticks: { color: P.ink, padding: 6, maxRotation: 0, autoSkip: true, maxTicksLimit: 8 } });

    // Dashed horizontal rule at a fixed y-value — used to mark the board-readiness
    // benchmark on the accuracy trend so "on target" is a visual read, not a lookup.
    const benchmarkLine = (value, label) => ({
        id: 'benchmarkLine',
        afterDatasetsDraw(chart) {
            const { ctx, chartArea, scales } = chart;
            if (!chartArea || !scales.y) return;
            const y = scales.y.getPixelForValue(value);
            ctx.save();
            ctx.strokeStyle = M(900);
            ctx.setLineDash([5, 4]);
            ctx.lineWidth = 1.5;
            ctx.beginPath();
            ctx.moveTo(chartArea.left, y);
            ctx.lineTo(chartArea.right, y);
            ctx.stroke();
            ctx.setLineDash([]);
            ctx.font = "600 9.5px 'Poppins', sans-serif";
            ctx.fillStyle = M(900);
            ctx.textBaseline = 'bottom';
            ctx.fillText(label, chartArea.right - ctx.measureText(label).width - 4, y - 3);
            ctx.restore();
        },
    });

    Viz.chart('engagementChart', {
        type: 'bar',
        data: {
            labels: pluck(trend, 'label'),
            datasets: [Viz.bar({ label: 'Active students', data: pluck(trend, 'active_students'), backgroundColor: M(600) })],
        },
        options: {
            scales: { y: Viz.countAxis(), x: xAxis },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => {
                    const period = trend[c.dataIndex];
                    return period.active_students + ' student' + (period.active_students === 1 ? '' : 's') + ' · ' + period.quizzes + ' quiz' + (period.quizzes === 1 ? '' : 'zes');
                } } },
            },
        },
    });

    Viz.chart('accuracyChart', {
        type: 'line',
        data: {
            labels: pluck(trend, 'label'),
            datasets: [Viz.line({ label: 'Accuracy', data: pluck(trend, 'accuracy'), borderColor: M(700), backgroundColor: 'rgba(123,29,29,.08)', pointBackgroundColor: M(700), fill: true, pointRadius: trend.length > 16 ? 0 : 3 })],
        },
        options: {
            spanGaps: true,
            scales: { y: Viz.percentAxis(), x: xAxis },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => c.raw === null ? 'No quizzes in this period' : c.raw + '% accuracy (benchmark ' + benchmark + '%)' } },
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
                datasets: [Viz.bar({ data: subjectTotals, backgroundColor: M(600) })],
            },
            options: {
                indexAxis: 'y',
                scales: { x: Viz.countAxis(), y: Viz.catAxis() },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (c) => c.raw + ' question' + (c.raw === 1 ? '' : 's') + (bySubject.length > 1 ? ' (avg ' + Math.round(subjectAvg) + ')' : '') } },
                },
            },
            // An average mark only means something against other subjects.
            plugins: bySubject.length > 1 ? [Viz.referenceMarks(subjectTotals.map(() => subjectAvg), '#1a1a1a')] : [],
        });
    }

    if (byType.total > 0) {
        Viz.chart('typeChart', {
            type: 'doughnut',
            data: {
                labels: ['Multiple Choice', 'True / False'],
                datasets: [{ data: [byType.mcq.count, byType.tf.count], backgroundColor: [M(700), M(300)], borderWidth: 2, borderColor: P.surface }],
            },
            options: { cutout: '62%', plugins: { legend: { display: false } } },
        });
    }

    const diffTotal = byDifficulty.easy.count + byDifficulty.medium.count + byDifficulty.hard.count;
    if (diffTotal > 0) {
        Viz.chart('difficultyChart', {
            type: 'doughnut',
            data: {
                labels: ['Easy', 'Medium', 'Hard'],
                datasets: [{ data: [byDifficulty.easy.count, byDifficulty.medium.count, byDifficulty.hard.count], backgroundColor: [M(300), M(500), M(700)], borderWidth: 2, borderColor: P.surface }],
            },
            options: { cutout: '62%', plugins: { legend: { display: false } } },
        });
    }

    if (studentBand.measured > 0) {
        Viz.chart('readinessChart', {
            type: 'doughnut',
            data: {
                labels: ['Ready (≥' + benchmark + '%)', 'Developing', 'At risk (<' + atRiskThreshold + '%)'],
                datasets: [{
                    data: [studentBand.ready, studentBand.developing, studentBand.at_risk],
                    // Darker = further along: Ready, Developing, At risk.
                    backgroundColor: [M(700), M(500), M(300)],
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
}
initFacultyCharts();

/* ── Filters ── */
(function () {
    const DEFAULTS = @json($defaults);
    const MAX_DAYS = 366;
    let filters = @json($filters);
    let inflight = null;

    const $ = (id) => document.getElementById(id);
    const iso = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    const parse = (s) => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
    const pretty = (s) => parse(s).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    const daysBetween = (a, b) => Math.round((parse(b) - parse(a)) / 86400000) + 1;

    function presetRange(key) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (key === 'this-month') { return [iso(new Date(today.getFullYear(), today.getMonth(), 1)), iso(today)]; }
        if (key === 'last-month') { return [iso(new Date(today.getFullYear(), today.getMonth() - 1, 1)), iso(new Date(today.getFullYear(), today.getMonth(), 0))]; }
        const from = new Date(today);
        from.setDate(from.getDate() - (Number(key) - 1));
        return [iso(from), iso(today)];
    }

    function query() {
        const params = new URLSearchParams();
        if (!(filters.from === DEFAULTS.from && filters.to === DEFAULTS.to)) { params.set('from', filters.from); params.set('to', filters.to); }
        if (filters.subject) { params.set('subject', filters.subject); }
        if (filters.section) { params.set('section', filters.section); }
        const qs = params.toString();
        return qs ? '?' + qs : '';
    }

    function syncControls() {
        $('rangeLabel').textContent = pretty(filters.from) + ' – ' + pretty(filters.to);
        $('filterSubject').value = filters.subject ?? '';
        if ($('filterSection')) { $('filterSection').value = filters.section ?? ''; }
        document.querySelectorAll('.range-presets button').forEach((b) => {
            const [from, to] = presetRange(b.dataset.preset);
            b.classList.toggle('active', from === filters.from && to === filters.to);
        });
    }

    // Re-render the page server-side for the new filters and swap the body in
    // place: every card here is server-rendered, so this keeps one source of
    // truth instead of re-implementing each card in script.
    function load() {
        if (inflight) { inflight.abort(); }
        const controller = inflight = new AbortController();
        const url = location.pathname + query();
        const body = $('dashBody');

        history.replaceState(null, '', url + location.hash);
        syncControls();
        body.classList.add('is-loading');
        $('filterStatus').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating…';

        fetch(url, { headers: { 'Accept': 'text/html' }, signal: controller.signal })
            .then((res) => { if (!res.ok) { throw new Error(res.status); } return res.text(); })
            .then((html) => {
                const fresh = new DOMParser().parseFromString(html, 'text/html').getElementById('dashBody');
                if (!fresh) { throw new Error('missing body'); }
                body.innerHTML = fresh.innerHTML;
                // The server normalises bad input, so adopt what it used.
                filters = JSON.parse($('dashData').textContent).filters;
                syncControls();
                history.replaceState(null, '', location.pathname + query() + location.hash);
                initFacultyCharts();
                $('filterStatus').textContent = '';
            })
            .catch((err) => {
                if (err.name === 'AbortError') { return; }
                $('filterStatus').innerHTML = '<i class="fas fa-triangle-exclamation" style="color:#b91c1c;"></i> Could not update — try again.';
            })
            .finally(() => { if (inflight === controller) { body.classList.remove('is-loading'); } });
    }

    const rangePop = $('rangePop');
    const rangeTrigger = $('rangeTrigger');
    function openRange(open) {
        rangePop.hidden = !open;
        rangeTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
            $('rangeFrom').value = filters.from;
            $('rangeTo').value = filters.to;
            $('rangeError').textContent = '';
        }
    }

    rangeTrigger.addEventListener('click', () => openRange(rangePop.hidden));
    $('rangeCancel').addEventListener('click', () => { openRange(false); rangeTrigger.focus(); });
    document.addEventListener('click', (e) => {
        if (!rangePop.hidden && !rangePop.contains(e.target) && !rangeTrigger.contains(e.target)) { openRange(false); }
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !rangePop.hidden) { openRange(false); rangeTrigger.focus(); }
    });
    document.querySelectorAll('.range-presets button').forEach((b) => {
        b.addEventListener('click', () => { [filters.from, filters.to] = presetRange(b.dataset.preset); openRange(false); load(); });
    });
    $('rangeApply').addEventListener('click', () => {
        const from = $('rangeFrom').value;
        const to = $('rangeTo').value;
        if (!from || !to) { $('rangeError').textContent = 'Pick both a start and an end date.'; return; }
        if (from > to) { $('rangeError').textContent = 'The start date must be on or before the end date.'; return; }
        if (daysBetween(from, to) > MAX_DAYS) { $('rangeError').textContent = 'Pick a range of one year or less.'; return; }
        filters.from = from;
        filters.to = to;
        openRange(false);
        load();
    });
    $('filterSubject').addEventListener('change', (e) => { filters.subject = e.target.value ? Number(e.target.value) : null; load(); });
    if ($('filterSection')) { $('filterSection').addEventListener('change', (e) => { filters.section = e.target.value || null; load(); }); }
    $('filterReset').addEventListener('click', () => { filters = Object.assign({}, DEFAULTS); load(); });

    syncControls();
    // Cards animate in on the first paint only, not on every filter swap.
    window.addEventListener('load', () => document.body.classList.add('no-anim'));
})();
</script>

    @include('partials.alerts')
</body>
</html>
