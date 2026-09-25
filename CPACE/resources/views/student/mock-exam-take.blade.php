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
        .warn-overlay.lock { z-index:1000; background:rgba(20,24,40,.97); }
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

{{-- Blocks the whole exam while the camera or screen share is off. It is not
     dismissable: the only way out is to share again. The timer keeps running. --}}
<div class="warn-overlay lock" id="lockOverlay">
    <div>
        <i class="fas fa-lock" style="font-size:44px;margin-bottom:14px;"></i>
        <h2>Exam locked</h2>
        <p id="lockMsg">Your camera or screen sharing is off. Share it again to continue. Your time is still running.</p>
        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
            <button class="btn btn-ghost" type="button" id="lockCam"><i class="fas fa-video"></i> Share camera again</button>
            <button class="btn btn-ghost" type="button" id="lockScreen"><i class="fas fa-display"></i> Share screen again</button>
        </div>
        <p id="lockNote" style="margin-top:14px;font-size:12.5px;"></p>
    </div>
</div>

{{-- display:none — and, it turns out, parking the element thousands of pixels
     off-screen — both stop Chrome from actually decoding frames into it (the
     "pause video when not visible" battery optimization treats anything
     outside the viewport as invisible, same as display:none). So this stays
     inside the viewport at (0,0), just under everything else and fully
     transparent, which keeps it "visible" enough for the browser to keep
     decoding while the student never sees it. --}}
<video id="camFeed" autoplay muted playsinline
       style="position:fixed; left:0; top:0; width:2px; height:2px; opacity:0.01; z-index:-1; pointer-events:none;"></video>
