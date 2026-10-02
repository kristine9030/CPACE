@php
    $subjColors = ['FAR'=>'#3b82f6','AFAR'=>'#17a2b8','MS'=>'#8b5cf6','TAX'=>'#27ae60','AUD'=>'#e8567d','RFBT'=>'#f59e0b'];
    $subjIcons  = ['FAR'=>'fa-coins','AFAR'=>'fa-layer-group','MS'=>'fa-chart-pie','TAX'=>'fa-landmark','AUD'=>'fa-magnifying-glass-chart','RFBT'=>'fa-gavel'];
@endphp

<!-- DYNAMIC BODY (swapped in place via AJAX) -->
<div class="perf-pair a2" id="perfBody">
    <!-- Class Strong Topics | Score Distribution: where the class is doing well -->
    <!-- STRONGEST TOPICS — same rule as the weak list: pooled class accuracy at 75%+ -->
    <div class="side-card">
        <div class="side-title"><i class="fas fa-trophy" style="margin-right:6px;color:#10b981;"></i>Class Strong Topics</div>
        <div class="weak-list-scroll">
            @forelse($strongTopics as $t)
            <div class="weak-item" style="align-items:flex-start;">
                <div class="weak-icon" style="background:{{ ($subjColors[$t->subject_code] ?? '#888') }}20;color:{{ $subjColors[$t->subject_code] ?? '#888' }};">
                    <i class="fas {{ $subjIcons[$t->subject_code] ?? 'fa-book' }}"></i>
                </div>
                <span class="weak-name">
                    {{ $t->topic }}<br><span class="weak-sub">{{ $t->subject_code }}</span>
                    <div class="weak-why">Class accuracy is {{ $t->accuracy }}% over {{ $t->attempts }} attempts</div>
                </span>
                <span class="weak-rate" style="color:#059669;">{{ $t->accuracy }}%</span>
            </div>
            @empty
                <div class="muted-empty">No topic has reached 75% class accuracy yet.</div>
            @endforelse
        </div>
    </div>

    <!-- SCORE DISTRIBUTION -->
    <div class="side-card">
            <div class="side-title">Score Distribution</div>
            @if($distribution['total'] === 0)
                <div class="muted-empty">No scored quizzes in this view.</div>
            @else
            @foreach($distribution['bands'] as $b)
            <div style="margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
                    <span style="color:#555;">{{ $b['label'] }}</span>
                    <span style="font-weight:700;color:#1a1a1a;">{{ $b['count'] }} student{{ $b['count'] === 1 ? '' : 's' }}</span>
                </div>
                <div style="height:7px;background:#f0f0f0;border-radius:4px;overflow:hidden;">
                    <div style="height:100%;border-radius:4px;background:{{ $b['color'] }};width:{{ $b['pct'] }}%;"></div>
                </div>
            </div>
            @endforeach
            @endif
    </div>

    {{-- Per-student data for the detail modal, plus the class weak-topics chart
         data (current page + current filters). Re-read after each AJAX swap. --}}
    <script type="application/json" id="perfData">{!! json_encode([
        'students'   => $students->keyBy('id'),
        'details'    => $details,
        'weakTopics' => $weakTopics->map(fn ($t) => ['topic' => $t->topic, 'subject' => $t->subject_code, 'accuracy' => $t->accuracy])->values(),
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
</div>
