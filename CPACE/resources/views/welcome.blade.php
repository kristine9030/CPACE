<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#7B1D1D">
    <title>CPAce — Adaptive Review. Smarter Preparation.</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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

        /* scroll-padding-top parks every #anchor target just below the fixed
           navbar instead of underneath it, so a nav click locks the section
           flush to the bar. 74px is the bar's scrolled height, which is what
           it always is by the time the scroll lands. One declaration covers
           every anchor on the page - nav, hero buttons and footer links. */
        html { scroll-behavior: smooth; overflow-x: hidden; scroll-padding-top: 74px; -webkit-text-size-adjust: 100%; text-size-adjust: 100%; -webkit-tap-highlight-color: transparent; }
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
        /* The bar alone runs full-bleed so Get Started sits hard right against
           the viewport edge instead of stopping at the 1320px column. The left
           padding re-creates that column's inner edge, so the wordmark stays
           aligned with the copy below it. Measured in % (of the fixed header,
           which excludes the scrollbar) rather than vw, or the scrollbar would
           throw the wordmark ~8px out of line with the hero. */
        .nav-inner {
            display: flex; align-items: center; width: 100%;
            max-width: none;
            padding-left: max(32px, calc((100% - var(--container)) / 2 + 32px));
            padding-right: 32px;
        }
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

        .burger { display: none; flex-direction: column; justify-content: center; gap: 5px; cursor: pointer; padding: 12px 10px; margin-right: -10px; }
        .burger span { width: 22px; height: 2px; background: var(--navy); border-radius: 3px; transition: .25s; }
        .burger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .burger.open span:nth-child(2) { opacity: 0; }
        .burger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

        .mobile-menu {
            position: fixed; top: 86px; left: 0; right: 0; z-index: 199;
            background: #fff; border-bottom: 1px solid var(--line);
            padding: 14px 28px 20px; display: none; flex-direction: column;
            box-shadow: 0 14px 30px rgba(20,30,45,.1);
            transition: top .25s ease;
            max-height: calc(100vh - 86px); max-height: calc(100svh - 86px); overflow-y: auto;
        }
        /* The bar shrinks to 74px once scrolled; the panel hangs off it and
           has to follow, or a white gap opens between the two. */
        header.nav.scrolled ~ .mobile-menu { top: 74px; }
        .mobile-menu.open { display: flex; }
        .mobile-menu a {
            font-size: .85rem; font-weight: 500; color: var(--navy);
            padding: .75rem 0; border-bottom: 1px solid var(--line);
        }
        .mobile-menu a:last-child { border: none; color: var(--red); font-weight: 600; }
        .mobile-menu a.active { color: var(--red); font-weight: 600; }

        /* ─── HERO ────────────────────────────────────────────── */
        /* Full-screen, but capped in px at the 1080px design height so the
           section scales as ONE unit with the px-sized copy under browser
           zoom. Left uncapped, zooming out grows the hero with the CSS
           viewport while the copy and the photo stay put, opening a void
           above the building. 1080px is a floor, not a ceiling: taller
           content still grows the section normally. */
        .hero {
            position: relative; background: #fff; overflow: hidden;
            min-height: 100vh; min-height: min(100svh, 1080px);
            display: flex; align-items: center;
            padding: 118px 0 72px;
        }

        /* The source image is already desaturated and already fades to white
           on its left side, so it is laid in full-bleed with no filter or
           gradient of our own on top of it. */
        /* The photo is pinned to the same centred --container column the copy
           sits in, in the same px units, so browser zoom scales it with the
           text instead of against it: sized off the viewport alone it kept
           growing past the 1320px column as the CSS viewport grew on zoom-out.
           1613px is the width it resolves to at the 1920px design viewport, so
           the desktop composition is unchanged and the cap only bites on a
           monitor (or a zoom level) wider than that.
           The svh term keeps it inside the hero on a short wide screen; the
           floors in the media queries keep it a real backdrop on a phone.
           Both layers must keep identical geometry or the cut-out stops
           registering with the plate beneath it. */
        .hero-photo, .hero-photo-fg {
            position: absolute; z-index: 0;
            bottom: 0;
            left: calc(50% + 25px);
            width: 84%;
            width: min(84%, 1613px, calc(96svh * 16 / 9));
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
            transform: translateX(-50%);
        }
        /* Cut-out of the same frame (transparent sky) laid over the plate so
           the building reads clearly instead of washing out. */
        .hero-photo-fg {
            background-image: url("{{ asset('images/overaly.png') }}");
            /* The -50% is the centring; the 1.042% / -2.222% is the cut-out's
               own registration offset against the plate and must survive it. */
            transform: translate(calc(1.042% - 50%), -2.222%);
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
        .why-grid { display: grid; grid-template-columns: 360px minmax(0, 1fr); gap: 62px; align-items: start; }
        .why-right { border-left: 1px solid var(--line); padding-left: 62px; }
        .feat-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 42px 58px; max-width: 690px; }
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
        .how-grid { display: grid; grid-template-columns: 400px minmax(0, 1fr); gap: 60px; align-items: center; }

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
            position: relative; z-index: 1; display: grid; grid-template-columns: 128px minmax(0, 1fr);
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

        .mock-cards { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 10px; margin-top: 11px; }
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
        .reco-row { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
        .reco {
            background: #fff; border: 1px solid #EFF1F4; border-radius: 8px; padding: 9px;
        }
        .reco .r-ico { width: 17px; height: 17px; border-radius: 5px; background: var(--red-pale); color: var(--red); display: grid; place-items: center; font-size: 7px; margin-bottom: 7px; }
        .reco h6 { font-size: 7.5px; font-weight: 600; color: var(--navy); }
        .reco small { font-size: 6.5px; color: var(--muted); display: block; margin: 2px 0 8px; }
        .reco b { display: block; background: var(--red); color: #fff; font-size: 6.5px; font-weight: 600; text-align: center; padding: 4px 0; border-radius: 4px; }

        /* ─── FEATURES ────────────────────────────────────────── */
        .features { padding: 88px 0 92px; background: #fff; position: relative; overflow: hidden; }
        .features-grid { display: grid; grid-template-columns: 368px minmax(0, 1fr); gap: 56px; align-items: center; }
        .device-row { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 26px; max-width: 745px; margin-left: auto; }
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
        .impact-grid { display: grid; grid-template-columns: 330px minmax(0, 1fr); gap: 56px; align-items: center; }
        .impact h3 { font-size: clamp(1.25rem, 2.1vw, 1.6rem); font-weight: 700; color: #fff; line-height: 1.28; letter-spacing: -.015em; }
        .impact p { font-size: .72rem; line-height: 1.7; color: rgba(255,255,255,.82); margin-top: 12px; max-width: 300px; }
        .stat-row { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 22px; max-width: 790px; margin-left: auto; }
        .stat i { font-size: .8rem; color: rgba(255,255,255,.6); display: block; margin-bottom: 12px; }
        .stat b { display: block; font-size: clamp(1.5rem, 2.6vw, 2rem); font-weight: 700; color: #fff; letter-spacing: -.02em; }
        .stat span { display: block; font-size: .68rem; color: rgba(255,255,255,.82); margin-top: 5px; }

        /* ─── STORIES ─────────────────────────────────────────── */
        .stories { padding: 76px 0 84px; background: var(--soft); position: relative; overflow: hidden; }
        .stories-grid { display: grid; grid-template-columns: 330px minmax(0, 1fr); gap: 56px; align-items: center; }

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
            grid-template-columns: 1.6fr minmax(0, 1fr) minmax(0, 1fr) 1.7fr;
            gap: 48px; padding-bottom: 52px;
        }

        .foot-brand .wordmark { font-size: 1.6rem; }
        .foot-brand p {
            font-size: .74rem; line-height: 1.8; color: rgba(255,255,255,.6);
            margin: 16px 0 20px; max-width: 300px;
        }
        /* Support details live under the brand now that the social row is gone,
           so a visitor has somewhere to write to without hunting the columns. */
        .foot-contact { font-size: .72rem; line-height: 1.9; color: rgba(255,255,255,.55); }
        .foot-contact a { color: rgba(255,255,255,.75); transition: color .18s; }
        .foot-contact a:hover { color: #fff; }
        .foot-contact i { width: 16px; color: var(--red-bright); font-size: .72rem; }

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

        /* The university is the system's owner and accreditor, so it is given a
           card of its own rather than reading as one more list item. */
        .foot-uni {
            display: flex; gap: 14px; align-items: center;
            padding: 15px 16px; margin-bottom: 15px; border-radius: 12px;
            background: rgba(255,255,255,.045);
            border: 1px solid rgba(255,255,255,.10);
        }
        .foot-uni img {
            width: 56px; height: 56px; flex: 0 0 auto; object-fit: contain;
        }
        .foot-uni strong {
            display: block; font-size: .82rem; font-weight: 700;
            color: #fff; line-height: 1.3; letter-spacing: -.01em;
        }
        .foot-uni .uni-tag {
            display: block; margin-top: 5px;
            font-size: .58rem; font-weight: 700; letter-spacing: .1em;
            text-transform: uppercase; color: var(--red-bright);
        }
        .foot-uni .uni-campus {
            display: block; font-size: .7rem; font-weight: 600;
            color: rgba(255,255,255,.78); margin-top: 6px;
        }
        .foot-meta { font-size: .7rem; line-height: 1.75; color: rgba(255,255,255,.55); }
        .foot-meta a { color: rgba(255,255,255,.72); transition: color .18s; }
        .foot-meta a:hover { color: #fff; }
        .foot-meta i { width: 15px; color: var(--red-bright); font-size: .7rem; }

        /* Two disclosures a deployed build genuinely needs: what is done with
           the proctoring captures, and that a CPALE review tool is not the PRC.
           Given a heading and the columns' own rule so it reads as a section of
           the footer rather than fine print dropped at the bottom. */
        /* Folded away by default: a <details> so the keyboard, screen readers
           and find-in-page all work with no JS of our own. */
        .foot-disclose {
            position: relative;
            border-top: 1px solid rgba(255,255,255,.07);
        }
        .foot-disclose > summary {
            list-style: none; cursor: pointer;
            display: flex; align-items: center; justify-content: space-between; gap: 14px;
            padding: 18px 0;
            font-size: .72rem; font-weight: 600; color: rgba(255,255,255,.68);
            transition: color .18s;
        }
        .foot-disclose > summary::-webkit-details-marker { display: none; }
        .foot-disclose > summary:hover,
        .foot-disclose[open] > summary { color: #fff; }
        .foot-disclose summary .lead { color: var(--red-bright); margin-right: 9px; }
        .foot-disclose .chev {
            font-size: .64rem; color: rgba(255,255,255,.42);
            transition: transform .22s ease, color .18s;
        }
        .foot-disclose[open] .chev { transform: rotate(180deg); color: rgba(255,255,255,.7); }

        .foot-note {
            display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 20px 48px;
            padding-bottom: 26px;
            animation: discloseIn .24s ease both;
        }
        @keyframes discloseIn { from { opacity: 0; transform: translateY(-5px); } }
        .foot-note h6 {
            display: flex; align-items: center; gap: 8px;
            font-size: .68rem; font-weight: 600; color: rgba(255,255,255,.82);
            margin-bottom: 7px;
        }
        .foot-note h6 i { color: var(--red-bright); font-size: .7rem; }
        .foot-note p { font-size: .66rem; line-height: 1.85; color: rgba(255,255,255,.42); }
        .foot-note a { color: rgba(255,255,255,.62); text-decoration: underline; text-underline-offset: 2px; }
        .foot-note a:hover { color: #fff; }

        .foot-bottom {
            position: relative;
            display: flex; align-items: center; justify-content: space-between;
            gap: 18px; flex-wrap: wrap;
            padding: 20px 0 22px;
            border-top: 1px solid rgba(255,255,255,.07);
            font-size: .68rem; color: rgba(255,255,255,.45);
        }
        .foot-legal { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
        .foot-legal a { color: rgba(255,255,255,.55); transition: color .18s; }
        .foot-legal a:hover { color: #fff; }

        /* ─── REPORT AN ISSUE ─────────────────────────────────── */
        .rpt-back {
            position: fixed; inset: 0; z-index: 400;
            background: rgba(12,18,26,.62); backdrop-filter: blur(3px);
            display: none; align-items: flex-start; justify-content: center;
            padding: 24px; overflow-y: auto; overscroll-behavior: contain;
        }
        .rpt-back.open { display: flex; }
        .rpt-modal {
            position: relative; width: 100%; max-width: 520px; margin: auto;
            background: #fff; border-radius: 16px; padding: 30px 30px 26px;
            box-shadow: 0 30px 80px rgba(12,18,26,.32);
            animation: rptIn .22s ease both;
        }
        @keyframes rptIn { from { opacity: 0; transform: translateY(14px) scale(.985); } }
        .rpt-modal h3 { font-size: 1.05rem; font-weight: 700; color: var(--navy); letter-spacing: -.02em; }
        .rpt-modal .rpt-lead { font-size: .76rem; line-height: 1.7; color: var(--body); margin: 8px 0 20px; }
        .rpt-close {
            position: absolute; top: 16px; right: 16px;
            width: 30px; height: 30px; border-radius: 8px; border: none;
            background: var(--soft); color: var(--body); cursor: pointer;
            display: grid; place-items: center; font-size: .78rem;
            transition: background .18s, color .18s;
        }
        .rpt-close:hover { background: var(--red-pale); color: var(--red); }

        .rpt-field { margin-bottom: 14px; }
        .rpt-field label {
            display: block; font-size: .7rem; font-weight: 600;
            color: var(--navy); margin-bottom: 6px;
        }
        .rpt-field input, .rpt-field select, .rpt-field textarea {
            width: 100%; font-family: inherit; font-size: .78rem; color: var(--ink);
            padding: .62rem .8rem; border: 1px solid var(--line); border-radius: 9px;
            background: #fff; transition: border-color .18s, box-shadow .18s;
        }
        .rpt-field textarea { resize: vertical; min-height: 104px; line-height: 1.65; }
        .rpt-field input:focus, .rpt-field select:focus, .rpt-field textarea:focus {
            outline: none; border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(123,29,29,.10);
        }
        .rpt-row { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 12px; }
        /* Honeypot: off-screen rather than display:none, which some bots skip. */
        .rpt-hp { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }
        .rpt-err { font-size: .68rem; color: var(--red); margin-top: 5px; display: none; }
        .rpt-err.show { display: block; }
        .rpt-actions { display: flex; align-items: center; gap: 12px; margin-top: 18px; }
        .rpt-actions .btn[disabled] { opacity: .6; pointer-events: none; }
        .rpt-note { font-size: .66rem; line-height: 1.6; color: var(--muted); margin-top: 14px; }
        .rpt-ok { display: none; text-align: center; padding: 14px 0 6px; }
        .rpt-ok.show { display: block; }
        .rpt-ok i { font-size: 2rem; color: #21a366; }
        .rpt-ok p { font-size: .82rem; color: var(--navy); font-weight: 600; margin-top: 14px; }
        .rpt-ok span { display: block; font-size: .72rem; color: var(--body); margin-top: 7px; }
        @media (max-width: 560px) {
            .rpt-back { padding: 14px; }
            .rpt-modal { padding: 26px 20px 22px; }
            .rpt-row { grid-template-columns: minmax(0, 1fr); }
            /* 16px stops iOS Safari zooming the page when a field is focused. */
            .rpt-field input, .rpt-field select, .rpt-field textarea { font-size: 16px; }
            .rpt-actions { flex-direction: column-reverse; align-items: stretch; }
            .rpt-actions .btn { justify-content: center; }
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
            .foot-note { animation: none; }
            html { scroll-behavior: auto; }
        }

        /* ─── RESPONSIVE ──────────────────────────────────────── */
        @media (max-width: 1080px) {
            .why-grid, .how-grid, .features-grid, .impact-grid, .stories-grid { grid-template-columns: minmax(0, 1fr); gap: 42px; }
            .why-right { border-left: none; padding-left: 0; }
            /* Dead centre from here down (the viewport is now narrower than the
               1320px column, so there is nothing to align to) and the floor may
               run the photo wider than the viewport without pushing the
               building off one side. */
            .hero-photo, .hero-photo-fg {
                left: 50%;
                width: 92%;
                width: min(max(92%, calc(52svh * 16 / 9)), calc(88svh * 16 / 9));
            }
            .hero-photo { opacity: .55; }
            .hero-photo-fg { opacity: .7; }
        }
        @media (max-width: 880px) {
            .nav-links { display: none; }
            .burger { display: flex; }
            .nav-brand { margin-right: 0; }
            .nav-actions { margin-left: auto; margin-right: 12px; }
            .mobile-menu { padding-left: max(28px, env(safe-area-inset-left)); padding-right: max(28px, env(safe-area-inset-right)); }
            .mobile-menu a { padding: .95rem 0; font-size: .95rem; }
            .hero { min-height: 0; padding: 112px 0 48px; }
            .why, .how, .features, .stories { padding-top: 64px; padding-bottom: 68px; }
            .device-row { max-width: 440px; margin: 0 auto; }
            .mock-wrap { max-width: 640px; margin: 0 auto; }
            /* The building photo is the hero's background again, anchored to the bottom
               and kept faint enough that the copy on top stays easy to read. */
            .hero-photo, .hero-photo-fg {
                left: 60%; width: 150%; max-width: none; bottom: 22%;
            }
            .hero-photo { opacity: .42; }
            .hero-photo-fg { opacity: .5; }
            .hero-slashes { display: none; }
            .hero-mini .mini { border-right: none; padding: 0 22px 0 0; }
            .stat-row { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 26px; }
            .device-row { grid-template-columns: minmax(0, 1fr); }
            .story { flex: 0 0 100%; padding-right: 0; }
            .car-btn.prev { left: 6px; } .car-btn.next { right: 6px; }
            .cta-inner { justify-content: center; text-align: center; }
            .foot-top { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 38px; }
            .foot-note { grid-template-columns: minmax(0, 1fr); gap: 22px; }
        }
        @media (max-width: 560px) {
            .container { padding: 0 20px; }
            .nav-inner { padding-left: 20px; padding-right: 20px; }
            .nav-brand img { height: 34px; }
            .nav-actions .btn { padding: .62rem 1.15rem; font-size: .72rem; }
            .feat-grid { grid-template-columns: minmax(0, 1fr); gap: 28px; }
            .stat-row { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
            .hero-mini { gap: 18px; }
            .mock { grid-template-columns: minmax(0, 1fr); }
            .mock-side { display: none; }
            .mock-cards, .reco-row { grid-template-columns: minmax(0, 1fr); }
            footer { padding-top: 48px; }
            .foot-top { grid-template-columns: minmax(0, 1fr); gap: 32px; padding-bottom: 38px; }
            .foot-bottom { justify-content: center; text-align: center; }

            /* Phone typography: the desktop sizes (11-12px) are too small to
               read comfortably on a handheld. */
            .nav-actions { margin-right: 4px; }
            .nav-brand img, header.nav.scrolled .nav-brand img { height: 32px; }
            .eyebrow { font-size: .68rem; }
            .sec-title { font-size: 1.55rem; }
            .sec-text { font-size: .9rem; max-width: none; }
            .hero { padding-top: 108px; }
            .hero-head { font-size: 1.45rem; }
            .hero-sub { font-size: .95rem; max-width: none; }
            /* Compact buttons: the two hero actions share one row. */
            .btn { font-size: .78rem; min-height: 40px; justify-content: center; }
            .hero-cta { flex-direction: row; align-items: center; gap: 10px; margin-top: 20px; }
            .hero-cta .btn { flex: 1 1 0; width: auto; min-width: 0; padding: 0 .9rem; white-space: nowrap; }
            .nav-actions .btn { min-height: 36px; padding: 0 1rem; font-size: .72rem; }
            /* Three compact features in one row: small icon above a short label. */
            .hero-mini { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; margin-top: 26px; align-items: start; }
            .hero-mini .mini, .hero-mini .mini:first-child {
                flex-direction: column; align-items: flex-start; gap: 7px; width: auto; padding: 0 4px 0 0; border: none;
            }
            .mini-ico { width: 28px; height: 28px; font-size: .62rem; }
            .mini-txt { font-size: .64rem; line-height: 1.35; }
            .feat h4, .step h4 { font-size: .95rem; }
            .feat p, .step p { font-size: .85rem; }
            .step-num { width: 30px; height: 30px; font-size: .78rem; }
            .step::before { left: 14px; top: 34px; }
            .device-item figcaption h4 { font-size: .92rem; }
            .device-item figcaption p { font-size: .82rem; }
            .impact { padding: 48px 0; }
            .impact h3 { font-size: 1.45rem; }
            .impact p { font-size: .88rem; max-width: none; }
            .stat span { font-size: .78rem; }
            .story-card { padding: 20px; }
            .story-head h5 { font-size: .88rem; }
            .story-head small { font-size: .72rem; }
            .story-card q { font-size: .88rem; }
            /* Arrows would sit on top of the quote text; swipe + dots replace them. */
            .car-btn { display: none; }
            .dots { margin-top: 18px; }
            .dots button { width: 8px; height: 8px; }
            .dots button.on { width: 22px; }
            .cta-inner { flex-direction: column; gap: 18px; }
            .cta-copy h4 { font-size: 1.1rem; }
            .cta-copy p { font-size: .85rem; }
            .cta-inner .btn { width: 100%; padding: 0 1.5rem; }
            .btn-white, .btn-red, .btn-outline { white-space: normal; text-align: center; }
            .foot-brand p, .foot-contact { font-size: .82rem; }
            .foot-col h5 { font-size: .85rem; }
            .foot-col a { font-size: .86rem; display: inline-block; padding: 3px 0; }
            .foot-meta { font-size: .8rem; }
            .foot-disclose > summary { font-size: .8rem; padding: 20px 0; }
            .foot-note h6 { font-size: .78rem; }
            .foot-note p { font-size: .76rem; }
            .foot-bottom { font-size: .74rem; padding-bottom: max(22px, env(safe-area-inset-bottom)); }
            /* Dashboard preview: shrink the fake UI less aggressively and stop
               the red plate poking out past the page gutter. */
            .mock-behind { left: -8px; right: 8px; bottom: -12px; }
            .mock { font-size: 12px; }
            .mock-hello { font-size: 13px; }
            .mock-hello + span { font-size: 10px; }
            .mock-user { font-size: 10px; }
            .mock-card h6 { font-size: 10px; }
            .weak, .donut-bars span { font-size: 9.5px; }
            .mock-reco { font-size: 10.5px; }
            .reco h6 { font-size: 10px; }
            .reco small { font-size: 8.5px; }
            .reco b { font-size: 9px; padding: 5px 0; }
            .reco-row { gap: 8px; }
        }
        @media (max-width: 360px) {
            .container { padding: 0 16px; }
            .nav-inner { padding-left: 16px; padding-right: 16px; }
            .nav-actions .btn i { display: none; }
            .nav-actions .btn { padding: 0 .9rem; }
            .stat-row { gap: 20px 14px; }
            .hero-head { font-size: 1.32rem; }
        }
        /* Notched phones in landscape: keep content out from under the cut-out. */
        @supports (padding: max(0px)) {
            @media (max-width: 880px) {
                .container { padding-left: max(20px, env(safe-area-inset-left)); padding-right: max(20px, env(safe-area-inset-right)); }
                .nav-inner { padding-left: max(20px, env(safe-area-inset-left)); padding-right: max(20px, env(safe-area-inset-right)); }
            }
        }
        /* Landscape phones are short: let the hero grow with its content. */
        @media (max-height: 520px) and (orientation: landscape) {
            .hero { min-height: 0; padding-top: 100px; }
        }
        /* ─── PHONE: compact sections, cards and footer ───────────────────── */
        @media (max-width: 560px) {
            /* The navbar already has Get Started, so the hero keeps just Explore Features. */
            .hero-cta .btn-red { display: none; }
            .hero-cta .btn-outline { flex: 0 0 auto; width: auto; min-height: 36px; padding: 0 1.3rem; font-size: .74rem; }

            .why, .how, .features, .stories { padding: 38px 0 42px; }
            .impact { padding: 34px 0; }
            .eyebrow { margin-bottom: 10px; font-size: .62rem; }
            .sec-title { font-size: 1.3rem; line-height: 1.28; }
            .sec-text { font-size: .78rem; line-height: 1.6; margin-top: 12px; }
            .why-grid, .how-grid, .features-grid, .impact-grid, .stories-grid { gap: 24px; }
            .btn-white { padding: 0 1.1rem; }
            .why .btn-white { margin-top: 16px !important; }

            /* Why CPAce */
            .feat-grid { gap: 16px; }
            .feat { gap: 12px; }
            .feat-ico { width: 34px; height: 34px; font-size: .78rem; }
            .feat h4 { font-size: .8rem; margin-bottom: 3px; }
            .feat p { font-size: .72rem; line-height: 1.55; }

            /* How it works */
            .steps { margin-top: 20px; }
            .step { gap: 12px; padding-bottom: 16px; }
            .step-num { width: 24px; height: 24px; font-size: .64rem; }
            .step::before { left: 11px; top: 26px; }
            .step h4 { font-size: .78rem; }
            .step p { font-size: .7rem; line-height: 1.5; }

            /* Dashboard preview: keep its real layout, just smaller */
            .mock-wrap { margin-top: 4px; }
            .mock-behind { top: 18px; bottom: -12px; left: -10px; right: 12px; }
            .mock-main { padding: 10px 10px 12px; }
            .mock-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
            .reco-row { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 6px; }
            .mock-card { padding: 8px; }
            .reco { padding: 7px; }

            /* Features */
            /* margin:auto made these grid items shrink to their content; fill the column instead */
            .device-row, .stat-row { width: 100%; max-width: none; margin: 0; }
            .device-row { gap: 14px; }
            .device { aspect-ratio: 16 / 8; padding: 12px; border-radius: 12px; }
            .frame.phone { width: 30%; }
            .device-item figcaption h4 { font-size: .74rem; margin: 10px 0 3px; }
            .device-item figcaption p { font-size: .68rem; line-height: 1.5; }

            /* Impact */
            .impact h3 { font-size: 1.2rem; }
            .impact p { font-size: .7rem; margin-top: 8px; }
            .stat-row { gap: 18px 14px; }
            .stat i { margin-bottom: 6px; font-size: .74rem; }
            .stat b { font-size: 1.4rem; }
            .stat span { font-size: .64rem; }

            /* Stories */
            .story-card { padding: 14px 16px; }
            .story-head { margin-bottom: 8px; }
            .story-avatar { width: 30px; height: 30px; font-size: .66rem; }
            .story-card q { font-size: .72rem; line-height: 1.6; }
            .dots { margin-top: 14px; }

            /* CTA + footer */
            .cta-band { padding: 28px 0; }
            .cta-inner { gap: 14px; }
            .cta-inner .wordmark { font-size: 1.3rem; }
            .cta-copy h4 { font-size: .9rem; }
            .cta-copy p { font-size: .68rem; }
            /* Footer: brand on top, the two link lists side by side, then the university card. */
            footer { padding-top: 30px; }
            .foot-top { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 22px 18px; padding-bottom: 22px; }
            .foot-brand, .foot-col:last-child { grid-column: 1 / -1; }
            .foot-brand .wordmark { font-size: 1.3rem; }
            .foot-brand p { font-size: .68rem; line-height: 1.65; margin: 8px 0 12px; max-width: none; }
            .foot-contact { font-size: .66rem; line-height: 1.8; }
            .foot-contact i { width: 14px; }
            .foot-col h5 { font-size: .7rem; margin-bottom: 0; }
            .foot-col h5::after { margin: 6px 0 10px; width: 20px; }
            .foot-col li { margin-bottom: 7px; }
            .foot-col a { font-size: .7rem; }
            .foot-col a:hover { padding-left: 0; }
            .foot-uni { padding: 10px 12px; gap: 11px; margin-bottom: 8px; border-radius: 12px; }
            .foot-uni img { width: 38px; height: 38px; }
            .foot-uni strong { font-size: .72rem; }
            .foot-uni .uni-tag { font-size: .5rem; margin-top: 3px; letter-spacing: .06em; }
            .foot-uni .uni-campus { font-size: .64rem; margin-top: 3px; }
            .foot-meta { font-size: .64rem; line-height: 1.7; }
            .foot-disclose > summary { padding: 12px 0; font-size: .66rem; }
            .foot-note h6 { font-size: .64rem; }
            .foot-note p { font-size: .62rem; line-height: 1.7; }
            .foot-bottom { padding: 12px 0 16px; font-size: .6rem; gap: 6px; flex-direction: column; text-align: center; }
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

        <div class="burger" onclick="toggleMenu()" role="button" tabindex="0" aria-label="Menu" aria-expanded="false" aria-controls="mobileMenu" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleMenu();}"><span></span><span></span><span></span></div>
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
@php
    /* ─────────────────────────────────────────────────────────────────
       DEPLOYMENT: fill these five in and the whole footer wires itself
       up. Anything left as '#' renders as plain text instead of a dead
       link, so an unfilled slot cannot ship as a broken anchor.
       ───────────────────────────────────────────────────────────────── */
    $site = [
        'privacy'  => '#',                        // Privacy Notice page
        'terms'    => '#',                        // Terms of Use page
        'bsu'      => '#',                        // https://batstate-u.edu.ph
        'campus'   => '#',                        // ARASOF-Nasugbu campus page
        'email'    => 'accountancy@bsu.edu.ph',   // support inbox
    ];
    $captureDays = \App\Models\MockExamProctorCapture::RETENTION_DAYS;
@endphp
<footer id="contact">
    <div class="container foot-top">

        <div class="foot-brand">
            <div class="wordmark on-dark"><span class="w-a">CPA</span><span class="w-b">ce</span></div>
            <p>
                An adaptive CPALE review system built for Bachelor of Science in Accountancy
                students — personalized practice, performance analytics, and intelligent
                review scheduling in one place.
            </p>
            <p class="foot-contact">
                <i class="fas fa-envelope"></i>
                <a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a><br>
                <i class="fas fa-location-dot"></i> Nasugbu, Batangas, Philippines
            </p>
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
                <li><a href="#report" data-report-open>Report an issue</a></li>
            </ul>
        </div>

        <div class="foot-col">
            <h5>Institution</h5>
            <div class="foot-uni">
                <img src="{{ asset('images/logo-bsu.png') }}" alt="Batangas State University seal">
                <div>
                    <strong>Batangas State University</strong>
                    <span class="uni-tag">The National Engineering University</span>
                    <span class="uni-campus">ARASOF-Nasugbu Campus</span>
                </div>
            </div>
            <p class="foot-meta">
                <i class="fas fa-location-dot"></i> Nasugbu, Batangas, Philippines<br>
                @if ($site['bsu'] !== '#')
                    <i class="fas fa-up-right-from-square"></i>
                    <a href="{{ $site['bsu'] }}" target="_blank" rel="noopener">batstate-u.edu.ph</a><br>
                @endif
                @if ($site['campus'] !== '#')
                    <i class="fas fa-up-right-from-square"></i>
                    <a href="{{ $site['campus'] }}" target="_blank" rel="noopener">ARASOF-Nasugbu Campus</a>
                @endif
            </p>
        </div>
    </div>

    {{-- Disclosures a deployed build needs: the proctoring captures are
         personal data, and a CPALE reviewer must not read as PRC-affiliated. --}}
    <div class="container">
      <details class="foot-disclose">
        <summary>
            <span><i class="fas fa-shield-halved lead"></i>Data Privacy &amp; Disclosure</span>
            <i class="fas fa-chevron-down chev" aria-hidden="true"></i>
        </summary>

        <div class="foot-note">
            <div>
                <h6><i class="fas fa-shield-halved"></i> Data Privacy Act of 2012 (RA 10173)</h6>
                <p>
                    CPAce processes personal data — including camera images captured during
                    proctored quizzes and mock examinations. Captures are taken only with the
                    student's consent, are visible only to the assigned faculty and the Program
                    Chair, and are deleted automatically within {{ $captureDays }} days.
                    @if ($site['privacy'] !== '#')
                        See the <a href="{{ $site['privacy'] }}">Privacy Notice</a> for the full details.
                    @endif
                </p>
            </div>

            <div>
                <h6><i class="fas fa-circle-info"></i> Academic Disclaimer</h6>
                <p>
                    CPAce is an academic capstone project of Batangas State University — The
                    National Engineering University, ARASOF-Nasugbu Campus. It is not affiliated
                    with, endorsed by, or connected to the Professional Regulation Commission or
                    the Board of Accountancy. Practice results do not predict or guarantee
                    licensure examination outcomes.
                </p>
            </div>
        </div>
      </details>
    </div>

    <div class="container foot-bottom">
        <span>&copy; {{ date('Y') }} CPAce. All rights reserved.</span>
        <nav class="foot-legal">
            @if ($site['privacy'] !== '#')<a href="{{ $site['privacy'] }}">Privacy Notice</a>@endif
            @if ($site['terms'] !== '#')<a href="{{ $site['terms'] }}">Terms of Use</a>@endif
        </nav>
        <span>Version 1.0.0 &middot; Capstone Project &middot; BSU ARASOF-Nasugbu</span>
    </div>
</footer>

<!-- ══ REPORT AN ISSUE ══ -->
<div class="rpt-back" id="reportBack" role="dialog" aria-modal="true" aria-labelledby="reportTitle">
    <div class="rpt-modal">
        <button type="button" class="rpt-close" data-report-close aria-label="Close">
            <i class="fas fa-xmark"></i>
        </button>

        {{-- The form posts for real, so it still works with JS off; the script
             below intercepts it to submit in place and keep the visitor here. --}}
        <form id="reportForm" method="POST" action="{{ route('report-issue.store') }}" novalidate>
            @csrf
            <input type="hidden" name="page_url" id="reportPageUrl" value="">
            <div class="rpt-hp" aria-hidden="true">
                <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <h3 id="reportTitle">Report an issue</h3>
            <p class="rpt-lead">
                Tell us what went wrong and we'll look into it. If it's about your account,
                use the email address the account is registered under.
            </p>

            <div class="rpt-row">
                <div class="rpt-field">
                    <label for="rptName">Your name</label>
                    <input type="text" id="rptName" name="name" maxlength="120" required
                           value="{{ auth()->check() ? auth()->user()->first_name . ' ' . auth()->user()->last_name : '' }}">
                    <p class="rpt-err" data-err="name"></p>
                </div>
                <div class="rpt-field">
                    <label for="rptEmail">Email</label>
                    <input type="email" id="rptEmail" name="email" maxlength="160" required
                           value="{{ auth()->check() ? auth()->user()->email : '' }}">
                    <p class="rpt-err" data-err="email"></p>
                </div>
            </div>

            <div class="rpt-field">
                <label for="rptCategory">What kind of issue?</label>
                <select id="rptCategory" name="category" required>
                    @foreach (\App\Models\IssueReport::CATEGORIES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <p class="rpt-err" data-err="category"></p>
            </div>

            <div class="rpt-field">
                <label for="rptMessage">What happened?</label>
                <textarea id="rptMessage" name="message" maxlength="3000" required
                          placeholder="What were you doing, what did you expect, and what happened instead?"></textarea>
                <p class="rpt-err" data-err="message"></p>
            </div>

            <div class="rpt-actions">
                <button type="submit" class="btn btn-red btn-pill" id="reportSubmit">
                    Send report <i class="fas fa-paper-plane"></i>
                </button>
                <button type="button" class="btn btn-outline btn-pill" data-report-close>Cancel</button>
            </div>

            <p class="rpt-note">
                We record the page you were on and your browser version to help reproduce the
                problem. Your report is kept only for as long as it takes to resolve it.
            </p>
        </form>

        <div class="rpt-ok" id="reportOk">
            <i class="fas fa-circle-check"></i>
            <p id="reportOkMsg">Thanks — your report has been sent.</p>
            <span>We'll follow up by email if we need more detail.</span>
        </div>
    </div>
</div>

<script>
    // Navbar shadow on scroll
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 16);
    });

    // Mobile menu
    function toggleMenu() {
        const open = document.getElementById('mobileMenu').classList.toggle('open');
        const burger = document.querySelector('.burger');
        burger.classList.toggle('open', open);
        burger.setAttribute('aria-expanded', open);
    }
    // Close the menu if the screen grows past the phone/tablet breakpoint.
    window.addEventListener('resize', () => {
        if (window.innerWidth > 880 && document.getElementById('mobileMenu').classList.contains('open')) toggleMenu();
    });

    // Active nav link on scroll. The trip line sits just under the navbar so
    // the highlight flips at the moment a section locks to the bar, matching
    // html{scroll-padding-top}. The mobile menu is included so both menus
    // always agree on where you are.
    const NAV_OFFSET = 78;
    const navLinks = [...document.querySelectorAll('#navLinks a, #mobileMenu a')];
    const sections = [...new Set(navLinks.map(a => a.getAttribute('href')))]
        .filter(h => h && h.length > 1 && h.startsWith('#'))
        .map(h => document.querySelector(h))
        .filter(Boolean);

    function syncActiveLink() {
        const y = window.scrollY + NAV_OFFSET;
        let current = sections[0];
        sections.forEach(s => { if (s.offsetTop <= y) current = s; });

        // The footer is shorter than the viewport, so its top can never cross
        // the trip line - at the very bottom of the page the last link wins.
        if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2) {
            current = sections[sections.length - 1];
        }

        navLinks.forEach(a => a.classList.toggle('active', a.getAttribute('href') === '#' + current.id));
    }

    window.addEventListener('scroll', syncActiveLink, { passive: true });
    window.addEventListener('resize', syncActiveLink);
    syncActiveLink();

    // ── Report an issue ──────────────────────────────────────────────────
    // The form posts normally without JS; this only upgrades it to submit in
    // place so the visitor is not bounced off the page to see a flash message.
    const rptBack   = document.getElementById('reportBack');
    const rptForm   = document.getElementById('reportForm');
    const rptOk     = document.getElementById('reportOk');
    const rptSubmit = document.getElementById('reportSubmit');
    let   rptOpener = null;

    function openReport(e) {
        if (e) e.preventDefault();
        rptOpener = document.activeElement;
        document.getElementById('reportPageUrl').value = window.location.href;
        rptBack.classList.add('open');
        document.body.style.overflow = 'hidden';
        // Skip straight to the first empty field: signed-in visitors already
        // have their name and email filled in for them.
        const first = [...rptForm.querySelectorAll('input[required], textarea')].find(i => !i.value);
        (first || rptForm.querySelector('#rptName')).focus();
    }

    function closeReport() {
        rptBack.classList.remove('open');
        document.body.style.overflow = '';
        if (rptOpener) rptOpener.focus();
    }

    document.querySelectorAll('[data-report-open]').forEach(a => a.addEventListener('click', openReport));
    document.querySelectorAll('[data-report-close]').forEach(b => b.addEventListener('click', closeReport));
    rptBack.addEventListener('click', e => { if (e.target === rptBack) closeReport(); });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && rptBack.classList.contains('open')) closeReport();
    });

    function clearErrors() {
        rptForm.querySelectorAll('.rpt-err').forEach(p => { p.textContent = ''; p.classList.remove('show'); });
    }

    rptForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors();
        rptSubmit.disabled = true;
        rptSubmit.innerHTML = 'Sending…';

        try {
            const res = await fetch(rptForm.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(rptForm),
            });

            if (res.status === 422) {
                const { errors } = await res.json();
                Object.entries(errors || {}).forEach(([field, msgs]) => {
                    const p = rptForm.querySelector(`[data-err="${field}"]`);
                    if (p) { p.textContent = msgs[0]; p.classList.add('show'); }
                });
                return;
            }

            if (res.status === 429) {
                const p = rptForm.querySelector('[data-err="message"]');
                p.textContent = 'Too many reports sent just now. Please try again in a minute.';
                p.classList.add('show');
                return;
            }

            if (!res.ok) throw new Error('HTTP ' + res.status);

            const data = await res.json();
            document.getElementById('reportOkMsg').textContent = data.message;
            rptForm.style.display = 'none';
            rptOk.classList.add('show');
            setTimeout(() => {
                closeReport();
                // Put the form back so a second report can be filed this visit.
                rptForm.reset();
                rptForm.style.display = '';
                rptOk.classList.remove('show');
            }, 2600);
        } catch (err) {
            const p = rptForm.querySelector('[data-err="message"]');
            p.textContent = 'Could not send the report. Please check your connection and try again.';
            p.classList.add('show');
        } finally {
            rptSubmit.disabled = false;
            rptSubmit.innerHTML = 'Send report <i class="fas fa-paper-plane"></i>';
        }
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

    // Swipe left/right on touch screens.
    let touchX = null, touchY = null;
    const viewport = document.querySelector('.carousel-viewport');
    viewport.addEventListener('touchstart', e => {
        touchX = e.touches[0].clientX; touchY = e.touches[0].clientY;
    }, { passive: true });
    viewport.addEventListener('touchend', e => {
        if (touchX === null) return;
        const dx = e.changedTouches[0].clientX - touchX;
        const dy = e.changedTouches[0].clientY - touchY;
        touchX = touchY = null;
        if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) slide(dx < 0 ? 1 : -1);
    }, { passive: true });

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
