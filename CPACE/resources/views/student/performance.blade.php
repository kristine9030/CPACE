<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance - CPACE CPA Reviewer</title>

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
            position: relative;
            flex: 0 1 320px;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 14px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
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
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 11px 16px;
            background: none;
            border: none;
            text-align: left;
            color: #333;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
            border-bottom: 1px solid #f5f5f5;
        }

        .dropdown-menu button i {
            width: 16px;
            text-align: center;
            color: #7B1D1D;
        }

        .dropdown-menu button:hover {
            background: #f9f9f9;
        }

        .dropdown-menu .logout-btn { color: #e53e3e; }
        .dropdown-menu .logout-btn i { color: #e53e3e; }

        /* TABS + DATE RANGE ROW */
        .controls-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .tabs {
            display: flex;
            gap: 8px;
            background: white;
            padding: 6px;
            border-radius: 10px;
        }

        .tab {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            border: none;
            background: transparent;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #777;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
            white-space: nowrap;
        }

        .tab i {
            font-size: 14px;
        }

        .tab.active {
            background: #fdeaea;
            color: #c0392b;
        }

        .tab:hover:not(.active) {
            background: #f6f6f6;
            color: #555;
        }

        .date-range {
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
            padding: 11px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            color: #444;
            cursor: pointer;
        }

        .date-range .fa-calendar {
            color: #7B1D1D;
        }

        .date-range .fa-chevron-down {
            color: #999;
            font-size: 11px;
        }

        /* MAIN LAYOUT GRID */
        .perf-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 25px;
            align-items: start;
        }

        .perf-main {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .perf-side {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        /* STAT CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            background: white;
            padding: 25px;
            border-radius: 12px;
        }

        .stat-card {
            display: flex;
            flex-direction: column;
        }

        .stat-card + .stat-card {
            border-left: 1px solid #f0f0f0;
            padding-left: 16px;
        }

        .stat-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 11px;
            color: #888;
            font-weight: 500;
            line-height: 1.3;
            max-width: 90px;
        }

        .stat-badge {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
        }

        .stat-badge.red { background: #fdeaea; color: #c0392b; }
        .stat-badge.green { background: #e8f7ee; color: #21a366; }
        .stat-badge.blue { background: #e9f1fd; color: #3b7ddd; }
        .stat-badge.amber { background: #fef3e2; color: #e8910b; }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: #2b2b2b;
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-number small {
            font-size: 13px;
            font-weight: 500;
            color: #888;
        }

        .stat-change {
            font-size: 10.5px;
            font-weight: 500;
            margin-bottom: 12px;
        }

        .stat-change.up { color: #21a366; }
        .stat-change.down { color: #c0392b; }
        .stat-change.muted { color: #999; }

        .stat-spark {
            margin-top: auto;
            height: 38px;
        }

        .stat-spark svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .spark-bars {
            display: flex;
            align-items: flex-end;
            gap: 3px;
            height: 38px;
        }

        .spark-bars span {
            flex: 1;
            border-radius: 2px;
        }

        /* CARD */
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
        }

        .card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #2b2b2b;
            font-family: 'Poppins', sans-serif;
        }

        .card-link {
            font-size: 12px;
            color: #c0392b;
            text-decoration: none;
            font-weight: 500;
        }

        .card-link:hover { text-decoration: underline; }

        .chart-select {
            border: 1px solid #e2e2e2;
            border-radius: 8px;
            padding: 7px 14px;
            font-size: 12px;
            color: #555;
            font-family: 'Poppins', sans-serif;
            background: white;
            cursor: pointer;
        }

        /* LINE CHART */
        .line-chart-wrap {
            position: relative;
            display: flex;
            gap: 12px;
        }

        .y-axis {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-size: 11px;
            color: #bbb;
            height: 230px;
            padding: 4px 0;
        }

        .line-chart {
            flex: 1;
            position: relative;
        }

        .line-chart svg {
            width: 100%;
            height: 230px;
            display: block;
        }

        .x-axis {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #aaa;
            margin-top: 8px;
            padding: 0 5px;
        }

        .chart-tooltip {
            position: absolute;
            top: 18%;
            left: 48%;
            background: white;
            border: 1px solid #eee;
            box-shadow: 0 6px 18px rgba(0,0,0,0.12);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 11px;
            color: #666;
            white-space: nowrap;
        }

        .chart-tooltip strong { color: #c0392b; }

        /* DONUT / MASTERY */
        .mastery-body {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .donut {
            width: 130px;
            height: 130px;
            position: relative;
            flex-shrink: 0;
        }

        .donut svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .donut-center {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .donut-center .num {
            font-size: 26px;
            font-weight: 700;
            color: #2b2b2b;
        }

        .donut-center .lbl {
            font-size: 10px;
            color: #999;
        }

        .mastery-legend {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .mastery-legend .row {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #555;
        }

        .mastery-legend .dot {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .mastery-legend .pct {
            margin-left: auto;
            font-weight: 700;
            color: #2b2b2b;
        }

        .dot.strong { background: #21a366; }
        .dot.medium { background: #f0b429; }
        .dot.weak { background: #c0392b; }

        .mastery-note {
            margin-top: 20px;
            background: #fdeef0;
            border-radius: 10px;
            padding: 14px 16px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
            font-size: 12px;
            color: #7a5b5b;
            line-height: 1.5;
        }

        .mastery-note i {
            color: #c0392b;
            font-size: 15px;
            margin-top: 1px;
        }

        /* ACCURACY BARS */
        .accuracy-item {
            margin-bottom: 16px;
        }

        .accuracy-item:last-child { margin-bottom: 0; }

        .accuracy-top {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #555;
            margin-bottom: 7px;
        }

        .accuracy-top .val {
            font-weight: 600;
            color: #2b2b2b;
        }

        .accuracy-bar {
            height: 7px;
            background: #f0f0f0;
            border-radius: 5px;
            overflow: hidden;
        }

        .accuracy-bar span {
            display: block;
            height: 100%;
            border-radius: 5px;
        }

        /* INSIGHTS */
        .insight-item {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 13px;
            border-radius: 10px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .insight-item:last-child { margin-bottom: 0; }
        .insight-item:hover { transform: translateX(3px); }

        .insight-item.red { background: #fdeef0; }
        .insight-item.green { background: #eaf7f0; }
        .insight-item.blue { background: #eaf1fb; }

        .insight-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
            background: white;
        }

        .insight-item.red .insight-icon { color: #c0392b; }
        .insight-item.green .insight-icon { color: #21a366; }
        .insight-item.blue .insight-icon { color: #3b7ddd; }

        .insight-content { flex: 1; }

        .insight-title {
            font-size: 12.5px;
            font-weight: 600;
            color: #2b2b2b;
            margin-bottom: 2px;
        }

        .insight-desc {
            font-size: 11px;
            color: #999;
            line-height: 1.4;
        }

        .insight-arrow {
            color: #bbb;
            font-size: 13px;
        }

        /* BOTTOM 3 COLUMNS */
        .bottom-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .list-item {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 13px 0;
            border-bottom: 1px solid #f3f3f3;
        }

        .list-item:last-child { border-bottom: none; }

        /* Scrollable list for cards that may hold many topics (strengths/weaknesses) */
        .list-scroll { max-height: 280px; overflow-y: auto; }
        .list-scroll::-webkit-scrollbar { width: 6px; }
        .list-scroll::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 3px; }
        .count-badge {
            font-size: 11px;
            font-weight: 600;
            color: #777;
            background: #f1f1f1;
            border-radius: 10px;
            padding: 2px 9px;
        }

        .list-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .list-icon.green { background: #e8f7ee; color: #21a366; }
        .list-icon.red { background: #fdeaea; color: #c0392b; }
        .list-icon.grey { background: #f1f1f1; color: #777; }
        .list-icon.amber { background: #fef3e2; color: #e8910b; }
        .list-icon.blue { background: #e9f1fd; color: #3b7ddd; }

        .list-content { flex: 1; }

        .list-title {
            font-size: 13px;
            font-weight: 600;
            color: #2b2b2b;
            margin-bottom: 2px;
        }

        .list-sub {
            font-size: 11px;
            color: #999;
        }

        .list-value {
            font-size: 15px;
            font-weight: 700;
        }

        .list-value.green { color: #21a366; }
        .list-value.red { color: #c0392b; }

        .list-meta {
            font-size: 11px;
            color: #aaa;
            text-align: right;
        }

        /* CONSISTENCY BANNER */
        .consistency {
            background: white;
            border-radius: 12px;
            padding: 22px 28px;
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .consistency-icon {
            font-size: 50px;
            color: #c0392b;
            flex-shrink: 0;
        }

        .consistency-text { flex: 1; }

        .consistency-text h4 {
            font-size: 17px;
            font-weight: 600;
            color: #c0392b;
            margin-bottom: 5px;
            font-family: 'Poppins', sans-serif;
        }

        .consistency-text p {
            font-size: 12.5px;
            color: #888;
            line-height: 1.5;
        }

        .streak-count {
            text-align: center;
            padding: 0 20px;
            border-left: 1px solid #f0f0f0;
            border-right: 1px solid #f0f0f0;
        }

        .streak-count .num {
            font-size: 30px;
            font-weight: 700;
            color: #2b2b2b;
        }

        .streak-count .lbl {
            font-size: 11px;
            color: #999;
        }

        .streak-days {
            display: flex;
            gap: 14px;
        }

        .streak-day {
            text-align: center;
        }

        .streak-day .d {
            font-size: 12px;
            color: #888;
            margin-bottom: 8px;
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
            background: #fdeaea;
            color: #c0392b;
        }

        .streak-check.empty {
            border: 2px solid #eee;
            color: transparent;
        }

        .consistency-btn {
            padding: 12px 22px;
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

        .consistency-btn:hover { background: #6a1818; }

        /* TAB PANELS + TABLES */
        .perf-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .perf-table th {
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #999;
            font-weight: 600;
            padding: 0 12px 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .perf-table td {
            padding: 13px 12px 13px 0;
            border-bottom: 1px solid #f5f5f5;
            color: #444;
            vertical-align: middle;
        }

        .perf-table tbody tr:last-child td { border-bottom: none; }
        .perf-table tbody tr:hover { background: #fafafa; }
        .perf-table .accuracy-bar { margin: 0; }

        /* RESPONSIVE */
        @media (max-width: 1500px) {
            .perf-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 1100px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .stat-card + .stat-card { border-left: none; padding-left: 0; }
            .bottom-grid { grid-template-columns: 1fr; }
            .consistency { flex-wrap: wrap; }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 15px 0;
            }
            .main-content { margin-left: 0; padding: 20px; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        @include('partials.sidebar', ['active' => 'performance'])
        @include('partials.student-bottom-nav', ['active' => 'performance'])
        @include('partials.student-mobile-header')

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- HEADER -->
            <div class="header">
                <div class="header-left">
                    <div>
                        <div class="header-title">Performance</div>
                        <div class="header-subtitle">Track your progress and identify areas to improve.</div>
                    </div>
                </div>
                <div class="header-right">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search topics, questions...">
                    </div>
                    <div class="header-icons">
                        <button class="icon-btn">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                        <div style="position: relative;">
                            <button class="profile-btn" id="profileBtn">@include('partials.avatar-content')</button>
                            <div class="dropdown-menu" id="profileDropdown">
                                <a href="#"><i class="fas fa-user"></i> Profile Settings</a>
                                <a href="#"><i class="fas fa-chart-line"></i> My Progress</a>
                                <a href="#"><i class="fas fa-question-circle"></i> Help & Support</a>
                                <form method="POST" action="{{ route('logout') }}" style="margin: 0; padding: 0;">
                                    @csrf
                                    <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABS + DATE RANGE -->
            <div class="controls-row">
                <div class="tabs">
                    <button class="tab active" data-tab="overview"><i class="fas fa-th-large"></i> Overview</button>
                    <button class="tab" data-tab="subject"><i class="fas fa-book"></i> By Subject</button>
                    <button class="tab" data-tab="topic"><i class="fas fa-tag"></i> By Topic</button>
                    <button class="tab" data-tab="quiztype"><i class="fas fa-clipboard"></i> By Quiz Type</button>
                    <button class="tab" data-tab="time"><i class="fas fa-clock"></i> By Time</button>
                </div>
                <div class="date-range">
                    <i class="fas fa-calendar"></i>
                    <span>{{ \Illuminate\Support\Carbon::now()->subDays(7)->format('M j') }} &ndash; {{ \Illuminate\Support\Carbon::now()->format('M j, Y') }}</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>

            <!-- ============ OVERVIEW TAB ============ -->
            <div class="tab-panel" data-panel="overview">

            <!-- MAIN GRID -->
            <div class="perf-grid">
                <!-- LEFT / MAIN COLUMN -->
                <div class="perf-main">
                    <!-- STAT CARDS -->
                    @php
                        // Render the "+/- N from last week" line for a delta value.
                        $deltaLine = function ($delta, $suffix) {
                            if ($delta > 0) {
                                return ['up', 'fa-arrow-up', "+{$delta}{$suffix}"];
                            }
                            if ($delta < 0) {
                                return ['down', 'fa-arrow-down', "{$delta}{$suffix}"];
                            }
                            return ['muted', 'fa-minus', 'No change'];
                        };
                        // For avg time, a DOWN (faster) is the good direction.
                        $timeDelta = $stats['avg_delta_secs'];
                    @endphp
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-head">
                                <span class="stat-label">Overall Accuracy</span>
                                <span class="stat-badge red"><i class="fas fa-shield-alt"></i></span>
                            </div>
                            <div class="stat-number">{{ $stats['accuracy'] }}%</div>
                            @php [$cls, $ic, $txt] = $deltaLine($stats['accuracy_delta'], '% from last week'); @endphp
                            <div class="stat-change {{ $cls }}"><i class="fas {{ $ic }}"></i> {{ $txt }}</div>
                            <div class="stat-spark">
                                <div class="spark-bars">
                                    @foreach($spark['accuracy'] as $h)
                                        <span style="height:{{ max(4, $h) }}%;background:#e58a8a"></span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-head">
                                <span class="stat-label">Questions Attempted</span>
                                <span class="stat-badge red"><i class="fas fa-list-ol"></i></span>
                            </div>
                            <div class="stat-number">{{ number_format($stats['attempted']) }}</div>
                            @php [$cls, $ic, $txt] = $deltaLine($stats['attempted_delta'], ' from last week'); @endphp
                            <div class="stat-change {{ $cls }}"><i class="fas {{ $ic }}"></i> {{ $txt }}</div>
                            <div class="stat-spark">
                                <div class="spark-bars">
                                    @foreach($spark['attempted'] as $h)
                                        <span style="height:{{ $h }}%;background:#e58a8a"></span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-head">
                                <span class="stat-label">Correct Answers</span>
                                <span class="stat-badge green"><i class="fas fa-check"></i></span>
                            </div>
                            <div class="stat-number">{{ number_format($stats['correct']) }}</div>
                            @php [$cls, $ic, $txt] = $deltaLine($stats['correct_delta'], ' from last week'); @endphp
                            <div class="stat-change {{ $cls }}"><i class="fas {{ $ic }}"></i> {{ $txt }}</div>
                            <div class="stat-spark">
                                <div class="spark-bars">
                                    @foreach($spark['correct'] as $h)
                                        <span style="height:{{ $h }}%;background:#7fd2a0"></span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-head">
                                <span class="stat-label">Average Time / Question</span>
                                <span class="stat-badge blue"><i class="fas fa-clock"></i></span>
                            </div>
                            <div class="stat-number">{{ $stats['avg_time'] }}</div>
                            @if($timeDelta < 0)
                                <div class="stat-change up"><i class="fas fa-arrow-down"></i> {{ abs($timeDelta) }}s faster</div>
                            @elseif($timeDelta > 0)
                                <div class="stat-change down"><i class="fas fa-arrow-up"></i> {{ $timeDelta }}s slower</div>
                            @else
                                <div class="stat-change muted"><i class="fas fa-minus"></i> No change</div>
                            @endif
                            <div class="stat-spark">
                                <div class="spark-bars">
                                    @foreach($spark['time'] as $h)
                                        <span style="height:{{ $h }}%;background:#8db5ec"></span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-head">
                                <span class="stat-label">Current Streak</span>
                                <span class="stat-badge red"><i class="fas fa-fire"></i></span>
                            </div>
                            <div class="stat-number">{{ $streakDays }} <small>{{ \Illuminate\Support\Str::plural('day', $streakDays) }}</small></div>
                            <div class="stat-change muted">{{ $streakDays > 0 ? 'Keep it up!' : 'Start a streak today!' }}</div>
                            <div class="stat-spark" style="display:flex;align-items:flex-end;">
                                <i class="fas fa-fire" style="font-size:30px;color:{{ $streakDays > 0 ? '#c0392b' : '#f0c9c9' }};margin:0 auto;"></i>
                            </div>
                        </div>
                    </div>

                    <!-- PERFORMANCE OVER TIME -->
                    <div class="card">
                        <div class="card-head">
                            <span class="card-title">Performance Over Time</span>
                            <select class="chart-select">
                                <option>Daily</option>
                                <option>Weekly</option>
                                <option>Monthly</option>
                            </select>
                        </div>
                        <div class="line-chart-wrap">
                            <div class="y-axis">
                                <span>100%</span>
                                <span>75%</span>
                                <span>50%</span>
                                <span>25%</span>
                                <span>0%</span>
                            </div>
                            <div class="line-chart">
                                <svg viewBox="0 0 700 230" preserveAspectRatio="none">
                                    <defs>
                                        <linearGradient id="areaFill" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#c0392b" stop-opacity="0.18"/>
                                            <stop offset="100%" stop-color="#c0392b" stop-opacity="0"/>
                                        </linearGradient>
                                    </defs>
                                    <!-- gridlines -->
                                    <line x1="0" y1="2" x2="700" y2="2" stroke="#f3f3f3"/>
                                    <line x1="0" y1="59" x2="700" y2="59" stroke="#f3f3f3"/>
                                    <line x1="0" y1="116" x2="700" y2="116" stroke="#f3f3f3"/>
                                    <line x1="0" y1="173" x2="700" y2="173" stroke="#f3f3f3"/>
                                    <line x1="0" y1="228" x2="700" y2="228" stroke="#f3f3f3"/>
                                    <!-- area -->
                                    <path d="{{ $chart['area'] }}" fill="url(#areaFill)"/>
                                    <!-- line -->
                                    <polyline fill="none" stroke="#c0392b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                        points="{{ $chart['points'] }}"/>
                                    <!-- highlighted point (latest day) -->
                                    <circle cx="{{ $chart['highlight']['x'] }}" cy="{{ $chart['highlight']['y'] }}" r="6" fill="#c0392b" stroke="white" stroke-width="2.5"/>
                                </svg>
                                @if($chart['has_data'])
                                    <div class="chart-tooltip">
                                        {{ $chart['highlight']['label'] }}<br>Accuracy: <strong>{{ $chart['highlight']['accuracy'] }}%</strong>
                                    </div>
                                @else
                                    <div class="chart-tooltip">No quiz activity yet</div>
                                @endif
                            </div>
                        </div>
                        <div class="x-axis" style="margin-left:48px;">
                            @foreach($chart['labels'] as $label)
                                <span>{{ $label }}</span>
                            @endforeach
                        </div>
                    </div>

                    <!-- BOTTOM 3 COLUMNS -->
                    @php
                        $modeIcons = [
                            'adaptive' => ['fa-brain', 'green'],
                            'topic'    => ['fa-tag', 'amber'],
                            'timed'    => ['fa-clock', 'blue'],
                            'challenge'=> ['fa-trophy', 'red'],
                        ];
                        $modeLabels = [
                            'adaptive' => 'Adaptive Quiz', 'topic' => 'Topic Quiz',
                            'timed' => 'Timed Quiz', 'challenge' => 'Challenge Quiz',
                        ];
                    @endphp
                    <div class="bottom-grid">
                        <!-- STRENGTHS -->
                        <div class="card">
                            <div class="card-head">
                                <span class="card-title">Your Strengths</span>
                                @if($strengths->isNotEmpty())
                                    <span class="count-badge">{{ $strengths->count() }} topic{{ $strengths->count() === 1 ? '' : 's' }} &middot; 75%+</span>
                                @endif
                            </div>
                            <div class="list-scroll">
                                @forelse($strengths as $s)
                                    <div class="list-item">
                                        <div class="list-icon green"><i class="fas fa-clipboard-check"></i></div>
                                        <div class="list-content">
                                            <div class="list-title">{{ $s->topic }}</div>
                                            <div class="list-sub">{{ $s->subject_code }}</div>
                                        </div>
                                        <div class="list-value green">{{ $s->accuracy }}%</div>
                                    </div>
                                @empty
                                    <div style="color:#999;font-size:12px;padding:14px 0;">No mastered topics yet &ndash; reach 75%+ over a few quizzes to build your strengths.</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- WEAKNESSES -->
                        <div class="card">
                            <div class="card-head">
                                <span class="card-title">Your Weaknesses</span>
                                @if($weaknesses->isNotEmpty())
                                    <span class="count-badge">{{ $weaknesses->count() }} topic{{ $weaknesses->count() === 1 ? '' : 's' }} &middot; needs review</span>
                                @endif
                            </div>
                            <div class="list-scroll">
                                @forelse($weaknesses as $w)
                                    <div class="list-item">
                                        <div class="list-icon red"><i class="fas fa-triangle-exclamation"></i></div>
                                        <div class="list-content">
                                            <div class="list-title">{{ $w->topic }}</div>
                                            <div class="list-sub">{{ $w->subject_code }}</div>
                                        </div>
                                        <div class="list-value red">{{ $w->accuracy }}%</div>
                                    </div>
                                @empty
                                    <div style="color:#999;font-size:12px;padding:14px 0;">No weak topics flagged &ndash; keep practising to keep it that way!</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- RECENT ACTIVITY -->
                        <div class="card">
                            <div class="card-head">
                                <span class="card-title">Recent Activity</span>
                            </div>
                            @forelse($recentActivity as $a)
                                @php [$icon, $tone] = $modeIcons[$a->mode] ?? ['fa-file-alt', 'grey']; @endphp
                                <div class="list-item">
                                    <div class="list-icon {{ $tone }}"><i class="fas {{ $icon }}"></i></div>
                                    <div class="list-content">
                                        <div class="list-title">{{ $modeLabels[$a->mode] ?? 'Quiz' }} &ndash; {{ $a->subject_code ?? 'General' }}</div>
                                        <div class="list-sub">Scored {{ (int) round($a->score_percent) }}%</div>
                                    </div>
                                    <div class="list-meta">{{ \Illuminate\Support\Carbon::parse($a->completed_at)->diffForHumans(null, true) }} ago</div>
                                </div>
                            @empty
                                <div style="color:#999;font-size:12px;padding:14px 0;">No quizzes completed yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- RIGHT / SIDE COLUMN -->
                <div class="perf-side">
                    <!-- OVERALL MASTERY -->
                    <div class="card">
                        <div class="card-head">
                            <span class="card-title">Overall Mastery</span>
                        </div>
                        <div class="mastery-body">
                            <div class="donut">
                                <svg viewBox="0 0 36 36">
                                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#f0f0f0" stroke-width="3.2"/>
                                    @if($mastery['has_data'])
                                        <!-- Strong (green) -->
                                        @if($mastery['strong'] > 0)
                                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#21a366" stroke-width="3.2"
                                            stroke-dasharray="{{ $mastery['strong_dash'] }} {{ 100 - $mastery['strong_dash'] }}" stroke-dashoffset="0"/>
                                        @endif
                                        <!-- Medium (amber) -->
                                        @if($mastery['medium'] > 0)
                                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#f0b429" stroke-width="3.2"
                                            stroke-dasharray="{{ $mastery['medium_dash'] }} {{ 100 - $mastery['medium_dash'] }}" stroke-dashoffset="{{ $mastery['medium_offset'] }}"/>
                                        @endif
                                        <!-- Weak (red) -->
                                        @if($mastery['weak'] > 0)
                                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#c0392b" stroke-width="3.2"
                                            stroke-dasharray="{{ $mastery['weak_dash'] }} {{ 100 - $mastery['weak_dash'] }}" stroke-dashoffset="{{ $mastery['weak_offset'] }}"/>
                                        @endif
                                    @endif
                                </svg>
                                <div class="donut-center">
                                    <span class="num">{{ $mastery['level'] }}%</span>
                                    <span class="lbl">Mastery Level</span>
                                </div>
                            </div>
                            <div class="mastery-legend">
                                <div class="row"><span class="dot strong"></span> Strong <span class="pct">{{ $mastery['strong'] }}%</span></div>
                                <div class="row"><span class="dot medium"></span> Medium <span class="pct">{{ $mastery['medium'] }}%</span></div>
                                <div class="row"><span class="dot weak"></span> Weak <span class="pct">{{ $mastery['weak'] }}%</span></div>
                            </div>
                        </div>
                        <div class="mastery-note">
                            <i class="fas fa-shield-alt"></i>
                            @if(! $mastery['has_data'])
                                <span>Take a quiz to start building your mastery profile.</span>
                            @elseif($mastery['level'] >= 75)
                                <span>You're doing great! Keep practising your weak topics to stay sharp.</span>
                            @else
                                <span>Focus on your weak topics to level up your overall mastery.</span>
                            @endif
                        </div>
                    </div>

                    <!-- ACCURACY BY SUBJECT -->
                    <div class="card">
                        <div class="card-head">
                            <span class="card-title">Accuracy by Subject Area</span>
                        </div>
                        @foreach($subjectAccuracy as $subj)
                            <div class="accuracy-item">
                                <div class="accuracy-top"><span>{{ $subj->name }} ({{ $subj->code }})</span><span class="val">{{ $subj->accuracy }}%</span></div>
                                <div class="accuracy-bar"><span style="width:{{ $subj->accuracy }}%;background:{{ $subj->color }}"></span></div>
                            </div>
                        @endforeach
                    </div>

                    <!-- INSIGHTS & RECOMMENDATIONS -->
                    <div class="card">
                        <div class="card-head">
                            <span class="card-title">Insights &amp; Recommendations</span>
                        </div>
                        @foreach($insights as $insight)
                            <a href="{{ route('adaptive-quizzes') }}" class="insight-item {{ $insight['tone'] }}" style="text-decoration:none;">
                                <div class="insight-icon"><i class="fas {{ $insight['icon'] }}"></i></div>
                                <div class="insight-content">
                                    <div class="insight-title">{{ $insight['title'] }}</div>
                                    <div class="insight-desc">{{ $insight['desc'] }}</div>
                                </div>
                                <i class="fas fa-arrow-right insight-arrow"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- CONSISTENCY BANNER -->
            <div class="consistency" style="margin-top:25px;">
                <div class="consistency-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="consistency-text">
                    <h4>Consistency is the key!</h4>
                    @if($streakDays > 0)
                        <p>You've been consistent for {{ $streakDays }} {{ \Illuminate\Support\Str::plural('day', $streakDays) }} in a row.<br>Keep it up and achieve your goals!</p>
                    @else
                        <p>Practise a little every day to build a streak.<br>Start a quiz today to get going!</p>
                    @endif
                </div>
                <div class="streak-count">
                    <div class="num">{{ $streakDays }}</div>
                    <div class="lbl">Current Streak days</div>
                </div>
                <div class="streak-days">
                    @foreach($weekActivity['days'] as $day)
                        <div class="streak-day">
                            <div class="d">{{ $day['label'] }}</div>
                            <div class="streak-check {{ $day['done'] ? 'done' : 'empty' }}">
                                @if($day['done'])<i class="fas fa-check"></i>@endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('calendar') }}" class="consistency-btn" style="text-decoration:none;display:inline-flex;align-items:center;">View Calendar</a>
            </div>

            </div><!-- /overview panel -->

            <!-- ============ BY SUBJECT TAB ============ -->
            <div class="tab-panel" data-panel="subject" style="display:none;">
                <div class="card">
                    <div class="card-head"><span class="card-title">Accuracy by Subject Area</span></div>
                    <table class="perf-table">
                        <thead>
                            <tr><th>Subject</th><th>Questions</th><th>Correct</th><th>Accuracy</th><th style="width:30%;">Progress</th></tr>
                        </thead>
                        <tbody>
                            @foreach($subjectAccuracy as $subj)
                                <tr>
                                    <td><strong>{{ $subj->code }}</strong> <span style="color:#999;">{{ $subj->name }}</span></td>
                                    <td>{{ number_format($subj->attempts) }}</td>
                                    <td>{{ number_format($subj->correct) }}</td>
                                    <td style="font-weight:700;color:{{ $subj->color }};">{{ $subj->accuracy }}%</td>
                                    <td>
                                        <div class="accuracy-bar"><span style="width:{{ $subj->accuracy }}%;background:{{ $subj->color }}"></span></div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============ BY TOPIC TAB ============ -->
            <div class="tab-panel" data-panel="topic" style="display:none;">
                <div class="card">
                    <div class="card-head"><span class="card-title">Accuracy by Topic</span></div>
                    @if($byTopic->isEmpty())
                        <div style="color:#999;font-size:13px;padding:20px 0;text-align:center;">
                            No topic data yet. Complete a few quizzes to see your per-topic performance.
                        </div>
                    @else
                        <table class="perf-table">
                            <thead>
                                <tr><th>Topic</th><th>Subject</th><th>Questions</th><th>Correct</th><th>Accuracy</th><th style="width:25%;">Progress</th></tr>
                            </thead>
                            <tbody>
                                @foreach($byTopic as $t)
                                    @php $tc = $t->accuracy >= 75 ? '#21a366' : ($t->accuracy >= 60 ? '#3b7ddd' : ($t->accuracy >= 45 ? '#e8910b' : '#c0392b')); @endphp
                                    <tr>
                                        <td><strong>{{ $t->topic }}</strong></td>
                                        <td><span style="color:#999;">{{ $t->subject_code }}</span></td>
                                        <td>{{ number_format($t->attempts) }}</td>
                                        <td>{{ number_format($t->correct) }}</td>
                                        <td style="font-weight:700;color:{{ $tc }};">{{ $t->accuracy }}%</td>
                                        <td><div class="accuracy-bar"><span style="width:{{ $t->accuracy }}%;background:{{ $tc }}"></span></div></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            <!-- ============ BY QUIZ TYPE TAB ============ -->
            <div class="tab-panel" data-panel="quiztype" style="display:none;">
                <div class="bottom-grid" style="grid-template-columns:repeat(4,1fr);">
                    @foreach($byQuizType as $q)
                        <div class="card">
                            <div class="card-head">
                                <span class="card-title" style="display:flex;align-items:center;gap:10px;">
                                    <span class="list-icon {{ $q['tone'] }}" style="width:32px;height:32px;"><i class="fas {{ $q['icon'] }}"></i></span>
                                    {{ $q['label'] }}
                                </span>
                            </div>
                            <div style="font-size:34px;font-weight:700;color:{{ $q['color'] }};line-height:1;">{{ $q['accuracy'] }}%</div>
                            <div style="font-size:11px;color:#999;margin-bottom:16px;">Accuracy</div>
                            <div class="accuracy-bar" style="margin-bottom:18px;"><span style="width:{{ $q['accuracy'] }}%;background:{{ $q['color'] }}"></span></div>
                            <div style="display:flex;justify-content:space-between;font-size:12px;color:#666;margin-bottom:8px;">
                                <span>Quizzes taken</span><strong>{{ $q['sessions'] }}</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;font-size:12px;color:#666;margin-bottom:8px;">
                                <span>Questions</span><strong>{{ number_format($q['attempted']) }}</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;font-size:12px;color:#666;">
                                <span>Correct</span><strong>{{ number_format($q['correct']) }}</strong>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- ============ BY TIME TAB ============ -->
            <div class="tab-panel" data-panel="time" style="display:none;">
                <div class="perf-grid">
                    <div class="perf-main">
                        <div class="card">
                            <div class="card-head">
                                <span class="card-title">Accuracy by Day of Week</span>
                                @if($byTime['best_day'])
                                    <span class="card-link" style="cursor:default;">Best: {{ $byTime['best_day'] }}</span>
                                @endif
                            </div>
                            @foreach($byTime['weekday'] as $wd)
                                <div class="accuracy-item">
                                    <div class="accuracy-top">
                                        <span>{{ $wd['label'] }} <span style="color:#bbb;">({{ $wd['attempted'] }} q)</span></span>
                                        <span class="val">{{ $wd['accuracy'] }}%</span>
                                    </div>
                                    <div class="accuracy-bar"><span style="width:{{ $wd['accuracy'] }}%;background:{{ $wd['color'] }}"></span></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="perf-side">
                        <div class="card">
                            <div class="card-head"><span class="card-title">Last 14 Days</span></div>
                            @if(! $byTime['has_data'])
                                <div style="color:#999;font-size:13px;padding:14px 0;text-align:center;">No activity in this period.</div>
                            @else
                                @foreach(array_reverse($byTime['daily']) as $d)
                                    <div class="list-item">
                                        <div class="list-content">
                                            <div class="list-title">{{ $d['date'] }}</div>
                                            <div class="list-sub">{{ $d['attempted'] }} answered &bull; {{ $d['correct'] }} correct</div>
                                        </div>
                                        <div class="list-value" style="color:{{ $d['attempted'] > 0 ? $d['color'] : '#ccc' }};">
                                            {{ $d['attempted'] > 0 ? $d['accuracy'].'%' : '—' }}
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Fade-in animation
            const elements = document.querySelectorAll('.card, .stats-grid, .consistency');
            elements.forEach((el, index) => {
                el.style.animation = `slideUp 0.5s ease ${index * 0.08}s both`;
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

            // Tabs — switch the active button AND show the matching panel.
            const panels = document.querySelectorAll('.tab-panel');
            document.querySelectorAll('.tab').forEach(tab => {
                tab.addEventListener('click', function () {
                    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    const target = this.dataset.tab;
                    panels.forEach(p => {
                        const show = p.dataset.panel === target;
                        p.style.display = show ? '' : 'none';
                        if (show) {
                            p.querySelectorAll('.card, .stats-grid, .consistency').forEach((el, i) => {
                                el.style.animation = `slideUp 0.4s ease ${i * 0.05}s both`;
                            });
                        }
                    });
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

    </script>
</body>
</html>

