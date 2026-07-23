<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .filter-card {
            background: #fff;
            border-radius: 14px;
            padding: 15px 18px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .search-box {
            position: relative;
            flex: 1;
            min-width: 210px;
        }
        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #bbb;
            font-size: 12px;
        }
        .search-box input { padding-left: 34px; }
        .filter-card select {
            width: auto;
            min-width: 125px;
        }
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .table-wrap table { min-width: 920px; }
        .student-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .student-cell .user-av {
            width: 34px;
            height: 34px;
            font-size: 10px;
        }
        .student-name {
            font-size: 12.5px;
            font-weight: 600;
            color: #1a1a1a;
        }
        .student-meta {
            font-size: 10px;
            color: #aaa;
            margin-top: 2px;
        }
        .group-tag {
            display: inline-flex;
            padding: 3px 8px;
            border-radius: 12px;
            background: #ede9fe;
            color: #6d28d9;
            font-size: 9.5px;
            font-weight: 700;
            margin: 2px;
        }
        .ungrouped {
            color: #bbb;
            font-size: 10px;
        }
        .score {
            font-size: 13px;
            font-weight: 700;
        }
        .score-bar {
            width: 70px;
            height: 5px;
            border-radius: 4px;
            background: #eee;
            margin-top: 4px;
            overflow: hidden;
        }
        .score-bar span {
            display: block;
            height: 100%;
            border-radius: 4px;
        }
        .risk-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 14px;
            font-size: 9.5px;
            font-weight: 700;
            background: #fde8e8;
            color: #b91c1c;
        }
        .action-btn {
            width: 29px;
            height: 29px;
            border: 0;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            cursor: pointer;
            font-size: 10px;
        }
        .ab-view { background: #d1fae5; color: #047857; }
        .ab-edit { background: #dbeafe; color: #2563eb; }
        .ab-toggle { background: #fef3c7; color: #b45309; }
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 16px 10px 0;
            border-top: 1px solid #f5f5f5;
            margin-top: 4px;
        }
        .pagination-info {
            font-size: 11px;
            color: #999;
        }
        .page-links {
            display: flex;
            gap: 5px;
        }
        .page-btn {
            min-width: 30px;
            height: 30px;
            padding: 0 8px;
            border: 1px solid #e5e7eb;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #555;
            font-size: 11px;
        }
        .page-btn.active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }
        .page-btn.disabled {
            opacity: .4;
            pointer-events: none;
        }
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            width: 100%;
            max-width: 520px;
        }
        .modal h3 {
            font-size: 16px;
            margin-bottom: 4px;
        }
        .modal-sub {
            font-size: 11px;
            color: #999;
            line-height: 1.6;
            margin-bottom: 16px;
        }
        .upload-box {
            border: 2px dashed #ddd;
            border-radius: 11px;
            padding: 22px;
            text-align: center;
            background: #fafafa;
        }
        .upload-box i {
            display: block;
            font-size: 25px;
            color: var(--primary);
            margin-bottom: 9px;
        }
        .upload-box input {
            width: 100%;
            font-size: 11px;
        }
        .template-link {
            display: inline-flex;
            margin-top: 10px;
            font-size: 11px;
            color: var(--primary);
            text-decoration: none;
        }
        .template-link i { margin-right: 6px; }
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 9px;
            margin-top: 18px;
        }
        .import-errors {
            background: #fff7ed;
            color: #9a3412;
            padding: 11px 14px;
            border-radius: 9px;
            margin-bottom: 16px;
            font-size: 11px;
        }
        .import-errors ul {
            padding-left: 18px;
            margin-top: 5px;
        }
        @media (max-width: 768px) {
            .filter-card { align-items: stretch; }
            .filter-card select,
            .filter-card .btn { width: 100%; }
            .pagination {
                flex-direction: column;
                align-items: flex-start;
            }
            .topbar-right {
                width: 100%;
                justify-content: flex-end;
            }
            .topbar-right .btn span { display: none; }
        }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'students'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Student Management</div>
                <div class="page-sub">
                    Enrollment, readiness monitoring, sections, and departmental reporting.
                </div>
            </div>
        </div>
        <div class="topbar-right">
            <a class="btn btn-outline" href="{{ route('chair.students.import.form') }}">
                <i class="fas fa-file-import"></i><span>Bulk Enroll</span>
            </a>
            <a class="btn btn-primary" href="{{ route('chair.students.create') }}">
                <i class="fas fa-user-plus"></i><span>Enroll Student</span>
            </a>
            @include('partials.topbar-actions')
        </div>
    </div>

    <!-- Feedback messages -->
    @if (session('status'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">
            <i class="fas fa-triangle-exclamation"></i> {{ session('error') }}
        </div>
    @endif
    @if (session('import_errors'))
        <div class="import-errors">
            <strong>Some rows were skipped:</strong>
            <ul>
                @foreach (session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (isset($errors) && $errors->any())
        <div class="alert alert-error">
            <i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}
        </div>
    @endif

    <!-- Roster summary -->
    <div class="stats-row">
        @php
            $summaryCards = [
                ['value' => $stats['total'], 'label' => 'Enrolled Students', 'tone' => 'si-blue', 'icon' => 'fa-user-graduate'],
                ['value' => $stats['active'], 'label' => 'Active Accounts', 'tone' => 'si-green', 'icon' => 'fa-circle-check'],
                ['value' => $stats['average'].'%', 'label' => 'Average Readiness', 'tone' => 'si-orange', 'icon' => 'fa-chart-line'],
                ['value' => $stats['at_risk'], 'label' => 'Need Intervention', 'tone' => 'si-red', 'icon' => 'fa-triangle-exclamation'],
            ];
        @endphp
        @foreach ($summaryCards as $card)
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-num">{{ $card['value'] }}</div>
                        <div class="stat-lbl">{{ $card['label'] }}</div>
                    </div>
                    <div class="stat-icon {{ $card['tone'] }}">
                        <i class="fas {{ $card['icon'] }}"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Search, grouping, and status filters -->
    <form class="filter-card" method="GET">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input
                type="text"
                name="search"
                value="{{ $filters['search'] }}"
                placeholder="Search name, email, or student number"
            >
        </div>
        <select name="year">
            <option value="">All year levels</option>
            @foreach ($years as $year)
                <option value="{{ $year }}" @selected((string) $filters['year'] === (string) $year)>
                    Year {{ $year }}
                </option>
            @endforeach
        </select>
        <select name="section">
            <option value="">All sections</option>
            @foreach ($sections as $section)
                <option value="{{ $section }}" @selected($filters['section'] === $section)>
                    Section {{ $section }}
                </option>
            @endforeach
        </select>
        <select name="status">
            <option value="">All statuses</option>
            <option value="active" @selected($filters['status'] === 'active')>Active</option>
            <option value="disabled" @selected($filters['status'] === 'disabled')>Disabled</option>
            <option value="at_risk" @selected($filters['status'] === 'at_risk')>At Risk</option>
            <option value="setup_pending" @selected($filters['status'] === 'setup_pending')>Setup Pending</option>
        </select>
        <select name="sort">
            <option value="name" @selected($filters['sort'] === 'name')>Name</option>
            <option value="score_desc" @selected($filters['sort'] === 'score_desc')>Highest readiness</option>
            <option value="score_asc" @selected($filters['sort'] === 'score_asc')>Lowest readiness</option>
            <option value="recent" @selected($filters['sort'] === 'recent')>Recently active</option>
        </select>
        <button class="btn btn-primary btn-sm">
            <i class="fas fa-filter"></i> Apply
        </button>
        <a class="btn btn-ghost btn-sm" href="{{ route('chair.students') }}">Reset</a>
    </form>

    <!-- Student roster -->
    <div class="card">
        <div class="card-head">
            <span class="card-title">Student Roster ({{ $students->total() }})</span>
            <div style="display:flex;gap:7px;">
                <a href="{{ route('chair.students.export.csv', request()->query()) }}" class="btn btn-ghost btn-sm">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
                <a href="{{ route('chair.students.export.pdf', request()->query()) }}" class="btn btn-ghost btn-sm">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Group</th>
                        <th>Readiness</th>
                        <th>Quizzes</th>
                        <th>Streak</th>
                        <th>Last Active</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        @php
                            $scoreColor = match (true) {
                                $student['score'] === null => '#aaa',
                                $student['score'] >= 75 => '#059669',
                                $student['score'] >= 60 => '#d97706',
                                default => '#c0392b',
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="student-cell">
                                    <div class="user-av">{{ $student['initials'] }}</div>
                                    <div>
                                        <div class="student-name">{{ $student['name'] }}</div>
                                        <div class="student-meta">
                                            {{ $student['student_number'] ?: 'No student number' }}
                                            &bull; {{ $student['email'] }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($student['year_level'])
                                    <span class="group-tag">Year {{ $student['year_level'] }}</span>
                                @endif
                                @if ($student['section'])
                                    <span class="group-tag">{{ $student['section'] }}</span>
                                @endif
                                @if (! $student['year_level'] && ! $student['section'])
                                    <span class="ungrouped">Ungrouped</span>
                                @endif
                            </td>
                            <td>
                                <div class="score" style="color:{{ $scoreColor }};">
                                    {{ $student['score'] === null ? 'Not rated' : $student['score'].'%' }}
                                </div>
                                <div class="score-bar">
                                    <span style="width:{{ $student['score'] ?? 0 }}%;background:{{ $scoreColor }};"></span>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $student['quizzes'] }}</strong>
                                <div class="student-meta">{{ $student['attempted'] }} items</div>
                            </td>
                            <td>
                                <i class="fas fa-fire" style="color:#f59e0b;"></i>
                                {{ $student['streak'] }} day{{ $student['streak'] === 1 ? '' : 's' }}
                            </td>
                            <td style="font-size:11px;color:#666;">
                                {{ $student['last_active'] ? $student['last_active']->diffForHumans() : 'Never' }}
                            </td>
                            <td>
                                @if (! $student['is_active'])
                                    <span class="pill pill-off">Disabled</span>
                                @elseif (! $student['setup_completed'])
                                    <span class="pill pill-pending" title="Student hasn't completed first-login Account Setup yet">
                                        <i class="fas fa-hourglass-half"></i> Setup Pending
                                    </span>
                                @elseif ($student['at_risk'])
                                    <span class="risk-pill">
                                        <i class="fas fa-triangle-exclamation"></i> At Risk
                                    </span>
                                @else
                                    <span class="pill pill-on"><i class="fas fa-check"></i> On Track</span>
                                @endif
                            </td>
                            <td style="text-align:right;white-space:nowrap;">
                                <a
                                    class="action-btn ab-view"
                                    href="{{ route('chair.students.show', $student['id']) }}"
                                    title="View performance"
                                ><i class="fas fa-eye"></i></a>
                                <a
                                    class="action-btn ab-edit"
                                    href="{{ route('chair.students.edit', $student['id']) }}"
                                    title="Edit account"
                                ><i class="fas fa-pen"></i></a>
                                <form
                                    method="POST"
                                    action="{{ route('chair.students.toggle', $student['id']) }}"
                                    style="display:inline;"
                                >
                                    @csrf
                                    <button
                                        class="action-btn ab-toggle"
                                        title="{{ $student['is_active'] ? 'Disable' : 'Enable' }}"
                                    ><i class="fas fa-power-off"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty">
                                    <i class="fas fa-user-graduate"></i>
                                    <div>No students match the selected filters.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($students->hasPages())
            <div class="pagination">
                <span class="pagination-info">
                    Showing {{ $students->firstItem() }}&ndash;{{ $students->lastItem() }}
                    of {{ $students->total() }}
                </span>
                <div class="page-links">
                    <a
                        class="page-btn {{ $students->onFirstPage() ? 'disabled' : '' }}"
                        href="{{ $students->previousPageUrl() ?: '#' }}"
                    ><i class="fas fa-chevron-left"></i></a>
                    @for ($page = 1; $page <= $students->lastPage(); $page++)
                        <a
                            class="page-btn {{ $page === $students->currentPage() ? 'active' : '' }}"
                            href="{{ $students->url($page) }}"
                        >{{ $page }}</a>
                    @endfor
                    <a
                        class="page-btn {{ $students->hasMorePages() ? '' : 'disabled' }}"
                        href="{{ $students->nextPageUrl() ?: '#' }}"
                    ><i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
        @endif
    </div>
</main>

<!-- CSV import modal -->
<div class="modal-overlay" id="importModal">
    <div class="modal">
        <h3>Import Students from CSV</h3>
        <div class="modal-sub">
            Upload multiple accounts at once. Required columns are first_name, last_name,
            email, and password. Optional grouping columns are student_number, year_level,
            section, and is_active.
        </div>
        <form method="POST" action="{{ route('chair.students.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="upload-box">
                <i class="fas fa-cloud-arrow-up"></i>
                <input type="file" name="csv_file" accept=".csv,text/csv" required>
            </div>
            <a href="{{ route('chair.students.template') }}" class="template-link">
                <i class="fas fa-download"></i> Download CSV template
            </a>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeImportModal()">Cancel</button>
                <button class="btn btn-primary">
                    <i class="fas fa-file-import"></i> Import Students
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const importModal = document.getElementById('importModal');

    function openImportModal() {
        importModal.classList.add('open');
    }

    function closeImportModal() {
        importModal.classList.remove('open');
    }

    importModal.addEventListener('click', event => {
        if (event.target === importModal) closeImportModal();
    });
</script>
</body>
</html>
