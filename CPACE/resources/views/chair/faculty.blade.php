<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── Modals ── */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:2000; align-items:center; justify-content:center; padding:20px; }
        .modal-overlay.open { display:flex; }
        .modal { background:#fff; border-radius:16px; width:100%; max-width:480px; padding:24px; max-height:90vh; overflow-y:auto; }
        #assignModal .modal { max-width:640px; }
        .modal h3 { font-size:16px; color:#1a1a1a; margin-bottom:4px; }
        .modal p.sub { font-size:12px; color:#999; margin-bottom:18px; }
        .modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:20px; }

        /* Same soft shadow as the Students page cards. */
        :root { --card-shadow: 0 1px 2px rgba(16,24,40,.04), 0 4px 14px rgba(16,24,40,.06); }
        .card, .kpi { box-shadow: var(--card-shadow); }

        /* ── Summary cards: icon, number, plain label, one-line explanation ── */
        .kpi-row { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:16px; margin-bottom:22px; }
        .kpi { display:flex; flex-direction:column; background:#fff; border-radius:16px; padding:20px 20px 14px; }
        .kpi-top { display:flex; align-items:center; gap:16px; flex:1; padding-bottom:16px; }
        .kpi-icon { width:52px; height:52px; border-radius:14px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:20px; }
        .t-rose { background:#fbe9ec; color:var(--primary); }
        .t-blue { background:#e0ecff; color:#2563eb; }
        .t-green { background:#dcf5ec; color:#059669; }
        .t-amber { background:#fdf0d5; color:#d97706; }
        .kpi-num { font-size:26px; font-weight:700; color:#1a1a1a; line-height:1.1; }
        .kpi-lbl { font-size:13px; font-weight:600; color:#444; margin-top:2px; }
        .kpi-foot { padding-top:12px; border-top:1px solid #f0f0f0; font-size:11px; color:#888; line-height:1.45; }
        .kpi-foot strong { font-weight:700; }
        @media (max-width:1050px) { .kpi-row { grid-template-columns:repeat(2, minmax(0, 1fr)); } }
        @media (max-width:640px) { .kpi-row { grid-template-columns:minmax(0, 1fr); } }

        /* ── List header: title + helper on the left, search and filter on the right ── */
        .faculty-toolbar { display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; margin-bottom:12px; }
        #facultyList { padding:16px 18px; }
        .list-title { font-size:15px; font-weight:700; color:#1a1a1a; }
        .list-title .count { color:#999; font-weight:500; }
        .faculty-toolbar-right { display:flex; align-items:center; gap:8px; flex-wrap:nowrap; }
        .faculty-search { position:relative; }
        .faculty-search i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#a3a8b0; font-size:12.5px; pointer-events:none; }
        /* Scoped under .faculty-toolbar: the sidebar partial's shared
           input[type=text]/select rules load after this file and would
           otherwise win (full width, 12px padding over the search icon). */
        .faculty-toolbar .faculty-search input, .faculty-toolbar .faculty-filter-select {
            height:36px; border:1px solid #e5e7eb; border-radius:10px; background-color:#fff;
            font-family:'Poppins',sans-serif; font-size:12.5px; color:#1a1a1a; transition:border-color .15s, box-shadow .15s;
        }
        .faculty-toolbar .faculty-search input { width:260px; padding:0 14px 0 38px; }
        .faculty-toolbar .faculty-filter-select {
            width:auto; min-width:190px; padding:0 36px 0 14px; cursor:pointer; appearance:none; -webkit-appearance:none;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat:no-repeat; background-position:right 13px center;
        }
        .faculty-toolbar .faculty-search input:hover, .faculty-toolbar .faculty-filter-select:hover { border-color:#d1d5db; }
        .faculty-toolbar .faculty-search input:focus, .faculty-toolbar .faculty-filter-select:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(123,29,29,.1); }
        .faculty-toolbar .faculty-filter-select.is-set { background-color:var(--primary-light); border-color:#ecc7cd; color:var(--primary); font-weight:600; }

        /* ── Pagination footer (client-side; rows are already all rendered) ── */
        .faculty-pagination { display:flex; justify-content:space-between; align-items:center; padding:14px 4px 2px; margin-top:6px; }
        .faculty-pag-info { font-size:11.5px; color:#999; }
        .faculty-pag-btns { display:flex; gap:5px; }
        .faculty-pag-btn { min-width:30px; height:30px; padding:0 8px; border:1px solid #e5e7eb; background:#fff; border-radius:8px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:12px; color:#555; transition:all .15s; font-family:'Poppins',sans-serif; }
        .faculty-pag-btn.active { background:var(--primary); color:#fff; border-color:var(--primary); }
        .faculty-pag-btn:hover:not(.active):not(:disabled) { background:#f5f5f5; }
        .faculty-pag-btn:disabled { opacity:.4; cursor:not-allowed; }
        @media (max-width:820px) { .faculty-toolbar-right { flex-wrap:wrap; width:100%; } .faculty-toolbar .faculty-search { flex:1 1 100%; } .faculty-toolbar .faculty-search input { width:100%; } .faculty-toolbar .faculty-filter-select { flex:1; } }
        @media (max-width:620px) { .faculty-pagination { flex-direction:column; gap:8px; align-items:flex-start; } }

        .otp-cell { display:flex; align-items:center; gap:6px; margin-top:4px; }
        .temp-pass { font-family:'Courier New', monospace; font-weight:700; color:#7B1D1D; background:#f8eaea; padding:2px 8px; border-radius:6px; font-size:11px; letter-spacing:.5px; }
        .otp-reveal, .copy-mini { background:none; border:none; color:#9ca3af; cursor:pointer; font-size:11px; font-family:'Poppins',sans-serif; padding:0; }
        .otp-reveal:hover, .copy-mini:hover { color:var(--primary); }

        .faculty-table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
        @media (max-width:768px) {
            .modal { padding:18px 16px; }
            .check-grid { grid-template-columns:minmax(0, 1fr) !important; }
        }
        @media (max-width:480px) {
            .modal-actions { flex-direction:column-reverse; }
            .modal-actions .btn { width:100%; justify-content:center; }
        }

        /* ── Faculty table: account + contribution in one row ── */
        .fac-table { table-layout:fixed; border-collapse:separate; border-spacing:0; min-width:980px; }
        .fac-table thead th {
            background:#f3f4f6; color:#4b5563; font-size:11px; font-weight:700; letter-spacing:.3px;
            padding:10px 12px; border-bottom:1px solid #e5e7eb; white-space:nowrap;
        }
        .fac-table thead th:first-child { border-top-left-radius:12px; }
        .th-tip { position:relative; display:inline-flex; align-items:center; gap:5px; cursor:help; }
        .th-tip i { font-size:10px; color:#9ca3af; }
        .th-tip::after {
            content:attr(data-tip); display:none; position:absolute; top:calc(100% + 10px); left:-6px; z-index:30;
            width:230px; white-space:normal; text-transform:none; letter-spacing:0; font-weight:400; font-size:11.5px; line-height:1.5;
            background:#1f2430; color:#f1f1f4; padding:10px 12px; border-radius:9px; box-shadow:0 10px 26px rgba(0,0,0,.2);
        }
        .th-tip.tip-right::after { left:auto; right:-6px; }
        .th-tip:hover::after, .th-tip:focus::after { display:block; }
        .th-tip:hover i { color:var(--primary); }
        .fac-table thead th:last-child { border-top-right-radius:12px; }
        .fac-table tbody tr { border-top:0; }
        .fac-table tbody td { padding:14px 12px; border-bottom:1px solid #f2f2f4; vertical-align:middle; }
        .fac-table tbody tr:hover { background:#fafafa; }
        .fac-table .col-actions { text-align:center; }
        .fac-table .col-check { text-align:center; padding-left:0; padding-right:0; }
        .fac-table input[type=checkbox] { width:15px; height:15px; accent-color:var(--primary); cursor:pointer; vertical-align:middle; }
        .fac-table tbody tr.is-selected { background:#fdf6f7; }

        /* ── Bulk actions bar ── */
        .bulk-bar { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:12px; padding:10px 14px; border-radius:12px; background:#fdf3f4; border:1px solid #f2d9dd; }
        .bulk-bar[hidden] { display:none; }
        .bulk-count { font-size:12.5px; font-weight:600; color:var(--primary); display:inline-flex; align-items:center; gap:8px; }
        .bulk-actions { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
        .bulk-btn { display:inline-flex; align-items:center; gap:7px; height:34px; padding:0 13px; border:1px solid #e5e7eb; border-radius:9px; background:#fff; color:#374151; font-family:'Poppins',sans-serif; font-size:12px; font-weight:600; cursor:pointer; transition:background .15s, border-color .15s; }
        .bulk-btn:hover { background:#f9fafb; border-color:#d1d5db; }
        .bulk-btn.danger { color:#b91c1c; border-color:#f5c2c2; }
        .bulk-btn.danger:hover { background:#fde8e8; }
        .bulk-clear { background:none; border:0; color:#888; font-family:'Poppins',sans-serif; font-size:12px; cursor:pointer; padding:0 6px; }
        .bulk-clear:hover { color:var(--primary); text-decoration:underline; }
        .fac-table .user-av { width:34px; height:34px; font-size:10px; flex-shrink:0; }
        .faculty-email { font-size:10px; color:#aaa; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .fac-table .faculty-name { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .fac-table .otp-cell { flex-wrap:wrap; row-gap:4px; }
        .muted-dash { color:#bbb; font-size:12px; }
        .last-contrib { font-size:11px; color:#666; }

        /* ── Row actions "..." menu (fixed-positioned so the table's scroll
           container doesn't clip it) ── */
        .row-menu { display:inline-block; }
        .row-dots { width:32px; height:32px; border:1px solid #e5e7eb; border-radius:9px; background:#fff; color:#4b5563; font-size:14px; cursor:pointer; transition:background .15s, border-color .15s, color .15s; }
        .row-dots:hover, .row-menu.open .row-dots { background:#f3f4f6; border-color:#d1d5db; color:#111827; }
        .row-dropdown { display:none; position:fixed; z-index:1500; min-width:200px; background:#fff; border-radius:12px; padding:6px; box-shadow:0 12px 32px rgba(16,24,40,.16); border:1px solid #f0f0f0; text-align:left; }
        .row-menu.open .row-dropdown { display:block; }
        .row-dropdown form { margin:0; }
        .row-dropdown a, .row-dropdown button { display:flex; align-items:center; gap:10px; width:100%; padding:9px 12px; border:0; background:none; border-radius:8px; font-family:'Poppins', sans-serif; font-size:12.5px; color:#333; cursor:pointer; text-align:left; text-decoration:none; }
        .row-dropdown a i, .row-dropdown button i { width:14px; color:#999; }
        .row-dropdown a:hover, .row-dropdown button:hover { background:#f6f6f7; }
        .row-dropdown button.danger, .row-dropdown button.danger i { color:#b91c1c; }

        /* ── Decision status chips used in the faculty table ── */
        .fstatus { display:inline-flex; align-items:center; gap:4px; padding:4px 8px; border-radius:14px; font-size:9.5px; font-weight:700; white-space:nowrap; }
        .fstatus.crit { background:#fde8e8; color:#b91c1c; }
        .fstatus.warn { background:#fef3c7; color:#b45309; }
        .fstatus.ok   { background:#d1fae5; color:#047857; }
        .fstatus.idle { background:#f3f4f6; color:#6b7280; }
        .fstatus-why { font-size:10px; color:#999; margin-top:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

        .faculty-cell { display:flex; align-items:center; gap:10px; }
        .faculty-name { font-size:12.5px; font-weight:600; color:#1a1a1a; }
        .metric-main { font-size:13px; font-weight:700; color:#222; }
        .metric-sub { font-size:10px; color:#aaa; margin-top:2px; }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'faculty'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Faculty</div>
                <div class="page-sub">Manage faculty accounts and subjects, and see how each one is building the question bank.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('chair.faculty.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Faculty</a>
            @include('partials.topbar-actions')
        </div>
    </div>

    {{-- Flash messages surface as SweetAlert popups via partials.alerts --}}

    @php
        $totalFaculty = $facultyRows->count();
        $nonContributors = $facultyRows->where('questions', 0)->count();
        $flagPct = $stats['questions'] > 0 ? (int) round($stats['flags'] / $stats['questions'] * 100) : 0;

        // A contribution older than this reads as stale for content authoring.
        $staleDays = 60;

        // One contribution status per faculty member for the table below.
        $facultyStatus = function (array $m) use ($staleDays) {
            $idleDays = $m['last_contribution'] ? (int) $m['last_contribution']->diffInDays(now()) : null;

            if (empty($m['subjects'])) {
                return ['key' => 'no_subjects', 'rank' => 1, 'tone' => 'crit', 'label' => 'No subjects yet', 'why' => 'Assign a subject so they can start writing questions.'];
            }
            if ($m['questions'] === 0) {
                return ['key' => 'never', 'rank' => 2, 'tone' => 'crit', 'label' => 'No questions yet', 'why' => 'Handles ' . implode(', ', $m['subjects']) . ' but hasn\'t written a question.'];
            }
            if ($m['quality_flags'] > 0) {
                return ['key' => 'quality', 'rank' => 3, 'tone' => 'warn', 'label' => $m['quality_flags'] . ' to review', 'why' => $m['quality_flags'] . ' question' . ($m['quality_flags'] === 1 ? ' is' : 's are') . ' unused, too easy, or too hard for students.'];
            }
            if ($idleDays !== null && $idleDays > $staleDays) {
                return ['key' => 'stale', 'rank' => 4, 'tone' => 'warn', 'label' => 'Idle ' . $idleDays . ' days', 'why' => 'No new or edited question in over ' . $staleDays . ' days.'];
            }
            return ['key' => 'ok', 'rank' => 5, 'tone' => 'ok', 'label' => 'On track', 'why' => $m['active'] . ' published question' . ($m['active'] === 1 ? '' : 's') . ', nothing to review.'];
        };

        $facultyWithStatus = $facultyRows->map(fn ($m) => $m + ['status' => $facultyStatus($m)]);
        $performanceById = $facultyWithStatus->keyBy('id');

        // Faculty table: account rows joined to their contribution figures,
        // what needs attention first, then by name.
        $facultyList = $faculty->sortBy(fn ($f) => [($performanceById[$f->id]['status']['rank'] ?? 9), mb_strtolower($f->name)])->values();
    @endphp

    <!-- Summary -->
    <div class="kpi-row">
        <div class="kpi">
            <div class="kpi-top">
                <div class="kpi-icon t-rose"><i class="fas fa-file-pen"></i></div>
                <div><div class="kpi-num">{{ number_format($stats['questions']) }}</div><div class="kpi-lbl">Questions Written</div></div>
            </div>
            <div class="kpi-foot">By <strong>{{ $stats['contributors'] }}</strong> of {{ $totalFaculty }} faculty, across all subjects.</div>
        </div>
        <div class="kpi">
            <div class="kpi-top">
                <div class="kpi-icon t-blue"><i class="fas fa-user-pen"></i></div>
                <div><div class="kpi-num">{{ $stats['contributors'] }} <span style="font-size:14px;color:#aaa;font-weight:600;">/ {{ $totalFaculty }}</span></div><div class="kpi-lbl">Faculty Writers</div></div>
            </div>
            <div class="kpi-foot">
                @if($nonContributors > 0)
                    <strong style="color:var(--accent);">{{ $nonContributors }}</strong> {{ $nonContributors === 1 ? "hasn't" : "haven't" }} written a question yet.
                @else
                    <strong style="color:#059669;">Everyone</strong> has written at least one question.
                @endif
            </div>
        </div>
        @php
            // Subjects with at least one faculty member actually writing questions.
            $uncovered = $subjectRows->where('contributors', 0)->pluck('code');
            $coveredCount = $subjectRows->count() - $uncovered->count();
        @endphp
        <div class="kpi">
            <div class="kpi-top">
                <div class="kpi-icon t-green"><i class="fas fa-book-open"></i></div>
                <div><div class="kpi-num">{{ $coveredCount }} <span style="font-size:14px;color:#aaa;font-weight:600;">/ {{ $subjectRows->count() }}</span></div><div class="kpi-lbl">Subjects Covered</div></div>
            </div>
            <div class="kpi-foot">
                @if($subjectRows->isEmpty())
                    No subjects set up yet.
                @elseif($uncovered->isNotEmpty())
                    No one is writing questions for <strong style="color:var(--accent);">{{ $uncovered->implode(', ') }}</strong> yet.
                @else
                    <strong style="color:#059669;">Every subject</strong> has at least one faculty member writing questions.
                @endif
            </div>
        </div>
        <div class="kpi">
            <div class="kpi-top">
                <div class="kpi-icon t-amber"><i class="fas fa-magnifying-glass"></i></div>
                <div><div class="kpi-num">{{ $stats['flags'] }}</div><div class="kpi-lbl">Questions to Review</div></div>
            </div>
            <div class="kpi-foot">
                @if($stats['flags'] > 0)
                    Unused, too easy, or too hard ({{ $flagPct }}% of all questions).
                @else
                    <strong style="color:#059669;">Nothing to review</strong> right now.
                @endif
            </div>
        </div>
    </div>

    <!-- One list: account, subjects, contribution, results, and actions -->
    <div class="card" id="facultyList">
        <div class="faculty-toolbar">
            <div>
                <div class="list-title">Faculty List <span class="count">({{ $faculty->count() }})</span></div>
            </div>
            <div class="faculty-toolbar-right">
                <div class="faculty-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="facultySearchInput" placeholder="Search by name or email" aria-label="Search faculty" autocomplete="off">
                </div>
                <select id="facultyFilter" class="faculty-filter-select" aria-label="Filter faculty">
                    <option value="">All faculty</option>
                    <optgroup label="Status">
                        <option value="status:active">Active</option>
                        <option value="status:pending">Setup Pending</option>
                        <option value="status:inactive">Inactive</option>
                    </optgroup>
                    <optgroup label="Question Writing">
                        <option value="contrib:attention">Needs follow-up</option>
                        <option value="contrib:ok">On track</option>
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="bulk-bar" id="facultyBulkBar" hidden>
            <span class="bulk-count"><i class="fas fa-square-check"></i> <span id="facultyBulkCount">0</span> selected</span>
            <div class="bulk-actions">
                <button type="button" class="bulk-btn" data-bulk="activate"><i class="fas fa-circle-check"></i> Activate</button>
                <button type="button" class="bulk-btn" data-bulk="deactivate"><i class="fas fa-power-off"></i> Deactivate</button>
                <button type="button" class="bulk-btn danger" data-bulk="delete"><i class="fas fa-trash-can"></i> Delete</button>
                <button type="button" class="bulk-clear" id="facultyBulkClear">Clear</button>
            </div>
        </div>
        <form id="facultyBulkForm" method="POST" action="{{ route('chair.faculty.bulk') }}" hidden>
            @csrf
            <input type="hidden" name="action" id="facultyBulkAction">
        </form>
        <div class="faculty-table-wrap">
        <table class="fac-table">
            <colgroup>
                <col style="width:46px;">
                <col>
                <col style="width:130px;">
                <col style="width:140px;">
                <col style="width:210px;">
                <col style="width:110px;">
                <col style="width:120px;">
                <col style="width:76px;">
            </colgroup>
            <thead>
                <tr>
                    <th class="col-check"><input type="checkbox" id="facultySelectAll" title="Select all faculty shown on this page" aria-label="Select all faculty shown"></th>
                    <th>Faculty</th>
                    <th>Subjects</th>
                    <th>Status</th>
                    <th><span class="th-tip" tabindex="0" data-tip="Whether they are writing questions for their subjects, and what to follow up on.">Question Writing <i class="fas fa-circle-info"></i></span></th>
                    <th><span class="th-tip" tabindex="0" data-tip="How often students answer this faculty member's questions correctly. Around 75% is the target.">Accuracy <i class="fas fa-circle-info"></i></span></th>
                    <th><span class="th-tip tip-right" tabindex="0" data-tip="When they last added or edited a question.">Last Active <i class="fas fa-circle-info"></i></span></th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody id="facultyTableBody">
            @forelse ($facultyList as $f)
                @php
                    $rowStatus = ! $f->is_active ? 'inactive' : ($f->setup_completed_at === null ? 'pending' : 'active');
                    $perf = $performanceById[$f->id] ?? null;
                    $contrib = $perf && $perf['status']['key'] === 'ok' ? 'ok' : 'attention';
                @endphp
                <tr class="faculty-row" data-name="{{ strtolower($f->name) }}" data-email="{{ strtolower($f->email) }}" data-status="{{ $rowStatus }}" data-contrib="{{ $contrib }}">
                    <td class="col-check"><input type="checkbox" class="faculty-select" value="{{ $f->id }}" data-name="{{ $f->name }}" aria-label="Select {{ $f->name }}"></td>
                    <td>
                        <div class="faculty-cell">
                            <div class="user-av">{{ strtoupper(substr($f->first_name,0,1)).strtoupper(substr($f->last_name,0,1)) }}</div>
                            <div style="min-width:0;">
                                <div class="faculty-name">{{ $f->name }}</div>
                                <div class="faculty-email">{{ $f->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @forelse ($f->assignedSubjects as $s)
                            <span class="subj-badge b-{{ strtolower($s->code) }}">{{ $s->code }}</span>
                        @empty
                            <span class="muted-dash">None assigned</span>
                        @endforelse
                    </td>
                    <td>
                        @if (! $f->is_active)
                            <span class="pill pill-off"><i class="fas fa-ban"></i> Inactive</span>
                        @elseif ($f->setup_completed_at === null)
                            <span class="pill pill-pending" title="Faculty hasn't changed their one-time password yet">
                                <i class="fas fa-hourglass-half"></i> Setup Pending
                            </span>
                            <div class="otp-cell">
                                <form method="POST" action="{{ route('chair.faculty.regenerate-otp', $f->id) }}"
                                      data-confirm="A new one-time password will be emailed to {{ $f->name }} ({{ $f->email }}). Any password they were given earlier stops working."
                                      data-confirm-title="Resend one-time password?"
                                      data-confirm-ok="Yes, resend"
                                      data-confirm-icon="question">
                                    @csrf
                                    <button type="submit" class="otp-reveal" title="Email a fresh one-time password to this faculty member">
                                        <i class="fas fa-paper-plane"></i> Resend OTP
                                    </button>
                                </form>
                                @if ($f->temp_password)
                                    <span class="temp-pass" title="Manual fallback if the email never arrives">{{ $f->temp_password }}</span>
                                    <button type="button" class="copy-mini" title="Copy" onclick="navigator.clipboard.writeText('{{ $f->temp_password }}')"><i class="fas fa-copy"></i></button>
                                @endif
                            </div>
                        @else
                            <span class="pill pill-on"><i class="fas fa-check"></i> Active</span>
                        @endif
                    </td>
                    <td>
                        @if ($perf)
                            <span class="fstatus {{ $perf['status']['tone'] }}">
                                @if($perf['status']['tone'] === 'ok')<i class="fas fa-check"></i>
                                @elseif($perf['status']['tone'] === 'crit')<i class="fas fa-triangle-exclamation"></i>
                                @else<i class="fas fa-circle-exclamation"></i>@endif
                                {{ $perf['status']['label'] }}
                            </span>
                            <div class="fstatus-why" title="{{ $perf['status']['why'] }}">{{ $perf['status']['why'] }}</div>
                        @else
                            <span class="muted-dash">—</span>
                        @endif
                    </td>
                    <td>
                        @php $acc = $perf['accuracy'] ?? null; @endphp
                        <div class="metric-main" style="color:{{ $acc === null ? '#aaa' : ($acc >= 75 ? '#047857' : ($acc >= 50 ? '#b45309' : '#b91c1c')) }};">{{ $acc === null ? '—' : $acc . '%' }}</div>
                        <div class="metric-sub">{{ number_format($perf['answered'] ?? 0) }} answer{{ ($perf['answered'] ?? 0) === 1 ? '' : 's' }}</div>
                    </td>
                    <td class="last-contrib">{{ ($perf['last_contribution'] ?? null) ? $perf['last_contribution']->diffForHumans() : 'None yet' }}</td>
                    <td class="col-actions">
                        <div class="row-menu">
                            <button type="button" class="row-dots" onclick="toggleRowMenu(event, this)" aria-label="Actions for {{ $f->name }}"><i class="fas fa-ellipsis"></i></button>
                            <div class="row-dropdown">
                                <a href="{{ route('chair.faculty.activity', $f->id) }}"><i class="fas fa-clock-rotate-left"></i> View activity</a>
                                <button type="button" data-assign-for="{{ $f->id }}"
                                        onclick="openAssign({{ $f->id }}, '{{ addslashes($f->name) }}', {{ $f->assignedSubjects->pluck('id')->toJson() }}, {{ $f->sectionsBySubject->toJson() }})">
                                    <i class="fas fa-layer-group"></i> Assign subjects
                                </button>
                                <a href="{{ route('chair.faculty.edit', $f->id) }}"><i class="fas fa-pen-to-square"></i> Edit account</a>
                                <form method="POST" action="{{ route('chair.faculty.toggle', $f->id) }}"
                                      data-confirm="{{ $f->is_active
                                          ? $f->name . ' will be deactivated and can no longer sign in or manage their subjects.'
                                          : $f->name . ' will be reactivated and can sign in to CPACE again.' }}"
                                      data-confirm-title="{{ $f->is_active ? 'Deactivate this faculty account?' : 'Activate this faculty account?' }}"
                                      data-confirm-ok="{{ $f->is_active ? 'Yes, deactivate' : 'Yes, activate' }}"
                                      data-confirm-icon="question"
                                      @if ($f->is_active) data-confirm-danger @endif>
                                    @csrf
                                    <button type="submit" class="{{ $f->is_active ? 'danger' : '' }}">
                                        <i class="fas fa-power-off"></i> {{ $f->is_active ? 'Deactivate account' : 'Activate account' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8"><div class="empty"><i class="fas fa-user-slash"></i><div>No faculty accounts yet. Click "Add Faculty" to create one.</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="empty" id="facultyNoResults" style="display:none;"><i class="fas fa-user-slash"></i><div>No faculty match your search or filter.</div></div>
        </div>{{-- /.faculty-table-wrap --}}
        @if($faculty->isNotEmpty())
            <div class="faculty-pagination" id="facultyPagination">
                <span class="faculty-pag-info" id="facultyPagInfo"></span>
                <div class="faculty-pag-btns" id="facultyPagBtns"></div>
            </div>
        @endif
    </div>

</main>

<!-- ASSIGN SUBJECTS MODAL -->
<div class="modal-overlay" id="assignModal">
    <div class="modal">
        <h3>Assign Subjects</h3>
        <p class="sub" id="assignSub">Tick the subjects this faculty member handles. Each one is open to all sections unless you pick specific ones.</p>
        <form method="POST" id="assignForm"
              data-confirm="This faculty member's subject access will be replaced with exactly what's ticked here."
              data-confirm-title="Save subject assignments?"
              data-confirm-ok="Yes, save assignments"
              data-confirm-icon="question">
            @csrf
            @include('chair.partials.subject-section-fields', ['subjects' => $subjects, 'sections' => $sections])
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeAssign()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Assignments</button>
            </div>
        </form>
    </div>
</div>

<script>
    const assignModal = document.getElementById('assignModal');
    const assignForm  = document.getElementById('assignForm');

    function openAssign(id, name, current, currentSections) {
        assignForm.action = `/chair/faculty/${id}/assign`;
        document.getElementById('assignSub').textContent = `Tick the subjects ${name} handles. Each one is open to all sections unless you pick specific ones.`;
        currentSections = currentSections || {};
        assignForm.querySelectorAll('input[name="subjects[]"]').forEach(cb => {
            const sid = parseInt(cb.dataset.sid);
            setSubjectSections(sid, current.includes(sid), currentSections[sid] || []);
        });
        assignModal.classList.add('open');
    }
    function closeAssign() { assignModal.classList.remove('open'); }

    // Row "..." menus: open below (or above near the bottom of the screen).
    function closeRowMenus() { document.querySelectorAll('.row-menu.open').forEach(m => m.classList.remove('open')); }
    function toggleRowMenu(event, btn) {
        event.stopPropagation();
        const menu = btn.closest('.row-menu');
        const wasOpen = menu.classList.contains('open');
        closeRowMenus();
        if (wasOpen) return;
        menu.classList.add('open');
        const drop = menu.querySelector('.row-dropdown');
        const r = btn.getBoundingClientRect();
        const below = r.bottom + 6 + drop.offsetHeight <= window.innerHeight;
        drop.style.top = (below ? r.bottom + 6 : r.top - 6 - drop.offsetHeight) + 'px';
        drop.style.left = Math.max(8, r.right - drop.offsetWidth) + 'px';
    }
    document.addEventListener('click', closeRowMenus);
    window.addEventListener('scroll', closeRowMenus, true);
    window.addEventListener('resize', closeRowMenus);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeRowMenus(); });
    assignModal.addEventListener('click', e => { if (e.target === assignModal) closeAssign(); });

    /* ── Search + status filter + pagination over the faculty table.
       Every row is already rendered server-side; this just narrows which
       ones are visible and slices the result into pages, so a growing
       faculty roster doesn't turn into one endless table. ── */
    (function () {
        const searchInput = document.getElementById('facultySearchInput');
        const filterSelect = document.getElementById('facultyFilter');
        const tbody = document.getElementById('facultyTableBody');
        const pagination = document.getElementById('facultyPagination');
        const noResults = document.getElementById('facultyNoResults');
        if (!tbody) return;

        const allRows = Array.from(tbody.querySelectorAll('.faculty-row'));
        const pageSize = 10;
        let filter = '';   // "status:active", "contrib:attention", ...
        let currentPage = 1;

        function matches(row) {
            const q = (searchInput?.value || '').trim().toLowerCase();
            const searchOk = !q || row.dataset.name.includes(q) || row.dataset.email.includes(q);
            const [kind, value] = filter.split(':');
            const filterOk = !filter || row.dataset[kind] === value;
            return searchOk && filterOk;
        }

        function render() {
            const visible = allRows.filter(matches);
            const totalPages = Math.max(1, Math.ceil(visible.length / pageSize));
            currentPage = Math.min(currentPage, totalPages);

            allRows.forEach(row => { row.style.display = 'none'; });
            const start = (currentPage - 1) * pageSize;
            visible.slice(start, start + pageSize).forEach(row => { row.style.display = ''; });

            if (noResults) noResults.style.display = visible.length === 0 ? '' : 'none';

            if (pagination) {
                pagination.style.display = visible.length === 0 ? 'none' : '';
                const from = visible.length ? start + 1 : 0;
                const to = Math.min(start + pageSize, visible.length);
                document.getElementById('facultyPagInfo').textContent = `Showing ${from}–${to} of ${visible.length} faculty`;

                let html = `<button type="button" class="faculty-pag-btn" data-go="prev" ${currentPage <= 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
                for (let p = 1; p <= totalPages; p++) {
                    html += `<button type="button" class="faculty-pag-btn ${p === currentPage ? 'active' : ''}" data-go="${p}">${p}</button>`;
                }
                html += `<button type="button" class="faculty-pag-btn" data-go="next" ${currentPage >= totalPages ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
                document.getElementById('facultyPagBtns').innerHTML = html;
            }
        }

        document.getElementById('facultyPagBtns')?.addEventListener('click', e => {
            const btn = e.target.closest('[data-go]');
            if (!btn || btn.disabled) return;
            const go = btn.dataset.go;
            if (go === 'prev') currentPage = Math.max(1, currentPage - 1);
            else if (go === 'next') currentPage += 1;
            else currentPage = parseInt(go, 10);
            clearSelection();
            render();
        });

        if (searchInput) {
            searchInput.addEventListener('input', () => { currentPage = 1; clearSelection(); render(); });
        }

        // ── Select + bulk actions. Changing the search/filter/page clears the
        //    selection, so an action only ever touches rows the chair can see.
        const selectAll = document.getElementById('facultySelectAll');
        const bulkBar = document.getElementById('facultyBulkBar');
        const bulkForm = document.getElementById('facultyBulkForm');
        const boxes = () => allRows.map(r => r.querySelector('.faculty-select')).filter(Boolean);
        const visibleBoxes = () => boxes().filter(cb => cb.closest('tr').style.display !== 'none');
        const checked = () => boxes().filter(cb => cb.checked);

        function syncSelection() {
            boxes().forEach(cb => cb.closest('tr').classList.toggle('is-selected', cb.checked));
            const n = checked().length;
            bulkBar.hidden = n === 0;
            document.getElementById('facultyBulkCount').textContent = n;
            const vis = visibleBoxes();
            selectAll.checked = vis.length > 0 && vis.every(cb => cb.checked);
            selectAll.indeterminate = !selectAll.checked && vis.some(cb => cb.checked);
        }
        function clearSelection() { boxes().forEach(cb => { cb.checked = false; }); syncSelection(); }

        selectAll?.addEventListener('change', () => {
            visibleBoxes().forEach(cb => { cb.checked = selectAll.checked; });
            syncSelection();
        });
        tbody.addEventListener('change', e => { if (e.target.classList.contains('faculty-select')) syncSelection(); });
        document.getElementById('facultyBulkClear')?.addEventListener('click', clearSelection);

        const BULK_TEXT = {
            activate: { title: 'Activate {n}?', text: 'They will be able to sign in to CPACE again.', ok: 'Yes, activate', danger: false },
            deactivate: { title: 'Deactivate {n}?', text: 'They will no longer be able to sign in or manage their subjects. You can activate them again later.', ok: 'Yes, deactivate', danger: true },
            delete: { title: 'Delete {n}?', text: 'This permanently removes the accounts and cannot be undone. Accounts that already have questions, quizzes, or materials in CPACE are kept — deactivate those instead.', ok: 'Yes, delete', danger: true },
        };
        bulkBar?.addEventListener('click', async e => {
            const btn = e.target.closest('[data-bulk]');
            if (!btn) return;
            const picked = checked();
            if (!picked.length) return;
            const action = btn.dataset.bulk;
            const label = picked.length === 1 ? picked[0].dataset.name : picked.length + ' faculty accounts';
            const t = BULK_TEXT[action];
            const ok = await CPACE.confirm({
                title: t.title.replace('{n}', label), text: t.text, confirmText: t.ok,
                icon: t.danger ? 'warning' : 'question', danger: t.danger,
            });
            if (!ok) return;

            bulkForm.querySelectorAll('input[name="faculty_ids[]"]').forEach(el => el.remove());
            picked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = 'faculty_ids[]'; input.value = cb.value;
                bulkForm.appendChild(input);
            });
            document.getElementById('facultyBulkAction').value = action;
            CPACE.loading(action === 'delete' ? 'Deleting accounts...' : 'Updating accounts...');
            bulkForm.submit();
        });

        filterSelect?.addEventListener('change', () => {
            filter = filterSelect.value;
            filterSelect.classList.toggle('is-set', filter !== '');
            currentPage = 1;
            clearSelection();
            render();
        });

        render();
    })();
</script>
@include('chair.partials.subject-section-script')


    @include('partials.alerts')
</body>
</html>
