<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin · {{ $report->title }} · CPACE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @include('partials.superadmin-sidebar', ['active' => 'test-reports'])
    <style>
        pre.raw-json { background:#14283E; color:#d4dbe6; padding:16px; border-radius:10px; font-family:'IBM Plex Mono', monospace, monospace; font-size:11.5px; line-height:1.6; overflow-x:auto; max-height:520px; }
        .summary-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(150px,1fr)); gap:14px; margin-bottom:4px; }
        .summary-item { background:#f9fafb; border-radius:10px; padding:12px 14px; }
        .summary-item .v { font-size:19px; font-weight:700; color:#1a1a1a; }
        .summary-item .k { font-size:10.5px; color:#999; margin-top:2px; }
    </style>
</head>
<body>
    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <div>
                    <div class="page-title">{{ $report->title }}</div>
                    <div class="page-sub">{{ $types[$report->type] }} · uploaded {{ $report->created_at?->format('M j, Y g:i A') }} by {{ $report->uploader?->name ?? 'Unknown' }}</div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('superadmin.test-reports', ['type' => $report->type]) }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
            </div>
        </div>

        @php $s = $report->summary; @endphp
        <div class="card">
            <div class="card-title" style="margin-bottom:14px;">Summary</div>
            @if (! ($s['recognized'] ?? false))
                <div class="empty"><i class="fas fa-circle-exclamation"></i><div>This JSON shape wasn't recognized, so no summary could be computed — see the raw payload below.</div></div>
            @elseif ($report->type === 'frontend')
                <div class="summary-grid">
                    <div class="summary-item"><div class="v" style="color:#059669;">{{ $s['passed'] }}</div><div class="k">Passed</div></div>
                    <div class="summary-item"><div class="v" style="color:#c0392b;">{{ $s['failed'] }}</div><div class="k">Failed</div></div>
                    <div class="summary-item"><div class="v">{{ $s['skipped'] }}</div><div class="k">Skipped</div></div>
                    <div class="summary-item"><div class="v">{{ $s['flaky'] }}</div><div class="k">Flaky</div></div>
                    <div class="summary-item"><div class="v">{{ $s['duration_ms'] !== null ? number_format($s['duration_ms']).'ms' : '—' }}</div><div class="k">Duration</div></div>
                </div>
            @elseif ($report->type === 'api')
                <div class="summary-grid">
                    <div class="summary-item"><div class="v">{{ $s['requests_total'] }}</div><div class="k">Requests</div></div>
                    <div class="summary-item"><div class="v" style="color:#c0392b;">{{ $s['requests_failed'] }}</div><div class="k">Requests Failed</div></div>
                    <div class="summary-item"><div class="v">{{ $s['assertions_total'] }}</div><div class="k">Assertions</div></div>
                    <div class="summary-item"><div class="v" style="color:#c0392b;">{{ $s['assertions_failed'] }}</div><div class="k">Assertions Failed</div></div>
                    <div class="summary-item"><div class="v">{{ $s['duration_ms'] !== null ? number_format($s['duration_ms']).'ms' : '—' }}</div><div class="k">Duration</div></div>
                </div>
            @elseif ($report->type === 'load')
                <div class="summary-grid">
                    <div class="summary-item"><div class="v">{{ $s['requests'] ?? '—' }}</div><div class="k">Requests</div></div>
                    <div class="summary-item"><div class="v">{{ $s['rps'] ?? '—' }}</div><div class="k">Requests/sec</div></div>
                    <div class="summary-item"><div class="v">{{ $s['p95_ms'] ?? '—' }}</div><div class="k">p95 (ms)</div></div>
                    <div class="summary-item"><div class="v">{{ $s['error_rate'] ?? '0' }}%</div><div class="k">Error Rate</div></div>
                    <div class="summary-item"><div class="v" style="font-size:13px;">{{ $s['tool'] ?? 'Unspecified' }}</div><div class="k">Tool</div></div>
                </div>
            @else
                <div class="summary-grid">
                    <div class="summary-item"><div class="v">{{ $s['query_count'] }}</div><div class="k">Queries</div></div>
                    <div class="summary-item"><div class="v">{{ number_format($s['total_duration_ms']) }}ms</div><div class="k">Total Duration</div></div>
                    <div class="summary-item"><div class="v">{{ $s['avg_duration_ms'] }}ms</div><div class="k">Avg Duration</div></div>
                    <div class="summary-item"><div class="v" style="font-size:13px;">{{ $s['slowest_query'] ?? '—' }}</div><div class="k">Slowest ({{ $s['slowest_ms'] }}ms)</div></div>
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card-title" style="margin-bottom:14px;">Raw Payload</div>
            <pre class="raw-json">{{ json_encode(json_decode($report->raw_payload), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    </main>
</body>
</html>
