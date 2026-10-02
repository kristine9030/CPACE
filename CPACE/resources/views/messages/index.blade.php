<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Messages - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6;
            /* CPACE's own accent for "my message" bubbles, active items and
               chat action icons — kept as its own variable (rather than
               hard-coding var(--primary) at each use) so the chat's accent
               can still be told apart from other maroon UI at a glance. */
            --fb-blue:#7B1D1D; --fb-blue-light:#f5e8e8;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

        /* Chair and alumni already get their sidebar offset for free (it's
           built into their own shared sidebar partial), and the student
           sidebar handles .main-content the same way — but the faculty
           sidebar partial expects every faculty page to set this itself,
           and this page never did. Scoped to .is-faculty (set on <body>
           below) so it can't collide with the student/chair/alumni rules
           that already apply to this same element via their own classes. */
        body.is-faculty .messages-page { margin-left:230px; transition:margin-left .3s; }
        body.is-faculty .sidebar.collapsed ~ .messages-page { margin-left:70px; }
        .messages-page { display:flex; flex-direction:column; height:100vh; }

        /* ── Global page topbar (title + search + notif + profile) ── */
        .messages-topbar {
            flex-shrink:0; display:flex; justify-content:space-between; align-items:center;
            gap:20px; padding:20px 28px 16px; flex-wrap:wrap;
        }
        .messages-topbar .page-title {
            font-family:'Montserrat',sans-serif; font-size:28px; font-weight:700; color:#14283E;
            margin-bottom:6px; padding-bottom:8px; position:relative;
        }
        .messages-topbar .page-title::after {
            content:''; position:absolute; left:0; bottom:0;
            width:40px; height:4px; border-radius:2px;
            background:linear-gradient(90deg, #c0392b, #7B1D1D);
        }
        .messages-topbar .page-subtitle { font-size:14px; color:#999; }
        .messages-topbar-right { display:flex; align-items:center; gap:14px; }

        .search-wrap { position:relative; }
        .search-wrap i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#aaa; font-size:15px; }
        /* Scoped under the topbar: the chair sidebar's shared input[type=text] rule loads later and would otherwise reset the left padding over the icon. */
        .messages-topbar .search-wrap input {
            width:280px; padding:11px 16px 11px 40px;
            border:1px solid #e0e0e0; border-radius:24px;
            font-size:14px; font-family:'Poppins',sans-serif;
            background:#fff; color:#555; outline:none;
        }
        .messages-topbar .search-wrap input:focus { border-color:var(--primary); }
        .messages-topbar .search-wrap input::placeholder { color:#bbb; }

        .notif-btn {
            position:relative; width:44px; height:44px;
            border:none; background:#fff; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:18px; color:#555; cursor:pointer; text-decoration:none;
            box-shadow:0 1px 4px rgba(0,0,0,0.08); flex-shrink:0;
        }
        .notif-btn:hover { background:#f0f0f0; }
        .badge {
            position:absolute; top:-3px; right:-3px;
            width:19px; height:19px; background:var(--accent);
            color:#fff; border-radius:50%; font-size:10.5px; font-weight:700;
            display:flex; align-items:center; justify-content:center;
        }
        .profile-avatar {
            width:44px; height:44px; background:var(--primary);
            border-radius:11px; border:none; color:#fff;
            font-weight:700; font-size:15px; cursor:pointer;
            font-family:'Poppins',sans-serif; transition:background 0.2s;
            overflow:hidden;
        }
        .profile-avatar:hover { background:var(--primary-hover); }
        .profile-avatar img { width:100%; height:100%; object-fit:cover; }

        .header-dropdown-wrap { position:relative; }
        .dropdown-menu {
            position:absolute; top:calc(100% + 8px); right:0;
            background:#fff; border:1px solid #e5e7eb; border-radius:10px;
            min-width:190px; box-shadow:0 6px 20px rgba(0,0,0,0.12);
            display:none; z-index:2000;
        }
        .dropdown-menu.active { display:block; }
        .dropdown-menu a, .dropdown-menu button {
            display:flex; align-items:center; gap:10px;
            padding:12px 16px; font-size:13.5px; font-family:'Poppins',sans-serif;
            text-decoration:none; color:#333; background:none; border:none;
            width:100%; text-align:left; cursor:pointer; transition:background 0.2s;
            border-bottom:1px solid #f5f5f5;
        }
        .dropdown-menu a:last-child, .dropdown-menu form:last-child button { border-bottom:none; }
        .dropdown-menu a:hover, .dropdown-menu button:hover { background:#f9f9f9; }
        .dropdown-menu a i, .dropdown-menu button i { color:var(--primary); width:16px; text-align:center; }
        .dropdown-menu .logout-btn { color:#e53e3e; }
        .dropdown-menu .logout-btn i { color:#e53e3e; }

        .chat-shell { display:flex; flex:1; min-height:0; overflow:hidden; }

        /* ── Conversation list pane ── */
        .chat-list-pane { width:360px; flex-shrink:0; background:#fff; border-right:1px solid #ececec; display:flex; flex-direction:column; }
        .cl-head { padding:18px 18px 12px; border-bottom:1px solid #f2f2f2; }
        .cl-head-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
        .cl-title { font-size:22px; font-weight:800; color:#050505; }
        .cl-actions { display:flex; gap:6px; }
        .cl-icon-btn { width:38px; height:38px; border-radius:50%; border:none; background:#f0f2f5; color:var(--fb-blue); font-size:15px; cursor:pointer; transition:background .15s; }
        .cl-icon-btn:hover { background:#e4e6eb; }
        .cl-search { position:relative; }
        .chat-list-pane .cl-search input { width:100%; border:none; background:#f0f2f5; border-radius:20px; padding:11px 16px 11px 38px; font-size:14px; font-family:'Poppins',sans-serif; outline:none; }
        .chat-list-pane .cl-search input:focus { background:#e8eaed; }
        .cl-search i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#65676b; font-size:13px; }

        .cl-items { flex:1; overflow-y:auto; padding:8px; }
        .cl-item { display:flex; align-items:center; gap:12px; padding:11px 12px; text-decoration:none; color:inherit; border-radius:10px; transition:background .15s; position:relative; }
        .cl-item:hover { background:#f2f2f2; }
        .cl-item.active { background:var(--fb-blue-light); }
        .cl-avatar { width:54px; height:54px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:17px; flex-shrink:0; position:relative; overflow:hidden; }
        .cl-avatar.group { background:#5a7fb0; }
        .cl-avatar img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
        .cl-avatar::after { content:''; position:absolute; bottom:-1px; right:-1px; width:12px; height:12px; border-radius:50%; background:#ccc; border:2.5px solid #fff; }
        .cl-avatar.group::after, .cl-avatar.offline::after { display:none; }
        .cl-avatar.online::after { background:#31a24c; }
        .cl-info { flex:1; min-width:0; }
        .cl-name { font-size:15px; font-weight:600; color:#050505; display:flex; align-items:center; gap:6px; }
        .cl-preview { font-size:13.5px; color:#65676b; margin-top:3px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .cl-item.has-unread .cl-preview, .cl-item.has-unread .cl-name { color:#050505; font-weight:700; }
        .cl-meta { display:flex; flex-direction:column; align-items:flex-end; gap:5px; flex-shrink:0; }
        .cl-time { font-size:11px; color:#999; }
        .cl-unread-dot { width:11px; height:11px; border-radius:50%; background:var(--fb-blue); }
        .cl-empty { padding:40px 20px; text-align:center; color:#bbb; font-size:13px; }

        /* ── Thread pane ── */
        .chat-thread-pane { flex:1; display:flex; flex-direction:column; min-width:0; background:#fff; }
        .thread-empty { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#ccc; }
        .thread-empty i { font-size:48px; margin-bottom:14px; color:#e5d8d8; }

        .thread-head { padding:14px 22px; background:#fff; border-bottom:1px solid #ececec; display:flex; align-items:center; gap:12px; }
        .thread-title { font-size:16.5px; font-weight:700; color:#050505; }
        .thread-sub { font-size:13px; color:#31a24c; margin-top:1px; }

        .thread-body { flex:1; overflow-y:auto; padding:18px 24px; display:flex; flex-direction:column; gap:5px; background:#fff; }
        .msg-row { display:flex; flex-direction:column; max-width:62%; margin-bottom:7px; }
        .msg-row.mine { align-self:flex-end; align-items:flex-end; }
        .msg-row.theirs { align-self:flex-start; align-items:flex-start; }
        .msg-sender { font-size:11.5px; color:#aaa; margin-bottom:2px; padding:0 4px; }
        .msg-bubble { padding:11px 16px; border-radius:18px; font-size:15px; line-height:1.45; word-wrap:break-word; }
        .msg-row.mine .msg-bubble { background:var(--fb-blue); color:#fff; }
        .msg-row.theirs .msg-bubble { background:#e4e6eb; color:#050505; }
        .msg-time { font-size:10.5px; color:#ccc; margin-top:3px; padding:0 4px; }

        .msg-attach-img { max-width:260px; max-height:260px; border-radius:14px; display:block; margin-bottom:4px; }
        .msg-attach-file { display:flex; align-items:center; gap:10px; padding:10px 13px; border-radius:14px; background:#f0f2f5; text-decoration:none; color:inherit; max-width:240px; margin-bottom:4px; }
        .msg-attach-file .msg-af-icon { width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:14px; color:#fff; flex-shrink:0; }
        .msg-attach-file .msg-af-info { min-width:0; }
        .msg-attach-file .msg-af-name { font-size:12.5px; font-weight:600; color:#050505; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .msg-attach-file .msg-af-size { font-size:10.5px; color:#999; }
        .msg-uploading { font-size:11px; color:#aaa; padding:0 4px; }

        .thread-form { display:flex; align-items:center; gap:9px; padding:14px 20px; background:#fff; border-top:1px solid #ececec; }
        .thread-form-icon { width:40px; height:40px; border-radius:50%; border:none; background:transparent; color:var(--fb-blue); font-size:16px; cursor:pointer; flex-shrink:0; }
        .thread-form-icon:hover { background:#f0f2f5; }
        .thread-form input { flex:1; border:none; background:#f0f2f5; border-radius:22px; padding:12px 18px; font-size:14.5px; font-family:'Poppins',sans-serif; outline:none; }
        .thread-form input:focus { background:#e8eaed; }
        .thread-form button.send-btn { width:42px; height:42px; border-radius:50%; border:none; background:transparent; color:var(--fb-blue); cursor:pointer; flex-shrink:0; font-size:18px; }
        .thread-form button.send-btn:hover { background:#f0f2f5; }

        /* ── Profile / conversation-info pane ── */
        .chat-profile-pane { width:300px; flex-shrink:0; background:#fff; border-left:1px solid #ececec; overflow-y:auto; padding:0 18px 24px; }
        .pp-close-wrap { display:none; padding:10px 0 0; }
        .pp-identity { text-align:center; padding:24px 0 18px; border-bottom:1px solid #f2f2f2; }
        .pp-avatar { width:84px; height:84px; margin:0 auto 12px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:28px; position:relative; overflow:hidden; }
        .pp-avatar img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
        .pp-avatar.group { background:#5a7fb0; }
        .pp-name { font-size:16px; font-weight:700; color:#050505; }
        .pp-sub { font-size:12.5px; color:#65676b; margin-top:4px; display:flex; align-items:center; justify-content:center; gap:5px; }
        .pp-action-btn { margin-top:12px; display:inline-flex; align-items:center; gap:7px; background:#f0f2f5; border:none; border-radius:8px; padding:8px 14px; font-size:12.5px; font-weight:600; color:#050505; cursor:pointer; font-family:'Poppins',sans-serif; }
        .pp-action-btn:hover { background:#e4e6eb; }

        .pp-section { padding:16px 0; border-bottom:1px solid #f2f2f2; }
        .pp-section:last-child { border-bottom:none; }
        .pp-section-title { font-size:12px; font-weight:700; color:#050505; margin-bottom:10px; }
        .pp-empty { font-size:12px; color:#bbb; }

        .pp-members { display:flex; flex-wrap:wrap; gap:8px; }
        .pp-member-av { width:36px; height:36px; border-radius:50%; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; overflow:hidden; position:relative; }
        .pp-member-av img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
        .pp-member-more { background:#f0f2f5; color:#65676b; }

        .pp-media-grid { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:6px; }
        .pp-media-thumb { display:block; aspect-ratio:1; border-radius:8px; overflow:hidden; }
        .pp-media-thumb img { width:100%; height:100%; object-fit:cover; }

        .pp-file-list, .pp-link-list { display:flex; flex-direction:column; gap:4px; }
        .pp-file-row, .pp-link-row { display:flex; align-items:center; gap:10px; padding:7px 8px; border-radius:9px; text-decoration:none; color:inherit; transition:background .15s; }
        .pp-file-row:hover, .pp-link-row:hover { background:#f2f2f2; }
        .pp-file-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:13px; color:#fff; flex-shrink:0; }
        .pp-file-info, .pp-link-info { min-width:0; }
        .pp-file-name { font-size:12px; font-weight:600; color:#050505; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .pp-file-meta { font-size:10.5px; color:#999; }
        .pp-link-icon { width:32px; height:32px; border-radius:50%; background:var(--fb-blue-light); color:var(--fb-blue); display:flex; align-items:center; justify-content:center; font-size:12px; flex-shrink:0; }
        .pp-link-host { font-size:12px; font-weight:600; color:#050505; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .pp-link-url { font-size:10.5px; color:#999; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

        body.info-hidden .chat-profile-pane { display:none; }

        @media (max-width:1300px) {
            .chat-profile-pane { display:none; position:fixed; right:0; top:0; bottom:0; z-index:1500; box-shadow:-6px 0 20px rgba(0,0,0,.15); }
            .chat-profile-pane.open { display:block; }
            body.info-hidden .chat-profile-pane.open { display:block; }
            .pp-close-wrap { display:flex; justify-content:flex-end; }
        }

        /* ── Chair: Chats | Announcements switch ── */
        .msg-switch { display:inline-flex; gap:4px; margin-top:12px; padding:4px; background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(16,24,40,.08); }
        .msg-switch a { display:inline-flex; align-items:center; gap:8px; padding:8px 16px; border-radius:9px; color:#6b7280; font-size:13px; font-weight:600; text-decoration:none; transition:background .15s, color .15s; }
        .msg-switch a:hover { background:#f6f6f8; color:#333; }
        .msg-switch a.on { background:var(--primary); color:#fff; box-shadow:0 3px 10px rgba(123,29,29,.25); }

        /* ── Announcements board ── */
        .an-board { flex:1; overflow-y:auto; padding:6px 28px 32px; background:#f4f5f7; }
        .an-head { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:16px; }
        .an-head h2 { font-size:20px; font-weight:700; color:#1a1a1a; }
        .an-head p { font-size:13px; color:#8a8f98; margin-top:2px; }
        .an-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:11px 20px; border:0; border-radius:11px; font:600 13px 'Poppins',sans-serif; cursor:pointer; transition:background .15s, box-shadow .15s; }
        .an-btn.primary { background:var(--primary); color:#fff; box-shadow:0 4px 12px rgba(123,29,29,.22); }
        .an-btn.primary:hover { background:var(--primary-hover); }
        .an-btn.ghost { background:#f1f2f4; color:#4b5563; }
        .an-btn.ghost:hover { background:#e7e9ec; }
        .an-toolbar { display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; margin:14px 0 16px; }
        .an-filters { display:flex; gap:8px; flex-wrap:wrap; }
        .an-pill { display:inline-flex; align-items:center; gap:8px; padding:9px 16px; border-radius:22px; background:#fff; color:#555; font-size:13px; font-weight:600; text-decoration:none; border:1px solid #e8eaee; transition:all .15s; }
        .an-pill:hover { border-color:#d3d6dc; }
        .an-pill i { color:#9ca3af; font-size:12px; }
        .an-pill span { min-width:22px; height:20px; padding:0 7px; border-radius:10px; background:#f1f2f4; color:#6b7280; font-size:11px; font-weight:700; display:inline-flex; align-items:center; justify-content:center; }
        .an-pill.on { background:var(--primary); border-color:var(--primary); color:#fff; }
        .an-pill.on i { color:rgba(255,255,255,.85); }
        .an-pill.on span { background:rgba(255,255,255,.22); color:#fff; }

        /* the list: one announcement per row, click to open */
        .an-rows { display:flex; flex-direction:column; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 1px 2px rgba(16,24,40,.04), 0 4px 14px rgba(16,24,40,.06); }
        .an-row { display:grid; grid-template-columns:42px minmax(0, 1fr) 190px 150px 92px 18px; gap:16px; align-items:center; padding:15px 20px; border-bottom:1px solid #f1f1f4; cursor:pointer; transition:background .12s; }
        .an-row:last-child { border-bottom:0; }
        .an-row:hover, .an-row:focus-visible { background:#fbf7f7; outline:none; }
        .an-ic { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:16px; }
        .an-ic.students { background:#dbeafe; color:#1d4ed8; }
        .an-ic.faculty { background:#ede9fe; color:#6d28d9; }
        .an-main { min-width:0; }
        .an-title { font-size:14px; font-weight:600; color:#1a1a1a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:flex; align-items:center; gap:8px; }
        .an-clip { flex-shrink:0; display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:10px; background:#f3f4f6; color:#6b7280; font-size:10.5px; font-weight:600; }
        .an-msg { font-size:12.5px; color:#8a8f98; margin-top:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .an-aud { display:inline-flex; align-items:center; gap:6px; padding:3px 10px; border-radius:13px; font-size:11px; font-weight:700; }
        .an-aud.students { background:#dbeafe; color:#1d4ed8; }
        .an-aud.faculty { background:#ede9fe; color:#6d28d9; }
        .an-to small, .an-seen small { display:block; margin-top:5px; font-size:11.5px; color:#8a8f98; }
        .an-seen small b { color:#1a1a1a; }
        .an-bar { height:6px; border-radius:6px; background:#eceef1; overflow:hidden; }
        .an-bar.lg { height:8px; margin:12px 0 4px; }
        .an-bar span { display:block; height:100%; border-radius:6px; background:#10b981; transition:width .3s; }
        .an-when { font-size:12.5px; color:#374151; text-align:right; }
        .an-when small { display:block; margin-top:2px; font-size:11px; color:#9ca3af; }
        .an-go { color:#c4c8cf; font-size:12px; }
        .an-row:hover .an-go { color:var(--primary); }

        .an-empty { display:flex; flex-direction:column; align-items:center; gap:8px; text-align:center; padding:64px 20px; background:#fff; border-radius:16px; color:#8a8f98; font-size:13px; box-shadow:0 1px 2px rgba(16,24,40,.04), 0 4px 14px rgba(16,24,40,.06); }
        .an-empty > i { width:64px; height:64px; border-radius:50%; background:#f3f4f6; color:#c4c8cf; display:flex; align-items:center; justify-content:center; font-size:24px; margin-bottom:6px; }
        .an-empty strong { font-size:16px; color:#1a1a1a; }
        .an-empty .an-btn { margin-top:12px; }
        .an-pager { display:flex; justify-content:center; align-items:center; gap:10px; margin-top:20px; font-size:12.5px; color:#6b7280; }
        .an-pager a, .an-pager .off { padding:8px 15px; border:1px solid #e5e7eb; border-radius:10px; background:#fff; color:#4b5563; text-decoration:none; }
        .an-pager a:hover { border-color:#d1d5db; }
        .an-pager .off { color:#c4c8cf; }

        /* ── Popups (open an announcement / make one) ── */
        .an-modal { background:#fff; border-radius:18px; width:100%; max-width:660px; max-height:92vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 24px 60px rgba(0,0,0,.25); }
        .an-modal.an-detail { max-width:720px; }
        .an-m-head { display:flex; align-items:center; gap:14px; padding:18px 22px; background:#faf7f7; border-bottom:1px solid #f0eaea; }
        .an-m-ic { width:44px; height:44px; border-radius:12px; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:17px; flex-shrink:0; }
        .an-m-head h3 { font-size:17px; font-weight:700; color:#1a1a1a; margin:0; word-break:break-word; }
        .an-m-head p { font-size:12px; color:#8a8f98; margin-top:2px; }
        .an-x { margin-left:auto; width:36px; height:36px; border:0; border-radius:10px; background:#f1f1f3; color:#666; cursor:pointer; flex-shrink:0; }
        .an-x:hover { background:#e6e6e9; }
        .an-m-body { padding:20px 22px; overflow-y:auto; }
        .an-m-foot { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding:14px 22px; background:#faf7f7; border-top:1px solid #f0eaea; }
        .an-m-actions { display:flex; gap:10px; }
        .an-sendto { font-size:12.5px; color:#6b7280; display:flex; align-items:center; gap:8px; }
        .an-sendto i { color:var(--primary); }
        .an-sendto strong { color:#1a1a1a; font-weight:600; }
        .an-sendto strong.zero { color:#b91c1c; }

        /* the opened announcement */
        .d-msg { font-size:14px; color:#374151; line-height:1.7; white-space:pre-line; word-break:break-word; padding-bottom:4px; }
        .d-link { margin-top:12px; font-size:12px; color:#6b7280; }
        .d-link i { color:var(--primary); margin-right:6px; }
        .d-files { margin-top:18px; }
        .d-label { font-size:12px; font-weight:700; color:#374151; margin-bottom:8px; }
        .d-file-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(230px, 1fr)); gap:10px; }
        .d-file { display:flex; align-items:center; gap:12px; padding:10px 12px; border:1px solid #eceef1; border-radius:12px; text-decoration:none; color:inherit; transition:border-color .15s, background .15s; }
        .d-file:hover { border-color:#e0c3c8; background:#fdf8f8; }
        .d-file .ic { width:40px; height:40px; border-radius:10px; color:#fff; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; }
        .d-file img { width:40px; height:40px; border-radius:10px; object-fit:cover; flex-shrink:0; }
        .d-file .nm { min-width:0; }
        .d-file .nm b { display:block; font-size:12.5px; font-weight:600; color:#1f2937; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .d-file .nm small { display:block; font-size:11px; color:#9ca3af; margin-top:1px; }
        .d-seen { margin-top:22px; padding-top:20px; border-top:1px solid #f1f1f4; }
        .d-seen-head { display:flex; align-items:flex-end; justify-content:space-between; gap:12px; }
        .d-seen-head h4 { font-size:14px; font-weight:700; color:#1a1a1a; }
        .d-seen-head p { font-size:12px; color:#8a8f98; margin-top:2px; }
        .d-big { display:flex; align-items:baseline; gap:6px; }
        .d-big b { font-size:26px; color:#059669; line-height:1; }
        .d-big span { font-size:13px; color:#9ca3af; }
        .d-tools { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin:14px 0 10px; }
        .d-tools .s { position:relative; width:220px; max-width:100%; }
        .d-tools .s i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#a3a8b0; font-size:12px; pointer-events:none; }
        .an-modal .d-tools .an-input { padding:8px 12px 8px 33px; font-size:12.5px; }
        .d-tab { border:1.5px solid #e5e7eb; background:#fff; border-radius:18px; padding:6px 13px; font:500 12px 'Poppins',sans-serif; color:#4b5563; cursor:pointer; display:inline-flex; align-items:center; gap:7px; transition:all .15s; }
        .d-tab span { font-size:10.5px; font-weight:700; color:#9ca3af; }
        .d-tab:hover { border-color:#d1d5db; }
        .d-tab.on { background:var(--primary); border-color:var(--primary); color:#fff; font-weight:600; }
        .d-tab.on span { color:rgba(255,255,255,.8); }
        .d-people { max-height:280px; overflow-y:auto; display:grid; gap:6px; }
        .d-person { display:flex; align-items:center; gap:12px; padding:9px 12px; border:1px solid #eef0f3; border-radius:10px; background:#fff; }
        .d-person .av { width:32px; height:32px; border-radius:50%; background:#c4c8cf; color:#fff; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .d-person .av.seen { background:var(--primary); }
        .d-person .who { min-width:0; flex:1; }
        .d-person .who b { display:block; font-size:12.5px; font-weight:600; color:#1f2937; }
        .d-person .who small { display:block; font-size:11px; color:#9ca3af; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .d-person .st { font-size:11.5px; font-weight:600; white-space:nowrap; }
        .d-person .st.seen { color:#059669; }
        .d-person .st.not { color:#9ca3af; }

        /* the form */
        .an-field { margin-bottom:18px; }
        .an-label { display:flex; align-items:center; justify-content:space-between; gap:10px; font-size:12.5px; font-weight:600; color:#374151; margin-bottom:8px; }
        .an-label.sm { font-size:12px; margin-bottom:6px; }
        .an-label small { font-weight:400; font-size:11px; color:#9ca3af; }
        .an-modal .an-input { width:100%; border:1px solid #e5e7eb; border-radius:11px; padding:10px 13px; font:13px 'Poppins',sans-serif; color:#1a1a1a; background:#fff; outline:none; margin:0; transition:border-color .15s, box-shadow .15s; }
        .an-modal .an-input::placeholder { color:#a3a8b0; }
        .an-modal .an-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(123,29,29,.1); }
        .an-modal textarea.an-input { resize:vertical; min-height:110px; line-height:1.6; }
        .an-modal select.an-input { appearance:none; -webkit-appearance:none; cursor:pointer; padding-right:34px;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat:no-repeat; background-position:right 13px center; }
        .an-two { display:grid; grid-template-columns:minmax(0, 1fr) minmax(0, 1fr); gap:14px; }

        .an-aud-pick { display:grid; grid-template-columns:minmax(0, 1fr) minmax(0, 1fr); gap:12px; }
        .an-aud-opt { position:relative; cursor:pointer; display:block; }
        .an-aud-opt input { position:absolute; opacity:0; inset:0; width:100%; height:100%; margin:0; cursor:pointer; }
        .an-aud-opt .b { display:flex; align-items:center; gap:12px; padding:13px 15px; border:1.5px solid #e5e7eb; border-radius:13px; transition:all .15s; }
        .an-aud-opt .b i { width:36px; height:36px; border-radius:10px; background:#f3f4f6; color:#6b7280; display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; transition:all .15s; }
        .an-aud-opt strong { display:block; font-size:13.5px; color:#1a1a1a; font-weight:600; }
        .an-aud-opt small { display:block; font-size:11.5px; color:#8a8f98; margin-top:1px; }
        .an-aud-opt:hover .b { border-color:#d1d5db; }
        .an-aud-opt input:focus-visible + .b { box-shadow:0 0 0 3px rgba(123,29,29,.18); }
        .an-aud-opt input:checked + .b { border-color:var(--primary); background:#fdf6f7; box-shadow:0 0 0 3px rgba(123,29,29,.08); }
        .an-aud-opt input:checked + .b i { background:var(--primary); color:#fff; }

        .an-chips { display:flex; gap:8px; flex-wrap:wrap; }
        .an-chips.tight { gap:6px; }
        .an-chip { position:relative; cursor:pointer; }
        .an-chip input { position:absolute; opacity:0; inset:0; width:100%; height:100%; margin:0; cursor:pointer; }
        .an-chip span { display:inline-flex; align-items:center; padding:8px 15px; border:1.5px solid #e5e7eb; border-radius:20px; font-size:12.5px; font-weight:500; color:#4b5563; background:#fff; transition:all .15s; }
        .an-chip:hover span { border-color:#d1d5db; background:#fafafa; }
        .an-chip input:focus-visible + span { box-shadow:0 0 0 3px rgba(123,29,29,.18); }
        .an-chip input:checked + span { border-color:var(--primary); background:var(--primary); color:#fff; font-weight:600; }

        .an-panel { display:none; margin-top:12px; padding:14px; background:#f9fafb; border:1px solid #eef0f3; border-radius:13px; }
        .an-panel.on { display:block; }
        .an-pick-tools { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
        .an-pick-tools .s { position:relative; flex:1; }
        .an-pick-tools .s i { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#a3a8b0; font-size:12px; pointer-events:none; }
        .an-modal .an-pick-tools .an-input { padding:9px 12px 9px 34px; font-size:12.5px; }
        .an-link { background:none; border:0; color:var(--primary); font:600 12px 'Poppins',sans-serif; cursor:pointer; padding:4px 2px; white-space:nowrap; }
        .an-link:hover { text-decoration:underline; }
        .an-list { max-height:220px; overflow:auto; display:grid; gap:6px; }
        .an-person { display:flex; align-items:center; gap:11px; padding:8px 11px; background:#fff; border:1px solid #eef0f3; border-radius:10px; cursor:pointer; }
        .an-person[hidden], .an-none[hidden] { display:none; }
        .an-person:hover { border-color:#d8dce2; }
        .an-person.is-on { border-color:#e7c6cb; background:#fdf6f7; }
        .an-person input { width:16px; height:16px; accent-color:var(--primary); flex-shrink:0; margin:0; }
        .an-person .av { width:30px; height:30px; border-radius:50%; background:var(--primary); color:#fff; font-size:10.5px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .an-person .who { min-width:0; }
        .an-person .who b { display:block; font-size:12.5px; font-weight:600; color:#1f2937; }
        .an-person .who small { display:block; font-size:11px; color:#9ca3af; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .an-none { padding:16px; text-align:center; color:#9ca3af; font-size:12px; }

        .an-drop { display:flex; align-items:center; gap:12px; flex-wrap:wrap; padding:12px 14px; border:1.5px dashed #d9dde3; border-radius:13px; background:#fafbfc; transition:border-color .15s, background .15s; }
        .an-drop.over { border-color:var(--primary); background:#fdf6f7; }
        .an-drop .hint { font-size:11.5px; color:#9ca3af; }
        .an-attach { display:inline-flex; align-items:center; gap:8px; padding:8px 14px; border:1px solid #e5e7eb; border-radius:10px; background:#fff; color:#374151; font:600 12.5px 'Poppins',sans-serif; cursor:pointer; transition:border-color .15s, color .15s; }
        .an-attach:hover { border-color:var(--primary); color:var(--primary); }
        .an-files { display:grid; gap:8px; margin-top:10px; }
        .an-files:empty { display:none; }
        .an-file { display:flex; align-items:center; gap:12px; padding:8px 10px; border:1px solid #eceef1; border-radius:11px; background:#fff; }
        .an-file img { width:38px; height:38px; border-radius:9px; object-fit:cover; flex-shrink:0; }
        .an-file .ic { width:38px; height:38px; border-radius:9px; background:#f3f4f6; color:#6b7280; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .an-file .nm { min-width:0; flex:1; }
        .an-file .nm b { display:block; font-size:12.5px; font-weight:600; color:#1f2937; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .an-file .nm small { font-size:11px; color:#9ca3af; }
        .an-file .rm { width:28px; height:28px; border:0; border-radius:8px; background:#f1f2f4; color:#6b7280; cursor:pointer; flex-shrink:0; }
        .an-file .rm:hover { background:#fde8e8; color:#b91c1c; }
        .an-ferr { margin-top:8px; font-size:12px; color:#b91c1c; line-height:1.5; }

        @media (max-width:980px) {
            .an-row { grid-template-columns:42px minmax(0, 1fr) 150px 90px 16px; }
            .an-to { display:none; }
        }
        @media (max-width:700px) {
            .an-board { padding:6px 16px 28px; }
            .an-row { grid-template-columns:42px minmax(0, 1fr) 16px; row-gap:10px; padding:14px 16px; }
            .an-seen { grid-column:2 / 3; grid-row:2; }
            .an-when { grid-column:2 / 3; grid-row:3; text-align:left; }
            .an-when small { display:inline; margin-left:6px; }
            .an-go { grid-column:3; grid-row:1; }
            .an-two, .an-aud-pick { grid-template-columns:minmax(0, 1fr); }
            .an-head .an-btn { width:100%; }
            .an-m-foot { flex-direction:column; align-items:stretch; }
            .an-m-actions .an-btn { flex:1; }
        }

        /* ── Chair: title and Chats | Announcements switch on one row ── */
        .msg-titlerow { display:flex; align-items:center; gap:22px; flex-wrap:wrap; }
        .msg-titlerow .page-title { margin-bottom:6px; }
        .msg-titlerow .msg-switch { margin-top:0; }

        /* ── Chat list header: clear actions, filters ── */
        .cl-head-top { margin-bottom:12px; }
        .cl-actions-row { display:flex; gap:8px; margin-bottom:12px; }
        .cl-btn { flex:1; display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:10px 12px; border:0; border-radius:11px; font:600 13px 'Poppins',sans-serif; cursor:pointer; transition:background .15s; }
        .cl-btn.primary { background:var(--primary); color:#fff; box-shadow:0 3px 10px rgba(123,29,29,.22); }
        .cl-btn.primary:hover { background:var(--primary-hover); }
        .cl-btn.ghost { background:#f0f2f5; color:#4b5563; }
        .cl-btn.ghost:hover { background:#e4e6eb; }
        .cl-pills { display:flex; gap:6px; flex-wrap:wrap; margin-top:12px; }
        .cl-pill { border:1.5px solid #e5e7eb; background:#fff; border-radius:18px; padding:6px 13px; font:500 12.5px 'Poppins',sans-serif; color:#4b5563; cursor:pointer; transition:all .15s; }
        .cl-pill:hover { border-color:#d1d5db; }
        .cl-pill.on { background:var(--primary); border-color:var(--primary); color:#fff; font-weight:600; }
        .cl-name { gap:8px; }
        .cl-name .nm { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .cl-chip { flex-shrink:0; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700; letter-spacing:.2px; }
        .cl-chip.group { background:#e8f0fb; color:#3b6fb0; }
        .cl-avatar { width:48px; height:48px; font-size:15px; }
        .cl-empty { display:flex; flex-direction:column; align-items:center; gap:6px; padding:44px 22px; color:#8a8f98; font-size:13px; line-height:1.5; }
        .cl-empty[hidden] { display:none; }
        .cl-empty i { font-size:30px; color:#d6d9de; margin-bottom:4px; }
        .cl-empty strong { color:#1a1a1a; font-size:14px; }

        /* ── Nothing open yet: say what to do ── */
        .thread-empty.welcome { gap:0; padding:24px; text-align:center; color:#6b7280; }
        .thread-empty.welcome > i { font-size:44px; color:#e5d8d8; margin-bottom:12px; }
        .thread-empty.welcome h3 { font-size:18px; font-weight:700; color:#1a1a1a; }
        .thread-empty.welcome p { font-size:13px; margin:4px 0 20px; }
        .we-cards { display:flex; gap:12px; flex-wrap:wrap; justify-content:center; }
        .we-card { width:170px; display:flex; flex-direction:column; align-items:center; gap:4px; padding:18px 14px; border:1.5px solid #ececef; border-radius:15px; background:#fff; cursor:pointer; font-family:'Poppins',sans-serif; transition:all .15s; }
        .we-card:hover { border-color:#e0c3c8; background:#fdf8f8; transform:translateY(-2px); box-shadow:0 8px 20px rgba(16,24,40,.08); }
        .we-card i { width:44px; height:44px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:18px; margin-bottom:6px; }
        .we-card b { font-size:13.5px; color:#1a1a1a; }
        .we-card small { font-size:11.5px; color:#8a8f98; }

        /* ── People picker (New message / New group) ── */
        .modal.wide { max-width:520px; padding:0; overflow:hidden; display:flex; flex-direction:column; max-height:86vh; }
        .modal.wide .mh { padding:20px 22px 12px; }
        .modal.wide .mh h3 { margin-bottom:2px; }
        .modal.wide .mh p { font-size:12px; color:#8a8f98; }
        .modal.wide .mb { padding:0 22px 6px; overflow-y:auto; }
        .modal.wide .mf { display:flex; justify-content:flex-end; gap:10px; padding:14px 22px; border-top:1px solid #f0f0f2; background:#fafafb; }
        .pk-tabs { display:flex; gap:6px; flex-wrap:wrap; margin:8px 0 12px; }
        .pk-tab { border:1.5px solid #e5e7eb; background:#fff; border-radius:18px; padding:6px 13px; font:500 12.5px 'Poppins',sans-serif; color:#4b5563; cursor:pointer; display:inline-flex; align-items:center; gap:7px; transition:all .15s; }
        .pk-tab i { font-size:11px; color:#9ca3af; }
        .pk-tab span { font-size:10.5px; font-weight:700; color:#9ca3af; }
        .pk-tab:hover { border-color:#d1d5db; }
        .pk-tab.on { background:var(--primary); border-color:var(--primary); color:#fff; font-weight:600; }
        .pk-tab.on i, .pk-tab.on span { color:rgba(255,255,255,.85); }
        .pk-search { position:relative; margin-bottom:10px; }
        .pk-search i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#a3a8b0; font-size:12px; pointer-events:none; }
        .pk .pk-search input[type=search] { width:100%; padding:10px 14px 10px 36px; border:1px solid #e5e7eb; border-radius:11px; font:13px 'Poppins',sans-serif; background:#fff; outline:none; }
        .pk .pk-search input[type=search]:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(123,29,29,.1); }
        .pk-bar { display:flex; align-items:center; gap:12px; font-size:12px; color:#6b7280; margin-bottom:8px; }
        .pk-bar b { color:var(--primary); }
        .pk-link { background:none; border:0; color:var(--primary); font:600 12px 'Poppins',sans-serif; cursor:pointer; padding:2px; }
        .pk-link:hover { text-decoration:underline; }
        .pk-list { max-height:340px; overflow-y:auto; margin:0 -8px; padding:0 8px 8px; }
        .pk-head { position:sticky; top:0; z-index:1; display:flex; align-items:center; gap:8px; padding:10px 4px 6px; background:#fff; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.4px; }
        .pk-head i { color:#9ca3af; }
        .pk-head span { font-weight:600; color:#b0b5bc; }
        .pk-item[hidden], .pk-head[hidden], .pk-none[hidden] { display:none; }
        .pk-item { display:block; margin:0; }
        .pk-row { display:flex; align-items:center; gap:12px; width:100%; padding:9px 10px; border:0; border-radius:12px; background:none; text-align:left; font-family:'Poppins',sans-serif; cursor:pointer; transition:background .12s; }
        .pk-row:hover { background:#f6f6f8; }
        .pk-item.pk-row:has(input:checked) { background:#fdf6f7; }
        .pk-row input[type=checkbox] { width:16px; height:16px; accent-color:var(--primary); flex-shrink:0; margin:0; }
        .pk-av { width:38px; height:38px; border-radius:50%; color:#fff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .pk-who { flex:1; min-width:0; }
        .pk-who b { display:block; font-size:13.5px; font-weight:600; color:#1a1a1a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .pk-who small { display:block; font-size:11.5px; color:#8a8f98; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .pk-chip { flex-shrink:0; padding:3px 10px; border-radius:11px; font-size:10.5px; font-weight:700; }
        .pk-none { padding:26px; text-align:center; color:#9ca3af; font-size:13px; }
        .group-name { width:100%; padding:11px 14px; border:1px solid #e5e7eb; border-radius:11px; font:13px 'Poppins',sans-serif; outline:none; margin-bottom:6px; }
        .group-name:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(123,29,29,.1); }

        @media (max-width:700px) { .we-card { width:calc(50% - 6px); } }

        /* ── Modals ── */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:2000; align-items:center; justify-content:center; padding:20px; }
        .modal-overlay.open { display:flex; }
        .modal { background:#fff; border-radius:16px; width:100%; max-width:420px; padding:22px; max-height:82vh; overflow-y:auto; }
        .modal h3 { font-size:16px; color:#1a1a1a; margin-bottom:14px; }
        .modal input[type=text] { width:100%; padding:10px 12px; border:1.5px solid #e2e2e6; border-radius:9px; font-size:13px; font-family:'Poppins',sans-serif; margin-bottom:12px; outline:none; }
        .modal input[type=text]:focus { border-color:var(--fb-blue); }
        .pick-list { max-height:280px; overflow-y:auto; border:1px solid #f0f0f0; border-radius:10px; margin-bottom:14px; }
        .pick-item { display:flex; align-items:center; gap:10px; padding:9px 12px; border-bottom:1px solid #f6f6f6; cursor:pointer; font-size:12.5px; }
        .pick-item:last-child { border-bottom:none; }
        .pick-item:hover { background:#faf9f9; }
        .pick-item input { width:15px; height:15px; accent-color:var(--fb-blue); }
        .pick-av { width:28px; height:28px; border-radius:50%; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:10.5px; flex-shrink:0; }
        .modal-actions { display:flex; gap:10px; justify-content:flex-end; }
        .btn-primary { background:var(--fb-blue); color:#fff; border:none; padding:9px 18px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; }
        .btn-primary:hover { background:#166fe0; }
        .btn-ghost { background:#f1f1f3; color:#555; border:none; padding:9px 18px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; }

        @media (max-width:700px) {
            .messages-topbar { padding:16px 16px 12px; gap:12px; }
            .messages-topbar-right { width:100%; gap:10px; }
            .messages-topbar .search-wrap { flex:1; min-width:0; }
            .messages-topbar .search-wrap input { width:100%; }
        }
        @media (max-width:900px) {
            .chat-list-pane { width:100%; }
            .chat-thread-pane { display:none; }
            body.thread-open .chat-list-pane { display:none; }
            body.thread-open .chat-thread-pane { display:flex; }
            .chat-profile-pane { display:none !important; }
            /* The in-thread "back to conversations" arrow only makes sense
               once the list pane is hidden and the thread takes over the
               whole screen — it was stuck at display:none with no rule to
               reveal it, leaving mobile users with no way back but the
               browser's own back button. */
            .mobile-back { display:flex !important; align-items:center; justify-content:center; width:34px; height:34px; border-radius:50%; color:#65676b; text-decoration:none; flex-shrink:0; }
            .mobile-back:hover { background:#f0f2f5; }
        }
    </style>
</head>
<body class="{{ $active ? 'thread-open' : '' }} {{ Auth::user()->isFaculty() ? 'is-faculty' : '' }}">

@if(Auth::user()->isAlumni())
    @include('partials.alumni-sidebar', ['active' => 'messages'])
@elseif(Auth::user()->isChair())
    @include('partials.chair-sidebar', ['active' => 'messages'])
@elseif(Auth::user()->isFaculty())
    @include('partials.faculty-sidebar', ['active' => ''])
@else
    @include('partials.sidebar', ['active' => 'messages'])
@endif

{{-- .main is used by the faculty/chair/alumni sidebars, .main-content by the student sidebar --}}
<div class="main main-content messages-page" style="padding:0;">
    <div class="messages-topbar">
        <div>
            <div class="msg-titlerow">
                <div class="page-title">Messages</div>
                @if(Auth::user()->isChair())
                    <div class="msg-switch" role="tablist" aria-label="Messages sections">
                        <a class="{{ ($announce ?? null) ? '' : 'on' }}" href="{{ route('messages.index') }}"><i class="fas fa-comment-dots"></i> Chats</a>
                        <a class="{{ ($announce ?? null) ? 'on' : '' }}" href="{{ route('messages.index', ['view' => 'announcements']) }}"><i class="fas fa-bullhorn"></i> Announcements</a>
                    </div>
                @endif
            </div>
            @unless(Auth::user()->isChair())
                <div class="page-subtitle">Chat with faculty, classmates, and the community.</div>
            @endunless
        </div>
        <div class="messages-topbar-right">
            <div class="search-wrap gs-wrap">
                <i class="fas fa-search"></i>
                <input type="text" data-gs="true" placeholder="Search topics, questions...">
            </div>
            <a class="notif-btn" href="{{ route('notifications.index') }}" title="Notifications" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                @if($unreadNotifications > 0)<span class="badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>@endif
            </a>
            <div class="header-dropdown-wrap">
                <button class="profile-avatar" id="topbarProfileBtn">@include('partials.avatar-content')</button>
                <div class="dropdown-menu" id="topbarProfileDropdown">
                    @if(Auth::user()->isFaculty())
                        <a href="{{ route('faculty.settings') }}"><i class="fas fa-user"></i> Profile Settings</a>
                    @elseif(Auth::user()->isAlumni())
                        <a href="{{ route('alumni.profile') }}"><i class="fas fa-user"></i> Profile Settings</a>
                    @elseif(!Auth::user()->isChair())
                        <a href="#" class="js-open-profile-modal"><i class="fas fa-user"></i> Profile Settings</a>
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
<div class="chat-shell">
@if($announce ?? null)
    @include('messages.partials.announcements')
@else
    <div class="chat-list-pane">
        <div class="cl-head">
            <div class="cl-head-top">
                <div class="cl-title">Chats</div>
            </div>
            <div class="cl-actions-row">
                <button type="button" class="cl-btn primary" onclick="openNewMessage()"><i class="fas fa-pen"></i> New message</button>
                @if(Auth::user()->hasAlumniAccess() || Auth::user()->isChair())
                    <button type="button" class="cl-btn ghost" onclick="document.getElementById('newGroupModal').classList.add('open')"><i class="fas fa-users"></i> New group</button>
                @endif
            </div>
            <div class="cl-search">
                <i class="fas fa-magnifying-glass"></i>
                <input type="text" id="chatSearch" placeholder="Search chats" oninput="filterChats()">
            </div>
            @if(Auth::user()->isChair())
                <div class="cl-pills" id="clPills" role="tablist" aria-label="Filter chats">
                    <button type="button" class="cl-pill on" data-kind="all">All</button>
                    <button type="button" class="cl-pill" data-kind="faculty">Faculty</button>
                    <button type="button" class="cl-pill" data-kind="student">Students</button>
                    <button type="button" class="cl-pill" data-kind="group">Groups</button>
                </div>
            @endif
        </div>
        <div class="cl-items" id="clItems">
            @forelse($conversations as $c)
                @php
                    $isGroup = $c->type === 'group';
                    $name = $c->displayNameFor(Auth::user());
                    $rowOther = $isGroup ? null : $c->participants->firstWhere('id', '!=', Auth::id());
                    $rowOnline = $rowOther?->last_login_at && $rowOther->last_login_at->diffInMinutes(now()) <= 5;
                    $unread = $c->unreadCountFor(Auth::user());
                    $rowRole = $rowOther ? \App\Http\Controllers\ChatController::roleMeta($rowOther->role_id) : null;
                    $kind = $isGroup ? 'group' : ($rowRole['key'] ?? 'staff');
                    $preview = $c->latestMessage ? ($c->latestMessage->sender_id === Auth::id() ? 'You: ' : '') . \Illuminate\Support\Str::limit($c->latestMessage->body, 38) : 'No messages yet';
                @endphp
                <a href="{{ route('messages.show', $c->id) }}" class="cl-item {{ $active && $active->id === $c->id ? 'active' : '' }} {{ $unread > 0 ? 'has-unread' : '' }}" data-name="{{ strtolower($name) }}" data-kind="{{ $kind }}">
                    <div class="cl-avatar {{ $isGroup ? 'group' : ($rowOnline ? 'online' : 'offline') }}">
                        @if($isGroup)<i class="fas fa-users"></i>@elseif($rowOther)@include('partials.user-avatar', ['user' => $rowOther])@else{{ strtoupper(substr($name,0,1)) }}@endif
                    </div>
                    <div class="cl-info">
                        <div class="cl-name"><span class="nm">{{ $name }}</span>@if($c->is_default_group)<i class="fas fa-house-chimney" style="font-size:10px;color:#bbb;" title="Default community chat"></i>@endif
                            @if($rowRole)<span class="cl-chip" style="background:{{ $rowRole['bg'] }};color:{{ $rowRole['color'] }}">{{ $rowRole['label'] }}</span>@elseif($isGroup && ! $c->is_default_group)<span class="cl-chip group">Group</span>@endif</div>
                        <div class="cl-preview">{{ $preview }}</div>
                    </div>
                    <div class="cl-meta">
                        <div class="cl-time">{{ ($c->latestMessage->created_at ?? $c->created_at)->diffForHumans(null, true) }}</div>
                        @if($unread > 0)<div class="cl-unread-dot" title="{{ $unread }} unread"></div>@endif
                    </div>
                </a>
            @empty
                <div class="cl-empty">
                    <i class="fas fa-comments"></i>
                    <strong>No chats yet</strong>
                    <span>{{ Auth::user()->isChair() ? 'Message a faculty member or a student to get started.' : 'Start a conversation with the New message button.' }}</span>
                </div>
            @endforelse
            <div class="cl-empty" id="clNoMatch" hidden><i class="fas fa-magnifying-glass"></i><strong>No chats match</strong></div>
        </div>
    </div>

    <div class="chat-thread-pane">
        @if($active)
            @php
                $isGroup = $active->type === 'group';
                $threadName = $active->displayNameFor(Auth::user());
                $canManageGroup = $isGroup && (Auth::user()->isChair() || $active->created_by === Auth::id());
            @endphp
            <div class="thread-head">
                <a href="{{ route('messages.index') }}" style="display:none;" class="mobile-back"><i class="fas fa-arrow-left"></i></a>
                <div @if($isGroup) role="button" tabindex="0" onclick="document.getElementById('groupInfoModal').classList.add('open')" style="cursor:pointer;display:flex;align-items:center;gap:12px;" @else style="display:flex;align-items:center;gap:12px;" @endif>
                    <div class="cl-avatar {{ $isGroup ? 'group' : (($presence === 'Active now') ? 'online' : 'offline') }}" style="width:38px;height:38px;font-size:12px;">
                        @if($isGroup)<i class="fas fa-users"></i>@elseif($otherUser)@include('partials.user-avatar', ['user' => $otherUser])@else{{ strtoupper(substr($threadName,0,1)) }}@endif
                    </div>
                    <div>
                        <div class="thread-title">{{ $threadName }}</div>
                        @if($isGroup)
                            <div class="thread-sub" style="color:#999;">{{ $active->participants->count() }} members @if($active->is_default_group)· Default community chat @endif <i class="fas fa-chevron-right" style="font-size:9px;margin-left:3px;"></i></div>
                        @else
                            <div class="thread-sub"><i class="fas fa-circle" style="font-size:7px;color:{{ $presence === 'Active now' ? '#31a24c' : '#bbb' }};"></i> {{ $presence }}</div>
                        @endif
                    </div>
                </div>
                <button type="button" class="thread-form-icon" id="toggleInfoBtn" title="Conversation info" style="margin-left:auto;"><i class="fas fa-circle-info"></i></button>
            </div>

            <div class="thread-body" id="threadBody" data-conversation="{{ $active->id }}" data-poll-url="{{ route('messages.poll', $active->id) }}" data-send-url="{{ route('messages.send', $active->id) }}">
                @foreach($messages as $m)
                    @php $mine = $m->sender_id === Auth::id(); @endphp
                    <div class="msg-row {{ $mine ? 'mine' : 'theirs' }}" data-id="{{ $m->id }}">
                        @if(!$mine && $isGroup)<div class="msg-sender">{{ $m->sender->name ?? 'User' }}</div>@endif
                        @if($m->file_path)
                            @php $meta = $m->iconMeta(); @endphp
                            @if($m->file_category === 'image')
                                <a href="{{ $m->url() }}" target="_blank" rel="noopener"><img class="msg-attach-img" src="{{ $m->url() }}" alt=""></a>
                            @else
                                <a class="msg-attach-file" href="{{ route('messages.attachments.download', $m->id) }}" target="_blank" rel="noopener">
                                    <div class="msg-af-icon" style="background:{{ $meta['color'] }};"><i class="fas {{ $meta['icon'] }}"></i></div>
                                    <div class="msg-af-info">
                                        <div class="msg-af-name">{{ $m->original_name }}</div>
                                        <div class="msg-af-size">{{ $m->humanSize() }}</div>
                                    </div>
                                </a>
                            @endif
                        @endif
                        @if($m->body !== '')<div class="msg-bubble">{{ $m->body }}</div>@endif
                        <div class="msg-time">{{ $m->created_at->format('g:i A') }}</div>
                    </div>
                @endforeach
            </div>

            <form class="thread-form" id="threadForm">
                @csrf
                <button type="button" class="thread-form-icon" title="Attach a file" id="attachFileBtn"><i class="fas fa-paperclip"></i></button>
                <button type="button" class="thread-form-icon" title="Send a photo" id="attachImageBtn"><i class="far fa-image"></i></button>
                <input type="file" id="fileInput" hidden accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.csv,.txt,.rtf,.odt,.jpg,.jpeg,.png,.gif,.webp,.zip,.rar">
                <input type="file" id="imageInput" hidden accept="image/*">
                <input type="text" name="body" id="threadInput" placeholder="Aa" autocomplete="off" maxlength="3000">
                <button type="submit" class="send-btn" id="sendBtn"><i class="fas fa-thumbs-up"></i></button>
            </form>
        @else
            @if(Auth::user()->isChair())
                @php $contactCounts = $contacts->countBy('key'); @endphp
                <div class="thread-empty welcome">
                    <i class="fas fa-comments"></i>
                    <h3>Start a conversation</h3>
                    <p>Pick who you want to message, or open a chat on the left.</p>
                    <div class="we-cards">
                        <button type="button" class="we-card" onclick="openNewMessage('faculty')"><i class="fas fa-chalkboard-user" style="background:#ede9fe;color:#6d28d9;"></i><b>Faculty</b><small>{{ $contactCounts['faculty'] ?? 0 }} people</small></button>
                        <button type="button" class="we-card" onclick="openNewMessage('student')"><i class="fas fa-user-graduate" style="background:#dbeafe;color:#1d4ed8;"></i><b>Students</b><small>{{ $contactCounts['student'] ?? 0 }} people</small></button>
                        <button type="button" class="we-card" onclick="document.getElementById('newGroupModal').classList.add('open')"><i class="fas fa-users" style="background:#f5e8e8;color:var(--primary);"></i><b>Group chat</b><small>Faculty and students</small></button>
                    </div>
                </div>
            @else
                <div class="thread-empty">
                    <i class="fas fa-comments"></i>
                    <div>Select a conversation to start chatting.</div>
                </div>
            @endif
        @endif
    </div>

    @if($active)
        <div class="chat-profile-pane" id="chatProfilePane">
            <div class="pp-close-wrap"><button type="button" class="thread-form-icon" id="closeInfoBtn" title="Close"><i class="fas fa-xmark"></i></button></div>

            <div class="pp-identity">
                <div class="pp-avatar {{ $isGroup ? 'group' : '' }}">
                    @if($isGroup)<i class="fas fa-users"></i>@elseif($otherUser)@include('partials.user-avatar', ['user' => $otherUser])@else{{ strtoupper(substr($threadName,0,1)) }}@endif
                </div>
                <div class="pp-name">{{ $threadName }}</div>
                @if($isGroup)
                    <div class="pp-sub">{{ $active->participants->count() }} members</div>
                    <button type="button" class="pp-action-btn" onclick="document.getElementById('groupInfoModal').classList.add('open')"><i class="fas fa-users"></i> See group info</button>
                @else
                    <div class="pp-sub"><i class="fas fa-circle" style="font-size:6px;color:{{ $presence === 'Active now' ? '#31a24c' : '#bbb' }};"></i> {{ $presence }}</div>
                @endif
            </div>

            @if($isGroup)
                <div class="pp-section">
                    <div class="pp-section-title">Members</div>
                    <div class="pp-members">
                        @foreach($active->participants->take(8) as $member)
                            <div class="pp-member-av" title="{{ $member->name }}">@include('partials.user-avatar', ['user' => $member])</div>
                        @endforeach
                        @if($active->participants->count() > 8)
                            <div class="pp-member-av pp-member-more">+{{ $active->participants->count() - 8 }}</div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="pp-section">
                <div class="pp-section-title">Shared Media</div>
                @if($sharedMedia->isEmpty())
                    <div class="pp-empty">No photos shared yet.</div>
                @else
                    <div class="pp-media-grid">
                        @foreach($sharedMedia->take(9) as $m)
                            <a href="{{ $m->url() }}" target="_blank" rel="noopener" class="pp-media-thumb">
                                <img src="{{ $m->url() }}" alt="">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="pp-section">
                <div class="pp-section-title">Files</div>
                @if($sharedFiles->isEmpty())
                    <div class="pp-empty">No files shared yet.</div>
                @else
                    <div class="pp-file-list">
                        @foreach($sharedFiles->take(12) as $f)
                            @php $meta = $f->iconMeta(); @endphp
                            <a class="pp-file-row" href="{{ route('messages.attachments.download', $f->id) }}" target="_blank" rel="noopener">
                                <div class="pp-file-icon" style="background:{{ $meta['color'] }};"><i class="fas {{ $meta['icon'] }}"></i></div>
                                <div class="pp-file-info">
                                    <div class="pp-file-name">{{ $f->original_name }}</div>
                                    <div class="pp-file-meta">{{ $f->humanSize() }} · {{ $f->created_at->format('M j') }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="pp-section">
                <div class="pp-section-title">Shared Links</div>
                @if($sharedLinks->isEmpty())
                    <div class="pp-empty">No links shared yet.</div>
                @else
                    <div class="pp-link-list">
                        @foreach($sharedLinks as $link)
                            <a class="pp-link-row" href="{{ $link['url'] }}" target="_blank" rel="noopener">
                                <div class="pp-link-icon"><i class="fas fa-link"></i></div>
                                <div class="pp-link-info">
                                    <div class="pp-link-host">{{ $link['host'] }}</div>
                                    <div class="pp-link-url">{{ \Illuminate\Support\Str::limit($link['url'], 42) }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif
</div>
</div>

@if($active && $active->type === 'group')
    {{-- Group Info / Settings modal --}}
    <div class="modal-overlay" id="groupInfoModal">
        <div class="modal">
            <h3><i class="fas fa-users" style="color:var(--fb-blue);"></i> {{ $active->displayNameFor(Auth::user()) }}</h3>

            {{-- Status and validation messages surface as SweetAlert popups via partials.alerts --}}

            @if($canManageGroup)
                <form method="POST" action="{{ route('messages.rename', $active->id) }}" style="display:flex; gap:8px; margin-bottom:14px;"
                      data-confirm="Everyone in this conversation will see the new name."
                      data-confirm-title="Rename this group?"
                      data-confirm-ok="Yes, rename it"
                      data-confirm-icon="question">
                    @csrf @method('PUT')
                    <input type="text" name="name" value="{{ $active->name }}" required style="flex:1;margin-bottom:0;">
                    <button type="submit" class="btn-primary" style="flex-shrink:0;"><i class="fas fa-pen"></i></button>
                </form>
            @endif

            <div style="font-size:11.5px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">
                {{ $active->participants->count() }} Member{{ $active->participants->count() === 1 ? '' : 's' }}
            </div>
            <div class="pick-list" style="margin-bottom:14px;">
                @foreach($active->participants as $member)
                    <div class="pick-item" style="cursor:default;">
                        <div class="pick-av">{{ strtoupper(substr($member->first_name,0,1)) }}</div>
                        <span>
                            {{ $member->name }}{{ $member->id === Auth::id() ? ' (You)' : '' }}
                            <small style="color:#aaa;">({{ ucfirst($member->roleName() ?? '') }})</small>
                            @if($active->created_by === $member->id)<small style="color:var(--fb-blue);font-weight:600;"> · Creator</small>@endif
                        </span>
                    </div>
                @endforeach
            </div>

            @if($canManageGroup && $addableCandidates->isNotEmpty())
                <form method="POST" action="{{ route('messages.members.add', $active->id) }}" style="margin-bottom:14px;"
                      data-confirm="The people you selected will be added to this group and can read new messages from now on."
                      data-confirm-title="Add these members?"
                      data-confirm-ok="Yes, add them"
                      data-confirm-icon="question">
                    @csrf
                    <div style="font-size:11.5px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Add Members</div>
                    <input type="text" id="addMemberSearch" placeholder="Search students / alumni..." oninput="filterPick('addMemberSearch','addMemberPickList')">
                    <div class="pick-list" id="addMemberPickList" style="margin-bottom:10px;">
                        @foreach($addableCandidates as $u)
                            <label class="pick-item" data-name="{{ strtolower($u->name) }}">
                                <input type="checkbox" name="member_ids[]" value="{{ $u->id }}">
                                <div class="pick-av">{{ strtoupper(substr($u->first_name,0,1)) }}</div>
                                <span>{{ $u->name }} <small style="color:#aaa;">({{ ucfirst($u->roleName() ?? '') }})</small></span>
                            </label>
                        @endforeach
                    </div>
                    <button type="submit" class="btn-primary"><i class="fas fa-user-plus"></i> Add Selected</button>
                </form>
            @endif

            <div class="modal-actions" style="justify-content:space-between;">
                @if(!$active->is_default_group)
                    <form method="POST" action="{{ route('messages.leave', $active->id) }}"
                          data-confirm="You will stop receiving messages from &quot;{{ $active->displayNameFor(Auth::user()) }}&quot; and will need to be re-added to rejoin."
                          data-confirm-title="Leave this group chat?"
                          data-confirm-ok="Yes, leave group"
                          data-confirm-danger>
                        @csrf
                        <button type="submit" class="btn-ghost" style="color:var(--accent);"><i class="fas fa-right-from-bracket"></i> Leave Group</button>
                    </form>
                @else
                    <span></span>
                @endif
                <button type="button" class="btn-ghost" onclick="document.getElementById('groupInfoModal').classList.remove('open')">Close</button>
            </div>
        </div>
    </div>
@endif

{{-- New Message modal: people sorted by who they are --}}
<div class="modal-overlay" id="newMsgModal">
    <div class="modal wide">
        <div class="mh"><h3>New message</h3><p>Choose who you want to chat with.</p></div>
        <div class="mb">
            @include('messages.partials.people-picker', ['people' => $contacts, 'mode' => 'dm', 'id' => 'dmPicker'])
        </div>
        <div class="mf"><button type="button" class="btn-ghost" onclick="document.getElementById('newMsgModal').classList.remove('open')">Close</button></div>
    </div>
</div>

@if(Auth::user()->hasAlumniAccess() || Auth::user()->isChair())
    {{-- New Group modal --}}
    <div class="modal-overlay" id="newGroupModal">
        <div class="modal wide">
            <form method="POST" action="{{ route('messages.group.create') }}" style="display:contents;"
                  data-confirm="A group conversation will be created and everyone you picked will be added to it."
                  data-confirm-title="Create this group chat?"
                  data-confirm-ok="Yes, create group"
                  data-confirm-icon="question">
                @csrf
                <div class="mh"><h3>New group chat</h3><p>Name the group, then tick who should be in it.</p></div>
                <div class="mb">
                    <input type="text" class="group-name" name="name" placeholder="Group name" required maxlength="120">
                    @include('messages.partials.people-picker', ['people' => $groupContacts, 'mode' => 'group', 'id' => 'groupPicker'])
                </div>
                <div class="mf">
                    <button type="button" class="btn-ghost" onclick="document.getElementById('newGroupModal').classList.remove('open')">Cancel</button>
                    <button type="submit" class="btn-primary"><i class="fas fa-users"></i> Create group</button>
                </div>
            </form>
        </div>
    </div>
@endif

<script>
/* Chat list: the search box and (for the chair) the Faculty / Students / Groups pills work together. */
function filterChats() {
    const q = (document.getElementById('chatSearch')?.value || '').toLowerCase();
    const kind = document.querySelector('#clPills .cl-pill.on')?.dataset.kind || 'all';
    let shown = 0;
    document.querySelectorAll('#clItems .cl-item').forEach(el => {
        const ok = el.dataset.name.includes(q) && (kind === 'all' || el.dataset.kind === kind);
        el.style.display = ok ? '' : 'none';
        if (ok) shown++;
    });
    const none = document.getElementById('clNoMatch');
    if (none) none.hidden = shown > 0 || !document.querySelector('#clItems .cl-item');
}
document.querySelectorAll('#clPills .cl-pill').forEach(btn => btn.addEventListener('click', () => {
    document.querySelectorAll('#clPills .cl-pill').forEach(b => b.classList.toggle('on', b === btn));
    filterChats();
}));
function filterPick(inputId, listId) {
    const q = document.getElementById(inputId).value.toLowerCase();
    document.getElementById(listId).querySelectorAll('[data-name]').forEach(function (el) {
        el.style.display = el.dataset.name.includes(q) ? '' : 'none';
    });
}
document.querySelectorAll('.modal-overlay').forEach(function (ov) {
    ov.addEventListener('click', function (e) { if (e.target === ov) ov.classList.remove('open'); });
});

(function () {
    const btn = document.getElementById('topbarProfileBtn');
    const drop = document.getElementById('topbarProfileDropdown');
    if (btn && drop) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            drop.classList.toggle('active');
        });
        document.addEventListener('click', function () { drop.classList.remove('active'); });
        drop.addEventListener('click', function (e) { e.stopPropagation(); });
    }
})();

(function () {
    const pane = document.getElementById('chatProfilePane');
    const toggleBtn = document.getElementById('toggleInfoBtn');
    const closeBtn = document.getElementById('closeInfoBtn');
    if (!pane) return;

    function isNarrow() { return window.matchMedia('(max-width:1300px)').matches; }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            if (isNarrow()) {
                pane.classList.toggle('open');
            } else {
                document.body.classList.toggle('info-hidden');
            }
        });
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            pane.classList.remove('open');
            document.body.classList.add('info-hidden');
        });
    }
})();

(function () {
    const body = document.getElementById('threadBody');
    const form = document.getElementById('threadForm');
    if (!body) return;

    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    const input = document.getElementById('threadInput');
    const sendBtn = document.getElementById('sendBtn');
    if (input && sendBtn) {
        function syncSendIcon() {
            sendBtn.innerHTML = input.value.trim()
                ? '<i class="fas fa-paper-plane"></i>'
                : '<i class="fas fa-thumbs-up"></i>';
        }
        input.addEventListener('input', syncSendIcon);
        syncSendIcon();
    }

    function scrollToBottom() { body.scrollTop = body.scrollHeight; }
    scrollToBottom();

    function lastMessageId() {
        const rows = body.querySelectorAll('.msg-row');
        return rows.length ? rows[rows.length - 1].dataset.id : 0;
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function attachmentHtml(a) {
        if (!a) return '';
        if (a.is_image) {
            return '<a href="' + a.url + '" target="_blank" rel="noopener"><img class="msg-attach-img" src="' + a.preview_url + '" alt=""></a>';
        }
        return (
            '<a class="msg-attach-file" href="' + a.url + '" target="_blank" rel="noopener">' +
                '<div class="msg-af-icon" style="background:' + a.color + ';"><i class="fas ' + a.icon + '"></i></div>' +
                '<div class="msg-af-info">' +
                    '<div class="msg-af-name">' + escapeHtml(a.name || 'File') + '</div>' +
                    '<div class="msg-af-size">' + escapeHtml(a.size || '') + '</div>' +
                '</div>' +
            '</a>'
        );
    }

    function appendMessage(m, mine) {
        const row = document.createElement('div');
        row.className = 'msg-row ' + (mine ? 'mine' : 'theirs');
        row.dataset.id = m.id;
        let html = '';
        if (!mine) html += '<div class="msg-sender">' + escapeHtml(m.sender_name) + '</div>';
        html += attachmentHtml(m.attachment);
        if (m.body !== '') html += '<div class="msg-bubble"></div>';
        html += '<div class="msg-time">' + escapeHtml(m.created_at) + '</div>';
        row.innerHTML = html;
        const bubble = row.querySelector('.msg-bubble');
        if (bubble) bubble.textContent = m.body;
        body.appendChild(row);
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const text = input.value.trim() || '👍';
            if (!text) return;

            fetch(body.dataset.sendUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ body: text }),
            })
                .then(r => r.json())
                .then(data => {
                    appendMessage(data.message, true);
                    scrollToBottom();
                    input.value = '';
                    if (sendBtn) sendBtn.innerHTML = '<i class="fas fa-thumbs-up"></i>';
                })
                .catch(() => { form.submit(); });
        });
    }

    const fileInput = document.getElementById('fileInput');
    const imageInput = document.getElementById('imageInput');
    const attachFileBtn = document.getElementById('attachFileBtn');
    const attachImageBtn = document.getElementById('attachImageBtn');

    function sendFile(file) {
        if (!file) return;
        const notice = document.createElement('div');
        notice.className = 'msg-uploading';
        notice.textContent = 'Sending ' + file.name + '…';
        body.appendChild(notice);
        scrollToBottom();

        const fd = new FormData();
        fd.append('file', file);

        fetch(body.dataset.sendUrl, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: fd,
        })
            .then(r => r.json())
            .then(data => { notice.remove(); appendMessage(data.message, true); scrollToBottom(); })
            .catch(() => { notice.textContent = 'Failed to send ' + file.name + '.'; });
    }

    if (attachFileBtn) attachFileBtn.addEventListener('click', function () { fileInput.click(); });
    if (attachImageBtn) attachImageBtn.addEventListener('click', function () { imageInput.click(); });
    if (fileInput) fileInput.addEventListener('change', function () { if (this.files[0]) sendFile(this.files[0]); this.value = ''; });
    if (imageInput) imageInput.addEventListener('change', function () { if (this.files[0]) sendFile(this.files[0]); this.value = ''; });

    function poll() {
        fetch(body.dataset.pollUrl + '?after_id=' + lastMessageId(), {
            headers: { 'Accept': 'application/json' },
        })
            .then(r => r.json())
            .then(data => {
                if (data.messages && data.messages.length) {
                    data.messages.forEach(m => { if (!m.is_mine) appendMessage(m, false); });
                    scrollToBottom();
                }
            })
            .catch(() => {});
    }

    setInterval(poll, 4000);
})();
</script>

    @include('partials.global-search')
    @include('partials.alerts')
</body>
</html>
