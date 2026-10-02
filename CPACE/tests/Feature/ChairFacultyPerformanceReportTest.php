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
 * Covers Chair\FacultyOversightController::performance — the "Faculty
 * Performance Report" is a snapshot of the CURRENT test bank's health, so
 * every figure on it (the top KPI cards, the per-faculty rows, and the
 * per-subject "Needs Your Attention" rows) must agree with each other and
 * reflect only the active curriculum. A question authored under an archived
 * curriculum is real past work, but showing it here would make a freshly
 * published, still-empty curriculum look falsely covered.
 */
class ChairFacultyPerformanceReportTest extends TestCase
{
    private const TABLES = [
        'quiz_answers', 'question_variants', 'questions', 'topics', 'curriculum_versions', 'subjects', 'faculty_subjects',
        'faculty_subject_sections', 'sections', 'notifications', 'messages', 'conversation_participants', 'conversations', 'users',
    ];

    private int $activeId;
    private int $archivedId;

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
        Schema::create('curriculum_versions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label', 80);
            $table->string('status', 10)->default('draft');
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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('year_level')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('faculty_subject_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
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

        $this->archivedId = DB::table('curriculum_versions')->insertGetId(['label' => 'Old', 'status' => 'archived']);
        $this->activeId = DB::table('curriculum_versions')->insertGetId(['label' => 'New', 'status' => 'active']);
        \App\Support\CurriculumScope::flush();
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        \App\Support\CurriculumScope::flush();
        parent::tearDown();
    }

