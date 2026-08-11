<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Quiz – {{ $session->subject->name ?? 'CPACE' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:       #7B1D1D;
            --primary-hover: #6a1818;
            --primary-light: #f5e8e8;
            --accent:        #c0392b;
            --green:         #10b981;
            --green-light:   #d1fae5;
            --amber:         #f59e0b;
            --amber-light:   #fef3c7;
            --gray:          #9ca3af;
        }
        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }

        /* ══════════ BASE (mobile-first phone shell) ══════════ */
        html, body { height:100%; overflow:hidden; }
        body {
            font-family:'Poppins',sans-serif;
            background:#1a0a0a;
            color:#1a1a1a;
            display:flex;
            justify-content:center;
            align-items:stretch;
        }

        .quiz-wrap {
            width:100%;
            max-width:480px;
            background:#f5f5f7;
            display:flex;
            flex-direction:column;
            height:100%;
            position:relative;
            overflow:hidden;
        }

        /* Content row (mobile: just a column flex) */
        .quiz-content-row {
            display:flex;
            flex-direction:column;
            flex:1;
            overflow:hidden;
        }

        /* Sidebar hidden on mobile */
        .quiz-sidebar { display:none; }

        /* Main panel */
        .quiz-main {
            display:flex;
            flex-direction:column;
            flex:1;
            overflow:hidden;
            position:relative;   /* anchors the live-room toasts */
        }

        /* ══════════ HEADER ══════════ */
        .hdr {
            background:var(--primary);
            padding:14px 18px 0;
            flex-shrink:0;
        }
        .hdr-row {
            display:flex;
            align-items:center;
            gap:10px;
            padding-bottom:12px;
        }
        .hdr-back {
            width:36px; height:36px;
            background:rgba(255,255,255,.15);
            border:none; border-radius:10px;
            color:#fff; font-size:14px;
            display:flex; align-items:center; justify-content:center;
            cursor:pointer; flex-shrink:0;
        }
        .hdr-info { flex:1; min-width:0; }
        .hdr-subject { font-size:14px; font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .hdr-mode    { font-size:10px; color:rgba(255,255,255,.6); margin-top:1px; }
        .hdr-timer {
            display:flex; align-items:center; gap:5px;
            background:rgba(255,255,255,.18);
            border-radius:20px; padding:6px 12px;
            font-size:13px; font-weight:700; color:#fff;
            flex-shrink:0;
        }
        .hdr-timer.warning { background:#d97706; }
        .hdr-timer.danger  { background:var(--accent); animation:blink 1s infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.4} }

        .hdr-progress {
            height:4px;
            background:rgba(255,255,255,.2);
            border-radius:0;
            overflow:hidden;
        }
        .hdr-progress-fill {
            height:100%;
            background:#fff;
            border-radius:0 4px 4px 0;
            transition:width .4s ease;
        }

        /* ══════════ QUESTION COUNTER STRIP ══════════ */
        .q-counter-strip {
            background:#fff;
            padding:10px 18px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            border-bottom:1px solid #ebebeb;
            flex-shrink:0;
        }
        .q-counter-num  { font-size:13px; font-weight:700; color:var(--primary); }
        .q-counter-stats { display:flex; gap:8px; }
        .q-chip {
            display:inline-flex; align-items:center; gap:4px;
            font-size:10px; font-weight:600;
            padding:3px 9px; border-radius:20px;
        }
        .q-chip.ans  { background:var(--green-light); color:#065f46; }
        .q-chip.flag { background:var(--amber-light);  color:#92400e; }
        .q-chip.left { background:#f3f4f6;              color:#6b7280; }

        /* ══════════ SCROLLABLE BODY ══════════ */
        .q-body {
            flex:1;
            overflow-y:auto;
            -webkit-overflow-scrolling:touch;
            padding:20px 18px 24px;
        }

        /* Flag banner */
        .flag-banner {
            display:none;
            align-items:center;
            gap:8px;
            background:var(--amber-light);
            border:1.5px solid #fcd34d;
            border-radius:10px;
            padding:8px 14px;
            font-size:12px; font-weight:600; color:#92400e;
            margin-bottom:14px;
        }
        .flag-banner.visible { display:flex; }

        /* Question card */
        .q-card {
            background:#fff;
            border-radius:16px;
            padding:20px 18px;
            margin-bottom:14px;
            box-shadow:0 1px 6px rgba(0,0,0,.07);
        }
        .q-card-label {
            font-size:10px; font-weight:700;
            color:var(--gray);
            text-transform:uppercase;
            letter-spacing:.8px;
            margin-bottom:10px;
        }
        .q-text { font-size:16px; font-weight:500; color:#111; line-height:1.65; }

        /* Choices */
        .choices { display:flex; flex-direction:column; gap:10px; }
        .choice {
            display:flex; align-items:center; gap:14px;
            padding:16px 16px;
            background:#fff;
            border:2px solid #e5e7eb;
            border-radius:14px;
            cursor:pointer;
            transition:border-color .15s, background .15s, transform .08s;
            -webkit-user-select:none; user-select:none;
        }
        .choice:active { transform:scale(.98); }
        .choice.selected { border-color:var(--primary); background:var(--primary-light); }
        .choice input[type=radio] { display:none; }
        .choice-letter {
            width:34px; height:34px;
            border-radius:50%;
            background:#f3f4f6;
            border:2px solid #e5e7eb;
            display:flex; align-items:center; justify-content:center;
            font-size:13px; font-weight:700; color:#555;
            flex-shrink:0;
            transition:background .15s, border-color .15s, color .15s;
        }
        .choice.selected .choice-letter { background:var(--primary); border-color:var(--primary); color:#fff; }
        .choice-text { font-size:14px; color:#222; line-height:1.5; flex:1; }
        .choice-check {
            width:20px; height:20px;
            border-radius:50%;
            background:var(--primary);
            display:none;
            align-items:center; justify-content:center;
            color:#fff; font-size:10px;
            flex-shrink:0;
        }
        .choice.selected .choice-check { display:flex; }

        .q-slide { display:none; }
        .q-slide.active { display:block; }

        /* Training mode feedback */
        .choice.reveal-correct {
            border-color:#10b981 !important; background:#ecfdf5 !important; pointer-events:none;
        }
        .choice.reveal-correct .choice-letter {
            background:#10b981 !important; border-color:#10b981 !important; color:#fff !important;
        }
        .choice.reveal-wrong {
            border-color:#ef4444 !important; background:#fef2f2 !important; pointer-events:none;
        }
        .choice.reveal-wrong .choice-letter {
            background:#ef4444 !important; border-color:#ef4444 !important; color:#fff !important;
        }
        .q-slide.revealed .choice { pointer-events:none; }

        .feedback-panel {
            display:none;
            margin-top:14px;
            border-radius:14px;
            padding:14px 16px;
            font-size:13px; font-weight:600; line-height:1.55;
        }
        .feedback-panel.correct { background:#ecfdf5; color:#065f46; border:1.5px solid #6ee7b7; }
        .feedback-panel.wrong   { background:#fef2f2; color:#991b1b; border:1.5px solid #fca5a5; }
        .feedback-panel.visible { display:block; }
        .feedback-panel .explain-text { margin-top:8px; font-weight:400; color:#555; font-size:12px; }

        .training-badge {
            display:inline-flex; align-items:center; gap:5px;
            background:#d1fae5; color:#065f46;
            border-radius:20px; padding:3px 10px;
            font-size:10px; font-weight:700;
        }

        /* ══════════ BOTTOM NAV ══════════ */
        .bottom-nav {
            background:#fff;
            border-top:1px solid #e8e8e8;
            padding:10px 16px 14px;
            flex-shrink:0;
            box-shadow:0 -4px 16px rgba(0,0,0,.07);
        }
        .bottom-nav-row { display:flex; align-items:center; gap:8px; }
        .bn-btn {
            display:flex; align-items:center; justify-content:center; gap:6px;
            border:none; border-radius:12px;
            font-family:'Poppins',sans-serif;
            font-size:13px; font-weight:600;
            cursor:pointer;
            transition:all .15s;
            height:48px; padding:0 14px;
        }
        .bn-flag {
            background:var(--amber-light); color:#92400e;
            border:1.5px solid #fcd34d;
            width:48px; padding:0; flex-shrink:0;
        }
        .bn-flag.active { background:var(--amber); color:#fff; border-color:var(--amber); }
        .bn-prev { background:#f3f4f6; color:#374151; border:1.5px solid #e5e7eb; flex:1; }
        .bn-prev:disabled { opacity:.4; cursor:not-allowed; }
        .bn-next { background:var(--primary); color:#fff; flex:2; }
        .bn-next:hover { background:var(--primary-hover); }
        .bn-grid {
            background:#f3f4f6; color:#374151;
            border:1.5px solid #e5e7eb;
            width:48px; padding:0; flex-shrink:0; position:relative;
        }
        .bn-grid-badge {
            position:absolute; top:-5px; right:-5px;
            width:16px; height:16px;
            background:var(--accent); color:#fff;
            border-radius:50%; font-size:9px; font-weight:700;
            display:flex; align-items:center; justify-content:center;
        }

        /* ══════════ BOTTOM SHEET NAVIGATOR (mobile) ══════════ */
        .sheet-overlay {
            display:none; position:fixed; inset:0;
            background:rgba(0,0,0,.5); z-index:50;
        }
        .sheet-overlay.open { display:block; }
        .nav-sheet {
            position:fixed;
            bottom:0; left:50%; transform:translateX(-50%) translateY(100%);
            width:100%; max-width:480px;
            background:#fff; border-radius:20px 20px 0 0;
            z-index:51;
            transition:transform .3s cubic-bezier(.32,0,.67,0);
            max-height:80vh; display:flex; flex-direction:column;
        }
        .nav-sheet.open { transform:translateX(-50%) translateY(0); }
        .sheet-handle {
            width:40px; height:4px;
            background:#e5e7eb; border-radius:2px;
            margin:12px auto 0; flex-shrink:0;
        }
        .sheet-title {
            padding:14px 18px 10px;
            font-size:14px; font-weight:700; color:#1a1a1a;
            border-bottom:1px solid #f0f0f0; flex-shrink:0;
            display:flex; align-items:center; justify-content:space-between;
        }
        .sheet-close {
            width:28px; height:28px; background:#f3f4f6;
            border:none; border-radius:50%; font-size:13px; color:#555;
            cursor:pointer; display:flex; align-items:center; justify-content:center;
        }
        .sheet-legend {
            display:flex; gap:12px; flex-wrap:wrap;
            padding:10px 18px; border-bottom:1px solid #f0f0f0; flex-shrink:0;
        }
        .leg-item { display:flex; align-items:center; gap:5px; font-size:10px; color:#666; }
        .leg-dot  { width:10px; height:10px; border-radius:3px; flex-shrink:0; }
        .sheet-grid {
            flex:1; overflow-y:auto; padding:14px 18px 24px;
            display:flex; flex-wrap:wrap; align-content:flex-start; gap:8px;
        }
        .nav-btn {
            width:44px; height:44px; border-radius:10px;
            border:2px solid #e5e7eb; background:#f9fafb;
            font-size:13px; font-weight:600; color:#555;
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            position:relative; transition:all .15s; font-family:'Poppins',sans-serif;
        }
        .nav-btn.current  { background:var(--primary); border-color:var(--primary); color:#fff; }
        .nav-btn.answered { background:var(--green-light); border-color:#6ee7b7; color:#065f46; }
        .nav-btn.flagged  { background:var(--amber-light); border-color:#fcd34d; color:#92400e; }
        .nav-btn.current.answered, .nav-btn.current.flagged { background:var(--primary); border-color:var(--primary); color:#fff; }
        .nav-btn .flag-dot {
            position:absolute; top:2px; right:2px;
            width:7px; height:7px;
            background:var(--amber); border-radius:50%; display:none;
        }
        .nav-btn.flagged .flag-dot { display:block; }
        .nav-btn.current .flag-dot { background:rgba(255,255,255,.85); }

        /* ══════════ END QUIZ MODAL ══════════ */
        .modal-overlay {
            display:none; position:fixed; inset:0;
            background:rgba(0,0,0,.5);
            z-index:100; align-items:flex-end; justify-content:center;
        }
        .modal-overlay.open { display:flex; }
        .modal {
            background:#fff; border-radius:20px 20px 0 0;
            padding:28px 22px 36px;
            width:100%; max-width:480px;
            box-shadow:0 -8px 40px rgba(0,0,0,.2);
            animation:slideUp .25s ease;
        }
        @keyframes slideUp { from{transform:translateY(100%)} to{transform:translateY(0)} }
        .modal-handle { width:36px; height:4px; background:#e5e7eb; border-radius:2px; margin:0 auto 20px; }
        .modal h3 { font-size:17px; font-weight:700; color:#1a1a1a; margin-bottom:6px; }
        .modal p  { font-size:13px; color:#666; line-height:1.6; margin-bottom:16px; }
        .modal-chips { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:22px; }
        .modal-actions { display:flex; flex-direction:column; gap:10px; }
        .modal-btn {
            width:100%; padding:15px; border-radius:14px; border:none;
            font-size:14px; font-weight:700; font-family:'Poppins',sans-serif; cursor:pointer;
        }
        .modal-btn.primary { background:var(--primary); color:#fff; }
        .modal-btn.ghost   { background:#f3f4f6; color:#374151; }

        /* ══════════ START GATE OVERLAY ══════════ */
        .start-overlay {
            display:flex; position:fixed; inset:0; z-index:10000;
            align-items:center; justify-content:center;
            background:rgba(10,5,5,.92);
            backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px);
        }
        .start-overlay.hidden { display:none; }

        /* ══════════ ANTI-CHEAT OVERLAY ══════════ */
        .tab-overlay {
            display:none; position:fixed; inset:0; z-index:9999;
            align-items:center; justify-content:center;
            background:rgba(10,5,5,.92);
            backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px);
        }
        .tab-overlay.show { display:flex; }
        .tab-overlay-box {
            background:#fff; border-radius:20px; padding:36px 28px;
            width:90%; max-width:360px; text-align:center;
            box-shadow:0 24px 80px rgba(0,0,0,.5);
            animation:popIn .22s cubic-bezier(.34,1.56,.64,1);
        }
        @keyframes popIn { from{transform:scale(.82);opacity:0} to{transform:scale(1);opacity:1} }
        .tab-overlay-icon {
            width:70px; height:70px; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            margin:0 auto 16px; font-size:28px;
        }
        .tab-overlay-icon.warn { background:#fee2e2; color:var(--accent); }
        .tab-overlay-icon.crit { background:#7f1d1d; color:#fff; }
        .tab-overlay-box h2 { font-size:19px; font-weight:700; color:#1a1a1a; margin-bottom:6px; }
        .tab-overlay-box p  { font-size:12px; color:#555; line-height:1.65; margin-bottom:16px; }
        .violation-bar { display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:14px; }
        .v-dot { width:14px; height:14px; border-radius:50%; background:#e5e7eb; border:2px solid #d1d5db; transition:all .3s; }
        .v-dot.used  { background:var(--amber);  border-color:var(--amber); }
        .v-dot.final { background:var(--accent); border-color:var(--accent); }
        .violation-label { font-size:11px; font-weight:700; border-radius:20px; padding:4px 12px; display:inline-block; margin-bottom:18px; }
        .violation-label.warn     { background:#fef3c7; color:#92400e; }
        .violation-label.critical { background:#fee2e2; color:var(--accent); }
        .tab-overlay-actions { display:flex; flex-direction:column; gap:8px; }
        .btn-resume {
            display:flex; align-items:center; justify-content:center; gap:8px;
            background:var(--primary); color:#fff;
            border:none; border-radius:12px; padding:14px;
            font-size:14px; font-weight:700; font-family:'Poppins',sans-serif; cursor:pointer;
        }
        .btn-leave {
            display:flex; align-items:center; justify-content:center; gap:8px;
            background:#fff; color:#999;
            border:1.5px solid #e5e7eb; border-radius:12px; padding:12px;
            font-size:12px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer;
        }
        .btn-leave:hover { border-color:#fca5a5; color:var(--accent); }

        .quiz-frozen .quiz-main > *:not(.tab-overlay),
        .quiz-frozen .quiz-sidebar { filter:blur(5px) brightness(.55); pointer-events:none; user-select:none; }

        /* ══════════ SIDEBAR COMPONENT STYLES ══════════ */
        .qs-section-title {
            font-size:10.5px; font-weight:700; color:#aaa;
            letter-spacing:.9px; text-transform:uppercase; margin-bottom:10px;
        }
        .qs-stats-row { display:flex; flex-direction:column; gap:8px; }
        .qs-stat-item {
            display:flex; align-items:center; justify-content:space-between;
            padding:10px 14px; border-radius:10px;
        }
        .qs-stat-item.ans  { background:var(--green-light); }
        .qs-stat-item.flag { background:var(--amber-light); }
        .qs-stat-item.left { background:#f3f4f6; }
        .qs-stat-label {
            font-size:12px; font-weight:600;
            display:flex; align-items:center; gap:7px;
        }
        .qs-stat-label.ans  { color:#065f46; }
        .qs-stat-label.flag { color:#92400e; }
        .qs-stat-label.left { color:#6b7280; }
        .qs-stat-value { font-size:18px; font-weight:700; }
        .qs-stat-value.ans  { color:#065f46; }
        .qs-stat-value.flag { color:#92400e; }
        .qs-stat-value.left { color:#374151; }

        .qs-legend { display:flex; flex-direction:column; gap:7px; }
        .qs-leg-item { display:flex; align-items:center; gap:9px; font-size:11px; color:#666; }
        .qs-leg-dot  { width:12px; height:12px; border-radius:4px; flex-shrink:0; }

        .qs-nav-grid { display:flex; flex-wrap:wrap; gap:6px; }
        .snav-btn {
            width:38px; height:38px; border-radius:9px;
            border:2px solid #e5e7eb; background:#f9fafb;
            font-size:12px; font-weight:600; color:#555;
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            position:relative; transition:all .15s; font-family:'Poppins',sans-serif;
        }
        .snav-btn:hover { border-color:var(--primary); color:var(--primary); }
        .snav-btn.current  { background:var(--primary); border-color:var(--primary); color:#fff; }
        .snav-btn.answered { background:var(--green-light); border-color:#6ee7b7; color:#065f46; }
        .snav-btn.flagged  { background:var(--amber-light); border-color:#fcd34d; color:#92400e; }
        .snav-btn.current.answered,
        .snav-btn.current.flagged { background:var(--primary); border-color:var(--primary); color:#fff; }
        .snav-btn .flag-dot {
            position:absolute; top:2px; right:2px;
            width:6px; height:6px;
            background:var(--amber); border-radius:50%; display:none;
        }
        .snav-btn.flagged .flag-dot { display:block; }
        .snav-btn.current .flag-dot { background:rgba(255,255,255,.85); }

        /* ══════════ LIVE ROOM (AI pace rivals) ══════════ */
        .room-block { display:none; }
        .room-block.on { display:block; }

        .room-head {
            display:flex; align-items:center; justify-content:space-between;
            gap:8px; margin-bottom:10px;
        }
        .room-live {
            display:inline-flex; align-items:center; gap:5px;
            background:#fee2e2; color:#b91c1c;
            border-radius:20px; padding:2px 9px;
            font-size:9px; font-weight:700; letter-spacing:.6px;
        }
        .room-live .live-dot {
            width:6px; height:6px; border-radius:50%; background:#ef4444;
            animation:pulseDot 1.4s infinite;
        }
        @keyframes pulseDot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.3;transform:scale(.6)} }

        /* Rank + momentum hero */
        .room-hero {
            background:linear-gradient(135deg,#7B1D1D 0%,#c0392b 100%);
            border-radius:14px; padding:13px 14px; color:#fff; margin-bottom:12px;
            box-shadow:0 4px 14px rgba(123,29,29,.25);
        }
        .room-hero-top { display:flex; align-items:flex-end; justify-content:space-between; gap:10px; }
        .room-rank { font-size:27px; font-weight:700; line-height:1; letter-spacing:-.5px; }
        .room-rank-sub { font-size:10px; opacity:.8; margin-top:3px; }
        .room-streak {
            display:inline-flex; align-items:center; gap:4px;
            background:rgba(255,255,255,.18); border-radius:20px;
            padding:5px 11px; font-size:12px; font-weight:700;
        }
        .room-streak.hot { background:#fbbf24; color:#7c2d12; }
        .room-mom { margin-top:11px; }
        .room-mom-label {
            display:flex; justify-content:space-between;
            font-size:9.5px; font-weight:600; opacity:.85; margin-bottom:5px;
            text-transform:uppercase; letter-spacing:.5px;
        }
        .room-mom-track { height:6px; background:rgba(255,255,255,.22); border-radius:4px; overflow:hidden; }
        .room-mom-fill {
            display:block; height:100%; width:0;
            background:linear-gradient(90deg,#fbbf24,#fde68a);
            border-radius:4px; transition:width .45s cubic-bezier(.34,1.3,.64,1);
        }

        /* Rival rows */
        .room-row {
            display:flex; align-items:center; gap:9px;
            padding:7px 0;
            transition:opacity .3s;
        }
        .room-row.done { opacity:.55; }
        .room-av {
            width:28px; height:28px; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            color:#fff; font-size:11px; font-weight:700; flex-shrink:0;
        }
        .room-row-body { flex:1; min-width:0; }
        .room-row-top {
            display:flex; align-items:baseline; justify-content:space-between; gap:8px;
        }
        .room-name {
            font-size:11.5px; font-weight:600; color:#374151;
            white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
        }
        .room-row.you .room-name { color:var(--primary); font-weight:700; }
        .room-tag { font-size:9px; color:#9ca3af; font-weight:500; }
        .room-q { font-size:10px; font-weight:700; color:#6b7280; flex-shrink:0; }
        .room-bar { height:5px; background:#eceef1; border-radius:3px; margin-top:5px; overflow:hidden; }
        .room-bar span { display:block; height:100%; width:0; border-radius:3px; transition:width .6s ease; }

        /* Feed */
        .room-feed {
            margin-top:10px; padding-top:9px;
            border-top:1px dashed #e8e8e8;
            display:flex; flex-direction:column; gap:6px;
        }
        .room-feed-item {
            display:flex; align-items:flex-start; gap:6px;
            font-size:10.5px; color:#7a7a7a; line-height:1.45;
            animation:feedIn .3s ease;
        }
        .room-feed-item i { color:#bbb; font-size:9px; margin-top:3px; flex-shrink:0; }
        @keyframes feedIn { from{opacity:0;transform:translateY(-4px)} to{opacity:1;transform:none} }
        .room-note {
            margin-top:10px; font-size:9px; color:#b0b0b0;
            display:flex; align-items:center; gap:5px; line-height:1.4;
        }

        /* Toasts */
        .room-toasts {
            position:absolute; top:10px; left:50%; transform:translateX(-50%);
            z-index:60; display:flex; flex-direction:column; align-items:center; gap:7px;
            pointer-events:none; width:max-content; max-width:90%;
        }
        .room-toast {
            display:flex; align-items:center; gap:8px;
            background:rgba(17,24,39,.94); color:#fff;
            border-radius:22px; padding:9px 16px;
            font-size:12px; font-weight:600; white-space:nowrap;
            box-shadow:0 8px 26px rgba(0,0,0,.28);
            animation:toastIn .32s cubic-bezier(.34,1.56,.64,1);
            transition:opacity .3s, transform .3s;
        }
        .room-toast.good { background:linear-gradient(135deg,#047857,#10b981); }
        .room-toast.warn { background:linear-gradient(135deg,#b45309,#f59e0b); }
        .room-toast.fire { background:linear-gradient(135deg,#7B1D1D,#e11d48); }
        .room-toast.out  { opacity:0; transform:translateY(-10px); }
        @keyframes toastIn { from{opacity:0;transform:translateY(-12px) scale(.9)} to{opacity:1;transform:none} }

        /* Mobile trigger pill (in the counter strip) */
        .room-pill {
            display:none; align-items:center; gap:5px;
            background:var(--primary); color:#fff;
            border:none; border-radius:20px; padding:4px 10px;
            font-size:10px; font-weight:700; font-family:'Poppins',sans-serif;
            cursor:pointer;
        }
        .room-pill.on { display:inline-flex; }
        .room-pill .pill-streak { opacity:.85; }

        /* Mobile room sheet reuses the navigator sheet shell */
        .room-sheet-body { padding:16px 18px 26px; overflow-y:auto; }

        /* ══════════ DESKTOP LAYOUT (≥ 900px) ══════════ */
        @media (min-width:900px) {
            html, body { overflow:hidden; background:#f0f2f5; }

            body { justify-content:flex-start; align-items:stretch; }

            .quiz-wrap {
                max-width:none; width:100%;
                background:#f0f2f5;
                display:flex; flex-direction:column;
                height:100vh; overflow:hidden;
            }

            /* Header: full width, slightly taller */
            .hdr { padding:16px 28px 0; }
            .hdr-row { padding-bottom:14px; }
            .hdr-subject { font-size:16px; }
            .hdr-mode    { font-size:11px; }
            .hdr-timer   { font-size:14px; padding:7px 16px; }
            .hdr-back    { width:40px; height:40px; font-size:15px; border-radius:12px; }

            /* Content row: sidebar + main side by side */
            .quiz-content-row {
                flex-direction:row;
                flex:1;
                overflow:hidden;
            }

            /* Sidebar */
            .quiz-sidebar {
                display:flex;
                flex-direction:column;
                width:260px;
                flex-shrink:0;
                background:#fff;
                border-right:1px solid #e8e8e8;
                overflow-y:auto;
                padding:20px 18px;
                gap:18px;
            }

            /* Main quiz panel */
            .quiz-main {
                flex:1;
                display:flex;
                flex-direction:column;
                overflow:hidden;
                background:#f4f5f7;
            }

            /* The room lives in the sidebar on desktop; the mobile pill + sheet
               are redundant there. */
            .room-pill.on { display:none; }
            #roomSheet { display:none !important; }

            /* Keep the sidebar copy compact so the question map stays in reach. */
            .quiz-sidebar .room-row { padding:5px 0; }
            .quiz-sidebar .room-feed-item:nth-child(n+3) { display:none; }

            /* Counter strip hidden (stats live in sidebar) */
            .q-counter-strip { display:none; }

            /* Spacious question body */
            .q-body { padding:28px 40px 30px; }
            .q-card { padding:28px 28px; border-radius:18px; }
            .q-text { font-size:17px; }
            .q-card-label { font-size:11px; }

            /* Larger choices */
            .choices { gap:12px; }
            .choice { padding:18px 20px; border-radius:16px; }
            .choice-text { font-size:15px; }
            .choice-letter { width:38px; height:38px; font-size:14px; }

            /* Feedback panel */
            .feedback-panel { font-size:14px; }

            /* Bottom nav: wider, no grid button */
            .bottom-nav { padding:14px 40px 18px; background:#fff; box-shadow:0 -2px 12px rgba(0,0,0,.06); }
            .bn-btn { height:52px; font-size:14px; border-radius:14px; }
            .bn-grid { display:none; }
            .bn-prev { flex:1; }
            .bn-next { flex:2; max-width:320px; }

            /* End modal: centered dialog instead of bottom sheet */
            .modal-overlay { align-items:center; }
            .modal {
                border-radius:20px;
                max-width:460px;
                padding:32px 28px 32px;
                animation:modalIn .22s ease;
                box-shadow:0 20px 60px rgba(0,0,0,.22);
            }
            @keyframes modalIn { from{opacity:0;transform:scale(.95)} to{opacity:1;transform:scale(1)} }
            .modal-handle { display:none; }
            .modal-actions { flex-direction:row; flex-wrap:wrap; gap:10px; }
            .modal-btn { flex:1; min-width:140px; }

            /* Hide mobile bottom sheet (navigator is always visible in sidebar) */
            .sheet-overlay, .nav-sheet { display:none !important; }
        }

        /* ══════════ LARGE DESKTOP (≥ 1200px) ══════════ */
        @media (min-width:1200px) {
            .quiz-sidebar { width:280px; padding:24px 22px; }
            .q-body { padding:36px 56px 36px; }
            .bottom-nav { padding:16px 56px 20px; }
        }
    </style>
</head>
<body>

@php
    $modeLabels = [
        'adaptive'  => ['Adaptive',     'fa-chart-line'],
        'topic'     => ['Topic Focus',  'fa-book-open'],
        'timed'     => ['Timed',        'fa-clock'],
        'challenge' => ['Challenge',    'fa-trophy'],
    ];
    [$modeName, $modeIcon] = $modeLabels[$session->mode] ?? $modeLabels['adaptive'];
    $total = $questions->count();
    $typeLabel = ['mcq' => 'Multiple Choice', 'true_false' => 'True / False'];
@endphp

<div class="quiz-wrap">

    <!-- ══════ HEADER ══════ -->
    <div class="hdr">
        <div class="hdr-row">
            <button class="hdr-back" id="endQuizBtn" title="End Quiz">
                <i class="fas fa-times"></i>
            </button>
            <div class="hdr-info">
                <div class="hdr-subject">{{ $session->subject->name ?? 'Quiz' }}</div>
                <div class="hdr-mode">
                    <i class="fas {{ $modeIcon }}"></i> {{ $modeName }}
                    @if($session->session_type === 'training')
                        &nbsp;<span class="training-badge"><i class="fas fa-brain"></i> Training</span>
                    @endif
                </div>
            </div>
            @isset($timeLimit)
                <div class="hdr-timer" id="timerBadge">
                    <i class="fas fa-clock"></i><span id="timer">--:--</span>
                </div>
            @endisset
        </div>
        <div class="hdr-progress">
            <div class="hdr-progress-fill" id="progFill" style="width:{{ $total > 0 ? round(1/$total*100,1) : 0 }}%"></div>
        </div>
    </div>

    <!-- ══════ CONTENT ROW: SIDEBAR + MAIN ══════ -->
    <div class="quiz-content-row">

        <!-- ── DESKTOP SIDEBAR (hidden on mobile) ── -->
        <aside class="quiz-sidebar">

            <!-- ── LIVE ROOM: AI pace rivals racing you through the quiz ── -->
            <div class="room-block" id="roomD">
                <div class="room-head">
                    <div class="qs-section-title" style="margin:0;">Live Room</div>
                    <span class="room-live"><span class="live-dot"></span> LIVE</span>
                </div>

                <div class="room-hero">
                    <div class="room-hero-top">
                        <div>
                            <div class="room-rank" data-room="rank">#1</div>
                            <div class="room-rank-sub" data-room="ranksub">of 5 in the room</div>
                        </div>
                        <span class="room-streak" data-room="streak"><i class="fas fa-bolt"></i> 0</span>
                    </div>
                    <div class="room-mom">
                        <div class="room-mom-label">
                            <span>Momentum</span><span data-room="momlabel">Warming up</span>
                        </div>
                        <div class="room-mom-track"><span class="room-mom-fill" data-room="momfill"></span></div>
                    </div>
                </div>

                <div data-room="roster"></div>
                <div class="room-feed" data-room="feed"></div>
                <div class="room-note">
                    <i class="fas fa-robot"></i>
                    <span>AI pace partners, tuned to stay ahead of you &mdash; not real students.</span>
                </div>
            </div>

            <div>
                <div class="qs-section-title">Your Progress</div>
                <div class="qs-stats-row">
                    <div class="qs-stat-item ans">
                        <span class="qs-stat-label ans"><i class="fas fa-check-circle"></i> Answered</span>
                        <span class="qs-stat-value ans" id="sStatAnswered">0</span>
                    </div>
                    <div class="qs-stat-item flag">
                        <span class="qs-stat-label flag"><i class="fas fa-flag"></i> For Review</span>
                        <span class="qs-stat-value flag" id="sStatFlagged">0</span>
                    </div>
                    <div class="qs-stat-item left">
                        <span class="qs-stat-label left"><i class="fas fa-minus-circle"></i> Remaining</span>
                        <span class="qs-stat-value left" id="sStatLeft">{{ $total }}</span>
                    </div>
                </div>
            </div>

            <div>
                <div class="qs-section-title">Question Map</div>
                <div class="qs-legend" style="margin-bottom:12px;">
                    <div class="qs-leg-item"><div class="qs-leg-dot" style="background:var(--primary)"></div> Current</div>
                    <div class="qs-leg-item"><div class="qs-leg-dot" style="background:#6ee7b7"></div> Answered</div>
                    <div class="qs-leg-item"><div class="qs-leg-dot" style="background:#fcd34d"></div> For Review</div>
                    <div class="qs-leg-item"><div class="qs-leg-dot" style="background:#f3f4f6;border:1.5px solid #e5e7eb"></div> Unanswered</div>
                </div>
                <div class="qs-nav-grid">
                    @foreach($questions as $i => $q)
                    <button type="button" class="snav-btn{{ $i === 0 ? ' current' : '' }}" id="snav-{{ $i }}"
                            onclick="goTo({{ $i }})">
                        {{ $i + 1 }}<span class="flag-dot"></span>
                    </button>
                    @endforeach
                </div>
            </div>

        </aside>

        <!-- ── MAIN QUIZ AREA ── -->
        <div class="quiz-main">

            <!-- Live-room toasts (overtakes, streaks, milestones) -->
            <div class="room-toasts" id="roomToasts"></div>

            <!-- Counter strip (mobile only; hidden on desktop) -->
            <div class="q-counter-strip">
                <span class="q-counter-num" id="qCounterNum">Question 1 of {{ $total }}</span>
                <div class="q-counter-stats">
                    <button type="button" class="room-pill" id="roomPill" onclick="openRoomSheet()" title="Live Room">
                        <i class="fas fa-tower-broadcast"></i>
                        <span data-room="rank">#1</span>
                        <span class="pill-streak"><i class="fas fa-bolt"></i><span data-room="streaknum">0</span></span>
                    </button>
                    <span class="q-chip ans"><i class="fas fa-check"></i><span id="statAnswered">0</span></span>
                    <span class="q-chip flag"><i class="fas fa-flag"></i><span id="statFlagged">0</span></span>
                    <span class="q-chip left"><i class="fas fa-minus"></i><span id="statLeft">{{ $total }}</span></span>
                </div>
            </div>

            <!-- Scrollable question body -->
            <div class="q-body" id="qBody">
                <!-- Question number shown on desktop since counter strip is hidden -->
                <div id="desktopCounter" style="font-size:12px;font-weight:700;color:var(--gray);text-transform:uppercase;letter-spacing:.8px;margin-bottom:14px;display:none;">
                    Question <span id="dCounterNum">1</span> of {{ $total }}
                </div>

                <form method="POST" action="{{ route('quiz.submit', $session->id) }}" id="quizForm">
                    @csrf
                    @foreach($questions as $i => $question)
                    <div class="q-slide{{ $i === 0 ? ' active' : '' }}" id="slide-{{ $i }}">

                        <div class="flag-banner" id="flagBanner-{{ $i }}">
                            <i class="fas fa-flag"></i> Marked for Review
                        </div>

                        <div class="q-card">
                            <div class="q-card-label">{{ $typeLabel[$question->question_type] ?? 'Question' }}</div>
                            <div class="q-text">{{ $question->question_text }}</div>
                        </div>

                        <div class="choices">
                            @foreach($question->choices as $choice)
                            <label class="choice" @if($choice->is_correct) data-correct="1" @endif>
                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $choice->id }}">
                                <div class="choice-letter">{{ $choice->choice_label }}</div>
                                <span class="choice-text">{{ $choice->choice_text }}</span>
                                <span class="choice-check"><i class="fas fa-check"></i></span>
                            </label>
                            @endforeach
                        </div>

                        <div class="feedback-panel" id="feedback-{{ $i }}"
                             data-explanation="{{ $question->explanation ?? '' }}">
                        </div>

                    </div>
                    @endforeach
                </form>
            </div>

            <!-- Bottom navigation -->
            <div class="bottom-nav">
                <div class="bottom-nav-row">
                    <button type="button" class="bn-btn bn-flag" id="flagBtn" onclick="toggleFlag()" title="Mark for Review">
                        <i class="fas fa-flag"></i>
                    </button>
                    <button type="button" class="bn-btn bn-prev" id="prevBtn" onclick="prev()" disabled>
                        <i class="fas fa-chevron-left"></i> Prev
                    </button>
                    <button type="button" class="bn-btn bn-next" id="nextBtn" onclick="next()">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                    <!-- Grid button: mobile only (hidden on desktop via CSS) -->
                    <button type="button" class="bn-btn bn-grid" onclick="openSheet()" title="Question Map">
                        <i class="fas fa-th"></i>
                        <span class="bn-grid-badge" id="unansweredBadge">{{ $total }}</span>
                    </button>
                </div>
            </div>

        </div><!-- /quiz-main -->

    </div><!-- /quiz-content-row -->

    <!-- Start gate: clicking Start enters fullscreen and begins the quiz -->
    <div class="start-overlay" id="startOverlay">
        <div class="tab-overlay-box">
            <div class="tab-overlay-icon warn" style="background:var(--primary-light);color:var(--primary);">
                <i class="fas fa-expand"></i>
            </div>
            <h2>Ready to Begin?</h2>
            <p>This quiz runs in <strong>fullscreen</strong>. Leaving fullscreen or switching tabs will be flagged as a violation. Click start when you're ready.</p>
            <div class="tab-overlay-actions">
                <button class="btn-resume" onclick="startQuiz()">
                    <i class="fas fa-play"></i> Start Quiz
                </button>
            </div>
        </div>
    </div>

    <!-- Anti-cheat overlay (inside quiz-wrap so blur scope works) -->
    <div class="tab-overlay" id="tabOverlay">
        <div class="tab-overlay-box">
            <div class="tab-overlay-icon warn" id="overlayIcon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h2 id="overlayTitle">Quiz Frozen</h2>
            <p id="overlayMsg">You left the quiz. It's paused and locked until you return.</p>
            <div class="violation-bar">
                <div class="v-dot" id="vdot0"></div>
                <div class="v-dot" id="vdot1"></div>
                <div class="v-dot" id="vdot2"></div>
            </div>
            <div class="violation-label warn" id="violationLabel">
                Violation <span id="vCount">1</span> of 3
            </div>
            <div class="tab-overlay-actions">
                <button class="btn-resume" id="resumeBtn" onclick="resumeQuiz()">
                    <i class="fas fa-play"></i> Resume Quiz
                </button>
                <button class="btn-leave" onclick="confirmLeave()">
                    <i class="fas fa-sign-out-alt"></i> Leave &amp; End Quiz
                </button>
            </div>
        </div>
    </div>

</div><!-- /quiz-wrap -->

<!-- ══════ BOTTOM SHEET NAVIGATOR (mobile only; hidden on desktop) ══════ -->
<div class="sheet-overlay" id="sheetOverlay" onclick="closeSheet()"></div>
<div class="nav-sheet" id="navSheet">
    <div class="sheet-handle"></div>
    <div class="sheet-title">
        Question Map
        <button class="sheet-close" onclick="closeSheet()"><i class="fas fa-times"></i></button>
    </div>
    <div class="sheet-legend">
        <div class="leg-item"><div class="leg-dot" style="background:var(--primary)"></div> Current</div>
        <div class="leg-item"><div class="leg-dot" style="background:#6ee7b7"></div> Answered</div>
        <div class="leg-item"><div class="leg-dot" style="background:#fcd34d"></div> For Review</div>
        <div class="leg-item"><div class="leg-dot" style="background:#f3f4f6;border:1.5px solid #e5e7eb"></div> Unanswered</div>
    </div>
    <div class="sheet-grid" id="navGrid">
        @foreach($questions as $i => $q)
        <button type="button" class="nav-btn{{ $i === 0 ? ' current' : '' }}" id="nav-{{ $i }}"
                onclick="goTo({{ $i }}); closeSheet();">
            {{ $i + 1 }}<span class="flag-dot"></span>
        </button>
        @endforeach
    </div>
</div>

<!-- ══════ LIVE ROOM SHEET (mobile only; the sidebar shows it on desktop) ══════ -->
<div class="sheet-overlay" id="roomOverlay" onclick="closeRoomSheet()"></div>
<div class="nav-sheet" id="roomSheet">
    <div class="sheet-handle"></div>
    <div class="sheet-title">
        <span><i class="fas fa-tower-broadcast" style="color:var(--primary);margin-right:7px;"></i>Live Room</span>
        <button class="sheet-close" onclick="closeRoomSheet()"><i class="fas fa-times"></i></button>
    </div>
    <div class="room-sheet-body">
        <div class="room-block on">
            <div class="room-hero">
                <div class="room-hero-top">
                    <div>
                        <div class="room-rank" data-room="rank">#1</div>
                        <div class="room-rank-sub" data-room="ranksub">of 5 in the room</div>
                    </div>
                    <span class="room-streak" data-room="streak"><i class="fas fa-bolt"></i> 0</span>
                </div>
                <div class="room-mom">
                    <div class="room-mom-label">
                        <span>Momentum</span><span data-room="momlabel">Warming up</span>
                    </div>
                    <div class="room-mom-track"><span class="room-mom-fill" data-room="momfill"></span></div>
                </div>
            </div>
            <div data-room="roster"></div>
            <div class="room-feed" data-room="feed"></div>
            <div class="room-note">
                <i class="fas fa-robot"></i>
                <span>AI pace partners, tuned to stay ahead of you &mdash; not real students.</span>
            </div>
        </div>
    </div>
</div>

<!-- ══════ END QUIZ MODAL ══════ -->
<div class="modal-overlay" id="endModal" onclick="if(event.target===this)closeModal()">
    <div class="modal">
        <div class="modal-handle"></div>
        <h3><i class="fas fa-flag-checkered" style="color:var(--primary);margin-right:8px;"></i>Submit Quiz?</h3>
        <p>Review your progress before submitting. This cannot be undone.</p>
        <div class="modal-chips">
            <span class="q-chip ans" style="font-size:12px;padding:5px 12px;"><i class="fas fa-check-circle"></i> <span id="mAnswered">0</span> Answered</span>
            <span class="q-chip flag" style="font-size:12px;padding:5px 12px;"><i class="fas fa-flag"></i> <span id="mFlagged">0</span> For Review</span>
            <span class="q-chip left" style="font-size:12px;padding:5px 12px;"><i class="fas fa-minus-circle"></i> <span id="mLeft">0</span> Remaining</span>
            <span class="q-chip flag" id="mRoomChip" style="display:none;font-size:12px;padding:5px 12px;"><i class="fas fa-medal"></i> Room place <span id="mRoomPlace">#1</span></span>
        </div>
        <div class="modal-actions">
            <button class="modal-btn primary" onclick="confirmSubmitQuiz()"><i class="fas fa-check"></i> Submit Now</button>
            <button class="modal-btn ghost"   onclick="closeModal()">Go Back</button>
        </div>
    </div>
</div>

<script>
const TOTAL        = {{ $total }};
const SESSION_TYPE = '{{ $session->session_type }}';
let current   = 0;
const flagged  = new Set();
const answered = new Set();

/* ══════════════════════════════════════════════════════════════════
   LIVE ROOM — AI pace rivals
   Four simulated candidates work through the same quiz alongside the
   student so practice carries the pulse of a real exam hall: someone
   is always a question ahead, and passing them is a moment worth
   chasing. The roster is seeded from the session id, so one sitting
   always draws the same rivals while a retake draws a fresh set.

   This layer is purely cosmetic — it never touches grading, points,
   the spaced-repetition schedule, or what gets submitted.
   ══════════════════════════════════════════════════════════════════ */
const ROOM_ON   = localStorage.getItem('quizLiveRoom') !== '0';
const QUIZ_MODE = '{{ $session->mode }}';
const ROOM_SEED = {{ $session->id }};

const Room = {
    rivals: [], feed: [], streak: 0, bestStreak: 0,
    lastAnswerAt: 0, place: 1, closed: false,
    t: 0, youDoneAt: null,   // room clock, used to break ties at the finish line
    paceSamples: [], youCorrect: 0,
    lastPlace: null, lastRankToastAt: -99,

    /* Rival personalities. Every one of them is a strong candidate — this is
       a topnotcher's room, not a random crowd — but the seconds-per-question
       and accuracy still give each a recognisable rhythm. */
    POOL: [
        { name:'Aria', tag:'Speedster',  color:'#ef4444', spq:13, acc:0.81 },
        { name:'Dex',  tag:'Risk-taker', color:'#f59e0b', spq:15, acc:0.78 },
        { name:'Mira', tag:'Methodical', color:'#3b82f6', spq:24, acc:0.93 },
        { name:'Kip',  tag:'Steady',     color:'#10b981', spq:19, acc:0.87 },
        { name:'Nova', tag:'Clutch',     color:'#8b5cf6', spq:17, acc:0.90 },
        { name:'Rio',  tag:'Grinder',    color:'#0ea5e9', spq:21, acc:0.85 },
        { name:'Sage', tag:'Precise',    color:'#14b8a6', spq:26, acc:0.95 },
        { name:'Zed',  tag:'Sprinter',   color:'#e11d48', spq:11, acc:0.76 },
    ],

    /* How much faster than the student each rival aims to be, strongest first.
       Training is ranked on accuracy, so every rival can out-pace you there and
       the room is still winnable by answering well. Testing is ranked on pace
       alone, so the last rival sits a hair behind you — without someone to
       actually catch, that panel would be pure discouragement. */
    EDGES:         [0.72, 0.82, 0.90, 0.98],
    EDGES_TESTING: [0.76, 0.86, 0.94, 1.05],

    /* Small deterministic PRNG (mulberry32) — same session, same roster. */
    rng(seed) {
        let a = seed >>> 0;
        return function () {
            a = (a + 0x6D2B79F5) | 0;
            let t = Math.imul(a ^ (a >>> 15), 1 | a);
            t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
            return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
        };
    },

    init() {
        // A 1–2 question quiz is over before a race can mean anything.
        if (!ROOM_ON || TOTAL < 3) return;

        const rand = this.rng(Math.imul(ROOM_SEED || 1, 2654435761));
        // Harder questions slow everyone down; Timed mode speeds them up.
        const pace = QUIZ_MODE === 'challenge' ? 1.4 : (QUIZ_MODE === 'timed' ? 0.8 : 1);

        this.rivals = this.POOL
            .map(p => ({ p: p, k: rand() }))
            .sort((a, b) => a.k - b.k)
            .slice(0, 4)
            .map(function (entry) {
                const p = entry.p;
                return {
                    name: p.name, tag: p.tag, color: p.color, acc: p.acc,
                    spq: p.spq * pace * (0.85 + rand() * 0.3),
                    progress: 0, correct: 0, elapsed: 0, next: 0, ahead: false, done: false
                };
            });

        // The naturally quickest rival gets the sharpest edge over the student,
        // so a personality's character and its pressure point the same way.
        const edges = SESSION_TYPE === 'training' ? this.EDGES : this.EDGES_TESTING;
        this.rivals
            .slice()
            .sort((a, b) => a.spq - b.spq)
            .forEach((r, i) => { r.edge = edges[i]; });

        this.rivals.forEach(r => { r.next = r.spq * (0.5 + rand() * 0.4); });

        document.getElementById('roomD').classList.add('on');
        document.getElementById('roomPill').classList.add('on');
        this.say('Room open — 4 top-scoring candidates seated with you.', 'fa-door-open');
        this.render();
        setInterval(() => this.tick(), 1000);
    },

    /** The student's recent seconds-per-question (last 3 answers). */
    myPace() {
        if (!this.paceSamples.length) return null;
        const recent = this.paceSamples.slice(-3);
        const mean = recent.reduce((a, b) => a + b, 0) / recent.length;
        return Math.max(4, Math.min(mean, 180));
    },

    /**
     * How long this rival takes on its next question. Rivals chase the
     * student's own pace with an edge, so speeding up drags the whole room
     * faster and you never simply out-run them — but each stays within reach
     * of its own character, and nobody goes inhumanly fast.
     */
    target(r) {
        const mine = this.myPace();
        if (mine === null) return r.spq;                 // no read yet
        return Math.max(5, Math.min(mine * r.edge, r.spq * 1.8));
    },

    /**
     * Rival accuracy. In Training the student's own accuracy is on screen, so
     * rivals hold a margin above it — they are the standard to chase. Capped
     * below 100% so a near-perfect run still wins the room.
     */
    effAcc(r) {
        if (SESSION_TYPE !== 'training' || !answered.size) return r.acc;
        return Math.min(0.97, Math.max(r.acc, this.youCorrect / answered.size + 0.05));
    },

    /** One second of rival progress. Pauses whenever the quiz isn't live. */
    tick() {
        if (!this.rivals.length || this.closed) return;
        if (!started || frozen || timeUp || submitting) return;

        this.t++;
        const milestone = Math.max(3, Math.round(TOTAL / 4));
        let moved = false;

        this.rivals.forEach(r => {
            if (r.done) return;
            r.elapsed++;
            if (r.elapsed < r.next) return;

            r.elapsed = 0;
            r.next = this.target(r) * (0.8 + Math.random() * 0.4);   // human-ish jitter
            r.progress++;
            if (Math.random() < this.effAcc(r)) r.correct++;
            moved = true;

            if (r.progress >= TOTAL) {
                r.progress = TOTAL;
                r.done = true;
                r.doneAt = this.t;
                this.say(r.name + ' submitted their quiz.', 'fa-flag-checkered');
            } else if (r.progress % milestone === 0) {
                this.say(r.name + ' reached Q' + r.progress + '.', 'fa-forward');
            }
        });

        if (moved) this.checkOvertakes();
        this.render();
    },

    /**
     * Note a rival pulling ahead. This lands in the quiet feed only, and only
     * the first time each rival does it: against a room that out-paces you,
     * every crossing would fire a dozen times a quiz and read as nagging.
     * The toasts come from announceRank(), which fires on real standing moves.
     */
    checkOvertakes() {
        const you = answered.size;
        this.rivals.forEach(r => {
            const ahead = r.progress > you;
            if (ahead && !r.ahead && you > 0 && !r.passNoted) {
                r.passNoted = true;
                this.say(r.name + ' moved ahead of you.', 'fa-angles-up');
            }
            r.ahead = ahead;
        });
    },

    /** Toast when the student's actual placing changes, at most every ~12s. */
    announceRank(place) {
        if (this.lastPlace === null) { this.lastPlace = place; return; }
        if (place === this.lastPlace) return;

        const improved = place < this.lastPlace;
        const quiet = (this.t - this.lastRankToastAt) < 12;
        this.lastPlace = place;
        if (quiet) return;

        this.lastRankToastAt = this.t;
        if (improved) {
            this.toast('Climbed to #' + place + ' in the room!', 'good', 'fa-angles-up');
        } else {
            this.toast('Slipped to #' + place + ' — the room is pulling ahead', 'warn', 'fa-angles-down');
        }
    },

    /** Called whenever the student answers a question. */
    onAnswer(isCorrect) {
        if (!this.rivals.length || this.closed) return;

        const now = Date.now();
        const gap = this.lastAnswerAt ? (now - this.lastAnswerAt) / 1000 : null;
        this.lastAnswerAt = now;
        if (gap !== null) this.paceSamples.push(gap);   // feeds the rivals' pacing
        if (isCorrect) this.youCorrect++;
        if (answered.size >= TOTAL && this.youDoneAt === null) this.youDoneAt = this.t;

        // Training reveals correctness, so the streak can reward accuracy.
        // Testing must never leak it, so there the streak rewards pace only.
        const keepsStreak = SESSION_TYPE === 'training' ? isCorrect : true;

        if (keepsStreak) {
            this.streak++;
            this.bestStreak = Math.max(this.bestStreak, this.streak);
            if (this.streak === 3) {
                this.toast('3 in a row — you\'re heating up!', 'fire', 'fa-fire');
            } else if (this.streak === 5 || (this.streak > 5 && this.streak % 5 === 0)) {
                this.toast(this.streak + ' straight — unstoppable!', 'fire', 'fa-fire-flame-curved');
                this.say('You hit a ' + this.streak + '-answer streak.', 'fa-fire');
            }
        } else {
            if (this.streak >= 3) this.toast('Streak broken at ' + this.streak, 'warn', 'fa-heart-crack');
            this.streak = 0;
        }

        if (gap !== null && gap < 20) {
            this.toast('Quick answer — ' + Math.round(gap) + 's', 'good', 'fa-bolt');
        }

        // Passing a rival is the payoff moment the whole panel exists for.
        const you = answered.size;
        this.rivals.forEach(r => {
            if (r.ahead && you > r.progress) {
                r.ahead = false;
                this.toast('You passed ' + r.name + '!', 'good', 'fa-angles-up');
                this.say('You overtook ' + r.name + '.', 'fa-angles-up');
            }
        });

        this.render();
    },

    /**
     * Current placing. Training grades the room on correct answers — racing
     * ahead while getting them wrong should not out-rank a careful rival.
     * Testing has no visible correctness, so there it is pure progress.
     */
    standing() {
        const you = answered.size;
        const youDone = this.youDoneAt === null ? Infinity : this.youDoneAt;
        const byScore = SESSION_TYPE === 'training';
        const mine = byScore ? this.youCorrect : you;

        const ahead = this.rivals.filter(function (r) {
            const theirs = byScore ? r.correct : r.progress;
            if (theirs !== mine) return theirs > mine;
            // Training is graded on accuracy, and the rivals are always the
            // faster ones — so a tie on score goes to the student. That keeps
            // topping this room hard but genuinely reachable: answer
            // everything right and it is yours.
            if (byScore) return false;
            // Testing: level on progress, so further along / finished sooner.
            return r.done && you >= TOTAL && r.doneAt < youDone;
        }).length;

        return {
            you: you, correct: this.youCorrect, byScore: byScore,
            place: ahead + 1, total: this.rivals.length + 1
        };
    },

    /* ── rendering (the same markup feeds the sidebar and the mobile sheet) ── */
    set(key, html) {
        document.querySelectorAll('[data-room="' + key + '"]').forEach(n => { n.innerHTML = html; });
    },

    render() {
        if (!this.rivals.length) return;

        const s = this.standing();
        this.place = s.place;
        this.announceRank(s.place);

        this.set('rank', '#' + s.place);
        this.set('ranksub', 'of ' + s.total + ' in the room');
        this.set('streak', '<i class="fas fa-bolt"></i> ' + this.streak);
        this.set('streaknum', this.streak);
        document.querySelectorAll('[data-room="streak"]').forEach(n => {
            n.classList.toggle('hot', this.streak >= 3);
        });

        const labels = ['Warming up', 'Rolling', 'Rolling', 'On fire', 'On fire', 'Unstoppable'];
        this.set('momlabel', labels[Math.min(this.streak, 5)]);
        document.querySelectorAll('[data-room="momfill"]').forEach(n => {
            n.style.width = (Math.min(this.streak, 6) / 6 * 100) + '%';
        });

        // Ordered exactly like the rank number: the ranking metric first, then
        // progress, then who crossed the line earlier (UNFINISHED keeps ties
        // neutral while the room is still running).
        const UNFINISHED = 1e12;
        const rows = this.rivals.map(r => ({
            name: r.name, tag: r.tag, color: r.color,
            progress: r.progress, correct: r.correct, done: r.done,
            at: r.done ? r.doneAt : UNFINISHED, you: false
        }));
        rows.push({
            name: 'You', tag: 'your pace', color: '#7B1D1D',
            progress: s.you, correct: this.youCorrect, done: false,
            at: this.youDoneAt === null ? UNFINISHED : this.youDoneAt, you: true
        });

        const key = s.byScore ? 'correct' : 'progress';
        rows.sort((a, b) => (b[key] - a[key]) || (b.progress - a.progress)
                         || (a.at - b.at) || (a.you ? -1 : 1));

        this.set('roster', rows.map(function (r) {
            const pct = TOTAL > 0 ? Math.round(r.progress / TOTAL * 100) : 0;
            // Training shows accuracy too, so "better than you" is visible in
            // both dimensions rather than just who is further down the page.
            const stat = (s.byScore && r.progress > 0)
                ? r.progress + '/' + TOTAL + ' · ' + Math.round(r.correct / r.progress * 100) + '%'
                : r.progress + '/' + TOTAL;
            return '<div class="room-row' + (r.you ? ' you' : '') + (r.done ? ' done' : '') + '">'
                 +   '<div class="room-av" style="background:' + r.color + '">' + r.name.charAt(0) + '</div>'
                 +   '<div class="room-row-body">'
                 +     '<div class="room-row-top">'
                 +       '<span class="room-name">' + r.name + ' <span class="room-tag">· ' + (r.done ? 'finished' : r.tag) + '</span></span>'
                 +       '<span class="room-q">' + stat + '</span>'
                 +     '</div>'
                 +     '<div class="room-bar"><span style="width:' + pct + '%;background:' + r.color + '"></span></div>'
                 +   '</div>'
                 + '</div>';
        }).join(''));

        this.renderFeed();
    },

    renderFeed() {
        this.set('feed', this.feed.map(function (f) {
            return '<div class="room-feed-item"><i class="fas ' + f.icon + '"></i><span>' + f.text + '</span></div>';
        }).join(''));
    },

    say(text, icon) {
        this.feed.unshift({ text: text, icon: icon || 'fa-circle-info' });
        this.feed = this.feed.slice(0, 4);
        this.renderFeed();
    },

    toast(text, kind, icon) {
        const host = document.getElementById('roomToasts');
        if (!host) return;
        while (host.children.length >= 3) host.removeChild(host.firstChild);

        const node = document.createElement('div');
        node.className = 'room-toast ' + (kind || '');
        node.innerHTML = '<i class="fas ' + (icon || 'fa-bolt') + '"></i><span>' + text + '</span>';
        host.appendChild(node);

        setTimeout(function () {
            node.classList.add('out');
            setTimeout(function () { node.remove(); }, 320);
        }, 2400);
    },

    /** Freeze the race and hand the final placing to the results page. */
    finish() {
        if (!this.rivals.length || this.closed) return;
        this.closed = true;

        const s = this.standing();
        try {
            sessionStorage.setItem('cpaceRoom' + ROOM_SEED, JSON.stringify({
                place: s.place,
                total: s.total,
                streak: this.bestStreak,
                answered: s.you,
                correct: s.byScore ? this.youCorrect : null,
                byScore: s.byScore,
                questions: TOTAL,
                youAt: this.youDoneAt,
                rivals: this.rivals.map(r => ({
                    name: r.name, color: r.color, progress: r.progress,
                    correct: s.byScore ? r.correct : null,
                    at: r.done ? r.doneAt : null
                }))
            }));
        } catch (e) { /* private mode — the room summary is simply skipped */ }
    }
};

function openRoomSheet() {
    document.getElementById('roomOverlay').classList.add('open');
    document.getElementById('roomSheet').classList.add('open');
}
function closeRoomSheet() {
    document.getElementById('roomOverlay').classList.remove('open');
    document.getElementById('roomSheet').classList.remove('open');
}

/* Detect desktop to show the inline counter */
const isDesktop = () => window.innerWidth >= 900;

/* ── Navigate ── */
function goTo(idx) {
    if (idx < 0 || idx >= TOTAL) return;
    document.getElementById('slide-'  + current)?.classList.remove('active');
    current = idx;
    document.getElementById('slide-'  + current)?.classList.add('active');
    document.getElementById('qBody').scrollTop = 0;

    document.getElementById('progFill').style.width = ((current + 1) / TOTAL * 100) + '%';
    document.getElementById('qCounterNum').textContent = 'Question ' + (current + 1) + ' of ' + TOTAL;

    // Desktop inline counter
    const dc = document.getElementById('dCounterNum');
    if (dc) dc.textContent = current + 1;
    const dw = document.getElementById('desktopCounter');
    if (dw) dw.style.display = isDesktop() ? '' : 'none';

    document.getElementById('prevBtn').disabled = current === 0;
    const nb = document.getElementById('nextBtn');
    nb.innerHTML = current === TOTAL - 1
        ? '<i class="fas fa-flag-checkered"></i> Finish'
        : 'Next <i class="fas fa-chevron-right"></i>';

    const fb = document.getElementById('flagBtn');
    fb.classList.toggle('active', flagged.has(current));
    document.getElementById('flagBanner-' + current)?.classList.toggle('visible', flagged.has(current));

    refreshNav();
}

function prev() { goTo(current - 1); }
function next() { current === TOTAL - 1 ? openModal() : goTo(current + 1); }

/* ── Flag ── */
function toggleFlag() {
    flagged.has(current) ? flagged.delete(current) : flagged.add(current);
    goTo(current);
    refreshStats();
}

/* ── Answer listener ── */
document.getElementById('quizForm').addEventListener('change', function(e) {
    if (e.target.type !== 'radio') return;
    const slide = document.getElementById('slide-' + current);
    slide.querySelectorAll('.choice').forEach(c => c.classList.remove('selected'));
    const chosen = e.target.closest('.choice');
    chosen.classList.add('selected');
    // Changing an existing answer must not feed the Live Room a second time.
    const firstAnswer = !answered.has(current);
    answered.add(current);

    // Once a flagged question is answered correctly there's no need to keep it
    // marked for review, so clear its flag automatically. In testing mode the
    // correctness isn't revealed, so answering at all clears the flag.
    const answeredCorrectly = chosen.dataset.correct === '1';
    if (flagged.has(current) && (SESSION_TYPE !== 'training' || answeredCorrectly)) {
        flagged.delete(current);
        document.getElementById('flagBtn').classList.remove('active');
        document.getElementById('flagBanner-' + current)?.classList.remove('visible');
    }

    refreshNav();
    refreshStats();
    if (firstAnswer) Room.onAnswer(chosen.dataset.correct === '1');

    if (SESSION_TYPE === 'training' && !slide.classList.contains('revealed')) {
        slide.classList.add('revealed');
        const isCorrect = chosen.dataset.correct === '1';
        slide.querySelectorAll('.choice').forEach(function(c) {
            if (c.dataset.correct === '1') {
                c.classList.add('reveal-correct');
            } else if (c === chosen && !isCorrect) {
                c.classList.add('reveal-wrong');
            }
        });
        const panel = document.getElementById('feedback-' + current);
        if (panel) {
            const explanation = panel.dataset.explanation;
            panel.className = 'feedback-panel visible ' + (isCorrect ? 'correct' : 'wrong');
            panel.innerHTML = (isCorrect
                ? '<i class="fas fa-check-circle"></i> <strong>Correct!</strong>'
                : '<i class="fas fa-times-circle"></i> <strong>Incorrect.</strong>')
                + (explanation
                    ? '<div class="explain-text"><i class="fas fa-lightbulb"></i> ' + explanation + '</div>'
                    : '');
        }
    }
});

/* ── Navigator classes ── */
function refreshNav() {
    for (let i = 0; i < TOTAL; i++) {
        // Mobile bottom sheet buttons
        const btn = document.getElementById('nav-' + i);
        if (btn) {
            btn.className = 'nav-btn';
            if (i === current)   btn.classList.add('current');
            if (answered.has(i)) btn.classList.add('answered');
            if (flagged.has(i))  btn.classList.add('flagged');
        }
        // Desktop sidebar buttons
        const sbtn = document.getElementById('snav-' + i);
        if (sbtn) {
            sbtn.className = 'snav-btn';
            if (i === current)   sbtn.classList.add('current');
            if (answered.has(i)) sbtn.classList.add('answered');
            if (flagged.has(i))  sbtn.classList.add('flagged');
        }
    }
}

/* ── Stats ── */
function refreshStats() {
    const a = answered.size, f = flagged.size, l = TOTAL - a;
    // Mobile counter strip
    document.getElementById('statAnswered').textContent = a;
    document.getElementById('statFlagged').textContent  = f;
    document.getElementById('statLeft').textContent     = l;
    // Modal
    document.getElementById('mAnswered').textContent = a;
    document.getElementById('mFlagged').textContent  = f;
    document.getElementById('mLeft').textContent     = l;
    // Desktop sidebar
    const sa = document.getElementById('sStatAnswered');
    const sf = document.getElementById('sStatFlagged');
    const sl = document.getElementById('sStatLeft');
    if (sa) sa.textContent = a;
    if (sf) sf.textContent = f;
    if (sl) sl.textContent = l;
    // Mobile grid badge
    const badge = document.getElementById('unansweredBadge');
    if (badge) { badge.textContent = l; badge.style.display = l > 0 ? '' : 'none'; }
}

/* ── Bottom sheet (mobile) ── */
function openSheet()  {
    document.getElementById('sheetOverlay').classList.add('open');
    document.getElementById('navSheet').classList.add('open');
}
function closeSheet() {
    document.getElementById('sheetOverlay').classList.remove('open');
    document.getElementById('navSheet').classList.remove('open');
}

/* ── End quiz modal ── */
function openModal()  {
    refreshStats();
    // Show where the student stands in the room before they commit.
    if (Room.rivals.length) {
        const s = Room.standing();
        document.getElementById('mRoomPlace').textContent = '#' + s.place + ' of ' + s.total;
        document.getElementById('mRoomChip').style.display = '';
    }
    document.getElementById('endModal').classList.add('open');
}
function closeModal() { document.getElementById('endModal').classList.remove('open'); }
document.getElementById('endQuizBtn').addEventListener('click', openModal);

let timeUp = false;
/* Set true on every intentional submit so the beforeunload guard below stands
   down — otherwise the "Leave site?" prompt blocks timed/anti-cheat auto-submit. */
let submitting = false;
function submitQuiz() {
    submitting = true;
    Room.finish();
    CPACE.loading('Submitting your quiz...', 'Scoring your answers, hang tight.');
    document.getElementById('quizForm').submit();
}

/* Guarded submit used by the "Submit Now" button: unanswered questions get one
   last confirmation before the attempt is locked in. */
function confirmSubmitQuiz() {
    const left = TOTAL - answered.size;
    if (left <= 0) { submitQuiz(); return; }

    closeModal();
    CPACE.confirm({
        title: 'Submit with blanks?',
        text: left + ' question' + (left > 1 ? 's are' : ' is') + ' still unanswered and will be marked incorrect. This cannot be undone.',
        confirmText: 'Submit anyway',
        cancelText: 'Keep answering',
        danger: true,
    }).then(function (ok) {
        if (ok) submitQuiz();
        else openModal();
    });
}

/* ══════════ ANTI-CHEAT ══════════ */
const MAX_VIOLATIONS = 3;
let violations  = 0;
let frozen      = false;
let timerRunning = true;

let violationAudioCtx = null;
function playViolationSound() {
    try {
        violationAudioCtx = violationAudioCtx || new (window.AudioContext || window.webkitAudioContext)();
        const ctx = violationAudioCtx;
        if (ctx.state === 'suspended') ctx.resume();
        [0, 0.16].forEach(function (delay) {
            const osc  = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'square';
            osc.frequency.value = 880;
            gain.gain.setValueAtTime(0.0001, ctx.currentTime + delay);
            gain.gain.exponentialRampToValueAtTime(0.25, ctx.currentTime + delay + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + delay + 0.14);
            osc.connect(gain).connect(ctx.destination);
            osc.start(ctx.currentTime + delay);
            osc.stop(ctx.currentTime + delay + 0.15);
        });
    } catch (e) {}
}
function playTerminationSound() {
    try {
        violationAudioCtx = violationAudioCtx || new (window.AudioContext || window.webkitAudioContext)();
        const ctx = violationAudioCtx;
        if (ctx.state === 'suspended') ctx.resume();
        [660, 495, 330].forEach(function (freq, i) {
            const delay = i * 0.18;
            const osc  = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sawtooth';
            osc.frequency.value = freq;
            gain.gain.setValueAtTime(0.0001, ctx.currentTime + delay);
            gain.gain.exponentialRampToValueAtTime(0.3, ctx.currentTime + delay + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + delay + 0.22);
            osc.connect(gain).connect(ctx.destination);
            osc.start(ctx.currentTime + delay);
            osc.stop(ctx.currentTime + delay + 0.23);
        });
    } catch (e) {}
}

function freeze() {
    if (frozen) return;
    frozen = true; timerRunning = false;
    document.body.classList.add('quiz-frozen');
    playViolationSound();
}
function unfreeze() {
    frozen = false; timerRunning = true;
    document.body.classList.remove('quiz-frozen');
    document.getElementById('tabOverlay').classList.remove('show');
}
function resumeQuiz() { enterFullscreen(); unfreeze(); }

/* ══════════ FULLSCREEN LOCKDOWN ══════════ */
/* The quiz locks to the whole screen. Entering fullscreen needs a user
   gesture, so we kick it off on the first interaction and re-enter on resume.
   Leaving fullscreen (e.g. pressing Esc) is treated as a violation, exactly
   like switching tabs. */
function isFullscreen() {
    return !!(document.fullscreenElement || document.webkitFullscreenElement);
}
function enterFullscreen() {
    if (isFullscreen()) return;
    const el = document.documentElement;
    const req = el.requestFullscreen || el.webkitRequestFullscreen;
    if (req) { try { Promise.resolve(req.call(el)).catch(function(){}); } catch (e) {} }
}
function exitFullscreen() {
    if (!isFullscreen()) return;
    const ex = document.exitFullscreen || document.webkitExitFullscreen;
    if (ex) { try { Promise.resolve(ex.call(document)).catch(function(){}); } catch (e) {} }
}

/* The quiz sits behind a "Start Quiz" gate so the student's first action
   enters fullscreen — the quiz is fullscreen from the start, not only after
   they interact. Anti-cheat watchers stay off until the quiz has started. */
let started = false;
function startQuiz() {
    if (started) return;
    started = true;
    enterFullscreen();
    document.getElementById('startOverlay').classList.add('hidden');
    if (Room.rivals.length) {
        Room.lastAnswerAt = Date.now();
        Room.say('The room started together. They set a fast pace — go!', 'fa-flag-checkered');
        Room.toast('Race started — this room is quick. Keep up!', 'fire', 'fa-tower-broadcast');
    }
}

/* Treat exiting fullscreen as leaving the quiz. */
function handleFullscreenChange() {
    if (started && !isFullscreen() && !submitting && !timeUp && !frozen) {
        freeze();
        showViolationOverlay();
    }
}
document.addEventListener('fullscreenchange', handleFullscreenChange);
document.addEventListener('webkitfullscreenchange', handleFullscreenChange);

function showViolationOverlay() {
    violations++;
    for (let i = 0; i < 3; i++) {
        const d = document.getElementById('vdot' + i);
        if (!d) continue;
        d.classList.remove('used','final');
        if (i < violations) d.classList.add(i === MAX_VIOLATIONS - 1 ? 'final' : 'used');
    }
    document.getElementById('vCount').textContent = violations;
    const icon  = document.getElementById('overlayIcon');
    const title = document.getElementById('overlayTitle');
    const msg   = document.getElementById('overlayMsg');
    const label = document.getElementById('violationLabel');

    if (violations >= MAX_VIOLATIONS) {
        playTerminationSound();
        icon.className  = 'tab-overlay-icon crit';
        icon.innerHTML  = '<i class="fas fa-ban"></i>';
        title.textContent = 'Quiz Terminated';
        msg.innerHTML   = 'You left the quiz <strong>' + violations + ' times</strong>. Auto-submitting now…';
        label.className = 'violation-label critical';
        label.textContent = 'Maximum violations reached';
        document.getElementById('resumeBtn').style.display = 'none';
        document.getElementById('tabOverlay').classList.add('show');
        setTimeout(submitQuiz, 3000);
        return;
    }
    icon.className  = 'tab-overlay-icon warn';
    icon.innerHTML  = '<i class="fas fa-shield-alt"></i>';
    title.textContent = 'Quiz Frozen';
    const rem = MAX_VIOLATIONS - violations;
    msg.innerHTML = 'You left the quiz tab.<br><span style="color:var(--accent);font-weight:600;">⚠ ' + rem + ' more violation' + (rem > 1 ? 's' : '') + ' will auto-submit.</span>';
    label.className = 'violation-label warn';
    label.innerHTML = 'Violation <span id="vCount">' + violations + '</span> of ' + MAX_VIOLATIONS;
    document.getElementById('resumeBtn').style.display = '';
    document.getElementById('tabOverlay').classList.add('show');
}

function confirmLeave() {
    CPACE.confirm({
        title: 'Leave the quiz?',
        text: 'Your attempt ends here and the answers you have so far will be submitted for scoring.',
        confirmText: 'Yes, submit and leave',
        cancelText: 'Stay in the quiz',
        danger: true,
    }).then(function (ok) { if (ok) submitQuiz(); });
}

document.addEventListener('visibilitychange', function() {
    if (!started) return;
    if (document.hidden) { freeze(); }
    else { showViolationOverlay(); }
});
let blurTimer = null;
window.addEventListener('blur', function() {
    if (!started) return;
    blurTimer = setTimeout(function() { if (!document.hidden) { freeze(); showViolationOverlay(); } }, 300);
});
window.addEventListener('focus', function() { clearTimeout(blurTimer); });
document.addEventListener('contextmenu', e => e.preventDefault());
document.addEventListener('keydown', function(e) {
    const ctrl = e.ctrlKey || e.metaKey;
    if ((ctrl && ['t','w','n'].includes(e.key)) || e.key === 'F12') e.preventDefault();
});
window.addEventListener('beforeunload', function(e) {
    if (submitting) return;            // intentional submit — don't nag or block it
    e.preventDefault(); e.returnValue = 'Your quiz is still in progress.'; return e.returnValue;
});

/* ══════════ TIMER ══════════ */
@isset($timeLimit)
(function() {
    let remaining = {{ max(0, $timeLimit - (int) $session->started_at->diffInSeconds(now())) }};
    const badge = document.getElementById('timerBadge');
    const label = document.getElementById('timer');
    function render() {
        const m = Math.floor(remaining / 60), s = remaining % 60;
        label.textContent = m + ':' + String(s).padStart(2,'0');
        badge.classList.toggle('warning', remaining <= 60 && remaining > 20);
        badge.classList.toggle('danger',  remaining <= 20);
    }
    function tick() {
        if (!timerRunning) { setTimeout(tick, 500); return; }
        if (remaining <= 0) {
            timeUp = true; label.textContent = '0:00';
            submitQuiz(); return;
        }
        render(); remaining--; setTimeout(tick, 1000);
    }
    tick();
})();
@endisset

/* Show desktop inline counter on load if desktop */
(function() {
    const dw = document.getElementById('desktopCounter');
    if (dw && isDesktop()) dw.style.display = '';
    window.addEventListener('resize', function() {
        if (dw) dw.style.display = isDesktop() ? '' : 'none';
    });
})();

refreshStats();
Room.init();
</script>

    @include('partials.alerts')
</body>
</html>
