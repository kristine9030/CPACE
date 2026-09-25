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
        .tile.tile-high { border-color:var(--red); box-shadow:0 0 0 3px rgba(192,57,43,.28), 0 8px 20px -12px rgba(192,57,43,.5); }
        .risk { display:inline-block; font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; letter-spacing:.2px; white-space:nowrap; }
        .risk-low    { background:#e8f1fb; color:#2a5da8; }
        .risk-medium { background:#fdf0d8; color:#9a6200; }
        .risk-high   { background:#fdeceb; color:#a32318; }
        .risk-auto   { background:#eef0f4; color:#556; margin-left:4px; }
        .stat-sub { font-size:11px; color:var(--muted); margin-top:6px; }
        .sim-row { display:flex; align-items:center; gap:14px; padding:11px 0; border-bottom:1px solid #f0f1f4; font-size:12.5px; flex-wrap:wrap; }
        .sim-row:last-child { border-bottom:none; }
        .sim-names { font-weight:600; color:var(--ink); min-width:240px; }
        .sim-names a { color:inherit; text-decoration:none; border-bottom:1px dotted var(--muted); }
        .sim-names a:hover { color:var(--primary); }
        .sim-fact { color:var(--muted); }
        .sim-fact b { color:var(--ink); }
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
            A student who revokes camera or screen sharing mid-exam is flagged immediately and locked out until they share again.
        </div>
    </div>

    <div class="banner banner-info">
        <i class="fas fa-hard-drive"></i>
        <div>
            <strong>Recordings stored for this exam: {{ $storage['count'] }} ({{ \App\Support\ProctorCaptureRetention::humanSize($storage['bytes']) }}).</strong>
            Students with no flags keep only their opening camera photo (to show who sat the exam); everything else is deleted as
            soon as they submit. Flagged students keep only the frames behind a flag. What is kept is removed automatically {{ \App\Models\MockExamProctorCapture::RETENTION_DAYS }} days after the exam,
            or sooner with “Delete recordings” on a student's page. The flag timeline is always kept.
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
            <div class="stat-sub"><span id="kpiHigh">0</span> high risk</div>
        </div>
    </div>

    <div class="card">
        <div class="card-title"><i class="fas fa-users-viewfinder"></i> Students</div>
        <div class="card-sub">
            Highest risk first. The badge weighs each flag by how serious it is (losing the camera or screen counts more than
            a glance away). It is a signal for you to review, not a verdict. Click a tile for the timeline and captures.
        </div>
        <div class="grid" id="grid" style="margin-top:16px;">
            <div class="empty" style="grid-column:1/-1;"><i class="fas fa-hourglass-half"></i><h3>Nobody has started yet</h3><p>Tiles appear as students enter the exam.</p></div>
        </div>
    </div>

    <div class="card">
        <div class="card-title"><i class="fas fa-people-arrows"></i> Answer similarity</div>
        <div class="card-sub">
            Pairs of submitted students who chose the <strong>same wrong answer</strong> on far more questions than chance would
            explain. Matching right answers means nothing; matching wrong ones is unusual. This is a signal, not proof: students
            who studied from the same wrong source will match too.
        </div>
        <div id="simBody" style="margin-top:12px;font-size:12.5px;color:var(--muted);">Checking…</div>
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
            document.getElementById('kpiHigh').textContent = data.kpis.high_risk;
            document.getElementById('windowState').textContent = data.exam.window;

            if (!data.students.length) return;

            grid.innerHTML = data.students.map(s => `
                <a class="tile ${s.flags > 0 ? 'flagged' : ''} ${s.risk_level === 'high' ? 'tile-high' : ''}" href="${s.detail}">
                    ${s.camera
                        ? `<img class="tile-cam" src="${s.camera}" alt="">`
                        : `<div class="tile-none"><i class="fas fa-video-slash"></i></div>`}
                    <div class="tile-body">
                        <div class="tile-name">${escapeHtml(s.student)}</div>
                        <div class="tile-meta">
                            <span>${s.status === 'submitted' ? (s.percent !== null ? s.percent + '%' : 'submitted') : s.answered + ' answered'}</span>
                            <span>
                                ${s.risk_level !== 'none' ? `<span class="risk risk-${s.risk_level}" title="${s.flags} flag(s), weighted score ${s.risk_score}">${escapeHtml(s.risk_label)}</span>` : ''}
                                ${s.auto_closed ? '<span class="risk risk-auto" title="Time ran out and the server graded the last autosave">auto-closed</span>' : ''}
                            </span>
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

    // Answer similarity compares every pair of students, so it runs on load and
    // then once a minute rather than on the 15-second poll.
    const simUrl = @json($isChair ? route('chair.mock-exams.monitor.similarity', $exam) : route('faculty.mock-exams.monitor.similarity', $exam));
    const simBody = document.getElementById('simBody');

    async function loadSimilarity() {
        try {
            const res = await fetch(simUrl, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();

            if (data.submitted < 2) {
                simBody.textContent = 'Needs at least two submitted sittings to compare.';
                return;
            }
            if (!data.pairs.length) {
                simBody.textContent = 'No pair of the ' + data.submitted + ' submitted students stands out. Nothing to review.';
                return;
            }

            simBody.innerHTML = data.pairs.map(p => `
                <div class="sim-row">
                    <div class="sim-names">
                        <a href="${p.a.url}">${escapeHtml(p.a.name)}</a> &amp; <a href="${p.b.url}">${escapeHtml(p.b.name)}</a>
                    </div>
                    <div class="sim-fact">
                        Same wrong answer on <b>${p.shared}</b> of the ${p.both_wrong} questions both got wrong
                        (chance would give about ${p.expected}).
                    </div>
                </div>`).join('');
        } catch (e) { /* the next minute will try again */ }
    }
    loadSimilarity();
    setInterval(loadSimilarity, 60000);
})();
</script>
</body>
</html>
