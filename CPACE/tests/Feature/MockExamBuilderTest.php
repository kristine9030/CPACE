<?php

namespace Tests\Feature;

use App\Models\MockExam;
use App\Models\MockExamAudit;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsMockExamSchema;
use Tests\TestCase;

/**
 * Faculty side of the mock exam workflow: who may build, the one-subject and
 * 100-item rules, the auto-picker, and the hand-off to the Program Chair.
 */
class MockExamBuilderTest extends TestCase
{
    use BuildsMockExamSchema;

    private int $subjectId;
    private int $otherSubjectId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildMockExamSchema();
        $this->subjectId = $this->makeSubject('FAR', 'Financial Accounting');
        $this->otherSubjectId = $this->makeSubject('AUD', 'Auditing');
    }

    protected function tearDown(): void
    {
        $this->dropMockExamSchema();
        parent::tearDown();
    }

    public function test_a_faculty_member_can_only_build_for_subjects_they_are_assigned_to(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);

        $this->actingAs($faculty)
            ->post(route('faculty.mock-exams.store'), ['subject_id' => $this->subjectId])
            ->assertRedirect();

        // AUD belongs to someone else.
        $this->actingAs($faculty)
            ->post(route('faculty.mock-exams.store'), ['subject_id' => $this->otherSubjectId])
            ->assertForbidden();

        $this->assertSame(1, MockExam::count());
        $this->assertSame($this->subjectId, MockExam::first()->subject_id);
    }

    public function test_creating_a_draft_records_who_started_it_in_the_audit_trail(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);

        $this->actingAs($faculty)->post(route('faculty.mock-exams.store'), ['subject_id' => $this->subjectId]);

        $exam = MockExam::first();
        $audit = DB::table('mock_exam_audits')->where('exam_id', $exam->id)->first();

        $this->assertSame(MockExamAudit::ACTION_CREATED, $audit->action);
        $this->assertSame($faculty->id, (int) $audit->user_id);
    }

    public function test_an_exam_cannot_hold_more_than_one_hundred_questions(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        $exam = $this->draft($faculty->id);

        // 101 real bank questions, all posted at once.
        $ids = collect(range(1, 101))->map(fn () => $this->makeQuestion($topicId))->all();

        $this->actingAs($faculty)
            ->put(route('faculty.mock-exams.update', $exam), $this->payload($exam, $topicId, $ids))
            ->assertSessionHasErrors('items_json');

        $this->assertSame(0, DB::table('mock_exam_items')->count());
    }

    public function test_fewer_than_one_hundred_questions_is_accepted(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        $exam = $this->draft($faculty->id);
        $ids = collect(range(1, 70))->map(fn () => $this->makeQuestion($topicId))->all();

        $this->actingAs($faculty)
            ->put(route('faculty.mock-exams.update', $exam), $this->payload($exam, $topicId, $ids))
            ->assertSessionHasNoErrors();

        $this->assertSame(70, DB::table('mock_exam_items')->count());
        $this->assertSame(70, MockExam::find($exam->id)->total_items);
    }

    public function test_items_are_snapshotted_from_the_bank_not_from_the_posted_payload(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        $questionId = $this->makeQuestion($topicId, 'easy', 'The real question');
        $exam = $this->draft($faculty->id);

        // A tampered payload tries to smuggle in its own text and answer key.
        $this->actingAs($faculty)->put(route('faculty.mock-exams.update', $exam), array_merge(
            $this->payload($exam, $topicId, []),
            ['items_json' => json_encode([[
                'source_question_id' => $questionId,
                'question_text' => 'Injected question',
                'choices' => [['label' => 'A', 'text' => 'Injected', 'is_correct' => true]],
            ]])]
        ));

        $item = DB::table('mock_exam_items')->first();
        $this->assertSame('The real question', $item->question_text);
        $this->assertStringNotContainsString('Injected', $item->choices);
    }

    public function test_auto_pick_spreads_evenly_across_the_selected_topics(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicA = $this->makeTopic($this->subjectId, 'Inventory');
        $topicB = $this->makeTopic($this->subjectId, 'Leases');

        foreach ([$topicA, $topicB] as $topic) {
            foreach (['easy', 'moderate', 'difficult'] as $difficulty) {
                for ($i = 0; $i < 10; $i++) {
                    $this->makeQuestion($topic, $difficulty);
                }
            }
        }

        $response = $this->actingAs($faculty)->postJson(route('faculty.mock-exams.auto-pick'), [
            'subject_id' => $this->subjectId,
            'topic_ids' => [$topicA, $topicB],
            'count' => 20,
        ])->assertOk();

        $questions = collect($response->json('questions'));
        $this->assertCount(20, $questions);
        $this->assertFalse($response->json('short'));
        // 20 across 2 topics is 10 each.
        $this->assertSame(10, $questions->where('topic_id', $topicA)->count());
        $this->assertSame(10, $questions->where('topic_id', $topicB)->count());
    }

    public function test_auto_pick_reports_a_shortfall_instead_of_silently_returning_fewer(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        for ($i = 0; $i < 5; $i++) {
            $this->makeQuestion($topicId);
        }

        $response = $this->actingAs($faculty)->postJson(route('faculty.mock-exams.auto-pick'), [
            'subject_id' => $this->subjectId,
            'topic_ids' => [$topicId],
            'count' => 40,
        ])->assertOk();

        $this->assertTrue($response->json('short'));
        $this->assertSame(5, $response->json('returned'));
        $this->assertSame(40, $response->json('requested'));
    }

    public function test_an_exam_cannot_be_submitted_for_review_without_a_schedule_or_questions(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $exam = $this->draft($faculty->id);

        $this->actingAs($faculty)
            ->post(route('faculty.mock-exams.submit', $exam))
            ->assertSessionHasErrors('scheduled_at');

        $exam->update(['scheduled_at' => now()->addWeek()]);

        $this->actingAs($faculty)
            ->post(route('faculty.mock-exams.submit', $exam))
            ->assertSessionHasErrors('items_json');

        $this->assertTrue(MockExam::find($exam->id)->isDraft());
    }

    public function test_submitting_for_review_moves_the_exam_and_notifies_the_chair(): void
    {
        $chair = $this->makeChair();
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $topicId = $this->makeTopic($this->subjectId);
        $exam = $this->draft($faculty->id, ['scheduled_at' => now()->addWeek()]);
        $this->seedItems($exam->id, $topicId, 3);
        $exam->update(['total_items' => 3]);

        $this->actingAs($faculty)->post(route('faculty.mock-exams.submit', $exam))->assertRedirect();

        $this->assertSame(MockExam::STATUS_FOR_REVIEW, MockExam::find($exam->id)->status);
        $this->assertNotNull(MockExam::find($exam->id)->submitted_for_review_at);
        $this->assertSame(1, DB::table('notifications')->where('recipient_id', $chair->id)->count());
    }

    public function test_a_faculty_member_cannot_delete_an_exam_once_it_left_draft(): void
    {
        $faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $exam = $this->draft($faculty->id, ['status' => MockExam::STATUS_FOR_REVIEW]);

        $this->actingAs($faculty)->delete(route('faculty.mock-exams.destroy', $exam))
            ->assertSessionHasErrors('exam');

        $this->assertSame(1, MockExam::count());
    }

    // ── helpers ──────────────────────────────────────────────────────────

    private function draft(int $facultyId, array $overrides = []): MockExam
    {
        return MockExam::create(array_merge([
            'subject_id' => $this->subjectId,
            'created_by' => $facultyId,
            'title' => 'FAR Mock Exam',
            'status' => MockExam::STATUS_DRAFT,
            'duration_minutes' => 180,
        ], $overrides));
    }

    private function payload(MockExam $exam, int $topicId, array $questionIds): array
    {
        return [
            'version' => $exam->version,
            'title' => $exam->title,
            'scheduled_at' => now()->addWeek()->format('Y-m-d\TH:i'),
            'duration_minutes' => 180,
            'topic_mode' => 'manual',
            'question_mode' => 'manual',
            'topic_ids' => [$topicId],
            'items_json' => json_encode(array_map(fn ($id) => ['source_question_id' => $id], $questionIds)),
        ];
    }

    private function seedItems(int $examId, int $topicId, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            DB::table('mock_exam_items')->insert([
                'exam_id' => $examId,
                'source_question_id' => $this->makeQuestion($topicId),
                'topic_id' => $topicId,
                'question_text' => "Q{$i}",
                'choices' => json_encode([
                    ['label' => 'A', 'text' => 'Right', 'is_correct' => true],
                    ['label' => 'B', 'text' => 'Wrong', 'is_correct' => false],
                ]),
                'points' => 1,
                'sort_order' => $i,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
