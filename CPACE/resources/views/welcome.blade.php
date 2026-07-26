<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPAce — Adaptive CPALE Review System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --maroon:       #7B1D1D;
            --maroon-dark:  #5a1414;
            --maroon-mid:   #8B2525;
            --maroon-bright:#a12626;
            --maroon-pale:  #f9f0f0;
            --maroon-light: #f5e8e8;
            --maroon-border:#e8d5d5;
            --accent-red:   #c0392b;
            --white:        #ffffff;
            --dark:         #1a1a1a;
            --gray:         #666666;
            --gray-light:   #f4f5f7;
            --gray-border:  #eef0f2;
        }

        html { scroll-behavior: smooth; }
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark);
            overflow-x: hidden;
            background: var(--gray-light);
        }

        /* ─── NAVBAR ──────────────────────────────────────────── */
        nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            height: 68px;
            background: rgba(255,255,255,.97);
            border-bottom: 1px solid var(--gray-border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0;
            transition: box-shadow .3s;
        }
        nav.scrolled { box-shadow: 0 4px 24px rgba(0,0,0,.08); }

        .nav-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; padding-left: 6%; }
        .nav-logo img { height: 40px; object-fit: contain; }
        .nav-logo .nav-wordmark { height: 28px; object-fit: contain; }

        .nav-links { display: flex; gap: 1.8rem; list-style: none; }
        .nav-links a {
            font-size: .85rem; font-weight: 500; color: var(--dark);
            text-decoration: none; transition: color .2s;
        }
        .nav-links a:hover { color: var(--maroon); }

        .nav-actions { display: flex; gap: .75rem; align-items: center; padding-right: 6%; }

        .btn-login {
            padding: .55rem 1.5rem; font-size: .85rem; font-weight: 600;
            color: #fff; background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
            border: none; border-radius: 10px; text-decoration: none;
            box-shadow: 0 4px 14px rgba(123,29,29,.25);
            transition: transform .2s, box-shadow .2s;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(123,29,29,.35); }

        .hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; }
        .hamburger span { width: 24px; height: 2px; background: var(--dark); border-radius: 4px; }

        .mobile-menu {
            display: none; position: fixed; top: 68px; left: 0; right: 0;
            background: #fff; border-bottom: 1px solid var(--gray-border);
            padding: 1rem 6%; z-index: 99; flex-direction: column; gap: .75rem;
            box-shadow: 0 8px 24px rgba(0,0,0,.08);
        }
        .mobile-menu.open { display: flex; }
        .mobile-menu a {
            font-size: .9rem; font-weight: 500; color: var(--dark);
            text-decoration: none; padding: .6rem 0;
            border-bottom: 1px solid var(--gray-border);
        }
        .mobile-menu a:last-child { border: none; }

        /* ─── HERO ────────────────────────────────────────────── */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 118px 6% 140px;
            background: linear-gradient(115deg, #6a1a1a 0%, #3a1010 40%, #130707 100%);
            position: relative;
            overflow: hidden;
        }

        .hero-shapes { position: absolute; inset: 0; pointer-events: none; z-index: 0; }
        .hero-shapes span { position: absolute; pointer-events: none; }
        .hero-shapes .hs1 { top: 12%; left: 5%; width: 180px; height: 180px; border: 2px solid rgba(255,255,255,.06); border-radius: 50%; }
        .hero-shapes .hs2 { bottom: 18%; left: 8%; width: 120px; height: 120px; border: 2px solid rgba(255,215,106,.1); border-radius: 30px; transform: rotate(28deg); }
        .hero-shapes .hs3 { top: 20%; right: 35%; width: 0; height: 0; border-left: 26px solid transparent; border-right: 26px solid transparent; border-bottom: 45px solid rgba(255,255,255,.04); transform: rotate(-18deg); }
        .hero-shapes .hs4 { bottom: 25%; right: 8%; width: 80px; height: 80px; background: rgba(192,57,43,.18); border-radius: 20px; transform: rotate(20deg); }
        .hero-shapes .hs5 { top: 35%; right: 5%; width: 100px; height: 100px; border: 1.5px solid rgba(255,255,255,.05); border-radius: 50%; }
        .hero-shapes .hs6 { top: 8%; right: 22%; width: 60px; height: 60px; border: 1.5px dashed rgba(255,255,255,.08); border-radius: 50%; animation: spinSlow 30s linear infinite; }
        @keyframes spinSlow { to { transform: rotate(360deg) } }

        .hero::before {
            content: ''; position: absolute; z-index: 1; pointer-events: none;
            top: 90px; right: 4%; width: 240px; height: 180px;
            background-image: radial-gradient(rgba(255,255,255,.1) 1.5px, transparent 1.6px);
            background-size: 19px 19px;
            -webkit-mask-image: radial-gradient(ellipse at top right, #000 25%, transparent 78%);
                    mask-image: radial-gradient(ellipse at top right, #000 25%, transparent 78%);
        }

        .hero-inner {
            width: 100%; max-width: 1280px; margin: 0 auto;
            display: grid; grid-template-columns: 48fr 52fr;
            gap: 4rem; align-items: center;
            position: relative; z-index: 2;
        }

        .hero-badge {
            display: inline-flex; align-items: center; gap: .55rem;
            background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
            color: #f5a0a0; font-size: .72rem; font-weight: 600;
            padding: .4rem 1rem .4rem .85rem; border-radius: 50px; margin-bottom: 1.5rem;
            backdrop-filter: blur(8px);
        }
        .pulse-dot { width: 7px; height: 7px; border-radius: 50%; background: #f5a0a0; position: relative; flex-shrink: 0; }
        .pulse-dot::after {
            content: ''; position: absolute; inset: -4px; border-radius: 50%;
            background: rgba(245,160,160,.4);
            animation: pulseDot 2s ease-out infinite;
        }
        @keyframes pulseDot { 0% { transform: scale(.4); opacity: .9 } 70%,100% { transform: scale(1.3); opacity: 0 } }

        .hero-copy h1 {
            font-size: clamp(2.2rem, 4vw, 3.4rem);
            font-weight: 800; line-height: 1.12; color: #fff;
            margin-bottom: 1.2rem; letter-spacing: -1px;
        }
        .hero-copy h1 span { color: #ffca6a; }
        .hero-copy > p {
            font-size: .95rem; color: rgba(255,255,255,.55); line-height: 1.8;
            margin-bottom: 2.2rem; max-width: 520px;
        }

        .hero-cta { display: flex; gap: .9rem; flex-wrap: wrap; margin-bottom: 2.4rem; }

        .cta-primary {
            display: inline-flex; align-items: center; gap: .6rem;
            padding: .95rem 2rem;
            background: linear-gradient(135deg, #ffca6a 0%, #e8a830 100%);
            color: #3a1010; border-radius: 12px; font-weight: 700; font-size: .92rem;
            text-decoration: none; border: none; cursor: pointer;
            box-shadow: 0 6px 24px rgba(255,202,106,.3);
            transition: transform .2s, box-shadow .2s;
        }
        .cta-primary i { transition: transform .25s; }
        .cta-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 32px rgba(255,202,106,.45); }
        .cta-primary:hover i { transform: translateX(4px); }

        .cta-secondary {
            display: inline-flex; align-items: center; gap: .6rem;
            padding: .95rem 2rem;
            background: rgba(255,255,255,.08); color: #fff;
            border: 1.5px solid rgba(255,255,255,.2); border-radius: 12px;
            font-weight: 600; font-size: .92rem; text-decoration: none;
            backdrop-filter: blur(8px); transition: all .2s;
        }
        .cta-secondary:hover { background: rgba(255,255,255,.15); border-color: rgba(255,255,255,.35); }

        .hero-proof {
            display: flex; align-items: center; gap: 1.5rem;
            padding-top: 1.8rem; border-top: 1px solid rgba(255,255,255,.1);
        }
        .hero-proof-item { display: flex; align-items: center; gap: .5rem; }
        .hero-proof-item i { font-size: 1rem; color: rgba(255,255,255,.35); }
        .hero-proof-item span { font-size: .78rem; color: rgba(255,255,255,.45); font-weight: 500; }
        .hero-proof-item strong { color: rgba(255,255,255,.8); }

        .hero-visual { position: relative; }

        .hero-visual-back {
            position: absolute; z-index: 0;
            top: 28px; left: 30px; right: -22px; bottom: -22px;
            border-radius: 30px; transform: rotate(2.4deg);
            background: linear-gradient(135deg, var(--maroon-bright) 0%, var(--maroon-dark) 100%);
            box-shadow: 0 20px 60px rgba(0,0,0,.35);
        }

        .hero-ring-spin {
            position: absolute; z-index: 1; pointer-events: none;
            width: 120px; height: 120px; bottom: -30px; left: -36px;
            border: 1.5px dashed rgba(255,255,255,.15); border-radius: 50%;
            animation: spinSlow 30s linear infinite;
        }

        .hero-visual-frame {
            position: relative; z-index: 2;
            aspect-ratio: 4 / 3; border-radius: 26px; overflow: hidden;
            background: #fdf3f3;
            border: 5px solid rgba(255,255,255,.1);
            box-shadow: 0 35px 90px -25px rgba(0,0,0,.55);
        }
        .hero-visual-frame img { width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; }

        .float-card {
            position: absolute; z-index: 3;
            background: rgba(26,10,10,.55); backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,.1); border-radius: 16px;
            box-shadow: 0 12px 34px rgba(0,0,0,.3);
            display: flex; align-items: center; gap: .7rem;
            padding: .75rem .95rem;
            animation: chipFloat 6s ease-in-out infinite;
        }
        .float-card strong { display: block; font-size: .78rem; font-weight: 700; color: #fff; line-height: 1.25; white-space: nowrap; }
        .float-card small { display: block; font-size: .66rem; color: rgba(255,255,255,.45); white-space: nowrap; }

        .fc-readiness { top: -24px; left: -34px; }
        .fc-streak { top: 42%; right: -26px; animation-delay: 1.6s; }
        .fc-exam { bottom: -22px; left: 10%; animation-delay: .8s; animation-duration: 7s; }

        .fc-ring {
            width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0;
            background: conic-gradient(#ffca6a 0 81%, rgba(255,255,255,.15) 81% 100%);
            display: flex; align-items: center; justify-content: center; position: relative;
        }
        .fc-ring::before { content: ''; position: absolute; inset: 6px; background: rgba(26,10,10,.85); border-radius: 50%; }
        .fc-ring span { position: relative; font-size: .68rem; font-weight: 800; color: #ffca6a; }

        .fc-ico {
            width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: .95rem;
        }
        .fc-ico.flame { background: linear-gradient(135deg, #7B1D1D, #a53232); color: #ffd9a0; }
        .fc-ico.check { background: rgba(16,185,129,.15); color: #34d399; border: 1px solid rgba(16,185,129,.2); }

        .hero-wave { position: absolute; bottom: -2px; left: 0; right: 0; z-index: 4; line-height: 0; pointer-events: none; }
        .hero-wave svg { display: block; width: 100%; height: 96px; }

        @keyframes chipFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }

        /* ─── SECTION UTILITIES ───────────────────────────────── */
        section { padding: 80px 6%; }

        .section-eyebrow {
            display: inline-flex; align-items: center; gap: .4rem;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: #f5a0a0; font-size: .72rem; font-weight: 600;
            padding: .3rem .95rem; border-radius: 50px; margin-bottom: .9rem;
            box-shadow: 0 2px 8px rgba(123,29,29,.2);
        }
        .section-title {
            font-size: clamp(1.7rem, 2.8vw, 2.4rem);
            font-weight: 800; line-height: 1.2; color: var(--dark);
            margin-bottom: .9rem; letter-spacing: -.3px;
        }
        .section-title span { color: var(--maroon); }
        .section-sub { font-size: .92rem; color: var(--gray); line-height: 1.7; max-width: 560px; }
        .text-center { text-align: center; }
        .sub-center { margin-left: auto; margin-right: auto; }

        /* ─── ABOUT ────────────────────────────────────────────── */
        .about-section { background: #fff; }
        .about-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 4rem;
            align-items: center; margin-top: 3rem;
        }
        .about-text p { font-size: .92rem; color: var(--gray); line-height: 1.8; margin-bottom: 1rem; }
        .about-text p strong { color: var(--dark); font-weight: 600; }

        .about-cards { display: flex; flex-direction: column; gap: 1rem; }
        .about-card {
            background: #fff; border-radius: 16px; padding: 1.3rem 1.5rem;
            border: 1px solid var(--gray-border);
            box-shadow: 0 2px 10px rgba(0,0,0,.03);
            display: flex; align-items: flex-start; gap: 1rem;
            transition: transform .2s, box-shadow .2s;
        }
        .about-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(123,29,29,.08); }
        .about-card-icon {
            width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            display: flex; align-items: center; justify-content: center;
            color: #f5a0a0; font-size: 1rem;
            box-shadow: 0 4px 12px rgba(123,29,29,.2);
        }
        .about-card h4 { font-size: .88rem; font-weight: 700; color: var(--dark); margin-bottom: .2rem; }
        .about-card p { font-size: .8rem; color: var(--gray); line-height: 1.55; margin: 0; }

        /* ─── FEATURES ────────────────────────────────────────── */
        .features-section { background: var(--gray-light); }
        .features-grid {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem; margin-top: 3rem;
        }
        .feature-card {
            background: #fff; border-radius: 18px; padding: 1.8rem;
            border: 1px solid var(--gray-border);
            box-shadow: 0 2px 10px rgba(0,0,0,.04);
            transition: transform .25s, box-shadow .25s;
            position: relative; overflow: hidden;
        }
        .feature-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, var(--maroon), var(--accent-red));
        }
        .feature-card::after {
            content: ''; position: absolute; top: -55px; right: -55px;
            width: 150px; height: 150px; border-radius: 50%;
            background: rgba(123,29,29,.04); pointer-events: none;
        }
        .feature-card:hover { transform: translateY(-5px); box-shadow: 0 12px 40px rgba(123,29,29,.1); }
        .feature-icon {
            width: 50px; height: 50px; border-radius: 14px;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            display: flex; align-items: center; justify-content: center;
            color: #f5a0a0; font-size: 1.2rem; margin-bottom: 1.2rem;
            box-shadow: 0 4px 12px rgba(123,29,29,.2);
        }
        .feature-card h3 { font-size: 1rem; font-weight: 700; color: var(--dark); margin-bottom: .5rem; }
        .feature-card p { font-size: .84rem; color: var(--gray); line-height: 1.65; }

        /* ─── CPALE SUBJECTS ──────────────────────────────────── */
        .subjects-section { background: #fff; }
        .subjects-grid {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem; margin-top: 3rem;
        }
        .subject-card {
            background: #fff; border-radius: 18px; padding: 1.6rem;
            border: 1px solid var(--gray-border);
            box-shadow: 0 2px 10px rgba(0,0,0,.04);
            text-align: center;
            transition: transform .25s, box-shadow .25s, border-color .25s;
            position: relative; overflow: hidden;
        }
        .subject-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, var(--maroon), var(--accent-red));
        }
        .subject-card:hover { transform: translateY(-4px); box-shadow: 0 10px 32px rgba(123,29,29,.1); border-color: #e3d5d5; }
        .subject-icon {
            width: 56px; height: 56px; border-radius: 16px; margin: 0 auto 1rem;
            background: linear-gradient(135deg, var(--maroon-pale), #fff);
            border: 1px solid var(--maroon-border);
            display: flex; align-items: center; justify-content: center;
            color: var(--maroon); font-size: 1.4rem;
        }
        .subject-card h3 { font-size: .9rem; font-weight: 700; color: var(--dark); margin-bottom: .3rem; }
        .subject-card .subject-code { font-size: .72rem; font-weight: 600; color: var(--maroon); text-transform: uppercase; letter-spacing: .5px; }
        .subject-card p { font-size: .8rem; color: var(--gray); line-height: 1.55; margin-top: .5rem; }

        /* ─── HOW IT WORKS ────────────────────────────────────── */
        .how-section { background: var(--gray-light); }
        .flow-container {
            display: flex; align-items: flex-start; justify-content: center;
            gap: 0; margin-top: 3rem; flex-wrap: wrap;
            position: relative;
        }
        .flow-step {
            display: flex; flex-direction: column; align-items: center;
            text-align: center; flex: 0 0 auto; width: 160px;
        }
        .flow-circle {
            width: 64px; height: 64px; border-radius: 50%;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.3rem;
            box-shadow: 0 6px 20px rgba(123,29,29,.3);
            margin-bottom: .8rem;
            position: relative;
        }
        .flow-circle .flow-num {
            position: absolute; top: -6px; right: -6px;
            width: 22px; height: 22px; border-radius: 50%;
            background: #ffca6a; color: #3a1010;
            font-size: .6rem; font-weight: 800;
            display: flex; align-items: center; justify-content: center;
        }
        .flow-step h4 { font-size: .82rem; font-weight: 700; color: var(--dark); margin-bottom: .2rem; }
        .flow-step p { font-size: .72rem; color: var(--gray); line-height: 1.45; padding: 0 .5rem; }
        .flow-arrow {
            display: flex; align-items: center; padding-top: 22px;
            color: var(--maroon-border); font-size: 1.2rem;
        }

        /* ─── BENEFITS ────────────────────────────────────────── */
        .benefits-section { background: #fff; }
        .benefits-grid {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem; margin-top: 3rem;
        }
        .benefit-card {
            background: #fff; border-radius: 18px; padding: 1.6rem;
            border: 1px solid var(--gray-border);
            box-shadow: 0 2px 10px rgba(0,0,0,.04);
            display: flex; align-items: flex-start; gap: 1rem;
            transition: transform .25s, box-shadow .25s;
            position: relative; overflow: hidden;
        }
        .benefit-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, var(--maroon), var(--accent-red));
        }
        .benefit-card:hover { transform: translateY(-4px); box-shadow: 0 10px 32px rgba(123,29,29,.08); }
        .benefit-icon {
            width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--maroon-pale), #fff);
            border: 1px solid var(--maroon-border);
            display: flex; align-items: center; justify-content: center;
            color: var(--maroon); font-size: 1rem;
        }
        .benefit-card h4 { font-size: .88rem; font-weight: 700; color: var(--dark); margin-bottom: .3rem; }
        .benefit-card p { font-size: .8rem; color: var(--gray); line-height: 1.55; margin: 0; }

        /* ─── FAQ ─────────────────────────────────────────────── */
        .faq-section { background: var(--gray-light); }
        .faq-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;
            margin-top: 3rem; max-width: 960px; margin-left: auto; margin-right: auto;
        }
        .faq-item {
            background: #fff; border-radius: 16px;
            border: 1px solid var(--gray-border);
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,.03);
            transition: box-shadow .2s;
        }
        .faq-item:hover { box-shadow: 0 6px 20px rgba(123,29,29,.06); }
        .faq-question {
            padding: 1.1rem 1.4rem;
            font-size: .85rem; font-weight: 600; color: var(--dark);
            cursor: pointer; display: flex; align-items: center; justify-content: space-between;
            gap: .8rem; user-select: none;
            border-left: 4px solid transparent;
            transition: border-color .2s, background .2s;
        }
        .faq-question:hover { background: var(--maroon-pale); border-left-color: var(--maroon); }
        .faq-question i { color: var(--maroon); font-size: .75rem; transition: transform .3s; flex-shrink: 0; }
        .faq-answer {
            max-height: 0; overflow: hidden;
            transition: max-height .3s ease, padding .3s ease;
        }
        .faq-answer-inner {
            padding: 0 1.4rem 1.1rem;
            font-size: .82rem; color: var(--gray); line-height: 1.65;
        }
        .faq-item.active .faq-question i { transform: rotate(180deg); }
        .faq-item.active .faq-answer { max-height: 200px; }
        .faq-item.active .faq-question { background: var(--maroon-pale); border-left-color: var(--maroon); }

        /* ─── CONTACT ──────────────────────────────────────────── */
        .contact-section { background: #fff; }
        .contact-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;
            align-items: start; margin-top: 3rem;
        }
        .contact-info-cards { display: flex; flex-direction: column; gap: 1rem; }
        .contact-card {
            background: #fff; border-radius: 16px; padding: 1.4rem 1.6rem;
            border: 1px solid var(--gray-border);
            box-shadow: 0 2px 10px rgba(0,0,0,.03);
            display: flex; align-items: flex-start; gap: 1rem;
            transition: transform .2s;
        }
        .contact-card:hover { transform: translateY(-2px); }
        .contact-card-icon {
            width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            display: flex; align-items: center; justify-content: center;
            color: #f5a0a0; font-size: .95rem;
        }
        .contact-card h4 { font-size: .85rem; font-weight: 700; color: var(--dark); margin-bottom: .15rem; }
        .contact-card p { font-size: .8rem; color: var(--gray); line-height: 1.5; margin: 0; }

        .contact-map-card {
            background: #fff; border-radius: 18px; overflow: hidden;
            border: 1px solid var(--gray-border);
            box-shadow: 0 2px 10px rgba(0,0,0,.04);
        }
        .contact-map-header {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            padding: 1.2rem 1.5rem; color: #fff;
        }
        .contact-map-header h3 { font-size: .95rem; font-weight: 700; }
        .contact-map-header p { font-size: .78rem; opacity: .7; margin-top: .2rem; }
        .contact-map-body {
            padding: 1.5rem;
        }
        .contact-map-body p { font-size: .82rem; color: var(--gray); line-height: 1.65; margin-bottom: .8rem; }
        .contact-map-body p:last-child { margin-bottom: 0; }
        .contact-map-body strong { color: var(--dark); }

        /* ─── INTENDED USERS ──────────────────────────────────── */
        .users-section { background: var(--gray-light); }
        .users-grid {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem; margin-top: 3rem;
        }
        .user-card {
            background: #fff; border-radius: 18px; padding: 2rem;
            border: 1px solid var(--gray-border);
            box-shadow: 0 2px 10px rgba(0,0,0,.04);
            text-align: center;
            transition: transform .25s, box-shadow .25s;
            position: relative; overflow: hidden;
        }
        .user-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, var(--maroon), var(--accent-red));
        }
        .user-card:hover { transform: translateY(-5px); box-shadow: 0 12px 40px rgba(123,29,29,.1); }
        .user-avatar {
            width: 72px; height: 72px; border-radius: 50%; margin: 0 auto 1.2rem;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            display: flex; align-items: center; justify-content: center;
            color: #f5a0a0; font-size: 1.8rem;
            box-shadow: 0 6px 20px rgba(123,29,29,.25);
        }
        .user-card h3 { font-size: 1.05rem; font-weight: 700; color: var(--dark); margin-bottom: .5rem; }
        .user-card p { font-size: .82rem; color: var(--gray); line-height: 1.6; }

        /* ─── SECURITY & DEVICES ──────────────────────────────── */
        .info-banner {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
            padding: 50px 6%;
            position: relative; overflow: hidden;
        }
        .info-banner::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at 80% 50%, rgba(192,57,43,.2), transparent 50%);
            pointer-events: none;
        }
        .info-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;
            max-width: 960px; margin: 0 auto; position: relative; z-index: 1;
        }
        .info-card {
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
            border-radius: 16px; padding: 1.6rem;
            backdrop-filter: blur(8px);
        }
        .info-card-icon {
            width: 44px; height: 44px; border-radius: 12px; margin-bottom: 1rem;
            background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
            display: flex; align-items: center; justify-content: center;
            color: #f5a0a0; font-size: 1.1rem;
        }
        .info-card h3 { font-size: 1rem; font-weight: 700; color: #fff; margin-bottom: .4rem; }
        .info-card p { font-size: .82rem; color: rgba(255,255,255,.55); line-height: 1.65; }

        /* ─── FOOTER ──────────────────────────────────────────── */
        footer {
            background: linear-gradient(180deg, #111 0%, #080404 100%);
            padding: 56px 6% 28px; color: rgba(255,255,255,.4);
        }
        .footer-grid {
            display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 3rem;
            padding-bottom: 2.5rem; border-bottom: 1px solid rgba(255,255,255,.06);
            margin-bottom: 1.5rem;
        }
        .footer-brand img { height: 36px; filter: brightness(0) invert(1); margin-bottom: .9rem; }
        .footer-brand p { font-size: .82rem; line-height: 1.7; }
        .footer-col h4 { font-size: .82rem; font-weight: 700; color: rgba(255,255,255,.75); margin-bottom: .9rem; }
        .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: .45rem; }
        .footer-col ul a { font-size: .8rem; color: rgba(255,255,255,.3); text-decoration: none; transition: color .2s; }
        .footer-col ul a:hover { color: #f5a0a0; }
        .footer-bottom {
            display: flex; justify-content: space-between; align-items: center;
            font-size: .72rem; flex-wrap: wrap; gap: .5rem;
        }
        .footer-bottom a { color: #f5a0a0; text-decoration: none; }

        /* ─── SCROLL REVEAL ───────────────────────────────────── */
        .reveal { opacity: 0; transform: translateY(24px); transition: opacity .55s ease, transform .55s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* ─── RESPONSIVE ──────────────────────────────────────── */
        @media (max-width: 1024px) {
            .hero { padding: 104px 6% 150px; }
            .hero-inner { grid-template-columns: 1fr; gap: 3.5rem; }
            .hero-copy { margin: 0 auto; text-align: center; }
            .hero-badge { margin-left: auto; margin-right: auto; }
            .hero-copy > p { margin-left: auto; margin-right: auto; }
            .hero-cta { justify-content: center; }
            .hero-proof { justify-content: center; flex-wrap: wrap; }
            .hero-visual { max-width: 520px; width: 100%; margin: 0 auto; }
            .section-sub { margin-left: auto; margin-right: auto; }
            .features-grid, .benefits-grid { grid-template-columns: 1fr 1fr; }
            .subjects-grid { grid-template-columns: 1fr 1fr; }
            .about-grid, .contact-grid { grid-template-columns: 1fr; }
            .faq-grid { grid-template-columns: 1fr; }
            .users-grid { grid-template-columns: 1fr 1fr; }
            .footer-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .hamburger { display: flex; }
            .features-grid, .benefits-grid, .subjects-grid, .users-grid, .testi-grid { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr; }
            .footer-bottom { flex-direction: column; text-align: center; }
            .hero { padding-bottom: 120px; }
            .hero-proof { flex-direction: column; gap: .6rem; }
            .float-card { padding: .55rem .7rem; border-radius: 12px; }
            .float-card strong { font-size: .7rem; }
            .float-card small { font-size: .6rem; }
            .fc-readiness { top: -16px; left: -6px; }
            .fc-streak { right: -6px; }
            .fc-exam { bottom: -16px; left: 6%; }
            .fc-ring { width: 38px; height: 38px; }
            .fc-ring::before { inset: 5px; }
            .fc-ico { width: 34px; height: 34px; font-size: .85rem; }
            .hero-wave svg { height: 64px; }
            .hero-shapes { display: none; }
            .hero::before { display: none; }
            .flow-container { flex-direction: column; align-items: center; }
            .flow-arrow { transform: rotate(90deg); padding: .5rem 0; }
            .flow-step { width: 100%; }
            .info-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav id="navbar">
    <a href="/" class="nav-logo">
        <img src="{{ asset('images/logo-icon.png') }}" alt="CPAce">
        <img src="{{ asset('images/wordmark-cropped.png') }}" alt="CPAce" class="nav-wordmark">
    </a>
    <ul class="nav-links">
        <li><a href="#about">About</a></li>
        <li><a href="#features">Features</a></li>
        <li><a href="#subjects">Subjects</a></li>
        <li><a href="#how-it-works">How It Works</a></li>
        <li><a href="#contact">Contact</a></li>
    </ul>
    <div class="nav-actions">
        <a href="{{ route('login') }}" class="btn-login">Login</a>
    </div>
    <div class="hamburger" onclick="toggleMenu()">
        <span></span><span></span><span></span>
    </div>
</nav>

<div class="mobile-menu" id="mobileMenu">
    <a href="#about" onclick="toggleMenu()">About</a>
    <a href="#features" onclick="toggleMenu()">Features</a>
    <a href="#subjects" onclick="toggleMenu()">Subjects</a>
    <a href="#how-it-works" onclick="toggleMenu()">How It Works</a>
    <a href="#contact" onclick="toggleMenu()">Contact</a>
    <a href="{{ route('login') }}">Login &rarr;</a>
</div>

<!-- HERO -->
<div class="hero">
    <div class="hero-shapes" aria-hidden="true">
        <span class="hs1"></span><span class="hs2"></span><span class="hs3"></span>
        <span class="hs4"></span><span class="hs5"></span><span class="hs6"></span>
    </div>

    <div class="hero-inner">
        <div class="hero-copy">
            <div class="hero-badge reveal">
                <span class="pulse-dot"></span>
                Institutional Review System
            </div>
            <h1 class="reveal">
                CPAce:<br>
                <span>Adaptive CPALE</span><br>
                Review System
            </h1>
            <p class="reveal">
                A web-based adaptive review platform developed to support Bachelor of Science in Accountancy students through personalized learning, performance analytics, and intelligent review scheduling.
            </p>
            <div class="hero-cta reveal">
                <a href="{{ route('login') }}" class="cta-primary">
                    Login <i class="fas fa-arrow-right"></i>
                </a>
                <a href="#about" class="cta-secondary">
                    <i class="fas fa-info-circle"></i> Learn More
                </a>
            </div>
            <div class="hero-proof reveal">
                <div class="hero-proof-item">
                    <i class="fas fa-university"></i>
                    <span><strong>Batangas State University</strong></span>
                </div>
                <div class="hero-proof-item">
                    <i class="fas fa-graduation-cap"></i>
                    <span>ARASOF-Nasugbu</span>
                </div>
                <div class="hero-proof-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Institutional Access</span>
                </div>
            </div>
        </div>

        <div class="hero-visual reveal">
            <div class="hero-visual-back" aria-hidden="true"></div>
            <div class="hero-ring-spin" aria-hidden="true"></div>
            <div class="hero-visual-frame">
                <img src="{{ asset('images/hero-section.png') }}" alt="CPAce Dashboard Preview">
            </div>

            <div class="float-card fc-readiness">
                <div class="fc-ring"><span>81%</span></div>
                <div>
                    <strong>Overall Readiness</strong>
                    <small>+6% this week</small>
                </div>
            </div>
            <div class="float-card fc-streak">
                <div class="fc-ico flame"><i class="fas fa-fire"></i></div>
                <div>
                    <strong>7-day streak</strong>
                    <small>Keep it going!</small>
                </div>
            </div>
            <div class="float-card fc-exam">
                <div class="fc-ico check"><i class="fas fa-check"></i></div>
                <div>
                    <strong>Mock Exam — FAR</strong>
                    <small>Scored 88% · Passed</small>
                </div>
            </div>
        </div>
    </div>

    <div class="hero-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 100" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,64 C180,94 400,34 640,54 C880,74 1080,26 1260,44 C1340,52 1400,50 1440,54 L1440,100 L0,100 Z" fill="rgba(0,0,0,.15)"/>
            <path d="M0,76 C240,104 480,46 720,68 C960,90 1200,42 1440,66 L1440,100 L0,100 Z" fill="#f4f5f7"/>
        </svg>
    </div>
</div>

<!-- ABOUT -->
<section class="about-section" id="about">
    <div class="text-center reveal">
        <div class="section-eyebrow"><i class="fas fa-info-circle"></i> About the System</div>
        <h2 class="section-title">What is <span>CPAce?</span></h2>
    </div>
    <div class="about-grid">
        <div class="about-text reveal">
            <p>
                <strong>CPAce</strong> is an institutional review system designed for Bachelor of Science in Accountancy students at Batangas State University ARASOF-Nasugbu. It assists learners in preparing for the CPA Licensure Examination through adaptive quizzes, personalized recommendations, and comprehensive performance monitoring.
            </p>
            <p>
                The system leverages adaptive learning algorithms to identify each student's knowledge gaps and dynamically adjust quiz difficulty, ensuring focused and efficient review sessions tailored to individual learning needs.
            </p>
            <p>
                Developed as a capstone project, CPAce aims to bridge the gap between traditional review methods and technology-driven learning, providing students with a intelligent study companion throughout their CPALE preparation journey.
            </p>
        </div>
        <div class="about-cards reveal">
            <div class="about-card">
                <div class="about-card-icon"><i class="fas fa-bullseye"></i></div>
                <div>
                    <h4>Purpose</h4>
                    <p>To support BSA students in their CPALE preparation through an adaptive, data-driven review system.</p>
                </div>
            </div>
            <div class="about-card">
                <div class="about-card-icon"><i class="fas fa-users"></i></div>
                <div>
                    <h4>Institutional Use</h4>
                    <p>Exclusively available to authorized students and faculty of the Department of Accountancy.</p>
                </div>
            </div>
            <div class="about-card">
                <div class="about-card-icon"><i class="fas fa-cogs"></i></div>
                <div>
                    <h4>Technology</h4>
                    <p>Built with adaptive algorithms, real-time analytics, and a comprehensive CPALE question bank.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="features-section" id="features">
    <div class="text-center reveal">
        <div class="section-eyebrow"><i class="fas fa-star"></i> Core Features</div>
        <h2 class="section-title">Intelligent review, <span>designed for CPALE</span></h2>
        <p class="section-sub sub-center">CPAce combines adaptive learning technology with comprehensive CPALE review tools to help students study smarter, not harder.</p>
    </div>
    <div class="features-grid">
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-brain"></i></div>
            <h3>Adaptive Quiz Engine</h3>
            <p>Generates quizzes dynamically based on the learner's performance, adjusting difficulty to target knowledge gaps.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-search-plus"></i></div>
            <h3>Weakness Detection</h3>
            <p>Identifies specific topics and subtopics requiring further review through continuous performance analysis.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-sync-alt"></i></div>
            <h3>Spaced Repetition</h3>
            <p>Schedules review sessions using spaced repetition principles to improve long-term knowledge retention.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-file-signature"></i></div>
            <h3>Mock CPALE Examination</h3>
            <p>Provides board exam simulation with timed conditions, replicating the actual CPALE testing experience.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
            <h3>Performance Dashboard</h3>
            <p>Displays progress, mastery levels, and quiz statistics through an intuitive real-time analytics dashboard.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-database"></i></div>
            <h3>Question Bank</h3>
            <p>Comprehensive collection of review questions organized by CPALE subject, continuously updated for relevance.</p>
        </div>
    </div>
</section>

<!-- CPALE SUBJECTS -->
<section class="subjects-section" id="subjects">
    <div class="text-center reveal">
        <div class="section-eyebrow"><i class="fas fa-book-open"></i> CPALE Subjects</div>
        <h2 class="section-title">Covering all <span>six CPALE subjects</span></h2>
        <p class="section-sub sub-center">CPAce provides comprehensive coverage of all subjects tested in the CPA Licensure Examination.</p>
    </div>
    <div class="subjects-grid">
        <div class="subject-card reveal">
            <div class="subject-icon"><i class="fas fa-calculator"></i></div>
            <h3>Financial Accounting and Reporting</h3>
            <div class="subject-code">FAR</div>
            <p>Comprehensive coverage of financial accounting standards, reporting frameworks, and financial statement preparation.</p>
        </div>
        <div class="subject-card reveal">
            <div class="subject-icon"><i class="fas fa-chart-pie"></i></div>
            <h3>Advanced Financial Accounting and Reporting</h3>
            <div class="subject-code">AFAR</div>
            <p>Advanced topics including consolidations, partnerships, government accounting, and foreign currency transactions.</p>
        </div>
        <div class="subject-card reveal">
            <div class="subject-icon"><i class="fas fa-receipt"></i></div>
            <h3>Taxation</h3>
            <div class="subject-code">TAX</div>
            <p>Income taxation, transfer taxes, VAT, and tax compliance for individuals and corporations under Philippine tax law.</p>
        </div>
        <div class="subject-card reveal">
            <div class="subject-icon"><i class="fas fa-balance-scale"></i></div>
            <h3>Regulatory Framework for Business Transactions</h3>
            <div class="subject-code">RFBT</div>
            <p>Commercial law, obligations, contracts, negotiable instruments, and regulatory frameworks governing business.</p>
        </div>
        <div class="subject-card reveal">
            <div class="subject-icon"><i class="fas fa-search"></i></div>
            <h3>Auditing</h3>
            <div class="subject-code">AUD</div>
            <p>Audit principles, procedures, standards, internal control evaluation, and assurance engagement practices.</p>
        </div>
        <div class="subject-card reveal">
            <div class="subject-icon"><i class="fas fa-lightbulb"></i></div>
            <h3>Management Advisory Services</h3>
            <div class="subject-code">MAS</div>
            <p>Management consulting, financial management, strategic planning, and business decision-making techniques.</p>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="how-section" id="how-it-works">
    <div class="text-center reveal">
        <div class="section-eyebrow"><i class="fas fa-route"></i> How It Works</div>
        <h2 class="section-title">Your journey to <span>CPA success</span></h2>
        <p class="section-sub sub-center">CPAce follows a systematic adaptive learning process to maximize your review efficiency.</p>
    </div>
    <div class="flow-container reveal">
        <div class="flow-step">
            <div class="flow-circle">
                <i class="fas fa-sign-in-alt"></i>
                <span class="flow-num">1</span>
            </div>
            <h4>Login</h4>
            <p>Access your account using institutional credentials</p>
        </div>
        <div class="flow-arrow"><i class="fas fa-chevron-right"></i></div>
        <div class="flow-step">
            <div class="flow-circle">
                <i class="fas fa-clipboard-list"></i>
                <span class="flow-num">2</span>
            </div>
            <h4>Diagnostic Quiz</h4>
            <p>Take an initial assessment to establish your baseline</p>
        </div>
        <div class="flow-arrow"><i class="fas fa-chevron-right"></i></div>
        <div class="flow-step">
            <div class="flow-circle">
                <i class="fas fa-crosshairs"></i>
                <span class="flow-num">3</span>
            </div>
            <h4>Weakness Detection</h4>
            <p>System identifies topics requiring focused review</p>
        </div>
        <div class="flow-arrow"><i class="fas fa-chevron-right"></i></div>
        <div class="flow-step">
            <div class="flow-circle">
                <i class="fas fa-map-signs"></i>
                <span class="flow-num">4</span>
            </div>
            <h4>Personalized Plan</h4>
            <p>Receive a customized study plan based on your gaps</p>
        </div>
        <div class="flow-arrow"><i class="fas fa-chevron-right"></i></div>
        <div class="flow-step">
            <div class="flow-circle">
                <i class="fas fa-dumbbell"></i>
                <span class="flow-num">5</span>
            </div>
            <h4>Adaptive Practice</h4>
            <p>Practice with quizzes that adjust to your level</p>
        </div>
        <div class="flow-arrow"><i class="fas fa-chevron-right"></i></div>
        <div class="flow-step">
            <div class="flow-circle">
                <i class="fas fa-chart-area"></i>
                <span class="flow-num">6</span>
            </div>
            <h4>Progress Monitoring</h4>
            <p>Track your improvement with real-time analytics</p>
        </div>
    </div>
</section>

<!-- BENEFITS -->
<section class="benefits-section" id="benefits">
    <div class="text-center reveal">
        <div class="section-eyebrow"><i class="fas fa-check-double"></i> Benefits</div>
        <h2 class="section-title">Why use <span>CPAce?</span></h2>
        <p class="section-sub sub-center">CPAce provides institutional-level advantages for CPALE preparation that traditional review methods cannot match.</p>
    </div>
    <div class="benefits-grid">
        <div class="benefit-card reveal">
            <div class="benefit-icon"><i class="fas fa-user-graduate"></i></div>
            <div>
                <h4>Personalized Learning</h4>
                <p>Adaptive algorithms tailor quiz difficulty and content to each student's unique learning profile.</p>
            </div>
        </div>
        <div class="benefit-card reveal">
            <div class="benefit-icon"><i class="fas fa-chart-bar"></i></div>
            <div>
                <h4>Data-Driven Monitoring</h4>
                <p>Real-time analytics provide actionable insights into student performance and readiness levels.</p>
            </div>
        </div>
        <div class="benefit-card reveal">
            <div class="benefit-icon"><i class="fas fa-calendar-alt"></i></div>
            <div>
                <h4>Efficient Review Scheduling</h4>
                <p>Spaced repetition ensures optimal review timing for maximum knowledge retention.</p>
            </div>
        </div>
        <div class="benefit-card reveal">
            <div class="benefit-icon"><i class="fas fa-redo"></i></div>
            <div>
                <h4>Continuous Assessment</h4>
                <p>Ongoing performance evaluation helps students and faculty track progress throughout the review period.</p>
            </div>
        </div>
        <div class="benefit-card reveal">
            <div class="benefit-icon"><i class="fas fa-trophy"></i></div>
            <div>
                <h4>Exam Readiness Support</h4>
                <p>Mock examinations simulate actual board exam conditions to build confidence and familiarity.</p>
            </div>
        </div>
        <div class="benefit-card reveal">
            <div class="benefit-icon"><i class="fas fa-mobile-alt"></i></div>
            <div>
                <h4>Multi-Device Access</h4>
                <p>Review anytime, anywhere — accessible on desktops, tablets, and smartphones.</p>
            </div>
        </div>
    </div>
</section>

<!-- INTENDED USERS -->
<section class="users-section">
    <div class="text-center reveal">
        <div class="section-eyebrow"><i class="fas fa-users"></i> Intended Users</div>
        <h2 class="section-title">Designed for <span>the academic community</span></h2>
        <p class="section-sub sub-center">CPAce serves distinct roles within the Department of Accountancy.</p>
    </div>
    <div class="users-grid">
        <div class="user-card reveal">
            <div class="user-avatar"><i class="fas fa-user-graduate"></i></div>
            <h3>Students</h3>
            <p>BSA students preparing for the CPA Licensure Examination. Access adaptive quizzes, track progress, and follow personalized study plans.</p>
        </div>
        <div class="user-card reveal">
            <div class="user-avatar"><i class="fas fa-chalkboard-teacher"></i></div>
            <h3>Faculty</h3>
            <p>Accountancy faculty members who monitor student performance, review analytics, and guide the review process.</p>
        </div>
        <div class="user-card reveal">
            <div class="user-avatar"><i class="fas fa-user-shield"></i></div>
            <h3>Administrator</h3>
            <p>System administrators who manage accounts, question banks, subject configurations, and overall system maintenance.</p>
        </div>
    </div>
</section>

<!-- SECURITY & DEVICES -->
<div class="info-banner">
    <div class="info-grid">
        <div class="info-card reveal">
            <div class="info-card-icon"><i class="fas fa-lock"></i></div>
            <h3>Secure Institutional Access</h3>
            <p>Access to CPAce is restricted to authorized institutional users only. All student progress and performance data are securely stored and protected within the university's system infrastructure.</p>
        </div>
        <div class="info-card reveal">
            <div class="info-card-icon"><i class="fas fa-laptop"></i></div>
            <h3>Device Compatibility</h3>
            <p>CPAce is fully responsive and accessible across desktops, tablets, and smartphones — allowing students to review anytime and anywhere with an internet connection.</p>
        </div>
    </div>
</div>

<!-- FAQ -->
<section class="faq-section" id="faq">
    <div class="text-center reveal">
        <div class="section-eyebrow"><i class="fas fa-question-circle"></i> FAQ</div>
        <h2 class="section-title">Frequently Asked <span>Questions</span></h2>
    </div>
    <div class="faq-grid">
        <div class="faq-item reveal">
            <div class="faq-question" onclick="toggleFaq(this)">
                Who can access CPAce?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    CPAce is exclusively available to authorized users of the Department of Accountancy at Batangas State University ARASOF-Nasugbu, including BSA students, faculty members, and designated administrators.
                </div>
            </div>
        </div>
        <div class="faq-item reveal">
            <div class="faq-question" onclick="toggleFaq(this)">
                How do I obtain my account?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    Accounts are created by the system administrator. Contact the Department of Accountancy to receive your login credentials. Self-registration is not available as access is institution-controlled.
                </div>
            </div>
        </div>
        <div class="faq-item reveal">
            <div class="faq-question" onclick="toggleFaq(this)">
                Can I review using my mobile phone?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    Yes. CPAce is fully responsive and works on any device with a modern web browser, including smartphones and tablets.
                </div>
            </div>
        </div>
        <div class="faq-item reveal">
            <div class="faq-question" onclick="toggleFaq(this)">
                Are quiz attempts recorded?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    Yes. All quiz attempts, scores, and performance data are recorded and reflected in your performance dashboard for ongoing progress monitoring.
                </div>
            </div>
        </div>
        <div class="faq-item reveal">
            <div class="faq-question" onclick="toggleFaq(this)">
                How is my progress evaluated?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    CPAce tracks your quiz performance, subject mastery levels, and readiness scores in real time. The adaptive engine continuously analyzes your results to identify strengths and areas for improvement.
                </div>
            </div>
        </div>
        <div class="faq-item reveal">
            <div class="faq-question" onclick="toggleFaq(this)">
                Is my data kept private?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    Yes. All data is securely stored within the institutional system. Your performance information is only accessible to you, your assigned faculty, and authorized administrators.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CONTACT -->
<section class="contact-section" id="contact">
    <div class="text-center reveal">
        <div class="section-eyebrow"><i class="fas fa-envelope"></i> Contact</div>
        <h2 class="section-title">Get in <span>touch</span></h2>
    </div>
    <div class="contact-grid">
        <div class="contact-info-cards reveal">
            <div class="contact-card">
                <div class="contact-card-icon"><i class="fas fa-user-cog"></i></div>
                <div>
                    <h4>System Administrator</h4>
                    <p>For technical issues, account concerns, and system-related inquiries.</p>
                </div>
            </div>
            <div class="contact-card">
                <div class="contact-card-icon"><i class="fas fa-university"></i></div>
                <div>
                    <h4>Department of Accountancy</h4>
                    <p>College of Accountancy, Business, Economics, and International Hospitality Management</p>
                </div>
            </div>
            <div class="contact-card">
                <div class="contact-card-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div>
                    <h4>Batangas State University ARASOF-Nasugbu</h4>
                    <p>Nasugbu, Batangas, Philippines</p>
                </div>
            </div>
            <div class="contact-card">
                <div class="contact-card-icon"><i class="fas fa-envelope"></i></div>
                <div>
                    <h4>Institutional Email</h4>
                    <p>accountancy@bsu.edu.ph</p>
                </div>
            </div>
        </div>
        <div class="contact-map-card reveal">
            <div class="contact-map-header">
                <h3><i class="fas fa-map-marked-alt"></i> Our Location</h3>
                <p>Batangas State University — ARASOF Nasugbu Campus</p>
            </div>
            <div class="contact-map-body">
                <p><strong>Batangas State University</strong> Pablo Borbon Campus is the main campus, while the <strong>ARASOF-Nasugbu</strong> campus houses the College of Accountancy, Business, Economics, and International Hospitality Management where CPAce was developed.</p>
                <p>For inquiries regarding the system, you may reach the Department of Accountancy or the system development team through the institutional email provided above.</p>
                <p><strong>Office Hours:</strong> Monday to Friday, 8:00 AM – 5:00 PM</p>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="footer-grid">
        <div class="footer-brand">
            <img src="{{ asset('images/cpace_logo.png') }}" alt="CPAce">
            <p>CPAce is an adaptive CPALE review system developed as a capstone project by the Department of Accountancy, Batangas State University ARASOF-Nasugbu.</p>
        </div>
        <div class="footer-col">
            <h4>System</h4>
            <ul>
                <li><a href="#about">About CPAce</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#subjects">CPALE Subjects</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Support</h4>
            <ul>
                <li><a href="#faq">FAQ</a></li>
                <li><a href="#contact">Contact Us</a></li>
                <li><a href="#">User Guide</a></li>
                <li><a href="#">Report an Issue</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Institution</h4>
            <ul>
                <li><a href="#">Batangas State University</a></li>
                <li><a href="#">Dept. of Accountancy</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Use</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <span>&copy; 2026 CPAce. All rights reserved.</span>
        <span>Version 1.0.0 &middot; Capstone Project &middot; BSU ARASOF-Nasugbu</span>
    </div>
</footer>

<script>
    // Navbar scroll
    window.addEventListener('scroll', () => {
        document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 20);
    });

    // Mobile menu
    function toggleMenu() {
        document.getElementById('mobileMenu').classList.toggle('open');
    }

    // FAQ accordion
    function toggleFaq(el) {
        const item = el.parentElement;
        const wasActive = item.classList.contains('active');
        document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('active'));
        if (!wasActive) item.classList.add('active');
    }

    // Scroll reveal
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), i * 70);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
</body>
</html>
