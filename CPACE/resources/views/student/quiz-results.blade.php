<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results – {{ $session->subject->name ?? 'CPACE' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:      #7B1D1D;
            --primary-hover:#6a1818;
            --primary-light:#f5e8e8;
            --accent:       #c0392b;
            --green:        #10b981;
            --green-light:  #d1fae5;
            --red:          #ef4444;
            --amber:        #f59e0b;
            --amber-light:  #fef3c7;
            --gray:         #9ca3af;
        }
        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }

        /* ══════ BASE (mobile-first phone shell) ══════ */
        html, body { height:100%; overflow:hidden; }
        body {
            font-family:'Poppins',sans-serif;
            background:#1a0a0a;
            color:#1a1a1a;
            display:flex;
            justify-content:center;
            align-items:stretch;
        }

        .results-wrap {
            width:100%; max-width:480px;
            background:#f5f5f7;
            display:flex; flex-direction:column;
            height:100%; overflow:hidden;
        }

        /* ── Header ── */
        .hdr {
            background:var(--primary);
            padding:16px 18px;
            flex-shrink:0;
            display:flex; align-items:center; gap:12px;
        }
        .hdr-icon {
            width:38px; height:38px;
            background:rgba(255,255,255,.15);
            border-radius:10px;
            display:flex; align-items:center; justify-content:center;
            color:#fff; font-size:15px; flex-shrink:0;
        }
        .hdr-title { flex:1; min-width:0; }
        .hdr-title strong { display:block; font-size:14px; font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .hdr-title span   { font-size:10px; color:rgba(255,255,255,.6); }
        .hdr-back {
            background:rgba(255,255,255,.15); border:none;
            color:#fff; border-radius:10px;
            width:36px; height:36px; flex-shrink:0;
            display:flex; align-items:center; justify-content:center;
            cursor:pointer; font-size:14px; text-decoration:none;
        }

        /* ── Scrollable body ── */
        .results-body {
            flex:1; overflow-y:auto; -webkit-overflow-scrolling:touch;
            padding:16px 16px 20px;
        }

        /* ── Score card ── */
        .score-card {
            background:#fff; border-radius:18px;
            padding:24px 20px; margin-bottom:14px;
            box-shadow:0 2px 10px rgba(0,0,0,.07);
            text-align:center;
        }
        .score-ring {
            width:120px; height:120px; position:relative;
            margin:0 auto 16px;
            display:flex; align-items:center; justify-content:center;
        }
        .score-ring svg { position:absolute; inset:0; width:100%; height:100%; transform:rotate(-90deg); }
        .score-inner { position:relative; text-align:center; }
        .score-pct { font-size:30px; font-weight:700; line-height:1; }
        .score-lbl { font-size:10px; color:var(--gray); margin-top:2px; }

        .verdict { display:inline-block; padding:4px 16px; border-radius:20px; font-size:11px; font-weight:700; margin-bottom:8px; }
        .verdict.pass { background:var(--green-light); color:#059669; }
        .verdict.fail { background:var(--amber-light);  color:#d97706; }

        .score-label { font-size:16px; font-weight:700; color:#1a1a1a; margin-bottom:4px; }
        .score-sub   { font-size:12px; color:var(--gray); margin-bottom:14px; }
        .score-pills { display:flex; justify-content:center; gap:8px; flex-wrap:wrap; }
        .pill { display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:10px; font-size:11px; font-weight:600; }
        .pill.correct { background:var(--green-light); color:#059669; }
        .pill.wrong   { background:#fee2e2; color:var(--red); }
        .pill.time    { background:#dbeafe; color:#2563eb; }

        /* ── Review section ── */
        .section-lbl { font-size:11px; font-weight:700; color:var(--gray); text-transform:uppercase; letter-spacing:.8px; margin:16px 0 10px; }

        .rq {
            background:#fff; border-radius:14px;
            padding:16px; margin-bottom:10px;
            border-left:4px solid #ddd;
            box-shadow:0 1px 4px rgba(0,0,0,.05);
        }
        .rq.correct { border-left-color:var(--green); }
        .rq.wrong   { border-left-color:var(--red); }
        .rq-num { font-size:10px; font-weight:700; color:var(--gray); letter-spacing:.5px; margin-bottom:6px; text-transform:uppercase; }
        .rq-top { display:flex; align-items:flex-start; gap:10px; margin-bottom:12px; }
        .rq-status {
            width:26px; height:26px; flex-shrink:0; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            color:#fff; font-size:11px;
        }
        .rq-status.correct { background:var(--green); }
        .rq-status.wrong   { background:var(--red); }
        .rq-text { font-size:13px; font-weight:500; color:#1a1a1a; line-height:1.5; }

        .opts-list { display:flex; flex-direction:column; gap:6px; }
        .opt { display:flex; align-items:center; gap:8px; padding:9px 12px; border-radius:9px; font-size:12px; border:1px solid #f0f0f0; }
        .opt.is-correct      { background:#ecfdf5; border-color:#a7f3d0; color:#065f46; font-weight:600; }
        .opt.is-chosen-wrong { background:#fef2f2; border-color:#fecaca; color:#991b1b; }
        .opt-letter { width:20px; height:20px; flex-shrink:0; border-radius:50%; background:#f0f0f0; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; }
        .opt.is-correct .opt-letter      { background:#a7f3d0; color:#065f46; }
        .opt.is-chosen-wrong .opt-letter { background:#fecaca; color:#991b1b; }
        .opt .tag { margin-left:auto; font-size:10px; font-weight:700; white-space:nowrap; padding:2px 7px; border-radius:5px; }
        .opt.is-correct .tag      { background:#d1fae5; color:#059669; }
        .opt.is-chosen-wrong .tag { background:#fee2e2; color:#991b1b; }

        .skipped-note { font-size:11px; color:var(--gray); margin-top:8px; }
        .explain { margin-top:10px; padding:10px 12px; background:#fffbeb; border-radius:8px; font-size:11px; color:#92400e; line-height:1.55; }
        .explain i { margin-right:5px; }

        /* ── Bottom bar ── */
        .bottom-bar {
            background:#fff; border-top:1px solid #e8e8e8;
            padding:12px 16px 20px; flex-shrink:0;
            display:flex; flex-direction:column; gap:8px;
            box-shadow:0 -4px 16px rgba(0,0,0,.07);
        }
        .bb-btn {
            display:flex; align-items:center; justify-content:center; gap:8px;
            padding:14px; border-radius:14px; border:none;
            font-size:14px; font-weight:700;
            font-family:'Poppins',sans-serif; cursor:pointer; text-decoration:none;
        }
        .bb-primary { background:var(--primary); color:#fff; }
        .bb-primary:hover { background:var(--primary-hover); }
        .bb-ghost   { background:#f3f4f6; color:#374151; }
        .bb-ghost:hover { background:#e5e7eb; }

        /* ══════ DESKTOP LAYOUT (≥ 900px) ══════ */
        @media (min-width: 900px) {
            html, body { height:auto; overflow:auto; background:#f0f2f5; }
            body { align-items:flex-start; justify-content:flex-start; }

            .results-wrap {
                max-width:none; width:100%;
                background:transparent;
                height:auto; min-height:100vh;
                overflow:visible;
                display:flex; flex-direction:column;
            }

            /* Sticky header */
            .hdr {
                position:sticky; top:0; z-index:20;
                padding:18px 36px;
            }
            .hdr-icon  { width:44px; height:44px; font-size:18px; border-radius:12px; }
            .hdr-title strong { font-size:17px; }
            .hdr-title span   { font-size:11px; }
            .hdr-back  { width:40px; height:40px; font-size:15px; border-radius:12px; }

            /* Scrollable body becomes a regular content area */
            .results-body {
                overflow:visible; flex:none;
                padding:36px 0;
                width:100%; max-width:880px;
                margin:0 auto;
            }

            /* Score card: horizontal layout */
            .score-card {
                display:flex; align-items:center; gap:40px;
                text-align:left; padding:36px 40px;
                border-radius:22px; margin-bottom:24px;
            }
            .score-ring {
                width:160px; height:160px; flex-shrink:0; margin:0;
            }
            .score-pct { font-size:42px; }
            .score-lbl { font-size:11px; }

            .score-info { flex:1; }
            .verdict { font-size:12px; padding:5px 18px; margin-bottom:10px; }
            .score-label { font-size:22px; margin-bottom:6px; }
            .score-sub { font-size:14px; margin-bottom:18px; }
            .score-pills { justify-content:flex-start; gap:10px; }
            .pill { font-size:12px; padding:8px 14px; border-radius:12px; }

            /* Review section */
            .section-lbl { font-size:12px; margin:0 0 14px; }
            .rq { padding:22px 26px; margin-bottom:12px; border-radius:16px; border-left-width:5px; }
            .rq-text  { font-size:15px; }
            .opts-list { gap:8px; }
            .opt { font-size:13px; padding:12px 16px; border-radius:11px; }
            .opt-letter { width:26px; height:26px; font-size:11px; }
            .opt .tag { font-size:11px; padding:3px 9px; border-radius:6px; }
            .explain { font-size:12.5px; padding:13px 16px; border-radius:10px; }
            .rq-status { width:30px; height:30px; font-size:12px; }
            .rq-num { font-size:11px; }

            /* Bottom bar: horizontal row, not fixed */
            .bottom-bar {
                box-shadow:none; border-top:none;
                background:transparent;
                flex-direction:row; gap:12px;
                padding:0; margin-top:8px;
                width:100%; max-width:880px;
                margin-left:auto; margin-right:auto;
                padding-bottom:48px;
            }
            .bb-btn {
                flex:1; max-width:260px;
                padding:15px 20px; border-radius:14px; font-size:14px;
            }
        }

        /* Large desktop */
        @media (min-width: 1200px) {
            .results-body { max-width:960px; }
            .bottom-bar   { max-width:960px; }
            .hdr { padding:18px 48px; }
        }

        /* Small mobile */
        @media (max-width: 480px) {
            .score-ring { width:100px; height:100px; }
            .score-pct  { font-size:26px; }
            .score-label { font-size:14px; }
            .score-pills { gap:6px; }
            .pill { font-size:10px; padding:5px 10px; }
            .rq { padding:12px; }
            .rq-text { font-size:12px; }
            .opt { font-size:11px; padding:8px 10px; }
        }
    </style>
</head>
<body>

@php
    $score        = (int) round($session->score_percent);
    $passing      = $score >= 75;
    $circumference = 2 * pi() * 50;
    $filled       = round($circumference * $score / 100, 1);
    $mins         = intdiv((int) $session->duration_secs, 60);
    $secs         = (int) $session->duration_secs % 60;
@endphp

<div class="results-wrap">

    <!-- ── Header ── -->
    <div class="hdr">
        <div class="hdr-icon"><i class="fas fa-flag-checkered"></i></div>
        <div class="hdr-title">
            <strong>{{ $session->subject->name ?? 'Quiz' }} — Results</strong>
            <span>{{ $session->completed_at?->format('M d, Y · g:i A') }}</span>
        </div>
        <a href="{{ route('adaptive-quizzes') }}" class="hdr-back" title="Back to Quizzes">
            <i class="fas fa-home"></i>
        </a>
    </div>

    <!-- ── Scrollable body ── -->
    <div class="results-body">

        <!-- Score card -->
        <div class="score-card">
            <div class="score-ring">
                <svg viewBox="0 0 120 120">
                    <circle cx="60" cy="60" r="50" fill="none" stroke="#f0e6e6" stroke-width="10"/>
                    <circle cx="60" cy="60" r="50" fill="none"
                            stroke="{{ $passing ? '#10b981' : '#c0392b' }}" stroke-width="10"
                            stroke-dasharray="{{ $filled }} {{ round($circumference,1) }}"
                            stroke-linecap="round"/>
                </svg>
                <div class="score-inner">
                    <div class="score-pct" style="color:{{ $passing ? '#10b981' : '#c0392b' }}">{{ $score }}%</div>
                    <div class="score-lbl">Score</div>
                </div>
            </div>

            {{-- .score-info wraps text side (used for horizontal layout on desktop) --}}
            <div class="score-info">
                <span class="verdict {{ $passing ? 'pass' : 'fail' }}">{{ $passing ? '🎉 PASSED' : 'KEEP PRACTICING' }}</span>
                <div class="score-label">{{ $session->subject->name ?? 'Quiz' }} Complete!</div>
                <div class="score-sub">{{ $session->correct_answers }} of {{ $session->total_items }} correct</div>
                <div class="score-pills">
                    <span class="pill correct"><i class="fas fa-check-circle"></i> {{ $session->correct_answers }} Correct</span>
                    <span class="pill wrong"><i class="fas fa-times-circle"></i> {{ $session->total_items - $session->correct_answers }} Wrong</span>
                    <span class="pill time"><i class="fas fa-clock"></i> {{ $mins }}m {{ $secs }}s</span>
                </div>
            </div>
        </div>

        <!-- Review answers -->
        <div class="section-lbl">Review Answers</div>

        @foreach($questions as $i => $question)
            @php
                $answer     = $answers[$question->id] ?? null;
                $selectedId = $answer->selected_choice ?? null;
                $isCorrect  = $answer->is_correct ?? false;
            @endphp
            <div class="rq {{ $isCorrect ? 'correct' : 'wrong' }}">
                <div class="rq-top">
                    <div class="rq-status {{ $isCorrect ? 'correct' : 'wrong' }}">
                        <i class="fas {{ $isCorrect ? 'fa-check' : 'fa-times' }}"></i>
                    </div>
                    <div class="rq-text">{{ $i + 1 }}. {{ $question->question_text }}</div>
                </div>
                <div class="opts-list">
                    @foreach($question->choices as $choice)
                        @php
                            $isKey         = $choice->is_correct;
                            $isChosenWrong = $selectedId === $choice->id && !$choice->is_correct;
                        @endphp
                        <div class="opt {{ $isKey ? 'is-correct' : ($isChosenWrong ? 'is-chosen-wrong' : '') }}">
                            <span class="opt-letter">{{ $choice->choice_label }}</span>
                            <span>{{ $choice->choice_text }}</span>
                            @if($isKey)
                                <span class="tag">✓ Correct</span>
                            @elseif($isChosenWrong)
                                <span class="tag">✗ Your answer</span>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if($selectedId === null)
                    <div class="skipped-note"><i class="fas fa-info-circle"></i> Skipped</div>
                @endif
                @if($question->explanation)
                    <div class="explain"><i class="fas fa-lightbulb"></i> {{ $question->explanation }}</div>
                @endif
            </div>
        @endforeach

    </div>

    <!-- ── Action buttons ── -->
    <div class="bottom-bar">
        <a href="{{ route('adaptive-quizzes') }}" class="bb-btn bb-primary">
            <i class="fas fa-redo"></i> Take Another Quiz
        </a>
        <a href="{{ route('dashboard') }}" class="bb-btn bb-ghost">
            <i class="fas fa-home"></i> Back to Dashboard
        </a>
    </div>

</div>

</body>
</html>
