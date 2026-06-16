<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results – {{ $session->subject->name ?? 'CPACE' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #7B1D1D;
            --primary-hover: #6a1818;
            --primary-light: #f5e8e8;
            --accent: #c0392b;
            --green: #10b981;
            --red: #ef4444;
            --gray: #999;
            --top-h: 64px;
            --bot-h: 72px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; color: #333; height: 100vh; overflow: hidden; display: flex; flex-direction: column; }

        /* ── Top bar ── */
        .quiz-topbar {
            height: var(--top-h);
            background: #7B1D1D;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            flex-shrink: 0;
            gap: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,.18);
        }
        .topbar-left { display: flex; align-items: center; gap: 14px; }
        .topbar-logo { width: 36px; height: 36px; border-radius: 8px; overflow: hidden; flex-shrink: 0; }
        .topbar-logo img { width: 100%; height: 100%; object-fit: contain; }
        .topbar-subject { font-size: 16px; font-weight: 700; color: #fff; }
        .topbar-sub { font-size: 11px; color: rgba(255,255,255,.6); margin-top: 1px; }

        /* ── Scrollable body ── */
        .results-body { flex: 1; overflow-y: auto; padding: 28px 0; }
        .results-inner { max-width: 760px; margin: 0 auto; padding: 0 20px; }

        /* ── Score card ── */
        .score-card {
            background: #fff;
            border-radius: 16px;
            padding: 32px 36px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 32px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        .score-ring { width: 140px; height: 140px; flex-shrink: 0; position: relative; display: flex; align-items: center; justify-content: center; }
        .score-ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
        .score-inner { position: absolute; text-align: center; }
        .score-pct { font-size: 34px; font-weight: 700; line-height: 1; }
        .score-lbl { font-size: 11px; color: var(--gray); margin-top: 4px; }
        .score-info h2 { font-size: 22px; font-weight: 700; color: #1a1a1a; margin-bottom: 6px; }
        .score-info p { font-size: 13px; color: var(--gray); margin-bottom: 16px; }
        .score-pills { display: flex; gap: 10px; flex-wrap: wrap; }
        .pill { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 600; }
        .pill.correct { background: #d1fae5; color: #059669; }
        .pill.wrong   { background: #fee2e2; color: var(--red); }
        .pill.time    { background: #dbeafe; color: #2563eb; }
        .verdict { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; margin-bottom: 10px; }
        .verdict.pass { background: #d1fae5; color: #059669; }
        .verdict.fail { background: #fef3c7; color: #d97706; }

        .section-title { font-size: 15px; font-weight: 700; color: #1a1a1a; margin: 0 0 14px; }

        /* ── Review cards ── */
        .rq { background: #fff; border-radius: 14px; padding: 22px 24px; margin-bottom: 14px; border-left: 4px solid #ddd; box-shadow: 0 1px 4px rgba(0,0,0,.05); }
        .rq.correct { border-left-color: var(--green); }
        .rq.wrong   { border-left-color: var(--red); }
        .rq-top { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
        .rq-status { width: 28px; height: 28px; flex-shrink: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 12px; }
        .rq-status.correct { background: var(--green); }
        .rq-status.wrong   { background: var(--red); }
        .rq-text { font-size: 14px; font-weight: 500; color: #1a1a1a; line-height: 1.55; }
        .opt { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 9px; font-size: 13px; margin-bottom: 7px; border: 1px solid #f0f0f0; }
        .opt.is-correct       { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; font-weight: 600; }
        .opt.is-chosen-wrong  { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .opt-letter { width: 22px; height: 22px; flex-shrink: 0; border-radius: 50%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; }
        .opt .tag { margin-left: auto; font-size: 11px; font-weight: 700; }
        .explain { margin-top: 12px; padding: 12px 16px; background: #fffbeb; border-radius: 9px; font-size: 13px; color: #92400e; line-height: 1.55; }
        .explain i { margin-right: 6px; }

        /* ── Bottom bar ── */
        .quiz-bottombar {
            height: var(--bot-h);
            background: #fff;
            border-top: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 28px;
            gap: 10px;
            flex-shrink: 0;
            box-shadow: 0 -2px 12px rgba(0,0,0,.05);
        }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 11px 24px; border-radius: 10px; font-size: 14px; font-weight: 600; font-family: 'Poppins', sans-serif; cursor: pointer; border: none; text-decoration: none; transition: background .15s; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-ghost { background: #fff; color: #555; border: 1px solid #e0e0e0; }
        .btn-ghost:hover { background: #f9f9f9; }
    </style>
</head>
<body>

@php
    $score        = (int) round($session->score_percent);
    $passing      = $score >= 75;
    $circumference = 2 * pi() * 58;
    $filled       = round($circumference * $score / 100, 1);
    $mins         = intdiv((int) $session->duration_secs, 60);
    $secs         = (int) $session->duration_secs % 60;
@endphp

<!-- TOP BAR -->
<div class="quiz-topbar">
    <div class="topbar-left">
        <div class="topbar-logo">
            <img src="{{ asset('images/cpace_logo.png') }}" alt="CPACE">
        </div>
        <div>
            <div class="topbar-subject">{{ $session->subject->name ?? 'Quiz' }} — Results</div>
            <div class="topbar-sub">Review your answers below</div>
        </div>
    </div>
    <span style="color:rgba(255,255,255,.7);font-size:13px;">
        <i class="fas fa-calendar-alt" style="margin-right:6px;"></i>
        {{ $session->completed_at?->format('M d, Y · g:i A') }}
    </span>
</div>

<!-- SCROLLABLE BODY -->
<div class="results-body">
    <div class="results-inner">

        <!-- SCORE CARD -->
        <div class="score-card">
            <div class="score-ring">
                <svg viewBox="0 0 140 140">
                    <circle cx="70" cy="70" r="58" fill="none" stroke="#f0e6e6" stroke-width="12"/>
                    <circle cx="70" cy="70" r="58" fill="none"
                            stroke="{{ $passing ? '#10b981' : '#c0392b' }}" stroke-width="12"
                            stroke-dasharray="{{ $filled }} {{ round($circumference, 1) }}"
                            stroke-linecap="round"/>
                </svg>
                <div class="score-inner">
                    <div class="score-pct" style="color:{{ $passing ? '#10b981' : '#c0392b' }};">{{ $score }}%</div>
                    <div class="score-lbl">Score</div>
                </div>
            </div>
            <div class="score-info">
                <span class="verdict {{ $passing ? 'pass' : 'fail' }}">{{ $passing ? 'PASSED' : 'KEEP PRACTICING' }}</span>
                <h2>{{ $session->subject->name ?? 'Quiz' }} Complete!</h2>
                <p>You answered {{ $session->correct_answers }} of {{ $session->total_items }} questions correctly.</p>
                <div class="score-pills">
                    <span class="pill correct"><i class="fas fa-check"></i> {{ $session->correct_answers }} Correct</span>
                    <span class="pill wrong"><i class="fas fa-times"></i> {{ $session->total_items - $session->correct_answers }} Incorrect</span>
                    <span class="pill time"><i class="fas fa-clock"></i> {{ $mins }}m {{ $secs }}s</span>
                </div>
            </div>
        </div>

        <!-- REVIEW -->
        <div class="section-title">Review Your Answers</div>

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
                        $isAnswerKey   = $choice->is_correct;
                        $isChosenWrong = $selectedId === $choice->id && ! $choice->is_correct;
                    @endphp
                    <div class="opt {{ $isAnswerKey ? 'is-correct' : ($isChosenWrong ? 'is-chosen-wrong' : '') }}">
                        <span class="opt-letter">{{ $choice->choice_label }}</span>
                        <span>{{ $choice->choice_text }}</span>
                        @if($isAnswerKey)
                            <span class="tag" style="color:#059669;">Correct answer</span>
                        @elseif($isChosenWrong)
                            <span class="tag" style="color:#991b1b;">Your answer</span>
                        @endif
                    </div>
                @endforeach
                @if($selectedId === null)
                    <div style="font-size:12px;color:var(--gray);margin-top:8px;">
                        <i class="fas fa-info-circle"></i> You skipped this question.
                    </div>
                @endif
                @if($question->explanation)
                    <div class="explain"><i class="fas fa-lightbulb"></i>{{ $question->explanation }}</div>
                @endif
            </div>
        @endforeach

        <div style="height:8px;"></div>
    </div>
</div>

<!-- BOTTOM BAR -->
<div class="quiz-bottombar">
    <button class="btn btn-ghost" onclick="window.close()">
        <i class="fas fa-times"></i> Close
    </button>
    <a href="{{ route('adaptive-quizzes') }}" target="_blank" class="btn btn-primary">
        <i class="fas fa-redo"></i> Take Another Quiz
    </a>
</div>

</body>
</html>
