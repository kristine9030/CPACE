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

    /* SIDEBAR */
    .sidebar {
        background: var(--primary);
        position: fixed; width:230px; height:100vh;
        display:flex; flex-direction:column;
        overflow-y:auto; overflow-x:hidden;
        z-index:1000; transition:width .3s;
    }
    .sidebar.collapsed { width:70px; }
    .sidebar-header { padding:20px 18px 16px; border-bottom:1px solid rgba(255,255,255,.12); }
    .sidebar-brand { display:flex; align-items:center; gap:10px; }
    .brand-circle {
        width:42px; height:42px; flex-shrink:0;
        background:rgba(255,255,255,.15); border:2px solid rgba(255,255,255,.3);
        border-radius:50%; display:flex; align-items:center; justify-content:center;
        font-size:18px; color:white;
    }
    .brand-text strong { display:block; font-size:14px; font-weight:700; color:white; }
    .brand-text small  { font-size:10px; color:rgba(255,255,255,.7); }
    .role-badge {
        display:inline-block; margin-top:6px; background:rgba(255,255,255,.2);
        color:white; font-size:9px; font-weight:700; padding:2px 8px;
        border-radius:20px; letter-spacing:1px; text-transform:uppercase;
    }
    .sidebar.collapsed .brand-text,
    .sidebar.collapsed .role-badge { display:none; }

    .sidebar-nav { list-style:none; flex:1; padding:10px 0; }
    .sidebar-nav .nav-group-label {
        padding:14px 22px 4px; font-size:9px; font-weight:700;
        color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:1.2px;
    }
    .sidebar.collapsed .nav-group-label { display:none; }
    .sidebar-nav li { list-style:none; }
    .sidebar-nav li a {
        display:flex; align-items:center; gap:12px; padding:10px 22px;
        color:rgba(255,255,255,.72); text-decoration:none; font-size:13px;
        border-left:3px solid transparent; transition:all .2s;
    }
    .sidebar-nav li a:hover { color:white; background:rgba(255,255,255,.1); }
    .sidebar-nav li a.active { color:white; background:rgba(255,255,255,.18); border-left-color:white; font-weight:500; }
    .sidebar-nav li a i { width:18px; text-align:center; font-size:14px; flex-shrink:0; }
    .sidebar.collapsed .sidebar-nav li a { padding:10px 0; justify-content:center; gap:0; }
    .sidebar.collapsed .sidebar-nav li a span { display:none; }

    .sidebar-footer { border-top:1px solid rgba(255,255,255,.12); padding:14px 18px; }
    .user-menu { position:relative; }
    .user-row { display:flex; align-items:center; gap:10px; cursor:pointer; }
    .user-av {
        width:36px; height:36px; border-radius:50%; background:var(--accent);
        display:flex; align-items:center; justify-content:center;
        font-weight:700; font-size:12px; color:white; flex-shrink:0;
    }
    .user-info { flex:1; min-width:0; }
    .user-info .un { display:block; font-size:12px; font-weight:600; color:white; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .user-info .ur { display:block; font-size:10px; color:rgba(255,255,255,.6); }
    .sidebar.collapsed .user-info, .sidebar.collapsed .user-chevron { display:none; }
    .user-dropdown { display:none; margin-top:10px; background:rgba(0,0,0,.18); border-radius:6px; overflow:hidden; }
    .user-dropdown.open { display:block; }
    .user-dropdown button {
        display:flex; align-items:center; gap:10px; width:100%; padding:10px 14px;
        background:none; border:none; color:rgba(255,255,255,.85); font-size:13px;
        cursor:pointer; text-align:left; font-family:'Poppins',sans-serif;
    }
    .user-dropdown button:hover { background:rgba(255,255,255,.12); color:#fff; }
    .sidebar.collapsed .user-dropdown { display:none !important; }

    /* MAIN LAYOUT */
    .main { margin-left:230px; padding:26px 30px; transition:margin-left .3s; }
    .sidebar.collapsed ~ .main { margin-left:70px; }

    .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; gap:16px; }
    .topbar-left { display:flex; align-items:center; gap:12px; }
    .toggle-btn {
        width:36px; height:36px; border:1px solid #ddd; background:white; border-radius:8px;
        cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--primary); font-size:15px;
    }
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

    @media (max-width:900px) {
        .sidebar { width:70px; }
        .sidebar .brand-text, .sidebar-nav li a span, .user-info, .user-chevron, .role-badge { display:none; }
        .sidebar-nav li a { padding:10px 0; justify-content:center; gap:0; }
        .main { margin-left:70px; }
        .stats-row, .form-grid, .check-grid { grid-template-columns:1fr; }
    }
</style>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-circle"><i class="fas fa-user-tie"></i></div>
            <div class="brand-text">
                <strong>CPACE</strong>
                <small>CPA Reviewer</small>
            </div>
        </div>
        <div class="role-badge">Program Chair</div>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-group-label">Main</li>
        <li><a href="{{ route('chair.dashboard') }}" class="{{ $active === 'dashboard' ? 'active' : '' }}"><i class="fas fa-gauge-high"></i><span>Dashboard</span></a></li>

        <li class="nav-group-label">Management</li>
        <li><a href="{{ route('chair.faculty') }}" class="{{ $active === 'faculty' ? 'active' : '' }}"><i class="fas fa-chalkboard-user"></i><span>Faculty Accounts</span></a></li>
        <li><a href="{{ route('chair.subjects') }}" class="{{ $active === 'subjects' ? 'active' : '' }}"><i class="fas fa-layer-group"></i><span>Subject Assignments</span></a></li>
        <li><a href="{{ route('chair.faculty.create') }}"><i class="fas fa-user-plus"></i><span>Add Faculty</span></a></li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-menu" id="userMenu">
            <div class="user-row" onclick="document.getElementById('userMenu').classList.toggle('open'); document.getElementById('userDropdown').classList.toggle('open');">
                <div class="user-av">{{ strtoupper(substr(Auth::user()->first_name, 0, 1)) }}{{ strtoupper(substr(Auth::user()->last_name, 0, 1)) }}</div>
                <div class="user-info">
                    <span class="un">{{ Auth::user()->name }}</span>
                    <span class="ur">Program Chair</span>
                </div>
                <i class="fas fa-chevron-down user-chevron" style="color:rgba(255,255,255,.5);font-size:10px;"></i>
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
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('sidebarToggle');
        const sb  = document.getElementById('sidebar');
        if (btn) btn.addEventListener('click', () => {
            sb.classList.toggle('collapsed');
            localStorage.setItem('chairSidebar', sb.classList.contains('collapsed'));
        });
        if (localStorage.getItem('chairSidebar') === 'true') sb.classList.add('collapsed');
    });
</script>
