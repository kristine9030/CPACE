<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz History - CPACE CPA Reviewer</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            color: #333;
        }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 20px;
            flex-wrap: wrap;
        }
        .header-title { font-size: 28px; font-weight: 600; color: #333; }
        .header-subtitle { color: #999; font-size: 14px; }
        .back-link {
            display: inline-flex; align-items: center; gap: 8px;
            background: #7B1D1D; color: #fff; text-decoration: none;
            padding: 10px 18px; border-radius: 8px;
            font-size: 13px; font-weight: 600; transition: background .2s;
        }
        .back-link:hover { background: #6a1818; }

        /* FILTER CHIPS */
        .filter-row { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px; }
        .filter-chip {
            background: #fff; border: 2px solid #e5e7eb; border-radius: 20px;
            padding: 7px 16px; font-size: 13px; font-weight: 600; color: #555;
            cursor: pointer; transition: all .2s; font-family: 'Poppins', sans-serif;
        }
        .filter-chip:hover { border-color: #c0392b; }
        .filter-chip.active { border-color: #c0392b; background: #fff9f9; color: #c0392b; }

        /* HISTORY TABLE / LIST */
        .history-list { display: flex; flex-direction: column; gap: 12px; }

        .history-card {
            background: #fff; border-radius: 12px;
            padding: 18px 22px;
            border: 1px solid #f0f0f0;
            box-shadow: 0 2px 6px rgba(0,0,0,.04);
            display: flex; align-items: center; gap: 18px;
            transition: all .2s; text-decoration: none; color: inherit;
        }
        .history-card:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(0,0,0,.09); border-color: #e5d3d3; }

        .hc-icon {
            width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }

        .hc-main { flex: 1; min-width: 0; }
        .hc-title {
            font-size: 15px; font-weight: 700; color: #1a1a1a;
            display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
        }
        .hc-meta { font-size: 12px; color: #999; margin-top: 4px; display: flex; gap: 14px; flex-wrap: wrap; }
        .hc-meta span { display: inline-flex; align-items: center; gap: 5px; }

        .badge {
            font-size: 10px; font-weight: 700; padding: 2px 9px; border-radius: 20px;
            text-transform: uppercase; letter-spacing: .4px;
        }
        .badge.training { background: #d1fae5; color: #065f46; }
        .badge.testing  { background: #dbeafe; color: #1e40af; }
        .badge.mode     { background: #f3e8ff; color: #6b21a8; }

        .hc-score { text-align: center; flex-shrink: 0; min-width: 70px; }
        .hc-score-pct { font-size: 24px; font-weight: 700; line-height: 1; }
        .hc-score-lbl { font-size: 10px; color: #999; margin-top: 3px; }
        .hc-score.pass .hc-score-pct { color: #10b981; }
        .hc-score.fail .hc-score-pct { color: #c0392b; }

        .hc-action {
            flex-shrink: 0;
            display: inline-flex; align-items: center; gap: 6px;
            color: #c0392b; font-size: 13px; font-weight: 600;
        }

        /* EMPTY STATE */
        .empty-state {
            background: #fff; border-radius: 14px; padding: 60px 30px;
            text-align: center; color: #999; box-shadow: 0 2px 8px rgba(0,0,0,.05);
        }
        .empty-state i { font-size: 48px; color: #e5d3d3; margin-bottom: 16px; }
        .empty-state h3 { font-size: 18px; color: #555; margin-bottom: 8px; font-weight: 600; }
        .empty-state p { font-size: 13px; margin-bottom: 20px; }
        .empty-state a {
            display: inline-flex; align-items: center; gap: 8px;
            background: #7B1D1D; color: #fff; text-decoration: none;
            padding: 12px 22px; border-radius: 8px; font-size: 13px; font-weight: 600;
        }

        /* PAGINATION */
        .pagination-wrap {
            margin-top: 28px;
            display: flex; align-items: center; justify-content: center; gap: 16px;
        }
        .page-btn {
            display: inline-flex; align-items: center; gap: 7px;
            background: #fff; border: 2px solid #e5e7eb; border-radius: 8px;
            padding: 9px 16px; font-size: 13px; font-weight: 600; color: #555;
            text-decoration: none; transition: all .2s; font-family: 'Poppins', sans-serif;
        }
        .page-btn:hover { border-color: #c0392b; color: #c0392b; }
        .page-btn.disabled { opacity: .45; pointer-events: none; }
        .page-info { font-size: 13px; color: #999; font-weight: 600; }

        @media (max-width: 768px) {
            .main-content { padding: 80px 16px 90px !important; }
            .header-title { font-size: 22px; }
            .history-card { flex-wrap: wrap; gap: 12px; padding: 16px; }
            .hc-action span { display: none; }
        }
    </style>
</head>
<body>
    @include('partials.sidebar', ['active' => 'quizzes'])
    @include('partials.student-bottom-nav', ['active' => 'quizzes'])
    @include('partials.student-mobile-header')

    <main class="main-content">
        <div class="header">
            <div>
                <div class="header-title">Quiz History</div>
                <div class="header-subtitle">Every quiz session you've completed. Tap any to review your answers.</div>
            </div>
            <a href="{{ route('adaptive-quizzes') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Quizzes
            </a>
        </div>

        @if($sessions->count() > 0)
            @php
                $subjectColors = [
                    'FAR' => '#4A90E2', 'AFAR' => '#17A2B8', 'MS' => '#F39C12',
                    'TAX' => '#27AE60', 'AUD' => '#c0392b', 'RFBT' => '#9B59B6',
                ];
                $modeLabels = [
                    'adaptive' => 'Adaptive', 'topic' => 'Topic Focus',
                    'timed' => 'Timed', 'challenge' => 'Challenge',
                ];
            @endphp

            <div class="filter-row">
                <button type="button" class="filter-chip active" data-filter="all" onclick="filterHistory(this, 'all')">All</button>
                <button type="button" class="filter-chip" data-filter="testing" onclick="filterHistory(this, 'testing')">Testing</button>
                <button type="button" class="filter-chip" data-filter="training" onclick="filterHistory(this, 'training')">Training</button>
            </div>

            <div class="history-list" id="historyList">
                @foreach($sessions as $s)
                    @php
                        $code    = $s->subject->code ?? 'Quiz';
                        $color   = $subjectColors[$code] ?? '#7B1D1D';
                        $score   = (int) round($s->score_percent);
                        $passing = $score >= 75;
                        $mins    = intdiv((int) $s->duration_secs, 60);
                        $secs    = (int) $s->duration_secs % 60;
                    @endphp
                    <a href="{{ route('quiz.results', $s->id) }}" class="history-card" data-type="{{ $s->session_type }}">
                        <div class="hc-icon" style="background: {{ $color }}1a; color: {{ $color }};">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="hc-main">
                            <div class="hc-title">
                                {{ $s->subject->name ?? 'Quiz' }} ({{ $code }})
                                <span class="badge {{ $s->session_type === 'training' ? 'training' : 'testing' }}">{{ ucfirst($s->session_type) }}</span>
                                <span class="badge mode">{{ $modeLabels[$s->mode] ?? ucfirst($s->mode) }}</span>
                            </div>
                            <div class="hc-meta">
                                <span><i class="far fa-calendar"></i> {{ $s->completed_at?->format('M d, Y · g:i A') }}</span>
                                <span><i class="far fa-check-circle"></i> {{ $s->correct_answers }}/{{ $s->total_items }} correct</span>
                                <span><i class="far fa-clock"></i> {{ $mins }}m {{ $secs }}s</span>
                            </div>
                        </div>
                        <div class="hc-score {{ $passing ? 'pass' : 'fail' }}">
                            <div class="hc-score-pct">{{ $score }}%</div>
                            <div class="hc-score-lbl">Score</div>
                        </div>
                        <div class="hc-action">
                            <span>Review</span> <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                @endforeach
            </div>

            @if($sessions->hasPages())
                <div class="pagination-wrap">
                    <a href="{{ $sessions->previousPageUrl() ?? '#' }}" class="page-btn {{ $sessions->onFirstPage() ? 'disabled' : '' }}">
                        <i class="fas fa-chevron-left"></i> Prev
                    </a>
                    <span class="page-info">Page {{ $sessions->currentPage() }} of {{ $sessions->lastPage() }}</span>
                    <a href="{{ $sessions->nextPageUrl() ?? '#' }}" class="page-btn {{ $sessions->hasMorePages() ? '' : 'disabled' }}">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="fas fa-clock-rotate-left"></i>
                <h3>No quiz history yet</h3>
                <p>Once you complete a quiz, it will appear here so you can review your answers anytime.</p>
                <a href="{{ route('adaptive-quizzes') }}"><i class="fas fa-play"></i> Start a Quiz</a>
            </div>
        @endif
    </main>

    <script>
        function filterHistory(btn, type) {
            document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('#historyList .history-card').forEach(card => {
                card.style.display = (type === 'all' || card.dataset.type === type) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
