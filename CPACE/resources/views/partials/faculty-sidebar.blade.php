{{--
    Faculty portal sidebar + shared sidebar CSS for all faculty pages.
    Usage: @include('partials.faculty-sidebar', ['active' => 'dashboard'])
    Valid $active keys: dashboard, test-bank, add-question, subjects, performance
--}}
@php $active = $active ?? ''; @endphp

<style>
    /* ─── SHARED FACULTY SIDEBAR (keeps all faculty pages consistent) ─── */
    .sidebar { background:var(--primary); position:fixed; width:230px; height:100vh; display:flex; flex-direction:column; overflow-y:auto; overflow-x:hidden; z-index:1000; transition:width .3s; }
    .sidebar.collapsed { width:70px; }
    .sidebar-header { padding:20px 18px 16px; border-bottom:1px solid rgba(255,255,255,.12); }
    .sidebar-brand { display:flex; align-items:center; gap:10px; }
    .brand-circle { width:42px; height:42px; flex-shrink:0; background:rgba(255,255,255,.15); border:2px solid rgba(255,255,255,.3); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px; color:white; }
    .brand-text strong { display:block; font-size:14px; font-weight:700; color:white; }
    .brand-text small { font-size:10px; color:rgba(255,255,255,.7); }
    .faculty-badge { display:inline-block; margin-top:6px; background:rgba(255,255,255,.2); color:white; font-size:9px; font-weight:700; padding:2px 8px; border-radius:20px; letter-spacing:1px; text-transform:uppercase; }
    .sidebar.collapsed .brand-text, .sidebar.collapsed .faculty-badge { display:none; }

    .sidebar-nav { list-style:none; flex:1; padding:10px 0; }
    .sidebar-nav .nav-group-label { padding:14px 22px 4px; font-size:9px; font-weight:700; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:1.2px; }
    .sidebar.collapsed .nav-group-label { display:none; }
    .sidebar-nav li { list-style:none; }
    .sidebar-nav li a { display:flex; align-items:center; gap:12px; padding:10px 22px; color:rgba(255,255,255,.72); text-decoration:none; font-size:13px; border-left:3px solid transparent; transition:all .2s; }
    .sidebar-nav li a:hover { color:white; background:rgba(255,255,255,.1); }
    .sidebar-nav li a.active { color:white; background:rgba(255,255,255,.18); border-left-color:white; font-weight:500; }
    .sidebar-nav li a i { width:18px; text-align:center; font-size:14px; flex-shrink:0; }
    .sidebar.collapsed .sidebar-nav li a { padding:10px 0; justify-content:center; gap:0; }
    .sidebar.collapsed .sidebar-nav li a span { display:none; }
    .nav-badge { margin-left:auto; background:var(--accent); color:white; font-size:9px; font-weight:700; padding:2px 6px; border-radius:20px; min-width:18px; text-align:center; }
    .sidebar.collapsed .nav-badge { display:none; }

    .sidebar-footer { border-top:1px solid rgba(255,255,255,.12); padding:14px 18px; }
    .user-menu { position:relative; }
    .user-row { display:flex; align-items:center; gap:10px; cursor:pointer; }
    .user-av { width:36px; height:36px; border-radius:50%; background:var(--accent); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; color:white; flex-shrink:0; }
    .user-info { flex:1; min-width:0; }
    .user-info .un { display:block; font-size:12px; font-weight:600; color:white; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .user-info .ur { display:block; font-size:10px; color:rgba(255,255,255,.6); }
    .sidebar.collapsed .user-info, .sidebar.collapsed .user-chevron { display:none; }
    .user-chevron { transition:transform .2s; }
    .user-menu.open .user-chevron { transform:rotate(180deg); }
    .user-dropdown { display:none; margin-top:10px; background:rgba(0,0,0,.18); border-radius:6px; overflow:hidden; }
    .user-dropdown.open { display:block; }
    .user-dropdown button { display:flex; align-items:center; gap:10px; width:100%; padding:10px 14px; background:none; border:none; color:rgba(255,255,255,.85); font-size:13px; cursor:pointer; text-align:left; font-family:'Poppins',sans-serif; }
    .user-dropdown button:hover { background:rgba(255,255,255,.12); color:#fff; }
    .user-dropdown button i { width:16px; text-align:center; }
    .sidebar.collapsed .user-dropdown { display:none !important; }
</style>

@php
    $u = Auth::user();
    $initials = strtoupper(substr($u->first_name ?? $u->name, 0, 1)) . strtoupper(substr($u->last_name ?? '', 0, 1));
    if (trim($initials) === '') { $initials = strtoupper(substr($u->name, 0, 2)); }
@endphp

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-circle"><i class="fas fa-shield-alt"></i></div>
            <div class="brand-text"><strong>CPACE</strong><small>CPA Reviewer</small></div>
        </div>
        <div class="faculty-badge">Faculty Portal</div>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-group-label">Main</li>
        <li><a href="{{ route('faculty.dashboard') }}" class="{{ $active === 'dashboard' ? 'active' : '' }}"><i class="fas fa-home"></i><span>Dashboard</span></a></li>

        <li class="nav-group-label">Content</li>
        <li><a href="{{ route('faculty.test-bank') }}" class="{{ $active === 'test-bank' ? 'active' : '' }}"><i class="fas fa-database"></i><span>Test Bank</span></a></li>
        <li><a href="{{ route('faculty.question.create') }}" class="{{ $active === 'add-question' ? 'active' : '' }}"><i class="fas fa-plus-circle"></i><span>Add Question</span></a></li>
        <li><a href="{{ route('faculty.subjects') }}" class="{{ $active === 'subjects' ? 'active' : '' }}"><i class="fas fa-book-open"></i><span>Subjects &amp; Topics</span></a></li>

        <li class="nav-group-label">Analytics</li>
        <li><a href="{{ route('faculty.performance') }}" class="{{ $active === 'performance' ? 'active' : '' }}"><i class="fas fa-users"></i><span>Student Performance</span></a></li>
        <li><a href="#"><i class="fas fa-chart-line"></i><span>Reports</span></a></li>

        <li class="nav-group-label">System</li>
        <li><a href="#"><i class="fas fa-cog"></i><span>Settings</span></a></li>
        <li><a href="{{ route('dashboard') }}"><i class="fas fa-arrow-left"></i><span>Student View</span></a></li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-menu" id="userMenu">
            <div class="user-row" onclick="document.getElementById('userMenu').classList.toggle('open'); document.getElementById('userDropdown').classList.toggle('open');">
                <div class="user-av">{{ $initials }}</div>
                <div class="user-info">
                    <span class="un">{{ $u->name }}</span>
                    <span class="ur">Faculty</span>
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
