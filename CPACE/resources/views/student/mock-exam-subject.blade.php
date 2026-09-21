<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject->code }} Mock Exams - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.mock-exam-styles')
    <style>
        .exam-row { display:flex; gap:16px; align-items:center; padding:18px; border:1px solid var(--line);
                    border-radius:13px; background:#fff; margin-bottom:12px; flex-wrap:wrap; }
        .exam-when { min-width:150px; }
        .exam-when .d { font-size:15px; font-weight:700; color:var(--ink); font-family:'Montserrat',sans-serif; }
        .exam-when .t { font-size:12px; color:var(--muted); margin-top:2px; }
        .exam-main { flex:1; min-width:180px; }
        .countdown { font-family:'Montserrat',monospace; font-weight:700; color:var(--primary); font-size:13px; }
    </style>
</head>
<body>
<div class="dashboard-container">
    @include('partials.sidebar', ['active' => 'mock-exams'])
    @include('partials.student-bottom-nav', ['active' => 'mock-exams'])
    @include('partials.student-mobile-header')

    <main class="main-content">
        @include('partials.student-page-header', [
            'title' => $subject->code . ' — Mock Exams',
            'subtitle' => $subject->name,
            'back' => ['url' => route('mock-exams'), 'label' => 'All folders'],
        ])

        @error('exam')<div class="banner banner-danger"><i class="fas fa-triangle-exclamation"></i><div>{{ $message }}</div></div>@enderror

        @if($exams->isEmpty())
            <div class="card empty">
                <i class="fas fa-calendar-xmark"></i>
                <h3>Nothing scheduled for {{ $subject->code }}</h3>
                <p>Redeem a code for a day that includes this subject and it'll show up here.</p>
            </div>
        @else
            @foreach($exams as $exam)
                @php
                    $attempt = $attempts->get($exam->id);
                    $window = $exam->window();
                @endphp
                <div class="exam-row">
                    <div class="exam-when">
                        <div class="d">{{ $exam->scheduled_at?->format('M j') }}</div>
                        <div class="t">{{ $exam->scheduled_at?->format('Y · g:i A') }}</div>
                    </div>
                    <div class="exam-main">
                        <div style="font-weight:600;color:var(--ink);">{{ $exam->title }}</div>
                        <div style="font-size:12px;color:var(--muted);margin-top:3px;">
                            {{ $exam->total_items }} questions · {{ $exam->duration_minutes }} minutes
                        </div>
                        @if($window === 'upcoming')
                            <div class="countdown" data-starts="{{ $exam->scheduled_at?->toIso8601String() }}" style="margin-top:6px;">
                                Starts soon
                            </div>
                        @endif
                    </div>
                    <div style="display:flex;gap:10px;align-items:center;">
                        @if($attempt?->isSubmitted())
                            <span class="chip chip-published"><i class="fas fa-check"></i> Submitted</span>
                            <a class="btn btn-ghost btn-sm" href="{{ route('mock-exams.result', $exam) }}">View result</a>
                        @elseif($window === 'open')
                            <span class="chip chip-open">Open now</span>
                            <a class="btn btn-primary btn-sm" href="{{ route('mock-exams.show', $exam) }}">
                                {{ $attempt ? 'Resume' : 'Start' }} <i class="fas fa-arrow-right"></i>
                            </a>
                        @elseif($window === 'upcoming')
                            <span class="chip chip-upcoming"><i class="fas fa-lock"></i> Not yet open</span>
                            <a class="btn btn-ghost btn-sm" href="{{ route('mock-exams.show', $exam) }}">Details</a>
                        @else
                            <span class="chip chip-ended">Closed</span>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
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

    // Live countdown to each upcoming sitting.
    function tick() {
        document.querySelectorAll('.countdown[data-starts]').forEach(el => {
            const diff = new Date(el.dataset.starts) - new Date();
            if (diff <= 0) { el.textContent = 'Starting now — refresh'; return; }
            const d = Math.floor(diff / 86400000);
            const h = Math.floor(diff / 3600000) % 24;
            const m = Math.floor(diff / 60000) % 60;
            const s = Math.floor(diff / 1000) % 60;
            el.textContent = 'Opens in ' + (d > 0 ? d + 'd ' : '') + h + 'h ' + m + 'm ' + s + 's';
        });
    }
    tick();
    setInterval(tick, 1000);
</script>
</body>
</html>
