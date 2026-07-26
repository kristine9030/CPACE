<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $editMode ? 'Edit' : 'Enroll' }} Student - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-shell {
            max-width: 850px;
            margin: 0 auto;
        }
        .form-section { margin-bottom: 18px; }
        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 14px;
            padding-bottom: 9px;
            border-bottom: 1px solid #f1f1f1;
        }
        .section-title i {
            color: var(--primary);
            margin-right: 7px;
        }
        .form-grid.three { grid-template-columns: repeat(3, 1fr); }
        input[type=date],
        input[type=number] {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid #e2e2e6;
            border-radius: 8px;
            font: 13px 'Poppins', sans-serif;
        }
        .toggle-row {
            display: flex;
            align-items: center;
            gap: 9px;
            grid-column: span 2;
            background: #f8f8fa;
            border-radius: 9px;
            padding: 12px;
        }
        .toggle-row input {
            width: 17px;
            height: 17px;
            accent-color: var(--primary);
        }
        .toggle-row label { margin: 0; }
        .status-toggle {
            border: 1.5px solid #e2e2e6;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 12px;
        }
        .status-toggle.is-checked { border-color: var(--primary); background: #fff8f6; }
        .status-toggle .toggle-row { background: transparent; padding: 0; }
        .status-toggle .toggle-hint {
            margin: 6px 0 0 26px;
            font-size: 12px;
            color: #888;
        }
        .status-toggle .toggle-fields {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #f1f1f1;
            display: none;
        }
        .status-toggle.is-checked .toggle-fields { display: grid; }
        .status-toggle textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid #e2e2e6;
            border-radius: 8px;
            font: 13px 'Poppins', sans-serif;
            resize: vertical;
            min-height: 70px;
        }
        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 9px;
        }
        @media (max-width: 700px) {
            .form-grid,
            .form-grid.three { grid-template-columns: 1fr !important; }
            .toggle-row { grid-column: auto; }
        }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'students'])

@php
    $profile = $editMode ? $student->studentProfile : null;
    $backUrl = $editMode
        ? route('chair.students.show', $student)
        : route('chair.students');
