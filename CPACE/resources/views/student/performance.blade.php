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
        :root {
            --primary: #7B1D1D;
            --primary-hover: #6a1818;
            --accent-red: #c0392b;
            --green: #21a366;
            --green-text: #178a53;
            --blue: #3b7ddd;
            --amber: #e8910b;
            --purple: #8e44ad;
            --pink: #d4589e;
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
            margin-bottom: 14px;
            gap: 20px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.2;
        }

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
            width: 17px;
            height: 17px;
            background: var(--accent-red);
            color: white;
            border-radius: 50%;
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

        /* ─── DATE CHIP ROW ─── */
        .toolbar-row {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        /* ─── AI INSIGHTS BUTTON + PANEL ─── */
        .ai-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(123, 29, 29, 0.3);
            transition: all 0.2s;
        }

        .ai-btn:hover { 
            background: var(--primary-hover);
            box-shadow: 0 4px 14px rgba(123, 29, 29, 0.4);
            transform: translateY(-1px);
        }

        .ai-overlay {
            position: fixed;
            inset: 0;
            background: rgba(20, 10, 10, 0.45);
            z-index: 3000;
            display: none;
            align-items: flex-start;
            justify-content: center;
            padding: 60px 20px 20px;
        }

        .ai-overlay.open { display: flex; }

        .ai-panel {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 520px;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.3s ease both;
        }

        .ai-panel-head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 22px;
            border-bottom: 1px solid var(--line);
        }

        .ai-panel-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 9px;
            flex: 1;
        }

        .ai-panel-head button {
            border: none;
            background: #f6f0f0;
            color: var(--primary);
            width: 30px;
            height: 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
        }

        .ai-panel-head button:hover { background: var(--primary); color: #fff; }
        .ai-panel-head button:disabled { opacity: 0.5; cursor: not-allowed; }

        .ai-panel-body {
            padding: 18px 22px;
            overflow-y: auto;
        }

        .ai-panel-body::-webkit-scrollbar { width: 6px; }
        .ai-panel-body::-webkit-scrollbar-thumb { background: #e0d5d5; border-radius: 10px; }

        .insight-item {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 13px 14px;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .insight-item:last-child { margin-bottom: 0; }

        .insight-item.red   { background: #fff1f2; }
        .insight-item.green { background: #ecfdf5; }
        .insight-item.blue  { background: #eff6ff; }
        .insight-item.amber { background: #fffbeb; }

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

        .insight-item.red .insight-icon   { color: var(--accent-red); }
        .insight-item.green .insight-icon { color: var(--green); }
        .insight-item.blue .insight-icon  { color: var(--blue); }
        .insight-item.amber .insight-icon { color: var(--amber); }

        .insight-content { flex: 1; }

        .insight-title {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 2px;
        }

        .insight-desc {
            font-size: 12.5px;
            color: var(--ink-3);
            line-height: 1.45;
        }

        .ai-loading, .ai-error {
            font-size: 13px;
            color: var(--ink-3);
            padding: 14px 4px;
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .ai-error { color: #c99; flex-wrap: wrap; }

        .ai-retry {
            border: none;
            background: var(--primary);
            color: white;
            border-radius: 7px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
        }

        .ai-retry:hover { background: var(--primary-hover); }

        .date-range {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: white;
            padding: 10px 16px;
            border-radius: 10px;
            border: 1px solid var(--line);
            box-shadow: var(--card-shadow);
            font-size: 13.5px;
            font-weight: 500;
            color: #444;
            transition: box-shadow 0.2s;
        }

        .date-range:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .date-range .fa-calendar { color: var(--ink-2); font-size: 12px; }
        .date-range .fa-chevron-down { color: var(--ink-3); font-size: 10px; }

        /* ─── CARD BASE ─── */
        .card {
            background: white;
            border-radius: 20px;
            border: 1px solid var(--line);
            box-shadow: var(--card-shadow);
            padding: 24px;
            transition: box-shadow 0.25s;
        }

        .card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        }

        .card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            gap: 12px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--ink);
        }

        .card-more {
            color: #c3c7cd;
            font-size: 14px;
            letter-spacing: 1px;
        }

        .view-all-btn {
            font-size: 12px;
            font-weight: 600;
            color: var(--primary);
            background: #f6eeee;
            border: none;
            border-radius: 8px;
            padding: 6px 14px;
            cursor: pointer;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
        }

        .view-all-btn:hover { 
            background: #efdddd;
            transform: translateY(-1px);
        }

        /* ─── KPI ROW ─── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }

        .kpi-card {
            border-radius: 18px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            color: var(--ink-1);
            min-height: 115px;
            background: #fff;
            border: 1px solid var(--line);
            box-shadow: 0 4px 12px rgba(0,0,0,0.06), 0 12px 28px rgba(0,0,0,0.08);
            transition: box-shadow 0.25s, transform 0.25s;
        }

        .kpi-card:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.1), 0 16px 40px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: -50px; right: -50px;
            width: 180px; height: 180px;
            border-radius: 50%;
            pointer-events: none;
        }
        .kpi-card.t1::before { background: rgba(192,57,43,0.07); }
        .kpi-card.t2::before { background: rgba(232,145,11,0.07); }
        .kpi-card.t3::before { background: rgba(59,125,221,0.07); }
        .kpi-card.t6::before { background: rgba(142,68,173,0.07); }

        .kpi-card::after {
            content: '';
            position: absolute;
            bottom: -40px; left: 20%;
            width: 140px; height: 140px;
            border-radius: 50%;
            pointer-events: none;
        }
        .kpi-card.t1::after { background: rgba(192,57,43,0.05); }
        .kpi-card.t2::after { background: rgba(232,145,11,0.05); }
        .kpi-card.t3::after { background: rgba(59,125,221,0.05); }
        .kpi-card.t6::after { background: rgba(142,68,173,0.05); }

        .kpi-card .kpi-left {
            display: flex;
            flex-direction: column;
            gap: 0;
            position: relative;
            z-index: 1;
        }

        .kpi-card .kpi-number {
            font-size: 52px;
            font-weight: 800;
            color: var(--ink-1);
            line-height: 1;
        }

        .kpi-card .kpi-number small {
            font-size: 20px;
            font-weight: 600;
            color: var(--ink-3);
        }

        .kpi-card .kpi-label {
            font-size: 12.5px;
            color: var(--ink-3);
            font-weight: 500;
            margin-top: auto;
        }

        .kpi-card .kpi-delta {
            font-size: 11px;
            font-weight: 500;
            color: var(--ink-3);
            white-space: nowrap;
        }

        .kpi-card .kpi-delta .d { font-weight: 600; }
        .kpi-card .kpi-delta .d.up    { color: #21a366; }
        .kpi-card .kpi-delta .d.down  { color: #c0392b; }
        .kpi-card .kpi-delta .d.muted { color: #999; }

        .kpi-illust {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 130px;
            height: 130px;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }

        .kpi-illust img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 6px 16px rgba(0,0,0,0.15));
        }

        .kpi-shapes {
            position: absolute;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .kpi-shapes span {
            position: absolute;
        }

        .kpi-shapes span:nth-child(1) {
            width: 150px; height: 150px;
            top: -45px; right: 35%;
            border-radius: 50%;
        }
        .kpi-card.t1 .kpi-shapes span:nth-child(1) { background: rgba(192,57,43,0.06); }
        .kpi-card.t2 .kpi-shapes span:nth-child(1) { background: rgba(232,145,11,0.06); }
        .kpi-card.t3 .kpi-shapes span:nth-child(1) { background: rgba(59,125,221,0.06); }
        .kpi-card.t6 .kpi-shapes span:nth-child(1) { background: rgba(142,68,173,0.06); }

        .kpi-shapes span:nth-child(2) {
            width: 100px; height: 100px;
            bottom: -25px; left: 8%;
            border-radius: 20px;
            transform: rotate(35deg);
        }
        .kpi-card.t1 .kpi-shapes span:nth-child(2) { background: rgba(192,57,43,0.05); }
        .kpi-card.t2 .kpi-shapes span:nth-child(2) { background: rgba(232,145,11,0.05); }
        .kpi-card.t3 .kpi-shapes span:nth-child(2) { background: rgba(59,125,221,0.05); }
        .kpi-card.t6 .kpi-shapes span:nth-child(2) { background: rgba(142,68,173,0.05); }

        /* ─── ROW GRIDS ─── */
        .row-2 {
            display: grid;
            grid-template-columns: 1.6fr 1fr 0.7fr;
            gap: 18px;
            margin-bottom: 18px;
            align-items: stretch;
        }

        .row-3 {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 18px;
            margin-bottom: 18px;
            align-items: stretch;
        }

        .row-4 {
            display: grid;
            grid-template-columns: 1.8fr 1fr;
            gap: 18px;
            align-items: stretch;
        }

        /* ─── ACCURACY OVER TIME ─── */
        .chart-select {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 12px;
            color: var(--ink-2);
            font-family: 'Poppins', sans-serif;
            background: white;
            cursor: pointer;
            outline: none;
        }

        .line-chart-wrap {
            display: flex;
            gap: 10px;
        }

        .y-axis {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-size: 12px;
            color: #c2c6cb;
            height: 230px;
            padding: 2px 0;
            flex-shrink: 0;
        }

        .line-chart {
            flex: 1;
            position: relative;
            height: 230px;
        }

        .line-chart svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .pt-dot {
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--accent-red);
            border: 2px solid white;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 1px 4px rgba(0,0,0,0.2);
            pointer-events: none;
        }

        .pt-lbl {
            position: absolute;
            transform: translate(-50%, -100%);
            font-size: 12px;
            font-weight: 600;
            color: var(--ink-2);
            background: white;
            border-radius: 5px;
            padding: 1px 5px;
            pointer-events: none;
            white-space: nowrap;
        }

        .x-axis {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #aab0b6;
            margin-top: 8px;
            margin-left: 40px;
            padding: 0 4px;
        }

        /* ─── DONUTS ─── */
        .donut-body {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .donut {
            width: 170px;
            height: 170px;
            position: relative;
            margin: 0 auto 20px;
            filter: drop-shadow(2px 4px 6px rgba(0,0,0,0.15));
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
            color: var(--ink);
            line-height: 1.1;
        }

        .donut-center .lbl {
            font-size: 12px;
            color: var(--ink-3);
        }

        .donut-legend {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px 18px;
            width: 100%;
        }

        .donut-legend .row {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            color: var(--ink-2);
        }

        .donut-legend .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .donut-legend .name {
            white-space: nowrap;
        }

        .donut-legend .pct {
            font-weight: 600;
            color: var(--ink);
        }

        .empty-note {
            color: var(--ink-3);
            font-size: 13.5px;
            padding: 16px 0;
            line-height: 1.5;
        }

        /* ─── EXAM COUNTDOWN CARD ─── */
        .exam-card {
            border-radius: 20px;
            padding: 24px;
            position: relative;
            overflow: hidden;
            color: white;
            background: linear-gradient(145deg, #c0392b 0%, #7B1D1D 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            min-height: 230px;
        }

        .exam-card .exam-bg {
            position: absolute;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .exam-card .flag {
            position: absolute;
            font-size: 13px;
            color: #fff;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }

        .exam-kicker {
            font-size: 15px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.92);
            position: relative;
            z-index: 1;
            line-height: 1.4;
        }

        .exam-days {
            font-size: 72px;
            font-weight: 700;
            line-height: 1.05;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 12px rgba(0,0,0,0.2);
        }

        .exam-days-lbl {
            font-size: 15px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            position: relative;
            z-index: 1;
            margin-bottom: 4px;
        }

        .exam-date {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
            position: relative;
            z-index: 1;
        }

        /* ─── PERFORMANCE BY SUBJECT ─── */
        .subject-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 13px;
        }

        .subject-row:last-child { margin-bottom: 0; }

        .subject-row .code {
            width: 44px;
            font-size: 13px;
            font-weight: 600;
            color: var(--ink-2);
            flex-shrink: 0;
        }

        .subject-row .track {
            flex: 1;
            height: 9px;
            background: #f3f4f6;
            border-radius: 5px;
            overflow: hidden;
        }

        .subject-row .fill {
            display: block;
            height: 100%;
            border-radius: 5px;
        }

        .subject-row .val {
            width: 40px;
            text-align: right;
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            flex-shrink: 0;
        }

        .axis-note {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #b7bcc2;
            margin-top: 14px;
            padding-left: 54px;
        }

        /* ─── MINI STAT 2x2 GRID ─── */
        .mini-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-auto-rows: 1fr;
            gap: 14px;
        }

        .mini-card {
            background: white;
            border-radius: 20px;
            border: 1px solid var(--line);
            box-shadow: var(--card-shadow);
            padding: 20px;
            display: flex;
            flex-direction: column;
            min-width: 0;
            transition: box-shadow 0.2s;
        }

        .mini-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        .mini-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .mini-head i {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .mini-head i.red    { background: #fdeaea; color: var(--accent-red); }
        .mini-head i.green  { background: #e8f7ee; color: var(--green); }
        .mini-head i.amber  { background: #fef3e2; color: var(--amber); }
        .mini-head i.blue   { background: #e9f1fd; color: var(--blue); }
        .mini-head i.purple { background: #f3eaf9; color: var(--purple); }
        .mini-head i.teal   { background: #e0f7f6; color: #0d9488; }

        .mini-head span {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink-2);
            white-space: nowrap;
        }

        .mini-value {
            font-size: 22px;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mini-value.red { color: var(--accent-red); }

        .mini-sub {
            font-size: 12px;
            color: var(--ink-3);
            margin-top: 4px;
        }

        .mini-sub .d.up   { color: var(--green-text); font-weight: 600; }
        .mini-sub .d.down { color: var(--accent-red); font-weight: 600; }

        .mini-bar {
            height: 7px;
            background: #f1f3f5;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 10px;
        }

        .mini-bar span {
            display: block;
            height: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, #c0392b, #e74c3c);
        }

        /* ─── HEATMAP ─── */
        .hm-card {
            display: flex;
            flex-direction: column;
        }

        .heatmap-scroll {
            overflow: hidden;
            width: 100%;
            margin: auto 0;
        }

        .heatmap-scroll::-webkit-scrollbar { height: 0; }

        .hm-months {
            display: flex;
            gap: 3px;
            font-size: 10px;
            color: var(--ink-3);
            margin-bottom: 8px;
            margin-left: 28px;
        }

        .hm-months span {
            width: 0;
            flex-grow: 1;
            overflow: visible;
            white-space: nowrap;
            position: relative;
        }

        .hm-body { display: flex; width: 100%; }

        .hm-days {
            display: flex;
            flex-direction: column;
            gap: 3px;
            font-size: 10px;
            color: var(--ink-3);
            width: 28px;
            flex-shrink: 0;
            justify-content: space-between;
            padding: 1px 0;
        }

        .hm-days span {
            line-height: 1;
            display: flex;
            align-items: center;
        }

        .hm-weeks {
            display: flex;
            gap: 3px;
            flex: 1;
            min-width: 0;
        }

        .hm-week {
            display: flex;
            flex-direction: column;
            gap: 3px;
            flex: 1;
            min-width: 0;
        }

        .hm-week .hm-cell {
            flex: none;
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 2px;
        }

        .hm-legend {
            display: flex;
            align-items: center;
            gap: 5px;
            justify-content: flex-end;
            font-size: 11px;
            color: var(--ink-3);
            margin-top: auto;
            padding-top: 12px;
        }

        .hm-legend .hm-cell { display: inline-block; width: 11px; height: 11px; border-radius: 2px; }

        /* ─── RECENT QUIZZES TABLE ─── */
        .quiz-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }

        .quiz-table th {
            text-align: left;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--ink-3);
            font-weight: 600;
            padding: 0 10px 10px 0;
            border-bottom: 1px solid var(--line);
        }

        .quiz-table td {
            padding: 10px 10px 10px 0;
            border-bottom: 1px solid #f5f5f5;
            color: var(--ink-2);
            vertical-align: middle;
            white-space: nowrap;
        }

        .quiz-table tbody tr {
            transition: background 0.15s;
        }

        .quiz-table tbody tr:hover {
            background: #fafbfc;
        }

        .quiz-table tbody tr:last-child td { border-bottom: none; }

        .quiz-name {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: var(--ink);
        }

        .quiz-name i {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }

        .quiz-name i.green { background: #e8f7ee; color: var(--green); }
        .quiz-name i.red   { background: #fdeaea; color: var(--accent-red); }
        .quiz-name i.blue  { background: #e9f1fd; color: var(--blue); }
        .quiz-name i.amber { background: #fef3e2; color: var(--amber); }
        .quiz-name i.grey  { background: #f1f1f1; color: #999; }

        .score-val { font-weight: 700; }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 1500px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 1200px) {
            .row-2 { grid-template-columns: 1fr 1fr; }
            .row-2 .exam-card { grid-column: 1 / -1; min-height: 180px; }
            .row-3 { grid-template-columns: 1fr 1fr; }
            .row-4 { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: flex-start; gap: 12px; }
            .page-header-right { width: 100%; flex-wrap: wrap; }
            .search-wrap { flex: 1; min-width: 0; }
            .search-wrap input { width: 100%; }
            .kpi-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .row-2, .row-3 { grid-template-columns: 1fr; }
            .quiz-table { display: block; overflow-x: auto; }
        }

        @media (max-width: 480px) {
            .kpi-grid { grid-template-columns: 1fr; }
            .card { padding: 14px; }
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
            <div class="page-header">
                <div>
                    <div class="page-title">Performance</div>
                    <div class="page-subtitle">Track your progress and identify areas to improve.</div>
                </div>
                <div class="page-header-right">
                    <div class="search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search topics, quizzes, subjects...">
                    </div>
                    <button class="notif-btn" onclick="window.location.href='{{ route('messages.index') }}'" title="Messages" aria-label="Messages">
                        <i class="fas fa-comment-dots"></i>
                        @if($unreadMessages > 0)
                            <span class="notification-badge">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>
                        @endif
                    </button>
                    <button class="notif-btn" onclick="window.location.href='{{ route('notifications.index') }}'" title="Notifications" aria-label="Notifications">
                        <i class="fas fa-bell"></i>
                        @if($unreadNotifications > 0)
                            <span class="notification-badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                        @endif
                    </button>
                    <div style="position: relative;">
                        <button class="profile-btn" id="profileBtn">@include('partials.avatar-content')</button>
                        <div class="dropdown-menu" id="profileDropdown">
                            <a href="{{ route('settings') }}"><i class="fas fa-user"></i> Profile Settings</a>
                            <a href="{{ route('achievements') }}"><i class="fas fa-trophy"></i> Achievements</a>
                            <form method="POST" action="{{ route('logout') }}" style="margin: 0; padding: 0;">
                                @csrf
                                <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TOOLBAR: AI INSIGHTS + DATE RANGE -->
            <div class="toolbar-row">
                {{-- Inline fallback keeps the panel opening even if a script
                     error elsewhere stops the listener from being attached. --}}
                <button type="button" class="ai-btn" id="aiInsightsBtn"
                    onclick="document.getElementById('aiOverlay').classList.add('open')">
                    <i class="fas fa-wand-magic-sparkles"></i> AI Insights
                </button>
                <div class="date-range">
                    <i class="fas fa-calendar"></i>
                    <span id="dateRangeText">{{ $chartSeries['daily']['range'] }}</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>

            @php
                // "↑ 6.2% vs last week" delta line. $invert = a NEGATIVE delta is
                // the good direction (e.g. avg time per question).
                $deltaRow = function ($val, string $suffix = '%', bool $invert = false) {
                    $good = $invert ? $val < 0 : $val > 0;
                    $bad  = $invert ? $val > 0 : $val < 0;
                    $cls  = $good ? 'up' : ($bad ? 'down' : 'muted');
                    $icon = $val > 0 ? 'fa-arrow-up' : ($val < 0 ? 'fa-arrow-down' : 'fa-minus');
                    $txt  = ($val > 0 ? '+' : '') . $val . $suffix;
                    return [$cls, $icon, $txt];
                };

                // Tiny line-sparkline points for a 0-100 series in a 100x30 box.
                $sparkPts = function (array $vals) {
                    $n = count($vals);
                    if ($n < 2) {
                        return '';
                    }
                    $pts = [];
                    foreach ($vals as $i => $v) {
                        $x = round($i * (100 / ($n - 1)), 1);
                        $y = round(28 - ($v / 100) * 25, 1);
                        $pts[] = "$x,$y";
                    }
                    return implode(' ', $pts);
                };

                // Donut segments (dasharray/offset out of a 100-unit circumference)
                // with a small gap between adjacent slices.
                $donutSegs = function (array $slices) {
                    $off  = 0;
                    $segs = [];
                    foreach ($slices as $s) {
                        if ($s['pct'] <= 0) {
                            continue;
                        }
                        $len    = max(0.5, $s['pct'] - 1.5);
                        $segs[] = ['len' => $len, 'off' => -$off, 'color' => $s['color']];
                        $off   += $s['pct'];
                    }
                    return $segs;
                };

                $kpis = [
                    [
                        'icon' => 'fa-bullseye', 'card' => 't1', 'label' => 'Overall Accuracy',
                        'value' => $stats['accuracy'] . '%',
                        'delta' => $deltaRow($stats['accuracy_delta']),
                        'spark' => $spark['accuracy'],
                    ],
                    [
                        'icon' => 'fa-file-lines', 'card' => 't2', 'label' => 'Questions Answered',
                        'value' => number_format($stats['attempted']),
                        'delta' => $deltaRow($attemptedDeltaPct),
                        'spark' => $spark['attempted'],
                    ],
                    [
                        'icon' => 'fa-clock', 'card' => 't3', 'label' => 'Study Hours',
                        'value' => $studyHours . ' <small>hrs</small>',
                        'delta' => $deltaRow($studyHoursDelta),
                        'spark' => $spark['hours'],
                    ],
                    [
                        'icon' => 'fa-shield-halved', 'card' => 't6', 'label' => 'Readiness Score',
                        'value' => $readiness . ' <small>/100</small>',
                        'delta' => $deltaRow($readinessDelta, ' pts'),
                        'spark' => $spark['accuracy'],
                    ],
                ];
            @endphp

            <!-- KPI CARDS -->
            <div class="kpi-grid">
                {{-- Overall Accuracy --}}
                <div class="kpi-card t1">
                    <div class="kpi-shapes"><span></span><span></span></div>
                    <div class="kpi-left">
                        <div class="kpi-number">{!! $stats['accuracy'] !!}<small>%</small></div>
                        <span class="kpi-label">Overall Accuracy</span>
                        @php [$cls, $ic, $txt] = $deltaRow($stats['accuracy_delta']); @endphp
                        <div class="kpi-delta"><span class="d {{ $cls }}"><i class="fas {{ $ic }}"></i> {{ $txt }}</span> vs last week</div>
                    </div>
                    <div class="kpi-illust">
                        <img src="{{ asset('images/5.png') }}" alt="Overall Accuracy">
                    </div>
                </div>

                {{-- Questions Answered --}}
                <div class="kpi-card t2">
                    <div class="kpi-shapes"><span></span><span></span></div>
                    <div class="kpi-left">
                        <div class="kpi-number">{!! number_format($stats['attempted']) !!}</div>
                        <span class="kpi-label">Questions Answered</span>
                        @php [$cls, $ic, $txt] = $deltaRow($attemptedDeltaPct); @endphp
                        <div class="kpi-delta"><span class="d {{ $cls }}"><i class="fas {{ $ic }}"></i> {{ $txt }}</span> vs last week</div>
                    </div>
                    <div class="kpi-illust">
                        <img src="{{ asset('images/6.png') }}" alt="Questions Answered">
                    </div>
                </div>

                {{-- Study Hours --}}
                <div class="kpi-card t3">
                    <div class="kpi-shapes"><span></span><span></span></div>
                    <div class="kpi-left">
                        <div class="kpi-number">{!! $studyHours !!}<small> hrs</small></div>
                        <span class="kpi-label">Study Hours</span>
                        @php [$cls, $ic, $txt] = $deltaRow($studyHoursDelta); @endphp
                        <div class="kpi-delta"><span class="d {{ $cls }}"><i class="fas {{ $ic }}"></i> {{ $txt }}</span> vs last week</div>
                    </div>
                    <div class="kpi-illust">
                        <img src="{{ asset('images/9.png') }}" alt="Study Hours">
                    </div>
                </div>

                {{-- Readiness Score --}}
                <div class="kpi-card t6">
                    <div class="kpi-shapes"><span></span><span></span></div>
                    <div class="kpi-left">
                        <div class="kpi-number">{!! $readiness !!}<small>/100</small></div>
                        <span class="kpi-label">Readiness Score</span>
                        @php [$cls, $ic, $txt] = $deltaRow($readinessDelta, ' pts'); @endphp
                        <div class="kpi-delta"><span class="d {{ $cls }}"><i class="fas {{ $ic }}"></i> {{ $txt }}</span> vs last week</div>
                    </div>
                    <div class="kpi-illust">
                        <img src="{{ asset('images/8.png') }}" alt="Readiness Score">
                    </div>
                </div>
            </div>

            <!-- ROW 2: ACCURACY OVER TIME + STUDY DISTRIBUTION + EXAM COUNTDOWN -->
            <div class="row-2">
                <!-- ACCURACY OVER TIME -->
                <div class="card">
                    <div class="card-head">
                        <span class="card-title">Accuracy Over Time</span>
                        <select class="chart-select" id="chartGranularity">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly" selected>Monthly</option>
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
                            <svg id="chartSvg" viewBox="0 0 700 230" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="areaFill" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#c0392b" stop-opacity="0.16"/>
                                        <stop offset="100%" stop-color="#c0392b" stop-opacity="0"/>
                                    </linearGradient>
                                </defs>
                                <line x1="0" y1="2" x2="700" y2="2" stroke="#f1f3f5"/>
                                <line x1="0" y1="59" x2="700" y2="59" stroke="#f1f3f5"/>
                                <line x1="0" y1="116" x2="700" y2="116" stroke="#f1f3f5"/>
                                <line x1="0" y1="173" x2="700" y2="173" stroke="#f1f3f5"/>
                                <line x1="0" y1="228" x2="700" y2="228" stroke="#f1f3f5"/>
                                <path id="chartArea" d="{{ $chart['area'] }}" fill="url(#areaFill)"/>
                                <polyline id="chartLine" fill="none" stroke="#c0392b" stroke-width="3"
                                    vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round"
                                    points="{{ $chart['points'] }}"/>
                            </svg>
                            <div id="chartOverlay"></div>
                        </div>
                    </div>
                    <div class="x-axis" id="chartXAxis">
                        @foreach($chart['labels'] as $label)
                            <span>{{ $label }}</span>
                        @endforeach
                    </div>
                </div>

                <!-- STUDY DISTRIBUTION -->
                <div class="card">
                    <div class="card-head">
                        <span class="card-title">Study Distribution</span>
                    </div>
                    @if($studyDist['has_data'])
                        <div class="donut-body">
                            <div class="donut">
                                <svg viewBox="0 0 36 36">
                                    <circle cx="18" cy="18" r="14.5" fill="none" stroke="#f0f0f0" stroke-width="5.5"/>
                                    @foreach($donutSegs($studyDist['slices']) as $seg)
                                        <circle cx="18" cy="18" r="14.5" fill="none" stroke="{{ $seg['color'] }}" stroke-width="5.5"
                                            stroke-dasharray="{{ $seg['len'] }} {{ 100 - $seg['len'] }}" stroke-dashoffset="{{ $seg['off'] }}"/>
                                    @endforeach
                                </svg>
                                <div class="donut-center">
                                    <span class="num">{{ $studyDist['display_val'] }}</span>
                                    <span class="lbl">{{ $studyDist['display_unit'] }}</span>
                                </div>
                            </div>
                            <div class="donut-legend">
                                @foreach($studyDist['slices'] as $s)
                                    <div class="row">
                                        <span class="dot" style="background:{{ $s['color'] }}"></span>
                                        <span class="name">{{ $s['code'] }}</span>
                                        <span class="pct">{{ $s['pct'] }}%</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="empty-note">Complete quizzes to see how your study time is distributed across subjects.</div>
                    @endif
                </div>

                <!-- EXAM COUNTDOWN -->
                <div class="exam-card">
                    <div class="exam-bg">
                        <svg width="100%" height="100%" viewBox="0 0 300 240" preserveAspectRatio="none">
                            <circle cx="260" cy="40" r="70" fill="rgba(255,255,255,0.06)"/>
                            <circle cx="30" cy="200" r="55" fill="rgba(255,255,255,0.05)"/>
                            <circle cx="200" cy="190" r="40" fill="rgba(255,255,255,0.04)"/>
                            <ellipse cx="150" cy="30" rx="90" ry="35" fill="rgba(255,255,255,0.03)" transform="rotate(-12 150 30)"/>
                            <path d="M0,200 Q75,160 150,190 T300,170 V240 H0 Z" fill="rgba(0,0,0,0.08)"/>
                        </svg>
                    </div>
                    <i class="fas fa-flag flag" style="top:22%;right:30%;"></i>
                    <div class="exam-kicker">CPA Licensure Exam<br>is in</div>
                    @if($daysToExam !== null)
                        <div class="exam-days">{{ $daysToExam }}</div>
                        <div class="exam-days-lbl">{{ \Illuminate\Support\Str::plural('day', $daysToExam) }}</div>
                        <div class="exam-date">{{ \Illuminate\Support\Carbon::parse($examDate)->format('M j, Y') }}</div>
                    @else
                        <div class="exam-days">—</div>
                        <div class="exam-days-lbl">days</div>
                        <div class="exam-date">Set your target date in Settings</div>
                    @endif
                </div>
            </div>

            <!-- ROW 3: PERFORMANCE BY SUBJECT + WEEKLY ACCURACY + QUIZ TYPE + MINI STATS -->
            <div class="row-3">
                <!-- PERFORMANCE BY SUBJECT -->
                <div class="card">
                    <div class="card-head">
                        <span class="card-title">Performance by Subject</span>
                    </div>
                    @php $subjColors = ['#c0392b', '#3b7ddd', '#e8910b', '#8e44ad', '#21a366', '#d4589e']; @endphp
                    @foreach($subjectAccuracy as $si => $subj)
                        <div class="subject-row">
                            <span class="code">{{ $subj->code }}</span>
                            <div class="track"><span class="fill" style="width:{{ $subj->accuracy }}%;background:{{ $subjColors[$si % count($subjColors)] }};"></span></div>
                            <span class="val">{{ $subj->accuracy }}%</span>
                        </div>
                    @endforeach
                    <div class="axis-note"><span>0%</span><span>50%</span><span>100%</span></div>
                </div>

                <!-- MINI STATS 2x2 -->
                <div class="mini-grid">
                    <div class="mini-card">
                        <div class="mini-head"><i class="fas fa-clock red"></i><span>Study Time Today</span></div>
                        <div class="mini-value">{{ $todayStudy }}</div>
                        @php [$cls, $ic, $txt] = $deltaRow($todayDelta); @endphp
                        <div class="mini-sub"><span class="d {{ $cls }}"><i class="fas {{ $ic }}"></i> {{ $txt }}</span> vs yesterday</div>
                    </div>
                    <div class="mini-card">
                        <div class="mini-head"><i class="fas fa-circle-check green"></i><span>Consistency</span></div>
                        <div class="mini-value">{{ $consistencyPct }}%</div>
                        @php [$cls, $ic, $txt] = $deltaRow($consistencyDelta); @endphp
                        <div class="mini-sub"><span class="d {{ $cls }}"><i class="fas {{ $ic }}"></i> {{ $txt }}</span> vs last week</div>
                    </div>
                    <div class="mini-card">
                        <div class="mini-head"><i class="fas fa-triangle-exclamation red"></i><span>Weakest Topic</span></div>
                        @if($weakestTopic)
                            <div class="mini-value" title="{{ $weakestTopic->topic }}">{{ $weakestTopic->topic }}</div>
                            <div class="mini-sub">Accuracy <span class="d down">{{ $weakestTopic->accuracy }}%</span></div>
                        @else
                            <div class="mini-value">None yet</div>
                            <div class="mini-sub">No weak topics flagged</div>
                        @endif
                    </div>
                    <div class="mini-card">
                        <div class="mini-head"><i class="fas fa-bullseye amber"></i><span>Goal Progress</span></div>
                        <div class="mini-value">{{ $goalHours }} <small style="font-size:11px;color:var(--ink-3);font-weight:500;">/ {{ $goalTarget }} hrs</small></div>
                        <div class="mini-bar"><span style="width:{{ $goalPct }}%"></span></div>
                        <div class="mini-sub" style="text-align:right;">{{ $goalPct }}%</div>
                    </div>
                </div>
            </div>

            <!-- ROW 4: STUDY ACTIVITY HEATMAP + RECENT QUIZZES -->
            <div class="row-4">
                <!-- STUDY ACTIVITY (ONE YEAR) -->
                <div class="card hm-card">
                    <div class="card-head">
                        <span class="card-title">Study Activity (One Year)</span>
                        <span class="card-more">&#8230;</span>
                    </div>
                    @php $hmColors = ['#eef0f2', '#f6cdc9', '#ec9d96', '#dd6b62', '#c0392b']; @endphp
                    <div class="heatmap-scroll">
                        <div class="hm-months">
                            <span></span>
                            @php $printed = ''; @endphp
                            @foreach($heatmap['weeks'] as $wi => $w)
                                @php
                                    $lbl = $heatmap['labels'][$wi] ?? '';
                                    $show = ($lbl !== '' && $lbl !== $printed) ? $lbl : '';
                                    if ($show !== '') { $printed = $lbl; }
                                @endphp
                                <span>{{ $show }}</span>
                            @endforeach
                        </div>
                        <div class="hm-body">
                            <div class="hm-days">
                                <span></span><span>Mon</span><span></span><span>Wed</span><span></span><span>Fri</span><span></span>
                            </div>
                            <div class="hm-weeks">
                                @foreach($heatmap['weeks'] as $w)
                                    <div class="hm-week">
                                        @foreach($w as $cell)
                                            <div class="hm-cell" @if($cell['level'] !== null) style="background:{{ $hmColors[$cell['level']] }};" title="{{ $cell['date'] }} — {{ $cell['count'] }} {{ \Illuminate\Support\Str::plural('question', $cell['count']) }}" @endif></div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="hm-legend">
                        <span style="margin-right:2px;">Less</span>
                        @foreach($hmColors as $c)
                            <span class="hm-cell" style="background:{{ $c }};"></span>
                        @endforeach
                        <span style="margin-left:2px;">More</span>
                    </div>
                </div>

                <!-- RECENT QUIZZES -->
                <div class="card">
                    <div class="card-head">
                        <span class="card-title">Recent Quizzes</span>
                        <a href="{{ route('adaptive-quizzes') }}" class="view-all-btn">View all <i class="fas fa-chevron-right" style="font-size:9px;"></i></a>
                    </div>
                    @php
                        $modeIcons = [
                            'adaptive'  => ['fa-brain', 'green'],
                            'topic'     => ['fa-tag', 'amber'],
                            'timed'     => ['fa-clock', 'blue'],
                            'challenge' => ['fa-trophy', 'red'],
                        ];
                        $modeLabels = [
                            'adaptive' => 'Adaptive Quiz', 'topic' => 'Topic Quiz',
                            'timed' => 'Timed Quiz', 'challenge' => 'Challenge Quiz',
                        ];
                    @endphp
                    @if($recentActivity->isEmpty())
                        <div class="empty-note">No quizzes completed yet.</div>
                    @else
                        <table class="quiz-table">
                            <thead>
                                <tr><th>Quiz</th><th>Subject</th><th>Score</th><th>Duration</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                                @foreach($recentActivity as $a)
                                    @php
                                        [$icon, $tone] = $modeIcons[$a->mode] ?? ['fa-file-alt', 'grey'];
                                        $score = (int) round($a->score_percent);
                                        $sc = $score >= 75 ? '#21a366' : ($score >= 60 ? '#3b7ddd' : ($score >= 45 ? '#e8910b' : '#c0392b'));
                                    @endphp
                                    <tr>
                                        <td><span class="quiz-name"><i class="fas {{ $icon }} {{ $tone }}"></i> {{ $modeLabels[$a->mode] ?? 'Quiz' }}</span></td>
                                        <td>{{ $a->subject_code ?? 'General' }}</td>
                                        <td><span class="score-val" style="color:{{ $sc }};">{{ $score }}%</span></td>
                                        <td>{{ $a->duration }}</td>
                                        <td>{{ \Illuminate\Support\Carbon::parse($a->completed_at)->format('M j, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <!-- AI INSIGHTS PANEL -->
    <div class="ai-overlay" id="aiOverlay">
        <div class="ai-panel">
            <div class="ai-panel-head">
                <span class="ai-panel-title"><i class="fas fa-wand-magic-sparkles"></i> AI Insights</span>
                <button type="button" id="aiRefreshBtn" title="Regenerate insights"><i class="fas fa-rotate-right"></i></button>
                <button type="button" id="aiCloseBtn" title="Close"
                    onclick="document.getElementById('aiOverlay').classList.remove('open')"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="ai-panel-body" id="aiPanelBody">
                <div class="ai-loading"><i class="fas fa-circle-notch fa-spin"></i> Analyzing your performance...</div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Fade-in animation
            document.querySelectorAll('.card, .kpi-card, .mini-card, .exam-card').forEach((el, index) => {
                el.style.animation = `slideUp 0.45s ease ${index * 0.04}s both`;
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

            // Accuracy Over Time — Daily / Weekly / Monthly granularity. The SVG
            // geometry and the dot/label overlay are rebuilt from the raw series
            // so the dropdown switches the curve without a reload.
            const chartSeries = @json($chartSeries);
            const chartEls = {
                line:    document.getElementById('chartLine'),
                area:    document.getElementById('chartArea'),
                overlay: document.getElementById('chartOverlay'),
                xAxis:   document.getElementById('chartXAxis'),
                range:   document.getElementById('dateRangeText'),
            };

            function renderPerfChart(series) {
                if (!series || !chartEls.line) return;
                const W = 700, top = 2, bottom = 228;

                // Carry the last known accuracy across empty buckets so the line
                // stays continuous (matches the server-side daily render).
                let last = 0;
                const acc = series.values.map(v => (v === null ? last : (last = v)));
                const n = acc.length;

                const pts = acc.map((v, i) => {
                    const x = n > 1 ? +(i * (W / (n - 1))).toFixed(1) : 0;
                    const y = +(bottom - (v / 100) * (bottom - top)).toFixed(1);
                    return [x, y];
                });

                chartEls.line.setAttribute('points', pts.map(p => p.join(',')).join(' '));
                chartEls.area.setAttribute('d',
                    'M' + pts.map(p => p.join(',')).join(' L') +
                    ` L${pts[n - 1][0]},${bottom} L${pts[0][0]},${bottom} Z`);

                // Dots + % labels live in an HTML overlay (not the SVG) so they
                // don't stretch when the chart resizes.
                chartEls.overlay.innerHTML = pts.map(([x, y], i) => {
                    const lx = Math.min(96, Math.max(3, x / W * 100));
                    const ly = y / 230 * 100;
                    const lblTop = Math.max(6, ly - 5);
                    return `<div class="pt-dot" style="left:${lx}%;top:${ly}%"></div>` +
                           `<div class="pt-lbl" style="left:${lx}%;top:${lblTop}%">${acc[i]}%</div>`;
                }).join('');

                chartEls.xAxis.innerHTML = series.labels.map(l => `<span>${l}</span>`).join('');
                if (chartEls.range) chartEls.range.textContent = series.range;
            }

            renderPerfChart(chartSeries.monthly);

            const granularity = document.getElementById('chartGranularity');
            if (granularity) {
                granularity.addEventListener('change', function () {
                    renderPerfChart(chartSeries[this.value]);
                });
            }
        });

        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideUp {
                from { opacity: 0; transform: translateY(16px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(style);

        // ── AI Insights panel (personalised, generated from this student's data) ──
        (function () {
            const INSIGHTS_URL = "{{ route('ai-tutor.performance-insights') }}";
            const overlay    = document.getElementById('aiOverlay');
            const body       = document.getElementById('aiPanelBody');
            const openBtn    = document.getElementById('aiInsightsBtn');
            const refreshBtn = document.getElementById('aiRefreshBtn');
            const closeBtn   = document.getElementById('aiCloseBtn');
            if (!overlay || !openBtn) return;

            let loaded = false;

            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            async function loadInsights(refresh = false) {
                body.innerHTML = '<div class="ai-loading"><i class="fas fa-circle-notch fa-spin"></i> Analyzing your performance... this can take up to a minute.</div>';
                refreshBtn.disabled = true;

                // The AI call can be slow - give it 90s, then fail with a
                // retry message instead of spinning forever.
                const ctrl  = new AbortController();
                const timer = setTimeout(() => ctrl.abort(), 90000);

                try {
                    const res  = await fetch(INSIGHTS_URL + (refresh ? '?refresh=1' : ''), {
                        headers: { 'Accept': 'application/json' },
                        signal: ctrl.signal,
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.ok || !Array.isArray(data.insights)) {
                        throw new Error(data.message || ('Request failed (HTTP ' + res.status + ').'));
                    }
                    body.innerHTML = data.insights.map(ins => `
                        <div class="insight-item ${escapeHtml(ins.tone)}">
                            <div class="insight-icon"><i class="fas ${escapeHtml(ins.icon)}"></i></div>
                            <div class="insight-content">
                                <div class="insight-title">${escapeHtml(ins.title)}</div>
                                <div class="insight-desc">${escapeHtml(ins.desc)}</div>
                            </div>
                        </div>
                    `).join('');
                    loaded = true;
                } catch (e) {
                    console.error('AI insights failed:', e);
                    const why = e.name === 'AbortError'
                        ? 'The request timed out.'
                        : escapeHtml(e.message || 'AI insights are unavailable right now.');
                    body.innerHTML = '<div class="ai-error"><i class="fas fa-circle-exclamation"></i> ' + why
                        + ' <button type="button" class="ai-retry" id="aiRetryBtn">Try again</button></div>';
                    const retry = document.getElementById('aiRetryBtn');
                    if (retry) retry.addEventListener('click', () => loadInsights(refresh));
                } finally {
                    clearTimeout(timer);
                    refreshBtn.disabled = false;
                }
            }

            function openPanel() {
                overlay.classList.add('open');
                if (!loaded) loadInsights();
            }

            function closePanel() {
                overlay.classList.remove('open');
            }

            openBtn.addEventListener('click', openPanel);
            closeBtn.addEventListener('click', closePanel);
            refreshBtn.addEventListener('click', () => loadInsights(true));
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closePanel();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closePanel();
            });
        })();
    </script>
</body>
</html>
