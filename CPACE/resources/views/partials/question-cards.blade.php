{{-- Reusable list of question editors (multiple choice / true-false), each with the Word-style editor.
     Used for the questions under a scenario in the Test Bank and for hand-written questions in a mock exam.
       const qc = QCards.mount(el, { uploadUrl, points: false, difficulty: true, items: [...], onChange(items) {} });
       qc.get()     -> [{id, question_text, question_type, difficulty, explanation, points, choices:[{text,is_correct}]}]
       qc.add(type) -> append a blank card (type: 'mcq' | 'true_false')
     Needs partials.rich-editor on the page. --}}
<style>
    .qc-list { display:flex; flex-direction:column; gap:14px; }
    .qc { background:#fff; border:1.5px solid #e8e9ee; border-radius:16px; padding:16px 18px; transition:border-color .15s, box-shadow .15s; }
    .qc:focus-within { border-color:#d9b8b8; box-shadow:0 0 0 4px rgba(123,29,29,.05); }
    .qc-head { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:12px; }
    .qc-num { width:28px; height:28px; border-radius:50%; background:var(--primary-light, #f5e8e8); color:var(--primary, #7B1D1D); font-size:12.5px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .qc-pill { display:inline-flex; align-items:center; gap:6px; background:#f4f5f8; border-radius:10px; padding:0 4px 0 10px; font-size:11.5px; color:#8a8c97; }
    .qc-pill select, .qc-pill input { border:0; background:transparent; padding:7px 8px 7px 0; font-family:'Poppins',sans-serif; font-size:12.5px; font-weight:500; color:#3b3d45; outline:none; width:auto; cursor:pointer; }
    .qc-pill input[type=number] { width:48px; cursor:text; padding-left:4px; }
    .qc-pill > i { display:none; }
    .qc-spacer { flex:1; }
    .qc-ic { width:30px; height:30px; border:0; border-radius:9px; background:transparent; color:#9a9cab; cursor:pointer; font-size:12px; }
    .qc-ic:hover { background:#f1f2f6; color:#3b3d45; } .qc-ic.danger:hover { background:#fdecea; color:#c0392b; } .qc-ic:disabled { opacity:.35; cursor:not-allowed; }
    .qc-choices { display:flex; flex-direction:column; gap:8px; margin-top:12px; }
    .qc-choice { display:flex; align-items:center; gap:10px; }
    .qc-choice .letter { width:28px; height:28px; border-radius:50%; background:#f3f4f6; color:#555; font-size:11.5px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .qc-choice.correct .letter { background:#10b981; color:#fff; }
    .qc-choice input[type=text] { flex:1; width:auto; font-family:'Poppins',sans-serif; font-size:13px; border:1.5px solid #e8e8e8; border-radius:8px; padding:9px 12px; outline:none; transition:border-color .2s; }
    .qc-choice input[type=text]:focus { border-color:var(--primary, #7B1D1D); }
    .qc-choice input[type=text][readonly] { background:#fafafa; color:#777; }
    .qc-choice input[type=radio] { accent-color:var(--primary, #7B1D1D); width:16px; height:16px; cursor:pointer; flex-shrink:0; }
    .qc-add-choice { align-self:flex-start; background:none; border:1.5px dashed #ddd; border-radius:8px; color:#777; font-size:12px; font-weight:600; padding:7px 12px; cursor:pointer; font-family:'Poppins',sans-serif; }
    .qc-add-choice:hover { border-color:var(--primary, #7B1D1D); color:var(--primary, #7B1D1D); }
    .qc-expl { margin-top:12px; }
    .qc-expl summary { cursor:pointer; font-size:12px; font-weight:600; color:#8a8c97; list-style:none; display:inline-flex; align-items:center; gap:6px; }
    .qc-expl summary::-webkit-details-marker { display:none; }
    .qc-expl summary:before { content:'\f054'; font-family:'Font Awesome 6 Free'; font-weight:900; font-size:9px; transition:transform .2s; }
    .qc-expl[open] summary:before { transform:rotate(90deg); }
    .qc-expl .rte { margin-top:8px; }
    .qc-empty { text-align:center; padding:34px 20px; border:1.5px dashed #e3e4ea; border-radius:16px; color:#9a9cab; font-size:13px; background:#fafafb; }
    .qc-empty i { display:block; font-size:30px; color:#e0d0d0; margin-bottom:10px; }
    .qc-actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:14px; }
    .qc-btn { display:inline-flex; align-items:center; gap:7px; border:1.5px dashed #d6d7de; background:#fff; color:#555; border-radius:10px; padding:9px 14px; font-size:12.5px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif; }
    .qc-btn:hover { border-color:var(--primary, #7B1D1D); color:var(--primary, #7B1D1D); background:var(--primary-light, #f5e8e8); }
</style>
<script>
window.QCards = (function () {
    const LETTERS = Array.from({ length: 26 }, (_, i) => String.fromCharCode(65 + i));
    const esc = s => String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    let seq = 0;

    const blank = type => type === 'true_false'
        ? { question_text: '', question_type: 'true_false', difficulty: 'Medium', explanation: '', points: 1, choices: [{ text: 'True', is_correct: true }, { text: 'False', is_correct: false }] }
        : { question_text: '', question_type: 'mcq', difficulty: 'Medium', explanation: '', points: 1, choices: [0, 1, 2, 3].map(i => ({ text: '', is_correct: i === 0 })) };

    function normalise(it) {
        const tf = it.question_type === 'true_false';
        let choices = (it.choices || []).map(c => ({ text: c.text ?? c.choice_text ?? '', is_correct: !!c.is_correct }));
        if (!choices.length) choices = blank(it.question_type).choices;
        if (tf) choices = [{ text: 'True', is_correct: !!(choices[0] && choices[0].is_correct) }, { text: 'False', is_correct: !!(choices[1] && choices[1].is_correct) }];
        if (!choices.some(c => c.is_correct) && choices.length) choices[0].is_correct = true;
        return Object.assign(blank(it.question_type), it, { choices, _k: ++seq });
    }

    function mount(el, opts) {
        opts = Object.assign({ uploadUrl: '', points: false, difficulty: true, items: [], onChange: () => {}, minimum: 0 }, opts);
        const items = (opts.items || []).map(normalise);
        const api = { items };

        function changed() { opts.onChange(api.get()); }

        function cardHtml(it, i) {
            const tf = it.question_type === 'true_false';
            const choices = it.choices.map((c, j) => `
                <div class="qc-choice ${c.is_correct ? 'correct' : ''}">
                    <input type="radio" name="qc-${it._k}" data-correct="${j}" ${c.is_correct ? 'checked' : ''} title="Mark as the correct answer">
                    <span class="letter">${tf ? (j ? 'F' : 'T') : LETTERS[j]}</span>
                    <input type="text" data-choice="${j}" value="${esc(c.text)}" placeholder="Choice ${LETTERS[j]}" ${tf ? 'readonly' : ''}>
                    ${!tf && it.choices.length > 2 ? `<button type="button" class="qc-ic danger" data-remove-choice="${j}" title="Remove choice"><i class="fas fa-times"></i></button>` : ''}
                </div>`).join('');
            return `
            <div class="qc" data-k="${it._k}">
                <div class="qc-head">
                    <span class="qc-num">${i + 1}</span>
                    <label class="qc-pill"><i class="fas fa-list-check"></i>
                        <select data-f="question_type"><option value="mcq" ${tf ? '' : 'selected'}>Multiple choice</option><option value="true_false" ${tf ? 'selected' : ''}>True / False</option></select></label>
                    ${opts.difficulty ? `<label class="qc-pill"><i class="fas fa-gauge-high"></i>
                        <select data-f="difficulty">${['Easy', 'Medium', 'Hard'].map(d => `<option ${it.difficulty === d ? 'selected' : ''}>${d}</option>`).join('')}</select></label>` : ''}
                    ${opts.points ? `<label class="qc-pill">Points <input type="number" data-f="points" min="1" max="100" value="${it.points || 1}"></label>` : ''}
                    <span class="qc-spacer"></span>
                    <button type="button" class="qc-ic" data-move="-1" title="Move up" ${i === 0 ? 'disabled' : ''}><i class="fas fa-arrow-up"></i></button>
                    <button type="button" class="qc-ic" data-move="1" title="Move down" ${i === items.length - 1 ? 'disabled' : ''}><i class="fas fa-arrow-down"></i></button>
                    <button type="button" class="qc-ic" data-dup title="Duplicate"><i class="far fa-clone"></i></button>
                    <button type="button" class="qc-ic danger" data-del title="Remove question"><i class="fas fa-trash"></i></button>
                </div>
                <textarea class="rte-src" data-rte-collapsed="1" data-f="question_text" data-upload-url="${esc(opts.uploadUrl)}" placeholder="Type the question...">${esc(it.question_text)}</textarea>
                <div class="qc-choices">
                    ${choices}
                    ${!tf && it.choices.length < 26 ? '<button type="button" class="qc-add-choice" data-add-choice><i class="fas fa-plus"></i> Add choice</button>' : ''}
                </div>
                <details class="qc-expl" ${it.explanation ? 'open' : ''}>
                    <summary>Explanation <span style="font-weight:400;color:#b5b7c1;">(shown after answering)</span></summary>
                    <textarea class="rte-src" data-rte-size="sm" data-f="explanation" data-upload-url="${esc(opts.uploadUrl)}" placeholder="Why is the correct answer correct? (optional)">${esc(it.explanation)}</textarea>
                </details>
            </div>`;
        }

        function render() {
            if (!items.length) {
                el.innerHTML = `<div class="qc-empty"><i class="fas fa-clipboard-question"></i>No questions yet. Add the first one below.</div>
                    <div class="qc-actions"><button type="button" class="qc-btn" data-add="mcq"><i class="fas fa-plus"></i> Multiple choice</button><button type="button" class="qc-btn" data-add="true_false"><i class="fas fa-plus"></i> True / False</button></div>`;
            } else {
                el.innerHTML = `<div class="qc-list">${items.map(cardHtml).join('')}</div>
                    <div class="qc-actions"><button type="button" class="qc-btn" data-add="mcq"><i class="fas fa-plus"></i> Add multiple choice</button><button type="button" class="qc-btn" data-add="true_false"><i class="fas fa-plus"></i> Add true / false</button></div>`;
            }
            RTE.init(el);
        }

        const find = target => { const card = target.closest('.qc'); return card ? items.findIndex(x => String(x._k) === card.dataset.k) : -1; };

        el.addEventListener('input', ev => {
            const i = find(ev.target); if (i < 0) return;
            const f = ev.target.dataset.f;
            if (f) items[i][f] = f === 'points' ? (parseInt(ev.target.value, 10) || 1) : ev.target.value;
            else if (ev.target.dataset.choice !== undefined) items[i].choices[+ev.target.dataset.choice].text = ev.target.value;
            changed();
        });
        el.addEventListener('change', ev => {
            const i = find(ev.target); if (i < 0) return;
            const it = items[i];
            if (ev.target.dataset.correct !== undefined) {
                it.choices.forEach((c, j) => { c.is_correct = j === +ev.target.dataset.correct; });
                ev.target.closest('.qc-choices').querySelectorAll('.qc-choice').forEach((row, j) => row.classList.toggle('correct', j === +ev.target.dataset.correct));
                changed();
            } else if (ev.target.dataset.f === 'question_type') {
                const keep = { question_text: it.question_text, explanation: it.explanation, difficulty: it.difficulty, points: it.points, id: it.id };
                items[i] = Object.assign(normalise(Object.assign(blank(ev.target.value), keep)), { _k: it._k });
                render(); changed();
            } else if (ev.target.dataset.f === 'difficulty') { it.difficulty = ev.target.value; changed(); }
        });
        el.addEventListener('click', ev => {
            const add = ev.target.closest('[data-add]');
            if (add) { api.add(add.dataset.add); return; }
            const btn = ev.target.closest('button'); if (!btn) return;
            const i = find(btn); if (i < 0) return;
            const it = items[i];
            if (btn.dataset.move) { const j = i + (+btn.dataset.move); if (j < 0 || j >= items.length) return; [items[i], items[j]] = [items[j], items[i]]; render(); changed(); }
            else if (btn.hasAttribute('data-del')) { items.splice(i, 1); render(); changed(); }
            else if (btn.hasAttribute('data-dup')) { const copy = normalise(JSON.parse(JSON.stringify(Object.assign({}, it, { id: null })))); items.splice(i + 1, 0, copy); render(); changed(); }
            else if (btn.hasAttribute('data-add-choice')) { if (it.choices.length < 26) { it.choices.push({ text: '', is_correct: false }); render(); changed(); el.querySelectorAll('.qc')[i].querySelector(`[data-choice="${it.choices.length - 1}"]`)?.focus(); } }
            else if (btn.dataset.removeChoice !== undefined) {
                if (it.choices.length <= 2) return;
                const j = +btn.dataset.removeChoice, was = it.choices[j].is_correct;
                it.choices.splice(j, 1); if (was) it.choices[0].is_correct = true;
                render(); changed();
            }
        });

        api.add = type => {
            items.push(normalise(blank(type)));
            render(); changed();
            const last = el.querySelectorAll('.qc')[items.length - 1];
            if (last) { last.scrollIntoView({ behavior: 'smooth', block: 'center' }); last.querySelector('.rte-area')?.focus(); }
        };
        api.get = () => items.map(it => ({
            id: it.id || null, question_text: it.question_text, question_type: it.question_type, difficulty: it.difficulty,
            explanation: it.explanation, points: it.points || 1, choices: it.choices.map(c => ({ text: c.text, is_correct: c.is_correct })),
        }));
        api.set = list => { items.length = 0; (list || []).forEach(x => items.push(normalise(x))); render(); changed(); };
        api.render = render;

        render();
        return api;
    }

    return { mount };
})();
</script>
