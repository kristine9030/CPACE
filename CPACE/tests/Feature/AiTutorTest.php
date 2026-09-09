<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Covers Student\AiTutorController: the chat endpoint's validation and
 * provider-failure fallback, same pattern as NoteQuizTest for the other AI
 * feature. Hand-built schema, same rationale as the other Feature tests in
 * this suite.
 */
class AiTutorTest extends TestCase
{
    private const TABLES = [
        'performance_records', 'quiz_sessions', 'topics', 'subjects',
        'notifications', 'messages', 'conversation_participants', 'conversations', 'users',
    ];

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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('group');
            $table->string('name')->nullable();
            $table->boolean('is_default_group')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();
        });
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('sender_id');
            $table->text('body')->nullable();
            $table->timestamps();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipient_id');
            $table->boolean('is_read')->default(false);
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->unsignedTinyInteger('passing_threshold')->default(75);
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name');
        });
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('session_type')->default('testing');
            $table->boolean('is_practice_room')->default(false);
            $table->string('mode')->default('adaptive');
            $table->integer('total_items')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->integer('duration_secs')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });
        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('topic_id');
            $table->integer('correct_count')->default(0);
            $table->integer('total_attempts')->default(0);
            $table->integer('consecutive_wrong')->default(0);
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_the_chat_endpoint_returns_the_ai_reply(): void
    {
        $student = $this->student();
        $this->fakeAiReply('Sure, let me explain accrual accounting.');

        $response = $this->actingAs($student)->postJson(route('ai-tutor.chat'), [
            'messages' => [['role' => 'user', 'content' => 'Explain accrual accounting']],
        ]);

        $response->assertOk()->assertJson([
            'ok' => true,
            'reply' => 'Sure, let me explain accrual accounting.',
        ]);
    }

    public function test_the_chat_endpoint_requires_at_least_one_message(): void
    {
        $student = $this->student();

        $this->actingAs($student)->postJson(route('ai-tutor.chat'), ['messages' => []])
            ->assertStatus(422);
    }

    public function test_the_chat_endpoint_returns_a_503_when_every_ai_provider_fails(): void
    {
        $student = $this->student();
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response('nope', 500),
            'https://openrouter.ai/*' => Http::response('nope', 500),
        ]);

        $response = $this->actingAs($student)->postJson(route('ai-tutor.chat'), [
            'messages' => [['role' => 'user', 'content' => 'Hello']],
        ]);

        $response->assertStatus(503)->assertJson(['ok' => false]);
    }

    public function test_performance_insights_returns_ai_generated_cards(): void
    {
        $student = $this->student();
        $this->fakeAiReply(json_encode([
            ['tone' => 'red', 'icon' => 'fa-chart-line', 'title' => 'Focus on weak topics', 'desc' => 'Spend more time on Inventory.'],
        ]));

        $response = $this->actingAs($student)->getJson(route('ai-tutor.performance-insights'));

        $response->assertOk()->assertJson([
            'ok' => true,
            'insights' => [
                ['tone' => 'red', 'icon' => 'fa-chart-line', 'title' => 'Focus on weak topics', 'desc' => 'Spend more time on Inventory.'],
            ],
        ]);
    }

    public function test_performance_insights_returns_a_503_when_the_ai_reply_is_unusable(): void
    {
        $student = $this->student();
        $this->fakeAiReply('not json at all');

        $response = $this->actingAs($student)->getJson(route('ai-tutor.performance-insights'));

        $response->assertStatus(503)->assertJson(['ok' => false]);
    }

    private function fakeAiReply(string $text): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => $text]]]]],
            ], 200),
            'https://openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => $text]]],
            ], 200),
        ]);
    }

    private function student(): User
    {
        return User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'setup_completed_at' => now(),
        ]);
    }
}
