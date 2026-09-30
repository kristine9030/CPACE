<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin · Users · CPACE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @include('partials.superadmin-sidebar', ['active' => 'users'])
    <style>
        .filters-bar { display:flex; gap:10px; margin-bottom:18px; flex-wrap:wrap; }
        .filters-bar select, .filters-bar input[type=text] { width:auto; min-width:160px; }
        .filters-bar input[type=text] { flex:1; min-width:200px; }
        .role-chips { display:flex; gap:8px; margin-bottom:18px; flex-wrap:wrap; }
        .role-chip { display:flex; align-items:center; gap:7px; padding:8px 14px; border-radius:20px; background:#fff; border:1.5px solid #e8ebf0; font-size:12px; font-weight:600; color:#4b5563; text-decoration:none; }
        .role-chip b { font-weight:800; }
        .row-actions { display:flex; gap:6px; }
        .row-actions form { display:inline; }
    </style>
</head>
<body>
    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <div>
                    <div class="page-title">Users</div>
                    <div class="page-sub">{{ $users->total() }} accounts across every role.</div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Account</a>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success"><i class="fas fa-circle-check"></i> {{ session('status') }}</div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning"><i class="fas fa-triangle-exclamation"></i> {{ session('warning') }}</div>
        @endif

        <div class="role-chips">
            @foreach ($roleLabels as $id => $label)
                <a href="{{ route('superadmin.users', array_merge(request()->query(), ['role' => $id])) }}" class="role-chip">
                    {{ $label }} <b>{{ $totalsByRole[$id] ?? 0 }}</b>
                </a>
            @endforeach
        </div>

        <div class="card">
            <form method="GET" class="filters-bar">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name or email...">
                <select name="role" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    @foreach ($roleLabels as $id => $label)
                        <option value="{{ $id }}" @selected(($filters['role'] ?? '') == $id)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" onchange="this.form.submit()">
                    <option value="">Any Status</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-filter"></i> Filter</button>
                @if (($filters['search'] ?? '') || ($filters['role'] ?? '') || ($filters['status'] ?? ''))
                    <a href="{{ route('superadmin.users') }}" class="btn btn-ghost btn-sm">Clear</a>
                @endif
            </form>

            @if ($users->isEmpty())
                <div class="empty"><i class="fas fa-user-slash"></i><div>No accounts match these filters.</div></div>
            @else
                <table>
                    <thead>
                        <tr><th>Account</th><th>Role</th><th>Status</th><th>Last Login</th><th>Created</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <div class="user-av">{{ strtoupper(substr($user->first_name,0,1).substr($user->last_name,0,1)) }}</div>
                                        <div>
                                            <div style="font-weight:600;">{{ $user->name }}</div>
                                            <div style="font-size:11.5px;color:#9aa3b2;">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="role-pill r-{{ $user->role?->name }}">{{ $roleLabels[$user->role_id] ?? $user->roleName() }}</span></td>
                                <td><span class="pill {{ $user->is_active ? 'pill-on' : 'pill-off' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td class="cell-mono">{{ $user->last_login_at?->diffForHumans() ?? '—' }}</td>
                                <td class="cell-mono">{{ $user->created_at?->format('M j, Y') }}</td>
                                <td>
                                    <div class="row-actions">
                                        <form method="POST" action="{{ route('superadmin.users.resend-otp', $user->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-sm" title="Resend one-time password"><i class="fas fa-key"></i></button>
                                        </form>
                                        @if ($user->id !== Auth::id())
                                            <form method="POST" action="{{ route('superadmin.users.toggle-active', $user->id) }}"
                                                data-confirm="{{ $user->is_active ? 'Deactivate this account? They will be logged out and unable to sign back in.' : 'Reactivate this account?' }}"
                                                data-confirm-title="{{ $user->is_active ? 'Deactivate account?' : 'Reactivate account?' }}"
                                                data-confirm-ok="Yes, continue">
                                                @csrf
                                                <button type="submit" class="btn {{ $user->is_active ? 'btn-danger' : 'btn-outline' }} btn-sm">
                                                    {{ $user->is_active ? 'Deactivate' : 'Reactivate' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="pages">{{ $users->links() }}</div>
            @endif
        </div>
    </main>
</body>
</html>
