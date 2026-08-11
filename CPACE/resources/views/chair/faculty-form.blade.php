<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $editMode ? 'Edit Faculty' : 'Add Faculty' }} - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── Faculty-form responsive ── */
        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr !important; }
            .form-grid .form-group.full { grid-column: 1 !important; }
            .check-grid { grid-template-columns: 1fr !important; }
        }
        @media (max-width: 480px) {
            .form-submit-row { flex-direction: column-reverse; gap: 8px; }
            .form-submit-row .btn { width: 100%; justify-content: center; }
        }
    </style>
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
            <div>
                <div class="page-title">{{ $editMode ? 'Edit Faculty Account' : 'Add Faculty Account' }}</div>
                <div class="page-sub">{{ $editMode ? 'Update details, reset password, and reassign subjects.' : 'Create a login and assign CPALE subjects to a faculty member.' }}</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('chair.faculty') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
            @include('partials.topbar-actions')
        </div>
    </div>

    {{-- Validation errors surface as a SweetAlert popup via partials.alerts --}}

    <form method="POST" action="{{ $editMode ? route('chair.faculty.update', $faculty->id) : route('chair.faculty.store') }}"
          data-confirm="{{ $editMode
              ? 'The changes you made to this faculty account will be saved.'
              : 'A faculty account will be created and issued a one-time password for their first sign-in.' }}"
          data-confirm-title="{{ $editMode ? 'Save changes?' : 'Create this faculty account?' }}"
          data-confirm-ok="{{ $editMode ? 'Yes, save changes' : 'Yes, create account' }}"
          data-confirm-icon="question">
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
                    <input type="email" id="facultyEmail" name="email" value="{{ $old('email', $editMode ? $faculty->email : '') }}" autocomplete="off" required>
                    <div id="emailWarning" style="display:none; margin-top:6px; font-size:12px; border-radius:8px; padding:8px 11px;"></div>
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

        @unless ($editMode)
            <div class="card">
                <div class="hint"><i class="fas fa-circle-info"></i> A one-time password will be generated automatically and emailed directly to the faculty member — it is never shown to you.</div>
            </div>
        @endunless

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

        <div class="form-submit-row" style="display:flex; gap:10px; justify-content:flex-end; margin-top:6px;">
            <a href="{{ route('chair.faculty') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-{{ $editMode ? 'save' : 'user-plus' }}"></i> {{ $editMode ? 'Save Changes' : 'Create Account' }}</button>
        </div>
    </form>
</main>

    <script>
        (function () {
            var emailInput = document.getElementById('facultyEmail');
            var warning = document.getElementById('emailWarning');
            var currentUserId = {{ $editMode ? $faculty->id : 'null' }};
            var debounceTimer = null;

            function checkEmail() {
                var email = emailInput.value.trim();
                warning.style.display = 'none';
                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return;

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    var url = '{{ route('chair.check-email') }}?email=' + encodeURIComponent(email)
                        + (currentUserId ? '&exclude_id=' + currentUserId : '');
                    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.taken) {
                                warning.style.display = 'block';
                                warning.style.background = '#fef2f2';
                                warning.style.border = '1px solid #fecaca';
                                warning.style.color = '#b91c1c';
                                warning.innerHTML = '<i class="fas fa-triangle-exclamation"></i> This email is already registered to another account.';
                            } else if (data.valid_format && data.mx_ok === false) {
                                warning.style.display = 'block';
                                warning.style.background = '#fffbeb';
                                warning.style.border = '1px solid #fde68a';
                                warning.style.color = '#92400e';
                                warning.innerHTML = '<i class="fas fa-circle-exclamation"></i> This domain doesn\'t appear to accept email — double-check the address.';
                            }
                        })
                        .catch(function () {});
                }, 400);
            }

            emailInput.addEventListener('input', checkEmail);
            emailInput.addEventListener('blur', checkEmail);
        })();
    </script>

    @include('partials.alerts')
</body>
</html>
