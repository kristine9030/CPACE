<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CPACE CPA Reviewer</title>

    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #7B1D1D;
            --primary-hover: #6a1818;
            --primary-light: #f5e8e8;
            --accent-red: #c0392b;
            --sidebar-bg: linear-gradient(180deg, #a12626 0%, #7B1D1D 34%, #3d0c0c 74%, #1a0a0a 100%);
            --white: #ffffff;
            --gray-100: #f8f9fa;
            --gray-200: #f0f0f0;
            --gray-300: #e0e0e0;
            --gray-500: #999999;
            --gray-700: #555555;
            --gray-900: #333333;
            --green: #10b981;
            --blue: #3b82f6;
            --orange: #f59e0b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f5f7;
            color: var(--gray-900);
        }

        /* ─── HEADER ─── */
        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; gap:20px; }
        .top-bar-left { display:flex; align-items:center; gap:14px; }
        .top-bar-right { display:flex; align-items:center; gap:14px; }

        .search-wrap { position:relative; }
        .search-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#aaa; font-size:14px; }
        .search-wrap input {
            width:280px; padding:10px 14px 10px 36px;
            border:1px solid #e0e0e0; border-radius:24px;
            font-size:13px; font-family:'Poppins',sans-serif;
            background:white; color:#555; outline:none;
        }
        .search-wrap input:focus { border-color:var(--primary); }
        .search-wrap input::placeholder { color:#bbb; }

        .notif-btn {
            position:relative; width:40px; height:40px;
            border:none; background:white; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:17px; color:#555; cursor:pointer;
            box-shadow:0 1px 4px rgba(0,0,0,0.08); text-decoration:none;
        }
        .notif-btn:hover { background:#f0f0f0; }
        .badge {
            position:absolute; top:-3px; right:-3px;
            width:18px; height:18px; background:var(--accent-red);
            color:white; border-radius:50%; font-size:10px; font-weight:700;
            display:flex; align-items:center; justify-content:center;
        }
        .profile-avatar {
            width:40px; height:40px; background:var(--primary);
            border-radius:10px; border:none; color:white;
            font-weight:700; font-size:14px; cursor:pointer;
            font-family:'Poppins',sans-serif; transition:background 0.2s;
        }
        .profile-avatar:hover { background:var(--primary-hover); }

        .header-dropdown-wrap { position:relative; }
        .dropdown-menu {
            position:absolute; top:calc(100% + 8px); right:0;
            background:white; border:1px solid #e5e7eb; border-radius:10px;
            min-width:185px; box-shadow:0 6px 20px rgba(0,0,0,0.12);
            display:none; z-index:2000;
        }
        .dropdown-menu.active { display:block; }
        .dropdown-menu a, .dropdown-menu button {
            display:flex; align-items:center; gap:10px;
            padding:11px 16px; font-size:13px; font-family:'Poppins',sans-serif;
            text-decoration:none; color:#333; background:none; border:none;
            width:100%; text-align:left; cursor:pointer; transition:background 0.2s;
            border-bottom:1px solid #f5f5f5;
        }
        .dropdown-menu a:last-child, .dropdown-menu form:last-child button { border-bottom:none; }
        .dropdown-menu a:hover, .dropdown-menu button:hover { background:#f9f9f9; }
        .dropdown-menu a i, .dropdown-menu button i { color:var(--primary); width:16px; text-align:center; }
        .dropdown-menu .logout-btn { color:#e53e3e; }
        .dropdown-menu .logout-btn i { color:#e53e3e; }

        .page-title { font-size: 28px; font-weight: 700; color: #14283E; line-height: 1.2; }
        .page-subtitle { font-size: 13px; color: var(--gray-500); margin-top: 2px; }

        /* ─── SETTINGS LAYOUT ─── */
        .settings-wrap {
            max-width: 1240px;
            display: grid;
            grid-template-columns: 1.6fr 1fr;
            align-items: start;
            gap: 20px;
        }
        .settings-wrap > .alert-status { grid-column: 1 / -1; }
        .settings-col { display: flex; flex-direction: column; min-width: 0; }

        @media (max-width: 980px) {
            .settings-wrap { grid-template-columns: 1fr; max-width: 760px; }
        }

        .settings-card {
            background: var(--white);
            border-radius: 14px;
            padding: 26px 28px;
            margin-bottom: 20px;
        }

        .settings-card-head {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 6px;
        }
        .settings-card-head .sc-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px;
            background: var(--primary-light);
            color: var(--primary);
            flex-shrink: 0;
        }
        .settings-card-title { font-size: 16px; font-weight: 600; color: var(--gray-900); }
        .settings-card-desc  { font-size: 12.5px; color: var(--gray-500); margin-top: 1px; }

        .settings-divider { height: 1px; background: var(--gray-200); margin: 20px 0; }

        /* ─── SETTING ROW ─── */
        .setting-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 14px 0;
            border-bottom: 1px solid var(--gray-200);
        }
        .setting-row:last-child { border-bottom: none; padding-bottom: 0; }
        .setting-row:first-of-type { padding-top: 0; }

        .setting-info { flex: 1; min-width: 0; }
        .setting-name {
            font-size: 14px; font-weight: 500; color: var(--gray-900);
            display: flex; align-items: center; gap: 9px;
        }
        .setting-name i { color: var(--primary); width: 18px; text-align: center; }
        .setting-hint { font-size: 12px; color: var(--gray-500); margin-top: 3px; padding-left: 27px; }

        /* ─── TOGGLE SWITCH ─── */
        .switch { position: relative; display: inline-block; width: 50px; height: 28px; flex-shrink: 0; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute; cursor: pointer;
            inset: 0;
            background: var(--gray-300);
            border-radius: 28px;
            transition: background 0.25s;
        }
        .slider::before {
            content: '';
            position: absolute;
            height: 22px; width: 22px;
            left: 3px; bottom: 3px;
            background: #fff;
            border-radius: 50%;
            transition: transform 0.25s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        .switch input:checked + .slider { background: var(--primary); }
        .switch input:checked + .slider::before { transform: translateX(22px); }

        /* ─── THEME PREVIEW CHOICES ─── */
        .theme-choices { display: flex; gap: 16px; flex-wrap: wrap; margin-top: 4px; }
        .theme-choice {
            flex: 1; min-width: 190px;
            border: 2px solid var(--gray-300);
            border-radius: 12px;
            padding: 12px;
            cursor: pointer;
            transition: border-color 0.2s, transform 0.15s;
            background: var(--white);
        }
        .theme-choice:hover { transform: translateY(-2px); }
        .theme-choice.selected { border-color: var(--primary); }

        .tc-preview {
            height: 90px;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            margin-bottom: 10px;
            border: 1px solid var(--gray-200);
        }
        .tc-preview .tc-side { width: 34%; }
        .tc-preview .tc-body { flex: 1; padding: 10px; display: flex; flex-direction: column; gap: 6px; }
        .tc-preview .tc-line { height: 8px; border-radius: 4px; }
        .tc-preview .tc-block { flex: 1; border-radius: 6px; }

        /* Light preview */
        .tc-light .tc-side { background: linear-gradient(180deg, #7B1D1D, #5a1515); }
        .tc-light .tc-body { background: #f4f5f7; }
        .tc-light .tc-line { background: #d7d9de; }
        .tc-light .tc-line.short { width: 55%; }
        .tc-light .tc-block { background: #ffffff; }

        /* Dark preview */
        .tc-dark .tc-side { background: linear-gradient(180deg, #7B1D1D, #3a0f0f); }
        .tc-dark .tc-body { background: #121218; }
        .tc-dark .tc-line { background: #3a3a46; }
        .tc-dark .tc-line.short { width: 55%; }
        .tc-dark .tc-block { background: #1e1e26; }

        .tc-label {
            display: flex; align-items: center; justify-content: space-between;
            font-size: 13px; font-weight: 500; color: var(--gray-900);
        }
        .tc-label i { color: var(--primary); font-size: 15px; opacity: 0; transition: opacity 0.2s; }
        .theme-choice.selected .tc-label i { opacity: 1; }

        /* ─── STATIC INFO ROWS ─── */
        .info-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--gray-200); font-size: 13.5px; }
        .info-row:last-child { border-bottom: none; }
        .info-row .k { color: var(--gray-500); }
        .info-row .v { color: var(--gray-900); font-weight: 500; }

        /* ─── ACCOUNT PROFILE HEADER ─── */
        .account-head {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .account-avatar {
            width: 64px; height: 64px;
            border-radius: 16px;
            background: var(--primary);
            color: #fff;
            font-weight: 700;
            font-size: 22px;
            display: flex; align-items: center; justify-content: center;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(123,29,29,0.25);
        }
        .account-avatar img {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            object-fit: cover;
            border-radius: inherit;
        }
        .account-info { flex: 1; min-width: 160px; }
        .account-name { font-size: 17px; font-weight: 700; color: var(--gray-900); line-height: 1.3; }
        .account-email { font-size: 12.5px; color: var(--gray-500); margin-top: 2px; }
        .role-badge {
            display: inline-flex; align-items: center; gap: 5px;
            margin-top: 8px;
            padding: 3px 10px;
            border-radius: 20px;
            background: var(--primary-light);
            color: var(--primary);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.2px;
        }
        .role-badge i { font-size: 9px; }

        /* ─── ACCOUNT DETAIL GRID ─── */
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
        }
        .detail-box {
            background: var(--gray-100);
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            padding: 12px 14px;
        }
        .detail-box .dk {
            display: flex; align-items: center; gap: 6px;
            font-size: 11px; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.4px; color: var(--gray-500);
            margin-bottom: 4px;
        }
        .detail-box .dk i { font-size: 10px; color: var(--primary); }
        .detail-box .dv { font-size: 14.5px; font-weight: 600; color: var(--gray-900); }

        .save-hint {
            display: inline-flex; align-items: center; gap: 7px;
            font-size: 12.5px; color: var(--green); font-weight: 500;
            opacity: 0; transition: opacity 0.3s;
        }
        .save-hint.show { opacity: 1; }

        .alert-status {
            display: flex; align-items: center; gap: 10px;
            background: #ecfdf5; color: #047857;
            padding: 12px 18px; border-radius: 10px;
            font-size: 13px; margin-bottom: 18px;
        }

        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-outline { background:white; color:var(--primary); border:1.5px solid var(--primary); }
        .btn-outline:hover { background:var(--primary-light); }

        .form-field { margin-bottom: 16px; }
        .form-field label { display: block; font-size: 12.5px; font-weight: 500; color: #555; margin-bottom: 6px; }
        .form-field input {
            width: 100%; max-width: 320px; padding: 10px 12px;
            border: 1px solid var(--gray-300); border-radius: 8px;
            font-size: 13.5px; font-family: 'Poppins', sans-serif; color: #1a1a1a; outline: none;
        }
        .form-field input:focus { border-color: var(--primary); }
        .form-errors { color: #b91c1c; font-size: 12px; margin-top: 4px; }

        @media (max-width: 768px) {
            .page-title { font-size: 22px; }
            .settings-card { padding: 20px 18px; }
            .theme-choice { min-width: 100%; }
            .top-bar { flex-direction:column; align-items:flex-start; gap:12px; }
            .top-bar-right { width:100%; flex-wrap:wrap; }
            .search-wrap { flex:1; }
            .search-wrap input { width:100%; }
        }
    </style>
</head>
<body>

@include('partials.sidebar', ['active' => 'settings'])
@include('partials.student-bottom-nav', ['active' => 'settings'])
@include('partials.student-mobile-header')

<main class="main-content">

    <!-- HEADER -->
    <div class="top-bar">
        <div class="top-bar-left">
            <div>
                <div class="page-title">Settings</div>
                <div class="page-subtitle">Manage how CPACE looks and behaves for you.</div>
            </div>
        </div>
        <div class="top-bar-right">
            <div class="search-wrap gs-wrap">
                <i class="fas fa-search"></i>
                <input type="text" data-gs="true" placeholder="Search topics, questions...">
            </div>
            <a class="notif-btn" href="{{ route('messages.index') }}" title="Messages" aria-label="Messages">
                <i class="fas fa-comment-dots"></i>
                @if($unreadMessages > 0)<span class="badge">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>@endif
            </a>
            <a class="notif-btn" href="{{ route('notifications.index') }}" title="Notifications" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                @if($unreadNotifications > 0)<span class="badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>@endif
            </a>
            <div class="header-dropdown-wrap">
                <button class="profile-avatar" id="profileBtn">@include('partials.avatar-content')</button>
                <div class="dropdown-menu" id="profileDropdown">
                    <a href="#" class="js-open-profile-modal"><i class="fas fa-user"></i> Profile Settings</a>
                    <a href="#"><i class="fas fa-chart-line"></i> My Progress</a>
                    <a href="#"><i class="fas fa-question-circle"></i> Help &amp; Support</a>
                    <form method="POST" action="{{ route('logout') }}"
                          data-confirm="You will be signed out of CPACE and returned to the login page."
                          data-confirm-title="Log out of CPACE?"
                          data-confirm-ok="Yes, log me out"
                          data-confirm-icon="question" style="margin:0;padding:0;">
                        @csrf
                        <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <span class="save-hint" id="saveHint"><i class="fas fa-check-circle"></i> Saved</span>

    <div class="settings-wrap">

        @if (session('status'))
            <div class="alert-status"><i class="fas fa-check-circle"></i> {{ session('status') }}</div>
        @endif

        <div class="settings-col">

        <!-- APPEARANCE -->
        <div class="settings-card">
            <div class="settings-card-head">
                <div class="sc-icon"><i class="fas fa-palette"></i></div>
                <div>
                    <div class="settings-card-title">Appearance</div>
                    <div class="settings-card-desc">Customize the look and feel of your reviewer.</div>
                </div>
            </div>

            <div class="settings-divider"></div>

            <!-- Quick toggle -->
            <div class="setting-row">
                <div class="setting-info">
                    <div class="setting-name"><i class="fas fa-moon"></i> Dark Mode</div>
                    <div class="setting-hint">Switch the entire student area to a darker, eye-friendly theme.</div>
                </div>
                <label class="switch">
                    <input type="checkbox" id="darkToggle">
                    <span class="slider"></span>
                </label>
            </div>

            <!-- Theme picker -->
            <div class="setting-row" style="flex-direction:column; align-items:stretch;">
                <div class="setting-info" style="margin-bottom:12px;">
                    <div class="setting-name"><i class="fas fa-swatchbook"></i> Theme</div>
                    <div class="setting-hint">Pick your preferred theme. Your choice is remembered on this device.</div>
                </div>
                <div class="theme-choices">
                    <div class="theme-choice" data-theme="light" id="choiceLight">
                        <div class="tc-preview tc-light">
                            <div class="tc-side"></div>
                            <div class="tc-body">
                                <div class="tc-line short"></div>
                                <div class="tc-block"></div>
                            </div>
                        </div>
                        <div class="tc-label"><span><i class="fas fa-sun" style="opacity:1;color:var(--gray-700);margin-right:6px;"></i>Light</span> <i class="fas fa-check-circle"></i></div>
                    </div>
                    <div class="theme-choice" data-theme="dark" id="choiceDark">
                        <div class="tc-preview tc-dark">
                            <div class="tc-side"></div>
                            <div class="tc-body">
                                <div class="tc-line short"></div>
                                <div class="tc-block"></div>
                            </div>
                        </div>
                        <div class="tc-label"><span><i class="fas fa-moon" style="opacity:1;color:var(--gray-700);margin-right:6px;"></i>Dark</span> <i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACCOUNT -->
        <div class="settings-card">
            <div class="settings-card-head">
                <div class="sc-icon"><i class="fas fa-user"></i></div>
                <div>
                    <div class="settings-card-title">Account</div>
                    <div class="settings-card-desc">Your reviewer account details.</div>
                </div>
            </div>

            <div class="settings-divider"></div>

            <div class="account-head">
                <div class="account-avatar">@include('partials.avatar-content')</div>
                <div class="account-info">
                    <div class="account-name">{{ Auth::user()->name }}</div>
                    <div class="account-email">{{ Auth::user()->email }}</div>
                    <span class="role-badge"><i class="fas fa-circle"></i> {{ ($isAlumniStudent ?? false) ? 'Alumni' : 'Student Reviewer' }}</span>
                </div>
                <button type="button" class="btn btn-outline js-open-profile-modal"><i class="fas fa-pen"></i> Edit</button>
            </div>

            @if($profile?->student_number || $profile?->year_level || $profile?->section)
                <div class="settings-divider"></div>

                <div class="detail-grid">
                    @if($profile?->student_number)
                        <div class="detail-box">
                            <div class="dk"><i class="fas fa-id-badge"></i> Student Number</div>
                            <div class="dv">{{ $profile->student_number }}</div>
                        </div>
                    @endif
                    @if($profile?->year_level)
                        <div class="detail-box">
                            <div class="dk"><i class="fas fa-layer-group"></i> Year Level</div>
                            <div class="dv">{{ $profile->year_level }}</div>
                        </div>
                    @endif
                    @if($profile?->section)
                        <div class="detail-box">
                            <div class="dk"><i class="fas fa-users"></i> Section</div>
                            <div class="dv">{{ $profile->section }}</div>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        </div>

        <div class="settings-col">

        <!-- CONTACT DETAILS -->
        <div class="settings-card">
            <div class="settings-card-head">
                <div class="sc-icon"><i class="fas fa-id-card"></i></div>
                <div>
                    <div class="settings-card-title">Contact Details</div>
                    <div class="settings-card-desc">Used to reach you and plan your review timeline.</div>
                </div>
            </div>

            <div class="settings-divider"></div>

            <form method="POST" action="{{ route('settings.details') }}">
                @csrf
                <div class="form-field">
                    <label>Mobile Number</label>
                    <input type="text" name="mobile" value="{{ old('mobile', $profile->mobile ?? '') }}" placeholder="e.g. 09171234567">
                    @error('mobile') <div class="form-errors">{{ $message }}</div> @enderror
                </div>
                <div class="form-field">
                    <label>Target Exam Date</label>
                    <input type="date" name="exam_target_date" value="{{ old('exam_target_date', optional($profile?->exam_target_date)->format('Y-m-d')) }}">
                    @error('exam_target_date') <div class="form-errors">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Details</button>
            </form>
        </div>

        <!-- PASSWORD -->
        <div class="settings-card">
            <div class="settings-card-head">
                <div class="sc-icon"><i class="fas fa-lock"></i></div>
                <div>
                    <div class="settings-card-title">Password</div>
                    <div class="settings-card-desc">Change the password used to sign in to CPACE.</div>
                </div>
            </div>

            <div class="settings-divider"></div>

            <form method="POST" action="{{ route('settings.password') }}">
                @csrf
                <div class="form-field">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                    @error('current_password') <div class="form-errors">{{ $message }}</div> @enderror
                </div>
                <div class="form-field">
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                    @error('new_password') <div class="form-errors">{{ $message }}</div> @enderror
                </div>
                <div class="form-field">
                    <label>Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Change Password</button>
            </form>
        </div>

        </div>

    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar && localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
    }

    const profileBtn = document.getElementById('profileBtn');
    const profileDrop = document.getElementById('profileDropdown');
    if (profileBtn && profileDrop) {
        profileBtn.addEventListener('click', e => {
            e.stopPropagation();
            profileDrop.classList.toggle('active');
        });
        document.addEventListener('click', () => profileDrop.classList.remove('active'));
        profileDrop.addEventListener('click', e => e.stopPropagation());
    }

    const toggle      = document.getElementById('darkToggle');
    const choiceLight = document.getElementById('choiceLight');
    const choiceDark  = document.getElementById('choiceDark');
    const saveHint    = document.getElementById('saveHint');

    function reflect(theme) {
        const dark = theme === 'dark';
        toggle.checked = dark;
        choiceDark.classList.toggle('selected', dark);
        choiceLight.classList.toggle('selected', !dark);
    }

    function flashSaved() {
        saveHint.classList.add('show');
        clearTimeout(flashSaved._t);
        flashSaved._t = setTimeout(() => saveHint.classList.remove('show'), 1500);
    }

    function apply(theme) {
        window.CPACE.setTheme(theme);   // defined in partials/student-theme
        reflect(theme);
        flashSaved();
    }

    // Initialize from saved preference
    reflect(window.CPACE.getTheme());

    toggle.addEventListener('change', () => apply(toggle.checked ? 'dark' : 'light'));
    choiceLight.addEventListener('click', () => apply('light'));
    choiceDark.addEventListener('click', () => apply('dark'));

    // Keep the UI in sync if the theme is changed elsewhere
    window.addEventListener('cpace:themechange', e => reflect(e.detail.theme));
});
</script>
@include('partials.global-search')

    @include('partials.alerts')
</body>
</html>
