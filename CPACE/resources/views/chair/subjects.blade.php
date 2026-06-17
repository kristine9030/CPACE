<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Assignments - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── Summary ── */
        .summary-row {
            display: flex; gap: 14px; margin-bottom: 24px; flex-wrap: wrap;
        }
        .sum-card {
            flex: 1; min-width: 130px;
            background: #fff; border-radius: 12px;
            padding: 16px 18px;
            display: flex; align-items: center; gap: 12px;
            border: 1px solid #ebebeb;
        }
        .sum-icon {
            width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .sum-icon.total   { background: #f3f4f6; color: #6b7280; }
        .sum-icon.covered { background: #d1fae5; color: #059669; }
        .sum-icon.missing { background: #fee2e2; color: #dc2626; }
        .sum-num { font-size: 22px; font-weight: 700; color: #1a1a1a; line-height: 1; }
        .sum-lbl { font-size: 11px; color: #9ca3af; margin-top: 2px; }

        /* ── Grid ── */
        .subj-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 16px; }

        /* ── Card ── */
        .subj-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e8e8e8;
            overflow: hidden;
        }
        .sc-top {
            display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
            padding: 18px 20px 14px;
            border-left: 5px solid var(--subject-color, #7B1D1D);
        }
        .sc-left { flex: 1; min-width: 0; }
        .sc-code {
            font-size: 20px; font-weight: 800; color: var(--subject-color, #7B1D1D);
            line-height: 1;
        }
        .sc-name { font-size: 11.5px; color: #888; margin-top: 3px; }

        .sc-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 20px;
            font-size: 10.5px; font-weight: 700; white-space: nowrap; flex-shrink: 0;
        }
        .sc-badge.assigned { background: #d1fae5; color: #065f46; }
        .sc-badge.unassigned { background: #f3f4f6; color: #9ca3af; }

        /* ── Faculty list ── */
        .sc-body {
            padding: 12px 20px 16px;
            border-top: 1px solid #f3f4f6;
        }
        .fac-chip {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 6px 12px; border-radius: 8px;
            font-size: 12px; font-weight: 500; color: #374151;
            background: #f9fafb; border: 1px solid #e5e7eb;
            margin: 3px 4px 3px 0;
        }
        .fac-av {
            width: 20px; height: 20px; border-radius: 5px;
            display: flex; align-items: center; justify-content: center;
            font-size: 9px; font-weight: 700; color: #fff;
            background: var(--subject-color, #7B1D1D);
            flex-shrink: 0;
        }
        .empty-msg {
            font-size: 12px; color: #bbb;
            display: flex; align-items: center; gap: 7px;
        }

        /* ── Tip ── */
        .tip-card {
            margin-top: 20px;
            background: #fff; border-radius: 14px;
            border: 1px solid #e8e8e8;
            padding: 18px 22px;
            display: flex; align-items: flex-start; gap: 14px;
        }
        .tip-icon {
            width: 36px; height: 36px; border-radius: 9px;
            background: #fef3c7; color: #d97706;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; flex-shrink: 0;
        }
        .tip-title { font-size: 13px; font-weight: 600; color: #1a1a1a; margin-bottom: 5px; }
        .tip-text  { font-size: 12px; color: #666; line-height: 1.65; }
        .tip-link  {
            display: inline-flex; align-items: center; gap: 5px;
            margin-top: 10px; font-size: 12px; font-weight: 600;
            color: var(--primary); text-decoration: none;
        }
        .tip-link:hover { text-decoration: underline; }

        @media (max-width: 900px) { .subj-grid { grid-template-columns: 1fr; } }

        /* ── Subjects page responsive ── */
        @media (max-width: 768px) {
            .summary-row { gap: 10px; }
            .sum-card { min-width: 0; padding: 12px 14px; }
            .sum-num { font-size: 18px; }
            .tip-card { flex-direction: column; gap: 10px; }
            .sc-top { padding: 14px 14px 10px; }
            .sc-body { padding: 10px 14px 14px; }
        }
        @media (max-width: 480px) {
            .summary-row { flex-direction: column; }
            .sum-card { width: 100%; }
            .sc-code { font-size: 16px; }
            .fac-chip { font-size: 11px; padding: 5px 10px; }
        }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'subjects'])

@php
    $palette = [
        'FAR'  => ['color' => '#2563eb', 'icon' => 'fa-book'],
        'AFAR' => ['color' => '#0891b2', 'icon' => 'fa-book-open'],
        'MS'   => ['color' => '#7c3aed', 'icon' => 'fa-chart-pie'],
        'TAX'  => ['color' => '#059669', 'icon' => 'fa-file-invoice-dollar'],
        'AUD'  => ['color' => '#db2777', 'icon' => 'fa-magnifying-glass-chart'],
        'RFBT' => ['color' => '#d97706', 'icon' => 'fa-scale-balanced'],
    ];
    $subjectsColl = collect($subjects);
    $assigned   = $subjectsColl->filter(fn($s) => $s->faculty->count() > 0)->count();
    $unassigned = $subjectsColl->count() - $assigned;
@endphp

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div>
                <div class="page-title">Subject Assignments</div>
                <div class="page-sub">Faculty handling each of the six CPALE subjects.</div>
            </div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('chair.faculty.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Add Faculty
            </a>
            @include('partials.topbar-actions')
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('status') }}</div>
    @endif

    <!-- Summary -->
    <div class="summary-row">
        <div class="sum-card">
            <div class="sum-icon total"><i class="fas fa-layer-group"></i></div>
            <div>
                <div class="sum-num">{{ $subjectsColl->count() }}</div>
                <div class="sum-lbl">Total Subjects</div>
            </div>
        </div>
        <div class="sum-card">
            <div class="sum-icon covered"><i class="fas fa-circle-check"></i></div>
            <div>
                <div class="sum-num">{{ $assigned }}</div>
                <div class="sum-lbl">Assigned</div>
            </div>
        </div>
        <div class="sum-card">
            <div class="sum-icon missing"><i class="fas fa-circle-exclamation"></i></div>
            <div>
                <div class="sum-num">{{ $unassigned }}</div>
                <div class="sum-lbl">Unassigned</div>
            </div>
        </div>
    </div>

    <!-- Subject cards -->
    <div class="subj-grid">
        @foreach ($subjects as $s)
            @php
                $c = $palette[$s->code] ?? ['color' => '#7B1D1D', 'icon' => 'fa-book'];
                $hasFaculty = $s->faculty->count() > 0;
            @endphp
            <div class="subj-card">
                <div class="sc-top" style="--subject-color: {{ $c['color'] }}">
                    <div class="sc-left">
                        <div class="sc-code">{{ $s->code }}</div>
                        <div class="sc-name">{{ $s->name }}</div>
                    </div>
                    @if ($hasFaculty)
                        <span class="sc-badge assigned">
                            <i class="fas fa-check"></i> {{ $s->faculty->count() }} {{ Str::plural('faculty', $s->faculty->count()) }}
                        </span>
                    @else
                        <span class="sc-badge unassigned">
                            <i class="fas fa-triangle-exclamation"></i> Unassigned
                        </span>
                    @endif
                </div>
                <div class="sc-body" style="--subject-color: {{ $c['color'] }}">
                    @forelse ($s->faculty as $f)
                        <span class="fac-chip">
                            <span class="fac-av">{{ strtoupper(substr($f->name, 0, 1)) }}</span>
                            {{ $f->name }}
                        </span>
                    @empty
                        <div class="empty-msg">
                            <i class="fas fa-user-slash"></i>
                            No faculty assigned yet.
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <!-- Tip -->
    <div class="tip-card">
        <div class="tip-icon"><i class="fas fa-lightbulb"></i></div>
        <div>
            <div class="tip-title">How to assign faculty</div>
            <div class="tip-text">
                Open <strong>Faculty Accounts</strong>, click the
                <i class="fas fa-layer-group" style="color:#7c3aed;"></i> assign icon next to a faculty member,
                then tick the subjects they will handle. You can also set assignments while adding a new account.
            </div>
            <a href="{{ route('chair.faculty') }}" class="tip-link">
                <i class="fas fa-arrow-right"></i> Go to Faculty Accounts
            </a>
        </div>
    </div>
</main>
</body>
</html>
