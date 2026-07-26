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
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent-red:#c0392b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

        .main-content { margin-left:220px; padding:30px 40px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main-content { margin-left:70px; }

        .breadcrumb { display:flex; align-items:center; gap:8px; font-size:13px; color:#999; margin-bottom:18px; }
        .breadcrumb a { color:var(--primary); text-decoration:none; font-weight:500; }
        .breadcrumb a:hover { text-decoration:underline; }

        .subject-hero {
            display:flex; flex-direction:column; gap:20px;
            background:#fff; border-radius:16px; padding:24px 28px; margin-bottom:26px;
            box-shadow:0 2px 10px rgba(0,0,0,.05);
        }
        .hero-top { display:flex; align-items:center; gap:20px; }
        .hero-icon { width:70px; height:70px; border-radius:18px; display:flex; align-items:center; justify-content:center; font-size:28px; flex-shrink:0; }
        .hero-info h1 { font-size:24px; font-weight:800; color:#1a1a1a; }
        .hero-info p { font-size:13px; color:#888; margin-top:3px; }
        .hero-count { margin-left:auto; text-align:center; }
        .hero-count .n { font-size:26px; font-weight:800; color:var(--primary); }
        .hero-count .l { font-size:11px; color:#aaa; text-transform:uppercase; letter-spacing:.5px; }

        .hero-overall { border-top:1px solid #f2f2f2; padding-top:18px; }
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
            box-shadow:0 2px 8px rgba(0,0,0,.04); border:1px solid transparent;
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

        @media (max-width:768px) { .main-content { margin-left:0; padding:20px 16px; } .hero-count { display:none; } }
    </style>
</head>
<body>

@include('partials.sidebar', ['active' => 'subjects'])
@include('partials.student-bottom-nav', ['active' => 'subjects'])
@include('partials.student-mobile-header')

@php $color = $subject->color ?: '#7B1D1D'; @endphp

<main class="main-content">
    <div class="breadcrumb">
        <a href="{{ route('subjects') }}"><i class="fas fa-arrow-left"></i> Subjects</a>
        <span>/</span>
        <span>{{ $subject->code }}</span>
    </div>

    @php
        $heroHasAttempts = $overallAccuracy !== null;
        $heroColor = $heroHasAttempts
            ? ($overallAccuracy >= $subject->passing_threshold ? '#059669' : '#dc2626')
            : '#d1d5db';
    @endphp
    <div class="subject-hero">
        <div class="hero-top">
            <div class="hero-icon" style="background:{{ $color }}1a; color:{{ $color }};">
                <i class="fas {{ $subject->icon ?: 'fa-book' }}"></i>
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
</body>
</html>
