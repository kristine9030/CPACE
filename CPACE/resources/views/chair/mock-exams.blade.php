<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mock Exams - CPACE Program Chair</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.mock-exam-styles')
    <style>
        .folder-badge { position:absolute; right:16px; top:16px; background:#fff; color:var(--amber);
                        font-size:11px; font-weight:700; padding:4px 10px; border-radius:999px;
                        box-shadow:0 2px 6px rgba(0,0,0,.18); z-index:3; }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'mock-exams'])

<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">Mock Exams</div>
            <div class="page-sub">Review what faculty built, then publish it to release the redeem code to students.</div>
        </div>
        <div class="topbar-right">@include('partials.topbar-actions')</div>
    </div>

    @if($pendingTotal > 0)
        <div class="banner banner-warn">
            <i class="fas fa-bell"></i>
            <div><strong>{{ $pendingTotal }} {{ Str::plural('exam', $pendingTotal) }}</strong> waiting for your review.</div>
        </div>
    @endif

    <div class="banner banner-info">
        <i class="fas fa-key"></i>
        <div>
            Publishing generates <strong>one redeem code per exam day</strong> — every subject sitting on that
            date shares it. Once published an exam is locked and can no longer be edited by anyone.
        </div>
    </div>

    <div class="subject-grid">
        @foreach($cards as $card)
            <a class="subject-card" href="{{ route('chair.mock-exams.subject', $card['subject']->id) }}"
               style="--sc-base:{{ $card['theme']['base'] }}; --sc-dark:{{ $card['theme']['dark'] }};position:relative;">
                @if($card['for_review'] > 0)
                    <span class="folder-badge">{{ $card['for_review'] }} to review</span>
                @endif
                <div class="sc-banner">
                    <i class="fas fa-folder sc-illus"></i>
                    <div class="sc-code">{{ $card['subject']->code }}</div>
                    <div class="sc-name">{{ $card['subject']->name }}</div>
                </div>
                <div class="sc-icon"><i class="fas {{ $card['icon'] }}"></i></div>
                <div class="sc-body">
                    <div class="sc-stats">
                        <span><strong>{{ $card['total'] }}</strong> total</span>
                        @if($card['draft'] > 0)<span>{{ $card['draft'] }} draft</span>@endif
                        @if($card['published'] > 0)<span style="color:var(--green);font-weight:600;">{{ $card['published'] }} published</span>@endif
                        @if($card['closed'] > 0)<span>{{ $card['closed'] }} closed</span>@endif
                    </div>
                    @if($card['total'] === 0)
                        <div style="margin-top:10px;font-size:12px;color:var(--muted);">Nothing submitted yet</div>
                    @endif
                </div>
                <div class="sc-foot">Open folder <i class="fas fa-arrow-right"></i></div>
            </a>
        @endforeach
    </div>
</main>

@include('partials.alerts')
</body>
</html>
