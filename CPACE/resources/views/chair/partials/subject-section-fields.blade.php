{{--
    Shared "assign subjects + optionally limit to sections" field group.
    Used by both the Add/Edit Faculty form and the quick-assign modal on the
    faculty list, so the two stay visually and behaviorally identical.

    Expected variables:
    - $subjects: Collection of Subject
    - $sections: Collection of Section (active ones)
    - $checkedSubjects: array of subject ids to pre-check (default: [])
    - $checkedSectionsBySubject: array [subjectId => [sectionId, ...]] to pre-check (default: [])
--}}
@php
    $checkedSubjects = collect($checkedSubjects ?? []);
    $checkedSectionsBySubject = collect($checkedSectionsBySubject ?? []);
@endphp
<div class="check-grid">
    @foreach ($subjects as $s)
        @php
            $isChecked = $checkedSubjects->contains($s->id);
            $checkedSecIds = collect($checkedSectionsBySubject[$s->id] ?? []);
            $isSpecific = $checkedSecIds->isNotEmpty();
        @endphp
        <div>
            <label class="check-card">
                <input type="checkbox" name="subjects[]" value="{{ $s->id }}" data-sid="{{ $s->id }}"
                       {{ $isChecked ? 'checked' : '' }} onchange="toggleSectionPicker({{ $s->id }})">
                <span>
                    <span class="cc-code">{{ $s->code }}</span><br>
                    <span class="cc-name">{{ $s->name }}</span>
                </span>
            </label>
            <div class="section-picker" id="sectionPicker{{ $s->id }}" style="display:{{ $isChecked ? 'block' : 'none' }};">
                <div class="scope-toggle">
                    <label class="scope-opt">
                        <input type="radio" name="scope[{{ $s->id }}]" value="all"
                               {{ ! $isSpecific ? 'checked' : '' }} onchange="setScope({{ $s->id }}, 'all')">
                        <span><i class="fas fa-globe"></i> All sections</span>
                    </label>
                    <label class="scope-opt">
                        <input type="radio" name="scope[{{ $s->id }}]" value="specific"
                               {{ $isSpecific ? 'checked' : '' }} onchange="setScope({{ $s->id }}, 'specific')">
                        <span><i class="fas fa-list-check"></i> Specific sections</span>
                    </label>
                </div>
                <div class="section-chips" id="sectionChips{{ $s->id }}" style="display:{{ $isSpecific ? 'flex' : 'none' }};">
                    @forelse ($sections as $sec)
                        <label class="section-chip">
                            <input type="checkbox" name="sections[{{ $s->id }}][]" value="{{ $sec->id }}" data-secid="{{ $sec->id }}"
                                   {{ $checkedSecIds->contains($sec->id) ? 'checked' : '' }}>
                            {{ $sec->name }}
                        </label>
                    @empty
                        <span class="muted-note">No sections yet — add one on the Sections page.</span>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>
