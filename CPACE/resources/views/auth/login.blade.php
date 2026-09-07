<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CPACE CPA Reviewer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href='https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            /* Same values the landing page uses, so the two read as one product. */
            --maroon:        #7B1D1D;   /* landing --red        */
            --maroon-dark:   #5A1414;   /* landing --red-dark   */
            --maroon-bright: #A12626;   /* landing --red-bright */
            --ink:           #14283E;   /* landing --navy       */
            --navy-soft:     #1F3550;
            --gray:          #66768A;   /* landing --body       */
            --muted:         #93A0AE;
            --border:        #E5E9ED;   /* landing --line       */
            --soft:          #F4F5F7;
            --rose:          #EFC6C6;   /* the on-dark accent used in the dark bands */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', ui-sans-serif, system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
            position: relative;
            background: #0d0505;
            overflow-x: hidden;
        }

        /* ── Dark campus backdrop ── */
        .login-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            background: url('{{ asset("images/CPACE login bg.png") }}') center / cover no-repeat;
            filter: grayscale(35%) brightness(0.45) saturate(0.85);
            transform: scale(1.05);
        }

        /* maroon → near-black scrim so the photo reads as dark texture */
        .login-bg-scrim {
            position: fixed;
            inset: 0;
            z-index: 1;
            background:
                radial-gradient(ellipse 70% 60% at 20% 15%, rgba(161,38,38,.38), transparent 62%),
                radial-gradient(ellipse 60% 55% at 85% 90%, rgba(123,29,29,.32), transparent 60%),
                linear-gradient(135deg, rgba(19,7,7,.90) 0%, rgba(58,16,16,.86) 45%, rgba(13,5,5,.94) 100%);
        }

        /* subtle dot texture, same language as the landing hero */
        .login-bg-dots {
            position: fixed;
            inset: 0;
            z-index: 2;
            background-image: radial-gradient(rgba(255,255,255,.075) 1.5px, transparent 1.6px);
            background-size: 20px 20px;
            -webkit-mask-image: radial-gradient(ellipse at top right, #000 20%, transparent 75%);
            mask-image: radial-gradient(ellipse at top right, #000 20%, transparent 75%);
            pointer-events: none;
        }

        .container {
            display: grid;
            grid-template-columns: 1.05fr 1fr;
            width: 100%;
            max-width: 1080px;
            min-height: 620px;
            background: #fff;
            border-radius: 24px;
            box-shadow:
                0 40px 100px -25px rgba(0, 0, 0, .75),
                0 0 0 1px rgba(255, 255, 255, .08);
            overflow: hidden;
            position: relative;
            z-index: 3;
        }

        /* ── Left / brand panel (dark) ── */
        .left-section {
            position: relative;
            color: #fff;
            padding: 52px 44px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: linear-gradient(150deg, #7B1D1D 0%, #3A1010 48%, #130707 100%);
        }

        .left-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 50% at 15% 85%, rgba(161,38,38,.35), transparent 62%),
                radial-gradient(ellipse 55% 45% at 90% 10%, rgba(192,57,43,.28), transparent 58%);
            pointer-events: none;
        }

        .left-section::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255,255,255,.10) 1.5px, transparent 1.6px);
            background-size: 19px 19px;
            -webkit-mask-image: radial-gradient(ellipse at bottom left, #000 15%, transparent 72%);
                    mask-image: radial-gradient(ellipse at bottom left, #000 15%, transparent 72%);
            pointer-events: none;
        }

        .left-section > * {
            position: relative;
            z-index: 2;
        }

        /* ── Card ornamentation ───────────────────────────────── */
        /* Campus cut-out, lifted and dropped to a whisper so it reads as
           texture rather than a photo. */
        .panel-photo {
            position: absolute; right: -20%; bottom: -12%; width: 148%;
            aspect-ratio: 1920 / 1080; opacity: .1; z-index: 0;
            background: url("{{ asset('images/overaly.png') }}") no-repeat;
            background-size: 100% 100%;
            filter: grayscale(1) brightness(1.7) contrast(.9);
            -webkit-mask-image: linear-gradient(to bottom, transparent 22%, #000 58%);
                    mask-image: linear-gradient(to bottom, transparent 22%, #000 58%);
        }

        /* The skewed bars that run down the landing hero, scaled to the panel. */
        .panel-slash { position: absolute; z-index: 1; transform: skewX(-19deg); border-radius: 6px; }
        .ps-1 { top: -14%; right: 5%;   width: 58px; height: 58%; background: linear-gradient(180deg, rgba(161,38,38,.6), rgba(161,38,38,0)); }
        .ps-2 { top: -14%; right: 17%;  width: 15px; height: 44%; background: linear-gradient(180deg, rgba(255,255,255,.11), rgba(255,255,255,0)); }
        .ps-3 { bottom: -16%; left: -7%; width: 44px; height: 36%; background: linear-gradient(0deg, rgba(161,38,38,.5), rgba(161,38,38,0)); }

        /* Hairline down the seam between the two halves. */
        .card-seam {
            position: absolute; top: 0; bottom: 0; left: 51.2%;
            width: 3px; z-index: 4; pointer-events: none;
            background: linear-gradient(180deg,
                rgba(161,38,38,0) 0%, var(--maroon-bright) 26%,
                var(--maroon-bright) 74%, rgba(161,38,38,0) 100%);
        }

        /* Quiet ornaments on the form side, same set the landing page uses. */
        .rdeco { position: absolute; z-index: 0; pointer-events: none; }
        .rdeco-dots {
            color: rgba(123,29,29,.2);
            background-image: radial-gradient(currentColor 1.3px, transparent 1.4px);
            background-size: 17px 17px;
            -webkit-mask-image: radial-gradient(ellipse at center, #000 26%, transparent 72%);
                    mask-image: radial-gradient(ellipse at center, #000 26%, transparent 72%);
        }
        .rdeco-ring { border: 1.5px dashed rgba(123,29,29,.18); border-radius: 50%; animation: cardSpin 46s linear infinite; }
        .rdeco-tile { border: 1.5px solid rgba(123,29,29,.14); border-radius: 14px; }
        @keyframes cardSpin { to { transform: rotate(360deg); } }

        .rd-1 { right: 10px; bottom: 14px; width: 170px; height: 100px; }
        .rd-2 { right: 26px; top: 26px;    width: 84px;  height: 84px; }
        .panel-tile {
            position: absolute; z-index: 1; left: 44px; top: 33%;
            width: 66px; height: 66px; transform: rotate(20deg);
            border: 1.5px solid rgba(255,255,255,.14); border-radius: 15px;
        }
        .panel-ring {
            position: absolute; z-index: 1; right: 46px; top: 25%;
            width: 78px; height: 78px; border-radius: 50%;
            border: 1.5px dashed rgba(255,255,255,.13);
            animation: cardSpin 46s linear infinite;
        }

        @media (prefers-reduced-motion: reduce) { .rdeco-ring { animation: none; } }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: auto;
        }

        .logo-circle {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .logo-circle img {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        .wordmark { font-weight: 800; letter-spacing: -.035em; line-height: 1; }
        .wordmark .w-a { color: var(--maroon); }
        .wordmark .w-b { color: var(--ink); }
        .wordmark.on-dark .w-a, .wordmark.on-dark .w-b { color: #fff; }

        .logo-text h1 { font-size: 22px; }

        .logo-text p {
            font-size: 11.5px;
            font-weight: 300;
            font-style: italic;
            opacity: .8;
        }

        .hero-content {
            margin: 40px 0 32px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255, 255, 255, .16);
            font-size: 11.5px;
            font-weight: 500;
            letter-spacing: .3px;
            margin-bottom: 20px;
        }

        .hero-badge .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--rose);
            box-shadow: 0 0 0 4px rgba(239, 198, 198, .2);
        }

        .hero-content h2 {
            font-size: 31px;
            font-weight: 700;
            line-height: 1.28;
            letter-spacing: -.022em;
            margin-bottom: 14px;
        }

        .hero-content h2 span { color: var(--rose); }

        .hero-content p {
            font-size: 13.5px;
            line-height: 1.7;
            opacity: .85;
            max-width: 340px;
        }

        .hero-subjects {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .hero-subjects span {
            padding: 6px 12px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .12);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .4px;
            transition: background .18s, border-color .18s, transform .18s;
        }
        .hero-subjects span:hover {
            background: rgba(255, 255, 255, .14);
            border-color: rgba(255, 255, 255, .24);
            transform: translateY(-2px);
        }

        /* ── Right / form panel ── */
        .right-section-wrap {
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
            background: #fff;
        }

        .right-section {
            padding: 48px 46px;
            position: relative;
        }

        .right-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(123, 29, 29, .05) 0%, transparent 70%);
            pointer-events: none;
        }

        .mobile-brand {
            display: none;
            align-items: center;
            gap: 10px;
            margin-bottom: 26px;
        }

        .mobile-brand img {
            height: 34px;
            object-fit: contain;
        }

        .form-container {
            position: relative;
            z-index: 1;
        }

        .form-container h3 {
            font-size: 27px;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -.022em;
            margin-bottom: 8px;
        }

        .form-container > p {
            color: var(--gray);
            margin-bottom: 30px;
            font-size: 13.5px;
        }

        .form-container > p strong {
            color: var(--maroon);
            font-weight: 600;
        }

        .alert-error {
            display: flex;
            gap: 10px;
            background: #fef2f2;
            border: 1px solid #fca5a5;
            border-left: 3px solid #dc2626;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 22px;
        }

        .alert-error i {
            color: #dc2626;
            font-size: 14px;
            margin-top: 2px;
        }

        .alert-error div {
            color: #b91c1c;
            font-size: 12.5px;
            font-weight: 500;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            color: var(--ink);
            font-weight: 600;
            font-size: 12.5px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap > i.field-ico {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 14px;
            pointer-events: none;
            transition: color .25s;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 13px 15px 13px 42px;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            font-size: 14px;
            color: var(--ink);
            font-family: 'Montserrat', ui-sans-serif, system-ui, sans-serif;
            background: #fff;
            transition: border-color .25s, box-shadow .25s, background .25s;
        }

        input::placeholder { color: #AFB9C4; }

        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="text"]:focus {
            outline: none;
            border-color: var(--maroon);
            background: #fff;
            box-shadow: 0 0 0 3.5px rgba(123, 29, 29, .1);
        }

        .input-wrap:focus-within > i.field-ico { color: var(--maroon); }

        .input-wrap input[type="password"],
        .input-wrap input.pw { padding-right: 44px; }

        .toggle-eye {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            font-size: 15px;
            padding: 4px;
            display: flex;
            align-items: center;
            transition: color .2s;
        }

        .toggle-eye:hover { color: var(--maroon); }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            font-size: 12.5px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 7px;
            color: var(--navy-soft);
        }

        .checkbox-wrapper label {
            margin: 0;
            font-weight: 500;
            font-size: 12.5px;
            cursor: pointer;
        }

        input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: var(--maroon);
        }

        .forgot-password {
            color: var(--maroon);
            text-decoration: none;
            font-weight: 600;
            transition: color .2s;
        }

        .forgot-password:hover {
            text-decoration: underline;
            color: var(--maroon-dark);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--maroon);
            color: #fff;
            border: none;
            border-radius: 9px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Montserrat', ui-sans-serif, system-ui, sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            letter-spacing: .3px;
            box-shadow: 0 8px 20px rgba(123, 29, 29, .26);
            transition: background .18s, transform .18s, box-shadow .18s;
        }

        .btn-login i { font-size: 12px; transition: transform .2s; }
        .btn-login:hover {
            background: var(--maroon-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(123, 29, 29, .34);
        }
        .btn-login:hover i { transform: translateX(3px); }
        .btn-login:active { transform: translateY(0); }

        .divider {
            text-align: center;
            margin: 24px 0;
            position: relative;
            color: var(--muted);
            font-size: 12px;
        }

        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 32%;
            height: 1px;
            background: var(--border);
        }

        .divider::before { left: 0; }
        .divider::after  { right: 0; }

        .social-btn {
            padding: 12px;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            background: #fff;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Montserrat', ui-sans-serif, system-ui, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            text-decoration: none;
            color: var(--ink);
            transition: border-color .18s, background .18s, transform .18s, box-shadow .18s;
        }

        .social-btn:hover {
            border-color: #CFD6DE;
            background: var(--soft);
            transform: translateY(-2px);
        }

        .signup-link {
            text-align: center;
            color: var(--gray);
            font-size: 12.5px;
            margin-top: 22px;
        }

        .signup-link a {
            color: var(--maroon);
            text-decoration: none;
            font-weight: 700;
        }

        .signup-link a:hover { text-decoration: underline; }

        .back-home {
            position: fixed;
            top: 22px;
            left: 24px;
            z-index: 4;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255, 255, 255, .18);
            backdrop-filter: blur(10px);
            color: rgba(255, 255, 255, .9);
            font-size: 12.5px;
            font-weight: 500;
            text-decoration: none;
            transition: background .25s, border-color .25s;
        }

        .back-home:hover {
            background: rgba(255, 255, 255, .18);
            border-color: rgba(255, 255, 255, .3);
        }

        .error-message {
            color: #C0392B;
            font-size: 12px;
            margin-top: 6px;
        }

        .form-group.error input { border-color: #C0392B; }
        .form-group.error input:focus { box-shadow: 0 0 0 3.5px rgba(192, 57, 43, .13); }

        /* ── Tablet ── */
        @media (max-width: 1024px) {
            .container { max-width: 100%; }
            .left-section { padding: 40px 32px; }
            .right-section { padding: 40px 34px; }
            .hero-content h2 { font-size: 27px; }
        }

        /* ── Stack: hide brand panel ── */
        @media (max-width: 820px) {
            body { padding: 24px 16px; }
            .container {
                grid-template-columns: 1fr;
                min-height: 0;
                max-width: 460px;
            }
            .left-section { display: none; }
            .card-seam { display: none; }
            .rd-2 { right: 14px; top: 14px; width: 66px; height: 66px; }
            .mobile-brand { display: flex; }
            .right-section { padding: 36px 30px; }
            .back-home { position: static; margin-bottom: 0; display: none; }
        }

        /* ── Mobile ── */
        @media (max-width: 480px) {
            body { padding: 16px; align-items: flex-start; }
            .container { border-radius: 20px; }
            .right-section { padding: 28px 20px; }
            .form-container h3 { font-size: 23px; }
            .form-container > p { font-size: 12.5px; margin-bottom: 22px; }
            .form-group { margin-bottom: 15px; }
            input[type="email"],
            input[type="password"],
            input[type="text"] {
                font-size: 16px; /* prevents iOS zoom */
                padding: 12px 14px 12px 40px;
            }
            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .divider { margin: 20px 0; }
            .divider::before, .divider::after { width: 26%; }
        }
    </style>
</head>
<body>
    <div class="login-bg"></div>
    <div class="login-bg-scrim"></div>
    <div class="login-bg-dots"></div>

    <a href="{{ url('/') }}" class="back-home"><i class="fas fa-arrow-left"></i> Back to home</a>

    <div class="container">
        <span class="card-seam" aria-hidden="true"></span>

        <!-- Left / Brand Section -->
        <div class="left-section">
            <span class="panel-photo" aria-hidden="true"></span>
            <span class="panel-slash ps-1" aria-hidden="true"></span>
            <span class="panel-slash ps-2" aria-hidden="true"></span>
            <span class="panel-slash ps-3" aria-hidden="true"></span>
            <span class="panel-tile" aria-hidden="true"></span>
            <span class="panel-ring" aria-hidden="true"></span>

            <div class="logo">
                <div class="logo-circle">
                    <img src="{{ asset('images/logo-icon.png') }}" alt="CPAce">
                </div>
                <div class="logo-text">
                    <h1 class="wordmark on-dark"><span class="w-a">CPA</span><span class="w-b">ce</span></h1>
                    <p>Your Edge to Ace CPALE</p>
                </div>
            </div>

            <div class="hero-content">
                <div class="hero-badge"><span class="dot"></span> CPALE Review Platform</div>
                <h2>Master your CPA journey with <span>smart practice</span></h2>
                <p>Structured syllabus coverage, adaptive quizzes, and progress tracking across all six CPALE subjects — all in one place.</p>
            </div>

            <div class="hero-subjects">
                <span>FAR</span>
                <span>AFAR</span>
                <span>MS</span>
                <span>AUD</span>
                <span>TAX</span>
                <span>RFBT</span>
            </div>
        </div>

        <!-- Right / Form Section -->
        <div class="right-section-wrap">
            <div class="right-section">
                <span class="rdeco rdeco-ring rd-2" aria-hidden="true"></span>
                <span class="rdeco rdeco-dots rd-1" aria-hidden="true"></span>

                <div class="mobile-brand">
                    <img src="{{ asset('images/logo-icon.png') }}" alt="">
                    <span class="wordmark" style="font-size:26px"><span class="w-a">CPA</span><span class="w-b">ce</span></span>
                </div>

                <div class="form-container">
                    <h3>Welcome back!</h3>
                    <p>Log in to continue your <strong>CPA review</strong> journey.</p>

                    {{-- Validation errors surface as a SweetAlert popup via partials.alerts --}}

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group @error('email') error @enderror">
                            <label for="email">Email address</label>
                            <div class="input-wrap">
                                <i class="fas fa-envelope field-ico"></i>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                       placeholder="you@example.com" autocomplete="email" required>
                            </div>
                            @error('email')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group @error('password') error @enderror">
                            <label for="password">Password</label>
                            <div class="input-wrap">
                                <i class="fas fa-lock field-ico"></i>
                                <input type="password" id="password" name="password" class="pw"
                                       placeholder="Enter your password" autocomplete="current-password" required>
                                <button type="button" class="toggle-eye" aria-label="Show password"
                                        onclick="togglePassword('password', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="remember-forgot">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label for="remember">Remember me</label>
                            </div>
                            <a href="{{ route('forgot-password') }}" class="forgot-password">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn-login">Log In <i class="fas fa-arrow-right"></i></button>
                    </form>

                    <div class="divider">or continue with</div>

                    <a href="{{ route('auth.google') }}" class="social-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="18" height="18">
                            <path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9 3.2l6.7-6.7C35.7 2.3 30.2 0 24 0 14.6 0 6.6 5.4 2.6 13.3l7.8 6.1C12.4 13.2 17.7 9.5 24 9.5z"/>
                            <path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v8.5h12.7c-.6 3-2.4 5.5-4.9 7.2l7.7 6c4.5-4.2 7-10.4 7-17.2z"/>
                            <path fill="#FBBC05" d="M10.4 28.6c-.5-1.4-.8-2.9-.8-4.6s.3-3.2.8-4.6l-7.8-6.1C1 16.6 0 20.2 0 24s1 7.4 2.6 10.7l7.8-6.1z"/>
                            <path fill="#34A853" d="M24 48c6.2 0 11.4-2 15.2-5.5l-7.7-6c-2 1.4-4.6 2.2-7.5 2.2-6.3 0-11.6-3.7-13.6-9l-7.8 6.1C6.6 42.6 14.6 48 24 48z"/>
                        </svg>
                        Google
                    </a>
                </div>
            </div>
        </div>
    </div>

<script>
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
        btn.setAttribute('aria-label', 'Hide password');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
        btn.setAttribute('aria-label', 'Show password');
    }
}
</script>

    @include('partials.alerts')
</body>
</html>
