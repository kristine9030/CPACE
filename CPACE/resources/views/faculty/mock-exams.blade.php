<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mock Exams - CPACE Faculty</title>
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
            <div class="page-title">Mock Exams</div>
            <div class="page-sub">Build a full-length exam for one of your subjects, then send it to the Program Chair to review and publish.</div>
        </div>
        <div class="topbar-right">
            @include('partials.topbar-actions')
        </div>
    </div>

    <div class="banner banner-info">
        <i class="fas fa-circle-info"></i>
        <div>
            One mock exam covers <strong>one subject only</strong> — subjects are never combined.
            You can only build for subjects you're assigned to, and everyone assigned to a subject
            can collaborate on the same exam.
        </div>
    </div>

    @if($cards->isEmpty())
        <div class="card empty">
            <i class="fas fa-folder-open"></i>
            <h3>No subjects assigned yet</h3>
            <p>The Program Chair needs to assign you a subject before you can build a mock exam.</p>
        </div>
    @else
        <div class="subject-grid">
            @foreach($cards as $card)
                <a class="subject-card" href="{{ route('faculty.mock-exams.subject', $card['subject']->id) }}"
                   style="--sc-base:{{ $card['theme']['base'] }}; --sc-dark:{{ $card['theme']['dark'] }};">
                    <div class="sc-banner">
                        <i class="fas {{ $card['icon'] }} sc-illus"></i>
                        <div class="sc-code">{{ $card['subject']->code }}</div>
                        <div class="sc-name">{{ $card['subject']->name }}</div>
                    </div>
                    <div class="sc-icon"><i class="fas {{ $card['icon'] }}"></i></div>
                    <div class="sc-body">
                        <div class="sc-stats">
                            <span><strong>{{ $card['total'] }}</strong> total</span>
                            @if($card['draft'] > 0)<span>{{ $card['draft'] }} draft</span>@endif
                            @if($card['for_review'] > 0)<span style="color:var(--amber);font-weight:600;">{{ $card['for_review'] }} in review</span>@endif
                            @if($card['published'] > 0)<span style="color:var(--green);font-weight:600;">{{ $card['published'] }} published</span>@endif
                        </div>
                        @if($card['next'])
                            <div style="margin-top:10px;font-size:12px;color:var(--muted);">
                                <i class="fas fa-calendar-day"></i>
                                Next sitting {{ $card['next']->scheduled_at->format('M j, Y · g:i A') }}
                            </div>
                        @elseif($card['total'] === 0)
                            <div style="margin-top:10px;font-size:12px;color:var(--muted);">No mock exam built yet</div>
                        @endif
                    </div>
                    <div class="sc-foot">Open <i class="fas fa-arrow-right"></i></div>
                </a>
            @endforeach
        </div>
    @endif
</main>

@include('partials.alerts')
</body>
</html>
