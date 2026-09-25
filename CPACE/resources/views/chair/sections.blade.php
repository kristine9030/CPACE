<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sections - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── KPI cards — same treatment as the other chair pages: a darker,
           more pronounced shadow than the rest of the page's cards, a bold
           dark title, an inline unit next to the number, and a dashed-border
           context line underneath explaining what the number means. ── */
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
        @media(max-width:1050px) { .stats-row { grid-template-columns:repeat(2,1fr); } }

        /* ── Section rows ── */
        .section-cell { display:flex; align-items:center; gap:11px; }
        .section-avatar {
            width:36px; height:36px; border-radius:10px; flex-shrink:0;
            display:flex; align-items:center; justify-content:center;
            font-size:12.5px; font-weight:700; color:#fff;
            background:linear-gradient(155deg, var(--primary), #9b3a3a);
        }
        .section-avatar.na { background:#e5e7eb; color:#9ca3af; }
        .section-name { font-weight:700; color:#1a1a1a; font-size:13px; }
        .count-chip {
            display:inline-flex; align-items:center; gap:6px; font-size:12.5px; color:#444; font-weight:600;
        }
        .count-chip i { font-size:11px; color:#bbb; }
        .count-chip.warn { color:#b45309; }
        .count-chip.warn i { color:#d97706; }
        .needs-faculty-tag {
            display:inline-flex; align-items:center; gap:4px; margin-left:8px;
            padding:2px 8px; border-radius:12px; font-size:9px; font-weight:700;
            background:#fef3c7; color:#b45309; letter-spacing:.2px;
        }

        .action-btn { width:30px; height:30px; border:none; border-radius:7px; cursor:pointer; font-size:12px; display:inline-flex; align-items:center; justify-content:center; transition:all .2s; margin-left:6px; }
        .ab-edit { background:#dbeafe; color:#2563eb; }
        .ab-edit:hover { background:#bfdbfe; }
        .ab-toggle { background:#fef3c7; color:#b45309; }
        .ab-toggle:hover { background:#fde68a; }
        .ab-toggle.is-deactivate { background:#fde8e8; color:#b91c1c; }
        .ab-toggle.is-deactivate:hover { background:#fbd4d4; }
        .ab-delete { background:#fde8e8; color:#b91c1c; }
        .ab-delete:hover { background:#fbd4d4; }
        .year-pill { display:inline-flex; align-items:center; padding:3px 10px; border-radius:20px; font-size:10px; font-weight:700; background:#eef2ff; color:#4338ca; }
        .year-pill.na { background:#f3f4f6; color:#9ca3af; }
        tr.is-inactive .section-name,
        tr.is-inactive .count-chip { opacity:.55; }
        tr.is-inactive .section-avatar { filter:grayscale(1); opacity:.5; }
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:2000;align-items:center;justify-content:center;padding:20px; }
        .modal-overlay.open { display:flex; }
        .modal { background:#fff;border-radius:16px;width:100%;max-width:420px;padding:24px;max-height:90vh;overflow-y:auto; }
        .modal h3 { font-size:16px;color:#1a1a1a;margin-bottom:4px; }
        .modal-sub { font-size:11px;color:#999;margin-bottom:18px; }
        .modal-actions { display:flex;justify-content:flex-end;gap:9px;margin-top:20px; }

        /* ── Section roster modal ── */
        .section-cell.is-clickable { cursor:pointer; }
        .section-cell.is-clickable:hover .section-name { color:var(--primary); text-decoration:underline; }
        .modal.roster { max-width:560px; }
        .roster-tabs { display:flex; gap:4px; border-bottom:1px solid #eee; margin-bottom:14px; }
        .roster-tab { background:none; border:none; padding:9px 14px; font:inherit; font-size:12.5px; font-weight:600; color:#888; cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-1px; }
        .roster-tab.active { color:var(--primary); border-bottom-color:var(--primary); }
        .roster-tab .tab-count { background:#f3f4f6; color:#666; border-radius:10px; padding:1px 7px; font-size:10.5px; margin-left:4px; }
        .roster-search { width:100%; padding:9px 12px; border:1px solid #e5e7eb; border-radius:9px; font:inherit; font-size:12.5px; margin-bottom:10px; }
        .roster-list { max-height:340px; overflow-y:auto; border:1px solid #f0f0f0; border-radius:10px; }
        .roster-row { display:flex; align-items:center; gap:10px; padding:9px 12px; border-bottom:1px solid #f5f5f5; font-size:12.5px; }
        .roster-row:last-child { border-bottom:none; }
        label.roster-row { cursor:pointer; }
        label.roster-row:hover { background:#fafafa; }
        .roster-row .r-name { font-weight:600; color:#1a1a1a; }
        .roster-row .r-meta { font-size:11px; color:#999; }
        .roster-row .r-body { flex:1; min-width:0; }
        .roster-row .r-tag { font-size:10px; font-weight:700; background:#fef3c7; color:#b45309; border-radius:10px; padding:2px 8px; white-space:nowrap; }
        .roster-empty { padding:26px; text-align:center; color:#999; font-size:12.5px; }
        .roster-foot { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top:14px; }
        .roster-foot .sel-count { font-size:12px; color:#666; }
        .roster-error { color:#b91c1c; font-size:12px; margin-top:8px; }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'sections'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Sections</div>
                <div class="page-sub">The catalog of student sections and year levels used when restricting a faculty's subject assignment, and to break down analytics by cohort.</div>
            </div>
        </div>
        <div class="topbar-right">
            <button type="button" class="btn btn-primary" onclick="openSection()"><i class="fas fa-plus"></i> Add Section</button>
            @include('partials.topbar-actions')
        </div>
    </div>

    @php
        $sectionsColl = collect($sections);
        $totalSections = $sectionsColl->count();
        $activeSections = $sectionsColl->where('is_active', true)->count();
        $inactiveSections = $totalSections - $activeSections;
        $needsFaculty = $sectionsColl->where('is_active', true)->where('faculty_count', 0)->count();
        $totalStudents = $sectionsColl->sum('student_count');
        $avgPerSection = $totalSections > 0 ? round($totalStudents / $totalSections, 1) : 0;

        $summaryCards = [
            [
                'value' => $totalSections, 'unit' => 'Sections', 'label' => 'All Sections',
                'tone' => 'si-blue', 'icon' => 'fa-people-group',
                'context' => $totalSections > 0
                    ? '<strong>' . $activeSections . ' active</strong> across the curriculum.'
                    : 'No sections yet — add the first one to begin.',
            ],
            [
                'value' => $totalStudents, 'unit' => 'Students', 'label' => 'Sectioned Students',
                'tone' => 'si-green', 'icon' => 'fa-user-graduate',
                'context' => $totalSections > 0
                    ? '<strong>' . $avgPerSection . ' students</strong> per section on average'
                        . ($unsectioned > 0 ? ', <strong style="color:var(--accent);">' . $unsectioned . '</strong> enrolled ' . ($unsectioned === 1 ? 'student is' : 'students are') . ' not assigned to a section yet.' : '.')
                    : 'No enrollment to report yet.',
            ],
            [
                'value' => $needsFaculty, 'unit' => 'Sections', 'label' => 'Needs Faculty',
                'tone' => 'si-orange', 'icon' => 'fa-chalkboard-user',
                'context' => $needsFaculty > 0
                    ? '<strong style="color:var(--accent);">' . $needsFaculty . ' active ' . ($needsFaculty === 1 ? 'section has' : 'sections have') . '</strong> no faculty assigned.'
                    : '<strong style="color:#059669;">Every active section</strong> has faculty assigned.',
            ],
            [
                'value' => $inactiveSections, 'unit' => 'Hidden', 'label' => 'Inactive Sections',
                'tone' => 'si-red', 'icon' => 'fa-eye-slash',
                'context' => $inactiveSections > 0
                    ? '<strong style="color:var(--accent);">' . $inactiveSections . ' ' . ($inactiveSections === 1 ? 'section is' : 'sections are') . '</strong> hidden from assignment.'
                    : '<strong style="color:#059669;">All sections</strong> are selectable for assignment.',
            ],
        ];
    @endphp
    <div class="stats-row">
        @foreach ($summaryCards as $card)
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-lbl">{{ $card['label'] }}</div>
                        <div class="stat-num">{{ $card['value'] }} <span class="stat-unit">{{ $card['unit'] }}</span></div>
                    </div>
                    <div class="stat-icon {{ $card['tone'] }}">
                        <i class="fas {{ $card['icon'] }}"></i>
                    </div>
                </div>
                <div class="stat-context">{!! $card['context'] !!}</div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-head"><span class="card-title">All Sections ({{ $totalSections }})</span></div>
        <table>
            <thead>
                <tr><th>Section</th><th>Year Level</th><th>Students</th><th>Faculty Assigned</th><th>Status</th><th style="text-align:right;">Actions</th></tr>
            </thead>
            <tbody>
            @forelse ($sections as $s)
                <tr class="{{ $s->is_active ? '' : 'is-inactive' }}">
                    <td>
                        <div class="section-cell is-clickable" role="button" tabindex="0" title="View students in {{ $s->name }}"
                             onclick='openRoster(@json(["id" => $s->id, "name" => $s->name]))'
                             onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
                            <div class="section-avatar {{ $s->year_level ? '' : 'na' }}">
                                {{ $s->year_level ? 'Y' . $s->year_level : '—' }}
                            </div>
                            <span class="section-name">{{ $s->name }}</span>
                        </div>
                    </td>
                    <td>
                        @if ($s->year_level)
                            <span class="year-pill">{{ \App\Models\Section::YEAR_LABELS[$s->year_level] ?? $s->year_level }}</span>
                        @else
                            <span class="year-pill na"><i class="fas fa-triangle-exclamation"></i> Not set</span>
                        @endif
                    </td>
                    <td>
                        <span class="count-chip"><i class="fas fa-user-graduate"></i> {{ $s->student_count }}</span>
                    </td>
                    <td>
                        <span class="count-chip {{ $s->is_active && $s->faculty_count === 0 ? 'warn' : '' }}">
                            <i class="fas fa-chalkboard-user"></i> {{ $s->faculty_count }}
                        </span>
                        @if ($s->is_active && $s->faculty_count === 0)
                            <span class="needs-faculty-tag"><i class="fas fa-triangle-exclamation"></i> Unassigned</span>
                        @endif
                    </td>
                    <td>
                        @if ($s->is_active)
                            <span class="pill pill-on"><i class="fas fa-check"></i> Active</span>
                        @else
                            <span class="pill pill-off"><i class="fas fa-ban"></i> Inactive</span>
                        @endif
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <button type="button" class="action-btn ab-edit" title="Edit"
                                onclick='openSection(@json(["id" => $s->id, "name" => $s->name, "year_level" => $s->year_level]))'>
                            <i class="fas fa-pen"></i>
                        </button>
                        <form method="POST" action="{{ route('chair.sections.toggle', $s->id) }}" style="display:inline;"
                              data-confirm="{{ $s->is_active
                                  ? 'This section will no longer be selectable when assigning faculty to sections.'
                                  : 'This section will become selectable again when assigning faculty to sections.' }}"
                              data-confirm-title="{{ $s->is_active ? 'Deactivate this section?' : 'Activate this section?' }}"
                              data-confirm-ok="{{ $s->is_active ? 'Yes, deactivate' : 'Yes, activate' }}"
                              data-confirm-icon="question">
                            @csrf
                            <button type="submit" class="action-btn ab-toggle {{ $s->is_active ? 'is-deactivate' : '' }}" title="{{ $s->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fas fa-power-off"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('chair.sections.destroy', $s->id) }}" style="display:inline;"
                              data-confirm="{{ $s->student_count > 0 || $s->faculty_count > 0
                                  ? 'This section still has ' . $s->student_count . ' student(s) and ' . $s->faculty_count . ' faculty assignment(s) tied to it — it cannot be removed until those are reassigned.'
                                  : 'This will permanently remove "' . $s->name . '" from the catalog. This cannot be undone.' }}"
                              data-confirm-title="Delete this section?"
                              data-confirm-ok="Yes, delete"
                              data-confirm-icon="warning"
                              data-confirm-danger>
                            @csrf @method('DELETE')
                            <button type="submit" class="action-btn ab-delete" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty"><i class="fas fa-people-group"></i><div>No sections yet. Add one above.</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</main>

<div class="modal-overlay" id="sectionModal">
    <div class="modal">
        <h3 id="sectionModalTitle">Add Section</h3>
        <div class="modal-sub">A section belongs to one year level, so analytics and faculty assignment can be scoped by cohort.</div>
        <form method="POST" id="sectionForm" action="{{ route('chair.sections.store') }}">
            @csrf <input type="hidden" name="_method" id="sectionMethod" value="POST">
            <div class="form-group">
                <label>Section Name</label>
                <input type="text" name="name" id="sectionName" placeholder="e.g. BSA-3A" maxlength="30" required>
            </div>
            <div class="form-group">
                <label>Year Level</label>
                <select name="year_level" id="sectionYear" required>
                    <option value="">— Select year level —</option>
                    @foreach(\App\Models\Section::YEAR_LABELS as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeModal('sectionModal')">Cancel</button>
                <button class="btn btn-primary"><i class="fas fa-save"></i> Save Section</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="rosterModal">
    <div class="modal roster">
        <h3 id="rosterTitle">Section</h3>
        <div class="modal-sub" id="rosterSub">Students currently in this section.</div>
        <div class="roster-tabs">
            <button type="button" class="roster-tab active" data-tab="members" onclick="rosterTab('members')">Students <span class="tab-count" id="membersCount">0</span></button>
            <button type="button" class="roster-tab" data-tab="add" onclick="rosterTab('add')">Add Students</button>
        </div>
        <input type="search" class="roster-search" id="rosterSearch" placeholder="Search by name, email or student number…" oninput="renderRoster()">
        <div class="roster-list" id="rosterList"></div>
        <div class="roster-foot" id="rosterFoot" style="display:none;">
            <label style="font-size:12px;color:#666;cursor:pointer;"><input type="checkbox" id="rosterAll" onchange="toggleAllVisible(this.checked)"> Select all shown</label>
            <span class="sel-count" id="rosterSelCount">0 selected</span>
            <button type="button" class="btn btn-primary" id="rosterAddBtn" onclick="addSelected()" disabled><i class="fas fa-user-plus"></i> Add to section</button>
        </div>
        <div class="roster-error" id="rosterError"></div>
        <div class="modal-actions"><button type="button" class="btn btn-ghost" onclick="closeModal('rosterModal')">Close</button></div>
    </div>
</div>

<script>
const roster = { section: null, tab: 'members', members: [], available: [], selected: new Set() };
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

async function openRoster(section) {
    roster.section = section; roster.selected = new Set(); roster.tab = 'members';
    document.getElementById('rosterTitle').textContent = section.name;
    document.getElementById('rosterSearch').value = '';
    document.getElementById('rosterError').textContent = '';
    document.getElementById('rosterList').innerHTML = '<div class="roster-empty">Loading…</div>';
    document.getElementById('rosterModal').classList.add('open');
    rosterTab('members');
    await loadRoster();
}
async function loadRoster() {
    try {
        const res = await fetch(`/chair/sections/${roster.section.id}/students`, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error();
        const data = await res.json();
        roster.members = data.members; roster.available = data.available;
        renderRoster();
    } catch (e) {
        document.getElementById('rosterList').innerHTML = '<div class="roster-empty">Could not load students. Please try again.</div>';
    }
}
function rosterTab(tab) {
    roster.tab = tab;
    document.querySelectorAll('.roster-tab').forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
    document.getElementById('rosterSearch').value = '';
    document.getElementById('rosterFoot').style.display = tab === 'add' ? 'flex' : 'none';
    renderRoster();
}
function renderRoster() {
    const q = document.getElementById('rosterSearch').value.trim().toLowerCase();
    const matches = s => !q || [s.name, s.email, s.student_number].some(v => (v ?? '').toLowerCase().includes(q));
    document.getElementById('membersCount').textContent = roster.members.length;
    const list = document.getElementById('rosterList');
    const adding = roster.tab === 'add';
    const rows = (adding ? roster.available : roster.members).filter(matches);
    document.getElementById('rosterSub').textContent = adding
        ? `Tick the students to place in ${roster.section.name}. Students already in another section will be moved.`
        : `${roster.members.length} student(s) currently in ${roster.section.name}.`;
    if (!rows.length) {
        list.innerHTML = `<div class="roster-empty">${q ? 'No students match your search.' : (adding ? 'Every enrolled student is already in this section.' : 'No students in this section yet. Use the Add Students tab.')}</div>`;
    } else if (adding) {
        list.innerHTML = rows.map(s => `
            <label class="roster-row">
                <input type="checkbox" value="${s.id}" ${roster.selected.has(s.id) ? 'checked' : ''} onchange="toggleStudent(${s.id}, this.checked)">
                <div class="r-body"><div class="r-name">${esc(s.name)}</div><div class="r-meta">${esc(s.student_number || 'No student no.')} · ${esc(s.email)}</div></div>
                ${s.section ? `<span class="r-tag">In ${esc(s.section)}</span>` : '<span class="r-tag" style="background:#e0f2fe;color:#0369a1;">No section</span>'}
            </label>`).join('');
    } else {
        list.innerHTML = rows.map(s => `
            <div class="roster-row">
                <div class="r-body"><div class="r-name">${esc(s.name)}</div><div class="r-meta">${esc(s.student_number || 'No student no.')} · ${esc(s.email)}</div></div>
            </div>`).join('');
    }
    if (adding) syncSelection();
}
function toggleStudent(id, on) { on ? roster.selected.add(id) : roster.selected.delete(id); syncSelection(); }
function toggleAllVisible(on) {
    document.querySelectorAll('#rosterList input[type=checkbox]').forEach(cb => { cb.checked = on; toggleStudent(Number(cb.value), on); });
}
function syncSelection() {
    const n = roster.selected.size;
    document.getElementById('rosterSelCount').textContent = `${n} selected`;
    document.getElementById('rosterAddBtn').disabled = n === 0;
    const boxes = [...document.querySelectorAll('#rosterList input[type=checkbox]')];
    document.getElementById('rosterAll').checked = boxes.length > 0 && boxes.every(cb => cb.checked);
}
async function addSelected() {
    const btn = document.getElementById('rosterAddBtn');
    btn.disabled = true;
    document.getElementById('rosterError').textContent = '';
    try {
        const res = await fetch(`/chair/sections/${roster.section.id}/students`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
            body: JSON.stringify({ student_ids: [...roster.selected] }),
        });
        if (!res.ok) throw new Error();
        // Counts on the page are server-rendered, so reload to refresh them.
        location.reload();
    } catch (e) {
        document.getElementById('rosterError').textContent = 'Could not add the students. Please try again.';
        btn.disabled = false;
    }
}

function openSection(section = null) {
    const f = document.getElementById('sectionForm');
    document.getElementById('sectionModalTitle').textContent = section ? 'Edit Section' : 'Add Section';
    f.action = section ? `/chair/sections/${section.id}` : @json(route('chair.sections.store'));
    document.getElementById('sectionMethod').value = section ? 'PUT' : 'POST';
    document.getElementById('sectionName').value = section?.name ?? '';
    document.getElementById('sectionYear').value = section?.year_level ?? '';
    document.getElementById('sectionModal').classList.add('open');
}
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(modal => modal.addEventListener('click', event => { if (event.target === modal) modal.classList.remove('open'); }));
document.addEventListener('keydown', event => { if (event.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(modal => modal.classList.remove('open')); });
</script>

    @include('partials.alerts')
</body>
</html>
