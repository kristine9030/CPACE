<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects - CPACE CPA Reviewer</title>

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

        .sidebar-challenge {
            margin-bottom: 60px;
            padding: 0 20px;
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
            color: #8B3A3A;
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

        /* PAGE HEADER */
        .page-header {
            margin-bottom: 40px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
            font-family: 'Poppins', sans-serif;
        }

        .page-subtitle {
            font-size: 14px;
            color: #999;
        }

        /* SUBJECT CARDS GRID */
        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 40px;
        }

        .subject-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .subject-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .subject-header {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 25px;
        }

        .subject-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
            flex-shrink: 0;
        }

        .subject-icon.far {
            background: linear-gradient(135deg, #4A90E2, #357ABD);
        }

        .subject-icon.aud {
            background: linear-gradient(135deg, #E8567D, #D63860);
        }

        .subject-icon.tax {
            background: linear-gradient(135deg, #27AE60, #1F8449);
        }

        .subject-icon.ms {
            background: linear-gradient(135deg, #9B59B6, #7D3C98);
        }

        .subject-icon.rfbt {
            background: linear-gradient(135deg, #F39C12, #D68910);
        }

        .subject-icon.afar {
            background: linear-gradient(135deg, #17A2B8, #138496);
        }

        .subject-info h3 {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
            font-family: 'Poppins', sans-serif;
        }

        .subject-info p {
            font-size: 13px;
            color: #999;
        }

        .subject-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            padding: 20px 0;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
            margin-bottom: 20px;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat.weak .stat-number {
            color: #d84949;
        }

        .subject-action {
            display: block;
            width: 100%;;
            text-align: center;
            padding: 12px;
            background: none;
            border: none;
            color: #4A90E2;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .subject-card:nth-child(1) .subject-action {
            color: #4A90E2;
        }

        .subject-card:nth-child(2) .subject-action {
            color: #E8567D;
        }

        .subject-card:nth-child(3) .subject-action {
            color: #27AE60;
        }

        .subject-card:nth-child(4) .subject-action {
            color: #9B59B6;
        }

        .subject-card:nth-child(5) .subject-action {
            color: #F39C12;
        }

        .subject-card:nth-child(6) .subject-action {
            color: #17A2B8;
        }

        .subject-action:hover {
            transform: translateX(5px);
        }

        .subject-action i {
            margin-left: 8px;
        }

        /* RESPONSIVE */
        @media (max-width: 1400px) {
            .subjects-grid {
                grid-template-columns: repeat(2, 1fr);
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

            .subjects-grid {
                grid-template-columns: 1fr;
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
                <li><a href="{{ route('subjects') }}" class="active"><i class="fas fa-book"></i><span>Subjects</span></a></li>
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

            <div class="sidebar-challenge">
                <div class="challenge-box">
                    <p>Need a challenge?</p>
                    <p style="margin-bottom: 12px; font-size: 14px; font-weight: 700;">Try a Mock Exam</p>
                    <a href="#"><i class="fas fa-arrow-right"></i> Go to Mock Exams</a>
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
                        <div class="header-title">Subjects</div>
                        <div class="header-subtitle">Review by subject area and strengthen your knowledge.</div>
                    </div>
                </div>
                <div class="header-right">
                    <div class="search-box">
                        <input type="text" placeholder="Search topics, questions...">
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

            <!-- SUBJECTS GRID -->
            <div class="subjects-grid">
                <!-- FAR -->
                <div class="subject-card">
                    <div class="subject-header">
                        <div class="subject-icon far"><i class="fas fa-chart-line"></i></div>
                        <div class="subject-info">
                            <h3>FAR</h3>
                            <p>Financial Accounting and Reporting</p>
                        </div>
                    </div>
                    <div class="subject-stats">
                        <div class="stat">
                            <span class="stat-number">128</span>
                            <span class="stat-label">Topics</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">245</span>
                            <span class="stat-label">Questions</span>
                        </div>
                        <div class="stat weak">
                            <span class="stat-number">18</span>
                            <span class="stat-label">Weak Topics</span>
                        </div>
                    </div>
                    <button class="subject-action">Review Subject <i class="fas fa-arrow-right"></i></button>
                </div>

                <!-- AUD -->
                <div class="subject-card">
                    <div class="subject-header">
                        <div class="subject-icon aud"><i class="fas fa-search"></i></div>
                        <div class="subject-info">
                            <h3>AUD</h3>
                            <p>Auditing and Attestation</p>
                        </div>
                    </div>
                    <div class="subject-stats">
                        <div class="stat">
                            <span class="stat-number">98</span>
                            <span class="stat-label">Topics</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">189</span>
                            <span class="stat-label">Questions</span>
                        </div>
                        <div class="stat weak">
                            <span class="stat-number">14</span>
                            <span class="stat-label">Weak Topics</span>
                        </div>
                    </div>
                    <button class="subject-action">Review Subject <i class="fas fa-arrow-right"></i></button>
                </div>

                <!-- TAX -->
                <div class="subject-card">
                    <div class="subject-header">
                        <div class="subject-icon tax"><i class="fas fa-file-invoice-dollar"></i></div>
                        <div class="subject-info">
                            <h3>TAX</h3>
                            <p>Taxation</p>
                        </div>
                    </div>
                    <div class="subject-stats">
                        <div class="stat">
                            <span class="stat-number">87</span>
                            <span class="stat-label">Topics</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">176</span>
                            <span class="stat-label">Questions</span>
                        </div>
                        <div class="stat weak">
                            <span class="stat-number">12</span>
                            <span class="stat-label">Weak Topics</span>
                        </div>
                    </div>
                    <button class="subject-action">Review Subject <i class="fas fa-arrow-right"></i></button>
                </div>

                <!-- MS -->
                <div class="subject-card">
                    <div class="subject-header">
                        <div class="subject-icon ms"><i class="fas fa-users"></i></div>
                        <div class="subject-info">
                            <h3>MS</h3>
                            <p>Management Services</p>
                        </div>
                    </div>
                    <div class="subject-stats">
                        <div class="stat">
                            <span class="stat-number">76</span>
                            <span class="stat-label">Topics</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">142</span>
                            <span class="stat-label">Questions</span>
                        </div>
                        <div class="stat weak">
                            <span class="stat-number">10</span>
                            <span class="stat-label">Weak Topics</span>
                        </div>
                    </div>
                    <button class="subject-action">Review Subject <i class="fas fa-arrow-right"></i></button>
                </div>

                <!-- RFBT -->
                <div class="subject-card">
                    <div class="subject-header">
                        <div class="subject-icon rfbt"><i class="fas fa-balance-scale"></i></div>
                        <div class="subject-info">
                            <h3>RFBT</h3>
                            <p>Regulatory Framework for Business Transactions</p>
                        </div>
                    </div>
                    <div class="subject-stats">
                        <div class="stat">
                            <span class="stat-number">92</span>
                            <span class="stat-label">Topics</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">168</span>
                            <span class="stat-label">Questions</span>
                        </div>
                        <div class="stat weak">
                            <span class="stat-number">11</span>
                            <span class="stat-label">Weak Topics</span>
                        </div>
                    </div>
                    <button class="subject-action">Review Subject <i class="fas fa-arrow-right"></i></button>
                </div>

                <!-- AFAR -->
                <div class="subject-card">
                    <div class="subject-header">
                        <div class="subject-icon afar"><i class="fas fa-calculator"></i></div>
                        <div class="subject-info">
                            <h3>AFAR</h3>
                            <p>Advanced Financial Accounting and Reporting</p>
                        </div>
                    </div>
                    <div class="subject-stats">
                        <div class="stat">
                            <span class="stat-number">85</span>
                            <span class="stat-label">Topics</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">154</span>
                            <span class="stat-label">Questions</span>
                        </div>
                        <div class="stat weak">
                            <span class="stat-number">9</span>
                            <span class="stat-label">Weak Topics</span>
                        </div>
                    </div>
                    <button class="subject-action">Review Subject <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>
        </main>
    </div>

    <script>
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
