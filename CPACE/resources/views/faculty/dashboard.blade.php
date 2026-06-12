<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        /* MAIN */
        .main { margin-left:230px; padding:26px 30px; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }

        /* TOP BAR */
        .topbar {
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:24px; gap:16px;
        }
        .topbar-left { display:flex; align-items:center; gap:12px; }
        .toggle-btn {
            width:36px; height:36px;
            border:1px solid #ddd; background:white; border-radius:8px;
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            color:var(--primary); font-size:15px;
        }
        .page-title { font-size:26px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .topbar-right { display:flex; align-items:center; gap:12px; }
        .btn {
            display:inline-flex; align-items:center; gap:7px;
            padding:9px 18px; border-radius:8px;
            font-size:13px; font-weight:600; font-family:'Poppins',sans-serif;
            cursor:pointer; border:none; text-decoration:none; transition:all .2s;
        }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-outline { background:white; color:var(--primary); border:1.5px solid var(--primary); }
        .btn-outline:hover { background:var(--primary-light); }

        /* STATS ROW */
        .stats-row {
            display:grid; grid-template-columns:repeat(4,1fr);
            gap:16px; margin-bottom:22px;
        }
        .stat-card {
            background:white; border-radius:14px; padding:20px 22px;
        }
        .stat-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; }
        .stat-icon {
            width:40px; height:40px; border-radius:10px;
            display:flex; align-items:center; justify-content:center; font-size:18px;
        }
        .si-red    { background:#fde8e8; color:var(--accent); }
        .si-green  { background:#d1fae5; color:var(--green); }
        .si-blue   { background:#dbeafe; color:var(--blue); }
        .si-orange { background:#fef3c7; color:var(--orange); }
        .si-purple { background:#ede9fe; color:var(--purple); }
        .stat-num  { font-size:28px; font-weight:700; color:#1a1a1a; line-height:1; margin-bottom:4px; }
        .stat-lbl  { font-size:11px; color:#999; }
        .stat-chg  { font-size:11px; color:var(--green); margin-top:4px; }
        .stat-chg.neutral { color:#999; }

        /* MAIN GRID */
        .main-grid {
            display:grid; grid-template-columns:1fr 340px;
            gap:18px; margin-bottom:18px;
        }

        /* CARDS */
        .card { background:white; border-radius:14px; padding:22px; }
        .card + .card { margin-top:18px; }
        .card-head {
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:18px;
        }
        .card-title { font-size:14px; font-weight:600; color:#1a1a1a; }
        .card-link { font-size:12px; color:var(--accent); text-decoration:none; font-weight:500; }
        .card-link:hover { text-decoration:underline; }

        /* TABLE */
        table { width:100%; border-collapse:collapse; }
        thead th {
            text-align:left; font-size:11px; color:#aaa;
            font-weight:600; padding:0 10px 12px;
            text-transform:uppercase; letter-spacing:.4px;
        }
        tbody tr { border-top:1px solid #f5f5f5; }
        tbody td { padding:12px 10px; font-size:13px; vertical-align:middle; }
        tbody tr:hover { background:#fafafa; }

        .subj-badge {
            display:inline-block; padding:3px 9px; border-radius:5px;
            font-size:10px; font-weight:700;
        }
        .b-far  { background:#dbeafe; color:#2563eb; }
        .b-aud  { background:#fce7f3; color:#db2777; }
        .b-tax  { background:#d1fae5; color:#059669; }
        .b-ms   { background:#ede9fe; color:#7c3aed; }
        .b-rfbt { background:#fef3c7; color:#d97706; }
        .b-afar { background:#cffafe; color:#0891b2; }

        .diff-badge {
            display:inline-block; padding:3px 9px; border-radius:5px;
            font-size:10px; font-weight:600;
        }
        .d-easy   { background:#d1fae5; color:#059669; }
        .d-medium { background:#fef3c7; color:#d97706; }
        .d-hard   { background:#fde8e8; color:var(--accent); }

        .status-dot {
            width:7px; height:7px; border-radius:50%;
            display:inline-block; margin-right:5px;
        }
        .dot-active { background:var(--green); }
        .dot-draft  { background:#d1d5db; }

        .action-btn {
            width:28px; height:28px; border:none; border-radius:6px;
            cursor:pointer; font-size:12px;
            display:inline-flex; align-items:center; justify-content:center;
            transition:all .2s;
        }
        .ab-edit { background:#dbeafe; color:var(--blue); }
        .ab-del  { background:#fde8e8; color:var(--accent); }
        .ab-edit:hover { background:#bfdbfe; }
        .ab-del:hover  { background:#fecaca; }

        /* RIGHT COLUMN */
        .right-col { display:flex; flex-direction:column; gap:18px; }

        /* QUICK ACTIONS */
        .quick-actions { display:flex; flex-direction:column; gap:10px; }
        .qa-btn {
            display:flex; align-items:center; gap:12px;
            padding:13px 16px; border-radius:10px;
            text-decoration:none; transition:all .2s; cursor:pointer;
            border:none; width:100%; font-family:'Poppins',sans-serif;
        }
        .qa-btn .qa-icon {
            width:36px; height:36px; border-radius:8px;
            display:flex; align-items:center; justify-content:center;
            font-size:16px; flex-shrink:0;
        }
        .qa-title  { font-size:13px; font-weight:600; color:#1a1a1a; display:block; }
        .qa-sub    { font-size:11px; color:#999; display:block; }
        .qa-btn.primary-qa { background:var(--primary); }
        .qa-btn.primary-qa .qa-title,
        .qa-btn.primary-qa .qa-sub { color:rgba(255,255,255,.9); }
        .qa-btn.primary-qa .qa-icon { background:rgba(255,255,255,.2); color:white; }
        .qa-btn.secondary-qa { background:#f9f9f9; }
        .qa-btn.secondary-qa .qa-icon { background:white; }
        .qa-btn:hover { opacity:.9; transform:translateY(-1px); }

        /* SUBJECT DISTRIBUTION */
        .subj-dist-item { margin-bottom:14px; }
        .subj-dist-item:last-child { margin-bottom:0; }
        .subj-dist-top {
            display:flex; justify-content:space-between;
            font-size:12px; color:#555; margin-bottom:5px;
        }
        .subj-dist-top .val { font-weight:700; color:#1a1a1a; }
        .bar-bg { height:7px; background:#f0f0f0; border-radius:5px; overflow:hidden; }
        .bar-fill { height:100%; border-radius:5px; }

        /* ACTIVITY FEED */
        .activity-item {
            display:flex; align-items:center; gap:12px;
            padding:11px 0; border-bottom:1px solid #f5f5f5;
        }
        .activity-item:last-child { border-bottom:none; }
        .act-icon {
            width:34px; height:34px; border-radius:9px;
            display:flex; align-items:center; justify-content:center;
            font-size:14px; flex-shrink:0;
        }
        .act-name { font-size:13px; font-weight:600; color:#1a1a1a; margin-bottom:2px; }
        .act-sub  { font-size:11px; color:#999; }
        .act-time { font-size:11px; color:#bbb; white-space:nowrap; }

        /* BOTTOM ROW */
        .bottom-row {
            display:grid; grid-template-columns:repeat(3,1fr);
            gap:18px;
        }

        .mini-stat {
            display:flex; align-items:center; gap:12px;
            padding:13px 0; border-bottom:1px solid #f5f5f5;
        }
        .mini-stat:last-child { border-bottom:none; }
        .mini-icon {
            width:32px; height:32px; border-radius:8px;
            display:flex; align-items:center; justify-content:center;
            font-size:13px; flex-shrink:0;
        }
        .mini-label { font-size:12px; color:#888; margin-bottom:1px; }
        .mini-val   { font-size:14px; font-weight:700; color:#1a1a1a; }

        @keyframes fadeUp {
            from { opacity:0; transform:translateY(14px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .a0 { animation:fadeUp .4s ease both; }
        .a1 { animation:fadeUp .4s .07s ease both; }
        .a2 { animation:fadeUp .4s .14s ease both; }
        .a3 { animation:fadeUp .4s .21s ease both; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
@include('partials.faculty-sidebar', ['active' => 'dashboard'])

<!-- MAIN -->
<main class="main">

    <!-- TOPBAR -->
    <div class="topbar a0">
        <div class="topbar-left">
            <button class="toggle-btn" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <div>
                <div class="page-title">Faculty Dashboard</div>
                <div class="page-sub">Welcome back, {{ Auth::user()->name }}. Here's your overview.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('faculty.question.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Question</a>
            <a href="{{ route('faculty.test-bank') }}" class="btn btn-outline"><i class="fas fa-database"></i> Test Bank</a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-outline" style="color:#c0392b;border-color:#c0392b;">
                    <i class="fas fa-sign-out-alt"></i> Sign Out
                </button>
            </form>
        </div>
    </div>

    <!-- STATS -->
    <div class="stats-row a1">
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Total Questions</div>
                    <div class="stat-num">1,543</div>
                    <div class="stat-chg"><i class="fas fa-arrow-up"></i> 24 this week</div>
                </div>
                <div class="stat-icon si-red"><i class="fas fa-database"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Active Students</div>
                    <div class="stat-num">48</div>
                    <div class="stat-chg"><i class="fas fa-arrow-up"></i> 5 new this month</div>
                </div>
                <div class="stat-icon si-green"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Avg. Student Score</div>
                    <div class="stat-num">72%</div>
                    <div class="stat-chg"><i class="fas fa-arrow-up"></i> 3% from last month</div>
                </div>
                <div class="stat-icon si-blue"><i class="fas fa-chart-bar"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Questions Added</div>
                    <div class="stat-num">24</div>
                    <div class="stat-chg neutral">This week</div>
                </div>
                <div class="stat-icon si-orange"><i class="fas fa-pen"></i></div>
            </div>
        </div>
    </div>

    <!-- MAIN GRID -->
    <div class="main-grid a2">
        <!-- LEFT -->
        <div>
            <!-- RECENT QUESTIONS -->
            <div class="card">
                <div class="card-head">
                    <span class="card-title">Recently Added Questions</span>
                    <a href="{{ route('faculty.test-bank') }}" class="card-link">View All</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Subject</th>
                            <th>Difficulty</th>
                            <th>Status</th>
                            <th>Added</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="max-width:260px;">
                                <div style="font-weight:600;color:#1a1a1a;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:250px;">Under PFRS 15, revenue is recognized when...</div>
                                <div style="font-size:11px;color:#aaa;">Multiple Choice</div>
                            </td>
                            <td><span class="subj-badge b-far">FAR</span></td>
                            <td><span class="diff-badge d-medium">Medium</span></td>
                            <td><span class="status-dot dot-active"></span><span style="font-size:12px;">Active</span></td>
                            <td style="font-size:11px;color:#aaa;">2h ago</td>
                            <td>
                                <button class="action-btn ab-edit"><i class="fas fa-pen"></i></button>
                                <button class="action-btn ab-del" style="margin-left:4px;"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="font-weight:600;color:#1a1a1a;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:250px;">Which of the following best describes audit risk?</div>
                                <div style="font-size:11px;color:#aaa;">Multiple Choice</div>
                            </td>
                            <td><span class="subj-badge b-aud">AUD</span></td>
                            <td><span class="diff-badge d-hard">Hard</span></td>
                            <td><span class="status-dot dot-active"></span><span style="font-size:12px;">Active</span></td>
                            <td style="font-size:11px;color:#aaa;">5h ago</td>
                            <td>
                                <button class="action-btn ab-edit"><i class="fas fa-pen"></i></button>
                                <button class="action-btn ab-del" style="margin-left:4px;"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="font-weight:600;color:#1a1a1a;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:250px;">The estate tax rate in the Philippines is...</div>
                                <div style="font-size:11px;color:#aaa;">Multiple Choice</div>
                            </td>
                            <td><span class="subj-badge b-tax">TAX</span></td>
                            <td><span class="diff-badge d-easy">Easy</span></td>
                            <td><span class="status-dot dot-draft"></span><span style="font-size:12px;color:#aaa;">Draft</span></td>
                            <td style="font-size:11px;color:#aaa;">1d ago</td>
                            <td>
                                <button class="action-btn ab-edit"><i class="fas fa-pen"></i></button>
                                <button class="action-btn ab-del" style="margin-left:4px;"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="font-weight:600;color:#1a1a1a;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:250px;">Balance of payments (BOP) refers to...</div>
                                <div style="font-size:11px;color:#aaa;">True / False</div>
                            </td>
                            <td><span class="subj-badge b-ms">MS</span></td>
                            <td><span class="diff-badge d-medium">Medium</span></td>
                            <td><span class="status-dot dot-active"></span><span style="font-size:12px;">Active</span></td>
                            <td style="font-size:11px;color:#aaa;">2d ago</td>
                            <td>
                                <button class="action-btn ab-edit"><i class="fas fa-pen"></i></button>
                                <button class="action-btn ab-del" style="margin-left:4px;"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- STUDENT ACTIVITY -->
            <div class="card">
                <div class="card-head">
                    <span class="card-title">Recent Student Activity</span>
                    <a href="{{ route('faculty.performance') }}" class="card-link">View All</a>
                </div>
                <div class="activity-item">
                    <div class="act-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-check-circle"></i></div>
                    <div style="flex:1">
                        <div class="act-name">Maria Santos</div>
                        <div class="act-sub">Completed FAR Mock Exam &bull; Score: 85%</div>
                    </div>
                    <div class="act-time">2h ago</div>
                </div>
                <div class="activity-item">
                    <div class="act-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-brain"></i></div>
                    <div style="flex:1">
                        <div class="act-name">Juan dela Cruz</div>
                        <div class="act-sub">Finished Adaptive Quiz – AUD &bull; Score: 71%</div>
                    </div>
                    <div class="act-time">4h ago</div>
                </div>
                <div class="activity-item">
                    <div class="act-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-star"></i></div>
                    <div style="flex:1">
                        <div class="act-name">Ana Reyes</div>
                        <div class="act-sub">Earned Achievement: 10-Day Streak</div>
                    </div>
                    <div class="act-time">6h ago</div>
                </div>
                <div class="activity-item">
                    <div class="act-icon" style="background:#fde8e8;color:var(--accent);"><i class="fas fa-exclamation-circle"></i></div>
                    <div style="flex:1">
                        <div class="act-name">Carlo Mendoza</div>
                        <div class="act-sub">Low score alert – TAX Quiz &bull; Score: 42%</div>
                    </div>
                    <div class="act-time">1d ago</div>
                </div>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="right-col">
            <!-- QUICK ACTIONS -->
            <div class="card">
                <div class="card-head"><span class="card-title">Quick Actions</span></div>
                <div class="quick-actions">
                    <a href="{{ route('faculty.question.create') }}" class="qa-btn primary-qa">
                        <div class="qa-icon"><i class="fas fa-plus"></i></div>
                        <div>
                            <span class="qa-title">Add New Question</span>
                            <span class="qa-sub">Add to test bank</span>
                        </div>
                    </a>
                    <a href="{{ route('faculty.subjects') }}" class="qa-btn secondary-qa">
                        <div class="qa-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-book-open"></i></div>
                        <div>
                            <span class="qa-title">Manage Subjects</span>
                            <span class="qa-sub">Add or edit topics</span>
                        </div>
                    </a>
                    <a href="{{ route('faculty.performance') }}" class="qa-btn secondary-qa">
                        <div class="qa-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-users"></i></div>
                        <div>
                            <span class="qa-title">View Student Scores</span>
                            <span class="qa-sub">Monitor performance</span>
                        </div>
                    </a>
                    <a href="#" class="qa-btn secondary-qa">
                        <div class="qa-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-file-export"></i></div>
                        <div>
                            <span class="qa-title">Export Report</span>
                            <span class="qa-sub">Download CSV / PDF</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- QUESTION DISTRIBUTION -->
            <div class="card">
                <div class="card-head"><span class="card-title">Questions by Subject</span></div>
                <div class="subj-dist-item">
                    <div class="subj-dist-top"><span>FAR</span><span class="val">342</span></div>
                    <div class="bar-bg"><div class="bar-fill" style="width:88%;background:#3b82f6;"></div></div>
                </div>
                <div class="subj-dist-item">
                    <div class="subj-dist-top"><span>AUD</span><span class="val">289</span></div>
                    <div class="bar-bg"><div class="bar-fill" style="width:74%;background:#e8567d;"></div></div>
                </div>
                <div class="subj-dist-item">
                    <div class="subj-dist-top"><span>TAX</span><span class="val">310</span></div>
                    <div class="bar-bg"><div class="bar-fill" style="width:80%;background:#27ae60;"></div></div>
                </div>
                <div class="subj-dist-item">
                    <div class="subj-dist-top"><span>MS</span><span class="val">198</span></div>
                    <div class="bar-bg"><div class="bar-fill" style="width:51%;background:#8b5cf6;"></div></div>
                </div>
                <div class="subj-dist-item">
                    <div class="subj-dist-top"><span>RFBT</span><span class="val">220</span></div>
                    <div class="bar-bg"><div class="bar-fill" style="width:57%;background:#f59e0b;"></div></div>
                </div>
                <div class="subj-dist-item">
                    <div class="subj-dist-top"><span>AFAR</span><span class="val">184</span></div>
                    <div class="bar-bg"><div class="bar-fill" style="width:47%;background:#17a2b8;"></div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTTOM ROW -->
    <div class="bottom-row a3">
        <div class="card">
            <div class="card-head"><span class="card-title">Question Type Breakdown</span></div>
            <div class="mini-stat">
                <div class="mini-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-list-ul"></i></div>
                <div>
                    <div class="mini-label">Multiple Choice</div>
                    <div class="mini-val">1,120 <span style="font-size:11px;color:#aaa;font-weight:400;">(72.6%)</span></div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-check-square"></i></div>
                <div>
                    <div class="mini-label">True / False</div>
                    <div class="mini-val">280 <span style="font-size:11px;color:#aaa;font-weight:400;">(18.1%)</span></div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-pen"></i></div>
                <div>
                    <div class="mini-label">Identification</div>
                    <div class="mini-val">143 <span style="font-size:11px;color:#aaa;font-weight:400;">(9.3%)</span></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><span class="card-title">Difficulty Distribution</span></div>
            <div class="mini-stat">
                <div class="mini-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-smile"></i></div>
                <div>
                    <div class="mini-label">Easy</div>
                    <div class="mini-val">502 <span style="font-size:11px;color:#aaa;font-weight:400;">(32.5%)</span></div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-meh"></i></div>
                <div>
                    <div class="mini-label">Medium</div>
                    <div class="mini-val">741 <span style="font-size:11px;color:#aaa;font-weight:400;">(48.0%)</span></div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-icon" style="background:#fde8e8;color:var(--accent);"><i class="fas fa-frown"></i></div>
                <div>
                    <div class="mini-label">Hard</div>
                    <div class="mini-val">300 <span style="font-size:11px;color:#aaa;font-weight:400;">(19.5%)</span></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><span class="card-title">Top Performing Students</span></div>
            <div class="mini-stat">
                <div class="mini-icon" style="background:#fde8e8;color:var(--accent);font-weight:700;font-size:14px;">1</div>
                <div style="flex:1">
                    <div class="mini-label">Maria Santos</div>
                    <div class="mini-val" style="font-size:13px;">92% avg</div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-icon" style="background:#dbeafe;color:#2563eb;font-weight:700;font-size:14px;">2</div>
                <div style="flex:1">
                    <div class="mini-label">Ana Reyes</div>
                    <div class="mini-val" style="font-size:13px;">88% avg</div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-icon" style="background:#d1fae5;color:#059669;font-weight:700;font-size:14px;">3</div>
                <div style="flex:1">
                    <div class="mini-label">Juan dela Cruz</div>
                    <div class="mini-val" style="font-size:13px;">85% avg</div>
                </div>
            </div>
        </div>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('sidebarToggle');
    const sb  = document.getElementById('sidebar');
    if (btn) {
        btn.addEventListener('click', () => {
            sb.classList.toggle('collapsed');
            localStorage.setItem('facultySidebar', sb.classList.contains('collapsed'));
        });
    }
    if (localStorage.getItem('facultySidebar') === 'true') sb.classList.add('collapsed');
});
</script>
</body>
</html>
