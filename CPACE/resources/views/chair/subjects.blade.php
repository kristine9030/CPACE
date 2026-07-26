<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject & Curriculum Management - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .summary-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px; }
        .sum-card { background:#fff; border-radius:12px; padding:16px 18px; display:flex; align-items:center; gap:12px; border:1px solid #ebebeb; }
        .sum-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; }
        .sum-icon.total { background:#f3f4f6;color:#6b7280; } .sum-icon.covered { background:#d1fae5;color:#059669; }
        .sum-icon.topics { background:#dbeafe;color:#2563eb; } .sum-icon.inactive { background:#fef3c7;color:#d97706; }
        .sum-num { font-size:22px;font-weight:700;color:#1a1a1a;line-height:1; } .sum-lbl { font-size:10.5px;color:#9ca3af;margin-top:3px; }
        .subj-grid { display:grid;grid-template-columns:repeat(2,1fr);gap:16px; }
        .subj-card { background:#fff;border-radius:14px;border:1px solid #e8e8e8;overflow:hidden; }
        .subj-card.inactive { opacity:.72; }
        .sc-top { display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:18px 20px 14px;border-left:5px solid var(--subject-color); }
        .sc-code { font-size:20px;font-weight:800;color:var(--subject-color);line-height:1; }
        .sc-name { font-size:11.5px;color:#777;margin-top:4px; } .sc-desc { font-size:10.5px;color:#aaa;margin-top:6px;line-height:1.5; }
        .sc-actions { display:flex;align-items:center;gap:6px; }
        .icon-btn { width:30px;height:30px;border:0;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;font-size:11px;text-decoration:none; }
        .ib-edit { background:#dbeafe;color:#2563eb; } .ib-delete { background:#fde8e8;color:#b91c1c; }
        .threshold { display:inline-flex;align-items:center;gap:5px;padding:4px 9px;border-radius:15px;background:#fef3c7;color:#b45309;font-size:10px;font-weight:700;margin-top:9px; }
        .inactive-pill { display:inline-flex;padding:3px 8px;border-radius:12px;background:#f3f4f6;color:#6b7280;font-size:9px;font-weight:700;margin-left:5px; }
        .sc-section { padding:13px 20px;border-top:1px solid #f3f4f6; }
        .section-head { display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:9px; }
        .section-label { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#aaa; }
        .add-mini { border:0;background:var(--primary-light);color:var(--primary);border-radius:7px;padding:5px 9px;font:600 10px 'Poppins',sans-serif;cursor:pointer; }
        .fac-chip { display:inline-flex;align-items:center;gap:7px;padding:6px 10px;border-radius:8px;font-size:11px;font-weight:500;color:#374151;background:#f9fafb;border:1px solid #e5e7eb;margin:3px 4px 3px 0; }
        .fac-av { width:19px;height:19px;border-radius:5px;display:flex;align-items:center;justify-content:center;font-size:8px;font-weight:700;color:#fff;background:var(--subject-color); }
        .topic-list { display:flex;flex-direction:column;gap:6px; }
        .topic-node + .topic-node { margin-top:6px; }
        .topic-row { display:flex;align-items:center;gap:9px;padding:8px 9px;border-radius:8px;background:#fafafa;border:1px solid #f0f0f0; }
        .topic-toggle { width:18px;height:18px;flex-shrink:0;border:0;background:none;color:#999;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:9px;padding:0; }
        .topic-toggle.open i { transform:rotate(90deg); }
        .topic-toggle i { transition:transform .15s; }
        .topic-toggle-spacer { width:18px;flex-shrink:0; }
        .topic-order { width:22px;height:22px;flex-shrink:0;border-radius:6px;background:#fff;color:#999;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700; }
        .topic-info { flex:1;min-width:0; } .topic-name { font-size:11px;font-weight:600;color:#333;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
        .topic-meta { font-size:9.5px;color:#aaa;margin-top:2px; } .topic-actions { display:flex;gap:4px; }
        .topic-actions .icon-btn { width:25px;height:25px;font-size:9px; }
        .topic-children { margin-top:6px; }
        .topic-search { position:relative; margin-bottom:9px; }
        .topic-search i { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#bbb; font-size:10px; }
        .topic-search input { width:100%; padding:8px 10px 8px 30px; border:1.5px solid #e2e2e6; border-radius:8px; font:11px 'Poppins',sans-serif; }
        .topic-search input:focus { outline:none; border-color:var(--primary); }
        .empty-msg { font-size:11px;color:#bbb;display:flex;align-items:center;gap:7px;padding:4px 0; }
        .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:2000;align-items:center;justify-content:center;padding:20px; }
        .modal-overlay.open { display:flex; } .modal { background:#fff;border-radius:16px;width:100%;max-width:560px;padding:24px;max-height:90vh;overflow-y:auto; }
        .modal h3 { font-size:16px;color:#1a1a1a;margin-bottom:4px; } .modal-sub { font-size:11px;color:#999;margin-bottom:18px; }
        .modal-grid { display:grid;grid-template-columns:1fr 1fr;gap:14px; } .full { grid-column:1/-1; }
        textarea { width:100%;padding:10px 12px;border:1.5px solid #e2e2e6;border-radius:8px;font:13px 'Poppins',sans-serif;resize:vertical;min-height:78px; }
        input[type=number], input[type=color], select { width:100%;padding:10px 12px;border:1.5px solid #e2e2e6;border-radius:8px;font:13px 'Poppins',sans-serif;background:#fff; }
        input[type=color] { height:42px;padding:4px;cursor:pointer; } textarea:focus,input:focus { outline:none;border-color:var(--primary); }
        .toggle-row { display:flex;align-items:center;gap:9px;padding:10px 12px;background:#f8f8fa;border-radius:8px; }
        .toggle-row input { width:17px;height:17px;accent-color:var(--primary); } .toggle-row label { margin:0;font-size:11px; }
        .modal-actions { display:flex;justify-content:flex-end;gap:9px;margin-top:20px; }
        .btn-danger { border:none;background:#b91c1c;color:#fff;padding:9px 16px;border-radius:8px;font:600 12px Poppins,sans-serif;cursor:pointer;display:inline-flex;align-items:center;gap:6px; }
        .btn-danger:hover { background:#991b1b; }
        .btn-warning { border:none;background:#d97706;color:#fff;padding:9px 16px;border-radius:8px;font:600 12px Poppins,sans-serif;cursor:pointer;display:inline-flex;align-items:center;gap:6px; }
        .btn-warning:hover { background:#b45309; }
        .btn-success { border:none;background:#059669;color:#fff;padding:9px 16px;border-radius:8px;font:600 12px Poppins,sans-serif;cursor:pointer;display:inline-flex;align-items:center;gap:6px; }
        .btn-success:hover { background:#047857; }
        .ib-warning { background:#fef3c7;color:#d97706; } .ib-success { background:#d1fae5;color:#059669; } .ib-muted { background:#f3f4f6;color:#9ca3af; }
        .del-warn { margin:14px 0;padding:12px;background:#fef3c7;border-radius:8px;font-size:11.5px;color:#92400e;display:flex;align-items:flex-start;gap:8px; }
        .del-warn i { margin-top:1px;flex-shrink:0; }
        .info-note { margin:14px 0;padding:12px;background:#eff6ff;border-radius:8px;font-size:11.5px;color:#1e40af;display:flex;align-items:flex-start;gap:8px; }
        .info-note i { margin-top:1px;flex-shrink:0; }
        @media(max-width:1050px) { .summary-row { grid-template-columns:repeat(2,1fr); } }
        @media(max-width:900px) { .subj-grid { grid-template-columns:1fr; } }
        @media(max-width:620px) { .summary-row { grid-template-columns:1fr; }.modal-grid { grid-template-columns:1fr; }.full { grid-column:auto; }.sc-top,.sc-section { padding-left:14px;padding-right:14px; } }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'subjects'])

@php
    $subjectsColl = collect($subjects);
    $assigned = $subjectsColl->filter(fn($subject) => $subject->faculty->isNotEmpty())->count();
    $topicCount = $subjectsColl->sum(fn($subject) => $subject->topics->count());
    $inactiveCount = $subjectsColl->where('is_active', false)->count();
@endphp

<main class="main">
    <div class="topbar">
        <div class="topbar-left"><div><div class="page-title">Subject & Curriculum</div><div class="page-sub">Manage CPALE subjects, faculty coverage, topics, and readiness thresholds.</div></div></div>
        <div class="topbar-right">
            <button type="button" class="btn btn-primary" onclick="openSubject()"><i class="fas fa-plus"></i> Add Subject</button>
            @include('partials.topbar-actions')
        </div>
    </div>

    @if(session('status'))<div class="alert alert-success"><i class="fas fa-check-circle"></i>{{ session('status') }}</div>@endif
    @if(session('error'))<div class="alert alert-error"><i class="fas fa-triangle-exclamation"></i>{{ session('error') }}</div>@endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-error"><i class="fas fa-circle-exclamation"></i><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="summary-row">
        <div class="sum-card"><div class="sum-icon total"><i class="fas fa-layer-group"></i></div><div><div class="sum-num">{{ $subjectsColl->count() }}</div><div class="sum-lbl">Total Subjects</div></div></div>
        <div class="sum-card"><div class="sum-icon covered"><i class="fas fa-chalkboard-user"></i></div><div><div class="sum-num">{{ $assigned }}</div><div class="sum-lbl">With Assigned Faculty</div></div></div>
        <div class="sum-card"><div class="sum-icon topics"><i class="fas fa-list-check"></i></div><div><div class="sum-num">{{ $topicCount }}</div><div class="sum-lbl">Curriculum Topics</div></div></div>
        <div class="sum-card"><div class="sum-icon inactive"><i class="fas fa-eye-slash"></i></div><div><div class="sum-num">{{ $inactiveCount }}</div><div class="sum-lbl">Inactive Subjects</div></div></div>
    </div>

    <div class="subj-grid">
        @forelse($subjects as $subject)
            @php $color = $subject->color ?: '#7B1D1D'; @endphp
            <div class="subj-card {{ $subject->is_active ? '' : 'inactive' }}" style="--subject-color:{{ $color }};">
                <div class="sc-top">
                    <div style="flex:1;min-width:0;">
                        <div class="sc-code">{{ $subject->code }} @unless($subject->is_active)<span class="inactive-pill">Inactive</span>@endunless</div>
                        <div class="sc-name">{{ $subject->name }}</div>
                        @if($subject->description)<div class="sc-desc">{{ Str::limit($subject->description, 130) }}</div>@endif
                        <span class="threshold"><i class="fas fa-bullseye"></i> Passing threshold: {{ $subject->passing_threshold }}%</span>
                    </div>
                    <div class="sc-actions">
                        <button type="button" class="icon-btn ib-edit" title="Edit subject" onclick="openSubject({{ Illuminate\Support\Js::from(['id'=>$subject->id,'code'=>$subject->code,'name'=>$subject->name,'description'=>$subject->description,'passing_threshold'=>$subject->passing_threshold,'color'=>$color,'is_active'=>$subject->is_active]) }})"><i class="fas fa-pen"></i></button>
                        <button type="button" class="icon-btn ib-delete" title="Remove subject" onclick="openSubjectDelete({{ $subject->id }}, '{{ addslashes($subject->code) }}')"><i class="fas fa-trash"></i></button>
                    </div>
                </div>

                <div class="sc-section">
                    <div class="section-head"><span class="section-label">Assigned Faculty ({{ $subject->faculty->count() }})</span><a href="{{ route('chair.faculty') }}" class="add-mini" style="text-decoration:none;"><i class="fas fa-layer-group"></i> Assign</a></div>
                    @forelse($subject->faculty as $member)
                        <span class="fac-chip"><span class="fac-av">{{ strtoupper(substr($member->first_name,0,1).substr($member->last_name,0,1)) }}</span>{{ $member->name }}</span>
                    @empty
                        <div class="empty-msg"><i class="fas fa-user-slash"></i>No faculty assigned yet.</div>
                    @endforelse
                </div>

                <div class="sc-section">
                    <div class="section-head"><span class="section-label">Topics ({{ $subject->topics->count() }})</span><button type="button" class="add-mini" onclick="openTopic({{ $subject->id }}, '{{ addslashes($subject->code) }}')"><i class="fas fa-plus"></i> Add Topic</button></div>
                    @if($subject->topicTree->isNotEmpty())
                        <div class="topic-search">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="text" placeholder="Search topics..." oninput="searchChairTopics(this, this.value)">
                        </div>
                    @endif
                    <div class="topic-list" id="topics-data-{{ $subject->id }}" data-topics="{{ json_encode($subject->topics->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'parent_id' => $t->parent_id])) }}">
                        @if($subject->topicTree->isNotEmpty())
                            @include('chair.partials.topic-node', ['subject' => $subject, 'topics' => $subject->topicTree, 'depth' => 0])
                        @else
                            <div class="empty-msg"><i class="fas fa-list"></i>No topics added yet.</div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card" style="grid-column:1/-1;"><div class="empty"><i class="fas fa-layer-group"></i><div>No subjects yet. Add the first subject to begin.</div></div></div>
        @endforelse
    </div>
</main>

<div class="modal-overlay" id="subjectModal">
    <div class="modal">
        <h3 id="subjectModalTitle">Add Subject</h3><div class="modal-sub">Configure the subject and the score students need for readiness.</div>
        <form method="POST" id="subjectForm" action="{{ route('chair.subjects.store') }}">@csrf <input type="hidden" name="_method" id="subjectMethod" value="POST">
            <div class="modal-grid">
                <div class="form-group"><label>Subject Code</label><input type="text" name="code" maxlength="20" required placeholder="e.g. FAR"></div>
                <div class="form-group"><label>Subject Name</label><input type="text" name="name" required placeholder="Full subject name"></div>
                <div class="form-group"><label>Passing Threshold (%)</label><input type="number" name="passing_threshold" min="1" max="100" value="75" required><div class="hint">Used for quiz results and readiness status.</div></div>
                <div class="form-group"><label>Display Color</label><input type="color" name="color" value="#7B1D1D"></div>
                <div class="form-group full"><label>Description</label><textarea name="description" placeholder="Optional subject description"></textarea></div>
                <div class="toggle-row full"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" id="subjectActive" value="1" checked><label for="subjectActive">Active and available throughout the system</label></div>
            </div>
            <div class="modal-actions"><button type="button" class="btn btn-ghost" onclick="closeModal('subjectModal')">Cancel</button><button class="btn btn-primary"><i class="fas fa-save"></i> Save Subject</button></div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="topicModal">
    <div class="modal">
        <h3 id="topicModalTitle">Add Topic</h3><div class="modal-sub" id="topicModalSub">Add a curriculum topic to this subject.</div>
        <form method="POST" id="topicForm">@csrf <input type="hidden" name="_method" id="topicMethod" value="POST">
            <div class="modal-grid">
                <div class="form-group"><label>Topic Name</label><input type="text" name="name" required placeholder="Topic name"></div>
                <div class="form-group"><label>Display Order</label><input type="number" name="sort_order" min="0" max="9999" value="0" required></div>
                <div class="form-group full"><label>Parent Topic</label><select name="parent_id" id="topicParent"><option value="">— None (top-level topic) —</option></select><div class="hint">Choose an existing topic to make this a subtopic of it.</div></div>
                <div class="form-group full"><label>Description</label><textarea name="description" placeholder="Optional topic description"></textarea></div>
                <div class="toggle-row full"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" id="topicActive" value="1" checked><label for="topicActive">Active and available for questions and quizzes</label></div>
            </div>
            <div class="modal-actions"><button type="button" class="btn btn-ghost" onclick="closeModal('topicModal')">Cancel</button><button class="btn btn-primary"><i class="fas fa-save"></i> Save Topic</button></div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="confirmModal" style="z-index:2100;">
    <div class="modal" style="max-width:420px;text-align:center;">
        <div style="font-size:32px;margin-bottom:8px;" id="confirmIcon"><i class="fas fa-triangle-exclamation" style="color:#b91c1c;"></i></div>
        <h3 id="confirmTitle">Remove Topic</h3>
        <div class="modal-sub" id="confirmMsg">Are you sure?</div>
        <div id="confirmNote"></div>
        <form method="POST" id="confirmForm" style="display:inline-block;">@csrf <input type="hidden" name="_method" id="confirmMethod" value="DELETE">
            <div class="modal-actions" style="justify-content:center;">
                <button type="button" class="btn btn-ghost" onclick="closeModal('confirmModal')">Cancel</button>
                <button class="btn btn-danger" id="confirmBtn"><i class="fas fa-trash"></i> Remove</button>
            </div>
        </form>
    </div>
</div>

<script>
function getTopicForm() { return document.getElementById('topicForm'); }

function openSubject(subject = null) {
    const f = document.getElementById('subjectForm');
    if (!f) return;
    document.getElementById('subjectModalTitle').textContent = subject ? 'Edit Subject' : 'Add Subject';
    f.action = subject ? `/chair/subjects/${subject.id}` : @json(route('chair.subjects.store'));
    document.getElementById('subjectMethod').value = subject ? 'PUT' : 'POST';
    if (f.elements) {
        f.elements['code'].value = subject?.code ?? '';
        f.elements['name'].value = subject?.name ?? '';
        f.elements['description'].value = subject?.description ?? '';
        f.elements['passing_threshold'].value = subject?.passing_threshold ?? 75;
        f.elements['color'].value = subject?.color ?? '#7B1D1D';
    }
    document.getElementById('subjectActive').checked = subject ? Boolean(subject.is_active) : true;
    document.getElementById('subjectModal').classList.add('open');
}

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
    f.action = topic ? `/chair/subjects/${subjectId}/topics/${topic.id}` : `/chair/subjects/${subjectId}/topics`;
    document.getElementById('topicMethod').value = topic ? 'PUT' : 'POST';
    if (f.elements) {
        f.elements['name'].value = topic?.name ?? '';
        f.elements['description'].value = topic?.description ?? '';
        f.elements['sort_order'].value = topic?.sort_order ?? 0;
    }
    document.getElementById('topicActive').checked = topic ? Boolean(topic.is_active) : true;
    populateParentOptions(subjectId, topic?.id ?? null, topic?.parent_id ?? parentId);
    document.getElementById('topicModal').classList.add('open');
}

function applyChairTopicSearch(node, query) {
    const row = node.querySelector(':scope > .topic-row');
    const nameEl = row.querySelector('.topic-name');
    const selfMatch = nameEl.textContent.toLowerCase().includes(query);

    const childrenContainer = node.querySelector(':scope > .topic-children');
    let childMatch = false;
    if (childrenContainer) {
        childrenContainer.querySelectorAll(':scope > .topic-node').forEach(child => {
            if (applyChairTopicSearch(child, query)) childMatch = true;
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

function searchChairTopics(input, rawQuery) {
    const query = rawQuery.trim().toLowerCase();
    const list = input.closest('.sc-section').querySelector('.topic-list');
    if (!list) return;
    const topLevelNodes = list.querySelectorAll(':scope > .topic-node');

    if (!query) {
        list.querySelectorAll('.topic-node').forEach(node => { node.style.display = ''; });
        list.querySelectorAll('.topic-children').forEach(children => { children.hidden = true; });
        list.querySelectorAll('.topic-toggle').forEach(toggle => toggle.classList.remove('open'));
        return;
    }

    topLevelNodes.forEach(node => applyChairTopicSearch(node, query));
}

function toggleTopicChildren(button) {
    button.classList.toggle('open');
    const node = button.closest('.topic-node');
    const children = node.querySelector(':scope > .topic-children');
    if (children) children.hidden = !children.hidden;
}

function openTopicDelete(subjectId, topicId, topicName) {
    document.getElementById('confirmIcon').innerHTML = '<i class="fas fa-triangle-exclamation" style="color:#b91c1c;"></i>';
    document.getElementById('confirmTitle').textContent = 'Remove Topic';
    document.getElementById('confirmMsg').textContent = `Remove "${topicName}" from this subject?`;
    document.getElementById('confirmNote').innerHTML = '<div class="del-warn"><i class="fas fa-shield-halved"></i><span>Topics containing questions or subtopics are protected and cannot be removed. Mark them inactive instead.</span></div>';
    document.getElementById('confirmForm').action = `/chair/subjects/${subjectId}/topics/${topicId}`;
    document.getElementById('confirmMethod').value = 'DELETE';
    document.getElementById('confirmBtn').className = 'btn btn-danger';
    document.getElementById('confirmBtn').innerHTML = '<i class="fas fa-trash"></i> Remove';
    document.getElementById('confirmModal').classList.add('open');
}

function openSubjectDelete(subjectId, subjectCode) {
    document.getElementById('confirmIcon').innerHTML = '<i class="fas fa-triangle-exclamation" style="color:#b91c1c;"></i>';
    document.getElementById('confirmTitle').textContent = 'Remove Subject';
    document.getElementById('confirmMsg').textContent = `Remove "${subjectCode}"? Subjects with topics or assigned faculty are protected.`;
    document.getElementById('confirmNote').innerHTML = '<div class="del-warn"><i class="fas fa-shield-halved"></i><span>Subjects containing topics or assigned faculty cannot be removed. Remove those links first, or mark the subject inactive.</span></div>';
    document.getElementById('confirmForm').action = `/chair/subjects/${subjectId}`;
    document.getElementById('confirmMethod').value = 'DELETE';
    document.getElementById('confirmBtn').className = 'btn btn-danger';
    document.getElementById('confirmBtn').innerHTML = '<i class="fas fa-trash"></i> Remove';
    document.getElementById('confirmModal').classList.add('open');
}

function openTopicToggle(subjectId, topicId, topicName, makeActive) {
    const action = makeActive ? 'Enable' : 'Disable';
    const from = makeActive ? 'inactive' : 'active';
    document.getElementById('confirmIcon').innerHTML = makeActive
        ? '<i class="fas fa-eye" style="color:#059669;"></i>'
        : '<i class="fas fa-eye-slash" style="color:#d97706;"></i>';
    document.getElementById('confirmTitle').textContent = action + ' Topic';
    document.getElementById('confirmMsg').textContent = `${action} "${topicName}"?`;
    document.getElementById('confirmNote').innerHTML = makeActive
        ? '<div class="info-note"><i class="fas fa-info-circle"></i><span>This topic will become visible and available for questions and quizzes.</span></div>'
        : '<div class="del-warn"><i class="fas fa-info-circle"></i><span>This topic will be hidden. Existing questions and subtopics remain but won\'t appear in quizzes.</span></div>';
    document.getElementById('confirmForm').action = `/chair/subjects/${subjectId}/topics/${topicId}/toggle`;
    document.getElementById('confirmMethod').value = 'PATCH';
    document.getElementById('confirmBtn').className = makeActive ? 'btn btn-success' : 'btn btn-warning';
    document.getElementById('confirmBtn').innerHTML = makeActive
        ? '<i class="fas fa-eye"></i> Enable'
        : '<i class="fas fa-eye-slash"></i> Disable';
    document.getElementById('confirmModal').classList.add('open');
}

function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(modal => modal.addEventListener('click', event => { if (event.target === modal) modal.classList.remove('open'); }));
document.addEventListener('keydown', event => { if (event.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(modal => modal.classList.remove('open')); });
</script>
</body>
</html>
