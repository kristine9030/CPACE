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
        .add-section-form { display:flex; gap:10px; align-items:flex-end; max-width:420px; }
        .add-section-form .form-group { margin-bottom:0; flex:1; }
        .action-btn { width:30px; height:30px; border:none; border-radius:7px; cursor:pointer; font-size:12px; display:inline-flex; align-items:center; justify-content:center; transition:all .2s; }
        .ab-toggle { background:#fef3c7; color:#d97706; }
        .ab-toggle:hover { background:#fde68a; }
        @media (max-width: 480px) {
            .add-section-form { flex-direction:column; align-items:stretch; max-width:none; }
        }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'sections'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Sections</div>
                <div class="page-sub">The catalog of student sections used when restricting a faculty's subject assignment to specific sections.</div>
            </div>
        </div>
        <div class="topbar-right">
            @include('partials.topbar-actions')
        </div>
    </div>

    <div class="card">
        <div class="card-head"><span class="card-title">Add a Section</span></div>
        <form method="POST" action="{{ route('chair.sections.store') }}" class="add-section-form">
            @csrf
            <div class="form-group">
                <label>Section Name</label>
                <input type="text" name="name" placeholder="e.g. BSA-3A" maxlength="30" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Section</button>
        </form>
    </div>

    <div class="card">
        <div class="card-head"><span class="card-title">All Sections ({{ $sections->count() }})</span></div>
        <table>
            <thead>
                <tr><th>Section</th><th>Faculty Assigned</th><th>Status</th><th style="text-align:right;">Actions</th></tr>
            </thead>
            <tbody>
            @forelse ($sections as $s)
                <tr>
                    <td style="font-weight:600; color:#1a1a1a;">{{ $s->name }}</td>
                    <td style="color:#666;">{{ $s->faculty_count }}</td>
                    <td>
                        @if ($s->is_active)
                            <span class="pill pill-on"><i class="fas fa-check"></i> Active</span>
                        @else
                            <span class="pill pill-off"><i class="fas fa-ban"></i> Inactive</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
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
                <tr><td colspan="4"><div class="empty"><i class="fas fa-people-group"></i><div>No sections yet. Add one above.</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</main>

    @include('partials.alerts')
</body>
</html>
