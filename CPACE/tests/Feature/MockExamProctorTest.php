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

    public function test_the_purge_command_defaults_to_the_fourteen_day_window(): void
    {
        [, $old] = $this->sitting(sitting: now()->subDays(20));
        $oldCapture = $this->storeCapture($old);

        $this->artisan('mock-exam:purge-captures')->assertSuccessful();

        $this->assertSame(0, MockExamProctorCapture::count());
        Storage::disk('local')->assertMissing($oldCapture->path);
    }

    public function test_the_purge_is_on_the_daily_schedule(): void
    {
        $scheduled = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->contains(fn ($e) => str_contains($e->command, 'mock-exam:purge-captures'));

        $this->assertTrue($scheduled, 'Without a schedule entry nothing would ever delete the frames.');
    }

    public function test_the_face_check_flag_types_are_accepted(): void
    {
        [$student, $attempt] = $this->sitting();

        foreach ([MockExamProctorEvent::TYPE_NO_FACE, MockExamProctorEvent::TYPE_MULTIPLE_FACES, MockExamProctorEvent::TYPE_LOOKING_AWAY] as $type) {
            $this->actingAs($student)->postJson(route('mock-exams.proctor.event', $attempt), ['type' => $type])
                ->assertOk();
        }

        $this->assertSame(3, $attempt->fresh()->flag_count);
    }

    public function test_a_clean_sitting_keeps_no_recordings_once_submitted(): void
    {
        [$student, $attempt] = $this->sitting();
        $a = $this->storeCapture($attempt, 'start');
        $b = $this->storeCapture($attempt, 'interval');

        $this->actingAs($student)->post(route('mock-exams.submit', $attempt->exam))->assertRedirect();

        $this->assertSame(0, MockExamProctorCapture::count());
        Storage::disk('local')->assertMissing($a->path);
        Storage::disk('local')->assertMissing($b->path);
    }

    public function test_a_flagged_sitting_keeps_only_the_frames_behind_a_flag(): void
    {
        [$student, $attempt] = $this->sitting();
        $attempt->update(['flag_count' => 1]);
        $start = $this->storeCapture($attempt, 'start');
        $routine = $this->storeCapture($attempt, 'interval');
        $evidence = $this->storeCapture($attempt, 'multiple_faces');

        $this->actingAs($student)->post(route('mock-exams.submit', $attempt->exam))->assertRedirect();

        $this->assertEqualsCanonicalizing(
            [$start->id, $evidence->id],
            MockExamProctorCapture::pluck('id')->all()
        );
        Storage::disk('local')->assertMissing($routine->path);
        Storage::disk('local')->assertExists($evidence->path);
    }

    public function test_the_owner_can_delete_recordings_after_submit_and_the_flags_stay(): void
    {
        [, $attempt] = $this->sitting();
        $attempt->update(['status' => MockExamAttempt::STATUS_SUBMITTED, 'submitted_at' => now(), 'flag_count' => 1]);
        $capture = $this->storeCapture($attempt, 'blur');
        MockExamProctorEvent::create(['attempt_id' => $attempt->id, 'type' => 'blur', 'occurred_at' => now()]);

        $faculty = User::find($attempt->exam->created_by);
        $this->actingAs($faculty)
            ->delete(route('mock-exams.captures.destroy', $attempt), ['capture_ids' => [$capture->id]])
            ->assertRedirect();

        $this->assertSame(0, MockExamProctorCapture::count());
        Storage::disk('local')->assertMissing($capture->path);
        $this->assertSame(1, MockExamProctorEvent::count());
    }

    public function test_only_the_ticked_recordings_are_deleted(): void
    {
        [, $attempt] = $this->sitting();
        $attempt->update(['status' => MockExamAttempt::STATUS_SUBMITTED, 'submitted_at' => now()]);
        $gone = $this->storeCapture($attempt, 'interval');
        $kept = $this->storeCapture($attempt, 'multiple_faces');

        $this->actingAs($this->makeChair())
            ->delete(route('mock-exams.captures.destroy', $attempt), ['capture_ids' => [$gone->id]])
            ->assertRedirect();

        Storage::disk('local')->assertMissing($gone->path);
        Storage::disk('local')->assertExists($kept->path);
        $this->assertSame([$kept->id], MockExamProctorCapture::pluck('id')->all());
    }

    public function test_the_student_page_offers_a_checkbox_per_recording_only_once_submitted(): void
    {
        [, $attempt] = $this->sitting();
        $capture = $this->storeCapture($attempt, 'multiple_faces');
        $chair = $this->makeChair();

        $this->actingAs($chair)->get(route('chair.mock-exams.attempt', $attempt))
            ->assertOk()
            ->assertDontSee('name="capture_ids[]"', false)
            ->assertSee('once the student has submitted');

        $attempt->update(['status' => MockExamAttempt::STATUS_SUBMITTED, 'submitted_at' => now()]);

        $this->actingAs($chair)->get(route('chair.mock-exams.attempt', $attempt))
            ->assertOk()
            ->assertSee('name="capture_ids[]" value="' . $capture->id . '"', false)
            ->assertSee('Delete selected')
            ->assertSee('Select all');
    }

    public function test_ticking_nothing_deletes_nothing(): void
    {
        [, $attempt] = $this->sitting();
        $attempt->update(['status' => MockExamAttempt::STATUS_SUBMITTED, 'submitted_at' => now()]);
        $this->storeCapture($attempt);

        $this->actingAs($this->makeChair())
            ->from('/back')
            ->delete(route('mock-exams.captures.destroy', $attempt), ['capture_ids' => []])
            ->assertSessionHasErrors('capture_ids');

        $this->assertSame(1, MockExamProctorCapture::count());
    }

    public function test_recording_ids_from_another_sitting_cannot_be_deleted_through_this_one(): void
    {
        [, $mine] = $this->sitting();
        $mine->update(['status' => MockExamAttempt::STATUS_SUBMITTED, 'submitted_at' => now()]);
        $mineCapture = $this->storeCapture($mine);

        // A second student's sitting on the same exam.
        $theirs = MockExamAttempt::create([
            'exam_id' => $mine->exam_id,
            'student_id' => $this->makeStudent('other-sitter@example.com')->id,
            'started_at' => now(),
            'status' => MockExamAttempt::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'answers' => [],
        ]);
        $theirCapture = $this->storeCapture($theirs);

        $this->actingAs($this->makeChair())
            ->delete(route('mock-exams.captures.destroy', $mine), ['capture_ids' => [$mineCapture->id, $theirCapture->id]])
            ->assertRedirect();

        Storage::disk('local')->assertMissing($mineCapture->path);
        Storage::disk('local')->assertExists($theirCapture->path);
        $this->assertSame(1, MockExamProctorCapture::count());
    }

    public function test_recordings_cannot_be_deleted_while_the_student_is_still_sitting(): void
    {
        [, $attempt] = $this->sitting();
        $this->storeCapture($attempt);

        $this->actingAs($this->makeChair())->delete(route('mock-exams.captures.destroy', $attempt))->assertStatus(409);

        $this->assertSame(1, MockExamProctorCapture::count());
    }

    public function test_only_the_owning_faculty_or_chair_can_delete_recordings(): void
    {
        [$student, $attempt] = $this->sitting();
        $attempt->update(['status' => MockExamAttempt::STATUS_SUBMITTED, 'submitted_at' => now()]);
        $this->storeCapture($attempt);

        $this->actingAs($student)->delete(route('mock-exams.captures.destroy', $attempt))->assertForbidden();
        $outsider = $this->makeFaculty('aud2@example.com', $this->audId);
        $this->actingAs($outsider)->delete(route('mock-exams.captures.destroy', $attempt))->assertForbidden();

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

    private function storeCapture(MockExamAttempt $attempt, string $reason = 'interval'): MockExamProctorCapture
    {
        $path = MockExamProctorCapture::ROOT . '/' . $attempt->exam_id . '/' . $attempt->id . '/camera-' . $reason . '-' . uniqid() . '.jpg';
        Storage::disk('local')->put($path, 'fake-jpeg-bytes');

        return MockExamProctorCapture::create([
            'attempt_id' => $attempt->id,
            'kind' => MockExamProctorCapture::KIND_CAMERA,
            'path' => $path,
            'captured_at' => now(),
            'reason' => $reason,
        ]);
    }
}
