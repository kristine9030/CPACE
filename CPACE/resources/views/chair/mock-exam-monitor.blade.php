<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor {{ $exam->title }} - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.mock-exam-styles')
    <style>
        .kpis { display:grid; grid-template-columns:repeat(4, 1fr); gap:16px; margin-bottom:18px; }
        .kpi { background:#fff; border:1px solid var(--line); border-radius:13px; padding:16px 18px; }
        .kpi-n { font-size:27px; font-weight:700; color:var(--ink); font-family:'Montserrat',sans-serif; }
        .kpi-l { font-size:11.5px; color:var(--muted); text-transform:uppercase; letter-spacing:.4px; margin-top:2px; }
        .kpi.alert .kpi-n { color:var(--red); }

        .grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(190px, 1fr)); gap:14px; }
        .tile { background:#fff; border:1px solid var(--line); border-radius:12px; overflow:hidden;
                text-decoration:none; color:inherit; transition:box-shadow .2s, transform .2s; }
        .tile:hover { box-shadow:0 10px 22px -10px rgba(0,0,0,.25); transform:translateY(-2px); }
        .tile.flagged { border-color:var(--red); box-shadow:0 0 0 2px rgba(192,57,43,.12); }
        .tile-cam { width:100%; aspect-ratio:4/3; background:#101828; object-fit:cover; display:block; }
        .tile-none { width:100%; aspect-ratio:4/3; background:#1b2333; color:#5b6377; display:flex;
                     align-items:center; justify-content:center; font-size:26px; }
        .tile-body { padding:10px 12px; }
        .tile-name { font-size:12.5px; font-weight:600; color:var(--ink); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .tile-meta { font-size:11px; color:var(--muted); margin-top:3px; display:flex; justify-content:space-between; gap:6px; }
        .live-dot { width:8px; height:8px; border-radius:50%; background:var(--green); display:inline-block; }
        @media (max-width: 900px) { .kpis { grid-template-columns:repeat(2, 1fr); } }
    </style>
</head>
<body>
@if($isChair)
    @include('partials.chair-sidebar', ['active' => 'mock-exams'])
@else
    @include('partials.faculty-sidebar', ['active' => 'mock-exams'])
@endif

<main class="main">
    <div class="topbar">
        <div>
            <a href="{{ $isChair ? route('chair.mock-exams.subject', $exam->subject_id) : route('faculty.mock-exams.subject', $exam->subject_id) }}"
               style="font-size:12px;color:var(--muted);text-decoration:none;">
                <i class="fas fa-arrow-left"></i> {{ $subject->code }}
            </a>
            <div class="page-title" style="margin-top:4px;">{{ $exam->title }}</div>
            <div class="page-sub">
                <span class="live-dot"></span> Live monitor ·
                {{ $exam->scheduled_at?->format('M j, Y · g:i A') }} ·
                {{ $exam->duration_minutes }} min ·
                <span id="windowState">{{ $exam->window() }}</span>
            </div>
        </div>
        <div class="topbar-right">@include('partials.topbar-actions')</div>
    </div>

    <div class="banner banner-info">
        <i class="fas fa-camera"></i>
        <div>
            Snapshots refresh about every 15 seconds — this is a periodic capture, not a live video stream.
            A student who revokes camera or screen sharing mid-exam is flagged immediately.
        </div>
    </div>

    <div class="kpis">
        <div class="kpi"><div class="kpi-n" id="kpiRegistered">{{ $registered }}</div><div class="kpi-l">Redeemed code</div></div>
        <div class="kpi"><div class="kpi-n" id="kpiStarted">0</div><div class="kpi-l">Started</div></div>
        <div class="kpi"><div class="kpi-n" id="kpiSubmitted">0</div><div class="kpi-l">Submitted</div></div>
        <div class="kpi alert"><div class="kpi-n" id="kpiFlagged">0</div><div class="kpi-l">Flagged</div></div>
    </div>

    <div class="card">
        <div class="card-title"><i class="fas fa-users-viewfinder"></i> Students</div>
        <div class="card-sub">Most-flagged first. Click a tile for the full timeline and captures.</div>
        <div class="grid" id="grid" style="margin-top:16px;">
            <div class="empty" style="grid-column:1/-1;"><i class="fas fa-hourglass-half"></i><h3>Nobody has started yet</h3><p>Tiles appear as students enter the exam.</p></div>
        </div>
    </div>
</main>

@include('partials.alerts')
<script>
(function () {
    const feed = @json($isChair ? route('chair.mock-exams.monitor.feed', $exam) : route('faculty.mock-exams.monitor.feed', $exam));
    const grid = document.getElementById('grid');

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
    }

    async function poll() {
        try {
            const res = await fetch(feed, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();

            document.getElementById('kpiRegistered').textContent = data.kpis.registered;
            document.getElementById('kpiStarted').textContent = data.kpis.started;
            document.getElementById('kpiSubmitted').textContent = data.kpis.submitted;
            document.getElementById('kpiFlagged').textContent = data.kpis.flagged;
            document.getElementById('windowState').textContent = data.exam.window;

            if (!data.students.length) return;

            grid.innerHTML = data.students.map(s => `
                <a class="tile ${s.flags > 0 ? 'flagged' : ''}" href="${s.detail}">
                    ${s.camera
                        ? `<img class="tile-cam" src="${s.camera}" alt="">`
                        : `<div class="tile-none"><i class="fas fa-video-slash"></i></div>`}
                    <div class="tile-body">
                        <div class="tile-name">${escapeHtml(s.student)}</div>
                        <div class="tile-meta">
                            <span>${s.status === 'submitted' ? (s.percent !== null ? s.percent + '%' : 'submitted') : s.answered + ' answered'}</span>
                            ${s.flags > 0 ? `<span class="chip chip-flag">${s.flags}</span>` : '<span></span>'}
                        </div>
                    </div>
                </a>`).join('');
        } catch (e) {
            // A dropped poll is not worth interrupting the invigilator for;
            // the next tick will pick it up.
        }
    }

    poll();
    setInterval(poll, 15000);
})();
</script>
</body>
</html>
