@php
    $editMode = $quiz->exists;
    $initialItems = old('items_json') !== null ? (json_decode(old('items_json'), true) ?: []) : $items;
    $dt = fn ($value) => $value ? $value->format('Y-m-d\TH:i') : '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $editMode ? 'Edit Quiz' : 'New Quiz' }} - CPACE Faculty</title>
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

        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:16px; position:relative; z-index:100; }
        .breadcrumb { display:flex; align-items:center; gap:6px; font-size:12px; color:#aaa; margin-bottom:4px; }
        .breadcrumb a { color:var(--accent); text-decoration:none; }
        .page-title { font-size:26px; font-weight:700; color:#14283E; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:10px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:#fff; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-outline { background:#fff; color:var(--primary); border:1.5px solid var(--primary); }
        .btn-outline:hover { background:var(--primary-light); }
        .btn-ghost { background:#fff; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }
        .btn:disabled { opacity:.5; cursor:not-allowed; }

        .layout { display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start; }
        .card { background:#fff; border-radius:14px; padding:22px 24px; margin-bottom:18px; }
        .card-title { font-size:14px; font-weight:700; color:#1a1a1a; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; gap:10px; }
        .card-title i { color:var(--accent); margin-right:6px; }
        .card-title small { font-weight:500; color:#aaa; font-size:11px; }

        .form-group { margin-bottom:16px; }
        .form-group:last-child { margin-bottom:0; }
        label { display:block; font-size:12px; font-weight:600; color:#555; margin-bottom:6px; }
        label .req { color:var(--accent); }
        .help { font-size:11px; color:#aaa; margin-top:5px; }
        input[type=text], input[type=number], input[type=datetime-local], textarea, select {
            width:100%; font-family:'Poppins',sans-serif; font-size:13px;
            border:1.5px solid #e8e8e8; border-radius:8px; padding:10px 13px;
            color:#333; background:#fff; outline:none; transition:border-color .2s;
        }
        input:focus, textarea:focus, select:focus { border-color:var(--primary); }
        textarea { resize:vertical; min-height:80px; line-height:1.6; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

        .toggle-row { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-top:1px solid #f4f4f4; }
        .toggle-row:first-of-type { border-top:none; }
        .toggle-lbl { font-size:12.5px; color:#444; font-weight:500; }
        .toggle-lbl small { display:block; font-weight:400; color:#aaa; font-size:11px; }
        .toggle { position:relative; width:42px; height:23px; flex-shrink:0; }
        .toggle input { opacity:0; width:0; height:0; }
        .toggle-slider { position:absolute; inset:0; background:#ddd; border-radius:24px; cursor:pointer; transition:.3s; }
        .toggle-slider:before { content:''; position:absolute; width:17px; height:17px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
        .toggle input:checked + .toggle-slider { background:var(--primary); }
        .toggle input:checked + .toggle-slider:before { transform:translateX(19px); }

        /* Lock notice */
        .notice { display:flex; gap:10px; align-items:flex-start; background:#fef3c7; border:1px solid #fcd34d; color:#92400e; border-radius:10px; padding:11px 14px; font-size:12.5px; margin-bottom:16px; line-height:1.5; }
        .notice i { margin-top:2px; }

        /* Question cards */
        .q-list { display:flex; flex-direction:column; gap:14px; }
        .q-card { border:1.5px solid #ececec; border-radius:12px; padding:16px 18px; background:#fcfcfc; transition:border-color .18s; }
        .q-card:focus-within { border-color:#d9b8b8; background:#fff; }
        .q-head { display:flex; align-items:center; gap:10px; margin-bottom:12px; flex-wrap:wrap; }
        .q-num { width:30px; height:30px; border-radius:8px; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; flex-shrink:0; }
        .q-head select, .q-head input[type=number] { width:auto; padding:6px 10px; font-size:12px; }
        .q-head input[type=number] { width:70px; }
        .q-head .spacer { flex:1; }
        .q-src { font-size:10.5px; color:#7c3aed; background:#ede9fe; padding:2px 8px; border-radius:10px; font-weight:600; }
        .icon-btn { width:30px; height:30px; border:none; border-radius:7px; background:#f0f0f0; color:#666; cursor:pointer; font-size:12px; display:inline-flex; align-items:center; justify-content:center; transition:all .15s; }
        .icon-btn:hover { background:#e4e4e4; color:#222; }
        .icon-btn.danger:hover { background:#fde8e8; color:var(--accent); }
        .icon-btn:disabled { opacity:.35; cursor:not-allowed; }
        .q-card textarea { min-height:64px; }
        .choices { display:flex; flex-direction:column; gap:8px; margin-top:12px; }
        .choice-row { display:flex; align-items:center; gap:10px; }
        .choice-letter { width:30px; height:30px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:11.5px; font-weight:700; background:#eee; color:#666; }
        .choice-row.correct .choice-letter { background:#d1fae5; color:#059669; }
        .choice-row input[type=text] { flex:1; padding:8px 12px; }
        .choice-row input[type=radio] { accent-color:var(--green); width:17px; height:17px; cursor:pointer; flex-shrink:0; }
        .choice-hint { font-size:10.5px; color:#bbb; white-space:nowrap; }
        .add-choice { align-self:flex-start; background:none; border:1px dashed #d5d5d5; color:#888; border-radius:8px; padding:6px 12px; font-size:11.5px; cursor:pointer; font-family:'Poppins',sans-serif; }
        .add-choice:hover { border-color:var(--primary); color:var(--primary); }
        .expl { margin-top:12px; }
        .expl input { font-size:12.5px; padding:8px 12px; }

        .add-bar { display:flex; gap:10px; flex-wrap:wrap; margin-top:16px; }
        .q-empty { text-align:center; padding:34px 16px; color:#bbb; font-size:13px; border:1.5px dashed #e3e3e3; border-radius:12px; }
        .q-empty i { font-size:30px; color:#e6d5d5; display:block; margin-bottom:8px; }

        /* Summary side */
        .summary { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px; }
        .sum { background:#f8f8f8; border-radius:10px; padding:12px; text-align:center; }
        .sum b { display:block; font-size:20px; color:#1a1a1a; }
        .sum span { font-size:11px; color:#999; }
        .actions { display:flex; flex-direction:column; gap:10px; }
        .status-pill { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; }
        .sp-draft { background:#f3f4f6; color:#6b7280; }
        .sp-published { background:#d1fae5; color:#059669; }
        .sp-closed { background:#fde8e8; color:var(--accent); }
        .share-box { background:#f8f8f8; border:1px dashed #e0e0e0; border-radius:10px; padding:12px; }
        .share-box .url { font-size:11.5px; color:#444; word-break:break-all; margin-bottom:8px; }
        .share-box .row { display:flex; gap:8px; }
        .share-box .row .btn { flex:1; padding:8px 10px; font-size:12px; }

        /* Manual / Auto pick / All from topics */
        .modes { display:inline-flex; background:#eef0f4; border-radius:10px; padding:3px; gap:3px; margin-bottom:14px; }
        .modes button { border:none; background:transparent; padding:7px 15px; border-radius:8px; cursor:pointer; font-family:'Poppins',sans-serif; font-size:12.5px; font-weight:600; color:#5b6377; }
        .modes button.on { background:#fff; color:var(--primary); box-shadow:0 1px 3px rgba(0,0,0,.12); }
        .pick-panel { border:1px solid #eceef2; border-radius:12px; padding:14px 16px; margin-bottom:14px; background:#fbfbfc; }
        .pick-note { font-size:12px; color:#7a8296; margin-bottom:10px; line-height:1.55; }
        .pick-tools { display:flex; gap:8px; align-items:center; margin-bottom:8px; flex-wrap:wrap; }
        .pick-tools input[type=text] { flex:1; min-width:170px; padding:8px 12px; font-size:12.5px; border-radius:999px; }
        .pick-tools button { border:1px solid #e0e0e0; background:#fff; border-radius:999px; padding:7px 13px; font-size:12px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; color:#444; }
        .pick-tools button:hover { background:#f5f5f5; }
        .topic-list { max-height:260px; overflow-y:auto; border:1px solid #eceef2; border-radius:10px; padding:6px; background:#fff; }
        .topic-row { display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:8px; cursor:pointer; font-size:12.5px; }
        .topic-row:hover { background:#f7f8fa; }
        .topic-row:has(input:checked) { background:var(--primary-light); }
        .topic-row input { width:16px; height:16px; accent-color:var(--primary); flex-shrink:0; }
        .topic-row .tn { flex:1; color:#222; }
        .topic-row .tc { font-size:11px; color:#7a8296; background:#f1f2f5; padding:2px 8px; border-radius:999px; flex-shrink:0; }
        .topic-row .tc.zero { color:#c0392b; background:#fdeceb; }
        .pick-foot { display:flex; align-items:center; gap:12px; margin-top:12px; flex-wrap:wrap; }
        .pick-foot .count-box { display:flex; align-items:center; gap:8px; font-size:12.5px; color:#444; }
        .pick-foot .count-box input { width:80px; padding:8px 10px; font-size:13px; }
        .pick-foot label.rep { display:flex; align-items:center; gap:7px; font-size:12px; color:#666; cursor:pointer; }
        .pick-msg { font-size:12.5px; margin-top:10px; }
        .pick-msg.ok { color:#059669; } .pick-msg.warn { color:#b45309; } .pick-msg.err { color:#c0392b; }
        /* Bank modal */
        .modal-bg { display:none; position:fixed; inset:0; background:rgba(15,5,5,.55); z-index:3000; align-items:center; justify-content:center; padding:20px; }
        .modal-bg.open { display:flex; }
        .modal { background:#fff; border-radius:16px; width:100%; max-width:760px; max-height:88vh; display:flex; flex-direction:column; box-shadow:0 20px 60px rgba(0,0,0,.3); }
        .modal-head { display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid #f0f0f0; }
        .modal-head h3 { font-size:15px; font-weight:700; color:#1a1a1a; }
        .modal-close { width:30px; height:30px; border:none; background:#f4f5f7; border-radius:8px; color:#666; cursor:pointer; }
        .modal-filters { display:flex; gap:10px; padding:12px 20px; border-bottom:1px solid #f4f4f4; }
        .modal-filters input, .modal-filters select { padding:8px 12px; font-size:12.5px; }
        .modal-filters input { flex:1; }
        .modal-filters select { width:auto; }
        .modal-body { flex:1; overflow-y:auto; padding:8px 20px; }
        .bank-row { display:flex; gap:12px; align-items:flex-start; padding:12px 4px; border-bottom:1px solid #f5f5f5; cursor:pointer; }
        .bank-row:hover { background:#fafafa; }
        .bank-row input { margin-top:4px; accent-color:var(--primary); width:16px; height:16px; flex-shrink:0; }
        .bank-row.added { opacity:.45; cursor:default; }
        .bank-q { font-size:13px; color:#222; line-height:1.5; }
        .bank-meta { font-size:11px; color:#aaa; margin-top:3px; display:flex; gap:8px; flex-wrap:wrap; }
        .bank-meta span { background:#f3f4f6; padding:1px 8px; border-radius:10px; }
        .modal-foot { display:flex; justify-content:space-between; align-items:center; padding:14px 20px; border-top:1px solid #f0f0f0; font-size:12.5px; color:#777; }
        .modal-empty { padding:40px; text-align:center; color:#bbb; font-size:13px; }

        @media (max-width:900px) { .main { margin-left:68px; } }
        @media (max-width:768px) { .main { margin-left:0; padding:16px; } .layout { grid-template-columns:1fr; } .form-row { grid-template-columns:1fr; } .topbar { flex-direction:column; align-items:flex-start; } }
    </style>
</head>
<body>

@include('partials.faculty-sidebar', ['active' => 'quizzes'])

@php
    // Land back on the subject's own quiz list when this quiz belongs to
    // one (or was opened from one), not all the way back at the subject grid.
    $backRoute = $quiz->subject_id
        ? route('faculty.quizzes.subject', $quiz->subject_id)
        : route('faculty.quizzes');
@endphp

<main class="main">
    <div class="topbar">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('faculty.quizzes') }}">Class Quizzes</a>
                @if($quiz->subject_id)
                    <i class="fas fa-chevron-right" style="font-size:9px;"></i>
                    <a href="{{ $backRoute }}">{{ $quiz->subject?->code ?? $subjects->firstWhere('id', $quiz->subject_id)?->code }}</a>
                @endif
                <i class="fas fa-chevron-right" style="font-size:9px;"></i> {{ $editMode ? 'Edit' : 'New' }}
            </div>
            <div class="page-title">{{ $editMode ? 'Edit Quiz' : 'New Quiz' }}</div>
            <div class="page-sub">Write questions or pull them from your Test Bank, set the deadline, then save a draft or publish.</div>
        </div>
        <div class="topbar-right">
            @include('partials.topbar-actions')
            <a href="{{ $backRoute }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    <form method="POST" action="{{ $editMode ? route('faculty.quizzes.update', $quiz->id) : route('faculty.quizzes.store') }}" id="quizForm">
        @csrf
        @if($editMode) @method('PUT') @endif
        <input type="hidden" name="action" id="actionInput" value="draft">
        <input type="hidden" name="items_json" id="itemsJson">

        <div class="layout">
            {{-- ── LEFT: details + questions ── --}}
            <div>
                <div class="card">
                    <div class="card-title"><span><i class="fas fa-circle-info"></i>Quiz Details</span></div>
                    <div class="form-group">
                        <label>Title <span class="req">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $quiz->title) }}" placeholder="e.g. FAR Quiz 1 — Revenue Recognition" maxlength="150" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Subject</label>
                            <select name="subject_id">
                                <option value="">— No subject —</option>
                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}" {{ (string) old('subject_id', $quiz->subject_id) === (string) $s->id ? 'selected' : '' }}>{{ $s->code }} — {{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Time limit (minutes)</label>
                            <input type="number" name="time_limit_minutes" min="1" max="600" value="{{ old('time_limit_minutes', $quiz->time_limit_minutes) }}" placeholder="No limit">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Instructions <span style="color:#bbb;font-weight:400;">(optional)</span></label>
                        <textarea name="instructions" placeholder="Anything students should know before they start...">{{ old('instructions', $quiz->instructions) }}</textarea>
                    </div>
                </div>

                <div class="card">
                    <div class="card-title">
                        <span><i class="fas fa-list-ol"></i>Questions <small id="qCountLabel"></small></span>
                        @unless($locked)
                            <div style="display:flex;gap:8px;" id="manualBtns">
                                <button type="button" class="btn btn-outline" id="openBankBtn" style="padding:7px 13px;font-size:12px;"><i class="fas fa-database"></i> Add from Test Bank</button>
                                <button type="button" class="btn btn-primary" id="addQuestionBtn" style="padding:7px 13px;font-size:12px;"><i class="fas fa-plus"></i> Write question</button>
                            </div>
                        @endunless
                    </div>

                    @if($locked)
                        <div class="notice">
                            <i class="fas fa-lock"></i>
                            <div><strong>Questions locked</strong> — students have already answered. You can still edit the title, deadline and settings.<x-tip label="Why it's locked">Changing questions after students answer would make their scores meaningless.</x-tip></div>
                        </div>
                    @endif

                    @unless($locked)
                        <div class="modes" id="qModes">
                            <button type="button" data-mode="manual" class="on">Manual</button>
                            <button type="button" data-mode="auto">Auto pick</button>
                            <button type="button" data-mode="all">All from topics</button>
                        </div>

                        <div class="pick-panel" id="pickPanel" hidden>
                            <div class="pick-note" id="pickNote"></div>
                            <div id="pickNeedSubject" class="pick-msg warn" hidden>Choose a subject in Quiz Details first. Topics come from it.</div>
                            <div id="pickTopics" hidden>
                                <div class="pick-tools">
                                    <input type="text" id="topicSearch" placeholder="Search topics..." autocomplete="off">
                                    <button type="button" id="topicAll">Select all</button>
                                    <button type="button" id="topicNone">Clear</button>
                                </div>
                                <div class="topic-list" id="topicList"><div class="modal-empty">Loading...</div></div>
                                <div style="font-size:11.5px;color:#7a8296;margin-top:7px;"><span id="topicCount">0</span> selected · <span id="topicPool">0</span> questions in the bank for them</div>
                                <div class="pick-foot">
                                    <div class="count-box" id="countBox">How many? <input type="number" id="pickCount" min="1" max="100" value="10"></div>
                                    <label class="rep"><input type="checkbox" id="pickReplace"> Replace the questions already in the list</label>
                                    <button type="button" class="btn btn-primary" id="pickGo" style="padding:8px 15px;font-size:12.5px;margin-left:auto;"><i class="fas fa-wand-magic-sparkles"></i> <span id="pickGoLabel">Pick questions</span></button>
                                </div>
                                <div class="pick-msg" id="pickMsg"></div>
                            </div>
                        </div>
                    @endunless

                    <div class="q-list" id="qList"></div>

                    @unless($locked)
                        <div class="add-bar" id="addBar">
                            <button type="button" class="btn btn-ghost" data-add="mcq"><i class="fas fa-plus"></i> Multiple choice</button>
                            <button type="button" class="btn btn-ghost" data-add="true_false"><i class="fas fa-plus"></i> True / False</button>
                        </div>
                    @endunless
                </div>
            </div>

            {{-- ── RIGHT: schedule, options, actions ── --}}
            <div>
                <div class="card">
                    <div class="card-title"><span><i class="fas fa-calendar-check"></i>Schedule</span></div>
                    <div class="form-group">
                        <label>Deadline</label>
                        <input type="datetime-local" name="due_at" value="{{ old('due_at', $dt($quiz->due_at)) }}">
                        <div class="help">Students can't start or submit after this. Leave blank for no deadline.</div>
                    </div>
                    <div class="form-group">
                        <label>Opens at <span style="color:#bbb;font-weight:400;">(optional)</span></label>
                        <input type="datetime-local" name="opens_at" value="{{ old('opens_at', $dt($quiz->opens_at)) }}">
                        <div class="help">Publish now but only let students start from this time.</div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-title"><span><i class="fas fa-sliders"></i>Options</span></div>
                    <div class="toggle-row">
                        <div class="toggle-lbl">Shuffle questions<small>Each student gets a different order.</small></div>
                        <label class="toggle"><input type="checkbox" name="shuffle_questions" value="1" {{ old('shuffle_questions', $quiz->shuffle_questions) ? 'checked' : '' }}><span class="toggle-slider"></span></label>
                    </div>
                    <div class="toggle-row">
                        <div class="toggle-lbl">Show answers after submitting<small>Students see which items they got right, with explanations.</small></div>
                        <label class="toggle"><input type="checkbox" name="show_results" value="1" {{ old('show_results', $quiz->show_results) ? 'checked' : '' }}><span class="toggle-slider"></span></label>
                    </div>
                    <div class="toggle-row">
                        <div class="toggle-lbl">Monitor students<x-tip label="What monitoring does">Students must share their camera and entire screen. Switching tabs, leaving fullscreen, or an absent or extra face is flagged. Review snapshots on the Monitor page.</x-tip><small>Camera and screen sharing required.</small></div>
                        <label class="toggle"><input type="checkbox" name="monitor_enabled" value="1" {{ old('monitor_enabled', $quiz->monitor_enabled) ? 'checked' : '' }}><span class="toggle-slider"></span></label>
                    </div>
                </div>

                <div class="card">
                    <div class="card-title">
                        <span><i class="fas fa-flag-checkered"></i>Status</span>
                        @php $pillClass = ['draft' => 'sp-draft', 'published' => 'sp-published', 'closed' => 'sp-closed'][$quiz->status ?? 'draft'] ?? 'sp-draft'; @endphp
                        <span class="status-pill {{ $pillClass }}">{{ ucfirst($quiz->status ?? 'draft') }}</span>
                    </div>
                    <div class="summary">
                        <div class="sum"><b id="sumQ">0</b><span>questions</span></div>
                        <div class="sum"><b id="sumP">0</b><span>total points</span></div>
                    </div>

                    @if($editMode && ! $quiz->isDraft())
                        <div class="share-box" style="margin-bottom:14px;">
                            <label style="margin-bottom:6px;"><i class="fas fa-link" style="color:var(--accent);"></i> Quiz link</label>
                            <div class="url" id="shareUrl">{{ $quiz->shareUrl() }}</div>
                            <div class="row">
                                <button type="button" class="btn btn-outline" id="copyLinkBtn"><i class="fas fa-copy"></i> Copy link</button>
                                <a href="{{ $quiz->shareUrl() }}" target="_blank" class="btn btn-ghost"><i class="fas fa-eye"></i> Preview</a>
                            </div>
                        </div>
                    @endif

                    <div class="actions">
                        @if($quiz->isPublished())
                            <button type="button" class="btn btn-primary" data-action="publish"><i class="fas fa-floppy-disk"></i> Save changes</button>
                        @else
                            <button type="button" class="btn btn-primary" data-action="publish"><i class="fas fa-paper-plane"></i> {{ $quiz->status === 'closed' ? 'Publish again' : 'Publish' }}</button>
                            <button type="button" class="btn btn-outline" data-action="draft"><i class="fas fa-floppy-disk"></i> Save as draft</button>
                        @endif
                    </div>
                    <div class="help" style="margin-top:10px;">Publishing makes the quiz available to students through its link and on their Class Quizzes page.</div>
                </div>
            </div>
        </div>
    </form>
</main>

{{-- ── Test Bank picker ── --}}
<div class="modal-bg" id="bankModal">
    <div class="modal">
        <div class="modal-head">
            <h3><i class="fas fa-database" style="color:var(--accent);margin-right:6px;"></i>Add from Test Bank</h3>
            <button type="button" class="modal-close" id="bankClose"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-filters">
            <input type="text" id="bankSearch" placeholder="Search question text...">
            <select id="bankSubject">
                <option value="">All my subjects</option>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}">{{ $s->code }}</option>
                @endforeach
            </select>
        </div>
        <div class="modal-body" id="bankBody"><div class="modal-empty">Loading...</div></div>
        <div class="modal-foot">
            <span id="bankSelectedLabel">0 selected</span>
            <button type="button" class="btn btn-primary" id="bankAddBtn" disabled><i class="fas fa-plus"></i> Add selected</button>
        </div>
    </div>
</div>

<script>
(function () {
    const LOCKED = @json((bool) $locked);
    const BANK_URL = @json(route('faculty.quizzes.bank-questions'));
    let items = @json($initialItems);

    const qList = document.getElementById('qList');
    const itemsJson = document.getElementById('itemsJson');
    const actionInput = document.getElementById('actionInput');
    const form = document.getElementById('quizForm');

    const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    const LETTERS = ['A','B','C','D','E','F'];

    function blankItem(type) {
        return type === 'true_false'
            ? { question_text:'', question_type:'true_false', points:1, explanation:'', source_question_id:null,
                choices:[{label:'T', text:'True', is_correct:true}, {label:'F', text:'False', is_correct:false}] }
            : { question_text:'', question_type:'mcq', points:1, explanation:'', source_question_id:null,
                choices:[{label:'A', text:'', is_correct:true}, {label:'B', text:'', is_correct:false}, {label:'C', text:'', is_correct:false}, {label:'D', text:'', is_correct:false}] };
    }

    function relabel(item) {
        if (item.question_type === 'true_false') {
            item.choices = item.choices.slice(0, 2);
            item.choices[0].label = 'T'; item.choices[1].label = 'F';
        } else {
            item.choices.forEach((c, i) => c.label = LETTERS[i] || String(i + 1));
        }
    }

    function render() {
        if (!items.length) {
            qList.innerHTML = `<div class="q-empty"><i class="fas fa-clipboard-question"></i>${LOCKED ? 'This quiz has no questions.' : 'No questions yet. Write one below or pull some from your Test Bank.'}</div>`;
        } else {
            qList.innerHTML = items.map((it, i) => {
                const tf = it.question_type === 'true_false';
                const choices = it.choices.map((c, j) => `
                    <div class="choice-row ${c.is_correct ? 'correct' : ''}">
                        <input type="radio" name="correct_${i}" data-correct="${j}" ${c.is_correct ? 'checked' : ''} title="Mark as correct answer" ${LOCKED ? 'disabled' : ''}>
                        <div class="choice-letter">${esc(c.label)}</div>
                        <input type="text" data-choice="${j}" value="${esc(c.text)}" placeholder="Choice ${esc(c.label)}" ${tf || LOCKED ? 'readonly' : ''}>
                        ${!tf && !LOCKED && it.choices.length > 2 ? `<button type="button" class="icon-btn danger" data-remove-choice="${j}" title="Remove choice"><i class="fas fa-times"></i></button>` : ''}
                    </div>`).join('');
                return `
                <div class="q-card" data-i="${i}">
                    <div class="q-head">
                        <div class="q-num">${i + 1}</div>
                        <select data-field="question_type" ${LOCKED ? 'disabled' : ''}>
                            <option value="mcq" ${!tf ? 'selected' : ''}>Multiple choice</option>
                            <option value="true_false" ${tf ? 'selected' : ''}>True / False</option>
                        </select>
                        <input type="number" data-field="points" min="1" max="100" value="${it.points || 1}" title="Points" ${LOCKED ? 'readonly' : ''}> <span class="choice-hint">pts</span>
                        ${it.source_question_id ? '<span class="q-src"><i class="fas fa-database"></i> Test Bank</span>' : ''}
                        <span class="spacer"></span>
                        ${LOCKED ? '' : `
                        <button type="button" class="icon-btn" data-move="-1" title="Move up" ${i === 0 ? 'disabled' : ''}><i class="fas fa-arrow-up"></i></button>
                        <button type="button" class="icon-btn" data-move="1" title="Move down" ${i === items.length - 1 ? 'disabled' : ''}><i class="fas fa-arrow-down"></i></button>
                        <button type="button" class="icon-btn danger" data-remove-q title="Remove question"><i class="fas fa-trash"></i></button>`}
                    </div>
                    <textarea data-field="question_text" placeholder="Type the question..." ${LOCKED ? 'readonly' : ''}>${esc(it.question_text)}</textarea>
                    <div class="choices">
                        ${choices}
                        ${!tf && !LOCKED && it.choices.length < 6 ? '<button type="button" class="add-choice" data-add-choice><i class="fas fa-plus"></i> Add choice</button>' : ''}
                    </div>
                    <div class="expl">
                        <input type="text" data-field="explanation" value="${esc(it.explanation)}" placeholder="Explanation shown after submitting (optional)" ${LOCKED ? 'readonly' : ''}>
                    </div>
                </div>`;
            }).join('');
        }
        updateSummary();
    }

    function updateSummary() {
        const pts = items.reduce((a, it) => a + (parseInt(it.points, 10) || 1), 0);
        document.getElementById('sumQ').textContent = items.length;
        document.getElementById('sumP').textContent = pts;
        document.getElementById('qCountLabel').textContent = items.length ? `(${items.length})` : '';
    }

    /* In-place edits: update state only, no re-render (keeps the cursor). */
    qList.addEventListener('input', ev => {
        const card = ev.target.closest('.q-card'); if (!card) return;
        const it = items[+card.dataset.i]; if (!it) return;
        if (ev.target.dataset.field) {
            it[ev.target.dataset.field] = ev.target.dataset.field === 'points' ? (parseInt(ev.target.value, 10) || 1) : ev.target.value;
            if (ev.target.dataset.field === 'points') updateSummary();
        } else if (ev.target.dataset.choice !== undefined) {
            it.choices[+ev.target.dataset.choice].text = ev.target.value;
        }
    });

    /* Structural edits: update state, then re-render. */
    qList.addEventListener('change', ev => {
        const card = ev.target.closest('.q-card'); if (!card) return;
        const i = +card.dataset.i, it = items[i]; if (!it) return;
        if (ev.target.dataset.correct !== undefined) {
            it.choices.forEach((c, j) => c.is_correct = j === +ev.target.dataset.correct);
            render();
        } else if (ev.target.dataset.field === 'question_type') {
            const type = ev.target.value;
            const fresh = blankItem(type);
            it.question_type = type;
            it.choices = type === 'true_false' ? fresh.choices : (it.choices.length >= 2 && it.choices[0].label !== 'T' ? it.choices : fresh.choices);
            relabel(it);
            render();
        }
    });

    qList.addEventListener('click', ev => {
        const btn = ev.target.closest('button'); if (!btn) return;
        const card = btn.closest('.q-card'); if (!card) return;
        const i = +card.dataset.i, it = items[i]; if (!it) return;

        if (btn.hasAttribute('data-remove-q')) {
            items.splice(i, 1); render();
        } else if (btn.dataset.move) {
            const j = i + (+btn.dataset.move);
            if (j < 0 || j >= items.length) return;
            [items[i], items[j]] = [items[j], items[i]]; render();
        } else if (btn.hasAttribute('data-add-choice')) {
            if (it.choices.length >= 6) return;
            it.choices.push({ label:'', text:'', is_correct:false }); relabel(it); render();
            card.querySelector(`[data-choice="${it.choices.length - 1}"]`)?.focus();
        } else if (btn.dataset.removeChoice !== undefined) {
            if (it.choices.length <= 2) return;
            const wasCorrect = it.choices[+btn.dataset.removeChoice].is_correct;
            it.choices.splice(+btn.dataset.removeChoice, 1);
            if (wasCorrect) it.choices[0].is_correct = true;
            relabel(it); render();
        }
    });

    function addQuestion(type) {
        items.push(blankItem(type)); render();
        const last = qList.querySelector('.q-card:last-child textarea');
        if (last) { last.focus(); last.scrollIntoView({ behavior:'smooth', block:'center' }); }
    }
    document.getElementById('addQuestionBtn')?.addEventListener('click', () => addQuestion('mcq'));
    document.querySelectorAll('[data-add]').forEach(b => b.addEventListener('click', () => addQuestion(b.dataset.add)));

    /* ── Save / publish ── */
    document.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const action = btn.dataset.action;
            if (action === 'publish' && !items.length && !LOCKED) {
                (window.CPACE?.error || alert)('Add at least one question before publishing.');
                return;
            }
            if (action === 'publish' && window.CPACE?.confirm) {
                const ok = await CPACE.confirm({
                    title: @json($quiz->isPublished() ? 'Save changes?' : 'Publish this quiz?'),
                    text: @json($quiz->isPublished() ? 'Students will see the updated quiz right away.' : 'Students will be able to open and answer it through the quiz link.'),
                    icon: 'question', confirmText: @json($quiz->isPublished() ? 'Yes, save' : 'Yes, publish'),
                });
                if (!ok) return;
            }
            actionInput.value = action;
            itemsJson.value = JSON.stringify(items.map(it => ({
                question_text: it.question_text, question_type: it.question_type, points: it.points,
                explanation: it.explanation, source_question_id: it.source_question_id,
                choices: it.choices.map(c => ({ label: c.label, text: c.text, is_correct: !!c.is_correct })),
            })));
            form.requestSubmit ? form.requestSubmit() : form.submit();
        });
    });

    /* ── Copy link ── */
    document.getElementById('copyLinkBtn')?.addEventListener('click', function () {
        const url = document.getElementById('shareUrl').textContent.trim();
        const done = () => { const o = this.innerHTML; this.innerHTML = '<i class="fas fa-check"></i> Copied'; setTimeout(() => this.innerHTML = o, 1600); };
        if (navigator.clipboard?.writeText) navigator.clipboard.writeText(url).then(done);
        else { const ta = document.createElement('textarea'); ta.value = url; document.body.appendChild(ta); ta.select(); try { document.execCommand('copy'); } catch (e) {} ta.remove(); done(); }
    });

    /* ── Test Bank picker ── */
    const bankModal = document.getElementById('bankModal');
    const bankBody = document.getElementById('bankBody');
    const bankSearch = document.getElementById('bankSearch');
    const bankSubject = document.getElementById('bankSubject');
    const bankAddBtn = document.getElementById('bankAddBtn');
    const bankSelectedLabel = document.getElementById('bankSelectedLabel');
    let bankResults = [], bankSelected = new Set(), bankTimer = null;

    function addedIds() { return new Set(items.map(it => it.source_question_id).filter(Boolean)); }

    async function loadBank() {
        bankBody.innerHTML = '<div class="modal-empty">Loading...</div>';
        const params = new URLSearchParams({ search: bankSearch.value.trim(), subject: bankSubject.value });
        try {
            const res = await fetch(BANK_URL + '?' + params, { headers: { 'Accept': 'application/json' } });
            bankResults = await res.json();
        } catch (e) { bankResults = []; }
        renderBank();
    }

    function renderBank() {
        const added = addedIds();
        if (!bankResults.length) { bankBody.innerHTML = '<div class="modal-empty">No matching questions in your Test Bank.</div>'; return; }
        bankBody.innerHTML = bankResults.map(q => {
            const isAdded = added.has(q.id);
            return `<label class="bank-row ${isAdded ? 'added' : ''}">
                <input type="checkbox" data-bank-id="${q.id}" ${isAdded ? 'disabled' : ''} ${bankSelected.has(q.id) ? 'checked' : ''}>
                <div>
                    <div class="bank-q">${esc(q.question_text)}</div>
                    <div class="bank-meta"><span>${esc(q.subject)}</span><span>${esc(q.topic)}</span><span>${q.question_type === 'true_false' ? 'True/False' : 'MCQ'}</span><span>${esc(q.difficulty)}</span>${isAdded ? '<span style="color:#059669;">already in quiz</span>' : ''}</div>
                </div>
            </label>`;
        }).join('');
        syncBankFooter();
    }

    function syncBankFooter() {
        bankSelectedLabel.textContent = `${bankSelected.size} selected`;
        bankAddBtn.disabled = bankSelected.size === 0;
    }

    bankBody.addEventListener('change', ev => {
        const cb = ev.target.closest('[data-bank-id]'); if (!cb) return;
        const id = +cb.dataset.bankId;
        cb.checked ? bankSelected.add(id) : bankSelected.delete(id);
        syncBankFooter();
    });

    bankAddBtn.addEventListener('click', () => {
        bankResults.filter(q => bankSelected.has(q.id)).forEach(q => {
            const item = {
                question_text: q.question_text, question_type: q.question_type === 'true_false' ? 'true_false' : 'mcq',
                points: 1, explanation: q.explanation || '', source_question_id: q.id,
                choices: q.choices.map(c => ({ label: c.label, text: c.text, is_correct: !!c.is_correct })),
            };
            relabel(item);
            items.push(item);
        });
        bankSelected.clear();
        render();
        closeBank();
    });

    function openBank() { bankModal.classList.add('open'); bankSelected.clear(); loadBank(); }
    function closeBank() { bankModal.classList.remove('open'); }
    document.getElementById('openBankBtn')?.addEventListener('click', openBank);
    document.getElementById('bankClose').addEventListener('click', closeBank);
    bankModal.addEventListener('click', ev => { if (ev.target === bankModal) closeBank(); });
    bankSearch.addEventListener('input', () => { clearTimeout(bankTimer); bankTimer = setTimeout(loadBank, 350); });
    bankSubject.addEventListener('change', loadBank);

    /* ── Manual / Auto pick / All from topics ── */
    const TOPICS_URL = @json(route('faculty.quizzes.topics'));
    const PICK_URL = @json(route('faculty.quizzes.pick-questions'));
    const CSRF = @json(csrf_token());
    const MAX_ITEMS = 100;
    const qModes = document.getElementById('qModes');
    const pickPanel = document.getElementById('pickPanel');
    const subjectSelect = document.querySelector('select[name=subject_id]');
    let pickMode = 'manual', topicList = [], topicChecked = new Set(), topicsFor = null;

    if (qModes) {
        const $ = id => document.getElementById(id);
        const pickMsg = (text, cls) => { $('pickMsg').textContent = text || ''; $('pickMsg').className = 'pick-msg ' + (cls || ''); };

        function setMode(mode) {
            pickMode = mode;
            qModes.querySelectorAll('button').forEach(b => b.classList.toggle('on', b.dataset.mode === mode));
            const manual = mode === 'manual';
            pickPanel.hidden = manual;
            $('manualBtns').style.display = manual ? '' : 'none';
            $('addBar').style.display = manual ? '' : 'none';
            if (manual) return;
            $('pickNote').textContent = mode === 'auto'
                ? 'Spreads the number you ask for evenly across the topics you tick, aiming for about 30% easy, 50% moderate and 20% difficult.'
                : 'Adds every usable question from the topics you tick, up to ' + MAX_ITEMS + '.';
            $('countBox').style.display = mode === 'auto' ? '' : 'none';
            $('pickGoLabel').textContent = mode === 'auto' ? 'Pick questions' : 'Add all questions';
            pickMsg('');
            ensureTopics();
        }

        async function ensureTopics() {
            const subject = subjectSelect?.value || '';
            $('pickNeedSubject').hidden = !!subject;
            $('pickTopics').hidden = !subject;
            if (!subject || topicsFor === subject) return;
            $('topicList').innerHTML = '<div class="modal-empty">Loading...</div>';
            try {
                const res = await fetch(TOPICS_URL + '?subject=' + encodeURIComponent(subject), { headers: { 'Accept': 'application/json' } });
                topicList = res.ok ? await res.json() : [];
            } catch (e) { topicList = []; }
            topicsFor = subject;
            topicChecked = new Set();
            renderTopics();
        }

        function descendants(id) {
            const out = [], walk = pid => topicList.filter(t => t.parent_id === pid).forEach(t => { out.push(t.id); walk(t.id); });
            walk(id);
            return out;
        }

        function renderTopics() {
            const q = ($('topicSearch').value || '').trim().toLowerCase();
            const rows = topicList.filter(t => !q || t.name.toLowerCase().includes(q));
            $('topicList').innerHTML = rows.length ? rows.map(t => `
                <label class="topic-row" style="padding-left:${10 + t.depth * 20}px;">
                    <input type="checkbox" data-topic="${t.id}" ${topicChecked.has(t.id) ? 'checked' : ''}>
                    <span class="tn">${esc(t.name)}</span>
                    <span class="tc ${t.bank_count ? '' : 'zero'}">${t.bank_count} q</span>
                </label>`).join('') : '<div class="modal-empty">No topics found.</div>';
            $('topicCount').textContent = topicChecked.size;
            $('topicPool').textContent = topicList.filter(t => topicChecked.has(t.id)).reduce((n, t) => n + t.bank_count, 0);
        }

        qModes.addEventListener('click', ev => { const b = ev.target.closest('button[data-mode]'); if (b) setMode(b.dataset.mode); });
        subjectSelect?.addEventListener('change', () => { topicsFor = null; topicChecked = new Set(); if (pickMode !== 'manual') ensureTopics(); });
        $('topicSearch').addEventListener('input', renderTopics);
        $('topicAll').addEventListener('click', () => { topicList.forEach(t => topicChecked.add(t.id)); renderTopics(); });
        $('topicNone').addEventListener('click', () => { topicChecked.clear(); renderTopics(); });
        $('topicList').addEventListener('change', ev => {
            const cb = ev.target.closest('[data-topic]'); if (!cb) return;
            const id = +cb.dataset.topic;
            // Ticking a parent ticks its subtopics too, as the mock exam builder does.
            [id, ...descendants(id)].forEach(x => cb.checked ? topicChecked.add(x) : topicChecked.delete(x));
            renderTopics();
        });

        $('pickGo').addEventListener('click', async () => {
            if (!topicChecked.size) { pickMsg('Tick at least one topic first.', 'err'); return; }
            const btn = $('pickGo');
            btn.disabled = true; pickMsg('Picking...');
            try {
                const res = await fetch(PICK_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({
                        subject_id: +subjectSelect.value, mode: pickMode, topic_ids: [...topicChecked],
                        count: pickMode === 'auto' ? +$('pickCount').value : null,
                    }),
                });
                const data = await res.json();
                if (!res.ok) { pickMsg(data.message || 'Could not pick questions.', 'err'); return; }

                if ($('pickReplace').checked) items = [];
                const have = addedIds();
                let added = 0, skipped = 0;
                for (const q of data.questions) {
                    if (have.has(q.id)) { skipped++; continue; }
                    if (items.length >= MAX_ITEMS) break;
                    const item = {
                        question_text: q.question_text, question_type: q.question_type === 'true_false' ? 'true_false' : 'mcq',
                        points: 1, explanation: q.explanation || '', source_question_id: q.id,
                        choices: q.choices.map(c => ({ label: c.label, text: c.text, is_correct: !!c.is_correct })),
                    };
                    relabel(item);
                    items.push(item);
                    have.add(q.id);
                    added++;
                }
                render();

                let text = `Added ${added} question${added === 1 ? '' : 's'}.`;
                if (skipped) text += ` ${skipped} already in the quiz were skipped.`;
                let cls = 'ok';
                if (pickMode === 'auto' && data.short) { text += ` The bank only had ${data.returned} of the ${data.requested} you asked for.`; cls = 'warn'; }
                if (pickMode === 'all' && data.short) { text += ` Those topics hold ${data.available}; the first ${MAX_ITEMS} were used.`; cls = 'warn'; }
                if (items.length >= MAX_ITEMS) { text += ` The quiz is at the ${MAX_ITEMS}-question limit.`; cls = 'warn'; }
                pickMsg(text, cls);
            } catch (e) {
                pickMsg('Something went wrong. Try again.', 'err');
            } finally { btn.disabled = false; }
        });
    }

    items.forEach(relabel);
    render();
})();
</script>

@include('partials.alerts')
</body>
</html>
