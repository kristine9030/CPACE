<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Setup - CPACE CPA Reviewer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #7B1D1D;
            --primary-hover: #6a1818;
            --primary-light: #f5e8e8;
            --accent: #c0392b;
            --gold: #ffd76a;
            --gold-2: #f5a623;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f0f0;
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .setup-shell {
            width: 100%; max-width: 1080px;
            min-height: 660px;
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 24px 60px rgba(50,10,10,.22);
            overflow: hidden;
            display: grid;
            grid-template-columns: 340px 1fr;
        }

        /* ── LEFT BRAND / STEP RAIL ── */
        .brand {
            background: linear-gradient(160deg, #1a0a0a 0%, #3d0c0c 30%, #7B1D1D 68%, #a12626 100%);
            color: #fff;
            padding: 34px 30px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .brand::after {
            content:''; position:absolute; bottom:-70px; left:-40px;
            width: 260px; height: 260px; border-radius:50%;
            background: radial-gradient(circle, rgba(255,215,106,.10) 0%, transparent 65%);
        }
        .b-shape { position:absolute; pointer-events:none; }
        .b-shape.s1 { top:60px; right:-20px; width:90px; height:90px; border:2px solid rgba(255,215,106,.14); border-radius:22px; transform:rotate(28deg); }
        .b-shape.s2 { top:220px; right:40px; width:46px; height:46px; background:rgba(192,57,43,.22); border-radius:50%; }
        .b-shape.s3 { bottom:120px; left:-10px; width:70px; height:70px; border:2px solid rgba(255,255,255,.08); border-radius:50%; }

        .brand-logo { display:flex; align-items:center; gap:11px; position:relative; z-index:1; margin-bottom: 30px; }
        .brand-logo .bl-mark {
            width: 46px; height:46px; border-radius:12px; background:rgba(255,255,255,.14);
            display:flex; align-items:center; justify-content:center; font-size:22px; color:var(--gold);
        }
        .brand-logo strong { font-size:20px; font-weight:800; letter-spacing:.5px; display:block; line-height:1; }
        .brand-logo small { font-size:10px; color:rgba(255,255,255,.6); font-style:italic; }

        .brand-head { position:relative; z-index:1; margin-bottom: 26px; }
        .brand-head h1 { font-size: 21px; font-weight:700; line-height:1.3; margin-bottom:7px; }
        .brand-head p { font-size:12.5px; color:rgba(255,255,255,.78); line-height:1.6; }

        /* step list */
        .step-rail { list-style:none; position:relative; z-index:1; flex:1; }
        .step-rail li { display:flex; gap:13px; align-items:flex-start; padding:9px 0; position:relative; }
        .step-rail li::before {
            content:''; position:absolute; left:15px; top:34px; bottom:-3px; width:2px;
            background:rgba(255,255,255,.12);
        }
        .step-rail li:last-child::before { display:none; }
        .sr-dot {
            width:32px; height:32px; border-radius:50%; flex-shrink:0;
            display:flex; align-items:center; justify-content:center;
            font-size:12px; font-weight:700;
            background:rgba(255,255,255,.10); color:rgba(255,255,255,.55);
            border:2px solid rgba(255,255,255,.14); transition:all .3s; position:relative; z-index:1;
        }
        .sr-txt .t { font-size:12.5px; font-weight:600; color:rgba(255,255,255,.6); transition:color .3s; }
        .sr-txt .d { font-size:10.5px; color:rgba(255,255,255,.4); }
        .step-rail li.active .sr-dot { background:var(--gold); color:#5a1515; border-color:var(--gold); box-shadow:0 0 0 4px rgba(255,215,106,.2); }
        .step-rail li.active .sr-txt .t { color:#fff; }
        .step-rail li.done .sr-dot { background:#10b981; color:#fff; border-color:#10b981; }
        .step-rail li.done .sr-txt .t { color:rgba(255,255,255,.85); }

        .brand-foot { position:relative; z-index:1; font-size:11px; color:rgba(255,255,255,.55); margin-top:18px; display:flex; align-items:center; gap:8px; }

        /* ── RIGHT PANEL ── */
        .panel { padding: 34px 42px; display:flex; flex-direction:column; overflow-y:auto; }
        .panel-top { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; }
        .step-count { font-size:12px; color:#999; font-weight:600; }
        .step-count b { color:var(--primary); }
        .skip-link { font-size:12px; color:#bbb; text-decoration:none; }
        .skip-link:hover { color:var(--primary); }

        .progress-track { height:7px; background:#efe6e6; border-radius:10px; overflow:hidden; margin:8px 0 26px; }
        .progress-fill { height:100%; border-radius:10px; background:linear-gradient(90deg, var(--gold), var(--gold-2)); width:16%; transition:width .4s cubic-bezier(.4,0,.2,1); }

        .step-panel { display:none; animation: fadeUp .4s ease; flex:1; }
        .step-panel.active { display:flex; flex-direction:column; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(14px);} to {opacity:1; transform:translateY(0);} }

        .step-emoji {
            width:64px; height:64px; border-radius:18px; margin-bottom:16px;
            display:flex; align-items:center; justify-content:center; font-size:28px;
            background:radial-gradient(circle at 35% 28%, #ff8a6b, var(--accent) 82%);
            color:#fff; box-shadow:0 8px 20px rgba(192,57,43,.35);
        }
        .step-h { font-size:23px; font-weight:700; color:#1a1a1a; margin-bottom:6px; }
        .step-sub { font-size:13px; color:#888; line-height:1.6; margin-bottom:24px; max-width:460px; }

        label { display:block; font-size:12.5px; font-weight:600; color:#444; margin-bottom:7px; }
        .fld { margin-bottom:18px; }
        .fld input[type=text], .fld input[type=email], .fld input[type=password], .fld input[type=number], .fld select {
            width:100%; padding:12px 14px; border:1.6px solid #e6dede; border-radius:10px;
            font-size:13.5px; font-family:'Poppins',sans-serif; color:#333; background:#fff; transition:border-color .2s, box-shadow .2s;
        }
        .fld input:focus, .fld select:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(123,29,29,.09); }
        .fld input:read-only { background:#f7f3f3; color:#888; }
        .fld .hint { font-size:11px; color:#aaa; margin-top:6px; }
        .fld-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }

        .pw-wrap { position:relative; }
        .pw-wrap .eye { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:#bbb; cursor:pointer; font-size:15px; }
        .pw-wrap .eye:hover { color:var(--primary); }
        .strength { display:flex; gap:5px; margin-top:9px; }
        .strength span { flex:1; height:5px; border-radius:5px; background:#eee; transition:background .25s; }
        .strength-label { font-size:11px; margin-top:6px; font-weight:600; }
        .pw-rules { list-style:none; margin-top:10px; display:grid; grid-template-columns:1fr 1fr; gap:5px 14px; }
        .pw-rules li { font-size:11px; color:#aaa; display:flex; align-items:center; gap:6px; }
        .pw-rules li i { font-size:10px; }
        .pw-rules li.ok { color:#059669; }
        .pw-rules li.ok i { color:#059669; }

        /* chip choices */
        .chips { display:flex; flex-wrap:wrap; gap:9px; }
        .chip {
            padding:9px 15px; border:1.6px solid #e6dede; border-radius:22px;
            font-size:12.5px; font-weight:500; color:#666; cursor:pointer; user-select:none;
            transition:all .15s; background:#fff; display:inline-flex; align-items:center; gap:7px;
        }
        .chip:hover { border-color:var(--primary); color:var(--primary); }
        .chip.sel { background:var(--primary); border-color:var(--primary); color:#fff; }
        .chip.sel i { color:#fff; }

        /* subject cards */
        .subj-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:11px; }
        .subj-card {
            display:flex; align-items:center; gap:12px; padding:13px 15px;
            border:1.6px solid #e6dede; border-radius:13px; cursor:pointer; transition:all .15s; position:relative;
        }
        .subj-card:hover { border-color:var(--primary); background:var(--primary-light); }
        .subj-card.sel { border-color:var(--primary); background:var(--primary-light); }
        .subj-card .sc-ic { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:15px; font-weight:700; color:#fff; flex-shrink:0; }
        .subj-card .sc-name { font-size:13px; font-weight:600; color:#1a1a1a; }
        .subj-card .sc-desc { font-size:10.5px; color:#999; }
        .subj-card .sc-check { margin-left:auto; width:22px; height:22px; border-radius:50%; border:2px solid #ddd; display:flex; align-items:center; justify-content:center; color:transparent; transition:all .15s; font-size:11px; }
        .subj-card.sel .sc-check { background:var(--primary); border-color:var(--primary); color:#fff; }

        /* goal / countdown */
        .goal-preview {
            margin-top:20px; background:linear-gradient(150deg,#fdf1f1,#fbe3e3); border:1.5px solid #f2c7c7;
            border-radius:16px; padding:20px 22px; text-align:center; position:relative; overflow:hidden;
        }
        .goal-preview .gp-label { font-size:11px; font-weight:700; letter-spacing:1px; color:var(--accent); text-transform:uppercase; }
        .goal-preview .gp-days { font-size:44px; font-weight:800; color:var(--primary); line-height:1.1; }
        .goal-preview .gp-sub { font-size:12px; color:#a07070; }
        .goal-preview .gp-rocket { position:absolute; right:16px; bottom:20px; font-size:30px; color:var(--accent); transform:rotate(-20deg); opacity:.85; }

        .intensity-opts { display:grid; grid-template-columns:repeat(3,1fr); gap:11px; }
        .int-card { border:1.6px solid #e6dede; border-radius:13px; padding:15px 12px; text-align:center; cursor:pointer; transition:all .15s; }
        .int-card:hover { border-color:var(--primary); }
        .int-card.sel { border-color:var(--primary); background:var(--primary-light); }
        .int-card .ic-ic { font-size:20px; color:var(--primary); margin-bottom:7px; }
        .int-card .ic-t { font-size:13px; font-weight:600; color:#1a1a1a; }
        .int-card .ic-d { font-size:10.5px; color:#999; margin-top:2px; }

        /* review summary */
        .summary-list { display:flex; flex-direction:column; gap:1px; background:#f0eaea; border-radius:14px; overflow:hidden; border:1px solid #f0eaea; }
        .summary-row { display:flex; align-items:center; gap:13px; background:#fff; padding:13px 16px; }
        .summary-row .sm-ic { width:36px; height:36px; border-radius:10px; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }
        .summary-row .sm-k { font-size:11px; color:#999; }
        .summary-row .sm-v { font-size:13px; font-weight:600; color:#1a1a1a; }

        /* footer nav */
        .step-nav { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-top:26px; padding-top:20px; border-top:1px solid #f2ecec; }
        .btn {
            display:inline-flex; align-items:center; gap:8px; padding:12px 26px; border-radius:11px;
            font-size:13.5px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s;
        }
        .btn-primary { background:linear-gradient(135deg, var(--accent), var(--primary)); color:#fff; box-shadow:0 6px 16px rgba(123,29,29,.28); }
        .btn-primary:hover { transform:translateY(-2px); box-shadow:0 9px 22px rgba(123,29,29,.38); }
        .btn-ghost { background:#f3eeee; color:#666; }
        .btn-ghost:hover { background:#e9e0e0; }
        .btn-ghost.hidden { visibility:hidden; }

        /* verifying overlay */
        .verify-overlay {
            position:fixed; inset:0; background:rgba(26,8,8,.7); backdrop-filter:blur(5px);
            display:none; align-items:center; justify-content:center; z-index:9000;
        }
        .verify-overlay.show { display:flex; }
        .verify-box { background:#fff; border-radius:22px; padding:44px 46px; text-align:center; max-width:360px; animation:fadeUp .4s ease; }
        .verify-spinner {
            width:64px; height:64px; margin:0 auto 20px; border-radius:50%;
            border:5px solid #f0e6e6; border-top-color:var(--primary); animation:spin .8s linear infinite;
        }
        @keyframes spin { to { transform:rotate(360deg);} }
        .verify-box h3 { font-size:18px; color:#1a1a1a; margin-bottom:6px; }
        .verify-box p { font-size:12.5px; color:#999; }
        .verify-check { width:64px; height:64px; margin:0 auto 20px; border-radius:50%; background:linear-gradient(160deg,#34d67e,#16a34a); color:#fff; display:none; align-items:center; justify-content:center; font-size:30px; }

        @media (max-width: 860px) {
            .setup-shell { grid-template-columns:1fr; }
            .brand { display:none; }
            .panel { padding:26px 22px; }
        }
        @media (max-width:520px) {
            .fld-row, .subj-grid, .intensity-opts { grid-template-columns:1fr; }
            .step-h { font-size:20px; }
        }
    </style>
</head>
<body>
@php
    $u = auth()->user();
    $profile = $profile ?? $u?->studentProfile;
    $fullName = $u?->name ?? 'Juan Dela Cruz';
    $email = $u?->email ?? 'juan.delacruz@cpace.edu';
    $first = trim(explode(' ', $fullName)[0] ?? 'there');
@endphp

<div class="setup-shell">
    <!-- LEFT RAIL -->
    <aside class="brand">
        <span class="b-shape s1"></span><span class="b-shape s2"></span><span class="b-shape s3"></span>
        <div class="brand-logo">
            <div class="bl-mark"><i class="fas fa-graduation-cap"></i></div>
            <div><strong>CPACE</strong><small>CPA Reviewer</small></div>
        </div>
        <div class="brand-head">
            <h1>Welcome, {{ $first }}! 👋<br>Let's set up your review space.</h1>
            <p>A few quick steps and CPACE will be tailored to how <em>you</em> plan to pass the boards.</p>
        </div>
        <ul class="step-rail" id="stepRail">
            <li data-step="1" class="active"><div class="sr-dot">1</div><div class="sr-txt"><div class="t">Secure account</div><div class="d">Set your own password</div></div></li>
            <li data-step="2"><div class="sr-dot">2</div><div class="sr-txt"><div class="t">Your details</div><div class="d">Section &amp; student info</div></div></li>
            <li data-step="3"><div class="sr-dot">3</div><div class="sr-txt"><div class="t">Your CPA goal</div><div class="d">Target exam date</div></div></li>
            <li data-step="4"><div class="sr-dot">4</div><div class="sr-txt"><div class="t">Study rhythm</div><div class="d">When you'll review</div></div></li>
            <li data-step="5"><div class="sr-dot">5</div><div class="sr-txt"><div class="t">Focus subjects</div><div class="d">Where to start</div></div></li>
            <li data-step="6"><div class="sr-dot">6</div><div class="sr-txt"><div class="t">All set</div><div class="d">Review &amp; finish</div></div></li>
        </ul>
        <div class="brand-foot"><i class="fas fa-shield-halved" style="color:var(--gold);"></i> Your details are private and secure.</div>
    </aside>

    <!-- RIGHT PANEL -->
    <section class="panel">
        <div class="panel-top">
            <div class="step-count">Step <b id="stepNum">1</b> of 6</div>
            <span class="skip-link" style="cursor:default;"><i class="fas fa-lock" style="font-size:10px;"></i> Required setup</span>
        </div>
        <div class="progress-track"><div class="progress-fill" id="progressFill"></div></div>

        @if ($errors->any())
            <div style="background:#fde8e8; border:1px solid #f5c2c2; color:#991b1b; border-radius:11px; padding:12px 15px; font-size:12.5px; margin-bottom:16px; display:flex; gap:9px; align-items:flex-start;">
                <i class="fas fa-circle-exclamation" style="margin-top:2px;"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <form id="setupForm" method="POST" action="{{ route('account-setup.store') }}">
            @csrf
            {{-- JS-managed chip/card selections are mirrored into these before submit --}}
            <input type="hidden" name="study_days" id="hidStudyDays" value="{{ old('study_days') }}">
            <input type="hidden" name="study_time" id="hidStudyTime" value="{{ old('study_time') }}">
            <input type="hidden" name="study_intensity" id="hidIntensity" value="{{ old('study_intensity') }}">
            <input type="hidden" name="focus_subjects" id="hidSubjects" value="{{ old('focus_subjects') }}">


            <!-- STEP 1: SECURE ACCOUNT -->
            <div class="step-panel active" data-step="1">
                <div class="step-emoji" style="background:radial-gradient(circle at 35% 28%,#63c0ff,#1f7bff 82%); box-shadow:0 8px 20px rgba(31,123,255,.32);"><i class="fas fa-lock"></i></div>
                <h2 class="step-h">Secure your account</h2>
                <p class="step-sub">You logged in with a one-time password from your program chair. Choose a new password only you know.</p>

                <div class="fld">
                    <label>New password</label>
                    <div class="pw-wrap">
                        <input type="password" id="pw" name="password" placeholder="Create a strong password">
                        <button type="button" class="eye" onclick="togglePw('pw',this)"><i class="fas fa-eye"></i></button>
                    </div>
                    <div class="strength" id="strength"><span></span><span></span><span></span><span></span></div>
                    <div class="strength-label" id="strengthLabel" style="color:#bbb;">Password strength</div>
                    <ul class="pw-rules" id="pwRules">
                        <li data-rule="len"><i class="fas fa-circle"></i> 8+ characters</li>
                        <li data-rule="upper"><i class="fas fa-circle"></i> Uppercase letter</li>
                        <li data-rule="lower"><i class="fas fa-circle"></i> Lowercase letter</li>
                        <li data-rule="num"><i class="fas fa-circle"></i> Number</li>
                    </ul>
                </div>
                <div class="fld">
                    <label>Confirm password</label>
                    <div class="pw-wrap">
                        <input type="password" id="pw2" name="password_confirmation" placeholder="Re-type your password">
                        <button type="button" class="eye" onclick="togglePw('pw2',this)"><i class="fas fa-eye"></i></button>
                    </div>
                    <div class="hint" id="matchHint"></div>
                </div>
            </div>

            <!-- STEP 2: YOUR DETAILS -->
            <div class="step-panel" data-step="2">
                <div class="step-emoji"><i class="fas fa-id-card"></i></div>
                <h2 class="step-h">Tell us about you</h2>
                <p class="step-sub">Confirm your account and let us know your section so your chair and faculty can find you.</p>

                <div class="fld-row">
                    <div class="fld"><label>Full name</label><input type="text" value="{{ $fullName }}" readonly></div>
                    <div class="fld"><label>GSuite email</label><input type="email" value="{{ $email }}" readonly></div>
                </div>
                <div class="fld-row">
                    <div class="fld"><label>Student number</label><input type="text" name="student_number" value="{{ old('student_number', $profile?->student_number) }}" placeholder="e.g. 2026-0001"></div>
                    <div class="fld"><label>Section / Batch</label><input type="text" name="section" value="{{ old('section', $profile?->section) }}" placeholder="e.g. BSA-4A"></div>
                </div>
                <div class="fld-row">
                    <div class="fld">
                        <label>Year level</label>
                        <select name="year_level">
                            <option value="">Select year</option>
                            @for ($y=1;$y<=6;$y++)<option value="{{ $y }}" @selected((string) old('year_level', $profile?->year_level) === (string) $y)>Year {{ $y }}</option>@endfor
                        </select>
                    </div>
                    <div class="fld"><label>Mobile number <span style="color:#bbb;font-weight:400;">(optional)</span></label><input type="text" name="mobile" value="{{ old('mobile', $profile?->mobile) }}" placeholder="09XX XXX XXXX"></div>
                </div>
            </div>

            <!-- STEP 3: CPA GOAL -->
            <div class="step-panel" data-step="3">
                <div class="step-emoji" style="background:radial-gradient(circle at 35% 28%,#ffe24a,#ffab00 82%); box-shadow:0 8px 20px rgba(255,171,0,.32);"><i class="fas fa-bullseye"></i></div>
                <h2 class="step-h">When are you taking the boards?</h2>
                <p class="step-sub">We'll build your countdown and pace your review plan around this target.</p>

                <div class="fld-row">
                    <div class="fld">
                        <label>Target exam month</label>
                        <select id="examMonth" name="exam_month">
                            <option value="">Select month</option>
                            @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $m)
                                <option value="{{ $i+1 }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fld">
                        <label>Target year</label>
                        <select id="examYear" name="exam_year">
                            <option value="">Select year</option>
                            @for($yr=2026;$yr<=2029;$yr++)<option value="{{ $yr }}">{{ $yr }}</option>@endfor
                        </select>
                    </div>
                </div>

                <div class="goal-preview" id="goalPreview" style="display:none;">
                    <div class="gp-label">Countdown to your exam</div>
                    <div class="gp-days" id="gpDays">—</div>
                    <div class="gp-sub" id="gpSub">days to prepare</div>
                    <i class="fas fa-rocket gp-rocket"></i>
                </div>
            </div>

            <!-- STEP 4: STUDY RHYTHM -->
            <div class="step-panel" data-step="4">
                <div class="step-emoji" style="background:radial-gradient(circle at 35% 28%,#57f593,#08d15c 82%); box-shadow:0 8px 20px rgba(8,209,92,.3);"><i class="fas fa-calendar-check"></i></div>
                <h2 class="step-h">Plan your study rhythm</h2>
                <p class="step-sub">Pick the days you plan to review and how intense you want to go. You can change this anytime.</p>

                <div class="fld">
                    <label>Which days will you review?</label>
                    <div class="chips" id="dayChips">
                        @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d)
                            <span class="chip" data-val="{{ $d }}">{{ $d }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="fld">
                    <label>Preferred study time</label>
                    <div class="chips" id="timeChips">
                        <span class="chip" data-val="Morning"><i class="fas fa-sun"></i> Morning</span>
                        <span class="chip" data-val="Afternoon"><i class="fas fa-cloud-sun"></i> Afternoon</span>
                        <span class="chip" data-val="Evening"><i class="fas fa-moon"></i> Evening</span>
                        <span class="chip" data-val="Late night"><i class="fas fa-star"></i> Late night</span>
                    </div>
                </div>
                <div class="fld">
                    <label>How intense is your review?</label>
                    <div class="intensity-opts" id="intensity">
                        <div class="int-card" data-val="Light"><div class="ic-ic"><i class="fas fa-feather"></i></div><div class="ic-t">Light</div><div class="ic-d">~1 hr/day</div></div>
                        <div class="int-card" data-val="Steady"><div class="ic-ic"><i class="fas fa-gauge"></i></div><div class="ic-t">Steady</div><div class="ic-d">~2-3 hrs/day</div></div>
                        <div class="int-card" data-val="Intense"><div class="ic-ic"><i class="fas fa-fire"></i></div><div class="ic-t">Intense</div><div class="ic-d">4+ hrs/day</div></div>
                    </div>
                </div>
            </div>

            <!-- STEP 5: FOCUS SUBJECTS -->
            <div class="step-panel" data-step="5">
                <div class="step-emoji" style="background:radial-gradient(circle at 35% 28%,#d488ff,#9412f5 82%); box-shadow:0 8px 20px rgba(148,18,245,.3);"><i class="fas fa-layer-group"></i></div>
                <h2 class="step-h">Which subjects need the most focus?</h2>
                <p class="step-sub">Choose the areas you want CPACE to prioritize first. We'll surface quizzes and materials for these.</p>

                <div class="subj-grid" id="subjGrid">
                    @php $subs = [
                        ['FAR','Financial Acctg &amp; Reporting','#2563eb'],
                        ['AFAR','Advanced Fin. Acctg','#0891b2'],
                        ['MS','Management Services','#7c3aed'],
                        ['TAX','Taxation','#059669'],
                        ['AUD','Auditing','#db2777'],
                        ['RFBT','Reg. Framework (Law)','#d97706'],
                    ]; @endphp
                    @foreach($subs as $s)
                        <div class="subj-card" data-val="{{ $s[0] }}">
                            <div class="sc-ic" style="background:{{ $s[2] }};">{{ $s[0] }}</div>
                            <div><div class="sc-name">{{ $s[0] }}</div><div class="sc-desc">{!! $s[1] !!}</div></div>
                            <div class="sc-check"><i class="fas fa-check"></i></div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- STEP 6: REVIEW -->
            <div class="step-panel" data-step="6">
                <div class="step-emoji" style="background:radial-gradient(circle at 35% 28%,#ffd76a,#f5a623 82%); box-shadow:0 8px 20px rgba(245,166,35,.35);"><i class="fas fa-flag-checkered"></i></div>
                <h2 class="step-h">You're all set, {{ $first }}! 🎉</h2>
                <p class="step-sub">Here's your personalized review setup. Confirm to unlock your CPACE dashboard.</p>

                <div class="summary-list">
                    <div class="summary-row"><div class="sm-ic"><i class="fas fa-user"></i></div><div><div class="sm-k">Section</div><div class="sm-v" id="sumSection">—</div></div></div>
                    <div class="summary-row"><div class="sm-ic"><i class="fas fa-bullseye"></i></div><div><div class="sm-k">Target exam</div><div class="sm-v" id="sumExam">—</div></div></div>
                    <div class="summary-row"><div class="sm-ic"><i class="fas fa-calendar-check"></i></div><div><div class="sm-k">Study days &amp; time</div><div class="sm-v" id="sumRhythm">—</div></div></div>
                    <div class="summary-row"><div class="sm-ic"><i class="fas fa-layer-group"></i></div><div><div class="sm-k">Focus subjects</div><div class="sm-v" id="sumSubjects">—</div></div></div>
                </div>
            </div>

            <!-- NAV -->
            <div class="step-nav">
                <button type="button" class="btn btn-ghost hidden" id="backBtn"><i class="fas fa-arrow-left"></i> Back</button>
                <button type="button" class="btn btn-primary" id="nextBtn">Continue <i class="fas fa-arrow-right"></i></button>
            </div>
        </form>
    </section>
</div>

<!-- VERIFYING OVERLAY -->
<div class="verify-overlay" id="verifyOverlay">
    <div class="verify-box">
        <div class="verify-spinner" id="verifySpinner"></div>
        <div class="verify-check" id="verifyCheck"><i class="fas fa-check"></i></div>
        <h3 id="verifyTitle">Verifying your setup…</h3>
        <p id="verifyMsg">Creating your personalized review space</p>
    </div>
</div>

<script>
(function () {
    let step = 1;
    const total = 6;
    const panels = document.querySelectorAll('.step-panel');
    const rail = document.querySelectorAll('#stepRail li');
    const fill = document.getElementById('progressFill');
    const stepNum = document.getElementById('stepNum');
    const backBtn = document.getElementById('backBtn');
    const nextBtn = document.getElementById('nextBtn');

    const state = { days:[], time:'', intensity:'', subjects:[] };

    function render() {
        panels.forEach(p => p.classList.toggle('active', +p.dataset.step === step));
        rail.forEach(li => {
            const s = +li.dataset.step;
            li.classList.toggle('active', s === step);
            li.classList.toggle('done', s < step);
            const dot = li.querySelector('.sr-dot');
            dot.innerHTML = s < step ? '<i class="fas fa-check"></i>' : s;
        });
        fill.style.width = (step/total*100) + '%';
        stepNum.textContent = step;
        backBtn.classList.toggle('hidden', step === 1);
        nextBtn.innerHTML = step === total
            ? 'Complete Setup <i class="fas fa-circle-check"></i>'
            : 'Continue <i class="fas fa-arrow-right"></i>';
        if (step === total) buildSummary();
        document.querySelector('.panel').scrollTop = 0;
    }

    function validate() {
        if (step === 1) {
            const pw = document.getElementById('pw').value, pw2 = document.getElementById('pw2').value;
            if (!strongEnough(pw)) { flash('Please choose a stronger password (8+ chars, upper, lower, number).'); return false; }
            if (pw !== pw2) { flash('Passwords do not match.'); return false; }
        }
        return true;
    }

    nextBtn.addEventListener('click', () => {
        if (!validate()) return;
        if (step < total) { step++; render(); }
        else finish();
    });
    backBtn.addEventListener('click', () => { if (step > 1) { step--; render(); } });

    /* ---- password strength ---- */
    const pw = document.getElementById('pw');
    pw.addEventListener('input', () => {
        const v = pw.value;
        const rules = { len:v.length>=8, upper:/[A-Z]/.test(v), lower:/[a-z]/.test(v), num:/[0-9]/.test(v) };
        let score = 0;
        Object.entries(rules).forEach(([k,ok]) => {
            const li = document.querySelector(`#pwRules li[data-rule="${k}"]`);
            li.classList.toggle('ok', ok);
            li.querySelector('i').className = ok ? 'fas fa-circle-check' : 'fas fa-circle';
            if (ok) score++;
        });
        const bars = document.querySelectorAll('#strength span');
        const colors = ['#ef4444','#f59e0b','#eab308','#10b981'];
        const labels = ['Weak','Fair','Good','Strong'];
        bars.forEach((b,i) => b.style.background = i < score ? colors[score-1] : '#eee');
        const lbl = document.getElementById('strengthLabel');
        lbl.textContent = v ? labels[Math.max(0,score-1)] + ' password' : 'Password strength';
        lbl.style.color = v ? colors[Math.max(0,score-1)] : '#bbb';
    });
    document.getElementById('pw2').addEventListener('input', () => {
        const h = document.getElementById('matchHint');
        const a = pw.value, b = document.getElementById('pw2').value;
        if (!b) { h.textContent=''; return; }
        h.textContent = a===b ? '✓ Passwords match' : '✗ Passwords do not match';
        h.style.color = a===b ? '#059669' : '#ef4444';
    });
    function strongEnough(v){ return v.length>=8 && /[A-Z]/.test(v) && /[a-z]/.test(v) && /[0-9]/.test(v); }

    /* ---- goal countdown ---- */
    const em = document.getElementById('examMonth'), ey = document.getElementById('examYear');
    function updateGoal() {
        const gp = document.getElementById('goalPreview');
        if (!em.value || !ey.value) { gp.style.display='none'; return; }
        const target = new Date(+ey.value, +em.value-1, 15);
        const days = Math.max(0, Math.round((target - new Date())/86400000));
        document.getElementById('gpDays').textContent = days;
        gp.style.display='block';
    }
    em.addEventListener('change', updateGoal); ey.addEventListener('change', updateGoal);

    /* ---- multi-select chips ---- */
    document.getElementById('dayChips').addEventListener('click', e => {
        const c = e.target.closest('.chip'); if(!c) return; c.classList.toggle('sel');
        state.days = [...document.querySelectorAll('#dayChips .chip.sel')].map(x=>x.dataset.val);
    });
    document.getElementById('timeChips').addEventListener('click', e => {
        const c = e.target.closest('.chip'); if(!c) return;
        document.querySelectorAll('#timeChips .chip').forEach(x=>x.classList.remove('sel'));
        c.classList.add('sel'); state.time = c.dataset.val;
    });
    document.getElementById('intensity').addEventListener('click', e => {
        const c = e.target.closest('.int-card'); if(!c) return;
        document.querySelectorAll('#intensity .int-card').forEach(x=>x.classList.remove('sel'));
        c.classList.add('sel'); state.intensity = c.dataset.val;
    });
    document.getElementById('subjGrid').addEventListener('click', e => {
        const c = e.target.closest('.subj-card'); if(!c) return; c.classList.toggle('sel');
        state.subjects = [...document.querySelectorAll('#subjGrid .subj-card.sel')].map(x=>x.dataset.val);
    });

    /* ---- summary ---- */
    function buildSummary() {
        const section = document.querySelector('input[name=section]').value || 'Not set';
        document.getElementById('sumSection').textContent = section;
        const mName = em.options[em.selectedIndex]?.text;
        document.getElementById('sumExam').textContent = (em.value && ey.value) ? `${mName} ${ey.value}` : 'Not set';
        document.getElementById('sumRhythm').textContent =
            (state.days.length ? state.days.join(', ') : 'No days') +
            (state.time ? ' · ' + state.time : '') +
            (state.intensity ? ' · ' + state.intensity : '');
        document.getElementById('sumSubjects').textContent = state.subjects.length ? state.subjects.join(', ') : 'None selected';
    }

    /* ---- finish: mirror state → verify → real submit (server redirects to welcome) ---- */
    function finish() {
        document.getElementById('hidStudyDays').value = state.days.join(',');
        document.getElementById('hidStudyTime').value = state.time;
        document.getElementById('hidIntensity').value = state.intensity;
        document.getElementById('hidSubjects').value = state.subjects.join(',');

        document.getElementById('verifyOverlay').classList.add('show');
        setTimeout(() => { document.getElementById('setupForm').submit(); }, 1400);
    }

    /* ---- helpers ---- */
    window.togglePw = function(id, btn){
        const el = document.getElementById(id), ic = btn.querySelector('i');
        if (el.type==='password'){ el.type='text'; ic.className='fas fa-eye-slash'; }
        else { el.type='password'; ic.className='fas fa-eye'; }
    };
    let flashTimer;
    function flash(msg){
        let t = document.getElementById('flashToast');
        if (!t){ t = document.createElement('div'); t.id='flashToast';
            t.style.cssText='position:fixed;top:22px;left:50%;transform:translateX(-50%);background:#c0392b;color:#fff;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:9999;box-shadow:0 8px 22px rgba(192,57,43,.4);';
            document.body.appendChild(t); }
        t.textContent = msg; t.style.display='block';
        clearTimeout(flashTimer); flashTimer = setTimeout(()=>t.style.display='none', 3000);
    }

    /* ---- re-hydrate selections after a validation error ---- */
    function hydrate() {
        const days = (document.getElementById('hidStudyDays').value || '').split(',').filter(Boolean);
        days.forEach(d => { const c = document.querySelector(`#dayChips .chip[data-val="${d}"]`); if (c) c.classList.add('sel'); });
        state.days = days;
        const t = document.getElementById('hidStudyTime').value;
        if (t) { const c = document.querySelector(`#timeChips .chip[data-val="${t}"]`); if (c) c.classList.add('sel'); state.time = t; }
        const it = document.getElementById('hidIntensity').value;
        if (it) { const c = document.querySelector(`#intensity .int-card[data-val="${it}"]`); if (c) c.classList.add('sel'); state.intensity = it; }
        const subs = (document.getElementById('hidSubjects').value || '').split(',').filter(Boolean);
        subs.forEach(s => { const c = document.querySelector(`#subjGrid .subj-card[data-val="${s}"]`); if (c) c.classList.add('sel'); });
        state.subjects = subs;
    }

    hydrate();
    render();
})();
</script>
</body>
</html>
