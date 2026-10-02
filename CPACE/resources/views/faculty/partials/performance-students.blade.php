{{-- Student table card — sits beside the leaderboard. Swapped in place via AJAX (#perfStudents). --}}
<section id="perfStudents" @if($pagination['total'] <= 3) style="align-self:start" @endif>
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
                    @elseif($st['trend'] === 'new')
                        <span class="trend-badge t-new"><i class="fas fa-star"></i> New</span>
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
</section>
