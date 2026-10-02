{{-- Shared interactivity for the subject + section dropdown picker (see subject-section-fields.blade.php). --}}
<script>
    const sectionRow = sid => document.querySelector(`[data-subject-row="${sid}"]`);
    const sectionBoxes = sid => [...document.querySelectorAll(`input[name="sections[${sid}][]"]`)];

    // Button label: "All sections", one section's name, or "N sections".
    function refreshSectionLabel(sid) {
        const row = sectionRow(sid);
        if (!row) return;
        const picked = sectionBoxes(sid).filter(cb => cb.checked);
        const btn = row.querySelector('.sec-dd-btn');
        const names = picked.map(cb => cb.dataset.name);
        row.querySelector('.sec-dd-btn .txt').textContent = picked.length === 0 ? 'All sections'
            : picked.length <= 2 ? names.join(', ') : `${picked.length} sections`;
        btn.classList.toggle('is-specific', picked.length > 0);
        btn.querySelector('.lbl i').className = picked.length ? 'fas fa-list-check' : 'fas fa-globe';
        btn.title = picked.length ? names.join(', ') : 'Every section can see this subject';
        row.querySelector('.sec-all').checked = picked.length === 0;
    }

    // Tick/untick a subject. Unticking clears its sections back to "All sections".
    function onSubjectToggle(sid) {
        const row = sectionRow(sid);
        const on = row.querySelector('input[name="subjects[]"]').checked;
        row.classList.toggle('is-on', on);
        row.querySelector('.sec-dd-btn').disabled = !on;
        if (!on) {
            sectionBoxes(sid).forEach(cb => { cb.checked = false; });
            closeSectionDropdowns();
        }
        refreshSectionLabel(sid);
    }

    // "All sections" clears any specific picks; picking a section unticks "All".
    function onAllSections(sid, checked) {
        if (checked) sectionBoxes(sid).forEach(cb => { cb.checked = false; });
        refreshSectionLabel(sid);
    }
    function onSectionPick(sid) { refreshSectionLabel(sid); }

    // Set a subject's state from outside (the quick-assign modal pre-fill).
    function setSubjectSections(sid, checked, sectionIds) {
        const row = sectionRow(sid);
        if (!row) return;
        row.querySelector('input[name="subjects[]"]').checked = checked;
        const ids = (sectionIds || []).map(Number);
        sectionBoxes(sid).forEach(cb => { cb.checked = checked && ids.includes(Number(cb.value)); });
        onSubjectToggle(sid);
    }

    function closeSectionDropdowns() { document.querySelectorAll('.sec-dd.open').forEach(d => d.classList.remove('open')); }
    function toggleSectionDropdown(event, sid) {
        event.stopPropagation();
        const dd = document.getElementById(`secDd${sid}`);
        const wasOpen = dd.classList.contains('open');
        closeSectionDropdowns();
        if (wasOpen) return;
        dd.classList.add('open');
        const menu = dd.querySelector('.sec-dd-menu');
        const r = dd.querySelector('.sec-dd-btn').getBoundingClientRect();
        const below = r.bottom + 6 + menu.offsetHeight <= window.innerHeight;
        menu.style.top = (below ? r.bottom + 6 : r.top - 6 - menu.offsetHeight) + 'px';
        menu.style.left = Math.max(8, r.right - menu.offsetWidth) + 'px';
    }
    document.addEventListener('click', closeSectionDropdowns);
    document.addEventListener('scroll', e => { if (!e.target.closest?.('.sec-dd-menu')) closeSectionDropdowns(); }, true);
    window.addEventListener('resize', closeSectionDropdowns);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSectionDropdowns(); });

    // Labels for anything pre-checked by the server (edit form / old input).
    document.querySelectorAll('[data-subject-row]').forEach(row => refreshSectionLabel(row.dataset.subjectRow));
</script>
