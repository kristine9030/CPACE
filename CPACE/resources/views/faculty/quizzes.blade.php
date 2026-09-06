<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Quizzes - CPACE Faculty</title>
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
        .page-title { font-size:26px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; white-space:nowrap; }
        .btn-primary { background:var(--primary); color:#fff; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:#fff; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }

        .tabs { display:flex; gap:8px; margin-bottom:18px; flex-wrap:wrap; }
        .tab { display:inline-flex; align-items:center; gap:8px; padding:8px 16px; border-radius:22px; background:#fff; border:1px solid #ececec; font-size:13px; font-weight:600; color:#666; text-decoration:none; transition:all .18s; }
        .tab:hover { border-color:#d8d8d8; }
        .tab.active { background:var(--primary); color:#fff; border-color:var(--primary); }
        .tab .n { background:rgba(0,0,0,.06); border-radius:10px; padding:1px 8px; font-size:11px; }
        .tab.active .n { background:rgba(255,255,255,.22); }

        .grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:16px; }
        .quiz-card { background:#fff; border-radius:16px; padding:20px 22px; box-shadow:0 2px 10px rgba(0,0,0,.04); display:flex; flex-direction:column; gap:12px; position:relative; }
        .qc-top { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; }
        .qc-title { font-size:15px; font-weight:700; color:#1a1a1a; line-height:1.35; }
        .qc-subject { font-size:11px; color:#999; margin-top:3px; }
        .status-pill { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; white-space:nowrap; flex-shrink:0; }
        .sp-draft { background:#f3f4f6; color:#6b7280; }
        .sp-published { background:#d1fae5; color:#059669; }
        .sp-closed { background:#fde8e8; color:var(--accent); }
        .sp-expired { background:#fef3c7; color:#d97706; }
        .qc-meta { display:flex; flex-wrap:wrap; gap:14px; font-size:12px; color:#777; }
        .qc-meta i { color:#bbb; margin-right:5px; width:14px; text-align:center; }
        .qc-meta b { color:#333; font-weight:600; }
        .qc-link { display:flex; align-items:center; gap:8px; background:#f8f8f8; border:1px dashed #e0e0e0; border-radius:9px; padding:8px 10px; font-size:11.5px; color:#666; }
        .qc-link code { flex:1; font-family:inherit; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:#444; }
        .copy-btn { border:none; background:var(--primary-light); color:var(--primary); border-radius:6px; padding:5px 9px; font-size:11px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif; white-space:nowrap; }
        .copy-btn:hover { background:#ecd9d9; }
        .qc-actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:auto; padding-top:4px; }
        .qc-actions form { margin:0; }
        .mini { display:inline-flex; align-items:center; gap:6px; padding:7px 12px; border-radius:8px; font-size:12px; font-weight:600; border:none; cursor:pointer; text-decoration:none; font-family:'Poppins',sans-serif; transition:all .18s; }
        .m-edit { background:#dbeafe; color:#2563eb; } .m-edit:hover { background:#bfdbfe; }
        .m-res { background:#ede9fe; color:#7c3aed; } .m-res:hover { background:#ddd6fe; }
        .m-pub { background:#d1fae5; color:#059669; } .m-pub:hover { background:#a7f3d0; }
        .m-close { background:#fef3c7; color:#d97706; } .m-close:hover { background:#fde68a; }
        .m-del { background:#fde8e8; color:var(--accent); } .m-del:hover { background:#fecaca; }
        .m-ghost { background:#f3f4f6; color:#555; } .m-ghost:hover { background:#e5e7eb; }

        .empty { background:#fff; border-radius:16px; padding:60px 20px; text-align:center; color:#aaa; font-size:13px; }
        .empty i { font-size:38px; color:#e0d0d0; display:block; margin-bottom:12px; }

        @media (max-width:900px) { .main { margin-left:68px; } }
        @media (max-width:768px) { .main { margin-left:0; padding:16px; } .topbar { flex-direction:column; align-items:flex-start; } }
    </style>
</head>
<body>

@include('partials.faculty-sidebar', ['active' => 'quizzes'])

<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">Class Quizzes</div>
            <div class="page-sub">Build a quiz, set a deadline, publish it, and share the link with your students.</div>
        </div>
        <div class="topbar-right">
            @include('partials.topbar-actions')
            <a href="{{ route('faculty.quizzes.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Quiz</a>
        </div>
    </div>

    <div class="tabs">
        @foreach(['' => 'All', 'draft' => 'Drafts', 'published' => 'Published', 'closed' => 'Closed'] as $key => $label)
            <a href="{{ route('faculty.quizzes', $key ? ['status' => $key] : []) }}" class="tab {{ ($status ?? '') === $key ? 'active' : '' }}">
                {{ $label }} <span class="n">{{ $counts[$key ?: 'all'] }}</span>
            </a>
        @endforeach
    </div>

    @if($quizzes->isEmpty())
        <div class="empty">
            <i class="fas fa-clipboard-list"></i>
            @if($status)
                No {{ $status }} quizzes.
            @else
                You haven't created a quiz yet. Click <strong>New Quiz</strong> to build your first one.
            @endif
        </div>
    @else
        <div class="grid">
            @foreach($quizzes as $quiz)
                @php
                    $avail = $quiz->availability();
                    $pill = match(true) {
                        $quiz->status === 'draft' => ['sp-draft', 'fa-pen', 'Draft'],
                        $quiz->status === 'closed' => ['sp-closed', 'fa-lock', 'Closed'],
                        $avail === 'expired' => ['sp-expired', 'fa-hourglass-end', 'Deadline passed'],
                        $avail === 'upcoming' => ['sp-expired', 'fa-clock', 'Scheduled'],
                        default => ['sp-published', 'fa-circle-check', 'Published'],
                    };
                @endphp
                <div class="quiz-card">
                    <div class="qc-top">
                        <div>
                            <div class="qc-title">{{ $quiz->title }}</div>
                            <div class="qc-subject">{{ $quiz->subject?->code ?? 'No subject' }} · updated {{ $quiz->updated_at?->diffForHumans() }}</div>
                        </div>
                        <span class="status-pill {{ $pill[0] }}"><i class="fas {{ $pill[1] }}"></i> {{ $pill[2] }}</span>
                    </div>

                    <div class="qc-meta">
                        <span><i class="fas fa-list-ol"></i><b>{{ $quiz->items_count }}</b> question{{ $quiz->items_count === 1 ? '' : 's' }}</span>
                        <span><i class="fas fa-users"></i><b>{{ $quiz->submitted_count }}</b> submitted</span>
                        <span><i class="fas fa-calendar-day"></i>{{ $quiz->due_at ? 'Due <b>' . $quiz->due_at->format('M j, g:i A') . '</b>' : 'No deadline' }}</span>
                        @if($quiz->time_limit_minutes)
                            <span><i class="fas fa-stopwatch"></i><b>{{ $quiz->time_limit_minutes }}</b> min</span>
                        @endif
                    </div>

                    @if($quiz->status !== 'draft')
                        <div class="qc-link">
                            <i class="fas fa-link"></i>
                            <code>{{ $quiz->shareUrl() }}</code>
                            <button type="button" class="copy-btn" data-copy="{{ $quiz->shareUrl() }}"><i class="fas fa-copy"></i> Copy</button>
                        </div>
                    @endif

                    <div class="qc-actions">
                        <a href="{{ route('faculty.quizzes.edit', $quiz->id) }}" class="mini m-edit"><i class="fas fa-pen"></i> Edit</a>
                        <a href="{{ route('faculty.quizzes.results', $quiz->id) }}" class="mini m-res"><i class="fas fa-chart-simple"></i> Results</a>

                        @if($quiz->status === 'draft')
                            <form method="POST" action="{{ route('faculty.quizzes.publish', $quiz->id) }}"
                                  data-confirm="Students will be able to open and answer this quiz once it's published."
                                  data-confirm-title="Publish this quiz?" data-confirm-ok="Yes, publish" data-confirm-icon="question">
                                @csrf
                                <button class="mini m-pub"><i class="fas fa-paper-plane"></i> Publish</button>
                            </form>
                        @elseif($quiz->status === 'published')
                            <form method="POST" action="{{ route('faculty.quizzes.close', $quiz->id) }}"
                                  data-confirm="Students will no longer be able to start or submit this quiz."
                                  data-confirm-title="Close this quiz?" data-confirm-ok="Yes, close it">
                                @csrf
                                <button class="mini m-close"><i class="fas fa-lock"></i> Close</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('faculty.quizzes.publish', $quiz->id) }}"
                                  data-confirm="Students will be able to open and answer this quiz again."
                                  data-confirm-title="Re-open this quiz?" data-confirm-ok="Yes, re-open" data-confirm-icon="question">
                                @csrf
                                <button class="mini m-pub"><i class="fas fa-lock-open"></i> Re-open</button>
                            </form>
                            <form method="POST" action="{{ route('faculty.quizzes.reopen', $quiz->id) }}">
                                @csrf
                                <button class="mini m-ghost"><i class="fas fa-rotate-left"></i> To draft</button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('faculty.quizzes.destroy', $quiz->id) }}"
                              data-confirm="&quot;{{ $quiz->title }}&quot; and every student submission for it will be permanently deleted."
                              data-confirm-title="Delete this quiz?" data-confirm-ok="Yes, delete it" data-confirm-danger>
                            @csrf @method('DELETE')
                            <button class="mini m-del"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</main>

<script>
document.addEventListener('click', function (ev) {
    const btn = ev.target.closest('[data-copy]');
    if (!btn) return;
    const text = btn.getAttribute('data-copy');
    const done = () => {
        const old = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied';
        setTimeout(() => { btn.innerHTML = old; }, 1600);
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(done);
    } else {
        const ta = document.createElement('textarea');
        ta.value = text; document.body.appendChild(ta); ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta); done();
    }
});
</script>

@include('partials.alerts')
</body>
</html>
