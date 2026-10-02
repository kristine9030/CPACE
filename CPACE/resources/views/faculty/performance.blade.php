<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Performance - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; --orange:#f59e0b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

        /* MAIN */
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }

        /* TOPBAR */
        .topbar { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:22px; position:relative; z-index:100; }
        .topbar-left { display:flex; align-items:center; gap:12px; }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .page-title { font-size:26px; font-weight:700; color:#14283E; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:white; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }

        /* STATS — same KPI card vibe as the faculty dashboard */
        .stats-row { display:grid; grid-template-columns:repeat(4,minmax(0, 1fr)); gap:14px; margin-bottom:14px; }
        .stat-card {
            background:white; border-radius:14px; padding:20px 22px;
            display:flex; flex-direction:column; height:100%;
            box-shadow:0 1px 2px rgba(16,24,40,.04), 0 4px 14px rgba(16,24,40,.06);
            transition:transform .18s ease, box-shadow .18s ease;
        }
        .stat-card:hover { transform:translateY(-3px); box-shadow:0 2px 4px rgba(16,24,40,.05), 0 8px 20px rgba(16,24,40,.09); }
        .stat-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; }
        .stat-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
        .si-red    { background:#fde8e8; color:var(--accent); }
        .si-green  { background:#d1fae5; color:var(--green); }
        .si-blue   { background:#dbeafe; color:var(--blue); }
        .si-orange { background:#fef3c7; color:var(--orange); }
        .si-gray   { background:#f3f4f6; color:#9ca3af; }
        .stat-lbl  { font-family:'Poppins',sans-serif; font-size:15px; font-weight:700; color:#1a1a1a; margin-bottom:12px; display:block; }
        .stat-num  { font-size:28px; font-weight:700; color:#1a1a1a; line-height:1; margin-bottom:0; }
        .stat-chg  { font-size:11px; color:var(--green); margin-top:2px; }
        .stat-chg.neutral { color:#999; }

        /* LEADERBOARD — podium on a platform, then the rest of the top 10 */
        .lb2 { background:#fff; border-radius:14px; padding:16px 20px 14px; margin-bottom:14px; box-shadow:0 1px 2px rgba(16,24,40,.04), 0 4px 14px rgba(16,24,40,.06); }
        .lb2-head { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; flex-wrap:wrap; margin-bottom:6px; }
        .lb2-title { font-size:15px; font-weight:600; color:#1f2937; display:flex; align-items:center; gap:8px; }
        .lb2-title i { color:#d4a017; }
        .lb2-sub { font-size:12px; color:#6b7280; margin-top:2px; }
        .lb2-note { font-size:12px; color:#9ca3af; }
        .lb2-empty { text-align:center; padding:34px 10px; color:#9ca3af; font-size:13px; }
        .lb2-empty i { display:block; font-size:28px; color:#e5e7eb; margin-bottom:10px; }
        .lb2-grid { display:grid; grid-template-columns:minmax(0, 1fr); gap:14px; align-items:start; }
        .podium { display:flex; align-items:flex-end; justify-content:center; gap:8px; padding-top:6px; max-width:420px; margin:0 auto; width:100%; }
        .pod { flex:1; min-width:0; max-width:130px; display:flex; flex-direction:column; align-items:center; text-align:center; position:relative; }
        .pod-crown { color:#d4a017; font-size:15px; margin-bottom:2px; }
        .pod-av { width:42px; height:42px; border-radius:50%; color:#fff; font-weight:700; font-size:13px; display:flex; align-items:center; justify-content:center; overflow:hidden; border:2px solid #fff; box-shadow:0 2px 6px rgba(16,24,40,.18); margin-bottom:5px; flex-shrink:0; }
        .pod-1 .pod-av { width:52px; height:52px; border-color:#f3d77a; }
        .pod-av img { width:100%; height:100%; object-fit:cover; display:block; }
        .pod-av-empty { background:#eceef2 !important; color:#c4c8d0; box-shadow:none; }
        .pod-name { font-size:12px; font-weight:600; color:#1f2937; max-width:100%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .pod-score { font-size:18px; font-weight:600; color:var(--primary); line-height:1.15; margin-top:1px; font-variant-numeric:tabular-nums; }
        .pod-score small { font-size:11px; font-weight:500; }
        .pod-meta { font-size:10.5px; color:#9ca3af; margin-bottom:4px; }
        .pod-skills { display:flex; flex-direction:column; gap:3px; align-items:center; margin-bottom:6px; }
        .pod.is-empty .pod-name, .pod.is-empty .pod-score { color:#c4c8d0; }
        .pod-base { width:100%; display:flex; align-items:flex-start; justify-content:center; border-radius:8px 8px 0 0; color:#fff; font-size:20px; font-weight:700; padding-top:6px; background:linear-gradient(180deg, #9ca3af, #6b7280); }
        .pod-1 .pod-base { height:70px; background:linear-gradient(180deg, #e6b422, #b8860b); }
        .pod-2 .pod-base { height:52px; background:linear-gradient(180deg, #a8b0bd, #6f7886); }
        .pod-3 .pod-base { height:38px; background:linear-gradient(180deg, #c98b5a, #8e5a30); }
        .pod.is-empty .pod-base { background:#f1f2f5; color:#d1d5db; }
        .pod.is-empty .pod-name { font-size:11px; font-weight:500; }
        .skill { display:inline-flex; align-items:center; gap:4px; font-size:10.5px; font-weight:600; padding:2px 8px; border-radius:20px; white-space:nowrap; }
        .skill.good { background:#d1fae5; color:#047857; } .skill.mid { background:#fef3c7; color:#b45309; } .skill.bad { background:#fee2e2; color:#b91c1c; }
        .skill i { font-size:9px; }
        .lb2-list { display:flex; flex-direction:column; gap:8px; }
        .lb2-row { display:flex; align-items:center; gap:10px; padding:8px 10px; border:1px solid #f0f1f4; border-radius:10px; }
        .lb2-rank { width:24px; text-align:center; font-size:13px; font-weight:600; color:#9ca3af; flex-shrink:0; }
        .lb2-av { width:34px; height:34px; border-radius:50%; color:#fff; font-size:11.5px; font-weight:700; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; }
        .lb2-av img { width:100%; height:100%; object-fit:cover; }
        .lb2-who { flex:1; min-width:0; }
        .lb2-name { font-size:13px; font-weight:600; color:#1f2937; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .lb2-skills { display:flex; flex-wrap:wrap; gap:5px; margin-top:5px; align-items:center; }
        .lb2-weak { font-size:11px; color:#b91c1c; }
        .lb2-weak i { font-size:10px; margin-right:3px; }
        .lb2-score { font-size:16px; font-weight:600; color:var(--primary); flex-shrink:0; font-variant-numeric:tabular-nums; }
        .lb2-more { text-align:center; color:#9ca3af; font-size:12.5px; padding:20px 10px; border:1px dashed #e5e7eb; border-radius:12px; }
        .lb2-more i { margin-right:6px; }
        .lb2-legend { display:flex; flex-wrap:wrap; gap:6px; align-items:center; margin-top:10px; padding-top:10px; border-top:1px solid #f3f4f6; }
        .lb2-legend-note { font-size:11.5px; color:#9ca3af; margin-left:auto; }
        @media (max-width:1000px) { .lb2-grid { grid-template-columns:minmax(0, 1fr); } .lb2-legend-note { margin-left:0; width:100%; } }
        @media (max-width:520px) { .pod-av { width:38px; height:38px; font-size:12px; } .pod-1 .pod-av { width:46px; height:46px; } .pod-score { font-size:16px; } .lb2 { padding:14px 12px; } }

        /* Leaderboard and the student table side by side */
        .perf-duo { display:grid; grid-template-columns:minmax(0, 1fr) minmax(0, 1fr); gap:14px; margin-bottom:14px; align-items:stretch; }
        .perf-duo > section { min-width:0; display:flex; flex-direction:column; }
        .perf-duo .lb2 { margin-bottom:0; flex:1; }
        .perf-duo .lb2-grid { grid-template-columns:minmax(0, 1fr); gap:22px; align-items:stretch; }
        .perf-duo #perfStudents .table-card { flex:1; overflow-x:auto; }
        /* Half-width table: the leaderboard already shows each student's subjects, so that column steps aside. */
        .perf-duo #perfStudents th:nth-child(3), .perf-duo #perfStudents td:nth-child(3) { display:none; }
        .perf-duo #perfStudents thead th, .perf-duo #perfStudents tbody td { padding-left:10px; padding-right:10px; }
        .perf-duo #perfStudents .table-card table { min-width:0; width:100%; }
        .perf-duo #perfStudents .student-email { display:none; }
        .perf-duo #perfStudents .last-active { white-space:normal; }
        @media (max-width:1100px) { .perf-duo { grid-template-columns:minmax(0, 1fr); } }
        /* INSIGHTS */
        .insights-head { font-size:13px; font-weight:700; color:#333; margin:4px 0 12px; display:flex; align-items:center; gap:8px; }
        .insights-head i { color:var(--primary); }
        .insights-fold { background:#fff; border-radius:14px; margin-bottom:14px; box-shadow:0 1px 2px rgba(16,24,40,.04), 0 4px 14px rgba(16,24,40,.06); }
        .insights-fold > summary { list-style:none; cursor:pointer; display:flex; align-items:center; gap:12px; padding:13px 18px; user-select:none; }
        .insights-fold > summary::-webkit-details-marker { display:none; }
        .insights-fold .if-title { font-size:13.5px; font-weight:600; color:#1f2937; display:flex; align-items:center; gap:8px; }
        .insights-fold .if-title i { color:#f59e0b; }
        .insights-fold .if-count { font-size:11px; font-weight:600; color:#b45309; background:#fffbeb; border:1px solid #fde68a; padding:2px 9px; border-radius:20px; }
        .insights-fold .if-hint { margin-left:auto; font-size:12px; color:#6b7280; display:flex; align-items:center; gap:6px; }
        .insights-fold .if-hint i { font-size:10px; transition:transform .2s; }
        .insights-fold[open] .if-hint i { transform:rotate(180deg); }
        .insights-fold .if-hide, .insights-fold[open] .if-show { display:none; } .insights-fold[open] .if-hide { display:inline; }
        .insights-fold > .insights-grid { padding:0 18px 16px; margin-bottom:0; }
        .insights-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:14px; margin-bottom:14px; align-items:stretch; }
        .insight-card { background:#fff; border-radius:12px; padding:16px 18px; display:flex; gap:13px; align-items:flex-start; border-left:4px solid #ccc; box-shadow:0 2px 8px rgba(0,0,0,.04); }
        .insight-card.tone-good { border-left-color:var(--green); }
        .insight-card.tone-warn { border-left-color:var(--orange); }
        .insight-card.tone-crit { border-left-color:var(--accent); }
        .insight-card.tone-info { border-left-color:var(--blue); }
        .insight-icon { width:34px; height:34px; border-radius:9px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:14px; }
        .insight-card.tone-good .insight-icon { background:#d1fae5; color:var(--green); }
        .insight-card.tone-warn .insight-icon { background:#fef3c7; color:var(--orange); }
        .insight-card.tone-crit .insight-icon { background:#fde8e8; color:var(--accent); }
        .insight-card.tone-info .insight-icon { background:#dbeafe; color:var(--blue); }
        .insight-title { font-size:12.5px; font-weight:700; color:#1a1a1a; margin-bottom:3px; }
        .insight-text { font-size:11.5px; color:#777; line-height:1.5; }
        .insights-empty { background:#fff; border-radius:12px; padding:20px; text-align:center; color:#aaa; font-size:13px; margin-bottom:20px; }

        /* ANALYTICS CHARTS */
        .analytics-row { display:grid; grid-template-columns:minmax(0, 1fr) minmax(0, 1fr); gap:14px; margin-bottom:14px; align-items:start; }
        .chart-card { background:#fff; border-radius:14px; padding:18px 20px; }
        .chart-card h4 { font-size:13px; font-weight:700; color:#222; margin-bottom:4px; }
        .chart-card .chart-sub { font-size:11px; color:#aaa; margin-bottom:14px; }
        .chart-card .chart-wrap { position:relative; height:230px; }
        .chart-empty { height:230px; display:flex; align-items:center; justify-content:center; color:#bbb; font-size:12.5px; text-align:center; }
        .donut-legend { display:flex; justify-content:center; gap:18px; margin-top:12px; font-size:11.5px; color:#666; }
        .donut-legend span { display:inline-flex; align-items:center; gap:6px; }
        .donut-legend i { width:9px; height:9px; border-radius:3px; display:inline-block; }

        /* FILTER BAR */
        .filter-bar { background:white; border-radius:12px; padding:16px 20px; margin-bottom:18px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
        .search-wrap { position:relative; }
        .search-wrap i.search-ico { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#aaa; font-size:13px; }
        .search-wrap input { font-family:'Poppins',sans-serif; font-size:13px; border:1px solid #e0e0e0; border-radius:8px; padding:8px 12px 8px 32px; color:#555; background:white; outline:none; width:220px; }
        .search-wrap input:focus { border-color:var(--primary); }
        .search-wrap .search-spin { position:absolute; right:10px; top:50%; transform:translateY(-50%); color:var(--primary); font-size:12px; display:none; }
        .search-wrap.loading .search-spin { display:block; }
        select { font-family:'Poppins',sans-serif; font-size:13px; border:1px solid #e0e0e0; border-radius:8px; padding:8px 12px; color:#555; background:white; outline:none; cursor:pointer; }
        select:focus { border-color:var(--primary); }
        .filter-divider { width:1px; height:28px; background:#e8e8e8; }
        .filter-label { font-size:12px; color:#888; font-weight:500; }

        /* DYNAMIC CONTENT (AJAX-swapped) */
        #perfStats, #perfBody { transition:opacity .15s ease; }
        #perfBody.loading { opacity:.45; pointer-events:none; }

        /* MAIN LAYOUT */
        /* Left/right columns stretch to equal height so a short student list
           (e.g. one row) doesn't leave a big empty gap of bare page
           background hanging below the table while the side panels run on. */
        .perf-pair { display:grid; grid-template-columns:minmax(0, 1fr) minmax(0, 1fr); gap:14px; margin-bottom:14px; align-items:start; }
        .perf-pair > .side-card { min-width:0; }
        /* Every row is two equal cards of equal height, with the same gap and spacing below */
        .perf-pair > .side-card { display:flex; flex-direction:column; }
        .perf-pair > .side-card > form { margin-top:auto; padding-top:12px; }
        #perfAttention { align-items:stretch; }
        #perfAttention .chart-box-sm { flex:1; min-height:210px; margin-bottom:0; }
        .side-head { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:12px; }
        .side-head .side-title { margin-bottom:0; }
        .bulb-btn { display:inline-flex; align-items:center; gap:6px; border:1px solid #fde68a; background:#fffbeb; color:#b45309; font:600 11.5px 'Poppins',sans-serif; padding:5px 11px; border-radius:20px; cursor:pointer; }
        .bulb-btn:hover { background:#fef3c7; }
        .bulb-btn i { color:#f59e0b; }
        .wk-overlay { position:fixed; inset:0; z-index:3000; background:rgba(15,5,5,.5); display:flex; align-items:center; justify-content:center; padding:20px; }
        .wk-overlay[hidden] { display:none; }
        .wk-modal { background:#fff; border-radius:16px; width:100%; max-width:1000px; max-height:92vh; display:flex; flex-direction:column; box-shadow:0 20px 50px rgba(0,0,0,.3); }
        .wk-head { display:flex; justify-content:space-between; gap:12px; padding:18px 22px 12px; border-bottom:1px solid #f3f4f6; }
        .wk-title { font-size:16px; font-weight:600; color:#1f2937; display:flex; align-items:center; gap:9px; }
        .wk-title i { color:#f59e0b; }
        .wk-sub { font-size:12px; color:#6b7280; margin-top:3px; }
        .wk-close { border:0; background:#f4f5f7; width:32px; height:32px; border-radius:50%; cursor:pointer; color:#666; flex-shrink:0; }
        .wk-body { padding:14px 22px; overflow-y:auto; display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:4px 28px; align-content:start; }
        @media (max-width:760px) { .wk-body { grid-template-columns:minmax(0, 1fr); } }
        .wk-foot { padding:12px 22px; border-top:1px solid #f3f4f6; text-align:right; }
        .wk-foot a { font-size:12.5px; font-weight:600; color:var(--accent); text-decoration:none; }

        /* Left column: student table, with Class Weak Topics stacked below it. */
        .left-col { display:flex; flex-direction:column; gap:18px; }

        /* TABLE CARD */
        .table-card { background:white; border-radius:14px; overflow:hidden; display:flex; flex-direction:column; flex:1; }
        .table-card table { flex-shrink:0; }
        .table-head-bar { padding:16px 20px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f5f5f5; }
        .count { font-size:13px; color:#888; }

        table { width:100%; border-collapse:collapse; }
        thead th { text-align:left; font-size:11px; color:#aaa; font-weight:600; padding:12px 16px; text-transform:uppercase; letter-spacing:.4px; border-bottom:1px solid #f5f5f5; background:#fafafa; }
        thead th:first-child { padding-left:20px; }
        tbody tr { border-bottom:1px solid #f8f8f8; transition:background .15s; }
        tbody tr:last-child { border-bottom:none; }
        tbody tr:hover { background:#fafafa; }
        tbody td { padding:13px 16px; font-size:13px; vertical-align:middle; }
        tbody td:first-child { padding-left:20px; }

        .student-cell { display:flex; align-items:center; gap:10px; }
        .student-av { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:white; flex-shrink:0; }
        .student-name { font-weight:600; color:#1a1a1a; font-size:13px; }
        .student-email { font-size:11px; color:#bbb; }

        .score-cell { display:flex; align-items:center; gap:10px; }
        .score-num { font-size:15px; font-weight:700; width:42px; }
        .score-bar-bg { flex:1; height:6px; background:#f0f0f0; border-radius:3px; overflow:hidden; }
        .score-bar-fill { height:100%; border-radius:3px; }

        .subj-dots { display:flex; gap:5px; flex-wrap:wrap; }
        .subj-dot { width:auto; min-width:24px; height:24px; padding:0 6px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:9px; font-weight:700; }

        .trend-badge { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:600; padding:3px 8px; border-radius:5px; }
        .t-up   { background:#d1fae5; color:#059669; }
        .t-down { background:#fde8e8; color:var(--accent); }
        .t-flat { background:#f3f4f6; color:#9ca3af; }
        .t-new  { background:#e4edfb; color:#2f6fd0; }

        .last-active { font-size:11px; color:#aaa; }

        .view-btn { display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:7px; font-size:11px; font-weight:600; color:var(--accent); background:var(--primary-light); border:none; cursor:pointer; font-family:'Poppins',sans-serif; transition:all .2s; text-decoration:none; }
        .view-btn:hover { background:#fbd5d5; }

        .empty-row td { text-align:center; color:#aaa; padding:40px 16px; font-size:13px; }

        /* PAGINATION */
        .pagination { padding:14px 20px; display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f5f5f5; margin-top:auto; }
        .pag-info { font-size:12px; color:#999; }
        .pag-btns { display:flex; gap:5px; }
        .pag-btn { min-width:30px; height:30px; padding:0 8px; border:1px solid #e0e0e0; background:white; border-radius:7px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:12px; color:#555; transition:all .2s; text-decoration:none; }
        .pag-btn.active { background:var(--primary); color:white; border-color:var(--primary); }
        .pag-btn:hover:not(.active):not(.disabled) { background:#f5f5f5; }
        .pag-btn.disabled { opacity:.4; pointer-events:none; }

        /* RIGHT PANEL */
        .right-panel { display:flex; flex-direction:column; gap:16px; }
        .side-card { background:white; border-radius:14px; padding:20px; }
        .side-title { font-size:13px; font-weight:700; color:#1a1a1a; margin-bottom:14px; }

        .at-risk-item { display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #f8f8f8; }
        .at-risk-item:last-child { border-bottom:none; }
        .at-risk-av { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:white; background:var(--accent); flex-shrink:0; }
        .at-risk-name { font-size:12px; font-weight:600; color:#1a1a1a; }
        .at-risk-score { font-size:11px; color:var(--accent); font-weight:700; }
        .at-risk-sub { font-size:10px; color:#aaa; }

        .weak-item { display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #f8f8f8; }
        .weak-item:last-child { border-bottom:none; }
        .weak-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0; }
        .weak-name { font-size:12px; font-weight:600; color:#1a1a1a; flex:1; }
        .weak-sub { font-size:10px; color:#bbb; font-weight:500; }
        .weak-rate { font-size:12px; font-weight:700; color:var(--accent); }
        .weak-why { font-size:10.5px; color:#8a8a8a; font-weight:500; margin-top:3px; line-height:1.4; }
        .weak-why.weak-miss { color:#b45309; }
        .chart-box-sm { position:relative; height:210px; margin-bottom:14px; overflow-y:auto; overflow-x:hidden; }
        .chart-box-sm .chart-inner { position:relative; width:100%; height:100%; }
        .chart-box-sm::-webkit-scrollbar { width:5px; }
        .chart-box-sm::-webkit-scrollbar-thumb { background:#e5d5d5; border-radius:3px; }
        .chart-box-sm::-webkit-scrollbar-track { background:transparent; }
        .weak-list-scroll { max-height:280px; overflow-y:auto; padding-right:4px; margin-right:-4px; }
        .weak-list-scroll::-webkit-scrollbar { width:5px; }
        .weak-list-scroll::-webkit-scrollbar-thumb { background:#e5d5d5; border-radius:3px; }
        .weak-list-scroll::-webkit-scrollbar-track { background:transparent; }

        .muted-empty { font-size:12px; color:#bbb; padding:6px 0; }

        /* FLASH */
        .flash { background:#d1fae5; color:#059669; padding:12px 18px; border-radius:10px; margin-bottom:16px; font-size:13px; font-weight:600; }

        /* MODAL */
        .modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.45); display:none; align-items:center; justify-content:center; z-index:1000; padding:20px; }
        .modal-overlay.open { display:flex; }
        .modal { background:white; border-radius:16px; width:100%; max-width:520px; max-height:88vh; overflow-y:auto; padding:24px; animation:fadeUp .25s ease both; }
        .modal-head { display:flex; align-items:center; gap:12px; margin-bottom:18px; }
        .modal-av { width:46px; height:46px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:15px; font-weight:700; color:white; flex-shrink:0; }
        .modal-name { font-size:16px; font-weight:700; color:#1a1a1a; }
        .modal-email { font-size:12px; color:#aaa; }
        .modal-close { margin-left:auto; background:#f4f5f7; border:none; width:32px; height:32px; border-radius:8px; cursor:pointer; color:#777; font-size:14px; }
        .modal-close:hover { background:#eceef1; }
        .modal-metrics { display:grid; grid-template-columns:repeat(3,minmax(0, 1fr)); gap:10px; margin-bottom:18px; }
        .mm { background:#f9f9fb; border-radius:10px; padding:12px; text-align:center; }
        .mm-num { font-size:18px; font-weight:700; color:#1a1a1a; }
        .mm-lbl { font-size:10px; color:#999; margin-top:2px; }
        .modal-sec-title { font-size:12px; font-weight:700; color:#1a1a1a; margin:14px 0 10px; }
        .subj-row { margin-bottom:10px; }
        .subj-row-top { display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px; }
        .subj-row-bar { height:6px; background:#f0f0f0; border-radius:4px; overflow:hidden; }
        .modal-weak-item { font-size:12px; padding:8px 10px; background:#fef2f2; border-radius:8px; margin-bottom:6px; }
        .modal-weak-item .wt-row { display:flex; justify-content:space-between; align-items:center; }
        .modal-weak-item .wt { color:#7f1d1d; font-weight:600; }
        .modal-weak-item .wr { color:var(--accent); font-weight:700; }
        .modal-weak-item .wt-why { color:#9a4a4a; font-weight:500; font-size:10.5px; margin-top:4px; line-height:1.4; }
        .modal-weak-item .wt-miss { color:#b45309; font-weight:500; font-size:10.5px; margin-top:2px; line-height:1.4; }
        /* ── Student view: header, four figures, then two cards (strengths | where they need help) ── */
        .modal.sv { padding:22px 26px 20px; }
        .sv-head { display:flex; align-items:center; gap:14px; padding-right:48px; margin-bottom:14px; flex-wrap:wrap; }
        .sv-av { width:54px; height:54px; font-size:17px; }
        .sv-who { min-width:0; flex:1 1 220px; }
        .sv-who .modal-name { font-size:19px; font-weight:600; color:#1f2937; }
        .sv-who .modal-email { font-size:12.5px; color:#6b7280; margin-top:1px; }
        .sv-chips { display:flex; gap:8px; flex-wrap:wrap; }
        .sv-chip { display:inline-flex; align-items:center; gap:6px; font-size:11.5px; font-weight:600; padding:5px 12px; border-radius:20px; }
        .sv-chip.ok { background:#d1fae5; color:#047857; } .sv-chip.risk { background:#fee2e2; color:#b91c1c; }
        .sv-chip.neutral { background:#f3f4f6; color:#6b7280; } .sv-chip.up { background:#dbeafe; color:#1d4ed8; }
        .sv-tiles { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:12px; margin-bottom:14px; }
        .sv-tile { background:#f9fafb; border:1px solid #f0f1f4; border-radius:12px; padding:11px 16px; }
        .sv-tile-lbl { font-size:11px; font-weight:600; letter-spacing:.4px; text-transform:uppercase; color:#9ca3af; margin-bottom:6px; }
        .sv-tile-num { font-size:24px; font-weight:600; color:#1f2937; line-height:1.1; font-variant-numeric:tabular-nums; }
        .sv-grid { display:grid; grid-template-columns:minmax(0, 5fr) minmax(0, 7fr); gap:14px; align-items:stretch; }
        .sv-card { border:1px solid #f0f1f4; border-radius:14px; padding:15px 18px; background:#fff; display:flex; flex-direction:column; }
        .sv-h { font-size:13.5px; font-weight:600; color:#1f2937; margin:0 0 12px; display:flex; align-items:center; gap:8px; }
        .sv-h i { color:#9ca3af; font-size:12px; }
        .sv-sub { margin-bottom:10px; }
        .sv-sub-top { display:flex; justify-content:space-between; align-items:baseline; font-size:12.5px; margin-bottom:5px; }
        .sv-sub-code { font-weight:600; color:#374151; } .sv-sub-n { font-weight:400; color:#9ca3af; font-size:11.5px; margin-left:4px; }
        .sv-sub-pct { font-weight:600; font-variant-numeric:tabular-nums; }
        .sv-bar { height:7px; background:#f0f1f4; border-radius:5px; overflow:hidden; }
        .sv-bar i { display:block; height:100%; border-radius:5px; }
        .sv-callouts { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:10px; margin-top:6px; }
        .sv-call { border-radius:10px; padding:10px 12px; font-size:11.5px; line-height:1.4; }
        .sv-call b { display:block; font-size:13px; font-weight:600; margin-top:2px; }
        .sv-call.good { background:#ecfdf5; color:#047857; } .sv-call.bad { background:#fef2f2; color:#b91c1c; }
        .sv-weak { border:1px solid #f3e4e4; background:#fffafa; border-radius:12px; padding:9px 14px; margin-bottom:8px; }
        .sv-weak:last-child { margin-bottom:0; }
        .sv-weak-top { display:flex; align-items:center; gap:8px; margin-bottom:6px; }
        .sv-weak-name { font-size:13px; font-weight:600; color:#1f2937; min-width:0; flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .sv-weak-subj { font-size:10.5px; font-weight:600; color:#6b7280; background:#f3f4f6; padding:2px 8px; border-radius:20px; flex-shrink:0; }
        .sv-weak-pct { font-size:13px; font-weight:600; color:#b91c1c; flex-shrink:0; font-variant-numeric:tabular-nums; }
        .sv-weak .sv-bar { height:5px; margin-bottom:7px; }
        .sv-weak-note { font-size:11.5px; color:#6b7280; line-height:1.45; }
        .sv-weak-note i { width:14px; color:#9ca3af; }
        .sv-weak-tip { font-size:11.5px; color:#b45309; line-height:1.45; margin-top:2px; }
        .sv-weak-tip i { width:14px; color:#f59e0b; }
        .sv-more { text-align:center; font-size:12px; color:#9ca3af; padding-top:10px; }
        .sv-empty { text-align:center; color:#6b7280; font-size:13px; padding:26px 10px; }
        .sv-empty i { display:block; font-size:26px; color:#10b981; margin-bottom:8px; }
        @media (max-width:900px) { .sv-grid { grid-template-columns:minmax(0, 1fr); } .sv-tiles { grid-template-columns:repeat(2, minmax(0, 1fr)); } }
        .modal-weak-list { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:8px; }
        .modal-weak-list .modal-weak-item { margin-bottom:0; }
        .modal-weak-list .modal-more-note { grid-column:1 / -1; }
        /* The student view is one wide card with two columns, so nothing inside needs its own scroll bar */
        .modal.modal-lg { max-width:1080px; max-height:94vh; padding:22px 26px 20px; position:relative; }
        .modal-close-abs { position:absolute; top:16px; right:16px; z-index:2; }
        .modal-cols { display:grid; grid-template-columns:minmax(0, 360px) minmax(0, 1fr); gap:34px; align-items:start; }
        .modal-col-left .modal-head { padding-right:0; }
        .modal-col-right #mWeakChartBox { height:auto; overflow:visible; margin-bottom:14px; }
        .modal-col-right { padding-right:34px; }
        @media (max-width:900px) { .modal-cols { grid-template-columns:minmax(0, 1fr); gap:18px; } .modal-col-right { padding-right:0; } .modal-weak-list { grid-template-columns:minmax(0, 1fr); } }
        .modal-weak-list::-webkit-scrollbar { width:5px; }
        .modal-weak-list::-webkit-scrollbar-thumb { background:#e5d5d5; border-radius:3px; }
        .modal-weak-list::-webkit-scrollbar-track { background:transparent; }
        .modal-more-note { font-size:11px; color:#aaa; text-align:center; padding:4px 0 2px; }

        @keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
        .a0{animation:fadeUp .4s ease both} .a1{animation:fadeUp .4s .07s ease both} .a2{animation:fadeUp .4s .14s ease both}

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .stats-row { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
            .perf-pair { grid-template-columns: minmax(0, 1fr) !important; }
            .analytics-row { grid-template-columns: minmax(0, 1fr) !important; }
            .table-card { overflow-x: auto; }
            table { min-width: 620px; }
            .filter-bar { flex-direction: column; align-items: stretch; gap: 10px; }
            .filter-divider { display: none; }
            .search-wrap input { width: 100%; }
            .pagination { flex-direction: column; gap: 10px; align-items: flex-start; }
            .topbar-right { flex-wrap: wrap; gap: 8px; }
        }

        @media (max-width: 480px) {
            .stats-row { grid-template-columns: minmax(0, 1fr) !important; }
            .stat-num { font-size: 22px; }
            .score-bar-bg { display: none; }
        }
    </style>
</head>
<body>

@include('partials.faculty-sidebar', ['active' => 'performance'])

<main class="main">
    <div class="topbar a0">
        <div class="topbar-left">
            <div>
                <div class="page-title">Student Performance</div>
                <div class="page-sub">Monitor student progress and identify students that need help.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('faculty.performance.export', $activeQuery) }}" id="exportBtn" class="btn btn-ghost"><i class="fas fa-file-excel"></i> Export Excel</a>
            <form method="POST" action="{{ route('faculty.performance.remind') }}" id="sendReportForm" style="display:inline;"
                  data-confirm="Every student in the current view will receive a performance check-in email. This cannot be unsent."
                  data-confirm-title="Send performance report?"
                  data-confirm-ok="Yes, send it"
                  data-confirm-icon="question"
                  data-loading="Sending check-in emails...">
                @csrf
                <input type="hidden" name="scope" value="all">
                <button type="submit" class="btn btn-primary"><i class="fas fa-envelope"></i> Send Report</button>
            </form>
            @include('partials.topbar-actions')
        </div>
    </div>

    {{-- Status and validation messages surface as SweetAlert popups via partials.alerts --}}

    <!-- STATS (above the filter bar) -->
    @include('faculty.partials.performance-stats')

    <!-- FILTER BAR -->
    <form method="GET" action="{{ route('faculty.performance') }}" id="filterForm" class="filter-bar">
        <div class="search-wrap" id="searchWrap">
            <i class="fas fa-search search-ico"></i>
            <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search students..." autocomplete="off">
            <i class="fas fa-spinner fa-spin search-spin"></i>
        </div>
        <div class="filter-divider"></div>
        <span class="filter-label">Subject</span>
        <select name="subject">
            <option value="">All Subjects</option>
            @foreach($subjects as $s)
                <option value="{{ $s->id }}" @selected($filters['subject'] === $s->id)>{{ $s->code }}</option>
            @endforeach
        </select>
        <span class="filter-label">Period</span>
        <select name="period">
            <option value="30"  @selected($filters['period']==='30')>Last 30 Days</option>
            <option value="7"   @selected($filters['period']==='7')>Last 7 Days</option>
            <option value="90"  @selected($filters['period']==='90')>Last 3 Months</option>
            <option value="all" @selected($filters['period']==='all')>All Time</option>
        </select>
        <span class="filter-label">Sort by</span>
        <select name="sort">
            <option value="score_desc" @selected($filters['sort']==='score_desc')>Avg Score (Desc)</option>
            <option value="score_asc"  @selected($filters['sort']==='score_asc')>Avg Score (Asc)</option>
            <option value="active"     @selected($filters['sort']==='active')>Most Active</option>
            <option value="name"       @selected($filters['sort']==='name')>Name A-Z</option>
        </select>
    </form>

    <!-- ANALYTICS (insights + charts, swapped in place via AJAX) -->
    <!-- 1 · What it means, then who needs help right now -->
    @include('faculty.partials.performance-insights')
    @include('faculty.partials.performance-attention')

    <!-- 2 · The people: leaderboard and the student list -->
    <div class="perf-duo">
        @include('faculty.partials.performance-leaderboard')
        @include('faculty.partials.performance-students')
    </div>

    <!-- 3 · Where the class is strong, and how scores are spread -->
    @include('faculty.partials.performance-body')

    <!-- 4 · Trend over time and board readiness -->
    @include('faculty.partials.performance-analytics')
</main>

<!-- STUDENT DETAIL MODAL -->
<div class="modal-overlay" id="studentModal" onclick="if(event.target===this)closeStudent()">
    <div class="modal modal-lg sv" role="dialog" aria-modal="true" aria-labelledby="mName">
        <button class="modal-close modal-close-abs" onclick="closeStudent()" aria-label="Close"><i class="fas fa-times"></i></button>

        <header class="sv-head">
            <div class="modal-av sv-av" id="mAv"></div>
            <div class="sv-who">
                <div class="modal-name" id="mName"></div>
                <div class="modal-email" id="mEmail"></div>
            </div>
            <div class="sv-chips" id="mChips"></div>
        </header>

        <div class="sv-tiles">
            <div class="sv-tile"><div class="sv-tile-lbl">Average score</div><div class="sv-tile-num" id="mScore"></div></div>
            <div class="sv-tile"><div class="sv-tile-lbl">Quizzes taken</div><div class="sv-tile-num" id="mQuizzes"></div></div>
            <div class="sv-tile"><div class="sv-tile-lbl">Questions answered</div><div class="sv-tile-num" id="mAttempted"></div></div>
            <div class="sv-tile"><div class="sv-tile-lbl">Weak topics</div><div class="sv-tile-num" id="mWeakCount"></div></div>
        </div>

        <div class="sv-grid">
            <section class="sv-card" aria-label="Accuracy by subject">
                <h4 class="sv-h"><i class="fas fa-chart-simple"></i> Accuracy by subject</h4>
                <div id="mSubjects"></div>
                <div class="sv-callouts" id="mCallouts"></div>
            </section>
            <section class="sv-card" aria-label="Weak topics">
                <h4 class="sv-h"><i class="fas fa-triangle-exclamation"></i> Where they need help</h4>
                <div id="mWeak"></div>
            </section>
        </div>
    </div>
</div>

<script>
    const PERF_URL        = "{{ route('faculty.performance') }}";
    const PERF_EXPORT_URL = "{{ route('faculty.performance.export') }}";

    // Per-student data for the detail modal - re-read after each AJAX swap.
    let STUDENTS = {};
    let DETAILS  = {};
    let classWeakChart   = null;
    let studentWeakChart = null;
    const chartFont = "'Poppins', sans-serif";
    function weakBarColor(acc) { return acc < 40 ? '#c0392b' : (acc < 60 ? '#e8567d' : '#f59e0b'); }

    // Break a topic name into short lines so Chart.js renders the full label
    // (as a multi-line tick) instead of clipping it against the narrow
    // side-panel width.
    function wrapChartLabel(label, maxLen = 16) {
        const words = String(label).split(' ');
        const lines = [];
        let line = '';
        words.forEach(w => {
            if (line && (line + ' ' + w).length > maxLen) {
                lines.push(line);
                line = w;
            } else {
                line = line ? line + ' ' + w : w;
            }
        });
        if (line) lines.push(line);
        return lines;
    }

    // Grows the scrollable chart wrapper to fit however many bars/label-lines
    // there are, instead of squeezing every topic into one fixed-height box
    // (which is what caused labels to overlap when a list got long).
    function sizeChartInner(innerId, topics, wrapLen) {
        const inner = document.getElementById(innerId);
        if (!inner) return;
        const perBar = 26;
        const perLine = 12;
        const rowHeights = topics.map(t => perBar + wrapChartLabel(t.topic, wrapLen).length * perLine);
        const total = rowHeights.reduce((a, b) => a + b, 0) + 20;
        inner.style.height = Math.max(210, total) + 'px';
    }

    function hydratePerf() {
        const el = document.getElementById('perfData');
        if (el) {
            let weakTopics = [];
            try {
                const data = JSON.parse(el.textContent);
                STUDENTS   = data.students   || {};
                DETAILS    = data.details    || {};
                weakTopics = data.weakTopics || [];
            } catch (e) { STUDENTS = {}; DETAILS = {}; }
            renderClassWeakChart(weakTopics);
        }

        const analyticsEl = document.getElementById('perfAnalyticsData');
        if (analyticsEl) {
            try {
                const data = JSON.parse(analyticsEl.textContent);
                renderTrendChart(data.trend || []);
                renderReadyChart(data.passRate);
            } catch (e) {}
        }
    }

    // ── Weekly accuracy trend (line) ──
    let trendChart = null;
    function renderTrendChart(trend) {
        if (trendChart) { trendChart.destroy(); trendChart = null; }
        const canvas = document.getElementById('trendChart');
        if (!canvas || !trend.length) return;

        trendChart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: trend.map(w => w.label),
                datasets: [{
                    label: 'Accuracy',
                    data: trend.map(w => w.accuracy),
                    borderColor: '#7B1D1D',
                    backgroundColor: 'rgba(123,29,29,.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    spanGaps: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#7B1D1D',
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (c) => c.parsed.y === null ? 'No activity' : c.parsed.y + '% accuracy',
                            afterLabel: (c) => trend[c.dataIndex].active_students + ' active student' + (trend[c.dataIndex].active_students === 1 ? '' : 's'),
                        },
                    },
                },
                scales: {
                    y: { beginAtZero: true, max: 100, grid: { color: '#f2f2f2' }, ticks: { callback: v => v + '%' } },
                    x: { grid: { display: false } },
                },
            },
        });
    }

    // ── Board-readiness donut, with the % drawn in the center ──
    let readyChart = null;
    const centerTextPlugin = {
        id: 'centerText',
        afterDraw(chart) {
            if (chart.canvas.id !== 'readyChart') return;
            const { ctx, chartArea } = chart;
            const cx = (chartArea.left + chartArea.right) / 2;
            const cy = (chartArea.top + chartArea.bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.font = "700 22px 'Poppins', sans-serif";
            ctx.fillStyle = '#1a1a1a';
            ctx.fillText(chart.data.datasets[0].data[0] + '%', cx, cy - 6);
            ctx.font = "600 10px 'Poppins', sans-serif";
            ctx.fillStyle = '#aaa';
            ctx.fillText('ready', cx, cy + 12);
            ctx.restore();
        },
    };
    function renderReadyChart(passRate) {
        if (readyChart) { readyChart.destroy(); readyChart = null; }
        const canvas = document.getElementById('readyChart');
        if (!canvas || passRate === null || passRate === undefined) return;

        readyChart = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: ['Ready', 'Not yet'],
                datasets: [{
                    data: [passRate, 100 - passRate],
                    backgroundColor: ['#10b981', '#e5484d'],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '72%',
                plugins: { legend: { display: false } },
            },
            plugins: [centerTextPlugin],
        });
    }

    // Rebuilt after every AJAX swap (subject/period filter change), since the
    // canvas element itself is replaced along with the rest of #perfBody.
    function renderClassWeakChart(weakTopics) {
        if (classWeakChart) { classWeakChart.destroy(); classWeakChart = null; }
        const canvas = document.getElementById('chartClassWeak');
        if (!canvas || !weakTopics.length) return;

        sizeChartInner('classWeakChartInner', weakTopics, 15);
        classWeakChart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: weakTopics.map(t => t.topic),
                datasets: [{
                    data: weakTopics.map(t => t.accuracy),
                    backgroundColor: weakTopics.map(t => weakBarColor(t.accuracy)),
                    borderRadius: 5,
                    borderSkipped: false,
                    barThickness: 16
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                indexAxis: 'y',
                layout: { padding: { left: 10 } },
                scales: {
                    x: { beginAtZero: true, max: 100, grid: { color: '#f3f4f6' }, ticks: { font: { family: chartFont, size: 9 }, callback: v => v + '%' } },
                    y: {
                        grid: { display: false },
                        ticks: {
                            font: { family: chartFont, size: 10, weight: '600' },
                            color: '#374151',
                            autoSkip: false,
                            callback: function (val) { return wrapChartLabel(this.getLabelForValue(val), 15); }
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: items => weakTopics[items[0].dataIndex].topic,
                            label: ctx => ctx.raw + '% accuracy',
                            afterLabel: ctx => weakTopics[ctx.dataIndex].subject
                        }
                    }
                }
            }
        });
    }

    function scoreColor(s) { return s >= 75 ? '#059669' : (s >= 60 ? '#d97706' : '#c0392b'); }
    function barColor(s)   { return s >= 75 ? '#10b981' : (s >= 60 ? '#f59e0b' : '#c0392b'); }

    function openStudent(id) {
        const s = STUDENTS[id];
        const d = DETAILS[id] || { subjects: [], weak: [] };
        if (!s) return;
        const $ = (x) => document.getElementById(x);
        const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

        // Header: who, and a plain-language status
        $('mAv').textContent = s.initials;
        $('mAv').style.background = s.color;
        $('mName').textContent = s.name;
        $('mEmail').textContent = s.email;
        const trend = { up: ['up', 'fa-arrow-trend-up', 'Improving'], down: ['risk', 'fa-arrow-trend-down', 'Slipping'], new: ['neutral', 'fa-star', 'New this fortnight'], flat: ['neutral', 'fa-minus', 'Steady'] }[s.trend] || ['neutral', 'fa-minus', 'Steady'];
        $('mChips').innerHTML =
            (s.at_risk ? '<span class="sv-chip risk"><i class="fas fa-triangle-exclamation"></i> At risk</span>' : '<span class="sv-chip ok"><i class="fas fa-circle-check"></i> On track</span>')
            + `<span class="sv-chip ${trend[0]}"><i class="fas ${trend[1]}"></i> ${trend[2]}</span>`;

        // Four figures
        $('mScore').textContent = s.score + '%';
        $('mScore').style.color = scoreColor(s.score);
        $('mQuizzes').textContent = s.quizzes;
        $('mAttempted').textContent = Number(s.attempted).toLocaleString();
        $('mWeakCount').textContent = d.weak.length;
        $('mWeakCount').style.color = d.weak.length ? '#b91c1c' : '#059669';

        // Accuracy by subject, best first, then the strongest / weakest callouts
        const subj = d.subjects.slice().sort((a, b) => b.accuracy - a.accuracy);
        $('mSubjects').innerHTML = subj.length ? subj.map((sub) => `
            <div class="sv-sub">
                <div class="sv-sub-top"><span><span class="sv-sub-code">${esc(sub.code)}</span><span class="sv-sub-n">${Number(sub.attempts).toLocaleString()} answers</span></span><span class="sv-sub-pct" style="color:${scoreColor(sub.accuracy)};">${sub.accuracy}%</span></div>
                <div class="sv-bar"><i style="width:${sub.accuracy}%;background:${barColor(sub.accuracy)};"></i></div>
            </div>`).join('') : '<div class="muted-empty">No topic data recorded yet.</div>';
        const best = subj[0], worst = subj.length > 1 ? subj[subj.length - 1] : null;
        $('mCallouts').innerHTML = (best ? `<div class="sv-call good"><i class="fas fa-arrow-up"></i> Strongest<b>${esc(best.code)} · ${best.accuracy}%</b></div>` : '')
            + (worst && worst.accuracy < best.accuracy ? `<div class="sv-call bad"><i class="fas fa-arrow-down"></i> Needs the most help<b>${esc(worst.code)} · ${worst.accuracy}%</b></div>` : '');

        // Weak topics: the four weakest, each with the reason and the usual wrong answer
        const MAX = 4;
        $('mWeak').innerHTML = !d.weak.length
            ? '<div class="sv-empty"><i class="fas fa-circle-check"></i>No weak topics — this student is on track.</div>'
            : d.weak.slice(0, MAX).map((w) => `
                <div class="sv-weak">
                    <div class="sv-weak-top"><span class="sv-weak-name" title="${esc(w.topic)}">${esc(w.topic)}</span><span class="sv-weak-subj">${esc(w.subject)}</span><span class="sv-weak-pct">${w.accuracy}%</span></div>
                    <div class="sv-bar"><i style="width:${w.accuracy}%;background:${weakBarColor(w.accuracy)};"></i></div>
                    <div class="sv-weak-note"><i class="fas fa-circle-info"></i>${esc(w.why)}</div>
                    ${w.miss ? `<div class="sv-weak-tip"><i class="fas fa-lightbulb"></i>${esc(w.miss)}</div>` : ''}
                </div>`).join('') + (d.weak.length > MAX ? `<div class="sv-more">+ ${d.weak.length - MAX} more weak topic${d.weak.length - MAX === 1 ? '' : 's'}</div>` : '');

        $('studentModal').classList.add('open');
    }
    function closeStudent() { document.getElementById('studentModal').classList.remove('open'); }

    // ── AJAX live filtering / sorting / pagination ─────────────────────────
    const filterForm  = document.getElementById('filterForm');
    const searchInput = filterForm.querySelector('input[name="search"]');
    const searchWrap  = document.getElementById('searchWrap');
    let   searchTimer;

    // Build the request URL from the current filter controls (page resets to 1).
    function currentUrl() {
        const params = new URLSearchParams();
        new FormData(filterForm).forEach((v, k) => { if (v !== '') params.set(k, v); });
        const qs = params.toString();
        return PERF_URL + (qs ? ('?' + qs) : '');
    }

    function loadPerf(url, push = true) {
        // Subtle, flicker-free loading hint: only the search-box spinner. We do
        // NOT dim or re-animate the regions, so the data swaps in seamlessly.
        searchWrap.classList.add('loading');

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                // Parse the response and swap the stats + analytics + body
                // regions in place, leaving the filter bar (and the focused
                // search box) untouched.
                const tmp = document.createElement('div');
                tmp.innerHTML = html;
                // Strip the entry-animation classes so the swap doesn't replay
                // the fade/slide (that was the flicker) - animate first load only.
                ['perfStats', 'perfInsights', 'perfAttention', 'perfLeaderboard', 'perfStudents', 'perfBody', 'perfAnalytics'].forEach(function (id) {
                    const fresh = tmp.querySelector('#' + id);
                    const old = document.getElementById(id);
                    if (fresh && old) { fresh.classList.remove('a1', 'a2'); old.replaceWith(fresh); }
                });
                restoreInsightsFold();
                hydratePerf();
                syncTopbar(url);
                if (push) history.pushState({ url }, '', url);
            })
            .catch(() => {})
            .finally(() => searchWrap.classList.remove('loading'));
    }

    // Keep the Export link + Send Report form in sync with the live filters.
    function syncTopbar(url) {
        const u = new URL(url, window.location.origin);
        u.searchParams.delete('page');
        const search = u.search;

        document.getElementById('exportBtn').href = PERF_EXPORT_URL + search;

        const form = document.getElementById('sendReportForm');
        form.querySelectorAll('[data-filter]').forEach(n => n.remove());
        const scope = form.querySelector('[name="scope"]');
        u.searchParams.forEach((v, k) => {
            const i = document.createElement('input');
            i.type = 'hidden'; i.name = k; i.value = v; i.dataset.filter = '1';
            form.insertBefore(i, scope);
        });
    }

    // Selects: instant. Search: debounced (real-time as you type).
    filterForm.addEventListener('change', e => {
        if (e.target.name !== 'search') loadPerf(currentUrl());
    });
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadPerf(currentUrl()), 300);
    });
    searchInput.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); clearTimeout(searchTimer); loadPerf(currentUrl()); }
    });

    // Pagination links inside the swapped body (document-level delegation, so
    // it keeps working after the body element is replaced).
    document.addEventListener('click', e => {
        const a = e.target.closest('#perfBody .pag-btn, #perfStudents .pag-btn');
        if (a && !a.classList.contains('disabled')) { e.preventDefault(); loadPerf(a.href); }
    });

    // Back / forward buttons.
    window.addEventListener('popstate', () => loadPerf(window.location.href, false));

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeStudent(); });

    // Initial hydrate + sync the topbar to the first-load filters.
    hydratePerf();
    syncTopbar(window.location.href);
</script>

    @include('partials.alerts')
<script>
// Class Weak Topics popup. The modal sits inside a region that is swapped by AJAX and plays an entry
// animation (which would trap a fixed-position child), so it is moved to <body> while it is open.
(function () {
    let home = null;
    function modal() { return document.getElementById('weakModal'); }
    function open() {
        const m = modal();
        if (!m) return;
        home = m.parentNode;
        document.body.appendChild(m);
        m.hidden = false;
    }
    function close() {
        const m = modal();
        if (!m) return;
        m.hidden = true;
        const region = document.getElementById('perfAttention');
        if (region && m.parentNode === document.body) region.appendChild(m);
    }
    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-open-weak]')) { open(); return; }
        const m = modal();
        if (m && !m.hidden && (e.target.closest('[data-close-weak]') || e.target === m)) close();
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();
</script>
<script>
// "What this view means" stays folded until opened; remember the choice while filters refresh the page.
function restoreInsightsFold() {
    const el = document.getElementById('insightsFold');
    if (el && sessionStorage.getItem('perfInsightsOpen') === '1') el.setAttribute('open', '');
}
document.addEventListener('toggle', function (e) {
    if (e.target && e.target.id === 'insightsFold') sessionStorage.setItem('perfInsightsOpen', e.target.open ? '1' : '0');
}, true);
restoreInsightsFold();
</script>
</body>
</html>
