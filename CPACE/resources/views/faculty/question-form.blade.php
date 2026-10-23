<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($editMode) ? 'Edit Question' : 'Add Question' }} - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; --orange:#f59e0b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

        /* MAIN */
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }

        /* TOPBAR */
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; position:relative; z-index:100; }
        .topbar-left { display:flex; align-items:center; gap:12px; }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .page-title { font-size:26px; font-weight:700; color:#14283E; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .breadcrumb { display:flex; align-items:center; gap:6px; font-size:12px; color:#aaa; margin-bottom:4px; }
        .breadcrumb a { color:var(--accent); text-decoration:none; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:white; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }

        /* FORM LAYOUT */
        .form-layout { display:grid; grid-template-columns:minmax(0, 1fr) 320px; gap:20px; align-items:start; }

        /* CARDS */
        .card { background:white; border-radius:14px; padding:24px; margin-bottom:20px; }
        .card:last-child { margin-bottom:0; }
        .card-title { font-size:14px; font-weight:700; color:#1a1a1a; margin-bottom:18px; display:flex; align-items:center; gap:8px; }
        .card-title i { color:var(--accent); }
        /* Quiet step numbers: just a hint of the order to fill the form in */
        .card-title .step-no { width:22px; height:22px; border-radius:50%; background:#f3f4f6; border:1px solid #e5e7eb; color:#9ca3af; font-size:11px; font-weight:600; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; }

        /* FORM FIELDS */
        .form-group { margin-bottom:18px; }
        .form-group:last-child { margin-bottom:0; }
        label { display:block; font-size:12px; font-weight:600; color:#555; margin-bottom:6px; }
        label .req { color:var(--accent); margin-left:2px; }
        input[type=text], textarea, select {
            width:100%; font-family:'Poppins',sans-serif; font-size:13px;
            border:1.5px solid #e8e8e8; border-radius:8px; padding:10px 14px;
            color:#333; background:white; outline:none; transition:border-color .2s;
        }
        input[type=text]:focus, textarea:focus, select:focus { border-color:var(--primary); }
        textarea { resize:vertical; min-height:110px; line-height:1.6; }

        .form-row { display:grid; grid-template-columns:minmax(0, 1fr) minmax(0, 1fr); gap:14px; }
        .form-row-3 { display:grid; grid-template-columns:minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr); gap:14px; }

        /* DRAFT WITH AI: collapsible, open by default */
        .ai-head { display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .ai-toggle { display:inline-flex; align-items:center; gap:6px; border:0; background:#efe9fe; color:#6d28d9; border-radius:999px; padding:6px 12px; font-size:12px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif; transition:background .15s; }
        .ai-toggle:hover { background:#e4dafd; }
        .ai-toggle i { font-size:10px; transition:transform .25s; }
        .ai-card.collapsed .ai-toggle i { transform:rotate(180deg); }
        .ai-body { display:grid; grid-template-rows:1fr; transition:grid-template-rows .28s ease; }
        .ai-inner { min-height:0; overflow:hidden; transition:opacity .2s; }
        .ai-card.collapsed .ai-body { grid-template-rows:0fr; }
        .ai-card.collapsed .ai-inner { opacity:0; visibility:hidden; transition:opacity .2s, visibility 0s .28s; }
        .ai-card.collapsed { padding-bottom:18px; }
        /* QUESTION TYPE + DIFFICULTY: pills in the Answer card header */
        .ans-head { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
        .ans-head .q-head-opts { margin-bottom:0; }
        .ans-empty { font-size:12.5px; color:#9a9cab; background:#fafafb; border:1.5px dashed #e3e4ea; border-radius:12px; padding:20px; text-align:center; }
        .ans-empty i { margin-right:6px; color:var(--accent); }
        .q-head-opts { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px; }
        .rte-tabs .q-head-opts { margin:0 0 0 auto; flex-wrap:nowrap; }
        .rte-tabs .q-head-opts + .rte-collapse { margin-left:6px; }
        .q-opt { display:inline-flex; align-items:center; gap:4px; margin:0; background:#f4f5f8; border-radius:10px; padding:0 4px 0 11px; color:#3b3d45; font-size:12px; transition:box-shadow .15s, background .15s; }
        .q-opt .req { margin:0; font-size:13px; }
        .q-opt select { width:auto; border:0; background:transparent; padding:7px 8px 7px 2px; font-size:12.5px; font-weight:500; color:#3b3d45; cursor:pointer; outline:none; }
        .q-opt:focus-within { box-shadow:0 0 0 2px var(--primary-light); }
        .q-opt-type { background:var(--primary-light); color:var(--primary); box-shadow:inset 0 0 0 1px #e5c4c4; }
        .q-opt-type select { color:var(--primary); font-weight:600; }
        .q-opt-type.empty { background:#fff3f0; box-shadow:inset 0 0 0 1.5px var(--accent); animation:qpulse 1.6s ease-in-out infinite; }
        @keyframes qpulse { 0%,100% { box-shadow:inset 0 0 0 1.5px var(--accent), 0 0 0 0 rgba(192,57,43,.25); } 50% { box-shadow:inset 0 0 0 1.5px var(--accent), 0 0 0 5px rgba(192,57,43,0); } }
        @media (max-width:760px) { .rte-tabs { flex-wrap:wrap; } .rte-tabs .q-head-opts { order:5; width:100%; margin:6px 0 0; flex-wrap:wrap; } }

        /* ANSWER CHOICES */
        .choices-list { display:flex; flex-direction:column; gap:10px; }
        .choice-item { display:flex; align-items:center; gap:10px; }
        .choice-letter {
            width:32px; height:32px; border-radius:50%; flex-shrink:0;
            display:flex; align-items:center; justify-content:center;
            font-size:12px; font-weight:700;
        }
        .choice-letter { background:#f3f4f6; color:#555; }
        .choice-item input[type=text] { flex:1; }
        .choice-radio { flex-shrink:0; width:16px; height:16px; accent-color:var(--primary); cursor:pointer; }
        .correct-label { font-size:11px; color:#aaa; white-space:nowrap; }
        .choice-remove { flex-shrink:0; background:none; border:none; color:#bbb; cursor:pointer; font-size:14px; padding:4px; }
        .choice-remove:hover { color:var(--accent); }
        .add-choice-btn { align-self:flex-start; background:none; border:1.5px dashed #ddd; border-radius:8px; color:#777; font-size:12px; font-weight:600; padding:8px 14px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
        .add-choice-btn:hover { border-color:var(--primary); color:var(--primary); }
        .add-choice-btn:disabled { opacity:.4; cursor:not-allowed; }

        /* RADIO GROUP */
        .radio-group { display:flex; gap:12px; flex-wrap:wrap; }
        .radio-option { display:flex; align-items:center; gap:6px; cursor:pointer; }
        .radio-option input { accent-color:var(--primary); }
        .radio-option span { font-size:13px; color:#555; }

        /* TOGGLE */
        .toggle-row { display:flex; justify-content:space-between; align-items:center; }
        .toggle-wrap { display:flex; align-items:center; gap:8px; }
        .toggle { position:relative; width:44px; height:24px; }
        .toggle input { opacity:0; width:0; height:0; }
        .toggle-slider {
            position:absolute; inset:0; background:#ddd; border-radius:24px; cursor:pointer; transition:.3s;
        }
        .toggle-slider:before { content:''; position:absolute; width:18px; height:18px; left:3px; bottom:3px; background:white; border-radius:50%; transition:.3s; }
        .toggle input:checked + .toggle-slider { background:var(--primary); }
        .toggle input:checked + .toggle-slider:before { transform:translateX(20px); }
        .toggle-lbl { font-size:13px; color:#555; }

        /* SIDE PANEL */
        .side-card { background:white; border-radius:14px; padding:22px; margin-bottom:16px; }
        .side-card:last-child { margin-bottom:0; }
        .side-title { font-size:13px; font-weight:700; color:#1a1a1a; margin-bottom:14px; }

        .meta-item { margin-bottom:14px; }
        .meta-item:last-child { margin-bottom:0; }

        .help-tip { font-size:11px; color:#aaa; margin-top:5px; }
        .topic-search { position:relative; margin-bottom:8px; }
        .topic-search i { position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:12px; color:#aaa; pointer-events:none; }
        .topic-search input { width:100%; font-family:'Poppins',sans-serif; font-size:12.5px; border:1.5px solid #e8e8e8; border-radius:8px; padding:9px 12px 9px 32px; outline:none; background:#fff; transition:border-color .2s; }
        .topic-search input:focus { border-color:var(--primary); }
        .topic-search input:disabled { background:#f6f6f8; cursor:not-allowed; }
        #topicSelect[size] { padding:4px; } #topicSelect[size] option { padding:7px 10px; border-radius:6px; }

        .preview-box {
            background:#f8f8f8; border-radius:10px; padding:16px;
            font-size:13px; color:#444; line-height:1.7; min-height:80px;
            border:1px dashed #e0e0e0;
        }
        .preview-placeholder { color:#ccc; font-style:italic; }
        /* Everything the editor can produce is kept inside the card: wide pictures, tables, shapes and deep indents scale or scroll. */
        .preview-box { max-width:100%; max-height:380px; overflow:auto; overflow-wrap:anywhere; word-break:break-word; isolation:isolate; }
        .preview-box * { max-width:100%; box-sizing:border-box; }
        .preview-box #previewText { display:block; }
        .preview-box img { height:auto; border-radius:6px; }
        .preview-box table { border-collapse:collapse; font-size:12px; table-layout:auto !important; width:100% !important; }
        .preview-box td, .preview-box th { width:auto !important; }
        .preview-box td, .preview-box th { overflow-wrap:anywhere; }
        .preview-box ul, .preview-box ol { margin:.3em 0 .3em 1.4em; padding:0; }
        .preview-box h1 { font-size:1.4em; margin:.4em 0; } .preview-box h2 { font-size:1.25em; margin:.4em 0; } .preview-box h3 { font-size:1.1em; margin:.4em 0; } .preview-box h4 { font-size:1em; margin:.4em 0; }
        .preview-box [style*="margin-left"] { margin-left:min(2em, 12%) !important; }
        .preview-box [style*="inline-block"] { max-width:100%; flex-shrink:0; }
        .preview-box hr { margin:.5em 0; }
        .sv-screen .q-text, .sv-screen .explain-text { overflow-x:auto; max-width:100%; }
        .sv-screen .q-text *, .sv-screen .explain-text * { max-width:100%; }

        .submit-actions { display:flex; flex-direction:column; gap:10px; }

        @keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
        .a0{animation:fadeUp .4s ease both} .a1{animation:fadeUp .4s .07s ease both}

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            /* form layout: content+sidebar → single column */
            .form-layout { grid-template-columns: minmax(0, 1fr) !important; }
            /* 2-col form row → stack */
            .form-row { grid-template-columns: minmax(0, 1fr) !important; }
            /* 3-col form row → stack */
            .form-row-3 { grid-template-columns: minmax(0, 1fr) !important; }
            /* choice items: ensure they wrap properly */
            .choice-item { flex-wrap: wrap; gap: 8px; }
            .correct-label { font-size: 10px; }
            /* topbar right */
            .topbar-right { flex-wrap: wrap; gap: 8px; }
        }

        @media (max-width: 480px) {
            .card { padding: 16px; }
            .side-card { padding: 14px; }
            .card-title { font-size: 13px; }
            /* choice letter and text */
            .choice-letter { width: 28px; height: 28px; font-size: 11px; }
            input[type=text], textarea, select { font-size: 12px; }
        }

    {!! \App\Support\RichText::fitCss() !!}
    /* ── VIEW AS STUDENT ── */
    .sv-overlay { position:fixed; inset:0; background:rgba(15,17,30,.55); backdrop-filter:blur(3px); z-index:9000; display:none; align-items:center; justify-content:center; padding:20px; }
    .sv-overlay.open { display:flex; }
    .sv-dialog { background:#f4f5f7; border-radius:20px; width:min(860px,100%); max-height:100%; display:flex; flex-direction:column; box-shadow:0 30px 80px rgba(0,0,0,.35); overflow:hidden; }
    .sv-top { display:flex; align-items:center; gap:12px; padding:14px 20px; background:#fff; border-bottom:1px solid #eceef2; }
    .sv-top h3 { font-size:14px; font-weight:700; color:#14283E; }
    .sv-top small { color:#9a9cab; font-size:11.5px; }
    .sv-seg { margin-left:auto; display:inline-flex; background:#f0f1f5; border-radius:10px; padding:3px; }
    .sv-seg button { border:0; background:transparent; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:600; color:#777; cursor:pointer; font-family:'Poppins',sans-serif; display:inline-flex; gap:6px; align-items:center; }
    .sv-seg button.on { background:#fff; color:var(--primary); box-shadow:0 1px 3px rgba(0,0,0,.1); }
    .sv-close { border:0; background:#f0f1f5; width:32px; height:32px; border-radius:50%; cursor:pointer; color:#666; }
    .sv-close:hover { background:#e4e6ec; }
    .sv-stage { overflow:auto; padding:26px 20px; display:flex; justify-content:center; }
    .sv-device { width:100%; max-width:720px; transition:max-width .25s; }
    .sv-device.phone { max-width:390px; }
    .sv-device.phone .sv-screen { border:8px solid #1c1e2a; border-radius:34px; }
    .sv-screen { background:#f4f5f7; border-radius:16px; padding:18px 16px 20px; box-shadow:0 8px 30px rgba(0,0,0,.12); }
    .sv-bar { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; font-size:12px; font-weight:600; color:#555; }
    .sv-track { height:5px; border-radius:5px; background:#e5e7eb; margin-bottom:16px; overflow:hidden; } .sv-track span { display:block; width:34%; height:100%; background:var(--primary); border-radius:5px; }
    .sv-screen .q-card { background:#fff; border-radius:16px; padding:20px 18px; margin-bottom:14px; box-shadow:0 1px 6px rgba(0,0,0,.07); }
    .sv-screen .q-card-label { font-size:10px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.8px; margin-bottom:10px; }
    .sv-screen .q-text { font-size:16px; font-weight:500; color:#111; line-height:1.65; overflow-wrap:anywhere; }
    .sv-screen .q-text .sv-empty { color:#c4c6cf; font-style:italic; font-weight:400; }
    .sv-screen .choices { display:flex; flex-direction:column; gap:10px; }
    .sv-screen .choice { display:flex; align-items:center; gap:14px; padding:16px; background:#fff; border:2px solid #e5e7eb; border-radius:14px; cursor:pointer; transition:border-color .15s, background .15s; }
    .sv-screen .choice.selected { border-color:var(--primary); background:var(--primary-light); }
    .sv-screen .choice-letter { width:34px; height:34px; border-radius:50%; background:#f3f4f6; border:2px solid #e5e7eb; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; color:#555; flex-shrink:0; }
    .sv-screen .choice.selected .choice-letter { background:var(--primary); border-color:var(--primary); color:#fff; }
    .sv-screen .choice-text { font-size:14px; color:#222; line-height:1.5; flex:1; overflow-wrap:anywhere; }
    .sv-screen .choice.reveal-correct { border-color:#10b981; background:#ecfdf5; pointer-events:none; }
    .sv-screen .choice.reveal-correct .choice-letter { background:#10b981; border-color:#10b981; color:#fff; }
    .sv-screen .choice.reveal-wrong { border-color:#ef4444; background:#fef2f2; pointer-events:none; }
    .sv-screen .choice.reveal-wrong .choice-letter { background:#ef4444; border-color:#ef4444; color:#fff; }
    .sv-screen .feedback-panel { display:none; margin-top:14px; border-radius:14px; padding:14px 16px; font-size:13px; font-weight:600; line-height:1.55; }
    .sv-screen .feedback-panel.visible { display:block; }
    .sv-screen .feedback-panel.correct { background:#ecfdf5; color:#065f46; border:1.5px solid #6ee7b7; }
    .sv-screen .feedback-panel.wrong { background:#fef2f2; color:#991b1b; border:1.5px solid #fca5a5; }
    .sv-screen .feedback-panel .explain-text { margin-top:8px; font-weight:400; color:#555; font-size:12px; }
    .sv-screen .q-text ul, .sv-screen .q-text ol, .sv-screen .explain-text ul, .sv-screen .explain-text ol { margin:.3em 0 .3em 1.5em; padding:0; }
    .sv-screen img { max-width:100%; height:auto; }
    .sv-screen table { border-collapse:collapse; max-width:100%; }
    .sv-screen h1 { font-size:1.5em; margin:.4em 0; } .sv-screen h2 { font-size:1.3em; margin:.4em 0; } .sv-screen h3 { font-size:1.15em; margin:.4em 0; } .sv-screen h4 { font-size:1em; margin:.4em 0; }
    .sv-hint { text-align:center; font-size:11.5px; color:#9a9cab; margin-top:12px; }
    </style>
    @include('partials.rich-editor')
</head>
<body>

@php
    $isChair ??= false;
    $backRoute = $isChair ? 'chair.ai-review' : 'faculty.test-bank';
    $backLabel = $isChair ? 'AI Substitute Review' : 'Test Bank';
@endphp

@if($isChair)
    @include('partials.chair-sidebar', ['active' => 'ai-review'])
@else
    @include('partials.faculty-sidebar', ['active' => 'add-question'])
@endif

<main class="main">
    <div class="topbar a0">
        <div class="topbar-left">
            <div>
                <div class="breadcrumb">
                    <a href="{{ route($backRoute) }}">{{ $backLabel }}</a>
                    <i class="fas fa-chevron-right" style="font-size:9px;"></i>
                    <span>{{ $editMode ? 'Edit Question' : 'Add Question' }}</span>
                </div>
                <div class="page-title">{{ $editMode ? 'Edit Question' : 'Add New Question' }}</div>
                <div class="page-sub">{{ $editMode ? 'Update question details and answers.' : 'Create a new question for the test bank.' }}</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route($backRoute) }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
            @include('partials.topbar-actions')
        </div>
    </div>

    <form action="{{ $editMode ? route($isChair ? 'chair.question.update' : 'faculty.question.update', $question->id) : route($isChair ? 'chair.question.store' : 'faculty.question.store') }}" method="POST" id="questionForm" enctype="multipart/form-data"
          data-confirm="{{ $editMode
              ? 'Your changes go live immediately and affect quizzes generated from this point on.'
              : 'This question is added to the test bank and can be served to students right away.' }}"
          data-confirm-title="{{ $editMode ? 'Save changes to this question?' : 'Publish this question?' }}"
          data-confirm-ok="{{ $editMode ? 'Yes, save changes' : 'Yes, publish it' }}"
          data-confirm-icon="question">
        @csrf
        @if($editMode) @method('PUT') @endif

        {{-- Validation errors surface as a SweetAlert popup via partials.alerts --}}

        @php
            $choicesByLabel = $editMode ? $question->choices->keyBy('choice_label') : collect();
            $diffEnumToLabel = ['easy' => 'Easy', 'moderate' => 'Medium', 'difficult' => 'Hard'];
            $curType = old('question_type', $editMode ? $question->question_type : '');
            $curDiff = old('difficulty', $editMode ? ($diffEnumToLabel[$question->difficulty] ?? 'Medium') : 'Medium');
            $tfCorrect = $editMode && $question->question_type === 'true_false'
                ? optional($question->choices->firstWhere('is_correct', true))->choice_text
                : null;
        @endphp
        <div class="form-layout a1">
            <!-- LEFT — MAIN FORM -->
            <div>
                <!-- AI DRAFT -->
                <div class="card ai-card" id="aiCard" style="border:1.5px dashed #ddd6fe; background:#faf8ff;">
                    <div class="ai-head">
                        <div class="card-title" style="margin-bottom:0;"><i class="fas fa-wand-magic-sparkles" style="color:#7c3aed;"></i> Draft with AI<x-tip label="How AI drafting works">The AI writes for the chosen topic and avoids repeating questions already in the bank. Its draft fills the form below for you to review before saving.</x-tip></div>
                        <button type="button" class="ai-toggle" id="aiToggle" aria-expanded="true" aria-controls="aiBody" title="Hide or show this section"><span>Hide</span> <i class="fas fa-chevron-up"></i></button>
                    </div>
                    <div class="ai-body" id="aiBody"><div class="ai-inner">
                    <p style="font-size:12px;color:#999;margin:18px 0 12px;">Pick a Subject and Topic on the right first.</p>
                    <div class="form-group">
                        <label>What should the question be about? <span style="font-size:11px;color:#aaa;">(optional)</span></label>
                        <textarea id="aiSeedIdea" placeholder="e.g. the lower of cost or NRV rule for inventory" style="min-height:60px;"></textarea>
                    </div>
                    <button type="button" class="btn" id="aiDraftBtn" style="background:#7c3aed;color:white;">
                        <i class="fas fa-wand-magic-sparkles"></i> Draft with AI
                    </button>
                    </div></div>
                </div>

                <!-- QUESTION TEXT -->
                <div class="card">
                    <div class="card-title" style="margin-bottom:14px;"><span class="step-no" aria-hidden="true">1</span> <i class="fas fa-question-circle"></i> Question <span class="req" title="Required">*</span></div>
                    <textarea name="question_text" class="rte-src" data-upload-url="{{ route($isChair ? 'chair.question.upload-image' : 'faculty.question.upload-image') }}" placeholder="Enter the full question here..." id="questionText">{{ old('question_text', $editMode ? $question->question_text : '') }}</textarea>
                </div>

                {{-- Questions saved before pictures and tables went inline keep their old attachment here. --}}
                @if(! empty($editMode) && ($question->exhibitImageUrl() || ! empty($question->table_data)))
                    <div class="card">
                        <div class="card-title"><i class="fas fa-paperclip"></i> Attached picture / table <span style="font-size:11px;color:#aaa;font-weight:500;">(older format &mdash; you can keep or remove it)</span></div>
                        @include('faculty.partials.question-exhibit-fields', ['legacy' => true])
                    </div>
                @endif

                <!-- ANSWERS: one card; the question type and difficulty are set here -->
                <div class="card" id="answerCard">
                    <div class="ans-head">
                        <div class="card-title" style="margin-bottom:0;"><span class="step-no" aria-hidden="true">2</span> <i class="fas fa-list-ul"></i> Answer / Choices <span class="req" title="Required">*</span></div>
                <div class="q-head-opts" id="qHeadOpts">
                    <label class="q-opt q-opt-type {{ $curType === '' ? 'empty' : '' }}" title="Question type (required)"><span class="req">*</span>
                        <select name="question_type" id="questionType" required onchange="handleTypeChange(this.value); this.parentElement.classList.toggle('empty', !this.value)" aria-label="Question type">
                            <option value="" disabled {{ $curType === '' ? 'selected' : '' }}>Select question type</option>
                            <option value="mcq" {{ $curType === 'mcq' ? 'selected' : '' }}>Multiple Choice (MCQ)</option>
                            <option value="true_false" {{ $curType === 'true_false' ? 'selected' : '' }}>True / False</option>
                        </select>
                    </label>
                    <label class="q-opt" title="Difficulty (required)"><span class="req">*</span>
                        <select name="difficulty" required aria-label="Difficulty">
                            <option {{ $curDiff === 'Easy' ? 'selected' : '' }}>Easy</option>
                            <option {{ $curDiff === 'Medium' ? 'selected' : '' }}>Medium</option>
                            <option {{ $curDiff === 'Hard' ? 'selected' : '' }}>Hard</option>
                        </select>
                    </label>
                </div>
                    </div>

                    <div class="ans-empty" id="ansEmpty"><i class="fas fa-arrow-up"></i> Choose a question type above to set up the answers.</div>

                    <!-- Multiple choice -->
                    <div id="mcqSection">
                        <p style="font-size:12px;color:#aaa;margin-bottom:14px;">Select the radio button next to the correct answer. You can add choices beyond D if needed.</p>
                        <div class="choices-list" id="choicesList"></div>
                        <button type="button" class="add-choice-btn" id="addChoiceBtn" style="margin-top:10px;"><i class="fas fa-plus"></i> Add choice</button>
                    </div>

                    <!-- True / False -->
                    <div id="tfSection" style="display:none;">
                        <p style="font-size:12px;color:#aaa;margin-bottom:12px;">Which statement is correct?</p>
                        <div class="radio-group">
                            <label class="radio-option"><input type="radio" name="tf_answer" value="true" {{ old('tf_answer', $tfCorrect === 'True' ? 'true' : '') === 'true' ? 'checked' : '' }}><span>True</span></label>
                            <label class="radio-option"><input type="radio" name="tf_answer" value="false" {{ old('tf_answer', $tfCorrect === 'False' ? 'false' : '') === 'false' ? 'checked' : '' }}><span>False</span></label>
                        </div>
                    </div>
                </div>

                <!-- EXPLANATION -->
                <div class="card">
                    <div class="card-title"><span class="step-no" aria-hidden="true">3</span> <i class="fas fa-lightbulb"></i> Explanation / Rationale</div>
                    <div class="form-group">
                        <label>Explanation <span style="font-size:11px;color:#aaa;">(shown after answering)</span></label>
                        <textarea name="explanation" class="rte-src" data-upload-url="{{ route($isChair ? 'chair.question.upload-image' : 'faculty.question.upload-image') }}" placeholder="Explain why the correct answer is correct. This helps students understand the concept." style="min-height:90px;">{{ old('explanation', $editMode ? $question->explanation : '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- RIGHT — META PANEL -->
            <div>
                <!-- PUBLISH -->
                <div class="side-card">
                    <div class="side-title">Publish</div>
                    <div class="meta-item">
                        <div class="toggle-row">
                            <span style="font-size:13px;color:#555;">Status</span>
                            <div class="toggle-wrap">
                                @php $isActive = old('is_active', $editMode ? $question->is_active : true); @endphp
                                <label class="toggle">
                                    <input type="checkbox" name="is_active" value="1" {{ $isActive ? 'checked' : '' }} id="statusToggle">
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-lbl" id="statusLabel">{{ $isActive ? 'Active' : 'Draft' }}</span>
                            </div>
                        </div>
                        <div class="help-tip" style="margin-top:6px;">Active questions appear in quizzes and exams.</div>
                    </div>
                    <div class="submit-actions" style="margin-top:18px;">
                        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                            <i class="fas fa-save"></i> {{ isset($editMode) ? 'Save Changes' : 'Save Question' }}
                        </button>
                        <button type="button" class="btn btn-ghost" style="width:100%;justify-content:center;" onclick="saveDraft()">
                            <i class="fas fa-file-alt"></i> Save as Draft
                        </button>
                    </div>
                </div>

                <!-- CLASSIFICATION -->
                <div class="side-card">
                    <div class="side-title">Classification</div>
                    @php $selectedTopic = old('topic_id', $editMode ? $question->topic_id : ''); @endphp
                    <div class="meta-item">
                        <label>Subject <span class="req">*</span></label>
                        <select id="subjectSelect" onchange="loadTopics(this.value)">
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ (string) old('subject', $editMode ? $currentSubject : '') === (string) $subject->id ? 'selected' : '' }}>{{ $subject->code }} – {{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="meta-item">
                        <label>Topic <span class="req">*</span></label>
                        <div class="topic-search"><i class="fas fa-magnifying-glass"></i><input type="search" id="topicSearch" placeholder="Search topics…" autocomplete="off" aria-label="Search topics" disabled></div>
                        <select name="topic_id" id="topicSelect" data-selected="{{ $selectedTopic }}">
                            <option value="">Select Subject First</option>
                        </select>
                        <div class="help-tip" id="topicCount"></div>
                    </div>
                </div>

                <!-- LIVE PREVIEW -->
                <div class="side-card">
                    <div class="side-title">Live Preview</div>
                    <div class="preview-box" id="previewBox">
                        <span class="preview-placeholder" id="previewPlaceholder">Start typing a question to see a preview...</span>
                        <span id="previewText" style="display:none;"></span>
                    </div>
                    <button type="button" class="btn btn-ghost" id="viewAsStudentBtn" style="width:100%;justify-content:center;margin-top:12px;">
                        <i class="fas fa-eye"></i> View as student
                    </button>
                </div>

                <!-- STATS (edit mode only) -->
                @if(!empty($editMode))
                <div class="side-card">
                    <div class="side-title">Question Stats</div>
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:12px;color:#888;">Times Answered</span>
                            <span style="font-size:13px;font-weight:700;color:#1a1a1a;">{{ number_format($stats['times_answered']) }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:12px;color:#888;">Correct Rate</span>
                            <span style="font-size:13px;font-weight:700;color:var(--green);">{{ $stats['correct_rate'] !== null ? $stats['correct_rate'].'%' : '—' }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:12px;color:#888;">Avg. Time Spent</span>
                            <span style="font-size:13px;font-weight:700;color:#1a1a1a;">{{ $stats['avg_time_secs'] !== null ? $stats['avg_time_secs'].' sec' : '—' }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:12px;color:#888;">Date Added</span>
                            <span style="font-size:12px;color:#aaa;">{{ $stats['date_added']->format('F j, Y') }}</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </form>
</main>


<!-- VIEW AS STUDENT -->
<div class="sv-overlay" id="svOverlay" role="dialog" aria-modal="true" aria-label="Student preview">
    <div class="sv-dialog">
        <div class="sv-top">
            <div><h3>Student preview</h3><small>How this question looks when a student answers it</small></div>
            <div class="sv-seg" id="svSeg">
                <button type="button" data-d="phone" class="on"><i class="fas fa-mobile-screen"></i> Phone</button>
                <button type="button" data-d="desktop"><i class="fas fa-desktop"></i> Desktop</button>
            </div>
            <button type="button" class="sv-close" id="svClose" aria-label="Close preview"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="sv-stage">
            <div class="sv-device phone" id="svDevice">
                <div class="sv-screen">
                    <div class="sv-bar"><span>Question 1 of 10</span><span><i class="far fa-clock"></i> 00:45</span></div>
                    <div class="sv-track"><span></span></div>
                    <div class="q-card">
                        <div class="q-card-label" id="svLabel">Multiple Choice</div>
                        <div class="q-text rt-fit" id="svText"></div>
                    </div>
                    <div class="choices" id="svChoices"></div>
                    <div class="feedback-panel" id="svFeedback"></div>
                    <div class="sv-hint">Tap an answer to see the feedback and explanation students get.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    RTE.init();

    // "Draft with AI" can be hidden to keep the form short; it starts open.
    const aiCard = document.getElementById('aiCard'), aiToggle = document.getElementById('aiToggle');
    aiToggle.addEventListener('click', () => {
        const hide = !aiCard.classList.contains('collapsed');
        aiCard.classList.toggle('collapsed', hide);
        aiToggle.setAttribute('aria-expanded', hide ? 'false' : 'true');
        aiToggle.querySelector('span').textContent = hide ? 'Show' : 'Hide';
    });

    // Live preview
    const qText = document.getElementById('questionText');
    const previewText = document.getElementById('previewText');
    const previewPlaceholder = document.getElementById('previewPlaceholder');
    if (qText) {
        qText.addEventListener('input', function() {
            if (RTE.toPlain(this.value)) {
                previewText.innerHTML = RTE.html(this.value);
                previewText.style.display = 'block';
                previewPlaceholder.style.display = 'none';
            } else {
                previewText.style.display = 'none';
                previewPlaceholder.style.display = 'inline';
            }
        });
        qText.dispatchEvent(new Event('input'));
    }

    // Status toggle label
    const toggle = document.getElementById('statusToggle');
    const lbl = document.getElementById('statusLabel');
    if (toggle) toggle.addEventListener('change', function() { lbl.textContent = this.checked ? 'Active' : 'Draft'; });

    // Initialise type sections and topic dropdown from current values.
    handleTypeChange(document.getElementById('questionType').value);
    loadTopics(document.getElementById('subjectSelect').value);
});

function handleTypeChange(type) {
    document.getElementById('mcqSection').style.display = type === 'mcq' ? 'block' : 'none';
    document.getElementById('tfSection').style.display  = type === 'true_false' ? 'block' : 'none';
    document.getElementById('ansEmpty').style.display   = type ? 'none' : 'block';
}

/* ── MCQ CHOICES: dynamic A..H list ── */
const MAX_CHOICES = {{ \App\Http\Controllers\Faculty\TestBankController::MAX_MCQ_CHOICES }};
const choicesList = document.getElementById('choicesList');
const addChoiceBtn = document.getElementById('addChoiceBtn');

@php
    $existingChoices = $editMode && $question->question_type === 'mcq'
        ? $question->choices->sortBy('choice_label')->map(fn($c) => ['text' => $c->choice_text, 'is_correct' => (bool) $c->is_correct])->values()
        : collect();
    $oldChoices = old('choices');
@endphp
let choices = @if($oldChoices)
    @json(collect($oldChoices)->map(fn($t, $i) => ['text' => $t, 'is_correct' => (int) old('correct_index') === (int) $i])->values())
@elseif($existingChoices->isNotEmpty())
    @json($existingChoices)
@else
    [{text:'', is_correct:true}, {text:'', is_correct:false}, {text:'', is_correct:false}, {text:'', is_correct:false}]
@endif;

function renderChoices() {
    const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    choicesList.innerHTML = choices.map((c, i) => {
        const label = String.fromCharCode(65 + i);
        return `<div class="choice-item">
            <div class="choice-letter">${label}</div>
            <input type="text" data-i="${i}" class="choice-text-input" placeholder="Choice ${label}" value="${esc(c.text)}">
            <input type="radio" class="choice-radio" name="correct_index_ui" value="${i}" ${c.is_correct ? 'checked' : ''} title="Mark as correct">
            <span class="correct-label">Correct</span>
            ${choices.length > 2 ? `<button type="button" class="choice-remove" data-remove="${i}" title="Remove choice"><i class="fas fa-times"></i></button>` : ''}
        </div>`;
    }).join('');
    addChoiceBtn.disabled = choices.length >= MAX_CHOICES;
}

choicesList.addEventListener('input', ev => {
    if (ev.target.classList.contains('choice-text-input')) {
        choices[+ev.target.dataset.i].text = ev.target.value;
    }
});
choicesList.addEventListener('change', ev => {
    if (ev.target.name === 'correct_index_ui') {
        choices.forEach((c, i) => c.is_correct = i === +ev.target.value);
    }
});
choicesList.addEventListener('click', ev => {
    const btn = ev.target.closest('[data-remove]'); if (!btn) return;
    const i = +btn.dataset.remove;
    const wasCorrect = choices[i].is_correct;
    choices.splice(i, 1);
    if (wasCorrect) choices[0].is_correct = true;
    renderChoices();
});
addChoiceBtn.addEventListener('click', () => {
    if (choices.length >= MAX_CHOICES) return;
    choices.push({ text: '', is_correct: false });
    renderChoices();
    choicesList.querySelector('.choice-item:last-child .choice-text-input')?.focus();
});

document.getElementById('questionForm').addEventListener('submit', () => {
    document.querySelectorAll('input[name="choices[]"], input[name="correct_index"]').forEach(el => el.remove());
    if (document.getElementById('questionType').value !== 'mcq') return;
    const form = document.getElementById('questionForm');
    choices.forEach(c => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'choices[]'; inp.value = c.text;
        form.appendChild(inp);
    });
    const correctIdx = choices.findIndex(c => c.is_correct);
    const ci = document.createElement('input');
    ci.type = 'hidden'; ci.name = 'correct_index'; ci.value = correctIdx >= 0 ? correctIdx : 0;
    form.appendChild(ci);
});

renderChoices();

const topicMap = @json($subjects->mapWithKeys(fn($s) => [$s->id => $s->topics->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->values()]));

let topicList = [], topicChosen = '';
function renderTopics() {
    const sel = document.getElementById('topicSelect'), search = document.getElementById('topicSearch');
    const q = search.value.trim().toLowerCase();
    const matches = topicList.filter(t => !q || t.name.toLowerCase().includes(q));
    sel.innerHTML = '';
    const first = document.createElement('option');
    first.value = '';
    first.textContent = !topicList.length ? 'Select Subject First' : (q && !matches.length ? 'No topics match your search' : 'Select Topic');
    sel.appendChild(first);
    // The topic already chosen stays in the list even when the search hides it, so the form never loses it.
    const shown = matches.slice();
    const chosen = topicList.find(t => String(t.id) === String(topicChosen));
    if (chosen && !shown.includes(chosen)) shown.unshift(chosen);
    shown.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t.id;
        opt.textContent = t.name;
        if (String(t.id) === String(topicChosen)) opt.selected = true;
        sel.appendChild(opt);
    });
    if (q && matches.length) sel.setAttribute('size', Math.min(7, shown.length + 1)); else sel.removeAttribute('size');
    document.getElementById('topicCount').textContent = !topicList.length ? '' : (q ? matches.length + ' of ' + topicList.length + ' topics' : topicList.length + (topicList.length === 1 ? ' topic' : ' topics'));
}
function loadTopics(subjectId) {
    const sel = document.getElementById('topicSelect'), search = document.getElementById('topicSearch');
    if (topicChosen === '' && sel.dataset.selected) topicChosen = String(sel.dataset.selected);
    topicList = topicMap[subjectId] || [];
    if (!topicList.some(t => String(t.id) === String(topicChosen))) topicChosen = '';
    search.value = '';
    search.disabled = !topicList.length;
    renderTopics();
}
(function () {
    const sel = document.getElementById('topicSelect'), search = document.getElementById('topicSearch');
    search.addEventListener('input', renderTopics);
    search.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const q = search.value.trim().toLowerCase(), hit = topicList.find(t => !q || t.name.toLowerCase().includes(q));
            if (hit) { topicChosen = String(hit.id); search.value = ''; renderTopics(); }
        }
        if (e.key === 'Escape') { search.value = ''; renderTopics(); }
    });
    sel.addEventListener('change', () => { topicChosen = sel.value; if (search.value) { search.value = ''; renderTopics(); } });
    document.getElementById('subjectSelect').addEventListener('change', () => { topicChosen = ''; sel.dataset.selected = ''; });
})();

const AI_DRAFT_URL = @json(route($isChair ? 'chair.question.ai-draft' : 'faculty.question.ai-draft'));
const AI_CSRF = @json(csrf_token());

document.getElementById('aiDraftBtn').addEventListener('click', async () => {
    const btn = document.getElementById('aiDraftBtn');
    const topicId = document.getElementById('topicSelect').value;
    const difficulty = document.querySelector('select[name="difficulty"]').value;
    const questionType = document.getElementById('questionType').value;
    const seedIdea = document.getElementById('aiSeedIdea').value.trim();

    if (!questionType) {
        CPACE.warning('Choose a question type first', 'Pick Multiple Choice or True / False in the Answer / Choices card, then try again.');
        return;
    }

    if (!topicId) {
        CPACE.warning('Pick a Subject and Topic first', 'The AI needs to know which topic to write for — select them in the Classification panel, then try again.');
        return;
    }

    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Drafting...';

    try {
        const res = await fetch(AI_DRAFT_URL, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': AI_CSRF,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                topic_id: topicId,
                difficulty: difficulty,
                question_type: questionType,
                seed_idea: seedIdea || null,
            }),
        });

        const data = await res.json();
        if (!res.ok) {
            if (data.not_assigned) {
                CPACE.warning('Not your subject', data.message);
            } else {
                CPACE.error('Draft not generated', data.message || 'The AI could not draft a question right now. Please try again.');
            }
            return;
        }

        document.getElementById('questionText').value = data.question_text || '';
        RTE.sync(document.getElementById('questionText'));
        document.getElementById('questionText').dispatchEvent(new Event('input'));
        document.querySelector('textarea[name="explanation"]').value = data.explanation || '';
        RTE.sync(document.querySelector('textarea[name="explanation"]'));

        if (questionType === 'true_false') {
            const radio = document.querySelector(`input[name="tf_answer"][value="${data.tf_answer ? 'true' : 'false'}"]`);
            if (radio) radio.checked = true;
        } else if (data.choices) {
            choices = Object.keys(data.choices).sort().map(label => ({
                text: data.choices[label].text || '',
                is_correct: !!data.choices[label].is_correct,
            }));
            renderChoices();
        }

        CPACE.toast('AI draft added — review it before saving.', 'success');
    } catch (e) {
        CPACE.error('Draft not generated', 'The AI could not draft a question right now. Please try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = original;
    }
});

function saveDraft() {
    CPACE.confirm({
        title: 'Save as draft?',
        text: 'The question is stored but stays inactive, so students will not see it until you publish it.',
        icon: 'question',
        confirmText: 'Yes, save draft',
    }).then(ok => {
        if (!ok) return;
        document.querySelector('input[name="is_active"]').checked = false;
        // Native submit() intentionally skips the form's data-confirm handler —
        // this flow has already been confirmed above.
        document.getElementById('questionForm').submit();
    });
}
/* ── VIEW AS STUDENT ── */
(function () {
    const $ = id => document.getElementById(id);
    const overlay = $('svOverlay');
    const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    let picked = null;

    function currentChoices() {
        if ($('questionType').value === 'true_false') {
            const ans = document.querySelector('input[name="tf_answer"]:checked');
            return [{ text: 'True', is_correct: ans ? ans.value === 'true' : false }, { text: 'False', is_correct: ans ? ans.value === 'false' : false }];
        }
        return choices.filter(c => (c.text || '').trim() !== '').map(c => ({ text: c.text, is_correct: !!c.is_correct }));
    }
    const keepShapes = el => el.querySelectorAll('[style*="inline-block"]').forEach(e => { const w = parseFloat(e.style.width), h = parseFloat(e.style.height); if (w && h) e.style.aspectRatio = w + ' / ' + h; });
    function draw() {
        const tf = $('questionType').value === 'true_false';
        $('svLabel').textContent = tf ? 'True / False' : 'Multiple Choice';
        const qv = $('questionText').value;
        $('svText').innerHTML = RTE.toPlain(qv) || /<(img|table)/i.test(qv) ? RTE.html(qv) : '<span class="sv-empty">Your question will appear here&hellip;</span>';
        keepShapes($('svText'));
        const list = currentChoices();
        $('svChoices').innerHTML = list.map((c, i) => `<div class="choice" data-i="${i}"><div class="choice-letter">${tf ? (i ? 'F' : 'T') : String.fromCharCode(65 + i)}</div><span class="choice-text">${esc(c.text)}</span></div>`).join('')
            || '<div class="choice"><div class="choice-letter">A</div><span class="choice-text" style="color:#c4c6cf">Add answer choices to preview them</span></div>';
        const fb = $('svFeedback'); fb.className = 'feedback-panel'; fb.innerHTML = '';
        picked = null;
    }
    function answer(i) {
        const list = currentChoices(), ok = list[i] && list[i].is_correct;
        $('svChoices').querySelectorAll('.choice').forEach((el, k) => {
            el.classList.remove('selected', 'reveal-correct', 'reveal-wrong');
            if (list[k] && list[k].is_correct) el.classList.add('reveal-correct');
            else if (k === i) el.classList.add('reveal-wrong');
        });
        const ex = document.querySelector('textarea[name="explanation"]').value;
        const fb = $('svFeedback');
        fb.className = 'feedback-panel visible ' + (ok ? 'correct' : 'wrong');
        fb.innerHTML = (ok ? '<i class="fas fa-check-circle"></i> <strong>Correct!</strong>' : '<i class="fas fa-times-circle"></i> <strong>Incorrect.</strong>')
            + (RTE.toPlain(ex) || /<(img|table)/i.test(ex) ? '<div class="explain-text rt-fit"><i class="fas fa-lightbulb"></i> ' + RTE.html(ex) + '</div>' : '');
        keepShapes(fb);
    }
    $('viewAsStudentBtn').addEventListener('click', () => { draw(); overlay.classList.add('open'); });
    $('svClose').addEventListener('click', () => overlay.classList.remove('open'));
    overlay.addEventListener('mousedown', e => { if (e.target === overlay) overlay.classList.remove('open'); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') overlay.classList.remove('open'); });
    $('svChoices').addEventListener('click', e => { const c = e.target.closest('.choice[data-i]'); if (c) answer(+c.dataset.i); });
    $('svSeg').addEventListener('click', e => {
        const b = e.target.closest('button[data-d]'); if (!b) return;
        $('svSeg').querySelectorAll('button').forEach(x => x.classList.toggle('on', x === b));
        $('svDevice').classList.toggle('phone', b.dataset.d === 'phone');
    });
})();
</script>

    @include('partials.alerts')
</body>
</html>
