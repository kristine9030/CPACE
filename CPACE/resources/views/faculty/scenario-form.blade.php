<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $editMode ? 'Edit Scenario' : 'New Scenario' }} - CPACE {{ $isChair ? 'Program Chair' : 'Faculty' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; position:relative; z-index:100; }
        .topbar-left { display:flex; align-items:center; gap:12px; }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .page-title { font-size:26px; font-weight:700; color:#14283E; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .breadcrumb { display:flex; align-items:center; gap:6px; font-size:12px; color:#aaa; margin-bottom:4px; }
        .breadcrumb a { color:var(--accent); text-decoration:none; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:#fff; } .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:#fff; color:#555; border:1px solid #e0e0e0; } .btn-ghost:hover { background:#f5f5f5; }
        .btn-danger { background:#fff; color:#c0392b; border:1px solid #f1c9c4; } .btn-danger:hover { background:#fdecea; }
        .form-layout { display:grid; grid-template-columns:minmax(0,1fr) 320px; gap:20px; align-items:start; }
        .card { background:#fff; border-radius:14px; padding:24px; margin-bottom:20px; }
        .card-title { font-size:14px; font-weight:700; color:#1a1a1a; margin-bottom:6px; display:flex; align-items:center; gap:8px; }
        .card-title i { color:var(--accent); }
        .card-title .step-no { width:22px; height:22px; border-radius:50%; background:#f3f4f6; border:1px solid #e5e7eb; color:#9ca3af; font-size:11px; font-weight:600; display:inline-flex; align-items:center; justify-content:center; }
        .card-hint { font-size:12px; color:#9a9cab; margin-bottom:16px; line-height:1.55; }
        label { display:block; font-size:12px; font-weight:600; color:#555; margin-bottom:6px; }
        .req { color:var(--accent); margin-left:2px; }
        input[type=text], select { width:100%; font-family:'Poppins',sans-serif; font-size:13px; border:1.5px solid #e8e8e8; border-radius:8px; padding:10px 14px; color:#333; background:#fff; outline:none; transition:border-color .2s; }
        input[type=text]:focus, select:focus { border-color:var(--primary); }
        .side-card { background:#fff; border-radius:14px; padding:22px; margin-bottom:16px; }
        .side-title { font-size:13px; font-weight:700; color:#1a1a1a; margin-bottom:14px; }
        .meta-item { margin-bottom:14px; } .meta-item:last-child { margin-bottom:0; }
        .help-tip { font-size:11px; color:#aaa; margin-top:5px; }
        .toggle-row { display:flex; justify-content:space-between; align-items:center; }
        .toggle { position:relative; width:44px; height:24px; } .toggle input { opacity:0; width:0; height:0; }
        .toggle-slider { position:absolute; inset:0; background:#ddd; border-radius:24px; cursor:pointer; transition:.3s; }
        .toggle-slider:before { content:''; position:absolute; width:18px; height:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
        .toggle input:checked + .toggle-slider { background:var(--primary); } .toggle input:checked + .toggle-slider:before { transform:translateX(20px); }
        .topic-search { position:relative; margin-bottom:8px; }
        .topic-search i { position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:12px; color:#aaa; pointer-events:none; }
        .topic-search input { width:100%; font-family:'Poppins',sans-serif; font-size:12.5px; border:1.5px solid #e8e8e8; border-radius:8px; padding:9px 12px 9px 32px; outline:none; background:#fff; }
        .topic-search input:focus { border-color:var(--primary); } .topic-search input:disabled { background:#f6f6f8; cursor:not-allowed; }
        #topicSelect[size] { padding:4px; } #topicSelect[size] option { padding:7px 10px; border-radius:6px; }
        .q-count { margin-left:auto; font-size:11.5px; font-weight:600; color:var(--primary); background:var(--primary-light); border-radius:999px; padding:3px 10px; }
        .submit-actions { display:flex; flex-direction:column; gap:10px; }

        /* student preview */
        .sv-overlay { position:fixed; inset:0; background:rgba(15,17,30,.55); backdrop-filter:blur(3px); z-index:9000; display:none; align-items:center; justify-content:center; padding:20px; }
        .sv-overlay.open { display:flex; }
        .sv-dialog { background:#f4f5f7; border-radius:20px; width:min(900px,100%); max-height:100%; display:flex; flex-direction:column; box-shadow:0 30px 80px rgba(0,0,0,.35); overflow:hidden; }
        .sv-top { display:flex; align-items:center; gap:12px; padding:14px 20px; background:#fff; border-bottom:1px solid #eceef2; }
        .sv-top h3 { font-size:14px; font-weight:700; color:#14283E; } .sv-top small { color:#9a9cab; font-size:11.5px; }
        .sv-seg { margin-left:auto; display:inline-flex; background:#f0f1f5; border-radius:10px; padding:3px; }
        .sv-seg button { border:0; background:transparent; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:600; color:#777; cursor:pointer; font-family:'Poppins',sans-serif; display:inline-flex; gap:6px; align-items:center; }
        .sv-seg button.on { background:#fff; color:var(--primary); box-shadow:0 1px 3px rgba(0,0,0,.1); }
        .sv-close { border:0; background:#f0f1f5; width:32px; height:32px; border-radius:50%; cursor:pointer; color:#666; }
        .sv-stage { overflow:auto; padding:26px 20px; display:flex; justify-content:center; }
        .sv-device { width:100%; max-width:760px; transition:max-width .25s; } .sv-device.phone { max-width:390px; }
        .sv-device.phone .sv-screen { border:8px solid #1c1e2a; border-radius:34px; }
        .sv-screen { background:#f4f5f7; border-radius:16px; padding:16px; box-shadow:0 8px 30px rgba(0,0,0,.12); }
        {!! \App\Support\RichText::fitCss() !!}
        @include('partials.scenario-box-styles')
        .sv-screen .q-card { background:#fff; border-radius:16px; padding:18px; margin-bottom:12px; box-shadow:0 1px 6px rgba(0,0,0,.07); }
        .sv-screen .q-card-label { font-size:10px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.8px; margin-bottom:10px; }
        .sv-screen .q-text { font-size:15px; font-weight:500; color:#111; line-height:1.65; overflow-wrap:anywhere; }
        .sv-screen .choice { display:flex; align-items:center; gap:12px; padding:13px 14px; background:#fff; border:2px solid #e5e7eb; border-radius:14px; margin-top:8px; font-size:13.5px; }
        .sv-screen .choice .l { width:30px; height:30px; border-radius:50%; background:#f3f4f6; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; color:#555; flex-shrink:0; }
        .sv-screen .choice.ok { border-color:#10b981; background:#ecfdf5; } .sv-screen .choice.ok .l { background:#10b981; color:#fff; }
        .sv-screen ul, .sv-screen ol { margin:.3em 0 .3em 1.5em; padding:0; } .sv-screen img { max-width:100%; height:auto; } .sv-screen table { border-collapse:collapse; max-width:100%; }

        @media (max-width:768px) { .form-layout { grid-template-columns:minmax(0,1fr); } .main { margin-left:0; padding:16px; } .topbar { flex-wrap:wrap; gap:12px; } }
    </style>
    @include('partials.rich-editor')
    @include('partials.question-cards')
</head>
<body>

@if($isChair)
    @include('partials.chair-sidebar', ['active' => 'ai-review'])
@else
    @include('partials.faculty-sidebar', ['active' => 'add-question'])
@endif

@php
    $ns = $isChair ? 'chair' : 'faculty';
    $backRoute = $isChair ? 'chair.ai-review' : 'faculty.test-bank';
    $uploadUrl = route($ns . '.question.upload-image');
@endphp

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="breadcrumb">
                    <a href="{{ route($backRoute) }}">{{ $isChair ? 'AI Substitute Review' : 'Test Bank' }}</a>
                    <i class="fas fa-chevron-right" style="font-size:9px;"></i>
                    <span>{{ $editMode ? 'Edit Scenario' : 'New Scenario' }}</span>
                </div>
                <div class="page-title">{{ $editMode ? 'Edit Scenario' : 'New Scenario' }}</div>
                <div class="page-sub">One situation with as many questions as you need. Students always get them together.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route($backRoute) }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
            @include('partials.topbar-actions')
        </div>
    </div>

    <form action="{{ $editMode ? route($ns . '.scenario.update', $scenario['id']) : route($ns . '.scenario.store') }}" method="POST" id="scenarioForm"
          data-confirm="{{ $editMode ? 'Your changes go live immediately for every quiz and exam built from now on.' : 'This scenario and its questions are added to the test bank and can be served to students right away.' }}"
          data-confirm-title="{{ $editMode ? 'Save changes to this scenario?' : 'Publish this scenario?' }}"
          data-confirm-ok="{{ $editMode ? 'Yes, save changes' : 'Yes, publish it' }}"
          data-confirm-icon="question">
        @csrf
        @if($editMode) @method('PUT') @endif
        <input type="hidden" name="questions_json" id="questionsJson" value="">

        <div class="form-layout">
            <div>
                <div class="card">
                    <div class="card-title"><span class="step-no">1</span> <i class="fas fa-file-lines"></i> Scenario <span class="req" title="Required">*</span></div>
                    <p class="card-hint">The situation, case or data the questions refer to. You can use tables, pictures, lists and any formatting. Students see this above each of the questions below.</p>
                    <div class="meta-item">
                        <label>Title <span style="font-weight:400;color:#aaa;">(optional, only you see it)</span></label>
                        <input type="text" name="title" maxlength="150" value="{{ old('title', $scenario['title']) }}" placeholder="e.g. Impairment of equipment: ABC Corp.">
                    </div>
                    <textarea name="body" class="rte-src" id="scenarioBody" data-upload-url="{{ $uploadUrl }}" placeholder="Write the scenario here...">{{ old('body', $scenario['body']) }}</textarea>
                </div>

                <div class="card">
                    <div class="card-title"><span class="step-no">2</span> <i class="fas fa-list-ol"></i> Questions <span class="req" title="At least one">*</span> <span class="q-count" id="qCount">0 questions</span></div>
                    <p class="card-hint">Add as many questions as the scenario needs; there is no limit. Drag nothing: use the arrows to reorder.</p>
                    <div id="qcMount"></div>
                </div>
            </div>

            <div>
                <div class="side-card">
                    <div class="side-title">Publish</div>
                    <div class="meta-item">
                        <div class="toggle-row">
                            <span style="font-size:13px;color:#555;">Status</span>
                            <div style="display:flex;align-items:center;gap:8px;">
                                @php $isActive = old('is_active', $scenario['is_active']); @endphp
                                <label class="toggle" style="margin:0;"><input type="checkbox" name="is_active" value="1" id="statusToggle" {{ $isActive ? 'checked' : '' }}><span class="toggle-slider"></span></label>
                                <span style="font-size:13px;color:#555;" id="statusLabel">{{ $isActive ? 'Active' : 'Draft' }}</span>
                            </div>
                        </div>
                        <div class="help-tip" style="margin-top:6px;">Active scenarios (all their questions) appear in quizzes and exams.</div>
                    </div>
                    <div class="submit-actions" style="margin-top:18px;">
                        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="fas fa-save"></i> {{ $editMode ? 'Save Changes' : 'Save Scenario' }}</button>
                        <button type="button" class="btn btn-ghost" id="previewBtn" style="width:100%;justify-content:center;"><i class="fas fa-eye"></i> View as student</button>
                    </div>
                </div>

                <div class="side-card">
                    <div class="side-title">Classification</div>
                    <div class="meta-item">
                        <label>Subject <span class="req">*</span></label>
                        <select id="subjectSelect">
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ (string) ($currentSubject ?? '') === (string) $subject->id ? 'selected' : '' }}>{{ $subject->code }} – {{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="meta-item">
                        <label>Topic <span class="req">*</span></label>
                        <div class="topic-search"><i class="fas fa-magnifying-glass"></i><input type="text" id="topicSearch" placeholder="Search topics…" autocomplete="off" disabled></div>
                        <select name="topic_id" id="topicSelect" data-selected="{{ old('topic_id', $scenario['topic_id']) }}"><option value="">Select Subject First</option></select>
                        <div class="help-tip" id="topicCount"></div>
                    </div>
                </div>

                @if($editMode)
                    <div class="side-card">
                        <div class="side-title">Danger zone</div>
                        <button type="button" class="btn btn-danger" style="width:100%;justify-content:center;" id="deleteBtn"><i class="fas fa-trash"></i> Delete scenario</button>
                        <div class="help-tip" style="margin-top:8px;">Deletes the scenario and all of its questions.</div>
                    </div>
                @endif
            </div>
        </div>
    </form>

    @if($editMode)
        <form action="{{ route($ns . '.scenario.destroy', $scenario['id']) }}" method="POST" id="deleteForm"
              data-confirm="The scenario and every question under it will be permanently removed from the test bank."
              data-confirm-title="Delete this scenario?" data-confirm-ok="Yes, delete it" data-confirm-danger>
            @csrf @method('DELETE')
        </form>
    @endif
</main>

<!-- VIEW AS STUDENT -->
<div class="sv-overlay" id="svOverlay" role="dialog" aria-modal="true" aria-label="Student preview">
    <div class="sv-dialog">
        <div class="sv-top">
            <div><h3>Student preview</h3><small>The scenario and its questions as a student sees them</small></div>
            <div class="sv-seg" id="svSeg">
                <button type="button" data-d="phone" class="on"><i class="fas fa-mobile-screen"></i> Phone</button>
                <button type="button" data-d="desktop"><i class="fas fa-desktop"></i> Desktop</button>
            </div>
            <button type="button" class="sv-close" id="svClose" aria-label="Close preview"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="sv-stage"><div class="sv-device phone" id="svDevice"><div class="sv-screen" id="svScreen"></div></div></div>
    </div>
</div>

@include('partials.alerts')

<script>
const SCENARIO = @json($scenario);
const topicMap = @json($subjects->mapWithKeys(fn($s) => [$s->id => $s->topics->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->values()]));
const esc = s => String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

document.addEventListener('DOMContentLoaded', () => {
    RTE.init();

    // ── the questions ──
    const counter = document.getElementById('qCount');
    const qc = QCards.mount(document.getElementById('qcMount'), {
        uploadUrl: @json($uploadUrl),
        items: SCENARIO.questions && SCENARIO.questions.length ? SCENARIO.questions : [{ question_type: 'mcq' }],
        onChange: items => { counter.textContent = items.length + (items.length === 1 ? ' question' : ' questions'); },
    });
    counter.textContent = qc.get().length + (qc.get().length === 1 ? ' question' : ' questions');

    const form = document.getElementById('scenarioForm');
    form.addEventListener('submit', () => { document.getElementById('questionsJson').value = JSON.stringify(qc.get()); });

    const toggle = document.getElementById('statusToggle');
    toggle.addEventListener('change', () => { document.getElementById('statusLabel').textContent = toggle.checked ? 'Active' : 'Draft'; });

    const del = document.getElementById('deleteBtn');
    if (del) del.addEventListener('click', () => document.getElementById('deleteForm').requestSubmit());

    // ── topics, with search ──
    let topicList = [], chosen = '';
    const sel = document.getElementById('topicSelect'), search = document.getElementById('topicSearch'), subj = document.getElementById('subjectSelect');
    function renderTopics() {
        const q = search.value.trim().toLowerCase();
        const matches = topicList.filter(t => !q || t.name.toLowerCase().includes(q));
        sel.innerHTML = '';
        const first = document.createElement('option');
        first.value = '';
        first.textContent = !topicList.length ? 'Select Subject First' : (q && !matches.length ? 'No topics match your search' : 'Select Topic');
        sel.appendChild(first);
        const shown = matches.slice(), keep = topicList.find(t => String(t.id) === String(chosen));
        if (keep && !shown.includes(keep)) shown.unshift(keep);
        shown.forEach(t => { const o = document.createElement('option'); o.value = t.id; o.textContent = t.name; if (String(t.id) === String(chosen)) o.selected = true; sel.appendChild(o); });
        if (q && matches.length) sel.setAttribute('size', Math.min(7, shown.length + 1)); else sel.removeAttribute('size');
        document.getElementById('topicCount').textContent = !topicList.length ? '' : (q ? matches.length + ' of ' + topicList.length + ' topics' : topicList.length + (topicList.length === 1 ? ' topic' : ' topics'));
    }
    function loadTopics() {
        topicList = topicMap[subj.value] || [];
        if (!topicList.some(t => String(t.id) === String(chosen))) chosen = '';
        search.value = ''; search.disabled = !topicList.length;
        renderTopics();
    }
    chosen = String(sel.dataset.selected || '');
    subj.addEventListener('change', () => { chosen = ''; loadTopics(); });
    search.addEventListener('input', renderTopics);
    search.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); const q = search.value.trim().toLowerCase(), hit = topicList.find(t => !q || t.name.toLowerCase().includes(q)); if (hit) { chosen = String(hit.id); search.value = ''; renderTopics(); } }
        if (e.key === 'Escape') { search.value = ''; renderTopics(); }
    });
    sel.addEventListener('change', () => { chosen = sel.value; if (search.value) { search.value = ''; renderTopics(); } });
    loadTopics();

    // ── view as student ──
    const overlay = document.getElementById('svOverlay');
    document.getElementById('previewBtn').addEventListener('click', () => {
        const body = document.getElementById('scenarioBody').value;
        const items = qc.get();
        const keep = el => el.querySelectorAll('[style*="inline-block"]').forEach(e => { const w = parseFloat(e.style.width), h = parseFloat(e.style.height); if (w && h) e.style.aspectRatio = w + ' / ' + h; });
        const screen = document.getElementById('svScreen');
        screen.innerHTML = `<div class="sc-box" id="svSc"><button type="button" class="sc-head" id="svScHead"><span><i class="fas fa-file-lines"></i> Scenario</span><i class="fas fa-chevron-up sc-chev"></i></button><div class="sc-body rt-fit">${RTE.toPlain(body) || /<(img|table)/i.test(body) ? RTE.html(body) : '<em style="color:#bbb">Your scenario will appear here…</em>'}</div></div>` +
            items.map((it, i) => `<div class="q-card"><div class="q-card-label">Question ${i + 1} of ${items.length}</div><div class="q-text rt-fit">${RTE.toPlain(it.question_text) || /<(img|table)/i.test(it.question_text) ? RTE.html(it.question_text) : '<em style="color:#bbb">Question text…</em>'}</div>` +
                it.choices.filter(c => c.text.trim()).map((c, j) => `<div class="choice ${c.is_correct ? 'ok' : ''}"><span class="l">${it.question_type === 'true_false' ? (j ? 'F' : 'T') : String.fromCharCode(65 + j)}</span>${esc(c.text)}</div>`).join('') + '</div>').join('');
        keep(screen);
        document.getElementById('svScHead').addEventListener('click', () => document.getElementById('svSc').classList.toggle('collapsed'));
        overlay.classList.add('open');
    });
    document.getElementById('svClose').addEventListener('click', () => overlay.classList.remove('open'));
    overlay.addEventListener('mousedown', e => { if (e.target === overlay) overlay.classList.remove('open'); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') overlay.classList.remove('open'); });
    document.getElementById('svSeg').addEventListener('click', e => {
        const b = e.target.closest('button[data-d]'); if (!b) return;
        document.querySelectorAll('#svSeg button').forEach(x => x.classList.toggle('on', x === b));
        document.getElementById('svDevice').classList.toggle('phone', b.dataset.d === 'phone');
    });
});
</script>
</body>
</html>
