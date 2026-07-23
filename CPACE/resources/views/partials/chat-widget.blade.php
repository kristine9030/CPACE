{{-- Facebook/Messenger-style floating chat popups, docked bottom-right.
     Opened from the Contacts rail; reuses the existing messages.start / send / poll endpoints. --}}
@php $fbContacts = $contacts ?? collect(); @endphp
{{-- Row layout, bottom-aligned: popups open to the left, the bubble
     rail + compose pencil stay pinned at the right edge — same as facebook.com --}}
<div class="fb-chat-widgets">
    <div id="fbChatDock" class="fb-chat-dock"></div>
    <div id="fbBubbleRail" class="fb-bubble-rail">
        <button type="button" class="fb-bubble-compose" title="New message" id="fbComposeBtn"><i class="fas fa-pen"></i></button>
    </div>
</div>

{{-- "New message" picker, opened by the compose (pencil) button --}}
<div class="fb-newmsg-overlay" id="fbNewMsgOverlay">
    <div class="fb-newmsg-modal">
        <div class="fb-newmsg-head">
            <div class="fb-newmsg-title">New message</div>
            <button type="button" class="fb-newmsg-close" id="fbNewMsgClose"><i class="fas fa-times"></i></button>
        </div>
        <div class="fb-newmsg-to">
            <span>To:</span>
            <input type="text" id="fbNewMsgSearch" placeholder="Type a name..." autocomplete="off">
        </div>
        <div class="fb-newmsg-list" id="fbNewMsgList">
            @forelse($fbContacts as $c)
                <div class="fb-newmsg-item" data-name="{{ strtolower($c->name) }}" data-id="{{ $c->id }}" data-label="{{ addslashes($c->name) }}">
                    <div class="fb-newmsg-av">{{ strtoupper(substr($c->first_name,0,1)) }}</div>
                    <div>
                        <div class="fb-newmsg-name">{{ $c->name }}</div>
                        <div class="fb-newmsg-role">{{ $c->roleName() }}</div>
                    </div>
                </div>
            @empty
                <div class="fb-newmsg-empty">No one else here yet.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- "View profile" mini card, opened from a popup's name dropdown --}}
<div class="fcp-profile-overlay" id="fcpProfileOverlay">
    <div class="fcp-profile-card">
        <div class="fcp-profile-av" id="fcpProfileAv"></div>
        <div class="fcp-profile-name" id="fcpProfileName"></div>
        <div class="fcp-profile-status"><i class="fas fa-circle" style="font-size:6px;"></i> Active now</div>
        <button type="button" class="fcp-profile-close" id="fcpProfileClose">Close</button>
    </div>
</div>

