<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Build {{ $exam->title }} - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.mock-exam-styles')
    <style>
        .steps { display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap; }
        .step-tab { display:flex; align-items:center; gap:9px; padding:11px 18px; background:#fff; border:1px solid var(--line);
                    border-radius:11px; cursor:pointer; font-size:13px; font-weight:600; color:var(--muted); }
        .step-tab.on { border-color:var(--primary); color:var(--primary); box-shadow:0 2px 8px -3px rgba(123,29,29,.35); }
        .step-tab .n { width:22px; height:22px; border-radius:50%; background:#eef0f4; color:#5b6377;
                       display:flex; align-items:center; justify-content:center; font-size:11.5px; }
        .step-tab.on .n { background:var(--primary); color:#fff; }
        .step-tab.done .n { background:var(--green); color:#fff; }
        .step-panel { display:none; }
        .step-panel.on { display:block; }

        .topic-list { max-height:430px; overflow-y:auto; border:1px solid var(--line); border-radius:10px; padding:6px; }
        .topic-row { display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:7px; cursor:pointer; font-size:13px; }
        .topic-row:hover { background:#f7f8fa; }
        .topic-name { flex:1; }
        .topic-bank { font-size:11px; color:var(--muted); background:#f1f2f5; padding:2px 8px; border-radius:999px; }
        .topic-bank.zero { color:var(--red); background:#fdeceb; }

        .q-list { max-height:480px; overflow-y:auto; border:1px solid var(--line); border-radius:10px; }
        .q-row { display:flex; gap:11px; padding:12px 14px; border-bottom:1px solid #f0f1f4; font-size:13px; align-items:flex-start; }
        .q-row:last-child { border-bottom:none; }
        .q-row .q-text { flex:1; line-height:1.5; }
        .q-meta { font-size:11px; color:var(--muted); margin-top:4px; display:flex; gap:9px; flex-wrap:wrap; }
        .diff { padding:1px 7px; border-radius:999px; font-weight:600; font-size:10.5px; }
        .diff-easy { background:#dff3e8; color:var(--green); }
        .diff-moderate { background:#fdf0d8; color:var(--amber); }
        .diff-difficult { background:#fdeceb; color:var(--red); }

        .counter { font-size:13px; font-weight:700; color:var(--ink); }
        .counter.over { color:var(--red); }
        .split { display:grid; grid-template-columns:1fr 320px; gap:18px; align-items:start; }
        .audit { max-height:420px; overflow-y:auto; }
        .audit-row { padding:10px 0; border-bottom:1px solid #f0f1f4; font-size:12.5px; }
        .audit-row:last-child { border-bottom:none; }
        .audit-who { font-weight:600; color:var(--ink); }
        .audit-when { font-size:11px; color:var(--muted); margin-top:2px; }
        @media (max-width: 1000px) { .split { grid-template-columns:1fr; } }
    </style>
</head>
<body>
@include('partials.faculty-sidebar', ['active' => 'mock-exams'])

<main class="main">
    <div class="topbar">
        <div>
            <a href="{{ route('faculty.mock-exams.subject', $exam->subject_id) }}" style="font-size:12px;color:var(--muted);text-decoration:none;">
                <i class="fas fa-arrow-left"></i> {{ $subject->code }} mock exams
            </a>
            <div class="page-title" style="margin-top:4px;">{{ $exam->title }}</div>
            <div class="page-sub">
                {{ $subject->name }} ·
                <span class="chip {{ ['draft'=>'chip-draft','for_review'=>'chip-review','published'=>'chip-published','closed'=>'chip-closed'][$exam->status] ?? 'chip-draft' }}">
                    {{ str_replace('_', ' ', $exam->status) }}
                </span>
            </div>
        </div>
        <div class="topbar-right">@include('partials.topbar-actions')</div>
    </div>

    @if($readOnly)
        <div class="banner banner-warn">
            <i class="fas fa-lock"></i>
            <div>
                <strong>This exam is locked.</strong>
                It has been published by the Program Chair, so its questions and schedule can no longer change —
                that's what guarantees every student sits the same paper.
            </div>
        </div>
    @endif

    @if($exam->review_note)
        <div class="banner banner-danger">
            <i class="fas fa-rotate-left"></i>
            <div><strong>Returned by the Program Chair:</strong> {{ $exam->review_note }}</div>
        </div>
    @endif

    @error('version')<div class="banner banner-warn"><i class="fas fa-users"></i><div>{{ $message }}</div></div>@enderror
    @error('items_json')<div class="banner banner-danger"><i class="fas fa-triangle-exclamation"></i><div>{{ $message }}</div></div>@enderror
    @error('scheduled_at')<div class="banner banner-danger"><i class="fas fa-triangle-exclamation"></i><div>{{ $message }}</div></div>@enderror

    <div class="steps">
        <div class="step-tab on" data-step="1"><span class="n">1</span> Subject</div>
        <div class="step-tab" data-step="2"><span class="n">2</span> Topics</div>
        <div class="step-tab" data-step="3"><span class="n">3</span> Questions</div>
        <div class="step-tab" data-step="4"><span class="n">4</span> Schedule</div>
    </div>

    <form method="POST" action="{{ route('faculty.mock-exams.update', $exam) }}" id="buildForm">
        @csrf @method('PUT')
        {{-- Optimistic lock: if a co-assigned colleague saved while this page
             was open, the server rejects rather than clobbering their work. --}}
        <input type="hidden" name="version" value="{{ $exam->version }}">
        <input type="hidden" name="items_json" id="itemsJson">
        <input type="hidden" name="topic_mode" id="topicMode" value="{{ $exam->topic_mode }}">
        <input type="hidden" name="question_mode" id="questionMode" value="{{ $exam->question_mode }}">

        <div class="split">
            <div>
                {{-- Step 1 --}}
                <div class="step-panel on" data-panel="1">
                    <div class="card">
                        <div class="card-title"><i class="fas fa-book"></i> Subject</div>
                        <div class="card-sub">A mock exam covers one subject only. This is fixed once the draft exists.</div>
                        <div style="margin-top:16px;display:flex;align-items:center;gap:14px;">
                            <div class="sc-icon" style="margin:0;--sc-base:{{ $theme['base'] }};color:{{ $theme['base'] }};border-color:{{ $theme['pastel'] }};">
                                <i class="fas {{ $icon }}"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;color:var(--ink);">{{ $subject->code }}</div>
                                <div style="font-size:12px;color:var(--muted);">{{ $subject->name }}</div>
                            </div>
                        </div>
                        <div class="field" style="margin-top:18px;">
                            <label>Exam title</label>
                            <input type="text" name="title" value="{{ old('title', $exam->title) }}" @disabled($readOnly)>
                            @error('title')<div class="err">{{ $message }}</div>@enderror
                        </div>
                        <div style="font-size:12px;color:var(--muted);">
                            Test bank for this subject:
                            <strong>{{ $bankCounts['total'] }}</strong> active questions
                            ({{ $bankCounts['easy'] }} easy · {{ $bankCounts['moderate'] }} moderate · {{ $bankCounts['difficult'] }} difficult)
                        </div>
                    </div>
                </div>

                {{-- Step 2 --}}
                <div class="step-panel" data-panel="2">
                    <div class="card">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
                            <div>
                                <div class="card-title"><i class="fas fa-sitemap"></i> Topics</div>
                                <div class="card-sub">Choose which parts of the syllabus this exam covers.</div>
                            </div>
                            <div class="modes" id="topicModes">
                                <button type="button" data-mode="manual">Manual</button>
                                <button type="button" data-mode="auto">Auto</button>
                                <button type="button" data-mode="all">All topics</button>
                            </div>
                        </div>
                        <div style="margin:14px 0 10px;font-size:12px;color:var(--muted);">
                            <span id="topicCount">0</span> selected ·
                            <span id="topicPool">0</span> questions available in the bank for them
                        </div>
                        <div class="topic-list" id="topicList">
                            @forelse($topicTree as $node)
                                @include('faculty.partials.mock-topic-node', [
                                    'node' => $node, 'selectedTopics' => $selectedTopics, 'depth' => 0, 'readOnly' => $readOnly,
                                ])
                            @empty
                                <div class="empty" style="padding:30px;">
                                    <i class="fas fa-folder-open"></i>
                                    <h3>No topics yet</h3>
                                    <p>The Program Chair adds topics under Subjects.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Step 3 --}}
                <div class="step-panel" data-panel="3">
                    <div class="card">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
                            <div>
                                <div class="card-title"><i class="fas fa-list-check"></i> Questions</div>
                                <div class="card-sub">Maximum {{ \App\Models\MockExam::MAX_ITEMS }} questions. Fewer is fine.</div>
                            </div>
                            <div class="modes" id="questionModes">
                                <button type="button" data-mode="manual">Manual</button>
                                <button type="button" data-mode="auto">Auto pick</button>
                                <button type="button" data-mode="all">All from topics</button>
                            </div>
                        </div>

                        <div id="autoControls" style="display:none;margin-top:14px;gap:10px;align-items:flex-end;">
                            <div class="field" style="margin:0;max-width:150px;">
                                <label>How many questions?</label>
                                <input type="number" id="autoCount" min="1" max="{{ \App\Models\MockExam::MAX_ITEMS }}" value="70">
                            </div>
                            <button type="button" class="btn btn-primary" id="autoPickBtn" @disabled($readOnly)>
                                <i class="fas fa-wand-magic-sparkles"></i> Generate
                            </button>
                            <div class="field-hint" style="margin:0 0 10px;">
                                Spreads evenly across your selected topics, aiming for ~30% easy / 50% moderate / 20% difficult.
                            </div>
                        </div>

                        <div style="display:flex;justify-content:space-between;align-items:center;margin:14px 0 10px;">
                            <div class="counter" id="itemCounter">0 / {{ \App\Models\MockExam::MAX_ITEMS }} selected</div>
                            @unless($readOnly)
                                <button type="button" class="btn btn-ghost btn-sm" id="clearItems"><i class="fas fa-xmark"></i> Clear all</button>
                            @endunless
                        </div>

                        <div class="q-list" id="qList">
                            <div class="empty" style="padding:34px;">
                                <i class="fas fa-inbox"></i>
                                <h3>Pick your topics first</h3>
                                <p>Go back to step 2, choose topics, then come back here.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 4 --}}
                <div class="step-panel" data-panel="4">
                    <div class="card">
                        <div class="card-title"><i class="fas fa-calendar-day"></i> Schedule</div>
                        <div class="card-sub">When students sit this exam, and how long they get.</div>
                        <div style="display:grid;grid-template-columns:1fr 200px;gap:16px;margin-top:16px;">
                            <div class="field">
                                <label>Exam date and start time</label>
                                <input type="datetime-local" name="scheduled_at" @disabled($readOnly)
                                       value="{{ old('scheduled_at', $exam->scheduled_at?->format('Y-m-d\TH:i')) }}">
                                <div class="field-hint">Students can only enter during this window.</div>
                            </div>
                            <div class="field">
                                <label>Duration (minutes)</label>
                                <input type="number" name="duration_minutes" min="5" max="600" @disabled($readOnly)
                                       value="{{ old('duration_minutes', $exam->duration_minutes) }}">
                                <div class="field-hint">Real CPALE allots 180.</div>
                            </div>
                        </div>
                        @error('duration_minutes')<div class="err">{{ $message }}</div>@enderror

                        <div class="banner banner-info" style="margin:16px 0 0;">
                            <i class="fas fa-shield-halved"></i>
                            <div>
                                Students must allow <strong>camera and screen sharing</strong> to enter. Alt-tabbing,
                                leaving fullscreen or turning either off during the exam is flagged for you and the
                                Program Chair.
                            </div>
                        </div>
                    </div>
                </div>

                @unless($readOnly)
                    <div style="display:flex;gap:10px;margin-top:18px;flex-wrap:wrap;">
                        <button class="btn btn-primary" type="submit" id="saveBtn"><i class="fas fa-floppy-disk"></i> Save draft</button>
                        <button class="btn btn-green" type="button" id="submitReviewBtn"><i class="fas fa-paper-plane"></i> Submit for review</button>
                    </div>
                @endunless
            </div>

            {{-- Audit trail: who changed what, for both collaborating faculty and the Chair. --}}
            <div class="card">
                <div class="card-title"><i class="fas fa-clock-rotate-left"></i> Activity</div>
                <div class="card-sub">Every change to this exam is recorded.</div>
                <div class="audit" style="margin-top:12px;">
                    @forelse($exam->audits as $entry)
                        <div class="audit-row">
                            <span class="audit-who">{{ $entry->user?->first_name ?? 'Someone' }} {{ $entry->user?->last_name }}</span>
                            {{ $entry->label() }}
                            @if($entry->details)
                                <div style="font-size:11.5px;color:var(--muted);margin-top:3px;">{{ $entry->details }}</div>
                            @endif
                            <div class="audit-when">{{ $entry->created_at?->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div style="font-size:12.5px;color:var(--muted);">Nothing recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </form>

    @unless($readOnly)
        <form method="POST" action="{{ route('faculty.mock-exams.submit', $exam) }}" id="reviewForm" style="display:none;">@csrf</form>
    @endunless
</main>

@include('partials.alerts')
@php
    // Built here rather than inline in @json below: a multi-line array literal
    // inside a Blade directive's argument trips its bracket matcher.
    $existingItems = $exam->items
        ->filter(fn ($i) => $i->source_question_id)
        ->map(fn ($i) => [
            'id' => $i->source_question_id,
            'question_text' => $i->question_text,
            'difficulty' => $i->difficulty,
            'topic' => null,
        ])->values();
@endphp
<script>
(function () {
    const readOnly = @json($readOnly);
    const MAX = {{ \App\Models\MockExam::MAX_ITEMS }};
    const routes = {
        bank: @json(route('faculty.mock-exams.bank-questions')),
        auto: @json(route('faculty.mock-exams.auto-pick')),
    };
    const subjectId = {{ $exam->subject_id }};
    const csrf = @json(csrf_token());

    // Items already on the exam, restored so a reload never loses the build.
    let selected = new Map(@json($existingItems).map(q => [q.id, q]));

    let pool = [];   // questions available for the selected topics
    let topicMode = @json($exam->topic_mode);
    let questionMode = @json($exam->question_mode);

    // ── steps ────────────────────────────────────────────────────────────
    document.querySelectorAll('.step-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const n = tab.dataset.step;
            document.querySelectorAll('.step-tab').forEach(t => t.classList.toggle('on', t === tab));
            document.querySelectorAll('.step-panel').forEach(p => p.classList.toggle('on', p.dataset.panel === n));
            if (n === '3') refreshPool();
        });
    });

    // ── mode toggles ─────────────────────────────────────────────────────
    function paintModes(groupId, value) {
        document.querySelectorAll('#' + groupId + ' button').forEach(b => b.classList.toggle('on', b.dataset.mode === value));
    }
    paintModes('topicModes', topicMode);
    paintModes('questionModes', questionMode);

    document.querySelectorAll('#topicModes button').forEach(b => b.addEventListener('click', () => {
        if (readOnly) return;
        topicMode = b.dataset.mode;
        document.getElementById('topicMode').value = topicMode;
        paintModes('topicModes', topicMode);
        if (topicMode === 'all') {
            document.querySelectorAll('.topic-check').forEach(c => { c.checked = true; });
        } else if (topicMode === 'auto') {
            // "Auto" for topics means every topic that actually has questions —
            // selecting an empty topic would just contribute nothing.
            document.querySelectorAll('.topic-check').forEach(c => { c.checked = Number(c.dataset.bank) > 0; });
        }
        syncTopics();
    }));

    document.querySelectorAll('#questionModes button').forEach(b => b.addEventListener('click', async () => {
        if (readOnly) return;
        questionMode = b.dataset.mode;
        document.getElementById('questionMode').value = questionMode;
        paintModes('questionModes', questionMode);
        document.getElementById('autoControls').style.display = questionMode === 'auto' ? 'flex' : 'none';
        await refreshPool();
        if (questionMode === 'all') {
            selected = new Map();
            pool.slice(0, MAX).forEach(q => selected.set(q.id, q));
            if (pool.length > MAX) {
                alert('The selected topics hold ' + pool.length + ' questions. Only the first ' + MAX + ' were added — that is the cap for one exam.');
            }
            renderQuestions();
        }
    }));

    // ── topics ───────────────────────────────────────────────────────────
    function checkedTopics() {
        return [...document.querySelectorAll('.topic-check:checked')].map(c => Number(c.value));
    }
    function syncTopics() {
        const ids = checkedTopics();
        document.getElementById('topicCount').textContent = ids.length;
        const bank = [...document.querySelectorAll('.topic-check:checked')]
            .reduce((sum, c) => sum + Number(c.dataset.bank || 0), 0);
        document.getElementById('topicPool').textContent = bank;
    }
    document.querySelectorAll('.topic-check').forEach(c => c.addEventListener('change', () => {
        if (topicMode !== 'manual') { topicMode = 'manual'; document.getElementById('topicMode').value = 'manual'; paintModes('topicModes', 'manual'); }
        syncTopics();
    }));
    syncTopics();

    // ── questions ────────────────────────────────────────────────────────
    async function refreshPool() {
        const ids = checkedTopics();
        if (!ids.length) { pool = []; renderQuestions(); return; }
        const params = new URLSearchParams();
        params.set('subject_id', subjectId);
        ids.forEach(id => params.append('topic_ids[]', id));
        try {
            const res = await fetch(routes.bank + '?' + params.toString(), { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            pool = data.questions || [];
        } catch (e) {
            pool = [];
        }
        renderQuestions();
    }

    function renderQuestions() {
        const list = document.getElementById('qList');
        if (!pool.length && !selected.size) {
            list.innerHTML = '<div class="empty" style="padding:34px;"><i class="fas fa-inbox"></i><h3>Pick your topics first</h3><p>Go back to step 2, choose topics, then come back here.</p></div>';
            updateCounter();
            return;
        }
        // Anything already chosen but no longer in the pool (topic deselected)
        // still shows, so a colleague's picks aren't silently dropped.
        const shown = [...pool];
        selected.forEach((q, id) => { if (!pool.some(p => p.id === id)) shown.push(q); });

        list.innerHTML = shown.map(q => `
            <label class="q-row">
                <input type="checkbox" class="q-check" value="${q.id}" ${selected.has(q.id) ? 'checked' : ''} ${readOnly ? 'disabled' : ''}>
                <span class="q-text">
                    ${escapeHtml(q.question_text || '').slice(0, 260)}
                    <span class="q-meta">
                        ${q.difficulty ? `<span class="diff diff-${q.difficulty}">${q.difficulty}</span>` : ''}
                        ${q.topic ? `<span>${escapeHtml(q.topic)}</span>` : ''}
                    </span>
                </span>
            </label>`).join('');

        list.querySelectorAll('.q-check').forEach(c => c.addEventListener('change', () => {
            const id = Number(c.value);
            if (c.checked) {
                if (selected.size >= MAX) {
                    c.checked = false;
                    alert('A mock exam can hold at most ' + MAX + ' questions.');
                    return;
                }
                selected.set(id, pool.find(p => p.id === id) || { id });
            } else {
                selected.delete(id);
            }
            updateCounter();
        }));
        updateCounter();
    }

    function updateCounter() {
        const el = document.getElementById('itemCounter');
        el.textContent = selected.size + ' / ' + MAX + ' selected';
        el.classList.toggle('over', selected.size > MAX);
    }

    const clearBtn = document.getElementById('clearItems');
    if (clearBtn) clearBtn.addEventListener('click', () => { selected = new Map(); renderQuestions(); });

    const autoBtn = document.getElementById('autoPickBtn');
    if (autoBtn) autoBtn.addEventListener('click', async () => {
        const ids = checkedTopics();
        if (!ids.length) { alert('Choose at least one topic in step 2 first.'); return; }
        autoBtn.disabled = true;
        try {
            const res = await fetch(routes.auto, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ subject_id: subjectId, topic_ids: ids, count: Number(document.getElementById('autoCount').value) }),
            });
            const data = await res.json();
            if (!res.ok) { alert(Object.values(data.errors || { e: ['Could not generate.'] })[0][0]); return; }
            selected = new Map((data.questions || []).map(q => [q.id, q]));
            if (data.short) {
                alert('Only ' + data.returned + ' of the ' + data.requested + ' questions you asked for could be found — the test bank for these topics has run out. Add more topics or more questions to the bank.');
            }
            renderQuestions();
        } finally {
            autoBtn.disabled = false;
        }
    });

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
    }

    // ── submit ───────────────────────────────────────────────────────────
    function packForm() {
        const form = document.getElementById('buildForm');
        form.querySelectorAll('input[name="topic_ids[]"]').forEach(n => n.remove());
        checkedTopics().forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'topic_ids[]'; input.value = id;
            form.appendChild(input);
        });
        document.getElementById('itemsJson').value = JSON.stringify([...selected.keys()].map(id => ({ source_question_id: id })));
    }

    const buildForm = document.getElementById('buildForm');
    if (buildForm) buildForm.addEventListener('submit', packForm);

    const reviewBtn = document.getElementById('submitReviewBtn');
    if (reviewBtn) reviewBtn.addEventListener('click', () => {
        if (!selected.size) { alert('Add at least one question before submitting for review.'); return; }
        if (!confirm('Send this to the Program Chair for review?\n\nYou can keep editing until they publish it.')) return;
        // then_submit makes the save and the hand-over one request, so the Chair
        // always reviews exactly what is on screen right now.
        const marker = document.createElement('input');
        marker.type = 'hidden'; marker.name = 'then_submit'; marker.value = '1';
        buildForm.appendChild(marker);
        packForm();
        buildForm.submit();
    });

    renderQuestions();
    if (questionMode === 'auto') document.getElementById('autoControls').style.display = 'flex';
})();
</script>
</body>
</html>
