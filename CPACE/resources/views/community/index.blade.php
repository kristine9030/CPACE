<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Alumni Community - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:#7B1D1D; --primary-hover:#641717; --primary-light:#f5e8e8; --primary-soft:#fbf3f3;
            --accent:#c0392b;
            --ink:#14283E; --text:#2b2b33; --muted:#6b7280; --faint:#9ca3af;
            --line:#ececf0; --line-2:#f3f3f6; --canvas:#f4f5f7; --surface:#fff;
            --radius:16px;
            --shadow:0 1px 2px rgba(16,24,40,.04), 0 4px 14px rgba(16,24,40,.04);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:var(--canvas); color:var(--text); }
        a { color:inherit; }
        button { font-family:inherit; }
        :focus-visible { outline:3px solid rgba(123,29,29,.28); outline-offset:2px; }

        /* Any avatar box: fills with the user's photo or initials on their colour. */
        .av { border-radius:50%; overflow:hidden; flex-shrink:0; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; background:var(--primary); }
        .av img { width:100%; height:100%; object-fit:cover; }
        .av .avatar-default { width:100%; height:100%; display:flex; align-items:center; justify-content:center; }

        /* ── Layout: header on top, three columns that travel together ── */
        /* This page is shared by every role. The student sidebar lays out
           .main-content, but the alumni sidebar only sets .main's margin and
           the faculty sidebar leaves it to each page, so set both here. */
        .main { margin-left:230px; padding:28px 30px 40px; transition:margin-left .28s cubic-bezier(.4,0,.2,1); }
        .sidebar.collapsed ~ .main { margin-left:68px; }
        @media (max-width:900px) { .main { margin-left:68px; } }
        .page { max-width:1360px; margin:0 auto; }
        .fb-page { display:grid; grid-template-columns:260px minmax(0,1fr) 290px; gap:24px; align-items:start; }
        .feed-wrap { min-width:0; max-width:720px; width:100%; margin:0 auto; padding-bottom:40px; }
        .rail { position:sticky; top:18px; display:flex; flex-direction:column; gap:16px; }

        /* ── Global top bar (same as the other student pages) ── */
        .top-bar { display:flex; justify-content:space-between; align-items:center; gap:20px; margin-bottom:26px; }
        .page-title { font-family:'Montserrat',sans-serif; font-size:30px; font-weight:700; color:var(--ink); margin-bottom:6px; padding-bottom:10px; position:relative; }
        .page-title::after { content:''; position:absolute; left:0; bottom:0; width:46px; height:3px; border-radius:2px; background:linear-gradient(90deg,#c0392b,#7B1D1D); }
        .page-subtitle { font-size:14px; color:#999; }
        .top-bar-right { display:flex; align-items:center; gap:14px; }
        .search-wrap { position:relative; }
        .search-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#aaa; font-size:14px; }
        .search-wrap input { width:280px; padding:10px 14px 10px 36px; border:1px solid #e0e0e0; border-radius:24px; font-size:13px; font-family:'Poppins',sans-serif; background:#fff; color:#555; outline:none; }
        .search-wrap input:focus { border-color:var(--primary); }
        .search-wrap input::placeholder { color:#bbb; }
        .notif-btn { position:relative; width:40px; height:40px; border:none; background:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:17px; color:#555; cursor:pointer; text-decoration:none; box-shadow:0 1px 4px rgba(0,0,0,.08); }
        .notif-btn:hover { background:#f0f0f0; }
        .badge { position:absolute; top:-3px; right:-3px; width:18px; height:18px; background:var(--accent); color:#fff; border-radius:50%; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; }
        .header-dropdown-wrap { position:relative; }
        .profile-avatar { width:40px; height:40px; background:var(--primary); border-radius:10px; border:none; padding:0; color:#fff; font-weight:700; font-size:14px; cursor:pointer; overflow:hidden; display:flex; align-items:center; justify-content:center; }
        .profile-avatar img { width:100%; height:100%; object-fit:cover; }
        .profile-avatar .avatar-default { width:100%; height:100%; display:flex; align-items:center; justify-content:center; }
        .dropdown-menu { position:absolute; top:calc(100% + 8px); right:0; background:#fff; border:1px solid #e5e7eb; border-radius:10px; min-width:190px; box-shadow:0 6px 20px rgba(0,0,0,.12); display:none; z-index:2000; overflow:hidden; }
        .dropdown-menu.show { display:block; }
        .dropdown-menu a, .dropdown-menu button { display:flex; align-items:center; gap:10px; width:100%; padding:11px 16px; font-size:13px; color:#333; background:none; border:none; border-bottom:1px solid #f5f5f5; text-align:left; cursor:pointer; text-decoration:none; }
        .dropdown-menu form:last-child button { border-bottom:none; }
        .dropdown-menu a:hover, .dropdown-menu button:hover { background:#f9f9f9; }
        .dropdown-menu i { width:16px; text-align:center; color:var(--primary); }
        .dropdown-menu .logout-btn, .dropdown-menu .logout-btn i { color:#e53e3e; }

        /* ── Cards ── */
        .card { background:var(--surface); border:1px solid var(--line); border-radius:var(--radius); box-shadow:var(--shadow); margin-bottom:16px; }
        .rail .card { margin-bottom:0; }
        .card-head { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:16px 18px 10px; }
        .card-head h3 { font-size:14px; font-weight:700; color:var(--ink); display:flex; align-items:center; gap:8px; }
        .card-head h3 i { color:var(--primary); font-size:13px; }
        .count-pill { font-size:11px; font-weight:700; color:var(--primary); background:var(--primary-light); border-radius:999px; padding:2px 9px; }
        .section-label { padding:14px 18px 6px; font-size:11px; font-weight:700; color:var(--faint); text-transform:uppercase; letter-spacing:.7px; }

        /* ── Explore rail ── */
        .nav-list { padding:8px; }
        .explore-link { display:flex; align-items:center; gap:12px; padding:10px 10px; border-radius:11px; text-decoration:none; font-size:14px; font-weight:600; color:var(--text); transition:background .15s; }
        .explore-link .ico { width:34px; height:34px; border-radius:10px; display:grid; place-items:center; background:var(--line-2); color:var(--muted); font-size:14px; transition:background .15s, color .15s; }
        .explore-link:hover { background:var(--primary-soft); }
        .explore-link.active { background:var(--primary-light); color:var(--primary); }
        .explore-link.active .ico { background:var(--primary); color:#fff; }

        .subject-list { padding:0 8px 10px; max-height:calc(100vh - 330px); overflow-y:auto; }
        .subject-link { display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:10px; text-decoration:none; font-size:13px; color:var(--text); transition:background .15s; }
        .subject-link:hover { background:var(--primary-soft); }
        .subject-code { flex-shrink:0; min-width:48px; text-align:center; font-size:10.5px; font-weight:700; letter-spacing:.4px; color:var(--primary); background:var(--primary-light); border-radius:7px; padding:4px 6px; }
        .subject-name { flex:1; min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:var(--muted); font-weight:500; }
        .subject-count { font-size:11px; font-weight:600; color:var(--faint); }
        .subject-link.active { background:var(--primary-light); }
        .subject-link.active .subject-code { background:var(--primary); color:#fff; }
        .subject-link.active .subject-name { color:var(--primary); font-weight:600; }

        /* Subject chips replace the explore rail on narrower screens. */
        .chip-bar { display:none; gap:8px; overflow-x:auto; padding-bottom:4px; margin-bottom:14px; scrollbar-width:none; }
        .chip-bar::-webkit-scrollbar { display:none; }
        .chip { flex-shrink:0; padding:7px 14px; border-radius:999px; border:1px solid var(--line); background:#fff; font-size:12.5px; font-weight:600; color:var(--muted); text-decoration:none; }
        .chip.active { background:var(--primary); border-color:var(--primary); color:#fff; }

        /* ── Quote carousel ── */
        .hero { position:relative; overflow:hidden; border-radius:var(--radius); margin-bottom:16px; color:#fff;
            background:radial-gradient(ellipse 60% 90% at 100% 0%, rgba(255,255,255,.10), transparent 60%), linear-gradient(135deg,#8f2520 0%,#7B1D1D 45%,#4a1010 100%);
            box-shadow:0 10px 26px -12px rgba(90,20,20,.55); }
        .hero::after { content:'\f10e'; font-family:'Font Awesome 6 Free'; font-weight:900; position:absolute; right:22px; bottom:-18px; font-size:120px; color:rgba(255,255,255,.07); pointer-events:none; }
        .hero-top { display:flex; align-items:center; gap:8px; padding:18px 24px 0; font-size:11px; font-weight:700; letter-spacing:.8px; text-transform:uppercase; color:rgba(255,255,255,.7); }
        .hero-top .live { width:7px; height:7px; border-radius:50%; background:#ffd76a; box-shadow:0 0 0 4px rgba(255,215,106,.2); }
        .hero-viewport { overflow:hidden; }
        .hero-track { display:flex; transition:transform .5s cubic-bezier(.4,0,.2,1); }
        .hero-slide { flex:0 0 100%; min-width:100%; padding:10px 24px 18px; }
        .hero-quote { font-size:19px; line-height:1.45; font-weight:600; letter-spacing:-.01em; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }
        .hero-attr { margin-top:10px; font-size:13px; font-weight:700; color:#ffd76a; }
        .hero-by { font-size:11.5px; color:rgba(255,255,255,.6); margin-top:2px; }
        .hero-foot { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:0 20px 16px 24px; }
        .hero-dots { display:flex; gap:6px; }
        .hero-dot { width:7px; height:7px; border-radius:50%; border:0; padding:0; cursor:pointer; background:rgba(255,255,255,.3); transition:background .2s, width .2s; }
        .hero-dot.on { background:#fff; width:20px; border-radius:4px; }
        .hero-arrows { display:flex; gap:6px; }
        .hero-arrow { width:30px; height:30px; border-radius:50%; border:1px solid rgba(255,255,255,.2); cursor:pointer; background:rgba(255,255,255,.08); color:#fff; font-size:11px; display:grid; place-items:center; transition:background .15s; }
        .hero-arrow:hover { background:rgba(255,255,255,.2); }

        .filter-note { display:flex; justify-content:space-between; align-items:center; gap:10px; padding:11px 16px; margin-bottom:16px; border-radius:12px; background:var(--primary-light); color:var(--primary); font-size:13px; }
        .filter-note a { font-weight:600; text-decoration:none; }

        /* ── Composer ── */
        .composer { padding:16px 18px; }
        .composer-trigger { display:flex; align-items:center; gap:12px; }
        .composer-trigger .av { width:42px; height:42px; font-size:14px; }
        .composer-fake-input { flex:1; background:var(--line-2); border:1px solid transparent; border-radius:999px; padding:11px 18px; font-size:14px; color:var(--faint); cursor:pointer; transition:background .15s, border-color .15s; }
        .composer-fake-input:hover { background:#fff; border-color:var(--line); }
        .composer-quick { display:flex; gap:6px; margin-top:12px; padding-top:12px; border-top:1px solid var(--line-2); }
        .quick-btn { flex:1; display:flex; align-items:center; justify-content:center; gap:8px; padding:9px; border:none; background:transparent; border-radius:10px; font-size:13px; font-weight:600; color:var(--muted); cursor:pointer; transition:background .15s; }
        .quick-btn:hover { background:var(--line-2); }
        .quick-btn i { font-size:15px; }
        .composer-full { display:none; }
        .composer-full.open { display:block; }
        .composer.is-open .composer-trigger, .composer.is-open .composer-quick { display:none; }
        .composer-title { font-size:14px; font-weight:700; color:var(--ink); margin-bottom:14px; display:flex; align-items:center; justify-content:space-between; }
        .composer-close { border:none; background:var(--line-2); width:30px; height:30px; border-radius:50%; cursor:pointer; color:var(--muted); }
        .kind-toggle { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:12px; }
        .kind-opt input { position:absolute; opacity:0; pointer-events:none; }
        .kind-opt label { display:flex; align-items:center; justify-content:center; gap:8px; padding:10px; border:1.5px solid var(--line); border-radius:11px; font-size:12.5px; font-weight:600; color:var(--muted); cursor:pointer; transition:all .15s; }
        .kind-opt input:checked + label { border-color:var(--primary); background:var(--primary-light); color:var(--primary); }
        .kind-opt input:focus-visible + label { outline:3px solid rgba(123,29,29,.28); }
        .field { margin-bottom:12px; }
        .field label { display:block; font-size:11.5px; font-weight:600; color:var(--muted); margin-bottom:6px; }
        .field label .opt { color:var(--faint); font-weight:400; }
        .field textarea, .field input[type=text], .field select { width:100%; font-family:'Poppins',sans-serif; font-size:13.5px; color:var(--text); border:1px solid var(--line); border-radius:11px; padding:11px 13px; outline:none; background:#fff; transition:border-color .15s, box-shadow .15s; }
        .field textarea:focus, .field input:focus, .field select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(123,29,29,.08); }
        .field textarea { resize:vertical; min-height:90px; line-height:1.55; }
        .field-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .file-drop { display:flex !important; align-items:center; justify-content:center; gap:8px; border:1.5px dashed #dcd3d3; border-radius:11px; padding:13px; color:var(--faint); font-size:12.5px; cursor:pointer; transition:border-color .15s, background .15s; margin:0 !important; }
        .file-drop:hover { border-color:var(--primary); background:var(--primary-soft); }
        .file-drop i { color:var(--primary); }
        .file-drop .fname { color:var(--primary); font-weight:600; }
        .btn-primary { background:linear-gradient(135deg,#5c1515,#8B2525); color:#fff; border:none; padding:11px 22px; border-radius:11px; font-size:13.5px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 6px 14px rgba(123,29,29,.22); transition:box-shadow .15s, transform .05s; }
        .btn-primary:hover { box-shadow:0 8px 18px rgba(123,29,29,.3); }
        .btn-primary:active { transform:translateY(1px); }
        .composer-foot { display:flex; justify-content:flex-end; margin-top:4px; }

        /* ── Post ── */
        .post { padding:18px 20px 8px; transition:border-color .2s, box-shadow .2s; }
        .post:hover { border-color:#e5dada; box-shadow:0 2px 4px rgba(16,24,40,.04), 0 10px 24px rgba(16,24,40,.06); }
        .post-head { display:flex; align-items:flex-start; gap:12px; }
        .post-head .av { width:44px; height:44px; font-size:14px; }
        .post-who { flex:1; min-width:0; }
        .post-name { font-size:14.5px; font-weight:700; color:var(--ink); display:flex; align-items:center; gap:7px; flex-wrap:wrap; }
        .role-badge { font-size:9.5px; font-weight:700; background:var(--primary-light); color:var(--primary); padding:2px 8px; border-radius:999px; text-transform:uppercase; letter-spacing:.5px; }
        .pin-badge { font-size:10px; font-weight:700; color:#b45309; background:#fef3c7; padding:2px 8px; border-radius:999px; }
        .post-meta { font-size:12px; color:var(--faint); margin-top:2px; display:flex; flex-wrap:wrap; align-items:center; gap:4px 6px; }
        .post-meta .sep { color:#d1d5db; }
        .subject-chip { font-size:10.5px; font-weight:700; color:var(--primary); background:var(--primary-light); padding:2px 8px; border-radius:6px; }

        .post-menu { position:relative; }
        .icon-btn { width:34px; height:34px; border-radius:50%; border:none; background:transparent; color:var(--faint); cursor:pointer; font-size:15px; transition:background .15s, color .15s; }
        .icon-btn:hover { background:var(--line-2); color:var(--text); }
        .menu-dropdown { display:none; position:absolute; right:0; top:38px; background:#fff; border:1px solid var(--line); border-radius:12px; box-shadow:0 10px 30px rgba(16,24,40,.14); min-width:200px; z-index:50; overflow:hidden; padding:6px; }
        .menu-dropdown.open { display:block; }
        .menu-dropdown button, .menu-dropdown a { display:flex; align-items:center; gap:10px; width:100%; text-align:left; border:none; background:none; padding:10px 12px; border-radius:8px; font-size:13px; color:var(--text); cursor:pointer; text-decoration:none; }
        .menu-dropdown button:hover, .menu-dropdown a:hover { background:var(--line-2); }
        .menu-dropdown i { width:16px; color:var(--muted); }
        .menu-dropdown .danger, .menu-dropdown .danger i { color:var(--accent); }

        .post-body { font-size:15px; color:var(--text); line-height:1.65; margin-top:14px; white-space:pre-line; word-wrap:break-word; }
        .post-body.quote { position:relative; padding:18px 20px 18px 52px; border-radius:14px; background:linear-gradient(135deg,#fbf3f3,#fff); border:1px solid #f1e3e3; font-size:16px; font-weight:500; font-style:italic; color:#4a1414; }
        .post-body.quote i.fa-quote-left { position:absolute; left:18px; top:18px; color:#e4b9b9; font-size:20px; }
        .quote-attr { display:block; margin-top:10px; font-size:12.5px; font-weight:700; font-style:normal; color:var(--primary); }

        .attachment { display:flex; align-items:center; gap:12px; padding:12px 14px; background:#fafafb; border:1px solid var(--line); border-radius:12px; margin-top:12px; text-decoration:none; transition:border-color .15s, background .15s; }
        .attachment:hover { background:#fff; border-color:#e0cfcf; }
        .att-icon { width:40px; height:40px; border-radius:10px; display:grid; place-items:center; font-size:16px; color:#fff; flex-shrink:0; }
        .att-info { flex:1; min-width:0; }
        .att-title { font-size:13px; font-weight:600; color:var(--ink); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .att-meta { font-size:11.5px; color:var(--faint); margin-top:2px; }
        .att-go { width:32px; height:32px; border-radius:50%; display:grid; place-items:center; background:#fff; border:1px solid var(--line); color:var(--muted); font-size:12px; flex-shrink:0; }
        .attachment:hover .att-go { color:var(--primary); border-color:#e0cfcf; }

        .post-stats { display:flex; justify-content:space-between; align-items:center; margin-top:14px; font-size:12.5px; color:var(--faint); }
        .stat-likes { display:flex; align-items:center; gap:6px; }
        .stat-likes .dot { width:18px; height:18px; border-radius:50%; background:var(--primary); color:#fff; display:grid; place-items:center; font-size:8.5px; }
        .post-actions { display:flex; gap:6px; margin-top:10px; padding-top:6px; border-top:1px solid var(--line-2); }
        .act-btn { flex:1; display:flex; align-items:center; justify-content:center; gap:8px; border:none; background:transparent; color:var(--muted); font-size:13px; font-weight:600; padding:9px 12px; border-radius:10px; cursor:pointer; transition:background .15s, color .15s; }
        .act-btn:hover { background:var(--line-2); color:var(--text); }
        .act-btn.liked { color:var(--primary); }
        .act-btn.liked:hover { background:var(--primary-soft); }

        .comments { border-top:1px solid var(--line-2); padding:12px 0 8px; margin-top:6px; }
        .comment { display:flex; gap:10px; margin-bottom:10px; align-items:flex-start; }
        .comment .av { width:30px; height:30px; font-size:10.5px; }
        .comment-bubble { background:var(--line-2); border-radius:4px 14px 14px 14px; padding:9px 13px; max-width:calc(100% - 80px); }
        .comment-name { display:block; font-size:12.5px; font-weight:700; color:var(--ink); margin-bottom:1px; }
        .comment-text { font-size:13.5px; color:var(--text); line-height:1.5; word-wrap:break-word; }
        .comment-del { border:none; background:none; color:#cbd0d6; font-size:11px; cursor:pointer; padding:6px; border-radius:50%; align-self:center; }
        .comment-del:hover { color:var(--accent); background:var(--line-2); }
        .comment-form { display:flex; gap:10px; align-items:center; margin-top:4px; }
        .comment-form .av { width:30px; height:30px; font-size:10.5px; }
        .comment-input { flex:1; display:flex; align-items:center; background:var(--line-2); border:1px solid transparent; border-radius:999px; padding-right:4px; transition:border-color .15s, background .15s; }
        .comment-input:focus-within { background:#fff; border-color:var(--line); }
        .comment-input input { flex:1; min-width:0; border:none; background:transparent; padding:9px 14px; font-size:13px; font-family:'Poppins',sans-serif; outline:none; }
        .comment-input button { border:none; background:transparent; color:var(--primary); width:32px; height:32px; border-radius:50%; cursor:pointer; font-size:13px; }
        .comment-input button:hover { background:var(--primary-light); }
        .open-hidden { display:none; }

        .empty { text-align:center; padding:48px 24px; color:var(--faint); }
        .empty-art { width:84px; height:84px; margin:0 auto 16px; border-radius:24px; background:var(--primary-light); display:grid; place-items:center; }
        .empty-art i { font-size:32px; color:var(--primary); opacity:.7; }
        .empty-title { font-size:15px; font-weight:700; color:var(--ink); margin-bottom:5px; }
        .empty-sub { font-size:13px; line-height:1.55; max-width:340px; margin:0 auto; }

        .pagination { display:flex; justify-content:center; margin-top:10px; }
        .pagination nav > div:first-child { display:none; }
        .pagination a, .pagination span { font-size:12px; }

        /* ── Right rail ── */
        .contrib-list { padding:0 10px 12px; }
        .contrib-item { display:flex; align-items:center; gap:11px; padding:8px; border-radius:11px; }
        .contrib-item:hover { background:var(--line-2); }
        .contrib-rank { width:22px; height:22px; flex-shrink:0; border-radius:50%; display:grid; place-items:center; font-size:11px; font-weight:800; color:var(--muted); background:var(--line-2); }
        .contrib-item:nth-child(1) .contrib-rank { background:#fef3c7; color:#b45309; }
        .contrib-item:nth-child(2) .contrib-rank { background:#eef0f3; color:#4b5563; }
        .contrib-item:nth-child(3) .contrib-rank { background:#fbe7dc; color:#9a4a1f; }
        .contrib-item .av { width:34px; height:34px; font-size:11.5px; }
        .person-name { font-size:13px; font-weight:600; color:var(--ink); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .person-sub { font-size:11px; color:var(--faint); }
        .rail-empty { padding:4px 18px 16px; font-size:12px; color:var(--faint); }

        .contact-search { margin:0 12px 6px; position:relative; }
        .contact-search i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--faint); font-size:12px; }
        .contact-search input { width:100%; padding:8px 12px 8px 32px; border:1px solid var(--line); border-radius:10px; background:var(--line-2); font-size:12.5px; font-family:'Poppins',sans-serif; outline:none; }
        .contact-search input:focus { background:#fff; border-color:var(--primary); }
        .contact-list { max-height:calc(100vh - 360px); min-height:160px; overflow-y:auto; padding:0 8px 10px; scrollbar-width:thin; scrollbar-color:#e2d6d6 transparent; }
        .contact-list::-webkit-scrollbar { width:6px; }
        .contact-list::-webkit-scrollbar-thumb { background:#e2d6d6; border-radius:6px; }
        .contact-group { display:flex; align-items:center; justify-content:space-between; padding:10px 8px 4px; }
        .contact-group span { font-size:10.5px; font-weight:700; color:var(--faint); text-transform:uppercase; letter-spacing:.7px; }
        .contact-group b { font-size:10.5px; font-weight:700; color:var(--muted); }
        .contact-item { display:flex; align-items:center; gap:10px; width:100%; padding:7px 8px; border:none; background:none; text-align:left; border-radius:10px; cursor:pointer; transition:background .15s; }
        .contact-item:hover { background:var(--primary-soft); }
        .contact-item .av { width:34px; height:34px; font-size:11.5px; }
        .contact-item .msg-hint { margin-left:auto; color:var(--faint); font-size:12px; opacity:0; transition:opacity .15s; }
        .contact-item:hover .msg-hint { opacity:1; color:var(--primary); }

        /* The chair sidebar ships global .card / .card-head / input rules for
           its own pages; undo them here so every role sees the same feed. */
        .page .card { padding:0; border-radius:var(--radius); }
        .page .card + .card { margin-top:0; }
        .page .card.composer { padding:16px 18px; }
        .page .card.post { padding:18px 20px 8px; }
        .page .card-head { margin-bottom:0; }
        .page .search-wrap input { padding:10px 14px 10px 36px; border:1px solid #e0e0e0; border-radius:24px; }
        .page .comment-input input { width:auto; padding:9px 14px; border:none; border-radius:0; background:transparent; }

        /* ── Responsive ── */
        @media (max-width:1360px) { .fb-page { grid-template-columns:240px minmax(0,1fr) 270px; gap:20px; } }
        @media (max-width:1240px) {
            .fb-page { grid-template-columns:minmax(0,1fr) 280px; }
            .explore-rail { display:none; }
            .chip-bar { display:flex; }
        }
        @media (max-width:980px) {
            .fb-page { grid-template-columns:minmax(0,1fr); }
            .contacts-rail { display:none; }
            .search-wrap input { width:180px; }
        }
        @media (max-width:768px) {
            .main { padding:16px 12px 90px; }
            .top-bar { flex-direction:column; align-items:flex-start; gap:12px; }
            .top-bar-right { width:100%; }
            .search-wrap { flex:1; }
            .search-wrap input { width:100%; }
            .page-title { font-size:22px; }
            .page .card.post { padding:16px 16px 6px; }
            .hero-quote { font-size:16px; -webkit-line-clamp:4; }
            .field-row, .kind-toggle { grid-template-columns:1fr; }
            .quick-btn span { display:none; }
        }
        @media (prefers-reduced-motion:reduce) { .hero-track { transition:none; } }
    </style>
</head>
<body>

@if(Auth::user()->isAlumni())
    @include('partials.alumni-sidebar', ['active' => 'community'])
@elseif(Auth::user()->isChair())
    @include('partials.chair-sidebar', ['active' => 'community'])
@elseif(Auth::user()->isFaculty())
    @include('partials.faculty-sidebar', ['active' => ''])
@else
    @include('partials.sidebar', ['active' => 'community'])
@endif

@php
    $me = Auth::user();
    $canPost = $me->hasAlumniAccess() || $me->isChair();
    $roleLabel = fn ($u) => match (true) {
        $u === null      => 'Member',
        $u->isChair()    => 'Program Chair',
        $u->isFaculty()  => 'Faculty',
        default          => 'Alumni',
    };
@endphp

{{-- .main is used by the faculty/chair/alumni sidebars, .main-content by the student sidebar --}}
<main class="main main-content">
<div class="page">

    <div class="top-bar">
        <div>
            <div class="page-title">Alumni Community</div>
            <div class="page-subtitle">Motivation, updates, and study materials shared by our alumni.</div>
        </div>
        <div class="top-bar-right">
            <div class="search-wrap gs-wrap">
                <i class="fas fa-search"></i>
                <input type="text" data-gs="true" placeholder="Search topics, questions..." autocomplete="off">
            </div>
            <a class="notif-btn" href="{{ route('messages.index') }}" title="Messages" aria-label="Messages">
                <i class="fas fa-comment-dots"></i>
                @if($unreadMessages > 0)<span class="badge">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>@endif
            </a>
            <a class="notif-btn" href="{{ route('notifications.index') }}" title="Notifications" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                @if($unreadNotifications > 0)<span class="badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>@endif
            </a>
            <div class="header-dropdown-wrap">
                <button class="profile-avatar" id="profileBtn" aria-label="Account menu">@include('partials.avatar-content')</button>
                <div class="dropdown-menu" id="profileDropdown">
                    {{-- Shared by students, alumni, faculty and the chair, so role-specific links stay behind a guard. --}}
                    @if($me->isStudent())
                        <a href="{{ route('performance') }}"><i class="fas fa-chart-line"></i> My Progress</a>
                        <a href="{{ route('achievements') }}"><i class="fas fa-trophy"></i> Achievements</a>
                    @elseif($me->isAlumni())
                        <a href="{{ route('alumni.profile') }}"><i class="fas fa-user"></i> My Profile</a>
                    @endif
                    <a href="{{ route('help.index') }}"><i class="fas fa-question-circle"></i> Help &amp; Support</a>
                    <form method="POST" action="{{ route('logout') }}"
                          data-confirm="You will be signed out of CPACE and returned to the login page."
                          data-confirm-title="Log out of CPACE?"
                          data-confirm-ok="Yes, log me out"
                          data-confirm-icon="question" style="margin:0;padding:0;">
                        @csrf
                        <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="fb-page">

    {{-- ── Left: explore ── --}}
    <aside class="rail explore-rail" id="exploreRail" aria-label="Community navigation">
        <div class="card">
            <div class="nav-list">
                <a href="{{ route('community.index') }}" class="explore-link {{ !$activeSubjectId ? 'active' : '' }}">
                    <span class="ico"><i class="fas fa-house"></i></span> All Posts
                </a>
                <a href="{{ route('community.resources.index') }}" class="explore-link">
                    <span class="ico"><i class="fas fa-book"></i></span> Resource Library
                </a>
                <a href="{{ route('messages.index') }}" class="explore-link">
                    <span class="ico"><i class="fas fa-comment-dots"></i></span> Messages
                </a>
            </div>

            @if($subjects->isNotEmpty())
                <div class="section-label">Browse by subject</div>
                <div class="subject-list">
                    @foreach($subjects as $s)
                        <a href="{{ route('community.index', ['subject_id' => $s->id]) }}" class="subject-link {{ $activeSubjectId === $s->id ? 'active' : '' }}" title="{{ $s->name }}">
                            <span class="subject-code">{{ $s->code }}</span>
                            <span class="subject-name">{{ $s->name }}</span>
                            <span class="subject-count">{{ $postsPerSubject[$s->id] ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </aside>

    {{-- ── Center: feed ── --}}
    <div class="feed-wrap">

        @if($subjects->isNotEmpty())
            <nav class="chip-bar" aria-label="Filter by subject">
                <a class="chip {{ !$activeSubjectId ? 'active' : '' }}" href="{{ route('community.index') }}">All posts</a>
                @foreach($subjects as $s)
                    <a class="chip {{ $activeSubjectId === $s->id ? 'active' : '' }}" href="{{ route('community.index', ['subject_id' => $s->id]) }}">{{ $s->code }}</a>
                @endforeach
                <a class="chip" href="{{ route('community.resources.index') }}"><i class="fas fa-book"></i> Library</a>
            </nav>
        @endif

        {{-- Sliding motivational header, built from alumni-posted quotes. --}}
        @if($quotes->isNotEmpty())
            <section class="hero" id="hero" data-count="{{ $quotes->count() }}" aria-roledescription="carousel" aria-label="Words from our alumni">
                <div class="hero-top"><span class="live"></span> Words from our alumni</div>
                <div class="hero-viewport">
                    <div class="hero-track" id="heroTrack">
                        @foreach($quotes as $q)
                            <div class="hero-slide">
                                <div class="hero-quote">“{{ $q->body }}”</div>
                                @if($q->title)<div class="hero-attr">— {{ $q->title }}</div>@endif
                                <div class="hero-by">Shared by {{ $q->author->name ?? 'an alumnus' }} · {{ $q->created_at?->diffForHumans() }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @if($quotes->count() > 1)
                    <div class="hero-foot">
                        <div class="hero-dots" id="heroDots">
                            @foreach($quotes as $i => $q)
                                <button type="button" class="hero-dot {{ $i === 0 ? 'on' : '' }}" data-go="{{ $i }}" aria-label="Quote {{ $i + 1 }}"></button>
                            @endforeach
                        </div>
                        <div class="hero-arrows">
                            <button type="button" class="hero-arrow" id="heroPrev" aria-label="Previous quote"><i class="fas fa-chevron-left"></i></button>
                            <button type="button" class="hero-arrow" id="heroNext" aria-label="Next quote"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                @else
                    <div style="height:6px;"></div>
                @endif
            </section>
        @endif

        @if($activeSubjectId)
            @php $activeSubject = $subjects->firstWhere('id', $activeSubjectId); @endphp
            <div class="filter-note">
                <span><i class="fas fa-filter"></i> Showing posts about <strong>{{ $activeSubject->code ?? 'this subject' }}</strong></span>
                <a href="{{ route('community.index') }}">Clear <i class="fas fa-times"></i></a>
            </div>
        @endif

        {{-- Status and validation messages surface as SweetAlert popups via partials.alerts --}}

        @if($canPost)
            <div class="card composer" id="composer">
                <div class="composer-trigger" id="composerTrigger">
                    <div class="av">@include('partials.avatar-content')</div>
                    <div class="composer-fake-input" role="button" tabindex="0">What's on your mind, {{ $me->first_name }}?</div>
                </div>
                <div class="composer-quick">
                    <button type="button" class="quick-btn" data-compose="discussion"><i class="fas fa-pen-to-square" style="color:#7B1D1D"></i><span>Post</span></button>
                    <button type="button" class="quick-btn" data-compose="tip"><i class="fas fa-quote-right" style="color:#d97706"></i><span>Quote</span></button>
                    <button type="button" class="quick-btn" data-compose="file"><i class="fas fa-paperclip" style="color:#0d9488"></i><span>Material</span></button>
                </div>

                <div class="composer-full" id="composerFull">
                    <div class="composer-title">
                        <span><i class="fas fa-pen" style="color:var(--primary);margin-right:6px"></i> Share with the community</span>
                        <button type="button" class="composer-close" id="composerClose" aria-label="Close composer"><i class="fas fa-xmark"></i></button>
                    </div>
                    <form method="POST" action="{{ route('community.posts.store') }}" enctype="multipart/form-data" id="composerForm"
                          data-confirm="Your post will be visible to everyone in the CPACE community feed."
                          data-confirm-title="Publish this post?"
                          data-confirm-ok="Yes, publish it"
                          data-confirm-icon="question"
                          data-loading="Publishing your post...">
                        @csrf
                        <div class="kind-toggle">
                            <div class="kind-opt">
                                <input type="radio" name="post_type" id="typePost" value="discussion" checked>
                                <label for="typePost"><i class="fas fa-comment-dots"></i> Post / Update</label>
                            </div>
                            <div class="kind-opt">
                                <input type="radio" name="post_type" id="typeQuote" value="tip">
                                <label for="typeQuote"><i class="fas fa-quote-right"></i> Motivational Quote</label>
                            </div>
                        </div>

                        <div class="field">
                            <textarea name="body" placeholder="What do you want to share with the reviewers?" required>{{ old('body') }}</textarea>
                        </div>

                        <div class="field" id="quoteAuthorField" style="display:none;">
                            <label>Attributed to <span class="opt">(optional — leave blank if it's your own words)</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Warren Buffett">
                        </div>

                        <div class="field-row">
                            <div class="field">
                                <label for="subjectSelect">Related subject <span class="opt">(optional)</span></label>
                                <select name="subject_id" id="subjectSelect">
                                    <option value="">— None —</option>
                                    @foreach($subjects as $s)
                                        <option value="{{ $s->id }}" {{ (string) old('subject_id') === (string) $s->id ? 'selected' : '' }}>{{ $s->code }} — {{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label>Attach a file <span class="opt">(max 20MB)</span></label>
                                <label class="file-drop" for="fileInput" id="fileDrop">
                                    <i class="fas fa-paperclip"></i><span id="fileLabel">Click to attach a material</span>
                                </label>
                                <input type="file" name="file" id="fileInput" hidden
                                       accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.csv,.txt,.rtf,.odt,.jpg,.jpeg,.png,.gif,.webp,.zip,.rar">
                            </div>
                        </div>

                        @if($myResources->isNotEmpty())
                            <div class="field">
                                <label for="resourceSelect">…or link an existing material <span class="opt">(from your Resource Library uploads)</span></label>
                                <select name="resource_id" id="resourceSelect">
                                    <option value="">— None —</option>
                                    @foreach($myResources as $r)
                                        <option value="{{ $r->id }}" {{ (string) old('resource_id') === (string) $r->id ? 'selected' : '' }}>{{ $r->title }} ({{ $r->original_name }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="composer-foot">
                            <button type="submit" class="btn-primary"><i class="fas fa-paper-plane"></i> Share</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @forelse($posts as $post)
            @php
                $author = $post->author;
                $isQuote = $post->post_type === 'tip';
                $canDelete = $me->isChair() || $post->author_id === $me->id;
                $liked = $post->isLikedBy($me);
                $likeCount = $post->likes->count();
                $commentCount = $post->replies->count();
            @endphp
            <article class="card post">
                <div class="post-head">
                    <div class="av">@if($author)@include('partials.user-avatar', ['user' => $author])@else ?@endif</div>
                    <div class="post-who">
                        <div class="post-name">
                            {{ $author->name ?? 'Former Member' }}
                            <span class="role-badge">{{ $roleLabel($author) }}</span>
                            @if($post->is_pinned)<span class="pin-badge"><i class="fas fa-thumbtack"></i> Pinned</span>@endif
                        </div>
                        <div class="post-meta">
                            @if($author?->alumniProfile?->batch_year)<span>Batch {{ $author->alumniProfile->batch_year }}</span><span class="sep">•</span>@endif
                            @if($author?->alumniProfile?->current_job)<span>{{ $author->alumniProfile->current_job }}{{ $author->alumniProfile->company ? ' at '.$author->alumniProfile->company : '' }}</span><span class="sep">•</span>@endif
                            <span title="{{ $post->created_at?->format('M j, Y g:i A') }}">{{ $post->created_at?->diffForHumans() }}</span>
                            @if($post->subject)<span class="subject-chip">{{ $post->subject->code }}</span>@endif
                        </div>
                    </div>
                    <div class="post-menu">
                        <button type="button" class="icon-btn" onclick="toggleMenu({{ $post->id }})" aria-label="Post options"><i class="fas fa-ellipsis"></i></button>
                        <div class="menu-dropdown" id="menu-{{ $post->id }}">
                            @if($author && $author->id !== $me->id)
                                <button type="button" onclick="openFbChat({{ $author->id }}, '{{ addslashes($author->name ?? 'User') }}')"><i class="fas fa-comment-dots"></i> Message {{ $author->first_name }}</button>
                            @endif
                            @if($canDelete)
                                <form method="POST" action="{{ route('community.posts.destroy', $post->id) }}"
                                      data-confirm="This post and all of its comments will be permanently removed from the community feed."
                                      data-confirm-title="Delete this post?"
                                      data-confirm-ok="Yes, delete it"
                                      data-confirm-danger>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="danger"><i class="fas fa-trash"></i> Delete post</button>
                                </form>
                            @endif
                            @if(!($author && $author->id !== $me->id) && !$canDelete)
                                <button type="button" disabled style="color:var(--faint);cursor:default"><i class="fas fa-circle-info"></i> No actions available</button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="post-body {{ $isQuote ? 'quote' : '' }}">@if($isQuote)<i class="fas fa-quote-left"></i>@endif{{ $post->body }}@if($isQuote && $post->title)<span class="quote-attr">— {{ $post->title }}</span>@endif</div>

                @foreach($post->attachments as $att)
                    @php $meta = $att->iconMeta(); @endphp
                    <a class="attachment" href="{{ route('community.attachments.download', $att->id) }}">
                        <div class="att-icon" style="background:{{ $meta['color'] }};"><i class="fas {{ $meta['icon'] }}"></i></div>
                        <div class="att-info">
                            <div class="att-title">{{ $att->original_name }}</div>
                            <div class="att-meta">{{ strtoupper($att->file_category) }} · {{ $att->humanSize() }}</div>
                        </div>
                        <span class="att-go" aria-hidden="true"><i class="fas fa-download"></i></span>
                    </a>
                @endforeach

                @if($post->resource)
                    @php $rmeta = $post->resource->iconMeta(); @endphp
                    {{-- Library files are view-only (no download route), so this
                         opens the same viewer as the Resource Library's View button. --}}
                    <a class="attachment" href="{{ $post->resource->viewerUrl() }}" target="_blank" rel="noopener">
                        <div class="att-icon" style="background:{{ $rmeta['color'] }};"><i class="fas {{ $rmeta['icon'] }}"></i></div>
                        <div class="att-info">
                            <div class="att-title">{{ $post->resource->title }} <span class="role-badge" style="margin-left:4px;"><i class="fas fa-book" style="margin-right:3px;"></i>Library</span></div>
                            <div class="att-meta">{{ strtoupper($post->resource->file_category) }} · {{ $post->resource->humanSize() }} · {{ $post->resource->downloads_count }} view{{ $post->resource->downloads_count === 1 ? '' : 's' }}</div>
                        </div>
                        <span class="att-go" aria-hidden="true"><i class="fas fa-eye"></i></span>
                    </a>
                @endif

                @if($likeCount || $commentCount)
                    <div class="post-stats">
                        <div class="stat-likes">
                            @if($likeCount)<span class="dot"><i class="fas fa-thumbs-up"></i></span> {{ $likeCount }}@endif
                        </div>
                        <div>@if($commentCount){{ $commentCount }} comment{{ $commentCount === 1 ? '' : 's' }}@endif</div>
                    </div>
                @endif

                <div class="post-actions">
                    <form method="POST" action="{{ route('community.posts.like', $post->id) }}" style="flex:1;display:flex;">
                        @csrf
                        <button type="submit" class="act-btn {{ $liked ? 'liked' : '' }}" aria-pressed="{{ $liked ? 'true' : 'false' }}">
                            <i class="fa{{ $liked ? 's' : 'r' }} fa-thumbs-up"></i> {{ $liked ? 'Liked' : 'Like' }}
                        </button>
                    </form>
                    <button type="button" class="act-btn" onclick="focusComment({{ $post->id }})">
                        <i class="far fa-comment"></i> Comment
                    </button>
                    @if($author && $author->id !== $me->id)
                        <button type="button" class="act-btn" onclick="openFbChat({{ $author->id }}, '{{ addslashes($author->name ?? 'User') }}')">
                            <i class="far fa-paper-plane"></i> Message
                        </button>
                    @endif
                </div>

                <div class="comments" id="comments-{{ $post->id }}">
                    @foreach($post->replies as $comment)
                        <div class="comment">
                            <div class="av">@if($comment->author)@include('partials.user-avatar', ['user' => $comment->author])@else ?@endif</div>
                            <div class="comment-bubble">
                                <span class="comment-name">{{ $comment->author->name ?? 'User' }}</span>
                                <span class="comment-text">{{ $comment->body }}</span>
                            </div>
                            @if($comment->author_id === $me->id)
                                <form method="POST" action="{{ route('community.comments.destroy', $comment->id) }}"
                                      data-confirm="Your comment will be permanently removed."
                                      data-confirm-title="Delete this comment?"
                                      data-confirm-ok="Yes, delete it"
                                      data-confirm-danger>
                                    @csrf @method('DELETE')
                                    <button class="comment-del" title="Delete comment" aria-label="Delete comment"><i class="fas fa-times"></i></button>
                                </form>
                            @endif
                        </div>
                    @endforeach

                    <form method="POST" action="{{ route('community.comments.store', $post->id) }}" class="comment-form">
                        @csrf
                        <div class="av">@include('partials.avatar-content')</div>
                        <div class="comment-input">
                            <input type="text" name="body" id="comment-input-{{ $post->id }}" placeholder="Write a comment..." maxlength="1000" required aria-label="Write a comment">
                            <button type="submit" aria-label="Send comment"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </form>
                </div>
            </article>
        @empty
            <div class="card">
                <div class="empty">
                    <div class="empty-art"><i class="fas fa-seedling"></i></div>
                    <div class="empty-title">Nothing here yet</div>
                    <div class="empty-sub">
                        {{ $me->hasAlumniAccess()
                            ? 'Be the first to share something — a tip, a win, or a file that helped you pass.'
                            : 'Check back soon. Alumni post updates, study materials and words of encouragement here.' }}
                    </div>
                </div>
            </div>
        @endforelse

        @if($posts->hasPages())
            <div class="pagination">{{ $posts->links() }}</div>
        @endif
    </div>

    {{-- ── Right: people ── --}}
    <aside class="rail contacts-rail" id="contactsRail" aria-label="People">
        {{-- Who's actually carrying the community, by post count. --}}
        <div class="card">
            <div class="card-head"><h3><i class="fas fa-award"></i> Top Contributors</h3></div>
            @if($topContributors->isEmpty())
                <div class="rail-empty">No posts yet — the board is wide open.</div>
            @else
                <div class="contrib-list">
                    @foreach($topContributors as $i => $t)
                        <div class="contrib-item">
                            <div class="contrib-rank">{{ $i + 1 }}</div>
                            <div class="av">@include('partials.user-avatar', ['user' => $t])</div>
                            <div style="min-width:0;">
                                <div class="person-name">{{ $t->name }}</div>
                                <div class="person-sub">{{ $t->posts_count }} {{ Str::plural('post', $t->posts_count) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card">
            @php
                // Grouped by role so a long flat list stays scannable.
                $groups = [
                    'Alumni'   => $contacts->where('role_id', \App\Models\Role::ALUMNI),
                    'Faculty'  => $contacts->where('role_id', \App\Models\Role::FACULTY),
                    'Students' => $contacts->where('role_id', \App\Models\Role::STUDENT),
                ];
            @endphp
            <div class="card-head"><h3><i class="fas fa-address-book"></i> Contacts</h3><span class="count-pill">{{ $contacts->count() }}</span></div>
            @if($contacts->isNotEmpty())
                <div class="contact-search">
                    <i class="fas fa-search"></i>
                    <input type="search" id="contactFilter" placeholder="Find someone" aria-label="Filter contacts">
                </div>
            @endif
            <div class="contact-list" id="contactList">
                @if($contacts->isEmpty())
                    <div class="rail-empty">No one else here yet.</div>
                @endif
                @foreach($groups as $label => $people)
                    @continue($people->isEmpty())
                    <div class="contact-group" data-group><span>{{ $label }}</span><b>{{ $people->count() }}</b></div>
                    @foreach($people as $c)
                        <button type="button" class="contact-item" data-name="{{ strtolower($c->name) }}" onclick="openFbChat({{ $c->id }}, '{{ addslashes($c->name) }}')">
                            <div class="av">@include('partials.user-avatar', ['user' => $c])</div>
                            <div style="min-width:0;">
                                <div class="person-name">{{ $c->name }}</div>
                                <div class="person-sub" style="text-transform:capitalize">{{ $c->roleName() }}</div>
                            </div>
                            <i class="fas fa-comment-dots msg-hint" aria-hidden="true"></i>
                        </button>
                    @endforeach
                @endforeach
            </div>
        </div>
    </aside>

    </div>
</div>
</main>

@include('partials.chat-widget', ['contacts' => $contacts])
@include('partials.global-search')

<script>
// Profile dropdown in the page header.
(function () {
    const btn  = document.getElementById('profileBtn');
    const menu = document.getElementById('profileDropdown');
    if (!btn || !menu) return;
    btn.addEventListener('click', e => { e.stopPropagation(); menu.classList.toggle('show'); });
    menu.addEventListener('click', e => e.stopPropagation());
    document.addEventListener('click', () => menu.classList.remove('show'));
})();

// ── Motivational header slider ──────────────────────────────────────────
(function () {
    const hero = document.getElementById('hero');
    if (!hero) return;
    const track = document.getElementById('heroTrack');
    const dots  = Array.from(document.querySelectorAll('#heroDots .hero-dot'));
    const count = Number(hero.dataset.count || 0);
    if (count < 2) return;

    let i = 0, timer = null;
    function go(next) {
        i = (next + count) % count;
        track.style.transform = `translateX(-${i * 100}%)`;
        dots.forEach((d, di) => d.classList.toggle('on', di === i));
    }
    function start() { stop(); timer = setInterval(() => go(i + 1), 6000); }
    function stop()  { if (timer) { clearInterval(timer); timer = null; } }

    document.getElementById('heroPrev').addEventListener('click', () => { go(i - 1); start(); });
    document.getElementById('heroNext').addEventListener('click', () => { go(i + 1); start(); });
    dots.forEach(d => d.addEventListener('click', () => { go(Number(d.dataset.go)); start(); }));
    // Pause while the reader is on it, and while the tab is hidden.
    hero.addEventListener('mouseenter', stop);
    hero.addEventListener('mouseleave', start);
    document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
    start();
})();

// ── Composer ────────────────────────────────────────────────────────────
(function () {
    const composer   = document.getElementById('composer');
    if (!composer) return;
    const full       = document.getElementById('composerFull');
    const postRadio  = document.getElementById('typePost');
    const quoteRadio = document.getElementById('typeQuote');
    const quoteField = document.getElementById('quoteAuthorField');
    const fileInput  = document.getElementById('fileInput');
    const fileLabel  = document.getElementById('fileLabel');
    const resourceSelect = document.getElementById('resourceSelect');

    function sync() { quoteField.style.display = quoteRadio.checked ? '' : 'none'; }
    postRadio.addEventListener('change', sync);
    quoteRadio.addEventListener('change', sync);
    sync();

    function open(kind) {
        composer.classList.add('is-open');
        full.classList.add('open');
        if (kind === 'tip') { quoteRadio.checked = true; sync(); }
        if (kind === 'discussion') { postRadio.checked = true; sync(); }
        if (kind === 'file') { fileInput.click(); }
        const ta = full.querySelector('textarea');
        if (ta && kind !== 'file') ta.focus();
    }
    function close() {
        composer.classList.remove('is-open');
        full.classList.remove('open');
    }

    const fake = composer.querySelector('.composer-fake-input');
    fake.addEventListener('click', () => open());
    fake.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); } });
    composer.querySelectorAll('[data-compose]').forEach(b => b.addEventListener('click', () => open(b.dataset.compose)));
    document.getElementById('composerClose').addEventListener('click', close);

    fileInput.addEventListener('change', function () {
        fileLabel.innerHTML = '';
        if (this.files.length) {
            const s = document.createElement('span');
            s.className = 'fname';
            s.textContent = this.files[0].name;
            fileLabel.appendChild(s);
        } else {
            fileLabel.textContent = 'Click to attach a material';
        }
        // A post carries either a fresh attachment or a linked library
        // material, never both — picking one clears the other.
        if (this.files.length && resourceSelect) resourceSelect.value = '';
    });
    if (resourceSelect) {
        resourceSelect.addEventListener('change', function () {
            if (this.value) { fileInput.value = ''; fileLabel.textContent = 'Click to attach a material'; }
        });
    }

    // Reopen after a failed submit so the old input isn't hidden.
    @if($errors->any() || old('body'))
        open();
    @endif
})();

// ── Contacts filter ────────────────────────────────────────────────────
(function () {
    const input = document.getElementById('contactFilter');
    const list  = document.getElementById('contactList');
    if (!input || !list) return;
    input.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        let group = null, groupHits = 0;
        const flush = () => { if (group) group.style.display = groupHits ? '' : 'none'; };
        Array.from(list.children).forEach(el => {
            if (el.hasAttribute('data-group')) { flush(); group = el; groupHits = 0; return; }
            if (!el.dataset.name) return;
            const hit = !q || el.dataset.name.includes(q);
            el.style.display = hit ? '' : 'none';
            if (hit) groupHits++;
        });
        flush();
    });
})();

function focusComment(id) {
    const box = document.getElementById('comments-' + id);
    box.classList.remove('open-hidden');
    const input = document.getElementById('comment-input-' + id);
    if (input) input.focus();
}

function toggleMenu(id) {
    const el = document.getElementById('menu-' + id);
    document.querySelectorAll('.menu-dropdown.open').forEach(function (m) { if (m !== el) m.classList.remove('open'); });
    el.classList.toggle('open');
}
document.addEventListener('click', function (e) {
    if (!e.target.closest('.post-menu')) {
        document.querySelectorAll('.menu-dropdown.open').forEach(function (m) { m.classList.remove('open'); });
    }
});
</script>

    @include('partials.alerts')
</body>
</html>
