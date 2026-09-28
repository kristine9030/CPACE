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
        html, body { height: 100%; }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
            background: radial-gradient(circle at 18% 15%, var(--maroon-bright) 0%, var(--maroon-dark) 48%, #22090a 100%);
        }

        /* ── Decorative backdrop: soft rings + the status code ghosted huge,
             echoing the circle motif used on the dashboard's hero banner ── */
        .bg-ring {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, .09);
            pointer-events: none;
        }
        .bg-ring.r1 { width: 620px; height: 620px; top: -180px; right: -160px; }
        .bg-ring.r2 { width: 420px; height: 420px; bottom: -160px; left: -120px; }
        .bg-ring.r3 { width: 260px; height: 260px; bottom: 8%; right: 8%; border-color: rgba(255,255,255,.06); }
        .bg-code {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -52%);
            font-size: min(38vw, 460px);
            font-weight: 800;
            color: rgba(255, 255, 255, .05);
            letter-spacing: -6px;
            pointer-events: none;
            user-select: none;
            white-space: nowrap;
        }

        .card {
            position: relative;
            z-index: 1;
            background: #fff;
            border-radius: 24px;
            padding: 40px 38px 36px;
            max-width: 440px;
            width: 100%;
            text-align: center;
            box-shadow: 0 40px 90px rgba(0, 0, 0, .4), 0 1px 0 rgba(255,255,255,.06) inset;
            animation: rise .5s cubic-bezier(.22,.61,.36,1);
        }
        @keyframes rise {
            from { opacity: 0; transform: translateY(18px) scale(.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            margin-bottom: 26px;
        }
        .brand img { width: 26px; height: 26px; object-fit: contain; }
        .brand span {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: var(--ink);
            text-transform: uppercase;
        }

        .icon-badge {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 30%, #fff, #fdf1f1);
            border: 1px solid #fbe3e3;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            position: relative;
            box-shadow: 0 12px 28px rgba(123, 29, 29, .16);
        }
        .icon-badge::before {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: 1.5px dashed #f3c9c9;
            animation: spin 22s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .icon-badge i { font-size: 32px; color: var(--maroon); }

        .code {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--maroon);
            text-transform: uppercase;
            background: #fdf1f1;
            padding: 5px 14px;
            border-radius: 20px;
            margin-bottom: 14px;
        }
        .heading {
            font-size: 22px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 10px;
            line-height: 1.35;
        }
        .message {
            font-size: 13.5px;
            color: #6b7280;
            line-height: 1.7;
            margin-bottom: 32px;
        }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 11px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: transform .15s ease, filter .15s ease, background .15s ease;
        }
        .btn:active { transform: translateY(1px); }
        .btn-primary { background: linear-gradient(135deg, var(--maroon-bright), var(--maroon-dark)); color: #fff; box-shadow: 0 10px 22px rgba(123, 29, 29, .3); }
        .btn-primary:hover { filter: brightness(1.07); transform: translateY(-1px); }
        .btn-ghost { background: #f4f5f7; color: #444; }
        .btn-ghost:hover { background: #ebebee; transform: translateY(-1px); }

        @media (max-width: 420px) {
            .card { padding: 32px 24px 28px; border-radius: 20px; }
            .heading { font-size: 19px; }
        }
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
    <div class="bg-ring r1"></div>
    <div class="bg-ring r2"></div>
    <div class="bg-ring r3"></div>
    <div class="bg-code">@yield('code')</div>

    <div class="card">
        <div class="brand">
            <img src="{{ asset('images/logo-icon.png') }}" alt="CPACE">
            <span>CPACE</span>
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
