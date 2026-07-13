<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #7B1D1D;
            --primary-hover: #6a1818;
            --primary-light: #f5e8e8;
            --accent: #c0392b;
            --green: #10b981;
            --blue: #3b82f6;
            --orange: #f59e0b;
            --purple: #8b5cf6;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

        /* MAIN */
        .main { margin-left:230px; padding:26px 30px; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }

        /* TOP BAR */
        .topbar {
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:24px; gap:16px;
        }
        .topbar-left { display:flex; align-items:center; gap:12px; }
        .page-title { font-size:26px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .topbar-right { display:flex; align-items:center; gap:12px; }
        .btn {
            display:inline-flex; align-items:center; gap:7px;
            padding:9px 18px; border-radius:8px;
            font-size:13px; font-weight:600; font-family:'Poppins',sans-serif;
            cursor:pointer; border:none; text-decoration:none; transition:all .2s;
        }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-outline { background:white; color:var(--primary); border:1.5px solid var(--primary); }
        .btn-outline:hover { background:var(--primary-light); }

        /* STATS ROW */
        .stats-row {
            display:grid; grid-template-columns:repeat(4,1fr);
            gap:16px; margin-bottom:22px;
        }
        .stat-card {
            background:white; border-radius:14px; padding:20px 22px;
        }
        .stat-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; }
        .stat-icon {
            width:40px; height:40px; border-radius:10px;
            display:flex; align-items:center; justify-content:center; font-size:18px;
        }
        .si-red    { background:#fde8e8; color:var(--accent); }
        .si-green  { background:#d1fae5; color:var(--green); }
        .si-blue   { background:#dbeafe; color:var(--blue); }
        .si-orange { background:#fef3c7; color:var(--orange); }
        .si-purple { background:#ede9fe; color:var(--purple); }
        .stat-num  { font-size:28px; font-weight:700; color:#1a1a1a; line-height:1; margin-bottom:4px; }
        .stat-lbl  { font-size:11px; color:#999; }
        .stat-chg  { font-size:11px; color:var(--green); margin-top:4px; }
        .stat-chg.neutral { color:#999; }

        /* MAIN GRID */
        .main-grid {
            display:grid; grid-template-columns:1fr 340px;
            gap:18px; margin-bottom:18px;
        }

        /* CARDS */
        .card { background:white; border-radius:14px; padding:22px; }
        .card + .card { margin-top:18px; }
        .card-head {
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:18px;
        }
        .card-title { font-size:14px; font-weight:600; color:#1a1a1a; }
        .card-link { font-size:12px; color:var(--accent); text-decoration:none; font-weight:500; }
        .card-link:hover { text-decoration:underline; }

        /* TABLE */
        table { width:100%; border-collapse:collapse; }
        thead th {
            text-align:left; font-size:11px; color:#aaa;
            font-weight:600; padding:0 10px 12px;
            text-transform:uppercase; letter-spacing:.4px;
        }
        tbody tr { border-top:1px solid #f5f5f5; }
        tbody td { padding:12px 10px; font-size:13px; vertical-align:middle; }
        tbody tr:hover { background:#fafafa; }

        .subj-badge {
            display:inline-block; padding:3px 9px; border-radius:5px;
            font-size:10px; font-weight:700;
        }
        .b-far  { background:#dbeafe; color:#2563eb; }
        .b-aud  { background:#fce7f3; color:#db2777; }
        .b-tax  { background:#d1fae5; color:#059669; }
        .b-ms   { background:#ede9fe; color:#7c3aed; }
        .b-rfbt { background:#fef3c7; color:#d97706; }
        .b-afar { background:#cffafe; color:#0891b2; }

        .diff-badge {
            display:inline-block; padding:3px 9px; border-radius:5px;
            font-size:10px; font-weight:600;
        }
        .d-easy   { background:#d1fae5; color:#059669; }
        .d-medium { background:#fef3c7; color:#d97706; }
        .d-hard   { background:#fde8e8; color:var(--accent); }

        .status-dot {
            width:7px; height:7px; border-radius:50%;
            display:inline-block; margin-right:5px;
        }
        .dot-active { background:var(--green); }
        .dot-draft  { background:#d1d5db; }

        .action-btn {
            width:28px; height:28px; border:none; border-radius:6px;
            cursor:pointer; font-size:12px;
            display:inline-flex; align-items:center; justify-content:center;
            transition:all .2s;
        }
        .ab-edit { background:#dbeafe; color:var(--blue); }
        .ab-del  { background:#fde8e8; color:var(--accent); }
        .ab-edit:hover { background:#bfdbfe; }
        .ab-del:hover  { background:#fecaca; }

        /* RIGHT COLUMN */
        .right-col { display:flex; flex-direction:column; gap:18px; }

        /* QUICK ACTIONS */
        .quick-actions { display:flex; flex-direction:column; gap:10px; }
        .qa-btn {
            display:flex; align-items:center; gap:12px;
            padding:13px 16px; border-radius:10px;
            text-decoration:none; transition:all .2s; cursor:pointer;
            border:none; width:100%; font-family:'Poppins',sans-serif;
        }
        .qa-btn .qa-icon {
            width:36px; height:36px; border-radius:8px;
            display:flex; align-items:center; justify-content:center;
            font-size:16px; flex-shrink:0;
        }
        .qa-title  { font-size:13px; font-weight:600; color:#1a1a1a; display:block; }
        .qa-sub    { font-size:11px; color:#999; display:block; }
        .qa-btn.primary-qa { background:var(--primary); }
        .qa-btn.primary-qa .qa-title,
        .qa-btn.primary-qa .qa-sub { color:rgba(255,255,255,.9); }
        .qa-btn.primary-qa .qa-icon { background:rgba(255,255,255,.2); color:white; }
        .qa-btn.secondary-qa { background:#f9f9f9; }
        .qa-btn.secondary-qa .qa-icon { background:white; }
        .qa-btn:hover { opacity:.9; transform:translateY(-1px); }

        /* SUBJECT DISTRIBUTION */
        .subj-dist-item { margin-bottom:14px; }
        .subj-dist-item:last-child { margin-bottom:0; }
        .subj-dist-top {
            display:flex; justify-content:space-between;
            font-size:12px; color:#555; margin-bottom:5px;
        }
        .subj-dist-top .val { font-weight:700; color:#1a1a1a; }
        .bar-bg { height:7px; background:#f0f0f0; border-radius:5px; overflow:hidden; }
        .bar-fill { height:100%; border-radius:5px; }

        /* ACTIVITY FEED */
        .activity-item {
            display:flex; align-items:center; gap:12px;
            padding:11px 0; border-bottom:1px solid #f5f5f5;
        }
        .activity-item:last-child { border-bottom:none; }
        .act-icon {
            width:34px; height:34px; border-radius:9px;
            display:flex; align-items:center; justify-content:center;
            font-size:14px; flex-shrink:0;
        }
        .act-name { font-size:13px; font-weight:600; color:#1a1a1a; margin-bottom:2px; }
        .act-sub  { font-size:11px; color:#999; }
        .act-time { font-size:11px; color:#bbb; white-space:nowrap; }

        /* BOTTOM ROW */
        .bottom-row {
            display:grid; grid-template-columns:repeat(3,1fr);
            gap:18px;
        }

        .mini-stat {
            display:flex; align-items:center; gap:12px;
            padding:13px 0; border-bottom:1px solid #f5f5f5;
        }
        .mini-stat:last-child { border-bottom:none; }
        .mini-icon {
            width:32px; height:32px; border-radius:8px;
            display:flex; align-items:center; justify-content:center;
            font-size:13px; flex-shrink:0;
        }
        .mini-label { font-size:12px; color:#888; margin-bottom:1px; }
        .mini-val   { font-size:14px; font-weight:700; color:#1a1a1a; }

        @keyframes fadeUp {
            from { opacity:0; transform:translateY(14px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .a0 { animation:fadeUp .4s ease both; }
        .a1 { animation:fadeUp .4s .07s ease both; }
        .a2 { animation:fadeUp .4s .14s ease both; }
        .a3 { animation:fadeUp .4s .21s ease both; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            /* table overflow */
            .card { overflow-x: auto; }
            table { min-width: 520px; }
            /* bottom 3-col → 1-col */
            .bottom-row { grid-template-columns: 1fr !important; }
            /* right column stacks under left */
            .right-col { flex-direction: column; }
            /* stat numbers */
            .stat-num { font-size: 22px; }
        }

        @media (max-width: 480px) {
            .stat-num { font-size: 20px; }
            .card-title { font-size: 13px; }
            .qa-btn { padding: 10px 12px; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
@include('partials.faculty-sidebar', ['active' => 'dashboard'])

<!-- MAIN -->
<main class="main">

    <!-- TOPBAR -->
    <div class="topbar a0">
        <div class="topbar-left">
            <div>
                <div class="page-title">Faculty Dashboard</div>
                <div class="page-sub">Welcome back, {{ Auth::user()->name }}. Here's your overview.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('faculty.question.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Question</a>
            <a href="{{ route('faculty.test-bank') }}" class="btn btn-outline"><i class="fas fa-database"></i> Test Bank</a>
            @include('partials.topbar-actions')
        </div>
    </div>

    <!-- STATS -->
    <div class="stats-row a1">
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Total Questions</div>
                    <div class="stat-num">{{ number_format($stats['total_questions']) }}</div>
                    @if($stats['added_this_week'] > 0)
                        <div class="stat-chg"><i class="fas fa-arrow-up"></i> {{ $stats['added_this_week'] }} this week</div>
                    @else
                        <div class="stat-chg neutral">No new this week</div>
                    @endif
                </div>
                <div class="stat-icon si-red"><i class="fas fa-database"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Active Students</div>
                    <div class="stat-num">{{ number_format($stats['active_students']) }}</div>
                    @if($stats['new_this_month'] > 0)
                        <div class="stat-chg"><i class="fas fa-arrow-up"></i> {{ $stats['new_this_month'] }} new this month</div>
                    @else
                        <div class="stat-chg neutral">With graded activity</div>
                    @endif
                </div>
                <div class="stat-icon si-green"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Avg. Student Score</div>
                    <div class="stat-num">{{ $stats['avg_score'] }}%</div>
                    @if($stats['avg_delta'] === null)
                        <div class="stat-chg neutral">Across all quizzes</div>
                    @elseif($stats['avg_delta'] >= 0)
                        <div class="stat-chg"><i class="fas fa-arrow-up"></i> {{ $stats['avg_delta'] }}% from last month</div>
                    @else
                        <div class="stat-chg" style="color:var(--accent);"><i class="fas fa-arrow-down"></i> {{ abs($stats['avg_delta']) }}% from last month</div>
                    @endif
                </div>
                <div class="stat-icon si-blue"><i class="fas fa-chart-bar"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-lbl">Questions Added</div>
                    <div class="stat-num">{{ number_format($stats['added_this_week']) }}</div>
                    <div class="stat-chg neutral">This week</div>
                </div>
                <div class="stat-icon si-orange"><i class="fas fa-pen"></i></div>
            </div>
        </div>
    </div>

    <!-- MAIN GRID -->
    <div class="main-grid a2">
        <!-- LEFT -->
        <div>
            <!-- RECENT QUESTIONS -->
            <div class="card">
                <div class="card-head">
                    <span class="card-title">Recently Added Questions</span>
                    <a href="{{ route('faculty.test-bank') }}" class="card-link">View All</a>
                </div>
                @php
                    $badgeClass = [
                        'FAR' => 'b-far', 'AUD' => 'b-aud', 'TAX' => 'b-tax',
                        'MS' => 'b-ms', 'RFBT' => 'b-rfbt', 'AFAR' => 'b-afar',
                    ];
                    $diffClass = ['Easy' => 'd-easy', 'Medium' => 'd-medium', 'Hard' => 'd-hard'];
                @endphp
                <table>
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Subject</th>
                            <th>Difficulty</th>
                            <th>Status</th>
                            <th>Added</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentQuestions as $q)
                        <tr>
                            <td style="max-width:260px;">
                                <div style="font-weight:600;color:#1a1a1a;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:250px;">{{ $q['text'] }}</div>
                                <div style="font-size:11px;color:#aaa;">{{ $q['type_label'] }}</div>
                            </td>
                            <td><span class="subj-badge {{ $badgeClass[$q['subject']] ?? 'b-far' }}">{{ $q['subject'] }}</span></td>
                            <td><span class="diff-badge {{ $diffClass[$q['difficulty']] ?? 'd-medium' }}">{{ $q['difficulty'] }}</span></td>
                            <td>
                                @if($q['active'])
                                    <span class="status-dot dot-active"></span><span style="font-size:12px;">Active</span>
                                @else
                                    <span class="status-dot dot-draft"></span><span style="font-size:12px;color:#aaa;">Draft</span>
                                @endif
                            </td>
                            <td style="font-size:11px;color:#aaa;">{{ $q['ago'] }}</td>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('faculty.question.edit', $q['id']) }}" class="action-btn ab-edit"><i class="fas fa-pen"></i></a>
                                <form method="POST" action="{{ route('faculty.question.destroy', $q['id']) }}" style="display:inline;" onsubmit="return confirm('Delete this question?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn ab-del" style="margin-left:4px;"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center;color:#aaa;padding:26px;">No questions in your subjects yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- STUDENT ACTIVITY -->
            <div class="card">
                <div class="card-head">
                    <span class="card-title">Recent Student Activity</span>
                    <a href="{{ route('faculty.performance') }}" class="card-link">View All</a>
                </div>
                @forelse($recentActivity as $act)
                <div class="activity-item">
                    <div class="act-icon" style="background:{{ $act['tone']['bg'] }};color:{{ $act['tone']['fg'] }};"><i class="fas {{ $act['tone']['icon'] }}"></i></div>
                    <div style="flex:1">
                        <div class="act-name">{{ $act['name'] }}</div>
                        <div class="act-sub">{!! $act['detail'] !!}</div>
                    </div>
                    <div class="act-time">{{ $act['ago'] }}</div>
                </div>
                @empty
                <div style="text-align:center;color:#aaa;padding:24px;font-size:13px;">No recent student activity in your subjects.</div>
                @endforelse
            </div>
        </div>

        <!-- RIGHT -->
        <div class="right-col">
            <!-- QUICK ACTIONS -->
            <div class="card">
                <div class="card-head"><span class="card-title">Quick Actions</span></div>
                <div class="quick-actions">
                    <a href="{{ route('faculty.question.create') }}" class="qa-btn primary-qa">
                        <div class="qa-icon"><i class="fas fa-plus"></i></div>
                        <div>
                            <span class="qa-title">Add New Question</span>
                            <span class="qa-sub">Add to test bank</span>
                        </div>
                    </a>
                    <a href="{{ route('faculty.subjects') }}" class="qa-btn secondary-qa">
                        <div class="qa-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-book-open"></i></div>
                        <div>
                            <span class="qa-title">Manage Subjects</span>
                            <span class="qa-sub">Add or edit topics</span>
                        </div>
                    </a>
                    <a href="{{ route('faculty.performance') }}" class="qa-btn secondary-qa">
                        <div class="qa-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-users"></i></div>
                        <div>
                            <span class="qa-title">View Student Scores</span>
                            <span class="qa-sub">Monitor performance</span>
                        </div>
                    </a>
                    <a href="{{ route('faculty.reports') }}" class="qa-btn secondary-qa">
                        <div class="qa-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-file-export"></i></div>
                        <div>
                            <span class="qa-title">Export Report</span>
                            <span class="qa-sub">Download CSV / PDF</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- QUESTION DISTRIBUTION -->
            <div class="card">
                <div class="card-head"><span class="card-title">Questions by Subject</span></div>
                @forelse($bySubject as $s)
                <div class="subj-dist-item">
                    <div class="subj-dist-top"><span>{{ $s['code'] }}</span><span class="val">{{ number_format($s['total']) }}</span></div>
                    <div class="bar-bg"><div class="bar-fill" style="width:{{ $s['width'] }}%;background:{{ $s['color'] }};"></div></div>
                </div>
                @empty
                <div style="text-align:center;color:#aaa;padding:16px;font-size:13px;">No subjects assigned.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- BOTTOM ROW -->
    <div class="bottom-row a3">
        <div class="card">
            <div class="card-head"><span class="card-title">Question Type Breakdown</span></div>
            <div class="mini-stat">
                <div class="mini-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-list-ul"></i></div>
                <div>
                    <div class="mini-label">Multiple Choice</div>
                    <div class="mini-val">{{ number_format($byType['mcq']['count']) }} <span style="font-size:11px;color:#aaa;font-weight:400;">({{ $byType['mcq']['pct'] }}%)</span></div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-check-square"></i></div>
                <div>
                    <div class="mini-label">True / False</div>
                    <div class="mini-val">{{ number_format($byType['tf']['count']) }} <span style="font-size:11px;color:#aaa;font-weight:400;">({{ $byType['tf']['pct'] }}%)</span></div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-layer-group"></i></div>
                <div>
                    <div class="mini-label">Total Questions</div>
                    <div class="mini-val">{{ number_format($byType['total']) }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><span class="card-title">Difficulty Distribution</span></div>
            <div class="mini-stat">
                <div class="mini-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-smile"></i></div>
                <div>
                    <div class="mini-label">Easy</div>
                    <div class="mini-val">{{ number_format($byDifficulty['easy']['count']) }} <span style="font-size:11px;color:#aaa;font-weight:400;">({{ $byDifficulty['easy']['pct'] }}%)</span></div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-meh"></i></div>
                <div>
                    <div class="mini-label">Medium</div>
                    <div class="mini-val">{{ number_format($byDifficulty['medium']['count']) }} <span style="font-size:11px;color:#aaa;font-weight:400;">({{ $byDifficulty['medium']['pct'] }}%)</span></div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-icon" style="background:#fde8e8;color:var(--accent);"><i class="fas fa-frown"></i></div>
                <div>
                    <div class="mini-label">Hard</div>
                    <div class="mini-val">{{ number_format($byDifficulty['hard']['count']) }} <span style="font-size:11px;color:#aaa;font-weight:400;">({{ $byDifficulty['hard']['pct'] }}%)</span></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><span class="card-title">Top Performing Students</span></div>
            @php $rankStyles = [['#fde8e8','var(--accent)'],['#dbeafe','#2563eb'],['#d1fae5','#059669']]; @endphp
            @forelse($topStudents as $i => $st)
            <div class="mini-stat">
                <div class="mini-icon" style="background:{{ $rankStyles[$i][0] ?? '#f1f5f9' }};color:{{ $rankStyles[$i][1] ?? '#64748b' }};font-weight:700;font-size:14px;">{{ $i + 1 }}</div>
                <div style="flex:1">
                    <div class="mini-label">{{ $st['name'] }}</div>
                    <div class="mini-val" style="font-size:13px;">{{ $st['score'] }}% avg</div>
                </div>
            </div>
            @empty
            <div style="text-align:center;color:#aaa;padding:24px;font-size:13px;">Not enough graded activity yet.</div>
            @endforelse
        </div>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
});
</script>
</body>
</html>
