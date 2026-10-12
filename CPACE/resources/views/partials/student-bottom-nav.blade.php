{{--
    Mobile navigation for student pages (phones / small tablets only).

    A floating pill bar like the CPAce mobile app - Home | Quizzes | (menu) |
    Performance | Calendar - plus a centre button that opens a sheet holding
    EVERY page from the web sidebar, grouped the same way (Main / Study /
    Track / Community / Account) with the same icons and labels.

    Usage: @include('partials.student-bottom-nav', ['active' => 'dashboard'])
    Active keys: dashboard | subjects | quizzes | class-quizzes | mock-exams |
                 review-notes | performance | calendar | achievements |
                 community | resources | notifications | settings | help
--}}
@php
    $bnActive = $active ?? '';
    $bnAlumni = Auth::user()?->hasAlumniAccess() ?? false;
    $bnUnread = (int) ($unreadNotifications ?? 0);

    // Same groups, order, labels and icons as partials/sidebar.blade.php.
    $bnGroups = [
        'Main' => [
            ['dashboard',      'Home',             'fa-home',           route('dashboard')],
            ['subjects',       'Resources',        'fa-book-open',      route('subjects')],
        ],
        'Study' => [
            ['quizzes',        'Practice Quizzes', 'fa-pen-fancy',      route('adaptive-quizzes')],
            ['class-quizzes',  'Class Quizzes',    'fa-clipboard-list', route('class-quizzes')],
            ['mock-exams',     'Mock Exams',       'fa-file-alt',       route('mock-exams'), $bnAlumni],
            ['review-notes',   'Review Notes',     'fa-sticky-note',    route('review-notes')],
        ],
        'Track' => [
            ['performance',    'Performance',      'fa-chart-bar',      route('performance')],
            ['calendar',       'Calendar',         'fa-calendar-alt',   route('calendar')],
            ['achievements',   'Achievements',     'fa-trophy',         route('achievements')],
        ],
        'Community' => array_values(array_filter([
            ['community',      'Alumni Community', 'fa-people-group',   route('community.index')],
            $bnAlumni ? ['resources', 'Resource Library', 'fa-book', route('community.resources.index')] : null,
        ])),
        'Account' => [
            ['notifications',  'Notifications',    'fa-bell',           route('notifications.index'), false, $bnUnread],
            ['settings',       'Settings',         'fa-cog',            route('settings')],
            ['help',           'Help & Support',   'fa-circle-question',route('help.index')],
        ],
    ];
    $bnInSheet = in_array($bnActive, ['subjects','class-quizzes','mock-exams','review-notes','achievements','community','resources','notifications','settings','help'], true);
@endphp

