{{-- The scenario (case) text shown above its questions.
     Expects $text (stored rich text). Optional: $label (e.g. "Questions 3 to 5"), $sticky (default true), $collapsed (default false).
     Styles come from partials.scenario-box-styles, included inside the page's <style>. --}}
<div class="sc-box {{ ($sticky ?? true) ? 'sticky' : '' }} {{ ($collapsed ?? false) ? 'collapsed' : '' }}">
    <button type="button" class="sc-head" onclick="this.closest('.sc-box').classList.toggle('collapsed')" aria-label="Show or hide the scenario">
        <span><i class="fas fa-file-lines"></i> Scenario @if(! empty($label))<span class="sc-sub">{{ $label }}</span>@endif</span>
        <i class="fas fa-chevron-up sc-chev"></i>
    </button>
    <div class="sc-body">{{ \App\Support\RichText::render($text) }}</div>
</div>
