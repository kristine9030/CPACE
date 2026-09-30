{{--
    Shared student dark-mode theme.
    Included from partials.sidebar, so it loads on every student page automatically.

    - The inline script (run as early as possible) applies the saved theme before
      paint to avoid a flash of the light theme.
    - The CSS below overrides both the shared CSS custom properties and the common
      hard-coded surface/border colours used across the student pages.
    - Toggle the theme anywhere with:  window.CPACE.setTheme('dark' | 'light')
      or listen for the 'cpace:themechange' event on window.
--}}

{{-- Apply saved theme ASAP (before body paints) --}}
<script>
    (function () {
        try {
            var t = localStorage.getItem('cpace-theme') || 'light';
            if (t === 'dark') document.documentElement.classList.add('dark');
        } catch (e) {}

        window.CPACE = window.CPACE || {};
        window.CPACE.getTheme = function () {
            try { return localStorage.getItem('cpace-theme') || 'light'; } catch (e) { return 'light'; }
        };
        window.CPACE.setTheme = function (theme) {
            var dark = theme === 'dark';
            document.documentElement.classList.toggle('dark', dark);
            try { localStorage.setItem('cpace-theme', dark ? 'dark' : 'light'); } catch (e) {}
            window.dispatchEvent(new CustomEvent('cpace:themechange', { detail: { theme: dark ? 'dark' : 'light' } }));
        };
        window.CPACE.toggleTheme = function () {
            window.CPACE.setTheme(window.CPACE.getTheme() === 'dark' ? 'light' : 'dark');
        };
    })();
</script>

