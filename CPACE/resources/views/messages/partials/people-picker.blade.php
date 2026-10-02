{{--
    A people list for Messages, sorted out by who they are.
      mode "dm":    each row starts a one-to-one chat when clicked.
      mode "group": each row is a checkbox that adds the person to a new group.
    Tabs filter by role (All / Faculty / Students / Alumni / Staff), search
    narrows by name or email, and "All" shows a heading above each role.

    Expects: $people (rows from ChatController::contactRows), $mode, $id.
--}}
@php
    $roleLabels = ['faculty' => 'Faculty', 'student' => 'Students', 'alumni' => 'Alumni', 'staff' => 'Staff'];
    $roleIcons = ['faculty' => 'fa-chalkboard-user', 'student' => 'fa-user-graduate', 'alumni' => 'fa-award', 'staff' => 'fa-user-shield'];
    $counts = $people->countBy('key');
    $tabs = collect(\App\Http\Controllers\ChatController::ROLE_ORDER)->filter(fn ($k) => ($counts[$k] ?? 0) > 0);
    $last = null;
@endphp
<div class="pk" id="{{ $id }}" data-mode="{{ $mode }}">
    <div class="pk-tabs" role="tablist">
        <button type="button" class="pk-tab on" data-tab="all">All <span>{{ $people->count() }}</span></button>
        @foreach($tabs as $key)
            <button type="button" class="pk-tab" data-tab="{{ $key }}"><i class="fas {{ $roleIcons[$key] }}"></i> {{ $roleLabels[$key] }} <span>{{ $counts[$key] }}</span></button>
        @endforeach
    </div>
    <div class="pk-search"><i class="fas fa-search"></i><input type="search" placeholder="Search by name or email" aria-label="Search people"></div>
    @if($mode === 'group')
        <div class="pk-bar"><span><b data-picked>0</b> selected</span><button type="button" class="pk-link" data-all>Select shown</button><button type="button" class="pk-link" data-none>Clear</button></div>
    @endif

    <div class="pk-list">
        @foreach($people as $p)
            @if($p['key'] !== $last)
                @php $last = $p['key']; @endphp
                <div class="pk-head" data-role="{{ $p['key'] }}"><i class="fas {{ $roleIcons[$p['key']] }}"></i> {{ $roleLabels[$p['key']] }} <span>{{ $counts[$p['key']] }}</span></div>
            @endif
            @if($mode === 'dm')
                <form method="POST" action="{{ route('messages.start') }}" class="pk-item" data-role="{{ $p['key'] }}" data-name="{{ strtolower($p['name'] . ' ' . $p['email']) }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $p['id'] }}">
                    <button type="submit" class="pk-row">
                        <span class="pk-av" style="background:{{ $p['color'] }}">{{ $p['initials'] }}</span>
                        <span class="pk-who"><b>{{ $p['name'] }}</b><small>{{ $p['meta'] }}</small></span>
                        <span class="pk-chip" style="background:{{ $p['bg'] }};color:{{ $p['color'] }}">{{ $p['label'] }}</span>
                    </button>
                </form>
            @else
                <label class="pk-item pk-row" data-role="{{ $p['key'] }}" data-name="{{ strtolower($p['name'] . ' ' . $p['email']) }}">
                    <input type="checkbox" name="member_ids[]" value="{{ $p['id'] }}">
                    <span class="pk-av" style="background:{{ $p['color'] }}">{{ $p['initials'] }}</span>
                    <span class="pk-who"><b>{{ $p['name'] }}</b><small>{{ $p['meta'] }}</small></span>
                    <span class="pk-chip" style="background:{{ $p['bg'] }};color:{{ $p['color'] }}">{{ $p['label'] }}</span>
                </label>
            @endif
        @endforeach
        <div class="pk-none" hidden>No one matches.</div>
    </div>
</div>

@once
<script>
function initPeoplePicker(root) {
    const items = [...root.querySelectorAll('.pk-item')];
    const heads = [...root.querySelectorAll('.pk-head')];
    const search = root.querySelector('.pk-search input');
    const tabs = [...root.querySelectorAll('.pk-tab')];
    let tab = 'all';

    function apply() {
        const q = search.value.trim().toLowerCase();
        let shown = 0;
        items.forEach(el => {
            const ok = (tab === 'all' || el.dataset.role === tab) && el.dataset.name.includes(q);
            el.hidden = !ok;
            if (ok) shown++;
        });
        // role headings only help in "All", and only above rows that are showing
        heads.forEach(h => { h.hidden = tab !== 'all' || !items.some(el => !el.hidden && el.dataset.role === h.dataset.role); });
        root.querySelector('.pk-none').hidden = shown > 0;
        const picked = root.querySelector('[data-picked]');
        if (picked) picked.textContent = items.filter(el => el.querySelector('input:checked')).length;
    }
    root.setTab = t => { tab = t; tabs.forEach(b => b.classList.toggle('on', b.dataset.tab === t)); apply(); };
    tabs.forEach(b => b.addEventListener('click', () => root.setTab(b.dataset.tab)));
    search.addEventListener('input', apply);
    root.addEventListener('change', apply);
    root.querySelector('[data-all]')?.addEventListener('click', () => { items.filter(el => !el.hidden).forEach(el => { el.querySelector('input').checked = true; }); apply(); });
    root.querySelector('[data-none]')?.addEventListener('click', () => { items.forEach(el => { el.querySelector('input').checked = false; }); apply(); });
    apply();
}
document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.pk').forEach(initPeoplePicker));

// Open the "New message" popup, optionally already on one role's tab.
function openNewMessage(role) {
    const modal = document.getElementById('newMsgModal');
    modal.classList.add('open');
    const root = document.getElementById('dmPicker');
    root.setTab(role || 'all');
    const input = root.querySelector('.pk-search input');
    input.value = ''; root.setTab(role || 'all');
    setTimeout(() => input.focus(), 50);
}
</script>
@endonce
