<?php

namespace Tests\Feature;

use App\Models\CurriculumVersion;
use App\Models\Topic;
use App\Support\CurriculumScope;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsCurriculumSchema;
use Tests\TestCase;

/**
 * Curriculum versioning: a chair starts a blank DRAFT curriculum, builds it
 * while students keep studying the ACTIVE one, then publishes it (the old one
 * becomes read-only history) or discards it.
 */
class CurriculumVersionTest extends TestCase
{
    use BuildsCurriculumSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildCurriculumSchema();
    }

    protected function tearDown(): void
    {
        $this->dropCurriculumSchema();
        parent::tearDown();
    }

    public function test_starting_a_new_curriculum_creates_an_empty_draft_and_leaves_the_active_one_alone(): void
    {
        $chair = $this->chair();
        $subjectId = $this->subject('FAR');
        $this->topic($subjectId, $this->activeId, 'Inventories');
        $faculty = $this->faculty($subjectId);

        $this->actingAs($chair)->post(route('chair.curriculum.store'), [
            'label' => 'CPALE 2027', 'effective_from_batch' => '2027-2028',
        ])->assertRedirect();

        $draft = CurriculumVersion::where('status', 'draft')->first();
        $this->assertNotNull($draft);
        $this->assertSame(0, Topic::where('curriculum_version_id', $draft->id)->count(), 'a new curriculum starts blank');
        $this->assertSame('active', CurriculumVersion::find($this->activeId)->status);

        // The page opens on the draft and its (empty) topic lists.
        $this->actingAs($chair)->get(route('chair.subjects'))
            ->assertOk()
            ->assertSee('CPALE 2027')
            ->assertSee('No topics in this draft yet', false)
            ->assertDontSee('Inventories')
            // The switcher's onchange must stay one well-formed attribute: a
            // stray double quote inside it once left the <select> unclosed
            // and the browser swallowed the rest of the page.
            ->assertSee("onchange=\"window.location = '" . route('chair.subjects') . "?version=' + encodeURIComponent(this.value)\">", false);

        $this->assertSame(1, DB::table('notifications')->where('recipient_id', $faculty->id)->where('type', 'curriculum_update')->count());
    }

    public function test_only_one_draft_can_be_in_progress(): void
    {
        $chair = $this->chair();
        $this->draft();

        $this->actingAs($chair)->post(route('chair.curriculum.store'), [
            'label' => 'Another', 'effective_from_batch' => '2028-2029',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertSame(1, CurriculumVersion::where('status', 'draft')->count());
    }

    public function test_the_first_batch_must_be_a_school_year(): void
    {
        $this->actingAs($this->chair())->post(route('chair.curriculum.store'), [
            'label' => 'Bad', 'effective_from_batch' => '2027',
        ])->assertSessionHasErrors('effective_from_batch');

        $this->assertSame(0, CurriculumVersion::where('status', 'draft')->count());
    }

    public function test_a_topic_added_while_viewing_the_draft_goes_into_the_draft_and_may_reuse_an_active_name(): void
    {
        $chair = $this->chair();
        $subjectId = $this->subject('FAR');
        $this->topic($subjectId, $this->activeId, 'Inventories');
        $draftId = $this->draft();

        $this->actingAs($chair)->post(route('chair.subjects.topics.store', $subjectId), [
            'name' => 'Inventories', 'sort_order' => 1, 'is_active' => '1',
            'curriculum_version_id' => $draftId,
        ])->assertSessionDoesntHaveErrors();

        $this->assertSame(1, Topic::where('curriculum_version_id', $draftId)->where('name', 'Inventories')->count());
        $this->assertSame(1, Topic::where('curriculum_version_id', $this->activeId)->where('name', 'Inventories')->count());
    }

    public function test_students_only_ever_see_the_active_curriculum_until_the_draft_is_published(): void
    {
        $chair = $this->chair();
        $subjectId = $this->subject('FAR');
        $old = $this->topic($subjectId, $this->activeId, 'Old Topic');
        $draftId = $this->draft();
        $new = $this->topic($subjectId, $draftId, 'New Topic');

        $this->assertSame([$old], Topic::inActiveCurriculum()->pluck('id')->all());
        $this->assertSame([$old], CurriculumScope::restrictToActive(DB::table('topics'))->pluck('id')->all());

        $this->actingAs($chair)->post(route('chair.curriculum.publish', $draftId))->assertRedirect();

        $this->assertSame([$new], Topic::inActiveCurriculum()->pluck('id')->all());
    }

    public function test_publishing_archives_the_old_curriculum_and_closes_its_batch_range(): void
    {
        $chair = $this->chair();
        $subjectId = $this->subject('FAR');
        $draftId = $this->draft('2027-2028');
        $this->topic($subjectId, $draftId, 'New Topic');
        $faculty = $this->faculty($subjectId);

        $this->actingAs($chair)->post(route('chair.curriculum.publish', $draftId))->assertRedirect();

        $old = CurriculumVersion::find($this->activeId);
        $this->assertSame('archived', $old->status);
        $this->assertSame('2026-2027', $old->effective_to_batch, 'the old curriculum covers batches up to the one before the new curriculum');
        $this->assertSame('active', CurriculumVersion::find($draftId)->status);
        $this->assertNotNull(CurriculumVersion::find($draftId)->published_at);
        $this->assertSame(1, DB::table('notifications')->where('recipient_id', $faculty->id)->where('title', 'New curriculum is now live')->count());
    }

    public function test_a_draft_cannot_be_published_while_an_active_subject_has_no_topics(): void
    {
        $chair = $this->chair();
        $far = $this->subject('FAR');
        $this->subject('AUD');
        $draftId = $this->draft();
        $this->topic($far, $draftId, 'Only FAR has topics');

        $this->actingAs($chair)->post(route('chair.curriculum.publish', $draftId))
            ->assertRedirect()->assertSessionHas('error');

        $this->assertSame('draft', CurriculumVersion::find($draftId)->status);
        $this->assertSame('active', CurriculumVersion::find($this->activeId)->status);
    }

    public function test_an_archived_curriculum_is_read_only(): void
    {
        $chair = $this->chair();
        $subjectId = $this->subject('FAR');
        $archivedTopic = $this->topic($subjectId, $this->activeId, 'Historic');
        $draftId = $this->draft();
        $this->topic($subjectId, $draftId, 'Current');
        $this->actingAs($chair)->post(route('chair.curriculum.publish', $draftId));

        $this->actingAs($chair)->put(route('chair.subjects.topics.update', [$subjectId, $archivedTopic]), [
            'name' => 'Rewritten history', 'sort_order' => 1, 'is_active' => '1',
        ])->assertSessionHas('error');
        $this->actingAs($chair)->delete(route('chair.subjects.topics.destroy', [$subjectId, $archivedTopic]))
            ->assertSessionHas('error');
        $this->actingAs($chair)->post(route('chair.subjects.topics.store', $subjectId), [
            'name' => 'Sneaked in', 'sort_order' => 1, 'is_active' => '1', 'curriculum_version_id' => $this->activeId,
        ])->assertSessionHas('error');

        $this->assertSame('Historic', Topic::find($archivedTopic)->name);
        $this->assertSame(0, Topic::where('name', 'Sneaked in')->count());

        // History stays viewable, without any edit controls.
        $this->actingAs($chair)->get(route('chair.subjects', ['version' => $this->activeId]))
            ->assertOk()
            ->assertSee('Historic')
            ->assertSee('read-only history', false)
            ->assertDontSee('title="Edit topic"', false)
            ->assertDontSee('<i class="fas fa-plus"></i> Add Topic</button>', false);
    }

    public function test_discarding_a_draft_removes_only_what_was_prepared_under_it(): void
    {
        $chair = $this->chair();
        $subjectId = $this->subject('FAR');
        $keptTopic = $this->topic($subjectId, $this->activeId, 'Kept');
        $keptQuestion = $this->question($keptTopic, 'Kept question');
        $draftId = $this->draft();
        $draftParent = $this->topic($subjectId, $draftId, 'Draft parent');
        $draftChild = $this->topic($subjectId, $draftId, 'Draft child', $draftParent);
        $this->question($draftChild, 'Draft question');

        $this->actingAs($chair)->delete(route('chair.curriculum.destroy', $draftId))->assertRedirect();

        $this->assertNull(CurriculumVersion::find($draftId));
        $this->assertSame(0, Topic::where('curriculum_version_id', $draftId)->count());
        $this->assertSame(0, DB::table('questions')->where('question_text', 'Draft question')->count());
        $this->assertNotNull(Topic::find($keptTopic));
        $this->assertNotNull(DB::table('questions')->find($keptQuestion));
    }

    public function test_copying_questions_matches_topics_by_their_path_and_leaves_the_originals(): void
    {
        $chair = $this->chair();
        $subjectId = $this->subject('FAR');

        // "Nature" exists under two parents, so matching must use the path.
        $ppe = $this->topic($subjectId, $this->activeId, 'Property, Plant and Equipment');
        $ppeNature = $this->topic($subjectId, $this->activeId, 'Nature', $ppe);
        $ip = $this->topic($subjectId, $this->activeId, 'Investment Property');
        $ipNature = $this->topic($subjectId, $this->activeId, 'Nature', $ip);
        $dropped = $this->topic($subjectId, $this->activeId, 'Dropped Topic');
        $original = $this->question($ppeNature, 'What is PPE?');
        $this->question($ipNature, 'What is investment property?');
        $this->question($dropped, 'Obsolete question');

        $draftId = $this->draft();
        $newPpe = $this->topic($subjectId, $draftId, 'Property, Plant and Equipment');
        $newPpeNature = $this->topic($subjectId, $draftId, 'Nature', $newPpe);
        $newIp = $this->topic($subjectId, $draftId, 'Investment Property');
        $newIpNature = $this->topic($subjectId, $draftId, 'Nature', $newIp);

        $this->actingAs($chair)->post(route('chair.curriculum.copy-questions', [$draftId, $subjectId]))
            ->assertRedirect()->assertSessionHas('status');

        $this->assertSame(['What is PPE?'], DB::table('questions')->where('topic_id', $newPpeNature)->pluck('question_text')->all());
        $this->assertSame(['What is investment property?'], DB::table('questions')->where('topic_id', $newIpNature)->pluck('question_text')->all());
        $this->assertSame(2, DB::table('question_choices')->whereIn('question_id', DB::table('questions')->where('topic_id', $newPpeNature)->pluck('id'))->count(), 'choices are copied with the question');
        $this->assertSame($ppeNature, (int) DB::table('questions')->find($original)->topic_id, 'the original stays on the old curriculum');

        // Running it again copies nothing new.
        $this->actingAs($chair)->post(route('chair.curriculum.copy-questions', [$draftId, $subjectId]));
        $this->assertSame(1, DB::table('questions')->where('topic_id', $newPpeNature)->count());
    }

    public function test_the_test_bank_follows_the_faculty_members_curriculum_switch(): void
    {
        $subjectId = $this->subject('FAR');
        $faculty = $this->faculty($subjectId);
        $draftId = $this->draft();

        $this->actingAs($faculty);
        $this->assertSame($this->activeId, CurriculumScope::testBankVersionId());

        $this->post(route('faculty.test-bank.curriculum'), ['curriculum' => 'draft'])->assertRedirect();
        $this->assertSame($draftId, CurriculumScope::testBankVersionId());

        $this->post(route('faculty.test-bank.curriculum'), ['curriculum' => 'current']);
        $this->assertSame($this->activeId, CurriculumScope::testBankVersionId());
    }

    public function test_faculty_cannot_manage_curriculum_versions(): void
    {
        $subjectId = $this->subject('FAR');
        $faculty = $this->faculty($subjectId);
        $draftId = $this->draft();

        $this->actingAs($faculty)->post(route('chair.curriculum.store'), ['label' => 'x', 'effective_from_batch' => '2030-2031'])->assertForbidden();
        $this->actingAs($faculty)->post(route('chair.curriculum.publish', $draftId))->assertForbidden();
        $this->actingAs($faculty)->delete(route('chair.curriculum.destroy', $draftId))->assertForbidden();
    }
}