<style>
    /* ═══════════════════════ DARK MODE ═══════════════════════ */
    /* Smooth transition when flipping themes (not on first paint). */
    html.dark body,
    html.dark .card,
    html.dark .metric-card,
    html.dark .welcome-banner,
    html.dark .streak-card,
    html.dark .quote-card {
        transition: background-color 0.25s ease, color 0.25s ease, border-color 0.25s ease;
    }

    /* --- Remap the shared design tokens so var()-based colours flip for free --- */
    html.dark {
        --white:     #1e1e26;
        --gray-100:  #24242e;
        --gray-200:  #2b2b36;
        --gray-300:  #3a3a46;
        --gray-500:  #9aa0ab;
        --gray-700:  #c4c7cf;
        --gray-900:  #e7e7ec;
        --primary-light: #3a2323;
    }

    /* --- Page background --- */
    html.dark body {
        background: #121218 !important;
        color: #e7e7ec;
    }

    /* --- Surfaces / cards --- */
    html.dark .card,
    html.dark .metric-card,
    html.dark .streak-card,
    html.dark .quote-card,
    html.dark .dropdown-menu,
    html.dark .more-drawer,
    html.dark .modal,
    html.dark .modal-content,
    html.dark .panel,
    html.dark .box,
    html.dark .tile,
    html.dark .stat-card,
    html.dark .list-card,
    html.dark .section-card {
        background: #1e1e26 !important;
        color: #e7e7ec;
        border-color: #2e2e3a;
    }

    /* Welcome banner uses a light-pink gradient -> darken it */
    html.dark .welcome-banner {
        background: linear-gradient(to right, #2a1c1c 0%, #221a1f 50%, #1e1e26 100%) !important;
    }
    html.dark .welcome-banner h2,
    html.dark .welcome-banner p { color: #e7e7ec; }

    /* --- Headings & text --- */
    html.dark h1, html.dark h2, html.dark h3, html.dark h4, html.dark h5, html.dark h6,
    html.dark .page-title,
    html.dark .card-title,
    html.dark .metric-number,
    html.dark .streak-num,
    html.dark .progress-pct {
        color: #f2f2f5;
    }
    html.dark .page-subtitle,
    html.dark .metric-label,
    html.dark .metric-change.neutral,
    html.dark .streak-sub,
    html.dark .quote-author,
    html.dark .activity-meta,
    html.dark .activity-time,
    html.dark .weakness-sub {
        color: #9aa0ab;
    }
    html.dark .quote-text { color: #c4c7cf; }

    /* --- Borders / dividers (light greys used across pages) --- */
    html.dark .subject-item,
    html.dark .weakness-item,
    html.dark .activity-item,
    html.dark .card-header,
    html.dark table th,
    html.dark table td,
    html.dark tr {
        border-color: #2e2e3a !important;
    }
    html.dark hr { border-color: #2e2e3a; }

    /* --- Hover states that used light greys --- */
    html.dark .subject-item:hover,
    html.dark .dropdown-menu a:hover,
    html.dark .dropdown-menu button:hover,
    html.dark .toggle-btn:hover,
    html.dark .notif-btn:hover {
        background: #2b2b36 !important;
    }

    /* --- Top-bar controls / inputs --- */
    html.dark .toggle-btn,
    html.dark .notif-btn,
    html.dark .search-wrap input,
    html.dark input,
    html.dark textarea,
    html.dark select {
        background: #24242e !important;
        color: #e7e7ec !important;
        border-color: #3a3a46 !important;
    }
    html.dark .search-wrap i { color: #9aa0ab; }
    html.dark input::placeholder,
    html.dark textarea::placeholder { color: #6b7280 !important; }

    /* --- Tables --- */
    html.dark table { color: #e7e7ec; }
    html.dark thead th { background: #24242e !important; color: #c4c7cf !important; }
    html.dark tbody tr:hover { background: #24242e !important; }

    /* --- Dropdown / menu text --- */
    html.dark .dropdown-menu a,
    html.dark .dropdown-menu button { color: #e7e7ec; border-color: #2b2b36; }

    /* --- Badges / pills that used very light backgrounds keep their accent,
           but neutral "grey" chips get darkened --- */
    html.dark .badge-neutral,
    html.dark .chip,
    html.dark .tag {
        background: #2b2b36 !important;
        color: #c4c7cf !important;
    }

    /* --- Scrollbar --- */
    html.dark ::-webkit-scrollbar { width: 10px; height: 10px; }
    html.dark ::-webkit-scrollbar-track { background: #16161c; }
    html.dark ::-webkit-scrollbar-thumb { background: #3a3a46; border-radius: 6px; }
    html.dark ::-webkit-scrollbar-thumb:hover { background: #4a4a58; }

    /* Native controls (date pickers, checkboxes, scrollbars) follow the theme. */
    html.dark { color-scheme: dark; }

    /* Set for a frame while the contrast guard below measures colours. */
    html.dm-measuring *, html.dm-measuring *::before, html.dm-measuring *::after { transition: none !important; }
</style>

{{--
    Dark-mode contrast guard.

    The student pages each hard-code their own text and surface colours, so the
    class-based rules above can never cover every one of them - text ended up
    light-on-white or dark-on-dark. When dark mode is on, this pass:
      1. turns any remaining solid light surface (white cards, pale chips,
         white buttons) into a dark one, and
      2. checks every piece of text against the background it actually sits
         on and, when it is hard to read (contrast < 4.5:1), lightens it while
         keeping its hue - maroon becomes a light rose, navy a near-white.
    Elements painted with an image or gradient (the sidebar, banners, hero
    cards) are left alone, as are their contents. Every change is recorded and
    undone when the student switches back to light mode, and new content
    (dropdowns, modals, charts rendered later) is handled as it appears.

    Mark an element with data-dm-skip to exclude it and its contents.
--}}
<script>
(function () {
    var DARK_SURFACE = [30, 30, 38];      // #1e1e26, same as html.dark .card
    var SKIP_TAGS = { SCRIPT: 1, STYLE: 1, NOSCRIPT: 1, SVG: 1, CANVAS: 1, IMG: 1, VIDEO: 1, IFRAME: 1, PATH: 1, BR: 1, HEAD: 1, META: 1, LINK: 1, TITLE: 1 };
    var touched = [];
    var observer = null;
    var queued = null;

    function parse(c) {
        var m = c && c.match(/rgba?\(([^)]+)\)/);
        if (!m) return null;
        var p = m[1].split(',').map(function (x) { return parseFloat(x); });
        return [p[0], p[1], p[2], p.length > 3 ? p[3] : 1];
    }
    function lum(c) {
        var a = [c[0], c[1], c[2]].map(function (v) {
            v /= 255;
            return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
        });
        return 0.2126 * a[0] + 0.7152 * a[1] + 0.0722 * a[2];
    }
    function contrast(a, b) {
        var la = lum(a), lb = lum(b);
        return (Math.max(la, lb) + 0.05) / (Math.min(la, lb) + 0.05);
    }
    function toHsl(c) {
        var r = c[0] / 255, g = c[1] / 255, b = c[2] / 255;
        var max = Math.max(r, g, b), min = Math.min(r, g, b), h = 0, s = 0, l = (max + min) / 2;
        if (max !== min) {
            var d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            h = max === r ? (g - b) / d + (g < b ? 6 : 0) : max === g ? (b - r) / d + 2 : (r - g) / d + 4;
            h /= 6;
        }
        return [h, s, l];
    }
    function hslCss(h, s, l) {
        return 'hsl(' + Math.round(h * 360) + ',' + Math.round(s * 100) + '%,' + Math.round(l * 100) + '%)';
    }
    function fromHsl(h, s, l) {
        function f(n) {
            var k = (n + h * 12) % 12, a = s * Math.min(l, 1 - l);
            return Math.round(255 * (l - a * Math.max(-1, Math.min(k - 3, 9 - k, 1))));
        }
        return [f(0), f(8), f(4)];
    }

    function skip(el) {
        return SKIP_TAGS[el.tagName.toUpperCase()] || el.closest('[data-dm-skip]') || el.closest('svg');
    }

    // The colour a piece of text actually sits on. null = an image/gradient
    // (unknown), in which case the text is left as designed.
    function backdrop(el) {
        for (var n = el; n && n.nodeType === 1; n = n.parentElement) {
            var cs = getComputedStyle(n);
            if (cs.backgroundImage && cs.backgroundImage !== 'none') return null;
            var bg = parse(cs.backgroundColor);
            if (bg && bg[3] >= 0.5) return bg;
        }
        return parse(getComputedStyle(document.body).backgroundColor);
    }

    function hasOwnText(el) {
        if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT') return false;
        for (var c = el.firstChild; c; c = c.nextSibling) {
            if (c.nodeType === 3 && c.nodeValue.trim()) return true;
        }
        // Icon fonts carry no text node but still need a readable colour.
        return el.tagName === 'I' && /\bfa-/.test(el.className);
    }

    // dataset keys must be camelCase: 'background-color' -> 'dmOrigBackgroundColor'.
    function keyFor(prop) {
        return 'dmOrig' + prop.replace(/(^|-)([a-z])/g, function (_, __, c) { return c.toUpperCase(); });
    }

    function record(el, prop) {
        var key = keyFor(prop);
        if (el.dataset[key] === undefined) {
            el.dataset[key] = el.style.getPropertyValue(prop) + '|' + el.style.getPropertyPriority(prop);
            touched.push(el);
        }
    }

    function darkenSurfaces(els) {
        els.forEach(function (el) {
            if (skip(el)) return;
            var cs = getComputedStyle(el);
            if (cs.backgroundImage !== 'none') return;
            var bg = parse(cs.backgroundColor);
            if (!bg || bg[3] < 0.9 || lum(bg) < 0.78) return;
            record(el, 'background-color');
            el.style.setProperty('background-color', 'rgb(' + DARK_SURFACE.join(',') + ')', 'important');
            var border = parse(cs.borderTopColor);
            if (border && parseFloat(cs.borderTopWidth) > 0 && lum(border) > 0.6) {
                record(el, 'border-color');
                el.style.setProperty('border-color', '#2e2e3a', 'important');
            }
        });
    }

    function fixText(els) {
        els.forEach(function (el) {
            if (skip(el) || !hasOwnText(el)) return;
            var fg = parse(getComputedStyle(el).color);
            if (!fg || fg[3] < 0.5) return;               // transparent / gradient-clipped text
            var bg = backdrop(el);
            if (!bg || contrast(fg, bg) >= 4.5) return;

            var hsl = toHsl(fg), h = hsl[0], s = hsl[1], l;
            var bgDark = lum(bg) < 0.4;
            if (bgDark) {
                // Keep brand hues recognisable (maroon -> rose); neutrals and
                // near-black inks (e.g. the #14283E navy titles) become near-white.
                if (s > 0.25 && hsl[2] >= 0.22) { s = Math.min(s, 0.8); l = Math.max(hsl[2], 0.72); }
                else          { s = Math.min(s, 0.1); l = hsl[2] < 0.45 ? 0.9 : 0.78; }
                while (l < 0.96 && contrast(fromHsl(h, s, l), bg) < 4.5) l += 0.04;
            } else {
                l = Math.min(hsl[2], 0.3);
                while (l > 0.05 && contrast(fromHsl(h, s, l), bg) < 4.5) l -= 0.04;
            }
            record(el, 'color');
            el.style.setProperty('color', hslCss(h, s, l), 'important');
        });
    }

    function run(root) {
        if (!document.documentElement.classList.contains('dark')) return;
        var scope = root || document.body;
        if (!scope || !scope.querySelectorAll) return;
        var els = [scope].concat(Array.prototype.slice.call(scope.querySelectorAll('*')));
        // Freeze transitions while measuring: mid-transition, getComputedStyle
        // reports the old (light) colours and the text would be judged wrongly.
        var root = document.documentElement;
        root.classList.add('dm-measuring');
        void root.offsetHeight;
        darkenSurfaces(els);
        void root.offsetHeight;
        fixText(els);
        requestAnimationFrame(function () { root.classList.remove('dm-measuring'); });
    }

    function undo() {
        touched.forEach(function (el) {
            ['background-color', 'border-color', 'color'].forEach(function (prop) {
                var key = keyFor(prop);
                if (el.dataset[key] === undefined) return;
                var parts = el.dataset[key].split('|');
                if (parts[0]) el.style.setProperty(prop, parts[0], parts[1] || '');
                else el.style.removeProperty(prop);
                delete el.dataset[key];
            });
        });
        touched = [];
    }

    function watch(on) {
        if (!('MutationObserver' in window)) return;
        if (on && !observer) {
            var pending = [];
            observer = new MutationObserver(function (muts) {
                muts.forEach(function (m) {
                    m.addedNodes.forEach(function (n) { if (n.nodeType === 1) pending.push(n); });
                });
                if (!pending.length || queued) return;
                queued = setTimeout(function () {
                    var nodes = pending.splice(0);
                    queued = null;
                    nodes.forEach(function (n) { if (n.isConnected) run(n); });
                }, 60);
            });
            observer.observe(document.body, { childList: true, subtree: true });
        } else if (!on && observer) {
            observer.disconnect();
            observer = null;
        }
    }

    function apply() {
        var dark = document.documentElement.classList.contains('dark');
        undo();
        if (dark) run();
        watch(dark);
    }

    window.CPACE = window.CPACE || {};
    window.CPACE.refreshContrast = apply;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', apply);
    } else {
        apply();
    }
    // Late-loading fonts, charts and entrance animations settle after load.
    window.addEventListener('load', function () { setTimeout(apply, 50); });
    window.addEventListener('cpace:themechange', apply);
})();
</script>
