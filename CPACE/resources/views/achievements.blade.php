<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievements - CPACE CPA Reviewer</title>

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
            background: #8B3A3A;
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
            margin-bottom: 60px;
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
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: white;
            color: #8B3A3A;
            padding: 8px 14px;
            border-radius: 6px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .challenge-box a:hover {
            background: #f9f9f9;
            transform: translateY(-2px);
        }

        .challenge-trophy {
            position: absolute;
            right: 10px;
            top: 8px;
            font-size: 34px;
            color: #f4b740;
            opacity: 0.9;
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
            background: #d84949;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
        }

        .user-info-sidebar {
            flex: 1;
            font-family: 'Poppins', sans-serif;
        }

        .sidebar.collapsed .user-info-sidebar {
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
            align-items: center;
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
            color: #8B3A3A;
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
            flex: 0 1 320px;
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #d84949;
            font-size: 14px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 8px;
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
            background: white;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            position: relative;
            color: #555;
        }

        .icon-btn:hover {
            background: #f0f0f0;
        }

        .notification-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            width: 18px;
            height: 18px;
            background: #d84949;
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
            background: #8B3A3A;
            border: none;
            border-radius: 50%;
            color: white;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
        }

        .profile-btn:hover {
            background: #6d2e2e;
        }

        /* STATUS BANNER */
        .status-banner {
            background: white;
            border-radius: 14px;
            padding: 26px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 25px;
        }

        .banner-profile {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .banner-avatar {
            width: 84px;
            height: 84px;
            background: #fdeaea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            font-weight: 700;
            color: #d84949;
            flex-shrink: 0;
        }

        .banner-name {
            font-size: 22px;
            font-weight: 700;
            color: #222;
        }

        .banner-role {
            font-size: 14px;
            color: #999;
            margin-bottom: 8px;
        }

        .banner-tag {
            display: inline-block;
            background: #fdeaea;
            color: #d84949;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 14px;
            border-radius: 20px;
        }

        .banner-status {
            flex: 1;
            text-align: center;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
        }

        .banner-laurel {
            font-size: 56px;
            color: #f4b740;
            opacity: 0.85;
        }

        .banner-status-label {
            font-size: 14px;
            color: #999;
            margin-bottom: 4px;
        }

        .banner-status-value {
            font-size: 34px;
            font-weight: 700;
            color: #d84949;
            line-height: 1;
            margin-bottom: 6px;
        }

        .banner-status-sub {
            font-size: 13px;
            color: #999;
            max-width: 200px;
            margin: 0 auto;
            line-height: 1.4;
        }

        .banner-stats {
            display: flex;
            gap: 18px;
        }

        .stat-box {
            background: #fdfbf6;
            border: 1px solid #f3eede;
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 165px;
        }

        .stat-box.days {
            background: #f5f8fd;
            border-color: #e4edf8;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .stat-icon.star {
            background: #fcedcb;
            color: #f1a417;
        }

        .stat-icon.flame {
            background: #e0ecfb;
            color: #4a90d9;
        }

        .stat-label {
            font-size: 12px;
            color: #999;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #222;
            line-height: 1.1;
        }

        .stat-extra {
            font-size: 12px;
            color: #27AE60;
            font-weight: 600;
        }

        .stat-extra.muted {
            color: #aaa;
            font-weight: 500;
        }

        /* LAYOUT */
        .achievements-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 25px;
            align-items: start;
        }

        .panel {
            background: white;
            border-radius: 14px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }

        .panel + .panel {
            margin-top: 25px;
        }

        .panel-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .panel-title {
            font-size: 19px;
            font-weight: 700;
            color: #222;
        }

        .panel-link {
            font-size: 13px;
            font-weight: 600;
            color: #d84949;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .panel-link:hover {
            text-decoration: underline;
        }

        /* FILTER TABS */
        .badge-tabs {
            display: flex;
            gap: 10px;
            margin: 18px 0 22px;
            flex-wrap: wrap;
        }

        .badge-tab {
            padding: 7px 18px;
            border: 1px solid #eee;
            background: white;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            color: #777;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
        }

        .badge-tab.active {
            background: #fdeaea;
            border-color: #fdeaea;
            color: #d84949;
            font-weight: 600;
        }

        .badge-tab:hover {
            border-color: #d84949;
        }

        /* BADGE GRID */
        .badge-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }

        .badge-card {
            border: 1px solid #f0f0f0;
            border-radius: 12px;
            padding: 20px 14px;
            text-align: center;
            transition: all 0.2s;
        }

        .badge-card:hover {
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            transform: translateY(-2px);
        }

        .badge-card.locked {
            opacity: 0.75;
        }

        .badge-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin: 0 auto 14px;
        }

        .badge-icon.red { background: #fdeaea; color: #d84949; }
        .badge-icon.green { background: #e8f6ee; color: #27AE60; }
        .badge-icon.blue { background: #e5eefc; color: #4a7fd9; }
        .badge-icon.yellow { background: #fcedcb; color: #f1a417; }
        .badge-icon.purple { background: #eee7fb; color: #8b5cf6; }
        .badge-icon.pink { background: #fdeaf0; color: #e0588a; }
        .badge-icon.teal { background: #e2f4f3; color: #1aa39a; }
        .badge-icon.gray { background: #eeeeee; color: #999; }

        .badge-name {
            font-size: 15px;
            font-weight: 600;
            color: #222;
            margin-bottom: 8px;
        }

        .badge-desc {
            font-size: 12px;
            color: #888;
            line-height: 1.5;
            margin-bottom: 14px;
            min-height: 36px;
        }

        .badge-earned {
            font-size: 12px;
            color: #aaa;
        }

        /* BADGE PROGRESS */
        .progress-head {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 4px;
        }

        .progress-count {
            font-size: 14px;
            color: #888;
        }

        .progress-count strong {
            color: #222;
            font-weight: 700;
        }

        .progress-sub {
            font-size: 13px;
            color: #999;
            margin-bottom: 22px;
        }

        .progress-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 26px;
        }

        .progress-item-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .progress-tier-icon {
            font-size: 20px;
        }

        .progress-tier-icon.beginner { color: #27AE60; }
        .progress-tier-icon.intermediate { color: #f1a417; }
        .progress-tier-icon.advanced { color: #8b5cf6; }
        .progress-tier-icon.legend { color: #d84949; }

        .progress-tier-name {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .progress-tier-count {
            font-size: 12px;
            color: #999;
        }

        .progress-bar {
            height: 7px;
            background: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar span {
            display: block;
            height: 100%;
            border-radius: 10px;
        }

        .progress-bar span.beginner { background: #27AE60; }
        .progress-bar span.intermediate { background: #f1a417; }
        .progress-bar span.advanced { background: #8b5cf6; }
        .progress-bar span.legend { background: #d84949; }

        /* LEADERBOARD */
        .leaderboard-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .leaderboard-select {
            padding: 7px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 12px;
            color: #555;
            font-family: 'Poppins', sans-serif;
            background: white;
            cursor: pointer;
        }

        .leaderboard-cols {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #aaa;
            font-weight: 600;
            padding: 0 4px 12px;
            border-bottom: 1px solid #f2f2f2;
        }

        .leaderboard-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 11px 8px;
            border-radius: 8px;
        }

        .leaderboard-row.me {
            background: #fdeaea;
        }

        .rank {
            width: 24px;
            font-size: 13px;
            font-weight: 600;
            color: #888;
            text-align: center;
            flex-shrink: 0;
        }

        .rank-badge {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }

        .rank-badge.gold { background: #f1c40f; }
        .rank-badge.silver { background: #bdc3c7; }
        .rank-badge.bronze { background: #cd7f32; }

        .leaderboard-learner {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #444;
        }

        .me-avatar {
            width: 26px;
            height: 26px;
            background: #8B3A3A;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            color: white;
        }

        .leaderboard-row.me .leaderboard-learner {
            font-weight: 600;
            color: #8B3A3A;
        }

        .leaderboard-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            margin-top: 16px;
            padding: 12px;
            background: #fdeaea;
            color: #d84949;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
        }

        .leaderboard-btn:hover {
            background: #fbdcdc;
        }

        /* STATUS CARD */
        .status-card {
            text-align: center;
        }

        .status-card-laurel {
            font-size: 28px;
            color: #f4b740;
            margin-bottom: 6px;
        }

        .status-card-label {
            font-size: 14px;
            color: #999;
        }

        .status-card-value {
            font-size: 30px;
            font-weight: 700;
            color: #d84949;
            line-height: 1.1;
            margin-bottom: 8px;
        }

        .status-card-text {
            font-size: 13px;
            color: #888;
            line-height: 1.5;
            margin-bottom: 16px;
        }

        .status-card-text strong {
            color: #333;
        }

        .status-card-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #e8f6ee;
            color: #27AE60;
            font-size: 13px;
            font-weight: 600;
            padding: 10px 18px;
            border-radius: 8px;
            width: 100%;
            justify-content: center;
        }

        /* RESPONSIVE */
        @media (max-width: 1100px) {
            .achievements-layout {
                grid-template-columns: 1fr;
            }
            .status-banner {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }
            .banner-profile {
                justify-content: center;
            }
        }

        @media (max-width: 900px) {
            .badge-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .progress-grid {
                grid-template-columns: repeat(2, 1fr);
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
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            .header-right {
                width: 100%;
            }
            .search-box {
                flex: 1;
            }
            .banner-stats {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <div class="sidebar-logo-icon"><i class="fas fa-bullseye"></i></div>
                <div class="sidebar-logo-text">
                    <strong>CPACE</strong>
                    <small>CPA Reviewer</small>
                </div>
            </div>

            <nav class="sidebar-nav">
                <li><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
                <li><a href="{{ route('subjects') }}"><i class="fas fa-book"></i><span>Subjects</span></a></li>
                <li><a href="{{ route('adaptive-quizzes') }}"><i class="fas fa-brain"></i><span>Adaptive Quizzes</span></a></li>
                <li><a href="{{ route('mock-exams') }}"><i class="fas fa-file-alt"></i><span>Mock Exams</span></a></li>
                <li><a href="{{ route('performance') }}"><i class="fas fa-chart-line"></i><span>Performance</span></a></li>
                <li><a href="{{ route('review-notes') }}"><i class="fas fa-sticky-note"></i><span>Review Notes</span></a></li>
                <li><a href="#"><i class="fas fa-layer-group"></i><span>Flashcards</span></a></li>
                <li><a href="{{ route('calendar') }}"><i class="fas fa-calendar-alt"></i><span>Calendar</span></a></li>
                <li><a href="#"><i class="fas fa-chart-bar"></i><span>Progress</span></a></li>
                <li><a href="{{ route('achievements') }}" class="active"><i class="fas fa-award"></i><span>Achievements</span></a></li>
                <li><a href="#"><i class="fas fa-cog"></i><span>Settings</span></a></li>
            </nav>

            <div class="sidebar-challenge">
                <div class="challenge-box">
                    <i class="fas fa-trophy challenge-trophy"></i>
                    <p style="font-size: 13px; font-weight: 700;">Keep going strong!</p>
                    <p style="font-weight: 400; opacity: 0.85;">Every step brings you closer to your goal.</p>
                    <a href="#">View Study Plan <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <div class="sidebar-footer">
                <div class="user-avatar">
                    <div class="avatar-circle">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', Auth::user()->name)[array_key_last(explode(' ', Auth::user()->name))], 0, 1)) }}</div>
                    <div class="user-info-sidebar">
                        <span class="name">{{ Auth::user()->name }}</span>
                        <span class="role">Reviewer</span>
                    </div>
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
                        <div class="header-title">Achievements</div>
                        <div class="header-subtitle">Celebrate your progress and compete with others.</div>
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
                        <button class="profile-btn">KD</button>
                    </div>
                </div>
            </div>

            <!-- STATUS BANNER -->
            <div class="status-banner">
                <div class="banner-profile">
                    <div class="banner-avatar">KD</div>
                    <div>
                        <div class="banner-name">Kristine D.</div>
                        <div class="banner-role">Reviewer</div>
                        <span class="banner-tag">CPALE Aspirant</span>
                    </div>
                </div>

                <div class="banner-status">
                    <i class="fas fa-leaf banner-laurel" style="transform: scaleX(-1);"></i>
                    <div>
                        <div class="banner-status-label">Your Status</div>
                        <div class="banner-status-value">Top 8</div>
                        <div class="banner-status-sub">Keep it up! You're on your way to the top.</div>
                    </div>
                    <i class="fas fa-leaf banner-laurel"></i>
                </div>

                <div class="banner-stats">
                    <div class="stat-box">
                        <div class="stat-icon star"><i class="fas fa-star"></i></div>
                        <div>
                            <div class="stat-label">Badges Earned</div>
                            <div class="stat-value">18</div>
                            <div class="stat-extra">+2 this month</div>
                        </div>
                    </div>
                    <div class="stat-box days">
                        <div class="stat-icon flame"><i class="fas fa-fire"></i></div>
                        <div>
                            <div class="stat-label">Days Active</div>
                            <div class="stat-value">34</div>
                            <div class="stat-extra muted">Keep the streak going!</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LAYOUT -->
            <div class="achievements-layout">
                <!-- LEFT COLUMN -->
                <div>
                    <!-- BADGES -->
                    <div class="panel">
                        <div class="panel-head">
                            <div class="panel-title">Your Badges</div>
                            <a href="#" class="panel-link">View All Badges <i class="fas fa-arrow-right"></i></a>
                        </div>

                        <div class="badge-tabs">
                            <button class="badge-tab active">All</button>
                            <button class="badge-tab">Milestone</button>
                            <button class="badge-tab">Performance</button>
                            <button class="badge-tab">Consistency</button>
                            <button class="badge-tab">Special</button>
                        </div>

                        <div class="badge-grid">
                            <div class="badge-card">
                                <div class="badge-icon red"><i class="fas fa-bullseye"></i></div>
                                <div class="badge-name">First Step</div>
                                <div class="badge-desc">Complete your first adaptive quiz.</div>
                                <div class="badge-earned">Earned on May 1, 2025</div>
                            </div>
                            <div class="badge-card">
                                <div class="badge-icon green"><i class="fas fa-chart-line"></i></div>
                                <div class="badge-name">Consistent Learner</div>
                                <div class="badge-desc">Study for 7 days in a row.</div>
                                <div class="badge-earned">Earned on May 6, 2025</div>
                            </div>
                            <div class="badge-card">
                                <div class="badge-icon blue"><i class="fas fa-book-open"></i></div>
                                <div class="badge-name">Topic Explorer</div>
                                <div class="badge-desc">Complete quizzes in 10 different topics.</div>
                                <div class="badge-earned">Earned on May 10, 2025</div>
                            </div>
                            <div class="badge-card">
                                <div class="badge-icon yellow"><i class="fas fa-bolt"></i></div>
                                <div class="badge-name">Quick Thinker</div>
                                <div class="badge-desc">Answer 20 questions in under 10 minutes.</div>
                                <div class="badge-earned">Earned on May 12, 2025</div>
                            </div>
                            <div class="badge-card">
                                <div class="badge-icon purple"><i class="fas fa-chart-area"></i></div>
                                <div class="badge-name">Score Booster</div>
                                <div class="badge-desc">Improve accuracy by 10% in a week.</div>
                                <div class="badge-earned">Earned on May 15, 2025</div>
                            </div>
                            <div class="badge-card">
                                <div class="badge-icon pink"><i class="fas fa-trophy"></i></div>
                                <div class="badge-name">Mock Master</div>
                                <div class="badge-desc">Complete 5 mock exams.</div>
                                <div class="badge-earned">Earned on May 18, 2025</div>
                            </div>
                            <div class="badge-card">
                                <div class="badge-icon teal"><i class="fas fa-clock"></i></div>
                                <div class="badge-name">Time Manager</div>
                                <div class="badge-desc">Finish 10 timed quizzes with 70%+ accuracy.</div>
                                <div class="badge-earned">Earned on May 20, 2025</div>
                            </div>
                            <div class="badge-card locked">
                                <div class="badge-icon gray"><i class="fas fa-lock"></i></div>
                                <div class="badge-name">Perfectionist</div>
                                <div class="badge-desc">Achieve 90% or higher in any mock exam.</div>
                                <div class="badge-earned">Earned on May 22, 2025</div>
                            </div>
                        </div>
                    </div>

                    <!-- BADGE PROGRESS -->
                    <div class="panel">
                        <div class="progress-head">
                            <div class="panel-title">Badge Progress</div>
                            <div class="progress-count"><strong>18</strong> / 30 badges earned</div>
                        </div>
                        <div class="progress-sub">Keep earning badges and unlock more achievements!</div>

                        <div class="progress-grid">
                            <div class="progress-item">
                                <div class="progress-item-head">
                                    <i class="fas fa-seedling progress-tier-icon beginner"></i>
                                    <div>
                                        <div class="progress-tier-name">Beginner</div>
                                        <div class="progress-tier-count">5 / 6</div>
                                    </div>
                                </div>
                                <div class="progress-bar"><span class="beginner" style="width: 83%;"></span></div>
                            </div>
                            <div class="progress-item">
                                <div class="progress-item-head">
                                    <i class="fas fa-spa progress-tier-icon intermediate"></i>
                                    <div>
                                        <div class="progress-tier-name">Intermediate</div>
                                        <div class="progress-tier-count">7 / 10</div>
                                    </div>
                                </div>
                                <div class="progress-bar"><span class="intermediate" style="width: 70%;"></span></div>
                            </div>
                            <div class="progress-item">
                                <div class="progress-item-head">
                                    <i class="fas fa-tree progress-tier-icon advanced"></i>
                                    <div>
                                        <div class="progress-tier-name">Advanced</div>
                                        <div class="progress-tier-count">4 / 8</div>
                                    </div>
                                </div>
                                <div class="progress-bar"><span class="advanced" style="width: 50%;"></span></div>
                            </div>
                            <div class="progress-item">
                                <div class="progress-item-head">
                                    <i class="fas fa-crown progress-tier-icon legend"></i>
                                    <div>
                                        <div class="progress-tier-name">Legend</div>
                                        <div class="progress-tier-count">2 / 6</div>
                                    </div>
                                </div>
                                <div class="progress-bar"><span class="legend" style="width: 33%;"></span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div>
                    <!-- LEADERBOARD -->
                    <div class="panel">
                        <div class="leaderboard-head">
                            <div class="panel-title">Leaderboard</div>
                            <select class="leaderboard-select">
                                <option>This Month</option>
                                <option>This Week</option>
                                <option>All Time</option>
                            </select>
                        </div>

                        <div class="leaderboard-cols">
                            <span>Rank</span>
                            <span style="flex: 1; margin-left: 38px;">Learner</span>
                        </div>

                        <div class="leaderboard-row">
                            <div class="rank-badge gold">1</div>
                            <div class="leaderboard-learner">Learner 1</div>
                        </div>
                        <div class="leaderboard-row">
                            <div class="rank-badge silver">2</div>
                            <div class="leaderboard-learner">Learner 2</div>
                        </div>
                        <div class="leaderboard-row">
                            <div class="rank-badge bronze">3</div>
                            <div class="leaderboard-learner">Learner 3</div>
                        </div>
                        <div class="leaderboard-row">
                            <div class="rank">4</div>
                            <div class="leaderboard-learner">Learner 4</div>
                        </div>
                        <div class="leaderboard-row">
                            <div class="rank">5</div>
                            <div class="leaderboard-learner">Learner 5</div>
                        </div>
                        <div class="leaderboard-row">
                            <div class="rank">6</div>
                            <div class="leaderboard-learner">Learner 6</div>
                        </div>
                        <div class="leaderboard-row">
                            <div class="rank">7</div>
                            <div class="leaderboard-learner">Learner 7</div>
                        </div>
                        <div class="leaderboard-row me">
                            <div class="rank">8</div>
                            <div class="leaderboard-learner">
                                <span class="me-avatar">KD</span>
                                Kristine D.
                            </div>
                        </div>

                        <a href="#" class="leaderboard-btn"><i class="fas fa-trophy"></i> View Full Leaderboard <i class="fas fa-arrow-right"></i></a>
                    </div>

                    <!-- YOUR STATUS -->
                    <div class="panel status-card">
                        <div class="status-card-laurel"><i class="fas fa-medal"></i></div>
                        <div class="status-card-label">Your Status</div>
                        <div class="status-card-value">Top 8</div>
                        <div class="status-card-text">You are currently in the <strong>top 8%</strong> of all CPALE aspirants.</div>
                        <div class="status-card-pill"><i class="fas fa-arrow-trend-up"></i> Up 3 spots from last month</div>
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

        // Load sidebar state
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }

        // Badge filter tabs
        document.querySelectorAll('.badge-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.badge-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
            });
        });
    </script>
</body>
</html>
