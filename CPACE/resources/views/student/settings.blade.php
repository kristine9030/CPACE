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
            --white: #ffffff;
            --gray-100: #f8f9fa;
            --gray-200: #f0f0f0;
            --gray-300: #e0e0e0;
            --gray-500: #999999;
            --gray-700: #555555;
            --gray-900: #333333;
            --green: #10b981;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f5f7;
            color: var(--gray-900);
        }

        /* ─── HEADER ─── */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            gap: 20px;
        }
        .page-title { font-size: 28px; font-weight: 700; color: var(--gray-900); line-height: 1.2; }
        .page-subtitle { font-size: 13px; color: var(--gray-500); margin-top: 2px; }

        /* ─── SETTINGS LAYOUT ─── */
        .settings-wrap { max-width: 760px; }

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

        .save-hint {
            display: inline-flex; align-items: center; gap: 7px;
            font-size: 12.5px; color: var(--green); font-weight: 500;
            opacity: 0; transition: opacity 0.3s;
        }
        .save-hint.show { opacity: 1; }

        @media (max-width: 768px) {
            .page-title { font-size: 22px; }
            .settings-card { padding: 20px 18px; }
            .theme-choice { min-width: 100%; }
        }
    </style>
</head>
<body>

@include('partials.sidebar', ['active' => 'settings'])
@include('partials.student-bottom-nav', ['active' => 'settings'])
@include('partials.student-mobile-header')

<main class="main-content">

    <!-- HEADER -->
    <div class="page-header">
        <div>
            <div class="page-title">Settings</div>
            <div class="page-subtitle">Manage how CPACE looks and behaves for you.</div>
        </div>
        <span class="save-hint" id="saveHint"><i class="fas fa-check-circle"></i> Saved</span>
    </div>

    <div class="settings-wrap">

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

            <div class="info-row"><span class="k">Name</span><span class="v">{{ Auth::user()->name }}</span></div>
            <div class="info-row"><span class="k">Email</span><span class="v">{{ Auth::user()->email }}</span></div>
            <div class="info-row"><span class="k">Role</span><span class="v">Student Reviewer</span></div>
        </div>

    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
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
</body>
</html>
