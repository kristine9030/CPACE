{{-- Subject description banner. Expects $description (string|null); optional $dark for dark backgrounds. --}}
@if(filled($description ?? null))
    <div class="subject-desc-box" style="display:flex;gap:10px;align-items:flex-start;background:{{ !empty($dark) ? 'rgba(255,255,255,.14)' : '#fff' }};color:{{ !empty($dark) ? 'inherit' : '#555' }};border-radius:12px;padding:12px 16px;margin:0 0 18px;font-size:12.5px;line-height:1.6;{{ empty($dark) ? 'border-left:3px solid #7B1D1D;' : '' }}">
        <i class="fas fa-circle-info" style="margin-top:3px;opacity:.7;"></i>
        <div><strong style="display:block;font-size:11px;letter-spacing:.04em;text-transform:uppercase;opacity:.65;margin-bottom:2px;">About this subject</strong>{!! nl2br(e($description)) !!}</div>
    </div>
@endif
