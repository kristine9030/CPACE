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
 * Covers Chair\FacultyOversightController::activity - the per-faculty
 * contribution report computes question counts and accuracy from ONLY that
 * faculty member's own questions, so this locks in that another faculty's
 * questions/answers never bleed into the numbers shown. Hand-built schema,
 * same rationale as the other Feature tests in this suite (migration set
 * doesn't run cleanly from scratch).
 */
class ChairFacultyActivityTest extends TestCase
{
    private const TABLES = [
        'quiz_answers', 'question_variants', 'questions', 'topics', 'curriculum_versions', 'subjects', 'faculty_subjects',
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
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('curriculum_versions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label', 80);
            $table->string('status', 10)->default('draft');
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedInteger('curriculum_version_id')->nullable();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->primary(['faculty_id', 'subject_id']);
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('question_text');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('question_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
        });
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->boolean('is_correct')->nullable();
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        \App\Support\CurriculumScope::flush();
        parent::tearDown();
    }

    public function test_the_activity_report_only_counts_this_facultys_own_questions_and_answers(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty('me@example.com');
        $otherFaculty = $this->faculty('other@example.com');
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()]);

        $myQuestionId = DB::table('questions')->insertGetId([
            'topic_id' => $topicId, 'created_by' => $faculty->id, 'question_text' => 'Mine',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('quiz_answers')->insert(['question_id' => $myQuestionId, 'is_correct' => true]);
        DB::table('quiz_answers')->insert(['question_id' => $myQuestionId, 'is_correct' => false]);

        $otherQuestionId = DB::table('questions')->insertGetId([
            'topic_id' => $topicId, 'created_by' => $otherFaculty->id, 'question_text' => 'Not mine',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('quiz_answers')->insert(['question_id' => $otherQuestionId, 'is_correct' => false]);
        DB::table('quiz_answers')->insert(['question_id' => $otherQuestionId, 'is_correct' => false]);

        $response = $this->actingAs($chair)->get(route('chair.faculty.activity', $faculty->id));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) {
            return $stats['questions'] === 1 && $stats['answered'] === 2 && $stats['accuracy'] === 50;
        });
        $response->assertSee('Mine');
        $response->assertDontSee('Not mine');
    }

    public function test_a_question_authored_under_an_archived_curriculum_does_not_count_as_current_activity(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty('me@example.com');
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);

        $archivedVersion = DB::table('curriculum_versions')->insertGetId(['label' => 'Old', 'status' => 'archived']);
        $activeVersion = DB::table('curriculum_versions')->insertGetId(['label' => 'New', 'status' => 'active']);
        $oldTopicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'curriculum_version_id' => $archivedVersion, 'name' => 'Retired Topic', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'curriculum_version_id' => $activeVersion, 'name' => 'Current Topic', 'created_at' => now(), 'updated_at' => now()]);

        DB::table('questions')->insert([
            'topic_id' => $oldTopicId, 'created_by' => $faculty->id, 'question_text' => 'From the old curriculum',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        \App\Support\CurriculumScope::flush();

        // "Needs Your Attention" would call this faculty "never contributed"
        // for the current curriculum, so its own activity page must agree
        // rather than surfacing the retired-curriculum question as if it counted.
        $response = $this->actingAs($chair)->get(route('chair.faculty.activity', $faculty->id));

        $response->assertOk();
        $response->assertViewHas('stats', fn ($stats) => $stats['questions'] === 0);
        $response->assertDontSee('From the old curriculum');
    }

    public function test_a_faculty_member_with_no_contributions_shows_a_zero_state(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty('new@example.com');

        $response = $this->actingAs($chair)->get(route('chair.faculty.activity', $faculty->id));

        $response->assertOk();
        $response->assertViewHas('stats', fn ($stats) => $stats['questions'] === 0 && $stats['accuracy'] === null);
    }

    private function chair(): User
    {
        return User::create([
            'role_id' => Role::ADMIN, 'first_name' => 'Program', 'last_name' => 'Chair',
            'email' => 'chair@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }

    private function faculty(string $email): User
    {
        return User::create([
            'role_id' => Role::FACULTY, 'first_name' => 'Test', 'last_name' => 'Faculty',
            'email' => $email, 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }
}
