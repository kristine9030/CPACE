<?php

namespace Tests\Feature;

use App\Models\MockExam;
use App\Models\MockExamAttempt;
use App\Models\MockExamEvent;
use App\Models\MockExamProctorEvent;
use App\Models\MockExamRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsMockExamSchema;
use Tests\TestCase;

/**
 * Renders every Mock Exam screen once.
 *
 * The feature ships thirteen Blade views, and a template error (a mismatched
 * directive, a missing variable) only surfaces when the view is actually
 * rendered - the controller tests alone would not catch it. This walks the
 * whole workflow front to back so a broken template fails the build rather
 * than the defense.
 */
class MockExamViewSmokeTest extends TestCase
{
    use BuildsMockExamSchema;

    private int $subjectId;
    private int $topicId;
    private User $faculty;
    private User $chair;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildMockExamSchema();
        $this->subjectId = $this->makeSubject('FAR', 'Financial Accounting');
        $this->topicId = $this->makeTopic($this->subjectId);
        $this->faculty = $this->makeFaculty('far@example.com', $this->subjectId);
        $this->chair = $this->makeChair();
        $this->student = $this->makeStudent();
    }

    protected function tearDown(): void
    {
        $this->dropMockExamSchema();
        parent::tearDown();
    }

    public function test_every_faculty_screen_renders(): void
    {
        $exam = $this->exam(MockExam::STATUS_DRAFT, now()->addWeek());

        $this->actingAs($this->faculty)->get(route('faculty.mock-exams'))->assertOk()->assertSee('FAR');
        $this->actingAs($this->faculty)->get(route('faculty.mock-exams.subject', $this->subjectId))->assertOk();
        $this->actingAs($this->faculty)->get(route('faculty.mock-exams.build', $exam))->assertOk()->assertSee('Topics');
    }

    public function test_the_builder_renders_in_read_only_mode_once_published(): void
    {
        $exam = $this->exam(MockExam::STATUS_PUBLISHED, now()->addWeek(), withEvent: true);

        $this->actingAs($this->faculty)->get(route('faculty.mock-exams.build', $exam))
            ->assertOk()
            ->assertSee('locked', false);
    }

    public function test_every_chair_screen_renders(): void
    {
        $exam = $this->exam(MockExam::STATUS_FOR_REVIEW, now()->addWeek());

        $this->actingAs($this->chair)->get(route('chair.mock-exams'))->assertOk()->assertSee('FAR');
        $this->actingAs($this->chair)->get(route('chair.mock-exams.subject', $this->subjectId))->assertOk();
        $this->actingAs($this->chair)->get(route('chair.mock-exams.review', $exam))->assertOk()->assertSee('Audit trail');
    }

    public function test_the_monitor_and_attempt_screens_render_for_both_chair_and_faculty(): void
    {
        $exam = $this->exam(MockExam::STATUS_PUBLISHED, now()->subMinutes(10), withEvent: true);
        $attempt = $this->attempt($exam);
        MockExamProctorEvent::create([
            'attempt_id' => $attempt->id,
            'type' => MockExamProctorEvent::TYPE_BLUR,
            'occurred_at' => now(),
        ]);
        $attempt->update(['flag_count' => 1]);

        $this->actingAs($this->chair)->get(route('chair.mock-exams.monitor', $exam))->assertOk();
        $this->actingAs($this->faculty)->get(route('faculty.mock-exams.monitor', $exam))->assertOk();
        $this->actingAs($this->chair)->get(route('chair.mock-exams.attempt', $attempt))
            ->assertOk()
            ->assertSee('Flag timeline');
        $this->actingAs($this->faculty)->get(route('faculty.mock-exams.attempt', $attempt))
            ->assertOk()
            ->assertSee('Flag timeline');
    }

    public function test_every_student_screen_renders(): void
    {
        $exam = $this->exam(MockExam::STATUS_PUBLISHED, now()->subMinutes(10), withEvent: true);
        $this->register($exam);

        $this->actingAs($this->student)->get(route('mock-exams'))->assertOk()->assertSee('Redeem');
        $this->actingAs($this->student)->get(route('mock-exams.subject', $this->subjectId))->assertOk();
        // The consent gate must actually say what is recorded.
        $this->actingAs($this->student)->get(route('mock-exams.show', $exam))
            ->assertOk()
            ->assertSee('what gets recorded', false)
            ->assertSee('Screen sharing');
    }

    public function test_the_exam_runner_and_result_screens_render(): void
    {
        $exam = $this->exam(MockExam::STATUS_PUBLISHED, now()->subMinutes(10), withEvent: true);
        $this->register($exam);

        $this->actingAs($this->student)->post(route('mock-exams.start', $exam));
        $this->actingAs($this->student)->get(route('mock-exams.take', $exam))
            ->assertOk()
            ->assertSee('Submit exam');

        $item = $exam->items()->first();
        $this->actingAs($this->student)->post(route('mock-exams.submit', $exam), [
            'answers' => [$item->id => 'A'],
        ]);

        $this->actingAs($this->student)->get(route('mock-exams.result', $exam))
            ->assertOk()
            ->assertSee('How you did by topic');
    }

    public function test_an_upcoming_exam_shows_the_countdown_rather_than_the_consent_gate(): void
    {
        $exam = $this->exam(MockExam::STATUS_PUBLISHED, now()->addDays(3), withEvent: true);
        $this->register($exam);

        $this->actingAs($this->student)->get(route('mock-exams.show', $exam))
            ->assertOk()
            ->assertSee("hasn't opened yet", false)
            ->assertDontSee('Start exam');
    }

    // ── helpers ──────────────────────────────────────────────────────────

    private function exam(string $status, $sitting, bool $withEvent = false): MockExam
    {
        $eventId = null;
        if ($withEvent) {
            $eventId = MockExamEvent::create([
                'exam_date' => $sitting->toDateString(),
                'access_code' => MockExamEvent::newAccessCode($sitting),
            ])->id;
        }

        $exam = MockExam::create([
            'event_id' => $eventId,
            'subject_id' => $this->subjectId,
            'created_by' => $this->faculty->id,
            'title' => 'FAR Mock Exam',
            'status' => $status,
            'scheduled_at' => $sitting,
            'duration_minutes' => 180,
            'total_items' => 2,
            'published_at' => $status === MockExam::STATUS_PUBLISHED ? now() : null,
        ]);

        for ($i = 0; $i < 2; $i++) {
            DB::table('mock_exam_items')->insert([
                'exam_id' => $exam->id,
                'source_question_id' => $this->makeQuestion($this->topicId),
                'topic_id' => $this->topicId,
                'question_text' => "Question {$i}",
                'difficulty' => 'moderate',
                'explanation' => 'Because.',
                'choices' => json_encode([
                    ['label' => 'A', 'text' => 'Right', 'is_correct' => true],
                    ['label' => 'B', 'text' => 'Wrong', 'is_correct' => false],
                ]),
                'points' => 1, 'sort_order' => $i,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        DB::table('mock_exam_topics')->insert(['exam_id' => $exam->id, 'topic_id' => $this->topicId]);

        return $exam;
    }

    private function register(MockExam $exam): void
    {
        MockExamRegistration::create([
            'event_id' => $exam->event_id,
            'student_id' => $this->student->id,
            'redeemed_at' => now(),
        ]);
    }

    private function attempt(MockExam $exam): MockExamAttempt
    {
        $this->register($exam);

        return MockExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'started_at' => now(),
            'status' => MockExamAttempt::STATUS_IN_PROGRESS,
            'answers' => [],
        ]);
    }
}
