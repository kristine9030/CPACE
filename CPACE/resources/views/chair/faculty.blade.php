<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Accounts - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'faculty'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Faculty Accounts</div>
                <div class="page-sub">Create logins for faculty and assign them to CPALE subjects.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('chair.faculty.performance') }}" class="btn btn-outline"><i class="fas fa-chart-column"></i> Performance Report</a>
            <a href="{{ route('chair.faculty.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Faculty</a>
            @include('partials.topbar-actions')
        </div>
    </div>

    {{-- Flash messages surface as SweetAlert popups via partials.alerts --}}

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

    @include('partials.alerts')
</body>
</html>
