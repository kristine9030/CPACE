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
        .page-title { font-size:24px; font-weight:600; color:#14283E; line-height:1.25; }
        .page-sub { font-size:12.5px; font-weight:400; color:#6b7280; margin-top:3px; }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:1px solid #e0e0e0; text-decoration:none; background:#fff; color:#555; }
        .card { background:#fff; border-radius:14px; padding:20px 24px; margin-bottom:18px; }
        .card-head { font-size:14px; font-weight:700; color:#222; margin-bottom:6px; }
        /* Long lists scroll inside the card; the heading stays put. */
        .card.scroll-card { display:flex; flex-direction:column; max-height:640px; }
        .card.scroll-card .scroll-body { overflow-y:auto; min-height:0; padding-right:6px; margin-right:-6px; }
        /* Result summary: four evenly spaced figures, label above value */
        .card.summary-card { padding:6px 8px; }
        .summary { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); }
        .sum-item { padding:16px 22px; min-width:0; }
        .sum-item + .sum-item { border-left:1px solid #f0f1f4; }
        .sum-label { font-size:11px; font-weight:600; letter-spacing:.5px; text-transform:uppercase; color:#9ca3af; margin-bottom:6px; }
        .sum-value { font-size:26px; font-weight:600; color:#1f2937; line-height:1.15; font-variant-numeric:tabular-nums; }
        .sum-value small { font-size:15px; font-weight:500; color:#9ca3af; }
        .sum-value.is-date { font-size:17px; line-height:1.5; }
        .sum-note { font-size:12px; font-weight:400; color:#9ca3af; margin-top:3px; }
        .sum-bar { height:5px; border-radius:4px; background:#eef0f3; margin-top:9px; overflow:hidden; }
        .sum-bar span { display:block; height:100%; border-radius:4px; background:currentColor; }
        @media (max-width:760px) {
            .summary { grid-template-columns:repeat(2, minmax(0, 1fr)); }
            .sum-item:nth-child(3) { border-left:0; }
            .sum-item:nth-child(n+3) { border-top:1px solid #f0f1f4; }
        }

        /* Proctoring result: a slim bar, not a full-width alert */
        .risk-bar { display:flex; align-items:center; gap:12px; flex-wrap:wrap; background:#fff; border:1px solid #eceef2; border-left:4px solid var(--rk, #d97706); border-radius:12px; padding:12px 16px; margin-bottom:18px; font-size:13px; color:#374151; }
        .risk-bar.risk-high { --rk:#c0392b; } .risk-bar.risk-medium { --rk:#d97706; } .risk-bar.risk-low { --rk:#6b7280; }
        .risk-ico { width:30px; height:30px; border-radius:50%; background:color-mix(in srgb, var(--rk) 12%, #fff); color:var(--rk); display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0; }
        .risk-level { font-size:11px; font-weight:700; letter-spacing:.4px; text-transform:uppercase; color:var(--rk); background:color-mix(in srgb, var(--rk) 10%, #fff); padding:3px 10px; border-radius:20px; }
        .risk-text { font-weight:500; color:#374151; }
        .risk-text b { font-weight:600; color:#1f2937; }
        .split { display:grid; grid-template-columns:minmax(0, 1fr) 320px; gap:18px; align-items:start; }
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
        @media (max-width: 1000px) { .split { grid-template-columns:minmax(0, 1fr); } }
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

    @php
        $scoreTone = $p >= 75 ? 'good' : ($p >= 50 ? 'mid' : 'bad');
        $scoreColor = ['good' => '#059669', 'mid' => '#d97706', 'bad' => '#c0392b'][$scoreTone];
    @endphp
    <div class="card summary-card">
        <div class="summary">
            <div class="sum-item">
                <div class="sum-label">Score</div>
                <div class="sum-value" style="color:{{ $scoreColor }};">{{ round($p, 1) }}<small>%</small></div>
                <div class="sum-bar" style="color:{{ $scoreColor }};"><span style="width:{{ max(0, min(100, $p)) }}%"></span></div>
            </div>
            <div class="sum-item">
                <div class="sum-label">Points</div>
                <div class="sum-value">{{ $attempt->score }}<small> / {{ $attempt->total_points }}</small></div>
                <div class="sum-note">earned</div>
            </div>
            <div class="sum-item">
                <div class="sum-label">Answered</div>
                <div class="sum-value">{{ $answered }}<small> / {{ $quiz->items->count() }}</small></div>
                <div class="sum-note">{{ $answered === $quiz->items->count() ? 'every question' : ($quiz->items->count() - $answered) . ' left blank' }}</div>
            </div>
            <div class="sum-item">
                <div class="sum-label">Submitted</div>
                <div class="sum-value is-date">{{ $attempt->submitted_at?->format('M j, Y') }}</div>
                <div class="sum-note">{{ $attempt->submitted_at?->format('g:i A') }}</div>
            </div>
        </div>
    </div>

    @if($quiz->monitor_enabled && $risk)
        @if($attempt->proctorEvents->contains('type', \App\Models\QuizProctorEvent::TYPE_FACE_CHECK_UNAVAILABLE))
            <x-hint tone="warn" icon="fa-face-meh">
                <x-slot:title><strong>Face check did not run on this device.</strong></x-slot:title>
                The face detector couldn't load, so there are no "no face", "more than one face" or "looking away" flags.
                A clean face record means nothing here — rely on the camera frames and other flags.
            </x-hint>
        @endif
        @if($attempt->flag_count > 0)
            <div class="risk-bar risk-{{ $risk['level'] }}">
                <span class="risk-ico"><i class="fas fa-flag"></i></span>
                <span class="risk-level">{{ $risk['label'] }}</span>
                <span class="risk-text"><b>{{ $attempt->flag_count }}</b> {{ \Illuminate\Support\Str::plural('flag', $attempt->flag_count) }} raised during this sitting</span>
                <x-tip label="Risk breakdown">
                        <table>
                            @foreach($risk['rows'] as $row)
                                <tr><td>{{ $row['label'] }}</td><td>×{{ $row['count'] }}</td><td><strong>{{ $row['points'] }} pts</strong></td></tr>
                            @endforeach
                            <tr class="tip-total"><td colspan="2">Weighted score (Medium from {{ \App\Support\ProctorRisk::MEDIUM_FROM }}, High from {{ \App\Support\ProctorRisk::HIGH_FROM }})</td><td><strong>{{ $risk['score'] }}</strong></td></tr>
                        </table>
                        <span class="tip-note">Flags are signals, not proof — review the timeline and captures before drawing a conclusion.</span>
                    </x-tip>
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
