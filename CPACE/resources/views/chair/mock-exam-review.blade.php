<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review {{ $exam->title }} - CPACE Program Chair</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.mock-exam-styles')
    <style>
        .split { display:grid; grid-template-columns:1fr 320px; gap:18px; align-items:start; }
        .item { padding:14px; border:1px solid var(--line); border-radius:10px; margin-bottom:10px; }
        .item-head { display:flex; gap:10px; align-items:flex-start; font-size:13px; }
        .item-n { width:26px; height:26px; border-radius:7px; background:var(--primary-light); color:var(--primary);
                  display:flex; align-items:center; justify-content:center; font-size:11.5px; font-weight:700; flex-shrink:0; }
        .choices { margin:10px 0 0 36px; display:grid; gap:5px; }
        .choice { font-size:12.5px; color:#555; padding:5px 9px; border-radius:6px; }
        .choice.correct { background:#dff3e8; color:#14683f; font-weight:600; }
        .diff { padding:1px 7px; border-radius:999px; font-weight:600; font-size:10.5px; }
        .diff-easy { background:#dff3e8; color:var(--green); }
        .diff-moderate { background:#fdf0d8; color:var(--amber); }
        .diff-difficult { background:#fdeceb; color:var(--red); }
        .audit-row { padding:10px 0; border-bottom:1px solid #f0f1f4; font-size:12.5px; }
        .audit-row:last-child { border-bottom:none; }
        .audit-who { font-weight:600; color:var(--ink); }
        .audit-when { font-size:11px; color:var(--muted); margin-top:2px; }
        .modal { position:fixed; inset:0; background:rgba(15,20,30,.55); display:none; align-items:center; justify-content:center; z-index:900; }
        .modal.on { display:flex; }
        .modal-box { background:#fff; border-radius:14px; padding:24px; width:min(520px, 92vw); }
        @media (max-width: 1000px) { .split { grid-template-columns:1fr; } }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'mock-exams'])

<main class="main">
    <div class="topbar">
        <div>
            <a href="{{ route('chair.mock-exams.subject', $exam->subject_id) }}" style="font-size:12px;color:var(--muted);text-decoration:none;">
                <i class="fas fa-arrow-left"></i> {{ $subject->code }} folder
            </a>
            <div class="page-title" style="margin-top:4px;">{{ $exam->title }}</div>
            <div class="page-sub">
                {{ $subject->name }} ·
                <span class="chip {{ ['draft'=>'chip-draft','for_review'=>'chip-review','published'=>'chip-published','closed'=>'chip-closed'][$exam->status] ?? 'chip-draft' }}">
                    {{ str_replace('_', ' ', $exam->status) }}
                </span>
                · started by {{ $exam->creator?->first_name }} {{ $exam->creator?->last_name }}
            </div>
        </div>
        <div class="topbar-right">@include('partials.topbar-actions')</div>
    </div>

    @if($readOnly)
        <div class="banner banner-warn">
            <i class="fas fa-lock"></i>
            <div>
                <strong>Published — locked.</strong>
                Neither you nor the faculty can change this paper any more. That's deliberate: students
                must all sit the identical exam.
                @if($exam->event)
                    Redeem code <strong>{{ $exam->event->access_code }}</strong>.
                @endif
            </div>
        </div>
    @endif

    @error('version')<div class="banner banner-warn"><i class="fas fa-users"></i><div>{{ $message }}</div></div>@enderror
    @error('scheduled_at')<div class="banner banner-danger"><i class="fas fa-triangle-exclamation"></i><div>{{ $message }}</div></div>@enderror
    @error('items_json')<div class="banner banner-danger"><i class="fas fa-triangle-exclamation"></i><div>{{ $message }}</div></div>@enderror
    @error('review_note')<div class="banner banner-danger"><i class="fas fa-triangle-exclamation"></i><div>{{ $message }}</div></div>@enderror

    <div class="split">
        <div>
            <form method="POST" action="{{ route('chair.mock-exams.update', $exam) }}" id="reviewForm">
                @csrf @method('PUT')
                <input type="hidden" name="version" value="{{ $exam->version }}">
                <input type="hidden" name="topic_mode" value="{{ $exam->topic_mode }}">
                <input type="hidden" name="question_mode" value="{{ $exam->question_mode }}">
                {{-- The Chair edits settings here; the item list is preserved
                     verbatim unless questions are removed below. --}}
                <input type="hidden" name="items_json" id="itemsJson"
                       value='@json($exam->items->map(fn ($i) => ["source_question_id" => $i->source_question_id]))'>
                @foreach($selectedTopics as $tid)
                    <input type="hidden" name="topic_ids[]" value="{{ $tid }}">
                @endforeach

                <div class="card">
                    <div class="card-title"><i class="fas fa-sliders"></i> Exam settings</div>
                    <div style="display:grid;grid-template-columns:1fr 220px 160px;gap:14px;margin-top:14px;">
                        <div class="field" style="margin:0;">
                            <label>Title</label>
                            <input type="text" name="title" value="{{ old('title', $exam->title) }}" @disabled($readOnly)>
                        </div>
                        <div class="field" style="margin:0;">
                            <label>Sitting</label>
                            <input type="datetime-local" name="scheduled_at" @disabled($readOnly)
                                   value="{{ old('scheduled_at', $exam->scheduled_at?->format('Y-m-d\TH:i')) }}">
                        </div>
                        <div class="field" style="margin:0;">
                            <label>Duration (min)</label>
                            <input type="number" name="duration_minutes" min="5" max="600" @disabled($readOnly)
                                   value="{{ old('duration_minutes', $exam->duration_minutes) }}">
                        </div>
                    </div>
                    <div style="margin-top:14px;font-size:12px;color:var(--muted);">
                        {{ $exam->items->count() }} questions ·
                        {{ $exam->topics->count() }} topics ·
                        built {{ $exam->topic_mode }} topics / {{ $exam->question_mode }} questions
                    </div>
                </div>

                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                        <div>
                            <div class="card-title"><i class="fas fa-list-ol"></i> Questions ({{ $exam->items->count() }})</div>
                            <div class="card-sub">Correct answers are highlighted. Remove anything unsuitable before publishing.</div>
                        </div>
                    </div>
                    <div style="margin-top:14px;">
                        @forelse($exam->items as $i => $item)
                            <div class="item" data-qid="{{ $item->source_question_id }}">
                                <div class="item-head">
                                    <span class="item-n">{{ $i + 1 }}</span>
                                    <div style="flex:1;">
                                        <div>{{ $item->question_text }}</div>
                                        <div style="margin-top:5px;display:flex;gap:8px;align-items:center;">
                                            @if($item->difficulty)
                                                <span class="diff diff-{{ $item->difficulty }}">{{ $item->difficulty }}</span>
                                            @endif
                                            <span style="font-size:11px;color:var(--muted);">{{ $item->topic?->name }}</span>
                                        </div>
                                    </div>
                                    @unless($readOnly)
                                        <button type="button" class="btn btn-ghost btn-sm remove-item" style="color:var(--red);">
                                            <i class="fas fa-xmark"></i>
                                        </button>
                                    @endunless
                                </div>
                                <div class="choices">
                                    @foreach((array) $item->choices as $choice)
                                        <div class="choice {{ !empty($choice['is_correct']) ? 'correct' : '' }}">
                                            <strong>{{ $choice['label'] ?? '' }}.</strong> {{ $choice['text'] ?? '' }}
                                            @if(!empty($choice['is_correct']))<i class="fas fa-check" style="margin-left:5px;"></i>@endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="empty"><i class="fas fa-inbox"></i><h3>No questions</h3><p>This exam cannot be published until the faculty adds questions.</p></div>
                        @endforelse
                    </div>
                </div>

                @unless($readOnly)
                    <div style="display:flex;gap:10px;margin-top:18px;flex-wrap:wrap;">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-floppy-disk"></i> Save changes</button>
                        <button class="btn btn-ghost" type="button" onclick="document.getElementById('returnModal').classList.add('on')">
                            <i class="fas fa-rotate-left"></i> Return for revision
                        </button>
                        <button class="btn btn-green" type="button" id="publishBtn"><i class="fas fa-bullhorn"></i> Publish</button>
                    </div>
                @endunless
            </form>

            @unless($readOnly)
                <form method="POST" action="{{ route('chair.mock-exams.publish', $exam) }}" id="publishForm" style="display:none;">@csrf</form>
            @endunless
            @if($exam->isPublished())
                <form method="POST" action="{{ route('chair.mock-exams.close', $exam) }}" style="margin-top:18px;"
                      onsubmit="return confirm('Close this exam? Students who have not started will no longer be able to.');">
                    @csrf
                    <button class="btn btn-ghost" type="submit"><i class="fas fa-circle-stop"></i> Close exam</button>
                </form>
            @endif
        </div>

        <div class="card">
            <div class="card-title"><i class="fas fa-clock-rotate-left"></i> Audit trail</div>
            <div class="card-sub">Who changed what, and when.</div>
            <div style="margin-top:12px;max-height:520px;overflow-y:auto;">
                @forelse($exam->audits as $entry)
                    <div class="audit-row">
                        <span class="audit-who">{{ $entry->user?->first_name ?? 'Someone' }} {{ $entry->user?->last_name }}</span>
                        {{ $entry->label() }}
                        @if($entry->details)
                            <div style="font-size:11.5px;color:var(--muted);margin-top:3px;">{{ $entry->details }}</div>
                        @endif
                        <div class="audit-when">{{ $entry->created_at?->format('M j, Y g:i A') }}</div>
                    </div>
                @empty
                    <div style="font-size:12.5px;color:var(--muted);">Nothing recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Return for revision --}}
    <div class="modal" id="returnModal">
        <div class="modal-box">
            <div class="card-title"><i class="fas fa-rotate-left"></i> Return for revision</div>
            <div class="card-sub">The faculty will see this note on their builder.</div>
            <form method="POST" action="{{ route('chair.mock-exams.return', $exam) }}" style="margin-top:14px;">
                @csrf
                <div class="field">
                    <label>What needs changing?</label>
                    <textarea name="review_note" rows="4" required placeholder="e.g. Questions 12 and 40 are duplicates; please widen topic coverage."></textarea>
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" class="btn btn-ghost" onclick="document.getElementById('returnModal').classList.remove('on')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send back</button>
                </div>
            </form>
        </div>
    </div>
</main>

@include('partials.alerts')
<script>
(function () {
    const itemsInput = document.getElementById('itemsJson');
    if (!itemsInput) return;

    // Removing an item drops it from the hidden payload; the actual delete
    // happens on save, so the Chair can back out by simply not saving.
    document.querySelectorAll('.remove-item').forEach(btn => btn.addEventListener('click', () => {
        const row = btn.closest('.item');
        const qid = Number(row.dataset.qid);
        const items = JSON.parse(itemsInput.value || '[]').filter(i => Number(i.source_question_id) !== qid);
        itemsInput.value = JSON.stringify(items);
        row.style.display = 'none';
    }));

    const publishBtn = document.getElementById('publishBtn');
    if (publishBtn) publishBtn.addEventListener('click', () => {
        if (!confirm('Publish this mock exam?\n\nThis generates the redeem code and LOCKS the paper — after this nobody, including you, can edit it.')) return;
        document.getElementById('publishForm').submit();
    });
})();
</script>
</body>
</html>
