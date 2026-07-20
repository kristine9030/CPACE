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
            display:flex; align-items:center; gap:20px;
            background:#fff; border-radius:16px; padding:24px 28px; margin-bottom:26px;
            box-shadow:0 2px 10px rgba(0,0,0,.05);
        }
        .hero-icon { width:70px; height:70px; border-radius:18px; display:flex; align-items:center; justify-content:center; font-size:28px; flex-shrink:0; }
        .hero-info h1 { font-size:24px; font-weight:800; color:#1a1a1a; }
        .hero-info p { font-size:13px; color:#888; margin-top:3px; }
        .hero-count { margin-left:auto; text-align:center; }
        .hero-count .n { font-size:26px; font-weight:800; color:var(--primary); }
        .hero-count .l { font-size:11px; color:#aaa; text-transform:uppercase; letter-spacing:.5px; }

        .section-title { font-size:16px; font-weight:700; color:#333; margin-bottom:14px; }

        .topics-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; }
        .topic-card {
            display:flex; align-items:center; gap:16px;
            background:#fff; border-radius:14px; padding:18px 20px;
            text-decoration:none; color:inherit; box-shadow:0 2px 8px rgba(0,0,0,.04);
            transition:transform .2s, box-shadow .2s; border:1px solid transparent;
        }
        .topic-card:hover { transform:translateY(-3px); box-shadow:0 10px 22px rgba(0,0,0,.09); }
        .topic-num { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:15px; color:#fff; flex-shrink:0; }
        .topic-body { flex:1; min-width:0; }
        .topic-body .tt { font-size:14px; font-weight:600; color:#222; }
        .topic-body .ts { font-size:11.5px; color:#aaa; margin-top:4px; display:flex; gap:12px; flex-wrap:wrap; }
        .topic-body .ts span { display:inline-flex; align-items:center; gap:5px; }
        .topic-arrow { color:#ccc; font-size:15px; }
        .topic-card:hover .topic-arrow { color:var(--primary); }

        .empty { grid-column:1/-1; text-align:center; padding:60px 20px; color:#aaa; background:#fff; border-radius:14px; }
        .empty i { font-size:36px; color:#e5d5d5; display:block; margin-bottom:12px; }

        @media (max-width:768px) { .main-content { margin-left:0; padding:20px 16px; } .topics-grid { grid-template-columns:1fr; } .hero-count { display:none; } }
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

    <div class="subject-hero">
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

    <div class="section-title">Choose a topic to study</div>

    <div class="topics-grid">
        @forelse($topics as $i => $topic)
            <a href="{{ route('subjects.topic', ['subject' => $subject->id, 'topic' => $topic->id]) }}" class="topic-card">
                <div class="topic-num" style="background:{{ $color }};">{{ $i + 1 }}</div>
                <div class="topic-body">
                    <div class="tt">{{ $topic->name }}</div>
                    <div class="ts">
                        <span><i class="fas fa-folder-open"></i> {{ $topic->material_count }} material{{ $topic->material_count == 1 ? '' : 's' }}</span>
                        <span><i class="fas fa-circle-question"></i> {{ $topic->question_count }} questions</span>
                    </div>
                </div>
                <i class="fas fa-chevron-right topic-arrow"></i>
            </a>
        @empty
            <div class="empty">
                <i class="fas fa-inbox"></i>
                No topics available for this subject yet.
            </div>
        @endforelse
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar && localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
    }
});
</script>
</body>
</html>
