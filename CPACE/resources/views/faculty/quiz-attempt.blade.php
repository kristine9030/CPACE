<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $attempt->student?->name }} · {{ $quiz->title }} - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --accent:#c0392b; --green:#10b981; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:16px; position:relative; z-index:100; }
        .breadcrumb { display:flex; align-items:center; gap:6px; font-size:12px; color:#aaa; margin-bottom:4px; }
        .breadcrumb a { color:var(--accent); text-decoration:none; }
        .page-title { font-size:24px; font-weight:700; color:#14283E; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:1px solid #e0e0e0; text-decoration:none; background:#fff; color:#555; }
        .card { background:#fff; border-radius:14px; padding:20px 24px; margin-bottom:18px; }
        .card-head { font-size:14px; font-weight:700; color:#222; margin-bottom:6px; }
        /* Long lists scroll inside the card; the heading stays put. */
        .card.scroll-card { display:flex; flex-direction:column; max-height:640px; }
        .card.scroll-card .scroll-body { overflow-y:auto; min-height:0; padding-right:6px; margin-right:-6px; }
        .summary { display:flex; gap:32px; flex-wrap:wrap; }
        .summary b { display:block; font-size:28px; font-weight:700; color:#1a1a1a; line-height:1.1; }
        .summary span { font-size:11.5px; color:#999; }
        .banner { border-radius:12px; padding:14px 16px; font-size:13px; display:flex; gap:11px; align-items:flex-start; margin-bottom:18px; background:#fdeceb; border:1px solid #f5cdc9; color:#8d2b22; }
        .banner i { margin-top:2px; }
        .banner table { margin-top:8px; font-size:12px; border-collapse:collapse; }
        .banner td { padding:2px 18px 2px 0; }
        .split { display:grid; grid-template-columns:1fr 320px; gap:18px; align-items:start; }
        .shots { display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:12px; margin-top:14px; }
        .shot { border:1px solid #e3e5ea; border-radius:10px; overflow:hidden; background:#fff; }
        .shot.evt { border-color:var(--accent); }
        .shot-img { position:relative; }
        .shot-img img { width:100%; aspect-ratio:4/3; object-fit:cover; display:block; background:#101828; }
        .shot-img .pick { position:absolute; top:7px; left:7px; width:18px; height:18px; cursor:pointer; accent-color:var(--primary); }
        .shot-cap { padding:8px 10px; font-size:10.5px; color:#7a8296; }
        .shot-what { font-size:12px; font-weight:700; color:#14283E; line-height:1.3; }
        .shot.evt .shot-what { color:var(--accent); }
        .shot-meta { display:flex; justify-content:space-between; gap:6px; margin-top:3px; }
        .tl { position:relative; padding-left:18px; }
        .tl-row { position:relative; padding:9px 0; border-bottom:1px solid #f0f1f4; font-size:12.5px; }
        .tl-row:last-child { border-bottom:none; }
        .tl-row::before { content:''; position:absolute; left:-13px; top:15px; width:7px; height:7px; border-radius:50%; background:#c98a08; }
        .tl-row.severe::before { background:var(--accent); }
        .tl-when { font-size:11px; color:#7a8296; margin-top:2px; }
        .pick-bar { display:flex; align-items:center; gap:14px; margin-top:12px; padding:9px 12px; background:#f7f8fa; border:1px solid #e3e5ea; border-radius:10px; font-size:12.5px; }
        @media (max-width: 1000px) { .split { grid-template-columns:1fr; } }
        .good { color:#059669 !important; } .mid { color:#d97706 !important; } .bad { color:var(--accent) !important; }
    </style>
</head>
<body>

@include('partials.faculty-sidebar', ['active' => 'quizzes'])

@php
    $p = (float) $attempt->percent;
    $answers = (array) $attempt->answers;
    $answered = collect($quiz->items)->filter(fn ($i) => ($answers[(string) $i->id] ?? '') !== '')->count();
    $name = $attempt->student?->name ?? 'Unknown student';
@endphp

<main class="main">
    <div class="topbar">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('faculty.quizzes') }}">Class Quizzes</a>
                <i class="fas fa-chevron-right" style="font-size:9px;"></i>
                <a href="{{ route('faculty.quizzes.results', $quiz->id) }}">Results</a>
                <i class="fas fa-chevron-right" style="font-size:9px;"></i> {{ $name }}
            </div>
            <div class="page-title">{{ $name }}</div>
            <div class="page-sub">{{ $attempt->student?->email }} · {{ $quiz->title }}</div>
        </div>
        <div class="topbar-right">
            @include('partials.topbar-actions')
            <a href="{{ route('faculty.quizzes.results', $quiz->id) }}" class="btn"><i class="fas fa-arrow-left"></i> Back to results</a>
        </div>
    </div>

    <div class="card">
        <div class="summary">
            <div><b class="{{ $p >= 75 ? 'good' : ($p >= 50 ? 'mid' : 'bad') }}">{{ round($p, 1) }}%</b><span>Score</span></div>
            <div><b>{{ $attempt->score }} / {{ $attempt->total_points }}</b><span>Points</span></div>
            <div><b>{{ $answered }} / {{ $quiz->items->count() }}</b><span>Answered</span></div>
            <div><b style="font-size:16px;line-height:1.9;">{{ $attempt->submitted_at?->format('M j, g:i A') }}</b><span>Submitted</span></div>
        </div>
    </div>

    @if($quiz->monitor_enabled && $risk)
        @if($attempt->proctorEvents->contains('type', \App\Models\QuizProctorEvent::TYPE_FACE_CHECK_UNAVAILABLE))
            <div class="banner">
                <i class="fas fa-face-meh"></i>
                <div>
                    <strong>Face check did not run on this device.</strong>
                    The in-browser face detector could not load, so this sitting has no "no face", "more than one face" or "looking away" flags. A clean face record here means nothing; rely on the camera frames and the other flags.
                </div>
            </div>
        @endif
        @if($attempt->flag_count > 0)
            <div class="banner">
                <i class="fas fa-flag"></i>
                <div>
                    <strong>{{ $risk['label'] }} — {{ $attempt->flag_count }} {{ \Illuminate\Support\Str::plural('flag', $attempt->flag_count) }} raised during this sitting.</strong>
                    Flags are signals, not proof. Review the timeline and captures before drawing a conclusion.
                    <table>
                        @foreach($risk['rows'] as $row)
                            <tr><td>{{ $row['label'] }}</td><td>×{{ $row['count'] }}</td><td><strong>{{ $row['points'] }} pts</strong></td></tr>
                        @endforeach
                    </table>
                </div>
            </div>
        @endif
    @endif

    <div class="split">
        <div>
            <div class="card scroll-card">
                <div class="card-head">Answers</div>
                <div class="scroll-body">
                    @include('partials.answer-review', ['items' => $quiz->items, 'answers' => $answers])
                </div>
            </div>

            @if($quiz->monitor_enabled)
                <div class="card">
                    <div class="card-head"><i class="fas fa-images"></i> Captures ({{ $attempt->captures->count() }})</div>
                    <div style="font-size:12px;color:#7a8296;">
                        Red-bordered frames were taken because something was flagged. Recordings are deleted automatically
                        {{ \App\Models\QuizProctorCapture::RETENTION_DAYS }} days later.
                    </div>
                    <form method="POST" action="{{ route('class-quiz.captures.destroy', $attempt) }}"
                          onsubmit="return confirm('Delete the selected recordings? The flag timeline is kept, but the images cannot be recovered.');">
                        @csrf @method('DELETE')
                        @if($attempt->captures->isNotEmpty())
                            <div class="pick-bar">
                                <span>Tick recordings to delete them once you have decided the case.</span>
                                <button type="submit" class="btn" style="margin-left:auto;padding:6px 12px;font-size:12px;"><i class="fas fa-trash"></i> Delete selected</button>
                            </div>
                        @endif
                        <div class="shots">
                            @forelse($attempt->captures as $capture)
                                <div class="shot {{ $capture->isEventTriggered() ? 'evt' : '' }}">
                                    <div class="shot-img">
                                        <a href="{{ route('class-quiz.capture', $capture) }}" target="_blank" rel="noopener">
                                            <img src="{{ route('class-quiz.capture', $capture) }}" alt="{{ $capture->reasonLabel() }}" loading="lazy">
                                        </a>
                                        <input type="checkbox" class="pick" name="capture_ids[]" value="{{ $capture->id }}" aria-label="Select this recording">
                                    </div>
                                    <div class="shot-cap">
                                        <div class="shot-what">{{ $capture->reasonLabel() }}</div>
                                        <div class="shot-meta">
                                            <span>{{ $capture->kind === 'camera' ? 'Camera' : 'Screen' }}</span>
                                            <span>{{ $capture->captured_at?->format('g:i:s A') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div style="grid-column:1/-1;font-size:12.5px;color:#7a8296;">No captures are stored for this student.</div>
                            @endforelse
                        </div>
                    </form>
                </div>
            @endif
        </div>

        @if($quiz->monitor_enabled)
            <div class="card scroll-card">
                <div class="card-head"><i class="fas fa-flag"></i> Flag timeline</div>
                <div style="font-size:12px;color:#7a8296;margin-bottom:10px;">Everything the quiz page detected, in order.</div>
                <div class="scroll-body">
                <div class="tl">
                    @forelse($attempt->proctorEvents as $event)
                        <div class="tl-row {{ $event->isSevere() ? 'severe' : '' }}">
                            <div style="font-weight:600;color:#14283E;">{{ $event->label() }}</div>
                            @if($event->meta)<div style="font-size:11.5px;color:#7a8296;">{{ $event->meta }}</div>@endif
                            <div class="tl-when">{{ $event->occurred_at?->format('g:i:s A') }}</div>
                        </div>
                    @empty
                        <div style="font-size:12.5px;color:#7a8296;">No flags — clean sitting.</div>
                    @endforelse
                </div>
                </div>
            </div>
        @endif
    </div>
</main>

@include('partials.alerts')
</body>
</html>
