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
 * Covers Student\SubjectController: the subjects grid's weak-topic count and
 * accuracy rollup, and the 404 guards that keep an inactive subject or a
 * mismatched subject/topic pair from being reachable. Hand-built schema,
 * same rationale as the other Feature tests in this suite.
 */
class StudentSubjectBrowseTest extends TestCase
{
    private const TABLES = [
        'performance_records', 'materials', 'questions', 'topics', 'subjects',
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
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->unsignedTinyInteger('passing_threshold')->default(75);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->text('question_text')->default('');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('title');
            $table->string('kind', 10)->default('file');
            $table->string('file_category', 20)->default('other');
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('external_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('topic_id');
            $table->integer('correct_count')->default(0);
            $table->integer('total_attempts')->default(0);
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_a_topic_below_the_subjects_passing_threshold_counts_as_weak(): void
    {
        $student = $this->student();
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'passing_threshold' => 75, 'created_at' => now(), 'updated_at' => now()]);
        $weakTopicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()]);
        $strongTopicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Leases', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('performance_records')->insert(['student_id' => $student->id, 'topic_id' => $weakTopicId, 'total_attempts' => 10, 'correct_count' => 5]);
        // SQLite (unlike MySQL) truncates integer/integer division, so a
        // fractional accuracy would falsely read as 0% here. A perfect score
        // avoids the truncation while still proving the "strong" topic isn't
        // counted as weak.
        DB::table('performance_records')->insert(['student_id' => $student->id, 'topic_id' => $strongTopicId, 'total_attempts' => 10, 'correct_count' => 10]);

        $response = $this->actingAs($student)->get(route('subjects'));

        $response->assertOk();
        $response->assertViewHas('subjects', function ($subjects) use ($subjectId) {
            $subject = $subjects->firstWhere('id', $subjectId);

            return $subject->weak_count === 1 && $subject->overall_attempts === 20 && $subject->overall_accuracy === 75;
        });
    }

    public function test_an_inactive_subject_cannot_be_opened(): void
    {
        $student = $this->student();
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'OLD', 'name' => 'Retired Subject', 'is_active' => false, 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($student)->get(route('subjects.show', $subjectId))->assertNotFound();
    }

    public function test_a_topic_cannot_be_opened_through_a_subject_it_does_not_belong_to(): void
    {
        $student = $this->student();
        $subjectA = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        $subjectB = DB::table('subjects')->insertGetId(['code' => 'AUD', 'name' => 'Auditing', 'created_at' => now(), 'updated_at' => now()]);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectA, 'name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($student)->get(route('subjects.topic', [$subjectB, $topicId]))->assertNotFound();
    }

    public function test_a_topic_can_be_opened_through_its_own_subject(): void
    {
        $student = $this->student();
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($student)->get(route('subjects.topic', [$subjectId, $topicId]))->assertOk();
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
