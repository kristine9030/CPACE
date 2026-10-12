{{--
    Phone-width fixes shared by every student page (loaded from
    student-bottom-nav, so it lands after each page's own <style> and wins ties).
    Layout/sizing only - nothing here changes behaviour.
--}}
<style>
    @media (max-width: 768px) {
        /* Nothing may push the page sideways; media always fits its column. */
        html, body { max-width: 100%; overflow-x: hidden; }
        img, video, canvas, svg { max-width: 100%; }
        .main-content { min-width: 0; max-width: 100%; box-sizing: border-box; }
        .main-content table { max-width: 100%; }

        /* Room for the floating tab bar under the last element on every page. */
        .main-content { padding-bottom: calc(var(--bn-h, 72px) + 34px + env(safe-area-inset-bottom)) !important; }
        body:has(.summary-bar) .main-content { padding-bottom: calc(var(--bn-h, 72px) + 150px + env(safe-area-inset-bottom)) !important; }

        /* Practice-quiz summary bar: a compact floating card above the tab bar. */
        .summary-bar {
            left: 12px !important; right: 12px;
            bottom: calc(var(--bn-h, 72px) + 20px + max(10px, env(safe-area-inset-bottom)));
            border: 1px solid #E8ECF0; border-radius: 20px; padding: 10px 12px; gap: 8px;
            box-shadow: 0 8px 26px rgba(0,0,0,.16); z-index: 1000;
        }
        .summary-chips { flex-wrap: nowrap; overflow-x: auto; gap: 8px; scrollbar-width: none; padding-bottom: 2px; }
        .summary-chips::-webkit-scrollbar { display: none; }
        .sum-chip { flex: 0 0 auto; padding: 6px 10px; font-size: 12px; }
        .summary-cta { white-space: normal; justify-content: space-between; font-size: 12px; }

        /* iOS zooms the whole page when a field under 16px takes focus. */
        input:not([type=checkbox]):not([type=radio]):not([type=range]):not([type=color]):not([type=file]),
        select, textarea { font-size: 16px !important; }

        /* Comfortable thumb targets. */
        .main-content .btn, .main-content select, .main-content input[type=submit],
        .main-content .view-all-btn, .main-content .today-btn, .main-content .add-folder-btn {
            min-height: 40px; min-width: 40px; display: inline-flex; align-items: center; justify-content: center;
        }
        .f-caret, .card-menu-btn { position: relative; }
        .f-caret::after, .card-menu-btn::after { content: ''; position: absolute; inset: -11px -9px; }
        .main-content .icon-btn { min-width: 40px; min-height: 40px; }

        /* Page header: title, then a search that takes the full row and icons beside it. */
        .header { flex-direction: column; align-items: stretch; gap: 12px; }
        .header-right { width: 100%; flex-wrap: wrap; gap: 12px; }
        .search-box { flex: 1 1 100%; min-width: 0; }
        .search-box input { width: 100%; box-sizing: border-box; }


        /* Header tools row (count chip / filters + messages + bell + avatar):
           search takes its own full row, the rest share ONE row - the avatar
           never drops to a line of its own. */
        .top-bar { flex-direction: column; align-items: stretch; gap: 12px; }
        .top-bar-right, .header-right {
            display: flex; flex-wrap: wrap; align-items: center; gap: 10px; width: 100%;
        }
        .top-bar-right > .gs-wrap, .top-bar-right > .search-wrap, .header-right > .search-box,
        .header-right > .gs-wrap { order: -1; flex: 1 1 100%; width: 100%; margin: 0; }
        .top-bar-right > .gs-wrap input, .search-wrap input { width: 100%; box-sizing: border-box; }
        .header-count, .header-right > .range-pill, .header-right > .filter-chip {
            flex: 1 1 0; min-width: 0; margin-right: auto; white-space: normal; line-height: 1.3;
            font-size: 12.5px; padding: 9px 14px; border-radius: 22px;
        }
        .top-bar-right > .notif-btn, .top-bar-right > .header-dropdown-wrap,
        .header-right > .notif-btn, .header-right > .header-dropdown-wrap,
        .header-right > .header-icons { flex: 0 0 auto; }
        .header-right > .header-icons { margin-left: auto; flex-wrap: nowrap; }
        /* When there is no chip to push them, icons still sit on the right. */
        .top-bar-right > .notif-btn:first-of-type, .header-right > .notif-btn:first-of-type { margin-left: auto; }


        /* The app header already carries search, messages, notifications and the
           profile - never repeat them inside a page header. */
        .top-bar-right .gs-wrap, .top-bar-right .search-wrap, .top-bar-right .search-box, .top-bar-right .notif-btn,
        .top-bar-right .header-dropdown-wrap, .top-bar-right .profile-avatar,
        .header-right .gs-wrap, .header-right .search-wrap, .header-right .search-box, .header-right .notif-btn,
        .header-right .header-dropdown-wrap, .header-right .profile-avatar, .header-right .header-icons,
        .page-header-right .gs-wrap, .page-header-right .search-wrap, .page-header-right .notif-btn,
        .page-header-right .header-dropdown-wrap, .page-header-right .profile-avatar,
        .messages-topbar-right .gs-wrap, .messages-topbar-right .search-wrap, .messages-topbar-right .notif-btn,
        .messages-topbar-right .header-dropdown-wrap, .messages-topbar-right .profile-avatar,
        .topbar-right .gs-wrap, .topbar-right .topbar-search, .topbar-right .topbar-avatar-wrap { display: none !important; }
        .top-bar-right:empty, .header-right:empty, .messages-topbar-right:empty { display: none; }
        .messages-topbar { padding: 4px 16px 10px; }

        /* Chat thread open: it owns the whole screen, so the app chrome steps aside. */
        body.thread-open .mobile-app-header, body.thread-open .bottom-nav { display: none !important; }
        body.thread-open .main-content { padding: 0 !important; }
        body.thread-open .messages-topbar { display: none; }
        .thread-form { gap: 8px; padding: 10px 12px calc(10px + env(safe-area-inset-bottom)); }
        .thread-form input[type=text] { flex: 1 1 0; min-width: 0; }
        .thread-form .thread-form-icon, .thread-form .send-btn { flex: 0 0 auto; }

        /* Modals / dialogs never exceed the screen. */
        .modal, .modal-content, [class*="modal-box"], [class*="-modal"] > div { max-width: calc(100vw - 24px); }

        /* ── Dashboard ── */
        .metrics-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .metric-card {
            flex-direction: column-reverse; align-items: flex-start; justify-content: flex-end;
            gap: 4px; padding: 14px; min-height: 0;
        }
        .metric-illust { align-self: flex-end; width: 54px; height: 54px; margin: 4px 0 -2px; }
        .metric-number-lg { font-size: 28px; }
        .metric-label-lg { font-size: 12.5px; }
        .metric-sub { font-size: 11px; }
        .card-link { display: inline-block; padding: 8px 0; }




        /* ── Practice Quizzes: compact step cards ── */
        .pipeline { padding: 16px 12px; border-radius: 18px; }
        .pipeline-header { margin-bottom: 14px; }
        .pipeline-header h2 { font-size: 17px; }
        .pipeline-header p { font-size: 12px; }
        .step-card { padding: 16px 14px 14px; border-radius: 18px; margin-bottom: 12px; }
        .step-head { gap: 10px; margin-bottom: 4px; }
        .step-badge { width: 30px; height: 30px; font-size: 14px; border-radius: 9px; }
        .step-title { font-size: 15px; }
        .step-desc { font-size: 12px; line-height: 1.5; margin: 2px 0 12px; }
        .choose-mode-grid, .session-type-row { gap: 8px; }
        .mode-card, .stype-card { padding: 10px 12px; gap: 10px; border-radius: 12px; border-width: 1.5px; }
        .mode-icon, .stype-icon { width: 34px; height: 34px; flex: 0 0 34px; font-size: 14px; border-radius: 10px; }
        .mode-title, .stype-title { font-size: 13px; margin-bottom: 1px; }
        .mode-description, .stype-desc { font-size: 11.5px; line-height: 1.4; }
        .stype-badge { margin-top: 5px; padding: 2px 8px; font-size: 10.5px; }
        .live-room-toggle { margin-top: 12px; padding: 10px 12px; gap: 10px; border-radius: 12px; }
        .lrt-icon { width: 34px; height: 34px; font-size: 14px; border-radius: 10px; }
        .lrt-title { font-size: 13px; }
        .lrt-desc { font-size: 11.5px; margin-top: 2px; line-height: 1.4; }
        .length-row { gap: 8px; }
        .length-options { display: flex; flex-wrap: wrap; gap: 6px; }
        .length-chip { min-width: 46px; padding: 8px 12px; font-size: 13px; border-radius: 10px; border-width: 1.5px; }
        .length-custom { height: 40px; padding: 0 12px; font-size: 16px; width: 110px; border-radius: 10px; }
        .subject-lock-note { padding: 10px 12px; font-size: 12px; border-radius: 12px; margin-bottom: 10px; }
        .subject-toolbar { margin-bottom: 10px; gap: 8px; }
        .quick-btn { padding: 8px 12px; font-size: 12px; border-radius: 10px; min-height: 38px; }
        .selected-count { font-size: 12px; }
        .subject-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 8px; }
        .subject-select-card { padding: 12px 10px; border-radius: 12px; border-width: 1.5px; }
        .subject-select-icon { width: 38px; height: 38px; margin-bottom: 8px; font-size: 16px; border-radius: 10px; }
        .subject-select-name { font-size: 14px; margin-bottom: 2px; }
        .subject-select-full { font-size: 10.5px; margin-bottom: 8px; min-height: 0; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .questions-count { font-size: 10.5px; padding: 3px 9px; }
        .subject-start-hint { display: none; }
        .rail-card { padding: 14px; border-radius: 16px; }


        /* Practice Quizzes right rail: donut beside its legend, stats in one tidy row. */
        .rail-card { padding: 14px; border-radius: 16px; margin-bottom: 12px; }
        .rail-title { font-size: 14px; margin-bottom: 12px; }
        .rail-card:has(.mastery-circle) { display: grid; grid-template-columns: auto minmax(0, 1fr); column-gap: 16px; align-items: center; }
        .rail-card:has(.mastery-circle) > .rail-title { grid-column: 1 / -1; }
        .rail-card:has(.mastery-circle) > .mastery-circle { grid-column: 1; grid-row: 2 / span 3; }
        .rail-card:has(.mastery-circle) > .stat-item { grid-column: 2; }
        .rail-card:has(.mastery-circle) > .stats-grid-sidebar { grid-column: 1 / -1; }
        .mastery-circle { width: 104px; height: 104px; margin: 0; }
        .mastery-circle-inner { width: 84px; height: 84px; }
        .mastery-percentage { font-size: 22px; line-height: 1.1; }
        .mastery-label { font-size: 9.5px; text-align: center; line-height: 1.2; }
        .rail-card .stat-item { margin-bottom: 8px; gap: 8px; font-size: 12.5px; }
        .rail-card .stat-item .stat-dot { width: 9px; height: 9px; flex: 0 0 auto; }
        .rail-card .stat-item .stat-value { font-size: 15px; font-weight: 700; }
        .stats-grid-sidebar { margin-top: 10px; padding-top: 12px; gap: 10px; }
        .rail-card .stat-box-value { font-size: 18px; margin-bottom: 2px; }
        .rail-card .stat-box-label { font-size: 9.5px; }
        .rail-history-item { padding: 10px 0; }
        .rail-hist-title { font-size: 12.5px; }
        .rail-hist-score { font-size: 14px; }
        .rail-history-btn { padding: 10px; font-size: 12.5px; }


        /* Community on phones: people to chat with live on the Messages page, but the
           Top Contributors card still shows (below the feed); the desktop chat popups do not. */
        .contacts-rail { display: block !important; }
        .contacts-rail > .card:nth-of-type(2) { display: none; }

        /* A little air between the app header and the first card. */
        .main-content { padding-top: 90px !important; }

        /* Dashboard: Study Streak + Quote become slim cards (text left, small emoji right). */
        .bottom-grid { gap: 10px !important; }
        .streak-card, .quote-card { flex-direction: row !important; align-items: center; justify-content: space-between; gap: 12px !important; padding: 14px 16px !important; border-radius: 16px; }
        .streak-card:hover, .quote-card:hover { transform: none; }
        .streak-card > div:last-child, .quote-card > div:last-child { font-size: 32px !important; opacity: .45 !important; flex: 0 0 auto; line-height: 1; letter-spacing: -4px; }
        .streak-title { font-size: 13px; margin-bottom: 6px; }
        .streak-num-row { margin-bottom: 2px; }
        .streak-num { font-size: 32px; }
        .streak-unit { font-size: 14px; }
        .streak-sub { font-size: 12px; }
        .quote-marks { font-size: 28px; margin-bottom: 4px; }
        .quote-text { font-size: 13px; line-height: 1.5; margin-bottom: 6px; }
        .quote-author { font-size: 12px; }

        /* Dashboard "today" card: an app-style date tile (month over day) beside the live clock. */
        .today-card {
            display: grid; grid-template-columns: auto minmax(0, 1fr); grid-template-rows: auto 1fr; column-gap: 16px; align-items: stretch;
            padding: 14px 16px; border-radius: 22px; text-align: left; overflow: hidden; position: relative;
            background: linear-gradient(135deg, #ffffff 0%, #fbeeee 100%); border: 1px solid #f1dede;
            box-shadow: 0 10px 28px rgba(123,29,29,.12);
        }
        .today-card::after {
            content: ''; position: absolute; right: -30px; top: -30px; width: 120px; height: 120px; border-radius: 50%;
            background: radial-gradient(circle, rgba(192,57,43,.12), rgba(192,57,43,0) 70%); pointer-events: none;
        }
        .cal-body, .cal-hero { display: contents; }
        .cal-top {
            grid-column: 1; grid-row: 1; width: 96px; padding: 8px 0 7px; border-radius: 16px 16px 0 0; white-space: nowrap;
            font-size: 10px; letter-spacing: .9px; font-weight: 700; text-align: center;
        }
        .cal-rings::before, .cal-rings::after { display: none; }
        .cal-daynum {
            grid-column: 1; grid-row: 2; width: 96px; box-sizing: border-box; padding: 4px 0 8px; text-align: center;
            font-size: 40px; line-height: 1.1; background: #fff; border: 1px solid #efd9d9; border-top: none;
            border-radius: 0 0 16px 16px; box-shadow: 0 6px 14px rgba(123,29,29,.10);
        }
        .cal-weekday {
            grid-column: 2; grid-row: 1; align-self: end; margin: 0 0 2px; font-size: 12px; font-weight: 700;
            letter-spacing: 2px; text-transform: uppercase; color: #7B1D1D;
        }
        .cal-clock {
            grid-column: 2; grid-row: 2; align-self: start; margin: 0; padding: 0; border: none; text-align: left;
            font-size: 31px; font-weight: 800; letter-spacing: .2px; line-height: 1.1; color: #1e293b; white-space: nowrap;
        }
        .cal-clock .ampm { font-size: 13px; font-weight: 700; margin-left: 6px; letter-spacing: 1px; color: #c0392b; }

        /* Flip-clock countdown: size to the card instead of fixed px. */
        .countdown-grid { gap: 6px; width: 100%; align-items: flex-start; }
        .cd-unit { flex: 1 1 0; min-width: 0; gap: 8px; }
        .cd-flip {
            width: 100%; height: auto; aspect-ratio: 1 / 1.05;
            font-size: clamp(24px, 9vw, 40px); letter-spacing: 0; border-radius: 11px;
        }
        .cd-flip::after {
            left: -3px; right: -3px; width: auto; height: 7px; box-shadow: none; transform: translateY(-50%);
            background:
                radial-gradient(circle at 3.5px 50%, rgba(0,0,0,.5) 3.5px, transparent 4px),
                radial-gradient(circle at calc(100% - 3.5px) 50%, rgba(0,0,0,.5) 3.5px, transparent 4px);
            border-radius: 0;
        }
        .cd-lbl { font-size: 10px; letter-spacing: 1.5px; }
        .cd-sep { margin-top: 0; align-self: center; gap: 6px; padding-bottom: 20px; }
        .cd-sep span { width: 5px; height: 5px; }
        .exam-hero { padding: 20px 18px; }
        .exam-hero-greeting { font-size: 24px; }
        .exam-hero-target { font-size: 14px; margin-top: 16px; }


        /* ── Performance ── */
        .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 12px; }
        .kpi-card {
            flex-direction: column-reverse; align-items: flex-start; justify-content: flex-end;
            gap: 4px; padding: 14px; min-height: 0;
        }
        .kpi-card:hover { transform: none; }
        .kpi-illust { align-self: flex-end; width: 54px !important; height: 54px !important; margin: 4px 0 -2px; }
        .kpi-card .kpi-number { font-size: 28px; }
        .kpi-card .kpi-number small { font-size: 14px; }
        .kpi-card .kpi-label { font-size: 12.5px; margin-top: 8px; }
        .kpi-card .kpi-delta { white-space: normal; font-size: 11px; line-height: 1.35; }
        .toolbar-row { display: flex; flex-wrap: wrap; align-items: stretch; gap: 10px; }
        .ai-btn { flex: 0 0 auto; white-space: nowrap; min-height: 46px; padding: 0 16px; }
        .date-range { flex: 1 1 170px; min-width: 0; padding: 0 14px; min-height: 46px; font-size: 13px; }
        .date-range #dateRangeText { flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .date-range-menu { left: auto; right: 0; min-width: 260px; max-width: calc(100vw - 32px); }

        /* ── Achievements banner: profile on top, rank, then the two stats side by side ── */
        .status-banner { padding: 22px 16px 18px; overflow: hidden; gap: 0; }
        .banner-shape-1, .banner-shape-2, .banner-shape-3, .banner-shape-4,
        .banner-shape-5, .banner-shape-6, .banner-shape-7, .banner-shape-8 { opacity: .5; }
        .banner-mascot { display: none !important; }

        .banner-profile {
            flex-direction: column; align-items: center; text-align: center; gap: 12px;
            width: 100%; box-sizing: border-box; padding: 0 0 18px; margin: 0;
            border-right: none; border-bottom: 1px solid rgba(255,255,255,.14);
        }
        .banner-avatar-wrap { padding-top: 18px; }
        .banner-avatar { width: 84px; height: 84px; font-size: 30px; }
        .banner-crown { top: -2px; font-size: 26px; }
        .banner-name { font-size: 21px; line-height: 1.25; }
        .banner-name-doodle { margin: 0 auto !important; }
        .banner-role { margin: 6px 0 10px; font-size: 14px; justify-content: center; }
        .banner-chips { justify-content: center; gap: 8px; }
        .banner-tag { font-size: 12px; padding: 6px 12px; }

        .banner-right { flex-direction: column; align-items: stretch; width: 100%; padding: 18px 0 0; gap: 18px; }
        .banner-status { width: 100%; min-width: 0; }
        .banner-status-label { font-size: 12px; letter-spacing: 1.4px; }
        .banner-status-value { font-size: 54px; margin: 4px 0 6px; }
        .banner-status-sub { max-width: none; font-size: 12.5px; }
        .banner-move { font-size: 11.5px; }

        .banner-stats { display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; width: 100%; }
        .banner-stats .stat-box { min-width: 0; width: auto; box-sizing: border-box; padding: 14px 12px; text-align: center; }
        .banner-stats .stat-box-top { flex-direction: column; align-items: center; gap: 8px; margin-bottom: 8px; }
        .banner-stats .stat-icon { width: 38px; height: 38px; }
        .banner-stats .stat-label { font-size: 12.5px; }
        .banner-stats .stat-value { font-size: 30px; justify-content: center; }
        .banner-stats .stat-extra { font-size: 11px; line-height: 1.35; }

        /* ── Review Notes: one pane at a time (folders+list, then the note) ── */
        .workspace, .workspace.preview-open { grid-template-columns: minmax(0, 1fr) !important; gap: 14px; }
        .workspace.preview-open .folders-panel,
        .workspace.preview-open .list-panel { display: none; }
        .workspace .folders-panel, .workspace .list-panel, .workspace .preview-panel { max-height: none; grid-column: auto; }
        .list-panel .notes-scroll { max-height: none; overflow: visible; }
        .list-panel .panel-head { flex-wrap: wrap; gap: 8px; }
        .list-panel .sort-wrap { flex: 1 1 100%; min-width: 0; }
        .list-panel .sort-select { flex: 1; min-width: 0; }
        .preview-panel { position: relative; }
        .preview-panel .panel-head { flex-wrap: wrap; gap: 6px 8px; padding-top: 58px; }
        #pvCloseBtn {
            position: absolute; top: 12px; left: 14px; width: auto; height: 36px; padding: 0 14px 0 12px; gap: 8px;
            border-radius: 18px; background: #F5E8E8; color: #7B1D1D; font-size: 12.5px; font-weight: 600;
            font-family: 'Poppins', sans-serif; display: inline-flex; align-items: center;
        }
        #pvCloseBtn i { display: none; }
        #pvCloseBtn::before { content: '\f060'; font-family: 'Font Awesome 6 Free'; font-weight: 900; font-size: 12px; }
        #pvCloseBtn::after { content: 'Back to notes'; }
        .preview-panel .pv-tool { width: 38px; height: 38px; }
        .pv-body { padding: 12px 10px 28px; }
        .doc-sheet { padding: 22px 18px 34px; border-radius: 12px; }
        .pv-title { font-size: 19px; }
        .pv-meta { flex-wrap: wrap; row-gap: 4px; }
        .doc-toolbar { padding: 8px 10px; position: sticky; top: 0; z-index: 3; overflow-x: auto; flex-wrap: nowrap; }
        .doc-toolbar > * { flex: 0 0 auto; }

    }
    @media (max-width: 360px) {
        .metric-number-lg { font-size: 24px; }
        .bottom-nav .bn-tab i { font-size: 18px; }
    }
</style>

<script>
    // Review Notes: opening a note swaps to the note pane - start it at the top.
    (function () {
        const ws = document.getElementById('workspace');
        if (!ws || !window.matchMedia) return;
        const mq = window.matchMedia('(max-width: 768px)');
        new MutationObserver(() => { if (mq.matches) window.scrollTo({ top: 0 }); })
            .observe(ws, { attributes: true, attributeFilter: ['class'] });
    })();
</script>
