<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $attempt->student?->first_name }}'s sitting - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.mock-exam-styles')
    <style>
        .split { display:grid; grid-template-columns:1fr 340px; gap:18px; align-items:start; }
        .shots { display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:12px; }
        .shot { border:1px solid var(--line); border-radius:10px; overflow:hidden; background:#fff; }
        .shot.evt { border-color:var(--red); }
        .risk-table { margin-top:10px; font-size:12px; border-collapse:collapse; }
        .risk-table td { padding:3px 18px 3px 0; }
        .risk-table .muted { opacity:.7; }
        .risk-table .risk-total td { border-top:1px solid rgba(0,0,0,.15); padding-top:6px; }
        .shot-img { position:relative; }
        .shot-img .pick { position:absolute; top:7px; left:7px; width:18px; height:18px; cursor:pointer; accent-color:var(--primary); }
        .pick-bar { display:flex; align-items:center; gap:14px; margin-top:12px; padding:9px 12px; background:#f7f8fa;
                    border:1px solid var(--line); border-radius:10px; font-size:12.5px; }
        .pick-all { display:flex; align-items:center; gap:7px; cursor:pointer; font-weight:600; color:var(--ink); }
        .pick-count { color:var(--muted); }
        .shot img { width:100%; aspect-ratio:4/3; object-fit:cover; display:block; background:#101828; }
        .shot-cap { padding:8px 10px; font-size:10.5px; color:var(--muted); }
        .shot-what { font-size:12px; font-weight:700; color:var(--ink); line-height:1.3; }
        .shot.evt .shot-what { color:var(--red); }
        .shot-meta { display:flex; justify-content:space-between; gap:6px; margin-top:3px; }
        .shot-kind { display:inline-flex; align-items:center; gap:5px; font-weight:600; }
        .shot-img img.zoom { cursor:zoom-in; }

        /* full-size viewer */
        .lb { position:fixed; inset:0; z-index:2000; background:rgba(10,12,20,.92); display:none;
              align-items:center; justify-content:center; flex-direction:column; padding:24px; }
        .lb.on { display:flex; }
        .lb img { max-width:min(1200px, 94vw); max-height:78vh; border-radius:8px; background:#101828; }
        .lb-cap { color:#fff; margin-top:14px; text-align:center; font-size:13.5px; }
        .lb-cap b { font-size:15px; }
        .lb-cap small { display:block; margin-top:4px; opacity:.7; font-size:12px; }
        .lb-btn { position:absolute; background:rgba(255,255,255,.14); border:0; color:#fff; width:44px; height:44px;
                  border-radius:50%; font-size:18px; cursor:pointer; }
        .lb-btn:hover { background:rgba(255,255,255,.28); }
        .lb-close { top:18px; right:18px; }
        .lb-prev { left:18px; top:50%; transform:translateY(-50%); }
        .lb-next { right:18px; top:50%; transform:translateY(-50%); }
        .tl { position:relative; padding-left:18px; }
        .tl-row { position:relative; padding:9px 0; border-bottom:1px solid #f0f1f4; font-size:12.5px; }
        .tl-row:last-child { border-bottom:none; }
        .tl-row::before { content:''; position:absolute; left:-13px; top:15px; width:7px; height:7px;
                          border-radius:50%; background:var(--amber); }
        .tl-row.severe::before { background:var(--red); }
        .tl-when { font-size:11px; color:var(--muted); margin-top:2px; }
        @media (max-width: 1000px) { .split { grid-template-columns:1fr; } }
    </style>
</head>
<body>
@if($isChair)
    @include('partials.chair-sidebar', ['active' => 'mock-exams'])
@else
    @include('partials.faculty-sidebar', ['active' => 'mock-exams'])
@endif

<main class="main">
    <div class="topbar">
        <div>
            <a href="{{ $isChair ? route('chair.mock-exams.monitor', $exam) : route('faculty.mock-exams.monitor', $exam) }}"
               style="font-size:12px;color:var(--muted);text-decoration:none;">
                <i class="fas fa-arrow-left"></i> Back to monitor
            </a>
            <div class="page-title" style="margin-top:4px;">{{ $attempt->student?->first_name }} {{ $attempt->student?->last_name }}</div>
            <div class="page-sub">
                {{ $exam->title }} ·
                started {{ $attempt->started_at?->format('g:i A') }}
                @if($attempt->submitted_at) · submitted {{ $attempt->submitted_at->format('g:i A') }} @endif
                @if($attempt->is_late) · <span class="chip chip-late">late</span> @endif
            </div>
        </div>
        <div class="topbar-right">@include('partials.topbar-actions')</div>
    </div>

    @if($autoClosed)
        <div class="banner banner-warn">
            <i class="fas fa-clock-rotate-left"></i>
            <div>
                <strong>Closed automatically.</strong>
                This student did not press Submit before their time ran out. The server graded the answers saved by autosave,
                and any question they hadn't reached counts as unanswered.
            </div>
        </div>
    @endif

    @if($attempt->flag_count > 0)
        <div class="banner banner-danger">
            <i class="fas fa-flag"></i>
            <div>
                <strong>{{ $risk['label'] }} — {{ $attempt->flag_count }} {{ Str::plural('flag', $attempt->flag_count) }} raised during this sitting.</strong>
                Flags are signals, not proof — review the timeline and captures before drawing a conclusion.
                <table class="risk-table">
                    @foreach($risk['rows'] as $row)
                        <tr>
                            <td>{{ $row['label'] }}</td>
                            <td>×{{ $row['count'] }}@if($row['count'] > $row['counted']) <span class="muted">(first {{ $row['counted'] }} counted)</span>@endif</td>
                            <td><strong>{{ $row['points'] }} pts</strong></td>
                        </tr>
                    @endforeach
                    <tr class="risk-total"><td colspan="2">Weighted score (Medium from {{ \App\Support\ProctorRisk::MEDIUM_FROM }}, High from {{ \App\Support\ProctorRisk::HIGH_FROM }})</td><td><strong>{{ $risk['score'] }}</strong></td></tr>
                </table>
            </div>
        </div>
    @endif

    <div class="split">
        <div>
            <div class="card">
                <div class="card-title"><i class="fas fa-chart-simple"></i> Result</div>
                <div style="display:flex;gap:28px;margin-top:14px;flex-wrap:wrap;">
                    <div>
                        <div style="font-size:30px;font-weight:700;font-family:'Montserrat',sans-serif;color:var(--ink);">
                            {{ $attempt->isSubmitted() ? number_format((float) $attempt->percent, 1) . '%' : '—' }}
                        </div>
                        <div style="font-size:11.5px;color:var(--muted);">Score</div>
                    </div>
                    <div>
                        <div style="font-size:30px;font-weight:700;font-family:'Montserrat',sans-serif;color:var(--ink);">
                            {{ $attempt->score }}/{{ $attempt->total_points ?: $items->count() }}
                        </div>
                        <div style="font-size:11.5px;color:var(--muted);">Points</div>
                    </div>
                    <div>
                        <div style="font-size:30px;font-weight:700;font-family:'Montserrat',sans-serif;color:var(--ink);">
                            {{ is_array($attempt->answers) ? count(array_filter($attempt->answers)) : 0 }}
                        </div>
                        <div style="font-size:11.5px;color:var(--muted);">Answered</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-title"><i class="fas fa-images"></i> Captures ({{ $attempt->captures->count() }})</div>
                <div class="card-sub">
                    Red-bordered frames were taken because something was flagged. Recordings are deleted automatically
                    {{ \App\Models\MockExamProctorCapture::RETENTION_DAYS }} days after the exam.
                </div>
                @php $canDelete = $attempt->isSubmitted() && $attempt->captures->isNotEmpty(); @endphp
                <form method="POST" action="{{ route('mock-exams.captures.destroy', $attempt) }}" id="deleteForm"
                      onsubmit="return confirm('Delete the selected recordings? The flag timeline is kept, but the images cannot be recovered.');">
                    @csrf
                    @method('DELETE')

                    @if($canDelete)
                        <div class="pick-bar">
                            <label class="pick-all"><input type="checkbox" id="pickAll"> Select all</label>
                            <span class="pick-count" id="pickCount">None selected</span>
                            <button type="submit" class="btn btn-ghost" id="pickDelete" style="font-size:12px;margin-left:auto;" disabled>
                                <i class="fas fa-trash"></i> Delete selected
                            </button>
                        </div>
                    @elseif($attempt->captures->isNotEmpty())
                        <div class="card-sub" style="margin-top:8px;">Recordings can be deleted once the student has submitted.</div>
                    @endif

                <div class="shots" style="margin-top:14px;">
                    @forelse($attempt->captures as $capture)
                        <div class="shot {{ $capture->isEventTriggered() ? 'evt' : '' }}">
                            @php $isCamera = $capture->kind === \App\Models\MockExamProctorCapture::KIND_CAMERA; @endphp
                            <div class="shot-img">
                                <img src="{{ route('mock-exams.capture', $capture) }}" alt="{{ $capture->reasonLabel() }}" loading="lazy"
                                     class="zoom" title="Click to enlarge"
                                     data-title="{{ $capture->reasonLabel() }}"
                                     data-kind="{{ $isCamera ? 'Camera' : 'Screen' }}"
                                     data-time="{{ $capture->captured_at?->format('g:i:s A') }}">
                                @if($canDelete)
                                    <input type="checkbox" class="pick" name="capture_ids[]" value="{{ $capture->id }}"
                                           aria-label="Select this recording">
                                @endif
                            </div>
                            <div class="shot-cap">
                                <div class="shot-what">{{ $capture->reasonLabel() }}</div>
                                <div class="shot-meta">
                                    <span class="shot-kind"><i class="fas {{ $isCamera ? 'fa-video' : 'fa-display' }}"></i> {{ $isCamera ? 'Camera' : 'Screen' }}</span>
                                    <span>{{ $capture->captured_at?->format('g:i:s A') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty" style="grid-column:1/-1;">
                            <i class="fas fa-camera-rotate"></i>
                            <h3>No captures</h3>
                            <p>Either the sitting hasn't started, or the student never granted camera/screen access.</p>
                        </div>
                    @endforelse
                </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-title"><i class="fas fa-flag"></i> Flag timeline</div>
            <div class="card-sub">Everything the runner detected, in order.</div>
            <div class="tl" style="margin-top:14px;max-height:560px;overflow-y:auto;">
                @forelse($attempt->proctorEvents as $event)
                    <div class="tl-row {{ $event->isSevere() ? 'severe' : '' }}">
                        <div style="font-weight:600;color:var(--ink);">{{ $event->label() }}</div>
                        @if($event->meta)<div style="font-size:11.5px;color:var(--muted);">{{ $event->meta }}</div>@endif
                        <div class="tl-when">{{ $event->occurred_at?->format('g:i:s A') }}</div>
                    </div>
                @empty
                    <div style="font-size:12.5px;color:var(--muted);">No flags — clean sitting.</div>
                @endforelse
            </div>
        </div>
    </div>
</main>

@include('partials.alerts')

{{-- Full-size viewer: click any recording; arrows / arrow keys step through them, Esc closes. --}}
<div class="lb" id="lb" role="dialog" aria-modal="true" aria-label="Recording viewer">
    <button type="button" class="lb-btn lb-close" id="lbClose" aria-label="Close"><i class="fas fa-xmark"></i></button>
    <button type="button" class="lb-btn lb-prev" id="lbPrev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
    <button type="button" class="lb-btn lb-next" id="lbNext" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
    <img id="lbImg" alt="">
    <div class="lb-cap"><b id="lbTitle"></b><small id="lbMeta"></small></div>
</div>

<script>
(function () {
    const frames = Array.from(document.querySelectorAll('img.zoom'));
    if (!frames.length) return;
    const lb = document.getElementById('lb');
    const img = document.getElementById('lbImg');
    let at = 0;

    function show(i) {
        at = (i + frames.length) % frames.length;
        const f = frames[at];
        img.src = f.src;
        img.alt = f.dataset.title;
        document.getElementById('lbTitle').textContent = f.dataset.title;
        document.getElementById('lbMeta').textContent =
            f.dataset.kind + ' · ' + f.dataset.time + ' · ' + (at + 1) + ' of ' + frames.length;
    }
    function open(i) { show(i); lb.classList.add('on'); }
    function close() { lb.classList.remove('on'); img.removeAttribute('src'); }

    frames.forEach((f, i) => f.addEventListener('click', () => open(i)));
    document.getElementById('lbClose').addEventListener('click', close);
    document.getElementById('lbPrev').addEventListener('click', () => show(at - 1));
    document.getElementById('lbNext').addEventListener('click', () => show(at + 1));
    // Clicking the dark backdrop (not the picture or buttons) also closes it.
    lb.addEventListener('click', e => { if (e.target === lb) close(); });
    document.addEventListener('keydown', e => {
        if (!lb.classList.contains('on')) return;
        if (e.key === 'Escape') close();
        else if (e.key === 'ArrowLeft') show(at - 1);
        else if (e.key === 'ArrowRight') show(at + 1);
    });
})();
</script>

<script>
(function () {
    const all = document.getElementById('pickAll');
    if (!all) return;
    const boxes = Array.from(document.querySelectorAll('.pick'));
    const count = document.getElementById('pickCount');
    const btn = document.getElementById('pickDelete');

    function sync() {
        const n = boxes.filter(b => b.checked).length;
        count.textContent = n === 0 ? 'None selected' : n + ' of ' + boxes.length + ' selected';
        btn.disabled = n === 0;
        all.checked = n > 0 && n === boxes.length;
        all.indeterminate = n > 0 && n < boxes.length;
    }

    all.addEventListener('change', () => { boxes.forEach(b => { b.checked = all.checked; }); sync(); });
    boxes.forEach(b => b.addEventListener('change', sync));
    sync();
})();
</script>
</body>
</html>
