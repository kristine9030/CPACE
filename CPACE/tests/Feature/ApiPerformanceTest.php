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
 * Covers the mobile API's Performance endpoint (Api\PerformanceApiController)
 * - read-only, but must scope every figure to the authenticated student.
 * Hand-built schema, same rationale as the other Feature tests in this suite
 * (migration set doesn't run cleanly from scratch).
 */
class ApiPerformanceTest extends TestCase
{
    private const TABLES = [
        'performance_records', 'quiz_sessions', 'topics', 'subjects',
        'api_tokens', 'notifications', 'messages', 'conversation_participants',
        'conversations', 'student_profiles', 'users',
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
            $table->unsignedBigInteger('user_id')->primary();
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->string('color')->nullable();
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name');
        });
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
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

    public function test_performance_stats_never_reflect_another_students_activity(): void
    {
        $me = $this->student('me@example.com');
        $other = $this->student('other@example.com');
        DB::table('quiz_sessions')->insert([
            'student_id' => $other->id, 'session_type' => 'testing',
            'total_items' => 100, 'correct_answers' => 1, 'started_at' => now(), 'completed_at' => now(),
        ]);
        DB::table('quiz_sessions')->insert([
            'student_id' => $me->id, 'session_type' => 'testing',
            'total_items' => 10, 'correct_answers' => 8, 'started_at' => now(), 'completed_at' => now(),
        ]);

        $response = $this->getJson('/api/performance', $this->tokenHeaders($me->id));

        $response->assertOk()
            ->assertJsonPath('stats.attempted', 10)
            ->assertJsonPath('stats.correct', 8);
    }

    public function test_the_performance_endpoint_requires_a_valid_token(): void
    {
        $this->getJson('/api/performance')->assertStatus(401);
    }

    private function student(string $email): User
    {
        $student = User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test', 'last_name' => 'Student',
            'email' => $email, 'password' => Hash::make('password'),
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
