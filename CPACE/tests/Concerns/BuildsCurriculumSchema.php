<?php

namespace Tests\Concerns;

use App\Models\Role;
use App\Models\User;
use App\Support\CurriculumScope;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Hand-built SQLite schema for the curriculum-versioning tests (versions,
 * audits, TOS import staging, and the topic / question tables they touch),
 * shared so a new column is added in one place — same reasoning as
 * BuildsMockExamSchema. Starts with one ACTIVE curriculum in $activeId.
 */
trait BuildsCurriculumSchema
{
    private array $curriculumTables = [
        'student_profiles', 'curriculum_import_items', 'curriculum_import_batches',
        'question_variants', 'question_choices', 'questions', 'topics', 'faculty_subjects', 'subjects',
        'curriculum_audits', 'curriculum_versions',
        'notifications', 'messages', 'conversation_participants', 'conversations', 'users',
    ];

    private int $activeId;

    protected function buildCurriculumSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('role_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->boolean('email_verified')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('setup_completed_at')->nullable();
            $table->string('temp_password')->nullable();
            $table->string('profile_photo')->nullable();
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
            $table->unsignedBigInteger('communication_id')->nullable();
            $table->unsignedBigInteger('recipient_id');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('type')->nullable();
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
        Schema::create('curriculum_versions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label', 80);
            $table->string('effective_from_batch', 9)->nullable();
            $table->string('effective_to_batch', 9)->nullable();
            $table->string('status', 10)->default('draft');
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->dateTime('created_at')->nullable();
        });
        Schema::create('curriculum_audits', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('curriculum_version_id');
            $table->unsignedInteger('subject_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('action', 30);
            $table->text('details')->nullable();
            $table->dateTime('created_at')->nullable();
        });
        Schema::create('curriculum_import_batches', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('curriculum_version_id');
            $table->unsignedInteger('created_by')->nullable();
            $table->string('original_filename');
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });
        Schema::create('curriculum_import_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('batch_id');
            $table->unsignedInteger('subject_id');
            $table->unsignedInteger('parent_item_id')->nullable();
            $table->string('ref', 30)->nullable();
            $table->string('name', 150);
            $table->text('full_name')->nullable();
            $table->unsignedTinyInteger('depth')->default(0);
            $table->decimal('weight_percent', 5, 2)->nullable();
            $table->unsignedSmallInteger('item_count')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('included')->default(true);
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20);
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('passing_threshold')->default(75);
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedInteger('curriculum_version_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->decimal('tos_weight', 5, 2)->nullable();
            $table->unsignedSmallInteger('tos_items')->nullable();
            $table->dateTime('gap_flagged_at')->nullable();
            $table->dateTime('gap_warned_at')->nullable();
            $table->boolean('is_active')->default(true);
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('source', 20)->default('faculty');
            $table->text('question_text');
            $table->string('question_type')->default('mcq');
            $table->string('difficulty')->default('moderate');
            $table->text('explanation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('review_status', 10)->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->dateTime('reviewed_at')->nullable();
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
            $table->string('source')->default('faculty');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->primary(['faculty_id', 'subject_id']);
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('batch_year', 9)->nullable();
        });

        $this->activeId = DB::table('curriculum_versions')->insertGetId([
            'label' => 'CPALE 2022', 'effective_from_batch' => '2022-2023',
            'status' => 'active', 'published_at' => now(), 'created_at' => now(),
        ]);
        CurriculumScope::flush();
    }

    protected function dropCurriculumSchema(): void
    {
        foreach ($this->curriculumTables as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function chair(): User
    {
        return User::create([
            'role_id' => Role::ADMIN,
            'first_name' => 'Program', 'last_name' => 'Chair',
            'email' => 'chair@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }

    private function faculty(int $subjectId): User
    {
        $faculty = User::create([
            'role_id' => Role::FACULTY,
            'first_name' => 'Test', 'last_name' => 'Faculty',
            'email' => 'faculty' . $subjectId . '@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'assigned_at' => now()]);

        return $faculty;
    }

    private function studentInBatch(?string $batch, string $email = 'student@example.com'): User
    {
        $student = User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test', 'last_name' => 'Student',
            'email' => $email, 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
        DB::table('student_profiles')->insert(['user_id' => $student->id, 'batch_year' => $batch]);

        return $student;
    }

    private function subject(string $code): int
    {
        return DB::table('subjects')->insertGetId(['code' => $code, 'name' => $code, 'passing_threshold' => 75]);
    }

    private function draft(string $fromBatch = '2027-2028'): int
    {
        $id = DB::table('curriculum_versions')->insertGetId([
            'label' => 'Draft ' . $fromBatch, 'effective_from_batch' => $fromBatch,
            'status' => 'draft', 'created_at' => now(),
        ]);
        CurriculumScope::flush();

        return $id;
    }

    private function topic(int $subjectId, int $versionId, string $name, ?int $parentId = null): int
    {
        return DB::table('topics')->insertGetId([
            'subject_id' => $subjectId, 'curriculum_version_id' => $versionId,
            'parent_id' => $parentId, 'name' => $name, 'sort_order' => 1, 'is_active' => true,
        ]);
    }

    private function question(int $topicId, string $text): int
    {
        $id = DB::table('questions')->insertGetId([
            'topic_id' => $topicId, 'question_text' => $text, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('question_choices')->insert([
            ['question_id' => $id, 'choice_label' => 'A', 'choice_text' => 'Right', 'is_correct' => true],
            ['question_id' => $id, 'choice_label' => 'B', 'choice_text' => 'Wrong', 'is_correct' => false],
        ]);

        return $id;
    }
}
