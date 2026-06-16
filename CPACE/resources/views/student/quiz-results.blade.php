<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
        html, body { height:100%; overflow:hidden; }
        body {
            font-family:'Poppins',sans-serif;
            background:#1a0a0a;
            color:#1a1a1a;
            display:flex;
            justify-content:center;
            align-items:stretch;
        }

        /* Phone shell */
        .phone {
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

        /* ── Scrollable ── */
        .results-body {
            flex:1;
            overflow-y:auto;
            -webkit-overflow-scrolling:touch;
            padding:16px 16px 20px;
        }

        /* ── Score card ── */
        .score-card {
            background:#fff;
            border-radius:18px;
            padding:24px 20px;
            margin-bottom:14px;
            box-shadow:0 2px 10px rgba(0,0,0,.07);
            text-align:center;
        }
        .score-ring {
            width:120px; height:120px;
            position:relative;
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
            background:#fff;
            border-radius:14px;
            padding:16px;
            margin-bottom:10px;
            border-left:4px solid #ddd;
            box-shadow:0 1px 4px rgba(0,0,0,.05);
        }
        .rq.correct { border-left-color:var(--green); }
        .rq.wrong   { border-left-color:var(--red); }

        .rq-top { display:flex; align-items:flex-start; gap:10px; margin-bottom:12px; }
        .rq-status {
            width:26px; height:26px; flex-shrink:0;
            border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            color:#fff; font-size:11px;
        }
        .rq-status.correct { background:var(--green); }
        .rq-status.wrong   { background:var(--red); }
        .rq-text { font-size:13px; font-weight:500; color:#1a1a1a; line-height:1.5; }

        .opt { display:flex; align-items:center; gap:8px; padding:9px 12px; border-radius:9px; font-size:12px; margin-bottom:6px; border:1px solid #f0f0f0; }
        .opt.is-correct      { background:#ecfdf5; border-color:#a7f3d0; color:#065f46; font-weight:600; }
        .opt.is-chosen-wrong { background:#fef2f2; border-color:#fecaca; color:#991b1b; }
        .opt-letter { width:20px; height:20px; flex-shrink:0; border-radius:50%; background:#f0f0f0; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; }
        .opt .tag { margin-left:auto; font-size:10px; font-weight:700; white-space:nowrap; }

        .explain { margin-top:10px; padding:10px 12px; background:#fffbeb; border-radius:8px; font-size:11px; color:#92400e; line-height:1.55; }

        /* ── Bottom bar ── */
        .bottom-bar {
            background:#fff;
            border-top:1px solid #e8e8e8;
            padding:12px 16px 20px;
            flex-shrink:0;
            display:flex; flex-direction:column; gap:8px;
            box-shadow:0 -4px 16px rgba(0,0,0,.07);
        }
        .bb-btn {
            display:flex; align-items:center; justify-content:center; gap:8px;
            padding:14px;
            border-radius:14px; border:none;
            font-size:14px; font-weight:700;
            font-family:'Poppins',sans-serif; cursor:pointer;
        }
        .bb-primary { background:var(--primary); color:#fff; }
        .bb-ghost   { background:#f3f4f6; color:#374151; }
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

<div class="phone">

    <!-- ── Header ── -->
    <div class="hdr">
        <div class="hdr-icon"><i class="fas fa-flag-checkered"></i></div>
        <div class="hdr-title">
            <strong>{{ $session->subject->name ?? 'Quiz' }} — Results</strong>
            <span>{{ $session->completed_at?->format('M d, Y · g:i A') }}</span>
        </div>
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
            <span class="verdict {{ $passing ? 'pass' : 'fail' }}">{{ $passing ? '🎉 PASSED' : 'KEEP PRACTICING' }}</span>
            <div class="score-label">{{ $session->subject->name ?? 'Quiz' }} Complete!</div>
            <div class="score-sub">{{ $session->correct_answers }} of {{ $session->total_items }} correct</div>
            <div class="score-pills">
                <span class="pill correct"><i class="fas fa-check"></i> {{ $session->correct_answers }} Correct</span>
                <span class="pill wrong"><i class="fas fa-times"></i> {{ $session->total_items - $session->correct_answers }} Wrong</span>
                <span class="pill time"><i class="fas fa-clock"></i> {{ $mins }}m {{ $secs }}s</span>
            </div>
        </div>

        <!-- Review -->
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
                @foreach($question->choices as $choice)
                    @php
                        $isKey         = $choice->is_correct;
                        $isChosenWrong = $selectedId === $choice->id && !$choice->is_correct;
                    @endphp
                    <div class="opt {{ $isKey ? 'is-correct' : ($isChosenWrong ? 'is-chosen-wrong' : '') }}">
                        <span class="opt-letter">{{ $choice->choice_label }}</span>
                        <span>{{ $choice->choice_text }}</span>
                        @if($isKey)<span class="tag" style="color:#059669;">✓ Correct</span>
                        @elseif($isChosenWrong)<span class="tag" style="color:#991b1b;">✗ Your answer</span>
                        @endif
                    </div>
                @endforeach
                @if($selectedId === null)
                    <div style="font-size:11px;color:var(--gray);margin-top:8px;"><i class="fas fa-info-circle"></i> Skipped</div>
                @endif
                @if($question->explanation)
                    <div class="explain"><i class="fas fa-lightbulb"></i> {{ $question->explanation }}</div>
                @endif
            </div>
        @endforeach

    </div>

    <!-- ── Bottom bar ── -->
    <div class="bottom-bar">
        <a href="{{ route('adaptive-quizzes') }}" target="_blank" class="bb-btn bb-primary">
            <i class="fas fa-redo"></i> Take Another Quiz
        </a>
        <button class="bb-btn bb-ghost" onclick="window.close()">
            <i class="fas fa-times"></i> Close
        </button>
    </div>

</div>

</body>
</html>
