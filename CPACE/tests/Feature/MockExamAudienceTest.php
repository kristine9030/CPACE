<?php

namespace Tests\Feature;

use App\Models\MockExam;
use App\Models\MockExamAudit;
use App\Models\MockExamEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsMockExamSchema;
use Tests\TestCase;

/**
 * There is no redeem code any more: a published exam is visible only to the
 * year levels (and sections) the Program Chair or an assigned faculty member
 * name, so a student outside them - say a 3rd year who heard about it from a
 * classmate - is refused, with no code involved at all.
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

    public function test_a_student_outside_the_chosen_year_cannot_open_the_exam(): void
    {
        $exam = $this->publishedFor(['audience_years' => [4]]);
        $thirdYear = $this->studentIn(3, 'BSA 3-A', 'third@example.com');

        $this->actingAs($thirdYear)->get(route('mock-exams.show', $exam))->assertForbidden();
        $this->actingAs($thirdYear)->post(route('mock-exams.start', $exam))->assertForbidden();
        $this->actingAs($thirdYear)->get(route('mock-exams'))->assertOk()->assertDontSee('FAR');
    }

    public function test_a_student_in_the_chosen_year_sees_and_can_open_the_exam_automatically(): void
    {
        $exam = $this->publishedFor(['audience_years' => [4]]);
        $fourth = $this->studentIn(4, 'BSA 4-A', 'fourth@example.com');

        $this->actingAs($fourth)->get(route('mock-exams'))->assertOk()->assertSee('FAR');
        $this->actingAs($fourth)->get(route('mock-exams.show', $exam))->assertOk();
    }

    public function test_sections_narrow_within_the_chosen_years(): void
    {
        $exam = $this->publishedFor(['audience_years' => [4], 'audience_sections' => ['BSA 4-A']]);

        $this->actingAs($this->studentIn(4, 'BSA 4-B', 'b@example.com'))
            ->get(route('mock-exams.show', $exam))->assertForbidden();

        $this->actingAs($this->studentIn(4, 'bsa 4-a', 'a@example.com'))
            ->get(route('mock-exams.show', $exam))->assertOk();
    }

    public function test_a_student_loses_access_once_the_audience_narrows(): void
    {
        $exam = $this->publishedFor(['audience_years' => [3, 4]]);
        $third = $this->studentIn(3, 'BSA 3-A', 'third@example.com');
        $this->actingAs($third)->get(route('mock-exams.show', $exam))->assertOk();

        $this->actingAs($this->makeChair())
            ->put(route('chair.mock-exams.audience', $exam), ['audience_years' => [4]])
            ->assertSessionHasNoErrors();

        $this->actingAs($third)->get(route('mock-exams.show', $exam))->assertForbidden();
        $this->actingAs($third)->post(route('mock-exams.start', $exam))->assertForbidden();
    }

    public function test_an_exam_with_no_audience_recorded_stays_open_to_everyone(): void
    {
        $exam = $this->publishedFor([]);

        $this->actingAs($this->studentIn(3, null, 'any@example.com'))
            ->get(route('mock-exams.show', $exam))->assertOk();
    }

    public function test_an_alumnus_cannot_open_a_mock_exam_even_if_the_audience_matches(): void
    {
        $exam = $this->publishedFor(['audience_years' => [4]]);
        $alumnus = $this->makeStudent('alum@example.com', isAlumni: true);
        DB::table('student_profiles')->where('user_id', $alumnus->id)->update(['year_level' => 4, 'section' => 'BSA 4-A']);

        $this->actingAs($alumnus)->get(route('mock-exams.show', $exam))->assertForbidden();
    }

    public function test_the_chair_saves_the_audience_and_it_is_audited(): void
    {
        $exam = $this->publishedFor([]);

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

    public function test_a_faculty_member_assigned_to_the_subject_can_also_set_the_audience(): void
    {
        $exam = $this->publishedFor([]);
        $faculty = $this->makeFaculty('f@example.com', $this->farId);

        $this->actingAs($faculty)
            ->put(route('faculty.mock-exams.audience', $exam), ['audience_years' => [3]])
            ->assertRedirect(route('faculty.mock-exams.build', $exam));

        $this->assertSame([3], $exam->fresh()->audience_years);
    }

    public function test_an_unassigned_faculty_member_cannot_change_the_audience_and_neither_can_once_closed(): void
    {
        $exam = $this->publishedFor(['audience_years' => [4]]);
        $otherSubject = $this->makeSubject('AUD', 'Auditing');
        $outsider = $this->makeFaculty('outsider@example.com', $otherSubject);

        $this->actingAs($outsider)->put(route('faculty.mock-exams.audience', $exam), ['audience_years' => [3]])->assertForbidden();

        $exam->update(['status' => MockExam::STATUS_CLOSED]);
        $this->actingAs($this->makeChair())->put(route('chair.mock-exams.audience', $exam), ['audience_years' => [3]])->assertForbidden();
        $this->assertSame([4], $exam->fresh()->audience_years);
    }

    public function test_publishing_needs_an_audience(): void
    {
        $exam = $this->publishedFor([]);
        $exam->update(['status' => MockExam::STATUS_FOR_REVIEW, 'event_id' => null, 'audience_years' => null]);

        $this->actingAs($this->makeChair())
            ->post(route('chair.mock-exams.publish', $exam))
            ->assertSessionHasErrors('audience');

        $this->assertSame(MockExam::STATUS_FOR_REVIEW, $exam->fresh()->status);
    }

    public function test_the_review_page_shows_the_audience_card(): void
    {
        $exam = $this->publishedFor(['audience_years' => [4]]);

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

    /** A published exam tomorrow with the given audience columns. */
    private function publishedFor(array $audience): MockExam
    {
        $date = now()->addDay()->setTime(8, 0);
        $event = MockExamEvent::create(['exam_date' => $date->toDateString(), 'access_code' => MockExamEvent::newAccessCode($date)]);
        $faculty = $this->makeFaculty('fac' . uniqid() . '@example.com', $this->farId);
        $topicId = $this->makeTopic($this->farId, 'Topic ' . uniqid());

        $exam = MockExam::create(array_merge([
            'event_id' => $event->id,
            'subject_id' => $this->farId,
            'created_by' => $faculty->id,
            'title' => 'FAR Sitting',
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

        return $exam;
    }
}
