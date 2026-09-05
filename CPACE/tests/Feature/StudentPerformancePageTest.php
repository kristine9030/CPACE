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
 * Covers Student\PerformanceController: a brand-new student sees a sane
 * empty state, and the page's figures are scoped strictly to the signed-in
 * student. Hand-built schema, same rationale as the other Feature tests in
 * this suite.
 */
class StudentPerformancePageTest extends TestCase
{
    private const TABLES = [
        'quiz_sessions', 'performance_records', 'topics', 'subjects', 'student_profiles',
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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->date('exam_target_date')->nullable();
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->unsignedTinyInteger('passing_threshold')->default(75);
            $table->timestamps();
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('topic_id');
            $table->integer('correct_count')->default(0);
            $table->integer('total_attempts')->default(0);
            $table->integer('consecutive_wrong')->default(0);
        });
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('session_type')->default('testing');
            $table->string('mode')->default('adaptive');
            $table->integer('total_items')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->decimal('score_percent', 5, 2)->nullable();
            $table->integer('duration_secs')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_a_brand_new_student_sees_a_zero_state_performance_page(): void
    {
        $student = $this->student('new@example.com');

        $response = $this->actingAs($student)->get(route('performance'));

        $response->assertOk();
        $response->assertViewHas('stats', fn ($stats) => $stats['accuracy'] === 0 && $stats['attempted'] === 0);
        $response->assertViewHas('weaknesses', fn ($weaknesses) => $weaknesses->isEmpty());
    }

    public function test_a_students_performance_figures_never_reflect_another_students_activity(): void
    {
        $me = $this->student('me@example.com');
        $other = $this->student('other@example.com');
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);

        // Other student has a lot of (wrong) activity that must not bleed into mine.
        DB::table('quiz_sessions')->insert([
            'student_id' => $other->id, 'subject_id' => $subjectId, 'session_type' => 'testing',
            'total_items' => 10, 'correct_answers' => 1, 'started_at' => now(), 'completed_at' => now(),
        ]);
        DB::table('quiz_sessions')->insert([
            'student_id' => $me->id, 'subject_id' => $subjectId, 'session_type' => 'testing',
            'total_items' => 10, 'correct_answers' => 8, 'started_at' => now(), 'completed_at' => now(),
        ]);

        $response = $this->actingAs($me)->get(route('performance'));

        $response->assertOk();
        $response->assertViewHas('stats', fn ($stats) => $stats['attempted'] === 10 && $stats['correct'] === 8);
    }

    private function student(string $email): User
    {
        return User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => $email,
            'password' => Hash::make('password'),
            'is_active' => true,
            'setup_completed_at' => now(),
        ]);
    }
}
