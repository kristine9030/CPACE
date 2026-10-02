{{--
    Program Chair: Announcements, inside Messages (?view=announcements).
    A list of what was sent (click one to read it and see who has seen it),
    filtered by audience, plus a "Make an announcement" form in a popup.
    Data comes from Chair\CommunicationController::boardData().
--}}
@php
    $filter = $announce['filter'];
    $items = $announce['items'];
    $counts = $announce['counts'];
    $studentList = $announce['students'];
    $facultyList = $announce['faculty'];
    $subjectList = $announce['subjects'];
    $yearLevels = $announce['yearLevels'];
    $sectionList = $announce['sections'];

    // The form was posted and failed validation: reopen it with what was typed.
    $reopen = $errors->any() && old('title') !== null;
    $oldAudience = old('audience', 'students');
    $oldTarget = old('target_type', 'all');
    $oldType = old('type', 'announcement');

    $filterUrl = fn (string $to) => route('messages.index', array_filter(['view' => 'announcements', 'to' => $to === 'all' ? null : $to]));
    $typeIcons = ['announcement' => 'fa-bullhorn', 'reminder' => 'fa-bell', 'schedule_change' => 'fa-calendar-days'];
    $typeLabels = ['announcement' => 'Announcement', 'reminder' => 'Reminder', 'schedule_change' => 'Schedule change'];
    $allowedExtensions = explode(',', \App\Models\CommunicationAttachment::ALLOWED_EXTENSIONS);
    $fileAccept = collect($allowedExtensions)->map(fn ($e) => '.' . $e)->implode(',');
@endphp

<section class="an-board">
    <div class="an-toolbar">
        <div class="an-filters" role="tablist" aria-label="Filter announcements">
            <a class="an-pill {{ $filter === 'all' ? 'on' : '' }}" href="{{ $filterUrl('all') }}"><i class="fas fa-layer-group"></i> All <span>{{ $counts['all'] }}</span></a>
            <a class="an-pill {{ $filter === 'students' ? 'on' : '' }}" href="{{ $filterUrl('students') }}"><i class="fas fa-user-graduate"></i> Students <span>{{ $counts['students'] }}</span></a>
            <a class="an-pill {{ $filter === 'faculty' ? 'on' : '' }}" href="{{ $filterUrl('faculty') }}"><i class="fas fa-chalkboard-user"></i> Faculty <span>{{ $counts['faculty'] }}</span></a>
        </div>
        <button type="button" class="an-btn primary" onclick="openAnnounce()"><i class="fas fa-plus"></i> Make an announcement</button>
    </div>

    @if($items->isEmpty())
        <div class="an-empty">
            <i class="fas fa-bullhorn"></i>
            <strong>{{ $filter === 'all' ? 'No announcements yet' : 'Nothing sent to ' . $filter . ' yet' }}</strong>
            <span>Make an announcement to reach {{ $filter === 'faculty' ? 'your faculty' : ($filter === 'students' ? 'your students' : 'students or faculty') }} in one step.</span>
            <button type="button" class="an-btn primary" onclick="openAnnounce('{{ $filter === 'faculty' ? 'faculty' : 'students' }}')"><i class="fas fa-plus"></i> Make an announcement</button>
        </div>
    @else
        <div class="an-rows">
            @foreach($items as $item)
                @php
                    $isStudents = $item->audience === 'students';
                    $seenPct = $item->recipient_count > 0 ? (int) round($item->read_count / $item->recipient_count * 100) : 0;
                    $files = (int) ($item->attachments_count ?? 0);
                @endphp
                <div class="an-row" role="button" tabindex="0" data-url="{{ route('chair.communications.show', $item->id) }}" aria-label="Open {{ $item->title }}">
                    <span class="an-ic {{ $item->audience }}"><i class="fas {{ $typeIcons[$item->type] ?? 'fa-bullhorn' }}"></i></span>
                    <div class="an-main">
                        <div class="an-title">{{ $item->title }}@if($files)<span class="an-clip" title="{{ $files }} attached {{ $files === 1 ? 'file' : 'files' }}"><i class="fas fa-paperclip"></i> {{ $files }}</span>@endif</div>
                        <div class="an-msg">{{ $item->message }}</div>
                    </div>
                    <div class="an-to">
                        <span class="an-aud {{ $item->audience }}"><i class="fas {{ $isStudents ? 'fa-user-graduate' : 'fa-chalkboard-user' }}"></i> {{ $isStudents ? 'Students' : 'Faculty' }}</span>
                        <small>{{ $item->targetSummary() }} &bull; {{ $item->recipient_count }}</small>
                    </div>
                    <div class="an-seen" title="{{ $item->read_count }} of {{ $item->recipient_count }} have seen it">
                        <div class="an-bar"><span style="width:{{ $seenPct }}%;"></span></div>
                        <small><b>{{ $item->read_count }}</b> of {{ $item->recipient_count }} seen</small>
                    </div>
                    <div class="an-when">{{ $item->created_at->format('M j, Y') }}<small>{{ $item->created_at->format('g:i A') }}</small></div>
                    <i class="fas fa-chevron-right an-go"></i>
                </div>
            @endforeach
        </div>

        @if($items->hasPages())
            <div class="an-pager">
                @if($items->onFirstPage())<span class="off">Previous</span>@else<a href="{{ $items->previousPageUrl() }}">Previous</a>@endif
                <span>Page {{ $items->currentPage() }} of {{ $items->lastPage() }}</span>
                @if($items->hasMorePages())<a href="{{ $items->nextPageUrl() }}">Next</a>@else<span class="off">Next</span>@endif
            </div>
        @endif
    @endif
