<?php

namespace Tests\Feature;

use App\Mail\AccountCredentialsMail;
use App\Models\Role;
use App\Models\Subject;
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
        'quiz_sessions', 'performance_records', 'topics', 'alumni_profiles', 'student_profiles', 'faculty_profiles', 'faculty_subjects', 'subjects',
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
        Schema::create('alumni_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->year('batch_year')->nullable();
            $table->string('cpa_number', 30)->nullable();
            $table->date('passed_at')->nullable();
            $table->string('current_job')->nullable();
            $table->string('company')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->text('bio')->nullable();
        });
        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->primary(['faculty_id', 'subject_id']);
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
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('topic_id');
            $table->integer('correct_count')->default(0);
            $table->integer('total_attempts')->default(0);
            $table->integer('consecutive_wrong')->default(0);
        });
        // studentRows() (backs the chair.students index) always queries this
        // table even when nobody has taken a quiz yet.
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('session_type');
            $table->integer('total_items');
            $table->integer('correct_answers');
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

    public function test_regenerating_a_faculty_otp_emails_a_new_password_and_invalidates_the_old_one(): void
    {
        Mail::fake();
        $chair = $this->chair();
        $faculty = $this->faculty('pending-faculty2@example.com', setupComplete: false);

        $this->actingAs($chair)->post(route('chair.faculty.regenerate-otp', $faculty->id))
            ->assertRedirect();

        $fresh = $faculty->fresh();
        $this->assertNotSame('OldPass1', $fresh->temp_password);

        Mail::assertSent(AccountCredentialsMail::class, function (AccountCredentialsMail $mail) use ($fresh) {
            return $mail->hasTo($fresh->email)
                && $mail->tempPassword === $fresh->temp_password
                && $mail->isReissue === true;
        });
    }

    public function test_regenerating_a_faculty_otp_is_rejected_once_setup_is_already_complete(): void
    {
        Mail::fake();
        $chair = $this->chair();
        $faculty = $this->faculty('already-setup@example.com');
        $oldTempPassword = $faculty->temp_password;

        $this->actingAs($chair)->post(route('chair.faculty.regenerate-otp', $faculty->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame($oldTempPassword, $faculty->fresh()->temp_password);
        Mail::assertNothingSent();
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

    public function test_students_index_shows_the_manual_otp_only_for_accounts_still_pending_setup(): void
    {
        $chair = $this->chair();
        $pending = $this->student('pending@example.com', setupComplete: false);
        $active = $this->student('active@example.com', setupComplete: true);

        $response = $this->actingAs($chair)->get(route('chair.students'));

        $response->assertOk();
        $response->assertSee($pending->temp_password);
        $response->assertDontSee($active->temp_password ?? '__none__');
    }

    public function test_faculty_index_shows_the_manual_otp_only_for_accounts_still_pending_setup(): void
    {
        $chair = $this->chair();
        $pending = $this->faculty('pending-faculty@example.com', setupComplete: false);
        $active = $this->faculty('active-faculty@example.com', setupComplete: true);

        $response = $this->actingAs($chair)->get(route('chair.faculty'));

        $response->assertOk();
        $response->assertSee($pending->temp_password);
        $response->assertDontSee($active->temp_password ?? '__none__');
    }

    public function test_toggling_a_student_flips_their_active_status_each_time(): void
    {
        $chair = $this->chair();
        $student = $this->student('toggle-student@example.com');
        $this->assertTrue($student->is_active);

        $this->actingAs($chair)->post(route('chair.students.toggle', $student->id))->assertRedirect();
        $this->assertFalse($student->fresh()->is_active);

        $this->actingAs($chair)->post(route('chair.students.toggle', $student->id))->assertRedirect();
        $this->assertTrue($student->fresh()->is_active);
    }

    public function test_updating_a_student_as_shifted_forces_the_account_inactive_regardless_of_the_toggle(): void
    {
        $chair = $this->chair();
        $student = $this->student('shifting@example.com');

        $this->actingAs($chair)->put(route('chair.students.update', $student->id), [
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'email' => $student->email,
            'is_active' => '1',
            'is_shifted' => '1',
            'shift_reason' => 'Transferred to another program',
        ])->assertRedirect();

        $this->assertFalse($student->fresh()->is_active);
    }

    public function test_toggling_a_faculty_account_flips_their_active_status_each_time(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty('toggle-faculty@example.com');
        $this->assertTrue($faculty->is_active);

        $this->actingAs($chair)->post(route('chair.faculty.toggle', $faculty->id))->assertRedirect();
        $this->assertFalse($faculty->fresh()->is_active);

        $this->actingAs($chair)->post(route('chair.faculty.toggle', $faculty->id))->assertRedirect();
        $this->assertTrue($faculty->fresh()->is_active);
    }

    public function test_assigning_subjects_replaces_a_facultys_previous_assignments_rather_than_adding_to_them(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty('assign-faculty@example.com');
        [$subjectA, $subjectB] = [
            Subject::create(['code' => 'GEC1', 'name' => 'General Ed 1']),
            Subject::create(['code' => 'GEC2', 'name' => 'General Ed 2']),
        ];
        $faculty->assignedSubjects()->attach($subjectA->id, ['assigned_at' => now()]);

        $this->actingAs($chair)->post(route('chair.faculty.assign', $faculty->id), [
            'subjects' => [$subjectB->id],
        ])->assertRedirect();

        $assigned = $faculty->assignedSubjects()->pluck('subjects.id')->all();
        $this->assertSame([$subjectB->id], $assigned);
    }

    public function test_chair_can_view_a_students_detail_page_including_weak_areas(): void
    {
        $chair = $this->chair();
        $student = $this->student('detail@example.com');
        $subjectId = DB::table('subjects')->insertGetId(['code' => 'FAR', 'name' => 'Financial Accounting', 'passing_threshold' => 75, 'created_at' => now(), 'updated_at' => now()]);
        $topicId = DB::table('topics')->insertGetId(['subject_id' => $subjectId, 'name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()]);
        // 5 attempts, 40% accuracy: meets MIN_ATTEMPTS and is below ACCURACY_THRESHOLD.
        DB::table('performance_records')->insert(['student_id' => $student->id, 'topic_id' => $topicId, 'total_attempts' => 5, 'correct_count' => 2, 'consecutive_wrong' => 0]);

        $response = $this->actingAs($chair)->get(route('chair.students.show', $student->id));

        $response->assertOk();
        $response->assertViewHas('weakAreas', fn ($weakAreas) => $weakAreas->count() === 1 && $weakAreas->first()->topic === 'Inventory');
    }

    public function test_chair_can_view_the_student_edit_form(): void
    {
        $chair = $this->chair();
        $student = $this->student('editme@example.com');

        $this->actingAs($chair)->get(route('chair.students.edit', $student->id))
            ->assertOk()
            ->assertSee($student->email);
    }

    public function test_chair_can_update_a_students_basic_details(): void
    {
        $chair = $this->chair();
        $student = $this->student('before@example.com');

        $this->actingAs($chair)->put(route('chair.students.update', $student->id), [
            'first_name' => 'Renamed',
            'last_name' => $student->last_name,
            'email' => 'after@example.com',
            'is_active' => '1',
        ])->assertRedirect();

        $fresh = $student->fresh();
        $this->assertSame('Renamed', $fresh->first_name);
        $this->assertSame('after@example.com', $fresh->email);
    }

    public function test_a_students_email_must_be_unique_when_updating(): void
    {
        $chair = $this->chair();
        $student = $this->student('keep-mine@example.com');
        $this->student('taken-by-other@example.com');

        $this->actingAs($chair)->put(route('chair.students.update', $student->id), [
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'email' => 'taken-by-other@example.com',
            'is_active' => '1',
        ])->assertSessionHasErrors('email');

        $this->assertSame('keep-mine@example.com', $student->fresh()->email);
    }

    public function test_chair_can_view_the_faculty_edit_form(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty('editme-faculty@example.com');

        $this->actingAs($chair)->get(route('chair.faculty.edit', $faculty->id))
            ->assertOk()
            ->assertSee($faculty->email);
    }

    public function test_chair_can_update_a_faculty_account(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty('before-faculty@example.com');

        $this->actingAs($chair)->put(route('chair.faculty.update', $faculty->id), [
            'first_name' => 'Renamed',
            'last_name' => $faculty->last_name,
            'email' => 'after-faculty@example.com',
            'is_active' => '1',
            'subjects' => [],
        ])->assertRedirect();

        $fresh = $faculty->fresh();
        $this->assertSame('Renamed', $fresh->first_name);
        $this->assertSame('after-faculty@example.com', $fresh->email);
    }

    public function test_a_facultys_email_must_be_unique_when_updating(): void
    {
        $chair = $this->chair();
        $faculty = $this->faculty('keep-mine-faculty@example.com');
        $this->faculty('taken-by-other-faculty@example.com');

        $this->actingAs($chair)->put(route('chair.faculty.update', $faculty->id), [
            'first_name' => $faculty->first_name,
            'last_name' => $faculty->last_name,
            'email' => 'taken-by-other-faculty@example.com',
            'is_active' => '1',
            'subjects' => [],
        ])->assertSessionHasErrors('email');

        $this->assertSame('keep-mine-faculty@example.com', $faculty->fresh()->email);
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

    private function faculty(string $email, bool $setupComplete = true): User
    {
        $user = User::create([
            'role_id' => Role::FACULTY,
            'first_name' => 'Test',
            'last_name' => 'Faculty',
            'email' => $email,
            'password' => Hash::make('password'),
            'is_active' => true,
            'setup_completed_at' => $setupComplete ? now() : null,
            'temp_password' => $setupComplete ? null : 'OldPass1',
        ]);
        DB::table('faculty_profiles')->insert(['user_id' => $user->id]);

        return $user;
    }
}
