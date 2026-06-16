<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz – {{ $session->subject->name ?? 'CPACE' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:       #7B1D1D;
            --primary-hover: #6a1818;
            --primary-light: #f5e8e8;
            --accent:        #c0392b;
            --green:         #10b981;
            --green-light:   #d1fae5;
            --amber:         #f59e0b;
            --amber-light:   #fef3c7;
            --gray:          #9ca3af;
            --top-h:         62px;
            --bot-h:         68px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        html, body { height:100%; }
        body {
            font-family:'Poppins',sans-serif;
            background:#f0f2f5;
            color:#333;
            display:flex;
            flex-direction:column;
            height:100vh;
            overflow:hidden;
        }

        /* ════════════════ TOP BAR ════════════════ */
        .topbar {
            height: var(--top-h);
            background: var(--primary);
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 16px;
            flex-shrink: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,.25);
            z-index: 20;
        }
        .topbar-logo { width:34px; height:34px; border-radius:8px; overflow:hidden; flex-shrink:0; }
        .topbar-logo img { width:100%; height:100%; object-fit:contain; }
        .topbar-title { flex:1; min-width:0; }
        .topbar-title strong { display:block; font-size:15px; font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .topbar-title span   { font-size:11px; color:rgba(255,255,255,.55); }

        .mode-badge { display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,.14); color:#fff; border-radius:20px; padding:5px 13px; font-size:11px; font-weight:600; flex-shrink:0; }
        .timer-badge { display:inline-flex; align-items:center; gap:7px; background:#fff; border-radius:20px; padding:5px 14px; font-size:13px; font-weight:700; color:#2563eb; flex-shrink:0; }
        .timer-badge.warning { color:#d97706; }
        .timer-badge.danger  { color:var(--accent); animation:blink 1s infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.45} }

        .btn-end { display:inline-flex; align-items:center; gap:7px; background:rgba(255,255,255,.15); border:1.5px solid rgba(255,255,255,.35); color:#fff; border-radius:9px; padding:7px 16px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; transition:background .15s; flex-shrink:0; }
        .btn-end:hover { background:rgba(255,255,255,.25); }

        /* ════════════════ PROGRESS ROW ════════════════ */
        .prog-row {
            background:#fff;
            border-bottom:1px solid #e8e8e8;
            padding:10px 24px;
            display:flex;
            align-items:center;
            gap:14px;
            flex-shrink:0;
        }
        .prog-label { font-size:13px; font-weight:600; color:var(--primary); white-space:nowrap; }
        .prog-track { flex:1; height:8px; background:#f0e6e6; border-radius:8px; overflow:hidden; }
        .prog-fill  { height:100%; background:var(--primary); border-radius:8px; transition:width .35s ease; }
        .prog-stats { display:flex; gap:10px; flex-shrink:0; }
        .stat-chip { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; }
        .stat-chip.answered { background:var(--green-light); color:#059669; }
        .stat-chip.flagged  { background:var(--amber-light);  color:#b45309; }
        .stat-chip.skipped  { background:#f3f4f6;              color:#6b7280; }

        /* ════════════════ MAIN AREA ════════════════ */
        .main-area {
            flex:1;
            display:flex;
            overflow:hidden;
        }

        /* ── Question panel ── */
        .q-panel {
            flex:1;
            overflow-y:auto;
            padding:28px 32px 100px;
            display:flex;
            flex-direction:column;
        }

        .q-header {
            display:flex;
            align-items:center;
            gap:10px;
            margin-bottom:20px;
        }
        .q-num-badge {
            width:38px; height:38px;
            background:var(--primary);
            color:#fff;
            border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:14px; font-weight:700;
            flex-shrink:0;
        }
        .q-type-tag { font-size:10px; font-weight:700; padding:3px 10px; border-radius:5px; background:#f3f4f6; color:#6b7280; }
        .q-flag-indicator { display:none; align-items:center; gap:5px; font-size:11px; font-weight:600; color:var(--amber); margin-left:auto; }
        .q-flag-indicator.visible { display:flex; }

        .q-text {
            font-size:17px;
            font-weight:500;
            color:#1a1a1a;
            line-height:1.65;
            margin-bottom:28px;
            padding:0 4px;
        }

        .choices { display:flex; flex-direction:column; gap:11px; }
        .choice {
            display:flex;
            align-items:center;
            gap:14px;
            padding:15px 18px;
            border:2px solid #e5e7eb;
            border-radius:12px;
            cursor:pointer;
            transition:all .15s;
            background:#fff;
        }
        .choice:hover { border-color:var(--primary); background:var(--primary-light); }
        .choice.selected { border-color:var(--primary); background:var(--primary-light); }
        .choice input[type=radio] { display:none; }
        .choice-letter {
            width:30px; height:30px;
            border-radius:50%;
            background:#f3f4f6;
            border:2px solid #e5e7eb;
            display:flex; align-items:center; justify-content:center;
            font-size:12px; font-weight:700; color:#555;
            flex-shrink:0;
            transition:all .15s;
        }
        .choice.selected .choice-letter { background:var(--primary); color:#fff; border-color:var(--primary); }
        .choice-text { font-size:14.5px; color:#222; line-height:1.45; }

        /* question slide - only one shown at a time */
        .q-slide { display:none; }
        .q-slide.active { display:block; }

        /* ── Navigator sidebar ── */
        .nav-sidebar {
            width:220px;
            flex-shrink:0;
            background:#fff;
            border-left:1px solid #e8e8e8;
            display:flex;
            flex-direction:column;
            overflow:hidden;
        }
        .nav-sidebar-title {
            padding:14px 16px 10px;
            font-size:11px;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:1px;
            color:#888;
            border-bottom:1px solid #f0f0f0;
            flex-shrink:0;
        }
        .nav-grid {
            flex:1;
            overflow-y:auto;
            padding:12px;
            display:flex;
            flex-wrap:wrap;
            align-content:flex-start;
            gap:6px;
        }
        .nav-btn {
            width:36px; height:36px;
            border-radius:8px;
            border:2px solid #e5e7eb;
            background:#f9fafb;
            font-size:12px;
            font-weight:600;
            color:#555;
            cursor:pointer;
            display:flex; align-items:center; justify-content:center;
            position:relative;
            transition:all .15s;
            font-family:'Poppins',sans-serif;
        }
        .nav-btn:hover { border-color:var(--primary); color:var(--primary); }
        .nav-btn.current { background:var(--primary); border-color:var(--primary); color:#fff; }
        .nav-btn.answered { background:var(--green-light); border-color:#6ee7b7; color:#065f46; }
        .nav-btn.flagged  { background:var(--amber-light);  border-color:#fcd34d; color:#92400e; }
        .nav-btn.answered.flagged { background:var(--amber-light); border-color:#fcd34d; color:#92400e; }
        .nav-btn.current.answered { background:var(--primary); border-color:var(--primary); color:#fff; }
        .nav-btn.current.flagged  { background:var(--primary); border-color:var(--primary); color:#fff; }
        .nav-btn .flag-dot {
            position:absolute;
            top:2px; right:2px;
            width:7px; height:7px;
            background:var(--amber);
            border-radius:50%;
            display:none;
        }
        .nav-btn.flagged .flag-dot { display:block; }
        .nav-btn.current .flag-dot { background:rgba(255,255,255,.8); }

        .nav-legend { padding:12px 14px; border-top:1px solid #f0f0f0; flex-shrink:0; }
        .legend-item { display:flex; align-items:center; gap:8px; font-size:11px; color:#666; margin-bottom:6px; }
        .legend-dot { width:12px; height:12px; border-radius:3px; flex-shrink:0; }

        /* ════════════════ BOTTOM BAR ════════════════ */
        .bottombar {
            height: var(--bot-h);
            background:#fff;
            border-top:1px solid #e8e8e8;
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:0 28px;
            flex-shrink:0;
            box-shadow:0 -2px 10px rgba(0,0,0,.05);
            z-index:10;
        }
        .btn {
            display:inline-flex; align-items:center; gap:8px;
            padding:11px 22px;
            border-radius:10px;
            font-size:13px; font-weight:600;
            font-family:'Poppins',sans-serif;
            cursor:pointer; border:none; transition:all .15s;
        }
        .btn-primary { background:var(--primary); color:#fff; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-secondary { background:#f3f4f6; color:#374151; border:1.5px solid #e5e7eb; }
        .btn-secondary:hover { background:#e9eaec; }
        .btn-flag { background:var(--amber-light); color:#92400e; border:1.5px solid #fcd34d; }
        .btn-flag:hover { background:#fde68a; }
        .btn-flag.active { background:var(--amber); color:#fff; border-color:var(--amber); }

        .nav-btns { display:flex; gap:8px; }

        /* ════════════════ CONFIRM MODAL ════════════════ */
        .modal-overlay {
            display:none;
            position:fixed; inset:0;
            background:rgba(0,0,0,.45);
            z-index:100;
            align-items:center; justify-content:center;
        }
        .modal-overlay.open { display:flex; }
        .modal {
            background:#fff;
            border-radius:16px;
            padding:32px 36px;
            max-width:420px;
            width:90%;
            box-shadow:0 20px 60px rgba(0,0,0,.2);
        }
        .modal h3 { font-size:18px; font-weight:700; color:#1a1a1a; margin-bottom:8px; }
        .modal p  { font-size:13px; color:#666; line-height:1.6; margin-bottom:20px; }
        .modal-stats { display:flex; gap:10px; margin-bottom:24px; flex-wrap:wrap; }
        .modal-actions { display:flex; gap:10px; justify-content:flex-end; }

        /* ════════════════ TAB-SWITCH / ANTI-CHEAT OVERLAY ════════════════ */
        .tab-overlay {
            display:none;
            position:fixed; inset:0;
            z-index:9999;
            align-items:center; justify-content:center;
            background:rgba(10,5,5,.88);
            backdrop-filter:blur(10px);
            -webkit-backdrop-filter:blur(10px);
        }
        .tab-overlay.show { display:flex; }

        .tab-overlay-box {
            background:#fff;
            border-radius:20px;
            padding:40px 44px;
            max-width:460px;
            width:90%;
            text-align:center;
            box-shadow:0 24px 80px rgba(0,0,0,.5);
            animation:popIn .2s cubic-bezier(.34,1.56,.64,1);
        }
        @keyframes popIn {
            from { transform:scale(.82); opacity:0; }
            to   { transform:scale(1);   opacity:1; }
        }

        .tab-overlay-icon {
            width:76px; height:76px;
            border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            margin:0 auto 20px;
            font-size:32px;
        }
        .tab-overlay-icon.warn  { background:#fee2e2; color:var(--accent); }
        .tab-overlay-icon.crit  { background:#7f1d1d; color:#fff; }

        .tab-overlay-box h2 { font-size:21px; font-weight:700; color:#1a1a1a; margin-bottom:8px; }
        .tab-overlay-box p  { font-size:13px; color:#555; line-height:1.7; margin-bottom:18px; }

        .violation-bar {
            display:flex; align-items:center; justify-content:center; gap:8px;
            margin-bottom:22px;
        }
        .v-dot {
            width:14px; height:14px; border-radius:50%;
            background:#e5e7eb; border:2px solid #d1d5db;
            transition:all .3s;
        }
        .v-dot.used  { background:var(--amber);  border-color:var(--amber); }
        .v-dot.final { background:var(--accent);  border-color:var(--accent); }

        .violation-label {
            font-size:12px; font-weight:700; color:#92400e;
            background:#fef3c7; border-radius:20px;
            padding:5px 14px; display:inline-block; margin-bottom:22px;
        }
        .violation-label.critical { background:#fee2e2; color:var(--accent); }

        .tab-overlay-actions { display:flex; gap:10px; justify-content:center; flex-wrap:wrap; }

        .btn-resume {
            display:inline-flex; align-items:center; gap:8px;
            background:var(--primary); color:#fff;
            border:none; border-radius:10px;
            padding:12px 30px;
            font-size:14px; font-weight:700;
            font-family:'Poppins',sans-serif;
            cursor:pointer; transition:background .15s;
        }
        .btn-resume:hover { background:var(--primary-hover); }

        .btn-leave {
            display:inline-flex; align-items:center; gap:8px;
            background:#fff; color:#888;
            border:1.5px solid #e5e7eb; border-radius:10px;
            padding:12px 20px;
            font-size:12px; font-weight:600;
            font-family:'Poppins',sans-serif;
            cursor:pointer; transition:all .15s;
        }
        .btn-leave:hover { border-color:#fca5a5; color:var(--accent); background:#fef2f2; }

        /* Freeze the quiz content underneath */
        .quiz-frozen .main-area,
        .quiz-frozen .prog-row,
        .quiz-frozen .topbar,
        .quiz-frozen .bottombar {
            filter:blur(4px) brightness(.6);
            pointer-events:none;
            user-select:none;
        }
    </style>
</head>
<body>

@php
    $modeLabels = [
        'adaptive'  => ['Adaptive Mode',   'fa-chart-line'],
        'topic'     => ['Topic Focus',      'fa-book-open'],
        'timed'     => ['Timed Mode',       'fa-clock'],
        'challenge' => ['Challenge Mode',   'fa-trophy'],
    ];
    [$modeName, $modeIcon] = $modeLabels[$session->mode] ?? $modeLabels['adaptive'];
    $total = $questions->count();
@endphp

<!-- ══════════ TOP BAR ══════════ -->
<div class="topbar">
    <div class="topbar-logo">
        <img src="{{ asset('images/cpace_logo.png') }}" alt="CPACE">
    </div>
    <div class="topbar-title">
        <strong>{{ $session->subject->name ?? 'Adaptive Quiz' }}</strong>
        <span>{{ $modeName }}</span>
    </div>
    <span class="mode-badge"><i class="fas {{ $modeIcon }}"></i> {{ $modeName }}</span>
    @if(!is_null($timeLimit))
        <span class="timer-badge" id="timerBadge"><i class="fas fa-clock"></i> <span id="timer">--:--</span></span>
    @endif
    <button class="btn-end" id="endQuizBtn"><i class="fas fa-flag-checkered"></i> End Quiz</button>
</div>

<!-- ══════════ PROGRESS ROW ══════════ -->
<div class="prog-row">
    <span class="prog-label" id="progLabel">Question 1 of {{ $total }}</span>
    <div class="prog-track"><div class="prog-fill" id="progFill" style="width:{{ $total > 0 ? round(1/$total*100) : 0 }}%"></div></div>
    <div class="prog-stats">
        <span class="stat-chip answered"><i class="fas fa-check-circle"></i> <span id="statAnswered">0</span> Answered</span>
        <span class="stat-chip flagged"><i class="fas fa-flag"></i> <span id="statFlagged">0</span> For Review</span>
        <span class="stat-chip skipped"><i class="fas fa-minus-circle"></i> <span id="statSkipped">{{ $total }}</span> Remaining</span>
    </div>
</div>

<!-- ══════════ MAIN ══════════ -->
<div class="main-area">

    <!-- QUESTION PANEL -->
    <div class="q-panel" id="qPanel">
        <form method="POST" action="{{ route('quiz.submit', $session->id) }}" id="quizForm">
            @csrf
            @php $typeLabel = ['mcq' => 'Multiple Choice', 'true_false' => 'True / False']; @endphp

            @foreach($questions as $i => $question)
            <div class="q-slide{{ $i === 0 ? ' active' : '' }}" id="slide-{{ $i }}" data-index="{{ $i }}" data-qid="{{ $question->id }}">

                <div class="q-header">
                    <div class="q-num-badge">{{ $i + 1 }}</div>
                    <span class="q-type-tag">{{ $typeLabel[$question->question_type] ?? $question->question_type }}</span>
                    <div class="q-flag-indicator" id="flagIndicator-{{ $i }}">
                        <i class="fas fa-flag"></i> Marked for Review
                    </div>
                </div>

                <div class="q-text">{{ $question->question_text }}</div>

                <div class="choices">
                    @foreach($question->choices as $choice)
                    <label class="choice" id="choice-{{ $question->id }}-{{ $choice->id }}">
                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $choice->id }}">
                        <div class="choice-letter">{{ $choice->choice_label }}</div>
                        <span class="choice-text">{{ $choice->choice_text }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </form>
    </div>

    <!-- NAVIGATOR SIDEBAR -->
    <div class="nav-sidebar">
        <div class="nav-sidebar-title">Question Navigator</div>
        <div class="nav-grid" id="navGrid">
            @foreach($questions as $i => $q)
            <button type="button" class="nav-btn{{ $i === 0 ? ' current' : '' }}" id="nav-{{ $i }}" onclick="goTo({{ $i }})">
                {{ $i + 1 }}
                <span class="flag-dot"></span>
            </button>
            @endforeach
        </div>
        <div class="nav-legend">
            <div class="legend-item"><div class="legend-dot" style="background:var(--primary)"></div> Current</div>
            <div class="legend-item"><div class="legend-dot" style="background:#6ee7b7;border:1.5px solid #6ee7b7"></div> Answered</div>
            <div class="legend-item"><div class="legend-dot" style="background:#fcd34d"></div> For Review</div>
            <div class="legend-item"><div class="legend-dot" style="background:#f3f4f6;border:1.5px solid #e5e7eb"></div> Unanswered</div>
        </div>
    </div>
</div>

<!-- ══════════ BOTTOM BAR ══════════ -->
<div class="bottombar">
    <button type="button" class="btn btn-flag" id="flagBtn" onclick="toggleFlag()">
        <i class="fas fa-flag"></i> Mark for Review
    </button>
    <div class="nav-btns">
        <button type="button" class="btn btn-secondary" id="prevBtn" onclick="prev()" disabled>
            <i class="fas fa-chevron-left"></i> Previous
        </button>
        <button type="button" class="btn btn-primary" id="nextBtn" onclick="next()">
            Next <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<!-- ══════════ ANTI-CHEAT OVERLAY ══════════ -->
<div class="tab-overlay" id="tabOverlay">
    <div class="tab-overlay-box">
        <div class="tab-overlay-icon warn" id="overlayIcon">
            <i class="fas fa-shield-alt"></i>
        </div>
        <h2 id="overlayTitle">Quiz Frozen</h2>
        <p id="overlayMsg">
            You left the quiz tab. Your quiz has been <strong>paused</strong> and is locked
            until you return. The timer has also stopped.
        </p>

        <!-- Violation dots (max 3) -->
        <div class="violation-bar">
            <div class="v-dot" id="vdot0"></div>
            <div class="v-dot" id="vdot1"></div>
            <div class="v-dot" id="vdot2"></div>
        </div>
        <div class="violation-label" id="violationLabel">
            Violation <span id="vCount">1</span> of 3 — Resume to continue
        </div>

        <div class="tab-overlay-actions">
            <button class="btn-resume" id="resumeBtn" onclick="resumeQuiz()">
                <i class="fas fa-play"></i> Resume Quiz
            </button>
            <button class="btn-leave" onclick="confirmLeave()">
                <i class="fas fa-sign-out-alt"></i> Leave &amp; End Quiz
            </button>
        </div>
    </div>
</div>

<!-- ══════════ END QUIZ MODAL ══════════ -->
<div class="modal-overlay" id="endModal">
    <div class="modal">
        <h3><i class="fas fa-flag-checkered" style="color:var(--primary);margin-right:8px;"></i>Submit Quiz?</h3>
        <p>Please review your progress before submitting. You won't be able to change answers after submission.</p>
        <div class="modal-stats">
            <span class="stat-chip answered"><i class="fas fa-check-circle"></i> <span id="mAnswered">0</span> Answered</span>
            <span class="stat-chip flagged"><i class="fas fa-flag"></i> <span id="mFlagged">0</span> For Review</span>
            <span class="stat-chip skipped"><i class="fas fa-minus-circle"></i> <span id="mSkipped">0</span> Remaining</span>
        </div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeModal()">Go Back</button>
            <button class="btn btn-primary" onclick="submitQuiz()"><i class="fas fa-check"></i> Submit Now</button>
        </div>
    </div>
</div>

<script>
const TOTAL      = {{ $total }};
let current      = 0;
const flagged    = new Set();
const answered   = new Set();      // indices
const answerMap  = {};             // qid → choiceId  (for restoring selections)

// Map index → { qid, choices[] }
const qData = [
    @foreach($questions as $i => $q)
    { index: {{ $i }}, qid: {{ $q->id }} },
    @endforeach
];

/* ── Navigate to a specific question ── */
function goTo(idx) {
    if (idx < 0 || idx >= TOTAL) return;

    // Deactivate current
    document.getElementById('slide-' + current)?.classList.remove('active');
    document.getElementById('nav-' + current)?.classList.remove('current');

    current = idx;

    // Activate new
    document.getElementById('slide-' + current)?.classList.add('active');
    const navBtn = document.getElementById('nav-' + current);
    if (navBtn) navBtn.classList.add('current');

    // Scroll question panel to top
    document.getElementById('qPanel').scrollTop = 0;

    // Update progress bar & label
    document.getElementById('progLabel').textContent = `Question ${current + 1} of ${TOTAL}`;
    document.getElementById('progFill').style.width  = ((current + 1) / TOTAL * 100) + '%';

    // Prev / Next buttons
    document.getElementById('prevBtn').disabled = (current === 0);
    document.getElementById('nextBtn').innerHTML = current === TOTAL - 1
        ? '<i class="fas fa-flag-checkered"></i> Finish'
        : 'Next <i class="fas fa-chevron-right"></i>';

    // Flag button state
    const fb = document.getElementById('flagBtn');
    if (flagged.has(current)) {
        fb.classList.add('active');
        fb.innerHTML = '<i class="fas fa-flag"></i> Unmark Review';
    } else {
        fb.classList.remove('active');
        fb.innerHTML = '<i class="fas fa-flag"></i> Mark for Review';
    }

    // Flag indicator on question
    const fi = document.getElementById('flagIndicator-' + current);
    if (fi) fi.classList.toggle('visible', flagged.has(current));
}

function prev() { goTo(current - 1); }
function next() {
    if (current === TOTAL - 1) { openModal(); return; }
    goTo(current + 1);
}

/* ── Mark / unmark for review ── */
function toggleFlag() {
    if (flagged.has(current)) {
        flagged.delete(current);
    } else {
        flagged.add(current);
    }
    refreshNav();
    goTo(current); // refresh button state & indicator
}

/* ── Listen for answer selection ── */
document.getElementById('quizForm').addEventListener('change', function (e) {
    if (e.target.type !== 'radio') return;

    // Deselect all choices in this question visually
    const slide = document.getElementById('slide-' + current);
    slide.querySelectorAll('.choice').forEach(c => c.classList.remove('selected'));
    e.target.closest('.choice').classList.add('selected');

    // Track answered state
    const idx = current;
    answered.add(idx);
    refreshNav();
    refreshStats();
});

/* ── Refresh navigator button classes ── */
function refreshNav() {
    for (let i = 0; i < TOTAL; i++) {
        const btn = document.getElementById('nav-' + i);
        if (!btn) continue;
        btn.className = 'nav-btn';
        if (i === current)       btn.classList.add('current');
        if (answered.has(i))     btn.classList.add('answered');
        if (flagged.has(i))      btn.classList.add('flagged');
    }
}

/* ── Refresh stat chips (top + modal) ── */
function refreshStats() {
    const a = answered.size;
    const f = flagged.size;
    const s = TOTAL - a;
    ['statAnswered','mAnswered'].forEach(id => { const el = document.getElementById(id); if(el) el.textContent = a; });
    ['statFlagged', 'mFlagged' ].forEach(id => { const el = document.getElementById(id); if(el) el.textContent = f; });
    ['statSkipped', 'mSkipped' ].forEach(id => { const el = document.getElementById(id); if(el) el.textContent = s; });
}

/* ── End quiz modal ── */
function openModal() {
    refreshStats();
    document.getElementById('endModal').classList.add('open');
}
function closeModal() {
    document.getElementById('endModal').classList.remove('open');
}
document.getElementById('endQuizBtn').addEventListener('click', openModal);
document.getElementById('endModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

let timeUp = false;
function submitQuiz() {
    // Remove the beforeunload guard so the form submit navigates cleanly
    window.onbeforeunload = null;
    document.getElementById('quizForm').submit();
}

/* ── Cancel / close tab ── */
// No explicit cancel button — End Quiz modal replaces it.
// If user closes the tab without submitting, session stays open but can be recovered.

@if(!is_null($timeLimit))
(function () {
    let remaining = {{ max(0, $timeLimit - (int) $session->started_at->diffInSeconds(now())) }};
    const badge = document.getElementById('timerBadge');
    const label = document.getElementById('timer');

    function render() {
        const m = Math.floor(remaining / 60), s = remaining % 60;
        label.textContent = `${m}:${s.toString().padStart(2, '0')}`;
        badge.classList.toggle('warning', remaining <= 60 && remaining > 20);
        badge.classList.toggle('danger',  remaining <= 20);
    }

    function tick() {
        if (remaining <= 0) {
            timeUp = true;
            label.textContent = '0:00';
            window.onbeforeunload = null;
            document.getElementById('quizForm').submit();
            return;
        }
        render();
        remaining--;
        setTimeout(tick, 1000);
    }
    tick();
})();
@endif

// Init
refreshStats();

/* ════════════════════════════════════════════════
   ANTI-CHEAT: TAB / WINDOW VISIBILITY
   ════════════════════════════════════════════════
   Rules:
   • Switching away freezes the quiz and pauses the timer.
   • On return, the overlay must be manually dismissed.
   • 3rd violation → quiz is auto-submitted immediately.
   • "Leave & End Quiz" cancels the session and closes tab.
   ════════════════════════════════════════════════ */

const MAX_VIOLATIONS  = 3;
let   violations      = 0;
let   frozen          = false;
let   timerRunning    = true;   // controls the countdown tick

const tabOverlay      = document.getElementById('tabOverlay');

/* ── Freeze / unfreeze ── */
function freeze() {
    if (frozen) return;
    frozen = true;
    timerRunning = false;
    document.body.classList.add('quiz-frozen');
}

function unfreeze() {
    frozen = false;
    timerRunning = true;
    document.body.classList.remove('quiz-frozen');
    tabOverlay.classList.remove('show');
}

/* ── Show the overlay with up-to-date state ── */
function showViolationOverlay() {
    violations++;

    // Update violation dots
    for (let i = 0; i < 3; i++) {
        const dot = document.getElementById('vdot' + i);
        if (!dot) continue;
        dot.classList.remove('used', 'final');
        if (i < violations) {
            dot.classList.add(i === MAX_VIOLATIONS - 1 ? 'final' : 'used');
        }
    }

    document.getElementById('vCount').textContent = violations;

    const label = document.getElementById('violationLabel');
    const icon  = document.getElementById('overlayIcon');
    const title = document.getElementById('overlayTitle');
    const msg   = document.getElementById('overlayMsg');

    if (violations >= MAX_VIOLATIONS) {
        // Final violation — auto-submit, no resume option
        icon.className  = 'tab-overlay-icon crit';
        icon.innerHTML  = '<i class="fas fa-ban"></i>';
        title.textContent = 'Quiz Terminated';
        msg.innerHTML   = 'You have left the quiz tab <strong>' + violations + ' times</strong>. Your quiz is being <strong>auto-submitted</strong> now.';
        label.className = 'violation-label critical';
        label.textContent = 'Maximum violations reached';
        document.getElementById('resumeBtn').style.display = 'none';
        tabOverlay.classList.add('show');

        // Auto-submit after a short pause so the student sees the message
        setTimeout(function () {
            window.onbeforeunload = null;
            document.getElementById('quizForm').submit();
        }, 3000);
        return;
    }

    // Warning overlay (violations 1 or 2)
    icon.className  = 'tab-overlay-icon warn';
    icon.innerHTML  = '<i class="fas fa-shield-alt"></i>';
    title.textContent = 'Quiz Frozen';

    const remaining = MAX_VIOLATIONS - violations;
    msg.innerHTML = 'You left the quiz tab. Your quiz is <strong>paused and locked</strong>.<br>'
        + '<span style="color:var(--accent);font-weight:600;">⚠ ' + remaining + ' more violation' + (remaining > 1 ? 's' : '') + ' will auto-submit your quiz.</span>';

    label.className = 'violation-label';
    label.innerHTML = 'Violation <span id="vCount">' + violations + '</span> of ' + MAX_VIOLATIONS + ' — resume to continue';

    document.getElementById('resumeBtn').style.display = '';
    tabOverlay.classList.add('show');
}

/* ── Resume button ── */
function resumeQuiz() {
    unfreeze();
}

/* ── Leave & End Quiz ── */
function confirmLeave() {
    if (!confirm('Leave the quiz? Your current progress will be submitted as-is.')) return;
    window.onbeforeunload = null;
    document.getElementById('quizForm').submit();
}

/* ── Page Visibility API (tab switch) ── */
document.addEventListener('visibilitychange', function () {
    if (document.hidden) {
        freeze();
    } else {
        // Returned — show violation overlay (freeze stays until dismissed)
        showViolationOverlay();
    }
});

/* ── Window blur (alt-tab, opening DevTools, clicking outside) ── */
let blurTimer = null;
window.addEventListener('blur', function () {
    // Debounce — ignore accidental micro-blurs (< 300ms)
    blurTimer = setTimeout(function () {
        if (!document.hidden) {   // not already handled by visibilitychange
            freeze();
            showViolationOverlay();
        }
    }, 300);
});
window.addEventListener('focus', function () {
    clearTimeout(blurTimer);
});

/* ── Prevent right-click (minor deterrent) ── */
document.addEventListener('contextmenu', function (e) {
    e.preventDefault();
});

/* ── Prevent common keyboard shortcuts ── */
document.addEventListener('keydown', function (e) {
    // Block Ctrl/Cmd + T (new tab), Ctrl/Cmd + W (close), F12 (devtools)
    const ctrl = e.ctrlKey || e.metaKey;
    if ((ctrl && (e.key === 't' || e.key === 'w' || e.key === 'n')) || e.key === 'F12') {
        e.preventDefault();
    }
});

/* ── Warn on tab close / refresh ── */
window.addEventListener('beforeunload', function (e) {
    e.preventDefault();
    e.returnValue = 'Your quiz is still in progress. Leaving will submit your current answers.';
    return e.returnValue;
});
</script>
</body>
</html>
