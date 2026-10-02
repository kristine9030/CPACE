<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin · Dashboard · CPACE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @include('partials.superadmin-sidebar', ['active' => 'dashboard'])
    <style>
        .metric-row { display:grid; grid-template-columns:2fr minmax(0, 1fr); gap:18px; margin-bottom:18px; }
        .feature-bar-wrap { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
        .feature-name { width:130px; font-size:12px; color:#4b5563; flex-shrink:0; }
        .feature-track { flex:1; height:8px; border-radius:4px; background:#eef1f5; overflow:hidden; }
        .feature-fill { height:100%; background:var(--primary); border-radius:4px; }
        .feature-count { width:34px; text-align:right; font-size:11.5px; color:#6b7280; flex-shrink:0; }
        .activity-row { display:flex; align-items:flex-start; gap:10px; padding:10px 0; border-top:1px solid #f0f2f5; }
        .activity-row:first-child { border-top:none; }
        .activity-dot { width:8px; height:8px; border-radius:50%; background:var(--primary); margin-top:5px; flex-shrink:0; }
        .activity-text { font-size:12.5px; color:#374151; }
        .activity-text b { color:#111827; }
        .activity-time { font-size:10.5px; color:#9aa3b2; margin-top:2px; }
        .health-grid { display:grid; grid-template-columns:repeat(2,minmax(0, 1fr)); gap:12px; }
        .health-item { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-top:1px solid #f0f2f5; }
        .health-item:nth-child(1), .health-item:nth-child(2) { border-top:none; }
    </style>
</head>
<body>
    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <div>
                    <div class="page-title">System Console</div>
                    <div class="page-sub">Welcome back, {{ Auth::user()->name }}. Live snapshot of platform health and activity.</div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Account</a>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success"><i class="fas fa-circle-check"></i> {{ session('status') }}</div>
        @endif

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-num sa-mono">{{ $userStats['total'] }}</div>
                        <div class="stat-lbl">Total Accounts</div>
                    </div>
                    <div class="stat-icon si-teal"><i class="fas fa-users"></i></div>
                </div>
                <span class="stat-delta up">+{{ $userStats['new_this_week'] }} this week</span>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-num sa-mono">{{ $userStats['active'] }}</div>
                        <div class="stat-lbl">Active Accounts</div>
                    </div>
                    <div class="stat-icon si-green"><i class="fas fa-signal"></i></div>
                </div>
                <span class="stat-delta">{{ $userStats['total'] - $userStats['active'] }} deactivated</span>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-num sa-mono">{{ $aiStats['today'] }}</div>
                        <div class="stat-lbl">AI Requests Today</div>
                    </div>
                    <div class="stat-icon si-amber"><i class="fas fa-robot"></i></div>
                </div>
                <span class="stat-delta">{{ $aiStats['this_week'] }} in the last 7 days</span>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-num sa-mono">{{ $performance['avg_response_ms'] ?? '—' }}{{ $performance['avg_response_ms'] !== null ? 'ms' : '' }}</div>
                        <div class="stat-lbl">Avg Response Time</div>
                    </div>
                    <div class="stat-icon si-teal"><i class="fas fa-gauge-high"></i></div>
                </div>
                <span class="stat-delta">p95 {{ $performance['p95_response_ms'] ?? '—' }}{{ $performance['p95_response_ms'] !== null ? 'ms' : '' }} · {{ $performance['sample_size'] }} samples</span>
            </div>
        </div>

        <div class="metric-row">
            <div class="card">
                <div class="card-head">
                    <div class="card-title">Accounts by Role</div>
                    <a href="{{ route('superadmin.users') }}" class="card-link">Manage users →</a>
                </div>
                <div class="health-grid">
                    <div class="health-item"><span>Students</span><span class="cell-mono">{{ $userStats['students'] }}</span></div>
                    <div class="health-item"><span>Faculty</span><span class="cell-mono">{{ $userStats['faculty'] }}</span></div>
                    <div class="health-item"><span>Program Chairs</span><span class="cell-mono">{{ $userStats['chairs'] }}</span></div>
                    <div class="health-item"><span>Alumni</span><span class="cell-mono">{{ $userStats['alumni'] }}</span></div>
                    <div class="health-item"><span>Super Admins</span><span class="cell-mono">{{ $userStats['super_admins'] }}</span></div>
                    <div class="health-item"><span>Requests Today</span><span class="cell-mono">{{ $performance['requests_today'] }}</span></div>
                </div>

                <div class="card-head" style="margin-top:22px;">
                    <div class="card-title">AI Usage by Feature <span style="font-weight:400;color:#9aa3b2;">(7 days)</span></div>
                </div>
                @if ($aiStats['by_feature']->isEmpty())
                    <div class="empty" style="padding:20px;"><i class="fas fa-robot"></i><div>No AI requests recorded yet.</div></div>
                @else
                    @php $maxFeature = max($aiStats['by_feature']->max('total'), 1); @endphp
                    @foreach ($aiStats['by_feature'] as $row)
                        <div class="feature-bar-wrap">
                            <span class="feature-name">{{ $row->feature }}</span>
                            <span class="feature-track"><span class="feature-fill" style="width:{{ round($row->total / $maxFeature * 100) }}%"></span></span>
                            <span class="feature-count sa-mono">{{ $row->total }}</span>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="card">
                <div class="card-head">
                    <div class="card-title">Recent Activity</div>
                    <a href="{{ route('superadmin.activity-log') }}" class="card-link">Full log →</a>
                </div>
                @if ($recentActivity->isEmpty())
                    <div class="empty" style="padding:20px;"><i class="fas fa-list-check"></i><div>Nothing logged yet.</div></div>
                @else
                    @foreach ($recentActivity as $entry)
                        <div class="activity-row">
                            <div class="activity-dot"></div>
                            <div>
                                <div class="activity-text"><b>{{ $entry->actor_name ?? 'System' }}</b> — {{ $entry->description ?? $entry->action }}</div>
                                <div class="activity-time" title="{{ optional($entry->created_at)->diffForHumans() }}">{{ optional($entry->created_at)->format('Y-m-d H:i:s') }}</div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        @if (($performance['errors_today'] ?? 0) > 0)
            <div class="card" style="border-left:3px solid var(--accent);">
                <div class="card-title" style="color:var(--accent);"><i class="fas fa-triangle-exclamation"></i> {{ $performance['errors_today'] }} server error(s) recorded today</div>
            </div>
        @endif
    </main>
</body>
</html>
