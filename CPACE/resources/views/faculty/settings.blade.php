<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #7B1D1D;
            --primary-hover: #6a1818;
            --primary-light: #f5e8e8;
            --accent: #c0392b;
            --green: #10b981;
            --gray-100: #f8f9fa;
            --gray-200: #f0f0f0;
            --gray-300: #e0e0e0;
            --gray-500: #999999;
            --gray-900: #333333;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

        .main { margin-left:230px; padding:26px 30px; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }

        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; gap:16px; position:relative; z-index:50; }
        .topbar-left { display:flex; align-items:center; gap:12px; }
        .page-title { font-size:26px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .topbar-right { display:flex; align-items:center; gap:12px; }

        .settings-wrap { max-width: 760px; }

        .alert-status {
            display: flex; align-items: center; gap: 10px;
            background: #ecfdf5; color: #047857;
            padding: 12px 18px; border-radius: 10px;
            font-size: 13px; margin-bottom: 18px;
        }

        .settings-card { background: #fff; border-radius: 14px; padding: 26px 28px; margin-bottom: 20px; }
        .settings-card-head { display: flex; align-items: center; gap: 12px; margin-bottom: 6px; }
        .settings-card-head .sc-icon {
            width: 40px; height: 40px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px; background: var(--primary-light); color: var(--primary); flex-shrink: 0;
        }
        .settings-card-title { font-size: 16px; font-weight: 600; color: var(--gray-900); }
        .settings-card-desc  { font-size: 12.5px; color: var(--gray-500); margin-top: 1px; }
        .settings-divider { height: 1px; background: var(--gray-200); margin: 20px 0; }

        .info-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--gray-200); font-size: 13.5px; }
        .info-row:last-child { border-bottom: none; }
        .info-row .k { color: var(--gray-500); }
        .info-row .v { color: var(--gray-900); font-weight: 500; }

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
        }
    </style>
</head>
<body>

@include('partials.faculty-sidebar', ['active' => 'settings'])

<main class="main">

    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Settings</div>
                <div class="page-sub">Manage your account and profile details.</div>
            </div>
        </div>
        <div class="topbar-right">
            @include('partials.topbar-actions')
        </div>
    </div>

    <div class="settings-wrap">

        @if (session('status'))
            <div class="alert-status"><i class="fas fa-check-circle"></i> {{ session('status') }}</div>
        @endif

        <!-- ACCOUNT -->
        <div class="settings-card">
            <div class="settings-card-head">
                <div class="sc-icon"><i class="fas fa-user"></i></div>
                <div>
                    <div class="settings-card-title">Account</div>
                    <div class="settings-card-desc">Your CPACE account details.</div>
                </div>
            </div>

            <div class="settings-divider"></div>

            <div class="info-row"><span class="k">Name</span><span class="v">{{ Auth::user()->name }}</span></div>
            <div class="info-row"><span class="k">Email</span><span class="v">{{ Auth::user()->email }}</span></div>
            <div class="info-row"><span class="k">Role</span><span class="v">Faculty</span></div>

            <div class="settings-divider"></div>

            <button type="button" class="btn btn-outline" id="settingsEditProfileBtn"><i class="fas fa-pen"></i> Edit name, email &amp; photo</button>
        </div>

        <!-- EMPLOYEE DETAILS -->
        <div class="settings-card">
            <div class="settings-card-head">
                <div class="sc-icon"><i class="fas fa-id-badge"></i></div>
                <div>
                    <div class="settings-card-title">Employee Details</div>
                    <div class="settings-card-desc">Details used for identification within the department.</div>
                </div>
            </div>

            <div class="settings-divider"></div>

            <form method="POST" action="{{ route('faculty.settings.details') }}">
                @csrf
                <div class="form-field">
                    <label>Employee Number</label>
                    <input type="text" name="employee_number" value="{{ old('employee_number', $profile->employee_number ?? '') }}" placeholder="e.g. 2024-0123">
                    @error('employee_number') <div class="form-errors">{{ $message }}</div> @enderror
                </div>
                <div class="form-field">
                    <label>Department</label>
                    <input type="text" name="department" value="{{ old('department', $profile->department ?? '') }}" placeholder="e.g. Department of Accountancy">
                    @error('department') <div class="form-errors">{{ $message }}</div> @enderror
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

            <form method="POST" action="{{ route('faculty.settings.password') }}">
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

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editBtn = document.getElementById('settingsEditProfileBtn');
    const topbarLink = document.getElementById('topbarProfileLink');
    if (editBtn && topbarLink) {
        editBtn.addEventListener('click', function () { topbarLink.click(); });
    }
});
</script>
@include('partials.global-search')
</body>
</html>
