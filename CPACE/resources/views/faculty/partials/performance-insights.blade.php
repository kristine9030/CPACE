<!-- WHAT THIS VIEW MEANS (swapped in place via AJAX) — folded away until asked for -->
<div id="perfInsights">
    @if(!empty($insights))
        <details class="insights-fold" id="insightsFold">
            <summary>
                <span class="if-title"><i class="fas fa-lightbulb"></i> What this view means for you</span>
                <span class="if-count">{{ count($insights) }} {{ \Illuminate\Support\Str::plural('note', count($insights)) }}</span>
                <span class="if-hint"><span class="if-show">Show</span><span class="if-hide">Hide</span> <i class="fas fa-chevron-down"></i></span>
            </summary>
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
        </details>
    @endif
</div>
