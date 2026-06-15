<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        .notes-table th a {
            color: #999;
            text-decoration: none;
        }
        .notes-table th a:hover { color: #7B1D1D; }

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
        .subject-tag.afar { background: #eaf0fb; color: #2f63c4; }
        .subject-tag.rfbt { background: #f0eafb; color: #8e5bd0; }
        .subject-tag.ms { background: #fef3e2; color: #e8910b; }
        .subject-tag.none { background: #eee; color: #888; }

        .last-reviewed-recent { color: #21a366; font-weight: 500; }
        .muted-cell { color: #bbb; }

        .actions-cell {
            display: flex;
            align-items: center;
            gap: 14px;
            color: #aaa;
        }

        .actions-cell i {
            cursor: pointer;
            transition: color 0.2s;
        }

        .actions-cell i:hover { color: #7B1D1D; }
        .actions-cell i.fav-on { color: #e8910b; }
        .actions-cell i.del:hover { color: #c0392b; }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #aaa;
        }
        .empty-state i { font-size: 42px; margin-bottom: 14px; color: #d8d8d8; }
        .empty-state p { font-size: 14px; margin-bottom: 4px; color: #888; }
        .empty-state span { font-size: 12.5px; }

        /* PAGINATION */
        .notes-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 4px;
            flex-wrap: wrap;
            gap: 12px;
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

        .pagination a, .pagination span {
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
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            padding: 0 8px;
        }

        .pagination a:hover { background: #f6f6f6; }

        .pagination .active {
            background: #7B1D1D;
            color: white;
            border-color: #7B1D1D;
        }

        .pagination .disabled {
            color: #ccc;
            cursor: not-allowed;
        }

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
            text-decoration: none;
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

        .empty-mini { font-size: 12.5px; color: #aaa; padding: 8px 0; }

        /* MODAL */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 20px;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: #fff;
            border-radius: 14px;
            width: 100%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
            animation: modalUp 0.25s ease;
        }
        @keyframes modalUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .modal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #f0f0f0;
        }
        .modal-head h3 { font-size: 17px; font-weight: 600; color: #2b2b2b; }
        .modal-close {
            background: none; border: none; font-size: 20px; color: #aaa; cursor: pointer; line-height: 1;
        }
        .modal-close:hover { color: #c0392b; }
        .modal-body { padding: 22px 24px; }
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block; font-size: 12.5px; font-weight: 600; color: #555; margin-bottom: 7px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 13px;
            border: 1px solid #e2e2e2;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            color: #444;
        }
        .form-group textarea { resize: vertical; min-height: 120px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: #7B1D1D;
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-hint { font-size: 11px; color: #aaa; margin-top: 5px; }
        .field-error { font-size: 11.5px; color: #c0392b; margin-top: 5px; display: none; }
        .modal-foot {
            display: flex; justify-content: flex-end; gap: 12px;
            padding: 16px 24px; border-top: 1px solid #f0f0f0;
        }
        .btn-secondary {
            padding: 10px 18px; border: 1px solid #ddd; background: #fff; color: #666;
            border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; font-family: 'Poppins', sans-serif;
        }
        .btn-secondary:hover { background: #f6f6f6; }
        .btn-primary {
            padding: 10px 20px; border: none; background: #7B1D1D; color: #fff;
            border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif;
        }
        .btn-primary:hover { background: #6a1818; }
        .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

        /* VIEW MODAL */
        .view-meta { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; align-items: center; }
        .view-meta .meta-pill { font-size: 11.5px; color: #888; background: #f5f5f5; border-radius: 20px; padding: 4px 11px; }
        .view-content {
            font-size: 13.5px; line-height: 1.7; color: #444; white-space: pre-wrap;
            background: #fafafa; border-radius: 10px; padding: 16px; min-height: 80px;
        }
        .view-tags { margin-top: 16px; display: flex; flex-wrap: wrap; gap: 8px; }
        .view-tags span { font-size: 11px; font-weight: 500; color: #7B1D1D; background: #f6ecec; border-radius: 16px; padding: 4px 11px; }

        /* TOAST */
        .toast {
            position: fixed;
            bottom: 26px;
            right: 26px;
            background: #2b2b2b;
            color: #fff;
            padding: 13px 20px;
            border-radius: 10px;
            font-size: 13px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 3000;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
            pointer-events: none;
        }
        .toast.show { opacity: 1; transform: translateY(0); }
        .toast.success i { color: #4ade80; }
        .toast.error { background: #c0392b; }

        /* RESPONSIVE */
        @media (max-width: 1300px) {
            .notes-grid { grid-template-columns: 1fr; }
            .stats-row { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 20px; }
            .stats-row { grid-template-columns: 1fr; }
            .notes-table { display: block; overflow-x: auto; white-space: nowrap; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        @include('partials.sidebar', ['active' => 'review-notes'])
        @include('partials.student-bottom-nav', ['active' => 'review-notes'])
        @include('partials.student-mobile-header')

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- HEADER -->
            <div class="header">
                <div class="header-left">
                    <div>
                        <div class="header-title">Review Notes</div>
                        <div class="header-subtitle">Organize your key learnings and important concepts.</div>
                    </div>
                </div>
                <div class="header-right">
                    <form class="search-box" method="GET" action="{{ route('review-notes') }}">
                        <i class="fas fa-search"></i>
                        <input type="text" name="q" value="{{ $search }}" placeholder="Search notes, topics, or subjects...">
                        @if($subject) <input type="hidden" name="subject" value="{{ $subject }}"> @endif
                    </form>
                    <div class="header-icons">
                        <a href="{{ route('calendar') }}" class="icon-btn" title="Calendar">
                            <i class="fas fa-calendar-alt"></i>
                        </a>
                        <div style="position: relative;">
                            <button class="profile-btn" id="profileBtn">@include('partials.avatar-content')</button>
                            <div class="dropdown-menu" id="profileDropdown">
                                <a href="{{ route('performance') }}"><i class="fas fa-chart-line"></i> My Progress</a>
                                <a href="{{ route('achievements') }}"><i class="fas fa-trophy"></i> Achievements</a>
                                <form method="POST" action="{{ route('logout') }}" style="margin: 0; padding: 0;">
                                    @csrf
                                    <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
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
                        <div class="value">{{ $stats['total'] }}</div>
                        @if($stats['this_week'] > 0)
                            <div class="sub up">+ {{ $stats['this_week'] }} this week</div>
                        @else
                            <div class="sub muted">No new notes this week</div>
                        @endif
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-tag"></i></div>
                    <div class="stat-info">
                        <div class="label">Subjects Covered</div>
                        <div class="value">{{ $stats['subjects'] }}</div>
                        <div class="sub muted">of {{ $stats['subjects_total'] }} CPA subject areas</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="fas fa-bookmark"></i></div>
                    <div class="stat-info">
                        <div class="label">Top Topic</div>
                        <div class="value" style="font-size:18px;">{{ $stats['top_topic'] ?? '—' }}</div>
                        <div class="sub muted">
                            @if($stats['top_topic'])
                                Reviewed {{ $stats['top_topic_count'] }} time{{ $stats['top_topic_count'] == 1 ? '' : 's' }}
                            @else
                                Review notes to rank topics
                            @endif
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amber"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <div class="label">Last Reviewed</div>
                        <div class="value" style="font-size:18px;">{{ $stats['last_reviewed'] ? $stats['last_reviewed']->format('M j, Y') : '—' }}</div>
                        <div class="sub muted">{{ $stats['last_reviewed'] ? $stats['last_reviewed']->diffForHumans() : 'No reviews yet' }}</div>
                    </div>
                </div>
            </div>

            <!-- MAIN GRID -->
            <div class="notes-grid">
                <!-- LEFT: NOTES TABLE -->
                <div class="card">
                    <div class="notes-card-head">
                        <div class="notes-card-title">Your Notes</div>
                        <form class="notes-toolbar" method="GET" action="{{ route('review-notes') }}" id="filterForm" onsubmit="return false;">
                            <div class="notes-search">
                                <i class="fas fa-search"></i>
                                <input type="text" id="notesSearchInput" name="q" value="{{ $search }}" placeholder="Search your notes..." autocomplete="off">
                            </div>
                            <select class="notes-select" name="subject">
                                <option value="">All Subjects</option>
                                @foreach($subjects as $subj)
                                    <option value="{{ $subj->id }}" {{ (string)$subject === (string)$subj->id ? 'selected' : '' }}>{{ $subj->code }}</option>
                                @endforeach
                            </select>
                            <select class="notes-select" name="sort">
                                <option value="recent" {{ $sort === 'recent' ? 'selected' : '' }}>Most Recent</option>
                                <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest</option>
                                <option value="az" {{ $sort === 'az' ? 'selected' : '' }}>A &ndash; Z</option>
                                <option value="reviewed" {{ $sort === 'reviewed' ? 'selected' : '' }}>Recently Reviewed</option>
                            </select>
                            <input type="hidden" name="filter" value="{{ $filter }}">
                            <button type="button" class="new-note-btn" onclick="openCreateModal()"><i class="fas fa-plus"></i> New Note</button>
                        </form>
                    </div>

                    <div id="notesTableContainer">
                        @include('student.partials.review-notes-table')
                    </div>
                </div>

                <!-- RIGHT: SIDE COLUMN -->
                <div class="side-col">
                    <!-- QUICK ACCESS -->
                    <div class="card">
                        <div class="side-title">Quick Access</div>
                        <div class="quick-grid">
                            <a class="quick-btn red" href="{{ route('review-notes', ['filter' => 'recent', 'sort' => 'reviewed']) }}"><i class="fas fa-clock-rotate-left"></i> Recently Viewed</a>
                            <a class="quick-btn amber" href="{{ route('review-notes', ['filter' => 'favorites']) }}"><i class="fas fa-star"></i> Favorites</a>
                            <a class="quick-btn green" href="{{ route('review-notes', ['sort' => 'recent']) }}"><i class="fas fa-book"></i> All Notes</a>
                            <a class="quick-btn blue" href="{{ route('review-notes', ['sort' => 'az']) }}"><i class="fas fa-tag"></i> A &ndash; Z</a>
                        </div>
                    </div>

                    <!-- REVIEW STREAK -->
                    <div class="card">
                        <div class="streak-head">
                            <i class="fas fa-fire"></i>
                            <span class="title">Review Streak</span>
                        </div>
                        <div class="streak-count-row">
                            <span class="num">{{ $streakDays }}</span>
                            <span class="txt">day{{ $streakDays == 1 ? '' : 's' }} in a row!</span>
                        </div>
                        <div class="streak-sub">Keep reviewing to build your streak.</div>
                        <div class="streak-days">
                            @foreach($weekDays as $day)
                                <div class="streak-day">
                                    <div class="d">{{ $day['label'] }}</div>
                                    <div class="streak-check {{ $day['done'] ? 'done' : 'empty' }}">
                                        @if($day['done'])<i class="fas fa-check"></i>@endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- TOP REVIEWED TOPICS -->
                    <div class="card">
                        <div class="top-head">
                            <span class="title">Top Reviewed Topics</span>
                            <a href="{{ route('review-notes', ['sort' => 'reviewed']) }}">View All</a>
                        </div>
                        @php $topColors = ['red','green','purple','amber']; @endphp
                        @forelse($topReviewed as $i => $item)
                            <div class="top-item">
                                <div class="top-icon {{ $topColors[$i % count($topColors)] }}"><i class="fas fa-file-lines"></i></div>
                                <span class="name">{{ $item['name'] }}</span>
                                <span class="reviews">{{ $item['reviews'] }} review{{ $item['reviews'] == 1 ? '' : 's' }}</span>
                            </div>
                        @empty
                            <div class="empty-mini">No reviewed topics yet. Open a note to start tracking.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- CREATE / EDIT MODAL -->
    <div class="modal-overlay" id="noteModal">
        <div class="modal">
            <div class="modal-head">
                <h3 id="noteModalTitle">New Note</h3>
                <button class="modal-close" onclick="closeModal('noteModal')">&times;</button>
            </div>
            <form id="noteForm">
                <div class="modal-body">
                    <input type="hidden" id="noteId" value="">
                    <div class="form-group">
                        <label for="f_title">Title <span style="color:#c0392b">*</span></label>
                        <input type="text" id="f_title" name="title" maxlength="180" placeholder="e.g. Audit Sampling Key Concepts" required>
                        <div class="field-error" id="err_title"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="f_subject">Subject</label>
                            <select id="f_subject" name="subject_id" onchange="filterTopicOptions()">
                                <option value="">— None —</option>
                                @foreach($subjects as $subj)
                                    <option value="{{ $subj->id }}">{{ $subj->code }} — {{ $subj->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="f_topic">Topic</label>
                            <select id="f_topic" name="topic_id">
                                <option value="">— None —</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="f_tags">Topic Tags</label>
                        <input type="text" id="f_tags" name="tags" maxlength="255" placeholder="Comma separated, e.g. Sampling, Risk, PSA 530">
                        <div class="form-hint">Used for the "Topics" count and search.</div>
                    </div>
                    <div class="form-group">
                        <label for="f_content">Content</label>
                        <textarea id="f_content" name="content" placeholder="Write your note here..."></textarea>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn-secondary" onclick="closeModal('noteModal')">Cancel</button>
                    <button type="submit" class="btn-primary" id="noteSaveBtn">Save Note</button>
                </div>
            </form>
        </div>
    </div>

    <!-- VIEW MODAL -->
    <div class="modal-overlay" id="viewModal">
        <div class="modal">
            <div class="modal-head">
                <h3 id="viewTitle">Note</h3>
                <button class="modal-close" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="view-meta" id="viewMeta"></div>
                <div class="view-content" id="viewContent"></div>
                <div class="view-tags" id="viewTags"></div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-secondary" onclick="closeModal('viewModal')">Close</button>
                <button type="button" class="btn-primary" id="viewEditBtn">Edit Note</button>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"><i class="fas fa-circle-check"></i> <span id="toastMsg"></span></div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const TOPICS = @json($topics);
        const ROUTES = {
            store:   "{{ route('review-notes.store') }}",
            base:    "{{ url('review-notes') }}",
        };

        document.addEventListener('DOMContentLoaded', function () {
            // Fade-in animation
            document.querySelectorAll('.card, .stat-card').forEach((el, index) => {
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
                document.addEventListener('click', () => profileDropdown.classList.remove('active'));
                profileDropdown.addEventListener('click', (e) => e.stopPropagation());
            }

            // Live search / filter / sort — only the table re-renders (no full
            // page reload). Filtering runs server-side via AJAX so it spans all
            // pages, not just the visible rows (same pattern as the Test Bank).
            setupLiveTable();

            // Note form submit
            document.getElementById('noteForm').addEventListener('submit', saveNote);

            // Close modal on overlay click
            document.querySelectorAll('.modal-overlay').forEach(ov => {
                ov.addEventListener('click', function (e) {
                    if (e.target === ov) closeModal(ov.id);
                });
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
            });
        });

        // ── Toast ──────────────────────────────────────────────────────────
        let toastTimer;
        function toast(msg, type = 'success') {
            const t = document.getElementById('toast');
            document.getElementById('toastMsg').textContent = msg;
            t.className = 'toast ' + type;
            const icon = t.querySelector('i');
            icon.className = type === 'success' ? 'fas fa-circle-check' : 'fas fa-circle-exclamation';
            void t.offsetWidth;
            t.classList.add('show');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => t.classList.remove('show'), 2600);
        }

        // ── Live table (AJAX search / filter / sort / pagination) ──────────
        function setupLiveTable() {
            const form      = document.getElementById('filterForm');
            const search    = document.getElementById('notesSearchInput');
            const container = document.getElementById('notesTableContainer');
            if (!form || !search || !container) return;

            let reqToken = 0;

            // Build the request URL from the current filter form state.
            function filterUrl() {
                const params = new URLSearchParams(new FormData(form));
                return form.action + '?' + params.toString();
            }

            // Fetch the filtered table and swap it into the container.
            async function loadTable(url) {
                const token = ++reqToken;
                container.style.opacity = '.5';
                container.style.pointerEvents = 'none';
                try {
                    const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const html = await res.text();
                    if (token !== reqToken) return;           // a newer request already won
                    container.innerHTML = html;
                    window.history.replaceState({}, '', url);
                } catch (e) {
                    window.location = url;                     // network hiccup → normal nav
                    return;
                } finally {
                    if (token === reqToken) { container.style.opacity = ''; container.style.pointerEvents = ''; }
                }
            }
            window.reloadNotesTable = () => loadTable(filterUrl());

            // Debounced live search as the user types.
            let timer;
            search.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => loadTable(filterUrl()), 250);
            });

            // Dropdown filters update the table immediately.
            form.querySelectorAll('select').forEach(sel => {
                sel.addEventListener('change', () => loadTable(filterUrl()));
            });

            // Enter inside the search box → filter (never a full reload).
            form.addEventListener('submit', (e) => { e.preventDefault(); loadTable(filterUrl()); });

            // Pagination + sort links inside the swapped table → AJAX them too.
            container.addEventListener('click', (e) => {
                const link = e.target.closest('a[href]');
                if (link && link.href.includes('review-notes') && !link.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    loadTable(link.href);
                }
            });
        }

        // ── Modal helpers ──────────────────────────────────────────────────
        function openModal(id) { document.getElementById(id).classList.add('open'); }
        function closeModal(id) { document.getElementById(id).classList.remove('open'); }

        function filterTopicOptions(selectedTopicId = null) {
            const subjectId = document.getElementById('f_subject').value;
            const topicSel = document.getElementById('f_topic');
            topicSel.innerHTML = '<option value="">— None —</option>';
            TOPICS.filter(t => String(t.subject_id) === String(subjectId)).forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.name;
                if (selectedTopicId && String(selectedTopicId) === String(t.id)) opt.selected = true;
                topicSel.appendChild(opt);
            });
        }

        function clearErrors() {
            document.querySelectorAll('.field-error').forEach(e => { e.style.display = 'none'; e.textContent = ''; });
        }

        // ── Create / Edit ──────────────────────────────────────────────────
        function openCreateModal() {
            clearErrors();
            document.getElementById('noteModalTitle').textContent = 'New Note';
            document.getElementById('noteForm').reset();
            document.getElementById('noteId').value = '';
            filterTopicOptions();
            openModal('noteModal');
            setTimeout(() => document.getElementById('f_title').focus(), 100);
        }

        async function editNote(id) {
            try {
                const res = await fetch(`${ROUTES.base}/${id}`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (!data.ok) throw new Error();
                const n = data.note;
                clearErrors();
                document.getElementById('noteModalTitle').textContent = 'Edit Note';
                document.getElementById('noteId').value = n.id;
                document.getElementById('f_title').value = n.title || '';
                document.getElementById('f_subject').value = n.subject_id || '';
                filterTopicOptions(n.topic_id);
                document.getElementById('f_tags').value = n.tags || '';
                document.getElementById('f_content').value = n.content || '';
                closeModal('viewModal');
                openModal('noteModal');
            } catch (e) {
                toast('Could not load the note.', 'error');
            }
        }

        async function saveNote(e) {
            e.preventDefault();
            clearErrors();
            const id = document.getElementById('noteId').value;
            const btn = document.getElementById('noteSaveBtn');
            const isEdit = !!id;
            const url = isEdit ? `${ROUTES.base}/${id}` : ROUTES.store;

            const payload = {
                title: document.getElementById('f_title').value,
                subject_id: document.getElementById('f_subject').value || null,
                topic_id: document.getElementById('f_topic').value || null,
                tags: document.getElementById('f_tags').value,
                content: document.getElementById('f_content').value,
            };
            if (isEdit) payload._method = 'PUT';

            btn.disabled = true;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                    },
                    body: JSON.stringify(payload),
                });

                if (res.status === 422) {
                    const data = await res.json();
                    Object.entries(data.errors || {}).forEach(([field, msgs]) => {
                        const el = document.getElementById('err_' + field);
                        if (el) { el.textContent = msgs[0]; el.style.display = 'block'; }
                    });
                    btn.disabled = false;
                    return;
                }
                if (!res.ok) throw new Error();

                toast(isEdit ? 'Note updated.' : 'Note created.');
                setTimeout(() => location.reload(), 500);
            } catch (err) {
                btn.disabled = false;
                toast('Something went wrong. Please try again.', 'error');
            }
        }

        // ── View (counts as a review) ──────────────────────────────────────
        async function viewNote(id) {
            try {
                const res = await fetch(`${ROUTES.base}/${id}?read=1`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                });
                const data = await res.json();
                if (!data.ok) throw new Error();
                const n = data.note;

                document.getElementById('viewTitle').textContent = n.title;
                const meta = [];
                if (n.subject_code) meta.push(`<span class="meta-pill">${n.subject_code}</span>`);
                if (n.topic_name) meta.push(`<span class="meta-pill">${n.topic_name}</span>`);
                meta.push(`<span class="meta-pill">Created ${n.created_on}</span>`);
                meta.push(`<span class="meta-pill">Reviewed ${n.review_count}×</span>`);
                document.getElementById('viewMeta').innerHTML = meta.join('');
                document.getElementById('viewContent').textContent = n.content || 'This note has no content yet.';

                const tagsEl = document.getElementById('viewTags');
                tagsEl.innerHTML = (n.tag_list || []).map(t => `<span>${t}</span>`).join('');

                document.getElementById('viewEditBtn').onclick = () => editNote(n.id);
                openModal('viewModal');
            } catch (e) {
                toast('Could not open the note.', 'error');
            }
        }

        // ── Favorite ───────────────────────────────────────────────────────
        async function toggleFavorite(id, el) {
            try {
                const res = await fetch(`${ROUTES.base}/${id}/favorite`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                });
                const data = await res.json();
                if (!data.ok) throw new Error();
                el.classList.toggle('fav-on', data.is_favorite);
                toast(data.is_favorite ? 'Added to favorites.' : 'Removed from favorites.');
            } catch (e) {
                toast('Could not update favorite.', 'error');
            }
        }

        // ── Delete ─────────────────────────────────────────────────────────
        async function deleteNote(id, title) {
            if (!confirm(`Delete "${title}"? This cannot be undone.`)) return;
            try {
                const res = await fetch(`${ROUTES.base}/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                    },
                    body: JSON.stringify({ _method: 'DELETE' }),
                });
                if (!res.ok) throw new Error();
                toast('Note deleted.');
                setTimeout(() => location.reload(), 500);
            } catch (e) {
                toast('Could not delete the note.', 'error');
            }
        }

        const style = document.createElement('style');
        style.textContent = `@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }`;
        document.head.appendChild(style);
    </script>
</body>
</html>
