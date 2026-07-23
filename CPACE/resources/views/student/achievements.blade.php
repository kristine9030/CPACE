<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievements - CPACE CPA Reviewer</title>

    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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

        .challenge-trophy {
            position: absolute;
            right: 10px;
            top: 8px;
            font-size: 34px;
            color: #f4b740;
            opacity: 0.9;
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
            position: relative;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: 70px;
        }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
        }

        .header-left {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-title {
            font-size: 28px;
            font-weight: 700;
            color: #222;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-title .title-doodle {
            color: #c0392b;
            font-size: 20px;
            transform: rotate(-8deg);
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
            border: 1px solid #eee;
            border-radius: 22px;
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

        /* CREATIVE PAGE HEADER */
        .title-trophy {
            width: 54px;
            height: 54px;
            object-fit: contain;
            transform: rotate(-8deg);
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.18));
            animation: trophy-bob 3s ease-in-out infinite;
        }

        @keyframes trophy-bob {
            0%, 100% { transform: rotate(-8deg) translateY(0); }
            50%      { transform: rotate(-4deg) translateY(-5px); }
        }

        .title-underline {
            display: block;
            margin-top: 2px;
        }

        /* STATUS BANNER — dark red-black gradient with abstract shapes */
        .status-banner {
            background: linear-gradient(135deg, #1a0a0a 0%, #3d0c0c 30%, #7B1D1D 60%, #a12626 100%);
            border-radius: 20px;
            padding: 28px 34px;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.35);
            display: flex;
            align-items: center;
            gap: 0;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
        }

        .status-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                repeating-linear-gradient(
                    0deg,
                    transparent,
                    transparent 38px,
                    rgba(255, 255, 255, 0.06) 38px,
                    rgba(255, 255, 255, 0.06) 40px
                ),
                repeating-linear-gradient(
                    90deg,
                    transparent,
                    transparent 38px,
                    rgba(255, 255, 255, 0.06) 38px,
                    rgba(255, 255, 255, 0.06) 40px
                );
            mask-image: linear-gradient(to right, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 30%, transparent 50%);
            -webkit-mask-image: linear-gradient(to right, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 30%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .status-banner::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 15%;
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, rgba(255, 215, 106, 0.08) 0%, transparent 65%);
            border-radius: 50%;
            pointer-events: none;
        }

        .status-banner .banner-shape-1,
        .status-banner .banner-shape-2,
        .status-banner .banner-shape-3,
        .status-banner .banner-shape-4,
        .status-banner .banner-shape-5,
        .status-banner .banner-shape-6,
        .status-banner .banner-shape-7,
        .status-banner .banner-shape-8 {
            position: absolute;
            pointer-events: none;
            z-index: 0;
        }

        .status-banner .banner-shape-1 {
            top: 10px;
            right: 25%;
            width: 90px;
            height: 90px;
            border: 2px solid rgba(255, 215, 106, 0.12);
            border-radius: 24px;
            transform: rotate(30deg);
        }

        .status-banner .banner-shape-2 {
            bottom: 8px;
            right: 10%;
            width: 50px;
            height: 50px;
            background: rgba(192, 57, 43, 0.2);
            border-radius: 50%;
        }

        .status-banner .banner-shape-3 {
            top: 50%;
            left: 40%;
            width: 0;
            height: 0;
            border-left: 30px solid transparent;
            border-right: 30px solid transparent;
            border-bottom: 52px solid rgba(255, 255, 255, 0.03);
            transform: translateY(-50%) rotate(-15deg);
        }

        .status-banner .banner-shape-4 {
            top: -20px;
            left: 8%;
            width: 70px;
            height: 70px;
            border: 2px solid rgba(255, 255, 255, 0.07);
            border-radius: 50%;
        }

        .status-banner .banner-shape-5 {
            bottom: -15px;
            left: 35%;
            width: 120px;
            height: 120px;
            border: 1.5px solid rgba(255, 215, 106, 0.06);
            border-radius: 32px;
            transform: rotate(45deg);
        }

        .status-banner .banner-shape-6 {
            top: 15px;
            right: 8%;
            width: 0;
            height: 0;
            border-left: 20px solid transparent;
            border-right: 20px solid transparent;
            border-bottom: 35px solid rgba(192, 57, 43, 0.1);
            transform: rotate(20deg);
        }

        .status-banner .banner-shape-7 {
            bottom: 20px;
            right: 28%;
            width: 40px;
            height: 40px;
            border: 1.5px solid rgba(255, 255, 255, 0.06);
            border-radius: 50%;
        }

        .status-banner .banner-shape-8 {
            top: 50%;
            left: 20%;
            width: 55px;
            height: 55px;
            background: rgba(255, 215, 106, 0.04);
            border-radius: 16px;
            transform: translateY(-50%) rotate(-25deg);
        }


        .banner-profile {
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
            z-index: 1;
            flex-shrink: 0;
            align-self: stretch;
            padding-right: 34px;
            border-right: 1px solid rgba(255, 255, 255, 0.12);
        }

        .banner-right {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 20px;
            padding-left: 30px;
            position: relative;
            z-index: 1;
        }

        .banner-avatar-wrap {
            position: relative;
            flex-shrink: 0;
            padding-top: 6px;
        }

        .banner-avatar {
            width: 96px;
            height: 96px;
            background: #fff;
            border: 4px solid rgba(255, 255, 255, 0.55);
            outline: 3px solid rgba(255, 255, 255, 0.18);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            font-weight: 700;
            color: #c0392b;
        }

        .banner-crown {
            position: absolute;
            top: -6px;
            left: 50%;
            transform: translateX(-50%) rotate(-8deg);
            font-size: 30px;
            color: #f4b740;
            text-shadow: 0 2px 0 rgba(0,0,0,0.08);
        }

        .banner-name {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
        }

        .banner-role {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 8px;
        }

        .banner-doodle-star {
            color: #ffd76a;
            font-size: 16px;
            margin-left: 4px;
            display: inline-block;
            animation: twinkle 2s ease-in-out infinite;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 1; transform: scale(1) rotate(0deg); }
            50% { opacity: 0.5; transform: scale(0.8) rotate(15deg); }
        }

        .banner-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff;
            color: #c0392b;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 16px;
            border-radius: 20px;
        }

        .banner-status {
            flex: 0 0 auto;
            text-align: center;
            position: relative;
            z-index: 1;
            min-width: 128px;
        }

        .banner-status-label {
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 4px;
        }

        .banner-status-value {
            font-size: 72px;
            font-weight: 800;
            color: #fff;
            line-height: 1;
            margin-bottom: 8px;
            text-shadow: 0 4px 16px rgba(0, 0, 0, 0.35);
        }

        .banner-status-sub {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.8);
            max-width: 180px;
            margin: 0 auto;
            line-height: 1.5;
        }

        .banner-status-sub strong { color: #ffd76a; }

        .banner-stats {
            display: flex;
            gap: 14px;
            position: relative;
            z-index: 1;
            flex-shrink: 0;
        }

        .stat-box {
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 18px 20px 16px;
            min-width: 158px;
            backdrop-filter: blur(6px);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .stat-box.days {
            background: rgba(0, 0, 0, 0.25);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .stat-box-top {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .stat-icon.trophy {
            background: rgba(252, 237, 203, 0.2);
            color: #ffd76a;
            box-shadow: 0 0 8px rgba(255, 215, 106, 0.15);
        }

        .stat-icon.drop {
            background: rgba(74, 144, 217, 0.2);
            color: #6aaef5;
            box-shadow: 0 0 8px rgba(106, 174, 245, 0.15);
        }

        .stat-label {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #fff;
            line-height: 1.15;
        }

        .stat-extra {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 12px;
        }

        .stat-bar {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .stat-bar span {
            display: block;
            height: 100%;
            border-radius: 10px;
            background: linear-gradient(90deg, #ffd76a, #f5a623);
        }

        .stat-box.days .stat-bar { background: rgba(255, 255, 255, 0.1); }
        .stat-box.days .stat-bar span { background: linear-gradient(90deg, #6aaef5, #3e7fd0); }

        .banner-mascot {
            flex-shrink: 0;
            width: 158px;
            height: 158px;
            margin-left: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border-radius: 18px;
            padding: 4px;
            transform: rotate(2deg);
            z-index: 1;
            position: relative;
        }

        .banner-mascot img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
        }

        /* LAYOUT */
        .achievements-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 25px;
            align-items: start;
            position: relative;
            z-index: 1;
        }

        .panel {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            position: relative;
        }

        .panel + .panel {
            margin-top: 25px;
        }

        .panel-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            gap: 12px;
            flex-wrap: wrap;
        }

        .panel-title {
            font-size: 19px;
            font-weight: 700;
            color: #222;
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .panel-title .title-emoji { font-size: 20px; }

        .panel-title .title-doodle {
            color: #c0392b;
            font-size: 14px;
            transform: rotate(-8deg);
        }

        .panel-head-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .unlocked-count {
            font-size: 13px;
            font-weight: 600;
            color: #c0392b;
        }

        .see-all-btn {
            background: #c0392b;
            color: white;
            border: none;
            padding: 7px 16px;
            border-radius: 18px;
            font-size: 12px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: background 0.2s;
        }

        .see-all-btn:hover { background: #a93226; }

        /* FILTER TABS */
        .badge-tabs {
            display: flex;
            gap: 10px;
            margin: 16px 0 22px;
            flex-wrap: wrap;
        }

        .badge-tab {
            padding: 7px 18px;
            border: 1px solid #eee;
            background: white;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            color: #777;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
        }

        .badge-tab.active {
            background: #c0392b;
            border-color: #c0392b;
            color: white;
            font-weight: 600;
        }

        .badge-tab:hover:not(.active) {
            border-color: #c0392b;
            color: #c0392b;
        }

        /* BADGE GRID */
        .badge-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        /* ── Colour themes (per badge) — bright & vivid ────────────────── */
        .c-red    { --b1:#ff8a6b; --b2:#ff2740; --bg:#ffe0d9; --glow:rgba(255,39,64,.55); }
        .c-green  { --b1:#57f593; --b2:#08d15c; --bg:#d3fce2; --glow:rgba(8,209,92,.52); }
        .c-blue   { --b1:#63c0ff; --b2:#1f7bff; --bg:#d7ecff; --glow:rgba(31,123,255,.52); }
        .c-yellow { --b1:#ffe24a; --b2:#ffab00; --bg:#fff1bd; --glow:rgba(255,171,0,.55); }
        .c-purple { --b1:#d488ff; --b2:#9412f5; --bg:#efdcff; --glow:rgba(148,18,245,.52); }
        .c-pink   { --b1:#ff9fdc; --b2:#ff1e8e; --bg:#ffdaee; --glow:rgba(255,30,142,.52); }
        .c-teal   { --b1:#3ff7de; --b2:#00c7b1; --bg:#cbfcf4; --glow:rgba(0,199,177,.52); }
        .c-gray   { --b1:#cbd2da; --b2:#8b97a6; --bg:#eaedf1; --glow:rgba(148,163,184,.3); }

        .badge-card {
            border: 1px solid #f1eef0;
            border-radius: 16px;
            padding: 22px 14px 18px;
            text-align: center;
            transition: transform .22s cubic-bezier(.2,.8,.3,1.1), box-shadow .22s;
            position: relative;
            background:
                radial-gradient(circle at 50% -10%, var(--bg), transparent 60%),
                #fff;
            overflow: hidden;
            cursor: pointer;
        }

        .badge-card:hover {
            box-shadow: 0 12px 26px var(--glow);
            transform: translateY(-5px);
        }
        .badge-card:active { transform: translateY(-2px) scale(.99); }

        .lock-chip, .earned-chip {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            z-index: 3;
        }
        .lock-chip {
            background: #fff;
            border: 1px solid #e5e5e5;
            color: #9aa0a6;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .earned-chip {
            background: linear-gradient(160deg, #34d67e, #16a34a);
            color: #fff;
            box-shadow: 0 3px 8px rgba(22,163,74,.45);
        }

        /* ── Celebratory medallion ─────────────────────────────────────── */
        .badge-medal-wrap {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 6px auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .badge-card:not(.locked) .badge-medal-wrap { animation: badge-bob 3.2s ease-in-out infinite; }

        /* spinning sunburst rays behind the medal */
        .badge-rays {
            position: absolute;
            inset: -6px;
            border-radius: 50%;
            background: repeating-conic-gradient(var(--b1) 0deg 11deg, transparent 11deg 22deg);
            opacity: .32;
            animation: badge-spin 16s linear infinite;
        }
        .badge-card.locked .badge-rays { opacity: .12; animation-play-state: paused; }

        .badge-medal {
            position: relative;
            width: 82px;
            height: 82px;
            border-radius: 50%;
            background: radial-gradient(circle at 34% 26%, var(--b1), var(--b2) 82%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 31px;
            border: 3px solid rgba(255,255,255,.9);
            box-shadow:
                0 6px 16px var(--glow),
                inset 0 3px 7px rgba(255,255,255,.6),
                inset 0 -7px 13px rgba(0,0,0,.28),
                0 0 0 3px var(--b2);
            z-index: 1;
            overflow: hidden;
        }
        .badge-medal > i { filter: drop-shadow(0 2px 2px rgba(0,0,0,.28)); }

        /* moving glare sweep */
        .badge-medal::after {
            content: '';
            position: absolute;
            top: -40%;
            left: -70%;
            width: 55%;
            height: 190%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.7), transparent);
            transform: rotate(20deg);
            animation: badge-sweep 3.8s ease-in-out infinite;
        }

        .badge-card.locked .badge-medal {
            background: radial-gradient(circle at 34% 26%, #d7dce2, #97a3b2 82%);
            filter: grayscale(.35);
            box-shadow: inset 0 3px 7px rgba(255,255,255,.5), inset 0 -7px 13px rgba(0,0,0,.22), 0 0 0 3px #97a3b2;
        }
        .badge-card.locked .badge-medal::after { display: none; }
        .badge-card.locked .badge-medal-wrap { opacity: .9; }

        /* twinkling sparkles */
        .badge-sparkle {
            position: absolute;
            background: #fff;
            clip-path: polygon(50% 0,61% 39%,100% 50%,61% 61%,50% 100%,39% 61%,0 50%,39% 39%);
            filter: drop-shadow(0 0 3px var(--b1));
            animation: badge-twinkle 2.4s ease-in-out infinite;
            z-index: 2;
        }
        .badge-card.locked .badge-sparkle { display: none; }
        .badge-sparkle.s1 { width:13px; height:13px; top:2px;   left:12px;  animation-delay:0s;   }
        .badge-sparkle.s2 { width:9px;  height:9px;  top:20px;  right:8px;  animation-delay:.5s;  }
        .badge-sparkle.s3 { width:10px; height:10px; bottom:6px; left:20px; animation-delay:1.1s; }

        @keyframes badge-bob { 0%,100%{transform:translateY(0);} 50%{transform:translateY(-6px);} }
        @keyframes badge-spin { to { transform: rotate(360deg); } }
        @keyframes badge-sweep {
            0% { left:-70%; } 55%,100% { left:130%; }
        }
        @keyframes badge-twinkle {
            0%,100% { transform: scale(1) rotate(0); opacity:.95; }
            50%     { transform: scale(.35) rotate(45deg); opacity:.3; }
        }

        .badge-name {
            font-size: 15px;
            font-weight: 700;
            color: #222;
            margin-bottom: 6px;
        }

        .badge-desc {
            font-size: 12px;
            color: #888;
            line-height: 1.5;
            margin-bottom: 12px;
            min-height: 36px;
        }

        .badge-earned {
            font-size: 12px;
            color: #16a34a;
            font-weight: 600;
        }

        .badge-progress-track {
            height: 6px;
            background: #eef0f2;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .badge-progress-track span {
            display: block;
            height: 100%;
            border-radius: 10px;
            background: linear-gradient(90deg, var(--b1), var(--b2));
            transition: width .5s ease;
        }

        .badge-progress-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--b2);
        }

        .badge-card.locked .badge-name { color: #777; }
        .badge-card.locked .badge-progress-label { color: #8b97a6; }

        /* ── Badge detail modal + certificate ──────────────────────────── */
        .badge-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(24, 8, 8, .6);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9000;
            padding: 20px;
            animation: badge-fade .2s ease;
        }
        .badge-modal {
            background: #fff;
            border-radius: 24px;
            width: min(410px, 100%);
            padding: 10px 10px 6px;
            box-shadow: 0 30px 80px rgba(0,0,0,.45);
            position: relative;
            animation: badge-pop .34s cubic-bezier(.2,.9,.3,1.35);
        }
        .badge-modal-close {
            position: absolute;
            top: 14px;
            right: 16px;
            width: 30px;
            height: 30px;
            border: none;
            border-radius: 50%;
            background: rgba(0,0,0,.06);
            color: #555;
            font-size: 17px;
            cursor: pointer;
            z-index: 4;
            transition: background .15s;
        }
        .badge-modal-close:hover { background: rgba(0,0,0,.13); }

        .badge-cert {
            border-radius: 18px;
            padding: 30px 26px 26px;
            text-align: center;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 50% -8%, var(--bg), #fff 66%),
                #fff;
            border: 1px solid #f1e9e9;
        }
        .cert-brand {
            font-size: 10.5px;
            letter-spacing: 2.5px;
            font-weight: 700;
            color: var(--b2);
            margin-bottom: 14px;
            text-transform: uppercase;
        }
        .cert-spark { position:absolute; font-size:18px; opacity:.9; }
        .cert-spark.a { top:16px; left:22px; }
        .cert-spark.b { top:40px; right:26px; font-size:13px; }
        .cert-spark.c { top:120px; left:30px; font-size:14px; }

        .badge-cert .badge-medal-wrap {
            width: 138px;
            height: 138px;
            margin: 2px auto 18px;
            animation: none;
        }
        .badge-cert .badge-medal {
            width: 118px;
            height: 118px;
            font-size: 46px;
            border-width: 4px;
        }
        .badge-cert .badge-sparkle.s1 { top:6px; left:22px; width:16px; height:16px; }
        .badge-cert .badge-sparkle.s2 { top:30px; right:16px; width:12px; height:12px; }
        .badge-cert .badge-sparkle.s3 { bottom:10px; left:32px; width:13px; height:13px; }

        .cert-name {
            font-size: 23px;
            font-weight: 800;
            color: #1f1f1f;
            margin-bottom: 6px;
        }
        .cert-desc {
            font-size: 13px;
            color: #777;
            line-height: 1.55;
            max-width: 280px;
            margin: 0 auto 14px;
        }
        .cert-status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 12.5px;
            font-weight: 700;
            padding: 7px 16px;
            border-radius: 20px;
            background: linear-gradient(160deg, #34d67e, #16a34a);
            color: #fff;
            box-shadow: 0 4px 12px rgba(22,163,74,.35);
        }
        .cert-status.locked {
            background: #eef0f2;
            color: #8b97a6;
            box-shadow: none;
        }

        .badge-cert.locked .badge-sparkle { display: none; }
        .badge-cert.locked .badge-rays { opacity: .12; animation-play-state: paused; }
        .badge-cert.locked .badge-medal::after { display: none; }

        .badge-modal-actions {
            display: flex;
            gap: 10px;
            padding: 16px 12px 12px;
        }
        .badge-save-btn, .badge-celebrate-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 13px 12px;
            border: none;
            border-radius: 14px;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            transition: transform .15s, box-shadow .15s, opacity .15s;
        }
        .badge-save-btn {
            background: linear-gradient(160deg, #ef5b4d, #c0392b);
            color: #fff;
            box-shadow: 0 6px 16px rgba(192,57,43,.4);
        }
        .badge-save-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 9px 22px rgba(192,57,43,.5); }
        .badge-save-btn:disabled { opacity: .7; cursor: default; }
        .badge-celebrate-btn {
            background: #fff5e6;
            color: #d97706;
            border: 1px solid #ffe2b8;
        }
        .badge-celebrate-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(217,119,6,.22); }

        @keyframes badge-fade { from { opacity:0; } to { opacity:1; } }
        @keyframes badge-pop {
            0%   { opacity:0; transform: translateY(18px) scale(.92); }
            100% { opacity:1; transform: translateY(0) scale(1); }
        }

        /* LEADERBOARD + MOTIVATION — glossy red & black card with a spotlight */
        .lb-card {
            background:
                radial-gradient(ellipse 55% 42% at 50% -6%, rgba(255, 255, 255, 0.22), transparent 62%),
                linear-gradient(rgba(255, 255, 255, 0.05) 1.5px, transparent 1.5px) 0 0 / 42px 42px,
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1.5px, transparent 1.5px) 0 0 / 42px 42px,
                linear-gradient(155deg, #c0392b 0%, #7B1D1D 45%, #1b0808 100%);
            color: #fff;
            box-shadow: 0 12px 28px rgba(15, 4, 4, 0.45);
            position: relative;
            overflow: hidden;
        }

        /* Keep all content above the spotlight beam */
        .lb-card > * {
            position: relative;
            z-index: 1;
        }

        /* Spotlight beam shining down from the top onto the leaderboard */
        .lb-card::before {
            content: '';
            position: absolute;
            top: -70px;
            left: 50%;
            width: 300px;
            height: 400px;
            transform: translateX(-50%);
            background: linear-gradient(180deg,
                rgba(255, 255, 255, 0.38) 0%,
                rgba(255, 240, 205, 0.20) 28%,
                rgba(255, 255, 255, 0.05) 62%,
                transparent 88%);
            clip-path: polygon(41% 0, 59% 0, 100% 100%, 0 100%);
            filter: blur(9px);
            pointer-events: none;
            z-index: 0;
        }

        /* Bright light source glow where the beam originates */
        .lb-card::after {
            content: '';
            position: absolute;
            top: -34px;
            left: 50%;
            width: 130px;
            height: 90px;
            transform: translateX(-50%);
            background: radial-gradient(ellipse at center, rgba(255, 248, 225, 0.55), transparent 70%);
            filter: blur(4px);
            pointer-events: none;
            z-index: 0;
        }

        .lb-card .panel-title { color: #fff; }
        .lb-card .panel-title .title-doodle { color: #ffd76a; }

        .lb-card .leaderboard-select {
            background: rgba(0, 0, 0, 0.35);
            border-color: rgba(255, 255, 255, 0.25);
            color: #fff;
        }

        .lb-card .leaderboard-select option { color: #333; background: #fff; }

        .lb-motivation {
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px dashed rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
        }

        .lb-motivation-art {
            width: 62px;
            height: 62px;
            flex-shrink: 0;
            object-fit: contain;
            background: #fff;
            border-radius: 50%;
            padding: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .lb-motivation h4 {
            font-size: 14.5px;
            font-weight: 700;
            color: #fff;
            line-height: 1.4;
        }

        .lb-motivation p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.75);
            margin-top: 2px;
        }

        .lb-motivation-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            margin-top: 14px;
            background: linear-gradient(180deg, #ffffff, #f3e3e3);
            color: #7B1D1D;
            padding: 12px;
            border-radius: 24px;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.9);
            position: relative;
        }

        .lb-motivation-btn:hover {
            background: linear-gradient(180deg, #ffffff, #fdeaea);
            transform: translateY(-2px);
        }

        /* LEADERBOARD */
        .leaderboard-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .leaderboard-select {
            padding: 7px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 12px;
            color: #555;
            font-family: 'Poppins', sans-serif;
            background: white;
            cursor: pointer;
        }

        /* Podium (top 3) */
        .podium {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 22px;
            padding: 18px 0 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
            margin-bottom: 8px;
        }

        .podium-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .podium-avatar {
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #7B1D1D;
            background: #fdeaea;
            position: relative;
        }

        .podium-col.first .podium-avatar {
            width: 74px;
            height: 74px;
            font-size: 22px;
            border: 4px solid #f4b740;
            box-shadow: 0 0 0 4px rgba(244, 183, 64, 0.35);
        }

        .podium-col.second .podium-avatar {
            width: 56px;
            height: 56px;
            font-size: 17px;
            border: 3px solid #c3c9cf;
            box-shadow: 0 0 0 3px rgba(195, 201, 207, 0.35);
        }

        .podium-col.third .podium-avatar {
            width: 56px;
            height: 56px;
            font-size: 17px;
            border: 3px solid #d99a63;
            box-shadow: 0 0 0 3px rgba(217, 154, 99, 0.35);
        }

        .podium-crown {
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%) rotate(-6deg);
            font-size: 20px;
            color: #f4b740;
        }

        .podium-rank {
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 20px;
            height: 20px;
            border-radius: 50%;
            color: white;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255, 255, 255, 0.85);
        }

        .podium-col.first  .podium-rank { background: #f4b740; }
        .podium-col.second .podium-rank { background: #b9bfc6; }
        .podium-col.third  .podium-rank { background: #d9915c; }

        .leaderboard-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 10px;
            border-radius: 10px;
        }

        .leaderboard-row.me {
            background: rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(2px);
        }

        .lb-rank-circle {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }

        .lb-rank-circle.r1 { background: #f4b740; }
        .lb-rank-circle.r2 { background: #f0a04b; }
        .lb-rank-circle.r3 { background: #e8873a; }
        .lb-rank-circle.rn { background: rgba(255, 255, 255, 0.28); color: #fff; }

        .leaderboard-learner {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.92);
            font-weight: 500;
        }

        .me-avatar {
            width: 26px;
            height: 26px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            color: #7B1D1D;
        }

        .leaderboard-row.me .leaderboard-learner {
            font-weight: 600;
            color: #fff;
        }

        .lb-score {
            font-size: 14px;
            font-weight: 700;
            color: #ffd76a;
        }

        /* EXAM COUNTDOWN CARD */
        .exam-card {
            background: linear-gradient(150deg, #fdf1f1, #fbe3e3);
            border: 1.5px solid #f2c7c7;
            border-radius: 16px;
            padding: 24px 22px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin-top: 25px;
        }

        .exam-card-label {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #c0392b;
            text-transform: uppercase;
        }

        .exam-card-sub {
            font-size: 13px;
            color: #a07070;
            margin-bottom: 12px;
        }

        .exam-digits {
            display: inline-flex;
            gap: 8px;
            margin-bottom: 10px;
        }

        .exam-digit {
            width: 52px;
            height: 64px;
            background: #7B1D1D;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            font-weight: 800;
            color: white;
            box-shadow: 0 4px 10px rgba(123, 29, 29, 0.3);
        }

        .exam-days-left {
            font-size: 17px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #7B1D1D;
            margin-bottom: 8px;
        }

        .exam-card-foot {
            font-size: 12px;
            color: #a07070;
        }

        .exam-rocket {
            position: absolute;
            right: 12px;
            bottom: 34px;
            font-size: 34px;
            color: #c0392b;
            transform: rotate(-20deg);
            opacity: 0.9;
        }

        /* QUOTE CARD */
        .quote-card {
            margin-top: 25px;
            position: relative;
            overflow: hidden;
        }

        .quote-mark {
            font-size: 30px;
            color: #c0392b;
            line-height: 1;
            margin-bottom: 8px;
        }

        .quote-text {
            font-size: 17px;
            font-weight: 700;
            color: #333;
            line-height: 1.5;
            margin-bottom: 10px;
            max-width: 210px;
        }

        .quote-author {
            font-size: 13px;
            color: #999;
        }

        .quote-plant {
            position: absolute;
            right: 16px;
            bottom: 12px;
            font-size: 42px;
            color: #c0392b;
            opacity: 0.75;
        }

        /* RESPONSIVE */
        /* Breakpoints account for the ~230px fixed sidebar eating into content width */
        @media (max-width: 1480px) {
            .banner-mascot { display: none; }
        }

        @media (max-width: 1300px) {
            .achievements-layout {
                grid-template-columns: 1fr;
            }
            .status-banner {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
                gap: 22px;
            }
            .banner-profile {
                justify-content: center;
                align-self: auto;
                padding-right: 0;
                padding-bottom: 22px;
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            }
            .banner-right {
                justify-content: center;
                padding-left: 0;
            }
            .banner-stats { justify-content: center; }
        }

        @media (max-width: 900px) {
            .badge-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 70px; padding: 20px 16px; }
            .sidebar { width: 70px; }
            .header { flex-direction: column; align-items: flex-start; gap: 12px; }
            .header-right { width: 100%; flex-wrap: wrap; }
            .search-box { flex: 1; min-width: 0; }
            .header-title { font-size: 22px; }
            .status-banner { padding: 20px; gap: 18px; }
            .banner-stats { flex-direction: column; gap: 12px; }
            .stat-box { min-width: unset; width: 100%; }
            .badge-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        }

        @media (max-width: 480px) {
            .main-content { padding: 16px 12px; }
            .header-title { font-size: 20px; }
            .badge-grid { grid-template-columns: 1fr; }
            .panel { padding: 18px; }
            .badge-tabs { gap: 6px; }
            .badge-tab { padding: 6px 12px; font-size: 12px; }
            .exam-digit { width: 44px; height: 56px; font-size: 30px; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        @include('partials.sidebar', ['active' => 'achievements'])
        @include('partials.student-bottom-nav', ['active' => 'achievements'])
        @include('partials.student-mobile-header')

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- HEADER -->
            <div class="header">
                <div class="header-left">
                    <img src="{{ asset('images/12.png') }}" alt="" class="title-trophy">
                    <div>
                        <div class="header-title">
                            Achievements
                            <i class="fas fa-arrow-trend-up title-doodle"></i>
                        </div>
                        <svg class="title-underline" width="170" height="10" viewBox="0 0 170 10" fill="none">
                            <path d="M2 7 Q 45 1 85 5 T 168 4" stroke="#c0392b" stroke-width="3" stroke-linecap="round" fill="none"/>
                        </svg>
                        <div class="header-subtitle">Celebrate your progress and compete with others.</div>
                    </div>
                </div>
                <div class="header-right">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search topics, questions...">
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

            <!-- STATUS BANNER -->
            @php $status = $leaderboard['status']; @endphp
            <div class="status-banner">
                <div class="banner-shape-1"></div>
                <div class="banner-shape-2"></div>
                <div class="banner-shape-3"></div>
                <div class="banner-shape-4"></div>
                <div class="banner-shape-5"></div>
                <div class="banner-shape-6"></div>
                <div class="banner-shape-7"></div>
                <div class="banner-shape-8"></div>
                <div class="banner-profile">
                    <div class="banner-avatar-wrap">
                        <i class="fas fa-crown banner-crown"></i>
                        <div class="banner-avatar">{{ strtoupper(substr($user->first_name,0,1) . substr($user->last_name,0,1)) }}</div>
                    </div>
                    <div>
                        <div class="banner-name">{{ $user->name }}
                            <svg class="banner-name-doodle" width="120" height="12" viewBox="0 0 120 12" fill="none" style="display:block;margin-top:-2px;">
                                <path d="M2 8 Q 15 2 30 7 T 60 5 T 90 8 T 118 4" stroke="rgba(255,215,106,0.45)" stroke-width="2.5" stroke-linecap="round" fill="none"/>
                            </svg>
                        </div>
                        <div class="banner-role">Reviewer <span class="banner-doodle-star">&#10023;</span></div>
                        <span class="banner-tag"><i class="fas fa-star" style="font-size:10px;"></i> CPALE Aspirant</span>
                    </div>
                </div>

                <div class="banner-right">
                    <div class="banner-status">
                        <div class="banner-status-label">
                            Your Status
                            <svg width="80" height="8" viewBox="0 0 80 8" fill="none" style="display:block;margin:0 auto;">
                                <path d="M2 5 Q 12 1 22 5 T 42 4 T 62 6 T 78 3" stroke="rgba(255,215,106,0.35)" stroke-width="2" stroke-linecap="round" fill="none"/>
                            </svg>
                        </div>
                        @if($status['ranked'])
                            <div class="banner-status-value">#{{ $status['rank'] }}</div>
                            <div class="banner-status-sub">You're in the <strong>top {{ $status['percentile'] }}%</strong> of {{ $status['total'] }} active reviewers.</div>
                        @else
                            <div class="banner-status-value">&mdash;</div>
                            <div class="banner-status-sub">Complete a quiz to join the leaderboard.</div>
                        @endif
                    </div>

                    <div class="banner-stats">
                        <div class="stat-box">
                            <div class="stat-box-top">
                                <div class="stat-icon trophy"><i class="fas fa-trophy"></i></div>
                                <div class="stat-label">Badges Earned</div>
                            </div>
                            <div class="stat-value">{{ $earnedCount }}</div>
                            <div class="stat-extra">of {{ $totalCount }} total</div>
                            <div class="stat-bar"><span style="width: {{ $totalCount ? round($earnedCount / $totalCount * 100) : 0 }}%;"></span></div>
                        </div>
                        <div class="stat-box days">
                            <div class="stat-box-top">
                                <div class="stat-icon drop"><i class="fas fa-droplet"></i></div>
                                <div class="stat-label">Days Active</div>
                            </div>
                            <div class="stat-value">{{ $activeDays }}</div>
                            @if($streak > 0)
                                <div class="stat-extra">{{ $streak }}-day streak going!</div>
                            @else
                                <div class="stat-extra">Study today to start a streak!</div>
                            @endif
                            <div class="stat-bar"><span style="width: {{ min(100, round($activeDays / 30 * 100)) }}%;"></span></div>
                        </div>
                    </div>

                    <!-- Mascot: CPA chick reading a book -->
                    <!-- Climb-to-the-trophy illustration -->
                    <div class="banner-mascot">
                        <svg class="mascot-sparkle-1" width="24" height="24" viewBox="0 0 24 24" fill="none" style="position:absolute;top:8px;right:12px;z-index:2;">
                            <path d="M12 2 L14 9 L21 9 L15 14 L17 21 L12 17 L7 21 L9 14 L3 9 L10 9 Z" fill="rgba(255,215,106,0.35)" stroke="rgba(255,215,106,0.5)" stroke-width="1"/>
                        </svg>
                        <svg class="mascot-sparkle-2" width="16" height="16" viewBox="0 0 24 24" fill="none" style="position:absolute;bottom:14px;left:8px;z-index:2;">
                            <path d="M12 2 L14 9 L21 9 L15 14 L17 21 L12 17 L7 21 L9 14 L3 9 L10 9 Z" fill="rgba(192,57,43,0.3)" stroke="rgba(255,255,255,0.2)" stroke-width="1"/>
                        </svg>
                        <img src="{{ asset('images/11.png') }}" alt="Climb to the top">
                    </div>
                </div>
            </div>

            <!-- LAYOUT -->
            <div class="achievements-layout">
                <!-- LEFT COLUMN -->
                <div>
                    <!-- BADGES (Vue app) -->
                    <div class="panel" id="badgesApp">
                        <div class="panel-head">
                            <div class="panel-title">
                                <span class="title-emoji">&#127942;</span>
                                Your Badges
                                <i class="fas fa-arrow-trend-up title-doodle"></i>
                            </div>
                            <div class="panel-head-right">
                                <span class="unlocked-count">@{{ earnedCount }} of @{{ totalCount }} unlocked</span>
                                <button class="see-all-btn" v-on:click="seeAll">See All</button>
                            </div>
                        </div>

                        <div class="badge-tabs">
                            <button v-for="c in categoryList" v-bind:key="c.key"
                                    class="badge-tab" v-bind:class="{ active: filter === c.key }"
                                    v-on:click="setFilter(c.key)">@{{ c.label }}</button>
                        </div>

                        <div class="badge-grid">
                            <div v-for="b in visibleBadges" v-bind:key="b.key"
                                 class="badge-card" v-bind:class="[ 'c-' + b.colour, { locked: !b.earned } ]"
                                 v-on:click="onBadgeClick(b, $event)">
                                <div v-if="b.earned" class="earned-chip"><i class="fas fa-check"></i></div>
                                <div v-else class="lock-chip"><i class="fas fa-lock"></i></div>

                                <div class="badge-medal-wrap">
                                    <div class="badge-rays"></div>
                                    <div class="badge-sparkle s1"></div>
                                    <div class="badge-sparkle s2"></div>
                                    <div class="badge-sparkle s3"></div>
                                    <div class="badge-medal"><i class="fas" v-bind:class="b.icon"></i></div>
                                </div>

                                <div class="badge-name">@{{ b.name }}</div>
                                <div class="badge-desc">@{{ b.desc }}</div>
                                <div v-if="b.earned" class="badge-earned">
                                    <i class="fas fa-check-circle"></i> Earned @{{ b.earned_at }}
                                </div>
                                <template v-else>
                                    <div class="badge-progress-track"><span v-bind:style="{ width: b.percent + '%' }"></span></div>
                                    <div class="badge-progress-label">@{{ b.progress }}</div>
                                </template>
                            </div>
                        </div>

                        <div class="badge-empty" v-if="visibleBadges.length === 0"
                             style="text-align:center; color:#999; font-size:13px; padding:20px 0 24px;">
                            <img src="{{ asset('images/13.png') }}" alt="" style="width:110px; height:110px; object-fit:contain; display:block; margin:0 auto 8px;">
                            No badges in this category yet &mdash; keep studying to unlock the surprise!
                        </div>

                        <!-- Badge detail modal -->
                        <teleport to="body">
                            <div class="badge-modal-overlay" v-if="active" v-on:click.self="close">
                                <div class="badge-modal" v-bind:class="'c-' + active.colour">
                                    <button class="badge-modal-close" v-on:click="close">&times;</button>
                                    <div class="badge-cert" ref="cert" v-bind:class="{ locked: !active.earned }">
                                        <span class="cert-spark a">&#10024;</span>
                                        <span class="cert-spark b">&#11088;</span>
                                        <span class="cert-spark c">&#10024;</span>
                                        <div class="cert-brand">CPACE &middot; CPA Reviewer</div>
                                        <div class="badge-medal-wrap">
                                            <div class="badge-rays"></div>
                                            <div class="badge-sparkle s1"></div>
                                            <div class="badge-sparkle s2"></div>
                                            <div class="badge-sparkle s3"></div>
                                            <div class="badge-medal"><i class="fas" v-bind:class="active.icon"></i></div>
                                        </div>
                                        <div class="cert-name">@{{ active.name }}</div>
                                        <div class="cert-desc">@{{ active.desc }}</div>
                                        <div v-if="active.earned" class="cert-status">
                                            <i class="fas fa-check-circle"></i> Earned @{{ active.earned_at }}
                                        </div>
                                        <div v-else class="cert-status locked">
                                            <i class="fas fa-hourglass-half"></i> @{{ active.progress }}
                                        </div>
                                    </div>
                                    <div class="badge-modal-actions">
                                        <button class="badge-save-btn" v-on:click="saveBadge" v-bind:disabled="saving">
                                            <i class="fas fa-download"></i> @{{ saving ? 'Saving…' : 'Save Badge' }}
                                        </button>
                                        <button class="badge-celebrate-btn" v-on:click="celebrate">
                                            <i class="fas fa-star"></i> Celebrate
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </teleport>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div>
                    <!-- LEADERBOARD + MOTIVATION (merged glossy card) -->
                    <div class="panel lb-card">
                        <div class="leaderboard-head">
                            <div class="panel-title" style="font-size: 17px;">
                                Leaderboard
                                <i class="fas fa-arrow-trend-up title-doodle"></i>
                            </div>
                            <select class="leaderboard-select" id="leaderboardPeriod">
                                <option value="month">This Month</option>
                                <option value="week">This Week</option>
                                <option value="all">All Time</option>
                            </select>
                        </div>

                        <div class="podium" id="leaderboardPodium"></div>

                        <div id="leaderboardBody">
                            <!-- rendered by JS from the data below -->
                        </div>

                        <div class="leaderboard-empty" id="leaderboardEmpty" style="display:none; text-align:center; color:rgba(255,255,255,0.75); font-size:13px; padding:24px 0;">
                            No quiz activity in this period yet.
                        </div>

                        <div class="lb-motivation">
                            <img src="{{ asset('images/14.png') }}" alt="" class="lb-motivation-art">
                            <div>
                                <h4>Small steps today, big results tomorrow!</h4>
                                <p>Stay consistent and achieve your goals.</p>
                            </div>
                        </div>
                        <a href="{{ route('review-notes') }}" class="lb-motivation-btn">Go to Reviews <i class="fas fa-arrow-right"></i></a>
                    </div>

                    <!-- CPA BOARD EXAM COUNTDOWN -->
                    <div class="exam-card">
                        <div class="exam-card-label">CPA Board Exam</div>
                        @if(!is_null($daysToExam))
                            <div class="exam-card-sub">In just</div>
                            <div class="exam-digits">
                                @foreach(str_split(str_pad((string) $daysToExam, 3, '0', STR_PAD_LEFT)) as $digit)
                                    <div class="exam-digit">{{ $digit }}</div>
                                @endforeach
                            </div>
                            <div class="exam-days-left">DAYS LEFT!</div>
                            <div class="exam-card-foot">Keep pushing! You're closer than you think!</div>
                        @else
                            <div class="exam-card-sub" style="margin-top:6px;">No exam date set yet.</div>
                            <div class="exam-card-foot">Ask your program chair to set your target exam date to start the countdown.</div>
                        @endif
                        <i class="fas fa-rocket exam-rocket"></i>
                    </div>

                    <!-- QUOTE CARD -->
                    <div class="panel quote-card">
                        <div class="quote-mark"><i class="fas fa-quote-left"></i></div>
                        <div class="quote-text">Discipline today, excellence tomorrow.</div>
                        <div class="quote-author">&mdash; Future CPA</div>
                        <i class="fas fa-seedling quote-plant"></i>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>

        // (Sidebar collapse state is handled by the sidebar partial itself.)

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

        // Badges are now a Vue app (see the #badgesApp script near the end of the page).

        // ── Leaderboard (real data, switchable by period) ──────────────
        const leaderboardData = @json($leaderboard);
        const lbBody   = document.getElementById('leaderboardBody');
        const lbPodium = document.getElementById('leaderboardPodium');
        const lbEmpty  = document.getElementById('leaderboardEmpty');
        const lbSelect = document.getElementById('leaderboardPeriod');

        function escapeHtml(s) {
            const d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        }

        function initials(name) {
            return name.split(/\s+/).filter(Boolean).slice(0, 2).map(w => w[0].toUpperCase()).join('');
        }

        function renderPodium(rows) {
            const top = {};
            rows.forEach(r => { if (r.rank <= 3) top[r.rank] = r; });

            // Podium order: 2nd — 1st — 3rd
            const slots = [
                { rank: 2, cls: 'second' },
                { rank: 1, cls: 'first' },
                { rank: 3, cls: 'third' },
            ];

            lbPodium.innerHTML = slots.map(slot => {
                const r = top[slot.rank];
                if (!r) return '';
                const crown = slot.rank === 1 ? '<i class="fas fa-crown podium-crown"></i>' : '';
                return '<div class="podium-col ' + slot.cls + '">' +
                           '<div class="podium-avatar">' + crown + escapeHtml(initials(r.name)) +
                               '<div class="podium-rank">' + slot.rank + '</div>' +
                           '</div>' +
                       '</div>';
            }).join('');
            lbPodium.style.display = Object.keys(top).length ? 'flex' : 'none';
        }

        function renderLeaderboard(period) {
            const rows = leaderboardData[period] || [];
            if (!rows.length) {
                lbBody.innerHTML = '';
                lbPodium.innerHTML = '';
                lbPodium.style.display = 'none';
                lbEmpty.style.display = 'block';
                return;
            }
            lbEmpty.style.display = 'none';

            renderPodium(rows);

            lbBody.innerHTML = rows.map(r => {
                const circleClass = r.rank <= 3 ? 'r' + r.rank : 'rn';
                const rankCell = '<div class="lb-rank-circle ' + circleClass + '">' + r.rank + '</div>';

                const learner = r.is_me
                    ? '<div class="leaderboard-learner"><span class="me-avatar">' + escapeHtml(r.initials) + '</span>' + escapeHtml(r.name) + '</div>'
                    : '<div class="leaderboard-learner">' + escapeHtml(r.name) + '</div>';

                const score = '<div class="lb-score">' + r.score + '</div>';

                return '<div class="leaderboard-row' + (r.is_me ? ' me' : '') + '">' + rankCell + learner + score + '</div>';
            }).join('');
        }

        if (lbSelect) {
            lbSelect.addEventListener('change', () => renderLeaderboard(lbSelect.value));
        }
        renderLeaderboard('month');
    </script>

    <!-- Badges feature: Vue 3 + confetti (badge image is drawn natively on canvas) -->
    <script src="https://unpkg.com/vue@3.4.38/dist/vue.global.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
    <script>
        window.__BADGES__     = @json($badges);
        window.__CATEGORIES__ = @json($categories);

        (function () {
            if (!window.Vue) { console.warn('Vue failed to load — badges will not be interactive.'); return; }
            const { createApp } = window.Vue;

            const CONFETTI_COLORS = ['#e03131', '#f59e0b', '#22c55e', '#3b82f6', '#a855f7', '#ec4899', '#14b8a6', '#ffd76a'];

            // Per-colour theme: [light, dark, tint] — bright & vivid
            const BADGE_COLORS = {
                red:    ['#ff8a6b', '#ff2740', '#ffe0d9'],
                green:  ['#57f593', '#08d15c', '#d3fce2'],
                blue:   ['#63c0ff', '#1f7bff', '#d7ecff'],
                yellow: ['#ffe24a', '#ffab00', '#fff1bd'],
                purple: ['#d488ff', '#9412f5', '#efdcff'],
                pink:   ['#ff9fdc', '#ff1e8e', '#ffdaee'],
                teal:   ['#3ff7de', '#00c7b1', '#cbfcf4'],
                gray:   ['#cbd2da', '#8b97a6', '#eaedf1'],
            };

            // Read the real FontAwesome glyph for an icon class (avoids hard-coded unicodes).
            function glyphFor(iconClass) {
                const i = document.createElement('i');
                i.className = 'fas ' + iconClass;
                i.style.cssText = 'position:absolute;left:-9999px;font-weight:900';
                document.body.appendChild(i);
                let c = getComputedStyle(i, '::before').content;
                document.body.removeChild(i);
                if (!c || c === 'none' || c === 'normal') return '';
                return c.replace(/^["']|["']$/g, '');
            }

            function roundRect(ctx, x, y, w, h, r) {
                ctx.beginPath();
                ctx.moveTo(x + r, y);
                ctx.arcTo(x + w, y, x + w, y + h, r);
                ctx.arcTo(x + w, y + h, x, y + h, r);
                ctx.arcTo(x, y + h, x, y, r);
                ctx.arcTo(x, y, x + w, y, r);
                ctx.closePath();
            }

            function drawStar(ctx, cx, cy, s, fill) {
                ctx.save();
                ctx.translate(cx, cy);
                ctx.fillStyle = fill;
                ctx.beginPath();
                for (let i = 0; i < 4; i++) {
                    ctx.rotate(Math.PI / 2);
                    ctx.moveTo(0, 0);
                    ctx.quadraticCurveTo(s * 0.32, s * 0.32, s, 0);
                    ctx.quadraticCurveTo(s * 0.32, -s * 0.32, 0, 0);
                }
                ctx.fill();
                ctx.restore();
            }

            function wrapText(ctx, text, cx, y, maxW, lh) {
                const words = String(text).split(' ');
                let line = '', lines = [];
                for (const w of words) {
                    const test = line ? line + ' ' + w : w;
                    if (ctx.measureText(test).width > maxW && line) { lines.push(line); line = w; }
                    else { line = test; }
                }
                if (line) lines.push(line);
                lines.forEach((ln, i) => ctx.fillText(ln, cx, y + i * lh));
                return lines.length;
            }

            // Draw a shareable badge card entirely on a canvas (crisp, dependency-free).
            async function drawBadgeCanvas(badge) {
                const W = 640, H = 582, dpr = 2;
                const canvas = document.createElement('canvas');
                canvas.width = W * dpr;
                canvas.height = H * dpr;
                const ctx = canvas.getContext('2d');
                ctx.scale(dpr, dpr);
                ctx.textAlign = 'center';

                const [b1, b2, tint] = BADGE_COLORS[badge.colour] || BADGE_COLORS.gray;
                const earned = !!badge.earned;

                try { await document.fonts.load('900 70px "Font Awesome 6 Free"'); } catch (e) {}
                try { await document.fonts.load('800 30px Poppins'); await document.fonts.load('400 16px Poppins'); } catch (e) {}

                // Card + top tint
                ctx.fillStyle = '#ffffff';
                roundRect(ctx, 0, 0, W, H, 30); ctx.fill();
                const tg = ctx.createRadialGradient(W / 2, -30, 20, W / 2, -30, 340);
                tg.addColorStop(0, tint); tg.addColorStop(1, 'rgba(255,255,255,0)');
                ctx.fillStyle = tg;
                roundRect(ctx, 0, 0, W, H, 30); ctx.fill();

                // Brand
                ctx.fillStyle = b2;
                ctx.font = '700 15px Poppins, sans-serif';
                ctx.fillText('C P A C E   ·   C P A   R E V I E W E R', W / 2, 58);

                const cx = W / 2, cy = 258, R = 94;

                // Sunburst rays
                if (earned) {
                    ctx.save();
                    ctx.translate(cx, cy);
                    for (let i = 0; i < 24; i++) {
                        ctx.rotate((Math.PI * 2) / 24);
                        ctx.globalAlpha = i % 2 ? 0.10 : 0.28;
                        ctx.fillStyle = b1;
                        ctx.beginPath();
                        ctx.moveTo(0, 0);
                        ctx.lineTo(-15, -(R + 48));
                        ctx.lineTo(15, -(R + 48));
                        ctx.closePath();
                        ctx.fill();
                    }
                    ctx.restore();
                    ctx.globalAlpha = 1;
                }

                // Medallion
                const mg = ctx.createRadialGradient(cx - 32, cy - 36, 12, cx, cy, R);
                mg.addColorStop(0, b1); mg.addColorStop(1, b2);
                ctx.fillStyle = mg;
                ctx.beginPath(); ctx.arc(cx, cy, R, 0, Math.PI * 2); ctx.fill();
                // outer colour ring + white rim
                ctx.lineWidth = 6; ctx.strokeStyle = b2;
                ctx.beginPath(); ctx.arc(cx, cy, R + 3, 0, Math.PI * 2); ctx.stroke();
                ctx.lineWidth = 6; ctx.strokeStyle = 'rgba(255,255,255,0.92)';
                ctx.beginPath(); ctx.arc(cx, cy, R - 3, 0, Math.PI * 2); ctx.stroke();
                // top gloss
                const gl = ctx.createLinearGradient(cx, cy - R, cx, cy + 6);
                gl.addColorStop(0, 'rgba(255,255,255,0.5)'); gl.addColorStop(1, 'rgba(255,255,255,0)');
                ctx.fillStyle = gl;
                ctx.beginPath(); ctx.arc(cx, cy, R - 7, Math.PI, 0); ctx.fill();

                // Icon
                ctx.fillStyle = '#ffffff';
                ctx.font = '900 72px "Font Awesome 6 Free"';
                ctx.textBaseline = 'middle';
                ctx.fillText(glyphFor(badge.icon), cx, cy + 3);
                ctx.textBaseline = 'alphabetic';

                // Sparkles
                if (earned) {
                    drawStar(ctx, cx - 122, cy - 98, 11, '#ffffff');
                    drawStar(ctx, cx + 124, cy - 74, 8, '#ffffff');
                    drawStar(ctx, cx - 118, cy + 76, 9, '#ffffff');
                }

                // Name
                ctx.fillStyle = '#1f1f1f';
                ctx.font = '800 30px Poppins, sans-serif';
                ctx.fillText(badge.name, cx, cy + R + 68);

                // Description
                ctx.fillStyle = '#777777';
                ctx.font = '400 16px Poppins, sans-serif';
                const lines = wrapText(ctx, badge.desc, cx, cy + R + 100, 400, 23);

                // Status pill
                const label = earned ? ('Earned ' + (badge.earned_at || '')) : (badge.progress || 'Locked');
                const gW = 30;
                ctx.font = '700 15px Poppins, sans-serif';
                const pw = ctx.measureText(label).width + gW + 34;
                const px = cx - pw / 2;
                const py = cy + R + 108 + lines * 23 + 8;
                ctx.fillStyle = earned ? '#16a34a' : '#eef0f2';
                roundRect(ctx, px, py, pw, 40, 20); ctx.fill();
                ctx.fillStyle = earned ? '#ffffff' : '#8b97a6';
                ctx.textBaseline = 'middle';
                ctx.font = '900 15px "Font Awesome 6 Free"';
                ctx.textAlign = 'left';
                ctx.fillText(glyphFor(earned ? 'fa-circle-check' : 'fa-hourglass-half'), px + 18, py + 21);
                ctx.font = '700 15px Poppins, sans-serif';
                ctx.fillText(label, px + 18 + gW, py + 21);
                ctx.textAlign = 'center';
                ctx.textBaseline = 'alphabetic';

                return canvas;
            }

            createApp({
                data() {
                    return {
                        badges: window.__BADGES__ || [],
                        categories: window.__CATEGORIES__ || {},
                        filter: 'all',
                        active: null,
                        saving: false,
                    };
                },
                computed: {
                    earnedCount() { return this.badges.filter(b => b.earned).length; },
                    totalCount() { return this.badges.length; },
                    categoryList() {
                        return Object.keys(this.categories).map(key => ({ key, label: this.categories[key] }));
                    },
                    visibleBadges() {
                        return this.filter === 'all'
                            ? this.badges
                            : this.badges.filter(b => b.category === this.filter);
                    },
                },
                methods: {
                    setFilter(key) { this.filter = key; },
                    seeAll() { this.filter = 'all'; },

                    fireConfetti(x, y, big) {
                        if (typeof window.confetti !== 'function') return;
                        const base = {
                            origin: { x, y },
                            colors: CONFETTI_COLORS,
                            zIndex: 10000,
                            disableForReducedMotion: true,
                        };
                        window.confetti(Object.assign({}, base, {
                            particleCount: big ? 150 : 70,
                            spread: big ? 100 : 65,
                            startVelocity: big ? 45 : 32,
                            scalar: big ? 1.05 : 0.9,
                        }));
                        if (big) {
                            setTimeout(() => window.confetti(Object.assign({}, base, {
                                particleCount: 90, spread: 130, startVelocity: 34, scalar: 0.85,
                            })), 160);
                        }
                    },

                    onBadgeClick(badge, event) {
                        const rect = event.currentTarget.getBoundingClientRect();
                        const x = (rect.left + rect.width / 2) / window.innerWidth;
                        const y = (rect.top + rect.height / 2) / window.innerHeight;
                        this.fireConfetti(x, y, badge.earned);
                        this.active = badge;
                    },

                    celebrate() { this.fireConfetti(0.5, 0.42, true); },

                    close() { this.active = null; },

                    async saveBadge() {
                        if (!this.active) return;
                        this.saving = true;
                        try {
                            if (document.fonts && document.fonts.ready) {
                                await document.fonts.ready;
                            }
                            const canvas = await drawBadgeCanvas(this.active);
                            const slug = (this.active.name || 'badge')
                                .replace(/[^a-z0-9]+/gi, '-')
                                .replace(/^-+|-+$/g, '')
                                .toLowerCase();
                            const link = document.createElement('a');
                            link.download = 'CPACE-badge-' + slug + '.png';
                            link.href = canvas.toDataURL('image/png');
                            document.body.appendChild(link);
                            link.click();
                            link.remove();
                        } catch (err) {
                            console.error('Badge save failed:', err);
                            alert('Sorry, the badge could not be saved.');
                        } finally {
                            this.saving = false;
                        }
                    },
                },
            }).mount('#badgesApp');
        })();
    </script>
</body>
</html>
