{{-- AI Tutor: floating chat bubble + highlight-to-ask popover.
     Include once near the end of <body>. Requires the csrf-token meta tag. --}}

<style>
    /* ── Floating bubble ─────────────────────────────────────────────── */
    .ai-fab {
        position: fixed;
        bottom: 26px;
        right: 26px;
        width: 58px;
        height: 58px;
        border-radius: 50%;
        border: none;
        background: linear-gradient(135deg, #7B1D1D, #a83232);
        color: #fff;
        font-size: 22px;
        cursor: pointer;
        box-shadow: 0 6px 20px rgba(123, 29, 29, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2500;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .ai-fab:hover { transform: scale(1.08); box-shadow: 0 8px 26px rgba(123, 29, 29, 0.5); }
    .ai-fab .fa-xmark { display: none; }
    .ai-fab.open .fa-robot { display: none; }
    .ai-fab.open .fa-xmark { display: block; }

    /* ── Chat panel ──────────────────────────────────────────────────── */
    .ai-panel {
        position: fixed;
        bottom: 96px;
        right: 26px;
        width: 370px;
        max-width: calc(100vw - 32px);
        height: 520px;
        max-height: calc(100vh - 130px);
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 14px 45px rgba(0, 0, 0, 0.22);
        display: none;
        flex-direction: column;
        overflow: hidden;
        z-index: 2500;
        font-family: 'Poppins', sans-serif;
    }
    .ai-panel.open { display: flex; animation: aiPanelUp 0.22s ease; }
    @keyframes aiPanelUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }

    .ai-head {
        background: linear-gradient(135deg, #7B1D1D, #a83232);
        color: #fff;
        padding: 15px 18px;
        display: flex;
        align-items: center;
        gap: 11px;
        flex-shrink: 0;
    }
    .ai-head .ai-avatar {
        width: 36px; height: 36px; border-radius: 50%;
        background: rgba(255,255,255,0.18);
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; flex-shrink: 0;
    }
    .ai-head .t { font-size: 14px; font-weight: 600; line-height: 1.2; }
    .ai-head .s { font-size: 11px; opacity: 0.85; }
    .ai-head .ai-clear {
        margin-left: auto; background: none; border: none; color: rgba(255,255,255,0.8);
        font-size: 13px; cursor: pointer; padding: 6px; border-radius: 6px;
    }
    .ai-head .ai-clear:hover { background: rgba(255,255,255,0.15); color: #fff; }

    .ai-body {
        flex: 1;
        overflow-y: auto;
        padding: 16px 14px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        background: #faf9f8;
    }

    .ai-msg {
        max-width: 85%;
        padding: 10px 14px;
        border-radius: 14px;
        font-size: 12.5px;
        line-height: 1.65;
        white-space: pre-wrap;
        word-wrap: break-word;
        overflow-wrap: anywhere;
    }
    .ai-msg.user {
        align-self: flex-end;
        background: #7B1D1D;
        color: #fff;
        border-bottom-right-radius: 4px;
    }
    .ai-msg.bot {
        align-self: flex-start;
        background: #fff;
        color: #333;
        border: 1px solid #eee;
        border-bottom-left-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .ai-msg.bot code {
        background: #f3eeee; color: #7B1D1D;
        padding: 1px 5px; border-radius: 4px;
        font-size: 11.5px;
    }
    .ai-msg.bot .ai-heading { color: #7B1D1D; font-size: 13px; }
    .ai-msg.bot .ai-divider {
        display: block; border-top: 1px solid #eee;
        margin: 2px 0; height: 1px;
    }
    .ai-msg .ai-provider {
        display: block; margin-top: 7px;
        font-size: 9.5px; color: #b5b5b5; letter-spacing: 0.3px;
    }

    .ai-welcome {
        text-align: center; color: #999; font-size: 12px;
        padding: 24px 18px; line-height: 1.7;
    }
    .ai-welcome i { font-size: 26px; color: #d8c4c4; display: block; margin-bottom: 10px; }
    .ai-welcome b { color: #7B1D1D; }

    .ai-typing {
        align-self: flex-start; background: #fff; border: 1px solid #eee;
        border-radius: 14px; border-bottom-left-radius: 4px;
        padding: 12px 16px; display: flex; gap: 5px;
    }
    .ai-typing span {
        width: 7px; height: 7px; border-radius: 50%; background: #c9a3a3;
        animation: aiBounce 1.2s infinite ease-in-out;
    }
    .ai-typing span:nth-child(2) { animation-delay: 0.15s; }
    .ai-typing span:nth-child(3) { animation-delay: 0.3s; }
    @keyframes aiBounce { 0%, 60%, 100% { transform: translateY(0); opacity: .5; } 30% { transform: translateY(-5px); opacity: 1; } }

    .ai-foot {
        display: flex; gap: 8px; padding: 12px;
        border-top: 1px solid #f0f0f0; background: #fff; flex-shrink: 0;
    }
    .ai-foot textarea {
        flex: 1; resize: none; border: 1px solid #e4e4e4; border-radius: 22px;
        padding: 10px 16px; font-size: 12.5px; font-family: 'Poppins', sans-serif;
        color: #444; max-height: 90px; line-height: 1.4; outline: none;
    }
    .ai-foot textarea:focus { border-color: #7B1D1D; }
    .ai-send {
        width: 40px; height: 40px; border-radius: 50%; border: none;
        background: #7B1D1D; color: #fff; font-size: 14px; cursor: pointer;
        flex-shrink: 0; align-self: flex-end; transition: background 0.2s;
    }
    .ai-send:hover { background: #6a1818; }
    .ai-send:disabled { opacity: 0.5; cursor: not-allowed; }

    /* ── Highlight popover ───────────────────────────────────────────── */
    .ai-selection-pop {
        position: absolute;
        display: none;
        gap: 0;
        background: #2b2b2b;
        border-radius: 9px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        z-index: 2600;
        overflow: hidden;
    }
    .ai-selection-pop.show { display: flex; animation: aiPopIn 0.15s ease; }
    @keyframes aiPopIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
    .ai-selection-pop button {
        background: none; border: none; color: #eee;
        font-size: 12px; font-family: 'Poppins', sans-serif;
        padding: 9px 14px; cursor: pointer;
        display: flex; align-items: center; gap: 7px;
        transition: background 0.15s;
    }
    .ai-selection-pop button:hover { background: rgba(255,255,255,0.12); }
    .ai-selection-pop button + button { border-left: 1px solid rgba(255,255,255,0.14); }
    .ai-selection-pop .ask i { color: #f5b0b0; }
    .ai-selection-pop .srch i { color: #9ec3f5; }

    @media (max-width: 768px) {
        .ai-fab { bottom: 84px; right: 18px; }       /* clear the mobile bottom nav */
        .ai-panel { bottom: 150px; right: 16px; height: 440px; max-height: calc(100vh - 180px); }
    }
</style>

{{-- Highlight popover --}}
<div class="ai-selection-pop" id="aiSelectionPop">
    <button type="button" class="ask" id="aiPopAsk"><i class="fas fa-wand-magic-sparkles"></i> Ask AI</button>
    <button type="button" class="srch" id="aiPopSearch"><i class="fas fa-magnifying-glass"></i> Search</button>
</div>

{{-- Chat panel --}}
<div class="ai-panel" id="aiPanel">
    <div class="ai-head">
        <div class="ai-avatar"><i class="fas fa-robot"></i></div>
        <div>
            <div class="t">CPACE AI Tutor</div>
            <div class="s">Ask anything from your review notes</div>
        </div>
        <button type="button" class="ai-clear" id="aiClearBtn" title="Clear conversation"><i class="fas fa-rotate-left"></i></button>
    </div>
    <div class="ai-body" id="aiBody">
        <div class="ai-welcome" id="aiWelcome">
            <i class="fas fa-highlighter"></i>
            Hi! I'm your <b>AI study tutor</b>.<br>
            Highlight any word or phrase in your notes and tap <b>Ask AI</b> for the rationale — or just type a question below.
        </div>
    </div>
    <div class="ai-foot">
        <textarea id="aiInput" rows="1" placeholder="Ask a question..." maxlength="4000"></textarea>
        <button type="button" class="ai-send" id="aiSendBtn"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

{{-- Floating bubble --}}
<button type="button" class="ai-fab" id="aiFab" title="AI Tutor">
    <i class="fas fa-robot"></i>
    <i class="fas fa-xmark"></i>
</button>

<script>
(function () {
    const CHAT_URL   = "{{ route('ai-tutor.chat') }}";
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const fab      = document.getElementById('aiFab');
    const panel    = document.getElementById('aiPanel');
    const body     = document.getElementById('aiBody');
    const welcome  = document.getElementById('aiWelcome');
    const input    = document.getElementById('aiInput');
    const sendBtn  = document.getElementById('aiSendBtn');
    const clearBtn = document.getElementById('aiClearBtn');
    const pop      = document.getElementById('aiSelectionPop');

    // Conversation history sent to the backend each turn (role: user|assistant).
    let history = [];
    let waiting = false;

    // ── Panel open/close ────────────────────────────────────────────────
    function openPanel() {
        panel.classList.add('open');
        fab.classList.add('open');
        setTimeout(() => input.focus(), 120);
    }
    function closePanel() {
        panel.classList.remove('open');
        fab.classList.remove('open');
    }
    fab.addEventListener('click', () => panel.classList.contains('open') ? closePanel() : openPanel());

    clearBtn.addEventListener('click', () => {
        history = [];
        body.querySelectorAll('.ai-msg, .ai-typing').forEach(el => el.remove());
        welcome.style.display = '';
    });

    // ── Rendering ───────────────────────────────────────────────────────
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Minimal markdown: ### headings, --- dividers, * bullets, **bold**,
    // *italic*, `code`. Everything else is escaped; white-space: pre-wrap
    // preserves line breaks and list indentation.
    function renderMarkdown(str) {
        return escapeHtml(str)
            .replace(/^#{1,6}\s+(.+)$/gm, '<b class="ai-heading">$1</b>')
            .replace(/^\s*[-*_]{3,}\s*$/gm, '<span class="ai-divider"></span>')
            .replace(/^(\s*)[*+]\s+/gm, '$1• ')
            .replace(/\*\*(.+?)\*\*/g, '<b>$1</b>')
            .replace(/`([^`]+)`/g, '<code>$1</code>')
            .replace(/(^|\s)\*([^*\n]+)\*(?=\s|[.,;:!?]|$)/g, '$1<i>$2</i>');
    }

    function addMessage(role, text, provider) {
        welcome.style.display = 'none';
        const el = document.createElement('div');
        el.className = 'ai-msg ' + (role === 'user' ? 'user' : 'bot');
        el.innerHTML = role === 'user' ? escapeHtml(text) : renderMarkdown(text);
        if (role === 'assistant' && provider) {
            const tag = document.createElement('span');
            tag.className = 'ai-provider';
            tag.textContent = provider === 'gemini' ? 'via Gemini' : 'via OpenRouter';
            el.appendChild(tag);
        }
        body.appendChild(el);
        body.scrollTop = body.scrollHeight;
        return el;
    }

    function showTyping() {
        const el = document.createElement('div');
        el.className = 'ai-typing';
        el.innerHTML = '<span></span><span></span><span></span>';
        body.appendChild(el);
        body.scrollTop = body.scrollHeight;
        return el;
    }

    // ── Sending ─────────────────────────────────────────────────────────
    async function send(text) {
        text = (text || '').trim();
        if (!text || waiting) return;

        waiting = true;
        sendBtn.disabled = true;
        addMessage('user', text);
        history.push({ role: 'user', content: text });

        const typing = showTyping();
        try {
            const res = await fetch(CHAT_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                // Keep the payload bounded: only the last 20 turns matter.
                body: JSON.stringify({ messages: history.slice(-20) }),
            });
            const data = await res.json().catch(() => ({}));
            typing.remove();

            if (!res.ok || !data.ok) {
                addMessage('assistant', data.message || 'Sorry, something went wrong. Please try again.');
            } else {
                history.push({ role: 'assistant', content: data.reply });
                addMessage('assistant', data.reply, data.provider);
            }
        } catch (e) {
            typing.remove();
            addMessage('assistant', 'Could not reach the AI tutor. Check your connection and try again.');
        } finally {
            waiting = false;
            sendBtn.disabled = false;
        }
    }

    sendBtn.addEventListener('click', () => { const t = input.value; input.value = ''; autoGrow(); send(t); });
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            const t = input.value; input.value = ''; autoGrow(); send(t);
        }
    });
    function autoGrow() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 90) + 'px';
    }
    input.addEventListener('input', autoGrow);

    // ── Highlight-to-ask popover ────────────────────────────────────────
    let selectedText = '';

    function hidePop() { pop.classList.remove('show'); }

    document.addEventListener('mouseup', (e) => {
        // Ignore selections made inside the chat widget or the popover itself.
        if (panel.contains(e.target) || pop.contains(e.target) || fab.contains(e.target)) return;
        if (e.target.closest('input, textarea, select')) return;

        // Let the browser finalize the selection first.
        setTimeout(() => {
            const sel  = window.getSelection();
            const text = sel ? sel.toString().trim() : '';
            if (!text || text.length < 2 || text.length > 1500 || sel.rangeCount === 0) { hidePop(); return; }

            selectedText = text;
            const rect = sel.getRangeAt(0).getBoundingClientRect();
            pop.classList.add('show');
            const popW = pop.offsetWidth, popH = pop.offsetHeight;
            let left = rect.left + window.scrollX + rect.width / 2 - popW / 2;
            left = Math.max(8, Math.min(left, window.scrollX + document.documentElement.clientWidth - popW - 8));
            let top = rect.top + window.scrollY - popH - 8;
            if (top < window.scrollY + 4) top = rect.bottom + window.scrollY + 8;
            pop.style.left = left + 'px';
            pop.style.top  = top + 'px';
        }, 10);
    });

    document.addEventListener('mousedown', (e) => { if (!pop.contains(e.target)) hidePop(); });
    document.addEventListener('scroll', hidePop, true);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { hidePop(); closePanel(); } });

    document.getElementById('aiPopAsk').addEventListener('click', () => {
        const text = selectedText;
        hidePop();
        window.getSelection()?.removeAllRanges();
        openPanel();
        send('Please explain this from my CPA review notes and give the rationale behind it:\n\n"' + text + '"');
    });

    document.getElementById('aiPopSearch').addEventListener('click', () => {
        const text = selectedText;
        hidePop();
        window.open('https://www.google.com/search?q=' + encodeURIComponent(text), '_blank', 'noopener');
    });
})();
</script>
