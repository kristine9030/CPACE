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
 * Covers GlobalSearchController's data-isolation rules: a student's search
 * never surfaces another student's private notes, and a faculty member's
 * search never surfaces test-bank questions outside their assigned subjects
 * - the same authorization boundary already fixed once in TestBankController.
 * Hand-built schema, same rationale as the other Feature tests in this suite.
 */
class GlobalSearchTest extends TestCase
{
    private const TABLES = [
        'quiz_sessions', 'question_choices', 'questions', 'review_notes', 'materials', 'student_badges', 'badges',
        'study_plan_items', 'study_plans', 'community_resources', 'community_posts',
        'communications', 'topics', 'subjects', 'faculty_subjects',
        'notifications', 'messages', 'conversation_participants', 'conversations', 'users',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // The controller uses whereRaw("CONCAT(...)"), which is MySQL syntax
        // (production's real driver). SQLite (this test's in-memory driver)
        // has no built-in CONCAT, so register one here purely to let the
        // query run under the test driver.
        DB::connection()->getPdo()->sqliteCreateFunction('CONCAT', fn (...$args) => implode('', $args));

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
            $table->string('title', 150)->default('');
            $table->text('message')->nullable();
            $table->boolean('is_read')->default(false);
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->timestamp('assigned_at')->nullable();
            $table->primary(['faculty_id', 'subject_id']);
        });
        Schema::create('review_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('title', 180);
            $table->longText('content')->nullable();
            $table->string('tags')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->text('question_text');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('question_choices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('choice_label', 1)->default('A');
            $table->text('choice_text');
            $table->boolean('is_correct')->default(false);
        });
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('original_name')->nullable();
            $table->boolean('is_active')->default(true);
        });
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('description')->nullable();
        });
        Schema::create('student_badges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('badge_id');
        });
        Schema::create('study_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->boolean('is_active')->default(true);
        });
        Schema::create('study_plan_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('topic_id');
            $table->date('scheduled_date')->nullable();
            $table->string('priority', 10)->default('medium');
            $table->boolean('is_completed')->default(false);
        });
        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
        });
        Schema::create('community_resources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uploader_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('original_name')->nullable();
        });
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
        });
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('session_type')->default('testing');
            $table->boolean('is_practice_room')->default(false);
            $table->string('mode')->default('adaptive');
            $table->decimal('score_percent', 5, 2)->nullable();
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

    public function test_a_query_shorter_than_two_characters_returns_no_results(): void
    {
        $student = $this->student('student@example.com');

        $response = $this->actingAs($student)->getJson(route('global.search', ['q' => 'a']));

        $response->assertOk()->assertJson([]);
    }

    public function test_a_students_search_never_surfaces_another_students_review_notes(): void
    {
        $me = $this->student('me@example.com');
        $other = $this->student('other@example.com');
        DB::table('review_notes')->insert([
            'student_id' => $other->id, 'title' => 'Partnership Dissolution Notes', 'content' => 'x',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('review_notes')->insert([
            'student_id' => $me->id, 'title' => 'My Partnership Notes', 'content' => 'x',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $results = $this->actingAs($me)->getJson(route('global.search', ['q' => 'Partnership']))->json();

        $titles = collect($results)->pluck('title');
        $this->assertTrue($titles->contains('My Partnership Notes'));
        $this->assertFalse($titles->contains('Partnership Dissolution Notes'));
    }

    public function test_a_faculty_members_search_never_surfaces_questions_outside_their_assigned_subjects(): void
    {
        $faculty = $this->faculty();
        $assignedSubjectId = DB::table('subjects')->insertGetId(['code' => 'AUD', 'name' => 'Auditing', 'created_at' => now(), 'updated_at' => now()]);
        $otherSubjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $assignedSubjectId, 'assigned_at' => now()]);
        $assignedTopicId = DB::table('topics')->insertGetId(['subject_id' => $assignedSubjectId, 'name' => 'Risk', 'created_at' => now(), 'updated_at' => now()]);
        $otherTopicId = DB::table('topics')->insertGetId(['subject_id' => $otherSubjectId, 'name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('questions')->insert(['topic_id' => $assignedTopicId, 'question_text' => 'Sampling technique question', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('questions')->insert(['topic_id' => $otherTopicId, 'question_text' => 'Sampling for inventory count', 'created_at' => now(), 'updated_at' => now()]);

        $results = $this->actingAs($faculty)->getJson(route('global.search', ['q' => 'Sampling']))->json();

        $descs = collect($results)->pluck('title');
        $this->assertTrue($descs->contains('Sampling technique question'));
        $this->assertFalse($descs->contains('Sampling for inventory count'));
    }

    private function student(string $email): User
    {
        return User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test', 'last_name' => 'Student',
            'email' => $email, 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }

    private function faculty(): User
    {
        return User::create([
            'role_id' => Role::FACULTY,
            'first_name' => 'Test', 'last_name' => 'Faculty',
            'email' => 'faculty@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }
}
