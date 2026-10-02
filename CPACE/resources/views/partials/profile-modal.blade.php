{{--
    Profile Settings popup for Program Chair, Super Admin and Alumni.
    Opens from the sidebar menu's "Profile Settings" button, the topbar
    avatar menu (#topbarProfileLink) or anything with .js-open-profile-modal.
--}}
<div class="cp-overlay" id="cpOverlay">
    <div class="cp-modal" role="dialog" aria-modal="true" aria-labelledby="cpTitle">
        <div class="cp-head">
            <h3 id="cpTitle">Profile Settings</h3>
            <button type="button" class="cp-close" id="cpClose" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
        <div class="cp-body">
            <div class="cp-status" id="cpStatus"></div>
            @if ($errors->any() && old('first_name') !== null)
                <div class="cp-errors">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
            @endif
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="cpForm">
                @csrf
                <div class="cp-avatar-row">
                    <div class="cp-avatar-preview" id="cpAvatarPreview">@include('partials.avatar-content')</div>
                    <div>
                        <label class="cp-upload" for="cpPhotoInput"><i class="fas fa-camera"></i> Upload photo</label>
                        <input type="file" name="photo" id="cpPhotoInput" accept="image/*" style="display:none;">
                        @if(Auth::user()->profile_photo)
                            <button type="button" class="cp-remove" id="cpRemovePhoto"><i class="fas fa-xmark"></i> Remove photo, use my avatar</button>
                        @endif
                    </div>
                </div>

                @include('partials.avatar-picker', ['p' => 'cp'])
                <input type="hidden" name="remove_photo" id="cpRemovePhotoInput" value="0">

                <div class="cp-row">
                    <div class="cp-field"><label>First name</label><input type="text" name="first_name" value="{{ old('first_name', Auth::user()->first_name) }}" required></div>
                    <div class="cp-field"><label>Last name</label><input type="text" name="last_name" value="{{ old('last_name', Auth::user()->last_name) }}" required></div>
                </div>
                <div class="cp-field"><label>Email</label><input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" required></div>
                <div class="cp-foot">
                    <button type="button" class="cp-btn cp-cancel" id="cpCancel">Cancel</button>
                    <button type="submit" class="cp-btn cp-save">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .cp-overlay { display:none; position:fixed; inset:0; background:rgba(15,5,5,.55); z-index:3000; align-items:center; justify-content:center; padding:20px; }
    .cp-overlay.open { display:flex; }
    .cp-modal { background:#fff; border-radius:16px; width:100%; max-width:420px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.3); font-family:'Poppins',sans-serif; }
    .cp-head { display:flex; align-items:center; justify-content:space-between; padding:18px 22px; border-bottom:1px solid #f0f0f0; }
    .cp-head h3 { font-size:16px; font-weight:600; color:#1a1a1a; margin:0; }
    .cp-close { width:30px; height:30px; border:none; background:#f4f5f7; border-radius:8px; color:#666; cursor:pointer; font-size:13px; }
    .cp-close:hover { background:#e9eaed; }
    .cp-body { padding:22px; }
    .cp-avatar-row { display:flex; align-items:center; gap:16px; margin-bottom:18px; }
    .cp-avatar-preview { width:72px; height:72px; border-radius:50%; background:#f4f5f7; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:22px; overflow:hidden; position:relative; flex-shrink:0; }
    .cp-avatar-preview img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
    .cp-avatar-preview .avatar-default { width:100%; height:100%; display:flex; align-items:center; justify-content:center; }
    .cp-upload { display:inline-flex; align-items:center; gap:7px; padding:8px 14px; border-radius:8px; border:1.5px solid #7B1D1D; background:#fff; color:#7B1D1D; font-size:12.5px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif; }
    .cp-upload:hover { background:#f5e8e8; }
    .cp-remove { display:inline-flex; align-items:center; gap:6px; margin-top:6px; padding:0; border:none; background:none; color:#999; font-size:11.5px; font-family:'Poppins',sans-serif; cursor:pointer; }
    .cp-remove:hover { color:#c0392b; }
    .cp-field { margin-bottom:14px; }
    .cp-field label { display:block; font-size:12.5px; font-weight:500; color:#555; margin-bottom:6px; }
    .cp-field input { width:100%; padding:10px 12px; border:1px solid #e0e0e0; border-radius:8px; font-size:13.5px; font-family:'Poppins',sans-serif; color:#1a1a1a; outline:none; }
    .cp-field input:focus { border-color:#7B1D1D; }
    .cp-row { display:flex; gap:12px; } .cp-row .cp-field { flex:1; min-width:0; }
    .cp-foot { display:flex; justify-content:flex-end; gap:10px; padding-top:6px; }
    .cp-btn { padding:10px 20px; border-radius:8px; border:none; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; }
    .cp-cancel { background:#f4f5f7; color:#555; } .cp-cancel:hover { background:#e9eaed; }
    .cp-save { background:#7B1D1D; color:#fff; } .cp-save:hover { background:#6a1818; }
    .cp-status { display:none; margin-bottom:14px; padding:10px 14px; border-radius:8px; font-size:12.5px; background:#ecfdf5; color:#047857; }
    .cp-status.show { display:block; }
    .cp-errors { margin-bottom:14px; padding:10px 14px; border-radius:8px; font-size:12.5px; background:#fef2f2; color:#b91c1c; }
</style>

<script>
(function () {
    const overlay = document.getElementById('cpOverlay');
    if (!overlay) return;
    const open = () => overlay.classList.add('open');
    const close = () => overlay.classList.remove('open');

    document.addEventListener('click', function (e) {
        if (e.target.closest('.js-open-profile-modal, #topbarProfileLink')) {
            e.preventDefault();
            e.stopPropagation();
            open();
        }
    });
    document.getElementById('cpClose').addEventListener('click', close);
    document.getElementById('cpCancel').addEventListener('click', close);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });

    const preview = document.getElementById('cpAvatarPreview');
    const photoInput = document.getElementById('cpPhotoInput');
    const removeInput = document.getElementById('cpRemovePhotoInput');
    const removeBtn = document.getElementById('cpRemovePhoto');

    photoInput.addEventListener('change', function () {
        const file = photoInput.files[0];
        if (!file) return;
        removeInput.value = '0';
        const reader = new FileReader();
        reader.onload = function (e) { preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">'; };
        reader.readAsDataURL(file);
    });

    if (removeBtn) {
        removeBtn.addEventListener('click', function () {
            photoInput.value = '';
            removeInput.value = '1';
            preview.innerHTML = '<img src="' + {!! json_encode(Auth::user()->presetAvatarUrl()) !!} + '" alt="">';
            removeBtn.style.display = 'none';
        });
    }

    document.getElementById('cpForm').addEventListener('submit', function () { sessionStorage.setItem('cpModalReopen', '1'); });
    if (sessionStorage.getItem('cpModalReopen') === '1') {
        sessionStorage.removeItem('cpModalReopen');
        @if (session('status'))
            const st = document.getElementById('cpStatus');
            st.textContent = @json(session('status'));
            st.classList.add('show');
        @endif
        open();
    }
})();
</script>
