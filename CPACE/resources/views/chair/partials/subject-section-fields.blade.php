{{--
    Shared "assign subjects + optionally limit to sections" field group.
    Used by both the Add/Edit Faculty form and the quick-assign modal on the
    faculty list, so the two stay visually and behaviorally identical.

    One row per subject: tick the subject, then pick its sections from a
    dropdown — "All sections" (the default) or any specific ones. Submits
    subjects[] and sections[<subjectId>][]; no sections = all sections.

    Expected variables:
    - $subjects: Collection of Subject
    - $sections: Collection of Section (active ones)
    - $checkedSubjects: array of subject ids to pre-check (default: [])
    - $checkedSectionsBySubject: array [subjectId => [sectionId, ...]] to pre-check (default: [])
--}}
@php
    $checkedSubjects = collect($checkedSubjects ?? []);
    $checkedSectionsBySubject = collect($checkedSectionsBySubject ?? []);
    $yearLabels = \App\Models\Section::YEAR_LABELS;
@endphp
@once
<style>
    .subj-list { border:1px solid #ececef; border-radius:12px; overflow:visible; }
    .subj-list-head { display:grid; grid-template-columns:minmax(0, 1fr) 210px; gap:12px; padding:10px 14px; background:#f3f4f6; border-radius:12px 12px 0 0; font-size:11px; font-weight:700; color:#4b5563; text-transform:uppercase; letter-spacing:.3px; }
    .subj-row { display:grid; grid-template-columns:minmax(0, 1fr) 210px; gap:12px; align-items:center; padding:11px 14px; border-top:1px solid #f0f0f2; transition:background .12s; }
    .subj-row:hover { background:#fafafa; }
    .subj-row.is-on { background:#fdf7f8; }
    .subj-pick { display:flex; align-items:center; gap:12px; cursor:pointer; min-width:0; }
    .subj-pick input { width:17px; height:17px; accent-color:var(--primary); cursor:pointer; flex-shrink:0; }
    .subj-code { display:inline-block; min-width:44px; font-size:11px; font-weight:700; color:var(--primary); background:var(--primary-light); border-radius:6px; padding:3px 7px; text-align:center; }
    .subj-name { font-size:12.5px; color:#333; line-height:1.35; }

    .sec-dd { position:relative; }
    .sec-dd-btn {
        width:100%; height:36px; display:flex; align-items:center; justify-content:space-between; gap:8px; padding:0 12px;
        border:1px solid #e5e7eb; border-radius:9px; background:#fff; color:#1a1a1a; cursor:pointer;
        font-family:'Poppins',sans-serif; font-size:12px; text-align:left; transition:border-color .15s, background .15s;
    }
    .sec-dd-btn:hover:not(:disabled) { border-color:#d1d5db; }
    .sec-dd-btn:disabled { background:#f6f6f7; color:#aaa; cursor:not-allowed; }
    .sec-dd-btn .lbl { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:flex; align-items:center; gap:7px; }
    .sec-dd-btn .lbl i { font-size:11px; color:#9ca3af; }
    .sec-dd-btn.is-specific { border-color:#ecc7cd; background:var(--primary-light); color:var(--primary); font-weight:600; }
    .sec-dd-btn.is-specific .lbl i { color:var(--primary); }
    .sec-dd-btn .chev { font-size:10px; color:#9ca3af; transition:transform .15s; }
    .sec-dd.open .sec-dd-btn { border-color:var(--primary); box-shadow:0 0 0 3px rgba(123,29,29,.1); }
    .sec-dd.open .chev { transform:rotate(180deg); }
    /* Fixed so the modal's scroll area never clips it; placed by JS. */
    .sec-dd-menu { display:none; position:fixed; z-index:2600; width:230px; max-height:260px; overflow-y:auto; background:#fff; border:1px solid #ececef; border-radius:12px; box-shadow:0 14px 34px rgba(16,24,40,.18); padding:6px; }
    .sec-dd.open .sec-dd-menu { display:block; }
    .sec-opt { display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:8px; cursor:pointer; font-size:12.5px; color:#333; }
    .sec-opt:hover { background:#f6f6f7; }
    .sec-opt input { width:15px; height:15px; accent-color:var(--primary); cursor:pointer; }
    .sec-opt small { margin-left:auto; font-size:10.5px; color:#aaa; }
    .sec-opt.all { font-weight:600; }
    .sec-dd-sep { height:1px; background:#f0f0f2; margin:5px 4px; }
    .sec-dd-empty { padding:10px; font-size:11.5px; color:#999; line-height:1.5; }
    @media (max-width:560px) {
        .subj-list-head { display:none; }
        .subj-row { grid-template-columns:minmax(0, 1fr); }
    }
</style>
@endonce
<div class="subj-list">
    <div class="subj-list-head"><span>Subject</span><span>Sections</span></div>
    @foreach ($subjects as $s)
        @php
            $isChecked = $checkedSubjects->contains($s->id);
            $checkedSecIds = collect($checkedSectionsBySubject[$s->id] ?? []);
        @endphp
        <div class="subj-row {{ $isChecked ? 'is-on' : '' }}" data-subject-row="{{ $s->id }}">
            <label class="subj-pick">
                <input type="checkbox" name="subjects[]" value="{{ $s->id }}" data-sid="{{ $s->id }}"
                       {{ $isChecked ? 'checked' : '' }} onchange="onSubjectToggle({{ $s->id }})">
                <span class="subj-code">{{ $s->code }}</span>
                <span class="subj-name">{{ $s->name }}</span>
            </label>

            <div class="sec-dd" id="secDd{{ $s->id }}">
                <button type="button" class="sec-dd-btn" onclick="toggleSectionDropdown(event, {{ $s->id }})"
                        {{ $isChecked ? '' : 'disabled' }} aria-haspopup="true" title="Tick the subject first, then choose its sections">
                    <span class="lbl"><i class="fas fa-globe"></i> <span class="txt">All sections</span></span>
                    <i class="fas fa-chevron-down chev"></i>
                </button>
                <div class="sec-dd-menu" onclick="event.stopPropagation()">
                    <label class="sec-opt all">
                        <input type="checkbox" class="sec-all" {{ $checkedSecIds->isEmpty() ? 'checked' : '' }} onchange="onAllSections({{ $s->id }}, this.checked)">
                        All sections
                    </label>
                    <div class="sec-dd-sep"></div>
                    @forelse ($sections as $sec)
                        <label class="sec-opt">
                            <input type="checkbox" name="sections[{{ $s->id }}][]" value="{{ $sec->id }}" data-secid="{{ $sec->id }}" data-name="{{ $sec->name }}"
                                   {{ $checkedSecIds->contains($sec->id) ? 'checked' : '' }} onchange="onSectionPick({{ $s->id }})">
                            {{ $sec->name }}
                            @if ($sec->year_level)<small>{{ $yearLabels[$sec->year_level] ?? '' }}</small>@endif
                        </label>
                    @empty
                        <div class="sec-dd-empty">No sections yet — add one on the Students &amp; Sections page.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>
