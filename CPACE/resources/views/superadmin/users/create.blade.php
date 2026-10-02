<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin · Add Account · CPACE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @include('partials.superadmin-sidebar', ['active' => 'users'])
</head>
<body>
    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <div>
                    <div class="page-title">Add Account</div>
                    <div class="page-sub">Provision any role. A one-time password is emailed to the new account.</div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('superadmin.users') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back to Users</a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-triangle-exclamation"></i>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card" style="max-width:640px;">
            <form method="POST" action="{{ route('superadmin.users.store') }}">
                @csrf

                <div class="form-group full">
                    <label>Role</label>
                    <div class="role-picker">
                        @foreach ($roleLabels as $id => $label)
                            <label class="role-opt">
                                <input type="radio" name="role_id" value="{{ $id }}" @checked(old('role_id') == $id) required>
                                <span>
                                    <i class="fas {{ match($id){2=>'fa-user-graduate',3=>'fa-chalkboard-user',1=>'fa-user-tie',4=>'fa-user-graduate',5=>'fa-user-shield',default=>'fa-user'} }}"></i>
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required>
                    </div>
                    <div class="form-group full">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Employee Number <span style="font-weight:400;color:#aaa;">(faculty only)</span></label>
                        <input type="text" name="employee_number" value="{{ old('employee_number') }}">
                    </div>
                    <div class="form-group">
                        <label>Department <span style="font-weight:400;color:#aaa;">(faculty only)</span></label>
                        <input type="text" name="department" value="{{ old('department') }}" placeholder="College of Accountancy">
                    </div>
                </div>

                <div class="hint" style="margin-bottom:16px;">
                    <i class="fas fa-circle-info"></i> A generated one-time password is emailed to this address. The account is active immediately; the person changes their password on first login.
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Create Account</button>
            </form>
        </div>
    </main>
</body>
</html>
