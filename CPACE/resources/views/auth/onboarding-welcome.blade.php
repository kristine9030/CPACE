<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to CPACE!</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --accent:#c0392b; --gold:#ffd76a; --gold-2:#f5a623; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Poppins',sans-serif;
            background: linear-gradient(150deg, #1a0a0a 0%, #3d0c0c 32%, #7B1D1D 70%, #a12626 100%);
            min-height:100vh; display:flex; align-items:center; justify-content:center;
            padding:24px; overflow:hidden; position:relative;
        }
        /* abstract background shapes */
        .bg-shape { position:absolute; pointer-events:none; z-index:0; }
        .bg-shape.a { top:8%; left:10%; width:120px; height:120px; border:2px solid rgba(255,215,106,.1); border-radius:32px; transform:rotate(24deg); }
        .bg-shape.b { bottom:12%; right:12%; width:150px; height:150px; border:2px solid rgba(255,255,255,.06); border-radius:50%; }
        .bg-shape.c { top:55%; left:6%; width:70px; height:70px; background:rgba(192,57,43,.25); border-radius:50%; }
        .bg-shape.d { top:14%; right:18%; width:0; height:0; border-left:26px solid transparent; border-right:26px solid transparent; border-bottom:46px solid rgba(255,215,106,.08); transform:rotate(18deg); }

        .welcome-card {
            position:relative; z-index:2;
            width:100%; max-width:560px;
            background:#fff; border-radius:26px;
            padding:46px 44px 34px; text-align:center;
            box-shadow:0 30px 80px rgba(10,3,3,.5);
            animation:pop .5s cubic-bezier(.2,.9,.3,1.3);
        }
        @keyframes pop { 0%{opacity:0; transform:translateY(24px) scale(.94);} 100%{opacity:1; transform:translateY(0) scale(1);} }

        /* medallion */
        .medal-wrap { position:relative; width:130px; height:130px; margin:0 auto 22px; }
        .medal-rays {
            position:absolute; inset:-10px; border-radius:50%;
            background:repeating-conic-gradient(var(--gold) 0deg 11deg, transparent 11deg 22deg);
            opacity:.32; animation:spin 16s linear infinite;
        }
        @keyframes spin { to { transform:rotate(360deg);} }
        .medal {
            position:absolute; inset:14px; border-radius:50%;
            background:radial-gradient(circle at 34% 26%, #ffe89a, var(--gold-2) 82%);
            display:flex; align-items:center; justify-content:center;
            color:#fff; font-size:48px;
            border:4px solid rgba(255,255,255,.9);
            box-shadow:0 6px 18px rgba(245,166,35,.5), inset 0 3px 8px rgba(255,255,255,.6), inset 0 -8px 14px rgba(0,0,0,.2);
            animation:bob 3.2s ease-in-out infinite;
        }
        @keyframes bob { 0%,100%{transform:translateY(0);} 50%{transform:translateY(-7px);} }
        .medal i { filter:drop-shadow(0 2px 3px rgba(0,0,0,.25)); }

        .welcome-tag { font-size:11px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:var(--accent); margin-bottom:10px; }
        .welcome-card h1 { font-size:29px; font-weight:800; color:#1a1a1a; line-height:1.2; margin-bottom:10px; }
        .welcome-card h1 span { color:var(--primary); }
        .welcome-card .lead { font-size:13.5px; color:#888; line-height:1.6; max-width:400px; margin:0 auto 26px; }

        /* plan summary chips */
        .plan-summary {
            background:#faf5f5; border:1px solid #f1e6e6; border-radius:16px;
            padding:18px; margin-bottom:24px; text-align:left;
        }
        .plan-summary h4 { font-size:11px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; color:#bbb; margin-bottom:13px; text-align:center; }
        .plan-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .plan-item { display:flex; align-items:center; gap:11px; }
        .plan-item .pi-ic { width:38px; height:38px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:15px; color:#fff; flex-shrink:0; }
        .pi-ic.g { background:radial-gradient(circle at 35% 28%,#ffe24a,#ffab00 82%); }
        .pi-ic.r { background:radial-gradient(circle at 35% 28%,#ff8a6b,#ff2740 82%); }
        .pi-ic.b { background:radial-gradient(circle at 35% 28%,#63c0ff,#1f7bff 82%); }
        .pi-ic.p { background:radial-gradient(circle at 35% 28%,#d488ff,#9412f5 82%); }
        .plan-item .pi-k { font-size:10.5px; color:#aaa; }
        .plan-item .pi-v { font-size:13px; font-weight:600; color:#1a1a1a; }

        .btn-enter {
            display:inline-flex; align-items:center; justify-content:center; gap:10px;
            width:100%; padding:15px; border:none; border-radius:14px;
            background:linear-gradient(135deg, var(--accent), var(--primary)); color:#fff;
            font-size:15px; font-weight:700; font-family:'Poppins',sans-serif; cursor:pointer;
            text-decoration:none; box-shadow:0 8px 22px rgba(123,29,29,.4); transition:all .2s;
        }
        .btn-enter:hover { transform:translateY(-2px); box-shadow:0 12px 28px rgba(123,29,29,.5); }
        .welcome-foot { margin-top:16px; font-size:11.5px; color:#bbb; }
        .welcome-foot i { color:var(--gold-2); }

        /* confetti */
        .confetti { position:absolute; top:-12px; width:9px; height:14px; opacity:.9; z-index:1; animation:fall linear forwards; }
        @keyframes fall { to { transform:translateY(102vh) rotate(640deg); opacity:.4; } }

        @media (max-width:520px) {
            .welcome-card { padding:34px 22px 26px; }
            .welcome-card h1 { font-size:23px; }
            .plan-grid { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>
@php
    $u = auth()->user();
    $first = trim(explode(' ', $u?->name ?? 'Juan')[0] ?? 'there');
@endphp

<span class="bg-shape a"></span><span class="bg-shape b"></span><span class="bg-shape c"></span><span class="bg-shape d"></span>

<div class="welcome-card">
    <div class="medal-wrap">
        <div class="medal-rays"></div>
        <div class="medal"><i class="fas fa-trophy"></i></div>
    </div>

    <div class="welcome-tag">Your review space is ready</div>
    <h1>Welcome to CPACE,<br><span>{{ $first }}!</span></h1>
    <p class="lead">Everything's set up around your goal. Your dashboard, quizzes, and study plan are now personalized just for you. Let's pass those boards! 🚀</p>

    <div class="plan-summary">
        <h4>Your Review Plan</h4>
        <div class="plan-grid">
            <div class="plan-item"><div class="pi-ic g"><i class="fas fa-bullseye"></i></div><div><div class="pi-k">Target Exam</div><div class="pi-v">{{ $examTarget ?? 'May 2027' }}</div></div></div>
            <div class="plan-item"><div class="pi-ic r"><i class="fas fa-fire"></i></div><div><div class="pi-k">Intensity</div><div class="pi-v">{{ $intensity ?? 'Steady' }}</div></div></div>
            <div class="plan-item"><div class="pi-ic b"><i class="fas fa-calendar-check"></i></div><div><div class="pi-k">Study Days</div><div class="pi-v">{{ $studyDays ?? 'Mon–Fri' }}</div></div></div>
            <div class="plan-item"><div class="pi-ic p"><i class="fas fa-layer-group"></i></div><div><div class="pi-k">Focus</div><div class="pi-v">{{ $focus ?? 'FAR, AUD, TAX' }}</div></div></div>
        </div>
    </div>

    <a href="{{ route('dashboard') ?? '#' }}" class="btn-enter">
        <i class="fas fa-rocket"></i> Enter my dashboard
    </a>
    <div class="welcome-foot"><i class="fas fa-lightbulb"></i> Tip: you can adjust your plan anytime from Settings.</div>
</div>

<script>
(function () {
    const colors = ['#ffd76a','#f5a623','#c0392b','#7B1D1D','#63c0ff','#08d15c','#d488ff'];
    for (let i = 0; i < 70; i++) {
        const c = document.createElement('span');
        c.className = 'confetti';
        c.style.left = Math.random()*100 + 'vw';
        c.style.background = colors[Math.floor(Math.random()*colors.length)];
        c.style.animationDuration = (2.5 + Math.random()*2.5) + 's';
        c.style.animationDelay = (Math.random()*1.5) + 's';
        if (Math.random() > .5) c.style.borderRadius = '50%';
        document.body.appendChild(c);
        setTimeout(() => c.remove(), 6500);
    }
})();
</script>
</body>
</html>
