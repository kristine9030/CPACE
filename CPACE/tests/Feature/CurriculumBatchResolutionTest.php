<?php

namespace Tests\Feature;

use App\Models\CurriculumVersion;
use App\Models\Topic;
use App\Support\CurriculumScope;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsCurriculumSchema;
use Tests\TestCase;

/**
 * Phase 2 of curriculum versioning: a student studies the curriculum that
 * covers their enrollment batch, so a batch keeps its curriculum after a newer
 * one is published for later batches. Plus editing a draft's details, and the
 * rule that a new curriculum must start after the current one.
 */
class CurriculumBatchResolutionTest extends TestCase
{
    use BuildsCurriculumSchema;

    /** Old curriculum: covers every batch up to 2026-2027 (no start batch). */
    private int $oldId;

    /** Current curriculum: 2027-2028 onward. */
    private int $currentId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildCurriculumSchema();

        // Reshape the trait's single active curriculum into a realistic
        // history: an archived one closed at 2026-2027, and the one after it.
        $this->oldId = $this->activeId;
        DB::table('curriculum_versions')->where('id', $this->oldId)->update([
            'label' => 'Old Curriculum', 'status' => 'archived',
            'effective_from_batch' => null, 'effective_to_batch' => '2026-2027',
        ]);
        $this->currentId = DB::table('curriculum_versions')->insertGetId([
            'label' => 'Current Curriculum', 'status' => 'active',
            'effective_from_batch' => '2027-2028', 'published_at' => now(), 'created_at' => now(),
        ]);
        CurriculumScope::flush();
    }

    protected function tearDown(): void
    {
        $this->dropCurriculumSchema();
        parent::tearDown();
    }

    public function test_a_batch_resolves_to_the_curriculum_whose_range_covers_it(): void
    {
        $draftId = $this->draft('2029-2030');

        $this->assertSame($this->oldId, CurriculumVersion::forBatch('2024-2025')?->id, 'no start batch means "from the beginning"');
        $this->assertSame($this->oldId, CurriculumVersion::forBatch('2026-2027')?->id, 'the last batch of a closed range is still covered');
        $this->assertSame($this->currentId, CurriculumVersion::forBatch('2027-2028')?->id);
        $this->assertSame($this->currentId, CurriculumVersion::forBatch('2031-2032')?->id, 'an open range covers every later batch');
        $this->assertNotSame($draftId, CurriculumVersion::forBatch('2030-2031')?->id, 'a draft never covers anyone');
        $this->assertNull(CurriculumVersion::forBatch(null));
    }

    public function test_each_student_is_scoped_to_their_own_batchs_curriculum(): void
    {
        $olderBatch = $this->studentInBatch('2026-2027', 'older@example.com');
        $newerBatch = $this->studentInBatch('2028-2029', 'newer@example.com');
        $noBatch = $this->studentInBatch(null, 'nobatch@example.com');

        $subjectId = $this->subject('FAR');
        $oldTopic = $this->topic($subjectId, $this->oldId, 'Old Topic');
        $newTopic = $this->topic($subjectId, $this->currentId, 'New Topic');

        $this->assertSame([$oldTopic], Topic::forStudent($olderBatch->id)->pluck('id')->all(),
            'a batch keeps its curriculum even after a newer one is published');
        $this->assertSame([$newTopic], Topic::forStudent($newerBatch->id)->pluck('id')->all());
        $this->assertSame([$newTopic], Topic::forStudent($noBatch->id)->pluck('id')->all(),
            'no batch on record falls back to the active curriculum');
    }

    public function test_a_batch_older_than_every_curriculum_falls_back_to_the_active_one(): void
    {
        // Give the old curriculum an explicit start, so 2019-2020 isn't covered by anything.
        DB::table('curriculum_versions')->where('id', $this->oldId)->update(['effective_from_batch' => '2022-2023']);
        CurriculumScope::flush();
        $student = $this->studentInBatch('2019-2020');

        $this->assertSame($this->currentId, CurriculumScope::forStudent($student->id));
    }

    public function test_a_draft_curriculums_name_and_first_batch_can_be_edited(): void
    {
        $chair = $this->chair();
        $draftId = $this->draft('2028-2029');

        $this->actingAs($chair)->put(route('chair.curriculum.update', $draftId), [
            'label' => 'CPALE TOS — Effective Oct 2029',
            'effective_from_batch' => '2029-2030',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $draft = CurriculumVersion::find($draftId);
        $this->assertSame('CPALE TOS — Effective Oct 2029', $draft->label);
        $this->assertSame('2029-2030', $draft->effective_from_batch);
        $this->assertSame(1, DB::table('curriculum_audits')->where('curriculum_version_id', $draftId)->where('action', 'version_edited')->count());
    }

    public function test_a_published_curriculums_details_are_locked(): void
    {
        $this->actingAs($this->chair())->put(route('chair.curriculum.update', $this->currentId), [
            'label' => 'Renamed', 'effective_from_batch' => '2030-2031',
        ])->assertSessionHas('error');

        $current = CurriculumVersion::find($this->currentId);
        $this->assertSame('Current Curriculum', $current->label);
        $this->assertSame('2027-2028', $current->effective_from_batch);
    }

    public function test_a_new_curriculum_cannot_start_at_or_before_the_current_ones_first_batch(): void
    {
        $chair = $this->chair();

        // The exact case found in testing: a draft starting BEFORE the active one.
        $this->actingAs($chair)->post(route('chair.curriculum.store'), [
            'label' => 'Backwards', 'effective_from_batch' => '2026-2027',
        ])->assertSessionHasErrors('effective_from_batch');

        $this->actingAs($chair)->post(route('chair.curriculum.store'), [
            'label' => 'Same start', 'effective_from_batch' => '2027-2028',
        ])->assertSessionHasErrors('effective_from_batch');

        $this->assertSame(0, CurriculumVersion::where('status', 'draft')->count());

        $this->actingAs($chair)->post(route('chair.curriculum.store'), [
            'label' => 'Next', 'effective_from_batch' => '2028-2029',
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, CurriculumVersion::where('status', 'draft')->count());
    }

    public function test_editing_a_draft_to_start_before_the_current_curriculum_is_rejected(): void
    {
        $draftId = $this->draft('2028-2029');

        $this->actingAs($this->chair())->put(route('chair.curriculum.update', $draftId), [
            'label' => 'Draft', 'effective_from_batch' => '2025-2026',
        ])->assertSessionHasErrors('effective_from_batch');

        $this->assertSame('2028-2029', CurriculumVersion::find($draftId)->effective_from_batch);
    }

    public function test_publishing_closes_the_current_curriculum_at_the_batch_before_the_new_one(): void
    {
        $chair = $this->chair();
        $subjectId = $this->subject('FAR');
        $draftId = $this->draft('2029-2030');
        $this->topic($subjectId, $draftId, 'Newest Topic');
        $olderStudent = $this->studentInBatch('2028-2029');

        $this->actingAs($chair)->post(route('chair.curriculum.publish', $draftId))->assertRedirect();
        CurriculumScope::flush();

        $this->assertSame('2028-2029', CurriculumVersion::find($this->currentId)->effective_to_batch);
        // The 2028-2029 student stays on the curriculum they started with.
        $this->assertSame($this->currentId, CurriculumScope::forStudent($olderStudent->id));
    }
}
