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

    $mhHour = (int) now()->format('H');
    $mhGreet = $mhHour < 12 ? 'Good morning' : ($mhHour < 18 ? 'Good afternoon' : 'Good evening');
@endphp

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
    .mobile-app-header { display: flex; }
    .page-header       { display: none !important; }

    /* welcome banner — hide illustration, compact padding */
    .welcome-illustration { display: none !important; }
    .welcome-banner {
        padding: 20px 20px !important;
        border-radius: 14px !important;
    }
    .welcome-banner h2 { font-size: 20px !important; }

    /* metrics 2-up on mobile */
    .metrics-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 12px !important; }

    /* content stacks to single column */
    .content-grid  { grid-template-columns: 1fr !important; }
    .bottom-grid   { grid-template-columns: 1fr !important; }
    .content-grid .right-panel { grid-column: 1 !important; }

    /* search bar hidden — no space for it on mobile */
    .search-wrap   { display: none !important; }
}
</style>

<header class="mobile-app-header" id="mobileAppHeader">
    <div class="mh-left">
        <div class="mh-avatar">{{ $mhInit }}</div>
        <div>
            <div class="mh-greeting">{{ $mhGreet }}, {{ $mhFirst }}!</div>
            <div class="mh-sub">Welcome back</div>
        </div>
    </div>
    <div class="mh-right">
        <button class="mh-bell" type="button">
            <i class="fas fa-bell"></i>
            @if($mhNotifs > 0)
                <span class="mh-badge">{{ $mhNotifs > 9 ? '9+' : $mhNotifs }}</span>
            @endif
        </button>
    </div>
</header>
