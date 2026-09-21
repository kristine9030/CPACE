<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject->code }} Mock Exams - CPACE Program Chair</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.mock-exam-styles')
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'mock-exams'])

<main class="main">
    <div class="topbar">
        <div>
            <a href="{{ route('chair.mock-exams') }}" style="font-size:12px;color:var(--muted);text-decoration:none;">
                <i class="fas fa-arrow-left"></i> All folders
            </a>
            <div class="page-title" style="margin-top:4px;">{{ $subject->code }} — Mock Exams</div>
            <div class="page-sub">{{ $subject->name }}</div>
        </div>
        <div class="topbar-right">@include('partials.topbar-actions')</div>
    </div>

    @error('exam')<div class="banner banner-danger"><i class="fas fa-triangle-exclamation"></i><div>{{ $message }}</div></div>@enderror

    <div class="card">
        @if($exams->isEmpty())
            <div class="empty">
                <i class="fas fa-folder-open"></i>
                <h3>Nothing here yet</h3>
                <p>Exams appear once a faculty member assigned to {{ $subject->code }} submits one for review.</p>
            </div>
        @else
            <table class="tbl">
                <thead>
                <tr>
                    <th>Exam</th>
                    <th>Status</th>
                    <th>Sitting</th>
                    <th>Items</th>
                    <th>Redeem code</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($exams as $exam)
                    <tr>
                        <td>
                            <div style="font-weight:600;color:var(--ink);">{{ $exam->title }}</div>
                            <div style="font-size:11.5px;color:var(--muted);margin-top:2px;">
                                by {{ $exam->creator?->first_name }} {{ $exam->creator?->last_name }}
                                @if($exam->submitted_for_review_at)
                                    · submitted {{ $exam->submitted_for_review_at->diffForHumans() }}
                                @endif
                            </div>
                        </td>
                        <td>
                            @php $chip = ['draft'=>'chip-draft','for_review'=>'chip-review','published'=>'chip-published','closed'=>'chip-closed'][$exam->status] ?? 'chip-draft'; @endphp
                            <span class="chip {{ $chip }}">{{ str_replace('_', ' ', $exam->status) }}</span>
                        </td>
                        <td>
                            @if($exam->scheduled_at)
                                {{ $exam->scheduled_at->format('M j, Y · g:i A') }}
                                <div style="font-size:11px;color:var(--muted);">{{ $exam->duration_minutes }} min</div>
                            @else
                                <span style="color:var(--muted);">Not scheduled</span>
                            @endif
                        </td>
                        <td>
                            {{ $exam->items_count }}
                            @if($exam->attempts_count > 0)
                                <div style="font-size:11px;color:var(--muted);">{{ $exam->submitted_count }}/{{ $exam->attempts_count }} submitted</div>
                            @endif
                        </td>
                        <td>
                            @if($exam->event)
                                <span class="code-pill" style="font-size:12px;padding:6px 11px;letter-spacing:1px;">
                                    {{ $exam->event->access_code }}
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $exam->event->access_code }}');this.textContent='Copied';">Copy</button>
                                </span>
                            @else
                                <span style="color:var(--muted);font-size:12px;">—</span>
                            @endif
                        </td>
                        <td style="text-align:right;white-space:nowrap;">
                            <a class="btn btn-ghost btn-sm" href="{{ route('chair.mock-exams.review', $exam) }}">
                                <i class="fas fa-{{ $exam->isEditable() ? 'pen-to-square' : 'eye' }}"></i>
                                {{ $exam->isEditable() ? 'Review' : 'View' }}
                            </a>
                            @if($exam->isPublished() || $exam->isClosed())
                                <a class="btn btn-ghost btn-sm" href="{{ route('chair.mock-exams.monitor', $exam) }}">
                                    <i class="fas fa-desktop"></i> Monitor
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</main>

@include('partials.alerts')
</body>
</html>
