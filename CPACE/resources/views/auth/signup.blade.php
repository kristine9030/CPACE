<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - CPACE CPA Reviewer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
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
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-height: 90vh;
        }

        .left-section {
            background: linear-gradient(135deg, #8B3A3A 0%, #A84C4C 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
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
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 60px;
            line-height: 1.6;
        }

        .hero-image {
            text-align: center;
        }

        .hero-image svg {
            max-width: 250px;
            height: auto;
        }

        .right-section {
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            max-height: 90vh;
            overflow-y: auto;
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

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #8B3A3A;
            box-shadow: 0 0 0 3px rgba(139, 58, 58, 0.1);
        }

        .password-hint {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 25px;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #8B3A3A;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .checkbox-wrapper label {
            margin: 0;
            font-size: 13px;
            cursor: pointer;
            color: #666;
        }

        .btn-signup {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #8B3A3A 0%, #A84C4C 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-signup:hover {
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
            transition: border-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .social-btn:hover {
            border-color: #8B3A3A;
        }

        .login-link {
            text-align: center;
            color: #666;
            font-size: 13px;
        }

        .login-link a {
            color: #8B3A3A;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
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

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }

            .left-section {
                display: none;
            }

            .right-section {
                padding: 40px 20px;
            }

            .form-container h3 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Section -->
        <div class="left-section">
            <div>
                <div class="logo">
                    <div class="logo-circle">🎯</div>
                    <div class="logo-text">
                        <h1>CPACE</h1>
                        <p>CPA Reviewer</p>
                    </div>
                </div>

                <div class="hero-content">
                    <h2>Master your CPA journey with smart practice.</h2>
                    <p>Focused quizzes, detailed insights, and personalized learning to help you succeed.</p>
                </div>
            </div>

            <div class="hero-image">
                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                    <rect x="30" y="120" width="40" height="50" fill="#4CAF50" rx="4"/>
                    <rect x="35" y="115" width="30" height="8" fill="#2196F3"/>
                    <text x="50" y="135" font-size="12" fill="white" text-anchor="middle" font-weight="bold">FAR</text>
                    <circle cx="130" cy="100" r="40" fill="rgba(255,255,255,0.1)"/>
                    <circle cx="130" cy="100" r="35" fill="rgba(255,255,255,0.05)"/>
                </svg>
            </div>
        </div>

        <!-- Right Section -->
        <div class="right-section">
            <div class="form-container">
                <h3>Create your account</h3>
                <p>Join CPACE and start your CPA review journey today.</p>

                @if ($errors->any())
                    <div style="background: #fee; border: 1px solid #fcc; border-radius: 6px; padding: 12px; margin-bottom: 20px;">
                        @foreach ($errors->all() as $error)
                            <div style="color: #c33; font-size: 13px;">{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('signup') }}">
                    @csrf

                    <div class="form-group @error('name') error @enderror">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group @error('email') error @enderror">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group @error('password') error @enderror">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <div class="password-hint">At least 8 characters, with uppercase, lowercase, and numbers</div>
                        @error('password')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group @error('password_confirmation') error @enderror">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required>
                        @error('password_confirmation')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">I agree to the Terms of Service and Privacy Policy</label>
                    </div>

                    <button type="submit" class="btn-signup">Create Account</button>
                </form>

                <div class="divider">or continue with</div>

                <div class="social-login">
                    <button class="social-btn" onclick="alert('Google signup coming soon')">
                        <span>🔍</span> Google
                    </button>
                    <button class="social-btn" onclick="alert('Microsoft signup coming soon')">
                        <span>⊞</span> Microsoft
                    </button>
                </div>

                <div class="login-link">
                    Already have an account? <a href="{{ route('login') }}">Log in</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
