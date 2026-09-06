<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Quizzes - CPACE CPA Reviewer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        :root { --maroon:#7B1D1D; --accent:#c0392b; --ink:#1f2430; --muted:#6b7280; --line:#e8eaef; --bg:#f4f5f7; }
        body { font-family:'Poppins',sans-serif; background:var(--bg); color:var(--ink); }
        .dashboard-container { display:block; min-height:100vh; }

        .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:26px; gap:20px; }
        .header-title { font-size:26px; font-weight:700; color:#1a1a1a; }
        .header-subtitle { font-size:13px; color:#888; margin-top:3px; }

        .grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:16px; }
        .qz { background:#fff; border-radius:16px; padding:20px 22px; box-shadow:0 2px 10px rgba(0,0,0,.04); display:flex; flex-direction:column; gap:12px; border-top:4px solid var(--maroon); }
        .qz.done { border-top-color:#10b981; }
        .qz.off { border-top-color:#d1d5db; opacity:.85; }
        .qz-top { display:flex; justify-content:space-between; gap:10px; align-items:flex-start; }
        .qz-title { font-size:15px; font-weight:700; color:#1a1a1a; line-height:1.35; }
        .qz-sub { font-size:11.5px; color:#999; margin-top:3px; }
        .pill { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; white-space:nowrap; flex-shrink:0; }
        .p-open { background:#d1fae5; color:#059669; }
        .p-done { background:#dbeafe; color:#2563eb; }
        .p-soon { background:#fef3c7; color:#d97706; }
        .p-off { background:#f3f4f6; color:#6b7280; }
        .qz-meta { display:flex; flex-wrap:wrap; gap:12px; font-size:12px; color:#777; }
        .qz-meta i { color:#bbb; margin-right:5px; }
        .qz-meta b { color:#333; }
        .score { background:#f8f8f8; border-radius:10px; padding:10px 12px; display:flex; justify-content:space-between; align-items:center; font-size:12.5px; }
        .score b { font-size:18px; color:var(--maroon); }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:10px 16px; border-radius:9px; font-size:13px; font-weight:600; text-decoration:none; border:none; cursor:pointer; font-family:'Poppins',sans-serif; margin-top:auto; transition:all .2s; }
        .btn-primary { background:var(--maroon); color:#fff; }
        .btn-primary:hover { background:#6a1818; }
        .btn-ghost { background:#f3f4f6; color:#444; }
        .btn-ghost:hover { background:#e5e7eb; }
        .empty { background:#fff; border-radius:16px; padding:60px 20px; text-align:center; color:#aaa; font-size:13.5px; }
        .empty i { font-size:40px; color:#e6d5d5; display:block; margin-bottom:12px; }
    </style>
</head>
<body>
<div class="dashboard-container">
    @include('partials.sidebar', ['active' => 'class-quizzes'])
    @include('partials.student-bottom-nav', ['active' => 'quizzes'])
    @include('partials.student-mobile-header')

    <main class="main-content">
        <div class="header">
            <div>
                <div class="header-title">Class Quizzes</div>
                <div class="header-subtitle">Quizzes assigned by your instructors. Finish them before the deadline.</div>
            </div>
        </div>

        @if($quizzes->isEmpty())
            <div class="empty">
                <i class="fas fa-clipboard-list"></i>
                No class quizzes have been posted yet. When your instructor publishes one, it will show up here.
            </div>
        @else
            <div class="grid">
                @foreach($quizzes as $quiz)
                    @php
                        $attempt = $attempts->get($quiz->id);
                        $done = $attempt && $attempt->isSubmitted();
                        $avail = $quiz->availability();
                        $state = $done ? 'done' : ($avail === 'open' ? 'open' : ($avail === 'upcoming' ? 'soon' : 'off'));
                        $pill = ['done' => ['p-done', 'fa-check', 'Completed'], 'open' => ['p-open', 'fa-circle-dot', $attempt ? 'In progress' : 'Open'], 'soon' => ['p-soon', 'fa-clock', 'Opens soon'], 'off' => ['p-off', 'fa-lock', $avail === 'expired' ? 'Deadline passed' : 'Closed']][$state];
                    @endphp
                    <div class="qz {{ $state === 'done' ? 'done' : ($state === 'open' ? '' : 'off') }}">
                        <div class="qz-top">
                            <div>
                                <div class="qz-title">{{ $quiz->title }}</div>
                                <div class="qz-sub">{{ $quiz->subject?->code ?? 'General' }} · {{ $quiz->faculty?->name }}</div>
                            </div>
                            <span class="pill {{ $pill[0] }}"><i class="fas {{ $pill[1] }}"></i> {{ $pill[2] }}</span>
                        </div>
                        <div class="qz-meta">
                            <span><i class="fas fa-list-ol"></i><b>{{ $quiz->items_count }}</b> items</span>
                            @if($quiz->time_limit_minutes)<span><i class="fas fa-stopwatch"></i><b>{{ $quiz->time_limit_minutes }}</b> min</span>@endif
                            <span><i class="fas fa-calendar-day"></i>{!! $quiz->due_at ? 'Due <b>' . e($quiz->due_at->format('M j, g:i A')) . '</b>' : 'No deadline' !!}</span>
                        </div>

                        @if($done)
                            <div class="score">
                                <span>Your score</span>
                                <span><b>{{ $attempt->score }}</b> / {{ $attempt->total_points }} &nbsp;·&nbsp; {{ round((float) $attempt->percent, 1) }}%</span>
                            </div>
                            <a href="{{ route('class-quiz.result', $quiz->share_token) }}" class="btn btn-ghost"><i class="fas fa-eye"></i> View result</a>
                        @elseif($state === 'open')
                            <a href="{{ route('class-quiz.show', $quiz->share_token) }}" class="btn btn-primary"><i class="fas {{ $attempt ? 'fa-play' : 'fa-arrow-right' }}"></i> {{ $attempt ? 'Continue quiz' : 'Open quiz' }}</a>
                        @else
                            <a href="{{ route('class-quiz.show', $quiz->share_token) }}" class="btn btn-ghost"><i class="fas fa-circle-info"></i> Details</a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </main>
</div>
@include('partials.alerts')
</body>
</html>
