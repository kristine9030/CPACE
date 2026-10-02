{{--
    "Choose your avatar" grid for the profile modals.
    Usage: @include('partials.avatar-picker', ['p' => 'sp'])   ({{ '' }}p = id prefix of the modal)
    Picking one fills the hidden "avatar" field, previews it, and replaces any uploaded photo on save.
    Leaving it alone keeps whatever the user has — or the system-assigned default.
--}}
@php
    $pickUser = Auth::user();
    $pickFiles = \App\Models\User::availableAvatars();
    $pickCurrent = $pickUser->avatar && in_array($pickUser->avatar, $pickFiles, true)
        ? $pickUser->avatar
        : ($pickFiles ? $pickFiles[$pickUser->id % count($pickFiles)] : null);
@endphp
@if($pickFiles)
    <div class="av-picker" id="{{ $p }}AvatarPicker">
        <div class="av-picker-label">Choose your avatar
            <span>{{ $pickUser->avatar ? '' : '(one was picked for you — change it anytime)' }}</span>
        </div>
        <div class="av-picker-grid" role="radiogroup" aria-label="Avatar">
            @foreach($pickFiles as $file)
                @php $src = asset('images/AVATARS/' . rawurlencode($file)); @endphp
                <button type="button" class="av-pick {{ (! $pickUser->profile_photo && $pickCurrent === $file) ? 'selected' : '' }}"
                        role="radio" aria-checked="{{ (! $pickUser->profile_photo && $pickCurrent === $file) ? 'true' : 'false' }}"
                        data-file="{{ $file }}" data-src="{{ $src }}" title="Use this avatar">
                    <img src="{{ $src }}" alt="">
                </button>
            @endforeach
        </div>
        <input type="hidden" name="avatar" id="{{ $p }}AvatarInput" value="{{ old('avatar', $pickUser->avatar) }}">
    </div>
    <style>
        .av-picker { margin-bottom: 18px; }
        .av-picker-label { font-size: 12.5px; font-weight: 500; color: #555; margin-bottom: 8px; }
        .av-picker-label span { font-weight: 400; color: #aaa; margin-left: 4px; }
        .av-picker-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; }
        .av-pick { position: relative; aspect-ratio: 1; padding: 0; border: 2px solid transparent; border-radius: 50%; background: #f4f5f7; cursor: pointer; overflow: hidden; transition: transform .12s, border-color .12s; }
        .av-pick img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .av-pick:hover { transform: scale(1.06); }
        .av-pick.selected { border-color: #7B1D1D; box-shadow: 0 0 0 3px rgba(123,29,29,.18); }
        @media (max-width: 420px) { .av-picker-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    </style>
    <script>
    (function () {
        const p = @json($p);
        const root = document.getElementById(p + 'AvatarPicker');
        if (!root) return;
        const input = document.getElementById(p + 'AvatarInput');
        const preview = document.getElementById(p + 'AvatarPreview');
        const removeBtn = document.getElementById(p + 'RemovePhoto');
        const photoInput = document.getElementById(p + 'PhotoInput');
        root.querySelectorAll('.av-pick').forEach(function (btn) {
            btn.addEventListener('click', function () {
                root.querySelectorAll('.av-pick').forEach(function (b) { b.classList.toggle('selected', b === btn); b.setAttribute('aria-checked', b === btn ? 'true' : 'false'); });
                input.value = btn.dataset.file;
                if (preview) preview.innerHTML = '<img src="' + btn.dataset.src + '" alt="Preview">';
                // Picking an avatar replaces an uploaded photo.
                // Looked up now: this hidden field sits just below the picker in the form.
                const removeInput = document.getElementById(p + 'RemovePhotoInput');
                if (removeInput) removeInput.value = '1';
                if (photoInput) photoInput.value = '';
                if (removeBtn) removeBtn.style.display = 'none';
            });
        });
    })();
    </script>
@endif
