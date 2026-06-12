<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Assignments - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .subj-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:18px; }
        .subj-card { background:#fff; border-radius:14px; padding:20px 22px; border-top:4px solid var(--primary); }
        .subj-card .sc-head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; }
        .sc-code { font-size:18px; font-weight:700; color:var(--primary); }
        .sc-name { font-size:12px; color:#888; }
        .fac-chip { display:inline-flex; align-items:center; gap:6px; background:#f5f5f7; border-radius:20px; padding:5px 12px; font-size:12px; color:#444; margin:3px 4px 3px 0; }
        .fac-chip i { color:var(--primary); font-size:10px; }
        @media (max-width:900px){ .subj-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'subjects'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <button class="toggle-btn" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <div>
                <div class="page-title">Subject Assignments</div>
                <div class="page-sub">Faculty handling each of the six CPALE subjects.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('chair.faculty.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Faculty</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('status') }}</div>
    @endif

    <div class="subj-grid">
        @foreach ($subjects as $s)
            <div class="subj-card">
                <div class="sc-head">
                    <div>
                        <div class="sc-code">{{ $s->code }}</div>
                        <div class="sc-name">{{ $s->name }}</div>
                    </div>
                    @if ($s->faculty->count())
                        <span class="pill pill-on"><i class="fas fa-check"></i> {{ $s->faculty->count() }} faculty</span>
                    @else
                        <span class="pill pill-off"><i class="fas fa-triangle-exclamation"></i> Unassigned</span>
                    @endif
                </div>
                <div>
                    @forelse ($s->faculty as $f)
                        <span class="fac-chip"><i class="fas fa-user"></i> {{ $f->name }}</span>
                    @empty
                        <span style="color:#bbb; font-size:12px;">No faculty assigned yet. Assign one from the Faculty Accounts page.</span>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <div class="card" style="margin-top:18px;">
        <div class="card-head">
            <span class="card-title">Tip</span>
            <a href="{{ route('chair.faculty') }}" class="card-link">Go to Faculty Accounts</a>
        </div>
        <p style="font-size:13px; color:#666; line-height:1.6;">
            To assign a faculty member to a subject, open <strong>Faculty Accounts</strong>, click the
            <i class="fas fa-layer-group" style="color:#7c3aed;"></i> assign icon next to their name, then tick the subjects they will handle.
            You can also set assignments while creating a new account with <strong>Add Faculty</strong>.
        </p>
    </div>
</main>
</body>
</html>
