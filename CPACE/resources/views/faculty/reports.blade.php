<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; --orange:#f59e0b; --ink:#1f2937; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:16px; position:relative; z-index:100; }
        .topbar-left { display:flex; align-items:center; gap:12px; }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .page-title { font-size:26px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:white; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }
        .btn-soft { background:var(--primary-light); color:var(--primary); }
        .flash { background:#d1fae5; border:1px solid #6ee7b7; color:#065f46; padding:10px 14px; border-radius:9px; font-size:13px; margin-bottom:16px; }
        .report-shell { display:grid; grid-template-columns:280px minmax(600px, 1fr) 290px; gap:18px; align-items:start; }
        .tool-panel, .export-panel { background:white; border-radius:14px; padding:18px; }
        .panel-title { font-size:13px; font-weight:700; color:#1a1a1a; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
        .field { margin-bottom:14px; }
        .field label { display:block; font-size:11px; font-weight:700; color:#777; margin-bottom:6px; text-transform:uppercase; letter-spacing:.4px; }
        select, input { width:100%; font-family:'Poppins',sans-serif; font-size:13px; border:1px solid #e0e0e0; border-radius:8px; padding:9px 11px; color:#555; background:white; outline:none; }
        select:focus, input:focus { border-color:var(--primary); }
        .check-list { display:flex; flex-direction:column; gap:9px; margin-top:2px; }
        .check-row { display:flex; align-items:center; gap:8px; font-size:12px; color:#555; cursor:pointer; }
        .check-row input { width:auto; }
        .preview-wrap { background:#e5e7eb; border-radius:14px; padding:24px; overflow:auto; min-height:760px; }
        .preview-toolbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
        .preview-title { font-size:13px; font-weight:700; color:#444; display:flex; align-items:center; gap:8px; }
        .zoom-group { display:flex; align-items:center; gap:8px; font-size:12px; color:#777; }
        .paper { width:794px; min-height:1123px; background:white; margin:0 auto; box-shadow:0 12px 28px rgba(0,0,0,.14); padding:48px 54px; color:var(--ink); transform-origin:top center; }
        .paper-head { display:flex; justify-content:space-between; gap:24px; border-bottom:3px solid var(--primary); padding-bottom:18px; margin-bottom:22px; }
        .brand { display:flex; gap:12px; align-items:center; }
        .brand-mark { width:48px; height:48px; border-radius:10px; background:var(--primary-light); display:flex; align-items:center; justify-content:center; color:var(--primary); font-size:22px; }
        .brand h1 { font-size:20px; color:var(--primary); line-height:1.1; }
        .brand p, .report-meta p { font-size:10px; color:#6b7280; margin-top:3px; }
        .report-meta { text-align:right; }
        .report-meta strong { display:block; font-size:12px; color:#111827; margin-bottom:3px; }
        .doc-title { margin-bottom:18px; }
        .doc-title h2 { font-size:18px; color:#111827; margin-bottom:5px; }
        .doc-title p { font-size:11px; color:#6b7280; line-height:1.55; }
        .summary-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:18px; }
        .summary-box { border:1px solid #e5e7eb; border-radius:8px; padding:10px; }
        .summary-box .num { font-size:18px; font-weight:700; color:#111827; line-height:1; }
        .summary-box .lbl { font-size:9px; color:#6b7280; margin-top:5px; text-transform:uppercase; letter-spacing:.35px; }
        .paper-section { margin-top:18px; }
        .section-head { display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:9px; }
        .section-head h3 { font-size:13px; color:#111827; }
        .section-head span { font-size:9px; color:#9ca3af; }
        .chart-grid { display:grid; grid-template-columns:1.1fr .9fr; gap:14px; }
        .paper-card { border:1px solid #e5e7eb; border-radius:8px; padding:12px; }
        .bar-row { display:grid; grid-template-columns:46px 1fr 34px; gap:8px; align-items:center; margin-bottom:9px; }
        .bar-row:last-child { margin-bottom:0; }
        .bar-label, .bar-val { font-size:10px; color:#4b5563; font-weight:700; }
        .bar-track { height:8px; background:#f3f4f6; border-radius:99px; overflow:hidden; }
        .bar-fill { height:100%; border-radius:99px; }
        .chart-caption { font-size:9px; color:#6b7280; margin-top:7px; line-height:1.5; }
        .dist-row { display:grid; grid-template-columns:74px 1fr 28px; gap:8px; align-items:center; margin-bottom:8px; }
        .dist-row:last-child { margin-bottom:0; }
        .dist-row span { font-size:10px; color:#4b5563; }
        .report-table { width:100%; border-collapse:collapse; border:1px solid #e5e7eb; }
        .report-table th { background:#fafafa; color:#6b7280; font-size:9px; text-align:left; padding:8px; text-transform:uppercase; letter-spacing:.3px; border-bottom:1px solid #e5e7eb; }
        .report-table td { font-size:10px; padding:8px; border-bottom:1px solid #f1f1f1; vertical-align:top; }
        .report-table tr:last-child td { border-bottom:none; }
        .status { display:inline-flex; padding:2px 7px; border-radius:99px; font-size:9px; font-weight:700; }
        .status-red { background:#fde8e8; color:#c0392b; }
        .status-amber { background:#fef3c7; color:#b45309; }
        .status-green { background:#d1fae5; color:#047857; }
        .status-gray { background:#eef2f7; color:#475569; }
        .two-col { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .recommend-list { padding-left:16px; color:#4b5563; font-size:10px; line-height:1.7; }
        .empty-note { font-size:10px; color:#9ca3af; padding:10px; text-align:center; }
        .signature-row { display:grid; grid-template-columns:1fr 1fr; gap:60px; margin-top:34px; }
        .sig-line { border-top:1px solid #9ca3af; padding-top:7px; font-size:10px; color:#4b5563; text-align:center; }
        .paper-foot { margin-top:22px; padding-top:10px; border-top:1px solid #e5e7eb; font-size:9px; color:#9ca3af; display:flex; justify-content:space-between; }
        .export-actions { display:grid; gap:8px; margin-bottom:18px; }
        .export-actions .btn { width:100%; }
        .mini-stat { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #f1f1f1; }
        .mini-stat:last-child { border-bottom:none; }
        .mini-stat span { font-size:11px; color:#777; }
        .mini-stat strong { font-size:13px; color:#1a1a1a; }
        .outline-list { display:flex; flex-direction:column; gap:8px; }
        .outline-item { display:flex; gap:9px; align-items:flex-start; font-size:12px; color:#555; }
        .outline-item i { color:var(--primary); width:15px; margin-top:2px; }
        .csv-preview { border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; margin-top:12px; }
        .csv-row { display:grid; grid-template-columns:1.1fr .7fr .7fr .8fr; }
        .csv-row span { font-size:9px; padding:7px; border-right:1px solid #eee; border-bottom:1px solid #eee; color:#555; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .csv-row span:last-child { border-right:none; }
        .csv-row.head span { background:#fafafa; color:#6b7280; font-weight:700; text-transform:uppercase; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
        .a0{animation:fadeUp .4s ease both} .a1{animation:fadeUp .4s .07s ease both}
        @media (max-width: 1260px) {
            .report-shell { grid-template-columns:260px 1fr; }
            .export-panel { grid-column:1 / -1; }
        }
        @media (max-width: 980px) {
            .report-shell { grid-template-columns:1fr; }
            .paper { transform-origin:top left; width:720px; }
        }
        @media (max-width: 768px) {
            .topbar { flex-direction:column; align-items:flex-start; }
            .topbar-right { width:100%; justify-content:flex-end; flex-wrap:wrap; }
            .preview-wrap { padding:14px; }
            .paper { width:680px; padding:36px; }
        }
        /* Print: only the paper, at full width. */
        @media print {
            .sidebar, .topbar, .tool-panel, .export-panel, .preview-toolbar, .flash { display:none !important; }
            body { background:white; }
            .main { margin:0 !important; padding:0 !important; }
            .report-shell { display:block !important; }
            .preview-wrap { background:white !important; padding:0 !important; min-height:auto !important; overflow:visible !important; }
            .paper { width:auto !important; min-height:auto !important; box-shadow:none !important; margin:0 !important; padding:0 !important; transform:none !important; }
            @page { size:A4 portrait; margin:14mm; }
        }
    </style>
</head>
<body>

@include('partials.faculty-sidebar', ['active' => 'reports'])

@php
    $f = $filters;
    $inc = fn ($key) => in_array($key, $f['include'], true);
    // The include checkboxes drive the Class Performance / At-Risk layouts.
    // Subject Mastery and Question Quality reports render their own body.
    $isCustomBody = in_array($f['report'], ['subject_mastery', 'question_quality'], true);
@endphp

<main class="main">
    <div class="topbar a0">
        <div class="topbar-left">
            <div>
                <div class="page-title">Reports</div>
                <div class="page-sub">Build paper-ready student performance reports for PDF, print, and CSV export.</div>
            </div>
        </div>
        <div class="topbar-right">
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('reportPaper').scrollIntoView({behavior:'smooth'})"><i class="fas fa-eye"></i> Preview</button>
            <a class="btn btn-ghost" href="{{ route('faculty.reports.export', $activeQuery) }}"><i class="fas fa-file-csv"></i> CSV</a>
            <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-file-pdf"></i> PDF</button>
            @include('partials.topbar-actions')
        </div>
    </div>

    @if(session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif

    <div class="report-shell a1">
        <aside class="tool-panel">
            <div class="panel-title"><i class="fas fa-sliders"></i> Report Setup</div>
            <form method="GET" action="{{ route('faculty.reports') }}" id="reportForm">
                <div class="field">
                    <label>Report Type</label>
                    <select name="report" onchange="document.getElementById('reportForm').submit()">
                        @foreach($reportTypes as $key => $label)
                            <option value="{{ $key }}" @selected($f['report'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Subject Scope</label>
                    <select name="scope" onchange="document.getElementById('reportForm').submit()">
                        <option value="">Assigned Subjects</option>
                        @foreach($assigned as $subject)
                            <option value="{{ $subject->id }}" @selected($f['scope'] === $subject->id)>{{ $subject->code }} — {{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Date Range</label>
                    <select name="range" onchange="document.getElementById('reportForm').submit()">
                        <option value="term" @selected($f['range'] === 'term')>Current Term</option>
                        <option value="30" @selected($f['range'] === '30')>Last 30 Days</option>
                        <option value="7" @selected($f['range'] === '7')>Last 7 Days</option>
                        <option value="all" @selected($f['range'] === 'all')>All Time</option>
                    </select>
                </div>
                <div class="field">
                    <label>Student Group</label>
                    <select name="group" onchange="document.getElementById('reportForm').submit()">
                        <option value="all" @selected($f['group'] === 'all')>All Sections</option>
                        @foreach($sections as $section)
                            <option value="{{ $section }}" @selected($f['group'] === $section)>{{ $section }}</option>
                        @endforeach
                        <option value="at_risk" @selected($f['group'] === 'at_risk')>At-Risk Only</option>
                    </select>
                </div>
                <div class="field">
                    <label>Include Sections</label>
                    <div class="check-list">
                        <label class="check-row"><input type="checkbox" name="include[]" value="summary" @checked($inc('summary'))> Executive summary</label>
                        <label class="check-row"><input type="checkbox" name="include[]" value="charts" @checked($inc('charts'))> Performance charts</label>
                        <label class="check-row"><input type="checkbox" name="include[]" value="atrisk" @checked($inc('atrisk'))> At-risk students</label>
                        <label class="check-row"><input type="checkbox" name="include[]" value="weak" @checked($inc('weak'))> Weak topics</label>
                        <label class="check-row"><input type="checkbox" name="include[]" value="recommendations" @checked($inc('recommendations'))> Recommendations</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-soft" style="width:100%;"><i class="fas fa-rotate"></i> Regenerate Preview</button>
            </form>
        </aside>

        <section class="preview-wrap">
            <div class="preview-toolbar">
                <div class="preview-title"><i class="fas fa-file-lines"></i> Paper Preview</div>
                <div class="zoom-group">
                    <button type="button" class="btn btn-ghost" style="padding:7px 10px;" onclick="zoomPaper(-10)"><i class="fas fa-minus"></i></button>
                    <span id="zoomVal">100%</span>
                    <button type="button" class="btn btn-ghost" style="padding:7px 10px;" onclick="zoomPaper(10)"><i class="fas fa-plus"></i></button>
                </div>
            </div>

            <article class="paper" id="reportPaper">
                <header class="paper-head">
                    <div class="brand">
                        <div class="brand-mark"><i class="fas fa-graduation-cap"></i></div>
                        <div>
                            <h1>CPACE</h1>
                            <p>CPA Reviewer Faculty Report</p>
                        </div>
                    </div>
                    <div class="report-meta">
                        <strong>{{ $reportLabel }}</strong>
                        <p>Prepared by: {{ Auth::user()->name }}</p>
                        <p>Scope: {{ $scopeLabel }}</p>
                        <p>Coverage: {{ $rangeLabel }}@if($f['group'] !== 'all') · {{ $f['group'] === 'at_risk' ? 'At-Risk Only' : $f['group'] }}@endif</p>
                        <p>Generated: {{ $generatedAt->format('M d, Y g:i A') }}</p>
                    </div>
                </header>

                <section class="doc-title">
                    <h2>Student Performance and Intervention Report</h2>
                    <p>This report summarizes quiz activity, accuracy, weak topic concentration, and recommended interventions for students under the selected faculty subject scope.</p>
                </section>

                {{-- Executive summary (shared) --}}
                @if($isCustomBody || $inc('summary'))
                    <section class="summary-grid">
                        <div class="summary-box"><div class="num">{{ $stats['students'] }}</div><div class="lbl">Active Students</div></div>
                        <div class="summary-box"><div class="num">{{ $stats['accuracy'] }}%</div><div class="lbl">Class Accuracy</div></div>
                        <div class="summary-box"><div class="num">{{ $stats['at_risk'] }}</div><div class="lbl">At Risk</div></div>
                        <div class="summary-box"><div class="num">{{ $stats['weak_topics'] }}</div><div class="lbl">Weak Topics</div></div>
                    </section>
                @endif

                @if($stats['students'] === 0 && ! $isCustomBody)
                    <div class="paper-card"><p class="empty-note">No completed quiz activity in the selected scope and date range yet. Try widening the date range to “All Time” or changing the subject scope.</p></div>
                @endif

                {{-- ══ CLASS PERFORMANCE SUMMARY ══ --}}
                @if($f['report'] === 'class_summary' || $f['report'] === 'at_risk')

                    @if($f['report'] === 'class_summary' && $inc('charts') && $stats['students'] > 0)
                        <section class="paper-section">
                            <div class="section-head">
                                <h3>Performance Charts</h3>
                                <span>Based on completed non-training quizzes</span>
                            </div>
                            <div class="chart-grid">
                                <div class="paper-card">
                                    <div class="section-head" style="margin-bottom:11px;">
                                        <h3>Subject Accuracy</h3>
                                        <span>{{ $scopeLabel }}</span>
                                    </div>
                                    @forelse($subjectBars as $bar)
                                        <div class="bar-row">
                                            <div class="bar-label">{{ $bar['code'] }}</div>
                                            <div class="bar-track"><div class="bar-fill" style="width:{{ $bar['accuracy'] }}%;background:{{ $bar['color'] }};"></div></div>
                                            <div class="bar-val">{{ $bar['accuracy'] }}%</div>
                                        </div>
                                    @empty
                                        <p class="empty-note">No subject activity in range.</p>
                                    @endforelse
                                </div>
                                <div class="paper-card">
                                    <div class="section-head" style="margin-bottom:6px;">
                                        <h3>Score Distribution</h3>
                                        <span>{{ $distribution['total'] }} students</span>
                                    </div>
                                    @foreach($distribution['bands'] as $r)
                                        <div class="dist-row">
                                            <span>{{ $r['label'] }}</span>
                                            <div class="bar-track"><div class="bar-fill" style="width:{{ $r['pct'] }}%;background:{{ $r['color'] }};"></div></div>
                                            <span>{{ $r['count'] }}</span>
                                        </div>
                                    @endforeach
                                    <p class="chart-caption">Distribution of average per-student accuracy across the selected scope.</p>
                                </div>
                            </div>
                        </section>
                    @endif

                    @if($inc('atrisk') || $f['report'] === 'at_risk')
                        <section class="paper-section">
                            <div class="section-head">
                                <h3>{{ $f['report'] === 'at_risk' ? 'At-Risk Student List' : 'At-Risk Students' }}</h3>
                                <span>Sorted by intervention priority</span>
                            </div>
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Student</th><th>Section</th><th>Accuracy</th>
                                        <th>Weak Area</th><th>Activity</th><th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $roster = $f['report'] === 'at_risk' ? $students->sortBy('score') : $atRisk; @endphp
                                    @forelse($roster as $r)
                                        <tr>
                                            <td>{{ $r['name'] }}</td>
                                            <td>{{ $r['section'] ?: '—' }}</td>
                                            <td>{{ $r['score'] }}%</td>
                                            <td>{{ count($r['weak_areas']) ? implode(', ', array_slice($r['weak_areas'], 0, 2)) : '—' }}</td>
                                            <td>
                                                @if($r['days_idle'] === null) No activity
                                                @elseif($r['days_idle'] <= 0) Active today
                                                @elseif($r['days_idle'] === 1) Active yesterday
                                                @else {{ $r['days_idle'] }}d inactive @endif
                                            </td>
                                            <td>
                                                <span class="status status-{{ $r['status'] === 'high' ? 'red' : ($r['status'] === 'watch' ? 'amber' : 'green') }}">{{ $r['status_label'] }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6"><p class="empty-note">No {{ $f['report'] === 'at_risk' ? '' : 'at-risk ' }}students in the selected scope.</p></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </section>
                    @endif

                    @if($inc('weak') || $inc('recommendations'))
                        <section class="paper-section two-col">
                            @if($inc('weak'))
                                <div>
                                    <div class="section-head"><h3>Top Weak Topics</h3></div>
                                    <table class="report-table">
                                        <thead><tr><th>Topic</th><th>Students</th><th>Accuracy</th></tr></thead>
                                        <tbody>
                                            @forelse($weakTopics->take(6) as $t)
                                                <tr><td>{{ $t->topic }} <span style="color:#9ca3af;">({{ $t->subject_code }})</span></td><td>{{ $t->students }}</td><td>{{ $t->accuracy }}%</td></tr>
                                            @empty
                                                <tr><td colspan="3"><p class="empty-note">No weak topics detected.</p></td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                            @if($inc('recommendations'))
                                <div>
                                    <div class="section-head"><h3>Recommended Actions</h3></div>
                                    <div class="paper-card">
                                        <ul class="recommend-list">
                                            @foreach($recommendations as $rec)
                                                <li>{{ $rec }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </section>
                    @endif

                {{-- ══ SUBJECT MASTERY ══ --}}
                @elseif($f['report'] === 'subject_mastery')
                    @forelse($mastery as $subject)
                        <section class="paper-section">
                            <div class="section-head">
                                <h3>{{ $subject['code'] }} — {{ $subject['name'] }}</h3>
                                <span>Overall accuracy {{ $subject['accuracy'] }}%</span>
                            </div>
                            <div class="bar-row" style="grid-template-columns:1fr 40px;">
                                <div class="bar-track"><div class="bar-fill" style="width:{{ $subject['accuracy'] }}%;background:{{ $subject['color'] }};"></div></div>
                                <div class="bar-val">{{ $subject['accuracy'] }}%</div>
                            </div>
                            <table class="report-table" style="margin-top:10px;">
                                <thead><tr><th>Topic</th><th>Students</th><th>Attempts</th><th>Accuracy</th><th>Mastery</th></tr></thead>
                                <tbody>
                                    @foreach($subject['topics'] as $t)
                                        <tr>
                                            <td>{{ $t['topic'] }}</td>
                                            <td>{{ $t['students'] }}</td>
                                            <td>{{ $t['attempts'] }}</td>
                                            <td>{{ $t['accuracy'] }}%</td>
                                            <td>
                                                <span class="status status-{{ $t['accuracy'] >= 85 ? 'green' : ($t['accuracy'] >= 70 ? 'gray' : ($t['accuracy'] >= 60 ? 'amber' : 'red')) }}">{{ $t['level'] }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </section>
                    @empty
                        <div class="paper-card"><p class="empty-note">No topic performance recorded for the selected subjects yet.</p></div>
                    @endforelse

                {{-- ══ QUESTION QUALITY ══ --}}
                @elseif($f['report'] === 'question_quality')
                    <section class="paper-section">
                        <div class="section-head">
                            <h3>Question Bank Quality</h3>
                            <span>{{ $questions->count() }} active questions · {{ $scopeLabel }}</span>
                        </div>
                        <table class="report-table">
                            <thead>
                                <tr><th>#</th><th>Question</th><th>Topic</th><th>Diff.</th><th>Answered</th><th>Correct</th><th>Flag</th></tr>
                            </thead>
                            <tbody>
                                @forelse($questions->take(40) as $i => $q)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($q['text'], 90) }}</td>
                                        <td>{{ $q['topic'] }} <span style="color:#9ca3af;">({{ $q['subject'] }})</span></td>
                                        <td>{{ ucfirst($q['difficulty']) }}</td>
                                        <td>{{ $q['answered'] }}</td>
                                        <td>{{ $q['answered'] ? $q['accuracy'].'%' : '—' }}</td>
                                        <td>
                                            <span class="status status-{{ $q['flag'] === 'Healthy' ? 'green' : ($q['flag'] === 'Unused' ? 'gray' : ($q['flag'] === 'Too Easy' ? 'amber' : 'red')) }}">{{ $q['flag'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7"><p class="empty-note">No active questions in the selected subject scope.</p></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        @if($questions->count() > 40)
                            <p class="chart-caption">Showing the 40 questions most in need of review. Download the CSV for the full list.</p>
                        @endif
                    </section>
                @endif

                <section class="signature-row">
                    <div class="sig-line">Faculty Signature</div>
                    <div class="sig-line">Program Chair / Reviewer</div>
                </section>

                <footer class="paper-foot">
                    <span>CPACE CPA Reviewer · {{ $reportLabel }}</span>
                    <span>Generated {{ $generatedAt->format('M d, Y') }}</span>
                </footer>
            </article>
        </section>

        <aside class="export-panel">
            <div class="panel-title"><i class="fas fa-file-export"></i> Export Package</div>
            <div class="export-actions">
                <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-file-pdf"></i> Export PDF</button>
                <button type="button" class="btn btn-ghost" onclick="window.print()"><i class="fas fa-print"></i> Print Preview</button>
                <a class="btn btn-ghost" href="{{ route('faculty.reports.export', $activeQuery) }}"><i class="fas fa-file-csv"></i> Download CSV</a>
            </div>
            <div class="mini-stat"><span>Report Type</span><strong>{{ $reportLabel }}</strong></div>
            <div class="mini-stat"><span>Paper Size</span><strong>A4 Portrait</strong></div>
            <div class="mini-stat"><span>Coverage</span><strong>{{ $rangeLabel }}</strong></div>
            <div class="mini-stat"><span>Active Students</span><strong>{{ $stats['students'] }}</strong></div>
            @if($f['report'] === 'question_quality')
                <div class="mini-stat"><span>Questions</span><strong>{{ $questions->count() }}</strong></div>
            @else
                <div class="mini-stat"><span>At Risk</span><strong>{{ $stats['at_risk'] }}</strong></div>
            @endif

            <div class="panel-title" style="margin-top:18px;"><i class="fas fa-list-check"></i> Report Outline</div>
            <div class="outline-list">
                @if($f['report'] === 'subject_mastery')
                    <div class="outline-item"><i class="fas fa-check-circle"></i><span>Executive summary with key totals</span></div>
                    <div class="outline-item"><i class="fas fa-check-circle"></i><span>Per-subject topic mastery tables</span></div>
                    <div class="outline-item"><i class="fas fa-check-circle"></i><span>Mastery level per topic</span></div>
                @elseif($f['report'] === 'question_quality')
                    <div class="outline-item"><i class="fas fa-check-circle"></i><span>Question bank usage & difficulty</span></div>
                    <div class="outline-item"><i class="fas fa-check-circle"></i><span>Per-item correct rate</span></div>
                    <div class="outline-item"><i class="fas fa-check-circle"></i><span>Quality flags (unused / too hard / too easy)</span></div>
                @else
                    @if($inc('summary'))<div class="outline-item"><i class="fas fa-check-circle"></i><span>Executive summary with key totals</span></div>@endif
                    @if($inc('charts') && $f['report'] === 'class_summary')<div class="outline-item"><i class="fas fa-check-circle"></i><span>Subject accuracy & score distribution</span></div>@endif
                    @if($inc('atrisk') || $f['report'] === 'at_risk')<div class="outline-item"><i class="fas fa-check-circle"></i><span>At-risk student intervention table</span></div>@endif
                    @if($inc('weak'))<div class="outline-item"><i class="fas fa-check-circle"></i><span>Top weak topics</span></div>@endif
                    @if($inc('recommendations'))<div class="outline-item"><i class="fas fa-check-circle"></i><span>Recommended actions</span></div>@endif
                @endif
            </div>

            <div class="panel-title" style="margin-top:18px;"><i class="fas fa-table"></i> CSV Preview</div>
            <div class="csv-preview">
                @if($f['report'] === 'question_quality')
                    <div class="csv-row head"><span>Topic</span><span>Answered</span><span>Correct</span><span>Flag</span></div>
                    @forelse($questions->take(3) as $q)
                        <div class="csv-row"><span>{{ $q['topic'] }}</span><span>{{ $q['answered'] }}</span><span>{{ $q['answered'] ? $q['accuracy'].'%' : '—' }}</span><span>{{ $q['flag'] }}</span></div>
                    @empty
                        <div class="csv-row"><span colspan="4" style="grid-column:1/-1;">No rows</span></div>
                    @endforelse
                @elseif($f['report'] === 'subject_mastery')
                    <div class="csv-row head"><span>Subject</span><span>Topics</span><span>Acc.</span><span>—</span></div>
                    @forelse(collect($mastery)->take(3) as $s)
                        <div class="csv-row"><span>{{ $s['code'] }}</span><span>{{ count($s['topics']) }}</span><span>{{ $s['accuracy'] }}%</span><span>—</span></div>
                    @empty
                        <div class="csv-row"><span style="grid-column:1/-1;">No rows</span></div>
                    @endforelse
                @else
                    <div class="csv-row head"><span>Student</span><span>Section</span><span>Accuracy</span><span>Status</span></div>
                    @php $csvRows = $f['report'] === 'at_risk' ? $atRisk : $students; @endphp
                    @forelse($csvRows->take(3) as $r)
                        <div class="csv-row"><span>{{ $r['name'] }}</span><span>{{ $r['section'] ?: '—' }}</span><span>{{ $r['score'] }}%</span><span>{{ $r['status_label'] }}</span></div>
                    @empty
                        <div class="csv-row"><span style="grid-column:1/-1;">No rows</span></div>
                    @endforelse
                @endif
            </div>
        </aside>
    </div>
</main>

<script>
    let zoom = 100;
    function zoomPaper(delta) {
        zoom = Math.min(150, Math.max(50, zoom + delta));
        document.getElementById('zoomVal').textContent = zoom + '%';
        document.getElementById('reportPaper').style.transform = 'scale(' + (zoom / 100) + ')';
    }
</script>

</body>
</html>
