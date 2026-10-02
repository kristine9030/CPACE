<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Faculty "Student Performance" must mirror the Test Bank's subject scoping:
 * the Subject filter, and the students/stats/weak-topics it computes, are
 * restricted to the subjects the Program Chair assigned to this faculty
 * member. Hand-built schema, same convention as the other Feature tests.
 */
/**
 * The Student Performance leaderboard: podium + top 10 with strengths and
 * weaknesses. Same hand-built schema as FacultyPerformanceScopeTest.
 */
class FacultyLeaderboardTest extends TestCase
{
    private const TABLES = [
        'faculty_subject_sections', 'sections', 'student_profiles',
        'quiz_answers', 'question_choices', 'questions',
        'performance_records', 'quiz_sessions', 'topics', 'subjects', 'faculty_subjects',
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
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('setup_completed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Creating a student fires User::booted(), which drops them into the
        // default community group - so the chat tables have to exist here too.
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
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('type', 50)->default('normal');
            $table->string('title', 150)->default('');
            $table->text('message')->nullable();
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10);
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name', 150);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('session_type')->default('testing');
            $table->boolean('is_practice_room')->default(false);
            $table->string('mode')->default('adaptive');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedSmallInteger('total_items')->default(0);
            $table->unsignedSmallInteger('correct_answers')->default(0);
            $table->decimal('score_percent', 5, 2)->nullable();
            $table->unsignedInteger('duration_secs')->nullable();
        });

        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('topic_id');
            $table->unsignedInteger('total_attempts')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('consecutive_wrong')->default(0);
            $table->boolean('is_weak_area')->default(false);
        });

        // Empty in every test here - only exist so classWeakTopics()'s
        // "most-missed question" lookup (a left-joinable extra, not the
        // subject of this test) has tables to query instead of erroring.
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->text('question_text');
        });
        Schema::create('question_choices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->text('choice_text');
        });
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('selected_choice')->nullable();
            $table->boolean('is_correct')->nullable();
        });

        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('section', 30)->nullable();
        });
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('faculty_subject_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    private function faculty(): User
    {
        $faculty = User::create([
            'role_id' => Role::FACULTY, 'first_name' => 'Test', 'last_name' => 'Faculty',
            'email' => 'faculty@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
        foreach (['FAR' => 'Financial Accounting', 'TAX' => 'Taxation'] as $code => $name) {
            $id = DB::table('subjects')->insertGetId(['code' => $code, 'name' => $name]);
            DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $id, 'assigned_at' => now()]);
        }

        return $faculty;
    }

    /** A student with a quiz session and per-subject performance records. */
    private function learner(string $name, int $items, int $correct, array $bySubject = []): User
    {
        $student = User::create([
            'role_id' => Role::STUDENT, 'first_name' => $name, 'last_name' => 'Reviewer',
            'email' => strtolower($name) . '@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
        DB::table('student_profiles')->insert(['user_id' => $student->id, 'section' => null]);
        $far = DB::table('subjects')->where('code', 'FAR')->value('id');
        DB::table('quiz_sessions')->insert([
            'student_id' => $student->id, 'session_type' => 'testing', 'subject_id' => $far,
            'started_at' => now(), 'completed_at' => now(), 'total_items' => $items, 'correct_answers' => $correct,
        ]);

        foreach ($bySubject as $code => [$attempts, $right]) {
            $subjectId = DB::table('subjects')->where('code', $code)->value('id');
            $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => "$code topic of $name", 'is_active' => true]);
            DB::table('performance_records')->insert([
                'student_id' => $student->id, 'topic_id' => $topicId, 'total_attempts' => $attempts, 'correct_count' => $right,
            ]);
        }

        return $student;
    }

    public function test_the_board_ranks_by_accuracy_and_shows_strong_and_weak_subjects(): void
    {
        $faculty = $this->faculty();
        $this->learner('Alma', 20, 18, ['FAR' => [10, 9], 'TAX' => [10, 4]]);   // 90%: strong FAR, weak TAX
        $this->learner('Ben', 20, 14);                                           // 70%
        $this->learner('Cara', 20, 12);                                          // 60%

        $response = $this->actingAs($faculty)->get(route('faculty.performance'))->assertOk();

        $response->assertViewHas('leaderboard', function ($board) {
            $names = collect($board['entries'])->pluck('name')->all();
            $alma = $board['entries'][0];

            return $names === ['Alma Reviewer', 'Ben Reviewer', 'Cara Reviewer']
                && $alma['rank'] === 1 && $alma['score'] === 90
                && $alma['best']['code'] === 'FAR' && $alma['best']['accuracy'] === 90
                && $alma['worst']['code'] === 'TAX' && $alma['worst']['accuracy'] === 40;
        });
        $response->assertSee('id="perfLeaderboard"', false)
            ->assertSee('Alma Reviewer')
            ->assertSee('FAR 90%')
            ->assertSee('TAX 40%');
    }

    public function test_students_with_too_few_answers_are_not_ranked(): void
    {
        $faculty = $this->faculty();
        $this->learner('Lucky', 3, 3);          // 100% on three answers
        $this->learner('Steady', 20, 15);       // 75% on twenty

        $this->actingAs($faculty)->get(route('faculty.performance'))
            ->assertViewHas('leaderboard', function ($board) {
                return collect($board['entries'])->pluck('name')->all() === ['Steady Reviewer']
                    && $board['ranked'] === 1 && $board['unranked'] === 1;
            });
    }

    public function test_the_podium_keeps_three_places_and_holds_empty_ones(): void
    {
        $faculty = $this->faculty();
        $this->learner('Solo', 20, 16);

        $html = $this->actingAs($faculty)->get(route('faculty.performance'))->assertOk()->getContent();

        $this->assertSame(3, substr_count($html, 'class="pod pod-'));
        $this->assertSame(2, substr_count($html, 'No student yet'));
        $this->assertLessThan(strpos($html, 'pod pod-1'), strpos($html, 'pod pod-2'), 'second place stands left of first');
        $this->assertLessThan(strpos($html, 'pod pod-3'), strpos($html, 'pod pod-1'), 'third place stands right of first');
    }

    public function test_searching_the_table_does_not_reshuffle_the_board(): void
    {
        $faculty = $this->faculty();
        $this->learner('Alma', 20, 18);
        $this->learner('Ben', 20, 14);

        $this->actingAs($faculty)->get(route('faculty.performance', ['search' => 'Ben']))
            ->assertViewHas('leaderboard', fn ($b) => collect($b['entries'])->pluck('name')->all() === ['Alma Reviewer', 'Ben Reviewer'])
            ->assertViewHas('students', fn ($s) => $s->count() === 1);
    }

    public function test_the_ajax_refresh_also_carries_the_leaderboard(): void
    {
        $faculty = $this->faculty();
        $this->learner('Alma', 20, 18);

        $this->actingAs($faculty)->get(route('faculty.performance'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('id="perfLeaderboard"', false);
    }
}
