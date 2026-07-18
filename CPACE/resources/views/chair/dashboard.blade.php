<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Chair Dashboard - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── Dashboard-specific responsive ── */
        @media (max-width: 768px) {
            .dash-content-grid {
                grid-template-columns: 1fr !important;
            }
            .dash-table-wrap {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .risk-meta { display:none; }
        }
        .risk-summary { display:flex; align-items:center; gap:8px; }
        .risk-count { min-width:24px; height:24px; padding:0 7px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; background:#fde8e8; color:var(--accent); font-size:11px; font-weight:700; }
        .risk-list { display:flex; flex-direction:column; }
        .risk-row { display:grid; grid-template-columns:minmax(190px, 1.4fr) minmax(160px, 1fr) 100px 120px 78px; gap:14px; align-items:center; padding:13px 10px; border-top:1px solid #f5f5f5; }
        .risk-row:hover { background:#fafafa; }
        .risk-student { display:flex; align-items:center; gap:11px; min-width:0; }
        .risk-name { font-size:13px; font-weight:600; color:#1a1a1a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .risk-email, .risk-meta { font-size:10.5px; color:#aaa; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .risk-reasons { display:flex; flex-wrap:wrap; gap:5px; }
        .risk-reason { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:12px; background:#fef3c7; color:#b45309; font-size:10px; font-weight:600; }
        .risk-reason.low { background:#fde8e8; color:#b91c1c; }
        .risk-score { font-size:13px; font-weight:700; color:var(--accent); }
        .risk-score.na { color:#aaa; font-weight:500; }
        .risk-priority { display:inline-flex; align-items:center; justify-content:center; padding:4px 9px; border-radius:20px; font-size:10px; font-weight:700; }
        .risk-priority.high { background:#fde8e8; color:#b91c1c; }
        .risk-priority.watch { background:#fef3c7; color:#b45309; }
        .risk-head { color:#aaa; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.4px; padding-top:0; border-top:0; }
        .risk-empty { padding:24px 10px 8px; text-align:center; color:#999; font-size:12px; }
        .risk-empty i { color:var(--green); margin-right:6px; }
        .analytics-strip { margin:18px 0; }
        .analytics-head { display:flex; align-items:end; justify-content:space-between; gap:12px; margin-bottom:10px; }
        .analytics-title { font-size:14px; font-weight:700; color:#222; }
        .analytics-sub { font-size:10.5px; color:#999; margin-top:2px; }
        .analytics-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
        .analytics-card { display:block; background:#fff; border:1px solid #eee; border-radius:12px; padding:15px; text-decoration:none; transition:.18s ease; }
        .analytics-card:hover { transform:translateY(-2px); border-color:#ddd; box-shadow:0 8px 22px rgba(0,0,0,.06); }
        .analytics-card-top { display:flex; align-items:center; justify-content:space-between; gap:8px; }
        .analytics-card-label { color:#777; font-size:10.5px; font-weight:600; }
        .analytics-card-icon { width:30px; height:30px; border-radius:9px; display:grid; place-items:center; background:#fef2f2; color:var(--accent); }
        .analytics-card-value { margin-top:10px; font-size:23px; line-height:1; font-weight:700; color:#1b1b1b; }
        .analytics-card-note { margin-top:7px; font-size:9.5px; color:#aaa; min-height:14px; }
        .trend-up { color:#047857; } .trend-down { color:#b91c1c; }
        @media (max-width: 980px) {
            .risk-row { grid-template-columns:minmax(180px, 1.3fr) minmax(145px, 1fr) 85px 70px; }
            .risk-last { display:none; }
            .analytics-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
        }
        @media (max-width: 620px) {
            .risk-row { grid-template-columns:1fr auto; gap:9px; }
            .risk-head { display:none; }
            .risk-reasons { grid-column:1 / -1; padding-left:49px; }
            .risk-score, .risk-last { display:none; }
            .analytics-grid { grid-template-columns:1fr; }
        }
    </style>
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

    <section class="analytics-strip" aria-labelledby="analytics-title">
        <div class="analytics-head">
            <div><div class="analytics-title" id="analytics-title">System Analytics Snapshot</div><div class="analytics-sub">Live class-level and test-bank indicators</div></div>
            <a href="{{ route('chair.analytics.performance') }}" class="card-link">Open Analytics</a>
        </div>
        <div class="analytics-grid">
            <a class="analytics-card" href="{{ route('chair.analytics.performance') }}">
                <div class="analytics-card-top"><span class="analytics-card-label">Class-Level Accuracy</span><span class="analytics-card-icon"><i class="fas fa-bullseye"></i></span></div>
                <div class="analytics-card-value">{{ $analytics['class_accuracy'] === null ? '—' : $analytics['class_accuracy'].'%' }}</div>
                <div class="analytics-card-note">{{ $analytics['weakest_subject'] ? 'Weakest: '.$analytics['weakest_subject']['code'].' at '.$analytics['weakest_subject']['accuracy'].'%' : 'Waiting for student attempts' }}</div>
            </a>
            <a class="analytics-card" href="{{ route('chair.analytics.performance') }}#readiness-trend">
                <div class="analytics-card-top"><span class="analytics-card-label">Board Readiness</span><span class="analytics-card-icon"><i class="fas fa-chart-line"></i></span></div>
                <div class="analytics-card-value">{{ $analytics['readiness_rate'] === null ? '—' : $analytics['readiness_rate'].'%' }}</div>
                <div class="analytics-card-note {{ ($analytics['readiness_change'] ?? 0) >= 0 ? 'trend-up' : 'trend-down' }}">{{ $analytics['readiness_change'] === null ? 'Trend begins after two periods' : (($analytics['readiness_change'] >= 0 ? '+' : '').$analytics['readiness_change'].' pts this week') }}</div>
            </a>
            <a class="analytics-card" href="{{ route('chair.analytics.test-bank-coverage') }}">
                <div class="analytics-card-top"><span class="analytics-card-label">Thin Test-Bank Areas</span><span class="analytics-card-icon"><i class="fas fa-layer-group"></i></span></div>
                <div class="analytics-card-value">{{ $analytics['thin_topics'] }}</div>
                <div class="analytics-card-note">Topics below 25 active questions</div>
            </a>
            <a class="analytics-card" href="{{ route('chair.analytics.performance') }}#pass-projection">
                <div class="analytics-card-top"><span class="analytics-card-label">Pass Projection</span><span class="analytics-card-icon"><i class="fas fa-graduation-cap"></i></span></div>
                <div class="analytics-card-value">{{ $analytics['pass_projection'] === null ? '—' : $analytics['pass_projection'].'%' }}</div>
                <div class="analytics-card-note">Readiness-based · {{ $analytics['eligible_students'] }} eligible students</div>
            </a>
        </div>
    </section>

    <div class="dash-content-grid" style="display:grid; grid-template-columns:1fr 340px; gap:18px;">
        <div class="card">
            <div class="card-head">
                <span class="card-title">Subject Coverage</span>
                <a href="{{ route('chair.subjects') }}" class="card-link">Manage Assignments</a>
            </div>
            <div class="dash-table-wrap">
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
            </div>{{-- /.dash-table-wrap --}}
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

    <div class="card" style="margin-top:18px;">
        <div class="card-head">
            <div class="risk-summary">
                <span class="card-title"><i class="fas fa-triangle-exclamation" style="color:var(--accent);margin-right:7px;"></i>At-Risk Student Alerts</span>
                <span class="risk-count">{{ $atRiskStudents->count() }}</span>
            </div>
            <span class="risk-meta">Low readiness (&lt;60% after 5+ items) or inactive for 7+ days</span>
        </div>

        @if($atRiskStudents->isNotEmpty())
            <div class="risk-list">
                <div class="risk-row risk-head" aria-hidden="true">
                    <span>Student</span><span>Alert reason</span><span>Readiness</span><span class="risk-last">Last active</span><span>Priority</span>
                </div>
                @foreach($atRiskStudents as $student)
                    <div class="risk-row">
                        <div class="risk-student">
                            <div class="user-av" style="background:var(--primary);">{{ $student['initials'] }}</div>
                            <div style="min-width:0;">
                                <div class="risk-name">{{ $student['name'] }}</div>
                                <div class="risk-email">{{ $student['email'] }}</div>
                            </div>
                        </div>
                        <div class="risk-reasons">
                            @foreach($student['reasons'] as $reason)
                                <span class="risk-reason {{ $reason === 'Low readiness' ? 'low' : '' }}">
                                    <i class="fas {{ $reason === 'Low readiness' ? 'fa-chart-line' : 'fa-clock' }}"></i>{{ $reason }}
                                </span>
                            @endforeach
                        </div>
                        <div class="risk-score {{ $student['score'] === null ? 'na' : '' }}">
                            {{ $student['score'] === null ? 'Not rated' : $student['score'].'%' }}
                            <div class="risk-meta">{{ $student['attempted'] }} items</div>
                        </div>
                        <div class="risk-last">
                            <div style="font-size:12px;color:#555;">{{ $student['last_active'] ? $student['last_active']->diffForHumans() : 'Never' }}</div>
                            <div class="risk-meta">{{ $student['quizzes'] }} completed quiz{{ $student['quizzes'] === 1 ? '' : 'zes' }}</div>
                        </div>
                        <span class="risk-priority {{ $student['priority'] }}">{{ $student['priority'] === 'high' ? 'High' : 'Watch' }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="risk-empty"><i class="fas fa-circle-check"></i>No students currently need intervention.</div>
        @endif
    </div>
</main>
</body>
</html>
