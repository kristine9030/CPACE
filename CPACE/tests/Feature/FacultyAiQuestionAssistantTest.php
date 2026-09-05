<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Faculty-side AI assistance for the test bank: drafting a full question from
 * a subject/topic, and rewriting a question into a variant with a fallback to
 * the rule-based paraphraser when the AI is unavailable. Hand-built schema,
 * same convention as the other Feature tests in this suite.
 */
class FacultyAiQuestionAssistantTest extends TestCase
{
    private const TABLES = ['question_variants', 'question_choices', 'questions', 'topics', 'subjects', 'faculty_subjects', 'users'];

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('role_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamp('setup_completed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10);
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name', 150);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->unsignedBigInteger('created_by');
            $table->text('question_text');
            $table->string('question_type')->default('mcq');
            $table->string('difficulty')->default('moderate');
            $table->text('explanation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('question_choices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('choice_label', 1);
            $table->text('choice_text');
            $table->boolean('is_correct')->default(false);
        });

        Schema::create('question_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->text('variant_text');
            $table->string('source', 20)->default('faculty');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
        });
    }

    protected function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }

    private function faculty(): User
    {
        return User::create([
            'role_id'    => Role::FACULTY,
            'first_name' => 'Fac',
            'last_name'  => 'Ulty',
            'email'      => 'faculty@example.com',
            'password'   => bcrypt('secret'),
            'setup_completed_at' => now(),
        ]);
    }

    private function assignSubject(User $faculty, int $subjectId): void
    {
        \DB::table('faculty_subjects')->insert([
            'faculty_id'  => $faculty->id,
            'subject_id'  => $subjectId,
            'assigned_at' => now(),
        ]);
    }

    /**
     * Fakes a successful reply from BOTH providers with the same content, so
     * the test doesn't care whether the service actually reaches Gemini or
     * falls through to OpenRouter — which depends on whether GEMINI_API_KEY
     * is set in the current environment (it isn't in CI, which runs off
     * .env.example, but it is in local dev off the real .env).
     */
    private function fakeAiReply(string $text): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => $text]]]],
                ],
            ], 200),
            'https://openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => $text]]],
            ], 200),
        ]);
    }

    public function test_ai_draft_endpoint_fills_a_full_mcq_question_from_gemini(): void
    {
        $subject = \DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting and Reporting', 'is_active' => true]);
        $topicId = \DB::table('topics')->insertGetId(['subject_id' => $subject, 'name' => 'Inventory', 'sort_order' => 1, 'is_active' => true]);
        $faculty = $this->faculty();
        $this->assignSubject($faculty, $subject);

        $this->fakeAiReply(json_encode([
            'question_text' => 'Under the lower of cost or NRV rule, inventory is written down when:',
            'choices' => [
                ['text' => 'NRV falls below cost', 'is_correct' => true],
                ['text' => 'Cost falls below NRV', 'is_correct' => false],
                ['text' => 'NRV equals cost', 'is_correct' => false],
                ['text' => 'The entity changes its cost formula', 'is_correct' => false],
            ],
            'explanation' => 'PAS 2 requires inventory to be measured at the lower of cost and NRV.',
        ]));

        $response = $this->actingAs($faculty)
            ->postJson(route('faculty.question.ai-draft'), [
                'topic_id'      => $topicId,
                'difficulty'    => 'Medium',
                'question_type' => 'mcq',
                'seed_idea'     => null,
            ]);

        $response->assertOk();
        $response->assertJsonPath('choices.a.text', 'NRV falls below cost');
        $response->assertJsonPath('choices.a.is_correct', true);
        $response->assertJsonPath('choices.b.is_correct', false);
        $this->assertStringContainsString('PAS 2', $response->json('explanation'));
    }

    public function test_ai_draft_endpoint_returns_a_friendly_error_when_all_providers_fail(): void
    {
        $subject = \DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'FAR', 'is_active' => true]);
        $topicId = \DB::table('topics')->insertGetId(['subject_id' => $subject, 'name' => 'Inventory', 'sort_order' => 1, 'is_active' => true]);
        $faculty = $this->faculty();
        $this->assignSubject($faculty, $subject);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([], 500),
            'https://openrouter.ai/*' => Http::response([], 500),
        ]);

        $response = $this->actingAs($faculty)
            ->postJson(route('faculty.question.ai-draft'), [
                'topic_id'      => $topicId,
                'difficulty'    => 'Easy',
                'question_type' => 'mcq',
            ]);

        $response->assertStatus(503);
        $response->assertJsonStructure(['message']);
    }

    public function test_ai_draft_endpoint_returns_a_friendly_not_assigned_response_instead_of_a_bare_403(): void
    {
        // Subject exists but is never assigned to this faculty member via faculty_subjects.
        $subject = \DB::table('subjects')->insertGetId(['code' => 'AUD', 'name' => 'Auditing', 'is_active' => true]);
        $topicId = \DB::table('topics')->insertGetId(['subject_id' => $subject, 'name' => 'Risk Assessment', 'sort_order' => 1, 'is_active' => true]);

        $response = $this->actingAs($this->faculty())
            ->postJson(route('faculty.question.ai-draft'), [
                'topic_id'      => $topicId,
                'difficulty'    => 'Easy',
                'question_type' => 'mcq',
            ]);

        $response->assertStatus(403);
        $response->assertJson(['not_assigned' => true]);
    }

    public function test_suggest_variant_uses_ai_rewrite_when_available(): void
    {
        $subject = \DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'FAR', 'is_active' => true]);
        $topicId = \DB::table('topics')->insertGetId(['subject_id' => $subject, 'name' => 'Inventory', 'sort_order' => 1, 'is_active' => true]);
        $questionId = \DB::table('questions')->insertGetId([
            'topic_id' => $topicId, 'created_by' => 1,
            'question_text' => 'Which of the following is NOT a cost formula under PAS 2?',
            'question_type' => 'mcq', 'difficulty' => 'moderate',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $faculty = $this->faculty();
        $this->assignSubject($faculty, $subject);

        $this->fakeAiReply('Identify which of the following is NOT a cost formula under PAS 2?');

        $response = $this->actingAs($faculty)
            ->postJson(route('faculty.question.variants.suggest', $questionId));

        $response->assertOk();
        $response->assertJson(['source' => 'ai']);
        $this->assertStringContainsString('NOT a cost formula', $response->json('draft'));
    }

    public function test_suggest_variant_falls_back_to_rule_based_paraphraser_when_ai_fails(): void
    {
        $subject = \DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'FAR', 'is_active' => true]);
        $topicId = \DB::table('topics')->insertGetId(['subject_id' => $subject, 'name' => 'Inventory', 'sort_order' => 1, 'is_active' => true]);
        $questionId = \DB::table('questions')->insertGetId([
            'topic_id' => $topicId, 'created_by' => 1,
            'question_text' => 'Which of the following is NOT a cost formula under PAS 2?',
            'question_type' => 'mcq', 'difficulty' => 'moderate',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $faculty = $this->faculty();
        $this->assignSubject($faculty, $subject);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([], 500),
            'https://openrouter.ai/*' => Http::response([], 500),
        ]);

        $response = $this->actingAs($faculty)
            ->postJson(route('faculty.question.variants.suggest', $questionId));

        $response->assertOk();
        $response->assertJson(['source' => 'rule']);
        $this->assertNotEmpty($response->json('draft'));
    }

    public function test_store_variant_tags_the_source_it_was_told_came_from_ai(): void
    {
        $subject = \DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'FAR', 'is_active' => true]);
        $topicId = \DB::table('topics')->insertGetId(['subject_id' => $subject, 'name' => 'Inventory', 'sort_order' => 1, 'is_active' => true]);
        $questionId = \DB::table('questions')->insertGetId([
            'topic_id' => $topicId, 'created_by' => 1,
            'question_text' => 'Which of the following is NOT a cost formula under PAS 2?',
            'question_type' => 'mcq', 'difficulty' => 'moderate',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $faculty = $this->faculty();
        $this->assignSubject($faculty, $subject);

        $this->actingAs($faculty)
            ->post(route('faculty.question.variants.store', $questionId), [
                'variant_text' => 'Identify which of the following is NOT a cost formula under PAS 2?',
                'source'       => 'ai',
            ]);

        $this->assertDatabaseHas('question_variants', [
            'question_id' => $questionId,
            'source'      => 'ai',
        ]);
    }
}
