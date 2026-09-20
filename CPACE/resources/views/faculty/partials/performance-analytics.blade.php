<!-- ANALYTICS (swapped in place via AJAX) -->
<div id="perfAnalytics">
    @if(!empty($insights))
        <div class="insights-head"><i class="fas fa-lightbulb"></i> What this view means for you</div>
        <div class="insights-grid">
            @foreach($insights as $insight)
                <div class="insight-card tone-{{ $insight['tone'] }}">
                    <div class="insight-icon"><i class="fas {{ $insight['icon'] }}"></i></div>
                    <div>
                        <div class="insight-title">{{ $insight['title'] }}</div>
                        <div class="insight-text">{{ $insight['text'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="analytics-row">
        <div class="chart-card">
            <h4>Weekly Accuracy Trend</h4>
            <div class="chart-sub">Class-wide accuracy over the last 8 weeks, regardless of the Period filter above.</div>
            @if(collect($weeklyTrend)->contains(fn ($w) => $w['accuracy'] !== null))
                <div class="chart-wrap"><canvas id="trendChart"></canvas></div>
            @else
                <div class="chart-empty">Not enough graded activity yet to plot a trend.</div>
            @endif
        </div>
        <div class="chart-card">
            <h4>Board-Readiness <span style="font-weight:500;color:#aaa;font-size:11px;">(≥75%)</span></h4>
            <div class="chart-sub">Share of active students averaging at benchmark.</div>
            @if($passRate !== null)
                <div class="chart-wrap" style="height:190px;"><canvas id="readyChart"></canvas></div>
                <div class="donut-legend">
                    <span><i style="background:#10b981;"></i> Ready ({{ $passRate }}%)</span>
                    <span><i style="background:#e5484d;"></i> Not yet ({{ 100 - $passRate }}%)</span>
                </div>
            @else
                <div class="chart-empty">No graded activity yet</div>
            @endif
        </div>
    </div>

    <script type="application/json" id="perfAnalyticsData">{!! json_encode([
        'trend'    => $weeklyTrend,
        'passRate' => $passRate,
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
</div>
