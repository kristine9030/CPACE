<?php

namespace Tests\Feature;

use App\Mail\AccountCredentialsMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Covers the chair's bulk student import (CSV) and export (CSV/PDF)
 * endpoints - import in particular is a bulk account-creation path that
 * deserves the same scrutiny as the single-student enrollment flow covered
 * in ChairAccountProvisioningTest. Hand-built schema, same rationale as the
 * other Feature tests in this suite.
 */
class ChairStudentImportExportTest extends TestCase
{
    private const TABLES = [
        'quiz_sessions', 'student_profiles', 'notifications', 'messages',
        'conversation_participants', 'conversations', 'users',
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
            $table->timestamp('setup_completed_at')->nullable();
            $table->string('temp_password')->nullable();
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
            $table->string('student_number', 30)->nullable();
            $table->string('section', 30)->nullable();
        });
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('session_type', 20)->default('testing');
            $table->integer('total_items')->default(0);
            $table->integer('correct_answers')->default(0);
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

    public function test_importing_a_csv_creates_an_account_for_each_valid_row(): void
    {
        Mail::fake();
        $chair = $this->chair();
        $csv = "first_name,last_name,email,student_number,section\n"
            . "Juan,Dela Cruz,juan@example.com,23-00001,BSA-4A\n"
            . "Maria,Santos,maria@example.com,23-00002,BSA-4A\n";

        $this->actingAs($chair)->post(route('chair.students.import'), [
            'file' => UploadedFile::fake()->createWithContent('students.csv', $csv),
        ])->assertRedirect();

        $this->assertSame(2, DB::table('users')->where('role_id', Role::STUDENT)->count());
        $this->assertNotNull(DB::table('student_profiles')->where('student_number', '23-00001')->first());
        Mail::assertSent(AccountCredentialsMail::class, 2);
    }

    public function test_a_row_missing_a_last_name_is_skipped_but_the_rest_still_import(): void
    {
        Mail::fake();
        $chair = $this->chair();
        $csv = "first_name,last_name,email\n"
            . "NoLastName,,broken@example.com\n"
            . "Juan,Dela Cruz,juan@example.com\n";

        $this->actingAs($chair)->post(route('chair.students.import'), [
            'file' => UploadedFile::fake()->createWithContent('students.csv', $csv),
        ])->assertRedirect()->assertSessionHas('import_errors', fn ($errors) => count($errors) === 1);

        $this->assertSame(1, DB::table('users')->where('role_id', Role::STUDENT)->count());
        $this->assertSame(0, DB::table('users')->where('email', 'broken@example.com')->count());
    }

    public function test_a_row_with_an_email_already_in_use_is_skipped(): void
    {
        Mail::fake();
        $chair = $this->chair();
        $this->student('taken@example.com');
        $csv = "first_name,last_name,email\nDup,Licate,taken@example.com\n";

        $this->actingAs($chair)->post(route('chair.students.import'), [
            'file' => UploadedFile::fake()->createWithContent('students.csv', $csv),
        ])->assertSessionHas('import_errors', fn ($errors) => count($errors) === 1);

        $this->assertSame(1, DB::table('users')->where('email', 'taken@example.com')->count());
    }

    public function test_a_blank_email_is_auto_generated_from_the_student_number(): void
    {
        Mail::fake();
        $chair = $this->chair();
        $csv = "first_name,last_name,email,student_number\nJuan,Dela Cruz,,23-70362\n";

        $this->actingAs($chair)->post(route('chair.students.import'), [
            'file' => UploadedFile::fake()->createWithContent('students.csv', $csv),
        ])->assertRedirect();

        $this->assertNotNull(DB::table('users')->where('email', '23-70362@g.batstate-u.edu.ph')->first());
    }

    public function test_submitting_edited_preview_rows_creates_accounts_from_the_edits_not_the_original_file(): void
    {
        // The browser parses the CSV and lets the chair fix it up client-side
        // before posting back — so a submission carrying rows_json must use
        // those (possibly corrected) values instead of re-parsing the file.
        Mail::fake();
        $chair = $this->chair();
        $csv = "first_name,last_name,email\nTypo,Name,wrong@example.com\n";

        $this->actingAs($chair)->post(route('chair.students.import'), [
            'file' => UploadedFile::fake()->createWithContent('students.csv', $csv),
            'rows_json' => json_encode([
                ['first_name' => 'Fixed', 'last_name' => 'Name', 'email' => 'fixed@example.com', 'student_number' => '23-00099', 'section' => 'BSA-4A'],
            ]),
        ])->assertRedirect();

        $this->assertSame(0, DB::table('users')->where('email', 'wrong@example.com')->count());
        $this->assertNotNull(DB::table('users')->where('email', 'fixed@example.com')->first());
    }

    public function test_a_row_removed_from_the_edited_preview_is_not_created(): void
    {
        Mail::fake();
        $chair = $this->chair();

        $this->actingAs($chair)->post(route('chair.students.import'), [
            'rows_json' => json_encode([
                ['first_name' => 'Keep', 'last_name' => 'Me', 'email' => 'keep@example.com'],
            ]),
        ])->assertRedirect();

        $this->assertSame(1, DB::table('users')->where('role_id', Role::STUDENT)->count());
        $this->assertNotNull(DB::table('users')->where('email', 'keep@example.com')->first());
    }

    public function test_a_file_without_the_required_columns_is_rejected(): void
    {
        $chair = $this->chair();
        $csv = "name,email\nJuan Dela Cruz,juan@example.com\n";

        $this->actingAs($chair)->post(route('chair.students.import'), [
            'file' => UploadedFile::fake()->createWithContent('students.csv', $csv),
        ])->assertSessionHas('error');

        $this->assertSame(0, DB::table('users')->where('role_id', Role::STUDENT)->count());
    }

    public function test_exporting_csv_streams_the_filtered_student_roster(): void
    {
        $chair = $this->chair();
        $alice = $this->student('alice@example.com');
        $bob = $this->student('bob@example.com');

        $response = $this->actingAs($chair)->get(route('chair.students.export.csv'));

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString($alice->email, $content);
        $this->assertStringContainsString($bob->email, $content);
    }

    public function test_exporting_pdf_view_includes_the_scoped_roster(): void
    {
        $chair = $this->chair();
        $student = $this->student('roster@example.com');

        $this->actingAs($chair)->get(route('chair.students.export.pdf'))
            ->assertOk()
            ->assertSee($student->email);
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

    private function student(string $email): User
    {
        $user = User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test', 'last_name' => 'Student',
            'email' => $email, 'password' => Hash::make('password'),
            'is_active' => true, 'setup_completed_at' => now(),
        ]);
        DB::table('student_profiles')->insert(['user_id' => $user->id]);

        return $user;
    }
}
