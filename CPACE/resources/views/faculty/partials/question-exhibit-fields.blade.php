{{--
    "Picture or table" card for the question form. Posts:
      image        (file)   remove_image (0/1)   table_json (the table, or empty)
    Expects $editMode and, when editing, $question.
--}}
@php
    $exCurrentImage = $editMode ? $question->exhibitImageUrl() : null;
    $exOldTable = old('table_json');
    $exStartTable = $exOldTable !== null
        ? \App\Support\QuestionExhibit::sanitizeTable($exOldTable)
        : ($editMode ? $question->table_data : null);
@endphp
<div class="ex-embed" id="exhibitCard">
    <div class="ex-attach" id="exDrop">
        <div class="ex-drop-row" @if(! empty($legacy)) hidden @endif>
            <i class="fas fa-cloud-arrow-up ex-drop-ico"></i>
            <div class="ex-drop-text">
                <b>Drag &amp; drop a picture anywhere in this card</b>
                <span>or paste one with Ctrl+V &middot; JPG, PNG or WebP, up to 3 MB. For accounting problems you can also add a table (journal, schedule, statement).</span>
            </div>
            <div class="ex-attach-btns">
                <label class="ex-btn" for="exImageInput"><i class="fas fa-image"></i> <span id="exPicLabel">{{ $exCurrentImage ? 'Replace picture' : 'Add picture' }}</span></label>
                <button type="button" class="ex-btn" id="exAddTable"><i class="fas fa-table"></i> Add table</button>
            </div>
        </div>
        <div class="ex-msg" id="exMsg" hidden></div>

        {{-- Picture --}}
        <div class="ex-pic" id="exPic">
            <div class="ex-pic-preview" id="exPicPreview" @if(! $exCurrentImage) hidden @endif>
                <img src="{{ $exCurrentImage }}" alt="Current picture" id="exPicImg">
                <button type="button" class="ex-pic-x" id="exPicRemove" title="Remove picture" aria-label="Remove picture" @if(! $exCurrentImage) hidden @endif><i class="fas fa-xmark"></i></button>
            </div>
            <input type="file" name="image" id="exImageInput" accept="image/png,image/jpeg,image/webp" style="display:none;">
            <input type="hidden" name="remove_image" id="exRemoveImage" value="0">
        </div>

    {{-- Table --}}
    <div>
        <div id="exTableWrap" hidden>
            <div class="ex-tools">
                <span class="ex-group"><span>Rows</span>
                    <button type="button" class="ex-step" data-act="row-" aria-label="Remove a row">−</button>
                    <b id="exRowCount">0</b>
                    <button type="button" class="ex-step" data-act="row+" aria-label="Add a row">+</button>
                </span>
                <span class="ex-group"><span>Columns</span>
                    <button type="button" class="ex-step" data-act="col-" aria-label="Remove a column">−</button>
                    <b id="exColCount">0</b>
                    <button type="button" class="ex-step" data-act="col+" aria-label="Add a column">+</button>
                </span>
                <label class="ex-check"><input type="checkbox" id="exHeader"> First row is a header</label>
                <label class="ex-check"><input type="checkbox" id="exTotal"> Last row is a total</label>
                <button type="button" class="ex-link danger" id="exRemoveTable"><i class="fas fa-trash"></i> Remove table</button>
            </div>
            <div class="ex-grid-scroll"><table class="ex-grid" id="exGrid"></table></div>
            <p class="ex-hint">Numbers line up on the right for students. Leave a cell empty if it has nothing; empty rows and columns are dropped when you save.</p>
        </div>
        <input type="hidden" name="table_json" id="exTableJson" value="">
    </div>
    </div>
</div>

