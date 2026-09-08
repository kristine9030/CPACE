<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Questions - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:16px; }
        .page-title { font-size:26px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-primary:disabled { background:#c9a9a9; cursor:not-allowed; }
        .btn-ghost { background:white; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }

        .layout { display:grid; grid-template-columns:1fr 380px; gap:20px; align-items:start; width:100%; }
        @media (max-width:960px) { .layout { grid-template-columns:1fr; } }

        .card { background:white; border-radius:14px; padding:26px; }
        .field { margin-bottom:20px; }
        .field label { display:block; font-size:13px; font-weight:600; color:#444; margin-bottom:8px; }
        select { width:100%; font-family:'Poppins',sans-serif; font-size:13px; border:1px solid #e0e0e0; border-radius:8px; padding:10px 12px; color:#555; background:white; outline:none; }
        select:focus { border-color:var(--primary); }

        .drop-zone { border:2px dashed #d8d8d8; border-radius:12px; padding:36px 20px; text-align:center; cursor:pointer; transition:all .2s; }
        .drop-zone.drag { border-color:var(--primary); background:var(--primary-light); }
        .drop-zone i { font-size:34px; color:#bbb; margin-bottom:10px; }
        .drop-zone .dz-title { font-size:14px; font-weight:600; color:#444; margin-bottom:4px; }
        .drop-zone .dz-sub { font-size:12px; color:#999; }
        .drop-zone.has-file { border-color:var(--green); background:#f0fdf4; }
        .drop-zone.has-file i { color:var(--green); }
        input[type=file] { display:none; }

        .format-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:8px; margin-top:16px; }
        .format-chip { border:1px solid #eee; border-radius:9px; padding:10px 6px; text-align:center; font-size:11px; color:#777; text-decoration:none; display:block; transition:all .15s; cursor:pointer; }
        .format-chip i { display:block; font-size:16px; margin-bottom:5px; color:var(--primary); }
        .format-chip:hover { border-color:var(--primary); background:var(--primary-light); }
        .format-chip.no-link { cursor:default; }
        .format-chip.no-link:hover { border-color:#eee; background:transparent; }
        .dl-hint { display:block; margin-top:5px; font-size:9px; font-weight:700; color:var(--primary); text-transform:uppercase; letter-spacing:.3px; opacity:0; transition:opacity .15s; }
        .format-chip:hover .dl-hint { opacity:1; }
        .dl-hint-static { font-weight:500; text-transform:none; color:#aaa; letter-spacing:0; font-size:9.5px; opacity:1 !important; }
        .csv-hint { font-size:11px; color:#999; margin-top:10px; text-align:right; }
        .csv-hint a { color:var(--primary); font-weight:600; text-decoration:none; }
        .csv-hint a:hover { text-decoration:underline; }

        .note { background:#eef6ff; border:1px solid #d6e9ff; border-radius:10px; padding:12px 14px; font-size:12px; color:#3a5f82; margin-top:18px; display:flex; gap:10px; align-items:flex-start; }
        .note i { color:var(--blue); margin-top:1px; }

        .actions { margin-top:24px; display:flex; gap:10px; align-items:center; }
        .spinner { display:none; width:16px; height:16px; border:2px solid rgba(255,255,255,.4); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; }
        @keyframes spin { to { transform:rotate(360deg); } }
        .uploading .spinner { display:inline-block; }
        .uploading .btn-label-idle { display:none; }

        /* SIDE PANEL */
        .side-card { background:white; border-radius:14px; padding:22px; margin-bottom:20px; }
        .side-card h3 { font-size:13px; font-weight:700; color:#1a1a1a; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
        .side-card h3 i { color:var(--primary); font-size:13px; }

        .step-list { list-style:none; counter-reset:step; }
        .step-list li { display:flex; gap:10px; margin-bottom:14px; }
        .step-list li:last-child { margin-bottom:0; }
        .step-num { counter-increment:step; content:counter(step); width:22px; height:22px; border-radius:50%; background:var(--primary-light); color:var(--primary); font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .step-num::before { content:counter(step); }
        .step-text { font-size:12px; color:#666; line-height:1.5; padding-top:2px; }
        .step-text strong { color:#333; }

        .tip-row { display:flex; gap:10px; margin-bottom:12px; align-items:flex-start; }
        .tip-row:last-child { margin-bottom:0; }
        .tip-row i { width:26px; height:26px; border-radius:7px; background:#f3f4f6; color:#7c7c7c; font-size:11px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .tip-row .tip-text { font-size:12px; color:#666; line-height:1.5; padding-top:3px; }
        .tip-row .tip-text strong { color:#333; }

        /* EXAMPLE PREVIEW (fills the left card's remaining space) */
        .example-block { margin-top:26px; }
        .example-block .field-label-row { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
        .example-block label { font-size:13px; font-weight:600; color:#444; }
        .example-toggle { font-size:11px; color:var(--primary); font-weight:600; cursor:pointer; }
        .example-box { background:#fafafa; border:1px solid #eee; border-radius:10px; padding:16px 18px; font-family:'Courier New',monospace; font-size:12px; line-height:1.9; color:#555; white-space:pre-wrap; }
        .example-box .num { color:var(--primary); font-weight:700; }
        .example-box .choice { color:#777; }
        .example-box .ans { color:var(--green); font-weight:700; }
        .dl-row { margin-top:12px; font-size:11.5px; color:#999; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
        .dl-row a { color:var(--primary); font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:5px; }
        .dl-row a:hover { text-decoration:underline; }

        /* RECENT IMPORTS */
        .recent-row { display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #f2f2f2; }
        .recent-row:last-child { border-bottom:none; }
        .recent-icon { width:34px; height:34px; border-radius:9px; background:#f3f4f6; color:#888; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }
        .recent-info { flex:1; min-width:0; }
        .recent-name { font-size:12px; font-weight:600; color:#333; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .recent-meta { font-size:11px; color:#aaa; margin-top:2px; }
        .recent-status { font-size:10px; font-weight:700; padding:3px 9px; border-radius:20px; text-decoration:none; white-space:nowrap; }
        .rs-ready { background:#fef3c7; color:#b45309; }
        .rs-committed { background:#d1fae5; color:#059669; }
        .rs-failed { background:#fde8e8; color:var(--accent); }
        .rs-parsing { background:#dbeafe; color:#2563eb; }
        .empty-recent { text-align:center; padding:20px 10px; color:#bbb; font-size:12px; }
        .empty-recent i { display:block; font-size:22px; margin-bottom:8px; color:#ddd; }
    </style>
</head>
<body>

@include('partials.faculty-sidebar', ['active' => 'test-bank'])

<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">Import Questions</div>
            <div class="page-sub">Upload a file and we'll turn it into Test Bank questions for you to review.</div>
        </div>
        <a href="{{ route('faculty.test-bank') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back to Test Bank</a>
    </div>

    <div class="layout">
        <div class="card">
            <form id="importForm" action="{{ route('faculty.test-bank.import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="field">
                    <label for="subject_id">Subject</label>
                    <select name="subject_id" id="subject_id" required>
                        <option value="">Choose a subject...</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->code }} — {{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>File</label>
                    <div class="drop-zone" id="dropZone">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <div class="dz-title" id="dzTitle">Drag & drop a file here, or click to browse</div>
                        <div class="dz-sub" id="dzSub">PDF, Word, Excel, CSV, or a photo (JPG/PNG) — up to 20 MB</div>
                        <input type="file" name="file" id="fileInput" accept=".pdf,.docx,.doc,.xlsx,.xls,.csv,.txt,.jpg,.jpeg,.png,.webp" required>
                    </div>

                    <div class="format-grid">
                        <a href="{{ route('faculty.test-bank.import.template', 'pdf') }}" class="format-chip" title="Download a sample PDF"><i class="fas fa-file-pdf"></i>PDF<span class="dl-hint"><i class="fas fa-download"></i> Sample</span></a>
                        <a href="{{ route('faculty.test-bank.import.template', 'docx') }}" class="format-chip" title="Download a sample Word file"><i class="fas fa-file-word"></i>Word<span class="dl-hint"><i class="fas fa-download"></i> Sample</span></a>
                        <a href="{{ route('faculty.test-bank.import.template', 'xlsx') }}" class="format-chip" title="Download a sample Excel file"><i class="fas fa-file-excel"></i>Excel<span class="dl-hint"><i class="fas fa-download"></i> Sample</span></a>
                        <div class="format-chip no-link"><i class="fas fa-image"></i>Photo<span class="dl-hint dl-hint-static">No template — just take a clear photo</span></div>
                        <a href="{{ route('faculty.test-bank.import.template', 'txt') }}" class="format-chip" title="Download a sample text file"><i class="fas fa-file-alt"></i>Plain text<span class="dl-hint"><i class="fas fa-download"></i> Sample</span></a>
                    </div>
                    <div class="csv-hint">Prefer CSV? <a href="{{ route('faculty.test-bank.import.template', 'csv') }}">Download the sample as .csv instead</a>.</div>
                </div>

                <div class="note">
                    <i class="fas fa-circle-info"></i>
                    <div>
                        We try to read the file's own numbering and answers first. For messy files or photos (like a written computation problem), our AI reads it and drafts the question for you. Either way, <strong>nothing is saved to the Test Bank until you review and approve it</strong> on the next screen.
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <span class="spinner"></span>
                        <span class="btn-label-idle"><i class="fas fa-file-import"></i> Upload & Parse</span>
                        <span class="btn-label-busy" style="display:none;">Reading file…</span>
                    </button>
                </div>
            </form>

            <div class="example-block">
                <div class="field-label-row">
                    <label><i class="fas fa-file-lines" style="color:#999; margin-right:6px;"></i>Example of a well-formatted file (PDF / Word / plain text)</label>
                </div>
                <div class="example-box"><span class="num">1.</span> Which financial statement shows a company's financial position at a point in time?
<span class="choice">A. Income Statement
B. Statement of Cash Flows
C. Balance Sheet
D. Statement of Changes in Equity</span>
<span class="ans">Answer: C</span>
Explanation: The Balance Sheet reports assets, liabilities, and equity as of a specific date.

<span class="num">2.</span> Cash is classified as a current asset.
<span class="ans">Answer: True</span></div>
                <div class="dl-row">
                    Download this same example, ready to edit:
                    <a href="{{ route('faculty.test-bank.import.template', 'txt') }}"><i class="fas fa-file-alt"></i> .txt</a>
                    <a href="{{ route('faculty.test-bank.import.template', 'docx') }}"><i class="fas fa-file-word"></i> .docx</a>
                    <a href="{{ route('faculty.test-bank.import.template', 'pdf') }}"><i class="fas fa-file-pdf"></i> .pdf</a>
                    <a href="{{ route('faculty.test-bank.import.template', 'xlsx') }}"><i class="fas fa-file-excel"></i> .xlsx</a>
                    <a href="{{ route('faculty.test-bank.import.template', 'csv') }}"><i class="fas fa-file-csv"></i> .csv</a>
                </div>
            </div>
        </div>

        <div class="side">
            <div class="side-card">
                <h3><i class="fas fa-route"></i> How it works</h3>
                <ul class="step-list">
                    <li><span class="step-num"></span><span class="step-text"><strong>Upload</strong> your file — a reviewer, a past exam, even a photo of a written problem.</span></li>
                    <li><span class="step-num"></span><span class="step-text"><strong>We read it</strong> — numbering and answers first, then AI for anything messy or a photo.</span></li>
                    <li><span class="step-num"></span><span class="step-text"><strong>You review</strong> every question found — edit, fix, or remove any of them.</span></li>
                    <li><span class="step-num"></span><span class="step-text"><strong>You approve</strong> — only then do they get added to the Test Bank.</span></li>
                </ul>
            </div>

            <div class="side-card">
                <h3><i class="fas fa-lightbulb"></i> Tips for a cleaner import</h3>
                <div class="tip-row">
                    <i class="fas fa-list-ol"></i>
                    <div class="tip-text">Number questions clearly (<strong>"1."</strong> or <strong>"1)"</strong>) and label choices <strong>A./B./C./D.</strong></div>
                </div>
                <div class="tip-row">
                    <i class="fas fa-check-double"></i>
                    <div class="tip-text">Include an explicit <strong>"Answer: A"</strong> line where you can — it's more reliable than us guessing.</div>
                </div>
                <div class="tip-row">
                    <i class="fas fa-table"></i>
                    <div class="tip-text">For Excel/CSV, a header row like <strong>Question, Choice A-D, Answer</strong> parses instantly.</div>
                </div>
                <div class="tip-row">
                    <i class="fas fa-camera"></i>
                    <div class="tip-text">For photos, make sure the text is in focus and well-lit — AI reads it, but clarity still helps.</div>
                </div>
            </div>

            <div class="side-card">
                <h3><i class="fas fa-clock-rotate-left"></i> Recent Imports</h3>
                @forelse ($recentBatches as $batch)
                    @php
                        $statusMap = [
                            'ready'     => ['rs-ready', 'Needs review'],
                            'committed' => ['rs-committed', 'Saved'],
                            'failed'    => ['rs-failed', 'Failed'],
                            'parsing'   => ['rs-parsing', 'Parsing'],
                        ];
                        [$statusClass, $statusLabel] = $statusMap[$batch->status] ?? ['rs-parsing', ucfirst($batch->status)];
                        $iconMap = ['pdf' => 'fa-file-pdf', 'docx' => 'fa-file-word', 'doc' => 'fa-file-word', 'xlsx' => 'fa-file-excel', 'xls' => 'fa-file-excel', 'csv' => 'fa-file-excel', 'jpg' => 'fa-image', 'jpeg' => 'fa-image', 'png' => 'fa-image', 'webp' => 'fa-image', 'txt' => 'fa-file-alt'];
                    @endphp
                    <div class="recent-row">
                        <div class="recent-icon"><i class="fas {{ $iconMap[$batch->file_type] ?? 'fa-file' }}"></i></div>
                        <div class="recent-info">
                            <div class="recent-name" title="{{ $batch->original_filename }}">{{ $batch->original_filename }}</div>
                            <div class="recent-meta">{{ $batch->subject->code ?? '—' }} · {{ $batch->created_at->diffForHumans() }}</div>
                        </div>
                        @if ($batch->status === 'ready')
                            <a href="{{ route('faculty.test-bank.import.review', $batch->id) }}" class="recent-status {{ $statusClass }}">{{ $statusLabel }}</a>
                        @else
                            <span class="recent-status {{ $statusClass }}">{{ $statusLabel }}</span>
                        @endif
                    </div>
                @empty
                    <div class="empty-recent">
                        <i class="fas fa-inbox"></i>
                        No imports yet — your uploads will show up here.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @include('partials.alerts')
</main>

<script>
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const dzTitle = document.getElementById('dzTitle');
    const dzSub = document.getElementById('dzSub');
    const submitBtn = document.getElementById('submitBtn');
    const subjectSelect = document.getElementById('subject_id');
    const form = document.getElementById('importForm');

    function refreshSubmitState() {
        submitBtn.disabled = !(fileInput.files.length && subjectSelect.value);
    }

    dropZone.addEventListener('click', () => fileInput.click());
    ['dragenter', 'dragover'].forEach(evt => dropZone.addEventListener(evt, e => {
        e.preventDefault();
        dropZone.classList.add('drag');
    }));
    ['dragleave', 'drop'].forEach(evt => dropZone.addEventListener(evt, e => {
        e.preventDefault();
        dropZone.classList.remove('drag');
    }));
    dropZone.addEventListener('drop', e => {
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            onFileChosen();
        }
    });
    fileInput.addEventListener('change', onFileChosen);
    subjectSelect.addEventListener('change', refreshSubmitState);

    function onFileChosen() {
        if (fileInput.files.length) {
            const file = fileInput.files[0];
            dropZone.classList.add('has-file');
            dzTitle.textContent = file.name;
            dzSub.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB — click to change';
        }
        refreshSubmitState();
    }

    form.addEventListener('submit', () => {
        submitBtn.classList.add('uploading');
        submitBtn.querySelector('.btn-label-idle').style.display = 'none';
        submitBtn.querySelector('.btn-label-busy').style.display = 'inline';
        submitBtn.disabled = true;
    });
</script>

</body>
</html>
