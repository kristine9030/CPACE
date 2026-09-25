<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $exam->title }} - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.mock-exam-styles')
    <style>
        .facts { display:grid; grid-template-columns:repeat(3, 1fr); gap:14px; margin-top:16px; }
        .fact { background:#f8f9fb; border-radius:11px; padding:14px 16px; }
        .fact .n { font-size:21px; font-weight:700; color:var(--ink); font-family:'Montserrat',sans-serif; }
        .fact .l { font-size:11.5px; color:var(--muted); margin-top:2px; }
        .rules { margin:14px 0 0; padding-left:20px; }
        .rules li { font-size:13px; line-height:1.9; color:#444; }
        .perm { display:flex; align-items:center; gap:12px; padding:13px 15px; border:1px solid var(--line);
                border-radius:11px; margin-bottom:10px; }
        .perm .ic { width:38px; height:38px; border-radius:10px; background:#f1f2f5; color:var(--muted);
                    display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; }
        .perm.ok { border-color:var(--green); background:#f5fcf8; }
        .perm.ok .ic { background:#dff3e8; color:var(--green); }
        .perm.fail { border-color:var(--red); background:#fdf7f6; }
        .perm.fail .ic { background:#fdeceb; color:var(--red); }
        .perm-body { flex:1; }
        .perm-t { font-size:13px; font-weight:600; color:var(--ink); }
        .perm-s { font-size:11.5px; color:var(--muted); margin-top:2px; }
        .countdown-big { font-family:'Montserrat',monospace; font-size:24px; font-weight:700; color:var(--primary); }
        @media (max-width: 700px) { .facts { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<div class="dashboard-container">
    @include('partials.sidebar', ['active' => 'mock-exams'])
    @include('partials.student-bottom-nav', ['active' => 'mock-exams'])
    @include('partials.student-mobile-header')

    <main class="main-content">
        @include('partials.student-page-header', [
            'title' => $exam->title,
            'subtitle' => $subject->name,
            'back' => ['url' => route('mock-exams.subject', $exam->subject_id), 'label' => $subject->code . ' folder'],
        ])

        @error('exam')<div class="banner banner-danger"><i class="fas fa-triangle-exclamation"></i><div>{{ $message }}</div></div>@enderror

        <div class="card">
            <div class="card-title"><i class="fas fa-circle-info"></i> Exam details</div>
            <div class="facts">
                <div class="fact">
                    <div class="n">{{ $exam->total_items }}</div>
                    <div class="l">Questions</div>
                </div>
                <div class="fact">
                    <div class="n">{{ $exam->duration_minutes }} min</div>
                    <div class="l">Time limit</div>
                </div>
                <div class="fact">
                    <div class="n">{{ $exam->scheduled_at?->format('g:i A') }}</div>
                    <div class="l">{{ $exam->scheduled_at?->format('M j, Y') }}</div>
                </div>
            </div>

            @if($window === 'upcoming')
                <div class="banner banner-info" style="margin:18px 0 0;">
                    <i class="fas fa-hourglass-start"></i>
                    <div>
                        This exam hasn't opened yet.
                        <div class="countdown-big" data-starts="{{ $exam->scheduled_at?->toIso8601String() }}" style="margin-top:6px;">—</div>
                    </div>
                </div>
            @elseif($window === 'ended')
                <div class="banner banner-warn" style="margin:18px 0 0;">
                    <i class="fas fa-circle-xmark"></i>
                    <div>This exam window has closed.</div>
                </div>
            @endif
        </div>

        @if($attempt?->isSubmitted())
            <div class="card">
                <div class="card-title"><i class="fas fa-circle-check"></i> You've already submitted</div>
                <div class="card-sub">A mock exam can only be taken once.</div>
                <a class="btn btn-primary" style="margin-top:14px;" href="{{ route('mock-exams.result', $exam) }}">
                    <i class="fas fa-chart-simple"></i> View your result
                </a>
            </div>
        @elseif($window === 'open')
            <div class="card">
                <div class="card-title"><i class="fas fa-shield-halved"></i> Before you start — what gets recorded</div>
                <div class="card-sub">Read this carefully. Starting the exam means you agree to it.</div>

                <ul class="rules">
                    <li>Your <strong>camera</strong> is photographed periodically (about once every two minutes) for the whole exam.</li>
                    <li>Your <strong>shared screen</strong> is captured every ten minutes or so.</li>
                    <li>Your browser checks, <strong>on your own device</strong>, that exactly one face is in view and that you are
                        facing the screen. Nothing is uploaded for this check; only a flagged moment is photographed.</li>
                    <li>Switching tabs, leaving fullscreen, or an absent, extra or turned-away face is
                        <strong>recorded and flagged</strong> to your faculty and the Program Chair.</li>
                    <li>If your camera or screen sharing stops, the exam is <strong>locked</strong> until you share again. The timer keeps running.</li>
                    <li>Copy and paste are blocked inside the exam.</li>
                    <li>You must share your <strong>entire screen</strong> (not a window or tab) and use <strong>one monitor</strong>.</li>
                    <li>If you finish with no flags, your recordings are <strong>deleted as soon as you submit</strong>, apart from your
                        opening camera photo. Otherwise only the flagged frames are kept. Anything kept is deleted after
                        {{ \App\Models\MockExamProctorCapture::RETENTION_DAYS }} days at most, and is visible only to your faculty and the Program Chair.</li>
                    <li>If you close the tab without submitting, your saved answers are graded when your time runs out.</li>
                    <li>Your browser will ask permission for the camera and for screen sharing — you must allow both to enter.</li>
                </ul>

                <div style="margin-top:18px;">
                    <div class="perm" id="permCam">
                        <div class="ic"><i class="fas fa-video"></i></div>
                        <div class="perm-body">
                            <div class="perm-t">Camera</div>
                            <div class="perm-s" id="permCamMsg">Not granted yet</div>
                        </div>
                        <button class="btn btn-ghost btn-sm" type="button" id="grantCam">Allow</button>
                    </div>
                    <div class="perm" id="permScreen">
                        <div class="ic"><i class="fas fa-display"></i></div>
                        <div class="perm-body">
                            <div class="perm-t">Screen sharing</div>
                            <div class="perm-s" id="permScreenMsg">Not shared yet</div>
                        </div>
                        <button class="btn btn-ghost btn-sm" type="button" id="grantScreen">Share</button>
                    </div>
                </div>

                <div class="banner banner-warn" style="margin-top:16px;">
                    <i class="fas fa-triangle-exclamation"></i>
                    <div>
                        Once you start, the timer runs on the server. Closing the tab or losing power
                        <strong>does not pause it</strong> — your answers are saved as you go, so reopen the
                        exam and carry on.
                    </div>
                </div>

                <form method="POST" action="{{ route('mock-exams.start', $exam) }}" id="startForm" style="margin-top:16px;">
                    @csrf
                    <label style="display:flex;gap:9px;align-items:flex-start;font-size:13px;margin-bottom:14px;cursor:pointer;">
                        <input type="checkbox" id="agree" style="margin-top:3px;">
                        <span>I understand my camera and screen will be recorded during this exam, and I agree to sit it under these conditions.</span>
                    </label>
                    <button class="btn btn-primary" type="submit" id="startBtn" disabled>
                        <i class="fas fa-play"></i> {{ $attempt ? 'Resume exam' : 'Start exam' }}
                    </button>
                    <div class="field-hint" id="startHint" style="margin-top:8px;">
                        Allow the camera and screen sharing above, then tick the box.
                    </div>
                </form>
            </div>
        @endif
    </main>
</div>

@include('partials.global-search')
@include('partials.alerts')
<script>
(function () {
    const profileBtn = document.getElementById('profileBtn');
    const profileDrop = document.getElementById('profileDropdown');
    if (profileBtn && profileDrop) {
        profileBtn.addEventListener('click', e => { e.stopPropagation(); profileDrop.classList.toggle('active'); });
        document.addEventListener('click', () => profileDrop.classList.remove('active'));
        profileDrop.addEventListener('click', e => e.stopPropagation());
    }

    // Countdown for an exam that hasn't opened yet.
    const cd = document.querySelector('.countdown-big[data-starts]');
    if (cd) {
        const tick = () => {
            const diff = new Date(cd.dataset.starts) - new Date();
            if (diff <= 0) { cd.textContent = 'Opening now — refresh the page'; return; }
            const h = Math.floor(diff / 3600000);
            const m = Math.floor(diff / 60000) % 60;
            const s = Math.floor(diff / 1000) % 60;
            cd.textContent = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        };
        tick(); setInterval(tick, 1000);
    }

    const startBtn = document.getElementById('startBtn');
    if (!startBtn) return;

    // The browser will not let a page open the camera or read the screen without
    // an explicit prompt and a user gesture, and shows a persistent sharing
    // indicator. So the gate is honest about it: we ask, and we only let the
    // student in once both are actually granted.
    let camOk = false, screenOk = false;

    function paint(id, msgId, ok, text) {
        const box = document.getElementById(id);
        box.classList.toggle('ok', ok === true);
        box.classList.toggle('fail', ok === false);
        document.getElementById(msgId).textContent = text;
        refresh();
    }
    function refresh() {
        const agreed = document.getElementById('agree').checked;
        startBtn.disabled = !(camOk && screenOk && agreed);
        document.getElementById('startHint').textContent = startBtn.disabled
            ? 'Allow the camera and screen sharing above, then tick the box.'
            : 'You are ready to begin. Good luck.';
    }
    document.getElementById('agree').addEventListener('change', refresh);

    document.getElementById('grantCam').addEventListener('click', async () => {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            // Released immediately; the runner re-acquires it. Holding it open
            // here would leave the camera light on while the student reads.
            stream.getTracks().forEach(t => t.stop());
            camOk = true;
            paint('permCam', 'permCamMsg', true, 'Allowed');
        } catch (e) {
            camOk = false;
            paint('permCam', 'permCamMsg', false, 'Blocked — allow camera access in your browser, then try again.');
        }
    });

    document.getElementById('grantScreen').addEventListener('click', async () => {
        try {
            const stream = await navigator.mediaDevices.getDisplayMedia({
                video: { displaySurface: 'monitor' }, selfBrowserSurface: 'exclude', monitorTypeSurfaces: 'include',
            });
            // Chrome and Edge say what was shared; anything but the entire screen is refused here
            // so the student finds out now, not when the exam locks. Other browsers report nothing.
            const surface = stream.getVideoTracks()[0]?.getSettings?.().displaySurface;
            stream.getTracks().forEach(t => t.stop());
            if (surface && surface !== 'monitor') {
                screenOk = false;
                paint('permScreen', 'permScreenMsg', false, 'You shared a window or tab. Choose "Entire screen" and try again.');
                return;
            }
            screenOk = true;
            paint('permScreen', 'permScreenMsg', true, 'Shared — you will be asked again when the exam opens.');
        } catch (e) {
            screenOk = false;
            paint('permScreen', 'permScreenMsg', false, 'Not shared — you must share your entire screen to sit this exam.');
        }
    });
})();
</script>
</body>
</html>
