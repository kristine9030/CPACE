<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin · API Tokens · CPACE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @include('partials.superadmin-sidebar', ['active' => 'api-tokens'])
    <style>
        .new-token-box { background:#14283E; color:#d4dbe6; border-radius:12px; padding:18px 20px; margin-bottom:18px; }
        .new-token-box .label { font-size:11.5px; color:#8fa1b8; margin-bottom:8px; }
        .new-token-value { display:flex; align-items:center; gap:10px; background:rgba(255,255,255,.06); border-radius:8px; padding:10px 14px; font-family:'IBM Plex Mono', monospace, monospace; font-size:12px; word-break:break-all; }
        .copy-btn { flex-shrink:0; background:var(--primary); color:#fff; border:none; border-radius:6px; padding:6px 12px; font-size:11px; font-weight:600; cursor:pointer; }
        .warn-once { font-size:11px; color:#f5a623; margin-top:8px; }
        #createPanel { display:none; }
        #createPanel.open { display:block; }
        textarea.example { width:100%; background:#14283E; color:#a8e6cf; font-family:'IBM Plex Mono', monospace, monospace; font-size:11px; border:none; border-radius:8px; padding:14px; min-height:90px; }
    </style>
</head>
<body>
    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <div>
                    <div class="page-title">API Tokens</div>
                    <div class="page-sub">Named tokens for automation — CI uploading test reports, a scheduled benchmark, etc.</div>
                </div>
            </div>
            <div class="topbar-right">
                <button type="button" class="btn btn-primary" onclick="document.getElementById('createPanel').classList.toggle('open')"><i class="fas fa-plus"></i> New Token</button>
            </div>
        </div>

        @if (session('newToken'))
            <div class="new-token-box">
                <div class="label"><i class="fas fa-circle-check"></i> Token "{{ session('newTokenName') }}" created</div>
                <div class="new-token-value" id="tokenValue">{{ session('newToken') }}
                    <button type="button" class="copy-btn" onclick="copyToken()">Copy</button>
                </div>
                <div class="warn-once"><i class="fas fa-triangle-exclamation"></i> This is shown once. Copy it now — it can't be retrieved again, only revoked and reissued.</div>
            </div>
        @endif
        @if (session('status'))
            <div class="alert alert-success"><i class="fas fa-circle-check"></i> {{ session('status') }}</div>
        @endif

        <div id="createPanel" class="card {{ session('newToken') ? 'open' : '' }}">
            <div class="card-title" style="margin-bottom:14px;">Issue a new token</div>
            <form method="POST" action="{{ route('superadmin.api-tokens.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" required placeholder="e.g. GitHub Actions — Playwright">
                    </div>
                    <div class="form-group">
                        <label>Expires In (days)</label>
                        <input type="text" name="expires_in_days" placeholder="Leave blank for never">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Issue Token</button>
            </form>
        </div>

        <div class="card">
            <div class="card-head"><div class="card-title">Issued Tokens</div></div>
            @if ($tokens->isEmpty())
                <div class="empty"><i class="fas fa-key"></i><div>No automation tokens issued yet.</div></div>
            @else
                <table>
                    <thead><tr><th>Name</th><th>Issued By</th><th>Last Used</th><th>Expires</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($tokens as $token)
                            <tr>
                                <td style="font-weight:600;">{{ $token->name }}</td>
                                <td>{{ $token->user?->name ?? '—' }}</td>
                                <td class="cell-mono">{{ $token->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                                <td class="cell-mono">{{ $token->expires_at?->format('M j, Y') ?? 'Never' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('superadmin.api-tokens.destroy', $token->id) }}"
                                        data-confirm="Revoke this token? Anything using it (CI, a scheduled job) will stop working immediately." data-confirm-title="Revoke token?" data-confirm-ok="Yes, revoke">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-ban"></i> Revoke</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card">
            <div class="card-title" style="margin-bottom:10px;"><i class="fas fa-terminal" style="color:var(--primary);"></i> Uploading a report with this token</div>
            <div class="hint" style="margin-bottom:10px;">POST the tool's JSON as the <code>payload</code> field to <code>{{ url('/api/test-reports') }}</code>:</div>
            <textarea class="example" readonly onclick="this.select()">curl -X POST {{ url('/api/test-reports') }} \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"type":"frontend","title":"Nightly regression","payload":{"stats":{"expected":18,"unexpected":0,"skipped":1}}}'</textarea>
        </div>
    </main>
    <script>
        function copyToken() {
            const text = document.getElementById('tokenValue').firstChild.textContent.trim();
            navigator.clipboard?.writeText(text);
        }
    </script>
</body>
</html>
