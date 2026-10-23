{{-- "About" button that opens a topic's full description in a popup. Mirrors
     partials.subject-info-btn, but for a single topic (no subject code badge).
     Pass either $topic (anything with name / description) or $name, $description.
     Optional: $variant = 'corner' (white, top-right of a coloured banner) | 'inline' (default, tinted).
     Optional: $editable = true shows a chair-facing "add one from Edit topic" empty state. --}}
@php
    $tiName = $name ?? ($topic->name ?? '');
    $tiDesc = $description ?? ($topic->description ?? '');
    $tiVariant = $variant ?? 'inline';
    $tiEditable = $editable ?? false;
@endphp
<span class="ti-btn ti-{{ $tiVariant }}" role="button" tabindex="0" title="View the description of this topic"
      data-topic-info data-name="{{ $tiName }}" data-desc="{{ $tiDesc }}" data-editable="{{ $tiEditable ? 1 : 0 }}">
    <i class="fas fa-circle-info"></i><span>About</span>
</span>

@once
<style>
    .ti-btn { display:inline-flex; align-items:center; gap:5px; font:600 10.5px 'Poppins',sans-serif; border-radius:999px; padding:3px 9px; cursor:pointer; user-select:none; transition:background .15s, transform .1s; line-height:1.2; text-decoration:none; vertical-align:middle; margin-left:6px; }
    .ti-btn:active { transform:scale(.96); }
    .ti-btn i { font-size:10px; }
    .ti-inline { background:#f5e8e8; color:#7B1D1D; }
    .ti-inline:hover { background:#efd6d6; }
    .ti-corner { position:absolute; top:12px; right:14px; z-index:4; background:rgba(255,255,255,.22); color:#fff; backdrop-filter:blur(3px); border:1px solid rgba(255,255,255,.35); text-shadow:none; }
    .ti-corner:hover { background:rgba(255,255,255,.36); }
    .ti-btn:focus-visible { outline:3px solid #7B1D1D; outline-offset:2px; }
    .ti-overlay { position:fixed; inset:0; background:rgba(15,17,30,.55); backdrop-filter:blur(3px); z-index:99990; display:none; align-items:center; justify-content:center; padding:20px; }
    .ti-overlay.open { display:flex; }
    .ti-dialog { background:#fff; border-radius:20px; width:min(520px,100%); max-height:86vh; display:flex; flex-direction:column; box-shadow:0 30px 80px rgba(0,0,0,.35); overflow:hidden; font-family:'Poppins',sans-serif; animation:tiIn .18s ease; }
    @keyframes tiIn { from { opacity:0; transform:translateY(10px) scale(.98); } to { opacity:1; transform:none; } }
    .ti-head { display:flex; gap:12px; align-items:flex-start; padding:20px 22px 14px; border-bottom:1px solid #f0f1f4; }
    .ti-title { flex:1; min-width:0; } .ti-title b { display:block; font-size:15px; color:#14283E; line-height:1.35; } .ti-title small { color:#9a9cab; font-size:11.5px; }
    .ti-x { border:0; background:#f0f1f5; width:32px; height:32px; border-radius:50%; cursor:pointer; color:#666; flex-shrink:0; }
    .ti-x:hover { background:#e4e6ec; }
    .ti-body { padding:18px 22px 22px; overflow-y:auto; font-size:13.5px; line-height:1.75; color:#333; white-space:pre-line; overflow-wrap:anywhere; }
    .ti-empty { color:#9a9cab; font-style:italic; white-space:normal; }
</style>
<script>
(function () {
    let overlay;
    function build() {
        overlay = document.createElement('div');
        overlay.className = 'ti-overlay';
        overlay.setAttribute('role', 'dialog'); overlay.setAttribute('aria-modal', 'true');
        overlay.innerHTML = '<div class="ti-dialog"><div class="ti-head"><div class="ti-title"><b></b><small>About this topic</small></div><button type="button" class="ti-x" aria-label="Close"><i class="fas fa-xmark"></i></button></div><div class="ti-body"></div></div>';
        document.body.appendChild(overlay);
        overlay.addEventListener('mousedown', e => { if (e.target === overlay) close(); });
        overlay.querySelector('.ti-x').addEventListener('click', close);
    }
    function close() { if (overlay) overlay.classList.remove('open'); }
    function open(btn) {
        if (!overlay) build();
        overlay.querySelector('.ti-title b').textContent = btn.dataset.name || '';
        const body = overlay.querySelector('.ti-body'), text = (btn.dataset.desc || '').trim();
        if (text) { body.classList.remove('ti-empty'); body.textContent = text; }
        else {
            body.classList.add('ti-empty');
            body.textContent = btn.dataset.editable === '1'
                ? 'No description yet. You can add one from Edit topic.'
                : 'No description has been added for this topic yet.';
        }
        overlay.classList.add('open');
        overlay.querySelector('.ti-x').focus();
    }
    document.addEventListener('click', e => {
        const btn = e.target.closest && e.target.closest('[data-topic-info]');
        if (!btn) return;
        e.preventDefault(); e.stopPropagation();
        open(btn);
    }, true);
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') close();
        const btn = e.target.closest && e.target.closest('[data-topic-info]');
        if (btn && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); e.stopPropagation(); open(btn); }
    }, true);
})();
</script>
@endonce
