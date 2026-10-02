@php
    $subjColors = ['FAR'=>'#3b82f6','AFAR'=>'#17a2b8','MS'=>'#8b5cf6','TAX'=>'#27ae60','AUD'=>'#e8567d','RFBT'=>'#f59e0b'];
    $subjIcons  = ['FAR'=>'fa-coins','AFAR'=>'fa-layer-group','MS'=>'fa-chart-pie','TAX'=>'fa-landmark','AUD'=>'fa-magnifying-glass-chart','RFBT'=>'fa-gavel'];
@endphp

<!-- NEEDS ATTENTION: who is struggling and which topics are weak (swapped in place via AJAX) -->
<div class="perf-pair a2" id="perfAttention">
    <!-- AT RISK -->
    <div class="side-card">
        <div class="side-title" style="color:var(--accent);"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i>At-Risk Students</div>
        @forelse($atRisk as $r)
        <div class="at-risk-item">
            <div class="at-risk-av" style="background:{{ $r['color'] }};">{{ $r['initials'] }}</div>
            <div style="flex:1">
                <div class="at-risk-name">{{ $r['name'] }}</div>
                <div class="at-risk-sub">{{ $r['subjects'] ? implode(', ', $r['subjects']) : 'No subject' }} &bull; {{ $r['quizzes'] }} quizzes</div>
            </div>
            <div><div class="at-risk-score">{{ $r['score'] }}%</div></div>
        </div>
        @empty
            <div class="muted-empty"><i class="fas fa-check-circle" style="color:#10b981;margin-right:5px;"></i>No at-risk students in this view.</div>
        @endforelse
        @if($atRisk->isNotEmpty())
        <form method="POST" action="{{ route('faculty.performance.remind') }}"
              data-confirm="All {{ $atRisk->count() }} at-risk student(s) in this view will receive a study reminder email."
              data-confirm-title="Send study reminders?"
              data-confirm-ok="Yes, send reminders"
              data-confirm-icon="question"
              data-loading="Sending reminders...">
            @csrf
            @foreach($activeQuery as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
            <input type="hidden" name="scope" value="at_risk">
            <button type="submit" class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:12px;font-size:12px;"><i class="fas fa-envelope"></i> Send Reminder to All</button>
        </form>
        @endif
    </div>

    <!-- WEAKEST TOPICS: the chart at a glance; the lightbulb opens every topic with the explanation -->
    <div class="side-card">
        <div class="side-head">
            <div class="side-title"><i class="fas fa-chart-bar" style="margin-right:6px;color:var(--accent);"></i>Class Weak Topics</div>
            @if($weakTopics->isNotEmpty())
                <button type="button" class="bulb-btn" data-open-weak aria-haspopup="dialog" title="Show every weak topic and why">
                    <i class="fas fa-lightbulb"></i> Why?
                </button>
            @endif
        </div>
        @if($weakTopics->isNotEmpty())
            <div class="chart-box-sm" id="classWeakChartBox"><div class="chart-inner" id="classWeakChartInner"><canvas id="chartClassWeak"></canvas></div></div>
        @else
            <div class="muted-empty">Not enough attempts yet to rank topics.</div>
        @endif
    </div>

    {{-- All weak topics with the reason and the misconception behind each --}}
    <div class="wk-overlay" id="weakModal" role="dialog" aria-modal="true" aria-labelledby="weakModalTitle" hidden>
        <div class="wk-modal">
            <div class="wk-head">
                <div>
                    <div class="wk-title" id="weakModalTitle"><i class="fas fa-lightbulb"></i> Class weak topics</div>
                    <div class="wk-sub">{{ $weakTopics->count() }} {{ \Illuminate\Support\Str::plural('topic', $weakTopics->count()) }} below the class benchmark, with why each one is flagged.</div>
                </div>
                <button type="button" class="wk-close" data-close-weak aria-label="Close"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="wk-body">
                @forelse($weakTopics as $t)
                    <div class="weak-item" style="align-items:flex-start;">
                        <div class="weak-icon" style="background:{{ ($subjColors[$t->subject_code] ?? '#888') }}20;color:{{ $subjColors[$t->subject_code] ?? '#888' }};">
                            <i class="fas {{ $subjIcons[$t->subject_code] ?? 'fa-book' }}"></i>
                        </div>
                        <span class="weak-name">
                            {{ $t->topic }}<br><span class="weak-sub">{{ $t->subject_code }}</span>
                            <div class="weak-why">{{ $t->why }}</div>
                            @if($t->miss)<div class="weak-why weak-miss">{{ $t->miss }}</div>@endif
                        </span>
                        <span class="weak-rate">{{ $t->accuracy }}%</span>
                    </div>
                @empty
                    <div class="muted-empty">Not enough attempts yet to rank topics.</div>
                @endforelse
            </div>
            <div class="wk-foot">
                <a href="{{ route('faculty.test-bank') }}"><i class="fas fa-plus"></i> Add questions for these topics</a>
            </div>
        </div>
    </div>
</div>
