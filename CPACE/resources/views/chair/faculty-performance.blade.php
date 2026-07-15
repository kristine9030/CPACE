<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Performance Report - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .report-grid { display:grid; grid-template-columns:minmax(0,1.35fr) minmax(320px,.65fr); gap:18px; }
        .report-table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
        .report-table-wrap table { min-width:780px; }
        .faculty-cell { display:flex; align-items:center; gap:10px; }
        .faculty-name { font-size:12.5px; font-weight:600; color:#1a1a1a; }
        .faculty-subjects { font-size:10px; color:#aaa; margin-top:3px; }
        .metric-main { font-size:13px; font-weight:700; color:#222; }
        .metric-sub { font-size:10px; color:#aaa; margin-top:2px; }
        .quality-pill { display:inline-flex; align-items:center; gap:5px; padding:4px 9px; border-radius:15px; font-size:10px; font-weight:700; }
        .quality-pill.good { background:#d1fae5; color:#047857; }
        .quality-pill.flag { background:#fde8e8; color:#b91c1c; }
        .subject-row { padding:14px 0; border-top:1px solid #f5f5f5; }
        .subject-top { display:flex; justify-content:space-between; gap:10px; align-items:center; }
        .subject-title { font-size:12px; font-weight:600; color:#333; }
        .subject-count { font-size:15px; font-weight:700; color:#1a1a1a; }
        .subject-bar { height:6px; background:#f1f1f3; border-radius:4px; overflow:hidden; margin:8px 0 6px; }
        .subject-fill { height:100%; min-width:2px; border-radius:4px; background:var(--primary); }
        .subject-meta { display:flex; justify-content:space-between; gap:8px; font-size:10px; color:#999; }
        .legend-note { font-size:10.5px; line-height:1.6; color:#999; background:#f8f8fa; border-radius:9px; padding:11px 13px; margin-top:14px; }
        @media(max-width:1050px) { .report-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'faculty-performance'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Faculty Performance Report</div>
                <div class="page-sub">Compare test-bank contributions, subject coverage, and live question-quality signals.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('chair.faculty') }}" class="btn btn-outline"><i class="fas fa-users"></i> Faculty Accounts</a>
            @include('partials.topbar-actions')
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card"><div class="stat-top"><div><div class="stat-num">{{ $stats['questions'] }}</div><div class="stat-lbl">Total Contributions</div></div><div class="stat-icon si-red"><i class="fas fa-circle-question"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-num">{{ $stats['contributors'] }}</div><div class="stat-lbl">Contributing Faculty</div></div><div class="stat-icon si-blue"><i class="fas fa-user-pen"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-num">{{ $stats['accuracy'] ?? '—' }}{{ $stats['accuracy'] !== null ? '%' : '' }}</div><div class="stat-lbl">Overall Question Accuracy</div></div><div class="stat-icon si-green"><i class="fas fa-bullseye"></i></div></div></div>
        <div class="stat-card"><div class="stat-top"><div><div class="stat-num">{{ $stats['flags'] }}</div><div class="stat-lbl">Questions Needing Review</div></div><div class="stat-icon si-orange"><i class="fas fa-triangle-exclamation"></i></div></div></div>
    </div>

    <div class="report-grid">
        <div class="card">
            <div class="card-head">
                <span class="card-title">Faculty Contribution Ranking</span>
                <span style="font-size:10.5px;color:#aaa;">Ranked by questions contributed</span>
            </div>
            <div class="report-table-wrap">
                <table>
                    <thead><tr><th>Faculty</th><th>Questions</th><th>Variants</th><th>Student Results</th><th>Quality</th><th>Last Contribution</th><th></th></tr></thead>
                    <tbody>
                    @forelse($facultyRows as $member)
                        <tr>
                            <td>
                                <div class="faculty-cell">
                                    <div class="user-av" style="width:32px;height:32px;font-size:10px;">{{ $member['initials'] }}</div>
                                    <div><div class="faculty-name">{{ $member['name'] }}</div><div class="faculty-subjects">{{ $member['subjects'] ? implode(', ', $member['subjects']) : 'No subjects assigned' }}</div></div>
                                </div>
                            </td>
                            <td><div class="metric-main">{{ $member['questions'] }}</div><div class="metric-sub">{{ $member['active'] }} active · {{ $member['draft'] }} draft</div></td>
                            <td><div class="metric-main">{{ $member['variants'] }}</div></td>
                            <td><div class="metric-main">{{ $member['accuracy'] === null ? '—' : $member['accuracy'].'%' }}</div><div class="metric-sub">{{ $member['answered'] }} answers</div></td>
                            <td>
                                @if($member['questions'] === 0)
                                    <span class="quality-pill" style="background:#f3f4f6;color:#9ca3af;">No data</span>
                                @elseif($member['quality_flags'] > 0)
                                    <span class="quality-pill flag"><i class="fas fa-triangle-exclamation"></i>{{ $member['quality_flags'] }} review</span>
                                    <div class="metric-sub">{{ $member['unused'] }} unused</div>
                                @else
                                    <span class="quality-pill good"><i class="fas fa-check"></i>Healthy</span>
                                @endif
                            </td>
                            <td><div style="font-size:11px;color:#555;">{{ $member['last_contribution'] ? $member['last_contribution']->diffForHumans() : 'No contributions' }}</div></td>
                            <td><a href="{{ route('chair.faculty.activity', $member['id']) }}" class="btn btn-ghost btn-sm" title="View activity"><i class="fas fa-clock-rotate-left"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty"><i class="fas fa-chart-column"></i><div>No faculty accounts available.</div></div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><span class="card-title">Question Coverage by Subject</span></div>
            @forelse($subjectRows as $subject)
                <div class="subject-row">
                    <div class="subject-top">
                        <div><span class="subj-badge b-{{ strtolower($subject['code']) }}">{{ $subject['code'] }}</span><span class="subject-title">{{ $subject['name'] }}</span></div>
                        <span class="subject-count">{{ $subject['questions'] }}</span>
                    </div>
                    <div class="subject-bar"><div class="subject-fill" style="width:{{ $subject['width'] }}%;"></div></div>
                    <div class="subject-meta">
                        <span>{{ $subject['active'] }} active · {{ $subject['contributors'] }} contributor{{ $subject['contributors'] === 1 ? '' : 's' }} · {{ $subject['assigned_faculty'] }} assigned</span>
                        <span>{{ $subject['accuracy'] === null ? 'No answers' : $subject['accuracy'].'% accuracy' }}</span>
                    </div>
                    @if($subject['assigned_faculty'] === 0)
                        <div style="margin-top:6px;"><span class="pill pill-off"><i class="fas fa-user-slash"></i> No faculty assigned</span></div>
                    @endif
                </div>
            @empty
                <div class="empty"><div>No subjects available.</div></div>
            @endforelse
            <div class="legend-note"><strong>Quality review:</strong> flags questions that are unused, below 40% accuracy, or above 95% accuracy after at least five student answers. These are review signals, not faculty grades.</div>
        </div>
    </div>
</main>
</body>
</html>
