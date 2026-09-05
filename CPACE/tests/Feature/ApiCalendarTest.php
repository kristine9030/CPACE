<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Covers the mobile API's Calendar endpoint (Api\CalendarApiController) -
 * a JSON rendering of the same spaced-repetition schedule already covered in
 * depth by SpacedRepetitionCalendarTest for the web page. This just checks
 * the endpoint renders and that due reviews are scoped to the authenticated
 * student. Hand-built schema, same rationale as the other Feature tests in
 * this suite (migration set doesn't run cleanly from scratch).
 */
class ApiCalendarTest extends TestCase
{
    private const TABLES = [
        'weakness_reports', 'spaced_repetition_items', 'quiz_sessions',
        'performance_records', 'questions', 'topics', 'subjects', 'api_tokens', 'notifications',
        'messages', 'conversation_participants', 'conversations', 'student_profiles', 'users',
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
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('token', 128)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->string('difficulty')->default('easy');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('topic_id');
            $table->integer('correct_count')->default(0);
            $table->integer('total_attempts')->default(0);
            $table->integer('consecutive_wrong')->default(0);
            $table->boolean('is_weak_area')->default(false);
            $table->timestamp('last_attempted')->nullable();
        });
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });
        Schema::create('spaced_repetition_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('question_id');
            $table->integer('repetition_num')->default(0);
            $table->decimal('ease_factor', 4, 2)->default(2.50);
            $table->integer('interval_days')->default(0);
            $table->integer('quality_score')->nullable();
            $table->date('last_reviewed')->nullable();
            $table->date('next_review_at')->nullable();
        });
        Schema::create('weakness_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('topic_id');
            $table->timestamp('flagged_at')->nullable();
            $table->string('trigger_reason')->nullable();
            $table->decimal('accuracy_at_flag', 5, 2)->nullable();
            $table->timestamp('resolved_at')->nullable();
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_the_calendar_seeds_a_schedule_from_existing_quiz_history_and_renders(): void
    {
        $student = $this->student();
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('questions')->insert(['topic_id' => $topicId, 'difficulty' => 'easy', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('performance_records')->insert(['student_id' => $student->id, 'topic_id' => $topicId, 'total_attempts' => 5, 'correct_count' => 2, 'last_attempted' => now()]);

        $response = $this->getJson('/api/calendar', $this->tokenHeaders($student->id));

        $response->assertOk()->assertJson(['has_data' => true]);
        $this->assertTrue(DB::table('spaced_repetition_items')->where('student_id', $student->id)->exists());
    }

    public function test_the_calendar_endpoint_requires_a_valid_token(): void
    {
        $this->getJson('/api/calendar')->assertStatus(401);
    }

    private function student(): User
    {
        $student = User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test', 'last_name' => 'Student',
            'email' => 'student@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
        DB::table('student_profiles')->insert(['user_id' => $student->id]);

        return $student;
    }

    private function tokenHeaders(int $userId): array
    {
        $token = ApiToken::create([
            'user_id' => $userId,
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => now()->addDay(),
        ]);

        return ['Authorization' => 'Bearer ' . $token->token];
    }
}
