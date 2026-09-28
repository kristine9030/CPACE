<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * <x-hint> and <x-tip>: explanations stay folded until the reader asks for them.
 */
class HintComponentsTest extends TestCase
{
    public function test_a_hint_with_details_folds_them_behind_a_summary(): void
    {
        $html = (string) $this->blade('<x-hint tone="warn" title="Locked.">Why it is locked.</x-hint>');

        $this->assertStringContainsString('<details', $html);
        $this->assertStringContainsString('cx-hint--warn', $html);
        $this->assertMatchesRegularExpression('/<summary[^>]*>.*Locked\..*<\/summary>/s', $html);
        $this->assertMatchesRegularExpression('/<div class="cx-hint-body">\s*Why it is locked\./', $html);
    }

    public function test_a_hint_without_details_is_a_plain_notice(): void
    {
        $html = (string) $this->blade('<x-hint title="Just a headline." />');

        $this->assertStringNotContainsString('<details', $html);
        $this->assertStringContainsString('role="note"', $html);
        $this->assertStringContainsString('Just a headline.', $html);
    }

    public function test_a_hint_title_slot_keeps_its_markup(): void
    {
        // Blade needs whitespace after </x-slot:title>, or "@endslotMore." is not read as @endslot.
        $this->blade("<x-hint><x-slot:title><strong>Draft</strong> only.</x-slot:title>\nMore.</x-hint>")
            ->assertSee('<strong>Draft</strong> only.', false);
    }

    public function test_a_tip_button_is_described_by_its_hidden_text(): void
    {
        $html = (string) $this->blade('<x-tip label="About flags">Flags are signals.</x-tip>');

        $this->assertMatchesRegularExpression('/<button type="button" class="cx-tip-btn" aria-label="About flags" aria-expanded="false" aria-describedby="(cx-tip-\w+)">/', $html);
        preg_match('/aria-describedby="(cx-tip-\w+)"/', $html, $m);
        $this->assertStringContainsString('id="' . $m[1] . '">Flags are signals.</span>', $html);
    }

    public function test_the_tip_script_and_styles_are_emitted_once_per_page(): void
    {
        $html = (string) $this->blade('<x-tip>One</x-tip><x-tip>Two</x-tip>');

        $this->assertSame(1, substr_count($html, "layer.id = 'cx-tip-layer'"));
        $this->assertSame(2, substr_count($html, 'class="cx-tip-btn"'));
    }
}
