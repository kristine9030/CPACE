<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $code }} Quizzes - CPACE CPA Reviewer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        :root { --maroon:#7B1D1D; --ink:#1f2430; --muted:#6b7280; --line:#e3e5ea; --bg:#f4f5f7;
                --class-from:{{ $theme['base'] }}; --class-to:{{ $theme['dark'] }};
                --class-pastel:{{ $theme['pastel'] }}; --class-soft:{{ $theme['soft'] }}; }
        body { font-family:'Poppins',sans-serif; background:var(--bg); color:var(--ink); }
        .dashboard-container { display:block; min-height:100vh; }
        .sheet { width:100%; }

        /* ─── Top bar: search + messages + notifications + profile ───
           Same markup/behaviour as every other student page. */
        .top-bar { display:flex; justify-content:flex-end; align-items:flex-start; margin-bottom:18px; gap:20px; flex-wrap:wrap; }
        .top-bar-right { display:flex; align-items:center; gap:14px; flex-wrap:wrap; }

        .search-wrap { position:relative; }
        .search-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#aaa; font-size:14px; }
        .search-wrap input {
            width:220px; padding:10px 14px 10px 36px;
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

        .back { display:inline-flex; align-items:center; gap:8px; font-size:12.5px; font-weight:500; color:#70757a;
                text-decoration:none; margin-bottom:12px; transition:color .2s; }
        .back:hover { color:var(--maroon); }

        /* ─── Subject header ───
           Same pastel-tile language as the class list: a light tint of the
           subject's colour, an icon badge, and a small illustration cluster
           tucked in the corner — no dark banner. */
        .banner { position:relative; border-radius:16px; overflow:hidden; color:var(--ink); padding:26px 30px;
                  min-height:150px; display:flex; align-items:center; gap:20px;
                  background:var(--class-pastel); border:1px solid rgba(0,0,0,.04);
                  /* A darker drop shadow so the panel lifts off the page,
                     the way the other cards on this side of the app do. */
                  box-shadow:0 14px 28px -14px rgba(20,20,30,.35), 0 2px 6px rgba(20,20,30,.08); }
        .banner-illus { position:absolute; right:-8px; bottom:-16px; width:170px; height:170px; pointer-events:none; }
        .banner-illus .blob { position:absolute; right:10px; bottom:10px; width:126px; height:126px; border-radius:38px;
                               background:var(--class-soft); transform:rotate(-8deg); }
        .banner-illus i { position:absolute; color:var(--class-from); opacity:.8; }
        .banner-illus .i1 { font-size:58px; right:56px; bottom:44px; opacity:.55; }
        .banner-illus .i2 { font-size:44px; right:20px; bottom:20px; }
        .banner-emblem { position:relative; z-index:1; flex-shrink:0; width:68px; height:68px; border-radius:19px;
                          background:var(--class-from); color:#fff; display:flex; align-items:center; justify-content:center;
                          font-size:27px; box-shadow:0 6px 14px -4px rgba(0,0,0,.28); }
        .banner-text { position:relative; z-index:1; padding-right:110px; }
        .banner-kicker { font-size:10.5px; letter-spacing:1.6px; text-transform:uppercase;
                         color:var(--class-from); opacity:.85; font-weight:700; margin-bottom:5px; display:flex; align-items:center; gap:6px; }
        .banner-code { font-family:'Montserrat',ui-sans-serif,system-ui,sans-serif; font-size:28px; font-weight:700;
                       line-height:1.15; color:#1a1a1a; }
        .banner-name { font-size:13.5px; color:#5c5450; opacity:.9; margin-top:5px; max-width:520px; }
        .banner-teacher { font-size:12.5px; color:#6b625d; margin-top:14px; display:flex; align-items:center; gap:9px; flex-wrap:wrap; }
        .t-avatar { width:28px; height:28px; border-radius:50%; background:var(--class-soft); border:2px solid #fff;
                    box-shadow:0 1px 4px rgba(0,0,0,.12);
                    display:inline-flex; align-items:center; justify-content:center; font-size:10.5px; font-weight:700;
                    color:var(--class-to); overflow:hidden; }
        .t-avatar img { width:100%; height:100%; object-fit:cover; }

        /* ─── Progress strip ─── */
        .strip { display:flex; align-items:center; gap:14px; background:#fff; border:1px solid var(--line);
                 border-radius:12px; padding:12px 18px; margin-top:12px; flex-wrap:wrap; }
        .strip-bar { flex:1; min-width:180px; height:7px; border-radius:5px; background:#eef0f3; overflow:hidden; }
        .strip-bar span { display:block; height:100%; border-radius:5px;
                          background:linear-gradient(90deg, var(--class-from), var(--class-to)); transition:width .5s ease; }
        .strip-txt { font-size:12px; color:#5f6368; white-space:nowrap; }
        .strip-txt b { color:#26282c; font-weight:600; }

        .section-label { font-size:11px; letter-spacing:1.2px; text-transform:uppercase; color:#9aa0a6;
                         font-weight:600; margin:20px 0 9px; }

        /* ─── Classwork rows: collapsed by default, expand in place ─── */
        .work { background:#fff; border:1px solid var(--line); border-radius:12px; margin-bottom:12px; overflow:hidden;
                transition:box-shadow .2s ease, border-color .2s ease; }
        .work:hover { box-shadow:0 4px 14px rgba(0,0,0,.08); }
        .work.open { border-color:#d6d9df; box-shadow:0 6px 20px rgba(40,10,10,.1); }

        .work-head { display:flex; align-items:center; gap:18px; padding:20px 22px; cursor:pointer; min-height:78px;
                     background:none; border:none; width:100%; text-align:left; font-family:'Poppins',sans-serif; }
        .work-head:focus-visible { outline:3px solid var(--class-from); outline-offset:-3px; }
        .work-icon { width:46px; height:46px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center;
                     color:#fff; font-size:17px; background:linear-gradient(135deg, var(--class-from), var(--class-to)); }
        .work.is-done .work-icon { background:linear-gradient(135deg,#34d399,#10b981); }
        .work.is-off  .work-icon { background:#c3c7ce; }
        .work-main { flex:1; min-width:0; }
        .work-title { display:block; font-size:15.5px; font-weight:600; color:#25282d; line-height:1.35; }
        .work-sub { display:block; font-size:12.5px; color:#8c9197; margin-top:4px; }
        .work-right { display:flex; align-items:center; gap:16px; flex-shrink:0; }
        .work-due { font-size:12.5px; color:#70757a; white-space:nowrap; }
        .work-due.soon { color:#c0392b; font-weight:600; }
        .chev { color:#b0b4ba; font-size:13px; transition:transform .25s ease; }
        .work.open .chev { transform:rotate(180deg); }

        .pill { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:600;
                padding:4px 11px; border-radius:20px; white-space:nowrap; }
        .p-open { background:#d1fae5; color:#059669; }
        .p-done { background:#dbeafe; color:#2563eb; }
        .p-soon { background:#fef3c7; color:#d97706; }
        .p-off  { background:#f1f2f4; color:#6b7280; }

        .work-body { display:none; padding:4px 22px 20px 86px; border-top:1px solid #f0f1f4; }
        .work.open .work-body { display:block; }
        .instr { background:#fffaf0; border:1px solid #fde9c6; border-radius:10px; padding:11px 14px; font-size:12.5px;
                 color:#5b4a2a; line-height:1.6; margin:11px 0 0; white-space:pre-line; }
        .facts { display:flex; flex-wrap:wrap; gap:8px; margin-top:11px; }
        .fact { background:#f7f8fa; border-radius:9px; padding:8px 12px; font-size:12px; color:#5f6368; }
        .fact i { color:var(--class-from); margin-right:7px; }
        .fact b { color:#26282c; }
        .score { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-top:11px;
                 background:#f7f8fa; border-radius:10px; padding:11px 15px; font-size:12.5px; color:#5f6368; }
        .score b { font-size:19px; color:var(--class-to); }
        .actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:13px; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:10px 18px; border-radius:9px;
               font-size:13px; font-weight:600; text-decoration:none; border:none; cursor:pointer; font-family:'Poppins',sans-serif; transition:all .2s; }
        .btn-primary { background:linear-gradient(135deg, var(--class-from), var(--class-to)); color:#fff; }
        .btn-primary:hover { filter:brightness(1.08); }
        .btn-ghost { background:#f1f2f4; color:#444; }
        .btn-ghost:hover { background:#e5e7eb; }

        /* ─── Empty state ─── */
        .empty { background:#fff; border:1px solid var(--line); border-radius:16px; padding:52px 24px 56px; text-align:center; margin-top:18px; }
        .empty-art { width:160px; height:142px; margin:0 auto; display:block; }
        .empty-title { font-size:16px; font-weight:600; color:#3c3f45; margin-top:12px; }
        .empty-text { font-size:13px; color:#8a8f98; margin-top:8px; line-height:1.7; max-width:400px; margin-left:auto; margin-right:auto; }
        .bob { animation:bob 3.2s ease-in-out infinite; transform-origin:center; }
        @keyframes bob { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-7px); } }
        .twinkle { animation:twinkle 2.4s ease-in-out infinite; }
        .twinkle.d { animation-delay:1.1s; }
        @keyframes twinkle { 0%,100% { opacity:.25; } 50% { opacity:1; } }
        @media (prefers-reduced-motion: reduce) { .bob, .twinkle { animation:none; } }

        @media (max-width:600px) {
            .banner { padding:20px 18px; min-height:auto; gap:14px; }
            .banner-emblem { width:52px; height:52px; font-size:21px; border-radius:15px; }
            .banner-text { padding-right:70px; }
            .banner-code { font-size:22px; }
            .banner-name { max-width:100%; }
            .banner-illus { width:120px; height:120px; }
            .banner-illus .blob { width:88px; height:88px; }
            .banner-illus .i1 { font-size:40px; }
            .banner-illus .i2 { font-size:30px; }
            .work-head { padding:15px 16px; gap:13px; min-height:64px; }
            .work-icon { width:38px; height:38px; font-size:15px; }
            .work-body { padding:3px 16px 16px 67px; }
            .work-right .work-due { display:none; }
            .search-wrap input { width:100%; }
            .search-wrap { flex:1; }
            .top-bar-right { width:100%; }
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
            <div class="top-bar-right">
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
        <div class="sheet">
            <a class="back" href="{{ route('class-quizzes') }}"><i class="fas fa-arrow-left"></i> All classes</a>

            <div class="banner">
                <div class="banner-illus" aria-hidden="true">
                    <span class="blob"></span>
                    <i class="fas {{ $icon2 }} i1"></i>
                    <i class="fas {{ $icon }} i2"></i>
                </div>

                <span class="banner-emblem"><i class="fas {{ $icon }}"></i></span>

                <div class="banner-text">
                    <div class="banner-kicker"><i class="fas fa-graduation-cap"></i> Class</div>
                    <div class="banner-code">{{ $code }}</div>
                    <div class="banner-name">{{ $name }}</div>
                    @if($faculty->isNotEmpty())
                        <div class="banner-teacher">
                            @foreach($faculty->take(3) as $teacher)
                                <span class="t-avatar">
                                    @if($teacher->profile_photo)
                                        <img src="{{ asset('storage/' . $teacher->profile_photo) }}" alt="">
                                    @else
                                        {{ strtoupper(substr($teacher->first_name ?? 'C', 0, 1)) }}{{ strtoupper(substr($teacher->last_name ?? 'F', 0, 1)) }}
                                    @endif
                                </span>
                            @endforeach
                            <span>{{ $faculty->pluck('name')->take(2)->join(', ') }}@if($faculty->count() > 2) +{{ $faculty->count() - 2 }} more @endif</span>
                        </div>
                    @endif
                </div>
            </div>

            @if($quizzes->isNotEmpty())
                @php $pct = (int) round($done / $quizzes->count() * 100); @endphp
                <div class="strip">
                    <div class="strip-txt"><b>{{ $done }}</b> of <b>{{ $quizzes->count() }}</b> completed</div>
                    <div class="strip-bar"><span style="width:{{ $pct }}%"></span></div>
                    <div class="strip-txt">{{ $pct }}%</div>
                </div>

                <div class="section-label">Classwork</div>

                @foreach($quizzes as $quiz)
                    @php
                        $attempt = $attempts->get($quiz->id);
                        $submitted = $attempt && $attempt->isSubmitted();
                        $avail = $quiz->availability();
                        $state = $submitted ? 'done' : ($avail === 'open' ? 'open' : ($avail === 'upcoming' ? 'soon' : 'off'));
                        $pill = [
                            'done' => ['p-done', 'fa-check', 'Completed'],
                            'open' => ['p-open', 'fa-circle-dot', $attempt ? 'In progress' : 'Assigned'],
                            'soon' => ['p-soon', 'fa-clock', 'Opens soon'],
                            'off'  => ['p-off', 'fa-lock', $avail === 'expired' ? 'Missed' : 'Closed'],
                        ][$state];
                        $dueSoon = ! $submitted && $quiz->due_at && $quiz->due_at->isFuture() && $quiz->due_at->isBefore(now()->addDays(2));
                    @endphp
                    <div class="work {{ $submitted ? 'is-done' : ($state === 'open' ? '' : 'is-off') }}" data-work>
                        <button type="button" class="work-head" aria-expanded="false">
                            <span class="work-icon"><i class="fas {{ $submitted ? 'fa-check' : ($state === 'off' ? 'fa-lock' : 'fa-clipboard-list') }}"></i></span>
                            <span class="work-main">
                                <span class="work-title">{{ $quiz->title }}</span>
                                <span class="work-sub">
                                    {{ $quiz->faculty?->name ?? 'CPACE Faculty' }} ·
                                    {{ $quiz->items_count }} {{ Str::plural('item', $quiz->items_count) }}
                                    @if($quiz->published_at) · posted {{ $quiz->published_at->format('M j') }} @endif
                                </span>
                            </span>
                            <span class="work-right">
                                <span class="pill {{ $pill[0] }}"><i class="fas {{ $pill[1] }}"></i> {{ $pill[2] }}</span>
                                <span class="work-due {{ $dueSoon ? 'soon' : '' }}">
                                    {{ $quiz->due_at ? 'Due ' . $quiz->due_at->format('M j') : 'No due date' }}
                                </span>
                                <i class="fas fa-chevron-down chev"></i>
                            </span>
                        </button>

                        <div class="work-body">
                            @if($quiz->instructions)
                                <div class="instr">{{ $quiz->instructions }}</div>
                            @endif
                            <div class="facts">
                                <span class="fact"><i class="fas fa-list-ol"></i><b>{{ $quiz->items_count }}</b> items</span>
                                <span class="fact"><i class="fas fa-stopwatch"></i>{!! $quiz->time_limit_minutes ? '<b>' . $quiz->time_limit_minutes . '</b> min' : 'No time limit' !!}</span>
                                <span class="fact"><i class="fas fa-door-open"></i>Opens <b>{{ $quiz->opens_at ? $quiz->opens_at->format('M j, g:i A') : 'now' }}</b></span>
                                <span class="fact"><i class="fas fa-calendar-day"></i>{!! $quiz->due_at ? 'Due <b>' . e($quiz->due_at->format('M j, g:i A')) . '</b>' : 'No deadline' !!}</span>
                            </div>

                            @if($submitted)
                                <div class="score">
                                    <span>Your score</span>
                                    <span><b>{{ $attempt->score }}</b> / {{ $attempt->total_points }} &nbsp;·&nbsp; {{ round((float) $attempt->percent, 1) }}%</span>
                                </div>
                            @endif

                            <div class="actions">
                                @if($submitted)
                                    <a href="{{ route('class-quiz.result', $quiz->share_token) }}" class="btn btn-ghost"><i class="fas fa-eye"></i> View result</a>
                                @elseif($state === 'open')
                                    <a href="{{ route('class-quiz.show', $quiz->share_token) }}" class="btn btn-primary">
                                        <i class="fas {{ $attempt ? 'fa-play' : 'fa-arrow-right' }}"></i> {{ $attempt ? 'Continue quiz' : 'Open quiz' }}
                                    </a>
                                @else
                                    <a href="{{ route('class-quiz.show', $quiz->share_token) }}" class="btn btn-ghost"><i class="fas fa-circle-info"></i> Details</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty">
                    <svg class="empty-art" viewBox="0 0 180 160" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="An empty clipboard">
                        <ellipse cx="90" cy="140" rx="46" ry="7" fill="#e9dada"/>
                        <g class="bob">
                            <rect x="46" y="26" width="88" height="106" rx="12" fill="#fdf6f6" stroke="#e3cfcf" stroke-width="2.5"/>
                            <rect x="72" y="16" width="36" height="20" rx="7" fill="#c0392b"/>
                            <rect x="79" y="10" width="22" height="13" rx="6" fill="#7B1D1D"/>
                            <rect x="62" y="56" width="56" height="7" rx="3.5" fill="#efdfdf"/>
                            <rect x="62" y="74" width="42" height="7" rx="3.5" fill="#efdfdf"/>
                            <rect x="62" y="92" width="50" height="7" rx="3.5" fill="#efdfdf"/>
                            <circle cx="76" cy="114" r="3" fill="#c9a9a9"/>
                            <circle cx="104" cy="114" r="3" fill="#c9a9a9"/>
                            <path d="M81 121q9 7 18 0" stroke="#c9a9a9" stroke-width="2.5" stroke-linecap="round" fill="none"/>
                        </g>
                        <path class="twinkle" d="M34 46l3 7 7 3-7 3-3 7-3-7-7-3 7-3z" fill="#e4bcbc"/>
                        <path class="twinkle d" d="M146 78l2.4 5.6 5.6 2.4-5.6 2.4-2.4 5.6-2.4-5.6-5.6-2.4 5.6-2.4z" fill="#e4bcbc"/>
                    </svg>
                    <div class="empty-title">No classwork here yet</div>
                    <div class="empty-text">Your instructor hasn't posted a quiz for {{ $code }}. It will appear the moment they publish one.</div>
                </div>
            @endif
        </div>
    </main>
</div>

<script>
// Classroom-style rows: tap a row to expand it in place; only one stays open.
document.querySelectorAll('[data-work] .work-head').forEach(function (head) {
    head.addEventListener('click', function () {
        var row = head.closest('[data-work]');
        var wasOpen = row.classList.contains('open');
        document.querySelectorAll('[data-work].open').forEach(function (other) {
            other.classList.remove('open');
            other.querySelector('.work-head').setAttribute('aria-expanded', 'false');
        });
        if (!wasOpen) {
            row.classList.add('open');
            head.setAttribute('aria-expanded', 'true');
        }
    });
});

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
