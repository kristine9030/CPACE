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
    <style>
        .exam-cards { display:flex; flex-direction:column; gap:14px; }
        .exam-card { display:flex; align-items:center; gap:18px; background:#fff; border:1px solid var(--line);
                     border-left:4px solid var(--exam-accent, #cfd3db); border-radius:14px; padding:18px 20px;
                     box-shadow:0 8px 22px -12px rgba(15,20,35,.3), 0 2px 6px -2px rgba(15,20,35,.1);
                     transition:box-shadow .2s, transform .2s; }
        .exam-card:hover { box-shadow:0 16px 34px -14px rgba(15,20,35,.38), 0 4px 10px -2px rgba(15,20,35,.16); transform:translateY(-2px); }
        .exam-card-icon { width:52px; height:52px; border-radius:14px; flex-shrink:0; display:flex; align-items:center; justify-content:center;
                          font-size:19px; background:var(--exam-accent-light, #eef0f4); color:var(--exam-accent, #5b6377); }
        .exam-card-body { flex:1; min-width:0; }
        .exam-card-top { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
        .exam-card-title { font-size:15px; font-weight:700; color:var(--ink); }
        .exam-card-returned { font-size:11.5px; color:var(--red); margin-top:6px; display:flex; gap:6px; align-items:flex-start; line-height:1.4; }
        .exam-card-meta { display:flex; gap:20px; flex-wrap:wrap; margin-top:10px; }
        .exam-card-meta .meta-item { display:flex; align-items:center; gap:7px; font-size:12px; color:var(--muted); }
        .exam-card-meta .meta-item i { font-size:11px; width:13px; text-align:center; color:var(--exam-accent, var(--muted)); }
        .exam-card-meta .meta-item strong { color:var(--ink); font-weight:600; }
        .exam-card-actions { display:flex; align-items:center; gap:8px; flex-shrink:0; }
        .exam-card-del { border:1px solid var(--line); background:#fff; color:var(--muted); width:34px; height:34px; border-radius:9px;
                         display:flex; align-items:center; justify-content:center; cursor:pointer; transition:background .15s, color .15s, border-color .15s; }
        .exam-card-del:hover { background:#fdeceb; color:var(--red); border-color:#f5cdc9; }
        @media (max-width: 640px) {
            .exam-card { flex-wrap:wrap; }
            .exam-card-actions { width:100%; justify-content:flex-end; }
        }
    </style>
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

    @if($exams->isEmpty())
        <div class="card">
            <div class="empty">
                <i class="fas fa-file-circle-plus"></i>
                <h3>No mock exam for {{ $subject->code }} yet</h3>
                <p>Start one and you'll pick the topics, then the questions, then the schedule.</p>
            </div>
        </div>
    @else
        <div class="exam-cards">
            @foreach($exams as $exam)
                @php
                    $accent = [
                        'draft' => ['#5b6377', '#eef0f4'],
                        'for_review' => ['#c98a08', '#fdf0d8'],
                        'published' => ['#1e9e63', '#dff3e8'],
                        'closed' => ['#8b93a6', '#eceff3'],
                    ][$exam->status] ?? ['#5b6377', '#eef0f4'];
                    $chip = ['draft'=>'chip-draft','for_review'=>'chip-review','published'=>'chip-published','closed'=>'chip-closed'][$exam->status] ?? 'chip-draft';
                @endphp
                <div class="exam-card" style="--exam-accent:{{ $accent[0] }};--exam-accent-light:{{ $accent[1] }};">
                    <div class="exam-card-icon"><i class="fas fa-file-lines"></i></div>
                    <div class="exam-card-body">
                        <div class="exam-card-top">
                            <div class="exam-card-title">{{ $exam->title }}</div>
                            <span class="chip {{ $chip }}">{{ str_replace('_', ' ', $exam->status) }}</span>
                        </div>
                        @if($exam->review_note)
                            {{-- The Chair sent this back; surfacing the note here means
                                 faculty see why without opening the builder. --}}
                            <div class="exam-card-returned">
                                <i class="fas fa-rotate-left"></i> Returned: {{ Str::limit($exam->review_note, 90) }}
                            </div>
                        @endif
                        <div class="exam-card-meta">
                            <span class="meta-item">
                                <i class="fas fa-calendar-day"></i>
                                @if($exam->scheduled_at)
                                    <strong>{{ $exam->scheduled_at->format('M j, Y · g:i A') }}</strong> · {{ $exam->duration_minutes }} min
                                @else
                                    Not scheduled
                                @endif
                            </span>
                            <span class="meta-item"><i class="fas fa-list-check"></i> <strong>{{ $exam->items_count }}</strong> {{ Str::plural('item', $exam->items_count) }}</span>
                            <span class="meta-item"><i class="fas fa-user"></i> {{ $exam->creator?->first_name }} {{ $exam->creator?->last_name }}</span>
                        </div>
                    </div>
                    <div class="exam-card-actions">
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
                                <button class="exam-card-del" type="submit" title="Delete draft">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</main>

@include('partials.alerts')
</body>
</html>
