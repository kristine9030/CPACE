{{--
    Proctoring for a monitored class quiz, adapted from the mock exam runner
    (student/mock-exam-take.blade.php): camera and whole-screen sharing are
    required, tab switches / leaving fullscreen / an absent, extra or turned-away
    face are flagged, and the quiz locks while either stream is off.

    Browsers cannot capture silently: both streams were granted by the student
    on the quiz's start page, the browser shows a sharing indicator throughout,
    and revoking either one is itself flagged.

    Expects $eventUrl, $captureUrl and $heartbeatUrl. The host page calls
    window.quizProctorFinish() just before it submits.
--}}
<style>
    .qp-overlay { position:fixed; inset:0; background:rgba(120,15,15,.94); color:#fff; z-index:999; display:none;
                  align-items:center; justify-content:center; text-align:center; padding:30px; }
    .qp-overlay.on { display:flex; }
    .qp-overlay.lock { z-index:1000; background:rgba(20,24,40,.97); }
    .qp-overlay h2 { font-size:24px; margin-bottom:10px; }
    .qp-overlay p { font-size:14px; opacity:.9; max-width:480px; margin:0 auto 18px; line-height:1.7; }
    .qp-btn { display:inline-flex; align-items:center; gap:8px; padding:11px 18px; border-radius:10px; border:1px solid rgba(255,255,255,.4);
              background:rgba(255,255,255,.12); color:#fff; font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; cursor:pointer; }
    .qp-btn:hover { background:rgba(255,255,255,.22); }
    .qp-dot { display:inline-flex; align-items:center; gap:7px; font-size:11.5px; background:rgba(255,255,255,.18); border-radius:20px; padding:7px 12px; font-weight:600; }
    .qp-dot .d { width:9px; height:9px; border-radius:50%; background:#34d399; }
    .qp-dot.off .d { background:#f87171; }
</style>

<div class="qp-overlay" id="qpWarn">
    <div>
        <i class="fas fa-triangle-exclamation" style="font-size:44px;margin-bottom:14px;"></i>
        <h2 id="qpWarnTitle">Stay on the quiz</h2>
        <p id="qpWarnBody">Leaving the quiz window has been recorded and reported to your faculty.</p>
        <button class="qp-btn" type="button" id="qpWarnBack">Return to the quiz</button>
    </div>
</div>

<div class="qp-overlay lock" id="qpLock">
    <div>
        <i class="fas fa-lock" style="font-size:44px;margin-bottom:14px;"></i>
        <h2>Quiz locked</h2>
        <p id="qpLockMsg">Your camera or screen sharing is off. Share it again to continue. Your time is still running.</p>
        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
            <button class="qp-btn" type="button" id="qpLockCam"><i class="fas fa-video"></i> Share camera again</button>
            <button class="qp-btn" type="button" id="qpLockScreen"><i class="fas fa-display"></i> Share screen again</button>
        </div>
        <p id="qpLockNote" style="margin-top:14px;font-size:12.5px;"></p>
    </div>
</div>

{{-- Kept inside the viewport and nearly transparent: Chrome stops decoding frames
     for video that is display:none or off-screen, which would make every capture black. --}}
<video id="qpCam" autoplay muted playsinline style="position:fixed;left:0;top:0;width:2px;height:2px;opacity:0.01;z-index:-1;pointer-events:none;"></video>
<video id="qpScreen" autoplay muted playsinline style="position:fixed;left:0;top:0;width:2px;height:2px;opacity:0.01;z-index:-1;pointer-events:none;"></video>
<canvas id="qpShot" style="display:none;"></canvas>

<script>
(function () {
    const URLS = { event: @json($eventUrl), capture: @json($captureUrl), heartbeat: @json($heartbeatUrl), csrf: @json(csrf_token()) };
    const canvas = document.getElementById('qpShot');
    const camFeed = document.getElementById('qpCam');
    const screenFeed = document.getElementById('qpScreen');
    const lockEl = document.getElementById('qpLock');
    const overlay = document.getElementById('qpWarn');
    const dot = document.getElementById('qpDot');
    let camStream = null, screenStream = null;
    let camOk = false, screenOk = false;
    let submitting = false;
    // Picking a screen opens a native dialog that blurs this window; that must
    // not be counted as leaving the quiz.
    let resharing = false;

    window.quizProctorFinish = function () { submitting = true; updateLock(); };

    async function flag(type, meta) {
        try {
            await fetch(URLS.event, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': URLS.csrf },
                body: JSON.stringify({ type, meta: meta || null }),
            });
        } catch (e) { /* the quiz matters more than the telemetry */ }
    }

    async function grab(kind, reason) {
        const video = kind === 'camera' ? camFeed : screenFeed;
        if (!video.videoWidth || video.readyState < 2) return;
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
        try { await fetch(URLS.capture, { method: 'POST', headers: { 'X-CSRF-TOKEN': URLS.csrf }, body }); } catch (e) { /* ignore */ }
    }

    function setCam(on) {
        if (!dot) return;
        dot.classList.toggle('off', !on);
        document.getElementById('qpDotLabel').textContent = on ? 'Monitored' : 'Camera off';
    }

    function lockNote(text) { document.getElementById('qpLockNote').textContent = text || ''; }

    function updateLock() {
        const locked = !(camOk && screenOk) && !submitting;
        lockEl.classList.toggle('on', locked);
        document.querySelectorAll('.wrap, .bottom').forEach(el => el.toggleAttribute('inert', locked));
        document.getElementById('qpLockCam').style.display = camOk ? 'none' : '';
        document.getElementById('qpLockScreen').style.display = screenOk ? 'none' : '';
        const what = !camOk && !screenOk ? 'camera and screen sharing are' : (!camOk ? 'camera is' : 'screen sharing is');
        document.getElementById('qpLockMsg').textContent =
            `Your ${what} off. Share again to continue. Your time is still running, and this has been recorded.`;
    }

    function attachCamera(stream) {
        camStream = stream;
        camFeed.srcObject = stream;
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
        try { attachCamera(await navigator.mediaDevices.getUserMedia({ video: { width: 640 } })); }
        catch (e) { lockNote('Camera access was refused. Allow the camera in your browser, then try again.'); }
        finally { setTimeout(() => { resharing = false; }, 1500); }
    }

    // Chrome and Edge report what was actually shared; other browsers report
    // nothing and are let through rather than blocked for a limit of theirs.
    const SCREEN_OPTS = { video: { displaySurface: 'monitor' }, selfBrowserSurface: 'exclude', monitorTypeSurfaces: 'include' };
    function isWholeScreen(stream) {
        const surface = stream.getVideoTracks()[0]?.getSettings?.().displaySurface;
        return !surface || surface === 'monitor';
    }
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
        try { attachScreen(await acquireScreen()); }
        catch (e) {
            lockNote(e.message === 'partial'
                ? 'You shared a window or tab. Choose "Entire screen" and try again.'
                : 'Screen sharing was cancelled. Choose your entire screen and try again.');
        } finally { setTimeout(() => { resharing = false; }, 1500); }
    }
    document.getElementById('qpLockCam').addEventListener('click', shareCamera);
    document.getElementById('qpLockScreen').addEventListener('click', shareScreen);

    // ── face check (runs entirely in this browser) ───────────────────────
    // MediaPipe's small BlazeFace detector reads the camera locally; nothing is
    // uploaded for the check itself, only a frame when something is flagged. If
    // the model can't load the check quietly does nothing: a failure of ours
    // must never count against the student.
    const FACE = { everyMs: 3000, noFaceAfter: 3, multiAfter: 2, awayAfter: 4, awayRatio: 0.45, cooldownMs: 60000 };
    const VISION = 'https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14';
    const FACE_MODEL = 'https://storage.googleapis.com/mediapipe-models/face_detector/blaze_face_short_range/float16/1/blaze_face_short_range.tflite';
    let faceDetector = null;
    const seen = { none: 0, multi: 0, away: 0 };
    const lastRaised = {};

    // A face check that never ran must be visible to the reviewer, or a clean
    // face record would look like good behaviour. Informational: no points.
    let faceReported = false, faceErrors = 0;
    function faceUnavailable(reason) {
        faceDetector = null;
        if (faceReported) return;
        faceReported = true;
        flag('face_check_unavailable', String(reason || 'could not load').slice(0, 120));
    }

    async function loadFaceDetector() {
        try {
            const vision = await import(VISION + '/+esm');
            const fileset = await vision.FilesetResolver.forVisionTasks(VISION + '/wasm');
            faceDetector = await vision.FaceDetector.createFromOptions(fileset, {
                baseOptions: { modelAssetPath: FACE_MODEL }, runningMode: 'VIDEO', minDetectionConfidence: 0.6,
            });
            setInterval(faceTick, FACE.everyMs);
        } catch (e) { faceUnavailable(e && e.message); }
    }

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
        grab('camera', type);
    }

    function faceTick() {
        if (!faceDetector || !camOk || submitting) return;
        if (!camFeed.videoWidth || camFeed.readyState < 2) return;
        let faces;
        try { faces = faceDetector.detectForVideo(camFeed, performance.now()).detections || []; faceErrors = 0; }
        catch (e) { if (++faceErrors >= 5) faceUnavailable('detector kept failing'); return; }

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

    // A second monitor plugged in, or the share narrowed to a tab mid-quiz.
    let monitorFlagged = false;
    function checkDisplay() {
        if (submitting) return;
        if (window.screen && window.screen.isExtended === true && !monitorFlagged) {
            monitorFlagged = true;
            flag('second_monitor');
            warn('A second monitor is connected', 'Only one screen may be used during this quiz. This has been recorded.');
        } else if (window.screen && window.screen.isExtended === false) {
            monitorFlagged = false;
        }
        if (screenOk && screenStream && !isWholeScreen(screenStream)) {
            screenStream.getTracks().forEach(t => t.stop());
            screenStream = null;
            screenOk = false;
            flag('partial_screen', 'switched to a window or tab');
            lockNote('Your shared screen was narrowed to a window or tab. Choose "Entire screen" again.');
            updateLock();
        }
    }

    function warn(title, body) {
        document.getElementById('qpWarnTitle').textContent = title;
        document.getElementById('qpWarnBody').textContent = body;
        overlay.classList.add('on');
    }
    document.getElementById('qpWarnBack').addEventListener('click', () => overlay.classList.remove('on'));

    // Leaving the window: flagged, with a frame grabbed at that moment so the
    // flag has a picture and not just a timestamp.
    let lastFlag = 0;
    function leaveFlag(type, title, body) {
        if (submitting || resharing) return;
        const now = Date.now();
        // blur and visibilitychange both fire on one alt-tab; count it once.
        if (now - lastFlag < 1500) return;
        lastFlag = now;
        flag(type);
        grab('camera', type);
        grab('screen', type);
        warn(title, body);
    }
    window.addEventListener('blur', () => leaveFlag('blur', 'Stay on the quiz',
        'Leaving the quiz window has been recorded and reported to your faculty.'));
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) leaveFlag('visibility_hidden', 'You switched away',
            'Switching tabs or minimising during the quiz has been recorded.');
    });
    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement) leaveFlag('fullscreen_exit', 'You left fullscreen',
            'The quiz should stay in fullscreen. This has been recorded.');
    });
    ['copy', 'paste', 'cut'].forEach(evt => document.addEventListener(evt, e => {
        e.preventDefault();
        flag('paste_blocked', evt);
    }));
    document.addEventListener('contextmenu', e => e.preventDefault());
    // Backs up the .wrap { user-select:none } in class-quiz-take.blade.php:
    // if anything still manages to select text, block the selection itself
    // so there's never a live selection for a browser's built-in "ask AI /
    // look this up" popup (e.g. Edge Copilot) to latch onto.
    document.addEventListener('selectstart', e => e.preventDefault());

    // Fullscreen needs a gesture, so it is requested on the first click.
    document.addEventListener('click', function once() {
        document.documentElement.requestFullscreen?.().catch(() => {});
        document.removeEventListener('click', once);
    }, { once: true });

    // Pulse for the server: any long silence is recorded as a "no signal" flag,
    // so a page that stops reporting can't pass for a student who behaved.
    // Started before the permission prompts so waiting on them isn't a gap.
    function beat() {
        try { fetch(URLS.heartbeat, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': URLS.csrf }, keepalive: true }).catch(() => {}); } catch (e) { /* ignore */ }
    }

    async function start() {
        beat();
        setInterval(beat, 30000);

        try {
            attachCamera(await navigator.mediaDevices.getUserMedia({ video: { width: 640 } }));
        } catch (e) {
            setCam(false);
            flag('camera_lost', 'not granted at start');
        }
        try {
            attachScreen(await acquireScreen());
        } catch (e) {
            if (e.message !== 'partial') flag('screen_lost', 'not granted at start');
            lockNote(e.message === 'partial' ? 'You shared a window or tab. Choose "Entire screen" and try again.' : '');
        }
        updateLock();
        setInterval(checkDisplay, 10000);
        checkDisplay();

        setTimeout(() => { grab('camera', 'start'); grab('screen', 'start'); }, 2500);
        // Routine frames are sparse and deleted at submit for a clean sitting;
        // the frames that matter are the ones taken on a flag.
        setInterval(() => grab('camera', 'interval'), 60000);
        setInterval(() => grab('screen', 'interval'), 600000);
        loadFaceDetector();
    }
    start();
})();
</script>
