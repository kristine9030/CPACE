<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Build {{ $exam->title }} - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.mock-exam-styles')
    @include('partials.rich-editor')
    @include('partials.question-cards')
    <style>
        /* Stronger, "dark" card shadows for this page — the builder has a lot of
           panels stacked on a light grey background, so a heavier shadow reads
           much cleaner than the site-wide subtle one. */
        .card { box-shadow:0 10px 26px -14px rgba(15,20,35,.35), 0 2px 8px -2px rgba(15,20,35,.14); }

        .steps { display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap; background:#fff; border:1px solid var(--line);
                 border-radius:14px; padding:10px; box-shadow:0 10px 26px -14px rgba(15,20,35,.35), 0 2px 8px -2px rgba(15,20,35,.14); }
        .step-tab { display:flex; align-items:center; gap:9px; padding:11px 18px; background:transparent; border:1px solid transparent;
                    border-radius:11px; cursor:pointer; font-size:13px; font-weight:600; color:var(--muted); transition:background .15s, border-color .15s; }
        .step-tab:hover { background:#f7f8fa; }
        .step-tab.on { background:var(--primary-light); border-color:rgba(123,29,29,.18); color:var(--primary); }
        .step-tab .n { width:22px; height:22px; border-radius:50%; background:#eef0f4; color:#5b6377;
                       display:flex; align-items:center; justify-content:center; font-size:11.5px; flex-shrink:0; }
        .step-tab.on .n { background:var(--primary); color:#fff; }
        .step-tab.done .n { background:var(--green); color:#fff; }
        .step-panel { display:none; }
        .step-panel.on { display:block; }

        /* Topics step — search bar + quick actions above the tree */
        .topic-toolbar { display:flex; align-items:center; gap:10px; margin:14px 0 12px; flex-wrap:wrap; }
        .topic-search { position:relative; flex:1; min-width:200px; }
        .topic-search i { position:absolute; left:13px; top:50%; transform:translateY(-50%); font-size:12.5px; color:var(--muted); }
        .topic-search input { width:100%; padding:9px 12px 9px 34px; border:1px solid var(--line); border-radius:999px;
                              font-family:'Poppins',sans-serif; font-size:13px; background:#fafbfc; transition:border-color .15s, background .15s; }
        .topic-search input:focus { outline:none; border-color:var(--primary); background:#fff; }
        .topic-search .clear-search { position:absolute; right:8px; top:50%; transform:translateY(-50%); border:none; background:#eef0f4;
                                       color:#5b6377; width:20px; height:20px; border-radius:50%; font-size:10px; cursor:pointer; display:none;
                                       align-items:center; justify-content:center; }
        .topic-search.has-value .clear-search { display:flex; }
        .topic-quick { display:flex; gap:6px; flex-shrink:0; }
        .topic-quick button { border:1px solid var(--line); background:#fff; color:var(--ink); padding:8px 13px; border-radius:999px;
                              font-family:'Poppins',sans-serif; font-size:12px; font-weight:600; cursor:pointer; white-space:nowrap; }
        .topic-quick button:hover { background:#f7f8fa; }

        .topic-list { max-height:430px; overflow-y:auto; border:1px solid var(--line); border-radius:12px; padding:8px; background:#fafbfc; }
        .topic-row { position:relative; display:flex; align-items:center; gap:11px; padding:10px 12px; border-radius:9px; cursor:pointer;
                     font-size:13px; margin-bottom:3px; border:1px solid transparent; background:#fff; transition:background .15s, border-color .15s; }
        .topic-row:last-child { margin-bottom:0; }
        .topic-row:hover { border-color:var(--line); background:#f7f8fa; }
        .topic-row:has(.topic-check:checked) { background:var(--primary-light); border-color:rgba(123,29,29,.2); }
        .topic-row .topic-check { width:16px; height:16px; accent-color:var(--primary); flex-shrink:0; cursor:pointer; }
        .topic-branch { position:absolute; left:-8px; top:0; bottom:50%; width:12px; border-left:2px solid var(--line); border-bottom:2px solid var(--line);
                        border-radius:0 0 0 6px; pointer-events:none; }
        .topic-name { flex:1; font-weight:500; color:var(--ink); }
        .topic-bank { font-size:11px; color:var(--muted); background:#f1f2f5; padding:2px 8px; border-radius:999px; flex-shrink:0; }
        .topic-bank.zero { color:var(--red); background:#fdeceb; }
        .topic-empty { display:none; text-align:center; padding:30px 20px; color:var(--muted); }
        .topic-empty.show { display:block; }
        .topic-empty i { font-size:26px; opacity:.35; margin-bottom:8px; display:block; }

        .q-list { max-height:480px; overflow-y:auto; border:1px solid var(--line); border-radius:10px; }
        .q-row { display:flex; gap:11px; padding:12px 14px; border-bottom:1px solid #f0f1f4; font-size:13px; align-items:flex-start; }
        .q-row:last-child { border-bottom:none; }
        .q-row .q-text { flex:1; line-height:1.5; }
        .q-meta { font-size:11px; color:var(--muted); margin-top:4px; display:flex; gap:9px; flex-wrap:wrap; }
        .diff { padding:1px 7px; border-radius:999px; font-weight:600; font-size:10.5px; }
        .diff-easy { background:#dff3e8; color:var(--green); }
        .diff-moderate { background:#fdf0d8; color:var(--amber); }
        .diff-difficult { background:#fdeceb; color:var(--red); }

        /* Schedule step — friendlier date/time picker with presets + a live preview */
        .dt-input-wrap { position:relative; }
        .dt-input-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:13px; color:var(--muted); pointer-events:none; }
        .dt-input-wrap input { padding-left:34px !important; }
        .quick-pills { display:flex; gap:6px; flex-wrap:wrap; margin-top:8px; }
        .quick-pills button { border:1px solid var(--line); background:#fff; color:var(--ink); padding:6px 12px; border-radius:999px;
                              font-family:'Poppins',sans-serif; font-size:11.5px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:5px;
                              transition:background .15s, border-color .15s, color .15s; }
        .quick-pills button:hover { background:var(--primary-light); border-color:rgba(123,29,29,.2); color:var(--primary); }
        .dt-preview { margin-top:16px; padding:12px 14px; border-radius:10px; background:#eef4fd; border:1px solid #d3e1f7; color:#254a80;
                      font-size:12.5px; display:flex; gap:10px; align-items:flex-start; line-height:1.5; }
        .dt-preview.warn { background:#fdeceb; border-color:#f5cdc9; color:#8d2b22; }
        .dt-preview i { margin-top:2px; }

        .counter { font-size:13px; font-weight:700; color:var(--ink); }
        .counter.over { color:var(--red); }
        .split { display:grid; grid-template-columns:minmax(0, 1fr) 320px; gap:20px; align-items:start; }
        .split > .card:last-child { position:sticky; top:20px; }
        .audit { max-height:420px; overflow-y:auto; }
        .audit-row { padding:10px 0; border-bottom:1px solid #f0f1f4; font-size:12.5px; }
        .audit-row:last-child { border-bottom:none; }
        .audit-who { font-weight:600; color:var(--ink); }
        .audit-when { font-size:11px; color:var(--muted); margin-top:2px; }
        /* scenarios in the question pool */
        .scn-head { background:#faf8ff; padding:9px 14px 8px; font-size:11px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:#6d28d9; border-bottom:1px solid #eee9fb; display:flex; align-items:center; gap:7px; }
        .scn-head small { font-weight:500; text-transform:none; letter-spacing:0; color:var(--muted); }
        .scn-prev { padding:8px 14px 8px; font-size:12px; color:var(--muted); background:#fcfbff; border-bottom:1px solid #f0edfa; line-height:1.5; }
        .q-row.in-scn { padding-left:26px; border-left:3px solid #e3dafa; }
        /* the exam's own (hand-written) questions */
        .own-block { border:1.5px solid #ececf2; border-radius:14px; padding:14px 16px 16px; margin-top:14px; background:#fcfcfd; }
        .own-block.scn { background:#faf8ff; border:1.5px dashed #d9ccf5; }
        .own-head { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
        .own-tag { font-size:11px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; border-radius:999px; padding:4px 11px; background:#eef0f4; color:#5b6377; }
        .own-block.scn .own-tag { background:#efe9fe; color:#6d28d9; }
        .own-topic { display:inline-flex; align-items:center; gap:6px; background:#f4f5f8; border-radius:10px; padding:0 4px 0 10px; font-size:11.5px; color:var(--muted); }
        .own-topic select { border:0; background:transparent; padding:7px 8px 7px 0; font-size:12.5px; font-weight:500; max-width:210px; font-family:inherit; color:#3b3d45; outline:none; cursor:pointer; }
        .own-x { margin-left:auto; border:0; background:transparent; color:#9a9cab; cursor:pointer; width:30px; height:30px; border-radius:9px; }
        .own-x:hover { background:#fdecea; color:#c0392b; }
        .own-block .rte { margin-bottom:14px; }
        @media (max-width: 1000px) { .split { grid-template-columns:minmax(0, 1fr); } .split > .card:last-child { position:static; } }
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
        <x-hint tone="warn" icon="fa-lock">
            <x-slot:title><strong>This exam is locked</strong> — published by the Program Chair.</x-slot:title>
            Questions and schedule can no longer change, so every student sits the same paper.
        </x-hint>
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
                        <div class="card-sub">Fixed once the draft exists.</div>
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
                        <div style="margin-top:14px;font-size:12px;color:var(--muted);">
                            <span id="topicCount">0</span> selected ·
                            <span id="topicPool">0</span> questions available in the bank for them
                        </div>

                        @if($topicTree->isNotEmpty())
                            <div class="topic-toolbar">
                                <div class="topic-search" id="topicSearchWrap">
                                    <i class="fas fa-magnifying-glass"></i>
                                    <input type="text" id="topicSearch" placeholder="Search topics…" autocomplete="off">
                                    <button type="button" class="clear-search" id="clearTopicSearch"><i class="fas fa-xmark"></i></button>
                                </div>
                                @unless($readOnly)
                                    <div class="topic-quick">
                                        <button type="button" id="selectVisibleTopics"><i class="fas fa-check-double"></i> Select shown</button>
                                        <button type="button" id="clearTopics"><i class="fas fa-eraser"></i> Clear</button>
                                    </div>
                                @endunless
                            </div>
                        @endif

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
                            <div class="topic-empty" id="topicNoResults">
                                <i class="fas fa-magnifying-glass"></i>
                                <div>No topics match "<span id="topicNoResultsQuery"></span>"</div>
                            </div>
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

                    {{-- Questions written for this exam only --}}
                    <div class="card" id="ownCard">
                        <div class="card-title"><i class="fas fa-pen-to-square"></i> Your own questions <span class="counter" id="ownCounter" style="margin-left:8px;">0</span></div>
                        <div class="card-sub">Write questions that belong to this exam only (they are not added to the Test Bank). When several questions share one situation, put them in a scenario.</div>
                        @if($readOnly)
                            <div id="ownReadOnly" style="margin-top:12px;font-size:13px;color:var(--muted);"></div>
                        @else
                            <div class="own-block" id="ownStandaloneBlock">
                                <div class="own-head">
                                    <span class="own-tag"><i class="fas fa-circle-question"></i> Single questions</span>
                                    <label class="own-topic" title="The topic these questions count toward in students' analytics"><i class="fas fa-tag"></i><select id="ownStandaloneTopic"></select></label>
                                </div>
                                <div id="ownStandalone"></div>
                            </div>
                            <div id="ownScenarios"></div>
                            <div class="qc-actions" style="margin-top:14px;">
                                <button type="button" class="qc-btn" id="addOwnScenario"><i class="fas fa-layer-group"></i> Add a scenario with questions</button>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Step 4 --}}
                <div class="step-panel" data-panel="4">
                    <div class="card">
                        <div class="card-title"><i class="fas fa-calendar-day"></i> Schedule</div>
                        <div class="card-sub">When students sit this exam, and how long they get.</div>
                        <div style="display:grid;grid-template-columns:minmax(0, 1fr) 200px;gap:16px;margin-top:16px;">
                            <div class="field">
                                <label>Exam date and start time</label>
                                <div class="dt-input-wrap">
                                    <i class="fas fa-calendar"></i>
                                    <input type="datetime-local" name="scheduled_at" id="scheduledAt" @disabled($readOnly)
                                           value="{{ old('scheduled_at', $exam->scheduled_at?->format('Y-m-d\TH:i')) }}">
                                </div>
                                @unless($readOnly)
                                    <div class="quick-pills" id="datePills">
                                        <button type="button" data-days="0"><i class="fas fa-sun"></i> Today</button>
                                        <button type="button" data-days="1">Tomorrow</button>
                                        <button type="button" data-days="7">In a week</button>
                                    </div>
                                    <div class="quick-pills" id="timePills">
                                        <button type="button" data-time="07:00">7:00 AM</button>
                                        <button type="button" data-time="08:00">8:00 AM</button>
                                        <button type="button" data-time="13:00">1:00 PM</button>
                                        <button type="button" data-time="14:00">2:00 PM</button>
                                    </div>
                                @endunless
                                <div class="field-hint">Students can only enter during this window.</div>
                            </div>
                            <div class="field">
                                <label>Duration (minutes)</label>
                                <div class="dt-input-wrap">
                                    <i class="fas fa-hourglass-half"></i>
                                    <input type="number" name="duration_minutes" id="durationInput" min="5" max="600" @disabled($readOnly)
                                           value="{{ old('duration_minutes', $exam->duration_minutes) }}">
                                </div>
                                @unless($readOnly)
                                    <div class="quick-pills" id="durationPills">
                                        <button type="button" data-min="60">60</button>
                                        <button type="button" data-min="120">120</button>
                                        <button type="button" data-min="180">180</button>
                                        <button type="button" data-min="240">240</button>
                                    </div>
                                @endunless
                                <div class="field-hint">Real CPALE allots 180.</div>
                            </div>
                        </div>
                        @error('duration_minutes')<div class="err">{{ $message }}</div>@enderror
                        @error('scheduled_at')<div class="err">{{ $message }}</div>@enderror

                        <div class="dt-preview" id="dtPreview" style="display:none;"></div>

                        <x-hint icon="fa-shield-halved" style="margin:16px 0 0;">
                            <x-slot:title>Proctored: <strong>camera and screen sharing</strong> required.</x-slot:title>
                            Alt-tabbing, leaving fullscreen or turning either off during the exam is flagged
                            for you and the Program Chair.
                        </x-hint>
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

    <div class="card" style="margin-top:18px;">
        <div class="card-title"><i class="fas fa-users-viewfinder"></i> Who can take this exam</div>
        <div class="card-sub">
            Only the ticked year levels, sections, and batch years will see it once published.<x-tip>There's no code to hand out. You or the Program Chair can change this later.</x-tip>
        </div>
        @error('audience')<div class="banner banner-danger" style="margin-top:10px;"><i class="fas fa-triangle-exclamation"></i><div>{{ $message }}</div></div>@enderror

        @if($exam->isClosed())
            <div style="margin-top:12px;font-size:13px;"><strong>{{ $exam->audienceLabel() }}</strong> <span style="color:var(--muted);">(closed — can't be changed)</span></div>
        @else
            <form method="POST" action="{{ route('faculty.mock-exams.audience', $exam) }}" style="margin-top:12px;">
                @csrf @method('PUT')
                @php
                    $pickedYears = collect(old('audience_years', $exam->audience_years ?? []))->map(fn ($y) => (int) $y)->all();
                    $pickedSections = old('audience_sections', $exam->audience_sections ?? []);
                    $pickedBatchYears = old('audience_batch_years', $exam->audience_batch_years ?? []);
                @endphp
                <div style="font-size:12px;font-weight:600;margin-bottom:6px;">Year level</div>
                <div style="display:flex;flex-wrap:wrap;gap:8px 14px;">
                    @php
                        // Only years that actually have an active section, plus any year already saved on this exam.
                        $yearsInUse = $sections->pluck('year_level')->filter()->map(fn ($y) => (int) $y)
                            ->merge($pickedYears)->unique()->sort()->values();
                    @endphp
                    @foreach($yearsInUse as $level)
                        @php $label = $yearLabels[$level] ?? 'Year '.$level; @endphp
                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                            <input type="checkbox" name="audience_years[]" value="{{ $level }}" class="aud-year" {{ in_array($level, $pickedYears, true) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                @if($yearsInUse->isEmpty())
                    <div style="font-size:12.5px;color:var(--muted);">No sections exist yet. Ask the Program Chair to add one first.</div>
                @endif

                @if($sections->isNotEmpty())
                    <div id="audSectionsWrap" style="display:none;">
                    <div style="font-size:12px;font-weight:600;margin:14px 0 4px;">Sections <span style="font-weight:400;color:var(--muted);">(optional — leave all unticked to admit every section of the chosen years)</span></div>
                    <div style="max-height:180px;overflow-y:auto;display:flex;flex-wrap:wrap;gap:6px 14px;">
                        @foreach($sections as $section)
                            <label class="aud-section" data-year="{{ $section->year_level }}" style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                                <input type="checkbox" name="audience_sections[]" value="{{ $section->name }}" {{ in_array($section->name, (array) $pickedSections, true) ? 'checked' : '' }}>
                                {{ $section->name }}
                            </label>
                        @endforeach
                    </div>
                    </div>
                @endif

                @if(!empty($batchYears))
                    <div style="font-size:12px;font-weight:600;margin:14px 0 4px;">Batch year <span style="font-weight:400;color:var(--muted);">(optional — leave all unticked to admit every batch of the chosen years/sections)</span></div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px 14px;">
                        @foreach($batchYears as $batchYear)
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                                <input type="checkbox" name="audience_batch_years[]" value="{{ $batchYear }}" {{ in_array($batchYear, (array) $pickedBatchYears, true) ? 'checked' : '' }}>
                                {{ $batchYear }}
                            </label>
                        @endforeach
                    </div>
                @endif

                <div style="display:flex;align-items:center;gap:12px;margin-top:14px;flex-wrap:wrap;">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-floppy-disk"></i> Save audience</button>
                    <span style="font-size:12px;color:var(--muted);">Now: <strong>{{ $exam->audienceLabel() }}</strong></span>
                </div>
            </form>
            <script>
                (function () {
                    const card = document.currentScript.closest('.card');
                    const years = card.querySelectorAll('.aud-year');
                    const wrap = card.querySelector('#audSectionsWrap');
                    if (!years.length || !wrap) return;
                    const sync = () => {
                        const on = new Set([...years].filter(y => y.checked).map(y => y.value));
                        let any = false;
                        card.querySelectorAll('.aud-section').forEach(l => {
                            const show = on.has(l.dataset.year);
                            l.style.display = show ? 'flex' : 'none';
                            if (!show) l.querySelector('input').checked = false;
                            any = any || show;
                        });
                        wrap.style.display = any ? 'block' : 'none';
                    };
                    years.forEach(y => y.addEventListener('change', sync));
                    sync();
                })();
            </script>
        @endif
    </div>

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
            'question_text' => \App\Support\RichText::plain($i->question_text),
            'difficulty' => $i->difficulty,
            'topic' => null,
            'scenario_group' => $i->scenario_group ?? null,
            'scenario_text' => \App\Support\RichText::plain($i->scenario_text ?? ''),
        ])->values();

    // Questions written in the builder: the single ones, then each hand-written scenario with its questions.
    $diffLabel = ['easy' => 'Easy', 'moderate' => 'Medium', 'difficult' => 'Hard'];
    $ownRows = $exam->items->filter(fn ($i) => ! $i->source_question_id)->map(fn ($i) => [
        'question_text' => $i->question_text,
        'question_type' => $i->question_type,
        'difficulty' => $diffLabel[$i->difficulty] ?? 'Medium',
        'explanation' => $i->explanation,
        'points' => $i->points,
        'topic_id' => $i->topic_id,
        'scenario_group' => $i->scenario_group ?? null,
        'scenario_text' => $i->scenario_text ?? null,
        'choices' => collect((array) $i->choices)->map(fn ($c) => ['text' => $c['text'] ?? '', 'is_correct' => ! empty($c['is_correct'])])->values()->all(),
    ])->values();
    $ownData = [
        'single' => $ownRows->filter(fn ($r) => ! $r['scenario_group'])->values()->all(),
        'scenarios' => $ownRows->filter(fn ($r) => $r['scenario_group'])->groupBy('scenario_group')->map(fn ($g) => [
            'text' => $g->first()['scenario_text'],
            'topic_id' => $g->first()['topic_id'],
            'items' => $g->values()->all(),
        ])->values()->all(),
    ];
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
    const OWN = @json($ownData);
    const UPLOAD_URL = @json(route('faculty.question.upload-image'));

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

    // ── topic search ─────────────────────────────────────────────────────
    const topicSearch = document.getElementById('topicSearch');
    const topicRows = [...document.querySelectorAll('.topic-row')];
    function applyTopicSearch() {
        if (!topicSearch) return;
        const q = topicSearch.value.trim().toLowerCase();
        document.getElementById('topicSearchWrap').classList.toggle('has-value', q.length > 0);
        if (!q) {
            topicRows.forEach(r => { r.style.display = ''; });
            document.getElementById('topicNoResults').classList.remove('show');
            return;
        }
        const matches = topicRows.map(r => r.querySelector('.topic-name').textContent.toLowerCase().includes(q));
        const visible = matches.slice();
        // Keep a matched row's ancestors visible too, so the hierarchy still makes sense.
        topicRows.forEach((r, i) => {
            if (!matches[i]) return;
            let depth = Number(r.dataset.depth);
            for (let j = i - 1; j >= 0 && depth > 0; j--) {
                const d = Number(topicRows[j].dataset.depth);
                if (d < depth) { visible[j] = true; depth = d; }
            }
        });
        topicRows.forEach((r, i) => { r.style.display = visible[i] ? '' : 'none'; });
        document.getElementById('topicNoResults').classList.toggle('show', !visible.some(Boolean));
        document.getElementById('topicNoResultsQuery').textContent = topicSearch.value.trim();
    }
    if (topicSearch) {
        topicSearch.addEventListener('input', applyTopicSearch);
        const clearSearchBtn = document.getElementById('clearTopicSearch');
        if (clearSearchBtn) clearSearchBtn.addEventListener('click', () => { topicSearch.value = ''; applyTopicSearch(); topicSearch.focus(); });
    }

    const selectVisibleBtn = document.getElementById('selectVisibleTopics');
    if (selectVisibleBtn) selectVisibleBtn.addEventListener('click', () => {
        topicRows.forEach(r => {
            if (r.style.display === 'none') return;
            const box = r.querySelector('.topic-check');
            if (box && !box.disabled) box.checked = true;
        });
        topicMode = 'manual'; document.getElementById('topicMode').value = 'manual'; paintModes('topicModes', 'manual');
        syncTopics();
    });
    const clearTopicsBtn = document.getElementById('clearTopics');
    if (clearTopicsBtn) clearTopicsBtn.addEventListener('click', () => {
        document.querySelectorAll('.topic-check').forEach(c => { if (!c.disabled) c.checked = false; });
        topicMode = 'manual'; document.getElementById('topicMode').value = 'manual'; paintModes('topicModes', 'manual');
        syncTopics();
    });

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

        let lastGroup = null;
        list.innerHTML = shown.map(q => {
            const g = q.scenario_group || null;
            let head = '';
            if (g && g !== lastGroup) {
                const n = shown.filter(x => x.scenario_group === g).length;
                head = `<div class="scn-head"><i class="fas fa-layer-group"></i> Scenario <small>${n} question${n === 1 ? '' : 's'}, taken together</small></div>` +
                    (q.scenario_text ? `<div class="scn-prev">${escapeHtml(q.scenario_text).slice(0, 260)}</div>` : '');
            }
            lastGroup = g;
            return head + `
            <label class="q-row ${g ? 'in-scn' : ''}">
                <input type="checkbox" class="q-check" value="${q.id}" ${selected.has(q.id) ? 'checked' : ''} ${readOnly ? 'disabled' : ''}>
                <span class="q-text">
                    ${escapeHtml(q.question_text || '').slice(0, 260)}
                    <span class="q-meta">
                        ${q.difficulty ? `<span class="diff diff-${q.difficulty}">${q.difficulty}</span>` : ''}
                        ${q.topic ? `<span>${escapeHtml(q.topic)}</span>` : ''}
                    </span>
                </span>
            </label>`;
        }).join('');

        list.querySelectorAll('.q-check').forEach(c => c.addEventListener('change', () => {
            const id = Number(c.value);
            const hit = shown.find(p => p.id === id);
            // a scenario is picked or dropped as a whole
            const ids = hit && hit.scenario_group ? shown.filter(p => p.scenario_group === hit.scenario_group).map(p => p.id) : [id];
            if (c.checked) {
                const fresh = ids.filter(x => !selected.has(x)).length;
                if (selected.size + ownCount() + fresh > MAX) {
                    c.checked = false;
                    alert('A mock exam can hold at most ' + MAX + ' questions' + (ids.length > 1 ? ' (this scenario has ' + ids.length + ').' : '.'));
                    return;
                }
                ids.forEach(x => selected.set(x, shown.find(p => p.id === x) || { id: x }));
            } else {
                ids.forEach(x => selected.delete(x));
            }
            list.querySelectorAll('.q-check').forEach(box => { if (ids.includes(Number(box.value))) box.checked = c.checked; });
            updateCounter();
        }));
        updateCounter();
    }

    function updateCounter() {
        const el = document.getElementById('itemCounter');
        const total = selected.size + ownCount();
        el.textContent = total + ' / ' + MAX + ' selected' + (ownCount() ? ' (' + ownCount() + ' of your own)' : '');
        el.classList.toggle('over', total > MAX);
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

    // ── your own questions ───────────────────────────────────────────────
    const DIFF_OUT = { Easy: 'easy', Medium: 'moderate', Hard: 'difficult' };
    const ownScn = [];   // { el, qc, text: textarea, topic: select }
    let ownSingle = null, ownSingleTopic = null;
    function topicOptions(current) {
        const rows = [...document.querySelectorAll('.topic-row')].map(r => ({ id: r.querySelector('.topic-check').value, name: r.querySelector('.topic-name').textContent.trim(), depth: Number(r.dataset.depth || 0) }));
        return '<option value="">No topic (not tracked)</option>' + rows.map(t => `<option value="${t.id}" ${String(current) === String(t.id) ? 'selected' : ''}>${'\u00a0\u00a0'.repeat(t.depth)}${escapeHtml(t.name)}</option>`).join('');
    }
    function ownCount() {
        if (readOnly) return OWN.single.length + OWN.scenarios.reduce((n, s) => n + s.items.length, 0);
        return (ownSingle ? ownSingle.get().length : 0) + ownScn.reduce((n, b) => n + b.qc.get().length, 0);
    }
    function refreshOwn() {
        if (readOnly) return;
        document.getElementById('ownCounter').textContent = ownCount();
        updateCounter();
    }
    function addScenarioBlock(data) {
        const wrap = document.createElement('div');
        wrap.className = 'own-block scn';
        wrap.innerHTML = `<div class="own-head">
                <span class="own-tag"><i class="fas fa-layer-group"></i> Scenario</span>
                <label class="own-topic" title="The topic its questions count toward in students' analytics"><i class="fas fa-tag"></i><select data-own-topic>${topicOptions(data.topic_id)}</select></label>
                <button type="button" class="own-x" title="Remove this scenario and its questions"><i class="fas fa-trash"></i></button>
            </div>
            <textarea class="rte-src" data-upload-url="${UPLOAD_URL}" placeholder="Write the scenario: the situation the questions below refer to...">${escapeHtml(data.text || '')}</textarea>
            <div data-own-qs></div>`;
        document.getElementById('ownScenarios').appendChild(wrap);
        RTE.init(wrap);
        const block = { el: wrap, text: wrap.querySelector('textarea.rte-src, textarea[data-upload-url]'), topic: wrap.querySelector('[data-own-topic]') };
        block.qc = QCards.mount(wrap.querySelector('[data-own-qs]'), { uploadUrl: UPLOAD_URL, points: true, items: data.items && data.items.length ? data.items : [{ question_type: 'mcq' }], onChange: refreshOwn });
        wrap.querySelector('.own-x').addEventListener('click', () => {
            const n = block.qc.get().length;
            if (n && !confirm('Remove this scenario and its ' + n + ' question' + (n === 1 ? '' : 's') + '?')) return;
            wrap.remove();
            ownScn.splice(ownScn.indexOf(block), 1);
            refreshOwn();
        });
        ownScn.push(block);
        refreshOwn();
        return block;
    }
    if (readOnly) {
        const ro = document.getElementById('ownReadOnly');
        if (ro) {
            const total = ownCount();
            document.getElementById('ownCounter').textContent = total;
            ro.textContent = total ? total + ' question' + (total === 1 ? '' : 's') + ' written for this exam.' : 'No questions were written for this exam.';
        }
    } else {
        ownSingle = QCards.mount(document.getElementById('ownStandalone'), { uploadUrl: UPLOAD_URL, points: true, items: OWN.single, onChange: refreshOwn });
        ownSingleTopic = document.getElementById('ownStandaloneTopic');
        ownSingleTopic.innerHTML = topicOptions((OWN.single[0] || {}).topic_id || '');
        OWN.scenarios.forEach(addScenarioBlock);
        document.getElementById('addOwnScenario').addEventListener('click', () => {
            const b = addScenarioBlock({ text: '', items: [{ question_type: 'mcq' }] });
            b.el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            b.el.querySelector('.rte-area')?.focus();
        });
        refreshOwn();
    }
    function ownRows() {
        if (readOnly) return { rows: [], error: null };
        const rows = [];
        const topicOf = sel => (sel && sel.value ? Number(sel.value) : null);
        const row = (it, extra) => Object.assign({
            question_text: it.question_text, question_type: it.question_type, difficulty: DIFF_OUT[it.difficulty] || 'moderate',
            explanation: it.explanation, points: it.points || 1, choices: it.choices,
        }, extra);
        ownSingle.get().forEach(it => rows.push(row(it, { topic_id: topicOf(ownSingleTopic) })));
        for (let k = 0; k < ownScn.length; k++) {
            const b = ownScn[k], text = b.text.value, group = 'c' + (k + 1);
            const items = b.qc.get();
            if (!items.length) continue;
            if (!RTE.toPlain(text) && !/<(img|table)/i.test(text)) return { rows, error: 'A scenario needs its text. Write the situation, or remove the scenario.', block: b.el };
            items.forEach(it => rows.push(row(it, { topic_id: topicOf(b.topic), scenario_group: group, scenario_text: text })));
        }
        return { rows, error: null };
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
        const own = ownRows();
        if (own.error) {
            alert(own.error);
            own.block?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        document.getElementById('itemsJson').value = JSON.stringify([...selected.keys()].map(id => ({ source_question_id: id })).concat(own.rows));
        return true;
    }

    const buildForm = document.getElementById('buildForm');
    if (buildForm) buildForm.addEventListener('submit', e => { if (!packForm()) e.preventDefault(); });

    const reviewBtn = document.getElementById('submitReviewBtn');
    if (reviewBtn) reviewBtn.addEventListener('click', () => {
        if (!selected.size && !ownCount()) { alert('Add at least one question before submitting for review.'); return; }
        if (!confirm('Send this to the Program Chair for review?\n\nYou can keep editing until they publish it.')) return;
        // then_submit makes the save and the hand-over one request, so the Chair
        // always reviews exactly what is on screen right now.
        const marker = document.createElement('input');
        marker.type = 'hidden'; marker.name = 'then_submit'; marker.value = '1';
        buildForm.appendChild(marker);
        if (!packForm()) { marker.remove(); return; }
        buildForm.submit();
    });

    renderQuestions();
    if (questionMode === 'auto') document.getElementById('autoControls').style.display = 'flex';

    // ── schedule: date/time presets + live human-readable preview ──────────
    const schedInput = document.getElementById('scheduledAt');
    const durInput = document.getElementById('durationInput');
    const dtPreview = document.getElementById('dtPreview');

    function pad2(n) { return String(n).padStart(2, '0'); }

    function setDatePart(daysFromToday) {
        const base = new Date();
        base.setDate(base.getDate() + daysFromToday);
        const time = schedInput.value && schedInput.value.includes('T') ? schedInput.value.split('T')[1] : '13:00';
        schedInput.value = `${base.getFullYear()}-${pad2(base.getMonth() + 1)}-${pad2(base.getDate())}T${time}`;
        updateSchedulePreview();
    }
    function setTimePart(hhmm) {
        let datePart;
        if (schedInput.value && schedInput.value.includes('T')) {
            datePart = schedInput.value.split('T')[0];
        } else {
            const t = new Date();
            datePart = `${t.getFullYear()}-${pad2(t.getMonth() + 1)}-${pad2(t.getDate())}`;
        }
        schedInput.value = `${datePart}T${hhmm}`;
        updateSchedulePreview();
    }

    document.querySelectorAll('#datePills button').forEach(b => b.addEventListener('click', () => setDatePart(Number(b.dataset.days))));
    document.querySelectorAll('#timePills button').forEach(b => b.addEventListener('click', () => setTimePart(b.dataset.time)));
    document.querySelectorAll('#durationPills button').forEach(b => b.addEventListener('click', () => {
        if (durInput) { durInput.value = b.dataset.min; updateSchedulePreview(); }
    }));

    function updateSchedulePreview() {
        if (!dtPreview || !schedInput) return;
        if (!schedInput.value) { dtPreview.style.display = 'none'; return; }
        const start = new Date(schedInput.value);
        if (isNaN(start.getTime())) { dtPreview.style.display = 'none'; return; }
        const mins = Number((durInput && durInput.value) || 0);
        const end = new Date(start.getTime() + mins * 60000);
        const dateFmt = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const timeFmt = { hour: 'numeric', minute: '2-digit' };
        const isPast = start.getTime() < Date.now();

        dtPreview.style.display = 'flex';
        dtPreview.classList.toggle('warn', isPast);
        dtPreview.innerHTML = isPast
            ? '<i class="fas fa-triangle-exclamation"></i><div>This date is in the past — move it forward so students can sit this exam.</div>'
            : `<i class="fas fa-circle-info"></i><div><strong>${start.toLocaleDateString(undefined, dateFmt)}</strong><br>${start.toLocaleTimeString(undefined, timeFmt)} – ${end.toLocaleTimeString(undefined, timeFmt)} (${mins} min)</div>`;
    }
    if (schedInput) { schedInput.addEventListener('input', updateSchedulePreview); schedInput.addEventListener('change', updateSchedulePreview); }
    if (durInput) durInput.addEventListener('input', updateSchedulePreview);
    updateSchedulePreview();
})();
</script>
</body>
</html>
