<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Quizzes - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --line:#e3e5ea; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }

        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:16px; position:relative; z-index:100; }
        .page-title { font-size:26px; font-weight:700; color:#14283E; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; white-space:nowrap; }
        .btn-primary { background:var(--primary); color:#fff; }
        .btn-primary:hover { background:var(--primary-hover); }

        /* ── Subject class cards — same Classroom-style tile the student
           side uses on Class Quizzes, so a subject reads as the same card
           whichever side of CPACE you're on. Faculty see their own quiz
           counts by status instead of a to-do count. ── */
        .classes { display:grid; grid-template-columns:repeat(3, 1fr); gap:22px; }

        .class-card {
            display:flex; flex-direction:column;
            background:#fff; border:1px solid var(--line); border-radius:16px;
            overflow:hidden; text-decoration:none; color:inherit;
            box-shadow:0 1px 3px rgba(0,0,0,.05);
            transition:box-shadow .2s ease, transform .2s ease;
            min-height:210px;
        }
        .class-card:hover { box-shadow:0 12px 28px -10px rgba(20,20,30,.22); transform:translateY(-3px); }
        .class-card:focus-visible { outline:3px solid var(--cc-base); outline-offset:2px; }

        .cc-banner { position:relative; padding:22px 24px 34px; color:#fff; min-height:88px; overflow:hidden;
                     background:linear-gradient(135deg, var(--cc-base) 0%, var(--cc-dark) 100%);
                     box-shadow:0 10px 18px -8px rgba(0,0,0,.45), inset 0 -22px 26px -18px rgba(0,0,0,.4); }
        .cc-banner::before { content:''; position:absolute; inset:0;
                             background:linear-gradient(115deg, rgba(255,255,255,.18) 0%, rgba(255,255,255,0) 55%); }
        .cc-code, .cc-name { text-shadow:0 1px 4px rgba(0,0,0,.25); }
        .cc-banner-illus { position:absolute; right:-8px; top:-10px; pointer-events:none; }
        .cc-banner-illus i { position:absolute; color:#fff; }
        .cc-banner-illus .i1 { font-size:64px; opacity:.14; right:6px; top:2px; }
        .cc-banner-illus .i2 { font-size:34px; opacity:.22; right:52px; top:44px; }
        .cc-code { position:relative; font-size:20px; font-weight:700; line-height:1.25; letter-spacing:.2px; }
        .cc-name { position:relative; font-size:12.5px; opacity:.92; margin-top:4px; line-height:1.4; padding-right:20px;
                   display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }

        .cc-icon {
            position:relative; z-index:2; width:52px; height:52px; border-radius:15px; margin:-26px 0 0 24px;
            background:#fff; color:var(--cc-base); display:flex; align-items:center; justify-content:center;
            font-size:21px; box-shadow:0 4px 10px -2px rgba(0,0,0,.2); border:3px solid #fff;
        }

        .cc-body { flex:1; display:flex; flex-direction:column; padding:12px 24px 16px; }
        .cc-sub { font-size:12px; color:#70757a; margin-top:2px; }

        .cc-status { font-size:12px; color:#70757a; margin-top:auto; padding-top:14px; display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
        .cc-status .todo-badge { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:700;
                      padding:4px 11px; border-radius:20px; background:var(--cc-pastel); color:var(--cc-dark); }
        .cc-status .done-note { display:inline-flex; align-items:center; gap:6px; }
        .cc-status .done-note i { color:#10b981; }
        .cc-status .empty-note { font-style:italic; opacity:.75; }

        .cc-foot { display:flex; justify-content:flex-end; align-items:center; padding:11px 22px; border-top:1px solid #f0f1f4; }
        .cc-open { font-size:12px; font-weight:600; color:#70757a; display:inline-flex; align-items:center; gap:6px; }
        .class-card:hover .cc-open { color:var(--cc-base); }
        .class-card:hover .cc-open i { transform:translateX(3px); }
        .cc-open i { transition:transform .2s; }

        .empty { background:#fff; border-radius:16px; padding:60px 20px; text-align:center; color:#aaa; font-size:13px; }
        .empty i { font-size:38px; color:#e0d0d0; display:block; margin-bottom:12px; }

        @media (max-width:1240px) { .classes { grid-template-columns:repeat(2, 1fr); } }
        @media (max-width:900px) { .main { margin-left:68px; } }
        @media (max-width:768px) { .main { margin-left:0; padding:16px; } .topbar { flex-direction:column; align-items:flex-start; } }
        @media (max-width:680px) { .classes { grid-template-columns:1fr; } }
    </style>
</head>
<body>

@include('partials.faculty-sidebar', ['active' => 'quizzes'])

<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">Class Quizzes</div>
            <div class="page-sub">Your assigned subjects. Open one to build, publish, and share quizzes there.</div>
        </div>
        <div class="topbar-right">
            @include('partials.topbar-actions')
            <a href="{{ route('faculty.quizzes.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Quiz</a>
        </div>
    </div>

    @if($classes->isEmpty())
        <div class="empty">
            <i class="fas fa-chalkboard"></i>
            You have no subjects assigned yet. Ask the Program Chair to assign you a subject.
        </div>
    @else
        <div class="classes">
            @foreach($classes as $class)
                <a class="class-card" href="{{ route('faculty.quizzes.subject', $class['key']) }}"
                   style="--cc-base:{{ $class['theme']['base'] }}; --cc-dark:{{ $class['theme']['dark'] }}; --cc-pastel:{{ $class['theme']['pastel'] }}; --cc-soft:{{ $class['theme']['soft'] }};">
                    <div class="cc-banner">
                        <div class="cc-banner-illus" aria-hidden="true">
                            <i class="fas {{ $class['icon2'] }} i1"></i>
                            <i class="fas {{ $class['icon'] }} i2"></i>
                        </div>
                        <div class="cc-code">{{ $class['code'] }}</div>
                        <div class="cc-name">{{ $class['name'] }}</div>
                    </div>

                    <span class="cc-icon"><i class="fas {{ $class['icon'] }}"></i></span>

                    <div class="cc-body">
                        <div class="cc-sub">
                            {{ $class['total'] }} {{ Str::plural('quiz', $class['total']) }}
                            @if($class['submissions'] > 0)
                                · {{ $class['submissions'] }} {{ Str::plural('submission', $class['submissions']) }}
                            @endif
                        </div>
                        <div class="cc-status">
                            @if($class['draft'] > 0)
                                <span class="todo-badge"><i class="fas fa-pen-to-square"></i> {{ $class['draft'] }} draft{{ $class['draft'] === 1 ? '' : 's' }} to finish</span>
                            @elseif($class['total'] === 0)
                                <span class="empty-note">No quizzes posted yet</span>
                            @else
                                <span class="done-note"><i class="fas fa-circle-check"></i> {{ $class['published'] }} published</span>
                            @endif
                        </div>
                    </div>

                    <div class="cc-foot">
                        <span class="cc-open">Open <i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</main>

@include('partials.alerts')
</body>
</html>
