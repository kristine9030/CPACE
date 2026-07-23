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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f5f7;
            color: var(--gray-900);
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
            width: 44px;
            height: 44px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            flex-shrink: 0;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .logo-text { line-height: 1.25; }
        .logo-text strong { display: block; font-size: 15px; font-weight: 700; letter-spacing: 0.5px; }
        .logo-text small  { font-size: 11px; opacity: 0.8; font-weight: 400; }

        .sidebar.collapsed .logo-text { display: none; }

        .sidebar-nav {
            list-style: none;
            flex: 1;
            padding: 12px 0;
        }

        .sidebar-nav li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 22px;
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            font-size: 13px;
            font-weight: 400;
            transition: all 0.2s;
            white-space: nowrap;
            border-left: 3px solid transparent;
        }

        .sidebar-nav li a:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }

        .sidebar-nav li a.active {
            color: white;
            background: rgba(255,255,255,0.18);
            border-left-color: white;
            font-weight: 500;
        }

        .sidebar-nav li a i {
            width: 18px;
            text-align: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .sidebar.collapsed .sidebar-nav li a {
            padding: 11px 0;
            justify-content: center;
            gap: 0;
        }
        .sidebar.collapsed .sidebar-nav li a span { display: none; }
        .sidebar.collapsed .sidebar-logo .logo-text { display: none; }

        .sidebar-footer {
            border-top: 1px solid rgba(255,255,255,0.12);
            padding: 16px 20px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .avatar-sm {
            width: 38px;
            height: 38px;
            background: var(--accent-red);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            color: white;
            flex-shrink: 0;
        }

        .user-details { flex: 1; min-width: 0; }
        .user-details .uname { display: block; font-size: 13px; font-weight: 600; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-details .urole { display: block; font-size: 11px; color: rgba(255,255,255,0.65); }

        .sidebar.collapsed .user-details,
        .sidebar.collapsed .chevron-icon { display: none; }

        /* ─── MAIN CONTENT ─── */
        .main-content {
            margin-left: 220px;
            padding: 30px 40px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .sidebar.collapsed ~ .main-content { margin-left: 70px; }

        /* ─── HEADER ─── */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            gap: 20px;
        }

        .page-header-left { display: flex; align-items: center; gap: 14px; }

        .toggle-btn {
            width: 38px; height: 38px;
            border: 1px solid var(--gray-300);
            background: white;
            border-radius: 8px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: var(--primary);
            font-size: 16px;
            transition: background 0.2s;
        }
        .toggle-btn:hover { background: var(--gray-200); }

        .page-title { font-size: 28px; font-weight: 700; color: var(--gray-900); line-height: 1.2; }
        .page-subtitle { font-size: 13px; color: var(--gray-500); margin-top: 2px; }

        .page-header-right { display: flex; align-items: center; gap: 14px; }

        .search-wrap {
            position: relative;
        }
        .search-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
            font-size: 14px;
        }
        .search-wrap input {
            width: 300px;
            padding: 10px 14px 10px 36px;
            border: 1px solid var(--gray-300);
            border-radius: 24px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            background: white;
            color: var(--gray-700);
            outline: none;
            transition: border-color 0.2s;
        }
        .search-wrap input:focus { border-color: var(--primary); }
        .search-wrap input::placeholder { color: #bbb; }

        .notif-btn {
            position: relative;
            width: 40px; height: 40px;
            border: none;
            background: white;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px;
            color: var(--gray-700);
            cursor: pointer;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            transition: background 0.2s;
        }
        .notif-btn:hover { background: var(--gray-200); }

        .badge {
            position: absolute;
            top: -3px; right: -3px;
            width: 18px; height: 18px;
            background: var(--accent-red);
            color: white;
            border-radius: 50%;
            font-size: 10px;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }

        .profile-avatar {
            width: 40px; height: 40px;
            background: var(--primary);
            border-radius: 10px;
            border: none;
            color: white;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: background 0.2s;
        }
        .profile-avatar:hover { background: var(--primary-hover); }

        .header-dropdown-wrap { position: relative; }

        .dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: white;
            border: 1px solid var(--gray-300);
            border-radius: 10px;
            min-width: 185px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
            display: none;
            z-index: 2000;
        }
        .dropdown-menu.active { display: block; }
        .dropdown-menu a, .dropdown-menu button {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 16px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            color: var(--gray-900);
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: background 0.2s;
            border-bottom: 1px solid #f5f5f5;
        }
        .dropdown-menu a:last-child,
        .dropdown-menu form:last-child button { border-bottom: none; }
        .dropdown-menu a:hover,
        .dropdown-menu button:hover { background: var(--gray-100); }
        .dropdown-menu a i, .dropdown-menu button i { color: var(--primary); width: 16px; text-align: center; }
        .dropdown-menu .logout-btn { color: #e53e3e; }
        .dropdown-menu .logout-btn i { color: #e53e3e; }

        /* countdown + calendar sit on one row */
        .overview-top {
            display: grid;
            grid-template-columns: 1fr 240px;
            gap: 18px;
            margin-bottom: 22px;
            align-items: stretch;
        }

        /* ─── EXAM COUNTDOWN (flip-clock style) ─── */
        .exam-hero {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            padding: 30px 40px;
            background: linear-gradient(115deg, #6a1a1a 0%, #3a1010 46%, #130707 100%);
            border: 1px solid rgba(255,255,255,0.06);
            box-shadow: 0 14px 34px rgba(0,0,0,0.40);
            color: #fff;
            height: 100%;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 30px;
            align-items: center;
        }
        .exam-hero-left { position: relative; z-index: 1; }
        .exam-hero-greeting { font-size: 32px; font-weight: 800; color: #fff; line-height: 1.1; }
        .exam-hero-subtitle { font-size: 16px; font-weight: 500; color: rgba(255,255,255,0.78); margin-top: 5px; }

        /* abstract decorative shapes (pure background overlay, out of the grid flow) */
        .exam-hero-shapes { position: absolute; inset: 0; pointer-events: none; z-index: 0; }
        .exam-hero-shapes span { position: absolute; pointer-events: none; z-index: 0; }
        .exam-hero-shapes span:nth-child(1) { top: -34px; left: 28%; width: 120px; height: 120px; border: 2px solid rgba(255,255,255,0.06); border-radius: 50%; }
        .exam-hero-shapes span:nth-child(2) { bottom: -30px; left: 10%; width: 88px; height: 88px; border: 2px solid rgba(255,215,106,0.14); border-radius: 24px; transform: rotate(30deg); }
        .exam-hero-shapes span:nth-child(3) { top: 24px; left: -16px; width: 0; height: 0; border-left: 22px solid transparent; border-right: 22px solid transparent; border-bottom: 38px solid rgba(255,255,255,0.05); transform: rotate(-18deg); }
        .exam-hero-shapes span:nth-child(4) { bottom: 18px; left: 44%; width: 56px; height: 56px; background: rgba(192,57,43,0.30); border-radius: 15px; transform: rotate(20deg); }
        .exam-hero-shapes span:nth-child(5) { top: 30px; right: 3%; width: 66px; height: 66px; border: 1.5px solid rgba(255,255,255,0.07); border-radius: 50%; }

        /* calendar-style "today" card, same row as the countdown */
        .today-card {
            background: #fff;
            border: 1px solid #eef0f2;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 16px 44px rgba(0,0,0,0.10);
            display: flex;
            flex-direction: column;
            text-align: center;
        }
        .cal-top {
            background: linear-gradient(135deg, #c0392b, #7B1D1D);
            color: #fff;
            font-size: 13.5px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 15px 12px 12px;
            position: relative;
        }
        .cal-rings::before,
        .cal-rings::after {
            content: '';
            position: absolute;
            top: 5px;
            width: 7px; height: 12px;
            border-radius: 4px;
            background: rgba(255,255,255,0.85);
            box-shadow: inset 0 0 0 1px rgba(0,0,0,0.08);
        }
        .cal-rings::before { left: 32%; }
        .cal-rings::after  { right: 32%; }
        .cal-body {
            flex: 1;
            display: flex; flex-direction: column;
            padding: 20px 20px 22px;
        }
        .cal-hero {
            flex: 1;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
        }
        .cal-daynum { font-size: 84px; font-weight: 800; color: var(--gray-900); line-height: 0.92; }
        .cal-weekday { font-size: 16px; font-weight: 700; color: var(--accent-red); letter-spacing: 0.4px; margin-top: 2px; }
        .cal-clock {
            margin-top: 16px; padding-top: 16px;
            border-top: 1px dashed #e6e6e6;
            font-size: 26px; font-weight: 700; color: var(--gray-700);
            font-variant-numeric: tabular-nums; letter-spacing: 1px;
        }
        .cal-clock .ampm { font-size: 13px; font-weight: 600; color: var(--accent-red); margin-left: 5px; }
        .exam-hero-target { font-size: 16px; font-weight: 600; color: #e0a94b; margin-top: 22px; }
        .exam-hero-target strong { color: #ffb020; font-weight: 700; }

        /* flip-clock countdown */
        .countdown-grid { display: flex; align-items: flex-start; gap: 15px; position: relative; z-index: 1; }
        .cd-unit { display: flex; flex-direction: column; align-items: center; gap: 12px; }
        .cd-flip {
            position: relative;
            width: 98px; height: 96px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 13px;
            background: linear-gradient(180deg, #343434 0%, #1c1c1c 49%, #050505 50%, #191919 100%);
            box-shadow: 0 8px 18px rgba(0,0,0,0.48), inset 0 1px 0 rgba(255,255,255,0.07);
            font-size: 62px; font-weight: 800; color: #f5f5f5;
            font-variant-numeric: tabular-nums; letter-spacing: 1px;
        }
        /* center flip seam */
        .cd-flip::before {
            content: ''; position: absolute; left: 0; right: 0; top: 50%;
            height: 2px; background: rgba(0,0,0,0.6); transform: translateY(-1px); z-index: 2;
        }
        /* hinge dots on both sides */
        .cd-flip::after {
            content: ''; position: absolute; top: 50%; left: -3px;
            width: 7px; height: 7px; border-radius: 50%;
            background: rgba(0,0,0,0.5); transform: translateY(-50%);
            box-shadow: 98px 0 0 rgba(0,0,0,0.5);
        }
        .cd-lbl { font-size: 12px; font-weight: 600; letter-spacing: 2.5px; text-transform: uppercase; color: rgba(255,255,255,0.45); }
        .cd-sep { align-self: flex-start; margin-top: 38px; display: flex; flex-direction: column; gap: 9px; }
        .cd-sep span { width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,0.32); }

        .exam-hero-empty { font-size: 13px; color: rgba(255,255,255,0.72); margin: 10px 0 16px; max-width: 400px; line-height: 1.6; }
        .exam-hero-btn {
            display: inline-flex; align-items: center; gap: 8px;
            background: #ffca6a; color: #4a1010; text-decoration: none;
            padding: 11px 22px; border-radius: 10px; font-size: 13px; font-weight: 700;
            transition: transform 0.2s, filter 0.2s;
        }
        .exam-hero-btn:hover { filter: brightness(1.06); transform: translateY(-1px); }

        /* tighter 2-column range: shrink the flip cards so they never overflow */
        @media (max-width: 1350px) {
            .cd-flip { width: 80px; height: 80px; font-size: 50px; }
            .cd-flip::after { box-shadow: 80px 0 0 rgba(0,0,0,0.5); }
            .cd-sep { margin-top: 31px; }
            .exam-hero-greeting { font-size: 28px; }
        }
        /* stack the row → countdown gets full width, go big again */
        @media (max-width: 1100px) {
            .overview-top { grid-template-columns: 1fr; }
            .cd-flip { width: 98px; height: 96px; font-size: 62px; }
            .cd-flip::after { box-shadow: 98px 0 0 rgba(0,0,0,0.5); }
            .cd-sep { margin-top: 38px; }
            .exam-hero-greeting { font-size: 32px; }
        }
        @media (max-width: 600px) {
            .exam-hero { grid-template-columns: 1fr; padding: 22px; }
            .countdown-grid { gap: 9px; }
            .cd-flip { width: 62px; height: 62px; font-size: 38px; }
            .cd-flip::after { box-shadow: 62px 0 0 rgba(0,0,0,0.5); }
            .cd-sep { margin-top: 24px; gap: 8px; }
            .exam-hero-greeting { font-size: 26px; }
        }

        /* ─── WELCOME BANNER (dark red-black gradient, matches Achievements) ─── */
        .welcome-banner {
            background: linear-gradient(135deg, #1a0a0a 0%, #3d0c0c 30%, #7B1D1D 60%, #a12626 100%);
            border-radius: 16px;
            padding: 26px 34px;
            margin-bottom: 22px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 30px;
            align-items: center;
            overflow: hidden;
            position: relative;
            box-shadow: 0 8px 22px rgba(0,0,0,0.28);
        }

        /* faint blueprint grid, faded toward the text side */
        .welcome-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                repeating-linear-gradient(0deg, transparent, transparent 38px, rgba(255,255,255,0.06) 38px, rgba(255,255,255,0.06) 40px),
                repeating-linear-gradient(90deg, transparent, transparent 38px, rgba(255,255,255,0.06) 38px, rgba(255,255,255,0.06) 40px);
            mask-image: linear-gradient(to left, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 30%, transparent 55%);
            -webkit-mask-image: linear-gradient(to left, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 30%, transparent 55%);
            pointer-events: none;
            z-index: 0;
        }

        /* warm glow behind the vector */
        .welcome-banner::after {
            content: '';
            position: absolute;
            bottom: -70px;
            right: 10%;
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, rgba(255,215,106,0.10) 0%, transparent 65%);
            border-radius: 50%;
            pointer-events: none;
        }

        .welcome-content { position: relative; z-index: 1; }

        .welcome-banner h2 {
            font-size: 26px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
        }

        .welcome-banner p {
            font-size: 13px;
            color: rgba(255,255,255,0.72);
            margin-bottom: 18px;
        }

        /* floating vector accents */
        .welcome-banner .w-shape { position: absolute; pointer-events: none; z-index: 0; }
        .welcome-banner .w-shape-1 { top: -18px; right: 30%; width: 80px; height: 80px; border: 2px solid rgba(255,215,106,0.12); border-radius: 22px; transform: rotate(28deg); }
        .welcome-banner .w-shape-2 { bottom: 10px; right: 8%; width: 46px; height: 46px; background: rgba(192,57,43,0.22); border-radius: 50%; }
        .welcome-banner .w-shape-3 { top: 18px; right: 12%; width: 0; height: 0; border-left: 18px solid transparent; border-right: 18px solid transparent; border-bottom: 32px solid rgba(255,255,255,0.05); transform: rotate(18deg); }
        .welcome-banner .w-shape-4 { bottom: -20px; right: 24%; width: 110px; height: 110px; border: 1.5px solid rgba(255,255,255,0.06); border-radius: 30px; transform: rotate(42deg); }

        /* right-side study vector illustration */
        .welcome-vector {
            position: relative;
            z-index: 1;
            width: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .welcome-vector svg { width: 100%; height: auto; }

        @media (max-width: 768px) {
            .welcome-vector { display: none; }
        }

        .welcome-illustration {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 10px 20px;
            position: relative;
        }

        .illus-book {
            width: 70px; height: 90px;
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700;
            color: white;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            letter-spacing: 2px;
        }
        .illus-book.tax { background: linear-gradient(135deg, #c0392b, #962d22); }
        .illus-book.audit { background: linear-gradient(135deg, #7B1D1D, #5a1515); }
        .illus-book.far { background: linear-gradient(135deg, #8e44ad, #6c3483); }

        .illus-laptop {
            width: 120px; height: 85px;
            background: #2d3748;
            border-radius: 8px 8px 0 0;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: -2px;
            position: relative;
        }
        .illus-laptop::after {
            content: '';
            position: absolute;
            bottom: -10px; left: -15px;
            width: 150px; height: 10px;
            background: #1a202c;
            border-radius: 0 0 6px 6px;
        }

        .illus-laptop-screen {
            width: 104px; height: 70px;
            background: #1a1a2e;
            border-radius: 4px;
            display: flex; align-items: center; justify-content: center;
        }

        .illus-logo-screen {
            width: 48px; height: 48px;
            background: var(--sidebar-bg);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: white;
        }

        .illus-plant {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .illus-mug {
            font-size: 36px;
        }

        .welcome-deco {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        /* ─── METRICS ─── */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 22px;
        }

        /* horizontal KPI card — big number on the left, illustration on the right */
        .metric-card {
            background: #fff;
            border: 1px solid #eef0f2;
            border-radius: 18px;
            padding: 18px 20px;
            position: relative;
            overflow: hidden;
            min-height: 130px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05), 0 12px 26px rgba(0,0,0,0.06);
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        }
        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 18px rgba(0,0,0,0.10), 0 18px 40px rgba(0,0,0,0.11);
            border-color: #e3e6e9;
        }
        /* top accent bar */
        .metric-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: var(--m-accent, #dcdfe3);
            z-index: 2;
        }
        /* soft tinted glow in the corner */
        .metric-card::after {
            content: '';
            position: absolute;
            top: -55px; right: -55px;
            width: 170px; height: 170px;
            border-radius: 50%;
            background: var(--m-glow, transparent);
            pointer-events: none;
        }
        .metric-card.accent-red    { --m-accent: linear-gradient(90deg, #c0392b, #7B1D1D); --m-glow: rgba(192,57,43,0.08); }
        .metric-card.accent-green  { --m-accent: linear-gradient(90deg, #10b981, #059669); --m-glow: rgba(16,185,129,0.08); }
        .metric-card.accent-blue   { --m-accent: linear-gradient(90deg, #3b82f6, #2563eb); --m-glow: rgba(59,130,246,0.08); }
        .metric-card.accent-orange { --m-accent: linear-gradient(90deg, #f59e0b, #d97706); --m-glow: rgba(245,158,11,0.08); }

        .metric-left {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .metric-number-lg { font-size: 34px; font-weight: 800; color: var(--gray-900); line-height: 1; }
        .metric-number-lg small { font-size: 16px; font-weight: 600; color: var(--gray-500); }
        .metric-label-lg { font-size: 12.5px; font-weight: 600; color: var(--gray-900); margin-top: 9px; }
        .metric-sub { font-size: 11px; color: var(--gray-500); margin-top: 3px; }
        .metric-sub .up { color: var(--green); font-weight: 600; }

        .metric-illust {
            width: 96px; height: 96px;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
            display: flex; align-items: center; justify-content: center;
        }
        .metric-illust img {
            width: 100%; height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 6px 14px rgba(0,0,0,0.15));
        }

        @media (max-width: 480px) {
            .metric-illust { width: 78px; height: 78px; }
        }

        .metric-body {}
        .metric-label { font-size: 11px; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .metric-number { font-size: 30px; font-weight: 700; color: var(--gray-900); line-height: 1; margin-bottom: 6px; }
        .metric-change { font-size: 11px; color: var(--green); }
        .metric-change.neutral { color: var(--gray-500); }

        .metric-chart { margin-top: 14px; }

        /* Sparkline charts */
        .sparkline { width: 100%; height: 36px; }
        .fire-row { display: flex; gap: 4px; margin-top: 6px; }
        .fire-icon { font-size: 18px; }
        .fire-icon.lit   { color: var(--orange); }
        .fire-icon.unlit { color: var(--gray-300); }

        /* ─── CONTENT GRID ─── */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 330px;
            gap: 18px;
            margin-bottom: 18px;
        }

        /* ─── CARDS ─── */
        .card {
            background: white;
            border: 1px solid #eef0f2;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            transition: box-shadow 0.22s ease, border-color 0.22s ease;
        }
        .card:hover {
            box-shadow: 0 10px 24px rgba(0,0,0,0.07);
            border-color: #e3e6e9;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .card-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--gray-900);
            position: relative;
            padding-left: 13px;
        }
        .card-title::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);
            width: 4px; height: 16px;
            border-radius: 2px;
            background: linear-gradient(180deg, #c0392b, #7B1D1D);
        }

        .card-link {
            font-size: 12px;
            color: var(--accent-red);
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
        }
        .card-link:hover { text-decoration: underline; }

        /* ─── SUBJECT MASTERY ─── */
        .subject-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 8px;
            border-bottom: 1px solid #f5f5f5;
            cursor: pointer;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .subject-item:last-child { border-bottom: none; }
        .subject-item:hover { background: var(--gray-100); }

        .subject-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }
        .subject-icon.s1 { background: #fde8e8; color: var(--accent-red); }
        .subject-icon.s2 { background: #fde8e8; color: var(--accent-red); }
        .subject-icon.s3 { background: #d1fae5; color: var(--green); }
        .subject-icon.s4 { background: #dbeafe; color: var(--blue); }
        .subject-icon.s5 { background: #fef3c7; color: var(--orange); }

        .subject-name { flex: 1; font-size: 13px; color: var(--gray-900); }
        .subject-arrow { color: var(--gray-500); font-size: 12px; }

        /* ─── TOP WEAKNESSES ─── */
        .weakness-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 13px 0;
            border-bottom: 1px solid #f5f5f5;
            cursor: pointer;
        }
        .weakness-item:last-child { border-bottom: none; }

        .weakness-num {
            width: 30px; height: 30px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 13px;
            color: white; flex-shrink: 0;
        }
        .weakness-num.n1 { background: var(--accent-red); }
        .weakness-num.n2 { background: var(--orange); }
        .weakness-num.n3 { background: var(--primary); }

        .weakness-info { flex: 1; }
        .weakness-title { font-size: 13px; font-weight: 600; color: var(--gray-900); margin-bottom: 2px; }
        .weakness-sub   { font-size: 11px; color: var(--gray-500); }
        .weakness-arrow { color: var(--gray-500); font-size: 12px; }

        /* ─── RIGHT PANEL ─── */
        .right-panel { display: flex; flex-direction: column; gap: 18px; }

        /* Overall Progress */
        .progress-wrap { text-align: center; }

        .progress-circle-container {
            width: 160px; height: 160px;
            margin: 0 auto 16px;
            position: relative;
            display: flex; align-items: center; justify-content: center;
        }

        .progress-circle-container svg {
            width: 100%; height: 100%;
            transform: rotate(-90deg);
        }

        .progress-inner {
            position: absolute;
            text-align: center;
        }

        .progress-pct { font-size: 34px; font-weight: 700; color: var(--gray-900); line-height: 1; }
        .progress-lbl { font-size: 12px; color: var(--gray-500); margin-top: 3px; }

        .progress-legend {
            display: flex;
            justify-content: center;
            gap: 18px;
            font-size: 12px;
            color: var(--gray-700);
            margin-bottom: 20px;
        }
        .legend-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
            vertical-align: middle;
        }
        .legend-dot.done { background: var(--primary); }
        .legend-dot.left { background: #f0c9c9; }

        /* Quick Action Buttons */
        .quick-btn {
            width: 100%;
            padding: 13px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            margin-bottom: 10px;
            text-decoration: none;
        }
        .quick-btn:last-child { margin-bottom: 0; }
        .quick-btn.primary {
            background: var(--primary);
            color: white;
            border: none;
        }
        .quick-btn.primary:hover { background: var(--primary-hover); }
        .quick-btn.outline {
            background: white;
            color: var(--primary);
            border: 1.5px solid var(--primary);
        }
        .quick-btn.outline:hover { background: var(--primary-light); }

        /* Recent Activity */
        .activity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        .activity-item:last-child { border-bottom: none; }

        .activity-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .activity-icon.quiz { background: #d1fae5; color: var(--green); }

        .activity-info { flex: 1; }
        .activity-name { font-size: 13px; font-weight: 600; color: var(--gray-900); margin-bottom: 2px; }
        .activity-meta { font-size: 11px; color: var(--gray-500); }

        .activity-right { text-align: right; flex-shrink: 0; }
        .activity-time { font-size: 11px; color: var(--gray-500); }
        .activity-chevron { font-size: 11px; color: var(--gray-500); margin-top: 4px; }

        /* ─── BOTTOM ROW ─── */
        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        /* Study Streak */
        .streak-card {
            background: linear-gradient(135deg, #ffffff 0%, #fdf3f3 100%);
            border: 1px solid #f4e3e3;
            border-radius: 18px;
            padding: 28px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            transition: box-shadow 0.22s ease, transform 0.22s ease;
        }
        .streak-card:hover { box-shadow: 0 10px 24px rgba(123,29,29,0.10); transform: translateY(-3px); }

        .streak-title { font-size: 15px; font-weight: 600; color: var(--gray-900); margin-bottom: 14px; }

        .streak-num-row { display: flex; align-items: baseline; gap: 6px; margin-bottom: 4px; }
        .streak-num { font-size: 52px; font-weight: 700; color: var(--gray-900); line-height: 1; }
        .streak-unit { font-size: 18px; font-weight: 500; color: var(--gray-500); }
        .streak-sub { font-size: 13px; color: var(--gray-500); }

        .streak-deco { font-size: 90px; color: #f0c9c9; flex-shrink: 0; opacity: 0.7; }

        /* Quote Card */
        .quote-card {
            background: linear-gradient(135deg, #ffffff 0%, #f6f7fb 100%);
            border: 1px solid #eef0f2;
            border-radius: 18px;
            padding: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            transition: box-shadow 0.22s ease, transform 0.22s ease;
        }
        .quote-card:hover { box-shadow: 0 10px 24px rgba(0,0,0,0.07); transform: translateY(-3px); }

        .quote-body {}
        .quote-marks { font-size: 48px; color: var(--primary-light); line-height: 0.8; margin-bottom: 10px; font-family: Georgia, serif; color: #e0d0d0; }
        .quote-text { font-size: 14px; color: var(--gray-700); line-height: 1.6; font-style: italic; margin-bottom: 12px; }
        .quote-author { font-size: 13px; color: var(--gray-500); font-weight: 500; }

        .quote-deco { font-size: 80px; flex-shrink: 0; opacity: 0.6; color: #f0c9c9; }

        /* ─── CARD MENU ─── */
        .card-menu-btn {
            background: none; border: none;
            color: var(--gray-500); font-size: 16px;
            cursor: pointer; padding: 2px 6px;
        }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 1280px) {
            .content-grid { grid-template-columns: 1fr 1fr; }
            .content-grid .right-panel { grid-column: 1 / 3; }
            .metrics-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 900px) {
            .sidebar { width: 70px; }
            .main-content { margin-left: 70px; }
            .content-grid { grid-template-columns: 1fr; }
            .content-grid .right-panel { grid-column: 1; }
            .bottom-grid { grid-template-columns: 1fr; }
            .metrics-grid { grid-template-columns: repeat(2, 1fr); }
            .search-wrap input { width: 180px; }
        }

        /* ─── RESPONSIVE (added) ─── */
        @media (max-width: 1100px) {
            .content-grid { grid-template-columns: 1fr 1fr; }
            .content-grid .right-panel { grid-column: 1 / 3; }
        }

        @media (max-width: 768px) {
            .main-content { padding: 20px 16px; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 12px; }
            .page-header-right { width: 100%; flex-wrap: wrap; }
            .search-wrap input { width: 100%; }
            .search-wrap { flex: 1; }
            .page-title { font-size: 22px; }
            .welcome-banner { grid-template-columns: 1fr; gap: 12px; }
            .metrics-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .content-grid { grid-template-columns: 1fr; }
            .content-grid .right-panel { grid-column: 1; }
            .bottom-grid { grid-template-columns: 1fr; }
            .streak-card, .quote-card { flex-direction: column; gap: 10px; }
        }

        @media (max-width: 480px) {
            .main-content { padding: 16px 12px; }
            .metrics-grid { grid-template-columns: 1fr; }
            .metric-number { font-size: 24px; }
            .page-title { font-size: 20px; }
            .card { padding: 16px; }
        }

        /* ─── ANIMATION ─── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .anim { animation: fadeUp 0.45s ease both; }
    </style>
</head>
<body>

<!-- ════════════════════════ SIDEBAR ════════════════════════ -->
@include('partials.sidebar', ['active' => 'dashboard'])
@include('partials.student-bottom-nav', ['active' => 'dashboard'])
@include('partials.student-mobile-header')

<!-- ════════════════════════ MAIN CONTENT ════════════════════════ -->
<main class="main-content">

    <!-- HEADER -->
    <div class="page-header anim" style="animation-delay:0s">
        <div class="page-header-left">
            <div>
                <div class="page-title">Dashboard</div>
                <div class="page-subtitle">Welcome back, {{ Auth::user()->name }}! Let's keep up the momentum.</div>
            </div>
        </div>
        <div class="page-header-right">
            <div class="search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search topics, questions, subjects...">
            </div>
            <a class="notif-btn" href="{{ route('messages.index') }}" aria-label="Messages" title="Messages" style="text-decoration:none">
                <i class="fas fa-comment-dots"></i>
                @if($unreadMessages > 0)
                    <span class="badge">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>
                @endif
            </a>
            <a class="notif-btn" href="{{ route('notifications.index') }}" aria-label="Notifications" title="Notifications" style="text-decoration:none">
                <i class="fas fa-bell"></i>
                @if($unreadNotifications > 0)
                    <span class="badge">{{ $unreadNotifications }}</span>
                @endif
            </a>
            <div class="header-dropdown-wrap">
                <button class="profile-avatar" id="profileBtn">@include('partials.avatar-content')</button>
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

    <!-- COUNTDOWN + CALENDAR ROW -->
    <div class="overview-top anim" style="animation-delay:0.03s">

        <!-- EXAM COUNTDOWN (flip-clock style) -->
        <div class="exam-hero">
            <div class="exam-hero-shapes" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></div>

            <div class="exam-hero-left">
                <div class="exam-hero-greeting" id="examHeroGreeting">Good Day, {{ explode(' ', Auth::user()->name)[0] }}!</div>
                <div class="exam-hero-subtitle">Board Exam Countdown</div>
                @if($examDateIso)
                    <div class="exam-hero-target">Target Board Exam: <strong id="examTargetLabel">&mdash;</strong></div>
                @else
                    <p class="exam-hero-empty">Set your target board exam date to start the countdown.</p>
                    <a href="{{ route('settings') }}" class="exam-hero-btn"><i class="fas fa-calendar-plus"></i> Set target date</a>
                @endif
            </div>

            @if($examDateIso)
                <div class="countdown-grid" id="countdownGrid">
                    <div class="cd-unit"><div class="cd-flip"><span id="cdDays">--</span></div><span class="cd-lbl">Days</span></div>
                    <div class="cd-sep"><span></span><span></span></div>
                    <div class="cd-unit"><div class="cd-flip"><span id="cdHours">--</span></div><span class="cd-lbl">Hrs</span></div>
                    <div class="cd-sep"><span></span><span></span></div>
                    <div class="cd-unit"><div class="cd-flip"><span id="cdMins">--</span></div><span class="cd-lbl">Mins</span></div>
                    <div class="cd-sep"><span></span><span></span></div>
                    <div class="cd-unit"><div class="cd-flip"><span id="cdSecs">--</span></div><span class="cd-lbl">Secs</span></div>
                </div>
            @endif
        </div>

        <!-- CALENDAR / TODAY -->
        <div class="today-card">
            <div class="cal-top"><span class="cal-rings"></span><span id="calMonth">&mdash;</span></div>
            <div class="cal-body">
                <div class="cal-hero">
                    <div class="cal-daynum" id="calDayNum">--</div>
                    <div class="cal-weekday" id="calWeekday">&mdash;</div>
                </div>
                <div class="cal-clock"><span id="clockTime">--:--:--</span><span class="ampm" id="clockAmPm"></span></div>
            </div>
        </div>
    </div>

    <!-- METRICS -->
    <div class="metrics-grid anim" style="animation-delay:0.14s">
        <!-- Board Readiness Score -->
        <div class="metric-card accent-red">
            <div class="metric-left">
                <div class="metric-number-lg">{{ $readiness }}<small>%</small></div>
                <div class="metric-label-lg">Board Readiness Score</div>
                <div class="metric-sub">Overall accuracy across all topics</div>
            </div>
            <div class="metric-illust"><img src="{{ asset('images/5.png') }}" alt="Board Readiness Score"></div>
        </div>

        <!-- Questions Attempted -->
        <div class="metric-card accent-red">
            <div class="metric-left">
                <div class="metric-number-lg">{{ number_format($questionsAttempted) }}</div>
                <div class="metric-label-lg">Questions Attempted</div>
                <div class="metric-sub">
                    @if($questionsThisWeek > 0)<span class="up"><i class="fas fa-arrow-up"></i> {{ $questionsThisWeek }}</span> this week @else No activity this week @endif
                </div>
            </div>
            <div class="metric-illust"><img src="{{ asset('images/6.png') }}" alt="Questions Attempted"></div>
        </div>

        <!-- Study Time -->
        <div class="metric-card accent-red">
            <div class="metric-left">
                <div class="metric-number-lg">{{ $studyHours }}<small>h</small></div>
                <div class="metric-label-lg">Study Time</div>
                <div class="metric-sub">
                    @if($studyHoursWeek > 0)<span class="up"><i class="fas fa-arrow-up"></i> {{ $studyHoursWeek }}h</span> this week @else No activity this week @endif
                </div>
            </div>
            <div class="metric-illust"><img src="{{ asset('images/9.png') }}" alt="Study Time"></div>
        </div>

        <!-- Day Streak -->
        <div class="metric-card accent-red">
            <div class="metric-left">
                <div class="metric-number-lg">{{ $streak }}</div>
                <div class="metric-label-lg">Day Streak</div>
                <div class="metric-sub">{{ $streak > 0 ? 'Keep it up!' : 'Start a quiz to begin a streak' }}</div>
            </div>
            <div class="metric-illust"><img src="{{ asset('images/8.png') }}" alt="Day Streak"></div>
        </div>
    </div>

    <!-- CONTENT GRID -->
    <div class="content-grid anim" style="animation-delay:0.2s">

        <!-- Subject Mastery -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Subject Mastery</span>
                <a class="card-link" href="{{ route('subjects') }}">View All</a>
            </div>
            @php
                $subjectIcons = [
                    'FAR' => 'fa-book', 'AFAR' => 'fa-layer-group', 'MS' => 'fa-users',
                    'TAX' => 'fa-table', 'AUD' => 'fa-search', 'RFBT' => 'fa-balance-scale',
                ];
            @endphp
            @foreach($subjectMastery as $i => $subject)
                <a href="{{ route('subjects') }}" class="subject-item" style="text-decoration:none;">
                    <div class="subject-icon s{{ $i % 5 + 1 }}"><i class="fas {{ $subjectIcons[$subject->code] ?? 'fa-book' }}"></i></div>
                    <span class="subject-name">{{ $subject->name }}</span>
                    <span style="font-size:11px;font-weight:600;color:{{ $subject->is_passing ? '#059669' : 'var(--gray-500)' }};margin-right:10px;text-align:right;">
                        {{ $subject->mastery }}%<br><small style="font-size:9px;font-weight:500;">Pass: {{ $subject->passing_threshold }}%</small>
                    </span>
                    <i class="fas fa-chevron-right subject-arrow"></i>
                </a>
            @endforeach
        </div>

        <!-- Top Weaknesses -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Top Weaknesses</span>
                <a class="card-link" href="{{ route('performance') }}">Focus Areas</a>
            </div>
            @forelse($weaknesses as $i => $weakness)
                <div class="weakness-item">
                    <div class="weakness-num n{{ $i + 1 }}">{{ $i + 1 }}</div>
                    <div class="weakness-info">
                        <div class="weakness-title">{{ $weakness->topic }}</div>
                        <div class="weakness-sub">{{ $weakness->subject_code }} &ndash; {{ round($weakness->accuracy_rate) }}% accuracy</div>
                    </div>
                    <i class="fas fa-chevron-right weakness-arrow"></i>
                </div>
            @empty
                <p style="font-size:13px; color:var(--gray-500); padding:14px 0;">
                    No weak areas detected yet. Take a few quizzes and your focus areas will appear here.
                </p>
            @endforelse
        </div>

        <!-- RIGHT PANEL -->
        <div class="right-panel">
            <!-- Overall Progress -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Overall Progress</span>
                    <button class="card-menu-btn"><i class="fas fa-ellipsis-h"></i></button>
                </div>
                <div class="progress-wrap">
                    <div class="progress-circle-container">
                        @php $circumference = 2 * pi() * 42; $filled = round($circumference * $readiness / 100, 1); @endphp
                        <svg viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="42" fill="none" stroke="#f0c9c9" stroke-width="9"/>
                            <circle cx="50" cy="50" r="42" fill="none" stroke="#7B1D1D" stroke-width="9"
                                    stroke-dasharray="{{ $filled }} {{ round($circumference, 1) }}" stroke-linecap="round"/>
                        </svg>
                        <div class="progress-inner">
                            <div class="progress-pct">{{ $readiness }}%</div>
                            <div class="progress-lbl">Ready</div>
                        </div>
                    </div>
                    <div class="progress-legend">
                        <span><span class="legend-dot done"></span>Completed</span>
                        <span><span class="legend-dot left"></span>Remaining</span>
                    </div>
                </div>
                <a href="{{ route('adaptive-quizzes') }}" class="quick-btn primary">Start Quick Quiz &rarr;</a>
                <a href="{{ route('mock-exams') }}" class="quick-btn outline">Take a Mock Exam</a>
            </div>
        </div>
    </div>

    <!-- BOTTOM ROW -->
    <div class="bottom-grid anim" style="animation-delay:0.26s">
        <!-- Study Streak -->
        <div class="streak-card">
            <div>
                <div class="streak-title">Study Streak</div>
                <div class="streak-num-row">
                    <span class="streak-num">{{ $streak }}</span>
                    <span class="streak-unit">{{ \Illuminate\Support\Str::plural('day', $streak) }}</span>
                </div>
                <div class="streak-sub">{{ $streak > 0 ? 'Keep the momentum going!' : 'Study today to start your streak.' }}</div>
            </div>
            <div style="font-size:72px; opacity:0.35; user-select:none;">&#128197;&#127807;</div>
        </div>

        <!-- Quote -->
        <div class="quote-card">
            <div class="quote-body">
                <div class="quote-marks">&ldquo;</div>
                <div class="quote-text">Success is the sum of small efforts, repeated day in and day out.</div>
                <div class="quote-author">&mdash; Robert Collier</div>
            </div>
            <div style="font-size:70px; opacity:0.3; user-select:none; flex-shrink:0;">&#128218;&#128161;</div>
        </div>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Live digital clock + live board-exam countdown
    (function () {
        const timeEl = document.getElementById('clockTime');
        const ampmEl = document.getElementById('clockAmPm');
        const calMonth   = document.getElementById('calMonth');
        const calDayNum  = document.getElementById('calDayNum');
        const calWeekday = document.getElementById('calWeekday');
        if (!timeEl) return;

        const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        const pad = n => String(n).padStart(2, '0');

        // Countdown target (midnight of the exam day, viewer's local time), or null.
        const EXAM_TARGET = @json($examDateIso);
        const target = EXAM_TARGET ? new Date(EXAM_TARGET + 'T00:00:00') : null;

        const cdDays = document.getElementById('cdDays');
        const cdHours = document.getElementById('cdHours');
        const cdMins = document.getElementById('cdMins');
        const cdSecs = document.getElementById('cdSecs');
        const targetLabel = document.getElementById('examTargetLabel');
        if (target && targetLabel) {
            targetLabel.textContent = `${months[target.getMonth()]} ${target.getDate()}, ${target.getFullYear()}`;
        }

        const greetingEl = document.getElementById('examHeroGreeting');
        if (greetingEl) {
            const hour = new Date().getHours();
            const period = hour < 12 ? 'Good Morning' : (hour < 18 ? 'Good Afternoon' : 'Good Evening');
            greetingEl.textContent = greetingEl.textContent.replace('Good Day', period);
        }

        function tick() {
            const now = new Date();

            // clock
            let h = now.getHours();
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            timeEl.textContent = `${pad(h)}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
            ampmEl.textContent = ampm;

            // calendar card
            if (calMonth)   calMonth.textContent   = `${months[now.getMonth()].toUpperCase()} ${now.getFullYear()}`;
            if (calDayNum)  calDayNum.textContent  = now.getDate();
            if (calWeekday) calWeekday.textContent = days[now.getDay()];

            // countdown
            if (target && cdDays) {
                let diff = Math.max(0, target.getTime() - now.getTime());
                const d = Math.floor(diff / 86400000); diff -= d * 86400000;
                const hh = Math.floor(diff / 3600000); diff -= hh * 3600000;
                const mm = Math.floor(diff / 60000); diff -= mm * 60000;
                const ss = Math.floor(diff / 1000);
                cdDays.textContent  = d;
                cdHours.textContent = pad(hh);
                cdMins.textContent  = pad(mm);
                cdSecs.textContent  = pad(ss);
            }
        }
        tick();
        setInterval(tick, 1000);
    })();

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
});
</script>
</body>
</html>
