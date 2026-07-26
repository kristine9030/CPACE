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
            background: linear-gradient(180deg, #a12626 0%, #7B1D1D 34%, #3d0c0c 74%, #1a0a0a 100%);
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
            text-decoration: none;
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
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

        /* ─── RESPONSIVE (added) ─── */
        @media (max-width: 1100px) {
            .main-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .main-content { padding: 20px 16px; }
            .header { flex-direction: column; align-items: flex-start; gap: 12px; }
            .header-right { width: 100%; flex-wrap: wrap; }
            .search-box { flex: 1; min-width: 0; }
            .header-title { font-size: 22px; }
            .stats-row { grid-template-columns: repeat(2, 1fr); gap: 14px; }
            .card { padding: 18px; }
        }

        @media (max-width: 480px) {
            .main-content { padding: 16px 12px; }
            .stats-row { grid-template-columns: 1fr; }
            .header-title { font-size: 20px; }
            .card-head { flex-direction: column; align-items: flex-start; gap: 10px; }
            .btn-primary { width: 100%; justify-content: center; }
            .tabs { gap: 14px; overflow-x: auto; white-space: nowrap; padding-bottom: 2px; }
        }

        /* ─── MOCK EXAM AREA ─── */
        .mock-area { position: relative; }
        .mock-flash {
            display: flex; align-items: center; gap: 9px;
            background: #e6f7ee; color: #1c8f52; border: 1px solid #b8e6cd;
            border-radius: 10px; padding: 11px 16px; font-size: 13px; font-weight: 500;
            margin-bottom: 18px;
        }

        /* ─── DRAMATIC DARK LOCK ─── */
        .mock-lock {
            position: absolute; inset: 0; z-index: 30;
            display: flex; align-items: flex-start; justify-content: center;
            padding: 44px 24px;
            background: rgba(10, 4, 4, 0.66);
            backdrop-filter: blur(9px); -webkit-backdrop-filter: blur(9px);
            border-radius: 16px;
        }
        .lock-card {
            position: relative; overflow: hidden;
            width: 100%; max-width: 500px;
            padding: 46px 44px 34px;
            text-align: center; color: #333;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 22px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.35);
        }
        .lock-shapes span { position: absolute; pointer-events: none; z-index: 0; }
        .lock-shapes span:nth-child(1) { top: 22px; left: 22px; width: 58px; height: 58px; border: 1.5px solid rgba(123,29,29,0.07); border-radius: 16px; transform: rotate(24deg); }
        .lock-shapes span:nth-child(2) { bottom: 22px; right: 24px; width: 70px; height: 70px; border: 1.5px solid rgba(192,57,43,0.07); border-radius: 50%; }

        .lock-badge {
            position: relative; z-index: 1;
            width: 90px; height: 90px; margin: 0 auto 22px;
            border-radius: 24px;
            background: linear-gradient(135deg, #e04a3a, #7B1D1D);
            color: #fff; font-size: 36px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 14px 30px rgba(192,57,43,0.32);
        }
        .lock-kicker { position: relative; z-index: 1; font-size: 11px; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; color: #c0392b; margin-bottom: 10px; }
        .lock-card h2 { position: relative; z-index: 1; font-size: 25px; font-weight: 800; color: #2d2d2d; margin-bottom: 12px; letter-spacing: 0.3px; }
        .lock-card p { position: relative; z-index: 1; font-size: 13.5px; color: #666; line-height: 1.7; margin-bottom: 24px; }
        .lock-card p strong { color: #c0392b; font-weight: 600; }
        .lock-error {
            position: relative; z-index: 1;
            background: #fdecec; color: #c0392b; border: 1px solid #f5c6c6;
            border-radius: 10px; padding: 10px 14px; font-size: 13px; margin-bottom: 18px;
            display: flex; align-items: center; gap: 8px; justify-content: center;
        }
        .lock-form { position: relative; z-index: 1; }
        .lock-label { display: block; font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #999; margin-bottom: 10px; }
        .lock-inputrow { display: flex; gap: 10px; }
        .lock-inputrow input {
            flex: 1; min-width: 0;
            padding: 14px 16px; border-radius: 12px;
            background: #faf9f9; border: 1.5px solid #ddd;
            color: #333; font-size: 18px; font-family: inherit; letter-spacing: 5px; text-align: center; text-transform: uppercase;
            outline: none; transition: border-color 0.2s, box-shadow 0.2s;
        }
        .lock-inputrow input::placeholder { color: #bbb; letter-spacing: 2px; }
        .lock-inputrow input:focus { border-color: #c0392b; box-shadow: 0 0 0 3px rgba(192,57,43,0.12); }
        .lock-inputrow button {
            background: linear-gradient(135deg, #e04a3a, #7B1D1D); color: #fff; border: none;
            padding: 14px 22px; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer;
            display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;
            transition: filter 0.2s, transform 0.2s;
        }
        .lock-inputrow button:hover { filter: brightness(1.06); transform: translateY(-1px); }
        .lock-hint { position: relative; z-index: 1; font-size: 12px; color: #999; margin-top: 18px; display: flex; align-items: center; gap: 6px; justify-content: center; }

        /* ─── COMPREHENSIVE PRE-BOARD EXAM ─── */
        .pb-hero {
            position: relative; overflow: hidden;
            border-radius: 20px; padding: 40px 44px; margin-bottom: 20px;
            background: linear-gradient(125deg, #7B1D1D 0%, #4a1212 46%, #190808 100%);
            color: #fff; box-shadow: 0 18px 44px rgba(58,16,16,0.42);
        }
        .pb-hero-shapes span { position: absolute; pointer-events: none; z-index: 0; }
        .pb-hero-shapes span:nth-child(1) { top: -40px; right: 8%; width: 150px; height: 150px; border: 2px solid rgba(255,255,255,0.05); border-radius: 40px; transform: rotate(30deg); }
        .pb-hero-shapes span:nth-child(2) { bottom: -54px; right: 24%; width: 130px; height: 130px; border: 2px solid rgba(255,215,106,0.08); border-radius: 50%; }
        .pb-hero-shapes span:nth-child(3) { top: 30px; right: 3%; width: 60px; height: 60px; background: rgba(192,57,43,0.28); border-radius: 16px; transform: rotate(20deg); }
        .pb-hero-body { position: relative; z-index: 1; max-width: 660px; }
        .pb-badge {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
            color: #ffca6a; background: rgba(255,202,106,0.12); border: 1px solid rgba(255,202,106,0.28);
            padding: 6px 14px; border-radius: 20px; margin-bottom: 16px;
        }
        .pb-title { font-size: 34px; font-weight: 800; line-height: 1.12; margin-bottom: 12px; }
        .pb-sub { font-size: 14px; color: rgba(255,255,255,0.74); line-height: 1.65; margin-bottom: 20px; }
        .pb-meta { display: flex; flex-wrap: wrap; gap: 22px; margin-bottom: 26px; }
        .pb-meta span { display: inline-flex; align-items: center; gap: 8px; font-size: 12.5px; color: rgba(255,255,255,0.82); font-weight: 500; }
        .pb-meta span i { color: #ffca6a; }
        .pb-start {
            display: inline-flex; align-items: center; gap: 10px;
            background: #fff; color: #7B1D1D; text-decoration: none;
            padding: 14px 28px; border-radius: 12px; font-size: 15px; font-weight: 700;
            box-shadow: 0 10px 24px rgba(0,0,0,0.28);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .pb-start:hover { transform: translateY(-2px); box-shadow: 0 14px 30px rgba(0,0,0,0.34); }

        .pb-specs { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
        .pb-spec {
            background: #fff; border: 1px solid #eef0f2; border-radius: 16px;
            padding: 20px 22px; display: flex; align-items: center; gap: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }
        .pb-spec-ic {
            width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: #c0392b; background: linear-gradient(135deg, #fdecec, #f7d3d3);
        }
        .pb-spec-num { font-size: 24px; font-weight: 800; color: #2d2d2d; line-height: 1; }
        .pb-spec-lbl { font-size: 11.5px; color: #888; margin-top: 4px; font-weight: 500; }

        .pb-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 18px; margin-bottom: 20px; }
        .pb-card { background: #fff; border: 1px solid #eef0f2; border-radius: 18px; padding: 26px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
        .pb-card-h { font-size: 16px; font-weight: 700; color: #2d2d2d; margin-bottom: 18px; display: flex; align-items: center; gap: 10px; }
        .pb-card-h i { color: #c0392b; }
        .pb-subjects { list-style: none; margin: 0; padding: 0; }
        .pb-subjects li { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f4f4f4; }
        .pb-subjects li:last-child { border-bottom: none; }
        .pb-scode { width: 54px; flex-shrink: 0; text-align: center; font-size: 11px; font-weight: 700; color: #fff; padding: 6px 0; border-radius: 7px; background: linear-gradient(135deg, #c0392b, #7B1D1D); letter-spacing: 0.5px; }
        .pb-sname { flex: 1; font-size: 13.5px; color: #333; }
        .pb-sitems { font-size: 12px; color: #888; font-weight: 600; }
        .pb-rules { margin: 0; padding-left: 20px; }
        .pb-rules li { font-size: 13px; color: #555; line-height: 1.6; margin-bottom: 12px; padding-left: 4px; }
        .pb-rules li:last-child { margin-bottom: 0; }
        .pb-rules li::marker { color: #c0392b; font-weight: 700; }

        @media (max-width: 1000px) {
            .pb-specs { grid-template-columns: repeat(2, 1fr); }
            .pb-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 560px) {
            .lock-inputrow { flex-direction: column; }
            .lock-inputrow button { justify-content: center; }
            .pb-title { font-size: 26px; }
            .pb-hero { padding: 28px 22px; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        @include('partials.sidebar', ['active' => 'mock-exams'])
        @include('partials.student-bottom-nav', ['active' => 'mock-exams'])
        @include('partials.student-mobile-header')

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- HEADER -->
            <div class="header">
                <div class="header-left">
                    <div>
                        <div class="header-title">Mock Exams</div>
                        <div class="header-subtitle">Simulate the real CPA Exam and track your readiness.</div>
                    </div>
                </div>
                <div class="header-right">
                    <div class="search-box gs-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" data-gs="true" placeholder="Search topics, questions, subjects...">
                    </div>
                    <div class="header-icons">
                        <button class="icon-btn" onclick="window.location.href='{{ route('messages.index') }}'" title="Messages" aria-label="Messages">
                            <i class="fas fa-comment-dots"></i>
                            @if($unreadMessages > 0)<span class="notification-badge">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>@endif
                        </button>
                        <button class="icon-btn" onclick="window.location.href='{{ route('notifications.index') }}'" title="Notifications" aria-label="Notifications">
                            <i class="fas fa-bell"></i>
                            @if($unreadNotifications > 0)<span class="notification-badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>@endif
                        </button>
                        <div class="header-dropdown-wrap">
                            <button class="profile-btn" id="profileBtn">@include('partials.avatar-content')</button>
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

            @if(session('status'))
                <div class="mock-flash"><i class="fas fa-circle-check"></i> {{ session('status') }}</div>
            @endif

            <div class="mock-area">

            @if($isAlumniLocked)
                <!-- LOCK OVERLAY: alumni no longer take mock exams -->
                <div class="mock-lock">
                    <div class="lock-card">
                        <div class="lock-shapes"><span></span><span></span></div>
                        <div class="lock-badge"><i class="fas fa-user-graduate"></i></div>
                        <div class="lock-kicker">Alumni Account</div>
                        <h2>Mock Exams Are Locked For You</h2>
                        <p>You're marked as an <strong>alumni</strong> in CPACE, so this feature is reserved for active reviewers only. Check out the <strong>Resource Library</strong> in your sidebar to share materials with current students instead.</p>
                        <div class="lock-hint"><i class="fas fa-circle-info"></i> Contact the Program Chair if this looks wrong.</div>
                    </div>
                </div>
            @elseif(! $mockUnlocked)
                <!-- LOCK OVERLAY: proctored, faculty-administered exam -->
                <div class="mock-lock">
                    <div class="lock-card">
                        <div class="lock-shapes"><span></span><span></span></div>
                        <div class="lock-badge"><i class="fas fa-lock"></i></div>
                        <div class="lock-kicker">Comprehensive Pre-Board Examination</div>
                        <h2>This Examination is Locked</h2>
                        <p>This is a <strong>proctored, faculty-administered</strong> exam. It unlocks only when your proctor starts the session and provides the access code.</p>

                        @if($errors->has('access_code'))
                            <div class="lock-error"><i class="fas fa-circle-exclamation"></i> {{ $errors->first('access_code') }}</div>
                        @endif

                        <form method="POST" action="{{ route('mock-exams.unlock') }}" class="lock-form">
                            @csrf
                            <label class="lock-label">Enter Access Code</label>
                            <div class="lock-inputrow">
                                <input type="text" name="access_code" placeholder="• • • • • •" autocomplete="off" autofocus required>
                                <button type="submit"><i class="fas fa-unlock-keyhole"></i> Unlock</button>
                            </div>
                        </form>
                        <div class="lock-hint"><i class="fas fa-circle-info"></i> Codes are issued at exam time &mdash; do not share yours.</div>
                    </div>
                </div>
            @endif

            <!-- COMPREHENSIVE PRE-BOARD EXAM -->
            <div class="pb-hero">
                <div class="pb-hero-shapes"><span></span><span></span><span></span></div>
                <div class="pb-hero-body">
                    <span class="pb-badge"><i class="fas fa-shield-halved"></i> Comprehensive &middot; Pre-Board</span>
                    <h1 class="pb-title">Comprehensive Pre-Board Examination</h1>
                    <p class="pb-sub">A full, timed simulation of the CPA Licensure Examination (CPALE). All six subjects in one sitting, under real exam conditions &mdash; your truest measure of board readiness.</p>
                    <div class="pb-meta">
                        <span><i class="fas fa-user-shield"></i> Proctored by Faculty</span>
                        <span><i class="fas fa-calendar-day"></i> Scheduled Session</span>
                        <span><i class="fas fa-hourglass-half"></i> One Continuous Sitting</span>
                    </div>
                    <a href="{{ route('mock-exams.simulation', ['exam' => 'Comprehensive Pre-Board Examination']) }}" class="pb-start"><i class="fas fa-play"></i> Begin Examination</a>
                </div>
            </div>

            <!-- SPECS -->
            <div class="pb-specs">
                <div class="pb-spec">
                    <div class="pb-spec-ic"><i class="fas fa-list-ol"></i></div>
                    <div><div class="pb-spec-num">350</div><div class="pb-spec-lbl">Total Items</div></div>
                </div>
                <div class="pb-spec">
                    <div class="pb-spec-ic"><i class="fas fa-clock"></i></div>
                    <div><div class="pb-spec-num">4h 00m</div><div class="pb-spec-lbl">Time Limit</div></div>
                </div>
                <div class="pb-spec">
                    <div class="pb-spec-ic"><i class="fas fa-layer-group"></i></div>
                    <div><div class="pb-spec-num">6</div><div class="pb-spec-lbl">Subjects</div></div>
                </div>
                <div class="pb-spec">
                    <div class="pb-spec-ic"><i class="fas fa-award"></i></div>
                    <div><div class="pb-spec-num">75%</div><div class="pb-spec-lbl">Passing Score</div></div>
                </div>
            </div>

            <!-- COVERAGE + GUIDELINES -->
            <div class="pb-grid">
                <div class="pb-card">
                    <div class="pb-card-h"><i class="fas fa-book-open"></i> Subject Coverage</div>
                    <ul class="pb-subjects">
                        <li><span class="pb-scode">FAR</span><span class="pb-sname">Financial Accounting &amp; Reporting</span><span class="pb-sitems">70 items</span></li>
                        <li><span class="pb-scode">AFAR</span><span class="pb-sname">Advanced Financial Accounting &amp; Reporting</span><span class="pb-sitems">70 items</span></li>
                        <li><span class="pb-scode">MS</span><span class="pb-sname">Management Services</span><span class="pb-sitems">60 items</span></li>
                        <li><span class="pb-scode">TAX</span><span class="pb-sname">Taxation</span><span class="pb-sitems">50 items</span></li>
                        <li><span class="pb-scode">AUD</span><span class="pb-sname">Auditing</span><span class="pb-sitems">50 items</span></li>
                        <li><span class="pb-scode">RFBT</span><span class="pb-sname">Regulatory Framework for Business Transactions</span><span class="pb-sitems">50 items</span></li>
                    </ul>
                </div>
                <div class="pb-card">
                    <div class="pb-card-h"><i class="fas fa-clipboard-check"></i> Examination Guidelines</div>
                    <ol class="pb-rules">
                        <li>Once you begin, the exam runs on a single continuous timer and cannot be paused.</li>
                        <li>Ensure a stable internet connection and enough battery for the full sitting.</li>
                        <li>No external notes, references, or applications are permitted during the exam.</li>
                        <li>Unanswered items are marked wrong; answers auto-submit when time expires.</li>
                        <li>Scores and rationales are released after your proctor closes the session.</li>
                    </ol>
                </div>
            </div>

            </div><!-- /.mock-area -->
        </main>
    </div>

    <script>
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
    @include('partials.global-search')
</body>
</html>
