<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Every card on this page (KPIs, sections toolbar, section cards,
           student list) carries the same soft shadow. */
        :root { --card-shadow: 0 1px 2px rgba(16,24,40,.04), 0 4px 14px rgba(16,24,40,.06); }
        .card, .kpi { box-shadow: var(--card-shadow); }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 210px;
        }
        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #bbb;
            font-size: 12px;
        }
        .search-box input { padding-left: 34px; }
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .table-wrap table { min-width: 1080px; }

        /* ── Student roster table: gray header band + fixed column widths ── */
        .roster-table { table-layout:fixed; border-collapse:separate; border-spacing:0; }
        .roster-table thead th {
            background:#f3f4f6; color:#4b5563; font-size:11px; font-weight:700; letter-spacing:.5px;
            padding:13px 12px; border-bottom:1px solid #e5e7eb; white-space:nowrap;
        }
        .roster-table thead th:first-child { border-top-left-radius:12px; }
        .roster-table thead th:last-child { border-top-right-radius:12px; }
        .roster-table tbody tr { border-top:0; transition:background .12s; }
        .roster-table tbody td { padding:14px 12px; border-bottom:1px solid #f2f2f4; overflow:hidden; text-overflow:ellipsis; }
        .roster-table tbody tr:hover { background:#fafafa; }
        .roster-table .col-check { text-align:center; padding-left:0; padding-right:0; }
        .roster-table tbody td:first-child { text-align:center; padding-left:0; padding-right:0; }
        .roster-table input[type=checkbox] { width:15px; height:15px; accent-color:var(--primary); cursor:pointer; vertical-align:middle; }
        .roster-table .col-actions { text-align:center; overflow:visible; }
        .roster-table .student-cell > div { min-width:0; }
        .roster-table .student-name, .roster-table .student-meta { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .roster-table .score-bar { width:100%; max-width:90px; }
        .roster-table .otp-cell { flex-wrap:wrap; row-gap:4px; }
        .student-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .student-cell .user-av {
            width: 34px;
            height: 34px;
            font-size: 10px;
        }
        .student-name {
            font-size: 12.5px;
            font-weight: 600;
            color: #1a1a1a;
        }
        .student-meta {
            font-size: 10px;
            color: #aaa;
            margin-top: 2px;
        }
        .group-tag {
            display: inline-flex;
            padding: 3px 8px;
            border-radius: 12px;
            background: #ede9fe;
            color: #6d28d9;
            font-size: 9.5px;
            font-weight: 700;
            margin: 2px;
        }
        .ungrouped {
            color: #bbb;
            font-size: 10px;
        }
        .score {
            font-size: 13px;
            font-weight: 700;
        }
        .score-bar {
            width: 70px;
            height: 5px;
            border-radius: 4px;
            background: #eee;
            margin-top: 4px;
            overflow: hidden;
        }
        .score-bar span {
            display: block;
            height: 100%;
            border-radius: 4px;
        }
        .risk-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 14px;
            font-size: 9.5px;
            font-weight: 700;
            background: #fde8e8;
            color: #b91c1c;
        }
        .action-btn {
            width: 29px;
            height: 29px;
            border: 0;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            cursor: pointer;
            font-size: 10px;
        }
        .ab-view { background: #d1fae5; color: #047857; }
        .ab-edit { background: #dbeafe; color: #2563eb; }
        .ab-toggle { background: #fef3c7; color: #b45309; }
        .ab-section { background: #ede9fe; color: #6d28d9; }

        /* ── Row actions "..." menu (fixed-positioned so the table's
           horizontal scroll container doesn't clip it) ── */
        .row-menu { display:inline-block; }
        .row-dots { width:32px; height:32px; border:1px solid #e5e7eb; border-radius:9px; background:#fff; color:#4b5563; font-size:14px; cursor:pointer; transition:background .15s, border-color .15s, color .15s; }
        .row-dots:hover, .row-menu.open .row-dots { background:#f3f4f6; border-color:#d1d5db; color:#111827; }
        .row-dropdown { display:none; position:fixed; z-index:1500; min-width:190px; background:#fff; border-radius:12px; padding:6px; box-shadow:0 12px 32px rgba(16,24,40,.16); border:1px solid #f0f0f0; text-align:left; }
        .row-menu.open .row-dropdown { display:block; }
        .row-dropdown form { margin:0; }
        .row-dropdown a, .row-dropdown button { display:flex; align-items:center; gap:10px; width:100%; padding:9px 12px; border:0; background:none; border-radius:8px; font-family:'Poppins', sans-serif; font-size:12.5px; color:#333; cursor:pointer; text-align:left; text-decoration:none; }
        .row-dropdown a i, .row-dropdown button i { width:14px; color:#999; }
        .row-dropdown a:hover, .row-dropdown button:hover { background:#f6f6f7; }
        .row-dropdown button.danger, .row-dropdown button.danger i { color:#b91c1c; }
        .ab-section:hover { background: #ddd6fe; }

        .ab-delete { background:#fde8e8; color:#b91c1c; }
        .btn-plain { background:#fff; color:#1a1a1a; border:1px solid #e5e7eb; }
        .btn-plain:hover { background:#f9fafb; border-color:#d1d5db; }

        /* ── KPI cards: icon box + number, context line with a chevron ── */
        .kpi-row { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:16px; margin-bottom:28px; }
        .kpi { display:flex; flex-direction:column; background:#fff; border-radius:16px; padding:20px 20px 14px; text-decoration:none; color:inherit; transition:transform .15s ease; }
        .kpi:hover { transform:translateY(-2px); }
        .kpi-top { display:flex; align-items:center; gap:16px; flex:1; padding-bottom:16px; }
        .kpi-icon { width:56px; height:56px; border-radius:14px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:22px; }
        .t-rose { background:#fbe9ec; color:var(--primary); }
        .t-green { background:#dcf5ec; color:#059669; }
        .t-amber { background:#fdf0d5; color:#d97706; }
        .t-red { background:#fde8e8; color:#b91c1c; }
        .kpi-num { font-size:28px; font-weight:700; color:#1a1a1a; line-height:1.1; }
        .kpi-lbl { font-size:13px; font-weight:500; color:#444; margin-top:2px; }
        .kpi-foot { display:flex; align-items:center; justify-content:space-between; gap:10px; padding-top:12px; border-top:1px solid #f0f0f0; font-size:11px; color:#888; }
        .kpi-foot strong { font-weight:700; }
        .kpi-foot i { color:#999; font-size:11px; }
        .kpi:hover .kpi-foot i { color:var(--primary); }

        /* ── Sections header + toolbar ── */
        .sec-header { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:18px; }
        .sec-h-title { font-size:24px; font-weight:700; color:#1a1a1a; line-height:1.2; letter-spacing:-.02em; }
        .sec-h-sub { font-size:12.5px; color:#888; margin-top:4px; }
        .sec-toolbar { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
        .sec-search { position:relative; width:230px; }
        .sec-search i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#a3a8b0; font-size:12.5px; pointer-events:none; transition:color .15s; }
        .sec-toolbar input, .sec-toolbar select {
            height:40px; border:1px solid #e5e7eb; border-radius:11px; background-color:#fff;
            font-size:12.5px; font-weight:500; color:#1a1a1a; transition:background-color .15s, border-color .15s, box-shadow .15s;
        }
        .sec-toolbar input { width:100%; padding:0 14px 0 38px; }
        .sec-toolbar input::placeholder { color:#a3a8b0; font-weight:400; }
        .sec-toolbar select {
            width:auto; min-width:150px; padding:0 38px 0 14px; cursor:pointer;
            appearance:none; -webkit-appearance:none;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat:no-repeat; background-position:right 14px center;
        }
        .sec-toolbar input:hover, .sec-toolbar select:hover { border-color:#d1d5db; }
        .sec-toolbar input:focus, .sec-toolbar select:focus { outline:none; background-color:#fff; border-color:var(--primary); box-shadow:0 0 0 3px rgba(123,29,29,.1); }
        .sec-search:focus-within i { color:var(--primary); }
        .sec-toolbar select.is-set { background-color:var(--primary-light); color:var(--primary); font-weight:600; }
        .sec-clear {
            display:inline-flex; align-items:center; gap:7px; height:40px; padding:0 8px 0 14px; border:0; border-radius:11px;
            background:var(--primary-light); color:var(--primary); font:inherit; font-size:12.5px; font-weight:600; cursor:pointer;
            opacity:0; transform:scale(.85); width:0; padding:0; overflow:hidden; pointer-events:none;
            transition:opacity .2s ease, transform .2s ease, background .15s;
        }
        .sec-clear.show { opacity:1; transform:scale(1); width:auto; padding:0 8px 0 14px; pointer-events:auto; }
        .sec-clear:hover { background:#efd6d6; }
        .sec-clear:active { transform:scale(.95); }
        .sec-clear-n { min-width:20px; height:20px; border-radius:10px; background:var(--primary); color:#fff; font-size:10.5px; display:inline-flex; align-items:center; justify-content:center; padding:0 6px; }
        .sec-result { font-size:12px; color:#888; margin:-6px 0 14px; display:none; }
        .sec-result.show { display:block; }
        .sec-result strong { color:#1a1a1a; }
        .sec-divider { width:1px; height:24px; background:#e8e9ec; margin:0 4px; }
        .sec-link { display:inline-flex; align-items:center; gap:8px; height:40px; padding:0 14px; border-radius:11px; color:#444; font-size:12.5px; font-weight:600; text-decoration:none; transition:background .15s, color .15s; }
        .sec-link:hover { background:#fff; color:var(--primary); }
        .sec-toolbar .btn-primary { height:40px; padding:0 18px; border-radius:11px; font-size:12.5px; font-weight:600; box-shadow:0 4px 12px rgba(123,29,29,.22); }
        .sec-toolbar .btn-primary:hover { box-shadow:0 6px 16px rgba(123,29,29,.3); }

        /* ── Section cards ── */
        .sec-h-title { font-family:'Montserrat', 'Poppins', sans-serif; }
        .sc-name { font-family:'Poppins', sans-serif; }
        .sc-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:18px; margin-bottom:26px; align-items:stretch; }
        .sc { background:#fff; border-radius:16px; padding:12px; display:flex; flex-direction:column; box-shadow:var(--card-shadow); }
        .sc-head { display:flex; align-items:center; gap:14px; min-height:80px; padding:14px 14px 16px; background:#fff; border-bottom:1px solid #f0f0f2; }
        .sc-icon {
            width:48px; height:48px; border-radius:14px; flex-shrink:0; display:flex; align-items:center; justify-content:center;
            background:linear-gradient(145deg, #fff 0%, #f8dfe3 100%); color:var(--primary);
            box-shadow:inset 0 0 0 1px rgba(123,29,29,.08), 0 4px 10px rgba(123,29,29,.10);
        }
        .sc-icon svg { width:24px; height:24px; }
        .sc-title { min-width:0; flex:1; }
        .sc-name { font-size:17px; font-weight:700; color:#1a1a1a; line-height:1.2; }
        .sc-meta { font-size:12px; color:#888; margin-top:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .sc-name { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .sc-badge { font-size:10px; font-weight:700; background:#f1f2f4; color:#888; border-radius:20px; padding:3px 9px; }
        .sc-menu { position:relative; align-self:flex-start; }
        .sc-dots { width:32px; height:32px; border:1px solid #e5e7eb; border-radius:9px; background:#fff; color:#4b5563; font-size:14px; cursor:pointer; transition:background .15s, border-color .15s, color .15s; }
        .sc-dots:hover, .sc-menu.open .sc-dots { background:#f3f4f6; border-color:#d1d5db; color:#111827; }
        .sc-dropdown { display:none; position:absolute; right:0; top:34px; z-index:20; min-width:170px; background:#fff; border-radius:12px; padding:6px; box-shadow:0 10px 30px rgba(16,24,40,.14); border:1px solid #f0f0f0; }
        .sc-menu.open .sc-dropdown { display:block; }
        .sc-dropdown form { margin:0; }
        .sc-dropdown button { display:flex; align-items:center; gap:10px; width:100%; padding:9px 12px; border:0; background:none; border-radius:8px; font-family:'Poppins', sans-serif; font-size:12.5px; color:#333; cursor:pointer; text-align:left; }
        .sc-dropdown button i { width:14px; color:#999; }
        .sc-dropdown button:hover { background:#f6f6f7; }
        .sc-dropdown button.danger, .sc-dropdown button.danger i { color:#b91c1c; }
        .sc-body { padding:16px 6px 16px; flex:1; }
        .sc-label { font-size:12px; font-weight:600; color:#333; }
        .sc-readiness { display:flex; align-items:center; gap:16px; margin-top:4px; }
        .sc-pct { font-size:30px; font-weight:700; color:#1a1a1a; min-width:76px; }
        .sc-bar { flex:1; height:8px; border-radius:8px; background:#eceef1; overflow:hidden; }
        .sc-bar span { display:block; height:100%; border-radius:8px; background:var(--accent); }
        .sc-note { margin-top:10px; font-size:11px; color:#b45309; }
        .sc-note.muted { color:#999; }
        .sc-cta { display:flex; align-items:center; justify-content:center; gap:10px; padding:11px; border-radius:10px; background:#fdf0f2; color:var(--primary); font-size:12.5px; font-weight:600; text-decoration:none; transition:background .15s; }
        .sc-cta:hover { background:#f9e1e5; }
        .sc.is-inactive .sc-body, .sc.is-inactive .sc-head { opacity:.6; }
        .sc.is-unassigned .sc-icon { background:linear-gradient(145deg, #fff 0%, #fde6bd 100%); color:#d97706; box-shadow:inset 0 0 0 1px rgba(217,119,6,.12), 0 4px 10px rgba(217,119,6,.12); }
        .sc-alert { display:flex; align-items:center; gap:12px; padding:14px 16px; border-radius:12px; background:#fff8eb; border:1px solid #fde3b0; color:#b45309; font-size:12.5px; font-weight:500; line-height:1.5; }
        .sc-alert i { font-size:18px; color:#d97706; }
        .sc.is-unassigned .sc-cta { background:#f2c66d; color:#7a4a00; }
        .sc.is-unassigned .sc-cta:hover { background:#ecb94f; }
        .sc-empty { grid-column:1 / -1; text-align:center; padding:32px; color:#999; font-size:12.5px; background:#fff; border-radius:16px; box-shadow:var(--card-shadow); }
        .sc-empty i { display:block; font-size:22px; color:#ccc; margin-bottom:8px; }
        .crumb { display:inline-flex; align-items:center; gap:8px; font-size:12.5px; color:#888; text-decoration:none; margin-bottom:14px; }
        .crumb:hover { color:var(--primary); }
        .section-focus { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
        .year-pill { display:inline-flex; align-items:center; padding:3px 10px; border-radius:20px; font-size:10px; font-weight:700; background:#eef2ff; color:#4338ca; }

        /* ── Student list filters (inside the list card) ── */
        .list-filters { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
        .list-filters input, .list-filters select, .sec-toolbar input, .sec-toolbar select { font-family:'Poppins', sans-serif; }
        .lf-search { position:relative; flex:1; min-width:220px; max-width:420px; }
        .lf-search i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#a3a8b0; font-size:12.5px; pointer-events:none; }
        .list-filters input[type=search], .list-filters select {
            height:40px; border:1px solid #e5e7eb; border-radius:11px; background-color:#fff;
            font-size:12.5px; color:#1a1a1a; transition:border-color .15s, box-shadow .15s, background-color .15s;
        }
        .list-filters input[type=search] { width:100%; padding:0 14px 0 38px; }
        .list-filters select {
            width:auto; min-width:160px; padding:0 36px 0 14px; cursor:pointer; appearance:none; -webkit-appearance:none;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat:no-repeat; background-position:right 13px center;
        }
        .list-filters input[type=search]:hover, .list-filters select:hover { border-color:#d1d5db; }
        .list-filters input[type=search]:focus, .list-filters select:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(123,29,29,.1); }
        .list-filters select.is-set { background-color:var(--primary-light); border-color:#ecc7cd; color:var(--primary); font-weight:600; }
        .lf-clear { display:inline-flex; align-items:center; gap:6px; height:40px; padding:0 12px; border-radius:11px; color:var(--primary); font-size:12.5px; font-weight:600; text-decoration:none; }
        .lf-clear:hover { background:var(--primary-light); }
        @media (max-width: 1050px) { .kpi-row { grid-template-columns:repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 640px) {
            .kpi-row { grid-template-columns:minmax(0, 1fr); }
            .sec-toolbar, .sec-search { width:100%; }
            .sec-toolbar select { flex:1; }
            .sc-grid { grid-template-columns:minmax(0, 1fr); }
        }

        /* ── Modals (header band / body / footer band) ── */
        .modal.m2 { padding:0; border-radius:18px; overflow:hidden; display:flex; flex-direction:column; max-height:90vh; }
        .m-head { display:flex; align-items:center; gap:14px; padding:18px 22px; background:#faf7f7; border-bottom:1px solid #f0eaea; }
        .m-icon { width:44px; height:44px; border-radius:11px; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:var(--primary-light); color:var(--primary); font-size:16px; }
        .m-title { font-size:17px; font-weight:700; color:#1a1a1a; }
        .m-sub { font-size:12px; color:#888; margin-top:2px; }
        .m-close { margin-left:auto; width:38px; height:38px; border:0; border-radius:10px; background:#f1f1f3; color:#666; cursor:pointer; font-size:15px; flex-shrink:0; }
        .m-close:hover { background:#e6e6e9; }
        .m-body { padding:20px 22px; overflow-y:auto; }
        .m-foot { display:flex; justify-content:flex-end; gap:10px; padding:14px 22px; background:#faf7f7; border-top:1px solid #f0eaea; }
        .m-foot .btn { padding:10px 20px; }
        .btn:disabled { opacity:.5; cursor:not-allowed; }
        .req { color:var(--accent); }
        .sec-builder { display:grid; grid-template-columns:minmax(0, 1fr) 1.3fr minmax(0, 1fr) minmax(0, 1fr); gap:10px; }
        .sec-builder .form-group { margin-bottom:0; }
        .sec-builder input, .sec-builder select { width:100%; min-width:0; }
        #sectionProgram { text-transform:uppercase; }
        .sec-preview { margin-top:16px; padding:14px 16px; border-radius:12px; background:#fdf3f4; border:1px dashed #ecc7cd; }
        .sp-label { font-size:11px; font-weight:600; color:#999; text-transform:uppercase; letter-spacing:.4px; }
        .sp-name { font-size:22px; font-weight:700; color:var(--primary); margin:2px 0; letter-spacing:.02em; }
        .sp-legend { font-size:11.5px; color:#888; }
        .sp-legend b { color:#555; font-weight:600; }
        .sp-old { display:flex; gap:10px; align-items:flex-start; margin-bottom:14px; padding:11px 14px; border-radius:10px; background:#fff8eb; border:1px solid #fde3b0; color:#92400e; font-size:12px; line-height:1.5; }
        .sp-old i { color:#d97706; margin-top:2px; }
        .sp-error { margin-top:8px; font-size:12px; font-weight:600; color:#b91c1c; }
        .sp-legend span { display:block; }
        @media (max-width: 560px) { .sec-builder { grid-template-columns:minmax(0, 1fr) minmax(0, 1fr); } }

        /* ── Assign-to-section picker ── */
        .assign-group-label { font-size:10.5px; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:.3px; margin:14px 0 6px; }
        .assign-group-label:first-child { margin-top:0; }
        .assign-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(140px, 1fr)); gap:7px; }
        .assign-opt { position:relative; display:flex; flex-direction:column; gap:2px; padding:9px 12px; border:1.5px solid #e5e7eb; border-radius:10px; cursor:pointer; transition:all .15s; }
        .assign-opt:hover { border-color:#d8b4b4; background:#fdf8f8; }
        .assign-opt input { position:absolute; opacity:0; pointer-events:none; }
        .assign-opt .a-name { font-size:12.5px; font-weight:700; color:#1a1a1a; }
        .assign-opt .a-meta { font-size:10.5px; color:#999; }
        .assign-opt:has(input:checked) { border-color:var(--primary); background:#fdf2f2; box-shadow:0 0 0 2px rgba(123,29,29,.12); }
        .assign-opt .a-current { position:absolute; top:7px; right:8px; font-size:9px; font-weight:700; color:var(--primary); background:var(--primary-light); border-radius:10px; padding:1px 6px; }
        .assign-opt.is-remove .a-name { color:#b91c1c; }

        /* ── Add students to a section ── */
        .drop-box { border:2px dashed #e2d6d6; border-radius:14px; padding:22px; text-align:center; background:#fdfbfb; }
        .drop-box .big { font-size:20px; color:var(--primary); }
        .drop-box .db-title { font-size:15px; font-weight:600; color:#1a1a1a; margin-top:8px; }
        .drop-box .db-sub { font-size:12px; color:#888; margin:4px 0 12px; }
        .pick-head { display:flex; align-items:center; justify-content:space-between; gap:10px; margin:18px 0 8px; font-size:13px; font-weight:700; color:#1a1a1a; }
        .pick-head .muted { color:#aaa; font-weight:500; }
        .pick-tools { display:grid; grid-template-columns:minmax(0, 1fr) auto; gap:8px; margin-bottom:8px; }
        .pick-tools input, .pick-tools select { padding:9px 12px; font-size:12.5px; width:100%; min-width:0; }
        .pick-list { max-height:260px; overflow-y:auto; border:1px solid #eee; border-radius:12px; }
        .pick-row { display:flex; align-items:center; gap:10px; padding:9px 14px; border-bottom:1px solid #f5f5f5; font-size:12.5px; cursor:pointer; }
        .pick-row:last-child { border-bottom:none; }
        .pick-row:hover { background:#fafafa; }
        .pick-row input { width:16px; height:16px; accent-color:var(--primary); }
        .pick-row .p-body { flex:1; min-width:0; }
        .pick-row .p-name { font-weight:600; color:#1a1a1a; }
        .pick-row .p-meta { font-size:11px; color:#999; }
        .pick-row .p-tag { font-size:10px; font-weight:700; background:#fef3c7; color:#b45309; border-radius:10px; padding:2px 8px; white-space:nowrap; }
        .pick-row .p-tag.none { background:#e0f2fe; color:#0369a1; }
        .pick-empty { padding:22px; text-align:center; color:#999; font-size:12.5px; }
        .add-hand { display:inline-flex; align-items:center; gap:8px; margin-top:12px; padding:8px 14px; border:1.5px dashed #ddd; border-radius:10px; color:#555; font-size:12.5px; font-weight:500; text-decoration:none; }
        .add-hand:hover { border-color:var(--primary); color:var(--primary); }
        .m-error { color:#b91c1c; font-size:12px; margin-top:8px; }
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 16px 10px 0;
            border-top: 1px solid #f5f5f5;
            margin-top: 4px;
        }
        .pagination-info {
            font-size: 11px;
            color: #999;
        }
        .page-links {
            display: flex;
            gap: 5px;
        }
        .page-btn {
            min-width: 30px;
            height: 30px;
            padding: 0 8px;
            border: 1px solid #e5e7eb;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #555;
            font-size: 11px;
        }
        .page-btn.active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }
        .page-btn.disabled {
            opacity: .4;
            pointer-events: none;
        }
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            width: 100%;
            max-width: 520px;
        }
        .modal h3 {
            font-size: 16px;
            margin-bottom: 4px;
        }
        .modal-sub {
            font-size: 11px;
            color: #999;
            line-height: 1.6;
            margin-bottom: 16px;
        }
        .upload-box {
            border: 2px dashed #ddd;
            border-radius: 11px;
            padding: 22px;
            text-align: center;
            background: #fafafa;
        }
        .upload-box i {
            display: block;
            font-size: 25px;
            color: var(--primary);
            margin-bottom: 9px;
        }
        .upload-box input {
            width: 100%;
            font-size: 11px;
        }
        .template-link {
            display: inline-flex;
            margin-top: 10px;
            font-size: 11px;
            color: var(--primary);
            text-decoration: none;
        }
        .template-link i { margin-right: 6px; }
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 9px;
            margin-top: 18px;
        }
        .import-errors {
            background: #fff7ed;
            color: #9a3412;
            padding: 11px 14px;
            border-radius: 9px;
            margin-bottom: 16px;
            font-size: 11px;
        }
        .import-errors ul {
            padding-left: 18px;
            margin-top: 5px;
        }
        .otp-cell { display: flex; align-items: center; gap: 6px; margin-top: 4px; }
        .temp-pass { font-family: 'Courier New', monospace; font-weight: 700; color: #7B1D1D; background: #f8eaea; padding: 2px 8px; border-radius: 6px; font-size: 11px; letter-spacing: .5px; }
        .otp-reveal, .copy-mini { background: none; border: none; color: #bbb; cursor: pointer; font-size: 11px; }
        .otp-reveal:hover, .copy-mini:hover { color: var(--primary); }
        @media (max-width: 1180px) {
            .topbar { flex-wrap: wrap; gap: 12px; }
            .topbar-right { flex-wrap: wrap; }
            .topbar-right .btn { white-space: nowrap; }
            .sec-divider { display: none; }
        }
        @media (max-width: 768px) {
            .lf-search { max-width:none; flex-basis:100%; }
            .list-filters select { flex:1; min-width:0; }
            .pagination {
                flex-direction: column;
                align-items: flex-start;
            }
            .topbar-right {
                width: 100%;
                justify-content: flex-end;
            }
            .topbar-right .btn span { display: none; }
            .sec-grid { grid-template-columns: minmax(0, 1fr); }
        }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'students'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Student Management</div>
                <div class="page-sub">
                    Enrollment, sections, and readiness monitoring.
                </div>
            </div>
        </div>
        <div class="topbar-right">
            <a class="btn btn-primary" href="{{ route('chair.students.create') }}">
                <i class="fas fa-user-plus"></i><span>Enroll Student</span>
            </a>
            <a class="btn btn-outline" href="{{ route('chair.students.import.form') }}">
                <i class="fas fa-users"></i><span>Bulk Enroll</span>
            </a>
            @include('partials.topbar-actions')
        </div>
    </div>

    {{-- Feedback messages (status, errors, skipped import rows) surface as
         SweetAlert popups via partials.alerts --}}

    <!-- Roster summary -->
    @php
        $activePct = $stats['total'] > 0 ? (int) round($stats['active'] / $stats['total'] * 100) : 0;
        $atRiskPct = $stats['total'] > 0 ? (int) round($stats['at_risk'] / $stats['total'] * 100) : 0;
        $readinessGap = $stats['average'] - 75;
        $listUrl = fn (array $query = []) => route('chair.students', $query) . '#studentList';
        $summaryCards = [
            [
                'value' => $stats['total'], 'label' => 'Enrolled Students',
                'tone' => 't-rose', 'icon' => 'fa-users', 'href' => $listUrl(),
                'context' => 'Across ' . $years->count() . ' year level' . ($years->count() === 1 ? '' : 's') . ' and ' . $sections->count() . ' section' . ($sections->count() === 1 ? '' : 's') . '.',
            ],
            [
                'value' => $stats['active'], 'label' => 'Active Students',
                'tone' => 't-green', 'icon' => 'fa-circle-check', 'href' => $listUrl(['status' => 'active']),
                'context' => '<strong style="color:#059669;">' . $activePct . '%</strong> of enrolled students.',
            ],
            [
                'value' => $stats['average'] . '%', 'label' => 'Average Readiness',
                'tone' => 't-amber', 'icon' => 'fa-chart-simple', 'href' => $listUrl(['sort' => 'score_asc']),
                'context' => $readinessGap >= 0
                    ? '<strong style="color:#059669;">+' . $readinessGap . ' pts</strong> above the 75% benchmark.'
                    : '<strong style="color:var(--accent);">' . $readinessGap . ' pts</strong> below the 75% benchmark.',
            ],
            [
                'value' => $stats['at_risk'], 'label' => 'Need Intervention',
                'tone' => 't-red', 'icon' => 'fa-triangle-exclamation', 'href' => $listUrl(['status' => 'at_risk']),
                'context' => $stats['at_risk'] > 0
                    ? '<strong style="color:var(--accent);">' . $atRiskPct . '%</strong> of enrolled students need support.'
                    : '<strong style="color:#059669;">No students</strong> currently need intervention.',
            ],
        ];

        $catalogColl = collect($sectionCatalog);
        $activeSectionFilter = $filters['section'];
        $focusedSection = $catalogColl->firstWhere('name', $activeSectionFilter);
        $noSectionValue = \App\Http\Controllers\Chair\StudentManagementController::NO_SECTION;
        $sectionsByYear = $catalogColl->groupBy(fn ($s) => $s->year_level ?: 0)->sortKeys();
        $yearLabels = \App\Models\Section::YEAR_LABELS;
        $sectionUrl = fn ($value) => request()->fullUrlWithQuery(['section' => $value, 'page' => null]);
        $showList = $activeSectionFilter || $filters['search'] !== '' || filled($filters['year'])
            || filled($filters['status']) || $filters['sort'] !== 'name'
            || request('view') === 'list' || request()->filled('page');
    @endphp
    <div class="kpi-row">
        @foreach ($summaryCards as $card)
            <a class="kpi" href="{{ $card['href'] }}">
                <div class="kpi-top">
                    <div class="kpi-icon {{ $card['tone'] }}"><i class="fas {{ $card['icon'] }}"></i></div>
                    <div>
                        <div class="kpi-num">{{ $card['value'] }}</div>
                        <div class="kpi-lbl">{{ $card['label'] }}</div>
                    </div>
                </div>
                <div class="kpi-foot">
                    <span>{!! $card['context'] !!}</span>
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
        @endforeach
    </div>

    @if ($showList)
        <a class="crumb" href="{{ route('chair.students') }}"><i class="fas fa-arrow-left"></i> Back to sections</a>
    @else
        <!-- Sections -->
        <div class="sec-header">
            <div>
                <div class="sec-h-title">Sections</div>
                <div class="sec-h-sub">Manage your sections and view student progress.</div>
            </div>
            <div class="sec-toolbar">
                <form class="sec-search" method="GET" action="{{ route('chair.students') }}#studentList">
                    <i class="fas fa-search"></i>
                    <input type="hidden" name="view" value="list">
                    <input type="search" name="search" placeholder="Search students..." aria-label="Search students">
                </form>
                <select id="secYear" onchange="filterSectionCards()">
                    <option value="">All Year Levels</option>
                    @foreach ($catalogColl->pluck('year_level')->filter()->unique()->sort() as $yl)
                        <option value="{{ $yl }}">{{ $yearLabels[$yl] ?? 'Year ' . $yl }}</option>
                    @endforeach
                </select>
                <select id="secStatus" onchange="filterSectionCards()">
                    <option value="">All Statuses</option>
                    <option value="at_risk">Has At-Risk Students</option>
                    <option value="below">Below Target (&lt; 75%)</option>
                    <option value="on_target">On Target (75%+)</option>
                    <option value="no_data">No Quiz Data Yet</option>
                    <option value="no_faculty">No Faculty Assigned</option>
                    <option value="inactive">Inactive Sections</option>
                </select>
                <button type="button" class="sec-clear" id="secClear" onclick="clearSectionFilters()" title="Clear all filters">
                    <i class="fas fa-xmark"></i> Clear <span class="sec-clear-n" id="secClearCount">0</span>
                </button>
                <span class="sec-divider"></span>
                <a class="sec-link" href="{{ route('chair.students', ['view' => 'list']) }}#studentList">
                    <i class="fas fa-list-ul"></i> All students
                </a>
                <button type="button" class="btn btn-primary" onclick="openSectionForm()">
                    <i class="fas fa-plus"></i> New Section
                </button>
            </div>
        </div>

        <div class="sec-result" id="secResult"></div>
        <div class="sc-grid" id="sectionCards">
            @foreach ($catalogColl as $sec)
                @php
                    $ready = $sectionStats[$sec->name]['readiness'] ?? null;
                    $risk = $sectionStats[$sec->name]['at_risk'] ?? 0;
                @endphp
                <div class="sc {{ $sec->is_active ? '' : 'is-inactive' }}"
                     data-year="{{ $sec->year_level }}" data-status="{{ $sec->is_active ? 'active' : 'inactive' }}"
                     data-ready="{{ $ready }}" data-risk="{{ $risk }}" data-faculty="{{ $sec->faculty_count }}">
                    <div class="sc-head">
                        <div class="sc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.42 10.92a1 1 0 0 0-.02-1.84L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.83l8.57 3.91a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/></svg></div>
                        <div class="sc-title">
                            <div class="sc-name">{{ $sec->name }}</div>
                            {{-- Semester is read from the name (BSA 3101 → 1st Sem); older names like BSA-3A show none. --}}
                            @php
                                $meta = collect([
                                    $sec->year_level ? ($yearLabels[$sec->year_level] ?? 'Year ' . $sec->year_level) : null,
                                    $sec->semester_label,
                                    $sec->student_count . ' Student' . ($sec->student_count === 1 ? '' : 's'),
                                ])->filter()->implode(' · ');
                            @endphp
                            <div class="sc-meta" title="{{ $meta }}">{{ $meta }}</div>
                        </div>
                        @unless ($sec->is_active)<span class="sc-badge">Inactive</span>@endunless
                        <div class="sc-menu">
                            <button type="button" class="sc-dots" onclick="toggleSectionMenu(event, this)" aria-label="Section options"><i class="fas fa-ellipsis"></i></button>
                            <div class="sc-dropdown">
                                <button type="button" onclick="openSectionForm({{ json_encode(['id' => $sec->id, 'name' => $sec->name, 'year_level' => $sec->year_level, 'parts' => \App\Models\Section::parseName($sec->name)]) }})">
                                    <i class="fas fa-pen-to-square"></i> Edit section
                                </button>
                                <form method="POST" action="{{ route('chair.sections.toggle', $sec->id) }}"
                                      data-confirm="{{ $sec->is_active
                                          ? 'This section will no longer be selectable when assigning faculty or students.'
                                          : 'This section will become selectable again when assigning faculty or students.' }}"
                                      data-confirm-title="{{ $sec->is_active ? 'Deactivate this section?' : 'Activate this section?' }}"
                                      data-confirm-ok="{{ $sec->is_active ? 'Yes, deactivate' : 'Yes, activate' }}"
                                      data-confirm-icon="question">
                                    @csrf
                                    <button type="submit"><i class="fas fa-power-off"></i> {{ $sec->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                                <form method="POST" action="{{ route('chair.sections.destroy', $sec->id) }}"
                                      data-confirm="{{ $sec->student_count > 0 || $sec->faculty_count > 0
                                          ? 'This section still has ' . $sec->student_count . ' student(s) and ' . $sec->faculty_count . ' faculty assignment(s) tied to it — it cannot be removed until those are reassigned.'
                                          : 'This will permanently remove "' . $sec->name . '" from the catalog. This cannot be undone.' }}"
                                      data-confirm-title="Delete this section?"
                                      data-confirm-ok="Yes, delete"
                                      data-confirm-icon="warning"
                                      data-confirm-danger>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="danger"><i class="fas fa-trash-can"></i> Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="sc-body">
                        <div class="sc-label">Board Readiness</div>
                        <div class="sc-readiness">
                            <span class="sc-pct">{{ $ready === null ? '—' : $ready . '%' }}</span>
                            <div class="sc-bar"><span style="width:{{ $ready ?? 0 }}%;"></span></div>
                        </div>
                        @if ($ready === null)
                            <div class="sc-note muted">No quiz results yet</div>
                        @elseif ($sec->is_active && $sec->faculty_count === 0)
                            <div class="sc-note"><i class="fas fa-chalkboard-user"></i> No faculty assigned yet</div>
                        @endif
                    </div>

                    <a class="sc-cta" href="{{ $sectionUrl($sec->name) }}#studentList">View Students <i class="fas fa-arrow-right"></i></a>
                </div>
            @endforeach

            @if ($noSectionCount > 0)
                <div class="sc is-unassigned" data-kind="unassigned" data-year="" data-status="active">
                    <div class="sc-head">
                        <div class="sc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg></div>
                        <div class="sc-title">
                            <div class="sc-name">Not in a Section</div>
                            <div class="sc-meta">{{ $noSectionCount }} Student{{ $noSectionCount === 1 ? '' : 's' }}</div>
                        </div>
                    </div>
                    <div class="sc-body">
                        <div class="sc-alert">
                            <i class="fas fa-circle-exclamation"></i>
                            <span>{{ $noSectionCount }} {{ $noSectionCount === 1 ? 'student needs' : 'students need' }} to be assigned to a section.</span>
                        </div>
                    </div>
                    <a class="sc-cta" href="{{ $sectionUrl($noSectionValue) }}#studentList">Assign Students <i class="fas fa-arrow-right"></i></a>
                </div>
            @endif

            <div class="sc-empty" id="sectionCardsEmpty" style="{{ $catalogColl->isEmpty() ? '' : 'display:none;' }}">
                <i class="fas fa-layer-group"></i>
                <div id="sectionCardsEmptyText">No sections yet. Click <strong>New Section</strong> to create the first one.</div>
            </div>
        </div>
    @endif

    @if ($showList)
    <!-- Student roster -->
    {{-- A standalone form (kept outside the table) that the "Mark as Alumni"
         button fills with the checked ids and submits. It can't wrap the
         table itself: each row already has its own <form> for the
         enable/disable toggle, and HTML doesn't allow forms to nest. --}}
    <form id="bulkAlumniForm" method="POST" action="{{ route('chair.students.bulk-alumni') }}">
        @csrf
    </form>
    <div class="card" id="studentList">
        <div class="card-head">
            @if ($focusedSection)
                <div class="section-focus">
                    <span class="card-title">{{ $focusedSection->name }} ({{ $students->total() }})</span>
                    @if ($focusedSection->year_level)
                        <span class="year-pill">{{ $yearLabels[$focusedSection->year_level] ?? 'Year ' . $focusedSection->year_level }}</span>
                    @endif
                    <span class="student-meta" style="margin:0;"><i class="fas fa-chalkboard-user"></i> {{ $focusedSection->faculty_count }} faculty</span>
                </div>
            @elseif ($activeSectionFilter === $noSectionValue)
                <span class="card-title">Not in a section ({{ $students->total() }})</span>
            @else
                <span class="card-title">All Students ({{ $students->total() }})</span>
            @endif
            <div style="display:flex;gap:7px;align-items:center;flex-wrap:wrap;justify-content:flex-end;">
                <span id="bulkSelectedCount" class="student-meta" style="display:none;"></span>
                <button type="button" id="bulkSectionBtn" class="btn btn-primary btn-sm" style="display:none;">
                    <i class="fas fa-people-arrows"></i> Assign to Section
                </button>
                <button type="button" id="bulkAlumniBtn" class="btn btn-outline btn-sm" style="display:none;">
                    <i class="fas fa-user-graduate"></i> Mark as Alumni
                </button>
                @if ($focusedSection)
                    <button type="button" class="btn btn-plain btn-sm"
                            onclick="openSectionForm({{ json_encode(['id' => $focusedSection->id, 'name' => $focusedSection->name, 'year_level' => $focusedSection->year_level, 'parts' => \App\Models\Section::parseName($focusedSection->name)]) }})">
                        <i class="fas fa-pen-to-square"></i> Edit section
                    </button>
                    @if ($focusedSection->is_active)
                        <button type="button" class="btn btn-primary btn-sm" onclick="openAddStudents()">
                            <i class="fas fa-user-plus"></i> Add students
                        </button>
                    @endif
                @endif
                <a href="{{ route('chair.students.export.excel', request()->query()) }}" class="btn btn-ghost btn-sm" title="Download as an Excel report">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('chair.students.export.pdf', request()->query()) }}" class="btn btn-ghost btn-sm" title="Download as a PDF report">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>

        <!-- Search, status, and sort for the list -->
        @php
            $listFiltered = $filters['search'] !== '' || filled($filters['status']) || $filters['sort'] !== 'name';
            $clearUrl = route('chair.students', array_filter(['view' => 'list', 'section' => $activeSectionFilter, 'year' => $filters['year']])) . '#studentList';
        @endphp
        <form class="list-filters" method="GET" action="{{ route('chair.students') }}#studentList">
            <input type="hidden" name="view" value="list">
            @if ($activeSectionFilter)<input type="hidden" name="section" value="{{ $activeSectionFilter }}">@endif
            @if (filled($filters['year']))<input type="hidden" name="year" value="{{ $filters['year'] }}">@endif
            <div class="lf-search">
                <i class="fas fa-search"></i>
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Search name, email, or student no." aria-label="Search students">
            </div>
            <select name="status" onchange="this.form.submit()" class="{{ filled($filters['status']) ? 'is-set' : '' }}" aria-label="Status">
                <option value="">All statuses</option>
                <option value="at_risk" @selected($filters['status'] === 'at_risk')>At Risk</option>
                <option value="low_score" @selected($filters['status'] === 'low_score')>Low Scores</option>
                <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive 7+ Days</option>
                <option value="setup_pending" @selected($filters['status'] === 'setup_pending')>Setup Pending</option>
                <option value="active" @selected($filters['status'] === 'active')>Active Accounts</option>
                <option value="disabled" @selected($filters['status'] === 'disabled')>Disabled Accounts</option>
            </select>
            <select name="sort" onchange="this.form.submit()" class="{{ $filters['sort'] !== 'name' ? 'is-set' : '' }}" aria-label="Sort by">
                <option value="name" @selected($filters['sort'] === 'name')>Sort: Name</option>
                <option value="score_asc" @selected($filters['sort'] === 'score_asc')>Sort: Lowest readiness</option>
                <option value="score_desc" @selected($filters['sort'] === 'score_desc')>Sort: Highest readiness</option>
                <option value="recent" @selected($filters['sort'] === 'recent')>Sort: Recently active</option>
            </select>
            @if ($listFiltered)
                <a class="lf-clear" href="{{ $clearUrl }}"><i class="fas fa-xmark"></i> Clear</a>
            @endif
        </form>

        {{-- Shown once every checkbox on the current page is ticked, so a
             filtered result that spans several pages can be acted on in one
             go instead of page by page. --}}
        <div id="selectAllBar" style="display:none;padding:8px 24px;background:#fdf6e3;border-bottom:1px solid #f0e4bd;font-size:12.5px;color:#7a5c00;">
            All <strong id="selectAllBarPageCount"></strong> students on this page are selected.
            <button type="button" id="selectAllMatchingBtn" style="background:none;border:0;padding:0;color:#7B1D1D;font-weight:700;font-size:12.5px;cursor:pointer;text-decoration:underline;">
                Select all <span id="selectAllBarTotal"></span> students matching your filters
            </button>
        </div>
        <div id="selectAllActiveBar" style="display:none;padding:8px 24px;background:#fdeceb;border-bottom:1px solid #f5cdc9;font-size:12.5px;color:#8d2b22;">
            All <strong id="selectAllActiveTotal"></strong> students matching your filters are selected.
            <button type="button" id="clearSelectAllBtn" style="background:none;border:0;padding:0;color:#7B1D1D;font-weight:700;font-size:12.5px;cursor:pointer;text-decoration:underline;">
                Clear selection
            </button>
        </div>
        <div class="table-wrap">
            <table class="roster-table">
                {{-- Fixed column widths so every row lines up the same way. --}}
                <colgroup>
                    <col style="width:46px;">
                    <col>
                    <col style="width:120px;">
                    <col style="width:130px;">
                    <col style="width:96px;">
                    <col style="width:96px;">
                    <col style="width:130px;">
                    <col style="width:170px;">
                    <col style="width:84px;">
                </colgroup>
                <thead>
                    <tr>
                        <th class="col-check"><input type="checkbox" id="selectAllStudents" title="Select all on this page"></th>
                        <th>Student</th>
                        <th>Section</th>
                        <th>Readiness</th>
                        <th>Quizzes</th>
                        <th>Streak</th>
                        <th>Last Active</th>
                        <th>Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        @php
                            $scoreColor = match (true) {
                                $student['score'] === null => '#aaa',
                                $student['score'] >= 75 => '#059669',
                                $student['score'] >= 60 => '#d97706',
                                default => '#c0392b',
                            };
                        @endphp
                        <tr>
                            <td>
                                @unless ($student['is_alumni'])
                                    <input type="checkbox" class="student-select" name="student_ids[]" value="{{ $student['id'] }}">
                                @endunless
                            </td>
                            <td>
                                <div class="student-cell">
                                    <div class="user-av">{{ $student['initials'] }}</div>
                                    <div>
                                        <div class="student-name">{{ $student['name'] }}</div>
                                        <div class="student-meta">
                                            {{ $student['student_number'] ?: 'No student number' }}
                                            &bull; {{ $student['email'] }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($student['section'])
                                    <span class="group-tag">{{ $student['section'] }}</span>
                                @else
                                    <span class="ungrouped">No section</span>
                                @endif
                            </td>
                            <td>
                                <div class="score" style="color:{{ $scoreColor }};">
                                    {{ $student['score'] === null ? 'Not rated' : $student['score'].'%' }}
                                </div>
                                <div class="score-bar">
                                    <span style="width:{{ $student['score'] ?? 0 }}%;background:{{ $scoreColor }};"></span>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $student['quizzes'] }}</strong>
                                <div class="student-meta">{{ $student['attempted'] }} items</div>
                            </td>
                            <td>
                                <i class="fas fa-fire" style="color:#f59e0b;"></i>
                                {{ $student['streak'] }} day{{ $student['streak'] === 1 ? '' : 's' }}
                            </td>
                            <td style="font-size:11px;color:#666;">
                                {{ $student['last_active'] ? $student['last_active']->diffForHumans() : 'Never' }}
                            </td>
                            <td>
                                @if (! $student['is_active'])
                                    <span class="pill pill-off">Disabled</span>
                                @elseif (! $student['setup_completed'])
                                    <span class="pill pill-pending" title="Student hasn't completed first-login Account Setup yet">
                                        <i class="fas fa-hourglass-half"></i> Setup Pending
                                    </span>
                                    <div class="otp-cell">
                                        <form method="POST" action="{{ route('chair.students.regenerate-otp', $student['id']) }}"
                                              data-confirm="A new one-time password will be emailed to {{ $student['name'] }} ({{ $student['email'] }}). Any password they were given earlier stops working."
                                              data-confirm-title="Resend one-time password?"
                                              data-confirm-ok="Yes, resend"
                                              data-confirm-icon="question">
                                            @csrf
                                            <button type="submit" class="otp-reveal" title="Email a fresh one-time password to this student">
                                                <i class="fas fa-paper-plane"></i> Resend OTP
                                            </button>
                                        </form>
                                        @if ($student['temp_password'])
                                            <span class="temp-pass" title="Manual fallback if the email never arrives">{{ $student['temp_password'] }}</span>
                                            <button type="button" class="copy-mini" title="Copy" onclick="navigator.clipboard.writeText('{{ $student['temp_password'] }}')"><i class="fas fa-copy"></i></button>
                                        @endif
                                    </div>
                                @elseif ($student['at_risk'])
                                    <span class="risk-pill">
                                        <i class="fas fa-triangle-exclamation"></i> At Risk
                                    </span>
                                @else
                                    <span class="pill pill-on"><i class="fas fa-check"></i> On Track</span>
                                @endif
                            </td>
                            <td class="col-actions">
                                <div class="row-menu">
                                    <button type="button" class="row-dots" onclick="toggleRowMenu(event, this)" aria-label="Actions for {{ $student['name'] }}">
                                        <i class="fas fa-ellipsis"></i>
                                    </button>
                                    <div class="row-dropdown">
                                        <a href="{{ route('chair.students.show', $student['id']) }}"><i class="fas fa-chart-line"></i> View performance</a>
                                        <a href="{{ route('chair.students.edit', $student['id']) }}"><i class="fas fa-pen-to-square"></i> Edit account</a>
                                        @unless ($student['is_alumni'] || $student['is_shifted'])
                                            <button type="button" onclick='openAssign([{{ $student['id'] }}], @json($student['name']), @json($student['section']))'>
                                                <i class="fas fa-people-arrows"></i> Assign to section
                                            </button>
                                        @endunless
                                        <form
                                            method="POST"
                                            action="{{ route('chair.students.toggle', $student['id']) }}"
                                            data-confirm="{{ $student['is_active']
                                                ? $student['name'] . ' will be disabled and can no longer sign in to CPACE.'
                                                : $student['name'] . ' will be re-enabled and can sign in to CPACE again.' }}"
                                            data-confirm-title="{{ $student['is_active'] ? 'Disable this account?' : 'Enable this account?' }}"
                                            data-confirm-ok="{{ $student['is_active'] ? 'Yes, disable' : 'Yes, enable' }}"
                                            data-confirm-icon="question"
                                            @if ($student['is_active']) data-confirm-danger @endif
                                        >
                                            @csrf
                                            <button type="submit" class="{{ $student['is_active'] ? 'danger' : '' }}">
                                                <i class="fas fa-power-off"></i> {{ $student['is_active'] ? 'Disable account' : 'Enable account' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty">
                                    <i class="fas fa-user-graduate"></i>
                                    <div>No students match the selected filters.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($students->hasPages())
            <div class="pagination">
                <span class="pagination-info">
                    Showing {{ $students->firstItem() }}&ndash;{{ $students->lastItem() }}
                    of {{ $students->total() }}
                </span>
                <div class="page-links">
                    <a
                        class="page-btn {{ $students->onFirstPage() ? 'disabled' : '' }}"
                        href="{{ $students->previousPageUrl() ?: '#' }}"
                    ><i class="fas fa-chevron-left"></i></a>
                    @for ($page = 1; $page <= $students->lastPage(); $page++)
                        <a
                            class="page-btn {{ $page === $students->currentPage() ? 'active' : '' }}"
                            href="{{ $students->url($page) }}"
                        >{{ $page }}</a>
                    @endfor
                    <a
                        class="page-btn {{ $students->hasMorePages() ? '' : 'disabled' }}"
                        href="{{ $students->nextPageUrl() ?: '#' }}"
                    ><i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
        @endif
    </div>
    @endif
</main>

<!-- CSV import modal -->
<div class="modal-overlay" id="importModal">
    <div class="modal">
        <h3>Import Students from CSV</h3>
        <div class="modal-sub">
            Required: first_name, last_name, email, password.<x-tip label="Optional columns">Optional: student_number, year_level, section, is_active.</x-tip>
        </div>
        <form method="POST" action="{{ route('chair.students.import') }}" enctype="multipart/form-data"
              data-confirm="Every valid row in the file becomes a student account with its own one-time password. Rows with problems are skipped and reported."
              data-confirm-title="Import these students?"
              data-confirm-ok="Yes, import them"
              data-confirm-icon="question"
              data-loading="Importing students...">
            @csrf
            <div class="upload-box">
                <i class="fas fa-cloud-arrow-up"></i>
                <input type="file" name="csv_file" accept=".csv,text/csv" required>
            </div>
            <a href="{{ route('chair.students.template') }}" class="template-link">
                <i class="fas fa-download"></i> Download CSV template
            </a>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeImportModal()">Cancel</button>
                <button class="btn btn-primary">
                    <i class="fas fa-file-import"></i> Import Students
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Assign to section -->
<div class="modal-overlay" id="assignModal">
    <div class="modal m2" style="max-width:600px;">
        <div class="m-head">
            <div class="m-icon"><i class="fas fa-people-arrows"></i></div>
            <div>
                <div class="m-title">Assign to section</div>
                <div class="m-sub" id="assignSub">Pick the section to place the student in.</div>
            </div>
            <button type="button" class="m-close" onclick="closeModal('assignModal')" aria-label="Close"><i class="fas fa-xmark"></i></button>
        </div>
        <form method="POST" id="assignForm" action="{{ route('chair.students.assign-section') }}" data-loading="Updating sections..." style="display:contents;">
            @csrf
            <div id="assignHidden"></div>
            <div class="m-body">
                @foreach ($sectionsByYear as $year => $group)
                    @php $activeInGroup = $group->where('is_active', true); @endphp
                    @if ($activeInGroup->isNotEmpty())
                        <div class="assign-group-label">{{ $year ? ($yearLabels[$year] ?? 'Year ' . $year) : 'No year set' }}</div>
                        <div class="assign-grid">
                            @foreach ($activeInGroup as $sec)
                                <label class="assign-opt" data-section-name="{{ $sec->name }}">
                                    <input type="radio" name="section_id" value="{{ $sec->id }}" required>
                                    <span class="a-name">{{ $sec->name }}</span>
                                    <span class="a-meta">{{ $sec->student_count }} student{{ $sec->student_count === 1 ? '' : 's' }}</span>
                                    <span class="a-current" style="display:none;">Current</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                @endforeach
                <div class="assign-group-label">Other</div>
                <div class="assign-grid">
                    <label class="assign-opt is-remove">
                        <input type="radio" name="section_id" value="" required>
                        <span class="a-name"><i class="fas fa-user-minus"></i> No section</span>
                        <span class="a-meta">Take out of their section</span>
                    </label>
                </div>
            </div>
            <div class="m-foot">
                <button type="button" class="btn btn-plain" onclick="closeModal('assignModal')">Cancel</button>
                <button class="btn btn-primary" id="assignSubmit" disabled>Save</button>
            </div>
        </form>
    </div>
</div>

<!-- New / edit section -->
<div class="modal-overlay" id="sectionFormModal">
    <div class="modal m2" style="max-width:500px;">
        <div class="m-head">
            <div class="m-icon"><i class="fas fa-folder-plus" id="sectionFormIcon"></i></div>
            <div>
                <div class="m-title" id="sectionFormTitle">New section</div>
                <div class="m-sub">Sections group students and their faculty by year level.</div>
            </div>
            <button type="button" class="m-close" onclick="closeModal('sectionFormModal')" aria-label="Close"><i class="fas fa-xmark"></i></button>
        </div>
        <form method="POST" id="sectionForm" action="{{ route('chair.sections.store') }}" style="display:contents;">
            @csrf <input type="hidden" name="_method" id="sectionMethod" value="POST">
            <div class="m-body">
                <div class="sp-old" id="sectionOldName" style="display:none;">
                    <i class="fas fa-circle-info"></i>
                    <span>This section uses an older naming format<span id="sectionOldLabel"></span>. Please select the new section details before saving.</span>
                </div>
                <div class="sec-builder">
                    <div class="form-group">
                        <label for="sectionProgram">Program <span class="req">*</span></label>
                        <input type="text" name="program" id="sectionProgram" maxlength="10" placeholder="BSA" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label for="sectionYear">Year level <span class="req">*</span></label>
                        <select name="year_level" id="sectionYear" required>
                            <option value="">Select</option>
                            @foreach (\App\Models\Section::CODE_YEAR_LEVELS as $value)
                                <option value="{{ $value }}">{{ $yearLabels[$value] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sectionSem">Semester <span class="req">*</span></label>
                        <select name="semester" id="sectionSem" required>
                            <option value="">Select</option>
                            @foreach (\App\Models\Section::SEMESTER_LABELS as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sectionNo">Section no. <span class="req">*</span></label>
                        <input type="text" name="section_number" id="sectionNo" inputmode="numeric" maxlength="2" placeholder="01" autocomplete="off" required>
                    </div>
                </div>
                <div class="sec-preview">
                    <div class="sp-label">Section name</div>
                    <div class="sp-name" id="sectionPreview">—</div>
                    <div class="sp-legend" id="sectionLegend">Fill in the fields above.</div>
                    <div class="sp-error" id="sectionDuplicate" style="display:none;"></div>
                </div>
            </div>
            <div class="m-foot">
                <button type="button" class="btn btn-plain" onclick="closeModal('sectionFormModal')">Cancel</button>
                <button class="btn btn-primary" id="sectionFormSubmit">Create section</button>
            </div>
        </form>
    </div>
</div>

@if ($focusedSection)
<!-- Add students to the section being viewed -->
<div class="modal-overlay" id="addStudentsModal">
    <div class="modal m2" style="max-width:720px;">
        <div class="m-head">
            <div class="m-icon"><i class="fas fa-users"></i></div>
            <div>
                <div class="m-title">Add students to {{ $focusedSection->name }}</div>
                <div class="m-sub">Import a class list, pick students already enrolled, or add one by hand.</div>
            </div>
            <button type="button" class="m-close" onclick="closeModal('addStudentsModal')" aria-label="Close"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="m-body">
            <div class="drop-box">
                <i class="fas fa-file-arrow-up big"></i>
                <div class="db-title">Import a CSV class list</div>
                <div class="db-sub">New accounts are created and placed in {{ $focusedSection->name }}. You can review every row before enrolling.</div>
                <a class="btn btn-primary" href="{{ route('chair.students.import.form', ['section' => $focusedSection->name]) }}">Choose file</a>
            </div>

            <div class="pick-head">
                <span>Already-enrolled students <span class="muted" id="pickCount">(0)</span></span>
                <label style="font-size:12px;font-weight:500;color:#666;cursor:pointer;"><input type="checkbox" id="pickAll" style="accent-color:var(--primary);"> Select all shown</label>
            </div>
            <div class="pick-tools">
                <input type="search" id="pickSearch" placeholder="Search by name, email or student number…">
                <select id="pickFrom">
                    <option value="">From any section</option>
                    <option value="{{ $noSectionValue }}">Not in a section</option>
                </select>
            </div>
            <div class="pick-list" id="pickList"><div class="pick-empty">Loading…</div></div>
            <div class="m-error" id="pickError"></div>

            <a class="add-hand" href="{{ route('chair.students.create', ['section' => $focusedSection->name]) }}">
                <i class="fas fa-plus"></i> Add a student by hand
            </a>
        </div>
        <div class="m-foot">
            <button type="button" class="btn btn-plain" onclick="closeModal('addStudentsModal')">Cancel</button>
            <button type="button" class="btn btn-primary" id="pickSubmit" disabled>Add to {{ $focusedSection->name }}</button>
        </div>
    </div>
</div>
@endif

<script>
    const importModal = document.getElementById('importModal');

    function openImportModal() {
        importModal.classList.add('open');
    }

    function closeImportModal() {
        importModal.classList.remove('open');
    }

    importModal.addEventListener('click', event => {
        if (event.target === importModal) closeImportModal();
    });

    // ── Bulk "Mark as Alumni" ───────────────────────────────────────────────
    // Alumni rows have no checkbox (they're already Alumni), so this only
    // ever selects students who can still be marked. The header checkbox can
    // only reach rows on the current page, so once every one of those is
    // ticked we surface a "select all N matching your filters" prompt; that
    // mode is submitted as select_all=1 + the active filters, and the
    // server re-resolves the full matching set instead of trusting ids.
    const selectAll = document.getElementById('selectAllStudents');
    const rowChecks = () => Array.from(document.querySelectorAll('.student-select'));
    const bulkBtn = document.getElementById('bulkAlumniBtn');
    const bulkSectionBtn = document.getElementById('bulkSectionBtn');
    const bulkCount = document.getElementById('bulkSelectedCount');
    const bulkForm = document.getElementById('bulkAlumniForm');
    const selectAllBar = document.getElementById('selectAllBar');
    const selectAllBarPageCount = document.getElementById('selectAllBarPageCount');
    const selectAllBarTotal = document.getElementById('selectAllBarTotal');
    const selectAllMatchingBtn = document.getElementById('selectAllMatchingBtn');
    const selectAllActiveBar = document.getElementById('selectAllActiveBar');
    const selectAllActiveTotal = document.getElementById('selectAllActiveTotal');
    const clearSelectAllBtn = document.getElementById('clearSelectAllBtn');
    const totalMatching = {{ (int) $students->total() }};
    let selectAllMode = false;

    function exitSelectAllMode() {
        selectAllMode = false;
        selectAllActiveBar.style.display = 'none';
    }

    function syncBulkBar() {
        const checked = rowChecks().filter(c => c.checked);
        const all = rowChecks();
        if (selectAll) {
            selectAll.checked = all.length > 0 && checked.length === all.length;
            selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
        }

        // Offer "select all matching" only when this page doesn't already
        // hold every matching student, and the page really is fully ticked.
        const pageIsFull = all.length > 0 && checked.length === all.length;
        if (pageIsFull && all.length < totalMatching && !selectAllMode) {
            selectAllBarPageCount.textContent = all.length;
            selectAllBarTotal.textContent = totalMatching;
            selectAllBar.style.display = '';
        } else {
            selectAllBar.style.display = 'none';
        }

        if (selectAllMode && checked.length === all.length && all.length > 0) {
            bulkBtn.style.display = '';
            bulkCount.style.display = '';
            bulkCount.textContent = totalMatching + ' selected (all matching filters)';
        } else if (checked.length > 0) {
            exitSelectAllMode();
            bulkBtn.style.display = '';
            bulkCount.style.display = '';
            bulkCount.textContent = checked.length + ' selected';
        } else {
            exitSelectAllMode();
            bulkBtn.style.display = 'none';
            bulkCount.style.display = 'none';
        }
        bulkSectionBtn.style.display = bulkBtn.style.display;
    }

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            rowChecks().forEach(c => { c.checked = selectAll.checked; });
            if (!selectAll.checked) exitSelectAllMode();
            syncBulkBar();
        });
    }
    document.addEventListener('change', event => {
        if (event.target.classList && event.target.classList.contains('student-select')) syncBulkBar();
    });

    if (selectAllMatchingBtn) {
        selectAllMatchingBtn.addEventListener('click', () => {
            selectAllMode = true;
            selectAllBar.style.display = 'none';
            selectAllActiveTotal.textContent = totalMatching;
            selectAllActiveBar.style.display = '';
            bulkBtn.style.display = '';
            bulkCount.style.display = '';
            bulkCount.textContent = totalMatching + ' selected (all matching filters)';
            bulkSectionBtn.style.display = '';
        });
    }

    if (clearSelectAllBtn) {
        clearSelectAllBtn.addEventListener('click', () => {
            exitSelectAllMode();
            rowChecks().forEach(c => { c.checked = false; });
            syncBulkBar();
        });
    }

    if (bulkBtn) {
        bulkBtn.addEventListener('click', async () => {
            const ids = rowChecks().filter(c => c.checked).map(c => c.value);
            const useSelectAll = selectAllMode;
            if (!useSelectAll && !ids.length) return;

            const ok = await CPACE.confirm({
                title: 'Mark as Alumni?',
                text: useSelectAll
                    ? totalMatching + ' students matching your filters will be marked as Alumni.'
                    : (ids.length === 1
                        ? 'This student will be marked as Alumni.'
                        : ids.length + ' students will be marked as Alumni.'),
                confirmText: 'Yes, mark as Alumni',
                icon: 'question',
            });
            if (!ok) return;

            bulkForm.querySelectorAll('input[name="student_ids[]"], input[name="select_all"], input[name="search"], input[name="year"], input[name="section"], input[name="status"]').forEach(el => el.remove());

            if (useSelectAll) {
                const flag = document.createElement('input');
                flag.type = 'hidden';
                flag.name = 'select_all';
                flag.value = '1';
                bulkForm.appendChild(flag);

                const params = new URLSearchParams(window.location.search);
                ['search', 'year', 'section', 'status'].forEach(key => {
                    const value = params.get(key);
                    if (value === null || value === '') return;
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    bulkForm.appendChild(input);
                });

                CPACE.loading('Marking students as Alumni...');
                bulkForm.submit();
                return;
            }

            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'student_ids[]';
                input.value = id;
                bulkForm.appendChild(input);
            });
            CPACE.loading('Marking students as Alumni...');
            bulkForm.submit();
        });
    }

    // ── Sections ────────────────────────────────────────────────────────────
    function openModal(id) { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }
    document.querySelectorAll('.modal-overlay').forEach(modal => modal.addEventListener('click', event => {
        if (event.target === modal) modal.classList.remove('open');
    }));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(modal => modal.classList.remove('open'));
    });

    function openSectionForm(section = null) {
        const form = document.getElementById('sectionForm');
        document.getElementById('sectionFormTitle').textContent = section ? 'Edit section' : 'New section';
        document.getElementById('sectionFormSubmit').textContent = section ? 'Save changes' : 'Create section';
        document.getElementById('sectionFormIcon').className = 'fas ' + (section ? 'fa-pen-to-square' : 'fa-folder-plus');
        form.action = section ? `/chair/sections/${section.id}` : @json(route('chair.sections.store'));
        document.getElementById('sectionMethod').value = section ? 'PUT' : 'POST';
        // Pre-fill from the parts the server parsed out of the name. An older
        // name (BSA-3A) has no parts: keep its program/year but leave the
        // semester and section number for the chair to choose.
        const parts = section?.parts ?? null;
        const isOldFormat = !!section && !parts;
        editingSectionName = section?.name ?? null;
        document.getElementById('sectionProgram').value = parts?.program
            ?? (section?.name.match(/^[A-Za-z]{2,10}/)?.[0].toUpperCase() ?? DEFAULT_PROGRAM);
        const year = parts?.year_level ?? section?.year_level ?? '';
        document.getElementById('sectionYear').value = CODE_YEARS.includes(Number(year)) ? String(year) : '';
        document.getElementById('sectionSem').value = parts ? String(parts.semester) : '';
        document.getElementById('sectionNo').value = parts ? pad2(parts.section_number) : '';
        document.getElementById('sectionOldName').style.display = isOldFormat ? '' : 'none';
        document.getElementById('sectionOldLabel').textContent = isOldFormat ? ` ("${section.name}")` : '';
        updateSectionPreview();
        openModal('sectionFormModal');
        document.getElementById(isOldFormat ? 'sectionSem' : (section ? 'sectionNo' : 'sectionProgram')).focus();
    }
    const DEFAULT_PROGRAM = @json($catalogColl->map(fn ($s) => \App\Models\Section::parseName($s->name)['program'] ?? null)->filter()->countBy()->sortDesc()->keys()->first() ?? 'BSA');
    const CODE_YEARS = @json(\App\Models\Section::CODE_YEAR_LEVELS);
    const YEAR_LABELS_JS = @json($yearLabels);
    const SEM_LABELS_JS = @json(\App\Models\Section::SEMESTER_LABELS);
    const EXISTING_SECTION_NAMES = @json($catalogColl->pluck('name')->map(fn ($n) => mb_strtolower($n))->values());
    let editingSectionName = null;
    const pad2 = n => String(n).padStart(2, '0');

    function updateSectionPreview() {
        const programEl = document.getElementById('sectionProgram');
        const noEl = document.getElementById('sectionNo');
        // Letters only for the program, digits only for the section number.
        // (Only rewritten when it changes, so the caret doesn't jump.)
        const cleanProgram = programEl.value.replace(/[^A-Za-z]/g, '').toUpperCase();
        if (cleanProgram !== programEl.value) programEl.value = cleanProgram;
        const cleanNo = noEl.value.replace(/\D/g, '').slice(0, 2);
        if (cleanNo !== noEl.value) noEl.value = cleanNo;

        const program = programEl.value;
        const year = document.getElementById('sectionYear').value;
        const sem = document.getElementById('sectionSem').value;
        const no = Number(noEl.value);
        const complete = program.length >= 2 && year !== '' && sem !== '' && no >= 1 && no <= 99;
        const name = complete ? `${program} ${year}${sem}${pad2(no)}` : '';

        const duplicate = complete
            && EXISTING_SECTION_NAMES.includes(name.toLowerCase())
            && name.toLowerCase() !== (editingSectionName ?? '').toLowerCase();

        document.getElementById('sectionPreview').textContent = name || '—';
        document.getElementById('sectionLegend').innerHTML = complete
            ? `<span><b>${year}</b> = ${YEAR_LABELS_JS[year]}</span><span><b>${sem}</b> = ${SEM_LABELS_JS[sem]}</span><span><b>${pad2(no)}</b> = Section ${pad2(no)}</span>`
            : 'Fill in the fields above.';
        const dup = document.getElementById('sectionDuplicate');
        dup.style.display = duplicate ? '' : 'none';
        dup.textContent = duplicate ? `"${name}" already exists. Choose a different section number.` : '';
        document.getElementById('sectionFormSubmit').disabled = !complete || duplicate;
    }
    ['sectionProgram', 'sectionYear', 'sectionSem', 'sectionNo'].forEach(id => {
        document.getElementById(id).addEventListener('input', updateSectionPreview);
        document.getElementById(id).addEventListener('change', updateSectionPreview);
    });
    // 1 → 01 once the chair leaves the field.
    document.getElementById('sectionNo').addEventListener('blur', event => {
        if (event.target.value !== '' && Number(event.target.value) >= 1) event.target.value = pad2(Number(event.target.value));
    });

    // Section card "..." menu: one open at a time, closes on outside click.
    function toggleSectionMenu(event, btn) {
        event.stopPropagation();
        const menu = btn.closest('.sc-menu');
        const wasOpen = menu.classList.contains('open');
        document.querySelectorAll('.sc-menu.open').forEach(m => m.classList.remove('open'));
        if (!wasOpen) menu.classList.add('open');
    }
    document.addEventListener('click', () => document.querySelectorAll('.sc-menu.open').forEach(m => m.classList.remove('open')));

    // Section-card status filters ("what needs attention").
    const SECTION_CONDITIONS = {
        at_risk: c => Number(c.dataset.risk) > 0,
        below: c => c.dataset.ready !== '' && Number(c.dataset.ready) < 75,
        on_target: c => c.dataset.ready !== '' && Number(c.dataset.ready) >= 75,
        no_data: c => c.dataset.ready === '',
        no_faculty: c => c.dataset.status === 'active' && c.dataset.faculty === '0',
        inactive: c => c.dataset.status === 'inactive',
    };
    function closeRowMenus() { document.querySelectorAll('.row-menu.open').forEach(m => m.classList.remove('open')); }
    function toggleRowMenu(event, btn) {
        event.stopPropagation();
        const menu = btn.closest('.row-menu');
        const wasOpen = menu.classList.contains('open');
        closeRowMenus();
        document.querySelectorAll('.sc-menu.open').forEach(m => m.classList.remove('open'));
        if (wasOpen) return;
        menu.classList.add('open');
        const drop = menu.querySelector('.row-dropdown');
        const r = btn.getBoundingClientRect();
        const below = r.bottom + 6 + drop.offsetHeight <= window.innerHeight;
        drop.style.top = (below ? r.bottom + 6 : r.top - 6 - drop.offsetHeight) + 'px';
        drop.style.left = Math.max(8, r.right - drop.offsetWidth) + 'px';
    }
    document.addEventListener('click', closeRowMenus);
    window.addEventListener('scroll', closeRowMenus, true);
    window.addEventListener('resize', closeRowMenus);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeRowMenus(); document.querySelectorAll('.sc-menu.open').forEach(m => m.classList.remove('open')); } });

    function filterSectionCards() {
        const yearSel = document.getElementById('secYear');
        const statusSel = document.getElementById('secStatus');
        const year = yearSel.value;
        const status = statusSel.value;
        const cards = [...document.querySelectorAll('#sectionCards .sc')];
        const realCards = cards.filter(c => c.dataset.kind !== 'unassigned');
        let shown = 0;
        cards.forEach(card => {
            const isUnassigned = card.dataset.kind === 'unassigned';
            const match = (!year || card.dataset.year === year)
                && (!status || (!isUnassigned && SECTION_CONDITIONS[status](card)));
            card.style.display = match ? '' : 'none';
            if (match && !isUnassigned) shown++;
        });

        // Highlight the filters in use and show the Clear button with a count.
        yearSel.classList.toggle('is-set', !!year);
        statusSel.classList.toggle('is-set', !!status);
        const active = [year, status].filter(Boolean).length;
        document.getElementById('secClear').classList.toggle('show', active > 0);
        document.getElementById('secClearCount').textContent = active;

        const result = document.getElementById('secResult');
        result.classList.toggle('show', active > 0 && realCards.length > 0);
        result.innerHTML = `Showing <strong>${shown}</strong> of ${realCards.length} section${realCards.length === 1 ? '' : 's'}`;

        const empty = document.getElementById('sectionCardsEmpty');
        if (realCards.length) {
            const anyVisible = cards.some(c => c.style.display !== 'none');
            empty.style.display = anyVisible ? 'none' : '';
            document.getElementById('sectionCardsEmptyText').innerHTML =
                'No sections match these filters. <a href="#" onclick="clearSectionFilters(); return false;" style="color:var(--primary);font-weight:600;">Clear filters</a>';
        }
    }
    function clearSectionFilters() {
        document.getElementById('secYear').value = '';
        document.getElementById('secStatus').value = '';
        filterSectionCards();
    }

    // ids: student ids to move; or null with selectAll=true for "all matching filters".
    const assignForm = document.getElementById('assignForm');
    const assignSubmit = document.getElementById('assignSubmit');
    function openAssign(ids, label, currentSection = null, selectAll = false) {
        const hidden = document.getElementById('assignHidden');
        hidden.innerHTML = '';
        const addHidden = (name, value) => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = name; input.value = value;
            hidden.appendChild(input);
        };
        if (selectAll) {
            addHidden('select_all', '1');
            const params = new URLSearchParams(window.location.search);
            ['search', 'year', 'section', 'status'].forEach(key => {
                const value = params.get(key);
                if (value) addHidden(key, value);
            });
        } else {
            ids.forEach(id => addHidden('student_ids[]', id));
        }

        assignForm.querySelectorAll('input[name=section_id]').forEach(r => { r.checked = false; });
        assignForm.querySelectorAll('.assign-opt').forEach(opt => {
            const isCurrent = !!currentSection && opt.dataset.sectionName === currentSection;
            const tag = opt.querySelector('.a-current');
            if (tag) tag.style.display = isCurrent ? '' : 'none';
        });
        assignSubmit.disabled = true;
        document.getElementById('assignSub').textContent = selectAll || ids.length > 1
            ? `Pick the section for ${label}. Students already in another section will be moved.`
            : `Pick the section for ${label}${currentSection ? ' (currently in ' + currentSection + ')' : ' (not in a section yet)'}.`;
        openModal('assignModal');
    }
    assignForm.addEventListener('change', event => {
        if (event.target.name === 'section_id') assignSubmit.disabled = false;
    });

    bulkSectionBtn?.addEventListener('click', () => {
        if (selectAllMode) {
            openAssign(null, totalMatching + ' students', null, true);
            return;
        }
        const ids = rowChecks().filter(c => c.checked).map(c => c.value);
        if (!ids.length) return;
        openAssign(ids, ids.length === 1 ? '1 selected student' : ids.length + ' selected students');
    });

    @if ($focusedSection)
    // ── Add already-enrolled students to the section being viewed ─────────
    const pick = { loaded: false, available: [], selected: new Set() };
    const NO_SECTION = @json($noSectionValue);
    const pickEsc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const pickSubmit = document.getElementById('pickSubmit');

    async function openAddStudents() {
        openModal('addStudentsModal');
        if (pick.loaded) return renderPick();
        try {
            const res = await fetch(@json(route('chair.sections.students', $focusedSection->id)), { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error();
            pick.available = (await res.json()).available;
            pick.loaded = true;
            const fromSel = document.getElementById('pickFrom');
            [...new Set(pick.available.map(s => s.section).filter(Boolean))].sort().forEach(sec => {
                fromSel.insertAdjacentHTML('beforeend', `<option value="${pickEsc(sec)}">From ${pickEsc(sec)}</option>`);
            });
            renderPick();
        } catch (e) {
            document.getElementById('pickList').innerHTML = '<div class="pick-empty">Could not load students. Please try again.</div>';
        }
    }
    function pickRows() {
        const q = document.getElementById('pickSearch').value.trim().toLowerCase();
        const from = document.getElementById('pickFrom').value;
        return pick.available.filter(s =>
            (!q || [s.name, s.email, s.student_number].some(v => (v ?? '').toLowerCase().includes(q)))
            && (!from || (from === NO_SECTION ? !s.section : s.section === from)));
    }
    function renderPick() {
        const rows = pickRows();
        document.getElementById('pickCount').textContent = `(${pick.available.length})`;
        document.getElementById('pickList').innerHTML = rows.length ? rows.map(s => `
            <label class="pick-row">
                <input type="checkbox" value="${s.id}" ${pick.selected.has(s.id) ? 'checked' : ''}>
                <div class="p-body"><div class="p-name">${pickEsc(s.name)}</div><div class="p-meta">${pickEsc(s.student_number || 'No student no.')} · ${pickEsc(s.email)}</div></div>
                ${s.section ? `<span class="p-tag">In ${pickEsc(s.section)}</span>` : '<span class="p-tag none">No section</span>'}
            </label>`).join('')
            : `<div class="pick-empty">${pick.available.length ? 'No students match.' : 'Every enrolled student is already in this section.'}</div>`;
        syncPick();
    }
    function syncPick() {
        const n = pick.selected.size;
        pickSubmit.disabled = n === 0;
        pickSubmit.textContent = n ? `Add ${n} to ${@json($focusedSection->name)}` : `Add to ${@json($focusedSection->name)}`;
        const boxes = [...document.querySelectorAll('#pickList input[type=checkbox]')];
        document.getElementById('pickAll').checked = boxes.length > 0 && boxes.every(cb => cb.checked);
    }
    document.getElementById('pickSearch').addEventListener('input', renderPick);
    document.getElementById('pickFrom').addEventListener('change', renderPick);
    document.getElementById('pickList').addEventListener('change', event => {
        const id = Number(event.target.value);
        event.target.checked ? pick.selected.add(id) : pick.selected.delete(id);
        syncPick();
    });
    document.getElementById('pickAll').addEventListener('change', event => {
        document.querySelectorAll('#pickList input[type=checkbox]').forEach(cb => {
            cb.checked = event.target.checked;
            event.target.checked ? pick.selected.add(Number(cb.value)) : pick.selected.delete(Number(cb.value));
        });
        syncPick();
    });
    pickSubmit.addEventListener('click', async () => {
        pickSubmit.disabled = true;
        document.getElementById('pickError').textContent = '';
        try {
            const res = await fetch(@json(route('chair.sections.students.add', $focusedSection->id)), {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                body: JSON.stringify({ student_ids: [...pick.selected] }),
            });
            if (!res.ok) throw new Error();
            location.reload(); // counts on the page are server-rendered
        } catch (e) {
            document.getElementById('pickError').textContent = 'Could not add the students. Please try again.';
            pickSubmit.disabled = false;
        }
    });
    @endif
</script>

    @include('partials.alerts')
</body>
</html>
