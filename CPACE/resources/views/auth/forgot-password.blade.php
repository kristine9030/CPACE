<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - CPACE CPA Reviewer</title>
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
            overflow: hidden;
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

        .right-section-wrap {
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
        }

        .right-section {
            padding: 60px 40px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #7B1D1D;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 30px;
        }

        .back-link:hover { text-decoration: underline; }

        .icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #f5e8e8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #7B1D1D;
            margin-bottom: 24px;
        }

        .form-container h3 {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .form-container .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
            line-height: 1.6;
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

        input[type="email"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus {
            outline: none;
            border-color: #7B1D1D;
            box-shadow: 0 0 0 3px rgba(123, 29, 29, 0.1);
        }

        .btn-submit {
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

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(139, 58, 58, 0.3);
        }

        .status-message {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 14px 16px;
            color: #166534;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .status-message i { margin-top: 2px; flex-shrink: 0; }

        .error-message {
            color: #ef4444;
            font-size: 13px;
            margin-top: 5px;
        }

        .form-group.error input {
            border-color: #ef4444;
        }

        .info-box {
            background: #fef9ec;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 14px 16px;
            color: #92400e;
            font-size: 12px;
            line-height: 1.6;
            margin-top: 20px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .info-box i { margin-top: 2px; flex-shrink: 0; color: #f59e0b; }

        /* ── Tablet: 640px – 1024px ── */
        @media (max-width: 1024px) {
            .container {
                max-width: 100%;
            }

            .right-section {
                padding: 48px 32px;
            }
        }

        /* ── Small tablet / large mobile: 641px – 768px ── */
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                min-height: auto;
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

            .form-container .subtitle {
                font-size: 13px;
                margin-bottom: 20px;
            }

            .icon-circle {
                width: 52px;
                height: 52px;
                font-size: 22px;
                margin-bottom: 18px;
            }

            .form-group {
                margin-bottom: 16px;
            }

            input[type="email"] {
                font-size: 16px; /* prevents iOS zoom */
                padding: 11px 13px;
            }

            .btn-submit {
                font-size: 15px;
                padding: 13px;
            }

            .info-box,
            .status-message {
                font-size: 12px;
            }

            .back-link {
                font-size: 13px;
                margin-bottom: 20px;
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
                            <i class="fas fa-check-circle"></i>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div style="background: #fee; border: 1px solid #fcc; border-radius: 6px; padding: 12px; margin-bottom: 20px;">
                            @foreach ($errors->all() as $error)
                                <div style="color: #c33; font-size: 13px;">{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="form-group @error('email') error @enderror">
                            <label for="email">Email address</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn-submit">Send Reset Request</button>
                    </form>

                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <span>This is a school-based system. If you cannot access your account, please contact your school administrator or faculty for assistance.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
