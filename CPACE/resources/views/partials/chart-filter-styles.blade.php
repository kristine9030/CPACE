{{--
    Filter bar + chart card styles shared by the chart pages (Program Chair
    dashboard, Class-Level Performance, Faculty dashboard). Include once
    inside <head>, after partials.chart-kit.
--}}
<style>
        /* ── Filter bar ── */
        .dash-filters {
            display:flex; align-items:center; gap:10px; flex-wrap:wrap;
            background:#fff; border-radius:14px; padding:12px 14px; margin-bottom:16px;
            box-shadow:0 1px 2px rgba(16,24,40,.04), 0 4px 14px rgba(16,24,40,.06);
        }
        .filter-label { display:flex; align-items:center; gap:8px; font-size:13px; font-weight:700; color:#1a1a1a; padding-right:12px; margin-right:2px; border-right:1px solid #eee; }
        .filter-label i { color:var(--primary); }
        .filter-field { position:relative; }
        .filter-field[hidden] { display:none !important; }
        .filter-trigger, label.filter-field {
            display:flex; align-items:center; gap:10px; min-width:190px; height:46px;
            padding:0 12px 0 8px; border:1px solid #e8e8ea; border-radius:10px; background:#fff;
            cursor:pointer; font-family:inherit; text-align:left; transition:border-color .15s, box-shadow .15s;
        }
        .filter-trigger:hover, label.filter-field:hover { border-color:#d6d6da; }
        .filter-trigger:focus-visible, label.filter-field:focus-within { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(123,29,29,.12); }
        .filter-ico { width:30px; height:30px; border-radius:8px; display:grid; place-items:center; background:#fef2f2; color:var(--primary); font-size:12.5px; flex:none; }
        .filter-text { display:flex; flex-direction:column; min-width:0; flex:1; }
        .filter-cap { font-size:9.5px; color:#999; font-weight:500; line-height:1.2; }
        .filter-val, .filter-field select { font-size:12px; font-weight:600; color:#1a1a1a; line-height:1.4; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .filter-field select { appearance:none; -webkit-appearance:none; border:none; background:none; padding:0 18px 0 0; margin:0; font-family:inherit; cursor:pointer; outline:none; width:100%; max-width:210px; }
        .filter-chev { font-size:10px; color:#999; flex:none; }
        label.filter-field .filter-chev { position:absolute; right:12px; pointer-events:none; }
        .filter-status { font-size:11px; color:#999; margin-left:auto; display:flex; align-items:center; gap:6px; }
        .filter-reset {
            display:inline-flex; align-items:center; gap:7px; height:38px; padding:0 14px;
            border:1px solid var(--primary); border-radius:9px; background:var(--primary); color:#fff;
            font-family:inherit; font-size:12px; font-weight:600; cursor:pointer; transition:background .15s, border-color .15s;
        }
        .filter-reset:hover { background:#5f1515; border-color:#5f1515; }
        .filter-reset:focus-visible { outline:2px solid var(--primary); outline-offset:2px; }

        .range-pop {
            position:absolute; top:calc(100% + 8px); left:0; z-index:50; width:300px;
            background:#fff; border-radius:12px; padding:14px; border:1px solid #eee;
            box-shadow:0 12px 32px -8px rgba(15,10,10,.28);
        }
        .range-pop[hidden] { display:none; }
        .range-presets { display:grid; grid-template-columns:minmax(0, 1fr) minmax(0, 1fr); gap:6px; margin-bottom:12px; }
        .range-presets button {
            padding:7px 8px; border:1px solid #eee; border-radius:8px; background:#fff; color:#555;
            font-family:inherit; font-size:11.5px; font-weight:500; cursor:pointer; text-align:left;
        }
        .range-presets button:hover { background:#fafafa; }
        .range-presets button.active { border-color:var(--primary); color:var(--primary); background:#fef2f2; font-weight:600; }
        .range-custom { display:grid; grid-template-columns:minmax(0, 1fr) minmax(0, 1fr); gap:8px; padding-top:12px; border-top:1px solid #f3f3f3; }
        .range-custom label { font-size:10px; color:#999; font-weight:500; display:flex; flex-direction:column; gap:4px; }
        .range-custom input { font-family:inherit; font-size:12px; padding:6px 8px; border:1px solid #e5e5e5; border-radius:7px; color:#1a1a1a; min-width:0; }
        .range-error { font-size:10.5px; color:#b91c1c; margin-top:8px; }
        .range-error:empty { display:none; }
        .range-actions { display:flex; justify-content:flex-end; gap:6px; margin-top:12px; }

        /* ── Chart cards ── */
        .chart-grid.cols-2 { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .chart-grid.kpi-grid { grid-template-columns:repeat(4,minmax(0,1fr)); }
        /* KPI cards: a large value, light supporting text, and the
           comparison line pinned to the bottom of the card. */
        .kpi-card { padding:16px 18px 14px; }
        .kpi-card .chart-card-icon { width:30px; height:30px; font-size:13px; }
        .kpi-card .chart-card-title { font-size:12.5px; font-weight:600; color:#4b5563; }
        .kpi-card .chart-card-value { font-size:34px; margin-top:10px; letter-spacing:-1px; }
        .kpi-card .chart-card-value.is-alert { color:#b91c1c; }
        .kpi-of { font-size:13px; font-weight:500; color:#9ca3af; margin-left:5px; letter-spacing:0; }
        .kpi-foot { font-size:11px; color:#8a8f98; margin-top:auto; padding-top:8px; border-top:1px solid #f1f1f3; line-height:1.45; }
        .kpi-card .chart-card-note { margin-top:4px; margin-bottom:10px; font-size:11px; color:#9ca3af; min-height:0; }
        .kpi-foot strong { color:#374151; font-weight:600; }
        .kpi-foot .up { color:#047857; font-weight:600; }
        .kpi-foot .down { color:#b91c1c; font-weight:600; }
        .chart-grid.cols-3 { grid-template-columns:repeat(3,minmax(0,1fr)); }
        .chart-card .chart-canvas-wrap.tall { height:230px; }
        .chart-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; margin-bottom:18px; transition:opacity .2s; }
        .chart-card {
            background:#fff; border-radius:14px; padding:16px 16px 12px; min-width:0; display:flex; flex-direction:column;
            box-shadow:0 1px 2px rgba(16,24,40,.04), 0 4px 14px rgba(16,24,40,.06);
        }
        .chart-card-head { display:flex; align-items:center; gap:10px; }
        .chart-card-icon { width:34px; height:34px; border-radius:10px; display:grid; place-items:center; background:#fef2f2; color:var(--primary); font-size:14px; flex:none; }
        .chart-card-title { font-size:13px; font-weight:700; color:#1a1a1a; flex:1; min-width:0; }
        .chart-card-link { width:26px; height:26px; border-radius:7px; display:grid; place-items:center; color:#999; font-size:11px; text-decoration:none; transition:background .15s, color .15s; }
        .chart-card-link:hover { background:#f5f5f5; color:var(--primary); }
        .chart-card-value { margin-top:12px; font-size:28px; line-height:1; font-weight:700; color:#1b1b1b; font-variant-numeric:tabular-nums; }
        .chart-card-note { margin-top:6px; font-size:10.5px; color:#999; min-height:15px; }
        .chart-card-note .up { color:#047857; font-weight:700; }
        .chart-card-note .down { color:#b91c1c; font-weight:700; }
        .chart-card-cap { font-size:9.5px; color:#b5b5b5; margin-top:auto; padding-top:8px; }
        .chart-card .chart-canvas-wrap { height:170px; margin-top:12px; }
        .chart-card.span-2 { grid-column:1 / -1; }
        .chart-empty { position:absolute; inset:0; background:#fff; }
        .chart-empty[hidden] { display:none; }
        @media (max-width: 1280px) { .chart-grid, .chart-grid.cols-3, .chart-grid.kpi-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (max-width: 900px) { .chart-grid.cols-2, .chart-grid.cols-3 { grid-template-columns:minmax(0, 1fr); } }
        @media (max-width: 640px) {
            .chart-grid, .chart-grid.cols-2, .chart-grid.cols-3, .chart-grid.kpi-grid { grid-template-columns:minmax(0, 1fr); }
            .filter-label { border-right:0; width:100%; }
            .filter-field, .filter-trigger, label.filter-field { width:100%; }
            .filter-field select { max-width:none; }
            .filter-status { margin-left:0; }
            .filter-reset { width:100%; justify-content:center; }
            .range-pop { width:100%; }
        }
</style>
