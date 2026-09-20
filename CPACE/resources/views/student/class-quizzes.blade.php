<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Quizzes - CPACE CPA Reviewer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        :root { --maroon:#7B1D1D; --ink:#1f2430; --muted:#6b7280; --line:#e3e5ea; --bg:#f4f5f7; }
        body { font-family:'Poppins',sans-serif; background:var(--bg); color:var(--ink); }
        .dashboard-container { display:block; min-height:100vh; }

        /* ─── Top bar: search + messages + notifications + profile ───
           Same markup/behaviour as every other student page. */
        .top-bar { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:22px; gap:20px; flex-wrap:wrap; }
        .header-title {
            font-family:'Montserrat',sans-serif; font-size:30px; font-weight:700; color:#14283E;
            margin-bottom:6px; padding-bottom:10px; position:relative;
        }
        .header-title::after {
            content:''; position:absolute; left:0; bottom:0;
            width:40px; height:4px; border-radius:2px;
            background:linear-gradient(90deg, #c0392b, #7B1D1D);
        }
        .header-subtitle { font-size:14px; color:#999; }
        .header-count { background:#fff; border:1px solid var(--line); border-radius:22px; padding:8px 16px; font-size:12.5px; color:#555; font-weight:500; white-space:nowrap; }
        .header-count i { color:var(--maroon); margin-right:6px; }
        .top-bar-right { display:flex; align-items:center; gap:14px; flex-wrap:wrap; }

        .search-wrap { position:relative; }
        .search-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#aaa; font-size:14px; }
        .search-wrap input {
            width:280px; padding:10px 14px 10px 36px;
            border:1px solid #e0e0e0; border-radius:24px;
            font-size:13px; font-family:'Poppins',sans-serif;
            background:#fff; color:#555; outline:none;
        }
        .search-wrap input:focus { border-color:var(--maroon); }
        .search-wrap input::placeholder { color:#bbb; }

        .notif-btn {
            position:relative; width:40px; height:40px;
            border:none; background:#fff; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:17px; color:#555; cursor:pointer;
            box-shadow:0 1px 4px rgba(0,0,0,.08); flex-shrink:0;
        }
        .notif-btn:hover { background:#f0f0f0; }
        .badge {
            position:absolute; top:-3px; right:-3px;
            width:18px; height:18px; background:#c0392b;
            color:#fff; border-radius:50%; font-size:10px; font-weight:700;
            display:flex; align-items:center; justify-content:center;
        }
        .profile-avatar {
            width:40px; height:40px; background:var(--maroon);
            border-radius:10px; border:none; color:#fff;
            font-weight:700; font-size:14px; cursor:pointer;
            font-family:'Poppins',sans-serif; transition:background .2s; flex-shrink:0;
        }
        .profile-avatar:hover { background:#6a1818; }

        .header-dropdown-wrap { position:relative; }
        .dropdown-menu {
            position:absolute; top:calc(100% + 8px); right:0;
            background:#fff; border:1px solid #e5e7eb; border-radius:10px;
            min-width:185px; box-shadow:0 6px 20px rgba(0,0,0,.12);
            display:none; z-index:2000;
        }
        .dropdown-menu.active { display:block; }
        .dropdown-menu a, .dropdown-menu button {
            display:flex; align-items:center; gap:10px;
            padding:11px 16px; font-size:13px; font-family:'Poppins',sans-serif;
            text-decoration:none; color:#333; background:none; border:none;
            width:100%; text-align:left; cursor:pointer; transition:background .2s;
            border-bottom:1px solid #f5f5f5;
        }
        .dropdown-menu a:last-child, .dropdown-menu form:last-child button { border-bottom:none; }
        .dropdown-menu a:hover, .dropdown-menu button:hover { background:#f9f9f9; }
        .dropdown-menu a i, .dropdown-menu button i { color:var(--maroon); width:16px; text-align:center; }
        .dropdown-menu .logout-btn { color:#e53e3e; }
        .dropdown-menu .logout-btn i { color:#e53e3e; }

        @media (max-width:600px) {
            .search-wrap input { width:100%; }
            .search-wrap { flex:1; }
            .top-bar-right { width:100%; }
        }

        /* ─── Subject class cards ───
           A proper Google Classroom card: a coloured banner up top holding
           the subject identity, a round icon badge hanging off its edge,
           and a white body below with the teacher and what's due. Every
           enrolled subject gets one, even a class with nothing posted yet. */
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
                     /* A deeper shadow under the banner so it reads as raised
                        off the white body, plus a vignette along its own
                        bottom edge for extra depth. */
                     box-shadow:0 10px 18px -8px rgba(0,0,0,.45), inset 0 -22px 26px -18px rgba(0,0,0,.4); }
        .cc-banner::before { content:''; position:absolute; inset:0;
                             background:linear-gradient(115deg, rgba(255,255,255,.18) 0%, rgba(255,255,255,0) 55%); }
        .cc-code, .cc-name { text-shadow:0 1px 4px rgba(0,0,0,.25); }
        /* Faded icon cluster riding in the banner's corner, like Classroom artwork. */
        .cc-banner-illus { position:absolute; right:-8px; top:-10px; pointer-events:none; }
        .cc-banner-illus i { position:absolute; color:#fff; }
        .cc-banner-illus .i1 { font-size:64px; opacity:.14; right:6px; top:2px; }
        .cc-banner-illus .i2 { font-size:34px; opacity:.22; right:52px; top:44px; }
        .cc-code { position:relative; font-size:20px; font-weight:700; line-height:1.25; letter-spacing:.2px; }
        .cc-name { position:relative; font-size:12.5px; opacity:.92; margin-top:4px; line-height:1.4; padding-right:20px;
                   display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }

        /* Icon badge hanging off the banner — Classroom's teacher-avatar spot,
           carrying the subject's own icon instead. */
        .cc-icon {
            position:relative; z-index:2; width:52px; height:52px; border-radius:15px; margin:-26px 0 0 24px;
            background:#fff; color:var(--cc-base); display:flex; align-items:center; justify-content:center;
            font-size:21px; box-shadow:0 4px 10px -2px rgba(0,0,0,.2); border:3px solid #fff;
        }

        .cc-body { flex:1; display:flex; flex-direction:column; padding:12px 24px 16px; }
        .cc-teacher { font-size:12px; color:#70757a; margin-top:2px; display:flex; align-items:center; gap:7px; }
        .cc-teacher i { font-size:10.5px; color:#9aa0a6; }

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

        /* ─── Empty state ─── */
        .empty { background:#fff; border:1px solid var(--line); border-radius:16px; padding:56px 24px 60px; text-align:center; }
        .empty-art { width:170px; height:150px; margin:0 auto 6px; display:block; }
        .empty-title { font-size:16.5px; font-weight:600; color:#3c3f45; margin-top:10px; }
        .empty-text { font-size:13px; color:#8a8f98; margin-top:8px; line-height:1.7; max-width:400px; margin-left:auto; margin-right:auto; }
        .empty-hint { display:inline-flex; align-items:center; gap:8px; margin-top:20px; font-size:12px; color:#9aa0a6;
                      background:#f7f8fa; border-radius:20px; padding:8px 16px; }
        .empty-hint i { color:#c0392b; }
        /* The little clipboard mascot gives a slow, friendly bob. */
        .bob { animation:bob 3.2s ease-in-out infinite; transform-origin:center; }
        @keyframes bob { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-7px); } }
        .twinkle { animation:twinkle 2.4s ease-in-out infinite; }
        .twinkle.d { animation-delay:1.1s; }
        @keyframes twinkle { 0%,100% { opacity:.25; } 50% { opacity:1; } }
        @media (prefers-reduced-motion: reduce) { .bob, .twinkle { animation:none; } }

        @media (max-width:1240px) {
            .classes { grid-template-columns:repeat(2, 1fr); }
        }
        @media (max-width:680px) {
            .classes { grid-template-columns:1fr; }
            .header-title { font-size:22px; }
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    @include('partials.sidebar', ['active' => 'class-quizzes'])
    @include('partials.student-bottom-nav', ['active' => 'quizzes'])
    @include('partials.student-mobile-header')

    <main class="main-content">
        <div class="top-bar">
            <div>
                <div class="header-title">Class Quizzes</div>
                <div class="header-subtitle">Your classes. Open one to see the quizzes your instructor posted there.</div>
            </div>
            <div class="top-bar-right">
                @if($classes->isNotEmpty())
                    <div class="header-count">
                        <i class="fas fa-chalkboard"></i>
                        {{ $classes->count() }} {{ Str::plural('class', $classes->count()) }} ·
                        {{ $classes->sum('total') }} {{ Str::plural('quiz', $classes->sum('total')) }}
                    </div>
                @endif
                <div class="search-wrap gs-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" data-gs="true" placeholder="Search topics, questions...">
                </div>
                <a class="notif-btn" href="{{ route('messages.index') }}" title="Messages" aria-label="Messages">
                    <i class="fas fa-comment-dots"></i>
                    @if($unreadMessages > 0)<span class="badge">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>@endif
                </a>
                <a class="notif-btn" href="{{ route('notifications.index') }}" title="Notifications" aria-label="Notifications">
                    <i class="fas fa-bell"></i>
                    @if($unreadNotifications > 0)<span class="badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>@endif
                </a>
                <div class="header-dropdown-wrap">
                    <button class="profile-avatar" id="profileBtn">@include('partials.avatar-content')</button>
                    <div class="dropdown-menu" id="profileDropdown">
                        <a href="#" class="js-open-profile-modal"><i class="fas fa-user"></i> Profile Settings</a>
                        <a href="#"><i class="fas fa-chart-line"></i> My Progress</a>
                        <a href="#"><i class="fas fa-question-circle"></i> Help &amp; Support</a>
                        <form method="POST" action="{{ route('logout') }}"
                              data-confirm="You will be signed out of CPACE and returned to the login page."
                              data-confirm-title="Log out of CPACE?"
                              data-confirm-ok="Yes, log me out"
                              data-confirm-icon="question" style="margin:0;padding:0;">
                            @csrf
                            <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if($classes->isEmpty())
            <div class="empty">
                {{-- A clipboard mascot: friendlier than a lone icon for a page
                     a student may land on before any quiz exists. --}}
                <svg class="empty-art" viewBox="0 0 180 160" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="An empty clipboard">
                    <ellipse cx="90" cy="140" rx="46" ry="7" fill="#e9dada"/>
                    <g class="bob">
                        <rect x="46" y="26" width="88" height="106" rx="12" fill="#fdf6f6" stroke="#e3cfcf" stroke-width="2.5"/>
                        <rect x="72" y="16" width="36" height="20" rx="7" fill="#c0392b"/>
                        <rect x="79" y="10" width="22" height="13" rx="6" fill="#7B1D1D"/>
                        <rect x="62" y="56" width="56" height="7" rx="3.5" fill="#efdfdf"/>
                        <rect x="62" y="74" width="42" height="7" rx="3.5" fill="#efdfdf"/>
                        <rect x="62" y="92" width="50" height="7" rx="3.5" fill="#efdfdf"/>
                        {{-- a sleepy little face, because nothing is due --}}
                        <circle cx="76" cy="114" r="3" fill="#c9a9a9"/>
                        <circle cx="104" cy="114" r="3" fill="#c9a9a9"/>
                        <path d="M81 121q9 7 18 0" stroke="#c9a9a9" stroke-width="2.5" stroke-linecap="round" fill="none"/>
                    </g>
                    <path class="twinkle" d="M34 46l3 7 7 3-7 3-3 7-3-7-7-3 7-3z" fill="#e4bcbc"/>
                    <path class="twinkle d" d="M146 78l2.4 5.6 5.6 2.4-5.6 2.4-2.4 5.6-2.4-5.6-5.6-2.4 5.6-2.4z" fill="#e4bcbc"/>
                </svg>
                <div class="empty-title">No classes yet</div>
                <div class="empty-text">
                    You'll see a class tile here for every subject you're enrolled in — quizzes will appear as your instructors post them.
                </div>
                <div class="empty-hint"><i class="fas fa-lightbulb"></i> In the meantime, keep sharp with an adaptive quiz.</div>
            </div>
        @else
            <div class="classes">
                @foreach($classes as $class)
                    @php
                        $lead = $class['faculty']->first();
                        $others = max(0, $class['faculty']->count() - 1);
                    @endphp
                    <a class="class-card" href="{{ route('class-quizzes.subject', $class['key']) }}"
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
                            <div class="cc-teacher">
                                <i class="fas fa-chalkboard-user"></i>
                                {{ $lead?->name ?? 'CPACE Faculty' }}@if($others) +{{ $others }} more @endif
                            </div>
                            <div class="cc-status">
                                @if($class['todo'])
                                    <span class="todo-badge"><i class="fas fa-pen-to-square"></i> {{ $class['todo'] }} to do</span>
                                @elseif($class['total'] === 0)
                                    <span class="empty-note">No quizzes posted yet</span>
                                @else
                                    <span class="done-note"><i class="fas fa-circle-check"></i> {{ $class['done'] }}/{{ $class['total'] }} completed</span>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const profileBtn = document.getElementById('profileBtn');
    const profileDrop = document.getElementById('profileDropdown');
    if (profileBtn && profileDrop) {
        profileBtn.addEventListener('click', e => {
            e.stopPropagation();
            profileDrop.classList.toggle('active');
        });
        document.addEventListener('click', () => profileDrop.classList.remove('active'));
        profileDrop.addEventListener('click', e => e.stopPropagation());
    }
});
</script>
@include('partials.global-search')
@include('partials.alerts')
</body>
</html>
