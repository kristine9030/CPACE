{{--
    Small (i) button that reveals a short explanation on hover, tap or keyboard
    focus. Use it next to a title or label instead of a paragraph under it.

        <div class="card-title">Answer similarity <x-tip>Pairs who chose the same wrong answer…</x-tip></div>

    label: accessible name of the button (default "What is this?").
    The bubble is drawn in a single body-level layer so card overflow/transforms
    never clip it; Esc or a tap elsewhere closes it.
--}}
@props(['label' => 'What is this?'])
@php($tipId = 'cx-tip-' . \Illuminate\Support\Str::random(8))

@once
<style>
    .cx-tip-btn { display:inline-flex; align-items:center; justify-content:center; width:18px; height:18px;
                  margin-left:4px; padding:0; border:0; border-radius:50%; background:transparent;
                  color:#9ca3af; font-size:13px; line-height:1; cursor:help; vertical-align:middle;
                  transition:color .15s, background .15s; }
    .cx-tip-btn:hover, .cx-tip-btn[aria-expanded="true"] { color:#7B1D1D; background:rgba(123,29,29,.08); }
    .cx-tip-btn:focus-visible { outline:2px solid #7B1D1D; outline-offset:2px; }
    .cx-tip-src { display:none; }
    #cx-tip-layer { position:fixed; z-index:99999; width:max-content; max-width:min(300px, calc(100vw - 24px));
                    background:#1f2937; color:#f9fafb; font:400 12.5px/1.55 'Poppins', system-ui, sans-serif;
                    text-transform:none; letter-spacing:normal; text-align:left; white-space:normal;
                    overflow-wrap:break-word; box-sizing:border-box;
                    padding:10px 12px; border-radius:10px; box-shadow:0 12px 30px rgba(0,0,0,.25);
                    opacity:0; transform:translateY(-4px); pointer-events:none; transition:opacity .12s, transform .12s; }
    #cx-tip-layer.show { opacity:1; transform:none; pointer-events:auto; }
    #cx-tip-layer strong, #cx-tip-layer b { color:#fff; }
    #cx-tip-layer table { width:100%; border-collapse:collapse; margin-top:2px; }
    #cx-tip-layer td { padding:3px 0; overflow-wrap:break-word; }
    #cx-tip-layer td:not(:first-child) { text-align:right; padding-left:10px; white-space:nowrap; width:1%; }
    #cx-tip-layer tr.tip-total td { border-top:1px solid rgba(255,255,255,.18); padding-top:6px; }
    #cx-tip-layer .tip-note { display:block; margin-top:8px; padding-top:8px; border-top:1px solid rgba(255,255,255,.18); color:#cbd2dc; }
</style>
<script>
(function () {
    let layer, current = null, pinned = false, hideTimer;
    function ensureLayer() {
        if (layer) return layer;
        layer = document.createElement('div');
        layer.id = 'cx-tip-layer';
        layer.setAttribute('role', 'tooltip');
        layer.addEventListener('pointerenter', () => clearTimeout(hideTimer));
        layer.addEventListener('pointerleave', () => { if (!pinned) scheduleHide(); });
        document.body.appendChild(layer);
        return layer;
    }
    function place(btn) {
        const r = btn.getBoundingClientRect(), l = layer, gap = 8;
        l.style.left = '0px'; l.style.top = '0px';
        const w = l.offsetWidth, h = l.offsetHeight;
        let left = Math.min(Math.max(12, r.left + r.width / 2 - w / 2), window.innerWidth - w - 12);
        let top = r.bottom + gap;
        if (top + h > window.innerHeight - 12) top = Math.max(12, r.top - h - gap);
        l.style.left = left + 'px'; l.style.top = top + 'px';
    }
    function show(btn, pin) {
        clearTimeout(hideTimer);
        const src = document.getElementById(btn.getAttribute('aria-describedby'));
        if (!src) return;
        if (current && current !== btn) current.setAttribute('aria-expanded', 'false');
        ensureLayer().innerHTML = src.innerHTML;
        current = btn; pinned = !!pin;
        btn.setAttribute('aria-expanded', 'true');
        place(btn);
        layer.classList.add('show');
    }
    function hide() {
        if (!layer) return;
        layer.classList.remove('show');
        if (current) current.setAttribute('aria-expanded', 'false');
        current = null; pinned = false;
    }
    function scheduleHide() { clearTimeout(hideTimer); hideTimer = setTimeout(hide, 120); }
    const tipOf = (e) => e.target.closest && e.target.closest('.cx-tip-btn');

    document.addEventListener('click', (e) => {
        const btn = tipOf(e);
        if (btn) { e.preventDefault(); e.stopPropagation(); (current === btn && pinned) ? hide() : show(btn, true); return; }
        if (layer && !layer.contains(e.target)) hide();
    }, true);
    document.addEventListener('pointerover', (e) => { const b = tipOf(e); if (b && e.pointerType === 'mouse' && !pinned) show(b, false); });
    document.addEventListener('pointerout', (e) => { const b = tipOf(e); if (b && e.pointerType === 'mouse' && !pinned) scheduleHide(); });
    document.addEventListener('focusin', (e) => { const b = tipOf(e); if (b && b.matches(':focus-visible')) show(b, false); });
    document.addEventListener('focusout', (e) => { if (tipOf(e) && !pinned) scheduleHide(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && current) { const b = current; hide(); b.focus(); } });
    window.addEventListener('scroll', () => { if (current) place(current); }, true);
    window.addEventListener('resize', hide);
})();
</script>
@endonce

<button type="button" {{ $attributes->class('cx-tip-btn') }} aria-label="{{ $label }}" aria-expanded="false" aria-describedby="{{ $tipId }}"><i class="fas fa-circle-info" aria-hidden="true"></i></button><span class="cx-tip-src" id="{{ $tipId }}">{{ $slot }}</span>
