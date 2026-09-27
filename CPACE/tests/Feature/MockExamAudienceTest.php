<?php

namespace Tests\Feature;

use App\Models\MockExam;
use App\Models\MockExamAudit;
use App\Models\MockExamEvent;
use App\Models\MockExamRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsMockExamSchema;
use Tests\TestCase;

/**
 * The redeem code is shared by whoever hears it, so it cannot be the only gate:
 * the Chair names the year levels (and sections) allowed to sit an exam, and a
 * student outside them - say a 3rd year who got the code - is refused.
 */
class MockExamAudienceTest extends TestCase
{
    use BuildsMockExamSchema;

    private int $farId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildMockExamSchema();
        $this->farId = $this->makeSubject('FAR', 'Financial Accounting');
        DB::table('sections')->insert([
            ['name' => 'BSA 4-A', 'year_level' => 4, 'is_active' => true],
            ['name' => 'BSA 4-B', 'year_level' => 4, 'is_active' => true],
            ['name' => 'BSA 3-A', 'year_level' => 3, 'is_active' => true],
        ]);
    }

    protected function tearDown(): void
    {
        $this->dropMockExamSchema();
        parent::tearDown();
    }

    public function test_a_student_outside_the_chosen_year_cannot_redeem_even_with_the_code(): void
    {
        [$event] = $this->publishedFor(['audience_years' => [4]]);
        $thirdYear = $this->studentIn(3, 'BSA 3-A', 'third@example.com');

        $this->actingAs($thirdYear)
            ->post(route('mock-exams.redeem'), ['access_code' => $event->access_code])
            ->assertSessionHasErrors('access_code');

        $this->assertSame(0, MockExamRegistration::count());
    }

    public function test_a_student_in_the_chosen_year_can_redeem_and_open_the_exam(): void
    {
        [$event, $exam] = $this->publishedFor(['audience_years' => [4]]);
        $fourth = $this->studentIn(4, 'BSA 4-A', 'fourth@example.com');

        $this->actingAs($fourth)
            ->post(route('mock-exams.redeem'), ['access_code' => $event->access_code])
            ->assertSessionHasNoErrors();

        $this->actingAs($fourth)->get(route('mock-exams.show', $exam))->assertOk();
    }

    public function test_sections_narrow_within_the_chosen_years(): void
    {
        [$event] = $this->publishedFor(['audience_years' => [4], 'audience_sections' => ['BSA 4-A']]);

        $this->actingAs($this->studentIn(4, 'BSA 4-B', 'b@example.com'))
            ->post(route('mock-exams.redeem'), ['access_code' => $event->access_code])
            ->assertSessionHasErrors('access_code');

        $this->actingAs($this->studentIn(4, 'bsa 4-a', 'a@example.com'))
            ->post(route('mock-exams.redeem'), ['access_code' => $event->access_code])
            ->assertSessionHasNoErrors();
    }

    public function test_a_student_who_redeemed_before_the_audience_narrowed_loses_access(): void
    {
        [$event, $exam] = $this->publishedFor(['audience_years' => [3, 4]]);
        $third = $this->studentIn(3, 'BSA 3-A', 'third@example.com');
        $this->actingAs($third)->post(route('mock-exams.redeem'), ['access_code' => $event->access_code]);
        $this->actingAs($third)->get(route('mock-exams.show', $exam))->assertOk();

        $this->actingAs($this->makeChair())
            ->put(route('chair.mock-exams.audience', $exam), ['audience_years' => [4]])
            ->assertSessionHasNoErrors();

        $this->actingAs($third)->get(route('mock-exams.show', $exam))->assertForbidden();
        $this->actingAs($third)->post(route('mock-exams.start', $exam))->assertForbidden();
    }

    public function test_an_exam_with_no_audience_recorded_stays_open_to_everyone(): void
    {
        [$event] = $this->publishedFor([]);

        $this->actingAs($this->studentIn(3, null, 'any@example.com'))
            ->post(route('mock-exams.redeem'), ['access_code' => $event->access_code])
            ->assertSessionHasNoErrors();
    }

    public function test_the_chair_saves_the_audience_and_it_is_audited(): void
    {
        [, $exam] = $this->publishedFor([]);

        $this->actingAs($this->makeChair())
            ->put(route('chair.mock-exams.audience', $exam), [
                'audience_years' => [4, 5],
                // 3-A is not in a chosen year and Ghost is not a real section: both dropped.
                'audience_sections' => ['BSA 4-A', 'BSA 3-A', 'Ghost'],
            ])->assertRedirect();

        $exam->refresh();
        $this->assertSame([4, 5], $exam->audience_years);
        $this->assertSame(['BSA 4-A'], $exam->audience_sections);
        $this->assertSame(1, MockExamAudit::where('exam_id', $exam->id)->where('action', MockExamAudit::ACTION_AUDIENCE)->count());
    }

    public function test_only_the_chair_can_change_the_audience_and_not_once_closed(): void
    {
        [, $exam] = $this->publishedFor(['audience_years' => [4]]);
        $faculty = $this->makeFaculty('f@example.com', $this->farId);

        $this->actingAs($faculty)->put(route('chair.mock-exams.audience', $exam), ['audience_years' => [3]])->assertForbidden();

        $exam->update(['status' => MockExam::STATUS_CLOSED]);
        $this->actingAs($this->makeChair())->put(route('chair.mock-exams.audience', $exam), ['audience_years' => [3]])->assertForbidden();
        $this->assertSame([4], $exam->fresh()->audience_years);
    }

    public function test_publishing_needs_an_audience(): void
    {
        [, $exam] = $this->publishedFor([]);
        $exam->update(['status' => MockExam::STATUS_FOR_REVIEW, 'event_id' => null, 'audience_years' => null]);

        $this->actingAs($this->makeChair())
            ->post(route('chair.mock-exams.publish', $exam))
            ->assertSessionHasErrors('audience');

        $this->assertSame(MockExam::STATUS_FOR_REVIEW, $exam->fresh()->status);
    }

    public function test_the_review_page_shows_the_audience_card(): void
    {
        [, $exam] = $this->publishedFor(['audience_years' => [4]]);

        $this->actingAs($this->makeChair())->get(route('chair.mock-exams.review', $exam))
            ->assertOk()->assertSee('Who can take this exam')->assertSee('4th Year');
    }

    // ── helpers ──────────────────────────────────────────────────────────

    private function studentIn(int $year, ?string $section, string $email): User
    {
        $student = $this->makeStudent($email);
        DB::table('student_profiles')->where('user_id', $student->id)->update(['year_level' => $year, 'section' => $section]);

        return $student;
    }

    /** @return array{0: MockExamEvent, 1: MockExam} a published exam tomorrow with the given audience columns */
    private function publishedFor(array $audience): array
    {
        $date = now()->addDay()->setTime(8, 0);
        $event = MockExamEvent::create(['exam_date' => $date->toDateString(), 'access_code' => MockExamEvent::newAccessCode($date)]);
        $faculty = $this->makeFaculty('fac' . uniqid() . '@example.com', $this->farId);
        $topicId = $this->makeTopic($this->farId, 'Topic ' . uniqid());

        $exam = MockExam::create(array_merge([
            'event_id' => $event->id,
            'subject_id' => $this->farId,
            'created_by' => $faculty->id,
            'title' => 'Mock Exam',
            'status' => MockExam::STATUS_PUBLISHED,
            'scheduled_at' => $date,
            'duration_minutes' => 180,
            'total_items' => 1,
            'published_at' => now(),
        ], $audience));

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

        return [$event, $exam];
    }
}
