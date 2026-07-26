<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject->code }} Topics - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #7B1D1D;
            --primary-hover: #6a1818;
            --primary-light: #f5e8e8;
            --primary-mid: #c0392b;
            --accent-red: #c0392b;
            --sidebar-bg: linear-gradient(180deg, #a12626 0%, #7B1D1D 34%, #3d0c0c 74%, #1a0a0a 100%);
            --white: #ffffff;
            --gray-100: #f8f9fa;
            --gray-200: #f0f0f0;
            --gray-300: #e0e0e0;
            --gray-500: #999999;
            --gray-700: #555555;
            --gray-900: #333333;
            --green: #10b981;
            --blue: #3b82f6;
            --orange: #f59e0b;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

        .main-content { margin-left:220px; padding:30px 40px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main-content { margin-left:70px; }

        /* ─── TOP BAR ─── */
        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; gap:20px; }
        .top-bar-left { display:flex; align-items:center; gap:14px; }
        .top-bar-right { display:flex; align-items:center; gap:14px; }

        .search-wrap { position:relative; }
        .search-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#aaa; font-size:14px; }
        .search-wrap input {
            width:280px; padding:10px 14px 10px 36px;
            border:1px solid #e0e0e0; border-radius:24px;
            font-size:13px; font-family:'Poppins',sans-serif;
            background:white; color:#555; outline:none;
        }
        .search-wrap input:focus { border-color:var(--primary); }
        .search-wrap input::placeholder { color:#bbb; }

        .notif-btn {
            position:relative; width:40px; height:40px;
            border:none; background:white; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:17px; color:#555; cursor:pointer;
            box-shadow:0 1px 4px rgba(0,0,0,0.08); text-decoration:none;
        }
        .notif-btn:hover { background:#f0f0f0; }
        .badge {
            position:absolute; top:-3px; right:-3px;
            width:18px; height:18px; background:var(--accent-red);
            color:white; border-radius:50%; font-size:10px; font-weight:700;
            display:flex; align-items:center; justify-content:center;
        }
        .profile-avatar {
            width:40px; height:40px; background:var(--primary);
            border-radius:10px; border:none; color:white;
            font-weight:700; font-size:14px; cursor:pointer;
            font-family:'Poppins',sans-serif; transition:background 0.2s;
        }
        .profile-avatar:hover { background:var(--primary-hover); }

        .header-dropdown-wrap { position:relative; }
        .dropdown-menu {
            position:absolute; top:calc(100% + 8px); right:0;
            background:white; border:1px solid #e5e7eb; border-radius:10px;
            min-width:185px; box-shadow:0 6px 20px rgba(0,0,0,0.12);
            display:none; z-index:2000;
        }
        .dropdown-menu.active { display:block; }
        .dropdown-menu a, .dropdown-menu button {
            display:flex; align-items:center; gap:10px;
            padding:11px 16px; font-size:13px; font-family:'Poppins',sans-serif;
            text-decoration:none; color:#333; background:none; border:none;
            width:100%; text-align:left; cursor:pointer; transition:background 0.2s;
            border-bottom:1px solid #f5f5f5;
        }
        .dropdown-menu a:last-child, .dropdown-menu form:last-child button { border-bottom:none; }
        .dropdown-menu a:hover, .dropdown-menu button:hover { background:#f9f9f9; }
        .dropdown-menu a i, .dropdown-menu button i { color:var(--primary); width:16px; text-align:center; }
        .dropdown-menu .logout-btn { color:#e53e3e; }
        .dropdown-menu .logout-btn i { color:#e53e3e; }

        .breadcrumb { display:flex; align-items:center; gap:8px; font-size:13px; color:#999; margin-bottom:18px; }
        .breadcrumb a { color:var(--primary); text-decoration:none; font-weight:500; }
        .breadcrumb a:hover { text-decoration:underline; }

        .subject-hero {
            position:relative; display:flex; flex-direction:column; gap:20px;
            border-radius:16px; padding:28px 32px; margin-bottom:26px;
            overflow:hidden; z-index:0;
        }
        .subject-hero::before {
            content:'';
            position:absolute; inset:-2px;
            border-radius:18px;
            background: linear-gradient(135deg, #c0392b, #7B1D1D, #a12626);
            z-index:-2;
        }
        .subject-hero::after {
            content:'';
            position:absolute; inset:0;
            border-radius:16px;
            background:#fff;
            z-index:-1;
        }
        /* abstract shapes */
        .hero-shapes { position:absolute; inset:0; pointer-events:none; overflow:hidden; z-index:0; }
        .hero-shapes span { position:absolute; border-radius:50%; }
        .hero-shapes .hs1 { top:-30px; right:8%; width:120px; height:120px; border:2px solid rgba(192,57,43,.08); }
        .hero-shapes .hs2 { bottom:-20px; right:18%; width:80px; height:80px; background:rgba(123,29,29,.04); }
        .hero-shapes .hs3 { top:20px; right:28%; width:0; height:0; border-left:16px solid transparent; border-right:16px solid transparent; border-bottom:28px solid rgba(192,57,29,.06); transform:rotate(15deg); }
        .hero-shapes .hs4 { bottom:10px; right:5%; width:60px; height:60px; border:1.5px dashed rgba(123,29,29,.07); border-radius:14px; transform:rotate(35deg); }
        .hero-shapes .hs5 { top:-15px; right:40%; width:40px; height:40px; background:rgba(255,200,100,.06); border-radius:50%; }
        .hero-top { display:flex; align-items:center; gap:20px; position:relative; z-index:1; }
        .hero-icon { width:70px; height:70px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; position:relative; }
        .hero-icon::before { content:''; position:absolute; inset:0; border-radius:50%; background:var(--icon-bg, #f5e8e8); z-index:0; }
        .hero-icon img { width:40px; height:40px; object-fit:contain; position:relative; z-index:1; }
        .hero-info h1 { font-size:24px; font-weight:800; color:#1a1a1a; }
        .hero-info p { font-size:13px; color:#888; margin-top:3px; }
        .hero-count { margin-left:auto; text-align:center; }
        .hero-count .n { font-size:26px; font-weight:800; color:var(--primary); }
        .hero-count .l { font-size:11px; color:#aaa; text-transform:uppercase; letter-spacing:.5px; }

        .hero-overall { border-top:1px solid #f2f2f2; padding-top:18px; position:relative; z-index:1; }
        .hero-overall-head { display:flex; justify-content:space-between; align-items:baseline; margin-bottom:9px; }
        .hero-overall-label { font-size:11.5px; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:.5px; }
        .hero-overall-value { font-size:20px; font-weight:800; }
        .hero-overall-sub { font-size:11px; color:#aaa; }
        .hero-track { height:14px; border-radius:8px; background:#f0f0f0; overflow:hidden; }
        .hero-fill {
            height:100%; border-radius:8px;
            background-image:repeating-linear-gradient(45deg, rgba(255,255,255,.18) 0 8px, transparent 8px 16px);
            transition:width .6s ease;
        }

        .section-title { font-size:16px; font-weight:700; color:#333; margin-bottom:14px; }

        .topic-search { position:relative; margin-bottom:18px; }
        .topic-search i { position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#bbb; font-size:13px; }
        .topic-search input {
            width:100%; padding:12px 16px 12px 40px; border:1.5px solid #e5e5e5; border-radius:10px;
            font:13px 'Poppins',sans-serif; background:#fff; outline:none; transition:border-color .15s;
        }
        .topic-search input:focus { border-color:var(--primary); }
        .topic-search .clear-btn {
            position:absolute; right:10px; top:50%; transform:translateY(-50%);
            border:0; background:#f3f4f6; color:#999; width:22px; height:22px; border-radius:50%;
            cursor:pointer; display:none; align-items:center; justify-content:center; font-size:10px;
        }
        .topic-search input:not(:placeholder-shown) ~ .clear-btn { display:flex; }

        .topics-list { display:flex; flex-direction:column; gap:12px; }
        .topic-node + .topic-node { margin-top:12px; }
        .topic-node .topic-node { margin-top:8px; }

        .topic-row-item {
            display:flex; align-items:center; gap:14px;
            background:#fff; border-radius:14px; padding:16px 18px;
            box-shadow:0 2px 8px rgba(0,0,0,.04); border:1px solid #f0f0f0;
        }
        .topic-row-item:not(.root) { padding:10px 14px; border-radius:10px; background:#fafafa; box-shadow:none; border:1px solid #f0f0f0; }

        .node-toggle { width:22px; height:22px; flex-shrink:0; border:0; background:#f3f4f6; color:#888; border-radius:6px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:11px; padding:0; }
        .node-toggle.open i { transform:rotate(90deg); }
        .node-toggle i { transition:transform .15s; }
        .node-toggle-spacer { width:22px; flex-shrink:0; }

        .topic-num { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:15px; color:#fff; flex-shrink:0; }
        .node-body { flex:1; min-width:0; }
        .node-name { font-size:14px; font-weight:600; color:#222; }
        .node-meta { font-size:11.5px; color:#aaa; margin-top:4px; display:flex; gap:12px; flex-wrap:wrap; }
        .node-meta span { display:inline-flex; align-items:center; gap:5px; }
        .node-meta a.meta-link { display:inline-flex; align-items:center; gap:5px; color:var(--primary); font-weight:600; text-decoration:none; }
        .node-meta a.meta-link:hover { text-decoration:underline; }
        .node-materials-link {
            display:flex; align-items:center; gap:6px; flex-shrink:0;
            color:var(--primary); background:var(--primary-light); font-size:11px; font-weight:600;
            padding:8px 12px; border-radius:8px; text-decoration:none; white-space:nowrap;
        }
        .node-materials-link:hover { background:var(--primary); color:#fff; }
        .node-materials-link i { font-size:13px; }
        .topic-children { margin-top:8px; }

        .node-progress { display:flex; align-items:center; gap:9px; margin-top:8px; }
        .progress-track { flex:1; max-width:200px; height:6px; background:#eee; border-radius:3px; overflow:hidden; }
        .progress-fill { height:100%; border-radius:3px; transition:width .3s; }
        .progress-fill.empty { width:0; }
        .progress-label { font-size:10.5px; font-weight:600; white-space:nowrap; }
        .progress-label.muted { color:#bbb; font-weight:500; }

        .empty { text-align:center; padding:60px 20px; color:#aaa; background:#fff; border-radius:14px; }
        .empty i { font-size:36px; color:#e5d5d5; display:block; margin-bottom:12px; }

        @media (max-width:768px) {
            .main-content { margin-left:0; padding:20px 16px; }
            .hero-count { display:none; }
            .top-bar { flex-direction:column; align-items:flex-start; gap:12px; }
            .top-bar-right { width:100%; flex-wrap:wrap; }
            .search-wrap { flex:1; }
            .search-wrap input { width:100%; }
        }
    </style>
</head>
<body>

@include('partials.sidebar', ['active' => 'subjects'])
@include('partials.student-bottom-nav', ['active' => 'subjects'])
@include('partials.student-mobile-header')

@php $color = $subject->color ?: '#7B1D1D'; @endphp

<main class="main-content">
    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="top-bar-left">
            <div>
                <div class="breadcrumb">
                    <a href="{{ route('subjects') }}"><i class="fas fa-arrow-left"></i> Subjects</a>
                    <span>/</span>
                    <span>{{ $subject->code }}</span>
                </div>
            </div>
        </div>
        <div class="top-bar-right">
            <div class="search-wrap gs-wrap">
                <i class="fas fa-search"></i>
                <input type="text" data-gs="true" placeholder="Search topics, questions...">
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
                <button class="profile-avatar" id="profileBtn">@include('partials.avatar-content')</button>
                <div class="dropdown-menu" id="profileDropdown">
                    <a href="#"><i class="fas fa-user"></i> Profile Settings</a>
                    <a href="#"><i class="fas fa-chart-line"></i> My Progress</a>
                    <a href="#"><i class="fas fa-question-circle"></i> Help &amp; Support</a>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0;padding:0;">
                        @csrf
                        <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @php
        $heroHasAttempts = $overallAccuracy !== null;
        $heroColor = $heroHasAttempts
            ? ($overallAccuracy >= $subject->passing_threshold ? '#059669' : '#dc2626')
            : '#d1d5db';
    @endphp
    <div class="subject-hero">
        <div class="hero-shapes" aria-hidden="true">
            <span class="hs1"></span><span class="hs2"></span><span class="hs3"></span>
            <span class="hs4"></span><span class="hs5"></span>
        </div>
        <div class="hero-top">
            <div class="hero-icon" style="--icon-bg:{{ $color }}1a;">
                <img src="{{ asset('images/' . $subject->code . '.png') }}" alt="{{ $subject->code }}">
            </div>
            <div class="hero-info">
                <h1>{{ $subject->code }}</h1>
                <p>{{ $subject->name }}</p>
            </div>
            <div class="hero-count">
                <div class="n">{{ $topics->count() }}</div>
                <div class="l">Topics</div>
            </div>
        </div>

        <div class="hero-overall">
            <div class="hero-overall-head">
                <span class="hero-overall-label">Overall Progress</span>
                <span>
                    <span class="hero-overall-value" style="color:{{ $heroColor }};">{{ $heroHasAttempts ? $overallAccuracy.'%' : '—' }}</span>
                    <span class="hero-overall-sub">{{ $heroHasAttempts ? "· {$overallCorrect}/{$overallAttempts} correct across all topics" : '· Not attempted yet' }}</span>
                </span>
            </div>
            <div class="hero-track">
                <div class="hero-fill" style="width:{{ $heroHasAttempts ? $overallAccuracy : 0 }}%; background-color:{{ $heroColor }};"></div>
            </div>
        </div>
    </div>

    <div class="section-title">Choose a topic to study</div>

    @if($topicTree->isNotEmpty())
        <div class="topic-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" id="topicSearchInput" placeholder="Search topics and subtopics..." oninput="searchTopics(this.value)">
            <button type="button" class="clear-btn" onclick="const i=document.getElementById('topicSearchInput'); i.value=''; searchTopics(''); i.focus();"><i class="fas fa-xmark"></i></button>
        </div>
    @endif

    <div class="topics-list" id="topicsList">
        @if($topicTree->isNotEmpty())
            @include('student.partials.topic-node', ['topics' => $topicTree, 'subject' => $subject, 'color' => $color, 'depth' => 0])
        @else
            <div class="empty">
                <i class="fas fa-inbox"></i>
                No topics available for this subject yet.
            </div>
        @endif
    </div>

    <div class="empty" id="topicSearchEmpty" hidden>
        <i class="fas fa-magnifying-glass"></i>
        No topics or subtopics match your search.
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar && localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
    }

    const profileBtn = document.getElementById('profileBtn');
    const profileDrop = document.getElementById('profileDropdown');
    if (profileBtn && profileDrop) {
        profileBtn.addEventListener('click', e => {
            e.stopPropagation();
            profileDrop.classList.toggle('active');
        });
        document.addEventListener('click', () => profileDrop.classList.remove('active'));
        profileDrop.addEventListener('click', e => e.stopPropagation());
    }
});

function toggleStudentTopicChildren(button) {
    button.classList.toggle('open');
    const node = button.closest('.topic-node');
    const children = node.querySelector(':scope > .topic-children');
    if (children) children.hidden = !children.hidden;
}

function applyTopicSearch(node, query) {
    const row = node.querySelector(':scope > .topic-row-item');
    const nameEl = row.querySelector('.node-name');
    const selfMatch = nameEl.textContent.toLowerCase().includes(query);

    const childrenContainer = node.querySelector(':scope > .topic-children');
    let childMatch = false;
    if (childrenContainer) {
        childrenContainer.querySelectorAll(':scope > .topic-node').forEach(child => {
            if (applyTopicSearch(child, query)) childMatch = true;
        });
    }

    const visible = selfMatch || childMatch;
    node.style.display = visible ? '' : 'none';

    if (childrenContainer) {
        childrenContainer.hidden = !childMatch;
        const toggle = row.querySelector('.node-toggle');
        if (toggle) toggle.classList.toggle('open', childMatch);
    }

    return visible;
}

function searchTopics(rawQuery) {
    const query = rawQuery.trim().toLowerCase();
    const topLevelNodes = document.querySelectorAll('#topicsList > .topic-node');
    const emptyMsg = document.getElementById('topicSearchEmpty');

    if (!query) {
        document.querySelectorAll('.topic-node').forEach(node => { node.style.display = ''; });
        document.querySelectorAll('.topic-children').forEach(children => { children.hidden = true; });
        document.querySelectorAll('.node-toggle').forEach(toggle => toggle.classList.remove('open'));
        emptyMsg.hidden = true;
        return;
    }

    let anyVisible = false;
    topLevelNodes.forEach(node => { if (applyTopicSearch(node, query)) anyVisible = true; });
    emptyMsg.hidden = anyVisible;
}
</script>
@include('partials.global-search')
</body>
</html>
