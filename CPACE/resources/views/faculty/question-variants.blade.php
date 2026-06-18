<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Variants - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; --purple:#7c3aed; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }

        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:16px; }
        .topbar-left { display:flex; align-items:center; gap:12px; }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .page-title { font-size:24px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:white; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }
        .btn-purple { background:var(--purple); color:white; }
        .btn-purple:hover { background:#6d28d9; }

        .grid { display:grid; grid-template-columns:1fr 360px; gap:20px; align-items:start; }
        .card { background:white; border-radius:14px; padding:22px 24px; margin-bottom:20px; }
        .card-title { font-size:15px; font-weight:700; color:#1a1a1a; margin-bottom:4px; display:flex; align-items:center; gap:8px; }
        .card-sub { font-size:12px; color:#999; margin-bottom:16px; }

        /* original question reference */
        .orig-box { background:#f9fafb; border:1px solid #eee; border-left:3px solid var(--primary); border-radius:8px; padding:14px 16px; }
        .orig-meta { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px; }
        .badge { font-size:10px; font-weight:700; padding:3px 9px; border-radius:6px; background:#f3f4f6; color:#555; }
        .badge.subj { background:#f5e8e8; color:var(--primary); }
        .orig-text { font-size:14px; font-weight:500; color:#1a1a1a; line-height:1.5; }
        .orig-choices { margin-top:12px; display:flex; flex-direction:column; gap:6px; }
        .orig-choice { font-size:12.5px; color:#555; display:flex; gap:8px; align-items:center; }
        .orig-choice.correct { color:#059669; font-weight:600; }
        .orig-choice .ltr { width:20px; height:20px; border-radius:50%; background:#eee; display:inline-flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; flex-shrink:0; }
        .orig-choice.correct .ltr { background:#d1fae5; color:#059669; }

        /* variant list */
        .vlist { display:flex; flex-direction:column; gap:10px; }
        .vitem { border:1px solid #eee; border-radius:10px; padding:13px 15px; display:flex; gap:12px; align-items:flex-start; }
        .vitem.inactive { opacity:.55; }
        .vitem .vtext { flex:1; font-size:13.5px; color:#1a1a1a; line-height:1.5; }
        .vitem .vsrc { font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; padding:2px 7px; border-radius:5px; margin-top:6px; display:inline-block; }
        .vsrc.faculty { background:#ede9fe; color:#7c3aed; }
        .vsrc.ai { background:#dbeafe; color:#2563eb; }
        .vsrc.rule { background:#f3f4f6; color:#6b7280; }
        .vacts { display:flex; gap:6px; flex-shrink:0; }
        .icon-btn { width:30px; height:30px; border:none; border-radius:7px; cursor:pointer; font-size:12px; display:inline-flex; align-items:center; justify-content:center; transition:all .2s; }
        .ib-toggle { background:#f3f4f6; color:#555; }
        .ib-toggle:hover { background:#e5e7eb; }
        .ib-del { background:#fde8e8; color:var(--accent); }
        .ib-del:hover { background:#fecaca; }
        .empty { text-align:center; color:#aaa; font-size:13px; padding:26px 0; }

        /* add form */
        textarea { width:100%; min-height:90px; border:1.5px solid #e0e0e0; border-radius:10px; padding:12px 14px; font-family:'Poppins',sans-serif; font-size:13.5px; color:#333; resize:vertical; outline:none; line-height:1.5; }
        textarea:focus { border-color:var(--primary); }
        .form-row { display:flex; justify-content:space-between; align-items:center; margin-top:12px; gap:10px; }
        .hint { font-size:11px; color:#aaa; }

        /* suggestions panel */
        .sg-group { margin-bottom:18px; }
        .sg-label { font-size:11px; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:.04em; margin-bottom:9px; display:flex; align-items:center; gap:6px; }
        .chips { display:flex; flex-wrap:wrap; gap:7px; }
        .chip { font-size:12px; font-weight:500; padding:6px 11px; border-radius:20px; border:1px solid #e5e7eb; background:white; color:#444; cursor:pointer; transition:all .15s; white-space:nowrap; }
        .chip:hover { border-color:var(--purple); background:#f5f3ff; color:var(--purple); }
        .chip.syn { border-style:dashed; }
        .chip .arrow { color:#bbb; margin:0 3px; }
        .chip:hover .arrow { color:var(--purple); }
        .sg-empty { font-size:12px; color:#bbb; font-style:italic; }
        .info-note { background:#fffbeb; border-radius:8px; padding:11px 14px; font-size:12px; color:#92400e; line-height:1.5; margin-bottom:16px; }
        .info-note i { margin-right:5px; }

        /* keep the Writing Helper within the viewport and scroll its suggestions */
        .helper-card { display:flex; flex-direction:column; max-height:calc(100vh - 40px); }
        .helper-scroll { overflow-y:auto; padding-right:6px; margin-right:-6px; }
        .helper-scroll::-webkit-scrollbar { width:6px; }
        .helper-scroll::-webkit-scrollbar-thumb { background:#ddd; border-radius:3px; }
        .helper-scroll::-webkit-scrollbar-thumb:hover { background:#ccc; }
        .helper-scroll .sg-group:last-child { margin-bottom:0; }

        @media (max-width: 1050px) { .grid { grid-template-columns:1fr; } .helper-card { max-height:none; } }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            /* grid already collapses at 1050px; ensure no horizontal overflow */
            .grid { gap: 14px; }
            /* variant item: wrap actions below text on very narrow */
            .vitem { flex-wrap: wrap; gap: 8px; }
            .vacts { width: 100%; justify-content: flex-end; }
            /* form row in add-variant: stack */
            .form-row { flex-direction: column; align-items: stretch; gap: 10px; }
            .form-row > div { justify-content: flex-end; }
            /* topbar right */
            .topbar-right { flex-wrap: wrap; gap: 8px; }
            /* helper card: don't stick on mobile */
            .helper-card { position: static !important; top: auto !important; }
            /* chip wrapping */
            .chips { gap: 5px; }
        }

        @media (max-width: 480px) {
            .card { padding: 14px 16px; }
            .page-title { font-size: 20px; }
            .btn { padding: 8px 12px; font-size: 12px; }
            .orig-text { font-size: 13px; }
            .chip { font-size: 11px; padding: 5px 9px; }
        }
    </style>
</head>
<body>

@include('partials.faculty-sidebar', ['active' => 'test-bank'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Question Variants</div>
                <div class="page-sub">Add alternative wordings so students can&rsquo;t just memorise one phrasing.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('faculty.test-bank') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back to Test Bank</a>
            @include('partials.topbar-actions')
        </div>
    </div>

    @if(session('status'))
        <div style="background:#d1fae5;color:#059669;padding:12px 18px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:600;">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background:#fee2e2;color:#b91c1c;padding:12px 18px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:600;">
            <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
        </div>
    @endif

    @php
        $subjectCode = $question->topic->subject->code ?? '';
        $typeLabel = ['mcq' => 'Multiple Choice', 'true_false' => 'True / False'];
    @endphp

    <div class="grid">
        <!-- LEFT: original + variant list + add form -->
        <div>
            <div class="card">
                <div class="card-title"><i class="fas fa-lock" style="color:var(--primary);"></i> Original Question (never changed)</div>
                <div class="card-sub">This is the faculty&rsquo;s real question. Variants only change how it is shown &mdash; the correct answer stays the same.</div>
                <div class="orig-box">
                    <div class="orig-meta">
                        <span class="badge subj">{{ $subjectCode }}</span>
                        <span class="badge">{{ $question->topic->name ?? '' }}</span>
                        <span class="badge">{{ $typeLabel[$question->question_type] ?? $question->question_type }}</span>
                    </div>
                    <div class="orig-text" id="originalText">{{ $question->question_text }}</div>
                    <div class="orig-choices">
                        @foreach($question->choices as $c)
                            <div class="orig-choice {{ $c->is_correct ? 'correct' : '' }}">
                                <span class="ltr">{{ $c->choice_label }}</span>
                                <span>{{ $c->choice_text }}</span>
                                @if($c->is_correct)<i class="fas fa-check" style="font-size:10px;"></i>@endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-title"><i class="fas fa-shuffle" style="color:var(--purple);"></i> Variants ({{ $question->variants->count() }})</div>
                <div class="card-sub">Each active variant is one of the wordings a student might see for this question.</div>
                <div class="vlist">
                    @forelse($question->variants as $v)
                        <div class="vitem {{ $v->is_active ? '' : 'inactive' }}">
                            <div class="vtext">
                                {{ $v->variant_text }}
                                <span class="vsrc {{ $v->source }}">{{ $v->source }}{{ $v->is_active ? '' : ' · hidden' }}</span>
                            </div>
                            <div class="vacts">
                                <form method="POST" action="{{ route('faculty.question.variants.toggle', [$question->id, $v->id]) }}">
                                    @csrf
                                    <button class="icon-btn ib-toggle" title="{{ $v->is_active ? 'Hide from students' : 'Show to students' }}">
                                        <i class="fas {{ $v->is_active ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('faculty.question.variants.destroy', [$question->id, $v->id]) }}" onsubmit="return confirm('Delete this variant?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="icon-btn ib-del" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="empty">
                            <i class="fas fa-shuffle" style="font-size:22px;color:#ddd;display:block;margin-bottom:8px;"></i>
                            No variants yet. Add one below &mdash; until then students see an automatically reworded version.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-title"><i class="fas fa-plus" style="color:var(--green);"></i> Add a Variant</div>
                <div class="card-sub">Re-word the question while keeping the meaning and the same correct answer.</div>
                <form method="POST" action="{{ route('faculty.question.variants.store', $question->id) }}">
                    @csrf
                    <textarea name="variant_text" id="variantInput" placeholder="Type an alternative wording of the question here...">{{ old('variant_text') }}</textarea>
                    <div class="form-row">
                        <span class="hint"><i class="fas fa-lightbulb" style="color:#f59e0b;"></i> Tip: click the suggestions on the right to build your wording.</span>
                        <div style="display:flex;gap:8px;">
                            <button type="button" class="btn btn-purple" id="suggestBtn"><i class="fas fa-wand-magic-sparkles"></i> Suggest a draft</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save Variant</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- RIGHT: intelligent suggestions -->
        <div class="card helper-card" style="position:sticky;top:20px;">
            <div class="card-title"><i class="fas fa-lightbulb" style="color:#f59e0b;"></i> Writing Helper</div>
            <div class="card-sub">Click a word to drop it into your variant at the cursor.</div>

            <div class="info-note">
                <i class="fas fa-circle-info"></i> Keep technical terms (FIFO, VAT, NRV&hellip;) and any &ldquo;NOT&rdquo; exactly as they are &mdash; only change the wording around them.
            </div>

            <div class="helper-scroll">
                <div class="sg-group">
                    <div class="sg-label"><i class="fas fa-quote-left"></i> Question openers</div>
                    <div class="chips" id="openerChips"></div>
                </div>

                <div class="sg-group">
                    <div class="sg-label"><i class="fas fa-right-left"></i> Word swaps for this question</div>
                    <div class="chips" id="synChips"></div>
                    <div class="sg-empty" id="synEmpty" style="display:none;">No suggested swaps detected &mdash; try the general swaps below.</div>
                </div>

                <div class="sg-group">
                    <div class="sg-label"><i class="fas fa-list"></i> General swaps</div>
                    <div class="chips" id="generalChips"></div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    const VOCAB = @json($vocabulary);
    const ORIGINAL = @json($question->question_text);
    const SUGGEST_URL = @json(route('faculty.question.variants.suggest', $question->id));
    const CSRF = @json(csrf_token());
    const input = document.getElementById('variantInput');

    // Insert text at the textarea cursor (or replace the selection).
    function insertAtCursor(text) {
        input.focus();
        const start = input.selectionStart ?? input.value.length;
        const end = input.selectionEnd ?? input.value.length;
        const before = input.value.slice(0, start);
        const after = input.value.slice(end);
        // add a leading space if needed so words don't run together
        const sep = (before && !/\s$/.test(before)) ? ' ' : '';
        input.value = before + sep + text + after;
        const pos = (before + sep + text).length;
        input.setSelectionRange(pos, pos);
    }

    function chip(label, onClick, extraClass) {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'chip' + (extraClass ? ' ' + extraClass : '');
        b.innerHTML = label;
        b.addEventListener('click', onClick);
        return b;
    }

    // Openers
    const openerWrap = document.getElementById('openerChips');
    VOCAB.openers.forEach(op => {
        openerWrap.appendChild(chip(op, () => insertAtCursor(op + ' ')));
    });

    // Word swaps relevant to THIS question (the "intelligent" part)
    const synWrap = document.getElementById('synChips');
    const genWrap = document.getElementById('generalChips');
    const lowerOrig = ORIGINAL.toLowerCase();
    let relevant = 0;
    VOCAB.synonyms.forEach(s => {
        const label = `${s.from}<span class="arrow">&rarr;</span>${s.to}`;
        // a chip that inserts the replacement word
        const makeChip = () => chip(label, () => insertAtCursor(s.to), 'syn');
        if (lowerOrig.includes(s.from.toLowerCase())) {
            synWrap.appendChild(makeChip());
            relevant++;
        } else {
            genWrap.appendChild(makeChip());
        }
    });
    if (relevant === 0) document.getElementById('synEmpty').style.display = 'block';

    // "Suggest a draft" — asks the server for a rule-based rewrite to start from
    const suggestBtn = document.getElementById('suggestBtn');
    suggestBtn.addEventListener('click', async () => {
        const original = suggestBtn.innerHTML;
        suggestBtn.disabled = true;
        suggestBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Thinking...';
        try {
            const res = await fetch(SUGGEST_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const data = await res.json();
            input.value = data.draft || ORIGINAL;
            input.focus();
        } catch (e) {
            alert('Could not generate a draft. Please try again.');
        } finally {
            suggestBtn.disabled = false;
            suggestBtn.innerHTML = original;
        }
    });

</script>
</body>
</html>
