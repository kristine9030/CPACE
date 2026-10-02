{{--
    Shared by every role's sidebar:
      1. Avatars are always circles, so the pictures from public/images/AVATARS fit everywhere
         (page-level styles used rounded squares in several places).
      2. The collapse chevron: it only shows while the pointer is over the sidebar.
    Included right after each sidebar's </aside>.
--}}
<style>
    .profile-avatar, .profile-btn, .topbar-avatar-btn, .avatar-sm, .ni-avatar, .fac-av, .user-av, .student-av, .t-avatar {
        border-radius: 50% !important; overflow: hidden;
    }
    .profile-avatar img, .profile-btn img, .topbar-avatar-btn img, .avatar-sm img, .ni-avatar img, .user-av img { border-radius: 50% !important; }

    /* Page headers sit above the cards below them, so the profile dropdown is never covered
       (a header that plays an entry animation otherwise ends up underneath later cards). */
    .top-bar { position: relative; z-index: 500; }
    .topbar { z-index: 500; }

    /* Tablet widths: header actions wrap to a second line instead of pushing the page sideways. */
    @media (max-width: 1180px) {
        .topbar { flex-wrap: wrap; }
        .topbar-right { flex-wrap: wrap; justify-content: flex-end; max-width: 100%; }
    }

    .sidebar .sidebar-collapse-btn {
        position: absolute; top: 16px; right: 10px;
        width: 26px; height: 26px; border: 0; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(255,255,255,0.16); color: #fff; font-size: 12px;
        cursor: pointer; opacity: 0; pointer-events: none;
        transition: opacity .18s, background .15s; z-index: 5;
    }
    .sidebar:hover .sidebar-collapse-btn, .sidebar .sidebar-collapse-btn:focus-visible { opacity: 1; pointer-events: auto; }
    .sidebar .sidebar-collapse-btn:hover { background: rgba(255,255,255,0.3); }
    .sidebar .sidebar-collapse-btn i { transition: transform .28s; }
    .sidebar.collapsed .sidebar-collapse-btn { top: 4px; right: 4px; width: 20px; height: 20px; font-size: 10px; }
    .sidebar.collapsed .sidebar-collapse-btn i { transform: rotate(180deg); }
    @media (hover: none) { .sidebar .sidebar-collapse-btn { opacity: 1; pointer-events: auto; } }

    /* ── Phones and small tablets: the sidebar becomes a slide-in drawer opened from a top bar ── */
    .mob-bar, .mob-scrim { display: none; }
    @media (max-width: 768px) {
        .mob-bar {
            display: flex; position: fixed; top: 0; left: 0; right: 0; height: 56px; z-index: 2400;
            align-items: center; gap: 12px; padding: 0 14px; color: #fff;
            background: linear-gradient(135deg, #6b1515 0%, #9b2b2b 100%); box-shadow: 0 2px 12px rgba(0,0,0,.2);
            font-family: 'Poppins', sans-serif;
        }
        .mob-bar button { width: 40px; height: 40px; border: 0; border-radius: 10px; background: rgba(255,255,255,.16); color: #fff; font-size: 17px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .mob-bar img { width: 32px; height: 32px; object-fit: contain; }
        .mob-bar b { font-size: 16px; font-weight: 700; letter-spacing: .5px; }
        .mob-bar span { font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; background: rgba(255,255,255,.2); padding: 2px 9px; border-radius: 20px; margin-left: auto; }
        body.has-mob-bar { padding-top: 56px; }
        .mob-scrim { display: block; position: fixed; inset: 0; z-index: 2500; background: rgba(15,5,5,.5); opacity: 0; pointer-events: none; transition: opacity .2s; }
        .mob-scrim.show { opacity: 1; pointer-events: auto; }

        body.has-mob-bar .sidebar {
            display: flex !important; transform: translateX(-104%); transition: transform .25s ease;
            width: 272px !important; z-index: 2600 !important; border-radius: 0 22px 22px 0;
        }
        body.has-mob-bar .sidebar.mobile-open { transform: none; box-shadow: 8px 0 32px rgba(0,0,0,.35); }
        body.has-mob-bar .sidebar.collapsed { width: 272px !important; }
        body.has-mob-bar .sidebar .logo-text, body.has-mob-bar .sidebar .portal-badge,
        body.has-mob-bar .sidebar .sidebar-nav li a span, body.has-mob-bar .sidebar .user-details,
        body.has-mob-bar .sidebar .chevron-icon, body.has-mob-bar .sidebar .nav-label { display: revert !important; visibility: visible !important; }
        body.has-mob-bar .sidebar .sidebar-nav li a { justify-content: flex-start !important; padding: 10px 14px !important; gap: 12px !important; margin: 1px 10px !important; }
        body.has-mob-bar .sidebar .sidebar-logo { justify-content: flex-start !important; padding: 14px 20px !important; }
        body.has-mob-bar .sidebar .user-profile { justify-content: flex-start !important; padding: 8px 12px !important; }
        body.has-mob-bar .sidebar .sidebar-collapse-btn { display: none; }
        body.has-mob-bar .main, body.has-mob-bar .main-content, body.has-mob-bar .help-main, body.has-mob-bar .notification-main { margin-left: 0 !important; }
    }
</style>
<script>
(function () {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar || document.getElementById('sidebarChevron')) return;
    const logo = document.getElementById('sidebarCollapseBtn');
    if (!logo) return;
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.id = 'sidebarChevron';
    btn.className = 'sidebar-collapse-btn';
    btn.setAttribute('aria-label', 'Collapse or expand the sidebar');
    btn.title = 'Collapse or expand sidebar';
    btn.innerHTML = '<i class="fas fa-angles-left"></i>';
    // Does exactly what clicking the logo does, so each role's own remembered state keeps working.
    btn.addEventListener('click', function (e) { e.stopPropagation(); logo.click(); });
    sidebar.appendChild(btn);
})();

// Mobile drawer: a top bar with a menu button opens the sidebar. Pages that already ship their own
// mobile header and bottom navigation (most student pages) keep it and get no second bar.
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar || document.querySelector('.mobile-app-header, .mobile-bottom-nav, .mob-bar')) return;
    const badge = sidebar.querySelector('.portal-badge');
    const bar = document.createElement('header');
    bar.className = 'mob-bar';
    bar.innerHTML = '<button type="button" aria-label="Open menu" aria-expanded="false"><i class="fas fa-bars"></i></button>'
        + '<img src="' + @json(asset('images/cpace_logo.png')) + '" alt=""><b>CPACE</b><span>' + (badge ? badge.textContent.trim() : 'Menu') + '</span>';
    const scrim = document.createElement('div');
    scrim.className = 'mob-scrim';
    document.body.prepend(bar);
    document.body.appendChild(scrim);
    document.body.classList.add('has-mob-bar');
    const btn = bar.querySelector('button');
    function setOpen(open) {
        sidebar.classList.toggle('mobile-open', open);
        scrim.classList.toggle('show', open);
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    btn.addEventListener('click', function () { setOpen(!sidebar.classList.contains('mobile-open')); });
    scrim.addEventListener('click', function () { setOpen(false); });
    sidebar.addEventListener('click', function (e) { if (e.target.closest('a[href]')) setOpen(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
    window.matchMedia('(min-width: 769px)').addEventListener('change', function (m) { if (m.matches) setOpen(false); });
});
</script>
