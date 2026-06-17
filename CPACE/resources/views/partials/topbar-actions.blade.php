{{-- Shared topbar: search + avatar dropdown. Include inside .topbar-right --}}
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
