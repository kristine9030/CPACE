<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Messages - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

        .chat-shell { display:flex; height:100vh; overflow:hidden; }

        /* ── Conversation list pane ── */
        .chat-list-pane { width:320px; flex-shrink:0; background:#fff; border-right:1px solid #ececec; display:flex; flex-direction:column; }
        .cl-head { padding:18px 18px 12px; border-bottom:1px solid #f2f2f2; }
        .cl-title { font-size:19px; font-weight:700; color:#1a1a1a; margin-bottom:10px; }
        .cl-actions { display:flex; gap:8px; }
        .cl-btn { flex:1; display:flex; align-items:center; justify-content:center; gap:6px; padding:8px; border-radius:9px; border:1.5px solid var(--primary-light); background:var(--primary-light); color:var(--primary); font-size:12px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; transition:all .15s; }
        .cl-btn:hover { background:#efd9d9; }

        .cl-items { flex:1; overflow-y:auto; }
        .cl-item { display:flex; align-items:center; gap:11px; padding:12px 16px; text-decoration:none; color:inherit; border-bottom:1px solid #f8f8f8; transition:background .15s; position:relative; }
        .cl-item:hover { background:#faf9f9; }
        .cl-item.active { background:var(--primary-light); }
        .cl-avatar { width:44px; height:44px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; flex-shrink:0; }
        .cl-avatar.group { background:#5a7fb0; }
        .cl-info { flex:1; min-width:0; }
        .cl-name { font-size:13.5px; font-weight:600; color:#1a1a1a; display:flex; align-items:center; gap:6px; }
        .cl-preview { font-size:12px; color:#999; margin-top:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .cl-meta { display:flex; flex-direction:column; align-items:flex-end; gap:4px; flex-shrink:0; }
        .cl-time { font-size:10.5px; color:#bbb; }
        .cl-unread { background:var(--accent); color:#fff; font-size:10px; font-weight:700; min-width:18px; height:18px; border-radius:10px; display:flex; align-items:center; justify-content:center; padding:0 5px; }
        .cl-empty { padding:40px 20px; text-align:center; color:#bbb; font-size:12.5px; }

        /* ── Thread pane ── */
        .chat-thread-pane { flex:1; display:flex; flex-direction:column; min-width:0; }
        .thread-empty { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#ccc; }
        .thread-empty i { font-size:48px; margin-bottom:14px; color:#e5d8d8; }

        .thread-head { padding:16px 22px; background:#fff; border-bottom:1px solid #ececec; display:flex; align-items:center; gap:12px; }
        .thread-title { font-size:15px; font-weight:700; color:#1a1a1a; }
        .thread-sub { font-size:11.5px; color:#999; margin-top:1px; }

        .thread-body { flex:1; overflow-y:auto; padding:20px 22px; display:flex; flex-direction:column; gap:4px; }
        .msg-row { display:flex; flex-direction:column; max-width:60%; margin-bottom:6px; }
        .msg-row.mine { align-self:flex-end; align-items:flex-end; }
        .msg-row.theirs { align-self:flex-start; align-items:flex-start; }
        .msg-sender { font-size:10.5px; color:#aaa; margin-bottom:2px; padding:0 4px; }
        .msg-bubble { padding:9px 14px; border-radius:16px; font-size:13px; line-height:1.5; word-wrap:break-word; }
        .msg-row.mine .msg-bubble { background:var(--primary); color:#fff; border-bottom-right-radius:4px; }
        .msg-row.theirs .msg-bubble { background:#fff; color:#2a2a2a; border:1px solid #ececec; border-bottom-left-radius:4px; }
        .msg-time { font-size:10px; color:#ccc; margin-top:3px; padding:0 4px; }

        .thread-form { display:flex; gap:10px; padding:14px 20px; background:#fff; border-top:1px solid #ececec; }
        .thread-form input { flex:1; border:1.5px solid #e2e2e2; border-radius:22px; padding:10px 18px; font-size:13px; font-family:'Poppins',sans-serif; outline:none; }
        .thread-form input:focus { border-color:var(--primary); }
        .thread-form button { width:42px; height:42px; border-radius:50%; border:none; background:var(--primary); color:#fff; cursor:pointer; flex-shrink:0; }
        .thread-form button:hover { background:var(--primary-hover); }

        /* ── Modals ── */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:2000; align-items:center; justify-content:center; padding:20px; }
        .modal-overlay.open { display:flex; }
        .modal { background:#fff; border-radius:16px; width:100%; max-width:420px; padding:22px; max-height:82vh; overflow-y:auto; }
        .modal h3 { font-size:16px; color:#1a1a1a; margin-bottom:14px; }
        .modal input[type=text] { width:100%; padding:10px 12px; border:1.5px solid #e2e2e6; border-radius:9px; font-size:13px; font-family:'Poppins',sans-serif; margin-bottom:12px; outline:none; }
        .modal input[type=text]:focus { border-color:var(--primary); }
        .pick-list { max-height:280px; overflow-y:auto; border:1px solid #f0f0f0; border-radius:10px; margin-bottom:14px; }
        .pick-item { display:flex; align-items:center; gap:10px; padding:9px 12px; border-bottom:1px solid #f6f6f6; cursor:pointer; font-size:12.5px; }
        .pick-item:last-child { border-bottom:none; }
        .pick-item:hover { background:#faf9f9; }
        .pick-item input { width:15px; height:15px; accent-color:var(--primary); }
        .pick-av { width:28px; height:28px; border-radius:50%; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:10.5px; flex-shrink:0; }
        .modal-actions { display:flex; gap:10px; justify-content:flex-end; }
        .btn-primary { background:var(--primary); color:#fff; border:none; padding:9px 18px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:#f1f1f3; color:#555; border:none; padding:9px 18px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; }

        @media (max-width:900px) {
            .chat-list-pane { width:100%; }
            .chat-thread-pane { display:none; }
            body.thread-open .chat-list-pane { display:none; }
            body.thread-open .chat-thread-pane { display:flex; }
        }
    </style>
</head>
<body class="{{ $active ? 'thread-open' : '' }}">

@if(Auth::user()->isAlumni())
    @include('partials.alumni-sidebar', ['active' => 'messages'])
@elseif(Auth::user()->isChair())
    @include('partials.chair-sidebar', ['active' => 'messages'])
@elseif(Auth::user()->isFaculty())
    @include('partials.faculty-sidebar', ['active' => ''])
@else
    @include('partials.sidebar', ['active' => 'messages'])
@endif

{{-- .main is used by the faculty/chair/alumni sidebars, .main-content by the student sidebar --}}
<div class="main main-content" style="padding:0;">
<div class="chat-shell">
    <div class="chat-list-pane">
        <div class="cl-head">
            <div class="cl-title">Messages</div>
            <div class="cl-actions">
                <button type="button" class="cl-btn" onclick="document.getElementById('newMsgModal').classList.add('open')"><i class="fas fa-pen"></i> New Message</button>
                @if(Auth::user()->isAlumni() || Auth::user()->isChair())
                    <button type="button" class="cl-btn" onclick="document.getElementById('newGroupModal').classList.add('open')"><i class="fas fa-users"></i> New Group</button>
                @endif
            </div>
        </div>
        <div class="cl-items">
            @forelse($conversations as $c)
                @php
                    $isGroup = $c->type === 'group';
                    $name = $c->displayNameFor(Auth::user());
                    $initials = $isGroup ? strtoupper(substr($name,0,2)) : strtoupper(substr($name,0,1));
                    $unread = $c->unreadCountFor(Auth::user());
                    $preview = $c->latestMessage ? ($c->latestMessage->sender_id === Auth::id() ? 'You: ' : '') . \Illuminate\Support\Str::limit($c->latestMessage->body, 38) : 'No messages yet';
                @endphp
                <a href="{{ route('messages.show', $c->id) }}" class="cl-item {{ $active && $active->id === $c->id ? 'active' : '' }}">
                    <div class="cl-avatar {{ $isGroup ? 'group' : '' }}">
                        @if($isGroup)<i class="fas fa-users"></i>@else{{ $initials }}@endif
                    </div>
                    <div class="cl-info">
                        <div class="cl-name">{{ $name }} @if($c->is_default_group)<i class="fas fa-house-chimney" style="font-size:10px;color:#bbb;" title="Default community chat"></i>@endif</div>
                        <div class="cl-preview">{{ $preview }}</div>
                    </div>
                    <div class="cl-meta">
                        <div class="cl-time">{{ ($c->latestMessage->created_at ?? $c->created_at)->diffForHumans() }}</div>
                        @if($unread > 0)<div class="cl-unread">{{ $unread > 9 ? '9+' : $unread }}</div>@endif
                    </div>
                </a>
            @empty
                <div class="cl-empty">No conversations yet.</div>
            @endforelse
        </div>
    </div>

    <div class="chat-thread-pane">
        @if($active)
            @php
                $isGroup = $active->type === 'group';
                $threadName = $active->displayNameFor(Auth::user());
                $canManageGroup = $isGroup && (Auth::user()->isChair() || $active->created_by === Auth::id());
            @endphp
            <div class="thread-head" @if($isGroup) role="button" tabindex="0" onclick="document.getElementById('groupInfoModal').classList.add('open')" style="cursor:pointer;" @endif>
                <a href="{{ route('messages.index') }}" style="display:none;" class="mobile-back"><i class="fas fa-arrow-left"></i></a>
                <div class="cl-avatar {{ $isGroup ? 'group' : '' }}" style="width:38px;height:38px;font-size:12px;">
                    @if($isGroup)<i class="fas fa-users"></i>@else{{ strtoupper(substr($threadName,0,1)) }}@endif
                </div>
                <div>
                    <div class="thread-title">{{ $threadName }}</div>
                    @if($isGroup)<div class="thread-sub">{{ $active->participants->count() }} members @if($active->is_default_group)· Default community chat @endif <i class="fas fa-chevron-right" style="font-size:9px;margin-left:3px;"></i></div>@endif
                </div>
            </div>

            <div class="thread-body" id="threadBody" data-conversation="{{ $active->id }}" data-poll-url="{{ route('messages.poll', $active->id) }}" data-send-url="{{ route('messages.send', $active->id) }}">
                @foreach($messages as $m)
                    @php $mine = $m->sender_id === Auth::id(); @endphp
                    <div class="msg-row {{ $mine ? 'mine' : 'theirs' }}" data-id="{{ $m->id }}">
                        @if(!$mine && $isGroup)<div class="msg-sender">{{ $m->sender->name ?? 'User' }}</div>@endif
                        <div class="msg-bubble">{{ $m->body }}</div>
                        <div class="msg-time">{{ $m->created_at->format('g:i A') }}</div>
                    </div>
                @endforeach
            </div>

            <form class="thread-form" id="threadForm">
                @csrf
                <input type="text" name="body" id="threadInput" placeholder="Type a message..." autocomplete="off" maxlength="3000" required>
                <button type="submit"><i class="fas fa-paper-plane"></i></button>
            </form>
        @else
            <div class="thread-empty">
                <i class="fas fa-comments"></i>
                <div>Select a conversation to start chatting.</div>
            </div>
        @endif
    </div>
</div>
</div>

@if($active && $active->type === 'group')
    {{-- Group Info / Settings modal --}}
    <div class="modal-overlay" id="groupInfoModal">
        <div class="modal">
            <h3><i class="fas fa-users" style="color:var(--primary);"></i> {{ $active->displayNameFor(Auth::user()) }}</h3>

            @if(session('status'))
                <div style="background:#e8f7ee;color:#1e7e46;border:1px solid #bfead0;padding:9px 12px;border-radius:9px;font-size:12px;margin-bottom:12px;">{{ session('status') }}</div>
            @endif

            @if($canManageGroup)
                <form method="POST" action="{{ route('messages.rename', $active->id) }}" style="display:flex; gap:8px; margin-bottom:14px;">
                    @csrf @method('PUT')
                    <input type="text" name="name" value="{{ $active->name }}" required style="flex:1;margin-bottom:0;">
                    <button type="submit" class="btn-primary" style="flex-shrink:0;"><i class="fas fa-pen"></i></button>
                </form>
            @endif

            <div style="font-size:11.5px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">
                {{ $active->participants->count() }} Member{{ $active->participants->count() === 1 ? '' : 's' }}
            </div>
            <div class="pick-list" style="margin-bottom:14px;">
                @foreach($active->participants as $member)
                    <div class="pick-item" style="cursor:default;">
                        <div class="pick-av">{{ strtoupper(substr($member->first_name,0,1)) }}</div>
                        <span>
                            {{ $member->name }}{{ $member->id === Auth::id() ? ' (You)' : '' }}
                            <small style="color:#aaa;">({{ ucfirst($member->roleName() ?? '') }})</small>
                            @if($active->created_by === $member->id)<small style="color:var(--primary);font-weight:600;"> · Creator</small>@endif
                        </span>
                    </div>
                @endforeach
            </div>

            @if($canManageGroup && $addableCandidates->isNotEmpty())
                <form method="POST" action="{{ route('messages.members.add', $active->id) }}" style="margin-bottom:14px;">
                    @csrf
                    <div style="font-size:11.5px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Add Members</div>
                    <input type="text" id="addMemberSearch" placeholder="Search students / alumni..." oninput="filterPick('addMemberSearch','addMemberPickList')">
                    <div class="pick-list" id="addMemberPickList" style="margin-bottom:10px;">
                        @foreach($addableCandidates as $u)
                            <label class="pick-item" data-name="{{ strtolower($u->name) }}">
                                <input type="checkbox" name="member_ids[]" value="{{ $u->id }}">
                                <div class="pick-av">{{ strtoupper(substr($u->first_name,0,1)) }}</div>
                                <span>{{ $u->name }} <small style="color:#aaa;">({{ ucfirst($u->roleName() ?? '') }})</small></span>
                            </label>
                        @endforeach
                    </div>
                    <button type="submit" class="btn-primary"><i class="fas fa-user-plus"></i> Add Selected</button>
                </form>
            @endif

            <div class="modal-actions" style="justify-content:space-between;">
                @if(!$active->is_default_group)
                    <form method="POST" action="{{ route('messages.leave', $active->id) }}" onsubmit="return confirm('Leave this group chat?');">
                        @csrf
                        <button type="submit" class="btn-ghost" style="color:var(--accent);"><i class="fas fa-right-from-bracket"></i> Leave Group</button>
                    </form>
                @else
                    <span></span>
                @endif
                <button type="button" class="btn-ghost" onclick="document.getElementById('groupInfoModal').classList.remove('open')">Close</button>
            </div>
        </div>
    </div>
@endif

{{-- New Message modal --}}
<div class="modal-overlay" id="newMsgModal">
    <div class="modal">
        <h3>New Message</h3>
        <input type="text" id="msgSearch" placeholder="Search people..." oninput="filterPick('msgSearch','msgPickList')">
        <div class="pick-list" id="msgPickList">
            @foreach($messageable as $u)
                <form method="POST" action="{{ route('messages.start') }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $u->id }}">
                    <button type="submit" class="pick-item" style="width:100%;border:none;background:none;text-align:left;font-family:'Poppins',sans-serif;" data-name="{{ strtolower($u->name) }}">
                        <div class="pick-av">{{ strtoupper(substr($u->first_name,0,1)) }}</div>
                        <span>{{ $u->name }} <small style="color:#aaa;">({{ ucfirst($u->roleName() ?? '') }})</small></span>
                    </button>
                </form>
            @endforeach
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-ghost" onclick="document.getElementById('newMsgModal').classList.remove('open')">Close</button>
        </div>
    </div>
</div>

@if(Auth::user()->isAlumni() || Auth::user()->isChair())
    {{-- New Group modal --}}
    <div class="modal-overlay" id="newGroupModal">
        <div class="modal">
            <h3>New Group Chat</h3>
            <form method="POST" action="{{ route('messages.group.create') }}">
                @csrf
                <input type="text" name="name" placeholder="Group name" required>
                <input type="text" id="groupSearch" placeholder="Search students / alumni..." oninput="filterPick('groupSearch','groupPickList')">
                <div class="pick-list" id="groupPickList">
                    @foreach($groupCandidates as $u)
                        <label class="pick-item" data-name="{{ strtolower($u->name) }}">
                            <input type="checkbox" name="member_ids[]" value="{{ $u->id }}">
                            <div class="pick-av">{{ strtoupper(substr($u->first_name,0,1)) }}</div>
                            <span>{{ $u->name }} <small style="color:#aaa;">({{ ucfirst($u->roleName() ?? '') }})</small></span>
                        </label>
                    @endforeach
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-ghost" onclick="document.getElementById('newGroupModal').classList.remove('open')">Cancel</button>
                    <button type="submit" class="btn-primary"><i class="fas fa-users"></i> Create Group</button>
                </div>
            </form>
        </div>
    </div>
@endif

<script>
function filterPick(inputId, listId) {
    const q = document.getElementById(inputId).value.toLowerCase();
    document.getElementById(listId).querySelectorAll('[data-name]').forEach(function (el) {
        el.style.display = el.dataset.name.includes(q) ? '' : 'none';
    });
}
document.querySelectorAll('.modal-overlay').forEach(function (ov) {
    ov.addEventListener('click', function (e) { if (e.target === ov) ov.classList.remove('open'); });
});

(function () {
    const body = document.getElementById('threadBody');
    const form = document.getElementById('threadForm');
    if (!body) return;

    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    function scrollToBottom() { body.scrollTop = body.scrollHeight; }
    scrollToBottom();

    function lastMessageId() {
        const rows = body.querySelectorAll('.msg-row');
        return rows.length ? rows[rows.length - 1].dataset.id : 0;
    }

    function appendMessage(m, mine) {
        const row = document.createElement('div');
        row.className = 'msg-row ' + (mine ? 'mine' : 'theirs');
        row.dataset.id = m.id;
        let html = '';
        if (!mine) html += '<div class="msg-sender">' + escapeHtml(m.sender_name) + '</div>';
        html += '<div class="msg-bubble"></div><div class="msg-time">' + escapeHtml(m.created_at) + '</div>';
        row.innerHTML = html;
        row.querySelector('.msg-bubble').textContent = m.body;
        body.appendChild(row);
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const input = document.getElementById('threadInput');
            const text = input.value.trim();
            if (!text) return;

            fetch(body.dataset.sendUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ body: text }),
            })
                .then(r => r.json())
                .then(data => {
                    appendMessage(data.message, true);
                    scrollToBottom();
                    input.value = '';
                })
                .catch(() => { form.submit(); });
        });
    }

    function poll() {
        fetch(body.dataset.pollUrl + '?after_id=' + lastMessageId(), {
            headers: { 'Accept': 'application/json' },
        })
            .then(r => r.json())
            .then(data => {
                if (data.messages && data.messages.length) {
                    data.messages.forEach(m => { if (!m.is_mine) appendMessage(m, false); });
                    scrollToBottom();
                }
            })
            .catch(() => {});
    }

    setInterval(poll, 4000);
})();
</script>
</body>
</html>
