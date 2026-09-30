{{--
    The student top bar (title, search, messages, notifications, profile
    dropdown) for the Mock Exam screens. Mirrors the global top bar on the
    other student pages (see student/subjects.blade.php) so every student
    screen carries the same chrome. Styles are scoped under .global-topbar so
    partials.mock-exam-styles can't restyle it.

    Expects: $title, $subtitle. $back is optional (a ['url' =>, 'label' =>] pair).
    The page wires #profileBtn / #profileDropdown and includes partials.global-search.
--}}
<style>
    .global-topbar { display:flex; justify-content:space-between; align-items:center; gap:20px; margin-bottom:28px; position:relative; z-index:100; }
    .global-topbar .gt-back { display:inline-flex; align-items:center; gap:6px; font-size:12px; color:#999; text-decoration:none; margin-bottom:6px; }
    .global-topbar .gt-back:hover { color:var(--primary, #7B1D1D); }
    .global-topbar .page-title {
        font-family:'Montserrat',sans-serif; font-size:30px; font-weight:700; color:#14283E;
        margin-bottom:6px; padding-bottom:10px; position:relative;
    }
    .global-topbar .page-title::after {
        content:''; position:absolute; left:0; bottom:0; width:46px; height:3px; border-radius:2px;
        background:linear-gradient(90deg, #c0392b, #7B1D1D);
    }
    .global-topbar .page-subtitle { font-size:14px; color:#999; max-width:640px; }
    .global-topbar .top-bar-right { display:flex; align-items:center; gap:14px; flex-shrink:0; }

    .global-topbar .search-wrap { position:relative; }
    .global-topbar .search-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#aaa; font-size:14px; }
    .global-topbar .search-wrap input {
        width:280px; padding:10px 14px 10px 36px; border:1px solid #e0e0e0; border-radius:24px;
        font-size:13px; font-family:'Poppins',sans-serif; background:#fff; color:#555; outline:none;
    }
    .global-topbar .search-wrap input:focus { border-color:#7B1D1D; }
    .global-topbar .search-wrap input::placeholder { color:#bbb; }

    .global-topbar .notif-btn {
        position:relative; width:40px; height:40px; border:none; background:#fff; border-radius:50%;
        display:flex; align-items:center; justify-content:center; font-size:17px; color:#555;
        cursor:pointer; text-decoration:none; box-shadow:0 1px 4px rgba(0,0,0,.08); flex-shrink:0;
    }
    .global-topbar .notif-btn:hover { background:#f0f0f0; }
    .global-topbar .badge {
        position:absolute; top:-3px; right:-3px; width:18px; height:18px; background:#c0392b;
        color:#fff; border-radius:50%; font-size:10px; font-weight:700; padding:0;
        display:flex; align-items:center; justify-content:center;
    }

    .global-topbar .header-dropdown-wrap { position:relative; }
    .global-topbar .profile-avatar {
        width:40px; height:40px; background:#7B1D1D; border-radius:10px; border:none; padding:0;
        color:#fff; font-weight:700; font-size:14px; cursor:pointer; overflow:hidden;
        font-family:'Poppins',sans-serif; transition:background .2s;
    }
    .global-topbar .profile-avatar:hover { background:#641717; }
    .global-topbar .profile-avatar img { width:100%; height:100%; object-fit:cover; }

    .global-topbar .dropdown-menu {
        position:absolute; top:calc(100% + 8px); right:0; left:auto; padding:0;
        background:#fff; border:1px solid #e5e7eb; border-radius:10px;
        min-width:185px; box-shadow:0 6px 20px rgba(0,0,0,.12); display:none; z-index:2000;
    }
    .global-topbar .dropdown-menu.active { display:block; }
    .global-topbar .dropdown-menu a, .global-topbar .dropdown-menu button {
        display:flex; align-items:center; gap:10px; padding:11px 16px; margin:0;
        font-size:13px; font-family:'Poppins',sans-serif; text-decoration:none; color:#333;
        background:none; border:none; border-bottom:1px solid #f5f5f5; border-radius:0;
        width:100%; text-align:left; cursor:pointer; transition:background .2s;
    }
    .global-topbar .dropdown-menu form:last-child button { border-bottom:none; }
    .global-topbar .dropdown-menu a:hover, .global-topbar .dropdown-menu button:hover { background:#f9f9f9; }
    .global-topbar .dropdown-menu a i, .global-topbar .dropdown-menu button i { color:#7B1D1D; width:16px; text-align:center; }
    .global-topbar .dropdown-menu .logout-btn, .global-topbar .dropdown-menu .logout-btn i { color:#e53e3e; }

    @media (max-width:1024px) { .global-topbar .search-wrap input { width:160px; } }
    @media (max-width:768px) {
        .global-topbar { flex-direction:column; align-items:flex-start; gap:12px; }
        .global-topbar .top-bar-right { width:100%; flex-wrap:wrap; }
        .global-topbar .search-wrap { flex:1; }
        .global-topbar .search-wrap input { width:100%; }
        .global-topbar .page-title { font-size:22px; }
    }
</style>

<div class="top-bar global-topbar">
    <div class="top-bar-left">
        <div>
            @isset($back)
                <a class="gt-back" href="{{ $back['url'] }}"><i class="fas fa-arrow-left"></i> {{ $back['label'] }}</a>
            @endisset
            <div class="page-title">{{ $title }}</div>
            <div class="page-subtitle">{{ $subtitle }}</div>
        </div>
    </div>
    <div class="top-bar-right">
        <div class="search-wrap gs-wrap">
            <i class="fas fa-search"></i>
            <input type="text" data-gs="true" placeholder="Search topics, questions...">
        </div>
        <a class="notif-btn" href="{{ route('messages.index') }}" title="Messages" aria-label="Messages">
            <i class="fas fa-comment-dots"></i>
            @if(($unreadMessages ?? 0) > 0)<span class="badge">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>@endif
        </a>
        <a class="notif-btn" href="{{ route('notifications.index') }}" title="Notifications" aria-label="Notifications">
            <i class="fas fa-bell"></i>
            @if(($unreadNotifications ?? 0) > 0)<span class="badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>@endif
        </a>
        <div class="header-dropdown-wrap">
            <button class="profile-avatar" id="profileBtn" aria-label="Account menu">@include('partials.avatar-content')</button>
            <div class="dropdown-menu" id="profileDropdown">
                <a href="#" class="js-open-profile-modal"><i class="fas fa-user"></i> Profile Settings</a>
                <a href="{{ route('performance') }}"><i class="fas fa-chart-line"></i> My Progress</a>
                <a href="{{ route('help.index') }}"><i class="fas fa-question-circle"></i> Help &amp; Support</a>
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
