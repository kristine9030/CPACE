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
        /* ── KPI cards — same treatment as the Program Chair Dashboard and
           Student Management: a darker, more pronounced shadow than the rest
           of the page's cards, a bold dark title (was a faint 10.5px gray
           label), an inline unit next to the number, and a dashed-border
           context line underneath explaining what the number means. ── */
        .stats-row .stat-card {
            display:flex; flex-direction:column; height:100%;
            box-shadow:0 4px 10px rgba(10,5,5,.14), 0 16px 32px -8px rgba(10,5,5,.34);
            transition:transform .18s ease, box-shadow .18s ease;
        }
        .stats-row .stat-card:hover {
            transform:translateY(-2px);
            box-shadow:0 6px 14px rgba(10,5,5,.18), 0 22px 40px -8px rgba(10,5,5,.4);
        }
        .stats-row .stat-top { flex:1; }
        .stats-row .stat-icon { width:52px; height:52px; border-radius:13px; font-size:24px; flex-shrink:0; }
        .stats-row .stat-lbl { font-size:13.5px; font-weight:700; color:#1a1a1a; margin-bottom:6px; letter-spacing:-.01em; }
        .stat-unit { font-size:12px; font-weight:600; color:#aaa; vertical-align:middle; margin-left:2px; }
        .stat-context {
            font-size:10.5px; color:#999; margin-top:12px;
            padding-top:10px; border-top:1px dashed #eee; line-height:1.4;
        }
        .stat-context strong { color:#1a1a1a; font-weight:700; }
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
        /* ── Collapsible topics panel ── */
        .topics-tab { cursor:pointer;user-select:none;margin-bottom:0;padding:2px 0;border-radius:6px; }
        .topics-tab:hover .section-label { color:var(--primary); }
        .topics-tab:focus-visible { outline:2px solid var(--primary);outline-offset:3px; }
        .topics-tab .section-label { display:inline-flex;align-items:center;gap:7px;transition:color .15s; }
        .topics-caret { font-size:9px;color:#bbb;transition:transform .2s ease; }
        .topics-tab[aria-expanded="true"] .topics-caret { transform:rotate(90deg);color:var(--primary); }
        .topics-count { display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 5px;border-radius:9px;background:#f3f4f6;color:#6b7280;font-size:9.5px;font-weight:700;letter-spacing:0; }
        .topics-tab[aria-expanded="true"] .topics-count { background:var(--primary-light);color:var(--primary); }
        /* 0fr → 1fr animates to the content's natural height without hardcoding one. */
        .topics-panel { display:grid;grid-template-rows:0fr;transition:grid-template-rows .26s ease; }
        .topics-panel > .topics-panel-inner { overflow:hidden;min-height:0; }
        .topics-tab[aria-expanded="true"] + .topics-panel { grid-template-rows:1fr; }
        .topics-panel-inner > *:first-child { padding-top:9px; }
        /* Progressive reveal: root topics fade in one after another as the panel opens. */
        .topics-panel.revealing .topic-list > .topic-node { opacity:0;transform:translateY(-4px);animation:topicIn .22s ease forwards; }
        @keyframes topicIn { to { opacity:1;transform:none; } }
        @media (prefers-reduced-motion:reduce) {
            .topics-panel { transition:none; }
            .topics-panel.revealing .topic-list > .topic-node { animation:none;opacity:1;transform:none; }
            .topics-caret { transition:none; }
        }
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
        @media(max-width:1050px) { .stats-row { grid-template-columns:repeat(2,1fr); } }
        @media(max-width:900px) { .subj-grid { grid-template-columns:1fr; } }
        @media(max-width:620px) { .modal-grid { grid-template-columns:1fr; }.full { grid-column:auto; }.sc-top,.sc-section { padding-left:14px;padding-right:14px; } }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'subjects'])

@php
    $subjectsColl = collect($subjects);
    $totalSubjects = $subjectsColl->count();
    $assigned = $subjectsColl->filter(fn($subject) => $subject->faculty->isNotEmpty())->count();
    $topicCount = $subjectsColl->sum(fn($subject) => $subject->topics->count());
    $inactiveCount = $subjectsColl->where('is_active', false)->count();

    // Context figures for the KPI cards — each number gets a line underneath
    // saying what it means, so a bare count is never left to interpretation.
    $activeCount = $totalSubjects - $inactiveCount;
    $unassigned = $totalSubjects - $assigned;
    $emptySubjects = $subjectsColl->filter(fn($subject) => $subject->topics->isEmpty())->count();
    $avgTopics = $totalSubjects > 0 ? round($topicCount / $totalSubjects, 1) : 0;
@endphp

<main class="main">
    <div class="topbar">
        <div class="topbar-left"><div><div class="page-title">Subject & Curriculum</div><div class="page-sub">Manage CPALE subjects, faculty coverage, topics, and readiness thresholds.</div></div></div>
        <div class="topbar-right">
            <button type="button" class="btn btn-primary" onclick="openSubject()"><i class="fas fa-plus"></i> Add Subject</button>
            @include('partials.topbar-actions')
        </div>
    </div>

    {{-- Status and validation messages surface as SweetAlert popups via partials.alerts --}}

    @php
        $summaryCards = [
            [
                'value' => $totalSubjects, 'unit' => 'Subjects', 'label' => 'CPALE Subjects',
                'tone' => 'si-blue', 'icon' => 'fa-layer-group',
                'context' => $totalSubjects > 0
                    ? '<strong>' . $activeCount . ' active</strong> in the curriculum right now.'
                    : 'No subjects defined yet — add one to begin.',
            ],
            [
                'value' => $assigned, 'unit' => 'Covered', 'label' => 'Faculty Coverage',
                'tone' => 'si-green', 'icon' => 'fa-chalkboard-user',
                'context' => $unassigned > 0
                    ? '<strong style="color:var(--accent);">' . $unassigned . ' ' . ($unassigned === 1 ? 'subject' : 'subjects') . '</strong> still need a faculty assignment.'
                    : '<strong style="color:#059669;">Every subject</strong> has an assigned faculty.',
            ],
            [
                'value' => $topicCount, 'unit' => 'Topics', 'label' => 'Curriculum Topics',
                'tone' => 'si-orange', 'icon' => 'fa-list-check',
                'context' => $emptySubjects > 0
                    ? '<strong style="color:var(--accent);">' . $emptySubjects . ' ' . ($emptySubjects === 1 ? 'subject has' : 'subjects have') . '</strong> no topics mapped yet.'
                    : '<strong>' . $avgTopics . ' topics</strong> per subject on average.',
            ],
            [
                'value' => $inactiveCount, 'unit' => 'Hidden', 'label' => 'Inactive Subjects',
                'tone' => 'si-red', 'icon' => 'fa-eye-slash',
                'context' => $inactiveCount > 0
                    ? '<strong style="color:var(--accent);">' . $inactiveCount . ' ' . ($inactiveCount === 1 ? 'subject is' : 'subjects are') . '</strong> hidden from students.'
                    : '<strong style="color:#059669;">All subjects</strong> are visible to students.',
            ],
        ];
    @endphp
    <div class="stats-row">
        @foreach ($summaryCards as $card)
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-lbl">{{ $card['label'] }}</div>
                        <div class="stat-num">{{ $card['value'] }} <span class="stat-unit">{{ $card['unit'] }}</span></div>
                    </div>
                    <div class="stat-icon {{ $card['tone'] }}">
                        <i class="fas {{ $card['icon'] }}"></i>
                    </div>
                </div>
                <div class="stat-context">{!! $card['context'] !!}</div>
            </div>
        @endforeach
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

                {{-- Topics collapse: the curriculum tree is the tallest part of
                     a subject card, so it stays folded behind its own header and
                     opens on click. The header doubles as the toggle; "Add Topic"
                     sits inside it but stops the click from reaching it. --}}
                <div class="sc-section topics-section">
                    <div class="section-head topics-tab" role="button" tabindex="0"
                         aria-expanded="false" aria-controls="topics-panel-{{ $subject->id }}"
                         onclick="toggleTopicsPanel(this)" onkeydown="topicsTabKey(event, this)">
                        <span class="section-label">
                            <i class="fas fa-chevron-right topics-caret"></i>
                            Topics
                            <span class="topics-count">{{ $subject->topics->count() }}</span>
                        </span>
                        <button type="button" class="add-mini" onclick="event.stopPropagation(); openTopic({{ $subject->id }}, '{{ addslashes($subject->code) }}')"><i class="fas fa-plus"></i> Add Topic</button>
                    </div>

                    <div class="topics-panel" id="topics-panel-{{ $subject->id }}" data-subject="{{ $subject->id }}">
                        <div class="topics-panel-inner">
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
        <form method="POST" id="subjectForm" action="{{ route('chair.subjects.store') }}"
              data-confirm="This subject and its passing threshold will be saved and applied across quizzes and readiness reports."
              data-confirm-title="Save this subject?"
              data-confirm-ok="Yes, save subject"
              data-confirm-icon="question">@csrf <input type="hidden" name="_method" id="subjectMethod" value="POST">
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
        <form method="POST" id="topicForm"
              data-confirm="This topic will be saved to the curriculum and become available to questions and quizzes."
              data-confirm-title="Save this topic?"
              data-confirm-ok="Yes, save topic"
              data-confirm-icon="question">@csrf <input type="hidden" name="_method" id="topicMethod" value="POST">
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

{{-- Carrier form for the destructive curriculum actions; the confirmation itself
     is a SweetAlert dialog raised by openTopicDelete / openSubjectDelete / openTopicToggle. --}}
<form method="POST" id="confirmForm" hidden>@csrf <input type="hidden" name="_method" id="confirmMethod" value="DELETE"></form>

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

/* Opens/closes a subject's topics panel. Root topics are staggered in on open
   so a long curriculum unfolds rather than appearing all at once. The open/closed
   state is remembered per subject, since a chair usually works one subject at a
   time and reloads after every topic edit. */
const TOPICS_OPEN_KEY = 'cpace.chair.subjects.openTopics';

function readOpenTopics() {
    try { return JSON.parse(localStorage.getItem(TOPICS_OPEN_KEY) || '[]') || []; }
    catch (e) { return []; }
}

function rememberTopicsPanel(subjectId, isOpen) {
    try {
        const open = new Set(readOpenTopics().map(String));
        isOpen ? open.add(String(subjectId)) : open.delete(String(subjectId));
        localStorage.setItem(TOPICS_OPEN_KEY, JSON.stringify([...open]));
    } catch (e) { /* private mode or blocked storage — the toggle still works */ }
}

function setTopicsPanel(tab, isOpen, animate = true) {
    const panel = tab.nextElementSibling;
    if (!panel) return;

    tab.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

    const roots = panel.querySelectorAll('.topic-list > .topic-node');
    if (isOpen && animate && roots.length) {
        panel.classList.add('revealing');
        roots.forEach((node, i) => { node.style.animationDelay = (i * 40) + 'ms'; });
    } else if (!isOpen) {
        panel.classList.remove('revealing');
        roots.forEach(node => { node.style.animationDelay = ''; });
    }

    rememberTopicsPanel(panel.dataset.subject, isOpen);
}

function toggleTopicsPanel(tab) {
    setTopicsPanel(tab, tab.getAttribute('aria-expanded') !== 'true');
}

function topicsTabKey(event, tab) {
    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        toggleTopicsPanel(tab);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const open = new Set(readOpenTopics().map(String));
    document.querySelectorAll('.topics-tab').forEach(tab => {
        const panel = tab.nextElementSibling;
        if (panel && open.has(String(panel.dataset.subject))) {
            setTopicsPanel(tab, true, false);
        }
    });
});

function toggleTopicChildren(button) {
    button.classList.toggle('open');
    const node = button.closest('.topic-node');
    const children = node.querySelector(':scope > .topic-children');
    if (children) children.hidden = !children.hidden;
}

/* Points the carrier form at `action`/`method`, then submits it once the
   chair confirms the dialog. */
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
    askThenSubmit(`/chair/subjects/${subjectId}/topics/${topicId}`, 'DELETE', {
        title: 'Remove this topic?',
        html: `"${topicName}" will be removed from this subject.`
            + '<div class="cpace-note">Topics containing questions or subtopics are protected and cannot be removed &mdash; mark them inactive instead.</div>',
        confirmText: 'Yes, remove topic',
        danger: true,
    });
}

function openSubjectDelete(subjectId, subjectCode) {
    askThenSubmit(`/chair/subjects/${subjectId}`, 'DELETE', {
        title: 'Remove this subject?',
        html: `"${subjectCode}" will be removed from the curriculum.`
            + '<div class="cpace-note">Subjects containing topics or assigned faculty cannot be removed. Unlink those first, or mark the subject inactive.</div>',
        confirmText: 'Yes, remove subject',
        danger: true,
    });
}

function openTopicToggle(subjectId, topicId, topicName, makeActive) {
    askThenSubmit(`/chair/subjects/${subjectId}/topics/${topicId}/toggle`, 'PATCH', {
        title: (makeActive ? 'Enable' : 'Disable') + ' this topic?',
        html: makeActive
            ? `"${topicName}" will become visible and available for questions and quizzes.`
            : `"${topicName}" will be hidden. Existing questions and subtopics stay, but won't appear in quizzes.`,
        icon: makeActive ? 'question' : 'warning',
        confirmText: makeActive ? 'Yes, enable it' : 'Yes, disable it',
        danger: !makeActive,
    });
}

function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(modal => modal.addEventListener('click', event => { if (event.target === modal) modal.classList.remove('open'); }));
document.addEventListener('keydown', event => { if (event.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(modal => modal.classList.remove('open')); });
</script>

    @include('partials.alerts')
</body>
</html>
