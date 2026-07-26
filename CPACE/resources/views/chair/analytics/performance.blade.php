<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class-Level Performance - CPACE</title>
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
        .analytics-layout { display:grid; grid-template-columns:minmax(0,1.3fr) minmax(310px,.7fr); gap:18px; }
        .metric-row { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin-bottom:18px; }
        .metric { background:#fff; border:1px solid #eee; border-radius:12px; padding:15px; }
        .metric-label { font-size:10px; color:#888; font-weight:600; }
        .metric-value { font-size:24px; font-weight:700; color:#222; margin-top:7px; }
        .metric-note { font-size:9.5px; color:#aaa; margin-top:4px; }
        .subject-row { display:grid; grid-template-columns:minmax(185px,1fr) minmax(160px,1.3fr) 72px 78px 90px; gap:13px; align-items:center; padding:13px 0; border-top:1px solid #f3f3f3; }
        .subject-row.head { border:0; padding-top:0; font-size:9.5px; color:#aaa; text-transform:uppercase; letter-spacing:.3px; }
        .subject-name { font-size:11px; color:#555; margin-left:7px; }
        .bar { height:7px; border-radius:5px; background:#f0f1f3; overflow:hidden; }
        .bar > span { display:block; height:100%; border-radius:5px; background:var(--primary); }
        .accuracy { font-size:13px; font-weight:700; color:#222; }
        .delta { font-size:11px; font-weight:700; }
        .delta.up { color:#047857; } .delta.down { color:#b91c1c; } .delta.na { color:#bbb; font-weight:500; }
        .small-meta { font-size:9.5px; color:#aaa; }
        .trend-chart { height:190px; display:flex; align-items:end; gap:10px; padding:18px 6px 0; border-bottom:1px solid #eee; }
        .trend-col { flex:1; min-width:25px; height:100%; display:flex; flex-direction:column; justify-content:end; align-items:center; gap:5px; }
        .trend-value { font-size:9px; font-weight:600; color:#777; }
        .trend-bar { width:min(32px,70%); min-height:2px; background:linear-gradient(180deg,var(--accent),#f49a9a); border-radius:6px 6px 0 0; }
        .trend-label { font-size:8.5px; color:#999; white-space:nowrap; }
        .bands { display:grid; grid-template-columns:repeat(3,1fr); gap:9px; margin-top:15px; }
        .band { padding:12px; border-radius:10px; text-align:center; background:#f8f8fa; }
        .band strong { display:block; font-size:20px; color:#222; }
        .band span { font-size:9px; color:#888; }
        .band.ready { background:#ecfdf5; } .band.ready strong { color:#047857; }
        .band.risk { background:#fef2f2; } .band.risk strong { color:#b91c1c; }
        .method-note { font-size:10px; line-height:1.7; color:#888; background:#f8f8fa; border-radius:10px; padding:12px 14px; margin-top:14px; }
        .cohort-strip { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin-bottom:18px; }
        .cohort { background:#fff; border:1px solid #eee; border-radius:12px; padding:13px 15px; }
        .cohort-num { font-size:19px; font-weight:700; color:#222; }
        .cohort-lbl { font-size:9.5px; color:#999; font-weight:600; margin-top:3px; }

        @media(max-width:1050px) { .analytics-layout{grid-template-columns:1fr}.metric-row{grid-template-columns:repeat(2,1fr)}.cohort-strip{grid-template-columns:repeat(2,1fr)} }
        @media(max-width:640px) { .metric-row{grid-template-columns:1fr}.cohort-strip{grid-template-columns:1fr}.subject-row{grid-template-columns:1fr 65px}.subject-row .bar,.subject-row .small-meta,.subject-row .delta,.subject-row.head{display:none}.filter-card select{min-width:0;width:100%} }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'analytics-performance'])
<main class="main">
    <div class="topbar">
        <div class="topbar-left"><div><div class="page-title">Class-Level Performance</div><div class="page-sub">Aggregated accuracy and board readiness for all active students.</div></div></div>
        <div class="topbar-right">@include('partials.topbar-actions')</div>
    </div>

    {{-- One filter row, above everything it scopes: both tabs read the same slice. --}}
    <form class="card filter-card" method="GET">
        <label for="subject">Subject</label>
        <select name="subject" id="subject">
            <option value="">All CPALE subjects</option>
            @foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected($selectedSubject === $subject->id)>{{ $subject->code }} — {{ $subject->name }}</option>@endforeach
        </select>
        <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-filter"></i> Apply</button>
        @if($selectedSubject)<a class="btn btn-ghost btn-sm" href="{{ route('chair.analytics.performance') }}">Clear</a>@endif
    </form>

    <div class="tab-bar" role="tablist">
        <button class="tab-btn active" role="tab" aria-selected="true" onclick="switchTab('overview', this)"><i class="fas fa-table-columns"></i> Overview</button>
        <button class="tab-btn" role="tab" aria-selected="false" onclick="switchTab('visualization', this)"><i class="fas fa-chart-line"></i> Visualization</button>
    </div>

    @php
        $metrics = [
            ['Class-Level Accuracy', $report['overall_accuracy'] === null ? '—' : $report['overall_accuracy'].'%', 'Weighted across all student attempts'],
            ['Participating Students', $report['participating_students'], 'With at least one recorded attempt'],
            ['Total Attempts', number_format($report['total_attempts']), 'Across the selected scope'],
            ['Board Ready', $report['readiness']['readiness_rate'] === null ? '—' : $report['readiness']['readiness_rate'].'%', $report['readiness']['ready'].' of '.$report['readiness']['eligible'].' eligible'],
        ];
        $cohort = $report['cohort'];
    @endphp

    <div id="tab-overview" class="tab-panel active">
    <div class="metric-row">
        @foreach($metrics as [$label, $value, $note])
            <div class="metric"><div class="metric-label">{{ $label }}</div><div class="metric-value">{{ $value }}</div><div class="metric-note">{{ $note }}</div></div>
        @endforeach
    </div>

    <div class="cohort-strip">
        <div class="cohort"><div class="cohort-num">{{ $cohort['enrolled'] }}</div><div class="cohort-lbl">Enrolled students</div></div>
        <div class="cohort"><div class="cohort-num">{{ $cohort['practising'] }}</div><div class="cohort-lbl">Practised in last 7 days</div></div>
        <div class="cohort"><div class="cohort-num">{{ $cohort['never_practised'] }}</div><div class="cohort-lbl">Never practised</div></div>
        <div class="cohort"><div class="cohort-num">{{ $cohort['alumni'] }} / {{ $cohort['shifted'] }}</div><div class="cohort-lbl">Alumni / shifted out</div></div>
    </div>

    <div class="analytics-layout">
        <div>
            <section class="card">
                <div class="card-head"><span class="card-title">Subject-by-Subject Accuracy</span><span class="small-meta">Weighted class-level results</span></div>
                <div class="subject-row head"><span>Subject</span><span>Accuracy</span><span>Score</span><span>vs pass</span><span>Participation</span></div>
                @forelse($report['subjects'] as $subject)
                    <div class="subject-row">
                        <div><span class="subj-badge b-{{ strtolower($subject['code']) }}">{{ $subject['code'] }}</span><span class="subject-name">{{ $subject['name'] }}</span></div>
                        <div class="bar"><span style="width:{{ $subject['accuracy'] ?? 0 }}%"></span></div>
                        <div class="accuracy">{{ $subject['accuracy'] === null ? '—' : $subject['accuracy'].'%' }}</div>
                        <div class="delta {{ $subject['gap_to_threshold'] === null ? 'na' : ($subject['gap_to_threshold'] >= 0 ? 'up' : 'down') }}">
                            {{ $subject['gap_to_threshold'] === null ? '—' : ($subject['gap_to_threshold'] > 0 ? '+' : '').$subject['gap_to_threshold'].' pts' }}
                        </div>
                        <div><div class="small-meta">{{ $subject['students'] }} students</div><div class="small-meta">{{ number_format($subject['attempts']) }} attempts</div></div>
                    </div>
                @empty<div class="empty">No subjects are available.</div>@endforelse
                <div class="method-note">“vs pass” is the distance between class accuracy and that subject's own passing threshold (currently {{ $report['subjects']->first()['threshold'] ?? 75 }}% by default). Negative means the cohort is below the mark for that subject.</div>
            </section>

            <section class="card" id="readiness-trend" style="margin-top:18px;">
                <div class="card-head"><span class="card-title">Board Readiness Trend</span><span class="small-meta">Cumulative, ending each week</span></div>
                <div class="trend-chart" role="img" aria-label="Eight-week readiness trend">
                    @foreach($report['trend'] as $point)
                        <div class="trend-col" title="{{ $point['ready'] }} of {{ $point['eligible'] }} eligible students">
                            <span class="trend-value">{{ $point['rate'] }}%</span>
                            <span class="trend-bar" style="height:{{ max(2, $point['rate']) }}%"></span>
                            <span class="trend-label">{{ $point['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="card" style="margin-top:18px;">
                <div class="card-head"><span class="card-title">Weekly Engagement</span><span class="small-meta">Not cumulative — the leading indicator</span></div>
                <table class="viz-table">
                    <thead><tr><th>Week of</th><th class="num">Active students</th><th class="num">Quizzes</th><th class="num">Items</th><th class="num">Accuracy</th><th class="num">Hours</th></tr></thead>
                    <tbody>
                    @foreach($report['engagement'] as $week)
                        <tr>
                            <td>{{ $week['label'] }}</td>
                            <td class="num">{{ $week['active_students'] }}</td>
                            <td class="num">{{ $week['quizzes'] }}</td>
                            <td class="num">{{ number_format($week['items']) }}</td>
                            <td class="num">{{ $week['accuracy'] === null ? '—' : $week['accuracy'].'%' }}</td>
                            <td class="num">{{ $week['hours'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </section>

            <section class="card" style="margin-top:18px;">
                <div class="card-head"><span class="card-title">Weakest Topics</span><span class="small-meta">Lowest accuracy, {{ \App\Services\ChairAnalyticsService::TOPIC_MIN_ATTEMPTS }}+ attempts</span></div>
                <table class="viz-table">
                    <thead><tr><th>Topic</th><th>Subject</th><th class="num">Accuracy</th><th class="num">Attempts</th><th class="num">Students</th></tr></thead>
                    <tbody>
                    @forelse($report['weak_topics'] as $topic)
                        <tr>
                            <td>{{ $topic['name'] }}</td>
                            <td>{{ $topic['subject_code'] }}</td>
                            <td class="num"><strong>{{ $topic['accuracy'] }}%</strong></td>
                            <td class="num">{{ number_format($topic['attempts']) }}</td>
                            <td class="num">{{ $topic['students'] }}</td>
                        </tr>
                    @empty<tr><td colspan="5"><div class="empty">No topic has reached {{ \App\Services\ChairAnalyticsService::TOPIC_MIN_ATTEMPTS }} recorded attempts yet.</div></td></tr>@endforelse
                    </tbody>
                </table>
            </section>
        </div>

        <div>
            <section class="card">
                <div class="card-head"><span class="card-title">Readiness Bands</span></div>
                <div class="bands">
                    <div class="band ready"><strong>{{ $report['readiness']['ready'] }}</strong><span>Ready</span></div>
                    <div class="band"><strong>{{ $report['readiness']['developing'] }}</strong><span>Developing</span></div>
                    <div class="band risk"><strong>{{ $report['readiness']['at_risk'] }}</strong><span>At risk</span></div>
                </div>
                <div class="method-note">Ready requires at least {{ \App\Services\ChairAnalyticsService::READY_ATTEMPTS }} completed items, {{ \App\Services\ChairAnalyticsService::READY_ACCURACY }}% accuracy, and—when viewing all subjects—activity in at least {{ \App\Services\ChairAnalyticsService::READY_SUBJECTS }} subjects. Students need {{ \App\Services\ChairAnalyticsService::DEVELOPING_ATTEMPTS }} items to be included in the measured class.</div>
            </section>

            <section class="card" id="pass-projection" style="margin-top:18px;">
                <div class="card-head"><span class="card-title">Predicted Pass Rate</span><i class="fas fa-graduation-cap" style="color:var(--accent)"></i></div>
                <div style="text-align:center;padding:12px 0 6px;"><div style="font-size:42px;font-weight:700;color:#222;">{{ $report['readiness']['pass_projection'] === null ? '—' : $report['readiness']['pass_projection'].'%' }}</div><div class="small-meta">Readiness-based class-level projection</div></div>
                <div class="method-note"><strong>Planning estimate only.</strong> The projection counts ready students fully and developing students at 50%. It is not an official board-exam prediction and becomes more useful as students complete more practice.</div>
                @if($report['readiness']['insufficient'] > 0)<div class="method-note"><i class="fas fa-circle-info"></i> {{ $report['readiness']['insufficient'] }} active student{{ $report['readiness']['insufficient'] === 1 ? '' : 's' }} currently have insufficient activity for the projection.</div>@endif
            </section>

            <section class="card" style="margin-top:18px;">
                <div class="card-head"><span class="card-title">Score Distribution</span></div>
                <table class="viz-table">
                    <thead><tr><th>Accuracy band</th><th class="num">Students</th></tr></thead>
                    <tbody>
                    @foreach($report['distribution'] as $band)
                        <tr><td>{{ $band['label'] }}</td><td class="num">{{ $band['students'] }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </section>

            <section class="card" style="margin-top:18px;">
                <div class="card-head"><span class="card-title">Accuracy by Difficulty</span></div>
                <table class="viz-table">
                    <thead><tr><th>Difficulty</th><th class="num">Answered</th><th class="num">Accuracy</th></tr></thead>
                    <tbody>
                    @foreach($report['difficulty'] as $level)
                        <tr><td>{{ $level['label'] }}</td><td class="num">{{ number_format($level['answered']) }}</td><td class="num">{{ $level['accuracy'] === null ? '—' : $level['accuracy'].'%' }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="method-note">Accuracy should fall as difficulty rises. If it does not, the difficulty labels in the test bank need review.</div>
            </section>
        </div>
    </div>
    </div><!-- /tab-overview -->

    <div id="tab-visualization" class="tab-panel">
        <div class="metric-row">
            @foreach($metrics as [$label, $value, $note])
                <div class="metric"><div class="metric-label">{{ $label }}</div><div class="metric-value">{{ $value }}</div><div class="metric-note">{{ $note }}</div></div>
            @endforeach
        </div>

        <div class="viz-grid-layout">
            <div class="viz-card full">
                <h4><i class="fas fa-bullseye"></i> Subject Accuracy vs Passing Threshold</h4>
                <div class="viz-sub">Bar length is class accuracy; the vertical rule on each bar is that subject's own passing threshold.</div>
                <div class="chart-canvas-wrap h-md"><canvas id="vizSubjectAccuracy"></canvas></div>
            </div>

            <div class="viz-card">
                <h4><i class="fas fa-chart-pie"></i> Readiness Bands</h4>
                <div class="viz-sub">Measured students only ({{ \App\Services\ChairAnalyticsService::DEVELOPING_ATTEMPTS }}+ completed items).</div>
                <div class="chart-canvas-wrap"><canvas id="vizReadinessDonut"></canvas></div>
            </div>

            <div class="viz-card">
                <h4><i class="fas fa-chart-simple"></i> Score Distribution</h4>
                <div class="viz-sub">How the measured class is spread — an average alone hides a split cohort.</div>
                <div class="chart-canvas-wrap"><canvas id="vizDistribution"></canvas></div>
            </div>

            <div class="viz-card full">
                <h4><i class="fas fa-chart-line"></i> Board Readiness &amp; Class Accuracy Trend</h4>
                <div class="viz-sub">Both series are percentages on one axis, cumulative to the end of each week.</div>
                <div class="chart-canvas-wrap h-md"><canvas id="vizTrendLine"></canvas></div>
            </div>

            <div class="viz-card">
                <h4><i class="fas fa-users"></i> Students Practising Each Week</h4>
                <div class="viz-sub">Distinct students who completed at least one quiz that week.</div>
                <div class="chart-canvas-wrap"><canvas id="vizActiveStudents"></canvas></div>
            </div>

            <div class="viz-card">
                <h4><i class="fas fa-list-check"></i> Practice Volume Each Week</h4>
                <div class="viz-sub">Items answered in completed quizzes.</div>
                <div class="chart-canvas-wrap"><canvas id="vizVolume"></canvas></div>
            </div>

            <div class="viz-card">
                <h4><i class="fas fa-gauge-high"></i> Accuracy by Difficulty</h4>
                <div class="viz-sub">A calibration check on the test bank, not a student metric.</div>
                <div class="chart-canvas-wrap"><canvas id="vizDifficulty"></canvas></div>
            </div>

            <div class="viz-card">
                <h4><i class="fas fa-layer-group"></i> Practice Attempts by Subject</h4>
                <div class="viz-sub">Where the cohort is actually spending its practice.</div>
                <div class="chart-canvas-wrap"><canvas id="vizAttemptsBar"></canvas></div>
            </div>

            <div class="viz-card full">
                <h4><i class="fas fa-triangle-exclamation"></i> Weakest Topics</h4>
                <div class="viz-sub">Lowest class accuracy among topics with at least {{ \App\Services\ChairAnalyticsService::TOPIC_MIN_ATTEMPTS }} recorded attempts — the remediation shortlist.</div>
                <div class="chart-canvas-wrap h-xl"><canvas id="vizWeakTopics"></canvas></div>
            </div>
        </div>

        <div class="method-note" style="margin-top:16px;">Every value plotted here is also listed as a table on the <strong>Overview</strong> tab.</div>
    </div><!-- /tab-visualization -->
</main>

<script>
(function () {
    const P = Viz.palette;

    const subjects     = @json($report['subjects']->values());
    const trend        = @json($report['trend']->values());
    const engagement   = @json($report['engagement']->values());
    const distribution = @json($report['distribution']->values());
    const difficulty   = @json($report['difficulty']->values());
    const weakTopics   = @json($report['weak_topics']->values());
    const readiness    = @json($report['readiness']);

    const pluck = (rows, key) => rows.map((row) => row[key]);

    /* ── Subject accuracy vs each subject's passing threshold ───────────── */
    Viz.chart('vizSubjectAccuracy', {
        type: 'bar',
        data: {
            labels: pluck(subjects, 'code'),
            datasets: [Viz.bar({
                label: 'Class accuracy',
                data: subjects.map((s) => s.accuracy === null ? 0 : s.accuracy),
                backgroundColor: P.s1,
                maxBarThickness: 22,
            })],
        },
        options: {
            indexAxis: 'y',
            layout: { padding: { right: 46 } },
            scales: { x: Viz.percentAxis(), y: Viz.catAxis() },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => {
                    const s = subjects[c.dataIndex];
                    return s.accuracy === null
                        ? 'No attempts recorded'
                        : s.accuracy + '% accuracy · passing mark ' + s.threshold + '% · ' + s.attempts.toLocaleString() + ' attempts';
                } } },
            },
        },
        plugins: [
            Viz.referenceMarks(pluck(subjects, 'threshold'), P.ink),
            Viz.endLabels((v) => v + '%'),
        ],
    });

    /* ── Readiness bands (status colours, always labelled) ──────────────── */
    Viz.chart('vizReadinessDonut', {
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
                    return c.label + ': ' + c.raw + ' student' + (c.raw === 1 ? '' : 's')
                        + ' (' + Math.round(c.raw / total * 100) + '%)';
                } } },
            },
        },
    });

    /* ── Score distribution ─────────────────────────────────────────────── */
    Viz.chart('vizDistribution', {
        type: 'bar',
        data: {
            labels: pluck(distribution, 'label'),
            datasets: [Viz.bar({ label: 'Students', data: pluck(distribution, 'students'), backgroundColor: P.s1 })],
        },
        options: {
            scales: { y: Viz.countAxis({ title: { display: true, text: 'Students', color: P.muted } }), x: Viz.catAxis() },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => c.raw + ' student' + (c.raw === 1 ? '' : 's') + ' in ' + c.label } },
            },
        },
    });

    /* ── Readiness + accuracy trend (two percentage series, one axis) ───── */
    Viz.chart('vizTrendLine', {
        type: 'line',
        data: {
            labels: pluck(trend, 'label'),
            datasets: [
                Viz.line({ label: 'Board readiness', data: pluck(trend, 'rate'), borderColor: P.s1, backgroundColor: 'rgba(163,43,43,.08)', pointBackgroundColor: P.s1, fill: true }),
                Viz.line({ label: 'Class accuracy', data: pluck(trend, 'accuracy'), borderColor: P.s2, backgroundColor: 'transparent', pointBackgroundColor: P.s2 }),
            ],
        },
        options: {
            interaction: { mode: 'index', intersect: false },
            scales: { y: Viz.percentAxis(), x: Viz.catAxis() },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { afterBody: (items) => {
                    const point = trend[items[0].dataIndex];
                    return point.ready + ' of ' + point.eligible + ' eligible students ready';
                } } },
            },
        },
    });

    /* ── Weekly active students ─────────────────────────────────────────── */
    Viz.chart('vizActiveStudents', {
        type: 'line',
        data: {
            labels: pluck(engagement, 'label'),
            datasets: [Viz.line({ label: 'Active students', data: pluck(engagement, 'active_students'), borderColor: P.s2, backgroundColor: 'rgba(42,120,214,.08)', pointBackgroundColor: P.s2, fill: true })],
        },
        options: {
            interaction: { mode: 'index', intersect: false },
            scales: { y: Viz.countAxis(), x: Viz.catAxis() },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { afterBody: (items) => {
                    const week = engagement[items[0].dataIndex];
                    return week.quizzes + ' quizzes · ' + week.items.toLocaleString() + ' items · ' + week.hours + ' h';
                } } },
            },
        },
    });

    /* ── Weekly practice volume ─────────────────────────────────────────── */
    Viz.chart('vizVolume', {
        type: 'bar',
        data: {
            labels: pluck(engagement, 'label'),
            datasets: [Viz.bar({ label: 'Items answered', data: pluck(engagement, 'items'), backgroundColor: P.s1 })],
        },
        options: {
            scales: { y: Viz.countAxis(), x: Viz.catAxis() },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => c.raw.toLocaleString() + ' items answered' } },
            },
        },
    });

    /* ── Accuracy by difficulty (ordered bands → ordinal ramp) ──────────── */
    Viz.chart('vizDifficulty', {
        type: 'bar',
        data: {
            labels: pluck(difficulty, 'label'),
            datasets: [Viz.bar({
                label: 'Accuracy',
                data: difficulty.map((d) => d.accuracy === null ? 0 : d.accuracy),
                backgroundColor: P.ordinal,
            })],
        },
        options: {
            scales: { y: Viz.percentAxis(), x: Viz.catAxis() },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => {
                    const level = difficulty[c.dataIndex];
                    return level.accuracy === null
                        ? 'Not answered yet'
                        : level.accuracy + '% correct across ' + level.answered.toLocaleString() + ' answers';
                } } },
            },
        },
    });

    /* ── Attempts by subject ────────────────────────────────────────────── */
    Viz.chart('vizAttemptsBar', {
        type: 'bar',
        data: {
            labels: pluck(subjects, 'code'),
            datasets: [Viz.bar({ label: 'Attempts', data: pluck(subjects, 'attempts'), backgroundColor: P.s1 })],
        },
        options: {
            scales: { y: Viz.countAxis(), x: Viz.catAxis() },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => {
                    const s = subjects[c.dataIndex];
                    return s.attempts.toLocaleString() + ' attempts by ' + s.students + ' student' + (s.students === 1 ? '' : 's');
                } } },
            },
        },
    });

    /* ── Weakest topics ─────────────────────────────────────────────────── */
    if (weakTopics.length) {
        Viz.chart('vizWeakTopics', {
            type: 'bar',
            data: {
                labels: weakTopics.map((t) => t.subject_code + ' · ' + t.name),
                datasets: [Viz.bar({ label: 'Accuracy', data: pluck(weakTopics, 'accuracy'), backgroundColor: P.s1, maxBarThickness: 18 })],
            },
            options: {
                indexAxis: 'y',
                layout: { padding: { right: 46 } },
                scales: { x: Viz.percentAxis(), y: Viz.catAxis({ ticks: { color: P.ink, padding: 6, font: { size: 10 } } }) },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (c) => {
                        const t = weakTopics[c.dataIndex];
                        return t.accuracy + '% across ' + t.attempts.toLocaleString() + ' attempts by ' + t.students + ' student' + (t.students === 1 ? '' : 's');
                    } } },
                },
            },
            plugins: [Viz.endLabels((v) => v + '%')],
        });
    } else {
        document.getElementById('vizWeakTopics').outerHTML =
            '<div class="viz-empty">No topic has reached {{ \App\Services\ChairAnalyticsService::TOPIC_MIN_ATTEMPTS }} recorded attempts yet.</div>';
    }
})();
</script>
</body>
</html>
