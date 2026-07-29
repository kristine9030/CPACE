<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FacultyTestBankTest extends TestCase
{
    /**
     * Tables torn down in FK-safe order.
     *
     * Hand-built schema, same rationale as the other Feature tests in this
     * suite: the migration set cannot run cleanly from scratch, so
     * RefreshDatabase is not used. question_choices is modelled after the
     * real production schema (choice_label, no sort_order/timestamps) since
     * that is what QuestionChoice/TestBankController actually read and write
     * - the Laravel migration file for this table has drifted from it.
     */
    private const TABLES = [
        'question_choices', 'questions', 'topics', 'subjects',
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
            $table->boolean('email_verified')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('setup_completed_at')->nullable();
            $table->string('temp_password')->nullable();
            $table->string('profile_photo')->nullable();
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
            $table->boolean('is_read')->default(false);
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
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
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
        // Matches the real production schema, not the drifted migration file:
        // choice_label (A-D), no sort_order, no timestamps.
        Schema::create('question_choices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('choice_label', 1);
            $table->text('choice_text');
            $table->boolean('is_correct')->default(false);
            $table->foreign('question_id')->references('id')->on('questions')->cascadeOnDelete();
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_faculty_can_create_an_mcq_question_with_exactly_one_correct_choice(): void
    {
        $faculty = $this->faculty();
        $topicId = $this->topic();

        $this->actingAs($faculty)->post(route('faculty.question.store'), [
            'topic_id'       => $topicId,
            'question_text'  => 'Which financial statement reports assets and liabilities?',
            'question_type'  => 'mcq',
            'difficulty'     => 'Easy',
            'choice_a'       => 'Income Statement',
            'choice_b'       => 'Balance Sheet',
            'choice_c'       => 'Cash Flow Statement',
            'choice_d'       => 'Statement of Equity',
            'correct_answer' => 'b',
            'is_active'      => '1',
        ])->assertRedirect(route('faculty.test-bank'));

        $question = DB::table('questions')->where('topic_id', $topicId)->first();
        $this->assertNotNull($question);
        $this->assertSame('easy', $question->difficulty); // "Easy" label mapped to the DB enum
        $this->assertSame($faculty->id, $question->created_by);

        $choices = DB::table('question_choices')->where('question_id', $question->id)->get();
        $this->assertCount(4, $choices);
        $this->assertSame(1, $choices->where('is_correct', true)->count());
        $this->assertSame('Balance Sheet', $choices->firstWhere('choice_label', 'B')->choice_text);
        $this->assertTrue((bool) $choices->firstWhere('choice_label', 'B')->is_correct);
    }

    public function test_faculty_can_create_a_true_false_question(): void
    {
        $faculty = $this->faculty();
        $topicId = $this->topic();

        $this->actingAs($faculty)->post(route('faculty.question.store'), [
            'topic_id'      => $topicId,
            'question_text' => 'The going concern assumption presumes the entity will continue operating.',
            'question_type' => 'true_false',
            'difficulty'    => 'Medium',
            'tf_answer'     => 'true',
            'is_active'     => '1',
        ])->assertRedirect(route('faculty.test-bank'));

        $question = DB::table('questions')->where('topic_id', $topicId)->first();
        $this->assertSame('moderate', $question->difficulty);

        $choices = DB::table('question_choices')->where('question_id', $question->id)->get();
        $this->assertCount(2, $choices);
        $this->assertTrue((bool) $choices->firstWhere('choice_label', 'A')->is_correct); // "True"
        $this->assertFalse((bool) $choices->firstWhere('choice_label', 'B')->is_correct); // "False"
    }

    public function test_an_incomplete_mcq_submission_is_rejected_and_nothing_is_saved(): void
    {
        $faculty = $this->faculty();
        $topicId = $this->topic();

        $this->actingAs($faculty)->post(route('faculty.question.store'), [
            'topic_id'      => $topicId,
            'question_text' => 'Missing choice_d and a correct answer.',
            'question_type' => 'mcq',
            'difficulty'    => 'Easy',
            'choice_a'      => 'A',
            'choice_b'      => 'B',
            'choice_c'      => 'C',
            // choice_d and correct_answer omitted
            'is_active'     => '1',
        ])->assertSessionHasErrors(['choice_d', 'correct_answer']);

        $this->assertSame(0, DB::table('questions')->count());
    }

    public function test_updating_a_question_replaces_its_choices_rather_than_editing_them_in_place(): void
    {
        $faculty = $this->faculty();
        $topicId = $this->topic();
        $questionId = $this->mcqQuestion($topicId, $faculty->id, correct: 'a');

        $originalChoiceIds = DB::table('question_choices')->where('question_id', $questionId)->pluck('id');

        $this->actingAs($faculty)->put(route('faculty.question.update', $questionId), [
            'topic_id'       => $topicId,
            'question_text'  => 'Updated wording',
            'question_type'  => 'mcq',
            'difficulty'     => 'Hard',
            'choice_a'       => 'New A',
            'choice_b'       => 'New B',
            'choice_c'       => 'New C',
            'choice_d'       => 'New D',
            'correct_answer' => 'c',
            'is_active'      => '1',
        ])->assertRedirect(route('faculty.test-bank'));

        $question = DB::table('questions')->find($questionId);
        $this->assertSame('difficult', $question->difficulty);
        $this->assertSame('Updated wording', $question->question_text);

        $newChoices = DB::table('question_choices')->where('question_id', $questionId)->get();
        $this->assertCount(4, $newChoices);
        // The old choice rows were deleted, not updated in place - none of the
        // new rows share an id with the originals.
        $this->assertEmpty($newChoices->pluck('id')->intersect($originalChoiceIds));
        $this->assertTrue((bool) $newChoices->firstWhere('choice_label', 'C')->is_correct);
        $this->assertFalse((bool) $newChoices->firstWhere('choice_label', 'A')->is_correct);
    }

    public function test_deleting_a_question_cascades_its_choices(): void
    {
        $faculty = $this->faculty();
        $topicId = $this->topic();
        $questionId = $this->mcqQuestion($topicId, $faculty->id, correct: 'a');

        $this->assertSame(4, DB::table('question_choices')->where('question_id', $questionId)->count());

        $this->actingAs($faculty)->delete(route('faculty.question.destroy', $questionId))
            ->assertRedirect(route('faculty.test-bank'));

        $this->assertNull(DB::table('questions')->find($questionId));
        $this->assertSame(0, DB::table('question_choices')->where('question_id', $questionId)->count());
    }

    public function test_chair_cannot_manage_the_test_bank(): void
    {
        $chair = User::create([
            'role_id' => Role::ADMIN,
            'first_name' => 'Program', 'last_name' => 'Chair',
            'email' => 'chair@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
        $topicId = $this->topic();

        $this->actingAs($chair)->get(route('faculty.test-bank'))->assertForbidden();
        $this->actingAs($chair)->post(route('faculty.question.store'), [
            'topic_id' => $topicId, 'question_text' => 'x', 'question_type' => 'true_false',
            'difficulty' => 'Easy', 'tf_answer' => 'true', 'is_active' => '1',
        ])->assertForbidden();
    }

    public function test_student_cannot_manage_the_test_bank(): void
    {
        $student = User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test', 'last_name' => 'Student',
            'email' => 'student@example.com', 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);

        $this->actingAs($student)->get(route('faculty.test-bank'))->assertForbidden();
    }

    private function faculty(string $email = 'faculty@example.com'): User
    {
        return User::create([
            'role_id' => Role::FACULTY,
            'first_name' => 'Test', 'last_name' => 'Faculty',
            'email' => $email, 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }

    private function topic(): int
    {
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);

        return DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()]);
    }

    private function mcqQuestion(int $topicId, int $createdBy, string $correct): int
    {
        $questionId = DB::table('questions')->insertGetId([
            'topic_id' => $topicId, 'created_by' => $createdBy,
            'question_text' => 'Seed question', 'question_type' => 'mcq', 'difficulty' => 'easy',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        foreach (['a', 'b', 'c', 'd'] as $label) {
            DB::table('question_choices')->insert([
                'question_id' => $questionId,
                'choice_label' => strtoupper($label),
                'choice_text' => "Choice {$label}",
                'is_correct' => $label === $correct,
            ]);
        }

        return $questionId;
    }
}
