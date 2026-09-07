<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPAce — Adaptive Review. Smarter Preparation.</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --red:        #7B1D1D;   /* login --maroon        */
            --red-dark:   #5A1414;   /* login --maroon-dark   */
            --red-bright: #A12626;   /* login --maroon-bright */
            --red-soft:   #EBD6D6;
            --red-pale:   #F5E8E8;
            --navy:       #14283E;
            --navy-soft:  #1F3550;
            --body:       #66768A;
            --muted:      #93A0AE;
            --line:       #E5E9ED;
            --soft:       #F4F5F7;
            --ink:        #15181D;
            --container:  1320px;
        }

        html { scroll-behavior: smooth; overflow-x: hidden; }
        body {
            font-family: 'Montserrat', ui-sans-serif, system-ui, sans-serif;
            color: var(--body);
            background: #fff;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        img { max-width: 100%; display: block; }
        a { text-decoration: none; }
        ul { list-style: none; }

        .container { width: 100%; max-width: var(--container); margin: 0 auto; padding: 0 32px; position: relative; z-index: 1; }

        /* ─── SHARED BITS ─────────────────────────────────────── */
        .eyebrow {
            display: flex; align-items: center; gap: 12px;
            font-size: .7rem; font-weight: 600; letter-spacing: .17em;
            text-transform: uppercase; color: var(--navy); margin-bottom: 18px;
        }
        .eyebrow::before { content: ''; width: 30px; height: 2px; background: var(--red); flex: 0 0 auto; }
        .eyebrow .nocaps { text-transform: none; }
        .eyebrow.on-red { color: rgba(255,255,255,.92); }
        .eyebrow.on-red::before { background: rgba(255,255,255,.85); }

        .sec-title {
            font-size: clamp(1.55rem, 2.35vw, 2.05rem); font-weight: 700;
            color: var(--navy); line-height: 1.3; letter-spacing: -.022em;
        }
        .sec-text { font-size: .8rem; line-height: 1.72; color: var(--body); margin-top: 20px; max-width: 420px; }

        .btn {
            display: inline-flex; align-items: center; gap: 10px;
            padding: .82rem 1.75rem; border-radius: 8px; white-space: nowrap;
            font-size: .76rem; font-weight: 600; letter-spacing: .005em;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
        }
        .btn i { font-size: .72rem; transition: transform .18s ease; }
        .btn:hover i { transform: translateX(3px); }

        .btn-red { background: var(--red); color: #fff; box-shadow: 0 8px 20px rgba(123,29,29,.26); }
        .btn-red:hover { background: var(--red-dark); transform: translateY(-2px); box-shadow: 0 12px 26px rgba(123,29,29,.34); }

        .btn-outline { background: #fff; color: var(--red); border: 1.5px solid var(--red); }
        .btn-outline:hover { background: var(--red-pale); transform: translateY(-2px); }

        .btn-white { background: #fff; color: var(--red); box-shadow: 0 8px 22px rgba(0,0,0,.16); }
        .btn-white:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(0,0,0,.2); }

        .btn-pill { border-radius: 999px; }

        /* wordmark */
        .wordmark { font-weight: 800; letter-spacing: -.035em; line-height: 1; }
        .wordmark .w-a { color: var(--red); }
        .wordmark .w-b { color: var(--navy); }
        .wordmark.on-dark .w-a, .wordmark.on-dark .w-b { color: #fff; }

        /* ─── NAVBAR ──────────────────────────────────────────── */
        header.nav {
            position: fixed; inset: 0 0 auto 0; z-index: 200; height: 86px;
            background: rgba(255,255,255,.96);
            backdrop-filter: saturate(180%) blur(10px);
            display: flex; align-items: center;
            transition: box-shadow .25s ease, height .25s ease;
        }
        header.nav.scrolled { box-shadow: 0 2px 18px rgba(20,32,48,.06); height: 74px; }
        .nav-inner { display: flex; align-items: center; width: 100%; }
        .nav-brand { margin-right: 48px; display: flex; align-items: center; }
        .nav-brand img { height: 47px; width: auto; object-fit: contain; transition: height .25s ease; }
        header.nav.scrolled .nav-brand img { height: 41px; }

        .nav-links { display: flex; gap: 2.1rem; margin-right: auto; }
        .nav-links a {
            position: relative; font-size: .92rem; font-weight: 500;
            color: var(--navy-soft); padding-bottom: 6px; transition: color .18s;
        }
        .nav-links a::after {
            content: ''; position: absolute; left: 0; bottom: 0; height: 2px;
            width: 0; background: var(--red); transition: width .22s ease;
        }
        .nav-links a:hover { color: var(--red); }
        .nav-links a:hover::after, .nav-links a.active::after { width: 100%; }
        .nav-links a.active { color: var(--red); font-weight: 600; }

        .nav-actions { display: flex; align-items: center; gap: 1.2rem; }

        .burger { display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: 6px; }
        .burger span { width: 22px; height: 2px; background: var(--navy); border-radius: 3px; transition: .25s; }

        .mobile-menu {
            position: fixed; top: 86px; left: 0; right: 0; z-index: 199;
            background: #fff; border-bottom: 1px solid var(--line);
            padding: 14px 28px 20px; display: none; flex-direction: column;
            box-shadow: 0 14px 30px rgba(20,30,45,.1);
        }
        .mobile-menu.open { display: flex; }
        .mobile-menu a {
            font-size: .85rem; font-weight: 500; color: var(--navy);
            padding: .75rem 0; border-bottom: 1px solid var(--line);
        }
        .mobile-menu a:last-child { border: none; color: var(--red); font-weight: 600; }

        /* ─── HERO ────────────────────────────────────────────── */
        .hero {
            position: relative; background: #fff; overflow: hidden;
            min-height: 100vh; min-height: 100svh;
            display: flex; align-items: center;
            padding: 118px 0 72px;
        }

        /* The source image is already desaturated and already fades to white
           on its left side, so it is laid in full-bleed with no filter or
           gradient of our own on top of it. */
        .hero-photo, .hero-photo-fg {
            position: absolute; z-index: 0;
            bottom: 0; left: 9.3%; width: 84%;
            aspect-ratio: 1920 / 1080;
            background-size: 100% 100%;
            background-repeat: no-repeat;
            -webkit-mask-image: linear-gradient(to bottom, transparent 0%, #000 7%, #000 74%, transparent 99%),
                                linear-gradient(to right, transparent 0%, #000 14%);
            -webkit-mask-composite: source-in;
                    mask-image: linear-gradient(to bottom, transparent 0%, #000 7%, #000 74%, transparent 99%),
                                linear-gradient(to right, transparent 0%, #000 14%);
                    mask-composite: intersect;
        }
        /* Base plate: already desaturated and faded to white, so it is
           multiplied onto the hero to drop its near-white edges. */
        .hero-photo {
            background-image: url("{{ asset('images/landing page.png') }}");
            mix-blend-mode: multiply;
            filter: brightness(1.06) contrast(.94);
        }
        /* Cut-out of the same frame (transparent sky) laid over the plate so
           the building reads clearly instead of washing out. */
        .hero-photo-fg {
            background-image: url("{{ asset('images/overaly.png') }}");
            transform: translate(1.042%, -2.222%);
            z-index: 2;
        }
        /* The cut-out tops out at ~87% alpha, so the red shapes behind it bled
           through the building. A second identical pass takes it to ~98%. */
        .hero-photo-fg::after {
            content: ''; position: absolute; inset: 0;
            background-image: inherit;
            background-size: 100% 100%;
            background-repeat: no-repeat;
        }

        .hero-slashes { position: absolute; inset: 0; z-index: 1; pointer-events: none; overflow: hidden; }
        .hero-inner { position: relative; z-index: 3; }
        .hero-slashes i {
            position: absolute; display: block; transform: skewX(-19deg); border-radius: 4px;
        }
        .hs-1 { top: -14%; right: -5%;  width: 13%;  height: 82%; background: linear-gradient(165deg, var(--red-bright), var(--red-dark)); }
        .hs-2 { top: -14%; right: 9%;    width: 2.7%; height: 66%; background: linear-gradient(165deg, rgba(156,38,38,.82), rgba(94,20,20,.66)); }
        .hs-3 { top: -14%; right: 12.4%; width: .9%;  height: 52%; background: rgba(156,38,38,.32); }
        .hs-4 { bottom: -8%; right: -5%;  width: 9.6%; height: 36%; background: linear-gradient(195deg, var(--red-dark), var(--red-bright)); }
        .hs-5 { bottom: -8%; right: 7.4%; width: 1.7%; height: 25%; background: rgba(156,38,38,.44); }

        .hero-copy { max-width: 560px; }

        .hero-brand { line-height: 0; margin-bottom: 16px; }
        .hero-brand img {
            height: clamp(38px, 5.4vw, 78px); width: auto; object-fit: contain;
        }
        .hero-head {
            font-size: clamp(1.35rem, 2.1vw, 1.88rem); font-weight: 700;
            color: var(--navy); line-height: 1.34; letter-spacing: -.022em;
        }
        .hero-sub { font-size: .9rem; line-height: 1.6; color: var(--body); margin-top: 22px; max-width: 374px; }
        .hero-cta { display: flex; gap: 14px; margin-top: 26px; flex-wrap: wrap; }

        .hero-mini { display: flex; align-items: center; margin-top: 50px; flex-wrap: wrap; }
        .hero-mini .mini {
            display: flex; align-items: center; gap: 13px;
            padding: 3px 30px; border-right: 1px solid var(--line);
        }
        .hero-mini .mini:first-child { padding-left: 0; }
        .hero-mini .mini:last-child { border-right: none; }
        .mini-ico {
            width: 38px; height: 38px; border-radius: 50%; flex: 0 0 auto;
            background: var(--red-pale); color: var(--red);
            display: grid; place-items: center; font-size: .82rem;
        }
        .mini-txt { font-size: .66rem; font-weight: 600; color: var(--navy); line-height: 1.5; }

        /* ─── WHY / ABOUT ─────────────────────────────────────── */
                /* ─── DARK BAND ───────────────────────────────────────── */
        /* The login screen's three-layer backdrop — dark campus photo, a
           maroon-to-near-black scrim, then a soft dot texture — reused by
           every red section on the page so they read as one surface. */
        .band { position: relative; overflow: hidden; background: #0d0505; }
        .band-bg {
            position: absolute; inset: 0; z-index: 0;
            background: url("{{ asset('images/CPACE login bg.png') }}") center / cover no-repeat;
            filter: grayscale(35%) brightness(.45) saturate(.85);
            transform: scale(1.05);
        }
        .band-scrim {
            position: absolute; inset: 0; z-index: 1;
            background:
                radial-gradient(ellipse 70% 60% at 20% 15%, rgba(161,38,38,.38), transparent 62%),
                radial-gradient(ellipse 60% 55% at 85% 90%, rgba(123,29,29,.32), transparent 60%),
                linear-gradient(135deg, rgba(19,7,7,.90) 0%, rgba(58,16,16,.86) 45%, rgba(13,5,5,.94) 100%);
        }
        .band-dots {
            position: absolute; inset: 0; z-index: 2; pointer-events: none;
            background-image: radial-gradient(rgba(255,255,255,.075) 1.5px, transparent 1.6px);
            background-size: 20px 20px;
            -webkit-mask-image: radial-gradient(ellipse at top right, #000 20%, transparent 75%);
                    mask-image: radial-gradient(ellipse at top right, #000 20%, transparent 75%);
        }
        .band .deco      { z-index: 3; }
        .band .container { z-index: 4; }

        .why { padding: 96px 0 100px; }

        /* Type and rules flipped for the dark ground */
        .why .eyebrow          { color: rgba(255,255,255,.92); }
        .why .eyebrow::before  { background: #D9A0A0; }
        .why .sec-title        { color: #fff; }
        .why .sec-text         { color: rgba(255,255,255,.8); }
        .why .why-right        { border-left-color: rgba(255,255,255,.15); }
        .why .feat-ico {
            background: rgba(255,255,255,.1); color: #EFC6C6;
            border: 1px solid rgba(255,255,255,.15);
        }
        .why .feat h4          { color: #fff; }
        .why .feat p           { color: rgba(255,255,255,.78); }

        /* Ornaments have to go light here or they vanish into the dark. */
        .why .deco-dots { color: rgba(255,255,255,.2); }
        .why .deco-ring { border-color: rgba(255,255,255,.14); }
        .why .deco-tile { border-color: rgba(255,255,255,.15); }
        .why .deco-glow { background: radial-gradient(circle, rgba(255,255,255,.07) 0%, rgba(255,255,255,0) 70%); }
        .why-grid { display: grid; grid-template-columns: 360px 1fr; gap: 62px; align-items: start; }
        .why-right { border-left: 1px solid var(--line); padding-left: 62px; }
        .feat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 42px 58px; max-width: 690px; }
        .feat { display: flex; gap: 16px; }
        .feat-ico {
            width: 44px; height: 44px; border-radius: 50%; flex: 0 0 auto;
            background: var(--red-pale); color: var(--red);
            display: grid; place-items: center; font-size: .92rem;
        }
        .feat h4 { font-size: .84rem; font-weight: 600; color: var(--navy); margin-bottom: 7px; }
        .feat p { font-size: .76rem; line-height: 1.72; color: var(--body); }

        /* ─── HOW IT WORKS ────────────────────────────────────── */
        .how { padding: 88px 0 96px; background: var(--soft); position: relative; overflow: hidden; }
        .how-grid { display: grid; grid-template-columns: 400px 1fr; gap: 60px; align-items: center; }

        .steps { margin-top: 34px; }
        .step { display: flex; gap: 16px; position: relative; padding-bottom: 24px; }
        .step:last-child { padding-bottom: 0; }
        .step::before {
            content: ''; position: absolute; left: 13px; top: 30px; bottom: 2px;
            width: 1.5px; background: #E2CDCD;
        }
        .step:last-child::before { display: none; }
        .step-num {
            width: 27px; height: 27px; border-radius: 50%; flex: 0 0 auto; z-index: 1;
            background: var(--red); color: #fff; font-size: .7rem; font-weight: 700;
            display: grid; place-items: center;
        }
        .step h4 { font-size: .82rem; font-weight: 600; color: var(--navy); margin-bottom: 4px; }
        .step p { font-size: .74rem; line-height: 1.6; color: var(--body); }

        /* dashboard mockup */
        .mock-wrap { position: relative; max-width: 760px; margin-left: auto; }
        .mock-behind {
            position: absolute; z-index: 0; border-radius: 20px;
            top: 26px; bottom: -20px; left: -26px; right: 22px;
            background: linear-gradient(150deg, var(--red-bright), var(--red-dark));
            opacity: .92;
        }
        .mock {
            position: relative; z-index: 1; display: grid; grid-template-columns: 128px 1fr;
            background: #fff; border-radius: 12px; overflow: hidden;
            box-shadow: 0 30px 70px rgba(20,32,48,.16); font-size: 11px;
        }
        .mock-side { background: linear-gradient(160deg, #3A1010, #130707); padding: 14px 10px; }
        .mock-side .wordmark { font-size: .92rem; margin: 2px 0 16px 6px; }
        .mock-side ul li {
            display: flex; align-items: center; gap: 8px;
            font-size: 8.5px; color: rgba(255,255,255,.6);
            padding: 7px 8px; border-radius: 6px; margin-bottom: 2px;
        }
        .mock-side ul li i { font-size: 8px; width: 10px; }
        .mock-side ul li.on { background: rgba(255,255,255,.13); color: #fff; font-weight: 600; }

        .mock-main { background: #FBFBFC; padding: 12px 14px 14px; }
        .mock-top { display: flex; justify-content: flex-end; margin-bottom: 12px; }
        .mock-user { display: flex; align-items: center; gap: 6px; font-size: 8px; color: var(--navy); font-weight: 500; }
        .mock-avatar { width: 16px; height: 16px; border-radius: 50%; background: var(--red-pale); display: grid; place-items: center; color: var(--red); font-size: 7px; }

        .mock-hello { font-size: 10.5px; font-weight: 700; color: var(--navy); }
        .mock-hello + span { font-size: 7.5px; color: var(--muted); display: block; margin-top: 2px; }

        .mock-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 11px; }
        .mock-card { background: #fff; border: 1px solid #EFF1F4; border-radius: 8px; padding: 10px; }
        .mock-card h6 { font-size: 8px; font-weight: 600; color: var(--navy); margin-bottom: 8px; }
        .mock-card h6 span { float: right; color: var(--muted); font-weight: 400; }

        .donut-row { display: flex; align-items: center; gap: 10px; }
        .donut {
            width: 46px; height: 46px; border-radius: 50%; flex: 0 0 auto;
            background: conic-gradient(var(--red) 0 68%, #EFEFF2 68% 100%);
            display: grid; place-items: center;
        }
        .donut::after {
            content: '68%'; width: 34px; height: 34px; border-radius: 50%; background: #fff;
            display: grid; place-items: center; font-size: 8.5px; font-weight: 700; color: var(--navy);
        }
        .donut-bars { flex: 1; }
        .donut-bars span { display: block; font-size: 7px; color: var(--muted); margin-bottom: 5px; }
        .bar { height: 5px; border-radius: 4px; background: #EFEFF2; overflow: hidden; }
        .bar i { display: block; height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--red), var(--red-bright)); }

        .weak { display: flex; align-items: center; gap: 7px; margin-bottom: 6px; font-size: 7.5px; color: var(--navy); }
        .weak b { width: 22px; font-weight: 600; }
        .weak .bar { flex: 1; }
        .weak em { font-style: normal; color: var(--muted); width: 20px; text-align: right; }

        .mock-reco { font-size: 8px; font-weight: 600; color: var(--navy); margin: 12px 0 8px; }
        .reco-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .reco {
            background: #fff; border: 1px solid #EFF1F4; border-radius: 8px; padding: 9px;
        }
        .reco .r-ico { width: 17px; height: 17px; border-radius: 5px; background: var(--red-pale); color: var(--red); display: grid; place-items: center; font-size: 7px; margin-bottom: 7px; }
        .reco h6 { font-size: 7.5px; font-weight: 600; color: var(--navy); }
        .reco small { font-size: 6.5px; color: var(--muted); display: block; margin: 2px 0 8px; }
        .reco b { display: block; background: var(--red); color: #fff; font-size: 6.5px; font-weight: 600; text-align: center; padding: 4px 0; border-radius: 4px; }

        /* ─── FEATURES ────────────────────────────────────────── */
        .features { padding: 88px 0 92px; background: #fff; position: relative; overflow: hidden; }
        .features-grid { display: grid; grid-template-columns: 368px 1fr; gap: 56px; align-items: center; }
        .device-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px; max-width: 745px; margin-left: auto; }
        .device-item figcaption h4 { font-size: .78rem; font-weight: 600; color: var(--navy); margin: 16px 0 6px; }
        .device-item figcaption p { font-size: .7rem; line-height: 1.65; color: var(--body); }

        .device {
            border-radius: 10px; overflow: hidden; position: relative;
            background: linear-gradient(150deg, #F2F4F7 0%, #E3E7EC 100%);
            box-shadow: 0 14px 34px rgba(20,32,48,.1);
            aspect-ratio: 4 / 3; display: grid; place-items: center; padding: 16px;
        }
        .frame {
            width: 100%; height: 100%; background: #2B2F36; border-radius: 9px;
            padding: 5px; box-shadow: 0 10px 22px rgba(20,30,45,.28);
        }
        .frame.laptop { border-radius: 6px; padding: 5px 5px 9px; }
        .frame.laptop::after {
            content: ''; display: block; width: 34%; height: 2px; margin: 3px auto 0;
            background: rgba(255,255,255,.32); border-radius: 2px;
        }
        .frame.phone { width: 46%; height: 100%; margin: 0 auto; border-radius: 14px; padding: 6px 4px; }
        .frame.phone::before {
            content: ''; display: block; width: 26%; height: 3px; margin: 0 auto 4px;
            background: rgba(255,255,255,.3); border-radius: 3px;
        }
        .screen {
            width: 100%; height: 100%; background: #fff; border-radius: 5px;
            padding: 8px; overflow: hidden;
            display: flex; flex-direction: column; gap: 5px;
        }
        .frame.phone .screen { border-radius: 9px; padding: 7px 6px; }
        .sk { border-radius: 3px; background: #EDEFF2; height: 6px; }
        .sk.red { background: var(--red); }
        .sk.pale { background: var(--red-pale); }
        .sk.w40 { width: 40%; } .sk.w60 { width: 60%; } .sk.w75 { width: 75%; } .sk.w25 { width: 25%; }
        .sk.tall { height: 26px; }
        .sk-row { display: flex; gap: 5px; }
        .sk-row > * { flex: 1; }
        .sk-donut {
            width: 30px; height: 30px; border-radius: 50%; margin: 2px auto 4px;
            background: conic-gradient(var(--red) 0 62%, #E9EBEF 62% 100%);
        }
        .sk-bars { display: flex; align-items: flex-end; gap: 4px; height: 26px; }
        .sk-bars i { flex: 1; border-radius: 2px 2px 0 0; background: var(--red-pale); display: block; }
        .sk-bars i:nth-child(2) { background: var(--red); }
        .sk-bars i:nth-child(4) { background: var(--red-bright); }

        /* ─── IMPACT ──────────────────────────────────────────── */
        .impact { padding: 58px 0; }
        .impact::before {
            content: ''; position: absolute; inset: -20% -10%; z-index: 3; pointer-events: none;
            background: repeating-linear-gradient(112deg, rgba(255,255,255,.05) 0 2px, transparent 2px 90px);
        }
        .impact-grid { display: grid; grid-template-columns: 330px 1fr; gap: 56px; align-items: center; }
        .impact h3 { font-size: clamp(1.25rem, 2.1vw, 1.6rem); font-weight: 700; color: #fff; line-height: 1.28; letter-spacing: -.015em; }
        .impact p { font-size: .72rem; line-height: 1.7; color: rgba(255,255,255,.82); margin-top: 12px; max-width: 300px; }
        .stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; max-width: 790px; margin-left: auto; }
        .stat i { font-size: .8rem; color: rgba(255,255,255,.6); display: block; margin-bottom: 12px; }
        .stat b { display: block; font-size: clamp(1.5rem, 2.6vw, 2rem); font-weight: 700; color: #fff; letter-spacing: -.02em; }
        .stat span { display: block; font-size: .68rem; color: rgba(255,255,255,.82); margin-top: 5px; }

        /* ─── STORIES ─────────────────────────────────────────── */
        .stories { padding: 76px 0 84px; background: var(--soft); position: relative; overflow: hidden; }
        .stories-grid { display: grid; grid-template-columns: 330px 1fr; gap: 56px; align-items: center; }

        .carousel { position: relative; }
        .carousel-viewport { overflow: hidden; }
        .carousel-track { display: flex; transition: transform .45s cubic-bezier(.4,0,.2,1); }
        .story {
            flex: 0 0 50%; padding-right: 22px;
        }
        .story-card {
            background: #fff; border: 1px solid var(--line); border-radius: 10px;
            padding: 18px 20px; height: 100%;
            box-shadow: 0 6px 20px rgba(20,32,48,.05);
        }
        .story-head { display: flex; align-items: center; gap: 11px; margin-bottom: 12px; }
        .story-avatar {
            width: 34px; height: 34px; border-radius: 50%; flex: 0 0 auto;
            background: var(--red-pale); color: var(--red);
            display: grid; place-items: center; font-size: .72rem; font-weight: 700;
        }
        .story-head h5 { font-size: .76rem; font-weight: 600; color: var(--navy); }
        .story-head small { font-size: .62rem; color: var(--muted); }
        .story-card q { display: block; font-size: .74rem; line-height: 1.75; color: var(--body); font-style: normal; }
        .stars { margin-top: 12px; color: var(--red); font-size: .6rem; letter-spacing: 2px; }

        .car-btn {
            position: absolute; top: 50%; transform: translateY(-50%); z-index: 5;
            width: 30px; height: 30px; border-radius: 50%; border: 1px solid var(--line);
            background: #fff; color: var(--navy); cursor: pointer;
            display: grid; place-items: center; font-size: .72rem;
            box-shadow: 0 4px 12px rgba(20,30,45,.09); transition: .18s;
        }
        .car-btn:hover { background: var(--red); color: #fff; border-color: var(--red); }
        .car-btn.prev { left: -15px; }
        .car-btn.next { right: 7px; }

        .dots { display: flex; justify-content: center; gap: 6px; margin-top: 20px; }
        .dots button {
            width: 6px; height: 6px; border-radius: 50%; border: none; padding: 0;
            background: #D2D7DE; cursor: pointer; transition: .2s;
        }
        .dots button.on { background: var(--red); width: 18px; border-radius: 4px; }

        /* ─── CTA BAND ────────────────────────────────────────── */
        .cta-band { padding: 42px 0; }
        .cta-inner {
            display: flex; align-items: center;
            justify-content: space-between; gap: 28px; flex-wrap: wrap;
        }
        .cta-inner .wordmark { font-size: 1.55rem; }
        .cta-copy h4 { font-size: 1rem; font-weight: 700; color: #fff; }
        .cta-copy p { font-size: .72rem; color: rgba(255,255,255,.8); margin-top: 3px; }

        /* ─── FOOTER ──────────────────────────────────────────── */
        /* The CTA band is dark now too, so the footer needs a hairline to
           keep the two from reading as one slab. */
        /* The CTA band is dark now too, so the footer needs a hairline to
           keep the two from reading as one slab. */
        footer {
            position: relative; overflow: hidden;
            background: var(--ink); padding: 64px 0 0;
            border-top: 1px solid rgba(255,255,255,.08);
        }
        footer::before {
            content: ''; position: absolute; left: -6%; top: -40%;
            width: 460px; height: 460px; border-radius: 50%; pointer-events: none;
            background: radial-gradient(circle, rgba(161,38,38,.16) 0%, rgba(161,38,38,0) 70%);
        }
        .foot-top {
            position: relative; display: grid;
            grid-template-columns: 1.7fr 1fr 1fr 1.4fr;
            gap: 48px; padding-bottom: 52px;
        }

        .foot-brand .wordmark { font-size: 1.6rem; }
        .foot-brand p {
            font-size: .74rem; line-height: 1.8; color: rgba(255,255,255,.6);
            margin: 16px 0 22px; max-width: 300px;
        }
        .foot-social { display: flex; gap: 8px; }
        .foot-social a {
            width: 30px; height: 30px; border-radius: 7px; background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.09);
            color: rgba(255,255,255,.7); display: grid; place-items: center;
            font-size: .72rem; transition: background .18s, color .18s, transform .18s;
        }
        .foot-social a:hover { background: var(--red); color: #fff; border-color: var(--red); transform: translateY(-2px); }

        .foot-col h5 {
            font-size: .74rem; font-weight: 600; color: #fff;
            letter-spacing: .01em; margin-bottom: 6px;
        }
        .foot-col h5::after {
            content: ''; display: block; width: 24px; height: 2px;
            background: var(--red-bright); margin: 9px 0 16px;
        }
        .foot-col li { margin-bottom: 11px; }
        .foot-col a {
            font-size: .74rem; color: rgba(255,255,255,.6);
            transition: color .18s, padding-left .18s;
        }
        .foot-col a:hover { color: #fff; padding-left: 4px; }

        .foot-uni { display: flex; gap: 12px; margin-bottom: 16px; }
        .foot-uni img {
            width: 38px; height: 38px; flex: 0 0 auto; object-fit: contain;
            background: #fff; border-radius: 50%; padding: 4px;
        }
        .foot-uni strong { display: block; font-size: .74rem; font-weight: 600; color: #fff; }
        .foot-uni span { display: block; font-size: .68rem; line-height: 1.55; color: rgba(255,255,255,.55); margin-top: 3px; }
        .foot-meta { font-size: .7rem; line-height: 1.75; color: rgba(255,255,255,.55); }
        .foot-meta a { color: rgba(255,255,255,.72); transition: color .18s; }
        .foot-meta a:hover { color: #fff; }
        .foot-meta i { width: 15px; color: var(--red-bright); font-size: .7rem; }

        .foot-bottom {
            position: relative;
            display: flex; align-items: center; justify-content: space-between;
            gap: 18px; flex-wrap: wrap;
            padding: 20px 0 22px;
            border-top: 1px solid rgba(255,255,255,.07);
            font-size: .68rem; color: rgba(255,255,255,.45);
        }

        /* Grid and flex children default to min-width:auto, which lets a wide
           child (the carousel track) push its column past the viewport. */
        .why-grid > *, .how-grid > *, .features-grid > *,
        .impact-grid > *, .stories-grid > *,
        .carousel, .carousel-viewport, .feat > div, .step > div { min-width: 0; }

        /* ─── DECOR ───────────────────────────────────────────── */
        .deco { position: absolute; z-index: 0; pointer-events: none; }

        .deco-dots {
            color: rgba(123,29,29,.24);
            background-image: radial-gradient(currentColor 1.3px, transparent 1.4px);
            background-size: 17px 17px;
            -webkit-mask-image: radial-gradient(ellipse at center, #000 26%, transparent 72%);
                    mask-image: radial-gradient(ellipse at center, #000 26%, transparent 72%);
        }
        .deco-ring   { border: 1.5px solid  rgba(123,29,29,.13); border-radius: 50%; }
        .deco-ring-d { border: 1.5px dashed rgba(123,29,29,.16); border-radius: 50%; }
        .deco-tile   { border: 1.5px solid  rgba(123,29,29,.13); border-radius: 14px; }
        .deco-glow   { border-radius: 50%; background: radial-gradient(circle, rgba(123,29,29,.075) 0%, rgba(123,29,29,0) 70%); }
        .deco-slash  { border-radius: 6px; transform: skewX(-19deg); background: linear-gradient(180deg, rgba(123,29,29,.14), rgba(123,29,29,0)); }
        .deco-quote {
            font-size: 210px; line-height: .8; font-weight: 800;
            color: rgba(123,29,29,.055); user-select: none;
        }
        .deco-spin { animation: decoSpin 46s linear infinite; }
        @keyframes decoSpin { to { transform: rotate(360deg); } }

        /* The copy is centred, so the free space is the strip above the
           eyebrow and the band under the mini-features. */
        .hero     .d1 { left: 2%;     bottom: 3%;  width: 210px; height: 104px; }
        .hero     .d2 { left: 3.5%;   top: 14%;    width: 70px;  height: 70px; transform: rotate(22deg); }
        .hero     .d3 { left: -110px; bottom: -80px; width: 300px; height: 300px; }
        .hero     .d4 { left: 27%;    bottom: 4%;  width: 84px;  height: 84px; }

        .why      .d1 { right: 1.5%; bottom: 4%;    width: 196px; height: 118px; }
        .why      .d2 { right: 6%;   top: 8%;       width: 122px; height: 122px; }
        .why      .d3 { left: -110px; bottom: -90px; width: 300px; height: 300px; }
        .why      .d4 { left: 23%;   bottom: 9%;    width: 58px;  height: 58px; transform: rotate(-16deg); }

        .how      .d1 { left: 0.5%;  bottom: 1%;    width: 200px; height: 116px; }
        .how      .d2 { right: 3%;   top: 7%;       width: 104px; height: 104px; }
        .how      .d3 { right: -70px; bottom: -90px; width: 320px; height: 320px; }

        .features .d1 { left: 1.5%;  bottom: 6%;    width: 204px; height: 118px; }
        .features .d2 { left: 3%;    top: 9%;       width: 62px;  height: 62px; transform: rotate(-18deg); }
        .features .d3 { right: -90px; top: -80px;   width: 280px; height: 280px; }

        .stories  .d1 { left: 1.5%;  bottom: 5%;    width: 192px; height: 116px; }
        .stories  .d2 { right: 2.5%; top: 7%;       width: 92px;  height: 92px; }
        .stories  .d3 { left: 1%;    top: 24%; }

        @media (max-width: 1080px) { .deco { display: none; } }
        /* The hero centres its copy, so on short screens the free bands above
           and below it disappear and the ornaments would sit on the text. */
        @media (max-height: 820px) { .hero .d1, .hero .d2, .hero .d4 { display: none; } }

        /* ─── REVEAL ──────────────────────────────────────────── */
        .reveal { opacity: 0; transform: translateY(18px); transition: opacity .6s ease, transform .6s ease; }
        .reveal.visible { opacity: 1; transform: none; }
        @media (prefers-reduced-motion: reduce) {
            .reveal { opacity: 1; transform: none; transition: none; }
            html { scroll-behavior: auto; }
        }

        /* ─── RESPONSIVE ──────────────────────────────────────── */
        @media (max-width: 1080px) {
            .why-grid, .how-grid, .features-grid, .impact-grid, .stories-grid { grid-template-columns: 1fr; gap: 42px; }
            .why-right { border-left: none; padding-left: 0; }
            .hero-photo, .hero-photo-fg { left: 4%; width: 92%; }
            .hero-photo { opacity: .55; }
            .hero-photo-fg { opacity: .7; }
        }
        @media (max-width: 880px) {
            .nav-links { display: none; }
            .burger { display: flex; }
            .hero { padding: 122px 0 64px; }
            .hero-photo, .hero-photo-fg { left: 0; width: 100%; }
            .hero-photo { opacity: .3; }
            .hero-photo-fg { opacity: .42; }
            .hero-slashes .hs-1 { top: -8%; right: -16%; width: 42%; height: 26%; }
            .hero-slashes .hs-2 { top: -8%; right: 26%;  width: 7%;  height: 18%; }
            .hero-slashes .hs-4 { bottom: -8%; right: -16%; width: 36%; height: 15%; }
            .hero-slashes .hs-3, .hero-slashes .hs-5 { display: none; }
            .hero-mini .mini { border-right: none; padding: 0 22px 0 0; }
            .stat-row { grid-template-columns: repeat(2, 1fr); gap: 26px; }
            .device-row { grid-template-columns: 1fr; }
            .story { flex: 0 0 100%; padding-right: 0; }
            .car-btn.prev { left: 6px; } .car-btn.next { right: 6px; }
            .cta-inner { justify-content: center; text-align: center; }
            .foot-top { grid-template-columns: 1fr 1fr; gap: 38px; }
        }
        @media (max-width: 560px) {
            .container { padding: 0 20px; }
            .nav-brand img { height: 34px; }
            .nav-actions .btn { padding: .62rem 1.15rem; font-size: .72rem; }
            .feat-grid { grid-template-columns: 1fr; gap: 28px; }
            .stat-row { grid-template-columns: 1fr 1fr; }
            .hero-mini { gap: 18px; }
            .mock { grid-template-columns: 1fr; }
            .mock-side { display: none; }
            .mock-cards, .reco-row { grid-template-columns: 1fr; }
            footer { padding-top: 48px; }
            .foot-top { grid-template-columns: 1fr; gap: 32px; padding-bottom: 38px; }
            .foot-bottom { justify-content: center; text-align: center; }
        }
    </style>
</head>
<body>

<!-- ══ NAVBAR ══ -->
<header class="nav" id="navbar">
    <div class="container nav-inner">
        <a href="#home" class="nav-brand">
            <img src="{{ asset('images/logo no.2.png') }}" alt="CPAce">
        </a>

        <nav class="nav-links" id="navLinks">
            <a href="#home" class="active">Home</a>
            <a href="#features">Features</a>
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
        </nav>

        <div class="nav-actions">
            <a href="{{ route('login') }}" class="btn btn-red btn-pill">Get Started <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="burger" onclick="toggleMenu()" aria-label="Menu"><span></span><span></span><span></span></div>
    </div>
</header>

<div class="mobile-menu" id="mobileMenu">
    <a href="#home" onclick="toggleMenu()">Home</a>
    <a href="#features" onclick="toggleMenu()">Features</a>
    <a href="#about" onclick="toggleMenu()">About</a>
    <a href="#contact" onclick="toggleMenu()">Contact</a>
    <a href="{{ route('login') }}">Get Started &rarr;</a>
</div>

<!-- ══ HERO ══ -->
<section class="hero" id="home">
    <div class="hero-photo" aria-hidden="true"></div>
    <div class="hero-photo-fg" aria-hidden="true"></div>

    <div class="deco deco-glow   d3" aria-hidden="true"></div>
    <div class="deco deco-ring-d d4 deco-spin" aria-hidden="true"></div>
    <div class="deco deco-tile   d2" aria-hidden="true"></div>
    <div class="deco deco-dots   d1" aria-hidden="true"></div>
    <div class="hero-slashes" aria-hidden="true">
        <i class="hs-1"></i><i class="hs-2"></i><i class="hs-3"></i><i class="hs-4"></i><i class="hs-5"></i>
    </div>

    <div class="container hero-inner">
        <div class="hero-copy">
            <div class="eyebrow reveal">CPALE Review System</div>
            <h1 class="hero-brand reveal"><img src="{{ asset('images/wordmark-transparent.png') }}" alt="CPAce"></h1>
            <h2 class="hero-head reveal">Adaptive Review.<br>Smarter Preparation.</h2>
            <p class="hero-sub reveal">
                An adaptive board exam review system designed to help aspiring Certified
                Public Accountants identify weaknesses, practice strategically, and track
                their progress.
            </p>
            <div class="hero-cta reveal">
                <a href="{{ route('login') }}" class="btn btn-red">Get Started <i class="fas fa-arrow-right"></i></a>
                <a href="#features" class="btn btn-outline">Explore Features</a>
            </div>

            <div class="hero-mini reveal">
                <div class="mini">
                    <div class="mini-ico"><i class="fas fa-bullseye"></i></div>
                    <div class="mini-txt">Personalized<br>Learning Path</div>
                </div>
                <div class="mini">
                    <div class="mini-ico"><i class="fas fa-chart-simple"></i></div>
                    <div class="mini-txt">Track Your<br>Progress</div>
                </div>
                <div class="mini">
                    <div class="mini-ico"><i class="fas fa-brain"></i></div>
                    <div class="mini-txt">Focus on<br>Your Weak Areas</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ WHY CPAce ══ -->
<section class="why band" id="about">
    <div class="band-bg" aria-hidden="true"></div>
    <div class="band-scrim" aria-hidden="true"></div>
    <div class="band-dots" aria-hidden="true"></div>

    <div class="deco deco-glow d3" aria-hidden="true"></div>
    <div class="deco deco-ring d2" aria-hidden="true"></div>
    <div class="deco deco-tile d4" aria-hidden="true"></div>
    <div class="deco deco-dots d1" aria-hidden="true"></div>
    <div class="container why-grid">
        <div class="why-left reveal">
            <div class="eyebrow"><span>Why <span class="nocaps">CPAce</span>?</span></div>
            <h2 class="sec-title">Built for Your<br>CPA Journey</h2>
            <p class="sec-text">
                CPAce combines smart technology with proven review strategies to give you a
                personalized, effective, and flexible study experience.
            </p>
            <a href="#how-it-works" class="btn btn-white" style="margin-top:26px">Learn More <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="why-right reveal">
            <div class="feat-grid">
                <div class="feat">
                    <div class="feat-ico"><i class="fas fa-brain"></i></div>
                    <div>
                        <h4>Adaptive Learning</h4>
                        <p>Adjusts to your performance and focuses on what you need most.</p>
                    </div>
                </div>
                <div class="feat">
                    <div class="feat-ico"><i class="fas fa-chart-simple"></i></div>
                    <div>
                        <h4>Progress Tracking</h4>
                        <p>Monitor your growth with detailed analytics and insights.</p>
                    </div>
                </div>
                <div class="feat">
                    <div class="feat-ico"><i class="fas fa-file-lines"></i></div>
                    <div>
                        <h4>Mock Exams</h4>
                        <p>Simulate the real CPALE experience and build confidence.</p>
                    </div>
                </div>
                <div class="feat">
                    <div class="feat-ico"><i class="fas fa-calendar-days"></i></div>
                    <div>
                        <h4>Spaced Repetition</h4>
                        <p>Reinforce your learning with scientifically proven scheduling.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ HOW IT WORKS ══ -->
<section class="how" id="how-it-works">
    <div class="deco deco-glow   d3" aria-hidden="true"></div>
    <div class="deco deco-ring-d d2 deco-spin" aria-hidden="true"></div>
    <div class="deco deco-dots   d1" aria-hidden="true"></div>
    <div class="container how-grid">
        <div class="how-left reveal">
            <div class="eyebrow">How It Works</div>
            <h2 class="sec-title">From Learning<br>to Passing</h2>
            <p class="sec-text">
                Follow a simple 4-step process and let CPAce guide you toward your CPA goals.
            </p>

            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <div>
                        <h4>Take a Diagnostic Test</h4>
                        <p>Identify your strengths and weak areas.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div>
                        <h4>Get Your Personalized Plan</h4>
                        <p>Focus on what matters most.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <div>
                        <h4>Practice and Improve</h4>
                        <p>Use adaptive quizzes and mock exams.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">4</div>
                    <div>
                        <h4>Track Your Progress</h4>
                        <p>See your growth, stay motivated.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mock-wrap reveal">
            <div class="mock-behind" aria-hidden="true"></div>
            <div class="mock" role="img" aria-label="Preview of the CPAce student dashboard">
                <aside class="mock-side">
                    <div class="wordmark on-dark"><span class="w-a">CPA</span><span class="w-b">ce</span></div>
                    <ul>
                        <li class="on"><i class="fas fa-gauge"></i> Dashboard</li>
                        <li><i class="fas fa-book"></i> Subjects</li>
                        <li><i class="fas fa-list-check"></i> Quizzes</li>
                        <li><i class="fas fa-file-lines"></i> Mock Exams</li>
                        <li><i class="fas fa-chart-simple"></i> Analytics</li>
                        <li><i class="fas fa-gear"></i> Settings</li>
                    </ul>
                </aside>

                <div class="mock-main">
                    <div class="mock-top">
                        <div class="mock-user">
                            <span class="mock-avatar"><i class="fas fa-user"></i></span> Student
                            <i class="fas fa-chevron-down" style="font-size:6px"></i>
                        </div>
                    </div>

                    <div class="mock-hello">Good morning, Future CPA! 👋</div>
                    <span>Keep going! You're doing great.</span>

                    <div class="mock-cards">
                        <div class="mock-card">
                            <h6>Overall Progress <span>7d</span></h6>
                            <div class="donut-row">
                                <div class="donut"></div>
                                <div class="donut-bars">
                                    <span>Recent 7 quizzes</span>
                                    <div class="bar"><i style="width:74%"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="mock-card">
                            <h6>Weak Areas <span>%</span></h6>
                            <div class="weak"><b>FAR</b><div class="bar"><i style="width:62%"></i></div><em>62%</em></div>
                            <div class="weak"><b>AUD</b><div class="bar"><i style="width:74%"></i></div><em>74%</em></div>
                            <div class="weak"><b>TAX</b><div class="bar"><i style="width:58%"></i></div><em>58%</em></div>
                            <div class="weak"><b>MS</b><div class="bar"><i style="width:81%"></i></div><em>81%</em></div>
                        </div>
                    </div>

                    <div class="mock-reco">Recommended for You</div>
                    <div class="reco-row">
                        <div class="reco">
                            <div class="r-ico"><i class="fas fa-list-check"></i></div>
                            <h6>Practice Quiz</h6>
                            <small>20 questions · Adaptive</small>
                            <b>Start</b>
                        </div>
                        <div class="reco">
                            <div class="r-ico"><i class="fas fa-book-open"></i></div>
                            <h6>Topic Review</h6>
                            <small>Key concepts · 15 min</small>
                            <b>Start</b>
                        </div>
                        <div class="reco">
                            <div class="r-ico"><i class="fas fa-file-lines"></i></div>
                            <h6>Mock Exam</h6>
                            <small>100 questions · Timed</small>
                            <b>Start</b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ FEATURES ══ -->
<section class="features" id="features">
    <div class="deco deco-glow d3" aria-hidden="true"></div>
    <div class="deco deco-tile d2" aria-hidden="true"></div>
    <div class="deco deco-dots d1" aria-hidden="true"></div>
    <div class="container features-grid">
        <div class="reveal">
            <div class="eyebrow">Features</div>
            <h2 class="sec-title">Everything You Need<br>in One Place</h2>
            <p class="sec-text">CPAce gives you the tools to study smarter, not harder.</p>
        </div>

        <div class="device-row reveal">
            <figure class="device-item">
                <div class="device">
                    <div class="frame">
                        <div class="screen">
                            <div class="sk red w40"></div>
                            <div class="sk w75"></div>
                            <div class="sk-row"><div class="sk pale tall"></div><div class="sk tall"></div></div>
                            <div class="sk w60"></div>
                            <div class="sk-row"><div class="sk"></div><div class="sk red w25"></div></div>
                        </div>
                    </div>
                </div>
                <figcaption>
                    <h4>Adaptive Quiz Engine</h4>
                    <p>Questions adjust to your strengths and weaknesses.</p>
                </figcaption>
            </figure>

            <figure class="device-item">
                <div class="device">
                    <div class="frame laptop">
                        <div class="screen">
                            <div class="sk red w40"></div>
                            <div class="sk-donut"></div>
                            <div class="sk-bars"><i style="height:45%"></i><i style="height:80%"></i><i style="height:60%"></i><i style="height:100%"></i><i style="height:55%"></i></div>
                            <div class="sk w60"></div>
                        </div>
                    </div>
                </div>
                <figcaption>
                    <h4>Performance Dashboard</h4>
                    <p>Visualize your progress and identify areas to improve.</p>
                </figcaption>
            </figure>

            <figure class="device-item">
                <div class="device phone">
                    <div class="frame phone">
                        <div class="screen">
                            <div class="sk red w60"></div>
                            <div class="sk"></div>
                            <div class="sk pale tall"></div>
                            <div class="sk w75"></div>
                            <div class="sk w40"></div>
                            <div class="sk red"></div>
                        </div>
                    </div>
                </div>
                <figcaption>
                    <h4>Mobile Friendly</h4>
                    <p>Study anytime, anywhere, on any device.</p>
                </figcaption>
            </figure>
        </div>
    </div>
</section>

<!-- ══ IMPACT ══ -->
<section class="impact band">
    <div class="band-bg" aria-hidden="true"></div>
    <div class="band-scrim" aria-hidden="true"></div>
    <div class="band-dots" aria-hidden="true"></div>

    <div class="container impact-grid">
        <div class="reveal">
            <div class="eyebrow on-red">The Impact</div>
            <h3>More Practice.<br>Higher Chances.</h3>
            <p>Join thousands of aspiring CPAs and take your review to the next level with CPAce.</p>
        </div>

        <div class="stat-row reveal">
            <div class="stat"><i class="fas fa-user-group"></i><b>10K+</b><span>Active Students</span></div>
            <div class="stat"><i class="fas fa-face-smile"></i><b>95%</b><span>Satisfaction Rate</span></div>
            <div class="stat"><i class="fas fa-circle-question"></i><b>3.2K+</b><span>Practice Questions</span></div>
            <div class="stat"><i class="fas fa-arrow-trend-up"></i><b>85%</b><span>Average Score Increase</span></div>
        </div>
    </div>
</section>

<!-- ══ STUDENT STORIES ══ -->
<section class="stories">
    <div class="deco deco-quote d3" aria-hidden="true">&rdquo;</div>
    <div class="deco deco-ring  d2" aria-hidden="true"></div>
    <div class="deco deco-dots  d1" aria-hidden="true"></div>
    <div class="container stories-grid">
        <div class="reveal">
            <div class="eyebrow">Student Stories</div>
            <h2 class="sec-title">Real People. Real Progress.</h2>
            <p class="sec-text">Hear from future CPAs who are on their way to success with CPAce.</p>
        </div>

        <div class="carousel reveal" id="carousel">
            <button class="car-btn prev" onclick="slide(-1)" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
            <div class="carousel-viewport">
                <div class="carousel-track" id="track">
                    <div class="story">
                        <div class="story-card">
                            <div class="story-head">
                                <div class="story-avatar">MS</div>
                                <div><h5>Maria Santos</h5><small>CPALE Taker</small></div>
                            </div>
                            <q>CPAce helped me focus on my weak areas. The adaptive quizzes are a game changer!</q>
                            <div class="stars">★★★★★</div>
                        </div>
                    </div>
                    <div class="story">
                        <div class="story-card">
                            <div class="story-head">
                                <div class="story-avatar">JR</div>
                                <div><h5>John Reyes</h5><small>CPALE Taker</small></div>
                            </div>
                            <q>The progress tracking feature kept me motivated. I know exactly where I stand and what to improve.</q>
                            <div class="stars">★★★★★</div>
                        </div>
                    </div>
                    <div class="story">
                        <div class="story-card">
                            <div class="story-head">
                                <div class="story-avatar">AD</div>
                                <div><h5>Angela Dizon</h5><small>BS Accountancy Graduate</small></div>
                            </div>
                            <q>The mock exams felt exactly like the real thing. Walking into the CPALE, I already knew the pacing.</q>
                            <div class="stars">★★★★★</div>
                        </div>
                    </div>
                    <div class="story">
                        <div class="story-card">
                            <div class="story-head">
                                <div class="story-avatar">PM</div>
                                <div><h5>Paulo Mendoza</h5><small>CPALE Taker</small></div>
                            </div>
                            <q>Spaced repetition kept older topics fresh. I stopped forgetting what I reviewed weeks ago.</q>
                            <div class="stars">★★★★★</div>
                        </div>
                    </div>
                </div>
            </div>
            <button class="car-btn next" onclick="slide(1)" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
            <div class="dots" id="dots"></div>
        </div>
    </div>
</section>

<!-- ══ CTA BAND ══ -->
<section class="cta-band band">
    <div class="band-bg" aria-hidden="true"></div>
    <div class="band-scrim" aria-hidden="true"></div>
    <div class="band-dots" aria-hidden="true"></div>

    <div class="container cta-inner">
        <div class="wordmark on-dark"><span class="w-a">CPA</span><span class="w-b">ce</span></div>
        <div class="cta-copy">
            <h4>Your CPA journey starts here.</h4>
            <p>Smarter review. Greater possibilities.</p>
        </div>
        <a href="{{ route('login') }}" class="btn btn-white btn-pill">Get Started <i class="fas fa-arrow-right"></i></a>
    </div>
</section>

<!-- ══ FOOTER ══ -->
<footer id="contact">
    <div class="container foot-top">

        <div class="foot-brand">
            <div class="wordmark on-dark"><span class="w-a">CPA</span><span class="w-b">ce</span></div>
            <p>
                An adaptive CPALE review system built for Bachelor of Science in Accountancy
                students — personalized practice, performance analytics, and intelligent
                review scheduling in one place.
            </p>
            <div class="foot-social">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="X"><i class="fab fa-x-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            </div>
        </div>

        <div class="foot-col">
            <h5>Platform</h5>
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About CPAce</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li><a href="#features">Features</a></li>
            </ul>
        </div>

        <div class="foot-col">
            <h5>Account</h5>
            <ul>
                <li><a href="{{ route('login') }}">Log in</a></li>
                <li><a href="{{ route('forgot-password') }}">Forgot password</a></li>
                <li><a href="mailto:accountancy@bsu.edu.ph?subject=CPAce%20—%20Report%20an%20issue">Report an issue</a></li>
            </ul>
        </div>

        <div class="foot-col">
            <h5>Institution</h5>
            <div class="foot-uni">
                <img src="{{ asset('images/logo-icon.png') }}" alt="">
                <div>
                    <strong>Batangas State University</strong>
                    <span>ARASOF-Nasugbu Campus<br>Nasugbu, Batangas, Philippines</span>
                </div>
            </div>
            <p class="foot-meta">
                <i class="fas fa-building-columns"></i> Department of Accountancy<br>
                <i class="fas fa-envelope"></i> <a href="mailto:accountancy@bsu.edu.ph">accountancy@bsu.edu.ph</a>
            </p>
        </div>
    </div>

    <div class="container foot-bottom">
        <span>&copy; {{ date('Y') }} CPAce. All rights reserved.</span>
        <span>Version 1.0.0 &middot; Capstone Project &middot; BSU ARASOF-Nasugbu</span>
    </div>
</footer>

<script>
    // Navbar shadow on scroll
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 16);
    });

    // Mobile menu
    function toggleMenu() {
        document.getElementById('mobileMenu').classList.toggle('open');
    }

    // Active nav link on scroll
    const navLinks = [...document.querySelectorAll('#navLinks a')];
    const sections = navLinks
        .map(a => document.querySelector(a.getAttribute('href')))
        .filter(Boolean);

    window.addEventListener('scroll', () => {
        const y = window.scrollY + 140;
        let current = sections[0];
        sections.forEach(s => { if (s.offsetTop <= y) current = s; });
        navLinks.forEach(a => a.classList.toggle('active', a.getAttribute('href') === '#' + current.id));
    });

    // Testimonial carousel
    const track   = document.getElementById('track');
    const stories = track.children.length;
    const dotsBox = document.getElementById('dots');
    let index = 0;

    const perView = () => (window.innerWidth <= 880 ? 1 : 2);
    const maxIndex = () => Math.max(0, stories - perView());

    function buildDots() {
        dotsBox.innerHTML = '';
        for (let i = 0; i <= maxIndex(); i++) {
            const b = document.createElement('button');
            b.setAttribute('aria-label', 'Go to slide ' + (i + 1));
            b.onclick = () => { index = i; render(); };
            dotsBox.appendChild(b);
        }
    }

    function render() {
        index = Math.min(Math.max(index, 0), maxIndex());
        track.style.transform = 'translateX(-' + (index * (100 / perView())) + '%)';
        [...dotsBox.children].forEach((d, i) => d.classList.toggle('on', i === index));
    }

    function slide(dir) {
        index = index + dir;
        if (index > maxIndex()) index = 0;
        if (index < 0) index = maxIndex();
        render();
    }

    window.addEventListener('resize', () => { buildDots(); render(); });
    buildDots(); render();

    // Scroll reveal
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), i * 70);
                io.unobserve(entry.target);
            }
        });
    }, { threshold: .08, rootMargin: '0px 0px -30px 0px' });
    document.querySelectorAll('.reveal').forEach(el => io.observe(el));
</script>

    @include('partials.alerts')
</body>
</html>
