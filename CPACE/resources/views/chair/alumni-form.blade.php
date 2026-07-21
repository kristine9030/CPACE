<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $editMode ? 'Edit Alumni' : 'Add Alumni' }} - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr !important; } }
        @media (max-width: 480px) {
            .form-submit-row { flex-direction: column-reverse; gap: 8px; }
            .form-submit-row .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'alumni'])

@php $old = fn($key, $fallback = '') => old($key, $fallback); @endphp

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">{{ $editMode ? 'Edit Alumni Account' : 'Add Alumni Account' }}</div>
                <div class="page-sub">{{ $editMode ? 'Update details or reset the password.' : 'Create a login so an alumnus can post in the Community feed.' }}</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('chair.alumni') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
            @include('partials.topbar-actions')
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <i class="fas fa-circle-exclamation"></i>
            <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ $editMode ? route('chair.alumni.update', $alumnus->id) : route('chair.alumni.store') }}">
        @csrf
        @if ($editMode) @method('PUT') @endif

        <div class="card">
            <div class="card-head"><span class="card-title"><i class="fas fa-id-card" style="color:var(--primary);"></i> Account Details</span></div>
            <div class="form-grid">
                <div class="form-group">
                    <label>First Name <span style="color:var(--accent)">*</span></label>
                    <input type="text" name="first_name" value="{{ $old('first_name', $editMode ? $alumnus->first_name : '') }}" required>
                </div>
                <div class="form-group">
                    <label>Last Name <span style="color:var(--accent)">*</span></label>
                    <input type="text" name="last_name" value="{{ $old('last_name', $editMode ? $alumnus->last_name : '') }}" required>
                </div>
                <div class="form-group">
                    <label>Email (login) <span style="color:var(--accent)">*</span></label>
                    <input type="email" name="email" value="{{ $old('email', $editMode ? $alumnus->email : '') }}" required>
                </div>
                <div class="form-group">
                    <label>Batch Year</label>
                    <input type="text" name="batch_year" value="{{ $old('batch_year', $editMode ? optional($alumnus->alumniProfile)->batch_year : '') }}" placeholder="e.g. 2021">
                </div>
                <div class="form-group">
                    <label>Current Job Title</label>
                    <input type="text" name="current_job" value="{{ $old('current_job', $editMode ? optional($alumnus->alumniProfile)->current_job : '') }}" placeholder="e.g. Senior Auditor">
                </div>
                <div class="form-group">
                    <label>Company</label>
                    <input type="text" name="company" value="{{ $old('company', $editMode ? optional($alumnus->alumniProfile)->company : '') }}" placeholder="e.g. SGV & Co.">
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

        @if ($editMode)
            <div class="card">
                <label class="check-card" style="max-width:280px;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $alumnus->is_active) ? 'checked' : '' }}>
                    <span><span class="cc-name" style="font-weight:600; color:#444;">Account is active (can log in)</span></span>
                </label>
            </div>
        @endif

        <div class="form-submit-row" style="display:flex; gap:10px; justify-content:flex-end; margin-top:6px;">
            <a href="{{ route('chair.alumni') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-{{ $editMode ? 'save' : 'user-plus' }}"></i> {{ $editMode ? 'Save Changes' : 'Create Account' }}</button>
        </div>
    </form>
</main>
</body>
</html>
