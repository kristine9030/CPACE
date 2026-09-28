<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --maroon: #7B1D1D;
            --maroon-dark: #5A1414;
            --maroon-bright: #A12626;
            --ink: #14283E;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: radial-gradient(circle at 20% 20%, var(--maroon-bright) 0%, var(--maroon-dark) 55%, #2b0b0b 100%);
        }
        .card {
            background: #fff;
            border-radius: 20px;
            padding: 44px 38px 38px;
            max-width: 440px;
            width: 100%;
            text-align: center;
            box-shadow: 0 30px 70px rgba(0, 0, 0, .35);
        }
        .logo {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(145deg, var(--maroon-bright), var(--maroon-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 24px rgba(123, 29, 29, .28);
        }
        .logo img { width: 32px; height: 32px; object-fit: contain; }
        .icon-badge {
            width: 74px;
            height: 74px;
            border-radius: 50%;
            background: #fdf1f1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 22px;
        }
        .icon-badge i { font-size: 30px; color: var(--maroon); }
        .code {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--maroon);
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .heading {
            font-size: 21px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 10px;
            line-height: 1.35;
        }
        .message {
            font-size: 13.5px;
            color: #6b7280;
            line-height: 1.65;
            margin-bottom: 30px;
        }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all .2s;
        }
        .btn-primary { background: var(--maroon); color: #fff; }
        .btn-primary:hover { background: var(--maroon-dark); }
        .btn-ghost { background: #f4f5f7; color: #444; }
        .btn-ghost:hover { background: #ebebee; }
    </style>
</head>
@php
    // One place, not re-derived per error page: send a signed-in visitor to
    // their own dashboard (see User::homeRouteName — the same lookup
    // RedirectIfAuthenticated and AuthController::homeFor use), a guest to
    // login. A missing named route (edge case on a fresh install) falls back
    // to "/" instead of throwing from inside an error page.
    $homeRoute = auth()->check() ? auth()->user()->homeRouteName() : 'login';
    $errorHomeUrl = \Illuminate\Support\Facades\Route::has($homeRoute) ? route($homeRoute) : url('/');
    $errorHomeLabel = auth()->check() ? 'Go to my dashboard' : 'Go to login';
@endphp
<body>
    <div class="card">
        <div class="logo">
            <img src="{{ asset('images/logo-icon.png') }}" alt="CPACE">
        </div>
        <div class="icon-badge"><i class="fas @yield('icon', 'fa-triangle-exclamation')"></i></div>
        <div class="code">Error @yield('code')</div>
        <div class="heading">@yield('heading')</div>
        <div class="message">@yield('message')</div>
        <div class="actions">
            <a href="#" onclick="history.length > 1 ? history.back() : (window.location = '{{ $errorHomeUrl }}'); return false;" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Go back
            </a>
            <a href="{{ $errorHomeUrl }}" class="btn btn-primary">
                <i class="fas fa-house"></i> {{ $errorHomeLabel }}
            </a>
        </div>
    </div>
</body>
</html>
