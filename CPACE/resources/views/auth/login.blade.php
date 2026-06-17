<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CPACE CPA Reviewer</title>
    <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ffffff 0%, #f5f0f0 50%, #e8d5d5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            width: 100%;
            max-width: 1200px;
            min-height: 680px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .left-section {
            position: relative;
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            overflow: hidden;
            min-height: 600px;
        }

        .left-section img.left-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center center;
            z-index: 0;
        }

        .left-section > *:not(img) {
            position: relative;
            z-index: 1;
        }

        .left-section .logo,
        .left-section .hero-content {
            text-shadow: 0 2px 8px rgba(0,0,0,0.7), 0 1px 3px rgba(0,0,0,0.9);
        }

        .left-section .logo-circle {
            background: rgba(0,0,0,0.3);
            border: 2px solid rgba(255,255,255,0.6);
        }

        .right-section-wrap {
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 40px;
        }

        .logo-circle {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .logo-text h1 {
            font-size: 24px;
            font-weight: 600;
        }

        .logo-text p {
            font-size: 12px;
            opacity: 0.9;
        }

        .hero-content h2 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .hero-content p {
            font-size: 13px;
            opacity: 0.95;
            line-height: 1.6;
        }

        .right-section {
            padding: 40px;
        }

        .form-container h3 {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .form-container p {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="text"]:focus {
            outline: none;
            border-color: #7B1D1D;
            box-shadow: 0 0 0 3px rgba(139, 58, 58, 0.1);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 44px;
        }

        .toggle-eye {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
            font-size: 16px;
            padding: 0;
            display: flex;
            align-items: center;
        }

        .toggle-eye:hover { color: #7B1D1D; }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 13px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #7B1D1D;
        }

        .forgot-password {
            color: #7B1D1D;
            text-decoration: none;
            font-weight: 600;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #7B1D1D 0%, #8B2525 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(139, 58, 58, 0.3);
        }

        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
            color: #999;
            font-size: 13px;
        }

        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #ddd;
        }

        .divider::before {
            left: 0;
        }

        .divider::after {
            right: 0;
        }

        .social-login {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        .social-btn {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            color: #333;
        }

        .social-btn:hover {
            border-color: #7B1D1D;
        }

        .signup-link {
            text-align: center;
            color: #666;
            font-size: 13px;
        }

        .signup-link a {
            color: #7B1D1D;
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: #ef4444;
            font-size: 13px;
            margin-top: 5px;
        }

        .form-group.error input {
            border-color: #ef4444;
        }

        /* ── Tablet: 640px – 1024px ── */
        @media (max-width: 1024px) {
            .container {
                max-width: 100%;
            }

            .left-section {
                padding: 40px 30px;
            }

            .right-section {
                padding: 32px 32px;
            }
        }

        /* ── Small tablet / large mobile: 641px – 768px ── */
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                max-height: none;
                height: auto;
            }

            .left-section {
                display: none;
            }

            .right-section-wrap {
                min-height: auto;
            }

            .right-section {
                padding: 32px 24px;
            }

            .form-container h3 {
                font-size: 24px;
            }

            .social-login {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }
        }

        /* ── Mobile: < 640px ── */
        @media (max-width: 639px) {
            body {
                padding: 16px;
                align-items: flex-start;
            }

            .container {
                grid-template-columns: 1fr;
                min-height: auto;
                border-radius: 8px;
            }

            .right-section {
                padding: 24px 16px;
            }

            .form-container h3 {
                font-size: 22px;
            }

            .form-container p {
                font-size: 13px;
                margin-bottom: 20px;
            }

            .form-group {
                margin-bottom: 16px;
            }

            input[type="email"],
            input[type="password"],
            input[type="text"] {
                font-size: 16px; /* prevents iOS zoom */
                padding: 11px 13px;
            }

            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                margin-bottom: 20px;
            }

            .btn-login {
                font-size: 15px;
                padding: 13px;
            }

            .divider {
                margin: 18px 0;
            }

            .social-login {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .social-btn {
                font-size: 14px;
                padding: 11px;
            }

            .signup-link {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Section -->
        <div class="left-section">
            <img src="{{ asset('images/login_bg.png') }}" class="left-bg" alt="">
        </div>

        <!-- Right Section -->
        <div class="right-section-wrap">
        <div class="right-section">
            <div class="form-container">
                <h3>Welcome back!</h3>
                <p>Log in to continue your CPA review journey.</p>

                @if ($errors->any())
                    <div style="background: #fee; border: 1px solid #fcc; border-radius: 6px; padding: 12px; margin-bottom: 20px;">
                        @foreach ($errors->all() as $error)
                            <div style="color: #c33; font-size: 13px;">{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group @error('email') error @enderror">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group @error('password') error @enderror">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" required>
                            <button type="button" class="toggle-eye" onclick="togglePassword('password', this)">
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
                            <label for="remember" style="margin: 0;">Remember me</label>
                        </div>
                        <a href="{{ route('forgot-password') }}" class="forgot-password">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-login">Log In</button>
                </form>

                <div class="divider">or continue with</div>

                <div class="social-login">
                    <a href="{{ route('auth.google') }}" class="social-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="18" height="18">
                            <path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9 3.2l6.7-6.7C35.7 2.3 30.2 0 24 0 14.6 0 6.6 5.4 2.6 13.3l7.8 6.1C12.4 13.2 17.7 9.5 24 9.5z"/>
                            <path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v8.5h12.7c-.6 3-2.4 5.5-4.9 7.2l7.7 6c4.5-4.2 7-10.4 7-17.2z"/>
                            <path fill="#FBBC05" d="M10.4 28.6c-.5-1.4-.8-2.9-.8-4.6s.3-3.2.8-4.6l-7.8-6.1C1 16.6 0 20.2 0 24s1 7.4 2.6 10.7l7.8-6.1z"/>
                            <path fill="#34A853" d="M24 48c6.2 0 11.4-2 15.2-5.5l-7.7-6c-2 1.4-4.6 2.2-7.5 2.2-6.3 0-11.6-3.7-13.6-9l-7.8 6.1C6.6 42.6 14.6 48 24 48z"/>
                        </svg>
                        Google
                    </a>
                    <a href="{{ route('auth.microsoft') }}" class="social-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 23 23" width="18" height="18">
                            <rect x="1" y="1" width="10" height="10" fill="#F25022"/>
                            <rect x="12" y="1" width="10" height="10" fill="#7FBA00"/>
                            <rect x="1" y="12" width="10" height="10" fill="#00A4EF"/>
                            <rect x="12" y="12" width="10" height="10" fill="#FFB900"/>
                        </svg>
                        Microsoft
                    </a>
                </div>

                <div class="signup-link">
                    Don't have an account? <a href="{{ route('signup') }}">Sign up</a>
                </div>

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
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
</body>
</html>


