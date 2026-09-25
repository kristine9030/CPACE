<?php

namespace Tests\Feature;

use App\Models\MockExam;
use App\Models\MockExamAttempt;
use App\Models\MockExamEvent;
use App\Models\MockExamProctorEvent;
use App\Models\MockExamRegistration;
use App\Models\User;
use App\Support\MockExamSimilarity;
use App\Support\ProctorRisk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsMockExamSchema;
use Tests\TestCase;

/**
 * The integrity layer around a mock exam sitting: sittings a student abandons
 * are graded by the server, flags are weighted into a risk level, and pairs of
 * students with suspiciously identical wrong answers are surfaced.
 */
class MockExamIntegrityTest extends TestCase
{
    use BuildsMockExamSchema;

    private int $subjectId;
    private int $topicId;
    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->buildMockExamSchema();
        $this->subjectId = $this->makeSubject('FAR', 'Financial Accounting');
        $this->topicId = $this->makeTopic($this->subjectId);
    }

    protected function tearDown(): void
    {
        $this->dropMockExamSchema();
        parent::tearDown();
    }

    // ── sittings a student walked away from ──────────────────────────────

    public function test_an_abandoned_sitting_is_graded_from_its_last_autosave(): void
    {
        $exam = $this->exam(sitting: now()->subHours(4));
        $items = $this->itemIds($exam);
        // Answered 3 of 4 before the tab closed: two right, one wrong.
        $attempt = $this->sitter($exam, [$items[0] => 'A', $items[1] => 'A', $items[2] => 'B'], startedAt: now()->subHours(4));

        $this->artisan('mock-exam:close-expired')->assertSuccessful();

        $attempt->refresh();
        $this->assertSame(MockExamAttempt::STATUS_SUBMITTED, $attempt->status);
        $this->assertSame(2, $attempt->score);
        $this->assertSame(4, $attempt->total_points);
        $this->assertEquals(50.0, (float) $attempt->percent);
        $this->assertFalse($attempt->is_late, 'Closed at their own deadline, so not late.');
        $this->assertTrue($attempt->submitted_at->equalTo($attempt->started_at->copy()->addMinutes(180)));
    }

    public function test_an_auto_closed_sitting_is_marked_but_not_counted_as_a_flag(): void
    {
        $exam = $this->exam(sitting: now()->subHours(4));
        $attempt = $this->sitter($exam, [], startedAt: now()->subHours(4));

        $this->artisan('mock-exam:close-expired')->assertSuccessful();

        $this->assertTrue($attempt->fresh()->wasAutoClosed());
        $this->assertSame(0, $attempt->fresh()->flag_count);
        $this->assertSame(0, ProctorRisk::assess(ProctorRisk::countsFor([$attempt->id])[$attempt->id])['score']);
    }

    public function test_a_sitting_still_inside_its_time_is_left_alone(): void
    {
        $exam = $this->exam(sitting: now()->subMinutes(30));
        $attempt = $this->sitter($exam, [], startedAt: now()->subMinutes(30));

        $this->artisan('mock-exam:close-expired')->assertSuccessful();

        $this->assertSame(MockExamAttempt::STATUS_IN_PROGRESS, $attempt->fresh()->status);
    }

    public function test_a_sitting_just_past_its_deadline_gets_a_grace_period_to_submit_itself(): void
    {
        // 181 minutes in: one minute over, well inside the two-minute grace.
        $exam = $this->exam(sitting: now()->subMinutes(181));
        $attempt = $this->sitter($exam, [], startedAt: now()->subMinutes(181));

        $this->artisan('mock-exam:close-expired')->assertSuccessful();

        $this->assertSame(MockExamAttempt::STATUS_IN_PROGRESS, $attempt->fresh()->status);
    }

    public function test_a_submitted_sitting_is_never_regraded(): void
    {
        $exam = $this->exam(sitting: now()->subHours(4));
        $attempt = $this->sitter($exam, [], startedAt: now()->subHours(4));
        $attempt->update(['status' => MockExamAttempt::STATUS_SUBMITTED, 'submitted_at' => now()->subHours(3), 'score' => 3, 'percent' => 75]);

        $this->artisan('mock-exam:close-expired')->assertSuccessful();

        $this->assertSame(3, $attempt->fresh()->score);
        $this->assertFalse($attempt->fresh()->wasAutoClosed());
    }

    public function test_a_student_returning_after_their_time_gets_the_result_not_the_paper(): void
    {
        $exam = $this->exam(sitting: now()->subMinutes(200));
        $items = $this->itemIds($exam);
        $attempt = $this->sitter($exam, [$items[0] => 'A'], startedAt: now()->subMinutes(200));

        $this->actingAs(User::find($attempt->student_id))
            ->get(route('mock-exams.take', $exam))
            ->assertRedirect(route('mock-exams.result', $exam));

        $this->assertSame(MockExamAttempt::STATUS_SUBMITTED, $attempt->fresh()->status);
        $this->assertSame(1, $attempt->fresh()->score);
    }

    public function test_the_monitor_closes_expired_sittings_itself_and_labels_them(): void
    {
        $exam = $this->exam(sitting: now()->subHours(4));
        $attempt = $this->sitter($exam, [], startedAt: now()->subHours(4));

        $json = $this->actingAs($this->makeChair())
            ->getJson(route('chair.mock-exams.monitor.feed', $exam))
            ->assertOk()->json();

        $this->assertSame(1, $json['kpis']['submitted']);
        $this->assertTrue($json['students'][0]['auto_closed']);
        $this->assertSame(MockExamAttempt::STATUS_SUBMITTED, $attempt->fresh()->status);
    }

    public function test_the_close_command_is_scheduled(): void
    {
        $scheduled = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->contains(fn ($e) => str_contains($e->command, 'mock-exam:close-expired'));

        $this->assertTrue($scheduled);
    }

    // ── flag types the runner may post ───────────────────────────────────

    public function test_partial_screen_and_second_monitor_flags_are_accepted_but_a_client_cannot_post_auto_closed(): void
    {
        $exam = $this->exam(sitting: now()->subMinutes(10));
        $attempt = $this->sitter($exam, [], startedAt: now());
        $student = User::find($attempt->student_id);

        foreach ([MockExamProctorEvent::TYPE_PARTIAL_SCREEN, MockExamProctorEvent::TYPE_SECOND_MONITOR] as $type) {
            $this->actingAs($student)->postJson(route('mock-exams.proctor.event', $attempt), ['type' => $type])->assertOk();
        }
        $this->actingAs($student)
            ->postJson(route('mock-exams.proctor.event', $attempt), ['type' => MockExamProctorEvent::TYPE_AUTO_CLOSED])
            ->assertStatus(422);

        $this->assertSame(2, $attempt->fresh()->flag_count);
    }

    // ── risk score ───────────────────────────────────────────────────────

    public function test_serious_flags_outweigh_minor_ones(): void
    {
        $lostScreen = ProctorRisk::assess([MockExamProctorEvent::TYPE_SCREEN_LOST => 1]);
        $glance = ProctorRisk::assess([MockExamProctorEvent::TYPE_LOOKING_AWAY => 1]);

        $this->assertSame(5, $lostScreen['score']);
        $this->assertSame(ProctorRisk::LEVEL_MEDIUM, $lostScreen['level']);
        $this->assertSame(1, $glance['score']);
        $this->assertSame(ProctorRisk::LEVEL_LOW, $glance['level']);
    }

    public function test_a_flag_type_stops_adding_points_after_the_cap(): void
    {
        $risk = ProctorRisk::assess([MockExamProctorEvent::TYPE_LOOKING_AWAY => 60]);

        $this->assertSame(ProctorRisk::CAP_PER_TYPE, $risk['score']);
        $this->assertSame(60, $risk['rows'][0]['count']);
        $this->assertSame(ProctorRisk::CAP_PER_TYPE, $risk['rows'][0]['counted']);
    }

    public function test_levels_and_notes_that_are_not_flags(): void
    {
        $this->assertSame(ProctorRisk::LEVEL_NONE, ProctorRisk::level(0));
        $this->assertSame(ProctorRisk::LEVEL_LOW, ProctorRisk::level(ProctorRisk::MEDIUM_FROM - 1));
        $this->assertSame(ProctorRisk::LEVEL_MEDIUM, ProctorRisk::level(ProctorRisk::MEDIUM_FROM));
        $this->assertSame(ProctorRisk::LEVEL_HIGH, ProctorRisk::level(ProctorRisk::HIGH_FROM));

        // The auto-closed note has no weight: it is a record, not a flag.
        $this->assertSame(0, ProctorRisk::assess([MockExamProctorEvent::TYPE_AUTO_CLOSED => 1])['score']);
    }

    public function test_the_monitor_ranks_by_risk_not_by_raw_flag_count(): void
    {
        $exam = $this->exam(sitting: now()->subMinutes(10));

        // More flags (4), but all minor: 4 points, Low...
        $noisy = $this->sitter($exam, [], startedAt: now());
        $this->flags($noisy, [MockExamProctorEvent::TYPE_PASTE_BLOCKED => 2, MockExamProctorEvent::TYPE_FULLSCREEN_EXIT => 2]);
        // ...against fewer flags (3) that are serious: 13 points, High.
        $serious = $this->sitter($exam, [], startedAt: now());
        $this->flags($serious, [MockExamProctorEvent::TYPE_SCREEN_LOST => 1, MockExamProctorEvent::TYPE_MULTIPLE_FACES => 2]);

        $json = $this->actingAs($this->makeChair())
            ->getJson(route('chair.mock-exams.monitor.feed', $exam))->assertOk()->json();

        $this->assertSame($serious->id, $json['students'][0]['attempt_id']);
        $this->assertSame('high', $json['students'][0]['risk_level']);
        $this->assertSame('low', $json['students'][1]['risk_level']);
        $this->assertSame(1, $json['kpis']['high_risk']);
    }

    public function test_the_student_page_explains_the_risk_score(): void
    {
        $exam = $this->exam(sitting: now()->subMinutes(10));
        $attempt = $this->sitter($exam, [], startedAt: now());
        $this->flags($attempt, [MockExamProctorEvent::TYPE_SCREEN_LOST => 1]);

        $this->actingAs($this->makeChair())->get(route('chair.mock-exams.attempt', $attempt))
            ->assertOk()
            ->assertSee('Medium risk')
            ->assertSee('Screen sharing stopped')
            ->assertSee('5 pts');
    }

    // ── answer similarity ────────────────────────────────────────────────

    public function test_pairs_with_the_same_wrong_answers_are_surfaced_and_others_are_not(): void
    {
        $exam = $this->exam(sitting: now()->subHours(1), itemCount: 10);
        $ids = $this->itemIds($exam);

        $wrongB = fn (int $n) => collect($ids)->mapWithKeys(fn ($id, $i) => [$id => $i < $n ? 'B' : 'A'])->all();
        $wrongMixed = collect($ids)->mapWithKeys(fn ($id, $i) => [$id => $i < 6 ? ['B', 'C', 'D'][$i % 3] : 'A'])->all();
        $allRight = collect($ids)->mapWithKeys(fn ($id) => [$id => 'A'])->all();

        $twinOne = $this->sitter($exam, $wrongB(6), submitted: true);
        $twinTwo = $this->sitter($exam, $wrongB(6), submitted: true);
        $this->sitter($exam, $wrongMixed, submitted: true);   // wrong on the same items, different choices
        $this->sitter($exam, $allRight, submitted: true);
        $this->sitter($exam, $allRight, submitted: true);      // identical, but right answers mean nothing

        $pairs = app(MockExamSimilarity::class)->pairs($exam);

        $this->assertCount(1, $pairs);
        $this->assertEqualsCanonicalizing([$twinOne->id, $twinTwo->id], [$pairs[0]['a']->id, $pairs[0]['b']->id]);
        $this->assertSame(6, $pairs[0]['shared']);
    }

    public function test_a_couple_of_shared_mistakes_is_not_enough_to_mention(): void
    {
        $exam = $this->exam(sitting: now()->subHours(1), itemCount: 10);
        $ids = $this->itemIds($exam);
        $twoWrong = collect($ids)->mapWithKeys(fn ($id, $i) => [$id => $i < 2 ? 'B' : 'A'])->all();

        $this->sitter($exam, $twoWrong, submitted: true);
        $this->sitter($exam, $twoWrong, submitted: true);

        $this->assertSame([], app(MockExamSimilarity::class)->pairs($exam));
    }

    public function test_the_similarity_endpoint_is_for_the_chair_and_assigned_faculty_only(): void
    {
        $exam = $this->exam(sitting: now()->subHours(1), itemCount: 10);
        $ids = $this->itemIds($exam);
        $wrong = collect($ids)->mapWithKeys(fn ($id, $i) => [$id => $i < 6 ? 'B' : 'A'])->all();
        $one = $this->sitter($exam, $wrong, submitted: true);
        $this->sitter($exam, $wrong, submitted: true);

        $json = $this->actingAs($this->makeChair())
            ->getJson(route('chair.mock-exams.monitor.similarity', $exam))
            ->assertOk()->json();
        $this->assertSame(2, $json['submitted']);
        $this->assertCount(1, $json['pairs']);
        $this->assertSame(6, $json['pairs'][0]['shared']);

        $this->actingAs(User::find($exam->created_by))
            ->getJson(route('faculty.mock-exams.monitor.similarity', $exam))->assertOk();

        $this->actingAs(User::find($one->student_id))
            ->getJson(route('chair.mock-exams.monitor.similarity', $exam))->assertForbidden();

        $outsider = $this->makeFaculty('other-subject@example.com', $this->makeSubject('AUD', 'Auditing'));
        $this->actingAs($outsider)
            ->getJson(route('faculty.mock-exams.monitor.similarity', $exam))->assertForbidden();
    }

    // ── helpers ──────────────────────────────────────────────────────────

    private function exam($sitting, int $itemCount = 4, int $duration = 180): MockExam
    {
        $faculty = $this->makeFaculty('far-owner@example.com', $this->subjectId);
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
            'duration_minutes' => $duration,
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
                    ['label' => 'B', 'text' => 'Wrong 1', 'is_correct' => false],
                    ['label' => 'C', 'text' => 'Wrong 2', 'is_correct' => false],
                    ['label' => 'D', 'text' => 'Wrong 3', 'is_correct' => false],
                ]),
                'points' => 1, 'sort_order' => $i,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return $exam;
    }

    /** @return array<int, int> item ids in order */
    private function itemIds(MockExam $exam): array
    {
        return DB::table('mock_exam_items')->where('exam_id', $exam->id)->orderBy('sort_order')->pluck('id')->all();
    }

    /**
     * A registered student with a sitting in progress (or, with $submitted, a finished one).
     *
     * @param  array<int, string>  $answers  item id => label
     */
    private function sitter(MockExam $exam, array $answers, $startedAt = null, bool $submitted = false): MockExamAttempt
    {
        $student = $this->makeStudent('sitter' . (++$this->seq) . '@example.com');

        MockExamRegistration::create([
            'event_id' => $exam->event_id, 'student_id' => $student->id, 'redeemed_at' => now(),
        ]);

        return MockExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => $startedAt ?? now()->subMinutes(30),
            'submitted_at' => $submitted ? now() : null,
            'status' => $submitted ? MockExamAttempt::STATUS_SUBMITTED : MockExamAttempt::STATUS_IN_PROGRESS,
            'answers' => $answers,
        ]);
    }

    /** @param array<string, int> $counts flag type => how many */
    private function flags(MockExamAttempt $attempt, array $counts): void
    {
        foreach ($counts as $type => $n) {
            for ($i = 0; $i < $n; $i++) {
                MockExamProctorEvent::create(['attempt_id' => $attempt->id, 'type' => $type, 'occurred_at' => now()]);
            }
        }
        $attempt->update(['flag_count' => array_sum($counts)]);
    }
}
