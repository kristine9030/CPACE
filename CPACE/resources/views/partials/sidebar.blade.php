{{--
    Shared reviewer sidebar.
    Usage: @include('partials.sidebar', ['active' => 'dashboard'])
    Valid $active keys: dashboard, subjects, quizzes, mock-exams,
                        performance, review-notes, calendar, achievements
--}}
@php $active = $active ?? ''; @endphp

<style>
    /* ─── SHARED SIDEBAR ─── */
    .sidebar {
        background: #6B1A1A;
        background: linear-gradient(180deg, #7B1D1D 0%, #5a1515 100%);
        color: #fff;
        position: fixed;
        top: 0; left: 0;
        width: 230px;
        height: 100vh;
        display: flex;
        flex-direction: column;
        z-index: 1000;
        overflow-y: auto;
        overflow-x: hidden;
        transition: width 0.28s cubic-bezier(.4,0,.2,1);
        box-shadow: 4px 0 24px rgba(0,0,0,0.18);
    }
    .sidebar.collapsed { width: 68px; }

    /* ── Collapse button ── */
    .sidebar-collapse-btn {
        position: absolute;
        top: 24px; right: -14px;
        width: 28px; height: 28px;
        background: #fff;
        border: 1.5px solid #e0e0e0;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        font-size: 11px;
        color: #7B1D1D;
        z-index: 1010;
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        transition: background 0.2s, border-color 0.2s, box-shadow 0.2s;
        flex-shrink: 0;
    }
    .sidebar-collapse-btn:hover { background: #f5e8e8; border-color: #7B1D1D; box-shadow: 0 4px 12px rgba(0,0,0,0.18); }
    .sidebar-collapse-btn i { transition: transform 0.28s ease; }
    .sidebar.collapsed .sidebar-collapse-btn i { transform: rotate(180deg); }

    /* ── Logo ── */
    .sidebar .sidebar-logo {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 14px 20px 14px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        flex-shrink: 0;
    }
    .sidebar .logo-icon {
        width: 52px; height: 52px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        overflow: hidden;
    }
    .sidebar .logo-icon img {
        width: 100%; height: 100%;
        object-fit: contain;
    }
    .sidebar .logo-text { line-height: 1.2; }
    .sidebar .logo-text strong { display: block; font-size: 19px; font-weight: 700; letter-spacing: 0.6px; color: #fff; }
    .sidebar .logo-text small  { font-size: 10px; color: rgba(255,255,255,0.55); font-weight: 400; letter-spacing: 0.2px; font-style: italic; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sidebar.collapsed .logo-text { display: none; }
    .sidebar.collapsed .sidebar-logo { justify-content: center; padding: 14px 0 18px; }

    /* ── Nav section labels ── */
    .sidebar .nav-section {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .sidebar .nav-label {
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: rgba(255,255,255,0.35);
        padding: 18px 22px 6px;
        list-style: none;
        white-space: nowrap;
        overflow: hidden;
    }
    .sidebar.collapsed .nav-label { visibility: hidden; padding: 14px 0 4px; }

    /* ── Nav items ── */
    .sidebar .sidebar-nav {
        list-style: none;
        flex: 1;
        margin: 0;
        padding: 8px 0 0;
    }
    .sidebar .sidebar-nav li { list-style: none; }
    .sidebar .sidebar-nav li a {
        display: flex;
        align-items: center;
        gap: 11px;
        margin: 1px 10px;
        padding: 9px 12px;
        border-radius: 8px;
        color: rgba(255,255,255,0.65);
        text-decoration: none;
        font-size: 13px;
        font-weight: 400;
        transition: background 0.18s, color 0.18s;
        white-space: nowrap;
    }
    .sidebar .sidebar-nav li a:hover {
        color: #fff;
        background: rgba(255,255,255,0.1);
    }
    .sidebar .sidebar-nav li a.active {
        color: #fff;
        background: rgba(255,255,255,0.16);
        font-weight: 500;
    }
    .sidebar .sidebar-nav li a.active i {
        color: #ffb3b3;
    }
    .sidebar .sidebar-nav li a i {
        width: 17px;
        text-align: center;
        font-size: 14px;
        flex-shrink: 0;
        color: rgba(255,255,255,0.5);
        transition: color 0.18s;
    }
    .sidebar .sidebar-nav li a:hover i { color: rgba(255,255,255,0.85); }

    .sidebar.collapsed .sidebar-nav li a {
        margin: 1px 6px;
        padding: 10px 0;
        justify-content: center;
        gap: 0;
        border-radius: 8px;
    }
    .sidebar.collapsed .sidebar-nav li a span { display: none; }

    /* ── Footer / user ── */
    .sidebar .sidebar-footer {
        border-top: 1px solid rgba(255,255,255,0.1);
        padding: 14px 12px;
        flex-shrink: 0;
    }
    .sidebar .user-menu { position: relative; }
    .sidebar .user-profile {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 10px;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.18s;
    }
    .sidebar .user-profile:hover { background: rgba(255,255,255,0.08); }
    .sidebar .avatar-sm {
        width: 34px; height: 34px;
        background: rgba(255,255,255,0.18);
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 12px; color: #fff;
        flex-shrink: 0;
        letter-spacing: 0.5px;
    }
    .sidebar .user-details { flex: 1; min-width: 0; }
    .sidebar .user-details .uname { display: block; font-size: 12.5px; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sidebar .user-details .urole { display: block; font-size: 10.5px; color: rgba(255,255,255,0.5); margin-top: 1px; }
    .sidebar.collapsed .user-details,
    .sidebar.collapsed .chevron-icon { display: none; }
    .sidebar.collapsed .user-profile { justify-content: center; padding: 8px 0; }

    .sidebar .user-dropdown {
        display: none;
        margin-top: 6px;
        background: rgba(0,0,0,0.22);
        border-radius: 8px;
        overflow: hidden;
    }
    .sidebar .user-dropdown.open { display: block; }
    .sidebar .user-dropdown button {
        display: flex; align-items: center; gap: 9px;
        width: 100%; padding: 9px 14px;
        background: none; border: none;
        color: rgba(255,255,255,0.8);
        font-size: 12.5px; cursor: pointer; text-align: left;
        transition: background 0.15s, color 0.15s;
    }
    .sidebar .user-dropdown button:hover { background: rgba(255,255,255,0.1); color: #fff; }
    .sidebar .user-dropdown button i { width: 15px; text-align: center; color: rgba(255,255,255,0.55); }
    .sidebar.collapsed .user-dropdown { display: none !important; }
    .sidebar .chevron-icon { transition: transform 0.2s; color: rgba(255,255,255,0.4); font-size: 10px; }
    .sidebar .user-menu.open .chevron-icon { transform: rotate(180deg); }

    /* ── Shared avatar image handling (top-bar buttons + sidebar) ── */
    .profile-btn, .profile-avatar, .avatar-sm {
        position: relative;
        overflow: hidden;
    }
    .profile-btn img, .profile-avatar img, .avatar-sm img {
        position: absolute;
        inset: 0;
        width: 100%; height: 100%;
        object-fit: cover;
        border-radius: inherit;
    }
    .avatar-default {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%; height: 100%;
        font-size: inherit;
        font-weight: inherit;
        color: inherit;
        letter-spacing: inherit;
        line-height: 1;
    }

    /* ── Layout ── */
    .main-content { margin-left: 230px; }
    .sidebar.collapsed ~ .main-content { margin-left: 68px; }

    @media (max-width: 900px) {
        .sidebar { width: 68px; }
        .sidebar .logo-text,
        .sidebar .sidebar-nav li a span,
        .sidebar .user-details,
        .sidebar .chevron-icon,
        .sidebar .nav-label { display: none; }
        .sidebar .sidebar-nav li a { margin: 1px 6px; padding: 10px 0; justify-content: center; gap: 0; }
        .sidebar .sidebar-logo { justify-content: center; padding: 24px 0 22px; }
        .sidebar .user-profile { justify-content: center; padding: 8px 0; }
        .main-content { margin-left: 68px; }
    }

    @media (max-width: 768px) {
        .sidebar-collapse-btn { display: none !important; }
    }

    @media (min-width: 769px) {
        .bottom-nav,
        .more-drawer,
        .more-drawer-overlay,
        .mobile-app-header { display: none !important; }
    }

    @media (max-width: 768px) {
        .sidebar { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 80px 16px 90px !important; }
    }
</style>

<aside class="sidebar" id="sidebar">
    <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Toggle sidebar">
        <i class="fas fa-chevron-left"></i>
    </button>

    <div class="sidebar-logo">
        <div class="logo-icon">
            <img src="{{ asset('images/cpace_logo.png') }}" alt="CPACE Logo">
        </div>
        <div class="logo-text">
            <strong>CPACE</strong>
            <small>Your edge to Ace CPALE</small>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-label">Main</li>
        <li><a href="{{ route('dashboard') }}" class="{{ $active === 'dashboard' ? 'active' : '' }}"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
        <li><a href="{{ route('subjects') }}" class="{{ $active === 'subjects' ? 'active' : '' }}"><i class="fas fa-book-open"></i><span>Subjects</span></a></li>

        <li class="nav-label">Study</li>
        <li><a href="{{ route('adaptive-quizzes') }}" class="{{ $active === 'quizzes' ? 'active' : '' }}"><i class="fas fa-pen-fancy"></i><span>Quizzes</span></a></li>
        <li><a href="{{ route('mock-exams') }}" class="{{ $active === 'mock-exams' ? 'active' : '' }}"><i class="fas fa-file-alt"></i><span>Mock Exams</span></a></li>
        <li><a href="{{ route('review-notes') }}" class="{{ $active === 'review-notes' ? 'active' : '' }}"><i class="fas fa-sticky-note"></i><span>Review Notes</span></a></li>

        <li class="nav-label">Track</li>
        <li><a href="{{ route('performance') }}" class="{{ $active === 'performance' ? 'active' : '' }}"><i class="fas fa-chart-bar"></i><span>Performance</span></a></li>
        <li><a href="{{ route('calendar') }}" class="{{ $active === 'calendar' ? 'active' : '' }}"><i class="fas fa-calendar-alt"></i><span>Calendar</span></a></li>
        <li><a href="{{ route('achievements') }}" class="{{ $active === 'achievements' ? 'active' : '' }}"><i class="fas fa-trophy"></i><span>Achievements</span></a></li>

        <li class="nav-label">Account</li>
        <li><a href="#"><i class="fas fa-cog"></i><span>Settings</span></a></li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-menu" id="userMenu">
            <div class="user-profile" onclick="document.getElementById('userMenu').classList.toggle('open'); document.getElementById('userDropdown').classList.toggle('open');">
                <div class="avatar-sm">
                    @include('partials.avatar-content')
                </div>
                <div class="user-details">
                    <span class="uname">{{ Auth::user()->name }}</span>
                    <span class="urole">Student Reviewer</span>
                </div>
                <i class="fas fa-chevron-down chevron-icon"></i>
            </div>
            <div class="user-dropdown" id="userDropdown">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"><i class="fas fa-sign-out-alt"></i><span>Logout</span></button>
                </form>
            </div>
        </div>
    </div>
</aside>

<script>
(function () {
    const sidebar = document.getElementById('sidebar');
    const btn     = document.getElementById('sidebarCollapseBtn');
    if (!sidebar) return;

    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
    }

    if (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }
})();
</script>
