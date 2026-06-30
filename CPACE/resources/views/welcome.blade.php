<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPAce — Your Edge to Ace CPALE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --maroon:      #7B1D1D;
            --maroon-dark: #5a1414;
            --maroon-mid:  #8B2525;
            --maroon-pale: #f9f0f0;
            --maroon-light: #f5e8e8;
            --maroon-border: #e8d5d5;
            --white:       #ffffff;
            --dark:        #1a1a1a;
            --gray:        #666666;
            --light-gray:  #f8f8f8;
        }

        html { scroll-behavior: smooth; }
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark);
            overflow-x: hidden;
            background: #fff;
        }

        /* ─── NAVBAR ──────────────────────────────────────────── */
        nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            height: 68px;
            background: rgba(255,255,255,.97);
            border-bottom: 1px solid var(--maroon-border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 6%;
            transition: box-shadow .3s;
        }
        nav.scrolled { box-shadow: 0 4px 24px rgba(123,29,29,.10); }

        .nav-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-logo img { height: 40px; object-fit: contain; }

        .nav-links { display: flex; gap: 2rem; list-style: none; }
        .nav-links a {
            font-size: .88rem; font-weight: 500; color: var(--dark);
            text-decoration: none; transition: color .2s;
        }
        .nav-links a:hover { color: var(--maroon); }

        .nav-actions { display: flex; gap: .75rem; align-items: center; }

        .btn-ghost {
            padding: .5rem 1.2rem; font-size: .85rem; font-weight: 600;
            color: var(--maroon); background: transparent;
            border: 1.5px solid var(--maroon); border-radius: 6px;
            text-decoration: none; transition: all .2s;
        }
        .btn-ghost:hover { background: var(--maroon); color: #fff; }

        .btn-solid {
            padding: .5rem 1.4rem; font-size: .85rem; font-weight: 600;
            color: #fff; background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-mid) 100%);
            border: none; border-radius: 6px; text-decoration: none;
            transition: transform .2s, box-shadow .2s;
        }
        .btn-solid:hover { transform: translateY(-1px); box-shadow: 0 5px 20px rgba(123,29,29,.3); }

        .hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; }
        .hamburger span { width: 24px; height: 2px; background: var(--dark); border-radius: 4px; }

        .mobile-menu {
            display: none; position: fixed; top: 68px; left: 0; right: 0;
            background: #fff; border-bottom: 1px solid var(--maroon-border);
            padding: 1rem 6%; z-index: 99; flex-direction: column; gap: .75rem;
        }
        .mobile-menu.open { display: flex; }
        .mobile-menu a {
            font-size: .9rem; font-weight: 500; color: var(--dark);
            text-decoration: none; padding: .5rem 0;
            border-bottom: 1px solid var(--maroon-border);
        }
        .mobile-menu a:last-child { border: none; }

        /* ─── HERO ────────────────────────────────────────────── */
        .hero {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 48fr 52fr;
            padding-top: 68px;
            background: linear-gradient(150deg, #ffffff 0%, #fdf5f5 50%, #f5e8e8 100%);
            position: relative;
            overflow: hidden;
        }

        /* decorative + marks matching the illustration style */
        .hero-plus {
            position: absolute; pointer-events: none; z-index: 1;
            color: var(--maroon); opacity: .18; font-weight: 300;
            line-height: 1; user-select: none;
        }
        .hp1 { top: 18%; left: 3%;  font-size: 1.6rem; }
        .hp2 { top: 38%; left: 44%; font-size: 1rem;   opacity: .12; }
        .hp3 { bottom: 32%; left: 6%; font-size: 1rem; opacity: .12; }
        .hp4 { top: 14%; right: 2%; font-size: 1.4rem; }
        .hp5 { bottom: 24%; right: 5%; font-size: 1rem; opacity: .1; }

        /* decorative clock ring (like in the illustration bg) */
        .hero-deco-ring {
            position: absolute; pointer-events: none; z-index: 1;
            border: 2px solid rgba(123,29,29,.1);
            border-radius: 50%;
        }
        .ring-sm { width: 52px; height: 52px; bottom: 30%; left: 2%; }
        .ring-sm::after {
            content: ''; position: absolute; top: 50%; left: 50%;
            transform: translate(-50%,-50%);
            width: 8px; height: 8px; border-radius: 50%;
            background: rgba(123,29,29,.15);
        }

        /* LEFT — text content */
        .hero-left {
            display: flex; flex-direction: column; justify-content: center;
            padding: 60px 4% 100px 7%;
            position: relative; z-index: 2;
        }

        .hero-eyebrow {
            display: inline-flex; align-items: center; gap: .45rem;
            background: var(--maroon-pale); border: 1px solid var(--maroon-border);
            color: var(--maroon); font-size: .75rem; font-weight: 600;
            padding: .3rem 1rem; border-radius: 50px; margin-bottom: 1.5rem;
            width: fit-content;
        }

        .hero-left h1 {
            font-size: clamp(2.1rem, 3.8vw, 3.4rem);
            font-weight: 800; line-height: 1.12; color: var(--dark);
            margin-bottom: 1.1rem; letter-spacing: -.5px;
        }
        .hero-left h1 .line-accent { color: var(--maroon); }
        .hero-left h1 .line-light { font-weight: 400; color: #444; }

        .hero-left > p {
            font-size: .96rem; color: var(--gray); line-height: 1.78;
            margin-bottom: 2.2rem; max-width: 420px;
        }

        .hero-cta { display: flex; gap: .9rem; flex-wrap: wrap; margin-bottom: 2.8rem; }

        .cta-primary {
            display: inline-flex; align-items: center; gap: .55rem;
            padding: .88rem 1.9rem;
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-mid) 100%);
            color: #fff; border-radius: 8px; font-weight: 700; font-size: .92rem;
            text-decoration: none; border: none; cursor: pointer;
            box-shadow: 0 6px 24px rgba(123,29,29,.32);
            transition: transform .2s, box-shadow .2s;
        }
        .cta-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 32px rgba(123,29,29,.42); }

        .cta-secondary {
            display: inline-flex; align-items: center; gap: .55rem;
            padding: .88rem 1.9rem;
            background: #fff; color: var(--maroon);
            border: 1.5px solid var(--maroon-border); border-radius: 8px;
            font-weight: 600; font-size: .92rem; text-decoration: none;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            transition: all .2s;
        }
        .cta-secondary:hover { border-color: var(--maroon); box-shadow: 0 4px 16px rgba(123,29,29,.15); }

        /* hero mini stats */
        .hero-stats {
            display: flex; gap: 2.2rem; padding-top: 2rem;
            border-top: 1px solid var(--maroon-border);
        }
        .hero-stat-val {
            font-size: 1.55rem; font-weight: 800; color: var(--maroon); line-height: 1;
        }
        .hero-stat-lbl {
            font-size: .71rem; color: var(--gray); font-weight: 500; margin-top: 4px;
        }

        /* RIGHT — illustration panel */
        .hero-right {
            position: relative; z-index: 2;
            display: flex; align-items: flex-end; justify-content: center;
            padding-top: 20px;
            overflow: hidden;
        }
        .hero-right img.hero-illustration {
            width: 100%;
            max-height: calc(100vh - 68px);
            object-fit: contain;
            object-position: center bottom;
            display: block;
            position: relative; z-index: 2;
        }

        /* maroon wave that sits at the bottom of hero, matching illustration */
        .hero-wave {
            position: absolute; bottom: 0; left: -2px; right: -2px; z-index: 3;
            line-height: 0;
        }
        .hero-wave svg { display: block; width: 100%; }

        @keyframes chipFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }

        /* ─── SUBJECTS BAR ────────────────────────────────────── */
        .subjects-bar {
            background: var(--maroon);
            padding: 1.3rem 6%;
            display: flex; align-items: center; justify-content: center;
            gap: 2.5rem; flex-wrap: wrap;
            margin-top: -2px;
        }
        .subjects-bar span {
            color: rgba(255,255,255,.75); font-size: .78rem; font-weight: 500;
            display: flex; align-items: center; gap: .5rem;
        }
        .subjects-bar span i { color: rgba(255,255,255,.5); font-size: .7rem; }
        .subjects-bar-label {
            color: rgba(255,255,255,.45) !important;
            font-size: .72rem !important; text-transform: uppercase;
            letter-spacing: .5px;
        }

        /* ─── SECTIONS ────────────────────────────────────────── */
        section { padding: 80px 6%; }

        .section-eyebrow {
            display: inline-flex; align-items: center; gap: .4rem;
            background: var(--maroon-pale); border: 1px solid var(--maroon-border);
            color: var(--maroon); font-size: .72rem; font-weight: 600;
            padding: .28rem .9rem; border-radius: 50px; margin-bottom: .9rem;
        }
        .section-title {
            font-size: clamp(1.7rem, 2.8vw, 2.4rem);
            font-weight: 800; line-height: 1.2; color: var(--dark);
            margin-bottom: .9rem; letter-spacing: -.3px;
        }
        .section-title span { color: var(--maroon); }
        .section-sub {
            font-size: .92rem; color: var(--gray); line-height: 1.7; max-width: 500px;
        }
        .text-center { text-align: center; }
        .sub-center { margin-left: auto; margin-right: auto; }

        /* ─── FEATURES ────────────────────────────────────────── */
        .features-section { background: var(--light-gray); }

        .features-grid {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem; margin-top: 3rem;
        }
        .feature-card {
            background: #fff; border-radius: 12px; padding: 1.8rem;
            border: 1px solid var(--maroon-border);
            box-shadow: 0 2px 12px rgba(0,0,0,.04);
            transition: transform .25s, box-shadow .25s, border-color .25s;
            position: relative; overflow: hidden;
        }
        .feature-card::after {
            content: '';
            position: absolute; bottom: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--maroon), var(--maroon-mid));
            transform: scaleX(0); transform-origin: left; transition: transform .3s;
        }
        .feature-card:hover { transform: translateY(-5px); box-shadow: 0 12px 40px rgba(123,29,29,.1); border-color: #d4a0a0; }
        .feature-card:hover::after { transform: scaleX(1); }

        .feature-icon {
            width: 48px; height: 48px; border-radius: 12px;
            background: var(--maroon-pale); border: 1px solid var(--maroon-border);
            display: flex; align-items: center; justify-content: center;
            color: var(--maroon); font-size: 1.15rem; margin-bottom: 1.1rem;
        }
        .feature-card h3 {
            font-size: .97rem; font-weight: 700; color: var(--dark); margin-bottom: .45rem;
        }
        .feature-card p { font-size: .84rem; color: var(--gray); line-height: 1.65; }

        /* ─── HOW IT WORKS ────────────────────────────────────── */
        .how-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 5rem;
            align-items: center; margin-top: 3.5rem;
        }
        .steps { display: flex; flex-direction: column; gap: 1.5rem; }

        .step {
            display: flex; gap: 1.2rem; align-items: flex-start;
            padding: 1.3rem; border-radius: 12px;
            border: 1px solid transparent; transition: all .25s; cursor: default;
        }
        .step:hover { background: #fff; border-color: var(--maroon-border); box-shadow: 0 4px 20px rgba(123,29,29,.07); }

        .step-num {
            width: 42px; height: 42px; border-radius: 10px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-mid) 100%);
            color: #fff; font-weight: 800; font-size: .85rem;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 12px rgba(123,29,29,.25);
        }
        .step h3 { font-size: .95rem; font-weight: 700; color: var(--dark); margin-bottom: .3rem; }
        .step p { font-size: .84rem; color: var(--gray); line-height: 1.6; }

        /* visual dashboard card */
        .dashboard-card {
            background: #fff; border-radius: 16px; overflow: hidden;
            border: 1px solid var(--maroon-border);
            box-shadow: 0 10px 40px rgba(123,29,29,.1);
        }
        .db-header {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-mid) 100%);
            padding: 1.4rem 1.6rem; color: #fff;
        }
        .db-header h4 { font-size: .8rem; font-weight: 600; opacity: .7; text-transform: uppercase; letter-spacing: .5px; }
        .db-score { font-size: 2.2rem; font-weight: 900; margin-top: .2rem; }
        .db-score span { font-size: .85rem; font-weight: 400; opacity: .7; }

        .db-body { padding: 1.5rem 1.6rem; }
        .db-subject { margin-bottom: 1rem; }
        .db-subject:last-child { margin-bottom: 0; }
        .db-sub-header { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .db-sub-name { font-size: .78rem; font-weight: 600; color: var(--dark); }
        .db-sub-pct { font-size: .78rem; font-weight: 700; color: var(--maroon); }
        .db-bar-bg { height: 6px; background: var(--maroon-light); border-radius: 4px; overflow: hidden; }
        .db-bar { height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--maroon), var(--maroon-mid)); }

        .db-footer {
            padding: 1rem 1.6rem;
            background: var(--maroon-pale);
            border-top: 1px solid var(--maroon-border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .db-badge {
            display: inline-flex; align-items: center; gap: .4rem;
            background: var(--maroon); color: #fff;
            font-size: .7rem; font-weight: 600; padding: .3rem .8rem; border-radius: 50px;
        }
        .db-next { font-size: .72rem; color: var(--gray); }

        /* ─── STATS BAND ──────────────────────────────────────── */
        .stats-band {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
            padding: 56px 6%;
        }
        .stats-grid {
            display: grid; grid-template-columns: repeat(4,1fr); gap: 2rem; text-align: center;
        }
        .stat-block { color: #fff; }
        .stat-block i { font-size: 1.5rem; opacity: .6; margin-bottom: .6rem; display: block; }
        .stat-block .num { font-size: 2.4rem; font-weight: 900; line-height: 1; }
        .stat-block .lbl { font-size: .78rem; opacity: .65; margin-top: .35rem; font-weight: 500; }

        /* ─── TESTIMONIALS ────────────────────────────────────── */
        .testi-section { background: var(--maroon-pale); }
        .testi-grid {
            display: grid; grid-template-columns: repeat(3,1fr);
            gap: 1.25rem; margin-top: 3rem;
        }
        .testi-card {
            background: #fff; border-radius: 12px; padding: 1.6rem;
            border: 1px solid var(--maroon-border);
            box-shadow: 0 2px 12px rgba(0,0,0,.04);
            transition: transform .25s, box-shadow .25s;
        }
        .testi-card:hover { transform: translateY(-4px); box-shadow: 0 10px 32px rgba(123,29,29,.1); }
        .stars { color: #f59e0b; font-size: .8rem; margin-bottom: .75rem; }
        .testi-text {
            font-size: .85rem; color: var(--gray); line-height: 1.7;
            margin-bottom: 1.2rem; font-style: italic;
        }
        .testi-author { display: flex; align-items: center; gap: .75rem; }
        .testi-av {
            width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-mid));
            color: #fff; font-weight: 700; font-size: .8rem;
            display: flex; align-items: center; justify-content: center;
        }
        .testi-name { font-size: .83rem; font-weight: 700; color: var(--dark); }
        .testi-role { font-size: .7rem; color: var(--gray); }

        /* ─── DOWNLOAD ────────────────────────────────────────── */
        .download-section {
            background: var(--dark);
            padding: 80px 6%;
            position: relative; overflow: hidden;
        }
        .download-section::before {
            content: ''; position: absolute; top: -180px; right: -120px;
            width: 500px; height: 500px; border-radius: 50%;
            background: radial-gradient(circle, rgba(123,29,29,.3) 0%, transparent 65%);
            pointer-events: none;
        }

        .download-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 5rem; align-items: center; }

        .dl-eyebrow { color: #f5a0a0 !important; background: rgba(123,29,29,.25) !important; border-color: rgba(123,29,29,.3) !important; }
        .download-grid .section-title { color: #fff; }
        .download-grid .section-sub { color: rgba(255,255,255,.5); }

        .btn-download {
            display: inline-flex; align-items: center; gap: 1rem;
            padding: 1rem 2rem; margin-top: 2rem;
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-mid) 100%);
            color: #fff; border-radius: 10px; text-decoration: none; border: none; cursor: pointer;
            box-shadow: 0 8px 28px rgba(123,29,29,.45);
            transition: transform .2s, box-shadow .2s;
        }
        .btn-download:hover { transform: translateY(-2px); box-shadow: 0 12px 36px rgba(123,29,29,.6); }
        .btn-download i { font-size: 1.5rem; }
        .btn-download-text small { display: block; font-size: .68rem; opacity: .75; font-weight: 400; }
        .btn-download-text strong { display: block; font-size: .95rem; font-weight: 700; margin-top: 1px; }

        .dl-checklist { margin-top: 1.6rem; display: flex; flex-direction: column; gap: .6rem; }
        .dl-check { display: flex; align-items: center; gap: .7rem; font-size: .85rem; color: rgba(255,255,255,.55); }
        .dl-check i { color: #4ade80; font-size: .8rem; flex-shrink: 0; }

        /* APK info card */
        .apk-wrap { display: flex; flex-direction: column; gap: 1.1rem; }
        .apk-card {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 14px; padding: 1.4rem;
        }
        .apk-card-top { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
        .apk-icon {
            width: 50px; height: 50px; border-radius: 12px;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-mid));
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; color: #fff; flex-shrink: 0;
        }
        .apk-name { font-size: .92rem; font-weight: 700; color: #fff; }
        .apk-desc { font-size: .72rem; color: rgba(255,255,255,.4); }
        .apk-meta { display: flex; gap: 2rem; }
        .apk-meta-item strong { display: block; font-size: .82rem; font-weight: 600; color: rgba(255,255,255,.8); }
        .apk-meta-item span { font-size: .7rem; color: rgba(255,255,255,.4); }

        .install-card {
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 14px; padding: 1.4rem;
        }
        .install-title {
            font-size: .72rem; font-weight: 600; color: rgba(255,255,255,.5);
            text-transform: uppercase; letter-spacing: .5px; margin-bottom: .9rem;
            display: flex; align-items: center; gap: .4rem;
        }
        .install-title i { color: #f5a0a0; }
        .install-steps { display: flex; flex-direction: column; gap: .6rem; }
        .install-step { display: flex; gap: .7rem; align-items: center; }
        .install-n {
            width: 20px; height: 20px; border-radius: 5px; flex-shrink: 0;
            background: var(--maroon); color: #fff; font-size: .6rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        .install-step span { font-size: .78rem; color: rgba(255,255,255,.5); }

        /* ─── FOOTER ──────────────────────────────────────────── */
        footer {
            background: #111; padding: 56px 6% 28px;
            color: rgba(255,255,255,.45);
        }
        .footer-grid {
            display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 3rem;
            padding-bottom: 2.5rem; border-bottom: 1px solid rgba(255,255,255,.07);
            margin-bottom: 1.5rem;
        }
        .footer-brand img { height: 36px; filter: brightness(0) invert(1); margin-bottom: .9rem; }
        .footer-brand p { font-size: .82rem; line-height: 1.7; }

        .footer-col h4 { font-size: .82rem; font-weight: 700; color: rgba(255,255,255,.8); margin-bottom: .9rem; }
        .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: .45rem; }
        .footer-col ul a { font-size: .8rem; color: rgba(255,255,255,.35); text-decoration: none; transition: color .2s; }
        .footer-col ul a:hover { color: #f5a0a0; }

        .footer-socials { display: flex; gap: .6rem; margin-top: 1rem; }
        .footer-socials a {
            width: 32px; height: 32px; border-radius: 8px;
            background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.09);
            display: flex; align-items: center; justify-content: center;
            color: rgba(255,255,255,.4); font-size: .78rem; text-decoration: none; transition: all .2s;
        }
        .footer-socials a:hover { background: var(--maroon); border-color: var(--maroon); color: #fff; }

        .footer-bottom {
            display: flex; justify-content: space-between; align-items: center;
            font-size: .75rem; flex-wrap: wrap; gap: .5rem;
        }
        .footer-bottom a { color: #f5a0a0; text-decoration: none; }

        /* ─── SCROLL REVEAL ───────────────────────────────────── */
        .reveal { opacity: 0; transform: translateY(24px); transition: opacity .55s ease, transform .55s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* ─── RESPONSIVE ──────────────────────────────────────── */
        @media (max-width: 1024px) {
            .hero { grid-template-columns: 1fr; }
            .hero-left { padding: 56px 6% 40px; text-align: center; }
            .hero-eyebrow { margin-left: auto; margin-right: auto; }
            .hero-left > p { margin-left: auto; margin-right: auto; }
            .hero-cta { justify-content: center; }
            .hero-stats { justify-content: center; }
            .hero-right { min-height: 380px; }
            .hero-right img.hero-illustration { max-height: 420px; }
            .section-sub { margin-left: auto; margin-right: auto; }
            .features-grid { grid-template-columns: 1fr 1fr; }
            .how-grid { grid-template-columns: 1fr; gap: 2.5rem; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .testi-grid { grid-template-columns: 1fr 1fr; }
            .download-grid { grid-template-columns: 1fr; gap: 2.5rem; }
            .footer-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 768px) {
            .nav-links, .nav-actions { display: none; }
            .hamburger { display: flex; }
            .features-grid, .testi-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .footer-grid { grid-template-columns: 1fr; }
            .footer-bottom { flex-direction: column; text-align: center; }
            .hero-right img.hero-illustration { max-height: 320px; }
            .hero-plus, .hero-deco-ring { display: none; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav id="navbar">
    <a href="/" class="nav-logo">
        <img src="{{ asset('images/cpace_logo.png') }}" alt="CPAce Logo">
    </a>
    <ul class="nav-links">
        <li><a href="#features">Features</a></li>
        <li><a href="#how-it-works">How It Works</a></li>
        <li><a href="#download">Download</a></li>
    </ul>
    <div class="nav-actions">
        <a href="{{ route('login') }}" class="btn-ghost">Log In</a>
        <a href="{{ route('signup') }}" class="btn-solid">Get Started Free</a>
    </div>
    <div class="hamburger" onclick="toggleMenu()">
        <span></span><span></span><span></span>
    </div>
</nav>

<div class="mobile-menu" id="mobileMenu">
    <a href="#features" onclick="toggleMenu()">Features</a>
    <a href="#how-it-works" onclick="toggleMenu()">How It Works</a>
    <a href="#download" onclick="toggleMenu()">Download APK</a>
    <a href="{{ route('login') }}">Log In</a>
    <a href="{{ route('signup') }}">Get Started &rarr;</a>
</div>

<!-- HERO -->
<div class="hero">

    <!-- decorative plus marks (matching illustration style) -->
    <span class="hero-plus hp1">+</span>
    <span class="hero-plus hp2">+</span>
    <span class="hero-plus hp3">+</span>
    <span class="hero-plus hp4">+</span>
    <span class="hero-plus hp5">+</span>
    <div class="hero-deco-ring ring-sm"></div>

    <!-- LEFT: content -->
    <div class="hero-left">
        <div class="hero-eyebrow reveal">
            <i class="fas fa-graduation-cap"></i>&nbsp; CPA Licensure Exam Reviewer
        </div>
        <h1 class="reveal">
            <span class="line-light">Master your</span><br>
            CPA journey<br>
            with <span class="line-accent">smart practice</span>
        </h1>
        <p class="reveal">
            CPAce is your all-in-one CPALE reviewer — adaptive quizzes, real-time performance analytics, and intelligent study tools built for serious CPA candidates.
        </p>
        <div class="hero-cta reveal">
            <a href="{{ route('signup') }}" class="cta-primary">
                <i class="fas fa-rocket"></i> Start Reviewing Free
            </a>
            <a href="#download" class="cta-secondary">
                <i class="fab fa-android"></i> Download APK
            </a>
        </div>
        <div class="hero-stats reveal">
            <div>
                <div class="hero-stat-val">5,000+</div>
                <div class="hero-stat-lbl">Practice Questions</div>
            </div>
            <div>
                <div class="hero-stat-val">98%</div>
                <div class="hero-stat-lbl">Pass Rate</div>
            </div>
            <div>
                <div class="hero-stat-val">6</div>
                <div class="hero-stat-lbl">CPA Subjects</div>
            </div>
        </div>
    </div>

    <!-- RIGHT: illustration -->
    <div class="hero-right">
        <img src="{{ asset('images/login_bg.png') }}"
             alt="CPAce Students — FAR, MAS, Taxation, Auditing"
             class="hero-illustration">

        <!-- maroon wave matching the illustration's bottom wave -->
        <div class="hero-wave">
            <svg viewBox="0 0 1440 90" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,60 C240,100 480,20 720,55 C960,90 1200,20 1440,50 L1440,90 L0,90 Z" fill="#7B1D1D"/>
            </svg>
        </div>
    </div>
</div>

<!-- SUBJECTS BAR -->
<div class="subjects-bar">
    <span class="subjects-bar-label">Covering all CPALE subjects</span>
    <span><i class="fas fa-circle"></i> Financial Accounting & Reporting</span>
    <span><i class="fas fa-circle"></i> Advanced Financial Accounting</span>
    <span><i class="fas fa-circle"></i> Auditing & Assurance</span>
    <span><i class="fas fa-circle"></i> Taxation</span>
    <span><i class="fas fa-circle"></i> Management Advisory Services</span>
    <span><i class="fas fa-circle"></i> Regulatory Framework</span>
</div>

<!-- FEATURES -->
<section class="features-section" id="features">
    <div class="text-center reveal">
        <div class="section-eyebrow"><i class="fas fa-star"></i> Core Features</div>
        <h2 class="section-title">Everything you need to <span>pass</span></h2>
        <p class="section-sub sub-center">A complete CPA review ecosystem designed around how board exam candidates actually study and retain knowledge.</p>
    </div>
    <div class="features-grid">
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-brain"></i></div>
            <h3>Adaptive Quizzes</h3>
            <p>AI-powered quizzes that adjust difficulty based on your performance, automatically focusing more time on your weak areas.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
            <h3>Performance Analytics</h3>
            <p>Real-time dashboards showing your score trends, subject-level breakdown, and board exam readiness score.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-sticky-note"></i></div>
            <h3>Review Notes</h3>
            <p>Create, organize, and favorite personal study notes that sync instantly across web and mobile.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-file-alt"></i></div>
            <h3>Mock Exams</h3>
            <p>Full-length timed mock exams that simulate the actual CPALE experience, with detailed post-exam reports.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
            <h3>Study Calendar</h3>
            <p>Visualize your study schedule, track completed sessions, and plan your review timeline leading to board day.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-trophy"></i></div>
            <h3>Achievements</h3>
            <p>Stay motivated with streak tracking, completion badges, and leaderboard standings that gamify your review.</p>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section id="how-it-works">
    <div class="reveal">
        <div class="section-eyebrow"><i class="fas fa-map"></i> How It Works</div>
        <h2 class="section-title">Pass the board in <span>4 simple steps</span></h2>
    </div>
    <div class="how-grid">
        <div class="steps">
            <div class="step reveal">
                <div class="step-num">01</div>
                <div>
                    <h3>Create Your Account</h3>
                    <p>Sign up in seconds using your email, Google, or Microsoft account. Progress syncs instantly across all your devices.</p>
                </div>
            </div>
            <div class="step reveal">
                <div class="step-num">02</div>
                <div>
                    <h3>Take a Diagnostic Quiz</h3>
                    <p>Complete a quick subject assessment. CPAce maps your strengths and gaps to build your personalized study path.</p>
                </div>
            </div>
            <div class="step reveal">
                <div class="step-num">03</div>
                <div>
                    <h3>Study & Review Daily</h3>
                    <p>Practice adaptive quizzes, create notes, and follow your calendar — daily sessions that compound over time.</p>
                </div>
            </div>
            <div class="step reveal">
                <div class="step-num">04</div>
                <div>
                    <h3>Track & Ace the Exam</h3>
                    <p>Monitor your weekly readiness score. When CPAce says you're ready — walk into that exam room with confidence.</p>
                </div>
            </div>
        </div>

        <div class="dashboard-card reveal">
            <div class="db-header">
                <h4>Your Performance Overview</h4>
                <div class="db-score">81% <span>Overall Readiness</span></div>
            </div>
            <div class="db-body">
                <div class="db-subject">
                    <div class="db-sub-header">
                        <span class="db-sub-name">Financial Accounting</span>
                        <span class="db-sub-pct">88%</span>
                    </div>
                    <div class="db-bar-bg"><div class="db-bar" style="width:88%"></div></div>
                </div>
                <div class="db-subject">
                    <div class="db-sub-header">
                        <span class="db-sub-name">Auditing & Assurance</span>
                        <span class="db-sub-pct">75%</span>
                    </div>
                    <div class="db-bar-bg"><div class="db-bar" style="width:75%"></div></div>
                </div>
                <div class="db-subject">
                    <div class="db-sub-header">
                        <span class="db-sub-name">Taxation</span>
                        <span class="db-sub-pct">69%</span>
                    </div>
                    <div class="db-bar-bg"><div class="db-bar" style="width:69%"></div></div>
                </div>
                <div class="db-subject">
                    <div class="db-sub-header">
                        <span class="db-sub-name">Management Advisory Services</span>
                        <span class="db-sub-pct">91%</span>
                    </div>
                    <div class="db-bar-bg"><div class="db-bar" style="width:91%"></div></div>
                </div>
                <div class="db-subject">
                    <div class="db-sub-header">
                        <span class="db-sub-name">Regulatory Framework</span>
                        <span class="db-sub-pct">82%</span>
                    </div>
                    <div class="db-bar-bg"><div class="db-bar" style="width:82%"></div></div>
                </div>
            </div>
            <div class="db-footer">
                <div class="db-badge"><i class="fas fa-fire"></i> 7-day streak</div>
                <span class="db-next"><i class="fas fa-calendar-alt"></i> Next: Taxation — Today 3PM</span>
            </div>
        </div>
    </div>
</section>

<!-- STATS BAND -->
<div class="stats-band">
    <div class="stats-grid">
        <div class="stat-block reveal">
            <i class="fas fa-users"></i>
            <div class="num">2,000+</div>
            <div class="lbl">Active Reviewees</div>
        </div>
        <div class="stat-block reveal">
            <i class="fas fa-question-circle"></i>
            <div class="num">5,000+</div>
            <div class="lbl">Practice Questions</div>
        </div>
        <div class="stat-block reveal">
            <i class="fas fa-medal"></i>
            <div class="num">98%</div>
            <div class="lbl">Satisfaction Rate</div>
        </div>
        <div class="stat-block reveal">
            <i class="fas fa-clock"></i>
            <div class="num">24/7</div>
            <div class="lbl">Study Anytime</div>
        </div>
    </div>
</div>

<!-- TESTIMONIALS -->
<section class="testi-section">
    <div class="text-center reveal">
        <div class="section-eyebrow"><i class="fas fa-heart"></i> Student Stories</div>
        <h2 class="section-title">Trusted by thousands of <span>reviewees</span></h2>
    </div>
    <div class="testi-grid">
        <div class="testi-card reveal">
            <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            <p class="testi-text">"The adaptive quiz feature is a game-changer. It felt like having a personal tutor that knew exactly what I needed to focus on. Passed on my first take!"</p>
            <div class="testi-author">
                <div class="testi-av">AM</div>
                <div>
                    <div class="testi-name">Angela M.</div>
                    <div class="testi-role">CPA Passer, May 2025</div>
                </div>
            </div>
        </div>
        <div class="testi-card reveal">
            <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            <p class="testi-text">"I used CPAce for 3 months before the board exam. The performance analytics showed me exactly which topics were dragging my score down — fixed them all."</p>
            <div class="testi-author">
                <div class="testi-av">RD</div>
                <div>
                    <div class="testi-name">Renz D.</div>
                    <div class="testi-role">CPA Passer, October 2025</div>
                </div>
            </div>
        </div>
        <div class="testi-card reveal">
            <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            <p class="testi-text">"Being able to review on my phone with the Android app made a huge difference. I could study anywhere and pick up right where I left off on my laptop."</p>
            <div class="testi-author">
                <div class="testi-av">KC</div>
                <div>
                    <div class="testi-name">Kristine C.</div>
                    <div class="testi-role">CPA Passer, May 2026</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- DOWNLOAD -->
<section class="download-section" id="download">
    <div class="download-grid">
        <div class="reveal">
            <div class="section-eyebrow dl-eyebrow"><i class="fab fa-android"></i> Mobile App</div>
            <h2 class="section-title">Review anywhere,<br><span>anytime</span></h2>
            <p class="section-sub">Take CPAce with you. The Android app gives you full access to your quizzes, notes, and analytics — even offline.</p>

            <a href="{{ asset('downloads/cpace.apk') }}" class="btn-download" download>
                <i class="fab fa-android"></i>
                <div class="btn-download-text">
                    <small>Download for Android</small>
                    <strong>Get the APK &darr;</strong>
                </div>
            </a>

            <div class="dl-checklist">
                <div class="dl-check"><i class="fas fa-check-circle"></i> Free to download — no hidden fees</div>
                <div class="dl-check"><i class="fas fa-check-circle"></i> Compatible with Android 8.0 and above</div>
                <div class="dl-check"><i class="fas fa-check-circle"></i> Syncs with your web account instantly</div>
                <div class="dl-check"><i class="fas fa-check-circle"></i> Offline mode for studying without internet</div>
            </div>
        </div>

        <div class="apk-wrap reveal">
            <div class="apk-card">
                <div class="apk-card-top">
                    <div class="apk-icon"><i class="fab fa-android"></i></div>
                    <div>
                        <div class="apk-name">CPAce Reviewer</div>
                        <div class="apk-desc">CPA Licensure Exam Reviewer</div>
                    </div>
                </div>
                <div class="apk-meta">
                    <div class="apk-meta-item"><strong>v1.0.0</strong><span>Version</span></div>
                    <div class="apk-meta-item"><strong>Android</strong><span>Platform</span></div>
                    <div class="apk-meta-item"><strong>Free</strong><span>Price</span></div>
                </div>
            </div>
            <div class="install-card">
                <div class="install-title"><i class="fas fa-shield-alt"></i> Installation Guide</div>
                <div class="install-steps">
                    <div class="install-step"><div class="install-n">1</div><span>Download the APK file above</span></div>
                    <div class="install-step"><div class="install-n">2</div><span>Enable "Install from unknown sources" in Android Settings</span></div>
                    <div class="install-step"><div class="install-n">3</div><span>Open the downloaded file and tap <strong style="color:rgba(255,255,255,.7)">Install</strong></span></div>
                    <div class="install-step"><div class="install-n">4</div><span>Log in with your CPAce account and start reviewing!</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="footer-grid">
        <div class="footer-brand">
            <img src="{{ asset('images/cpace_logo.png') }}" alt="CPAce">
            <p>CPAce is a comprehensive CPA licensure exam reviewer built to help Filipino accountancy students ace the CPALE board exam.</p>
            <div class="footer-socials">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        <div class="footer-col">
            <h4>Product</h4>
            <ul>
                <li><a href="#features">Features</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li><a href="#download">Download APK</a></li>
                <li><a href="{{ route('signup') }}">Sign Up Free</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Resources</h4>
            <ul>
                <li><a href="#">Study Tips</a></li>
                <li><a href="#">CPALE Guide</a></li>
                <li><a href="#">Subject Coverage</a></li>
                <li><a href="#">FAQ</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Company</h4>
            <ul>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
                <li><a href="#">Contact Us</a></li>
                <li><a href="{{ route('login') }}">Log In</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <span>&copy; 2026 CPAce. All rights reserved. — Your Edge to Ace CPALE</span>
        <span>Made with <span style="color:#f5a0a0">&#10084;</span> for Filipino CPA candidates</span>
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
