<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Chair Dashboard - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'dashboard'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Program Chair Dashboard</div>
                <div class="page-sub">Welcome back, {{ Auth::user()->name }}. Manage faculty and subject assignments here.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('chair.faculty.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Faculty</a>
            @include('partials.topbar-actions')
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('status') }}</div>
    @endif

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-top">
                <div><div class="stat-lbl">Faculty Members</div><div class="stat-num">{{ $stats['faculty'] }}</div></div>
                <div class="stat-icon si-red"><i class="fas fa-chalkboard-user"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div><div class="stat-lbl">CPALE Subjects</div><div class="stat-num">{{ $stats['subjects'] }}</div></div>
                <div class="stat-icon si-blue"><i class="fas fa-book-open"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div><div class="stat-lbl">Subjects Covered</div><div class="stat-num">{{ $stats['assigned'] }}</div></div>
                <div class="stat-icon si-green"><i class="fas fa-circle-check"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div><div class="stat-lbl">Unassigned</div><div class="stat-num">{{ $stats['unassigned'] }}</div></div>
                <div class="stat-icon si-orange"><i class="fas fa-triangle-exclamation"></i></div>
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 340px; gap:18px;">
        <div class="card">
            <div class="card-head">
                <span class="card-title">Subject Coverage</span>
                <a href="{{ route('chair.subjects') }}" class="card-link">Manage Assignments</a>
            </div>
            <table>
                <thead><tr><th>Subject</th><th>Faculty Assigned</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($subjects as $s)
                    <tr>
                        <td>
                            <span class="subj-badge b-{{ strtolower($s->code) }}">{{ $s->code }}</span>
                            <span style="color:#555;">{{ $s->name }}</span>
                        </td>
                        <td>{{ $s->faculty_count }}</td>
                        <td>
                            @if ($s->faculty_count > 0)
                                <span class="pill pill-on"><i class="fas fa-check"></i> Covered</span>
                            @else
                                <span class="pill pill-off"><i class="fas fa-minus"></i> No faculty</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="card-head">
                <span class="card-title">Recent Faculty</span>
                <a href="{{ route('chair.faculty') }}" class="card-link">View All</a>
            </div>
            @forelse ($faculty as $f)
                <div style="display:flex; align-items:center; gap:12px; padding:11px 0; border-bottom:1px solid #f5f5f5;">
                    <div class="user-av" style="background:var(--primary);">{{ strtoupper(substr($f->first_name,0,1)).strtoupper(substr($f->last_name,0,1)) }}</div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:13px; font-weight:600; color:#1a1a1a;">{{ $f->name }}</div>
                        <div style="font-size:11px; color:#999;">
                            {{ $f->assignedSubjects->count() ? $f->assignedSubjects->pluck('code')->join(', ') : 'No subjects yet' }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty"><i class="fas fa-user-slash"></i><div>No faculty accounts yet.</div></div>
            @endforelse
            <a href="{{ route('chair.faculty.create') }}" class="btn btn-outline btn-sm" style="margin-top:14px; width:100%; justify-content:center;"><i class="fas fa-user-plus"></i> Create Faculty Account</a>
        </div>
    </div>
</main>
</body>
</html>
