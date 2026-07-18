<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class-Level Performance - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .filter-card { padding:13px 16px; margin-bottom:18px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
        .filter-card label { font-size:10.5px; font-weight:600; color:#777; }
        .filter-card select { min-width:230px; padding:8px 11px; border:1px solid #ddd; border-radius:8px; font:11px Poppins,sans-serif; color:#444; background:#fff; }
        .analytics-layout { display:grid; grid-template-columns:minmax(0,1.3fr) minmax(310px,.7fr); gap:18px; }
        .metric-row { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin-bottom:18px; }
        .metric { background:#fff; border:1px solid #eee; border-radius:12px; padding:15px; }
        .metric-label { font-size:10px; color:#888; font-weight:600; }
        .metric-value { font-size:24px; font-weight:700; color:#222; margin-top:7px; }
        .metric-note { font-size:9.5px; color:#aaa; margin-top:4px; }
        .subject-row { display:grid; grid-template-columns:minmax(185px,1fr) minmax(160px,1.3fr) 72px 90px; gap:13px; align-items:center; padding:13px 0; border-top:1px solid #f3f3f3; }
        .subject-row.head { border:0; padding-top:0; font-size:9.5px; color:#aaa; text-transform:uppercase; letter-spacing:.3px; }
        .subject-name { font-size:11px; color:#555; margin-left:7px; }
        .bar { height:7px; border-radius:5px; background:#f0f1f3; overflow:hidden; }
        .bar > span { display:block; height:100%; border-radius:5px; background:var(--primary); }
        .accuracy { font-size:13px; font-weight:700; color:#222; }
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
        @media(max-width:1050px) { .analytics-layout{grid-template-columns:1fr}.metric-row{grid-template-columns:repeat(2,1fr)} }
        @media(max-width:640px) { .metric-row{grid-template-columns:1fr}.subject-row{grid-template-columns:1fr 65px}.subject-row .bar,.subject-row .small-meta,.subject-row.head{display:none}.filter-card select{min-width:0;width:100%} }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'analytics-performance'])
<main class="main">
    <div class="topbar">
        <div class="topbar-left"><div><div class="page-title">Class-Level Performance</div><div class="page-sub">Aggregated accuracy and board readiness for all active students.</div></div></div>
        <div class="topbar-right">@include('partials.topbar-actions')</div>
    </div>

    <form class="card filter-card" method="GET">
        <label for="subject">Subject</label>
        <select name="subject" id="subject">
            <option value="">All CPALE subjects</option>
            @foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected($selectedSubject === $subject->id)>{{ $subject->code }} — {{ $subject->name }}</option>@endforeach
        </select>
        <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-filter"></i> Apply</button>
        @if($selectedSubject)<a class="btn btn-ghost btn-sm" href="{{ route('chair.analytics.performance') }}">Clear</a>@endif
    </form>

    <div class="metric-row">
        <div class="metric"><div class="metric-label">Class-Level Accuracy</div><div class="metric-value">{{ $report['overall_accuracy'] === null ? '—' : $report['overall_accuracy'].'%' }}</div><div class="metric-note">Weighted across all student attempts</div></div>
        <div class="metric"><div class="metric-label">Participating Students</div><div class="metric-value">{{ $report['participating_students'] }}</div><div class="metric-note">With at least one recorded attempt</div></div>
        <div class="metric"><div class="metric-label">Total Attempts</div><div class="metric-value">{{ number_format($report['total_attempts']) }}</div><div class="metric-note">Across the selected scope</div></div>
        <div class="metric"><div class="metric-label">Board Ready</div><div class="metric-value">{{ $report['readiness']['readiness_rate'] === null ? '—' : $report['readiness']['readiness_rate'].'%' }}</div><div class="metric-note">{{ $report['readiness']['ready'] }} of {{ $report['readiness']['eligible'] }} eligible</div></div>
    </div>

    <div class="analytics-layout">
        <div>
            <section class="card">
                <div class="card-head"><span class="card-title">Subject-by-Subject Accuracy</span><span class="small-meta">Weighted class-level results</span></div>
                <div class="subject-row head"><span>Subject</span><span>Accuracy</span><span>Score</span><span>Participation</span></div>
                @forelse($report['subjects'] as $subject)
                    <div class="subject-row">
                        <div><span class="subj-badge b-{{ strtolower($subject['code']) }}">{{ $subject['code'] }}</span><span class="subject-name">{{ $subject['name'] }}</span></div>
                        <div class="bar"><span style="width:{{ $subject['accuracy'] ?? 0 }}%"></span></div>
                        <div class="accuracy">{{ $subject['accuracy'] === null ? '—' : $subject['accuracy'].'%' }}</div>
                        <div><div class="small-meta">{{ $subject['students'] }} students</div><div class="small-meta">{{ number_format($subject['attempts']) }} attempts</div></div>
                    </div>
                @empty<div class="empty">No subjects are available.</div>@endforelse
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
        </div>

        <div>
            <section class="card">
                <div class="card-head"><span class="card-title">Readiness Bands</span></div>
                <div class="bands">
                    <div class="band ready"><strong>{{ $report['readiness']['ready'] }}</strong><span>Ready</span></div>
                    <div class="band"><strong>{{ $report['readiness']['developing'] }}</strong><span>Developing</span></div>
                    <div class="band risk"><strong>{{ $report['readiness']['at_risk'] }}</strong><span>At risk</span></div>
                </div>
                <div class="method-note">Ready requires at least 50 completed items, 60% accuracy, and—when viewing all subjects—activity in at least three subjects. Students need 20 items to be included in the measured class.</div>
            </section>

            <section class="card" id="pass-projection" style="margin-top:18px;">
                <div class="card-head"><span class="card-title">Predicted Pass Rate</span><i class="fas fa-graduation-cap" style="color:var(--accent)"></i></div>
                <div style="text-align:center;padding:12px 0 6px;"><div style="font-size:42px;font-weight:700;color:#222;">{{ $report['readiness']['pass_projection'] === null ? '—' : $report['readiness']['pass_projection'].'%' }}</div><div class="small-meta">Readiness-based class-level projection</div></div>
                <div class="method-note"><strong>Planning estimate only.</strong> The projection counts ready students fully and developing students at 50%. It is not an official board-exam prediction and becomes more useful as students complete more practice.</div>
                @if($report['readiness']['insufficient'] > 0)<div class="method-note"><i class="fas fa-circle-info"></i> {{ $report['readiness']['insufficient'] }} active student{{ $report['readiness']['insufficient'] === 1 ? '' : 's' }} currently have insufficient activity for the projection.</div>@endif
            </section>
        </div>
    </div>
</main>
</body>
</html>
