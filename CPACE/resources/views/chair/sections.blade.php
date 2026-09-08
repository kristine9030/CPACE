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
        .action-btn { width:30px; height:30px; border:none; border-radius:7px; cursor:pointer; font-size:12px; display:inline-flex; align-items:center; justify-content:center; transition:all .2s; margin-left:6px; }
        .ab-edit { background:#e0e7ff; color:#4338ca; }
        .ab-edit:hover { background:#c7d2fe; }
        .ab-toggle { background:#fef3c7; color:#d97706; }
        .ab-toggle:hover { background:#fde68a; }
        .year-pill { display:inline-flex; align-items:center; padding:3px 10px; border-radius:20px; font-size:10px; font-weight:700; background:#eef2ff; color:#4338ca; }
        .year-pill.na { background:#f3f4f6; color:#9ca3af; }
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:2000;align-items:center;justify-content:center;padding:20px; }
        .modal-overlay.open { display:flex; }
        .modal { background:#fff;border-radius:16px;width:100%;max-width:420px;padding:24px;max-height:90vh;overflow-y:auto; }
        .modal h3 { font-size:16px;color:#1a1a1a;margin-bottom:4px; }
        .modal-sub { font-size:11px;color:#999;margin-bottom:18px; }
        .modal-actions { display:flex;justify-content:flex-end;gap:9px;margin-top:20px; }
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

    <div class="card">
        <div class="card-head"><span class="card-title">All Sections ({{ $sections->count() }})</span></div>
        <table>
            <thead>
                <tr><th>Section</th><th>Year Level</th><th>Faculty Assigned</th><th>Status</th><th style="text-align:right;">Actions</th></tr>
            </thead>
            <tbody>
            @forelse ($sections as $s)
                <tr>
                    <td style="font-weight:600; color:#1a1a1a;">{{ $s->name }}</td>
                    <td>
                        @if ($s->year_level)
                            <span class="year-pill">{{ \App\Models\Section::YEAR_LABELS[$s->year_level] ?? $s->year_level }}</span>
                        @else
                            <span class="year-pill na"><i class="fas fa-triangle-exclamation"></i> Not set</span>
                        @endif
                    </td>
                    <td style="color:#666;">{{ $s->faculty_count }}</td>
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
                            <button type="submit" class="action-btn ab-toggle" title="{{ $s->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fas fa-power-off"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="empty"><i class="fas fa-people-group"></i><div>No sections yet. Add one above.</div></div></td></tr>
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

<script>
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
