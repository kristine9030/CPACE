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
        .modal h3 { font-size:16px; color:#1a1a1a; margin-bottom:4px; }
        .modal p.sub { font-size:12px; color:#999; margin-bottom:18px; }
        .modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:20px; }
        .action-btn { width:30px; height:30px; border:none; border-radius:7px; cursor:pointer; font-size:12px; display:inline-flex; align-items:center; justify-content:center; transition:all .2s; }
        .ab-edit { background:#dbeafe; color:var(--blue); }
        .ab-edit:hover { background:#bfdbfe; }
        .ab-assign { background:#ede9fe; color:#7c3aed; }
        .ab-assign:hover { background:#ddd6fe; }
        .ab-toggle { background:#fef3c7; color:#d97706; }
        .ab-toggle:hover { background:#fde68a; }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'faculty'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <button class="toggle-btn" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <div>
                <div class="page-title">Faculty Accounts</div>
                <div class="page-sub">Create logins for faculty and assign them to CPALE subjects.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('chair.faculty.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Faculty</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-head"><span class="card-title">All Faculty ({{ $faculty->count() }})</span></div>
        <table>
            <thead>
                <tr><th>Name</th><th>Email</th><th>Assigned Subjects</th><th>Status</th><th style="text-align:right;">Actions</th></tr>
            </thead>
            <tbody>
            @forelse ($faculty as $f)
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="user-av" style="background:var(--primary); width:32px; height:32px; font-size:11px;">{{ strtoupper(substr($f->first_name,0,1)).strtoupper(substr($f->last_name,0,1)) }}</div>
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
                        @if ($f->is_active)
                            <span class="pill pill-on"><i class="fas fa-check"></i> Active</span>
                        @else
                            <span class="pill pill-off"><i class="fas fa-ban"></i> Inactive</span>
                        @endif
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <button class="action-btn ab-assign" title="Assign subjects"
                            onclick="openAssign({{ $f->id }}, '{{ addslashes($f->name) }}', {{ $f->assignedSubjects->pluck('id')->toJson() }})">
                            <i class="fas fa-layer-group"></i>
                        </button>
                        <a href="{{ route('chair.faculty.edit', $f->id) }}" class="action-btn ab-edit" title="Edit account"><i class="fas fa-pen"></i></a>
                        <form method="POST" action="{{ route('chair.faculty.toggle', $f->id) }}" style="display:inline;">
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
    </div>
</main>

<!-- ASSIGN SUBJECTS MODAL -->
<div class="modal-overlay" id="assignModal">
    <div class="modal">
        <h3>Assign Subjects</h3>
        <p class="sub" id="assignSub">Select the CPALE subjects for this faculty member.</p>
        <form method="POST" id="assignForm">
            @csrf
            <div class="check-grid">
                @foreach ($subjects as $s)
                    <label class="check-card">
                        <input type="checkbox" name="subjects[]" value="{{ $s->id }}" data-sid="{{ $s->id }}">
                        <span>
                            <span class="cc-code">{{ $s->code }}</span><br>
                            <span class="cc-name">{{ $s->name }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
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
    function openAssign(id, name, current) {
        assignForm.action = `/chair/faculty/${id}/assign`;
        document.getElementById('assignSub').textContent = `Select the CPALE subjects for ${name}.`;
        assignForm.querySelectorAll('input[name="subjects[]"]').forEach(cb => {
            cb.checked = current.includes(parseInt(cb.dataset.sid));
        });
        assignModal.classList.add('open');
    }
    function closeAssign() { assignModal.classList.remove('open'); }
    assignModal.addEventListener('click', e => { if (e.target === assignModal) closeAssign(); });
</script>
</body>
</html>
