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

        /* MAIN */
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }

        /* TOPBAR */
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:16px; position:relative; z-index:100; }
        .topbar-left { display:flex; align-items:center; gap:12px; }
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

        /* EXPORT DROPDOWN */
        .export-wrap { position:relative; }
        .export-menu { position:absolute; top:calc(100% + 6px); right:0; background:white; border:1px solid #ececec; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.12); padding:6px; min-width:210px; z-index:50; display:none; }
        .export-menu.open { display:block; }
        .export-menu a { display:flex; align-items:center; gap:10px; padding:9px 12px; border-radius:7px; font-size:13px; color:#444; text-decoration:none; transition:background .15s; }
        .export-menu a:hover { background:#f5f5f5; }

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
        .ab-var { background:#ede9fe; color:#7c3aed; position:relative; text-decoration:none; width:auto; min-width:28px; padding:0 7px; gap:4px; }
        .ab-var:hover { background:#ddd6fe; }
        .var-count { font-size:11px; font-weight:700; }
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

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            /* stats: 4-col → 2-col */
            .stats-row { grid-template-columns: repeat(2, 1fr) !important; }
            /* filter bar: wrap all items vertically */
            .filter-bar { flex-direction: column; align-items: stretch; gap: 10px; }
            .filter-group { flex-wrap: wrap; }
            .filter-divider { display: none; }
            .search-inp { width: 100%; }
            .search-wrap-tb { width: 100%; }
            /* table: horizontal scroll */
            .table-card { overflow-x: auto; }
            table { min-width: 640px; }
            /* topbar buttons: wrap */
            .topbar-right { flex-wrap: wrap; gap: 8px; }
            /* bulk action bar: stack */
            .table-head-bar { flex-direction: column; align-items: flex-start; gap: 8px; }
            /* pagination: wrap */
            .pagination { flex-direction: column; gap: 10px; align-items: flex-start; }
        }

        @media (max-width: 480px) {
            .stats-row { grid-template-columns: 1fr !important; }
            .chip-num { font-size: 18px; }
            .btn { padding: 8px 12px; font-size: 12px; }
        }
    </style>
</head>
<body>

@include('partials.faculty-sidebar', ['active' => 'test-bank'])

<main class="main">
    <div class="topbar a0">
        <div class="topbar-left">
            <div>
                <div class="page-title">Test Bank</div>
                <div class="page-sub">Manage all questions across all subjects.</div>
            </div>
        </div>
        <div class="topbar-right">
            <button class="btn btn-ghost"><i class="fas fa-file-import"></i> Import</button>
            <div class="export-wrap">
                <button class="btn btn-ghost" id="exportBtn" type="button">
                    <i class="fas fa-file-export"></i> Export <i class="fas fa-chevron-down" style="font-size:10px;"></i>
                </button>
                <div class="export-menu" id="exportMenu">
                    <a href="#" data-format="csv"><i class="fas fa-file-csv" style="color:#059669;"></i> Export as CSV (Excel)</a>
                    <a href="#" data-format="json"><i class="fas fa-file-code" style="color:#2563eb;"></i> Export as JSON</a>
                    <a href="#" data-format="pdf" target="_blank"><i class="fas fa-file-pdf" style="color:#c0392b;"></i> Export as PDF</a>
                </div>
            </div>
            <a href="{{ route('faculty.question.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Question</a>
            @include('partials.topbar-actions')
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
            <input class="search-inp" id="searchInput" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search questions..." autocomplete="off">
        </div>
        <div class="filter-divider"></div>
        <div class="filter-group">
            <span class="filter-label">Subject</span>
            <select name="subject">
                <option value="">All Subjects</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ ($filters['subject'] ?? '') == $subject->id ? 'selected' : '' }}>{{ $subject->code }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <span class="filter-label">Type</span>
            <select name="type">
                <option value="">All Types</option>
                <option value="mcq" {{ ($filters['type'] ?? '') === 'mcq' ? 'selected' : '' }}>Multiple Choice</option>
                <option value="true_false" {{ ($filters['type'] ?? '') === 'true_false' ? 'selected' : '' }}>True / False</option>
            </select>
        </div>
        <div class="filter-group">
            <span class="filter-label">Difficulty</span>
            <select name="difficulty">
                <option value="">All Levels</option>
                <option value="easy" {{ ($filters['difficulty'] ?? '') === 'easy' ? 'selected' : '' }}>Easy</option>
                <option value="moderate" {{ ($filters['difficulty'] ?? '') === 'moderate' ? 'selected' : '' }}>Medium</option>
                <option value="difficult" {{ ($filters['difficulty'] ?? '') === 'difficult' ? 'selected' : '' }}>Hard</option>
            </select>
        </div>
        <div class="filter-group">
            <span class="filter-label">Status</span>
            <select name="status">
                <option value="">All Status</option>
                <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
        </div>
    </form>

    <!-- TABLE -->
    <div class="table-card a2" id="tableCard">
        @include('faculty.partials.test-bank-table')
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // Live search — only the table re-renders (no full-page reload). Filtering
    // runs server-side via AJAX so it spans all pages, not just the visible rows.
    const search = document.getElementById('searchInput');
    const form   = document.getElementById('filterForm');
    const card   = document.getElementById('tableCard');
    if (!search || !form || !card) return;

    let reqToken = 0;

    // Fetch the filtered table for the given URL and swap it into the card.
    async function loadTable(url, { push = true } = {}) {
        const token = ++reqToken;
        card.style.opacity = '.5';
        card.style.pointerEvents = 'none';
        try {
            const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const html = await res.text();
            if (token !== reqToken) return; // a newer request already won
            card.innerHTML = html;
            if (push) window.history.replaceState({}, '', url);
        } catch (e) {
            // Network hiccup — fall back to a normal navigation.
            window.location = url;
            return;
        } finally {
            if (token === reqToken) { card.style.opacity = ''; card.style.pointerEvents = ''; }
        }
    }

    // Build the request URL from the current filter form state.
    function filterUrl() {
        const params = new URLSearchParams(new FormData(form));
        return form.action + '?' + params.toString();
    }

    // Debounced live search as the user types.
    let timer;
    search.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => loadTable(filterUrl()), 300);
    });

    // Dropdown filters update the table immediately (replaces the old submit).
    form.querySelectorAll('select').forEach(sel => {
        sel.addEventListener('change', () => loadTable(filterUrl()));
    });

    // Don't full-reload when the search box's Enter key submits the form.
    form.addEventListener('submit', (e) => { e.preventDefault(); loadTable(filterUrl()); });

    // Pagination links inside the swapped-in table — intercept and AJAX them.
    card.addEventListener('click', (e) => {
        const link = e.target.closest('a.pag-btn');
        if (link && link.getAttribute('href')) { e.preventDefault(); loadTable(link.href); }
    });

    // ── EXPORT DROPDOWN ──
    // Each option exports the questions matching the *currently applied* filters
    // by reusing the live filter form state, just pointed at the export route.
    const exportBtn  = document.getElementById('exportBtn');
    const exportMenu = document.getElementById('exportMenu');
    const exportBase = @json(route('faculty.test-bank.export'));

    if (exportBtn && exportMenu) {
        exportBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            exportMenu.classList.toggle('open');
        });

        // Close the menu when clicking anywhere else.
        document.addEventListener('click', () => exportMenu.classList.remove('open'));

        // Build the export URL from the current filters + chosen format.
        exportMenu.querySelectorAll('a[data-format]').forEach(opt => {
            opt.addEventListener('click', (e) => {
                e.preventDefault();
                const params = new URLSearchParams(new FormData(form));
                params.set('format', opt.dataset.format);
                const url = exportBase + '?' + params.toString();
                if (opt.dataset.format === 'pdf') {
                    window.open(url, '_blank');     // printable view in a new tab
                } else {
                    window.location = url;          // triggers the file download
                }
                exportMenu.classList.remove('open');
            });
        });
    }
});
</script>
</body>
</html>
