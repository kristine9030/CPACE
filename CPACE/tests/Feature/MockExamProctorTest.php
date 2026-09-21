<?php

namespace Tests\Feature;

use App\Models\MockExam;
use App\Models\MockExamAttempt;
use App\Models\MockExamEvent;
use App\Models\MockExamProctorCapture;
use App\Models\MockExamProctorEvent;
use App\Models\MockExamRegistration;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsMockExamSchema;
use Tests\TestCase;

/**
 * Proctoring ingest and playback.
 *
 * The privacy assertions here are the important ones: captures are
 * photographs of students' faces and screens, so they must live on the
 * private disk and be unreachable by anyone but the owning faculty and the
 * Program Chair.
 */
class MockExamProctorTest extends TestCase
{
    use BuildsMockExamSchema;

    private int $farId;
    private int $audId;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->buildMockExamSchema();
        $this->farId = $this->makeSubject('FAR', 'Financial Accounting');
        $this->audId = $this->makeSubject('AUD', 'Auditing');
    }

    protected function tearDown(): void
    {
        $this->dropMockExamSchema();
        parent::tearDown();
    }

    public function test_a_student_can_record_a_flag_against_their_own_attempt(): void
    {
        [$student, $attempt] = $this->sitting();

        $this->actingAs($student)->postJson(route('mock-exams.proctor.event', $attempt), [
            'type' => MockExamProctorEvent::TYPE_BLUR,
        ])->assertOk()->assertJson(['recorded' => true, 'flags' => 1]);

        $this->assertSame(1, MockExamProctorEvent::count());
        $this->assertSame(1, $attempt->fresh()->flag_count);
    }

    public function test_an_unknown_flag_type_is_rejected(): void
    {
        [$student, $attempt] = $this->sitting();

        $this->actingAs($student)->postJson(route('mock-exams.proctor.event', $attempt), [
            'type' => 'made_up_type',
        ])->assertStatus(422);

        $this->assertSame(0, MockExamProctorEvent::count());
    }

    public function test_a_student_cannot_post_flags_onto_another_students_attempt(): void
    {
        [, $attempt] = $this->sitting();
        $intruder = $this->makeStudent('intruder@example.com');

        $this->actingAs($intruder)->postJson(route('mock-exams.proctor.event', $attempt), [
            'type' => MockExamProctorEvent::TYPE_BLUR,
        ])->assertForbidden();

        $this->assertSame(0, MockExamProctorEvent::count());
    }

    public function test_flags_cannot_be_back_filled_onto_a_finished_attempt(): void
    {
        [$student, $attempt] = $this->sitting();
        $attempt->update(['status' => MockExamAttempt::STATUS_SUBMITTED, 'submitted_at' => now()]);

        $this->actingAs($student)->postJson(route('mock-exams.proctor.event', $attempt), [
            'type' => MockExamProctorEvent::TYPE_BLUR,
        ])->assertStatus(409);
    }

    public function test_a_capture_is_stored_on_the_private_disk_not_the_public_one(): void
    {
        [$student, $attempt] = $this->sitting();

        $this->actingAs($student)->post(route('mock-exams.proctor.capture', $attempt), [
            'kind' => MockExamProctorCapture::KIND_CAMERA,
            'reason' => 'interval',
            'frame' => UploadedFile::fake()->image('frame.jpg', 320, 240),
        ])->assertOk();

        $capture = MockExamProctorCapture::first();
        $this->assertNotNull($capture);
        $this->assertStringStartsWith(MockExamProctorCapture::ROOT . '/', $capture->path);
        Storage::disk('local')->assertExists($capture->path);
        // Never the public disk - that would make it a guessable URL.
        $this->assertStringNotContainsString('public', $capture->path);
    }

    public function test_the_owning_faculty_and_the_chair_can_view_a_capture(): void
    {
        [, $attempt] = $this->sitting();
        $capture = $this->storeCapture($attempt);

        $faculty = User::find($attempt->exam->created_by);
        $this->actingAs($faculty)->get(route('mock-exams.capture', $capture))->assertOk();

        $this->actingAs($this->makeChair())->get(route('mock-exams.capture', $capture))->assertOk();
    }

    public function test_a_capture_is_not_reachable_by_another_student_or_an_unassigned_faculty(): void
    {
        [, $attempt] = $this->sitting();
        $capture = $this->storeCapture($attempt);

        $otherStudent = $this->makeStudent('other@example.com');
        $this->actingAs($otherStudent)->get(route('mock-exams.capture', $capture))->assertForbidden();

        // Faculty assigned to a different subject.
        $outsider = $this->makeFaculty('aud@example.com', $this->audId);
        $this->actingAs($outsider)->get(route('mock-exams.capture', $capture))->assertForbidden();
    }

    public function test_a_capture_is_not_reachable_while_logged_out(): void
    {
        [, $attempt] = $this->sitting();
        $capture = $this->storeCapture($attempt);

        $this->get(route('mock-exams.capture', $capture))->assertRedirect(route('login'));
    }

    public function test_the_monitor_feed_reports_flags_and_is_closed_to_outsiders(): void
    {
        [$student, $attempt] = $this->sitting();
        $attempt->update(['flag_count' => 3]);

        $faculty = User::find($attempt->exam->created_by);
        $response = $this->actingAs($faculty)
            ->getJson(route('faculty.mock-exams.monitor.feed', $attempt->exam))
            ->assertOk();

        $this->assertSame(1, $response->json('kpis.started'));
        $this->assertSame(1, $response->json('kpis.flagged'));
        $this->assertSame(3, $response->json('students.0.flags'));

        $this->actingAs($student)
            ->getJson(route('faculty.mock-exams.monitor.feed', $attempt->exam))
            ->assertForbidden();
    }

    public function test_the_purge_command_deletes_old_frames_but_keeps_the_flag_history(): void
    {
        [, $attempt] = $this->sitting(sitting: now()->subDays(60));
        $capture = $this->storeCapture($attempt);
        MockExamProctorEvent::create([
            'attempt_id' => $attempt->id, 'type' => MockExamProctorEvent::TYPE_BLUR, 'occurred_at' => now(),
        ]);

        $this->artisan('mock-exam:purge-captures', ['--days' => 30])->assertSuccessful();

        $this->assertSame(0, MockExamProctorCapture::count(), 'Old frames should be swept.');
        Storage::disk('local')->assertMissing($capture->path);
        $this->assertSame(1, MockExamProctorEvent::count(), 'Flag history is the evidence and must be kept.');
    }

    public function test_the_purge_command_leaves_recent_exams_alone(): void
    {
        [, $attempt] = $this->sitting(sitting: now()->subDays(2));
        $this->storeCapture($attempt);

        $this->artisan('mock-exam:purge-captures', ['--days' => 30])->assertSuccessful();

        $this->assertSame(1, MockExamProctorCapture::count());
    }

    // ── helpers ──────────────────────────────────────────────────────────

    /** @return array{0: User, 1: MockExamAttempt} */
    private function sitting($sitting = null): array
    {
        $sitting ??= now()->subMinutes(10);
        $faculty = $this->makeFaculty('far@example.com', $this->farId);
        $student = $this->makeStudent();
        $topicId = $this->makeTopic($this->farId);

        $event = MockExamEvent::create([
            'exam_date' => $sitting->toDateString(),
            'access_code' => MockExamEvent::newAccessCode($sitting),
        ]);

        $exam = MockExam::create([
            'event_id' => $event->id,
            'subject_id' => $this->farId,
            'created_by' => $faculty->id,
            'title' => 'FAR Mock Exam',
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

        MockExamRegistration::create([
            'event_id' => $event->id, 'student_id' => $student->id, 'redeemed_at' => now(),
        ]);

        $attempt = MockExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'status' => MockExamAttempt::STATUS_IN_PROGRESS,
            'answers' => [],
        ]);

        return [$student, $attempt];
    }

    private function storeCapture(MockExamAttempt $attempt): MockExamProctorCapture
    {
        $path = MockExamProctorCapture::ROOT . '/' . $attempt->exam_id . '/' . $attempt->id . '/camera-test.jpg';
        Storage::disk('local')->put($path, 'fake-jpeg-bytes');

        return MockExamProctorCapture::create([
            'attempt_id' => $attempt->id,
            'kind' => MockExamProctorCapture::KIND_CAMERA,
            'path' => $path,
            'captured_at' => now(),
            'reason' => 'interval',
        ]);
    }
}
