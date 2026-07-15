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
