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

        /* One page: headline figures and the key charts. */

        #tab-charts .chart-grid { align-items:stretch; }
        .is-loading .chart-grid, .is-loading .table-card { opacity:.55; pointer-events:none; transition:opacity .2s; }
        .confidence { display:inline-block; font-size:10px; font-weight:700; padding:2px 9px; border-radius:20px; margin-left:6px; vertical-align:middle; }
        .confidence.high { color:#047857; background:#e7f6ef; }
        .confidence.medium { color:#b45309; background:#fef3c7; }
        .confidence.low { color:#b91c1c; background:#fde8e8; }

        /* Tables view */
        .table-card { background:#fff; border-radius:14px; padding:20px 22px; margin-bottom:16px; box-shadow:0 1px 2px rgba(16,24,40,.04), 0 4px 14px rgba(16,24,40,.06); }
        .table-card h3 { font-size:14px; font-weight:700; color:#1a1a1a; display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:12px; }
        .table-card h3 small { font-size:10px; font-weight:500; color:#aaa; }
        .table-pair { display:grid; grid-template-columns:minmax(0, 1fr) minmax(0, 1fr); gap:16px; }
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

        @media (max-width: 1050px) { .table-pair { grid-template-columns:minmax(0, 1fr); } }
        @media (max-width: 640px) {
            .subject-row { grid-template-columns:minmax(0, 1fr) 65px; }
            .subject-row .bar, .subject-row .small-meta, .subject-row .delta, .subject-row.head { display:none; }
        }
            /* Top students + topic tables */
        .top-card .chart-card-head { flex-wrap:wrap; }
        .lb-sort { display:flex; gap:3px; background:#f4f5f7; border-radius:9px; padding:3px; }
        .lb-sort button { padding:5px 10px; border:none; border-radius:7px; background:none; color:#777; font:600 11px 'Poppins',sans-serif; cursor:pointer; }
        .lb-sort button.active { background:#fff; color:var(--primary); box-shadow:0 1px 3px rgba(0,0,0,.1); }
        .tp-podium { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; align-items:end; justify-items:center; margin:10px 0 12px; }
        .tp-top { position:relative; width:100%; max-width:200px; text-align:center; padding:16px 8px 12px; border:1px solid #eee; border-radius:14px; background:#fafbfc; text-decoration:none; color:inherit; }
        .tp-top.p1 { background:linear-gradient(#fffdf3,#fff7dc); border-color:#f3e3a6; padding-bottom:18px; }
        .tp-top:hover { border-color:#e3c4c4; }
        .tp-medal { position:absolute; top:-10px; left:50%; transform:translateX(-50%); width:22px; height:22px; border-radius:50%; color:#fff; font-size:11px; font-weight:700; line-height:22px; }
        .p1 .tp-medal { background:#d4a017; } .p2 .tp-medal { background:#9ca3af; } .p3 .tp-medal { background:#b87333; }
        .tp-av { width:42px; height:42px; margin:0 auto 6px; border-radius:50%; background:var(--primary); color:#fff; display:grid; place-items:center; font-size:13px; font-weight:700; }
        .tp-name { font-size:12px; font-weight:600; color:#1a1a1a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .tp-meta { font-size:10px; color:#999; }
        .tp-score { margin-top:4px; font-size:18px; font-weight:700; color:var(--primary); line-height:1.1; }
        .tp-score small { display:block; font-size:9.5px; font-weight:500; color:#aaa; }
        .tp-row { display:flex; align-items:center; gap:10px; padding:8px 10px; border:1px solid #eee; border-radius:10px; margin-bottom:6px; text-decoration:none; color:inherit; }
        .tp-row:hover { border-color:#e3c4c4; background:#fdf8f8; }
        .tp-rank { width:22px; text-align:center; font-size:11.5px; font-weight:700; color:#888; }
        .tp-row .tp-name { flex:1; min-width:0; }
        .tp-row b { font-size:12.5px; color:#1a1a1a; }
        .tp-empty { text-align:center; padding:26px 10px; color:#aaa; font-size:12px; }
        .topic-tables { margin-bottom:16px; }
        .table-note { font-size:11px; color:#999; margin:-6px 0 10px; }
        .topic-filter { font:500 12px 'Poppins',sans-serif; padding:6px 10px; border:1px solid #e5e7eb; border-radius:9px; background:#fff; color:#444; max-width:46%; }
        .tp-empty-slot { background:#fafafa; border:1.5px dashed #e3e3e8; color:#bbb; }
        .tp-empty-slot .tp-av { background:#ececf0; color:#c4c4cc; }
        .tp-empty-slot .tp-name, .tp-empty-slot .tp-score { color:#b5b5be; }
        .tp-empty-slot .tp-medal { background:#d6d6dc !important; }
        .tp-row-empty { border-style:dashed; background:#fafafa; color:#b5b5be; }
        .tp-row-empty b, .tp-row-empty .tp-rank { color:#b5b5be; font-weight:600; }
        .top-card { display:flex; flex-direction:column; }
        .top-card .tp-viewall { margin-top:auto; }
        .tp-list { margin-bottom:10px; }
        .tp-viewall { display:block; width:100%; text-align:right; padding-top:10px; background:none; border:0; border-top:1px solid #f0f0f2; color:var(--primary); font:600 12px 'Poppins',sans-serif; cursor:pointer; padding:4px 0; }
        .tp-viewall:hover { text-decoration:underline; }
        .lbm-overlay { position:fixed; inset:0; z-index:1000; background:rgba(15,10,10,.5); display:none; align-items:center; justify-content:center; padding:20px; }
        .lbm-overlay.open { display:flex; }
        .lbm { background:#fff; border-radius:16px; width:100%; max-width:860px; max-height:88vh; display:flex; flex-direction:column; box-shadow:0 20px 50px rgba(0,0,0,.3); }
        .lbm-head { display:flex; justify-content:space-between; gap:12px; padding:20px 22px 10px; }
        .lbm-head h3 { font-size:16px; font-weight:700; }
        .lbm-scope { font-size:11px; color:#999; margin-top:2px; }
        .lbm-x { border:0; background:#f4f5f7; width:32px; height:32px; border-radius:50%; cursor:pointer; color:#666; }
        .lbm-tools { display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; padding:0 22px 12px; }
        .lbm-tools input[type=search] { flex:1; min-width:180px; max-width:340px; padding:9px 14px; border:1px solid #e5e7eb; border-radius:10px; font:13px 'Poppins',sans-serif; outline:none; }
        .lbm-body { overflow:auto; padding:0 22px; }
        .lbm-body thead th { position:sticky; top:0; background:#fff; z-index:1; }
        .lb-rank { font-weight:700; text-align:center; }
        .lb-student { display:flex; align-items:center; gap:10px; text-decoration:none; color:inherit; }
        .lb-av { width:28px; height:28px; border-radius:50%; background:var(--primary); color:#fff; display:grid; place-items:center; font-size:10px; font-weight:700; flex:none; }
        .lb-name { font-size:12px; font-weight:600; color:#1a1a1a; } .lb-student:hover .lb-name { color:var(--primary); text-decoration:underline; }
        .lb-sub { font-size:10px; color:#aaa; }
        .lbm-foot { padding:12px 22px; font-size:11px; color:#999; border-top:1px solid #f0f0f2; }
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

    <div id="tab-charts">
        <section class="chart-grid cols-2" aria-label="Charts">
            <article class="chart-card" id="readiness-trend">
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

            <article class="chart-card top-card" aria-label="Top students">
                <div class="chart-card-head">
                    <span class="chart-card-icon"><i class="fas fa-trophy"></i></span>
                    <span class="chart-card-title">Top Students</span>
                    <select class="topic-filter" id="tpSubject" aria-label="Show top students for a subject" style="margin-left:auto;"></select>
                </div>
                <div class="chart-card-note" id="lbScope">&nbsp;</div>
                <div class="tp-podium" id="tpPodium"></div>
                <div class="tp-list" id="tpList"></div>
                <button type="button" class="tp-viewall" id="lbOpen">View full leaderboard <i class="fas fa-arrow-right"></i></button>
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
                    <span class="chart-card-title">Section Engagement</span>
                </div>
                <div class="chart-card-note" id="noteSections">&nbsp;</div>
                <div class="chart-canvas-wrap tall">
                    <canvas id="chartSections" role="img" aria-label="Share of each section's students who completed a quiz"></canvas>
                    <div class="viz-empty chart-empty" id="emptySections" hidden>No section has quiz activity in this range.</div>
                </div>
                <div class="chart-card-cap">% of each section's active students who completed a quiz in the range — the selected section is highlighted</div>
            </article>

        </section>

        <section class="table-pair topic-tables" aria-label="Topics">
            <div class="table-card">
                <h3><span><i class="fas fa-triangle-exclamation" style="color:#b91c1c;margin-right:7px;"></i>Top 5 Weakest Topics</span>
                    <select class="topic-filter" id="weakSubject" aria-label="Filter weakest topics by subject"></select></h3>
                <div class="table-note">Under 60% class accuracy, with {{ \App\Services\ChairAnalyticsService::TOPIC_MIN_ATTEMPTS }}+ answers in the range.</div>
                <table class="viz-table"><thead><tr><th>Topic</th><th>Subject</th><th class="num">Accuracy</th><th class="num">Answers</th></tr></thead><tbody id="tableWeak"></tbody></table>
            </div>
            <div class="table-card">
                <h3><span><i class="fas fa-circle-check" style="color:#047857;margin-right:7px;"></i>Top 5 Strongest Topics</span>
                    <select class="topic-filter" id="strongSubject" aria-label="Filter strongest topics by subject"></select></h3>
                <div class="table-note">75% and above class accuracy, with {{ \App\Services\ChairAnalyticsService::TOPIC_MIN_ATTEMPTS }}+ answers in the range.</div>
                <table class="viz-table"><thead><tr><th>Topic</th><th>Subject</th><th class="num">Accuracy</th><th class="num">Answers</th></tr></thead><tbody id="tableStrong"></tbody></table>
            </div>
        </section>
    </div>

    <div class="method-note" style="margin:0 0 16px;">
        <i class="fas fa-circle-info"></i>
        Accuracy and practice count quizzes completed within the date range. Readiness and the pass projection are cumulative as of the range end, since a student's standing is built from everything they have practised.
    </div>
</main>

<div class="lbm-overlay" id="lbModal" role="dialog" aria-modal="true" aria-labelledby="lbModalTitle">
    <div class="lbm">
        <div class="lbm-head">
            <div>
                <h3 id="lbModalTitle"><i class="fas fa-trophy" style="color:#d4a017;margin-right:8px;"></i>Student Leaderboard</h3>
                <div class="lbm-scope" id="lbmScope"></div>
            </div>
            <button type="button" class="lbm-x" id="lbClose" aria-label="Close"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="lbm-tools">
            <input type="search" id="lbSearch" placeholder="Search student or section" aria-label="Search students">
            <span class="lb-sort" role="radiogroup" aria-label="Rank students by">
                <button type="button" class="active" data-sort="correct" role="radio" aria-checked="true">Most correct</button>
                <button type="button" data-sort="accuracy" role="radio" aria-checked="false">Accuracy</button>
            </span>
        </div>
        <div class="lbm-body">
            <table class="viz-table lb-table">
                <thead><tr><th style="width:52px;">Rank</th><th>Student</th><th class="num">Correct</th><th class="num">Items</th><th class="num">Accuracy</th><th class="num">Quizzes</th><th class="num">Last active</th></tr></thead>
                <tbody id="lbmBody"></tbody>
            </table>
        </div>
        <div class="lbm-foot" id="lbmCount"></div>
    </div>
</div>

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

        // Section engagement — share of each section's students who practised.
        const secs = r.by_section.map((x) => Object.assign({}, x, { rate: x.enrolled ? Math.round(x.students / x.enrolled * 100) : null }));
        $('noteSections').textContent = filters.section ? `${filters.section} vs the other sections` : `${plural(secs.length, 'active section')} compared`;
        toggleEmpty('emptySections', allNull(secs.map((x) => x.rate)) || secs.every((x) => !x.students));
        // Value on top of each (vertical) bar.
        const topLabels = {
            id: 'sectionTopLabels',
            afterDatasetsDraw(chart) {
                const ctx = chart.ctx;
                ctx.save();
                ctx.font = "600 10.5px 'Poppins', sans-serif";
                ctx.fillStyle = P.ink;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';
                chart.getDatasetMeta(0).data.forEach((el, i) => { if (secs[i].rate !== null) { ctx.fillText(secs[i].rate + '%', el.x, el.y - 4); } });
                ctx.restore();
            },
        };
        Viz.chart('chartSections', {
            type: 'bar',
            data: {
                labels: secs.map((x) => x.section),
                datasets: [Viz.bar({ label: 'Students practising', data: secs.map((x) => x.rate), backgroundColor: secs.map((x) => (!filters.section || x.section === filters.section ? P.s1 : 'rgba(163,43,43,.28)')), maxBarThickness: 42 })],
            },
            options: {
                layout: { padding: { top: 18 } },
                scales: { y: Viz.percentAxis(), x: tickAxis(12) },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (c) => {
                        const x = secs[c.dataIndex];
                        return x.rate === null ? 'No students enrolled' : `${x.rate}% · ${x.students} of ${plural(x.enrolled, 'student')} · ${plural(x.quizzes, 'quiz', 'quizzes')}`;
                    } } },
                },
            },
            plugins: [topLabels],
        });
    }

    // ── Top students ──
    const LB_MIN_ITEMS = {{ \App\Services\ChairAnalyticsService::DEVELOPING_ATTEMPTS }};
    let lbRows = [];
    let lbSort = 'correct';
    let rangeText = '';

    function renderTop(rows, range, subjects) {
        lbRows = rows || [];
        fillSubjectFilter($('tpSubject'), subjects);
        rangeText = `${pretty(range.from)} – ${pretty(range.to)}`;
        drawTop();
    }

    // Rows for the subject picked on the card: that subject's own totals and order.
    function scopedRows() {
        const code = $('tpSubject').value;
        if (!code) { return lbRows; }
        return lbRows
            .filter((s) => s.standings && s.standings[code])
            .map((s) => Object.assign({}, s, { correct: s.standings[code].correct, items: s.standings[code].items, accuracy: s.standings[code].accuracy }))
            .sort((x, y) => y.correct - x.correct || x.id - y.id);
    }

    function scopeText() {
        const code = $('tpSubject').value;
        return [code || (filters.subject ? $('filterSubject').selectedOptions[0].textContent.trim() : 'All subjects'), filters.section || 'All sections', rangeText].join(' · ');
    }

    function drawTop() {
        const ranked = scopedRows().slice(0, 5);
        $('lbScope').textContent = scopeText();
        const slot = (i) => {
            const s = ranked[i];
            return s ? `
            <a class="tp-top p${i + 1}" href="${esc(s.url)}">
                <span class="tp-medal">${i + 1}</span>
                <div class="tp-av">${esc(s.initials)}</div>
                <div class="tp-name">${esc(s.name)}</div>
                <div class="tp-meta">${esc(s.section || '')}</div>
                <div class="tp-score">${s.correct.toLocaleString()}<small>correct answers</small></div>
            </a>` : `
            <div class="tp-top tp-empty-slot p${i + 1}" aria-label="Rank ${i + 1}: no student yet">
                <span class="tp-medal">${i + 1}</span>
                <div class="tp-av"><i class="fas fa-user"></i></div>
                <div class="tp-name">No student yet</div>
                <div class="tp-meta">&nbsp;</div>
                <div class="tp-score">—<small>&nbsp;</small></div>
            </div>`;
        };
        // Podium order: 2nd, 1st, 3rd — always three slots, so a lone leader stays centred.
        $('tpPodium').innerHTML = [1, 0, 2].map(slot).join('');
        // Ranks 4 and 5 are always listed; an empty one is held with a placeholder.
        $('tpList').innerHTML = [3, 4].map((i) => {
            const s = ranked[i];
            return s ? `
            <a class="tp-row" href="${esc(s.url)}">
                <span class="tp-rank">${i + 1}</span>
                <span class="tp-name"><b>${esc(s.name)}</b><div class="tp-meta">${esc(s.section || '')} · correct answers</div></span>
                <b>${s.correct.toLocaleString()}</b>
            </a>` : `
            <div class="tp-row tp-row-empty" aria-label="Rank ${i + 1}: no student yet">
                <span class="tp-rank">${i + 1}</span>
                <span class="tp-name"><b>No student yet</b></span>
                <b>—</b>
            </div>`;
        }).join('');
        if (lbModal.classList.contains('open')) { drawFull(); }
    }
    $('tpSubject').addEventListener('change', drawTop);
    // The popup has its own sort toggle (most correct / accuracy).
    document.querySelectorAll('#lbModal .lb-sort button').forEach((btn) => btn.addEventListener('click', () => {
        lbSort = btn.dataset.sort;
        document.querySelectorAll('#lbModal .lb-sort button').forEach((x) => { x.classList.toggle('active', x === btn); x.setAttribute('aria-checked', x === btn ? 'true' : 'false'); });
        drawFull();
    }));

    // ── Full leaderboard popup ──
    function rankedRows() {
        const rows = scopedRows();
        return lbSort === 'accuracy'
            ? rows.filter((s) => s.items >= LB_MIN_ITEMS).slice().sort((a, b) => b.accuracy - a.accuracy || b.items - a.items || a.id - b.id)
            : rows;
    }

    function drawFull() {
        const q = $('lbSearch').value.trim().toLowerCase();
        const ranked = rankedRows().map((s, i) => Object.assign({ rank: i + 1 }, s));
        const shown = q ? ranked.filter((s) => (s.name + ' ' + (s.section || '')).toLowerCase().includes(q)) : ranked;
        $('lbmBody').innerHTML = shown.length ? shown.map((s) => `
            <tr>
                <td class="lb-rank">${s.rank}</td>
                <td><a class="lb-student" href="${esc(s.url)}"><span class="lb-av">${esc(s.initials)}</span><span><div class="lb-name">${esc(s.name)}</div><div class="lb-sub">${esc(s.section || '')}</div></span></a></td>
                <td class="num"><strong>${s.correct.toLocaleString()}</strong></td>
                <td class="num">${s.items.toLocaleString()}</td>
                <td class="num">${pct(s.accuracy)}</td>
                <td class="num">${s.quizzes}</td>
                <td class="num">${esc(s.last_active)}</td>
            </tr>`).join('')
            : '<tr><td colspan="7"><div class="table-empty">No student matches.</div></td></tr>';
        $('lbmCount').textContent = `${shown.length} of ${plural(ranked.length, 'ranked student')}` + (lbSort === 'accuracy' ? ` · only students with ${LB_MIN_ITEMS}+ items` : '');
        $('lbmScope').textContent = scopeText();
    }
    const lbModal = $('lbModal');
    const closeFull = () => lbModal.classList.remove('open');
    $('lbOpen').addEventListener('click', () => { lbModal.classList.add('open'); $('lbSearch').value = ''; drawFull(); });
    $('lbClose').addEventListener('click', closeFull);
    lbModal.addEventListener('click', (e) => { if (e.target === lbModal) { closeFull(); } });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { closeFull(); } });
    $('lbSearch').addEventListener('input', drawFull);

    // ── Weakest / strongest topics (top 5, filtered by subject on the page) ──
    let topicData = { weak: [], strong: [] };

    function fillSubjectFilter(select, subjects) {
        const keep = select.value;
        select.innerHTML = '<option value="">All subjects</option>' + subjects.map((x) => `<option value="${esc(x.code)}">${esc(x.code)}</option>`).join('');
        select.value = subjects.some((x) => x.code === keep) ? keep : '';
    }

    function drawTopics() {
        const rows = (list, code, empty) => {
            const top = list.filter((t) => !code || t.subject_code === code).slice(0, 5);
            return top.length
                ? top.map((t) => `<tr><td>${esc(t.name)}</td><td>${esc(t.subject_code)}</td><td class="num"><strong>${t.accuracy}%</strong></td><td class="num">${t.attempts.toLocaleString()}</td></tr>`).join('')
                : `<tr><td colspan="4"><div class="table-empty">${empty}</div></td></tr>`;
        };
        $('tableWeak').innerHTML = rows(topicData.weak, $('weakSubject').value, 'No topic is below 60% class accuracy here.');
        $('tableStrong').innerHTML = rows(topicData.strong, $('strongSubject').value, 'No topic has reached 75% class accuracy here yet.');
    }
    ['weakSubject', 'strongSubject'].forEach((id) => $(id).addEventListener('change', drawTopics));

    function render(r) {
        renderKpis(r.kpis, r.range);
        renderCharts(r);
        renderTop(r.leaderboard, r.range, r.by_subject);
        topicData = { weak: r.weak_topics, strong: r.strong_topics };
        fillSubjectFilter($('weakSubject'), r.by_subject);
        fillSubjectFilter($('strongSubject'), r.by_subject);
        drawTopics();
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
