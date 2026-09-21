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

        /* Alumni lock screen — fills the centre instead of a one-line banner. */
        .lock-hero {
            display:flex; flex-direction:column; align-items:center; text-align:center;
            padding:64px 32px 56px; margin-top:6px; border-radius:20px; position:relative; overflow:hidden;
            background:radial-gradient(circle at 50% 0%, #fff 0%, #f7f4ee 60%, #f4f5f7 100%);
            border:1px solid var(--line);
            box-shadow:0 2px 6px rgba(15,10,10,.08), 0 16px 34px -16px rgba(15,10,10,.22);
            min-height:calc(100vh - 260px);
            justify-content:center;
        }
        .lock-hero::before, .lock-hero::after {
            content:''; position:absolute; border-radius:50%; opacity:.5; pointer-events:none;
        }
        .lock-hero::before { width:340px; height:340px; background:radial-gradient(circle, var(--primary-light) 0%, transparent 70%); top:-140px; right:-100px; }
        .lock-hero::after { width:280px; height:280px; background:radial-gradient(circle, #fdf0d8 0%, transparent 70%); bottom:-120px; left:-90px; }
        .lock-badge {
            position:relative; width:88px; height:88px; border-radius:26px; margin-bottom:22px;
            background:linear-gradient(135deg, var(--primary) 0%, #4a1212 100%);
            display:flex; align-items:center; justify-content:center; font-size:34px; color:#fff;
            box-shadow:0 14px 30px -10px rgba(123,29,29,.5);
        }
        .lock-badge i { filter:drop-shadow(0 2px 3px rgba(0,0,0,.2)); }
        .lock-hero h2 { position:relative; font-size:23px; font-weight:700; color:var(--ink); margin-bottom:10px; }
        .lock-hero p { position:relative; font-size:13.5px; color:var(--muted); max-width:460px; line-height:1.6; margin-bottom:6px; }
        .lock-chip {
            position:relative; display:inline-flex; align-items:center; gap:7px; margin:14px 0 28px;
            background:#fdf0d8; color:var(--amber); border:1px solid #f0dfb5;
            padding:7px 16px; border-radius:999px; font-size:12px; font-weight:700;
        }
        .lock-actions { position:relative; display:flex; gap:12px; flex-wrap:wrap; justify-content:center; margin-bottom:34px; }
        .lock-actions .btn { padding:11px 22px; }
        .lock-features { position:relative; display:flex; gap:16px; flex-wrap:wrap; justify-content:center; max-width:720px; }
        .lock-feature {
            display:flex; align-items:center; gap:11px; background:#fff; border:1px solid var(--line); border-radius:13px;
            padding:13px 18px; min-width:190px; box-shadow:0 6px 16px -10px rgba(15,10,10,.2);
            text-decoration:none; color:inherit; transition:transform .18s ease, box-shadow .18s ease;
        }
        .lock-feature:hover { transform:translateY(-2px); box-shadow:0 12px 22px -10px rgba(15,10,10,.3); }
        .lock-feature .lf-icon {
            width:38px; height:38px; border-radius:10px; flex-shrink:0;
            display:flex; align-items:center; justify-content:center; font-size:15px;
        }
        .lock-feature .lf-text { text-align:left; }
        .lock-feature .lf-title { font-size:12.5px; font-weight:700; color:var(--ink); }
        .lock-feature .lf-sub { font-size:11px; color:var(--muted); margin-top:1px; }
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
            <div class="lock-hero">
                <div class="lock-badge"><i class="fas fa-lock"></i></div>
                <h2>Mock exams are for enrolled students</h2>
                <p>
                    Proctored, full-length simulations are built and scheduled by faculty for the current
                    class — they close once you graduate to an alumni account.
                </p>
                <div class="lock-chip"><i class="fas fa-graduation-cap"></i> Alumni account</div>

                <div class="lock-actions">
                    <a href="{{ route('adaptive-quizzes') }}" class="btn btn-primary"><i class="fas fa-pen-fancy"></i> Practice Quizzes</a>
                    <a href="{{ route('community.index') }}" class="btn btn-ghost"><i class="fas fa-people-group"></i> Alumni Community</a>
                </div>

                <div class="lock-features">
                    <a href="{{ route('adaptive-quizzes') }}" class="lock-feature">
                        <div class="lf-icon" style="background:#fde8e8;color:var(--red);"><i class="fas fa-pen-fancy"></i></div>
                        <div class="lf-text"><div class="lf-title">Practice Quizzes</div><div class="lf-sub">Sharpen weak topics anytime</div></div>
                    </a>
                    <a href="{{ route('review-notes') }}" class="lock-feature">
                        <div class="lf-icon" style="background:#d1fae5;color:var(--green);"><i class="fas fa-sticky-note"></i></div>
                        <div class="lf-text"><div class="lf-title">Review Notes</div><div class="lf-sub">Your notes, still yours</div></div>
                    </a>
                    <a href="{{ route('community.index') }}" class="lock-feature">
                        <div class="lf-icon" style="background:#dbeafe;color:var(--blue);"><i class="fas fa-people-group"></i></div>
                        <div class="lf-text"><div class="lf-title">Alumni Community</div><div class="lf-sub">Share tips and reviewers</div></div>
                    </a>
                </div>
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
