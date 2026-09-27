<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor {{ $quiz->title }} - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.mock-exam-styles')
    <style>
        .kpis { display:grid; grid-template-columns:repeat(4, 1fr); gap:16px; margin-bottom:18px; }
        .stat-card { background:#fff; border-radius:14px; padding:18px 20px;
                     box-shadow:0 2px 6px rgba(15,10,10,.08), 0 10px 22px -10px rgba(15,10,10,.22); }
        .stat-top { display:flex; justify-content:space-between; align-items:flex-start; }
        .stat-icon { width:40px; height:40px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:17px; flex-shrink:0; }
        .si-blue   { background:#dbeafe; color:var(--blue); }
        .si-orange { background:#fef3c7; color:var(--amber); }
        .si-green  { background:#d1fae5; color:var(--green); }
        .si-red    { background:#fde8e8; color:var(--red); }
        .stat-lbl { font-size:11.5px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.4px; }
        .stat-num { font-size:28px; font-weight:700; color:var(--ink); font-family:'Montserrat',sans-serif; line-height:1; margin-top:6px; }
        .stat-sub { font-size:11px; color:var(--muted); margin-top:6px; }

        .grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(190px, 1fr)); gap:14px; }
        .tile { background:#fff; border:1px solid var(--line); border-radius:13px; overflow:hidden;
                text-decoration:none; color:inherit; transition:box-shadow .2s, transform .2s;
                box-shadow:0 8px 20px -12px rgba(15,20,35,.28); }
        .tile:hover { box-shadow:0 14px 30px -12px rgba(15,20,35,.4); transform:translateY(-3px); }
        .tile.flagged { border-color:var(--red); box-shadow:0 0 0 2px rgba(192,57,43,.15), 0 8px 20px -12px rgba(192,57,43,.35); }
        .tile.tile-high { border-color:var(--red); box-shadow:0 0 0 3px rgba(192,57,43,.28), 0 8px 20px -12px rgba(192,57,43,.5); }
        .tile-cam { width:100%; aspect-ratio:4/3; background:#101828; object-fit:cover; display:block; }
        .tile-none { width:100%; aspect-ratio:4/3; background:#1b2333; color:#5b6377; display:flex; align-items:center; justify-content:center; font-size:26px; }
        .tile-body { padding:11px 13px; }
        .tile-name { font-size:12.5px; font-weight:600; color:var(--ink); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .tile-meta { font-size:11px; color:var(--muted); margin-top:4px; display:flex; justify-content:space-between; align-items:center; gap:6px; }
        .risk { display:inline-block; font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; white-space:nowrap; }
        .risk-low    { background:#e8f1fb; color:#2a5da8; }
        .risk-medium { background:#fdf0d8; color:#9a6200; }
        .risk-high   { background:#fdeceb; color:#a32318; }
        .live-dot { width:8px; height:8px; border-radius:50%; background:var(--green); display:inline-block; animation:live-pulse 2s infinite; }
        @keyframes live-pulse {
            0%   { box-shadow:0 0 0 0 rgba(30,158,99,.45); }
            70%  { box-shadow:0 0 0 6px rgba(30,158,99,0); }
            100% { box-shadow:0 0 0 0 rgba(30,158,99,0); }
        }
        @media (max-width: 900px) { .kpis { grid-template-columns:repeat(2, 1fr); } }
    </style>
</head>
<body>
@include('partials.faculty-sidebar', ['active' => 'quizzes'])

<main class="main">
    <div class="topbar">
        <div>
            <a href="{{ $quiz->subject_id ? route('faculty.quizzes.subject', $quiz->subject_id) : route('faculty.quizzes') }}"
               style="font-size:12px;color:var(--muted);text-decoration:none;">
                <i class="fas fa-arrow-left"></i> {{ $quiz->subject?->code ?? 'Class Quizzes' }}
            </a>
            <div class="page-title" style="margin-top:4px;">{{ $quiz->title }}</div>
            <div class="page-sub"><span class="live-dot"></span> Live monitor · {{ $quiz->items()->count() }} questions
                @if($quiz->due_at) · due {{ $quiz->due_at->format('M j, g:i A') }} @endif
            </div>
        </div>
        <div class="topbar-right">
            @include('partials.topbar-actions')
            <a href="{{ route('faculty.quizzes.results', $quiz->id) }}" class="btn btn-ghost"><i class="fas fa-chart-column"></i> Results</a>
        </div>
    </div>

    <div class="banner banner-info">
        <i class="fas fa-camera"></i>
        <div>
            Snapshots refresh about every 15 seconds. This is a periodic capture, not a live video stream.
            A student who stops sharing their camera or screen is flagged immediately and locked out until they share again.
        </div>
    </div>

    <div class="banner banner-info">
        <i class="fas fa-hard-drive"></i>
        <div>
            <strong>Recordings stored for this quiz: {{ $storage['count'] }} ({{ \App\Support\ProctorCaptureRetention::humanSize($storage['bytes']) }}).</strong>
            Students with no flags keep only their opening camera photo; everything else is deleted when they submit.
            Flagged students keep only the frames behind a flag. What is kept is removed automatically
            {{ \App\Models\QuizProctorCapture::RETENTION_DAYS }} days later, or sooner from a student's page. The flag timeline is always kept.
        </div>
    </div>

    <div class="kpis">
        <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">Started</div><div class="stat-num" id="kpiStarted">0</div></div><div class="stat-icon si-orange"><i class="fas fa-person-running"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">Submitted</div><div class="stat-num" id="kpiSubmitted">0</div></div><div class="stat-icon si-green"><i class="fas fa-circle-check"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">Flagged</div><div class="stat-num" id="kpiFlagged" style="color:var(--red);">0</div></div><div class="stat-icon si-red"><i class="fas fa-triangle-exclamation"></i></div></div><div class="stat-sub"><span id="kpiHigh">0</span> high risk</div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-lbl">In progress</div><div class="stat-num" id="kpiLive">0</div></div><div class="stat-icon si-blue"><i class="fas fa-hourglass-half"></i></div></div></div>
    </div>

    <div class="card">
        <div class="card-title"><i class="fas fa-users-viewfinder"></i> Students</div>
        <div class="card-sub">
            Highest risk first. The badge weighs each flag by how serious it is (losing the camera or screen counts more than
            a glance away). It is a signal for you to review, not a verdict. Click a tile for the answers, flag timeline and captures.
        </div>
        <div class="grid" id="grid" style="margin-top:16px;">
            <div class="empty" style="grid-column:1/-1;"><i class="fas fa-hourglass-half"></i><h3>Nobody has started yet</h3><p>Tiles appear as students open the quiz.</p></div>
        </div>
    </div>
</main>

@include('partials.alerts')
<script>
(function () {
    const feed = @json(route('faculty.quizzes.monitor.feed', $quiz->id));
    const grid = document.getElementById('grid');

    function esc(s) {
        return String(s ?? '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
    }

    async function poll() {
        try {
            const res = await fetch(feed, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();

            document.getElementById('kpiStarted').textContent = data.kpis.started;
            document.getElementById('kpiSubmitted').textContent = data.kpis.submitted;
            document.getElementById('kpiFlagged').textContent = data.kpis.flagged;
            document.getElementById('kpiHigh').textContent = data.kpis.high_risk;
            document.getElementById('kpiLive').textContent = data.kpis.started - data.kpis.submitted;

            if (!data.students.length) return;

            grid.innerHTML = data.students.map(s => `
                <a class="tile ${s.flags > 0 ? 'flagged' : ''} ${s.risk_level === 'high' ? 'tile-high' : ''}" href="${s.detail}">
                    ${s.camera
                        ? `<img class="tile-cam" src="${s.camera}" alt="">`
                        : `<div class="tile-none"><i class="fas fa-video-slash"></i></div>`}
                    <div class="tile-body">
                        <div class="tile-name">${esc(s.student)}</div>
                        <div class="tile-meta">
                            <span>${s.submitted ? (s.percent !== null ? s.percent + '%' : 'submitted') : s.answered + ' answered'}</span>
                            ${s.risk_level !== 'none' ? `<span class="risk risk-${s.risk_level}" title="${s.flags} flag(s), weighted score ${s.risk_score}">${esc(s.risk_label)}</span>` : ''}
                        </div>
                    </div>
                </a>`).join('');
        } catch (e) { /* the next tick will pick it up */ }
    }

    poll();
    setInterval(poll, 15000);
})();
</script>
</body>
</html>
