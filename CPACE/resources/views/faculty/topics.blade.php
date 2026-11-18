<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Topics - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:16px; flex-wrap:wrap; }
        .page-title { font-size:26px; font-weight:700; color:#14283E; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:white; } .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:white; color:#555; border:1px solid #e0e0e0; } .btn-ghost:hover { background:#f5f5f5; }
        .btn-sm { padding:7px 13px; font-size:12px; }
        .card { background:white; border-radius:14px; padding:22px; box-shadow:0 1px 2px rgba(16,24,40,.04), 0 4px 14px rgba(16,24,40,.06); }
        .card + .card { margin-top:18px; }
        .empty { text-align:center; padding:40px 20px; color:#aaa; }
        .form-group { margin-bottom:16px; } .form-group.full { grid-column:1 / -1; }
        .form-group label { display:block; font-size:12px; font-weight:600; color:#555; margin-bottom:6px; }
        .hint { font-size:11px; color:#aaa; margin-top:5px; }

        /* Subject picker */
        .sub-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(230px, 1fr)); gap:14px; }
        .sub-card { display:flex; align-items:center; gap:13px; padding:16px; background:#fff; border-radius:13px; border-left:4px solid var(--sc-base, var(--primary)); box-shadow:0 1px 2px rgba(16,24,40,.04), 0 4px 14px rgba(16,24,40,.06); text-decoration:none; color:inherit; transition:transform .15s, box-shadow .15s; }
        .sub-card:hover { transform:translateY(-1px); box-shadow:0 2px 4px rgba(16,24,40,.05), 0 8px 20px rgba(16,24,40,.09); }
        .sub-ic { width:44px; height:44px; border-radius:12px; flex-shrink:0; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:17px; }
        .sub-code { font-size:14px; font-weight:700; color:#1a1a1a; }
        .sub-name { font-size:11.5px; color:#888; margin-top:2px; }
        .sub-topics { font-size:11px; color:#aaa; margin-top:4px; }

        /* Opened subject */
        .crumb { display:inline-flex; align-items:center; gap:8px; font-size:12.5px; color:#888; text-decoration:none; margin-bottom:14px; }
        .crumb:hover { color:var(--primary); }
        .sd-head { position:relative; display:flex; align-items:center; gap:18px; padding:20px 24px; border-radius:16px; color:#fff; margin-bottom:18px; background:linear-gradient(135deg, var(--primary) 0%, #4a1212 100%); }
        .sd-icon { width:50px; height:50px; border-radius:14px; flex-shrink:0; background:#fff; color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:20px; }
        .sd-code { font-size:21px; font-weight:700; }
        .sd-name { font-size:12.5px; opacity:.95; margin-top:2px; }

        .section-head { display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:9px; }
        .sd-section-title { display:inline-flex; align-items:center; gap:8px; font-size:14px; font-weight:700; color:#1a1a1a; }
        .sd-section-title i { color:var(--primary); }
        .topics-count { display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;border-radius:10px;background:#f3f4f6;color:#6b7280;font-size:10.5px;font-weight:700; }
        .add-mini { border:0;background:var(--primary-light);color:var(--primary);border-radius:7px;padding:5px 9px;font:600 10px 'Poppins',sans-serif;cursor:pointer; }
        .topic-search { position:relative; margin-bottom:9px; }
        .topic-search i { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#bbb; font-size:10px; }
        .topic-search input[type=text] { width:100%; padding:8px 10px 8px 32px; border:1.5px solid #e2e2e6; border-radius:8px; font:11px 'Poppins',sans-serif; }
        .topic-search input[type=text]:focus { outline:none; border-color:var(--primary); }
        .topic-list { display:flex;flex-direction:column;gap:6px; }
        .topic-node + .topic-node { margin-top:6px; }
        .topic-row { display:flex;align-items:center;gap:9px;padding:8px 9px;border-radius:8px;background:#fafafa;border:1px solid #f0f0f0; }
        .topic-toggle { width:18px;height:18px;flex-shrink:0;border:0;background:none;color:#999;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:9px;padding:0; }
        .topic-toggle.open i { transform:rotate(90deg); } .topic-toggle i { transition:transform .15s; }
        .topic-toggle-spacer { width:18px;flex-shrink:0; }
        .topic-check { flex-shrink:0;width:15px;height:15px;cursor:pointer; }
        .topic-order { width:22px;height:22px;flex-shrink:0;border-radius:6px;background:#fff;color:#999;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700; }
        .topic-info { flex:1;min-width:0; } .topic-name { font-size:11px;font-weight:600;color:#333;display:flex;align-items:center;gap:0; } .topic-name .tn-text { white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
        .topic-meta { font-size:9.5px;color:#aaa;margin-top:2px; } .topic-actions { display:flex;gap:4px; }
        .topic-actions .icon-btn { width:25px;height:25px;font-size:9px; }
        .topic-children { margin-top:6px; }
        .topic-audience-badge { color:#7c3aed; font-weight:600; }
        .inactive-pill { display:inline-flex;padding:3px 8px;border-radius:12px;background:#f3f4f6;color:#6b7280;font-size:9px;font-weight:700;margin-left:5px; }
        .icon-btn { width:30px;height:30px;border:0;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;font-size:11px;text-decoration:none; }
        .ib-edit { background:#dbeafe;color:#2563eb; } .ib-delete { background:#fde8e8;color:#b91c1c; }
        .ib-success { background:#d1fae5;color:#059669; } .ib-muted { background:#f3f4f6;color:#9ca3af; }
        .empty-msg { font-size:11px;color:#bbb;display:flex;align-items:center;gap:7px;padding:4px 0; }

        /* Modal + audience picker */
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:2000;align-items:center;justify-content:center;padding:20px; }
        .modal-overlay.open { display:flex; } .modal { background:#fff;border-radius:16px;width:100%;max-width:560px;padding:24px;max-height:90vh;overflow-y:auto; }
        .modal h3 { font-size:16px;color:#1a1a1a;margin-bottom:4px; } .modal-sub { font-size:11px;color:#999;margin-bottom:18px; }
        .modal-grid { display:grid;grid-template-columns:minmax(0, 1fr) minmax(0, 1fr);gap:14px; } .full { grid-column:1/-1; }
        textarea { width:100%;padding:10px 12px;border:1.5px solid #e2e2e6;border-radius:8px;font:13px 'Poppins',sans-serif;resize:vertical;min-height:78px; }
        input[type=number], input[type=text], select { width:100%;padding:10px 12px;border:1.5px solid #e2e2e6;border-radius:8px;font:13px 'Poppins',sans-serif;background:#fff; }
        textarea:focus,input:focus,select:focus { outline:none;border-color:var(--primary); }
        .toggle-row { display:flex;align-items:center;gap:9px;padding:10px 12px;background:#f8f8fa;border-radius:8px; }
        .toggle-row input { width:17px;height:17px;accent-color:var(--primary); } .toggle-row label { margin:0;font-size:11px; }
        .modal-actions { display:flex;justify-content:flex-end;gap:9px;margin-top:20px; }
        .audience-group { margin-bottom:8px; }
        .audience-label { font-size:10.5px; font-weight:600; color:#777; margin-bottom:4px; }
        .audience-options { display:flex; flex-wrap:wrap; gap:6px; max-height:90px; overflow-y:auto; }
        .audience-chip { display:flex; align-items:center; gap:4px; font-size:10.5px; color:#555; background:#f5f5f7; border:1px solid #ececef; border-radius:6px; padding:4px 7px; cursor:pointer; }
        .audience-chip input { margin:0; }
        @media(max-width:620px) { .modal-grid { grid-template-columns:minmax(0, 1fr); }.full { grid-column:auto; } }
        @media (max-width:980px) { .main { margin-left:0; padding:18px; } }
    </style>
</head>
<body>
@include('partials.faculty-sidebar', ['active' => 'test-bank'])

<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">Topics</div>
            <div class="page-sub">Add topics and set which year, semester, and section study them.</div>
        </div>
        <a href="{{ route('faculty.test-bank') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back to Test Bank</a>
    </div>

    @if($openSubject)
        @php $subject = $openSubject; @endphp
        <a class="crumb" href="{{ route('faculty.test-bank.topics') }}"><i class="fas fa-arrow-left"></i> All subjects</a>

        <div class="sd-head">
            <div class="sd-icon"><i class="fas fa-book"></i></div>
            <div>
                <div class="sd-code">{{ $subject->code }}</div>
                <div class="sd-name">{{ $subject->name }}</div>
            </div>
        </div>

        <div class="card">
            <div class="section-head">
                <span class="sd-section-title"><i class="fas fa-list-check"></i> Topics <span class="topics-count">{{ $subject->topics->count() }}</span></span>
                @unless($readOnly)
                    <span style="display:inline-flex;gap:6px;align-items:center;">
                        @if($subject->topics->isNotEmpty())
                            <label style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#666;cursor:pointer;">
                                <input type="checkbox" id="topicSelectAll-{{ $subject->id }}" onchange="toggleAllTopics({{ $subject->id }}, this.checked)"> Select all
                            </label>
                            <button type="button" class="add-mini" id="topicBulkDeleteBtn-{{ $subject->id }}" style="display:none;background:#fde8e8;color:#b42318;" onclick="openBulkTopicDelete({{ $subject->id }}, '{{ addslashes($subject->code) }}')"><i class="fas fa-trash"></i> Delete selected (<span id="topicBulkCount-{{ $subject->id }}">0</span>)</button>
                        @endif
                        <button type="button" class="add-mini" onclick="openTopic({{ $subject->id }}, '{{ addslashes($subject->code) }}')"><i class="fas fa-plus"></i> Add Topic</button>
                    </span>
                @endunless
            </div>
            @if($subject->topicTree->isNotEmpty())
                <div class="topic-search">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" placeholder="Search topics..." oninput="searchFacultyTopics(this, this.value)">
                </div>
            @endif
            <div class="topic-list" id="topics-data-{{ $subject->id }}" data-topics="{{ json_encode($subject->topics->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'parent_id' => $t->parent_id])) }}">
                @if($subject->topicTree->isNotEmpty())
                    @include('chair.partials.topic-node', ['subject' => $subject, 'topics' => $subject->topicTree, 'depth' => 0, 'readOnly' => $readOnly])
                @else
                    <div class="empty-msg"><i class="fas fa-list"></i>No topics added yet.</div>
                @endif
            </div>
        </div>
    @else
        @if($subjects->isEmpty())
            <div class="card"><div class="empty"><i class="fas fa-layer-group"></i><div>You're not assigned to any subjects yet. Ask your Program Chair for access.</div></div></div>
        @else
            <div class="sub-grid">
                @foreach($subjects as $subject)
                    <a class="sub-card" href="{{ route('faculty.test-bank.topics', ['subject' => $subject->id]) }}">
                        <div class="sub-ic"><i class="fas fa-book"></i></div>
                        <div>
                            <div class="sub-code">{{ $subject->code }}</div>
                            <div class="sub-name">{{ $subject->name }}</div>
                            <div class="sub-topics">{{ $subject->topics->count() }} topic{{ $subject->topics->count() === 1 ? '' : 's' }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    @endif
</main>

<div class="modal-overlay" id="topicModal">
    <div class="modal">
        <h3 id="topicModalTitle">Add Topic</h3><div class="modal-sub" id="topicModalSub">Add a curriculum topic to this subject.</div>
        <form method="POST" id="topicForm"
              data-confirm="This topic will be saved and become available to questions and quizzes."
              data-confirm-title="Save this topic?"
              data-confirm-ok="Yes, save topic"
              data-confirm-icon="question">@csrf <input type="hidden" name="_method" id="topicMethod" value="POST">
            <div class="modal-grid">
                <div class="form-group"><label>Topic Name</label><input type="text" name="name" required placeholder="Topic name"></div>
                <div class="form-group"><label>Display Order</label><input type="number" name="sort_order" min="0" max="9999" value="0" required></div>
                <div class="form-group full"><label>Parent Topic</label><select name="parent_id" id="topicParent"><option value="">— None (top-level topic) —</option></select><div class="hint">Choose an existing topic to make this a subtopic of it.</div></div>
                <div class="form-group full"><label>Description</label><textarea name="description" placeholder="Optional topic description"></textarea></div>
                <div class="form-group full">
                    <label>Who studies this topic <span style="font-weight:400;color:#999;">(leave all unchecked = every year/sem/section)</span></label>
                    <div class="hint" style="margin-bottom:6px;">Use this when a topic is only covered in a specific term, e.g. "Non-Financial Assets" taught to 3rd Year in 2nd Sem only — students outside this won't be quizzed on it until it's their turn.</div>
                    <div class="audience-group">
                        <div class="audience-label">Year level</div>
                        <div class="audience-options" id="topicAudienceYears">
                            @foreach(\App\Models\Section::YEAR_LABELS as $yearValue => $yearLabel)
                                <label class="audience-chip"><input type="checkbox" name="audience_years[]" value="{{ $yearValue }}" onchange="filterTopicAudienceSections()">{{ $yearLabel }}</label>
                            @endforeach
                        </div>
                    </div>
                    <div class="audience-group">
                        <div class="audience-label">Semester</div>
                        <div class="audience-options" id="topicAudienceSemesters">
                            @foreach(\App\Models\Section::SEMESTER_LABELS as $semValue => $semLabel)
                                <label class="audience-chip"><input type="checkbox" name="audience_semesters[]" value="{{ $semValue }}">{{ $semLabel }}</label>
                            @endforeach
                        </div>
                    </div>
                    <div class="audience-group">
                        <div class="audience-label">Section <span style="font-weight:400;color:#999;">(optional, narrows further)</span></div>
                        <div class="hint" id="topicAudienceSectionsHint" style="margin:0 0 6px;">Pick a year level above to narrow this down to its sections.</div>
                        <div class="audience-options" id="topicAudienceSections">
                            @foreach($sections as $section)
                                <label class="audience-chip" data-year="{{ $section->year_level ?? '' }}"><input type="checkbox" name="audience_sections[]" value="{{ $section->name }}">{{ $section->name }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="toggle-row full"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" id="topicActive" value="1" checked><label for="topicActive">Active and available for questions and quizzes</label></div>
            </div>
            <div class="modal-actions"><button type="button" class="btn btn-ghost" onclick="closeModal('topicModal')">Cancel</button><button class="btn btn-primary"><i class="fas fa-save"></i> Save Topic</button></div>
        </form>
    </div>
</div>

<form method="POST" id="confirmForm" hidden>@csrf <input type="hidden" name="_method" id="confirmMethod" value="DELETE"></form>
<form method="POST" id="bulkTopicDeleteForm" hidden>@csrf</form>

<script>
const FACULTY_TOPICS_BASE = @json(url('/faculty/test-bank/topics'));

function getTopicForm() { return document.getElementById('topicForm'); }

function descendantIds(topics, rootId) {
    const ids = new Set();
    let frontier = [rootId];
    while (frontier.length) {
        const next = topics.filter(t => frontier.includes(t.parent_id)).map(t => t.id);
        next.forEach(id => ids.add(id));
        frontier = next;
    }
    return ids;
}

function populateParentOptions(subjectId, excludeTopicId, selectedParentId) {
    const el = document.getElementById(`topics-data-${subjectId}`);
    const topics = el ? JSON.parse(el.dataset.topics || '[]') : [];

    const excluded = excludeTopicId ? descendantIds(topics, excludeTopicId) : new Set();
    if (excludeTopicId) excluded.add(excludeTopicId);

    const byId = Object.fromEntries(topics.map(t => [t.id, t]));
    const depthOf = (t) => { let d = 0, p = t.parent_id; while (p) { d++; p = byId[p]?.parent_id; } return d; };

    const select = document.getElementById('topicParent');
    select.innerHTML = '<option value="">— None (top-level topic) —</option>';
    topics
        .filter(t => !excluded.has(t.id))
        .sort((a, b) => a.name.localeCompare(b.name))
        .forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = '—'.repeat(depthOf(t)) + ' ' + t.name;
            select.appendChild(opt);
        });
    select.value = selectedParentId ?? '';
}

function openTopic(subjectId, subjectCode, topic = null, parentId = null) {
    const f = getTopicForm();
    if (!f) return;
    document.getElementById('topicModalTitle').textContent = topic ? 'Edit Topic' : (parentId ? 'Add Subtopic' : 'Add Topic');
    document.getElementById('topicModalSub').textContent = `${topic ? 'Update a' : 'Add a new'} curriculum topic for ${subjectCode}.`;
    f.action = topic ? `${FACULTY_TOPICS_BASE}/${subjectId}/${topic.id}` : `${FACULTY_TOPICS_BASE}/${subjectId}`;
    document.getElementById('topicMethod').value = topic ? 'PUT' : 'POST';
    if (f.elements) {
        f.elements['name'].value = topic?.name ?? '';
        f.elements['description'].value = topic?.description ?? '';
        f.elements['sort_order'].value = topic?.sort_order ?? 0;
    }
    document.getElementById('topicActive').checked = topic ? Boolean(topic.is_active) : true;
    setAudienceCheckboxes('topicAudienceYears', 'audience_years[]', (topic?.audience_years ?? []).map(String));
    setAudienceCheckboxes('topicAudienceSemesters', 'audience_semesters[]', (topic?.audience_semesters ?? []).map(String));
    setAudienceCheckboxes('topicAudienceSections', 'audience_sections[]', topic?.audience_sections ?? []);
    filterTopicAudienceSections();
    populateParentOptions(subjectId, topic?.id ?? null, topic?.parent_id ?? parentId);
    document.getElementById('topicModal').classList.add('open');
}

function setAudienceCheckboxes(containerId, inputName, selectedValues) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.querySelectorAll(`input[name="${inputName}"]`).forEach(cb => {
        cb.checked = selectedValues.includes(cb.value);
    });
}

function filterTopicAudienceSections() {
    const checkedYears = Array.from(document.querySelectorAll('#topicAudienceYears input:checked')).map(cb => cb.value);
    const chips = document.querySelectorAll('#topicAudienceSections .audience-chip');
    chips.forEach(chip => {
        const chipYear = chip.dataset.year || '';
        const visible = checkedYears.length === 0 || chipYear === '' || checkedYears.includes(chipYear);
        chip.style.display = visible ? '' : 'none';
        if (!visible) {
            const cb = chip.querySelector('input');
            if (cb.checked) cb.checked = false;
        }
    });
    document.getElementById('topicAudienceSectionsHint').style.display = checkedYears.length === 0 ? '' : 'none';
}

function toggleTopicChildren(button) {
    button.classList.toggle('open');
    const node = button.closest('.topic-node');
    const children = node.querySelector(':scope > .topic-children');
    if (children) children.hidden = !children.hidden;
}

function askThenSubmit(action, method, dialog) {
    CPACE.confirm(dialog).then(ok => {
        if (!ok) return;
        const f = document.getElementById('confirmForm');
        f.action = action;
        document.getElementById('confirmMethod').value = method;
        f.submit();
    });
}

function openTopicDelete(subjectId, topicId, topicName) {
    askThenSubmit(`${FACULTY_TOPICS_BASE}/${subjectId}/${topicId}`, 'DELETE', {
        title: 'Remove this topic?',
        html: `"${topicName}" will be removed from this subject.`
            + '<div class="cpace-note">Topics containing questions or subtopics are protected and cannot be removed &mdash; mark them inactive instead.</div>',
        confirmText: 'Yes, remove topic',
        danger: true,
    });
}

function openTopicToggle(subjectId, topicId, topicName, makeActive) {
    askThenSubmit(`${FACULTY_TOPICS_BASE}/${subjectId}/${topicId}/toggle`, 'PATCH', {
        title: (makeActive ? 'Enable' : 'Disable') + ' this topic?',
        html: makeActive
            ? `"${topicName}" will become visible and available for questions and quizzes.`
            : `"${topicName}" will be hidden. Existing questions and subtopics stay, but won't appear in quizzes.`,
        icon: makeActive ? 'question' : 'warning',
        confirmText: makeActive ? 'Yes, enable it' : 'Yes, disable it',
        danger: !makeActive,
    });
}

function toggleAllTopics(subjectId, checked) {
    document.querySelectorAll(`#topics-data-${subjectId} .topic-check`).forEach(cb => cb.checked = checked);
    updateTopicBulkBar(subjectId);
}

function updateTopicBulkBar(subjectId) {
    const all = document.querySelectorAll(`#topics-data-${subjectId} .topic-check`);
    const checked = document.querySelectorAll(`#topics-data-${subjectId} .topic-check:checked`);
    const btn = document.getElementById(`topicBulkDeleteBtn-${subjectId}`);
    const count = document.getElementById(`topicBulkCount-${subjectId}`);
    if (btn) btn.style.display = checked.length ? '' : 'none';
    if (count) count.textContent = checked.length;
    const selectAll = document.getElementById(`topicSelectAll-${subjectId}`);
    if (selectAll) selectAll.checked = all.length > 0 && checked.length === all.length;
}

function openBulkTopicDelete(subjectId, subjectCode) {
    const ids = Array.from(document.querySelectorAll(`#topics-data-${subjectId} .topic-check:checked`)).map(cb => cb.value);
    if (!ids.length) return;

    CPACE.confirm({
        title: `Remove ${ids.length} topic${ids.length === 1 ? '' : 's'}?`,
        html: `Selected topics will be removed from ${subjectCode}.`
            + '<div class="cpace-note">Topics containing questions or subtopics are protected and will be skipped.</div>',
        confirmText: `Yes, remove ${ids.length === 1 ? 'it' : 'them'}`,
        danger: true,
    }).then(ok => {
        if (!ok) return;
        const f = document.getElementById('bulkTopicDeleteForm');
        f.action = `${FACULTY_TOPICS_BASE}/${subjectId}/bulk-destroy`;
        f.querySelectorAll('input[name="topic_ids[]"]').forEach(el => el.remove());
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'topic_ids[]';
            input.value = id;
            f.appendChild(input);
        });
        f.submit();
    });
}

function applyFacultyTopicSearch(node, query) {
    const row = node.querySelector(':scope > .topic-row');
    const nameEl = row.querySelector('.topic-name');
    const selfMatch = nameEl.textContent.toLowerCase().includes(query);

    const childrenContainer = node.querySelector(':scope > .topic-children');
    let childMatch = false;
    if (childrenContainer) {
        childrenContainer.querySelectorAll(':scope > .topic-node').forEach(child => {
            if (applyFacultyTopicSearch(child, query)) childMatch = true;
        });
    }

    const visible = selfMatch || childMatch;
    node.style.display = visible ? '' : 'none';

    if (childrenContainer) {
        childrenContainer.hidden = !childMatch;
        const toggle = row.querySelector('.topic-toggle');
        if (toggle) toggle.classList.toggle('open', childMatch);
    }

    return visible;
}

function searchFacultyTopics(input, rawQuery) {
    const query = rawQuery.trim().toLowerCase();
    const list = input.closest('.card').querySelector('.topic-list');
    if (!list) return;
    const topLevelNodes = list.querySelectorAll(':scope > .topic-node');

    if (!query) {
        list.querySelectorAll('.topic-node').forEach(node => { node.style.display = ''; });
        list.querySelectorAll('.topic-children').forEach(children => { children.hidden = true; });
        list.querySelectorAll('.topic-toggle').forEach(toggle => toggle.classList.remove('open'));
        return;
    }

    topLevelNodes.forEach(node => applyFacultyTopicSearch(node, query));
}

function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(modal => modal.addEventListener('click', event => { if (event.target === modal) modal.classList.remove('open'); }));
document.addEventListener('keydown', event => { if (event.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(modal => modal.classList.remove('open')); });
</script>

@include('partials.alerts')
</body>
</html>
