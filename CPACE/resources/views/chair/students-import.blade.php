<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Enroll Students - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .import-shell { max-width: 1000px; margin: 0 auto; }

        /* Flow hero — maroon gradient like the achievement banner */
        .flow-hero {
            background: linear-gradient(135deg, #1a0a0a 0%, #3d0c0c 30%, #7B1D1D 62%, #a12626 100%);
            border-radius: 20px;
            padding: 26px 30px;
            color: #fff;
            position: relative;
            overflow: hidden;
            margin-bottom: 22px;
            box-shadow: 0 8px 22px rgba(0,0,0,.28);
        }
        .flow-hero::after {
            content: '';
            position: absolute; right: -40px; bottom: -60px;
            width: 220px; height: 220px; border-radius: 50%;
            background: radial-gradient(circle, rgba(255,215,106,.14) 0%, transparent 65%);
            pointer-events: none;
        }
        .flow-hero h2 { font-size: 20px; font-weight: 700; margin-bottom: 4px; display:flex; align-items:center; gap:10px; }
        .flow-hero p { font-size: 12.5px; color: rgba(255,255,255,.8); max-width: 560px; }
        .flow-steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-top: 20px; position: relative; z-index: 1; }
        .flow-step {
            background: rgba(0,0,0,.22); border: 1px solid rgba(255,255,255,.12);
            border-radius: 14px; padding: 15px 16px; backdrop-filter: blur(6px);
        }
        .flow-step .fs-ic {
            width: 34px; height: 34px; border-radius: 10px; display:flex; align-items:center; justify-content:center;
            font-size: 15px; margin-bottom: 9px;
            background: rgba(255,215,106,.16); color: #ffd76a;
        }
        .flow-step .fs-n { font-size: 10px; font-weight: 700; letter-spacing: 1px; color: rgba(255,255,255,.55); text-transform: uppercase; }
        .flow-step .fs-t { font-size: 13px; font-weight: 600; margin: 2px 0 4px; }
        .flow-step .fs-d { font-size: 11px; color: rgba(255,255,255,.72); line-height: 1.5; }

        /* Upload */
        .upload-grid { display: grid; grid-template-columns: 1.35fr 1fr; gap: 18px; align-items: start; }
        .dropzone {
            border: 2px dashed #d9c2c2; border-radius: 16px;
            background: #fff9f9; padding: 40px 24px; text-align: center;
            cursor: pointer; transition: all .2s; position: relative;
        }
        .dropzone:hover, .dropzone.drag { border-color: var(--primary); background: #fdeff0; }
        .dropzone .dz-ic {
            width: 66px; height: 66px; margin: 0 auto 14px; border-radius: 50%;
            display:flex; align-items:center; justify-content:center;
            background: var(--primary-light); color: var(--primary); font-size: 26px;
        }
        .dropzone h4 { font-size: 15px; color: #1a1a1a; margin-bottom: 5px; }
        .dropzone p { font-size: 12px; color: #999; }
        .dropzone .dz-formats { margin-top: 12px; display: flex; gap: 6px; justify-content: center; flex-wrap: wrap; }
        .dz-formats span { font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 20px; background: #f1e3e3; color: var(--primary); }
        .dz-file {
            display: none; align-items: center; gap: 12px; text-align: left;
            background: #fff; border: 1.5px solid #eadada; border-radius: 12px; padding: 12px 14px; margin-top: 14px;
        }
        .dz-file.show { display: flex; }
        .dz-file .df-ic { width: 40px; height: 40px; border-radius: 9px; background: #d1fae5; color: #059669; display:flex; align-items:center; justify-content:center; font-size: 17px; flex-shrink:0; }
        .dz-file .df-name { font-size: 13px; font-weight: 600; color: #1a1a1a; }
        .dz-file .df-meta { font-size: 11px; color: #aaa; }
        .dz-file .df-x { margin-left: auto; background: none; border: none; color: #bbb; cursor: pointer; font-size: 15px; }
        .dz-file .df-x:hover { color: var(--accent); }

        .side-card .req-list { list-style: none; margin: 10px 0 0; }
        .side-card .req-list li { display: flex; align-items: flex-start; gap: 9px; font-size: 12px; color: #555; padding: 7px 0; border-bottom: 1px dashed #f0f0f0; }
        .side-card .req-list li:last-child { border-bottom: none; }
        .side-card .req-list i { color: var(--primary); font-size: 11px; margin-top: 3px; }
        .side-card .req-list b { color: #1a1a1a; }
        .gsuite-note {
            display:flex; gap:10px; align-items:flex-start;
            background:#eef5ff; border:1px solid #d6e6ff; color:#1e40af;
            border-radius:10px; padding:11px 13px; font-size:11.5px; line-height:1.5; margin-top:14px;
        }
        .gsuite-note i { color:#2563eb; margin-top:2px; }

        .import-actions { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-top:20px; flex-wrap: wrap; }
        .btn-lg { padding: 12px 24px; font-size: 14px; border-radius: 10px; }
        .btn-primary:disabled { opacity: .5; cursor: not-allowed; }

        /* Preview table */
        .preview-wrap { margin-top: 20px; display: none; }
        .preview-wrap.show { display: block; }
        .preview-head { display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px; }
        .preview-head .ph-count { font-size: 12px; color: #999; }
        .preview-head .ph-count b { color: var(--primary); }
        .tbl-scroll { overflow-x: auto; border-radius: 10px; border: 1px solid #f0eaea; }
        .preview-wrap table td, .preview-wrap table th { white-space: nowrap; }
        .row-tag { font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; }
        .rt-ok { background:#d1fae5; color:#059669; }
        .rt-dup { background:#fef3c7; color:#d97706; }
        .rt-exists { background:#fee2e2; color:#b91c1c; }
        .rt-checking { background:#f1f1f1; color:#999; }
        .cell-input {
            width: 100%; min-width: 110px; border: 1px solid transparent; background: transparent;
            font: inherit; color: inherit; padding: 5px 7px; border-radius: 7px; transition: all .15s;
        }
        .cell-input:hover { border-color: #eadada; background: #fff; }
        .cell-input:focus { outline: none; border-color: var(--primary); background: #fff; box-shadow: 0 0 0 2px var(--primary-light); }
        .cell-input.bad { border-color: #f3a1a1; background: #fff5f5; }
        .row-remove { background: none; border: none; color: #ccc; cursor: pointer; font-size: 12.5px; padding: 6px 8px; }
        .row-remove:hover { color: var(--accent); }
        .preview-add-row { font-size: 12px; }

        /* Results panel */
        .results-panel { display:none; margin-top: 22px; }
        .results-panel.show { display:block; }
        .result-banner {
            background: linear-gradient(135deg, #065f46, #10b981);
            border-radius: 16px; padding: 22px 26px; color:#fff;
            display:flex; align-items:center; gap:18px; position:relative; overflow:hidden;
        }
        .result-banner .rb-ic { font-size: 40px; }
        .result-banner h3 { font-size: 19px; font-weight:700; }
        .result-banner p { font-size: 12.5px; color: rgba(255,255,255,.85); margin-top:2px; }
        .result-banner .rb-actions { margin-left:auto; display:flex; gap:9px; }
        .btn-onwhite { background:rgba(255,255,255,.16); color:#fff; border:1px solid rgba(255,255,255,.3); }
        .btn-onwhite:hover { background:rgba(255,255,255,.26); }
        .cred-note {
            display:flex; gap:10px; align-items:flex-start;
            background:#fffbeb; border:1px solid #fde68a; color:#92400e;
            border-radius:10px; padding:11px 14px; font-size:12px; line-height:1.5; margin:14px 0;
        }
        .cred-note i { color:#d97706; margin-top:2px; }
        .temp-pass { font-family: 'Courier New', monospace; font-weight:700; color:#7B1D1D; background:#f8eaea; padding:3px 9px; border-radius:6px; font-size:12px; letter-spacing:.5px; }
        .copy-mini { background:none; border:none; color:#bbb; cursor:pointer; font-size:12px; margin-left:6px; }
        .copy-mini:hover { color: var(--primary); }

        @media (max-width: 820px) {
            .flow-steps, .upload-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'students'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Bulk Enroll Students</div>
                <div class="page-sub">Upload a class list and let the system create student accounts automatically.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a class="btn btn-outline" href="{{ route('chair.students') }}"><i class="fas fa-arrow-left"></i> Back</a>
            @includeIf('partials.topbar-actions')
        </div>
    </div>

    <div class="import-shell">
        {{-- Errors and skipped import rows surface as SweetAlert popups via partials.alerts --}}

        <!-- How the new enrollment flow works -->
        <div class="flow-hero">
            <h2><i class="fas fa-wand-magic-sparkles" style="color:#ffd76a;"></i> Automated Enrollment</h2>
            <p>No more one-by-one sign-ups. Drop in your class list and CPACE provisions every student a GSuite login with a one-time password — they finish their own setup on first login.</p>
            <div class="flow-steps">
                <div class="flow-step">
                    <div class="fs-ic"><i class="fas fa-file-arrow-up"></i></div>
                    <div class="fs-n">Step 1</div>
                    <div class="fs-t">You upload the list</div>
                    <div class="fs-d">Import a CSV, Excel, or Word class list of your students.</div>
                </div>
                <div class="flow-step">
                    <div class="fs-ic"><i class="fas fa-robot"></i></div>
                    <div class="fs-n">Step 2</div>
                    <div class="fs-t">System creates accounts</div>
                    <div class="fs-d">GSuite emails are provisioned and a one-time password is sent straight to each student's inbox.</div>
                </div>
                <div class="flow-step">
                    <div class="fs-ic"><i class="fas fa-user-gear"></i></div>
                    <div class="fs-n">Step 3</div>
                    <div class="fs-t">Students set themselves up</div>
                    <div class="fs-d">On first login they change the password and personalize their review plan.</div>
                </div>
            </div>
        </div>

        <div class="upload-grid">
            <!-- Dropzone -->
            <div class="card">
                <div class="card-head" style="margin-bottom:12px;">
                    <div class="card-title"><i class="fas fa-cloud-arrow-up" style="color:var(--primary); margin-right:6px;"></i> Upload class list</div>
                    <a href="{{ route('chair.students.template') }}" class="card-link"><i class="fas fa-download"></i> Download template</a>
                </div>

                <form id="importForm" method="POST" action="{{ route('chair.students.import') }}" enctype="multipart/form-data">
                    @csrf
                    <label class="dropzone" id="dropzone" for="fileInput">
                        <div class="dz-ic"><i class="fas fa-file-arrow-up"></i></div>
                        <h4>Drag &amp; drop your file here</h4>
                        <p>or click to browse from your computer</p>
                        <div class="dz-formats"><span>.CSV</span><span>.XLSX</span><span>.DOCX</span></div>
                        <input type="file" id="fileInput" name="file" accept=".csv,.xlsx,.xls,.docx" hidden>
                    </label>
                    <input type="hidden" name="rows_json" id="rowsJson">

                    <div class="dz-file" id="dzFile">
                        <div class="df-ic"><i class="fas fa-file-csv"></i></div>
                        <div>
                            <div class="df-name" id="dfName">students.csv</div>
                            <div class="df-meta" id="dfMeta">—</div>
                        </div>
                        <button type="button" class="df-x" id="dfRemove" title="Remove"><i class="fas fa-xmark"></i></button>
                    </div>
                </form>

                <div class="import-actions">
                    <span class="hint" id="parseHint">Supported: comma-separated CSV up to 500 students.</span>
                    <button class="btn btn-primary btn-lg" id="createBtn" disabled>
                        <i class="fas fa-bolt"></i> Create Accounts
                    </button>
                </div>
            </div>

            <!-- Requirements side card -->
            <div class="card side-card">
                <div class="card-title"><i class="fas fa-list-check" style="color:var(--primary); margin-right:6px;"></i> Required columns</div>
                <ul class="req-list">
                    <li><i class="fas fa-circle-check"></i><span><b>first_name</b> — student given name</span></li>
                    <li><i class="fas fa-circle-check"></i><span><b>last_name</b> — student surname</span></li>
                    <li><i class="fas fa-circle-check"></i><span><b>email</b> — GSuite address (or leave blank to auto-generate)</span></li>
                    <li><i class="fas fa-circle-check"></i><span><b>student_number</b> <span style="color:#aaa;">(optional) — SR-Code, e.g. 23-00001</span></span></li>
                    <li><i class="fas fa-circle-check"></i><span><b>section</b> <span style="color:#aaa;">(optional)</span></span></li>
                </ul>
                <div class="gsuite-note">
                    <i class="fab fa-google"></i>
                    <span>Accounts are tied to your institution's <b>Google Workspace (GSuite)</b>. A one-time password is generated per student and must be changed on first login.</span>
                </div>
            </div>
        </div>

        <!-- Parsed preview -->
        <div class="card preview-wrap" id="previewWrap">
            <div class="preview-head">
                <div class="card-title"><i class="fas fa-table-list" style="color:var(--primary); margin-right:6px;"></i> Preview</div>
                <div class="ph-count">Detected <b id="rowCount">0</b> students — click any cell to fix it before creating accounts</div>
            </div>
            <div class="tbl-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>#</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Student No.</th><th>Section</th><th>Status</th><th></th>
                        </tr>
                    </thead>
                    <tbody id="previewBody"></tbody>
                </table>
            </div>
            <button type="button" class="btn btn-outline preview-add-row" id="addRowBtn" style="margin-top:12px;">
                <i class="fas fa-plus"></i> Add row
            </button>
        </div>

        <!-- Results (rendered after the server creates the accounts) -->
        @if (session('created_credentials') && count(session('created_credentials')))
            @php
                $creds = session('created_credentials');
                $failedCreds = collect($creds)->reject(fn ($r) => $r['mailed']);
            @endphp
            <div class="results-panel show" id="resultsPanel">
                <div class="result-banner">
                    <div class="rb-ic"><i class="fas fa-circle-check"></i></div>
                    <div>
                        <h3>{{ count($creds) }} account{{ count($creds) === 1 ? '' : 's' }} created</h3>
                        <p>GSuite logins are ready — each student's one-time password was emailed straight to their own inbox.</p>
                    </div>
                </div>

                <div class="cred-note">
                    <i class="fas fa-shield-halved"></i>
                    <span>One-time passwords are never shown here — they go directly to each student's GSuite inbox. Students will be asked to set a new password when they first open CPACE.</span>
                </div>

                @if ($failedCreds->isNotEmpty())
                    <div class="cred-note" style="background:#fef2f2; border-color:#fecaca; color:#b91c1c;">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span>{{ $failedCreds->count() }} credential email{{ $failedCreds->count() === 1 ? '' : 's' }} couldn't be sent. These one-time passwords are shown below as a fallback — deliver them to the student securely, then they won't be shown again.</span>
                    </div>
                @endif

                <div class="card" style="padding:0; overflow:hidden;">
                    <div class="tbl-scroll">
                        <table>
                            <thead>
                                <tr><th>Student</th><th>GSuite Email</th><th>Credential Status</th><th>Setup Status</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($creds as $r)
                                    <tr>
                                        <td>
                                            <div style="display:flex; align-items:center; gap:10px;">
                                                <div class="user-av">{{ strtoupper(substr($r['first_name'],0,1).substr($r['last_name'],0,1)) }}</div>
                                                <div>
                                                    <div style="font-weight:600;">{{ $r['first_name'] }} {{ $r['last_name'] }}</div>
                                                    <div style="font-size:11px; color:#aaa;">{{ $r['student_number'] ?: '—' }} · {{ $r['section'] ?: 'No section' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $r['email'] }}</td>
                                        <td>
                                            @if ($r['mailed'])
                                                <span class="pill pill-on"><i class="fas fa-paper-plane"></i> Emailed</span>
                                            @else
                                                <span class="temp-pass">{{ $r['temp_password'] }}</span>
                                                <button type="button" class="copy-mini" title="Copy" onclick="navigator.clipboard.writeText('{{ $r['temp_password'] }}')"><i class="fas fa-copy"></i></button>
                                            @endif
                                        </td>
                                        <td><span class="pill pill-off"><i class="fas fa-hourglass-half"></i> Awaiting setup</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div style="text-align:center; margin-top:20px;">
                    <a href="{{ route('chair.students') }}" class="btn btn-primary btn-lg"><i class="fas fa-users"></i> Go to Student List</a>
                </div>
            </div>
        @endif
    </div>
</main>

<script>
(function () {
    const dz = document.getElementById('dropzone');
    const input = document.getElementById('fileInput');
    const dzFile = document.getElementById('dzFile');
    const dfName = document.getElementById('dfName');
    const dfMeta = document.getElementById('dfMeta');
    const dfRemove = document.getElementById('dfRemove');
    const createBtn = document.getElementById('createBtn');
    const previewWrap = document.getElementById('previewWrap');
    const previewBody = document.getElementById('previewBody');
    const rowCount = document.getElementById('rowCount');
    const rowsJsonInput = document.getElementById('rowsJson');
    const addRowBtn = document.getElementById('addRowBtn');

    let rows = [];
    let recheckTimer = null;

    ['dragenter','dragover'].forEach(e => dz.addEventListener(e, ev => { ev.preventDefault(); dz.classList.add('drag'); }));
    ['dragleave','drop'].forEach(e => dz.addEventListener(e, ev => { ev.preventDefault(); dz.classList.remove('drag'); }));
    dz.addEventListener('drop', ev => { if (ev.dataTransfer.files.length) { input.files = ev.dataTransfer.files; handleFile(input.files[0]); } });
    input.addEventListener('change', () => { if (input.files.length) handleFile(input.files[0]); });
    dfRemove.addEventListener('click', reset);

    function reset() {
        input.value = '';
        rows = [];
        rowsJsonInput.value = '';
        dzFile.classList.remove('show');
        previewWrap.classList.remove('show');
        createBtn.disabled = true;
    }

    function handleFile(file) {
        dfName.textContent = file.name;
        dfMeta.textContent = (file.size/1024).toFixed(1) + ' KB · ' + (file.type || 'file');
        dzFile.classList.add('show');

        if (file.name.toLowerCase().endsWith('.csv')) {
            const reader = new FileReader();
            reader.onload = e => { parseCsv(e.target.result); };
            reader.readAsText(file);
        } else {
            // Non-CSV: UI-only preview can't parse binary here — enable action, real parsing happens server-side.
            rows = [];
            previewWrap.classList.remove('show');
            createBtn.disabled = false;
        }
    }

    function parseCsv(text) {
        const lines = text.split(/\r?\n/).filter(l => l.trim().length);
        if (!lines.length) return;
        const headers = lines[0].split(',').map(h => h.trim().toLowerCase());
        const idx = k => headers.indexOf(k);
        const seen = new Set();
        rows = lines.slice(1).map(line => {
            const cells = line.split(',');
            const g = k => (idx(k) > -1 ? (cells[idx(k)] || '').trim() : '');
            let email = g('email');
            const fn = g('first_name'), ln = g('last_name'), sn = g('student_number');
            if (!email && sn) email = sn.toLowerCase().replace(/[^a-z0-9\-]/g, '') + '@g.batstate-u.edu.ph';
            if (!email && fn && ln) email = (fn + '.' + ln).toLowerCase().replace(/\s+/g,'') + '@cpace.edu';
            const dup = seen.has(email); seen.add(email);
            return { first_name: fn, last_name: ln, email, student_number: g('student_number'), section: g('section'), dup, exists: null };
        }).filter(r => r.first_name || r.last_name);
        renderPreview();
        checkExistingEmails();
    }

    function statusTag(r) {
        if (r.dup) return '<span class="row-tag rt-dup">Duplicate in file</span>';
        if (r.exists === true) return '<span class="row-tag rt-exists">Already registered</span>';
        if (r.exists === null) return '<span class="row-tag rt-checking">Checking…</span>';
        return '<span class="row-tag rt-ok">Ready</span>';
    }

    // Field inputs are built once per full render; typing in one updates
    // `rows` directly (see the delegated 'input' listener below) instead of
    // re-rendering the whole table, so the chair's cursor/focus isn't lost
    // mid-edit. Only structural changes (add/remove row, initial parse,
    // existence-check results) trigger a full re-render.
    function renderPreview() {
        previewBody.innerHTML = rows.map((r, i) => `
            <tr data-i="${i}">
                <td>${i+1}</td>
                <td><input class="cell-input" data-field="first_name" value="${escAttr(r.first_name)}" placeholder="First name"></td>
                <td><input class="cell-input" data-field="last_name" value="${escAttr(r.last_name)}" placeholder="Last name"></td>
                <td><input class="cell-input" data-field="email" value="${escAttr(r.email)}" placeholder="Auto-generated if blank"></td>
                <td><input class="cell-input" data-field="student_number" value="${escAttr(r.student_number)}" placeholder="Optional"></td>
                <td><input class="cell-input" data-field="section" value="${escAttr(r.section)}" placeholder="Optional"></td>
                <td class="status-cell">${statusTag(r)}</td>
                <td><button type="button" class="row-remove" title="Remove row"><i class="fas fa-trash"></i></button></td>
            </tr>`).join('');
        rowCount.textContent = rows.length;
        previewWrap.classList.add('show');
        createBtn.disabled = rows.length === 0;
    }

    // Refreshes only the status pill in each row — used after an in-place
    // edit, so the input the chair is typing into is never replaced.
    function updateStatusCells() {
        previewBody.querySelectorAll('tr').forEach(tr => {
            const i = parseInt(tr.dataset.i, 10);
            const cell = tr.querySelector('.status-cell');
            if (cell && rows[i]) cell.innerHTML = statusTag(rows[i]);
        });
        rowCount.textContent = rows.length;
        createBtn.disabled = rows.length === 0;
    }

    function recomputeDupFlags() {
        const seen = new Set();
        rows.forEach(r => {
            const email = (r.email || '').toLowerCase();
            r.dup = email !== '' && seen.has(email);
            seen.add(email);
        });
    }

    // Cross-checks each (non in-file-duplicate) email against accounts already
    // in the system, so the chair sees "Already registered" before submitting
    // instead of only finding out from a skipped-row error afterwards.
    // `afterUpdate` lets edit-triggered rechecks refresh just the status
    // pills instead of rebuilding the whole (currently-focused) table.
    let checkingEmails = false;

    async function checkExistingEmails(afterUpdate) {
        checkingEmails = true;
        createBtn.disabled = true;
        const uniqueEmails = [...new Set(rows.filter(r => !r.dup && r.email).map(r => r.email))];
        const cache = {};

        await Promise.all(uniqueEmails.map(async email => {
            try {
                const res = await fetch('{{ route('chair.check-email') }}?email=' + encodeURIComponent(email), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json();
                cache[email] = !!data.taken;
            } catch (e) {
                cache[email] = false;
            }
        }));

        rows.forEach(r => { r.exists = r.dup || !r.email ? false : (cache[r.email] || false); });
        checkingEmails = false;
        (afterUpdate || renderPreview)();
    }

    // Editing a cell updates `rows` in place. Email edits also refresh the
    // duplicate flag immediately and re-check "already registered" (debounced
    // so it doesn't fire a request per keystroke).
    previewBody.addEventListener('input', ev => {
        const cell = ev.target.closest('.cell-input');
        if (!cell) return;
        const tr = cell.closest('tr');
        const i = parseInt(tr.dataset.i, 10);
        if (!rows[i]) return;
        const field = cell.dataset.field;
        const value = field === 'email' ? cell.value.trim().toLowerCase() : cell.value.trim();
        rows[i][field] = value || null;
        if (field === 'first_name' || field === 'last_name') rows[i][field] = value;

        if (field === 'email') {
            rows[i].exists = null;
            recomputeDupFlags();
            updateStatusCells();
            clearTimeout(recheckTimer);
            recheckTimer = setTimeout(() => checkExistingEmails(updateStatusCells), 400);
        }
    });

    // Row removal — a full re-render is fine here since it's a discrete
    // click, not an in-progress keystroke.
    previewBody.addEventListener('click', ev => {
        const btn = ev.target.closest('.row-remove');
        if (!btn) return;
        const tr = btn.closest('tr');
        const i = parseInt(tr.dataset.i, 10);
        rows.splice(i, 1);
        renderPreview();
        checkExistingEmails();
    });

    // Lets the chair add a student the file missed, straight in the preview.
    addRowBtn.addEventListener('click', () => {
        rows.push({ first_name: '', last_name: '', email: '', student_number: null, section: null, dup: false, exists: false });
        renderPreview();
        previewBody.querySelector('tr:last-child .cell-input')?.focus();
    });

    // Real submit — the server creates the accounts and returns credentials.
    createBtn.addEventListener('click', () => {
        if (!input.files.length) return;

        if (checkingEmails) {
            CPACE.warning('Still checking emails', 'Hang on a moment while we finish checking these addresses against existing accounts.');
            return;
        }

        const dupCount = rows.filter(r => r.dup).length;
        const existsCount = rows.filter(r => r.exists === true).length;
        if (dupCount > 0 || existsCount > 0) {
            const parts = [];
            if (dupCount > 0) parts.push(dupCount + ' row' + (dupCount === 1 ? '' : 's') + ' repeat the same email within the file');
            if (existsCount > 0) parts.push(existsCount + ' row' + (existsCount === 1 ? '' : 's') + ' use an email already registered to another account');
            CPACE.error(
                'Fix the flagged rows before continuing',
                parts.join(' and ') + '. Correct or remove those rows from your file and re-upload before creating accounts.'
            );
            return;
        }

        const n = rows.length;
        CPACE.confirm({
            title: 'Create ' + n + ' student account' + (n === 1 ? '' : 's') + '?',
            html: 'Each student\'s one-time password will be emailed straight to their own GSuite inbox — it is never shown here.',
            icon: 'question',
            confirmText: 'Yes, create accounts',
            cancelText: 'Review the list again',
        }).then(ok => {
            if (!ok) return;
            createBtn.disabled = true;
            createBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating accounts…';
            CPACE.loading('Creating accounts...', 'Generating one-time passwords for ' + n + ' student' + (n === 1 ? '' : 's') + '.');

            // If we parsed the file ourselves (CSV), submit the edited rows
            // as-is instead of making the server re-parse the original file —
            // so any fixes made in the preview are what actually get created.
            if (rows.length > 0) {
                rowsJsonInput.value = JSON.stringify(rows.map(r => ({
                    first_name: r.first_name, last_name: r.last_name, email: r.email,
                    student_number: r.student_number, section: r.section,
                })));
            }
            document.getElementById('importForm').submit();
        });
    });

    document.getElementById('resultsPanel')?.scrollIntoView({ behavior: 'smooth' });

    function esc(s) { return (s||'').replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
    function escAttr(s) { return esc(s).replace(/'/g, '&#39;'); }
})();
</script>

    @include('partials.alerts')
</body>
</html>
