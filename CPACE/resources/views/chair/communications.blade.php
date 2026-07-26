<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Communications - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .communication-tabs { display:flex; gap:6px; background:#fff; padding:7px; border-radius:12px; margin-bottom:18px; width:max-content; max-width:100%; }
        .communication-tab { display:flex; align-items:center; gap:7px; padding:9px 15px; border-radius:8px; color:#777; text-decoration:none; font-size:12px; font-weight:600; white-space:nowrap; }
        .communication-tab.active { color:#fff; background:var(--primary); }
        .compose-grid { display:grid; grid-template-columns:minmax(0,1fr) 310px; gap:18px; }
        .communication-card { background:#fff; border-radius:14px; padding:22px; }
        .card-heading { font-size:15px; font-weight:700; color:#222; margin-bottom:3px; }
        .card-sub { font-size:11px; color:#999; margin-bottom:19px; }
        .field { margin-bottom:16px; }
        .field label { display:block; font-size:11px; font-weight:600; color:#555; margin-bottom:7px; }
        .field input, .field textarea, .field select { width:100%; border:1px solid #e2e5e9; border-radius:9px; padding:10px 12px; font:12px 'Poppins',sans-serif; outline:none; background:#fff; }
        .field textarea { min-height:145px; resize:vertical; line-height:1.6; }
        .field input:focus, .field textarea:focus, .field select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(123,29,29,.08); }
        .field-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .target-options { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
        .target-option input { position:absolute; opacity:0; }
        .target-option span { display:flex; align-items:center; justify-content:center; gap:6px; padding:10px; border:1px solid #e5e7eb; border-radius:9px; font-size:11px; cursor:pointer; color:#666; }
        .target-option input:checked + span { border-color:var(--primary); background:#f8eeee; color:var(--primary); font-weight:600; }
        .target-panel { display:none; padding:13px; background:#fafafa; border:1px solid #eee; border-radius:10px; margin-top:10px; }
        .target-panel.visible { display:block; }
        .recipient-list { max-height:245px; overflow:auto; display:grid; gap:6px; }
        .recipient-item { display:flex; gap:10px; align-items:center; padding:9px; background:#fff; border:1px solid #eee; border-radius:8px; cursor:pointer; }
        .recipient-item input { width:auto; }
        .recipient-name { font-size:11.5px; font-weight:600; color:#333; }
        .recipient-meta { font-size:9.5px; color:#999; }
        .recipient-search { margin-bottom:9px; }
        .send-row { display:flex; align-items:center; justify-content:space-between; gap:12px; padding-top:5px; }
        .recipient-count { font-size:11px; color:#777; }
        .recipient-count strong { color:var(--primary); font-size:16px; }
        .summary-card { position:sticky; top:20px; height:max-content; }
        .summary-icon { width:47px; height:47px; border-radius:12px; display:flex; align-items:center; justify-content:center; background:#f5e8e8; color:var(--primary); font-size:19px; margin-bottom:14px; }
        .summary-list { list-style:none; margin:17px 0; padding:0; }
        .summary-list li { display:flex; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px solid #f2f2f2; font-size:10.5px; color:#888; }
        .summary-list strong { color:#333; text-align:right; }
        .help-box { background:#fff8e8; color:#8a6012; border-radius:9px; padding:11px; font-size:10px; line-height:1.55; }
        .history-table { width:100%; border-collapse:collapse; }
        .history-table th { text-align:left; font-size:10px; color:#aaa; text-transform:uppercase; padding:0 10px 11px; }
        .history-table td { border-top:1px solid #f2f2f2; padding:13px 10px; font-size:11.5px; vertical-align:top; }
        .history-title { font-weight:600; color:#222; }
        .history-preview { color:#999; font-size:10px; max-width:420px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:3px; }
        .pill { display:inline-flex; padding:4px 8px; border-radius:15px; font-size:9px; font-weight:700; text-transform:capitalize; }
        .pill-students { background:#dbeafe; color:#1d4ed8; }
        .pill-faculty { background:#ede9fe; color:#6d28d9; }
        .priority-high { color:#b45309; } .priority-urgent { color:#b91c1c; }
        .empty-state { text-align:center; padding:50px 20px; color:#aaa; font-size:12px; }
        .empty-state i { display:block; font-size:31px; color:#ddd; margin-bottom:10px; }
        .history-pagination { display:flex; justify-content:flex-end; gap:7px; margin-top:16px; }
        .history-pagination a, .history-pagination span { padding:7px 11px; border:1px solid #e5e7eb; border-radius:7px; font-size:10px; color:#555; text-decoration:none; }
        .history-pagination span { color:#bbb; }
        @media(max-width:900px){ .compose-grid{grid-template-columns:1fr}.summary-card{position:static} }
        @media(max-width:600px){ .communication-tabs{width:100%;overflow:auto}.communication-tab span{display:none}.communication-tab{flex:1}.target-options,.field-row{grid-template-columns:1fr}.communication-card{padding:16px}.history-wrap{overflow-x:auto}.history-table{min-width:700px} }
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(20,10,10,.5); align-items:center; justify-content:center; z-index:1000; padding:20px; }
        .modal-overlay.visible { display:flex; }
        .modal-box { background:#fff; border-radius:14px; padding:28px; max-width:380px; width:100%; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .modal-icon { width:52px; height:52px; border-radius:50%; background:#f5e8e8; color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:22px; margin:0 auto 16px; }
        .modal-title { font-size:16px; font-weight:700; color:#222; margin-bottom:8px; }
        .modal-text { font-size:12.5px; color:#777; line-height:1.6; margin-bottom:22px; }
        .modal-actions { display:flex; gap:10px; justify-content:center; }
        .modal-actions button { flex:1; padding:11px; border-radius:9px; font-size:12.5px; font-weight:600; cursor:pointer; border:1px solid #e2e5e9; background:#fff; color:#555; }
        .modal-actions .btn-confirm { border:none; background:var(--primary); color:#fff; }
    </style>
</head>
<body>
@include('partials.chair-sidebar', ['active' => 'communications'])

<main class="main">
    <div class="topbar">
        <div class="topbar-left"><div><div class="page-title">Communications</div><div class="page-sub">Send announcements and internal messages to students and faculty.</div></div></div>
        <div class="topbar-right">@include('partials.topbar-actions')</div>
    </div>

    @if(session('status'))<div class="alert alert-success"><i class="fas fa-circle-check"></i> {{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-error"><i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}</div>@endif

    <nav class="communication-tabs" aria-label="Communication sections">
        <a class="communication-tab {{ $tab === 'students' ? 'active' : '' }}" href="{{ route('chair.communications', ['tab' => 'students']) }}"><i class="fas fa-user-graduate"></i><span>Student Announcements</span></a>
        <a class="communication-tab {{ $tab === 'faculty' ? 'active' : '' }}" href="{{ route('chair.communications', ['tab' => 'faculty']) }}"><i class="fas fa-chalkboard-user"></i><span>Faculty Messages</span></a>
        <a class="communication-tab {{ $tab === 'history' ? 'active' : '' }}" href="{{ route('chair.communications', ['tab' => 'history']) }}"><i class="fas fa-clock-rotate-left"></i><span>Sent History</span></a>
    </nav>

    @if($tab !== 'history')
        @php $isStudents = $tab === 'students'; $audienceCount = $isStudents ? $students->count() : $faculty->count(); @endphp
        <form method="POST" action="{{ route('chair.communications.store') }}" id="communicationForm">
            @csrf
            <input type="hidden" name="audience" value="{{ $tab }}">
            <div class="compose-grid">
                <section class="communication-card">
                    <div class="card-heading">Compose {{ $isStudents ? 'Student Announcement' : 'Faculty Message' }}</div>
                    <div class="card-sub">Choose an audience, write the message, and review the recipient count before sending.</div>

                    <div class="field">
                        <label>Recipient group</label>
                        <div class="target-options">
                            <label class="target-option"><input type="radio" name="target_type" value="all" {{ old('target_type','all') === 'all' ? 'checked' : '' }}><span><i class="fas fa-users"></i> All {{ $isStudents ? 'Students' : 'Faculty' }}</span></label>
                            <label class="target-option"><input type="radio" name="target_type" value="group" {{ old('target_type') === 'group' ? 'checked' : '' }}><span><i class="fas fa-filter"></i> By {{ $isStudents ? 'Year / Section' : 'Subject' }}</span></label>
                            <label class="target-option"><input type="radio" name="target_type" value="selected" {{ old('target_type') === 'selected' ? 'checked' : '' }}><span><i class="fas fa-user-check"></i> Select Individuals</span></label>
                        </div>

                        <div class="target-panel" data-panel="group">
                            @if($isStudents)
                                <div class="field-row">
                                    <div class="field" style="margin:0"><label>Year level</label><select name="year_level" id="yearLevel"><option value="">All year levels</option>@foreach($yearLevels as $year)<option value="{{ $year }}" {{ (string)old('year_level') === (string)$year ? 'selected' : '' }}>Year {{ $year }}</option>@endforeach</select></div>
                                    <div class="field" style="margin:0"><label>Section</label><select name="section" id="section"><option value="">All sections</option>@foreach($sections as $section)<option value="{{ $section }}" {{ old('section') === $section ? 'selected' : '' }}>{{ $section }}</option>@endforeach</select></div>
                                </div>
                            @else
                                <div class="field" style="margin:0"><label>Assigned subject</label><select name="subject_id" id="subjectId"><option value="">All subjects</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" {{ (string)old('subject_id') === (string)$subject->id ? 'selected' : '' }}>{{ $subject->code }} — {{ $subject->name }}</option>@endforeach</select></div>
                            @endif
                        </div>

                        <div class="target-panel" data-panel="selected">
                            <input class="recipient-search" id="recipientSearch" type="search" placeholder="Search by name or email...">
                            <div class="recipient-list" id="recipientList">
                                @foreach($isStudents ? $students : $faculty as $person)
                                    @php
                                        $meta = $isStudents
                                            ? collect(['Year '.($person->studentProfile?->year_level ?: '—'), $person->studentProfile?->section ?: 'No section'])->join(' · ')
                                            : ($person->assignedSubjects->pluck('code')->join(', ') ?: 'No assigned subjects');
                                        $subjectIds = $isStudents ? '' : $person->assignedSubjects->pluck('id')->join(',');
                                    @endphp
                                    <label class="recipient-item" data-search="{{ strtolower($person->name.' '.$person->email) }}" data-year="{{ $person->studentProfile?->year_level }}" data-section="{{ $person->studentProfile?->section }}" data-subjects="{{ $subjectIds }}">
                                        <input type="checkbox" name="recipient_ids[]" value="{{ $person->id }}" {{ in_array($person->id, old('recipient_ids', [])) ? 'checked' : '' }}>
                                        <span><span class="recipient-name">{{ $person->name }}</span><span class="recipient-meta">{{ $person->email }} · {{ $meta }}</span></span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="field"><label for="title">Title</label><input id="title" name="title" maxlength="150" required value="{{ old('title') }}" placeholder="e.g. CPALE Mock Examination Schedule Change"></div>
                    <div class="field"><label for="message">Message</label><textarea id="message" name="message" maxlength="5000" required placeholder="Write your message here...">{{ old('message') }}</textarea></div>
                    <div class="field-row">
                        <div class="field"><label for="type">Message type</label><select id="type" name="type"><option value="announcement">Announcement</option><option value="reminder" {{ old('type') === 'reminder' ? 'selected' : '' }}>Reminder</option><option value="schedule_change" {{ old('type') === 'schedule_change' ? 'selected' : '' }}>Schedule change</option></select></div>
                        <div class="field"><label for="priority">Priority</label><select id="priority" name="priority"><option value="normal">Normal</option><option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option><option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option></select></div>
                    </div>
                    <div class="field"><label for="link">Internal link <span style="font-weight:400;color:#aaa">(optional)</span></label><input id="link" name="link" value="{{ old('link') }}" placeholder="/calendar"></div>
                    <div class="send-row"><div class="recipient-count"><strong id="recipientCount">{{ $audienceCount }}</strong> active {{ $isStudents ? 'student' : 'faculty member' }}<span id="recipientPlural">{{ $audienceCount === 1 ? '' : 's' }}</span></div><button class="btn btn-primary" type="submit" id="sendButton"><i class="fas fa-paper-plane"></i> Send Message</button></div>
                </section>

                <aside class="communication-card summary-card">
                    <div class="summary-icon"><i class="fas fa-bullhorn"></i></div>
                    <div class="card-heading">Delivery summary</div>
                    <div class="card-sub">Messages appear immediately in each recipient's notification inbox.</div>
                    <ul class="summary-list"><li><span>Audience</span><strong>{{ $isStudents ? 'Students' : 'Faculty' }}</strong></li><li><span>Delivery</span><strong>In-app notification</strong></li><li><span>Sender</span><strong>{{ Auth::user()->name }}</strong></li><li><span>Recipients</span><strong id="summaryCount">{{ $audienceCount }}</strong></li></ul>
                    <div class="help-box"><i class="fas fa-circle-info"></i> High and urgent messages are visually emphasized in the recipient inbox. Only active accounts receive the message.</div>
                </aside>
            </div>
        </form>

        <div class="modal-overlay" id="sendConfirmModal">
            <div class="modal-box">
                <div class="modal-icon"><i class="fas fa-paper-plane"></i></div>
                <div class="modal-title">Send this message?</div>
                <div class="modal-text">It will go out to <strong id="modalRecipientCount">0</strong> recipient<span id="modalRecipientPlural">s</span> as an in-app notification and email.</div>
                <div class="modal-actions">
                    <button type="button" id="modalCancelBtn">Cancel</button>
                    <button type="button" class="btn-confirm" id="modalConfirmBtn">Yes, Send</button>
                </div>
            </div>
        </div>
    @else
        <section class="communication-card">
            <div class="card-heading">Sent History</div><div class="card-sub">Messages sent from your Program Chair account and their current read totals.</div>
            @if($history->isEmpty())
                <div class="empty-state"><i class="fas fa-paper-plane"></i>No communications have been sent yet.</div>
            @else
                <div class="history-wrap"><table class="history-table"><thead><tr><th>Message</th><th>Audience</th><th>Priority</th><th>Delivery</th><th>Sent</th></tr></thead><tbody>
                    @foreach($history as $item)<tr><td><div class="history-title">{{ $item->title }}</div><div class="history-preview">{{ $item->message }}</div></td><td><span class="pill pill-{{ $item->audience }}">{{ $item->audience }}</span><div class="recipient-meta" style="margin-top:5px">{{ ucfirst($item->target_type) }}</div></td><td><span class="priority-{{ $item->priority }}">{{ ucfirst($item->priority) }}</span></td><td>{{ $item->read_count }} / {{ $item->recipient_count }} read</td><td>{{ $item->created_at->format('M j, Y') }}<div class="recipient-meta">{{ $item->created_at->format('g:i A') }}</div></td></tr>@endforeach
                </tbody></table></div>
                <div class="history-pagination">@if($history->onFirstPage())<span>Previous</span>@else<a href="{{ $history->previousPageUrl() }}">Previous</a>@endif <span>Page {{ $history->currentPage() }} of {{ $history->lastPage() }}</span> @if($history->hasMorePages())<a href="{{ $history->nextPageUrl() }}">Next</a>@else<span>Next</span>@endif</div>
            @endif
        </section>
    @endif
</main>

@if($tab !== 'history')
<script>
(function () {
    const total = {{ $audienceCount }};
    const radios = document.querySelectorAll('input[name="target_type"]');
    const panels = document.querySelectorAll('.target-panel');
    const people = [...document.querySelectorAll('.recipient-item')];
    const count = document.getElementById('recipientCount');
    const summary = document.getElementById('summaryCount');
    const plural = document.getElementById('recipientPlural');
    const search = document.getElementById('recipientSearch');
    const year = document.getElementById('yearLevel');
    const section = document.getElementById('section');
    const subject = document.getElementById('subjectId');

    function selectedTarget() { return document.querySelector('input[name="target_type"]:checked').value; }
    function updateCount() {
        let value = total;
        if (selectedTarget() === 'selected') value = people.filter(p => p.querySelector('input').checked).length;
        if (selectedTarget() === 'group') {
            value = people.filter(p => {
                if (year && year.value && p.dataset.year !== year.value) return false;
                if (section && section.value && p.dataset.section !== section.value) return false;
                if (subject && subject.value && !p.dataset.subjects.split(',').includes(subject.value)) return false;
                return true;
            }).length;
        }
        count.textContent = summary.textContent = value;
        plural.textContent = value === 1 ? '' : 's';
    }
    function updatePanels() {
        panels.forEach(p => p.classList.toggle('visible', p.dataset.panel === selectedTarget()));
        updateCount();
    }
    radios.forEach(r => r.addEventListener('change', updatePanels));
    people.forEach(p => p.querySelector('input').addEventListener('change', updateCount));
    [year, section, subject].filter(Boolean).forEach(el => el.addEventListener('change', updateCount));
    if (search) search.addEventListener('input', () => { const q = search.value.toLowerCase(); people.forEach(p => p.style.display = p.dataset.search.includes(q) ? '' : 'none'); });
    const form = document.getElementById('communicationForm');
    const modal = document.getElementById('sendConfirmModal');
    const modalCount = document.getElementById('modalRecipientCount');
    const modalPlural = document.getElementById('modalRecipientPlural');
    let confirmed = false;

    form.addEventListener('submit', function (event) {
        if (confirmed) return;
        const n = Number(count.textContent);
        if (!n) { event.preventDefault(); return; }
        event.preventDefault();
        modalCount.textContent = n;
        modalPlural.textContent = n === 1 ? '' : 's';
        modal.classList.add('visible');
    });
    document.getElementById('modalCancelBtn').addEventListener('click', () => modal.classList.remove('visible'));
    modal.addEventListener('click', (event) => { if (event.target === modal) modal.classList.remove('visible'); });
    document.getElementById('modalConfirmBtn').addEventListener('click', () => {
        confirmed = true;
        modal.classList.remove('visible');
        form.requestSubmit();
    });
    updatePanels();
})();
</script>
@endif
</body>
</html>