<video id="screenFeed" autoplay muted playsinline
       style="position:fixed; left:0; top:0; width:2px; height:2px; opacity:0.01; z-index:-1; pointer-events:none;"></video>
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
        // readyState < 2 (HAVE_CURRENT_DATA) means no decoded frame exists yet —
        // drawImage would silently paint a black canvas instead of failing.
        if (!video.videoWidth || video.readyState < 2) return;
        // Downscaled hard: a 3-hour sitting is thousands of frames, and full
        // resolution would fill the server's disk within a term.
        // Wide enough to be read when a reviewer opens the frame full-size:
        // faces at 480px, on-screen text at 1280px.
        const w = kind === 'camera' ? 480 : 1280;
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

    // ── camera / screen state and the lock ───────────────────────────────
    // While either stream is off the whole exam is locked (inert + overlay).
    // The only way out is to share again. The timer keeps running, and the
    // loss itself was already flagged once, so re-sharing doesn't erase it.
    let camOk = false, screenOk = false;
    // Picking a screen opens a native dialog that blurs this window; that must
    // not be counted as "left the exam".
    let resharing = false;
    const lockEl = document.getElementById('lockOverlay');
    const examWrap = document.querySelector('.wrap');

    function updateLock() {
        const locked = !(camOk && screenOk) && !submitting;
        lockEl.classList.toggle('on', locked);
        examWrap.toggleAttribute('inert', locked);
        document.getElementById('lockCam').style.display = camOk ? 'none' : '';
        document.getElementById('lockScreen').style.display = screenOk ? 'none' : '';
        const what = !camOk && !screenOk ? 'camera and screen sharing are' : (!camOk ? 'camera is' : 'screen sharing is');
        document.getElementById('lockMsg').textContent =
            `Your ${what} off. Share again to continue. Your time is still running, and this has been recorded.`;
    }

    function lockNote(text) { document.getElementById('lockNote').textContent = text || ''; }

    function attachCamera(stream) {
        camStream = stream;
        camFeed.srcObject = stream;
        // Autoplay is not reliably honored for a srcObject assigned after load,
        // so play() is kicked off explicitly — muted, so no gesture is required.
        camFeed.play().catch(() => {});
        camOk = true;
        setCam(true);
        stream.getVideoTracks()[0].addEventListener('ended', () => {
            if (submitting || camStream !== stream) return;
            camOk = false;
            setCam(false);
            flag('camera_lost');
            updateLock();
        });
        updateLock();
    }

    function attachScreen(stream) {
        screenStream = stream;
        screenFeed.srcObject = stream;
        screenFeed.play().catch(() => {});
        screenOk = true;
        stream.getVideoTracks()[0].addEventListener('ended', () => {
            if (submitting || screenStream !== stream) return;
            screenOk = false;
            flag('screen_lost');
            updateLock();
        });
        updateLock();
    }

    async function shareCamera() {
        resharing = true;
        lockNote('');
        try {
            attachCamera(await navigator.mediaDevices.getUserMedia({ video: { width: 640 } }));
        } catch (e) {
            lockNote('Camera access was refused. Allow the camera in your browser, then try again.');
        } finally { setTimeout(() => { resharing = false; }, 1500); }
    }

    // Chrome and Edge report what was actually shared: 'monitor' (the entire
    // screen), 'window' or 'browser' (a single tab). Other browsers report
    // nothing, so they are let through rather than blocked for a limit of theirs.
    const SCREEN_OPTS = { video: { displaySurface: 'monitor' }, selfBrowserSurface: 'exclude', monitorTypeSurfaces: 'include' };

    function isWholeScreen(stream) {
        const surface = stream.getVideoTracks()[0]?.getSettings?.().displaySurface;
        return !surface || surface === 'monitor';
    }

    // Asks for the screen and refuses anything smaller than the whole display.
    async function acquireScreen() {
        const stream = await navigator.mediaDevices.getDisplayMedia(SCREEN_OPTS);
        if (!isWholeScreen(stream)) {
            stream.getTracks().forEach(t => t.stop());
            flag('partial_screen', 'shared a window or tab');
            throw new Error('partial');
        }
        return stream;
    }

    async function shareScreen() {
        resharing = true;
        lockNote('');
        try {
            attachScreen(await acquireScreen());
        } catch (e) {
            lockNote(e.message === 'partial'
                ? 'You shared a window or tab. Choose "Entire screen" and try again.'
                : 'Screen sharing was cancelled. Choose your entire screen and try again.');
        } finally { setTimeout(() => { resharing = false; }, 1500); }
    }

    document.getElementById('lockCam').addEventListener('click', shareCamera);
    document.getElementById('lockScreen').addEventListener('click', shareScreen);

    // ── face check (runs entirely in this browser) ───────────────────────
    // Uses MediaPipe's small BlazeFace detector. It reads the camera video
    // locally; no image leaves the device for this check. Only when something
    // is flagged is one frame uploaded as evidence. If the model can't load
    // (offline, blocked CDN) the check quietly does nothing: a failure of ours
    // must never be counted against the student.
    const FACE = {
        everyMs: 3000,      // how often a frame is examined
        noFaceAfter: 3,     // consecutive checks (~9s) with nobody in view
        multiAfter: 2,      // consecutive checks (~6s) with 2+ faces
        awayAfter: 4,       // consecutive checks (~12s) turned away
        awayRatio: 0.45,    // nose offset from eye-centre, in eye-distances
        cooldownMs: 60000,  // same flag type at most once a minute
    };
    const VISION = 'https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14';
    const FACE_MODEL = 'https://storage.googleapis.com/mediapipe-models/face_detector/blaze_face_short_range/float16/1/blaze_face_short_range.tflite';
    let faceDetector = null;
    const seen = { none: 0, multi: 0, away: 0 };
    const lastRaised = {};

    async function loadFaceDetector() {
        try {
            const vision = await import(VISION + '/+esm');
            const fileset = await vision.FilesetResolver.forVisionTasks(VISION + '/wasm');
            faceDetector = await vision.FaceDetector.createFromOptions(fileset, {
                baseOptions: { modelAssetPath: FACE_MODEL },
                runningMode: 'VIDEO',
                minDetectionConfidence: 0.6,
            });
            setInterval(faceTick, FACE.everyMs);
        } catch (e) { faceDetector = null; }
    }

    // Keypoint order from BlazeFace: right eye, left eye, nose tip, mouth, ears.
    function isLookingAway(detection) {
        const k = detection.keypoints;
        if (!k || k.length < 3) return false;
        const eyeDist = Math.abs(k[0].x - k[1].x);
        if (eyeDist < 0.02) return false;
        const eyeMid = (k[0].x + k[1].x) / 2;
        return Math.abs(k[2].x - eyeMid) / eyeDist > FACE.awayRatio;
    }

    function raiseFace(type, meta) {
        const now = Date.now();
        if (now - (lastRaised[type] || 0) < FACE.cooldownMs) return;
        lastRaised[type] = now;
        flag(type, meta);
        // A face flag is about the camera, so the camera frame is the only
        // evidence taken. (A screen frame here would just be confusing.)
        grab('camera', type);
    }

    function faceTick() {
        if (!faceDetector || !camOk || submitting) return;
        if (!camFeed.videoWidth || camFeed.readyState < 2) return;

        let faces;
        try { faces = faceDetector.detectForVideo(camFeed, performance.now()).detections || []; }
        catch (e) { return; }

        if (faces.length === 0) {
            seen.none++; seen.multi = 0; seen.away = 0;
            if (seen.none >= FACE.noFaceAfter) { raiseFace('no_face'); seen.none = 0; }
        } else if (faces.length >= 2) {
            seen.multi++; seen.none = 0; seen.away = 0;
            if (seen.multi >= FACE.multiAfter) { raiseFace('multiple_faces', faces.length + ' faces'); seen.multi = 0; }
        } else {
            seen.none = 0; seen.multi = 0;
            seen.away = isLookingAway(faces[0]) ? seen.away + 1 : 0;
            if (seen.away >= FACE.awayAfter) { raiseFace('looking_away'); seen.away = 0; }
        }
    }

    // Two things can change after the exam starts: a second monitor is plugged
    // in, or the student uses the browser's "share this tab instead" control to
    // narrow the share. Both are checked every few seconds.
    let monitorFlagged = false;
    function checkDisplay() {
        if (submitting) return;

        if (window.screen && window.screen.isExtended === true && !monitorFlagged) {
            monitorFlagged = true;
            flag('second_monitor');
            warn('A second monitor is connected', 'Only one screen may be used during the exam. This has been recorded.');
        } else if (window.screen && window.screen.isExtended === false) {
            monitorFlagged = false;
        }

        if (screenOk && screenStream && !isWholeScreen(screenStream)) {
            const stream = screenStream;
            stream.getTracks().forEach(t => t.stop());
            screenStream = null;
            screenOk = false;
            flag('partial_screen', 'switched to a window or tab');
            lockNote('Your shared screen was narrowed to a window or tab. Choose "Entire screen" again.');
            updateLock();
        }
    }

    async function startProctoring() {
        try {
            attachCamera(await navigator.mediaDevices.getUserMedia({ video: { width: 640 } }));
        } catch (e) {
            setCam(false);
            flag('camera_lost', 'not granted at start');
        }

        try {
            attachScreen(await acquireScreen());
        } catch (e) {
            // A partial share was already flagged as such; only a refusal is "not granted".
            if (e.message !== 'partial') flag('screen_lost', 'not granted at start');
            lockNote(e.message === 'partial' ? 'You shared a window or tab. Choose "Entire screen" and try again.' : '');
        }
        updateLock();
        setInterval(checkDisplay, 10000);
        checkDisplay();

        setTimeout(() => { grab('camera', 'start'); grab('screen', 'start'); }, 2500);
        // Routine frames are deliberately sparse: they are deleted at submit
        // for a clean sitting, so they only exist to show the room is being
        // watched. The frames that matter are the ones taken on a flag.
        setInterval(() => grab('camera', 'interval'), 120000);
        setInterval(() => grab('screen', 'interval'), 600000);
        loadFaceDetector();
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
        if (submitting || resharing) return;
        const now = Date.now();
        // Debounced: blur and visibilitychange both fire on a single alt-tab,
        // which would otherwise double-count one action.
        if (now - lastFlag < 1500) return;
        lastFlag = now;
        flag(type);
        grab('camera', type);
        grab('screen', type);
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
