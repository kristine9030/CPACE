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

        :root {
            --maroon: #7B1D1D;
            --accent: #c0392b;
            --accent-soft: #fff5f5;
            --ink: #1f2430;
            --muted: #6b7280;
            --line: #e8eaef;
            --bg: #f4f5f7;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--ink);
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

        .sidebar.collapsed { width: 70px; }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 20px 30px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 30px;
        }

        .sidebar.collapsed .sidebar-logo { padding: 0 10px 30px 10px; }
        .sidebar-logo-icon { font-size: 32px; }
        .sidebar-logo-text { font-size: 14px; line-height: 1.3; font-family: 'Poppins', sans-serif; }
        .sidebar.collapsed .sidebar-logo-text { display: none; }
        .sidebar-logo-text strong { display: block; font-size: 13px; font-weight: 700; }

        .sidebar-nav { list-style: none; }
        .sidebar-nav li { margin: 0; }

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

        .sidebar.collapsed .sidebar-nav a { padding: 12px 10px; justify-content: center; gap: 0; }
        .sidebar.collapsed .sidebar-nav a span { display: none; }
        .sidebar-nav a:hover { color: white; background: rgba(255, 255, 255, 0.1); }
        .sidebar-nav a.active { color: white; background: rgba(255, 255, 255, 0.15); border-left-color: white; }
        .sidebar-nav i { margin-right: 8px; width: 18px; text-align: center; }
        .sidebar.collapsed .sidebar-nav i { margin-right: 0; }

        .sidebar-footer {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            padding: 0 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
        }

        .sidebar.collapsed .sidebar-footer { padding: 0 10px; }
        .sidebar-challenge { margin-bottom: 60px; padding: 0 20px; }
        .sidebar.collapsed .sidebar-challenge { display: none; }

        .challenge-box {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .challenge-box p { font-size: 12px; color: white; margin-bottom: 10px; font-weight: 600; }

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

        .challenge-box a:hover { background: #f9f9f9; transform: translateY(-2px); }

        .user-avatar { display: flex; align-items: center; gap: 10px; color: white; font-size: 13px; }
        .sidebar.collapsed .user-avatar { flex-direction: column; gap: 5px; }

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

        .user-info-sidebar { flex: 1; font-family: 'Poppins', sans-serif; }
        .sidebar.collapsed .user-info-sidebar { display: none; }
        .user-info-sidebar .name { display: block; font-weight: 600; font-size: 13px; }
        .user-info-sidebar .role { display: block; font-size: 11px; opacity: 0.8; }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 211px;
            padding: 30px 40px 120px;
            overflow-y: auto;
            transition: margin-left 0.3s ease;
        }

        .sidebar.collapsed ~ .main-content { margin-left: 70px; }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            gap: 20px;
        }

        .header-left { flex: 1; display: flex; align-items: center; gap: 20px; }
        .header-title { font-size: 28px; font-weight: 700; color: var(--ink); font-family: 'Poppins', sans-serif; }
        .header-subtitle { color: var(--muted); font-size: 14px; margin-top: 2px; }
        .header-right { display: flex; align-items: center; gap: 20px; }

        .search-box { flex: 0 1 300px; }
        .search-box input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--line);
            border-radius: 8px;
            font-size: 13px;
            background: white;
            color: #666;
            font-family: 'Poppins', sans-serif;
        }

        .header-icons { display: flex; gap: 15px; align-items: center; }
        .icon-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            position: relative;
            color: #7B1D1D;
        }
        .icon-btn:hover { background: #f0f0f0; }

        .badge {
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

        .profile-avatar {
            width: 40px;
            height: 40px;
            background: #7B1D1D;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            position: relative;
            font-family: 'Poppins', sans-serif;
        }
        .profile-avatar:hover { background: #6a1818; }

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

        /* ─────────────────────────────────────────────
           PROGRESS PIPELINE (the guide rail)
        ───────────────────────────────────────────── */
        .pipeline {
            background: linear-gradient(135deg, #1a0a0a 0%, #3d0c0c 30%, #7B1D1D 60%, #a12626 100%);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 18px;
            padding: 26px 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(123, 29, 29, .28);
            position: relative;
            overflow: hidden;
        }
        /* faint decorative grid, echoing the achievements banner */
        .pipeline::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px) 0 0 / 38px 38px,
                linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px) 0 0 / 38px 38px;
            mask-image: linear-gradient(to right, rgba(0,0,0,.6), transparent 60%);
            -webkit-mask-image: linear-gradient(to right, rgba(0,0,0,.6), transparent 60%);
            pointer-events: none;
        }
        .pipeline-track {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            position: relative;
            z-index: 1;
        }
        .pipe-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            flex: 1;
            position: relative;
            z-index: 2;
        }
        /* connector line between steps */
        .pipe-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 21px;
            left: 50%;
            width: 100%;
            height: 3px;
            background: rgba(255,255,255,.15);
            z-index: -1;
            transition: background .35s ease;
        }
        .pipe-step.done:not(:last-child)::after {
            background: linear-gradient(90deg, #34d67e, #ffd76a);
        }
        .pipe-dot {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255,255,255,.10);
            color: rgba(255,255,255,.7);
            border: 3px solid rgba(255,255,255,.22);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
            transition: all .3s ease;
        }
        .pipe-step.active .pipe-dot {
            background: linear-gradient(135deg, #ffd76a, #f5a623);
            border-color: #ffe9a8;
            color: #5a3600;
            box-shadow: 0 0 0 5px rgba(255,215,106,.22);
        }
        .pipe-step.done .pipe-dot {
            background: linear-gradient(135deg, #34d67e, #16a34a);
            border-color: #7ff0ad;
            color: #fff;
        }
        .pipe-label {
            font-size: 12px;
            font-weight: 600;
            color: rgba(255,255,255,.65);
            margin-top: 10px;
            line-height: 1.3;
        }
        .pipe-step.active .pipe-label,
        .pipe-step.done .pipe-label { color: #fff; }
        .pipe-sub {
            font-size: 11px;
            color: rgba(255,255,255,.5);
            margin-top: 2px;
            font-weight: 500;
            max-width: 130px;
        }
        .pipe-step.active .pipe-sub { color: #ffd76a; font-weight: 600; }
        .pipe-step.done .pipe-sub { color: #7ff0ad; font-weight: 600; }

        /* pipeline card header + sublabels */
        .pipeline-header { position: relative; z-index: 1; margin-bottom: 22px; }
        .pipeline-header h2 {
            color: #fff; font-size: 18px; font-weight: 700;
            display: flex; align-items: center; gap: 10px;
        }
        .pipeline-header h2 i { color: #ffd76a; }
        .pipeline-header p { color: rgba(255,255,255,.62); font-size: 13px; margin-top: 5px; }
        .pipe-hint {
            font-size: 10px;
            color: rgba(255,255,255,.42);
            margin-top: 3px;
            font-weight: 500;
            letter-spacing: .2px;
        }
        .pipe-step.active .pipe-hint { color: rgba(255,235,180,.75); }

        /* abstract decorative shapes on the pipeline card */
        .pipe-shape { position: absolute; border-radius: 50%; pointer-events: none; z-index: 0; }
        .pipe-shape.s1 { width: 200px; height: 200px; right: -60px; top: -80px;
            background: radial-gradient(circle at 32% 30%, rgba(255,215,106,.28), transparent 70%); }
        .pipe-shape.s2 { width: 130px; height: 130px; right: 150px; bottom: -70px;
            background: radial-gradient(circle at 32% 30%, rgba(255,255,255,.12), transparent 70%); }
        .pipe-shape.s3 { width: 100px; height: 100px; left: -34px; bottom: -46px;
            background: radial-gradient(circle at 32% 30%, rgba(230,80,80,.45), transparent 70%); }
        .pipe-ring { position: absolute; border: 2px solid rgba(255,255,255,.09); border-radius: 50%; pointer-events: none; z-index: 0; }
        .pipe-ring.r1 { width: 240px; height: 240px; right: -90px; top: -100px; }
        .pipe-ring.r2 { width: 66px; height: 66px; left: 38%; top: -34px; }
        .pipe-tri {
            position: absolute; z-index: 0; pointer-events: none;
            width: 0; height: 0; opacity: .5;
            left: 62%; bottom: 14px;
            border-left: 16px solid transparent;
            border-right: 16px solid transparent;
            border-bottom: 26px solid rgba(255,215,106,.14);
            transform: rotate(18deg);
        }

        /* BIG START QUIZ BUTTON */
        .start-quiz-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 11px;
            background: linear-gradient(135deg, #16a34a, #059669);
            color: #fff; border: none; cursor: pointer;
            font-family: 'Poppins', sans-serif; font-size: 16px; font-weight: 700;
            padding: 15px 40px; border-radius: 12px;
            box-shadow: 0 8px 22px rgba(22,163,74,.35);
            transition: all .2s; white-space: nowrap;
        }
        .start-quiz-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(22,163,74,.48); }
        .start-quiz-btn:disabled { background: #d7dbe0; color: #9aa0aa; cursor: not-allowed; box-shadow: none; }
        .start-quiz-btn i { font-size: 14px; }

        /* ── layout for step + supporting rail ── */
        .flow-layout {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 28px;
            align-items: start;
        }

        /* ── STEP CARDS ── */
        .step-card {
            background: white;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 24px 26px;
            margin-bottom: 22px;
            box-shadow: 0 2px 10px rgba(20,25,40,.04);
            scroll-margin-top: 20px;
            position: relative;
            overflow: hidden;
            border-top: 3px solid var(--step-color, var(--accent));
        }
        .step-head {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 4px;
        }
        .step-badge {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: var(--step-color, var(--accent));
            color: #fff;
            font-weight: 700;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 10px var(--step-shadow, rgba(192,57,43,.28));
        }
        /* per-step accent colors — catchy + guides the eye down the flow */
        #step1 { --step-color: #4A90E2; --step-shadow: rgba(74,144,226,.3); }
        #step2 { --step-color: #9B59B6; --step-shadow: rgba(155,89,182,.3); }
        #step3 { --step-color: #F39C12; --step-shadow: rgba(243,156,18,.3); }
        #step4 { --step-color: #16a34a; --step-shadow: rgba(22,163,74,.3); }
        .step-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--ink);
        }
        .step-req { color: var(--accent); font-size: 13px; }
        .step-desc {
            font-size: 13px;
            color: var(--muted);
            margin: 6px 0 20px 48px;
            line-height: 1.5;
        }
        @media (max-width: 560px) { .step-desc { margin-left: 0; } }

        /* CHOOSE MODE GRID */
        .choose-mode-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .mode-card {
            background: white;
            border-radius: 12px;
            padding: 18px;
            border: 2px solid var(--line);
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            font-family: 'Poppins', sans-serif;
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }
        .mode-card:hover { border-color: var(--accent); box-shadow: 0 4px 12px rgba(0,0,0,.07); }
        .mode-card.active { border-color: var(--accent); background: var(--accent-soft); }
        .mode-card.active::before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 14px;
            right: 14px;
            width: 22px;
            height: 22px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 11px;
        }
        .mode-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: var(--accent-soft);
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        /* catchy per-mode icon colors */
        .choose-mode-grid .mode-card:nth-child(1) .mode-icon { background: #e7f0fd; color: #4A90E2; }
        .choose-mode-grid .mode-card:nth-child(2) .mode-icon { background: #e6f7f4; color: #17A2B8; }
        .choose-mode-grid .mode-card:nth-child(3) .mode-icon { background: #fef3e2; color: #F39C12; }
        .choose-mode-grid .mode-card:nth-child(4) .mode-icon { background: #f3eaf9; color: #9B59B6; }
        .mode-title { font-size: 14px; font-weight: 700; color: var(--ink); margin-bottom: 4px; }
        .mode-description { font-size: 12px; color: var(--muted); line-height: 1.45; }

        /* SESSION TYPE */
        .session-type-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .stype-card {
            background: #fff;
            border: 2px solid var(--line);
            border-radius: 12px;
            padding: 18px 20px;
            cursor: pointer;
            transition: all .2s;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            position: relative;
            font-family: 'Poppins', sans-serif;
        }
        .stype-card:hover { border-color: var(--accent); box-shadow: 0 4px 12px rgba(0,0,0,.07); }
        .stype-card.active { border-color: var(--accent); background: var(--accent-soft); }
        .stype-card.active::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute; top: 12px; right: 14px;
            width: 22px; height: 22px;
            background: var(--accent); color: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 10px;
        }
        .stype-icon {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0;
        }
        .stype-icon.training { background: #d1fae5; color: #059669; }
        .stype-icon.testing  { background: #dbeafe; color: #2563eb; }
        .stype-title { font-size: 14px; font-weight: 700; color: #1a1a1a; margin-bottom: 3px; }
        .stype-desc  { font-size: 12px; color: var(--muted); line-height: 1.5; }
        .stype-badge {
            display: inline-block; margin-top: 8px;
            font-size: 10px; font-weight: 700;
            padding: 3px 9px; border-radius: 20px;
        }
        .stype-badge.training { background: #d1fae5; color: #065f46; }
        .stype-badge.testing  { background: #dbeafe; color: #1e40af; }

        /* QUIZ LENGTH SELECTOR */
        .length-row { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
        .length-options { display: flex; gap: 10px; flex-wrap: wrap; }
        .length-chip {
            background: white;
            border: 2px solid var(--line);
            border-radius: 10px;
            min-width: 54px;
            padding: 11px 18px;
            font-size: 14px;
            font-weight: 600;
            color: #555;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Poppins', sans-serif;
        }
        .length-chip:hover { border-color: var(--accent); }
        .length-chip.active { border-color: var(--accent); background: var(--accent-soft); color: var(--accent); }
        .length-custom {
            width: 100px;
            padding: 11px 12px;
            border: 2px solid var(--line);
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #555;
            font-family: 'Poppins', sans-serif;
        }
        .length-custom:focus { outline: none; border-color: var(--accent); }

        /* SUBJECT CARDS GRID */
        .subject-lock-note {
            background: #fff7ed;
            color: #b45309;
            padding: 13px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        /* subject multi-select toolbar */
        .subject-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .subject-quick { display: flex; gap: 10px; flex-wrap: wrap; }
        .quick-btn {
            display: inline-flex; align-items: center; gap: 7px;
            background: #fff; border: 1.5px solid var(--line);
            border-radius: 9px; padding: 8px 14px;
            font-size: 12.5px; font-weight: 600; color: #4b5563;
            cursor: pointer; font-family: 'Poppins', sans-serif;
            transition: all .2s;
        }
        .quick-btn:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-soft); }
        .selected-count {
            display: inline-flex; align-items: center; gap: 7px;
            font-size: 13px; font-weight: 700; color: #16a34a;
        }
        .selected-count i { color: #16a34a; }
        .selected-count.none { color: #9aa0aa; }
        .selected-count.none i { color: #cfd3da; }

        .subject-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        .subject-grid.locked { opacity: .6; pointer-events: none; }
        .subject-cell { display: block; cursor: pointer; margin: 0; }
        .subject-cell.disabled { cursor: not-allowed; opacity: .55; }
        .subject-select-card {
            display: block;
            background: white;
            border-radius: 14px;
            padding: 22px 18px;
            text-align: center;
            border: 2px solid var(--line);
            transition: all 0.25s;
            font-family: 'Poppins', sans-serif;
            position: relative;
            overflow: hidden;
        }
        .subject-select-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: var(--card-accent, var(--maroon));
            opacity: 0;
            transition: opacity .25s;
        }
        .subject-cell:not(.disabled):hover .subject-select-card {
            transform: translateY(-4px);
            box-shadow: 0 10px 22px rgba(0, 0, 0, 0.10);
            border-color: var(--card-accent, var(--accent));
        }
        .subject-cell:not(.disabled):hover .subject-select-card::before { opacity: 1; }
        .subject-select-icon {
            width: 56px; height: 56px;
            margin: 0 auto 14px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
        }
        .subject-select-name { display: block; font-size: 17px; font-weight: 700; color: var(--ink); margin-bottom: 4px; }
        .subject-select-full { display: block; font-size: 12px; color: var(--muted); margin-bottom: 14px; line-height: 1.4; min-height: 34px; }
        .questions-count {
            font-size: 12px;
            color: #4b5563;
            font-weight: 600;
            background: #f3f4f6;
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
        }
        .subject-start-hint {
            margin-top: 12px;
            font-size: 12px;
            font-weight: 700;
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            opacity: 0;
            transition: opacity .2s;
        }
        .subject-cell:not(.disabled):hover .subject-start-hint { opacity: .5; }
        /* selected subject (checkbox drives the styling) */
        .subject-check:checked + .subject-select-card {
            border-color: var(--card-accent, var(--accent));
            background: #fbfdff;
            box-shadow: 0 10px 22px rgba(0,0,0,.10);
        }
        .subject-check:checked + .subject-select-card::before { opacity: 1; }
        .subject-check:checked + .subject-select-card .subject-start-hint { opacity: 1; }
        .subject-check:checked + .subject-select-card::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free'; font-weight: 900;
            position: absolute; top: 12px; right: 12px;
            width: 22px; height: 22px; border-radius: 50%;
            background: var(--card-accent, var(--accent)); color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 11px;
        }

        /* rail history list (replaces How It Works) */
        .rail-history-item {
            display: flex; align-items: center; gap: 12px;
            padding: 13px 0; border-bottom: 1px solid #f2f3f5;
            text-decoration: none;
        }
        .rail-history-item:last-of-type { border-bottom: none; }
        .rail-hist-icon {
            width: 38px; height: 38px; border-radius: 10px;
            background: #ffe8e8; color: #c0392b;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; flex-shrink: 0;
        }
        .rail-hist-body { flex: 1; min-width: 0; }
        .rail-hist-title { font-size: 13px; font-weight: 700; color: var(--ink); }
        .rail-hist-meta { font-size: 11px; color: var(--muted); margin-top: 2px; }
        .rail-hist-score { font-size: 15px; font-weight: 700; flex-shrink: 0; }
        .rail-history-btn {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            margin-top: 16px; background: #7B1D1D; color: #fff; text-decoration: none;
            padding: 11px; border-radius: 10px; font-size: 13px; font-weight: 600;
            transition: background .2s;
        }
        .rail-history-btn:hover { background: #6a1818; }
        .rail-empty { text-align: center; color: #aaa; font-size: 12px; padding: 14px 0; }

        /* ── SUPPORTING RAIL (stats) ── */
        .rail-card {
            background: white;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(20,25,40,.04);
            margin-bottom: 22px;
        }
        .rail-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .rail-title i { color: var(--accent); }

        .mastery-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 22px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .mastery-circle-inner {
            width: 122px;
            height: 122px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        .mastery-percentage { font-size: 32px; font-weight: 700; color: var(--ink); }
        .mastery-label { font-size: 11px; color: var(--muted); }

        .stat-item { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; font-size: 12px; }
        .stat-dot { width: 10px; height: 10px; border-radius: 50%; }
        .stat-label { flex: 1; color: var(--muted); }
        .stat-value { color: var(--ink); font-weight: 600; }

        .stats-grid-sidebar {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 22px;
            padding-top: 22px;
            border-top: 1px solid #f0f0f0;
        }
        .stat-box { text-align: center; }
        .stat-box-value { font-size: 22px; font-weight: 700; color: var(--ink); margin-bottom: 4px; }
        .stat-box-label { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: .3px; }

        /* how it works – inside rail */
        .hiw-step {
            display: flex;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f2f3f5;
        }
        .hiw-step:last-child { border-bottom: none; padding-bottom: 0; }
        .hiw-num {
            width: 26px; height: 26px;
            border-radius: 8px;
            background: var(--maroon);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .hiw-title { font-size: 13px; font-weight: 700; color: var(--ink); margin-bottom: 2px; }
        .hiw-desc { font-size: 12px; color: var(--muted); line-height: 1.45; }

        /* ── RECENT QUIZZES ── */
        .section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 16px;
            flex-wrap: wrap;
        }
        .section-title { font-size: 18px; font-weight: 700; color: var(--ink); font-family: 'Poppins', sans-serif; }
        .section-subtitle { font-size: 13px; color: var(--muted); margin-top: 2px; }
        .history-btn {
            display: inline-flex; align-items: center; gap: 8px;
            background: #7B1D1D; color: #fff; text-decoration: none;
            padding: 10px 18px; border-radius: 8px;
            font-size: 13px; font-weight: 600; font-family: 'Poppins', sans-serif;
            transition: background .2s;
        }
        .history-btn:hover { background: #6a1818; }

        .continue-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }
        .continue-card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            border: 1px solid var(--line);
            transition: all 0.25s;
            font-family: 'Poppins', sans-serif;
        }
        .continue-card:hover { transform: translateY(-4px); box-shadow: 0 10px 22px rgba(0, 0, 0, 0.08); }
        .continue-header { display: flex; gap: 12px; margin-bottom: 15px; align-items: center; }
        .continue-icon {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .continue-title { font-size: 14px; font-weight: 700; color: var(--ink); }
        .continue-type { font-size: 11px; color: var(--muted); margin-top: 2px; }
        .continue-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 12px;
            border-top: 1px solid #f2f3f5;
        }
        .continue-btn {
            color: var(--accent);
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .continue-timestamp { font-size: 11px; color: #9aa0aa; }

        /* ── STICKY SUMMARY BAR (the finish line) ── */
        .summary-bar {
            position: fixed;
            bottom: 0;
            left: 211px;
            right: 0;
            background: #ffffff;
            border-top: 1px solid var(--line);
            box-shadow: 0 -4px 20px rgba(20,25,40,.08);
            padding: 14px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            z-index: 900;
            transition: left 0.3s ease;
        }
        .sidebar.collapsed ~ .summary-bar { left: 70px; }
        .summary-chips { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .sum-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f4f5f7;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 13px;
            color: var(--ink);
            font-weight: 600;
        }
        .sum-chip i { color: var(--accent); font-size: 13px; }
        .sum-chip .sum-key { color: var(--muted); font-weight: 500; font-size: 11px; text-transform: uppercase; letter-spacing: .3px; }
        .sum-chip.pending { color: #9aa0aa; }
        .sum-chip.pending i { color: #cfd3da; }
        .summary-cta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
            white-space: nowrap;
        }
        .summary-cta .ready { color: #27AE60; }
        .summary-cta i { font-size: 15px; }

        /* RESPONSIVE */
        @media (max-width: 1150px) {
            .flow-layout { grid-template-columns: 1fr; }
            .subject-grid { grid-template-columns: repeat(2, 1fr); }
            .continue-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 20px 16px 130px; }
            .sidebar { width: 70px; }
            .summary-bar { left: 0; padding: 12px 16px; flex-direction: column; align-items: stretch; }
            .summary-cta { justify-content: center; }
            .header { flex-direction: column; align-items: flex-start; gap: 12px; }
            .header-right { width: 100%; flex-wrap: wrap; }
            .search-box { flex: 1; min-width: 0; }
            .header-title { font-size: 22px; }
            .choose-mode-grid { grid-template-columns: 1fr; }
            .session-type-row { grid-template-columns: 1fr; }
            .subject-grid { grid-template-columns: repeat(2, 1fr); }
            .continue-grid { grid-template-columns: 1fr; }
            .pipe-label { font-size: 11px; }
            .pipe-sub { display: none; }
            .pipeline { padding: 18px 14px; }
        }
        @media (max-width: 480px) {
            .subject-grid { grid-template-columns: 1fr; }
            .pipe-dot { width: 38px; height: 38px; font-size: 14px; }
            .pipe-step:not(:last-child)::after { top: 18px; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        @include('partials.sidebar', ['active' => 'quizzes'])
        @include('partials.student-bottom-nav', ['active' => 'quizzes'])
        @include('partials.student-mobile-header')

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- HEADER -->
            <div class="header">
                <div class="header-left">
                    <div>
                        <div class="header-title">Adaptive Quizzes</div>
                        <div class="header-subtitle">Follow the 4 steps below to build your practice session.</div>
                    </div>
                </div>
                <div class="header-right">
                    <div class="search-box gs-wrap">
                        <input type="text" data-gs="true" placeholder="Search topics, questions...">
                    </div>
                    <div class="header-icons">
                        <button class="icon-btn" onclick="window.location.href='{{ route('messages.index') }}'" title="Messages" aria-label="Messages">
                            <i class="fas fa-comment-dots"></i>
                            @if($unreadMessages > 0)<span class="badge">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>@endif
                        </button>
                        <button class="icon-btn" onclick="window.location.href='{{ route('notifications.index') }}'" title="Notifications" aria-label="Notifications">
                            <i class="fas fa-bell"></i>
                            @if($unreadNotifications > 0)<span class="badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>@endif
                        </button>
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
            </div>

            <!-- ── PROGRESS PIPELINE ── -->
            <div class="pipeline">
                <!-- abstract decorative shapes -->
                <span class="pipe-shape s1"></span>
                <span class="pipe-shape s2"></span>
                <span class="pipe-shape s3"></span>
                <span class="pipe-ring r1"></span>
                <span class="pipe-ring r2"></span>
                <span class="pipe-tri"></span>

                <div class="pipeline-header">
                    <h2><i class="fas fa-diagram-project"></i> Your Quiz Pipeline</h2>
                    <p>Follow the 4 steps below &mdash; each choice updates here live before you start.</p>
                </div>

                <div class="pipeline-track">
                    <div class="pipe-step done" id="pipe-1" data-target="step1">
                        <div class="pipe-dot"><i class="fas fa-chart-line"></i></div>
                        <div class="pipe-label">Step 1 &middot; Mode</div>
                        <div class="pipe-sub" id="pipeSub1">Adaptive</div>
                        <div class="pipe-hint">How questions are picked</div>
                    </div>
                    <div class="pipe-step done" id="pipe-2" data-target="step2">
                        <div class="pipe-dot"><i class="fas fa-sliders"></i></div>
                        <div class="pipe-label">Step 2 &middot; Format</div>
                        <div class="pipe-sub" id="pipeSub2">Training</div>
                        <div class="pipe-hint">Learn or exam-style</div>
                    </div>
                    <div class="pipe-step active" id="pipe-3" data-target="step3">
                        <div class="pipe-dot"><i class="fas fa-list-ol"></i></div>
                        <div class="pipe-label">Step 3 &middot; Length</div>
                        <div class="pipe-sub" id="pipeSub3">Choose count</div>
                        <div class="pipe-hint">Number of questions</div>
                    </div>
                    <div class="pipe-step" id="pipe-4" data-target="step4">
                        <div class="pipe-dot"><i class="fas fa-play"></i></div>
                        <div class="pipe-label">Step 4 &middot; Subject</div>
                        <div class="pipe-sub" id="pipeSub4">Locked</div>
                        <div class="pipe-hint">Pick &amp; start quiz</div>
                    </div>
                </div>
            </div>

            <div class="flow-layout">
                <!-- LEFT: the guided steps -->
                <div>
                    <!-- STEP 1 : MODE -->
                    <div class="step-card" id="step1">
                        <div class="step-head">
                            <div class="step-badge">1</div>
                            <div class="step-title">Choose Your Mode</div>
                        </div>
                        <div class="step-desc">How should the questions be picked? This shapes your whole session.</div>
                        <div class="choose-mode-grid">
                            <div class="mode-card active" data-mode="adaptive" data-name="Adaptive" onclick="selectMode(this)">
                                <div class="mode-icon"><i class="fas fa-chart-line"></i></div>
                                <div>
                                    <div class="mode-title">Adaptive Mode</div>
                                    <div class="mode-description">Focuses more questions on your weak areas.</div>
                                </div>
                            </div>
                            <div class="mode-card" data-mode="topic" data-name="Topic" onclick="selectMode(this)">
                                <div class="mode-icon"><i class="fas fa-book-open"></i></div>
                                <div>
                                    <div class="mode-title">Topic Mode</div>
                                    <div class="mode-description">Drill one topic at a time to build mastery.</div>
                                </div>
                            </div>
                            <div class="mode-card" data-mode="timed" data-name="Timed" onclick="selectMode(this)">
                                <div class="mode-icon"><i class="fas fa-clock"></i></div>
                                <div>
                                    <div class="mode-title">Timed Mode</div>
                                    <div class="mode-description">Beat the clock - auto-submits when time runs out.</div>
                                </div>
                            </div>
                            <div class="mode-card" data-mode="challenge" data-name="Challenge" onclick="selectMode(this)">
                                <div class="mode-icon"><i class="fas fa-trophy"></i></div>
                                <div>
                                    <div class="mode-title">Challenge Mode</div>
                                    <div class="mode-description">Hardest questions first, for 1.5&times; points.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2 : SESSION TYPE -->
                    <div class="step-card" id="step2">
                        <div class="step-head">
                            <div class="step-badge">2</div>
                            <div class="step-title">Pick a Format</div>
                        </div>
                        <div class="step-desc">Do you want to learn as you go, or simulate the real exam?</div>
                        <div class="session-type-row">
                            <div class="stype-card active" data-stype="training" data-name="Training" onclick="selectStype(this)">
                                <div class="stype-icon training"><i class="fas fa-brain"></i></div>
                                <div class="stype-body">
                                    <div class="stype-title">Training Mode</div>
                                    <div class="stype-desc">See the correct answer immediately after each question. Great for learning.</div>
                                    <span class="stype-badge training">Instant Feedback</span>
                                </div>
                            </div>
                            <div class="stype-card" data-stype="testing" data-name="Testing" onclick="selectStype(this)">
                                <div class="stype-icon testing"><i class="fas fa-file-alt"></i></div>
                                <div class="stype-body">
                                    <div class="stype-title">Testing Mode</div>
                                    <div class="stype-desc">Answer all questions first, then review results at the end. Simulates real exam.</div>
                                    <span class="stype-badge testing">Exam Simulation</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3 : LENGTH -->
                    <div class="step-card" id="step3">
                        <div class="step-head">
                            <div class="step-badge">3</div>
                            <div class="step-title">How Many Questions? <span class="step-req">*</span></div>
                        </div>
                        <div class="step-desc">Pick a length you can finish in one sitting - this unlocks the subjects below.</div>
                        <div class="length-row">
                            <div class="length-options">
                                <button type="button" class="length-chip" data-count="5" onclick="selectLength(this, 5)">5</button>
                                <button type="button" class="length-chip" data-count="10" onclick="selectLength(this, 10)">10</button>
                                <button type="button" class="length-chip" data-count="20" onclick="selectLength(this, 20)">20</button>
                                <button type="button" class="length-chip" data-count="30" onclick="selectLength(this, 30)">30</button>
                                <button type="button" class="length-chip" data-count="50" onclick="selectLength(this, 50)">50</button>
                            </div>
                            <input type="number" min="1" max="100" class="length-custom" id="lengthCustom" placeholder="Custom" oninput="customLength(this.value)">
                            <span id="lengthChosen" style="font-size:13px;font-weight:700;color:#27AE60;display:none;"><i class="fas fa-check-circle"></i> <span id="lengthChosenNum"></span> questions</span>
                        </div>
                    </div>

                    <!-- STEP 4 : SUBJECT -->
                    <div class="step-card" id="step4">
                        <div class="step-head">
                            <div class="step-badge">4</div>
                            <div class="step-title">Select Subjects to Start</div>
                        </div>
                        <div class="step-desc">Pick <strong>one or more</strong> subjects &mdash; mix 2, 3, or all of them for a combined quiz &mdash; then hit the big <strong>Start Quiz</strong> button.</div>

                        @if(session('error') || $errors->any())
                            <div style="background:#fee2e2;color:#b91c1c;padding:12px 18px;border-radius:10px;margin-bottom:18px;font-size:13px;font-weight:600;">
                                <i class="fas fa-exclamation-circle"></i> {{ session('error') ?? $errors->first() }}
                            </div>
                        @endif

                        <div id="lengthHint" class="subject-lock-note">
                            <i class="fas fa-lock"></i> Choose how many questions in Step 3 to unlock the subjects.
                        </div>

                        @php
                            $subjectIcons = [
                                'FAR' => ['fa-chart-line', '#4A90E2'], 'AFAR' => ['fa-coins', '#17A2B8'],
                                'MS' => ['fa-gears', '#F39C12'], 'TAX' => ['fa-file-invoice-dollar', '#27AE60'],
                                'AUD' => ['fa-magnifying-glass', '#c0392b'], 'RFBT' => ['fa-scale-balanced', '#9B59B6'],
                            ];
                        @endphp
                        <form method="POST" action="{{ route('quiz.start') }}" id="quizForm">
                            @csrf
                            <input type="hidden" name="mode" value="adaptive" class="mode-input">
                            <input type="hidden" name="count" value="" class="count-input">
                            <input type="hidden" name="session_type" value="training" class="stype-input">

                            <div class="subject-toolbar" id="subjectToolbar">
                                <div class="subject-quick">
                                    <button type="button" class="quick-btn" onclick="selectAllSubjects()"><i class="fas fa-layer-group"></i> Select all</button>
                                    <button type="button" class="quick-btn" onclick="clearAllSubjects()"><i class="fas fa-xmark"></i> Clear</button>
                                </div>
                                <span class="selected-count" id="selectedCount"><i class="fas fa-check-circle"></i> 0 selected</span>
                            </div>

                            <div class="subject-grid locked" id="subjectGrid">
                                @foreach($subjects as $subject)
                                    @php [$icon, $color] = $subjectIcons[$subject->code] ?? ['fa-book', '#7B1D1D']; @endphp
                                    <label class="subject-cell {{ $subject->question_count === 0 ? 'disabled' : '' }}">
                                        <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}"
                                               class="subject-check" hidden
                                               data-qcount="{{ $subject->question_count }}"
                                               data-name="{{ $subject->code }}"
                                               onchange="onSubjectChange()"
                                               {{ $subject->question_count === 0 ? 'disabled' : '' }}>
                                        <span class="subject-select-card" style="--card-accent: {{ $color }};">
                                            <span class="subject-select-icon" style="color: {{ $color }}; background: {{ $color }}1a;"><i class="fas {{ $icon }}"></i></span>
                                            <span class="subject-select-name">{{ $subject->code }}</span>
                                            <span class="subject-select-full">{{ $subject->name }}</span>
                                            <span class="questions-count">{{ number_format($subject->question_count) }} Questions</span>
                                            <span class="subject-start-hint"><i class="fas fa-check"></i> Selected</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </form>
                    </div>
                </div>

                <!-- RIGHT: supporting rail -->
                <aside>
                    <!-- Stats -->
                    <div class="rail-card">
                        <div class="rail-title"><i class="fas fa-gauge-high"></i> Your Adaptive Stats</div>

                        @php
                            $masteryGradient = $mastery['has_data']
                                ? "conic-gradient(#17A2B8 0deg {$mastery['strong_deg']}deg, #F39C12 {$mastery['strong_deg']}deg {$mastery['medium_deg']}deg, #c0392b {$mastery['medium_deg']}deg 360deg)"
                                : 'conic-gradient(#e5e7eb 0deg 360deg)';
                        @endphp
                        <div class="mastery-circle" style="background: {{ $masteryGradient }};">
                            <div class="mastery-circle-inner">
                                <div class="mastery-percentage">{{ $accuracy }}%</div>
                                <div class="mastery-label">Overall Mastery</div>
                            </div>
                        </div>

                        @if($mastery['has_data'])
                            <div class="stat-item">
                                <div class="stat-dot" style="background: #17A2B8;"></div>
                                <span class="stat-label">Strong</span>
                                <span class="stat-value">{{ $mastery['strong'] }}%</span>
                            </div>
                            <div class="stat-item">
                                <div class="stat-dot" style="background: #F39C12;"></div>
                                <span class="stat-label">Medium</span>
                                <span class="stat-value">{{ $mastery['medium'] }}%</span>
                            </div>
                            <div class="stat-item">
                                <div class="stat-dot" style="background: #c0392b;"></div>
                                <span class="stat-label">Weak</span>
                                <span class="stat-value">{{ $mastery['weak'] }}%</span>
                            </div>
                        @else
                            <div style="text-align:center;color:#aaa;font-size:12px;padding:6px 0 4px;">
                                <i class="fas fa-chart-pie"></i> Take a quiz to see your strong, medium, and weak areas.
                            </div>
                        @endif

                        <div class="stats-grid-sidebar">
                            <div class="stat-box">
                                <div class="stat-box-value">{{ number_format($totalAttempted) }}</div>
                                <div class="stat-box-label">Attempted</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-box-value">{{ $accuracy }}%</div>
                                <div class="stat-box-label">Accuracy</div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent quizzes / history -->
                    <div class="rail-card">
                        <div class="rail-title"><i class="fas fa-clock-rotate-left"></i> Recent Quizzes</div>
                        @php
                            $scoreColor = fn($p) => $p >= 75 ? '#16a34a' : ($p >= 50 ? '#F39C12' : '#c0392b');
                        @endphp
                        @forelse($recentSessions as $rs)
                            @php $pct = (int) round($rs->score_percent); @endphp
                            <a href="{{ route('quiz.results', $rs->id) }}" class="rail-history-item">
                                <div class="rail-hist-icon"><i class="fas fa-file-alt"></i></div>
                                <div class="rail-hist-body">
                                    <div class="rail-hist-title">{{ $rs->subject->code ?? 'Mixed' }} Quiz</div>
                                    <div class="rail-hist-meta">{{ $rs->correct_answers }}/{{ $rs->total_items }} &bull; {{ \Illuminate\Support\Carbon::parse($rs->completed_at)->diffForHumans() }}</div>
                                </div>
                                <div class="rail-hist-score" style="color: {{ $scoreColor($pct) }};">{{ $pct }}%</div>
                            </a>
                        @empty
                            <div class="rail-empty">
                                <i class="fas fa-clipboard-list"></i><br>
                                No quizzes yet. Finish your first one to see it here!
                            </div>
                        @endforelse
                        <a href="{{ route('quiz.history') }}" class="rail-history-btn">
                            <i class="fas fa-clock-rotate-left"></i> View Full History
                        </a>
                    </div>
                </aside>
            </div>
        </main>

        <!-- ── STICKY SUMMARY BAR ── -->
        <div class="summary-bar">
            <div class="summary-chips">
                <div class="sum-chip"><i class="fas fa-chart-line"></i> <span class="sum-key">Mode</span> <span id="sumMode">Adaptive</span></div>
                <div class="sum-chip"><i class="fas fa-sliders"></i> <span class="sum-key">Format</span> <span id="sumStype">Training</span></div>
                <div class="sum-chip pending" id="sumCountChip"><i class="fas fa-list-ol"></i> <span class="sum-key">Length</span> <span id="sumCount">Not set</span></div>
                <div class="sum-chip pending" id="sumSubjectChip"><i class="fas fa-book"></i> <span class="sum-key">Subject</span> <span id="sumSubject">Not set</span></div>
            </div>
            <button type="submit" form="quizForm" class="start-quiz-btn" id="startQuizBtn" disabled>
                <i class="fas fa-play"></i> Start Quiz
            </button>
        </div>
    </div>

    <script>

        // Always show fresh stats: if this page is restored from the browser's
        // back/forward cache (e.g. the student finished a quiz and navigated
        // back), force a reload so the server re-renders the latest numbers.
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) location.reload();
        });

        // Load sidebar state
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            const sb = document.querySelector('.sidebar');
            if (sb) sb.classList.add('collapsed');
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

        // Clicking a pipeline step scrolls to its section.
        document.querySelectorAll('.pipe-step').forEach(step => {
            step.style.cursor = 'pointer';
            step.addEventListener('click', () => {
                const t = document.getElementById(step.dataset.target);
                if (t) t.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        // ── Session type (training / testing) ──
        function applyStype(stype) {
            let name = 'Training';
            document.querySelectorAll('.stype-card').forEach(c => {
                const on = c.dataset.stype === stype;
                c.classList.toggle('active', on);
                if (on) name = c.dataset.name;
            });
            document.querySelectorAll('.stype-input').forEach(i => { i.value = stype; });
            localStorage.setItem('quizStype', stype);
            document.getElementById('sumStype').textContent = name;
            document.getElementById('pipeSub2').textContent = name;
        }
        function selectStype(el) { applyStype(el.dataset.stype); }

        // ── Select Mode ── highlight the card + push into every subject form.
        function applyMode(mode) {
            let name = 'Adaptive';
            document.querySelectorAll('.mode-card').forEach(card => {
                const on = card.dataset.mode === mode;
                card.classList.toggle('active', on);
                if (on) name = card.dataset.name;
            });
            document.querySelectorAll('.mode-input').forEach(input => { input.value = mode; });
            localStorage.setItem('quizMode', mode);
            document.getElementById('sumMode').textContent = name;
            document.getElementById('pipeSub1').textContent = name;
        }
        function selectMode(element) { applyMode(element.dataset.mode || 'adaptive'); }

        // ── Quiz length ── the student MUST pick a length before any subject can
        // start. The choice is intentionally NOT remembered between visits.
        function applyLength(count) {
            count = Math.max(1, Math.min(parseInt(count, 10) || 0, 100));
            if (!count) return;
            document.querySelectorAll('.count-input').forEach(input => { input.value = count; });

            let matched = false;
            document.querySelectorAll('.length-chip').forEach(chip => {
                const on = parseInt(chip.dataset.count, 10) === count;
                chip.classList.toggle('active', on);
                if (on) matched = true;
            });
            const custom = document.getElementById('lengthCustom');
            if (matched && document.activeElement !== custom) custom.value = '';

            document.getElementById('lengthChosenNum').textContent = count;
            document.getElementById('lengthChosen').style.display = 'inline';
            updateSubjectsEnabled(count);
        }

        // Track the two required choices for the big Start button.
        let currentCount = 0;

        // Enable subjects only when a length is chosen; a subject with 0
        // questions stays disabled either way.
        function updateSubjectsEnabled(count) {
            currentCount = count || 0;
            const hasCount = !!count;
            document.getElementById('lengthHint').style.display = hasCount ? 'none' : 'flex';
            document.getElementById('subjectGrid').classList.toggle('locked', !hasCount);

            document.querySelectorAll('.subject-check').forEach(chk => {
                const hasQuestions = parseInt(chk.dataset.qcount, 10) > 0;
                const enabled = hasCount && hasQuestions;
                chk.disabled = !enabled;
                chk.closest('.subject-cell').classList.toggle('disabled', !enabled);
                if (!enabled) chk.checked = false; // drop locked/empty picks
            });

            // Length chip + pipeline step 3
            const sumCountChip = document.getElementById('sumCountChip');
            const pipe3 = document.getElementById('pipe-3');
            if (hasCount) {
                document.getElementById('sumCount').textContent = count + ' questions';
                sumCountChip.classList.remove('pending');
                pipe3.classList.remove('active'); pipe3.classList.add('done');
                document.getElementById('pipeSub3').textContent = count + ' questions';
            } else {
                document.getElementById('sumCount').textContent = 'Not set';
                sumCountChip.classList.add('pending');
                pipe3.classList.add('active'); pipe3.classList.remove('done');
                document.getElementById('pipeSub3').textContent = 'Choose count';
            }
            onSubjectChange();
        }

        // ── Subject multi-select (checkboxes drive it; the big button submits) ──
        function onSubjectChange() {
            const all     = document.querySelectorAll('.subject-check');
            const checked = document.querySelectorAll('.subject-check:checked');
            const n = checked.length;

            // "N selected" badge
            const badge = document.getElementById('selectedCount');
            badge.innerHTML = '<i class="fas fa-check-circle"></i> ' + n + ' selected';
            badge.classList.toggle('none', n === 0);

            // Friendly label for the summary chip + pipeline sublabel
            let label;
            if (n === 0)               label = 'Not set';
            else if (n === 1)          label = checked[0].dataset.name;
            else if (n === all.length) label = 'All subjects';
            else                       label = n + ' subjects';

            document.getElementById('sumSubject').textContent = label;
            document.getElementById('sumSubjectChip').classList.toggle('pending', n === 0);

            // Pipeline step 4
            const pipe4 = document.getElementById('pipe-4');
            const sub4  = document.getElementById('pipeSub4');
            pipe4.classList.remove('active', 'done');
            if (n > 0)             { pipe4.classList.add('done'); sub4.textContent = label; }
            else if (currentCount) { pipe4.classList.add('active'); sub4.textContent = 'Pick subject'; }
            else                   { sub4.textContent = 'Locked'; }

            document.getElementById('startQuizBtn').disabled = !(currentCount && n > 0);
        }

        function selectAllSubjects() {
            if (document.getElementById('subjectGrid').classList.contains('locked')) return;
            document.querySelectorAll('.subject-check').forEach(chk => { if (!chk.disabled) chk.checked = true; });
            onSubjectChange();
        }
        function clearAllSubjects() {
            document.querySelectorAll('.subject-check').forEach(chk => { chk.checked = false; });
            onSubjectChange();
        }

        function selectLength(element, count) {
            document.getElementById('lengthCustom').value = '';
            applyLength(count);
        }
        function customLength(value) { if (value !== '') applyLength(value); }

        // ── init ──
        applyStype(localStorage.getItem('quizStype') || 'training');
        applyMode(localStorage.getItem('quizMode') || 'adaptive');
        updateSubjectsEnabled(0); // start locked
    </script>
    @include('partials.global-search')
</body>
</html>
