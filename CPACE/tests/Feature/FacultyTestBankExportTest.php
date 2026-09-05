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
 * Covers TestBankController::export - it reuses the same scopedQuestionQuery()
 * as the listing page, so this locks in that the CSV/JSON/PDF exports never
 * leak questions from subjects the faculty isn't assigned to. Hand-built
 * schema, same rationale as FacultyTestBankTest (migration set doesn't run
 * cleanly from scratch; question_choices matches the real production schema,
 * not the drifted migration file).
 */
class FacultyTestBankExportTest extends TestCase
{
    private const TABLES = [
        'question_variants', 'question_choices', 'questions', 'topics', 'subjects', 'faculty_subjects',
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
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name');
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
        Schema::create('question_choices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('choice_label', 1);
            $table->text('choice_text');
            $table->boolean('is_correct')->default(false);
            $table->foreign('question_id')->references('id')->on('questions')->cascadeOnDelete();
        });
        Schema::create('question_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->text('variant_text');
            $table->string('source', 20)->default('faculty');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_the_csv_export_never_includes_questions_outside_the_facultys_assigned_subjects(): void
    {
        $faculty = $this->faculty();
        $assignedSubjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        $otherSubjectId = DB::table('subjects')->insertGetId(['code' => 'AUD', 'name' => 'Auditing', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $assignedSubjectId, 'assigned_at' => now()]);
        $assignedTopicId = DB::table('topics')->insertGetId(['subject_id' => $assignedSubjectId, 'name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()]);
        $otherTopicId = DB::table('topics')->insertGetId(['subject_id' => $otherSubjectId, 'name' => 'Risk', 'created_at' => now(), 'updated_at' => now()]);
        $this->question($assignedTopicId, $faculty->id, 'My assigned-subject question');
        $this->question($otherTopicId, $faculty->id, 'A question from a subject I am not assigned to');

        $response = $this->actingAs($faculty)->get(route('faculty.test-bank.export'));

        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('My assigned-subject question', $csv);
        $this->assertStringNotContainsString('A question from a subject I am not assigned to', $csv);
    }

    public function test_the_json_export_format_is_also_scoped_to_assigned_subjects(): void
    {
        $faculty = $this->faculty();
        $assignedSubjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'created_at' => now(), 'updated_at' => now()]);
        $otherSubjectId = DB::table('subjects')->insertGetId(['code' => 'AUD', 'name' => 'Auditing', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $assignedSubjectId, 'assigned_at' => now()]);
        $assignedTopicId = DB::table('topics')->insertGetId(['subject_id' => $assignedSubjectId, 'name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()]);
        $otherTopicId = DB::table('topics')->insertGetId(['subject_id' => $otherSubjectId, 'name' => 'Risk', 'created_at' => now(), 'updated_at' => now()]);
        $this->question($assignedTopicId, $faculty->id, 'Mine');
        $this->question($otherTopicId, $faculty->id, 'Not mine');

        $response = $this->actingAs($faculty)->get(route('faculty.test-bank.export', ['format' => 'json']));

        $response->assertOk();
        $texts = collect($response->json())->pluck('question_text');
        $this->assertTrue($texts->contains('Mine'));
        $this->assertFalse($texts->contains('Not mine'));
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

    private function question(int $topicId, int $createdBy, string $text): int
    {
        $questionId = DB::table('questions')->insertGetId([
            'topic_id' => $topicId, 'created_by' => $createdBy,
            'question_text' => $text, 'question_type' => 'mcq', 'difficulty' => 'easy',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        foreach (['A', 'B'] as $label) {
            DB::table('question_choices')->insert([
                'question_id' => $questionId, 'choice_label' => $label,
                'choice_text' => "Choice {$label}", 'is_correct' => $label === 'A',
            ]);
        }

        return $questionId;
    }
}
