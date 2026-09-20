<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Bank Coverage - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.chart-kit')
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; --orange:#f59e0b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:68px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:16px; }
        .topbar-left { display:flex; align-items:center; gap:12px; }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .page-title { font-size:26px; font-weight:700; color:#14283E; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:white; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }
        .btn-sm { padding:6px 12px; font-size:12px; }
        .card { background:white; border-radius:14px; padding:22px; border:1px solid #eee; }

        /* ── Card depth — the same two tiers the Program Chair Dashboard uses:
             the section and chart cards get this lighter lift, and the KPI row
             further down gets a darker one so it still reads as the headline.
             The border is dropped on a lifted card because an outline plus a
             shadow reads as two competing edges.
             Scoped to .main for the same reason as the KPI rules below —
             partials/chair-sidebar.blade.php is included after this sheet and
             defines its own bare .card. ── */
        .main .card, .main .viz-card {
            border-color:transparent;
            box-shadow:0 2px 6px rgba(15,10,10,.08), 0 10px 22px -10px rgba(15,10,10,.22);
            transition:transform .18s ease, box-shadow .18s ease;
        }
        .main .card:hover, .main .viz-card:hover {
            transform:translateY(-2px);
            box-shadow:0 4px 10px rgba(15,10,10,.1), 0 16px 30px -10px rgba(15,10,10,.3);
        }
        @media (prefers-reduced-motion:reduce) {
            .main .card, .main .viz-card, .main .stat-card { transition:none; }
            .main .card:hover, .main .viz-card:hover, .main .stat-card:hover { transform:none; }
        }
        .card-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; gap:12px; }
        .card-title { font-size:14px; font-weight:600; color:#1a1a1a; }
        .empty { text-align:center; padding:40px 20px; color:#aaa; }

        /* Tabs */
        .tab-bar { display:flex; gap:0; background:white; border-radius:12px; padding:4px; border:1px solid #eee; width:fit-content; flex-shrink:0; }
        .tab-btn { padding:9px 22px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; background:transparent; color:#888; transition:all .2s; display:flex; align-items:center; gap:7px; }
        .tab-btn:hover { color:#555; background:#f8f8fa; }
        .tab-btn.active { background:var(--primary); color:white; box-shadow:0 2px 8px rgba(123,29,29,0.25); }
        .tab-btn i { font-size:13px; }
        .tab-panel { display:none; }
        .tab-panel.active { display:block; }

        /* ── Tabs + their filter share one row, tabs pinned left and the
           filter pinned right, so the row reads as one scoped view instead
           of two stacked, unrelated-looking controls. ── */
        .tab-filter-row { display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; margin-bottom:18px; }

        /* ── Filter bar — no card wrapper: the controls sit straight on the
           page so the row is one control tall instead of a padded panel.
           Labels run inline beside their select rather than stacked above,
           which is what the base stylesheet's block-level <label> and
           full-width <select> would otherwise force. ── */
        .filter-card { margin:0; padding:0; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
        .filter-field { display:flex; align-items:center; gap:7px; }
        .filter-card label { font-size:10px; font-weight:700; color:#8a8a94; text-transform:uppercase; letter-spacing:.4px; margin:0; white-space:nowrap; }
        .filter-card select { width:auto; min-width:230px; padding:7px 10px; border:1.5px solid #e2e2e6; border-radius:8px; font:12px Poppins,sans-serif; color:#333; background:#fff; cursor:pointer; transition:border-color .15s; }
        .filter-card select:hover { border-color:#c7c7cf; }
        .filter-card select:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(123,29,29,.08); }
        /* ── KPI cards — same treatment as the other chair pages, sized down
           for a 5-up row: a pronounced shadow so the headline row lifts off
           the page, a bold dark title, an inline unit beside the number, and
           a dashed-border context line that reads the number rather than
           just restating its label.

           EVERY rule here is scoped to .main on purpose. partials/chair-
           sidebar.blade.php is included in <body>, i.e. AFTER this stylesheet,
           and it defines bare .stats-row / .stat-card / .stat-top / .stat-num /
           .stat-lbl / .stat-icon of its own. At equal specificity the later
           sheet wins, so unscoped rules here are silently dead — that is what
           forced this row back to the sidebar's repeat(4,1fr) (4 cards + 1
           stranded) and its flex .stat-top. .main lifts these to (0,2,0) so
           they actually apply. Do not drop the prefix. ── */

        /* All five stay on one row at every desktop width — the type and the
           icon scale with the viewport instead of the row breaking to a
           second line, which used to strand two cards underneath. */
        .main .stats-row { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:10px; margin-bottom:18px; }
        .main .stat-card {
            background:white; border:1px solid #eee; border-radius:12px;
            padding:15px clamp(12px,1.05vw,18px);
            display:flex; flex-direction:column; height:100%; min-width:0;
            box-shadow:0 4px 10px rgba(10,5,5,.14), 0 16px 32px -8px rgba(10,5,5,.34);
            transition:transform .18s ease, box-shadow .18s ease;
        }
        .main .stat-card:hover {
            transform:translateY(-2px);
            box-shadow:0 6px 14px rgba(10,5,5,.18), 0 22px 40px -8px rgba(10,5,5,.4);
        }
        /* The title gets the card's full width instead of competing with the
           icon for it, so "Curriculum Areas" stays on one line in a narrow
           card; the number and the icon then share the row below it.
           display:contents lets the markup's wrapper div drop out of the
           grid so its two children can be placed directly. */
        .main .stat-top { display:grid; grid-template-columns:minmax(0,1fr) auto; align-items:center; column-gap:8px; flex:1; }
        .main .stat-top > div:first-child { display:contents; }
        .main .stat-lbl { grid-column:1/-1; grid-row:1; font-size:clamp(10.5px,.8vw,12.5px); font-weight:700; color:#1a1a1a; letter-spacing:-.01em; margin-bottom:9px; }
        .main .stat-num { grid-column:1; grid-row:2; font-size:clamp(20px,1.5vw,26px); font-weight:700; color:#1a1a1a; line-height:1; margin-bottom:0; }
        .main .stat-unit { font-size:clamp(9.5px,.72vw,11px); font-weight:600; color:#aaa; vertical-align:middle; margin-left:2px; }
        .main .stat-context {
            font-size:clamp(9.5px,.7vw,10.5px); color:#999; margin-top:12px;
            padding-top:9px; border-top:1px dashed #eee; line-height:1.45;
        }
        .main .stat-context strong { color:#1a1a1a; font-weight:700; }
        .main .stat-icon { grid-column:2; grid-row:2; width:clamp(34px,2.6vw,46px); height:clamp(34px,2.6vw,46px); border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:clamp(14px,1.2vw,20px); flex-shrink:0; }
        .si-red { background:#fde8e8; color:var(--accent); }
        .si-green { background:#d1fae5; color:var(--green); }
        .si-blue { background:#dbeafe; color:var(--blue); }
        .si-orange { background:#fef3c7; color:var(--orange); }
        .si-grey { background:#f1f2f4; color:#8a8f98; }

        .coverage-wrap { overflow-x:auto; max-height:620px; overflow-y:auto; }
        .coverage-wrap table { min-width:960px; width:100%; border-collapse:collapse; }
        .coverage-wrap thead th { position:sticky; top:0; background:#fff; z-index:1; text-align:left; font-size:9.5px; color:#aaa; font-weight:600; padding:0 10px 12px; text-transform:uppercase; letter-spacing:.3px; }
        .coverage-wrap td { padding:12px 10px; font-size:11px; border-top:1px solid #f3f3f3; vertical-align:middle; }
        .topic-name { font-size:12px; font-weight:600; color:#333; }
        .topic-subject { font-size:9.5px; color:#aaa; margin-top:3px; }
        .count-main { font-size:14px; font-weight:700; color:#222; }
        .count-sub { font-size:9px; color:#aaa; }
        .coverage-bar { width:120px; height:6px; border-radius:5px; background:#eee; overflow:hidden; margin-top:6px; }
        .coverage-bar span { display:block; height:100%; background:var(--primary); border-radius:5px; }
        .status { display:inline-flex; align-items:center; gap:5px; padding:4px 9px; border-radius:15px; font-size:9.5px; font-weight:700; }
        .status.adequate { background:#d1fae5; color:#047857; }
        .status.thin { background:#fef3c7; color:#b45309; }
        .status.critical { background:#fee2e2; color:#b91c1c; }
        .legend { margin-top:13px; padding:11px 13px; border-radius:9px; background:#f8f8fa; font-size:10px; color:#888; line-height:1.6; }

        /* Below ~1100px five readable cards no longer fit side by side, so the
           row becomes a swipeable strip rather than breaking apart — it stays
           one row either way. The doubled class and !important are needed
           because partials/chair-sidebar.blade.php is included after this
           stylesheet and forces .stats-row to 1 / 2 / 1 columns at 900px,
           768px and 480px; without out-ranking those this strip never
           applies. The lifted shadow is toned down because a scroll
           container clips it vertically. */
        @media(max-width:1100px) {
            .main .stats-row {
                grid-template-columns:none !important; grid-auto-flow:column !important;
                grid-auto-columns:minmax(158px,1fr);
                overflow-x:auto; overscroll-behavior-x:contain;
                padding-bottom:6px; scroll-snap-type:x proximity;
            }
            .main .stat-card { scroll-snap-align:start; box-shadow:0 2px 6px rgba(10,5,5,.12), 0 8px 18px -6px rgba(10,5,5,.22); }
            .main .stat-card:hover { transform:none; box-shadow:0 2px 6px rgba(10,5,5,.12), 0 8px 18px -6px rgba(10,5,5,.22); }
        }
        @media(max-width:640px) { .tab-filter-row{flex-direction:column;align-items:stretch}.tab-bar{width:100%}.filter-card{width:100%}.filter-field{width:100%}.filter-card select{flex:1;width:auto;min-width:0} }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'analytics-coverage'])
<main class="main">
    <div class="topbar">
        <div class="topbar-left"><div><div class="page-title">Test Bank Coverage</div><div class="page-sub">Find the curriculum areas that need more active questions and a better difficulty mix.</div></div></div>
        <div class="topbar-right">@include('partials.topbar-actions')</div>
    </div>

    {{-- Tabs on the left, the filter that scopes both of them on the right —
         same row, so the row reads as one scoped view instead of two
         stacked, unrelated-looking controls. --}}
    <div class="tab-filter-row">
        <div class="tab-bar" role="tablist">
            <button class="tab-btn active" role="tab" aria-selected="true" onclick="switchTab('overview', this)"><i class="fas fa-table-columns"></i> Overview</button>
            <button class="tab-btn" role="tab" aria-selected="false" onclick="switchTab('visualization', this)"><i class="fas fa-chart-line"></i> Visualization</button>
        </div>

        <form class="filter-card" method="GET">
            <div class="filter-field">
                <label for="subject">Subject</label>
                <select name="subject" id="subject"><option value="">All CPALE subjects</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected($selectedSubject === $subject->id)>{{ $subject->code }} — {{ $subject->name }}</option>@endforeach</select>
            </div>
            <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-filter"></i> Apply</button>
            @if($selectedSubject)<a class="btn btn-ghost btn-sm" href="{{ route('chair.analytics.test-bank-coverage') }}">Clear</a>@endif
        </form>
    </div>

    @php
        // Context figures for the KPI cards — each number gets a line that
        // reads it (what it means, or what it asks the chair to do) rather
        // than a static caption that only restates the label.
        $avgPerArea = $stats['areas'] > 0 ? round($stats['active'] / $stats['areas'], 1) : 0;
        $thinPct = $stats['areas'] > 0 ? (int) round($stats['thin'] / $stats['areas'] * 100) : 0;
        $criticalPct = $stats['areas'] > 0 ? (int) round($stats['critical'] / $stats['areas'] * 100) : 0;

        $statCards = [
            [
                'Curriculum Areas', number_format($stats['areas']), 'Areas', 'si-blue', 'fa-sitemap',
                $stats['subtopics'] > 0
                    ? '<strong>'.number_format($stats['subtopics']).' nested subtopics</strong> sit inside them.'
                    : '<strong>Flat structure</strong> — no nested subtopics mapped yet.',
            ],
            [
                'Active Questions', number_format($stats['active']), 'Live', 'si-green', 'fa-circle-question',
                $stats['inactive'] > 0
                    ? '<strong>'.$avgPerArea.' per area</strong> live · <strong>'.number_format($stats['inactive']).'</strong> still draft.'
                    : '<strong>'.$avgPerArea.' per area</strong> against a '.$target.'-question target.',
            ],
            [
                'Overall Coverage', $stats['coverage'].'%', 'of target', 'si-grey', 'fa-gauge',
                $stats['gap'] > 0
                    ? '<strong style="color:var(--accent);">'.number_format($stats['gap']).' more questions</strong> to reach '.$target.' per area.'
                    : '<strong style="color:#047857;">Target met</strong> across every curriculum area.',
            ],
            [
                'Thin Areas', number_format($stats['thin']), 'Areas', 'si-orange', 'fa-battery-quarter',
                $stats['thin'] > 0
                    ? '<strong>'.$thinPct.'% of areas</strong> hold only 1–'.($target - 1).' questions.'
                    : '<strong style="color:#047857;">None thin</strong> — every area is at target.',
            ],
            [
                'Critical Areas', number_format($stats['critical']), 'Areas', 'si-red', 'fa-triangle-exclamation',
                $stats['critical'] > 0
                    ? '<strong style="color:var(--accent);">'.$criticalPct.'% of areas</strong> cannot be quizzed at all yet.'
                    : '<strong style="color:#047857;">Every area</strong> has at least one live question.',
            ],
        ];
    @endphp

    {{-- ═══ OVERVIEW TAB ═══ --}}
    <div id="tab-overview" class="tab-panel active">
        <div class="stats-row">
            @foreach($statCards as [$label, $value, $unit, $tone, $icon, $context])
                <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">{{ $label }}</div><div class="stat-num">{{ $value }} <span class="stat-unit">{{ $unit }}</span></div></div><div class="stat-icon {{ $tone }}"><i class="fas {{ $icon }}"></i></div></div><div class="stat-context">{!! $context !!}</div></div>
            @endforeach
        </div>

        <section class="card" style="margin-bottom:18px;">
            <div class="card-head"><span class="card-title">Coverage by Subject</span><span style="font-size:10px;color:#aaa;">Target: {{ $target }} active questions per curriculum area</span></div>
            <table class="viz-table">
                <thead><tr><th>Subject</th><th class="num">Areas</th><th class="num">Adequate</th><th class="num">Thin</th><th class="num">Critical</th><th class="num">Active</th><th class="num">Coverage</th><th class="num">Questions still needed</th></tr></thead>
                <tbody>
                @foreach($rollup as $row)
                    <tr>
                        <td><span class="subj-badge b-{{ strtolower($row['code']) }}">{{ $row['code'] }}</span> {{ $row['name'] }}</td>
                        <td class="num">{{ $row['areas'] }}</td>
                        <td class="num">{{ $row['adequate'] }}</td>
                        <td class="num">{{ $row['thin'] }}</td>
                        <td class="num">{{ $row['critical'] }}</td>
                        <td class="num">{{ number_format($row['active']) }}</td>
                        <td class="num"><strong>{{ $row['coverage'] }}%</strong></td>
                        <td class="num">{{ number_format($row['gap']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>

        <section class="card">
            <div class="card-head"><span class="card-title">Coverage by Curriculum Area</span><span style="font-size:10px;color:#aaa;">{{ number_format($stats['areas']) }} areas · questions from nested subtopics are counted in their area</span></div>
            <div class="coverage-wrap">
                <table>
                    <thead><tr><th>Curriculum area</th><th>Active Coverage</th><th>Total</th><th>Easy</th><th>Moderate</th><th>Difficult</th><th>Last Added</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($coverage as $topic)
                        <tr>
                            <td><div class="topic-name">{{ $topic['name'] }}</div><div class="topic-subject">{{ $topic['subject_code'] }} · {{ $topic['subject_name'] }}@if($topic['subtopics']) · {{ $topic['subtopics'] }} subtopics @endif</div></td>
                            <td><div class="count-main">{{ $topic['active'] }} / {{ $target }}</div><div class="coverage-bar"><span style="width:{{ $topic['coverage'] }}%"></span></div>@if($topic['gap'])<div class="count-sub">Needs {{ $topic['gap'] }} more</div>@endif</td>
                            <td><span class="count-main">{{ $topic['total'] }}</span></td>
                            <td>{{ $topic['easy'] }}</td><td>{{ $topic['moderate'] }}</td><td>{{ $topic['difficult'] }}</td>
                            <td><span style="font-size:10.5px;color:#666;">{{ $topic['last_added'] ? $topic['last_added']->diffForHumans() : 'Never' }}</span></td>
                            <td><span class="status {{ $topic['status'] }}"><i class="fas {{ $topic['status'] === 'adequate' ? 'fa-check' : 'fa-triangle-exclamation' }}"></i>{{ ucfirst($topic['status']) }}</span></td>
                        </tr>
                    @empty<tr><td colspan="8"><div class="empty">No active curriculum areas are available.</div></td></tr>@endforelse
                    </tbody>
                </table>
            </div>
            <div class="legend"><strong>Coverage rule:</strong> zero active questions is critical, 1–{{ $target - 1 }} is thin, and {{ $target }} or more is adequate. Coverage is measured per top-level curriculum area — a question written against a nested subtopic counts towards the area that contains it. Difficulty totals include both common naming styles (moderate/medium and difficult/hard).</div>
        </section>
    </div>

    {{-- ═══ VISUALIZATION TAB ═══ --}}
    <div id="tab-visualization" class="tab-panel">
        <div class="stats-row">
            @foreach($statCards as [$label, $value, $unit, $tone, $icon, $context])
                <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">{{ $label }}</div><div class="stat-num">{{ $value }} <span class="stat-unit">{{ $unit }}</span></div></div><div class="stat-icon {{ $tone }}"><i class="fas {{ $icon }}"></i></div></div><div class="stat-context">{!! $context !!}</div></div>
            @endforeach
        </div>

        <div class="viz-grid-layout">
            <div class="viz-card">
                <h4><i class="fas fa-chart-pie"></i> Coverage Status of Curriculum Areas</h4>
                <div class="viz-sub">One slice per state; every area falls in exactly one.</div>
                <div class="chart-canvas-wrap"><canvas id="vizStatusDonut"></canvas></div>
            </div>

            <div class="viz-card">
                <h4><i class="fas fa-gauge"></i> Coverage Reached per Subject</h4>
                <div class="viz-sub">Active questions as a share of that subject's full {{ $target }}-per-area target.</div>
                <div class="chart-canvas-wrap"><canvas id="vizSubjectCoverage"></canvas></div>
            </div>

            <div class="viz-card">
                <h4><i class="fas fa-layer-group"></i> Difficulty Mix per Subject</h4>
                <div class="viz-sub">A bank weighted to one band trains the cohort unevenly.</div>
                <div class="chart-canvas-wrap h-md"><canvas id="vizDiffStack"></canvas></div>
            </div>

            <div class="viz-card">
                <h4><i class="fas fa-toggle-on"></i> Active vs Inactive Questions</h4>
                <div class="viz-sub">Drafted questions do not reach students until they are activated.</div>
                <div class="chart-canvas-wrap h-md"><canvas id="vizActiveStack"></canvas></div>
            </div>

            {{-- "Largest Coverage Gaps" was removed here. It ranked the 12
                 areas furthest from target, but an empty area's gap is always
                 the full target, so once most areas are empty the ranking is a
                 mass tie (71 of 96 areas sat at 25) and the chart drew 12
                 identical bars picked arbitrarily out of that tie — while the
                 areas that actually varied, gap 9–15, never appeared at all.
                 The same information is carried honestly by the Critical Areas
                 KPI, the status donut, and the full area table on Overview. --}}

            <div class="viz-card full">
                <h4><i class="fas fa-chart-line"></i> Test Bank Growth</h4>
                <div class="viz-sub">Questions added per month. Compare against the {{ number_format($stats['gap']) }}-question outstanding gap.</div>
                <div class="chart-canvas-wrap h-sm"><canvas id="vizGrowth"></canvas></div>
            </div>
        </div>

        <div class="legend" style="margin-top:16px;">Every value plotted here is also listed as a table on the <strong>Overview</strong> tab.</div>
    </div>
</main>

<script>
(function () {
    const P = Viz.palette;

    const rollup = @json($rollup);
    const growth = @json($growth->values());
    const status = { adequate: {{ $stats['adequate'] }}, thin: {{ $stats['thin'] }}, critical: {{ $stats['critical'] }} };

    const pluck = (rows, key) => rows.map((row) => row[key]);

    /* ── Coverage status (status colours, labelled in the legend) ───────── */
    Viz.chart('vizStatusDonut', {
        type: 'doughnut',
        data: {
            labels: ['Adequate ({{ $target }}+)', 'Thin (1–{{ $target - 1 }})', 'Critical (none)'],
            datasets: [{
                data: [status.adequate, status.thin, status.critical],
                backgroundColor: [P.good, P.warn, P.crit],
                borderWidth: 2, borderColor: P.surface,
            }],
        },
        options: {
            cutout: '58%',
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: (c) => {
                    const total = status.adequate + status.thin + status.critical || 1;
                    return c.raw + ' area' + (c.raw === 1 ? '' : 's') + ' (' + Math.round(c.raw / total * 100) + '%)';
                } } },
            },
        },
    });

    /* ── Coverage reached per subject ───────────────────────────────────── */
    Viz.chart('vizSubjectCoverage', {
        type: 'bar',
        data: {
            labels: pluck(rollup, 'code'),
            datasets: [Viz.bar({ label: 'Coverage', data: pluck(rollup, 'coverage'), backgroundColor: P.s1 })],
        },
        options: {
            scales: { y: Viz.percentAxis(), x: Viz.catAxis() },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => {
                    const row = rollup[c.dataIndex];
                    return row.coverage + '% · ' + row.active.toLocaleString() + ' active across ' + row.areas + ' areas · ' + row.gap.toLocaleString() + ' still needed';
                } } },
            },
        },
    });

    /* ── Difficulty mix per subject (ordered bands → ordinal ramp) ──────── */
    Viz.chart('vizDiffStack', {
        type: 'bar',
        data: {
            labels: pluck(rollup, 'code'),
            datasets: [
                Viz.stacked({ label: 'Easy', data: pluck(rollup, 'easy'), backgroundColor: P.ordinal[0] }),
                Viz.stacked({ label: 'Moderate', data: pluck(rollup, 'moderate'), backgroundColor: P.ordinal[1] }),
                Viz.stacked({ label: 'Difficult', data: pluck(rollup, 'difficult'), backgroundColor: P.ordinal[2] }),
            ],
        },
        options: {
            scales: { x: Viz.catAxis({ stacked: true }), y: Viz.countAxis({ stacked: true }) },
            plugins: { legend: { position: 'bottom' }, tooltip: { mode: 'index' } },
        },
    });

    /* ── Active vs inactive questions ───────────────────────────────────── */
    Viz.chart('vizActiveStack', {
        type: 'bar',
        data: {
            labels: pluck(rollup, 'code'),
            datasets: [
                Viz.stacked({ label: 'Active', data: pluck(rollup, 'active'), backgroundColor: P.s1 }),
                Viz.stacked({ label: 'Inactive / draft', data: pluck(rollup, 'inactive'), backgroundColor: P.s2 }),
            ],
        },
        options: {
            scales: { x: Viz.catAxis({ stacked: true }), y: Viz.countAxis({ stacked: true }) },
            plugins: { legend: { position: 'bottom' }, tooltip: { mode: 'index' } },
        },
    });

    /* ── Bank growth ────────────────────────────────────────────────────── */
    Viz.chart('vizGrowth', {
        type: 'line',
        data: {
            labels: pluck(growth, 'label'),
            datasets: [Viz.line({ label: 'Questions added', data: pluck(growth, 'added'), borderColor: P.s1, backgroundColor: 'rgba(163,43,43,.08)', pointBackgroundColor: P.s1, fill: true })],
        },
        options: {
            interaction: { mode: 'index', intersect: false },
            scales: { y: Viz.countAxis(), x: Viz.catAxis() },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { afterBody: (items) => growth[items[0].dataIndex].active + ' of them are active' } },
            },
        },
    });
})();
</script>

    @include('partials.alerts')
</body>
</html>
