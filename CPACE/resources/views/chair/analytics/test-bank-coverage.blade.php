<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Bank Coverage - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .filter-card { padding:13px 16px; margin-bottom:18px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
        .filter-card label { font-size:10.5px; font-weight:600; color:#777; }
        .filter-card select { min-width:230px; padding:8px 11px; border:1px solid #ddd; border-radius:8px; font:11px Poppins,sans-serif; color:#444; background:#fff; }
        .coverage-wrap { overflow-x:auto; }
        .coverage-wrap table { min-width:900px; }
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
        @media(max-width:640px){.filter-card select{min-width:0;width:100%}}
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'analytics-coverage'])
<main class="main">
    <div class="topbar">
        <div class="topbar-left"><div><div class="page-title">Test Bank Coverage</div><div class="page-sub">Find topics that need more active questions and a better difficulty mix.</div></div></div>
        <div class="topbar-right">@include('partials.topbar-actions')</div>
    </div>

    <form class="card filter-card" method="GET">
        <label for="subject">Subject</label>
        <select name="subject" id="subject"><option value="">All CPALE subjects</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected($selectedSubject === $subject->id)>{{ $subject->code }} — {{ $subject->name }}</option>@endforeach</select>
        <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-filter"></i> Apply</button>
        @if($selectedSubject)<a class="btn btn-ghost btn-sm" href="{{ route('chair.analytics.test-bank-coverage') }}">Clear</a>@endif
    </form>

    <div class="stats-row">
        <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">Topics Monitored</div><div class="stat-num">{{ $stats['topics'] }}</div></div><div class="stat-icon si-blue"><i class="fas fa-list"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">Active Questions</div><div class="stat-num">{{ number_format($stats['active']) }}</div></div><div class="stat-icon si-green"><i class="fas fa-circle-question"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">Thin Topics</div><div class="stat-num">{{ $stats['thin'] }}</div></div><div class="stat-icon si-orange"><i class="fas fa-battery-quarter"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">Critical Topics</div><div class="stat-num">{{ $stats['critical'] }}</div></div><div class="stat-icon si-red"><i class="fas fa-triangle-exclamation"></i></div></div></div>
    </div>

    <section class="card">
        <div class="card-head"><span class="card-title">Coverage by Topic</span><span style="font-size:10px;color:#aaa;">Target: {{ $target }} active questions per topic</span></div>
        <div class="coverage-wrap">
            <table>
                <thead><tr><th>Topic</th><th>Active Coverage</th><th>Total</th><th>Easy</th><th>Moderate</th><th>Difficult</th><th>Last Added</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($coverage as $topic)
                    <tr>
                        <td><div class="topic-name">{{ $topic['name'] }}</div><div class="topic-subject">{{ $topic['subject_code'] }} · {{ $topic['subject_name'] }}</div></td>
                        <td><div class="count-main">{{ $topic['active'] }} / {{ $target }}</div><div class="coverage-bar"><span style="width:{{ min(100, round($topic['active'] / $target * 100)) }}%"></span></div>@if($topic['gap'])<div class="count-sub">Needs {{ $topic['gap'] }} more</div>@endif</td>
                        <td><span class="count-main">{{ $topic['total'] }}</span></td>
                        <td>{{ $topic['easy'] }}</td><td>{{ $topic['moderate'] }}</td><td>{{ $topic['difficult'] }}</td>
                        <td><span style="font-size:10.5px;color:#666;">{{ $topic['last_added'] ? $topic['last_added']->diffForHumans() : 'Never' }}</span></td>
                        <td><span class="status {{ $topic['status'] }}"><i class="fas {{ $topic['status'] === 'adequate' ? 'fa-check' : 'fa-triangle-exclamation' }}"></i>{{ ucfirst($topic['status']) }}</span></td>
                    </tr>
                @empty<tr><td colspan="8"><div class="empty">No active topics are available.</div></td></tr>@endforelse
                </tbody>
            </table>
        </div>
        <div class="legend"><strong>Coverage rule:</strong> zero active questions is critical, 1–{{ $target - 1 }} is thin, and {{ $target }} or more is adequate. Difficulty totals include both common naming styles (moderate/medium and difficult/hard).</div>
    </section>
</main>
</body>
</html>
