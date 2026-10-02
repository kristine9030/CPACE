<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin · Performance · CPACE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @include('partials.superadmin-sidebar', ['active' => 'performance'])
    @include('partials.chart-kit')
    <style>
        .reliability-note { font-size:11px; color:#999; margin-top:6px; }
    </style>
</head>
<body>
    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <div>
                    <div class="page-title">Performance</div>
                    <div class="page-sub">Real traffic captured by the app itself — response time, throughput and error trend over the last {{ $summary['retention_days'] }} days.</div>
                </div>
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-num">{{ $summary['avg_response_ms'] ?? '—' }}{{ $summary['avg_response_ms'] !== null ? 'ms' : '' }}</div>
                        <div class="stat-lbl">Avg Response Time</div>
                    </div>
                    <div class="stat-icon si-teal"><i class="fas fa-gauge-high"></i></div>
                </div>
                <span class="stat-delta">{{ $summary['sample_size'] }} recent samples</span>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-num">{{ $summary['p95_response_ms'] ?? '—' }}{{ $summary['p95_response_ms'] !== null ? 'ms' : '' }}</div>
                        <div class="stat-lbl">p95 Response Time</div>
                    </div>
                    <div class="stat-icon si-blue"><i class="fas fa-stopwatch"></i></div>
                </div>
                <span class="stat-delta">p99 {{ $summary['p99_response_ms'] ?? '—' }}{{ $summary['p99_response_ms'] !== null ? 'ms' : '' }}</span>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-num">{{ $summary['requests_today'] }}</div>
                        <div class="stat-lbl">Requests Today</div>
                    </div>
                    <div class="stat-icon si-green"><i class="fas fa-arrow-trend-up"></i></div>
                </div>
                <span class="stat-delta">{{ number_format($summary['window_requests']) }} in the last 14 days</span>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-num">{{ $summary['reliability'] !== null ? $summary['reliability'] . '%' : '—' }}</div>
                        <div class="stat-lbl">Reliability (14d)</div>
                    </div>
                    <div class="stat-icon {{ $summary['reliability'] === null || $summary['reliability'] >= 99.5 ? 'si-green' : ($summary['reliability'] >= 98 ? 'si-amber' : 'si-red') }}"><i class="fas fa-shield-heart"></i></div>
                </div>
                <span class="stat-delta">{{ $summary['errors_today'] }} server error(s) today</span>
            </div>
        </div>

        <div class="card" style="margin-bottom:22px;">
            <div class="card-title" style="margin-bottom:8px;"><i class="fas fa-robot" style="color:var(--primary);"></i> AI Provider Response Time</div>
            <div class="reliability-note" style="margin-bottom:14px;">
                Tracked separately from the app's own response time above — these are routes (AI Tutor chat, AI-drafted questions, curriculum gap-fill) that wait on Gemini/OpenRouter/Claude before responding, so their latency belongs to the provider, not CPACE.
            </div>
            <div class="stats-row" style="margin-bottom:0;">
                <div class="stat-card">
                    <div class="stat-top">
                        <div>
                            <div class="stat-num">{{ $summary['ai_avg_response_ms'] ?? '—' }}{{ $summary['ai_avg_response_ms'] !== null ? 'ms' : '' }}</div>
                            <div class="stat-lbl">Avg AI Response Time</div>
                        </div>
                        <div class="stat-icon si-teal"><i class="fas fa-brain"></i></div>
                    </div>
                    <span class="stat-delta">{{ $summary['ai_sample_size'] }} recent samples</span>
                </div>
                <div class="stat-card">
                    <div class="stat-top">
                        <div>
                            <div class="stat-num">{{ $summary['ai_p95_response_ms'] ?? '—' }}{{ $summary['ai_p95_response_ms'] !== null ? 'ms' : '' }}</div>
                            <div class="stat-lbl">p95 AI Response Time</div>
                        </div>
                        <div class="stat-icon si-blue"><i class="fas fa-stopwatch"></i></div>
                    </div>
                    <span class="stat-delta">AI Tutor, AI-drafted questions, gap-fill</span>
                </div>
            </div>
        </div>

        <div class="viz-grid-layout">
            <div class="viz-card">
                <h4><i class="fas fa-chart-line"></i> Response Time Trend</h4>
                <div class="viz-sub">Daily average, last 14 days. Empty points mean no traffic that day.</div>
                <div class="chart-canvas-wrap h-md" id="wrapResponseTime">
                    <canvas id="chartResponseTime"></canvas>
                </div>
            </div>
            <div class="viz-card">
                <h4><i class="fas fa-server"></i> Requests per Day</h4>
                <div class="viz-sub">Total requests handled by the app each day.</div>
                <div class="chart-canvas-wrap h-md" id="wrapRequests">
                    <canvas id="chartRequests"></canvas>
                </div>
            </div>
            <div class="viz-card full">
                <h4><i class="fas fa-triangle-exclamation"></i> Error Rate Trend</h4>
                <div class="viz-sub">Share of requests that returned a server error (HTTP 5xx) each day.</div>
                <div class="chart-canvas-wrap h-sm" id="wrapErrorRate">
                    <canvas id="chartErrorRate"></canvas>
                </div>
                <table class="viz-table">
                    <thead><tr><th>Date</th><th class="num">Requests</th><th class="num">Errors</th><th class="num">Error Rate</th><th class="num">Avg Response</th><th class="num">Avg AI Response</th></tr></thead>
                    <tbody>
                        @foreach ($trend->reverse() as $day)
                            <tr>
                                <td>{{ $day['label'] }}</td>
                                <td class="num">{{ number_format($day['requests']) }}</td>
                                <td class="num">{{ $day['errors'] }}</td>
                                <td class="num">{{ $day['error_rate'] !== null ? $day['error_rate'] . '%' : '—' }}</td>
                                <td class="num">{{ $day['avg_response_ms'] !== null ? $day['avg_response_ms'] . 'ms' : '—' }}</td>
                                <td class="num">{{ $day['ai_avg_response_ms'] !== null ? $day['ai_avg_response_ms'] . 'ms' : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-title" style="margin-bottom:8px;"><i class="fas fa-circle-info" style="color:var(--primary);"></i> About this page</div>
            <div class="reliability-note">
                These figures come from real traffic on this server, captured by a lightweight request-timing middleware — not a synthetic load test. Response-time samples are a rolling window of the most recent {{ $summary['sample_size'] }} requests; daily totals are retained for {{ $summary['retention_days'] }} days.
            </div>
        </div>
    </main>

    <script>
        const trend = @json($trend);

        Viz.chart('chartResponseTime', {
            type: 'line',
            data: {
                labels: trend.map(d => d.label),
                datasets: [Viz.line({
                    label: 'Avg response time (ms)',
                    data: trend.map(d => d.avg_response_ms),
                    borderColor: Viz.palette.s1,
                    pointBackgroundColor: Viz.palette.s1,
                    backgroundColor: 'rgba(163,43,43,.08)',
                    fill: true,
                    spanGaps: true,
                })],
            },
            options: {
                interaction: { mode: 'index', intersect: false },
                scales: { y: Viz.countAxis(), x: Viz.catAxis() },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (c) => c.raw === null ? 'No traffic' : `${c.raw}ms average` } },
                },
            },
        });

        Viz.chart('chartRequests', {
            type: 'bar',
            data: {
                labels: trend.map(d => d.label),
                datasets: [Viz.bar({
                    label: 'Requests',
                    data: trend.map(d => d.requests),
                    backgroundColor: Viz.palette.s2,
                })],
            },
            options: {
                scales: { y: Viz.countAxis(), x: Viz.catAxis() },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (c) => `${c.raw.toLocaleString()} requests` } },
                },
            },
        });

        Viz.chart('chartErrorRate', {
            type: 'line',
            data: {
                labels: trend.map(d => d.label),
                datasets: [Viz.line({
                    label: 'Error rate',
                    data: trend.map(d => d.error_rate),
                    borderColor: Viz.palette.crit,
                    pointBackgroundColor: Viz.palette.crit,
                    backgroundColor: 'rgba(208,59,59,.08)',
                    fill: true,
                    spanGaps: true,
                })],
            },
            options: {
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: Viz.axis({ beginAtZero: true, suggestedMax: 5, ticks: { color: Viz.palette.muted, padding: 6, callback: (v) => v + '%' } }),
                    x: Viz.catAxis(),
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (c) => c.raw === null ? 'No traffic' : `${c.raw}% error rate` } },
                },
            },
        });
    </script>
</body>
</html>
