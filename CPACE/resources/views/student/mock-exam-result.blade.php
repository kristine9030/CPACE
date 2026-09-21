<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $exam->title }} — Result</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.mock-exam-styles')
    <style>
        .score-hero { display:flex; gap:32px; align-items:center; flex-wrap:wrap; }
        .ring { width:132px; height:132px; border-radius:50%; display:flex; flex-direction:column;
                align-items:center; justify-content:center; color:#fff; flex-shrink:0;
                background:conic-gradient(var(--ring-col) calc(var(--pct) * 1%), #e8eaee 0); position:relative; }
        .ring::after { content:''; position:absolute; inset:11px; background:#fff; border-radius:50%; }
        .ring-in { position:relative; z-index:2; text-align:center; }
        .ring-n { font-size:27px; font-weight:700; font-family:'Montserrat',sans-serif; color:var(--ink); }
        .ring-l { font-size:10.5px; color:var(--muted); text-transform:uppercase; letter-spacing:.5px; }
        .topic-bar { margin-bottom:12px; }
        .topic-head { display:flex; justify-content:space-between; font-size:12.5px; margin-bottom:5px; }
        .topic-track { height:8px; background:#eef0f4; border-radius:999px; overflow:hidden; }
        .topic-fill { height:100%; border-radius:999px; }
        .rev { padding:15px 17px; border:1px solid var(--line); border-radius:11px; margin-bottom:11px; }
        .rev.wrong { border-left:4px solid var(--red); }
        .rev.right { border-left:4px solid var(--green); }
        .rev-q { font-size:13.5px; line-height:1.6; color:var(--ink); }
        .rev-a { font-size:12.5px; margin-top:9px; }
        .rev-a .yours { color:var(--red); }
        .rev-a .key { color:var(--green); font-weight:600; }
        .rev-exp { font-size:12.5px; color:#555; margin-top:8px; padding:9px 11px; background:#f8f9fb; border-radius:8px; line-height:1.6; }
    </style>
</head>
<body>
<div class="dashboard-container">
    @include('partials.sidebar', ['active' => 'mock-exams'])
    @include('partials.student-bottom-nav', ['active' => 'mock-exams'])
    @include('partials.student-mobile-header')

    <main class="main-content">
        @include('partials.student-page-header', [
            'title' => $exam->title . ' — Result',
            'subtitle' => $subject->name . ' · submitted ' . $attempt->submitted_at?->format('M j, Y g:i A'),
            'back' => ['url' => route('mock-exams.subject', $exam->subject_id), 'label' => $subject->code . ' folder'],
        ])

        @php
            $pct = (float) $attempt->percent;
            $ringCol = $pct >= 75 ? 'var(--green)' : ($pct >= 50 ? 'var(--amber)' : 'var(--red)');
        @endphp

        <div class="card">
            <div class="score-hero">
                <div class="ring" style="--pct:{{ $pct }}; --ring-col:{{ $ringCol }};">
                    <div class="ring-in">
                        <div class="ring-n">{{ number_format($pct, 1) }}%</div>
                        <div class="ring-l">Score</div>
                    </div>
                </div>
                <div style="flex:1;min-width:210px;">
                    <div style="font-size:15px;font-weight:700;color:var(--ink);">
                        {{ $attempt->score }} out of {{ $attempt->total_points }} points
                    </div>
                    <div style="font-size:12.5px;color:var(--muted);margin-top:5px;">
                        {{ $items->count() }} questions ·
                        {{ $attempt->started_at?->diffForHumans($attempt->submitted_at, ['parts' => 2, 'short' => true, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]) }} taken
                    </div>
                    <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
                        @if($attempt->is_late)
                            <span class="chip chip-late"><i class="fas fa-hourglass-end"></i> Submitted late</span>
                        @endif
                        @if($attempt->flag_count > 0)
                            <span class="chip chip-flag"><i class="fas fa-flag"></i> {{ $attempt->flag_count }} {{ Str::plural('flag', $attempt->flag_count) }} recorded</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-title"><i class="fas fa-chart-simple"></i> How you did by topic</div>
            <div class="card-sub">Weakest first — these are the areas worth reviewing.</div>
            <div style="margin-top:16px;">
                @foreach($byTopic as $row)
                    @php $col = $row['percent'] >= 75 ? 'var(--green)' : ($row['percent'] >= 50 ? 'var(--amber)' : 'var(--red)'); @endphp
                    <div class="topic-bar">
                        <div class="topic-head">
                            <span>{{ $row['topic'] }}</span>
                            <span style="color:{{ $col }};font-weight:700;">{{ $row['correct'] }}/{{ $row['total'] }} · {{ $row['percent'] }}%</span>
                        </div>
                        <div class="topic-track">
                            <div class="topic-fill" style="width:{{ $row['percent'] }}%;background:{{ $col }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="banner banner-info" style="margin-top:16px;">
                <i class="fas fa-lightbulb"></i>
                <div>
                    These results feed your weak-area detection and review schedule, so your
                    <a href="{{ route('calendar') }}">calendar</a> and adaptive quizzes will now target what you missed here.
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-title"><i class="fas fa-list-check"></i> Review your answers</div>
            <div style="margin-top:16px;">
                @foreach($items as $i => $item)
                    @php
                        $picked = $answers[(string) $item->id] ?? null;
                        $correct = $item->isCorrect($picked);
                        $key = $item->correctLabel();
                    @endphp
                    <div class="rev {{ $correct ? 'right' : 'wrong' }}">
                        <div class="rev-q"><strong>{{ $i + 1 }}.</strong> {{ $item->question_text }}</div>
                        <div class="rev-a">
                            @if($correct)
                                <span class="key"><i class="fas fa-check"></i> Correct — {{ $picked }}</span>
                            @else
                                <span class="yours">
                                    <i class="fas fa-xmark"></i>
                                    {{ $picked ? 'You answered ' . $picked : 'Not answered' }}
                                </span>
                                · <span class="key">Correct answer: {{ $key }}</span>
                            @endif
                        </div>
                        @if($item->explanation)
                            <div class="rev-exp"><strong>Why:</strong> {{ $item->explanation }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </main>
</div>

@include('partials.global-search')
@include('partials.alerts')
<script>
    const profileBtn = document.getElementById('profileBtn');
    const profileDrop = document.getElementById('profileDropdown');
    if (profileBtn && profileDrop) {
        profileBtn.addEventListener('click', e => { e.stopPropagation(); profileDrop.classList.toggle('active'); });
        document.addEventListener('click', () => profileDrop.classList.remove('active'));
        profileDrop.addEventListener('click', e => e.stopPropagation());
    }
</script>
</body>
</html>
