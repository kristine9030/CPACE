<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Import - CPACE Faculty</title>
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
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; gap:16px; flex-wrap:wrap; }
        .page-title { font-size:26px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .topbar-right { display:flex; gap:10px; }

        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:white; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }
        .btn-danger-outline { background:white; color:var(--accent); border:1px solid #f3caca; }
        .btn-danger-outline:hover { background:#fdf0f0; }

        .summary-bar { background:white; border-radius:12px; padding:14px 20px; margin-bottom:18px; display:flex; align-items:center; gap:18px; flex-wrap:wrap; font-size:13px; color:#666; }
        .summary-bar b { color:#1a1a1a; }
        .toolbar-actions { margin-left:auto; display:flex; gap:14px; font-size:12px; }
        .toolbar-actions a { color:var(--primary); cursor:pointer; text-decoration:none; font-weight:600; }

        .item-card { background:white; border-radius:14px; padding:20px 22px; margin-bottom:16px; border:1px solid #f0f0f0; }
        .item-card.excluded { opacity:.5; }
        .item-head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; gap:10px; }
        .item-num { font-size:12px; color:#aaa; font-weight:700; }
        .include-toggle { display:flex; align-items:center; gap:7px; font-size:12px; color:#555; font-weight:600; cursor:pointer; }
        .include-toggle input { width:16px; height:16px; accent-color: var(--green); cursor:pointer; }

        .badge { display:inline-flex; align-items:center; gap:5px; font-size:10px; font-weight:700; padding:3px 9px; border-radius:20px; margin-left:8px; }
        .badge-rule { background:#dbeafe; color:#2563eb; }
        .badge-ai   { background:#ede9fe; color:#7c3aed; }
        .badge-check { background:#fef3c7; color:#b45309; }

        .row2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px; }
        .row3 { display:grid; grid-template-columns:2fr 1fr 1fr; gap:14px; margin-bottom:14px; }
        @media (max-width:900px) { .row2, .row3 { grid-template-columns:1fr; } }

        label.small { display:block; font-size:11px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.3px; margin-bottom:6px; }
        textarea, select, input[type=text] {
            width:100%; font-family:'Poppins',sans-serif; font-size:13px; border:1px solid #e0e0e0; border-radius:8px;
            padding:9px 11px; color:#333; outline:none; resize:vertical;
        }
        textarea:focus, select:focus, input[type=text]:focus { border-color:var(--primary); }
        textarea.question-text { min-height:60px; }

        .choice-row { display:flex; align-items:center; gap:10px; margin-bottom:8px; }
        .choice-row input[type=radio] { width:16px; height:16px; accent-color:var(--green); flex-shrink:0; cursor:pointer; }
        .choice-label { width:20px; font-weight:700; color:#777; font-size:12px; flex-shrink:0; }
        .choice-row input[type=text] { flex:1; }

        .tf-row { display:flex; gap:20px; }
        .tf-opt { display:flex; align-items:center; gap:7px; font-size:13px; color:#444; cursor:pointer; }
        .tf-opt input { accent-color:var(--green); }

        .type-toggle { display:flex; gap:8px; }
        .type-btn { flex:1; text-align:center; padding:8px; border:1px solid #e0e0e0; border-radius:8px; font-size:12px; font-weight:600; color:#666; cursor:pointer; }
        .type-btn.active { background:var(--primary-light); color:var(--primary); border-color:var(--primary); }

        .empty-state { background:white; border-radius:14px; padding:60px 20px; text-align:center; color:#999; }
        .empty-state i { font-size:36px; color:#ddd; margin-bottom:12px; }

        .sticky-save { position:sticky; bottom:0; background:white; margin:0 -30px -26px; padding:16px 30px; border-top:1px solid #eee; display:flex; justify-content:flex-end; gap:10px; z-index:20; }

        @keyframes fadeUp { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
        .item-card { animation:fadeUp .3s ease both; }
    </style>
</head>
<body>

@include('partials.faculty-sidebar', ['active' => 'test-bank'])

<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">Review Import</div>
            <div class="page-sub">{{ $batch->original_filename }} — {{ $batch->subject->code ?? '' }}. Check each question, then save the ones that look right.</div>
        </div>
        <div class="topbar-right">
            <form action="{{ route('faculty.test-bank.import.destroy', $batch->id) }}" method="POST" onsubmit="return confirm('Discard this import? Nothing will be saved.');" style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger-outline"><i class="fas fa-trash"></i> Discard</button>
            </form>
            <a href="{{ route('faculty.test-bank') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back to Test Bank</a>
        </div>
    </div>

    @if ($batch->items->isEmpty())
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <div>No questions were staged for this import.</div>
        </div>
    @else
        <div class="summary-bar">
            <span><b>{{ $batch->items->count() }}</b> question{{ $batch->items->count() === 1 ? '' : 's' }} found</span>
            <span><b>{{ $batch->items->where('confidence', '<', 70)->count() }}</b> need a closer look</span>
            <div class="toolbar-actions">
                <a onclick="toggleAll(true); return false;">Select all</a>
                <a onclick="toggleAll(false); return false;">Deselect all</a>
            </div>
        </div>

        <form action="{{ route('faculty.test-bank.import.commit', $batch->id) }}" method="POST" id="commitForm">
            @csrf

            @foreach ($batch->items as $i => $item)
                @php
                    $correct = $item->correctLabel();
                    $tfCorrect = $item->question_type === 'true_false' ? ($correct === 'A' ? 'true' : ($correct === 'B' ? 'false' : null)) : null;
                    $needsCheck = $item->confidence < 70;
                @endphp
                <div class="item-card" data-item="{{ $item->id }}">
                    <div class="item-head">
                        <div>
                            <span class="item-num">#{{ $i + 1 }}</span>
                            <span class="badge {{ $item->source === 'ai' ? 'badge-ai' : 'badge-rule' }}">
                                <i class="fas {{ $item->source === 'ai' ? 'fa-wand-magic-sparkles' : 'fa-list-check' }}"></i>
                                {{ $item->source === 'ai' ? 'AI-read' : 'Pattern-matched' }}
                            </span>
                            @if ($needsCheck)
                                <span class="badge badge-check"><i class="fas fa-triangle-exclamation"></i> Check this one</span>
                            @endif
                        </div>
                        <label class="include-toggle">
                            <input type="checkbox" name="items[{{ $item->id }}][include]" value="1" checked>
                            Include
                        </label>
                    </div>

                    <div class="row3">
                        <div>
                            <label class="small">Topic</label>
                            <select name="items[{{ $item->id }}][topic_id]" required>
                                <option value="">Choose a topic...</option>
                                @foreach ($topics as $topic)
                                    <option value="{{ $topic->id }}" {{ $item->topic_id === $topic->id ? 'selected' : '' }}>{{ $topic->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="small">Type</label>
                            <input type="hidden" name="items[{{ $item->id }}][question_type]" value="{{ $item->question_type }}" class="type-input">
                            <div class="type-toggle">
                                <div class="type-btn {{ $item->question_type === 'mcq' ? 'active' : '' }}" onclick="setType({{ $item->id }}, 'mcq')">Multiple Choice</div>
                                <div class="type-btn {{ $item->question_type === 'true_false' ? 'active' : '' }}" onclick="setType({{ $item->id }}, 'true_false')">True / False</div>
                            </div>
                        </div>
                        <div>
                            <label class="small">Difficulty</label>
                            @php
                                $diffMap = ['easy' => 'Easy', 'moderate' => 'Medium', 'difficult' => 'Hard'];
                                $diffLabel = $diffMap[$item->difficulty] ?? 'Medium';
                            @endphp
                            <select name="items[{{ $item->id }}][difficulty]">
                                <option value="Easy" {{ $diffLabel === 'Easy' ? 'selected' : '' }}>Easy</option>
                                <option value="Medium" {{ $diffLabel === 'Medium' ? 'selected' : '' }}>Medium</option>
                                <option value="Hard" {{ $diffLabel === 'Hard' ? 'selected' : '' }}>Hard</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom:14px;">
                        <label class="small">Question</label>
                        <textarea class="question-text" name="items[{{ $item->id }}][question_text]" required>{{ $item->question_text }}</textarea>
                    </div>

                    <div class="mcq-block" style="display:{{ $item->question_type === 'mcq' ? 'block' : 'none' }};">
                        <label class="small">Choices — mark the correct one</label>
                        @foreach (['A', 'B', 'C', 'D'] as $label)
                            @php $choiceText = collect($item->choices ?? [])->firstWhere('label', $label)['text'] ?? ''; @endphp
                            <div class="choice-row">
                                <input type="radio" name="items[{{ $item->id }}][correct_label]" value="{{ $label }}" {{ $correct === $label ? 'checked' : '' }}>
                                <span class="choice-label">{{ $label }}.</span>
                                <input type="text" name="items[{{ $item->id }}][choices][{{ $label }}]" value="{{ $choiceText }}" placeholder="Choice {{ $label }} {{ in_array($label, ['C', 'D']) ? '(optional)' : '' }}">
                            </div>
                        @endforeach
                    </div>

                    <div class="tf-block" style="display:{{ $item->question_type === 'true_false' ? 'block' : 'none' }}; margin-bottom:14px;">
                        <label class="small">Correct answer</label>
                        <div class="tf-row">
                            <label class="tf-opt"><input type="radio" name="items[{{ $item->id }}][tf_answer]" value="true" {{ $tfCorrect === 'true' ? 'checked' : '' }}> True</label>
                            <label class="tf-opt"><input type="radio" name="items[{{ $item->id }}][tf_answer]" value="false" {{ $tfCorrect === 'false' ? 'checked' : '' }}> False</label>
                        </div>
                    </div>

                    <div>
                        <label class="small">Explanation (optional)</label>
                        <textarea name="items[{{ $item->id }}][explanation]" style="min-height:44px;">{{ $item->explanation }}</textarea>
                    </div>
                </div>
            @endforeach

            <div class="sticky-save">
                <a href="{{ route('faculty.test-bank') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save Selected to Test Bank</button>
            </div>
        </form>
    @endif

    @include('partials.alerts')
</main>

<script>
    function toggleAll(state) {
        document.querySelectorAll('.include-toggle input[type=checkbox]').forEach(cb => {
            cb.checked = state;
            cb.closest('.item-card').classList.toggle('excluded', !state);
        });
    }

    document.querySelectorAll('.include-toggle input[type=checkbox]').forEach(cb => {
        cb.addEventListener('change', () => cb.closest('.item-card').classList.toggle('excluded', !cb.checked));
    });

    function setType(itemId, type) {
        const card = document.querySelector(`.item-card[data-item="${itemId}"]`);
        card.querySelector('.type-input').value = type;
        card.querySelectorAll('.type-btn').forEach(btn => btn.classList.remove('active'));
        card.querySelector(type === 'mcq' ? '.type-btn:first-child' : '.type-btn:last-child').classList.add('active');
        card.querySelector('.mcq-block').style.display = type === 'mcq' ? 'block' : 'none';
        card.querySelector('.tf-block').style.display = type === 'true_false' ? 'block' : 'none';
    }
</script>

</body>
</html>
