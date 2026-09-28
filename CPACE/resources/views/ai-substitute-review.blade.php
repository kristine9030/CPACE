<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Substitute Review - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; gap:16px; position:relative; z-index:100; }
        .page-title { font-size:26px; font-weight:700; color:#14283E; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:8px 14px; border-radius:8px; font-size:12px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-approve { background:#d1fae5; color:#047857; }
        .btn-approve:hover { background:#a7f3d0; }
        .btn-reject { background:#fde8e8; color:var(--accent); }
        .btn-reject:hover { background:#fecaca; }
        .btn-ghost { background:#fff; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }

        .subj-head { font-size:13px; font-weight:700; color:#14283E; margin:22px 0 10px; display:flex; align-items:center; gap:8px; }
        .subj-head .count { font-size:11px; font-weight:600; color:#7c3aed; background:#ede9fe; padding:2px 9px; border-radius:20px; }
        .q-card { background:#fff; border-radius:12px; padding:16px 18px; margin-bottom:12px; box-shadow:0 1px 4px rgba(15,10,10,.06); }
        .q-top { display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:8px; }
        .q-topic { font-size:11px; color:#888; }
        .q-topic strong { color:#555; }
        .pill { display:inline-block; padding:2px 9px; border-radius:5px; font-size:10px; font-weight:600; margin-left:4px; }
        .pill-ai { background:#ede9fe; color:#7c3aed; }
        .d-easy { background:#d1fae5; color:#059669; } .d-moderate { background:#fef3c7; color:#d97706; } .d-difficult { background:#fde8e8; color:var(--accent); }
        .q-text { font-size:13.5px; font-weight:600; color:#1a1a1a; line-height:1.55; margin-bottom:10px; white-space:pre-line; }
        .choices { list-style:none; display:grid; gap:5px; margin-bottom:10px; }
        .choices li { font-size:12.5px; padding:6px 10px; border-radius:7px; background:#fafafa; border:1px solid #f0f0f0; }
        .choices li.correct { background:#ecfdf5; border-color:#a7f3d0; color:#065f46; font-weight:600; }
        .choices li .lbl { font-weight:700; margin-right:6px; }
        .expl { font-size:12px; color:#666; background:#f9fafb; border-radius:8px; padding:9px 12px; margin-bottom:12px; line-height:1.55; }
        .expl strong { color:#444; }
        .q-actions { display:flex; gap:8px; flex-wrap:wrap; }
        .empty { background:#fff; border-radius:12px; padding:40px; text-align:center; color:#999; font-size:13px; }
        .empty i { font-size:28px; color:#10b981; display:block; margin-bottom:10px; }
        .recent { background:#fff; border-radius:12px; overflow:hidden; margin-top:26px; }
        .recent h3 { font-size:13px; font-weight:700; color:#14283E; padding:14px 18px; border-bottom:1px solid #f3f3f3; }
        .recent table { width:100%; border-collapse:collapse; font-size:12px; }
        .recent td { padding:10px 18px; border-bottom:1px solid #f8f8f8; vertical-align:top; }
        .recent tr:last-child td { border-bottom:none; }
        .st-approved { color:#047857; font-weight:600; } .st-rejected { color:var(--accent); font-weight:600; }
        @media (max-width:768px) {
            .main { margin-left:0 !important; padding:16px !important; }
            .topbar { flex-wrap:wrap; }
            .recent { overflow-x:auto; }
        }
    </style>
</head>
<body>

@if($isChair)
    @include('partials.chair-sidebar', ['active' => 'ai-review'])
@else
    @include('partials.faculty-sidebar', ['active' => 'test-bank'])
@endif

<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">AI Substitute Review</div>
            <div class="page-sub">Questions AI drafted for topics that stayed short of their TOS item count.</div>
        </div>
        <div class="topbar-right">
            @unless($isChair)
                <a href="{{ route('faculty.test-bank') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Test Bank</a>
            @endunless
            @include('partials.topbar-actions')
        </div>
    </div>

    <x-hint tone="ai" icon="fa-wand-magic-sparkles">
        <x-slot:title>Hidden from students until you <strong>approve</strong> them.</x-slot:title>
        AI drafts the missing items when a topic stays below its TOS item count for
        {{ config('curriculum.gap_fill_grace_days') }} days. Check each one against current standards before
        approving{{ $isChair ? '' : ', or edit it in the Test Bank and publish it yourself' }}.
    </x-hint>

    @php
        $diffLabel = ['easy' => 'Easy', 'moderate' => 'Medium', 'difficult' => 'Hard'];
        $reviewRoute = $isChair ? 'chair.ai-review' : 'faculty.test-bank.ai-review';
    @endphp

    @forelse($pending as $subjectCode => $questions)
        <div class="subj-head">{{ $subjectCode }} <span class="count">{{ $questions->count() }} pending</span></div>

        @foreach($questions as $q)
            <div class="q-card">
                <div class="q-top">
                    <div class="q-topic">
                        @if($q->topic->parent)<strong>{{ $q->topic->parent->name }}</strong> › @endif{{ $q->topic->name }}
                    </div>
                    <div>
                        <span class="pill pill-ai"><i class="fas fa-robot"></i> AI substitute</span>
                        <span class="pill d-{{ $q->difficulty }}">{{ $diffLabel[$q->difficulty] ?? $q->difficulty }}</span>
                    </div>
                </div>
                <div class="q-text">{{ $q->question_text }}</div>
                <ul class="choices">
                    @foreach($q->choices as $choice)
                        <li class="{{ $choice->is_correct ? 'correct' : '' }}">
                            <span class="lbl">{{ $choice->choice_label }}.</span>{{ $choice->choice_text }}
                            @if($choice->is_correct) <i class="fas fa-check" style="margin-left:4px;"></i>@endif
                        </li>
                    @endforeach
                </ul>
                @if($q->explanation)
                    <div class="expl"><strong>Explanation:</strong> {{ $q->explanation }}</div>
                @endif
                <div class="q-actions">
                    <form method="POST" action="{{ route($reviewRoute . '.approve', $q->id) }}">
                        @csrf
                        <button class="btn btn-approve"><i class="fas fa-check"></i> Approve</button>
                    </form>
                    <form method="POST" action="{{ route($reviewRoute . '.reject', $q->id) }}"
                          data-confirm="The question stays hidden from students and a new one may be drafted on the next run if the topic is still short."
                          data-confirm-title="Reject this AI question?"
                          data-confirm-ok="Yes, reject it"
                          data-confirm-danger>
                        @csrf
                        <button class="btn btn-reject"><i class="fas fa-xmark"></i> Reject</button>
                    </form>
                    @unless($isChair)
                        <a href="{{ route('faculty.question.edit', $q->id) }}" class="btn btn-ghost"><i class="fas fa-pen"></i> Edit first</a>
                    @endunless
                </div>
            </div>
        @endforeach
    @empty
        <div class="empty">
            <i class="fas fa-circle-check"></i>
            No AI questions are waiting for review.
        </div>
    @endforelse

    @if($recent->isNotEmpty())
        <div class="recent">
            <h3>Recently reviewed</h3>
            <table>
                @foreach($recent as $q)
                    <tr>
                        <td style="width:70px;">{{ $q->topic->subject->code ?? '' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($q->question_text, 110) }}<div style="font-size:11px;color:#999;">{{ $q->topic->name }}</div></td>
                        <td style="white-space:nowrap;">
                            <span class="st-{{ $q->review_status }}">{{ ucfirst($q->review_status) }}</span>
                            <div style="font-size:11px;color:#999;">{{ $q->reviewer->name ?? '' }} · {{ $q->reviewed_at?->format('M j, Y') }}</div>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    @include('partials.alerts')
</main>
</body>
</html>
