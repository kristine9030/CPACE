<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects - CPACE CPA Reviewer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #7B1D1D;
            --primary-hover: #6a1818;
            --primary-light: #f5e8e8;
            --primary-mid: #c0392b;
            --accent-red: #c0392b;
            --sidebar-bg: linear-gradient(180deg, #a12626 0%, #7B1D1D 34%, #3d0c0c 74%, #1a0a0a 100%);
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

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f5f7;
            color: #333;
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
            width: 44px; height: 44px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: white; flex-shrink: 0;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .logo-text strong { display: block; font-size: 15px; font-weight: 700; letter-spacing: 0.5px; }
        .logo-text small  { font-size: 11px; opacity: 0.8; }
        .sidebar.collapsed .logo-text { display: none; }

        .sidebar-nav { list-style: none; flex: 1; padding: 12px 0; }

        .sidebar-nav li a {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 22px;
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            font-size: 13px;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar-nav li a:hover { color: white; background: rgba(255,255,255,0.1); }
        .sidebar-nav li a.active {
            color: white; background: rgba(255,255,255,0.18);
            border-left-color: white; font-weight: 500;
        }
        .sidebar-nav li a i { width: 18px; text-align: center; font-size: 15px; flex-shrink: 0; }

        .sidebar.collapsed .sidebar-nav li a { padding: 11px 0; justify-content: center; gap: 0; }
        .sidebar.collapsed .sidebar-nav li a span { display: none; }

        /* Challenge Box */
        .sidebar-challenge {
            padding: 14px 16px;
            margin: 8px 12px 4px;
        }
        .sidebar.collapsed .sidebar-challenge { display: none; }

        .challenge-box {
            background: rgba(0,0,0,0.25);
            border-radius: 10px;
            padding: 14px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .challenge-box .ch-label {
            font-size: 11px; color: rgba(255,255,255,0.75); margin-bottom: 3px;
        }
        .challenge-box .ch-title {
            font-size: 13px; font-weight: 700; color: white; margin-bottom: 12px;
        }
        .challenge-box a {
            display: inline-flex; align-items: center; gap: 6px;
            background: white; color: var(--primary);
            padding: 8px 12px;
            border-radius: 6px; font-size: 11px; font-weight: 700;
            text-decoration: none; transition: all 0.2s;
        }
        .challenge-box a:hover { background: #f5e8e8; }
        .challenge-icon {
            position: absolute; right: 10px; bottom: 8px;
            font-size: 28px; opacity: 0.3; color: white;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            border-top: 1px solid rgba(255,255,255,0.12);
            padding: 16px 20px;
        }
        .user-profile { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .avatar-sm {
            width: 38px; height: 38px;
            background: var(--accent-red);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 13px; color: white; flex-shrink: 0;
        }
        .user-details { flex: 1; min-width: 0; }
        .user-details .uname { display: block; font-size: 13px; font-weight: 600; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-details .urole { display: block; font-size: 11px; color: rgba(255,255,255,0.65); }
        .sidebar.collapsed .user-details, .sidebar.collapsed .chevron-icon { display: none; }

        /* ─── MAIN ─── */
        .main-content {
            margin-left: 220px;
            padding: 30px 40px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        .sidebar.collapsed ~ .main-content { margin-left: 70px; }

        /* ─── TOP BAR ─── */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            gap: 20px;
        }
        .top-bar-left { display: flex; align-items: center; gap: 14px; }
        .toggle-btn {
            width: 38px; height: 38px;
            border: 1px solid #e0e0e0; background: white; border-radius: 8px;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            color: var(--primary); font-size: 16px; transition: background 0.2s;
        }
        .toggle-btn:hover { background: #f0f0f0; }
        .top-bar-right { display: flex; align-items: center; gap: 14px; }

        .search-wrap { position: relative; }
        .search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #aaa; font-size: 14px; }
        .search-wrap input {
            width: 280px; padding: 10px 14px 10px 36px;
            border: 1px solid #e0e0e0; border-radius: 24px;
            font-size: 13px; font-family: 'Poppins', sans-serif;
            background: white; color: #555; outline: none;
        }
        .search-wrap input:focus { border-color: var(--primary); }
        .search-wrap input::placeholder { color: #bbb; }

        .notif-btn {
            position: relative; width: 40px; height: 40px;
            border: none; background: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px; color: #555; cursor: pointer;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }
        .notif-btn:hover { background: #f0f0f0; }
        .badge {
            position: absolute; top: -3px; right: -3px;
            width: 18px; height: 18px; background: var(--accent-red);
            color: white; border-radius: 50%; font-size: 10px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        .profile-avatar {
            width: 40px; height: 40px; background: var(--primary);
            border-radius: 10px; border: none; color: white;
            font-weight: 700; font-size: 14px; cursor: pointer;
            font-family: 'Poppins', sans-serif; transition: background 0.2s;
        }
        .profile-avatar:hover { background: var(--primary-hover); }

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
        .dropdown-menu a i, .dropdown-menu button i { color: var(--primary); width: 16px; text-align: center; }
        .dropdown-menu .logout-btn { color: #e53e3e; }
        .dropdown-menu .logout-btn i { color: #e53e3e; }

        /* ─── PAGE HEADER ROW ─── */
        .page-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 28px;
            gap: 20px;
        }

        .page-title {
            font-size: 30px; font-weight: 700; color: #1a1a1a;
            margin-bottom: 6px; padding-bottom: 10px;
            position: relative;
        }
        .page-title::after {
            content: '';
            position: absolute;
            left: 0; bottom: 0;
            width: 46px; height: 3px;
            border-radius: 2px;
            background: linear-gradient(90deg, #c0392b, #7B1D1D);
        }
        .page-subtitle { font-size: 14px; color: #999; }

        /* Page header illustration */
        .page-header-illus {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            position: relative;
        }
        .illus-circle-bg {
            position: absolute;
            right: -10px; top: -20px;
            width: 160px; height: 160px;
            background: radial-gradient(circle, #fde8e8 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
        }
        .illus-books {
            display: flex;
            align-items: flex-end;
            gap: 4px;
            position: relative;
            z-index: 1;
        }
        .illus-book {
            width: 36px;
            border-radius: 4px;
            display: flex; align-items: center; justify-content: center;
            font-size: 9px; font-weight: 800; color: white;
            writing-mode: vertical-rl; letter-spacing: 1px;
        }
        .illus-book.b1 { height: 110px; background: linear-gradient(180deg,#1abc9c,#16a085); }
        .illus-book.b2 { height: 90px; background: linear-gradient(180deg,#c0392b,#922b21); }
        .illus-book.b3 { height: 75px; background: linear-gradient(180deg,#27ae60,#1e8449); }
        .illus-plant { font-size: 38px; margin-bottom: 2px; position: relative; z-index:1; }
        .illus-mug { font-size: 34px; margin-bottom: 2px; position: relative; z-index:1; }

        /* ─── SUBJECT GRID ───────────────────────────────────────────────────
           Each subject is drawn as a manila-style folder: a tab riding above a
           body whose top-left corner is square, so the two read as one shape. */
        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            column-gap: 28px;
            row-gap: 14px;         /* each card adds its own tab clearance on top */
            margin-top: 26px;
        }

        /* The back panel of the folder — maroon, with the tab raised off its
           top-left corner. Same two-piece construction as the folder icons in
           Review Notes: dark back panel, lighter front flap over the bottom. */
        /* Proportions traced from the notes icon: tab is 33% of the width and
           sits ~15% of the folder's height above the body, and the front flap
           covers the bottom ~62%. */
        /* Back panel. Flat fill like the icon — the depth comes from stacked
           shadows and the flap sitting in front, not from gloss. */
        .subject-card {
            position: relative;
            margin-top: 26px;                 /* room for the tab */
            border-radius: 0 12px 12px 12px;  /* square where the tab joins */
            background: #7B1D1D;
            display: flex;
            flex-direction: column;
            box-shadow:
                0 1px 2px rgba(64,8,8,0.30),
                0 5px 12px rgba(64,8,8,0.20),
                0 14px 28px rgba(64,8,8,0.13);
            transition: transform 0.22s, box-shadow 0.22s;
        }
        .subject-card:hover {
            transform: translateY(-5px);
            box-shadow:
                0 2px 4px rgba(64,8,8,0.30),
                0 10px 22px rgba(64,8,8,0.26),
                0 24px 44px rgba(64,8,8,0.20);
        }

        /* Raised tab — a shade lighter, like a separate leaf behind the body,
           with a true triangular wedge for the diagonal right edge. */
        .folder-tab {
            position: absolute;
            top: -26px; left: 0;
            width: 33%; min-width: 84px; max-width: 128px;
            height: 27px;
            border-radius: 9px 0 0 0;
            background: #8d2626;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.14);
        }
        .folder-tab::after {
            content: '';
            position: absolute;
            left: 100%; top: 0; bottom: 0;
            width: 27px;
            background: #8d2626;
            clip-path: polygon(0 0, 100% 100%, 0 100%);
        }

        /* Header sits directly on the back panel, so its text is white. */
        .folder-head {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px 20px 20px;
        }

        /* Front flap — plain white against the maroon back panel. It physically
           sits in front, so it casts a shadow up onto the back panel. The white
           highlight the fold used to carry is gone — on a white flap it did
           nothing, and the maroon/white contrast defines the edge on its own. */
        .folder-front {
            background: #fff;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 -5px 12px rgba(58,6,6,0.34);
            padding: 18px 20px 20px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        /* White disc — the subject artwork is maroon-and-gold, so it needs a
           light ground to stay legible against the maroon back panel. */
        .subject-icon-circle {
            width: 46px; height: 46px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.20);
        }
        .subject-icon-circle img {
            width: 29px; height: 29px;
            object-fit: contain;
        }

        .subject-info { flex: 1; min-width: 0; }
        .subject-abbr {
            font-size: 16px; font-weight: 800; color: #fff;
            margin-bottom: 1px; text-shadow: 0 1px 2px rgba(0,0,0,0.20);
        }
        .subject-full {
            font-size: 11px; color: rgba(255,255,255,0.85); line-height: 1.4;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }

        .overall-progress {
            display: flex; align-items: center; gap: 14px;
            padding-bottom: 16px; margin-bottom: 16px;
            border-bottom: 1px solid #f0f0f0;
        }
        .ring-wrap { position: relative; width: 44px; height: 44px; flex-shrink: 0; }
        .ring-svg { width: 44px; height: 44px; transform: rotate(-90deg); }
        .ring-bg { fill: none; stroke: #ededed; stroke-width: 6; }
        .ring-fill { fill: none; stroke-width: 6; stroke-linecap: round; transition: stroke-dashoffset 0.6s ease; }
        .ring-pct {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 800; color: #1a1a1a;
        }
        .overall-info { flex: 1; min-width: 0; }
        .overall-title {
            font-size: 10px; font-weight: 700; color: var(--primary);
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .overall-sub { font-size: 10.5px; color: #999; margin-top: 4px; line-height: 1.45; }

        /* Blush panel with hairline dividers, instead of two flat grey rules. */
        /* White card floating on the flap — one more layer of depth. */
        .subject-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            padding: 13px 8px;
            background: #fafafa;
            border: 1px solid #f0f0f0;
            border-radius: 10px;
            margin-bottom: 16px;
            text-align: center;
        }
        .subject-stats > div + div { border-left: 1px solid #ececec; }
        .stat-num {
            display: block;
            font-size: 17px; font-weight: 700; color: var(--primary);
            margin-bottom: 3px;
        }
        /* Brighter than the maroon siblings so the warning still stands out. */
        .stat-num.weak { color: #e03131; }
        .stat-lbl {
            font-size: 9.5px; color: #999; text-transform: uppercase; letter-spacing: 0.4px;
        }

        .subject-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 16px;
            border: none;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            transition: background 0.2s, box-shadow 0.2s;
            margin-top: auto;
            /* Solid maroon — the pale 12% wash was the flattest thing on the card. */
            background: linear-gradient(135deg, #a52f26 0%, #7B1D1D 100%);
            color: #fff;
            box-shadow: 0 2px 5px rgba(90,20,20,.30);
        }
        .subject-btn:hover {
            background: linear-gradient(135deg, #8e2620 0%, #5c1414 100%);
            box-shadow: 0 5px 14px rgba(90,20,20,.42);
        }
        .subject-btn i { transition: transform 0.2s; }
        .subject-card:hover .subject-btn i { transform: translateX(3px); }

        @media (max-width: 1200px) {
            .subjects-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .main-content { margin-left: 70px; padding: 20px; }
            .subjects-grid { grid-template-columns: 1fr; }
            .search-wrap input { width: 160px; }
        }

        /* ─── RESPONSIVE (added) ─── */
        @media (max-width: 768px) {
            .main-content { padding: 20px 16px; }
            .top-bar { flex-direction: column; align-items: flex-start; gap: 12px; }
            .top-bar-right { width: 100%; flex-wrap: wrap; }
            .search-wrap { flex: 1; }
            .search-wrap input { width: 100%; }
            .page-title { font-size: 22px; }
            .page-header-illus { display: none; }
        }

        @media (max-width: 480px) {
            .main-content { padding: 16px 12px; }
            .subjects-grid { grid-template-columns: 1fr; column-gap: 16px; row-gap: 10px; }
            .folder-head { padding: 16px 17px 18px; gap: 13px; }
            .folder-front { padding: 16px 17px 18px; }
            .subject-icon-circle { width: 44px; height: 44px; }
            .subject-icon-circle img { width: 28px; height: 28px; }
            .subject-abbr { font-size: 15px; }
            .page-title { font-size: 20px; }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .anim { animation: fadeUp 0.4s ease both; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
@include('partials.sidebar', ['active' => 'subjects'])
@include('partials.student-bottom-nav', ['active' => 'subjects'])
@include('partials.student-mobile-header')

<!-- MAIN -->
<main class="main-content">

    <!-- TOP BAR -->
    <div class="top-bar anim" style="animation-delay:0s">
        <div class="top-bar-left">
            <div>
                <div class="page-title">Subjects</div>
                <div class="page-subtitle">Review by subject area and strengthen your knowledge.</div>
            </div>
        </div>
        <div class="top-bar-right">
            <div class="search-wrap gs-wrap">
                <i class="fas fa-search"></i>
                <input type="text" data-gs="true" placeholder="Search topics, questions...">
            </div>
            <a class="notif-btn" href="{{ route('messages.index') }}" title="Messages" aria-label="Messages">
                <i class="fas fa-comment-dots"></i>
                @if($unreadMessages > 0)<span class="badge">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>@endif
            </a>
            <a class="notif-btn" href="{{ route('notifications.index') }}" title="Notifications" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                @if($unreadNotifications > 0)<span class="badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>@endif
            </a>
            <div class="header-dropdown-wrap">
                <button class="profile-avatar" id="profileBtn">@include('partials.avatar-content')</button>
                <div class="dropdown-menu" id="profileDropdown">
                    <a href="#"><i class="fas fa-user"></i> Profile Settings</a>
                    <a href="#"><i class="fas fa-chart-line"></i> My Progress</a>
                    <a href="#"><i class="fas fa-question-circle"></i> Help &amp; Support</a>
                    <form method="POST" action="{{ route('logout') }}"
                          data-confirm="You will be signed out of CPACE and returned to the login page."
                          data-confirm-title="Log out of CPACE?"
                          data-confirm-ok="Yes, log me out"
                          data-confirm-icon="question" style="margin:0;padding:0;">
                        @csrf
                        <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SUBJECTS GRID -->
    <div class="subjects-grid anim" style="animation-delay:0.12s">

        @forelse($subjects as $subject)
            {{-- Brand maroon, not subjects.color — those stored values are a
                 mixed palette that clashed with the rest of the UI. --}}
            @php $color = '#7B1D1D'; @endphp
            <div class="subject-card">
                <span class="folder-tab" aria-hidden="true"></span>
                <div class="folder-head">
                    <div class="subject-icon-circle">
                        <img src="{{ asset('images/' . $subject->code . '.png') }}" alt="{{ $subject->code }}">
                    </div>
                    <div class="subject-info">
                        <div class="subject-abbr">{{ $subject->code }}</div>
                        <div class="subject-full">{{ $subject->name }}</div>
                    </div>
                </div>
                @php
                    $circumference = round(2 * M_PI * 26);
                    $hasAttempts = $subject->overall_accuracy !== null;
                    $ringColor = $hasAttempts
                        ? ($subject->overall_accuracy >= $subject->passing_threshold ? '#059669' : '#dc2626')
                        : '#e5e5e5';
                    $ringOffset = $hasAttempts
                        ? round($circumference * (1 - $subject->overall_accuracy / 100))
                        : $circumference;
                @endphp
                <div class="folder-front">
                <div class="overall-progress">
                    <div class="ring-wrap">
                        <svg viewBox="0 0 60 60" class="ring-svg">
                            <circle class="ring-bg" cx="30" cy="30" r="26"></circle>
                            <circle class="ring-fill" cx="30" cy="30" r="26"
                                    style="stroke:{{ $ringColor }}; stroke-dasharray:{{ $circumference }}; stroke-dashoffset:{{ $ringOffset }};"></circle>
                        </svg>
                        <div class="ring-pct">{{ $hasAttempts ? $subject->overall_accuracy.'%' : '—' }}</div>
                    </div>
                    <div class="overall-info">
                        <div class="overall-title">Overall Progress</div>
                        <div class="overall-sub">
                            @if($hasAttempts)
                                {{ $subject->overall_correct }}/{{ $subject->overall_attempts }} correct across all topics
                            @else
                                Not attempted yet
                            @endif
                        </div>
                    </div>
                </div>

                <div class="subject-stats">
                    <div>
                        <span class="stat-num">{{ $subject->topic_count }}</span>
                        <span class="stat-lbl">Topics</span>
                    </div>
                    <div>
                        <span class="stat-num">{{ $subject->question_count }}</span>
                        <span class="stat-lbl">Questions</span>
                    </div>
                    <div>
                        <span class="stat-num weak">{{ $subject->weak_count }}</span>
                        <span class="stat-lbl">Weak Topics</span>
                    </div>
                </div>
                <a href="{{ route('subjects.show', $subject->id) }}" class="subject-btn">Review Subject <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        @empty
            <div style="grid-column:1/-1; text-align:center; padding:60px 20px; color:#aaa;">
                <i class="fas fa-book-open" style="font-size:38px; color:#e5d5d5; display:block; margin-bottom:14px;"></i>
                No subjects available yet.
            </div>
        @endforelse

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
    }

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
    @include('partials.global-search')

    @include('partials.alerts')
</body>
</html>
