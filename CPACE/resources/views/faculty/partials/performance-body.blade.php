@php
    $subjColors = ['FAR'=>'#3b82f6','AFAR'=>'#17a2b8','MS'=>'#8b5cf6','TAX'=>'#27ae60','AUD'=>'#e8567d','RFBT'=>'#f59e0b'];
    $subjIcons  = ['FAR'=>'fa-coins','AFAR'=>'fa-layer-group','MS'=>'fa-chart-pie','TAX'=>'fa-landmark','AUD'=>'fa-magnifying-glass-chart','RFBT'=>'fa-gavel'];
@endphp

<!-- DYNAMIC BODY (swapped in place via AJAX) -->
<div class="perf-layout a2" id="perfBody">
    <!-- TABLE -->
    <div class="table-card">
        <div class="table-head-bar">
            <span class="count">Showing <strong>{{ $pagination['total'] }}</strong> student{{ $pagination['total'] === 1 ? '' : 's' }}</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Avg. Score</th>
                    <th>Subjects Covered</th>
                    <th>Quizzes</th>
                    <th>Trend</th>
                    <th>Last Active</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $st)
                <tr>
                    <td>
                        <div class="student-cell">
                            <div class="student-av" style="background:{{ $st['color'] }};">{{ $st['initials'] }}</div>
                            <div>
                                <div class="student-name">{{ $st['name'] }}</div>
                                <div class="student-email">{{ $st['email'] }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="score-cell">
                            <span class="score-num" style="color:{{ $st['score'] >= 75 ? '#059669' : ($st['score'] >= 60 ? '#d97706' : '#c0392b') }};">{{ $st['score'] }}%</span>
                            <div class="score-bar-bg">
                                <div class="score-bar-fill" style="width:{{ $st['score'] }}%;background:{{ $st['score'] >= 75 ? '#10b981' : ($st['score'] >= 60 ? '#f59e0b' : '#c0392b') }};"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="subj-dots">
                            @forelse($st['subjects'] as $code)
                                <div class="subj-dot" style="background:{{ ($subjColors[$code] ?? '#888') }}20;color:{{ $subjColors[$code] ?? '#888' }};">{{ $code }}</div>
                            @empty
                                <span style="font-size:11px;color:#ccc;">—</span>
                            @endforelse
                        </div>
                    </td>
                    <td style="font-size:13px;font-weight:600;color:#1a1a1a;">{{ $st['quizzes'] }}</td>
                    <td>
                        @if($st['trend'] === 'up')
                            <span class="trend-badge t-up"><i class="fas fa-arrow-up"></i> Up</span>
                        @elseif($st['trend'] === 'down')
                            <span class="trend-badge t-down"><i class="fas fa-arrow-down"></i> Down</span>
                        @else
                            <span class="trend-badge t-flat"><i class="fas fa-minus"></i> Flat</span>
                        @endif
                    </td>
                    <td><span class="last-active">{{ $st['last_active'] ? \Illuminate\Support\Carbon::parse($st['last_active'])->diffForHumans() : '—' }}</span></td>
                    <td><button type="button" class="view-btn" onclick="openStudent({{ $st['id'] }})"><i class="fas fa-eye"></i> View</button></td>
                </tr>
                @empty
                <tr class="empty-row"><td colspan="7"><i class="fas fa-inbox" style="font-size:22px;display:block;margin-bottom:8px;color:#ddd;"></i>No students match the current filters.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($pagination['total'] > 0)
        <div class="pagination">
            <span class="pag-info">Showing {{ $pagination['from'] }}–{{ $pagination['to'] }} of {{ $pagination['total'] }} students</span>
            <div class="pag-btns">
                <a href="{{ route('faculty.performance', array_merge($activeQuery, ['page' => $pagination['current'] - 1])) }}"
                   class="pag-btn {{ $pagination['current'] <= 1 ? 'disabled' : '' }}"><i class="fas fa-chevron-left"></i></a>
                @for($p = 1; $p <= $pagination['last']; $p++)
                    <a href="{{ route('faculty.performance', array_merge($activeQuery, ['page' => $p])) }}"
                       class="pag-btn {{ $p === $pagination['current'] ? 'active' : '' }}">{{ $p }}</a>
                @endfor
                <a href="{{ route('faculty.performance', array_merge($activeQuery, ['page' => $pagination['current'] + 1])) }}"
                   class="pag-btn {{ $pagination['current'] >= $pagination['last'] ? 'disabled' : '' }}"><i class="fas fa-chevron-right"></i></a>
            </div>
        </div>
        @endif
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
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
            <form method="POST" action="{{ route('faculty.performance.remind') }}" onsubmit="return confirm('Send a study reminder to all at-risk students?');">
                @csrf
                @foreach($activeQuery as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                <input type="hidden" name="scope" value="at_risk">
                <button type="submit" class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:12px;font-size:12px;"><i class="fas fa-envelope"></i> Send Reminder to All</button>
            </form>
            @endif
        </div>

        <!-- WEAKEST TOPICS -->
        <div class="side-card">
            <div class="side-title"><i class="fas fa-chart-bar" style="margin-right:6px;color:var(--accent);"></i>Class Weak Topics</div>
            @forelse($weakTopics as $t)
            <div class="weak-item">
                <div class="weak-icon" style="background:{{ ($subjColors[$t->subject_code] ?? '#888') }}20;color:{{ $subjColors[$t->subject_code] ?? '#888' }};">
                    <i class="fas {{ $subjIcons[$t->subject_code] ?? 'fa-book' }}"></i>
                </div>
                <span class="weak-name">{{ $t->topic }}<br><span class="weak-sub">{{ $t->subject_code }}</span></span>
                <span class="weak-rate">{{ $t->accuracy }}%</span>
            </div>
            @empty
                <div class="muted-empty">Not enough attempts yet to rank topics.</div>
            @endforelse
            <a href="{{ route('faculty.test-bank') }}" style="display:flex;align-items:center;justify-content:center;gap:6px;font-size:12px;color:var(--accent);text-decoration:none;margin-top:14px;font-weight:600;">Add Questions for These Topics <i class="fas fa-arrow-right"></i></a>
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
    </div>

    {{-- Per-student data for the detail modal (current page only). --}}
    <script type="application/json" id="perfData">{!! json_encode([
        'students' => $students->keyBy('id'),
        'details'  => $details,
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
</div>
