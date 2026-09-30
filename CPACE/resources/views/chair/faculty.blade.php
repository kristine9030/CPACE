<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.chart-kit')
    <style>
        /* ── Module-level tabs: Accounts vs. Performance Report. Same visual
           language as the inner Overview/Visualization tabs below, but on its
           own class names so the two tab systems don't fight over which
           .tab-panel / .tab-btn a click should touch. ── */
        .mtab-bar { display:flex; gap:0; margin-bottom:18px; background:white; border-radius:12px; padding:4px; border:1px solid #eee; width:fit-content; }
        .mtab-btn { padding:9px 22px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; background:transparent; color:#888; transition:all .2s; display:flex; align-items:center; gap:7px; }
        .mtab-btn:hover { color:#555; background:#f8f8fa; }
        .mtab-btn.active { background:var(--primary); color:white; box-shadow:0 2px 8px rgba(123,29,29,0.25); }
        .mtab-panel { display:none; }
        .mtab-panel.active { display:block; }

        /* ── Accounts tab ── */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:2000; align-items:center; justify-content:center; padding:20px; }
        .modal-overlay.open { display:flex; }
        .modal { background:#fff; border-radius:16px; width:100%; max-width:480px; padding:24px; max-height:90vh; overflow-y:auto; }
        #assignModal .modal { max-width:640px; }
        .modal h3 { font-size:16px; color:#1a1a1a; margin-bottom:4px; }
        .modal p.sub { font-size:12px; color:#999; margin-bottom:18px; }
        .modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:20px; }
        .action-btn { width:32px; height:32px; border:none; border-radius:50%; cursor:pointer; font-size:12px; display:inline-flex; align-items:center; justify-content:center; transition:all .2s; }
        .ab-edit { background:#f3f4f6; color:#555; }
        .ab-edit:hover { background:#e5e7eb; }
        .ab-assign { background:#f3f4f6; color:#555; }
        .ab-assign:hover { background:#e5e7eb; }
        .ab-toggle { background:#fde8e8; color:var(--accent); }
        .ab-toggle:hover { background:#fbd0d0; }
        .ab-activity { background:#f3f4f6; color:#555; }
        .ab-activity:hover { background:#e5e7eb; }

        /* ── Faculty count pill + search/filter toolbar, matching the
           reference design: a soft pill instead of plain text, a live
           search box, and a status filter — all client-side over the
           already-rendered rows (the roster is small; no need for a
           server round-trip). ── */
        .faculty-toolbar { display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; margin-bottom:18px; }
        .faculty-count-pill { display:inline-flex; align-items:center; gap:8px; background:var(--primary-light); color:var(--primary); font-weight:700; font-size:13px; padding:9px 16px; border-radius:20px; }
        .faculty-toolbar-right { display:flex; align-items:center; gap:10px; }
        .faculty-search { position:relative; }
        .faculty-search i { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#bbb; font-size:12px; }
        .faculty-search input { width:230px; font-family:'Poppins',sans-serif; font-size:13px; border:1px solid #ececec; border-radius:20px; padding:9px 14px 9px 34px; outline:none; background:#fff; color:#444; transition:border-color .15s; }
        .faculty-search input:focus { border-color:var(--primary); }
        .faculty-filter-btn { width:38px; height:38px; border-radius:50%; border:1px solid #ececec; background:#fff; color:#666; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; font-size:13px; position:relative; flex-shrink:0; }
        .faculty-filter-btn:hover { border-color:#ddd; }
        .faculty-filter-btn.active-filter { background:var(--primary); border-color:var(--primary); color:#fff; }
        .faculty-filter-menu { display:none; position:absolute; top:calc(100% + 8px); right:0; background:#fff; border:1px solid #ececec; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.12); padding:6px; min-width:170px; z-index:60; }
        .faculty-filter-menu.open { display:block; }
        .faculty-filter-menu button { display:flex; align-items:center; gap:9px; width:100%; padding:9px 12px; border-radius:7px; border:none; background:none; font-size:12.5px; color:#444; text-align:left; cursor:pointer; font-family:'Poppins',sans-serif; }
        .faculty-filter-menu button:hover { background:#f5f5f5; }
        .faculty-filter-menu button.selected { color:var(--primary); font-weight:700; }
        .faculty-filter-wrap { position:relative; }

        /* ── Pagination footer (client-side; rows are already all rendered) ── */
        .faculty-pagination { display:flex; justify-content:space-between; align-items:center; padding:14px 4px 2px; border-top:1px solid #f5f5f5; margin-top:6px; }
        .faculty-pag-info { font-size:11.5px; color:#999; }
        .faculty-pag-btns { display:flex; gap:5px; }
        .faculty-pag-btn { min-width:28px; height:28px; padding:0 7px; border:1px solid #e5e5e5; background:#fff; border-radius:7px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:12px; color:#555; transition:all .15s; font-family:'Poppins',sans-serif; }
        .faculty-pag-btn.active { background:var(--primary); color:#fff; border-color:var(--primary); }
        .faculty-pag-btn:hover:not(.active):not(:disabled) { background:#f5f5f5; }
        .faculty-pag-btn:disabled { opacity:.4; cursor:not-allowed; }
        @media (max-width:620px) { .faculty-pagination { flex-direction:column; gap:8px; align-items:flex-start; } .faculty-search input { width:160px; } }
        .otp-cell { display: flex; align-items: center; gap: 6px; margin-top: 4px; }
        .temp-pass { font-family: 'Courier New', monospace; font-weight: 700; color: #7B1D1D; background: #f8eaea; padding: 2px 8px; border-radius: 6px; font-size: 11px; letter-spacing: .5px; }
        .otp-reveal, .copy-mini { background: none; border: none; color: #bbb; cursor: pointer; font-size: 11px; }
        .otp-reveal:hover, .copy-mini:hover { color: var(--primary); }

        /* ── Faculty page responsive ── */
        .faculty-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        @media (max-width: 768px) {
            .faculty-table-wrap { overflow-x: auto; }
            .faculty-table-wrap table { min-width: 540px; }
            .modal { padding: 18px 16px; }
            .check-grid { grid-template-columns: 1fr !important; }
        }
        @media (max-width: 480px) {
            .modal-actions { flex-direction: column-reverse; }
            .modal-actions .btn { width: 100%; justify-content: center; }
        }

        /* ── Performance tab ── */
        .stats-row .stat-card {
            display:flex; flex-direction:column; height:100%;
            box-shadow:0 4px 10px rgba(10,5,5,.14), 0 16px 32px -8px rgba(10,5,5,.34);
            transition:transform .18s ease, box-shadow .18s ease;
        }
        .stats-row .stat-card:hover {
            transform:translateY(-2px);
            box-shadow:0 6px 14px rgba(10,5,5,.18), 0 22px 40px -8px rgba(10,5,5,.4);
        }
        .stats-row .stat-top { flex:1; }
        .stats-row .stat-icon { width:52px; height:52px; border-radius:13px; font-size:24px; flex-shrink:0; }
        .stats-row .stat-lbl { font-size:13.5px; font-weight:700; color:#1a1a1a; margin-bottom:6px; letter-spacing:-.01em; }
        .stat-unit { font-size:12px; font-weight:600; color:#aaa; vertical-align:middle; margin-left:2px; }
        .stat-context {
            font-size:10.5px; color:#999; margin-top:12px;
            padding-top:10px; border-top:1px dashed #eee; line-height:1.4;
        }
        .stat-context strong { color:#1a1a1a; font-weight:700; }

        /* ── Action queue — the page's answer to "what do I do about this?".
           Ranked worst-first, each row naming the person or subject, the
           reason, and the button that acts on it. ── */
        .queue-card { background:#fff; border-radius:14px; padding:0; margin-bottom:20px; box-shadow:0 2px 6px rgba(15,10,10,.08), 0 10px 22px -10px rgba(15,10,10,.22); overflow:hidden; }
        .queue-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:18px 22px 14px; flex-wrap:wrap; }
        .queue-title { font-size:14px; font-weight:700; color:#1a1a1a; display:flex; align-items:center; gap:9px; }
        .queue-title i { color:var(--accent); }
        .queue-count { display:inline-flex; align-items:center; justify-content:center; min-width:22px; height:22px; padding:0 8px; border-radius:11px; background:#fde8e8; color:var(--accent); font-size:11px; font-weight:700; }
        .queue-sub { font-size:10.5px; color:#aaa; }
        .queue-row { display:flex; align-items:flex-start; gap:14px; padding:14px 22px; border-top:1px solid #f4f4f6; transition:background .15s; }
        .queue-row:hover { background:#fafafb; }
        .queue-sev { width:34px; height:34px; border-radius:10px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:13px; }
        .queue-sev.crit { background:#fde8e8; color:var(--accent); }
        .queue-sev.warn { background:#fef3c7; color:#b45309; }
        .queue-body { flex:1; min-width:0; }
        .queue-row-title { font-size:13px; font-weight:700; color:#1a1a1a; }
        .queue-why { font-size:11.5px; color:#777; line-height:1.55; margin-top:3px; }
        .queue-act { flex-shrink:0; align-self:center; display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:8px; background:var(--primary-light); color:var(--primary); font-size:11.5px; font-weight:700; text-decoration:none; border:none; cursor:pointer; white-space:nowrap; font-family:'Poppins',sans-serif; }
        .queue-act:hover { background:#ecd9d9; }
        .queue-clear { padding:26px 22px; text-align:center; color:#999; font-size:12.5px; border-top:1px solid #f4f4f6; }
        .queue-clear i { color:var(--green); font-size:20px; display:block; margin-bottom:8px; }
        @media (max-width:640px) {
            .queue-row { flex-wrap:wrap; }
            .queue-act { width:100%; justify-content:center; margin-top:4px; }
        }

        /* ── Decision status chips used in the faculty table ── */
        .fstatus { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:15px; font-size:10px; font-weight:700; white-space:nowrap; }
        .fstatus.crit { background:#fde8e8; color:#b91c1c; }
        .fstatus.warn { background:#fef3c7; color:#b45309; }
        .fstatus.ok   { background:#d1fae5; color:#047857; }
        .fstatus.idle { background:#f3f4f6; color:#6b7280; }
        .fstatus-why { font-size:10px; color:#aaa; margin-top:4px; line-height:1.4; }

        /* ── Subject risk list — gap against the program average, worst first ── */
        .risk-subject { padding:14px 0; border-top:1px solid #f5f5f5; }
        .risk-subject:first-of-type { border-top:none; }
        .risk-subject-top { display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:7px; }
        .risk-subject-name { font-size:12px; font-weight:600; color:#333; }
        .risk-level { display:inline-flex; align-items:center; gap:5px; padding:3px 9px; border-radius:14px; font-size:9.5px; font-weight:700; }
        .risk-level.crit { background:#fde8e8; color:#b91c1c; }
        .risk-level.warn { background:#fef3c7; color:#b45309; }
        .risk-level.ok   { background:#d1fae5; color:#047857; }
        .risk-bar { height:7px; background:#f1f1f3; border-radius:4px; overflow:hidden; position:relative; }
        .risk-fill { height:100%; border-radius:4px; }
        .risk-avg-mark { position:absolute; top:-3px; bottom:-3px; width:2px; background:#1a1a1a; opacity:.35; }
        .risk-meta { display:flex; justify-content:space-between; gap:8px; font-size:10px; color:#999; margin-top:6px; }
        .risk-gap { font-weight:700; }
        .risk-gap.short { color:var(--accent); }
        .risk-gap.over { color:#047857; }

        /* ── Chart "what is this for" hover tooltip — a small info badge next
           to each chart's title so a graph reads as a purposeful decision
           tool, not just a plotted number. Pure CSS, no JS needed. ── */
        .viz-card h4 { display:flex; align-items:center; gap:7px; }
        .chart-info { position:relative; display:inline-flex; margin-left:auto; }
        .chart-info-icon { width:20px; height:20px; border-radius:50%; background:#f3f4f6; color:#999; display:flex; align-items:center; justify-content:center; font-size:10.5px; cursor:help; }
        .chart-info:hover .chart-info-icon { background:var(--primary-light); color:var(--primary); }
        .chart-tooltip {
            display:none; position:absolute; top:calc(100% + 8px); right:0; z-index:40;
            width:250px; background:#1f2430; color:#f1f1f4; font-weight:400;
            font-size:11px; line-height:1.55; padding:12px 14px; border-radius:10px;
            box-shadow:0 10px 30px rgba(0,0,0,.25);
        }
        .chart-tooltip strong { color:#fff; }
        .chart-tooltip::before {
            content:''; position:absolute; bottom:100%; right:14px;
            border:6px solid transparent; border-bottom-color:#1f2430;
        }
        .chart-info:hover .chart-tooltip { display:block; }

        /* ── Per-chart analytics readout — turns each canvas into a small
           dashboard tile with a headline number and a plain-language
           takeaway underneath, instead of a bare chart. ── */
        .chart-readout { display:flex; gap:18px; margin:2px 0 10px; flex-wrap:wrap; }
        .chart-readout-item { }
        .chart-readout-val { font-size:20px; font-weight:700; color:#1a1a1a; line-height:1; }
        .chart-readout-lbl { font-size:9.5px; color:#999; text-transform:uppercase; letter-spacing:.4px; margin-top:3px; }
        .chart-insight {
            display:flex; gap:9px; align-items:flex-start;
            margin-top:14px; padding-top:12px; border-top:1px solid #f2f2f2;
            font-size:11.5px; color:#666; line-height:1.55;
        }
        .chart-insight i { color:var(--primary); font-size:12px; margin-top:2px; flex-shrink:0; }

        .tab-bar { display:flex; gap:0; margin-bottom:18px; background:white; border-radius:12px; padding:4px; border:1px solid #eee; width:fit-content; }
        .tab-btn { padding:9px 22px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; background:transparent; color:#888; transition:all .2s; display:flex; align-items:center; gap:7px; }
        .tab-btn:hover { color:#555; background:#f8f8fa; }
        .tab-btn.active { background:var(--primary); color:white; box-shadow:0 2px 8px rgba(123,29,29,0.25); }
        .tab-panel { display:none; }
        .tab-panel.active { display:block; }
        .report-grid { display:grid; grid-template-columns:minmax(0,1.35fr) minmax(320px,.65fr); gap:18px; }
        .report-table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
        .report-table-wrap table { min-width:780px; }
        .faculty-cell { display:flex; align-items:center; gap:10px; }
        .faculty-name { font-size:12.5px; font-weight:600; color:#1a1a1a; }
        .faculty-subjects { font-size:10px; color:#aaa; margin-top:3px; }
        .metric-main { font-size:13px; font-weight:700; color:#222; }
        .metric-sub { font-size:10px; color:#aaa; margin-top:2px; }
        .legend-note { font-size:10.5px; line-height:1.6; color:#999; background:#f8f8fa; border-radius:9px; padding:11px 13px; margin-top:14px; }
        @media(max-width:1050px) { .report-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'faculty'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Faculty</div>
                <div class="page-sub">Manage faculty accounts, subject assignments, and their test-bank contributions.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('chair.faculty.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Faculty</a>
            @include('partials.topbar-actions')
        </div>
    </div>

    {{-- Flash messages surface as SweetAlert popups via partials.alerts --}}

    @php $activeTab = $activeTab ?? 'accounts'; @endphp
    <div class="mtab-bar" role="tablist">
        <button type="button" class="mtab-btn {{ $activeTab === 'accounts' ? 'active' : '' }}" role="tab" aria-selected="{{ $activeTab === 'accounts' ? 'true' : 'false' }}" onclick="switchModuleTab('accounts', this)"><i class="fas fa-chalkboard-user"></i> Accounts</button>
        <button type="button" class="mtab-btn {{ $activeTab === 'performance' ? 'active' : '' }}" role="tab" aria-selected="{{ $activeTab === 'performance' ? 'true' : 'false' }}" onclick="switchModuleTab('performance', this)"><i class="fas fa-chart-column"></i> Performance Report</button>
    </div>

    <div id="mtab-accounts" class="mtab-panel {{ $activeTab === 'accounts' ? 'active' : '' }}">
    <div class="card">
        <div class="faculty-toolbar">
            <span class="faculty-count-pill"><i class="fas fa-chalkboard-user"></i> All Faculty ({{ $faculty->count() }})</span>
            <div class="faculty-toolbar-right">
                <div class="faculty-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="facultySearchInput" placeholder="Search faculty..." autocomplete="off">
                </div>
                <div class="faculty-filter-wrap">
                    <button type="button" class="faculty-filter-btn" id="facultyFilterBtn" title="Filter by status"><i class="fas fa-sliders"></i></button>
                    <div class="faculty-filter-menu" id="facultyFilterMenu">
                        <button type="button" class="selected" data-status="">All statuses</button>
                        <button type="button" data-status="active">Active</button>
                        <button type="button" data-status="pending">Setup Pending</button>
                        <button type="button" data-status="inactive">Inactive</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="faculty-table-wrap">
        <table>
            <thead>
                <tr><th>Name</th><th>Email</th><th>Assigned Subjects</th><th>Status</th><th style="text-align:right;">Actions</th></tr>
            </thead>
            <tbody id="facultyTableBody">
            @forelse ($faculty as $f)
                @php
                    $rowStatus = ! $f->is_active ? 'inactive' : ($f->setup_completed_at === null ? 'pending' : 'active');
                @endphp
                <tr class="faculty-row" data-name="{{ strtolower($f->name) }}" data-email="{{ strtolower($f->email) }}" data-status="{{ $rowStatus }}">
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="user-av">{{ strtoupper(substr($f->first_name,0,1)).strtoupper(substr($f->last_name,0,1)) }}</div>
                            <span style="font-weight:600; color:#1a1a1a;">{{ $f->name }}</span>
                        </div>
                    </td>
                    <td style="color:#666;">{{ $f->email }}</td>
                    <td style="max-width:260px;">
                        @forelse ($f->assignedSubjects as $s)
                            <span class="subj-badge b-{{ strtolower($s->code) }}">{{ $s->code }}</span>
                        @empty
                            <span style="color:#bbb; font-size:12px;">— none —</span>
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
                    <td style="text-align:right; white-space:nowrap;">
                        <a href="{{ route('chair.faculty.activity', $f->id) }}" class="action-btn ab-activity" title="View activity log"><i class="fas fa-clock-rotate-left"></i></a>
                        <button class="action-btn ab-assign" title="Assign subjects"
                            onclick="openAssign({{ $f->id }}, '{{ addslashes($f->name) }}', {{ $f->assignedSubjects->pluck('id')->toJson() }}, {{ $f->sectionsBySubject->toJson() }})">
                            <i class="fas fa-layer-group"></i>
                        </button>
                        <a href="{{ route('chair.faculty.edit', $f->id) }}" class="action-btn ab-edit" title="Edit account"><i class="fas fa-pen"></i></a>
                        <form method="POST" action="{{ route('chair.faculty.toggle', $f->id) }}" style="display:inline;"
                              data-confirm="{{ $f->is_active
                                  ? $f->name . ' will be deactivated and can no longer sign in or manage their subjects.'
                                  : $f->name . ' will be reactivated and can sign in to CPACE again.' }}"
                              data-confirm-title="{{ $f->is_active ? 'Deactivate this faculty account?' : 'Activate this faculty account?' }}"
                              data-confirm-ok="{{ $f->is_active ? 'Yes, deactivate' : 'Yes, activate' }}"
                              data-confirm-icon="question"
                              @if ($f->is_active) data-confirm-danger @endif>
                            @csrf
                            <button type="submit" class="action-btn ab-toggle" title="{{ $f->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fas fa-power-off"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="empty"><i class="fas fa-user-slash"></i><div>No faculty accounts yet. Click "Add Faculty" to create one.</div></div></td></tr>
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
    </div><!-- /mtab-accounts -->

    @php
        $totalFaculty = $facultyRows->count();
        $nonContributors = $facultyRows->where('questions', 0)->count();
        $contribPct = $totalFaculty > 0 ? (int) round($stats['contributors'] / $totalFaculty * 100) : 0;
        $flagPct = $stats['questions'] > 0 ? (int) round($stats['flags'] / $stats['questions'] * 100) : 0;
        $accuracyGap = $stats['accuracy'] !== null ? $stats['accuracy'] - 75 : null;
        $unstaffedSubjects = $subjectRows->where('assigned_faculty', 0)->count();
        $understaffedSubjects = $subjectRows->filter(fn ($s) => $s['assigned_faculty'] > 0 && $s['contributors'] === 0)->count();
        $weakestSubject = $subjectRows->where('answered', '>', 0)->sortBy('accuracy')->first();
        $topContributor = $facultyRows->first();
    @endphp

    <div id="mtab-performance" class="mtab-panel {{ $activeTab === 'performance' ? 'active' : '' }}">

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Total Contributions</div>
                    <div class="stat-num">{{ $stats['questions'] }} <span class="stat-unit">Questions</span></div>
                </div>
                <div class="stat-icon si-red"><i class="fas fa-circle-question"></i></div>
            </div>
            <div class="stat-context">Authored by <strong>{{ $stats['contributors'] }}</strong> of {{ $totalFaculty }} faculty.</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Contributing Faculty</div>
                    <div class="stat-num">{{ $stats['contributors'] }} <span class="stat-unit">Faculty</span></div>
                </div>
                <div class="stat-icon si-blue"><i class="fas fa-user-pen"></i></div>
            </div>
            <div class="stat-context">
                @if($nonContributors > 0)
                    <strong style="color:var(--accent);">{{ $nonContributors }}</strong> faculty haven't authored a question yet.
                @else
                    <strong style="color:#059669;">Every faculty member</strong> has contributed at least one question.
                @endif
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Overall Question Accuracy</div>
                    <div class="stat-num">{{ $stats['accuracy'] ?? '—' }}{{ $stats['accuracy'] !== null ? '%' : '' }} <span class="stat-unit">Accuracy</span></div>
                </div>
                <div class="stat-icon si-green"><i class="fas fa-bullseye"></i></div>
            </div>
            <div class="stat-context">
                @if($accuracyGap === null)
                    No graded answers recorded yet.
                @elseif($accuracyGap >= 0)
                    <strong style="color:#059669;">+{{ $accuracyGap }} pts</strong> above the 75% benchmark.
                @else
                    <strong style="color:var(--accent);">{{ $accuracyGap }} pts</strong> below the 75% benchmark.
                @endif
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Questions Needing Review</div>
                    <div class="stat-num">{{ $stats['flags'] }} <span class="stat-unit">Flagged</span></div>
                </div>
                <div class="stat-icon si-orange"><i class="fas fa-triangle-exclamation"></i></div>
            </div>
            <div class="stat-context">
                @if($stats['flags'] > 0)
                    <strong style="color:var(--accent);">{{ $flagPct }}%</strong> of all authored questions.
                @else
                    <strong style="color:#059669;">No questions</strong> currently flagged for review.
                @endif
            </div>
        </div>
    </div>

    @php
        // A contribution older than this reads as stale for content authoring.
        $staleDays = 60;

        // One decision status per faculty member, reused by the action queue
        // and the roster table below so both tell the same story.
        $facultyStatus = function (array $m) use ($staleDays) {
            $idleDays = $m['last_contribution'] ? (int) $m['last_contribution']->diffInDays(now()) : null;

            if (empty($m['subjects'])) {
                return ['key' => 'no_subjects', 'rank' => 1, 'tone' => 'crit', 'label' => 'No subjects', 'why' => 'Cannot contribute — nothing assigned to them yet.'];
            }
            if ($m['questions'] === 0) {
                return ['key' => 'never', 'rank' => 2, 'tone' => 'crit', 'label' => 'Never contributed', 'why' => 'Assigned to ' . implode(', ', $m['subjects']) . ' but has authored nothing.'];
            }
            if ($m['quality_flags'] > 0) {
                return ['key' => 'quality', 'rank' => 3, 'tone' => 'warn', 'label' => $m['quality_flags'] . ' need review', 'why' => $m['unused'] . ' never used by a quiz.'];
            }
            if ($idleDays !== null && $idleDays > $staleDays) {
                return ['key' => 'stale', 'rank' => 4, 'tone' => 'warn', 'label' => 'Stale · ' . $idleDays . 'd', 'why' => 'No new or edited question in over ' . $staleDays . ' days.'];
            }
            return ['key' => 'ok', 'rank' => 5, 'tone' => 'ok', 'label' => 'On track', 'why' => $m['active'] . ' active questions, no quality flags.'];
        };

        $facultyWithStatus = $facultyRows->map(fn ($m) => $m + ['status' => $facultyStatus($m)]);
        $needsAttention = $facultyWithStatus->filter(fn ($m) => $m['status']['key'] !== 'ok');

        // Program-average baseline for subject coverage. Using the cohort's own
        // average rather than inventing a target keeps the comparison honest.
        $avgActivePerSubject = $subjectRows->isNotEmpty() ? (int) round($subjectRows->avg('active')) : 0;

        $subjectRisk = $subjectRows->map(function ($s) use ($avgActivePerSubject) {
            $gap = $s['active'] - $avgActivePerSubject;
            $miscalibrated = $s['answered'] >= 30 && ($s['accuracy'] < 50 || $s['accuracy'] > 95);

            if ($s['assigned_faculty'] === 0) {
                $risk = ['level' => 'crit', 'label' => 'No faculty', 'reason' => 'Nobody owns this subject.'];
            } elseif ($s['contributors'] === 0) {
                $risk = ['level' => 'crit', 'label' => 'No authors', 'reason' => $s['assigned_faculty'] . ' assigned, none authoring.'];
            } elseif ($s['active'] === 0) {
                $risk = ['level' => 'crit', 'label' => 'No active questions', 'reason' => 'Nothing here reaches students.'];
            } elseif ($miscalibrated) {
                $risk = ['level' => 'warn', 'label' => 'Miscalibrated', 'reason' => $s['accuracy'] . '% accuracy suggests wording or difficulty issues.'];
            } elseif ($avgActivePerSubject > 0 && $s['active'] < $avgActivePerSubject * 0.6) {
                $risk = ['level' => 'warn', 'label' => 'Thin coverage', 'reason' => 'Well below the ' . $avgActivePerSubject . '-question program average.'];
            } else {
                $risk = ['level' => 'ok', 'label' => 'Adequate', 'reason' => 'At or near the program average.'];
            }

            return $s + ['risk' => $risk, 'gap' => $gap];
        })->sortBy(fn ($s) => [['crit' => 0, 'warn' => 1, 'ok' => 2][$s['risk']['level']], $s['active']])->values();

        // ── Build the ranked action queue ──
        $queue = [];
        foreach ($subjectRisk->where('risk.level', 'crit') as $s) {
            $queue[] = [
                'rank' => 1, 'sev' => 'crit', 'icon' => 'fa-user-slash',
                'title' => $s['code'] . ' — ' . $s['risk']['label'],
                'why' => $s['risk']['reason'] . ' ' . $s['questions'] . ' questions in the bank, ' . $s['active'] . ' of them active.',
                'do' => $s['assigned_faculty'] === 0 ? 'Assign faculty' : 'Review coverage',
                'href' => $s['assigned_faculty'] === 0 ? route('chair.faculty') : route('chair.analytics.test-bank-coverage'),
            ];
        }
        foreach ($needsAttention->whereIn('status.key', ['no_subjects', 'never'])->take(4) as $m) {
            $queue[] = [
                'rank' => 2, 'sev' => 'crit', 'icon' => 'fa-user-pen',
                'title' => $m['name'] . ' — ' . $m['status']['label'],
                'why' => $m['status']['why'],
                'do' => $m['status']['key'] === 'no_subjects' ? 'Assign subjects' : 'View activity',
                'href' => $m['status']['key'] === 'no_subjects' ? route('chair.faculty') : route('chair.faculty.activity', $m['id']),
            ];
        }
        foreach ($needsAttention->where('status.key', 'quality')->sortByDesc('quality_flags')->take(3) as $m) {
            $queue[] = [
                'rank' => 3, 'sev' => 'warn', 'icon' => 'fa-triangle-exclamation',
                'title' => $m['name'] . ' — ' . $m['quality_flags'] . ' question' . ($m['quality_flags'] === 1 ? '' : 's') . ' to review',
                'why' => $m['status']['why'] . ' Flags mean unused, or accuracy under 40% / over 95% after enough attempts.',
                'do' => 'View activity',
                'href' => route('chair.faculty.activity', $m['id']),
            ];
        }
        foreach ($subjectRisk->where('risk.level', 'warn')->take(3) as $s) {
            $queue[] = [
                'rank' => 4, 'sev' => 'warn', 'icon' => 'fa-layer-group',
                'title' => $s['code'] . ' — ' . $s['risk']['label'],
                'why' => $s['risk']['reason'] . ' ' . $s['active'] . ' active questions vs a ' . $avgActivePerSubject . ' program average.',
                'do' => 'Review coverage',
                'href' => route('chair.analytics.test-bank-coverage'),
            ];
        }
        foreach ($needsAttention->where('status.key', 'stale')->take(3) as $m) {
            $queue[] = [
                'rank' => 5, 'sev' => 'warn', 'icon' => 'fa-clock',
                'title' => $m['name'] . ' — no recent contributions',
                'why' => $m['status']['why'] . ' Last authored ' . ($m['last_contribution'] ? $m['last_contribution']->diffForHumans() : 'never') . '.',
                'do' => 'View activity',
                'href' => route('chair.faculty.activity', $m['id']),
            ];
        }
        usort($queue, fn ($a, $b) => $a['rank'] <=> $b['rank']);
        $queue = array_slice($queue, 0, 8);
    @endphp

    <div class="queue-card">
        <div class="queue-head">
            <span class="queue-title">
                <i class="fas fa-circle-exclamation"></i> Needs Your Attention
                @if(count($queue)) <span class="queue-count">{{ count($queue) }}</span> @endif
            </span>
            <span class="queue-sub">Ranked most urgent first · each row names who or what, why, and the next step</span>
        </div>

        @forelse($queue as $item)
            <div class="queue-row">
                <div class="queue-sev {{ $item['sev'] }}"><i class="fas {{ $item['icon'] }}"></i></div>
                <div class="queue-body">
                    <div class="queue-row-title">{{ $item['title'] }}</div>
                    <div class="queue-why">{{ $item['why'] }}</div>
                </div>
                <a class="queue-act" href="{{ $item['href'] }}">{{ $item['do'] }} <i class="fas fa-arrow-right" style="font-size:9px;"></i></a>
            </div>
        @empty
            <div class="queue-clear">
                <i class="fas fa-circle-check"></i>
                Nothing needs action right now — every subject has an author, and no question is flagged for review.
            </div>
        @endforelse
    </div>

    <div class="tab-bar" role="tablist">
        <button class="tab-btn active" role="tab" aria-selected="true" onclick="switchTab('overview', this)"><i class="fas fa-table-columns"></i> Overview</button>
        <button class="tab-btn" role="tab" aria-selected="false" onclick="switchTab('visualization', this)"><i class="fas fa-chart-line"></i> Visualization</button>
    </div>

    <div id="tab-overview" class="tab-panel active">
    <div class="report-grid">
        <div class="card">
            <div class="card-head">
                <span class="card-title">Faculty List</span>
                <span style="font-size:10.5px;color:#aaa;">Sorted by what needs attention first, not by output</span>
            </div>
            <div class="report-table-wrap">
                <table>
                    <thead><tr><th>Faculty</th><th>Status</th><th>Questions</th><th>Student Results</th><th>Last Contribution</th><th></th></tr></thead>
                    <tbody>
                    @forelse($facultyWithStatus->sortBy(fn ($m) => [$m['status']['rank'], -$m['questions']]) as $member)
                        <tr>
                            <td>
                                <div class="faculty-cell">
                                    <div class="user-av" style="width:32px;height:32px;font-size:10px;">{{ $member['initials'] }}</div>
                                    <div><div class="faculty-name">{{ $member['name'] }}</div><div class="faculty-subjects">{{ $member['subjects'] ? implode(', ', $member['subjects']) : 'No subjects assigned' }}</div></div>
                                </div>
                            </td>
                            <td>
                                <span class="fstatus {{ $member['status']['tone'] }}">
                                    @if($member['status']['tone'] === 'ok')<i class="fas fa-check"></i>
                                    @elseif($member['status']['tone'] === 'crit')<i class="fas fa-triangle-exclamation"></i>
                                    @else<i class="fas fa-circle-exclamation"></i>@endif
                                    {{ $member['status']['label'] }}
                                </span>
                                <div class="fstatus-why">{{ $member['status']['why'] }}</div>
                            </td>
                            <td><div class="metric-main">{{ $member['questions'] }}</div><div class="metric-sub">{{ $member['active'] }} active · {{ $member['draft'] }} draft · {{ $member['variants'] }} variants</div></td>
                            <td>
                                <div class="metric-main" style="color:{{ $member['accuracy'] === null ? '#aaa' : ($member['accuracy'] >= 75 ? '#047857' : ($member['accuracy'] >= 50 ? '#b45309' : '#b91c1c')) }};">{{ $member['accuracy'] === null ? '—' : $member['accuracy'].'%' }}</div>
                                <div class="metric-sub">{{ $member['answered'] }} answers</div>
                            </td>
                            <td><div style="font-size:11px;color:#555;">{{ $member['last_contribution'] ? $member['last_contribution']->diffForHumans() : 'No contributions' }}</div></td>
                            <td><a href="{{ route('chair.faculty.activity', $member['id']) }}" class="btn btn-ghost btn-sm" title="View activity"><i class="fas fa-clock-rotate-left"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty"><i class="fas fa-chart-column"></i><div>No faculty accounts available.</div></div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <span class="card-title">Subject Risk<x-tip label="How questions are flagged">A question is flagged for review when it's unused, below 40% accuracy, or above 95% accuracy after at least five student answers. These are review signals, not faculty grades.</x-tip></span>
                <span style="font-size:10.5px;color:#aaa;">Highest risk first</span>
            </div>
            @php $riskMax = max(1, (int) $subjectRisk->max('active'), $avgActivePerSubject); @endphp
            @forelse($subjectRisk as $subject)
                <div class="risk-subject">
                    <div class="risk-subject-top">
                        <div>
                            <span class="subj-badge b-{{ strtolower($subject['code']) }}">{{ $subject['code'] }}</span>
                            <span class="risk-subject-name">{{ $subject['name'] }}</span>
                        </div>
                        <span class="risk-level {{ $subject['risk']['level'] }}">
                            @if($subject['risk']['level'] === 'ok')<i class="fas fa-check"></i>
                            @elseif($subject['risk']['level'] === 'crit')<i class="fas fa-triangle-exclamation"></i>
                            @else<i class="fas fa-circle-exclamation"></i>@endif
                            {{ $subject['risk']['label'] }}
                        </span>
                    </div>
                    <div class="risk-bar">
                        <div class="risk-fill" style="width:{{ (int) round($subject['active'] / $riskMax * 100) }}%;background:{{ $subject['risk']['level'] === 'crit' ? 'var(--accent)' : ($subject['risk']['level'] === 'warn' ? '#f59e0b' : '#10b981') }};"></div>
                        @if($avgActivePerSubject > 0)
                            <div class="risk-avg-mark" style="left:{{ (int) round($avgActivePerSubject / $riskMax * 100) }}%;" title="Program average: {{ $avgActivePerSubject }} active questions"></div>
                        @endif
                    </div>
                    <div class="risk-meta">
                        <span>{{ $subject['active'] }} active · {{ $subject['contributors'] }} author{{ $subject['contributors'] === 1 ? '' : 's' }} of {{ $subject['assigned_faculty'] }} assigned · {{ $subject['accuracy'] === null ? 'no answers' : $subject['accuracy'].'% accuracy' }}</span>
                        <span class="risk-gap {{ $subject['gap'] < 0 ? 'short' : 'over' }}">
                            {{ $subject['gap'] < 0 ? $subject['gap'] : '+'.$subject['gap'] }} vs avg
                        </span>
                    </div>
                    <div style="font-size:10.5px;color:#888;margin-top:5px;line-height:1.5;">{{ $subject['risk']['reason'] }}</div>
                </div>
            @empty
                <div class="empty"><div>No subjects available.</div></div>
            @endforelse
        </div>
    </div>
    </div><!-- /tab-overview -->

    @php
        $contributingCount = $stats['contributors'];
        $flaggedFacultyCount = $facultyRows->where('quality_flags', '>', 0)->count();
        $ratedSubjects = $subjectRows->where('answered', '>', 0);
        $avgSubjectAccuracy = $ratedSubjects->isNotEmpty() ? (int) round($ratedSubjects->avg('accuracy')) : null;
        $bestSubject = $ratedSubjects->sortByDesc('accuracy')->first();
        $authoringGapCount = $unstaffedSubjects + $understaffedSubjects;
        $fullyStaffedCount = $subjectRows->count() - $authoringGapCount;
    @endphp

    <div id="tab-visualization" class="tab-panel">
        <div class="viz-grid-layout">
            <div class="viz-card full">
                <h4>
                    <i class="fas fa-user-pen"></i> Test-Bank Contributions per Faculty
                    <span class="chart-info">
                        <span class="chart-info-icon"><i class="fas fa-info"></i></span>
                        <span class="chart-tooltip"><strong>Why this matters:</strong> shows who's actually growing the test bank and how much of it is still draft (invisible to students). Use it to spot faculty who need a nudge to activate their drafts, or who could take on more subjects.</span>
                    </span>
                </h4>
                <div class="viz-sub">Drafted questions do not reach students until they are activated, so both states are shown.</div>
                @if($contributingCount > 0)
                    <div class="chart-readout">
                        <div class="chart-readout-item"><div class="chart-readout-val">{{ $contributingCount }}</div><div class="chart-readout-lbl">Contributing Faculty</div></div>
                        <div class="chart-readout-item"><div class="chart-readout-val">{{ $stats['questions'] }}</div><div class="chart-readout-lbl">Total Questions</div></div>
                        @if($topContributor && $topContributor['questions'] > 0)
                            <div class="chart-readout-item"><div class="chart-readout-val">{{ $topContributor['questions'] }}</div><div class="chart-readout-lbl">Top: {{ $topContributor['name'] }}</div></div>
                        @endif
                    </div>
                @endif
                <div class="chart-canvas-wrap h-lg"><canvas id="vizFacultyContrib"></canvas></div>
                <div class="chart-insight"><i class="fas fa-lightbulb"></i>
                    @if($nonContributors > 0)
                        {{ $nonContributors }} of {{ $totalFaculty }} faculty have not authored a question yet — the fastest way to grow bank coverage is getting them started.
                    @else
                        Every faculty member is contributing — the bank's growth now depends on converting drafts to active questions.
                    @endif
                </div>
            </div>

            <div class="viz-card">
                <h4>
                    <i class="fas fa-triangle-exclamation"></i> Questions Flagged for Review
                    <span class="chart-info">
                        <span class="chart-info-icon"><i class="fas fa-info"></i></span>
                        <span class="chart-tooltip"><strong>Why this matters:</strong> a flag means a question is unused or its accuracy is suspiciously low/high after enough attempts — often a wording or difficulty problem, not a student problem. Prioritize the faculty with the most flags for a quick review pass.</span>
                    </span>
                </h4>
                <div class="viz-sub">Unused, or answered at under 40% / over 95% after five attempts.</div>
                @if($stats['flags'] > 0)
                    <div class="chart-readout">
                        <div class="chart-readout-item"><div class="chart-readout-val">{{ $stats['flags'] }}</div><div class="chart-readout-lbl">Flagged</div></div>
                        <div class="chart-readout-item"><div class="chart-readout-val">{{ $flaggedFacultyCount }}</div><div class="chart-readout-lbl">Faculty Affected</div></div>
                    </div>
                @endif
                <div class="chart-canvas-wrap h-md"><canvas id="vizFacultyFlags"></canvas></div>
                <div class="chart-insight"><i class="fas fa-lightbulb"></i>
                    @if($stats['flags'] > 0)
                        {{ $flagPct }}% of all authored questions are flagged, spread across {{ $flaggedFacultyCount }} faculty member{{ $flaggedFacultyCount === 1 ? '' : 's' }}.
                    @else
                        No question is currently flagged — the bank's quality signals are clean for now.
                    @endif
                </div>
            </div>

            <div class="viz-card">
                <h4>
                    <i class="fas fa-bullseye"></i> Student Accuracy on Each Subject's Questions
                    <span class="chart-info">
                        <span class="chart-info-icon"><i class="fas fa-info"></i></span>
                        <span class="chart-tooltip"><strong>Why this matters:</strong> this reads the test bank's calibration, not faculty performance. A subject sitting far from the 75% benchmark usually means its questions are miscalibrated (too hard, too easy, or ambiguous) rather than a teaching gap.</span>
                    </span>
                </h4>
                <div class="viz-sub">A calibration signal for the bank, not a ranking of faculty.</div>
                @if($avgSubjectAccuracy !== null)
                    <div class="chart-readout">
                        <div class="chart-readout-item"><div class="chart-readout-val">{{ $avgSubjectAccuracy }}%</div><div class="chart-readout-lbl">Avg. Accuracy</div></div>
                        @if($weakestSubject)
                            <div class="chart-readout-item"><div class="chart-readout-val" style="color:var(--accent);">{{ $weakestSubject['accuracy'] }}%</div><div class="chart-readout-lbl">Lowest: {{ $weakestSubject['code'] }}</div></div>
                        @endif
                        @if($bestSubject)
                            <div class="chart-readout-item"><div class="chart-readout-val" style="color:#059669;">{{ $bestSubject['accuracy'] }}%</div><div class="chart-readout-lbl">Highest: {{ $bestSubject['code'] }}</div></div>
                        @endif
                    </div>
                @endif
                <div class="chart-canvas-wrap h-md"><canvas id="vizSubjectAccuracy"></canvas></div>
                <div class="chart-insight"><i class="fas fa-lightbulb"></i>
                    @if($weakestSubject)
                        {{ $weakestSubject['code'] }} is the calibration outlier at {{ $weakestSubject['accuracy'] }}% — worth a sample review of its questions.
                    @else
                        No graded answers yet — this chart fills in once students start attempting quizzes.
                    @endif
                </div>
            </div>

            <div class="viz-card full">
                <h4>
                    <i class="fas fa-people-arrows"></i> Authoring Capacity per Subject
                    <span class="chart-info">
                        <span class="chart-info-icon"><i class="fas fa-info"></i></span>
                        <span class="chart-tooltip"><strong>Why this matters:</strong> being "assigned" to a subject doesn't mean a faculty member has actually written for it. A gap between the two bars means that subject's content depends on nobody, or on faculty who haven't started yet — a staffing risk, not just a content one.</span>
                    </span>
                </h4>
                <div class="viz-sub">Assigned faculty against those who have actually written questions — a gap here is an unstaffed subject.</div>
                <div class="chart-readout">
                    <div class="chart-readout-item"><div class="chart-readout-val">{{ $fullyStaffedCount }}</div><div class="chart-readout-lbl">Fully Staffed Subjects</div></div>
                    <div class="chart-readout-item"><div class="chart-readout-val" style="color:{{ $authoringGapCount > 0 ? 'var(--accent)' : '#059669' }};">{{ $authoringGapCount }}</div><div class="chart-readout-lbl">With an Authoring Gap</div></div>
                </div>
                <div class="chart-canvas-wrap h-md"><canvas id="vizCapacity"></canvas></div>
                <div class="chart-insight"><i class="fas fa-lightbulb"></i>
                    @if($authoringGapCount > 0)
                        {{ $authoringGapCount }} subject{{ $authoringGapCount === 1 ? '' : 's' }} {{ $authoringGapCount === 1 ? 'has' : 'have' }} assigned faculty who haven't authored a question yet (or nobody assigned at all) — the content gap won't close on its own.
                    @else
                        Every subject has at least one faculty member actively contributing questions.
                    @endif
                </div>
            </div>
        </div>

        <div class="legend-note" style="margin-top:16px;">Every value plotted here is also listed as a table on the <strong>Overview</strong> tab.</div>
    </div><!-- /tab-visualization -->
    </div><!-- /mtab-performance -->
</main>

<!-- ASSIGN SUBJECTS MODAL -->
<div class="modal-overlay" id="assignModal">
    <div class="modal">
        <h3>Assign Subjects</h3>
        <p class="sub" id="assignSub">Select the CPALE subjects for this faculty member, and optionally limit each one to specific sections.</p>
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
    /** Module-level tab switching (Accounts vs. Performance Report). Kept
        separate from the shared switchTab() in chart-kit so it doesn't
        collide with the nested Overview/Visualization tabs inside the
        Performance panel. */
    function switchModuleTab(id, trigger) {
        const button = trigger || (window.event && window.event.currentTarget);
        document.querySelectorAll('.mtab-panel').forEach((panel) => panel.classList.remove('active'));
        document.querySelectorAll('.mtab-btn').forEach((btn) => {
            btn.classList.remove('active');
            btn.setAttribute('aria-selected', 'false');
        });

        const panel = document.getElementById('mtab-' + id);
        if (panel) { panel.classList.add('active'); }
        if (button) { button.classList.add('active'); button.setAttribute('aria-selected', 'true'); }
        if (panel && window.Viz) { window.Viz.reveal(panel); }
    }

    const assignModal = document.getElementById('assignModal');
    const assignForm  = document.getElementById('assignForm');

    function openAssign(id, name, current, currentSections) {
        assignForm.action = `/chair/faculty/${id}/assign`;
        document.getElementById('assignSub').textContent = `Select the CPALE subjects for ${name}, and optionally limit each one to specific sections.`;
        currentSections = currentSections || {};
        assignForm.querySelectorAll('input[name="subjects[]"]').forEach(cb => {
            const sid = parseInt(cb.dataset.sid);
            cb.checked = current.includes(sid);
            const secIds = (currentSections[sid] || []).map(Number);
            assignForm.querySelectorAll(`input[data-secid][name="sections[${sid}][]"]`).forEach(sc => {
                sc.checked = secIds.includes(parseInt(sc.dataset.secid));
            });
            const mode = secIds.length > 0 ? 'specific' : 'all';
            const radio = assignForm.querySelector(`input[name="scope[${sid}]"][value="${mode}"]`);
            if (radio) radio.checked = true;
            setScope(sid, mode);
            toggleSectionPicker(sid, cb.checked);
        });
        assignModal.classList.add('open');
    }
    function closeAssign() { assignModal.classList.remove('open'); }
    assignModal.addEventListener('click', e => { if (e.target === assignModal) closeAssign(); });

    /* ── Search + status filter + pagination over the faculty table.
       Every row is already rendered server-side; this just narrows which
       ones are visible and slices the result into pages, so a growing
       faculty roster doesn't turn into one endless table. ── */
    (function () {
        const searchInput = document.getElementById('facultySearchInput');
        const filterBtn = document.getElementById('facultyFilterBtn');
        const filterMenu = document.getElementById('facultyFilterMenu');
        const tbody = document.getElementById('facultyTableBody');
        const pagination = document.getElementById('facultyPagination');
        const noResults = document.getElementById('facultyNoResults');
        if (!tbody) return;

        const allRows = Array.from(tbody.querySelectorAll('.faculty-row'));
        const pageSize = 10;
        let statusFilter = '';
        let currentPage = 1;

        function matches(row) {
            const q = (searchInput?.value || '').trim().toLowerCase();
            const searchOk = !q || row.dataset.name.includes(q) || row.dataset.email.includes(q);
            const statusOk = !statusFilter || row.dataset.status === statusFilter;
            return searchOk && statusOk;
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
            render();
        });

        if (searchInput) {
            searchInput.addEventListener('input', () => { currentPage = 1; render(); });
        }

        if (filterBtn && filterMenu) {
            filterBtn.addEventListener('click', e => {
                e.stopPropagation();
                filterMenu.classList.toggle('open');
            });
            filterMenu.querySelectorAll('button[data-status]').forEach(opt => {
                opt.addEventListener('click', () => {
                    statusFilter = opt.dataset.status;
                    filterMenu.querySelectorAll('button').forEach(b => b.classList.remove('selected'));
                    opt.classList.add('selected');
                    filterBtn.classList.toggle('active-filter', statusFilter !== '');
                    filterMenu.classList.remove('open');
                    currentPage = 1;
                    render();
                });
            });
            document.addEventListener('click', () => filterMenu.classList.remove('open'));
        }

        render();
    })();
</script>
@include('chair.partials.subject-section-script')

<script>
(function () {
    const P = Viz.palette;
    const faculty = @json($facultyRows);
    const subjects = @json($subjectRows);
    const pluck = (rows, key) => rows.map((row) => row[key]);

    const contributors = faculty.filter((member) => member.questions > 0);
    const flagged = faculty.filter((member) => member.quality_flags > 0);

    /* ── Contributions per faculty ──────────────────────────────────────── */
    if (contributors.length) {
        Viz.chart('vizFacultyContrib', {
            type: 'bar',
            data: {
                labels: pluck(contributors, 'name'),
                datasets: [
                    Viz.stacked({ label: 'Active', data: pluck(contributors, 'active'), backgroundColor: P.s1, maxBarThickness: 20 }),
                    Viz.stacked({ label: 'Draft', data: pluck(contributors, 'draft'), backgroundColor: P.s2, maxBarThickness: 20 }),
                ],
            },
            options: {
                indexAxis: 'y',
                scales: {
                    x: Viz.countAxis({ stacked: true, title: { display: true, text: 'Questions written', color: P.muted } }),
                    y: Viz.catAxis({ stacked: true }),
                },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { mode: 'index', callbacks: { afterBody: (items) => {
                        const member = contributors[items[0].dataIndex];
                        return member.variants + ' variants · ' + (member.accuracy === null ? 'no answers yet' : member.accuracy + '% student accuracy');
                    } } },
                },
            },
        });
    } else {
        document.getElementById('vizFacultyContrib').outerHTML = '<div class="viz-empty">No faculty member has contributed a question yet.</div>';
    }

    /* ── Questions flagged for review ───────────────────────────────────── */
    if (flagged.length) {
        Viz.chart('vizFacultyFlags', {
            type: 'bar',
            data: {
                labels: pluck(flagged, 'name'),
                datasets: [Viz.bar({ label: 'Flagged', data: pluck(flagged, 'quality_flags'), backgroundColor: P.s1, maxBarThickness: 20 })],
            },
            options: {
                indexAxis: 'y',
                layout: { padding: { right: 34 } },
                scales: { x: Viz.countAxis(), y: Viz.catAxis() },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (c) => {
                        const member = flagged[c.dataIndex];
                        return member.quality_flags + ' of ' + member.active + ' active questions flagged · ' + member.unused + ' never used';
                    } } },
                },
            },
            plugins: [Viz.endLabels()],
        });
    } else {
        document.getElementById('vizFacultyFlags').outerHTML = '<div class="viz-empty">No question is currently flagged for review.</div>';
    }

    /* ── Student accuracy per subject ───────────────────────────────────── */
    Viz.chart('vizSubjectAccuracy', {
        type: 'bar',
        data: {
            labels: pluck(subjects, 'code'),
            datasets: [Viz.bar({
                label: 'Accuracy',
                data: subjects.map((s) => s.accuracy === null ? 0 : s.accuracy),
                backgroundColor: P.s1,
            })],
        },
        options: {
            scales: { y: Viz.percentAxis(), x: Viz.catAxis() },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => {
                    const s = subjects[c.dataIndex];
                    return s.accuracy === null
                        ? 'No answers recorded yet'
                        : s.accuracy + '% across ' + s.answered.toLocaleString() + ' answers';
                } } },
            },
        },
    });

    /* ── Authoring capacity per subject ─────────────────────────────────── */
    Viz.chart('vizCapacity', {
        type: 'bar',
        data: {
            labels: pluck(subjects, 'code'),
            datasets: [
                Viz.bar({ label: 'Assigned faculty', data: pluck(subjects, 'assigned_faculty'), backgroundColor: P.s1 }),
                Viz.bar({ label: 'Faculty who have written questions', data: pluck(subjects, 'contributors'), backgroundColor: P.s2 }),
            ],
        },
        options: {
            scales: { y: Viz.countAxis(), x: Viz.catAxis() },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { mode: 'index', callbacks: { afterBody: (items) => {
                    const s = subjects[items[0].dataIndex];
                    return s.questions.toLocaleString() + ' questions in the bank (' + s.active + ' active)';
                } } },
            },
        },
    });
})();
</script>

    @include('partials.alerts')
</body>
</html>
