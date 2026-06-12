<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - CPACE CPA Reviewer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --red:#ef4444; --gray:#999; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }
        .main-content { margin-left:220px; padding:30px 40px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main-content { margin-left:70px; }

        .score-card { background:white; border-radius:16px; padding:34px; margin-bottom:24px; display:flex; align-items:center; gap:34px; }
        .score-ring { width:150px; height:150px; flex-shrink:0; position:relative; display:flex; align-items:center; justify-content:center; }
        .score-ring svg { width:100%; height:100%; transform:rotate(-90deg); }
        .score-inner { position:absolute; text-align:center; }
        .score-pct { font-size:36px; font-weight:700; line-height:1; }
        .score-lbl { font-size:12px; color:var(--gray); margin-top:4px; }
        .score-info h2 { font-size:24px; font-weight:700; color:#1a1a1a; margin-bottom:6px; }
        .score-info p { font-size:14px; color:var(--gray); margin-bottom:16px; }
        .score-pills { display:flex; gap:12px; flex-wrap:wrap; }
        .pill { display:inline-flex; align-items:center; gap:8px; padding:9px 16px; border-radius:10px; font-size:13px; font-weight:600; }
        .pill.correct { background:#d1fae5; color:#059669; }
        .pill.wrong { background:#fee2e2; color:var(--red); }
        .pill.time { background:#dbeafe; color:#2563eb; }
        .verdict { display:inline-block; padding:5px 14px; border-radius:20px; font-size:12px; font-weight:700; margin-bottom:10px; }
        .verdict.pass { background:#d1fae5; color:#059669; }
        .verdict.fail { background:#fef3c7; color:#d97706; }

        .section-title { font-size:16px; font-weight:700; color:#1a1a1a; margin:8px 0 16px; }

        .rq { background:white; border-radius:14px; padding:22px 24px; margin-bottom:16px; border-left:4px solid #ddd; }
        .rq.correct { border-left-color:var(--green); }
        .rq.wrong { border-left-color:var(--red); }
        .rq-top { display:flex; align-items:flex-start; gap:12px; margin-bottom:14px; }
        .rq-status { width:28px; height:28px; flex-shrink:0; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-size:12px; }
        .rq-status.correct { background:var(--green); }
        .rq-status.wrong { background:var(--red); }
        .rq-text { font-size:14px; font-weight:500; color:#1a1a1a; line-height:1.5; }
        .opt { display:flex; align-items:center; gap:10px; padding:10px 14px; border-radius:9px; font-size:13px; margin-bottom:7px; border:1px solid #f0f0f0; }
        .opt.is-correct { background:#ecfdf5; border-color:#a7f3d0; color:#065f46; font-weight:600; }
        .opt.is-chosen-wrong { background:#fef2f2; border-color:#fecaca; color:#991b1b; }
        .opt-letter { width:22px; height:22px; flex-shrink:0; border-radius:50%; background:#f0f0f0; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; }
        .opt .tag { margin-left:auto; font-size:11px; font-weight:700; }
        .explain { margin-top:12px; padding:12px 16px; background:#fffbeb; border-radius:9px; font-size:13px; color:#92400e; line-height:1.55; }
        .explain i { margin-right:6px; }

        .actions { display:flex; gap:12px; margin-top:8px; }
        .btn { display:inline-flex; align-items:center; gap:8px; padding:12px 24px; border-radius:10px; font-size:14px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:white; color:#555; border:1px solid #e0e0e0; }
    </style>
</head>
<body>

@include('partials.sidebar', ['active' => 'quizzes'])

@php
    $score = (int) round($session->score_percent);
    $passing = $score >= 75;
    $circumference = 2 * pi() * 65;
    $filled = round($circumference * $score / 100, 1);
    $mins = intdiv((int) $session->duration_secs, 60);
    $secs = (int) $session->duration_secs % 60;
@endphp

<main class="main-content">
    <!-- SCORE SUMMARY -->
    <div class="score-card">
        <div class="score-ring">
            <svg viewBox="0 0 150 150">
                <circle cx="75" cy="75" r="65" fill="none" stroke="#f0e6e6" stroke-width="12"/>
                <circle cx="75" cy="75" r="65" fill="none" stroke="{{ $passing ? '#10b981' : '#c0392b' }}" stroke-width="12"
                        stroke-dasharray="{{ $filled }} {{ round($circumference, 1) }}" stroke-linecap="round"/>
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

    <div class="section-title">Review Your Answers</div>

    @foreach($questions as $i => $question)
        @php
            $answer = $answers[$question->id] ?? null;
            $selectedId = $answer->selected_choice ?? null;
            $isCorrect = $answer->is_correct ?? false;
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
                    $isAnswerKey = $choice->is_correct;
                    $isChosenWrong = $selectedId === $choice->id && ! $choice->is_correct;
                @endphp
                <div class="opt {{ $isAnswerKey ? 'is-correct' : ($isChosenWrong ? 'is-chosen-wrong' : '') }}">
                    <span class="opt-letter">{{ $choice->choice_label }}</span>
                    <span>{{ $choice->choice_text }}</span>
                    @if($isAnswerKey)<span class="tag" style="color:#059669;">Correct answer</span>
                    @elseif($isChosenWrong)<span class="tag" style="color:#991b1b;">Your answer</span>@endif
                </div>
            @endforeach
            @if($selectedId === null)
                <div style="font-size:12px;color:var(--gray);margin-top:8px;"><i class="fas fa-info-circle"></i> You skipped this question.</div>
            @endif
            @if($question->explanation)
                <div class="explain"><i class="fas fa-lightbulb"></i>{{ $question->explanation }}</div>
            @endif
        </div>
    @endforeach

    <div class="actions">
        <a href="{{ route('adaptive-quizzes') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back to Quizzes</a>
        <a href="{{ route('dashboard') }}" class="btn btn-primary"><i class="fas fa-home"></i> Go to Dashboard</a>
    </div>
</main>
</body>
</html>
