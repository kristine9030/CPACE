<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Performance - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; --orange:#f59e0b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

        /* MAIN */
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }

        /* TOPBAR */
        .topbar { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:22px; }
        .topbar-left { display:flex; align-items:center; gap:12px; }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .page-title { font-size:26px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:white; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }

        /* STATS */
        .stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
        .stat-chip { background:white; border-radius:12px; padding:16px 18px; display:flex; align-items:center; gap:14px; }
        .chip-icon { width:38px; height:38px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:17px; flex-shrink:0; }
        .chip-num { font-size:22px; font-weight:700; color:#1a1a1a; line-height:1; }
        .chip-lbl { font-size:11px; color:#999; margin-top:2px; }

        /* FILTER BAR */
        .filter-bar { background:white; border-radius:12px; padding:16px 20px; margin-bottom:18px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
        .search-wrap { position:relative; }
        .search-wrap i.search-ico { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#aaa; font-size:13px; }
        .search-wrap input { font-family:'Poppins',sans-serif; font-size:13px; border:1px solid #e0e0e0; border-radius:8px; padding:8px 12px 8px 32px; color:#555; background:white; outline:none; width:220px; }
        .search-wrap input:focus { border-color:var(--primary); }
        .search-wrap .search-spin { position:absolute; right:10px; top:50%; transform:translateY(-50%); color:var(--primary); font-size:12px; display:none; }
        .search-wrap.loading .search-spin { display:block; }
        select { font-family:'Poppins',sans-serif; font-size:13px; border:1px solid #e0e0e0; border-radius:8px; padding:8px 12px; color:#555; background:white; outline:none; cursor:pointer; }
        select:focus { border-color:var(--primary); }
        .filter-divider { width:1px; height:28px; background:#e8e8e8; }
        .filter-label { font-size:12px; color:#888; font-weight:500; }

        /* DYNAMIC CONTENT (AJAX-swapped) */
        #perfStats, #perfBody { transition:opacity .15s ease; }
        #perfBody.loading { opacity:.45; pointer-events:none; }

        /* MAIN LAYOUT */
        .perf-layout { display:grid; grid-template-columns:1fr 300px; gap:18px; align-items:start; }

        /* TABLE CARD */
        .table-card { background:white; border-radius:14px; overflow:hidden; }
        .table-head-bar { padding:16px 20px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f5f5f5; }
        .count { font-size:13px; color:#888; }

        table { width:100%; border-collapse:collapse; }
        thead th { text-align:left; font-size:11px; color:#aaa; font-weight:600; padding:12px 16px; text-transform:uppercase; letter-spacing:.4px; border-bottom:1px solid #f5f5f5; background:#fafafa; }
        thead th:first-child { padding-left:20px; }
        tbody tr { border-bottom:1px solid #f8f8f8; transition:background .15s; }
        tbody tr:last-child { border-bottom:none; }
        tbody tr:hover { background:#fafafa; }
        tbody td { padding:13px 16px; font-size:13px; vertical-align:middle; }
        tbody td:first-child { padding-left:20px; }

        .student-cell { display:flex; align-items:center; gap:10px; }
        .student-av { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:white; flex-shrink:0; }
        .student-name { font-weight:600; color:#1a1a1a; font-size:13px; }
        .student-email { font-size:11px; color:#bbb; }

        .score-cell { display:flex; align-items:center; gap:10px; }
        .score-num { font-size:15px; font-weight:700; width:42px; }
        .score-bar-bg { flex:1; height:6px; background:#f0f0f0; border-radius:3px; overflow:hidden; }
        .score-bar-fill { height:100%; border-radius:3px; }

        .subj-dots { display:flex; gap:5px; flex-wrap:wrap; }
        .subj-dot { width:auto; min-width:24px; height:24px; padding:0 6px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:9px; font-weight:700; }

        .trend-badge { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:600; padding:3px 8px; border-radius:5px; }
        .t-up   { background:#d1fae5; color:#059669; }
        .t-down { background:#fde8e8; color:var(--accent); }
        .t-flat { background:#f3f4f6; color:#9ca3af; }

        .last-active { font-size:11px; color:#aaa; }

        .view-btn { display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:7px; font-size:11px; font-weight:600; color:var(--accent); background:var(--primary-light); border:none; cursor:pointer; font-family:'Poppins',sans-serif; transition:all .2s; text-decoration:none; }
        .view-btn:hover { background:#fbd5d5; }

        .empty-row td { text-align:center; color:#aaa; padding:40px 16px; font-size:13px; }

        /* PAGINATION */
        .pagination { padding:14px 20px; display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f5f5f5; }
        .pag-info { font-size:12px; color:#999; }
        .pag-btns { display:flex; gap:5px; }
        .pag-btn { min-width:30px; height:30px; padding:0 8px; border:1px solid #e0e0e0; background:white; border-radius:7px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:12px; color:#555; transition:all .2s; text-decoration:none; }
        .pag-btn.active { background:var(--primary); color:white; border-color:var(--primary); }
        .pag-btn:hover:not(.active):not(.disabled) { background:#f5f5f5; }
        .pag-btn.disabled { opacity:.4; pointer-events:none; }

        /* RIGHT PANEL */
        .right-panel { display:flex; flex-direction:column; gap:16px; }
        .side-card { background:white; border-radius:14px; padding:20px; }
        .side-title { font-size:13px; font-weight:700; color:#1a1a1a; margin-bottom:14px; }

        .at-risk-item { display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #f8f8f8; }
        .at-risk-item:last-child { border-bottom:none; }
        .at-risk-av { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:white; background:var(--accent); flex-shrink:0; }
        .at-risk-name { font-size:12px; font-weight:600; color:#1a1a1a; }
        .at-risk-score { font-size:11px; color:var(--accent); font-weight:700; }
        .at-risk-sub { font-size:10px; color:#aaa; }

        .weak-item { display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #f8f8f8; }
        .weak-item:last-child { border-bottom:none; }
        .weak-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0; }
        .weak-name { font-size:12px; font-weight:600; color:#1a1a1a; flex:1; }
        .weak-sub { font-size:10px; color:#bbb; font-weight:500; }
        .weak-rate { font-size:12px; font-weight:700; color:var(--accent); }

        .muted-empty { font-size:12px; color:#bbb; padding:6px 0; }

        /* FLASH */
        .flash { background:#d1fae5; color:#059669; padding:12px 18px; border-radius:10px; margin-bottom:16px; font-size:13px; font-weight:600; }

        /* MODAL */
        .modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.45); display:none; align-items:center; justify-content:center; z-index:1000; padding:20px; }
        .modal-overlay.open { display:flex; }
        .modal { background:white; border-radius:16px; width:100%; max-width:520px; max-height:88vh; overflow-y:auto; padding:24px; animation:fadeUp .25s ease both; }
        .modal-head { display:flex; align-items:center; gap:12px; margin-bottom:18px; }
        .modal-av { width:46px; height:46px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:15px; font-weight:700; color:white; flex-shrink:0; }
        .modal-name { font-size:16px; font-weight:700; color:#1a1a1a; }
        .modal-email { font-size:12px; color:#aaa; }
        .modal-close { margin-left:auto; background:#f4f5f7; border:none; width:32px; height:32px; border-radius:8px; cursor:pointer; color:#777; font-size:14px; }
        .modal-close:hover { background:#eceef1; }
        .modal-metrics { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:18px; }
        .mm { background:#f9f9fb; border-radius:10px; padding:12px; text-align:center; }
        .mm-num { font-size:18px; font-weight:700; color:#1a1a1a; }
        .mm-lbl { font-size:10px; color:#999; margin-top:2px; }
        .modal-sec-title { font-size:12px; font-weight:700; color:#1a1a1a; margin:14px 0 10px; }
        .subj-row { margin-bottom:10px; }
        .subj-row-top { display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px; }
        .subj-row-bar { height:6px; background:#f0f0f0; border-radius:4px; overflow:hidden; }
        .modal-weak-item { display:flex; justify-content:space-between; align-items:center; font-size:12px; padding:7px 10px; background:#fef2f2; border-radius:8px; margin-bottom:6px; }
        .modal-weak-item .wt { color:#7f1d1d; font-weight:600; }
        .modal-weak-item .wr { color:var(--accent); font-weight:700; }

        @keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
        .a0{animation:fadeUp .4s ease both} .a1{animation:fadeUp .4s .07s ease both} .a2{animation:fadeUp .4s .14s ease both}

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .stats-row { grid-template-columns: repeat(2, 1fr) !important; }
            .perf-layout { grid-template-columns: 1fr !important; }
            .table-card { overflow-x: auto; }
            table { min-width: 620px; }
            .filter-bar { flex-direction: column; align-items: stretch; gap: 10px; }
            .filter-divider { display: none; }
            .search-wrap input { width: 100%; }
            .pagination { flex-direction: column; gap: 10px; align-items: flex-start; }
            .topbar-right { flex-wrap: wrap; gap: 8px; }
        }

        @media (max-width: 480px) {
            .stats-row { grid-template-columns: 1fr !important; }
            .chip-num { font-size: 18px; }
            .score-bar-bg { display: none; }
        }
    </style>
</head>
<body>

@include('partials.faculty-sidebar', ['active' => 'performance'])

<main class="main">
    <div class="topbar a0">
        <div class="topbar-left">
            <div>
                <div class="page-title">Student Performance</div>
                <div class="page-sub">Monitor student progress and identify students that need help.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('faculty.performance.export', $activeQuery) }}" id="exportBtn" class="btn btn-ghost"><i class="fas fa-file-export"></i> Export</a>
            <form method="POST" action="{{ route('faculty.performance.remind') }}" id="sendReportForm" style="display:inline;" onsubmit="return confirm('Send a performance check-in to all listed students?');">
                @csrf
                <input type="hidden" name="scope" value="all">
                <button type="submit" class="btn btn-primary"><i class="fas fa-envelope"></i> Send Report</button>
            </form>
            @include('partials.topbar-actions')
        </div>
    </div>

    @if(session('status'))
        <div class="flash"><i class="fas fa-check-circle"></i> {{ session('status') }}</div>
    @endif

    <!-- STATS (above the filter bar) -->
    @include('faculty.partials.performance-stats')

    <!-- FILTER BAR -->
    <form method="GET" action="{{ route('faculty.performance') }}" id="filterForm" class="filter-bar">
        <div class="search-wrap" id="searchWrap">
            <i class="fas fa-search search-ico"></i>
            <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search students..." autocomplete="off">
            <i class="fas fa-spinner fa-spin search-spin"></i>
        </div>
        <div class="filter-divider"></div>
        <span class="filter-label">Subject</span>
        <select name="subject">
            <option value="">All Subjects</option>
            @foreach($subjects as $s)
                <option value="{{ $s->id }}" @selected($filters['subject'] === $s->id)>{{ $s->code }}</option>
            @endforeach
        </select>
        <span class="filter-label">Period</span>
        <select name="period">
            <option value="30"  @selected($filters['period']==='30')>Last 30 Days</option>
            <option value="7"   @selected($filters['period']==='7')>Last 7 Days</option>
            <option value="90"  @selected($filters['period']==='90')>Last 3 Months</option>
            <option value="all" @selected($filters['period']==='all')>All Time</option>
        </select>
        <span class="filter-label">Sort by</span>
        <select name="sort">
            <option value="score_desc" @selected($filters['sort']==='score_desc')>Avg Score (Desc)</option>
            <option value="score_asc"  @selected($filters['sort']==='score_asc')>Avg Score (Asc)</option>
            <option value="active"     @selected($filters['sort']==='active')>Most Active</option>
            <option value="name"       @selected($filters['sort']==='name')>Name A-Z</option>
        </select>
    </form>

    <!-- DYNAMIC BODY (table + side panels, swapped in place via AJAX) -->
    @include('faculty.partials.performance-body')
</main>

<!-- STUDENT DETAIL MODAL -->
<div class="modal-overlay" id="studentModal" onclick="if(event.target===this)closeStudent()">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-av" id="mAv"></div>
            <div>
                <div class="modal-name" id="mName"></div>
                <div class="modal-email" id="mEmail"></div>
            </div>
            <button class="modal-close" onclick="closeStudent()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-metrics">
            <div class="mm"><div class="mm-num" id="mScore"></div><div class="mm-lbl">Avg Score</div></div>
            <div class="mm"><div class="mm-num" id="mQuizzes"></div><div class="mm-lbl">Quizzes</div></div>
            <div class="mm"><div class="mm-num" id="mAttempted"></div><div class="mm-lbl">Questions</div></div>
        </div>
        <div class="modal-sec-title">Accuracy by Subject</div>
        <div id="mSubjects"></div>
        <div class="modal-sec-title">Weak Topics</div>
        <div id="mWeak"></div>
    </div>
</div>

<script>
    const PERF_URL        = "{{ route('faculty.performance') }}";
    const PERF_EXPORT_URL = "{{ route('faculty.performance.export') }}";

    // Per-student data for the detail modal - re-read after each AJAX swap.
    let STUDENTS = {};
    let DETAILS  = {};

    function hydratePerf() {
        const el = document.getElementById('perfData');
        if (!el) return;
        try {
            const data = JSON.parse(el.textContent);
            STUDENTS = data.students || {};
            DETAILS  = data.details  || {};
        } catch (e) { STUDENTS = {}; DETAILS = {}; }
    }

    function scoreColor(s) { return s >= 75 ? '#059669' : (s >= 60 ? '#d97706' : '#c0392b'); }
    function barColor(s)   { return s >= 75 ? '#10b981' : (s >= 60 ? '#f59e0b' : '#c0392b'); }

    function openStudent(id) {
        const s = STUDENTS[id];
        const d = DETAILS[id] || { subjects: [], weak: [] };
        if (!s) return;

        document.getElementById('mAv').textContent = s.initials;
        document.getElementById('mAv').style.background = s.color;
        document.getElementById('mName').textContent = s.name;
        document.getElementById('mEmail').textContent = s.email;
        document.getElementById('mScore').textContent = s.score + '%';
        document.getElementById('mScore').style.color = scoreColor(s.score);
        document.getElementById('mQuizzes').textContent = s.quizzes;
        document.getElementById('mAttempted').textContent = s.attempted;

        const subjEl = document.getElementById('mSubjects');
        if (!d.subjects.length) {
            subjEl.innerHTML = '<div class="muted-empty">No topic data recorded yet.</div>';
        } else {
            subjEl.innerHTML = d.subjects.map(sub => `
                <div class="subj-row">
                    <div class="subj-row-top"><span style="color:#555;">${sub.code} <span style="color:#bbb;">(${sub.attempts} attempts)</span></span><span style="font-weight:700;color:${scoreColor(sub.accuracy)};">${sub.accuracy}%</span></div>
                    <div class="subj-row-bar"><div style="height:100%;border-radius:4px;width:${sub.accuracy}%;background:${barColor(sub.accuracy)};"></div></div>
                </div>`).join('');
        }

        const weakEl = document.getElementById('mWeak');
        if (!d.weak.length) {
            weakEl.innerHTML = '<div class="muted-empty"><i class="fas fa-check-circle" style="color:#10b981;margin-right:5px;"></i>No weak topics — on track.</div>';
        } else {
            weakEl.innerHTML = d.weak.map(w => `
                <div class="modal-weak-item"><span class="wt">${w.topic} <span style="color:#c89;font-weight:500;">(${w.subject})</span></span><span class="wr">${w.accuracy}%</span></div>`).join('');
        }

        document.getElementById('studentModal').classList.add('open');
    }
    function closeStudent() { document.getElementById('studentModal').classList.remove('open'); }

    // ── AJAX live filtering / sorting / pagination ─────────────────────────
    const filterForm  = document.getElementById('filterForm');
    const searchInput = filterForm.querySelector('input[name="search"]');
    const searchWrap  = document.getElementById('searchWrap');
    let   searchTimer;

    // Build the request URL from the current filter controls (page resets to 1).
    function currentUrl() {
        const params = new URLSearchParams();
        new FormData(filterForm).forEach((v, k) => { if (v !== '') params.set(k, v); });
        const qs = params.toString();
        return PERF_URL + (qs ? ('?' + qs) : '');
    }

    function loadPerf(url, push = true) {
        // Subtle, flicker-free loading hint: only the search-box spinner. We do
        // NOT dim or re-animate the regions, so the data swaps in seamlessly.
        searchWrap.classList.add('loading');

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                // Parse the response and swap the stats + body regions in place,
                // leaving the filter bar (and the focused search box) untouched.
                const tmp = document.createElement('div');
                tmp.innerHTML = html;
                const newStats = tmp.querySelector('#perfStats');
                const newBody  = tmp.querySelector('#perfBody');
                // Strip the entry-animation classes so the swap doesn't replay
                // the fade/slide (that was the flicker) - animate first load only.
                if (newStats) { newStats.classList.remove('a1'); document.getElementById('perfStats').replaceWith(newStats); }
                if (newBody)  { newBody.classList.remove('a2');  document.getElementById('perfBody').replaceWith(newBody); }
                hydratePerf();
                syncTopbar(url);
                if (push) history.pushState({ url }, '', url);
            })
            .catch(() => {})
            .finally(() => searchWrap.classList.remove('loading'));
    }

    // Keep the Export link + Send Report form in sync with the live filters.
    function syncTopbar(url) {
        const u = new URL(url, window.location.origin);
        u.searchParams.delete('page');
        const search = u.search;

        document.getElementById('exportBtn').href = PERF_EXPORT_URL + search;

        const form = document.getElementById('sendReportForm');
        form.querySelectorAll('[data-filter]').forEach(n => n.remove());
        const scope = form.querySelector('[name="scope"]');
        u.searchParams.forEach((v, k) => {
            const i = document.createElement('input');
            i.type = 'hidden'; i.name = k; i.value = v; i.dataset.filter = '1';
            form.insertBefore(i, scope);
        });
    }

    // Selects: instant. Search: debounced (real-time as you type).
    filterForm.addEventListener('change', e => {
        if (e.target.name !== 'search') loadPerf(currentUrl());
    });
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadPerf(currentUrl()), 300);
    });
    searchInput.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); clearTimeout(searchTimer); loadPerf(currentUrl()); }
    });

    // Pagination links inside the swapped body (document-level delegation, so
    // it keeps working after the body element is replaced).
    document.addEventListener('click', e => {
        const a = e.target.closest('#perfBody .pag-btn');
        if (a && !a.classList.contains('disabled')) { e.preventDefault(); loadPerf(a.href); }
    });

    // Back / forward buttons.
    window.addEventListener('popstate', () => loadPerf(window.location.href, false));

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeStudent(); });

    // Initial hydrate + sync the topbar to the first-load filters.
    hydratePerf();
    syncTopbar(window.location.href);
</script>
</body>
</html>
