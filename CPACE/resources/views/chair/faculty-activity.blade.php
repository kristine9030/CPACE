<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $faculty->name }} Activity - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-card { display:flex; align-items:center; gap:16px; margin-bottom:18px; }
        .profile-card .user-av { width:52px; height:52px; font-size:16px; }
        .profile-name { font-size:17px; font-weight:700; color:#1a1a1a; }
        .profile-meta { font-size:11px; color:#999; margin-top:3px; }
        .activity-grid { display:grid; grid-template-columns:minmax(0,1fr) 330px; gap:18px; }
        .timeline-item { display:flex; gap:13px; padding:14px 4px; border-top:1px solid #f5f5f5; }
        .timeline-icon { width:34px; height:34px; flex-shrink:0; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:12px; }
        .timeline-icon.created { background:#d1fae5; color:#059669; }
        .timeline-icon.updated { background:#dbeafe; color:#2563eb; }
        .timeline-title { font-size:12.5px; font-weight:600; color:#222; }
        .timeline-question { font-size:11px; color:#666; margin-top:3px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        .timeline-meta { display:flex; flex-wrap:wrap; gap:8px; font-size:10px; color:#aaa; margin-top:6px; }
        .timeline-date { margin-left:auto; flex-shrink:0; text-align:right; font-size:10.5px; color:#aaa; }
        .coverage-row { padding:12px 0; border-top:1px solid #f5f5f5; }
        .coverage-top { display:flex; justify-content:space-between; gap:12px; align-items:center; }
        .coverage-name { font-size:12px; font-weight:600; color:#333; }
        .coverage-meta { font-size:10.5px; color:#999; margin-top:4px; }
        .login-box { display:flex; align-items:center; gap:11px; background:#f8f8fa; border-radius:10px; padding:13px; margin-top:16px; }
        .login-box i { color:var(--primary); }
        @media (max-width:900px) { .activity-grid { grid-template-columns:1fr; } }
        @media (max-width:620px) { .timeline-date { display:none; } .profile-card { align-items:flex-start; } }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'faculty'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Faculty Activity Log</div>
                <div class="page-sub">Question contributions, test-bank usage, and account activity.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('chair.faculty') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Faculty List</a>
            @include('partials.topbar-actions')
        </div>
    </div>

    <div class="card profile-card">
        <div class="user-av">{{ strtoupper(substr($faculty->first_name,0,1).substr($faculty->last_name,0,1)) }}</div>
        <div style="flex:1;min-width:0;">
            <div class="profile-name">{{ $faculty->name }}</div>
            <div class="profile-meta">{{ $faculty->email }} · {{ $faculty->is_active ? 'Active account' : 'Disabled account' }}</div>
            <div style="margin-top:7px;">
                @forelse($faculty->assignedSubjects as $subject)
                    <span class="subj-badge b-{{ strtolower($subject->code) }}">{{ $subject->code }}</span>
                @empty
                    <span class="profile-meta">No assigned subjects</span>
                @endforelse
            </div>
        </div>
        <a href="{{ route('chair.faculty.edit', $faculty->id) }}" class="btn btn-ghost btn-sm"><i class="fas fa-pen"></i> Edit</a>
    </div>

    <div class="stats-row">
        <div class="stat-card"><div class="stat-top"><div><div class="stat-num">{{ $stats['questions'] }}</div><div class="stat-lbl">Questions Added</div></div><div class="stat-icon si-red"><i class="fas fa-circle-question"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-num">{{ $stats['active'] }}</div><div class="stat-lbl">Active Questions</div></div><div class="stat-icon si-green"><i class="fas fa-circle-check"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-num">{{ $stats['variants'] }}</div><div class="stat-lbl">Question Variants</div></div><div class="stat-icon si-blue"><i class="fas fa-code-branch"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-num">{{ $stats['accuracy'] ?? '—' }}{{ $stats['accuracy'] !== null ? '%' : '' }}</div><div class="stat-lbl">Student Accuracy · {{ $stats['answered'] }} answers</div></div><div class="stat-icon si-orange"><i class="fas fa-bullseye"></i></div></div></div>
    </div>

    <div class="activity-grid">
        <div class="card">
            <div class="card-head">
                <span class="card-title">Recent Test Bank Activity</span>
                <span style="font-size:10.5px;color:#aaa;">Latest 50 events</span>
            </div>
            @forelse($events as $event)
                <div class="timeline-item">
                    <div class="timeline-icon {{ $event['type'] }}"><i class="fas {{ $event['type'] === 'created' ? 'fa-plus' : 'fa-pen' }}"></i></div>
                    <div style="flex:1;min-width:0;">
                        <div class="timeline-title">{{ $event['type'] === 'created' ? 'Added a new question' : 'Updated a question' }}</div>
                        <div class="timeline-question">{{ $event['text'] }}</div>
                        <div class="timeline-meta">
                            <span class="subj-badge b-{{ strtolower($event['subject']) }}">{{ $event['subject'] }}</span>
                            <span>{{ $event['topic'] }}</span><span>·</span>
                            <span>{{ $event['answered'] }} answers</span><span>·</span>
                            <span>{{ $event['variants'] }} variants</span>
                            @unless($event['active'])<span class="pill pill-off">Draft</span>@endunless
                        </div>
                    </div>
                    <div class="timeline-date">{{ $event['date']->diffForHumans() }}<br>{{ $event['date']->format('M j, Y') }}</div>
                </div>
            @empty
                <div class="empty"><i class="fas fa-clock-rotate-left"></i><div>No question activity recorded yet.</div></div>
            @endforelse
        </div>

        <div>
            <div class="card">
                <div class="card-head"><span class="card-title">Contributions by Subject</span></div>
                @forelse($subjectContributions as $subject)
                    <div class="coverage-row">
                        <div class="coverage-top">
                            <div><span class="subj-badge b-{{ strtolower($subject['code']) }}">{{ $subject['code'] }}</span><span class="coverage-name">{{ $subject['name'] }}</span></div>
                            <strong style="font-size:14px;">{{ $subject['questions'] }}</strong>
                        </div>
                        <div class="coverage-meta">{{ $subject['active'] }} active · {{ $subject['answered'] }} student answers · {{ $subject['accuracy'] === null ? 'No accuracy yet' : $subject['accuracy'].'% accuracy' }}</div>
                    </div>
                @empty
                    <div class="empty" style="padding:20px 10px;"><div>No subject contributions yet.</div></div>
                @endforelse

                <div class="login-box">
                    <i class="fas fa-right-to-bracket"></i>
                    <div><div style="font-size:11px;font-weight:600;">Last Login</div><div class="profile-meta">{{ $faculty->last_login_at ? $faculty->last_login_at->diffForHumans().' · '.$faculty->last_login_at->format('M j, Y g:i A') : 'No login recorded' }}</div></div>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
