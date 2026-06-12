<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adaptive Quizzes - CPACE CPA Reviewer</title>

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
        }

        .challenge-box p {
            font-size: 12px;
            color: white;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .challenge-box a {
            display: block;
            background: white;
            color: #7B1D1D;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            margin-bottom: 8px;
        }

        .challenge-box a:hover {
            background: #f9f9f9;
            transform: translateY(-2px);
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
            color: #7B1D1D;
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
            background: #c0392b;
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
            background: #7B1D1D;
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

        /* PAGE SECTION */
        .page-section {
            margin-bottom: 50px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
            font-family: 'Poppins', sans-serif;
        }

        .section-subtitle {
            font-size: 13px;
            color: #999;
            margin-bottom: 25px;
        }

        /* CHOOSE MODE GRID */
        .choose-mode-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 40px;
        }

        .mode-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            border: 2px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            font-family: 'Poppins', sans-serif;
        }

        .mode-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .mode-card.active {
            border-color: #c0392b;
            background: #fff9f9;
        }

        .mode-card.active::after {
            content: '';
            position: absolute;
            top: 15px;
            right: 15px;
            width: 24px;
            height: 24px;
            background: #c0392b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .mode-card.active::before {
            content: 'âœ“';
            position: absolute;
            top: 15px;
            right: 15px;
            width: 24px;
            height: 24px;
            background: #c0392b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .mode-icon {
            font-size: 32px;
            margin-bottom: 15px;
            color: #c0392b;
        }

        .mode-title {
            font-size: 16px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }

        .mode-description {
            font-size: 12px;
            color: #999;
            line-height: 1.5;
        }

        /* CONTENT WITH SIDEBAR */
        .content-with-sidebar {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 30px;
            margin-bottom: 40px;
        }

        .stats-sidebar {
            background: white;
            border-radius: 12px;
            padding: 25px;
            height: fit-content;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .stats-sidebar-title {
            font-size: 16px;
            font-weight: 700;
            color: #333;
            margin-bottom: 25px;
            font-family: 'Poppins', sans-serif;
        }

        .mastery-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: conic-gradient(#17A2B8 0deg 151.2deg, #F39C12 151.2deg 259.2deg, #c0392b 259.2deg 360deg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .mastery-circle-inner {
            width: 130px;
            height: 130px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .mastery-percentage {
            font-size: 32px;
            font-weight: 700;
            color: #333;
        }

        .mastery-label {
            font-size: 11px;
            color: #999;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            font-size: 12px;
        }

        .stat-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .stat-label {
            flex: 1;
            color: #999;
        }

        .stat-value {
            color: #333;
            font-weight: 600;
        }

        .stats-grid-sidebar {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #f0f0f0;
        }

        .stat-box {
            text-align: center;
        }

        .stat-box-value {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-box-label {
            font-size: 10px;
            color: #999;
            text-transform: uppercase;
        }

        .improvement {
            color: #27AE60;
        }

        /* SUBJECT CARDS GRID */
        .subject-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .subject-select-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            border: 2px solid #f0f0f0;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .subject-select-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border-color: #c0392b;
        }

        .subject-select-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }

        .subject-select-name {
            font-size: 16px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .subject-select-full {
            font-size: 12px;
            color: #999;
            margin-bottom: 12px;
        }

        .questions-count {
            font-size: 13px;
            color: #666;
            font-weight: 600;
        }

        /* CONTINUE SECTION */
        .continue-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .continue-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #f0f0f0;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .continue-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .continue-header {
            display: flex;
            gap: 12px;
            margin-bottom: 15px;
        }

        .continue-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .continue-title {
            font-size: 14px;
            font-weight: 700;
            color: #333;
        }

        .continue-type {
            font-size: 11px;
            color: #999;
        }

        .continue-footer {
            text-align: right;
        }

        .continue-btn {
            color: #4A90E2;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .continue-timestamp {
            font-size: 11px;
            color: #999;
            margin-bottom: 10px;
            display: block;
        }

        /* HOW IT WORKS SECTION */
        .how-it-works {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            text-align: center;
        }

        .how-step {
            font-family: 'Poppins', sans-serif;
        }

        .step-number {
            font-size: 24px;
            font-weight: 700;
            color: #7B1D1D;
            margin-bottom: 10px;
        }

        .step-title {
            font-size: 14px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .step-description {
            font-size: 12px;
            color: #999;
            line-height: 1.5;
        }

        /* RESPONSIVE */
        @media (max-width: 1200px) {
            .choose-mode-grid {
                grid-template-columns: 1fr 1fr;
            }

            .subject-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .continue-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .how-it-works {
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

            .choose-mode-grid,
            .subject-grid,
            .continue-grid {
                grid-template-columns: 1fr;
            }

            .how-it-works {
                grid-template-columns: 1fr;
            }

            .content-with-sidebar {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        @include('partials.sidebar', ['active' => 'quizzes'])

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- HEADER -->
            <div class="header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <div class="header-title">Adaptive Quizzes</div>
                        <div class="header-subtitle">Smart practice that adapts to you.</div>
                    </div>
                </div>
                <div class="header-right">
                    <div class="search-box">
                        <input type="text" placeholder="Search topics, questions...">
                    </div>
                    <div class="header-icons">
                        <button class="icon-btn">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">1</span>
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

            <!-- CHOOSE YOUR MODE -->
            <div class="page-section">
                <div class="section-title">Choose Your Mode</div>
                <div class="section-subtitle">Select how you want to practice today.</div>
                <div class="choose-mode-grid">
                    <div class="mode-card active" onclick="selectMode(this)">
                        <div class="mode-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="mode-title">Adaptive Mode</div>
                        <div class="mode-description">Questions adjust to your performance in real-time.</div>
                    </div>
                    <div class="mode-card" onclick="selectMode(this)">
                        <div class="mode-icon"><i class="fas fa-book-open"></i></div>
                        <div class="mode-title">Topic Mode</div>
                        <div class="mode-description">Focus on specific topics or competencies.</div>
                    </div>
                    <div class="mode-card" onclick="selectMode(this)">
                        <div class="mode-icon"><i class="fas fa-clock"></i></div>
                        <div class="mode-title">Timed Mode</div>
                        <div class="mode-description">Test your speed and accuracy.</div>
                    </div>
                    <div class="mode-card" onclick="selectMode(this)">
                        <div class="mode-icon"><i class="fas fa-trophy"></i></div>
                        <div class="mode-title">Challenge Mode</div>
                        <div class="mode-description">Take on harder questions for a bigger challenge.</div>
                    </div>
                </div>
            </div>

            <!-- STATS SIDEBAR WITH SUBJECT SELECTION -->
            <div class="content-with-sidebar">
                <div>
                    <div class="page-section">
                        <div class="section-title">Select Subject Area</div>
                        <div class="section-subtitle">Choose a CPALE subject to begin your quiz.</div>
                        @if(session('error'))
                            <div style="background:#fee2e2;color:#b91c1c;padding:12px 18px;border-radius:10px;margin-bottom:18px;font-size:13px;font-weight:600;">
                                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            </div>
                        @endif
                        @php
                            $subjectIcons = [
                                'FAR' => ['fa-chart-line', '#4A90E2'], 'AFAR' => ['fa-coins', '#17A2B8'],
                                'MS' => ['fa-gears', '#F39C12'], 'TAX' => ['fa-file-invoice-dollar', '#27AE60'],
                                'AUD' => ['fa-magnifying-glass', '#c0392b'], 'RFBT' => ['fa-scale-balanced', '#9B59B6'],
                            ];
                        @endphp
                        <div class="subject-grid">
                            @foreach($subjects as $subject)
                                @php [$icon, $color] = $subjectIcons[$subject->code] ?? ['fa-book', '#7B1D1D']; @endphp
                                <form method="POST" action="{{ route('quiz.start') }}">
                                    @csrf
                                    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                                    <button type="submit" class="subject-select-card" style="display:block;width:100%;text-align:center;cursor:pointer;{{ $subject->question_count === 0 ? 'opacity:.55;cursor:not-allowed;' : '' }}" {{ $subject->question_count === 0 ? 'disabled' : '' }}>
                                        <div class="subject-select-icon" style="color: {{ $color }};"><i class="fas {{ $icon }}"></i></div>
                                        <div class="subject-select-name">{{ $subject->code }}</div>
                                        <div class="subject-select-full">{{ $subject->name }}</div>
                                        <div class="questions-count">{{ number_format($subject->question_count) }} Questions</div>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- RIGHT STATS SIDEBAR -->
                <div class="stats-sidebar">
                    <div class="stats-sidebar-title">Your Adaptive Stats</div>

                    <div class="mastery-circle">
                        <div class="mastery-circle-inner">
                            <div class="mastery-percentage">{{ $accuracy }}%</div>
                            <div class="mastery-label">Overall Mastery</div>
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-dot" style="background: #17A2B8;"></div>
                        <span class="stat-label">Strong</span>
                        <span class="stat-value">42%</span>
                    </div>
                    <div class="stat-item">
                        <div class="stat-dot" style="background: #F39C12;"></div>
                        <span class="stat-label">Medium</span>
                        <span class="stat-value">30%</span>
                    </div>
                    <div class="stat-item">
                        <div class="stat-dot" style="background: #c0392b;"></div>
                        <span class="stat-label">Weak</span>
                        <span class="stat-value">28%</span>
                    </div>

                    <div class="stats-grid-sidebar">
                        <div class="stat-box">
                            <div class="stat-box-value">{{ number_format($totalAnswered) }}</div>
                            <div class="stat-box-label">Questions Answered</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-box-value">{{ $accuracy }}%</div>
                            <div class="stat-box-label">Accuracy</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTINUE WHERE YOU LEFT OFF -->
            <div class="page-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <div>
                        <div class="section-title">Your Recent Quizzes</div>
                        <div class="section-subtitle">Review the quizzes you have completed.</div>
                    </div>
                </div>
                <div class="continue-grid">
                    @forelse($recentSessions as $rs)
                        <div class="continue-card">
                            <div class="continue-header">
                                <div class="continue-icon" style="background: #ffe8e8; color: #c0392b;"><i class="fas fa-file-alt"></i></div>
                                <div>
                                    <div class="continue-title">{{ $rs->subject->code ?? 'Quiz' }} Quiz</div>
                                    <div class="continue-type">Score: {{ (int) round($rs->score_percent) }}% &bull; {{ $rs->correct_answers }}/{{ $rs->total_items }}</div>
                                </div>
                            </div>
                            <div class="continue-footer">
                                <span class="continue-timestamp">{{ \Illuminate\Support\Carbon::parse($rs->completed_at)->diffForHumans() }}</span>
                                <a href="{{ route('quiz.results', $rs->id) }}" class="continue-btn">View Results</a>
                            </div>
                        </div>
                    @empty
                        <div class="continue-card" style="grid-column:1/-1;text-align:center;color:#999;">
                            You haven't completed any quizzes yet. Pick a subject above to start your first one!
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- HOW ADAPTIVE QUIZZES WORK -->
            <div class="page-section">
                <div class="section-title">How Adaptive Quizzes Work</div>
                <div class="how-it-works">
                    <div class="how-step">
                        <div class="step-number">1</div>
                        <div class="step-title">Intelligent Selection</div>
                        <div class="step-description">Questions are selected based on your strengths and weaknesses.</div>
                    </div>
                    <div class="how-step">
                        <div class="step-number">2</div>
                        <div class="step-title">Real-time Adjustment</div>
                        <div class="step-description">Difficulty adapts as you answer correctly or incorrectly.</div>
                    </div>
                    <div class="how-step">
                        <div class="step-number">3</div>
                        <div class="step-title">Focused Learning</div>
                        <div class="step-description">More practice on weak areas, less on what you already know.</div>
                    </div>
                    <div class="how-step">
                        <div class="step-number">4</div>
                        <div class="step-title">Track Improvement</div>
                        <div class="step-description">See your progress and mastery grow over time.</div>
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

        // Select Mode
        function selectMode(element) {
            document.querySelectorAll('.mode-card').forEach(card => {
                card.classList.remove('active');
            });
            element.classList.add('active');
        }
    </script>
</body>
</html>


