{{-- Shared topbar: search + avatar dropdown. Include inside .topbar-right --}}
<a href="{{ route('notifications.index') }}" aria-label="Notifications" title="Notifications" style="position:relative;width:38px;height:38px;border-radius:10px;background:#fff;color:var(--primary);border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;text-decoration:none;flex-shrink:0;">
    <i class="fas fa-bell"></i>
    @if($unreadNotifications > 0)
        <span style="position:absolute;top:-5px;right:-5px;min-width:17px;height:17px;padding:0 4px;border-radius:9px;background:#dc2626;color:#fff;border:2px solid #fff;font-size:8px;font-weight:700;display:flex;align-items:center;justify-content:center;">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
    @endif
</a>
<div class="topbar-search">
    <i class="fas fa-search"></i>
    <input type="text" placeholder="Search...">
</div>
<div class="topbar-avatar-wrap" id="topbarAvatarWrap">
    <button class="topbar-avatar-btn" id="topbarAvatarBtn">
        @include('partials.avatar-content')
    </button>
    <div class="topbar-dropdown" id="topbarDropdown">
        <a href="#"><i class="fas fa-user"></i> Profile Settings</a>
        <a href="#"><i class="fas fa-question-circle"></i> Help &amp; Support</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0;padding:0;">
            @csrf
            <button type="submit" class="tda-logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
    </div>
</div>