</section>

{{-- ── An announcement opened: the message, its files, and who has seen it ── --}}
<div class="modal-overlay" id="detailModal">
    <div class="an-modal an-detail" role="dialog" aria-modal="true" aria-labelledby="dTitle">
        <div class="an-m-head">
            <div class="an-m-ic" id="dIcon"><i class="fas fa-bullhorn"></i></div>
            <div style="min-width:0;"><h3 id="dTitle">Loading…</h3><p id="dSub">&nbsp;</p></div>
            <button type="button" class="an-x" onclick="closeDetail()" aria-label="Close"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="an-m-body">
            <div id="dError" class="an-none" hidden>This announcement could not be loaded. Please try again.</div>
            <div id="dBody" hidden>
                <div class="d-msg" id="dMsg"></div>
                <div class="d-link" id="dLink" hidden></div>
                <div class="d-files" id="dFiles" hidden></div>

                <div class="d-seen">
                    <div class="d-seen-head">
                        <div><h4>Who has seen it</h4><p id="dSeenText"></p></div>
                        <div class="d-big"><b id="dSeenN">0</b><span id="dSeenOf">/ 0</span></div>
                    </div>
                    <div class="an-bar lg"><span id="dSeenBar" style="width:0;"></span></div>
                    <div class="d-tools">
                        <div class="an-chips tight" id="dTabs">
                            <button type="button" class="d-tab on" data-tab="all">All <span id="dCntAll">0</span></button>
                            <button type="button" class="d-tab" data-tab="seen">Seen <span id="dCntSeen">0</span></button>
                            <button type="button" class="d-tab" data-tab="not">Not yet <span id="dCntNot">0</span></button>
                        </div>
                        <div class="s"><i class="fas fa-search"></i><input class="an-input" type="search" id="dSearch" placeholder="Search by name" aria-label="Search recipients"></div>
                    </div>
                    <div class="d-people" id="dPeople"></div>
                </div>
            </div>
        </div>
        <div class="an-m-foot"><span></span><button type="button" class="an-btn ghost" onclick="closeDetail()">Close</button></div>
    </div>
</div>

