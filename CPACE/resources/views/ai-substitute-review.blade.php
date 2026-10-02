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

        /* Two cards side by side: topics still short (left), AI drafts to review (right). */
        .review-grid { display:grid; grid-template-columns:minmax(320px, 400px) minmax(0, 1fr); gap:18px; align-items:start; }
        .review-grid.solo { grid-template-columns:minmax(0, 1fr); }
        .rv-card { background:#fff; border-radius:14px; box-shadow:0 1px 2px rgba(16,24,40,.04), 0 4px 14px rgba(16,24,40,.06); overflow:hidden; }
        .rv-card + .rv-card { margin-top:18px; }
        .rv-head { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:16px 18px; border-bottom:1px solid #f3f3f3; }
        .rv-title { display:flex; align-items:center; gap:9px; font-size:14px; font-weight:700; color:#14283E; }
        .rv-title i { color:#7c3aed; }
        .rv-count { font-size:11px; font-weight:700; color:#7c3aed; background:#ede9fe; padding:2px 9px; border-radius:20px; }
        .rv-sub { font-size:11.5px; color:#999; padding:10px 18px 0; }

        /* left: topics short of their TOS count */
        .rv-left { position:sticky; top:16px; }
        .rv-filter { padding:12px 18px; border-bottom:1px solid #f3f3f3; }
        .rv-filter select { width:100%; font-family:'Poppins',sans-serif; font-size:12.5px; border:1px solid #e0e0e0; border-radius:8px; padding:8px 12px; color:#444; background:#fff; outline:none; }
        .rv-filter select:focus { border-color:var(--primary); }
        .rv-scroll { max-height:calc(100vh - 290px); min-height:180px; overflow-y:auto; }
        .short-subj { font-size:11px; font-weight:700; color:#7c3aed; background:#faf9ff; padding:8px 18px; border-bottom:1px solid #f3f3f3; letter-spacing:.3px; position:sticky; top:0; z-index:1; display:flex; justify-content:space-between; }
        .short-subj span { color:#a78bfa; font-weight:600; }
        .short-row { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:12px 18px; border-bottom:1px solid #f8f8f8; }
        .short-row:last-child { border-bottom:none; }
        .short-topic { font-size:12.5px; color:#333; min-width:0; }
        .short-topic strong { color:#14283E; }
        .short-meta { font-size:11px; color:#999; margin-top:2px; }
        .short-none { padding:28px 18px; text-align:center; color:#aaa; font-size:12.5px; }
        .short-none[hidden], .short-group[hidden] { display:none; }
        .btn-generate { background:#ede9fe; color:#7c3aed; flex-shrink:0; white-space:nowrap; }
        .btn-generate:hover { background:#ddd6fe; }

        /* right: AI drafts waiting for review */
        .rv-body { padding:6px 18px 18px; }
        .subj-head { font-size:13px; font-weight:700; color:#14283E; margin:16px 0 10px; display:flex; align-items:center; gap:8px; }
        .subj-head .count { font-size:11px; font-weight:600; color:#7c3aed; background:#ede9fe; padding:2px 9px; border-radius:20px; }
        .q-card { border:1px solid #f0f0f2; border-radius:12px; padding:16px 18px; margin-bottom:12px; background:#fff; }
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
        .empty { padding:46px 20px; text-align:center; color:#999; font-size:13px; }
        .empty i { font-size:28px; color:#10b981; display:block; margin-bottom:10px; }
        .recent table { width:100%; border-collapse:collapse; font-size:12px; }
        .recent td { padding:10px 18px; border-bottom:1px solid #f8f8f8; vertical-align:top; }
        .recent tr:last-child td { border-bottom:none; }
        .st-approved { color:#047857; font-weight:600; } .st-rejected { color:var(--accent); font-weight:600; }
        @media (max-width:1100px) {
            .review-grid { grid-template-columns:minmax(0, 1fr); }
            .rv-left { position:static; }
            .rv-scroll { max-height:340px; }
        }
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

    @php $hasShort = $shortlist->isNotEmpty(); @endphp
    <div class="review-grid {{ $hasShort ? '' : 'solo' }}">

        {{-- LEFT: topics that are still short of their TOS item count --}}
        @if($hasShort)
            <div class="rv-left">
                <section class="rv-card" aria-label="Topics short of their TOS item count">
                    <div class="rv-head">
                        <div class="rv-title"><i class="fas fa-hourglass-half"></i> Short of TOS count</div>
                        <span class="rv-count">{{ $shortlist->flatten(1)->count() }}</span>
                    </div>
                    <div class="rv-filter">
                        <select id="shortSubject" aria-label="Filter by subject">
                            <option value="">All subjects</option>
                            @foreach($shortlist as $subjectCode => $rows)
                                <option value="{{ $subjectCode }}">{{ $subjectCode }} ({{ count($rows) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="rv-scroll">
                        @foreach($shortlist as $subjectCode => $rows)
                            <div class="short-group" data-subject="{{ $subjectCode }}">
                                <div class="short-subj">{{ $subjectCode }} <span>{{ count($rows) }} {{ \Illuminate\Support\Str::plural('topic', count($rows)) }}</span></div>
                                @foreach($rows as $row)
                                    @php $topic = $row['topic']; @endphp
                                    <div class="short-row">
                                        <div class="short-topic">
                                            @if($topic->parent)<span style="color:#999;">{{ $topic->parent->name }} ›</span> @endif
                                            {{ $topic->name }}
                                            <div class="short-meta">
                                                {{ $row['shortBy'] }} question(s) short
                                                @if($row['graceEndsAt'])
                                                    · grace period ends {{ $row['graceEndsAt']->format('M j, Y') }}
                                                @else
                                                    · not yet flagged
                                                @endif
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route($reviewRoute . '.generate', $topic->id) }}"
                                              data-confirm="AI will draft {{ min($row['shortBy'], config('curriculum.gap_fill_max_per_topic', 5)) }} question(s) for this topic right now, without waiting out the grace period. They land pending review."
                                              data-confirm-title="Generate questions now?"
                                              data-confirm-ok="Yes, generate">
                                            @csrf
                                            <button class="btn btn-generate"><i class="fas fa-wand-magic-sparkles"></i> Generate now</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        @endif

        {{-- RIGHT: AI drafts waiting for review, then the history --}}
        <div class="rv-right">
            <section class="rv-card" aria-label="AI questions waiting for review">
                <div class="rv-head">
                    <div class="rv-title"><i class="fas fa-clipboard-check"></i> Waiting for your review</div>
                    <span class="rv-count">{{ $pending->flatten(1)->count() }}</span>
                </div>
                @forelse($pending as $subjectCode => $questions)
                    <div class="rv-body">
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
                                @include('partials.question-exhibit', ['item' => $q])
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
                    </div>
                @empty
                    <div class="empty">
                        <i class="fas fa-circle-check"></i>
                        No AI questions are waiting for review.
                    </div>
                @endforelse
            </section>

            @if($recent->isNotEmpty())
                <section class="rv-card recent" aria-label="Recently reviewed">
                    <div class="rv-head"><div class="rv-title"><i class="fas fa-clock-rotate-left"></i> Recently reviewed</div></div>
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
                </section>
            @endif
        </div>
    </div>

    <script>
        // Subject filter for the topics card.
        (function () {
            const sel = document.getElementById('shortSubject');
            if (!sel) return;
            sel.addEventListener('change', function () {
                document.querySelectorAll('.short-group').forEach(function (g) {
                    g.hidden = sel.value !== '' && g.dataset.subject !== sel.value;
                });
            });
        })();
    </script>

    @include('partials.alerts')
</main>
</body>
</html>
