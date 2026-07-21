<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Accounts - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .action-btn { width:30px; height:30px; border:none; border-radius:7px; cursor:pointer; font-size:12px; display:inline-flex; align-items:center; justify-content:center; transition:all .2s; }
        .ab-edit { background:#dbeafe; color:var(--blue); }
        .ab-edit:hover { background:#bfdbfe; }
        .ab-toggle { background:#fef3c7; color:#d97706; }
        .ab-toggle:hover { background:#fde68a; }
        .alumni-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        @media (max-width: 768px) { .alumni-table-wrap table { min-width: 520px; } }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'alumni'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Alumni Accounts</div>
                <div class="page-sub">Create logins for alumni so they can post in the Community feed.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('community.index') }}" class="btn btn-outline"><i class="fas fa-people-group"></i> View Community</a>
            <a href="{{ route('chair.alumni.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Alumni</a>
            @include('partials.topbar-actions')
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-head"><span class="card-title">All Alumni ({{ $alumni->count() }})</span></div>
        <div class="alumni-table-wrap">
        <table>
            <thead>
                <tr><th>Name</th><th>Email</th><th>Batch / Job</th><th>Status</th><th style="text-align:right;">Actions</th></tr>
            </thead>
            <tbody>
            @forelse ($alumni as $a)
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="user-av" style="background:var(--primary); width:32px; height:32px; font-size:11px;">{{ strtoupper(substr($a->first_name,0,1)).strtoupper(substr($a->last_name,0,1)) }}</div>
                            <span style="font-weight:600; color:#1a1a1a;">{{ $a->name }}</span>
                        </div>
                    </td>
                    <td style="color:#666;">{{ $a->email }}</td>
                    <td style="color:#666;">
                        @if($a->alumniProfile?->batch_year) Batch {{ $a->alumniProfile->batch_year }}<br>@endif
                        @if($a->alumniProfile?->current_job)<span style="font-size:11.5px;color:#999;">{{ $a->alumniProfile->current_job }}</span>@endif
                        @unless($a->alumniProfile?->batch_year || $a->alumniProfile?->current_job)<span style="color:#bbb;font-size:12px;">— not set —</span>@endunless
                    </td>
                    <td>
                        @if ($a->is_active)
                            <span class="pill pill-on"><i class="fas fa-check"></i> Active</span>
                        @else
                            <span class="pill pill-off"><i class="fas fa-ban"></i> Inactive</span>
                        @endif
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a href="{{ route('chair.alumni.edit', $a->id) }}" class="action-btn ab-edit" title="Edit account"><i class="fas fa-pen"></i></a>
                        <form method="POST" action="{{ route('chair.alumni.toggle', $a->id) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="action-btn ab-toggle" title="{{ $a->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fas fa-power-off"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="empty"><i class="fas fa-user-slash"></i><div>No alumni accounts yet. Click "Add Alumni" to create one.</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
</main>
</body>
</html>
