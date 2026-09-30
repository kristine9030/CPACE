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

        /* ─── Subject header ───
           Same lively language as the Class Quizzes header: a pastel tint of
           the subject's own colour, a coloured emblem, and an illustration
           cluster tucked into the corner. Colours come from SubjectTheme
           (--s-base / --s-dark / --s-pastel / --s-soft set inline). */
        .subject-hero {
            position:relative; display:flex; flex-direction:column; gap:20px;
            border-radius:16px; padding:26px 30px; margin-bottom:26px;
            overflow:hidden; isolation:isolate;
            background:var(--s-pastel);
            border:1px solid rgba(0,0,0,.04);
            box-shadow:0 14px 28px -14px rgba(20,20,30,.35), 0 2px 6px rgba(20,20,30,.08);
        }
        .hero-illus { position:absolute; right:-8px; bottom:-16px; width:190px; height:190px; pointer-events:none; z-index:0; }
        .hero-illus .blob { position:absolute; right:10px; bottom:10px; width:140px; height:140px; border-radius:40px;
                            background:var(--s-soft); transform:rotate(-8deg); }
        .hero-illus i { position:absolute; color:var(--s-base); }
        .hero-illus .i1 { font-size:58px; right:66px; bottom:58px; opacity:.5; }
        .hero-illus .i2 { font-size:46px; right:24px; bottom:26px; opacity:.85; }

        .hero-top { display:flex; align-items:center; gap:20px; position:relative; z-index:1; }
        .hero-icon {
            width:70px; height:70px; border-radius:19px; flex-shrink:0;
            display:flex; align-items:center; justify-content:center;
            background:linear-gradient(145deg, var(--s-base), var(--s-dark));
            box-shadow:0 8px 16px -6px rgba(0,0,0,.35);
        }
        .hero-icon img {
            width:44px; height:44px; object-fit:contain;
            background:#fff; border-radius:12px; padding:5px;
        }
        .hero-kicker {
            display:flex; align-items:center; gap:6px; margin-bottom:4px;
            font-size:10.5px; font-weight:700; letter-spacing:1.6px; text-transform:uppercase;
            color:var(--s-base);
        }
        .hero-info h1 { font-family:'Montserrat',ui-sans-serif,system-ui,sans-serif; font-size:28px; font-weight:700; line-height:1.15; color:#1a1a1a; }
        .hero-info p { font-size:13.5px; color:#5c5450; margin-top:4px; }
        /* Frosted stat pill; right margin keeps it clear of the illustration. */
        .hero-count {
            margin-left:auto; margin-right:150px; text-align:center; flex-shrink:0;
            background:rgba(255,255,255,.72); border:1px solid rgba(255,255,255,.9);
            border-radius:14px; padding:10px 18px;
            box-shadow:0 4px 12px -6px rgba(0,0,0,.2);
        }
        .hero-count .n { font-size:26px; font-weight:800; color:var(--s-dark); line-height:1.1; }
        .hero-count .l { font-size:11px; color:var(--s-base); font-weight:600; text-transform:uppercase; letter-spacing:.5px; }

        /* Progress sits on a white panel inside the tinted header. */
        .hero-overall {
            position:relative; z-index:1;
            margin-right:150px;
            background:rgba(255,255,255,.78); border:1px solid rgba(255,255,255,.95);
            border-radius:14px; padding:14px 18px;
        }
        .hero-overall-head { display:flex; justify-content:space-between; align-items:baseline; gap:12px; flex-wrap:wrap; margin-bottom:9px; }
        .hero-overall-label { font-size:11.5px; font-weight:700; color:var(--s-dark); text-transform:uppercase; letter-spacing:.5px; }
        .hero-overall-value { font-size:20px; font-weight:800; }
        .hero-overall-sub { font-size:11px; color:#8a817d; }
        .hero-track { height:12px; border-radius:8px; background:var(--s-soft); overflow:hidden; }
        @media (max-width: 900px) {
            .hero-count, .hero-overall { margin-right:0; }
            .hero-illus { opacity:.35; }
        }
        .hero-fill {
            height:100%; border-radius:8px;
            background-image:repeating-linear-gradient(45deg, rgba(255,255,255,.18) 0 8px, transparent 8px 16px);
            transition:width .6s ease;
        }

        .section-title {
            font-size:16px; font-weight:700; color:#333;
            margin-bottom:14px; padding-bottom:9px; position:relative;
        }
        .section-title::after {
            content:''; position:absolute; left:0; bottom:0;
            width:38px; height:3px; border-radius:2px;
            background:linear-gradient(90deg, #c0392b, #7B1D1D);
        }

        .topic-search { position:relative; margin-bottom:18px; }
        .topic-search i { position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#bbb; font-size:13px; }
        .topic-search input {
            width:100%; padding:12px 16px 12px 40px; border:1.5px solid #f0e0dd; border-radius:10px;
            font:13px 'Poppins',sans-serif; background:#fff; outline:none;
            transition:border-color .15s, box-shadow .15s;
        }
        .topic-search input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(192,57,43,.10); }
        .topic-search .clear-btn {
            position:absolute; right:10px; top:50%; transform:translateY(-50%);
            border:0; background:#f3f4f6; color:#999; width:22px; height:22px; border-radius:50%;
            cursor:pointer; display:none; align-items:center; justify-content:center; font-size:10px;
        }
        .topic-search input:not(:placeholder-shown) ~ .clear-btn { display:flex; }

        .topics-list { display:flex; flex-direction:column; gap:12px; }
        .topic-node + .topic-node { margin-top:12px; }
        .topic-node .topic-node { margin-top:8px; }

        /* Root rows lift on hover and grow a maroon edge, so the list reads as
           a set of doors rather than a flat stack of boxes. */
        .topic-row-item {
            position:relative; overflow:hidden;
            display:flex; align-items:center; gap:14px;
            background:#fff; border-radius:14px; padding:16px 18px;
            box-shadow:0 2px 8px rgba(0,0,0,.04); border:1px solid #f4e7e4;
            transition:transform .2s, box-shadow .2s, border-color .2s;
        }
        .topic-row-item.root::before {
            content:''; position:absolute; left:0; top:0; bottom:0;
            width:4px; background:linear-gradient(180deg, #c0392b, #7B1D1D);
            opacity:0; transition:opacity .2s;
        }
        .topic-row-item.root:hover {
            transform:translateY(-2px);
            border-color:#f0d8d4;
            box-shadow:0 8px 20px rgba(123,29,29,.14);
        }
        .topic-row-item.root:hover::before { opacity:1; }
        .topic-row-item:not(.root) {
            padding:10px 14px; border-radius:10px;
            background:#fdf8f7; box-shadow:none; border:1px solid #f6e7e4;
        }
        .topic-row-item:not(.root):hover { background:#fdf3f1; border-color:#f0d8d4; }

        .node-toggle { width:22px; height:22px; flex-shrink:0; border:0; background:#fbeceb; color:var(--primary); border-radius:6px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:11px; padding:0; transition:background .15s; }
        .node-toggle:hover { background:#f7dcd9; }
        .node-toggle.open i { transform:rotate(90deg); }
        .node-toggle i { transition:transform .15s; }
        .node-toggle-spacer { width:22px; flex-shrink:0; }

        /* Brand maroon, not the subject's stored colour — the grid, the hero and
           these badges all read as one palette. */
        .topic-num {
            width:44px; height:44px; border-radius:12px;
            display:flex; align-items:center; justify-content:center;
            font-weight:700; font-size:15px; color:#fff; flex-shrink:0;
            background:linear-gradient(135deg, #c0392b 0%, #7B1D1D 100%);
            box-shadow:0 3px 8px rgba(123,29,29,.28);
        }
        .node-body { flex:1; min-width:0; }
        .node-name { font-size:14px; font-weight:600; color:#222; }
        .node-meta { font-size:11.5px; color:#a98e8a; margin-top:4px; display:flex; gap:12px; flex-wrap:wrap; }
        .node-meta span { display:inline-flex; align-items:center; gap:5px; }
        .node-meta a.meta-link { display:inline-flex; align-items:center; gap:5px; color:var(--primary); font-weight:600; text-decoration:none; }
        .node-meta a.meta-link:hover { text-decoration:underline; }
        .node-materials-link {
            display:flex; align-items:center; gap:6px; flex-shrink:0;
            color:#fff; background:linear-gradient(135deg, #c0392b 0%, #7B1D1D 100%);
            font-size:11px; font-weight:600;
            padding:9px 14px; border-radius:8px; text-decoration:none; white-space:nowrap;
            transition:background .2s, box-shadow .2s;
        }
        .node-materials-link:hover {
            background:linear-gradient(135deg, #a12626 0%, #5c1414 100%);
            box-shadow:0 5px 14px rgba(123,29,29,.30);
        }
        .node-materials-link i { font-size:13px; }
        .topic-children { margin-top:8px; }

        .node-progress { display:flex; align-items:center; gap:9px; margin-top:8px; }
        .progress-track { flex:1; max-width:200px; height:6px; background:#f7e4e0; border-radius:3px; overflow:hidden; }
        .progress-fill { height:100%; border-radius:3px; transition:width .3s; }
        .progress-fill.empty { width:0; }
        .progress-label { font-size:10.5px; font-weight:600; white-space:nowrap; }
        .progress-label.muted { color:#c0a9a5; font-weight:500; }

        .empty { text-align:center; padding:60px 20px; color:#a98e8a; background:#fff; border:1px solid #f4e7e4; border-radius:14px; }
        .empty i { font-size:36px; color:#e5c9c4; display:block; margin-bottom:12px; }

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

{{-- Brand maroon throughout. The stored subjects.color values are a mixed
     palette (blue, green, orange…) that clashed with the rest of the UI. --}}
@php $color = '#7B1D1D'; @endphp

<main class="main-content">
    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="top-bar-left">
            <div>
                <div class="breadcrumb">
                    <a href="{{ route('subjects') }}"><i class="fas fa-arrow-left"></i> Resources</a>
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
                    <a href="#" class="js-open-profile-modal"><i class="fas fa-user"></i> Profile Settings</a>
                    <a href="#"><i class="fas fa-chart-line"></i> My Progress</a>
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

    @php
        $heroHasAttempts = $overallAccuracy !== null;
        $heroColor = $heroHasAttempts
            ? ($overallAccuracy >= $subject->passing_threshold ? '#059669' : '#dc2626')
            : '#d1d5db';
    @endphp
    <div class="subject-hero" style="--s-base:{{ $theme['base'] }}; --s-dark:{{ $theme['dark'] }}; --s-pastel:{{ $theme['pastel'] }}; --s-soft:{{ $theme['soft'] }};">
        {{-- Illustration cluster in the corner, same language as the Class Quizzes header. --}}
        <div class="hero-illus" aria-hidden="true">
            <span class="blob"></span>
            <i class="fas fa-book-open i1"></i>
            <i class="fas {{ $subjectIcon }} i2"></i>
        </div>
        <div class="hero-top">
            <div class="hero-icon">
                <img src="{{ asset('images/' . $subject->code . '.png') }}" alt="{{ $subject->code }}">
            </div>
            <div class="hero-info">
                <div class="hero-kicker"><i class="fas fa-book"></i> Resources</div>
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
            @include('student.partials.topic-node', ['topics' => $topicTree, 'subject' => $subject, 'depth' => 0])
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

    @include('partials.alerts')
</body>
</html>