{{-- ── Make an announcement (popup) ── --}}
<div class="modal-overlay" id="announceModal">
    <form class="an-modal" method="POST" action="{{ route('chair.communications.store') }}" id="announceForm" enctype="multipart/form-data">
        @csrf
        <div class="an-m-head">
            <div class="an-m-ic"><i class="fas fa-bullhorn"></i></div>
            <div><h3>Make an announcement</h3><p>Choose who gets it, then write your message.</p></div>
            <button type="button" class="an-x" onclick="closeAnnounce()" aria-label="Close"><i class="fas fa-xmark"></i></button>
        </div>

        <div class="an-m-body">
            {{-- 1. Audience --}}
            <div class="an-field">
                <span class="an-label">Send to</span>
                <div class="an-aud-pick">
                    <label class="an-aud-opt"><input type="radio" name="audience" value="students" {{ $oldAudience === 'students' ? 'checked' : '' }}>
                        <span class="b"><i class="fas fa-user-graduate"></i><strong>Students</strong><small>{{ $studentList->count() }} active</small></span></label>
                    <label class="an-aud-opt"><input type="radio" name="audience" value="faculty" {{ $oldAudience === 'faculty' ? 'checked' : '' }}>
                        <span class="b"><i class="fas fa-chalkboard-user"></i><strong>Faculty</strong><small>{{ $facultyList->count() }} active</small></span></label>
                </div>
            </div>

            {{-- 2. Which of them --}}
            <div class="an-field">
                <span class="an-label">Which ones?</span>
                <div class="an-chips">
                    <label class="an-chip"><input type="radio" name="target_type" value="all" {{ $oldTarget === 'all' ? 'checked' : '' }}><span>Everyone</span></label>
                    <label class="an-chip"><input type="radio" name="target_type" value="group" {{ $oldTarget === 'group' ? 'checked' : '' }}><span id="groupChipLabel">A year or section</span></label>
                    <label class="an-chip"><input type="radio" name="target_type" value="selected" {{ $oldTarget === 'selected' ? 'checked' : '' }}><span>Pick people</span></label>
                </div>

                <div class="an-panel" data-panel="group" data-aud="students">
                    <div class="an-two">
                        <div><label class="an-label sm" for="anYear">Year level</label><select class="an-input" name="year_level" id="anYear"><option value="">All year levels</option>@foreach($yearLevels as $year)<option value="{{ $year }}" {{ (string) old('year_level') === (string) $year ? 'selected' : '' }}>Year {{ $year }}</option>@endforeach</select></div>
                        <div><label class="an-label sm" for="anSection">Section</label><select class="an-input" name="section" id="anSection"><option value="">All sections</option>@foreach($sectionList as $section)<option value="{{ $section }}" {{ old('section') === $section ? 'selected' : '' }}>{{ $section }}</option>@endforeach</select></div>
                    </div>
                </div>
                <div class="an-panel" data-panel="group" data-aud="faculty">
                    <label class="an-label sm" for="anSubject">Subject they handle</label>
                    <select class="an-input" name="subject_id" id="anSubject"><option value="">All subjects</option>@foreach($subjectList as $subject)<option value="{{ $subject->id }}" {{ (string) old('subject_id') === (string) $subject->id ? 'selected' : '' }}>{{ $subject->code }} — {{ $subject->name }}</option>@endforeach</select>
                </div>

                <div class="an-panel" data-panel="selected">
                    <div class="an-pick-tools">
                        <div class="s"><i class="fas fa-search"></i><input class="an-input" type="search" id="anSearch" placeholder="Search by name or email" aria-label="Search people"></div>
                        <button type="button" class="an-link" id="anPickAll">Select shown</button>
                        <button type="button" class="an-link" id="anPickNone">Clear</button>
                    </div>
                    <div class="an-list" id="anList">
                        @foreach([['students', $studentList], ['faculty', $facultyList]] as [$aud, $people])
                            @foreach($people as $person)
                                @php
                                    $meta = $aud === 'students'
                                        ? collect(['Year ' . ($person->studentProfile?->year_level ?: '—'), $person->studentProfile?->section ?: 'No section'])->join(' · ')
                                        : ($person->assignedSubjects->pluck('code')->join(', ') ?: 'No assigned subjects');
                                @endphp
                                <label class="an-person" data-aud="{{ $aud }}" data-search="{{ strtolower($person->name . ' ' . $person->email) }}" data-year="{{ $person->studentProfile?->year_level }}" data-section="{{ $person->studentProfile?->section }}" data-subjects="{{ $aud === 'faculty' ? $person->assignedSubjects->pluck('id')->join(',') : '' }}">
                                    <input type="checkbox" name="recipient_ids[]" value="{{ $person->id }}" {{ in_array($person->id, old('recipient_ids', [])) ? 'checked' : '' }}>
                                    <span class="av">{{ strtoupper(mb_substr($person->first_name, 0, 1) . mb_substr($person->last_name, 0, 1)) }}</span>
                                    <span class="who"><b>{{ $person->name }}</b><small>{{ $person->email }} · {{ $meta }}</small></span>
                                </label>
                            @endforeach
                        @endforeach
                        <div class="an-none" id="anNone" hidden>No one matches that search.</div>
                    </div>
                </div>
            </div>

            {{-- 3. Message --}}
            <div class="an-field">
                <label class="an-label" for="anTitle">Title <small id="anTitleN">0/150</small></label>
                <input class="an-input" id="anTitle" name="title" maxlength="150" required value="{{ old('title') }}" placeholder="e.g. CPALE Mock Examination Schedule Change" autocomplete="off">
            </div>
            <div class="an-field">
                <label class="an-label" for="anMessage">Message <small id="anMessageN">0/5,000</small></label>
                <textarea class="an-input" id="anMessage" name="message" maxlength="5000" required rows="5" placeholder="Write your message here...">{{ old('message') }}</textarea>
            </div>

            {{-- 4. Attachments --}}
            <div class="an-field">
                <span class="an-label">Attachments <small>Optional &bull; up to {{ \App\Models\CommunicationAttachment::MAX_FILES }} files, {{ \App\Models\CommunicationAttachment::MAX_KILOBYTES / 1024 }} MB each</small></span>
                <div class="an-drop" id="anDrop">
                    <input type="file" id="anFiles" name="attachments[]" multiple hidden accept="{{ $fileAccept }}">
                    <button type="button" class="an-attach" id="anAttach"><i class="fas fa-paperclip"></i> Attach files or images</button>
                    <span class="hint">or drop them here &mdash; PDF, Word, Excel, PowerPoint, images, zip</span>
                </div>
                <div class="an-files" id="anFileList"></div>
                <div class="an-ferr" id="anFileErr" hidden></div>
            </div>

            {{-- 5. Type + link --}}
            <div class="an-two">
                <div class="an-field">
                    <label class="an-label sm" for="anType">Type</label>
                    <select class="an-input" id="anType" name="type">
                        @foreach($typeLabels as $value => $label)<option value="{{ $value }}" {{ $oldType === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div class="an-field">
                    <label class="an-label sm" for="anLink">Link <small>Optional</small></label>
                    <input class="an-input" id="anLink" name="link" value="{{ old('link') }}" placeholder="/calendar" autocomplete="off">
                </div>
            </div>
        </div>

        <div class="an-m-foot">
            <div class="an-sendto"><i class="fas fa-paper-plane"></i> Sending to <strong id="anSendTo">0</strong></div>
            <div class="an-m-actions">
                <button type="button" class="an-btn ghost" onclick="closeAnnounce()">Cancel</button>
                <button type="submit" class="an-btn primary" id="anSend"><i class="fas fa-paper-plane"></i> Send</button>
            </div>
        </div>
    </form>
</div>

<script>
(function () {
    const $ = id => document.getElementById(id);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const TYPE_ICONS = @json($typeIcons);
    const TYPE_LABELS = @json($typeLabels);

    /* ───────────── Open an announcement ───────────── */
    const detail = $('detailModal');
    let detailPeople = [], detailTab = 'all';

    function renderPeople() {
        const q = $('dSearch').value.trim().toLowerCase();
        const rows = detailPeople.filter(p => (detailTab === 'all' || (detailTab === 'seen') === p.seen) && p.name.toLowerCase().includes(q));
        $('dPeople').innerHTML = rows.length ? rows.map(p => `
            <div class="d-person">
                <span class="av ${p.seen ? 'seen' : ''}">${esc(p.initials)}</span>
                <span class="who"><b>${esc(p.name)}</b><small>${esc(p.meta)}</small></span>
                <span class="st ${p.seen ? 'seen' : 'not'}">${p.seen ? '<i class="fas fa-check"></i> Seen' + (p.seen_at ? ' · ' + esc(p.seen_at) : '') : 'Not yet'}</span>
            </div>`).join('') : '<div class="an-none">No one here.</div>';
    }

    function showDetail(d) {
        const aud = d.audience === 'students' ? 'Students' : 'Faculty';
        $('dIcon').innerHTML = `<i class="fas ${TYPE_ICONS[d.type] || 'fa-bullhorn'}"></i>`;
        $('dTitle').textContent = d.title;
        $('dSub').textContent = `${aud} · ${d.to} · ${d.recipient_count} recipient${d.recipient_count === 1 ? '' : 's'} · Sent ${d.sent_at}`;
        $('dMsg').textContent = d.message;
        $('dLink').hidden = !d.link;
        if (d.link) $('dLink').innerHTML = `<i class="fas fa-link"></i> Opens <b>${esc(d.link)}</b> for recipients`;

        $('dFiles').hidden = !d.attachments.length;
        $('dFiles').innerHTML = d.attachments.length ? `<div class="d-label">Attachments (${d.attachments.length})</div><div class="d-file-grid">` + d.attachments.map(f => `
            <a class="d-file" href="${esc(f.url)}" target="_blank" rel="noopener">
                ${f.is_image ? `<img src="${esc(f.url)}" alt="">` : `<span class="ic" style="background:${esc(f.color)}"><i class="fas ${esc(f.icon)}"></i></span>`}
                <span class="nm"><b>${esc(f.name)}</b><small>${esc(f.size)} · ${f.is_image ? 'Open' : 'Download'}</small></span>
            </a>`).join('') + '</div>' : '';

        detailPeople = d.recipients;
        const seen = d.seen_count, total = d.recipient_count, pct = total ? Math.round(seen / total * 100) : 0;
        $('dSeenN').textContent = seen; $('dSeenOf').textContent = '/ ' + total;
        $('dSeenText').textContent = total === 0 ? 'No recipients.' : seen === total ? 'Everyone has seen it.' : `${pct}% have seen it, ${total - seen} still haven't.`;
        $('dSeenBar').style.width = pct + '%';
        $('dCntAll').textContent = detailPeople.length;
        $('dCntSeen').textContent = detailPeople.filter(p => p.seen).length;
        $('dCntNot').textContent = detailPeople.filter(p => !p.seen).length;
        detailTab = 'all'; $('dSearch').value = '';
        document.querySelectorAll('#dTabs .d-tab').forEach(t => t.classList.toggle('on', t.dataset.tab === 'all'));
        renderPeople();
        $('dBody').hidden = false; $('dError').hidden = true;
    }

    async function openDetail(url) {
        $('dTitle').textContent = 'Loading…'; $('dSub').innerHTML = '&nbsp;';
        $('dBody').hidden = true; $('dError').hidden = true;
        detail.classList.add('open');
        try {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error();
            showDetail(await res.json());
        } catch (e) {
            $('dTitle').textContent = 'Announcement';
            $('dError').hidden = false;
        }
    }
    window.closeDetail = () => detail.classList.remove('open');

    document.querySelectorAll('.an-row').forEach(row => {
        row.addEventListener('click', () => openDetail(row.dataset.url));
        row.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openDetail(row.dataset.url); } });
    });
    $('dTabs').addEventListener('click', e => {
        const t = e.target.closest('.d-tab'); if (!t) return;
        detailTab = t.dataset.tab;
        document.querySelectorAll('#dTabs .d-tab').forEach(x => x.classList.toggle('on', x === t));
        renderPeople();
    });
    $('dSearch').addEventListener('input', renderPeople);

    /* ───────────── Make an announcement ───────────── */
    const overlay = $('announceModal');
    const form = $('announceForm');
    const people = [...document.querySelectorAll('.an-person')];
    const radios = n => [...form.querySelectorAll(`input[name="${n}"]`)];
    const audience = () => radios('audience').find(r => r.checked).value;
    const target = () => radios('target_type').find(r => r.checked).value;
    const word = a => a === 'students' ? 'students' : 'faculty';

    const forAud = () => people.filter(p => p.dataset.aud === audience());
    function groupMatches() {
        const aud = audience();
        const year = $('anYear').value, section = $('anSection').value, subject = $('anSubject').value;
        return forAud().filter(p => {
            if (aud === 'students') return (!year || p.dataset.year === year) && (!section || p.dataset.section === section);
            return !subject || p.dataset.subjects.split(',').includes(subject);
        });
    }
    const picked = () => forAud().filter(p => p.querySelector('input').checked);
    function count() {
        const t = target();
        return t === 'selected' ? picked().length : t === 'group' ? groupMatches().length : forAud().length;
    }

    function refresh() {
        const aud = audience(), t = target();
        $('groupChipLabel').textContent = aud === 'students' ? 'A year or section' : 'A subject';
        // Only what applies is submitted: the other audience's fields, the group
        // filters when not grouping, and ticked people when not picking people.
        document.querySelectorAll('.an-panel').forEach(p => {
            const show = p.dataset.panel === t && (!p.dataset.aud || p.dataset.aud === aud);
            p.classList.toggle('on', show);
            p.querySelectorAll('select').forEach(s => { s.disabled = !show; });
        });
        people.forEach(p => {
            const mine = p.dataset.aud === aud;
            p.hidden = !mine || !p.dataset.search.includes($('anSearch').value.trim().toLowerCase());
            p.querySelector('input').disabled = !(mine && t === 'selected');
            p.classList.toggle('is-on', p.querySelector('input').checked);
        });
        $('anNone').hidden = forAud().some(p => !p.hidden);
        const n = count();
        $('anSendTo').textContent = n ? `${n} ${n === 1 ? (aud === 'students' ? 'student' : 'faculty member') : word(aud)}` : 'no one yet';
        $('anSendTo').classList.toggle('zero', !n);
        $('anTitleN').textContent = $('anTitle').value.length + '/150';
        $('anMessageN').textContent = $('anMessage').value.length.toLocaleString() + '/5,000';
    }

    /* Attachments: pick or drop files, show them as removable chips. */
    const MAX_FILES = {{ \App\Models\CommunicationAttachment::MAX_FILES }};
    const MAX_BYTES = {{ \App\Models\CommunicationAttachment::MAX_KILOBYTES }} * 1024;
    const ALLOWED = @json($allowedExtensions);
    const IMG_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    let chosen = [];

    const ext = f => (f.name.split('.').pop() || '').toLowerCase();
    const human = b => b >= 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB';
    function syncInput() {
        const dt = new DataTransfer();
        chosen.forEach(f => dt.items.add(f));
        $('anFiles').files = dt.files;
    }
    function renderFiles() {
        $('anFileList').innerHTML = chosen.map((f, i) => `
            <div class="an-file">
                ${IMG_EXT.includes(ext(f)) ? `<img src="${URL.createObjectURL(f)}" alt="">` : `<span class="ic"><i class="fas fa-file"></i></span>`}
                <span class="nm"><b>${esc(f.name)}</b><small>${human(f.size)}</small></span>
                <button type="button" class="rm" data-i="${i}" aria-label="Remove ${esc(f.name)}"><i class="fas fa-xmark"></i></button>
            </div>`).join('');
        $('anAttach').innerHTML = chosen.length ? `<i class="fas fa-plus"></i> Add more` : `<i class="fas fa-paperclip"></i> Attach files or images`;
    }
    function addFiles(list) {
        const problems = [];
        [...list].forEach(f => {
            if (chosen.some(c => c.name === f.name && c.size === f.size)) return;
            if (!ALLOWED.includes(ext(f))) { problems.push(`${f.name}: this file type can't be attached.`); return; }
            if (f.size > MAX_BYTES) { problems.push(`${f.name}: bigger than ${human(MAX_BYTES)}.`); return; }
            if (chosen.length >= MAX_FILES) { problems.push(`Only ${MAX_FILES} files can be attached.`); return; }
            chosen.push(f);
        });
        syncInput(); renderFiles();
        $('anFileErr').hidden = !problems.length;
        $('anFileErr').innerHTML = [...new Set(problems)].map(esc).join('<br>');
    }
    $('anAttach').addEventListener('click', () => $('anFiles').click());
    $('anFiles').addEventListener('change', e => { addFiles(e.target.files); });
    $('anFileList').addEventListener('click', e => {
        const b = e.target.closest('.rm'); if (!b) return;
        chosen.splice(Number(b.dataset.i), 1); syncInput(); renderFiles(); $('anFileErr').hidden = true;
    });
    ['dragenter', 'dragover'].forEach(ev => $('anDrop').addEventListener(ev, e => { e.preventDefault(); $('anDrop').classList.add('over'); }));
    ['dragleave', 'drop'].forEach(ev => $('anDrop').addEventListener(ev, e => { e.preventDefault(); $('anDrop').classList.remove('over'); }));
    $('anDrop').addEventListener('drop', e => addFiles(e.dataTransfer.files));

    window.openAnnounce = function (aud) {
        if (aud) radios('audience').forEach(r => { r.checked = r.value === aud; });
        overlay.classList.add('open');
        refresh();
        setTimeout(() => $('anTitle').focus(), 50);
    };
    window.closeAnnounce = () => overlay.classList.remove('open');

    form.addEventListener('input', refresh);
    form.addEventListener('change', refresh);
    $('anPickAll').addEventListener('click', () => { forAud().filter(p => !p.hidden).forEach(p => { p.querySelector('input').checked = true; }); refresh(); });
    $('anPickNone').addEventListener('click', () => { people.forEach(p => { p.querySelector('input').checked = false; }); refresh(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeAnnounce(); closeDetail(); } });

    let confirmed = false;
    // The recipient count is live, so the confirmation is built at submit time.
    form.addEventListener('submit', function (event) {
        if (confirmed) return;
        event.preventDefault();
        const n = count();
        if (!n) { CPACE.warning('No recipients selected', 'Pick at least one active recipient before sending this announcement.'); return; }
        const files = chosen.length ? ` with ${chosen.length} attached file${chosen.length === 1 ? '' : 's'}` : '';
        CPACE.confirm({
            title: 'Send this announcement?',
            text: 'It will go out to ' + n + ' recipient' + (n === 1 ? '' : 's') + files + ' as an in-app notification and email. Sent messages cannot be recalled.',
            icon: 'question', confirmText: 'Yes, send it', cancelText: 'Keep editing',
        }).then(function (ok) {
            if (!ok) return;
            confirmed = true;
            CPACE.loading(chosen.length ? 'Uploading and sending your announcement...' : 'Sending your announcement...');
            form.requestSubmit();
        });
    });

    refresh();
    @if($reopen) openAnnounce(); @endif
})();
</script>
