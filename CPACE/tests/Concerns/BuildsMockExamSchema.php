<?php

namespace Tests\Concerns;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Hand-built SQLite schema for the mock exam feature tests.
 *
 * This suite builds schema by hand rather than running migrations (see
 * [[cpace-db-from-sql-dump]] — the migration files drift from the real,
 * dump-driven production schema). Normally each test file carries its own
 * copy, but the mock exam touches thirteen tables across six test files, and
 * a per-file copy is exactly how the `is_late` column came to be missing from
 * one schema and not another. One shared builder means a new column is added
 * in one place and every mock exam test picks it up.
 */
trait BuildsMockExamSchema
{
    /** Drop order matters: children before parents. */
    private array $mockExamTables = [
        'mock_exam_proctor_captures', 'mock_exam_proctor_events', 'mock_exam_attempts',
        'mock_exam_registrations', 'mock_exam_audits', 'mock_exam_items', 'mock_exam_topics',
        'mock_exams', 'mock_exam_events',
        'points_log', 'weakness_reports', 'spaced_repetition_items', 'performance_records',
        'question_choices', 'questions', 'topics', 'faculty_subjects', 'subjects',
        'student_profiles', 'notifications', 'messages', 'conversation_participants',
        'conversations', 'users',
    ];

    protected function buildMockExamSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('role_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamp('setup_completed_at')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('avatar_color')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Needed twice over: by the global view composer behind the top-bar
        // counters, and by User::booted(), which joins every new student to the
        // default community group chat.
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
            $table->string('type')->nullable();
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('section')->nullable();
            $table->integer('total_points')->default(0);
            $table->integer('streak_days')->default(0);
            $table->boolean('is_alumni')->default(false);
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->boolean('is_active')->default(true);
        });
        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('question_text');
            $table->string('question_type')->default('mcq');
            $table->string('difficulty')->default('moderate');
            $table->text('explanation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('question_choices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('choice_label', 1);
            $table->text('choice_text');
            $table->boolean('is_correct')->default(false);
        });

        // Analytics targets — a mock exam feeds these but awards no points.
        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('topic_id');
            $table->integer('correct_count')->default(0);
            $table->integer('total_attempts')->default(0);
            // Matches production: a real STORED generated column.
            $table->decimal('accuracy_rate', 5, 2)->storedAs(
                'CASE WHEN total_attempts = 0 THEN 0 ELSE ROUND((correct_count * 100.0) / total_attempts, 2) END'
            );
            $table->integer('consecutive_wrong')->default(0);
            $table->boolean('is_weak_area')->default(false);
            $table->timestamp('last_attempted')->nullable();
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
        Schema::create('points_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->integer('points');
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        // ── the feature's own tables ─────────────────────────────────────
        Schema::create('mock_exam_events', function (Blueprint $table) {
            $table->id();
            $table->date('exam_date')->unique();
            $table->string('access_code', 32)->unique();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
        Schema::create('mock_exams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('created_by');
            $table->string('title', 150);
            $table->string('status', 20)->default('draft');
            $table->dateTime('scheduled_at')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(180);
            $table->unsignedSmallInteger('total_items')->default(0);
            $table->string('topic_mode', 10)->default('manual');
            $table->string('question_mode', 10)->default('manual');
            $table->text('review_note')->nullable();
            $table->dateTime('submitted_for_review_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });
        Schema::create('mock_exam_topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('topic_id');
        });
        Schema::create('mock_exam_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('source_question_id')->nullable();
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->text('question_text');
            $table->string('question_type', 20)->default('mcq');
            $table->string('difficulty', 20)->nullable();
            $table->json('choices');
            $table->text('explanation')->nullable();
            $table->unsignedSmallInteger('points')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('mock_exam_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 40);
            $table->text('details')->nullable();
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('mock_exam_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('student_id');
            $table->dateTime('redeemed_at');
            $table->unique(['event_id', 'student_id']);
        });
        Schema::create('mock_exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('student_id');
            $table->dateTime('started_at');
            $table->dateTime('submitted_at')->nullable();
            $table->json('answers')->nullable();
            $table->unsignedSmallInteger('score')->default(0);
            $table->unsignedSmallInteger('total_points')->default(0);
            $table->decimal('percent', 5, 2)->nullable();
            $table->boolean('is_late')->default(false);
            $table->unsignedSmallInteger('flag_count')->default(0);
            $table->string('status', 20)->default('in_progress');
            $table->timestamps();
            $table->unique(['exam_id', 'student_id']);
        });
        Schema::create('mock_exam_proctor_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attempt_id');
            $table->string('type', 30);
            $table->dateTime('occurred_at');
            $table->string('meta', 255)->nullable();
        });
        Schema::create('mock_exam_proctor_captures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attempt_id');
            $table->string('kind', 10);
            $table->string('path', 255);
            $table->dateTime('captured_at');
            $table->string('reason', 30)->default('interval');
        });
    }

    protected function dropMockExamSchema(): void
    {
        foreach ($this->mockExamTables as $table) {
            Schema::dropIfExists($table);
        }
    }

    // ── fixtures ─────────────────────────────────────────────────────────

    protected function makeUser(int $roleId, string $email, array $overrides = []): User
    {
        return User::create(array_merge([
            'role_id' => $roleId,
            'first_name' => 'Test',
            'last_name' => ucfirst(explode('@', $email)[0]),
            'email' => $email,
            'password' => Hash::make('password'),
            'is_active' => true,
            'setup_completed_at' => now(),
        ], $overrides));
    }

    protected function makeStudent(string $email = 'student@example.com', bool $isAlumni = false): User
    {
        $student = $this->makeUser(Role::STUDENT, $email);
        DB::table('student_profiles')->insert(['user_id' => $student->id, 'is_alumni' => $isAlumni]);

        return $student;
    }

    /** A faculty member assigned to the given subject. */
    protected function makeFaculty(string $email, ?int $subjectId = null): User
    {
        $faculty = $this->makeUser(Role::FACULTY, $email);
        if ($subjectId !== null) {
            DB::table('faculty_subjects')->insert([
                'faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'assigned_at' => now(),
            ]);
        }

        return $faculty;
    }

    /** The Program Chair is the admin role in this app. */
    protected function makeChair(string $email = 'chair@example.com'): User
    {
        return $this->makeUser(Role::ADMIN, $email);
    }

    protected function makeSubject(string $code = 'FAR', string $name = 'Financial Accounting'): int
    {
        return DB::table('subjects')->insertGetId(['code' => $code, 'name' => $name, 'is_active' => true]);
    }

    protected function makeTopic(int $subjectId, string $name = 'Inventory'): int
    {
        return DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => $name, 'is_active' => true]);
    }

    /**
     * A question with two choices, A correct. Returns its id.
     */
    protected function makeQuestion(int $topicId, string $difficulty = 'moderate', ?string $text = null): int
    {
        $id = DB::table('questions')->insertGetId([
            'topic_id' => $topicId,
            'question_text' => $text ?? ('Question ' . uniqid()),
            'question_type' => 'mcq',
            'difficulty' => $difficulty,
            'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('question_choices')->insert([
            ['question_id' => $id, 'choice_label' => 'A', 'choice_text' => 'Right', 'is_correct' => true],
            ['question_id' => $id, 'choice_label' => 'B', 'choice_text' => 'Wrong', 'is_correct' => false],
        ]);

        return $id;
    }
}
