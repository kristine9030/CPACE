<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $editMode ? 'Edit Faculty' : 'Add Faculty' }} - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'faculty'])

@php
    $assigned = $assigned ?? [];
    $old = fn($key, $fallback = '') => old($key, $fallback);
@endphp

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <button class="toggle-btn" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <div>
                <div class="page-title">{{ $editMode ? 'Edit Faculty Account' : 'Add Faculty Account' }}</div>
                <div class="page-sub">{{ $editMode ? 'Update details, reset password, and reassign subjects.' : 'Create a login and assign CPALE subjects to a faculty member.' }}</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('chair.faculty') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <i class="fas fa-circle-exclamation"></i>
            <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ $editMode ? route('chair.faculty.update', $faculty->id) : route('chair.faculty.store') }}">
        @csrf
        @if ($editMode) @method('PUT') @endif

        <div class="card">
            <div class="card-head"><span class="card-title"><i class="fas fa-id-card" style="color:var(--primary);"></i> Account Details</span></div>
            <div class="form-grid">
                <div class="form-group">
                    <label>First Name <span style="color:var(--accent)">*</span></label>
                    <input type="text" name="first_name" value="{{ $old('first_name', $editMode ? $faculty->first_name : '') }}" required>
                </div>
                <div class="form-group">
                    <label>Last Name <span style="color:var(--accent)">*</span></label>
                    <input type="text" name="last_name" value="{{ $old('last_name', $editMode ? $faculty->last_name : '') }}" required>
                </div>
                <div class="form-group">
                    <label>Email (login) <span style="color:var(--accent)">*</span></label>
                    <input type="email" name="email" value="{{ $old('email', $editMode ? $faculty->email : '') }}" required>
                </div>
                <div class="form-group">
                    <label>Employee Number</label>
                    <input type="text" name="employee_number" value="{{ $old('employee_number', $editMode ? optional($faculty->facultyProfile)->employee_number : '') }}" placeholder="e.g. EMP-2026-001">
                </div>
                <div class="form-group full">
                    <label>Department</label>
                    <input type="text" name="department" value="{{ $old('department', $editMode ? optional($faculty->facultyProfile)->department : 'College of Accountancy') }}">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><span class="card-title"><i class="fas fa-key" style="color:var(--primary);"></i> {{ $editMode ? 'Reset Password' : 'Set Password' }}</span></div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Password {{ $editMode ? '' : '*' }}</label>
                    <input type="password" name="password" {{ $editMode ? '' : 'required' }} autocomplete="new-password">
                    @if ($editMode)<div class="hint">Leave blank to keep the current password.</div>@endif
                </div>
                <div class="form-group">
                    <label>Confirm Password {{ $editMode ? '' : '*' }}</label>
                    <input type="password" name="password_confirmation" {{ $editMode ? '' : 'required' }} autocomplete="new-password">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><span class="card-title"><i class="fas fa-layer-group" style="color:var(--primary);"></i> Assigned Subjects</span></div>
            <div class="check-grid">
                @foreach ($subjects as $s)
                    @php $checked = collect(old('subjects', $assigned))->contains($s->id); @endphp
                    <label class="check-card">
                        <input type="checkbox" name="subjects[]" value="{{ $s->id }}" {{ $checked ? 'checked' : '' }}>
                        <span>
                            <span class="cc-code">{{ $s->code }}</span><br>
                            <span class="cc-name">{{ $s->name }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        @if ($editMode)
            <div class="card">
                <label class="check-card" style="max-width:280px;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $faculty->is_active) ? 'checked' : '' }}>
                    <span><span class="cc-name" style="font-weight:600; color:#444;">Account is active (can log in)</span></span>
                </label>
            </div>
        @endif

        <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:6px;">
            <a href="{{ route('chair.faculty') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-{{ $editMode ? 'save' : 'user-plus' }}"></i> {{ $editMode ? 'Save Changes' : 'Create Account' }}</button>
        </div>
    </form>
</main>
</body>
</html>
