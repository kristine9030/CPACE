<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Performance - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; --orange:#f59e0b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

        /* SIDEBAR */
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
        .sidebar-nav li a { display:flex; align-items:center; gap:12px; padding:10px 22px; color:rgba(255,255,255,.72); text-decoration:none; font-size:13px; border-left:3px solid transparent; transition:all .2s; }
        .sidebar-nav li a:hover { color:white; background:rgba(255,255,255,.1); }
        .sidebar-nav li a.active { color:white; background:rgba(255,255,255,.18); border-left-color:white; font-weight:500; }
        .sidebar-nav li a i { width:18px; text-align:center; font-size:14px; flex-shrink:0; }
        .sidebar.collapsed .sidebar-nav li a { padding:10px 0; justify-content:center; gap:0; }
        .sidebar.collapsed .sidebar-nav li a span { display:none; }
        .nav-badge { margin-left:auto; background:var(--accent); color:white; font-size:9px; font-weight:700; padding:2px 6px; border-radius:20px; min-width:18px; text-align:center; }
        .sidebar.collapsed .nav-badge { display:none; }
        .sidebar-footer { border-top:1px solid rgba(255,255,255,.12); padding:14px 18px; }
        .user-row { display:flex; align-items:center; gap:10px; }
        .user-av { width:36px; height:36px; border-radius:50%; background:var(--accent); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; color:white; flex-shrink:0; }
        .user-info .un { display:block; font-size:12px; font-weight:600; color:white; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .user-info .ur { display:block; font-size:10px; color:rgba(255,255,255,.6); }
        .sidebar.collapsed .user-info, .sidebar.collapsed .user-chevron { display:none; }

        /* MAIN */
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }

        /* TOPBAR */
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; }
        .topbar-left { display:flex; align-items:center; gap:12px; }
        .toggle-btn { width:36px; height:36px; border:1px solid #ddd; background:white; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--primary); font-size:15px; }
        .page-title { font-size:26px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:white; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }

        /* STATS */
        .stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
        .stat-chip { background:white; border-radius:12px; padding:16px 18px; display:flex; align-items:center; gap:14px; }
        .chip-icon { width:38px; height:38px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:17px; flex-shrink:0; }
        .chip-num { font-size:22px; font-weight:700; color:#1a1a1a; line-height:1; }
        .chip-lbl { font-size:11px; color:#999; margin-top:2px; }

        /* FILTER BAR */
        .filter-bar { background:white; border-radius:12px; padding:16px 20px; margin-bottom:18px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
        .search-wrap { position:relative; }
        .search-wrap i { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#aaa; font-size:13px; }
        .search-wrap input { font-family:'Poppins',sans-serif; font-size:13px; border:1px solid #e0e0e0; border-radius:8px; padding:8px 12px 8px 32px; color:#555; background:white; outline:none; width:220px; }
        .search-wrap input:focus { border-color:var(--primary); }
        select { font-family:'Poppins',sans-serif; font-size:13px; border:1px solid #e0e0e0; border-radius:8px; padding:8px 12px; color:#555; background:white; outline:none; }
        select:focus { border-color:var(--primary); }
        .filter-divider { width:1px; height:28px; background:#e8e8e8; }
        .filter-label { font-size:12px; color:#888; font-weight:500; }

        /* MAIN LAYOUT */
        .perf-layout { display:grid; grid-template-columns:1fr 300px; gap:18px; align-items:start; }

        /* TABLE CARD */
        .table-card { background:white; border-radius:14px; overflow:hidden; }
        .table-head-bar { padding:16px 20px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f5f5f5; }
        .count { font-size:13px; color:#888; }

        table { width:100%; border-collapse:collapse; }
        thead th { text-align:left; font-size:11px; color:#aaa; font-weight:600; padding:12px 16px; text-transform:uppercase; letter-spacing:.4px; border-bottom:1px solid #f5f5f5; background:#fafafa; }
        thead th:first-child { padding-left:20px; }
        tbody tr { border-bottom:1px solid #f8f8f8; transition:background .15s; }
        tbody tr:last-child { border-bottom:none; }
        tbody tr:hover { background:#fafafa; }
        tbody td { padding:13px 16px; font-size:13px; vertical-align:middle; }
        tbody td:first-child { padding-left:20px; }

        .student-cell { display:flex; align-items:center; gap:10px; }
        .student-av { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:white; flex-shrink:0; }
        .student-name { font-weight:600; color:#1a1a1a; font-size:13px; }
        .student-email { font-size:11px; color:#bbb; }

        .score-cell { display:flex; align-items:center; gap:10px; }
        .score-num { font-size:15px; font-weight:700; width:42px; }
        .score-bar-bg { flex:1; height:6px; background:#f0f0f0; border-radius:3px; overflow:hidden; }
        .score-bar-fill { height:100%; border-radius:3px; }

        .subj-dots { display:flex; gap:5px; flex-wrap:wrap; }
        .subj-dot { width:24px; height:24px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:9px; font-weight:700; }

        .trend-badge { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:600; padding:3px 8px; border-radius:5px; }
        .t-up   { background:#d1fae5; color:#059669; }
        .t-down { background:#fde8e8; color:var(--accent); }
        .t-flat { background:#f3f4f6; color:#9ca3af; }

        .last-active { font-size:11px; color:#aaa; }

        .view-btn { display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:7px; font-size:11px; font-weight:600; color:var(--accent); background:var(--primary-light); border:none; cursor:pointer; font-family:'Poppins',sans-serif; transition:all .2s; text-decoration:none; }
        .view-btn:hover { background:#fbd5d5; }

        /* PAGINATION */
        .pagination { padding:14px 20px; display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f5f5f5; }
        .pag-info { font-size:12px; color:#999; }
        .pag-btns { display:flex; gap:5px; }
        .pag-btn { width:30px; height:30px; border:1px solid #e0e0e0; background:white; border-radius:7px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:12px; color:#555; transition:all .2s; }
        .pag-btn.active { background:var(--primary); color:white; border-color:var(--primary); }
        .pag-btn:hover:not(.active) { background:#f5f5f5; }

        /* RIGHT PANEL */
        .right-panel { display:flex; flex-direction:column; gap:16px; }
        .side-card { background:white; border-radius:14px; padding:20px; }
        .side-title { font-size:13px; font-weight:700; color:#1a1a1a; margin-bottom:14px; }

        .at-risk-item { display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #f8f8f8; }
        .at-risk-item:last-child { border-bottom:none; }
        .at-risk-av { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:white; background:var(--accent); flex-shrink:0; }
        .at-risk-name { font-size:12px; font-weight:600; color:#1a1a1a; }
        .at-risk-score { font-size:11px; color:var(--accent); font-weight:700; }
        .at-risk-sub { font-size:10px; color:#aaa; }

        .weak-item { display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #f8f8f8; }
        .weak-item:last-child { border-bottom:none; }
        .weak-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0; }
        .weak-name { font-size:12px; font-weight:600; color:#1a1a1a; flex:1; }
        .weak-rate { font-size:12px; font-weight:700; color:var(--accent); }

        @keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
        .a0{animation:fadeUp .4s ease both} .a1{animation:fadeUp .4s .07s ease both} .a2{animation:fadeUp .4s .14s ease both}
    </style>
</head>
<body>

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
        <li><a href="{{ route('faculty.dashboard') }}"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
        <li class="nav-group-label">Content</li>
        <li><a href="{{ route('faculty.test-bank') }}"><i class="fas fa-database"></i><span>Test Bank</span><span class="nav-badge">1,543</span></a></li>
        <li><a href="{{ route('faculty.question.create') }}"><i class="fas fa-plus-circle"></i><span>Add Question</span></a></li>
        <li><a href="{{ route('faculty.subjects') }}"><i class="fas fa-book-open"></i><span>Subjects &amp; Topics</span></a></li>
        <li class="nav-group-label">Analytics</li>
        <li><a href="{{ route('faculty.performance') }}" class="active"><i class="fas fa-users"></i><span>Student Performance</span></a></li>
        <li><a href="#"><i class="fas fa-chart-line"></i><span>Reports</span></a></li>
        <li class="nav-group-label">System</li>
        <li><a href="#"><i class="fas fa-cog"></i><span>Settings</span></a></li>
        <li><a href="{{ route('dashboard') }}"><i class="fas fa-arrow-left"></i><span>Student View</span></a></li>
    </ul>
    <div class="sidebar-footer">
        <div class="user-row">
            <div class="user-av">KD</div>
            <div class="user-info">
                <span class="un">{{ Auth::user()->name }}</span>
                <span class="ur">Faculty</span>
            </div>
            <i class="fas fa-chevron-down user-chevron" style="color:rgba(255,255,255,.5);font-size:10px;"></i>
        </div>
    </div>
</aside>

<main class="main">
    <div class="topbar a0">
        <div class="topbar-left">
            <button class="toggle-btn" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <div>
                <div class="page-title">Student Performance</div>
                <div class="page-sub">Monitor student progress and identify students that need help.</div>
            </div>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
            <button class="btn btn-ghost"><i class="fas fa-file-export"></i> Export</button>
            <button class="btn btn-primary"><i class="fas fa-envelope"></i> Send Report</button>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-ghost" style="color:#c0392b;border-color:#c0392b;">
                    <i class="fas fa-sign-out-alt"></i> Sign Out
                </button>
            </form>
        </div>
    </div>

    <!-- STATS -->
    <div class="stats-row a1">
        <div class="stat-chip">
            <div class="chip-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-users"></i></div>
            <div><div class="chip-num">48</div><div class="chip-lbl">Active Students</div></div>
        </div>
        <div class="stat-chip">
            <div class="chip-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-chart-bar"></i></div>
            <div><div class="chip-num">72%</div><div class="chip-lbl">Avg. Score</div></div>
        </div>
        <div class="stat-chip">
            <div class="chip-icon" style="background:#fde8e8;color:var(--accent);"><i class="fas fa-exclamation-triangle"></i></div>
            <div><div class="chip-num">7</div><div class="chip-lbl">At Risk Students</div></div>
        </div>
        <div class="stat-chip">
            <div class="chip-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-trophy"></i></div>
            <div><div class="chip-num">92%</div><div class="chip-lbl">Top Score</div></div>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-bar a1">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search students...">
        </div>
        <div class="filter-divider"></div>
        <span class="filter-label">Subject</span>
        <select>
            <option>All Subjects</option>
            <option>FAR</option><option>AUD</option><option>TAX</option>
            <option>MS</option><option>RFBT</option><option>AFAR</option>
        </select>
        <span class="filter-label">Period</span>
        <select>
            <option>Last 30 Days</option>
            <option>Last 7 Days</option>
            <option>Last 3 Months</option>
            <option>All Time</option>
        </select>
        <span class="filter-label">Sort by</span>
        <select>
            <option>Avg Score (Desc)</option>
            <option>Avg Score (Asc)</option>
            <option>Most Active</option>
            <option>Name A-Z</option>
        </select>
    </div>

    <!-- LAYOUT -->
    <div class="perf-layout a2">
        <!-- TABLE -->
        <div class="table-card">
            <div class="table-head-bar">
                <span class="count">Showing <strong>48</strong> students</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Avg. Score</th>
                        <th>Subjects Covered</th>
                        <th>Quizzes</th>
                        <th>Trend</th>
                        <th>Last Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $students = [
                        ['Maria Santos',   'MS', '#e8567d', 92, 6, 38, 'up',   '2h ago'],
                        ['Ana Reyes',      'AR', '#3b82f6', 88, 5, 31, 'up',   '4h ago'],
                        ['Juan dela Cruz', 'JD', '#27ae60', 85, 6, 45, 'flat', '6h ago'],
                        ['Mark Flores',    'MF', '#f59e0b', 79, 4, 22, 'up',   '1d ago'],
                        ['Carla Mendoza',  'CM', '#8b5cf6', 75, 5, 29, 'down', '1d ago'],
                        ['Rico Santos',    'RS', '#17a2b8', 71, 3, 17, 'flat', '2d ago'],
                        ['Joy Ocampo',     'JO', '#e8567d', 68, 4, 20, 'down', '3d ago'],
                        ['Leo Ramos',      'LR', '#f59e0b', 62, 3, 14, 'down', '4d ago'],
                        ['Nina Cruz',      'NC', '#27ae60', 58, 2, 11, 'up',   '5d ago'],
                        ['Carlo Garcia',   'CG', '#c0392b', 44, 2, 9,  'down', '6d ago'],
                    ];
                    @endphp
                    @foreach($students as $st)
                    <tr>
                        <td>
                            <div class="student-cell">
                                <div class="student-av" style="background:{{ $st[2] }};">{{ $st[1] }}</div>
                                <div>
                                    <div class="student-name">{{ $st[0] }}</div>
                                    <div class="student-email">{{ strtolower(str_replace(' ','.',explode(' ',$st[0])[0])) }}@student.cpace.edu</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="score-cell">
                                <span class="score-num" style="color:{{ $st[3] >= 75 ? '#059669' : ($st[3] >= 60 ? '#d97706' : '#c0392b') }};">{{ $st[3] }}%</span>
                                <div class="score-bar-bg">
                                    <div class="score-bar-fill" style="width:{{ $st[3] }}%;background:{{ $st[3] >= 75 ? '#10b981' : ($st[3] >= 60 ? '#f59e0b' : '#c0392b') }};"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="subj-dots">
                                @php $subjs=[['FAR','#3b82f6'],['AUD','#e8567d'],['TAX','#27ae60'],['MS','#8b5cf6'],['RFBT','#f59e0b'],['AFAR','#17a2b8']]; @endphp
                                @foreach(array_slice($subjs, 0, $st[4]) as $sb)
                                    <div class="subj-dot" style="background:{{ $sb[1] }}20;color:{{ $sb[1] }};">{{ $sb[0] }}</div>
                                @endforeach
                                @if($st[4] < 6)
                                    <div class="subj-dot" style="background:#f0f0f0;color:#bbb;">+{{ 6-$st[4] }}</div>
                                @endif
                            </div>
                        </td>
                        <td style="font-size:13px;font-weight:600;color:#1a1a1a;">{{ $st[5] }}</td>
                        <td>
                            @if($st[6] === 'up')
                                <span class="trend-badge t-up"><i class="fas fa-arrow-up"></i> Up</span>
                            @elseif($st[6] === 'down')
                                <span class="trend-badge t-down"><i class="fas fa-arrow-down"></i> Down</span>
                            @else
                                <span class="trend-badge t-flat"><i class="fas fa-minus"></i> Flat</span>
                            @endif
                        </td>
                        <td><span class="last-active">{{ $st[7] }}</span></td>
                        <td><a href="#" class="view-btn"><i class="fas fa-eye"></i> View</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination">
                <span class="pag-info">Showing 1–10 of 48 students</span>
                <div class="pag-btns">
                    <button class="pag-btn"><i class="fas fa-chevron-left"></i></button>
                    <button class="pag-btn active">1</button>
                    <button class="pag-btn">2</button>
                    <button class="pag-btn">3</button>
                    <button class="pag-btn">4</button>
                    <button class="pag-btn">5</button>
                    <button class="pag-btn"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL -->
        <div class="right-panel">
            <!-- AT RISK -->
            <div class="side-card">
                <div class="side-title" style="color:var(--accent);"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i>At-Risk Students</div>
                <div class="at-risk-item">
                    <div class="at-risk-av">CG</div>
                    <div style="flex:1">
                        <div class="at-risk-name">Carlo Garcia</div>
                        <div class="at-risk-sub">TAX &bull; Last quiz 6d ago</div>
                    </div>
                    <div>
                        <div class="at-risk-score">44%</div>
                    </div>
                </div>
                <div class="at-risk-item">
                    <div class="at-risk-av">NC</div>
                    <div style="flex:1">
                        <div class="at-risk-name">Nina Cruz</div>
                        <div class="at-risk-sub">FAR &bull; Low engagement</div>
                    </div>
                    <div><div class="at-risk-score">58%</div></div>
                </div>
                <div class="at-risk-item">
                    <div class="at-risk-av">LR</div>
                    <div style="flex:1">
                        <div class="at-risk-name">Leo Ramos</div>
                        <div class="at-risk-sub">AUD &bull; Declining trend</div>
                    </div>
                    <div><div class="at-risk-score">62%</div></div>
                </div>
                <button class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:12px;font-size:12px;"><i class="fas fa-envelope"></i> Send Reminder to All</button>
            </div>

            <!-- WEAKEST TOPICS -->
            <div class="side-card">
                <div class="side-title"><i class="fas fa-chart-bar" style="margin-right:6px;color:var(--accent);"></i>Class Weak Topics</div>
                <div class="weak-item">
                    <div class="weak-icon" style="background:#fde8e8;color:var(--accent);"><i class="fas fa-calculator"></i></div>
                    <span class="weak-name">Financial Instruments</span>
                    <span class="weak-rate">48%</span>
                </div>
                <div class="weak-item">
                    <div class="weak-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-landmark"></i></div>
                    <span class="weak-name">Estate Tax</span>
                    <span class="weak-rate">52%</span>
                </div>
                <div class="weak-item">
                    <div class="weak-icon" style="background:#dbeafe;color:#3b82f6;"><i class="fas fa-globe"></i></div>
                    <span class="weak-name">Foreign Currency</span>
                    <span class="weak-rate">54%</span>
                </div>
                <div class="weak-item">
                    <div class="weak-icon" style="background:#ede9fe;color:#8b5cf6;"><i class="fas fa-chart-pie"></i></div>
                    <span class="weak-name">Capital Budgeting</span>
                    <span class="weak-rate">58%</span>
                </div>
                <div class="weak-item">
                    <div class="weak-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-gavel"></i></div>
                    <span class="weak-name">Securities Regulation</span>
                    <span class="weak-rate">61%</span>
                </div>
                <a href="{{ route('faculty.test-bank') }}" style="display:flex;align-items:center;justify-content:center;gap:6px;font-size:12px;color:var(--accent);text-decoration:none;margin-top:14px;font-weight:600;">Add Questions for These Topics <i class="fas fa-arrow-right"></i></a>
            </div>

            <!-- SCORE DISTRIBUTION -->
            <div class="side-card">
                <div class="side-title">Score Distribution</div>
                @php
                $ranges = [['90-100%', 3, '#059669'],['75-89%', 18, '#3b82f6'],['60-74%', 17, '#d97706'],['Below 60%', 10, '#c0392b']];
                @endphp
                @foreach($ranges as $r)
                <div style="margin-bottom:12px;">
                    <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
                        <span style="color:#555;">{{ $r[0] }}</span>
                        <span style="font-weight:700;color:#1a1a1a;">{{ $r[1] }} students</span>
                    </div>
                    <div style="height:7px;background:#f0f0f0;border-radius:4px;overflow:hidden;">
                        <div style="height:100%;border-radius:4px;background:{{ $r[2] }};width:{{ ($r[1]/48)*100 }}%;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('sidebarToggle');
    const sb  = document.getElementById('sidebar');
    if (btn) btn.addEventListener('click', () => { sb.classList.toggle('collapsed'); localStorage.setItem('facultySidebar', sb.classList.contains('collapsed')); });
    if (localStorage.getItem('facultySidebar') === 'true') sb.classList.add('collapsed');
});
</script>
</body>
</html>
