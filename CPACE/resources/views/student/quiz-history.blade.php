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
        :root {
            --primary: #7B1D1D;
            --primary-hover: #6a1818;
            --primary-light: #f5e8e8;
            --primary-mid: #c0392b;
            --accent-red: #c0392b;
            --sidebar-bg: linear-gradient(180deg, #a12626 0%, #7B1D1D 34%, #3d0c0c 74%, #1a0a0a 100%);
            --white: #ffffff;
            --gray-100: #f8f9fa;
            --gray-200: #f0f0f0;
            --gray-300: #e0e0e0;
            --gray-500: #999999;
            --gray-700: #555555;
            --gray-900: #333333;
            --green: #10b981;
            --blue: #3b82f6;
            --orange: #f59e0b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f4f5f7;
            color: #333;
        }

        .main-content { margin-left: 220px; padding: 30px 40px; min-height: 100vh; transition: margin-left .3s; }
        .sidebar.collapsed ~ .main-content { margin-left: 70px; }

        /* ─── TOP BAR ─── */
        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; gap:20px; }
        .top-bar-left { display:flex; align-items:center; gap:14px; }
        .top-bar-right { display:flex; align-items:center; gap:14px; }

        .search-wrap { position:relative; }
        .search-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#aaa; font-size:14px; }
        .search-wrap input {
            width:280px; padding:10px 14px 10px 36px;
            border:1px solid #e0e0e0; border-radius:24px;
            font-size:13px; font-family:'Poppins',sans-serif;
            background:white; color:#555; outline:none;
        }
        .search-wrap input:focus { border-color:var(--primary); }
        .search-wrap input::placeholder { color:#bbb; }

        .notif-btn {
            position:relative; width:40px; height:40px;
            border:none; background:white; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:17px; color:#555; cursor:pointer;
            box-shadow:0 1px 4px rgba(0,0,0,0.08); text-decoration:none;
        }
        .notif-btn:hover { background:#f0f0f0; }
        .badge {
            position:absolute; top:-3px; right:-3px;
            width:18px; height:18px; background:var(--accent-red);
            color:white; border-radius:50%; font-size:10px; font-weight:700;
            display:flex; align-items:center; justify-content:center;
        }
        .profile-avatar {
            width:40px; height:40px; background:var(--primary);
            border-radius:10px; border:none; color:white;
            font-weight:700; font-size:14px; cursor:pointer;
            font-family:'Poppins',sans-serif; transition:background 0.2s;
        }
        .profile-avatar:hover { background:var(--primary-hover); }

        .header-dropdown-wrap { position:relative; }
        .dropdown-menu {
            position:absolute; top:calc(100% + 8px); right:0;
            background:white; border:1px solid #e5e7eb; border-radius:10px;
            min-width:185px; box-shadow:0 6px 20px rgba(0,0,0,0.12);
            display:none; z-index:2000;
        }
        .dropdown-menu.active { display:block; }
        .dropdown-menu a, .dropdown-menu button {
            display:flex; align-items:center; gap:10px;
            padding:11px 16px; font-size:13px; font-family:'Poppins',sans-serif;
            text-decoration:none; color:#333; background:none; border:none;
            width:100%; text-align:left; cursor:pointer; transition:background 0.2s;
            border-bottom:1px solid #f5f5f5;
        }
        .dropdown-menu a:last-child, .dropdown-menu form:last-child button { border-bottom:none; }
        .dropdown-menu a:hover, .dropdown-menu button:hover { background:#f9f9f9; }
        .dropdown-menu a i, .dropdown-menu button i { color:var(--primary); width:16px; text-align:center; }
        .dropdown-menu .logout-btn { color:#e53e3e; }
        .dropdown-menu .logout-btn i { color:#e53e3e; }

        /* HEADER */
        .header { font-size:28px; font-weight:600; color:#333; }
        .header-subtitle { color:#999; font-size:14px; margin-top:2px; }
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
            .main-content { padding: 20px 16px !important; }
            .page-header { font-size: 22px; }
            .history-card { flex-wrap: wrap; gap: 12px; padding: 16px; }
            .hc-action span { display: none; }
            .top-bar { flex-direction:column; align-items:flex-start; gap:12px; }
            .top-bar-right { width:100%; flex-wrap:wrap; }
            .search-wrap { flex:1; }
            .search-wrap input { width:100%; }
        }
    </style>
</head>
<body>
    @include('partials.sidebar', ['active' => 'quizzes'])
    @include('partials.student-bottom-nav', ['active' => 'quizzes'])
    @include('partials.student-mobile-header')

    <main class="main-content">
        <div class="top-bar">
            <div class="top-bar-left">
                <div>
                    <div class="header-title">Quiz History</div>
                    <div class="header-subtitle">Every quiz session you've completed. Tap any to review your answers.</div>
                </div>
            </div>
            <div class="top-bar-right">
                <div class="search-wrap gs-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" data-gs="true" placeholder="Search topics, questions...">
                </div>
                <a class="notif-btn" href="{{ route('messages.index') }}" title="Messages" aria-label="Messages">
                    <i class="fas fa-comment-dots"></i>
                    @if($unreadMessages > 0)<span class="badge">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>@endif
                </a>
                <a class="notif-btn" href="{{ route('notifications.index') }}" title="Notifications" aria-label="Notifications">
                    <i class="fas fa-bell"></i>
                    @if($unreadNotifications > 0)<span class="badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>@endif
                </a>
                <div class="header-dropdown-wrap">
                    <button class="profile-avatar" id="profileBtn">@include('partials.avatar-content')</button>
                    <div class="dropdown-menu" id="profileDropdown">
                        <a href="#"><i class="fas fa-user"></i> Profile Settings</a>
                        <a href="#"><i class="fas fa-chart-line"></i> My Progress</a>
                        <a href="#"><i class="fas fa-question-circle"></i> Help &amp; Support</a>
                        <form method="POST" action="{{ route('logout') }}" style="margin:0;padding:0;">
                            @csrf
                            <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
                        </form>
                    </div>
                </div>
            </div>
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
                        $passMark = (int) ($s->subject->passing_threshold ?? 75);
                        $passing = $score >= $passMark;
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
                            <div class="hc-score-lbl">{{ $passing ? 'Passed' : 'Needs '.$passMark.'%' }}</div>
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
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar && localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
            }

            const profileBtn = document.getElementById('profileBtn');
            const profileDrop = document.getElementById('profileDropdown');
            if (profileBtn && profileDrop) {
                profileBtn.addEventListener('click', e => {
                    e.stopPropagation();
                    profileDrop.classList.toggle('active');
                });
                document.addEventListener('click', () => profileDrop.classList.remove('active'));
                profileDrop.addEventListener('click', e => e.stopPropagation());
            }
        });

        function filterHistory(btn, type) {
            document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('#historyList .history-card').forEach(card => {
                card.style.display = (type === 'all' || card.dataset.type === type) ? '' : 'none';
            });
        }
    </script>
    @include('partials.global-search')
</body>
</html>
