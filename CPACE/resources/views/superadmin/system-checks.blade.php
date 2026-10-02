<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin · System Checks · CPACE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @include('partials.superadmin-sidebar', ['active' => 'system-checks'])
    <style>
        .check-list { display:flex; flex-direction:column; gap:1px; }
        .check-row { display:flex; align-items:center; gap:14px; padding:14px 6px; border-top:1px solid #f5f5f5; }
        .check-row:first-child { border-top:none; }
        .check-dot { width:12px; height:12px; border-radius:50%; flex-shrink:0; }
        .check-dot.ok { background:#10b981; }
        .check-dot.warn { background:#f59e0b; }
        .check-dot.fail { background:#c0392b; }
        .check-body { flex:1; min-width:0; }
        .check-label { font-size:13.5px; font-weight:600; color:#1a1a1a; }
        .check-message { font-size:12px; color:#888; margin-top:2px; }
        .check-latency { font-size:11px; color:#aaa; flex-shrink:0; }
        .check-status-badge { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; padding:3px 9px; border-radius:20px; flex-shrink:0; }
        .check-status-badge.ok { background:#d1fae5; color:#059669; }
        .check-status-badge.warn { background:#fef3c7; color:#b45309; }
        .check-status-badge.fail { background:#fde8e8; color:#c0392b; }
    </style>
</head>
<body>
    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <div>
                    <div class="page-title">System Checks</div>
                    <div class="page-sub">Live, read-only checks — ran just now, {{ $ranAt->format('M j, Y g:i A') }}.</div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('superadmin.system-checks') }}" class="btn btn-primary"><i class="fas fa-rotate"></i> Run Again</a>
            </div>
        </div>

        @php
            $okCount = collect($checks)->where('status', 'ok')->count();
        @endphp

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-num">{{ $okCount }}/{{ count($checks) }}</div>
                        <div class="stat-lbl">Checks Passing</div>
                    </div>
                    <div class="stat-icon {{ $okCount === count($checks) ? 'si-green' : 'si-amber' }}"><i class="fas fa-shield-heart"></i></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><div class="card-title">Results</div></div>
            <div class="check-list">
                @foreach ($checks as $check)
                    <div class="check-row">
                        <div class="check-dot {{ $check['status'] }}"></div>
                        <div class="check-body">
                            <div class="check-label">{{ $check['label'] }}</div>
                            <div class="check-message">{{ $check['message'] }}</div>
                        </div>
                        <div class="check-latency">{{ $check['latency_ms'] }}ms</div>
                        <span class="check-status-badge {{ $check['status'] }}">{{ $check['status'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-title" style="margin-bottom:8px;"><i class="fas fa-circle-info" style="color:var(--primary);"></i> What these checks do</div>
            <div class="reliability-note" style="font-size:11px;color:#999;">
                Database: opens a real connection and runs a trivial query. Session Signing: round-trips a random value through APP_KEY encryption. Email Delivery: opens the configured SMTP handshake and closes it — no message is sent. Gemini / OpenRouter / Claude: call each provider's free "list models" endpoint to confirm the configured API key is accepted — none of these calls generate content or spend usage.
            </div>
        </div>
    </main>
</body>
</html>
