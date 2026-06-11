<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects - CPACE CPA Reviewer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #7B1D1D;
            --primary-hover: #6a1818;
            --primary-light: #f5e8e8;
            --accent-red: #c0392b;
            --sidebar-bg: #7B1D1D;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f5f7;
            color: #333;
        }

        /* ─── SIDEBAR ─── */
        .sidebar {
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            width: 220px;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: width 0.3s ease;
        }
        .sidebar.collapsed { width: 70px; }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 24px 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
        }

        .logo-circle {
            width: 44px; height: 44px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: white; flex-shrink: 0;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .logo-text strong { display: block; font-size: 15px; font-weight: 700; letter-spacing: 0.5px; }
        .logo-text small  { font-size: 11px; opacity: 0.8; }
        .sidebar.collapsed .logo-text { display: none; }

        .sidebar-nav { list-style: none; flex: 1; padding: 12px 0; }

        .sidebar-nav li a {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 22px;
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            font-size: 13px;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar-nav li a:hover { color: white; background: rgba(255,255,255,0.1); }
        .sidebar-nav li a.active {
            color: white; background: rgba(255,255,255,0.18);
            border-left-color: white; font-weight: 500;
        }
        .sidebar-nav li a i { width: 18px; text-align: center; font-size: 15px; flex-shrink: 0; }

        .sidebar.collapsed .sidebar-nav li a { padding: 11px 0; justify-content: center; gap: 0; }
        .sidebar.collapsed .sidebar-nav li a span { display: none; }

        /* Challenge Box */
        .sidebar-challenge {
            padding: 14px 16px;
            margin: 8px 12px 4px;
        }
        .sidebar.collapsed .sidebar-challenge { display: none; }

        .challenge-box {
            background: rgba(0,0,0,0.25);
            border-radius: 10px;
            padding: 14px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .challenge-box .ch-label {
            font-size: 11px; color: rgba(255,255,255,0.75); margin-bottom: 3px;
        }
        .challenge-box .ch-title {
            font-size: 13px; font-weight: 700; color: white; margin-bottom: 12px;
        }
        .challenge-box a {
            display: inline-flex; align-items: center; gap: 6px;
            background: white; color: var(--primary);
            padding: 8px 12px;
            border-radius: 6px; font-size: 11px; font-weight: 700;
            text-decoration: none; transition: all 0.2s;
        }
        .challenge-box a:hover { background: #f5e8e8; }
        .challenge-icon {
            position: absolute; right: 10px; bottom: 8px;
            font-size: 28px; opacity: 0.3; color: white;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            border-top: 1px solid rgba(255,255,255,0.12);
            padding: 16px 20px;
        }
        .user-profile { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .avatar-sm {
            width: 38px; height: 38px;
            background: var(--accent-red);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 13px; color: white; flex-shrink: 0;
        }
        .user-details { flex: 1; min-width: 0; }
        .user-details .uname { display: block; font-size: 13px; font-weight: 600; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-details .urole { display: block; font-size: 11px; color: rgba(255,255,255,0.65); }
        .sidebar.collapsed .user-details, .sidebar.collapsed .chevron-icon { display: none; }

        /* ─── MAIN ─── */
        .main-content {
            margin-left: 220px;
            padding: 30px 40px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        .sidebar.collapsed ~ .main-content { margin-left: 70px; }

        /* ─── TOP BAR ─── */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            gap: 20px;
        }
        .top-bar-left { display: flex; align-items: center; gap: 14px; }
        .toggle-btn {
            width: 38px; height: 38px;
            border: 1px solid #e0e0e0; background: white; border-radius: 8px;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            color: var(--primary); font-size: 16px; transition: background 0.2s;
        }
        .toggle-btn:hover { background: #f0f0f0; }
        .top-bar-right { display: flex; align-items: center; gap: 14px; }

        .search-wrap { position: relative; }
        .search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #aaa; font-size: 14px; }
        .search-wrap input {
            width: 280px; padding: 10px 14px 10px 36px;
            border: 1px solid #e0e0e0; border-radius: 24px;
            font-size: 13px; font-family: 'Poppins', sans-serif;
            background: white; color: #555; outline: none;
        }
        .search-wrap input:focus { border-color: var(--primary); }
        .search-wrap input::placeholder { color: #bbb; }

        .notif-btn {
            position: relative; width: 40px; height: 40px;
            border: none; background: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px; color: #555; cursor: pointer;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }
        .notif-btn:hover { background: #f0f0f0; }
        .badge {
            position: absolute; top: -3px; right: -3px;
            width: 18px; height: 18px; background: var(--accent-red);
            color: white; border-radius: 50%; font-size: 10px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        .profile-avatar {
            width: 40px; height: 40px; background: var(--primary);
            border-radius: 10px; border: none; color: white;
            font-weight: 700; font-size: 14px; cursor: pointer;
            font-family: 'Poppins', sans-serif; transition: background 0.2s;
        }
        .profile-avatar:hover { background: var(--primary-hover); }

        /* ─── PAGE HEADER ROW ─── */
        .page-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 28px;
            gap: 20px;
        }

        .page-title { font-size: 30px; font-weight: 700; color: #1a1a1a; margin-bottom: 6px; }
        .page-subtitle { font-size: 14px; color: #999; }

        /* Page header illustration */
        .page-header-illus {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            position: relative;
        }
        .illus-circle-bg {
            position: absolute;
            right: -10px; top: -20px;
            width: 160px; height: 160px;
            background: radial-gradient(circle, #fde8e8 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
        }
        .illus-books {
            display: flex;
            align-items: flex-end;
            gap: 4px;
            position: relative;
            z-index: 1;
        }
        .illus-book {
            width: 36px;
            border-radius: 4px;
            display: flex; align-items: center; justify-content: center;
            font-size: 9px; font-weight: 800; color: white;
            writing-mode: vertical-rl; letter-spacing: 1px;
        }
        .illus-book.b1 { height: 110px; background: linear-gradient(180deg,#1abc9c,#16a085); }
        .illus-book.b2 { height: 90px; background: linear-gradient(180deg,#c0392b,#922b21); }
        .illus-book.b3 { height: 75px; background: linear-gradient(180deg,#27ae60,#1e8449); }
        .illus-plant { font-size: 38px; margin-bottom: 2px; position: relative; z-index:1; }
        .illus-mug { font-size: 34px; margin-bottom: 2px; position: relative; z-index:1; }

        /* ─── SUBJECT GRID ─── */
        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .subject-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            transition: transform 0.25s, box-shadow 0.25s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }
        .subject-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 24px rgba(0,0,0,0.1);
        }

        .subject-card-top {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 20px;
        }

        .subject-icon-circle {
            width: 72px; height: 72px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
            flex-shrink: 0;
        }
        .si-far   { background: #e8f0fd; color: #3b82f6; }
        .si-aud   { background: #fde8f0; color: #e8567d; }
        .si-tax   { background: #e8f7ee; color: #27ae60; }
        .si-ms    { background: #f0e8fd; color: #9b59b6; }
        .si-rfbt  { background: #fef3e0; color: #f39c12; }
        .si-afar  { background: #e8f7f9; color: #17a2b8; }

        .subject-info { flex: 1; }
        .subject-abbr { font-size: 20px; font-weight: 800; color: #1a1a1a; margin-bottom: 3px; }
        .subject-full { font-size: 12px; color: #888; line-height: 1.4; }

        .subject-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            padding: 16px 0;
            border-top: 1px solid #f5f5f5;
            border-bottom: 1px solid #f5f5f5;
            margin-bottom: 16px;
            text-align: center;
        }
        .stat-num {
            display: block;
            font-size: 20px; font-weight: 700; color: #1a1a1a;
            margin-bottom: 3px;
        }
        .stat-num.weak { color: var(--accent-red); }
        .stat-lbl {
            font-size: 10px; color: #aaa; text-transform: uppercase; letter-spacing: 0.4px;
        }

        .subject-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 16px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            transition: opacity 0.2s;
            margin-top: auto;
        }
        .subject-btn:hover { opacity: 0.85; }

        .btn-far   { background: #e8f0fd; color: #3b82f6; }
        .btn-aud   { background: #fde8f0; color: #e8567d; }
        .btn-tax   { background: #e8f7ee; color: #27ae60; }
        .btn-ms    { background: #f0e8fd; color: #9b59b6; }
        .btn-rfbt  { background: #fef3e0; color: #f39c12; }
        .btn-afar  { background: #e8f7f9; color: #17a2b8; }

        @media (max-width: 1200px) {
            .subjects-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .main-content { margin-left: 70px; padding: 20px; }
            .subjects-grid { grid-template-columns: 1fr; }
            .search-wrap input { width: 160px; }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .anim { animation: fadeUp 0.4s ease both; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
@include('partials.sidebar', ['active' => 'subjects'])

<!-- MAIN -->
<main class="main-content">

    <!-- TOP BAR -->
    <div class="top-bar anim" style="animation-delay:0s">
        <div class="top-bar-left">
            <button class="toggle-btn" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        </div>
        <div class="top-bar-right">
            <div class="search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search topics, questions...">
            </div>
            <button class="notif-btn">
                <i class="fas fa-bell"></i>
                <span class="badge">3</span>
            </button>
            <button class="profile-avatar">KD</button>
        </div>
    </div>

    <!-- PAGE HEADER -->
    <div class="page-header-row anim" style="animation-delay:0.06s">
        <div>
            <div class="page-title">Subjects</div>
            <div class="page-subtitle">Review by subject area and strengthen your knowledge.</div>
        </div>
        <div class="page-header-illus">
            <div class="illus-circle-bg"></div>
            <div class="illus-plant">&#127807;</div>
            <div class="illus-books">
                <div class="illus-book b1">FAR</div>
                <div class="illus-book b2">AUD</div>
                <div class="illus-book b3">TAX</div>
            </div>
            <div class="illus-mug">&#9749;</div>
        </div>
    </div>

    <!-- SUBJECTS GRID -->
    <div class="subjects-grid anim" style="animation-delay:0.12s">

        <!-- FAR -->
        <div class="subject-card">
            <div class="subject-card-top">
                <div class="subject-icon-circle si-far">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="subject-info">
                    <div class="subject-abbr">FAR</div>
                    <div class="subject-full">Financial Accounting<br>and Reporting</div>
                </div>
            </div>
            <div class="subject-stats">
                <div>
                    <span class="stat-num">128</span>
                    <span class="stat-lbl">Topics</span>
                </div>
                <div>
                    <span class="stat-num">245</span>
                    <span class="stat-lbl">Questions</span>
                </div>
                <div>
                    <span class="stat-num weak">18</span>
                    <span class="stat-lbl">Weak Topics</span>
                </div>
            </div>
            <a href="#" class="subject-btn btn-far">Review Subject <i class="fas fa-arrow-right"></i></a>
        </div>

        <!-- AUD -->
        <div class="subject-card">
            <div class="subject-card-top">
                <div class="subject-icon-circle si-aud">
                    <i class="fas fa-search"></i>
                </div>
                <div class="subject-info">
                    <div class="subject-abbr">AUD</div>
                    <div class="subject-full">Auditing and<br>Attestation</div>
                </div>
            </div>
            <div class="subject-stats">
                <div>
                    <span class="stat-num">98</span>
                    <span class="stat-lbl">Topics</span>
                </div>
                <div>
                    <span class="stat-num">189</span>
                    <span class="stat-lbl">Questions</span>
                </div>
                <div>
                    <span class="stat-num weak">14</span>
                    <span class="stat-lbl">Weak Topics</span>
                </div>
            </div>
            <a href="#" class="subject-btn btn-aud">Review Subject <i class="fas fa-arrow-right"></i></a>
        </div>

        <!-- TAX -->
        <div class="subject-card">
            <div class="subject-card-top">
                <div class="subject-icon-circle si-tax">
                    <i class="fas fa-table"></i>
                </div>
                <div class="subject-info">
                    <div class="subject-abbr">TAX</div>
                    <div class="subject-full">Taxation</div>
                </div>
            </div>
            <div class="subject-stats">
                <div>
                    <span class="stat-num">87</span>
                    <span class="stat-lbl">Topics</span>
                </div>
                <div>
                    <span class="stat-num">176</span>
                    <span class="stat-lbl">Questions</span>
                </div>
                <div>
                    <span class="stat-num weak">12</span>
                    <span class="stat-lbl">Weak Topics</span>
                </div>
            </div>
            <a href="#" class="subject-btn btn-tax">Review Subject <i class="fas fa-arrow-right"></i></a>
        </div>

        <!-- MS -->
        <div class="subject-card">
            <div class="subject-card-top">
                <div class="subject-icon-circle si-ms">
                    <i class="fas fa-users"></i>
                </div>
                <div class="subject-info">
                    <div class="subject-abbr">MS</div>
                    <div class="subject-full">Management<br>Services</div>
                </div>
            </div>
            <div class="subject-stats">
                <div>
                    <span class="stat-num">76</span>
                    <span class="stat-lbl">Topics</span>
                </div>
                <div>
                    <span class="stat-num">142</span>
                    <span class="stat-lbl">Questions</span>
                </div>
                <div>
                    <span class="stat-num weak">10</span>
                    <span class="stat-lbl">Weak Topics</span>
                </div>
            </div>
            <a href="#" class="subject-btn btn-ms">Review Subject <i class="fas fa-arrow-right"></i></a>
        </div>

        <!-- RFBT -->
        <div class="subject-card">
            <div class="subject-card-top">
                <div class="subject-icon-circle si-rfbt">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="subject-info">
                    <div class="subject-abbr">RFBT</div>
                    <div class="subject-full">Regulatory Framework<br>for Business Transactions</div>
                </div>
            </div>
            <div class="subject-stats">
                <div>
                    <span class="stat-num">92</span>
                    <span class="stat-lbl">Topics</span>
                </div>
                <div>
                    <span class="stat-num">168</span>
                    <span class="stat-lbl">Questions</span>
                </div>
                <div>
                    <span class="stat-num weak">11</span>
                    <span class="stat-lbl">Weak Topics</span>
                </div>
            </div>
            <a href="#" class="subject-btn btn-rfbt">Review Subject <i class="fas fa-arrow-right"></i></a>
        </div>

        <!-- AFAR -->
        <div class="subject-card">
            <div class="subject-card-top">
                <div class="subject-icon-circle si-afar">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="subject-info">
                    <div class="subject-abbr">AFAR</div>
                    <div class="subject-full">Advanced Financial<br>Accounting and Reporting</div>
                </div>
            </div>
            <div class="subject-stats">
                <div>
                    <span class="stat-num">85</span>
                    <span class="stat-lbl">Topics</span>
                </div>
                <div>
                    <span class="stat-num">154</span>
                    <span class="stat-lbl">Questions</span>
                </div>
                <div>
                    <span class="stat-num weak">9</span>
                    <span class="stat-lbl">Weak Topics</span>
                </div>
            </div>
            <a href="#" class="subject-btn btn-afar">Review Subject <i class="fas fa-arrow-right"></i></a>
        </div>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar   = document.getElementById('sidebar');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
    }
});
</script>
</body>
</html>
