<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Notes - CPACE CPA Reviewer</title>

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

        .sidebar-promo {
            margin: 25px 18px 0 18px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 16px;
        }

        .sidebar.collapsed .sidebar-promo {
            display: none;
        }

        .sidebar-promo h5 {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .sidebar-promo p {
            font-size: 10.5px;
            opacity: 0.8;
            line-height: 1.4;
            margin-bottom: 12px;
        }

        .sidebar-promo button {
            width: 100%;
            background: #c0392b;
            color: white;
            border: none;
            border-radius: 7px;
            padding: 9px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }

        .sidebar-promo button:hover {
            background: #c43d3d;
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

        .user-info-sidebar .fa-chevron-down {
            font-size: 11px;
            opacity: 0.7;
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
            margin-bottom: 28px;
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
            font-size: 30px;
            font-weight: 600;
            color: #2b2b2b;
            margin-bottom: 4px;
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
            flex: 0 1 340px;
        }

        .search-box i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 14px;
        }

        .search-box input {
            width: 100%;
            padding: 11px 15px 11px 42px;
            border: 1px solid #e6e6e6;
            border-radius: 25px;
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
            background: transparent;
            border-radius: 6px;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            position: relative;
            color: #666;
        }

        .icon-btn:hover {
            background: #f0f0f0;
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
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
            position: relative;
            font-family: 'Poppins', sans-serif;
        }

        .profile-btn:hover {
            background: #6a1818;
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

        .dropdown-menu a {
            display: block;
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
            color: #7B1D1D;
        }

        .dropdown-menu a:hover {
            background: #f9f9f9;
            color: #7B1D1D;
        }

        .dropdown-menu button {
            width: 100%;
            padding: 12px 16px;
            background: none;
            border: none;
            text-align: left;
            color: #c0392b;
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

        /* STAT CARDS */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 22px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 22px 24px;
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .stat-icon {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-icon.red { background: #fdeaea; color: #c0392b; }
        .stat-icon.green { background: #e8f7ee; color: #21a366; }
        .stat-icon.purple { background: #f0eafb; color: #8e5bd0; }
        .stat-icon.amber { background: #fef3e2; color: #e8910b; }

        .stat-info .label {
            font-size: 12.5px;
            color: #999;
            margin-bottom: 4px;
        }

        .stat-info .value {
            font-size: 22px;
            font-weight: 700;
            color: #2b2b2b;
            line-height: 1.1;
        }

        .stat-info .sub {
            font-size: 11px;
            margin-top: 3px;
        }

        .stat-info .sub.up { color: #21a366; }
        .stat-info .sub.muted { color: #aaa; }

        /* MAIN GRID */
        .notes-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 25px;
            align-items: start;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
        }

        /* NOTES TABLE CARD */
        .notes-card-head {
            margin-bottom: 20px;
        }

        .notes-card-title {
            font-size: 18px;
            font-weight: 600;
            color: #2b2b2b;
            margin-bottom: 18px;
        }

        .notes-toolbar {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .notes-search {
            position: relative;
            flex: 1;
            min-width: 180px;
        }

        .notes-search i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #bbb;
            font-size: 13px;
        }

        .notes-search input {
            width: 100%;
            padding: 10px 14px 10px 38px;
            border: 1px solid #e6e6e6;
            border-radius: 8px;
            font-size: 13px;
            color: #666;
            font-family: 'Poppins', sans-serif;
        }

        .notes-search input::placeholder { color: #b3b3b3; }

        .notes-select {
            border: 1px solid #e6e6e6;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #555;
            font-family: 'Poppins', sans-serif;
            background: white;
            cursor: pointer;
            min-width: 130px;
        }

        .new-note-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: #7B1D1D;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            white-space: nowrap;
            transition: all 0.3s;
        }

        .new-note-btn:hover { background: #6a1818; }

        /* TABLE */
        .notes-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .notes-table th {
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #999;
            padding: 14px 10px;
            border-bottom: 1px solid #f0f0f0;
        }

        .notes-table th .fa-chevron-down {
            font-size: 10px;
            margin-left: 4px;
        }

        .notes-table td {
            padding: 14px 10px;
            border-bottom: 1px solid #f4f4f4;
            font-size: 13px;
            color: #555;
            vertical-align: middle;
        }

        .notes-table tr:last-child td { border-bottom: none; }

        .note-title-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .note-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .note-icon.red { background: #fdeaea; color: #c0392b; }
        .note-icon.green { background: #e8f7ee; color: #21a366; }
        .note-icon.blue { background: #e9f1fd; color: #3b7ddd; }
        .note-icon.purple { background: #f0eafb; color: #8e5bd0; }
        .note-icon.amber { background: #fef3e2; color: #e8910b; }

        .note-title-text {
            font-size: 13px;
            font-weight: 600;
            color: #2b2b2b;
        }

        .subject-tag {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .subject-tag.aud { background: #fdeaea; color: #c0392b; }
        .subject-tag.tax { background: #e8f7ee; color: #21a366; }
        .subject-tag.far { background: #e9f1fd; color: #3b7ddd; }
        .subject-tag.rfbt { background: #f0eafb; color: #8e5bd0; }
        .subject-tag.ms { background: #fef3e2; color: #e8910b; }

        .last-reviewed-recent { color: #21a366; font-weight: 500; }

        .actions-cell {
            display: flex;
            align-items: center;
            gap: 16px;
            color: #aaa;
        }

        .actions-cell i {
            cursor: pointer;
            transition: color 0.2s;
        }

        .actions-cell i:hover { color: #7B1D1D; }

        /* PAGINATION */
        .notes-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 4px;
        }

        .notes-footer .info {
            font-size: 12.5px;
            color: #999;
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .pagination button {
            min-width: 34px;
            height: 34px;
            border: 1px solid #eee;
            background: white;
            border-radius: 8px;
            font-size: 13px;
            color: #777;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
        }

        .pagination button:hover { background: #f6f6f6; }

        .pagination button.active {
            background: #7B1D1D;
            color: white;
            border-color: #7B1D1D;
        }

        .pagination button.dots {
            border: none;
            cursor: default;
        }

        .pagination button.dots:hover { background: white; }

        /* SIDE COLUMN */
        .side-col {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .side-title {
            font-size: 16px;
            font-weight: 600;
            color: #2b2b2b;
            margin-bottom: 18px;
        }

        .quick-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .quick-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
        }

        .quick-btn:hover { transform: translateY(-2px); }

        .quick-btn.red { background: #fdeef0; color: #c0392b; }
        .quick-btn.amber { background: #fef6e8; color: #e8910b; }
        .quick-btn.green { background: #ecf7f0; color: #21a366; }
        .quick-btn.blue { background: #eaf1fb; color: #3b7ddd; }

        .quick-btn i { font-size: 15px; }

        /* STREAK CARD */
        .streak-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .streak-head i {
            font-size: 20px;
            color: #f0712f;
        }

        .streak-head .title {
            font-size: 16px;
            font-weight: 600;
            color: #2b2b2b;
        }

        .streak-count-row {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 4px;
        }

        .streak-count-row .num {
            font-size: 30px;
            font-weight: 700;
            color: #2b2b2b;
        }

        .streak-count-row .txt {
            font-size: 13px;
            color: #555;
        }

        .streak-sub {
            font-size: 12px;
            color: #999;
            margin-bottom: 20px;
        }

        .streak-days {
            display: flex;
            justify-content: space-between;
        }

        .streak-day {
            text-align: center;
        }

        .streak-day .d {
            font-size: 12px;
            color: #888;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .streak-check {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .streak-check.done {
            background: #7B1D1D;
            color: white;
        }

        .streak-check.empty {
            border: 2px solid #eee;
            color: transparent;
        }

        /* TOP REVIEWED */
        .top-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .top-head .title {
            font-size: 16px;
            font-weight: 600;
            color: #2b2b2b;
        }

        .top-head a {
            font-size: 12px;
            color: #c0392b;
            text-decoration: none;
            font-weight: 500;
        }

        .top-head a:hover { text-decoration: underline; }

        .top-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 0;
            border-bottom: 1px solid #f4f4f4;
        }

        .top-item:last-child { border-bottom: none; }

        .top-icon {
            width: 30px;
            height: 30px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }

        .top-icon.red { background: #fdeaea; color: #c0392b; }
        .top-icon.green { background: #e8f7ee; color: #21a366; }
        .top-icon.purple { background: #f0eafb; color: #8e5bd0; }
        .top-icon.amber { background: #fef3e2; color: #e8910b; }

        .top-item .name {
            flex: 1;
            font-size: 13px;
            color: #444;
            font-weight: 500;
        }

        .top-item .reviews {
            font-size: 12px;
            color: #999;
        }

        /* RESPONSIVE */
        @media (max-width: 1300px) {
            .notes-grid { grid-template-columns: 1fr; }
            .stats-row { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 15px 0;
            }
            .main-content { margin-left: 0; padding: 20px; }
            .stats-row { grid-template-columns: 1fr; }
            .notes-table { display: block; overflow-x: auto; white-space: nowrap; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        @include('partials.sidebar', ['active' => 'review-notes'])

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- HEADER -->
            <div class="header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <div class="header-title">Review Notes</div>
                        <div class="header-subtitle">Organize your key learnings and important concepts.</div>
                    </div>
                </div>
                <div class="header-right">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search notes, topics, or subjects...">
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

            <!-- STAT CARDS -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon red"><i class="fas fa-file-lines"></i></div>
                    <div class="stat-info">
                        <div class="label">Total Notes</div>
                        <div class="value">128</div>
                        <div class="sub up">+ 12 this week</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-tag"></i></div>
                    <div class="stat-info">
                        <div class="label">Subjects Covered</div>
                        <div class="value">6</div>
                        <div class="sub muted">All CPA subject areas</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="fas fa-bookmark"></i></div>
                    <div class="stat-info">
                        <div class="label">Top Topic</div>
                        <div class="value" style="font-size:18px;">Audit Sampling</div>
                        <div class="sub muted">Reviewed 8 times</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amber"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <div class="label">Last Reviewed</div>
                        <div class="value" style="font-size:18px;">May 16, 2025</div>
                        <div class="sub muted">2 hours ago</div>
                    </div>
                </div>
            </div>

            <!-- MAIN GRID -->
            <div class="notes-grid">
                <!-- LEFT: NOTES TABLE -->
                <div class="card">
                    <div class="notes-card-head">
                        <div class="notes-card-title">Your Notes</div>
                        <div class="notes-toolbar">
                            <div class="notes-search">
                                <i class="fas fa-search"></i>
                                <input type="text" placeholder="Search your notes...">
                            </div>
                            <select class="notes-select">
                                <option>All Subjects</option>
                                <option>AUD</option>
                                <option>TAX</option>
                                <option>FAR</option>
                                <option>RFBT</option>
                                <option>MS</option>
                            </select>
                            <select class="notes-select">
                                <option>Most Recent</option>
                                <option>Oldest</option>
                                <option>A &ndash; Z</option>
                            </select>
                            <button class="new-note-btn"><i class="fas fa-plus"></i> New Note</button>
                        </div>
                    </div>

                    <table class="notes-table">
                        <thead>
                            <tr>
                                <th>Note Title</th>
                                <th>Subject</th>
                                <th>Topics</th>
                                <th>Last Reviewed</th>
                                <th>Created On <i class="fas fa-chevron-down"></i></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="note-title-cell">
                                        <div class="note-icon red"><i class="fas fa-file-lines"></i></div>
                                        <span class="note-title-text">Audit Sampling Key Concepts</span>
                                    </div>
                                </td>
                                <td><span class="subject-tag aud">AUD</span></td>
                                <td>3</td>
                                <td><span class="last-reviewed-recent">2 hours ago</span></td>
                                <td>May 16, 2025</td>
                                <td><div class="actions-cell"><i class="fas fa-eye"></i><i class="fas fa-ellipsis-vertical"></i></div></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="note-title-cell">
                                        <div class="note-icon green"><i class="fas fa-file-lines"></i></div>
                                        <span class="note-title-text">Tax Basis of Property</span>
                                    </div>
                                </td>
                                <td><span class="subject-tag tax">TAX</span></td>
                                <td>4</td>
                                <td>Yesterday</td>
                                <td>May 15, 2025</td>
                                <td><div class="actions-cell"><i class="fas fa-eye"></i><i class="fas fa-ellipsis-vertical"></i></div></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="note-title-cell">
                                        <div class="note-icon blue"><i class="fas fa-file-lines"></i></div>
                                        <span class="note-title-text">Financial Statement Assertions</span>
                                    </div>
                                </td>
                                <td><span class="subject-tag far">FAR</span></td>
                                <td>5</td>
                                <td>May 14, 2025</td>
                                <td>May 12, 2025</td>
                                <td><div class="actions-cell"><i class="fas fa-eye"></i><i class="fas fa-ellipsis-vertical"></i></div></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="note-title-cell">
                                        <div class="note-icon purple"><i class="fas fa-file-lines"></i></div>
                                        <span class="note-title-text">PSA 530 &ndash; Audit Sampling</span>
                                    </div>
                                </td>
                                <td><span class="subject-tag aud">AUD</span></td>
                                <td>6</td>
                                <td>May 13, 2025</td>
                                <td>May 10, 2025</td>
                                <td><div class="actions-cell"><i class="fas fa-eye"></i><i class="fas fa-ellipsis-vertical"></i></div></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="note-title-cell">
                                        <div class="note-icon red"><i class="fas fa-file-lines"></i></div>
                                        <span class="note-title-text">Cash Flow Statement Overview</span>
                                    </div>
                                </td>
                                <td><span class="subject-tag far">FAR</span></td>
                                <td>2</td>
                                <td>May 11, 2025</td>
                                <td>May 8, 2025</td>
                                <td><div class="actions-cell"><i class="fas fa-eye"></i><i class="fas fa-ellipsis-vertical"></i></div></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="note-title-cell">
                                        <div class="note-icon green"><i class="fas fa-file-lines"></i></div>
                                        <span class="note-title-text">Regulatory Framework Overview</span>
                                    </div>
                                </td>
                                <td><span class="subject-tag rfbt">RFBT</span></td>
                                <td>4</td>
                                <td>May 9, 2025</td>
                                <td>May 7, 2025</td>
                                <td><div class="actions-cell"><i class="fas fa-eye"></i><i class="fas fa-ellipsis-vertical"></i></div></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="note-title-cell">
                                        <div class="note-icon amber"><i class="fas fa-file-lines"></i></div>
                                        <span class="note-title-text">Management Advisory Services</span>
                                    </div>
                                </td>
                                <td><span class="subject-tag ms">MS</span></td>
                                <td>3</td>
                                <td>May 8, 2025</td>
                                <td>May 6, 2025</td>
                                <td><div class="actions-cell"><i class="fas fa-eye"></i><i class="fas fa-ellipsis-vertical"></i></div></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="note-title-cell">
                                        <div class="note-icon red"><i class="fas fa-file-lines"></i></div>
                                        <span class="note-title-text">Depreciation Methods in Taxation</span>
                                    </div>
                                </td>
                                <td><span class="subject-tag tax">TAX</span></td>
                                <td>2</td>
                                <td>May 6, 2025</td>
                                <td>May 5, 2025</td>
                                <td><div class="actions-cell"><i class="fas fa-eye"></i><i class="fas fa-ellipsis-vertical"></i></div></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="notes-footer">
                        <div class="info">Showing 1 to 8 of 128 notes</div>
                        <div class="pagination">
                            <button><i class="fas fa-chevron-left"></i></button>
                            <button class="active">1</button>
                            <button>2</button>
                            <button>3</button>
                            <button class="dots">...</button>
                            <button>16</button>
                            <button><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: SIDE COLUMN -->
                <div class="side-col">
                    <!-- QUICK ACCESS -->
                    <div class="card">
                        <div class="side-title">Quick Access</div>
                        <div class="quick-grid">
                            <button class="quick-btn red"><i class="fas fa-clock-rotate-left"></i> Recently Viewed</button>
                            <button class="quick-btn amber"><i class="fas fa-star"></i> Favorites</button>
                            <button class="quick-btn green"><i class="fas fa-book"></i> By Subject</button>
                            <button class="quick-btn blue"><i class="fas fa-tag"></i> By Topic</button>
                        </div>
                    </div>

                    <!-- REVIEW STREAK -->
                    <div class="card">
                        <div class="streak-head">
                            <i class="fas fa-fire"></i>
                            <span class="title">Review Streak</span>
                        </div>
                        <div class="streak-count-row">
                            <span class="num">7</span>
                            <span class="txt">days in a row!</span>
                        </div>
                        <div class="streak-sub">Keep reviewing to build your streak.</div>
                        <div class="streak-days">
                            <div class="streak-day"><div class="d">M</div><div class="streak-check done"><i class="fas fa-check"></i></div></div>
                            <div class="streak-day"><div class="d">T</div><div class="streak-check done"><i class="fas fa-check"></i></div></div>
                            <div class="streak-day"><div class="d">W</div><div class="streak-check done"><i class="fas fa-check"></i></div></div>
                            <div class="streak-day"><div class="d">T</div><div class="streak-check done"><i class="fas fa-check"></i></div></div>
                            <div class="streak-day"><div class="d">F</div><div class="streak-check done"><i class="fas fa-check"></i></div></div>
                            <div class="streak-day"><div class="d">S</div><div class="streak-check done"><i class="fas fa-check"></i></div></div>
                            <div class="streak-day"><div class="d">S</div><div class="streak-check empty"></div></div>
                        </div>
                    </div>

                    <!-- TOP REVIEWED TOPICS -->
                    <div class="card">
                        <div class="top-head">
                            <span class="title">Top Reviewed Topics</span>
                            <a href="#">View All</a>
                        </div>
                        <div class="top-item">
                            <div class="top-icon red"><i class="fas fa-file-lines"></i></div>
                            <span class="name">Audit Sampling</span>
                            <span class="reviews">8 reviews</span>
                        </div>
                        <div class="top-item">
                            <div class="top-icon green"><i class="fas fa-file-lines"></i></div>
                            <span class="name">Revenue Recognition</span>
                            <span class="reviews">7 reviews</span>
                        </div>
                        <div class="top-item">
                            <div class="top-icon purple"><i class="fas fa-file-lines"></i></div>
                            <span class="name">Tax Computation</span>
                            <span class="reviews">6 reviews</span>
                        </div>
                        <div class="top-item">
                            <div class="top-icon amber"><i class="fas fa-file-lines"></i></div>
                            <span class="name">Financial Statements</span>
                            <span class="reviews">5 reviews</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Fade-in animation
            const elements = document.querySelectorAll('.card, .stat-card');
            elements.forEach((el, index) => {
                el.style.animation = `slideUp 0.5s ease ${index * 0.06}s both`;
            });

            // Profile dropdown
            const profileBtn = document.getElementById('profileBtn');
            const profileDropdown = document.getElementById('profileDropdown');
            if (profileBtn && profileDropdown) {
                profileBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    profileDropdown.classList.toggle('active');
                });
                document.addEventListener('click', function () {
                    profileDropdown.classList.remove('active');
                });
                profileDropdown.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            }

            // Pagination
            document.querySelectorAll('.pagination button:not(.dots)').forEach(btn => {
                btn.addEventListener('click', function () {
                    if (this.querySelector('i')) return;
                    document.querySelectorAll('.pagination button').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });

        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
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
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }
    </script>
</body>
</html>

