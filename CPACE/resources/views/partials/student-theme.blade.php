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
</style>
