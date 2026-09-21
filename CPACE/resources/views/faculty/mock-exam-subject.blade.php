<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject->code }} Mock Exams - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.mock-exam-styles')
</head>
<body>
@include('partials.faculty-sidebar', ['active' => 'mock-exams'])

<main class="main">
    <div class="topbar">
        <div>
            <a href="{{ route('faculty.mock-exams') }}" style="font-size:12px;color:var(--muted);text-decoration:none;">
                <i class="fas fa-arrow-left"></i> All subjects
            </a>
            <div class="page-title" style="margin-top:4px;">{{ $subject->code }} — Mock Exams</div>
            <div class="page-sub">{{ $subject->name }}</div>
        </div>
        <div class="topbar-right">
            <form method="POST" action="{{ route('faculty.mock-exams.store') }}">
                @csrf
                <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                <button class="btn btn-primary" type="submit"><i class="fas fa-plus"></i> New mock exam</button>
            </form>
            @include('partials.topbar-actions')
        </div>
    </div>

    @error('exam')<div class="banner banner-danger"><i class="fas fa-triangle-exclamation"></i><div>{{ $message }}</div></div>@enderror

    <div class="card">
        @if($exams->isEmpty())
            <div class="empty">
                <i class="fas fa-file-circle-plus"></i>
                <h3>No mock exam for {{ $subject->code }} yet</h3>
                <p>Start one and you'll pick the topics, then the questions, then the schedule.</p>
            </div>
        @else
            <table class="tbl">
                <thead>
                <tr>
                    <th>Exam</th>
                    <th>Status</th>
                    <th>Sitting</th>
                    <th>Items</th>
                    <th>Started by</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($exams as $exam)
                    <tr>
                        <td>
                            <div style="font-weight:600;color:var(--ink);">{{ $exam->title }}</div>
                            @if($exam->review_note)
                                {{-- The Chair sent this back; surfacing the note here means
                                     faculty see why without opening the builder. --}}
                                <div style="font-size:11.5px;color:var(--red);margin-top:3px;">
                                    <i class="fas fa-rotate-left"></i> Returned: {{ Str::limit($exam->review_note, 90) }}
                                </div>
                            @endif
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
                        <td>{{ $exam->items_count }}</td>
                        <td style="font-size:12px;">{{ $exam->creator?->first_name }} {{ $exam->creator?->last_name }}</td>
                        <td style="text-align:right;white-space:nowrap;">
                            @if($exam->isEditable())
                                <a class="btn btn-ghost btn-sm" href="{{ route('faculty.mock-exams.build', $exam) }}">
                                    <i class="fas fa-pen"></i> Build
                                </a>
                            @else
                                <a class="btn btn-ghost btn-sm" href="{{ route('faculty.mock-exams.build', $exam) }}">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                @if($exam->isPublished() || $exam->isClosed())
                                    <a class="btn btn-ghost btn-sm" href="{{ route('faculty.mock-exams.monitor', $exam) }}">
                                        <i class="fas fa-desktop"></i> Monitor
                                    </a>
                                @endif
                            @endif
                            @if($exam->isDraft())
                                <form method="POST" action="{{ route('faculty.mock-exams.destroy', $exam) }}" style="display:inline;"
                                      onsubmit="return confirm('Delete this draft? This cannot be undone.');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-ghost btn-sm" type="submit" style="color:var(--red);">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
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
