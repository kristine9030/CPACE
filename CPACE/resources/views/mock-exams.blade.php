<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mock Exams - CPACE CPA Reviewer</title>

    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            color: #333;
        }

        .dashboard-container {
            display: block;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            background: #7B1D1D;
            color: white;
            padding: 30px 0;
            position: fixed;
            width: 211px;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: width 0.3s ease;
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 20px 30px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 30px;
        }

        .sidebar.collapsed .sidebar-logo {
            padding: 0 10px 30px 10px;
        }

        .sidebar-logo-icon {
            font-size: 32px;
        }

        .sidebar-logo-text {
            font-size: 14px;
            line-height: 1.3;
            font-family: 'Poppins', sans-serif;
        }

        .sidebar.collapsed .sidebar-logo-text {
            display: none;
        }

        .sidebar-logo-text strong {
            display: block;
            font-size: 13px;
            font-weight: 700;
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-nav li {
            margin: 0;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            font-family: 'Poppins', sans-serif;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar.collapsed .sidebar-nav a {
            padding: 12px 10px;
            justify-content: center;
            gap: 0;
        }

        .sidebar.collapsed .sidebar-nav a span {
            display: none;
        }

        .sidebar-nav a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar-nav a.active {
            color: white;
            background: rgba(255, 255, 255, 0.15);
            border-left-color: white;
        }

        .sidebar-nav i {
            margin-right: 8px;
            width: 18px;
            text-align: center;
        }

        .sidebar.collapsed .sidebar-nav i {
            margin-right: 0;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            padding: 0 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
        }

        .sidebar.collapsed .sidebar-footer {
            padding: 0 10px;
        }

        .sidebar-challenge {
            margin-bottom: 80px;
            padding: 0 20px;
        }

        .sidebar.collapsed .sidebar-challenge {
            display: none;
        }

        .challenge-box {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .challenge-box p {
            font-size: 12px;
            color: white;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .challenge-box a {
            display: inline-block;
            background: white;
            color: #7B1D1D;
            padding: 8px 14px;
            border-radius: 6px;
            text-align: center;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .challenge-box a:hover {
            background: #f9f9f9;
            transform: translateY(-2px);
        }

        .challenge-icon {
            position: absolute;
            right: 12px;
            bottom: 10px;
            font-size: 26px;
            opacity: 0.5;
        }

        .user-avatar {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            font-size: 13px;
        }

        .sidebar.collapsed .user-avatar {
            flex-direction: column;
            gap: 5px;
        }

        .avatar-circle {
            width: 40px;
            height: 40px;
            background: #c0392b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            flex-shrink: 0;
        }

        .user-info-sidebar {
            flex: 1;
            font-family: 'Poppins', sans-serif;
        }

        .sidebar.collapsed .user-info-sidebar,
        .sidebar.collapsed .user-caret {
            display: none;
        }

        .user-info-sidebar .name {
            display: block;
            font-weight: 600;
            font-size: 13px;
        }

        .user-info-sidebar .role {
            display: block;
            font-size: 11px;
            opacity: 0.8;
        }

        .user-caret {
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 211px;
            padding: 30px 40px;
            overflow-y: auto;
            transition: margin-left 0.3s ease;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: 70px;
        }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            gap: 20px;
        }

        .header-left {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .sidebar-toggle {
            background: white;
            border: 1px solid #ddd;
            width: 40px;
            height: 40px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #7B1D1D;
            font-size: 18px;
            transition: all 0.3s;
        }

        .sidebar-toggle:hover {
            background: #f0f0f0;
        }

        .header-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            font-family: 'Poppins', sans-serif;
        }

        .header-subtitle {
            color: #999;
            font-size: 14px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .search-box {
            position: relative;
            flex: 0 1 320px;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #c0392b;
            font-size: 14px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid #eee;
            border-radius: 30px;
            font-size: 13px;
            background: white;
            color: #666;
            font-family: 'Poppins', sans-serif;
        }

        .header-icons {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .icon-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: transparent;
            border-radius: 6px;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            position: relative;
            color: #c0392b;
        }

        .icon-btn:hover {
            background: #f0f0f0;
        }

        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 18px;
            height: 18px;
            background: #c0392b;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 600;
        }

        .profile-btn {
            width: 42px;
            height: 42px;
            background: #7B1D1D;
            border: none;
            border-radius: 50%;
            color: white;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
        }

        .profile-btn:hover {
            background: #6a1818;
        }

        .header-dropdown-wrap { position: relative; }
        .dropdown-menu {
            position: absolute; top: calc(100% + 8px); right: 0;
            background: white; border: 1px solid #e5e7eb; border-radius: 10px;
            min-width: 185px; box-shadow: 0 6px 20px rgba(0,0,0,0.12);
            display: none; z-index: 2000;
        }
        .dropdown-menu.active { display: block; }
        .dropdown-menu a, .dropdown-menu button {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 16px; font-size: 13px; font-family: 'Poppins', sans-serif;
            text-decoration: none; color: #333; background: none; border: none;
            width: 100%; text-align: left; cursor: pointer; transition: background 0.2s;
            border-bottom: 1px solid #f5f5f5;
        }
        .dropdown-menu a:last-child,
        .dropdown-menu form:last-child button { border-bottom: none; }
        .dropdown-menu a:hover, .dropdown-menu button:hover { background: #f9f9f9; }
        .dropdown-menu a i, .dropdown-menu button i { color: #7B1D1D; width: 16px; text-align: center; }
        .dropdown-menu .logout-btn { color: #e53e3e; }
        .dropdown-menu .logout-btn i { color: #e53e3e; }

        /* STATS CARDS */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border-radius: 14px;
            padding: 22px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .stat-card-icon {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-card-label {
            font-size: 13px;
            color: #777;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .stat-card-value {
            font-size: 30px;
            font-weight: 700;
            color: #222;
            line-height: 1.1;
            margin-bottom: 6px;
        }

        .stat-card-sub {
            font-size: 12px;
            color: #999;
        }

        .stat-card-sub.green {
            color: #27AE60;
            font-weight: 600;
        }

        .stat-card-sub .muted {
            color: #999;
            font-weight: 400;
        }

        /* MAIN GRID */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 25px;
            align-items: start;
        }

        .card {
            background: white;
            border-radius: 14px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .card-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .card-subtitle {
            font-size: 13px;
            color: #999;
        }

        .btn-primary {
            background: #7B1D1D;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .btn-primary:hover {
            background: #6a1818;
        }

        /* TABS */
        .tabs {
            display: flex;
            gap: 30px;
            border-bottom: 1px solid #eee;
            margin-bottom: 5px;
        }

        .tab {
            padding: 0 0 12px 0;
            font-size: 14px;
            color: #999;
            cursor: pointer;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: all 0.2s;
        }

        .tab.active {
            color: #7B1D1D;
            border-bottom-color: #7B1D1D;
            font-weight: 600;
        }

        /* TABLE */
        .exam-table {
            width: 100%;
            border-collapse: collapse;
        }

        .exam-table thead th {
            text-align: left;
            font-size: 12px;
            color: #999;
            font-weight: 600;
            padding: 18px 12px;
            text-transform: none;
        }

        .exam-table tbody td {
            padding: 16px 12px;
            border-top: 1px solid #f2f2f2;
            font-size: 13px;
            vertical-align: middle;
        }

        .exam-name {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .exam-format {
            font-size: 12px;
            color: #999;
        }

        .type-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }

        .type-far { background: #e3f0fd; color: #2f80c2; }
        .type-aud { background: #f1eafc; color: #8b5cf6; }
        .type-tax { background: #e6f7ee; color: #27AE60; }
        .type-reg { background: #eef0fc; color: #5b6cd8; }

        .score-value {
            font-weight: 700;
            color: #333;
            font-size: 14px;
            display: block;
        }

        .score-tag {
            font-size: 11px;
            font-weight: 500;
        }

        .score-tag.above { color: #27AE60; }
        .score-tag.avg { color: #F39C12; }
        .score-tag.below { color: #c0392b; }
        .score-tag.none { color: #bbb; }

        .time-value {
            font-weight: 600;
            color: #333;
            display: block;
        }

        .time-sub {
            font-size: 11px;
            color: #999;
        }

        .date-value {
            color: #333;
            display: block;
        }

        .date-sub {
            font-size: 11px;
            color: #999;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            color: #555;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #27AE60;
        }

        .status.not-taken {
            color: #999;
        }

        .row-action {
            color: #bbb;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-take {
            background: #fde8e7;
            color: #7B1D1D;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }

        .btn-take:hover {
            background: #fbd5d3;
        }

        .view-all {
            text-align: center;
            margin-top: 20px;
        }

        .view-all a {
            color: #7B1D1D;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }

        /* RIGHT COLUMN */
        .perf-card {
            text-align: center;
        }

        .perf-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .perf-head .card-title {
            margin-bottom: 0;
        }

        .perf-select {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 12px;
            color: #666;
            font-family: 'Poppins', sans-serif;
            background: white;
            cursor: pointer;
        }

        .perf-illustration {
            width: 140px;
            height: 140px;
            margin: 10px auto 20px;
            border-radius: 50%;
            background: radial-gradient(circle, #fde8e7 0%, #fdf3f2 70%, #ffffff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .perf-illustration .fa-shield-alt {
            font-size: 64px;
            color: #c0392b;
        }

        .perf-illustration .fa-location-arrow {
            position: absolute;
            top: 28px;
            right: 32px;
            font-size: 22px;
            color: #2f3b6e;
            transform: rotate(45deg);
        }

        .perf-text {
            font-size: 15px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .perf-subtext {
            font-size: 12px;
            color: #999;
            line-height: 1.5;
        }

        .tips-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 4px;
        }

        .tips-subtitle {
            font-size: 13px;
            color: #999;
            margin-bottom: 20px;
        }

        .tip-item {
            display: flex;
            gap: 14px;
            margin-bottom: 20px;
        }

        .tip-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .tip-name {
            font-size: 13px;
            font-weight: 700;
            color: #333;
            margin-bottom: 3px;
        }

        .tip-desc {
            font-size: 12px;
            color: #999;
            line-height: 1.4;
        }

        .tips-illustration {
            text-align: right;
            margin-top: 10px;
            font-size: 40px;
            color: #c0392b;
        }

        /* RESPONSIVE */
        @media (max-width: 1200px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .main-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 70px;
                padding: 20px;
            }

            .sidebar {
                width: 70px;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
            }

            .exam-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <div class="logo-circle" style="width:44px;height:44px;background:rgba(255,255,255,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;border:2px solid rgba(255,255,255,0.3);"><i class="fas fa-shield-alt"></i></div>
                <div class="sidebar-logo-text">
                    <strong>CPACE</strong>
                    <small>CPA Reviewer</small>
                </div>
            </div>

            <nav class="sidebar-nav">
                <li><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
                <li><a href="{{ route('subjects') }}"><i class="fas fa-book"></i><span>Subjects</span></a></li>
                <li><a href="{{ route('adaptive-quizzes') }}"><i class="fas fa-circle-check"></i><span>Quizzes</span></a></li>
                <li><a href="{{ route('mock-exams') }}" class="active"><i class="fas fa-file-lines"></i><span>Mock Exams</span></a></li>
                <li><a href="{{ route('performance') }}"><i class="fas fa-chart-column"></i><span>Performance</span></a></li>
                <li><a href="{{ route('review-notes') }}"><i class="fas fa-file-alt"></i><span>Review Notes</span></a></li>
                <li><a href="#"><i class="fas fa-clone"></i><span>Flashcards</span></a></li>
                <li><a href="{{ route('calendar') }}"><i class="fas fa-calendar-alt"></i><span>Calendar</span></a></li>
                <li><a href="#"><i class="fas fa-chart-bar"></i><span>Progress</span></a></li>
                <li><a href="{{ route('achievements') }}"><i class="fas fa-award"></i><span>Achievements</span></a></li>
                <li><a href="#"><i class="fas fa-gear"></i><span>Settings</span></a></li>
            </nav>

            <div class="sidebar-challenge">
                <div class="challenge-box">
                    <p style="font-weight: 700; font-size: 13px;">Prepare like it's the real CPA Exam.</p>
                    <a href="#">View Exam Tips</a>
                    <div class="challenge-icon"><i class="fas fa-clipboard-list"></i></div>
                </div>
            </div>

            <div class="sidebar-footer">
                <div class="user-avatar">
                    <div class="avatar-circle">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', Auth::user()->name)[array_key_last(explode(' ', Auth::user()->name))], 0, 1)) }}</div>
                    <div class="user-info-sidebar">
                        <span class="name">{{ Auth::user()->name }}</span>
                        <span class="role">Reviewer</span>
                    </div>
                    <i class="fas fa-chevron-down user-caret"></i>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- HEADER -->
            <div class="header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <div class="header-title">Mock Exams</div>
                        <div class="header-subtitle">Simulate the real CPA Exam and track your readiness.</div>
                    </div>
                </div>
                <div class="header-right">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search topics, questions, subjects...">
                    </div>
                    <div class="header-icons">
                        <button class="icon-btn">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                        <div class="header-dropdown-wrap">
                            <button class="profile-btn" id="profileBtn">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', Auth::user()->name)[array_key_last(explode(' ', Auth::user()->name))], 0, 1)) }}</button>
                            <div class="dropdown-menu" id="profileDropdown">
                                <a href="#"><i class="fas fa-user"></i> Profile Settings</a>
                                <a href="#"><i class="fas fa-chart-line"></i> My Progress</a>
                                <a href="#"><i class="fas fa-question-circle"></i> Help &amp; Support</a>
                                <form method="POST" action="{{ route('logout') }}" style="margin:0;padding:0;">
                                    @csrf
                                    <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STATS CARDS -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-card-icon" style="background: #fde8e8; color: #c0392b;"><i class="fas fa-clipboard-list"></i></div>
                    <div>
                        <div class="stat-card-label">Overall Average Score</div>
                        <div class="stat-card-value">68%</div>
                        <div class="stat-card-sub">Across 4 exams</div>
                        <div class="stat-card-sub green" style="margin-top: 4px;"><i class="fas fa-arrow-up"></i> 8% <span class="muted">vs last 7 days</span></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon" style="background: #e6f7ee; color: #27AE60;"><i class="fas fa-chart-line"></i></div>
                    <div>
                        <div class="stat-card-label">Exams Taken</div>
                        <div class="stat-card-value">4</div>
                        <div class="stat-card-sub">of 10 total</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon" style="background: #f1eafc; color: #8b5cf6;"><i class="fas fa-trophy"></i></div>
                    <div>
                        <div class="stat-card-label">Highest Score</div>
                        <div class="stat-card-value">78%</div>
                        <div class="stat-card-sub">FAR Mock Exam 1</div>
                        <div class="stat-card-sub" style="margin-top: 4px;">May 5, 2024</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon" style="background: #fef3e0; color: #F39C12;"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="stat-card-label">Avg. Time Used</div>
                        <div class="stat-card-value">3h 12m</div>
                        <div class="stat-card-sub">Per exam</div>
                        <div class="stat-card-sub" style="margin-top: 4px;">Target: 4 hours</div>
                    </div>
                </div>
            </div>

            <!-- MAIN GRID -->
            <div class="main-grid">
                <!-- LEFT: EXAMS -->
                <div class="card">
                    <div class="card-head">
                        <div>
                            <div class="card-title">Mock Exams</div>
                            <div class="card-subtitle">Take a full-length exam or practice by subject area.</div>
                        </div>
                        <button class="btn-primary"><i class="fas fa-plus"></i> Take New Exam</button>
                    </div>

                    <div class="tabs">
                        <div class="tab active" onclick="selectTab(this)">All Exams</div>
                        <div class="tab" onclick="selectTab(this)">By Subject</div>
                        <div class="tab" onclick="selectTab(this)">Custom Exams</div>
                    </div>

                    <table class="exam-table">
                        <thead>
                            <tr>
                                <th>Exam</th>
                                <th>Type</th>
                                <th>Score</th>
                                <th>Time Used</th>
                                <th>Date Taken</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="exam-name">FAR Mock Exam 1</span>
                                    <span class="exam-format">Full-length</span>
                                </td>
                                <td><span class="type-badge type-far">FAR</span></td>
                                <td>
                                    <span class="score-value">78%</span>
                                    <span class="score-tag above">Above Average</span>
                                </td>
                                <td>
                                    <span class="time-value">3h 45m</span>
                                    <span class="time-sub">of 4h</span>
                                </td>
                                <td>
                                    <span class="date-value">May 5, 2024</span>
                                    <span class="date-sub">2:30 PM</span>
                                </td>
                                <td><span class="status"><span class="status-dot"></span> Completed</span></td>
                                <td><i class="fas fa-ellipsis-vertical row-action"></i></td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="exam-name">AUD Mock Exam 1</span>
                                    <span class="exam-format">Full-length</span>
                                </td>
                                <td><span class="type-badge type-aud">AUD</span></td>
                                <td>
                                    <span class="score-value">65%</span>
                                    <span class="score-tag avg">Average</span>
                                </td>
                                <td>
                                    <span class="time-value">3h 10m</span>
                                    <span class="time-sub">of 4h</span>
                                </td>
                                <td>
                                    <span class="date-value">May 2, 2024</span>
                                    <span class="date-sub">9:15 AM</span>
                                </td>
                                <td><span class="status"><span class="status-dot"></span> Completed</span></td>
                                <td><i class="fas fa-ellipsis-vertical row-action"></i></td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="exam-name">TAX Mock Exam 1</span>
                                    <span class="exam-format">Full-length</span>
                                </td>
                                <td><span class="type-badge type-tax">TAX</span></td>
                                <td>
                                    <span class="score-value">60%</span>
                                    <span class="score-tag below">Below Average</span>
                                </td>
                                <td>
                                    <span class="time-value">2h 55m</span>
                                    <span class="time-sub">of 4h</span>
                                </td>
                                <td>
                                    <span class="date-value">Apr 29, 2024</span>
                                    <span class="date-sub">1:40 PM</span>
                                </td>
                                <td><span class="status"><span class="status-dot"></span> Completed</span></td>
                                <td><i class="fas fa-ellipsis-vertical row-action"></i></td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="exam-name">REG Mock Exam 1</span>
                                    <span class="exam-format">Full-length</span>
                                </td>
                                <td><span class="type-badge type-reg">REG</span></td>
                                <td>
                                    <span class="score-value">72%</span>
                                    <span class="score-tag above">Above Average</span>
                                </td>
                                <td>
                                    <span class="time-value">3h 20m</span>
                                    <span class="time-sub">of 4h</span>
                                </td>
                                <td>
                                    <span class="date-value">Apr 26, 2024</span>
                                    <span class="date-sub">10:00 AM</span>
                                </td>
                                <td><span class="status"><span class="status-dot"></span> Completed</span></td>
                                <td><i class="fas fa-ellipsis-vertical row-action"></i></td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="exam-name">FAR Mock Exam 2</span>
                                    <span class="exam-format">Full-length</span>
                                </td>
                                <td><span class="type-badge type-far">FAR</span></td>
                                <td>
                                    <span class="score-value">â€”</span>
                                    <span class="score-tag none">Not taken</span>
                                </td>
                                <td>
                                    <span class="time-value">â€”</span>
                                    <span class="time-sub">of 4h</span>
                                </td>
                                <td>
                                    <span class="date-value">â€”</span>
                                </td>
                                <td><span class="status not-taken">Not Taken</span></td>
                                <td><button class="btn-take">Take Exam</button></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="view-all">
                        <a href="#">View All Exams</a>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div>
                    <!-- PERFORMANCE OVERVIEW -->
                    <div class="card perf-card" style="margin-bottom: 25px;">
                        <div class="perf-head">
                            <div class="card-title">Performance Overview</div>
                            <select class="perf-select">
                                <option>This Week</option>
                                <option>This Month</option>
                                <option>All Time</option>
                            </select>
                        </div>
                        <div class="perf-illustration">
                            <i class="fas fa-shield-alt"></i>
                            <i class="fas fa-location-arrow"></i>
                        </div>
                        <div class="perf-text">Focus on improvement, not perfection.</div>
                        <div class="perf-subtext">Keep taking exams to unlock your performance insights.</div>
                    </div>

                    <!-- EXAM TIPS -->
                    <div class="card">
                        <div class="tips-title">Exam Tips</div>
                        <div class="tips-subtitle">Prepare smarter for your mock exams.</div>

                        <div class="tip-item">
                            <div class="tip-icon" style="background: #fde8e8; color: #c0392b;"><i class="fas fa-calendar-day"></i></div>
                            <div>
                                <div class="tip-name">Simulate Real Conditions</div>
                                <div class="tip-desc">Take exams in a quiet place, with no interruptions.</div>
                            </div>
                        </div>

                        <div class="tip-item">
                            <div class="tip-icon" style="background: #e3f0fd; color: #2f80c2;"><i class="fas fa-clock"></i></div>
                            <div>
                                <div class="tip-name">Manage Your Time</div>
                                <div class="tip-desc">Aim for 4 hours. Practice pacing for each testlet.</div>
                            </div>
                        </div>

                        <div class="tip-item">
                            <div class="tip-icon" style="background: #fef3e0; color: #F39C12;"><i class="fas fa-chart-bar"></i></div>
                            <div>
                                <div class="tip-name">Review Thoroughly</div>
                                <div class="tip-desc">Learn from your mistakes and focus on weak areas.</div>
                            </div>
                        </div>

                        <div class="tips-illustration"><i class="fas fa-book"></i> <i class="fas fa-mug-hot"></i></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });

        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }

        // Profile dropdown
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

        // Tab switching
        function selectTab(element) {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            element.classList.add('active');
        }
    </script>
</body>
</html>

