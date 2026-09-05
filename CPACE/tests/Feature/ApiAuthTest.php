<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Covers the mobile API's bearer-token auth (Api\AuthApiController +
 * ApiAuthenticate middleware) - login, signup, logout, and the token
 * validity checks (expired, missing, wrong role, deactivated account).
 * Hand-built schema, same rationale as the other Feature tests in this
 * suite (migration set doesn't run cleanly from scratch).
 */
class ApiAuthTest extends TestCase
{
    private const TABLES = ['api_tokens', 'student_profiles', 'messages', 'conversation_participants', 'conversations', 'users'];

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
            $table->string('profile_photo')->nullable();
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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->integer('streak_days')->default(0);
            $table->integer('total_points')->default(0);
            $table->date('exam_target_date')->nullable();
        });
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('token', 128)->unique();
            $table->timestamp('expires_at')->nullable();
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

    public function test_a_student_can_log_in_and_receives_a_bearer_token(): void
    {
        $this->student('me@example.com', 'password123');

        $response = $this->postJson('/api/login', ['email' => 'me@example.com', 'password' => 'password123']);

        $response->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'email']]);
        $this->assertSame(1, DB::table('api_tokens')->count());
    }

    public function test_login_is_rejected_with_the_wrong_password(): void
    {
        $this->student('me@example.com', 'password123');

        $this->postJson('/api/login', ['email' => 'me@example.com', 'password' => 'wrong'])
            ->assertStatus(422);

        $this->assertSame(0, DB::table('api_tokens')->count());
    }

    public function test_a_deactivated_account_cannot_log_in(): void
    {
        $this->student('me@example.com', 'password123', active: false);

        $this->postJson('/api/login', ['email' => 'me@example.com', 'password' => 'password123'])
            ->assertStatus(403);

        $this->assertSame(0, DB::table('api_tokens')->count());
    }

    public function test_a_non_student_account_is_blocked_from_mobile_login(): void
    {
        $faculty = User::create([
            'role_id' => Role::FACULTY, 'first_name' => 'Test', 'last_name' => 'Faculty',
            'email' => 'faculty@example.com', 'password' => Hash::make('password123'), 'is_active' => true,
        ]);

        $this->postJson('/api/login', ['email' => $faculty->email, 'password' => 'password123'])
            ->assertStatus(403);

        $this->assertSame(0, DB::table('api_tokens')->count());
    }

    public function test_signing_up_creates_a_student_account_and_logs_it_in(): void
    {
        $response = $this->postJson('/api/signup', [
            'first_name' => 'New',
            'last_name' => 'Student',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)->assertJsonStructure(['token', 'user']);
        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertSame(Role::STUDENT, $user->role_id);
        $this->assertNotNull(DB::table('student_profiles')->where('user_id', $user->id)->first());
    }

    public function test_the_user_endpoint_requires_a_valid_token(): void
    {
        $this->getJson('/api/user')->assertStatus(401);
    }

    public function test_the_user_endpoint_rejects_an_expired_token(): void
    {
        $student = $this->student('me@example.com', 'password123');
        $token = ApiToken::create(['user_id' => $student->id, 'token' => 'expired-token', 'expires_at' => now()->subDay()]);

        $this->getJson('/api/user', ['Authorization' => 'Bearer ' . $token->token])->assertStatus(401);
    }

    public function test_the_user_endpoint_returns_the_authenticated_students_profile(): void
    {
        $student = $this->student('me@example.com', 'password123');

        $response = $this->getJson('/api/user', $this->tokenHeaders($student->id));

        $response->assertOk()->assertJsonPath('user.email', 'me@example.com');
    }

    public function test_logging_out_invalidates_the_token(): void
    {
        $student = $this->student('me@example.com', 'password123');
        $headers = $this->tokenHeaders($student->id);

        $this->postJson('/api/logout', [], $headers)->assertOk();

        $this->getJson('/api/user', $headers)->assertStatus(401);
    }

    private function student(string $email, string $password, bool $active = true): User
    {
        $student = User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test', 'last_name' => 'Student',
            'email' => $email, 'password' => Hash::make($password),
            'is_active' => $active,
        ]);
        DB::table('student_profiles')->insert(['user_id' => $student->id]);

        return $student;
    }

    private function tokenHeaders(int $userId): array
    {
        $token = ApiToken::create([
            'user_id' => $userId,
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => now()->addDay(),
        ]);

        return ['Authorization' => 'Bearer ' . $token->token];
    }
}
