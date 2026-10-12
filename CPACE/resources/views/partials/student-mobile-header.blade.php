{{--
    Mobile-only top header bar (app style).
    Shown at ≤ 768px. Includes greeting, avatar, notification bell.
--}}
@php
    $mhName   = Auth::user()->name ?? '';
    $mhFirst  = explode(' ', trim($mhName))[0];
    $mhParts  = explode(' ', trim($mhName));
    $mhInit   = strtoupper(substr($mhParts[0], 0, 1)) . (count($mhParts) > 1 ? strtoupper(substr(end($mhParts), 0, 1)) : '');
    $mhNotifs = $unreadNotifications ?? 0;
    $mhMsgs   = $unreadMessages ?? 0;

    $mhHour = (int) now()->format('H');
    $mhGreet = $mhHour < 12 ? 'Good morning' : ($mhHour < 18 ? 'Good afternoon' : 'Good evening');
@endphp
@include('partials.student-back')
@php $backUrl = cpaceBackUrl(); @endphp

<style>
/* ── Mobile App Header ── */
.mobile-app-header {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0;
    height: 68px;
    background: linear-gradient(135deg, #6b1515 0%, #9b2b2b 60%, #c0392b 100%);
    z-index: 1060;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    box-shadow: 0 3px 16px rgba(0,0,0,0.18);
}

.mh-left   { display: flex; align-items: center; gap: 12px; }

.mh-avatar {
    width: 42px; height: 42px;
    background: rgba(255,255,255,0.2);
    border: 2px solid rgba(255,255,255,0.45);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 14px; color: #fff;
    font-family: 'Poppins', sans-serif;
    flex-shrink: 0;
}

.mh-greeting {
    font-size: 14px; font-weight: 600; color: #fff;
    font-family: 'Poppins', sans-serif;
    line-height: 1.25;
}

.mh-sub {
    font-size: 11px;
    color: rgba(255,255,255,0.65);
    font-family: 'Poppins', sans-serif;
}

.mh-back {
    width: 40px; height: 40px; flex-shrink: 0; border-radius: 50%;
    background: rgba(255,255,255,0.18); border: 1.5px solid rgba(255,255,255,0.4);
    color: #fff; font-size: 16px; text-decoration: none;
    display: flex; align-items: center; justify-content: center;
    -webkit-tap-highlight-color: transparent;
}
.mh-back:active { background: rgba(255,255,255,0.3); }
.mh-profile-btn { padding: 0; cursor: pointer; overflow: hidden; -webkit-tap-highlight-color: transparent; }
.mh-profile-btn img, .mh-profile-btn .avatar-default {
    width: 100%; height: 100%; border-radius: 50%; object-fit: cover;
    display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 14px;
}
.mh-greet { min-width: 0; }
.mh-greet .mh-greeting, .mh-greet .mh-sub { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.mh-greet.sub { display: none; }
.mh-left { min-width: 0; flex: 1 1 auto; }
.mh-right { flex: 0 0 auto; gap: 8px; }
.mh-right .mh-bell { width: 38px; height: 38px; font-size: 15px; flex: 0 0 auto; }
.mh-right .mh-avatar { width: 38px; height: 38px; }
.mh-search {
    display: none; position: fixed; left: 0; right: 0; top: 68px; z-index: 1065; align-items: center; gap: 10px;
    padding: 10px 14px; background: #fff; box-shadow: 0 10px 24px rgba(0,0,0,.16); font-family: 'Poppins', sans-serif;
}
.mh-search.open { display: flex; }
.mh-search > i { color: #9aa0aa; font-size: 15px; }
.mh-search input { flex: 1; min-width: 0; border: none; outline: none; font: 400 16px 'Poppins', sans-serif; color: #1e293b; background: transparent; padding: 8px 0; }
.mh-search button { width: 36px; height: 36px; border: none; border-radius: 50%; background: #F5E8E8; color: #7B1D1D; font-size: 14px; cursor: pointer; }
.mh-menu-back { display: none; position: fixed; inset: 0; z-index: 1070; background: rgba(0,0,0,.28); opacity: 0; pointer-events: none; transition: opacity .18s; }
.mh-menu-back.open { opacity: 1; pointer-events: auto; }
.mh-menu {
    display: none; position: fixed; top: 74px; z-index: 1080; width: min(300px, calc(100vw - 24px));
    background: #fff; border-radius: 20px; padding: 8px; font-family: 'Poppins', sans-serif;
    box-shadow: 0 14px 40px rgba(0,0,0,.25); opacity: 0; transform: translateY(-8px) scale(.97); transform-origin: top left;
    transition: opacity .18s, transform .22s cubic-bezier(.3,1.3,.5,1); pointer-events: none;
}
.mh-menu.left  { left: 12px; }
.mh-menu.right { right: 12px; transform-origin: top right; }
.mh-menu.open  { opacity: 1; transform: none; pointer-events: auto; }
.mh-menu-user { display: flex; align-items: center; gap: 12px; padding: 12px 12px 14px; border-bottom: 1px solid #F0E4E4; margin-bottom: 6px; }
.mh-menu-ava { width: 46px; height: 46px; flex: 0 0 auto; border-radius: 50%; overflow: hidden; display: block; border: 2px solid #E8CFCF; }
.mh-menu-ava img, .mh-menu-ava .avatar-default { width: 100%; height: 100%; object-fit: cover; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 15px; }
.mh-menu-user strong { display: block; font-size: 14px; color: #1e293b; line-height: 1.25; }
.mh-menu-user small { display: block; font-size: 11.5px; color: #64748b; word-break: break-all; margin-top: 2px; }
.mh-menu a, .mh-menu > button {
    display: flex; align-items: center; gap: 12px; width: 100%; padding: 13px 12px; border: none; background: none;
    border-radius: 12px; font: 500 14px 'Poppins', sans-serif; color: #1e293b; text-decoration: none; cursor: pointer; text-align: left;
    -webkit-tap-highlight-color: transparent;
}
.mh-menu a i, .mh-menu > button i { width: 20px; text-align: center; color: #7B1D1D; font-size: 15px; }
.mh-menu a:active, .mh-menu > button:active { background: #F5E8E8; }
.mh-menu .mh-logout { color: #c0392b; margin-top: 4px; border-top: 1px solid #F0E4E4; border-radius: 0 0 12px 12px; }
.mh-menu .mh-logout i { color: #c0392b; }
.mh-right { display: flex; align-items: center; gap: 10px; }

.mh-bell {
    position: relative;
    width: 40px; height: 40px;
    background: rgba(255,255,255,0.15);
    border: none;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 17px;
    cursor: pointer;
    transition: background 0.2s;
}
.mh-bell:hover { background: rgba(255,255,255,0.25); }

.mh-badge {
    position: absolute;
    top: -1px; right: -1px;
    min-width: 17px; height: 17px;
    padding: 0 3px;
    background: #ff4444;
    border-radius: 9px;
    font-size: 9px; font-weight: 700;
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid #9b2b2b;
    font-family: 'Poppins', sans-serif;
}

/* ── Hide desktop page header on mobile ── */
@media (max-width: 768px) {
    .mobile-app-header { display: flex; padding: 0 14px; }
    .mh-menu, .mh-menu-back { display: block; }
    .page-header       { display: none !important; }

    /* welcome banner — hide illustration, compact padding */
    .welcome-illustration { display: none !important; }
    .welcome-banner {
        padding: 20px 20px !important;
        border-radius: 14px !important;
    }
    .welcome-banner h2 { font-size: 20px !important; }

    /* metrics 2-up on mobile */
    .metrics-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 12px !important; }

    /* content stacks to single column */
    .content-grid  { grid-template-columns: minmax(0, 1fr) !important; }
    .bottom-grid   { grid-template-columns: minmax(0, 1fr) !important; }
    .content-grid .right-panel { grid-column: 1 !important; }

    /* search bar hidden — no space for it on mobile */
    .search-wrap   { display: none !important; }
}
</style>

<header class="mobile-app-header" id="mobileAppHeader">
    <div class="mh-left">
        @if ($backUrl)
            <a href="{{ $backUrl }}" class="mh-back" onclick="return cpaceBack(this)" aria-label="Go back"><i class="fas fa-arrow-left"></i></a>
        @else
            <button type="button" class="mh-avatar mh-profile-btn" aria-label="Profile menu" aria-haspopup="menu" aria-expanded="false">@include('partials.avatar-content')</button>
        @endif
        <div class="mh-greet {{ $backUrl ? 'sub' : '' }}">
            <div class="mh-greeting">{{ $mhGreet }}, {{ $mhFirst }}!</div>
            <div class="mh-sub">Welcome back</div>
        </div>
    </div>
    <div class="mh-right">
        <button type="button" class="mh-bell" id="mhSearchBtn" aria-label="Search" aria-expanded="false"><i class="fas fa-search"></i></button>
        <a class="mh-bell" href="{{ route('messages.index') }}" aria-label="Messages" style="text-decoration:none">
            <i class="fas fa-comment-dots"></i>
            @if($mhMsgs > 0)
                <span class="mh-badge">{{ $mhMsgs > 9 ? '9+' : $mhMsgs }}</span>
            @endif
        </a>
        <a class="mh-bell" href="{{ route('notifications.index') }}" aria-label="Notifications" style="text-decoration:none">
            <i class="fas fa-bell"></i>
            @if($mhNotifs > 0)
                <span class="mh-badge">{{ $mhNotifs > 9 ? '9+' : $mhNotifs }}</span>
            @endif
        </a>
        @if ($backUrl)
            <button type="button" class="mh-avatar mh-profile-btn" aria-label="Profile menu" aria-haspopup="menu" aria-expanded="false">@include('partials.avatar-content')</button>
        @endif
    </div>
</header>

{{-- Search: one field for the whole app, opened from the header. --}}
<div class="mh-search" id="mhSearch">
    <i class="fas fa-search"></i>
    <input type="text" data-gs="true" id="mhSearchInput" placeholder="Search topics, questions, notes..." autocomplete="off" enterkeyhint="search">
    <button type="button" id="mhSearchClose" aria-label="Close search"><i class="fas fa-xmark"></i></button>
</div>

{{-- Profile menu: opens from the avatar on every student page. --}}
<div class="mh-menu-back" id="mhMenuBack"></div>
<div class="mh-menu {{ $backUrl ? 'right' : 'left' }}" id="mhMenu" role="menu" aria-hidden="true">
    <div class="mh-menu-user">
        <span class="mh-menu-ava">@include('partials.avatar-content')</span>
        <div>
            <strong>{{ $mhName }}</strong>
            <small>{{ Auth::user()->email ?? '' }}</small>
        </div>
    </div>
    <button type="button" class="js-open-profile-modal" role="menuitem"><i class="fas fa-user"></i> Profile Settings</button>
    <a href="{{ route('settings') }}" role="menuitem"><i class="fas fa-cog"></i> Settings</a>
    <a href="{{ route('help.index') }}" role="menuitem"><i class="fas fa-circle-question"></i> Help &amp; Support</a>
    <button type="button" class="mh-logout" role="menuitem"
            onclick="document.getElementById('mhLogoutForm').requestSubmit();"><i class="fas fa-sign-out-alt"></i> Logout</button>
    <form id="mhLogoutForm" method="POST" action="{{ route('logout') }}" style="display:none;"
          data-confirm="You will be signed out of CPACE and returned to the login page."
          data-confirm-title="Log out of CPACE?"
          data-confirm-ok="Yes, log me out"
          data-confirm-icon="question">@csrf</form>
</div>

<script>
(function () {
    const menu = document.getElementById('mhMenu'), back = document.getElementById('mhMenuBack');
    const btns = document.querySelectorAll('.mh-profile-btn');
    function set(open) {
        menu.classList.toggle('open', open);
        back.classList.toggle('open', open);
        menu.setAttribute('aria-hidden', !open);
        btns.forEach(b => b.setAttribute('aria-expanded', open));
    }
    btns.forEach(b => b.addEventListener('click', () => set(!menu.classList.contains('open'))));
    back.addEventListener('click', () => set(false));
    menu.querySelectorAll('a, .js-open-profile-modal').forEach(el => el.addEventListener('click', () => set(false)));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') set(false); });

    // Search bar
    const sBar = document.getElementById('mhSearch'), sBtn = document.getElementById('mhSearchBtn'), sIn = document.getElementById('mhSearchInput');
    function setSearch(open) {
        sBar.classList.toggle('open', open);
        sBtn.setAttribute('aria-expanded', open);
        if (open) { set(false); sIn.focus(); } else { sIn.value = ''; sIn.dispatchEvent(new Event('input')); sIn.blur(); }
    }
    sBtn.addEventListener('click', () => setSearch(!sBar.classList.contains('open')));
    document.getElementById('mhSearchClose').addEventListener('click', () => setSearch(false));
    sIn.addEventListener('keydown', e => { if (e.key === 'Escape') setSearch(false); });
})();
</script>
