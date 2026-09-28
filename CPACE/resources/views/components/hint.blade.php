{{--
    Compact notice with progressive disclosure: the one-line headline is always
    visible, the longer "why / how it works" text stays folded behind "Details".

        <x-hint tone="warn" icon="fa-lock" title="Locked — published by the Program Chair">
            Longer explanation, shown only when the reader asks for it.
        </x-hint>

    tone: info (default) | warn | danger | neutral | ai | success
    title: plain text, or <x-slot:title> for markup (e.g. <strong>).
    With no slot content it renders as a plain one-line notice.
--}}
@props(['tone' => 'info', 'icon' => 'fa-circle-info', 'title' => ''])

@once
<style>
    .cx-hint { --h-bg:#eff6ff; --h-line:#dbeafe; --h-ink:#1e3a8a; --h-icon:#2563eb;
               background:var(--h-bg); border:1px solid var(--h-line); color:var(--h-ink);
               border-radius:12px; font-size:13px; line-height:1.5; margin:0 0 16px; }
    .cx-hint--warn    { --h-bg:#fffbeb; --h-line:#fde68a; --h-ink:#78350f; --h-icon:#b45309; }
    .cx-hint--danger  { --h-bg:#fef2f2; --h-line:#fecaca; --h-ink:#7f1d1d; --h-icon:#b91c1c; }
    .cx-hint--neutral { --h-bg:#f8fafc; --h-line:#e5e7eb; --h-ink:#374151; --h-icon:#6b7280; }
    .cx-hint--ai      { --h-bg:#f5f3ff; --h-line:#ddd6fe; --h-ink:#4c1d95; --h-icon:#7c3aed; }
    .cx-hint--success { --h-bg:#ecfdf5; --h-line:#a7f3d0; --h-ink:#065f46; --h-icon:#059669; }
    .cx-hint-head { display:flex; align-items:center; gap:10px; padding:10px 14px; list-style:none; }
    .cx-hint-head::-webkit-details-marker { display:none; }
    summary.cx-hint-head { cursor:pointer; border-radius:12px; user-select:none; }
    summary.cx-hint-head:focus-visible { outline:2px solid var(--h-icon); outline-offset:2px; }
    .cx-hint-icon { color:var(--h-icon); flex-shrink:0; width:16px; text-align:center; }
    .cx-hint-title { flex:1; min-width:0; }
    .cx-hint-more { flex-shrink:0; display:inline-flex; align-items:center; gap:5px; font-size:12px;
                    font-weight:600; color:var(--h-icon); padding:3px 9px; border-radius:999px;
                    background:rgba(255,255,255,.65); white-space:nowrap; }
    summary.cx-hint-head:hover .cx-hint-more { background:#fff; }
    .cx-hint-more i { font-size:10px; transition:transform .2s ease; }
    .cx-hint[open] .cx-hint-more i { transform:rotate(180deg); }
    .cx-hint[open] .cx-hint-more-label::after { content:'Hide'; }
    .cx-hint:not([open]) .cx-hint-more-label::after { content:'Details'; }
    .cx-hint-body { padding:0 14px 12px 40px; opacity:.92; }
    .cx-hint-body p { margin:0 0 6px; } .cx-hint-body p:last-child { margin:0; }
    .cx-hint-body ul { margin:4px 0 0; padding-left:18px; } .cx-hint-body li { margin:2px 0; }
    @media (max-width:560px) { .cx-hint-body { padding-left:14px; } .cx-hint-more-label { display:none; } }
</style>
@endonce

@if (trim($slot) !== '')
    <details {{ $attributes->class(['cx-hint', 'cx-hint--' . $tone]) }}>
        <summary class="cx-hint-head">
            <i class="fas {{ $icon }} cx-hint-icon" aria-hidden="true"></i>
            <span class="cx-hint-title">{{ $title }}</span>
            <span class="cx-hint-more"><span class="cx-hint-more-label"></span><i class="fas fa-chevron-down" aria-hidden="true"></i></span>
        </summary>
        <div class="cx-hint-body">{{ $slot }}</div>
    </details>
@else
    <div {{ $attributes->class(['cx-hint', 'cx-hint--' . $tone]) }} role="note">
        <div class="cx-hint-head">
            <i class="fas {{ $icon }} cx-hint-icon" aria-hidden="true"></i>
            <span class="cx-hint-title">{{ $title }}</span>
        </div>
    </div>
@endif
