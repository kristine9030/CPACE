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
        :root {
            --primary: #7B1D1D;
            --primary-hover: #6a1818;
            --grad: linear-gradient(135deg, #a32d2d 0%, #7B1D1D 55%, #5c1111 100%);
            --accent-red: #c0392b;
            --green: #21a366;
            --green-text: #178a53;
            --blue: #3b7ddd;
            --amber: #e8910b;
            --purple: #8e5bd0;
            --ink: #2b2b2b;
            --ink-2: #555555;
            --ink-3: #999999;
            --line: #edeef2;
            --card-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f6f7f9;
            color: var(--ink);
        }

        .dashboard-container {
            display: block;
            min-height: 100vh;
        }

        /* ─── HEADER ─── */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            gap: 20px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.2;
        }

        .page-title .spark { color: var(--accent-red); font-size: 20px; }

        .page-subtitle {
            font-size: 13px;
            color: var(--ink-3);
            margin-top: 2px;
        }

        .page-header-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .search-wrap { position: relative; }

        .search-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--ink-3);
            font-size: 13px;
        }

        .search-wrap input {
            width: 300px;
            padding: 10px 14px 10px 38px;
            border: 1px solid var(--line);
            border-radius: 24px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            background: white;
            color: var(--ink-2);
            outline: none;
            transition: border-color 0.2s;
        }

        .search-wrap input:focus { border-color: var(--primary); }
        .search-wrap input::placeholder { color: #b7bcc2; }

        .notif-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border: 1px solid var(--line);
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            color: var(--ink-2);
            cursor: pointer;
            box-shadow: var(--card-shadow);
            transition: background 0.2s;
        }

        .notif-btn:hover { background: #f1f2f4; }

        .notification-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            min-width: 17px;
            height: 17px;
            padding: 0 4px;
            background: var(--accent-red);
            color: white;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9.5px;
            font-weight: 700;
        }

        .profile-btn {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border: none;
            border-radius: 50%;
            color: white;
            font-weight: 700;
            cursor: pointer;
            font-size: 13px;
            position: relative;
            font-family: 'Poppins', sans-serif;
        }

        .profile-btn:hover { background: var(--primary-hover); }

        .dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: white;
            border: 1px solid var(--line);
            border-radius: 10px;
            min-width: 185px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
            display: none;
            z-index: 2000;
            font-family: 'Poppins', sans-serif;
        }

        .dropdown-menu.active { display: block; }

        .dropdown-menu a,
        .dropdown-menu button {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 16px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            color: var(--ink);
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: background 0.2s;
            border-bottom: 1px solid #f5f5f5;
        }

        .dropdown-menu a:hover,
        .dropdown-menu button:hover { background: #f8f9fa; }

        .dropdown-menu a i,
        .dropdown-menu button i {
            color: var(--primary);
            width: 16px;
            text-align: center;
        }

        .dropdown-menu .logout-btn { color: #e53e3e; }
        .dropdown-menu .logout-btn i { color: #e53e3e; }

        /* ─── LAYOUT ─── */
        .cal-layout {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 18px;
            align-items: start;
        }

        .cal-left,
        .cal-right {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .card {
            background: white;
            border-radius: 14px;
            border: 1px solid var(--line);
            box-shadow: var(--card-shadow);
            padding: 22px;
        }

        /* ─── CALENDAR TOOLBAR ─── */
        .cal-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .month-label {
            font-size: 19px;
            font-weight: 700;
            color: var(--ink);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .month-label i { font-size: 12px; color: var(--ink-3); }

        .today-btn {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--ink);
            text-decoration: underline;
            padding: 6px 8px;
            border-radius: 8px;
        }

        .today-btn:hover { background: #f5f5f5; }

        .nav-btn {
            width: 32px;
            height: 32px;
            border: 1px solid var(--line);
            background: white;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--ink-2);
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }

        .nav-btn:hover { background: #f5f5f5; }

        .toolbar-spacer { flex: 1; }

        .view-toggle {
            display: flex;
            background: #f2f3f5;
            border-radius: 10px;
            padding: 4px;
            gap: 2px;
        }

        .view-toggle button {
            border: none;
            background: transparent;
            padding: 7px 16px;
            font-size: 12.5px;
            font-weight: 500;
            color: var(--ink-2);
            font-family: 'Poppins', sans-serif;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .view-toggle button.active {
            background: var(--grad);
            color: white;
            font-weight: 600;
        }

        .tool-btn {
            width: 34px;
            height: 34px;
            border: 1px solid var(--line);
            background: white;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--ink-2);
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }

        .tool-btn:hover { background: #f5f5f5; }

        .tool-btn.solid {
            background: var(--grad);
            color: white;
            border-color: var(--primary);
        }

        .tool-btn.solid:hover { filter: brightness(1.12); }

        /* ─── WEEK STRIP ─── */
        .week-strip {
            display: grid;
            grid-template-columns: 52px repeat(7, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }

        .strip-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 1px solid var(--line);
            border-radius: 12px;
            color: var(--accent-red);
            font-size: 16px;
        }

        .strip-icon .bar {
            width: 22px;
            height: 3px;
            border-radius: 2px;
            background: var(--accent-red);
        }

        .strip-day {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 10px 6px;
            text-align: center;
            text-decoration: none;
            color: var(--ink);
            background: white;
            transition: all 0.15s;
            position: relative;
        }

        .strip-day:hover { border-color: #d8bcbc; }

        .strip-day .dw {
            display: block;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.6px;
            color: var(--ink-3);
        }

        .strip-day .dn {
            display: block;
            font-size: 19px;
            font-weight: 700;
            margin: 2px 0;
        }

        .strip-day .dot {
            display: block;
            width: 5px;
            height: 5px;
            background: var(--accent-red);
            border-radius: 50%;
            margin: 0 auto 3px;
        }

        .strip-day .tc {
            display: block;
            font-size: 10.5px;
            color: var(--ink-3);
        }

        .strip-day.active {
            background: var(--grad);
            border-color: var(--primary);
            color: white;
            box-shadow: 0 6px 14px rgba(123, 29, 29, 0.35);
        }

        .strip-day.active .dw,
        .strip-day.active .tc { color: rgba(255, 255, 255, 0.8); }
        .strip-day.active .dot { background: white; }

        .strip-day.active::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: -9px;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: var(--primary);
        }

        /* ─── ALL DAY ROW ─── */
        .allday-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 0 12px;
            border-bottom: 1px solid var(--line);
            margin-bottom: 4px;
            flex-wrap: wrap;
        }

        .allday-lbl {
            width: 44px;
            font-size: 10.5px;
            color: var(--ink-3);
            flex-shrink: 0;
        }

        .allday-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
        }

        .allday-chip .cdot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        /* ─── TIME GRID (WEEK / DAY) ─── */
        .time-grid {
            display: grid;
            grid-template-columns: 52px 1fr;
        }

        .hours-col {
            display: flex;
            flex-direction: column;
        }

        .hours-col span {
            height: 48px;
            font-size: 10.5px;
            color: var(--ink-3);
            text-align: right;
            padding-right: 10px;
            transform: translateY(-6px);
        }

        .grid-cols {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            position: relative;
            border-left: 1px solid #f1f3f5;
        }

        .grid-cols.single { grid-template-columns: 1fr; }

        .grid-col {
            position: relative;
            border-right: 1px solid #f1f3f5;
            background-image: repeating-linear-gradient(
                to bottom,
                #f1f3f5 0, #f1f3f5 1px, transparent 1px, transparent 48px
            );
            height: 576px; /* 12 hours x 48px */
        }

        .grid-col.today-col { background-color: #fdf7f7; }

        .cal-event {
            position: absolute;
            left: 5px;
            right: 5px;
            border-radius: 9px;
            padding: 7px 9px;
            font-size: 10.5px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07);
            cursor: default;
            transition: transform 0.15s, box-shadow 0.15s;
        }

        .cal-event:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.13);
            z-index: 5;
        }

        .cal-event .ehead {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 700;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .cal-event .edot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .cal-event .etitle {
            font-weight: 600;
            color: var(--ink);
            line-height: 1.25;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .cal-event .etime {
            font-size: 9.5px;
            color: var(--ink-3);
            margin-top: 2px;
        }

        /* current time line */
        .now-line {
            position: absolute;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--accent-red);
            z-index: 6;
            display: none;
        }

        .now-line::after {
            content: '';
            position: absolute;
            left: -4px;
            top: -3px;
            width: 8px;
            height: 8px;
            background: var(--accent-red);
            border-radius: 50%;
        }

        .now-pill {
            position: absolute;
            left: -52px;
            top: -9px;
            background: var(--accent-red);
            color: white;
            font-size: 9px;
            font-weight: 700;
            border-radius: 5px;
            padding: 2px 5px;
            white-space: nowrap;
        }

        /* ─── MONTH VIEW ─── */
        .month-grid { width: 100%; }

        .month-head {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            margin-bottom: 6px;
        }

        .month-head span {
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: 0.6px;
            color: var(--ink-3);
            text-align: center;
            padding: 6px 0;
        }

        .month-week {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }

        .month-cell {
            border: 1px solid #f1f3f5;
            min-height: 92px;
            padding: 6px;
            font-size: 11px;
        }

        .month-cell .num {
            font-size: 11.5px;
            font-weight: 600;
            margin-bottom: 4px;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .month-cell.muted .num { color: #c4c8cd; }

        .month-cell.today .num {
            background: var(--grad);
            color: white;
        }

        .month-ev {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 9.5px;
            font-weight: 600;
            border-radius: 5px;
            padding: 2px 5px;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .month-ev .cdot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .month-more {
            font-size: 9.5px;
            color: var(--ink-3);
            padding-left: 4px;
        }

        /* ─── BOTTOM STATS BAR ─── */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            background: white;
            border-radius: 14px;
            border: 1px solid var(--line);
            box-shadow: var(--card-shadow);
            padding: 16px 10px;
        }

        .stat-cell {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
            padding: 4px 8px;
        }

        .stat-cell + .stat-cell { border-left: 1px solid var(--line); }

        .stat-cell i {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .stat-cell i.red    { background: #fdeaea; color: var(--accent-red); }
        .stat-cell i.amber  { background: #fef3e2; color: var(--amber); }
        .stat-cell i.green  { background: #e8f7ee; color: var(--green); }
        .stat-cell i.blue   { background: #e9f1fd; color: var(--blue); }
        .stat-cell i.purple { background: #f0eafb; color: var(--purple); }

        .stat-cell .lbl {
            font-size: 11px;
            font-weight: 600;
            color: var(--ink-2);
            line-height: 1.25;
        }

        .stat-cell .val {
            font-size: 12px;
            color: var(--ink-3);
        }

        .stat-cell .val strong {
            font-size: 15px;
            color: var(--ink);
        }

        /* ─── EXAM CARD ─── */
        .exam-card {
            border-radius: 14px;
            padding: 22px;
            color: white;
            background:
                radial-gradient(circle at 85% 12%, rgba(255,255,255,0.12) 0 6px, transparent 7px),
                radial-gradient(circle at 12% 25%, rgba(255,255,255,0.10) 0 4px, transparent 5px),
                radial-gradient(circle at 70% 80%, rgba(255,255,255,0.08) 0 5px, transparent 6px),
                linear-gradient(150deg, #8f2222 0%, #6d1616 60%, #591111 100%);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .exam-kicker {
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: 0.6px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #ffd9d9;
            margin-bottom: 8px;
        }

        .exam-sub { font-size: 12px; color: rgba(255,255,255,0.8); }

        .exam-days {
            font-size: 56px;
            font-weight: 700;
            line-height: 1.05;
        }

        .exam-days-lbl {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .exam-note {
            font-size: 11.5px;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 12px;
        }

        .exam-bar {
            height: 7px;
            background: rgba(255, 255, 255, 0.22);
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .exam-bar span {
            display: block;
            height: 100%;
            background: white;
            border-radius: 5px;
        }

        .exam-date { font-size: 12px; color: rgba(255, 255, 255, 0.85); font-weight: 600; }

        /* ─── SIDE CARDS ─── */
        .side-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            gap: 10px;
        }

        .side-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--ink);
        }

        .side-title small {
            font-size: 11px;
            font-weight: 500;
            color: var(--ink-3);
        }

        .count-pill {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--accent-red);
            background: #fdeaea;
            border-radius: 12px;
            padding: 3px 10px;
            white-space: nowrap;
        }

        .view-all {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--accent-red);
            background: none;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }

        .view-all:hover { text-decoration: underline; }

        .review-item {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            padding: 9px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .review-item:last-of-type { border-bottom: none; }

        .review-item .rdot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-top: 5px;
            flex-shrink: 0;
        }

        .review-info { flex: 1; min-width: 0; }

        .review-name {
            font-size: 12px;
            font-weight: 600;
            color: var(--ink);
            line-height: 1.35;
        }

        .review-name .sc { color: var(--ink-2); }

        .prio {
            display: inline-flex;
            font-size: 9px;
            font-weight: 700;
            border-radius: 9px;
            padding: 2px 8px;
            margin-top: 4px;
        }

        .prio.high   { background: #fdeaea; color: var(--accent-red); }
        .prio.medium { background: #fef3e2; color: var(--amber); }
        .prio.low    { background: #e8f7ee; color: var(--green-text); }

        .review-items-count {
            font-size: 11px;
            color: var(--ink-3);
            white-space: nowrap;
            padding-top: 2px;
        }

        .start-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            margin-top: 14px;
            padding: 12px;
            background: var(--grad);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            text-decoration: none;
            transition: filter 0.2s;
        }

        .start-btn:hover { filter: brightness(1.12); }

        /* upcoming */
        .up-item {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 9px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .up-item:last-child { border-bottom: none; }

        .up-date {
            width: 42px;
            border-radius: 10px;
            background: #fdf1f1;
            color: var(--accent-red);
            text-align: center;
            padding: 5px 0;
            flex-shrink: 0;
        }

        .up-date .m { display: block; font-size: 9px; font-weight: 700; letter-spacing: 0.6px; }
        .up-date .d { display: block; font-size: 16px; font-weight: 700; line-height: 1.1; }

        .up-info { flex: 1; min-width: 0; }

        .up-name {
            font-size: 12px;
            font-weight: 600;
            color: var(--ink);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .up-sub {
            font-size: 10.5px;
            color: var(--ink-3);
            margin-top: 1px;
        }

        .up-sub .hp { color: var(--accent-red); font-weight: 600; }

        .up-right {
            font-size: 10.5px;
            color: var(--ink-3);
            text-align: right;
            white-space: nowrap;
        }

        .up-hidden { display: none; }

        /* mascot */
        .mascot-card {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .mascot-face {
            width: 64px;
            height: 64px;
            background: #fdf1f1;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            flex-shrink: 0;
        }

        .mascot-text h4 {
            font-size: 13.5px;
            font-weight: 700;
            line-height: 1.35;
        }

        .mascot-text h4 .hl { color: var(--accent-red); }

        .mascot-text p {
            font-size: 11.5px;
            color: var(--ink-3);
            margin-top: 3px;
            line-height: 1.4;
        }

        .empty-note {
            color: var(--ink-3);
            font-size: 13px;
            padding: 10px 0;
        }

        /* ─── STUDENT STUDY PLAN BLOCKS ─── */
        .cal-event { cursor: pointer; }

        .cal-event.plan {
            border: 1.5px dashed rgba(0, 0, 0, 0.18);
        }

        .plan-tag {
            display: inline-block;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            background: rgba(255, 255, 255, 0.75);
            border-radius: 4px;
            padding: 1px 5px;
            margin-top: 2px;
            color: var(--ink-2);
        }

        /* ─── MODALS ─── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(20, 10, 10, 0.45);
            z-index: 3000;
            display: none;
            align-items: flex-start;
            justify-content: center;
            padding: 70px 20px 20px;
        }

        .modal-overlay.open { display: flex; }

        .modal-panel {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.25s ease both;
            overflow: hidden;
        }

        .modal-head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--line);
        }

        .modal-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 9px;
            flex: 1;
        }

        .modal-close {
            border: none;
            background: #f6f0f0;
            color: var(--primary);
            width: 30px;
            height: 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
        }

        .modal-close:hover { background: var(--primary); color: #fff; }

        .modal-body { padding: 18px 20px 20px; }

        .form-row { margin-bottom: 13px; }

        .form-row label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--ink-2);
            margin-bottom: 5px;
        }

        .form-row select,
        .form-row input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--gray-300, #e0e0e0);
            border-radius: 9px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            color: var(--ink);
            background: white;
            outline: none;
        }

        .form-row select:focus,
        .form-row input:focus { border-color: var(--primary); }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .modal-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            width: 100%;
            padding: 12px;
            background: var(--grad);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: filter 0.2s;
        }

        .modal-btn:hover { filter: brightness(1.12); }

        .modal-btn.ghost {
            background: white;
            color: var(--accent-red);
            border: 1.5px solid #f0caca;
            margin-top: 9px;
        }

        .modal-btn.ghost:hover { background: #fdf1f1; filter: none; }

        .ev-summary {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 14px;
        }

        .ev-summary .sdot {
            width: 12px;
            height: 12px;
            border-radius: 4px;
            flex-shrink: 0;
        }

        .ev-summary .sname {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            line-height: 1.35;
        }

        .ev-summary .smeta {
            font-size: 11px;
            color: var(--ink-3);
            margin-top: 1px;
        }

        .toast {
            position: fixed;
            bottom: 26px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--grad);
            color: white;
            font-size: 12.5px;
            font-weight: 600;
            border-radius: 10px;
            padding: 11px 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
            z-index: 4000;
            animation: slideUp 0.3s ease both;
        }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 1300px) {
            .cal-layout { grid-template-columns: 1fr; }
            .stats-bar { grid-template-columns: repeat(3, 1fr); gap: 10px; }
            .stat-cell + .stat-cell { border-left: none; }
        }

        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: flex-start; gap: 12px; }
            .page-header-right { width: 100%; flex-wrap: wrap; }
            .search-wrap { flex: 1; min-width: 0; }
            .search-wrap input { width: 100%; }
            .week-strip { grid-template-columns: repeat(7, 1fr); }
            .strip-icon { display: none; }
            .stats-bar { grid-template-columns: repeat(2, 1fr); }
            .cal-toolbar { gap: 8px; }
            .card { padding: 16px; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        @include('partials.sidebar', ['active' => 'calendar'])
        @include('partials.student-bottom-nav', ['active' => 'more'])
        @include('partials.student-mobile-header')

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- HEADER -->
            <div class="page-header">
                <div>
                    <div class="page-title">Spaced Repetition Calendar <span class="spark"><i class="fas fa-wand-magic-sparkles"></i></span></div>
                    <div class="page-subtitle">Plan your reviews and stay consistent.</div>
                </div>
                <div class="page-header-right">
                    <div class="search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search topics, questions...">
                    </div>
                    <button class="notif-btn" onclick="window.location.href='{{ route('notifications.index') }}'">
                        <i class="fas fa-bell"></i>
                        @if($unreadNotifications > 0)
                            <span class="notification-badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                        @endif
                    </button>
                    <div style="position: relative;">
                        <button class="profile-btn" id="profileBtn">@include('partials.avatar-content')</button>
                        <div class="dropdown-menu" id="profileDropdown">
                            <a href="{{ route('settings') }}"><i class="fas fa-user"></i> Profile Settings</a>
                            <a href="{{ route('performance') }}"><i class="fas fa-chart-line"></i> My Progress</a>
                            <a href="{{ route('achievements') }}"><i class="fas fa-trophy"></i> Achievements</a>
                            <form method="POST" action="{{ route('logout') }}" style="margin:0;padding:0;">
                                @csrf
                                <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cal-layout">
                <!-- ══════════ LEFT: CALENDAR ══════════ -->
                <div class="cal-left">
                    <div class="card">
                        <!-- TOOLBAR -->
                        <div class="cal-toolbar">
                            <span class="month-label">{{ $context['month_label'] }} <i class="fas fa-chevron-down"></i></span>
                            <a class="today-btn" href="{{ route('calendar') }}">Today</a>
                            <a class="nav-btn" id="navPrev" href="{{ route('calendar', ['date' => $context['nav']['week']['prev']]) }}"><i class="fas fa-chevron-left"></i></a>
                            <a class="nav-btn" id="navNext" href="{{ route('calendar', ['date' => $context['nav']['week']['next']]) }}"><i class="fas fa-chevron-right"></i></a>
                            <span class="toolbar-spacer"></span>
                            <div class="view-toggle" id="viewToggle">
                                <button data-view="day">Day</button>
                                <button data-view="week" class="active">Week</button>
                                <button data-view="month">Month</button>
                            </div>
                            <button class="tool-btn" title="Filter"><i class="fas fa-filter"></i></button>
                            <button class="tool-btn solid" id="addPlanBtn" title="Add a study block"><i class="fas fa-plus"></i></button>
                        </div>

                        <!-- WEEK STRIP -->
                        <div class="week-strip" data-panel-for="week day">
                            <div class="strip-icon">
                                <i class="far fa-calendar-check"></i>
                                <span class="bar"></span>
                            </div>
                            @foreach($weekDays as $wd)
                                <a class="strip-day {{ $wd['date'] === $context['cursor_date'] ? 'active' : '' }}"
                                   href="{{ route('calendar', ['date' => $wd['date']]) }}">
                                    <span class="dw">{{ $wd['label'] }}</span>
                                    <span class="dn">{{ $wd['day'] }}</span>
                                    <span class="dot" style="{{ $wd['tasks'] === 0 ? 'visibility:hidden;' : '' }}"></span>
                                    <span class="tc">{{ $wd['tasks'] }} {{ \Illuminate\Support\Str::plural('Task', $wd['tasks']) }}</span>
                                </a>
                            @endforeach
                        </div>

                        <!-- ALL DAY CHIPS -->
                        <div class="allday-row" data-panel-for="week day">
                            <span class="allday-lbl">All day</span>
                            @foreach($allDayChips as $chip)
                                <span class="allday-chip" style="background:{{ $chip['bg'] }};">
                                    <span class="cdot" style="background:{{ $chip['dot'] }};"></span>{{ $chip['label'] }}
                                </span>
                            @endforeach
                        </div>

                        <!-- ═══ WEEK VIEW ═══ -->
                        <div class="cal-panel" data-panel="week">
                            <div class="time-grid">
                                <div class="hours-col">
                                    @foreach(['8 AM','9 AM','10 AM','11 AM','12 PM','1 PM','2 PM','3 PM','4 PM','5 PM','6 PM','7 PM'] as $h)
                                        <span>{{ $h }}</span>
                                    @endforeach
                                </div>
                                <div class="grid-cols" id="weekCols">
                                    @foreach($weekDays as $di => $wd)
                                        <div class="grid-col {{ $wd['is_today'] ? 'today-col' : '' }}" data-date="{{ $wd['date'] }}">
                                            @foreach($wd['events'] as $e)
                                                <div class="cal-event" style="top:{{ $e['top'] }}%;height:{{ $e['height'] }}%;background:{{ $e['bg'] }};"
                                                     data-topic="{{ $e['topic'] }}" data-topic-id="{{ $e['topic_id'] }}"
                                                     data-subject-id="{{ $e['subject_id'] }}" data-subject-code="{{ $e['subject_code'] }}"
                                                     data-meta="{{ $e['count'] }} {{ \Illuminate\Support\Str::plural('item', $e['count']) }} due · {{ $e['priority'] }} priority · {{ $e['time_lbl'] }}"
                                                     data-dot="{{ $e['dot'] }}">
                                                    <div class="ehead" style="color:{{ $e['dot'] }};"><span class="edot" style="background:{{ $e['dot'] }};"></span>{{ $e['subject_code'] }}</div>
                                                    <div class="etitle">{{ $e['topic'] }}</div>
                                                    <div class="etime">{{ $e['time_lbl'] }}</div>
                                                </div>
                                            @endforeach
                                            @foreach(($plansByDate[$wd['date']] ?? []) as $p)
                                                <div class="cal-event plan" style="top:{{ $p['top'] }}%;height:{{ $p['height'] }}%;background:{{ $p['bg'] }};"
                                                     data-topic="{{ $p['title'] }}" data-topic-id="{{ $p['topic_id'] ?? '' }}"
                                                     data-subject-id="{{ $p['subject_id'] }}" data-subject-code="{{ $p['subject_code'] }}"
                                                     data-meta="My study block · {{ $p['time_lbl'] }}" data-plan-id="{{ $p['id'] }}"
                                                     data-dot="{{ $p['dot'] }}">
                                                    <div class="ehead" style="color:{{ $p['dot'] }};"><span class="edot" style="background:{{ $p['dot'] }};"></span>{{ $p['subject_code'] }}</div>
                                                    <div class="etitle">{{ $p['title'] }}</div>
                                                    <span class="plan-tag">My plan</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                    <div class="now-line" id="nowLineWeek"><span class="now-pill" id="nowPillWeek"></span></div>
                                </div>
                            </div>
                        </div>

                        <!-- ═══ DAY VIEW ═══ -->
                        <div class="cal-panel" data-panel="day" style="display:none;">
                            <div style="font-size:13px;font-weight:600;color:var(--ink-2);margin-bottom:10px;">{{ $context['day_label'] }}</div>
                            <div class="time-grid">
                                <div class="hours-col">
                                    @foreach(['8 AM','9 AM','10 AM','11 AM','12 PM','1 PM','2 PM','3 PM','4 PM','5 PM','6 PM','7 PM'] as $h)
                                        <span>{{ $h }}</span>
                                    @endforeach
                                </div>
                                <div class="grid-cols single">
                                    @php $cursorDayData = collect($weekDays)->firstWhere('date', $context['cursor_date']); @endphp
                                    <div class="grid-col {{ ($cursorDayData['is_today'] ?? false) ? 'today-col' : '' }}">
                                        @foreach(($cursorDayData['events'] ?? []) as $e)
                                            <div class="cal-event" style="top:{{ $e['top'] }}%;height:{{ $e['height'] }}%;background:{{ $e['bg'] }};max-width:320px;"
                                                 data-topic="{{ $e['topic'] }}" data-topic-id="{{ $e['topic_id'] }}"
                                                 data-subject-id="{{ $e['subject_id'] }}" data-subject-code="{{ $e['subject_code'] }}"
                                                 data-meta="{{ $e['count'] }} {{ \Illuminate\Support\Str::plural('item', $e['count']) }} due · {{ $e['priority'] }} priority · {{ $e['time_lbl'] }}"
                                                 data-dot="{{ $e['dot'] }}">
                                                <div class="ehead" style="color:{{ $e['dot'] }};"><span class="edot" style="background:{{ $e['dot'] }};"></span>{{ $e['subject_code'] }}</div>
                                                <div class="etitle">{{ $e['topic'] }} · {{ $e['count'] }} {{ \Illuminate\Support\Str::plural('item', $e['count']) }}</div>
                                                <div class="etime">{{ $e['time_lbl'] }}</div>
                                            </div>
                                        @endforeach
                                        @foreach(($plansByDate[$context['cursor_date']] ?? []) as $p)
                                            <div class="cal-event plan" style="top:{{ $p['top'] }}%;height:{{ $p['height'] }}%;background:{{ $p['bg'] }};max-width:320px;"
                                                 data-topic="{{ $p['title'] }}" data-topic-id="{{ $p['topic_id'] ?? '' }}"
                                                 data-subject-id="{{ $p['subject_id'] }}" data-subject-code="{{ $p['subject_code'] }}"
                                                 data-meta="My study block · {{ $p['time_lbl'] }}" data-plan-id="{{ $p['id'] }}"
                                                 data-dot="{{ $p['dot'] }}">
                                                <div class="ehead" style="color:{{ $p['dot'] }};"><span class="edot" style="background:{{ $p['dot'] }};"></span>{{ $p['subject_code'] }}</div>
                                                <div class="etitle">{{ $p['title'] }}</div>
                                                <span class="plan-tag">My plan</span>
                                            </div>
                                        @endforeach
                                        <div class="now-line" id="nowLineDay"><span class="now-pill" id="nowPillDay"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ═══ MONTH VIEW ═══ -->
                        <div class="cal-panel" data-panel="month" style="display:none;">
                            <div class="month-grid">
                                <div class="month-head">
                                    @foreach(['SUN','MON','TUE','WED','THU','FRI','SAT'] as $d)
                                        <span>{{ $d }}</span>
                                    @endforeach
                                </div>
                                @foreach($weeks as $week)
                                    <div class="month-week">
                                        @foreach($week as $day)
                                            <div class="month-cell {{ $day['muted'] ? 'muted' : '' }} {{ $day['is_today'] ? 'today' : '' }}" title="{{ $day['date_label'] }}">
                                                <div class="num">{{ $day['day'] }}</div>
                                                @foreach(array_slice($day['events'], 0, 3) as $e)
                                                    <div class="month-ev" style="background:{{ $e['bg'] }};color:{{ $e['dot'] }};">
                                                        <span class="cdot" style="background:{{ $e['dot'] }};"></span>{{ $e['subject_code'] }}
                                                    </div>
                                                @endforeach
                                                @if(count($day['events']) > 3)
                                                    <div class="month-more">+{{ count($day['events']) - 3 }} more</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- BOTTOM STATS BAR -->
                    <div class="stats-bar">
                        <div class="stat-cell">
                            <i class="fas fa-calendar-xmark red"></i>
                            <div><div class="lbl">Weak area</div><div class="val"><strong>{{ $stats['weak'] }}</strong> Topics</div></div>
                        </div>
                        <div class="stat-cell">
                            <i class="fas fa-fire-flame-curved amber"></i>
                            <div><div class="lbl">Needs work</div><div class="val"><strong>{{ $stats['needs'] }}</strong> Topics</div></div>
                        </div>
                        <div class="stat-cell">
                            <i class="fas fa-check green"></i>
                            <div><div class="lbl">On track</div><div class="val"><strong>{{ $stats['on_track'] }}</strong> Topics</div></div>
                        </div>
                        <div class="stat-cell">
                            <i class="fas fa-book-open blue"></i>
                            <div><div class="lbl">Total Reviews</div><div class="val"><strong>{{ $stats['reviews'] }}</strong> Items</div></div>
                        </div>
                        <div class="stat-cell">
                            <i class="fas fa-fire red"></i>
                            <div><div class="lbl">Study Streak</div><div class="val"><strong>{{ $stats['streak'] }}</strong> {{ \Illuminate\Support\Str::plural('Day', $stats['streak']) }}</div></div>
                        </div>
                        <div class="stat-cell">
                            <i class="fas fa-bullseye purple"></i>
                            <div><div class="lbl">Accuracy</div><div class="val"><strong>{{ $stats['accuracy'] }}%</strong></div></div>
                        </div>
                    </div>
                </div>

                <!-- ══════════ RIGHT RAIL ══════════ -->
                <div class="cal-right">
                    <!-- CPA BOARD EXAM COUNTDOWN -->
                    <div class="exam-card">
                        <div class="exam-kicker"><i class="fas fa-graduation-cap"></i> CPA BOARD EXAM</div>
                        @if($daysToExam !== null)
                            <div class="exam-sub">In just</div>
                            <div class="exam-days">{{ $daysToExam }}</div>
                            <div class="exam-days-lbl">DAYS LEFT!</div>
                            <div class="exam-note">Every day counts. You've got this! 💪</div>
                            <div class="exam-bar"><span style="width:{{ $examPct }}%"></span></div>
                            <div class="exam-date">{{ \Illuminate\Support\Carbon::parse($examDate)->format('F j, Y') }}</div>
                        @else
                            <div class="exam-days" style="font-size:38px;">—</div>
                            <div class="exam-note">Set your exam target date in Settings to start the countdown.</div>
                            <a href="{{ route('settings') }}" class="start-btn" style="background:white;color:var(--primary);">Set Exam Date</a>
                        @endif
                    </div>

                    <!-- TODAY'S REVIEWS -->
                    <div class="card">
                        <div class="side-head">
                            <span class="side-title">Today's Reviews</span>
                            <span class="count-pill">{{ $context['due_count'] }} items</span>
                        </div>
                        @forelse($todayReviews->take(5) as $r)
                            <div class="review-item">
                                <span class="rdot" style="background:{{ $r['dot'] }};"></span>
                                <div class="review-info">
                                    <div class="review-name"><span class="sc">{{ $r['subject_code'] }}</span> · {{ $r['topic'] }}</div>
                                    <span class="prio {{ strtolower($r['priority']) }}">{{ $r['priority'] }} priority</span>
                                </div>
                                <span class="review-items-count">{{ $r['count'] }} {{ \Illuminate\Support\Str::plural('item', $r['count']) }}</span>
                            </div>
                        @empty
                            <div class="empty-note">No reviews due today — you're all caught up! 🎉</div>
                        @endforelse
                        <a href="{{ route('adaptive-quizzes') }}" class="start-btn">
                            {{ $context['due_count'] > 0 ? 'Start Reviewing' : 'Practice a Quiz' }} <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>

                    <!-- UPCOMING -->
                    <div class="card">
                        <div class="side-head">
                            <span class="side-title">Upcoming <small>(Next 7 Days)</small></span>
                            @if($upcoming->count() > 3)
                                <button type="button" class="view-all" id="upcomingToggle">View all</button>
                            @endif
                        </div>
                        @forelse($upcoming as $i => $u)
                            <div class="up-item {{ $i >= 3 ? 'up-hidden' : '' }}">
                                <div class="up-date">
                                    <span class="m">{{ strtoupper(explode(' ', $u['date_label'])[0]) }}</span>
                                    <span class="d">{{ explode(' ', $u['date_label'])[1] ?? '' }}</span>
                                </div>
                                <div class="up-info">
                                    <div class="up-name">{{ $u['subject_code'] }} · {{ $u['topic'] }}</div>
                                    <div class="up-sub">
                                        @if($u['priority'] === 'High')<span class="hp">High priority</span> ·@endif
                                        {{ $u['count'] }} {{ \Illuminate\Support\Str::plural('item', $u['count']) }}
                                    </div>
                                </div>
                                <div class="up-right">1x<br>Review</div>
                            </div>
                        @empty
                            <div class="empty-note">Nothing scheduled in the next 7 days.</div>
                        @endforelse
                    </div>

                    <!-- MASCOT -->
                    <div class="card mascot-card">
                        <div class="mascot-face">🐤</div>
                        <div class="mascot-text">
                            <h4>Consistency is <span class="hl">the key to mastery!</span></h4>
                            <p>Small steps today, big results tomorrow. 📈</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- ═══ SCHEDULED TOPIC MODAL (click an event) ═══ -->
    <div class="modal-overlay" id="eventModal">
        <div class="modal-panel">
            <div class="modal-head">
                <span class="modal-title"><i class="fas fa-book-open"></i> <span id="evKind">Scheduled Review</span></span>
                <button type="button" class="modal-close" data-close="eventModal"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="ev-summary">
                    <span class="sdot" id="evDot"></span>
                    <div>
                        <div class="sname" id="evName"></div>
                        <div class="smeta" id="evMeta"></div>
                    </div>
                </div>

                <form method="POST" action="{{ route('quiz.start') }}" id="evExamForm">
                    @csrf
                    <input type="hidden" name="subject_id" id="evSubjectId">
                    <input type="hidden" name="topic_id" id="evTopicId">
                    <input type="hidden" name="mode" value="topic">
                    <input type="hidden" name="session_type" value="testing">
                    <div class="form-row">
                        <label>Number of questions</label>
                        <select name="count">
                            <option value="10" selected>10 questions</option>
                            <option value="15">15 questions</option>
                            <option value="20">20 questions</option>
                        </select>
                    </div>
                    <button type="submit" class="modal-btn"><i class="fas fa-graduation-cap"></i> Start as Exam</button>
                </form>
                <div class="empty-note" id="evNoExam" style="display:none;">
                    This block has no linked topic, so it can't be started as an exam.
                </div>

                <form method="POST" id="evDeleteForm" style="display:none;"
                      onsubmit="return confirm('Remove this study block from your calendar?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="modal-btn ghost"><i class="fas fa-trash-can"></i> Remove this study block</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ═══ ADD STUDY BLOCK MODAL (+) ═══ -->
    <div class="modal-overlay" id="addModal">
        <div class="modal-panel">
            <div class="modal-head">
                <span class="modal-title"><i class="fas fa-calendar-plus"></i> Add Study Block</span>
                <button type="button" class="modal-close" data-close="addModal"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                @if($errors->any())
                    <div class="empty-note" style="color:var(--accent-red);padding-top:0;">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="{{ route('calendar.plan.store') }}">
                    @csrf
                    <div class="form-row">
                        <label>Topic to study</label>
                        <select name="topic_id" id="planTopic">
                            <option value="">— Custom block (no topic) —</option>
                            @foreach($topicOptions as $code => $topics)
                                <optgroup label="{{ $code }}">
                                    @foreach($topics as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row" id="planTitleRow">
                        <label>Title</label>
                        <input type="text" name="title" maxlength="120" placeholder="e.g. Review formulas, Flashcards drill" value="{{ old('title') }}">
                    </div>
                    <div class="form-row">
                        <label>Date</label>
                        <input type="date" name="plan_date" value="{{ old('plan_date', $context['cursor_date']) }}" required>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-row">
                            <label>Start time</label>
                            <select name="start_time">
                                @for($h = 8; $h <= 19; $h++)
                                    @foreach([0, 30] as $mm)
                                        @php $v = sprintf('%02d:%02d', $h, $mm); @endphp
                                        <option value="{{ $v }}" {{ $v === '09:00' ? 'selected' : '' }}>
                                            {{ \Illuminate\Support\Carbon::createFromTime($h, $mm)->format('g:i A') }}
                                        </option>
                                    @endforeach
                                @endfor
                            </select>
                        </div>
                        <div class="form-row">
                            <label>Duration</label>
                            <select name="duration_min">
                                <option value="30">30 mins</option>
                                <option value="60" selected>1 hour</option>
                                <option value="90">1.5 hours</option>
                                <option value="120">2 hours</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="modal-btn"><i class="fas fa-plus"></i> Add to Calendar</button>
                </form>
            </div>
        </div>
    </div>

    @if(session('status'))
        <div class="toast" id="statusToast"><i class="fas fa-circle-check"></i> {{ session('status') }}</div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Fade-in
            document.querySelectorAll('.card, .exam-card, .stats-bar').forEach((el, i) => {
                el.style.animation = `slideUp 0.45s ease ${i * 0.05}s both`;
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
                profileDropdown.addEventListener('click', e => e.stopPropagation());
            }

            // ── Day / Week / Month toggle ──────────────────────────────────
            // Panels are pre-rendered server-side; the toggle swaps them and
            // repoints the prev/next arrows at the matching step size.
            const nav = @json($context['nav']);
            const calBase = "{{ route('calendar') }}";
            const panels = document.querySelectorAll('.cal-panel');
            const helpers = document.querySelectorAll('[data-panel-for]');
            const navPrev = document.getElementById('navPrev');
            const navNext = document.getElementById('navNext');

            function setView(view) {
                document.querySelectorAll('#viewToggle button').forEach(b =>
                    b.classList.toggle('active', b.dataset.view === view));
                panels.forEach(p => p.style.display = p.dataset.panel === view ? '' : 'none');
                helpers.forEach(h =>
                    h.style.display = h.dataset.panelFor.split(' ').includes(view) ? '' : 'none');
                if (nav[view]) {
                    navPrev.href = calBase + '?date=' + nav[view].prev + '&view=' + view;
                    navNext.href = calBase + '?date=' + nav[view].next + '&view=' + view;
                }
                // Keep the chosen view when clicking a strip day.
                document.querySelectorAll('.strip-day').forEach(a => {
                    const u = new URL(a.href);
                    u.searchParams.set('view', view);
                    a.href = u.toString();
                });
            }

            document.querySelectorAll('#viewToggle button').forEach(btn => {
                btn.addEventListener('click', () => setView(btn.dataset.view));
            });

            // Honour ?view= from navigation so arrows keep the active view.
            const startView = new URLSearchParams(location.search).get('view');
            if (startView && ['day', 'week', 'month'].includes(startView)) {
                setView(startView);
            }

            // ── Current-time line (8AM-8PM only, on today's grid) ──────────
            function positionNowLine() {
                const now = new Date();
                const h = now.getHours() + now.getMinutes() / 60;
                if (h < 8 || h > 20) return;

                const topPct = (h - 8) / 12 * 100;
                const label = now.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });

                const wk = document.getElementById('nowLineWeek');
                const todayCol = document.querySelector('#weekCols .grid-col.today-col');
                if (wk && todayCol) {
                    wk.style.top = topPct + '%';
                    wk.style.display = 'block';
                    document.getElementById('nowPillWeek').textContent = label;
                }

                const dy = document.getElementById('nowLineDay');
                if (dy && "{{ $context['cursor_date'] }}" === "{{ $context['today_date'] }}") {
                    dy.style.top = topPct + '%';
                    dy.style.display = 'block';
                    document.getElementById('nowPillDay').textContent = label;
                }
            }
            positionNowLine();
            setInterval(positionNowLine, 60000);

            // ── Upcoming: View all toggle ──────────────────────────────────
            const upToggle = document.getElementById('upcomingToggle');
            if (upToggle) {
                upToggle.addEventListener('click', function () {
                    const expanding = this.textContent.trim() === 'View all';
                    document.querySelectorAll('.up-item').forEach((el, i) => {
                        if (i >= 3) el.classList.toggle('up-hidden', !expanding);
                    });
                    this.textContent = expanding ? 'Show less' : 'View all';
                });
            }

            // ── Modals: open/close plumbing ────────────────────────────────
            const planDeleteBase = "{{ url('/calendar/plan') }}";

            function openModal(id) { document.getElementById(id).classList.add('open'); }
            function closeModal(id) { document.getElementById(id).classList.remove('open'); }

            document.querySelectorAll('.modal-close').forEach(btn =>
                btn.addEventListener('click', () => closeModal(btn.dataset.close)));
            document.querySelectorAll('.modal-overlay').forEach(ov =>
                ov.addEventListener('click', e => { if (e.target === ov) ov.classList.remove('open'); }));
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open')
                    .forEach(ov => ov.classList.remove('open'));
            });

            // + button → Add Study Block modal
            const addBtn = document.getElementById('addPlanBtn');
            if (addBtn) addBtn.addEventListener('click', () => openModal('addModal'));

            // Custom title only matters when no topic is chosen.
            const planTopic = document.getElementById('planTopic');
            const planTitleRow = document.getElementById('planTitleRow');
            function syncTitleRow() {
                planTitleRow.style.display = planTopic.value === '' ? '' : 'none';
            }
            if (planTopic) { syncTitleRow(); planTopic.addEventListener('change', syncTitleRow); }

            // Server-side validation failed → reopen the Add modal.
            @if($errors->any())
                openModal('addModal');
            @endif

            // ── Click a scheduled topic → "Start as Exam" modal ────────────
            document.querySelectorAll('.cal-event').forEach(el => {
                el.addEventListener('click', function () {
                    const d = this.dataset;
                    document.getElementById('evKind').textContent = d.planId ? 'My Study Block' : 'Scheduled Review';
                    document.getElementById('evDot').style.background = d.dot || '#c0392b';
                    document.getElementById('evName').textContent = d.subjectCode + ' · ' + d.topic;
                    document.getElementById('evMeta').textContent = d.meta || '';

                    // Exam form only when the block is tied to a real topic.
                    const examForm = document.getElementById('evExamForm');
                    const noExam   = document.getElementById('evNoExam');
                    const canExam  = d.topicId && d.subjectId && d.subjectId !== '0';
                    examForm.style.display = canExam ? '' : 'none';
                    noExam.style.display   = canExam ? 'none' : '';
                    if (canExam) {
                        document.getElementById('evSubjectId').value = d.subjectId;
                        document.getElementById('evTopicId').value   = d.topicId;
                    }

                    // Remove button only for the student's own plans.
                    const delForm = document.getElementById('evDeleteForm');
                    delForm.style.display = d.planId ? '' : 'none';
                    if (d.planId) delForm.action = planDeleteBase + '/' + d.planId;

                    openModal('eventModal');
                });
            });

            // Status toast fades out on its own.
            const toast = document.getElementById('statusToast');
            if (toast) setTimeout(() => toast.remove(), 4000);
        });

        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideUp {
                from { opacity: 0; transform: translateY(16px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
