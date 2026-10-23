{{-- Word-style editor for question stems and explanations.
     Any <textarea class="rte-src"> is upgraded; the textarea stays in the form and carries the HTML (sanitised again on save).
       data-upload-url  where inserted pictures are posted      data-rte-size="sm"  smaller page, ribbon starts collapsed   data-rte-collapsed="1"  ribbon starts collapsed (normal page)
     RTE.init(root) after injecting textareas; RTE.sync(textarea) after setting textarea.value in code. --}}
<style>
    .rte { --rte-line:#e8e9ee; --rte-ink:#3b3d45; --rte-soft:#f4f5f8; border:1.5px solid var(--rte-line); border-radius:14px; background:#fff; transition:border-color .2s, box-shadow .2s; position:relative; }
    .rte:focus-within { border-color:var(--primary, #7B1D1D); box-shadow:0 0 0 4px rgba(123,29,29,.07); }
    .rte button, .rte select, .rte input { font-family:'Poppins',sans-serif; }

    /* tabs */
    .rte-tabs { display:flex; align-items:center; gap:2px; padding:4px 10px 0; border-bottom:1px solid var(--rte-line); }
    .rte-tab { border:0; background:transparent; padding:8px 12px; font-size:12px; font-weight:500; color:#8a8c97; cursor:pointer; position:relative; transition:color .15s; }
    .rte-tab:hover { color:var(--rte-ink); }
    .rte-tab.on { color:var(--primary, #7B1D1D); font-weight:600; }
    .rte-tab.on:after { content:''; position:absolute; left:10px; right:10px; bottom:-1px; height:2px; border-radius:2px; background:var(--primary, #7B1D1D); }
    .rte-tab.ctx { color:#0d9488; } .rte-tab.ctx.on:after { background:#0d9488; }
    .rte-tab[hidden] { display:none; }
    .rte-collapse { margin-left:auto; border:0; background:transparent; color:#a0a2ad; font-size:11px; cursor:pointer; padding:6px 8px; border-radius:7px; }
    .rte-collapse:hover { background:var(--rte-soft); color:var(--rte-ink); }
    .rte.collapsed .rte-collapse i { transform:rotate(180deg); }

    /* ribbon */
    .rte-ribbon { display:flex; flex-wrap:wrap; align-items:stretch; padding:8px 10px; background:#fbfbfd; border-bottom:1px solid var(--rte-line); row-gap:4px; }
    .rte.collapsed .rte-ribbon { display:none; }
    .rte-panel { display:none; flex-wrap:wrap; align-items:stretch; row-gap:6px; width:100%; }
    .rte-panel.on { display:flex; }
    .rte-grp { display:flex; flex-direction:column; padding:0 10px; border-right:1px solid var(--rte-line); }
    .rte-grp:first-child { padding-left:2px; } .rte-grp:last-child { border-right:0; }
    .rte-grp-body { display:flex; align-items:center; flex-wrap:wrap; gap:2px; min-height:32px; flex:1; }
    .rte-grp-body.col { flex-direction:column; align-items:flex-start; justify-content:center; gap:4px; }
    .rte-row { display:flex; align-items:center; gap:2px; }
    .rte-grp-label { text-align:center; font-size:9.5px; letter-spacing:.06em; text-transform:uppercase; color:#b0b2bc; margin-top:4px; }
    .rte-btn { min-width:28px; height:28px; padding:0 6px; border:0; border-radius:8px; background:transparent; color:var(--rte-ink); font-size:12px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:4px; transition:background .12s, color .12s; }
    .rte-btn:hover { background:#eceef3; }
    .rte-btn:active { transform:scale(.94); }
    .rte-btn.on { background:var(--primary-light, #f5e8e8); color:var(--primary, #7B1D1D); }
    .rte-btn.big { flex-direction:column; height:48px; min-width:56px; font-size:10.5px; gap:3px; border-radius:10px; }
    .rte-btn.big i { font-size:17px; }
    .rte-btn.tiny { min-width:22px; padding:0 4px; font-size:11px; }
    .rte-split { display:inline-flex; align-items:center; border-radius:8px; }
    .rte-split:hover { background:#eceef3; }
    .rte-split .rte-btn { border-radius:8px; }
    .rte-split .rte-btn:first-child { flex-direction:column; gap:0; padding:0 5px; }
    .rte-split .rte-btn:last-child { min-width:14px; padding:0 3px; font-size:9px; color:#9a9cab; }
    .rte-split .rte-btn:hover { background:transparent; }
    .rte-swatch { display:block; width:14px; height:3px; border-radius:2px; margin-top:1px; }
    .rte-sel { height:28px; border:1px solid var(--rte-line); border-radius:8px; background:#fff; font-size:12px; color:#333; padding:0 6px; max-width:130px; cursor:pointer; outline:none; }
    .rte-sel:hover, .rte-sel:focus { border-color:#cfd1da; }
    .rte-sel.size { width:64px; text-align:center; font-weight:600; } .rte-sel.fam { width:132px; } .rte-sel.blk { width:112px; }
    .rte-num { width:60px; height:28px; border:1px solid var(--rte-line); border-radius:8px; padding:0 8px; font-size:12px; outline:none; }
    .rte-txt { width:160px; height:28px; border:1px solid var(--rte-line); border-radius:8px; padding:0 10px; font-size:12px; outline:none; }
    .rte-num:focus, .rte-txt:focus { border-color:var(--primary, #7B1D1D); }
    .rte-lbl { font-size:11px; color:#8a8c97; margin:0 4px; }

    /* writing surface: plain white, no frame inside the frame */
    .rte-body { position:relative; background:#fff; }
    .rte-area { min-height:140px; padding:18px 22px; font-family:'Poppins',sans-serif; font-size:13.5px; line-height:1.7; color:#1f2128; outline:none; overflow-wrap:anywhere; }
    .rte.sm .rte-area { min-height:64px; padding:12px 16px; font-size:12.5px; }
    .rte-area:empty:before { content:attr(data-placeholder); color:#b7b9c3; pointer-events:none; }
    .rte-area[contenteditable="false"] { background:#fafafa; color:#777; }
    .rte-area ul, .rte-area ol { margin:.3em 0 .3em 1.5em; padding:0; }
    .rte-area h1 { font-size:1.5em; margin:.4em 0; } .rte-area h2 { font-size:1.3em; margin:.4em 0; } .rte-area h3 { font-size:1.15em; margin:.4em 0; } .rte-area h4 { font-size:1em; margin:.4em 0; }
    .rte-area table { border-collapse:collapse; max-width:100%; margin:.4em 0; }
    .rte-area td, .rte-area th { min-width:40px; vertical-align:top; }
    .rte-area img { max-width:100%; cursor:pointer; border-radius:4px; }
    .rte-area hr { border:0; border-top:1px solid #c8cad2; margin:.6em 0; }
    .rte-area blockquote { margin:.4em 0 .4em 1.5em; padding:0; }
    .rte-area.drop { background:var(--primary-light, #f5e8e8); }
    .rte-imgbox { position:absolute; border:2px solid #3b82f6; border-radius:4px; pointer-events:none; display:none; z-index:5; }
    .rte-handle { position:absolute; width:12px; height:12px; background:#3b82f6; border:2px solid #fff; border-radius:50%; cursor:nwse-resize; pointer-events:auto; box-shadow:0 1px 3px rgba(0,0,0,.25); }
    .rte-status { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:6px 14px; font-size:11px; color:#a0a2ad; border-top:1px solid #f0f1f5; border-radius:0 0 13px 13px; }
    .rte-status .err { color:#b91c1c; }

    /* popups */
    .rte-pop { position:fixed; z-index:99999; background:#fff; border:1px solid var(--rte-line, #e8e9ee); border-radius:12px; box-shadow:0 12px 32px rgba(20,22,40,.16); padding:10px; font-family:'Poppins',sans-serif; font-size:12px; }
    .rte-pal { display:grid; grid-template-columns:repeat(10, 20px); gap:4px; }
    .rte-pal button { width:20px; height:20px; border:1px solid rgba(0,0,0,.12); border-radius:50%; padding:0; cursor:pointer; transition:transform .1s; }
    .rte-pal button:hover { transform:scale(1.2); }
    .rte-pop .rte-item { display:flex; align-items:center; gap:8px; width:100%; border:0; background:transparent; padding:7px 9px; border-radius:8px; font-size:12px; cursor:pointer; color:#333; text-align:left; }
    .rte-pop .rte-item:hover { background:#f3f4f8; }
    .rte-tgrid { display:grid; grid-template-columns:repeat(8, 20px); gap:3px; }
    .rte-tgrid span { width:20px; height:20px; border:1px solid #d4d6de; border-radius:4px; background:#fff; }
    .rte-tgrid span.hot { background:var(--primary-light, #f5e8e8); border-color:var(--primary, #7B1D1D); }
    .rte-tlabel { text-align:center; margin-top:8px; color:#555; font-weight:500; }
    .rte-sym { display:grid; grid-template-columns:repeat(10, 30px); gap:2px; }
    .rte-sym button { width:30px; height:30px; border:0; border-radius:8px; background:transparent; cursor:pointer; font-size:14px; }
    .rte-sym button:hover { background:#f3f4f8; }
    /* ── modern flat toolbar ── */
    .rte-top { position:sticky; top:0; z-index:6; background:#fff; border-radius:14px 14px 0 0; }
    .rte-tabs { padding:8px 10px 6px; gap:4px; border-bottom:0; }
    .rte-tab { padding:5px 14px; border-radius:999px; color:#6f717d; }
    .rte-tab:hover { background:var(--rte-soft); color:var(--rte-ink); }
    .rte-tab.on { background:var(--primary-light, #f5e8e8); color:var(--primary, #7B1D1D); }
    .rte-tab.on:after { display:none; }
    .rte-tab.ctx.on { background:#d9f3ef; color:#0d9488; }
    .rte-ribbon { background:#fff; border-bottom:1px solid var(--rte-line); padding:2px 10px 8px; }
    .rte-grp { padding:0 8px; border-right:1px solid var(--rte-line); display:flex; align-items:center; min-height:34px; }
    .rte-grp:first-child { padding-left:0; }
    .rte-grp-label { display:none; }
    .rte-grp-body, .rte-grp-body.col { flex-direction:row; align-items:center; flex-wrap:wrap; gap:2px; }
    .rte-btn { height:30px; min-width:30px; border-radius:9px; }
    .rte-btn.big, .rte-btn.big i { flex-direction:row; }
    .rte-btn.big { height:30px; min-width:0; padding:0 11px; font-size:12px; font-weight:500; gap:7px; border-radius:9px; background:var(--rte-soft); }
    .rte-btn.big:hover { background:#e9ebf1; }
    .rte-btn.big i { font-size:13px; }
    .rte-aa { font-size:13px; font-weight:700; line-height:1; } .rte-aa.sm { font-size:11px; } .rte-aa sup { font-size:8px; margin-left:1px; }
    .rte-sel { height:30px; background:var(--rte-soft); border-color:transparent; border-radius:9px; }
    .rte-sel:hover, .rte-sel:focus { background:#eceef3; border-color:transparent; }
    .rte-num, .rte-txt { height:30px; background:var(--rte-soft); border-color:transparent; border-radius:9px; }
    .rte-num:focus, .rte-txt:focus { background:#fff; }
    .rte-status { border-top:0; padding:8px 16px; }
    .rte-area { padding:20px 24px; }
    @media (max-width:760px) { .rte-grp { border-right:0; padding:0 4px 4px 0; } .rte-sel.fam { width:110px; } }
    .rte-sel.blk { margin-left:6px; }
    .rte-plabel { font-size:10px; letter-spacing:.06em; text-transform:uppercase; color:#a0a2ad; margin:8px 4px 3px; }
    .rte-plabel:first-child { margin-top:0; }
    .rte-linep { width:230px; }
    .rte-linep .rte-item { gap:12px; } .rte-linep .rte-item.on { background:var(--primary-light, #f5e8e8); color:var(--primary, #7B1D1D); font-weight:600; }
    .rte-lp { display:inline-block; width:90px; height:0; flex-shrink:0; }
    .rte-linep .rte-pal { grid-template-columns:repeat(10, 20px); }
    .rte-lprev { padding:10px 6px; background:#f7f8fa; border-radius:8px; } .rte-lsample { height:0; }
    .rte-linep .rte-ins { margin-top:8px; background:var(--primary, #7B1D1D); color:#fff; justify-content:center; font-weight:600; } .rte-linep .rte-ins:hover { background:var(--primary-hover, #6a1818); }
    .rte-shapes { display:grid; grid-template-columns:repeat(6, 42px); gap:4px; }
    .rte-shapes button { width:42px; height:42px; border:0; border-radius:10px; background:transparent; cursor:pointer; display:flex; align-items:center; justify-content:center; }
    .rte-shapes button:hover { background:#f1f2f6; }
    .rte-shapes button span { display:block; }
    .rte-area div[style*="inline-block"] { cursor:pointer; }
    .rte-area img[data-pending] { opacity:.55; filter:saturate(.6); }
    .rte-area td[data-rsel], .rte-area th[data-rsel] { background-image:linear-gradient(rgba(59,130,246,.25), rgba(59,130,246,.25)); outline:1px solid rgba(59,130,246,.7); outline-offset:-1px; }
    .rte-area.rte-selecting, .rte-area.rte-selecting * { user-select:none; cursor:cell; }
    .rte-area.rte-dragging, .rte-area.rte-dragging * { user-select:none; }
    .rte-thandle { position:absolute; width:11px; height:11px; border-radius:3px; background:#fff; border:2px solid #3b82f6; cursor:nwse-resize; z-index:5; box-shadow:0 1px 3px rgba(0,0,0,.2); }
    .rte-thandle[hidden] { display:none; }
    .rte-splitrow { display:flex; gap:10px; padding:2px 4px 6px; } .rte-splitrow label { display:flex; flex-direction:column; gap:4px; font-size:11px; color:#777; }
    .rte-splitrow input { width:78px; height:30px; border:1px solid var(--rte-line, #e8e9ee); border-radius:8px; padding:0 8px; font-size:12px; }
    .rte-wrow { display:flex; gap:4px; padding:0 4px; }
    .rte-handle[data-pos="nw"] { left:-7px; top:-7px; cursor:nwse-resize; } .rte-handle[data-pos="ne"] { right:-7px; top:-7px; cursor:nesw-resize; }
    .rte-handle[data-pos="sw"] { left:-7px; bottom:-7px; cursor:nesw-resize; } .rte-handle[data-pos="se"] { right:-7px; bottom:-7px; cursor:nwse-resize; }
    .rte-imgbox[data-tip]:after { content:attr(data-tip) " px"; position:absolute; left:50%; top:-26px; transform:translateX(-50%); background:#1f2937; color:#fff; font:600 11px 'Poppins',sans-serif; padding:3px 8px; border-radius:6px; white-space:nowrap; }
</style>
<script>
window.RTE = (function () {
    const HTML_RE = /<\/?(p|div|br|b|strong|i|em|u|s|strike|del|sub|sup|ul|ol|li|span|font|h[1-4]|blockquote|hr|table|thead|tbody|tfoot|tr|td|th|img)\b[^>]*>/i;
    const FONTS = ['Poppins', 'Arial', 'Calibri', 'Cambria', 'Courier New', 'Georgia', 'Segoe UI', 'Tahoma', 'Times New Roman', 'Verdana'];
    const SIZES = [8, 9, 10, 11, 12, 14, 16, 18, 20, 24, 28, 32, 36, 48];
    const PALETTE = [
        '#000000', '#434343', '#666666', '#999999', '#b7b7b7', '#cccccc', '#d9d9d9', '#efefef', '#f3f3f3', '#ffffff',
        '#980000', '#ff0000', '#ff9900', '#ffff00', '#00ff00', '#00ffff', '#4a86e8', '#0000ff', '#9900ff', '#ff00ff',
        '#e6b8af', '#f4cccc', '#fce5cd', '#fff2cc', '#d9ead3', '#d0e0e3', '#c9daf8', '#cfe2f3', '#d9d2e9', '#ead1dc',
        '#dd7e6b', '#ea9999', '#f9cb9c', '#ffe599', '#b6d7a8', '#a2c4c9', '#a4c2f4', '#9fc5e8', '#b4a7d6', '#d5a6bd',
        '#a61c00', '#cc0000', '#e69138', '#f1c232', '#6aa84f', '#45818e', '#3c78d8', '#3d85c6', '#674ea7', '#a64d79',
    ];
    const SYMBOLS = '₱ $ € £ ¥ ¢ % ‰ ° ± × ÷ = ≠ ≈ ≤ ≥ < > ∞ √ ∑ π Δ µ ² ³ ½ ¼ ¾ → ← ↑ ↓ ↔ ✓ ✗ • © ® ™ § ¶ † α β γ θ λ σ ω'.split(' ');
    const SHAPES = [
        { n: 'Rectangle', w: 130, h: 80, r: '0px' }, { n: 'Rounded rectangle', w: 130, h: 80, r: '16px' }, { n: 'Square', w: 90, h: 90, r: '0px' },
        { n: 'Circle', w: 90, h: 90, r: '50%' }, { n: 'Ellipse', w: 130, h: 80, r: '50%' },
        { n: 'Triangle', w: 100, h: 90, poly: '50% 0%, 0% 100%, 100% 100%' }, { n: 'Right triangle', w: 100, h: 90, poly: '0% 0%, 0% 100%, 100% 100%' },
        { n: 'Diamond', w: 100, h: 100, poly: '50% 0%, 100% 50%, 50% 100%, 0% 50%' }, { n: 'Pentagon', w: 100, h: 95, poly: '50% 0%, 100% 38%, 82% 100%, 18% 100%, 0% 38%' },
        { n: 'Hexagon', w: 110, h: 95, poly: '25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0% 50%' },
        { n: 'Star', w: 100, h: 95, poly: '50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%' },
        { n: 'Plus', w: 90, h: 90, poly: '35% 0%, 65% 0%, 65% 35%, 100% 35%, 100% 65%, 65% 65%, 65% 100%, 35% 100%, 35% 65%, 0% 65%, 0% 35%, 35% 35%' },
        { n: 'Arrow right', w: 130, h: 80, poly: '0% 30%, 60% 30%, 60% 10%, 100% 50%, 60% 90%, 60% 70%, 0% 70%' },
        { n: 'Arrow left', w: 130, h: 80, poly: '100% 30%, 40% 30%, 40% 10%, 0% 50%, 40% 90%, 40% 70%, 100% 70%' },
        { n: 'Arrow up', w: 80, h: 130, poly: '30% 100%, 30% 40%, 10% 40%, 50% 0%, 90% 40%, 70% 40%, 70% 100%' },
        { n: 'Arrow down', w: 80, h: 130, poly: '30% 0%, 30% 60%, 10% 60%, 50% 100%, 90% 60%, 70% 60%, 70% 0%' },
    ];
    const shapeStyle = (sh, w, h) => 'display:inline-block;vertical-align:middle;width:' + w + 'px;height:' + h + 'px;margin:4px;' +
        (sh.poly ? 'background-color:#7B1D1D;clip-path:polygon(' + sh.poly + ')' : 'background-color:#f5e8e8;border:2px solid #7B1D1D;border-radius:' + sh.r);
    const isShape = el => !!el && el.tagName === 'DIV' && el.style.display === 'inline-block';
    const MAX_BYTES = 3 * 1024 * 1024;
    const OK_TYPES = ['image/png', 'image/jpeg', 'image/webp'];
    const BLOCKS = 'p,div,li,h1,h2,h3,h4,td,th,blockquote';

    const esc = s => String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    // Pictures are stored as "question-images/<file>"; the editor needs a URL that loads on this server.
    const IMG_BASE = @json(asset('storage/question-images')) + '/';
    const toEditorHtml = v => {
        v = String(v ?? '');
        return HTML_RE.test(v) ? v.replace(/(<img\b[^>]*?\ssrc=")question-images\//gi, '$1' + IMG_BASE) : esc(v).replace(/\r?\n/g, '<br>');
    };
    const rgbToHex = c => { const m = /rgba?\((\d+),\s*(\d+),\s*(\d+)/.exec(c || ''); return m ? '#' + [m[1], m[2], m[3]].map(n => (+n).toString(16).padStart(2, '0')).join('') : c; };

    const b = (act, icon, title, extra = '') => `<button type="button" class="rte-btn ${extra}" data-act="${act}" title="${title}"><i class="${icon}"></i></button>`;
    const bigBtn = (act, icon, label, title) => `<button type="button" class="rte-btn big" data-act="${act}" title="${title}"><i class="${icon}"></i><span>${label}</span></button>`;
    const grp = (label, body, cls = '') => `<div class="rte-grp" title="${label}"><div class="rte-grp-body ${cls}">${body}</div></div>`;
    const colorBtn = (kind, icon, title, color) => `<span class="rte-split"><button type="button" class="rte-btn" data-act="color" data-kind="${kind}" title="${title}"><i class="${icon}"></i><span class="rte-swatch" data-sw="${kind}" style="background:${color}"></span></button><button type="button" class="rte-btn" data-act="color-menu" data-kind="${kind}" title="${title} options"><i class="fas fa-caret-down"></i></button></span>`;

    // presets for the size box; any number from 1 to 200 can also be typed
    if (!document.getElementById('rte-sizes')) {
        const dl = document.createElement('datalist'); dl.id = 'rte-sizes';
        dl.innerHTML = SIZES.concat([60, 72, 96]).map(n => `<option value="${n}">`).join('');
        (document.body || document.documentElement).appendChild(dl);
    }
    function ribbonHtml() {
        const fam = `<select class="rte-sel fam" data-sel="fontName" title="Font">${FONTS.map(f => `<option style="font-family:${f}">${f}</option>`).join('')}</select>`;
        const size = `<input type="text" class="rte-sel size" data-sel="size" list="rte-sizes" inputmode="decimal" autocomplete="off" title="Font size in points: pick one or type any size (1-200) and press Enter">`;
        const blk = `<select class="rte-sel blk" data-sel="block" title="Style"><option value="DIV">Normal</option><option value="H1">Heading 1</option><option value="H2">Heading 2</option><option value="H3">Heading 3</option><option value="H4">Heading 4</option></select>`;
        const home =
            grp('Undo', b('undo', 'fas fa-rotate-left', 'Undo (Ctrl+Z)') + b('redo', 'fas fa-rotate-right', 'Redo (Ctrl+Y)')) +
            grp('Font',
                `<div class="rte-row">${fam}${size}${b('grow', 'rte-ico', 'Increase font size').replace('<i class="rte-ico"></i>', '<span class="rte-aa">A<sup>+</sup></span>')}${b('shrink', 'rte-ico', 'Decrease font size').replace('<i class="rte-ico"></i>', '<span class="rte-aa sm">A<sup>&minus;</sup></span>')}${b('clear', 'fas fa-eraser', 'Clear all formatting')}</div>` +
                `<div class="rte-row">${b('bold', 'fas fa-bold', 'Bold (Ctrl+B)')}${b('italic', 'fas fa-italic', 'Italic (Ctrl+I)')}${b('underline', 'fas fa-underline', 'Underline (Ctrl+U)')}${b('strikeThrough', 'fas fa-strikethrough', 'Strikethrough')}${b('subscript', 'fas fa-subscript', 'Subscript')}${b('superscript', 'fas fa-superscript', 'Superscript')}${colorBtn('hiliteColor', 'fas fa-highlighter', 'Text highlight color', '#ffff00')}${colorBtn('foreColor', 'fas fa-font', 'Font color', '#c0392b')}</div>`, 'col') +
            grp('Paragraph',
                `<div class="rte-row">${b('insertUnorderedList', 'fas fa-list-ul', 'Bullets')}${b('insertOrderedList', 'fas fa-list-ol', 'Numbering')}${b('outdent', 'fas fa-outdent', 'Decrease indent')}${b('indent', 'fas fa-indent', 'Increase indent')}${b('spacing', 'fas fa-arrows-up-down', 'Line spacing')}</div>` +
                `<div class="rte-row">${b('justifyLeft', 'fas fa-align-left', 'Align left (Ctrl+L)')}${b('justifyCenter', 'fas fa-align-center', 'Center (Ctrl+E)')}${b('justifyRight', 'fas fa-align-right', 'Align right (Ctrl+R)')}${b('justifyFull', 'fas fa-align-justify', 'Justify (Ctrl+J)')}${blk}</div>`, 'col');
        const insert =
            grp('Table', bigBtn('table-menu', 'fas fa-table', 'Table', 'Insert a table')) +
            grp('Illustrations', bigBtn('picture', 'fas fa-image', 'Picture', 'Insert a picture from your device (you can also drag, drop or paste one)') + bigBtn('shape-menu', 'fas fa-shapes', 'Shapes', 'Insert a shape')) +
            grp('Lines', bigBtn('line-menu', 'fas fa-minus', 'Line', 'Insert a line: choose its type, weight and color')) +
            grp('Symbols', bigBtn('symbol-menu', 'fas fa-omega', 'Symbol', 'Insert a symbol'));
        const table =
            grp('Rows & columns', bigBtn('row-above', 'fas fa-arrow-up-long', 'Row above', 'Insert row above') + bigBtn('row-below', 'fas fa-arrow-down-long', 'Row below', 'Insert row below') + bigBtn('col-left', 'fas fa-arrow-left-long', 'Column left', 'Insert column to the left') + bigBtn('col-right', 'fas fa-arrow-right-long', 'Column right', 'Insert column to the right')) +
            grp('Delete', bigBtn('row-del', 'fas fa-grip-lines', 'Delete row', 'Delete row') + bigBtn('col-del', 'fas fa-grip-lines-vertical', 'Delete column', 'Delete column') + bigBtn('tbl-del', 'fas fa-trash', 'Delete table', 'Delete table')) +
            grp('Merge', bigBtn('tbl-merge', 'fas fa-object-group', 'Merge cells', 'Merge the selected cells (drag across cells, or Shift+click, to select them)') + bigBtn('tbl-split-menu', 'fas fa-table-cells-large', 'Split cell', 'Split this cell into rows and columns')) +
            grp('Cell size',
                `<span class="rte-lbl">Height</span><input type="number" class="rte-num" data-tbl="rowh" min="20" max="2000" title="Row height in pixels"><span class="rte-lbl">Width</span><input type="number" class="rte-num" data-tbl="colw" min="30" max="2000" title="Column width in pixels">` +
                bigBtn('tbl-dist-cols', 'fas fa-table-columns', 'Even columns', 'Make all columns the same width') + bigBtn('tbl-dist-rows', 'fas fa-bars', 'Even rows', 'Make all rows the same height') +
                bigBtn('tbl-fit-win', 'fas fa-expand', 'Fit window', 'Stretch the table to the full width') + bigBtn('tbl-fit-content', 'fas fa-compress', 'Fit contents', 'Shrink columns to their contents')) +
            grp('Alignment', b('valign', 'fas fa-arrows-up-to-line', 'Align cell top').replace('data-act="valign"', 'data-act="valign" data-v="top"') + b('valign', 'fas fa-arrows-up-down', 'Center cell vertically').replace('data-act="valign"', 'data-act="valign" data-v="middle"') + b('valign', 'fas fa-arrows-down-to-line', 'Align cell bottom').replace('data-act="valign"', 'data-act="valign" data-v="bottom"')) +
            grp('Table style', bigBtn('tbl-header', 'fas fa-heading', 'Header row', 'Make the first row a header') + bigBtn('border-menu', 'fas fa-border-all', 'Borders', 'Borders: which sides, weight and color') + colorBtn('shade', 'fas fa-fill-drip', 'Shade the selected cells', '#f3f4f6'));
        const picture =
            grp('Size', `<div class="rte-row">${[25, 50, 75, 100].map(p => `<button type="button" class="rte-btn" data-act="img-size" data-v="${p}" title="${p}% of the width">${p}%</button>`).join('')}</div><div class="rte-row"><span class="rte-lbl">Width</span><input type="number" class="rte-num" data-img="width" min="20" max="2000" title="Width in pixels"><span class="rte-lbl">px</span></div>`, 'col') +
            grp('Arrange', b('justifyLeft', 'fas fa-align-left', 'Align left') + b('justifyCenter', 'fas fa-align-center', 'Center') + b('justifyRight', 'fas fa-align-right', 'Align right')) +
            grp('Accessibility', `<input type="text" class="rte-txt" data-img="alt" placeholder="Describe the picture" title="Alt text">`) +
            grp('Picture border',
                colorBtn('imgborder', 'fas fa-border-top-left', 'Picture border color', '#7B1D1D') +
                `<select class="rte-sel" data-img="bweight" title="Border weight" style="width:96px">${[0, 1, 2, 3, 4, 6, 8].map(w => `<option value="${w}">${w ? w + ' px' : 'No border'}</option>`).join('')}</select>` +
                `<select class="rte-sel" data-img="bstyle" title="Border type" style="width:84px">${['solid', 'dashed', 'dotted', 'double'].map(t => `<option value="${t}">${t[0].toUpperCase() + t.slice(1)}</option>`).join('')}</select>` +
                `<select class="rte-sel" data-img="radius" title="Corners" style="width:110px"><option value="0px">Square corners</option><option value="8px">Rounded</option><option value="18px">More rounded</option><option value="50%">Circle / oval</option></select>`) +
            grp('Picture', bigBtn('img-replace', 'fas fa-arrows-rotate', 'Replace', 'Replace this picture') + bigBtn('img-del', 'fas fa-trash', 'Delete', 'Delete this picture'));
        const shape =
            grp('Shape style', colorBtn('fill', 'fas fa-fill-drip', 'Shape fill color', '#f5e8e8') + colorBtn('outline', 'fas fa-pen', 'Shape outline color', '#7B1D1D') +
                `<select class="rte-sel" data-shp="weight" title="Outline weight" style="width:78px">${[0, 1, 2, 3, 4, 6, 8].map(w => `<option value="${w}">${w ? w + ' px' : 'No line'}</option>`).join('')}</select>`) +
            grp('Size', `<span class="rte-lbl">W</span><input type="number" class="rte-num" data-shp="width" min="10" max="2000" title="Width in pixels"><span class="rte-lbl">H</span><input type="number" class="rte-num" data-shp="height" min="10" max="2000" title="Height in pixels">`) +
            grp('Arrange', b('justifyLeft', 'fas fa-align-left', 'Align left') + b('justifyCenter', 'fas fa-align-center', 'Center') + b('justifyRight', 'fas fa-align-right', 'Align right')) +
            grp('Shape', bigBtn('shape-del', 'fas fa-trash', 'Delete', 'Delete this shape'));
        return {
            tabs: `<button type="button" class="rte-tab on" data-tab="home">Home</button><button type="button" class="rte-tab" data-tab="insert">Insert</button><button type="button" class="rte-tab ctx" data-tab="table" hidden>Table Layout</button><button type="button" class="rte-tab ctx" data-tab="picture" hidden>Picture Format</button><button type="button" class="rte-tab ctx" data-tab="shape" hidden>Shape Format</button><button type="button" class="rte-collapse" data-act="collapse" title="Collapse or expand the ribbon"><i class="fas fa-chevron-up"></i></button>`,
            panels: `<div class="rte-panel on" data-panel="home">${home}</div><div class="rte-panel" data-panel="insert">${insert}</div><div class="rte-panel" data-panel="table">${table}</div><div class="rte-panel" data-panel="picture">${picture}</div><div class="rte-panel" data-panel="shape">${shape}</div>`,
        };
    }

    // ── popups (one at a time, fixed to the page so no container can clip them) ──
    let active = null; // the editor that last had focus; one shared listener serves them all
    document.addEventListener('selectionchange', () => {
        if (!active) return;
        const s = window.getSelection();
        if (s.rangeCount && active.area.contains(s.anchorNode) && document.activeElement === active.area) { active.remember(); active.refresh(); }
    });
    window.addEventListener('resize', () => active && active.placeImgBox());
    let openPop = null;
    function closePop() { if (openPop) { openPop.remove(); openPop = null; } }
    function showPop(anchor, html) {
        closePop();
        const pop = document.createElement('div');
        pop.className = 'rte-pop';
        pop.innerHTML = html;
        pop.addEventListener('mousedown', e => { if (!e.target.closest('input')) e.preventDefault(); });
        document.body.appendChild(pop);
        const r = anchor.getBoundingClientRect();
        pop.style.left = Math.max(6, Math.min(r.left, window.innerWidth - pop.offsetWidth - 8)) + 'px';
        pop.style.top = Math.min(r.bottom + 4, window.innerHeight - pop.offsetHeight - 8) + 'px';
        openPop = pop;
        return pop;
    }
    document.addEventListener('mousedown', e => { if (openPop && !openPop.contains(e.target) && !e.target.closest('[data-act$="-menu"], [data-act="spacing"]')) closePop(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closePop(); });
    window.addEventListener('resize', closePop);

    function init(root) { (root || document).querySelectorAll('textarea.rte-src').forEach(build); }

    function build(ta) {
        if (ta._rte) return;
        const readonly = ta.hasAttribute('readonly');
        const small = ta.dataset.rteSize === 'sm';
        const uploadUrl = ta.dataset.uploadUrl || '';
        const rib = ribbonHtml();

        const wrap = document.createElement('div');
        wrap.className = 'rte' + (small ? ' sm' : '') + (small || ta.dataset.rteCollapsed === '1' ? ' collapsed' : '');
        wrap.innerHTML =
            (readonly ? '' : `<div class="rte-top"><div class="rte-tabs">${rib.tabs}</div><div class="rte-ribbon">${rib.panels}</div></div>`) +
            `<div class="rte-body"><div class="rte-area" spellcheck="true"></div><div class="rte-imgbox"><span class="rte-handle" data-pos="nw"></span><span class="rte-handle" data-pos="ne"></span><span class="rte-handle" data-pos="sw"></span><span class="rte-handle" data-pos="se"></span></div><span class="rte-thandle" title="Drag to resize the whole table" hidden></span></div>` +
            `<div class="rte-status"><span class="msg"></span><span class="count"></span></div>` +
            `<input type="file" accept="image/png,image/jpeg,image/webp" multiple style="display:none" class="rte-file">`;
        const area = wrap.querySelector('.rte-area');
        const imgbox = wrap.querySelector('.rte-imgbox');
        const thandle = wrap.querySelector('.rte-thandle');
        const fileInput = wrap.querySelector('.rte-file');
        const msgEl = wrap.querySelector('.msg');
        const countEl = wrap.querySelector('.count');
        area.contentEditable = readonly ? 'false' : 'true';
        area.dataset.placeholder = ta.getAttribute('placeholder') || '';
        area.innerHTML = toEditorHtml(ta.value);

        ta.style.display = 'none';
        ta.classList.remove('rte-src');
        ta.parentNode.insertBefore(wrap, ta);

        const colors = { foreColor: '#c0392b', hiliteColor: '#ffff00', shade: '#f3f4f6', fill: '#f5e8e8', outline: '#7B1D1D', imgborder: '#7B1D1D' };
        let saved = null, selObj = null, replacing = false;

        // ── selection helpers ──
        const inArea = n => n && area.contains(n);
        const remember = () => { const s = window.getSelection(); if (s.rangeCount && inArea(s.anchorNode)) saved = s.getRangeAt(0).cloneRange(); };
        const restore = () => { area.focus(); if (saved) { const s = window.getSelection(); s.removeAllRanges(); s.addRange(saved); } };
        const elOf = n => (n && n.nodeType === 3 ? n.parentElement : n);
        const closest = (sel) => { const s = window.getSelection(); const e = s.rangeCount ? elOf(s.anchorNode) : null; const c = e && e.closest ? e.closest(sel) : null; return c && area.contains(c) ? c : null; };

        // Chrome splits a line's border into longhand properties; keep it as one declaration so type, weight and colour survive saving.
        const normaliseLines = () => area.querySelectorAll('hr').forEach(hr => {
            const cs = getComputedStyle(hr), w = parseFloat(cs.borderTopWidth);
            if (!w || cs.borderTopStyle === 'none') return;
            const line = w + 'px ' + cs.borderTopStyle + ' ' + cs.borderTopColor;
            if (hr.style.borderTop !== line || hr.style.borderBottomStyle) hr.setAttribute('style', 'border:0;border-top:' + line + ';margin:.6em 0');
        });
        const SIDES = ['Top', 'Right', 'Bottom', 'Left'];
        const setBorders = (td, map) => { // map: side -> "1px solid #999" | null ; keeps every other declaration
            const keep = td.style.cssText.split(';').map(x => x.trim()).filter(x => x && !/^border/i.test(x));
            const vals = SIDES.map(k => map[k.toLowerCase()] || null);
            if (vals.every(v => v && v === vals[0])) keep.push('border:' + vals[0]);
            else SIDES.forEach((k, i) => { if (vals[i]) keep.push('border-' + k.toLowerCase() + ':' + vals[i]); });
            td.setAttribute('style', keep.join(';'));
        };
        // Chrome splits mixed borders into border-width/-style/-color; the server keeps only per-side shorthands.
        const normaliseCells = () => area.querySelectorAll('td, th').forEach(td => {
            if (!/border-(width|style|color)\s*:/.test(td.getAttribute('style') || '')) return;
            const cs = getComputedStyle(td), map = {};
            SIDES.forEach(k => { const w = parseFloat(cs['border' + k + 'Width']), st = cs['border' + k + 'Style']; map[k.toLowerCase()] = w > 0 && st !== 'none' ? w + 'px ' + st + ' ' + cs['border' + k + 'Color'] : null; });
            setBorders(td, map);
        });
        const push = () => {
            normaliseLines();
            normaliseCells();
            const plain = area.textContent.replace(/\u200B/g, '').trim();
            const hasObj = area.querySelector('img, table, hr, ul, ol, [style*="inline-block"]');
            const clone = area.cloneNode(true);
            clone.querySelectorAll('[data-rsel]').forEach(x => x.removeAttribute('data-rsel'));
            clone.querySelectorAll('span').forEach(x => { if (!x.textContent.replace(/\u200B/g, '') && !x.children.length) x.remove(); });
            let html = !plain && !hasObj ? '' : clone.innerHTML.replace(/\u200B/g, '');
            if (html && !HTML_RE.test(html)) html = '<div>' + html + '</div>';
            ta.value = html;
            ta.dispatchEvent(new Event('input', { bubbles: true }));
            updateCount();
        };
        function updateCount() {
            const plain = area.textContent.replace(/\u200B/g, '').trim();
            const words = plain ? plain.split(/\s+/).length : 0;
            countEl.textContent = words + (words === 1 ? ' word' : ' words') + ' · ' + plain.length + ' characters';
        }
        const say = (t, isErr) => { msgEl.innerHTML = t ? `<span class="${isErr ? 'err' : ''}">${esc(t)}</span>` : ''; if (t) setTimeout(() => { if (msgEl.textContent === t) msgEl.textContent = ''; }, 5000); };

        // ── ribbon state ──
        const q = sel => wrap.querySelector(sel);
        const qa = sel => wrap.querySelectorAll(sel);
        function showTab(name) {
            qa('.rte-tab[data-tab]').forEach(t => t.classList.toggle('on', t.dataset.tab === name));
            qa('.rte-panel').forEach(p => p.classList.toggle('on', p.dataset.panel === name));
        }
        function currentSizePt() {
            const s = window.getSelection();
            const el = s.rangeCount ? elOf(s.anchorNode) : null;
            if (!el || !inArea(el)) return null;
            return Math.round(parseFloat(getComputedStyle(el).fontSize) * 0.75 * 2) / 2;
        }
        function refresh() {
            if (readonly) return;
            qa('[data-act]').forEach(btn => {
                const cmd = btn.dataset.act;
                if (!['bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', 'insertUnorderedList', 'insertOrderedList', 'justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'].includes(cmd)) return;
                let on = false; try { on = document.queryCommandState(cmd); } catch (e) {}
                btn.classList.toggle('on', on);
            });
            const sz = currentSizePt();
            if (sz) { const s = q('[data-sel="size"]'); if (document.activeElement !== s) s.value = String(sz); }
            try {
                const fn = (document.queryCommandValue('fontName') || '').replace(/["']/g, '');
                const f = q('[data-sel="fontName"]'); const hit = FONTS.find(x => x.toLowerCase() === fn.toLowerCase()); if (hit) f.value = hit;
                const bl = (document.queryCommandValue('formatBlock') || '').toUpperCase();
                q('[data-sel="block"]').value = /^H[1-4]$/.test(bl) ? bl : 'DIV';
            } catch (e) {}
            const tc = tctx(), td = tc;
            const tt = q('.rte-tab[data-tab="table"]'), pt = q('.rte-tab[data-tab="picture"]'), st = q('.rte-tab[data-tab="shape"]');
            const isImg = !!selObj && selObj.tagName === 'IMG', isShp = isShape(selObj);
            tt.hidden = !td; pt.hidden = !isImg; st.hidden = !isShp;
            const active = q('.rte-tab.on');
            if (active && active.hidden) showTab('home');
            if (tc) {
                const G = tgrid(tc.table), pp = G.pos.get(tc.td);
                if (pp) { q('[data-tbl="rowh"]').value = Math.round(tc.table.rows[pp.r].getBoundingClientRect().height); q('[data-tbl="colw"]').value = Math.round(tc.td.getBoundingClientRect().width / pp.cs); }
            }
            placeTblHandle();
            if (isImg) {
                q('[data-img="width"]').value = Math.round(selObj.getBoundingClientRect().width);
                q('[data-img="alt"]').value = selObj.getAttribute('alt') || '';
            }
            if (isImg) {
                const cs = getComputedStyle(selObj), on = cs.borderTopStyle !== 'none' && parseInt(cs.borderTopWidth, 10) > 0;
                q('[data-img="bweight"]').value = on ? String(Math.min(8, parseInt(cs.borderTopWidth, 10))) : '0';
                q('[data-img="bstyle"]').value = on ? cs.borderTopStyle : 'solid';
                const rad = selObj.style.borderRadius || '0px';
                q('[data-img="radius"]').value = ['0px', '8px', '18px', '50%'].includes(rad) ? rad : '0px';
            }
            if (isShp) {
                const r = selObj.getBoundingClientRect(), poly = !!selObj.style.clipPath;
                q('[data-shp="width"]').value = Math.round(r.width);
                q('[data-shp="height"]').value = Math.round(r.height);
                const wsel = q('[data-shp="weight"]');
                wsel.disabled = poly; wsel.value = poly ? '0' : String(parseInt(getComputedStyle(selObj).borderTopWidth, 10) || 0);
            }
            placeImgBox();
        }
        const after = () => { remember(); push(); refresh(); };

        function placeImgBox() {
            if (!selObj || !area.contains(selObj)) { imgbox.style.display = 'none'; return; }
            const r = selObj.getBoundingClientRect(), p = area.parentElement.getBoundingClientRect();
            Object.assign(imgbox.style, { display: 'block', left: (r.left - p.left - 2) + 'px', top: (r.top - p.top - 2) + 'px', width: r.width + 'px', height: r.height + 'px' });
        }

        // ── commands ──
        const exec = (cmd, val) => { restore(); document.execCommand('styleWithCSS', false, true); document.execCommand(cmd, false, val ?? null); after(); };
        const insertHtml = html => { restore(); document.execCommand('insertHTML', false, html); after(); };

        function setSize(pt) {
            restore();
            const cur = window.getSelection();
            if (cur.rangeCount && cur.getRangeAt(0).collapsed) {
                // Nothing selected: start our own span so the next characters really are this size
                // (the browser's pending style would give a keyword size such as xxx-large).
                document.execCommand('insertHTML', false, '<span data-rte-new="1" style="font-size:' + pt + 'pt">\u200B</span>');
                const sp = area.querySelector('[data-rte-new]');
                if (sp) {
                    sp.removeAttribute('data-rte-new');
                    const r = document.createRange(); r.setStart(sp.firstChild, 1); r.collapse(true);
                    const sl = window.getSelection(); sl.removeAllRanges(); sl.addRange(r);
                }
                after();
                return;
            }
            document.execCommand('styleWithCSS', false, false);
            document.execCommand('fontSize', false, '7');
            document.execCommand('styleWithCSS', false, true);
            const spans = [];
            area.querySelectorAll('font[size="7"]').forEach(f => {
                const s = document.createElement('span');
                s.style.fontSize = pt + 'pt';
                while (f.firstChild) s.appendChild(f.firstChild);
                s.querySelectorAll('[style*="font-size"]').forEach(c => { c.style.fontSize = ''; if (!c.getAttribute('style')) c.removeAttribute('style'); });
                if (!s.firstChild) s.textContent = '\u200B';
                f.replaceWith(s); spans.push(s);
            });
            if (spans.length) {
                const r = document.createRange(), sel = window.getSelection();
                if (spans.length === 1 && spans[0].textContent === '\u200B') { r.setStart(spans[0].firstChild, 1); r.collapse(true); }
                else { r.setStartBefore(spans[0]); r.setEndAfter(spans[spans.length - 1]); }
                sel.removeAllRanges(); sel.addRange(r);
            }
            after();
        }
        function blocksInSelection() {
            const s = window.getSelection(); if (!s.rangeCount) return [];
            const r = s.getRangeAt(0);
            const list = [...area.querySelectorAll(BLOCKS)].filter(el => r.intersectsNode(el) && !el.querySelector('p, div, li, h1, h2, h3, h4, table, blockquote'));
            return list;
        }
        function withBlocks(fn) {
            restore();
            let list = blocksInSelection();
            if (!list.length) { document.execCommand('formatBlock', false, 'div'); list = blocksInSelection(); }
            list.forEach(fn); after();
        }
        const HEADING = /^H[1-4]$/, BLOCKISH = 'p, div, ul, ol, table, h1, h2, h3, h4, blockquote';
        // A heading must never swallow other blocks (it would be invalid HTML): unwrap any that did.
        function fixHeadings() {
            area.querySelectorAll('h1, h2, h3, h4').forEach(h => {
                if (!h.querySelector(BLOCKISH)) return;
                while (h.firstChild) h.parentNode.insertBefore(h.firstChild, h);
                h.remove();
            });
        }
        function setBlock(tag) {
            restore();
            const li = closest('li'), sel = window.getSelection();
            if (li && sel.rangeCount && sel.getRangeAt(0).collapsed) {
                // a list item takes the heading inside it, so the list itself stays a list
                const head = [...li.children].find(c => HEADING.test(c.tagName));
                let target = null;
                if (tag === 'DIV') { if (head) { while (head.firstChild) li.insertBefore(head.firstChild, head); head.remove(); } }
                else if (head) { target = document.createElement(tag.toLowerCase()); while (head.firstChild) target.appendChild(head.firstChild); head.replaceWith(target); }
                else {
                    target = document.createElement(tag.toLowerCase());
                    [...li.childNodes].filter(c => !(c.nodeType === 1 && /^(UL|OL)$/.test(c.tagName))).forEach(c => target.appendChild(c));
                    li.insertBefore(target, li.firstChild);
                }
                if (target) { if (!target.firstChild) target.appendChild(document.createElement('br')); const r = document.createRange(); r.selectNodeContents(target); r.collapse(false); sel.removeAllRanges(); sel.addRange(r); }
            } else {
                document.execCommand('formatBlock', false, tag);
                fixHeadings();
            }
            after();
        }
        function indent(dir) {
            restore();
            const list = blocksInSelection();
            if (list.length && list.every(el => el.tagName === 'LI')) { document.execCommand(dir > 0 ? 'indent' : 'outdent'); after(); return; }
            withBlocks(el => { const cur = parseInt(el.style.marginLeft || '0', 10) || 0; const v = Math.max(0, Math.min(300, cur + dir * 30)); el.style.marginLeft = v ? v + 'px' : ''; });
        }
        function clearFormatting() {
            restore();
            document.execCommand('removeFormat');
            const sel = window.getSelection();
            if (sel.rangeCount) {
                const r = sel.getRangeAt(0);
                area.querySelectorAll('span[style], font').forEach(el => { if (r.intersectsNode(el)) { while (el.firstChild) el.parentNode.insertBefore(el.firstChild, el); el.remove(); } });
            }
            withBlocks(el => { ['marginLeft', 'lineHeight', 'textAlign'].forEach(k => el.style[k] = ''); if (!el.getAttribute('style')) el.removeAttribute('style'); });
        }

        // ── tables: a small grid engine so merged cells, splits and sizing behave like Word ──
        function tableHtml(rows, cols) {
            const cell = '<td style="border:1px solid #999999;padding:4px 8px;box-sizing:border-box;"><br></td>';
            return '<table style="border-collapse:collapse;width:100%"><tbody>' + ('<tr>' + cell.repeat(cols) + '</tr>').repeat(rows) + '</tbody></table><div><br></div>';
        }
        function tgrid(table) {
            const g = [], pos = new Map(), total = table.rows.length;
            [...table.rows].forEach((tr, r) => {
                g[r] = g[r] || [];
                let c = 0;
                [...tr.cells].forEach(td => {
                    while (g[r][c]) c++;
                    const rs = Math.max(1, Math.min(td.rowSpan || 1, total - r)), cs = Math.max(1, td.colSpan || 1);
                    pos.set(td, { r, c, rs, cs });
                    for (let i = 0; i < rs; i++) { g[r + i] = g[r + i] || []; for (let j = 0; j < cs; j++) g[r + i][c + j] = td; }
                    c += cs;
                });
            });
            return { g, pos, rows: total, cols: Math.max(0, ...g.map(x => x.length)) };
        }
        let cellSel = [], selRect = null, anchorCell = null;
        const clearCellSel = () => { cellSel.forEach(c => c.removeAttribute('data-rsel')); cellSel = []; selRect = null; };
        function selectCells(a, b) {
            clearCellSel();
            const table = a.closest('table');
            if (!table || b.closest('table') !== table) return;
            const G = tgrid(table);
            let r1 = Infinity, c1 = Infinity, r2 = -1, c2 = -1;
            [a, b].forEach(x => { const p = G.pos.get(x); r1 = Math.min(r1, p.r); c1 = Math.min(c1, p.c); r2 = Math.max(r2, p.r + p.rs - 1); c2 = Math.max(c2, p.c + p.cs - 1); });
            for (let grew = true; grew;) {
                grew = false;
                for (let r = r1; r <= r2; r++) for (let c = c1; c <= c2; c++) {
                    const p = G.g[r] && G.g[r][c] && G.pos.get(G.g[r][c]); if (!p) continue;
                    if (p.r < r1) { r1 = p.r; grew = true; } if (p.c < c1) { c1 = p.c; grew = true; }
                    if (p.r + p.rs - 1 > r2) { r2 = p.r + p.rs - 1; grew = true; } if (p.c + p.cs - 1 > c2) { c2 = p.c + p.cs - 1; grew = true; }
                }
            }
            const set = new Set();
            for (let r = r1; r <= r2; r++) for (let c = c1; c <= c2; c++) if (G.g[r] && G.g[r][c]) set.add(G.g[r][c]);
            cellSel = [...set]; cellSel.forEach(x => x.setAttribute('data-rsel', '1'));
            selRect = { r1, c1, r2, c2, G, table };
        }
        // The table the author is working in. Clicking a size box or menu moves the browser's cursor out of the page,
        // so fall back to the last cell they were in.
        let lastCell = null;
        function tctx() {
            const sel = window.getSelection(), inside = sel.rangeCount && inArea(sel.anchorNode);
            const td = closest('td, th') || cellSel[0] || (!inside ? lastCell : null);
            if (td && area.contains(td)) lastCell = td; else if (inside) lastCell = null;
            return td && area.contains(td) ? { td, tr: td.parentElement, table: td.closest('table') } : null;
        }
        const targets = () => cellSel.length ? cellSel : (tctx() ? [tctx().td] : []);
        const blankLike = td => { const c = td.cloneNode(false); c.removeAttribute('colspan'); c.removeAttribute('rowspan'); c.removeAttribute('data-rsel'); c.innerHTML = '<br>'; return c; };
        const cellAt = (tr, idx) => tr && (tr.cells[Math.min(idx, tr.cells.length - 1)] || tr.cells[0]);

        function insertRow(c, above) {
            const G = tgrid(c.table), p = G.pos.get(c.td), at = above ? p.r : p.r + p.rs;
            const tr = document.createElement('tr'), done = new Set();
            for (let col = 0; col < G.cols; col++) {
                const up = at > 0 ? G.g[at - 1][col] : null, down = at < G.rows ? G.g[at][col] : null;
                if (up && up === down) { if (!done.has(up)) { done.add(up); up.rowSpan = (up.rowSpan || 1) + 1; } continue; }
                tr.appendChild(blankLike(down || up || c.td));
            }
            const rows = [...c.table.rows];
            if (rows[at]) rows[at].parentNode.insertBefore(tr, rows[at]); else rows[rows.length - 1].parentNode.appendChild(tr);
            return cellAt(tr, c.td.cellIndex);
        }
        function insertCol(c, left) {
            const G = tgrid(c.table), p = G.pos.get(c.td), b = left ? p.c : p.c + p.cs, done = new Set();
            let focus = null;
            for (let r = 0; r < G.rows; r++) {
                const l = b > 0 ? G.g[r][b - 1] : null, rt = b < G.cols ? G.g[r][b] : null;
                if (l && l === rt) { if (!done.has(l)) { done.add(l); l.colSpan = (l.colSpan || 1) + 1; } continue; }
                const tr = c.table.rows[r], nc = blankLike(rt || l || c.td);
                tr.insertBefore(nc, [...tr.cells].find(x => G.pos.get(x).c >= b) || null);
                if (tr === c.tr) focus = nc;
            }
            return focus || c.td;
        }
        function deleteRow(c) {
            const G = tgrid(c.table), p = G.pos.get(c.td), r = p.r, tr = c.table.rows[r], next = c.table.rows[r + 1], seen = new Set();
            const idx = c.td.cellIndex;
            for (let col = 0; col < G.cols; col++) {
                const x = G.g[r][col]; if (!x || seen.has(x)) continue; seen.add(x);
                const q = G.pos.get(x);
                if (q.rs > 1) {
                    x.rowSpan = q.rs - 1;
                    if (q.r === r && next) next.insertBefore(x, [...next.cells].find(y => G.pos.get(y).c > q.c) || null);
                }
            }
            const target = next || c.table.rows[r - 1];
            tr.remove();
            if (!c.table.rows.length) { c.table.remove(); return null; }
            return cellAt(target, idx);
        }
        function deleteCol(c) {
            const G = tgrid(c.table), p = G.pos.get(c.td), seen = new Set(), idx = c.td.cellIndex, rowIdx = p.r;
            for (let r = 0; r < G.rows; r++) {
                const x = G.g[r][p.c]; if (!x || seen.has(x)) continue; seen.add(x);
                const q = G.pos.get(x);
                if (q.cs > 1) x.colSpan = q.cs - 1; else x.remove();
            }
            [...c.table.rows].forEach(tr => { if (!tr.cells.length && ![...c.table.rows].some(o => o !== tr && o.cells.length === 0 && false)) tr.remove(); });
            if (!c.table.rows.length || ![...c.table.rows].some(tr => tr.cells.length)) { c.table.remove(); return null; }
            return cellAt(c.table.rows[Math.min(rowIdx, c.table.rows.length - 1)], idx);
        }
        function mergeCells() {
            if (cellSel.length < 2 || !selRect) { say('Select two or more cells first: drag across them, or Shift+click.', true); return null; }
            const { r1, c1, r2, c2, G } = selRect, tl = G.g[r1][c1];
            const extra = cellSel.filter(x => x !== tl && (x.textContent.replace(/\u200B/g, '').trim() || x.querySelector('img'))).map(x => x.innerHTML);
            if (extra.length) tl.innerHTML = (tl.textContent.trim() || tl.querySelector('img') ? tl.innerHTML + '<br>' : '') + extra.join('<br>');
            cellSel.forEach(x => { if (x !== tl) x.remove(); });
            tl.colSpan = c2 - c1 + 1; tl.rowSpan = r2 - r1 + 1;
            if (tl.colSpan === 1) tl.removeAttribute('colspan');
            if (tl.rowSpan === 1) tl.removeAttribute('rowspan');
            tl.removeAttribute('data-rsel');
            clearCellSel();
            return tl;
        }
        function splitCell(c, nCols, nRows) {
            const td = c.td, table = c.table;
            let G = tgrid(table), p = G.pos.get(td);
            const pieces = [td]; // the rows we cut the cell into; each is then cut into columns
            if (nRows > 1) {
                if (p.rs > 1) {
                    const k = Math.min(nRows, p.rs), base = Math.floor(p.rs / k), extra = p.rs % k, first = base + (extra > 0 ? 1 : 0);
                    td.rowSpan = first; if (first === 1) td.removeAttribute('rowspan');
                    let row = p.r + first;
                    for (let i = 1; i < k; i++) {
                        const span = base + (i < extra ? 1 : 0), tr = table.rows[row], nc = blankLike(td);
                        if (p.cs > 1) nc.colSpan = p.cs;
                        if (span > 1) nc.rowSpan = span;
                        tr.insertBefore(nc, [...tr.cells].find(y => G.pos.has(y) && G.pos.get(y).c > p.c) || null);
                        pieces.push(nc); row += span;
                    }
                } else {
                    const seen = new Set();
                    for (let col = 0; col < G.cols; col++) { const x = G.g[p.r][col]; if (!x || seen.has(x)) continue; seen.add(x); if (x !== td) x.rowSpan = (x.rowSpan || 1) + nRows - 1; }
                    let after = table.rows[p.r];
                    for (let i = 1; i < nRows; i++) {
                        const tr = document.createElement('tr'), nc = blankLike(td);
                        if (p.cs > 1) nc.colSpan = p.cs;
                        tr.appendChild(nc); after.after(tr); after = tr; pieces.push(nc);
                    }
                }
                G = tgrid(table);
            }
            if (nCols > 1) {
                const p0 = G.pos.get(pieces[0]);
                if (p0.cs > 1) {
                    pieces.forEach(pc => {
                        const q = G.pos.get(pc), k = Math.min(nCols, q.cs), base = Math.floor(q.cs / k), extra = q.cs % k, first = base + (extra > 0 ? 1 : 0);
                        pc.colSpan = first; if (first === 1) pc.removeAttribute('colspan');
                        let after = pc;
                        for (let i = 1; i < k; i++) { const nc = blankLike(pc), span = base + (i < extra ? 1 : 0); if (span > 1) nc.colSpan = span; if (q.rs > 1) nc.rowSpan = q.rs; after.after(nc); after = nc; }
                    });
                } else {
                    const set = new Set(pieces), seen = new Set();
                    for (let r = 0; r < G.rows; r++) {
                        const x = G.g[r][p0.c]; if (!x || seen.has(x)) continue; seen.add(x);
                        if (set.has(x)) { const q = G.pos.get(x); let after = x; for (let i = 1; i < nCols; i++) { const nc = blankLike(x); if (q.rs > 1) nc.rowSpan = q.rs; after.after(nc); after = nc; } }
                        else x.colSpan = (x.colSpan || 1) + nCols - 1;
                    }
                }
            }
            return td;
        }
        const heightOf = tr => tr.getBoundingClientRect().height;
        function colWidths(G) {
            const w = [];
            for (let c = 0; c < G.cols; c++) {
                w[c] = null;
                for (let r = 0; r < G.rows && w[c] === null; r++) { const x = G.g[r][c], q = x && G.pos.get(x); if (q && q.cs === 1) w[c] = x.getBoundingClientRect().width; }
            }
            for (let c = 0; c < G.cols; c++) if (w[c] === null) { const x = G.g.map(r => r[c]).find(Boolean), q = G.pos.get(x); w[c] = x.getBoundingClientRect().width / q.cs; }
            return w;
        }
        function applyWidths(table, G, w) {
            table.style.tableLayout = 'fixed';
            table.style.width = Math.round(w.reduce((a, b) => a + b, 0)) + 'px';
            G.pos.forEach((q, td) => { let sum = 0; for (let j = 0; j < q.cs; j++) sum += w[q.c + j]; td.style.width = Math.round(sum) + 'px'; td.style.boxSizing = 'border-box'; });
        }
        const setRowHeight = (tr, h) => [...tr.cells].forEach(td => { td.style.height = Math.round(Math.max(20, h)) + 'px'; });
        function tableAction(act, extra, ctx) {
            const c = ctx || tctx(); if (!c) return;
            let focus = c.td;
            if (act === 'row-above' || act === 'row-below') focus = insertRow(c, act === 'row-above');
            else if (act === 'col-left' || act === 'col-right') focus = insertCol(c, act === 'col-left');
            else if (act === 'row-del') focus = deleteRow(c);
            else if (act === 'col-del') focus = deleteCol(c);
            else if (act === 'tbl-merge') { const m = mergeCells(); if (!m) return; focus = m; }
            else if (act === 'tbl-split') focus = splitCell(c, extra.cols, extra.rows);
            else if (act === 'tbl-del') {
                const next = c.table.nextElementSibling; c.table.remove(); focus = null; area.focus();
                if (next) { const r = document.createRange(); r.selectNodeContents(next); r.collapse(true); const sl = window.getSelection(); sl.removeAllRanges(); sl.addRange(r); remember(); }
            } else if (act === 'tbl-header') {
                const first = c.table.rows[0], on = first.cells[0].style.fontWeight !== '700';
                [...first.cells].forEach(td => { td.style.fontWeight = on ? '700' : ''; td.style.backgroundColor = on ? '#f3f4f6' : ''; });
            } else if (act === 'tbl-dist-cols') { const G = tgrid(c.table), w = colWidths(G), t = w.reduce((a, b) => a + b, 0); applyWidths(c.table, G, w.map(() => t / w.length)); }
            else if (act === 'tbl-dist-rows') { const rows = [...c.table.rows], t = rows.reduce((a, r) => a + heightOf(r), 0); rows.forEach(r => setRowHeight(r, t / rows.length)); }
            else if (act === 'tbl-fit-win') { c.table.style.width = '100%'; c.table.style.tableLayout = ''; c.table.querySelectorAll('td, th').forEach(x => x.style.removeProperty('width')); }
            else if (act === 'tbl-fit-content') { c.table.style.width = 'auto'; c.table.style.tableLayout = ''; c.table.querySelectorAll('td, th').forEach(x => x.style.removeProperty('width')); }
            else if (act === 'valign') targets().forEach(x => { x.style.verticalAlign = extra.v; });
            if (/^(row|col|tbl)-(above|below|left|right|del|merge|split)$/.test(act) || act === 'tbl-del') clearCellSel();
            if (focus && area.contains(focus)) placeCaret(focus);
            after();
        }
        function applyBorders(table, mode, w, color) {
            const G = tgrid(table), line = w + 'px solid ' + color;
            G.pos.forEach((q, td) => {
                const top = q.r === 0, left = q.c === 0, bottom = q.r + q.rs === G.rows, right = q.c + q.cs === G.cols;
                const on = mode === 'all' ? [1, 1, 1, 1] : mode === 'outside' ? [top, right, bottom, left] : mode === 'inside' ? [!top, !right, !bottom, !left] : [0, 0, 0, 0];
                setBorders(td, { top: on[0] ? line : null, right: on[1] ? line : null, bottom: on[2] ? line : null, left: on[3] ? line : null });
            });
            after();
        }
        function borderMenu(anchor) {
            const c = tctx(); if (!c) return;
            const st = { w: 1, color: '#999999' };
            const item = (attr, val, icon, label) => `<button type="button" class="rte-item" ${attr}="${val}"><i class="${icon}"></i>${label}</button>`;
            const pop = showPop(anchor,
                '<div class="rte-linep">' + item('data-bm', 'all', 'fas fa-border-all', 'All borders') + item('data-bm', 'outside', 'fas fa-border-outer', 'Outside borders') + item('data-bm', 'inside', 'fas fa-border-inner', 'Inside borders') + item('data-bm', 'none', 'fas fa-border-none', 'No borders') +
                '<div class="rte-plabel">Weight</div><div class="rte-wrow">' + [1, 2, 3, 4].map(w => `<button type="button" class="rte-btn${w === 1 ? ' on' : ''}" data-bw="${w}">${w}px</button>`).join('') + '</div>' +
                '<div class="rte-plabel">Color</div><div class="rte-pal">' + PALETTE.slice(0, 20).map(x => `<button type="button" data-bc="${x}" style="background:${x}" title="${x}"></button>`).join('') + '</div></div>');
            pop.addEventListener('click', ev => {
                const t = ev.target.closest('[data-bm],[data-bw],[data-bc]'); if (!t) return;
                if (t.dataset.bw) { st.w = +t.dataset.bw; pop.querySelectorAll('[data-bw]').forEach(x => x.classList.toggle('on', x === t)); }
                else if (t.dataset.bc) st.color = t.dataset.bc;
                else { closePop(); applyBorders(c.table, t.dataset.bm, st.w, st.color); }
            });
        }
        function splitMenu(anchor) {
            const c = tctx(); if (!c) return;
            const pop = showPop(anchor, '<div class="rte-linep"><div class="rte-plabel">Split cell into</div><div class="rte-splitrow"><label>Columns <input type="number" min="1" max="10" value="2" data-sc></label><label>Rows <input type="number" min="1" max="10" value="1" data-sr></label></div><button type="button" class="rte-item rte-ins" data-split="1"><i class="fas fa-table-cells-large"></i> Split</button></div>');
            pop.addEventListener('click', ev => {
                if (!ev.target.closest('[data-split]')) return;
                const cols = Math.max(1, Math.min(10, +pop.querySelector('[data-sc]').value || 1)), rows = Math.max(1, Math.min(10, +pop.querySelector('[data-sr]').value || 1));
                closePop();
                if (cols * rows > 1) tableAction('tbl-split', { cols, rows }, c);
            });
        }
        function tabInTable(e) {
            const c = tctx(); if (!c || e.key !== 'Tab') return false;
            e.preventDefault();
            let cells = [...c.table.querySelectorAll('td, th')];
            const at = cells.indexOf(c.td);
            if (!e.shiftKey && at === cells.length - 1) { tableAction('row-below'); cells = [...c.table.querySelectorAll('td, th')]; }
            const next = cells[e.shiftKey ? at - 1 : at + 1];
            if (next) placeCaret(next);
            return true;
        }
        function placeCaret(el) { const r = document.createRange(); r.selectNodeContents(el); r.collapse(true); const s = window.getSelection(); s.removeAllRanges(); s.addRange(r); remember(); }

        // drag a cell border to resize a column or row; drag the corner handle to resize the whole table
        function edgeAt(e) {
            const td = e.target.closest ? e.target.closest('td, th') : null;
            if (!td || !area.contains(td)) return null;
            const table = td.closest('table'), G = tgrid(table), p = G.pos.get(td), r = td.getBoundingClientRect(), T = 5;
            if (Math.abs(e.clientX - r.right) <= T) return { kind: 'col', table, idx: p.c + p.cs - 1 };
            if (Math.abs(e.clientX - r.left) <= T && p.c > 0) return { kind: 'col', table, idx: p.c - 1 };
            if (Math.abs(e.clientY - r.bottom) <= T) return { kind: 'row', table, idx: p.r + p.rs - 1 };
            if (Math.abs(e.clientY - r.top) <= T && p.r > 0) return { kind: 'row', table, idx: p.r - 1 };
            return null;
        }
        function drag(onMove) {
            const move = ev => onMove(ev), up = () => { document.removeEventListener('mousemove', move); document.removeEventListener('mouseup', up); area.classList.remove('rte-dragging'); after(); };
            area.classList.add('rte-dragging');
            document.addEventListener('mousemove', move);
            document.addEventListener('mouseup', up);
        }
        function startEdgeDrag(ed, e) {
            const G = tgrid(ed.table), x0 = e.clientX, y0 = e.clientY;
            if (ed.kind === 'col') {
                const w0 = colWidths(G); applyWidths(ed.table, G, w0);
                drag(ev => {
                    const dx = ev.clientX - x0, w = w0.slice(), i = ed.idx;
                    if (i < G.cols - 1) { const d = Math.max(30 - w0[i], Math.min(w0[i + 1] - 30, dx)); w[i] = w0[i] + d; w[i + 1] = w0[i + 1] - d; }
                    else w[i] = Math.max(30, w0[i] + dx);
                    applyWidths(ed.table, G, w); placeTblHandle();
                });
            } else {
                const tr = ed.table.rows[ed.idx], h0 = heightOf(tr);
                drag(ev => { setRowHeight(tr, h0 + ev.clientY - y0); placeTblHandle(); });
            }
        }
        function placeTblHandle() {
            const c = tctx();
            if (!c || !area.contains(c.table)) { thandle.hidden = true; return; }
            const r = c.table.getBoundingClientRect(), p = area.parentElement.getBoundingClientRect();
            thandle.hidden = false;
            thandle.style.left = (r.right - p.left - 5) + 'px'; thandle.style.top = (r.bottom - p.top - 5) + 'px';
        }
        thandle.addEventListener('mousedown', e => {
            const c = tctx(); if (!c) return;
            e.preventDefault();
            const G = tgrid(c.table), w0 = colWidths(G), t0 = w0.reduce((a, b) => a + b, 0), rows = [...c.table.rows], h0 = rows.map(heightOf), x0 = e.clientX, y0 = e.clientY;
            applyWidths(c.table, G, w0);
            drag(ev => {
                const fx = Math.max(0.2, (t0 + ev.clientX - x0) / t0), fy = Math.max(0.3, (h0.reduce((a, b) => a + b, 0) + ev.clientY - y0) / h0.reduce((a, b) => a + b, 0));
                applyWidths(c.table, G, w0.map(w => Math.max(30, w * fx)));
                if (Math.abs(ev.clientY - y0) > 3) rows.forEach((tr, i) => setRowHeight(tr, h0[i] * fy));
                placeTblHandle();
            });
        });
        // cell selection: drag across cells (or Shift+click) to select a block of them
        area.addEventListener('mousemove', e => { if (e.buttons) return; const ed = edgeAt(e); area.style.cursor = ed ? (ed.kind === 'col' ? 'col-resize' : 'row-resize') : ''; });
        area.addEventListener('mousedown', e => {
            if (e.button !== 0) return;
            const ed = edgeAt(e);
            if (ed) { e.preventDefault(); clearCellSel(); startEdgeDrag(ed, e); return; }
            const td = e.target.closest ? e.target.closest('td, th') : null;
            if (e.shiftKey && td && anchorCell && anchorCell.closest('table') === td.closest('table')) { e.preventDefault(); window.getSelection().removeAllRanges(); selectCells(anchorCell, td); refresh(); return; }
            clearCellSel();
            if (!td || !area.contains(td)) { anchorCell = null; return; }
            anchorCell = td;
            const mv = ev => {
                const t2 = ev.target.closest ? ev.target.closest('td, th') : null;
                if (t2 && t2 !== anchorCell && t2.closest('table') === anchorCell.closest('table')) { window.getSelection().removeAllRanges(); selectCells(anchorCell, t2); area.classList.add('rte-selecting'); }
            };
            const up = () => { document.removeEventListener('mousemove', mv); document.removeEventListener('mouseup', up); area.classList.remove('rte-selecting'); refresh(); };
            document.addEventListener('mousemove', mv);
            document.addEventListener('mouseup', up);
        });

        // pictures
        async function upload(file) {
            if (!uploadUrl) { say('Picture upload is not available here.', true); return null; }
            if (!OK_TYPES.includes(file.type)) { say('Only JPG, PNG or WebP pictures can be inserted.', true); return null; }
            if (file.size > MAX_BYTES) { say('That picture is larger than 3 MB.', true); return null; }
            const fd = new FormData();
            fd.append('image', file);
            const token = (document.querySelector('meta[name="csrf-token"]') || {}).content || (ta.form && ta.form.querySelector('input[name="_token"]') || {}).value || '';
            fd.append('_token', token);
            say('Uploading picture…');
            try {
                const res = await fetch(uploadUrl, { method: 'POST', body: fd, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.url) { say((data.errors && data.errors.image && data.errors.image[0]) || data.message || 'The picture could not be uploaded.', true); return null; }
                say('');
                return data.url;
            } catch (e) { say('The picture could not be uploaded.', true); return null; }
        }
        const checkFile = f => {
            if (!OK_TYPES.includes(f.type)) { say('Only JPG, PNG or WebP pictures can be inserted.', true); return false; }
            if (f.size > MAX_BYTES) { say('That picture is larger than 3 MB.', true); return false; }
            return true;
        };
        // The picture is placed at the cursor right away (from the local file) and swapped for the stored copy once it is uploaded.
        async function addPictures(files, range) {
            for (const f of files) {
                if (!checkFile(f)) continue;
                const blob = URL.createObjectURL(f);
                let img;
                if (replacing && selObj && selObj.tagName === 'IMG') { img = selObj; replacing = false; img.src = blob; img.setAttribute('data-pending', '1'); }
                else {
                    if (range) saved = range;
                    insertHtml(`<img src="${blob}" alt="" data-pending="1" style="max-width:100%">`);
                    img = [...area.querySelectorAll('img[data-pending]')].find(i => i.src === blob);
                }
                if (img) selectObject(img);
                const url = await upload(f);
                if (img && area.contains(img)) {
                    if (url) { img.src = url; img.removeAttribute('data-pending'); } else img.remove();
                    if (!url) selObj = null;
                    after();
                }
                URL.revokeObjectURL(blob);
            }
        }
        fileInput.addEventListener('change', () => { const f = [...fileInput.files]; fileInput.value = ''; if (f.length) addPictures(f); });

        function selectObject(img) {
            selObj = img;
            if (img) { const r = document.createRange(); r.selectNode(img); const s = window.getSelection(); s.removeAllRanges(); s.addRange(r); remember(); }
            refresh();
        }

        // ── events: ribbon ──
        if (!readonly) {
            wrap.querySelector('.rte-tabs').addEventListener('click', e => {
                const t = e.target.closest('[data-tab]');
                if (t) { wrap.classList.remove('collapsed'); showTab(t.dataset.tab); return; }
                if (e.target.closest('[data-act="collapse"]')) wrap.classList.toggle('collapsed');
            });
            const bar = wrap.querySelector('.rte-ribbon');
            bar.addEventListener('mousedown', e => { if (!e.target.closest('select, input')) e.preventDefault(); else remember(); });
            bar.addEventListener('click', e => {
                const el = e.target.closest('[data-act]'); if (!el) return;
                // commit a size typed into a ribbon box first, so its own 'change' cannot land after (and undo) this action
                if (document.activeElement && document.activeElement.closest && document.activeElement.closest('.rte-ribbon') && document.activeElement.matches('input')) document.activeElement.blur();
                const act = el.dataset.act, kind = el.dataset.kind;
                const simple = ['bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', 'insertUnorderedList', 'insertOrderedList', 'justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull', 'undo', 'redo'];
                if (simple.includes(act)) {
                    if (selObj && act.startsWith('justify')) { restore(); document.execCommand(act); after(); return; }
                    return exec(act);
                }
                if (act === 'color') return kind === 'shade' ? shadeCell(colors.shade) : (kind === 'fill' || kind === 'outline') ? shapeColor(kind, colors[kind]) : kind === 'imgborder' ? imgBorder({ color: colors.imgborder }) : exec(kind, colors[kind]);
                if (act === 'color-menu') {
                    const pop = showPop(el, `<div class="rte-pal">${PALETTE.map(c => `<button type="button" data-c="${c}" style="background:${c}" title="${c}"></button>`).join('')}</div><button type="button" class="rte-item" data-none="1" style="margin-top:6px"><i class="fas fa-ban"></i> No color</button><label class="rte-item"><i class="fas fa-eye-dropper"></i> More colors… <input type="color" style="width:26px;height:20px;border:0;padding:0;margin-left:auto"></label>`);
                    const apply = c => {
                        closePop();
                        if (kind === 'shade') { colors.shade = c || ''; shadeCell(c || ''); q('[data-sw="shade"]').style.background = c || '#fff'; return; }
                        if (kind === 'imgborder') { if (c) { colors.imgborder = c; q('[data-sw="imgborder"]').style.background = c; imgBorder({ color: c }); } else imgBorder({ weight: 0 }); return; }
                        if (kind === 'fill' || kind === 'outline') { colors[kind] = c || colors[kind]; if (c) q(`[data-sw="${kind}"]`).style.background = c; shapeColor(kind, c); return; }
                        if (c) { colors[kind] = c; q(`[data-sw="${kind}"]`).style.background = c; exec(kind, c); }
                        else if (kind === 'hiliteColor') exec('hiliteColor', 'transparent'); else exec('foreColor', '#222222');
                    };
                    pop.addEventListener('click', ev => { const c = ev.target.closest('[data-c]'); if (c) apply(c.dataset.c); else if (ev.target.closest('[data-none]')) apply(''); });
                    pop.querySelector('input[type=color]').addEventListener('input', ev => apply(ev.target.value));
                    return;
                }
                if (act === 'grow' || act === 'shrink') { const cur = currentSizePt() || 10; if (act === 'grow' && cur >= SIZES[SIZES.length - 1]) return setSize(Math.min(200, Math.round(cur + 4))); if (act === 'shrink' && cur <= SIZES[0]) return setSize(Math.max(1, Math.round(cur - 1))); const i = SIZES.reduce((bi, s, k) => Math.abs(s - cur) < Math.abs(SIZES[bi] - cur) ? k : bi, 0); const n = SIZES[Math.max(0, Math.min(SIZES.length - 1, i + (act === 'grow' ? 1 : -1)))]; return setSize(n); }
                if (act === 'clear') return clearFormatting();
                if (act === 'indent') return indent(1);
                if (act === 'outdent') return indent(-1);
                if (act === 'spacing') {
                    const pop = showPop(el, [1, 1.15, 1.5, 2, 2.5].map(v => `<button type="button" class="rte-item" data-ls="${v}">${v.toFixed(v % 1 ? 2 : 1).replace(/0$/, '')}</button>`).join(''));
                    pop.addEventListener('click', ev => { const l = ev.target.closest('[data-ls]'); if (l) { closePop(); withBlocks(x => { x.style.lineHeight = l.dataset.ls === '1' ? '' : l.dataset.ls; if (!x.getAttribute('style')) x.removeAttribute('style'); }); } });
                    return;
                }
                if (act === 'table-menu') {
                    const pop = showPop(el, `<div class="rte-tgrid">${Array.from({ length: 64 }, (_, i) => `<span data-r="${Math.floor(i / 8) + 1}" data-c="${i % 8 + 1}"></span>`).join('')}</div><div class="rte-tlabel">Insert table</div>`);
                    const cells = [...pop.querySelectorAll('.rte-tgrid span')], label = pop.querySelector('.rte-tlabel');
                    pop.addEventListener('mouseover', ev => { const t = ev.target.closest('span[data-r]'); if (!t) return; const r = +t.dataset.r, c = +t.dataset.c; cells.forEach(s => s.classList.toggle('hot', +s.dataset.r <= r && +s.dataset.c <= c)); label.textContent = c + ' × ' + r + ' Table'; });
                    pop.addEventListener('click', ev => {
                        const t = ev.target.closest('span[data-r]'); if (!t) return;
                        closePop();
                        const known = new Set(area.querySelectorAll('table'));
                        insertHtml(tableHtml(+t.dataset.r, +t.dataset.c));
                        const fresh = [...area.querySelectorAll('table')].find(x => !known.has(x));
                        if (fresh) { placeCaret(fresh.querySelector('td')); refresh(); }
                    });
                    return;
                }
                if (act === 'symbol-menu') {
                    const pop = showPop(el, `<div class="rte-sym">${SYMBOLS.map(s => `<button type="button" data-s="${s}">${s}</button>`).join('')}</div>`);
                    pop.addEventListener('click', ev => { const s = ev.target.closest('[data-s]'); if (s) { closePop(); restore(); document.execCommand('insertText', false, s.dataset.s); after(); } });
                    return;
                }
                if (act === 'hr') return exec('insertHorizontalRule');
                if (act === 'line-menu') return lineMenu(el);
                if (act === 'shape-menu') return shapeMenu(el);
                if (act === 'shape-del') { if (selObj) { selObj.remove(); selObj = null; after(); } return; }
                if (act === 'picture') { replacing = false; remember(); return fileInput.click(); }
                if (act === 'img-replace') { replacing = true; return fileInput.click(); }
                if (act === 'img-del') { if (selObj) { selObj.remove(); selObj = null; after(); } return; }
                if (act === 'img-size') { if (selObj) { const full = area.clientWidth - 36; selObj.style.width = Math.round(full * (+el.dataset.v) / 100) + 'px'; after(); } return; }
                if (act === 'tbl-split-menu') return splitMenu(el);
                if (act === 'border-menu') return borderMenu(el);
                if (act === 'valign') return tableAction('valign', { v: el.dataset.v });
                if (/^(row|col|tbl)-/.test(act)) return tableAction(act);
            });
            bar.addEventListener('change', ev => {
                const sel = ev.target.closest('[data-sel]');
                if (sel) {
                    if (sel.dataset.sel === 'fontName') exec('fontName', sel.value);
                    else if (sel.dataset.sel === 'size') {
                        const n = parseFloat(String(sel.value).replace(',', '.'));
                        if (!(n > 0)) { const cur = currentSizePt(); if (cur) sel.value = String(cur); return; }
                        const pt = Math.round(Math.max(1, Math.min(200, n)) * 2) / 2;
                        sel.value = String(pt);
                        setSize(pt);
                    }
                    else if (sel.dataset.sel === 'block') setBlock(sel.value);
                    return;
                }
                const tb = ev.target.closest('[data-tbl]');
                if (tb) {
                    const c = tctx(); if (!c) return;
                    const G = tgrid(c.table), pp = G.pos.get(c.td), v = +tb.value;
                    if (tb.dataset.tbl === 'rowh' && v) setRowHeight(c.table.rows[pp.r], v);
                    else if (tb.dataset.tbl === 'colw' && v) { const w = colWidths(G); for (let j = 0; j < pp.cs; j++) w[pp.c + j] = Math.max(30, v / pp.cs); applyWidths(c.table, G, w); }
                    after(); return;
                }
                const sp = ev.target.closest('[data-shp]');
                if (sp && isShape(selObj)) {
                    const k = sp.dataset.shp;
                    if (k === 'width' || k === 'height') selObj.style[k] = Math.max(10, Math.min(2000, +sp.value || 10)) + 'px';
                    else if (k === 'weight') { const w = +sp.value; selObj.style.border = w ? w + 'px solid ' + (selObj.style.borderColor || colors.outline) : 'none'; }
                    after(); return;
                }
                const im = ev.target.closest('[data-img]');
                if (im && selObj && ['bweight', 'bstyle', 'radius'].includes(im.dataset.img)) {
                    if (im.dataset.img === 'bweight') imgBorder({ weight: +im.value });
                    else if (im.dataset.img === 'bstyle') imgBorder({ style: im.value });
                    else { selObj.style.borderRadius = im.value === '0px' ? '' : im.value; after(); }
                    return;
                }
                if (im && selObj) { if (im.dataset.img === 'width') { selObj.style.width = Math.max(20, Math.min(2000, +im.value || 20)) + 'px'; } else selObj.setAttribute('alt', im.value); after(); }
            });
        }
        // Picture border: weight, type and colour; each change keeps the other two.
        function imgBorder(change) {
            if (!selObj || selObj.tagName !== 'IMG') return;
            const cs = getComputedStyle(selObj);
            let weight = change.weight !== undefined ? change.weight : (cs.borderTopStyle === 'none' ? 0 : parseInt(cs.borderTopWidth, 10) || 0);
            const style = change.style || (cs.borderTopStyle !== 'none' ? cs.borderTopStyle : 'solid');
            const color = change.color || (cs.borderTopStyle !== 'none' ? cs.borderTopColor : colors.imgborder);
            if (change.weight === undefined && weight === 0) weight = 2; // picking a type or colour switches the border on
            if (style === 'double' && weight && weight < 3) weight = 3;
            selObj.style.border = weight ? weight + 'px ' + style + ' ' + color : '';
            after();
        }
        function shapeColor(kind, c) {
            if (!isShape(selObj)) return;
            if (kind === 'fill') selObj.style.backgroundColor = c || 'transparent';
            else if (!selObj.style.clipPath) {
                const w = parseInt(getComputedStyle(selObj).borderTopWidth, 10) || 2;
                selObj.style.border = c ? w + 'px solid ' + c : 'none';
            }
            after();
        }
        function shapeMenu(anchor) {
            const pop = showPop(anchor, '<div class="rte-shapes">' + SHAPES.map((sh, i) => {
                const k = Math.min(30 / sh.w, 30 / sh.h);
                return `<button type="button" data-shape="${i}" title="${sh.n}"><span style="${shapeStyle(sh, Math.round(sh.w * k), Math.round(sh.h * k)).replace('margin:4px;', 'margin:0;')}"></span></button>`;
            }).join('') + '</div>');
            pop.addEventListener('click', ev => {
                const t = ev.target.closest('[data-shape]'); if (!t) return;
                closePop();
                const sh = SHAPES[+t.dataset.shape];
                const known = new Set(area.querySelectorAll('div[style*="inline-block"]'));
                insertHtml('<div style="' + shapeStyle(sh, sh.w, sh.h) + '"><br></div>');
                const fresh = [...area.querySelectorAll('div[style*="inline-block"]')].find(x => !known.has(x));
                if (fresh) selectObject(fresh);
                showTab('shape');
            });
        }
        function lineMenu(anchor) {
            const cur = { style: 'solid', weight: 2, color: '#333333' };
            const swatches = PALETTE.slice(0, 20).map(c => `<button type="button" data-lc="${c}" style="background:${c}" title="${c}"></button>`).join('');
            const row = (attr, val, sample, label) => `<button type="button" class="rte-item" ${attr}="${val}"><span class="rte-lp" style="border-top:${sample}"></span><span>${label}</span></button>`;
            const pop = showPop(anchor,
                '<div class="rte-linep"><div class="rte-plabel">Line type</div>' +
                ['solid', 'dashed', 'dotted', 'double'].map(st => row('data-lt', st, `3px ${st} #333`, st[0].toUpperCase() + st.slice(1))).join('') +
                '<div class="rte-plabel">Weight</div>' +
                [1, 2, 3, 4, 6, 8].map(w => row('data-lw', w, `${w}px solid #333`, w + ' px')).join('') +
                `<div class="rte-plabel">Color</div><div class="rte-pal">${swatches}</div>` +
                '<div class="rte-plabel">Preview</div><div class="rte-lprev"><div class="rte-lsample"></div></div>' +
                '<button type="button" class="rte-item rte-ins" data-lins="1"><i class="fas fa-plus"></i> Insert line</button></div>');
            const sample = pop.querySelector('.rte-lsample');
            const css = () => `${cur.style === 'double' ? Math.max(3, cur.weight) : cur.weight}px ${cur.style} ${cur.color}`;
            const redraw = () => {
                sample.style.borderTop = css();
                pop.querySelectorAll('[data-lt]').forEach(x => x.classList.toggle('on', x.dataset.lt === cur.style));
                pop.querySelectorAll('[data-lw]').forEach(x => x.classList.toggle('on', +x.dataset.lw === cur.weight));
            };
            redraw();
            pop.addEventListener('click', ev => {
                const t = ev.target.closest('[data-lt],[data-lw],[data-lc],[data-lins]'); if (!t) return;
                if (t.dataset.lt) cur.style = t.dataset.lt;
                else if (t.dataset.lw) cur.weight = +t.dataset.lw;
                else if (t.dataset.lc) cur.color = t.dataset.lc;
                else { closePop(); insertHtml('<hr style="border:0;border-top:' + css() + ';margin:.6em 0"><div><br></div>'); return; }
                redraw();
            });
        }
        function shadeCell(c) { const list = targets(); if (list.length) { list.forEach(x => { x.style.backgroundColor = c || ''; }); after(); } }

        // ── events: page ──
        area.addEventListener('input', e => {
            if (selObj && !area.contains(selObj)) selObj = null;
            if (e.inputType === 'insertParagraph') {
                // the paragraph after a heading is Normal text (like Word), not another heading
                const b = closest('h1, h2, h3, h4');
                if (b && !b.textContent.replace(/\u200B/g, '').trim() && b.previousElementSibling && HEADING.test(b.previousElementSibling.tagName)) document.execCommand('formatBlock', false, 'div');
            } else if (/^delete/.test(e.inputType || '') && !area.textContent.replace(/\u200B/g, '').trim() && !area.querySelector('img, table, hr, [style*="inline-block"]') && area.querySelector('h1, h2, h3, h4, ul, ol, blockquote')) {
                // everything was deleted: don't keep typing inside the last heading/list that was there
                area.innerHTML = '<div><br></div>';
                const r = document.createRange(); r.setStart(area.firstChild, 0); r.collapse(true);
                const sl = window.getSelection(); sl.removeAllRanges(); sl.addRange(r);
            }
            after();
        });
        area.addEventListener('keydown', e => {
            if (cellSel.length > 1 && (e.key === 'Delete' || e.key === 'Backspace')) { e.preventDefault(); cellSel.forEach(x => { x.innerHTML = '<br>'; }); after(); return; }
            if (cellSel.length && (e.key === 'Escape' || (e.key.length === 1 && !e.ctrlKey && !e.metaKey))) { clearCellSel(); refresh(); }
            if (tabInTable(e)) return;
            if (e.ctrlKey || e.metaKey) {
                const k = e.key.toLowerCase();
                const map = { l: 'justifyLeft', e: 'justifyCenter', r: 'justifyRight', j: 'justifyFull' };
                if (map[k]) { e.preventDefault(); exec(map[k]); }
            }
        });
        area.addEventListener('keyup', () => { remember(); refresh(); });
        area.addEventListener('scroll', () => { placeTblHandle(); });
        area.addEventListener('mouseup', () => { remember(); refresh(); });
        area.addEventListener('click', e => {
            const sh = e.target.closest ? e.target.closest('div[style*="inline-block"]') : null;
            if (e.target.tagName === 'IMG') selectObject(e.target);
            else if (sh && sh !== area && isShape(sh) && area.contains(sh)) selectObject(sh);
            else if (selObj) { selObj = null; refresh(); }
        });
        area.addEventListener('blur', remember);
        area.addEventListener('focus', () => { active = { remember, refresh, placeImgBox, area }; try { document.execCommand('defaultParagraphSeparator', false, 'div'); } catch (e) {} });

        area.addEventListener('paste', e => {
            const files = [...(e.clipboardData ? e.clipboardData.files : [])].filter(f => f.type.startsWith('image/'));
            e.preventDefault();
            if (files.length) { remember(); addPictures(files); return; }
            document.execCommand('insertText', false, e.clipboardData.getData('text/plain'));
        });
        const hasFiles = e => e.dataTransfer && [...e.dataTransfer.types].includes('Files');
        area.addEventListener('dragover', e => { if (hasFiles(e)) { e.preventDefault(); area.classList.add('drop'); } });
        area.addEventListener('dragleave', () => area.classList.remove('drop'));
        area.addEventListener('drop', e => {
            if (!hasFiles(e)) return;
            e.preventDefault(); area.classList.remove('drop');
            const files = [...e.dataTransfer.files].filter(f => f.type.startsWith('image/'));
            let range = null;
            if (document.caretRangeFromPoint) range = document.caretRangeFromPoint(e.clientX, e.clientY);
            if (files.length) addPictures(files, range);
        });

        // resize handles: any corner of a selected picture or shape. Pictures keep their proportions; shapes are free (Shift keeps them).
        imgbox.querySelectorAll('.rte-handle').forEach(h => h.addEventListener('pointerdown', e => {
            if (!selObj) return;
            e.preventDefault(); e.stopPropagation();
            const west = h.dataset.pos.includes('w'), north = h.dataset.pos.includes('n');
            const x0 = e.clientX, y0 = e.clientY, r0 = selObj.getBoundingClientRect(), w0 = r0.width, h0 = r0.height, shp = isShape(selObj), ratio = w0 / h0;
            h.setPointerCapture && h.setPointerCapture(e.pointerId);
            const move = ev => {
                const dx = (west ? -1 : 1) * (ev.clientX - x0), dy = (north ? -1 : 1) * (ev.clientY - y0);
                const w = Math.max(24, Math.min(area.clientWidth - 40, Math.round(w0 + dx)));
                selObj.style.width = w + 'px';
                if (shp) selObj.style.height = Math.max(24, Math.min(1200, ev.shiftKey ? Math.round(w / ratio) : Math.round(h0 + dy))) + 'px';
                else { selObj.style.height = 'auto'; selObj.removeAttribute('height'); }
                const r = selObj.getBoundingClientRect();
                imgbox.dataset.tip = Math.round(r.width) + ' × ' + Math.round(r.height);
                placeImgBox();
            };
            const up = () => { document.removeEventListener('pointermove', move); document.removeEventListener('pointerup', up); delete imgbox.dataset.tip; after(); };
            document.addEventListener('pointermove', move);
            document.addEventListener('pointerup', up);
        }));

        if (ta.form) ta.form.addEventListener('submit', e => {
            if (area.querySelector('img[data-pending]')) { e.preventDefault(); e.stopImmediatePropagation(); say('A picture is still uploading, please wait a moment and save again.', true); }
        }, true);
        updateCount();
        ta._rte = { area, sync() { area.innerHTML = toEditorHtml(ta.value); selObj = null; updateCount(); } };
    }

    function sync(ta) { if (ta && ta._rte) ta._rte.sync(); }

    return { init, sync, build, html: toEditorHtml, toPlain: v => { const d = document.createElement('div'); d.innerHTML = toEditorHtml(v); return (d.textContent || '').replace(/\u200B/g, '').trim(); } };
})();
</script>