@endphp

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">
                    {{ $editMode ? 'Edit Student Account' : 'Enroll Student' }}
                </div>
                <div class="page-sub">
                    {{ $editMode
                        ? 'Update enrollment, grouping, and login details.'
                        : 'Create an individual student login and enrollment profile.' }}
                </div>
            </div>
        </div>
        <div class="topbar-right">
            <a class="btn btn-outline" href="{{ $backUrl }}">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            @include('partials.topbar-actions')
        </div>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="alert alert-error">
            <i class="fas fa-circle-exclamation"></i>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        class="form-shell"
        method="POST"
        action="{{ $editMode ? route('chair.students.update', $student) : route('chair.students.store') }}"
    >
        @csrf
        @if ($editMode)
            @method('PUT')
        @endif

        <!-- Personal and login details -->
        <div class="card form-section">
            <div class="section-title">
                <i class="fas fa-user"></i> Personal &amp; Login Details
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>First Name</label>
                    <input
                        type="text"
                        name="first_name"
                        value="{{ old('first_name', $editMode ? $student->first_name : '') }}"
                        required
                    >
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input
                        type="text"
                        name="last_name"
                        value="{{ old('last_name', $editMode ? $student->last_name : '') }}"
                        required
                    >
                </div>
                <div class="form-group full">
                    <label>Email Address</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $editMode ? $student->email : '') }}"
                        required
                    >
                </div>
                <div class="form-group">
                    <label>
                        Password {{ $editMode ? '(leave blank to keep current)' : '' }}
                    </label>
                    <input type="password" name="password" {{ $editMode ? '' : 'required' }}>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="password_confirmation" {{ $editMode ? '' : 'required' }}>
                </div>
            </div>
        </div>

        <!-- Enrollment and grouping -->
        <div class="card form-section">
            <div class="section-title">
                <i class="fas fa-graduation-cap"></i> Enrollment &amp; Grouping
            </div>
            <div class="form-grid three">
                <div class="form-group">
                    <label>Student Number</label>
                    <input
                        type="text"
                        name="student_number"
                        value="{{ old('student_number', $profile?->student_number) }}"
                        placeholder="e.g. 2026-0001"
                    >
                </div>
                <div class="form-group">
                    <label>Year Level</label>
                    <select name="year_level">
                        <option value="">Not specified</option>
                        @for ($year = 1; $year <= 6; $year++)
                            <option
                                value="{{ $year }}"
                                @selected((string) old('year_level', $profile?->year_level) === (string) $year)
                            >
                                Year {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="form-group">
                    <label>Section / Batch</label>
                    <input
                        type="text"
                        name="section"
                        value="{{ old('section', $profile?->section) }}"
                        placeholder="e.g. BSA-4A"
                    >
                </div>
                <div class="form-group">
                    <label>Target Exam Date</label>
                    <input
                        type="date"
                        name="exam_target_date"
                        value="{{ old('exam_target_date', $profile?->exam_target_date?->format('Y-m-d')) }}"
                    >
                </div>
                <div class="toggle-row">
                    <input type="hidden" name="is_active" value="0">
                    <input
                        type="checkbox"
                        id="isActive"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', $editMode ? $student->is_active : true))
                    >
                    <label for="isActive">Account enabled and allowed to sign in</label>
                </div>
            </div>
        </div>

        @if ($editMode)
            @php
                $alumniProfile = $student->alumniProfile;
                $studentProfile = $student->studentProfile;
                $isAlumniChecked = old('is_alumni', $studentProfile?->is_alumni) ? true : false;
                $isShiftedChecked = old('is_shifted', $studentProfile?->is_shifted) ? true : false;
            @endphp
            <!-- Alumni & program status -->
            <div class="card form-section">
                <div class="section-title">
                    <i class="fas fa-user-graduate"></i> Alumni &amp; Program Status
                </div>

                <div class="status-toggle {{ $isAlumniChecked ? 'is-checked' : '' }}" id="alumniToggleWrap">
                    <div class="toggle-row">
                        <input type="hidden" name="is_alumni" value="0">
                        <input type="checkbox" id="isAlumni" name="is_alumni" value="1" @checked($isAlumniChecked)>
                        <label for="isAlumni">Mark as Alumni (graduated)</label>
                    </div>
                    <div class="toggle-hint">
                        Keeps this same login — the student still signs in as before, but gains access to the
                        Resource Library (to share materials with current students) and Mock Exams becomes locked.
                    </div>
                    <div class="form-grid three toggle-fields">
                        <div class="form-group">
                            <label>Batch Year</label>
                            <input type="number" name="batch_year" min="1980" max="2100"
                                value="{{ old('batch_year', $alumniProfile?->batch_year) }}" placeholder="e.g. 2026">
                        </div>
                        <div class="form-group">
                            <label>Current Job</label>
                            <input type="text" name="current_job"
                                value="{{ old('current_job', $alumniProfile?->current_job) }}" placeholder="e.g. Senior Auditor">
                        </div>
                        <div class="form-group">
                            <label>Company</label>
                            <input type="text" name="company"
                                value="{{ old('company', $alumniProfile?->company) }}" placeholder="e.g. SGV & Co.">
                        </div>
                    </div>
                </div>

                <div class="status-toggle {{ $isShiftedChecked ? 'is-checked' : '' }}" id="shiftedToggleWrap">
                    <div class="toggle-row">
                        <input type="hidden" name="is_shifted" value="0">
                        <input type="checkbox" id="isShifted" name="is_shifted" value="1" @checked($isShiftedChecked)>
                        <label for="isShifted">Shifted out of the BSA program</label>
                    </div>
                    <div class="toggle-hint">
                        Immediately locks this account out. The student will be blocked at login and shown the
                        reason you enter below.
                    </div>
                    <div class="toggle-fields" style="display: {{ $isShiftedChecked ? 'block' : 'none' }};">
                        <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Reason for shifting</label>
                        <textarea name="shift_reason" placeholder="e.g. Shifted to BS Accountancy Management at another university.">{{ old('shift_reason', $studentProfile?->shift_reason) }}</textarea>
                    </div>
                </div>
            </div>

            <script>
                (function () {
                    var alumniWrap = document.getElementById('alumniToggleWrap');
                    var shiftedWrap = document.getElementById('shiftedToggleWrap');
                    var alumniBox = document.getElementById('isAlumni');
                    var shiftedBox = document.getElementById('isShifted');

                    function sync(wrap, box, otherBox) {
                        wrap.classList.toggle('is-checked', box.checked);
                        var fields = wrap.querySelector('.toggle-fields');
                        if (fields) fields.style.display = box.checked ? '' : 'none';
                        if (box.checked && otherBox.checked) {
                            otherBox.checked = false;
                            otherBox.dispatchEvent(new Event('change'));
                        }
                    }

                    alumniBox.addEventListener('change', function () { sync(alumniWrap, alumniBox, shiftedBox); });
                    shiftedBox.addEventListener('change', function () { sync(shiftedWrap, shiftedBox, alumniBox); });
                })();
            </script>
        @endif

        <div class="actions">
            <a class="btn btn-ghost" href="{{ $backUrl }}">Cancel</a>
            <button class="btn btn-primary">
                <i class="fas fa-save"></i>
                {{ $editMode ? 'Save Changes' : 'Enroll Student' }}
            </button>
        </div>
    </form>
</main>
</body>
</html>
