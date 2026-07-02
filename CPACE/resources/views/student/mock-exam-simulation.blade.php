<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mock Exam Simulation - CPACE CPA Reviewer</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #eef1f4;
            color: #1f2933;
        }

        .dashboard-container { min-height: 100vh; }

        .exam-simulation-page .main-content {
            margin-left: 0;
            min-height: 100vh;
            padding: 0;
            transition: margin-left 0.3s ease;
        }

        .exam-simulation-page .sidebar,
        .exam-simulation-page .student-bottom-nav,
        .exam-simulation-page .mobile-header,
        .exam-simulation-page .student-mobile-header,
        .exam-simulation-page .bottom-nav,
        .exam-simulation-page .more-drawer,
        .exam-simulation-page .more-drawer-overlay,
        .exam-simulation-page .mobile-app-header,
        .exam-simulation-page.mock-test-active .sidebar,
        .exam-simulation-page.mock-test-active .student-bottom-nav,
        .exam-simulation-page.mock-test-active .mobile-header,
        .exam-simulation-page.mock-test-active .student-mobile-header {
            display: none !important;
        }

        .exam-simulation-page.mock-test-active .bottom-nav,
        .exam-simulation-page.mock-test-active .more-drawer,
        .exam-simulation-page.mock-test-active .more-drawer-overlay,
        .exam-simulation-page.mock-test-active .mobile-app-header {
            display: none !important;
        }

        .exam-simulation-page.mock-test-active .main-content {
            margin-left: 0 !important;
            padding: 0;
        }

        .exam-simulation-page.mock-test-active .exam-shell {
            min-height: 100vh;
        }

        .exam-shell {
            position: relative;
            min-height: 100vh;
            display: grid;
            grid-template-rows: 1fr;
            gap: 0;
        }

        .topbar,
        .control-bar {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        }

        .exam-shell > .topbar {
            display: none;
        }

        .exam-shell.exam-lifted > .topbar {
            position: absolute;
            top: 16px;
            left: 16px;
            right: 16px;
            z-index: 30;
            display: flex;
            background: rgba(255, 255, 255, 0.92);
            border-color: rgba(255, 255, 255, 0.58);
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.22);
            animation: fadeLiftedChrome 0.28s ease both;
        }

        .exam-title {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .exam-title i {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: #7b1d1d;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .exam-name {
            font-size: 17px;
            font-weight: 700;
            color: #111827;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .exam-meta {
            font-size: 12px;
            color: #667085;
            margin-top: 2px;
        }

        .top-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(104px, auto));
            gap: 10px;
        }

        .stat-pill {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 9px 12px;
            background: #fbfcfd;
            min-width: 104px;
        }

        .stat-label {
            font-size: 11px;
            color: #667085;
            margin-bottom: 2px;
        }

        .stat-value {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
        }

        .stat-value.danger { color: #b42318; }
        .stat-value.good { color: #027a48; }

        .classroom-view {
            display: block;
            height: 100vh;
            min-height: 0;
            transition: filter 0.25s ease;
        }

        .exam-shell.exam-lifted .classroom-view {
            filter: brightness(0.45);
        }

        .classroom-stage {
            position: relative;
            min-height: 100vh;
            height: 100%;
            border-radius: 0;
            border: none;
            overflow: hidden;
            background: url('{{ asset('images/Classroom.png') }}') center / cover no-repeat;
            box-shadow: none;
        }

        .desk-view {
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: min(760px, 92%);
            height: 235px;
            pointer-events: none;
        }

        .exam-booklet {
            position: absolute;
            left: 50%;
            bottom: 34px;
            transform: translateX(-50%) rotate(-2deg);
            width: min(330px, 54%);
            min-height: 160px;
            border: none;
            border-radius: 6px;
            background: #fffdf7;
            color: #111827;
            padding: 20px;
            cursor: pointer;
            text-align: left;
            font-family: inherit;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.22);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            pointer-events: auto;
        }

        .exam-booklet:hover {
            transform: translateX(-50%) rotate(0deg) translateY(-4px);
            box-shadow: 0 18px 28px rgba(15, 23, 42, 0.26);
        }

        .booklet-kicker {
            display: block;
            color: #7b1d1d;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .booklet-title {
            display: block;
            max-width: 230px;
            font-size: 20px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 18px;
        }

        .booklet-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border-top: 1px solid #ece5d6;
            padding-top: 12px;
            font-size: 12px;
            color: #667085;
        }

        .desk-items {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .pencil {
            position: absolute;
            right: 17%;
            bottom: 82px;
            width: 160px;
            height: 10px;
            border-radius: 999px;
            background: linear-gradient(90deg, #fdb022 0 72%, #344054 72% 82%, #f4b8a4 82% 100%);
            transform: rotate(18deg);
            box-shadow: 0 8px 14px rgba(15, 23, 42, 0.18);
        }

        .eraser {
            position: absolute;
            left: 18%;
            bottom: 78px;
            width: 58px;
            height: 34px;
            border-radius: 7px;
            background: #f9a8d4;
            transform: rotate(-12deg);
            box-shadow: 0 8px 14px rgba(15, 23, 42, 0.18);
        }

        .meet-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 16px;
            min-height: 0;
        }

        #testView {
            position: absolute;
            inset: 118px 0 10px;
            z-index: 20;
            padding: 16px;
            border-radius: 8px;
            background: rgba(17, 24, 39, 0.22);
            backdrop-filter: blur(1px);
            grid-template-rows: minmax(0, 1fr) auto;
            overflow-y: auto;
            animation: testSlideUp 0.32s cubic-bezier(.2,.8,.2,1) both;
        }

        #testView .control-bar {
            grid-column: 1 / -1;
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(255, 255, 255, 0.55);
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.22);
            animation: fadeLiftedChrome 0.34s ease 0.08s both;
        }

        .exam-room {
            display: grid;
            grid-template-rows: minmax(0, 1fr);
            gap: 16px;
            min-width: 0;
        }

        .question-stage {
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 8px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 210px;
            min-height: 0;
            height: 100%;
            overflow: hidden;
            box-shadow: 0 18px 46px rgba(15, 23, 42, 0.32);
            animation: fadeLiftedPanel 0.34s ease 0.04s both;
        }

        .question-panel {
            padding: 24px;
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 0;
            overflow-y: auto;
        }

        .question-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 16px;
            border-bottom: 1px solid #eef0f2;
        }

        .question-number {
            font-size: 13px;
            color: #667085;
            font-weight: 600;
        }

        .difficulty-badge {
            background: #fff3cd;
            color: #92400e;
            border-radius: 999px;
            padding: 7px 11px;
            font-size: 11px;
            font-weight: 700;
        }

        .question-text {
            font-size: 20px;
            line-height: 1.55;
            font-weight: 600;
            color: #111827;
            margin: 24px 0;
        }

        .choices {
            display: grid;
            gap: 12px;
            margin-bottom: 22px;
        }

        .choice {
            min-height: 54px;
            border: 1px solid rgba(208, 213, 221, 0.9);
            background: rgba(255, 255, 255, 0.86);
            border-radius: 8px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: 0.18s ease;
            font-family: inherit;
            text-align: left;
        }

        .choice:hover,
        .choice.selected {
            border-color: #7b1d1d;
            background: #fff8f8;
        }

        .choice-key {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #f2f4f7;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: #475467;
            flex-shrink: 0;
        }

        .choice.selected .choice-key {
            background: #7b1d1d;
            color: #ffffff;
        }

        .question-actions {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-top: 18px;
            border-top: 1px solid #eef0f2;
            flex-shrink: 0;
        }

        .btn {
            border: none;
            border-radius: 8px;
            min-height: 42px;
            padding: 10px 15px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .btn-primary { background: #7b1d1d; color: #ffffff; }
        .btn-primary:hover { background: #641717; }
        .btn-soft { background: #f2f4f7; color: #344054; }
        .btn-soft:hover { background: #e4e7ec; }
        .btn-danger { background: #b42318; color: #ffffff; }
        .btn-danger:hover { background: #912018; }

        .question-map {
            border-left: 1px solid rgba(238, 240, 242, 0.9);
            background: rgba(251, 252, 253, 0.74);
            padding: 18px;
            overflow-y: auto;
        }

        .map-title {
            font-size: 12px;
            color: #667085;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .map-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
        }

        .map-cell {
            aspect-ratio: 1;
            border: 1px solid #d0d5dd;
            border-radius: 6px;
            background: #ffffff;
            color: #475467;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .map-cell.current { border-color: #7b1d1d; color: #7b1d1d; background: #fff6f6; }
        .map-cell.answered { background: #dcfae6; border-color: #75e0a7; color: #027a48; }

        .side-panel {
            display: grid;
            grid-template-rows: minmax(0, 1fr);
            gap: 16px;
            min-width: 0;
            min-height: 0;
        }

        .panel {
            background: rgba(255, 255, 255, 0.86);
            border: 1px solid rgba(255, 255, 255, 0.52);
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
            min-width: 0;
            min-height: 0;
            overflow-y: auto;
            animation: fadeLiftedPanel 0.34s ease 0.1s both;
        }

        .panel-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 12px;
        }

        .competitor-row,
        .rank-row,
        .feed-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #eef0f2;
            font-size: 12px;
        }

        .competitor-row:last-child,
        .rank-row:last-child,
        .feed-row:last-child { border-bottom: none; }

        .progress-line {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .progress-name {
            font-weight: 700;
            color: #111827;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .progress-sub {
            color: #667085;
            font-size: 11px;
        }

        .rank-score {
            font-weight: 700;
            color: #027a48;
            white-space: nowrap;
        }

        .feed {
            max-height: 230px;
            overflow: hidden;
        }

        .feed-row {
            justify-content: flex-start;
            color: #475467;
            animation: fadeIn 0.25s ease;
        }

        .feed-row i { color: #7b1d1d; width: 16px; }

        .stress-banner {
            border-radius: 8px;
            border: 1px solid #fedf89;
            background: #fffaeb;
            color: #92400e;
            padding: 12px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 12px;
        }

        .control-left,
        .control-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .control-chip {
            min-height: 38px;
            border: 1px solid #d0d5dd;
            border-radius: 999px;
            background: #ffffff;
            padding: 8px 12px;
            font-size: 12px;
            color: #475467;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .hidden { display: none !important; }

        .exam-frozen .exam-shell {
            filter: blur(5px) brightness(0.58);
            pointer-events: none;
            user-select: none;
        }

        .lock-overlay {
            position: fixed;
            inset: 0;
            z-index: 5000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, 0.62);
            backdrop-filter: blur(8px);
        }

        .lock-overlay.show {
            display: flex;
        }

        .lock-box {
            width: min(440px, 100%);
            border-radius: 8px;
            background: #ffffff;
            padding: 28px;
            text-align: center;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
        }

        .lock-icon {
            width: 58px;
            height: 58px;
            border-radius: 16px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            background: #fff3cd;
            color: #92400e;
        }

        .lock-icon.critical {
            background: #fee2e2;
            color: #b42318;
        }

        .lock-title {
            font-size: 22px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 8px;
        }

        .lock-message {
            color: #667085;
            font-size: 13px;
            line-height: 1.55;
            margin-bottom: 16px;
        }

        .violation-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .v-dot {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            background: #e5e7eb;
        }

        .v-dot.used { background: #f79009; }
        .v-dot.final { background: #b42318; }

        .violation-label {
            display: inline-block;
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 11px;
            font-weight: 800;
            margin-bottom: 18px;
            background: #fffaeb;
            color: #92400e;
        }

        .violation-label.critical {
            background: #fee2e2;
            color: #b42318;
        }

        .lock-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes testSlideUp {
            from { opacity: 0; transform: translateY(72px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes fadeLiftedChrome {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeLiftedPanel {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 1200px) {
            .topbar { align-items: stretch; flex-direction: column; }
            .top-stats { grid-template-columns: repeat(2, 1fr); }
            .meet-layout { grid-template-columns: 1fr; }
        }

        @media (max-height: 820px) {
            .exam-shell.exam-lifted > .topbar {
                top: 8px;
            }

            #testView {
                inset: 98px 0 8px;
            }

            .question-panel {
                padding: 20px;
            }

            .question-text {
                margin: 18px 0;
                font-size: 18px;
            }

            .choice {
                min-height: 48px;
                padding: 11px 14px;
            }
        }

        @media (max-width: 768px) {
            .exam-simulation-page .main-content {
                margin-left: 0;
                padding: 0;
            }

            .question-stage,
            .side-panel {
                grid-template-columns: 1fr;
            }

            .classroom-stage { min-height: 100vh; }

            .question-map {
                border-left: none;
                border-top: 1px solid #eef0f2;
                max-height: 230px;
            }

            .question-actions,
            .control-bar {
                align-items: stretch;
                flex-direction: column;
            }

            .control-left,
            .control-right {
                width: 100%;
            }

            .btn,
            .control-chip {
                flex: 1;
            }
        }

        @media (max-width: 520px) {
            .exam-simulation-page .main-content { padding: 0; }
            .top-stats,
            .side-panel { grid-template-columns: 1fr; }
            .exam-name { white-space: normal; }
            .question-text { font-size: 17px; }
            .map-grid { grid-template-columns: repeat(6, 1fr); }
            .exam-booklet { width: 72%; }
        }
    </style>
</head>
<body class="exam-simulation-page">
    <div class="dashboard-container">
        <main class="main-content">
            <div class="exam-shell">
                <div class="topbar">
                    <div class="exam-title">
                        <i class="fas fa-video"></i>
                        <div>
                            <div class="exam-name" id="examName">{{ request('exam', 'Full CPALE Mock Exam') }}</div>
                            <div class="exam-meta">Live simulation room · 1,000 examinees · board exam atmosphere</div>
                        </div>
                    </div>

                    <div class="top-stats">
                        <div class="stat-pill">
                            <div class="stat-label">Time Left</div>
                            <div class="stat-value danger" id="timeLeft">04:00:00</div>
                        </div>
                        <div class="stat-pill">
                            <div class="stat-label">Your Progress</div>
                            <div class="stat-value"><span id="yourQuestion">1</span>/70</div>
                        </div>
                        <div class="stat-pill">
                            <div class="stat-label">Live Takers</div>
                            <div class="stat-value good" id="liveTakers">1,000</div>
                        </div>
                        <div class="stat-pill">
                            <div class="stat-label">Current Rank</div>
                            <div class="stat-value" id="currentRank">#3</div>
                        </div>
                    </div>
                </div>

                <div class="classroom-view" id="classroomView">
                    <section class="classroom-stage" aria-label="Board exam classroom">
                        <div class="desk-view">
                            <button class="exam-booklet" type="button" id="openExamBtn">
                                <span class="booklet-kicker">Exam booklet</span>
                                <span class="booklet-title">{{ request('exam', 'Full CPALE Mock Exam') }}</span>
                                <span class="booklet-footer">
                                    <span>Click to lift test</span>
                                    <i class="fas fa-arrow-up-right-from-square"></i>
                                </span>
                            </button>
                            <div class="desk-items">
                                <span class="pencil"></span>
                                <span class="eraser"></span>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="meet-layout hidden" id="testView">
                    <section class="exam-room">
                        <div class="question-stage">
                            <div class="question-panel">
                                <div id="stressBanner" class="stress-banner hidden">
                                    <i class="fas fa-triangle-exclamation"></i>
                                    <span id="stressText">Candidates are submitting. Stay composed.</span>
                                </div>

                                <div class="question-header">
                                    <div>
                                        <div class="question-number">Question <span id="questionNo">1</span> of 70</div>
                                        <div class="exam-meta">Financial Accounting and Reporting</div>
                                    </div>
                                    <span class="difficulty-badge" id="difficultyBadge">Board-style item</span>
                                </div>

                                <div class="question-text" id="questionText">
                                    Which accounting principle requires expenses to be recognized in the same period as the revenues they helped generate?
                                </div>

                                <div class="choices" id="choices">
                                    <button class="choice" type="button"><span class="choice-key">A</span><span>Historical cost principle</span></button>
                                    <button class="choice" type="button"><span class="choice-key">B</span><span>Matching principle</span></button>
                                    <button class="choice" type="button"><span class="choice-key">C</span><span>Full disclosure principle</span></button>
                                    <button class="choice" type="button"><span class="choice-key">D</span><span>Monetary unit assumption</span></button>
                                </div>

                                <div class="question-actions">
                                    <button class="btn btn-soft" type="button" id="prevBtn"><i class="fas fa-arrow-left"></i> Previous</button>
                                    <div class="control-left">
                                        <button class="btn btn-soft" type="button" id="putDownBtn"><i class="fas fa-eye"></i> Put Test Down</button>
                                        <button class="btn btn-soft" type="button" id="flagBtn"><i class="fas fa-flag"></i> Flag</button>
                                        <button class="btn btn-primary" type="button" id="nextBtn">Save & Next <i class="fas fa-arrow-right"></i></button>
                                    </div>
                                </div>
                            </div>

                            <aside class="question-map">
                                <div class="map-title">Question Navigator</div>
                                <div class="map-grid" id="questionMap"></div>
                            </aside>
                        </div>
                    </section>

                    <aside class="side-panel">
                        <div class="panel">
                            <div class="panel-title">Current Rank</div>
                            <div id="rankList">
                                <div class="rank-row"><span>1 Candidate</span><span class="rank-score">99%</span></div>
                                <div class="rank-row"><span>2 Candidate</span><span class="rank-score">98%</span></div>
                                <div class="rank-row"><span>3 You</span><span class="rank-score">97%</span></div>
                            </div>

                            <div class="panel-title" style="margin-top:16px;">Live Pressure Feed</div>
                            <div class="feed" id="pressureFeed">
                                <div class="feed-row"><i class="fas fa-circle-info"></i> Waiting room locked. Exam has started.</div>
                                <div class="feed-row"><i class="fas fa-user-clock"></i> 1,000 takers connected.</div>
                            </div>
                        </div>
                    </aside>

                    <div class="control-bar">
                        <div class="control-left">
                            <span class="control-chip"><i class="fas fa-shield-halved"></i> Difficulty-driven pressure</span>
                            <span class="control-chip"><i class="fas fa-users"></i> 1,000 simulated takers</span>
                        </div>
                        <div class="control-right">
                            <a href="{{ route('mock-exams') }}" class="btn btn-soft"><i class="fas fa-arrow-left"></i> Back</a>
                            <button class="btn btn-soft" type="button" id="pauseBtn"><i class="fas fa-file-lines"></i> Lift Test</button>
                            <button class="btn btn-danger" type="button" id="submitBtn"><i class="fas fa-paper-plane"></i> Submit Exam</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="lock-overlay" id="lockOverlay">
        <div class="lock-box">
            <div class="lock-icon" id="lockIcon"><i class="fas fa-shield-alt"></i></div>
            <div class="lock-title" id="lockTitle">Mock Exam Frozen</div>
            <div class="lock-message" id="lockMessage">You left the exam environment. The timer is paused and locked until you return.</div>
            <div class="violation-bar">
                <div class="v-dot" id="mockVdot0"></div>
                <div class="v-dot" id="mockVdot1"></div>
                <div class="v-dot" id="mockVdot2"></div>
            </div>
            <div class="violation-label" id="mockViolationLabel">Violation <span id="mockVCount">1</span> of 3</div>
            <div class="lock-actions">
                <button class="btn btn-primary" type="button" id="resumeLockBtn"><i class="fas fa-play"></i> Resume Mock Exam</button>
                <a href="{{ route('mock-exams') }}" class="btn btn-soft" id="leaveLockBtn"><i class="fas fa-arrow-left"></i> Leave Exam</a>
            </div>
        </div>
    </div>

    <script>
        const totalQuestions = 70;
        const questions = [
            {
                topic: 'Financial Accounting and Reporting',
                text: 'Which accounting principle requires expenses to be recognized in the same period as the revenues they helped generate?',
                choices: ['Historical cost principle', 'Matching principle', 'Full disclosure principle', 'Monetary unit assumption'],
                difficulty: 'Easy'
            },
            {
                topic: 'Auditing',
                text: 'Which audit procedure provides the most reliable evidence about the existence of cash in bank?',
                choices: ['Inquiry with management', 'Bank confirmation', 'Review of prior-year working papers', 'Analytical comparison'],
                difficulty: 'Difficult'
            },
            {
                topic: 'Taxation',
                text: 'A taxpayer is generally required to substantiate deductible business expenses through which documentation?',
                choices: ['Oral explanation only', 'Official receipts and records', 'Estimated monthly summaries', 'Personal bank balance'],
                difficulty: 'Average'
            },
            {
                topic: 'Management Advisory Services',
                text: 'Which variance compares the actual quantity of materials used with the standard quantity allowed?',
                choices: ['Material price variance', 'Labor rate variance', 'Material usage variance', 'Sales volume variance'],
                difficulty: 'Very Difficult'
            }
        ];

        const state = {
            mode: 'easy',
            question: 1,
            candidateQuestion: 5,
            secondsLeft: 4 * 60 * 60,
            running: false,
            examStarted: false,
            answered: new Set(),
            selected: {},
            rank: 3,
            liveTakers: 1000,
            lastModeQuestion: null,
            feed: ['Waiting room locked. Exam has started.', '1,000 takers connected.']
        };

        const el = {
            examShell: document.querySelector('.exam-shell'),
            classroomView: document.getElementById('classroomView'),
            testView: document.getElementById('testView'),
            openExamBtn: document.getElementById('openExamBtn'),
            putDownBtn: document.getElementById('putDownBtn'),
            timeLeft: document.getElementById('timeLeft'),
            yourQuestion: document.getElementById('yourQuestion'),
            currentRank: document.getElementById('currentRank'),
            liveTakers: document.getElementById('liveTakers'),
            questionNo: document.getElementById('questionNo'),
            questionText: document.getElementById('questionText'),
            difficultyBadge: document.getElementById('difficultyBadge'),
            choices: document.getElementById('choices'),
            questionMap: document.getElementById('questionMap'),
            rankList: document.getElementById('rankList'),
            pressureFeed: document.getElementById('pressureFeed'),
            stressBanner: document.getElementById('stressBanner'),
            stressText: document.getElementById('stressText'),
            pauseBtn: document.getElementById('pauseBtn'),
            lockOverlay: document.getElementById('lockOverlay'),
            lockIcon: document.getElementById('lockIcon'),
            lockTitle: document.getElementById('lockTitle'),
            lockMessage: document.getElementById('lockMessage'),
            mockViolationLabel: document.getElementById('mockViolationLabel'),
            mockVCount: document.getElementById('mockVCount'),
            resumeLockBtn: document.getElementById('resumeLockBtn'),
            leaveLockBtn: document.getElementById('leaveLockBtn')
        };

        const MAX_VIOLATIONS = 3;
        let violations = 0;
        let frozen = false;
        let terminated = false;
        let intentionalLeave = false;
        let blurTimer = null;

        function isFullscreen() {
            return !!(document.fullscreenElement || document.webkitFullscreenElement);
        }

        function enterFullscreen() {
            if (isFullscreen()) return;
            const target = document.documentElement;
            const request = target.requestFullscreen || target.webkitRequestFullscreen;
            if (request) {
                try {
                    Promise.resolve(request.call(target)).catch(function() {});
                } catch (e) {}
            }
        }

        function exitFullscreen() {
            if (!isFullscreen()) return;
            const exit = document.exitFullscreen || document.webkitExitFullscreen;
            if (exit) {
                try {
                    Promise.resolve(exit.call(document)).catch(function() {});
                } catch (e) {}
            }
        }

        function formatTime(seconds) {
            const h = Math.floor(seconds / 3600).toString().padStart(2, '0');
            const m = Math.floor((seconds % 3600) / 60).toString().padStart(2, '0');
            const s = Math.floor(seconds % 60).toString().padStart(2, '0');
            return `${h}:${m}:${s}`;
        }

        function buildMap() {
            el.questionMap.innerHTML = '';
            for (let i = 1; i <= totalQuestions; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'map-cell';
                btn.textContent = i;
                btn.addEventListener('click', () => {
                    state.question = i;
                    render();
                });
                el.questionMap.appendChild(btn);
            }
        }

        function currentQuestionData() {
            return questions[(state.question - 1) % questions.length];
        }

        function modeForDifficulty(difficulty) {
            const value = difficulty.toLowerCase();

            if (value.includes('very') || value.includes('difficult') || value.includes('board')) {
                return 'pressure';
            }

            if (value.includes('average') || value.includes('moderate') || value.includes('computation')) {
                return 'normal';
            }

            return 'easy';
        }

        function syncMode(data) {
            const nextMode = modeForDifficulty(data.difficulty);
            const changedQuestion = state.lastModeQuestion !== state.question;
            state.mode = nextMode;

            if (changedQuestion) {
                state.lastModeQuestion = state.question;

                if (state.mode === 'normal') {
                    addFeed(`Question ${state.question} difficulty: ${data.difficulty}. Countdown pressure increased.`, 'fa-hourglass-half');
                }

                if (state.mode === 'pressure') {
                    const message = `Question ${state.question} difficulty: ${data.difficulty}. Pressure simulation activated.`;
                    addFeed(message, 'fa-bolt');
                    showStress(message);
                }
            }
        }

        function renderQuestion() {
            const data = currentQuestionData();
            syncMode(data);
            el.questionNo.textContent = state.question;
            el.yourQuestion.textContent = state.question;
            el.questionText.textContent = data.text;
            el.difficultyBadge.textContent = `${data.difficulty} difficulty`;
            document.querySelector('.question-header .exam-meta').textContent = data.topic;

            el.choices.innerHTML = '';
            data.choices.forEach((choice, index) => {
                const key = String.fromCharCode(65 + index);
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `choice ${state.selected[state.question] === key ? 'selected' : ''}`;
                btn.innerHTML = `<span class="choice-key">${key}</span><span>${choice}</span>`;
                btn.addEventListener('click', () => {
                    state.selected[state.question] = key;
                    state.answered.add(state.question);
                    render();
                });
                el.choices.appendChild(btn);
            });
        }

        function renderMap() {
            document.querySelectorAll('.map-cell').forEach((cell, index) => {
                const number = index + 1;
                cell.classList.toggle('current', number === state.question);
                cell.classList.toggle('answered', state.answered.has(number));
            });
        }

        function renderCompetitors() {
            const yourAccuracy = Math.max(74, 98 - Math.floor(state.answered.size / 8));
            const candidateAccuracy = Math.max(88, 99 - Math.floor(state.candidateQuestion / 26));
            state.rank = state.mode === 'pressure'
                ? Math.max(3, Math.min(27, 3 + Math.floor((state.candidateQuestion - state.question) / 2)))
                : Math.max(3, Math.min(9, 3 + Math.floor((state.candidateQuestion - state.question) / 8)));

            el.currentRank.textContent = `#${state.rank}`;
            el.liveTakers.textContent = state.liveTakers.toLocaleString();

            const secondRank = state.mode === 'easy' ? 96 : 98;
            el.rankList.innerHTML = `
                <div class="rank-row"><span>1 Candidate</span><span class="rank-score">${candidateAccuracy}%</span></div>
                <div class="rank-row"><span>2 Candidate</span><span class="rank-score">${secondRank}%</span></div>
                <div class="rank-row"><span>${state.rank} You</span><span class="rank-score">${yourAccuracy}%</span></div>
            `;
        }

        function addFeed(message, icon = 'fa-circle-info') {
            if (state.mode === 'easy' && state.feed.length > 3) return;
            state.feed.unshift(message);
            state.feed = state.feed.slice(0, 8);
            el.pressureFeed.innerHTML = state.feed.map(item => (
                `<div class="feed-row"><i class="fas ${icon}"></i> ${item}</div>`
            )).join('');
        }

        function showStress(message) {
            if (state.mode === 'easy') {
                el.stressBanner.classList.add('hidden');
                return;
            }
            el.stressText.textContent = message;
            el.stressBanner.classList.remove('hidden');
            window.clearTimeout(showStress.timeout);
            showStress.timeout = window.setTimeout(() => el.stressBanner.classList.add('hidden'), 5200);
        }

        function render() {
            el.timeLeft.textContent = formatTime(state.secondsLeft);
            renderQuestion();
            renderMap();
            renderCompetitors();
        }

        function freezeExam() {
            if (!state.examStarted || terminated || frozen) return;
            frozen = true;
            state.running = false;
            document.body.classList.add('exam-frozen');
        }

        function unfreezeExam() {
            if (terminated) return;
            frozen = false;
            state.running = true;
            document.body.classList.remove('exam-frozen');
            el.lockOverlay.classList.remove('show');
        }

        function showViolationOverlay() {
            if (!state.examStarted || terminated) return;

            violations++;
            for (let i = 0; i < MAX_VIOLATIONS; i++) {
                const dot = document.getElementById('mockVdot' + i);
                dot.classList.remove('used', 'final');
                if (i < violations) dot.classList.add(i === MAX_VIOLATIONS - 1 ? 'final' : 'used');
            }

            el.mockVCount.textContent = violations;

            if (violations >= MAX_VIOLATIONS) {
                terminated = true;
                state.running = false;
                document.body.classList.remove('mock-test-active');
                el.lockIcon.className = 'lock-icon critical';
                el.lockIcon.innerHTML = '<i class="fas fa-ban"></i>';
                el.lockTitle.textContent = 'Mock Exam Terminated';
                el.lockMessage.innerHTML = 'You left the exam environment <strong>' + violations + ' times</strong>. This mock exam has been locked.';
                el.mockViolationLabel.className = 'violation-label critical';
                el.mockViolationLabel.textContent = 'Maximum violations reached';
                el.resumeLockBtn.style.display = 'none';
                addFeed('Mock exam terminated after maximum lockout violations.', 'fa-ban');
                el.lockOverlay.classList.add('show');
                return;
            }

            const remaining = MAX_VIOLATIONS - violations;
            el.lockIcon.className = 'lock-icon';
            el.lockIcon.innerHTML = '<i class="fas fa-shield-alt"></i>';
            el.lockTitle.textContent = 'Mock Exam Frozen';
            el.lockMessage.innerHTML = 'You left the exam environment.<br><span style="color:#b42318;font-weight:700;">' + remaining + ' more violation' + (remaining > 1 ? 's' : '') + ' will terminate this mock exam.</span>';
            el.mockViolationLabel.className = 'violation-label';
            el.mockViolationLabel.innerHTML = 'Violation <span id="mockVCount">' + violations + '</span> of ' + MAX_VIOLATIONS;
            el.mockVCount = document.getElementById('mockVCount');
            el.resumeLockBtn.style.display = '';
            el.lockOverlay.classList.add('show');
        }

        function resumeLockedExam() {
            enterFullscreen();
            unfreezeExam();
            el.pauseBtn.innerHTML = '<i class="fas fa-pause"></i> Pause';
            addFeed('Mock exam resumed after lockout.', 'fa-play');
        }

        function handleFullscreenChange() {
            if (state.examStarted && !terminated && !intentionalLeave && !isFullscreen() && !frozen) {
                freezeExam();
                showViolationOverlay();
            }
        }

        function openExam() {
            document.body.classList.add('mock-test-active');
            state.examStarted = true;
            state.running = true;
            intentionalLeave = false;
            enterFullscreen();
            el.examShell.classList.add('exam-lifted');
            el.testView.classList.remove('hidden');
            el.pauseBtn.innerHTML = '<i class="fas fa-pause"></i> Pause';
            addFeed('You lifted the test booklet. Timer started.', 'fa-file-lines');
            render();
        }

        function putTestDown() {
            el.testView.classList.add('hidden');
            el.examShell.classList.remove('exam-lifted');
            document.body.classList.remove('mock-test-active');
            addFeed(`You put the test down at Question ${state.question}.`, 'fa-eye');
        }

        function tick() {
            if (!state.running) return;

            state.secondsLeft = Math.max(0, state.secondsLeft - 1);

            if (state.mode !== 'easy' && Math.random() > 0.62) {
                state.candidateQuestion = Math.min(totalQuestions, state.candidateQuestion + 1);
            }

            if (state.mode === 'pressure' && Math.random() > 0.72) {
                state.liveTakers = Math.max(910, state.liveTakers - Math.floor(Math.random() * 4));
            }

            if (state.mode === 'normal' && state.secondsLeft % 45 === 0) {
                addFeed('Countdown check: pace yourself before the next section.', 'fa-hourglass-half');
            }

            if (state.mode === 'pressure' && state.secondsLeft % 18 === 0) {
                const events = [
                    'Candidates submitting in other rooms.',
                    'Current rank changed after AI candidate update.',
                    'Time warning: your pace is being compared live.',
                    'Another candidate moved ahead by two questions.'
                ];
                const event = events[Math.floor(Math.random() * events.length)];
                addFeed(event, 'fa-bolt');
                showStress(event);
            }

            if (state.secondsLeft === 15 * 60 || state.secondsLeft === 5 * 60) {
                showStress(`${Math.floor(state.secondsLeft / 60)} minutes remaining.`);
                addFeed(`${Math.floor(state.secondsLeft / 60)} minutes remaining.`, 'fa-triangle-exclamation');
            }

            render();
        }

        document.getElementById('nextBtn').addEventListener('click', () => {
            state.answered.add(state.question);
            state.question = Math.min(totalQuestions, state.question + 1);
            if (state.mode === 'pressure' && state.question % 5 === 0) {
                addFeed(`You reached Question ${state.question}. Other candidates are moving quickly.`, 'fa-users');
            }
            render();
        });

        document.getElementById('prevBtn').addEventListener('click', () => {
            state.question = Math.max(1, state.question - 1);
            render();
        });

        document.getElementById('flagBtn').addEventListener('click', () => {
            addFeed(`Question ${state.question} flagged for review.`, 'fa-flag');
        });

        el.openExamBtn.addEventListener('click', openExam);
        el.putDownBtn.addEventListener('click', putTestDown);

        el.pauseBtn.addEventListener('click', () => {
            if (!state.examStarted) {
                openExam();
                return;
            }

            state.running = !state.running;
            el.pauseBtn.innerHTML = state.running ? '<i class="fas fa-pause"></i> Pause' : '<i class="fas fa-play"></i> Resume';
            addFeed(state.running ? 'Simulation resumed.' : 'Simulation paused.', state.running ? 'fa-play' : 'fa-pause');
        });

        document.getElementById('submitBtn').addEventListener('click', () => {
            intentionalLeave = true;
            state.running = false;
            showStress('Exam submitted. Results simulation complete.');
            addFeed(`You submitted with ${state.answered.size}/${totalQuestions} questions answered.`, 'fa-paper-plane');
            el.pauseBtn.innerHTML = '<i class="fas fa-play"></i> Resume';
            document.body.classList.remove('mock-test-active');
            exitFullscreen();
        });

        el.resumeLockBtn.addEventListener('click', resumeLockedExam);
        el.leaveLockBtn.addEventListener('click', () => { intentionalLeave = true; });

        document.addEventListener('fullscreenchange', handleFullscreenChange);
        document.addEventListener('webkitfullscreenchange', handleFullscreenChange);

        document.addEventListener('visibilitychange', () => {
            if (!state.examStarted || terminated || intentionalLeave) return;
            if (document.hidden) {
                freezeExam();
            } else if (frozen) {
                showViolationOverlay();
            }
        });

        window.addEventListener('blur', () => {
            if (!state.examStarted || terminated || intentionalLeave) return;
            blurTimer = setTimeout(() => {
                if (!document.hidden && !frozen) {
                    freezeExam();
                    showViolationOverlay();
                }
            }, 300);
        });

        window.addEventListener('focus', () => clearTimeout(blurTimer));

        document.addEventListener('contextmenu', e => {
            if (state.examStarted && !terminated) e.preventDefault();
        });

        document.addEventListener('keydown', e => {
            if (!state.examStarted || terminated) return;
            const combo = e.ctrlKey || e.metaKey;
            if ((combo && ['t', 'w', 'n'].includes(e.key.toLowerCase())) || e.key === 'F12') {
                e.preventDefault();
            }
        });

        window.addEventListener('beforeunload', e => {
            if (!state.examStarted || intentionalLeave || terminated) return;
            e.preventDefault();
            e.returnValue = 'Your mock exam is still in progress.';
            return e.returnValue;
        });

        buildMap();
        render();
        window.setInterval(tick, 1000);
    </script>
</body>
</html>
