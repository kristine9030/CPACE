{{--
    Question-by-question review of one student's sitting: what they picked,
    what was correct, and the explanation. Shared by the class quiz and mock
    exam per-student result pages. Both item models store choices inline as
    [{label, text, is_correct}] and answers as {item id: label}.

    Expects $items and $answers.
--}}
<style>
    .ar-item { padding:15px 0; border-bottom:1px solid #f0f1f4; }
    .ar-item:last-child { border-bottom:none; }
    .ar-q { font-size:13.5px; color:#222; line-height:1.55; margin-bottom:9px; font-weight:500; }
    .ar-q b { color:#7B1D1D; margin-right:6px; }
    .ar-tag { font-size:10.5px; font-weight:700; padding:2px 9px; border-radius:20px; margin-left:8px; white-space:nowrap; }
    .ar-tag.ok { background:#d1fae5; color:#059669; }
    .ar-tag.no { background:#fde8e8; color:#c0392b; }
    .ar-tag.skip { background:#eef0f4; color:#7a8296; }
    .ar-choice { display:flex; gap:9px; font-size:12.5px; padding:7px 11px; border-radius:8px; border:1px solid #eceef2; margin-top:5px; color:#444; }
    .ar-choice b { min-width:16px; }
    .ar-choice.right { background:#ecfdf5; border-color:#a7f3d0; color:#065f46; }
    .ar-choice.wrong { background:#fef2f2; border-color:#fecaca; color:#991b1b; }
    .ar-choice small { margin-left:auto; font-weight:700; white-space:nowrap; }
    .ar-expl { font-size:12px; color:#6b7280; background:#f8f9fb; border-radius:8px; padding:9px 12px; margin-top:8px; line-height:1.5; }
</style>
@foreach($items as $i => $item)
    @php
        $picked = $answers[(string) $item->id] ?? null;
        $correct = $item->correctLabel();
        $state = $picked === null || $picked === '' ? 'skip' : ($picked === $correct ? 'ok' : 'no');
    @endphp
    <div class="ar-item">
        <div class="ar-q">
            <b>Q{{ $i + 1 }}</b>{{ $item->question_text }}
            <span class="ar-tag {{ $state }}">{{ $state === 'ok' ? 'Correct' : ($state === 'no' ? 'Wrong' : 'Not answered') }}</span>
        </div>
        @foreach((array) $item->choices as $choice)
            @php $label = (string) ($choice['label'] ?? ''); @endphp
            <div class="ar-choice {{ $label === $correct ? 'right' : ($label === $picked ? 'wrong' : '') }}">
                <b>{{ $label }}.</b> <span>{{ $choice['text'] ?? '' }}</span>
                @if($label === $picked && $label === $correct)<small>Their answer ✓</small>
                @elseif($label === $picked)<small>Their answer</small>
                @elseif($label === $correct)<small>Correct answer</small>@endif
            </div>
        @endforeach
        @if(!empty($item->explanation))
            <div class="ar-expl"><i class="fas fa-lightbulb"></i> {{ $item->explanation }}</div>
        @endif
    </div>
@endforeach
