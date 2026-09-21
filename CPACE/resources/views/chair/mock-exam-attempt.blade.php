<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $attempt->student?->first_name }}'s sitting - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.mock-exam-styles')
    <style>
        .split { display:grid; grid-template-columns:1fr 340px; gap:18px; align-items:start; }
        .shots { display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:12px; }
        .shot { border:1px solid var(--line); border-radius:10px; overflow:hidden; background:#fff; }
        .shot.evt { border-color:var(--red); }
        .shot img { width:100%; aspect-ratio:4/3; object-fit:cover; display:block; background:#101828; }
        .shot-cap { padding:7px 9px; font-size:10.5px; color:var(--muted); display:flex; justify-content:space-between; gap:5px; }
        .tl { position:relative; padding-left:18px; }
        .tl-row { position:relative; padding:9px 0; border-bottom:1px solid #f0f1f4; font-size:12.5px; }
        .tl-row:last-child { border-bottom:none; }
        .tl-row::before { content:''; position:absolute; left:-13px; top:15px; width:7px; height:7px;
                          border-radius:50%; background:var(--amber); }
        .tl-row.severe::before { background:var(--red); }
        .tl-when { font-size:11px; color:var(--muted); margin-top:2px; }
        @media (max-width: 1000px) { .split { grid-template-columns:1fr; } }
    </style>
</head>
<body>
@if($isChair)
    @include('partials.chair-sidebar', ['active' => 'mock-exams'])
@else
    @include('partials.faculty-sidebar', ['active' => 'mock-exams'])
@endif

<main class="main">
    <div class="topbar">
        <div>
            <a href="{{ $isChair ? route('chair.mock-exams.monitor', $exam) : route('faculty.mock-exams.monitor', $exam) }}"
               style="font-size:12px;color:var(--muted);text-decoration:none;">
                <i class="fas fa-arrow-left"></i> Back to monitor
            </a>
            <div class="page-title" style="margin-top:4px;">{{ $attempt->student?->first_name }} {{ $attempt->student?->last_name }}</div>
            <div class="page-sub">
                {{ $exam->title }} ·
                started {{ $attempt->started_at?->format('g:i A') }}
                @if($attempt->submitted_at) · submitted {{ $attempt->submitted_at->format('g:i A') }} @endif
                @if($attempt->is_late) · <span class="chip chip-late">late</span> @endif
            </div>
        </div>
        <div class="topbar-right">@include('partials.topbar-actions')</div>
    </div>

    @if($attempt->flag_count > 0)
        <div class="banner banner-danger">
            <i class="fas fa-flag"></i>
            <div>
                <strong>{{ $attempt->flag_count }} {{ Str::plural('flag', $attempt->flag_count) }} raised during this sitting.</strong>
                Flags are signals, not proof — review the timeline and captures before drawing a conclusion.
            </div>
        </div>
    @endif

    <div class="split">
        <div>
            <div class="card">
                <div class="card-title"><i class="fas fa-chart-simple"></i> Result</div>
                <div style="display:flex;gap:28px;margin-top:14px;flex-wrap:wrap;">
                    <div>
                        <div style="font-size:30px;font-weight:700;font-family:'Montserrat',sans-serif;color:var(--ink);">
                            {{ $attempt->isSubmitted() ? number_format((float) $attempt->percent, 1) . '%' : '—' }}
                        </div>
                        <div style="font-size:11.5px;color:var(--muted);">Score</div>
                    </div>
                    <div>
                        <div style="font-size:30px;font-weight:700;font-family:'Montserrat',sans-serif;color:var(--ink);">
                            {{ $attempt->score }}/{{ $attempt->total_points ?: $items->count() }}
                        </div>
                        <div style="font-size:11.5px;color:var(--muted);">Points</div>
                    </div>
                    <div>
                        <div style="font-size:30px;font-weight:700;font-family:'Montserrat',sans-serif;color:var(--ink);">
                            {{ is_array($attempt->answers) ? count(array_filter($attempt->answers)) : 0 }}
                        </div>
                        <div style="font-size:11.5px;color:var(--muted);">Answered</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-title"><i class="fas fa-images"></i> Captures ({{ $attempt->captures->count() }})</div>
                <div class="card-sub">Red-bordered frames were taken because something was flagged.</div>
                <div class="shots" style="margin-top:14px;">
                    @forelse($attempt->captures as $capture)
                        <div class="shot {{ $capture->isEventTriggered() ? 'evt' : '' }}">
                            <img src="{{ route('mock-exams.capture', $capture) }}" alt="" loading="lazy">
                            <div class="shot-cap">
                                <span>{{ $capture->kind }}</span>
                                <span>{{ $capture->captured_at?->format('g:i:s A') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="empty" style="grid-column:1/-1;">
                            <i class="fas fa-camera-rotate"></i>
                            <h3>No captures</h3>
                            <p>Either the sitting hasn't started, or the student never granted camera/screen access.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-title"><i class="fas fa-flag"></i> Flag timeline</div>
            <div class="card-sub">Everything the runner detected, in order.</div>
            <div class="tl" style="margin-top:14px;max-height:560px;overflow-y:auto;">
                @forelse($attempt->proctorEvents as $event)
                    <div class="tl-row {{ $event->isSevere() ? 'severe' : '' }}">
                        <div style="font-weight:600;color:var(--ink);">{{ $event->label() }}</div>
                        @if($event->meta)<div style="font-size:11.5px;color:var(--muted);">{{ $event->meta }}</div>@endif
                        <div class="tl-when">{{ $event->occurred_at?->format('g:i:s A') }}</div>
                    </div>
                @empty
                    <div style="font-size:12.5px;color:var(--muted);">No flags — clean sitting.</div>
                @endforelse
            </div>
        </div>
    </div>
</main>

@include('partials.alerts')
</body>
</html>
