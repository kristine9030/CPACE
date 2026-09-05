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
 * The Faculty Dashboard must scope its headline stats (question count,
 * active students) to the subjects the Program Chair assigned to the
 * signed-in faculty member - same subject-scoping pattern already fixed
 * once in TestBankController. Hand-built schema, same rationale as the
 * other Feature tests in this suite (migration set doesn't run cleanly
 * from scratch).
 */
class FacultyDashboardScopeTest extends TestCase
{
    private const TABLES = [
        'quiz_sessions', 'questions', 'topics', 'subjects', 'faculty_subjects',
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
            $table->timestamps();
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->text('question_text');
            $table->string('question_type')->default('mcq');
            $table->string('difficulty')->default('moderate');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('session_type')->default('testing');
            $table->integer('total_items')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->decimal('score_percent', 5, 2)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });
        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->primary(['faculty_id', 'subject_id']);
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_the_dashboard_headline_stats_only_count_the_facultys_assigned_subjects(): void
    {
        $faculty = $this->faculty();
        $farId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        $audId = DB::table('subjects')->insertGetId(['code' => 'AUD', 'name' => 'Auditing', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $farId, 'assigned_at' => now()]);
        $farTopicId = DB::table('topics')->insertGetId(['subject_id' => $farId, 'name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()]);
        $audTopicId = DB::table('topics')->insertGetId(['subject_id' => $audId, 'name' => 'Risk', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('questions')->insert(['topic_id' => $farTopicId, 'question_text' => 'FAR question', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('questions')->insert(['topic_id' => $audTopicId, 'question_text' => 'AUD question 1', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('questions')->insert(['topic_id' => $audTopicId, 'question_text' => 'AUD question 2', 'created_at' => now(), 'updated_at' => now()]);

        $farStudent = $this->student('far-student@example.com');
        $audStudent = $this->student('aud-student@example.com');
        DB::table('quiz_sessions')->insert([
            'student_id' => $farStudent->id, 'subject_id' => $farId, 'session_type' => 'testing',
            'total_items' => 10, 'correct_answers' => 7, 'started_at' => now(), 'completed_at' => now(),
        ]);
        DB::table('quiz_sessions')->insert([
            'student_id' => $audStudent->id, 'subject_id' => $audId, 'session_type' => 'testing',
            'total_items' => 10, 'correct_answers' => 7, 'started_at' => now(), 'completed_at' => now(),
        ]);

        $response = $this->actingAs($faculty)->get(route('faculty.dashboard'));

        $response->assertOk();
        $response->assertViewHas('stats', fn ($stats) => $stats['total_questions'] === 1 && $stats['active_students'] === 1);
        $response->assertSee('FAR question');
        $response->assertDontSee('AUD question 1');
    }

    private function faculty(): User
    {
        return User::create([
            'role_id' => Role::FACULTY, 'first_name' => 'Test', 'last_name' => 'Faculty',
            'email' => 'faculty@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }

    private function student(string $email): User
    {
        return User::create([
            'role_id' => Role::STUDENT, 'first_name' => 'Test', 'last_name' => 'Student',
            'email' => $email, 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }
}
