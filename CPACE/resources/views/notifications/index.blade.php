<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--primary:#7B1D1D;--primary-hover:#641717;--primary-light:#f5e8e8}
        *{box-sizing:border-box} body{margin:0;font-family:'Poppins',sans-serif;background:#f4f5f7;color:#333}
        .notification-main{margin-left:230px;padding:26px 30px;transition:margin-left .28s}.sidebar.collapsed~.notification-main{margin-left:68px}
        .notification-head{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:22px}.notification-title{font-size:25px;font-weight:700;color:#1a1a1a}.notification-sub{font-size:11px;color:#999;margin-top:2px}
        .read-all{border:1px solid var(--primary);background:#fff;color:var(--primary);padding:9px 14px;border-radius:8px;font:600 11px 'Poppins';cursor:pointer}.read-all:hover{background:var(--primary-light)}
        .notification-card{background:#fff;border-radius:14px;overflow:hidden}.notification-row{display:grid;grid-template-columns:44px minmax(0,1fr) auto;gap:13px;padding:17px 20px;border-bottom:1px solid #f1f1f1;align-items:start}.notification-row:last-child{border:0}.notification-row.unread{background:#fffafa;border-left:3px solid var(--primary)}
        .notification-icon{width:40px;height:40px;border-radius:11px;background:#f1f5f9;color:#64748b;display:flex;align-items:center;justify-content:center}.unread .notification-icon{background:#f5e8e8;color:var(--primary)}.notification-row[data-priority="urgent"] .notification-icon{background:#fee2e2;color:#b91c1c}.notification-row[data-priority="high"] .notification-icon{background:#fef3c7;color:#b45309}
        .notification-name{font-size:13px;font-weight:600;color:#222}.notification-message{font-size:11px;color:#777;line-height:1.6;margin-top:4px;white-space:pre-line}.notification-meta{font-size:9.5px;color:#aaa;margin-top:7px}.notification-actions{display:flex;gap:6px}.notification-actions form{margin:0}.notification-action{display:inline-flex;align-items:center;gap:5px;border:0;border-radius:7px;padding:7px 9px;background:#f5e8e8;color:var(--primary);font:600 9.5px 'Poppins';cursor:pointer}.notification-read{font-size:9.5px;color:#aaa;padding:7px}
        .empty{padding:65px 20px;text-align:center;color:#aaa;font-size:12px}.empty i{display:block;font-size:35px;color:#ddd;margin-bottom:12px}.pager{display:flex;justify-content:center;gap:7px;margin-top:16px}.pager a,.pager span{padding:7px 11px;border-radius:7px;border:1px solid #ddd;background:#fff;color:#555;text-decoration:none;font-size:10px}.pager span{color:#aaa}
        .status{background:#d1fae5;color:#047857;padding:11px 14px;border-radius:9px;font-size:11px;margin-bottom:14px}
        @media(max-width:900px){.notification-main{margin-left:68px}}@media(max-width:768px){.notification-main{margin-left:0!important;padding:80px 14px 90px}.notification-row{grid-template-columns:38px minmax(0,1fr);padding:14px}.notification-actions{grid-column:2}.notification-head{align-items:flex-start}.notification-title{font-size:21px}}
    </style>
</head>
<body>
@if(Auth::user()->isChair())
    @include('partials.chair-sidebar', ['active' => 'notifications'])
@elseif(Auth::user()->isFaculty())
    @include('partials.faculty-sidebar', ['active' => 'notifications'])
@else
    @include('partials.sidebar', ['active' => 'notifications'])
    @include('partials.student-bottom-nav', ['active' => 'more'])
    @include('partials.student-mobile-header')
@endif

<main class="notification-main">
    <header class="notification-head"><div><div class="notification-title">Notifications</div><div class="notification-sub">Announcements, reminders, and updates sent to your account.</div></div>
        @if($unreadCount > 0)<form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="read-all" type="submit"><i class="fas fa-check-double"></i> Mark all read ({{ $unreadCount }})</button></form>@endif
    </header>
    @if(session('status'))<div class="status">{{ session('status') }}</div>@endif
    <section class="notification-card">
        @forelse($notifications as $notification)
            @php $sender = trim(($notification->sender_first_name ?? '').' '.($notification->sender_last_name ?? '')) ?: 'CPACE'; @endphp
            <article class="notification-row {{ $notification->is_read ? '' : 'unread' }}" data-priority="{{ $notification->type }}">
                <div class="notification-icon"><i class="fas {{ $notification->type === 'urgent' ? 'fa-triangle-exclamation' : ($notification->type === 'high' ? 'fa-bullhorn' : 'fa-bell') }}"></i></div>
                <div><div class="notification-name">{{ $notification->title }}</div><div class="notification-message">{{ $notification->message }}</div><div class="notification-meta">From {{ $sender }} · {{ \Illuminate\Support\Carbon::parse($notification->created_at)->diffForHumans() }}</div></div>
                <div class="notification-actions">
                    @if(!$notification->is_read || $notification->link)<form method="POST" action="{{ route('notifications.read', $notification->id) }}">@csrf<button class="notification-action" type="submit"><i class="fas {{ $notification->link ? 'fa-arrow-up-right-from-square' : 'fa-check' }}"></i> {{ $notification->link ? 'Open' : 'Mark read' }}</button></form>@else<span class="notification-read"><i class="fas fa-check"></i> Read</span>@endif
                </div>
            </article>
        @empty
            <div class="empty"><i class="far fa-bell"></i>Your notification inbox is empty.</div>
        @endforelse
    </section>
    @if($notifications->hasPages())<div class="pager">@if($notifications->onFirstPage())<span>Previous</span>@else<a href="{{ $notifications->previousPageUrl() }}">Previous</a>@endif <span>Page {{ $notifications->currentPage() }} of {{ $notifications->lastPage() }}</span> @if($notifications->hasMorePages())<a href="{{ $notifications->nextPageUrl() }}">Next</a>@else<span>Next</span>@endif</div>@endif
</main>
</body>
</html>
