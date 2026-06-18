{{--
    Program Chair (Admin) portal sidebar + shared CSS for all chair pages.
    Usage: @include('partials.chair-sidebar', ['active' => 'dashboard'])
    Valid $active keys: dashboard, faculty, subjects
--}}
@php $active = $active ?? ''; @endphp

<style>
    :root {
        --primary: #7B1D1D;
        --primary-hover: #6a1818;
        --primary-light: #f5e8e8;
        --accent: #c0392b;
        --green: #10b981;
        --blue: #3b82f6;
        --orange: #f59e0b;
        --purple: #8b5cf6;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

    /* ── SIDEBAR ── */
    .sidebar {
        background: #6B1A1A;
        background: linear-gradient(180deg, #7B1D1D 0%, #5a1515 100%);
        position: fixed;
        top: 0; left: 0;
        width: 230px; height: 100vh;
        display: flex; flex-direction: column;
        overflow-y: auto; overflow-x: hidden;
        z-index: 1000;
        transition: width 0.28s cubic-bezier(.4,0,.2,1);
        box-shadow: 4px 0 24px rgba(0,0,0,0.18);
    }
    .sidebar.collapsed { width: 68px; }

    /* Logo doubles as collapse toggle */
    .sidebar .sidebar-logo {
        display: flex; align-items: center; gap: 8px;
        padding: 14px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        flex-shrink: 0;
        cursor: pointer; user-select: none;
        transition: opacity 0.15s;
    }
    .sidebar .sidebar-logo:hover { opacity: 0.85; }
    .sidebar .logo-icon {
        width: 52px; height: 52px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; overflow: hidden;
    }
    .sidebar .logo-icon img { width: 100%; height: 100%; object-fit: contain; }
    .sidebar .logo-text { line-height: 1.2; }
    .sidebar .logo-text strong { display: block; font-size: 19px; font-weight: 700; letter-spacing: 0.6px; color: #fff; }
    .sidebar .logo-text small  { font-size: 10px; color: rgba(255,255,255,0.55); font-style: italic; white-space: nowrap; }
    .sidebar .portal-badge {
        display: inline-block; margin-top: 4px;
        background: rgba(255,255,255,0.18); color: #fff;
        font-size: 9px; font-weight: 700;
        padding: 2px 8px; border-radius: 20px;
        letter-spacing: 1px; text-transform: uppercase;
    }
    .sidebar.collapsed .logo-text { display: none; }
    .sidebar.collapsed .sidebar-logo { justify-content: center; padding: 14px 0 18px; }

    .sidebar .sidebar-nav { list-style: none; flex: 1; margin: 0; padding: 8px 0 0; }
    .sidebar .sidebar-nav li { list-style: none; }
    .sidebar .nav-label {
        font-size: 9.5px; font-weight: 700; letter-spacing: 1.2px;
        text-transform: uppercase; color: rgba(255,255,255,0.35);
        padding: 18px 22px 6px; white-space: nowrap; overflow: hidden;
    }
    .sidebar.collapsed .nav-label { visibility: hidden; padding: 14px 0 4px; }

    .sidebar .sidebar-nav li a {
        display: flex; align-items: center; gap: 11px;
        margin: 1px 10px; padding: 9px 12px;
        border: 0; border-radius: 8px;
        color: rgba(255,255,255,0.65);
        text-decoration: none; font-size: 13px; font-weight: 400;
        transition: background 0.18s, color 0.18s;
        white-space: nowrap;
    }
    .sidebar .sidebar-nav li a:hover { color: #fff; background: rgba(255,255,255,0.1); }
    .sidebar .sidebar-nav li a.active { color: #fff; background: rgba(255,255,255,0.16); font-weight: 500; }
    .sidebar .sidebar-nav li a.active i { color: #ffb3b3; }
    .sidebar .sidebar-nav li a i {
        width: 17px; text-align: center; font-size: 14px; flex-shrink: 0;
        color: rgba(255,255,255,0.5); transition: color 0.18s;
    }
    .sidebar .sidebar-nav li a:hover i { color: rgba(255,255,255,0.85); }
    .sidebar.collapsed .sidebar-nav li a {
        margin: 1px 6px; padding: 10px 0;
        justify-content: center; gap: 0;
    }
    .sidebar.collapsed .sidebar-nav li a span { display: none; }

    .sidebar .sidebar-footer { border-top: 1px solid rgba(255,255,255,0.1); padding: 14px 12px; flex-shrink: 0; }
    .sidebar .user-menu { position: relative; }
    .sidebar .user-profile {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 10px; border-radius: 8px;
        cursor: pointer; transition: background 0.18s;
    }
    .sidebar .user-profile:hover { background: rgba(255,255,255,0.08); }
    .sidebar .avatar-sm {
        width: 34px; height: 34px;
        background: rgba(255,255,255,0.18); border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 12px; color: #fff;
        flex-shrink: 0; letter-spacing: 0.5px;
        position: relative; overflow: hidden;
    }
    .sidebar .avatar-sm img {
        position: absolute; inset: 0;
        width: 100%; height: 100%;
        object-fit: cover; border-radius: inherit;
    }
    .sidebar .avatar-default {
        display: flex; align-items: center; justify-content: center;
        width: 100%; height: 100%;
        font-size: inherit; font-weight: inherit;
        color: inherit; letter-spacing: inherit; line-height: 1;
    }
    .sidebar .user-details { flex: 1; min-width: 0; }
    .sidebar .user-details .uname { display: block; font-size: 12.5px; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sidebar .user-details .urole { display: block; font-size: 10.5px; color: rgba(255,255,255,0.5); margin-top: 1px; }
    .sidebar.collapsed .user-details,
    .sidebar.collapsed .chevron-icon { display: none; }
    .sidebar.collapsed .user-profile { justify-content: center; padding: 8px 0; }
    .sidebar .chevron-icon { transition: transform 0.2s; color: rgba(255,255,255,0.4); font-size: 10px; }
    .sidebar .user-menu.open .chevron-icon { transform: rotate(180deg); }
    .sidebar .user-dropdown { display: none; margin-top: 6px; background: rgba(0,0,0,0.22); border-radius: 8px; overflow: hidden; }
    .sidebar .user-dropdown.open { display: block; }
    .sidebar .user-dropdown button {
        display: flex; align-items: center; gap: 9px;
        width: 100%; padding: 9px 14px;
        background: none; border: none; color: rgba(255,255,255,0.8);
        font-size: 12.5px; cursor: pointer; text-align: left;
        font-family: 'Poppins', sans-serif; transition: background 0.15s, color 0.15s;
    }
    .sidebar .user-dropdown button:hover { background: rgba(255,255,255,0.1); color: #fff; }
    .sidebar .user-dropdown button i { width: 15px; text-align: center; color: rgba(255,255,255,0.55); }
    .sidebar.collapsed .user-dropdown { display: none !important; }

    /* MAIN LAYOUT */
    .main { margin-left: 230px; padding: 26px 30px; transition: margin-left 0.28s cubic-bezier(.4,0,.2,1); }
    .sidebar.collapsed ~ .main { margin-left: 68px; }

    .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; gap:16px; }
    .topbar-left { display:flex; align-items:center; gap:12px; }
    .page-title { font-size:26px; font-weight:700; color:#1a1a1a; }
    .page-sub { font-size:12px; color:#999; margin-top:2px; }
    .topbar-right { display:flex; align-items:center; gap:12px; }

    /* BUTTONS */
    .btn {
        display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px;
        font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer;
        border:none; text-decoration:none; transition:all .2s;
    }
    .btn-primary { background:var(--primary); color:white; }
    .btn-primary:hover { background:var(--primary-hover); }
    .btn-outline { background:white; color:var(--primary); border:1.5px solid var(--primary); }
    .btn-outline:hover { background:var(--primary-light); }
    .btn-sm { padding:6px 12px; font-size:12px; }
    .btn-ghost { background:#f1f1f3; color:#555; }
    .btn-ghost:hover { background:#e6e6e9; }

    /* CARDS */
    .card { background:white; border-radius:14px; padding:22px; }
    .card + .card { margin-top:18px; }
    .card-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
    .card-title { font-size:14px; font-weight:600; color:#1a1a1a; }
    .card-link { font-size:12px; color:var(--accent); text-decoration:none; font-weight:500; }

    /* STATS */
    .stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:22px; }
    .stat-card { background:white; border-radius:14px; padding:20px 22px; }
    .stat-top { display:flex; justify-content:space-between; align-items:flex-start; }
    .stat-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; }
    .si-red { background:#fde8e8; color:var(--accent); }
    .si-green { background:#d1fae5; color:var(--green); }
    .si-blue { background:#dbeafe; color:var(--blue); }
    .si-orange { background:#fef3c7; color:var(--orange); }
    .stat-num { font-size:28px; font-weight:700; color:#1a1a1a; line-height:1; margin-bottom:4px; }
    .stat-lbl { font-size:11px; color:#999; }

    /* TABLE */
    table { width:100%; border-collapse:collapse; }
    thead th { text-align:left; font-size:11px; color:#aaa; font-weight:600; padding:0 10px 12px; text-transform:uppercase; letter-spacing:.4px; }
    tbody tr { border-top:1px solid #f5f5f5; }
    tbody td { padding:12px 10px; font-size:13px; vertical-align:middle; }
    tbody tr:hover { background:#fafafa; }

    /* BADGES */
    .subj-badge { display:inline-block; padding:3px 9px; border-radius:5px; font-size:10px; font-weight:700; margin:2px 3px 2px 0; }
    .b-far  { background:#dbeafe; color:#2563eb; }
    .b-afar { background:#cffafe; color:#0891b2; }
    .b-ms   { background:#ede9fe; color:#7c3aed; }
    .b-tax  { background:#d1fae5; color:#059669; }
    .b-aud  { background:#fce7f3; color:#db2777; }
    .b-rfbt { background:#fef3c7; color:#d97706; }
    .pill { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:20px; font-size:10px; font-weight:700; }
    .pill-on  { background:#d1fae5; color:#059669; }
    .pill-off { background:#f3f4f6; color:#9ca3af; }

    /* USER AVATAR (initials) */
    .user-av {
        width:38px; height:38px; flex-shrink:0; border-radius:50%;
        display:inline-flex; align-items:center; justify-content:center;
        background:var(--primary); color:#fff; font-size:13px; font-weight:700;
        line-height:1; text-transform:uppercase; letter-spacing:.3px;
    }

    /* FORMS */
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .form-group { margin-bottom:16px; }
    .form-group.full { grid-column:1 / -1; }
    label { display:block; font-size:12px; font-weight:600; color:#444; margin-bottom:6px; }
    input[type=text], input[type=email], input[type=password], select {
        width:100%; padding:10px 12px; border:1.5px solid #e2e2e6; border-radius:8px;
        font-size:13px; font-family:'Poppins',sans-serif; color:#333; background:#fff;
    }
    input:focus, select:focus { outline:none; border-color:var(--primary); }
    .hint { font-size:11px; color:#aaa; margin-top:5px; }

    .check-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; }
    .check-card {
        display:flex; align-items:center; gap:10px; padding:12px 14px;
        border:1.5px solid #e6e6ea; border-radius:10px; cursor:pointer; transition:all .15s;
    }
    .check-card:hover { border-color:var(--primary); background:var(--primary-light); }
    .check-card input { width:17px; height:17px; accent-color:var(--primary); cursor:pointer; }
    .check-card .cc-code { font-size:11px; font-weight:700; color:var(--primary); }
    .check-card .cc-name { font-size:12px; color:#555; }

    /* ALERTS */
    .alert { padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:18px; display:flex; align-items:center; gap:10px; }
    .alert-success { background:#d1fae5; color:#065f46; }
    .alert-error   { background:#fde8e8; color:#991b1b; }
    .alert ul { margin:0; padding-left:18px; }

    .empty { text-align:center; padding:40px 20px; color:#aaa; }
    .empty i { font-size:34px; margin-bottom:10px; color:#ddd; }

    @media (max-width: 900px) {
        .sidebar { width: 68px; }
        .sidebar .logo-text,
        .sidebar .portal-badge,
        .sidebar .sidebar-nav li a span,
        .sidebar .user-details,
        .sidebar .chevron-icon,
        .sidebar .nav-label { display: none; }
        .sidebar .sidebar-nav li a { margin: 1px 6px; padding: 10px 0; justify-content: center; gap: 0; }
        .sidebar .sidebar-logo { justify-content: center; padding: 24px 0 22px; }
        .sidebar .user-profile { justify-content: center; padding: 8px 0; }
        .main { margin-left: 68px; }
        .stats-row, .form-grid, .check-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 768px) {
        .sidebar { display: none !important; }
        .main { margin-left: 0 !important; padding: 16px !important; }
        .topbar { flex-direction: column; align-items: flex-start; gap: 10px; }
        .topbar-right { width: 100%; justify-content: flex-end; flex-wrap: wrap; gap: 8px; }
        .page-title { font-size: 20px !important; }
        .stats-row { grid-template-columns: repeat(2, 1fr) !important; }
    }
    @media (max-width: 480px) {
        .stats-row { grid-template-columns: 1fr !important; }
        .page-sub { display: none; }
        .topbar-search input { width: 140px; }
    }

    /* ── Topbar search + avatar ── */
    .topbar-search { position: relative; }
    .topbar-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #bbb; font-size: 13px; pointer-events: none; }
    .topbar-search input {
        width: 220px; padding: 9px 14px 9px 34px;
        border: 1px solid #e5e7eb; border-radius: 22px;
        font-size: 12.5px; font-family: 'Poppins', sans-serif;
        background: #f9fafb; color: #374151; outline: none;
        transition: border-color .2s, background .2s;
    }
    .topbar-search input:focus { border-color: var(--primary); background: #fff; }
    .topbar-search input::placeholder { color: #bbb; }

    .topbar-avatar-wrap { position: relative; }
    .topbar-avatar-btn {
        width: 38px; height: 38px; border-radius: 10px; border: none;
        background: var(--primary); color: #fff;
        font-weight: 700; font-size: 13px; font-family: 'Poppins', sans-serif;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        position: relative; overflow: hidden; transition: background .2s;
    }
    .topbar-avatar-btn:hover { background: var(--primary-hover); }
    .topbar-avatar-btn img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; border-radius: inherit; }
    .topbar-avatar-btn .avatar-default { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; font-size: inherit; font-weight: inherit; color: inherit; line-height: 1; }

    .topbar-dropdown {
        display: none; position: absolute;
        top: calc(100% + 8px); right: 0;
        background: #fff; border: 1px solid #e2e8f0;
        border-radius: 10px; min-width: 185px;
        box-shadow: 0 6px 20px rgba(0,0,0,.12);
        z-index: 2000; overflow: hidden;
    }
    .topbar-dropdown.open { display: block; }
    .topbar-dropdown a,
    .topbar-dropdown button {
        display: flex; align-items: center; gap: 10px;
        padding: 11px 16px; font-size: 13px;
        font-family: 'Poppins', sans-serif;
        text-decoration: none; color: #1a202c;
        background: none; border: none; width: 100%;
        text-align: left; cursor: pointer;
        transition: background .2s;
        border-bottom: 1px solid #f5f5f5;
    }
    .topbar-dropdown a:last-child,
    .topbar-dropdown form:last-child button { border-bottom: none; }
    .topbar-dropdown a:hover,
    .topbar-dropdown button:hover { background: #f7fafc; }
    .topbar-dropdown a i,
    .topbar-dropdown button i { color: var(--primary); width: 16px; text-align: center; }
    .tda-logout { color: #e53e3e !important; }
    .tda-logout i { color: #e53e3e !important; }
</style>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo" id="sidebarCollapseBtn" title="Toggle sidebar">
        <div class="logo-icon">
            <img src="{{ asset('images/cpace_logo.png') }}" alt="CPACE Logo">
        </div>
        <div class="logo-text">
            <strong>CPACE</strong>
            <small>CPA Reviewer</small>
            <div class="portal-badge">Program Chair</div>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-label">Main</li>
        <li><a href="{{ route('chair.dashboard') }}" class="{{ $active === 'dashboard' ? 'active' : '' }}"><i class="fas fa-gauge-high"></i><span>Dashboard</span></a></li>

        <li class="nav-label">Management</li>
        <li><a href="{{ route('chair.faculty') }}" class="{{ $active === 'faculty' ? 'active' : '' }}"><i class="fas fa-chalkboard-user"></i><span>Faculty Accounts</span></a></li>
        <li><a href="{{ route('chair.subjects') }}" class="{{ $active === 'subjects' ? 'active' : '' }}"><i class="fas fa-layer-group"></i><span>Subject Assignments</span></a></li>
        <li><a href="{{ route('chair.faculty.create') }}"><i class="fas fa-user-plus"></i><span>Add Faculty</span></a></li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-menu" id="userMenu">
            <div class="user-profile" onclick="document.getElementById('userMenu').classList.toggle('open'); document.getElementById('userDropdown').classList.toggle('open');">
                <div class="avatar-sm">
                    @include('partials.avatar-content')
                </div>
                <div class="user-details">
                    <span class="uname">{{ Auth::user()->name }}</span>
                    <span class="urole">Program Chair</span>
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
    if (!sidebar) return;

    if (localStorage.getItem('chairSidebar') === 'true') {
        sidebar.classList.add('collapsed');
    }

    function toggle() {
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('chairSidebar', sidebar.classList.contains('collapsed'));
    }

    const logo = document.getElementById('sidebarCollapseBtn');
    if (logo) logo.addEventListener('click', function(e) { e.stopPropagation(); toggle(); });

    /* topbar avatar dropdown */
    document.addEventListener('DOMContentLoaded', function() {
        const avatarBtn = document.getElementById('topbarAvatarBtn');
        const dropdown  = document.getElementById('topbarDropdown');
        if (avatarBtn && dropdown) {
            avatarBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.classList.toggle('open');
            });
            document.addEventListener('click', function() { dropdown.classList.remove('open'); });
        }
    });
})();
</script>
