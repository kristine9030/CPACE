<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Performance Report - CPACE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #e5e7eb;
            font-family: 'Poppins', sans-serif;
            color: #1f2937;
        }
        .toolbar {
            max-width: 1100px;
            margin: 18px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        .toolbar a,
        .toolbar button {
            border: 0;
            border-radius: 8px;
            padding: 9px 16px;
            font: 600 12px 'Poppins', sans-serif;
            text-decoration: none;
            cursor: pointer;
        }
        .back { background: #fff; color: #555; }
        .print { background: #7B1D1D; color: #fff; }
        .paper {
            max-width: 1100px;
            min-height: 900px;
            margin: 0 auto 30px;
            background: #fff;
            padding: 42px 48px;
            box-shadow: 0 10px 28px rgba(0, 0, 0, .12);
        }
        .report-head {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            border-bottom: 3px solid #7B1D1D;
            padding-bottom: 16px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand img {
            width: 52px;
            height: 52px;
            object-fit: contain;
        }
        .brand h1 {
            font-size: 19px;
            color: #7B1D1D;
            margin: 0;
        }
        .brand p,
        .report-meta {
            font-size: 10px;
            color: #6b7280;
            margin: 3px 0;
        }
        .report-meta { text-align: right; }
        .report-title { margin: 22px 0 15px; }
        .report-title h2 {
            font-size: 18px;
            margin: 0 0 4px;
        }
        .report-title .report-meta { text-align: left; }
        .summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .summary-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 11px;
        }
        .summary-number {
            font-size: 19px;
            font-weight: 700;
        }
        .summary-label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            margin-top: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e5e7eb;
        }
        th {
            background: #fafafa;
            text-align: left;
            padding: 8px;
            font-size: 8.5px;
            text-transform: uppercase;
            color: #6b7280;
        }
        td {
            padding: 8px;
            border-top: 1px solid #eee;
            font-size: 9.5px;
            vertical-align: top;
        }
        .student-email { color: #999; }
        .status {
            font-size: 8px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
        }
        .status.risk { background: #fde8e8; color: #b91c1c; }
        .status.track { background: #d1fae5; color: #047857; }
        .status.disabled { background: #eee; color: #777; }
        .empty-row {
            text-align: center;
            padding: 25px;
        }
        .report-foot {
            display: flex;
            justify-content: space-between;
            font-size: 8.5px;
            color: #9ca3af;
            border-top: 1px solid #eee;
            margin-top: 20px;
            padding-top: 9px;
        }
        @media (max-width: 800px) {
            .paper {
                padding: 25px;
                overflow: auto;
            }
            .summary { grid-template-columns: repeat(2, 1fr); }
        }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .paper {
                max-width: none;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .report-head { break-after: avoid; }
            tr { break-inside: avoid; }
            @page { size: A4 landscape; margin: 12mm; }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <a class="back" href="{{ route('chair.students', request()->query()) }}">
        <i class="fas fa-arrow-left"></i> Back to Students
    </a>
    <button class="print" onclick="window.print()">
        <i class="fas fa-file-pdf"></i> Save as PDF / Print
    </button>
</div>

<main class="paper">
    <!-- Report identity -->
    <div class="report-head">
        <div class="brand">
            <img src="{{ asset('images/cpace_logo.png') }}" alt="CPACE">
            <div>
                <h1>CPACE Student Performance Report</h1>
                <p>College of Accountancy &bull; Program Chair Office</p>
            </div>
        </div>
        <div class="report-meta">
            <strong>Generated {{ now()->format('F j, Y') }}</strong><br>
            {{ now()->format('g:i A') }}<br>
            Prepared by {{ Auth::user()->name }}
        </div>
    </div>

    <!-- Applied report scope -->
    <div class="report-title">
        <h2>Enrollment and Readiness Summary</h2>
        <p class="report-meta">
            Scope: {{ $filters['year'] ? 'Year '.$filters['year'] : 'All year levels' }}
            &bull; {{ $filters['section'] ? 'Section '.$filters['section'] : 'All sections' }}
            &bull; {{ $filters['status']
                ? ucwords(str_replace('_', ' ', $filters['status']))
                : 'All statuses' }}
        </p>
    </div>

    <!-- Headline report metrics -->
    <div class="summary">
        <div class="summary-box">
            <div class="summary-number">{{ $stats['total'] }}</div>
            <div class="summary-label">Students in Report</div>
        </div>
        <div class="summary-box">
            <div class="summary-number">{{ $stats['active'] }}</div>
            <div class="summary-label">Active Accounts</div>
        </div>
        <div class="summary-box">
            <div class="summary-number">{{ $stats['average'] }}%</div>
            <div class="summary-label">Average Readiness</div>
        </div>
        <div class="summary-box">
            <div class="summary-number">{{ $stats['at_risk'] }}</div>
            <div class="summary-label">At Risk</div>
        </div>
    </div>

    <!-- Student performance roster -->
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Student No.</th>
                <th>Year / Section</th>
                <th>Readiness</th>
                <th>Attempted</th>
                <th>Quizzes</th>
                <th>Streak</th>
                <th>Last Active</th>
                <th>Intervention Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>
                        <strong>{{ $row['name'] }}</strong><br>
                        <span class="student-email">{{ $row['email'] }}</span>
                    </td>
                    <td>{{ $row['student_number'] ?: '—' }}</td>
                    <td>
                        {{ $row['year_level'] ? 'Year '.$row['year_level'] : '—' }}
                        / {{ $row['section'] ?: '—' }}
                    </td>
                    <td>
                        <strong>{{ $row['score'] === null ? 'Not rated' : $row['score'].'%' }}</strong>
                    </td>
                    <td>{{ $row['attempted'] }}</td>
                    <td>{{ $row['quizzes'] }}</td>
                    <td>{{ $row['streak'] }} days</td>
                    <td>{{ $row['last_active'] ? $row['last_active']->format('M j, Y') : 'Never' }}</td>
                    <td>
                        @if (! $row['is_active'])
                            <span class="status disabled">Disabled</span>
                        @elseif ($row['at_risk'])
                            <span class="status risk">At Risk</span>
                        @else
                            <span class="status track">On Track</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="empty-row">No students matched the selected scope.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="report-foot">
        <span>CPACE &bull; Confidential departmental document</span>
        <span>{{ $stats['total'] }} student records</span>
    </div>
</main>
</body>
</html>
