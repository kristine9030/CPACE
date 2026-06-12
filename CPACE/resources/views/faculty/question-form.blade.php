<!DOCTYPE html>
<html lang="en">
<head>
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
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
        .topbar-left { display:flex; align-items:center; gap:12px; }
        .toggle-btn { width:36px; height:36px; border:1px solid #ddd; background:white; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--primary); font-size:15px; }
        .page-title { font-size:26px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .breadcrumb { display:flex; align-items:center; gap:6px; font-size:12px; color:#aaa; margin-bottom:4px; }
        .breadcrumb a { color:var(--accent); text-decoration:none; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:white; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }

        /* FORM LAYOUT */
        .form-layout { display:grid; grid-template-columns:1fr 320px; gap:20px; align-items:start; }

        /* CARDS */
        .card { background:white; border-radius:14px; padding:24px; margin-bottom:20px; }
        .card:last-child { margin-bottom:0; }
        .card-title { font-size:14px; font-weight:700; color:#1a1a1a; margin-bottom:18px; display:flex; align-items:center; gap:8px; }
        .card-title i { color:var(--accent); }

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

        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .form-row-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; }

        /* ANSWER CHOICES */
        .choices-list { display:flex; flex-direction:column; gap:10px; }
        .choice-item { display:flex; align-items:center; gap:10px; }
        .choice-letter {
            width:32px; height:32px; border-radius:50%; flex-shrink:0;
            display:flex; align-items:center; justify-content:center;
            font-size:12px; font-weight:700;
        }
        .cl-a { background:#dbeafe; color:#2563eb; }
        .cl-b { background:#d1fae5; color:#059669; }
        .cl-c { background:#fef3c7; color:#d97706; }
        .cl-d { background:#fde8e8; color:var(--accent); }
        .choice-item input[type=text] { flex:1; }
        .choice-radio { flex-shrink:0; width:16px; height:16px; accent-color:var(--primary); cursor:pointer; }
        .correct-label { font-size:11px; color:#aaa; white-space:nowrap; }

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

        .preview-box {
            background:#f8f8f8; border-radius:10px; padding:16px;
            font-size:13px; color:#444; line-height:1.7; min-height:80px;
            border:1px dashed #e0e0e0;
        }
        .preview-placeholder { color:#ccc; font-style:italic; }

        .submit-actions { display:flex; flex-direction:column; gap:10px; }

        @keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
        .a0{animation:fadeUp .4s ease both} .a1{animation:fadeUp .4s .07s ease both}
    </style>
</head>
<body>

@include('partials.faculty-sidebar', ['active' => 'add-question'])

<main class="main">
    <div class="topbar a0">
        <div class="topbar-left">
            <button class="toggle-btn" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <div>
                <div class="breadcrumb">
                    <a href="{{ route('faculty.test-bank') }}">Test Bank</a>
                    <i class="fas fa-chevron-right" style="font-size:9px;"></i>
                    <span>{{ isset($editMode) ? 'Edit Question' : 'Add Question' }}</span>
                </div>
                <div class="page-title">{{ isset($editMode) ? 'Edit Question' : 'Add New Question' }}</div>
                <div class="page-sub">{{ isset($editMode) ? 'Update question details and answers.' : 'Create a new question for the test bank.' }}</div>
            </div>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
            <a href="{{ route('faculty.test-bank') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-ghost" style="color:#c0392b;border-color:#c0392b;">
                    <i class="fas fa-sign-out-alt"></i> Sign Out
                </button>
            </form>
        </div>
    </div>

    <form action="{{ $editMode ? route('faculty.question.update', $question->id) : route('faculty.question.store') }}" method="POST" id="questionForm">
        @csrf
        @if($editMode) @method('PUT') @endif

        @if($errors->any())
            <div style="background:#fde8e8;color:var(--accent);padding:12px 18px;border-radius:10px;margin-bottom:18px;font-size:13px;">
                <strong><i class="fas fa-exclamation-circle"></i> Please fix the following:</strong>
                <ul style="margin:6px 0 0 18px;">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        @php
            $choicesByLabel = $editMode ? $question->choices->keyBy('choice_label') : collect();
            $diffEnumToLabel = ['easy' => 'Easy', 'moderate' => 'Medium', 'difficult' => 'Hard'];
            $curType = old('question_type', $editMode ? $question->question_type : 'mcq');
            $curDiff = old('difficulty', $editMode ? ($diffEnumToLabel[$question->difficulty] ?? 'Medium') : 'Medium');
            $tfCorrect = $editMode && $question->question_type === 'true_false'
                ? optional($question->choices->firstWhere('is_correct', true))->choice_text
                : null;
        @endphp
        <div class="form-layout a1">
            <!-- LEFT — MAIN FORM -->
            <div>
                <!-- QUESTION TEXT -->
                <div class="card">
                    <div class="card-title"><i class="fas fa-question-circle"></i> Question</div>
                    <div class="form-group">
                        <label>Question Text <span class="req">*</span></label>
                        <textarea name="question_text" placeholder="Enter the full question here..." id="questionText">{{ old('question_text', $editMode ? $question->question_text : '') }}</textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Question Type <span class="req">*</span></label>
                            <select name="question_type" id="questionType" onchange="handleTypeChange(this.value)">
                                <option value="mcq" {{ $curType === 'mcq' ? 'selected' : '' }}>Multiple Choice (MCQ)</option>
                                <option value="true_false" {{ $curType === 'true_false' ? 'selected' : '' }}>True / False</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Difficulty <span class="req">*</span></label>
                            <select name="difficulty">
                                <option {{ $curDiff === 'Easy' ? 'selected' : '' }}>Easy</option>
                                <option {{ $curDiff === 'Medium' ? 'selected' : '' }}>Medium</option>
                                <option {{ $curDiff === 'Hard' ? 'selected' : '' }}>Hard</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ANSWER CHOICES (MCQ) -->
                <div class="card" id="mcqSection">
                    <div class="card-title"><i class="fas fa-list-ul"></i> Answer Choices</div>
                    <p style="font-size:12px;color:#aaa;margin-bottom:14px;">Select the radio button next to the correct answer.</p>
                    <div class="choices-list">
                        @foreach(['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $key => $label)
                        @php $choice = $choicesByLabel[$label] ?? null; @endphp
                        <div class="choice-item">
                            <div class="choice-letter cl-{{ $key }}">{{ $label }}</div>
                            <input type="text" name="choice_{{ $key }}" placeholder="Choice {{ $label }}" value="{{ old('choice_'.$key, $choice->choice_text ?? '') }}">
                            <input type="radio" class="choice-radio" name="correct_answer" value="{{ $key }}" {{ old('correct_answer', optional($choice)->is_correct ? $key : '') === $key ? 'checked' : '' }} title="Mark as correct">
                            <span class="correct-label">Correct</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- TRUE/FALSE (hidden by default) -->
                <div class="card" id="tfSection" style="display:none;">
                    <div class="card-title"><i class="fas fa-check-square"></i> True / False Answer</div>
                    <div class="radio-group">
                        <label class="radio-option"><input type="radio" name="tf_answer" value="true" {{ old('tf_answer', $tfCorrect === 'True' ? 'true' : '') === 'true' ? 'checked' : '' }}><span>True</span></label>
                        <label class="radio-option"><input type="radio" name="tf_answer" value="false" {{ old('tf_answer', $tfCorrect === 'False' ? 'false' : '') === 'false' ? 'checked' : '' }}><span>False</span></label>
                    </div>
                </div>

                <!-- EXPLANATION -->
                <div class="card">
                    <div class="card-title"><i class="fas fa-lightbulb"></i> Explanation / Rationale</div>
                    <div class="form-group">
                        <label>Explanation <span style="font-size:11px;color:#aaa;">(shown after answering)</span></label>
                        <textarea name="explanation" placeholder="Explain why the correct answer is correct. This helps students understand the concept." style="min-height:90px;">{{ old('explanation', $editMode ? $question->explanation : '') }}</textarea>
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
                        <select name="topic_id" id="topicSelect" data-selected="{{ $selectedTopic }}">
                            <option value="">Select Subject First</option>
                        </select>
                    </div>
                </div>

                <!-- LIVE PREVIEW -->
                <div class="side-card">
                    <div class="side-title">Live Preview</div>
                    <div class="preview-box" id="previewBox">
                        <span class="preview-placeholder" id="previewPlaceholder">Start typing a question to see a preview...</span>
                        <span id="previewText" style="display:none;"></span>
                    </div>
                </div>

                <!-- STATS (edit mode only) -->
                @if(isset($editMode))
                <div class="side-card">
                    <div class="side-title">Question Stats</div>
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:12px;color:#888;">Times Answered</span>
                            <span style="font-size:13px;font-weight:700;color:#1a1a1a;">1,284</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:12px;color:#888;">Correct Rate</span>
                            <span style="font-size:13px;font-weight:700;color:var(--green);">68%</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:12px;color:#888;">Avg. Time Spent</span>
                            <span style="font-size:13px;font-weight:700;color:#1a1a1a;">48 sec</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:12px;color:#888;">Date Added</span>
                            <span style="font-size:12px;color:#aaa;">June 9, 2026</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('sidebarToggle');
    const sb  = document.getElementById('sidebar');
    if (btn) btn.addEventListener('click', () => { sb.classList.toggle('collapsed'); localStorage.setItem('facultySidebar', sb.classList.contains('collapsed')); });
    if (localStorage.getItem('facultySidebar') === 'true') sb.classList.add('collapsed');

    // Live preview
    const qText = document.getElementById('questionText');
    const previewText = document.getElementById('previewText');
    const previewPlaceholder = document.getElementById('previewPlaceholder');
    if (qText) {
        qText.addEventListener('input', function() {
            if (this.value.trim()) {
                previewText.textContent = this.value.trim();
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
}

const topicMap = @json($subjects->mapWithKeys(fn($s) => [$s->id => $s->topics->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->values()]));

function loadTopics(subjectId) {
    const sel = document.getElementById('topicSelect');
    const preselect = String(sel.dataset.selected || '');
    sel.innerHTML = '<option value="">Select Topic</option>';
    (topicMap[subjectId] || []).forEach(t => {
        const opt = document.createElement('option');
        opt.value = t.id;
        opt.textContent = t.name;
        if (String(t.id) === preselect) opt.selected = true;
        sel.appendChild(opt);
    });
}

function saveDraft() {
    document.querySelector('input[name="is_active"]').checked = false;
    document.getElementById('questionForm').submit();
}
</script>
</body>
</html>
