<?php

namespace Tests\Feature;

use App\Models\MockExam;
use App\Models\MockExamAttempt;
use App\Models\MockExamEvent;
use App\Models\MockExamRegistration;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsMockExamSchema;
use Tests\TestCase;

/**
 * Sitting and grading a mock exam: the scheduled window, server-side timing,
 * grading against the snapshot, and the deliberate analytics split - weak-area
 * detection is fed, but no points are awarded and no quiz_sessions row is
 * created, so mock results stay off the leaderboard and out of quiz history.
 */
class MockExamAttemptTest extends TestCase
{
    use BuildsMockExamSchema;

    private int $subjectId;
    private int $topicId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildMockExamSchema();
        $this->subjectId = $this->makeSubject('FAR', 'Financial Accounting');
        $this->topicId = $this->makeTopic($this->subjectId);
    }

    protected function tearDown(): void
    {
        $this->dropMockExamSchema();
        parent::tearDown();
    }

    public function test_a_student_cannot_start_before_the_scheduled_time(): void
    {
        [$student, $exam] = $this->registeredFor(now()->addHours(3));

        $this->actingAs($student)->post(route('mock-exams.start', $exam))
            ->assertSessionHasErrors('exam');

        $this->assertSame(0, MockExamAttempt::count());
    }

    public function test_a_student_cannot_start_after_the_window_closed(): void
    {
        // Started 5 hours ago with a 180-minute duration: long over.
        [$student, $exam] = $this->registeredFor(now()->subHours(5));

        $this->actingAs($student)->post(route('mock-exams.start', $exam))
            ->assertSessionHasErrors('exam');

        $this->assertSame(0, MockExamAttempt::count());
    }

    public function test_starting_inside_the_window_creates_one_attempt_and_never_restarts_the_clock(): void
    {
        [$student, $exam] = $this->registeredFor(now()->subMinutes(10));

        $this->actingAs($student)->post(route('mock-exams.start', $exam))
            ->assertRedirect(route('mock-exams.take', $exam));

        $first = MockExamAttempt::first();

        // Re-entering (a reload, a dropped connection) must reuse the attempt.
        $this->travel(5)->minutes();
        $this->actingAs($student)->post(route('mock-exams.start', $exam));

        $this->assertSame(1, MockExamAttempt::count());
        $this->assertEquals(
            $first->started_at->toDateTimeString(),
            MockExamAttempt::first()->started_at->toDateTimeString(),
            'Re-entering must not reset started_at.'
        );
    }

    public function test_the_exam_is_graded_against_the_stored_snapshot(): void
    {
        [$student, $exam] = $this->registeredFor(now()->subMinutes(10), itemCount: 4);
        $this->actingAs($student)->post(route('mock-exams.start', $exam));

        $items = $exam->items()->get();

        // Three right, one wrong.
        $answers = [];
        foreach ($items as $i => $item) {
            $answers[$item->id] = $i === 3 ? 'B' : 'A';
        }

        $this->actingAs($student)->post(route('mock-exams.submit', $exam), ['answers' => $answers])
            ->assertRedirect(route('mock-exams.result', $exam));

        $attempt = MockExamAttempt::first();
        $this->assertSame(3, $attempt->score);
        $this->assertSame(4, $attempt->total_points);
        $this->assertEquals(75.00, (float) $attempt->percent);
        $this->assertSame(MockExamAttempt::STATUS_SUBMITTED, $attempt->status);
    }

    public function test_an_unanswered_question_is_marked_wrong_not_skipped(): void
    {
        [$student, $exam] = $this->registeredFor(now()->subMinutes(10), itemCount: 2);
        $this->actingAs($student)->post(route('mock-exams.start', $exam));

        $first = $exam->items()->first();

        $this->actingAs($student)->post(route('mock-exams.submit', $exam), [
            'answers' => [$first->id => 'A'],
        ]);

        $attempt = MockExamAttempt::first();
        $this->assertSame(1, $attempt->score);
        $this->assertSame(2, $attempt->total_points, 'The unanswered item must still count toward the total.');
        $this->assertEquals(50.00, (float) $attempt->percent);
    }

    public function test_an_answer_that_was_never_offered_is_discarded(): void
    {
        [$student, $exam] = $this->registeredFor(now()->subMinutes(10), itemCount: 1);
        $this->actingAs($student)->post(route('mock-exams.start', $exam));
        $item = $exam->items()->first();

        // 'Z' is not a choice on this item.
        $this->actingAs($student)->post(route('mock-exams.submit', $exam), [
            'answers' => [$item->id => 'Z'],
        ]);

        $attempt = MockExamAttempt::first();
        $this->assertSame(0, $attempt->score);
        $this->assertSame([], $attempt->answers);
    }

    public function test_lateness_is_derived_server_side_from_the_exam_window(): void
    {
        [$student, $exam] = $this->registeredFor(now()->subMinutes(10), itemCount: 1);
        $this->actingAs($student)->post(route('mock-exams.start', $exam));

        // Walk past the end of the window, as a frozen client timer would allow.
        $this->travelTo($exam->endsAt()->addMinutes(5));

        $this->actingAs($student)->post(route('mock-exams.submit', $exam), ['answers' => []]);

        $this->assertTrue(MockExamAttempt::first()->is_late);
    }

    public function test_an_on_time_submission_is_not_flagged_late(): void
    {
        [$student, $exam] = $this->registeredFor(now()->subMinutes(10), itemCount: 1);
        $this->actingAs($student)->post(route('mock-exams.start', $exam));

        $this->actingAs($student)->post(route('mock-exams.submit', $exam), ['answers' => []]);

        $this->assertFalse(MockExamAttempt::first()->is_late);
    }

    public function test_submitting_feeds_weak_area_detection_but_awards_no_points(): void
    {
        [$student, $exam] = $this->registeredFor(now()->subMinutes(10), itemCount: 4);
        $this->actingAs($student)->post(route('mock-exams.start', $exam));

        $answers = [];
        foreach ($exam->items()->get() as $i => $item) {
            $answers[$item->id] = $i === 0 ? 'A' : 'B';  // 1 of 4 correct
        }
        $this->actingAs($student)->post(route('mock-exams.submit', $exam), ['answers' => $answers]);

        $record = DB::table('performance_records')
            ->where('student_id', $student->id)->where('topic_id', $this->topicId)->first();

        $this->assertNotNull($record, 'Mock results must feed performance records.');
        $this->assertSame(4, $record->total_attempts);
        $this->assertSame(1, $record->correct_count);

        // ...but stay off the leaderboard and out of normal quiz history.
        $this->assertSame(0, DB::table('points_log')->count(), 'A graded exam must not award points.');
        $this->assertFalse(
            \Illuminate\Support\Facades\Schema::hasTable('quiz_sessions')
                && DB::table('quiz_sessions')->exists(),
            'A mock exam must not create a quiz_sessions row.'
        );
    }

    public function test_a_submitted_exam_cannot_be_submitted_again(): void
    {
        [$student, $exam] = $this->registeredFor(now()->subMinutes(10), itemCount: 2);
        $this->actingAs($student)->post(route('mock-exams.start', $exam));
        $items = $exam->items()->get();

        $this->actingAs($student)->post(route('mock-exams.submit', $exam), [
            'answers' => [$items[0]->id => 'A'],
        ]);
        $firstScore = MockExamAttempt::first()->score;

        // A second attempt with a perfect paper must be ignored.
        $this->actingAs($student)->post(route('mock-exams.submit', $exam), [
            'answers' => [$items[0]->id => 'A', $items[1]->id => 'A'],
        ])->assertRedirect(route('mock-exams.result', $exam));

        $this->assertSame($firstScore, MockExamAttempt::first()->score);
    }

    public function test_a_student_cannot_submit_another_students_exam(): void
    {
        [$student, $exam] = $this->registeredFor(now()->subMinutes(10), itemCount: 1);
        $this->actingAs($student)->post(route('mock-exams.start', $exam));

        $intruder = $this->makeStudent('intruder@example.com');
        MockExamRegistration::create([
            'event_id' => $exam->event_id, 'student_id' => $intruder->id, 'redeemed_at' => now(),
        ]);

        // The intruder is registered, so they get their own attempt - they can
        // never reach into someone else's.
        $this->actingAs($intruder)->post(route('mock-exams.submit', $exam), ['answers' => []])
            ->assertNotFound();

        $this->assertSame(1, MockExamAttempt::count());
    }

    public function test_results_are_not_viewable_before_submitting(): void
    {
        [$student, $exam] = $this->registeredFor(now()->subMinutes(10), itemCount: 1);
        $this->actingAs($student)->post(route('mock-exams.start', $exam));

        $this->actingAs($student)->get(route('mock-exams.result', $exam))->assertForbidden();
    }

    // ── helpers ──────────────────────────────────────────────────────────

    /**
     * A registered student and a published exam sitting at $sitting.
     *
     * @return array{0: \App\Models\User, 1: MockExam}
     */
    private function registeredFor($sitting, int $itemCount = 1): array
    {
        $faculty = $this->makeFaculty('f@example.com', $this->subjectId);
        $student = $this->makeStudent();

        $event = MockExamEvent::create([
            'exam_date' => $sitting->toDateString(),
            'access_code' => MockExamEvent::newAccessCode($sitting),
        ]);

        $exam = MockExam::create([
            'event_id' => $event->id,
            'subject_id' => $this->subjectId,
            'created_by' => $faculty->id,
            'title' => 'FAR Mock Exam',
            'status' => MockExam::STATUS_PUBLISHED,
            'scheduled_at' => $sitting,
            'duration_minutes' => 180,
            'total_items' => $itemCount,
            'published_at' => now(),
        ]);

        for ($i = 0; $i < $itemCount; $i++) {
            DB::table('mock_exam_items')->insert([
                'exam_id' => $exam->id,
                'source_question_id' => $this->makeQuestion($this->topicId),
                'topic_id' => $this->topicId,
                'question_text' => "Question {$i}",
                'difficulty' => 'moderate',
                'choices' => json_encode([
                    ['label' => 'A', 'text' => 'Right', 'is_correct' => true],
                    ['label' => 'B', 'text' => 'Wrong', 'is_correct' => false],
                ]),
                'points' => 1, 'sort_order' => $i,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        MockExamRegistration::create([
            'event_id' => $event->id, 'student_id' => $student->id, 'redeemed_at' => now(),
        ]);

        return [$student, $exam];
    }
}
