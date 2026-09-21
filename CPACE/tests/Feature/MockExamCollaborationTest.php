<?php

namespace Tests\Feature;

use App\Models\MockExam;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsMockExamSchema;
use Tests\TestCase;

/**
 * Two faculty assigned to the same subject share one exam - the creator holds
 * no special rights over a co-assigned colleague. The optimistic-lock guard
 * exists so simultaneous editing cannot silently destroy someone's work.
 */
class MockExamCollaborationTest extends TestCase
{
    use BuildsMockExamSchema;

    private int $farId;
    private int $audId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildMockExamSchema();
        $this->farId = $this->makeSubject('FAR', 'Financial Accounting');
        $this->audId = $this->makeSubject('AUD', 'Auditing');
    }

    protected function tearDown(): void
    {
        $this->dropMockExamSchema();
        parent::tearDown();
    }

    public function test_a_second_faculty_assigned_to_the_subject_can_edit_the_same_exam(): void
    {
        $owner = $this->makeFaculty('owner@example.com', $this->farId);
        $colleague = $this->makeFaculty('colleague@example.com', $this->farId);
        $topicId = $this->makeTopic($this->farId);
        $exam = $this->draft($owner->id);

        $this->actingAs($colleague)->get(route('faculty.mock-exams.build', $exam))->assertOk();

        $this->actingAs($colleague)
            ->put(route('faculty.mock-exams.update', $exam), $this->payload($exam, $topicId, 'Colleague edit'))
            ->assertSessionHasNoErrors();

        $this->assertSame('Colleague edit', MockExam::find($exam->id)->title);

        // ...and the trail names the colleague, not the creator.
        $audit = DB::table('mock_exam_audits')->where('action', 'settings_changed')->first();
        $this->assertSame($colleague->id, (int) $audit->user_id);
    }

    public function test_a_faculty_member_not_assigned_to_the_subject_is_refused(): void
    {
        $owner = $this->makeFaculty('owner@example.com', $this->farId);
        $outsider = $this->makeFaculty('aud@example.com', $this->audId);
        $topicId = $this->makeTopic($this->farId);
        $exam = $this->draft($owner->id);

        $this->actingAs($outsider)->get(route('faculty.mock-exams.build', $exam))->assertForbidden();
        $this->actingAs($outsider)
            ->put(route('faculty.mock-exams.update', $exam), $this->payload($exam, $topicId, 'Hijacked'))
            ->assertForbidden();

        $this->assertSame('FAR Mock Exam', MockExam::find($exam->id)->title);
    }

    public function test_a_save_built_on_a_stale_copy_is_rejected_rather_than_clobbering(): void
    {
        $owner = $this->makeFaculty('owner@example.com', $this->farId);
        $colleague = $this->makeFaculty('colleague@example.com', $this->farId);
        $topicId = $this->makeTopic($this->farId);
        $exam = $this->draft($owner->id);

        // Read the persisted value: the version column is a DB default, so a
        // freshly created model has not hydrated it yet.
        $staleVersion = $exam->refresh()->version;

        // The colleague saves first, bumping the version.
        $this->actingAs($colleague)
            ->put(route('faculty.mock-exams.update', $exam), $this->payload($exam, $topicId, 'Colleague was here'))
            ->assertSessionHasNoErrors();

        // The owner's browser still holds the old version number.
        $this->actingAs($owner)->put(route('faculty.mock-exams.update', $exam), array_merge(
            $this->payload($exam, $topicId, 'Owner overwrite'),
            ['version' => $staleVersion]
        ))->assertSessionHasErrors('version');

        $this->assertSame(
            'Colleague was here',
            MockExam::find($exam->id)->title,
            'The stale save must not have overwritten the colleague.'
        );
    }

    public function test_the_chair_can_always_edit_an_unpublished_exam_regardless_of_subject(): void
    {
        $chair = $this->makeChair();
        $owner = $this->makeFaculty('owner@example.com', $this->farId);
        $exam = $this->draft($owner->id);

        $this->actingAs($chair)->get(route('chair.mock-exams.review', $exam))->assertOk();
    }

    // ── helpers ──────────────────────────────────────────────────────────

    private function draft(int $facultyId): MockExam
    {
        return MockExam::create([
            'subject_id' => $this->farId,
            'created_by' => $facultyId,
            'title' => 'FAR Mock Exam',
            'status' => MockExam::STATUS_DRAFT,
            'duration_minutes' => 180,
        ]);
    }

    private function payload(MockExam $exam, int $topicId, string $title): array
    {
        return [
            'version' => $exam->fresh()->version,
            'title' => $title,
            'scheduled_at' => now()->addWeek()->format('Y-m-d\TH:i'),
            'duration_minutes' => 180,
            'topic_mode' => 'manual',
            'question_mode' => 'manual',
            'topic_ids' => [$topicId],
            'items_json' => json_encode([['source_question_id' => $this->makeQuestion($topicId)]]),
        ];
    }
}
