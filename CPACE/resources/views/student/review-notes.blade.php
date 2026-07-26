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
        :root {
            --brand: #7B1D1D;
            --red: #c0392b;
            --red-soft: #fdf1ee;
            --red-chip-bg: #fdecea;
            --ink: #1c1c1e;
            --muted: #8a8a8e;
            --line: #ececee;
            --panel-radius: 12px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* ── MODERN MINIMAL SCROLLBARS ──────────────────────────────────── */
        * {
            scrollbar-width: thin;                      /* Firefox + Chrome 121+ */
            scrollbar-color: rgba(0,0,0,0.18) transparent;
        }
        *::-webkit-scrollbar { width: 6px; height: 6px; }
        *::-webkit-scrollbar-track { background: transparent; }
        *::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.16); border-radius: 10px; }
        *::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.30); }
        *::-webkit-scrollbar-corner { background: transparent; }

        /* Panel scrollbars stay invisible until you hover over the panel */
        .folders-list, .notes-scroll, .pv-body, .tabs-card {
            scrollbar-color: transparent transparent;
        }
        .folders-list:hover, .notes-scroll:hover, .pv-body:hover, .tabs-card:hover {
            scrollbar-color: rgba(0,0,0,0.20) transparent;
        }
        .folders-list::-webkit-scrollbar-thumb,
        .notes-scroll::-webkit-scrollbar-thumb,
        .pv-body::-webkit-scrollbar-thumb,
        .tabs-card::-webkit-scrollbar-thumb { background: transparent; }
        .folders-list:hover::-webkit-scrollbar-thumb,
        .notes-scroll:hover::-webkit-scrollbar-thumb,
        .pv-body:hover::-webkit-scrollbar-thumb,
        .tabs-card:hover::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.20); }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f7f7f8;
            color: var(--ink);
        }

        .dashboard-container { display: block; min-height: 100vh; }

        .main-content {
            margin-left: 211px;
            padding: 28px 32px 32px;
            transition: margin-left 0.3s ease;
        }
        .sidebar.collapsed ~ .main-content { margin-left: 70px; }

        /* ── HEADER ─────────────────────────────────────────────────────── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            gap: 20px;
        }
        .header-title { font-size: 28px; font-weight: 600; color: #222; }
        .header-subtitle { color: #9a9aa0; font-size: 13.5px; margin-top: 2px; }

        .header-right { display: flex; align-items: center; gap: 14px; }

        .search-box { position: relative; width: 340px; }
        .search-box i {
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
            color: #b0b0b6; font-size: 14px;
        }
        .search-box input {
            width: 100%;
            padding: 12px 16px 12px 42px;
            border: 1px solid #ebebed;
            border-radius: 24px;
            font-size: 13px;
            background: #fff;
            color: #555;
            font-family: 'Poppins', sans-serif;
            outline: none;
        }
        .search-box input::placeholder { color: #b3b3b8; }
        .search-box input:focus { border-color: #ddd; }

        .icon-btn {
            width: 40px; height: 40px;
            border: none; background: transparent; border-radius: 10px;
            cursor: pointer; font-size: 17px; color: #4c4c52;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
        }
        .icon-btn:hover { background: #ededef; }

        .profile-btn {
            width: 42px; height: 42px;
            background: var(--brand);
            border: none; border-radius: 50%;
            color: #fff; font-weight: 600; cursor: pointer; font-size: 14px;
            font-family: 'Poppins', sans-serif;
        }
        .profile-btn:hover { background: #6a1818; }

        .dropdown-menu {
            position: absolute; top: 100%; right: 0;
            background: #fff; border: 1px solid #e6e6e8; border-radius: 10px;
            min-width: 185px; margin-top: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            display: none; z-index: 1000; overflow: hidden;
        }
        .dropdown-menu.active { display: block; }
        .dropdown-menu a, .dropdown-menu button {
            display: flex; align-items: center; gap: 10px;
            width: 100%; padding: 11px 16px;
            background: none; border: none; text-align: left;
            color: #333; font-size: 13px; text-decoration: none;
            font-family: 'Poppins', sans-serif; cursor: pointer;
            border-bottom: 1px solid #f4f4f5;
        }
        .dropdown-menu a i, .dropdown-menu button i { width: 16px; text-align: center; color: var(--brand); }
        .dropdown-menu a:hover, .dropdown-menu button:hover { background: #f9f9f9; }
        .dropdown-menu .logout-btn, .dropdown-menu .logout-btn i { color: #e53e3e; }

        /* ── TABS ───────────────────────────────────────────────────────── */
        .tabs-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--panel-radius);
            display: flex;
            align-items: stretch;
            gap: 6px;
            padding: 0 12px;
            margin-bottom: 20px;
            overflow-x: auto;
        }
        .tab-btn {
            display: flex; align-items: center; gap: 9px;
            padding: 18px 16px 15px;
            background: none; border: none;
            border-bottom: 3px solid transparent;
            font-size: 13.5px; font-weight: 500;
            color: #55555b; cursor: pointer;
            font-family: 'Poppins', sans-serif;
            white-space: nowrap;
            transition: color 0.15s;
        }
        .tab-btn i { font-size: 14px; }
        .tab-btn:hover { color: var(--red); }
        .tab-btn.active {
            color: var(--red);
            font-weight: 600;
            border-bottom-color: var(--red);
        }

        /* ── WORKSPACE GRID ─────────────────────────────────────────────── */
        .workspace {
            display: grid;
            grid-template-columns: 292px 1fr;
            gap: 20px;
            align-items: start;
        }
        /* Preview stays hidden until a note is opened */
        .workspace .preview-panel { display: none; }
        .workspace.preview-open { grid-template-columns: 292px minmax(360px, 445px) 1fr; }
        .workspace.preview-open .preview-panel { display: flex; }

        .panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--panel-radius);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 18px 20px;
            border-bottom: 1px solid #f1f1f3;
            flex-shrink: 0;
        }
        .panel-head .panel-title { font-size: 15px; font-weight: 600; color: #222; }

        /* ── FOLDERS PANEL ──────────────────────────────────────────────── */
        .folders-panel { max-height: calc(100vh - 235px); }

        .add-folder-btn {
            width: 30px; height: 30px;
            border: none; border-radius: 50%;
            background: var(--red);
            color: #fff; font-size: 12px;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: transform 0.15s, background 0.15s;
        }
        .add-folder-btn:hover { transform: scale(1.1) rotate(90deg); background: var(--brand); }

        .folders-list { overflow-y: auto; flex: 1; padding: 10px; }

        .folder-item {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 12px;
            border-radius: 12px;
            margin-bottom: 3px;
            cursor: pointer;
            transition: background 0.15s, transform 0.12s;
        }
        .folder-item:hover { background: #f5f6f8; transform: translateX(2px); }
        .folder-item.active { background: var(--fi-pastel, var(--red-soft)); }

        .f-svg { flex-shrink: 0; }
        .folder-item .f-name {
            flex: 1; font-size: 13px; font-weight: 500; color: #3c3c41; line-height: 1.45;
        }
        .folder-item.active .f-name { color: var(--fi-base, var(--brand)); font-weight: 600; }
        .folder-item .f-count {
            font-size: 11.5px; font-weight: 600;
            background: var(--fi-pastel, #f2f2f4); color: var(--fi-base, #a3a3a8);
            border-radius: 12px; padding: 3px 10px;
        }
        .folder-item.active .f-count { background: var(--fi-base, var(--red)); color: #fff; }

        .trash-item {
            border-top: 1px solid #f1f1f3;
            border-radius: 0 0 12px 12px;
            margin-top: 8px;
            padding-top: 13px;
        }

        /* ── NOTES LIST PANEL ───────────────────────────────────────────── */
        .list-panel { max-height: calc(100vh - 235px); }

        .sort-wrap { display: flex; align-items: center; gap: 6px; }
        .sort-select {
            border: none; background: transparent;
            font-size: 12.5px; color: #55555b; font-weight: 500;
            font-family: 'Poppins', sans-serif; cursor: pointer; outline: none;
            max-width: 165px;
        }
        .view-toggle-btn {
            width: 30px; height: 30px; border: none; border-radius: 7px;
            background: transparent; color: #6f6f75; font-size: 14px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }
        .view-toggle-btn:hover { background: #f1f1f3; }

        .notes-scroll { overflow-y: auto; flex: 1; padding: 14px; }

        .group-label {
            padding: 10px 6px 6px;
            font-size: 11px; font-weight: 600; letter-spacing: 0.6px;
            text-transform: uppercase; color: #a3a3a8;
        }

        .note-item {
            display: flex; align-items: flex-start; gap: 13px;
            padding: 15px 16px;
            background: var(--nc-accent-bg, #fff);
            border: 1.5px solid var(--nc-dark, #64748b);
            border-radius: 14px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s, background 0.15s;
            position: relative;
        }
        .note-item:hover { transform: translateY(-2px); }
        .note-item.selected {
            background: var(--nc-deep, var(--nc-accent-bg));
            box-shadow: 0 0 0 1.5px var(--nc-dark, var(--red));
        }

        /* Flat bright subject icon on each note */
        .ni-avatar {
            width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 15px;
        }
        .ni-avatar.sm { width: 32px; height: 32px; border-radius: 10px; font-size: 12.5px; }

        .ni-main { flex: 1; min-width: 0; }
        .ni-title {
            font-size: 14px; font-weight: 600; color: #232327;
            line-height: 1.4; margin-bottom: 9px;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .note-item.selected .ni-title { color: #1c1c1e; }

        .ni-chips { display: flex; flex-wrap: wrap; gap: 6px; }
        .chip {
            display: inline-block;
            padding: 3.5px 11px;
            border-radius: 14px;
            font-size: 10.5px; font-weight: 500;
            background: #f2f2f4; color: #6f6f75;
            white-space: nowrap;
        }
        /* Chips sit on pastel backgrounds → white with a thin outline so they read clean */
        .note-item .chip, .note-card .chip {
            background: rgba(255,255,255,0.85);
            color: #55555b;
            border: 1px solid rgba(0,0,0,0.10);
        }

        .ni-side {
            display: flex; flex-direction: column;
            align-items: flex-end; justify-content: space-between;
            gap: 8px; flex-shrink: 0;
        }
        .ni-actions { display: flex; align-items: center; gap: 2px; }

        .star-btn {
            background: none; border: none; cursor: pointer;
            font-size: 14px; color: #d3d3d7; padding: 3px 5px;
            transition: color 0.15s, transform 0.15s;
        }
        .star-btn:hover { color: #f0932b; transform: scale(1.12); }
        .star-btn.on { color: #f0932b; }

        .kebab-btn {
            background: none; border: none; cursor: pointer;
            font-size: 14px; color: #a8a8ad; padding: 3px 7px; border-radius: 6px;
        }
        .kebab-btn:hover { color: #4c4c52; background: rgba(0,0,0,0.05); }

        .ni-date { font-size: 11.5px; color: #a0a0a5; white-space: nowrap; }

        /* ── CARD VIEW (default) ────────────────────────────────────────── */
        .notes-scroll.cards-on { padding: 18px; }
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 16px;
        }
        .cards-group-label {
            grid-column: 1 / -1;
            font-size: 11px; font-weight: 600; letter-spacing: 0.6px;
            text-transform: uppercase; color: #a3a3a8;
            padding: 8px 2px 0;
        }
        .note-card {
            background: var(--nc-accent-bg, #fff);
            border: 1.5px solid var(--nc-dark, #64748b);
            border-radius: 14px;
            display: flex; flex-direction: column;
            overflow: hidden; cursor: pointer;
            transition: box-shadow 0.2s, transform 0.2s, background 0.2s;
        }
        .note-card:hover { transform: translateY(-3px); }
        .note-card.selected {
            background: var(--nc-deep, var(--nc-accent-bg));
            box-shadow: 0 0 0 1.5px var(--nc-dark, var(--red));
        }

        .nc-body { padding: 14px 15px 13px; display: flex; flex-direction: column; gap: 9px; flex: 1; }
        .nc-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .nc-topleft { display: flex; align-items: center; gap: 9px; }
        .nc-subject-badge {
            display: inline-block; padding: 3.5px 11px;
            border-radius: 20px; font-size: 10px;
            font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;
            background: var(--nc-accent, #94a3b8); color: #fff;
        }
        .nc-actions-top { display: flex; align-items: center; }
        .nc-title { font-size: 14px; font-weight: 600; color: #232327; line-height: 1.35; }
        .nc-preview {
            font-size: 12px; color: #98989d; line-height: 1.6;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .nc-footer {
            display: flex; align-items: center; justify-content: space-between; gap: 8px;
            padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.85); margin-top: auto;
        }
        .nc-date { font-size: 11px; color: #a8a8ad; display: flex; align-items: center; gap: 5px; }
        .nc-date i { font-size: 10.5px; }

        .list-empty {
            text-align: center; padding: 60px 24px; color: #a8a8ad;
        }
        .list-empty i { font-size: 38px; color: #dedee1; margin-bottom: 14px; display: block; }
        .list-empty p { font-size: 13.5px; color: #77777d; margin-bottom: 3px; }
        .list-empty span { font-size: 12px; }

        /* ── PREVIEW PANEL ──────────────────────────────────────────────── */
        .preview-panel { max-height: calc(100vh - 235px); }

        .pv-head-actions { display: flex; align-items: center; gap: 2px; }
        .pv-tool {
            width: 32px; height: 32px; border: none; border-radius: 8px;
            background: transparent; color: #55555b; font-size: 14px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }
        .pv-tool:hover { background: #f1f1f3; }
        .pv-tool.star-on { color: #f0932b; }

        .pv-body { overflow-y: auto; flex: 1; padding: 24px 22px 36px; background: #eef0f3; }

        /* Document sheet — the note reads like a paper file */
        .doc-sheet {
            background: #fff;
            max-width: 760px;
            margin: 0 auto;
            padding: 44px 52px 64px;
            border: 1px solid #e2e3e8;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.09), 0 10px 28px rgba(0,0,0,0.05);
            min-height: 640px;
        }
        .doc-sheet.editing {
            box-shadow: 0 0 0 2px rgba(192,57,43,0.22), 0 10px 28px rgba(0,0,0,0.06);
            caret-color: var(--red);
        }
        .doc-divider { border: none; border-top: 1px solid #f0f0f2; margin: 18px 0 8px; }

        #docTitle, #docEditor { outline: none; }
        #docEditor { min-height: 360px; cursor: text; }
        #docTitle:empty::before { content: 'Untitled note'; color: #c6c6cb; }

        /* Highlighter colours */
        mark[class^="hl-"], mark[class*=" hl-"] { padding: 0.5px 2px; border-radius: 2.5px; color: inherit; }
        .hl-yellow { background: #fff1a8; }
        .hl-green  { background: #c9f2c7; }
        .hl-pink   { background: #ffd6e7; }
        .hl-blue   { background: #cfe8ff; }

        /* Editing toolbar */
        .doc-toolbar {
            display: none;
            align-items: center; gap: 3px; flex-wrap: wrap;
            padding: 8px 14px;
            border-bottom: 1px solid #f1f1f3;
            background: #fcfcfd;
            flex-shrink: 0;
        }
        .doc-toolbar.on { display: flex; }
        .dt-btn {
            min-width: 30px; height: 30px; padding: 0 8px;
            border: none; border-radius: 7px; background: transparent;
            color: #4c4c52; font-size: 12.5px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 5px;
            font-family: 'Poppins', sans-serif;
        }
        .dt-btn:hover { background: #ededf0; }
        .dt-sep { width: 1px; height: 20px; background: #e6e6ea; margin: 0 6px; }
        .dt-hl-ico { color: #8a8a8e; font-size: 12px; margin: 0 4px 0 2px; }
        .dt-dot {
            width: 18px; height: 18px; border-radius: 50%;
            border: 1.5px solid rgba(0,0,0,0.12);
            cursor: pointer; padding: 0; margin: 0 2px;
            transition: transform 0.12s;
        }
        .dt-dot:hover { transform: scale(1.18); }
        .dt-spacer { flex: 1; }
        .dt-cancel {
            height: 30px; padding: 0 13px;
            border: 1px solid #ddd; border-radius: 7px; background: #fff;
            color: #666; font-size: 12px; font-weight: 500; cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }
        .dt-cancel:hover { background: #f6f6f6; }
        .dt-save {
            height: 30px; padding: 0 14px;
            border: none; border-radius: 7px; background: var(--brand);
            color: #fff; font-size: 12px; font-weight: 600; cursor: pointer;
            font-family: 'Poppins', sans-serif;
            display: flex; align-items: center; gap: 6px;
        }
        .dt-save:hover { background: #6a1818; }
        .dt-save:disabled { opacity: 0.6; cursor: not-allowed; }

        .pv-trash-banner {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            background: #fff7ed; border: 1px solid #fde4c4;
            color: #b45309; font-size: 12.5px;
            border-radius: 9px; padding: 10px 14px; margin-bottom: 16px;
        }
        .pv-trash-banner button {
            border: none; background: #b45309; color: #fff; font-size: 12px; font-weight: 600;
            border-radius: 7px; padding: 7px 13px; cursor: pointer; font-family: 'Poppins', sans-serif;
        }

        .pv-title { font-size: 22px; font-weight: 700; color: #1b1b1e; line-height: 1.3; }

        .pv-chips { display: flex; flex-wrap: wrap; gap: 8px; margin: 14px 0 15px; }
        .pv-chips .chip { background: var(--red-chip-bg); color: var(--red); font-weight: 500; padding: 5px 13px; font-size: 11px; }

        .pv-meta {
            display: flex; align-items: center; gap: 8px;
            font-size: 12.5px; color: #97979c; margin-bottom: 8px;
        }
        .pv-meta i { font-size: 12px; }
        .pv-meta .dot { color: #cfcfd3; }

        .pv-content { margin-top: 14px; }
        .pv-h {
            font-size: 14.5px; font-weight: 600; color: var(--red);
            margin: 24px 0 10px;
        }
        .pv-content > .pv-h:first-child { margin-top: 8px; }
        .pv-p { font-size: 13.5px; color: #3a3a3f; line-height: 1.75; margin-bottom: 10px; }
        .pv-formula {
            background: #f3f3f5; border-radius: 9px;
            padding: 17px 20px; text-align: center;
            font-size: 13.5px; font-weight: 500; color: #2c2c30; line-height: 1.8;
            margin: 6px 0 14px;
        }
        .pv-ul { list-style: none; margin: 4px 0 14px; }
        .pv-ul li {
            position: relative; padding-left: 20px;
            font-size: 13.5px; color: #3a3a3f; line-height: 1.7; margin-bottom: 9px;
        }
        .pv-ul li::before {
            content: ''; position: absolute; left: 4px; top: 9px;
            width: 6px; height: 6px; border-radius: 50%; background: var(--red);
        }
        .pv-example {
            background: #fdf6e0; border-radius: 9px;
            padding: 17px 20px;
            font-size: 13.5px; color: #3f3a2d; line-height: 1.9;
            margin: 6px 0 14px;
        }

        .pv-empty { text-align: center; padding: 90px 30px; color: #a8a8ad; }
        .pv-empty i { font-size: 44px; color: #e2e2e5; margin-bottom: 16px; display: block; }
        .pv-empty p { font-size: 14px; color: #77777d; margin-bottom: 4px; }
        .pv-empty span { font-size: 12.5px; }

        /* ── CONTEXT MENU ───────────────────────────────────────────────── */
        .ctx-menu {
            position: fixed;
            background: #fff; border: 1px solid #e6e6e8; border-radius: 10px;
            box-shadow: 0 10px 28px rgba(0,0,0,0.14);
            min-width: 185px; z-index: 4000; overflow: hidden;
            display: none;
        }
        .ctx-menu.open { display: block; }
        .ctx-menu button {
            display: flex; align-items: center; gap: 10px;
            width: 100%; padding: 10.5px 15px;
            background: none; border: none; text-align: left;
            font-size: 12.5px; color: #3a3a3f; cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }
        .ctx-menu button i { width: 15px; text-align: center; color: #8a8a90; font-size: 12px; }
        .ctx-menu button:hover { background: #f7f7f8; }
        .ctx-menu button.danger, .ctx-menu button.danger i { color: #e03131; }
        .ctx-menu .ctx-sep { height: 1px; background: #f1f1f3; }

        /* ── MODAL ──────────────────────────────────────────────────────── */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.45);
            display: none; align-items: center; justify-content: center;
            z-index: 5000; padding: 20px;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: #fff; border-radius: 14px;
            width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
            animation: modalUp 0.25s ease;
        }
        @keyframes modalUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .modal-head {
            display: flex; justify-content: space-between; align-items: center;
            padding: 20px 24px; border-bottom: 1px solid #f0f0f0;
        }
        .modal-head h3 { font-size: 17px; font-weight: 600; color: #2b2b2b; }
        .modal-close { background: none; border: none; font-size: 20px; color: #aaa; cursor: pointer; line-height: 1; }
        .modal-close:hover { color: var(--red); }
        .modal-body { padding: 22px 24px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 12.5px; font-weight: 600; color: #555; margin-bottom: 7px; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px 13px;
            border: 1px solid #e2e2e2; border-radius: 8px;
            font-size: 13px; font-family: 'Poppins', sans-serif; color: #444;
        }
        .form-group textarea { resize: vertical; min-height: 150px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: var(--brand);
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-hint { font-size: 11px; color: #aaa; margin-top: 5px; }
        .field-error { font-size: 11.5px; color: var(--red); margin-top: 5px; display: none; }
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
            padding: 10px 20px; border: none; background: var(--brand); color: #fff;
            border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif;
        }
        .btn-primary:hover { background: #6a1818; }
        .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

        /* ── TOAST ──────────────────────────────────────────────────────── */
        .toast {
            position: fixed; bottom: 26px; right: 26px;
            background: #2b2b2b; color: #fff;
            padding: 13px 20px; border-radius: 10px; font-size: 13px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
            display: flex; align-items: center; gap: 10px;
            z-index: 6000; opacity: 0; transform: translateY(20px);
            transition: all 0.3s ease; pointer-events: none;
        }
        .toast.show { opacity: 1; transform: translateY(0); }
        .toast.success i { color: #4ade80; }
        .toast.error { background: var(--red); }

        /* ── ALUMNI MATERIALS TAB ───────────────────────────────────────── */
        .materials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 16px;
        }
        .mat-card {
            background: #fff; border: 1px solid var(--line); border-radius: var(--panel-radius);
            padding: 16px; display: flex; flex-direction: column; gap: 10px;
        }
        .mat-head { display: flex; align-items: flex-start; gap: 12px; }
        .mat-icon {
            width: 42px; height: 42px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: #fff;
        }
        .mat-title { font-size: 13.5px; font-weight: 600; color: #232327; line-height: 1.35; }
        .mat-meta { font-size: 11px; color: #a0a0a5; margin-top: 3px; }
        .mat-desc { font-size: 12px; color: #6f6f75; line-height: 1.55; }
        .mat-tags { display: flex; flex-wrap: wrap; gap: 6px; }
        .mat-foot {
            display: flex; flex-direction: column; align-items: stretch; gap: 10px;
            border-top: 1px solid #f1f1f3; padding-top: 10px; margin-top: auto;
        }
        .mat-downloads {
            font-size: 11px; color: #a0a0a5; display: flex; align-items: center; gap: 5px;
            white-space: nowrap;
        }
        .mat-actions { display: flex; align-items: center; gap: 8px; }
        .mat-actions .mat-btn { flex: 1; }
        .mat-btn {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            border: none; padding: 8px 13px; border-radius: 8px;
            font-size: 12px; font-weight: 600; font-family: 'Poppins', sans-serif;
            cursor: pointer; text-decoration: none; white-space: nowrap;
        }
        .mat-btn-view { background: var(--red-chip-bg); color: var(--red); }
        .mat-btn-view:hover { background: #fbdbd3; }
        .mat-btn-download { background: #f2f2f4; color: #55555b; }
        .mat-btn-download:hover { background: #e8e8ea; }

        /* Big preview modal — regardless of file type */
        .mat-modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.6);
            display: none; align-items: center; justify-content: center;
            z-index: 5500; padding: 30px;
        }
        .mat-modal-overlay.open { display: flex; }
        .mat-modal {
            background: #fff; border-radius: 14px;
            width: 100%; max-width: 980px; height: calc(100vh - 60px);
            display: flex; flex-direction: column; overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            animation: modalUp 0.25s ease;
        }
        .mat-modal-head {
            display: flex; align-items: center; justify-content: space-between; gap: 14px;
            padding: 16px 22px; border-bottom: 1px solid #f0f0f0; flex-shrink: 0;
        }
        .mat-modal-title-wrap { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .mat-modal-title { font-size: 15.5px; font-weight: 600; color: #232327; }
        .mat-modal-sub { font-size: 11.5px; color: #a0a0a5; margin-top: 2px; }
        .mat-modal-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .mat-modal-body {
            flex: 1; background: #eef0f3; overflow: auto;
            display: flex; align-items: stretch; justify-content: center;
        }
        .mat-modal-body iframe { width: 100%; height: 100%; border: none; background: #fff; }
        .mat-modal-body img { max-width: 100%; max-height: 100%; margin: auto; object-fit: contain; }
        .mat-preview-fallback {
            margin: auto; text-align: center; padding: 50px 30px; color: #77777d;
        }
        .mat-preview-fallback i { font-size: 48px; margin-bottom: 16px; display: block; }
        .mat-preview-fallback p { font-size: 14px; margin-bottom: 6px; color: #3a3a3f; font-weight: 500; }
        .mat-preview-fallback span { font-size: 12.5px; color: #a0a0a5; }

        /* ── RESPONSIVE ─────────────────────────────────────────────────── */
        @media (max-width: 1350px) {
            .workspace { grid-template-columns: 250px 1fr; }
            .workspace.preview-open { grid-template-columns: 250px 1fr 1.15fr; }
        }
        @media (max-width: 1120px) {
            .workspace { grid-template-columns: 1fr; }
            .workspace.preview-open { grid-template-columns: 1fr 1.2fr; }
            .folders-panel { grid-column: 1 / -1; max-height: 320px; }
        }
        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 20px 16px 90px; }
            .workspace { grid-template-columns: 1fr; }
            .folders-panel, .list-panel, .preview-panel { max-height: none; }
            .list-panel .notes-scroll { max-height: 420px; }
            .search-box { display: none; }
            .header { flex-wrap: wrap; }
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
                <div>
                    <div class="header-title">Review Notes</div>
                    <div class="header-subtitle">Organize your key learnings and important concepts.</div>
                </div>
                <div class="header-right">
                    <div class="search-box gs-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" id="globalSearch" data-gs="true" placeholder="Search notes, topics, or subjects..." autocomplete="off">
                    </div>
                    <a href="{{ route('calendar') }}" class="icon-btn" title="Calendar"><i class="far fa-calendar"></i></a>
                    <a href="{{ route('messages.index') }}" class="icon-btn" title="Messages" aria-label="Messages" style="position:relative;"><i class="far fa-comment-dots"></i>@if($unreadMessages > 0)<span style="position:absolute;top:-4px;right:-4px;min-width:16px;height:16px;padding:0 4px;background:#c0392b;color:#fff;border-radius:8px;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>@endif</a>
                    <a href="{{ route('notifications.index') }}" class="icon-btn" title="Notifications" aria-label="Notifications" style="position:relative;"><i class="far fa-bell"></i>@if($unreadNotifications > 0)<span style="position:absolute;top:-4px;right:-4px;min-width:16px;height:16px;padding:0 4px;background:#c0392b;color:#fff;border-radius:8px;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>@endif</a>
                    <div style="position: relative;">
                        <button class="profile-btn" id="profileBtn">@include('partials.avatar-content')</button>
                        <div class="dropdown-menu" id="profileDropdown">
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

            <!-- TABS -->
            <div class="tabs-card" id="tabsBar">
                <button class="tab-btn active" data-tab="all"><i class="far fa-file-lines"></i> All Notes</button>
                <button class="tab-btn" data-tab="favorites"><i class="far fa-star"></i> Favorites</button>
                <button class="tab-btn" data-tab="subject"><i class="far fa-bookmark"></i> By Subject</button>
                <button class="tab-btn" data-tab="topic"><i class="fas fa-tag"></i> By Topic</button>
                <button class="tab-btn" data-tab="recent"><i class="far fa-clock"></i> Recent</button>
                <button class="tab-btn" data-tab="archived"><i class="fas fa-box-archive"></i> Archived</button>
                <button class="tab-btn" data-tab="materials"><i class="fas fa-graduation-cap"></i> Alumni Materials</button>
            </div>

            <!-- WORKSPACE -->
            <div class="workspace" id="workspace">
                <!-- FOLDERS -->
                <section class="panel folders-panel">
                    <div class="panel-head">
                        <span class="panel-title">My Folders (Subjects)</span>
                        <button class="add-folder-btn" id="newNoteBtn" title="New note"><i class="fas fa-plus"></i></button>
                    </div>
                    <div class="folders-list" id="foldersList"></div>
                </section>

                <!-- NOTES LIST -->
                <section class="panel list-panel">
                    <div class="panel-head">
                        <span class="panel-title">Notes List</span>
                        <div class="sort-wrap">
                            <select class="sort-select" id="sortSelect" title="Sort notes">
                                <option value="recent">Sort: Most Recent</option>
                                <option value="oldest">Sort: Oldest</option>
                                <option value="az">Sort: A – Z</option>
                                <option value="reviewed">Sort: Recently Reviewed</option>
                            </select>
                            <button class="view-toggle-btn" id="densityBtn" title="Switch to list view"><i class="fas fa-list-ul"></i></button>
                        </div>
                    </div>
                    <div class="notes-scroll" id="notesList"></div>
                </section>

                <!-- PREVIEW -->
                <section class="panel preview-panel">
                    <div class="panel-head">
                        <span class="panel-title">Note Preview</span>
                        <div style="display:flex;align-items:center;gap:2px;">
                            <div class="pv-head-actions" id="pvActions" style="display:none;">
                                <button class="pv-tool" id="pvEditBtn" title="Edit note"><i class="fas fa-pencil"></i></button>
                                <button class="pv-tool" id="pvStarBtn" title="Favorite"><i class="far fa-star"></i></button>
                                <button class="pv-tool" id="pvPrintBtn" title="Print / Save as PDF"><i class="fas fa-print"></i></button>
                                <button class="pv-tool" id="pvKebabBtn" title="More"><i class="fas fa-ellipsis-vertical"></i></button>
                            </div>
                            <button class="pv-tool" id="pvCloseBtn" title="Close preview"><i class="fas fa-xmark"></i></button>
                        </div>
                    </div>
                    <div class="doc-toolbar" id="docToolbar">
                        <button class="dt-btn" data-cmd="bold" title="Bold (Ctrl+B)"><i class="fas fa-bold"></i></button>
                        <button class="dt-btn" data-cmd="italic" title="Italic (Ctrl+I)"><i class="fas fa-italic"></i></button>
                        <button class="dt-btn" data-cmd="underline" title="Underline (Ctrl+U)"><i class="fas fa-underline"></i></button>
                        <span class="dt-sep"></span>
                        <i class="fas fa-highlighter dt-hl-ico" title="Highlight selection"></i>
                        <button class="dt-dot" data-hl="yellow" style="background:#fff1a8" title="Highlight yellow"></button>
                        <button class="dt-dot" data-hl="green" style="background:#c9f2c7" title="Highlight green"></button>
                        <button class="dt-dot" data-hl="pink" style="background:#ffd6e7" title="Highlight pink"></button>
                        <button class="dt-dot" data-hl="blue" style="background:#cfe8ff" title="Highlight blue"></button>
                        <button class="dt-btn" data-hl="none" title="Remove highlight"><i class="fas fa-eraser"></i></button>
                        <span class="dt-sep"></span>
                        <button class="dt-btn" data-block="h" title="Heading"><i class="fas fa-heading"></i></button>
                        <button class="dt-btn" data-block="ul" title="Bullet list"><i class="fas fa-list-ul"></i></button>
                        <button class="dt-btn" data-block="formula" title="Formula box"><i class="fas fa-square-root-variable"></i></button>
                        <button class="dt-btn" data-block="example" title="Example box"><i class="far fa-lightbulb"></i></button>
                        <span class="dt-spacer"></span>
                        <button class="dt-cancel" id="dtCancel">Cancel</button>
                        <button class="dt-save" id="dtSave"><i class="fas fa-check"></i> Save</button>
                    </div>
                    <div class="pv-body" id="previewBody"></div>
                </section>
            </div>

            <!-- ALUMNI MATERIALS -->
            <div class="panel" id="materialsWrap" style="display:none;">
                <div class="panel-head">
                    <span class="panel-title">Alumni Materials</span>
                    <div class="sort-wrap">
                        <select class="sort-select" id="matSubjectFilter" title="Filter by subject">
                            <option value="">All subjects</option>
                            @foreach($subjects as $subj)
                                <option value="{{ $subj->code }}">{{ $subj->code }} — {{ $subj->name }}</option>
                            @endforeach
                        </select>
                        <select class="sort-select" id="matSortSelect" title="Sort materials">
                            <option value="newest">Sort: Newest</option>
                            <option value="most_downloaded">Sort: Most Downloaded</option>
                            <option value="az">Sort: A – Z</option>
                        </select>
                    </div>
                </div>
                <div style="padding:16px;overflow-y:auto;max-height:calc(100vh - 235px);" id="materialsList"></div>
            </div>
        </main>
    </div>

    <!-- ALUMNI MATERIAL PREVIEW MODAL -->
    <div class="mat-modal-overlay" id="matModal">
        <div class="mat-modal">
            <div class="mat-modal-head">
                <div class="mat-modal-title-wrap">
                    <div class="mat-icon" id="matModalIcon"><i class="fas fa-file"></i></div>
                    <div style="min-width:0;">
                        <div class="mat-modal-title" id="matModalTitle"></div>
                        <div class="mat-modal-sub" id="matModalSub"></div>
                    </div>
                </div>
                <div class="mat-modal-actions">
                    <a href="#" id="matModalDownload" class="mat-btn mat-btn-download" title="Download"><i class="fas fa-arrow-down-to-line"></i> Download</a>
                    <button class="icon-btn" id="matModalClose" title="Close"><i class="fas fa-xmark"></i></button>
                </div>
            </div>
            <div class="mat-modal-body" id="matModalBody"></div>
        </div>
    </div>

    <!-- CONTEXT MENU -->
    <div class="ctx-menu" id="ctxMenu"></div>

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
                        <input type="text" id="f_title" name="title" maxlength="180" placeholder="e.g. Tax Basis of Property" required>
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
                        <input type="text" id="f_tags" name="tags" maxlength="255" placeholder="Comma separated, e.g. Basis, Capital Gains, TRAIN Law">
                        <div class="form-hint">Shown as chips on the note and used by search and the By Topic tab.</div>
                    </div>
                    <div class="form-group">
                        <label for="f_content">Content</label>
                        <textarea id="f_content" name="content" placeholder="Write your note here...&#10;&#10;Formatting tips:&#10;## Heading&#10;- bullet point&#10;``` formula block ```&#10;> example box&#10;**bold**, *italic*, __underline__, ==highlight=="></textarea>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn-secondary" onclick="closeModal('noteModal')">Cancel</button>
                    <button type="submit" class="btn-primary" id="noteSaveBtn">Save Note</button>
                </div>
            </form>
        </div>
    </div>

    <div class="toast" id="toast"><i class="fas fa-circle-check"></i> <span id="toastMsg"></span></div>

    <!-- AI TUTOR (floating chat + highlight-to-ask) -->
    @include('partials.student-ai-tutor')

    <script>
        const CSRF     = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const NOTES     = @json($notes);
        const SUBJECTS  = @json($subjects);
        const TOPICS    = @json($topics);
        const MATERIALS = @json($materials);
        const ROUTES   = {
            store: "{{ route('review-notes.store') }}",
            base:  "{{ url('review-notes') }}",
        };

        // Bright subject themes: base (vivid) → light (gloss) → pastel/deep (backgrounds).
        const SUBJECT_THEMES = {
            far:  { base: '#4C9AFF', light: '#9AC6FF', pastel: '#EAF3FF', deep: '#D8E9FF', dark: '#2F7BE0' },
            tax:  { base: '#FF5D5D', light: '#FFA1A1', pastel: '#FFECEC', deep: '#FFDCDC', dark: '#E03131' },
            aud:  { base: '#2ECC71', light: '#86E5AE', pastel: '#E7FAEF', deep: '#D2F5E0', dark: '#1FA05A' },
            ms:   { base: '#FF9F1C', light: '#FFC97E', pastel: '#FFF3E0', deep: '#FFE7C4', dark: '#E8890C' },
            afar: { base: '#5C7CFA', light: '#A5B6FF', pastel: '#EDF0FF', deep: '#DCE2FF', dark: '#4263EB' },
            rfbt: { base: '#A78BFA', light: '#CBBAFF', pastel: '#F3EEFF', deep: '#E7DEFF', dark: '#845EF7' },
        };
        const FALLBACK_THEMES = [
            { base: '#14B8A6', light: '#7DDDD2', pastel: '#E4F8F5', deep: '#CCF1EB', dark: '#0CA678' },
            { base: '#EC4899', light: '#F59BC8', pastel: '#FDEBF4', deep: '#FBD8EA', dark: '#D6336C' },
        ];
        const GRAY_THEME = { base: '#8B95A5', light: '#C2CAD6', pastel: '#F2F4F7', deep: '#E5E9EF', dark: '#64748B' };

        // One theme per subject code (known codes get their colour, the rest rotate).
        const THEMES = {};
        {
            let fi = 0;
            SUBJECTS.forEach(s => {
                const c = (s.code || '').toLowerCase();
                THEMES[c] = SUBJECT_THEMES[c] || FALLBACK_THEMES[fi++ % FALLBACK_THEMES.length];
            });
        }
        function themeOf(code) {
            return THEMES[(code || '').toLowerCase()] || GRAY_THEME;
        }

        // Flat bright folder icon (front flap slightly lighter for depth, no gloss).
        function folderSvg(t) {
            return `
                <svg class="f-svg" viewBox="0 0 24 20" width="27" height="23" aria-hidden="true">
                    <path d="M1.5 4.2C1.5 3.1 2.4 2.2 3.5 2.2h5l2.3 2.4h9.7c1.1 0 2 .9 2 2v9.2c0 1.1-.9 2-2 2h-17c-1.1 0-2-.9-2-2Z" fill="${t.base}"/>
                    <path d="M1.5 8.2h21v7.6c0 1.1-.9 2-2 2h-17c-1.1 0-2-.9-2-2Z" fill="${t.light}"/>
                </svg>`;
        }

        // Flat trash-can icon for the Trash folder.
        function trashSvg(t) {
            return `
                <svg class="f-svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                    <rect x="5" y="7.5" width="14" height="14" rx="2.6" fill="${t.light}"/>
                    <rect x="3.5" y="4" width="17" height="3" rx="1.5" fill="${t.base}"/>
                    <rect x="9.5" y="2.4" width="5" height="2.6" rx="1.2" fill="${t.base}"/>
                    <rect x="8.1" y="10.2" width="1.8" height="8.3" rx="0.9" fill="#fff" opacity="0.8"/>
                    <rect x="11.1" y="10.2" width="1.8" height="8.3" rx="0.9" fill="#fff" opacity="0.8"/>
                    <rect x="14.1" y="10.2" width="1.8" height="8.3" rx="0.9" fill="#fff" opacity="0.8"/>
                </svg>`;
        }

        // ── State ──────────────────────────────────────────────────────────
        const state = {
            tab: 'all',          // all | favorites | subject | topic | recent | archived
            folder: null,        // subject id | 'other' | 'trash' | null
            sort: 'recent',
            search: '',
            selectedId: null,
            editing: false,
            view: 'cards',       // cards | rows
            matSubject: '',      // Alumni Materials subject filter (subject code)
            matSort: 'newest',   // newest | most_downloaded | az
        };

        const esc = s => String(s ?? '').replace(/[&<>"']/g, c =>
            ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

        const byId = id => NOTES.find(n => n.id === Number(id));

        // ── Filtering / sorting ────────────────────────────────────────────
        function visibleNotes() {
            let list = NOTES.slice();

            if (state.folder === 'trash') {
                list = list.filter(n => n.trashed);
            } else {
                list = list.filter(n => !n.trashed);
                list = state.tab === 'archived'
                    ? list.filter(n => n.archived)
                    : list.filter(n => !n.archived);

                if (state.tab === 'favorites') list = list.filter(n => n.is_favorite);

                if (state.folder === 'other')      list = list.filter(n => !n.subject_id);
                else if (state.folder !== null)    list = list.filter(n => n.subject_id === state.folder);
            }

            if (state.search) {
                const q = state.search.toLowerCase();
                list = list.filter(n =>
                    (n.title || '').toLowerCase().includes(q) ||
                    (n.tags || '').toLowerCase().includes(q) ||
                    (n.content || '').toLowerCase().includes(q) ||
                    (n.subject_code || '').toLowerCase().includes(q) ||
                    (n.subject_name || '').toLowerCase().includes(q) ||
                    (n.topic_name || '').toLowerCase().includes(q));
            }

            const sort = state.tab === 'recent' ? 'reviewed' : state.sort;
            list.sort((a, b) => {
                if (sort === 'oldest')   return (a.created_at || '').localeCompare(b.created_at || '');
                if (sort === 'az')       return (a.title || '').localeCompare(b.title || '');
                if (sort === 'reviewed') return (b.last_reviewed_at || b.created_at || '').localeCompare(a.last_reviewed_at || a.created_at || '');
                return (b.created_at || '').localeCompare(a.created_at || '');
            });

            return list;
        }

        // ── Folders panel ──────────────────────────────────────────────────
        function renderFolders() {
            const active = NOTES.filter(n => !n.trashed && !n.archived);
            const trashCount = NOTES.filter(n => n.trashed).length;
            const el = document.getElementById('foldersList');
            let html = '';

            SUBJECTS.forEach(s => {
                const count = active.filter(n => n.subject_id === s.id).length;
                const t = themeOf(s.code);
                const isActive = state.folder === s.id;
                html += `
                    <div class="folder-item ${isActive ? 'active' : ''}" data-folder="${s.id}"
                         style="--fi-base:${t.base};--fi-pastel:${t.pastel};">
                        ${folderSvg(t)}
                        <span class="f-name">${esc(s.name)} (${esc(s.code)})</span>
                        <span class="f-count">${count}</span>
                    </div>`;
            });

            const otherCount = active.filter(n => !n.subject_id).length;
            const trashTheme = { base: '#F87171', light: '#FBB1B1', pastel: '#FEEEEE', deep: '#FDDCDC' };
            html += `
                <div class="folder-item ${state.folder === 'other' ? 'active' : ''}" data-folder="other"
                     style="--fi-base:${GRAY_THEME.base};--fi-pastel:${GRAY_THEME.pastel};">
                    ${folderSvg(GRAY_THEME)}
                    <span class="f-name">Other Topics</span>
                    <span class="f-count">${otherCount}</span>
                </div>
                <div class="folder-item trash-item ${state.folder === 'trash' ? 'active' : ''}" data-folder="trash"
                     style="--fi-base:${trashTheme.base};--fi-pastel:${trashTheme.pastel};">
                    ${trashSvg(trashTheme)}
                    <span class="f-name">Trash</span>
                    <span class="f-count">${trashCount}</span>
                </div>`;

            el.innerHTML = html;

            el.querySelectorAll('.folder-item').forEach(item => {
                item.addEventListener('click', () => {
                    if (!maybeExitEdit()) return;
                    const raw = item.dataset.folder;
                    const val = raw === 'other' || raw === 'trash' ? raw : Number(raw);
                    state.folder = state.folder === val ? null : val;
                    renderAll();
                });
            });
        }

        // ── Notes list panel ───────────────────────────────────────────────
        function noteItemHtml(n) {
            const t = themeOf(n.subject_code);
            const chips = `<span class="nc-subject-badge">${esc(n.subject_code || 'GEN')}</span>`
                + (n.tag_list && n.tag_list.length ? n.tag_list : (n.topic_name ? [n.topic_name] : []))
                    .slice(0, 4)
                    .map(tag => `<span class="chip">${esc(tag)}</span>`)
                    .join('');

            return `
                <div class="note-item ${n.id === state.selectedId ? 'selected' : ''}" data-id="${n.id}"
                     style="${noteThemeStyle(t)}">
                    ${noteAvatar(t)}
                    <div class="ni-main">
                        <div class="ni-title">${esc(n.title)}</div>
                        <div class="ni-chips">${chips}</div>
                    </div>
                    <div class="ni-side">
                        <div class="ni-actions">
                            <button class="star-btn ${n.is_favorite ? 'on' : ''}" data-act="fav" title="Favorite">
                                <i class="${n.is_favorite ? 'fas' : 'far'} fa-star"></i>
                            </button>
                            <button class="kebab-btn" data-act="menu" title="More"><i class="fas fa-ellipsis-vertical"></i></button>
                        </div>
                        <span class="ni-date">${esc(n.date_display || '')}</span>
                    </div>
                </div>`;
        }

        // Shared style attribute + glossy avatar for a note's subject theme.
        function noteThemeStyle(t) {
            return `--nc-accent:${t.base};--nc-accent-bg:${t.pastel};--nc-deep:${t.deep};--nc-dark:${t.dark};`;
        }
        function noteAvatar(t, small) {
            return `
                <div class="ni-avatar ${small ? 'sm' : ''}" style="background:${t.base};">
                    <i class="fas fa-file-lines"></i>
                </div>`;
        }

        // Plain-text snippet of the note content (markup markers stripped).
        function previewText(n) {
            if (!n.content) return '';
            return n.content
                .replace(/```/g, ' ')
                .replace(/^#{1,3}\s+/gm, '')
                .replace(/^>\s?/gm, '')
                .replace(/^[-•]\s+/gm, '')
                .replace(/==\{\w+\}/g, '')
                .replace(/==|\*|_{2}/g, '')
                .replace(/\s+/g, ' ')
                .trim()
                .slice(0, 160);
        }

        function noteCardHtml(n) {
            const t = themeOf(n.subject_code);
            const chips = (n.tag_list && n.tag_list.length ? n.tag_list : (n.topic_name ? [n.topic_name] : []))
                .slice(0, 3)
                .map(tag => `<span class="chip">${esc(tag)}</span>`)
                .join('');
            const prev = previewText(n);

            return `
                <div class="note-card ${n.id === state.selectedId ? 'selected' : ''}" data-id="${n.id}"
                     style="${noteThemeStyle(t)}">
                    <div class="nc-body">
                        <div class="nc-top">
                            <div class="nc-topleft">
                                ${noteAvatar(t, true)}
                                <span class="nc-subject-badge">${esc(n.subject_code || 'GEN')}</span>
                            </div>
                            <div class="nc-actions-top">
                                <button class="star-btn ${n.is_favorite ? 'on' : ''}" data-act="fav" title="Favorite">
                                    <i class="${n.is_favorite ? 'fas' : 'far'} fa-star"></i>
                                </button>
                                <button class="kebab-btn" data-act="menu" title="More"><i class="fas fa-ellipsis-vertical"></i></button>
                            </div>
                        </div>
                        <div class="nc-title">${esc(n.title)}</div>
                        ${prev ? `<div class="nc-preview">${esc(prev)}</div>` : ''}
                        ${chips ? `<div class="ni-chips">${chips}</div>` : ''}
                        <div class="nc-footer">
                            <span class="nc-date"><i class="far fa-calendar"></i> ${esc(n.date_display || '')}</span>
                            <span class="nc-date"><i class="far fa-eye"></i> ${n.review_count || 0}×</span>
                        </div>
                    </div>
                </div>`;
        }

        function renderList() {
            const el = document.getElementById('notesList');
            const list = visibleNotes();
            const cards = state.view === 'cards';
            el.classList.toggle('cards-on', cards);

            if (!list.length) {
                const inTrash = state.folder === 'trash';
                el.innerHTML = `
                    <div class="list-empty">
                        <i class="far ${inTrash ? 'fa-trash-can' : 'fa-file-lines'}"></i>
                        <p>${inTrash ? 'Trash is empty' : 'No notes found'}</p>
                        <span>${inTrash ? 'Deleted notes will appear here.' : 'Try a different filter, or create a new note with the + button.'}</span>
                    </div>`;
                return;
            }

            const itemHtml = cards ? noteCardHtml : noteItemHtml;
            let html = '';
            if (state.tab === 'subject' || state.tab === 'topic') {
                const keyOf = n => state.tab === 'subject'
                    ? (n.subject_name ? `${n.subject_name} (${n.subject_code})` : 'Other Topics')
                    : (n.tag_list && n.tag_list.length ? n.tag_list[0] : (n.topic_name || 'Untagged'));
                const groups = {};
                list.forEach(n => (groups[keyOf(n)] = groups[keyOf(n)] || []).push(n));
                Object.keys(groups).sort((a, b) => a.localeCompare(b)).forEach(g => {
                    html += `<div class="${cards ? 'cards-group-label' : 'group-label'}">${esc(g)}</div>`;
                    html += groups[g].map(itemHtml).join('');
                });
            } else {
                html = list.map(itemHtml).join('');
            }
            el.innerHTML = cards ? `<div class="cards-grid">${html}</div>` : html;

            el.querySelectorAll('.note-item, .note-card').forEach(item => {
                const id = Number(item.dataset.id);
                item.addEventListener('click', e => {
                    const actBtn = e.target.closest('[data-act]');
                    if (actBtn) {
                        e.stopPropagation();
                        if (actBtn.dataset.act === 'fav') toggleFavorite(id);
                        else openMenu(actBtn, id);
                        return;
                    }
                    selectNote(id);
                });
            });
        }

        // ── Preview panel ──────────────────────────────────────────────────
        function renderInline(text) {
            let s = esc(text);
            s = s.replace(/==\{(yellow|green|pink|blue)\}(.+?)==/g, '<mark class="hl-$1">$2</mark>');
            s = s.replace(/==(.+?)==/g, '<mark class="hl-yellow">$1</mark>');
            s = s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            s = s.replace(/__(.+?)__/g, '<u>$1</u>');
            s = s.replace(/\*(.+?)\*/g, '<em>$1</em>');
            return s;
        }

        // Tiny renderer: ## headings, - bullets, ``` formula blocks, > example boxes.
        function renderContent(raw) {
            if (!raw || !raw.trim()) {
                return `<p class="pv-p" style="color:#a8a8ad;font-style:italic;">This note has no content yet. Click the pencil to start writing.</p>`;
            }
            const lines = raw.replace(/\r\n/g, '\n').split('\n');
            let html = '', bullets = [], quote = [], fence = null;

            const flushBullets = () => {
                if (bullets.length) { html += `<ul class="pv-ul">${bullets.map(b => `<li>${renderInline(b)}</li>`).join('')}</ul>`; bullets = []; }
            };
            const flushQuote = () => {
                if (quote.length) { html += `<div class="pv-example">${quote.map(renderInline).join('<br>')}</div>`; quote = []; }
            };

            for (const line of lines) {
                if (fence !== null) {
                    if (line.trim().startsWith('```')) { html += `<div class="pv-formula">${fence.map(renderInline).join('<br>')}</div>`; fence = null; }
                    else fence.push(line);
                    continue;
                }
                const t = line.trim();
                if (t.startsWith('```')) { flushBullets(); flushQuote(); fence = []; const rest = t.slice(3).trim(); if (rest) fence.push(rest); continue; }
                if (/^#{1,3}\s+/.test(t)) { flushBullets(); flushQuote(); html += `<div class="pv-h">${renderInline(t.replace(/^#{1,3}\s+/, ''))}</div>`; continue; }
                if (/^[-•]\s+/.test(t))  { flushQuote(); bullets.push(t.replace(/^[-•]\s+/, '')); continue; }
                if (t.startsWith('>'))   { flushBullets(); quote.push(t.replace(/^>\s?/, '')); continue; }
                if (t === '')            { flushBullets(); flushQuote(); continue; }
                flushBullets(); flushQuote();
                html += `<p class="pv-p">${renderInline(t)}</p>`;
            }
            if (fence !== null) html += `<div class="pv-formula">${fence.map(renderInline).join('<br>')}</div>`;
            flushBullets(); flushQuote();
            return html;
        }

        function renderPreview() {
            const body = document.getElementById('previewBody');
            const actions = document.getElementById('pvActions');
            const n = byId(state.selectedId);

            state.editing = false;
            document.getElementById('docToolbar').classList.remove('on');

            if (!n) {
                actions.style.display = 'none';
                body.innerHTML = `
                    <div class="pv-empty">
                        <i class="far fa-file-lines"></i>
                        <p>Select a note to preview it</p>
                        <span>Your note will appear here with headings, formulas and examples.</span>
                    </div>`;
                return;
            }

            actions.style.display = 'flex';
            const starIcon = document.querySelector('#pvStarBtn i');
            starIcon.className = (n.is_favorite ? 'fas' : 'far') + ' fa-star';
            document.getElementById('pvStarBtn').classList.toggle('star-on', n.is_favorite);

            const chips = (n.tag_list || []).map(t => `<span class="chip">${esc(t)}</span>`).join('');
            const trashBanner = n.trashed ? `
                <div class="pv-trash-banner">
                    <span><i class="fas fa-trash-can"></i> This note is in the Trash.</span>
                    <button onclick="restoreNote(${n.id})">Restore</button>
                </div>` : '';
            const archBadge = n.archived ? ` <span class="chip" style="background:#f2f2f4;color:#6f6f75;"><i class="fas fa-box-archive"></i> Archived</span>` : '';

            body.innerHTML = `
                ${trashBanner}
                <div class="doc-sheet">
                    <h1 class="pv-title">${esc(n.title)}</h1>
                    <div class="pv-chips">${chips}${archBadge}</div>
                    <div class="pv-meta">
                        <i class="far fa-calendar"></i>
                        <span>${esc(n.date_display || '')}</span>
                        ${n.created_human ? `<span class="dot">•</span><span>${esc(n.created_human)}</span>` : ''}
                    </div>
                    <hr class="doc-divider">
                    <div class="pv-content">${renderContent(n.content)}</div>
                </div>`;
            body.scrollTop = 0;
        }

        // ── Inline document editor ─────────────────────────────────────────
        const HL_COLORS = { yellow: '#fff1a8', green: '#c9f2c7', pink: '#ffd6e7', blue: '#cfe8ff' };
        const HL_RGB = {
            'rgb(255, 241, 168)': 'yellow',
            'rgb(201, 242, 199)': 'green',
            'rgb(255, 214, 231)': 'pink',
            'rgb(207, 232, 255)': 'blue',
        };

        function pastePlain(e) {
            e.preventDefault();
            document.execCommand('insertText', false, e.clipboardData.getData('text/plain'));
        }

        // Highlights are edited as styled spans so the browser can split /
        // override them when re-highlighting or erasing a partial selection.
        function marksToSpans(root) {
            root.querySelectorAll('mark').forEach(m => {
                const name = (m.className.match(/hl-(\w+)/) || [])[1] || 'yellow';
                const s = document.createElement('span');
                s.style.backgroundColor = HL_COLORS[name];
                while (m.firstChild) s.appendChild(m.firstChild);
                m.replaceWith(s);
            });
        }

        function enterEditMode() {
            const n = byId(state.selectedId);
            if (!n) return;
            if (n.trashed) { toast('Restore the note from Trash before editing.', 'error'); return; }

            state.editing = true;
            const body = document.getElementById('previewBody');
            document.getElementById('pvActions').style.display = 'none';
            document.getElementById('docToolbar').classList.add('on');

            const inner = (n.content && n.content.trim()) ? renderContent(n.content) : '<p class="pv-p"><br></p>';
            body.innerHTML = `
                <div class="doc-sheet editing">
                    <h1 class="pv-title" id="docTitle" contenteditable="true" spellcheck="false">${esc(n.title)}</h1>
                    <div class="pv-meta" style="margin-top:10px;">
                        <i class="fas fa-pen"></i>
                        <span>Editing — select text to format or highlight it</span>
                    </div>
                    <hr class="doc-divider">
                    <div class="pv-content" id="docEditor" contenteditable="true">${inner}</div>
                </div>`;

            const editor = document.getElementById('docEditor');
            const title = document.getElementById('docTitle');
            marksToSpans(editor);
            document.execCommand('styleWithCSS', false, true);
            editor.addEventListener('paste', pastePlain);
            title.addEventListener('paste', pastePlain);
            title.addEventListener('keydown', e => {
                if (e.key === 'Enter') { e.preventDefault(); editor.focus(); }
            });
            editor.focus();
            body.scrollTop = 0;
        }

        // Returns false (and keeps the editor open) if the user wants to stay.
        function maybeExitEdit() {
            if (!state.editing) return true;
            if (!confirm('Discard unsaved changes to this note?')) return false;
            state.editing = false;
            renderPreview();
            return true;
        }

        function applyHighlight(name) {
            document.getElementById('docEditor').focus();
            document.execCommand('styleWithCSS', false, true);
            document.execCommand('hiliteColor', false, name === 'none' ? 'transparent' : HL_COLORS[name]);
        }

        function placeCaret(el) {
            const r = document.createRange();
            r.selectNodeContents(el);
            r.collapse(false);
            const s = window.getSelection();
            s.removeAllRanges();
            s.addRange(r);
            document.getElementById('docEditor').focus();
        }

        // Convert the block containing the caret to/from heading, list,
        // formula box or example box.
        function applyBlock(kind) {
            const editor = document.getElementById('docEditor');
            const sel = window.getSelection();
            if (!sel.rangeCount) return;
            let node = sel.anchorNode;
            if (node && node.nodeType === 3) node = node.parentElement;
            if (!node || !editor.contains(node)) return;
            if (node === editor) node = editor.firstElementChild;
            if (!node) return;
            while (node.parentElement !== editor) node = node.parentElement;

            let li = null;
            if (node.tagName === 'UL') {
                let a = sel.anchorNode;
                if (a.nodeType === 3) a = a.parentElement;
                li = a.closest('li');
                if (!li) return;
            }
            const inner = (li || node).innerHTML;

            if (kind === 'ul') {
                if (li) {
                    const p = document.createElement('p');
                    p.className = 'pv-p';
                    p.innerHTML = inner || '<br>';
                    node.after(p);
                    li.remove();
                    if (!node.querySelector('li')) node.remove();
                    placeCaret(p);
                } else {
                    const ul = document.createElement('ul');
                    ul.className = 'pv-ul';
                    const l = document.createElement('li');
                    l.innerHTML = inner || '<br>';
                    ul.appendChild(l);
                    node.replaceWith(ul);
                    placeCaret(l);
                }
                return;
            }

            const map = { h: 'pv-h', formula: 'pv-formula', example: 'pv-example' };
            const cls = map[kind];
            const isAlready = !li && node.classList.contains(cls);
            const el = document.createElement(isAlready ? 'p' : 'div');
            el.className = isAlready ? 'pv-p' : cls;
            el.innerHTML = inner || '<br>';
            if (li) {
                node.after(el);
                li.remove();
                if (!node.querySelector('li')) node.remove();
            } else {
                node.replaceWith(el);
            }
            placeCaret(el);
        }

        // ── Serialize the edited document back to note markup ──────────────
        function hlNameOf(el) {
            if (el.tagName === 'MARK') return (el.className.match(/hl-(\w+)/) || [])[1] || 'yellow';
            const bg = el.style ? el.style.backgroundColor : '';
            if (!bg || bg === 'transparent' || bg === 'rgba(0, 0, 0, 0)') return null;
            return HL_RGB[bg] || 'yellow';
        }

        function serializeInline(node) {
            let out = '';
            node.childNodes.forEach(ch => {
                if (ch.nodeType === 3) { out += ch.nodeValue; return; }
                if (ch.nodeType !== 1) return;
                if (ch.tagName === 'BR') { out += '\n'; return; }
                let inner = serializeInline(ch);
                if (!inner.trim()) { out += inner; return; }
                const st = ch.style || {};
                const hl = hlNameOf(ch);
                if (hl) inner = (hl === 'yellow' ? '==' : '=={' + hl + '}') + inner + '==';
                if (ch.tagName === 'B' || ch.tagName === 'STRONG' || st.fontWeight === 'bold' || parseInt(st.fontWeight) >= 600) inner = '**' + inner + '**';
                if (ch.tagName === 'I' || ch.tagName === 'EM' || st.fontStyle === 'italic') inner = '*' + inner + '*';
                if (ch.tagName === 'U' || (st.textDecoration || '').includes('underline')) inner = '__' + inner + '__';
                out += inner;
            });
            return out;
        }

        function serializeDoc(root) {
            const parts = [];
            root.childNodes.forEach(node => {
                if (node.nodeType === 3) {
                    const t = node.nodeValue.trim();
                    if (t) parts.push(t);
                    return;
                }
                if (node.nodeType !== 1) return;
                const cls = node.classList;
                if (node.tagName === 'UL') {
                    const items = [...node.querySelectorAll(':scope > li')]
                        .map(li => serializeInline(li).replace(/\n+/g, ' ').trim())
                        .filter(Boolean)
                        .map(t => '- ' + t);
                    if (items.length) parts.push(items.join('\n'));
                } else if (cls.contains('pv-h') || /^H[1-4]$/.test(node.tagName)) {
                    const t = serializeInline(node).replace(/\n+/g, ' ').trim();
                    if (t) parts.push('## ' + t);
                } else if (cls.contains('pv-formula')) {
                    const t = serializeInline(node).trim();
                    if (t) parts.push('```\n' + t + '\n```');
                } else if (cls.contains('pv-example')) {
                    const t = serializeInline(node).trim();
                    if (t) parts.push(t.split('\n').map(l => '> ' + l.trim()).filter(l => l !== '>').join('\n'));
                } else {
                    const t = serializeInline(node).trim();
                    if (t) parts.push(t);
                }
            });
            return parts.join('\n\n');
        }

        async function saveInlineEdit() {
            const n = byId(state.selectedId);
            if (!n) return;
            const title = (document.getElementById('docTitle').textContent || '').replace(/\s+/g, ' ').trim() || n.title;
            const content = serializeDoc(document.getElementById('docEditor'));
            const btn = document.getElementById('dtSave');
            btn.disabled = true;
            try {
                const d = await api(`${ROUTES.base}/${n.id}`, {
                    method: 'POST',
                    body: JSON.stringify({ _method: 'PUT', title, content }),
                });
                Object.assign(n, d.note);
                state.editing = false;
                renderAll();
                toast('Note saved.');
            } catch {
                toast('Could not save the note.', 'error');
            } finally {
                btn.disabled = false;
            }
        }

        function renderAll() {
            const onMaterials = state.tab === 'materials';
            document.getElementById('workspace').style.display = onMaterials ? 'none' : '';
            document.getElementById('materialsWrap').style.display = onMaterials ? 'flex' : 'none';
            document.getElementById('materialsWrap').style.flexDirection = 'column';

            if (onMaterials) {
                renderMaterials();
                return;
            }
            renderFolders();
            renderList();
            // Never wipe an open document editor from a background re-render.
            if (!state.editing) renderPreview();
        }

        // ── Alumni Materials tab ────────────────────────────────────────────
        function visibleMaterials() {
            let list = MATERIALS.slice();

            if (state.matSubject) {
                list = list.filter(m => (m.subject_code || '') === state.matSubject);
            }

            if (state.search) {
                const q = state.search.toLowerCase();
                list = list.filter(m =>
                    (m.title || '').toLowerCase().includes(q) ||
                    (m.description || '').toLowerCase().includes(q) ||
                    (m.uploader_name || '').toLowerCase().includes(q) ||
                    (m.subject_code || '').toLowerCase().includes(q) ||
                    (m.subject_name || '').toLowerCase().includes(q));
            }

            // MATERIALS already arrives ordered newest-first from the server,
            // so "newest" needs no extra sort — only re-sort for the others.
            if (state.matSort === 'most_downloaded') {
                list.sort((a, b) => (b.downloads_count || 0) - (a.downloads_count || 0));
            } else if (state.matSort === 'az') {
                list.sort((a, b) => (a.title || '').localeCompare(b.title || ''));
            }

            return list;
        }

        function materialCardHtml(m) {
            return `
                <div class="mat-card" data-id="${m.id}">
                    <div class="mat-head">
                        <div class="mat-icon" style="background:${m.color};"><i class="fas ${m.icon}"></i></div>
                        <div style="flex:1;min-width:0;">
                            <div class="mat-title">${esc(m.title)}</div>
                            <div class="mat-meta">${esc((m.file_category || '').toUpperCase())} · ${esc(m.file_size || '')} · ${esc(m.created_human || '')}</div>
                        </div>
                    </div>
                    ${m.description ? `<div class="mat-desc">${esc(m.description)}</div>` : ''}
                    <div class="mat-tags">
                        ${m.subject_code ? `<span class="chip">${esc(m.subject_code)}</span>` : ''}
                        <span class="chip"><i class="fas fa-user" style="margin-right:3px;"></i>${esc(m.uploader_name)}</span>
                    </div>
                    <div class="mat-foot">
                        <span class="mat-downloads"><i class="fas fa-download"></i> ${m.downloads_count} download${m.downloads_count === 1 ? '' : 's'}</span>
                        <div class="mat-actions">
                            <button class="mat-btn mat-btn-view" data-act="view" data-id="${m.id}"><i class="far fa-eye"></i> View</button>
                            <a class="mat-btn mat-btn-download" href="${m.download_url}"><i class="fas fa-arrow-down-to-line"></i> Download</a>
                        </div>
                    </div>
                </div>`;
        }

        function renderMaterials() {
            const el = document.getElementById('materialsList');
            const list = visibleMaterials();

            if (!list.length) {
                el.innerHTML = `
                    <div class="list-empty">
                        <i class="far fa-folder-open"></i>
                        <p>No materials yet</p>
                        <span>Check back soon — alumni will start sharing reviewers and notes here.</span>
                    </div>`;
                return;
            }

            el.innerHTML = `<div class="materials-grid">${list.map(materialCardHtml).join('')}</div>`;

            el.querySelectorAll('[data-act="view"]').forEach(btn => {
                btn.addEventListener('click', () => openMaterialModal(Number(btn.dataset.id)));
            });
        }

        // Office/Google-viewable extensions vs. natively-renderable ones.
        const OFFICE_CATEGORIES = ['word', 'excel', 'powerpoint'];

        // Office Online can only preview a file if it can fetch the URL itself,
        // so it's useless against localhost/private-network hosts — skip straight
        // to the download fallback there instead of showing a dead iframe.
        const IS_PUBLIC_HOST = (() => {
            const h = window.location.hostname;
            return h !== 'localhost' && h !== '127.0.0.1' &&
                !/^192\.168\.|^10\.|^172\.(1[6-9]|2[0-9]|3[0-1])\.|\.test$|\.local$/.test(h);
        })();

        function materialPreviewBody(m) {
            if (!m.view_url) {
                return `
                    <div class="mat-preview-fallback">
                        <i class="fas fa-file-circle-exclamation"></i>
                        <p>Preview unavailable</p>
                        <span>Use the Download button to view this file.</span>
                    </div>`;
            }
            if (m.file_category === 'pdf') {
                return `<iframe src="${esc(m.view_url)}" title="${esc(m.title)}"></iframe>`;
            }
            if (m.file_category === 'image') {
                return `<img src="${esc(m.view_url)}" alt="${esc(m.title)}">`;
            }
            if (m.file_category === 'text') {
                return `<iframe src="${esc(m.view_url)}" title="${esc(m.title)}"></iframe>`;
            }
            if (OFFICE_CATEGORIES.includes(m.file_category) && IS_PUBLIC_HOST) {
                // Best-effort Office Online viewer — needs the file URL to be
                // publicly reachable. Falls back to a download prompt below it.
                const viewerUrl = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(m.view_url);
                return `<iframe src="${viewerUrl}" title="${esc(m.title)}"></iframe>`;
            }
            if (OFFICE_CATEGORIES.includes(m.file_category)) {
                return `
                    <div class="mat-preview-fallback">
                        <i class="fas ${m.icon}" style="color:${m.color};"></i>
                        <p>${esc(m.original_name || m.title)}</p>
                        <span>Preview isn't available on a local/private server — Office Online needs a public URL. Use Download to open it.</span>
                    </div>`;
            }
            return `
                <div class="mat-preview-fallback">
                    <i class="fas ${m.icon}" style="color:${m.color};"></i>
                    <p>${esc(m.original_name || m.title)}</p>
                    <span>This file type can't be previewed in-browser. Use Download to open it.</span>
                </div>`;
        }

        function openMaterialModal(id) {
            const m = MATERIALS.find(x => x.id === id);
            if (!m) return;

            document.getElementById('matModalIcon').style.background = m.color;
            document.getElementById('matModalIcon').innerHTML = `<i class="fas ${m.icon}"></i>`;
            document.getElementById('matModalTitle').textContent = m.title;
            document.getElementById('matModalSub').textContent =
                `${(m.file_category || '').toUpperCase()} · ${m.file_size || ''} · Uploaded by ${m.uploader_name}`;
            document.getElementById('matModalDownload').href = m.download_url;
            document.getElementById('matModalBody').innerHTML = materialPreviewBody(m);
            document.getElementById('matModal').classList.add('open');
        }

        function closeMaterialModal() {
            document.getElementById('matModal').classList.remove('open');
            document.getElementById('matModalBody').innerHTML = '';
        }

        // ── Selection (counts as a review) ─────────────────────────────────
        function closePreview() {
            state.selectedId = null;
            state.editing = false;
            document.getElementById('workspace').classList.remove('preview-open');
            renderList();
            renderPreview();
        }

        function selectNote(id) {
            if (state.editing && id !== state.selectedId && !maybeExitEdit()) return;
            state.selectedId = id;
            document.getElementById('workspace').classList.add('preview-open');
            renderList();
            renderPreview();

            const n = byId(id);
            if (n && !n.trashed) {
                // Count the read server-side; refresh local counters silently.
                fetch(`${ROUTES.base}/${id}?read=1`, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(d => { if (d.ok) Object.assign(n, d.note); })
                    .catch(() => {});
            }
        }

        // ── Print / Download (A4 paper formatting) ─────────────────────────
        function slugify(s) {
            return (s || 'note').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 60) || 'note';
        }

        // Standalone A4 document of a note (used by both Print and Download).
        function printableHtml(n) {
            const chips = (n.tag_list || []).map(t => `<span class="tag">${esc(t)}</span>`).join('');
            const subject = n.subject_code
                ? n.subject_code + (n.subject_name ? ' — ' + n.subject_name : '')
                : 'General';
            const printedOn = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            return `<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>${esc(n.title)}</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    @page { size: A4; margin: 20mm 18mm; }
    * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    body { font-family: 'Poppins', Arial, sans-serif; color: #222; line-height: 1.7; font-size: 11.5pt; margin: 0; }
    .head { border-bottom: 2.5px solid #7B1D1D; padding-bottom: 12px; margin-bottom: 20px; }
    h1 { font-size: 20pt; margin: 0 0 6px; color: #1b1b1e; line-height: 1.25; }
    .meta { font-size: 9.5pt; color: #777; }
    .tag {
        display: inline-block; border: 1px solid #ddd; border-radius: 12px;
        padding: 1px 10px; font-size: 8.5pt; color: #555;
        margin: 0 4px 4px 0; background: #f7f7f8;
    }
    .pv-h { color: #c0392b; font-size: 13pt; font-weight: 600; margin: 18pt 0 6pt; }
    .pv-p { margin: 0 0 8pt; }
    .pv-formula {
        background: #f3f3f5; border-radius: 8px; padding: 12pt 14pt;
        text-align: center; font-weight: 500; margin: 6pt 0 12pt;
    }
    .pv-ul { margin: 4pt 0 12pt; padding: 0; list-style: none; }
    .pv-ul li { position: relative; padding-left: 16pt; margin-bottom: 6pt; }
    .pv-ul li::before {
        content: ''; position: absolute; left: 4pt; top: 7pt;
        width: 5pt; height: 5pt; border-radius: 50%; background: #c0392b;
    }
    .pv-example { background: #fdf6e0; border-radius: 8px; padding: 12pt 14pt; margin: 6pt 0 12pt; }
    mark { padding: 0 2px; border-radius: 2px; }
    .hl-yellow { background: #fff1a8; } .hl-green { background: #c9f2c7; }
    .hl-pink { background: #ffd6e7; } .hl-blue { background: #cfe8ff; }
    .pv-h, .pv-formula, .pv-example { break-inside: avoid; }
    .foot { margin-top: 26pt; font-size: 8.5pt; color: #aaa; border-top: 1px solid #eee; padding-top: 8pt; }
</style>
</head>
<body>
    <div class="head">
        <h1>${esc(n.title)}</h1>
        <div class="meta">${esc(subject)} &nbsp;&bull;&nbsp; ${esc(n.date_display || '')}</div>
        ${chips ? `<div style="margin-top:8px;">${chips}</div>` : ''}
    </div>
    <div class="content">${renderContent(n.content)}</div>
    <div class="foot">CPACE Review Notes &mdash; ${printedOn}</div>
</body>
</html>`;
        }

        // Opens the browser print dialog (user can print or "Save as PDF")
        // through a hidden iframe — no extra tab or full-page view appears.
        function printNote(id) {
            const n = byId(id);
            if (!n) return;

            document.getElementById('printFrame')?.remove();
            const frame = document.createElement('iframe');
            frame.id = 'printFrame';
            frame.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;visibility:hidden;';
            document.body.appendChild(frame);

            const doc = frame.contentWindow.document;
            doc.open();
            doc.write(printableHtml(n));
            doc.close();

            // Small delay so styles/fonts settle before the dialog opens.
            setTimeout(() => {
                frame.contentWindow.focus();
                frame.contentWindow.onafterprint = () => frame.remove();
                frame.contentWindow.print();
            }, 500);
        }

        // Downloads the note as a Word-compatible .doc file.
        function downloadNote(id) {
            const n = byId(id);
            if (!n) return;
            const blob = new Blob(['﻿' + printableHtml(n)], { type: 'application/msword' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = slugify(n.title) + '.doc';
            document.body.appendChild(a);
            a.click();
            a.remove();
            setTimeout(() => URL.revokeObjectURL(a.href), 2000);
            toast('Note downloaded.');
        }

        // ── Context menu ───────────────────────────────────────────────────
        function openMenu(anchor, id) {
            const n = byId(id);
            if (!n) return;
            const menu = document.getElementById('ctxMenu');

            let items = '';
            if (n.trashed) {
                items = `
                    <button data-cmd="restore"><i class="fas fa-rotate-left"></i> Restore</button>
                    <div class="ctx-sep"></div>
                    <button data-cmd="delete" class="danger"><i class="far fa-trash-can"></i> Delete Permanently</button>`;
            } else {
                items = `
                    <button data-cmd="edit"><i class="fas fa-pencil"></i> Edit Note</button>
                    <button data-cmd="details"><i class="fas fa-sliders"></i> Edit Details &amp; Tags</button>
                    <button data-cmd="fav"><i class="${n.is_favorite ? 'fas' : 'far'} fa-star"></i> ${n.is_favorite ? 'Remove from Favorites' : 'Add to Favorites'}</button>
                    <button data-cmd="archive"><i class="fas fa-box-archive"></i> ${n.archived ? 'Unarchive' : 'Archive'}</button>
                    <div class="ctx-sep"></div>
                    <button data-cmd="print"><i class="fas fa-print"></i> Print / Save as PDF</button>
                    <button data-cmd="download"><i class="fas fa-download"></i> Download (Word)</button>
                    <div class="ctx-sep"></div>
                    <button data-cmd="trash" class="danger"><i class="far fa-trash-can"></i> Move to Trash</button>`;
            }
            menu.innerHTML = items;

            menu.querySelectorAll('button').forEach(b => b.addEventListener('click', () => {
                closeMenu();
                const cmd = b.dataset.cmd;
                if (cmd === 'edit') { if (state.selectedId !== id) selectNote(id); if (state.selectedId === id) enterEditMode(); }
                if (cmd === 'details') openEditModal(id);
                if (cmd === 'fav') toggleFavorite(id);
                if (cmd === 'archive') toggleArchive(id);
                if (cmd === 'print') printNote(id);
                if (cmd === 'download') downloadNote(id);
                if (cmd === 'trash') trashNote(id);
                if (cmd === 'restore') restoreNote(id);
                if (cmd === 'delete') deleteForever(id);
            }));

            const r = anchor.getBoundingClientRect();
            menu.classList.add('open');
            const mw = menu.offsetWidth, mh = menu.offsetHeight;
            let x = Math.min(r.left, window.innerWidth - mw - 12);
            let y = r.bottom + 6;
            if (y + mh > window.innerHeight - 12) y = r.top - mh - 6;
            menu.style.left = x + 'px';
            menu.style.top = y + 'px';
        }
        function closeMenu() { document.getElementById('ctxMenu').classList.remove('open'); }

        // ── Actions ────────────────────────────────────────────────────────
        async function api(url, opts = {}) {
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                ...opts,
            });
            if (!res.ok) throw res;
            return res.json();
        }

        async function toggleFavorite(id) {
            const n = byId(id);
            try {
                const d = await api(`${ROUTES.base}/${id}/favorite`, { method: 'POST' });
                Object.assign(n, d.note || { is_favorite: d.is_favorite });
                renderList();
                if (!state.editing) renderPreview();
                toast(n.is_favorite ? 'Added to favorites.' : 'Removed from favorites.');
            } catch { toast('Could not update favorite.', 'error'); }
        }

        async function toggleArchive(id) {
            const n = byId(id);
            try {
                const d = await api(`${ROUTES.base}/${id}/archive`, { method: 'POST' });
                Object.assign(n, d.note);
                renderAll();
                toast(n.archived ? 'Note archived.' : 'Note unarchived.');
            } catch { toast('Could not archive the note.', 'error'); }
        }

        async function trashNote(id) {
            const n = byId(id);
            if (!confirm(`Move "${n.title}" to Trash?`)) return;
            try {
                const d = await api(`${ROUTES.base}/${id}`, { method: 'POST', body: JSON.stringify({ _method: 'DELETE' }) });
                if (d.note) Object.assign(n, d.note); else n.trashed = true;
                if (state.selectedId === id) closePreview();
                renderAll();
                toast('Note moved to Trash.');
            } catch { toast('Could not delete the note.', 'error'); }
        }

        async function restoreNote(id) {
            const n = byId(id);
            try {
                const d = await api(`${ROUTES.base}/${id}/restore`, { method: 'POST' });
                Object.assign(n, d.note);
                renderAll();
                toast('Note restored.');
            } catch { toast('Could not restore the note.', 'error'); }
        }

        async function deleteForever(id) {
            const n = byId(id);
            if (!confirm(`Permanently delete "${n.title}"? This cannot be undone.`)) return;
            try {
                await api(`${ROUTES.base}/${id}`, { method: 'POST', body: JSON.stringify({ _method: 'DELETE' }) });
                NOTES.splice(NOTES.indexOf(n), 1);
                if (state.selectedId === id) closePreview();
                renderAll();
                toast('Note permanently deleted.');
            } catch { toast('Could not delete the note.', 'error'); }
        }

        // ── Modal (create / edit) ──────────────────────────────────────────
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

        function openCreateModal() {
            clearErrors();
            document.getElementById('noteModalTitle').textContent = 'New Note';
            document.getElementById('noteForm').reset();
            document.getElementById('noteId').value = '';
            // Pre-select the active folder's subject.
            if (typeof state.folder === 'number') document.getElementById('f_subject').value = state.folder;
            filterTopicOptions();
            openModal('noteModal');
            setTimeout(() => document.getElementById('f_title').focus(), 100);
        }

        function openEditModal(id) {
            const n = byId(id);
            if (!n) return;
            if (!maybeExitEdit()) return;
            clearErrors();
            document.getElementById('noteModalTitle').textContent = 'Edit Note';
            document.getElementById('noteId').value = n.id;
            document.getElementById('f_title').value = n.title || '';
            document.getElementById('f_subject').value = n.subject_id || '';
            filterTopicOptions(n.topic_id);
            document.getElementById('f_tags').value = n.tags || '';
            document.getElementById('f_content').value = n.content || '';
            openModal('noteModal');
        }

        async function saveNote(e) {
            e.preventDefault();
            clearErrors();
            const id = document.getElementById('noteId').value;
            const btn = document.getElementById('noteSaveBtn');
            const isEdit = !!id;

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
                const res = await fetch(isEdit ? `${ROUTES.base}/${id}` : ROUTES.store, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify(payload),
                });

                if (res.status === 422) {
                    const data = await res.json();
                    Object.entries(data.errors || {}).forEach(([field, msgs]) => {
                        const el = document.getElementById('err_' + field);
                        if (el) { el.textContent = msgs[0]; el.style.display = 'block'; }
                    });
                    return;
                }
                if (!res.ok) throw new Error();

                const data = await res.json();
                if (isEdit) {
                    Object.assign(byId(Number(id)), data.note);
                } else {
                    NOTES.unshift(data.note);
                    state.selectedId = data.note.id;
                    document.getElementById('workspace').classList.add('preview-open');
                }
                closeModal('noteModal');
                renderAll();
                toast(isEdit ? 'Note updated.' : 'Note created.');
            } catch {
                toast('Something went wrong. Please try again.', 'error');
            } finally {
                btn.disabled = false;
            }
        }

        // ── Toast ──────────────────────────────────────────────────────────
        let toastTimer;
        function toast(msg, type = 'success') {
            const t = document.getElementById('toast');
            document.getElementById('toastMsg').textContent = msg;
            t.className = 'toast ' + type;
            t.querySelector('i').className = type === 'success' ? 'fas fa-circle-check' : 'fas fa-circle-exclamation';
            void t.offsetWidth;
            t.classList.add('show');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => t.classList.remove('show'), 2600);
        }

        // ── Wire-up ────────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            // Tabs
            document.querySelectorAll('#tabsBar .tab-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (!maybeExitEdit()) return;
                    document.querySelectorAll('#tabsBar .tab-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    state.tab = btn.dataset.tab;
                    if (state.folder === 'trash') state.folder = null;
                    renderAll();
                });
            });

            // Sort + density
            document.getElementById('sortSelect').addEventListener('change', e => {
                state.sort = e.target.value;
                renderList();
            });
            document.getElementById('densityBtn').addEventListener('click', () => {
                state.view = state.view === 'cards' ? 'rows' : 'cards';
                const icon = document.querySelector('#densityBtn i');
                icon.className = state.view === 'cards' ? 'fas fa-list-ul' : 'fas fa-grip';
                document.getElementById('densityBtn').title =
                    state.view === 'cards' ? 'Switch to list view' : 'Switch to card view';
                renderList();
            });

            // Search
            let searchTimer;
            document.getElementById('globalSearch').addEventListener('input', e => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    state.search = e.target.value.trim();
                    state.tab === 'materials' ? renderMaterials() : renderList();
                }, 200);
            });

            // New note (+), preview toolbar
            document.getElementById('newNoteBtn').addEventListener('click', openCreateModal);
            document.getElementById('pvEditBtn').addEventListener('click', () => state.selectedId && enterEditMode());
            document.getElementById('pvStarBtn').addEventListener('click', () => state.selectedId && toggleFavorite(state.selectedId));
            document.getElementById('pvPrintBtn').addEventListener('click', () => state.selectedId && printNote(state.selectedId));
            document.getElementById('pvKebabBtn').addEventListener('click', e => {
                e.stopPropagation();
                if (state.selectedId) openMenu(e.currentTarget, state.selectedId);
            });
            document.getElementById('pvCloseBtn').addEventListener('click', () => {
                if (!maybeExitEdit()) return;
                closePreview();
            });

            // Document editor toolbar
            const docToolbar = document.getElementById('docToolbar');
            docToolbar.addEventListener('mousedown', e => {
                // Keep the editor's text selection while clicking format buttons.
                if (!e.target.closest('#dtSave, #dtCancel')) e.preventDefault();
            });
            docToolbar.addEventListener('click', e => {
                const b = e.target.closest('button');
                if (!b || !state.editing) return;
                if (b.id === 'dtSave') return saveInlineEdit();
                if (b.id === 'dtCancel') { state.editing = false; renderPreview(); return; }
                if (b.dataset.cmd) document.execCommand(b.dataset.cmd, false, null);
                else if (b.dataset.hl) applyHighlight(b.dataset.hl);
                else if (b.dataset.block) applyBlock(b.dataset.block);
            });

            // Modal + menus
            document.getElementById('noteForm').addEventListener('submit', saveNote);
            document.querySelectorAll('.modal-overlay').forEach(ov =>
                ov.addEventListener('click', e => { if (e.target === ov) closeModal(ov.id); }));
            document.addEventListener('click', closeMenu);
            document.addEventListener('keydown', e => {
                if (e.key !== 'Escape') return;
                if (document.getElementById('matModal').classList.contains('open')) { closeMaterialModal(); return; }
                const hadMenu = document.getElementById('ctxMenu').classList.contains('open');
                const hadModal = document.querySelectorAll('.modal-overlay.open').length > 0;
                closeMenu();
                document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
                // Nothing else was open → Escape closes the note preview.
                if (!hadMenu && !hadModal && !state.editing && state.selectedId !== null) closePreview();
            });

            // Alumni Materials preview modal
            document.getElementById('matModalClose').addEventListener('click', closeMaterialModal);
            document.getElementById('matModal').addEventListener('click', e => {
                if (e.target === e.currentTarget) closeMaterialModal();
            });

            // Alumni Materials filters
            document.getElementById('matSubjectFilter').addEventListener('change', e => {
                state.matSubject = e.target.value;
                renderMaterials();
            });
            document.getElementById('matSortSelect').addEventListener('change', e => {
                state.matSort = e.target.value;
                renderMaterials();
            });
            window.addEventListener('scroll', closeMenu, true);

            // Profile dropdown
            const profileBtn = document.getElementById('profileBtn');
            const profileDropdown = document.getElementById('profileDropdown');
            if (profileBtn && profileDropdown) {
                profileBtn.addEventListener('click', e => { e.stopPropagation(); profileDropdown.classList.toggle('active'); });
                document.addEventListener('click', () => profileDropdown.classList.remove('active'));
                profileDropdown.addEventListener('click', e => e.stopPropagation());
            }

            // Initial render — the preview stays hidden until a note is clicked.
            renderAll();
        });
    </script>
    @include('partials.global-search')
</body>
</html>
