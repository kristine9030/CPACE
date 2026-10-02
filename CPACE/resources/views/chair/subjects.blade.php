<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject & Curriculum Management - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sf-illus { position:absolute; right:6px; top:2px; font-size:62px; opacity:.14; pointer-events:none; color:#fff; }

        /* ── Opened subject ── */
        .crumb { display:inline-flex; align-items:center; gap:8px; font-size:12.5px; color:#888; text-decoration:none; margin-bottom:14px; }
        .crumb:hover { color:var(--primary); }
        .sd-head { position:relative; display:flex; align-items:flex-start; gap:18px; padding:24px 26px; border-radius:16px; color:#fff; overflow:hidden; margin-bottom:18px; background:linear-gradient(135deg, var(--sc-base) 0%, var(--sc-dark) 100%); }
        .sd-head::before { content:''; position:absolute; inset:0; background:linear-gradient(115deg, rgba(255,255,255,.18) 0%, rgba(255,255,255,0) 55%); pointer-events:none; }
        .sd-head .sf-illus { font-size:90px; right:18px; top:6px; }
        .sd-icon { position:relative; width:56px; height:56px; border-radius:16px; flex-shrink:0; background:#fff; color:var(--sc-base); display:flex; align-items:center; justify-content:center; font-size:22px; box-shadow:0 4px 10px -2px rgba(0,0,0,.25); }
        .sd-titles { position:relative; flex:1; min-width:0; }
        .sd-code { font-size:24px; font-weight:700; text-shadow:0 1px 4px rgba(0,0,0,.25); display:flex; align-items:center; gap:10px; }
        .sd-name { font-size:13.5px; opacity:.95; margin-top:3px; }
        .sd-desc { font-size:12px; opacity:.85; margin-top:8px; line-height:1.55; max-width:720px; }
        .sd-pill { display:inline-flex; align-items:center; gap:6px; margin-top:12px; padding:5px 11px; border-radius:20px; background:rgba(255,255,255,.2); font-size:11.5px; font-weight:600; }
        .sd-badge { font-size:10px; font-weight:700; background:rgba(255,255,255,.25); border-radius:12px; padding:3px 9px; text-shadow:none; }
        .sd-actions { position:relative; display:flex; gap:8px; }
        .sd-btn { display:inline-flex; align-items:center; gap:7px; height:36px; padding:0 14px; border:1px solid rgba(255,255,255,.45); border-radius:10px; background:rgba(255,255,255,.14); color:#fff; font:600 12px 'Poppins',sans-serif; cursor:pointer; transition:background .15s; }
        .sd-btn:hover { background:rgba(255,255,255,.26); }
        .sd-grid { display:grid; grid-template-columns:minmax(0, 1fr) 320px; gap:18px; align-items:start; }
        .sd-grid .card + .card { margin-top:0; }
        .sd-grid .sc-section { padding:20px 22px; border-top:0; }
        .sd-section-title { display:inline-flex; align-items:center; gap:8px; font-size:14px; font-weight:700; color:#1a1a1a; }
        .sd-section-title i { color:var(--subject-color); }
        .sd-topics .topic-row { padding:10px 12px; }
        .sd-grid > * { min-width:0; }
        .sd-topics .section-head { flex-wrap:wrap; }
        .sd-topics .topic-name { font-size:12.5px; }
        .sd-topics .topic-meta { font-size:10.5px; }
        .sd-fac { display:flex; align-items:center; gap:11px; padding:9px 0; border-bottom:1px solid #f3f4f6; }
        .sd-fac:last-child { border-bottom:0; }
        .sd-fac .fac-av { width:32px; height:32px; border-radius:9px; font-size:10.5px; flex-shrink:0; }
        .sd-fac-name { font-size:12.5px; font-weight:600; color:#1a1a1a; }
        .sd-fac-email { font-size:11px; color:#999; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        @media (max-width:980px) { .sd-grid { grid-template-columns:minmax(0, 1fr); } .sd-head { flex-wrap:wrap; } }
        .topics-count { display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;border-radius:10px;background:#f3f4f6;color:#6b7280;font-size:10.5px;font-weight:700; }
        .sc-section { padding:13px 20px;border-top:1px solid #f3f4f6; }
        .section-head { display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:9px; }
        .section-label { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#aaa; }
        .add-mini { border:0;background:var(--primary-light);color:var(--primary);border-radius:7px;padding:5px 9px;font:600 10px 'Poppins',sans-serif;cursor:pointer; }
        .fac-chip { display:inline-flex;align-items:center;gap:7px;padding:6px 10px;border-radius:8px;font-size:11px;font-weight:500;color:#374151;background:#f9fafb;border:1px solid #e5e7eb;margin:3px 4px 3px 0; }
        .fac-av { width:19px;height:19px;border-radius:5px;display:flex;align-items:center;justify-content:center;font-size:8px;font-weight:700;color:#fff;background:var(--subject-color); }
        .topic-list { display:flex;flex-direction:column;gap:6px; }
        .topic-node + .topic-node { margin-top:6px; }
        .topic-row { display:flex;align-items:center;gap:9px;padding:8px 9px;border-radius:8px;background:#fafafa;border:1px solid #f0f0f0; }
        .topic-toggle { width:18px;height:18px;flex-shrink:0;border:0;background:none;color:#999;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:9px;padding:0; }
        .topic-toggle.open i { transform:rotate(90deg); }
        .topic-toggle i { transition:transform .15s; }
        .topic-toggle-spacer { width:18px;flex-shrink:0; }
        .topic-order { width:22px;height:22px;flex-shrink:0;border-radius:6px;background:#fff;color:#999;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700; }
        .topic-info { flex:1;min-width:0; } .topic-name { font-size:11px;font-weight:600;color:#333;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
        .topic-meta { font-size:9.5px;color:#aaa;margin-top:2px; } .topic-actions { display:flex;gap:4px; }
        .topic-actions .icon-btn { width:25px;height:25px;font-size:9px; }
        .topic-children { margin-top:6px; }
        .topic-search { position:relative; margin-bottom:9px; }
        .topic-search i { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#bbb; font-size:10px; }
        .topic-search input[type=text] { width:100%; padding:8px 10px 8px 32px; border:1.5px solid #e2e2e6; border-radius:8px; font:11px 'Poppins',sans-serif; }
        .topic-search input[type=text]:focus { outline:none; border-color:var(--primary); }
        .empty-msg { font-size:11px;color:#bbb;display:flex;align-items:center;gap:7px;padding:4px 0; }
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:2000;align-items:center;justify-content:center;padding:20px; }
        .modal-overlay.open { display:flex; } .modal { background:#fff;border-radius:16px;width:100%;max-width:560px;padding:24px;max-height:90vh;overflow-y:auto; }
        .modal h3 { font-size:16px;color:#1a1a1a;margin-bottom:4px; } .modal-sub { font-size:11px;color:#999;margin-bottom:18px; }
        .modal-grid { display:grid;grid-template-columns:minmax(0, 1fr) minmax(0, 1fr);gap:14px; } .full { grid-column:1/-1; }
        textarea { width:100%;padding:10px 12px;border:1.5px solid #e2e2e6;border-radius:8px;font:13px 'Poppins',sans-serif;resize:vertical;min-height:78px; }
        input[type=number], input[type=color], select { width:100%;padding:10px 12px;border:1.5px solid #e2e2e6;border-radius:8px;font:13px 'Poppins',sans-serif;background:#fff; }
        input[type=color] { height:42px;padding:4px;cursor:pointer; } textarea:focus,input:focus { outline:none;border-color:var(--primary); }
        .toggle-row { display:flex;align-items:center;gap:9px;padding:10px 12px;background:#f8f8fa;border-radius:8px; }
        .toggle-row input { width:17px;height:17px;accent-color:var(--primary); } .toggle-row label { margin:0;font-size:11px; }
        .modal-actions { display:flex;justify-content:flex-end;gap:9px;margin-top:20px; }
        .btn-danger { border:none;background:#b91c1c;color:#fff;padding:9px 16px;border-radius:8px;font:600 12px Poppins,sans-serif;cursor:pointer;display:inline-flex;align-items:center;gap:6px; }
        .btn-danger:hover { background:#991b1b; }
        .btn-warning { border:none;background:#d97706;color:#fff;padding:9px 16px;border-radius:8px;font:600 12px Poppins,sans-serif;cursor:pointer;display:inline-flex;align-items:center;gap:6px; }
        .btn-warning:hover { background:#b45309; }
        .btn-success { border:none;background:#059669;color:#fff;padding:9px 16px;border-radius:8px;font:600 12px Poppins,sans-serif;cursor:pointer;display:inline-flex;align-items:center;gap:6px; }
        .btn-success:hover { background:#047857; }
        .icon-btn { width:30px;height:30px;border:0;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;font-size:11px;text-decoration:none; }
        .ib-edit { background:#dbeafe;color:#2563eb; } .ib-delete { background:#fde8e8;color:#b91c1c; }
        .inactive-pill { display:inline-flex;padding:3px 8px;border-radius:12px;background:#f3f4f6;color:#6b7280;font-size:9px;font-weight:700;margin-left:5px; }
        .ib-warning { background:#fef3c7;color:#d97706; } .ib-success { background:#d1fae5;color:#059669; } .ib-muted { background:#f3f4f6;color:#9ca3af; }
        .del-warn { margin:14px 0;padding:12px;background:#fef3c7;border-radius:8px;font-size:11.5px;color:#92400e;display:flex;align-items:flex-start;gap:8px; }
        .del-warn i { margin-top:1px;flex-shrink:0; }
        .info-note { margin:14px 0;padding:12px;background:#eff6ff;border-radius:8px;font-size:11.5px;color:#1e40af;display:flex;align-items:flex-start;gap:8px; }
        .info-note i { margin-top:1px;flex-shrink:0; }
        @media(max-width:620px) { .modal-grid { grid-template-columns:minmax(0, 1fr); }.full { grid-column:auto; } }
        /* ── Page-level: soft card shadow, plain white action buttons ── */
        :root { --card-shadow: 0 1px 2px rgba(16,24,40,.04), 0 4px 14px rgba(16,24,40,.06); }
        .btn-plain { background:#fff; color:#374151; border:1px solid #e5e7eb; }
        .btn-plain:hover { background:#f9fafb; border-color:#d1d5db; }
        .btn-plain.accent { color:var(--primary); border-color:#ecc7cd; }

        /* ── Curriculum card: which one, where its topics came from, what's next ── */
        .cur-card { background:#fff; border:1px solid #eceef1; border-radius:16px; padding:18px 20px; margin-bottom:20px; box-shadow:var(--card-shadow); }
        .cur-card.is-draft { border-color:#fbe3a7; background:#fffdf6; }
        .cur-card.is-archived { background:#f9fafb; }
        .cur-row { display:grid; grid-template-columns:46px minmax(0, 1fr); gap:14px; align-items:center; }
        .cur-ic { width:46px; height:46px; border-radius:13px; display:flex; align-items:center; justify-content:center; background:var(--primary-light); color:var(--primary); font-size:18px; flex-shrink:0; }
        .cur-info { min-width:0; }
        .cur-eyebrow { font-size:10.5px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.5px; }
        .cur-name { font-size:14.5px; line-height:1.35; font-weight:700; color:#1a1a1a; margin-top:1px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
        .cur-sub { font-size:12px; color:#777; margin-top:3px; }
        .curr-pill { display:inline-flex; align-items:center; padding:3px 10px; border-radius:12px; font-size:10.5px; font-weight:700; }
        .curr-pill.active { background:#d1fae5; color:#047857; }
        .curr-pill.draft { background:#fef3c7; color:#b45309; }
        .curr-pill.archived { background:#e5e7eb; color:#4b5563; }
        .cur-actions { grid-column:1 / -1; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
        .cur-actions .btn { flex:1; justify-content:center; }
        .curr-select { width:100%; flex-basis:100%; max-width:none; height:38px; padding:0 34px 0 12px; border:1px solid #e5e7eb; border-radius:10px; font-size:12px; background-color:#fff; cursor:pointer; appearance:none; -webkit-appearance:none;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat:no-repeat; background-position:right 12px center; }
        .cur-menu { position:relative; }
        .cur-dots { width:38px; height:38px; border:1px solid #e5e7eb; border-radius:10px; background:#fff; color:#4b5563; cursor:pointer; }
        .cur-dots:hover, .cur-menu.open .cur-dots { background:#f3f4f6; color:#111827; }
        .cur-dropdown { display:none; position:absolute; right:0; top:44px; z-index:60; min-width:230px; background:#fff; border-radius:12px; padding:6px; box-shadow:0 12px 32px rgba(16,24,40,.16); border:1px solid #f0f0f0; }
        .cur-menu.open .cur-dropdown { display:block; }
        .cur-dropdown button { display:flex; align-items:center; gap:10px; width:100%; padding:9px 12px; border:0; background:none; border-radius:8px; font-family:'Poppins',sans-serif; font-size:12.5px; color:#333; cursor:pointer; text-align:left; }
        .cur-dropdown button i { width:14px; color:#999; }
        .cur-dropdown button:hover { background:#f6f6f7; }
        .cur-dropdown .danger, .cur-dropdown .danger i { color:#b91c1c; }
        .cur-layout.has-aside { display:grid; grid-template-columns:minmax(0, 1fr) 320px; gap:20px; align-items:start; }
        .cur-layout.has-aside .cur-aside { grid-column:2; grid-row:1; position:sticky; top:16px; }
        .cur-layout.has-aside .cur-content { grid-column:1; grid-row:1; min-width:0; }
        @media (max-width:1180px) { .cur-layout.has-aside { display:block; } .cur-layout.has-aside .cur-aside { position:static; margin-bottom:18px; } }
        .cur-help { margin-top:14px; padding-top:14px; border-top:1px solid #f0f0f2; display:flex; flex-direction:column; gap:12px; }
        .cur-help:empty { display:none; }
        .cur-text { font-size:12.5px; color:#555; line-height:1.6; margin:0; }
        .cur-text i { color:#059669; margin-right:6px; }
        .cur-card.is-archived .cur-text i { color:#9ca3af; }
        .cur-source { font-size:12px; color:#777; margin:0; }
        .cur-source i { color:#c0392b; margin-right:6px; }
        .cur-source strong { color:#333; font-weight:600; }
        .cur-steps { display:grid; grid-template-columns:minmax(0, 1fr); gap:8px; }
        .cur-step { display:flex; align-items:center; gap:11px; padding:10px 12px; border-radius:12px; background:#f6f6f8; color:#9ca3af; }
        .cur-step .n { width:26px; height:26px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:#e5e7eb; color:#6b7280; font-size:12px; font-weight:700; }
        .cur-step strong { display:block; font-size:12.5px; color:#555; }
        .cur-step small { display:block; font-size:11px; color:#9ca3af; margin-top:1px; }
        .cur-step.now { background:#fff3d6; }
        .cur-step.now .n { background:#d97706; color:#fff; }
        .cur-step.now strong { color:#92400e; }
        .cur-step.now small { color:#b45309; }
        .cur-step.done { background:#e9f8f1; }
        .cur-step.done .n { background:#059669; color:#fff; }
        .cur-step.done strong { color:#047857; }
        .cur-step.done small { color:#5b9c86; }
        .cur-alert { display:flex; align-items:center; gap:12px; flex-wrap:wrap; padding:11px 14px; border-radius:12px; background:#eff6ff; border:1px solid #cfe0fb; color:#1e40af; font-size:12.5px; line-height:1.5; }
        .cur-alert > span { flex:1; min-width:200px; }
        .cur-alert-btn { padding:7px 14px; border-radius:9px; background:#2563eb; color:#fff; font-size:12px; font-weight:600; text-decoration:none; white-space:nowrap; }
        .cur-alert-btn:hover { background:#1d4ed8; }
        .audit-list { list-style:none; margin:0; padding:0; font-size:11.5px; color:#555; }
        .audit-list li { padding:7px 0; border-bottom:1px dashed #eee; display:flex; gap:8px; }
        .audit-modal { max-height:60vh; overflow-y:auto; }
        .audit-list li:last-child { border-bottom:0; }
        .audit-when { color:#aaa; white-space:nowrap; min-width:92px; }

        /* ── Summary cards ── */
        .kpi-row { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:16px; margin-bottom:26px; }
        .kpi { display:flex; flex-direction:column; background:#fff; border-radius:16px; padding:18px 18px 14px; box-shadow:var(--card-shadow); }
        .kpi-top { display:flex; align-items:center; gap:14px; flex:1; padding-bottom:14px; }
        .kpi-icon { width:48px; height:48px; border-radius:13px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:19px; }
        .t-rose { background:#fde8ea; color:#c0392b; }
        .t-violet { background:#ede9fe; color:#6d28d9; }
        .t-green { background:#dcf5ec; color:#059669; }
        .t-amber { background:#fdf0d5; color:#d97706; }
        .kpi-lbl { font-size:12.5px; font-weight:600; color:#444; }
        .kpi-num { font-size:24px; font-weight:700; color:#1a1a1a; line-height:1.15; margin-top:2px; }
        .kpi-foot { padding-top:11px; border-top:1px solid #f0f0f0; font-size:11px; color:#888; line-height:1.45; }
        .kpi-foot strong { font-weight:700; }

        /* ── Subjects header + toolbar ── */
        .sj-header { display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; margin-bottom:16px; }
        .sj-h-title { font-size:22px; font-weight:700; color:#1a1a1a; }
        .sj-h-sub { font-size:12px; color:#888; margin-top:2px; }
        .sj-toolbar { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
        .sj-search { position:relative; }
        .sj-search i { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#a3a8b0; font-size:12px; pointer-events:none; }
        .sj-toolbar .sj-search input, .sj-toolbar .sj-select { height:36px; border:1px solid #e5e7eb; border-radius:10px; background-color:#fff; font-family:'Poppins',sans-serif; font-size:12.5px; color:#1a1a1a; }
        .sj-toolbar .sj-search input { width:220px; padding:0 12px 0 34px; }
        .sj-toolbar .sj-select { width:auto; min-width:160px; padding:0 34px 0 12px; cursor:pointer; appearance:none; -webkit-appearance:none;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat:no-repeat; background-position:right 12px center; }
        .sj-toolbar .sj-search input:focus, .sj-toolbar .sj-select:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(123,29,29,.1); }
        .sj-toolbar .sj-select.is-set { background-color:var(--primary-light); border-color:#ecc7cd; color:var(--primary); font-weight:600; }

        /* ── Subject rows: one compact line per subject ── */
        .sj-grid { display:flex; flex-direction:column; gap:11px; }
        .sj-card { position:relative; display:grid; grid-template-columns:minmax(0, 1fr) 122px 182px 82px 36px; gap:15px; align-items:center; padding:13px 16px 13px 14px; background:#fff; border-radius:13px; border-left:4px solid var(--sc-base); box-shadow:var(--card-shadow); transition:transform .15s, box-shadow .15s; }
        .sj-card:hover { transform:translateY(-1px); box-shadow:0 2px 4px rgba(16,24,40,.05), 0 8px 20px rgba(16,24,40,.09); }
        .sj-card.is-inactive { opacity:.7; }
        .sj-title { min-width:0; }
        .sj-name { display:block; color:#1a1a1a; text-decoration:none; font-size:13.5px; font-weight:600; line-height:1.3; }
        .sj-name:hover { color:var(--primary); text-decoration:underline; }
        .sj-meta { font-size:11.5px; color:#999; margin-top:3px; }
        .sj-lbl { font-size:11px; color:#9ca3af; margin-bottom:4px; }
        .sj-val { font-size:12.5px; color:#333; font-weight:500; display:flex; align-items:center; gap:6px; white-space:nowrap; }
        .sj-val i { color:var(--sc-base); font-size:11.5px; }
        .sj-val.warn, .sj-val.warn i { color:#b45309; }
        .sj-cov { display:flex; align-items:center; gap:8px; }
        .sj-cov strong { font-size:12.5px; color:#1a1a1a; min-width:34px; }
        .sj-ring { width:22px; height:22px; transform:rotate(-90deg); flex-shrink:0; }
        .sj-ring circle { fill:none; stroke-width:3.4; }
        .sj-ring .bg { stroke:#eceef1; }
        .sj-ring .fg { stroke:var(--sc-base); stroke-linecap:round; }
        .sj-bar { flex:1; height:6px; border-radius:6px; background:#eceef1; overflow:hidden; }
        .sj-bar span { display:block; height:100%; border-radius:6px; background:var(--sc-base); }
        .sj-status { display:inline-flex; align-items:center; justify-content:center; gap:5px; padding:4px 10px; border-radius:20px; background:#e9f8f1; color:#047857; font-size:11px; font-weight:700; }
        .sj-status i { font-size:6px; }
        .sj-status.off { background:#f1f2f4; color:#6b7280; }
        .sj-menu { position:relative; }
        .sj-dots { width:34px; height:34px; border:1px solid #e5e7eb; border-radius:10px; background:#fff; color:#4b5563; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; text-decoration:none; font-size:13px; }
        .sj-dots:hover, .sj-menu.open .sj-dots { background:#f3f4f6; color:#111827; }
        .sj-dropdown { display:none; position:fixed; z-index:1500; min-width:180px; background:#fff; border-radius:12px; padding:6px; box-shadow:0 12px 32px rgba(16,24,40,.16); border:1px solid #f0f0f0; }
        .sj-dropdown.is-open { display:block; }
        .sj-dropdown a, .sj-dropdown button { display:flex; align-items:center; gap:10px; width:100%; padding:9px 12px; border:0; background:none; border-radius:8px; font-family:'Poppins',sans-serif; font-size:12.5px; color:#333; cursor:pointer; text-align:left; text-decoration:none; }
        .sj-dropdown a i, .sj-dropdown button i { width:14px; color:#999; }
        .sj-dropdown a:hover, .sj-dropdown button:hover { background:#f6f6f7; }
        .sj-dropdown .danger, .sj-dropdown .danger i { color:#b91c1c; }
        .sj-empty { text-align:center; padding:28px; color:#999; font-size:12.5px; background:#fff; border-radius:12px; box-shadow:var(--card-shadow); }
        .sj-empty a { color:var(--primary); font-weight:600; }
        @media (max-width:1100px) { .kpi-row { grid-template-columns:repeat(2, minmax(0, 1fr)); } }
        @media (max-width:760px) {
            .kpi-row { grid-template-columns:minmax(0, 1fr); }
            .sj-card { grid-template-columns:minmax(0, 1fr) 34px; row-gap:10px; }
            .sj-fac, .sj-covcol { grid-column:1 / -1; }
            .sj-status { display:none; }
            .sj-toolbar .sj-search input { width:100%; }
        }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'subjects'])

@php
    $subjectsColl = collect($subjects);
    $totalSubjects = $subjectsColl->count();
    $assigned = $subjectsColl->filter(fn($subject) => $subject->faculty->isNotEmpty())->count();
    $topicCount = $subjectsColl->sum(fn($subject) => $subject->topics->count());
    $inactiveCount = $subjectsColl->where('is_active', false)->count();

    // Context figures for the KPI cards — each number gets a line underneath
    // saying what it means, so a bare count is never left to interpretation.
    $activeCount = $totalSubjects - $inactiveCount;
    $unassigned = $totalSubjects - $assigned;
    $emptySubjects = $subjectsColl->filter(fn($subject) => $subject->topics->isEmpty())->count();
    $avgTopics = $totalSubjects > 0 ? round($topicCount / $totalSubjects, 1) : 0;
@endphp

<main class="main">
    @php
        $hasDraft = $versions->contains('status', \App\Models\CurriculumVersion::STATUS_DRAFT);
    @endphp
    <div class="topbar">
        <div class="topbar-left"><div><div class="page-title">Subject &amp; Curriculum</div><div class="page-sub">The subjects and topics students study, and the TOS they come from.</div></div></div>
        <div class="topbar-right">@include('partials.topbar-actions')</div>
    </div>

    {{-- Status and validation messages surface as SweetAlert popups via partials.alerts --}}

    @php
        // Per subject: questions written under this curriculum's topics, and
        // "coverage" = share of its topics that have at least one question.
        $subjectFigures = $subjectsColl->mapWithKeys(function ($subject) {
            $topics = $subject->topics->count();
            $withQuestions = $subject->topics->where('questions_count', '>', 0)->count();

            return [$subject->id => [
                'topics' => $topics,
                'questions' => (int) $subject->topics->sum('questions_count'),
                'covered' => $withQuestions,
                'coverage' => $topics > 0 ? (int) round($withQuestions / $topics * 100) : 0,
            ]];
        });
        $totalQuestions = $subjectFigures->sum('questions');

        $summaryCards = [
            [
                'value' => $totalSubjects, 'label' => 'Total Subjects', 'tone' => 't-rose', 'icon' => 'fa-book',
                'context' => $totalSubjects > 0
                    ? '<strong>' . $activeCount . ' active</strong> in the curriculum right now.'
                    : 'No subjects yet — add one to begin.',
            ],
            [
                'value' => $assigned . ' / ' . $totalSubjects, 'label' => 'Faculty Coverage', 'tone' => 't-violet', 'icon' => 'fa-users',
                'context' => $unassigned > 0
                    ? '<strong style="color:var(--accent);">' . $unassigned . ' ' . ($unassigned === 1 ? 'subject needs' : 'subjects need') . '</strong> a faculty assignment.'
                    : '<strong style="color:#059669;">Every subject</strong> has an assigned faculty.',
            ],
            [
                'value' => number_format($topicCount), 'label' => 'Curriculum Topics', 'tone' => 't-green', 'icon' => 'fa-bullseye',
                'context' => $emptySubjects > 0
                    ? '<strong style="color:var(--accent);">' . $emptySubjects . ' ' . ($emptySubjects === 1 ? 'subject has' : 'subjects have') . '</strong> no topics yet.'
                    : '<strong>' . number_format($totalQuestions) . ' questions</strong> written across them.',
            ],
            [
                'value' => $inactiveCount, 'label' => 'Inactive Subjects', 'tone' => 't-amber', 'icon' => 'fa-triangle-exclamation',
                'context' => $inactiveCount > 0
                    ? '<strong style="color:var(--accent);">' . $inactiveCount . ' ' . ($inactiveCount === 1 ? 'subject is' : 'subjects are') . '</strong> hidden from students.'
                    : 'All subjects are available to students.',
            ],
        ];
    @endphp
    @if($openSubject)
        {{-- ── One subject opened: its details, faculty, and topics ── --}}
        @php
            $subject = $openSubject;
            $look = $looks[$subject->id];
            $color = $subject->color ?: '#7B1D1D';
        @endphp
        <a class="crumb" href="{{ route('chair.subjects', array_filter(['version' => request('version')])) }}"><i class="fas fa-arrow-left"></i> All subjects</a>

        <div class="sd-head" style="--sc-base:{{ $look['base'] }}; --sc-dark:{{ $look['dark'] }};">
            <i class="fas fa-folder-open sf-illus"></i>
            <div class="sd-icon"><i class="fas {{ $look['icon'] }}"></i></div>
            <div class="sd-titles">
                <div class="sd-code">{{ $subject->code }} @unless($subject->is_active)<span class="sd-badge">Inactive</span>@endunless</div>
                <div class="sd-name">{{ $subject->name }}</div>
                @if($subject->description)<div class="sd-desc">{{ $subject->description }}</div>@endif
                <span class="sd-pill"><i class="fas fa-bullseye"></i> Passing threshold: {{ $subject->passing_threshold }}%</span>
            </div>
            @unless($readOnly)
                <div class="sd-actions">
                    <button type="button" class="sd-btn" title="Edit subject" onclick="openSubject({{ Illuminate\Support\Js::from(['id'=>$subject->id,'code'=>$subject->code,'name'=>$subject->name,'description'=>$subject->description,'passing_threshold'=>$subject->passing_threshold,'color'=>$color,'is_active'=>$subject->is_active]) }})"><i class="fas fa-pen"></i> Edit</button>
                    <button type="button" class="sd-btn" title="Remove subject" onclick="openSubjectDelete({{ $subject->id }}, '{{ addslashes($subject->code) }}')"><i class="fas fa-trash"></i></button>
                </div>
            @endunless
        </div>

        <div class="sd-grid" style="--subject-color:{{ $look['base'] }};">
            {{-- Topics: the main thing a chair works on here, always open --}}
            <div class="card sc-section sd-topics">
                <div class="section-head">
                    <span class="sd-section-title"><i class="fas fa-list-check"></i> Topics <span class="topics-count">{{ $subject->topics->count() }}</span></span>
                    @unless($readOnly)
                        <span style="display:inline-flex;gap:6px;">
                            @if($version?->isDraft())
                                <button type="button" class="add-mini" title="Copy this subject's Test Bank questions from the current curriculum into matching topics of this draft" onclick="copyQuestions({{ $subject->id }}, '{{ addslashes($subject->code) }}')"><i class="fas fa-copy"></i> Copy questions</button>
                            @endif
                            <button type="button" class="add-mini" onclick="openTopic({{ $subject->id }}, '{{ addslashes($subject->code) }}')"><i class="fas fa-plus"></i> Add Topic</button>
                        </span>
                    @endunless
                </div>
                @if($subject->topicTree->isNotEmpty())
                    <div class="topic-search">
                        <i class="fas fa-magnifying-glass"></i>
                        <input type="text" placeholder="Search topics..." oninput="searchChairTopics(this, this.value)">
                    </div>
                @endif
                <div class="topic-list" id="topics-data-{{ $subject->id }}" data-topics="{{ json_encode($subject->topics->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'parent_id' => $t->parent_id])) }}">
                    @if($subject->topicTree->isNotEmpty())
                        @include('chair.partials.topic-node', ['subject' => $subject, 'topics' => $subject->topicTree, 'depth' => 0, 'readOnly' => $readOnly])
                    @else
                        <div class="empty-msg"><i class="fas fa-list"></i>{{ $version?->isDraft() ? 'No topics in this draft yet — add them or import the subject\'s TOS.' : 'No topics added yet.' }}</div>
                    @endif
                </div>
            </div>

            {{-- Who handles this subject --}}
            <div class="card sc-section sd-faculty">
                <div class="section-head">
                    <span class="sd-section-title"><i class="fas fa-chalkboard-user"></i> Assigned Faculty <span class="topics-count">{{ $subject->faculty->count() }}</span></span>
                    <a href="{{ route('chair.faculty') }}" class="add-mini" style="text-decoration:none;"><i class="fas fa-layer-group"></i> Assign</a>
                </div>
                @forelse($subject->faculty as $member)
                    <div class="sd-fac">
                        <span class="fac-av">{{ strtoupper(substr($member->first_name,0,1).substr($member->last_name,0,1)) }}</span>
                        <div style="min-width:0;">
                            <div class="sd-fac-name">{{ $member->name }}</div>
                            <div class="sd-fac-email">{{ $member->email }}</div>
                        </div>
                    </div>
                @empty
                    <div class="empty-msg"><i class="fas fa-user-slash"></i>No faculty assigned yet.</div>
                @endforelse
            </div>
        </div>
    @else
        {{-- ── All subjects ── --}}
        <div class="kpi-row">
            @foreach ($summaryCards as $card)
                <div class="kpi">
                    <div class="kpi-top">
                        <div class="kpi-icon {{ $card['tone'] }}"><i class="fas {{ $card['icon'] }}"></i></div>
                        <div>
                            <div class="kpi-lbl">{{ $card['label'] }}</div>
                            <div class="kpi-num">{{ $card['value'] }}</div>
                        </div>
                    </div>
                    <div class="kpi-foot">{!! $card['context'] !!}</div>
                </div>
            @endforeach
        </div>

        <div class="cur-layout {{ $version ? 'has-aside' : '' }}">
        @include('chair.partials.curriculum-card')
        <div class="cur-content">
        <div class="sj-header">
            <div>
                <div class="sj-h-title">Subjects</div>
                <div class="sj-h-sub">{{ $totalSubjects }} subject{{ $totalSubjects === 1 ? '' : 's' }} &bull; View details, coverage, and faculty assignments.</div>
            </div>
            <div class="sj-toolbar">
                <div class="sj-search">
                    <i class="fas fa-search"></i>
                    <input type="search" id="sjSearch" placeholder="Search subjects..." aria-label="Search subjects" oninput="filterSubjectCards()">
                </div>
                <select id="sjFilter" class="sj-select" aria-label="Filter subjects" onchange="filterSubjectCards()">
                    <option value="">All Subjects</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="no_faculty">Needs faculty</option>
                    <option value="no_topics">No topics yet</option>
                    <option value="low_coverage">Coverage below 50%</option>
                </select>
                @unless($readOnly)
                    <button type="button" class="btn btn-primary btn-sm sj-add" onclick="openSubject()"><i class="fas fa-plus"></i> Add Subject</button>
                @endunless
            </div>
        </div>

        <div class="sj-grid" id="sjGrid">
            @forelse($subjects as $subject)
                @php
                    $look = $looks[$subject->id];
                    $fig = $subjectFigures[$subject->id];
                    $color = $subject->color ?: '#7B1D1D';
                    $openUrl = route('chair.subjects', array_filter(['subject' => $subject->id, 'version' => request('version')]));
                    // Ring: circumference of r=9 is ~56.55.
                    $ringDash = round(56.55 * $fig['coverage'] / 100, 2);
                @endphp
                <div class="sj-card {{ $subject->is_active ? '' : 'is-inactive' }}"
                     data-search="{{ strtolower($subject->code . ' ' . $subject->name) }}"
                     data-state="{{ $subject->is_active ? 'active' : 'inactive' }}"
                     data-faculty="{{ $subject->faculty->count() }}" data-topics="{{ $fig['topics'] }}" data-coverage="{{ $fig['coverage'] }}"
                     style="--sc-base:{{ $look['base'] }};">
                    <div class="sj-title">
                        <a class="sj-name" href="{{ $openUrl }}">{{ $subject->name }}</a>
                        <div class="sj-meta">{{ $subject->code }} &bull; {{ $fig['topics'] }} topic{{ $fig['topics'] === 1 ? '' : 's' }} &bull; {{ number_format($fig['questions']) }} question{{ $fig['questions'] === 1 ? '' : 's' }}</div>
                    </div>
                    <div class="sj-col sj-fac">
                        <div class="sj-lbl">Faculty</div>
                        <div class="sj-val {{ $subject->faculty->isEmpty() ? 'warn' : '' }}"><i class="fas fa-user-group"></i> {{ $subject->faculty->isEmpty() ? 'None yet' : $subject->faculty->count() . ' assigned' }}</div>
                    </div>
                    <div class="sj-col sj-covcol" title="{{ $fig['topics'] > 0 ? $fig['covered'] . ' of ' . $fig['topics'] . ' topics have questions' : 'No topics yet' }}">
                        <div class="sj-lbl">Coverage</div>
                        <div class="sj-cov">
                            <svg class="sj-ring" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" class="bg"></circle>
                                <circle cx="12" cy="12" r="9" class="fg" stroke-dasharray="{{ $ringDash }} 56.55"></circle>
                            </svg>
                            <strong>{{ $fig['coverage'] }}%</strong>
                            <div class="sj-bar"><span style="width:{{ $fig['coverage'] }}%;"></span></div>
                        </div>
                    </div>
                    <span class="sj-status {{ $subject->is_active ? '' : 'off' }}"><i class="fas fa-circle"></i> {{ $subject->is_active ? 'Active' : 'Inactive' }}</span>
                    @unless($readOnly)
                        <div class="sj-menu">
                            <button type="button" class="sj-dots" onclick="toggleSubjectMenu(event, this)" aria-label="Options for {{ $subject->code }}"><i class="fas fa-ellipsis"></i></button>
                            <div class="sj-dropdown">
                                <a href="{{ $openUrl }}"><i class="fas fa-folder-open"></i> Open details</a>
                                <button type="button" onclick="openSubject({{ Illuminate\Support\Js::from(['id'=>$subject->id,'code'=>$subject->code,'name'=>$subject->name,'description'=>$subject->description,'passing_threshold'=>$subject->passing_threshold,'color'=>$color,'is_active'=>$subject->is_active]) }})"><i class="fas fa-pen"></i> Edit subject</button>
                                <button type="button" class="danger" onclick="openSubjectDelete({{ $subject->id }}, '{{ addslashes($subject->code) }}')"><i class="fas fa-trash"></i> Delete subject</button>
                            </div>
                        </div>
                    @else
                        <a class="sj-dots sj-go" href="{{ $openUrl }}" aria-label="Open {{ $subject->code }}"><i class="fas fa-chevron-right"></i></a>
                    @endunless
                </div>
            @empty
                <div class="card" style="grid-column:1/-1;"><div class="empty"><i class="fas fa-layer-group"></i><div>No subjects yet. Add the first subject to begin.</div></div></div>
            @endforelse
            <div class="sj-empty" id="sjNoMatch" hidden>No subjects match. <a href="#" onclick="clearSubjectFilters(); return false;">Clear filters</a></div>
        </div>

        </div>{{-- /.cur-content --}}
        </div>{{-- /.cur-layout --}}
    @endif
</main>

{{-- Import the PRC Table of Specifications PDF into the curriculum being viewed. --}}
@if($version && ! $readOnly)
<div class="modal-overlay" id="tosModal">
    <div class="modal">
        <h3>Import Table of Specifications</h3>
        <div class="modal-sub">Upload the official PRC TOS (PDF). You'll review everything before it's saved.</div>
        <form method="POST" action="{{ route('chair.curriculum.import.store') }}" enctype="multipart/form-data" data-loading="Reading the Table of Specifications...">
            @csrf
            <input type="hidden" name="curriculum_version_id" value="{{ $version->id }}">
            <div class="form-group"><label>TOS PDF</label><input type="file" name="file" accept="application/pdf,.pdf" required></div>
            <div class="info-note"><i class="fas fa-circle-info"></i><span>Importing into <strong>{{ $version->label }}</strong>{{ $version->isDraft() ? ' (draft)' : ' — live, students see new topics right away' }}.</span></div>
            <div class="modal-actions"><button type="button" class="btn btn-ghost" onclick="closeModal('tosModal')">Cancel</button><button class="btn btn-primary"><i class="fas fa-magnifying-glass"></i> Read PDF</button></div>
        </form>
    </div>
</div>
@endif

{{-- Start a new curriculum: creates an empty DRAFT the chair builds before publishing. --}}
<div class="modal-overlay" id="curriculumModal">
    <div class="modal">
        <h3>Start a New Curriculum</h3>
        <div class="modal-sub">Creates a blank draft. Students won't see it until you publish.</div>
        <form method="POST" action="{{ route('chair.curriculum.store') }}"
              data-confirm="A blank draft curriculum will be created. Nothing students see changes until you publish it."
              data-confirm-title="Start a new curriculum?"
              data-confirm-ok="Yes, start draft"
              data-confirm-icon="question">@csrf
            <div class="modal-grid">
                <div class="form-group full"><label>Curriculum Name</label><input type="text" name="label" maxlength="80" required placeholder="e.g. CPALE TOS — Effective Oct 2027"></div>
                <div class="form-group full"><label>First Batch Covered</label><input type="text" name="effective_from_batch" required pattern="\d{4}-\d{4}" value="{{ old('effective_from_batch', $suggestedBatch) }}" placeholder="e.g. {{ $suggestedBatch }}"><div class="hint">The first school-year batch that uses this curriculum.<x-tip>Must come after the current curriculum's first batch. The current curriculum will then cover batches up to the one before.</x-tip></div></div>
            </div>
            <div class="modal-actions"><button type="button" class="btn btn-ghost" onclick="closeModal('curriculumModal')">Cancel</button><button class="btn btn-primary"><i class="fas fa-plus"></i> Start Draft</button></div>
        </form>
    </div>
</div>

{{-- Edit a draft's name and first batch. Locked once published, because
     publishing closes the previous curriculum's range at this batch. --}}
@if($version?->isDraft())
@php
    $activeVersion = $versions->firstWhere('status', \App\Models\CurriculumVersion::STATUS_ACTIVE);
@endphp
<div class="modal-overlay" id="curriculumEditModal">
    <div class="modal">
        <h3>Edit Draft Curriculum</h3>
        <div class="modal-sub">Editable until you publish.</div>
        <form method="POST" action="{{ route('chair.curriculum.update', $version) }}"
              data-confirm="The draft's name and first batch will be updated."
              data-confirm-title="Save these details?"
              data-confirm-ok="Yes, save"
              data-confirm-icon="question">@csrf @method('PUT')
            <div class="modal-grid">
                <div class="form-group full"><label>Curriculum Name</label><input type="text" name="label" maxlength="80" required value="{{ old('label', $version->label) }}"></div>
                <div class="form-group full">
                    <label>First Batch Covered</label>
                    <input type="text" name="effective_from_batch" required pattern="\d{4}-\d{4}" value="{{ old('effective_from_batch', $version->effective_from_batch) }}" placeholder="e.g. {{ $suggestedBatch }}">
                    <div class="hint">
                        @if($activeVersion?->effective_from_batch)
                            Must be after <strong>{{ $activeVersion->effective_from_batch }}</strong>.<x-tip>That's the first batch of the current curriculum ("{{ $activeVersion->label }}"). Batches before this one keep the current curriculum.</x-tip>
                        @else
                            Batches before this one keep the current curriculum.
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-actions"><button type="button" class="btn btn-ghost" onclick="closeModal('curriculumEditModal')">Cancel</button><button class="btn btn-primary"><i class="fas fa-save"></i> Save Details</button></div>
        </form>
    </div>
</div>
@endif

<div class="modal-overlay" id="subjectModal">
    <div class="modal">
        <h3 id="subjectModalTitle">Add Subject</h3><div class="modal-sub">Configure the subject and the score students need for readiness.</div>
        <form method="POST" id="subjectForm" action="{{ route('chair.subjects.store') }}"
              data-confirm="This subject and its passing threshold will be saved and applied across quizzes and readiness reports."
              data-confirm-title="Save this subject?"
              data-confirm-ok="Yes, save subject"
              data-confirm-icon="question">@csrf <input type="hidden" name="_method" id="subjectMethod" value="POST">
            <div class="modal-grid">
                <div class="form-group"><label>Subject Code</label><input type="text" name="code" maxlength="20" required placeholder="e.g. FAR"></div>
                <div class="form-group"><label>Subject Name</label><input type="text" name="name" required placeholder="Full subject name"></div>
                <div class="form-group"><label>Passing Threshold (%)</label><input type="number" name="passing_threshold" min="1" max="100" value="75" required><div class="hint">Used for quiz results and readiness status.</div></div>
                <div class="form-group"><label>Display Color</label><input type="color" name="color" value="#7B1D1D"></div>
                <div class="form-group full"><label>Description</label><textarea name="description" placeholder="Optional subject description"></textarea></div>
                <div class="toggle-row full"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" id="subjectActive" value="1" checked><label for="subjectActive">Active and available throughout the system</label></div>
            </div>
            <div class="modal-actions"><button type="button" class="btn btn-ghost" onclick="closeModal('subjectModal')">Cancel</button><button class="btn btn-primary"><i class="fas fa-save"></i> Save Subject</button></div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="topicModal">
    <div class="modal">
        <h3 id="topicModalTitle">Add Topic</h3><div class="modal-sub" id="topicModalSub">Add a curriculum topic to this subject.</div>
        <form method="POST" id="topicForm"
              data-confirm="This topic will be saved to the curriculum and become available to questions and quizzes."
              data-confirm-title="Save this topic?"
              data-confirm-ok="Yes, save topic"
              data-confirm-icon="question">@csrf <input type="hidden" name="_method" id="topicMethod" value="POST">
            @if($version)<input type="hidden" name="curriculum_version_id" value="{{ $version->id }}">@endif
            <div class="modal-grid">
                <div class="form-group"><label>Topic Name</label><input type="text" name="name" required placeholder="Topic name"></div>
                <div class="form-group"><label>Display Order</label><input type="number" name="sort_order" min="0" max="9999" value="0" required></div>
                <div class="form-group full"><label>Parent Topic</label><select name="parent_id" id="topicParent"><option value="">— None (top-level topic) —</option></select><div class="hint">Choose an existing topic to make this a subtopic of it.</div></div>
                <div class="form-group full"><label>Description</label><textarea name="description" placeholder="Optional topic description"></textarea></div>
                <div class="toggle-row full"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" id="topicActive" value="1" checked><label for="topicActive">Active and available for questions and quizzes</label></div>
            </div>
            <div class="modal-actions"><button type="button" class="btn btn-ghost" onclick="closeModal('topicModal')">Cancel</button><button class="btn btn-primary"><i class="fas fa-save"></i> Save Topic</button></div>
        </form>
    </div>
</div>

{{-- Carrier form for the destructive curriculum actions; the confirmation itself
     is a SweetAlert dialog raised by openTopicDelete / openSubjectDelete / openTopicToggle. --}}
<form method="POST" id="confirmForm" hidden>@csrf <input type="hidden" name="_method" id="confirmMethod" value="DELETE"></form>

<script>
function getTopicForm() { return document.getElementById('topicForm'); }

function openSubject(subject = null) {
    const f = document.getElementById('subjectForm');
    if (!f) return;
    document.getElementById('subjectModalTitle').textContent = subject ? 'Edit Subject' : 'Add Subject';
    f.action = subject ? `/chair/subjects/${subject.id}` : @json(route('chair.subjects.store'));
    document.getElementById('subjectMethod').value = subject ? 'PUT' : 'POST';
    if (f.elements) {
        f.elements['code'].value = subject?.code ?? '';
        f.elements['name'].value = subject?.name ?? '';
        f.elements['description'].value = subject?.description ?? '';
        f.elements['passing_threshold'].value = subject?.passing_threshold ?? 75;
        f.elements['color'].value = subject?.color ?? '#7B1D1D';
    }
    document.getElementById('subjectActive').checked = subject ? Boolean(subject.is_active) : true;
    document.getElementById('subjectModal').classList.add('open');
}

function descendantIds(topics, rootId) {
    const ids = new Set();
    let frontier = [rootId];
    while (frontier.length) {
        const next = topics.filter(t => frontier.includes(t.parent_id)).map(t => t.id);
        next.forEach(id => ids.add(id));
        frontier = next;
    }
    return ids;
}

function populateParentOptions(subjectId, excludeTopicId, selectedParentId) {
    const el = document.getElementById(`topics-data-${subjectId}`);
    const topics = el ? JSON.parse(el.dataset.topics || '[]') : [];

    const excluded = excludeTopicId ? descendantIds(topics, excludeTopicId) : new Set();
    if (excludeTopicId) excluded.add(excludeTopicId);

    const byId = Object.fromEntries(topics.map(t => [t.id, t]));
    const depthOf = (t) => { let d = 0, p = t.parent_id; while (p) { d++; p = byId[p]?.parent_id; } return d; };

    const select = document.getElementById('topicParent');
    select.innerHTML = '<option value="">— None (top-level topic) —</option>';
    topics
        .filter(t => !excluded.has(t.id))
        .sort((a, b) => a.name.localeCompare(b.name))
        .forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = '—'.repeat(depthOf(t)) + ' ' + t.name;
            select.appendChild(opt);
        });
    select.value = selectedParentId ?? '';
}

function openTopic(subjectId, subjectCode, topic = null, parentId = null) {
    const f = getTopicForm();
    if (!f) return;
    document.getElementById('topicModalTitle').textContent = topic ? 'Edit Topic' : (parentId ? 'Add Subtopic' : 'Add Topic');
    document.getElementById('topicModalSub').textContent = `${topic ? 'Update a' : 'Add a new'} curriculum topic for ${subjectCode}.`;
    f.action = topic ? `/chair/subjects/${subjectId}/topics/${topic.id}` : `/chair/subjects/${subjectId}/topics`;
    document.getElementById('topicMethod').value = topic ? 'PUT' : 'POST';
    if (f.elements) {
        f.elements['name'].value = topic?.name ?? '';
        f.elements['description'].value = topic?.description ?? '';
        f.elements['sort_order'].value = topic?.sort_order ?? 0;
    }
    document.getElementById('topicActive').checked = topic ? Boolean(topic.is_active) : true;
    populateParentOptions(subjectId, topic?.id ?? null, topic?.parent_id ?? parentId);
    document.getElementById('topicModal').classList.add('open');
}

function applyChairTopicSearch(node, query) {
    const row = node.querySelector(':scope > .topic-row');
    const nameEl = row.querySelector('.topic-name');
    const selfMatch = nameEl.textContent.toLowerCase().includes(query);

    const childrenContainer = node.querySelector(':scope > .topic-children');
    let childMatch = false;
    if (childrenContainer) {
        childrenContainer.querySelectorAll(':scope > .topic-node').forEach(child => {
            if (applyChairTopicSearch(child, query)) childMatch = true;
        });
    }

    const visible = selfMatch || childMatch;
    node.style.display = visible ? '' : 'none';

    if (childrenContainer) {
        childrenContainer.hidden = !childMatch;
        const toggle = row.querySelector('.topic-toggle');
        if (toggle) toggle.classList.toggle('open', childMatch);
    }

    return visible;
}

function searchChairTopics(input, rawQuery) {
    const query = rawQuery.trim().toLowerCase();
    const list = input.closest('.sc-section').querySelector('.topic-list');
    if (!list) return;
    const topLevelNodes = list.querySelectorAll(':scope > .topic-node');

    if (!query) {
        list.querySelectorAll('.topic-node').forEach(node => { node.style.display = ''; });
        list.querySelectorAll('.topic-children').forEach(children => { children.hidden = true; });
        list.querySelectorAll('.topic-toggle').forEach(toggle => toggle.classList.remove('open'));
        return;
    }

    topLevelNodes.forEach(node => applyChairTopicSearch(node, query));
}

/* ── Subjects grid: search, filter, grid/list view, card "..." menu ── */
function filterSubjectCards() {
    const q = (document.getElementById('sjSearch')?.value || '').trim().toLowerCase();
    const filterEl = document.getElementById('sjFilter');
    const f = filterEl?.value || '';
    filterEl?.classList.toggle('is-set', f !== '');
    const tests = {
        active: c => c.dataset.state === 'active',
        inactive: c => c.dataset.state === 'inactive',
        no_faculty: c => c.dataset.faculty === '0',
        no_topics: c => c.dataset.topics === '0',
        low_coverage: c => Number(c.dataset.coverage) < 50,
    };
    let shown = 0;
    document.querySelectorAll('#sjGrid .sj-card').forEach(card => {
        const ok = (!q || card.dataset.search.includes(q)) && (!f || tests[f](card));
        card.style.display = ok ? '' : 'none';
        if (ok) shown++;
    });
    const none = document.getElementById('sjNoMatch');
    if (none) none.hidden = shown > 0 || !document.querySelector('#sjGrid .sj-card');
}
function clearSubjectFilters() {
    document.getElementById('sjSearch').value = '';
    document.getElementById('sjFilter').value = '';
    filterSubjectCards();
}

/* Curriculum card "..." menu (positioned inside the card, so a plain toggle works). */
function toggleCurMenu(event, btn) {
    event.stopPropagation();
    const menu = btn.closest('.cur-menu');
    const wasOpen = menu.classList.contains('open');
    document.querySelectorAll('.cur-menu.open').forEach(m => m.classList.remove('open'));
    if (!wasOpen) menu.classList.add('open');
}
document.addEventListener('click', () => document.querySelectorAll('.cur-menu.open').forEach(m => m.classList.remove('open')));

/* Subject row "..." menu. The rows lift on hover (a CSS transform), and a
   transformed parent makes "position:fixed" measure from the row instead of the
   screen — the menu then lands far from its button. So while open, the menu is
   moved to <body> and put back when it closes. */
function closeSubjectMenus() {
    document.querySelectorAll('.sj-dropdown.is-open').forEach(drop => {
        drop.classList.remove('is-open');
        if (drop._home) { drop._home.classList.remove('open'); drop._home.appendChild(drop); }
    });
}
function toggleSubjectMenu(event, btn) {
    event.stopPropagation();
    const menu = btn.closest('.sj-menu');
    menu._drop = menu._drop || menu.querySelector('.sj-dropdown');
    const drop = menu._drop;
    const wasOpen = drop.classList.contains('is-open');
    closeSubjectMenus();
    if (wasOpen) return;
    drop._home = menu;
    menu.classList.add('open');
    document.body.appendChild(drop);
    drop.classList.add('is-open');
    const r = btn.getBoundingClientRect();
    const below = r.bottom + 6 + drop.offsetHeight <= window.innerHeight;
    drop.style.top = (below ? r.bottom + 6 : Math.max(8, r.top - 6 - drop.offsetHeight)) + 'px';
    drop.style.left = Math.max(8, r.right - drop.offsetWidth) + 'px';
}
document.addEventListener('click', closeSubjectMenus);
window.addEventListener('scroll', closeSubjectMenus, true);
window.addEventListener('resize', closeSubjectMenus);
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeSubjectMenus(); document.querySelectorAll('.cur-menu.open').forEach(m => m.classList.remove('open')); } });

function toggleTopicChildren(button) {
    button.classList.toggle('open');
    const node = button.closest('.topic-node');
    const children = node.querySelector(':scope > .topic-children');
    if (children) children.hidden = !children.hidden;
}

/* Points the carrier form at `action`/`method`, then submits it once the
   chair confirms the dialog. */
function askThenSubmit(action, method, dialog) {
    CPACE.confirm(dialog).then(ok => {
        if (!ok) return;
        const f = document.getElementById('confirmForm');
        f.action = action;
        document.getElementById('confirmMethod').value = method;
        f.submit();
    });
}

function openTopicDelete(subjectId, topicId, topicName) {
    askThenSubmit(`/chair/subjects/${subjectId}/topics/${topicId}`, 'DELETE', {
        title: 'Remove this topic?',
        html: `"${topicName}" will be removed from this subject.`
            + '<div class="cpace-note">Topics containing questions or subtopics are protected and cannot be removed &mdash; mark them inactive instead.</div>',
        confirmText: 'Yes, remove topic',
        danger: true,
    });
}

function openSubjectDelete(subjectId, subjectCode) {
    askThenSubmit(`/chair/subjects/${subjectId}`, 'DELETE', {
        title: 'Remove this subject?',
        html: `"${subjectCode}" will be removed from the curriculum.`
            + '<div class="cpace-note">Subjects containing topics or assigned faculty cannot be removed. Unlink those first, or mark the subject inactive.</div>',
        confirmText: 'Yes, remove subject',
        danger: true,
    });
}

function openTopicToggle(subjectId, topicId, topicName, makeActive) {
    askThenSubmit(`/chair/subjects/${subjectId}/topics/${topicId}/toggle`, 'PATCH', {
        title: (makeActive ? 'Enable' : 'Disable') + ' this topic?',
        html: makeActive
            ? `"${topicName}" will become visible and available for questions and quizzes.`
            : `"${topicName}" will be hidden. Existing questions and subtopics stay, but won't appear in quizzes.`,
        icon: makeActive ? 'question' : 'warning',
        confirmText: makeActive ? 'Yes, enable it' : 'Yes, disable it',
        danger: !makeActive,
    });
}

@if($version?->isDraft())
function publishDraft() {
    askThenSubmit(@json(route('chair.curriculum.publish', $version)), 'POST', {
        title: 'Publish this curriculum?',
        html: @json('"' . $version->label . '" becomes the curriculum students study, and quizzes switch to its topics immediately.')
            + '<div class="cpace-note">The current curriculum becomes read-only history. Every subject needs at least one active topic in this draft.</div>',
        confirmText: 'Yes, publish it',
        icon: 'warning',
    });
}

function discardDraft() {
    askThenSubmit(@json(route('chair.curriculum.destroy', $version)), 'DELETE', {
        title: 'Discard this draft?',
        html: 'Every topic and copied question in this draft will be deleted.'
            + '<div class="cpace-note">Nothing students see is affected &mdash; they are still on the current curriculum.</div>',
        confirmText: 'Yes, discard draft',
        danger: true,
    });
}

function copyQuestions(subjectId, subjectCode) {
    askThenSubmit(@json(url('/chair/curriculum/' . $version->id . '/subjects')) + '/' + subjectId + '/copy-questions', 'POST', {
        title: `Copy ${subjectCode} questions?`,
        html: `${subjectCode}'s Test Bank questions in the current curriculum will be copied into the matching topics of this draft (matched by topic name).`
            + '<div class="cpace-note">The originals stay untouched. Questions already copied are skipped, so this is safe to run again after adding more topics.</div>',
        confirmText: 'Yes, copy them',
        icon: 'question',
    });
}
@endif

function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(modal => modal.addEventListener('click', event => { if (event.target === modal) modal.classList.remove('open'); }));
document.addEventListener('keydown', event => { if (event.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(modal => modal.classList.remove('open')); });
</script>

    @include('partials.alerts')
</body>
</html>
