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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
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

        .sidebar-nav .icon {
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        .sidebar-nav i {
            margin-right: 8px;
            width: 18px;
            text-align: center;
        }

        .sidebar.collapsed .sidebar-nav i {
            margin-right: 0;
        }

        .sidebar-nav .icon::before {
            margin: 0;
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

        .user-avatar {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            font-size: 13px;
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
            margin-bottom: 40px;
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
            font-size: 32px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
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
            flex: 0 1 300px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
            background: white;
            color: #666;
            font-family: 'Poppins', sans-serif;
        }

        .search-box input::placeholder {
            color: #aaa;
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
            border-radius: 6px;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            position: relative;
            color: #8B3A3A;
        }

        .icon-btn:hover {
            background: #f0f0f0;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 20px;
            height: 20px;
            background: #d84949;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
        }

        .profile-btn {
            width: 40px;
            height: 40px;
            background: #8B3A3A;
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            position: relative;
            font-family: 'Poppins', sans-serif;
        }

        .profile-btn:hover {
            background: #6d2e2e;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            min-width: 180px;
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: none;
            z-index: 1000;
            font-family: 'Poppins', sans-serif;
        }

        .dropdown-menu.active {
            display: block;
        }

        .dropdown-menu a,
        .dropdown-menu form {
            display: block;
        }

        .dropdown-menu a {
            padding: 12px 16px;
            color: #333;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.2s;
            border-bottom: 1px solid #f0f0f0;
        }

        .dropdown-menu a i {
            margin-right: 8px;
            width: 16px;
            text-align: center;
            color: #8B3A3A;
        }

        .dropdown-menu a:hover {
            background: #f9f9f9;
            color: #8B3A3A;
        }

        .dropdown-menu a:last-child,
        .dropdown-menu button {
            border-bottom: none;
        }

        .dropdown-menu button {
            width: 100%;
            padding: 12px 16px;
            background: none;
            border: none;
            text-align: left;
            color: #d84949;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .dropdown-menu button i {
            margin-right: 8px;
            width: 16px;
            text-align: center;
        }

        .dropdown-menu button:hover {
            background: #f9f9f9;
        }

        /* WELCOME BANNER */
        .welcome-banner {
            background: white;
            border-radius: 8px;
            padding: 40px;
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 40px;
            align-items: center;
        }

        .welcome-content h2 {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            font-family: 'Poppins', sans-serif;
        }

        .welcome-content p {
            color: #999;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .exam-countdown {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 20px;
        }

        .countdown-number {
            font-size: 36px;
            font-weight: 700;
            color: #d84949;
        }

        .countdown-text {
            font-size: 13px;
            color: #666;
        }

        .welcome-image {
            width: 300px;
            height: 200px;
            background: linear-gradient(135deg, #f8f9fa, #f0f0f0);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 25px;
            font-size: 70px;
            color: #8B3A3A;
        }

        .welcome-image i {
            opacity: 0.9;
            filter: drop-shadow(0 2px 4px rgba(139, 58, 58, 0.1));
        }

        /* METRICS SECTION */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .metric-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
        }

        .metric-icon {
            font-size: 24px;
            margin-bottom: 12px;
            color: #8B3A3A;
        }

        .metric-number {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .metric-label {
            color: #999;
            font-size: 12px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-change {
            font-size: 11px;
            color: #10b981;
        }

        /* MAIN GRID LAYOUT */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 360px;
            gap: 20px;
            margin-bottom: 30px;
        }

        .content-grid-left {
            grid-column: 1 / 3;
        }

        .content-grid-right {
            grid-column: 3;
            grid-row: 1 / 3;
        }

        /* CARDS */
        .section-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .section-card h3 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
        }

        .section-card h4 {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            font-family: 'Poppins', sans-serif;
        }

        .subject-item {
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: all 0.3s;
            padding-right: 10px;
        }

        .subject-item:last-child {
            border-bottom: none;
        }

        .subject-item:hover {
            background: #f9f9f9;
            border-radius: 4px;
            padding-left: 10px;
        }

        .subject-name {
            font-size: 13px;
            color: #333;
            flex: 1;
        }

        .subject-icon {
            font-size: 16px;
            margin-right: 10px;
            width: 18px;
            text-align: center;
            color: #8B3A3A;
        }

        .weakness-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .weakness-item:last-child {
            border-bottom: none;
        }

        .weakness-badge {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 12px;
            color: white;
            flex-shrink: 0;
        }

        .weakness-badge.badge-1 {
            background: #d84949;
        }

        .weakness-badge.badge-2 {
            background: #f59e0b;
        }

        .weakness-badge.badge-3 {
            background: #8B3A3A;
        }

        .weakness-content {
            flex: 1;
        }

        .weakness-title {
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
        }

        .weakness-category {
            font-size: 11px;
            color: #999;
        }

        .focus-area {
            padding: 12px;
            margin-bottom: 10px;
            background: #f9f9f9;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .focus-area:hover {
            background: #f0f0f0;
        }

        .focus-area-title {
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .focus-area-desc {
            font-size: 11px;
            color: #999;
        }

        /* PROGRESS CIRCLE */
        .progress-section {
            text-align: center;
        }

        .progress-circle {
            width: 150px;
            height: 150px;
            margin: 0 auto 25px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .progress-circle svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .progress-text {
            position: absolute;
            text-align: center;
        }

        .progress-number {
            font-size: 32px;
            font-weight: 700;
            color: #333;
        }

        .progress-label {
            font-size: 11px;
            color: #999;
        }

        .progress-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            font-size: 12px;
            margin-top: 20px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .legend-dot.completed {
            background: #8B3A3A;
        }

        .legend-dot.remaining {
            background: #f0c9c9;
        }

        /* ACTIVITY SECTION */
        .activity-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
            background: #f0f0f0;
            color: #333;
        }

        .activity-icon.quiz {
            background: #e8f5e9;
            color: #10b981;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
        }

        .activity-meta {
            font-size: 11px;
            color: #999;
        }

        .activity-score {
            font-size: 12px;
            font-weight: 600;
            color: #333;
            text-align: right;
        }

        .activity-time {
            font-size: 11px;
            color: #999;
            text-align: right;
        }

        .activity-link {
            font-size: 11px;
            color: #8B3A3A;
            cursor: pointer;
            text-decoration: none;
        }

        .activity-link i {
            margin-right: 4px;
        }

        .activity-link:hover {
            text-decoration: underline;
        }

        /* STREAK SECTION */
        .streak-section {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .streak-content {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .streak-info {
            flex: 1;
        }

        .streak-number {
            font-size: 48px;
            font-weight: 700;
            color: #8B3A3A;
            margin-bottom: 10px;
        }

        .streak-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .streak-text {
            font-size: 13px;
            color: #999;
        }

        .streak-image {
            font-size: 120px;
            color: #8B3A3A;
        }

        /* QUICK ACTIONS */
        .quick-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }

        .action-btn {
            padding: 12px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
        }

        .action-btn.primary {
            background: #8B3A3A;
            color: white;
        }

        .action-btn.primary:hover {
            background: #6d2e2e;
        }

        .action-btn.secondary {
            background: white;
            color: #8B3A3A;
            border: 1px solid #ddd;
        }

        .action-btn.secondary:hover {
            background: #f9f9f9;
        }

        /* RESPONSIVE */
        @media (max-width: 1400px) {
            .metrics-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .content-grid-left {
                grid-column: 1;
            }

            .content-grid-right {
                grid-column: 1;
                grid-row: auto;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 15px 0;
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .metrics-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .welcome-banner {
                grid-template-columns: 1fr;
            }

            .welcome-image {
                width: 100%;
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
                <li><a href="{{ route('dashboard') }}" class="active"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
                <li><a href="{{ route('subjects') }}"><i class="fas fa-book"></i><span>Subjects</span></a></li>
                <li><a href="{{ route('adaptive-quizzes') }}"><i class="fas fa-brain"></i><span>Adaptive Quizzes</span></a></li>
                <li><a href="#"><i class="fas fa-file-alt"></i><span>Mock Exams</span></a></li>
                <li><a href="#"><i class="fas fa-chart-bar"></i><span>Performance</span></a></li>
                <li><a href="#"><i class="fas fa-book-open"></i><span>Review Notes</span></a></li>
                <li><a href="#"><i class="fas fa-layer-group"></i><span>Flashcards</span></a></li>
                <li><a href="#"><i class="fas fa-calendar-alt"></i><span>Calendar</span></a></li>
                <li><a href="#"><i class="fas fa-chart-line"></i><span>Progress</span></a></li>
                <li><a href="#"><i class="fas fa-trophy"></i><span>Achievements</span></a></li>
                <li><a href="#"><i class="fas fa-cog"></i><span>Settings</span></a></li>
            </nav>

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
                        <div class="header-title">Dashboard</div>
                        <div class="header-subtitle">Welcome back, {{ Auth::user()->name }}! Let's keep up the momentum.</div>
                    </div>
                </div>
                <div class="header-right">
                    <div class="search-box">
                        <input type="text" placeholder="Search topics, questions, subjects...">
                    </div>
                    <div class="header-icons">
                        <button class="icon-btn">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                        <div style="position: relative;">
                            <button class="profile-btn" id="profileBtn">KD</button>
                            <div class="dropdown-menu" id="profileDropdown">
                                <a href="#"><i class="fas fa-user"></i> Profile Settings</a>
                                <a href="#"><i class="fas fa-chart-line"></i> My Progress</a>
                                <a href="#"><i class="fas fa-question-circle"></i> Help & Support</a>
                                <form method="POST" action="{{ route('logout') }}" style="margin: 0; padding: 0;">
                                    @csrf
                                    <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WELCOME BANNER -->
            <div class="welcome-banner">
                <div class="welcome-content">
                    <h2>Good morning, Kristine! <i class="fas fa-wave-hand"></i></h2>
                    <p>Every day you study brings you closer to your goal.</p>
                    <div class="exam-countdown">
                        <div>
                            <div class="countdown-number">78</div>
                            <div class="countdown-text">days until<br>board exam</div>
                        </div>
                    </div>
                </div>
                <div class="welcome-image">
                    <i class="fas fa-book"></i>
                    <i class="fas fa-laptop"></i>
                    <i class="fas fa-mug-hot"></i>
                </div>
            </div>

            <!-- METRICS -->
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-icon"><i class="fas fa-chart-area"></i></div>
                    <div class="metric-label">Board Readiness Score</div>
                    <div class="metric-number">78%</div>
                    <div class="metric-change"><i class="fas fa-arrow-up"></i> 5% from last week</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="metric-label">Questions Answered</div>
                    <div class="metric-number">1,247</div>
                    <div class="metric-change"><i class="fas fa-arrow-up"></i> 128 this week</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon"><i class="fas fa-hourglass-end"></i></div>
                    <div class="metric-label">Study Time</div>
                    <div class="metric-number">42h</div>
                    <div class="metric-change"><i class="fas fa-arrow-up"></i> 8h this week</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon"><i class="fas fa-fire"></i></div>
                    <div class="metric-label">Day Streak</div>
                    <div class="metric-number">14</div>
                    <div class="metric-change">Keep it up!</div>
                </div>
            </div>

            <!-- MAIN CONTENT GRID -->
            <div class="content-grid">
                <!-- LEFT COLUMN -->
                <div class="content-grid-left">
                    <!-- SUBJECT MASTERY -->
                    <div class="section-card">
                        <h3>Subject Mastery</h3>
                        <div class="subject-item">
                            <i class="subject-icon fas fa-chart-pie"></i>
                            <span class="subject-name">Financial Accounting & Reporting</span>
                            <i class="subject-icon fas fa-arrow-right"></i>
                        </div>
                        <div class="subject-item">
                            <i class="subject-icon fas fa-search"></i>
                            <span class="subject-name">Auditing</span>
                            <i class="subject-icon fas fa-arrow-right"></i>
                        </div>
                        <div class="subject-item">
                            <i class="subject-icon fas fa-list"></i>
                            <span class="subject-name">Taxation</span>
                            <i class="subject-icon fas fa-arrow-right"></i>
                        </div>
                        <div class="subject-item">
                            <i class="subject-icon fas fa-cogs"></i>
                            <span class="subject-name">Management Services</span>
                            <i class="subject-icon fas fa-arrow-right"></i>
                        </div>
                        <div class="subject-item">
                            <i class="subject-icon fas fa-balance-scale"></i>
                            <span class="subject-name">Regulatory Framework</span>
                            <i class="subject-icon fas fa-arrow-right"></i>
                        </div>
                    </div>

                    <!-- TOP WEAKNESSES -->
                    <div class="section-card">
                        <h3>Top Weaknesses</h3>
                        <div class="weakness-item">
                            <div class="weakness-badge badge-1">1</div>
                            <div class="weakness-content">
                                <div class="weakness-title">Estate Tax Computation</div>
                                <div class="weakness-category">Taxation</div>
                            </div>
                        </div>
                        <div class="weakness-item">
                            <div class="weakness-badge badge-2">2</div>
                            <div class="weakness-content">
                                <div class="weakness-title">Revenue Recognition</div>
                                <div class="weakness-category">FAR - PFRS 15</div>
                            </div>
                        </div>
                        <div class="weakness-item">
                            <div class="weakness-badge badge-3">3</div>
                            <div class="weakness-content">
                                <div class="weakness-title">Audit Sampling</div>
                                <div class="weakness-category">Auditing - PSA 530</div>
                            </div>
                        </div>
                    </div>

                    <!-- FOCUS AREAS -->
                    <div class="section-card">
                        <h3>Focus Areas</h3>
                        <div class="focus-area">
                            <div class="focus-area-title">Estate Tax Computation</div>
                            <div class="focus-area-desc">Practice 5 more questions</div>
                        </div>
                        <div class="focus-area">
                            <div class="focus-area-title">Revenue Recognition</div>
                            <div class="focus-area-desc">Review concepts & quiz</div>
                        </div>
                        <div class="focus-area">
                            <div class="focus-area-title">Audit Sampling</div>
                            <div class="focus-area-desc">Complete mock exam</div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="content-grid-right">
                    <!-- OVERALL PROGRESS -->
                    <div class="section-card">
                        <h3>Overall Progress</h3>
                        <div class="progress-section">
                            <div class="progress-circle">
                                <svg viewBox="0 0 100 100">
                                    <circle cx="50" cy="50" r="45" fill="none" stroke="#f0c9c9" stroke-width="8"/>
                                    <circle cx="50" cy="50" r="45" fill="none" stroke="#8B3A3A" stroke-width="8"
                                            stroke-dasharray="113.1 282.7" stroke-linecap="round"/>
                                </svg>
                                <div class="progress-text">
                                    <div class="progress-number">78%</div>
                                    <div class="progress-label">Complete</div>
                                </div>
                            </div>
                            <div class="progress-legend">
                                <div class="legend-item">
                                    <span class="legend-dot completed"></span>
                                    <span>Completed</span>
                                </div>
                                <div class="legend-item">
                                    <span class="legend-dot remaining"></span>
                                    <span>Remaining</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RECENT ACTIVITY -->
                    <div class="section-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h3 style="margin-bottom: 0;">Recent Activity</h3>
                            <a class="activity-link" href="#"><i class="fas fa-eye"></i> View All</a>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon quiz"><i class="fas fa-clipboard-list"></i></div>
                            <div class="activity-content">
                                <div class="activity-title">Adaptive Quiz - FAR</div>
                                <div class="activity-meta">20 Questions • Score: 85%</div>
                            </div>
                            <div>
                                <div class="activity-time">2h ago</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STUDY STREAK SECTION -->
            <div class="streak-section">
                <div class="streak-content">
                    <div class="streak-info">
                        <div class="streak-number">14</div>
                        <div class="streak-label">days</div>
                        <div class="streak-text">Keep the momentum going!</div>
                    </div>
                    <div class="streak-image"><i class="fas fa-calendar"></i></div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth animations
            const elements = document.querySelectorAll('.metric-card, .section-card, .welcome-banner');
            elements.forEach((el, index) => {
                el.style.animation = `slideUp 0.5s ease ${index * 0.1}s both`;
            });

            // Profile dropdown
            const profileBtn = document.getElementById('profileBtn');
            const profileDropdown = document.getElementById('profileDropdown');

            if (profileBtn && profileDropdown) {
                profileBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    profileDropdown.classList.toggle('active');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function() {
                    profileDropdown.classList.remove('active');
                });

                // Prevent closing when clicking inside dropdown
                profileDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });

        // Add CSS animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);

        // Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
        }

        // Load sidebar state
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }
    </script>
</body>
</html>
