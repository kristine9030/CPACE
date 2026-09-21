{{--
    The student top bar (search, messages, notifications, profile dropdown),
    lifted out of the individual student pages so the Mock Exam screens carry
    the same chrome without copying ~50 lines into each one.

    Expects: $title, $subtitle. $back is optional (a ['url' =>, 'label' =>] pair).
--}}
<div class="topbar">
    <div>
        @isset($back)
            <a href="{{ $back['url'] }}" style="font-size:12px;color:var(--muted);text-decoration:none;">
                <i class="fas fa-arrow-left"></i> {{ $back['label'] }}
            </a>
        @endisset
        <div class="page-title" @isset($back) style="margin-top:4px;" @endisset>{{ $title }}</div>
        <div class="page-sub">{{ $subtitle }}</div>
    </div>
    <div class="topbar-right">
        <button class="icon-btn" onclick="window.location.href='{{ route('messages.index') }}'" title="Messages" aria-label="Messages"
                style="background:#fff;border:1px solid var(--line);border-radius:9px;width:38px;height:38px;cursor:pointer;position:relative;">
            <i class="fas fa-comment-dots"></i>
            @if(($unreadMessages ?? 0) > 0)<span class="badge">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>@endif
        </button>
        <button class="icon-btn" onclick="window.location.href='{{ route('notifications.index') }}'" title="Notifications" aria-label="Notifications"
                style="background:#fff;border:1px solid var(--line);border-radius:9px;width:38px;height:38px;cursor:pointer;position:relative;">
            <i class="fas fa-bell"></i>
            @if(($unreadNotifications ?? 0) > 0)<span class="badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>@endif
        </button>
        <div class="header-dropdown-wrap" style="position:relative;">
            <button class="profile-avatar" id="profileBtn">@include('partials.avatar-content')</button>
            <div class="dropdown-menu" id="profileDropdown">
                <a href="#" class="js-open-profile-modal"><i class="fas fa-user"></i> Profile Settings</a>
                <a href="{{ route('performance') }}"><i class="fas fa-chart-line"></i> My Progress</a>
                <form method="POST" action="{{ route('logout') }}"
                      data-confirm="You will be signed out of CPACE and returned to the login page."
                      data-confirm-title="Log out of CPACE?"
                      data-confirm-ok="Yes, log me out"
                      data-confirm-icon="question" style="margin:0;padding:0;">
                    @csrf
                    <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>
