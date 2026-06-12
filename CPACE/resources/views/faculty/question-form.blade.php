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

        /* SIDEBAR */
        .sidebar { background:var(--primary); position:fixed; width:230px; height:100vh; display:flex; flex-direction:column; overflow-y:auto; overflow-x:hidden; z-index:1000; transition:width .3s; }
        .sidebar.collapsed { width:70px; }
        .sidebar-header { padding:20px 18px 16px; border-bottom:1px solid rgba(255,255,255,.12); }
        .sidebar-brand { display:flex; align-items:center; gap:10px; }
        .brand-circle { width:42px; height:42px; flex-shrink:0; background:rgba(255,255,255,.15); border:2px solid rgba(255,255,255,.3); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px; color:white; }
        .brand-text strong { display:block; font-size:14px; font-weight:700; color:white; }
        .brand-text small { font-size:10px; color:rgba(255,255,255,.7); }
        .faculty-badge { display:inline-block; margin-top:6px; background:rgba(255,255,255,.2); color:white; font-size:9px; font-weight:700; padding:2px 8px; border-radius:20px; letter-spacing:1px; text-transform:uppercase; }
        .sidebar.collapsed .brand-text, .sidebar.collapsed .faculty-badge { display:none; }
        .sidebar-nav { list-style:none; flex:1; padding:10px 0; }
        .sidebar-nav .nav-group-label { padding:14px 22px 4px; font-size:9px; font-weight:700; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:1.2px; }
        .sidebar.collapsed .nav-group-label { display:none; }
        .sidebar-nav li a { display:flex; align-items:center; gap:12px; padding:10px 22px; color:rgba(255,255,255,.72); text-decoration:none; font-size:13px; border-left:3px solid transparent; transition:all .2s; }
        .sidebar-nav li a:hover { color:white; background:rgba(255,255,255,.1); }
        .sidebar-nav li a.active { color:white; background:rgba(255,255,255,.18); border-left-color:white; font-weight:500; }
        .sidebar-nav li a i { width:18px; text-align:center; font-size:14px; flex-shrink:0; }
        .sidebar.collapsed .sidebar-nav li a { padding:10px 0; justify-content:center; gap:0; }
        .sidebar.collapsed .sidebar-nav li a span { display:none; }
        .nav-badge { margin-left:auto; background:var(--accent); color:white; font-size:9px; font-weight:700; padding:2px 6px; border-radius:20px; min-width:18px; text-align:center; }
        .sidebar.collapsed .nav-badge { display:none; }
        .sidebar-footer { border-top:1px solid rgba(255,255,255,.12); padding:14px 18px; }
        .user-row { display:flex; align-items:center; gap:10px; }
        .user-av { width:36px; height:36px; border-radius:50%; background:var(--accent); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; color:white; flex-shrink:0; }
        .user-info .un { display:block; font-size:12px; font-weight:600; color:white; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .user-info .ur { display:block; font-size:10px; color:rgba(255,255,255,.6); }
        .sidebar.collapsed .user-info, .sidebar.collapsed .user-chevron { display:none; }

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

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-circle"><i class="fas fa-shield-alt"></i></div>
            <div class="brand-text"><strong>CPACE</strong><small>CPA Reviewer</small></div>
        </div>
        <div class="faculty-badge">Faculty Portal</div>
    </div>
    <ul class="sidebar-nav">
        <li class="nav-group-label">Main</li>
        <li><a href="{{ route('faculty.dashboard') }}"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
        <li class="nav-group-label">Content</li>
        <li><a href="{{ route('faculty.test-bank') }}"><i class="fas fa-database"></i><span>Test Bank</span><span class="nav-badge">1,543</span></a></li>
        <li><a href="{{ route('faculty.question.create') }}" class="active"><i class="fas fa-plus-circle"></i><span>Add Question</span></a></li>
        <li><a href="{{ route('faculty.subjects') }}"><i class="fas fa-book-open"></i><span>Subjects &amp; Topics</span></a></li>
        <li class="nav-group-label">Analytics</li>
        <li><a href="{{ route('faculty.performance') }}"><i class="fas fa-users"></i><span>Student Performance</span></a></li>
        <li><a href="#"><i class="fas fa-chart-line"></i><span>Reports</span></a></li>
        <li class="nav-group-label">System</li>
        <li><a href="#"><i class="fas fa-cog"></i><span>Settings</span></a></li>
        <li><a href="{{ route('dashboard') }}"><i class="fas fa-arrow-left"></i><span>Student View</span></a></li>
    </ul>
    <div class="sidebar-footer">
        <div class="user-row">
            <div class="user-av">KD</div>
            <div class="user-info">
                <span class="un">{{ Auth::user()->name }}</span>
                <span class="ur">Faculty</span>
            </div>
            <i class="fas fa-chevron-down user-chevron" style="color:rgba(255,255,255,.5);font-size:10px;"></i>
        </div>
    </div>
</aside>

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

    <form action="#" method="POST" id="questionForm">
        @csrf
        <div class="form-layout a1">
            <!-- LEFT — MAIN FORM -->
            <div>
                <!-- QUESTION TEXT -->
                <div class="card">
                    <div class="card-title"><i class="fas fa-question-circle"></i> Question</div>
                    <div class="form-group">
                        <label>Question Text <span class="req">*</span></label>
                        <textarea name="question_text" placeholder="Enter the full question here..." id="questionText">{{ isset($editMode) ? 'Under PFRS 15, revenue is recognized when or as performance obligations are satisfied. Which of the following best describes when a performance obligation is satisfied?' : '' }}</textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Question Type <span class="req">*</span></label>
                            <select name="type" id="questionType" onchange="handleTypeChange(this.value)">
                                <option value="mcq" {{ !isset($editMode) ? 'selected' : '' }}>Multiple Choice (MCQ)</option>
                                <option value="tf">True / False</option>
                                <option value="id">Identification</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Difficulty <span class="req">*</span></label>
                            <select name="difficulty">
                                <option>Easy</option>
                                <option selected>Medium</option>
                                <option>Hard</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ANSWER CHOICES (MCQ) -->
                <div class="card" id="mcqSection">
                    <div class="card-title"><i class="fas fa-list-ul"></i> Answer Choices</div>
                    <p style="font-size:12px;color:#aaa;margin-bottom:14px;">Select the radio button next to the correct answer.</p>
                    <div class="choices-list">
                        <div class="choice-item">
                            <div class="choice-letter cl-a">A</div>
                            <input type="text" name="choice_a" placeholder="Choice A" value="{{ isset($editMode) ? 'When the customer pays for the good or service' : '' }}">
                            <input type="radio" class="choice-radio" name="correct_answer" value="a" title="Mark as correct">
                            <span class="correct-label">Correct</span>
                        </div>
                        <div class="choice-item">
                            <div class="choice-letter cl-b">B</div>
                            <input type="text" name="choice_b" placeholder="Choice B" value="{{ isset($editMode) ? 'When the contract is signed by both parties' : '' }}">
                            <input type="radio" class="choice-radio" name="correct_answer" value="b">
                            <span class="correct-label">Correct</span>
                        </div>
                        <div class="choice-item">
                            <div class="choice-letter cl-c">C</div>
                            <input type="text" name="choice_c" placeholder="Choice C" value="{{ isset($editMode) ? 'When the entity transfers control of a promised good or service' : '' }}">
                            <input type="radio" class="choice-radio" name="correct_answer" value="c" checked>
                            <span class="correct-label">Correct</span>
                        </div>
                        <div class="choice-item">
                            <div class="choice-letter cl-d">D</div>
                            <input type="text" name="choice_d" placeholder="Choice D" value="{{ isset($editMode) ? 'When the invoice is issued to the customer' : '' }}">
                            <input type="radio" class="choice-radio" name="correct_answer" value="d">
                            <span class="correct-label">Correct</span>
                        </div>
                    </div>
                </div>

                <!-- TRUE/FALSE (hidden by default) -->
                <div class="card" id="tfSection" style="display:none;">
                    <div class="card-title"><i class="fas fa-check-square"></i> True / False Answer</div>
                    <div class="radio-group">
                        <label class="radio-option"><input type="radio" name="tf_answer" value="true"><span>True</span></label>
                        <label class="radio-option"><input type="radio" name="tf_answer" value="false"><span>False</span></label>
                    </div>
                </div>

                <!-- IDENTIFICATION (hidden by default) -->
                <div class="card" id="idSection" style="display:none;">
                    <div class="card-title"><i class="fas fa-pen"></i> Correct Answer</div>
                    <div class="form-group">
                        <label>Expected Answer <span class="req">*</span></label>
                        <input type="text" name="id_answer" placeholder="Enter the correct answer keyword or phrase">
                        <div class="help-tip">The system will check for this exact phrase (case-insensitive).</div>
                    </div>
                </div>

                <!-- EXPLANATION -->
                <div class="card">
                    <div class="card-title"><i class="fas fa-lightbulb"></i> Explanation / Rationale</div>
                    <div class="form-group">
                        <label>Explanation <span style="font-size:11px;color:#aaa;">(shown after answering)</span></label>
                        <textarea name="explanation" placeholder="Explain why the correct answer is correct. This helps students understand the concept." style="min-height:90px;">{{ isset($editMode) ? 'Under PFRS 15, an entity satisfies a performance obligation by transferring a promised good or service to a customer. A good or service is transferred when (or as) the customer obtains control of that asset.' : '' }}</textarea>
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
                                <label class="toggle">
                                    <input type="checkbox" name="is_active" checked id="statusToggle">
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-lbl" id="statusLabel">Active</span>
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
                    <div class="meta-item">
                        <label>Subject <span class="req">*</span></label>
                        <select name="subject" id="subjectSelect" onchange="loadTopics(this.value)">
                            <option value="">Select Subject</option>
                            <option value="far" {{ isset($editMode) ? 'selected' : '' }}>FAR – Financial Accounting &amp; Reporting</option>
                            <option value="aud">AUD – Auditing</option>
                            <option value="tax">TAX – Taxation</option>
                            <option value="ms">MS – Management Services</option>
                            <option value="rfbt">RFBT – Regulatory Framework</option>
                            <option value="afar">AFAR – Advanced Financial Accounting</option>
                        </select>
                    </div>
                    <div class="meta-item">
                        <label>Topic</label>
                        <select name="topic" id="topicSelect">
                            <option value="">Select Subject First</option>
                            <option value="revenue" {{ isset($editMode) ? 'selected' : '' }}>Revenue Recognition</option>
                        </select>
                    </div>
                    <div class="meta-item">
                        <label>Tags <span style="font-size:11px;color:#aaa;">(optional, comma-separated)</span></label>
                        <input type="text" name="tags" placeholder="e.g. pfrs15, revenue, control" value="{{ isset($editMode) ? 'pfrs15, revenue recognition, control' : '' }}">
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
});

