<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
        }
        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
        html, body { height:100%; overflow:hidden; }
        body {
            font-family:'Poppins',sans-serif;
            background:#1a0a0a;
            color:#1a1a1a;
            display:flex;
            justify-content:center;
            align-items:stretch;
        }

        /* ── Phone shell (centered on desktop, full on mobile) ── */
        .phone {
            width:100%;
            max-width:480px;
            background:#f5f5f7;
            display:flex;
            flex-direction:column;
            height:100%;
            position:relative;
            overflow:hidden;
        }

        /* ══════════ HEADER ══════════ */
        .hdr {
            background:var(--primary);
            padding:14px 18px 0;
            flex-shrink:0;
        }
        .hdr-row {
            display:flex;
            align-items:center;
            gap:10px;
            padding-bottom:12px;
        }
        .hdr-back {
            width:36px; height:36px;
            background:rgba(255,255,255,.15);
            border:none; border-radius:10px;
            color:#fff; font-size:14px;
            display:flex; align-items:center; justify-content:center;
            cursor:pointer; flex-shrink:0;
        }
        .hdr-info { flex:1; min-width:0; }
        .hdr-subject { font-size:14px; font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .hdr-mode    { font-size:10px; color:rgba(255,255,255,.6); margin-top:1px; }
        .hdr-timer {
            display:flex; align-items:center; gap:5px;
            background:rgba(255,255,255,.18);
            border-radius:20px; padding:6px 12px;
            font-size:13px; font-weight:700; color:#fff;
            flex-shrink:0;
        }
        .hdr-timer.warning { background:#d97706; }
        .hdr-timer.danger  { background:var(--accent); animation:blink 1s infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.4} }

        /* Progress bar strip */
        .hdr-progress {
            height:4px;
            background:rgba(255,255,255,.2);
            border-radius:0;
            overflow:hidden;
        }
        .hdr-progress-fill {
            height:100%;
            background:#fff;
            border-radius:0 4px 4px 0;
            transition:width .4s ease;
        }

        /* ══════════ QUESTION COUNTER STRIP ══════════ */
        .q-counter-strip {
            background:#fff;
            padding:10px 18px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            border-bottom:1px solid #ebebeb;
            flex-shrink:0;
        }
        .q-counter-num {
            font-size:13px; font-weight:700; color:var(--primary);
        }
        .q-counter-stats {
            display:flex; gap:8px;
        }
        .q-chip {
            display:inline-flex; align-items:center; gap:4px;
            font-size:10px; font-weight:600;
            padding:3px 9px; border-radius:20px;
        }
        .q-chip.ans  { background:var(--green-light); color:#065f46; }
        .q-chip.flag { background:var(--amber-light);  color:#92400e; }
        .q-chip.left { background:#f3f4f6;              color:#6b7280; }

        /* ══════════ SCROLLABLE BODY ══════════ */
        .q-body {
            flex:1;
            overflow-y:auto;
            -webkit-overflow-scrolling:touch;
            padding:20px 18px 24px;
        }

        /* Flag banner */
        .flag-banner {
            display:none;
            align-items:center;
            gap:8px;
            background:var(--amber-light);
            border:1.5px solid #fcd34d;
            border-radius:10px;
            padding:8px 14px;
            font-size:12px;
            font-weight:600;
            color:#92400e;
            margin-bottom:14px;
        }
        .flag-banner.visible { display:flex; }

        /* Question text card */
        .q-card {
            background:#fff;
            border-radius:16px;
            padding:20px 18px;
            margin-bottom:14px;
            box-shadow:0 1px 6px rgba(0,0,0,.07);
        }
        .q-card-label {
            font-size:10px; font-weight:700;
            color:var(--gray);
            text-transform:uppercase;
            letter-spacing:.8px;
            margin-bottom:10px;
        }
        .q-text {
            font-size:16px;
            font-weight:500;
            color:#111;
            line-height:1.65;
        }

        /* Choices */
        .choices { display:flex; flex-direction:column; gap:10px; }

        .choice {
            display:flex;
            align-items:center;
            gap:14px;
            padding:16px 16px;
            background:#fff;
            border:2px solid #e5e7eb;
            border-radius:14px;
            cursor:pointer;
            transition:border-color .15s, background .15s, transform .08s;
            -webkit-user-select:none; user-select:none;
        }
        .choice:active { transform:scale(.98); }
        .choice.selected {
            border-color:var(--primary);
            background:var(--primary-light);
        }
        .choice input[type=radio] { display:none; }

        .choice-letter {
            width:34px; height:34px;
            border-radius:50%;
            background:#f3f4f6;
            border:2px solid #e5e7eb;
            display:flex; align-items:center; justify-content:center;
            font-size:13px; font-weight:700; color:#555;
            flex-shrink:0;
            transition:background .15s, border-color .15s, color .15s;
        }
        .choice.selected .choice-letter {
            background:var(--primary);
            border-color:var(--primary);
            color:#fff;
        }
        .choice-text {
            font-size:14px; color:#222; line-height:1.5;
            flex:1;
        }
        .choice-check {
            width:20px; height:20px;
            border-radius:50%;
            background:var(--primary);
            display:none;
            align-items:center; justify-content:center;
            color:#fff; font-size:10px;
            flex-shrink:0;
        }
        .choice.selected .choice-check { display:flex; }

        /* slide visibility */
        .q-slide { display:none; }
        .q-slide.active { display:block; }

        /* ── Training mode: per-question feedback ── */
        .choice.reveal-correct {
            border-color: #10b981 !important;
            background: #ecfdf5 !important;
            pointer-events: none;
        }
        .choice.reveal-correct .choice-letter {
            background: #10b981 !important;
            border-color: #10b981 !important;
            color: #fff !important;
        }
        .choice.reveal-wrong {
            border-color: #ef4444 !important;
            background: #fef2f2 !important;
            pointer-events: none;
        }
        .choice.reveal-wrong .choice-letter {
            background: #ef4444 !important;
            border-color: #ef4444 !important;
            color: #fff !important;
        }
        /* Lock all choices once revealed */
        .q-slide.revealed .choice { pointer-events: none; }

        .feedback-panel {
            display: none;
            margin-top: 14px;
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.55;
        }
        .feedback-panel.correct { background: #ecfdf5; color: #065f46; border: 1.5px solid #6ee7b7; }
        .feedback-panel.wrong   { background: #fef2f2; color: #991b1b; border: 1.5px solid #fca5a5; }
        .feedback-panel.visible { display: block; }
        .feedback-panel .explain-text { margin-top: 8px; font-weight: 400; color: #555; font-size: 12px; }

        /* Training badge on header */
        .training-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: #d1fae5; color: #065f46;
            border-radius: 20px; padding: 3px 10px;
            font-size: 10px; font-weight: 700;
        }

        /* ══════════ BOTTOM NAV ══════════ */
        .bottom-nav {
            background:#fff;
            border-top:1px solid #e8e8e8;
            padding:10px 16px 14px;
            flex-shrink:0;
            box-shadow:0 -4px 16px rgba(0,0,0,.07);
        }
        .bottom-nav-row {
            display:flex;
            align-items:center;
            gap:8px;
        }
        .bn-btn {
            display:flex; align-items:center; justify-content:center; gap:6px;
            border:none; border-radius:12px;
            font-family:'Poppins',sans-serif;
            font-size:13px; font-weight:600;
            cursor:pointer;
            transition:all .15s;
            height:48px;
            padding:0 14px;
        }
        .bn-flag {
            background:var(--amber-light);
            color:#92400e;
            border:1.5px solid #fcd34d;
            width:48px; padding:0;
            flex-shrink:0;
        }
        .bn-flag.active { background:var(--amber); color:#fff; border-color:var(--amber); }
        .bn-prev {
            background:#f3f4f6;
            color:#374151;
            border:1.5px solid #e5e7eb;
            flex:1;
        }
        .bn-prev:disabled { opacity:.4; cursor:not-allowed; }
        .bn-next {
            background:var(--primary);
            color:#fff;
            flex:2;
        }
        .bn-next:hover { background:var(--primary-hover); }
        .bn-grid {
            background:#f3f4f6;
            color:#374151;
            border:1.5px solid #e5e7eb;
            width:48px; padding:0;
            flex-shrink:0;
            position:relative;
        }
        .bn-grid-badge {
            position:absolute;
            top:-5px; right:-5px;
            width:16px; height:16px;
            background:var(--accent);
            color:#fff;
            border-radius:50%;
            font-size:9px; font-weight:700;
            display:flex; align-items:center; justify-content:center;
        }

        /* ══════════ QUESTION NAVIGATOR BOTTOM SHEET ══════════ */
        .sheet-overlay {
            display:none;
            position:fixed; inset:0;
            background:rgba(0,0,0,.5);
            z-index:50;
        }
        .sheet-overlay.open { display:block; }

        .nav-sheet {
            position:fixed;
            bottom:0; left:50%; transform:translateX(-50%) translateY(100%);
            width:100%; max-width:480px;
            background:#fff;
            border-radius:20px 20px 0 0;
            z-index:51;
            transition:transform .3s cubic-bezier(.32,0,.67,0);
            max-height:80vh;
            display:flex; flex-direction:column;
        }
        .nav-sheet.open { transform:translateX(-50%) translateY(0); }

        .sheet-handle {
            width:40px; height:4px;
            background:#e5e7eb;
            border-radius:2px;
            margin:12px auto 0;
            flex-shrink:0;
        }
        .sheet-title {
            padding:14px 18px 10px;
            font-size:14px; font-weight:700; color:#1a1a1a;
            border-bottom:1px solid #f0f0f0;
            flex-shrink:0;
            display:flex; align-items:center; justify-content:space-between;
        }
        .sheet-close {
            width:28px; height:28px;
            background:#f3f4f6;
            border:none; border-radius:50%;
            font-size:13px; color:#555;
            cursor:pointer;
            display:flex; align-items:center; justify-content:center;
        }

        .sheet-legend {
            display:flex; gap:12px; flex-wrap:wrap;
            padding:10px 18px;
            border-bottom:1px solid #f0f0f0;
            flex-shrink:0;
        }
        .leg-item { display:flex; align-items:center; gap:5px; font-size:10px; color:#666; }
        .leg-dot { width:10px; height:10px; border-radius:3px; flex-shrink:0; }

        .sheet-grid {
            flex:1; overflow-y:auto;
            padding:14px 18px 24px;
            display:flex; flex-wrap:wrap;
            align-content:flex-start;
            gap:8px;
        }
        .nav-btn {
            width:44px; height:44px;
            border-radius:10px;
            border:2px solid #e5e7eb;
            background:#f9fafb;
            font-size:13px; font-weight:600; color:#555;
            cursor:pointer;
            display:flex; align-items:center; justify-content:center;
            position:relative;
            transition:all .15s;
            font-family:'Poppins',sans-serif;
        }
        .nav-btn.current  { background:var(--primary); border-color:var(--primary); color:#fff; }
        .nav-btn.answered { background:var(--green-light); border-color:#6ee7b7; color:#065f46; }
        .nav-btn.flagged  { background:var(--amber-light); border-color:#fcd34d; color:#92400e; }
        .nav-btn.current.answered, .nav-btn.current.flagged { background:var(--primary); border-color:var(--primary); color:#fff; }
        .nav-btn .flag-dot {
            position:absolute; top:2px; right:2px;
            width:7px; height:7px;
            background:var(--amber); border-radius:50%; display:none;
        }
        .nav-btn.flagged .flag-dot { display:block; }
        .nav-btn.current .flag-dot { background:rgba(255,255,255,.85); }

        /* ══════════ END QUIZ MODAL ══════════ */
        .modal-overlay {
            display:none; position:fixed; inset:0;
            background:rgba(0,0,0,.5);
            z-index:100; align-items:flex-end; justify-content:center;
        }
        .modal-overlay.open { display:flex; }
        .modal {
            background:#fff;
            border-radius:20px 20px 0 0;
            padding:28px 22px 36px;
            width:100%; max-width:480px;
            box-shadow:0 -8px 40px rgba(0,0,0,.2);
            animation:slideUp .25s ease;
        }
        @keyframes slideUp { from{transform:translateY(100%)} to{transform:translateY(0)} }
        .modal-handle { width:36px; height:4px; background:#e5e7eb; border-radius:2px; margin:0 auto 20px; }
        .modal h3 { font-size:17px; font-weight:700; color:#1a1a1a; margin-bottom:6px; }
        .modal p  { font-size:13px; color:#666; line-height:1.6; margin-bottom:16px; }
        .modal-chips { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:22px; }
        .modal-actions { display:flex; flex-direction:column; gap:10px; }
        .modal-btn {
            width:100%; padding:15px;
            border-radius:14px; border:none;
            font-size:14px; font-weight:700;
            font-family:'Poppins',sans-serif;
            cursor:pointer;
        }
        .modal-btn.primary { background:var(--primary); color:#fff; }
        .modal-btn.ghost   { background:#f3f4f6; color:#374151; }

        /* ══════════ ANTI-CHEAT OVERLAY ══════════ */
        .tab-overlay {
            display:none; position:fixed; inset:0;
            z-index:9999;
            align-items:center; justify-content:center;
            background:rgba(10,5,5,.92);
            backdrop-filter:blur(12px);
            -webkit-backdrop-filter:blur(12px);
        }
        .tab-overlay.show { display:flex; }
        .tab-overlay-box {
            background:#fff; border-radius:20px;
            padding:36px 28px;
            width:90%; max-width:360px;
            text-align:center;
            box-shadow:0 24px 80px rgba(0,0,0,.5);
            animation:popIn .22s cubic-bezier(.34,1.56,.64,1);
        }
        @keyframes popIn { from{transform:scale(.82);opacity:0} to{transform:scale(1);opacity:1} }
        .tab-overlay-icon {
            width:70px; height:70px; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            margin:0 auto 16px; font-size:28px;
        }
        .tab-overlay-icon.warn { background:#fee2e2; color:var(--accent); }
        .tab-overlay-icon.crit { background:#7f1d1d; color:#fff; }
        .tab-overlay-box h2 { font-size:19px; font-weight:700; color:#1a1a1a; margin-bottom:6px; }
        .tab-overlay-box p  { font-size:12px; color:#555; line-height:1.65; margin-bottom:16px; }
        .violation-bar { display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:14px; }
        .v-dot { width:14px; height:14px; border-radius:50%; background:#e5e7eb; border:2px solid #d1d5db; transition:all .3s; }
        .v-dot.used  { background:var(--amber);  border-color:var(--amber); }
        .v-dot.final { background:var(--accent);  border-color:var(--accent); }
        .violation-label { font-size:11px; font-weight:700; border-radius:20px; padding:4px 12px; display:inline-block; margin-bottom:18px; }
        .violation-label.warn { background:#fef3c7; color:#92400e; }
        .violation-label.critical { background:#fee2e2; color:var(--accent); }
        .tab-overlay-actions { display:flex; flex-direction:column; gap:8px; }
        .btn-resume {
            display:flex; align-items:center; justify-content:center; gap:8px;
            background:var(--primary); color:#fff;
            border:none; border-radius:12px;
            padding:14px; font-size:14px; font-weight:700;
            font-family:'Poppins',sans-serif; cursor:pointer;
        }
        .btn-leave {
            display:flex; align-items:center; justify-content:center; gap:8px;
            background:#fff; color:#999;
            border:1.5px solid #e5e7eb; border-radius:12px;
            padding:12px; font-size:12px; font-weight:600;
            font-family:'Poppins',sans-serif; cursor:pointer;
        }
        .btn-leave:hover { border-color:#fca5a5; color:var(--accent); }

        /* Freeze blur */
        .quiz-frozen .phone > *:not(.tab-overlay) {
            filter:blur(5px) brightness(.55);
            pointer-events:none; user-select:none;
        }
    </style>
</head>
<body>

@php
    $modeLabels = [
        'adaptive'  => ['Adaptive',     'fa-chart-line'],
        'topic'     => ['Topic Focus',  'fa-book-open'],
        'timed'     => ['Timed',        'fa-clock'],
        'challenge' => ['Challenge',    'fa-trophy'],
    ];
    [$modeName, $modeIcon] = $modeLabels[$session->mode] ?? $modeLabels['adaptive'];
    $total = $questions->count();
    $typeLabel = ['mcq' => 'Multiple Choice', 'true_false' => 'True / False'];
@endphp

<div class="phone">

    <!-- ══════ HEADER ══════ -->
    <div class="hdr">
        <div class="hdr-row">
            <button class="hdr-back" id="endQuizBtn" title="End Quiz">
                <i class="fas fa-times"></i>
            </button>
            <div class="hdr-info">
                <div class="hdr-subject">{{ $session->subject->name ?? 'Quiz' }}</div>
                <div class="hdr-mode">
                    <i class="fas {{ $modeIcon }}"></i> {{ $modeName }}
                    @if($session->session_type === 'training')
                        &nbsp;<span class="training-badge"><i class="fas fa-brain"></i> Training</span>
                    @endif
                </div>
            </div>
            @isset($timeLimit)
                <div class="hdr-timer" id="timerBadge">
                    <i class="fas fa-clock"></i><span id="timer">--:--</span>
                </div>
            @endisset
        </div>
        <div class="hdr-progress">
            <div class="hdr-progress-fill" id="progFill" style="width:{{ $total > 0 ? round(1/$total*100,1) : 0 }}%"></div>
        </div>
    </div>

    <!-- ══════ COUNTER STRIP ══════ -->
    <div class="q-counter-strip">
        <span class="q-counter-num" id="qCounterNum">Question 1 of {{ $total }}</span>
        <div class="q-counter-stats">
            <span class="q-chip ans"><i class="fas fa-check"></i><span id="statAnswered">0</span></span>
            <span class="q-chip flag"><i class="fas fa-flag"></i><span id="statFlagged">0</span></span>
            <span class="q-chip left"><i class="fas fa-minus"></i><span id="statLeft">{{ $total }}</span></span>
        </div>
    </div>

    <!-- ══════ QUESTION BODY ══════ -->
    <div class="q-body" id="qBody">
        <form method="POST" action="{{ route('quiz.submit', $session->id) }}" id="quizForm">
            @csrf
            @foreach($questions as $i => $question)
            <div class="q-slide{{ $i === 0 ? ' active' : '' }}" id="slide-{{ $i }}">

                <!-- Flag banner -->
                <div class="flag-banner" id="flagBanner-{{ $i }}">
                    <i class="fas fa-flag"></i> Marked for Review
                </div>

                <!-- Question -->
                <div class="q-card">
                    <div class="q-card-label">{{ $typeLabel[$question->question_type] ?? 'Question' }}</div>
                    <div class="q-text">{{ $question->question_text }}</div>
                </div>

                <!-- Choices -->
                <div class="choices">
                    @foreach($question->choices as $choice)
                    <label class="choice" @if($choice->is_correct) data-correct="1" @endif>
                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $choice->id }}">
                        <div class="choice-letter">{{ $choice->choice_label }}</div>
                        <span class="choice-text">{{ $choice->choice_text }}</span>
                        <span class="choice-check"><i class="fas fa-check"></i></span>
                    </label>
                    @endforeach
                </div>

                <!-- Training mode feedback (hidden until answer selected) -->
                <div class="feedback-panel" id="feedback-{{ $i }}"
                     data-explanation="{{ $question->explanation ?? '' }}">
                </div>

            </div>
            @endforeach
        </form>
    </div>

    <!-- ══════ BOTTOM NAV ══════ -->
    <div class="bottom-nav">
        <div class="bottom-nav-row">
            <!-- Flag -->
            <button type="button" class="bn-btn bn-flag" id="flagBtn" onclick="toggleFlag()" title="Mark for Review">
                <i class="fas fa-flag"></i>
            </button>
            <!-- Prev -->
            <button type="button" class="bn-btn bn-prev" id="prevBtn" onclick="prev()" disabled>
                <i class="fas fa-chevron-left"></i> Prev
            </button>
            <!-- Next -->
            <button type="button" class="bn-btn bn-next" id="nextBtn" onclick="next()">
                Next <i class="fas fa-chevron-right"></i>
            </button>
            <!-- Grid -->
            <button type="button" class="bn-btn bn-grid" onclick="openSheet()" title="Question Map">
                <i class="fas fa-th"></i>
                <span class="bn-grid-badge" id="unansweredBadge">{{ $total }}</span>
            </button>
        </div>
    </div>

    <!-- ══════ ANTI-CHEAT OVERLAY ══════ -->
    <div class="tab-overlay" id="tabOverlay">
        <div class="tab-overlay-box">
            <div class="tab-overlay-icon warn" id="overlayIcon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h2 id="overlayTitle">Quiz Frozen</h2>
            <p id="overlayMsg">You left the quiz. It's paused and locked until you return.</p>
            <div class="violation-bar">
                <div class="v-dot" id="vdot0"></div>
                <div class="v-dot" id="vdot1"></div>
                <div class="v-dot" id="vdot2"></div>
            </div>
            <div class="violation-label warn" id="violationLabel">
                Violation <span id="vCount">1</span> of 3
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

</div><!-- /phone -->

<!-- ══════ QUESTION NAVIGATOR SHEET ══════ -->
<div class="sheet-overlay" id="sheetOverlay" onclick="closeSheet()"></div>
<div class="nav-sheet" id="navSheet">
    <div class="sheet-handle"></div>
    <div class="sheet-title">
        Question Map
        <button class="sheet-close" onclick="closeSheet()"><i class="fas fa-times"></i></button>
    </div>
    <div class="sheet-legend">
        <div class="leg-item"><div class="leg-dot" style="background:var(--primary)"></div> Current</div>
        <div class="leg-item"><div class="leg-dot" style="background:#6ee7b7"></div> Answered</div>
        <div class="leg-item"><div class="leg-dot" style="background:#fcd34d"></div> For Review</div>
        <div class="leg-item"><div class="leg-dot" style="background:#f3f4f6;border:1.5px solid #e5e7eb"></div> Unanswered</div>
    </div>
    <div class="sheet-grid" id="navGrid">
        @foreach($questions as $i => $q)
        <button type="button" class="nav-btn{{ $i === 0 ? ' current' : '' }}" id="nav-{{ $i }}"
                onclick="goTo({{ $i }}); closeSheet();">
            {{ $i + 1 }}<span class="flag-dot"></span>
        </button>
        @endforeach
    </div>
</div>

<!-- ══════ END QUIZ MODAL (bottom sheet) ══════ -->
<div class="modal-overlay" id="endModal" onclick="if(event.target===this)closeModal()">
    <div class="modal">
        <div class="modal-handle"></div>
        <h3><i class="fas fa-flag-checkered" style="color:var(--primary);margin-right:8px;"></i>Submit Quiz?</h3>
        <p>Review your progress before submitting. This cannot be undone.</p>
        <div class="modal-chips">
            <span class="q-chip ans" style="font-size:12px;padding:5px 12px;"><i class="fas fa-check-circle"></i> <span id="mAnswered">0</span> Answered</span>
            <span class="q-chip flag" style="font-size:12px;padding:5px 12px;"><i class="fas fa-flag"></i> <span id="mFlagged">0</span> For Review</span>
            <span class="q-chip left" style="font-size:12px;padding:5px 12px;"><i class="fas fa-minus-circle"></i> <span id="mLeft">0</span> Remaining</span>
        </div>
        <div class="modal-actions">
            <button class="modal-btn primary" onclick="submitQuiz()"><i class="fas fa-check"></i> Submit Now</button>
            <button class="modal-btn ghost"   onclick="closeModal()">Go Back</button>
        </div>
    </div>
</div>

<script>
const TOTAL        = {{ $total }};
const SESSION_TYPE = '{{ $session->session_type }}';
let current   = 0;
const flagged = new Set();
const answered = new Set();

/* ── Navigate ── */
function goTo(idx) {
    if (idx < 0 || idx >= TOTAL) return;
    document.getElementById('slide-'  + current)?.classList.remove('active');
    document.getElementById('nav-'    + current)?.classList.remove('current');
    current = idx;
    document.getElementById('slide-'  + current)?.classList.add('active');
    document.getElementById('nav-'    + current)?.classList.add('current');
    document.getElementById('qBody').scrollTop = 0;

    // Header progress (position in quiz, not answered count)
    document.getElementById('progFill').style.width = ((current + 1) / TOTAL * 100) + '%';
    document.getElementById('qCounterNum').textContent = 'Question ' + (current + 1) + ' of ' + TOTAL;

    // Buttons
    document.getElementById('prevBtn').disabled = current === 0;
    const nb = document.getElementById('nextBtn');
    nb.innerHTML = current === TOTAL - 1
        ? '<i class="fas fa-flag-checkered"></i> Finish'
        : 'Next <i class="fas fa-chevron-right"></i>';

    // Flag button state
    const fb = document.getElementById('flagBtn');
    fb.classList.toggle('active', flagged.has(current));

    // Flag banner
    document.getElementById('flagBanner-' + current)?.classList.toggle('visible', flagged.has(current));

    refreshNav();
}

function prev() { goTo(current - 1); }
function next() { current === TOTAL - 1 ? openModal() : goTo(current + 1); }

/* ── Flag ── */
function toggleFlag() {
    flagged.has(current) ? flagged.delete(current) : flagged.add(current);
    goTo(current);
    refreshStats();
}

/* ── Answer listener ── */
document.getElementById('quizForm').addEventListener('change', function(e) {
    if (e.target.type !== 'radio') return;
    const slide = document.getElementById('slide-' + current);
    slide.querySelectorAll('.choice').forEach(c => c.classList.remove('selected'));
    const chosen = e.target.closest('.choice');
    chosen.classList.add('selected');
    answered.add(current);
    refreshNav();
    refreshStats();

    if (SESSION_TYPE === 'training' && !slide.classList.contains('revealed')) {
        slide.classList.add('revealed');
        const isCorrect = chosen.dataset.correct === '1';

        // highlight all choices
        slide.querySelectorAll('.choice').forEach(function(c) {
            if (c.dataset.correct === '1') {
                c.classList.add('reveal-correct');
            } else if (c === chosen && !isCorrect) {
                c.classList.add('reveal-wrong');
            }
        });

        // feedback panel
        const panel = document.getElementById('feedback-' + current);
        if (panel) {
            const explanation = panel.dataset.explanation;
            panel.className = 'feedback-panel visible ' + (isCorrect ? 'correct' : 'wrong');
            panel.innerHTML = (isCorrect
                ? '<i class="fas fa-check-circle"></i> <strong>Correct!</strong>'
                : '<i class="fas fa-times-circle"></i> <strong>Incorrect.</strong>')
                + (explanation
                    ? '<div class="explain-text"><i class="fas fa-lightbulb"></i> ' + explanation + '</div>'
                    : '');
        }
    }
});

/* ── Navigator classes ── */
function refreshNav() {
    for (let i = 0; i < TOTAL; i++) {
        const btn = document.getElementById('nav-' + i);
        if (!btn) continue;
        btn.className = 'nav-btn';
        if (i === current)    btn.classList.add('current');
        if (answered.has(i))  btn.classList.add('answered');
        if (flagged.has(i))   btn.classList.add('flagged');
    }
}

/* ── Stats ── */
function refreshStats() {
    const a = answered.size, f = flagged.size, l = TOTAL - a;
    document.getElementById('statAnswered').textContent = a;
    document.getElementById('statFlagged').textContent  = f;
    document.getElementById('statLeft').textContent     = l;
    document.getElementById('mAnswered').textContent    = a;
    document.getElementById('mFlagged').textContent     = f;
    document.getElementById('mLeft').textContent        = l;
    const badge = document.getElementById('unansweredBadge');
    badge.textContent = l;
    badge.style.display = l > 0 ? '' : 'none';
}

/* ── Bottom sheet ── */
function openSheet()  { document.getElementById('sheetOverlay').classList.add('open'); document.getElementById('navSheet').classList.add('open'); }
function closeSheet() { document.getElementById('sheetOverlay').classList.remove('open'); document.getElementById('navSheet').classList.remove('open'); }

/* ── Submit modal ── */
function openModal()  { refreshStats(); document.getElementById('endModal').classList.add('open'); }
function closeModal() { document.getElementById('endModal').classList.remove('open'); }
document.getElementById('endQuizBtn').addEventListener('click', openModal);

let timeUp = false;
function submitQuiz() {
    window.onbeforeunload = null;
    document.getElementById('quizForm').submit();
}

/* ══════════ ANTI-CHEAT ══════════ */
const MAX_VIOLATIONS = 3;
let violations  = 0;
let frozen      = false;
let timerRunning = true;

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
    document.getElementById('tabOverlay').classList.remove('show');
}
function resumeQuiz() { unfreeze(); }

function showViolationOverlay() {
    violations++;
    for (let i = 0; i < 3; i++) {
        const d = document.getElementById('vdot' + i);
        if (!d) continue;
        d.classList.remove('used','final');
        if (i < violations) d.classList.add(i === MAX_VIOLATIONS - 1 ? 'final' : 'used');
    }
    document.getElementById('vCount').textContent = violations;

    const icon  = document.getElementById('overlayIcon');
    const title = document.getElementById('overlayTitle');
    const msg   = document.getElementById('overlayMsg');
    const label = document.getElementById('violationLabel');

    if (violations >= MAX_VIOLATIONS) {
        icon.className  = 'tab-overlay-icon crit';
        icon.innerHTML  = '<i class="fas fa-ban"></i>';
        title.textContent = 'Quiz Terminated';
        msg.innerHTML   = 'You left the quiz <strong>' + violations + ' times</strong>. Auto-submitting now…';
        label.className = 'violation-label critical';
        label.textContent = 'Maximum violations reached';
        document.getElementById('resumeBtn').style.display = 'none';
        document.getElementById('tabOverlay').classList.add('show');
        setTimeout(function() { window.onbeforeunload = null; document.getElementById('quizForm').submit(); }, 3000);
        return;
    }

    icon.className  = 'tab-overlay-icon warn';
    icon.innerHTML  = '<i class="fas fa-shield-alt"></i>';
    title.textContent = 'Quiz Frozen';
    const rem = MAX_VIOLATIONS - violations;
    msg.innerHTML = 'You left the quiz tab.<br><span style="color:var(--accent);font-weight:600;">⚠ ' + rem + ' more violation' + (rem > 1 ? 's' : '') + ' will auto-submit.</span>';
    label.className = 'violation-label warn';
    label.innerHTML = 'Violation <span id="vCount">' + violations + '</span> of ' + MAX_VIOLATIONS;
    document.getElementById('resumeBtn').style.display = '';
    document.getElementById('tabOverlay').classList.add('show');
}

function confirmLeave() {
    if (!confirm('Leave the quiz? Your current answers will be submitted.')) return;
    window.onbeforeunload = null;
    document.getElementById('quizForm').submit();
}

document.addEventListener('visibilitychange', function() {
    if (document.hidden) { freeze(); }
    else { showViolationOverlay(); }
});
let blurTimer = null;
window.addEventListener('blur', function() {
    blurTimer = setTimeout(function() { if (!document.hidden) { freeze(); showViolationOverlay(); } }, 300);
});
window.addEventListener('focus', function() { clearTimeout(blurTimer); });
document.addEventListener('contextmenu', e => e.preventDefault());
document.addEventListener('keydown', function(e) {
    const ctrl = e.ctrlKey || e.metaKey;
    if ((ctrl && ['t','w','n'].includes(e.key)) || e.key === 'F12') e.preventDefault();
});
window.addEventListener('beforeunload', function(e) {
    e.preventDefault();
    e.returnValue = 'Your quiz is still in progress.';
    return e.returnValue;
});

/* ══════════ TIMER ══════════ */
@isset($timeLimit)
(function() {
    let remaining = {{ max(0, $timeLimit - (int) $session->started_at->diffInSeconds(now())) }};
    const badge = document.getElementById('timerBadge');
    const label = document.getElementById('timer');

    function render() {
        const m = Math.floor(remaining / 60), s = remaining % 60;
        label.textContent = m + ':' + String(s).padStart(2,'0');
        badge.classList.toggle('warning', remaining <= 60 && remaining > 20);
        badge.classList.toggle('danger',  remaining <= 20);
    }
    function tick() {
        if (!timerRunning) { setTimeout(tick, 500); return; }
        if (remaining <= 0) {
            timeUp = true; label.textContent = '0:00';
            window.onbeforeunload = null;
            document.getElementById('quizForm').submit(); return;
        }
        render(); remaining--; setTimeout(tick, 1000);
    }
    tick();
})();
@endif

refreshStats();
</script>
</body>
</html>
