{{-- "About" button that opens the subject's full description in a popup. Works inside clickable cards.
     Pass either $subject (anything with code / name / description) or $code, $name, $description.
     Optional: $variant = 'corner' (white, top-right of a coloured banner) | 'inline' (default, tinted). --}}
@php
    $siCode = $code ?? ($subject->code ?? '');
    $siName = $name ?? ($subject->name ?? '');
    $siDesc = $description ?? ($subject->description ?? '');
    $siVariant = $variant ?? 'inline';
    $siChair = auth()->check() && method_exists(auth()->user(), 'isChair') && auth()->user()->isChair();
@endphp
<span class="si-btn si-{{ $siVariant }}" role="button" tabindex="0" title="View the description of this subject"
      data-subject-info data-code="{{ $siCode }}" data-name="{{ $siName }}" data-desc="{{ $siDesc }}" data-chair="{{ $siChair ? 1 : 0 }}">
    <i class="fas fa-circle-info"></i><span>About</span>
</span>

@once
<style>
    .si-btn { display:inline-flex; align-items:center; gap:6px; font:600 11.5px 'Poppins',sans-serif; border-radius:999px; padding:5px 11px; cursor:pointer; user-select:none; transition:background .15s, transform .1s; line-height:1.2; text-decoration:none; }
    .si-btn:active { transform:scale(.96); }
    .si-btn i { font-size:11px; }
    .si-inline { background:#f5e8e8; color:#7B1D1D; margin-top:6px; }
    .si-inline:hover { background:#efd6d6; }
    .si-corner { position:absolute; top:12px; right:14px; z-index:4; background:rgba(255,255,255,.22); color:#fff; backdrop-filter:blur(3px); border:1px solid rgba(255,255,255,.35); text-shadow:none; }
    .si-corner:hover { background:rgba(255,255,255,.36); }
    .si-btn:focus-visible { outline:3px solid #7B1D1D; outline-offset:2px; }
    .si-overlay { position:fixed; inset:0; background:rgba(15,17,30,.55); backdrop-filter:blur(3px); z-index:99990; display:none; align-items:center; justify-content:center; padding:20px; }
    .si-overlay.open { display:flex; }
    .si-dialog { background:#fff; border-radius:20px; width:min(520px,100%); max-height:86vh; display:flex; flex-direction:column; box-shadow:0 30px 80px rgba(0,0,0,.35); overflow:hidden; font-family:'Poppins',sans-serif; animation:siIn .18s ease; }
    @keyframes siIn { from { opacity:0; transform:translateY(10px) scale(.98); } to { opacity:1; transform:none; } }
    .si-head { display:flex; gap:12px; align-items:flex-start; padding:20px 22px 14px; border-bottom:1px solid #f0f1f4; }
    .si-code { background:#7B1D1D; color:#fff; border-radius:10px; font-weight:700; font-size:13px; padding:6px 11px; flex-shrink:0; }
    .si-title { flex:1; min-width:0; } .si-title b { display:block; font-size:15px; color:#14283E; line-height:1.35; } .si-title small { color:#9a9cab; font-size:11.5px; }
    .si-x { border:0; background:#f0f1f5; width:32px; height:32px; border-radius:50%; cursor:pointer; color:#666; flex-shrink:0; }
    .si-x:hover { background:#e4e6ec; }
    .si-body { padding:18px 22px 22px; overflow-y:auto; font-size:13.5px; line-height:1.75; color:#333; white-space:pre-line; overflow-wrap:anywhere; }
    .si-empty { color:#9a9cab; font-style:italic; white-space:normal; }
</style>
<script>
(function () {
    let overlay;
    function build() {
        overlay = document.createElement('div');
        overlay.className = 'si-overlay';
        overlay.setAttribute('role', 'dialog'); overlay.setAttribute('aria-modal', 'true');
        overlay.innerHTML = '<div class="si-dialog"><div class="si-head"><span class="si-code"></span><div class="si-title"><b></b><small>About this subject</small></div><button type="button" class="si-x" aria-label="Close"><i class="fas fa-xmark"></i></button></div><div class="si-body"></div></div>';
        document.body.appendChild(overlay);
        overlay.addEventListener('mousedown', e => { if (e.target === overlay) close(); });
        overlay.querySelector('.si-x').addEventListener('click', close);
    }
    function close() { if (overlay) overlay.classList.remove('open'); }
    function open(btn) {
        if (!overlay) build();
        overlay.querySelector('.si-code').textContent = btn.dataset.code || 'Subject';
        overlay.querySelector('.si-title b').textContent = btn.dataset.name || '';
        const body = overlay.querySelector('.si-body'), text = (btn.dataset.desc || '').trim();
        if (text) { body.classList.remove('si-empty'); body.textContent = text; }
        else {
            body.classList.add('si-empty');
            body.textContent = btn.dataset.chair === '1'
                ? 'No description yet. You can add one from Subjects, using Edit subject.'
                : 'No description has been added for this subject yet.';
        }
        overlay.classList.add('open');
        overlay.querySelector('.si-x').focus();
    }
    // capture phase: the button often sits inside a card that is itself a link
    document.addEventListener('click', e => {
        const btn = e.target.closest && e.target.closest('[data-subject-info]');
        if (!btn) return;
        e.preventDefault(); e.stopPropagation();
        open(btn);
    }, true);
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') close();
        const btn = e.target.closest && e.target.closest('[data-subject-info]');
        if (btn && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); e.stopPropagation(); open(btn); }
    }, true);
})();
</script>
@endonce
