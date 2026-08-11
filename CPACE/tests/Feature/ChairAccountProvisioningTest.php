<?php

namespace Tests\Feature;

use App\Mail\AccountCredentialsMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The Program Chair provisions student/faculty accounts with an
 * auto-generated one-time password. That OTP must reach the account
 * owner's own inbox and must never be visible to the chair - see the
 * "email OTP instead of showing it" request. Hand-built schema, same
 * rationale as the other Feature tests in this suite (migration set
 * doesn't run cleanly from scratch).
 */
class ChairAccountProvisioningTest extends TestCase
{
    private const TABLES = [
        'student_profiles', 'faculty_profiles', 'faculty_subjects',
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
        // default community group - so the chat tables must exist.
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
            $table->integer('year_level')->nullable();
            $table->string('section', 30)->nullable();
            $table->date('exam_target_date')->nullable();
            $table->integer('total_points')->default(0);
            $table->integer('streak_days')->default(0);
            $table->boolean('is_alumni')->default(false);
            $table->timestamp('alumni_marked_at')->nullable();
            $table->boolean('is_shifted')->default(false);
            $table->string('shift_reason')->nullable();
            $table->timestamp('shifted_at')->nullable();
        });
        Schema::create('faculty_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('employee_number', 20)->nullable();
            $table->string('department', 100)->nullable();
        });
        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->primary(['faculty_id', 'subject_id']);
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_enrolling_a_student_emails_the_otp_and_never_leaks_it_to_the_chair(): void
    {
        Mail::fake();
        $chair = $this->chair();

        $response = $this->actingAs($chair)->post(route('chair.students.store'), [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.com',
            'is_active' => '1',
        ]);

        $response->assertRedirect();
        $student = User::where('email', 'juan@example.com')->firstOrFail();
        $this->assertSame(Role::STUDENT, $student->role_id);
        $this->assertNotNull($student->temp_password);

        Mail::assertSent(AccountCredentialsMail::class, function (AccountCredentialsMail $mail) use ($student) {
            return $mail->hasTo($student->email)
                && $mail->tempPassword === $student->temp_password
                && $mail->isReissue === false;
        });

        $status = session('status');
        $this->assertStringNotContainsString($student->temp_password, $status);
    }

    public function test_enrolling_a_faculty_member_emails_the_otp_and_never_leaks_it_to_the_chair(): void
    {
        Mail::fake();
        $chair = $this->chair();

        $response = $this->actingAs($chair)->post(route('chair.faculty.store'), [
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => 'maria@example.com',
        ]);

        $response->assertRedirect();
        $faculty = User::where('email', 'maria@example.com')->firstOrFail();
        $this->assertSame(Role::FACULTY, $faculty->role_id);

        Mail::assertSent(AccountCredentialsMail::class, function (AccountCredentialsMail $mail) use ($faculty) {
            return $mail->hasTo($faculty->email) && $mail->tempPassword === $faculty->temp_password;
        });

        $status = session('status');
        $this->assertStringNotContainsString($faculty->temp_password, $status);
    }

    public function test_regenerating_a_student_otp_emails_a_new_password_and_invalidates_the_old_one(): void
    {
        Mail::fake();
        $chair = $this->chair();
        $student = $this->student('pending@example.com', setupComplete: false);
        $oldTempPassword = $student->temp_password;

        $this->actingAs($chair)->post(route('chair.students.regenerate-otp', $student->id))
            ->assertRedirect();

        $fresh = $student->fresh();
        $this->assertNotSame($oldTempPassword, $fresh->temp_password);

        Mail::assertSent(AccountCredentialsMail::class, function (AccountCredentialsMail $mail) use ($fresh) {
            return $mail->hasTo($fresh->email)
                && $mail->tempPassword === $fresh->temp_password
                && $mail->isReissue === true;
        });

        $this->assertStringNotContainsString($fresh->temp_password, session('status'));
    }

    public function test_check_email_endpoint_flags_an_email_already_in_use(): void
    {
        $chair = $this->chair();
        $this->student('taken@example.com');

        $taken = $this->actingAs($chair)->getJson(route('chair.check-email', ['email' => 'taken@example.com']));
        $taken->assertJson(['taken' => true]);

        $free = $this->actingAs($chair)->getJson(route('chair.check-email', ['email' => 'free@example.com']));
        $free->assertJson(['taken' => false]);
    }

    private function chair(): User
    {
        return User::create([
            'role_id' => Role::ADMIN,
            'first_name' => 'Chair',
            'last_name' => 'User',
            'email' => 'chair@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'setup_completed_at' => now(),
        ]);
    }

    private function student(string $email, bool $setupComplete = true): User
    {
        $user = User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => $email,
            'password' => Hash::make('password'),
            'is_active' => true,
            'setup_completed_at' => $setupComplete ? now() : null,
            'temp_password' => $setupComplete ? null : 'OldPass1',
        ]);
        DB::table('student_profiles')->insert(['user_id' => $user->id]);

        return $user;
    }
}
