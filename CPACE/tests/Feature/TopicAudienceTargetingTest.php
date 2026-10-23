<?php

namespace Tests\Feature;

use App\Models\Topic;
use App\Support\CurriculumScope;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsCurriculumSchema;
use Tests\TestCase;

/**
 * A topic can be restricted to the year level(s), section(s), and/or
 * semester(s) that actually cover it, so a student quizzed on a subject
 * never draws questions from a topic scheduled for a later term (see
 * CurriculumScope::restrictToAudience()). No targeting on a topic means
 * everyone can be quizzed on it, same as before this feature existed.
 */
class TopicAudienceTargetingTest extends TestCase
{
    use BuildsCurriculumSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildCurriculumSchema();
    }

    protected function tearDown(): void
    {
        $this->dropCurriculumSchema();
        parent::tearDown();
    }

    public function test_an_untargeted_topic_is_visible_to_every_student(): void
    {
        $subjectId = $this->subject('FAR');
        $topicId = $this->topic($subjectId, $this->activeId, 'Open Topic');
        $student = $this->studentInYearSection('2022-2023', 3, 'BSA 3101');

        $this->assertSame([$topicId], Topic::forStudent($student->id)->pluck('id')->all());
    }

    public function test_a_topic_targeted_to_a_year_level_is_hidden_from_other_years(): void
    {
        $subjectId = $this->subject('FAR');
        $topicId = $this->topic($subjectId, $this->activeId, 'Third Year Topic', null, ['years' => [3]]);

        $thirdYear = $this->studentInYearSection('2022-2023', 3, 'BSA 3101', 'y3@example.com');
        $fourthYear = $this->studentInYearSection('2022-2023', 4, 'BSA 4101', 'y4@example.com');

        $this->assertSame([$topicId], Topic::forStudent($thirdYear->id)->pluck('id')->all());
        $this->assertSame([], Topic::forStudent($fourthYear->id)->pluck('id')->all());
    }

    public function test_a_topic_targeted_to_a_semester_is_only_visible_that_semester(): void
    {
        $subjectId = $this->subject('FAR');
        // BSA 3201 = Year 3, 2nd Sem (see Section::parseName).
        $topicId = $this->topic($subjectId, $this->activeId, 'Non-Financial Assets', null, ['years' => [3], 'semesters' => [2]]);

        $firstSem = $this->studentInYearSection('2022-2023', 3, 'BSA 3101', 'sem1@example.com');
        $secondSem = $this->studentInYearSection('2022-2023', 3, 'BSA 3201', 'sem2@example.com');

        $this->assertSame([], Topic::forStudent($firstSem->id)->pluck('id')->all(),
            '3rd year in 1st sem has not reached this topic yet');
        $this->assertSame([$topicId], Topic::forStudent($secondSem->id)->pluck('id')->all());
    }

    public function test_a_topic_can_be_narrowed_further_to_specific_sections(): void
    {
        $subjectId = $this->subject('FAR');
        $topicId = $this->topic($subjectId, $this->activeId, 'Section-Specific Topic', null, ['sections' => ['BSA 3101']]);

        $inSection = $this->studentInYearSection('2022-2023', 3, 'BSA 3101', 'in@example.com');
        $outOfSection = $this->studentInYearSection('2022-2023', 3, 'BSA 3102', 'out@example.com');

        $this->assertSame([$topicId], Topic::forStudent($inSection->id)->pluck('id')->all());
        $this->assertSame([], Topic::forStudent($outOfSection->id)->pluck('id')->all());
    }

    public function test_an_unparseable_section_name_fails_open_on_the_semester_dimension(): void
    {
        $subjectId = $this->subject('FAR');
        $topicId = $this->topic($subjectId, $this->activeId, 'Semester-Targeted Topic', null, ['semesters' => [2]]);

        // Legacy-format section name, doesn't match Section::parseName's pattern.
        $student = $this->studentInYearSection('2022-2023', 3, 'BSA-3A', 'legacy@example.com');

        $this->assertSame([$topicId], Topic::forStudent($student->id)->pluck('id')->all(),
            'semester cannot be derived, so the semester-targeted topic stays visible rather than vanishing');
    }

    public function test_restrict_to_active_is_not_affected_by_audience_targeting(): void
    {
        $subjectId = $this->subject('FAR');
        $topicId = $this->topic($subjectId, $this->activeId, 'Chair-Only Visible Topic', null, ['years' => [3]]);

        $rows = CurriculumScope::restrictToActive(DB::table('topics'))->pluck('id')->all();

        $this->assertSame([$topicId], $rows, 'program-wide views (chair/faculty) must see every topic regardless of audience');
    }
}
