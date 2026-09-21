<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $exam->title }} - In progress</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.mock-exam-styles')
    <style>
        /* The runner deliberately has no sidebar: fewer exits, fewer accidents. */
        body { background:#eef0f4; }
        .exam-bar { position:sticky; top:0; z-index:500; background:#fff; border-bottom:1px solid var(--line);
                    padding:12px 22px; display:flex; align-items:center; gap:18px; flex-wrap:wrap;
                    box-shadow:0 2px 10px -6px rgba(0,0,0,.25); }
        .exam-bar .t { font-size:14px; font-weight:700; color:var(--ink); }
        .exam-bar .s { font-size:11.5px; color:var(--muted); }
        .timer { margin-left:auto; font-family:'Montserrat',monospace; font-size:22px; font-weight:700;
                 color:var(--ink); background:#f4f5f7; padding:7px 15px; border-radius:10px; }
        .timer.warn { background:#fdf0d8; color:var(--amber); }
        .timer.danger { background:#fdeceb; color:var(--red); animation:pulse 1.2s infinite; }
        @keyframes pulse { 50% { opacity:.55; } }
        .cam-dot { display:flex; align-items:center; gap:7px; font-size:11.5px; color:var(--muted); }
        .cam-dot .d { width:9px; height:9px; border-radius:50%; background:var(--green); }
        .cam-dot.off .d { background:var(--red); }

        .wrap { max-width:1180px; margin:0 auto; padding:22px; display:grid; grid-template-columns:1fr 220px; gap:20px; align-items:start; }
        .q-card { background:#fff; border:1px solid var(--line); border-radius:13px; padding:22px 24px; margin-bottom:14px; }
        .q-num { font-size:11.5px; font-weight:700; color:var(--primary); letter-spacing:.5px; text-transform:uppercase; }
        .q-text { font-size:14.5px; line-height:1.65; color:var(--ink); margin:8px 0 16px; }
        .opt { display:flex; gap:11px; align-items:flex-start; padding:12px 14px; border:1px solid var(--line);
               border-radius:10px; margin-bottom:9px; cursor:pointer; font-size:13.5px; line-height:1.55; transition:all .15s; }
        .opt:hover { border-color:var(--primary); background:#fdf9f9; }
        .opt input { margin-top:3px; }
        .opt.picked { border-color:var(--primary); background:var(--primary-light); }
        .opt .lbl { font-weight:700; color:var(--primary); min-width:17px; }

        .palette { position:sticky; top:82px; background:#fff; border:1px solid var(--line); border-radius:13px; padding:16px; }
        .palette-grid { display:grid; grid-template-columns:repeat(5, 1fr); gap:7px; margin-top:12px; max-height:340px; overflow-y:auto; }
        .pal { aspect-ratio:1; border:1px solid var(--line); border-radius:7px; background:#fff; cursor:pointer;
               font-size:11.5px; font-weight:600; color:var(--muted); font-family:'Poppins',sans-serif; }
        .pal.done { background:var(--primary); border-color:var(--primary); color:#fff; }

        .warn-overlay { position:fixed; inset:0; background:rgba(120,15,15,.94); color:#fff; z-index:999;
                        display:none; align-items:center; justify-content:center; text-align:center; padding:30px; }
        .warn-overlay.on { display:flex; }
        .warn-overlay h2 { font-size:24px; margin-bottom:10px; }
        .warn-overlay p { font-size:14px; opacity:.9; max-width:480px; margin:0 auto 18px; line-height:1.7; }

        @media (max-width: 900px) { .wrap { grid-template-columns:1fr; } .palette { position:static; } }
    </style>
</head>
<body>
<div class="exam-bar">
    <div>
        <div class="t">{{ $exam->title }}</div>
        <div class="s">{{ $subject->code }} · {{ $items->count() }} questions · answers save automatically</div>
    </div>
    <div class="cam-dot" id="camDot"><span class="d"></span> <span id="camLabel">Recording</span></div>
    <div class="timer" id="timer">--:--:--</div>
</div>

<div class="wrap">
    <form method="POST" action="{{ route('mock-exams.submit', $exam) }}" id="examForm">
        @csrf
        @foreach($items as $i => $item)
            <div class="q-card" id="q{{ $i + 1 }}">
                <div class="q-num">Question {{ $i + 1 }} of {{ $items->count() }}</div>
                <div class="q-text">{{ $item->question_text }}</div>
                @foreach((array) $item->choices as $choice)
                    @php $label = $choice['label'] ?? ''; @endphp
                    <label class="opt {{ ($answers[(string) $item->id] ?? null) === $label ? 'picked' : '' }}">
                        <input type="radio" name="answers[{{ $item->id }}]" value="{{ $label }}"
                               @checked(($answers[(string) $item->id] ?? null) === $label)>
                        <span class="lbl">{{ $label }}.</span>
                        <span>{{ $choice['text'] ?? '' }}</span>
                    </label>
                @endforeach
            </div>
        @endforeach

        <button class="btn btn-primary" type="button" id="submitBtn" style="width:100%;padding:14px;font-size:14px;">
            <i class="fas fa-paper-plane"></i> Submit exam
        </button>
    </form>

    <div class="palette">
        <div style="font-size:12.5px;font-weight:700;color:var(--ink);">Progress</div>
        <div style="font-size:11.5px;color:var(--muted);margin-top:3px;">
            <span id="answeredCount">0</span> of {{ $items->count() }} answered
        </div>
        <div class="palette-grid">
            @foreach($items as $i => $item)
                <button type="button" class="pal" data-q="{{ $i + 1 }}">{{ $i + 1 }}</button>
            @endforeach
        </div>
    </div>
</div>

{{-- Shown when the student leaves the window or drops a permission. --}}
<div class="warn-overlay" id="warnOverlay">
    <div>
        <i class="fas fa-triangle-exclamation" style="font-size:44px;margin-bottom:14px;"></i>
        <h2 id="warnTitle">Stay on the exam</h2>
        <p id="warnBody">Leaving the exam window has been recorded and reported to your faculty.</p>
        <button class="btn btn-ghost" type="button" id="warnBack">Return to the exam</button>
    </div>
</div>

<video id="camFeed" autoplay muted playsinline style="display:none;"></video>
<video id="screenFeed" autoplay muted playsinline style="display:none;"></video>
<canvas id="shot" style="display:none;"></canvas>

<script>
(function () {
    const EXAM = {
        submit: @json(route('mock-exams.submit', $exam)),
        autosave: @json(route('mock-exams.autosave', $exam)),
        event: @json(route('mock-exams.proctor.event', $attempt)),
        capture: @json(route('mock-exams.proctor.capture', $attempt)),
        csrf: @json(csrf_token()),
    };
    let secondsLeft = {{ $secondsLeft }};
    let submitting = false;

    // ── timer ────────────────────────────────────────────────────────────
    // The server computed secondsLeft from started_at. This only DISPLAYS the
    // countdown; submit() re-derives lateness server-side, so freezing this
    // clock gains nothing.
    const timerEl = document.getElementById('timer');
    function renderTimer() {
        const s = Math.max(0, secondsLeft);
        const h = Math.floor(s / 3600), m = Math.floor(s / 60) % 60, sec = s % 60;
        timerEl.textContent = [h, m, sec].map(n => String(n).padStart(2, '0')).join(':');
        timerEl.classList.toggle('warn', s <= 600 && s > 120);
        timerEl.classList.toggle('danger', s <= 120);
    }
    renderTimer();
    setInterval(() => {
        secondsLeft--;
        renderTimer();
        if (secondsLeft <= 0 && !submitting) {
            submitting = true;
            alert('Time is up. Your exam is being submitted.');
            document.getElementById('examForm').submit();
        }
    }, 1000);

    // ── answers, palette, autosave ───────────────────────────────────────
    const form = document.getElementById('examForm');

    function answeredMap() {
        const map = {};
        form.querySelectorAll('input[type=radio]:checked').forEach(r => {
            map[r.name.replace(/^answers\[|\]$/g, '')] = r.value;
        });
        return map;
    }

    function refreshPalette() {
        const cards = document.querySelectorAll('.q-card');
        let answered = 0;
        cards.forEach((card, i) => {
            const done = card.querySelector('input[type=radio]:checked') !== null;
            if (done) answered++;
            const pal = document.querySelector(`.pal[data-q="${i + 1}"]`);
            if (pal) pal.classList.toggle('done', done);
        });
        document.getElementById('answeredCount').textContent = answered;
    }

    form.addEventListener('change', e => {
        if (e.target.type === 'radio') {
            e.target.closest('.q-card').querySelectorAll('.opt').forEach(o => o.classList.remove('picked'));
            e.target.closest('.opt').classList.add('picked');
            refreshPalette();
        }
    });

    document.querySelectorAll('.pal').forEach(btn => btn.addEventListener('click', () => {
        document.getElementById('q' + btn.dataset.q)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }));

    async function autosave() {
        try {
            const res = await fetch(EXAM.autosave, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': EXAM.csrf },
                body: JSON.stringify({ answers: answeredMap() }),
            });
            if (res.ok) {
                const data = await res.json();
                // Re-sync from the server so a paused or throttled tab can't
                // drift the displayed clock away from the real one.
                if (typeof data.seconds_left === 'number') secondsLeft = data.seconds_left;
            }
        } catch (e) { /* retried on the next tick */ }
    }
    setInterval(autosave, 20000);
    refreshPalette();

    document.getElementById('submitBtn').addEventListener('click', () => {
        const total = document.querySelectorAll('.q-card').length;
        const done = Object.keys(answeredMap()).length;
        const msg = done < total
            ? `You have answered ${done} of ${total}. Unanswered questions are marked wrong.\n\nSubmit anyway?`
            : 'Submit your exam? You cannot change your answers afterwards.';
        if (!confirm(msg)) return;
        submitting = true;
        form.submit();
    });

    // ── proctoring ───────────────────────────────────────────────────────
    // Browsers cannot be made to capture silently: both streams below were
    // granted by the student on the consent screen, the browser shows a
    // sharing indicator throughout, and the student can revoke at any time —
    // which is itself recorded as a flag.
    const canvas = document.getElementById('shot');
    const camFeed = document.getElementById('camFeed');
    const screenFeed = document.getElementById('screenFeed');
    let camStream = null, screenStream = null;

    async function flag(type, meta) {
        try {
            await fetch(EXAM.event, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': EXAM.csrf },
                body: JSON.stringify({ type, meta: meta || null }),
            });
        } catch (e) { /* the sitting matters more than the telemetry */ }
    }

    async function grab(kind, reason) {
        const video = kind === 'camera' ? camFeed : screenFeed;
        if (!video.videoWidth) return;
        // Downscaled hard: a 3-hour sitting is thousands of frames, and full
        // resolution would fill the server's disk within a term.
        const w = kind === 'camera' ? 320 : 960;
        const h = Math.round(w * (video.videoHeight / video.videoWidth));
        canvas.width = w; canvas.height = h;
        canvas.getContext('2d').drawImage(video, 0, 0, w, h);

        const blob = await new Promise(r => canvas.toBlob(r, 'image/jpeg', 0.6));
        if (!blob) return;

        const body = new FormData();
        body.append('kind', kind);
        body.append('reason', reason || 'interval');
        body.append('frame', blob, kind + '.jpg');
        try {
            await fetch(EXAM.capture, { method: 'POST', headers: { 'X-CSRF-TOKEN': EXAM.csrf }, body });
        } catch (e) { /* ignore */ }
    }

    async function startProctoring() {
        try {
            camStream = await navigator.mediaDevices.getUserMedia({ video: { width: 640 } });
            camFeed.srcObject = camStream;
            camStream.getVideoTracks()[0].addEventListener('ended', () => {
                setCam(false);
                flag('camera_lost');
                warn('Your camera was turned off', 'This has been recorded. Turn it back on and reload to continue being monitored.');
            });
        } catch (e) {
            setCam(false);
            flag('camera_lost', 'not granted at start');
        }

        try {
            screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true });
            screenFeed.srcObject = screenStream;
            screenStream.getVideoTracks()[0].addEventListener('ended', () => {
                flag('screen_lost');
                warn('Screen sharing stopped', 'This has been recorded and reported to your faculty.');
            });
        } catch (e) {
            flag('screen_lost', 'not granted at start');
        }

        setTimeout(() => { grab('camera', 'start'); grab('screen', 'start'); }, 2500);
        setInterval(() => grab('camera', 'interval'), 60000);
        setInterval(() => grab('screen', 'interval'), 300000);
    }

    function setCam(on) {
        const dot = document.getElementById('camDot');
        dot.classList.toggle('off', !on);
        document.getElementById('camLabel').textContent = on ? 'Recording' : 'Camera off';
    }

    const overlay = document.getElementById('warnOverlay');
    function warn(title, body) {
        document.getElementById('warnTitle').textContent = title;
        document.getElementById('warnBody').textContent = body;
        overlay.classList.add('on');
    }
    document.getElementById('warnBack').addEventListener('click', () => overlay.classList.remove('on'));

    // Leaving the window: flagged, and a frame is grabbed at that moment so
    // there is a picture attached to the flag rather than just a timestamp.
    let lastFlag = 0;
    function leaveFlag(type, title, body) {
        if (submitting) return;
        const now = Date.now();
        // Debounced: blur and visibilitychange both fire on a single alt-tab,
        // which would otherwise double-count one action.
        if (now - lastFlag < 1500) return;
        lastFlag = now;
        flag(type);
        grab('camera', type);
        warn(title, body);
    }

    window.addEventListener('blur', () => leaveFlag('blur', 'Stay on the exam',
        'Leaving the exam window has been recorded and reported to your faculty.'));
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) leaveFlag('visibility_hidden', 'You switched away',
            'Switching tabs or minimising during the exam has been recorded.');
    });
    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement) leaveFlag('fullscreen_exit', 'You left fullscreen',
            'The exam should stay in fullscreen. This has been recorded.');
    });

    ['copy', 'paste', 'cut'].forEach(evt => document.addEventListener(evt, e => {
        e.preventDefault();
        flag('paste_blocked', evt);
    }));
    document.addEventListener('contextmenu', e => e.preventDefault());

    window.addEventListener('beforeunload', e => {
        if (submitting) return;
        e.preventDefault();
        e.returnValue = '';
    });

    // Fullscreen needs a gesture, so it is requested on the student's first
    // interaction rather than on load (where the browser would refuse it).
    document.addEventListener('click', function once() {
        document.documentElement.requestFullscreen?.().catch(() => {});
        document.removeEventListener('click', once);
    }, { once: true });

    startProctoring();
})();
</script>
</body>
</html>
