<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Covers the Achievements page (AchievementController + AchievementService):
 * a brand-new student sees a sane empty state, and the leaderboard ranks by
 * total correct answers while excluding deactivated accounts. Hand-built
 * schema, same rationale as the other Feature tests in this suite.
 */
class StudentAchievementsTest extends TestCase
{
    private const TABLES = [
        'performance_records', 'quiz_answers', 'quiz_sessions', 'questions',
        'notifications', 'messages', 'conversation_participants', 'conversations', 'student_profiles', 'users',
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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->date('exam_target_date')->nullable();
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id')->nullable();
        });
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('session_type')->default('testing');
            $table->string('mode')->default('adaptive');
            $table->integer('total_items')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->decimal('score_percent', 5, 2)->nullable();
            $table->integer('duration_secs')->default(0);
            $table->timestamp('completed_at')->nullable();
        });
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('question_id');
            $table->timestamp('answered_at')->nullable();
        });
        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->integer('correct_count')->default(0);
            $table->integer('total_attempts')->default(0);
            $table->timestamp('last_attempted')->nullable();
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_a_brand_new_student_sees_the_achievements_page_with_zero_progress(): void
    {
        $student = $this->student('new@example.com');

        $response = $this->actingAs($student)->get(route('achievements'));

        $response->assertOk();
        $response->assertViewHas('earnedCount', 0);
        $response->assertViewHas('leaderboard', fn ($leaderboard) => $leaderboard['status']['ranked'] === false);
    }

    public function test_the_leaderboard_ranks_by_total_correct_answers_and_excludes_deactivated_accounts(): void
    {
        $me = $this->student('me@example.com');
        $topScorer = $this->student('top@example.com');
        $deactivated = $this->student('gone@example.com', active: false);

        $this->completedSession($me->id, 5);
        $this->completedSession($topScorer->id, 20);
        $this->completedSession($deactivated->id, 100);

        $response = $this->actingAs($me)->get(route('achievements'));

        $response->assertOk();
        $response->assertViewHas('leaderboard', function ($leaderboard) {
            return $leaderboard['status']['rank'] === 2 && $leaderboard['status']['total'] === 2;
        });
    }

    private function student(string $email, bool $active = true): User
    {
        $student = User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => $email,
            'password' => Hash::make('password'),
            'is_active' => $active,
            'setup_completed_at' => now(),
        ]);
        DB::table('student_profiles')->insert(['user_id' => $student->id]);

        return $student;
    }

    private function completedSession(int $studentId, int $correctAnswers): void
    {
        DB::table('quiz_sessions')->insert([
            'student_id' => $studentId,
            'session_type' => 'testing',
            'mode' => 'adaptive',
            'total_items' => $correctAnswers,
            'correct_answers' => $correctAnswers,
            'completed_at' => now(),
        ]);
    }
}
