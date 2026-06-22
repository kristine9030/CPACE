<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Bank Export - CPACE</title>
    <style>
        @page { margin: 18mm 14mm; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI', Arial, sans-serif; color:#1a1a1a; font-size:12px; }

        .doc-head { border-bottom:2px solid #7B1D1D; padding-bottom:10px; margin-bottom:16px; }
        .doc-title { font-size:20px; font-weight:700; color:#7B1D1D; }
        .doc-sub { font-size:11px; color:#777; margin-top:3px; }
        .doc-filters { font-size:11px; color:#555; margin-top:6px; }
        .doc-filters span { display:inline-block; background:#f5e8e8; color:#7B1D1D; padding:2px 8px; border-radius:4px; margin-right:5px; font-weight:600; }

        .q-card { border:1px solid #e6e6e6; border-radius:6px; padding:12px 14px; margin-bottom:10px; page-break-inside:avoid; }
        .q-top { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; margin-bottom:6px; }
        .q-num { font-weight:700; color:#7B1D1D; }
        .q-tags { font-size:10px; }
        .tag { display:inline-block; padding:2px 7px; border-radius:4px; font-weight:600; margin-left:4px; }
        .t-subj { background:#dbeafe; color:#2563eb; }
        .t-type { background:#f3f4f6; color:#555; }
        .t-easy { background:#d1fae5; color:#059669; }
        .t-medium { background:#fef3c7; color:#d97706; }
        .t-hard { background:#fde8e8; color:#c0392b; }
        .t-draft { background:#f3f4f6; color:#9ca3af; }

        .q-text { font-weight:600; margin-bottom:6px; }
        .q-meta { font-size:10px; color:#999; margin-bottom:6px; }
        .choice { padding:3px 0 3px 14px; }
        .choice.correct { color:#059669; font-weight:600; }
        .choice.correct::before { content:'\2714  '; }
        .q-expl { margin-top:6px; font-size:11px; color:#555; font-style:italic; border-top:1px dashed #eee; padding-top:5px; }
        .q-expl b { font-style:normal; color:#333; }

        .empty { text-align:center; padding:40px; color:#999; }

        .print-bar { position:fixed; top:0; left:0; right:0; background:#7B1D1D; color:#fff; padding:10px 16px; display:flex; justify-content:space-between; align-items:center; font-size:13px; }
        .print-bar button { background:#fff; color:#7B1D1D; border:none; padding:7px 16px; border-radius:6px; font-weight:600; cursor:pointer; font-size:13px; margin-left:8px; }
        .print-bar button.ghost { background:transparent; color:#fff; border:1px solid rgba(255,255,255,.6); }
        .print-spacer { height:46px; }
        @media print { .print-bar, .print-spacer { display:none !important; } }
    </style>
</head>
<body>

<div class="print-bar">
    <span><strong>{{ $questions->count() }}</strong> question{{ $questions->count() === 1 ? '' : 's' }} ready to export</span>
    <span>
        <button class="ghost" onclick="window.close()">Close</button>
        <button onclick="window.print()">Save as PDF / Print</button>
    </span>
</div>
<div class="print-spacer"></div>

<div class="doc-head">
    <div class="doc-title">CPACE Test Bank Export</div>
    <div class="doc-sub">Generated {{ now()->format('F j, Y \a\t g:i A') }} &middot; {{ $questions->count() }} question{{ $questions->count() === 1 ? '' : 's' }}</div>
    @php
        $active = array_filter($filters ?? [], fn ($v) => $v !== null && $v !== '');
    @endphp
    @if(!empty($active))
        <div class="doc-filters">
            Filters:
            @foreach($active as $key => $val)
                <span>{{ ucfirst($key) }}: {{ $val }}</span>
            @endforeach
        </div>
    @endif
</div>

@forelse($questions as $i => $q)
    @php
        $diffLabel = ['easy' => 'Easy', 'moderate' => 'Medium', 'difficult' => 'Hard'][$q->difficulty] ?? $q->difficulty;
        $diffClass = ['easy' => 't-easy', 'moderate' => 't-medium', 'difficult' => 't-hard'][$q->difficulty] ?? 't-type';
    @endphp
    <div class="q-card">
        <div class="q-top">
            <span class="q-num">Q{{ $i + 1 }}</span>
            <span class="q-tags">
                <span class="tag t-subj">{{ $q->subject_code }}</span>
                <span class="tag t-type">{{ $q->question_type === 'mcq' ? 'MCQ' : 'True / False' }}</span>
                <span class="tag {{ $diffClass }}">{{ $diffLabel }}</span>
                @unless($q->is_active)<span class="tag t-draft">Draft</span>@endunless
            </span>
        </div>
        <div class="q-meta">{{ $q->topic_name }}</div>
        <div class="q-text">{{ $q->question_text }}</div>
        @foreach($q->choices->sortBy('choice_label') as $c)
            <div class="choice {{ $c->is_correct ? 'correct' : '' }}">{{ $c->choice_label }}. {{ $c->choice_text }}</div>
        @endforeach
        @if($q->explanation)
            <div class="q-expl"><b>Explanation:</b> {{ $q->explanation }}</div>
        @endif
    </div>
@empty
    <div class="empty">No questions match the current filters.</div>
@endforelse

</body>
</html>
