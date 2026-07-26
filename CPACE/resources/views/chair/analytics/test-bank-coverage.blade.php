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
        .page-title { font-size:26px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:white; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }
        .btn-sm { padding:6px 12px; font-size:12px; }
        .card { background:white; border-radius:14px; padding:22px; border:1px solid #eee; }
        .card-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; gap:12px; }
        .card-title { font-size:14px; font-weight:600; color:#1a1a1a; }
        .empty { text-align:center; padding:40px 20px; color:#aaa; }

        /* Tabs */
        .tab-bar { display:flex; gap:0; margin-bottom:18px; background:white; border-radius:12px; padding:4px; border:1px solid #eee; width:fit-content; }
        .tab-btn { padding:9px 22px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; background:transparent; color:#888; transition:all .2s; display:flex; align-items:center; gap:7px; }
        .tab-btn:hover { color:#555; background:#f8f8fa; }
        .tab-btn.active { background:var(--primary); color:white; box-shadow:0 2px 8px rgba(123,29,29,0.25); }
        .tab-btn i { font-size:13px; }
        .tab-panel { display:none; }
        .tab-panel.active { display:block; }

        .filter-card { padding:13px 16px; margin-bottom:18px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
        .filter-card label { font-size:10.5px; font-weight:600; color:#777; }
        .filter-card select { min-width:230px; padding:8px 11px; border:1px solid #ddd; border-radius:8px; font:11px Poppins,sans-serif; color:#444; background:#fff; }
        .stats-row { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; margin-bottom:18px; }
        .stat-card { background:white; border:1px solid #eee; border-radius:12px; padding:15px; }
        .stat-top { display:flex; justify-content:space-between; align-items:flex-start; }
        .stat-lbl { font-size:10px; color:#888; font-weight:600; }
        .stat-num { font-size:24px; font-weight:700; color:#222; margin-top:7px; }
        .stat-sub { font-size:9.5px; color:#aaa; margin-top:4px; }
        .stat-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; }
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

        @media(max-width:1250px) { .stats-row{grid-template-columns:repeat(3,1fr)} }
        @media(max-width:640px) { .stats-row{grid-template-columns:1fr}.filter-card select{min-width:0;width:100%} }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'analytics-coverage'])
<main class="main">
    <div class="topbar">
        <div class="topbar-left"><div><div class="page-title">Test Bank Coverage</div><div class="page-sub">Find the curriculum areas that need more active questions and a better difficulty mix.</div></div></div>
        <div class="topbar-right">@include('partials.topbar-actions')</div>
    </div>

    {{-- One filter row, above everything it scopes. --}}
    <form class="card filter-card" method="GET">
        <label for="subject">Subject</label>
        <select name="subject" id="subject"><option value="">All CPALE subjects</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected($selectedSubject === $subject->id)>{{ $subject->code }} — {{ $subject->name }}</option>@endforeach</select>
        <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-filter"></i> Apply</button>
        @if($selectedSubject)<a class="btn btn-ghost btn-sm" href="{{ route('chair.analytics.test-bank-coverage') }}">Clear</a>@endif
    </form>

    <div class="tab-bar" role="tablist">
        <button class="tab-btn active" role="tab" aria-selected="true" onclick="switchTab('overview', this)"><i class="fas fa-table-columns"></i> Overview</button>
        <button class="tab-btn" role="tab" aria-selected="false" onclick="switchTab('visualization', this)"><i class="fas fa-chart-line"></i> Visualization</button>
    </div>

    @php
        $statCards = [
            ['Curriculum Areas', number_format($stats['areas']), $stats['subtopics'].' nested subtopics', 'si-blue', 'fa-sitemap'],
            ['Active Questions', number_format($stats['active']), $stats['inactive'].' inactive / draft', 'si-green', 'fa-circle-question'],
            ['Overall Coverage', $stats['coverage'].'%', 'Of the '.number_format($stats['areas'] * $target).'-question target', 'si-grey', 'fa-gauge'],
            ['Thin Areas', number_format($stats['thin']), '1–'.($target - 1).' active questions', 'si-orange', 'fa-battery-quarter'],
            ['Critical Areas', number_format($stats['critical']), 'No active questions at all', 'si-red', 'fa-triangle-exclamation'],
        ];
    @endphp

    {{-- ═══ OVERVIEW TAB ═══ --}}
    <div id="tab-overview" class="tab-panel active">
        <div class="stats-row">
            @foreach($statCards as [$label, $value, $sub, $tone, $icon])
                <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">{{ $label }}</div><div class="stat-num">{{ $value }}</div><div class="stat-sub">{{ $sub }}</div></div><div class="stat-icon {{ $tone }}"><i class="fas {{ $icon }}"></i></div></div></div>
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
            @foreach($statCards as [$label, $value, $sub, $tone, $icon])
                <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">{{ $label }}</div><div class="stat-num">{{ $value }}</div><div class="stat-sub">{{ $sub }}</div></div><div class="stat-icon {{ $tone }}"><i class="fas {{ $icon }}"></i></div></div></div>
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

            <div class="viz-card full">
                <h4><i class="fas fa-arrow-down-wide-short"></i> Largest Coverage Gaps</h4>
                <div class="viz-sub">The {{ $gaps->count() }} curriculum areas furthest from the {{ $target }}-question target — the authoring backlog, in order.</div>
                <div class="chart-canvas-wrap h-xl"><canvas id="vizGapBar"></canvas></div>
            </div>

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
    const gaps   = @json($gaps);
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

    /* ── Largest coverage gaps ──────────────────────────────────────────── */
    if (gaps.length) {
        Viz.chart('vizGapBar', {
            type: 'bar',
            data: {
                labels: gaps.map((area) => area.subject_code + ' · ' + area.name),
                datasets: [Viz.bar({ label: 'Questions needed', data: pluck(gaps, 'gap'), backgroundColor: P.s1, maxBarThickness: 18 })],
            },
            options: {
                indexAxis: 'y',
                layout: { padding: { right: 40 } },
                scales: {
                    x: Viz.countAxis({ title: { display: true, text: 'Questions still needed', color: P.muted } }),
                    y: Viz.catAxis({ ticks: { color: P.ink, padding: 6, font: { size: 10 } } }),
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (c) => {
                        const area = gaps[c.dataIndex];
                        return area.gap === 0
                            ? 'Fully covered'
                            : area.gap + ' more needed (' + area.active + ' of {{ $target }} written)';
                    } } },
                },
            },
            plugins: [Viz.endLabels()],
        });
    } else {
        document.getElementById('vizGapBar').outerHTML = '<div class="viz-empty">Every curriculum area has met the target.</div>';
    }

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
</body>
</html>
