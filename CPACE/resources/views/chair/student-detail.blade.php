<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $student->name }} - Student Performance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-card {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
        }
        .profile-card .user-av {
            width: 54px;
            height: 54px;
            font-size: 16px;
        }
        .profile-info { flex: 1; }
        .profile-name {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a1a;
        }
        .profile-meta {
            font-size: 10.5px;
            color: #999;
            margin-top: 3px;
        }
        .profile-actions {
            display: flex;
            gap: 7px;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(300px, .75fr);
            gap: 18px;
        }
        .subject-row {
            padding: 12px 0;
            border-top: 1px solid #f5f5f5;
        }
        .subject-top {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 11px;
        }
        .subject-score { font-weight: 700; }
        .progress {
            height: 6px;
            background: #eee;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 7px;
        }
        .progress span {
            display: block;
            height: 100%;
            border-radius: 4px;
        }
        .weak-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 9px;
            background: #fef2f2;
            margin-bottom: 7px;
        }
        .weak-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: #fde8e8;
            color: #b91c1c;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .weak-info { flex: 1; }
        .weak-name {
            font-size: 11px;
            font-weight: 600;
            color: #7f1d1d;
        }
        .weak-meta {
            font-size: 9.5px;
            color: #b88;
        }
        .weak-score {
            color: #b91c1c;
            font-size: 12px;
        }
        .risk-pill {
            display: inline-flex;
            padding: 3px 8px;
            border-radius: 12px;
            background: #fde8e8;
            color: #b91c1c;
            font-size: 9px;
            font-weight: 700;
        }
        .history-wrap { overflow-x: auto; }
        .history-wrap table { min-width: 650px; }
        .score-good { color: #059669; }
        .score-low { color: #c0392b; }
        .pages {
            margin-top: 12px;
            font-size: 11px;
        }
        .pages a { color: var(--primary); }
        @media (max-width: 950px) {
            .detail-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 650px) {
            .profile-card {
                align-items: flex-start;
                flex-wrap: wrap;
            }
            .profile-actions { width: 100%; }
        }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'students'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Student Performance</div>
                <div class="page-sub">Readiness, quiz history, and detected weak areas.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a class="btn btn-outline" href="{{ route('chair.students') }}">
                <i class="fas fa-arrow-left"></i> Students
            </a>
            @include('partials.topbar-actions')
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
    @endif

    <!-- Student identity and enrollment summary -->
    <div class="card profile-card">
        <div class="user-av">{{ $row['initials'] }}</div>
        <div class="profile-info">
            <div class="profile-name">
                {{ $student->name }}
                @unless ($student->is_active)
                    <span class="pill pill-off">Disabled</span>
                @endunless
            </div>
            <div class="profile-meta">
                {{ $student->studentProfile?->student_number ?: 'No student number' }}
                &bull; {{ $student->email }}
            </div>
            <div class="profile-meta">
                {{ $student->studentProfile?->year_level
                    ? 'Year '.$student->studentProfile->year_level
                    : 'Year not set' }}
                &bull; {{ $student->studentProfile?->section ?: 'No section' }}
                &bull; Last active {{ $row['last_active'] ? $row['last_active']->diffForHumans() : 'never' }}
            </div>
        </div>
        <div class="profile-actions">
            <a
                class="btn btn-ghost btn-sm"
                href="{{ route('chair.students.export.pdf', ['search' => $student->email]) }}"
            >
                <i class="fas fa-file-pdf"></i> Report
            </a>
            <a class="btn btn-primary btn-sm" href="{{ route('chair.students.edit', $student) }}">
                <i class="fas fa-pen"></i> Edit
            </a>
        </div>
    </div>

    <!-- Headline student metrics -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-num">{{ $row['score'] === null ? '—' : $row['score'].'%' }}</div>
                    <div class="stat-lbl">Overall Readiness</div>
                </div>
                <div class="stat-icon si-red"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-num">{{ $row['quizzes'] }}</div>
                    <div class="stat-lbl">Completed Quizzes</div>
                </div>
                <div class="stat-icon si-blue"><i class="fas fa-clipboard-check"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-num">{{ $row['attempted'] }}</div>
                    <div class="stat-lbl">Questions Attempted</div>
                </div>
                <div class="stat-icon si-green"><i class="fas fa-circle-question"></i></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-num">{{ $row['streak'] }}</div>
                    <div class="stat-lbl">Day Streak</div>
                </div>
                <div class="stat-icon si-orange"><i class="fas fa-fire"></i></div>
            </div>
        </div>
    </div>

    <div class="detail-grid">
        <div>
            <!-- Performance by subject -->
            <div class="card">
                <div class="card-head">
                    <span class="card-title">Performance by Subject</span>
                    <span style="font-size:10px;color:#aaa;">Uses configured subject thresholds</span>
                </div>
                @foreach ($subjectPerformance as $subject)
                    @php
                        $color = $subject->passing
                            ? '#10b981'
                            : ($subject->accuracy >= 60 ? '#f59e0b' : '#c0392b');
                    @endphp
                    <div class="subject-row">
                        <div class="subject-top">
                            <span>
                                <span class="subj-badge b-{{ strtolower($subject->code) }}">
                                    {{ $subject->code }}
                                </span>
                                <strong>{{ $subject->name }}</strong>
                            </span>
                            <span class="subject-score" style="color:{{ $color }};">
                                {{ $subject->accuracy }}%
                                &bull; {{ $subject->passing ? 'Ready' : 'Needs '.$subject->passing_threshold.'%' }}
                            </span>
                        </div>
                        <div class="progress">
                            <span style="width:{{ $subject->accuracy }}%;background:{{ $color }};"></span>
                        </div>
                        <div class="profile-meta">
                            {{ $subject->correct }}/{{ $subject->attempted }} correct
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Quiz history -->
            <div class="card">
                <div class="card-head">
                    <span class="card-title">Quiz History</span>
                </div>
                <div class="history-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Type</th>
                                <th>Mode</th>
                                <th>Result</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($quizHistory as $quiz)
                                @php
                                    $score = (int) round($quiz->score_percent);
                                    $passMark = $quiz->subject->passing_threshold ?? 75;
                                    $passed = $score >= $passMark;
                                @endphp
                                <tr>
                                    <td>{{ $quiz->completed_at?->format('M j, Y') }}</td>
                                    <td>{{ $quiz->subject->code ?? '—' }}</td>
                                    <td>{{ ucfirst($quiz->session_type) }}</td>
                                    <td>{{ ucfirst($quiz->mode) }}</td>
                                    <td>
                                        <strong class="{{ $passed ? 'score-good' : 'score-low' }}">
                                            {{ $score }}%
                                        </strong>
                                        <div class="profile-meta">
                                            {{ $quiz->correct_answers }}/{{ $quiz->total_items }}
                                        </div>
                                    </td>
                                    <td>
                                        {{ intdiv((int) $quiz->duration_secs, 60) }}m
                                        {{ (int) $quiz->duration_secs % 60 }}s
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty" style="padding:22px;">
                                            No completed quizzes yet.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($quizHistory->hasPages())
                    <div class="pages">{{ $quizHistory->withQueryString()->links() }}</div>
                @endif
            </div>
        </div>

        <div>
            <!-- Detected weak areas -->
            <div class="card">
                <div class="card-head">
                    <span class="card-title">
                        <i class="fas fa-triangle-exclamation" style="color:var(--accent);margin-right:6px;"></i>
                        Weak Areas
                    </span>
                    <span class="risk-pill">{{ $weakAreas->count() }}</span>
                </div>
                @forelse ($weakAreas as $weak)
                    <div class="weak-row">
                        <div class="weak-icon"><i class="fas fa-arrow-trend-down"></i></div>
                        <div class="weak-info">
                            <div class="weak-name">{{ $weak->topic }}</div>
                            <div class="weak-meta">
                                {{ $weak->subject }} &bull; {{ $weak->total_attempts }} attempts
                            </div>
                        </div>
                        <strong class="weak-score">{{ $weak->accuracy }}%</strong>
                    </div>
                @empty
                    <div class="empty" style="padding:22px 10px;">
                        <i class="fas fa-circle-check" style="color:#10b981;"></i>
                        <div>No weak areas detected.</div>
                    </div>
                @endforelse
            </div>

            <!-- Enrollment profile -->
            <div class="card">
                <div class="card-head">
                    <span class="card-title">Enrollment Profile</span>
                </div>
                <table>
                    <tr>
                        <td style="color:#999;">Student No.</td>
                        <td><strong>{{ $student->studentProfile?->student_number ?: '—' }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color:#999;">Year Level</td>
                        <td><strong>{{ $student->studentProfile?->year_level ?: '—' }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color:#999;">Section</td>
                        <td><strong>{{ $student->studentProfile?->section ?: '—' }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color:#999;">Exam Target</td>
                        <td>
                            <strong>
                                {{ $student->studentProfile?->exam_target_date?->format('M j, Y') ?: '—' }}
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="color:#999;">Points</td>
                        <td><strong>{{ number_format($student->studentProfile?->total_points ?? 0) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</main>
</body>
</html>
