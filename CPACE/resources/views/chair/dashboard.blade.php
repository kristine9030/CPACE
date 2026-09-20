<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Chair Dashboard - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.chart-kit')
    <style>
        /* ── Elevated cards — a real dark-tinted shadow (not just a hairline
           border) so every card on this dashboard reads as a raised surface
           and stands out from the page background, matching the faculty
           dashboard's card treatment. Scoped to this page like every other
           analytics-heavy dashboard in the app (Test Bank, Performance,
           Quiz Results all do the same) rather than the shared chair
           sidebar partial, so other Program Chair pages are unaffected. */
        .card, .stat-card, .viz-card, .roster-modal {
            box-shadow:0 2px 6px rgba(15,10,10,.08), 0 10px 22px -10px rgba(15,10,10,.22);
        }
        .viz-card { border-color:transparent; }
        /* .viz-sub is normally scoped to .viz-card (chart-kit.blade.php) — the
           Faculty Workload card reuses the same caption style but lives in a
           plain .card, so it needs its own copy of the same small, muted
           look instead of falling back to unstyled body text. */
        .card .viz-sub { font-size:10.5px; color:#999; margin-bottom:14px; line-height:1.5; }
        .card, .stat-card { transition:transform .18s ease, box-shadow .18s ease; }
        .card:hover, .stat-card:hover {
            transform:translateY(-2px);
            box-shadow:0 4px 10px rgba(15,10,10,.1), 0 16px 30px -10px rgba(15,10,10,.3);
        }

        /* KPI cards get an even darker, more pronounced shadow than the rest
           of the page's cards so they read as the headline row and visibly
           pop off the background. */
        .stats-row .stat-card {
            box-shadow:0 4px 10px rgba(10,5,5,.14), 0 16px 32px -8px rgba(10,5,5,.34);
        }
        .stats-row .stat-card:hover {
            box-shadow:0 6px 14px rgba(10,5,5,.18), 0 22px 40px -8px rgba(10,5,5,.4);
        }

        /* ── KPI cards — bolder, darker titles (was a faint 11px gray label)
           and an inline unit next to the number so a bare count like "6"
           reads as "6 Subjects" at a glance, matching the faculty
           dashboard's KPI card treatment. ── */
        .stats-row .stat-card { display:flex; flex-direction:column; height:100%; }
        .stats-row .stat-top { flex:1; }
        .stats-row .stat-icon { width:52px; height:52px; border-radius:13px; font-size:24px; flex-shrink:0; }
        .stats-row .stat-lbl { font-size:13.5px; font-weight:700; color:#1a1a1a; margin-bottom:6px; letter-spacing:-.01em; }
        .stat-unit { font-size:12px; font-weight:600; color:#aaa; vertical-align:middle; margin-left:2px; }
        .stat-context {
            font-size:10.5px; color:#999; margin-top:12px;
            padding-top:10px; border-top:1px dashed #eee; line-height:1.4;
        }
        .stat-context strong { color:#1a1a1a; font-weight:700; }

        /* ── KPI comparison badges ── */
        .stat-delta { display:inline-flex; align-items:center; gap:4px; font-size:10.5px; font-weight:700; margin-top:6px; }
        .stat-delta.up { color:#047857; }
        .stat-delta.down { color:#b91c1c; }
        .stat-delta.flat { color:#999; }
        .stat-delta-note { font-size:9.5px; color:#bbb; font-weight:500; margin-left:2px; }

        /* ── Faculty workload table ── */
        .workload-table { width:100%; border-collapse:collapse; }
        .workload-table th { text-align:left; color:#aaa; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.4px; padding:0 10px 10px; }
        .workload-table td { padding:11px 10px; border-top:1px solid #f5f5f5; font-size:13px; vertical-align:middle; }
        .workload-table td:not(:first-child), .workload-table th:not(:first-child) { text-align:center; }
        .workload-name { font-weight:600; color:#1a1a1a; }
        .workload-email { font-size:10.5px; color:#999; }
        .workload-metric { font-weight:700; color:#1b1b1b; }
        .workload-flag { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:20px; font-size:10px; font-weight:700; }
        .workload-flag.unassigned { background:#f3f4f6; color:#6b7280; }
        .workload-flag.overloaded { background:#fef3c7; color:#b45309; }
        .workload-flag.idle { background:#fde8e8; color:#b91c1c; }
        .workload-flag.ok { background:#d1fae5; color:#059669; }
        .workload-empty { padding:20px 10px 6px; text-align:center; color:#999; font-size:12px; }

        .health-grid { display:grid; grid-template-columns:minmax(0,1.6fr) minmax(0,1fr) minmax(0,1fr); gap:18px; margin-bottom:18px; }
        @media (max-width: 1200px) { .health-grid { grid-template-columns:1fr 1fr; } .health-grid > :first-child { grid-column:1 / -1; } }
        @media (max-width: 760px) { .health-grid { grid-template-columns:1fr; } }
        /* ── Dashboard-specific responsive ── */
        @media (max-width: 768px) {
            .dash-content-grid {
                grid-template-columns: 1fr !important;
            }
            .dash-table-wrap {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .risk-meta { display:none; }
        }
        .risk-summary { display:flex; align-items:center; gap:8px; }
        .risk-count { min-width:24px; height:24px; padding:0 7px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; background:#fde8e8; color:var(--accent); font-size:11px; font-weight:700; }
        .risk-list { display:flex; flex-direction:column; }
        .risk-row { display:grid; grid-template-columns:minmax(190px, 1.4fr) minmax(160px, 1fr) 100px 120px 78px; gap:14px; align-items:center; padding:13px 10px; border-top:1px solid #f5f5f5; }
        .risk-row:hover { background:#fafafa; }
        .risk-student { display:flex; align-items:center; gap:11px; min-width:0; }
        .risk-name { font-size:13px; font-weight:600; color:#1a1a1a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .risk-email, .risk-meta { font-size:10.5px; color:#aaa; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .risk-reasons { display:flex; flex-wrap:wrap; gap:5px; }
        .risk-reason { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:12px; background:#fef3c7; color:#b45309; font-size:10px; font-weight:600; }
        .risk-reason.low { background:#fde8e8; color:#b91c1c; }
        .risk-score { font-size:13px; font-weight:700; color:var(--accent); }
        .risk-score.na { color:#aaa; font-weight:500; }
        .risk-priority { display:inline-flex; align-items:center; justify-content:center; padding:4px 9px; border-radius:20px; font-size:10px; font-weight:700; }
        .risk-priority.high { background:#fde8e8; color:#b91c1c; }
        .risk-priority.watch { background:#fef3c7; color:#b45309; }
        .risk-head { color:#aaa; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.4px; padding-top:0; border-top:0; }
        .risk-empty { padding:24px 10px 8px; text-align:center; color:#999; font-size:12px; }
        .risk-empty i { color:var(--green); margin-right:6px; }

        /* ── Lightweight client-side pagination for the At-Risk list — all
           rows already render server-side, JS just shows/hides a page at a
           time so a long alert list doesn't stretch the whole dashboard. ── */
        .mini-pagination { display:flex; justify-content:space-between; align-items:center; padding:14px 10px 2px; border-top:1px solid #f5f5f5; margin-top:6px; }
        .mini-pag-info { font-size:11.5px; color:#999; }
        .mini-pag-btns { display:flex; gap:5px; }
        .mini-pag-btn { min-width:28px; height:28px; padding:0 7px; border:1px solid #e5e5e5; background:#fff; border-radius:7px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:12px; color:#555; transition:all .15s; font-family:'Poppins',sans-serif; }
        .mini-pag-btn.active { background:var(--primary); color:#fff; border-color:var(--primary); }
        .mini-pag-btn:hover:not(.active):not(:disabled) { background:#f5f5f5; }
        .mini-pag-btn:disabled { opacity:.4; cursor:not-allowed; }
        @media (max-width:620px) { .mini-pagination { flex-direction:column; gap:8px; align-items:flex-start; } }
        .action-list { display:flex; flex-direction:column; gap:10px; }
        .action-row { display:flex; gap:12px; padding:12px 10px; border-top:1px solid #f5f5f5; }
        .action-row:first-child { border-top:0; }
        .action-icon { flex:none; width:30px; height:30px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:12px; }
        .action-icon.high { background:#fde8e8; color:#b91c1c; }
        .action-icon.watch { background:#fef3c7; color:#b45309; }
        .action-body { min-width:0; }
        .action-subject { font-size:12.5px; font-weight:600; color:#1a1a1a; }
        .action-detail { font-size:11px; color:#888; margin-top:2px; }
        .action-next { font-size:11px; color:var(--accent); font-weight:600; margin-top:4px; }
        .action-empty { padding:24px 10px 8px; text-align:center; color:#999; font-size:12px; }
        .action-empty i { color:var(--green); margin-right:6px; }
        .analytics-strip { margin:18px 0; }
        .analytics-head { display:flex; align-items:end; justify-content:space-between; gap:12px; margin-bottom:10px; }
        .analytics-title { font-size:14px; font-weight:700; color:#222; }
        .analytics-sub { font-size:10.5px; color:#999; margin-top:2px; }
        .analytics-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
        .analytics-card {
            display:block; background:#fff; border:1px solid transparent; border-radius:12px; padding:15px; text-decoration:none; transition:.18s ease;
            box-shadow:0 2px 6px rgba(15,10,10,.08), 0 10px 22px -10px rgba(15,10,10,.22);
        }
        .analytics-card:hover { transform:translateY(-2px); box-shadow:0 4px 10px rgba(15,10,10,.1), 0 16px 30px -10px rgba(15,10,10,.3); }
        .analytics-card-top { display:flex; align-items:center; justify-content:space-between; gap:8px; }
        /* Same type scale as the KPI row above (.stat-lbl / .stat-num) so the
           two card families read as one consistent system, not two. */
        .analytics-card-label { color:#777; font-size:11px; font-weight:600; }
        .analytics-card-icon { width:30px; height:30px; border-radius:9px; display:grid; place-items:center; background:#fef2f2; color:var(--accent); }
        .analytics-card-value { margin-top:10px; font-size:28px; line-height:1; font-weight:700; color:#1b1b1b; }
        .analytics-card-note { margin-top:7px; font-size:10.5px; color:#aaa; min-height:14px; }
        .trend-up { color:#047857; } .trend-down { color:#b91c1c; }
        .section-table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
        .section-table { width:100%; border-collapse:collapse; min-width:560px; }
        .section-table th { text-align:left; color:#aaa; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.4px; padding:0 10px 10px; }
        .section-table td { padding:12px 10px; border-top:1px solid #f5f5f5; font-size:13px; }
        .section-table td:not(:first-child), .section-table th:not(:first-child) { text-align:center; }
        .section-name { font-weight:600; color:#1a1a1a; }
        .section-name .muted { display:block; font-size:10.5px; font-weight:500; color:#999; }
        .section-metric { font-weight:700; color:#1b1b1b; }
        .section-metric.na { color:#ccc; font-weight:500; }
        .section-empty { padding:20px 10px 6px; text-align:center; color:#999; font-size:12px; }
        .year-tag { display:inline-flex; align-items:center; padding:2px 9px; border-radius:20px; font-size:10px; font-weight:700; background:#eef2ff; color:#4338ca; }
        .year-tag.na { background:#f3f4f6; color:#9ca3af; }
        .breakdown-tabs { display:flex; gap:6px; margin-bottom:14px; }
        .breakdown-tab { padding:6px 14px; border-radius:20px; border:1px solid #eee; background:#fff; color:#777; font-size:11.5px; font-weight:600; cursor:pointer; }
        .breakdown-tab.active { background:var(--primary); color:#fff; border-color:var(--primary); }
        .breakdown-pane[hidden] { display:none; }
        .eligible-link { border:none; background:none; padding:0; font:inherit; font-weight:700; color:var(--accent); cursor:pointer; text-decoration:underline; text-underline-offset:2px; }
        .eligible-link:hover { color:var(--primary); }
        .roster-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:2000; align-items:center; justify-content:center; padding:20px; }
        .roster-modal-overlay.open { display:flex; }
        .roster-modal { background:#fff; border-radius:16px; width:100%; max-width:520px; padding:24px; max-height:80vh; overflow-y:auto; }
        .roster-modal h3 { font-size:16px; color:#1a1a1a; margin-bottom:4px; }
        .roster-modal-sub { font-size:11px; color:#999; margin-bottom:16px; }
        .roster-row { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 0; border-top:1px solid #f5f5f5; }
        .roster-row:first-of-type { border-top:none; }
        .roster-name { font-size:13px; font-weight:600; color:#1a1a1a; }
        .roster-email { font-size:10.5px; color:#999; }
        .roster-meta { text-align:right; font-size:11px; color:#666; white-space:nowrap; }
        .roster-band { display:inline-block; margin-top:3px; padding:2px 8px; border-radius:20px; font-size:9.5px; font-weight:700; }
        .roster-band.ready { background:#d1fae5; color:#059669; }
        .roster-band.developing { background:#fef3c7; color:#b45309; }
        .roster-band.at_risk { background:#fde8e8; color:#b91c1c; }
        .roster-empty, .roster-loading { padding:20px 4px; text-align:center; color:#999; font-size:12px; }
        .roster-modal-close { display:flex; justify-content:flex-end; margin-top:16px; }
        @media (max-width: 980px) {
            .risk-row { grid-template-columns:minmax(180px, 1.3fr) minmax(145px, 1fr) 85px 70px; }
            .risk-last { display:none; }
            .analytics-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
        }
        @media (max-width: 620px) {
            .risk-row { grid-template-columns:1fr auto; gap:9px; }
            .risk-head { display:none; }
            .risk-reasons { grid-column:1 / -1; padding-left:49px; }
            .risk-score, .risk-last { display:none; }
            .analytics-grid { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'dashboard'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Program Chair Dashboard</div>
                <div class="page-sub">Welcome back, {{ Auth::user()->name }}. Manage faculty and subject assignments here.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('chair.faculty.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Faculty</a>
            @include('partials.topbar-actions')
        </div>
    </div>

    {{-- Flash messages surface as SweetAlert popups via partials.alerts --}}

    @php
        // A stat where "up" is good (faculty, subjects, coverage) vs where
        // "up" is bad (unassigned) need opposite coloring for the same sign.
        $deltaClass = fn ($delta, $goodWhenUp = true) => $delta === 0 ? 'flat' : (($delta > 0) === $goodWhenUp ? 'up' : 'down');
        $deltaText = fn ($delta) => $delta === 0 ? 'No change' : (($delta > 0 ? '+' : '') . $delta);
        $coveragePct = $stats['subjects'] > 0 ? (int) round($stats['assigned'] / $stats['subjects'] * 100) : 0;
    @endphp
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Faculty Members</div>
                    <div class="stat-num">{{ $stats['faculty'] }} <span class="stat-unit">Faculty</span></div>
                    <div class="stat-delta {{ $deltaClass($kpiDeltas['faculty']) }}">
                        <i class="fas {{ $kpiDeltas['faculty'] > 0 ? 'fa-arrow-up' : ($kpiDeltas['faculty'] < 0 ? 'fa-arrow-down' : 'fa-minus') }}"></i>
                        {{ $deltaText($kpiDeltas['faculty']) }}<span class="stat-delta-note">vs 30 days ago</span>
                    </div>
                </div>
                <div class="stat-icon si-red"><i class="fas fa-chalkboard-user"></i></div>
            </div>
            <div class="stat-context">Currently supporting <strong>{{ $stats['subjects'] }}</strong> CPALE subject{{ $stats['subjects'] === 1 ? '' : 's' }}.</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">CPALE Subjects</div>
                    <div class="stat-num">{{ $stats['subjects'] }} <span class="stat-unit">Subjects</span></div>
                </div>
                <div class="stat-icon si-blue"><i class="fas fa-book-open"></i></div>
            </div>
            <div class="stat-context">The full CPALE board exam curriculum tracked in CPACE.</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Subjects Covered</div>
                    <div class="stat-num">{{ $stats['assigned'] }} <span class="stat-unit">Covered</span></div>
                    <div class="stat-delta {{ $deltaClass($kpiDeltas['assigned']) }}">
                        <i class="fas {{ $kpiDeltas['assigned'] > 0 ? 'fa-arrow-up' : ($kpiDeltas['assigned'] < 0 ? 'fa-arrow-down' : 'fa-minus') }}"></i>
                        {{ $deltaText($kpiDeltas['assigned']) }}<span class="stat-delta-note">vs 30 days ago</span>
                    </div>
                </div>
                <div class="stat-icon si-green"><i class="fas fa-circle-check"></i></div>
            </div>
            <div class="stat-context"><strong>{{ $coveragePct }}%</strong> of subjects have at least one faculty assigned.</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Unassigned</div>
                    <div class="stat-num">{{ $stats['unassigned'] }} <span class="stat-unit">Unassigned</span></div>
                </div>
                <div class="stat-icon si-orange"><i class="fas fa-triangle-exclamation"></i></div>
            </div>
            <div class="stat-context">
                @if($stats['unassigned'] > 0)
                    <strong style="color:var(--accent);">{{ $stats['unassigned'] }}</strong> subject{{ $stats['unassigned'] === 1 ? '' : 's' }} still need{{ $stats['unassigned'] === 1 ? 's' : '' }} a faculty.
                @else
                    <strong style="color:#059669;">All subjects</strong> have faculty coverage.
                @endif
            </div>
        </div>
    </div>

    <section class="analytics-strip" aria-labelledby="analytics-title">
        <div class="analytics-head">
            <div><div class="analytics-title" id="analytics-title">System Analytics Snapshot</div><div class="analytics-sub">Live class-level and test-bank indicators</div></div>
            <a href="{{ route('chair.analytics.performance') }}" class="card-link">Open Analytics</a>
        </div>
        <div class="analytics-grid">
            <a class="analytics-card" href="{{ route('chair.analytics.performance') }}">
                <div class="analytics-card-top"><span class="analytics-card-label">Class-Level Accuracy</span><span class="analytics-card-icon"><i class="fas fa-bullseye"></i></span></div>
                <div class="analytics-card-value">{{ $analytics['class_accuracy'] === null ? '—' : $analytics['class_accuracy'].'%' }}</div>
                <div class="analytics-card-note">{{ $analytics['weakest_subject'] ? 'Weakest: '.$analytics['weakest_subject']['code'].' at '.$analytics['weakest_subject']['accuracy'].'%' : 'Waiting for student attempts' }}</div>
            </a>
            <a class="analytics-card" href="{{ route('chair.analytics.performance') }}#readiness-trend">
                <div class="analytics-card-top"><span class="analytics-card-label">Board Readiness</span><span class="analytics-card-icon"><i class="fas fa-chart-line"></i></span></div>
                <div class="analytics-card-value">{{ $analytics['readiness_rate'] === null ? '—' : $analytics['readiness_rate'].'%' }}</div>
                <div class="analytics-card-note {{ ($analytics['readiness_change'] ?? 0) >= 0 ? 'trend-up' : 'trend-down' }}">{{ $analytics['readiness_change'] === null ? 'Trend begins after two periods' : (($analytics['readiness_change'] >= 0 ? '+' : '').$analytics['readiness_change'].' pts this week') }}</div>
            </a>
            <a class="analytics-card" href="{{ route('chair.analytics.test-bank-coverage') }}">
                <div class="analytics-card-top"><span class="analytics-card-label">Thin Test-Bank Areas</span><span class="analytics-card-icon"><i class="fas fa-layer-group"></i></span></div>
                <div class="analytics-card-value">{{ $analytics['thin_topics'] }}</div>
                <div class="analytics-card-note">Topics below 25 active questions</div>
            </a>
            <a class="analytics-card" href="{{ route('chair.analytics.performance') }}#pass-projection">
                <div class="analytics-card-top"><span class="analytics-card-label">Pass Projection</span><span class="analytics-card-icon"><i class="fas fa-graduation-cap"></i></span></div>
                <div class="analytics-card-value">{{ $analytics['pass_projection'] === null ? '—' : $analytics['pass_projection'].'%' }}</div>
                <div class="analytics-card-note">Readiness-based · {{ $analytics['eligible_students'] }} measured students</div>
            </a>
        </div>
    </section>

    <div class="card" style="margin-bottom:18px;">
        <div class="card-head">
            <span class="card-title"><i class="fas fa-layer-group" style="color:var(--accent);margin-right:7px;"></i>Class-Level Performance by Cohort</span>
            <a href="{{ route('chair.analytics.performance') }}" class="card-link">Full Analytics</a>
        </div>

        <div class="breakdown-tabs" role="tablist">
            <button type="button" class="breakdown-tab active" data-pane="byYear">By Year Level</button>
            <button type="button" class="breakdown-tab" data-pane="bySection">By Section</button>
        </div>

        <div class="breakdown-pane" id="byYear">
            @if(($analytics['by_year'] ?? collect())->isNotEmpty())
                <div class="section-table-wrap">
                <table class="section-table">
                    <thead>
                        <tr><th>Year Level</th><th>Class Accuracy</th><th>Board Readiness</th><th>Pass Projection</th><th>Measured Students</th></tr>
                    </thead>
                    <tbody>
                    @foreach($analytics['by_year'] as $row)
                        <tr>
                            <td class="section-name">
                                {{ $row['year_label'] }}
                                <span class="muted">{{ $row['sections'] }} section{{ $row['sections'] === 1 ? '' : 's' }} · {{ $row['participating_students'] }} participating</span>
                            </td>
                            <td><span class="section-metric {{ $row['class_accuracy'] === null ? 'na' : '' }}">{{ $row['class_accuracy'] === null ? '—' : $row['class_accuracy'].'%' }}</span></td>
                            <td><span class="section-metric {{ $row['readiness_rate'] === null ? 'na' : '' }}">{{ $row['readiness_rate'] === null ? '—' : $row['readiness_rate'].'%' }}</span></td>
                            <td><span class="section-metric {{ $row['pass_projection'] === null ? 'na' : '' }}">{{ $row['pass_projection'] === null ? '—' : $row['pass_projection'].'%' }}</span></td>
                            <td>
                                @if($row['eligible_students'] > 0)
                                    <button type="button" class="eligible-link" onclick='openEligible({year: {{ $row['year_level'] }}, label: @json($row['year_label'])})'>{{ $row['eligible_students'] }}</button>
                                @else
                                    {{ $row['eligible_students'] }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
            @else
                <div class="section-empty"><i class="fas fa-circle-info"></i> No section has a year level set yet — set one under <a href="{{ route('chair.sections') }}">Program Chair &rarr; Sections</a> to see this breakdown.</div>
            @endif
        </div>

        <div class="breakdown-pane" id="bySection" hidden>
            @if(($analytics['by_section'] ?? collect())->isNotEmpty())
                <div class="section-table-wrap">
                <table class="section-table">
                    <thead>
                        <tr><th>Section</th><th>Year</th><th>Class Accuracy</th><th>Board Readiness</th><th>Pass Projection</th><th>Measured Students</th></tr>
                    </thead>
                    <tbody>
                    @foreach($analytics['by_section'] as $row)
                        <tr>
                            <td class="section-name">
                                <a href="{{ route('chair.analytics.performance', ['section' => $row['section']]) }}" style="color:inherit;text-decoration:none;">{{ $row['section'] }}</a>
                                <span class="muted">{{ $row['participating_students'] }} participating</span>
                            </td>
                            <td>
                                @if($row['year_label'])
                                    <span class="year-tag">{{ $row['year_label'] }}</span>
                                @else
                                    <span class="year-tag na">Not set</span>
                                @endif
                            </td>
                            <td><span class="section-metric {{ $row['class_accuracy'] === null ? 'na' : '' }}">{{ $row['class_accuracy'] === null ? '—' : $row['class_accuracy'].'%' }}</span></td>
                            <td><span class="section-metric {{ $row['readiness_rate'] === null ? 'na' : '' }}">{{ $row['readiness_rate'] === null ? '—' : $row['readiness_rate'].'%' }}</span></td>
                            <td><span class="section-metric {{ $row['pass_projection'] === null ? 'na' : '' }}">{{ $row['pass_projection'] === null ? '—' : $row['pass_projection'].'%' }}</span></td>
                            <td>
                                @if($row['eligible_students'] > 0)
                                    <button type="button" class="eligible-link" onclick='openEligible({section: @json($row['section']), label: @json($row['section'])})'>{{ $row['eligible_students'] }}</button>
                                @else
                                    {{ $row['eligible_students'] }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
            @else
                <div class="section-empty"><i class="fas fa-circle-info"></i> No active sections yet — add one under Program Chair &rarr; Sections to see per-section breakdowns here.</div>
            @endif
        </div>
    </div>

    <div class="roster-modal-overlay" id="rosterModal">
        <div class="roster-modal">
            <h3 id="rosterModalTitle">Measured Students</h3>
            <div class="roster-modal-sub" id="rosterModalSub">Attempted enough items to be measured for board readiness — includes Ready, Developing, and At-risk students.</div>
            <div id="rosterModalBody"><div class="roster-loading"><i class="fas fa-spinner fa-spin"></i> Loading students…</div></div>
            <div class="roster-modal-close"><button type="button" class="btn btn-ghost btn-sm" onclick="closeRoster()">Close</button></div>
        </div>
    </div>

    <section class="health-grid" aria-labelledby="health-title">
        <div class="viz-card">
            <h4 id="health-title"><i class="fas fa-chart-line"></i> Readiness &amp; Accuracy Trend</h4>
            <div class="viz-sub">Cumulative to the end of each week — both series are percentages. <a href="{{ route('chair.analytics.performance') }}" style="color:var(--accent);text-decoration:none;">Full report</a></div>
            <div class="chart-canvas-wrap h-sm"><canvas id="dashTrend"></canvas></div>
        </div>
        <div class="viz-card">
            <h4><i class="fas fa-users"></i> Students Practising</h4>
            <div class="viz-sub">Distinct students completing a quiz each week.</div>
            <div class="chart-canvas-wrap h-sm"><canvas id="dashEngagement"></canvas></div>
        </div>
        <div class="viz-card">
            <h4><i class="fas fa-chart-pie"></i> Readiness Bands</h4>
            <div class="viz-sub">{{ $analytics['readiness']['eligible'] }} measured · {{ $analytics['readiness']['insufficient'] }} not yet measurable.</div>
            <div class="chart-canvas-wrap h-sm"><canvas id="dashBands"></canvas></div>
        </div>
    </section>

    <div class="card" style="margin-bottom:18px;">
        <div class="card-head">
            <span class="card-title"><i class="fas fa-scale-balanced" style="color:var(--accent);margin-right:7px;"></i>Faculty Workload &amp; Activity</span>
            <a href="{{ route('chair.faculty') }}" class="card-link">Manage Faculty</a>
        </div>
        <div class="viz-sub" style="margin-bottom:6px;">Subject/section load and account activity per faculty — the staffing signals a chair acts on directly.</div>
        @if($facultyWorkload->isNotEmpty())
            <div class="dash-table-wrap">
            <table class="workload-table">
                <thead>
                    <tr><th>Faculty</th><th>Subjects</th><th>Sections</th><th>Questions Added (30d)</th><th>Last Login</th><th>Status</th></tr>
                </thead>
                <tbody>
                @foreach($facultyWorkload as $f)
                    <tr>
                        <td>
                            <div class="workload-name">{{ $f['name'] }}</div>
                            <div class="workload-email">{{ $f['email'] }}</div>
                        </td>
                        <td><span class="workload-metric">{{ $f['subjects'] }}</span></td>
                        <td><span class="workload-metric">{{ $f['sections'] }}</span></td>
                        <td><span class="workload-metric">{{ $f['questions_added_30d'] }}</span></td>
                        <td style="font-size:12px;color:#666;">{{ $f['last_login_at'] ? $f['last_login_at']->diffForHumans() : 'Never' }}</td>
                        <td>
                            @switch($f['flag'])
                                @case('unassigned')
                                    <span class="workload-flag unassigned"><i class="fas fa-circle-minus"></i> Unassigned</span>
                                    @break
                                @case('overloaded')
                                    <span class="workload-flag overloaded"><i class="fas fa-layer-group"></i> Overloaded</span>
                                    @break
                                @case('idle')
                                    <span class="workload-flag idle"><i class="fas fa-clock"></i> Idle 30+ days</span>
                                    @break
                                @default
                                    <span class="workload-flag ok"><i class="fas fa-check"></i> On track</span>
                            @endswitch
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
        @else
            <div class="workload-empty"><i class="fas fa-user-slash"></i> No faculty accounts yet.</div>
        @endif
    </div>

    <div class="dash-content-grid" style="display:grid; grid-template-columns:1fr 340px; gap:18px;">
        <div class="card">
            <div class="card-head">
                <span class="card-title">Subject Coverage</span>
                <a href="{{ route('chair.subjects') }}" class="card-link">Manage Assignments</a>
            </div>
            <div class="dash-table-wrap">
            <table>
                <thead><tr><th>Subject</th><th>Faculty Assigned</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($subjects as $s)
                    <tr>
                        <td>
                            <span class="subj-badge b-{{ strtolower($s->code) }}">{{ $s->code }}</span>
                            <span style="color:#555;">{{ $s->name }}</span>
                        </td>
                        <td>{{ $s->faculty_count }}</td>
                        <td>
                            @if ($s->faculty_count > 0)
                                <span class="pill pill-on"><i class="fas fa-check"></i> Covered</span>
                            @else
                                <span class="pill pill-off"><i class="fas fa-minus"></i> No faculty</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>{{-- /.dash-table-wrap --}}
        </div>

        <div class="card">
            <div class="card-head">
                <span class="card-title">Recent Faculty</span>
                <a href="{{ route('chair.faculty') }}" class="card-link">View All</a>
            </div>
            @forelse ($faculty as $f)
                <div style="display:flex; align-items:center; gap:12px; padding:11px 0; border-bottom:1px solid #f5f5f5;">
                    <div class="user-av" style="background:var(--primary);">{{ strtoupper(substr($f->first_name,0,1)).strtoupper(substr($f->last_name,0,1)) }}</div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:13px; font-weight:600; color:#1a1a1a;">{{ $f->name }}</div>
                        <div style="font-size:11px; color:#999;">
                            {{ $f->assignedSubjects->count() ? $f->assignedSubjects->pluck('code')->join(', ') : 'No subjects yet' }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty"><i class="fas fa-user-slash"></i><div>No faculty accounts yet.</div></div>
            @endforelse
            <a href="{{ route('chair.faculty.create') }}" class="btn btn-outline btn-sm" style="margin-top:14px; width:100%; justify-content:center;"><i class="fas fa-user-plus"></i> Create Faculty Account</a>
        </div>
    </div>

    <div class="card" style="margin-top:18px;">
        <div class="card-head">
            <div class="risk-summary">
                <span class="card-title"><i class="fas fa-triangle-exclamation" style="color:var(--accent);margin-right:7px;"></i>At-Risk Student Alerts</span>
                <span class="risk-count">{{ $atRiskStudents->count() }}</span>
            </div>
            <span class="risk-meta">Low readiness (&lt;60% after 5+ items) or inactive for 7+ days</span>
        </div>

        @if($atRiskStudents->isNotEmpty())
            @php $riskPageSize = 5; $riskPages = (int) ceil($atRiskStudents->count() / $riskPageSize); @endphp
            <div class="risk-list" id="riskList" data-page-size="{{ $riskPageSize }}">
                <div class="risk-row risk-head" aria-hidden="true">
                    <span>Student</span><span>Alert reason</span><span>Readiness</span><span class="risk-last">Last active</span><span>Priority</span>
                </div>
                @foreach($atRiskStudents as $student)
                    <div class="risk-row" data-page="{{ intdiv($loop->index, $riskPageSize) + 1 }}">
                        <div class="risk-student">
                            <div class="user-av" style="background:var(--primary);">{{ $student['initials'] }}</div>
                            <div style="min-width:0;">
                                <div class="risk-name">{{ $student['name'] }}</div>
                                <div class="risk-email">{{ $student['email'] }}</div>
                            </div>
                        </div>
                        <div class="risk-reasons">
                            @foreach($student['reasons'] as $reason)
                                <span class="risk-reason {{ $reason === 'Low readiness' ? 'low' : '' }}">
                                    <i class="fas {{ $reason === 'Low readiness' ? 'fa-chart-line' : 'fa-clock' }}"></i>{{ $reason }}
                                </span>
                            @endforeach
                        </div>
                        <div class="risk-score {{ $student['score'] === null ? 'na' : '' }}">
                            {{ $student['score'] === null ? 'Not rated' : $student['score'].'%' }}
                            <div class="risk-meta">{{ $student['attempted'] }} items</div>
                        </div>
                        <div class="risk-last">
                            <div style="font-size:12px;color:#555;">{{ $student['last_active'] ? $student['last_active']->diffForHumans() : 'Never' }}</div>
                            <div class="risk-meta">{{ $student['quizzes'] }} completed quiz{{ $student['quizzes'] === 1 ? '' : 'zes' }}</div>
                        </div>
                        <span class="risk-priority {{ $student['priority'] }}">{{ $student['priority'] === 'high' ? 'High' : 'Watch' }}</span>
                    </div>
                @endforeach
            </div>
            @if($riskPages > 1)
                <div class="mini-pagination" id="riskPagination" data-pages="{{ $riskPages }}">
                    <span class="mini-pag-info" id="riskPagInfo"></span>
                    <div class="mini-pag-btns" id="riskPagBtns"></div>
                </div>
            @endif
        @else
            <div class="risk-empty"><i class="fas fa-circle-check"></i>No students currently need intervention.</div>
        @endif
    </div>

    <div class="card" style="margin-top:18px;">
        <div class="card-head">
            <span class="card-title"><i class="fas fa-list-check" style="color:var(--accent);margin-right:7px;"></i>Recommended Actions</span>
            <span class="risk-meta">Rule-based, drawn from the alerts and weak topics above — not a prediction</span>
        </div>

        @if($recommendedActions->isNotEmpty())
            <div class="action-list">
                @foreach($recommendedActions as $action)
                    <div class="action-row">
                        <div class="action-icon {{ $action['severity'] }}">
                            <i class="fas {{ $action['type'] === 'student' ? 'fa-user' : 'fa-book' }}"></i>
                        </div>
                        <div class="action-body">
                            <div class="action-subject">{{ $action['subject'] }}</div>
                            <div class="action-detail">{{ $action['detail'] }}</div>
                            <div class="action-next"><i class="fas fa-arrow-right" style="font-size:9px;margin-right:4px;"></i>{{ $action['action'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="action-empty"><i class="fas fa-circle-check"></i>Nothing needs action right now.</div>
        @endif
    </div>
</main>

<script>
(function () {
    const P = Viz.palette;
    const trend = @json($analytics['trend']->values());
    const engagement = @json($analytics['engagement']->values());
    const readiness = @json($analytics['readiness']);
    const pluck = (rows, key) => rows.map((row) => row[key]);

    Viz.chart('dashTrend', {
        type: 'line',
        data: {
            labels: pluck(trend, 'label'),
            datasets: [
                Viz.line({ label: 'Board readiness', data: pluck(trend, 'rate'), borderColor: P.s1, backgroundColor: 'rgba(163,43,43,.08)', pointBackgroundColor: P.s1, fill: true, pointRadius: 3 }),
                Viz.line({ label: 'Class accuracy', data: pluck(trend, 'accuracy'), borderColor: P.s2, backgroundColor: 'transparent', pointBackgroundColor: P.s2, pointRadius: 3 }),
            ],
        },
        options: {
            interaction: { mode: 'index', intersect: false },
            scales: { y: Viz.percentAxis(), x: Viz.catAxis() },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { afterBody: (items) => {
                    const point = trend[items[0].dataIndex];
                    return point.ready + ' of ' + point.eligible + ' measured students ready';
                } } },
            },
        },
    });

    Viz.chart('dashEngagement', {
        type: 'bar',
        data: {
            labels: pluck(engagement, 'label'),
            datasets: [Viz.bar({ label: 'Active students', data: pluck(engagement, 'active_students'), backgroundColor: P.s1 })],
        },
        options: {
            scales: { y: Viz.countAxis(), x: Viz.catAxis() },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => {
                    const week = engagement[c.dataIndex];
                    return week.active_students + ' student' + (week.active_students === 1 ? '' : 's') + ' · ' + week.quizzes + ' quizzes · ' + week.items.toLocaleString() + ' items';
                } } },
            },
        },
    });

    Viz.chart('dashBands', {
        type: 'doughnut',
        data: {
            labels: ['Ready', 'Developing', 'At risk'],
            datasets: [{
                data: [readiness.ready, readiness.developing, readiness.at_risk],
                backgroundColor: [P.good, P.warn, P.crit],
                borderWidth: 2, borderColor: P.surface,
            }],
        },
        options: {
            cutout: '58%',
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: (c) => {
                    const total = readiness.eligible || 1;
                    return c.label + ': ' + c.raw + ' (' + Math.round(c.raw / total * 100) + '%)';
                } } },
            },
        },
    });

    document.querySelectorAll('.breakdown-tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.breakdown-tab').forEach((t) => t.classList.remove('active'));
            document.querySelectorAll('.breakdown-pane').forEach((p) => p.hidden = true);
            tab.classList.add('active');
            document.getElementById(tab.dataset.pane).hidden = false;
        });
    });
})();

const BAND_LABELS = { ready: 'Ready', developing: 'Developing', at_risk: 'At risk' };
const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

function openEligible({ section = null, year = null, label = '' }) {
    const overlay = document.getElementById('rosterModal');
    const body = document.getElementById('rosterModalBody');
    document.getElementById('rosterModalTitle').textContent = `Measured Students — ${label}`;
    body.innerHTML = '<div class="roster-loading"><i class="fas fa-spinner fa-spin"></i> Loading students…</div>';
    overlay.classList.add('open');

    const params = new URLSearchParams();
    if (section) params.set('section', section);
    if (year) params.set('year', year);

    fetch(`{{ route('chair.analytics.eligible-students') }}?${params.toString()}`, {
        headers: { 'Accept': 'application/json' },
    })
        .then((res) => res.json())
        .then((data) => {
            const students = data.students || [];
            if (!students.length) {
                body.innerHTML = '<div class="roster-empty"><i class="fas fa-circle-info"></i> No measured students found.</div>';
                return;
            }
            body.innerHTML = students.map((s) => `
                <div class="roster-row">
                    <div>
                        <div class="roster-name">${escapeHtml(s.name)}</div>
                        <div class="roster-email">${escapeHtml(s.email)}${s.section ? ' · ' + escapeHtml(s.section) : ''}</div>
                    </div>
                    <div class="roster-meta">
                        ${s.accuracy}% · ${s.attempts} items
                        <div class="roster-band ${escapeHtml(s.band)}">${escapeHtml(BAND_LABELS[s.band] || s.band)}</div>
                    </div>
                </div>
            `).join('');
        })
        .catch(() => {
            body.innerHTML = '<div class="roster-empty"><i class="fas fa-triangle-exclamation"></i> Could not load students. Try again.</div>';
        });
}

function closeRoster() { document.getElementById('rosterModal').classList.remove('open'); }
document.getElementById('rosterModal').addEventListener('click', (event) => { if (event.target.id === 'rosterModal') closeRoster(); });
document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeRoster(); });

/* ── At-Risk list pagination — every row is already in the DOM; this just
   shows one page's worth at a time so the card doesn't run on forever. ── */
(function () {
    const list = document.getElementById('riskList');
    const pagination = document.getElementById('riskPagination');
    if (!list || !pagination) return;

    const rows = Array.from(list.querySelectorAll('.risk-row:not(.risk-head)'));
    const pageSize = parseInt(list.dataset.pageSize, 10) || 5;
    const totalPages = parseInt(pagination.dataset.pages, 10) || Math.ceil(rows.length / pageSize);
    const infoEl = document.getElementById('riskPagInfo');
    const btnsEl = document.getElementById('riskPagBtns');
    let currentPage = 1;

    function render() {
        rows.forEach((row) => {
            const rowPage = parseInt(row.dataset.page, 10) || 1;
            row.style.display = rowPage === currentPage ? '' : 'none';
        });

        const from = (currentPage - 1) * pageSize + 1;
        const to = Math.min(currentPage * pageSize, rows.length);
        infoEl.textContent = `Showing ${from}–${to} of ${rows.length} students`;

        let html = `<button type="button" class="mini-pag-btn" data-go="prev" ${currentPage <= 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
        for (let p = 1; p <= totalPages; p++) {
            html += `<button type="button" class="mini-pag-btn ${p === currentPage ? 'active' : ''}" data-go="${p}">${p}</button>`;
        }
        html += `<button type="button" class="mini-pag-btn" data-go="next" ${currentPage >= totalPages ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
        btnsEl.innerHTML = html;
    }

    btnsEl.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-go]');
        if (!btn || btn.disabled) return;
        const go = btn.dataset.go;
        if (go === 'prev') currentPage = Math.max(1, currentPage - 1);
        else if (go === 'next') currentPage = Math.min(totalPages, currentPage + 1);
        else currentPage = parseInt(go, 10);
        render();
    });

    render();
})();
</script>

    @include('partials.alerts')
</body>
</html>
