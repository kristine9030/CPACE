<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Bank - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; --orange:#f59e0b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

        /* SIDEBAR — same as dashboard */
        .sidebar { background:var(--primary); position:fixed; width:230px; height:100vh; display:flex; flex-direction:column; overflow-y:auto; overflow-x:hidden; z-index:1000; transition:width .3s; }
        .sidebar.collapsed { width:70px; }
        .sidebar-header { padding:20px 18px 16px; border-bottom:1px solid rgba(255,255,255,.12); }
        .sidebar-brand { display:flex; align-items:center; gap:10px; }
        .brand-circle { width:42px; height:42px; flex-shrink:0; background:rgba(255,255,255,.15); border:2px solid rgba(255,255,255,.3); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px; color:white; }
        .brand-text strong { display:block; font-size:14px; font-weight:700; color:white; }
        .brand-text small { font-size:10px; color:rgba(255,255,255,.7); }
        .faculty-badge { display:inline-block; margin-top:6px; background:rgba(255,255,255,.2); color:white; font-size:9px; font-weight:700; padding:2px 8px; border-radius:20px; letter-spacing:1px; text-transform:uppercase; }
        .sidebar.collapsed .brand-text, .sidebar.collapsed .faculty-badge { display:none; }
        .sidebar-nav { list-style:none; flex:1; padding:10px 0; }
        .sidebar-nav .nav-group-label { padding:14px 22px 4px; font-size:9px; font-weight:700; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:1.2px; }
        .sidebar.collapsed .nav-group-label { display:none; }
        .sidebar-nav li a { display:flex; align-items:center; gap:12px; padding:10px 22px; color:rgba(255,255,255,.72); text-decoration:none; font-size:13px; border-left:3px solid transparent; transition:all .2s; }
        .sidebar-nav li a:hover { color:white; background:rgba(255,255,255,.1); }
        .sidebar-nav li a.active { color:white; background:rgba(255,255,255,.18); border-left-color:white; font-weight:500; }
        .sidebar-nav li a i { width:18px; text-align:center; font-size:14px; flex-shrink:0; }
        .sidebar.collapsed .sidebar-nav li a { padding:10px 0; justify-content:center; gap:0; }
        .sidebar.collapsed .sidebar-nav li a span { display:none; }
        .nav-badge { margin-left:auto; background:var(--accent); color:white; font-size:9px; font-weight:700; padding:2px 6px; border-radius:20px; min-width:18px; text-align:center; }
        .sidebar.collapsed .nav-badge { display:none; }
        .sidebar-footer { border-top:1px solid rgba(255,255,255,.12); padding:14px 18px; }
        .user-row { display:flex; align-items:center; gap:10px; }
        .user-av { width:36px; height:36px; border-radius:50%; background:var(--accent); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; color:white; flex-shrink:0; }
        .user-info { flex:1; min-width:0; }
        .user-info .un { display:block; font-size:12px; font-weight:600; color:white; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .user-info .ur { display:block; font-size:10px; color:rgba(255,255,255,.6); }
        .sidebar.collapsed .user-info, .sidebar.collapsed .user-chevron { display:none; }

        /* MAIN */
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }

        /* TOPBAR */
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:16px; }
        .topbar-left { display:flex; align-items:center; gap:12px; }
        .toggle-btn { width:36px; height:36px; border:1px solid #ddd; background:white; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--primary); font-size:15px; }
        .page-title { font-size:26px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-outline { background:white; color:var(--primary); border:1.5px solid var(--primary); }
        .btn-outline:hover { background:var(--primary-light); }
        .btn-ghost { background:white; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }

        /* STATS */
        .stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
        .stat-chip { background:white; border-radius:12px; padding:16px 18px; display:flex; align-items:center; gap:14px; }
        .chip-icon { width:38px; height:38px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:17px; flex-shrink:0; }
        .chip-num { font-size:22px; font-weight:700; color:#1a1a1a; line-height:1; }
        .chip-lbl { font-size:11px; color:#999; margin-top:2px; }

        /* FILTERS */
        .filter-bar { background:white; border-radius:12px; padding:16px 20px; margin-bottom:18px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
        .filter-group { display:flex; align-items:center; gap:8px; }
        .filter-label { font-size:12px; color:#888; font-weight:500; white-space:nowrap; }
        select, .search-inp { font-family:'Poppins',sans-serif; font-size:13px; border:1px solid #e0e0e0; border-radius:8px; padding:8px 12px; color:#555; background:white; outline:none; }
        select:focus, .search-inp:focus { border-color:var(--primary); }
        .search-inp { width:240px; }
        .search-wrap-tb { position:relative; }
        .search-wrap-tb i { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#aaa; font-size:13px; }
        .search-wrap-tb .search-inp { padding-left:32px; }
        .filter-divider { width:1px; height:28px; background:#e8e8e8; }

        /* TABLE CARD */
        .table-card { background:white; border-radius:14px; overflow:hidden; }
        .table-head-bar { padding:16px 20px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f5f5f5; }
        .table-head-bar .count { font-size:13px; color:#888; }
        .bulk-actions { display:flex; gap:8px; }
        .bulk-btn { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:7px; font-size:12px; font-weight:600; cursor:pointer; border:none; font-family:'Poppins',sans-serif; transition:all .2s; }
        .bulk-del { background:#fde8e8; color:var(--accent); }
        .bulk-exp { background:#dbeafe; color:var(--blue); }

        table { width:100%; border-collapse:collapse; }
        thead th { text-align:left; font-size:11px; color:#aaa; font-weight:600; padding:12px 16px; text-transform:uppercase; letter-spacing:.4px; border-bottom:1px solid #f5f5f5; background:#fafafa; }
        thead th:first-child { padding-left:20px; }
        tbody tr { border-bottom:1px solid #f8f8f8; transition:background .15s; }
        tbody tr:last-child { border-bottom:none; }
        tbody tr:hover { background:#fafafa; }
        tbody td { padding:14px 16px; font-size:13px; vertical-align:middle; }
        tbody td:first-child { padding-left:20px; }

        .q-text { font-weight:600; color:#1a1a1a; margin-bottom:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:300px; }
        .q-meta { font-size:11px; color:#bbb; }

        .subj-badge { display:inline-block; padding:3px 9px; border-radius:5px; font-size:10px; font-weight:700; }
        .b-far  { background:#dbeafe; color:#2563eb; }
        .b-aud  { background:#fce7f3; color:#db2777; }
        .b-tax  { background:#d1fae5; color:#059669; }
        .b-ms   { background:#ede9fe; color:#7c3aed; }
        .b-rfbt { background:#fef3c7; color:#d97706; }
        .b-afar { background:#cffafe; color:#0891b2; }

        .diff-badge { display:inline-block; padding:3px 9px; border-radius:5px; font-size:10px; font-weight:600; }
        .d-easy   { background:#d1fae5; color:#059669; }
        .d-medium { background:#fef3c7; color:#d97706; }
        .d-hard   { background:#fde8e8; color:var(--accent); }

        .type-badge { display:inline-block; padding:3px 9px; border-radius:5px; font-size:10px; font-weight:600; background:#f3f4f6; color:#6b7280; }

        .status-pill { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; }
        .sp-active { background:#d1fae5; color:#059669; }
        .sp-draft  { background:#f3f4f6; color:#9ca3af; }

        .action-btn { width:28px; height:28px; border:none; border-radius:6px; cursor:pointer; font-size:12px; display:inline-flex; align-items:center; justify-content:center; transition:all .2s; }
        .ab-edit { background:#dbeafe; color:var(--blue); }
        .ab-dup  { background:#d1fae5; color:#059669; }
        .ab-del  { background:#fde8e8; color:var(--accent); }
        .ab-edit:hover { background:#bfdbfe; }
        .ab-dup:hover  { background:#a7f3d0; }
        .ab-del:hover  { background:#fecaca; }

        /* PAGINATION */
        .pagination { padding:16px 20px; display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f5f5f5; }
        .pag-info { font-size:12px; color:#999; }
        .pag-btns { display:flex; gap:6px; }
        .pag-btn { width:32px; height:32px; border:1px solid #e0e0e0; background:white; border-radius:7px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:12px; color:#555; transition:all .2s; }
        .pag-btn.active { background:var(--primary); color:white; border-color:var(--primary); }
        .pag-btn:hover:not(.active) { background:#f5f5f5; }

        @keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
        .a0{animation:fadeUp .4s ease both} .a1{animation:fadeUp .4s .07s ease both} .a2{animation:fadeUp .4s .14s ease both}
    </style>
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-circle"><i class="fas fa-shield-alt"></i></div>
            <div class="brand-text"><strong>CPACE</strong><small>CPA Reviewer</small></div>
        </div>
        <div class="faculty-badge">Faculty Portal</div>
    </div>
    <ul class="sidebar-nav">
        <li class="nav-group-label">Main</li>
        <li><a href="{{ route('faculty.dashboard') }}"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
        <li class="nav-group-label">Content</li>
        <li><a href="{{ route('faculty.test-bank') }}" class="active"><i class="fas fa-database"></i><span>Test Bank</span><span class="nav-badge">{{ number_format($stats['total']) }}</span></a></li>
        <li><a href="{{ route('faculty.question.create') }}"><i class="fas fa-plus-circle"></i><span>Add Question</span></a></li>
        <li><a href="{{ route('faculty.subjects') }}"><i class="fas fa-book-open"></i><span>Subjects &amp; Topics</span></a></li>
        <li class="nav-group-label">Analytics</li>
        <li><a href="{{ route('faculty.performance') }}"><i class="fas fa-users"></i><span>Student Performance</span></a></li>
        <li><a href="#"><i class="fas fa-chart-line"></i><span>Reports</span></a></li>
        <li class="nav-group-label">System</li>
        <li><a href="#"><i class="fas fa-cog"></i><span>Settings</span></a></li>
        <li><a href="{{ route('dashboard') }}"><i class="fas fa-arrow-left"></i><span>Student View</span></a></li>
    </ul>
    <div class="sidebar-footer">
        <div class="user-row">
            <div class="user-av">KD</div>
            <div class="user-info">
                <span class="un">{{ Auth::user()->name }}</span>
                <span class="ur">Faculty</span>
            </div>
            <i class="fas fa-chevron-down user-chevron" style="color:rgba(255,255,255,.5);font-size:10px;"></i>
        </div>
    </div>
</aside>

<main class="main">
    <div class="topbar a0">
        <div class="topbar-left">
            <button class="toggle-btn" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <div>
                <div class="page-title">Test Bank</div>
                <div class="page-sub">Manage all questions across all subjects.</div>
            </div>
        </div>
        <div class="topbar-right">
            <button class="btn btn-ghost"><i class="fas fa-file-import"></i> Import</button>
            <button class="btn btn-ghost"><i class="fas fa-file-export"></i> Export</button>
            <a href="{{ route('faculty.question.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Question</a>
        </div>
    </div>

    @if(session('status'))
        <div style="background:#d1fae5;color:#059669;padding:12px 18px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:600;">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
    @endif

    <!-- STATS -->
    <div class="stats-row a1">
        <div class="stat-chip">
            <div class="chip-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-database"></i></div>
            <div><div class="chip-num">{{ number_format($stats['total']) }}</div><div class="chip-lbl">Total Questions</div></div>
        </div>
        <div class="stat-chip">
            <div class="chip-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-check-circle"></i></div>
            <div><div class="chip-num">{{ number_format($stats['active']) }}</div><div class="chip-lbl">Active</div></div>
        </div>
        <div class="stat-chip">
            <div class="chip-icon" style="background:#f3f4f6;color:#9ca3af;"><i class="fas fa-file-alt"></i></div>
            <div><div class="chip-num">{{ number_format($stats['draft']) }}</div><div class="chip-lbl">Drafts</div></div>
        </div>
        <div class="stat-chip">
            <div class="chip-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-clock"></i></div>
            <div><div class="chip-num">{{ number_format($stats['this_week']) }}</div><div class="chip-lbl">Added this week</div></div>
        </div>
    </div>

    <!-- FILTERS -->
    <form method="GET" action="{{ route('faculty.test-bank') }}" class="filter-bar a1" id="filterForm">
        <div class="search-wrap-tb">
            <i class="fas fa-search"></i>
            <input class="search-inp" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search questions...">
        </div>
        <div class="filter-divider"></div>
        <div class="filter-group">
            <span class="filter-label">Subject</span>
            <select name="subject" onchange="document.getElementById('filterForm').submit()">
                <option value="">All Subjects</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ ($filters['subject'] ?? '') == $subject->id ? 'selected' : '' }}>{{ $subject->code }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <span class="filter-label">Type</span>
            <select name="type" onchange="document.getElementById('filterForm').submit()">
                <option value="">All Types</option>
                <option value="mcq" {{ ($filters['type'] ?? '') === 'mcq' ? 'selected' : '' }}>Multiple Choice</option>
                <option value="true_false" {{ ($filters['type'] ?? '') === 'true_false' ? 'selected' : '' }}>True / False</option>
            </select>
        </div>
        <div class="filter-group">
            <span class="filter-label">Difficulty</span>
            <select name="difficulty" onchange="document.getElementById('filterForm').submit()">
                <option value="">All Levels</option>
                <option value="easy" {{ ($filters['difficulty'] ?? '') === 'easy' ? 'selected' : '' }}>Easy</option>
                <option value="moderate" {{ ($filters['difficulty'] ?? '') === 'moderate' ? 'selected' : '' }}>Medium</option>
                <option value="difficult" {{ ($filters['difficulty'] ?? '') === 'difficult' ? 'selected' : '' }}>Hard</option>
            </select>
        </div>
        <div class="filter-group">
            <span class="filter-label">Status</span>
            <select name="status" onchange="document.getElementById('filterForm').submit()">
                <option value="">All Status</option>
                <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
        </div>
    </form>

    <!-- TABLE -->
    <div class="table-card a2">
        <div class="table-head-bar">
            <span class="count">Showing {{ $questions->firstItem() ?? 0 }}–{{ $questions->lastItem() ?? 0 }} of <strong>{{ number_format($questions->total()) }}</strong> questions</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Question</th>
                    <th>Subject</th>
                    <th>Topic</th>
                    <th>Type</th>
                    <th>Difficulty</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                $subjectClass = ['FAR'=>'b-far','AUD'=>'b-aud','TAX'=>'b-tax','MS'=>'b-ms','RFBT'=>'b-rfbt','AFAR'=>'b-afar'];
                $diffClass = ['easy'=>'d-easy','moderate'=>'d-medium','difficult'=>'d-hard'];
                $diffLabel = ['easy'=>'Easy','moderate'=>'Medium','difficult'=>'Hard'];
                $typeLabel = ['mcq'=>'Multiple Choice','true_false'=>'True / False'];
                @endphp

                @forelse($questions as $q)
                <tr>
                    <td style="color:#aaa;font-size:12px;">{{ $q->id }}</td>
                    <td>
                        <div class="q-text">{{ \Illuminate\Support\Str::limit($q->question_text, 70) }}</div>
                        <div class="q-meta">{{ $diffLabel[$q->difficulty] }} difficulty</div>
                    </td>
                    <td><span class="subj-badge {{ $subjectClass[$q->subject_code] ?? 'b-far' }}">{{ $q->subject_code }}</span></td>
                    <td style="font-size:12px;color:#666;">{{ $q->topic_name }}</td>
                    <td><span class="type-badge">{{ $typeLabel[$q->question_type] ?? $q->question_type }}</span></td>
                    <td><span class="diff-badge {{ $diffClass[$q->difficulty] }}">{{ $diffLabel[$q->difficulty] }}</span></td>
                    <td>
                        @if($q->is_active)
                            <span class="status-pill sp-active"><i class="fas fa-circle" style="font-size:6px;"></i> Active</span>
                        @else
                            <span class="status-pill sp-draft"><i class="fas fa-circle" style="font-size:6px;"></i> Draft</span>
                        @endif
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('faculty.question.edit', $q->id) }}" class="action-btn ab-edit" title="Edit"><i class="fas fa-pen"></i></a>
                        <form method="POST" action="{{ route('faculty.question.destroy', $q->id) }}" style="display:inline;" onsubmit="return confirm('Delete this question?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn ab-del" style="margin-left:4px;" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;color:#aaa;padding:40px;">
                        No questions yet. <a href="{{ route('faculty.question.create') }}" style="color:var(--accent);font-weight:600;">Add the first one</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">
            <span class="pag-info">Showing {{ $questions->firstItem() ?? 0 }}–{{ $questions->lastItem() ?? 0 }} of {{ number_format($questions->total()) }} results</span>
            <div class="pag-btns">
                @if($questions->onFirstPage())
                    <span class="pag-btn" style="opacity:.4;"><i class="fas fa-chevron-left"></i></span>
                @else
                    <a href="{{ $questions->previousPageUrl() }}" class="pag-btn"><i class="fas fa-chevron-left"></i></a>
                @endif
                <span class="pag-btn active">{{ $questions->currentPage() }}</span>
                @if($questions->hasMorePages())
                    <a href="{{ $questions->nextPageUrl() }}" class="pag-btn"><i class="fas fa-chevron-right"></i></a>
                @else
                    <span class="pag-btn" style="opacity:.4;"><i class="fas fa-chevron-right"></i></span>
                @endif
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('sidebarToggle');
    const sb  = document.getElementById('sidebar');
    if (btn) btn.addEventListener('click', () => { sb.classList.toggle('collapsed'); localStorage.setItem('facultySidebar', sb.classList.contains('collapsed')); });
    if (localStorage.getItem('facultySidebar') === 'true') sb.classList.add('collapsed');
});
</script>
</body>
</html>
