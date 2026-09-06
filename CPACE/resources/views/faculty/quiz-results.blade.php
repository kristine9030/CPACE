<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results · {{ $quiz->title }} - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; --orange:#f59e0b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:16px; position:relative; z-index:100; }
        .breadcrumb { display:flex; align-items:center; gap:6px; font-size:12px; color:#aaa; margin-bottom:4px; }
        .breadcrumb a { color:var(--accent); text-decoration:none; }
        .page-title { font-size:24px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-ghost { background:#fff; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }
        .btn-outline { background:#fff; color:var(--primary); border:1.5px solid var(--primary); }
        .btn-outline:hover { background:var(--primary-light); }

        .stats-row { display:grid; grid-template-columns:repeat(5,1fr); gap:14px; margin-bottom:20px; }
        .stat { background:#fff; border-radius:12px; padding:16px 18px; }
        .stat b { display:block; font-size:22px; color:#1a1a1a; line-height:1; }
        .stat span { font-size:11px; color:#999; margin-top:4px; display:block; }

        .layout { display:grid; grid-template-columns:1fr 360px; gap:20px; align-items:start; }
        .card { background:#fff; border-radius:14px; overflow:hidden; }
        .card-head { padding:16px 20px; border-bottom:1px solid #f2f2f2; font-size:13.5px; font-weight:700; color:#222; display:flex; justify-content:space-between; align-items:center; }
        .card-head small { font-weight:500; color:#aaa; font-size:11px; }
        table { width:100%; border-collapse:collapse; }
        thead th { text-align:left; font-size:11px; color:#aaa; font-weight:600; padding:11px 16px; text-transform:uppercase; letter-spacing:.4px; background:#fafafa; border-bottom:1px solid #f5f5f5; }
        tbody td { padding:13px 16px; font-size:13px; border-bottom:1px solid #f8f8f8; vertical-align:middle; }
        tbody tr:last-child td { border-bottom:none; }
        .s-name { font-weight:600; color:#1a1a1a; }
        .s-email { font-size:11px; color:#aaa; }
        .pct { font-weight:700; }
        .pct.good { color:#059669; } .pct.mid { color:#d97706; } .pct.bad { color:var(--accent); }
        .pill { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; }
        .p-done { background:#d1fae5; color:#059669; }
        .p-prog { background:#fef3c7; color:#d97706; }
        .empty { padding:44px 20px; text-align:center; color:#bbb; font-size:13px; }

        .item-row { padding:13px 18px; border-bottom:1px solid #f6f6f6; }
        .item-row:last-child { border-bottom:none; }
        .item-q { font-size:12.5px; color:#333; line-height:1.5; margin-bottom:7px; }
        .item-q b { color:var(--primary); margin-right:5px; }
        .bar { height:6px; background:#f0f0f0; border-radius:4px; overflow:hidden; }
        .bar span { display:block; height:100%; border-radius:4px; }
        .item-meta { display:flex; justify-content:space-between; font-size:11px; color:#999; margin-top:5px; }

        @media (max-width:1100px) { .stats-row { grid-template-columns:repeat(3,1fr); } .layout { grid-template-columns:1fr; } }
        @media (max-width:900px) { .main { margin-left:68px; } }
        @media (max-width:768px) { .main { margin-left:0; padding:16px; } .stats-row { grid-template-columns:repeat(2,1fr); } .card { overflow-x:auto; } .topbar { flex-direction:column; align-items:flex-start; } }
    </style>
</head>
<body>

@include('partials.faculty-sidebar', ['active' => 'quizzes'])

<main class="main">
    <div class="topbar">
        <div>
            <div class="breadcrumb"><a href="{{ route('faculty.quizzes') }}">Class Quizzes</a> <i class="fas fa-chevron-right" style="font-size:9px;"></i> Results</div>
            <div class="page-title">{{ $quiz->title }}</div>
            <div class="page-sub">
                {{ $quiz->subject?->code ?? 'No subject' }} · {{ $quiz->items->count() }} question{{ $quiz->items->count() === 1 ? '' : 's' }} · {{ $quiz->totalPoints() }} points
                @if($quiz->due_at) · due {{ $quiz->due_at->format('M j, Y g:i A') }} @endif
            </div>
        </div>
        <div class="topbar-right">
            @include('partials.topbar-actions')
            <a href="{{ route('faculty.quizzes.edit', $quiz->id) }}" class="btn btn-outline"><i class="fas fa-pen"></i> Edit quiz</a>
            <a href="{{ route('faculty.quizzes') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat"><b>{{ $stats['started'] }}</b><span>Students started</span></div>
        <div class="stat"><b>{{ $stats['submitted'] }}</b><span>Submitted</span></div>
        <div class="stat"><b>{{ $stats['average'] !== null ? $stats['average'] . '%' : '—' }}</b><span>Average score</span></div>
        <div class="stat"><b>{{ $stats['highest'] !== null ? $stats['highest'] . '%' : '—' }}</b><span>Highest</span></div>
        <div class="stat"><b>{{ $stats['lowest'] !== null ? $stats['lowest'] . '%' : '—' }}</b><span>Lowest</span></div>
    </div>

    <div class="layout">
        <div class="card">
            <div class="card-head">Student submissions <small>{{ $attempts->count() }}</small></div>
            @if($attempts->isEmpty())
                <div class="empty">No student has opened this quiz yet. Share the quiz link to get started.</div>
            @else
                <table>
                    <thead><tr><th>Student</th><th>Status</th><th>Score</th><th>%</th><th>Submitted</th></tr></thead>
                    <tbody>
                    @foreach($attempts as $a)
                        <tr>
                            <td>
                                <div class="s-name">{{ $a->student?->name ?? 'Unknown' }}</div>
                                <div class="s-email">{{ $a->student?->email }}</div>
                            </td>
                            <td>
                                @if($a->isSubmitted())
                                    <span class="pill p-done"><i class="fas fa-check"></i> Submitted</span>
                                @else
                                    <span class="pill p-prog"><i class="fas fa-hourglass-half"></i> In progress</span>
                                @endif
                            </td>
                            <td>{{ $a->isSubmitted() ? $a->score . ' / ' . $a->total_points : '—' }}</td>
                            <td>
                                @if($a->isSubmitted())
                                    @php $p = (float) $a->percent; @endphp
                                    <span class="pct {{ $p >= 75 ? 'good' : ($p >= 50 ? 'mid' : 'bad') }}">{{ round($p, 1) }}%</span>
                                @else — @endif
                            </td>
                            <td style="font-size:12px;color:#777;">{{ $a->submitted_at?->format('M j, g:i A') ?? 'started ' . $a->started_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card">
            <div class="card-head">Per-question accuracy <small>of {{ $stats['submitted'] }} submitted</small></div>
            @forelse($quiz->items as $i => $item)
                @php $st = $itemStats[$item->id]; $rate = $st['rate']; @endphp
                <div class="item-row">
                    <div class="item-q"><b>Q{{ $i + 1 }}</b>{{ \Illuminate\Support\Str::limit($item->question_text, 110) }}</div>
                    <div class="bar"><span style="width:{{ $rate ?? 0 }}%;background:{{ $rate === null ? '#ddd' : ($rate >= 75 ? '#10b981' : ($rate >= 50 ? '#f59e0b' : '#c0392b')) }};"></span></div>
                    <div class="item-meta">
                        <span>Correct: <b>{{ $item->correctLabel() }}</b> · {{ $item->points }} pt{{ $item->points === 1 ? '' : 's' }}</span>
                        <span>{{ $rate === null ? 'no data' : $st['right'] . ' correct (' . $rate . '%)' }}</span>
                    </div>
                </div>
            @empty
                <div class="empty">This quiz has no questions.</div>
            @endforelse
        </div>
    </div>
</main>

@include('partials.alerts')
</body>
</html>