<style>
    :root { --bn-grad: linear-gradient(135deg, #4A0A0C, #7B1416, #B52525); --bn-h: 72px; }

    .bottom-nav, .bn-sheet, .bn-back { display: none; }

    @media (max-width: 768px) {
        .bottom-nav {
            display: block; position: fixed; left: 0; right: 0; bottom: 0; z-index: 1100;
            padding: 8px 8px max(10px, env(safe-area-inset-bottom));
            pointer-events: none; font-family: 'Poppins', sans-serif;
            transition: transform .25s ease;
        }
        /* Slides away while a field is focused so the keyboard has room. */
        .bottom-nav.bn-hidden { transform: translateY(120%); }
        /* Frosted floating capsule: icons only, the current page expands to icon + name. */
        .bottom-nav-card {
            pointer-events: auto; height: var(--bn-h); border-radius: 34px;
            background: rgba(255,255,255,.86);
            -webkit-backdrop-filter: saturate(180%) blur(18px); backdrop-filter: saturate(180%) blur(18px);
            border: 1px solid rgba(255,255,255,.7);
            display: flex; align-items: center; padding: 0 4px; gap: 0;
            box-shadow: 0 10px 30px rgba(74,10,12,.18), 0 2px 8px rgba(0,0,0,.06), inset 0 1px 0 rgba(255,255,255,.9);
        }
        /* Two equal halves either side of the button: a tab growing inside its half
           can never push the centre button off-centre. */
        .bn-group { flex: 1 1 0; min-width: 0; display: flex; align-items: center; justify-content: space-evenly; gap: 2px; }
        .bottom-nav .bn-tab {
            flex: 0 0 36px; min-width: 0; height: 46px; margin: 0; padding: 0; border-radius: 23px;
            display: flex; flex-direction: row; align-items: center; justify-content: center; gap: 8px;
            text-decoration: none; color: #8A8F9A; font-size: 12.5px; font-weight: 600; line-height: 1;
            white-space: nowrap; overflow: hidden;
            transition: flex-grow .3s cubic-bezier(.3,1.1,.5,1), background .25s, color .25s, box-shadow .25s;
            -webkit-tap-highlight-color: transparent;
        }
        .bottom-nav .bn-tab i { font-size: 19px; flex: 0 0 auto; }
        /* Inactive tabs show the icon only; the name stays available to screen readers. */
        .bottom-nav .bn-tab span {
            max-width: 0; opacity: 0; overflow: hidden; text-overflow: ellipsis;
            transition: max-width .3s ease, opacity .2s ease;
        }
        .bottom-nav .bn-tab.active {
            flex: 1 1 0; padding: 0 9px; gap: 5px; font-size: 12px; color: #fff; background: var(--bn-grad);
            box-shadow: 0 6px 16px rgba(123,20,22,.38), inset 0 1px 0 rgba(255,255,255,.22);
        }
        .bottom-nav .bn-tab.active span { max-width: 100%; opacity: 1; }
        .bottom-nav .bn-tab span b { font-weight: inherit; }
        .bn-short { display: none; }
        @media (max-width: 440px) { .bn-long { display: none; } .bn-short { display: inline; } }
        .bottom-nav .bn-tab:not(.active):active { background: rgba(123,20,22,.08); color: #7B1416; }

        .bn-fab {
            position: relative; flex: 0 0 56px; width: 56px; height: 56px; margin: 0 2px; z-index: 2; border: none; border-radius: 50%;
            cursor: pointer; background: var(--bn-grad); display: grid; place-items: center; padding: 0;
            transform: translateY(-12px);
            box-shadow: 0 0 0 5px rgba(255,255,255,.92), 0 10px 22px rgba(123,20,22,.45);
            transition: transform .25s cubic-bezier(.3,1.3,.5,1), box-shadow .2s;
            -webkit-tap-highlight-color: transparent;
        }
        .bn-fab:active { transform: translateY(-12px) scale(.94); }
        .bn-fab.open { transform: translateY(-12px) rotate(45deg); }
        .bn-fab.has-active::after {
            content: ''; position: absolute; top: 2px; right: 2px; width: 11px; height: 11px;
            border-radius: 50%; background: #FFC857; border: 2px solid #fff;
        }
        .bn-dots { display: grid; grid-template-columns: 11px 11px; gap: 5px; }
        .bn-dots i { width: 11px; height: 11px; border-radius: 3px; background: #fff; display: block; }

        .bn-back {
            display: block; position: fixed; inset: 0; z-index: 1050; background: rgba(0,0,0,.35);
            opacity: 0; pointer-events: none; transition: opacity .2s;
        }
        .bn-back.open { opacity: 1; pointer-events: auto; }

        /* The sheet: a rounded card floating just above the bar. */
        .bn-sheet {
            display: block; position: fixed; z-index: 1099; left: 12px; right: 12px;
            bottom: calc(var(--bn-h) + 18px + max(10px, env(safe-area-inset-bottom)));
            max-height: calc(100vh - var(--bn-h) - 84px - env(safe-area-inset-bottom));
            max-height: calc(100dvh - var(--bn-h) - 84px - env(safe-area-inset-bottom));
            overflow-y: auto; overscroll-behavior: contain; -webkit-overflow-scrolling: touch;
            background: #fff; border-radius: 28px; padding: 10px 14px 16px;
            box-shadow: 0 10px 34px rgba(0,0,0,.22); font-family: 'Poppins', sans-serif;
            opacity: 0; transform: translateY(24px) scale(.97); pointer-events: none;
            transition: opacity .18s, transform .26s cubic-bezier(.3,1.3,.5,1);
        }
        .bn-sheet.open { opacity: 1; transform: none; pointer-events: auto; }
        .bn-handle { position: sticky; top: 0; z-index: 2; background: #fff; padding: 2px 0 8px; touch-action: none; }
        .bn-handle::before { content: ''; display: block; width: 38px; height: 4px; border-radius: 2px; background: #E0E0E0; margin: 0 auto; }
        .bn-handle { padding-bottom: 4px; }

        .bn-head { display: flex; align-items: center; justify-content: space-between; padding: 12px 4px 2px; }
        .bn-head strong { font-size: 15px; font-weight: 700; color: #1e293b; }
        .bn-exit {
            display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 14px;
            border: none; border-radius: 17px; background: #F5E8E8; color: #7B1D1D;
            font: 600 12px 'Poppins', sans-serif; cursor: pointer; -webkit-tap-highlight-color: transparent;
        }
        .bn-exit:active { background: #EBD6D6; }
        .bn-exit i { font-size: 13px; }

        .bn-label {
            font-size: 10px; font-weight: 600; letter-spacing: .12em; text-transform: uppercase;
            color: #A52020; margin: 10px 4px 8px; display: flex; align-items: center; gap: 8px;
        }
        .bn-label::after { content: ''; flex: 1; height: 1px; background: #F0E4E4; }
        .bn-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px 2px; }
        @media (max-width: 340px) { .bn-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (min-width: 520px) { .bn-grid { grid-template-columns: repeat(6, minmax(0, 1fr)); } }

        .bn-item {
            min-width: 0; display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 6px 2px;
            text-decoration: none; background: none; border: none; cursor: pointer; font-family: inherit;
            border-radius: 14px; -webkit-tap-highlight-color: transparent;
        }
        .bn-item:active { background: #F5E8E8; }
        .bn-ic {
            position: relative; width: 46px; height: 46px; border-radius: 14px; display: grid; place-items: center;
            background: #F5E8E8; color: #7B1D1D; font-size: 18px; transition: background .2s, color .2s;
        }
        .bn-item .bn-t { font-size: 10.5px; font-weight: 500; color: #4B5563; text-align: center; line-height: 1.25; overflow-wrap: anywhere; }
        .bn-item.on .bn-ic { background: var(--bn-grad); color: #fff; box-shadow: 0 4px 10px rgba(123,20,22,.30); }
        .bn-item.on .bn-t { color: #7B1D1D; font-weight: 600; }
        .bn-item.danger .bn-ic { background: #fde8e8; color: #e53e3e; }
        .bn-badge {
            position: absolute; top: -5px; right: -5px; min-width: 18px; height: 18px; padding: 0 4px; box-sizing: border-box;
            border-radius: 9px; background: #e8910b; color: #fff; font-size: 10px; font-weight: 700;
            display: grid; place-items: center; border: 2px solid #fff; line-height: 1;
        }
        .bn-lock { position: absolute; right: -3px; bottom: -3px; font-size: 9px; width: 16px; height: 16px; border-radius: 50%;
            background: #fff; color: #7B1D1D; display: grid; place-items: center; box-shadow: 0 1px 4px rgba(0,0,0,.2); }
    }


    @media (max-width: 768px) {
        /* Icons only: the current page is the maroon circle; hover / long-press shows its name. */
        .bn-group { justify-content: space-evenly; }
        .bottom-nav .bn-tab {
            position: relative; overflow: visible; flex: 0 0 46px; width: 46px; height: 46px; padding: 0;
            border-radius: 50%; gap: 0; justify-content: center;
        }
        .bottom-nav .bn-tab i { font-size: 19px; }
        .bottom-nav .bn-tab.active { flex: 0 0 46px; width: 46px; padding: 0; }
        .bottom-nav .bn-tab span, .bottom-nav .bn-tab.active span {
            position: absolute; width: 1px; height: 1px; max-width: 1px; margin: -1px; overflow: hidden;
            clip: rect(0 0 0 0); opacity: 0; white-space: nowrap;
        }
        .bottom-nav .bn-tab::after {
            content: attr(data-label); position: absolute; bottom: calc(100% + 16px); left: 50%;
            transform: translate(-50%, 6px); padding: 6px 12px; border-radius: 10px; white-space: nowrap;
            background: #2b1214; color: #fff; font: 600 11.5px 'Poppins', sans-serif; letter-spacing: .2px;
            box-shadow: 0 6px 16px rgba(0,0,0,.25); opacity: 0; pointer-events: none;
            transition: opacity .15s ease, transform .15s ease;
        }
        .bottom-nav .bn-tab::before {
            content: ''; position: absolute; bottom: calc(100% + 11px); left: 50%; width: 10px; height: 10px;
            background: #2b1214; transform: translate(-50%, 6px) rotate(45deg); border-radius: 2px;
            opacity: 0; pointer-events: none; transition: opacity .15s ease, transform .15s ease;
        }
        .bottom-nav .bn-tab:hover::after, .bottom-nav .bn-tab:focus-visible::after,
        .bottom-nav .bn-tab:active::after { opacity: 1; transform: translate(-50%, 0); }
        .bottom-nav .bn-tab:hover::before, .bottom-nav .bn-tab:focus-visible::before,
        .bottom-nav .bn-tab:active::before { opacity: 1; transform: translate(-50%, 0) rotate(45deg); }
    }

    /* Short landscape phones: shrink the bar so content keeps its room. */
    @media (max-width: 900px) and (max-height: 480px) and (orientation: landscape) {
        :root { --bn-h: 58px; }
        .bn-fab { flex-basis: 44px; width: 44px; height: 44px; transform: none; box-shadow: 0 4px 12px rgba(123,20,22,.42); }
        .bn-fab.open { transform: rotate(45deg); }
        .bottom-nav .bn-tab { height: 38px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .bottom-nav, .bn-sheet, .bn-back, .bn-fab { transition: none; }
    }
</style>

@include('partials.student-mobile')

<div class="bn-back" id="bnBack" onclick="closeBnSheet()"></div>

<div class="bn-sheet" id="bnSheet" role="dialog" aria-label="All pages" aria-hidden="true">
    <div class="bn-handle" id="bnHandle">
        <div class="bn-head">
            <strong>Menu</strong>
            <button type="button" class="bn-exit" onclick="closeBnSheet()" aria-label="Close menu"><i class="fas fa-xmark"></i><span>Exit</span></button>
        </div>
    </div>

    @foreach ($bnGroups as $bnTitle => $bnItems)
        <div class="bn-label">{{ $bnTitle }}</div>
        <div class="bn-grid">
            @foreach ($bnItems as $bnItem)
                @php
                    [$bnKey, $bnName, $bnIcon, $bnHref] = $bnItem;
                    $bnLocked = $bnItem[4] ?? false;
                    $bnCount  = $bnItem[5] ?? 0;
                @endphp
                <a href="{{ $bnHref }}" class="bn-item {{ $bnActive === $bnKey ? 'on' : '' }}">
                    <span class="bn-ic">
                        <i class="fas {{ $bnIcon }}"></i>
                        @if ($bnCount > 0)<b class="bn-badge">{{ $bnCount > 99 ? '99+' : $bnCount }}</b>@endif
                        @if ($bnLocked)<i class="fas fa-lock bn-lock" title="Locked for alumni"></i>@endif
                    </span>
                    <span class="bn-t">{{ $bnName }}</span>
                </a>
            @endforeach

            @if ($bnTitle === 'Account')
                <button type="button" class="bn-item danger" onclick="document.getElementById('bottomNavLogoutForm').requestSubmit();">
                    <span class="bn-ic"><i class="fas fa-sign-out-alt"></i></span>
                    <span class="bn-t">Logout</span>
                </button>
            @endif
        </div>
    @endforeach

    <form id="bottomNavLogoutForm" method="POST" action="{{ route('logout') }}" style="display:none;"
          data-confirm="You will be signed out of CPACE and returned to the login page."
          data-confirm-title="Log out of CPACE?"
          data-confirm-ok="Yes, log me out"
          data-confirm-icon="question">@csrf</form>
</div>

<nav class="bottom-nav" id="bottomNav" aria-label="Main">
    <div class="bottom-nav-card">
        <div class="bn-group">
            <a href="{{ route('dashboard') }}" class="bn-tab {{ $bnActive === 'dashboard' ? 'active' : '' }}" data-label="Home" title="Home" aria-label="Home">
            <i class="fas fa-home"></i><span>Home</span>
        </a>
            <a href="{{ route('adaptive-quizzes') }}" class="bn-tab {{ $bnActive === 'quizzes' ? 'active' : '' }}" data-label="Quizzes" title="Quizzes" aria-label="Quizzes">
            <i class="fas fa-pen-fancy"></i><span>Quizzes</span>
        </a>
        </div>
        <button type="button" class="bn-fab {{ $bnInSheet ? 'has-active' : '' }}" id="bnFab"
                aria-label="All pages" aria-expanded="false" aria-controls="bnSheet" onclick="toggleBnSheet()">
            <span class="bn-dots"><i></i><i></i><i></i><i></i></span>
        </button>
        <div class="bn-group">
            <a href="{{ route('performance') }}" class="bn-tab {{ $bnActive === 'performance' ? 'active' : '' }}" data-label="Performance" title="Performance" aria-label="Performance">
            <i class="fas fa-chart-bar"></i><span>Performance</span>
        </a>
            <a href="{{ route('calendar') }}" class="bn-tab {{ $bnActive === 'calendar' ? 'active' : '' }}" data-label="Calendar" title="Calendar" aria-label="Calendar">
            <i class="fas fa-calendar-alt"></i><span>Calendar</span>
        </a>
        </div>
    </div>
</nav>

<script>
(function () {
    const sheet = document.getElementById('bnSheet');
    const back  = document.getElementById('bnBack');
    const fab   = document.getElementById('bnFab');
    const bar   = document.getElementById('bottomNav');
    const handle = document.getElementById('bnHandle');

    function setOpen(open) {
        sheet.classList.toggle('open', open);
        back.classList.toggle('open', open);
        fab.classList.toggle('open', open);
        fab.setAttribute('aria-expanded', open);
        sheet.setAttribute('aria-hidden', !open);
        // Lock page scroll behind the sheet, restore it afterwards.
        document.documentElement.style.overflow = open ? 'hidden' : '';
        if (open) {
            sheet.scrollTop = 0;
            // Bring the current page's tile into view if the sheet scrolls.
            const on = sheet.querySelector('.bn-item.on');
            if (on) on.scrollIntoView({ block: 'nearest' });
        }
    }
    window.toggleBnSheet = () => setOpen(!sheet.classList.contains('open'));
    window.closeBnSheet  = () => setOpen(false);

    // Esc closes; so does leaving the phone breakpoint (rotate / resize).
    document.addEventListener('keydown', e => { if (e.key === 'Escape') setOpen(false); });
    window.addEventListener('resize', () => { if (window.innerWidth > 768) setOpen(false); });
    sheet.querySelectorAll('a').forEach(a => a.addEventListener('click', () => setOpen(false)));

    // Swipe down on the handle (or from the top of the sheet) to dismiss.
    let startY = null;
    const begin = e => { if (sheet.scrollTop <= 0) startY = e.touches[0].clientY; };
    const end   = e => {
        if (startY !== null && e.changedTouches[0].clientY - startY > 60) setOpen(false);
        startY = null;
    };
    handle.addEventListener('touchstart', begin, { passive: true });
    handle.addEventListener('touchend', end, { passive: true });
    sheet.addEventListener('touchstart', begin, { passive: true });
    sheet.addEventListener('touchend', end, { passive: true });

    // Tuck the bar away while a text field has focus (on-screen keyboard).
    const typing = el => el && /^(INPUT|TEXTAREA|SELECT)$/.test(el.tagName)
        && !/^(button|submit|checkbox|radio|range|file|color)$/i.test(el.type || '');
    document.addEventListener('focusin',  e => { if (typing(e.target)) bar.classList.add('bn-hidden'); });
    document.addEventListener('focusout', () => setTimeout(() => {
        if (!typing(document.activeElement)) bar.classList.remove('bn-hidden');
    }, 50));
})();
</script>
