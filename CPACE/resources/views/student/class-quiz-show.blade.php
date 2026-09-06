@php
    $avail = $quiz->availability();
    $done = $attempt && $attempt->isSubmitted();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $quiz->title }} - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:linear-gradient(160deg,#f7f2f2 0%,#f4f5f7 60%); color:#1f2430; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
        .card { background:#fff; border-radius:22px; width:100%; max-width:560px; box-shadow:0 20px 60px rgba(60,10,10,.12); overflow:hidden; }
        .head { background:linear-gradient(135deg,#7B1D1D 0%,#a12626 100%); color:#fff; padding:28px 30px 24px; }
        .kicker { font-size:10.5px; letter-spacing:1.4px; text-transform:uppercase; opacity:.75; font-weight:600; margin-bottom:8px; }
        .title { font-size:22px; font-weight:700; line-height:1.3; }
        .by { font-size:12.5px; opacity:.8; margin-top:6px; }
        .body { padding:24px 30px 28px; }
        .facts { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; margin-bottom:20px; }
        .fact { background:#f8f8f8; border-radius:12px; padding:12px 14px; }
        .fact i { color:#7B1D1D; margin-right:7px; }
        .fact b { display:block; font-size:14px; color:#1a1a1a; margin-top:2px; }
        .fact span { font-size:11px; color:#999; }
        .instr { background:#fffaf0; border:1px solid #fde9c6; border-radius:12px; padding:14px 16px; font-size:13px; color:#5b4a2a; line-height:1.65; margin-bottom:20px; white-space:pre-line; }
        .instr strong { display:block; font-size:11px; text-transform:uppercase; letter-spacing:.8px; color:#b8860b; margin-bottom:5px; }
        .state { display:flex; gap:10px; align-items:flex-start; border-radius:12px; padding:13px 16px; font-size:13px; line-height:1.55; margin-bottom:18px; }
        .state i { margin-top:2px; }
        .s-open { background:#ecfdf5; color:#065f46; }
        .s-warn { background:#fef3c7; color:#92400e; }
        .s-off  { background:#f3f4f6; color:#4b5563; }
        .s-done { background:#dbeafe; color:#1e40af; }
        .btn { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:14px; border-radius:12px; border:none; font-size:14px; font-weight:700; font-family:'Poppins',sans-serif; cursor:pointer; text-decoration:none; transition:all .2s; }
        .btn-primary { background:#7B1D1D; color:#fff; }
        .btn-primary:hover { background:#6a1818; }
        .btn-ghost { background:#f3f4f6; color:#444; margin-top:10px; }
        .btn-ghost:hover { background:#e5e7eb; }
        .score-big { text-align:center; padding:8px 0 14px; }
        .score-big b { font-size:40px; color:#7B1D1D; line-height:1; }
        .score-big span { display:block; font-size:12px; color:#999; margin-top:6px; }
        @media (max-width:480px) { .facts { grid-template-columns:1fr; } .head, .body { padding-left:20px; padding-right:20px; } }
    </style>
</head>
<body>
<div class="card">
    <div class="head">
        <div class="kicker">{{ $isOwner ? 'Preview · Class Quiz' : 'Class Quiz' }}{{ $quiz->subject ? ' · ' . $quiz->subject->code : '' }}</div>
        <div class="title">{{ $quiz->title }}</div>
        <div class="by"><i class="fas fa-chalkboard-user"></i> {{ $quiz->faculty?->name }}</div>
    </div>
    <div class="body">
        <div class="facts">
            <div class="fact"><i class="fas fa-list-ol"></i><span>Questions</span><b>{{ $quiz->items_count }}</b></div>
            <div class="fact"><i class="fas fa-stopwatch"></i><span>Time limit</span><b>{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' minutes' : 'None' }}</b></div>
            <div class="fact"><i class="fas fa-calendar-day"></i><span>Deadline</span><b>{{ $quiz->due_at ? $quiz->due_at->format('M j, Y · g:i A') : 'None' }}</b></div>
            <div class="fact"><i class="fas fa-door-open"></i><span>Opens</span><b>{{ $quiz->opens_at ? $quiz->opens_at->format('M j, Y · g:i A') : 'Now' }}</b></div>
        </div>

        @if($quiz->instructions)
            <div class="instr"><strong>Instructions</strong>{{ $quiz->instructions }}</div>
        @endif

        @if($isOwner)
            <div class="state s-warn"><i class="fas fa-eye"></i><div>You're viewing this as the quiz owner. This is exactly what students see when they open your link{{ $quiz->isDraft() ? ' — but the quiz is still a draft, so only you can see it right now' : '' }}.</div></div>
            <a href="{{ route('faculty.quizzes.edit', $quiz->id) }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back to editor</a>
        @elseif(! $canTake)
            <div class="state s-off"><i class="fas fa-circle-info"></i><div>Only student accounts can take class quizzes.</div></div>
        @elseif($done)
            <div class="score-big"><b>{{ round((float) $attempt->percent, 1) }}%</b><span>{{ $attempt->score }} of {{ $attempt->total_points }} points · submitted {{ $attempt->submitted_at->format('M j, g:i A') }}</span></div>
            <a href="{{ route('class-quiz.result', $quiz->share_token) }}" class="btn btn-primary"><i class="fas fa-eye"></i> View my result</a>
            <a href="{{ route('class-quizzes') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> All class quizzes</a>
        @elseif($avail === 'open')
            @if($attempt)
                <div class="state s-warn"><i class="fas fa-hourglass-half"></i><div>You already started this quiz{{ $quiz->time_limit_minutes ? ' — your timer is still running' : '' }}. Continue where you left off.</div></div>
            @else
                <div class="state s-open"><i class="fas fa-circle-check"></i><div>This quiz is open. You get <strong>one attempt</strong>{{ $quiz->time_limit_minutes ? ', and the timer starts as soon as you click Start' : '' }}.</div></div>
            @endif
            <form method="POST" action="{{ route('class-quiz.start', $quiz->share_token) }}"
                  @unless($attempt)
                  data-confirm="{{ $quiz->time_limit_minutes ? 'Your ' . $quiz->time_limit_minutes . '-minute timer starts now and cannot be paused.' : 'You get one attempt at this quiz.' }}"
                  data-confirm-title="Start the quiz?" data-confirm-ok="Yes, start" data-confirm-icon="question"
                  @endunless>
                @csrf
                <button class="btn btn-primary"><i class="fas fa-play"></i> {{ $attempt ? 'Continue quiz' : 'Start quiz' }}</button>
            </form>
            <a href="{{ route('class-quizzes') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> All class quizzes</a>
        @else
            <div class="state s-off"><i class="fas fa-lock"></i><div>
                @if($avail === 'upcoming') This quiz opens on <strong>{{ $quiz->opens_at->format('M j, Y g:i A') }}</strong>. Come back then.
                @elseif($avail === 'expired') The deadline for this quiz has passed.
                @else This quiz has been closed by your instructor.
                @endif
            </div></div>
            <a href="{{ route('class-quizzes') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> All class quizzes</a>
        @endif
    </div>
</div>
@include('partials.alerts')
</body>
</html>
