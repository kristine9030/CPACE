<!-- STATS (swapped in place via AJAX) -->
<div class="stats-row a1" id="perfStats">
    <div class="stat-chip">
        <div class="chip-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-users"></i></div>
        <div><div class="chip-num">{{ $stats['active'] }}</div><div class="chip-lbl">Active Students</div></div>
    </div>
    <div class="stat-chip">
        <div class="chip-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-chart-bar"></i></div>
        <div><div class="chip-num">{{ $stats['avg'] }}%</div><div class="chip-lbl">Avg. Score</div></div>
    </div>
    <div class="stat-chip">
        <div class="chip-icon" style="background:#fde8e8;color:var(--accent);"><i class="fas fa-exclamation-triangle"></i></div>
        <div><div class="chip-num">{{ $stats['at_risk'] }}</div><div class="chip-lbl">At Risk Students</div></div>
    </div>
    <div class="stat-chip">
        <div class="chip-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-trophy"></i></div>
        <div><div class="chip-num">{{ $stats['top'] }}%</div><div class="chip-lbl">Top Score</div></div>
    </div>
</div>
