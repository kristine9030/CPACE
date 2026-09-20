@php
    $benchmarkGap = $stats['avg'] > 0 ? $stats['avg'] - 75 : null;
    $atRiskPct = $stats['active'] > 0 ? round($stats['at_risk'] / $stats['active'] * 100) : 0;
@endphp
<!-- STATS (swapped in place via AJAX) -->
<div class="stats-row a1" id="perfStats">
    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-lbl">Active Students</div>
                <div class="stat-num">{{ $stats['active'] }}</div>
                <div class="stat-chg neutral">In the current filters</div>
            </div>
            <div class="stat-icon si-blue"><i class="fas fa-users"></i></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-lbl">Avg. Score</div>
                <div class="stat-num">{{ $stats['avg'] }}%</div>
                @if($benchmarkGap === null)
                    <div class="stat-chg neutral">No graded activity yet</div>
                @elseif($benchmarkGap >= 0)
                    <div class="stat-chg"><i class="fas fa-arrow-up"></i> +{{ $benchmarkGap }} pts vs benchmark</div>
                @else
                    <div class="stat-chg" style="color:var(--accent);"><i class="fas fa-arrow-down"></i> {{ $benchmarkGap }} pts vs benchmark</div>
                @endif
            </div>
            <div class="stat-icon si-green"><i class="fas fa-chart-bar"></i></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-lbl">At Risk Students</div>
                <div class="stat-num">{{ $stats['at_risk'] }}</div>
                @if($stats['at_risk'] > 0)
                    <div class="stat-chg" style="color:var(--accent);"><i class="fas fa-triangle-exclamation"></i> {{ $atRiskPct }}% of active students</div>
                @else
                    <div class="stat-chg neutral">None flagged</div>
                @endif
            </div>
            <div class="stat-icon si-red"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-lbl">Top Score</div>
                <div class="stat-num">{{ $stats['top'] }}%</div>
                <div class="stat-chg neutral">Best performer this view</div>
            </div>
            <div class="stat-icon si-orange"><i class="fas fa-trophy"></i></div>
        </div>
    </div>
</div>
