<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review TOS Import - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .imp-head { background:#fff;border:1px solid #e8e8e8;border-radius:14px;padding:16px 18px;margin-bottom:16px;display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;align-items:center; }
        .imp-head .meta { font-size:12px;color:#666;line-height:1.6; }
        .imp-head .meta strong { color:#1a1a1a; }
        .subj-tabs { display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px; }
        .subj-tab { border:1.5px solid #e2e2e6;background:#fff;border-radius:10px;padding:8px 12px;font:600 12px 'Poppins',sans-serif;cursor:pointer;display:inline-flex;align-items:center;gap:8px; }
        .subj-tab.active { border-color:var(--primary);color:var(--primary);background:var(--primary-light); }
        .subj-tab input { width:15px;height:15px;accent-color:var(--primary); }
        .subj-tab .count { font-size:10px;color:#999;font-weight:500; }
        .panel { display:none;background:#fff;border:1px solid #e8e8e8;border-radius:14px;overflow:hidden; }
        .panel.active { display:block; }
        .panel-bar { display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;padding:12px 16px;border-bottom:1px solid #f0f0f0;font-size:11.5px;color:#666; }
        .panel-bar .levels { display:flex;gap:6px;align-items:center;flex-wrap:wrap; }
        .lvl-btn { border:1px solid #e2e2e6;background:#fafafa;border-radius:7px;padding:4px 9px;font:600 10.5px 'Poppins',sans-serif;cursor:pointer; }
        .lvl-btn:hover { border-color:var(--primary);color:var(--primary); }
        .check-ok { color:#047857;font-weight:600; } .check-warn { color:#b45309;font-weight:600; }
        .rows { max-height:62vh;overflow:auto;padding:8px 12px; }
        .row { display:flex;align-items:center;gap:8px;padding:4px 6px;border-radius:7px; }
        .row:hover { background:#fafafa; }
        .row.excluded { opacity:.45; }
        .row input[type=checkbox] { width:15px;height:15px;flex-shrink:0;accent-color:var(--primary); }
        .row .ref { font-size:10px;color:#999;font-weight:700;min-width:44px;flex-shrink:0; }
        .row input[type=text] { flex:1;min-width:0;border:1px solid transparent;background:transparent;border-radius:6px;padding:5px 7px;font:12px 'Poppins',sans-serif; }
        .row input[type=text]:hover, .row input[type=text]:focus { border-color:#e2e2e6;background:#fff;outline:none; }
        .row.depth-0 input[type=text] { font-weight:700; }
        .row .tos { font-size:10px;color:#b45309;font-weight:600;white-space:nowrap;flex-shrink:0; }
        .existing-note { margin:10px 16px 0;padding:9px 12px;background:#fffbeb;border-radius:8px;font-size:11px;color:#92400e; }
        .actions { display:flex;justify-content:flex-end;gap:9px;margin-top:16px;flex-wrap:wrap; }
        @media(max-width:620px) { .row .ref { min-width:30px; } .row .tos { display:none; } }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'subjects'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left"><div><div class="page-title">Review TOS Import</div><div class="page-sub">Check the outline read from the PDF before it becomes curriculum topics.</div></div></div>
        <div class="topbar-right">@include('partials.topbar-actions')</div>
    </div>

    <div class="imp-head">
        <div class="meta">
            <div><i class="fas fa-file-pdf" style="color:#c0392b;"></i> <strong>{{ $batch->original_filename }}</strong></div>
            <div>Importing into <strong>{{ $version->label }}</strong> ({{ $version->status }}). Found <strong>{{ $subjects->count() }}</strong> subject(s), <strong>{{ $batch->items->count() }}</strong> headings.</div>
            <div>Tick subjects to import · untick headings you don't want · fix names if needed.<x-tip>An unticked heading's subtopics move up to the nearest ticked one.</x-tip></div>
        </div>
        <a href="{{ route('chair.subjects', ['version' => $version->id]) }}" class="btn btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back to curriculum</a>
    </div>

    <div class="subj-tabs" role="tablist">
        @foreach($subjects as $i => $subject)
            @php $hasExisting = ($existingTopics[$subject->id] ?? 0) > 0; @endphp
            <label class="subj-tab {{ $i === 0 ? 'active' : '' }}" data-tab="{{ $subject->id }}" onclick="showPanel({{ $subject->id }})">
                <input type="checkbox" class="subject-pick" value="{{ $subject->id }}" @checked(! $hasExisting) onclick="event.stopPropagation()">
                {{ $subject->code }} <span class="count">{{ $itemsBySubject[$subject->id]->count() }}</span>
            </label>
        @endforeach
    </div>

    @foreach($subjects as $i => $subject)
        @php
            $items = $itemsBySubject[$subject->id];
            $topWeight = round($items->where('depth', 0)->sum('weight_percent'), 2);
            $existing = $existingTopics[$subject->id] ?? 0;
        @endphp
        <div class="panel {{ $i === 0 ? 'active' : '' }}" data-panel="{{ $subject->id }}">
            <div class="panel-bar">
                <div>
                    <strong style="color:#1a1a1a;">{{ $subject->code }}</strong> — {{ $subject->name }} ·
                    @if(abs($topWeight - 100) < 0.5)
                        <span class="check-ok"><i class="fas fa-circle-check"></i> Area weights total {{ $topWeight }}%</span>
                    @else
                        <span class="check-warn"><i class="fas fa-triangle-exclamation"></i> Area weights total {{ $topWeight }}% — check the outline</span>
                    @endif
                </div>
                <div class="levels">
                    Include levels:
                    @foreach([1 => 'Areas only', 2 => 'Up to 2', 3 => 'Up to 3'] as $lvl => $label)
                        <button type="button" class="lvl-btn" onclick="includeUpTo({{ $subject->id }}, {{ $lvl }})">{{ $label }}</button>
                    @endforeach
                    <button type="button" class="lvl-btn" onclick="includeUpTo({{ $subject->id }}, 99)">All</button>
                </div>
            </div>
            @if($existing > 0)
                <div class="existing-note"><i class="fas fa-circle-info"></i> {{ $subject->code }} already has {{ $existing }} topic(s), so it starts unticked.<x-tip>If you import it, topics with the same name under the same parent are reused, not duplicated.</x-tip></div>
            @endif
            <div class="rows">
                @foreach($items as $item)
                    <div class="row depth-{{ min($item->depth, 3) }}" data-item="{{ $item->id }}" data-depth="{{ $item->depth }}" style="padding-left:{{ 6 + $item->depth * 20 }}px;">
                        <input type="checkbox" class="inc" @checked($item->included) onchange="this.closest('.row').classList.toggle('excluded', !this.checked)">
                        <span class="ref">{{ $item->ref }}</span>
                        <input type="text" class="name" value="{{ $item->full_name ?: $item->name }}" maxlength="500" title="{{ $item->full_name }}">
                        @if($item->weight_percent !== null || $item->item_count !== null)
                            <span class="tos">{{ $item->weight_percent !== null ? rtrim(rtrim(number_format($item->weight_percent, 2), '0'), '.') . '%' : '' }}{{ $item->item_count !== null ? ' · ' . $item->item_count . ' items' : '' }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <form method="POST" action="{{ route('chair.curriculum.import.commit', $batch) }}" id="commitForm"
          data-confirm="The ticked headings will be added as topics to {{ $version->label }}. Names longer than 150 characters are shortened, with the full text kept as the topic description."
          data-confirm-title="Import these topics?"
          data-confirm-ok="Yes, import"
          data-confirm-icon="question"
          data-loading="Importing topics...">
        @csrf
        <input type="hidden" name="payload" id="payload">
        <div class="actions">
            <button type="button" class="btn btn-ghost" onclick="discardImport()"><i class="fas fa-trash"></i> Discard import</button>
            <button class="btn btn-primary"><i class="fas fa-file-import"></i> Import selected topics</button>
        </div>
    </form>
    <form method="POST" action="{{ route('chair.curriculum.import.destroy', $batch) }}" id="discardForm" hidden>@csrf @method('DELETE')</form>
</main>

<script>
function showPanel(id) {
    document.querySelectorAll('.subj-tab').forEach(t => t.classList.toggle('active', t.dataset.tab == id));
    document.querySelectorAll('.panel').forEach(p => p.classList.toggle('active', p.dataset.panel == id));
}

function includeUpTo(subjectId, levels) {
    document.querySelectorAll(`.panel[data-panel="${subjectId}"] .row`).forEach(row => {
        const on = Number(row.dataset.depth) < levels;
        row.querySelector('.inc').checked = on;
        row.classList.toggle('excluded', !on);
    });
    buildPayload();
}

// The review is sent as one JSON field instead of ~1000 form inputs (PHP's
// max_input_vars would silently drop the rest). It's kept current on every
// change, because the confirm dialog re-submits the form programmatically,
// which doesn't fire another submit event.
function buildPayload() {
    const items = {};
    document.querySelectorAll('.row').forEach(row => {
        items[row.dataset.item] = { include: row.querySelector('.inc').checked, name: row.querySelector('.name').value };
    });
    const subjects = Array.from(document.querySelectorAll('.subject-pick:checked')).map(c => Number(c.value));
    document.getElementById('payload').value = JSON.stringify({ subjects, items });
}
document.addEventListener('input', buildPayload);
document.addEventListener('change', buildPayload);
buildPayload();

function discardImport() {
    CPACE.confirm({
        title: 'Discard this import?',
        text: 'The parsed outline will be thrown away. No topics are changed.',
        confirmText: 'Yes, discard',
        danger: true,
    }).then(ok => { if (ok) document.getElementById('discardForm').submit(); });
}
</script>

    @include('partials.alerts')
</body>
</html>
