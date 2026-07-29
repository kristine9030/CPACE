<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * Tables torn down in FK-safe order. Hand-built schema, same rationale as
     * the other Feature tests in this suite (migration set doesn't run
     * cleanly from scratch). student_profiles mirrors the real StudentProfile
     * model (primary key is user_id, not a separate auto-incrementing id -
     * the migration file disagrees with this but the model is authoritative).
     */
    private const TABLES = [
        'password_reset_tokens', 'student_profiles',
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
        // Creating a student/alumni fires User::booted(), which drops them
        // into the default community group - so the chat tables must exist.
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
            $table->string('mobile', 30)->nullable();
            $table->string('study_days', 60)->nullable();
            $table->string('study_time')->nullable();
            $table->string('study_intensity')->nullable();
            $table->string('focus_subjects', 120)->nullable();
            $table->boolean('is_alumni')->default(false);
            $table->timestamp('alumni_marked_at')->nullable();
            $table->boolean('is_shifted')->default(false);
            $table->string('shift_reason')->nullable();
            $table->timestamp('shifted_at')->nullable();
        });
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    #[DataProvider('roleHomeRoutes')]
    public function test_login_routes_each_role_to_its_own_home(int $roleId, string $expectedRouteName): void
    {
        $user = $this->user($roleId, 'person@example.com');

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route($expectedRouteName));

        $this->assertAuthenticatedAs($user);
    }

    public static function roleHomeRoutes(): array
    {
        return [
            'chair'   => [Role::ADMIN, 'chair.dashboard'],
            'faculty' => [Role::FACULTY, 'faculty.dashboard'],
            'alumni'  => [Role::ALUMNI, 'community.index'],
            'student' => [Role::STUDENT, 'dashboard'],
        ];
    }

    public function test_login_fails_with_an_incorrect_password_without_revealing_which_field_was_wrong(): void
    {
        $user = $this->user(Role::STUDENT, 'student@example.com');

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_shifted_student_is_rejected_at_login_and_signed_back_out(): void
    {
        $user = $this->user(Role::STUDENT, 'shifted@example.com');
        DB::table('student_profiles')->where('user_id', $user->id)->update(['is_shifted' => true]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_signup_creates_a_student_account_and_signs_them_in(): void
    {
        $this->post('/signup', [
            'name' => 'Jane Dela Cruz',
            'email' => 'jane@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertRedirect(route('dashboard'));

        $user = User::where('email', 'jane@example.com')->firstOrFail();
        $this->assertSame(Role::STUDENT, $user->role_id);
        $this->assertAuthenticatedAs($user);
    }

    public function test_signup_rejects_a_duplicate_email(): void
    {
        $this->user(Role::STUDENT, 'existing@example.com');

        $this->post('/signup', [
            'name' => 'Someone Else',
            'email' => 'existing@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, DB::table('users')->where('email', 'existing@example.com')->count());
    }

    public function test_a_student_needing_setup_is_redirected_away_from_the_dashboard(): void
    {
        $user = $this->user(Role::STUDENT, 'needs-setup@example.com', setupComplete: false);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertRedirect(route('account-setup'));
    }

    public function test_completing_account_setup_marks_it_complete_and_redirects_to_welcome(): void
    {
        $user = $this->user(Role::STUDENT, 'setup@example.com', setupComplete: false);

        $this->actingAs($user)->post(route('account-setup.store'), [
            'password' => 'NewPassw0rd',
            'password_confirmation' => 'NewPassw0rd',
            'student_number' => '2026-00123',
        ])->assertRedirect(route('onboarding.welcome'));

        $this->assertNotNull($user->fresh()->setup_completed_at);
        $this->assertSame('2026-00123', DB::table('student_profiles')->where('user_id', $user->id)->value('student_number'));
    }

    public function test_a_faculty_needing_setup_is_redirected_to_the_faculty_setup_page(): void
    {
        $faculty = $this->user(Role::FACULTY, 'newfaculty@example.com', setupComplete: false);

        $this->actingAs($faculty)->get(route('faculty.dashboard'))
            ->assertRedirect(route('faculty.account-setup'));
    }

    public function test_completing_faculty_account_setup_clears_the_temp_password(): void
    {
        $faculty = $this->user(Role::FACULTY, 'facultysetup@example.com', setupComplete: false);
        DB::table('users')->where('id', $faculty->id)->update(['temp_password' => 'ABC12345']);

        $this->actingAs($faculty)->post(route('faculty.account-setup.store'), [
            'password' => 'NewPassw0rd',
            'password_confirmation' => 'NewPassw0rd',
        ])->assertRedirect(route('faculty.dashboard'));

        $fresh = $faculty->fresh();
        $this->assertNotNull($fresh->setup_completed_at);
        $this->assertNull($fresh->temp_password);
    }

    public function test_logout_ends_the_session(): void
    {
        $user = $this->user(Role::STUDENT, 'logout@example.com');

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_alumni_only_routes_reject_a_non_alumni_user(): void
    {
        $student = $this->user(Role::STUDENT, 'notalumni@example.com');

        $this->actingAs($student)->get(route('alumni.profile'))->assertForbidden();
    }

    public function test_forgot_password_sends_a_reset_link_only_when_the_account_exists_but_the_message_never_differs(): void
    {
        Mail::fake();
        $user = $this->user(Role::STUDENT, 'hasaccount@example.com');

        $this->post(route('password.email'), ['email' => $user->email]);
        $statusWithAccount = session('status');

        $this->post(route('password.email'), ['email' => 'nobody@example.com']);
        $statusWithoutAccount = session('status');

        // Same response either way - the endpoint must not leak which emails are registered.
        $this->assertNotNull($statusWithAccount);
        $this->assertSame($statusWithAccount, $statusWithoutAccount);
        Mail::assertSentCount(1);
    }

    public function test_resetting_a_password_with_a_valid_token_allows_logging_in_with_the_new_password(): void
    {
        $user = $this->user(Role::STUDENT, 'reset@example.com');
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'BrandNewPass1',
            'password_confirmation' => 'BrandNewPass1',
        ])->assertRedirect(route('login'));

        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'BrandNewPass1'])
            ->assertRedirect(route('dashboard'));
    }

    private function user(int $roleId, string $email, bool $setupComplete = true): User
    {
        $user = User::create([
            'role_id' => $roleId,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'password' => Hash::make('password'),
            'is_active' => true,
            'setup_completed_at' => $setupComplete ? now() : null,
        ]);

        if ($roleId === Role::STUDENT) {
            DB::table('student_profiles')->insert(['user_id' => $user->id]);
        }

        return $user;
    }
}
