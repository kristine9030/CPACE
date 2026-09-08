<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\QuestionImportParser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Import questions from a file: upload -> parse (rule-based here; the AI
 * fallback is unit-tested via the parser directly, not over HTTP, to avoid
 * live API calls) -> stage for review -> faculty approves -> Test Bank.
 * Hand-built schema, same rationale as the other Feature tests in this suite.
 */
class QuestionImportTest extends TestCase
{
    private const TABLES = [
        'question_import_items', 'question_import_batches',
        'question_choices', 'questions', 'topics', 'subjects', 'faculty_subjects',
        'notifications', 'messages', 'conversation_participants', 'conversations', 'student_profiles', 'users',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

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
            $table->string('section', 30)->nullable();
            $table->boolean('is_alumni')->default(false);
            $table->boolean('is_shifted')->default(false);
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->boolean('is_active')->default(true);
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name');
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
        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
        });
        Schema::create('question_import_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('original_filename', 255);
            $table->string('file_type', 20);
            $table->string('status', 20)->default('parsing');
            $table->string('parse_source', 10)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
        Schema::create('question_import_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->text('question_text');
            $table->string('question_type', 20)->default('mcq');
            $table->json('choices')->nullable();
            $table->text('explanation')->nullable();
            $table->string('difficulty', 20)->default('moderate');
            $table->string('source', 10)->default('rule');
            $table->unsignedTinyInteger('confidence')->default(100);
            $table->string('status', 20)->default('pending');
            $table->unsignedSmallInteger('sort_order')->default(0);
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

    public function test_uploading_a_clearly_formatted_text_file_stages_questions_for_review(): void
    {
        $faculty = $this->faculty();
        $subjectId = $this->subjectFor($faculty);

        $content = "1. What is the accounting equation?\n"
            . "A. Assets = Liabilities + Equity\n"
            . "B. Assets = Liabilities - Equity\n"
            . "C. Assets = Revenue - Expenses\n"
            . "D. Assets = Equity - Liabilities\n"
            . "Answer: A\n"
            . "Explanation: The fundamental accounting equation.\n\n"
            . "2. Cash is a current asset.\n"
            . "Answer: True\n";

        $file = UploadedFile::fake()->createWithContent('questions.txt', $content);

        $response = $this->actingAs($faculty)->post(route('faculty.test-bank.import.store'), [
            'subject_id' => $subjectId,
            'file' => $file,
        ]);

        $batch = DB::table('question_import_batches')->where('faculty_id', $faculty->id)->first();
        $this->assertNotNull($batch);
        $response->assertRedirect(route('faculty.test-bank.import.review', $batch->id));
        $this->assertSame('ready', $batch->status);
        $this->assertSame('rule', $batch->parse_source);

        $items = DB::table('question_import_items')->where('batch_id', $batch->id)->orderBy('id')->get();
        $this->assertCount(2, $items);

        $mcq = json_decode($items[0]->choices, true);
        $this->assertSame('mcq', $items[0]->question_type);
        $this->assertTrue(collect($mcq)->firstWhere('label', 'A')['is_correct']);

        $tf = json_decode($items[1]->choices, true);
        $this->assertSame('true_false', $items[1]->question_type);
        $this->assertTrue(collect($tf)->firstWhere('label', 'A')['is_correct']);
    }

    public function test_a_faculty_member_cannot_import_into_a_subject_they_are_not_assigned_to(): void
    {
        $faculty = $this->faculty();
        $otherSubject = $this->subjectFor(null, 'AUD');

        $file = UploadedFile::fake()->createWithContent('q.txt', "1. Q\nA. x\nB. y\nAnswer: A\n");

        $this->actingAs($faculty)->post(route('faculty.test-bank.import.store'), [
            'subject_id' => $otherSubject,
            'file' => $file,
        ])->assertRedirect(route('faculty.test-bank'));

        $this->assertSame(0, DB::table('question_import_batches')->count());
    }

    public function test_a_file_with_no_recognisable_questions_fails_gracefully(): void
    {
        $faculty = $this->faculty();
        $subjectId = $this->subjectFor($faculty);

        $file = UploadedFile::fake()->createWithContent('random.txt', "Just some notes, nothing structured here.");

        $this->actingAs($faculty)->post(route('faculty.test-bank.import.store'), [
            'subject_id' => $subjectId,
            'file' => $file,
        ])->assertRedirect(route('faculty.test-bank.import'))
            ->assertSessionHas('warning');

        $batch = DB::table('question_import_batches')->first();
        $this->assertSame('failed', $batch->status);
        $this->assertSame(0, DB::table('question_import_items')->count());
    }

    public function test_committing_the_review_form_creates_real_test_bank_questions(): void
    {
        $faculty = $this->faculty();
        $subjectId = $this->subjectFor($faculty);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Basics']);

        $batchId = DB::table('question_import_batches')->insertGetId([
            'faculty_id' => $faculty->id, 'subject_id' => $subjectId,
            'original_filename' => 'q.txt', 'file_type' => 'txt', 'status' => 'ready',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $mcqItemId = DB::table('question_import_items')->insertGetId([
            'batch_id' => $batchId, 'question_text' => 'What is FIFO?',
            'question_type' => 'mcq',
            'choices' => json_encode([
                ['label' => 'A', 'text' => 'First In First Out', 'is_correct' => true],
                ['label' => 'B', 'text' => 'Last In First Out', 'is_correct' => false],
            ]),
            'confidence' => 90, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $excludedItemId = DB::table('question_import_items')->insertGetId([
            'batch_id' => $batchId, 'question_text' => 'Bad row',
            'question_type' => 'mcq', 'choices' => json_encode([]),
            'confidence' => 20, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($faculty)->post(route('faculty.test-bank.import.commit', $batchId), [
            'items' => [
                $mcqItemId => [
                    'include' => '1',
                    'topic_id' => $topicId,
                    'question_text' => 'What is FIFO?',
                    'question_type' => 'mcq',
                    'difficulty' => 'Easy',
                    'choices' => ['A' => 'First In First Out', 'B' => 'Last In First Out'],
                    'correct_label' => 'A',
                    'explanation' => 'Inventory costing method.',
                ],
                // $excludedItemId omitted entirely -> treated as excluded.
            ],
        ]);

        $response->assertRedirect(route('faculty.test-bank'));
        $this->assertSame(1, DB::table('questions')->count());

        $question = DB::table('questions')->first();
        $this->assertSame('What is FIFO?', $question->question_text);
        $this->assertSame('easy', $question->difficulty);
        $this->assertSame(2, DB::table('question_choices')->where('question_id', $question->id)->count());

        $this->assertSame('committed', DB::table('question_import_batches')->find($batchId)->status);
        $this->assertSame('rejected', DB::table('question_import_items')->find($excludedItemId)->status);
    }

    public function test_discarding_a_batch_deletes_it_without_touching_the_test_bank(): void
    {
        $faculty = $this->faculty();
        $subjectId = $this->subjectFor($faculty);

        $batchId = DB::table('question_import_batches')->insertGetId([
            'faculty_id' => $faculty->id, 'subject_id' => $subjectId,
            'original_filename' => 'q.txt', 'file_type' => 'txt', 'status' => 'ready',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($faculty)->delete(route('faculty.test-bank.import.destroy', $batchId))
            ->assertRedirect(route('faculty.test-bank'));

        $this->assertSame(0, DB::table('question_import_batches')->count());
        $this->assertSame(0, DB::table('questions')->count());
    }

    public function test_a_downloaded_template_can_be_uploaded_and_understood_by_the_parser(): void
    {
        $generator = new \App\Services\QuestionImportTemplateGenerator();

        foreach (['txt', 'csv'] as $type) {
            [$contents] = $generator->build($type);
            $path = tempnam(sys_get_temp_dir(), 'tpl') . '.' . $type;
            file_put_contents($path, $contents);

            $parser = new QuestionImportParser();
            $items = $type === 'csv'
                ? $parser->parseStructuredTable($path, $type)
                : $parser->parseFreeText($parser->extractText($path, $type));

            $this->assertNotNull($items, "Template type [{$type}] did not parse back into any questions.");
            $this->assertCount(3, $items, "Template type [{$type}] should round-trip into exactly 3 sample questions.");

            unlink($path);
        }
    }

    public function test_faculty_can_download_a_sample_template_for_every_supported_format(): void
    {
        $faculty = $this->faculty();

        foreach (\App\Services\QuestionImportTemplateGenerator::TYPES as $type) {
            $this->actingAs($faculty)
                ->get(route('faculty.test-bank.import.template', $type))
                ->assertOk();
        }
    }

    public function test_an_unsupported_template_type_is_rejected(): void
    {
        $faculty = $this->faculty();

        $this->actingAs($faculty)
            ->get(route('faculty.test-bank.import.template', 'exe'))
            ->assertNotFound();
    }

    /** Unit-level: the free-text rule parser directly, no HTTP/AI involved. */
    public function test_rule_based_parser_extracts_mcq_and_true_false_from_free_text(): void
    {
        $parser = new QuestionImportParser();
        $text = "1) Which statement shows financial position?\n"
            . "A) Income Statement\n"
            . "B) Balance Sheet\n"
            . "C) Cash Flow Statement\n"
            . "D) Statement of Equity\n"
            . "Ans: B\n";

        $items = $parser->parseFreeText($text);

        $this->assertNotNull($items);
        $this->assertCount(1, $items);
        $this->assertSame('mcq', $items[0]['question_type']);
        $this->assertTrue(collect($items[0]['choices'])->firstWhere('label', 'B')['is_correct']);
        $this->assertGreaterThanOrEqual(70, $items[0]['confidence']);
    }

    public function test_rule_based_parser_returns_null_when_nothing_looks_like_a_question(): void
    {
        $parser = new QuestionImportParser();

        $this->assertNull($parser->parseFreeText('Chapter 3: Introduction to accounting concepts.'));
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

    private function subjectFor(?User $faculty, string $code = 'FAR'): int
    {
        $subjectId = DB::table('subjects')->insertGetId(['code' => $code, 'name' => $code . ' subject', 'is_active' => true]);
        if ($faculty) {
            DB::table('faculty_subjects')->insert(['faculty_id' => $faculty->id, 'subject_id' => $subjectId, 'assigned_at' => now()]);
        }

        return $subjectId;
    }
}
