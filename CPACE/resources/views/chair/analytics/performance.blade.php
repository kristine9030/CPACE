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
    @include('partials.chart-filter-styles')
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

        /* Charts / Tables switch — both views follow the same filters. */
        .tab-bar { display:flex; gap:0; background:white; border-radius:12px; padding:4px; border:1px solid #eee; width:fit-content; margin-bottom:16px; }
        .tab-btn { padding:9px 22px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; background:transparent; color:#888; transition:all .2s; display:flex; align-items:center; gap:7px; }
        .tab-btn:hover { color:#555; background:#f8f8fa; }
        .tab-btn.active { background:var(--primary); color:white; box-shadow:0 2px 8px rgba(123,29,29,0.25); }
        .tab-panel { display:none; }
        .tab-panel.active { display:block; }

        #tab-charts .chart-grid { align-items:start; }
        .is-loading .chart-grid, .is-loading .table-card { opacity:.55; pointer-events:none; transition:opacity .2s; }
        .confidence { display:inline-block; font-size:10px; font-weight:700; padding:2px 9px; border-radius:20px; margin-left:6px; vertical-align:middle; }
        .confidence.high { color:#047857; background:#e7f6ef; }
        .confidence.medium { color:#b45309; background:#fef3c7; }
        .confidence.low { color:#b91c1c; background:#fde8e8; }

        /* Tables view */
        .table-card { background:#fff; border-radius:14px; padding:20px 22px; margin-bottom:16px; box-shadow:0 2px 6px rgba(15,10,10,.08), 0 10px 22px -10px rgba(15,10,10,.22); }
        .table-card h3 { font-size:14px; font-weight:700; color:#1a1a1a; display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:12px; }
        .table-card h3 small { font-size:10px; font-weight:500; color:#aaa; }
        .table-pair { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .table-pair .table-card { margin-bottom:0; }
        .table-scroll { overflow-x:auto; }
        .subject-row { display:grid; grid-template-columns:minmax(185px,1fr) minmax(160px,1.3fr) 72px 78px 110px; gap:13px; align-items:center; padding:12px 0; border-top:1px solid #f3f3f3; }
        .subject-row.head { border:0; padding-top:0; font-size:9.5px; color:#aaa; text-transform:uppercase; letter-spacing:.3px; }
        .subject-name { font-size:11px; color:#555; margin-left:7px; }
        .bar { height:7px; border-radius:5px; background:#f0f1f3; overflow:hidden; }
        .bar > span { display:block; height:100%; border-radius:5px; background:var(--primary); }
        .accuracy { font-size:13px; font-weight:700; color:#222; }
        .delta { font-size:11px; font-weight:700; }
        .delta.up { color:#047857; } .delta.down { color:#b91c1c; } .delta.na { color:#bbb; font-weight:500; }
        .small-meta { font-size:9.5px; color:#aaa; }
        .table-empty { text-align:center; padding:26px 10px; color:#aaa; font-size:12px; }
        .method-note { font-size:10px; line-height:1.7; color:#888; background:#f8f8fa; border-radius:10px; padding:12px 14px; }

        /* Leaderboard */
        .lb-head { display:flex; justify-content:space-between; align-items:flex-start; gap:14px; flex-wrap:wrap; margin-bottom:16px; }
        .lb-scope { font-size:11px; color:#999; }
        .lb-sort { display:flex; gap:4px; background:#f4f5f7; border-radius:10px; padding:3px; }
        .lb-sort button { display:flex; align-items:center; gap:6px; padding:7px 12px; border:none; border-radius:8px; background:none; color:#777; font:600 11.5px 'Poppins',sans-serif; cursor:pointer; }
        .lb-sort button.active { background:#fff; color:var(--primary); box-shadow:0 1px 3px rgba(0,0,0,.1); }
        .lb-podium { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin-bottom:16px; }
        .lb-podium:empty { display:none; }
        .lb-top { display:flex; align-items:center; gap:12px; padding:14px; border-radius:12px; border:1px solid #f1e3e3; background:linear-gradient(135deg,#fff,#fdf6f6); text-decoration:none; color:inherit; }
        .lb-top:hover { border-color:#e3c4c4; }
        .lb-medal { width:34px; height:34px; border-radius:50%; display:grid; place-items:center; font-size:14px; color:#fff; flex:none; }
        .lb-medal.r1 { background:#d4a017; } .lb-medal.r2 { background:#9ca3af; } .lb-medal.r3 { background:#b87333; }
        .lb-top-name { font-size:13px; font-weight:700; color:#1a1a1a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .lb-top-meta { font-size:10.5px; color:#999; }
        .lb-top-score { margin-left:auto; text-align:right; font-size:18px; font-weight:700; color:var(--primary); line-height:1.1; }
        .lb-top-score small { display:block; font-size:9.5px; font-weight:500; color:#aaa; }
        .lb-table td { vertical-align:middle; }
        .lb-rank { font-weight:700; color:#1a1a1a; text-align:center; }
        .lb-student { display:flex; align-items:center; gap:10px; min-width:0; text-decoration:none; color:inherit; }
        .lb-av { width:30px; height:30px; border-radius:50%; background:var(--primary); color:#fff; display:grid; place-items:center; font-size:10.5px; font-weight:700; flex:none; }
        .lb-name { font-size:12px; font-weight:600; color:#1a1a1a; }
        .lb-student:hover .lb-name { color:var(--primary); text-decoration:underline; }
        .lb-sub { font-size:10px; color:#aaa; }
        .lb-foot { display:flex; justify-content:space-between; align-items:center; gap:10px; margin-top:12px; font-size:11px; color:#999; }
        .lb-foot .btn[hidden] { display:none; }
        /* Per-subject standing: rank chip over that subject's accuracy. */
        .lb-table th.lb-subj, .lb-table td.lb-subj { text-align:center; padding-left:4px; padding-right:4px; }
        .lb-table th.lb-subj { color:#7a7a7a; }
        .lb-srank { display:inline-block; min-width:32px; padding:2px 6px; border-radius:6px; font-size:11px; font-weight:700; background:#f3f4f6; color:#555; }
        .lb-srank.s1 { background:var(--primary); color:#fff; }
        .lb-srank.s2 { background:#e3c4c4; color:#5f1515; }
        .lb-srank.s3 { background:#f5e8e8; color:#7B1D1D; }
        .lb-sacc { display:block; font-size:9.5px; color:#aaa; margin-top:2px; }
        .lb-snone { color:#d4d4d4; }
        .lb-top-best { font-size:10px; font-weight:600; color:var(--primary); margin-top:2px; }

        @media (max-width: 1050px) { .table-pair { grid-template-columns:1fr; } .lb-podium { grid-template-columns:1fr; } }
        @media (max-width: 640px) {
            .tab-bar { width:100%; }
            .tab-btn { flex:1; justify-content:center; }
            .subject-row { grid-template-columns:1fr 65px; }
            .subject-row .bar, .subject-row .small-meta, .subject-row .delta, .subject-row.head { display:none; }
        }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'analytics-performance'])
<main class="main" id="page">
    <div class="topbar">
        <div class="topbar-left"><div><div class="page-title">Class-Level Performance</div><div class="page-sub">Accuracy, board readiness and practice across the class — every figure follows the filters below.</div></div></div>
        <div class="topbar-right">@include('partials.topbar-actions')</div>
    </div>

    {{-- Filters redraw everything below in place and are mirrored into the
         URL, so a filtered view can be bookmarked or shared. --}}
    <section class="dash-filters" aria-label="Filters">
        <div class="filter-label"><i class="fas fa-filter"></i> Filters</div>

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
                    <button type="button" class="btn btn-ghost btn-sm" id="rangeCancel">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" id="rangeApply">Apply</button>
                </div>
            </div>
        </div>

        <label class="filter-field">
            <span class="filter-ico"><i class="fas fa-book-open"></i></span>
            <span class="filter-text">
                <span class="filter-cap">Subject</span>
                <select id="filterSubject" aria-label="Subject">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" @selected($filters['subject'] === $s->id)>{{ $s->code }} — {{ $s->name }}</option>
                    @endforeach
                </select>
            </span>
            <i class="fas fa-chevron-down filter-chev"></i>
        </label>

        <label class="filter-field">
            <span class="filter-ico"><i class="fas fa-users"></i></span>
            <span class="filter-text">
                <span class="filter-cap">Section</span>
                <select id="filterSection" aria-label="Section">
                    <option value="">All Sections</option>
                    @foreach($sectionOptions->groupBy(fn ($sec) => $sec->year_level ? (\App\Models\Section::YEAR_LABELS[$sec->year_level] ?? 'Year '.$sec->year_level) : 'No year level') as $yearLabel => $group)
                        <optgroup label="{{ $yearLabel }}">
                            @foreach($group as $sec)
                                <option value="{{ $sec->name }}" @selected($filters['section'] === $sec->name)>{{ $sec->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </span>
            <i class="fas fa-chevron-down filter-chev"></i>
        </label>

        <span class="filter-status" id="filterStatus" aria-live="polite"></span>
        <button type="button" class="filter-reset" id="filterReset"><i class="fas fa-rotate"></i> Reset Filters</button>
    </section>

    @php
        $k = $report['kpis'];
        $confidenceLabel = ['high' => 'High confidence', 'medium' => 'Medium confidence', 'low' => 'Low confidence'][$k['confidence']];
    @endphp
    {{-- Headline figures. Rendered here for the first paint; the script below
         rewrites them whenever a filter changes. --}}
    <section class="chart-grid kpi-grid" aria-label="Headline figures">
        <article class="chart-card kpi-card">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-bullseye"></i></span>
                <span class="chart-card-title">Class Accuracy</span>
            </div>
            <div class="chart-card-value" id="kpiAccuracy">{{ $k['accuracy'] === null ? '—' : $k['accuracy'].'%' }}</div>
            <div class="chart-card-note" id="noteAccuracy">&nbsp;</div>
            <div class="kpi-foot" id="footAccuracy">&nbsp;</div>
        </article>
        <article class="chart-card kpi-card">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-user-graduate"></i></span>
                <span class="chart-card-title">Students Practising</span>
            </div>
            <div class="chart-card-value" id="kpiParticipating">{{ $k['participating'] }}<span class="kpi-of">/ {{ $k['enrolled'] }}</span></div>
            <div class="chart-card-note" id="noteParticipating">&nbsp;</div>
            <div class="kpi-foot" id="footParticipating">&nbsp;</div>
        </article>
        <article class="chart-card kpi-card">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-medal"></i></span>
                <span class="chart-card-title">Board Ready<x-tip label="How readiness is measured">Ready needs at least {{ \App\Services\ChairAnalyticsService::READY_ATTEMPTS }} completed items and {{ \App\Services\ChairAnalyticsService::READY_ACCURACY }}% accuracy — plus activity in at least {{ \App\Services\ChairAnalyticsService::READY_SUBJECTS }} subjects when viewing all subjects. Students need {{ \App\Services\ChairAnalyticsService::DEVELOPING_ATTEMPTS }} items to be measured at all.</x-tip></span>
            </div>
            <div class="chart-card-value" id="kpiReady">{{ $k['readiness_rate'] === null ? '—' : $k['readiness_rate'].'%' }}</div>
            <div class="chart-card-note" id="noteReady">{{ $k['ready'] }} of {{ $k['eligible'] }} measured students ready</div>
            <div class="kpi-foot" id="footReady">&nbsp;</div>
        </article>
        <article class="chart-card kpi-card" id="pass-projection">
            <div class="chart-card-head">
                <span class="chart-card-icon"><i class="fas fa-graduation-cap"></i></span>
                <span class="chart-card-title">Predicted Pass Rate<x-tip label="How the projection works">Counts ready students fully and developing students at 50%. It gets more useful as students complete more practice. A planning estimate, not an official board-exam prediction.</x-tip></span>
            </div>
            <div class="chart-card-value" id="kpiProjection">{{ $k['pass_projection'] === null ? '—' : $k['pass_projection'].'%' }}<span class="confidence {{ $k['confidence'] }}" id="kpiConfidence">{{ $confidenceLabel }}</span></div>
            <div class="chart-card-note" id="noteProjection">&nbsp;</div>
            <div class="kpi-foot" id="footProjection">Based on {{ $k['eligible'] }} of {{ $k['enrolled'] }} active students ({{ $k['coverage'] }}% coverage)</div>
        </article>
    </section>

    <div class="tab-bar" role="tablist">
        <button class="tab-btn active" role="tab" aria-selected="true" onclick="switchTab('charts', this)"><i class="fas fa-chart-line"></i> Charts</button>
        <button class="tab-btn" role="tab" aria-selected="false" onclick="switchTab('tables', this)"><i class="fas fa-table"></i> Tables</button>
        <button class="tab-btn" role="tab" aria-selected="false" onclick="switchTab('leaderboard', this)"><i class="fas fa-trophy"></i> Leaderboard</button>
    </div>

    <div id="tab-charts" class="tab-panel active">
        <section class="chart-grid cols-2" aria-label="Charts">
            <article class="chart-card span-2" id="readiness-trend">
                <div class="chart-card-head">
                    <span class="chart-card-icon"><i class="fas fa-chart-line"></i></span>
                    <span class="chart-card-title">Board Readiness Rate vs. Class Accuracy</span>
                </div>
                <div class="chart-card-note" id="noteTrend">&nbsp;</div>
                <div class="chart-canvas-wrap tall">
                    <canvas id="chartTrend" role="img" aria-label="Cumulative board readiness rate and per-period class accuracy over the selected range"></canvas>
                    <div class="viz-empty chart-empty" id="emptyTrend" hidden>No quiz activity yet.</div>
                </div>
                <div class="chart-card-cap">Readiness rate: share of measured students (20+ questions) who have 50+ questions at 75%+ accuracy across 3+ subjects, counted from the start. Class accuracy: % of questions answered correctly within each period.</div>
            </article>

            <article class="chart-card">
                <div class="chart-card-head">
                    <span class="chart-card-icon"><i class="fas fa-book-open"></i></span>
                    <span class="chart-card-title">Subject Accuracy vs Passing Mark</span>
                </div>
                <div class="chart-card-note">Bars are class accuracy; the dark rule is each subject's passing mark.</div>
                <div class="chart-canvas-wrap tall">
                    <canvas id="chartSubjects" role="img" aria-label="Class accuracy per subject against its passing mark"></canvas>
                    <div class="viz-empty chart-empty" id="emptySubjects" hidden>No quiz activity in this range.</div>
                </div>
                <div class="chart-card-cap">Quizzes completed within the range</div>
            </article>

            <article class="chart-card">
                <div class="chart-card-head">
                    <span class="chart-card-icon"><i class="fas fa-people-group"></i></span>
                    <span class="chart-card-title">Accuracy by Section</span>
                </div>
                <div class="chart-card-note" id="noteSections">&nbsp;</div>
                <div class="chart-canvas-wrap tall">
                    <canvas id="chartSections" role="img" aria-label="Class accuracy per section"></canvas>
                    <div class="viz-empty chart-empty" id="emptySections" hidden>No section has quiz activity in this range.</div>
                </div>
                <div class="chart-card-cap">Shows every section — the selected one is highlighted</div>
            </article>

            <article class="chart-card">
                <div class="chart-card-head">
                    <span class="chart-card-icon"><i class="fas fa-chart-simple"></i></span>
                    <span class="chart-card-title">Score Distribution</span>
                </div>
                <div class="chart-card-note">How the measured class is spread, not just its average.</div>
                <div class="chart-canvas-wrap tall">
                    <canvas id="chartDistribution" role="img" aria-label="Measured students by accuracy band"></canvas>
                    <div class="viz-empty chart-empty" id="emptyDistribution" hidden>No student has enough attempts to be measured yet.</div>
                </div>
                <div class="chart-card-cap">Measured students, as of the range end</div>
            </article>

            <article class="chart-card">
                <div class="chart-card-head">
                    <span class="chart-card-icon"><i class="fas fa-users"></i></span>
                    <span class="chart-card-title">Students Practising</span>
                </div>
                <div class="chart-card-note" id="noteEngagement">&nbsp;</div>
                <div class="chart-canvas-wrap tall">
                    <canvas id="chartEngagement" role="img" aria-label="Distinct students completing a quiz per period"></canvas>
                    <div class="viz-empty chart-empty" id="emptyEngagement" hidden>No quiz activity in this range.</div>
                </div>
                <div class="chart-card-cap">Distinct students completing a quiz in each period</div>
            </article>

            <article class="chart-card">
                <div class="chart-card-head">
                    <span class="chart-card-icon"><i class="fas fa-gauge-high"></i></span>
                    <span class="chart-card-title">Accuracy by Difficulty<x-tip>Accuracy should fall as difficulty rises. If it doesn't, the test bank's difficulty labels need review.</x-tip></span>
                </div>
                <div class="chart-card-note">A calibration check on the test bank, not a student metric.</div>
                <div class="chart-canvas-wrap tall">
                    <canvas id="chartDifficulty" role="img" aria-label="Class accuracy by question difficulty"></canvas>
                    <div class="viz-empty chart-empty" id="emptyDifficulty" hidden>No answers recorded in this range.</div>
                </div>
                <div class="chart-card-cap">Answers in quizzes completed within the range</div>
            </article>

            <article class="chart-card">
                <div class="chart-card-head">
                    <span class="chart-card-icon"><i class="fas fa-triangle-exclamation"></i></span>
                    <span class="chart-card-title">Weakest Topics</span>
                </div>
                <div class="chart-card-note">Under 60% class accuracy, with {{ \App\Services\ChairAnalyticsService::TOPIC_MIN_ATTEMPTS }}+ answers in the range.</div>
                <div class="chart-canvas-wrap" id="weakWrap" style="height:320px;">
                    <canvas id="chartWeak" role="img" aria-label="Weakest topics by class accuracy"></canvas>
                    <div class="viz-empty chart-empty" id="emptyWeak" hidden>No topic is below 60% (among topics with {{ \App\Services\ChairAnalyticsService::TOPIC_MIN_ATTEMPTS }}+ answers).</div>
                </div>
            </article>
        </section>
    </div>

    <div id="tab-tables" class="tab-panel">
        <section class="table-card">
            <h3>Subject-by-Subject Accuracy <small>Within the selected range</small></h3>
            <div class="table-scroll">
                <div class="subject-row head"><span>Subject</span><span>Accuracy</span><span>Score</span><span>vs pass</span><span>Practice</span></div>
                <div id="tableSubjects"></div>
            </div>
        </section>

        <section class="table-card">
            <h3>Practice per Period <small id="tableEngagementNote"></small></h3>
            <div class="table-scroll">
                <table class="viz-table">
                    <thead><tr><th>Period starting</th><th class="num">Students</th><th class="num">Quizzes</th><th class="num">Items</th><th class="num">Accuracy</th><th class="num">Board ready*</th><th class="num">Hours</th></tr></thead>
                    <tbody id="tableEngagement"></tbody>
                </table>
            </div>
            <div class="small-meta" style="margin-top:8px;">* Cumulative to the end of each period, like the readiness line in the chart above.</div>
        </section>

        <div class="table-pair" style="margin-bottom:16px;">
            <section class="table-card">
                <h3>Accuracy by Section <small>Every active section</small></h3>
                <table class="viz-table">
                    <thead><tr><th>Section</th><th class="num">Accuracy</th><th class="num">Students</th><th class="num">Items</th></tr></thead>
                    <tbody id="tableSections"></tbody>
                </table>
            </section>
            <section class="table-card">
                <h3>Accuracy by Difficulty <small>Should fall as difficulty rises</small></h3>
                <table class="viz-table">
                    <thead><tr><th>Difficulty</th><th class="num">Answers</th><th class="num">Accuracy</th></tr></thead>
                    <tbody id="tableDifficulty"></tbody>
                </table>
            </section>
        </div>

        <div class="table-pair">
            <section class="table-card">
                <h3>Weakest Topics <small>Under 60%</small></h3>
                <table class="viz-table">
                    <thead><tr><th>Topic</th><th>Subject</th><th class="num">Accuracy</th><th class="num">Answers</th><th class="num">Students</th></tr></thead>
                    <tbody id="tableWeak"></tbody>
                </table>
            </section>
            <section class="table-card">
                <h3>Strongest Topics <small>75% and above</small></h3>
                <table class="viz-table">
                    <thead><tr><th>Topic</th><th>Subject</th><th class="num">Accuracy</th><th class="num">Answers</th><th class="num">Students</th></tr></thead>
                    <tbody id="tableStrong"></tbody>
                </table>
            </section>
        </div>

        <div class="method-note" style="margin-top:16px;">
            <i class="fas fa-circle-info"></i>
            Accuracy and practice count quizzes completed within the date range. Readiness, the score distribution and the pass projection are cumulative as of the range end, since a student's standing is built from everything they have practised.
        </div>
    </div>

    <div id="tab-leaderboard" class="tab-panel">
        <section class="table-card">
            <div class="lb-head">
                <div>
                    <h3 style="margin-bottom:2px;">Student Leaderboard</h3>
                    <div class="lb-scope" id="lbScope">&nbsp;</div>
                </div>
                {{-- Ranking rule. "Correct answers" matches the students' own
                     leaderboard; "Accuracy" only ranks students with enough
                     items for the percentage to mean something. --}}
                <div class="lb-sort" role="radiogroup" aria-label="Rank students by">
                    <button type="button" class="active" data-sort="correct" role="radio" aria-checked="true"><i class="fas fa-check-double"></i> Most correct answers</button>
                    <button type="button" data-sort="accuracy" role="radio" aria-checked="false"><i class="fas fa-bullseye"></i> Highest accuracy</button>
                </div>
            </div>
            <div class="lb-podium" id="lbPodium"></div>
            <div class="table-scroll">
                <table class="viz-table lb-table">
                    <thead id="lbHead"></thead>
                    <tbody id="lbBody"></tbody>
                </table>
            </div>
            <div class="lb-foot">
                <span id="lbCount"></span>
                <button type="button" class="btn btn-ghost btn-sm" id="lbMore" hidden>Show all</button>
            </div>
        </section>
    </div>
</main>

<script>
(function () {
    const P = Viz.palette;
    const ENDPOINT = @json(route('chair.analytics.performance.data'));
    const DEFAULTS = @json($defaults);
    const MAX_DAYS = 366;
    const PASS_MARK = 75;

    let filters = @json($filters);
    let inflight = null;

    const $ = (id) => document.getElementById(id);
    const pct = (v) => (v === null || v === undefined ? '—' : v + '%');
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const plural = (n, word, many) => `${Number(n).toLocaleString()} ${n === 1 ? word : (many || word + 's')}`;
    const allNull = (values) => values.every((v) => v === null || v === undefined);
    const toggleEmpty = (id, empty) => { $(id).hidden = !empty; };

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
        if (key === 'last-month') { return [iso(new Date(today.getFullYear(), today.getMonth() - 1, 1)), iso(new Date(today.getFullYear(), today.getMonth(), 0))]; }
        const from = new Date(today);
        from.setDate(from.getDate() - (Number(key) - 1));
        return [iso(from), iso(today)];
    }

    const signed = (n, unit) => `<span class="${n > 0 ? 'up' : 'down'}">${n > 0 ? '+' : ''}${n}${unit}</span>`;
    const tickAxis = (limit) => Viz.catAxis({ ticks: { color: P.ink, padding: 6, maxRotation: 0, autoSkip: true, maxTicksLimit: limit } });
    // ── Headline figures ──
    function renderKpis(k, range) {
        const vsPrev = `vs the previous ${plural(range.days, 'day')}`;

        $('kpiAccuracy').textContent = pct(k.accuracy);
        if (k.accuracy === null) {
            $('noteAccuracy').textContent = 'No quiz activity in this range';
            $('footAccuracy').textContent = 'Nothing to compare yet';
        } else {
            const gap = k.accuracy - PASS_MARK;
            $('noteAccuracy').innerHTML = gap >= 0
                ? `<span class="up">${gap} pts above</span> the ${PASS_MARK}% pass mark`
                : `<span class="down">${Math.abs(gap)} pts below</span> the ${PASS_MARK}% pass mark`;
            $('footAccuracy').innerHTML = k.accuracy_change === null
                ? 'No activity in the period before to compare'
                : (k.accuracy_change === 0 ? `No change ${vsPrev}` : `${signed(k.accuracy_change, ' pts')} ${vsPrev}`);
        }

        $('kpiParticipating').innerHTML = `${k.participating}<span class="kpi-of">/ ${k.enrolled}</span>`;
        const share = k.enrolled ? Math.round(k.participating / k.enrolled * 100) : 0;
        $('noteParticipating').textContent = `${share}% of active students completed a quiz`;
        $('footParticipating').innerHTML = `<strong>${plural(k.quizzes, 'quiz', 'quizzes')}</strong> · <strong>${plural(k.items, 'item')}</strong> answered`;

        $('kpiReady').textContent = pct(k.readiness_rate);
        $('noteReady').innerHTML = k.eligible
            ? `<strong>${k.ready} of ${k.eligible}</strong> measured students ready`
            : 'No measured students yet';
        const notMeasured = Math.max(0, k.enrolled - k.eligible);
        $('footReady').innerHTML = `${k.developing} developing · ${k.at_risk} at risk · <strong>${notMeasured}</strong> not yet measured`;

        const conf = { high: 'High confidence', medium: 'Medium confidence', low: 'Low confidence' }[k.confidence];
        $('kpiProjection').innerHTML = `${pct(k.pass_projection)}<span class="confidence ${k.confidence}">${conf}</span>`;
        $('noteProjection').textContent = k.confidence === 'low'
            ? 'Treat as a rough placeholder until more students are measured'
            : 'Planning estimate, not an official board-exam prediction';
        $('footProjection').textContent = `Based on ${k.eligible} of ${plural(k.enrolled, 'active student')} (${k.coverage}% coverage)`;
    }

    // ── Charts ──
    function renderCharts(r) {
        const range = r.range;
        const last = r.trend[r.trend.length - 1];
        $('noteTrend').textContent = last && last.eligible
            ? `${last.ready} of ${plural(last.eligible, 'measured student')} ready by ${pretty(range.to)}`
            : 'No measured students yet';
        toggleEmpty('emptyTrend', allNull(r.trend.map((p) => p.accuracy)));
        const dots = r.trend.length > 16 ? 0 : 3;
        Viz.chart('chartTrend', {
            type: 'line',
            data: {
                labels: r.trend.map((p) => p.label),
                datasets: [
                    Viz.line({ label: 'Readiness rate (cumulative)', data: r.trend.map((p) => p.readiness), borderColor: P.s1, pointBackgroundColor: P.s1, backgroundColor: 'rgba(163,43,43,.08)', fill: true, spanGaps: true, pointRadius: dots }),
                    // Dashed with its own point shape: the two lines can sit on
                    // the same values for weeks and would otherwise hide each other.
                    Viz.line({ label: 'Class accuracy (per period)', data: r.trend.map((p) => p.accuracy), borderColor: P.s2, pointBackgroundColor: P.s2, backgroundColor: 'transparent', borderDash: [6, 4], pointStyle: 'rectRot', spanGaps: true, pointRadius: dots }),
                ],
            },
            options: {
                interaction: { mode: 'index', intersect: false },
                scales: { y: Viz.percentAxis(), x: tickAxis(10) },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: {
                        label: (c) => `${c.dataset.label}: ${c.raw === null ? '—' : c.raw + '%'}`,
                        afterBody: (items) => { const p = r.trend[items[0].dataIndex]; return [`${p.ready} of ${plural(p.eligible, 'measured student')} ready`, `${(p.answered || 0).toLocaleString()} questions answered this period`]; },
                    } },
                },
            },
        });

        // Subject accuracy vs its own passing mark (horizontal).
        const subjects = r.by_subject;
        toggleEmpty('emptySubjects', allNull(subjects.map((s) => s.accuracy)));
        Viz.chart('chartSubjects', {
            type: 'bar',
            data: {
                labels: subjects.map((s) => s.code),
                datasets: [Viz.bar({
                    label: 'Class accuracy',
                    // null, not 0: an unattempted subject must leave a gap.
                    data: subjects.map((s) => s.accuracy),
                    backgroundColor: subjects.map((s) => (s.accuracy !== null && s.accuracy >= s.threshold ? P.s1 : 'rgba(163,43,43,.55)')),
                    maxBarThickness: 20,
                })],
            },
            options: {
                indexAxis: 'y',
                layout: { padding: { right: 40 } },
                scales: { x: Viz.percentAxis(), y: Viz.catAxis() },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (c) => {
                        const s = subjects[c.dataIndex];
                        return s.accuracy === null ? 'No attempts in range' : `${s.accuracy}% · passing ${s.threshold}% · ${plural(s.items, 'item')}`;
                    } } },
                },
            },
            plugins: [Viz.referenceMarks(subjects.map((s) => s.threshold), P.ink), Viz.endLabels((v) => v + '%')],
        });

        // Section comparison — all sections, the selected one highlighted.
        const secs = r.by_section;
        $('noteSections').textContent = filters.section ? `${filters.section} vs the other sections` : `${plural(secs.length, 'active section')} compared`;
        toggleEmpty('emptySections', allNull(secs.map((s) => s.accuracy)));
        Viz.chart('chartSections', {
            type: 'bar',
            data: {
                labels: secs.map((s) => s.section),
                datasets: [Viz.bar({ label: 'Class accuracy', data: secs.map((s) => s.accuracy), backgroundColor: secs.map((s) => (!filters.section || s.section === filters.section ? P.s1 : 'rgba(163,43,43,.28)')), maxBarThickness: 20 })],
            },
            options: {
                indexAxis: 'y',
                layout: { padding: { right: 40 } },
                scales: { x: Viz.percentAxis(), y: Viz.catAxis() },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (c) => {
                        const s = secs[c.dataIndex];
                        return s.accuracy === null ? 'No activity in range' : `${s.accuracy}% · ${plural(s.students, 'student')} · ${plural(s.items, 'item')}`;
                    } } },
                },
            },
            plugins: [Viz.endLabels((v) => v + '%')],
        });

        // Score distribution.
        const dist = r.distribution;
        toggleEmpty('emptyDistribution', dist.every((d) => d.students === 0));
        Viz.chart('chartDistribution', {
            type: 'bar',
            data: { labels: dist.map((d) => d.label), datasets: [Viz.bar({ label: 'Students', data: dist.map((d) => d.students), backgroundColor: P.s1 })] },
            options: { scales: { y: Viz.countAxis(), x: Viz.catAxis() }, plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => plural(c.raw, 'student') } } } },
        });

        // Students practising, per period.
        const eng = r.engagement;
        const noActivity = eng.every((e) => e.quizzes === 0);
        const peak = eng.reduce((max, e) => Math.max(max, e.students), 0);
        $('noteEngagement').textContent = `Peak of ${plural(peak, 'student')} in a ${range.bucket}`;
        toggleEmpty('emptyEngagement', noActivity);
        Viz.chart('chartEngagement', {
            type: 'bar',
            data: { labels: eng.map((e) => e.label), datasets: [Viz.bar({ label: 'Students', data: eng.map((e) => e.students), backgroundColor: P.s1 })] },
            options: {
                scales: { y: Viz.countAxis(), x: tickAxis(8) },
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => { const e = eng[c.dataIndex]; return `${plural(e.students, 'student')} · ${plural(e.quizzes, 'quiz', 'quizzes')} · ${plural(e.items, 'item')}`; } } } },
            },
        });
        // Accuracy by difficulty (ordered → ordinal ramp).
        const diff = r.difficulty;
        toggleEmpty('emptyDifficulty', diff.every((d) => d.answered === 0));
        Viz.chart('chartDifficulty', {
            type: 'bar',
            data: { labels: diff.map((d) => d.label), datasets: [Viz.bar({ label: 'Accuracy', data: diff.map((d) => d.accuracy), backgroundColor: P.ordinal })] },
            options: {
                scales: { y: Viz.percentAxis(), x: Viz.catAxis() },
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => { const d = diff[c.dataIndex]; return d.accuracy === null ? 'Not answered yet' : `${d.accuracy}% correct across ${plural(d.answered, 'answer')}`; } } } },
            },
        });

        // Weakest topics (horizontal); the card grows with the list.
        const weak = r.weak_topics;
        toggleEmpty('emptyWeak', weak.length === 0);
        $('weakWrap').style.height = Math.max(160, weak.length * 30 + 40) + 'px';
        Viz.chart('chartWeak', {
            type: 'bar',
            data: { labels: weak.map((t) => `${t.subject_code} · ${t.name}`), datasets: [Viz.bar({ label: 'Accuracy', data: weak.map((t) => t.accuracy), backgroundColor: P.s1, maxBarThickness: 18, minBarLength: 2 })] },
            options: {
                indexAxis: 'y',
                layout: { padding: { right: 40 } },
                // Long topic names are shortened on the axis (they were being
                // clipped at the card edge); the tooltip keeps the full name.
                scales: { x: Viz.percentAxis(), y: Viz.catAxis({ ticks: { color: P.ink, padding: 6, font: { size: 10 }, callback(v) { const s = this.getLabelForValue(v); return s.length > 38 ? s.slice(0, 37) + '…' : s; } } }) },
                plugins: { legend: { display: false }, tooltip: { callbacks: { title: (items) => items[0].label, label: (c) => { const t = weak[c.dataIndex]; return `${t.accuracy}% across ${plural(t.attempts, 'answer')} by ${plural(t.students, 'student')}`; } } } },
            },
            plugins: [Viz.endLabels((v) => v + '%')],
        });
    }

    // ── Tables ──
    function topicRows(topics, emptyText) {
        return topics.length
            ? topics.map((t) => `<tr><td>${esc(t.name)}</td><td>${esc(t.subject_code)}</td><td class="num"><strong>${t.accuracy}%</strong></td><td class="num">${t.attempts.toLocaleString()}</td><td class="num">${t.students}</td></tr>`).join('')
            : `<tr><td colspan="5"><div class="table-empty">${emptyText}</div></td></tr>`;
    }

    function renderTables(r) {
        $('tableSubjects').innerHTML = r.by_subject.map((s) => `
            <div class="subject-row">
                <div><span class="subj-badge b-${esc(s.code.toLowerCase())}">${esc(s.code)}</span><span class="subject-name">${esc(s.name)}</span></div>
                <div class="bar"><span style="width:${s.accuracy ?? 0}%"></span></div>
                <div class="accuracy">${pct(s.accuracy)}</div>
                <div class="delta ${s.gap === null ? 'na' : (s.gap >= 0 ? 'up' : 'down')}">${s.gap === null ? '—' : (s.gap > 0 ? '+' : '') + s.gap + ' pts'}</div>
                <div><div class="small-meta">${plural(s.students, 'student')}</div><div class="small-meta">${plural(s.items, 'item')}</div></div>
            </div>`).join('') || '<div class="table-empty">No subjects are available.</div>';

        $('tableEngagementNote').textContent = `One row per ${r.range.bucket}`;
        // Engagement and trend share the same period buckets, row for row.
        $('tableEngagement').innerHTML = r.engagement.map((e, i) => `
            <tr><td>${esc(e.label)}</td><td class="num">${e.students}</td><td class="num">${e.quizzes}</td><td class="num">${e.items.toLocaleString()}</td><td class="num">${pct(e.accuracy)}</td><td class="num">${pct(r.trend[i] ? r.trend[i].readiness : null)}</td><td class="num">${e.hours}</td></tr>`).join('');

        $('tableSections').innerHTML = r.by_section.map((s) => `
            <tr${filters.section === s.section ? ' style="background:#fef2f2;"' : ''}><td>${esc(s.section)}</td><td class="num"><strong>${pct(s.accuracy)}</strong></td><td class="num">${s.students}</td><td class="num">${s.items.toLocaleString()}</td></tr>`).join('')
            || '<tr><td colspan="4"><div class="table-empty">No active sections.</div></td></tr>';

        $('tableDifficulty').innerHTML = r.difficulty.map((d) => `
            <tr><td>${esc(d.label)}</td><td class="num">${d.answered.toLocaleString()}</td><td class="num"><strong>${pct(d.accuracy)}</strong></td></tr>`).join('');

        $('tableWeak').innerHTML = topicRows(r.weak_topics, 'No topic is below 60% class accuracy.');
        $('tableStrong').innerHTML = topicRows(r.strong_topics, 'No topic has reached 75% class accuracy yet.');
    }

    // ── Leaderboard ──
    // Accuracy ranking needs the same floor a student needs to be measured at
    // all; below it one lucky 3/3 quiz would top the board.
    const LB_MIN_ITEMS = {{ \App\Services\ChairAnalyticsService::DEVELOPING_ATTEMPTS }};
    const LB_PAGE = 20;
    const MEDALS = ['r1', 'r2', 'r3'];
    let lbRows = [];
    let lbSubjects = [];
    let lbSort = 'correct';
    let lbShowAll = false;

    function renderLeaderboard(rows, range, subjects) {
        lbRows = rows;
        // Per-subject columns only mean something across several subjects;
        // with one subject selected they would just repeat the overall rank.
        lbSubjects = subjects.length > 1 ? subjects : [];
        lbShowAll = false;
        const subject = filters.subject ? $('filterSubject').selectedOptions[0].textContent.trim() : 'All subjects';
        $('lbScope').textContent = [subject, filters.section || 'All sections', `${pretty(range.from)} – ${pretty(range.to)}`].join(' · ');
        drawLeaderboard();
    }

    function drawLeaderboard() {
        const byAccuracy = lbSort === 'accuracy';
        const ranked = byAccuracy
            ? lbRows.filter((s) => s.items >= LB_MIN_ITEMS)
                .slice()
                .sort((a, b) => b.accuracy - a.accuracy || b.items - a.items || a.id - b.id)
            : lbRows;
        const score = (s) => (byAccuracy ? `${s.accuracy}%` : s.correct.toLocaleString());
        const scoreNote = (s) => (byAccuracy ? `of ${plural(s.items, 'item')}` : 'correct answers');
        // Mixed-subject quizzes carry no subject, so a count of 0 is left out.
        const sub = (s) => [s.section, s.subjects ? plural(s.subjects, 'subject') : null].filter(Boolean).map(esc).join(' · ');
        const standing = (s, code) => (s.standings || {})[code];
        const topIn = (s) => lbSubjects.filter((code) => standing(s, code)?.rank === 1);
        const subjectCell = (s, code) => {
            const st = standing(s, code);
            if (!st) { return `<td class="lb-subj"><span class="lb-snone" title="${esc(code)}: no quizzes in this range">—</span></td>`; }
            const tip = `${code}: #${st.rank} of ${st.of} · ${plural(st.correct, 'correct answer')} · ${pct(st.accuracy)} accuracy`;
            return `<td class="lb-subj" title="${esc(tip)}"><span class="lb-srank ${st.rank <= 3 ? 's' + st.rank : ''}">#${st.rank}</span><span class="lb-sacc">${pct(st.accuracy)}</span></td>`;
        };

        $('lbHead').innerHTML = '<tr><th style="width:52px;">Rank</th><th>Student</th><th class="num">Correct</th><th class="num">Items</th><th class="num">Accuracy</th>'
            + lbSubjects.map((code) => `<th class="lb-subj" title="Standing in ${esc(code)}">${esc(code)}</th>`).join('')
            + '<th class="num">Quizzes</th><th class="num">Last active</th></tr>';

        $('lbPodium').innerHTML = ranked.slice(0, 3).map((s, i) => `
            <a class="lb-top" href="${esc(s.url)}">
                <span class="lb-medal ${MEDALS[i]}"><i class="fas fa-trophy"></i></span>
                <span style="min-width:0;">
                    <div class="lb-top-name">${esc(s.name)}</div>
                    <div class="lb-top-meta">#${i + 1} · ${sub(s)}</div>
                    ${topIn(s).length ? `<div class="lb-top-best"><i class="fas fa-crown"></i> #1 in ${esc(topIn(s).join(', '))}</div>` : ''}
                </span>
                <span class="lb-top-score">${score(s)}<small>${scoreNote(s)}</small></span>
            </a>`).join('');

        const shown = lbShowAll ? ranked : ranked.slice(0, LB_PAGE);
        $('lbBody').innerHTML = shown.length
            ? shown.map((s, i) => `
                <tr>
                    <td class="lb-rank">${i + 1}</td>
                    <td>
                        <a class="lb-student" href="${esc(s.url)}">
                            <span class="lb-av">${esc(s.initials)}</span>
                            <span style="min-width:0;"><div class="lb-name">${esc(s.name)}</div><div class="lb-sub">${sub(s)}</div></span>
                        </a>
                    </td>
                    <td class="num"><strong>${s.correct.toLocaleString()}</strong></td>
                    <td class="num">${s.items.toLocaleString()}</td>
                    <td class="num">${pct(s.accuracy)}</td>
                    ${lbSubjects.map((code) => subjectCell(s, code)).join('')}
                    <td class="num">${s.quizzes}</td>
                    <td class="num">${esc(s.last_active)}</td>
                </tr>`).join('')
            : `<tr><td colspan="${7 + lbSubjects.length}"><div class="table-empty">${byAccuracy
                ? `No student answered ${LB_MIN_ITEMS}+ items in this range yet.`
                : 'No student completed a quiz in this range.'} Try a longer date range, e.g. Last 90 days or Last 12 months.</div></td></tr>`;

        $('lbCount').textContent = ranked.length
            ? `Showing ${shown.length} of ${plural(ranked.length, 'ranked student')}` + (byAccuracy ? ` · only students with ${LB_MIN_ITEMS}+ items` : '')
            : '';
        $('lbMore').hidden = lbShowAll || ranked.length <= LB_PAGE;
    }

    document.querySelectorAll('.lb-sort button').forEach((btn) => {
        btn.addEventListener('click', () => {
            lbSort = btn.dataset.sort;
            lbShowAll = false;
            document.querySelectorAll('.lb-sort button').forEach((b) => {
                const on = b === btn;
                b.classList.toggle('active', on);
                b.setAttribute('aria-checked', on ? 'true' : 'false');
            });
            drawLeaderboard();
        });
    });
    $('lbMore').addEventListener('click', () => { lbShowAll = true; drawLeaderboard(); });

    function render(r) {
        renderKpis(r.kpis, r.range);
        renderCharts(r);
        renderTables(r);
        renderLeaderboard(r.leaderboard, r.range, r.leaderboard_subjects);
    }

    // ── Filters ──
    const rangePop = $('rangePop');
    const rangeTrigger = $('rangeTrigger');

    function syncControls() {
        $('rangeLabel').textContent = pretty(filters.from) + ' – ' + pretty(filters.to);
        $('filterSubject').value = filters.subject ?? '';
        $('filterSection').value = filters.section ?? '';
        document.querySelectorAll('.range-presets button').forEach((b) => {
            const [from, to] = presetRange(b.dataset.preset);
            b.classList.toggle('active', from === filters.from && to === filters.to);
        });
    }

    function syncUrl() {
        const params = new URLSearchParams();
        if (!(filters.from === DEFAULTS.from && filters.to === DEFAULTS.to)) { params.set('from', filters.from); params.set('to', filters.to); }
        if (filters.subject) { params.set('subject', filters.subject); }
        if (filters.section) { params.set('section', filters.section); }
        const qs = params.toString();
        history.replaceState(null, '', location.pathname + (qs ? '?' + qs : '') + location.hash);
    }

    function load() {
        if (inflight) { inflight.abort(); }
        const controller = inflight = new AbortController();
        const params = new URLSearchParams({ from: filters.from, to: filters.to });
        if (filters.subject) { params.set('subject', filters.subject); }
        if (filters.section) { params.set('section', filters.section); }

        $('page').classList.add('is-loading');
        $('filterStatus').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating…';
        syncControls();
        syncUrl();

        fetch(ENDPOINT + '?' + params.toString(), { headers: { 'Accept': 'application/json' }, signal: controller.signal })
            .then((res) => { if (!res.ok) { throw new Error(res.status); } return res.json(); })
            .then((data) => {
                // The server normalises bad input (a swapped or too-long range),
                // so adopt what it actually used.
                filters = data.filters;
                syncControls();
                syncUrl();
                render(data.report);
                $('filterStatus').textContent = '';
            })
            .catch((err) => {
                if (err.name === 'AbortError') { return; }
                $('filterStatus').innerHTML = '<i class="fas fa-triangle-exclamation" style="color:#b91c1c;"></i> Could not update — try again.';
            })
            .finally(() => { if (inflight === controller) { $('page').classList.remove('is-loading'); } });
    }

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
    $('filterSection').addEventListener('change', (e) => { filters.section = e.target.value || null; load(); });
    $('filterReset').addEventListener('click', () => { filters = Object.assign({}, DEFAULTS); load(); });

    syncControls();
    render(@json($report));
})();
</script>

    @include('partials.alerts')
</body>
</html>
