{{-- Shared interactivity for the subject + section-scope picker (see subject-section-fields.blade.php). --}}
<script>
    // Show/hide a subject's section-scope picker when its checkbox is toggled.
    // Unchecking the subject clears everything underneath it (scope resets to "All sections").
    function toggleSectionPicker(subjectId, forceOpen) {
        const picker = document.getElementById(`sectionPicker${subjectId}`);
        if (!picker) return;
        const cb = document.querySelector(`input[name="subjects[]"][data-sid="${subjectId}"]`);
        const show = forceOpen !== undefined ? forceOpen : (cb && cb.checked);
        picker.style.display = show ? 'block' : 'none';
        if (!show) {
            picker.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = false);
            const allRadio = picker.querySelector('input[value="all"]');
            if (allRadio) allRadio.checked = true;
            setScope(subjectId, 'all');
        }
    }

    // Switches a subject between "All sections" (unrestricted, the default) and
    // "Specific sections" (reveals the checkbox list; picking none still behaves as "All").
    function setScope(subjectId, mode) {
        const chips = document.getElementById(`sectionChips${subjectId}`);
        if (!chips) return;
        chips.style.display = mode === 'specific' ? 'flex' : 'none';
        if (mode === 'all') {
            chips.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = false);
        }
    }
</script>
