<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $quiz->title }} - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-light:#f5e8e8; --accent:#c0392b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#1f2430; }
        .top { position:sticky; top:0; z-index:50; background:var(--primary); color:#fff; padding:14px 20px; box-shadow:0 4px 16px rgba(0,0,0,.15); }
        .top-in { max-width:820px; margin:0 auto; display:flex; align-items:center; gap:14px; }
        .top-title { flex:1; min-width:0; }
        .top-title b { display:block; font-size:15px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .top-title span { font-size:11px; opacity:.75; }
        .timer { display:inline-flex; align-items:center; gap:7px; background:rgba(255,255,255,.18); border-radius:20px; padding:7px 14px; font-weight:700; font-size:14px; }
        .timer.warn { background:#d97706; }
        .timer.danger { background:var(--accent); animation:blink 1s infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.45} }
        .prog { height:4px; background:rgba(255,255,255,.2); margin-top:12px; border-radius:2px; overflow:hidden; }
        .prog span { display:block; height:100%; background:#fff; width:0; transition:width .3s; }

        .wrap { max-width:820px; margin:0 auto; padding:22px 16px 120px; }
        .q { background:#fff; border-radius:16px; padding:22px 24px; margin-bottom:14px; box-shadow:0 1px 6px rgba(0,0,0,.05); scroll-margin-top:110px; }
        .q-label { font-size:10.5px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px; display:flex; justify-content:space-between; }
        .q-text { font-size:15.5px; font-weight:500; line-height:1.65; color:#111; margin-bottom:14px; }
        .choices { display:flex; flex-direction:column; gap:9px; }
        .choice { display:flex; align-items:center; gap:13px; padding:13px 15px; border:2px solid #e5e7eb; border-radius:13px; cursor:pointer; transition:all .15s; user-select:none; }
        .choice:hover { border-color:#d9b8b8; }
        .choice input { display:none; }
        .choice.selected { border-color:var(--primary); background:var(--primary-light); }
        .letter { width:32px; height:32px; border-radius:50%; background:#f3f4f6; border:2px solid #e5e7eb; display:flex; align-items:center; justify-content:center; font-size:12.5px; font-weight:700; color:#555; flex-shrink:0; }
        .choice.selected .letter { background:var(--primary); border-color:var(--primary); color:#fff; }
        .ctext { font-size:14px; line-height:1.5; }

        .bottom { position:fixed; bottom:0; left:0; right:0; background:#fff; border-top:1px solid #e8e8e8; box-shadow:0 -4px 16px rgba(0,0,0,.06); padding:12px 16px; z-index:40; }
        .bottom-in { max-width:820px; margin:0 auto; display:flex; align-items:center; gap:12px; }
        .count { flex:1; font-size:13px; color:#666; }
        .count b { color:var(--primary); }
        .btn { display:inline-flex; align-items:center; gap:8px; padding:13px 22px; border-radius:12px; border:none; font-size:14px; font-weight:700; font-family:'Poppins',sans-serif; cursor:pointer; background:var(--primary); color:#fff; transition:all .2s; }
        .btn:hover { background:#6a1818; }
        .btn:disabled { opacity:.6; cursor:wait; }
    </style>
</head>
<body>

<div class="top">
    <div class="top-in">
        <div class="top-title">
            <b>{{ $quiz->title }}</b>
            <span>{{ $quiz->subject?->code ?? 'Class quiz' }} · {{ $items->count() }} questions · {{ $items->sum('points') }} points</span>
        </div>
        @if($secondsLeft !== null)
            <div class="timer" id="timer"><i class="fas fa-clock"></i><span id="timerText">--:--</span></div>
        @endif
    </div>
    <div class="top-in" style="display:block;"><div class="prog"><span id="progFill"></span></div></div>
</div>

<div class="wrap">
    <form method="POST" action="{{ route('class-quiz.submit', $quiz->share_token) }}" id="quizForm">
        @csrf
        @foreach($items as $i => $item)
            <div class="q" id="q-{{ $i }}">
                <div class="q-label">
                    <span>Question {{ $i + 1 }} of {{ $items->count() }}</span>
                    <span>{{ $item->points }} pt{{ $item->points === 1 ? '' : 's' }}</span>
                </div>
                <div class="q-text">{{ $item->question_text }}</div>
                <div class="choices">
                    @foreach($item->choices as $choice)
                        <label class="choice">
                            <input type="radio" name="answers[{{ $item->id }}]" value="{{ $choice['label'] }}">
                            <span class="letter">{{ $choice['label'] }}</span>
                            <span class="ctext">{{ $choice['text'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </form>
</div>

<div class="bottom">
    <div class="bottom-in">
        <div class="count"><b id="answered">0</b> of {{ $items->count() }} answered</div>
        <button type="button" class="btn" id="submitBtn"><i class="fas fa-paper-plane"></i> Submit quiz</button>
    </div>
</div>

<script>
(function () {
    const TOTAL = {{ $items->count() }};
    const form = document.getElementById('quizForm');
    const submitBtn = document.getElementById('submitBtn');
    let submitting = false;

    function refresh() {
        const answered = new Set();
        form.querySelectorAll('input[type=radio]:checked').forEach(r => answered.add(r.name));
        document.getElementById('answered').textContent = answered.size;
        document.getElementById('progFill').style.width = (TOTAL ? answered.size / TOTAL * 100 : 0) + '%';
    }
    form.addEventListener('change', ev => {
        const choice = ev.target.closest('.choice'); if (!choice) return;
        choice.closest('.choices').querySelectorAll('.choice').forEach(c => c.classList.remove('selected'));
        choice.classList.add('selected');
        refresh();
    });

    function doSubmit() {
        if (submitting) return;
        submitting = true;
        submitBtn.disabled = true;
        window.onbeforeunload = null;
        form.submit();
    }

    submitBtn.addEventListener('click', async () => {
        const left = TOTAL - parseInt(document.getElementById('answered').textContent, 10);
        const text = left > 0 ? `You still have ${left} unanswered question${left === 1 ? '' : 's'}. Unanswered items are marked wrong.` : 'You cannot change your answers after submitting.';
        let ok = true;
        if (window.CPACE?.confirm) ok = await CPACE.confirm({ title: 'Submit your quiz?', text, icon: 'question', confirmText: 'Yes, submit' });
        else ok = confirm(text);
        if (ok) doSubmit();
    });

    window.onbeforeunload = () => 'Your quiz is still in progress.';

    /* Countdown to the time limit or the deadline, whichever is sooner. */
    @if($secondsLeft !== null)
    let left = {{ (int) $secondsLeft }};
    const timerEl = document.getElementById('timer');
    const timerText = document.getElementById('timerText');
    function tick() {
        const m = Math.floor(left / 60), s = left % 60;
        timerText.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        timerEl.classList.toggle('warn', left <= 120 && left > 30);
        timerEl.classList.toggle('danger', left <= 30);
        if (left <= 0) { doSubmit(); return; }
        left--;
        setTimeout(tick, 1000);
    }
    tick();
    @endif
})();
</script>

@include('partials.alerts')
</body>
</html>
