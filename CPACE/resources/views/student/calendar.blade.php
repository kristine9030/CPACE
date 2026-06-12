<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - CPACE CPA Reviewer</title>

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
            color: #7B1D1D;
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
            flex: 0 1 320px;
            position: relative;
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

        /* CALENDAR LAYOUT */
        .calendar-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 25px;
            align-items: start;
        }

        /* CALENDAR CARD */
        .calendar-card {
            background: white;
            border-radius: 14px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }

        .calendar-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
            flex-wrap: wrap;
        }

        .calendar-toolbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .calendar-month {
            font-size: 24px;
            font-weight: 700;
            color: #222;
        }

        .nav-btn {
            width: 36px;
            height: 36px;
            border: 1px solid #e5e7eb;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            color: #555;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .nav-btn:hover {
            background: #f5f5f5;
            border-color: #c0392b;
            color: #c0392b;
        }

        .today-btn {
            padding: 8px 18px;
            border: 1px solid #e5e7eb;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            color: #555;
            font-size: 13px;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
        }

        .today-btn:hover {
            background: #f5f5f5;
        }

        .view-toggle {
            display: flex;
            gap: 8px;
        }

        .view-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            border: 1px solid #e5e7eb;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            color: #777;
            font-size: 13px;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
        }

        .view-btn.active {
            border-color: #c0392b;
            color: #c0392b;
            background: #fff5f5;
        }

        .view-btn:hover {
            border-color: #c0392b;
        }

        /* CALENDAR GRID */
        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            margin-bottom: 8px;
        }

        .weekday {
            text-align: left;
            padding: 8px 10px;
            font-size: 13px;
            font-weight: 500;
            color: #999;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            border-top: 1px solid #eee;
            border-left: 1px solid #eee;
        }

        .calendar-day {
            min-height: 120px;
            border-right: 1px solid #eee;
            border-bottom: 1px solid #eee;
            padding: 8px 8px;
            position: relative;
        }

        .calendar-day.muted .day-number {
            color: #ccc;
        }

        .day-number {
            font-size: 14px;
            font-weight: 500;
            color: #555;
            margin-bottom: 6px;
            display: block;
        }

        .calendar-day.muted .day-number {
            text-align: right;
        }

        .event {
            border-radius: 6px;
            padding: 6px 8px;
            margin-bottom: 6px;
            font-size: 12px;
            cursor: pointer;
            position: relative;
            padding-left: 18px;
            transition: all 0.2s;
        }

        .event:hover {
            transform: translateX(2px);
        }

        .event::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 11px;
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }

        .event-title {
            font-weight: 600;
            color: #333;
            display: block;
            line-height: 1.3;
        }

        .event-time {
            font-size: 11px;
            color: #888;
        }

        /* Event color variants */
        .event.tax { background: #fdf3e3; }
        .event.tax::before { background: #F39C12; }

        .event.far { background: #eaf6ee; }
        .event.far::before { background: #27AE60; }

        .event.aud { background: #fdeaea; }
        .event.aud::before { background: #c0392b; }

        /* RIGHT SIDEBAR */
        .calendar-sidebar {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .side-card {
            background: white;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }

        .side-card-title {
            font-size: 16px;
            font-weight: 700;
            color: #222;
            margin-bottom: 18px;
        }

        .side-card-title span {
            font-size: 13px;
            font-weight: 500;
            color: #999;
        }

        /* Today's Reviews */
        .today-head {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
        }

        .today-date-badge {
            width: 52px;
            height: 52px;
            background: #fdeaea;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .today-date-badge .m {
            font-size: 11px;
            font-weight: 600;
            color: #c0392b;
            text-transform: uppercase;
        }

        .today-date-badge .d {
            font-size: 20px;
            font-weight: 700;
            color: #c0392b;
            line-height: 1;
        }

        .today-day-name {
            font-size: 15px;
            font-weight: 600;
            color: #222;
        }

        .today-day-sub {
            font-size: 12px;
            color: #999;
        }

        .review-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f2f2f2;
        }

        .review-item:last-of-type {
            border-bottom: none;
        }

        .review-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .review-info {
            flex: 1;
        }

        .review-subject {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .review-difficulty {
            font-size: 12px;
            color: #999;
        }

        .review-time {
            font-size: 13px;
            color: #666;
            font-weight: 500;
        }

        .card-btn {
            display: block;
            width: 100%;
            margin-top: 16px;
            padding: 12px;
            background: #fdeaea;
            color: #c0392b;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
        }

        .card-btn:hover {
            background: #fbdcdc;
        }

        /* Upcoming */
        .upcoming-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid #f2f2f2;
        }

        .upcoming-item:last-of-type {
            border-bottom: none;
        }

        .upcoming-date {
            font-size: 12px;
            color: #999;
            width: 42px;
            flex-shrink: 0;
        }

        .upcoming-info {
            flex: 1;
        }

        .upcoming-subject {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .upcoming-difficulty {
            font-size: 12px;
            color: #999;
        }

        .upcoming-time {
            font-size: 13px;
            color: #666;
            font-weight: 500;
        }

        /* Focus card */
        .focus-card {
            position: relative;
            overflow: hidden;
        }

        .focus-card-title {
            font-size: 16px;
            font-weight: 700;
            color: #222;
            margin-bottom: 10px;
        }

        .focus-card-text {
            font-size: 12px;
            color: #888;
            line-height: 1.6;
            max-width: 65%;
        }

        .focus-card-icon {
            position: absolute;
            right: 18px;
            bottom: 14px;
            font-size: 46px;
            color: #c0392b;
            opacity: 0.85;
        }

        /* RESPONSIVE */
        @media (max-width: 1100px) {
            .calendar-layout {
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

            .calendar-day {
                min-height: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        @include('partials.sidebar', ['active' => 'calendar'])

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- HEADER -->
            <div class="header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <div class="header-title">Spaced Repetition Calendar</div>
                        <div class="header-subtitle">Plan your reviews and stay consistent.</div>
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

            <!-- CALENDAR LAYOUT -->
            <div class="calendar-layout">
                <!-- CALENDAR CARD -->
                <div class="calendar-card">
                    <div class="calendar-toolbar">
                        <div class="calendar-toolbar-left">
                            <div class="calendar-month">May 2024</div>
                            <button class="nav-btn"><i class="fas fa-chevron-left"></i></button>
                            <button class="nav-btn"><i class="fas fa-chevron-right"></i></button>
                            <button class="today-btn">Today</button>
                        </div>
                        <div class="view-toggle">
                            <button class="view-btn active"><i class="fas fa-calendar"></i> Calendar View</button>
                            <button class="view-btn"><i class="fas fa-list"></i> List View</button>
                        </div>
                    </div>

                    <!-- WEEKDAYS -->
                    <div class="calendar-weekdays">
                        <div class="weekday">Sun</div>
                        <div class="weekday">Mon</div>
                        <div class="weekday">Tue</div>
                        <div class="weekday">Wed</div>
                        <div class="weekday">Thu</div>
                        <div class="weekday">Fri</div>
                        <div class="weekday">Sat</div>
                    </div>

                    <!-- GRID -->
                    <div class="calendar-grid">
                        <!-- Week 1 -->
                        <div class="calendar-day muted"><span class="day-number">28</span></div>
                        <div class="calendar-day muted"><span class="day-number">29</span></div>
                        <div class="calendar-day muted"><span class="day-number">30</span></div>
                        <div class="calendar-day"><span class="day-number">1</span></div>
                        <div class="calendar-day"><span class="day-number">2</span></div>
                        <div class="calendar-day"><span class="day-number">3</span></div>
                        <div class="calendar-day"><span class="day-number">4</span></div>

                        <!-- Week 2 -->
                        <div class="calendar-day">
                            <span class="day-number">5</span>
                            <div class="event tax">
                                <span class="event-title">Taxation</span>
                                <span class="event-time">10:00 AM</span>
                            </div>
                        </div>
                        <div class="calendar-day"><span class="day-number">6</span></div>
                        <div class="calendar-day">
                            <span class="day-number">7</span>
                            <div class="event aud">
                                <span class="event-title">Audit</span>
                                <span class="event-time">9:00 AM</span>
                            </div>
                            <div class="event far">
                                <span class="event-title">FAR</span>
                                <span class="event-time">4:00 PM</span>
                            </div>
                        </div>
                        <div class="calendar-day"><span class="day-number">8</span></div>
                        <div class="calendar-day">
                            <span class="day-number">9</span>
                            <div class="event tax">
                                <span class="event-title">Taxation</span>
                                <span class="event-time">11:00 AM</span>
                            </div>
                        </div>
                        <div class="calendar-day"><span class="day-number">10</span></div>
                        <div class="calendar-day"><span class="day-number">11</span></div>

                        <!-- Week 3 -->
                        <div class="calendar-day"><span class="day-number">12</span></div>
                        <div class="calendar-day">
                            <span class="day-number">13</span>
                            <div class="event far">
                                <span class="event-title">FAR</span>
                                <span class="event-time">3:00 PM</span>
                            </div>
                        </div>
                        <div class="calendar-day"><span class="day-number">14</span></div>
                        <div class="calendar-day">
                            <span class="day-number">15</span>
                            <div class="event aud">
                                <span class="event-title">Audit</span>
                                <span class="event-time">9:00 AM</span>
                            </div>
                        </div>
                        <div class="calendar-day">
                            <span class="day-number">16</span>
                            <div class="event tax">
                                <span class="event-title">Taxation</span>
                                <span class="event-time">1:00 PM</span>
                            </div>
                        </div>
                        <div class="calendar-day"><span class="day-number">17</span></div>
                        <div class="calendar-day"><span class="day-number">18</span></div>

                        <!-- Week 4 -->
                        <div class="calendar-day"><span class="day-number">19</span></div>
                        <div class="calendar-day"><span class="day-number">20</span></div>
                        <div class="calendar-day">
                            <span class="day-number">21</span>
                            <div class="event far">
                                <span class="event-title">FAR</span>
                                <span class="event-time">4:00 PM</span>
                            </div>
                        </div>
                        <div class="calendar-day"><span class="day-number">22</span></div>
                        <div class="calendar-day">
                            <span class="day-number">23</span>
                            <div class="event aud">
                                <span class="event-title">Audit</span>
                                <span class="event-time">10:00 AM</span>
                            </div>
                        </div>
                        <div class="calendar-day"><span class="day-number">24</span></div>
                        <div class="calendar-day"><span class="day-number">25</span></div>

                        <!-- Week 5 -->
                        <div class="calendar-day"><span class="day-number">26</span></div>
                        <div class="calendar-day">
                            <span class="day-number">27</span>
                            <div class="event tax">
                                <span class="event-title">Taxation</span>
                                <span class="event-time">2:00 PM</span>
                            </div>
                        </div>
                        <div class="calendar-day"><span class="day-number">28</span></div>
                        <div class="calendar-day">
                            <span class="day-number">29</span>
                            <div class="event far">
                                <span class="event-title">FAR</span>
                                <span class="event-time">5:00 PM</span>
                            </div>
                        </div>
                        <div class="calendar-day"><span class="day-number">30</span></div>
                        <div class="calendar-day">
                            <span class="day-number">31</span>
                            <div class="event aud">
                                <span class="event-title">Audit</span>
                                <span class="event-time">9:00 AM</span>
                            </div>
                        </div>
                        <div class="calendar-day muted"><span class="day-number">1</span></div>
                    </div>
                </div>

                <!-- RIGHT SIDEBAR -->
                <div class="calendar-sidebar">
                    <!-- TODAY'S REVIEWS -->
                    <div class="side-card">
                        <div class="side-card-title">Today's Reviews</div>
                        <div class="today-head">
                            <div class="today-date-badge">
                                <span class="m">May</span>
                                <span class="d">7</span>
                            </div>
                            <div>
                                <div class="today-day-name">Tuesday</div>
                                <div class="today-day-sub">3 topics scheduled</div>
                            </div>
                        </div>

                        <div class="review-item">
                            <div class="review-dot" style="background: #c0392b;"></div>
                            <div class="review-info">
                                <div class="review-subject">Audit</div>
                                <div class="review-difficulty">High difficulty</div>
                            </div>
                            <div class="review-time">9:00 AM</div>
                        </div>
                        <div class="review-item">
                            <div class="review-dot" style="background: #27AE60;"></div>
                            <div class="review-info">
                                <div class="review-subject">FAR</div>
                                <div class="review-difficulty">Medium difficulty</div>
                            </div>
                            <div class="review-time">4:00 PM</div>
                        </div>

                        <a href="#" class="card-btn">View All</a>
                    </div>

                    <!-- UPCOMING -->
                    <div class="side-card">
                        <div class="side-card-title">Upcoming <span>(Next 7 Days)</span></div>

                        <div class="upcoming-item">
                            <div class="upcoming-date">May 9</div>
                            <div class="upcoming-info">
                                <div class="upcoming-subject">Taxation</div>
                                <div class="upcoming-difficulty">High difficulty</div>
                            </div>
                            <div class="upcoming-time">11:00 AM</div>
                        </div>
                        <div class="upcoming-item">
                            <div class="upcoming-date">May 13</div>
                            <div class="upcoming-info">
                                <div class="upcoming-subject">FAR</div>
                                <div class="upcoming-difficulty">Medium difficulty</div>
                            </div>
                            <div class="upcoming-time">3:00 PM</div>
                        </div>
                        <div class="upcoming-item">
                            <div class="upcoming-date">May 15</div>
                            <div class="upcoming-info">
                                <div class="upcoming-subject">Audit</div>
                                <div class="upcoming-difficulty">High difficulty</div>
                            </div>
                            <div class="upcoming-time">9:00 AM</div>
                        </div>

                        <a href="#" class="card-btn">View Calendar</a>
                    </div>

                    <!-- FOCUS -->
                    <div class="side-card focus-card">
                        <div class="focus-card-title">Focus on What Matters</div>
                        <div class="focus-card-text">Topics you find difficult will appear more often. Keep reviewing to strengthen your understanding!</div>
                        <div class="focus-card-icon"><i class="fas fa-shield-alt"></i></div>
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

        // View toggle
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });
    </script>
</body>
</html>