    public function test_a_freshly_published_curriculum_with_no_questions_reports_zero_everywhere_not_just_per_subject(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty('me@example.com');
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'assigned_at' => now()]);

        // All 324 "old" questions belong to the now-archived curriculum.
        $oldTopicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'curriculum_version_id' => $this->archivedId, 'name' => 'Retired Topic', 'created_at' => now(), 'updated_at' => now()]);
        $oldQuestionId = DB::table('questions')->insertGetId(['topic_id' => $oldTopicId, 'created_by' => $faculty->id, 'question_text' => 'Old', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('quiz_answers')->insert(['question_id' => $oldQuestionId, 'is_correct' => true]);

        // The active curriculum has a topic but nobody has authored into it yet.
        DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'curriculum_version_id' => $this->activeId, 'name' => 'Fresh Topic', 'created_at' => now(), 'updated_at' => now()]);
        \App\Support\CurriculumScope::flush();

        $response = $this->actingAs($chair)->get(route('chair.faculty.performance'));

        $response->assertOk();
        $response->assertViewHas('stats', fn ($stats) => $stats['questions'] === 0 && $stats['contributors'] === 0);
        $response->assertViewHas('facultyRows', function ($rows) use ($faculty) {
            $row = collect($rows)->firstWhere('id', $faculty->id);

            return $row['questions'] === 0;
        });
        // The top KPI card and the per-subject row must never disagree.
        $response->assertViewHas('subjectRows', function ($rows) {
            $far = collect($rows)->firstWhere('code', 'FAR');

            return $far['questions'] === 0;
        });
    }

    public function test_questions_authored_into_the_active_curriculum_are_counted(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty('me@example.com');
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'assigned_at' => now()]);

        $newTopicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'curriculum_version_id' => $this->activeId, 'name' => 'Fresh Topic', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('questions')->insert(['topic_id' => $newTopicId, 'created_by' => $faculty->id, 'question_text' => 'New', 'created_at' => now(), 'updated_at' => now()]);
        \App\Support\CurriculumScope::flush();

        $this->actingAs($chair)->get(route('chair.faculty.performance'))
            ->assertOk()
            ->assertViewHas('stats', fn ($stats) => $stats['questions'] === 1 && $stats['contributors'] === 1);
    }

    public function test_the_calibration_chart_reads_student_accuracy_from_the_active_curriculum_only(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty('me@example.com');
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'assigned_at' => now()]);

        // Old curriculum: heavily answered, mostly wrong (would drag the chart down to ~20%).
        $oldTopicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'curriculum_version_id' => $this->archivedId, 'name' => 'Retired Topic', 'created_at' => now(), 'updated_at' => now()]);
        $oldQuestionId = DB::table('questions')->insertGetId(['topic_id' => $oldTopicId, 'created_by' => $faculty->id, 'question_text' => 'Old', 'created_at' => now(), 'updated_at' => now()]);
        for ($i = 0; $i < 4; $i++) {
            DB::table('quiz_answers')->insert(['question_id' => $oldQuestionId, 'is_correct' => false]);
        }
        DB::table('quiz_answers')->insert(['question_id' => $oldQuestionId, 'is_correct' => true]);

        // Active curriculum: a handful of answers, all correct.
        $newTopicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'curriculum_version_id' => $this->activeId, 'name' => 'Fresh Topic', 'created_at' => now(), 'updated_at' => now()]);
        $newQuestionId = DB::table('questions')->insertGetId(['topic_id' => $newTopicId, 'created_by' => $faculty->id, 'question_text' => 'New', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('quiz_answers')->insert(['question_id' => $newQuestionId, 'is_correct' => true]);
        DB::table('quiz_answers')->insert(['question_id' => $newQuestionId, 'is_correct' => true]);
        \App\Support\CurriculumScope::flush();

        $this->actingAs($chair)->get(route('chair.faculty.performance'))
            ->assertOk()
            ->assertViewHas('subjectRows', function ($rows) {
                $far = collect($rows)->firstWhere('code', 'FAR');

                // 100%, not the ~20% the archived curriculum's answers would produce.
                return $far['accuracy'] === 100 && $far['answered'] === 2;
            });
    }

    private function chair(): User
    {
        return User::create([
            'role_id' => Role::ADMIN, 'first_name' => 'Program', 'last_name' => 'Chair',
            'email' => 'chair@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }

    public function test_the_faculty_page_is_one_list_with_account_and_contribution_together(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty('me@example.com');
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'assigned_at' => now()]);
        \App\Support\CurriculumScope::flush();

        // Both old entry points show the same single page — no tabs.
        foreach (['chair.faculty', 'chair.faculty.performance'] as $route) {
            $this->actingAs($chair)->get(route($route))
                ->assertOk()
                ->assertDontSee('mtab-bar', false)
                ->assertDontSee('role="tablist"', false)
                ->assertSee('id="facultyList"', false)
                // One row carries the account (email, Active) and the contribution status.
                ->assertSeeInOrder(['me@example.com', 'FAR', 'Active', 'No questions yet', 'Assign subjects', 'Edit account'])
                // Summary cards + the list only: no to-do queue, subject risk, or charts.
                ->assertSee('Questions Written')->assertSee('Faculty List')
                ->assertDontSee('Needs Your Attention')
                ->assertDontSee('Subject Risk')
                ->assertDontSee('<canvas', false);
        }
    }

    public function test_bulk_deactivate_and_activate_selected_faculty(): void
    {
        $chair = $this->chair();
        $a = $this->faculty('a@example.com');
        $b = $this->faculty('b@example.com');
        $untouched = $this->faculty('c@example.com');

        $this->actingAs($chair)->post(route('chair.faculty.bulk'), ['action' => 'deactivate', 'faculty_ids' => [$a->id, $b->id]])
            ->assertRedirect()->assertSessionHas('status', '2 faculty accounts deactivated.');
        $this->assertFalse((bool) $a->fresh()->is_active);
        $this->assertFalse((bool) $b->fresh()->is_active);
        $this->assertTrue((bool) $untouched->fresh()->is_active);

        $this->actingAs($chair)->post(route('chair.faculty.bulk'), ['action' => 'activate', 'faculty_ids' => [$a->id]])
            ->assertSessionHas('status', '1 faculty account activated.');
        $this->assertTrue((bool) $a->fresh()->is_active);
    }

    public function test_bulk_delete_removes_only_faculty_with_no_content(): void
    {
        $chair = $this->chair();
        $empty = $this->faculty('empty@example.com');
        $author = $this->faculty('author@example.com');
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'curriculum_version_id' => $this->activeId, 'name' => 'T', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('questions')->insert(['topic_id' => $topicId, 'created_by' => $author->id, 'question_text' => 'Q', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($chair)->post(route('chair.faculty.bulk'), ['action' => 'delete', 'faculty_ids' => [$empty->id, $author->id, $chair->id]])
            ->assertRedirect()
            ->assertSessionHas('status', fn ($msg) => str_starts_with($msg, '1 faculty account deleted.') && str_contains($msg, 'Kept Test Faculty'));

        $this->assertNull(User::find($empty->id));
        $this->assertNotNull(User::find($author->id), 'a faculty member with questions is kept');
        $this->assertNotNull(User::find($chair->id), 'non-faculty ids are ignored');
    }

    public function test_bulk_delete_with_only_content_owners_deletes_nothing(): void
    {
        $chair = $this->chair();
        $author = $this->faculty('author@example.com');
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'curriculum_version_id' => $this->activeId, 'name' => 'T', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('questions')->insert(['topic_id' => $topicId, 'created_by' => $author->id, 'question_text' => 'Q', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($chair)->post(route('chair.faculty.bulk'), ['action' => 'delete', 'faculty_ids' => [$author->id]])
            ->assertSessionHas('error', fn ($msg) => str_starts_with($msg, 'No accounts were deleted.'));
        $this->assertNotNull(User::find($author->id));
    }

    public function test_bulk_action_is_validated(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty('a@example.com');

        $this->actingAs($chair)->post(route('chair.faculty.bulk'), ['action' => 'promote', 'faculty_ids' => [$faculty->id]])
            ->assertSessionHasErrors('action');
        $this->actingAs($chair)->post(route('chair.faculty.bulk'), ['action' => 'delete', 'faculty_ids' => []])
            ->assertSessionHasErrors('faculty_ids');
        $this->assertNotNull(User::find($faculty->id));
    }

    public function test_the_summary_shows_subject_coverage_instead_of_accuracy(): void
    {
        $chair = $this->chair();
        DB::table('subjects')->insert(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        \App\Support\CurriculumScope::flush();

        $this->actingAs($chair)->get(route('chair.faculty'))
            ->assertOk()
            ->assertSee('Subjects Covered')
            ->assertSee('No one is writing questions for')
            ->assertDontSee('Student Accuracy')
            ->assertSee('id="facultySelectAll"', false);
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
