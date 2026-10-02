<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin · Activity Log · CPACE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @include('partials.superadmin-sidebar', ['active' => 'activity-log'])
    <style>
        .filters-bar { display:flex; gap:10px; margin-bottom:18px; flex-wrap:wrap; align-items:center; }
        .filters-bar select, .filters-bar input { width:auto; }
        .filters-bar input[type=text] { flex:1; min-width:200px; }
        .filters-bar input[type=date] { min-width:140px; }
    </style>
</head>
<body>
    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <div>
                    <div class="page-title">Activity Log</div>
                    <div class="page-sub">{{ $logs->total() }} recorded actions across every portal.</div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('superadmin.activity-log.export', request()->query()) }}" class="btn btn-outline"><i class="fas fa-file-arrow-down"></i> Export CSV</a>
            </div>
        </div>

        <div class="card">
            <form method="GET" class="filters-bar">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search actor, action, description...">
                <select name="action" onchange="this.form.submit()">
                    <option value="">All Actions</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" onchange="this.form.submit()">
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" onchange="this.form.submit()">
                <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-filter"></i> Filter</button>
                @if (($filters['search'] ?? '') || ($filters['action'] ?? '') || ($filters['from'] ?? '') || ($filters['to'] ?? ''))
                    <a href="{{ route('superadmin.activity-log') }}" class="btn btn-ghost btn-sm">Clear</a>
                @endif
            </form>

            @if ($logs->isEmpty())
                <div class="empty"><i class="fas fa-list-check"></i><div>No activity recorded for these filters.</div></div>
            @else
                <table>
                    <thead>
                        <tr><th>When</th><th>Actor</th><th>Role</th><th>Action</th><th>Description</th><th>IP</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td class="cell-mono" title="{{ optional($log->created_at)->diffForHumans() }}">{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $log->actor_name ?? 'System' }}</td>
                                <td><span class="role-pill r-{{ $log->actor_role }}">{{ $log->actor_role ? ucfirst(str_replace('_',' ',$log->actor_role)) : '—' }}</span></td>
                                <td><span class="action-pill">{{ $log->action }}</span></td>
                                <td style="max-width:340px;">{{ $log->description }}</td>
                                <td class="cell-mono">{{ $log->ip_address }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="pages">{{ $logs->links() }}</div>
            @endif
        </div>
    </main>
</body>
</html>
