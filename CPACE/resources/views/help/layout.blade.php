{{--
    Page shell for Help & Support (all roles) and the Chair's Support Inbox.
    Picks the sidebar for the signed-in role, like notifications/index does.

    Sections: title, heading, subheading, actions (optional), content
    Pass $active to highlight the sidebar item (default "help").
--}}
@php $active = $active ?? 'help'; $me = Auth::user(); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - CPAce</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{
            --primary:#7B1D1D; --primary-hover:#641717; --primary-light:#f5e8e8; --accent:#c0392b;
            --ink:#1f1414; --text:#4a3f3f; --muted:#8a7c7b; --line:#eee4e3; --surface:#fff; --canvas:#f4f5f7;
            --ok:#047857; --ok-bg:#d1fae5; --warn:#92400e; --warn-bg:#fef3c7; --info:#1d4ed8; --info-bg:#dbeafe;
        }
        *{box-sizing:border-box}
        body{margin:0;font-family:'Poppins',sans-serif;background:var(--canvas);color:var(--text)}
        a{color:inherit}
        .help-main{margin-left:230px;padding:24px 30px 48px;transition:margin-left .28s}
        .sidebar.collapsed~.help-main{margin-left:68px}
        .help-wrap{max-width:1180px;margin:0 auto}

        .top-bar{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;margin-bottom:22px;flex-wrap:wrap}
        .page-title{font-family:'Montserrat',sans-serif;font-size:28px;font-weight:700;color:#14283E;margin:0 0 6px;padding-bottom:8px;position:relative}
        .page-title::after{content:'';position:absolute;left:0;bottom:0;width:40px;height:4px;border-radius:2px;background:linear-gradient(90deg,#c0392b,#7B1D1D)}
        .page-subtitle{font-size:14px;color:#999;margin:0}
        .top-actions{display:flex;gap:10px;flex-wrap:wrap}

        .card{background:var(--surface);border:1px solid var(--line);border-radius:16px;box-shadow:0 1px 3px rgba(20,10,10,.04)}
        .card-pad{padding:22px 24px}
        .card-title{display:flex;align-items:center;gap:10px;font-family:'Montserrat',sans-serif;font-size:16px;font-weight:700;color:var(--ink);margin:0 0 4px}
        .card-title i{color:var(--primary)}
        .card-sub{font-size:13px;color:var(--muted);margin:0 0 16px}

        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:none;border-radius:10px;padding:11px 18px;font:600 13.5px 'Poppins',sans-serif;cursor:pointer;text-decoration:none;transition:background .15s,transform .05s,box-shadow .15s}
        .btn:active{transform:translateY(1px)}
        .btn-primary{background:linear-gradient(135deg,#5c1515,#8B2525);color:#fff;box-shadow:0 4px 12px rgba(123,29,29,.22)}
        .btn-primary:hover{box-shadow:0 6px 16px rgba(123,29,29,.3)}
        .btn-ghost{background:#fff;color:var(--primary);border:1px solid var(--line)}
        .btn-ghost:hover{background:var(--primary-light)}
        .btn:focus-visible,.field:focus-visible{outline:3px solid rgba(123,29,29,.25);outline-offset:2px}

        .label{display:block;font-size:12px;font-weight:600;color:var(--text);margin:0 0 6px}
        .field{width:100%;padding:11px 14px;border:1px solid #e0d6d5;border-radius:10px;font:14px 'Poppins',sans-serif;color:var(--ink);background:#fff;outline:none;transition:border-color .15s}
        .field:focus{border-color:var(--primary)}
        textarea.field{resize:vertical;min-height:130px;line-height:1.55}
        .form-row{margin-bottom:14px}
        .hint{font-size:11.5px;color:var(--muted);margin-top:5px}
        .error-text{font-size:12px;color:#b91c1c;margin-top:5px}

        .pill{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;padding:4px 10px;border-radius:999px;white-space:nowrap}
        .pill::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor}
        .pill-new{background:var(--info-bg);color:var(--info)}
        .pill-in_review{background:var(--warn-bg);color:var(--warn)}
        .pill-resolved{background:var(--ok-bg);color:var(--ok)}
        .tag{display:inline-block;font-size:11.5px;color:var(--muted);background:#f6f1f0;padding:3px 9px;border-radius:6px}

        .empty{padding:36px 16px;text-align:center;color:var(--muted);font-size:13px}
        .empty i{display:block;font-size:30px;color:#dccfce;margin-bottom:10px}

        @media(max-width:900px){.help-main{margin-left:68px}}
        @media(max-width:768px){.help-main{margin-left:0!important;padding:80px 16px 96px}.page-title{font-size:22px}.card-pad{padding:18px}}
        @media(prefers-reduced-motion:reduce){*{transition:none!important;scroll-behavior:auto!important}}
    </style>
    @stack('styles')
</head>
<body>
@if($me->isChair())
    @include('partials.chair-sidebar', ['active' => $active])
@elseif($me->isFaculty())
    @include('partials.faculty-sidebar', ['active' => $active])
@elseif($me->isAlumni())
    @include('partials.alumni-sidebar', ['active' => $active])
@else
    @include('partials.sidebar', ['active' => $active])
    @include('partials.student-bottom-nav', ['active' => 'more'])
    @include('partials.student-mobile-header')
@endif

<main class="help-main">
    <div class="help-wrap">
        <div class="top-bar">
            <div>
                <h1 class="page-title">@yield('heading')</h1>
                <p class="page-subtitle">@yield('subheading')</p>
            </div>
            <div class="top-actions">@yield('actions')</div>
        </div>
        @yield('content')
    </div>
</main>

@include('partials.alerts')
@stack('scripts')
</body>
</html>
