<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mock Exams - CPACE CPA Reviewer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.mock-exam-styles')
    <style>
        .redeem { display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; margin-top:14px; }
        .redeem .field { margin:0; flex:1; min-width:240px; }
        .redeem input { font-family:'Montserrat',monospace; letter-spacing:2px; text-transform:uppercase; font-weight:600; }
    </style>
</head>
<body>
<div class="dashboard-container">
    @include('partials.sidebar', ['active' => 'mock-exams'])
    @include('partials.student-bottom-nav', ['active' => 'mock-exams'])
    @include('partials.student-mobile-header')

    <main class="main-content">
        @include('partials.student-page-header', [
            'title' => 'Mock Exams',
            'subtitle' => 'Full-length, proctored exams scheduled by your faculty. Redeem the code they give you to unlock them.',
        ])

        @if($isAlumniLocked)
            <div class="banner banner-warn">
                <i class="fas fa-lock"></i>
                <div>Mock exams are not available on alumni accounts.</div>
            </div>
        @else
            <div class="card">
                <div class="card-title"><i class="fas fa-key"></i> Redeem an exam code</div>
                <div class="card-sub">
                    One code covers every subject scheduled for that exam day. You only need to redeem it once.
                </div>
                <form method="POST" action="{{ route('mock-exams.redeem') }}" class="redeem">
                    @csrf
                    <div class="field">
                        <label>Exam code</label>
                        <input type="text" name="access_code" placeholder="MOCK-20260921-K7Q4"
                               value="{{ old('access_code') }}" autocomplete="off" required>
                        @error('access_code')<div class="err">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn btn-primary" type="submit" style="margin-bottom:2px;">
                        <i class="fas fa-unlock"></i> Redeem
                    </button>
                </form>
            </div>

            @if(!$hasAny)
                <div class="card empty" style="margin-top:18px;">
                    <i class="fas fa-calendar-xmark"></i>
                    <h3>No mock exams yet</h3>
                    <p>Once you redeem a code, your exams appear here in folders by subject.</p>
                </div>
            @else
                <div style="margin:22px 0 14px;font-size:13px;font-weight:700;color:var(--ink);">Your exams</div>
                <div class="subject-grid">
                    @foreach($folders as $folder)
                        <a class="subject-card" href="{{ route('mock-exams.subject', $folder['subject']->id) }}"
                           style="--sc-base:{{ $folder['theme']['base'] }}; --sc-dark:{{ $folder['theme']['dark'] }};">
                            <div class="sc-banner">
                                <i class="fas fa-folder sc-illus"></i>
                                <div class="sc-code">{{ $folder['subject']->code }}</div>
                                <div class="sc-name">{{ $folder['subject']->name }}</div>
                            </div>
                            <div class="sc-icon"><i class="fas {{ $folder['icon'] }}"></i></div>
                            <div class="sc-body">
                                <div class="sc-stats">
                                    <span><strong>{{ $folder['total'] }}</strong> {{ Str::plural('exam', $folder['total']) }}</span>
                                    @if($folder['open'] > 0)<span style="color:var(--green);font-weight:700;">{{ $folder['open'] }} open now</span>@endif
                                    @if($folder['done'] > 0)<span>{{ $folder['done'] }} done</span>@endif
                                </div>
                                @if($folder['next'])
                                    <div style="margin-top:10px;font-size:12px;color:var(--muted);">
                                        <i class="fas fa-calendar-day"></i>
                                        {{ $folder['next']->scheduled_at?->format('M j, Y · g:i A') }}
                                    </div>
                                @endif
                            </div>
                            <div class="sc-foot">Open folder <i class="fas fa-arrow-right"></i></div>
                        </a>
                    @endforeach
                </div>
            @endif
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
</script>
</body>
</html>
