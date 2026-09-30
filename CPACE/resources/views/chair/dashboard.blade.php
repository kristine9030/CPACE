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
    @include('partials.chart-filter-styles')
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
        /* Layout: title + icon on top, a large value in the middle, and the
           comparison + context pinned to the bottom so every card lines up. */
        .stats-row .stat-card { display:flex; flex-direction:column; height:100%; padding:16px 18px 14px; }
        .stats-row .stat-top { display:flex; align-items:center; justify-content:space-between; gap:10px; }
        .stats-row .stat-icon { width:34px; height:34px; border-radius:10px; font-size:15px; flex-shrink:0; }
        .stats-row .stat-lbl { font-size:12.5px; font-weight:600; color:#4b5563; margin:0; letter-spacing:0; }
        .stats-row .stat-num {
            display:flex; align-items:baseline; gap:6px;
            margin:6px 0 10px;
            font-size:34px; font-weight:700; line-height:1; letter-spacing:-1px;
            color:#1a1a1a; font-variant-numeric:tabular-nums;
        }
        .stats-row .stat-unit { font-size:13px; font-weight:500; color:#9ca3af; letter-spacing:0; margin:0; }
        .stats-row .stat-bottom { margin-top:auto; }
        /* Plain text flow (not flex) so a long comparison wraps as one sentence. */
        .stats-row .stat-bottom .stat-delta { display:block; margin-top:0; margin-bottom:6px; line-height:1.4; font-size:11px; }
        .stats-row .stat-bottom .stat-delta i { margin-right:3px; }
        .stat-unit { font-size:12px; font-weight:500; color:#aaa; vertical-align:middle; margin-left:2px; }
        .stat-context {
            font-size:11px; color:#8a8f98; margin-top:0;
            padding-top:8px; border-top:1px solid #f1f1f3; line-height:1.4;
        }
        .stat-context strong { color:#374151; font-weight:600; }
        a.stat-link { display:flex; text-decoration:none; color:inherit; cursor:pointer; }
        a.stat-link:focus-visible { outline:2px solid var(--primary); outline-offset:2px; }
        /* Pinned to the right edge so long context text never pushes it onto its own line. */
        .stat-context { position:relative; padding-right:18px; }
        .stat-go { position:absolute; right:0; top:10px; font-size:10px; color:#ccc; transition:color .15s, transform .15s; }
        a.stat-link:hover .stat-go { color:var(--primary); transform:translateX(2px); }
        .stat-num.is-alert { color:#b91c1c; }
        .stat-num.is-warn { color:#b45309; }

        /* ── KPI comparison badges ── */
        .stat-delta { display:inline-flex; align-items:center; gap:5px; font-size:11.5px; font-weight:600; margin-top:6px; }
        .stat-delta.up { color:#047857; }
        .stat-delta.down { color:#b91c1c; }
        .stat-delta.flat { color:#999; }
        .stat-delta-note { font-size:11px; color:#9ca3af; font-weight:400; margin-left:2px; }

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
        .dash-loading .chart-grid, .dash-loading #riskList { opacity:.55; pointer-events:none; transition:opacity .2s; }
        .filter-note { font-size:11px; color:#92400e; background:#fffbeb; border-radius:8px; padding:7px 10px; margin-bottom:8px; }
        .filter-note[hidden] { display:none; }
        .scope-tag { display:inline-block; margin-left:8px; padding:2px 8px; border-radius:20px; background:#f3f4f6; color:#6b7280; font-size:10px; font-weight:600; vertical-align:middle; }
        .mini-pag-gap { align-self:center; color:#bbb; font-size:12px; padding:0 2px; }
        .mini-pagination[hidden] { display:none; }
        .workload-table tr[hidden] { display:none; }
        .report-link { display:flex; align-items:center; gap:12px; margin:-2px 0 18px; padding:13px 16px; border-radius:12px; background:#fff; border:1px dashed #e3c4c4; color:#555; font-size:12px; text-decoration:none; transition:background .15s, border-color .15s; }
        .report-link:hover { background:#fef2f2; border-color:var(--primary); }
        .report-link > i:first-child { color:var(--primary); }
        .report-link > i:last-child { margin-left:auto; color:var(--primary); }
        .report-link strong { color:#1a1a1a; }
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
        /* ── Top-level dashboard tabs ──────────────────────────────────
           Underline tabs rather than the pill style used by .breakdown-tab
           inside the cohort card, so the page-level control never reads as
           the same kind of switch as the one nested within a card. */
        .dash-tabs {
            display:flex; gap:4px; margin:4px 0 18px; flex-wrap:wrap;
            border-bottom:1px solid #e8e8ea;
        }
        .dash-tab {
            position:relative; display:inline-flex; align-items:center; gap:8px;
            padding:11px 16px; border:none; background:none; cursor:pointer;
            font-family:inherit; font-size:13px; font-weight:600; color:#8a8a92;
            border-bottom:2px solid transparent; margin-bottom:-1px;
            transition:color .16s, border-color .16s;
        }
        .dash-tab i { font-size:12px; }
        .dash-tab:hover { color:var(--primary); }
        .dash-tab.active { color:var(--primary); border-bottom-color:var(--primary); }
        .dash-tab:focus-visible { outline:2px solid var(--primary); outline-offset:-3px; border-radius:6px; }
        .dash-tab-badge {
            display:inline-grid; place-items:center; min-width:18px; height:18px;
            padding:0 5px; border-radius:9px; background:var(--primary); color:#fff;
            font-size:10.5px; font-weight:700; line-height:1;
        }
        /* The blocks below carry their own top margins for the old stacked
           layout; the first one in a pane must not push the pane off the tabs. */
        .dash-pane > :first-child { margin-top:0 !important; }
        @media (max-width: 640px) {
            .dash-tabs { gap:0; }
            .dash-tab { padding:10px 12px; font-size:12px; }
            .dash-tab i { display:none; }
        }

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

        /* ── Recommended actions: slim bar on the Overview tab + modal ── */
        .actions-bar {
            display:flex; align-items:center; gap:12px;
            background:#fff; border-radius:14px; padding:12px 14px 12px 12px; margin-bottom:16px;
            box-shadow:0 2px 6px rgba(15,10,10,.08), 0 10px 22px -10px rgba(15,10,10,.22);
        }
        .actions-bar-icon { width:34px; height:34px; border-radius:10px; display:grid; place-items:center; background:#fef2f2; color:var(--accent); font-size:14px; flex:none; }
        .actions-bar-text { flex:1; min-width:0; display:flex; flex-direction:column; gap:1px; }
        .actions-bar-text strong { font-size:13px; font-weight:600; color:#1a1a1a; }
        .actions-bar-text span { font-size:11.5px; color:#8a8f98; }
        .actions-bar-text em { font-style:normal; color:#b91c1c; font-weight:600; }
        .actions-bar .btn:disabled { opacity:.5; cursor:not-allowed; }
        .actions-modal { max-width:640px; }
        .actions-modal-head { display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .actions-modal-x { width:32px; height:32px; border:none; border-radius:50%; background:#f3f4f6; color:#6b7280; cursor:pointer; flex:none; }
        .actions-modal-x:hover { background:#e5e7eb; color:#1a1a1a; }

        /* ── Notify an inactive faculty member ── */
        .notify-btn { background:#fef2f2; color:var(--accent); border:1px solid #fbd5d5; white-space:nowrap; }
        .notify-btn:hover { background:var(--accent); color:#fff; border-color:var(--accent); }
        .reminded-chip { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:600; color:#047857; background:#ecfdf5; padding:4px 9px; border-radius:999px; white-space:nowrap; }
        .reminded-note { font-size:10px; color:#9ca3af; margin-top:4px; white-space:nowrap; }
        .no-action { color:#d1d5db; }
        .remind-modal { max-width:520px; }
        .remind-label { display:block; font-size:11.5px; font-weight:600; color:#4b5563; margin:12px 0 5px; }
        .remind-input { width:100%; padding:10px 12px; border:1.5px solid #e2e2e6; border-radius:9px; font:13px 'Poppins',sans-serif; color:#1a1a1a; resize:vertical; }
        .remind-input:focus { outline:none; border-color:var(--primary); }
        .remind-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:16px; }
        @media (max-width: 980px) {
            .risk-row { grid-template-columns:minmax(180px, 1.3fr) minmax(145px, 1fr) 85px 70px; }
            .risk-last { display:none; }
        }
        @media (max-width: 620px) {
            .risk-row { grid-template-columns:1fr auto; gap:9px; }
            .risk-head { display:none; }
            .risk-reasons { grid-column:1 / -1; padding-left:49px; }
            .risk-score, .risk-last { display:none; }
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
        $deltaClass = fn ($delta, $goodWhenUp = true) => $delta === 0 ? 'flat' : (($delta > 0) === $goodWhenUp ? 'up' : 'down');
        $deltaIcon = fn ($delta) => $delta > 0 ? 'fa-arrow-up' : ($delta < 0 ? 'fa-arrow-down' : 'fa-minus');
        $deltaText = fn ($delta) => $delta === 0 ? 'No change' : (($delta > 0 ? '+' : '') . $delta);
        $practisingPct = $stats['students'] > 0 ? (int) round($stats['practising'] / $stats['students'] * 100) : 0;
        $facultyNotes = collect([
            $stats['faculty_idle'] ? $stats['faculty_idle'].' not logged in for 30+ days' : null,
            $stats['faculty_overloaded'] ? $stats['faculty_overloaded'].' with a heavy load' : null,
            $stats['faculty_unassigned'] ? $stats['faculty_unassigned'].' with no subjects yet' : null,
        ])->filter()->join(' · ');
    @endphp
    {{-- Headline row: the four numbers a chair acts on first. Each card opens
         the tab (or page) that holds the detail behind it. --}}
    <div class="stats-row">
        <a class="stat-card stat-link" href="#performance" data-open-tab="pane-performance">
            <div class="stat-top">
                <div class="stat-lbl">Students Practising</div>
                <div class="stat-icon si-blue"><i class="fas fa-user-graduate"></i></div>
            </div>
            <div class="stat-num">{{ $stats['practising'] }}<span class="stat-unit">of {{ $stats['students'] }}</span></div>
            <div class="stat-bottom">
                <div class="stat-delta {{ $deltaClass($kpiDeltas['practising']) }}">
                    <i class="fas {{ $deltaIcon($kpiDeltas['practising']) }}"></i>
                    {{ $deltaText($kpiDeltas['practising']) }}<span class="stat-delta-note">vs the week before</span>
                </div>
                <div class="stat-context"><strong>{{ $practisingPct }}%</strong> of active students completed a quiz in the last 7 days.<i class="fas fa-arrow-right stat-go"></i></div>
            </div>
        </a>
        <a class="stat-card stat-link" href="#alerts" data-open-tab="pane-alerts">
            <div class="stat-top">
                <div class="stat-lbl">Students At Risk</div>
                <div class="stat-icon si-red"><i class="fas fa-triangle-exclamation"></i></div>
            </div>
            <div class="stat-num {{ $stats['at_risk'] ? 'is-alert' : '' }}">{{ $stats['at_risk'] }}<span class="stat-unit">students</span></div>
            <div class="stat-bottom">
            @if($stats['at_risk'])
                <div class="stat-delta down"><i class="fas fa-circle-exclamation"></i> {{ $stats['at_risk_high'] }} high priority</div>
            @endif
            <div class="stat-context">
                @if($stats['at_risk'])
                    <strong>{{ $stats['at_risk_inactive'] }}</strong> haven't started any quiz yet.
                @else
                    <strong style="color:#059669;">No students</strong> need intervention.
                @endif
                <i class="fas fa-arrow-right stat-go"></i>
            </div>
            </div>
        </a>
        <a class="stat-card stat-link" href="#faculty" data-open-tab="pane-faculty">
            <div class="stat-top">
                <div class="stat-lbl">Faculty Needing Attention</div>
                <div class="stat-icon si-orange"><i class="fas fa-user-clock"></i></div>
            </div>
            <div class="stat-num {{ $stats['faculty_attention'] ? 'is-warn' : '' }}">{{ $stats['faculty_attention'] }}<span class="stat-unit">of {{ $stats['faculty'] }}</span></div>
            <div class="stat-bottom">
            <div class="stat-context">
                @if($stats['faculty_attention'])
                    {{ $facultyNotes }}
                @else
                    <strong style="color:#059669;">All faculty</strong> are active and assigned.
                @endif
                <i class="fas fa-arrow-right stat-go"></i>
            </div>
            </div>
        </a>
        <a class="stat-card stat-link" href="{{ route('chair.subjects') }}">
            <div class="stat-top">
                <div class="stat-lbl">Subject Coverage</div>
                <div class="stat-icon si-green"><i class="fas fa-circle-check"></i></div>
            </div>
            <div class="stat-num">{{ $stats['assigned'] }}<span class="stat-unit">/ {{ $stats['subjects'] }} covered</span></div>
            <div class="stat-bottom">
                <div class="stat-delta {{ $deltaClass($kpiDeltas['assigned']) }}">
                    <i class="fas {{ $deltaIcon($kpiDeltas['assigned']) }}"></i>
                    {{ $deltaText($kpiDeltas['assigned']) }}<span class="stat-delta-note">vs 30 days ago</span>
                </div>
            <div class="stat-context">
                @if($stats['uncovered']->isNotEmpty())
                    <strong style="color:var(--accent);">No faculty:</strong> {{ $stats['uncovered']->join(', ') }}
                @else
                    <strong style="color:#059669;">All subjects</strong> have faculty coverage.
                @endif
                <i class="fas fa-arrow-right stat-go"></i>
            </div>
            </div>
        </a>
    </div>


    {{-- Each tab keeps its own filters, and the bar shows only the ones that
         tab's charts use (the At-Risk tab has no date range, but can narrow by
         priority and reason). Changes redraw that tab's charts in place (see the chart script
         at the bottom) and are mirrored into the URL so a filtered view can be
         bookmarked or shared. --}}
    <section class="dash-filters" aria-label="Chart filters">
        <div class="filter-label"><i class="fas fa-filter"></i> Filters</div>

        <div class="filter-field" data-tabs="overview performance faculty">
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
                    <button type="button" class="btn btn-ghost btn-sm" id="rangeCancel">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" id="rangeApply">Apply</button>
                </div>
            </div>
        </div>

        <label class="filter-field" data-tabs="overview performance faculty alerts">
            <span class="filter-ico"><i class="fas fa-book-open"></i></span>
            <span class="filter-text">
                <span class="filter-cap">Subject</span>
                <select id="filterSubject" aria-label="Subject">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" @selected($chartFilters['subject'] === $s->id)>{{ $s->code }} — {{ $s->name }}</option>
                    @endforeach
                </select>
            </span>
            <i class="fas fa-chevron-down filter-chev"></i>
        </label>

        <label class="filter-field" data-tabs="overview performance faculty alerts">
            <span class="filter-ico"><i class="fas fa-users"></i></span>
            <span class="filter-text">
                <span class="filter-cap">Section</span>
                <select id="filterSection" aria-label="Section">
                    <option value="">All Sections</option>
                    @foreach($sectionOptions->groupBy(fn ($sec) => $sec->year_level ? (\App\Models\Section::YEAR_LABELS[$sec->year_level] ?? 'Year '.$sec->year_level) : 'No year level') as $yearLabel => $group)
                        <optgroup label="{{ $yearLabel }}">
                            @foreach($group as $sec)
                                <option value="{{ $sec->name }}" @selected($chartFilters['section'] === $sec->name)>{{ $sec->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </span>
            <i class="fas fa-chevron-down filter-chev"></i>
        </label>

        <label class="filter-field" data-tabs="alerts" hidden>
            <span class="filter-ico"><i class="fas fa-flag"></i></span>
            <span class="filter-text">
                <span class="filter-cap">Priority</span>
                <select id="filterPriority" aria-label="Priority">
                    <option value="">All Priorities</option>
                    <option value="high">High</option>
                    <option value="watch">Watch</option>
                </select>
            </span>
            <i class="fas fa-chevron-down filter-chev"></i>
        </label>

        <label class="filter-field" data-tabs="alerts" hidden>
            <span class="filter-ico"><i class="fas fa-circle-exclamation"></i></span>
            <span class="filter-text">
                <span class="filter-cap">Alert Reason</span>
                <select id="filterReason" aria-label="Alert reason">
                    <option value="">All Reasons</option>
                    @foreach(\App\Services\ChairDashboardService::REASONS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </span>
            <i class="fas fa-chevron-down filter-chev"></i>
        </label>

        <span class="filter-status" id="filterStatus" aria-live="polite"></span>
        <button type="button" class="filter-reset" id="filterReset"><i class="fas fa-rotate"></i> Reset Filters</button>
    </section>

    {{-- The dashboard grew past the point where it reads as a summary, so the
         detail is filed behind tabs. The four KPI cards above stay put: they
         are the at-a-glance figures and belong on every tab. --}}
    <div class="dash-tabs" role="tablist" aria-label="Dashboard sections">
        <button type="button" class="dash-tab active" role="tab" id="tab-overview"
                aria-controls="pane-overview" aria-selected="true" data-pane="pane-overview">
            <i class="fas fa-gauge-high"></i> Overview
        </button>
        <button type="button" class="dash-tab" role="tab" id="tab-performance"
                aria-controls="pane-performance" aria-selected="false" data-pane="pane-performance">
            <i class="fas fa-chart-line"></i> Performance
        </button>
        <button type="button" class="dash-tab" role="tab" id="tab-faculty"
                aria-controls="pane-faculty" aria-selected="false" data-pane="pane-faculty">
            <i class="fas fa-chalkboard-user"></i> Faculty
        </button>
        <button type="button" class="dash-tab" role="tab" id="tab-alerts"
                aria-controls="pane-alerts" aria-selected="false" data-pane="pane-alerts">
            <i class="fas fa-triangle-exclamation"></i> At-Risk
            <span class="dash-tab-badge" id="riskBadge" @if($atRiskStudents->isEmpty()) hidden @endif>{{ $atRiskStudents->count() }}</span>
        </button>
    </div>

    <section class="dash-pane" id="pane-overview" role="tabpanel" aria-labelledby="tab-overview">
    {{-- Recommended actions live in a modal; this bar just says how many are waiting. --}}
    @php $urgentActions = $recommendedActions->where('severity', 'high')->count(); @endphp
    <div class="actions-bar">
        <span class="actions-bar-icon"><i class="fas fa-list-check"></i></span>
        <div class="actions-bar-text">
            <strong>Recommended Actions</strong>
            <span>
                @if($recommendedActions->isNotEmpty())
                    {{ $recommendedActions->count() }} {{ Str::plural('action', $recommendedActions->count()) }} suggested
                    @if($urgentActions) · <em>{{ $urgentActions }} urgent</em>@endif
                @else
                    Nothing needs action right now
                @endif
            </span>
        </div>
        <button type="button" class="btn btn-primary btn-sm" onclick="openActions()" @disabled($recommendedActions->isEmpty())>
            <i class="fas fa-eye"></i> View actions
        </button>
    </div>

    <section class="chart-grid cols-2" aria-label="Filtered analytics">
        <article class="chart-card">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-bullseye"></i></span>
                <span class="chart-card-title">Class-Level Accuracy</span>
                <a href="{{ route('chair.analytics.performance') }}" class="chart-card-link" aria-label="Open performance analytics"><i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="chart-card-value" id="kpiAccuracy">—</div>
            <div class="chart-card-note" id="noteAccuracy">&nbsp;</div>
            <div class="chart-canvas-wrap">
                <canvas id="chartAccuracy" aria-label="Class accuracy over the selected range" role="img"></canvas>
                <div class="viz-empty chart-empty" id="emptyAccuracy" hidden>No quiz activity in this range.</div>
            </div>
            <div class="chart-card-cap">Quizzes completed within the range</div>
        </article>

        <article class="chart-card">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-chart-column"></i></span>
                <span class="chart-card-title">Board Readiness</span>
                <a href="{{ route('chair.analytics.performance') }}#readiness-trend" class="chart-card-link" aria-label="Open readiness analytics"><i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="chart-card-value" id="kpiReadiness">—</div>
            <div class="chart-card-note" id="noteReadiness">&nbsp;</div>
            <div class="chart-canvas-wrap">
                <canvas id="chartReadiness" aria-label="Board readiness by subject" role="img"></canvas>
                <div class="viz-empty chart-empty" id="emptyReadiness" hidden>No student has enough attempts to be measured yet.</div>
            </div>
            <div class="chart-card-cap" id="capReadiness">By subject, as of the range end</div>
        </article>

        <article class="chart-card">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-layer-group"></i></span>
                <span class="chart-card-title">Thin Test-Bank Areas</span>
                <a href="{{ route('chair.analytics.test-bank-coverage') }}" class="chart-card-link" aria-label="Open test-bank coverage"><i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="chart-card-value" id="kpiThin">—</div>
            <div class="chart-card-note" id="noteThin">Topics below {{ \App\Services\ChairAnalyticsService::COVERAGE_TARGET }} active questions</div>
            <div class="chart-canvas-wrap">
                <canvas id="chartThin" aria-label="Thin test-bank areas by subject" role="img"></canvas>
                <div class="viz-empty chart-empty" id="emptyThin" hidden>Every area is well stocked.</div>
            </div>
            <div class="chart-card-cap">Follows the subject filter only</div>
        </article>

        <article class="chart-card">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-graduation-cap"></i></span>
                <span class="chart-card-title">Pass Projection</span>
                <a href="{{ route('chair.analytics.performance') }}#pass-projection" class="chart-card-link" aria-label="Open pass projection analytics"><i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="chart-card-value" id="kpiProjection">—</div>
            <div class="chart-card-note" id="noteProjection">&nbsp;</div>
            <div class="chart-canvas-wrap">
                <canvas id="chartProjection" aria-label="Pass projection over the selected range" role="img"></canvas>
                <div class="viz-empty chart-empty" id="emptyProjection" hidden>No student has enough attempts to be measured yet.</div>
            </div>
            <div class="chart-card-cap">Readiness-based, as of each period end</div>
        </article>
    </section>

    <div class="roster-modal-overlay" id="actionsModal" role="dialog" aria-modal="true" aria-labelledby="actionsModalTitle">
        <div class="roster-modal actions-modal">
            <div class="actions-modal-head">
                <h3 id="actionsModalTitle"><i class="fas fa-list-check" style="color:var(--accent);margin-right:8px;"></i>Recommended Actions</h3>
                <button type="button" class="actions-modal-x" onclick="closeActions()" aria-label="Close"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="roster-modal-sub">Rule-based, drawn from the student alerts and weak topics — not a prediction.</div>

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
    </div>
    </section>

    <section class="dash-pane" id="pane-performance" role="tabpanel" aria-labelledby="tab-performance" hidden>
    <section class="chart-grid cols-2" aria-label="Filtered performance charts">
        <article class="chart-card span-2">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-chart-line"></i></span>
                <span class="chart-card-title">Board Readiness Rate vs. Class Accuracy</span>
                <a href="{{ route('chair.analytics.performance') }}#readiness-trend" class="chart-card-link" aria-label="Open the full performance report"><i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="chart-card-note" id="notePerfTrend">&nbsp;</div>
            <div class="chart-canvas-wrap tall">
                <canvas id="chartPerfTrend" role="img" aria-label="Cumulative board readiness rate and per-period class accuracy over the selected range"></canvas>
                <div class="viz-empty chart-empty" id="emptyPerfTrend" hidden>No quiz activity yet.</div>
            </div>
            <div class="chart-card-cap">Readiness rate: share of measured students (20+ questions) who have 50+ questions at 75%+ accuracy across 3+ subjects, counted from the start. Class accuracy: % of questions answered correctly within each period.</div>
        </article>

        <article class="chart-card">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-book-open"></i></span>
                <span class="chart-card-title">Accuracy by Subject</span>
            </div>
            <div class="chart-card-note">Bars are class accuracy; the dark rule is each subject's passing mark.</div>
            <div class="chart-canvas-wrap tall">
                <canvas id="chartPerfSubject" role="img" aria-label="Class accuracy per subject against its passing mark"></canvas>
                <div class="viz-empty chart-empty" id="emptyPerfSubject" hidden>No quiz activity in this range.</div>
            </div>
            <div class="chart-card-cap">Quizzes completed within the range</div>
        </article>

        <article class="chart-card">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-people-group"></i></span>
                <span class="chart-card-title">Accuracy by Section</span>
            </div>
            <div class="chart-card-note" id="notePerfSection">&nbsp;</div>
            <div class="chart-canvas-wrap tall">
                <canvas id="chartPerfSection" role="img" aria-label="Class accuracy per section"></canvas>
                <div class="viz-empty chart-empty" id="emptyPerfSection" hidden>No section has quiz activity in this range.</div>
            </div>
            <div class="chart-card-cap">Shows every section — the selected one is highlighted</div>
        </article>

    </section>
    <a class="report-link" href="{{ route('chair.analytics.performance') }}">
        <i class="fas fa-chart-line"></i>
        <span><strong>More in Class-Level Performance</strong> — readiness bands, score distribution, practice per period, difficulty, weakest topics and the student leaderboard.</span>
        <i class="fas fa-arrow-right"></i>
    </a>

    <div class="card" style="margin-bottom:18px;">
        <div class="card-head">
            <span class="card-title"><i class="fas fa-layer-group" style="color:var(--accent);margin-right:7px;"></i>Class-Level Performance by Cohort <span class="scope-tag">All-time · not filtered</span></span>
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

    </section>

    <section class="dash-pane" id="pane-faculty" role="tabpanel" aria-labelledby="tab-faculty" hidden>
    <section class="chart-grid kpi-grid" aria-label="Filtered faculty figures">
        <article class="chart-card kpi-card">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-chalkboard-user"></i></span>
                <span class="chart-card-title">Faculty</span>
                <a href="{{ route('chair.faculty') }}" class="chart-card-link" aria-label="Manage faculty"><i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="chart-card-value" id="kpiFacCount">—</div>
            <div class="chart-card-note" id="noteFacCount">&nbsp;</div>
            <div class="kpi-foot" id="footFacCount">&nbsp;</div>
        </article>

        <article class="chart-card kpi-card">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-file-circle-plus"></i></span>
                <span class="chart-card-title">Questions Added</span>
            </div>
            <div class="chart-card-value" id="kpiFacQuestions">—</div>
            <div class="chart-card-note" id="noteFacQuestions">&nbsp;</div>
            <div class="kpi-foot" id="footFacQuestions">&nbsp;</div>
        </article>

        <article class="chart-card kpi-card">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-user-clock"></i></span>
                <span class="chart-card-title">Needs Attention</span>
            </div>
            <div class="chart-card-value" id="kpiFacAttention">—</div>
            <div class="chart-card-note" id="noteFacAttention">&nbsp;</div>
            <div class="kpi-foot" id="footFacAttention">&nbsp;</div>
        </article>

        <article class="chart-card kpi-card">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-book-open"></i></span>
                <span class="chart-card-title">Subject Coverage</span>
                <a href="{{ route('chair.subjects') }}" class="chart-card-link" aria-label="Manage subject assignments"><i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="chart-card-value" id="kpiFacCoverage">—</div>
            <div class="chart-card-note" id="noteFacCoverage">&nbsp;</div>
            <div class="kpi-foot" id="footFacCoverage">&nbsp;</div>
        </article>
    </section>

    <div class="card" style="margin-bottom:18px;">
        <div class="card-head">
            <span class="card-title"><i class="fas fa-scale-balanced" style="color:var(--accent);margin-right:7px;"></i>Faculty Workload &amp; Activity</span>
            <a href="{{ route('chair.faculty') }}" class="card-link">Manage Faculty</a>
        </div>
        <div class="viz-sub" style="margin-bottom:6px;">Subject/section load and account activity per faculty — the staffing signals a chair acts on directly.</div>
        <div class="filter-note" id="workloadNote" hidden></div>
        @if($facultyWorkload->isNotEmpty())
            <div class="dash-table-wrap">
            <table class="workload-table">
                <thead>
                    <tr><th>Faculty</th><th>Subjects</th><th>Sections</th><th>Questions Added (30d)</th><th>Last Login</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                @foreach($facultyWorkload as $f)
                    <tr data-faculty-id="{{ $f['id'] }}">
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
                                    <span class="workload-flag unassigned"><i class="fas fa-circle-minus"></i> No subjects yet</span>
                                    @break
                                @case('overloaded')
                                    <span class="workload-flag overloaded"><i class="fas fa-layer-group"></i> Heavy load (3+ subjects)</span>
                                    @break
                                @case('idle')
                                    <span class="workload-flag idle"><i class="fas fa-clock"></i> Not logged in for 30+ days</span>
                                    @break
                                @default
                                    <span class="workload-flag ok"><i class="fas fa-check"></i> Active</span>
                            @endswitch
                        </td>
                        <td>
                            @if($f['flag'] === 'idle')
                                @php $remindedAt = $facultyReminders[$f['id']] ?? null; @endphp
                                @if($remindedAt && $remindedAt->gt(now()->subHours(\App\Http\Controllers\Chair\FacultyReminderController::COOLDOWN_HOURS)))
                                    <span class="reminded-chip" title="Reminded on {{ $remindedAt->format('M j, Y g:i A') }}"><i class="fas fa-check"></i> Reminded {{ $remindedAt->diffForHumans() }}</span>
                                @else
                                    <button type="button" class="btn btn-sm notify-btn"
                                            data-remind-id="{{ $f['id'] }}"
                                            data-remind-name="{{ $f['name'] }}"
                                            data-remind-first="{{ explode(' ', $f['name'])[0] }}"
                                            data-remind-last="{{ $f['last_login_at'] ? $f['last_login_at']->diffForHumans() : 'never' }}">
                                        <i class="fas fa-bell"></i> Notify
                                    </button>
                                    @if($remindedAt)
                                        <div class="reminded-note">Last reminded {{ $remindedAt->diffForHumans() }}</div>
                                    @endif
                                @endif
                            @else
                                <span class="no-action">—</span>
                            @endif
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

    {{-- Reminder for an inactive faculty member: in-app notification + email. --}}
    <div class="roster-modal-overlay" id="remindModal" role="dialog" aria-modal="true" aria-labelledby="remindModalTitle">
        <div class="roster-modal remind-modal">
            <div class="actions-modal-head">
                <h3 id="remindModalTitle"><i class="fas fa-bell" style="color:var(--accent);margin-right:8px;"></i>Notify <span id="remindName">faculty</span></h3>
                <button type="button" class="actions-modal-x" onclick="closeRemind()" aria-label="Close"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="roster-modal-sub">Last login: <strong id="remindLast">—</strong>. They'll get this in their CPAce notifications and by email. One reminder per faculty member every 24 hours.</div>
            <form method="POST" id="remindForm" action="#" data-loading="Sending reminder...">
                @csrf
                <label class="remind-label" for="remindTitle">Subject</label>
                <input class="remind-input" type="text" id="remindTitle" name="title" maxlength="150" required>
                <label class="remind-label" for="remindMessage">Message</label>
                <textarea class="remind-input" id="remindMessage" name="message" rows="8" maxlength="2000" required></textarea>
                <div class="remind-actions">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="closeRemind()">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane"></i> Send reminder</button>
                </div>
            </form>
        </div>
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
    </section>

    <section class="dash-pane" id="pane-alerts" role="tabpanel" aria-labelledby="tab-alerts" hidden>
    <section class="chart-grid cols-3" aria-label="Filtered at-risk charts">
        <article class="chart-card">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-triangle-exclamation"></i></span>
                <span class="chart-card-title">Alert Reasons</span>
            </div>
            <div class="chart-card-value" id="kpiRiskCount">—</div>
            <div class="chart-card-note" id="noteRiskCount">&nbsp;</div>
            <div class="chart-canvas-wrap">
                <canvas id="chartRiskReason" role="img" aria-label="At-risk students by alert reason"></canvas>
                <div class="viz-empty chart-empty" id="emptyRiskReason" hidden>No students need intervention.</div>
            </div>
            <div class="chart-card-cap">A student can have more than one reason</div>
        </article>

        <article class="chart-card">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-people-group"></i></span>
                <span class="chart-card-title">At-Risk by Section</span>
            </div>
            <div class="chart-card-note">High priority and watch, stacked.</div>
            <div class="chart-canvas-wrap tall">
                <canvas id="chartRiskSection" role="img" aria-label="At-risk students per section by priority"></canvas>
                <div class="viz-empty chart-empty" id="emptyRiskSection" hidden>No students need intervention.</div>
            </div>
            <div class="chart-card-cap">Shows every section — the selected one is highlighted</div>
        </article>

        <article class="chart-card">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-clock"></i></span>
                <span class="chart-card-title">Days Since Last Active</span>
            </div>
            <div class="chart-card-note">How long flagged students have been away.</div>
            <div class="chart-canvas-wrap tall">
                <canvas id="chartRiskIdle" role="img" aria-label="At-risk students by days since last activity"></canvas>
                <div class="viz-empty chart-empty" id="emptyRiskIdle" hidden>No students need intervention.</div>
            </div>
            <div class="chart-card-cap">Latest quiz or login</div>
        </article>
    </section>

    <div class="card">
        <div class="card-head">
            <div class="risk-summary">
                <span class="card-title"><i class="fas fa-triangle-exclamation" style="color:var(--accent);margin-right:7px;"></i>At-Risk Student Alerts</span>
                <span class="risk-count" id="riskCount">0</span>
            </div>
            <span class="risk-meta">Low readiness (&lt;60% after 5+ items) or inactive for 7+ days</span>
        </div>
        <div class="risk-list" id="riskList"></div>
        <div class="mini-pagination" id="riskPagination" hidden>
            <span class="mini-pag-info" id="riskPagInfo"></span>
            <div class="mini-pag-btns" id="riskPagBtns"></div>
        </div>
    </div>
    </section>

    {{-- Overlay: lives outside the panes so it is never hidden with one. --}}
    <div class="roster-modal-overlay" id="rosterModal">
        <div class="roster-modal">
            <h3 id="rosterModalTitle">Measured Students</h3>
            <div class="roster-modal-sub" id="rosterModalSub">Attempted enough items to be measured for board readiness — includes Ready, Developing, and At-risk students.</div>
            <div id="rosterModalBody"><div class="roster-loading"><i class="fas fa-spinner fa-spin"></i> Loading students…</div></div>
            <div class="roster-modal-close"><button type="button" class="btn btn-ghost btn-sm" onclick="closeRoster()">Close</button></div>
        </div>
    </div>

</main>

<script>
(function () {
    document.querySelectorAll('.breakdown-tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.breakdown-tab').forEach((t) => t.classList.remove('active'));
            document.querySelectorAll('.breakdown-pane').forEach((p) => p.hidden = true);
            tab.classList.add('active');
            document.getElementById(tab.dataset.pane).hidden = false;
            Viz.reveal(document.getElementById(tab.dataset.pane));
        });
    });

    // ── Top-level dashboard tabs ────────────────────────────────────────
    const dashTabs = [...document.querySelectorAll('.dash-tab')];
    const STORE_KEY = 'chairDashTab';

    function showPane(paneId, { remember = true } = {}) {
        const tab = dashTabs.find((t) => t.dataset.pane === paneId);
        if (!tab) { return; }

        dashTabs.forEach((t) => {
            const on = t === tab;
            t.classList.toggle('active', on);
            t.setAttribute('aria-selected', on ? 'true' : 'false');
            document.getElementById(t.dataset.pane).hidden = !on;
        });

        // A canvas sized while its pane was hidden measures zero, so the kit
        // is told to build or re-measure whatever just came on screen.
        Viz.reveal(document.getElementById(paneId));
        document.dispatchEvent(new CustomEvent('dash:tab', { detail: paneId }));

        if (remember) {
            try { sessionStorage.setItem(STORE_KEY, paneId); } catch (e) { /* private mode */ }
            history.replaceState(null, '', '#' + paneId.replace('pane-', ''));
        }
    }

    dashTabs.forEach((tab, i) => {
        tab.addEventListener('click', () => showPane(tab.dataset.pane));
        tab.addEventListener('keydown', (e) => {
            const step = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
            if (!step) { return; }
            e.preventDefault();
            const next = dashTabs[(i + step + dashTabs.length) % dashTabs.length];
            next.focus();
            showPane(next.dataset.pane);
        });
    });

    // Headline KPI cards jump to the tab holding their detail.
    document.querySelectorAll('[data-open-tab]').forEach((card) => {
        card.addEventListener('click', (e) => {
            e.preventDefault();
            showPane(card.dataset.openTab);
            document.querySelector('.dash-tabs').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Opening order: an explicit #hash wins (so a link can point at a tab),
    // then the tab this chair was last on, then Overview.
    let initial = null;
    const hash = window.location.hash.replace('#', '');
    if (hash && document.getElementById('pane-' + hash)) {
        initial = 'pane-' + hash;
    } else {
        try { initial = sessionStorage.getItem(STORE_KEY); } catch (e) { /* private mode */ }
    }
    if (initial && document.getElementById(initial)) { showPane(initial, { remember: false }); }
})();

/* ── Overview chart cards + filter bar ─────────────────────────────────
   The first render uses data the server already embedded; every filter
   change after that fetches chair.dashboard.data and redraws all four
   cards, so they always describe the same slice of students. */
(function () {
    const P = Viz.palette;
    const ENDPOINT = @json(route('chair.dashboard.data'));
    const DEFAULTS = @json($chartDefaults);
    const MAX_DAYS = 366;

    // Pane id → the key the endpoint and the charts use for that tab.
    const TAB_KEYS = { 'pane-overview': 'overview', 'pane-performance': 'performance', 'pane-faculty': 'faculty', 'pane-alerts': 'at_risk' };
    // Each tab keeps its own filters; the page's query string seeds them all.
    const initialFilters = @json($chartFilters);
    const state = Object.fromEntries(Object.values(TAB_KEYS).map((key) => [key, Object.assign({}, initialFilters)]));
    let activePane = document.querySelector('.dash-tab.active')?.dataset.pane || 'pane-overview';
    let activeTab = TAB_KEYS[activePane] || 'overview';
    const cur = () => state[activeTab];

    const $ = (id) => document.getElementById(id);
    const pct = (v) => (v === null || v === undefined ? '—' : v + '%');
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    // Dates travel as local Y-m-d strings; never through toISOString(),
    // which would shift them a day for anyone east of UTC.
    const iso = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    const parse = (s) => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
    const pretty = (s) => parse(s).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    const daysBetween = (a, b) => Math.round((parse(b) - parse(a)) / 86400000) + 1;

    function presetRange(key) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (key === 'this-month') { return [iso(new Date(today.getFullYear(), today.getMonth(), 1)), iso(today)]; }
        if (key === 'last-month') {
            return [iso(new Date(today.getFullYear(), today.getMonth() - 1, 1)), iso(new Date(today.getFullYear(), today.getMonth(), 0))];
        }
        const from = new Date(today);
        from.setDate(from.getDate() - (Number(key) - 1));
        return [iso(from), iso(today)];
    }

    function changeText(change, unit, suffix) {
        if (change === null || change === undefined) { return ''; }
        if (change === 0) { return '<span>No change</span> ' + suffix; }
        const cls = change > 0 ? 'up' : 'down';
        const icon = change > 0 ? 'fa-arrow-up' : 'fa-arrow-down';
        return `<span class="${cls}"><i class="fas ${icon}"></i> ${change > 0 ? '+' : ''}${change}${unit}</span> ${suffix}`;
    }

    const toggleEmpty = (id, empty) => { $(id).hidden = !empty; };
    const allNull = (values) => values.every((v) => v === null || v === undefined);

    function renderOverview(c) {
        const periodWord = { day: 'day', week: 'week', month: 'month' }[c.range.bucket] || 'period';
        const vsPrev = `vs previous ${c.range.days} day${c.range.days === 1 ? '' : 's'}`;

        // ── Class-level accuracy ──
        $('kpiAccuracy').textContent = pct(c.accuracy.value);
        const weakest = c.accuracy.weakest ? `Weakest: <strong>${esc(c.accuracy.weakest.code)}</strong> at ${c.accuracy.weakest.accuracy}%` : '';
        $('noteAccuracy').innerHTML = c.accuracy.value === null
            ? 'No quiz activity in this range'
            : (weakest || changeText(c.accuracy.change, ' pts', vsPrev));
        const accTrend = c.accuracy.trend;
        toggleEmpty('emptyAccuracy', allNull(accTrend.map((r) => r.accuracy)));
        Viz.chart('chartAccuracy', {
            type: 'line',
            data: {
                labels: accTrend.map((r) => r.label),
                datasets: [Viz.line({
                    label: 'Class accuracy (per period)', data: accTrend.map((r) => r.accuracy),
                    borderColor: P.s1, pointBackgroundColor: P.s1, backgroundColor: 'rgba(163,43,43,.10)',
                    fill: true, spanGaps: true, pointRadius: accTrend.length > 16 ? 0 : 3,
                })],
            },
            options: {
                interaction: { mode: 'index', intersect: false },
                scales: { y: Viz.percentAxis(), x: Viz.catAxis({ ticks: { color: P.ink, padding: 6, maxRotation: 0, autoSkip: true, maxTicksLimit: 5 } }) },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: {
                        title: (items) => (c.range.bucket === 'day' ? '' : periodWord.charAt(0).toUpperCase() + periodWord.slice(1) + ' of ') + items[0].label,
                        label: (ctx) => ctx.raw === null ? 'No activity' : ctx.raw + '% accuracy',
                    } },
                },
            },
        });

        // ── Board readiness by subject ──
        $('kpiReadiness').textContent = pct(c.readiness.value);
        // Spell out the count behind the percentage so a 0% reads as "none of
        // the few measured are ready yet", not as missing data.
        const rd = c.readiness;
        $('noteReadiness').innerHTML = rd.value === null
            ? 'No measured students yet'
            : `<strong>${rd.ready} of ${rd.eligible}</strong> measured student${rd.eligible === 1 ? '' : 's'} ready`
                + (rd.change ? ' · ' + changeText(rd.change, ' pts', '') : '');
        const bySubject = c.readiness.by_subject;
        const unmeasured = bySubject.filter((r) => r.rate === null).map((r) => r.code);
        $('capReadiness').innerHTML = (rd.not_measured ? `<strong>${rd.not_measured}</strong> not yet measured (under 20 items)` : 'Every active student is measured')
            + (unmeasured.length && unmeasured.length < bySubject.length ? ` · No measured students yet: ${esc(unmeasured.join(', '))}` : '');
        toggleEmpty('emptyReadiness', allNull(bySubject.map((r) => r.rate)));
        Viz.chart('chartReadiness', {
            type: 'bar',
            data: {
                labels: bySubject.map((r) => r.code),
                datasets: [Viz.bar({ label: 'Ready', data: bySubject.map((r) => r.rate), backgroundColor: P.s1, minBarLength: 2 })],
            },
            options: {
                scales: { y: Viz.percentAxis(), x: Viz.catAxis() },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => {
                        const row = bySubject[ctx.dataIndex];
                        return row.rate === null
                            ? 'No measured students yet'
                            : `${Math.round(row.rate * row.eligible / 100)} of ${row.eligible} measured ready (${row.rate}%)`;
                    } } },
                },
            },
        });

        // ── Thin test-bank areas ──
        $('kpiThin').textContent = c.thin.value;
        const thinRows = c.thin.by_subject;
        toggleEmpty('emptyThin', c.thin.value === 0);
        // Counts per subject are compared, not a share of a whole: bars, one
        // hue, with the count at each bar's end.
        Viz.chart('chartThin', {
            type: 'bar',
            data: {
                labels: thinRows.map((r) => r.code),
                datasets: [Viz.bar({ label: 'Thin areas', data: thinRows.map((r) => r.topics), backgroundColor: P.s1, maxBarThickness: 16 })],
            },
            options: {
                indexAxis: 'y',
                layout: { padding: { right: 24 } },
                scales: { x: Viz.countAxis(), y: Viz.catAxis() },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.raw} thin area${ctx.raw === 1 ? '' : 's'}` } },
                },
            },
            plugins: [Viz.endLabels()],
        });

        // ── Pass projection over time ──
        $('kpiProjection').textContent = pct(c.projection.value);
        $('noteProjection').textContent = c.projection.value === null
            ? 'No measured students yet'
            : `Readiness-based · ${c.projection.eligible} measured student${c.projection.eligible === 1 ? '' : 's'}`;
        const projTrend = c.projection.trend;
        toggleEmpty('emptyProjection', allNull(projTrend.map((r) => r.projection)));
        // A value over time: a line, like the accuracy card beside it.
        Viz.chart('chartProjection', {
            type: 'line',
            data: {
                labels: projTrend.map((r) => r.label),
                datasets: [Viz.line({
                    label: 'Pass projection', data: projTrend.map((r) => r.projection),
                    borderColor: P.s1, pointBackgroundColor: P.s1, backgroundColor: 'rgba(163,43,43,.10)',
                    fill: true, spanGaps: true, pointRadius: projTrend.length > 16 ? 0 : 3,
                })],
            },
            options: {
                interaction: { mode: 'index', intersect: false },
                scales: { y: Viz.percentAxis(), x: Viz.catAxis({ ticks: { color: P.ink, padding: 6, maxRotation: 0, autoSkip: true, maxTicksLimit: 6 } }) },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => {
                        const row = projTrend[ctx.dataIndex];
                        return row.projection === null ? 'No measured students yet' : `${row.projection}% projected · ${row.eligible} measured`;
                    } } },
                },
            },
        });
    }

    const plural = (n, word) => `${n} ${word}${n === 1 ? '' : 's'}`;
    const tickAxis = (limit) => Viz.catAxis({ ticks: { color: P.ink, padding: 6, maxRotation: 0, autoSkip: true, maxTicksLimit: limit } });
    // A highlighted bar keeps the full series colour; the rest fade back so
    // the selected section is found at a glance without a second hue.
    const highlight = (names, selected) => names.map((n) => (!selected || n === selected ? P.s1 : 'rgba(163,43,43,.28)'));

    function renderPerformance(p, range) {
        // ── Trend ──
        const last = p.trend[p.trend.length - 1];
        $('notePerfTrend').textContent = last && last.eligible
            ? `${last.ready} of ${plural(last.eligible, 'measured student')} ready by ${pretty(range.to)}`
            : 'No measured students yet';
        toggleEmpty('emptyPerfTrend', allNull(p.trend.map((r) => r.accuracy)));
        Viz.chart('chartPerfTrend', {
            type: 'line',
            data: {
                labels: p.trend.map((r) => r.label),
                datasets: [
                    Viz.line({ label: 'Readiness rate (cumulative)', data: p.trend.map((r) => r.readiness), borderColor: P.s1, pointBackgroundColor: P.s1, backgroundColor: 'rgba(163,43,43,.08)', fill: true, spanGaps: true, pointRadius: p.trend.length > 16 ? 0 : 3 }),
                    Viz.line({ label: 'Class accuracy (per period)', data: p.trend.map((r) => r.accuracy), borderColor: P.s2, pointBackgroundColor: P.s2, backgroundColor: 'transparent', spanGaps: true, pointRadius: p.trend.length > 16 ? 0 : 3 }),
                ],
            },
            options: {
                interaction: { mode: 'index', intersect: false },
                scales: { y: Viz.percentAxis(), x: tickAxis(6) },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: {
                        label: (ctx) => `${ctx.dataset.label}: ${ctx.raw === null ? '—' : ctx.raw + '%'}`,
                        afterBody: (items) => { const r = p.trend[items[0].dataIndex]; return [`${r.ready} of ${plural(r.eligible, 'measured student')} ready`, `${(r.answered || 0).toLocaleString()} questions answered this period`]; },
                    } },
                },
            },
        });

        // ── Accuracy by subject, with each subject's passing mark ──
        toggleEmpty('emptyPerfSubject', allNull(p.by_subject.map((r) => r.accuracy)));
        Viz.chart('chartPerfSubject', {
            type: 'bar',
            data: {
                labels: p.by_subject.map((r) => r.code),
                datasets: [Viz.bar({ label: 'Class accuracy', data: p.by_subject.map((r) => r.accuracy), backgroundColor: p.by_subject.map((r) => (r.accuracy !== null && r.accuracy >= r.threshold ? P.s1 : 'rgba(163,43,43,.55)')) })],
            },
            options: {
                scales: { y: Viz.percentAxis(), x: Viz.catAxis() },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => {
                        const r = p.by_subject[ctx.dataIndex];
                        return r.accuracy === null ? 'No activity in range' : `${r.accuracy}% accuracy · passing ${r.threshold}% · ${r.items.toLocaleString()} items`;
                    } } },
                },
            },
            plugins: [{
                id: 'thresholdRules',
                afterDatasetsDraw(chart) {
                    const { ctx, scales: { y } } = chart;
                    ctx.save();
                    ctx.strokeStyle = P.ink;
                    ctx.lineWidth = 2;
                    chart.getDatasetMeta(0).data.forEach((bar, i) => {
                        const py = y.getPixelForValue(p.by_subject[i].threshold);
                        const half = (bar.width || 20) / 2 + 3;
                        ctx.beginPath();
                        ctx.moveTo(bar.x - half, py);
                        ctx.lineTo(bar.x + half, py);
                        ctx.stroke();
                    });
                    ctx.restore();
                },
            }],
        });

        // ── Accuracy by section ──
        const secs = p.by_section;
        $('notePerfSection').textContent = state.performance.section
            ? `${state.performance.section} vs the other sections`
            : `${plural(secs.length, 'active section')} compared`;
        toggleEmpty('emptyPerfSection', allNull(secs.map((r) => r.accuracy)));
        Viz.chart('chartPerfSection', {
            type: 'bar',
            data: {
                labels: secs.map((r) => r.section),
                datasets: [Viz.bar({ label: 'Class accuracy', data: secs.map((r) => r.accuracy), backgroundColor: highlight(secs.map((r) => r.section), state.performance.section) })],
            },
            options: {
                scales: { y: Viz.percentAxis(), x: Viz.catAxis() },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => {
                        const r = secs[ctx.dataIndex];
                        return r.accuracy === null ? 'No activity in range' : `${r.accuracy}% · ${plural(r.students, 'student')} · ${r.items.toLocaleString()} items`;
                    } } },
                },
            },
        });
    }

    function renderFaculty(f, range) {
        const filtered = state.faculty.subject || state.faculty.section;
        const status = Object.fromEntries(f.status.map((s) => [s.flag, s.count]));

        // ── Faculty ──
        $('kpiFacCount').textContent = f.count;
        $('noteFacCount').textContent = filtered ? 'Matching the subject/section filter' : 'All faculty accounts';
        $('footFacCount').innerHTML = f.count
            ? `<strong>${status.ok || 0}</strong> active · ${Math.round((status.ok || 0) / f.count * 100)}% of faculty`
            : 'No faculty match these filters';

        // ── Questions added in the range ──
        const top = f.contributors.find((c) => c.added > 0);
        $('kpiFacQuestions').textContent = f.questions_total.toLocaleString();
        $('noteFacQuestions').textContent = `${pretty(range.from)} – ${pretty(range.to)}`;
        $('footFacQuestions').innerHTML = top
            ? `Top: <strong>${esc(top.name)}</strong> · ${plural(top.added, 'question')}`
            : 'No questions were added in this range';

        // ── Needs attention: anyone not on track ──
        const attention = (status.overloaded || 0) + (status.idle || 0) + (status.unassigned || 0);
        $('kpiFacAttention').textContent = attention;
        $('kpiFacAttention').classList.toggle('is-alert', attention > 0);
        $('noteFacAttention').textContent = attention ? 'Faculty who may need a follow-up' : 'Everyone is active and assigned';
        $('footFacAttention').innerHTML = `<strong>${status.idle || 0}</strong> not logged in for 30+ days · <strong>${status.overloaded || 0}</strong> heavy load · <strong>${status.unassigned || 0}</strong> no subjects yet`;

        // ── Subject coverage ──
        const per = f.per_subject;
        const covered = per.filter((r) => r.faculty > 0).length;
        const uncovered = per.filter((r) => r.faculty === 0).map((r) => r.code);
        $('kpiFacCoverage').innerHTML = `${covered}<span class="kpi-of">/ ${per.length}</span>`;
        $('noteFacCoverage').textContent = state.faculty.section ? `Subjects with faculty for ${state.faculty.section}` : 'Subjects with at least one faculty';
        $('footFacCoverage').innerHTML = uncovered.length
            ? `<span class="down">No faculty:</span> ${esc(uncovered.join(', '))}`
            : '<span class="up">Every subject is covered</span>';

        // ── Workload table follows the subject/section filter ──
        const ids = new Set(f.ids.map(Number));
        const rows = [...document.querySelectorAll('tr[data-faculty-id]')];
        rows.forEach((tr) => { tr.hidden = !ids.has(Number(tr.dataset.facultyId)); });
        const note = $('workloadNote');
        if (note) {
            const filtered = state.faculty.subject || state.faculty.section;
            note.hidden = !filtered;
            note.textContent = filtered ? `Showing ${ids.size} of ${plural(rows.length, 'faculty member')} matching the subject/section filter.` : '';
        }
    }

    // ── At-risk ──
    const RISK_PAGE = 8;
    let riskRows = [];
    let riskPage = 1;

    function renderRiskList() {
        const list = $('riskList');
        const pages = Math.max(1, Math.ceil(riskRows.length / RISK_PAGE));
        riskPage = Math.min(riskPage, pages);
        if (!riskRows.length) {
            list.innerHTML = '<div class="risk-empty"><i class="fas fa-circle-check"></i>No students currently need intervention.</div>';
            $('riskPagination').hidden = true;
            return;
        }
        const slice = riskRows.slice((riskPage - 1) * RISK_PAGE, riskPage * RISK_PAGE);
        list.innerHTML = '<div class="risk-row risk-head" aria-hidden="true"><span>Student</span><span>Alert reason</span><span>Readiness</span><span class="risk-last">Last active</span><span>Priority</span></div>'
            + slice.map((s) => `
            <div class="risk-row">
                <div class="risk-student">
                    <div class="user-av" style="background:var(--primary);">${esc(s.initials)}</div>
                    <div style="min-width:0;">
                        <div class="risk-name">${esc(s.name)}</div>
                        <div class="risk-email">${esc(s.email)}${s.section ? ' · ' + esc(s.section) : ''}</div>
                    </div>
                </div>
                <div class="risk-reasons">${s.reasons.map((r) => `<span class="risk-reason ${r === 'Low readiness' ? 'low' : ''}"><i class="fas ${r === 'Low readiness' ? 'fa-chart-line' : 'fa-clock'}"></i>${esc(r)}</span>`).join('')}</div>
                <div class="risk-score ${s.score === null ? 'na' : ''}">
                    ${s.score === null ? 'Not rated' : s.score + '%'}
                    <div class="risk-meta">${s.attempted} items</div>
                </div>
                <div class="risk-last">
                    <div style="font-size:12px;color:#555;">${esc(s.last_active || 'Never')}</div>
                    <div class="risk-meta">${s.quizzes} completed quiz${s.quizzes === 1 ? '' : 'zes'}</div>
                </div>
                <span class="risk-priority ${s.priority}">${s.priority === 'high' ? 'High' : 'Watch'}</span>
            </div>`).join('');

        $('riskPagination').hidden = pages <= 1;
        const from = (riskPage - 1) * RISK_PAGE + 1;
        $('riskPagInfo').textContent = `Showing ${from}–${Math.min(riskPage * RISK_PAGE, riskRows.length)} of ${plural(riskRows.length, 'student')}`;
        // First, last, and a window around the current page — 90 students
        // must not become 12 buttons.
        const shown = [...new Set([1, riskPage - 1, riskPage, riskPage + 1, pages])].filter((n) => n >= 1 && n <= pages).sort((a, b) => a - b);
        let html = `<button type="button" class="mini-pag-btn" data-go="prev" ${riskPage <= 1 ? 'disabled' : ''} aria-label="Previous page"><i class="fas fa-chevron-left"></i></button>`;
        shown.forEach((n, i) => {
            if (i && n - shown[i - 1] > 1) { html += '<span class="mini-pag-gap">…</span>'; }
            html += `<button type="button" class="mini-pag-btn ${n === riskPage ? 'active' : ''}" data-go="${n}">${n}</button>`;
        });
        html += `<button type="button" class="mini-pag-btn" data-go="next" ${riskPage >= pages ? 'disabled' : ''} aria-label="Next page"><i class="fas fa-chevron-right"></i></button>`;
        $('riskPagBtns').innerHTML = html;
    }

    $('riskPagBtns').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-go]');
        if (!btn || btn.disabled) { return; }
        const go = btn.dataset.go;
        riskPage = go === 'prev' ? riskPage - 1 : go === 'next' ? riskPage + 1 : Number(go);
        renderRiskList();
    });

    function renderAtRisk(r) {
        $('kpiRiskCount').textContent = r.count;
        $('noteRiskCount').innerHTML = `<span class="down">${r.high} high priority</span> · ${r.watch} watch`;
        $('riskCount').textContent = r.count;
        const badge = $('riskBadge');
        badge.textContent = r.count;
        badge.hidden = r.count === 0;

        // A student can carry two reasons, so the counts overlap and are not
        // shares of one whole — bars, not a donut.
        const reasons = r.by_reason;
        toggleEmpty('emptyRiskReason', r.count === 0);
        Viz.chart('chartRiskReason', {
            type: 'bar',
            // Long reasons wrap onto two lines so the axis never clips them.
            data: { labels: reasons.map((x) => (x.reason.length > 14 ? x.reason.replace(/ (?=\S+$)/, '\n').split('\n') : x.reason)), datasets: [Viz.bar({ label: 'Students', data: reasons.map((x) => x.students), backgroundColor: P.s1, maxBarThickness: 20 })] },
            options: {
                indexAxis: 'y',
                layout: { padding: { right: 28 } },
                scales: { x: Viz.countAxis(), y: Viz.catAxis() },
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => plural(ctx.raw, 'student') } } },
            },
            plugins: [Viz.endLabels()],
        });

        const secs = r.by_section;
        const fade = (color, name) => (!state.at_risk.section || name === state.at_risk.section ? color : color + '47');
        toggleEmpty('emptyRiskSection', secs.length === 0);
        Viz.chart('chartRiskSection', {
            type: 'bar',
            data: {
                labels: secs.map((x) => x.section),
                datasets: [
                    Viz.stacked({ label: 'High', data: secs.map((x) => x.high), backgroundColor: secs.map((x) => fade('#b91c1c', x.section)) }),
                    Viz.stacked({ label: 'Watch', data: secs.map((x) => x.watch), backgroundColor: secs.map((x) => fade('#fab219', x.section)) }),
                ],
            },
            options: {
                scales: { x: Viz.catAxis({ stacked: true }), y: Viz.countAxis({ stacked: true }) },
                plugins: { legend: { position: 'bottom' }, tooltip: { mode: 'index', intersect: false } },
            },
        });

        const idle = r.by_idle;
        toggleEmpty('emptyRiskIdle', r.count === 0);
        Viz.chart('chartRiskIdle', {
            type: 'bar',
            data: { labels: idle.map((x) => x.label), datasets: [Viz.bar({ label: 'Students', data: idle.map((x) => x.students), backgroundColor: ['rgba(163,43,43,.35)', 'rgba(163,43,43,.55)', 'rgba(163,43,43,.8)', P.s1] })] },
            options: {
                scales: { y: Viz.countAxis(), x: Viz.catAxis() },
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => plural(ctx.raw, 'student') } } },
            },
        });

        riskRows = r.students;
        riskPage = 1;
        renderRiskList();
    }

    // ── Filter controls ──
    // Each tab keeps its own filters and asks the endpoint for its own data
    // only, so changing a filter never disturbs what another tab is showing.
    const RENDERERS = {
        overview: (d) => renderOverview(Object.assign({ range: d.range }, d.overview)),
        performance: (d) => renderPerformance(d.performance, d.range),
        faculty: (d) => renderFaculty(d.faculty, d.range),
        at_risk: (d) => renderAtRisk(d.at_risk),
    };
    const render = (data) => Object.keys(RENDERERS).forEach((key) => { if (data[key]) { RENDERERS[key](data); } });

    const rangePop = $('rangePop');
    const rangeTrigger = $('rangeTrigger');
    const inflight = {};

    function syncControls() {
        const f = cur();
        const pane = activePane.replace('pane-', '');
        document.querySelectorAll('.dash-filters [data-tabs]').forEach((el) => {
            el.hidden = !el.dataset.tabs.split(' ').includes(pane);
        });
        $('rangeLabel').textContent = pretty(f.from) + ' – ' + pretty(f.to);
        $('filterSubject').value = f.subject ?? '';
        $('filterSection').value = f.section ?? '';
        $('filterPriority').value = f.priority ?? '';
        $('filterReason').value = f.reason ?? '';
        document.querySelectorAll('.range-presets button').forEach((b) => {
            const [from, to] = presetRange(b.dataset.preset);
            b.classList.toggle('active', from === f.from && to === f.to);
        });
    }

    /** The URL mirrors the tab on screen, so a copied link reopens that view. */
    function syncUrl() {
        const f = cur();
        const params = new URLSearchParams();
        const usesDates = activeTab !== 'at_risk';
        if (usesDates && !(f.from === DEFAULTS.from && f.to === DEFAULTS.to)) { params.set('from', f.from); params.set('to', f.to); }
        if (f.subject) { params.set('subject', f.subject); }
        if (f.section) { params.set('section', f.section); }
        if (!usesDates && f.priority) { params.set('priority', f.priority); }
        if (!usesDates && f.reason) { params.set('reason', f.reason); }
        const qs = params.toString();
        history.replaceState(null, '', location.pathname + (qs ? '?' + qs : '') + location.hash);
    }

    function load() {
        const tab = activeTab;
        const pane = $(activePane);
        const f = state[tab];
        if (inflight[tab]) { inflight[tab].abort(); }
        const controller = inflight[tab] = new AbortController();

        const params = new URLSearchParams({ tab, from: f.from, to: f.to });
        ['subject', 'section', 'priority', 'reason'].forEach((key) => { if (f[key]) { params.set(key, f[key]); } });

        pane.classList.add('dash-loading');
        $('filterStatus').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating…';
        syncControls();
        syncUrl();

        fetch(ENDPOINT + '?' + params.toString(), { headers: { 'Accept': 'application/json' }, signal: controller.signal })
            .then((res) => { if (!res.ok) { throw new Error(res.status); } return res.json(); })
            .then((data) => {
                // The server normalises bad input (e.g. a swapped or too-long
                // range), so adopt what it actually used.
                state[tab] = data.filters;
                if (tab === activeTab) { syncControls(); syncUrl(); }
                render(data.charts);
                $('filterStatus').textContent = '';
            })
            .catch((err) => {
                if (err.name === 'AbortError') { return; }
                $('filterStatus').innerHTML = '<i class="fas fa-triangle-exclamation" style="color:#b91c1c;"></i> Could not update — try again.';
            })
            .finally(() => {
                if (inflight[tab] === controller) { pane.classList.remove('dash-loading'); }
            });
    }

    function openRange(open) {
        rangePop.hidden = !open;
        rangeTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
            $('rangeFrom').value = cur().from;
            $('rangeTo').value = cur().to;
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
        b.addEventListener('click', () => {
            [cur().from, cur().to] = presetRange(b.dataset.preset);
            openRange(false);
            load();
        });
    });

    $('rangeApply').addEventListener('click', () => {
        const from = $('rangeFrom').value;
        const to = $('rangeTo').value;
        if (!from || !to) { $('rangeError').textContent = 'Pick both a start and an end date.'; return; }
        if (from > to) { $('rangeError').textContent = 'The start date must be on or before the end date.'; return; }
        if (daysBetween(from, to) > MAX_DAYS) { $('rangeError').textContent = 'Pick a range of one year or less.'; return; }
        cur().from = from;
        cur().to = to;
        openRange(false);
        load();
    });

    $('filterSubject').addEventListener('change', (e) => { cur().subject = e.target.value ? Number(e.target.value) : null; load(); });
    $('filterSection').addEventListener('change', (e) => { cur().section = e.target.value || null; load(); });
    $('filterPriority').addEventListener('change', (e) => { cur().priority = e.target.value || null; load(); });
    $('filterReason').addEventListener('change', (e) => { cur().reason = e.target.value || null; load(); });
    $('filterReset').addEventListener('click', () => { state[activeTab] = Object.assign({}, DEFAULTS); load(); });

    // The page arrives with Overview's data only; every other tab fetches its
    // own the first time it is opened, so a visit pays only for what it shows.
    const loaded = new Set();
    const ensureLoaded = () => { if (!loaded.has(activeTab)) { loaded.add(activeTab); load(); } };

    document.addEventListener('dash:tab', (e) => {
        activePane = e.detail;
        activeTab = TAB_KEYS[e.detail] || 'overview';
        openRange(false);
        $('filterStatus').textContent = '';
        syncControls();
        syncUrl();
        ensureLoaded();
    });

    const initial = @json($charts);
    Object.keys(RENDERERS).forEach((key) => { if (initial[key]) { loaded.add(key); } });
    syncControls();
    render(initial);
    ensureLoaded();
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

// Notify an inactive faculty member (Faculty tab).
const REMIND_URL = @json(route('chair.faculty.remind', ['id' => '__ID__']));
document.querySelectorAll('[data-remind-id]').forEach((btn) => btn.addEventListener('click', () => {
    const first = btn.dataset.remindFirst;
    document.getElementById('remindForm').action = REMIND_URL.replace('__ID__', btn.dataset.remindId);
    document.getElementById('remindName').textContent = btn.dataset.remindName;
    document.getElementById('remindLast').textContent = btn.dataset.remindLast;
    document.getElementById('remindTitle').value = 'We miss you on CPAce';
    document.getElementById('remindMessage').value =
        `Hi ${first},\n\nWe noticed you haven't signed in to CPAce in a while. Your students are relying on your classes, quizzes and test bank questions as they prepare for the CPALE.\n\nPlease sign in when you can to check on your classes. Let me know if you need any help.`;
    document.getElementById('remindModal').classList.add('open');
    // Start at the greeting so the whole message is visible.
    const message = document.getElementById('remindMessage');
    message.focus();
    message.setSelectionRange(0, 0);
    message.scrollTop = 0;
}));
function closeRemind() { document.getElementById('remindModal').classList.remove('open'); }
document.getElementById('remindModal').addEventListener('click', (event) => { if (event.target.id === 'remindModal') closeRemind(); });
document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeRemind(); });

// Recommended actions modal (opened from the bar on the Overview tab).
function openActions() {
    const modal = document.getElementById('actionsModal');
    modal.classList.add('open');
    modal.querySelector('.actions-modal-x').focus();
}
function closeActions() { document.getElementById('actionsModal').classList.remove('open'); }
document.getElementById('actionsModal').addEventListener('click', (event) => { if (event.target.id === 'actionsModal') closeActions(); });
document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeActions(); });
</script>

    @include('partials.alerts')
</body>
</html>
