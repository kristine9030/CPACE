<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Performance Report - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.chart-kit')
    <style>
        .tab-bar { display:flex; gap:0; margin-bottom:18px; background:white; border-radius:12px; padding:4px; border:1px solid #eee; width:fit-content; }
        .tab-btn { padding:9px 22px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; background:transparent; color:#888; transition:all .2s; display:flex; align-items:center; gap:7px; }
        .tab-btn:hover { color:#555; background:#f8f8fa; }
        .tab-btn.active { background:var(--primary); color:white; box-shadow:0 2px 8px rgba(123,29,29,0.25); }
        .tab-panel { display:none; }
        .tab-panel.active { display:block; }
        .report-grid { display:grid; grid-template-columns:minmax(0,1.35fr) minmax(320px,.65fr); gap:18px; }
        .report-table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
        .report-table-wrap table { min-width:780px; }
        .faculty-cell { display:flex; align-items:center; gap:10px; }
        .faculty-name { font-size:12.5px; font-weight:600; color:#1a1a1a; }
        .faculty-subjects { font-size:10px; color:#aaa; margin-top:3px; }
        .metric-main { font-size:13px; font-weight:700; color:#222; }
        .metric-sub { font-size:10px; color:#aaa; margin-top:2px; }
        .quality-pill { display:inline-flex; align-items:center; gap:5px; padding:4px 9px; border-radius:15px; font-size:10px; font-weight:700; }
        .quality-pill.good { background:#d1fae5; color:#047857; }
        .quality-pill.flag { background:#fde8e8; color:#b91c1c; }
        .subject-row { padding:14px 0; border-top:1px solid #f5f5f5; }
        .subject-top { display:flex; justify-content:space-between; gap:10px; align-items:center; }
        .subject-title { font-size:12px; font-weight:600; color:#333; }
        .subject-count { font-size:15px; font-weight:700; color:#1a1a1a; }
        .subject-bar { height:6px; background:#f1f1f3; border-radius:4px; overflow:hidden; margin:8px 0 6px; }
        .subject-fill { height:100%; min-width:2px; border-radius:4px; background:var(--primary); }
        .subject-meta { display:flex; justify-content:space-between; gap:8px; font-size:10px; color:#999; }
        .legend-note { font-size:10.5px; line-height:1.6; color:#999; background:#f8f8fa; border-radius:9px; padding:11px 13px; margin-top:14px; }
        @media(max-width:1050px) { .report-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'faculty-performance'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Faculty Performance Report</div>
                <div class="page-sub">Compare test-bank contributions, subject coverage, and live question-quality signals.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('chair.faculty') }}" class="btn btn-outline"><i class="fas fa-users"></i> Faculty Accounts</a>
            @include('partials.topbar-actions')
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card"><div class="stat-top"><div><div class="stat-num">{{ $stats['questions'] }}</div><div class="stat-lbl">Total Contributions</div></div><div class="stat-icon si-red"><i class="fas fa-circle-question"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-num">{{ $stats['contributors'] }}</div><div class="stat-lbl">Contributing Faculty</div></div><div class="stat-icon si-blue"><i class="fas fa-user-pen"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-num">{{ $stats['accuracy'] ?? '—' }}{{ $stats['accuracy'] !== null ? '%' : '' }}</div><div class="stat-lbl">Overall Question Accuracy</div></div><div class="stat-icon si-green"><i class="fas fa-bullseye"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-num">{{ $stats['flags'] }}</div><div class="stat-lbl">Questions Needing Review</div></div><div class="stat-icon si-orange"><i class="fas fa-triangle-exclamation"></i></div></div></div>
    </div>

    <div class="tab-bar" role="tablist">
        <button class="tab-btn active" role="tab" aria-selected="true" onclick="switchTab('overview', this)"><i class="fas fa-table-columns"></i> Overview</button>
        <button class="tab-btn" role="tab" aria-selected="false" onclick="switchTab('visualization', this)"><i class="fas fa-chart-line"></i> Visualization</button>
    </div>

    <div id="tab-overview" class="tab-panel active">
    <div class="report-grid">
        <div class="card">
            <div class="card-head">
                <span class="card-title">Faculty Contribution Ranking</span>
                <span style="font-size:10.5px;color:#aaa;">Ranked by questions contributed</span>
            </div>
            <div class="report-table-wrap">
                <table>
                    <thead><tr><th>Faculty</th><th>Questions</th><th>Variants</th><th>Student Results</th><th>Quality</th><th>Last Contribution</th><th></th></tr></thead>
                    <tbody>
                    @forelse($facultyRows as $member)
                        <tr>
                            <td>
                                <div class="faculty-cell">
                                    <div class="user-av" style="width:32px;height:32px;font-size:10px;">{{ $member['initials'] }}</div>
                                    <div><div class="faculty-name">{{ $member['name'] }}</div><div class="faculty-subjects">{{ $member['subjects'] ? implode(', ', $member['subjects']) : 'No subjects assigned' }}</div></div>
                                </div>
                            </td>
                            <td><div class="metric-main">{{ $member['questions'] }}</div><div class="metric-sub">{{ $member['active'] }} active · {{ $member['draft'] }} draft</div></td>
                            <td><div class="metric-main">{{ $member['variants'] }}</div></td>
                            <td><div class="metric-main">{{ $member['accuracy'] === null ? '—' : $member['accuracy'].'%' }}</div><div class="metric-sub">{{ $member['answered'] }} answers</div></td>
                            <td>
                                @if($member['questions'] === 0)
                                    <span class="quality-pill" style="background:#f3f4f6;color:#9ca3af;">No data</span>
                                @elseif($member['quality_flags'] > 0)
                                    <span class="quality-pill flag"><i class="fas fa-triangle-exclamation"></i>{{ $member['quality_flags'] }} review</span>
                                    <div class="metric-sub">{{ $member['unused'] }} unused</div>
                                @else
                                    <span class="quality-pill good"><i class="fas fa-check"></i>Healthy</span>
                                @endif
                            </td>
                            <td><div style="font-size:11px;color:#555;">{{ $member['last_contribution'] ? $member['last_contribution']->diffForHumans() : 'No contributions' }}</div></td>
                            <td><a href="{{ route('chair.faculty.activity', $member['id']) }}" class="btn btn-ghost btn-sm" title="View activity"><i class="fas fa-clock-rotate-left"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty"><i class="fas fa-chart-column"></i><div>No faculty accounts available.</div></div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><span class="card-title">Question Coverage by Subject</span></div>
            @forelse($subjectRows as $subject)
                <div class="subject-row">
                    <div class="subject-top">
                        <div><span class="subj-badge b-{{ strtolower($subject['code']) }}">{{ $subject['code'] }}</span><span class="subject-title">{{ $subject['name'] }}</span></div>
                        <span class="subject-count">{{ $subject['questions'] }}</span>
                    </div>
                    <div class="subject-bar"><div class="subject-fill" style="width:{{ $subject['width'] }}%;"></div></div>
                    <div class="subject-meta">
                        <span>{{ $subject['active'] }} active · {{ $subject['contributors'] }} contributor{{ $subject['contributors'] === 1 ? '' : 's' }} · {{ $subject['assigned_faculty'] }} assigned</span>
                        <span>{{ $subject['accuracy'] === null ? 'No answers' : $subject['accuracy'].'% accuracy' }}</span>
                    </div>
                    @if($subject['assigned_faculty'] === 0)
                        <div style="margin-top:6px;"><span class="pill pill-off"><i class="fas fa-user-slash"></i> No faculty assigned</span></div>
                    @endif
                </div>
            @empty
                <div class="empty"><div>No subjects available.</div></div>
            @endforelse
            <div class="legend-note"><strong>Quality review:</strong> flags questions that are unused, below 40% accuracy, or above 95% accuracy after at least five student answers. These are review signals, not faculty grades.</div>
        </div>
    </div>
    </div><!-- /tab-overview -->

    <div id="tab-visualization" class="tab-panel">
        <div class="viz-grid-layout">
            <div class="viz-card full">
                <h4><i class="fas fa-user-pen"></i> Test-Bank Contributions per Faculty</h4>
                <div class="viz-sub">Drafted questions do not reach students until they are activated, so both states are shown.</div>
                <div class="chart-canvas-wrap h-lg"><canvas id="vizFacultyContrib"></canvas></div>
            </div>

            <div class="viz-card">
                <h4><i class="fas fa-triangle-exclamation"></i> Questions Flagged for Review</h4>
                <div class="viz-sub">Unused, or answered at under 40% / over 95% after five attempts.</div>
                <div class="chart-canvas-wrap h-md"><canvas id="vizFacultyFlags"></canvas></div>
            </div>

            <div class="viz-card">
                <h4><i class="fas fa-bullseye"></i> Student Accuracy on Each Subject's Questions</h4>
                <div class="viz-sub">A calibration signal for the bank, not a ranking of faculty.</div>
                <div class="chart-canvas-wrap h-md"><canvas id="vizSubjectAccuracy"></canvas></div>
            </div>

            <div class="viz-card full">
                <h4><i class="fas fa-people-arrows"></i> Authoring Capacity per Subject</h4>
                <div class="viz-sub">Assigned faculty against those who have actually written questions — a gap here is an unstaffed subject.</div>
                <div class="chart-canvas-wrap h-md"><canvas id="vizCapacity"></canvas></div>
            </div>
        </div>

        <div class="legend-note" style="margin-top:16px;">Every value plotted here is also listed as a table on the <strong>Overview</strong> tab.</div>
    </div><!-- /tab-visualization -->
</main>

<script>
(function () {
    const P = Viz.palette;
    const faculty = @json($facultyRows);
    const subjects = @json($subjectRows);
    const pluck = (rows, key) => rows.map((row) => row[key]);

    const contributors = faculty.filter((member) => member.questions > 0);
    const flagged = faculty.filter((member) => member.quality_flags > 0);

    /* ── Contributions per faculty ──────────────────────────────────────── */
    if (contributors.length) {
        Viz.chart('vizFacultyContrib', {
            type: 'bar',
            data: {
                labels: pluck(contributors, 'name'),
                datasets: [
                    Viz.stacked({ label: 'Active', data: pluck(contributors, 'active'), backgroundColor: P.s1, maxBarThickness: 20 }),
                    Viz.stacked({ label: 'Draft', data: pluck(contributors, 'draft'), backgroundColor: P.s2, maxBarThickness: 20 }),
                ],
            },
            options: {
                indexAxis: 'y',
                scales: {
                    x: Viz.countAxis({ stacked: true, title: { display: true, text: 'Questions written', color: P.muted } }),
                    y: Viz.catAxis({ stacked: true }),
                },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { mode: 'index', callbacks: { afterBody: (items) => {
                        const member = contributors[items[0].dataIndex];
                        return member.variants + ' variants · ' + (member.accuracy === null ? 'no answers yet' : member.accuracy + '% student accuracy');
                    } } },
                },
            },
        });
    } else {
        document.getElementById('vizFacultyContrib').outerHTML = '<div class="viz-empty">No faculty member has contributed a question yet.</div>';
    }

    /* ── Questions flagged for review ───────────────────────────────────── */
    if (flagged.length) {
        Viz.chart('vizFacultyFlags', {
            type: 'bar',
            data: {
                labels: pluck(flagged, 'name'),
                datasets: [Viz.bar({ label: 'Flagged', data: pluck(flagged, 'quality_flags'), backgroundColor: P.s1, maxBarThickness: 20 })],
            },
            options: {
                indexAxis: 'y',
                layout: { padding: { right: 34 } },
                scales: { x: Viz.countAxis(), y: Viz.catAxis() },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (c) => {
                        const member = flagged[c.dataIndex];
                        return member.quality_flags + ' of ' + member.active + ' active questions flagged · ' + member.unused + ' never used';
                    } } },
                },
            },
            plugins: [Viz.endLabels()],
        });
    } else {
        document.getElementById('vizFacultyFlags').outerHTML = '<div class="viz-empty">No question is currently flagged for review.</div>';
    }

    /* ── Student accuracy per subject ───────────────────────────────────── */
    Viz.chart('vizSubjectAccuracy', {
        type: 'bar',
        data: {
            labels: pluck(subjects, 'code'),
            datasets: [Viz.bar({
                label: 'Accuracy',
                data: subjects.map((s) => s.accuracy === null ? 0 : s.accuracy),
                backgroundColor: P.s1,
            })],
        },
        options: {
            scales: { y: Viz.percentAxis(), x: Viz.catAxis() },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => {
                    const s = subjects[c.dataIndex];
                    return s.accuracy === null
                        ? 'No answers recorded yet'
                        : s.accuracy + '% across ' + s.answered.toLocaleString() + ' answers';
                } } },
            },
        },
    });

    /* ── Authoring capacity per subject ─────────────────────────────────── */
    Viz.chart('vizCapacity', {
        type: 'bar',
        data: {
            labels: pluck(subjects, 'code'),
            datasets: [
                Viz.bar({ label: 'Assigned faculty', data: pluck(subjects, 'assigned_faculty'), backgroundColor: P.s1 }),
                Viz.bar({ label: 'Faculty who have written questions', data: pluck(subjects, 'contributors'), backgroundColor: P.s2 }),
            ],
        },
        options: {
            scales: { y: Viz.countAxis(), x: Viz.catAxis() },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { mode: 'index', callbacks: { afterBody: (items) => {
                    const s = subjects[items[0].dataIndex];
                    return s.questions.toLocaleString() + ' questions in the bank (' + s.active + ' active)';
                } } },
            },
        },
    });
})();
</script>
</body>
</html>