<style>
    .fb-chat-widgets { position:fixed; right:16px; bottom:0; z-index:2500; display:flex; flex-direction:row; align-items:flex-end; gap:10px; }

    /* ── Collapsed bubble rail — where evicted popups shrink down to,
       stacked like overlapping coins the way Facebook does it ── */
    .fb-bubble-rail { display:flex; flex-direction:column; align-items:center; padding-bottom:2px; }
    .fb-bubble-rail .fb-bubble-compose { margin-top:12px; }
    .fb-bubble { width:42px; height:42px; border-radius:50%; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; cursor:pointer; box-shadow:0 1px 5px rgba(0,0,0,.28); position:relative; flex-shrink:0; border:2.5px solid #fff; transition:transform .12s; }
    .fb-bubble:hover { transform:scale(1.08); z-index:5; }
    .fb-bubble ~ .fb-bubble { margin-top:-12px; }
    .fb-bubble::after { content:''; position:absolute; bottom:-2px; right:-2px; width:12px; height:12px; border-radius:50%; background:#31a24c; border:2px solid #fff; }
    .fb-bubble-compose { width:56px; height:56px; border-radius:50%; background:#e4e6eb; color:#050505; border:none; display:flex; align-items:center; justify-content:center; font-size:19px; cursor:pointer; box-shadow:0 1px 6px rgba(0,0,0,.25); flex-shrink:0; transition:background .15s; }
    .fb-bubble-compose:hover { background:#d8dade; }

    /* ── Open popup dock ── */
    .fb-chat-dock { display:flex; align-items:flex-end; gap:10px; }
    .fb-chat-pop { width:308px; height:390px; background:#fff; border-radius:10px 10px 0 0; box-shadow:0 -2px 18px rgba(0,0,0,.18); display:flex; flex-direction:column; overflow:hidden; font-family:'Poppins',sans-serif; transition:height .15s; }
    .fb-chat-pop.minimized { height:48px; }

    .fb-chat-pop .fcp-head { height:56px; flex-shrink:0; background:#fff; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; gap:9px; padding:0 8px 0 10px; cursor:pointer; }
    .fb-chat-pop .fcp-av { width:34px; height:34px; border-radius:50%; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; flex-shrink:0; position:relative; }
    .fb-chat-pop .fcp-av::after { content:''; position:absolute; bottom:-1px; right:-1px; width:9px; height:9px; border-radius:50%; background:#31a24c; border:2px solid #fff; }
    .fb-chat-pop .fcp-headinfo { flex:1; min-width:0; position:relative; }
    .fb-chat-pop .fcp-name-wrap { position:relative; display:inline-block; max-width:100%; }
    .fb-chat-pop .fcp-name { font-size:13px; font-weight:700; color:#050505; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:flex; align-items:center; gap:5px; cursor:pointer; padding:2px 4px; border-radius:6px; margin-left:-4px; }
    .fb-chat-pop .fcp-name:hover { background:#f0f2f5; }
    .fb-chat-pop .fcp-name .fcp-chevron { font-size:9px; color:#8a8d91; }
    .fb-chat-pop .fcp-name .fa-thumbtack { font-size:10px; color:#f0b400; }

    .fcp-namemenu { display:none; position:absolute; top:100%; left:-4px; margin-top:4px; background:#fff; border-radius:10px; box-shadow:0 2px 14px rgba(0,0,0,.22); min-width:200px; z-index:2600; overflow:hidden; font-family:'Poppins',sans-serif; }
    .fcp-namemenu.open { display:block; }
    .fcp-namemenu button { display:flex; align-items:center; gap:10px; width:100%; text-align:left; border:none; background:none; padding:10px 14px; font-size:12.5px; font-family:'Poppins',sans-serif; color:#050505; cursor:pointer; }
    .fcp-namemenu button:hover { background:#f2f2f2; }
    .fcp-namemenu button i { width:15px; color:#65676b; flex-shrink:0; }
    .fcp-namemenu button.fcp-pinned i { color:#f0b400; }

    /* ── Mini "view profile" card ── */
    .fcp-profile-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:3100; align-items:center; justify-content:center; padding:20px; }
    .fcp-profile-overlay.open { display:flex; }
    .fcp-profile-card { background:#fff; border-radius:14px; width:100%; max-width:280px; padding:26px 22px; text-align:center; font-family:'Poppins',sans-serif; box-shadow:0 4px 30px rgba(0,0,0,.25); }
    .fcp-profile-av { width:76px; height:76px; border-radius:50%; color:#fff; font-size:26px; font-weight:700; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; }
    .fcp-profile-name { font-size:16px; font-weight:700; color:#050505; }
    .fcp-profile-status { font-size:12px; color:#31a24c; margin-top:3px; }
    .fcp-profile-close { margin-top:18px; border:none; background:#f0f2f5; color:#050505; padding:9px 20px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; }
    .fcp-profile-close:hover { background:#e4e6eb; }
    .fb-chat-pop .fcp-sub { font-size:10.5px; color:#31a24c; display:flex; align-items:center; gap:4px; }
    .fb-chat-pop .fcp-sub i { font-size:6px; }
    .fb-chat-pop .fcp-actions { display:flex; gap:1px; flex-shrink:0; }
    .fb-chat-pop .fcp-actions button { width:28px; height:28px; border:none; background:transparent; color:#0866ff; border-radius:50%; cursor:pointer; font-size:13px; }
    .fb-chat-pop .fcp-actions button.fcp-min, .fb-chat-pop .fcp-actions button.fcp-close { color:#65676b; }
    .fb-chat-pop .fcp-actions button:hover { background:#f0f2f5; }

    .fb-chat-pop .fcp-body { flex:1; overflow-y:auto; padding:10px; display:flex; flex-direction:column; gap:4px; background:#fff; }
    .fb-chat-pop.minimized .fcp-body, .fb-chat-pop.minimized .fcp-form { display:none; }
    .fcp-row { display:flex; flex-direction:column; max-width:78%; }
    .fcp-row.mine { align-self:flex-end; align-items:flex-end; }
    .fcp-row.theirs { align-self:flex-start; align-items:flex-start; gap:2px; }
    .fcp-row.theirs .fcp-with-av { display:flex; align-items:flex-end; gap:6px; }
    .fcp-row.theirs .fcp-mini-av { width:20px; height:20px; border-radius:50%; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:9px; flex-shrink:0; }
    .fcp-bubble { padding:7px 12px; border-radius:16px; font-size:12.5px; line-height:1.4; word-wrap:break-word; }
    .fcp-row.mine .fcp-bubble { color:#fff; }
    .fcp-row.theirs .fcp-bubble { background:#e4e6eb; color:#050505; }
    .fcp-bubble:empty { display:none; }
    .fcp-sent { font-size:10px; color:#aaa; margin-top:2px; padding:0 4px; }

    /* ── Shared file / photo attachments ── */
    .fcp-attach-img { max-width:200px; max-height:200px; border-radius:12px; display:block; cursor:pointer; }
    .fcp-attach-file { display:flex; align-items:center; gap:9px; padding:9px 11px; border-radius:12px; background:#f0f2f5; text-decoration:none; color:inherit; max-width:210px; }
    .fcp-attach-file .fcp-af-icon { width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:13px; color:#fff; flex-shrink:0; }
    .fcp-attach-file .fcp-af-info { min-width:0; }
    .fcp-attach-file .fcp-af-name { font-size:11.5px; font-weight:600; color:#050505; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .fcp-attach-file .fcp-af-size { font-size:10px; color:#999; }
    .fcp-uploading { font-size:11px; color:#aaa; padding:0 4px; }

    .fb-chat-pop .fcp-form { display:flex; align-items:center; gap:2px; padding:6px 8px; border-top:1px solid #f0f0f0; flex-shrink:0; }
    .fb-chat-pop .fcp-tool { width:30px; height:30px; border:none; background:transparent; color:#0866ff; font-size:14px; cursor:pointer; flex-shrink:0; border-radius:50%; }
    .fb-chat-pop .fcp-tool:hover { background:#f0f2f5; }
    .fb-chat-pop .fcp-form input { flex:1; min-width:0; border:none; background:#f0f2f5; border-radius:18px; padding:8px 12px; font-size:12.5px; font-family:'Poppins',sans-serif; outline:none; margin:0 4px; }
    .fb-chat-pop .fcp-send { width:30px; height:30px; border:none; background:transparent; color:#0866ff; font-size:16px; cursor:pointer; flex-shrink:0; border-radius:50%; }
    .fb-chat-pop .fcp-send:hover { background:#f0f2f5; }
    .fcp-empty { flex:1; display:flex; align-items:center; justify-content:center; color:#bbb; font-size:11.5px; text-align:center; padding:20px; }

    @media (max-width:768px) { .fb-chat-widgets { display:none; } }

    /* ── "New message" picker ── */
    .fb-newmsg-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:3000; align-items:center; justify-content:center; padding:20px; }
    .fb-newmsg-overlay.open { display:flex; }
    .fb-newmsg-modal { background:#fff; border-radius:12px; width:100%; max-width:360px; max-height:78vh; display:flex; flex-direction:column; overflow:hidden; font-family:'Poppins',sans-serif; box-shadow:0 4px 30px rgba(0,0,0,.25); }
    .fb-newmsg-head { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; border-bottom:1px solid #eee; }
    .fb-newmsg-title { font-size:16px; font-weight:700; color:#050505; }
    .fb-newmsg-close { width:30px; height:30px; border-radius:50%; border:none; background:#f0f2f5; color:#65676b; font-size:13px; cursor:pointer; }
    .fb-newmsg-close:hover { background:#e4e6eb; }
    .fb-newmsg-to { display:flex; align-items:center; gap:8px; padding:10px 16px; border-bottom:1px solid #eee; }
    .fb-newmsg-to span { font-size:13px; color:#65676b; flex-shrink:0; }
    .fb-newmsg-to input { flex:1; border:none; outline:none; font-size:13.5px; font-family:'Poppins',sans-serif; padding:4px 0; }
    .fb-newmsg-list { flex:1; overflow-y:auto; padding:6px; }
    .fb-newmsg-item { display:flex; align-items:center; gap:11px; padding:8px 10px; border-radius:8px; cursor:pointer; transition:background .12s; }
    .fb-newmsg-item:hover { background:#f2f2f2; }
    .fb-newmsg-av { width:38px; height:38px; border-radius:50%; background:var(--primary,#7B1D1D); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; flex-shrink:0; }
    .fb-newmsg-name { font-size:13.5px; font-weight:600; color:#050505; }
    .fb-newmsg-role { font-size:11px; color:#999; text-transform:capitalize; }
    .fb-newmsg-empty { padding:24px 16px; text-align:center; color:#bbb; font-size:12.5px; }
</style>

<script>
(function () {
    const dock = document.getElementById('fbChatDock');
    const bubbleRail = document.getElementById('fbBubbleRail');
    const composeBtn = document.getElementById('fbComposeBtn');
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.content : '';
    const open = {}; // userId -> { el, conversationId, pollTimer, lastId }
    const bubbles = {}; // userId -> bubble <button> element in the rail
    const MAX_POPUPS = 3;
    let order = []; // userIds, oldest (least-recently-active) first, newest last

    // CPACE brand color — every popup/bubble uses this instead of Facebook's
    // per-conversation color themes.
    const MAROON = '#7B1D1D';
    function themeFor() { return MAROON; }

    // Bumps a chat to "most recently active" — it slides to the end of the
    // dock, same spot Facebook puts the one you just touched.
    function touchOrder(userId) {
        order = order.filter(function (id) { return id !== userId; });
        order.push(userId);
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    // Instead of just closing the oldest popup, it shrinks into a small
    // round avatar bubble stacked above the compose button — the same
    // "collapsed chats" rail Facebook shows.
    function collapseToBubble(userId, name) {
        const win = open[userId];
        if (win) {
            if (win.timer) clearInterval(win.timer);
            win.el.remove();
            delete open[userId];
        }
        if (bubbles[userId]) return; // already collapsed

        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'fb-bubble';
        b.title = name;
        b.style.background = themeFor(userId);
        b.textContent = name.substring(0, 1).toUpperCase();
        b.addEventListener('click', function () {
            b.remove();
            delete bubbles[userId];
            window.openFbChat(userId, name);
        });
        bubbleRail.insertBefore(b, composeBtn);
        bubbles[userId] = b;
    }

    // Facebook only ever keeps 3 popups open — opening a 4th collapses
    // whichever one has gone longest without being touched into a bubble.
    // Pinned chats are skipped unless every open popup is pinned.
    function evictLruIfFull() {
        while (order.length >= MAX_POPUPS) {
            let idx = order.findIndex(function (id) { const w = open[id]; return !(w && w.pinned); });
            if (idx === -1) idx = 0;
            const evictId = order.splice(idx, 1)[0];
            const win = open[evictId];
            if (win) collapseToBubble(evictId, win.name);
        }
    }

    // Closing a popup deletes it for good, but if a bubble is waiting in the
    // rail, it slides straight into the slot that just opened up — same as
    // Facebook filling a closed chat head with the next queued conversation.
    function promoteNextBubble() {
        const bubbleEls = bubbleRail.querySelectorAll('.fb-bubble');
        if (!bubbleEls.length) return;
        bubbleEls[bubbleEls.length - 1].click();
    }

    function attachmentHtml(a) {
        if (!a) return '';
        if (a.is_image) {
            return '<img class="fcp-attach-img" src="' + a.preview_url + '" alt="" onclick="window.open(this.src, \'_blank\')">';
        }
        return (
            '<a class="fcp-attach-file" href="' + a.url + '" target="_blank" rel="noopener">' +
                '<div class="fcp-af-icon" style="background:' + a.color + ';"><i class="fas ' + a.icon + '"></i></div>' +
                '<div class="fcp-af-info">' +
                    '<div class="fcp-af-name">' + escapeHtml(a.name || 'File') + '</div>' +
                    '<div class="fcp-af-size">' + escapeHtml(a.size || '') + '</div>' +
                '</div>' +
            '</a>'
        );
    }

    function appendMsg(win, m, mine) {
        const placeholder = win.body.querySelector('.fcp-empty');
        if (placeholder) placeholder.remove();
        const row = document.createElement('div');
        row.className = 'fcp-row ' + (mine ? 'mine' : 'theirs');
        row.dataset.id = m.id;
        const attach = attachmentHtml(m.attachment);
        if (mine) {
            row.innerHTML =
                attach +
                '<div class="fcp-bubble" style="background:' + win.theme + ';"></div>' +
                '<div class="fcp-sent">Sent ' + escapeHtml(m.created_at || '') + '</div>';
        } else {
            row.innerHTML =
                '<div class="fcp-with-av">' +
                    '<div class="fcp-mini-av" style="background:' + win.theme + ';">' + escapeHtml(win.name.substring(0, 1).toUpperCase()) + '</div>' +
                    '<div>' + attach + '<div class="fcp-bubble"></div></div>' +
                '</div>';
        }
        row.querySelector('.fcp-bubble').textContent = m.body;
        win.body.appendChild(row);
        win.body.scrollTop = win.body.scrollHeight;
        win.lastId = m.id;
    }

    function poll(win) {
        fetch('/messages/' + win.conversationId + '/poll?after_id=' + (win.lastId || 0), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                const incoming = (data.messages || []).filter(m => !m.is_mine);
                incoming.forEach(m => appendMsg(win, m, false));
                if (incoming.length) touchOrder(win.userId);
            })
            .catch(() => {});
    }

    window.openFbChat = function (userId, name) {
        if (bubbles[userId]) {
            bubbles[userId].remove();
            delete bubbles[userId];
        }

        if (open[userId]) {
            open[userId].el.classList.remove('minimized');
            touchOrder(userId);
            dock.appendChild(open[userId].el); // slide to the "most recent" slot
            return;
        }

        evictLruIfFull();

        const theme = themeFor(userId);
        const el = document.createElement('div');
        el.className = 'fb-chat-pop';
        el.innerHTML =
            '<div class="fcp-head">' +
                '<div class="fcp-av" style="background:' + theme + ';">' + escapeHtml(name.substring(0, 1).toUpperCase()) + '</div>' +
                '<div class="fcp-headinfo">' +
                    '<div class="fcp-name-wrap">' +
                        '<div class="fcp-name"><span class="fcp-name-text">' + escapeHtml(name) + '</span> <i class="fas fa-chevron-down fcp-chevron"></i></div>' +
                        '<div class="fcp-namemenu">' +
                            '<button type="button" class="fcp-menu-open"><i class="fas fa-up-right-from-square"></i> Open in Messenger</button>' +
                            '<button type="button" class="fcp-menu-pin"><i class="fas fa-thumbtack"></i> Pin chat</button>' +
                            '<button type="button" class="fcp-menu-profile"><i class="fas fa-circle-user"></i> View profile</button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="fcp-sub"><i class="fas fa-circle"></i> Active now</div>' +
                '</div>' +
                '<div class="fcp-actions">' +
                    '<button type="button" class="fcp-min" title="Minimize"><i class="fas fa-minus"></i></button>' +
                    '<button type="button" class="fcp-close" title="Close"><i class="fas fa-times"></i></button>' +
                '</div>' +
            '</div>' +
            '<div class="fcp-body"><div class="fcp-empty">Loading conversation…</div></div>' +
            '<form class="fcp-form">' +
                '<button type="button" class="fcp-tool fcp-attach-file-btn" title="Attach a file"><i class="fas fa-paperclip"></i></button>' +
                '<button type="button" class="fcp-tool fcp-attach-img-btn" title="Send a photo"><i class="far fa-image"></i></button>' +
                '<input type="file" class="fcp-file-input" hidden accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.csv,.txt,.rtf,.odt,.jpg,.jpeg,.png,.gif,.webp,.zip,.rar">' +
                '<input type="file" class="fcp-image-input" hidden accept="image/*">' +
                '<input type="text" placeholder="Aa" maxlength="3000" autocomplete="off">' +
                '<button type="button" class="fcp-tool" title="Emoji"><i class="far fa-face-smile"></i></button>' +
                '<button type="submit" class="fcp-send"><i class="fas fa-thumbs-up"></i></button>' +
            '</form>';

        dock.appendChild(el);

        const win = { el, userId, name, theme, pinned: false, conversationId: null, lastId: 0, timer: null, body: el.querySelector('.fcp-body') };
        open[userId] = win;
        touchOrder(userId);

        el.querySelector('.fcp-head').addEventListener('click', function () { el.classList.toggle('minimized'); touchOrder(userId); });
        el.querySelector('.fcp-min').addEventListener('click', function (e) { e.stopPropagation(); el.classList.toggle('minimized'); });
        el.querySelector('.fcp-close').addEventListener('click', function (e) {
            e.stopPropagation();
            if (win.timer) clearInterval(win.timer);
            el.remove();
            delete open[userId];
            order = order.filter(function (id) { return id !== userId; });
            promoteNextBubble();
        });

        // Name dropdown: Open in Messenger / Pin chat / View profile
        const nameMenu = el.querySelector('.fcp-namemenu');
        el.querySelector('.fcp-name').addEventListener('click', function (e) {
            e.stopPropagation();
            document.querySelectorAll('.fcp-namemenu.open').forEach(function (m) { if (m !== nameMenu) m.classList.remove('open'); });
            nameMenu.classList.toggle('open');
        });

        el.querySelector('.fcp-menu-open').addEventListener('click', function (e) {
            e.stopPropagation();
            nameMenu.classList.remove('open');
            if (win.conversationId) window.location.href = '/messages/' + win.conversationId;
        });

        const pinBtn = el.querySelector('.fcp-menu-pin');
        pinBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            win.pinned = !win.pinned;
            nameMenu.classList.remove('open');
            pinBtn.classList.toggle('fcp-pinned', win.pinned);
            pinBtn.innerHTML = win.pinned
                ? '<i class="fas fa-thumbtack"></i> Unpin chat'
                : '<i class="fas fa-thumbtack"></i> Pin chat';
            let tack = el.querySelector('.fcp-name-text + .fa-thumbtack');
            if (win.pinned && !tack) {
                el.querySelector('.fcp-name-text').insertAdjacentHTML('afterend', ' <i class="fas fa-thumbtack" title="Pinned"></i>');
            } else if (!win.pinned && tack) {
                tack.remove();
            }
        });

        el.querySelector('.fcp-menu-profile').addEventListener('click', function (e) {
            e.stopPropagation();
            nameMenu.classList.remove('open');
            window.openFbProfile(name, theme);
        });

        const form = el.querySelector('.fcp-form');
        const input = form.querySelector('input[type="text"]');
        const sendBtn = form.querySelector('.fcp-send');
        input.addEventListener('input', function () {
            sendBtn.innerHTML = input.value.trim() ? '<i class="fas fa-paper-plane"></i>' : '<i class="fas fa-thumbs-up"></i>';
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const text = input.value.trim() || '👍';
            if (!win.conversationId) return;
            input.value = '';
            sendBtn.innerHTML = '<i class="fas fa-thumbs-up"></i>';

            fetch('/messages/' + win.conversationId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ body: text }),
            })
                .then(r => r.json())
                .then(data => { appendMsg(win, data.message, true); touchOrder(userId); })
                .catch(() => {});
        });

        // Sharing a file/photo — same endpoint, sent as multipart form data.
        function sendFile(file) {
            if (!win.conversationId || !file) return;
            const notice = document.createElement('div');
            notice.className = 'fcp-uploading';
            notice.textContent = 'Sending ' + file.name + '…';
            win.body.appendChild(notice);
            win.body.scrollTop = win.body.scrollHeight;

            const fd = new FormData();
            fd.append('file', file);

            fetch('/messages/' + win.conversationId, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: fd,
            })
                .then(r => r.json())
                .then(data => { notice.remove(); appendMsg(win, data.message, true); touchOrder(userId); })
                .catch(() => { notice.textContent = 'Failed to send ' + file.name + '.'; });
        }

        const fileInput = form.querySelector('.fcp-file-input');
        const imageInput = form.querySelector('.fcp-image-input');
        form.querySelector('.fcp-attach-file-btn').addEventListener('click', function (e) { e.stopPropagation(); fileInput.click(); });
        form.querySelector('.fcp-attach-img-btn').addEventListener('click', function (e) { e.stopPropagation(); imageInput.click(); });
        fileInput.addEventListener('change', function () { if (this.files[0]) sendFile(this.files[0]); this.value = ''; });
        imageInput.addEventListener('change', function () { if (this.files[0]) sendFile(this.files[0]); this.value = ''; });

        fetch('/messages/start/direct', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ user_id: userId }),
        })
            .then(r => r.json())
            .then(data => {
                win.conversationId = data.conversation.id;
                win.body.innerHTML = '';
                if (!data.messages.length) {
                    win.body.innerHTML = '<div class="fcp-empty">Say hi to ' + escapeHtml(name) + '!</div>';
                } else {
                    data.messages.forEach(m => appendMsg(win, m, m.is_mine));
                }
                win.timer = setInterval(function () { poll(win); }, 4000);
            })
            .catch(() => { win.body.innerHTML = '<div class="fcp-empty">Couldn\'t open this chat.</div>'; });
    };

    // "New message" picker
    const newMsgOverlay = document.getElementById('fbNewMsgOverlay');
    const newMsgSearch = document.getElementById('fbNewMsgSearch');
    const newMsgClose = document.getElementById('fbNewMsgClose');

    function openNewMsgModal() {
        if (!newMsgOverlay) return;
        newMsgOverlay.classList.add('open');
        if (newMsgSearch) { newMsgSearch.value = ''; newMsgSearch.focus(); }
        newMsgOverlay.querySelectorAll('.fb-newmsg-item').forEach(function (el) { el.style.display = ''; });
    }
    function closeNewMsgModal() { if (newMsgOverlay) newMsgOverlay.classList.remove('open'); }

    if (composeBtn) composeBtn.addEventListener('click', openNewMsgModal);
    if (newMsgClose) newMsgClose.addEventListener('click', closeNewMsgModal);
    if (newMsgOverlay) {
        newMsgOverlay.addEventListener('click', function (e) { if (e.target === newMsgOverlay) closeNewMsgModal(); });
        newMsgOverlay.querySelectorAll('.fb-newmsg-item').forEach(function (el) {
            el.addEventListener('click', function () {
                closeNewMsgModal();
                window.openFbChat(parseInt(el.dataset.id, 10), el.dataset.label);
            });
        });
    }
    if (newMsgSearch) {
        newMsgSearch.addEventListener('input', function () {
            const q = newMsgSearch.value.toLowerCase();
            newMsgOverlay.querySelectorAll('.fb-newmsg-item').forEach(function (el) {
                el.style.display = el.dataset.name.includes(q) ? '' : 'none';
            });
        });
    }

    // Also dispatched for any page that wants to react to the compose
    // button beyond opening this picker (e.g. highlighting its own rail).
    if (composeBtn) {
        composeBtn.addEventListener('click', function () {
            document.dispatchEvent(new CustomEvent('fbChatCompose'));
        });
    }

    // Closes any open popup name-dropdown when clicking outside of it.
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.fcp-name-wrap')) {
            document.querySelectorAll('.fcp-namemenu.open').forEach(function (m) { m.classList.remove('open'); });
        }
    });

    // "View profile" mini card — this app has no public profile page for
    // arbitrary users yet, so this shows a quick card instead of a dead link.
    const profileOverlay = document.getElementById('fcpProfileOverlay');
    const profileAv = document.getElementById('fcpProfileAv');
    const profileName = document.getElementById('fcpProfileName');
    const profileClose = document.getElementById('fcpProfileClose');

    window.openFbProfile = function (name, theme) {
        if (!profileOverlay) return;
        profileAv.style.background = theme;
        profileAv.textContent = name.substring(0, 1).toUpperCase();
        profileName.textContent = name;
        profileOverlay.classList.add('open');
    };
    if (profileClose) profileClose.addEventListener('click', function () { profileOverlay.classList.remove('open'); });
    if (profileOverlay) {
        profileOverlay.addEventListener('click', function (e) { if (e.target === profileOverlay) profileOverlay.classList.remove('open'); });
    }
})();
</script>
