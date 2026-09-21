<?php

namespace Tests\Feature;

use App\Models\MockExam;
use App\Models\MockExamEvent;
use App\Models\MockExamRegistration;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsMockExamSchema;
use Tests\TestCase;

/**
 * Students redeem one code per exam DAY. Registration is against the event
 * rather than an individual exam, which is what lets a subject published later
 * the same day appear without any backfill.
 *
 * Replaces the old MockExamTest, which covered the single hardcoded
 * config('mockexam.access_code') gate that this feature removed.
 */
class MockExamRedemptionTest extends TestCase
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

    public function test_a_valid_code_registers_the_student_for_that_exam_day(): void
    {
        $student = $this->makeStudent();
        $event = $this->publishedEvent($this->farId);

        $this->actingAs($student)
            ->post(route('mock-exams.redeem'), ['access_code' => $event->access_code])
            ->assertRedirect(route('mock-exams'))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, MockExamRegistration::where('student_id', $student->id)->count());
    }

    public function test_an_unrecognised_code_is_rejected(): void
    {
        $student = $this->makeStudent();
        $this->publishedEvent($this->farId);

        $this->actingAs($student)
            ->post(route('mock-exams.redeem'), ['access_code' => 'MOCK-20260101-ZZZZ'])
            ->assertSessionHasErrors('access_code');

        $this->assertSame(0, MockExamRegistration::count());
    }

    public function test_the_code_is_matched_case_insensitively_and_ignores_stray_whitespace(): void
    {
        $student = $this->makeStudent();
        $event = $this->publishedEvent($this->farId);

        $this->actingAs($student)->post(route('mock-exams.redeem'), [
            'access_code' => '  ' . strtolower($event->access_code) . ' ',
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, MockExamRegistration::count());
    }

    public function test_redeeming_the_same_code_twice_is_harmless(): void
    {
        $student = $this->makeStudent();
        $event = $this->publishedEvent($this->farId);

        foreach ([1, 2] as $ignored) {
            $this->actingAs($student)
                ->post(route('mock-exams.redeem'), ['access_code' => $event->access_code])
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(1, MockExamRegistration::count());
    }

    public function test_an_exam_published_later_the_same_day_is_visible_without_re_redeeming(): void
    {
        $student = $this->makeStudent();
        $event = $this->publishedEvent($this->farId);

        $this->actingAs($student)->post(route('mock-exams.redeem'), ['access_code' => $event->access_code]);

        // The Chair publishes AUD for the same day AFTER the student redeemed.
        $aud = $this->publishedExam($this->audId, $event, now()->addDay()->setTime(13, 0));

        $this->actingAs($student)->get(route('mock-exams.show', $aud))->assertOk();
        $this->actingAs($student)->get(route('mock-exams'))->assertOk()->assertSee('AUD');
    }

    public function test_a_student_cannot_open_an_exam_whose_code_they_never_redeemed(): void
    {
        $student = $this->makeStudent();
        $event = $this->publishedEvent($this->farId);
        $exam = MockExam::where('event_id', $event->id)->first();

        $this->actingAs($student)->get(route('mock-exams.show', $exam))->assertForbidden();
        $this->actingAs($student)->post(route('mock-exams.start', $exam))->assertForbidden();
    }

    public function test_a_code_for_a_day_with_nothing_published_yet_is_refused(): void
    {
        $student = $this->makeStudent();
        // An event exists but its exam is still only for_review.
        $event = MockExamEvent::create([
            'exam_date' => now()->addDay()->toDateString(),
            'access_code' => 'MOCK-20990101-AAAA',
        ]);
        MockExam::create([
            'event_id' => $event->id, 'subject_id' => $this->farId, 'created_by' => $this->makeFaculty('f@example.com', $this->farId)->id,
            'title' => 'FAR', 'status' => MockExam::STATUS_FOR_REVIEW,
            'scheduled_at' => now()->addDay(), 'duration_minutes' => 180,
        ]);

        $this->actingAs($student)
            ->post(route('mock-exams.redeem'), ['access_code' => $event->access_code])
            ->assertSessionHasErrors('access_code');
    }

    public function test_an_alumnus_cannot_redeem_or_enter_a_mock_exam(): void
    {
        $alumnus = $this->makeStudent('alum@example.com', isAlumni: true);
        $event = $this->publishedEvent($this->farId);
        $exam = MockExam::where('event_id', $event->id)->first();

        $this->actingAs($alumnus)
            ->post(route('mock-exams.redeem'), ['access_code' => $event->access_code])
            ->assertForbidden();

        $this->actingAs($alumnus)->get(route('mock-exams.show', $exam))->assertForbidden();
    }

    // ── helpers ──────────────────────────────────────────────────────────

    /** A published FAR exam tomorrow, plus the day's event. */
    private function publishedEvent(int $subjectId): MockExamEvent
    {
        $date = now()->addDay()->setTime(8, 0);
        $event = MockExamEvent::create([
            'exam_date' => $date->toDateString(),
            'access_code' => MockExamEvent::newAccessCode($date),
        ]);
        $this->publishedExam($subjectId, $event, $date);

        return $event;
    }

    private function publishedExam(int $subjectId, MockExamEvent $event, $sitting): MockExam
    {
        $faculty = $this->makeFaculty('f' . uniqid() . '@example.com', $subjectId);
        $topicId = $this->makeTopic($subjectId, 'Topic ' . uniqid());

        $exam = MockExam::create([
            'event_id' => $event->id,
            'subject_id' => $subjectId,
            'created_by' => $faculty->id,
            'title' => 'Mock Exam',
            'status' => MockExam::STATUS_PUBLISHED,
            'scheduled_at' => $sitting,
            'duration_minutes' => 180,
            'total_items' => 1,
            'published_at' => now(),
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
