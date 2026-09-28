<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Services\AiQuestionAssistantService;
use App\Services\CurriculumGapFiller;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsCurriculumSchema;
use Tests\TestCase;

/**
 * AI substitute questions: a topic that stays short of its TOS item count
 * gets a notice, then a final warning, then AI-drafted questions that stay
 * hidden from students until the faculty or chair approves them.
 */
class AiSubstituteQuestionsTest extends TestCase
{
    use BuildsCurriculumSchema;

    private int $far;

    /** How many times the fake AI was asked for a draft. */
    private int $aiCalls = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildCurriculumSchema();
        $this->far = $this->subject('FAR');
        config(['curriculum.gap_fill_grace_days' => 14, 'curriculum.gap_fill_warning_days' => 3, 'curriculum.gap_fill_max_per_topic' => 5]);
        $this->fakeAi();
    }

    protected function tearDown(): void
    {
        $this->dropCurriculumSchema();
        parent::tearDown();
    }

    public function test_a_short_topic_first_gets_a_notice_to_its_faculty_and_no_ai_drafts(): void
    {
        $faculty = $this->faculty($this->far);
        $this->chair();
        $area = $this->area('Cash', 3);

        $this->artisan('curriculum:fill-gaps')->assertSuccessful();

        $this->assertNotNull(DB::table('topics')->where('id', $area)->value('gap_flagged_at'));
        $this->assertSame(0, $this->aiCalls);
        $this->assertSame(1, $this->notificationsFor($faculty->id, 'Test Bank needs questions'));
        $this->assertSame(1, DB::table('notifications')->count(), 'only the faculty gets the first notice');
    }

    public function test_many_short_topics_send_one_digest_per_person(): void
    {
        $faculty = $this->faculty($this->far);
        $this->area('Cash', 3);
        $this->area('Receivables', 4);
        $this->area('Inventories', 2);

        $this->filler()->run();

        $this->assertSame(1, DB::table('notifications')->where('recipient_id', $faculty->id)->count());
        $message = DB::table('notifications')->value('message');
        $this->assertStringContainsString('3 topic(s)', $message);
        $this->assertStringContainsString('9 question(s)', $message);
    }

    public function test_the_final_warning_goes_to_faculty_and_chair_shortly_before_the_deadline(): void
    {
        $faculty = $this->faculty($this->far);
        $chair = $this->chair();
        $this->area('Cash', 3, flaggedDaysAgo: 12);

        $this->filler()->run();

        $this->assertSame(1, $this->notificationsFor($faculty->id, 'Test Bank deadline approaching'));
        $this->assertSame(1, $this->notificationsFor($chair->id, 'Test Bank deadline approaching'));
        $this->assertSame(0, $this->aiCalls);

        $this->filler()->run();
        $this->assertSame(1, $this->notificationsFor($faculty->id, 'Test Bank deadline approaching'), 'warned only once');
    }

    public function test_after_the_grace_period_ai_drafts_the_missing_items_as_hidden_pending_questions(): void
    {
        $faculty = $this->faculty($this->far);
        $chair = $this->chair();
        $area = $this->area('Cash', 3, flaggedDaysAgo: 15);
        $leaf = $this->topic($this->far, $this->activeId, 'Bank Reconciliation', $area);
        $this->question($leaf, 'An existing faculty question'); // 1 of 3 already there

        $this->filler()->run();

        $drafts = Question::where('source', 'ai_substitute')->with('choices')->get();
        $this->assertCount(2, $drafts, 'only the missing items are drafted');
        foreach ($drafts as $draft) {
            $this->assertFalse($draft->is_active, 'students never see an unreviewed AI question');
            $this->assertSame('pending', $draft->review_status);
            $this->assertNull($draft->created_by, 'not credited to any faculty member');
            $this->assertSame($leaf, (int) $draft->topic_id, 'drafted into the most specific subtopic');
            $this->assertCount(4, $draft->choices);
            $this->assertSame(1, $draft->choices->where('is_correct', true)->count());
        }

        $this->assertSame(1, $this->notificationsFor($faculty->id, 'AI substitute questions need review'));
        $this->assertSame(1, $this->notificationsFor($chair->id, 'AI substitute questions need review'));
        $this->assertSame(1, DB::table('curriculum_audits')->where('action', 'ai_gap_filled')->count());

        // Pending drafts count toward the quota, so the next run drafts nothing more.
        $this->filler()->run();
        $this->assertSame(2, Question::where('source', 'ai_substitute')->count());
    }

    public function test_drafting_is_capped_per_topic_per_run(): void
    {
        config(['curriculum.gap_fill_max_per_topic' => 2]);
        $this->faculty($this->far);
        $this->area('Cash', 10, flaggedDaysAgo: 20);

        $this->filler()->run();

        $this->assertSame(2, Question::where('source', 'ai_substitute')->count());
    }

    public function test_drafting_is_capped_across_all_topics_per_run(): void
    {
        config(['curriculum.gap_fill_max_per_run' => 3]);
        $this->faculty($this->far);
        $this->area('Cash', 2, flaggedDaysAgo: 20);
        $this->area('Receivables', 2, flaggedDaysAgo: 20);
        $this->area('Inventories', 2, flaggedDaysAgo: 20);

        $this->filler()->run();
        $this->assertSame(3, Question::where('source', 'ai_substitute')->count());

        $this->filler()->run();
        $this->assertSame(6, Question::where('source', 'ai_substitute')->count(), 'the rest are drafted the next night');
    }

    public function test_a_topic_back_at_quota_is_cleared_and_left_alone(): void
    {
        $this->faculty($this->far);
        $area = $this->area('Cash', 1, flaggedDaysAgo: 20);
        $this->question($area, 'Faculty caught up');

        $this->filler()->run();

        $this->assertNull(DB::table('topics')->where('id', $area)->value('gap_flagged_at'));
        $this->assertSame(0, $this->aiCalls);
        $this->assertSame(0, DB::table('notifications')->count());
    }

    public function test_an_ai_outage_drafts_nothing_and_does_not_crash(): void
    {
        $this->faculty($this->far);
        $this->area('Cash', 3, flaggedDaysAgo: 20);
        $this->app->instance(AiQuestionAssistantService::class, new class extends AiQuestionAssistantService {
            public function draftQuestion(string $subjectName, string $topicName, string $difficulty, string $questionType, array $existingQuestions, ?string $seedIdea): array
            {
                throw new \RuntimeException('provider down');
            }
        });

        $summary = $this->filler()->run();

        $this->assertSame(0, $summary['drafted']);
        $this->assertSame(3, $summary['failed']);
        $this->assertSame(0, Question::count());
    }

    public function test_topics_of_a_draft_curriculum_are_not_enforced(): void
    {
        $this->faculty($this->far);
        $draftId = $this->draft();
        $id = $this->topic($this->far, $draftId, 'Draft topic');
        DB::table('topics')->where('id', $id)->update(['tos_items' => 5]);

        $summary = $this->filler()->run();

        $this->assertSame(0, $summary['checked']);
    }

    public function test_the_assigned_faculty_can_approve_an_ai_question_which_makes_it_live(): void
    {
        $faculty = $this->faculty($this->far);
        $id = $this->pendingAiQuestion();

        $this->actingAs($faculty)->post(route('faculty.test-bank.ai-review.approve', $id))->assertSessionHas('status');

        $q = Question::find($id);
        $this->assertTrue($q->is_active);
        $this->assertSame('approved', $q->review_status);
        $this->assertSame($faculty->id, (int) $q->reviewed_by);
    }

    public function test_the_chair_can_reject_an_ai_question_which_keeps_it_hidden(): void
    {
        $chair = $this->chair();
        $id = $this->pendingAiQuestion();

        $this->actingAs($chair)->post(route('chair.ai-review.reject', $id))->assertSessionHas('status');

        $q = Question::find($id);
        $this->assertFalse($q->is_active);
        $this->assertSame('rejected', $q->review_status);

        // Already decided: a second decision is refused.
        $this->actingAs($chair)->post(route('chair.ai-review.approve', $id))->assertSessionHas('warning');
        $this->assertFalse(Question::find($id)->is_active);
    }

    public function test_faculty_not_assigned_to_the_subject_cannot_review_it(): void
    {
        $other = $this->faculty($this->subject('AUD'));
        $id = $this->pendingAiQuestion();

        $this->actingAs($other)->post(route('faculty.test-bank.ai-review.approve', $id))->assertSessionHas('warning');

        $this->assertSame('pending', Question::find($id)->review_status);
    }

    public function test_the_review_queue_lists_pending_ai_questions(): void
    {
        $faculty = $this->faculty($this->far);
        $this->pendingAiQuestion('What is the cash cut-off rule?');

        $this->actingAs($faculty)->get(route('faculty.test-bank.ai-review'))
            ->assertOk()
            ->assertSee('What is the cash cut-off rule?')
            ->assertSee('AI substitute');

        $this->actingAs($this->chair())->get(route('chair.ai-review'))
            ->assertOk()
            ->assertSee('What is the cash cut-off rule?');
    }

    public function test_assigned_faculty_can_generate_now_before_the_grace_period_ends(): void
    {
        $faculty = $this->faculty($this->far);
        $area = $this->area('Cash', 3, flaggedDaysAgo: 2); // well within the 14-day grace period

        $this->actingAs($faculty)
            ->post(route('faculty.test-bank.ai-review.generate', $area))
            ->assertSessionHas('status');

        $this->assertSame(3, $this->aiCalls, 'the button skips the grace-period wait entirely');
        $drafts = Question::where('source', 'ai_substitute')->get();
        $this->assertCount(3, $drafts);
        foreach ($drafts as $draft) {
            $this->assertFalse($draft->is_active);
            $this->assertSame('pending', $draft->review_status);
        }
        $this->assertSame(1, $this->notificationsFor($faculty->id, 'AI substitute questions need review'));
    }

    public function test_generate_now_is_capped_at_max_per_topic_and_leaves_the_rest_short(): void
    {
        config(['curriculum.gap_fill_max_per_topic' => 2]);
        $faculty = $this->faculty($this->far);
        $area = $this->area('Cash', 5, flaggedDaysAgo: 1);

        $this->actingAs($faculty)->post(route('faculty.test-bank.ai-review.generate', $area));

        $this->assertSame(2, Question::where('source', 'ai_substitute')->count());
    }

    public function test_generate_now_on_a_topic_already_at_quota_drafts_nothing(): void
    {
        $faculty = $this->faculty($this->far);
        $area = $this->area('Cash', 1);
        $this->question($area, 'Already enough');

        $this->actingAs($faculty)
            ->post(route('faculty.test-bank.ai-review.generate', $area))
            ->assertSessionHas('status');

        $this->assertSame(0, $this->aiCalls);
        $this->assertSame(0, Question::where('source', 'ai_substitute')->count());
    }

    public function test_faculty_not_assigned_to_the_subject_cannot_generate_now(): void
    {
        $other = $this->faculty($this->subject('AUD'));
        $area = $this->area('Cash', 3, flaggedDaysAgo: 1);

        $this->actingAs($other)
            ->post(route('faculty.test-bank.ai-review.generate', $area))
            ->assertSessionHas('warning');

        $this->assertSame(0, $this->aiCalls);
    }

    public function test_the_chair_can_generate_now_and_the_shortlist_shows_on_the_review_page(): void
    {
        $this->faculty($this->far);
        $this->area('Cash', 3, flaggedDaysAgo: 1);

        $this->actingAs($this->chair())->get(route('chair.ai-review'))
            ->assertOk()
            ->assertSee('Cash')
            ->assertSee('Generate now');
    }

    // ── helpers ─────────────────────────────────────────────────────────────

    private function filler(): CurriculumGapFiller
    {
        return app(CurriculumGapFiller::class);
    }

    private function area(string $name, int $tosItems, ?int $flaggedDaysAgo = null): int
    {
        $id = $this->topic($this->far, $this->activeId, $name);
        DB::table('topics')->where('id', $id)->update([
            'tos_items' => $tosItems,
            'gap_flagged_at' => $flaggedDaysAgo !== null ? now()->subDays($flaggedDaysAgo) : null,
        ]);

        return $id;
    }

    private function pendingAiQuestion(string $text = 'An AI drafted question'): int
    {
        $id = $this->question($this->topic($this->far, $this->activeId, 'Cash'), $text);
        DB::table('questions')->where('id', $id)->update([
            'source' => 'ai_substitute', 'review_status' => 'pending', 'is_active' => false,
        ]);

        return $id;
    }

    private function notificationsFor(int $userId, string $title): int
    {
        return DB::table('notifications')->where('recipient_id', $userId)->where('title', $title)->count();
    }

    private function fakeAi(): void
    {
        $test = $this;
        $this->app->instance(AiQuestionAssistantService::class, new class($test) extends AiQuestionAssistantService {
            public function __construct(private $test)
            {
            }

            public function draftQuestion(string $subjectName, string $topicName, string $difficulty, string $questionType, array $existingQuestions, ?string $seedIdea): array
            {
                $this->test->countAiCall();

                return [
                    'question_text' => "AI question about {$topicName} (" . count($existingQuestions) . ')',
                    'explanation' => 'Because.',
                    'choices' => [
                        'a' => ['text' => 'Right', 'is_correct' => true],
                        'b' => ['text' => 'Wrong 1', 'is_correct' => false],
                        'c' => ['text' => 'Wrong 2', 'is_correct' => false],
                        'd' => ['text' => 'Wrong 3', 'is_correct' => false],
                    ],
                ];
            }
        });
    }

    public function countAiCall(): void
    {
        $this->aiCalls++;
    }
}
