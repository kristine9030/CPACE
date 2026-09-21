<?php

namespace Tests\Feature;

use App\Models\MockExam;
use App\Models\MockExamAudit;
use App\Models\MockExamEvent;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsMockExamSchema;
use Tests\TestCase;

/**
 * Program Chair side: reviewing, returning, and the one-way publish door -
 * including the rule that a published exam is frozen for everybody, and the
 * "one redeem code per exam day, shared across subjects" behaviour.
 */
class MockExamReviewTest extends TestCase
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

    public function test_publishing_generates_one_code_per_day_shared_by_every_subject_that_day(): void
    {
        $chair = $this->makeChair();
        $faculty = $this->makeFaculty('f@example.com', $this->farId);
        $sitting = now()->addWeek()->setTime(8, 0);

        $far = $this->reviewable($this->farId, $faculty->id, $sitting);
        // AUD sits the same day, at a different hour.
        $aud = $this->reviewable($this->audId, $faculty->id, $sitting->copy()->setTime(13, 0));

        $this->actingAs($chair)->post(route('chair.mock-exams.publish', $far))->assertRedirect();
        $this->actingAs($chair)->post(route('chair.mock-exams.publish', $aud))->assertRedirect();

        $this->assertSame(1, MockExamEvent::count(), 'Both subjects on one date must share a single event.');

        $far->refresh();
        $aud->refresh();
        $this->assertSame($far->event_id, $aud->event_id);
        $this->assertMatchesRegularExpression(
            '/^MOCK-\d{8}-[A-HJ-NP-Z2-9]{4}$/',
            MockExamEvent::first()->access_code
        );
    }

    public function test_a_published_exam_can_no_longer_be_edited_by_the_chair_or_the_faculty(): void
    {
        $chair = $this->makeChair();
        $faculty = $this->makeFaculty('f@example.com', $this->farId);
        $exam = $this->reviewable($this->farId, $faculty->id, now()->addWeek());

        $this->actingAs($chair)->post(route('chair.mock-exams.publish', $exam));
        $exam->refresh();
        $this->assertTrue($exam->isPublished());

        $payload = [
            'version' => $exam->version,
            'title' => 'Tampered title',
            'scheduled_at' => now()->addWeeks(2)->format('Y-m-d\TH:i'),
            'duration_minutes' => 60,
            'topic_mode' => 'manual', 'question_mode' => 'manual',
            'topic_ids' => [], 'items_json' => '[]',
        ];

        $this->actingAs($chair)->put(route('chair.mock-exams.update', $exam), $payload)->assertForbidden();
        $this->actingAs($faculty)->put(route('faculty.mock-exams.update', $exam), $payload)->assertForbidden();
        $this->actingAs($faculty)->post(route('faculty.mock-exams.submit', $exam))->assertForbidden();

        $this->assertSame('FAR Mock Exam', MockExam::find($exam->id)->title);
    }

    public function test_the_chair_can_return_an_exam_with_a_note_instead_of_publishing(): void
    {
        $chair = $this->makeChair();
        $faculty = $this->makeFaculty('f@example.com', $this->farId);
        $exam = $this->reviewable($this->farId, $faculty->id, now()->addWeek());

        $this->actingAs($chair)->post(route('chair.mock-exams.return', $exam), [
            'review_note' => 'Questions 3 and 7 are duplicates.',
        ])->assertRedirect();

        $exam->refresh();
        $this->assertTrue($exam->isDraft());
        $this->assertSame('Questions 3 and 7 are duplicates.', $exam->review_note);
        $this->assertNull($exam->submitted_for_review_at);
        $this->assertSame(0, MockExamEvent::count(), 'Returning must not generate a code.');

        $audit = DB::table('mock_exam_audits')->where('action', MockExamAudit::ACTION_RETURNED)->first();
        $this->assertSame($chair->id, (int) $audit->user_id);
    }

    public function test_returning_requires_a_note_so_the_faculty_knows_what_to_fix(): void
    {
        $chair = $this->makeChair();
        $faculty = $this->makeFaculty('f@example.com', $this->farId);
        $exam = $this->reviewable($this->farId, $faculty->id, now()->addWeek());

        $this->actingAs($chair)->post(route('chair.mock-exams.return', $exam), ['review_note' => ''])
            ->assertSessionHasErrors('review_note');

        $this->assertTrue(MockExam::find($exam->id)->isForReview());
    }

    public function test_a_chair_edit_is_attributed_to_the_chair_in_the_audit_trail(): void
    {
        $chair = $this->makeChair();
        $faculty = $this->makeFaculty('f@example.com', $this->farId);
        $topicId = $this->makeTopic($this->farId);
        $exam = $this->reviewable($this->farId, $faculty->id, now()->addWeek());

        $this->actingAs($chair)->put(route('chair.mock-exams.update', $exam), [
            'version' => $exam->version,
            'title' => 'FAR Mock Exam (revised)',
            'scheduled_at' => $exam->scheduled_at->format('Y-m-d\TH:i'),
            'duration_minutes' => 180,
            'topic_mode' => 'manual', 'question_mode' => 'manual',
            'topic_ids' => [$topicId],
            'items_json' => json_encode([['source_question_id' => $this->makeQuestion($topicId)]]),
        ])->assertSessionHasNoErrors();

        $settings = DB::table('mock_exam_audits')
            ->where('exam_id', $exam->id)
            ->where('action', MockExamAudit::ACTION_SETTINGS)
            ->first();

        $this->assertNotNull($settings);
        $this->assertSame($chair->id, (int) $settings->user_id);
        $this->assertStringContainsString('Title', $settings->details);
    }

    public function test_an_exam_with_no_questions_cannot_be_published(): void
    {
        $chair = $this->makeChair();
        $faculty = $this->makeFaculty('f@example.com', $this->farId);
        $exam = MockExam::create([
            'subject_id' => $this->farId, 'created_by' => $faculty->id,
            'title' => 'Empty', 'status' => MockExam::STATUS_FOR_REVIEW,
            'scheduled_at' => now()->addWeek(), 'duration_minutes' => 180,
        ]);

        $this->actingAs($chair)->post(route('chair.mock-exams.publish', $exam))
            ->assertSessionHasErrors('items_json');

        $this->assertSame(0, MockExamEvent::count());
        $this->assertFalse(MockExam::find($exam->id)->isPublished());
    }

    public function test_an_exam_whose_sitting_has_passed_cannot_be_published(): void
    {
        $chair = $this->makeChair();
        $faculty = $this->makeFaculty('f@example.com', $this->farId);
        $exam = $this->reviewable($this->farId, $faculty->id, now()->subDay());

        $this->actingAs($chair)->post(route('chair.mock-exams.publish', $exam))
            ->assertSessionHasErrors('scheduled_at');
    }

    public function test_a_faculty_member_cannot_reach_the_chairs_review_screens(): void
    {
        $faculty = $this->makeFaculty('f@example.com', $this->farId);
        $exam = $this->reviewable($this->farId, $faculty->id, now()->addWeek());

        $this->actingAs($faculty)->get(route('chair.mock-exams'))->assertForbidden();
        $this->actingAs($faculty)->post(route('chair.mock-exams.publish', $exam))->assertForbidden();
    }

    // ── helpers ──────────────────────────────────────────────────────────

    /** An exam sitting in for_review with one real question attached. */
    private function reviewable(int $subjectId, int $facultyId, $sitting): MockExam
    {
        $topicId = $this->makeTopic($subjectId, 'Topic ' . uniqid());
        $exam = MockExam::create([
            'subject_id' => $subjectId,
            'created_by' => $facultyId,
            'title' => $subjectId === $this->farId ? 'FAR Mock Exam' : 'AUD Mock Exam',
            'status' => MockExam::STATUS_FOR_REVIEW,
            'scheduled_at' => $sitting,
            'duration_minutes' => 180,
            'total_items' => 1,
            'submitted_for_review_at' => now(),
        ]);

        DB::table('mock_exam_items')->insert([
            'exam_id' => $exam->id,
            'source_question_id' => $this->makeQuestion($topicId),
            'topic_id' => $topicId,
            'question_text' => 'A question',
            'choices' => json_encode([
                ['label' => 'A', 'text' => 'Right', 'is_correct' => true],
                ['label' => 'B', 'text' => 'Wrong', 'is_correct' => false],
            ]),
            'points' => 1, 'sort_order' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return $exam;
    }
}