function handleTypeChange(type) {
    document.getElementById('mcqSection').style.display = type === 'mcq' ? 'block' : 'none';
    document.getElementById('tfSection').style.display  = type === 'tf'  ? 'block' : 'none';
    document.getElementById('idSection').style.display  = type === 'id'  ? 'block' : 'none';
}

const topicMap = {
    far:  ['Revenue Recognition','Financial Instruments','Inventory','Property, Plant & Equipment','Leases','Business Combinations'],
    aud:  ['Audit Risk','Internal Controls','Audit Evidence','Audit Reports','Professional Ethics'],
    tax:  ['Income Tax','VAT','Estate Tax','Donor\'s Tax','Business Taxes'],
    ms:   ['Cost Accounting','Capital Budgeting','Macroeconomics','Financial Management'],
    rfbt: ['Contracts','Business Organizations','Taxation Law','Securities Regulation'],
    afar: ['Leases','Derivatives','Foreign Currency','Partnership Accounting'],
};
function loadTopics(subject) {
    const sel = document.getElementById('topicSelect');
    sel.innerHTML = '<option value="">Select Topic</option>';
    (topicMap[subject] || []).forEach(t => {
        const opt = document.createElement('option');
        opt.value = t.toLowerCase().replace(/\s+/g,'-');
        opt.textContent = t;
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
