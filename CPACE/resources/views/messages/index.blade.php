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
        .search-wrap input {
            width:280px; padding:11px 16px 11px 40px;
            border:1px solid #e0e0e0; border-radius:24px;
            font-size:14px; font-family:'Poppins',sans-serif;
            background:#fff; color:#555; outline:none;
        }
        .search-wrap input:focus { border-color:var(--primary); }
        .search-wrap input::placeholder { color:#bbb; }

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
        }
        .profile-avatar:hover { background:var(--primary-hover); }

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
        .cl-search input { width:100%; border:none; background:#f0f2f5; border-radius:20px; padding:11px 16px 11px 38px; font-size:14px; font-family:'Poppins',sans-serif; outline:none; }
        .cl-search input:focus { background:#e8eaed; }
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

        .pp-media-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:6px; }
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
            <div class="page-title">Messages</div>
            <div class="page-subtitle">Chat with faculty, classmates, and the community.</div>
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
                    <a href="#"><i class="fas fa-question-circle"></i> Help &amp; Support</a>
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
    <div class="chat-list-pane">
        <div class="cl-head">
            <div class="cl-head-top">
                <div class="cl-title">Chats</div>
                <div class="cl-actions">
                    <button type="button" class="cl-icon-btn" title="New Message" onclick="document.getElementById('newMsgModal').classList.add('open')"><i class="fas fa-pen"></i></button>
                    @if(Auth::user()->hasAlumniAccess() || Auth::user()->isChair())
                        <button type="button" class="cl-icon-btn" title="New Group" onclick="document.getElementById('newGroupModal').classList.add('open')"><i class="fas fa-users"></i></button>
                    @endif
                </div>
            </div>
            <div class="cl-search">
                <i class="fas fa-magnifying-glass"></i>
                <input type="text" id="chatSearch" placeholder="Search Messenger" oninput="filterPick('chatSearch','clItems')">
            </div>
        </div>
        <div class="cl-items" id="clItems">
            @forelse($conversations as $c)
                @php
                    $isGroup = $c->type === 'group';
                    $name = $c->displayNameFor(Auth::user());
                    $rowOther = $isGroup ? null : $c->participants->firstWhere('id', '!=', Auth::id());
                    $rowOnline = $rowOther?->last_login_at && $rowOther->last_login_at->diffInMinutes(now()) <= 5;
                    $unread = $c->unreadCountFor(Auth::user());
                    $preview = $c->latestMessage ? ($c->latestMessage->sender_id === Auth::id() ? 'You: ' : '') . \Illuminate\Support\Str::limit($c->latestMessage->body, 38) : 'No messages yet';
                @endphp
                <a href="{{ route('messages.show', $c->id) }}" class="cl-item {{ $active && $active->id === $c->id ? 'active' : '' }} {{ $unread > 0 ? 'has-unread' : '' }}" data-name="{{ strtolower($name) }}">
                    <div class="cl-avatar {{ $isGroup ? 'group' : ($rowOnline ? 'online' : 'offline') }}">
                        @if($isGroup)<i class="fas fa-users"></i>@elseif($rowOther)@include('partials.user-avatar', ['user' => $rowOther])@else{{ strtoupper(substr($name,0,1)) }}@endif
                    </div>
                    <div class="cl-info">
                        <div class="cl-name">{{ $name }} @if($c->is_default_group)<i class="fas fa-house-chimney" style="font-size:10px;color:#bbb;" title="Default community chat"></i>@endif</div>
                        <div class="cl-preview">{{ $preview }}</div>
                    </div>
                    <div class="cl-meta">
                        <div class="cl-time">{{ ($c->latestMessage->created_at ?? $c->created_at)->diffForHumans(null, true) }}</div>
                        @if($unread > 0)<div class="cl-unread-dot" title="{{ $unread }} unread"></div>@endif
                    </div>
                </a>
            @empty
                <div class="cl-empty">No conversations yet.</div>
            @endforelse
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
            <div class="thread-empty">
                <i class="fas fa-comments"></i>
                <div>Select a conversation to start chatting.</div>
            </div>
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

{{-- New Message modal --}}
<div class="modal-overlay" id="newMsgModal">
    <div class="modal">
        <h3>New Message</h3>
        <input type="text" id="msgSearch" placeholder="Search people..." oninput="filterPick('msgSearch','msgPickList')">
        <div class="pick-list" id="msgPickList">
            @foreach($messageable as $u)
                <form method="POST" action="{{ route('messages.start') }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $u->id }}">
                    <button type="submit" class="pick-item" style="width:100%;border:none;background:none;text-align:left;font-family:'Poppins',sans-serif;" data-name="{{ strtolower($u->name) }}">
                        <div class="pick-av">{{ strtoupper(substr($u->first_name,0,1)) }}</div>
                        <span>{{ $u->name }} <small style="color:#aaa;">({{ ucfirst($u->roleName() ?? '') }})</small></span>
                    </button>
                </form>
            @endforeach
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-ghost" onclick="document.getElementById('newMsgModal').classList.remove('open')">Close</button>
        </div>
    </div>
</div>

@if(Auth::user()->hasAlumniAccess() || Auth::user()->isChair())
    {{-- New Group modal --}}
    <div class="modal-overlay" id="newGroupModal">
        <div class="modal">
            <h3>New Group Chat</h3>
            <form method="POST" action="{{ route('messages.group.create') }}"
                  data-confirm="A group conversation will be created and everyone you picked will be added to it."
                  data-confirm-title="Create this group chat?"
                  data-confirm-ok="Yes, create group"
                  data-confirm-icon="question">
                @csrf
                <input type="text" name="name" placeholder="Group name" required>
                <input type="text" id="groupSearch" placeholder="Search students / alumni..." oninput="filterPick('groupSearch','groupPickList')">
                <div class="pick-list" id="groupPickList">
                    @foreach($groupCandidates as $u)
                        <label class="pick-item" data-name="{{ strtolower($u->name) }}">
                            <input type="checkbox" name="member_ids[]" value="{{ $u->id }}">
                            <div class="pick-av">{{ strtoupper(substr($u->first_name,0,1)) }}</div>
                            <span>{{ $u->name }} <small style="color:#aaa;">({{ ucfirst($u->roleName() ?? '') }})</small></span>
                        </label>
                    @endforeach
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-ghost" onclick="document.getElementById('newGroupModal').classList.remove('open')">Cancel</button>
                    <button type="submit" class="btn-primary"><i class="fas fa-users"></i> Create Group</button>
                </div>
            </form>
        </div>
    </div>
@endif

<script>
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
