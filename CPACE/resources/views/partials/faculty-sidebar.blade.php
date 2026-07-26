{{--
    Faculty portal sidebar + shared sidebar CSS for all faculty pages.
    Usage: @include('partials.faculty-sidebar', ['active' => 'dashboard'])
    Valid $active keys: dashboard, test-bank, add-question, subjects, performance, reports, notifications
--}}
@php $active = $active ?? ''; @endphp

<style>
    /* ─── SHARED FACULTY SIDEBAR ─── */
    .sidebar {
        background: #7B1D1D;
        background: linear-gradient(180deg, #7e1d1d 0%, #5c1616 34%, #2b0808 74%, #0f0505 100%);
        color: #fff;
        position: fixed;
        top: 0; left: 0;
        padding: 0; margin: 0;
        width: 230px;
        height: 100vh;
        display: flex;
        flex-direction: column;
        z-index: 1000;
        overflow-y: auto;
        overflow-x: hidden;
        transition: width 0.28s cubic-bezier(.4,0,.2,1);
        border-radius: 0 22px 22px 0;
        box-shadow: 6px 0 30px rgba(0,0,0,0.28);
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .sidebar::-webkit-scrollbar { width: 0; height: 0; display: none; }
    .sidebar.collapsed { width: 68px; }

    /* ── Logo (doubles as collapse toggle) ── */
    .sidebar .sidebar-logo {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 14px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        flex-shrink: 0;
        cursor: pointer;
        user-select: none;
        transition: opacity 0.15s;
    }
    .sidebar .sidebar-logo:hover { opacity: 0.85; }
    .sidebar .logo-icon {
        width: 52px; height: 52px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        overflow: hidden;
    }
    .sidebar .logo-icon img { width: 100%; height: 100%; object-fit: contain; }
    .sidebar .logo-text { line-height: 1.2; }
    .sidebar .logo-text strong { display: block; font-size: 19px; font-weight: 700; letter-spacing: 0.6px; color: #fff; }
    .sidebar .logo-text small  { font-size: 10px; color: rgba(255,255,255,0.55); font-weight: 400; font-style: italic; white-space: nowrap; }
    .sidebar .portal-badge {
        display: inline-block; margin-top: 4px;
        background: rgba(255,255,255,0.18); color: #fff;
        font-size: 9px; font-weight: 700;
        padding: 2px 8px; border-radius: 20px;
        letter-spacing: 1px; text-transform: uppercase;
    }
    .sidebar.collapsed .logo-text { display: none; }
    .sidebar.collapsed .sidebar-logo { justify-content: center; padding: 14px 0 18px; }

    /* ── Nav labels ── */
    .sidebar .sidebar-nav { list-style: none; flex: 1; margin: 0; padding: 8px 0 0; }
    .sidebar .sidebar-nav li { list-style: none; }
    .sidebar .nav-label {
        font-size: 9.5px; font-weight: 700; letter-spacing: 1.2px;
        text-transform: uppercase; color: rgba(255,255,255,0.35);
        padding: 18px 22px 6px;
        white-space: nowrap; overflow: hidden;
    }
    .sidebar.collapsed .nav-label { visibility: hidden; padding: 14px 0 4px; }

    /* ── Nav items ── */
    .sidebar .sidebar-nav li a {
        display: flex; align-items: center; gap: 11px;
        margin: 1px 10px; padding: 9px 12px;
        border: 0; border-radius: 8px;
        color: rgba(255,255,255,0.65);
        text-decoration: none; font-size: 13px; font-weight: 400;
        transition: background 0.18s, color 0.18s;
        white-space: nowrap;
    }
    .sidebar .sidebar-nav li a:hover { color: #fff; background: rgba(255,255,255,0.1); }
    .sidebar .sidebar-nav li a.active { color: #fff; background: rgba(255,255,255,0.16); font-weight: 500; }
    .sidebar .sidebar-nav li a.active i { color: #ffb3b3; }
    .sidebar .sidebar-nav li a i {
        width: 17px; text-align: center; font-size: 14px; flex-shrink: 0;
        color: rgba(255,255,255,0.5); transition: color 0.18s;
    }
    .sidebar .sidebar-nav li a:hover i { color: rgba(255,255,255,0.85); }
    .sidebar.collapsed .sidebar-nav li a {
        margin: 1px 6px; padding: 10px 0;
        justify-content: center; gap: 0;
    }
    .sidebar.collapsed .sidebar-nav li a span { display: none; }

    /* ── Footer / user ── */
    .sidebar .sidebar-footer {
        border-top: 1px solid rgba(255,255,255,0.1);
        padding: 14px 12px; flex-shrink: 0;
    }
    .sidebar .user-menu { position: relative; }
    .sidebar .user-profile {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 10px; border-radius: 8px;
        cursor: pointer; transition: background 0.18s;
    }
    .sidebar .user-profile:hover { background: rgba(255,255,255,0.08); }
    .sidebar .avatar-sm {
        width: 34px; height: 34px;
        background: rgba(255,255,255,0.18); border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 12px; color: #fff;
        flex-shrink: 0; letter-spacing: 0.5px;
        position: relative; overflow: hidden;
    }
    .sidebar .avatar-sm img {
        position: absolute; inset: 0;
        width: 100%; height: 100%;
        object-fit: cover; border-radius: inherit;
    }
    .sidebar .avatar-default {
        display: flex; align-items: center; justify-content: center;
        width: 100%; height: 100%;
        font-size: inherit; font-weight: inherit;
        color: inherit; letter-spacing: inherit; line-height: 1;
    }
    .sidebar .user-details { flex: 1; min-width: 0; }
    .sidebar .user-details .uname { display: block; font-size: 12.5px; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sidebar .user-details .urole { display: block; font-size: 10.5px; color: rgba(255,255,255,0.5); margin-top: 1px; }
    .sidebar.collapsed .user-details,
    .sidebar.collapsed .chevron-icon { display: none; }
    .sidebar.collapsed .user-profile { justify-content: center; padding: 8px 0; }
    .sidebar .chevron-icon { transition: transform 0.2s; color: rgba(255,255,255,0.4); font-size: 10px; }
    .sidebar .user-menu.open .chevron-icon { transform: rotate(180deg); }
    .sidebar .user-dropdown { display: none; margin-top: 6px; background: rgba(0,0,0,0.22); border-radius: 8px; overflow: hidden; }
    .sidebar .user-dropdown.open { display: block; }
    .sidebar .user-dropdown button {
        display: flex; align-items: center; gap: 9px;
        width: 100%; padding: 9px 14px;
        background: none; border: none; color: rgba(255,255,255,0.8);
        font-size: 12.5px; cursor: pointer; text-align: left;
        font-family: 'Poppins', sans-serif;
        transition: background 0.15s, color 0.15s;
    }
    .sidebar .user-dropdown button:hover { background: rgba(255,255,255,0.1); color: #fff; }
    .sidebar .user-dropdown button i { width: 15px; text-align: center; color: rgba(255,255,255,0.55); }
    .sidebar.collapsed .user-dropdown { display: none !important; }

    @media (max-width: 900px) {
        .sidebar { width: 68px; }
        .sidebar .logo-text,
        .sidebar .portal-badge,
        .sidebar .sidebar-nav li a span,
        .sidebar .user-details,
        .sidebar .chevron-icon,
        .sidebar .nav-label { display: none; }
        .sidebar .sidebar-nav li a { margin: 1px 6px; padding: 10px 0; justify-content: center; gap: 0; }
        .sidebar .sidebar-logo { justify-content: center; padding: 24px 0 22px; }
        .sidebar .user-profile { justify-content: center; padding: 8px 0; }
    }

    @media (max-width: 768px) {
        .sidebar { display: none !important; }
        .main { margin-left: 0 !important; padding: 16px !important; }
        .topbar { flex-direction: column; align-items: flex-start; gap: 10px; }
        .topbar-right { width: 100%; justify-content: flex-end; flex-wrap: wrap; gap: 8px; }
        .topbar-search input { width: 160px; }
        .page-title { font-size: 20px !important; }
        .stats-row { grid-template-columns: repeat(2, 1fr) !important; }
        .main-grid { grid-template-columns: 1fr !important; }
    }

    @media (max-width: 480px) {
        .stats-row { grid-template-columns: 1fr !important; }
        .page-sub { display: none; }
    }

    /* ── Topbar search + avatar ── */
    .topbar-search { position: relative; }
    .topbar-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #bbb; font-size: 13px; pointer-events: none; }
    .topbar-search input {
        width: 220px; padding: 9px 14px 9px 34px;
        border: 1px solid #e5e7eb; border-radius: 22px;
        font-size: 12.5px; font-family: 'Poppins', sans-serif;
        background: #f9fafb; color: #374151; outline: none;
        transition: border-color .2s, background .2s;
    }
    .topbar-search input:focus { border-color: var(--primary); background: #fff; }
    .topbar-search input::placeholder { color: #bbb; }

    .topbar-avatar-wrap { position: relative; }
    .topbar-avatar-btn {
        width: 38px; height: 38px; border-radius: 10px; border: none;
        background: var(--primary); color: #fff;
        font-weight: 700; font-size: 13px; font-family: 'Poppins', sans-serif;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        position: relative; overflow: hidden; transition: background .2s;
    }
    .topbar-avatar-btn:hover { background: var(--primary-hover); }
    .topbar-avatar-btn img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; border-radius: inherit; }
    .topbar-avatar-btn .avatar-default { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; font-size: inherit; font-weight: inherit; color: inherit; line-height: 1; }

    .topbar-dropdown {
        display: none; position: absolute;
        top: calc(100% + 8px); right: 0;
        background: #fff; border: 1px solid #e2e8f0;
        border-radius: 10px; min-width: 185px;
        box-shadow: 0 6px 20px rgba(0,0,0,.12);
        z-index: 2000; overflow: hidden;
    }
    .topbar-dropdown.open { display: block; }
    .topbar-dropdown a,
    .topbar-dropdown button {
        display: flex; align-items: center; gap: 10px;
        padding: 11px 16px; font-size: 13px;
        font-family: 'Poppins', sans-serif;
        text-decoration: none; color: #1a202c;
        background: none; border: none; width: 100%;
        text-align: left; cursor: pointer;
        transition: background .2s;
        border-bottom: 1px solid #f5f5f5;
    }
    .topbar-dropdown a:last-child,
    .topbar-dropdown form:last-child button { border-bottom: none; }
    .topbar-dropdown a:hover,
    .topbar-dropdown button:hover { background: #f7fafc; }
    .topbar-dropdown a i,
    .topbar-dropdown button i { color: var(--primary); width: 16px; text-align: center; }
    .tda-logout { color: #e53e3e !important; }
    .tda-logout i { color: #e53e3e !important; }

    /* ── Profile modal ── */
    .fp-modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(15, 5, 5, 0.55);
        z-index: 3000; align-items: center; justify-content: center;
        padding: 20px;
    }
    .fp-modal-overlay.open { display: flex; }
    .fp-modal {
        background: #fff; border-radius: 16px;
        width: 100%; max-width: 420px;
        max-height: 90vh; overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,.3);
    }
    .fp-modal-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 18px 22px; border-bottom: 1px solid #f0f0f0;
    }
    .fp-modal-head h3 { font-size: 16px; font-weight: 600; color: #1a1a1a; margin: 0; }
    .fp-modal-close {
        width: 30px; height: 30px; border: none; background: #f4f5f7;
        border-radius: 8px; color: #666; cursor: pointer; font-size: 13px;
    }
    .fp-modal-close:hover { background: #e9eaed; }
    .fp-modal-body { padding: 22px; }
    .fp-avatar-row { display: flex; align-items: center; gap: 16px; margin-bottom: 18px; }
    .fp-avatar-preview {
        width: 64px; height: 64px; border-radius: 14px;
        background: var(--primary); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 20px; overflow: hidden; position: relative; flex-shrink: 0;
    }
    .fp-avatar-preview img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .fp-upload-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 8px 14px; border-radius: 8px; border: 1.5px solid var(--primary);
        background: #fff; color: var(--primary); font-size: 12.5px; font-weight: 600;
        cursor: pointer; font-family: 'Poppins', sans-serif;
    }
    .fp-upload-btn:hover { background: var(--primary-light, #f5e8e8); }
    .fp-field { margin-bottom: 14px; }
    .fp-field label { display: block; font-size: 12.5px; font-weight: 500; color: #555; margin-bottom: 6px; }
    .fp-field input {
        width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 8px;
        font-size: 13.5px; font-family: 'Poppins', sans-serif; color: #1a1a1a; outline: none;
    }
    .fp-field input:focus { border-color: var(--primary); }
    .fp-row-2 { display: flex; gap: 12px; }
    .fp-row-2 .fp-field { flex: 1; }
    .fp-modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding-top: 6px; }
    .fp-btn {
        padding: 10px 20px; border-radius: 8px; border: none;
        font-size: 13px; font-weight: 600; font-family: 'Poppins', sans-serif; cursor: pointer;
    }
    .fp-btn-cancel { background: #f4f5f7; color: #555; }
    .fp-btn-cancel:hover { background: #e9eaed; }
    .fp-btn-save { background: var(--primary); color: #fff; }
    .fp-btn-save:hover { background: #6a1818; }
    .fp-status {
        margin-bottom: 14px; padding: 10px 14px; border-radius: 8px;
        font-size: 12.5px; background: #ecfdf5; color: #047857; display: none;
    }
    .fp-status.show { display: block; }
    .fp-errors {
        margin-bottom: 14px; padding: 10px 14px; border-radius: 8px;
        font-size: 12.5px; background: #fef2f2; color: #b91c1c;
    }
</style>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo" id="sidebarCollapseBtn" title="Toggle sidebar">
        <div class="logo-icon">
            <img src="{{ asset('images/cpace_logo.png') }}" alt="CPACE Logo">
        </div>
        <div class="logo-text">
            <strong>CPACE</strong>
            <small>CPA Reviewer</small>
            <div class="portal-badge">Faculty</div>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-label">Main</li>
        <li><a href="{{ route('faculty.dashboard') }}" class="{{ $active === 'dashboard' ? 'active' : '' }}"><i class="fas fa-home"></i><span>Dashboard</span></a></li>

        <li class="nav-label">Content</li>
        <li><a href="{{ route('faculty.test-bank') }}" class="{{ $active === 'test-bank' ? 'active' : '' }}"><i class="fas fa-database"></i><span>Test Bank</span></a></li>
        <li><a href="{{ route('faculty.question.create') }}" class="{{ $active === 'add-question' ? 'active' : '' }}"><i class="fas fa-plus-circle"></i><span>Add Question</span></a></li>
        <li><a href="{{ route('faculty.materials') }}" class="{{ $active === 'materials' ? 'active' : '' }}"><i class="fas fa-folder-open"></i><span>Learning Materials</span></a></li>

        <li class="nav-label">Analytics</li>
        <li><a href="{{ route('faculty.performance') }}" class="{{ $active === 'performance' ? 'active' : '' }}"><i class="fas fa-users"></i><span>Student Performance</span></a></li>
        <li><a href="{{ route('faculty.reports') }}" class="{{ $active === 'reports' ? 'active' : '' }}"><i class="fas fa-chart-line"></i><span>Reports</span></a></li>

        <li class="nav-label">System</li>
        <li><a href="{{ route('faculty.settings') }}" class="{{ $active === 'settings' ? 'active' : '' }}"><i class="fas fa-cog"></i><span>Settings</span></a></li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-menu" id="userMenu">
            <div class="user-profile" onclick="document.getElementById('userMenu').classList.toggle('open'); document.getElementById('userDropdown').classList.toggle('open');">
                <div class="avatar-sm">
                    @include('partials.avatar-content')
                </div>
                <div class="user-details">
                    <span class="uname">{{ Auth::user()->name }}</span>
                    <span class="urole">Faculty</span>
                </div>
                <i class="fas fa-chevron-down chevron-icon"></i>
            </div>
            <div class="user-dropdown" id="userDropdown">
                <button type="button" id="sidebarProfileLink"><i class="fas fa-id-badge"></i><span>Profile</span></button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"><i class="fas fa-sign-out-alt"></i><span>Logout</span></button>
                </form>
            </div>
        </div>
    </div>
</aside>

<div class="fp-modal-overlay" id="fpModalOverlay">
    <div class="fp-modal">
        <div class="fp-modal-head">
            <h3>Profile</h3>
            <button type="button" class="fp-modal-close" id="fpModalClose"><i class="fas fa-times"></i></button>
        </div>
        <div class="fp-modal-body">
            <div class="fp-status" id="fpStatus"></div>
            @if ($errors->any())
                <div class="fp-errors">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            <form method="POST" action="{{ route('faculty.settings.profile') }}" enctype="multipart/form-data" id="fpForm">
                @csrf
                <div class="fp-avatar-row">
                    <div class="fp-avatar-preview" id="fpAvatarPreview">
                        @include('partials.avatar-content')
                    </div>
                    <div>
                        <label class="fp-upload-btn" for="fpPhotoInput"><i class="fas fa-camera"></i> Change photo</label>
                        <input type="file" name="photo" id="fpPhotoInput" accept="image/*" style="display:none;">
                    </div>
                </div>
                <div class="fp-row-2">
                    <div class="fp-field">
                        <label>First name</label>
                        <input type="text" name="first_name" value="{{ old('first_name', Auth::user()->first_name) }}" required>
                    </div>
                    <div class="fp-field">
                        <label>Last name</label>
                        <input type="text" name="last_name" value="{{ old('last_name', Auth::user()->last_name) }}" required>
                    </div>
                </div>
                <div class="fp-field">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" required>
                </div>
                <div class="fp-modal-footer">
                    <button type="button" class="fp-btn fp-btn-cancel" id="fpCancelBtn">Cancel</button>
                    <button type="submit" class="fp-btn fp-btn-save">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;

    if (localStorage.getItem('facultySidebar') === 'true') {
        sidebar.classList.add('collapsed');
    }

    function toggle() {
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('facultySidebar', sidebar.classList.contains('collapsed'));
    }

    const logo = document.getElementById('sidebarCollapseBtn');
    if (logo) logo.addEventListener('click', function(e) { e.stopPropagation(); toggle(); });

    /* topbar avatar dropdown */
    document.addEventListener('DOMContentLoaded', function() {
        const avatarBtn = document.getElementById('topbarAvatarBtn');
        const dropdown  = document.getElementById('topbarDropdown');
        if (avatarBtn && dropdown) {
            avatarBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.classList.toggle('open');
            });
            document.addEventListener('click', function() { dropdown.classList.remove('open'); });
        }

        /* profile modal */
        const overlay  = document.getElementById('fpModalOverlay');
        const openBtns = [document.getElementById('topbarProfileLink'), document.getElementById('sidebarProfileLink')];
        const closeBtn = document.getElementById('fpModalClose');
        const cancelBtn = document.getElementById('fpCancelBtn');
        const photoInput = document.getElementById('fpPhotoInput');
        const avatarPreview = document.getElementById('fpAvatarPreview');
        const form = document.getElementById('fpForm');
        const statusEl = document.getElementById('fpStatus');

        function openModal() {
            if (overlay) overlay.classList.add('open');
        }
        function closeModal() {
            if (overlay) overlay.classList.remove('open');
        }

        openBtns.forEach(function(btn) {
            if (!btn) return;
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openModal();
            });
        });
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
        if (overlay) overlay.addEventListener('click', function(e) { if (e.target === overlay) closeModal(); });

        if (photoInput && avatarPreview) {
            photoInput.addEventListener('change', function() {
                const file = photoInput.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                };
                reader.readAsDataURL(file);
            });
        }

        if (form) {
            form.addEventListener('submit', function() {
                sessionStorage.setItem('fpModalReopen', '1');
            });
        }

        if (sessionStorage.getItem('fpModalReopen') === '1') {
            sessionStorage.removeItem('fpModalReopen');
            @if (session('status'))
                statusEl.textContent = @json(session('status'));
                statusEl.classList.add('show');
            @endif
            openModal();
        }
    });
})();
</script>
