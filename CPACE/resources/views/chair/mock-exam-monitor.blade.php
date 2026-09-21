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
        .card { box-shadow:0 2px 6px rgba(15,10,10,.08), 0 10px 22px -10px rgba(15,10,10,.22); }

        /* KPI strip — matches the .stat-card tiles on the faculty/chair dashboards
           (icon badge top-right, big number, uppercase label, dark drop shadow). */
        .kpis { display:grid; grid-template-columns:repeat(4, 1fr); gap:16px; margin-bottom:18px; }
        .stat-card { background:#fff; border-radius:14px; padding:18px 20px;
                     box-shadow:0 2px 6px rgba(15,10,10,.08), 0 10px 22px -10px rgba(15,10,10,.22);
                     transition:transform .18s ease, box-shadow .18s ease; }
        .stat-card:hover { transform:translateY(-3px); box-shadow:0 4px 10px rgba(15,10,10,.1), 0 16px 30px -10px rgba(15,10,10,.3); }
        .stat-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; }
        .stat-icon { width:40px; height:40px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:17px; flex-shrink:0; }
        .si-blue   { background:#dbeafe; color:var(--blue); }
        .si-orange { background:#fef3c7; color:var(--amber); }
        .si-green  { background:#d1fae5; color:var(--green); }
        .si-red    { background:#fde8e8; color:var(--red); }
        .stat-lbl { font-size:11.5px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.4px; }
        .stat-num { font-size:28px; font-weight:700; color:var(--ink); font-family:'Montserrat',sans-serif; line-height:1; margin-top:6px; }
        .stat-card.alert .stat-num { color:var(--red); }

        .grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(190px, 1fr)); gap:14px; }
        .tile { background:#fff; border:1px solid var(--line); border-radius:13px; overflow:hidden;
                text-decoration:none; color:inherit; transition:box-shadow .2s, transform .2s;
                box-shadow:0 8px 20px -12px rgba(15,20,35,.28); }
        .tile:hover { box-shadow:0 14px 30px -12px rgba(15,20,35,.4); transform:translateY(-3px); }
        .tile.flagged { border-color:var(--red); box-shadow:0 0 0 2px rgba(192,57,43,.15), 0 8px 20px -12px rgba(192,57,43,.35); }
        .tile-cam { width:100%; aspect-ratio:4/3; background:#101828; object-fit:cover; display:block; }
        .tile-none { width:100%; aspect-ratio:4/3; background:#1b2333; color:#5b6377; display:flex;
                     align-items:center; justify-content:center; font-size:26px; }
        .tile-body { padding:11px 13px; }
        .tile-name { font-size:12.5px; font-weight:600; color:var(--ink); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .tile-meta { font-size:11px; color:var(--muted); margin-top:4px; display:flex; justify-content:space-between; align-items:center; gap:6px; }
        .live-dot { width:8px; height:8px; border-radius:50%; background:var(--green); display:inline-block; box-shadow:0 0 0 rgba(30,158,99,.5); animation:live-pulse 2s infinite; }
        @keyframes live-pulse {
            0%   { box-shadow:0 0 0 0 rgba(30,158,99,.45); }
            70%  { box-shadow:0 0 0 6px rgba(30,158,99,0); }
            100% { box-shadow:0 0 0 0 rgba(30,158,99,0); }
        }
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
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Redeemed code</div>
                    <div class="stat-num" id="kpiRegistered">{{ $registered }}</div>
                </div>
                <div class="stat-icon si-blue"><i class="fas fa-ticket"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Started</div>
                    <div class="stat-num" id="kpiStarted">0</div>
                </div>
                <div class="stat-icon si-orange"><i class="fas fa-person-running"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Submitted</div>
                    <div class="stat-num" id="kpiSubmitted">0</div>
                </div>
                <div class="stat-icon si-green"><i class="fas fa-circle-check"></i></div>
            </div>
        </div>
        <div class="stat-card alert">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Flagged</div>
                    <div class="stat-num" id="kpiFlagged">0</div>
                </div>
                <div class="stat-icon si-red"><i class="fas fa-triangle-exclamation"></i></div>
            </div>
        </div>
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
