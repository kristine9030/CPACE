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
            width: 100%; max-width: 480px;
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 24px 60px rgba(50,10,10,.22);
            overflow: hidden;
            padding: 38px 40px;
        }
        .brand-logo { display:flex; align-items:center; gap:11px; margin-bottom: 22px; }
        .brand-logo .bl-mark {
            width: 46px; height:46px; border-radius:12px; background:var(--primary-light);
            display:flex; align-items:center; justify-content:center; font-size:22px; color:var(--primary);
        }
        .brand-logo strong { font-size:20px; font-weight:800; letter-spacing:.5px; display:block; line-height:1; color:#1a1a1a; }
        .brand-logo small { font-size:10px; color:#999; font-style:italic; }

        .step-emoji {
            width:64px; height:64px; border-radius:18px; margin-bottom:16px;
            display:flex; align-items:center; justify-content:center; font-size:28px;
            background:radial-gradient(circle at 35% 28%, #63c0ff, #1f7bff 82%);
            color:#fff; box-shadow:0 8px 20px rgba(31,123,255,.32);
        }
        .step-h { font-size:22px; font-weight:700; color:#1a1a1a; margin-bottom:6px; }
        .step-sub { font-size:13px; color:#888; line-height:1.6; margin-bottom:26px; }

        label { display:block; font-size:12.5px; font-weight:600; color:#444; margin-bottom:7px; }
        .fld { margin-bottom:18px; }
        .fld input[type=password] {
            width:100%; padding:12px 14px; border:1.6px solid #e6dede; border-radius:10px;
            font-size:13.5px; font-family:'Poppins',sans-serif; color:#333; background:#fff; transition:border-color .2s, box-shadow .2s;
        }
        .fld input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(123,29,29,.09); }

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

        .btn {
            display:inline-flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:13px 26px; border-radius:11px;
            font-size:13.5px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s;
            background:linear-gradient(135deg, var(--accent), var(--primary)); color:#fff; box-shadow:0 6px 16px rgba(123,29,29,.28);
            margin-top:8px;
        }
        .btn:hover { transform:translateY(-2px); box-shadow:0 9px 22px rgba(123,29,29,.38); }
    </style>
</head>
<body>
@php
    $u = auth()->user();
    $fullName = $u?->name ?? 'there';
    $first = trim(explode(' ', $fullName)[0] ?? 'there');
@endphp

<div class="setup-shell">
    <div class="brand-logo">
        <div class="bl-mark"><i class="fas fa-graduation-cap"></i></div>
        <div><strong>CPACE</strong><small>CPA Reviewer</small></div>
    </div>

    <div class="step-emoji"><i class="fas fa-lock"></i></div>
    <h2 class="step-h">Welcome, {{ $first }}!</h2>
    <p class="step-sub">You logged in with a one-time password from your Program Chair. Choose a new password only you know before continuing.</p>

    @if ($errors->any())
        <div style="background:#fde8e8; border:1px solid #f5c2c2; color:#991b1b; border-radius:11px; padding:12px 15px; font-size:12.5px; margin-bottom:16px; display:flex; gap:9px; align-items:flex-start;">
            <i class="fas fa-circle-exclamation" style="margin-top:2px;"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('faculty.account-setup.store') }}">
        @csrf

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
            <div class="hint" id="matchHint" style="font-size:11px; margin-top:6px;"></div>
        </div>

        <button type="submit" class="btn"><i class="fas fa-circle-check"></i> Set Password &amp; Continue</button>
    </form>
</div>

<script>
    window.togglePw = function (id, btn) {
        const el = document.getElementById(id), ic = btn.querySelector('i');
        if (el.type === 'password') { el.type = 'text'; ic.className = 'fas fa-eye-slash'; }
        else { el.type = 'password'; ic.className = 'fas fa-eye'; }
    };

    const pw = document.getElementById('pw');
    pw.addEventListener('input', () => {
        const v = pw.value;
        const rules = { len: v.length >= 8, upper: /[A-Z]/.test(v), lower: /[a-z]/.test(v), num: /[0-9]/.test(v) };
        let score = 0;
        Object.entries(rules).forEach(([k, ok]) => {
            const li = document.querySelector(`#pwRules li[data-rule="${k}"]`);
            li.classList.toggle('ok', ok);
            li.querySelector('i').className = ok ? 'fas fa-circle-check' : 'fas fa-circle';
            if (ok) score++;
        });
        const bars = document.querySelectorAll('#strength span');
        const colors = ['#ef4444', '#f59e0b', '#eab308', '#10b981'];
        const labels = ['Weak', 'Fair', 'Good', 'Strong'];
        bars.forEach((b, i) => b.style.background = i < score ? colors[score - 1] : '#eee');
        const lbl = document.getElementById('strengthLabel');
        lbl.textContent = v ? labels[Math.max(0, score - 1)] + ' password' : 'Password strength';
        lbl.style.color = v ? colors[Math.max(0, score - 1)] : '#bbb';
    });
    document.getElementById('pw2').addEventListener('input', () => {
        const h = document.getElementById('matchHint');
        const a = pw.value, b = document.getElementById('pw2').value;
        if (!b) { h.textContent = ''; return; }
        h.textContent = a === b ? '✓ Passwords match' : '✗ Passwords do not match';
        h.style.color = a === b ? '#059669' : '#ef4444';
    });
</script>
</body>
</html>
