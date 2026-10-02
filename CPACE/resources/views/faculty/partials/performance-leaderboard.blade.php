{{--
    Leaderboard with a podium, plus where each student is strong and weak.
    Expects $leaderboard (see FacultyPerformanceController::leaderboard()).
    Swapped in place with the rest of the page data.
--}}
@php
    $lbEntries = collect($leaderboard['entries'] ?? []);
    $lbTone = fn ($acc) => $acc >= 75 ? 'good' : ($acc >= 50 ? 'mid' : 'bad');
    $lbSlot = fn ($rank) => $lbEntries->firstWhere('rank', $rank);
@endphp
<section class="lb2" id="perfLeaderboard" aria-label="Leaderboard">
    <div class="lb2-head">
        <div>
            <div class="lb2-title"><i class="fas fa-trophy"></i> Leaderboard</div>
            <div class="lb2-sub">Top students by accuracy, with where each one is strong and where they need help.</div>
        </div>
        <div class="lb2-note">
            {{ number_format($leaderboard['ranked'] ?? 0) }} ranked
            @if(($leaderboard['unranked'] ?? 0) > 0)
                · {{ $leaderboard['unranked'] }} not yet ranked (under {{ $leaderboard['min_items'] }} answers)
            @endif
        </div>
    </div>

    @if($lbEntries->isEmpty())
        <div class="lb2-empty"><i class="fas fa-ranking-star"></i> No student has answered {{ $leaderboard['min_items'] ?? 10 }} questions in this view yet.</div>
    @else
        <div class="lb2-grid">
            {{-- Podium: 2nd, 1st, 3rd standing on a platform --}}
            <div class="podium" role="list">
                @foreach([2, 1, 3] as $place)
                    @php $e = $lbSlot($place); @endphp
                    <div class="pod pod-{{ $place }} {{ $e ? '' : 'is-empty' }}" role="listitem">
                        @if($e)
                            @if($place === 1)<i class="fas fa-crown pod-crown" aria-hidden="true"></i>@endif
                            <div class="pod-av" style="background:{{ $e['color'] }}">
                                @if($e['avatar'])<img src="{{ $e['avatar'] }}" alt="">@else{{ $e['initials'] }}@endif
                            </div>
                            <div class="pod-name" title="{{ $e['name'] }}">{{ $e['name'] }}</div>
                            <div class="pod-score">{{ $e['score'] }}<small>%</small></div>
                            <div class="pod-meta">{{ number_format($e['attempted']) }} answers</div>
                            <div class="pod-skills">
                                @if($e['best'])<span class="skill good" title="Strongest subject"><i class="fas fa-arrow-up"></i> {{ $e['best']['code'] }} {{ $e['best']['accuracy'] }}%</span>@endif
                                @if($e['worst'])<span class="skill {{ $lbTone($e['worst']['accuracy']) === 'good' ? 'mid' : $lbTone($e['worst']['accuracy']) }}" title="Weakest subject"><i class="fas fa-arrow-down"></i> {{ $e['worst']['code'] }} {{ $e['worst']['accuracy'] }}%</span>@endif
                            </div>
                        @else
                            <div class="pod-av pod-av-empty"><i class="fas fa-user"></i></div>
                            <div class="pod-name">No student yet</div>
                        @endif
                        <div class="pod-base"><span>{{ $place }}</span></div>
                    </div>
                @endforeach
            </div>

            {{-- The rest of the top 10, with strengths and weaknesses --}}
            @if($lbEntries->where('rank', '>', 3)->isNotEmpty())
            <div class="lb2-list">
                @foreach($lbEntries->where('rank', '>', 3) as $e)
                    <div class="lb2-row">
                        <span class="lb2-rank">{{ $e['rank'] }}</span>
                        <span class="lb2-av" style="background:{{ $e['color'] }}">@if($e['avatar'])<img src="{{ $e['avatar'] }}" alt="">@else{{ $e['initials'] }}@endif</span>
                        <div class="lb2-who">
                            <div class="lb2-name">{{ $e['name'] }}</div>
                            <div class="lb2-skills">
                                @foreach(collect($e['subject_scores'])->take(6) as $sub)
                                    <span class="skill {{ $lbTone($sub['accuracy']) }}" title="{{ $sub['name'] }} — {{ $sub['attempts'] }} answers">{{ $sub['code'] }} {{ $sub['accuracy'] }}%</span>
                                @endforeach
                                @if($e['weak_topics'])
                                    <span class="lb2-weak" title="Weakest topics"><i class="fas fa-triangle-exclamation"></i> {{ implode(' · ', array_map(fn ($t) => \Illuminate\Support\Str::limit($t, 28), $e['weak_topics'])) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="lb2-score">{{ $e['score'] }}%</div>
                    </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Podium students' full subject picture --}}
        @if($lbEntries->where('rank', '<=', 3)->isNotEmpty())
            <div class="lb2-legend">
                <span class="skill good">75%+ strong</span>
                <span class="skill mid">50–74% fair</span>
                <span class="skill bad">under 50% needs help</span>
            </div>
        @endif
    @endif
</section>
