<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin · Test Reports · CPACE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @include('partials.superadmin-sidebar', ['active' => 'test-reports'])
    <style>
        .type-tabs { display:flex; gap:8px; margin-bottom:18px; flex-wrap:wrap; }
        .type-tab { padding:9px 16px; border-radius:8px; font-size:12.5px; font-weight:600; color:#666; background:#fff; border:1.5px solid #e8ebf0; text-decoration:none; }
        .type-tab.active { background:var(--primary); border-color:var(--primary); color:#fff; }
        .upload-toggle { margin-bottom:18px; }
        .upload-panel { display:none; }
        .upload-panel.open { display:block; }
        .method-toggle { display:flex; gap:6px; margin-bottom:14px; }
        .method-btn { flex:1; padding:8px; border-radius:7px; border:1.5px solid #e2e2e6; background:#fff; font-size:12px; font-weight:600; color:#666; cursor:pointer; }
        .method-btn.active { border-color:var(--primary); background:var(--primary-light); color:var(--primary); }
        textarea { width:100%; min-height:160px; padding:10px 12px; border:1.5px solid #e2e2e6; border-radius:8px; font-family:'IBM Plex Mono', monospace, monospace; font-size:11.5px; color:#333; resize:vertical; }
        textarea:focus { outline:none; border-color:var(--primary); }
        .report-summary-line { font-size:12px; color:#666; }
        .report-summary-line b { color:#1a1a1a; }
        .unrecognized-note { font-size:11px; color:#b45309; margin-top:2px; }
    </style>
</head>
<body>
    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <div>
                    <div class="page-title">Test Reports</div>
                    <div class="page-sub">Results from suites you run separately (CLI/CI) — upload their JSON output to review here.</div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('superadmin.api-tokens') }}" class="btn btn-outline"><i class="fas fa-robot"></i> Automate via API</a>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('uploadPanel').classList.toggle('open')"><i class="fas fa-upload"></i> Upload Report</button>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success"><i class="fas fa-circle-check"></i> {{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-error"><i class="fas fa-triangle-exclamation"></i> {{ $errors->first() }}</div>
        @endif

        <div class="type-tabs">
            @foreach ($types as $key => $label)
                <a href="{{ route('superadmin.test-reports', ['type' => $key]) }}" class="type-tab {{ $activeType === $key ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div id="uploadPanel" class="upload-panel card">
            <div class="card-title" style="margin-bottom:14px;">Upload a {{ $types[$activeType] }} result</div>
            <form method="POST" action="{{ route('superadmin.test-reports.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="{{ $activeType }}">
                <div class="form-group full">
                    <label>Title</label>
                    <input type="text" name="title" required placeholder="e.g. Nightly regression run — {{ now()->format('M j') }}">
                </div>

                <div class="method-toggle">
                    <button type="button" class="method-btn active" id="btnPaste" onclick="switchMethod('paste')">Paste JSON</button>
                    <button type="button" class="method-btn" id="btnFile" onclick="switchMethod('file')">Upload File</button>
                </div>

                <div id="pasteMethod" class="form-group full">
                    <textarea name="payload" placeholder="Paste the tool's JSON output here..."></textarea>
                </div>
                <div id="fileMethod" class="form-group full" style="display:none;">
                    <input type="file" name="file" accept=".json,application/json">
                </div>

                <div class="hint" style="margin-bottom:14px;">
                    @if ($activeType === 'frontend')
                        Expects Playwright's JSON reporter output (<code>--reporter=json</code>) — reads <code>stats.expected/unexpected/skipped</code>, or falls back to walking <code>suites</code>.
                    @elseif ($activeType === 'api')
                        Expects a Newman JSON run report (<code>newman run collection.json --reporters json</code>) — reads <code>run.stats.requests</code> / <code>run.stats.assertions</code>.
                    @elseif ($activeType === 'load')
                        No universal format exists across load tools — shape your tool's output (or a wrapper script) into <code>{"tool","requests","duration_ms","rps","p95_ms","error_rate"}</code>.
                    @else
                        Shape your benchmark script's output into <code>{"queries":[{"query","rows","duration_ms"}, ...]}</code>.
                    @endif
                    Anything else is still stored and viewable as raw JSON — just without an auto-computed summary.
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload</button>
            </form>
        </div>

        <div class="card">
            @if ($reports->isEmpty())
                <div class="empty"><i class="fas fa-vial"></i><div>No {{ strtolower($types[$activeType]) }} reports uploaded yet.</div></div>
            @else
                <table>
                    <thead><tr><th>Title</th><th>Summary</th><th>Uploaded</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($reports as $report)
                            <tr>
                                <td><a href="{{ route('superadmin.test-reports.show', $report->id) }}" style="color:var(--primary);font-weight:600;text-decoration:none;">{{ $report->title }}</a></td>
                                <td>
                                    @php $s = $report->summary; @endphp
                                    @if (! ($s['recognized'] ?? false))
                                        <span class="unrecognized-note"><i class="fas fa-circle-exclamation"></i> Unrecognized shape — raw only</span>
                                    @elseif ($report->type === 'frontend')
                                        <span class="report-summary-line"><b>{{ $s['passed'] }}</b> passed, <b>{{ $s['failed'] }}</b> failed, {{ $s['skipped'] }} skipped</span>
                                    @elseif ($report->type === 'api')
                                        <span class="report-summary-line"><b>{{ $s['requests_total'] - $s['requests_failed'] }}</b>/{{ $s['requests_total'] }} requests ok, {{ $s['assertions_failed'] }} failed assertion(s)</span>
                                    @elseif ($report->type === 'load')
                                        <span class="report-summary-line">{{ $s['rps'] ?? '—' }} rps, p95 {{ $s['p95_ms'] ?? '—' }}ms, {{ $s['error_rate'] ?? '0' }}% errors</span>
                                    @else
                                        <span class="report-summary-line">{{ $s['query_count'] }} queries, avg {{ $s['avg_duration_ms'] }}ms</span>
                                    @endif
                                </td>
                                <td class="cell-mono">{{ $report->created_at?->diffForHumans() }}</td>
                                <td>
                                    <form method="POST" action="{{ route('superadmin.test-reports.destroy', $report->id) }}"
                                        data-confirm="Delete this report? This can't be undone." data-confirm-title="Delete report?" data-confirm-ok="Yes, delete">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="pages">{{ $reports->links() }}</div>
            @endif
        </div>
    </main>
    <script>
        function switchMethod(m) {
            document.getElementById('pasteMethod').style.display = m === 'paste' ? 'block' : 'none';
            document.getElementById('fileMethod').style.display = m === 'file' ? 'block' : 'none';
            document.getElementById('btnPaste').classList.toggle('active', m === 'paste');
            document.getElementById('btnFile').classList.toggle('active', m === 'file');
        }
        @if ($errors->any())
            document.getElementById('uploadPanel').classList.add('open');
        @endif
    </script>
</body>
</html>
