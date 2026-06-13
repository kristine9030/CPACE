{{--
    Shared reviewer sidebar.
    Usage: @include('partials.sidebar', ['active' => 'dashboard'])
    Valid $active keys: dashboard, subjects, quizzes, mock-exams,
                        performance, review-notes, calendar, achievements
--}}
@php $active = $active ?? ''; @endphp

<style>
    /* ─── SHARED SIDEBAR (keeps all reviewer pages consistent) ─── */
    .sidebar {
        background: #7B1D1D;
        color: #fff;
        position: fixed;
        top: 0;
        left: 0;
        width: 220px;
        height: 100vh;
        padding: 0;
        margin: 0;
        box-shadow: none;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        z-index: 1000;
        transition: width 0.3s ease;
    }
    .sidebar.collapsed { width: 70px; }

    /* ── Collapse toggle button (on sidebar's right edge) ── */
    .sidebar-collapse-btn {
        position: absolute;
        top: 22px;
        right: -17px;
        width: 34px; height: 34px;
        background: #fff;
        border: 2px solid #e0e0e0;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        font-size: 12px;
        color: #7B1D1D;
        z-index: 1010;
        box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        transition: background 0.2s, border-color 0.2s;
        flex-shrink: 0;
    }
    .sidebar-collapse-btn:hover {
        background: #f5e8e8;
        border-color: #7B1D1D;
    }
    .sidebar-collapse-btn i {
        transition: transform 0.3s ease;
    }
    .sidebar.collapsed .sidebar-collapse-btn i {
        transform: rotate(180deg);
    }

    @media (max-width: 768px) {
        .sidebar-collapse-btn { display: none !important; }
    }

    .sidebar .sidebar-logo {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 24px 20px 20px;
        margin: 0;
        border-bottom: 1px solid rgba(255,255,255,0.12);
    }
    .sidebar.collapsed .sidebar-logo { padding: 24px 20px 20px; }
    .sidebar .logo-circle {
        width: 44px;
        height: 44px;
        background: rgba(255,255,255,0.15);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #fff;
        flex-shrink: 0;
        border: 2px solid rgba(255,255,255,0.3);
    }
    .sidebar .logo-text { line-height: 1.25; }
    .sidebar .logo-text strong { display: block; font-size: 15px; font-weight: 700; letter-spacing: 0.5px; }
    .sidebar .logo-text small  { font-size: 11px; opacity: 0.8; font-weight: 400; }
    .sidebar.collapsed .logo-text { display: none; }

    .sidebar .sidebar-nav {
        list-style: none;
        flex: 1;
        margin: 0;
        padding: 12px 0;
    }
    .sidebar .sidebar-nav li { list-style: none; }
    .sidebar .sidebar-nav li a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 22px;
        color: rgba(255,255,255,0.75);
        text-decoration: none;
        font-size: 13px;
        font-weight: 400;
        transition: all 0.2s;
        white-space: nowrap;
        border-left: 3px solid transparent;
    }
    .sidebar .sidebar-nav li a:hover {
        color: #fff;
        background: rgba(255,255,255,0.1);
    }
    .sidebar .sidebar-nav li a.active {
        color: #fff;
        background: rgba(255,255,255,0.18);
        border-left-color: #fff;
        font-weight: 500;
    }
    .sidebar .sidebar-nav li a i {
        width: 18px;
        margin: 0;
        text-align: center;
        font-size: 15px;
        flex-shrink: 0;
    }
    .sidebar.collapsed .sidebar-nav li a {
        padding: 11px 0;
        justify-content: center;
        gap: 0;
    }
    .sidebar.collapsed .sidebar-nav li a span { display: none; }

    .sidebar .sidebar-footer {
        position: static;
        border-top: 1px solid rgba(255,255,255,0.12);
        padding: 16px 20px;
    }
    .sidebar .user-profile {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }
    .sidebar .avatar-sm {
        width: 38px;
        height: 38px;
        background: #c0392b;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        color: #fff;
        flex-shrink: 0;
    }
    .sidebar .user-details { flex: 1; min-width: 0; }
    .sidebar .user-details .uname { display: block; font-size: 13px; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sidebar .user-details .urole { display: block; font-size: 11px; color: rgba(255,255,255,0.65); }
    .sidebar.collapsed .user-details,
    .sidebar.collapsed .chevron-icon { display: none; }

    /* User dropdown (logout) */
    .sidebar .user-menu { position: relative; }
    .sidebar .user-dropdown {
        display: none;
        margin-top: 10px;
        background: rgba(0,0,0,0.18);
        border-radius: 6px;
        overflow: hidden;
    }
    .sidebar .user-dropdown.open { display: block; }
    .sidebar .user-dropdown button {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 10px 14px;
        background: none;
        border: none;
        color: rgba(255,255,255,0.85);
        font-size: 13px;
        cursor: pointer;
        text-align: left;
    }
    .sidebar .user-dropdown button:hover { background: rgba(255,255,255,0.12); color: #fff; }
    .sidebar .user-dropdown button i { width: 16px; text-align: center; }
    .sidebar.collapsed .user-dropdown { display: none !important; }
    .sidebar .user-profile .chevron-icon { transition: transform 0.2s; }
    .sidebar .user-menu.open .chevron-icon { transform: rotate(180deg); }

    /* Align page content with the 220px sidebar */
    .main-content { margin-left: 220px; }
    .sidebar.collapsed ~ .main-content { margin-left: 70px; }

    @media (max-width: 900px) {
        .sidebar { width: 70px; }
        .sidebar .logo-text,
        .sidebar .sidebar-nav li a span,
        .sidebar .user-details,
        .sidebar .chevron-icon { display: none; }
        .sidebar .sidebar-nav li a { padding: 11px 0; justify-content: center; gap: 0; }
        .main-content { margin-left: 70px; }
    }

    /* ── Desktop: hide all mobile-only elements ── */
    @media (min-width: 769px) {
        .bottom-nav,
        .more-drawer,
        .more-drawer-overlay,
        .mobile-app-header { display: none !important; }
    }

    /* ── Mobile: hide sidebar, adjust layout ── */
    @media (max-width: 768px) {
        .sidebar { display: none !important; }
        .main-content {
            margin-left: 0 !important;
            padding: 80px 16px 90px !important;
        }
    }
</style>

<aside class="sidebar" id="sidebar">
    <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Toggle sidebar">
        <i class="fas fa-chevron-left"></i>
    </button>
    <div class="sidebar-logo">
        <div class="logo-circle">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div class="logo-text">
            <strong>CPACE</strong>
            <small>CPA Reviewer</small>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li><a href="{{ route('dashboard') }}" class="{{ $active === 'dashboard' ? 'active' : '' }}"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
        <li><a href="{{ route('subjects') }}" class="{{ $active === 'subjects' ? 'active' : '' }}"><i class="fas fa-book-open"></i><span>Subjects</span></a></li>
        <li><a href="{{ route('adaptive-quizzes') }}" class="{{ $active === 'quizzes' ? 'active' : '' }}"><i class="fas fa-pen-fancy"></i><span>Quizzes</span></a></li>
        <li><a href="{{ route('mock-exams') }}" class="{{ $active === 'mock-exams' ? 'active' : '' }}"><i class="fas fa-file-alt"></i><span>Mock Exams</span></a></li>
        <li><a href="{{ route('performance') }}" class="{{ $active === 'performance' ? 'active' : '' }}"><i class="fas fa-chart-bar"></i><span>Performance</span></a></li>
        <li><a href="{{ route('review-notes') }}" class="{{ $active === 'review-notes' ? 'active' : '' }}"><i class="fas fa-sticky-note"></i><span>Review Notes</span></a></li>
        <li><a href="#"><i class="fas fa-layer-group"></i><span>Flashcards</span></a></li>
        <li><a href="{{ route('calendar') }}" class="{{ $active === 'calendar' ? 'active' : '' }}"><i class="fas fa-calendar-alt"></i><span>Calendar</span></a></li>
        <li><a href="#"><i class="fas fa-chart-line"></i><span>Progress</span></a></li>
        <li><a href="{{ route('achievements') }}" class="{{ $active === 'achievements' ? 'active' : '' }}"><i class="fas fa-trophy"></i><span>Achievements</span></a></li>
        <li><a href="#"><i class="fas fa-cog"></i><span>Settings</span></a></li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-menu" id="userMenu">
            <div class="user-profile" onclick="document.getElementById('userMenu').classList.toggle('open'); document.getElementById('userDropdown').classList.toggle('open');">
                <div class="avatar-sm">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', Auth::user()->name)[array_key_last(explode(' ', Auth::user()->name))], 0, 1)) }}
                </div>
                <div class="user-details">
                    <span class="uname">{{ Auth::user()->name }}</span>
                    <span class="urole">Reviewer</span>
                </div>
                <i class="fas fa-chevron-down chevron-icon" style="color:rgba(255,255,255,0.6); font-size:11px;"></i>
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
