<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - CPACE CPA Reviewer</title>
    <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --maroon:        #7B1D1D;
            --maroon-dark:   #5a1414;
            --maroon-bright: #a12626;
            --ink:           #1a1a1a;
            --gray:          #666666;
            --border:        #e8d5d5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
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

        .login-bg-scrim {
            position: fixed;
            inset: 0;
            z-index: 1;
            background:
                radial-gradient(ellipse 70% 60% at 20% 15%, rgba(161,38,38,.38), transparent 62%),
                radial-gradient(ellipse 60% 55% at 85% 90%, rgba(123,29,29,.32), transparent 60%),
                linear-gradient(135deg, rgba(19,7,7,.90) 0%, rgba(58,16,16,.86) 45%, rgba(13,5,5,.94) 100%);
        }

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
            background: linear-gradient(150deg, #6a1a1a 0%, #3a1010 45%, #130707 100%);
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
            z-index: 1;
        }

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
            background: rgba(255, 255, 255, .95);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .35);
        }

        .logo-circle img {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        .logo-text h1 {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -.3px;
        }

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

        .hero-badge i { font-size: 10px; color: #ffca6a; }

        .hero-content h2 {
            font-size: 32px;
            font-weight: 800;
            line-height: 1.22;
            letter-spacing: -.5px;
            margin-bottom: 14px;
        }

        .hero-content h2 span {
            background: linear-gradient(135deg, #ffca6a 0%, #e8a830 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-content p {
            font-size: 13.5px;
            line-height: 1.7;
            opacity: .85;
            max-width: 340px;
        }

        /* recovery steps */
        .steps {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .step {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .step-num {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11.5px;
            font-weight: 700;
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255, 255, 255, .16);
        }

        .step-body h4 {
            font-size: 12.5px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .step-body p {
            font-size: 11.5px;
            line-height: 1.5;
            opacity: .72;
            max-width: 300px;
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

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--maroon);
            text-decoration: none;
            font-size: 12.5px;
            font-weight: 600;
            margin-bottom: 26px;
            transition: gap .2s, color .2s;
        }

        .back-link:hover {
            gap: 11px;
            color: var(--maroon-dark);
        }

        .form-container {
            position: relative;
        }

        .icon-circle {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fff;
            margin-bottom: 22px;
            box-shadow: 0 10px 26px -8px rgba(123, 29, 29, .55);
        }

        .form-container h3 {
            font-size: 28px;
            font-weight: 800;
            color: var(--ink);
            letter-spacing: -.5px;
            margin-bottom: 8px;
        }

        .form-container .subtitle {
            color: var(--gray);
            margin-bottom: 28px;
            font-size: 13.5px;
            line-height: 1.65;
        }

        .status-message {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 3px solid #16a34a;
            border-radius: 10px;
            padding: 12px 14px;
            color: #166534;
            font-size: 12.5px;
            line-height: 1.55;
            margin-bottom: 22px;
        }

        .status-message i {
            margin-top: 2px;
            flex-shrink: 0;
            color: #16a34a;
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

        .alert-error > i {
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
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            color: #333;
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
            color: #b9a3a3;
            font-size: 14px;
            pointer-events: none;
            transition: color .25s;
        }

        input[type="email"] {
            width: 100%;
            padding: 13px 15px 13px 42px;
            border: 1.5px solid var(--border);
            border-radius: 11px;
            font-size: 14px;
            color: var(--ink);
            font-family: 'Poppins', sans-serif;
            background: #fbf9f9;
            transition: border-color .25s, box-shadow .25s, background .25s;
        }

        input::placeholder { color: #b0a0a0; }

        input[type="email"]:focus {
            outline: none;
            border-color: var(--maroon);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(123, 29, 29, .10);
        }

        .input-wrap:focus-within > i.field-ico { color: var(--maroon); }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
            color: #fff;
            border: none;
            border-radius: 11px;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            letter-spacing: .3px;
            box-shadow: 0 8px 24px -6px rgba(123, 29, 29, .55);
            transition: transform .2s, box-shadow .2s;
        }

        .btn-submit i { font-size: 12px; transition: transform .2s; }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 32px -8px rgba(123, 29, 29, .65);
        }
        .btn-submit:hover i { transform: translateX(3px); }
        .btn-submit:active { transform: translateY(0); }

        .info-box {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 12px 14px;
            color: #92400e;
            font-size: 11.5px;
            line-height: 1.6;
            margin-top: 22px;
        }

        .info-box i {
            margin-top: 2px;
            flex-shrink: 0;
            color: #f59e0b;
        }

        .error-message {
            color: #ef4444;
            font-size: 12px;
            margin-top: 6px;
        }

        .form-group.error input { border-color: #ef4444; }
        .form-group.error input:focus { box-shadow: 0 0 0 4px rgba(239, 68, 68, .12); }

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
            .mobile-brand { display: flex; }
            .right-section { padding: 36px 30px; }
        }

        /* ── Mobile ── */
        @media (max-width: 480px) {
            body { padding: 16px; align-items: flex-start; }
            .container { border-radius: 20px; }
            .right-section { padding: 28px 20px; }
            .back-link { margin-bottom: 20px; }
            .icon-circle {
                width: 48px;
                height: 48px;
                font-size: 19px;
                border-radius: 14px;
                margin-bottom: 18px;
            }
            .form-container h3 { font-size: 23px; }
            .form-container .subtitle { font-size: 12.5px; margin-bottom: 22px; }
            .form-group { margin-bottom: 16px; }
            input[type="email"] {
                font-size: 16px; /* prevents iOS zoom */
                padding: 12px 14px 12px 40px;
            }
        }
    </style>
</head>
<body>
    <div class="login-bg"></div>
    <div class="login-bg-scrim"></div>
    <div class="login-bg-dots"></div>

    <div class="container">
        <!-- Left / Brand Section -->
        <div class="left-section">
            <div class="logo">
                <div class="logo-circle">
                    <img src="{{ asset('images/logo-icon.png') }}" alt="CPAce">
                </div>
                <div class="logo-text">
                    <h1>CPAce</h1>
                    <p>Your Edge to Ace CPALE</p>
                </div>
            </div>

            <div class="hero-content">
                <div class="hero-badge"><i class="fas fa-shield-halved"></i> Account Recovery</div>
                <h2>Let's get you back to <span>your review</span></h2>
                <p>Your progress, quiz history, and study plan stay safe. Reset your password and pick up exactly where you left off.</p>
            </div>

            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <div class="step-body">
                        <h4>Enter your email</h4>
                        <p>Use the address registered to your CPAce account.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div class="step-body">
                        <h4>Check your inbox</h4>
                        <p>We'll send a secure, time-limited reset link.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <div class="step-body">
                        <h4>Set a new password</h4>
                        <p>Log back in and continue your CPALE prep.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right / Form Section -->
        <div class="right-section-wrap">
            <div class="right-section">
                <div class="mobile-brand">
                    <img src="{{ asset('images/logo-icon.png') }}" alt="CPAce">
                    <img src="{{ asset('images/wordmark-cropped.png') }}" alt="CPAce">
                </div>

                <a href="{{ route('login') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>

                <div class="form-container">
                    <div class="icon-circle">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3>Forgot your password?</h3>
                    <p class="subtitle">Enter your email address and we'll let you know the next steps to recover your account.</p>

                    @if (session('status'))
                        <div class="status-message">
                            <i class="fas fa-circle-check"></i>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert-error">
                            <i class="fas fa-circle-exclamation"></i>
                            <div>
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="form-group @error('email') error @enderror">
                            <label for="email">Email address</label>
                            <div class="input-wrap">
                                <i class="fas fa-envelope field-ico"></i>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                       placeholder="you@example.com" autocomplete="email" required autofocus>
                            </div>
                            @error('email')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn-submit">Send Reset Request <i class="fas fa-arrow-right"></i></button>
                    </form>

                    <div class="info-box">
                        <i class="fas fa-circle-info"></i>
                        <span>We'll email you a secure link to reset your password. If you don't see it within a few minutes, check your spam folder or contact your school administrator.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
