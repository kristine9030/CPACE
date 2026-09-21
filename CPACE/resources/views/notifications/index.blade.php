<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--primary:#7B1D1D;--primary-hover:#641717;--primary-light:#f5e8e8;--accent:#c0392b;--fb-blue:#0866ff;--fb-blue-light:#e7f3ff}
        *{box-sizing:border-box} body{margin:0;font-family:'Poppins',sans-serif;background:#f4f5f7;color:#333}
        .notification-main{margin-left:230px;padding:24px 30px 40px;transition:margin-left .28s}.sidebar.collapsed~.notification-main{margin-left:68px}
        .notification-wrap{max-width:700px;margin:0 auto}

        /* ── Global page topbar (title + search + notif + messages + profile) ── */
        .top-bar { display:flex; justify-content:space-between; align-items:center; gap:20px; margin-bottom:22px; flex-wrap:wrap; }
        .page-title {
            font-family:'Montserrat',sans-serif; font-size:28px; font-weight:700; color:#14283E;
            margin-bottom:6px; padding-bottom:8px; position:relative;
        }
        .page-title::after {
            content:''; position:absolute; left:0; bottom:0;
            width:40px; height:4px; border-radius:2px;
            background:linear-gradient(90deg, #c0392b, #7B1D1D);
        }
        .page-subtitle { font-size:14px; color:#999; }
        .top-bar-right { display:flex; align-items:center; gap:14px; }

        .search-wrap { position:relative; }
        .search-wrap i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#aaa; font-size:15px; }
        .search-wrap input {
            width:280px; padding:11px 16px 11px 40px;
            border:1px solid #e0e0e0; border-radius:24px;
            font-size:14px; font-family:'Poppins',sans-serif;
            background:#fff; color:#555; outline:none;
        }
        .search-wrap input:focus { border-color:var(--primary); }
        .search-wrap input::placeholder { color:#bbb; }

        .notif-btn {
            position:relative; width:44px; height:44px;
            border:none; background:#fff; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:18px; color:#555; cursor:pointer; text-decoration:none;
            box-shadow:0 1px 4px rgba(0,0,0,0.08); flex-shrink:0;
        }
        .notif-btn:hover { background:#f0f0f0; }
        .badge {
            position:absolute; top:-3px; right:-3px;
            width:19px; height:19px; background:var(--accent);
            color:#fff; border-radius:50%; font-size:10.5px; font-weight:700;
            display:flex; align-items:center; justify-content:center;
        }
        .profile-avatar {
            width:44px; height:44px; background:var(--primary);
            border-radius:11px; border:none; color:#fff;
            font-weight:700; font-size:15px; cursor:pointer;
            font-family:'Poppins',sans-serif; transition:background 0.2s;
            overflow:hidden;
        }
        .profile-avatar:hover { background:var(--primary-hover); }
        .profile-avatar img { width:100%; height:100%; object-fit:cover; }

        .header-dropdown-wrap { position:relative; }
        .dropdown-menu {
            position:absolute; top:calc(100% + 8px); right:0;
            background:#fff; border:1px solid #e5e7eb; border-radius:10px;
            min-width:190px; box-shadow:0 6px 20px rgba(0,0,0,0.12);
            display:none; z-index:2000;
        }
        .dropdown-menu.active { display:block; }
        .dropdown-menu a, .dropdown-menu button {
            display:flex; align-items:center; gap:10px;
            padding:12px 16px; font-size:13.5px; font-family:'Poppins',sans-serif;
            text-decoration:none; color:#333; background:none; border:none;
            width:100%; text-align:left; cursor:pointer; transition:background 0.2s;
            border-bottom:1px solid #f5f5f5;
        }
        .dropdown-menu a:last-child, .dropdown-menu form:last-child button { border-bottom:none; }
        .dropdown-menu a:hover, .dropdown-menu button:hover { background:#f9f9f9; }
        .dropdown-menu a i, .dropdown-menu button i { color:var(--primary); width:16px; text-align:center; }
        .dropdown-menu .logout-btn { color:#e53e3e; }
        .dropdown-menu .logout-btn i { color:#e53e3e; }

        .read-all{border:none;background:none;color:var(--fb-blue);padding:9px 12px;border-radius:8px;font:600 13.5px 'Poppins';cursor:pointer}
        .read-all:hover{background:#f0f2f5}

        .notification-card{background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,.04)}

        .notification-row-form{margin:0}
        .notification-row{
            display:flex; align-items:flex-start; gap:14px;
            width:100%; text-align:left; border:none; background:none; cursor:pointer;
            padding:16px 20px; font-family:'Poppins',sans-serif;
            border-bottom:1px solid #f1f1f1; transition:background .12s;
            position:relative;
        }
        .notification-row:last-child{border-bottom:none}
        .notification-row:hover{background:#f2f2f2}
        .notification-row.unread{background:var(--fb-blue-light)}
        .notification-row.unread:hover{background:#daedff}
        .notification-row.static{cursor:default}

        .n-avatar-wrap{position:relative;flex-shrink:0}
        .n-avatar{
            width:58px;height:58px;border-radius:50%;
            background:var(--primary);color:#fff;
            display:flex;align-items:center;justify-content:center;
            font-weight:700;font-size:19px;overflow:hidden;position:relative;
        }
        .n-avatar img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
        .n-badge{
            position:absolute;bottom:-3px;right:-3px;
            width:24px;height:24px;border-radius:50%;
            display:flex;align-items:center;justify-content:center;
            color:#fff;font-size:11.5px;border:2.5px solid #fff;
            background:var(--fb-blue);
        }
        .notification-row[data-priority="urgent"] .n-badge{background:#e02849}
        .notification-row[data-priority="high"] .n-badge{background:#f7b928}
        .notification-row[data-priority="normal"] .n-badge{background:var(--fb-blue)}

        .n-body{flex:1;min-width:0;padding-top:2px}
        .n-text{font-size:14.5px;line-height:1.45;color:#050505}
        .n-text strong{font-weight:700}
        .n-message{font-size:13.5px;color:#65676b;margin-top:3px;line-height:1.45;white-space:pre-line;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
        .n-meta{font-size:12.5px;color:var(--fb-blue);margin-top:7px;font-weight:600}
        .notification-row:not(.unread) .n-meta{color:#65676b;font-weight:500}

        .n-dot{width:12px;height:12px;border-radius:50%;background:var(--fb-blue);flex-shrink:0;margin-top:8px}

        .empty{padding:70px 20px;text-align:center;color:#aaa;font-size:13px}.empty i{display:block;font-size:38px;color:#ddd;margin-bottom:14px}
        .pager{display:flex;justify-content:center;gap:8px;margin-top:18px}.pager a,.pager span{padding:8px 13px;border-radius:7px;border:1px solid #ddd;background:#fff;color:#555;text-decoration:none;font-size:11.5px}.pager span{color:#aaa}
        .status{background:#d1fae5;color:#047857;padding:12px 16px;border-radius:9px;font-size:12.5px;margin-bottom:16px}

        @media(max-width:900px){.notification-main{margin-left:68px}}
        @media(max-width:768px){.notification-main{margin-left:0!important;padding:80px 14px 90px}.top-bar{flex-direction:column;align-items:flex-start;gap:12px}.top-bar-right{width:100%;flex-wrap:wrap}.search-wrap{flex:1}.search-wrap input{width:100%}.page-title{font-size:22px}}
    </style>
</head>
<body>
@if(Auth::user()->isChair())
    @include('partials.chair-sidebar', ['active' => 'notifications'])
@elseif(Auth::user()->isFaculty())
    @include('partials.faculty-sidebar', ['active' => 'notifications'])
@elseif(Auth::user()->isAlumni())
    @include('partials.alumni-sidebar', ['active' => 'notifications'])
@else
    @include('partials.sidebar', ['active' => 'notifications'])
    @include('partials.student-bottom-nav', ['active' => 'more'])
    @include('partials.student-mobile-header')
@endif

<main class="notification-main">
    <div class="top-bar">
        <div>
            <div class="page-title">Notifications</div>
            <div class="page-subtitle">Announcements, reminders, and updates sent to your account.</div>
        </div>
        <div class="top-bar-right">
            <div class="search-wrap gs-wrap">
                <i class="fas fa-search"></i>
                <input type="text" data-gs="true" placeholder="Search topics, questions...">
            </div>
            <a class="notif-btn" href="{{ route('messages.index') }}" title="Messages" aria-label="Messages">
                <i class="fas fa-comment-dots"></i>
                @if($unreadMessages > 0)<span class="badge">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>@endif
            </a>
            <div class="header-dropdown-wrap">
                <button class="profile-avatar" id="topbarProfileBtn">@include('partials.avatar-content')</button>
                <div class="dropdown-menu" id="topbarProfileDropdown">
                    @if(Auth::user()->isFaculty())
                        <a href="{{ route('faculty.settings') }}"><i class="fas fa-user"></i> Profile Settings</a>
                    @elseif(Auth::user()->isAlumni())
                        <a href="{{ route('alumni.profile') }}"><i class="fas fa-user"></i> Profile Settings</a>
                    @elseif(!Auth::user()->isChair())
                        <a href="#" class="js-open-profile-modal"><i class="fas fa-user"></i> Profile Settings</a>
                    @endif
                    <a href="#"><i class="fas fa-question-circle"></i> Help &amp; Support</a>
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
<div class="notification-wrap">
    @if($unreadCount > 0)
        <div style="display:flex;justify-content:flex-end;margin-bottom:10px;">
            <form method="POST" action="{{ route('notifications.read-all') }}"
                data-confirm="All {{ $unreadCount }} unread notification{{ $unreadCount === 1 ? '' : 's' }} will be marked as read. This cannot be undone."
                data-confirm-title="Mark everything as read?"
                data-confirm-ok="Yes, mark all read"
                data-confirm-icon="question">@csrf<button class="read-all" type="submit"><i class="fas fa-check-double"></i> Mark all as read</button></form>
        </div>
    @endif
    {{-- Status messages surface as SweetAlert toasts via partials.alerts --}}
    <section class="notification-card">
        @forelse($notifications as $notification)
            @php
                $avatarColors = [
                    'maroon' => '#7B1D1D', 'crimson' => '#c0392b', 'blue' => '#2563eb',
                    'teal' => '#0d9488', 'green' => '#059669', 'purple' => '#7c3aed',
                    'pink' => '#db2777', 'orange' => '#d97706', 'navy' => '#1e3a5f', 'slate' => '#475569',
                ];
                $sender = trim(($notification->sender_first_name ?? '').' '.($notification->sender_last_name ?? '')) ?: null;
                $senderBg = $avatarColors[$notification->sender_avatar_color ?? ''] ?? $avatarColors['maroon'];
                $badgeIcon = $notification->type === 'urgent' ? 'fa-exclamation'
                    : ($notification->type === 'high' ? 'fa-bullhorn' : 'fa-bell');
                $interactive = ! $notification->is_read || $notification->link;
            @endphp
            @if($interactive)
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="notification-row-form">
                    @csrf
                    <button type="submit" class="notification-row {{ $notification->is_read ? '' : 'unread' }}" data-priority="{{ $notification->type }}">
                        <div class="n-avatar-wrap">
                            <div class="n-avatar">
                                @if($notification->sender_photo)
                                    <img src="{{ asset('storage/' . $notification->sender_photo) }}" alt="">
                                @elseif($sender)
                                    <span style="background:{{ $senderBg }};width:100%;height:100%;display:flex;align-items:center;justify-content:center;">{{ strtoupper(substr($notification->sender_first_name,0,1)) }}{{ strtoupper(substr($notification->sender_last_name,0,1)) }}</span>
                                @else
                                    <i class="fas fa-graduation-cap"></i>
                                @endif
                            </div>
                            <div class="n-badge"><i class="fas {{ $badgeIcon }}"></i></div>
                        </div>
                        <div class="n-body">
                            <div class="n-text"><strong>{{ $notification->title }}</strong></div>
                            <div class="n-message">{{ $notification->message }}</div>
                            <div class="n-meta">{{ \Illuminate\Support\Carbon::parse($notification->created_at)->diffForHumans() }}@if($sender) · {{ $sender }} @endif</div>
                        </div>
                        @if(!$notification->is_read)<div class="n-dot" title="Unread"></div>@endif
                    </button>
                </form>
            @else
                <div class="notification-row static" data-priority="{{ $notification->type }}">
                    <div class="n-avatar-wrap">
                        <div class="n-avatar">
                            @if($notification->sender_photo)
                                <img src="{{ asset('storage/' . $notification->sender_photo) }}" alt="">
                            @elseif($sender)
                                <span style="background:{{ $senderBg }};width:100%;height:100%;display:flex;align-items:center;justify-content:center;">{{ strtoupper(substr($notification->sender_first_name,0,1)) }}{{ strtoupper(substr($notification->sender_last_name,0,1)) }}</span>
                            @else
                                <i class="fas fa-graduation-cap"></i>
                            @endif
                        </div>
                        <div class="n-badge"><i class="fas {{ $badgeIcon }}"></i></div>
                    </div>
                    <div class="n-body">
                        <div class="n-text"><strong>{{ $notification->title }}</strong></div>
                        <div class="n-message">{{ $notification->message }}</div>
                        <div class="n-meta">{{ \Illuminate\Support\Carbon::parse($notification->created_at)->diffForHumans() }}@if($sender) · {{ $sender }} @endif</div>
                    </div>
                </div>
            @endif
        @empty
            <div class="empty"><i class="far fa-bell"></i>Your notification inbox is empty.</div>
        @endforelse
    </section>
    @if($notifications->hasPages())<div class="pager">@if($notifications->onFirstPage())<span>Previous</span>@else<a href="{{ $notifications->previousPageUrl() }}">Previous</a>@endif <span>Page {{ $notifications->currentPage() }} of {{ $notifications->lastPage() }}</span> @if($notifications->hasMorePages())<a href="{{ $notifications->nextPageUrl() }}">Next</a>@else<span>Next</span>@endif</div>@endif
</div>
</main>

    @include('partials.global-search')
    @include('partials.alerts')
<script>
(function () {
    const btn = document.getElementById('topbarProfileBtn');
    const drop = document.getElementById('topbarProfileDropdown');
    if (btn && drop) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            drop.classList.toggle('active');
        });
        document.addEventListener('click', function () { drop.classList.remove('active'); });
        drop.addEventListener('click', function (e) { e.stopPropagation(); });
    }
})();
</script>
</body>
</html>
