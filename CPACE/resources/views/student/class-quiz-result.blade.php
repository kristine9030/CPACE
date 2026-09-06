@php
    $pct = round((float) $attempt->percent, 1);
    $answers = $attempt->answers ?? [];
    $correctCount = $items->filter(fn ($it) => ($answers[$it->id] ?? null) === $it->correctLabel())->count();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result · {{ $quiz->title }} - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --accent:#c0392b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#1f2430; }
        .wrap { max-width:820px; margin:0 auto; padding:26px 16px 60px; }
        .hero { background:linear-gradient(135deg,#7B1D1D 0%,#a12626 100%); color:#fff; border-radius:22px; padding:28px 30px; display:flex; align-items:center; gap:26px; box-shadow:0 12px 40px rgba(123,29,29,.25); margin-bottom:18px; }
        .ring { width:110px; height:110px; border-radius:50%; background:conic-gradient(#fff {{ $pct }}%, rgba(255,255,255,.2) 0); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .ring div { width:88px; height:88px; border-radius:50%; background:#7B1D1D; display:flex; flex-direction:column; align-items:center; justify-content:center; }
        .ring b { font-size:24px; line-height:1; }
        .ring span { font-size:10px; opacity:.75; }
        .hero h1 { font-size:20px; font-weight:700; line-height:1.3; }
        .hero p { font-size:13px; opacity:.85; margin-top:6px; line-height:1.6; }
        .chips { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }
        .chip { background:rgba(255,255,255,.16); border-radius:20px; padding:4px 12px; font-size:11.5px; font-weight:600; }
        .btns { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:22px; }
        .btn { display:inline-flex; align-items:center; gap:8px; padding:11px 18px; border-radius:11px; font-size:13px; font-weight:600; text-decoration:none; font-family:'Poppins',sans-serif; border:none; cursor:pointer; }
        .btn-primary { background:var(--primary); color:#fff; }
        .btn-ghost { background:#fff; color:#444; border:1px solid #e3e3e3; }
        .hidden-note { background:#fff; border-radius:16px; padding:34px 20px; text-align:center; color:#888; font-size:13.5px; }
        .hidden-note i { font-size:32px; color:#e0d0d0; display:block; margin-bottom:10px; }

        .q { background:#fff; border-radius:16px; padding:20px 22px; margin-bottom:12px; border-left:5px solid #d1d5db; }
        .q.right { border-left-color:#10b981; }
        .q.wrong { border-left-color:var(--accent); }
        .q-head { display:flex; justify-content:space-between; font-size:11px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px; }
        .q-head .tag { padding:2px 9px; border-radius:10px; }
        .t-right { background:#d1fae5; color:#059669; } .t-wrong { background:#fde8e8; color:var(--accent); } .t-skip { background:#f3f4f6; color:#6b7280; }
        .q-text { font-size:14.5px; font-weight:500; line-height:1.6; color:#111; margin-bottom:12px; }
        .ch { display:flex; align-items:center; gap:11px; padding:9px 12px; border-radius:10px; font-size:13px; margin-bottom:6px; border:1.5px solid #f0f0f0; }
        .ch .l { width:26px; height:26px; border-radius:50%; background:#f3f4f6; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:#555; flex-shrink:0; }
        .ch.correct { border-color:#6ee7b7; background:#ecfdf5; } .ch.correct .l { background:#10b981; color:#fff; }
        .ch.picked-wrong { border-color:#fca5a5; background:#fef2f2; } .ch.picked-wrong .l { background:var(--accent); color:#fff; }
        .ch .mark { margin-left:auto; font-size:11px; font-weight:600; }
        .expl { margin-top:10px; background:#fffaf0; border:1px solid #fde9c6; border-radius:10px; padding:10px 13px; font-size:12.5px; color:#5b4a2a; line-height:1.6; }
        .expl b { color:#b8860b; }
        @media (max-width:560px) { .hero { flex-direction:column; text-align:center; } .chips { justify-content:center; } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <div class="ring"><div><b>{{ $pct }}%</b><span>score</span></div></div>
        <div>
            <h1>{{ $quiz->title }}</h1>
            <p>{{ $quiz->subject?->code ?? 'Class quiz' }} · {{ $quiz->faculty?->name }}<br>Submitted {{ $attempt->submitted_at->format('M j, Y · g:i A') }}</p>
            <div class="chips">
                <span class="chip"><i class="fas fa-star"></i> {{ $attempt->score }} / {{ $attempt->total_points }} points</span>
                <span class="chip"><i class="fas fa-check"></i> {{ $correctCount }} of {{ $items->count() }} correct</span>
            </div>
        </div>
    </div>

    <div class="btns">
        <a href="{{ route('class-quizzes') }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i> All class quizzes</a>
        <a href="{{ route('dashboard') }}" class="btn btn-ghost"><i class="fas fa-home"></i> Dashboard</a>
    </div>

    @if(! $quiz->show_results)
        <div class="hidden-note">
            <i class="fas fa-eye-slash"></i>
            Your instructor has chosen not to show the answer key for this quiz. Your score has been recorded.
        </div>
    @else
        @foreach($items as $i => $item)
            @php
                $picked = $answers[$item->id] ?? null;
                $correct = $item->correctLabel();
                $state = $picked === null ? 'skip' : ($picked === $correct ? 'right' : 'wrong');
            @endphp
            <div class="q {{ $state === 'skip' ? '' : $state }}">
                <div class="q-head">
                    <span>Question {{ $i + 1 }} · {{ $item->points }} pt{{ $item->points === 1 ? '' : 's' }}</span>
                    <span class="tag t-{{ $state }}">{{ ['right' => 'Correct', 'wrong' => 'Incorrect', 'skip' => 'Not answered'][$state] }}</span>
                </div>
                <div class="q-text">{{ $item->question_text }}</div>
                @foreach($item->choices as $choice)
                    @php $isC = $choice['label'] === $correct; $isP = $choice['label'] === $picked; @endphp
                    <div class="ch {{ $isC ? 'correct' : ($isP ? 'picked-wrong' : '') }}">
                        <span class="l">{{ $choice['label'] }}</span>
                        <span>{{ $choice['text'] }}</span>
                        @if($isC)<span class="mark" style="color:#059669;"><i class="fas fa-check"></i> Correct answer</span>
                        @elseif($isP)<span class="mark" style="color:var(--accent);"><i class="fas fa-times"></i> Your answer</span>@endif
                    </div>
                @endforeach
                @if($item->explanation)
                    <div class="expl"><b>Explanation:</b> {{ $item->explanation }}</div>
                @endif
            </div>
        @endforeach
    @endif
</div>
@include('partials.alerts')
</body>
</html>
