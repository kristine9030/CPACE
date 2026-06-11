<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CPACE CPA Reviewer</title>

    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #7B1D1D;
            --primary-hover: #6a1818;
            --primary-light: #f5e8e8;
            --primary-mid: #c0392b;
            --accent-red: #c0392b;
            --sidebar-bg: #7B1D1D;
            --white: #ffffff;
            --gray-100: #f8f9fa;
            --gray-200: #f0f0f0;
            --gray-300: #e0e0e0;
            --gray-500: #999999;
            --gray-700: #555555;
            --gray-900: #333333;
            --green: #10b981;
            --blue: #3b82f6;
            --orange: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f5f7;
            color: var(--gray-900);
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
            width: 44px;
            height: 44px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            flex-shrink: 0;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .logo-text { line-height: 1.25; }
        .logo-text strong { display: block; font-size: 15px; font-weight: 700; letter-spacing: 0.5px; }
        .logo-text small  { font-size: 11px; opacity: 0.8; font-weight: 400; }

        .sidebar.collapsed .logo-text { display: none; }

        .sidebar-nav {
            list-style: none;
            flex: 1;
            padding: 12px 0;
        }

        .sidebar-nav li a {
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

        .sidebar-nav li a:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }

        .sidebar-nav li a.active {
            color: white;
            background: rgba(255,255,255,0.18);
            border-left-color: white;
            font-weight: 500;
        }

        .sidebar-nav li a i {
            width: 18px;
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
        .sidebar.collapsed .sidebar-logo .logo-text { display: none; }

        .sidebar-footer {
            border-top: 1px solid rgba(255,255,255,0.12);
            padding: 16px 20px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .avatar-sm {
            width: 38px;
            height: 38px;
            background: var(--accent-red);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            color: white;
            flex-shrink: 0;
        }

        .user-details { flex: 1; min-width: 0; }
        .user-details .uname { display: block; font-size: 13px; font-weight: 600; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-details .urole { display: block; font-size: 11px; color: rgba(255,255,255,0.65); }

        .sidebar.collapsed .user-details,
        .sidebar.collapsed .chevron-icon { display: none; }

        /* ─── MAIN CONTENT ─── */
        .main-content {
            margin-left: 220px;
            padding: 28px 32px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .sidebar.collapsed ~ .main-content { margin-left: 70px; }

        /* ─── HEADER ─── */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            gap: 20px;
        }

        .page-header-left { display: flex; align-items: center; gap: 14px; }

        .toggle-btn {
            width: 38px; height: 38px;
            border: 1px solid var(--gray-300);
            background: white;
            border-radius: 8px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: var(--primary);
            font-size: 16px;
            transition: background 0.2s;
        }
        .toggle-btn:hover { background: var(--gray-200); }

        .page-title { font-size: 28px; font-weight: 700; color: var(--gray-900); line-height: 1.2; }
        .page-subtitle { font-size: 13px; color: var(--gray-500); margin-top: 2px; }

        .page-header-right { display: flex; align-items: center; gap: 14px; }

        .search-wrap {
            position: relative;
        }
        .search-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
            font-size: 14px;
        }
        .search-wrap input {
            width: 300px;
            padding: 10px 14px 10px 36px;
            border: 1px solid var(--gray-300);
            border-radius: 24px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            background: white;
            color: var(--gray-700);
            outline: none;
            transition: border-color 0.2s;
        }
        .search-wrap input:focus { border-color: var(--primary); }
        .search-wrap input::placeholder { color: #bbb; }

        .notif-btn {
            position: relative;
            width: 40px; height: 40px;
            border: none;
            background: white;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px;
            color: var(--gray-700);
            cursor: pointer;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            transition: background 0.2s;
        }
        .notif-btn:hover { background: var(--gray-200); }

        .badge {
            position: absolute;
            top: -3px; right: -3px;
            width: 18px; height: 18px;
            background: var(--accent-red);
            color: white;
            border-radius: 50%;
            font-size: 10px;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }

        .profile-avatar {
            width: 40px; height: 40px;
            background: var(--primary);
            border-radius: 10px;
            border: none;
            color: white;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: background 0.2s;
        }
        .profile-avatar:hover { background: var(--primary-hover); }

        .header-dropdown-wrap { position: relative; }

        .dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: white;
            border: 1px solid var(--gray-300);
            border-radius: 10px;
            min-width: 185px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
            display: none;
            z-index: 2000;
        }
        .dropdown-menu.active { display: block; }
        .dropdown-menu a, .dropdown-menu button {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 16px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            color: var(--gray-900);
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: background 0.2s;
            border-bottom: 1px solid #f5f5f5;
        }
        .dropdown-menu a:last-child,
        .dropdown-menu form:last-child button { border-bottom: none; }
        .dropdown-menu a:hover,
        .dropdown-menu button:hover { background: var(--gray-100); }
        .dropdown-menu a i, .dropdown-menu button i { color: var(--primary); width: 16px; text-align: center; }
        .dropdown-menu .logout-btn { color: #e53e3e; }
        .dropdown-menu .logout-btn i { color: #e53e3e; }

        /* ─── WELCOME BANNER ─── */
        .welcome-banner {
            background: white;
            border-radius: 16px;
            padding: 32px 36px;
            margin-bottom: 22px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 30px;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        .welcome-banner h2 {
            font-size: 26px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 8px;
        }

        .welcome-banner p {
            font-size: 13px;
            color: var(--gray-500);
            margin-bottom: 18px;
        }

        .exam-countdown {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 600;
            color: var(--accent-red);
        }

        .welcome-illustration {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 10px 20px;
            position: relative;
        }

        .illus-book {
            width: 70px; height: 90px;
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700;
            color: white;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            letter-spacing: 2px;
        }
        .illus-book.tax { background: linear-gradient(135deg, #c0392b, #962d22); }
        .illus-book.audit { background: linear-gradient(135deg, #7B1D1D, #5a1515); }
        .illus-book.far { background: linear-gradient(135deg, #8e44ad, #6c3483); }

        .illus-laptop {
            width: 120px; height: 85px;
            background: #2d3748;
            border-radius: 8px 8px 0 0;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: -2px;
            position: relative;
        }
        .illus-laptop::after {
            content: '';
            position: absolute;
            bottom: -10px; left: -15px;
            width: 150px; height: 10px;
            background: #1a202c;
            border-radius: 0 0 6px 6px;
        }

        .illus-laptop-screen {
            width: 104px; height: 70px;
            background: #1a1a2e;
            border-radius: 4px;
            display: flex; align-items: center; justify-content: center;
        }

        .illus-logo-screen {
            width: 48px; height: 48px;
            background: var(--sidebar-bg);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: white;
        }

        .illus-plant {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .illus-mug {
            font-size: 36px;
        }

        .welcome-deco {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        /* ─── METRICS ─── */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 22px;
        }

        .metric-card {
            background: white;
            border-radius: 14px;
            padding: 22px 22px 16px;
            position: relative;
            overflow: hidden;
        }

        .metric-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .metric-icon-wrap {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }
        .metric-icon-wrap.red   { background: #fde8e8; color: var(--accent-red); }
        .metric-icon-wrap.green { background: #d1fae5; color: var(--green); }
        .metric-icon-wrap.blue  { background: #dbeafe; color: var(--blue); }
        .metric-icon-wrap.orange{ background: #fef3c7; color: var(--orange); }

        .metric-body {}
        .metric-label { font-size: 11px; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .metric-number { font-size: 30px; font-weight: 700; color: var(--gray-900); line-height: 1; margin-bottom: 6px; }
        .metric-change { font-size: 11px; color: var(--green); }
        .metric-change.neutral { color: var(--gray-500); }

        .metric-chart { margin-top: 14px; }

        /* Sparkline charts */
        .sparkline { width: 100%; height: 36px; }
        .fire-row { display: flex; gap: 4px; margin-top: 6px; }
        .fire-icon { font-size: 18px; }
        .fire-icon.lit   { color: var(--orange); }
        .fire-icon.unlit { color: var(--gray-300); }

        /* ─── CONTENT GRID ─── */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 330px;
            gap: 18px;
            margin-bottom: 18px;
        }

        /* ─── CARDS ─── */
        .card {
            background: white;
            border-radius: 14px;
            padding: 22px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .card-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--gray-900);
        }

        .card-link {
            font-size: 12px;
            color: var(--accent-red);
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
        }
        .card-link:hover { text-decoration: underline; }

        /* ─── SUBJECT MASTERY ─── */
        .subject-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 8px;
            border-bottom: 1px solid #f5f5f5;
            cursor: pointer;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .subject-item:last-child { border-bottom: none; }
        .subject-item:hover { background: var(--gray-100); }

        .subject-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }
        .subject-icon.s1 { background: #fde8e8; color: var(--accent-red); }
        .subject-icon.s2 { background: #fde8e8; color: var(--accent-red); }
        .subject-icon.s3 { background: #d1fae5; color: var(--green); }
        .subject-icon.s4 { background: #dbeafe; color: var(--blue); }
        .subject-icon.s5 { background: #fef3c7; color: var(--orange); }

        .subject-name { flex: 1; font-size: 13px; color: var(--gray-900); }
        .subject-arrow { color: var(--gray-500); font-size: 12px; }

        /* ─── TOP WEAKNESSES ─── */
        .weakness-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 13px 0;
            border-bottom: 1px solid #f5f5f5;
            cursor: pointer;
        }
        .weakness-item:last-child { border-bottom: none; }

        .weakness-num {
            width: 30px; height: 30px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 13px;
            color: white; flex-shrink: 0;
        }
        .weakness-num.n1 { background: var(--accent-red); }
        .weakness-num.n2 { background: var(--orange); }
        .weakness-num.n3 { background: var(--primary); }

        .weakness-info { flex: 1; }
        .weakness-title { font-size: 13px; font-weight: 600; color: var(--gray-900); margin-bottom: 2px; }
        .weakness-sub   { font-size: 11px; color: var(--gray-500); }
        .weakness-arrow { color: var(--gray-500); font-size: 12px; }

        /* ─── RIGHT PANEL ─── */
        .right-panel { display: flex; flex-direction: column; gap: 18px; }

        /* Overall Progress */
        .progress-wrap { text-align: center; }

        .progress-circle-container {
            width: 160px; height: 160px;
            margin: 0 auto 16px;
            position: relative;
            display: flex; align-items: center; justify-content: center;
        }

        .progress-circle-container svg {
            width: 100%; height: 100%;
            transform: rotate(-90deg);
        }

        .progress-inner {
            position: absolute;
            text-align: center;
        }

        .progress-pct { font-size: 34px; font-weight: 700; color: var(--gray-900); line-height: 1; }
        .progress-lbl { font-size: 12px; color: var(--gray-500); margin-top: 3px; }

        .progress-legend {
            display: flex;
            justify-content: center;
            gap: 18px;
            font-size: 12px;
            color: var(--gray-700);
            margin-bottom: 20px;
        }
        .legend-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
            vertical-align: middle;
        }
        .legend-dot.done { background: var(--primary); }
        .legend-dot.left { background: #f0c9c9; }

        /* Quick Action Buttons */
        .quick-btn {
            width: 100%;
            padding: 13px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            margin-bottom: 10px;
            text-decoration: none;
        }
        .quick-btn:last-child { margin-bottom: 0; }
        .quick-btn.primary {
            background: var(--primary);
            color: white;
            border: none;
        }
        .quick-btn.primary:hover { background: var(--primary-hover); }
        .quick-btn.outline {
            background: white;
            color: var(--primary);
            border: 1.5px solid var(--primary);
        }
        .quick-btn.outline:hover { background: var(--primary-light); }

        /* Recent Activity */
        .activity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        .activity-item:last-child { border-bottom: none; }

        .activity-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .activity-icon.quiz { background: #d1fae5; color: var(--green); }

        .activity-info { flex: 1; }
        .activity-name { font-size: 13px; font-weight: 600; color: var(--gray-900); margin-bottom: 2px; }
        .activity-meta { font-size: 11px; color: var(--gray-500); }

        .activity-right { text-align: right; flex-shrink: 0; }
        .activity-time { font-size: 11px; color: var(--gray-500); }
        .activity-chevron { font-size: 11px; color: var(--gray-500); margin-top: 4px; }

        /* ─── BOTTOM ROW ─── */
        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        /* Study Streak */
        .streak-card {
            background: white;
            border-radius: 14px;
            padding: 28px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            overflow: hidden;
        }

        .streak-title { font-size: 15px; font-weight: 600; color: var(--gray-900); margin-bottom: 14px; }

        .streak-num-row { display: flex; align-items: baseline; gap: 6px; margin-bottom: 4px; }
        .streak-num { font-size: 52px; font-weight: 700; color: var(--gray-900); line-height: 1; }
        .streak-unit { font-size: 18px; font-weight: 500; color: var(--gray-500); }
        .streak-sub { font-size: 13px; color: var(--gray-500); }

        .streak-deco { font-size: 90px; color: #f0c9c9; flex-shrink: 0; opacity: 0.7; }

        /* Quote Card */
        .quote-card {
            background: white;
            border-radius: 14px;
            padding: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            overflow: hidden;
        }

        .quote-body {}
        .quote-marks { font-size: 48px; color: var(--primary-light); line-height: 0.8; margin-bottom: 10px; font-family: Georgia, serif; color: #e0d0d0; }
        .quote-text { font-size: 14px; color: var(--gray-700); line-height: 1.6; font-style: italic; margin-bottom: 12px; }
        .quote-author { font-size: 13px; color: var(--gray-500); font-weight: 500; }

        .quote-deco { font-size: 80px; flex-shrink: 0; opacity: 0.6; color: #f0c9c9; }

        /* ─── CARD MENU ─── */
        .card-menu-btn {
            background: none; border: none;
            color: var(--gray-500); font-size: 16px;
            cursor: pointer; padding: 2px 6px;
        }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 1280px) {
            .content-grid { grid-template-columns: 1fr 1fr; }
            .content-grid .right-panel { grid-column: 1 / 3; }
            .metrics-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 900px) {
            .sidebar { width: 70px; }
            .main-content { margin-left: 70px; }
            .content-grid { grid-template-columns: 1fr; }
            .content-grid .right-panel { grid-column: 1; }
            .bottom-grid { grid-template-columns: 1fr; }
            .metrics-grid { grid-template-columns: repeat(2, 1fr); }
            .search-wrap input { width: 180px; }
        }

        /* ─── ANIMATION ─── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .anim { animation: fadeUp 0.45s ease both; }
    </style>
</head>
<body>

<!-- ════════════════════════ SIDEBAR ════════════════════════ -->
<aside class="sidebar" id="sidebar">
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
        <li><a href="{{ route('dashboard') }}" class="active"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
        <li><a href="{{ route('subjects') }}"><i class="fas fa-book-open"></i><span>Subjects</span></a></li>
        <li><a href="{{ route('adaptive-quizzes') }}"><i class="fas fa-pen-fancy"></i><span>Quizzes</span></a></li>
        <li><a href="{{ route('mock-exams') }}"><i class="fas fa-file-alt"></i><span>Mock Exams</span></a></li>
        <li><a href="{{ route('performance') }}"><i class="fas fa-chart-bar"></i><span>Performance</span></a></li>
        <li><a href="{{ route('review-notes') }}"><i class="fas fa-sticky-note"></i><span>Review Notes</span></a></li>
        <li><a href="#"><i class="fas fa-layer-group"></i><span>Flashcards</span></a></li>
        <li><a href="{{ route('calendar') }}"><i class="fas fa-calendar-alt"></i><span>Calendar</span></a></li>
        <li><a href="#"><i class="fas fa-chart-line"></i><span>Progress</span></a></li>
        <li><a href="{{ route('achievements') }}"><i class="fas fa-trophy"></i><span>Achievements</span></a></li>
        <li><a href="#"><i class="fas fa-cog"></i><span>Settings</span></a></li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-profile">
            <div class="avatar-sm">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', Auth::user()->name)[array_key_last(explode(' ', Auth::user()->name))], 0, 1)) }}
            </div>
            <div class="user-details">
                <span class="uname">{{ Auth::user()->name }}</span>
                <span class="urole">Reviewer</span>
            </div>
            <i class="fas fa-chevron-down chevron-icon" style="color:rgba(255,255,255,0.6); font-size:11px;"></i>
        </div>
    </div>
</aside>

<!-- ════════════════════════ MAIN CONTENT ════════════════════════ -->
<main class="main-content">

    <!-- HEADER -->
    <div class="page-header anim" style="animation-delay:0s">
        <div class="page-header-left">
            <button class="toggle-btn" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <div>
                <div class="page-title">Dashboard</div>
                <div class="page-subtitle">Welcome back, {{ Auth::user()->name }}! Let's keep up the momentum.</div>
            </div>
        </div>
        <div class="page-header-right">
            <div class="search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search topics, questions, subjects...">
            </div>
            <button class="notif-btn">
                <i class="fas fa-bell"></i>
                <span class="badge">3</span>
            </button>
            <div class="header-dropdown-wrap">
                <button class="profile-avatar" id="profileBtn">KD</button>
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

    <!-- WELCOME BANNER -->
    <div class="welcome-banner anim" style="animation-delay:0.08s">
        <div class="welcome-content">
            <h2>Good morning, {{ explode(' ', Auth::user()->name)[0] }}! &#128075;</h2>
            <p>Every day you study brings you closer to your goal.</p>
            <div class="exam-countdown">
                <i class="fas fa-fire-alt"></i>
                78 days until board exam
            </div>
        </div>
        <div class="welcome-illustration">
            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
                <div style="display:flex; align-items:flex-end; gap:8px;">
                    <div style="display:flex; flex-direction:column; gap:4px;">
                        <div class="illus-book tax">TAX</div>
                        <div class="illus-book audit" style="height:75px;">AUDIT</div>
                        <div class="illus-book far" style="height:65px; background:linear-gradient(135deg,#e67e22,#ca6f1e);">FAR</div>
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:center; gap:10px;">
                        <div>&#127807;</div>
                        <div class="illus-laptop">
                            <div class="illus-laptop-screen">
                                <div class="illus-logo-screen">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                            </div>
                        </div>
                        <div style="font-size:28px;">&#9749;</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- METRICS -->
    <div class="metrics-grid anim" style="animation-delay:0.14s">
        <!-- Board Readiness Score -->
        <div class="metric-card">
            <div class="metric-top">
                <div class="metric-icon-wrap red"><i class="fas fa-chart-area"></i></div>
            </div>
            <div class="metric-label">Board Readiness Score</div>
            <div class="metric-number">78%</div>
            <div class="metric-change"><i class="fas fa-arrow-up"></i> 5% from last week</div>
            <div class="metric-chart">
                <svg class="sparkline" viewBox="0 0 100 36" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="redGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#c0392b" stop-opacity="0.3"/>
                            <stop offset="100%" stop-color="#c0392b" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <path d="M0,30 L14,26 L28,22 L42,20 L56,16 L70,13 L84,9 L100,5" fill="none" stroke="#c0392b" stroke-width="2" stroke-linecap="round"/>
                    <path d="M0,30 L14,26 L28,22 L42,20 L56,16 L70,13 L84,9 L100,5 L100,36 L0,36 Z" fill="url(#redGrad)"/>
                </svg>
            </div>
        </div>

        <!-- Questions Answered -->
        <div class="metric-card">
            <div class="metric-top">
                <div class="metric-icon-wrap green"><i class="fas fa-clipboard-check"></i></div>
            </div>
            <div class="metric-label">Questions Answered</div>
            <div class="metric-number">1,247</div>
            <div class="metric-change"><i class="fas fa-arrow-up"></i> 128 this week</div>
            <div class="metric-chart">
                <svg class="sparkline" viewBox="0 0 100 36" preserveAspectRatio="none">
                    <rect x="2"  y="20" width="10" height="16" rx="2" fill="#10b981" opacity="0.5"/>
                    <rect x="16" y="14" width="10" height="22" rx="2" fill="#10b981" opacity="0.6"/>
                    <rect x="30" y="18" width="10" height="18" rx="2" fill="#10b981" opacity="0.65"/>
                    <rect x="44" y="10" width="10" height="26" rx="2" fill="#10b981" opacity="0.75"/>
                    <rect x="58" y="12" width="10" height="24" rx="2" fill="#10b981" opacity="0.8"/>
                    <rect x="72" y="6"  width="10" height="30" rx="2" fill="#10b981" opacity="0.9"/>
                    <rect x="86" y="2"  width="10" height="34" rx="2" fill="#10b981"/>
                </svg>
            </div>
        </div>

        <!-- Study Time -->
        <div class="metric-card">
            <div class="metric-top">
                <div class="metric-icon-wrap blue"><i class="fas fa-clock"></i></div>
            </div>
            <div class="metric-label">Study Time</div>
            <div class="metric-number">42h</div>
            <div class="metric-change"><i class="fas fa-arrow-up"></i> 8h this week</div>
            <div class="metric-chart">
                <svg class="sparkline" viewBox="0 0 100 36" preserveAspectRatio="none">
                    <rect x="2"  y="24" width="10" height="12" rx="2" fill="#3b82f6" opacity="0.5"/>
                    <rect x="16" y="20" width="10" height="16" rx="2" fill="#3b82f6" opacity="0.6"/>
                    <rect x="30" y="16" width="10" height="20" rx="2" fill="#3b82f6" opacity="0.65"/>
                    <rect x="44" y="18" width="10" height="18" rx="2" fill="#3b82f6" opacity="0.7"/>
                    <rect x="58" y="10" width="10" height="26" rx="2" fill="#3b82f6" opacity="0.8"/>
                    <rect x="72" y="8"  width="10" height="28" rx="2" fill="#3b82f6" opacity="0.9"/>
                    <rect x="86" y="4"  width="10" height="32" rx="2" fill="#3b82f6"/>
                </svg>
            </div>
        </div>

        <!-- Day Streak -->
        <div class="metric-card">
            <div class="metric-top">
                <div class="metric-icon-wrap orange"><i class="fas fa-fire"></i></div>
            </div>
            <div class="metric-label">Day Streak</div>
            <div class="metric-number">14</div>
            <div class="metric-change neutral">Keep it up!</div>
            <div class="metric-chart">
                <div class="fire-row">
                    <span class="fire-icon lit">&#128293;</span>
                    <span class="fire-icon lit">&#128293;</span>
                    <span class="fire-icon lit">&#128293;</span>
                    <span class="fire-icon lit">&#128293;</span>
                    <span class="fire-icon unlit"><i class="fas fa-fire" style="color:#e0e0e0;"></i></span>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENT GRID -->
    <div class="content-grid anim" style="animation-delay:0.2s">

        <!-- Subject Mastery -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Subject Mastery</span>
            </div>
            <div class="subject-item">
                <div class="subject-icon s1"><i class="fas fa-book"></i></div>
                <span class="subject-name">Financial Accounting &amp; Reporting</span>
                <i class="fas fa-chevron-right subject-arrow"></i>
            </div>
            <div class="subject-item">
                <div class="subject-icon s2"><i class="fas fa-search"></i></div>
                <span class="subject-name">Auditing</span>
                <i class="fas fa-chevron-right subject-arrow"></i>
            </div>
            <div class="subject-item">
                <div class="subject-icon s3"><i class="fas fa-table"></i></div>
                <span class="subject-name">Taxation</span>
                <i class="fas fa-chevron-right subject-arrow"></i>
            </div>
            <div class="subject-item">
                <div class="subject-icon s4"><i class="fas fa-users"></i></div>
                <span class="subject-name">Management Services</span>
                <i class="fas fa-chevron-right subject-arrow"></i>
            </div>
            <div class="subject-item">
                <div class="subject-icon s5"><i class="fas fa-balance-scale"></i></div>
                <span class="subject-name">Regulatory Framework</span>
                <i class="fas fa-chevron-right subject-arrow"></i>
            </div>
        </div>

        <!-- Top Weaknesses -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Top Weaknesses</span>
                <a class="card-link" href="#">Focus Areas</a>
            </div>
            <div class="weakness-item">
                <div class="weakness-num n1">1</div>
                <div class="weakness-info">
                    <div class="weakness-title">Estate Tax Computation</div>
                    <div class="weakness-sub">Taxation</div>
                </div>
                <i class="fas fa-chevron-right weakness-arrow"></i>
            </div>
            <div class="weakness-item">
                <div class="weakness-num n2">2</div>
                <div class="weakness-info">
                    <div class="weakness-title">Revenue Recognition</div>
                    <div class="weakness-sub">FAR &ndash; PFRS 15</div>
                </div>
                <i class="fas fa-chevron-right weakness-arrow"></i>
            </div>
            <div class="weakness-item">
                <div class="weakness-num n3">3</div>
                <div class="weakness-info">
                    <div class="weakness-title">Audit Sampling</div>
                    <div class="weakness-sub">Auditing &ndash; PSA 530</div>
                </div>
                <i class="fas fa-chevron-right weakness-arrow"></i>
            </div>
        </div>

        <!-- RIGHT PANEL -->
        <div class="right-panel">
            <!-- Overall Progress -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Overall Progress</span>
                    <button class="card-menu-btn"><i class="fas fa-ellipsis-h"></i></button>
                </div>
                <div class="progress-wrap">
                    <div class="progress-circle-container">
                        <svg viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="42" fill="none" stroke="#f0c9c9" stroke-width="9"/>
                            <circle cx="50" cy="50" r="42" fill="none" stroke="#7B1D1D" stroke-width="9"
                                    stroke-dasharray="205.8 263.9" stroke-linecap="round"/>
                        </svg>
                        <div class="progress-inner">
                            <div class="progress-pct">78%</div>
                            <div class="progress-lbl">Complete</div>
                        </div>
                    </div>
                    <div class="progress-legend">
                        <span><span class="legend-dot done"></span>Completed</span>
                        <span><span class="legend-dot left"></span>Remaining</span>
                    </div>
                </div>
                <a href="#" class="quick-btn primary">Start Quick Quiz &rarr;</a>
                <a href="#" class="quick-btn outline">Continue Last Session</a>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Recent Activity</span>
                    <a class="card-link" href="#">View All</a>
                </div>
                <div class="activity-item">
                    <div class="activity-icon quiz"><i class="fas fa-clipboard-list"></i></div>
                    <div class="activity-info">
                        <div class="activity-name">Adaptive Quiz &ndash; FAR</div>
                        <div class="activity-meta">20 Questions &bull; Score: 85%</div>
                    </div>
                    <div class="activity-right">
                        <div class="activity-time">2h ago</div>
                        <div class="activity-chevron"><i class="fas fa-chevron-right"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTTOM ROW -->
    <div class="bottom-grid anim" style="animation-delay:0.26s">
        <!-- Study Streak -->
        <div class="streak-card">
            <div>
                <div class="streak-title">Study Streak</div>
                <div class="streak-num-row">
                    <span class="streak-num">14</span>
                    <span class="streak-unit">days</span>
                </div>
                <div class="streak-sub">Keep the momentum going!</div>
            </div>
            <div style="font-size:72px; opacity:0.35; user-select:none;">&#128197;&#127807;</div>
        </div>

        <!-- Quote -->
        <div class="quote-card">
            <div class="quote-body">
                <div class="quote-marks">&ldquo;</div>
                <div class="quote-text">Success is the sum of small efforts, repeated day in and day out.</div>
                <div class="quote-author">&mdash; Robert Collier</div>
            </div>
            <div style="font-size:70px; opacity:0.3; user-select:none; flex-shrink:0;">&#128218;&#128161;</div>
        </div>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Sidebar toggle
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
});
</script>
</body>
</html>