<style>
    .ex-btn { display:inline-flex; align-items:center; gap:7px; padding:8px 14px; border-radius:8px; border:1.5px solid var(--primary); background:#fff; color:var(--primary); font-size:12.5px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif; }
    .ex-btn:hover { background:var(--primary-light); }
    .ex-link { background:none; border:0; padding:0; color:#888; font-size:12px; font-family:'Poppins',sans-serif; cursor:pointer; }
    .ex-link:hover { color:var(--accent); } .ex-link.danger { margin-left:auto; }
    .ex-btn[hidden], .ex-link[hidden], .ex-pic-preview[hidden], #exTableWrap[hidden] { display:none !important; }
    .ex-embed { margin-bottom:18px; }
    .ex-attach { border:1.5px dashed #d9d9e0; border-radius:12px; padding:14px 16px; background:#fcfcfd; transition:border-color .15s, background .15s; }
    .is-dragging .ex-attach { border-color:var(--primary); background:var(--primary-light); }
    .is-dragging { outline:2px dashed var(--primary); outline-offset:-6px; }
    .ex-drop-row[hidden] { display:none; }
    .ex-drop-row { display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
    .ex-drop-ico { font-size:24px; color:#c4c4cc; }
    .ex-drop-text { flex:1; min-width:200px; display:flex; flex-direction:column; gap:2px; }
    .ex-drop-text b { font-size:12.5px; color:#444; }
    .ex-drop-text span { font-size:11.5px; color:#9a9aa2; line-height:1.5; }
    .ex-attach-btns { display:flex; gap:8px; flex-wrap:wrap; }
    .ex-msg { margin-top:10px; font-size:12px; color:var(--accent); }
    .ex-msg[hidden] { display:none; }
    .ex-pic { margin-top:12px; }
    .ex-pic-preview { position:relative; display:inline-block; max-width:100%; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; background:#fff; }
    .ex-pic-preview img { max-width:100%; max-height:240px; display:block; }
    .ex-pic-x { position:absolute; top:6px; right:6px; width:26px; height:26px; border:0; border-radius:50%; background:rgba(0,0,0,.6); color:#fff; cursor:pointer; font-size:12px; }
    .ex-pic-x:hover { background:var(--accent); }
    .ex-pic-x[hidden] { display:none; }
    #exTableWrap { margin-top:12px; }
    .ex-tools { display:flex; align-items:center; gap:14px; flex-wrap:wrap; margin-bottom:10px; font-size:12.5px; color:#555; }
    .ex-group { display:inline-flex; align-items:center; gap:7px; } .ex-group > span { color:#888; font-size:12px; }
    .ex-group b { min-width:16px; text-align:center; color:#1a1a1a; }
    .ex-step { width:26px; height:26px; border:1px solid #e0e0e0; border-radius:7px; background:#fff; color:#444; font-size:15px; line-height:1; cursor:pointer; }
    .ex-step:hover { background:#f3f4f6; }
    .ex-check { display:inline-flex; align-items:center; gap:6px; cursor:pointer; font-size:12.5px; }
    .ex-check input { accent-color:var(--primary); }
    .ex-grid-scroll { overflow-x:auto; border:1px solid #e5e7eb; border-radius:10px; }
    table.ex-grid { border-collapse:collapse; width:100%; }
    .ex-grid td { padding:0; border-bottom:1px solid #eef0f3; border-right:1px solid #eef0f3; min-width:110px; }
    .ex-grid tr:last-child td { border-bottom:0; } .ex-grid td:last-child { border-right:0; }
    .ex-grid input[type=text] { width:100%; border:0; border-radius:0; padding:9px 10px; font-size:13px; background:transparent; }
    .ex-grid input[type=text]:focus { background:#fdf6f6; outline:none; }
    .ex-grid tr.is-head input { font-weight:700; background:#f3f4f6; }
    .ex-grid tr.is-total input { font-weight:700; background:#fafafa; }
    .ex-hint { font-size:11.5px; color:#aaa; margin-top:8px; }
</style>

<script>
(function () {
    const MAX_ROWS = {{ \App\Support\QuestionExhibit::MAX_ROWS }}, MAX_COLS = {{ \App\Support\QuestionExhibit::MAX_COLUMNS }};
    const $ = (id) => document.getElementById(id);
    const form = $('exhibitCard').closest('form');
    let data = @json($exStartTable) || null;   // {header,total,rows}
    let cells = data ? data.rows.map((r) => r.slice()) : [];

    // ── Picture ──
    const input = $('exImageInput'), preview = $('exPicPreview'), img = $('exPicImg'), msg = $('exMsg');
    const card = $('exhibitCard').closest('.card') || $('exhibitCard');
    const MAX_BYTES = {{ \App\Support\QuestionExhibit::MAX_IMAGE_KILOBYTES }} * 1024;
    const OK_TYPES = ['image/png', 'image/jpeg', 'image/webp'];
    const say = (t) => { msg.textContent = t || ''; msg.hidden = !t; };

    function showPicture() {
        const file = input.files[0];
        if (!file) return;
        img.src = URL.createObjectURL(file);
        preview.hidden = false;
        $('exRemoveImage').value = '0';
        $('exPicRemove').hidden = false;
        $('exPicLabel').textContent = 'Replace picture';
    }
    function takeFile(file) {
        if (!file) return false;
        if (!OK_TYPES.includes(file.type)) { say('Only JPG, PNG or WebP pictures can be attached.'); return false; }
        if (file.size > MAX_BYTES) { say('That picture is larger than ' + (MAX_BYTES / 1048576) + ' MB.'); return false; }
        say('');
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        showPicture();
        return true;
    }
    input.addEventListener('change', () => {
        const f = input.files[0];
        if (f && !takeFile(f)) input.value = '';
    });
    $('exPicRemove').addEventListener('click', () => {
        input.value = '';
        say('');
        $('exRemoveImage').value = '1';
        preview.hidden = true;
        $('exPicRemove').hidden = true;
        $('exPicLabel').textContent = 'Add picture';
    });

    // Drag & drop a picture anywhere on the question card; paste works too.
    const hasFiles = (e) => e.dataTransfer && [...e.dataTransfer.types].includes('Files');
    let depth = 0;
    card.addEventListener('dragenter', (e) => { if (hasFiles(e)) { e.preventDefault(); depth++; card.classList.add('is-dragging'); } });
    card.addEventListener('dragover', (e) => { if (hasFiles(e)) e.preventDefault(); });
    card.addEventListener('dragleave', () => { depth = Math.max(0, depth - 1); if (!depth) card.classList.remove('is-dragging'); });
    card.addEventListener('drop', (e) => {
        if (!hasFiles(e)) return;
        e.preventDefault(); depth = 0; card.classList.remove('is-dragging');
        const file = [...e.dataTransfer.files].find((f) => f.type.startsWith('image/')) || e.dataTransfer.files[0];
        takeFile(file);
    });
    card.addEventListener('paste', (e) => {
        const item = [...(e.clipboardData ? e.clipboardData.items : [])].find((i) => i.kind === 'file' && i.type.startsWith('image/'));
        if (!item) return;
        e.preventDefault(); e.stopPropagation();
        takeFile(item.getAsFile());
    }, true);

    // ── Table ──
    const grid = $('exGrid');
    const colCount = () => (cells[0] ? cells[0].length : 0);

    function read() {
        grid.querySelectorAll('tr').forEach((tr, r) => tr.querySelectorAll('input').forEach((inp, c) => { cells[r][c] = inp.value; }));
    }
    function draw() {
        $('exTableWrap').hidden = cells.length === 0;
        $('exAddTable').hidden = cells.length !== 0;
        $('exRowCount').textContent = cells.length;
        $('exColCount').textContent = colCount();
        grid.innerHTML = '';
        cells.forEach((row, r) => {
            const tr = document.createElement('tr');
            row.forEach((val, c) => {
                const td = document.createElement('td');
                const inp = document.createElement('input');
                inp.type = 'text'; inp.value = val; inp.maxLength = {{ \App\Support\QuestionExhibit::MAX_CELL_LENGTH }};
                inp.setAttribute('aria-label', 'Row ' + (r + 1) + ', column ' + (c + 1));
                td.appendChild(inp); tr.appendChild(td);
            });
            grid.appendChild(tr);
        });
        mark();
    }
    function mark() {
        const rows = [...grid.querySelectorAll('tr')];
        rows.forEach((tr, i) => {
            tr.classList.toggle('is-head', $('exHeader').checked && i === 0);
            tr.classList.toggle('is-total', $('exTotal').checked && i === rows.length - 1 && rows.length > 1);
        });
    }
    function start() { cells = [['', '', ''], ['', '', ''], ['', '', '']]; draw(); }

    $('exAddTable').addEventListener('click', start);
    $('exRemoveTable').addEventListener('click', () => { cells = []; $('exHeader').checked = false; $('exTotal').checked = false; draw(); });
    document.querySelectorAll('.ex-step').forEach((btn) => btn.addEventListener('click', () => {
        read();
        const act = btn.dataset.act, cols = colCount();
        if (act === 'row+' && cells.length < MAX_ROWS) cells.push(Array(cols).fill(''));
        if (act === 'row-' && cells.length > 1) cells.pop();
        if (act === 'col+' && cols < MAX_COLS) cells.forEach((r) => r.push(''));
        if (act === 'col-' && cols > 1) cells.forEach((r) => r.pop());
        draw();
    }));
    ['exHeader', 'exTotal'].forEach((id) => $(id).addEventListener('change', mark));

    if (data) { $('exHeader').checked = !!data.header; $('exTotal').checked = !!data.total; }
    draw();

    // Keep the hidden field current as the grid changes, so the value is right
    // however the form ends up being submitted (e.g. after the confirm popup).
    function sync() {
        read();
        const filled = cells.some((r) => r.some((v) => v.trim() !== ''));
        $('exTableJson').value = filled ? JSON.stringify({ header: $('exHeader').checked, total: $('exTotal').checked, rows: cells }) : '';
    }
    grid.addEventListener('input', sync);
    ['exHeader', 'exTotal'].forEach((id) => $(id).addEventListener('change', sync));
    $('exAddTable').addEventListener('click', sync);
    $('exRemoveTable').addEventListener('click', sync);
    document.querySelectorAll('.ex-step').forEach((btn) => btn.addEventListener('click', sync));
    form.addEventListener('submit', sync);
    sync();
})();
</script>
